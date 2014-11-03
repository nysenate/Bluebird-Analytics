<?php
/*
  AJAX Session class for BlueBird analytics
  Provides an easy reference object containing request parameters,
  database connections, configuration, etc.
  application.
*/

require_once 'AJAXResponse.php';
require_once 'AJAXLogger.php';

/*  EXAMPLE INCOMING REQUEST
array (
  'req' => 'summary',
  'filter' => '',
  'starttime' => '2014-03-06 15:41',
  'endtime' => '2014-05-23 16:41',
  'granularity' => 'day',
  'instance' => 'ALL',
  'reports' => array (
    0 => array (
      'report_name' => 'page_views',
      'report_type' => 'summary',
      'target_table' => 'summary',
      'datapoints' => array ( 0 => array ('field' => 'page_views','mod' => 'sum', ), ),
      'props' => array (
        'headerIcon' => 'fa fa-files-o fa-3x',
        'linkTarget' => '/datatable',
        'linkText' => 'Browse Content',
        'valueCaption' => 'Pages Served',
        'wrapperID' => 'page_views',
      ),
    ),
  ),
)
*/
class AJAXSession {
  private static $instance = NULL;

  public $response = NULL;
  public $config = NULL;
  public $request = NULL;
  public $logger = NULL;
  public $db = NULL;

  private function __construct() {
    $this->_parseRequest();
    $this->response = new AJAXResponse($this->req('req'),$this->req('action'));
    $this->loadConfig();
    $this->startLogger();
    $this->log("Logging started",LOG_LEVEL_DEBUG);
    $this->initDB();
  }

  protected function _parseRequest() {
    if (!is_array($this->request)) { $this->request = array(); }
    foreach ($_REQUEST as $k=>$v) {
      if ($k != 'reports') {
        $this->request[$k] = clean_string($v);
      }
    }
    $this->reports = array_value('reports',$_REQUEST,array());
    if (!is_array($this->reports)) { $this->reports = array(); }
  }

  public function fetchReq($key) {
    deprecate_to('AJAXSession->req');
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

  public function req($key) {
    return array_value($key, $this->request);
  }

  public function startLogger() {
    $level = $file = $loc = NULL;
    if (array_key_exists('debug',$this->config)) {
      $c = $this->config['debug'];
      $level = (int) array_value('level', $c, $level);
      $file  =       array_value('file',  $c, $file );
      $loc   =       array_value('path',  $c, $loc  );
    }
    $this->logger = AJAXLogger::getInstance($level, $file, $loc);
  }
}