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

$g_log_file = get_log_file($config['debug']);
$g_log_level = get_log_level($config['debug']);
$dbcon = get_db_connection($config['database']);
if ($dbcon === false) {
  exit(1);
}

///////////////////////////////
// Script specific setup
///////////////////////////////
global $INSTANCE_CACHE;
$INSTANCE_CACHE = array();

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

// Get the time and final offset of the last run. These values are also the default
// final values in case there was no new data to run. The schema automatically inserts
// a default 0/0 entry in this table so it will always have atleast 1 result row.
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
function process_apache_log($source_path, $offset, $dbcon)
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
}

/**
 * Transforms a single line in the log file into an array of request parameters.
 */
function process_entry($entry_parts, $dbcon)
{
  // Format the datetime by removing the [ ] and replacing the first :
  $datetime = DateTime::createFromFormat("d/M/Y:H:i:s O", substr($entry_parts[0],1)." ".substr($entry_parts[1], 0, 5));

  $servername = $entry_parts[2];
  $server_parts = explode('.', $servername);
  $instance_name = $server_parts[0];
  if (!isset($GLOBALS['INSTANCE_TYPE'][$server_parts[1]])) {
    return null;
  }
  $instance_type = $GLOBALS['INSTANCE_TYPE'][$server_parts[1]];
  $instance = get_or_create_instance($dbcon, $servername, $instance_type, $instance_name);
  $remote_ip = $entry_parts[3];
  $remote_location = ip_match($entry_parts[3]);
  $response_time = $entry_parts[4];
  $response_code = $entry_parts[5];
  $transfer_rx = $entry_parts[6];
  $transfer_tx = $entry_parts[7];
  $connection_status = $entry_parts[8];
  $method = trim($entry_parts[9], '"');

  $request_string = $entry_parts[10];
  $request_parts = parse_url($request_string);
  $request_path = $request_parts['path'];
  $request_query = isset($request_parts['query']) ? $request_parts['query'] : "";
  $request_protocol = $entry_parts[11];

  // We don't care about public files, accidental copy/pasta, or static files
  $is_page = !preg_match('/(^\\/sites\\/|https?:|\\.(css|js|jpg|jpeg|gif|img|txt|ico|png|bmp|pdf|tif|tiff|oft|ttf|eot|woff|svg|svgz|doc|mp4|mp3)$)/i', $request_path);

  return array(
    "id" => NULL,
    "instance_id" => $instance['id'],
    "remote_ip" => $remote_ip,
    //"remote_location" => $remote_location,
    "response_code" => $response_code,
    "response_time" => $response_time,
    "transfer_rx" => $transfer_rx,
    "transfer_tx" => $transfer_tx,
    "method" => $method,
    "path" => $request_path,
    "query" => $request_query,
    "time" => $datetime->format(DateTime::ISO8601),
    "is_page" => $is_page
  );


}

function ip_match($ip)
{

$location = ip2long($ip);

$locations =
  array(
    '0' =>
      array('name' => "LOB Fl B3", "range_start" => "10.11.3.26", "range_end" => "10.11.3.254"),
    '1' =>
      array('name' => "LOB Fl B2", "range_start" => "10.12.3.26", "range_end" => "10.12.3.254"),
    '2' =>
      array('name' => "LOB Fl 1", "range_start" => "10.13.4.26", "range_end" => "10.13.5.254"),
    '3' =>
      array('name' => "LOB Fl 2", "range_start" => "10.14.3.26", "range_end" => "10.14.3.254"),
    '4' =>
      array('name' => "LOB Fl 3", "range_start" => "10.15.3.26", "range_end" => "10.15.3.254"),
    '5' =>
      array('name' => "LOB Fl 4", "range_start" => "10.16.3.26", "range_end" => "10.16.3.254"),
    '6' =>
      array('name' => "LOB Fl 5", "range_start" => "10.17.3.26", "range_end" => "10.17.3.254"),
    '7' =>
      array('name' => "LOB Fl 6", "range_start" => "10.18.3.26", "range_end" => "10.18.3.254"),
    '8' =>
      array('name' => "LOB Fl 7", "range_start" => "10.19.3.26", "range_end" => "10.19.3.254"),
    '9' =>
      array('name' => "LOB Fl 8", "range_start" => "10.20.4.26", "range_end" => "10.20.5.254"),
    '10' =>
      array('name' => "LOB Fl 9", "range_start" => "10.21.4.26", "range_end" => "10.21.5.254"),
    '11' =>
      array('name' => "LOB 250 Broadway", "range_start" => "10.28.3.26", "range_end" => "10.28.3.26"),
    '12' =>
      array('name' => "A.E.S. Fl 13", "range_start" => "10.23.3.26", "range_end" => "10.23.3.254"),
    '13' =>
      array('name' => "A.E.S. Fl 14", "range_start" => "10.23.4.26", "range_end" => "10.23.4.254"),
    '14' =>
      array('name' => "A.E.S. Fl 15", "range_start" => "10.23.5.26", "range_end" => "10.23.5.254"),
    '15' =>
      array('name' => "A.E.S. Fl 16", "range_start" => "10.23.6.26", "range_end" => "10.23.6.254"),
    '16' =>
      array('name' => "A.E.S. Fl 24", "range_start" => "10.23.7.26", "range_end" => "10.23.7.254"),
    '17' =>
      array('name' => "A.E.S. Fl 25", "range_start" => "10.23.8.26", "range_end" => "10.23.8.254"),
    '18' =>
      array('name' => "A.E.S. Fl 26", "range_start" => "10.23.9.26", "range_end" => "10.23.9.254"),
    '19' =>
      array('name' => "A.E.S. Basement", "range_start" => "10.23.10.26", "range_end" => "10.23.10.254"),
    '20' =>
      array('name' => "Corporate Woods", "range_start" => "10.31.3.26", "range_end" => "10.31.3.254"),
    '21' =>
      array('name' => "Capitol West", "range_start" => "10.24.4.26", "range_end" => "10.24.5.254"),
    '22' =>
      array('name' => "Capitol East Fl 3", "range_start" => "10.25.3.26", "range_end" => "10.25.3.254"),
    '23' =>
      array('name' => "Capitol East Fl 4", "range_start" => "10.25.4.26", "range_end" => "10.25.4.254"),
    '24' =>
      array('name' => "Capitol East Fl 5", "range_start" => "10.25.5.26", "range_end" => "10.25.5.254"),
    '25' =>
      array('name' => "Agency-4 Fl 2 & Fl 11", "range_start" => "10.26.3.26", "range_end" => "10.26.3.254"),
    '26' =>
      array('name' => "Agency-4 Fl 16 & Fl 17", "range_start" => "10.26.4.26", "range_end" => "10.26.4.254"),
    '27' =>
      array('name' => "Agency-4 Fl 18", "range_start" => "10.26.5.26", "range_end" => "10.26.5.254"),
    '28' =>
      array('name' => "Satellite Offices", "range_start" => "10.99.1.0", "range_end" => "10.99.40.0"),
    '29' =>
      array('name' => "VPN User", "range_start" => "10.99.96.0", "range_end" => "10.99.96.255"),
    '30' =>
      array('name' => "VPN Sfms", "range_start" => "10.99.97.241", "range_end" => "10.99.97.254"),
    '31' =>
      array('name' => "VPN Telecom Vendor", "range_start" => "10.99.97.230", "range_end" => "10.99.97.239"),
    '32' =>
      array('name' => "VPN Asax", "range_start" => "10.99.98.0", "range_end" => "10.99.98.255"),
    '33' =>
      array('name' => "District Offices", "range_start" => "10.41.0.0", "range_end" => "10.41.255.255"),
    '34' =>
      array('name' => "District Offices", "range_start" => "10.42.0.0", "range_end" => "10.42.255.255"),
    '35' =>
      array('name' => "District Offices", "range_start" => "172.18.0.0", "range_end" => "172.18.255.255"),
    '36' =>
      array('name' => "District Offices", "range_start" => "172.28.0.0", "range_end" => "172.28.255.255"),
    '37' =>
      array('name' => "District Offices Visitor", "range_start" => "172.19.0.0", "range_end" => "172.19.255.255"),
    '38' =>
      array('name' => "District Offices Visitor", "range_start" => "172.29.0.0", "range_end" => "172.29.255.255"),
    '39' =>
      array('name' => "Wireless", "range_start" => "172.29.0.0", "range_end" => "172.29.255.255"),
    '40' =>
      array('name' => "Wireless LOB", "range_start" => "10.3.12.0", "range_end" => "10.3.12.255"),
    '41' =>
      array('name' => "Wireless Agency-4", "range_start" => "10.3.13.0", "range_end" => "10.3.13.255"),
    '42' =>
      array('name' => "Wireless A.E.S.", "range_start" => "10.3.14.0", "range_end" => "10.3.14.255"),
    '43' =>
      array('name' => "Wireless Capitol", "range_start" => "10.3.15.0", "range_end" => "10.3.15.255"),
    '44' =>
      array('name' => "Wireless C.Woods", "range_start" => "10.3.16.0", "range_end" => "10.3.16.255"),
    '45' =>
      array('name' => "Wireless District Offices", "range_start" => "10.3.17.0", "range_end" => "10.3.17.255"),
    '46' =>
      array('name' => "Wireless LOB-Top-Fls", "range_start" => "10.3.18.0", "range_end" => "10.3.18.255"),
    '47' =>
      array('name' => "Wireless Visitor", "range_start" => "10.99.70.0", "range_end" => "10.99.71.254"),
    '48' =>
      array('name' => "Wireless Visitor LOB", "range_start" => "10.99.72.0", "range_end" => "10.99.72.255"),
    '49' =>
      array('name' => "Wireless Visitor Agency-4", "range_start" => "10.99.73.0", "range_end" => "10.99.73.255"),
    '50' =>
      array('name' => "Wireless Visitor A.E.S.", "range_start" => "10.99.74.0", "range_end" => "10.99.74.255"),
    '51' =>
      array('name' => "Wireless Visitor Capitol", "range_start" => "10.99.75.0", "range_end" => "10.99.75.255"),
    '52' =>
      array('name' => "Wireless Visitor C.Woods", "range_start" => "10.99.76.0", "range_end" => "10.99.76.255"),
    '53' =>
      array('name' => "Wireless Visitor District Offices", "range_start" => "10.99.77.0", "range_end" => "10.99.77.255"),
    '54' =>
      array('name' => "Wireless Visitor LOB-Top-Fls", "range_start" => "10.99.78.0", "range_end" => "10.99.78.255"),
    '55' =>
      array('name' => "Serverfarm 1", "range_start" => "10.1.3.1", "range_end" => "10.1.3.30"),
    '56' =>
      array('name' => "Serverfarm 1", "range_start" => "10.1.4.1", "range_end" => "10.1.4.254"),
    '57' =>
      array('name' => "Serverfarm 2", "range_start" => "10.1.3.33", "range_end" => "10.1.3.62"),
    '58' =>
      array('name' => "Serverfarm 2", "range_start" => "10.1.5.1", "range_end" => "10.1.5.254"),
    '59' =>
      array('name' => "Serverfarm 3", "range_start" => "10.2.3.1", "range_end" => "10.2.3.30"),
    '60' =>
      array('name' => "Serverfarm 3", "range_start" => "10.1.6.1", "range_end" => "10.1.6.254"),
    '61' =>
      array('name' => "Serverfarm 4", "range_start" => "10.2.3.33", "range_end" => "10.2.3.62"),
    '62' =>
      array('name' => "Serverfarm 4", "range_start" => "10.1.7.1", "range_end" => "10.1.7.254"),
    '63' =>
      array('name' => "Serverfarm 5", "range_start" => "10.2.3.65", "range_end" => "10.2.3.126"),
    '64' =>
      array('name' => "AVAYA", "range_start" => "10.1.3.129", "range_end" => "10.1.3.254"),
    '65' =>
      array('name' => "AVAYA", "range_start" => "10.1.8.1", "range_end" => "10.1.8.254"),

  );

foreach ($locations as $id => $value) {
  if ($ip < $value['range_end'] && $ip > $value['range_start'] ) {
    $Location = $value['name'];
    

   return $Location;
        }
    }
}


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
}


/**
 *  Uses the given parameters to fetch an existing instance. If one cannot be found,
 *  it creates a new one and returns that instead.
 */
function get_or_create_instance($dbcon, $servername, $install_class, $name)
{
  // Check our cache first
  global $INSTANCE_CACHE;
  if (array_key_exists($servername, $INSTANCE_CACHE)) {
    return $INSTANCE_CACHE[$servername];
  }

  // Then check the database
  $result = $dbcon->query("SELECT * FROM instance WHERE servername = '$servername';");
  $row = $result->fetch(PDO::FETCH_ASSOC);
  if ($row) {
    return $row;
  }

  // Save a new instance if necessary
  $dbcon->exec("INSERT INTO instance (install_class, servername, name) VALUES ('$install_class', '$servername', '$name');");
  $instance = array(
    'id' => $dbcon->lastInsertId(),
    'servername' => $servername,
    'install_class' => $install_class,
    'name' => $name
  );

  $INSTANCE_CACHE[$servername] = $instance;
  return $instance;
}

?>
