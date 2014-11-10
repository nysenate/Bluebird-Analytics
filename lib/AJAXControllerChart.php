<?php
/*
  AJAX Controller class for BlueBird analytics chart reports
*/
require_once 'AJAXController.php';

class AJAXControllerChart extends AJAXController {
  protected static $extrapoints = array('timerange'=>'group');

  public function get() {
    // iterate through the tables to build each query and get results
    $result = array();
      $this->session->log("get=>reports: ".var_export($this->reports,1),LOG_LEVEL_DEBUG);
    foreach ($this->reports as $reportname => $onereport) {
      $this->session->log("get=>onereport: ".var_export($onereport,1),LOG_LEVEL_DEBUG);
      $this->session->log("static::extra: ".var_export(static::$extrapoints,1),LOG_LEVEL_DEBUG);
      // build the select and group clauses
      $fields = $this->_buildSelectFields($reportname, $onereport['fields']);
      $this->session->log("built select fields: ".var_export($fields,1),LOG_LEVEL_DEBUG);
      // create the common where and join clauses
      $where = "WHERE ".implode(' AND ',$this->clauses);
      $this->session->log("built where: ".var_export($where,1),LOG_LEVEL_DEBUG);
      $gb = $this->_buildGroupBy($reportname);
      $group = $gb ? "GROUP BY $gb" : '';
      $this->session->log("built group: ".var_export($group,1),LOG_LEVEL_DEBUG);
      $join = $this->getJoinClause();
      $this->session->log("built join: ".var_export($join,1),LOG_LEVEL_DEBUG);
      $ttable = $this->getTableName($onereport['target_table']);
      $query = "SELECT $fields FROM {$ttable} $join $where $group";
      $this->session->log("Final query: $query",LOG_LEVEL_DEBUG);
      $result += $this->getAllRows($query, $this->bind_params);
    }
    return $result;
  }
}