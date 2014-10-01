<?php
date_default_timezone_set('America/New_York');
require(realpath(dirname(__FILE__).'/../lib/utils.php'));
require(realpath(dirname(__FILE__).'/../lib/summarize.php'));

///////////////////////////////
// Bootstrap the environment
///////////////////////////////
$config = load_config();
if ($config === false) {
  exit(1);
}

global $INSTANCE_CACHE, $dbcon;

$g_log_file = get_log_file($config['debug']);
$g_log_level = get_log_level($config['debug']);
$dbcon = get_db_connection($config['database']);
if ($dbcon === false) {
  exit(1);
}

///////////////////////////////
// Script specific setup
///////////////////////////////

/****************
 * create the instance cache
 */
$INSTANCE_CACHE = load_bluebird_instances($config);
// match to the IDs in the instance table
$result = $dbcon->query("SELECT id,name FROM instance");
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
  if (array_key_exists($row['name'],$INSTANCE_CACHE)) {
    $INSTANCE_CACHE[$row['name']]['id']=$row['id'];
  }
}

$INSTANCE_TYPE = array(
  'crm'     => 'prod',
  'crmtest' => 'test',
  'crmdev'  => 'dev'
);

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
$result = $dbcon->query("SELECT * FROM apache_cron_runs ORDER BY final_ctime DESC LIMIT 1");
$row = $result->fetch(PDO::FETCH_ASSOC);
$final_offset = $start_offset = $row['final_offset'];
$final_ctime = $start_ctime = strtotime($row['final_ctime']);
echo "Last run ended at ".DateTime::createFromFormat('U', $start_ctime)->format(DateTime::ISO8601)." offset $start_offset\n";


// Process log files that have been updated since the last run. Use >= here so that we
// catch files that were rotated immediately after our last run. Otherwise we might apply
// the byte offset to a new, unrelated file.
foreach($source_paths as $source_path) {
  if (filemtime($source_path) >= $start_ctime) {
    $start = microtime(true);
    echo "Running: $source_path\n";
    list($final_offset, $final_ctime) = process_apache_log($source_path, $start_offset, $dbcon);
    echo "Inserting Requests took: ".(microtime(true)-$start)."s\n";
    $start_offset = 0;

    // Save the run state so we can easily resume. But only if we actually processed a log!
    if ($final_ctime != null) {
      $dbcon->exec("INSERT INTO apache_cron_runs VALUES ($final_offset, FROM_UNIXTIME($final_ctime))");
    }
  }
}


/**
 *  Opens the given log at the given byte offset and inserts the remaining records into the
 *  database. Returns the final log entry time and byte offset so that future runs can avoid
 *  reprocessing the same entries.
 */
function process_apache_log($source_path, $offset, PDO $dbcon)
{
  $handle = fopen($source_path, "r");
  if ($handle === false) {
    log_(ERROR, "Source file '$source_path' cannot be opened for reading.");
    return false;
  }

  // Starting from where we left off and process new entries.
  echo "Reading '$source_path' [size:".filesize($source_path)."] from offset '$offset'\n";
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
  while(true) {
    $log_entry = stream_get_line($handle, 100000,"\n");
    $entry_parts = explode(' ', $log_entry);
    if (count($entry_parts) == 12) {
      $new_entry = process_entry($entry_parts, $dbcon);
      if ($new_entry == null) {
        // There was invalid log line content
      } elseif (!$new_entry['is_page']) {
        // Not a page load we are concerned with.
      }
      else {
        if ($start_ctime == null) {
          $start_ctime = strtotime($new_entry['time']);
        }
        unset($new_entry['is_page']);
        $final_ctime = strtotime($new_entry['time']);
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
  echo "Generating summaries for requests from ".date("Y-m-d H:i:s", $start_ctime)." to ".date("Y-m-d H:i:s", $final_ctime)."\n";
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
  // Format the datetime by removing the [ ] and replacing the first :
  $datetime = DateTime::createFromFormat('d/M/Y:H:i:s O', substr($entry_parts[0],1).' '.substr($entry_parts[1], 0, 5));

  $servername = $entry_parts[2];
  $server_parts = explode('.', $servername);
  $instance_name = $server_parts[0];
  if (!isset($GLOBALS['INSTANCE_TYPE'][$server_parts[1]])) {
    return null;
  }
  $instance_type = $GLOBALS['INSTANCE_TYPE'][$server_parts[1]];
  $instance_id = (int)get_instance_id($instance_name);
  $request_parts = parse_url($entry_parts[10]);
  $request_path = $request_parts['path'];

  // We don't care about public files, accidental copy/paste, or static files
  $is_page = !preg_match('/(^\\/sites\\/|https?:|\\.(css|js|jpg|jpeg|gif|img|txt|ico|png|bmp|pdf|tif|tiff|oft|ttf|eot|woff|svg|svgz|doc|mp4|mp3)$)/i', $request_path);

  // Note that location_id and url_id will be set by the trigger on
  // the REQUEST table.

  return array(
    'id' => NULL,
    'instance_id' => $instance_id,
    'remote_ip' => $entry_parts[3],
    'response_code' => $entry_parts[5],
    'response_time' => $entry_parts[4],
    'transfer_rx' => $entry_parts[6],
    'transfer_tx' => $entry_parts[7],
    'method' => trim($entry_parts[9], '"'),
    'path' => $request_path,
    'query' => isset($request_parts['query']) ? $request_parts['query'] : '',
    'time' => $datetime->format(DateTime::ISO8601),
    'is_page' => $is_page
  );
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
  if (!isset($config['base_path'])) {
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
  return array_reverse($files);
} // get_source_files()


/**
 *  Uses the given parameters to fetch an existing instance. If one cannot be
 *  found, it creates a new one and returns that instead.
 */
function get_instance_id($name)
{
  global $INSTANCE_CACHE,$dbcon;

  $ret = false;
  $name = (string)$name;
  if ($name && array_key_exists($name,$INSTANCE_CACHE)) {
    $ret = (int)array_value('id', $INSTANCE_CACHE[$name], -1);
    if ($ret < 0) {
      $sth = $dbcon->prepare("INSERT INTO instance (install_class, servername, name) VALUES " .
                            "('prod', :servername, :instname);");
      $sth->execute(array(':servername'=>"{$name}.crm.nysenate.gov", ':instname'=>$name));
      $ret = $INSTANCE_CACHE[$name]['id'] = $dbcon->lastInsertId();
    }
  }
  return $ret;
} // get_instance_id()

?>
