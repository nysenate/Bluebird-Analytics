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
$final_ctime = Date('U');
$out = $current = $previous = array();

if ($dbcon === false)
{
  echo "Could not connect to DB server\n";
  exit(1);
}

$result = $dbcon->query("SELECT * FROM mysql ORDER BY time DESC LIMIT 1");
$row = $result->fetch(PDO::FETCH_ASSOC);
$start_ctime = strtotime($row['final_ctime']);
$mysql_date = date("Y-m-d H:i:s", $final_ctime);

echo "Last MYSQL run ended at ".DateTime::createFromFormat('U', $start_ctime)->format(DateTime::ISO8601)."\n";

if ((intval($final_ctime) - $start_ctime) > 65) {
  // The oldest record is over 65 seconds old, we had some downtime
  echo "Last run was ".(intval($final_ctime) - $start_ctime)." seconds ago\n";
}

// get current stats
$result = $dbcon->query("SELECT * from information_schema.SESSION_STATUS");
while($sub = $result->fetch(PDO::FETCH_OBJ))
{
  $out[strtolower($sub->VARIABLE_NAME)] = $sub->VARIABLE_VALUE;
}

// Clean out the stuff we don't want
foreach($out as $key => $value)
{
  if(preg_match("/^SSL_|^SLAVE_|^PERFORMANCE_|^HANDLER_|^COM_|^BINLOG_|^INNODB_B|^INNODB_D|^INNODB_H|^INNODB_L|^INNODB_O|^INNODB_P|^INNODB_T|^COMPRESSION|^TC_|^OPENED_TABLE|^PREPARED_STMT|^RPL_STATUS|^ABORTED_CL|^KEY_|^LAST_QUERY_COST|^FLUSH_|^NOT_FLUSHED|^SORT_|^SELECT_|^UPTIME_|^QCACHE_/i",$key))
  {
    unset($out[$key]);
  }
}

$dbcon->exec("INSERT INTO mysql (aborted_connects, bytes_received, bytes_sent, connections, created_tmp_disk_tables, created_tmp_files, created_tmp_tables, delayed_errors, delayed_insert_threads, delayed_writes, innodb_row_lock_current_waits, innodb_row_lock_time, innodb_row_lock_time_avg, innodb_row_lock_time_max, innodb_row_lock_waits, innodb_rows_deleted, innodb_rows_inserted, innodb_rows_read, innodb_rows_updated, max_used_connections, open_files, open_streams, open_table_definitions, open_tables, opened_files, queries, questions, slow_launch_threads, slow_queries, table_locks_immediate, table_locks_waited, threads_cached, threads_connected, threads_created, threads_running, uptime, time) VALUES ({$out['aborted_connects']}, {$out['bytes_received']}, {$out['bytes_sent']}, {$out['connections']}, {$out['created_tmp_disk_tables']}, {$out['created_tmp_files']}, {$out['created_tmp_tables']}, {$out['delayed_errors']}, {$out['delayed_insert_threads']}, {$out['delayed_writes']}, {$out['innodb_row_lock_current_waits']}, {$out['innodb_row_lock_time']}, {$out['innodb_row_lock_time_avg']}, {$out['innodb_row_lock_time_max']}, {$out['innodb_row_lock_waits']}, {$out['innodb_rows_deleted']}, {$out['innodb_rows_inserted']}, {$out['innodb_rows_read']}, {$out['innodb_rows_updated']}, {$out['max_used_connections']}, {$out['open_files']}, {$out['open_streams']}, {$out['open_table_definitions']}, {$out['open_tables']}, {$out['opened_files']}, {$out['queries']}, {$out['questions']}, {$out['slow_launch_threads']}, {$out['slow_queries']}, {$out['table_locks_immediate']}, {$out['table_locks_waited']}, {$out['threads_cached']}, {$out['threads_connected']}, {$out['threads_created']}, {$out['threads_running']}, {$out['uptime']}, '{$mysql_date}' );");

