<?php

// Define these constants prior to calling parse_ini_file().  That way, the
// function will translate the [debug]->level string into an integer value.
const FATAL     = 0;
const ERROR     = 1;
const WARN      = 2;
const INFO      = 3;
const DEBUG     = 4;

const USE_FASTER_SINGLE_INSERT_STMT = true;


/**
 * Implements Python's dict.get() and CiviCRM's CRM_Utils_Array::value()
 * Check an array for a key.  If the key exists, return its value.
 * Otherwise, return a default value.
 */
function array_value($key, $array, $default_value = null)
{
  return (is_array($array) && array_key_exists($key,$array)) ? $array[$key] : $default_value;
} // array_value()


// NOTE: This function exits the entire script intentionally.
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
} // send_response()


function clean_string($input)
{
  return preg_replace('/[^-a-zA-Z0-9: _,.]/', '', $input);
}

function convert($size)
{
  $unit=array('b','kb','mb','gb','tb','pb');
  return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
} // convert()


/**
 *  Inserts a batch of rows of arbitrary size.
 */
function insert_batch($dbcon, $table, &$rows)
{
  if (!empty($rows)) {
    $row_count = count($rows);
    log_(DEBUG, "Inserting $row_count log records starting at ".$rows[0]['ts']);
    $dbcon->beginTransaction();
    $dbcon->exec("SET foreign_key_checks=0;");
    $colnames = array_keys($rows[0]);
    $coltext = implode(',', $colnames);
    $markertext = implode(',', array_fill(0, count($rows[0]), '?'));

    if (USE_FASTER_SINGLE_INSERT_STMT) {
      $markertext = implode(',', array_fill(0, $row_count, "($markertext)"));
      $stmt = $dbcon->prepare("INSERT INTO $table ($coltext)
                               VALUES $markertext");
      $vals = array();
      // Flatten out the multi-dimensional array into a single dimension
      foreach ($rows as &$row) {
        foreach ($row as $col) {
          $vals[] = $col;
        }
      }
      $stmt->execute($vals);
    }
    else {
      $stmt = $dbcon->prepare("INSERT INTO $table ($coltext)
                               VALUES ($markertext)");
      foreach ($rows as &$row) {
        $stmt->execute(array_values($row));
      }
    }

    $dbcon->exec("SET foreign_key_checks=1;");
    $dbcon->commit();
  }
} // insert_batch()


function load_config()
{
  $config_file = realpath(dirname(__FILE__).'/../analytics.ini');
  if (in_array('BBSTATS_CONFIG', $_ENV)) {
    $config_file = $_ENV['config_file'];
  }

  $config = parse_ini_file($config_file, true);
  if (!$config) {
    log_(ERROR, "Configuration file not found at '$config_file'");
    return false;
  }

  foreach (array('database', 'input') as $section) {
    if (!array_key_exists($section, $config)) {
      log_(500,"Invalid config file. '$section' section required");
      return false;
    }
  }

  return $config;
} // load_config()


function log_($log_level, $message)
{
  global $g_log_level, $g_log_file;

  static $debug_levels = array(
    FATAL => 'FATAL',
    ERROR => 'ERROR',
    WARN => 'WARN',
    INFO => 'INFO',
    DEBUG => 'DEBUG'
  );

  //Get the integer level for each and ignore out of scope log messages
  if ($g_log_level >= $log_level) {
    $lvlstr = 'UNKNOWN';
    if (isset($debug_levels[$log_level])) {
      $lvlstr = $debug_levels[$log_level];
    }
    $datestr = date('Y-m-d:H:i:s');
    if ($g_log_file) {
      fprintf($g_log_file, "%s [%s] %s\n", $datestr, $lvlstr, $message);
    }
    else {
      echo "$datestr [$lvlstr] $message\n";
    }
  }
} // log_()


function get_db_connection($dbconfig)
{
  //Validate the database configuration settings
  $required_keys = array('type','host','name','user','pass','port');
  if ($missing_keys = array_diff_key(array_flip($required_keys), $dbconfig)) {
    $missing_key_msg = implode(', ', array_keys($missing_keys));
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
    return new PDO("$type:host=$host;port=$port;dbname=$name", $user, $pass,
                   array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
  }
  catch (PDOException $e) {
    log_(FATAL, "PDOException:".$e->getMessage());
    return false;
  }
} // get_db_connection()


function get_log_file($filepath)
{
  $log_file = fopen($filepath, 'a');
  if (!$log_file) {
    echo "[bbanalytics] $filepath: Unable to open for writing\n";
  }
  return $log_file;
} // get_log_file()


/**
 * Load all instances from bluebird.cfg
 * The bb_cfg setting in analytics.ini dictates the searched path
 * The default value for bb_cfg is './bluebird.cfg'
 * To force a reload of a previously cached cfg, use the $force parameter
 * Returns an array keyed by server name, with value of array('in_cfg'=>true)
 */
function load_bluebird_instances($config, $force = false)
{
  static $bbini = false;

  log_(DEBUG, "inside load_bluebird_instances, config=".var_export($config,1));
  $instances = array();
  $path = array_value('bb_cfg', $config, './bluebird.cfg');

  log_(DEBUG,"Attempting to load config file $path");

  if (($force || !$bbini) && file_exists($path)) {
    $bbini = parse_ini_file($path, true);
  }

  if (!$bbini) {
    log_(FATAL, "Could not find bluebird.cfg ($path). Check bb_cfg in analytics.ini");
    return false;
  }
  foreach ($bbini as $k=>$v) {
    if (substr($k,0,9)=='instance:') {
      $iname = substr($k,9);
      $instances[$iname]=-1;
    }
  }
  log_(INFO,"Loaded " .count($instances). " instances from $path");
  return $instances;
} // load_bluebird_instances()


function fetch_last_update_time($dbcon)
{
  $q = "SELECT MAX(final_ctime) AS last_update FROM apache_cron_runs";
  try {
    $last_update = $dbcon->query($q)->fetch()['last_update'];
  }
  catch (Exception $e) {
    $last_update = 'Unknown<br/>(ERR: '.$e->getCode().')';
  }

  if (!$last_update) {
    $last_update = 'Unknown<br/>(No Data Returned)';
  }
  return $last_update;
} // fetch_last_update_time()

function deprecate_to($rep = '') {
  $backTrace = debug_backtrace();
  $old=array_shift($backTrace);
  $isold = false;
  if (count($backTrace)) {
    $ref = array_shift($backTrace);
  } else {
    $ref = $old;
    $isold = true;
  }
  $ret = '';
  if ($isold) {
    $ret.="deprecate() called from top level: {$ref['file']}:{$ref['line']}";
  } else {
    $ret.="({$ref['file']}:{$ref['line']}) function " .
          (array_key_exists('class',$ref) ? $ref['class'] : '') .
          (array_key_exists('type',$ref) ? $ref['type'] : '') .
          "{$ref['function']} is deprecated" .
          ($rep ? ", use $rep instead" : '');
  }
  error_log($ret);
}
?>
