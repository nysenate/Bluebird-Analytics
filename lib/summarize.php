<?php

function summarize(PDO $dbcon, $first_ts, $last_ts)
{
  // Go back to the last possible block for which there could be new data.
  // Only process blocks on certain increments (e.g. 1:00, 1:15, 1:30)
  // Don't process blocks that end before our offical start time
  // Note: strtotime('today', $time) is midnight on the date specified by $time
  $start_time = strtotime($first_ts);
  $end_time = strtotime($last_ts);
  $start_range = strtotime('today', $start_time);

  while ($start_range < $end_time) {
    $start_ts = date('Y-m-d H:i:s', $start_range);

    // Process the minute block
    $end_range = $start_range + 60;
    if ($end_range > $start_time) {
      $end_ts = date('Y-m-d H:i:s', $end_range);
      create_summary_entries($dbcon, '1m', $start_ts, $end_ts);
      create_uniques_entries($dbcon, '1m', $start_ts, $end_ts);
    }

    // Process the 15 minute block every 15 minutes
    $end_range = $start_range + 900;
    if ($start_range % 900 == 0 && $end_range > $start_time) {
      $end_ts = date('Y-m-d H:i:s', $end_range);
      create_summary_entries($dbcon, '15m', $start_ts, $end_ts);
      create_uniques_entries($dbcon, '15m', $start_ts, $end_ts);
    }

    // Process the hour block every hour
    $end_range = $start_range + 3600;
    if ($start_range % 3600 == 0 && $end_range > $start_time) {
      $end_ts = date('Y-m-d H:i:s', $end_range);
      create_summary_entries($dbcon, '1h', $start_ts, $end_ts);
      create_uniques_entries($dbcon, '1h', $start_ts, $end_ts);
    }

    // Process the day block every day
    $end_range = $start_range + 86400;
    if (strtotime('today', $start_range) == $start_range && $end_range > $start_time) {
      $end_ts = date('Y-m-d H:i:s', $end_range);
      create_summary_entries($dbcon, '1d', $start_ts, $end_ts);
      create_uniques_entries($dbcon, '1d', $start_ts, $end_ts);
    }

    // Step up minute by minute
    $start_range += 60;
  }
} // summarize()


function create_summary_entries(PDO $dbcon, $table_suffix, $start_ts, $end_ts)
{
  $tabname = "summary_$table_suffix";
  log_(DEBUG, "Creating $tabname entries for range [$start_ts] to [$end_ts]");

  $rows = array();
  $result = $dbcon->query("
      SELECT instance_id, trans_ip, location_id, count(*) as page_views,
             IFNULL(sum(response_code = 503),0) as 503_errors,
             IFNULL(sum(response_code = 500),0) as 500_errors,
             IFNULL(sum(response_time), 0) as response_time
      FROM request
      WHERE ts BETWEEN '$start_ts' AND '$end_ts'
      GROUP BY instance_id, trans_ip");

  while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $row['ts'] = $start_ts;
    $rows[] = $row;
  }

  $result->closeCursor();
  $result = $dbcon->exec("DELETE FROM $tabname WHERE ts='$start_ts'");
  insert_batch($dbcon, $tabname, $rows);
} // create_summary_entries()


function create_uniques_entries(PDO $dbcon, $table_suffix, $start_ts, $end_ts)
{
  $tabname = "uniques_$table_suffix";
  log_(DEBUG, "Creating $tabname entries for range [$start_ts] to [$end_ts]");

  $rows = array();
  foreach (array('path') as $stat) {
    $result = $dbcon->query("
      SELECT instance_id, trans_ip, location_id, $stat as value
      FROM request
      WHERE ts BETWEEN '$start_ts' AND '$end_ts'
      GROUP BY instance_id, trans_ip, $stat");

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
      $row['ts'] = $start_ts;
      $row['type'] = $stat;
      $rows[] = $row;
    }
    $result->closeCursor();
  }
  $result = $dbcon->exec("DELETE FROM $tabname WHERE ts='$start_ts'");
  insert_batch($dbcon, $tabname, $rows);
} // create_uniques_entries()

?>
