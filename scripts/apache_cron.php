<?php

require(realpath(dirname(__FILE__).'/../lib/utils.php'));
require(realpath(dirname(__FILE__).'/../lib/summarize.php'));

const BATCH_SIZE = 5000;
$g_log_level = WARN;
$g_log_file = null;

///////////////////////////////
// Bootstrap the environment
///////////////////////////////

$config = load_config();
if ($config === false) {
  log_(FATAL, "Unable to load the Bluebird analytics configuration file");
  exit(1);
}

if (isset($config['debug']['level'])) {
  $g_log_level = $config['debug']['level'];
}

if (isset($config['debug']['file'])) {
  $g_log_file = get_log_file($config['debug']['file']);
}

$dbcon = get_db_connection($config['database']);
if ($dbcon === false) {
  log_(FATAL, "Unable to connect to the database");
  exit(1);
}
log_(DEBUG, "Loaded Configuration:\n".var_export($config,1));

// create the instance cache
$g_instance_cache = load_bluebird_instances($config['input']);
if (!$g_instance_cache) {
  log_(FATAL, 'Could not load BB Config!');
  exit(1);
}
// match to the IDs in the instance table
try {
  $result = $dbcon->query("SELECT id, name FROM instance");
}
catch (Exception $e) {
  log_(FATAL, 'Could not load instance records! '.$e->getMessage());
  exit(1);
}
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
  if (array_key_exists($row['name'], $g_instance_cache)) {
    $g_instance_cache[$row['name']] = $row['id'];
  }
}
log_(DEBUG, "Instance List:\n".var_export($g_instance_cache,1));

// Figure out which files to read
$source_paths = get_source_files($config['input']);
if (empty($source_paths)) {
  log_(ERROR, "Configured input.base_path does not match any files");
  exit(1);
}

// Get the time and final offset of the last run. These values are also the
// default final values in case there was no new data to run. The schema
// automatically inserts a default 0/0 entry in this table so it will always
// have at least one result row.
try {
  $result = $dbcon->query(
    "SELECT * FROM apache_cron_runs ORDER BY final_ctime DESC LIMIT 1");
}
catch (Exception $e) {
  log_(FATAL, 'Could not load cron run history! '.$e->getMessage());
  exit(1);
}

$row = $result->fetch(PDO::FETCH_ASSOC);
$start_offset = $row['final_offset'];
$start_ctime = $row['final_ctime'];
$start_utime = strtotime($start_ctime);
log_(INFO, "Last run ended at $start_ctime; offset=$start_offset");

// Process log files that have been updated since the last run. Use >= here
// so that we catch files that were rotated immediately after our last run.
// Otherwise we might apply the byte offset to a new, unrelated file.

$first_ts = null;
$last_ts = null;

foreach ($source_paths as $source_path) {
  if (filemtime($source_path) >= $start_utime) {
    $start_time = microtime(true);
    log_(INFO, "Processing log file: $source_path [offset=$start_offset]");
    $res = process_apache_log($source_path, $start_offset, $dbcon);
    $final_offset = $res['offset'];

    if ($first_ts == null || strtotime($res['first_ts']) < strtotime($first_ts)) {
      $first_ts = $res['first_ts'];
    }

    if ($last_ts == null || strtotime($res['last_ts']) > strtotime($last_ts)) {
      $last_ts = $res['last_ts'];
    }

    $elapsed_time = round(microtime(true) - $start_time, 3);
    log_(INFO, "Processed log file '$source_path' in ${elapsed_time}s; first_ts={$res['first_ts']}; last_ts={$res['last_ts']}; new offset=$final_offset");
    $start_offset = 0;
  }
}

if ($first_ts && $last_ts) {
  // Update all the summaries affected by this time range.
  log_(INFO, "Generating summaries for requests from $first_ts to $last_ts");
  summarize($dbcon, $first_ts, $last_ts);

  // Save run state so we can easily resume, if we actually processed a log.
  log_(INFO, "Recording final offset=$final_offset, ctime=$last_ts");
  try {
    $dbcon->exec("INSERT INTO apache_cron_runs
                  VALUES ($final_offset, '$last_ts')");
  }
  catch (Exception $e) {
    log_(ERROR, 'Could not insert latest cron run: '.$e->getMessage());
  }
}
else {
  log_(INFO, "No new logging records found; summaries not created; apache_cron_runs not updated");
}


/**
 *  Opens the given log at the given byte offset and inserts the remaining
 *  records into the database. Returns the final log entry time and byte
 *  offset so that future runs can avoid reprocessing the same entries.
 */
function process_apache_log($source_path, $offset, PDO $dbcon)
{
  $handle = fopen($source_path, "r");
  if ($handle === false) {
    log_(ERROR, "Source file '$source_path' cannot be opened for reading");
    return false;
  }

  $file_size = filesize($source_path);
  // Reset the offset if it exceeds the size of the file.
  if ($offset > $file_size) {
    $offset = 0;
  }

  // Start from where we left off and process new entries.
  log_(INFO, "Reading '$source_path' [size=$file_size] from offset '$offset'");
  fseek($handle, $offset);

  // Increase the insert time by deferring indexing and foreign key checks.

  // For every valid line in the rest of the file:
  //  * Get the insert values for that line
  //  * Do a periodic bulk insert
  //  * Track timestamp of the last entry
  $values = array();
  $first_ts = null;
  $last_ts = null;
  $batch_count = 0;

  while (!feof($handle)) {
    $log_line = stream_get_line($handle, 100000, "\n");
    $log_entry = parse_log_line($log_line);
    if ($log_entry) {
      if ($first_ts == null) {
        $first_ts = $log_entry['ts'];
      }
      $last_ts = $log_entry['ts'];
      $values[] = $log_entry;
      $val_count = count($values);

      if ($val_count == BATCH_SIZE) {
        log_(INFO, "About to insert batch #$batch_count [$val_count records]");
        insert_batch($dbcon, 'request', $values);
        $values = array();
        $batch_count++;
      }
    }
  }

  // Insert any remaining rows after reaching EOF.
  if ($values) {
    $val_count = count($values);
    log_(INFO, "About to insert final batch #$batch_count [$val_count records]");
    insert_batch($dbcon, 'request', $values);
  }

  // Clean up file resources
  $final_offset = ftell($handle);
  fclose($handle);
  return array('offset'   => $final_offset,
               'first_ts' => $first_ts,
               'last_ts'  => $last_ts);
} // process_apache_log()


/**
 * Transforms a single line in the log file into an array of log parameters
 */
function parse_log_line($text)
{
  static $instance_types = array(
    'crm'     => 'prod',
    'crmtest' => 'test',
    'crmdev'  => 'dev'
  );

  if (empty($text)) {
    return null;
  }

  // A valid log line for Bluebird will have 12 space-delimited parts.
  $entry_parts = explode(' ', $text);
  if (count($entry_parts) != 12) {
    log_(ERROR, "Invalid log line: [$text]");
    return null;
  }

  $servername = $entry_parts[2];
  $server_parts = explode('.', $servername);
  $instance_name = $server_parts[0];
  if (!isset($instance_types[$server_parts[1]])) {
    // Three recognized Bluebird environments are: crm, crmtest, crmdev
    log_(WARN, "Unrecognized Bluebird environment [{$server_parts[1]}]");
    return null;
  }
  $instance_type = $instance_types[$server_parts[1]];
  $instance_id = (int)get_instance_id($instance_name);
  $request_parts = parse_url($entry_parts[10]);
  $request_path = $request_parts['path'];

  // We don't care about public files, accidental copy/paste, or static files
  if (preg_match('/(^\\/sites\\/|https?:|\\.(css|js|jpg|jpeg|gif|img|txt|ico|png|bmp|pdf|tif|tiff|oft|ttf|eot|woff|svg|svgz|doc|mp4|mp3)$)/i', $request_path)) {
    return null;
  }

  // Format the datetime by removing the [ ] and replacing the first :
  $datetime = DateTime::createFromFormat('d/M/Y:H:i:s O', substr($entry_parts[0], 1).' '.substr($entry_parts[1], 0, 5));

  // Note that location_id and url_id will be set by the trigger on
  // the REQUEST table.

  $res = array(
    'instance_id' => $instance_id,
    'trans_ip' => sprintf('%u', ip2long($entry_parts[3])),
    'response_time' => $entry_parts[4],
    'response_code' => $entry_parts[5],
    'transfer_rx' => $entry_parts[6],
    'transfer_tx' => $entry_parts[7],
    'method' => ltrim($entry_parts[9], '"'),
    'path' => $request_path,
    'query' => isset($request_parts['query']) ? $request_parts['query'] : '',
    'ts' => $datetime->format(DateTime::ISO8601)
  );
  return $res;
} // parse_log_line()


/**
 *  Finds the log files matching the config.input.base_path with
 *  support for log rotation via numerical suffix. Example:
 *
 *    /var/log/apache2/access.log
 *    /var/log/apache2/access.log.1
 *    /var/log/apache2/access.log.2
 *    /var/log/apache2/access.log.3
 *
 *  Files that do not end with a numerical suffix (for example, *.gz) are
 *  ignored.
 *
 *  Files are returned in order from newest to oldest by the numeric suffix.
 */
function get_source_files($config)
{
  $base_path = array_value('base_path', $config, '');
  if (!$base_path) {
    log_(ERROR, "Section [input] missing key: base_path");
    return false;
  }

  $files = glob($base_path.'*');
  $files = array_filter($files, function ($v) {
    return preg_match('/log(\.[0-9]+)?$/', $v);
  });
  $suffix_pos = strlen($base_path) + 1;
  usort($files, function($a, $b) use ($suffix_pos) {
    $anum = (int) substr($a, $suffix_pos);
    $bnum = (int) substr($b, $suffix_pos);
    return $bnum - $anum;
  });
  log_(DEBUG, "Found source files:\n".var_export($files,1));
  return $files;
} // get_source_files()


/**
 *  Uses the given parameters to fetch an existing instance. If one cannot be
 *  found, it creates a new one and returns that instead.
 *  The default 0 value points to the "Invalid CRM" entry.
 *  Otherwise, returns instanceID that was found or inserted.
 */
function get_instance_id($name)
{
  global $g_instance_cache, $dbcon;

  //log_(DEBUG, "Searching for instance_id for $name");
  $name = (string)$name;
  $instanceID = (int)array_value($name, $g_instance_cache, 0);
  if ($instanceID < 0) {
    log_(DEBUG, "Found no cached instanceID for $name; adding now");
    try {
      $sth = $dbcon->prepare(
        "INSERT INTO instance (install_class, servername, name) ".
        "VALUES ('prod', :servername, :instname);");
      $sth->execute(array(':servername'=>"$name.crm.nysenate.gov", ':instname'=>$name));
      $instanceID = $dbcon->lastInsertId();
    }
    catch (Exception $e) {
      log_(ERROR, "Could not store instance record for $name: ".$e->getMessage());
      $instanceID = false;
    }
  }
  //log_(DEBUG, "Final instance_id for $name=$ret");
  $g_instance_cache[$name] = (int)$instanceID;
  return $instanceID;
} // get_instance_id()

?>
