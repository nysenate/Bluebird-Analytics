<?php
/*
  Model class for Report Property objects
*/

require_once 'Model.php';

class ModelReportProperty extends Model {
  /* A mapping between database field names and the model's property names */
  private static $_db_map = array(
      'report_id'=>'report_id',
      'property_id'=>'property_id',
      'property_type_id'=>'type',
      'property_name'=>'name',
      'property_desc'=>'description',
      'property_value'=>'value'
  );

  public function __construct($init=NULL) {
    if ($init) {
      foreach ($init as $k=>$v) {
        if (array_key_exists($k,self::$_db_map)) {
          $this->{self::$_db_map[$k]}=$v;
        }
      }
    }
  }

  public function __toString() {
    return (string)$this->value;
  }

  public static function loadAllProperties($id) {
    $param = array(':report_id'=>(int)$id);
    $query = "SELECT * FROM v_report_properties WHERE report_id = :report_id";
    $ret = new stdClass;
    if ((int)$id) {
      $tret = self::getDataObject($query, $param);
      foreach ($tret as $k=>$v) {
        $ret->{$v->property_name} = new ModelReportProperty($v);
      }
    }
    return $ret;
  }

}