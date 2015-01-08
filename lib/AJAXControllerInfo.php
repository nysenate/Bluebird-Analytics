<?php
/*
  AJAX Controller class for BlueBird analytics chart reports
*/
require_once 'AJAXController.php';

class AJAXControllerInfo extends AJAXController {

  public function __construct() {
    $this->session = AJAXSession::getInstance();
  }

  public function avail_datapoints() {
    $ret = array();
    foreach (self::$datapoints as $k1=>$v1) {
      foreach ($v1 as $k2=>$v2) {
        $n = "$k1.$k2";
        array_push($ret, array('id'=>$n, 'name'=>$n));
      }
    }
    return $ret;
  }

  public function saved_queries() {
    $query = "SELECT id, name FROM datatable ORDER BY name";
    $result = $this->getAllRows($query, $this->bind_params);
    return $result;
  }
}