<?php

// Define these constants prior to calling parse_ini_file().  That way, the
// function will translate the debug_level string into an integer value.
const FATAL = 0;
const ERROR = 1;
const WARN  = 2;
const INFO  = 3;
const DEBUG = 4;


function send_response($code, $message, $data=NULL)
{
  header("Content-Type: application/json; charset=UTF-8");
  http_response_code($code);
  echo json_encode(array(
      'code' => $code,
      'message' => $message,
      'data' => $data,
  ));
  exit(0);
}


function clean_string($input)
{
  return preg_replace('/[^-a-zA-Z0-9: _,.]/', '', $input);
}


/**
 *  Inserts a batch of rows of arbitrary size.
 */
function insert_batch($dbcon, $table, $rows)
{
  if (!empty($rows)) {
    $columns = implode(', ', array_keys($rows[0]));
    $place_holder = '('.implode(', ', array_fill(0, count($rows[0]), "?")).')';
    $place_holders = implode(', ', array_fill(0, count($rows), $place_holder));
    $stmt = $dbcon->prepare("INSERT INTO $table ($columns) VALUES $place_holders");

    // Extract value only arrays from each row and merge them into one big array for execution
    $values = array();
    foreach($rows as $row) {
      $values[] = array_values($row);
    }
    $final_values = call_user_func_array('array_merge', $values);
    $stmt->execute($final_values);
  }
}


function load_config()
{
  $config_file = realpath(dirname(__FILE__).'/../analytics.ini');
  if (in_array('BBSTATS_CONFIG', $_ENV)) {
    $config_file = $_ENV['config_file'];
  }

  $config = parse_ini_file($config_file, true);
  if (!$config) {
    log_(ERROR, "Configuration file not found at '$config_file'.");
    return FALSE;
  }

  foreach(array('database','input') as $section) {
    if (!array_key_exists($section, $config)) {
      log_(500,"Invalid config file. '$section' section required");
      return FALSE;
    }
  }

  return $config;
}


function log_($log_level, $message)
{
  global $g_debug_level, $g_log_file;
  echo "$message\n";

  //Get the integer level for each and ignore out of scope log messages
  if ($g_debug_level > $log_level) {
    return;
  }

  switch ($log_level) {
    case FATAL: $debug_level = 'FATAL'; break;
    case ERROR: $debug_level = 'ERROR'; break;
    case WARN: $debug_level = 'WARN'; break;
    case INFO: $debug_level = 'INFO'; break;
    case DEBUG: $debug_level = 'DEBUG'; break;
    default: $debug_level = $log_level; break;
  }
  $date = date('Y-m-d H:i:s');

  //Log to a debug file, or to Apache if debug file was not opened.
  if ($g_log_file) {
    fwrite($g_log_file, "$date [$debug_level] $message\n");
  }
  else {
    error_log("[statserver] $date [$debug_level] $message\n");
  }
}


function get_db_connection($dbconfig)
{
  //Validate the database configuration settings
  $required_keys = array('type','host','name','user','pass','port');
  if($missing_keys = array_diff_key(array_flip($required_keys), $dbconfig)) {
    $missing_key_msg = implode(', ',array_keys($missing_keys));
    log_(ERROR, "Section [database] missing keys: $missing_key_msg");
    return false;
  }

  try {
    $type = $dbconfig['type'];
    $host = $dbconfig['host'];
    $port = $dbconfig['port'];
    $user = $dbconfig['user'];
    $pass = $dbconfig['pass'];
    $name = $dbconfig['name'];
    return new PDO("$type:host=$host;port=$port;dbname=$name", $user, $pass, array(
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ));
  }
  catch (PDOException $e) {
    log_(FATAL, "PDOException:".$e->getMessage());
    return false;
  }
}


function get_log_level($config)
{
  $debug_level = WARN;  // default debug level is WARN
  if (isset($config['debug_level'])) {
    $debug_level_val = $config['debug_level'];
    if (is_numeric($debug_level_val)) {
      $debug_level = $debug_level_val;
    }
    else {
      error_log("[statserver] $debug_level_val: Invalid debug level");
    }
  }
  return $debug_level;
}


function get_log_file($config)
{
  $log_file = false;

  if (isset($config['log_file'])) {
    $filepath = $config['log_file'];
    $log_file = fopen($filepath, 'a');
    if (!$log_file) {
      error_log("[statserver] $filepath: Unable to open for writing");
    }
  }
  return $log_file;
}

?>
