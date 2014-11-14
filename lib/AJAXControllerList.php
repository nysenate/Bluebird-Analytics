<?php
/*
  AJAX Controller class for BlueBird analytics list reports
*/
require_once 'AJAXController.php';

class AJAXControllerList extends AJAXController {
  protected function _buildLimit() {
    $page = (int)$this->session->req('listpage',1);
    $count = (int)$this->session->req('listcount',10);
    if ($page < 1) { $page = 1; }
    if ($count < 1) { $count = 10; }
    $offset = ($page - 1) * $count;
    return "LIMIT $offset,$count";
  }

  protected function _buildOrder($table, $sort = NULL) {
    if (!is_array($sort)) { $sort = array(); }
    $ret = array();
    foreach($sort as $k=>$v) {
      $fld = array_value($k, static::$datapoints[$table]);
      if (!$fld) {
        $fld = array_value($k, static::$optionpoints['instance']);
      }
      if (!$fld) {
        $fld = array_value($k, static::$optionpoints['location']);
      }
      if ($fld && $table) {
        $ret[] = "{$k} " . ($v==='DESC' ? 'DESC' : 'ASC');
      }
    }
    $ret = count($ret) ? "ORDER BY " . implode(',',$ret) : '';
    return $ret;
  }

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
      $order = $this->_buildOrder($onereport['target_table'],array_value('sortorder',$onereport,array()));
      $this->session->log("built order: ".var_export($order,1),LOG_LEVEL_DEBUG);
      $limit = $this->_buildLimit();
      $this->session->log("built limit: ".var_export($limit,1),LOG_LEVEL_DEBUG);
      $ttable = $this->getTableName($onereport['target_table']);
      $query = "SELECT $fields FROM {$ttable} $join $where $group $order $limit";
      $this->session->log("Final query: $query",LOG_LEVEL_DEBUG);
      $result[$reportname] = $this->getAllRows($query, $this->bind_params);
    }
    return $result;
  }
}