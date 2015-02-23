<?php
/*
  Generic model class for BlueBird analytics
  Provides a base abstract class for models
*/

abstract class Model {

  public static function getBindParams($arr) {
    // set the filter parameters for PDO binding
    $PDOBindParams = array();
    foreach ($arr as $k=>$v) {
      $PDOBindParams[":{$k}"]=$v;
    }
    return $PDOBindParams;
  }

  public static function getData($query, $params=array(), $fetch_type='fetch', $fetch_style=PDO::FETCH_ASSOC) {
    $session = AJAXSession::getInstance();
    $stmt = $session->db->prepare($query);
    $stmt->execute($params);
    $result = $stmt->$fetch_type($fetch_style);
    if (!$result) { $result = array(); }
    $stmt->closeCursor();
    return $result;
  }

  public static function getDataObject($query, $params=array()) {
    return self::getData($query, $params, 'fetchAll', PDO::FETCH_OBJ);
  }

  public static function getDataObjectRow($query, $params=array()) {
    $all = self::getDataObject($query, $params);
    $ret = count($all) ? $all[0] : new stdClass;
    return $ret;
  }

}