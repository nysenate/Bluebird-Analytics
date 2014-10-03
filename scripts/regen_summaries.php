<?php
date_default_timezone_set('America/New_York');
require(realpath(dirname(__FILE__).'/../lib/utils.php'));
require(realpath(dirname(__FILE__).'/../lib/summarize.php'));

///////////////////////////////
// Bootstrap the environment
///////////////////////////////
$config = load_config();
if ($config === false) {
  echo "Unable to load configuration.\n";
  exit(1);
}

$g_log_file = get_log_file($config['debug']['file']);
$g_log_level = $config['debug']['level'];

$dbcon = get_db_connection($config['database']);
if ($dbcon === false) {
  echo "Unable to connect to database, check configuration\n";
  exit(1);
}

// Get the full requests time range.
$result = $dbcon->query("select min(time), max(time) from request");
list($start_date, $end_date) = $result->fetch();

// Resummarize the required time period.
echo "Now resumarizing $start_date, $end_date";
summarize($dbcon, strtotime($start_date), strtotime($end_date));

?>
