<?php
/*
  Session class for BlueBird analytics
  Provides an easy reference object containing request parameters,
  database connections, configuration, etc.
*/

require_once 'BB_Logger.php';

class BB_Session {
  protected static $instance = NULL;

  public $config = NULL;
  public $logger = NULL;
  public $db = NULL;

  protected function __construct() {
    $this->loadConfig();
    $this->startLogger();
    $this->log("Logging started",LOG_LEVEL_DEBUG);
    $this->initDB();
  }

  public function fetchReq($key) {
    deprecate_to('BB_Session->req');
    return array_value($key, $this->request);
  }

  public static function getInstance() {
    if (!(static::$instance)) {
      static::$instance = new static;
    }
    return static::$instance;
  }

  public function initDB() {
    $c = array_value('database', $this->config);
    $badc = is_array($c) ? false : true;

    //Validate the database configuration settings
    if (!$badc) {
      $required_keys = array('type','host','name','user','pass','port');
      if ($missing_keys = array_diff_key(array_flip($required_keys), $c)) {
        $missing_key_msg = implode(', ',array_keys($missing_keys));
        $this->log("Config missing database info: $missing_key_msg",LOG_LEVEL_FATAL);
        $badc = true;
      }
    }

    if ($badc) {
      $this->response->sendFatal("Missing database configuration, see error log for details");
      return false;
    }

    try {
      $dbconnstr = "{$c['type']}:host={$c['host']};port={$c['port']};dbname={$c['name']}";
      $this->db = new PDO($dbconnstr, $c['user'], $c['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    }
    catch (PDOException $e) {
      $this->log("PDOException:".$e->getMessage(),LOG_LEVEL_FATAL);
      $this->response->sendFatal("Failed to connect to database:".$e->getMessage());
      return false;
    }
  }

  public function loadConfig() {
    $cfgfile = '';
    $locations = array('./','../',$_SERVER['DOCUMENT_ROOT'], );
    if (array_key_exists('BBSTATS_CONFIG',$_ENV)) {
      array_unshift($locations, $_ENV['BBSTATS_CONFIG']);
    }
    $fn = array_value('BBSTATS_CONFIGNAME', $_ENV, 'analytics.ini');
    foreach ($locations as $k) {
      if (file_exists("$k/$fn")) {
        $cfgfile = "$k/$fn";
        break;
      }
    }
    if ($cfgfile) {
      $this->config = parse_ini_file($cfgfile, true);
    }
    if (!$this->config) {
      $this->response->sendFatal("Could not load config");
    }
  }

  /* wrapper around $this->log() for easier reference */
  public function log($msg, $lvl=LOG_LEVEL_INFO) {
    $this->logger->log($msg, $lvl);
  }

  public function req($key, $default=NULL) {
    return array_value($key, $this->request, $default);
  }

  public function startLogger() {
    $level = $file = $loc = NULL;
    if (array_key_exists('debug',$this->config)) {
      $c = $this->config['debug'];
      $level = (int) array_value('level', $c, $level);
      $file  =       array_value('file',  $c, $file );
      $loc   =       array_value('path',  $c, $loc  );
    }
    $this->logger = BB_Logger::getInstance($level, $file, $loc);
  }
}