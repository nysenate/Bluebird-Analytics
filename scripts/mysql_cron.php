<?php
$host  = 'localhost';
$user = 'root';
$password = 'stefan';
$db  = 'httpd_logs';
$port  = 8889;
$output = array();
$important = array();

$mysqli = new mysqli($host,$user,$password,$db);
if ($mysqli->connect_errno)
{
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}

$statsQuery = 'SELECT * from information_schema.SESSION_STATUS';
if ($Result = $mysqli->query($statsQuery))
{
  if($Result->num_rows > 0)
  {
    while($sub = $Result->fetch_object()){
      // $output[$sub->VARIABLE_NAME]= floatval($sub->VARIABLE_VALUE);
      $output[$sub->VARIABLE_NAME]= $sub->VARIABLE_VALUE;

    }
  }
}

// Clean out the stuff we don't want
foreach($output as $key => $value)
{
     if(preg_match("/^SSL_|^SLAVE_|^PERFORMANCE_|^HANDLER_|^COM_|^BINLOG_|^INNODB_|^COMPRESSION|^TC_|^OPENED_TABLE|^PREPARED_STMT|^RPL_STATUS|^ABORTED_CL|^KEY_|^LAST_QUERY_COST|^FLUSH_|^NOT_FLUSHED|^SORT_|^SELECT_|^UPTIME_/i",$key))
     {
          unset($output[$key]);
     }
}
// echo "<h1>".count($output)."</h1>";
echo "<pre>";


function secondsToTime($seconds) {
    $dtF = new DateTime('UTC');
    $dtT = clone $dtF;
    $dtT->modify("+$seconds seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

$output['_UPTIME_STRING'] = secondsToTime($output['UPTIME']);

print_r($output);

##############################################
### Possibly important reports ###############
// Aborted_connects
// Bytes_received
// Bytes_sent
// Connections
// Created_tmp_disk_tables
// Created_tmp_files
// Created_tmp_tables
// Flush_commands
// Last_query_cost
// Max_used_connections
// Open_files
// Open_streams
// Open_table_definitions
// OPEN_TABLES
// Opened_files
// Qcache_free_memory
// Qcache_hits
// Qcache_queries_in_cache
// Queries
// Questions
// Slow_launch_threads
// Slow_queries
// Threads_cached
// Threads_connected
// Threads_created
// Threads_running
// Uptime

##############################################
### Built in Simple stats string #############

// $string = mysqli_stat($mysqli);
// print_r($string);
// Uptime: 00
// Threads: 00
// Questions: 00
// Slow queries: 00
// Opens: 00
// Flush tables: 00
// Open tables: 00
// Queries per second avg: 00

mysqli_close($mysqli);
