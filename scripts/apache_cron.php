<?php

date_default_timezone_set('America/New_York');

require(realpath(dirname(__FILE__).'/../lib/utils.php'));
require(realpath(dirname(__FILE__).'/../lib/summarize.php'));

///////////////////////////////
// Bootstrap the environment
///////////////////////////////
$g_log_level = WARN;
$g_log_file = null;

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
  $result = $dbcon->query("SELECT id,name FROM instance");
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
  $result = $dbcon->query("SELECT * FROM apache_cron_runs ORDER BY final_ctime DESC LIMIT 1");
}
catch (Exception $e) {
  log_(FATAL,'Could not load cron run history! '.$e->getMessage());
  exit(1);
}

$row = $result->fetch(PDO::FETCH_ASSOC);
$final_offset = $start_offset = $row['final_offset'];
$final_ctime = $start_ctime = strtotime($row['final_ctime']);
log_(INFO, "Last run ended at ".date('Y-m-d H:i:s', $start_ctime)."; offset=$start_offset");


// Process log files that have been updated since the last run. Use >= here
// so that we catch files that were rotated immediately after our last run.
// Otherwise we might apply the byte offset to a new, unrelated file.
foreach ($source_paths as $source_path) {
  if (filemtime($source_path) >= $start_ctime) {
    $start = microtime(true);
    log_(INFO, "Running: $source_path");
    if (filesize($source_path) < $start_offset) {
      log_(WARN, "Reseting start offset due to under-sized file ($source_path)");
      log_(WARN, "(looking for $start_offset, filesize is ".filesize($source_path).")");
      $start_offset = 0;
    }
    list($final_offset, $final_ctime) = process_apache_log($source_path, $start_offset, $dbcon);
    log_(INFO, "Inserting Requests took: ".round(microtime(true)-$start,3)."s");
    $start_offset = 0;

    // Save the run state so we can easily resume. But only if we actually processed a log!
    if ($final_ctime != null) {
      try {
        $dbcon->exec("INSERT INTO apache_cron_runs VALUES ($final_offset, FROM_UNIXTIME($final_ctime))");
      }
      catch (Exception $e) {
        log_(ERROR,'Could not insert latest cron run: '.$e->getMessage());
      }
    }
  }
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
    log_(ERROR, "Source file '$source_path' cannot be opened for reading.");
    return false;
  }

  // Starting from where we left off and process new entries.
  log_(INFO, "Reading '$source_path' [size:".filesize($source_path)."] from offset '$offset'");
  fseek($handle, min($offset, filesize($source_path)));

  // Increase the insert time by deferring indexing and foreign key checks.
  $dbcon->beginTransaction();
  $dbcon->exec("SET foreign_key_checks=0;");

  // For every valid line in the rest of the file:
  //  * Get the insert values for that line
  //  * Do a periodic bulk insert
  //  * Track timestamp of the last entry
  $c = 1;
  $values = array();
  $start_ctime = null;
  $final_ctime = null;
  while (true) {
    $log_entry = stream_get_line($handle, 100000, "\n");
    $entry_parts = explode(' ', $log_entry);
    if (count($entry_parts) == 12) {
      $new_entry = process_entry($entry_parts, $dbcon);
      if ($new_entry == null) {
        // There was invalid log line content
      }
      elseif (!$new_entry['is_page']) {
        // Not a page load we are concerned with.
      }
      else {
        if ($start_ctime == null) {
          $start_ctime = strtotime($new_entry['ts']);
        }
        unset($new_entry['is_page']);
        $final_ctime = strtotime($new_entry['ts']);
        $values[] = $new_entry;
      }
    }
    else {
      // The log line format was invalid
    }

    // 100 seems to maximize insert speed for some reason.
    // Higher numbers like 1000 actually perform worse.
    // TOOD: Why would that be? MySQL Configuration issue?
    $is_eof = feof($handle);
    if (($c++ % 100) == 0 || $is_eof) {
      insert_batch($dbcon, 'request', $values);
      $values = array();
      $start = microtime(true);
      if ($is_eof) {
        break;
      }
    }
  }

  // Update all the summaries affected by this time range.
  log_(INFO, "Generating summaries for requests from ".date("Y-m-d H:i:s", $start_ctime)." to ".date("Y-m-d H:i:s", $final_ctime));
  summarize($dbcon, $start_ctime, $final_ctime);

  // Re-enable the foreign_key_checks and commit our work.
  $dbcon->exec("SET foreign_key_checks=1;");
  $dbcon->commit();

  // Clean up file resources
  $final_offset = ftell($handle);
  fclose($handle);
  return array($final_offset, $final_ctime);
} // process_apache_log()


/**
 * Transforms a single line in the log file into an array of request parameters.
 */
function process_entry($entry_parts, PDO $dbcon)
{
  static $instance_types = array(
    'crm'     => 'prod',
    'crmtest' => 'test',
    'crmdev'  => 'dev'
  );

  // Format the datetime by removing the [ ] and replacing the first :
  $datetime = DateTime::createFromFormat('d/M/Y:H:i:s O', substr($entry_parts[0],1).' '.substr($entry_parts[1], 0, 5));

  $servername = $entry_parts[2];
  $server_parts = explode('.', $servername);
  $instance_name = $server_parts[0];
  if (!isset($instance_types[$server_parts[1]])) {
    return null;
  }
  $instance_type = $instance_types[$server_parts[1]];
  $instance_id = (int)get_instance_id($instance_name);
  $request_parts = parse_url($entry_parts[10]);
  $request_path = $request_parts['path'];

  // We don't care about public files, accidental copy/paste, or static files
  $is_page = !preg_match('/(^\\/sites\\/|https?:|\\.(css|js|jpg|jpeg|gif|img|txt|ico|png|bmp|pdf|tif|tiff|oft|ttf|eot|woff|svg|svgz|doc|mp4|mp3)$)/i', $request_path);

  // Note that location_id and url_id will be set by the trigger on
  // the REQUEST table.

  $ret = array(
    'id' => NULL,
    'instance_id' => $instance_id,
    'trans_ip' => sprintf("%u", ip2long($entry_parts[3])),
    'response_code' => $entry_parts[5],
    'response_time' => $entry_parts[4],
    'transfer_rx' => $entry_parts[6],
    'transfer_tx' => $entry_parts[7],
    'method' => trim($entry_parts[9], '"'),
    'path' => $request_path,
    'query' => isset($request_parts['query']) ? $request_parts['query'] : '',
    'ts' => $datetime->format(DateTime::ISO8601),
    'is_page' => $is_page
  );
  log_(DEBUG, "Processing entry: ".var_export($ret,1));
  return $ret;
} // process_entry()


/**
 *  Finds the log files matching the config.input.base_path with
 *  support for log rotation via numerical suffix. Example:
 *
 *    /var/log/apache2/access.log
 *    /var/log/apache2/access.log.1
 *    /var/log/apache2/access.log.2
 *    /var/log/apache2/access.log.3
 *
 *  Files are returned in ordered from newest to oldest by the numeric suffix.
 */
function get_source_files($config)
{
  $base_path = array_value('base_path',$config,'');
  if (!$base_path) {
    log_(ERROR, "Section [input] missing keys: base_path");
    return false;
  }

  $base_path = $config['base_path'];
  $files = glob($base_path."*");
  usort($files, function($a, $b) use ($base_path) {
    $anum = (int) substr($a, strlen($base_path)+1);
    $bnum = (int) substr($b, strlen($base_path)+1);
    return $anum - $bnum;
  });
  log_(DEBUG, "Found source files:\n".var_export($files,1));
  return array_reverse($files);
} // get_source_files()


/**
 *  Uses the given parameters to fetch an existing instance. If one cannot be
 *  found, it creates a new one and returns that instead.
 */
function get_instance_id($name)
{
  global $g_instance_cache, $dbcon;

  log_(DEBUG, "Searching for instance_id for $name");
  $name = (string)$name;
  // the default 0 value points to the "Invalid CRM" entry.
  // otherwise, $ret= (integer id from database) | (-1 valid instance not in database)
  $ret = (int)array_value($name, $g_instance_cache, 0);
  if ($ret < 0) {
    log_(DEBUG, "Found no cache for $name (ret=$ret)");
    try {
      $sth = $dbcon->prepare("INSERT INTO instance (install_class, servername, name) VALUES " .
                            "('prod', :servername, :instname);");
      $sth->execute(array(':servername'=>"{$name}.crm.nysenate.gov", ':instname'=>$name));
      $ret = $dbcon->lastInsertId();
    }
    catch (Exception $e) {
      log_(ERROR,"Could not store instance record for $name: ".$e->getMessage());
      $ret = false;
    }
  }
  log_(DEBUG, "Final instance_id for $name=$ret");
  $g_instance_cache[$name] = (int)$ret;
  return $ret;
} // get_instance_id()

?>
