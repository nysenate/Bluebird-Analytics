<?php
date_default_timezone_set('America/New_York');
require(realpath(dirname(__FILE__).'/../lib/utils.php'));

$IGNORED_STATS = "/^SSL_|^SLAVE_|^PERFORMANCE_|^HANDLER_|^COM_|^BINLOG_|^INNODB_B|^INNODB_D|^INNODB_H|^INNODB_L|^INNODB_O|^INNODB_P|^INNODB_T|^COMPRESSION|^TC_|^OPENED_TABLE|^PREPARED_STMT|^RPL_STATUS|^ABORTED_CL|^KEY_|^LAST_QUERY_COST|^FLUSH_|^NOT_FLUSHED|^SORT_|^SELECT_|^UPTIME_|^QCACHE_/i";
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
$final_ctime = Date('U');
$out = $current = $previous = array();

if ($dbcon === false)
{
  echo "Could not connect to DB server\n";
  exit(1);
}


$now = time();
$time = $now - ($now % 60);

$result = $dbcon->query("SELECT * FROM mysql ORDER BY time DESC LIMIT 1");
$old_entry = $result->fetch(PDO::FETCH_ASSOC);

$new_entry = array('time'=>date("Y-m-d H:i:s", $time));
$result = $dbcon->query("SELECT * from information_schema.SESSION_STATUS");
while($stat = $result->fetch(PDO::FETCH_ASSOC)) {
  if (!preg_match($IGNORED_STATS, $stat['VARIABLE_NAME'])) {
    $new_entry[strtolower($stat['VARIABLE_NAME'])] = $stat['VARIABLE_VALUE'];
  }
}

insert_batch($dbcon, 'mysql', array($new_entry));

if (!$old_entry) {
  echo "first entry! Exiting.. \n";
  exit(0);
}

$rows = array();
$summary_keys = array_keys($old_entry);
$summary_keys[array_search('uptime', $summary_keys)] = 'availability';
$time_diff = $time - strtotime($old_entry['time']);
$start = strtotime($old_entry['time']);
while (($start+60) < $time) {
  // Insert blank entries for the missed time.
  $row = array_combine($summary_keys, array_fill(0, count($summary_keys), '?'));
  $row['time'] = date("Y-m-d H:i:s", $start);
  $rows[] = $row;
}

// Construct our new non-blank summary entry
if ($new_entry['uptime'] < $time_diff) {
  // There was server downtime, the new values must be for this minute
  $row = $new_entry;
}
else {
  $row = array();
  foreach (array_keys($new_entry) as $key) {
    $row[$key] = $new_entry[$key] - $old_entry[$key];
  }
}

$row['time'] = date("Y-m-d H:i:s", $start);
$row['availability'] = $row['uptime'] > 60 ? 1.00 : $row['uptime']/60.0;
unset($row['uptime']);
$rows[] = $row;
insert_batch($dbcon, 'mysql_1m', $rows);


function summarize($dbcon, $start_time, $end_time)
{
  // Go all the way back to the last possible block that there could be new data for.
  // Only process blocks on the even, e.g. 1:00, 1:15, 1:30
  // Don't process blocks that end before our offical start time
  $start_range = strtotime('today', $start_time);
  while ($start_range < $end_time) {
    $mysql_date = date("Y-m-d H:i:s", $start_range);

    // Process the 15 minute block every 15 minutes
    $end_range = $start_range + 900;
    if ($start_range % 900 == 0 && $end_range > $start_time) {
      create_summary_entries($dbcon, $mysql_date, '15m', $start_range, $end_range);
    }

    // Process the hour block every hour
    $end_range = $start_range + 3600;
    if ($start_range % 3600 == 0 && $end_range > $start_time) {
      create_summary_entries($dbcon, $mysql_date, '1h', $start_range, $end_range);
    }

    // Process the day block every day
    $end_range = strtotime('today', $start_range + 86400);
    if (strtotime('today', $start_range) == $start_range && $end_range > $start_time) {
      create_summary_entries($dbcon, $mysql_date, '1d', $start_range, $end_range);
    }

    // Step up minute by minute
    $start_range += 60;
  }
}


function create_summary_entries($dbcon, $mysql_date, $table_suffix, $start_range, $end_range) {
  $dbcon->query("
    INSERT INTO mysql_$table_suffix VALUES
      SELECT
        '$mysql_date',
        sum(aborted_connects),
        sum(bytes_received),
        sum(bytes_sent),
        sum(connections),
        sum(created_tmp_disk_tables),
        sum(created_tmp_files),
        sum(created_tmp_tables),
        sum(delayed_errors),
        sum(delayed_insert_threads),
        sum(delayed_writes),
        sum(innodb_row_lock_current_waits),
        sum(innodb_row_lock_time),
        sum(innodb_row_lock_time_avg),
        sum(innodb_row_lock_time_max),
        sum(innodb_row_lock_waits),
        sum(innodb_rows_deleted),
        sum(innodb_rows_inserted),
        sum(innodb_rows_read),
        sum(innodb_rows_updated),
        sum(max_used_connections),
        sum(open_files),
        sum(open_streams),
        sum(open_table_definitions),
        sum(open_tables),
        sum(opened_files),
        sum(queries),
        sum(questions),
        sum(slow_launch_threads),
        sum(slow_queries),
        sum(table_locks_immediate),
        sum(table_locks_waited),
        sum(threads_cached),
        sum(threads_connected),
        sum(threads_created),
        sum(threads_running),
        sum(availability)/count(*),
      FROM mysql_1m
      WHERE time BETWEEN FROM_UNIXTIME($start_range) AND FROM_UNIXTIME($end_range)"
  );
}
