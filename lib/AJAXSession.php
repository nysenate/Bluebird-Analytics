<?php
/*
  AJAX Session class for BlueBird analytics
  Provides an easy reference object containing request parameters,
  database connections, configuration, etc.
*/

require_once 'BB_Session.php';
require_once 'AJAXResponse.php';

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