<?php

function summarize($dbcon, $start_time, $end_time)
{
  // Go all the way back to the last possible block that there could be new data for.
  // Only process blocks on the even, e.g. 1:00, 1:15, 1:30
  // Don't process blocks that end before our offical start time
  $start_range = strtotime('today', $start_time);
  while ($start_range < $end_time) {
    $mysql_date = date("Y-m-d H:i:s", $start_range);

    // Process the minute block
    $end_range = $start_range + 60;
    if ($end_range > $start_time) {
      create_summary_entries($dbcon, $mysql_date, '1m', $start_range, $end_range);
      create_uniques_entries($dbcon, $mysql_date, '1m', $start_range, $end_range);
    }

    // Process the 15 minute block every 15 minutes
    $end_range = $start_range + 900;
    if ($start_range % 900 == 0 && $end_range > $start_time) {
      create_summary_entries($dbcon, $mysql_date, '15m', $start_range, $end_range);
      create_uniques_entries($dbcon, $mysql_date, '15m', $start_range, $end_range);
    }

    // Process the hour block every hour
    $end_range = $start_range + 3600;
    if ($start_range % 3600 == 0 && $end_range > $start_time) {
      create_summary_entries($dbcon, $mysql_date, '1h', $start_range, $end_range);
      create_uniques_entries($dbcon, $mysql_date, '1h', $start_range, $end_range);
    }

    // Process the day block every day
    $end_range = $start_range + 86400;
    if (strtotime('today', $start_range) == $start_range && $end_range > $start_time) {
      create_summary_entries($dbcon, $mysql_date, '1d', $start_range, $end_range);
      create_uniques_entries($dbcon, $mysql_date, '1d', $start_range, $end_range);
    }

    // Step up minute by minute
    $start_range += 60;
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
  $result = $dbcon->prepare("DELETE FROM summary_$table_suffix WHERE time=?");
  $result->execute(array($mysql_date));
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
  $result = $dbcon->prepare("DELETE FROM uniques_$table_suffix WHERE time=?");
  $result->execute(array($mysql_date));
  insert_batch($dbcon, "uniques_$table_suffix", $rows);
}

?>
