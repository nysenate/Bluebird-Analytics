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
    $this->_parseRequest();
    $this->response = new AJAXResponse($this->req('req'),$this->req('action'));
    parent::__construct();
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

}