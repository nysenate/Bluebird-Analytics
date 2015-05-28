<?php

require(realpath(dirname(__FILE__).'/../lib/utils.php'));
require(realpath(dirname(__FILE__).'/../lib/summarize.php'));

$prog = $argv[0];
$g_log_level = WARN;
$g_log_file = null;

if ($argc < 3) {
  echo "Usage: $prog start_ts end_ts\n";
  echo "   where start_ts is either 'min' or an ISO8601 timestamp\n";
  echo "     and end_ts is either 'max' or an ISO8601 timestamp\n";
  exit(1);
}

$start_ts = $argv[1];
$end_ts = $argv[2];

///////////////////////////////
// Bootstrap the environment
///////////////////////////////
$config = load_config();
if ($config === false) {
  echo "Unable to load configuration.\n";
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
  echo "$prog: Unable to connect to database; check configuration\n";
  exit(1);
}

// Replace "min" timestamp with the minimum timestamp from the REQUEST table
if ($start_ts == 'min') {
  $result = $dbcon->query("select min(ts) from request");
  $start_ts = $result->fetch()[0];
}

// Replace "max" timestamp with the maximum timestamp from the REQUEST table
if ($end_ts == 'max') {
  $result = $dbcon->query("select max(ts) from request");
  $end_ts = $result->fetch()[0];
}

// Check the two timestamps for validity
$start_time = strtotime($start_ts);
if ($start_time === false) {
  echo "$prog: $start_ts: Start timestamp is not valid\n";
  exit(1);
}
else {
  $start_ts = date('Y-m-d H:i:s', $start_time);
}

$end_time = strtotime($end_ts);
if ($end_time === false) {
  echo "$prog: $end_ts: End timestamp is not valid\n";
  exit(1);
}
else {
  $end_ts = date('Y-m-d H:i:s', $end_time);
}

// Resummarize the required time period.
echo "About to summarize REQUEST records from [$start_ts] to [$end_ts]...\n";
summarize($dbcon, $start_ts, $end_ts);
echo "Finished the summarize operation\n";

?>
