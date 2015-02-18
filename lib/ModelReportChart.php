<?php
/*
  Model class for Chart Report objects
*/

require_once 'ModelReport.php';

class ModelReportChart extends ModelReport {
  public function __construct($name, $filters) {
    parent::__construct($name, $filters);
    $this->addTimestampField();
  }

  public function addTimestampField() {
    $found_ts = false;
    foreach ($this->fields as $k=>$v) {
      if ($v->name=='timerange' && $v->sql=='ts') { $found_ts = true; }
    }
    if (!$found_ts) {
      $q = "SELECT 0 as id, :id as report_id, b.id as field_def_id, " .
           "'group' as aggregate, '' as fmtcode, 1 as sort_order, " .
           "b.name, b.source_table, b.select_sql, b.calculated " .
           "FROM report_field_defs b " .
           "WHERE b.source_table = :stable and b.name='timerange';";
      $p = array (':id'=>$this->id, ':stable'=>(string)$this->properties->target_table);
      $tret = self::getDataObject($q, $p);
      if (count($tret)) { $this->fields[] = new ModelField($tret[0]); }
    }
  }
}