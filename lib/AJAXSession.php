<?php
/*
  AJAX Session class for BlueBird analytics
  Provides an easy reference object containing request parameters,
  database connections, configuration, etc.
*/

require_once 'BB_Session.php';
require_once 'AJAXResponse.php';

class AJAXSession extends BB_Session {
  protected static $instance = NULL;

  public $response = NULL;
  public $request = NULL;

  protected function __construct() {
    parent::__construct();
    $this->_parseRequest();
    $this->response = new AJAXResponse($this->req('req'),$this->req('action'));
  }

  protected function _parseRequest() {
    $this->log("parseRequest full _REQUEST=\n".var_export($_REQUEST,1),LOG_LEVEL_DEBUG);
    if (!is_array($this->request)) { $this->request = array(); }
    foreach ($_REQUEST as $k=>$v) {
      if ($k != 'reports') {
        $this->log("parseRequest $k = ".var_export($v,1),LOG_LEVEL_DEBUG);
        $this->request[$k] = clean_string($v);
        $this->log("parseRequest set $k = ".var_export($this->request[$k],1),LOG_LEVEL_DEBUG);
      }
    }
    $this->log("parseRequest set request=\n".var_export($this->request,1),LOG_LEVEL_DEBUG);
    $this->reports = array_value('reports',$_REQUEST,array());
    if (!is_array($this->reports)) { $this->reports = array(); }
  }

  public function dualLog($msg, $lvl) {
    $this->log($msg, $lvl++);
    $this->response->addError($lvl, $msg);
  }
}