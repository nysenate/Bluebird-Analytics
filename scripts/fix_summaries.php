<?php
date_default_timezone_set('America/New_York');
require(realpath(dirname(__FILE__).'/../lib/utils.php'));

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
  echo "Unable to connect to database, check configuration\n";
  exit(1);
}

// Get the time range that needs to be fixed.
$result = $dbcon->query("select time, count(*) as entry_count from summary_1d group by time, instance_id, remote_ip having entry_count > 1 order by time asc limit 1");
$start_date = $result->fetchColumn(0);
$result = $dbcon->query("select max(time) from request");
$end_date = $result->fetchColumn(0);

if ($end_date == null) {
  echo "No requests found, nothing to fix?\n";
  exit(1);
}

if ($start_date == null) {
  echo "No duplicate records found in 1 day sumarries, no fixes needed!\n";
  exit(1);
}

// Remove all the old entries.
$dbcon->query("delete from uniques_1h where time >= '$start_date'");
$dbcon->query("delete from uniques_1d where time >= '$start_date'");
$dbcon->query("delete from summary_1h where time >= '$start_date'");
$dbcon->query("delete from summary_1d where time >= '$start_date'");

// Add new primary keys to all summary tables.
$dbcon->query("alter table summary_1m add constraint PRIMARY KEY (time, instance_id, remote_ip)");
$dbcon->query("alter table summary_15m add constraint PRIMARY KEY (time, instance_id, remote_ip)");
$dbcon->query("alter table summary_1h add constraint PRIMARY KEY (time, instance_id, remote_ip)");
$dbcon->query("alter table summary_1d add constraint PRIMARY KEY (time, instance_id, remote_ip)");
$dbcon->query("alter table uniques_1m add constraint PRIMARY KEY (time, instance_id, remote_ip, type)");
$dbcon->query("alter table uniques_15m add constraint PRIMARY KEY (time, instance_id, remote_ip, type)");
$dbcon->query("alter table uniques_1h add constraint PRIMARY KEY (time, instance_id, remote_ip, type)");
$dbcon->query("alter table uniques_1d add constraint PRIMARY KEY (time, instance_id, remote_ip, type)");

// Resummarize the required time period.
summarize($dbcon, strtotime($start_date), strtotime($end_date));

function summarize($dbcon, $start_time, $end_time)
{
  // Go all the way back to the last possible block that there could be new data for.
  // Only process blocks on the even, e.g. 1:00, 1:15, 1:30
  // Don't process blocks that end before our offical start time
  $start_range = strtotime('today', $start_time);
  while ($start_range < $end_time) {
    $mysql_date = date("Y-m-d H:i:s", $start_range);

    // Process the hour block every hour
    $end_range = $start_range + 3600;
    if ($start_range % 3600 == 0 && $end_range > $start_time) {
      create_summary_entries($dbcon, $mysql_date, '1h', $start_range, $end_range);
      create_uniques_entries($dbcon, $mysql_date, '1h', $start_range, $end_range);
    }

    // Process the day block every day
    $end_range = strtotime('today', $start_range + 86400);
    if (strtotime('today', $start_range) == $start_range && $end_range > $start_time) {
      echo "Processing day of $mysql_date\n";
      create_summary_entries($dbcon, $mysql_date, '1d', $start_range, $end_range);
      create_uniques_entries($dbcon, $mysql_date, '1d', $start_range, $end_range);
    }

    // Step up hour by hour
    $start_range += 3600;
  }
}


function create_summary_entries($dbcon, $mysql_date, $table_suffix, $start_range, $end_range)
{
  $result = $dbcon->query("
      SELECT
        instance_id,
        remote_ip,
        count(*) as page_views,
        IFNULL(sum(response_code = 503),0) as 503_errors,
        IFNULL(sum(response_code = 500),0) as 500_errors,
        IFNULL(sum(response_time), 0) as response_time
      FROM request
      WHERE time BETWEEN FROM_UNIXTIME($start_range) AND FROM_UNIXTIME($end_range)
      GROUP BY instance_id, remote_ip
  ");
  $rows = array();
  while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $row['time'] = $mysql_date;
    $rows[] = $row;
  }
  $result->closeCursor();
  insert_batch($dbcon, "summary_$table_suffix", $rows);
}


function create_uniques_entries($dbcon, $mysql_date, $table_suffix, $start_range, $end_range)
{
  $rows = array();
  foreach(array('path') as $stat) {
    $result = $dbcon->query("
      SELECT instance_id, remote_ip, $stat as value
      FROM request
      WHERE time BETWEEN FROM_UNIXTIME($start_range) AND FROM_UNIXTIME($end_range)
      GROUP BY instance_id, remote_ip, $stat
    ");

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
      $row['time'] = $mysql_date;
      $row['type'] = $stat;
      $rows[] = $row;
    }
    $result->closeCursor();
  }
  insert_batch($dbcon, "uniques_$table_suffix", $rows);
}

?>
