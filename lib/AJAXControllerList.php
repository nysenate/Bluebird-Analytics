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

  public function get() {
    // iterate through the tables to build each query and get results
    $result = array();
    $this->session->log("get=>reports: ".var_export($this->reports,1),LOG_LEVEL_INFO);
    foreach ($this->reports as $reportname => $onereport) {
      $this->session->log("get=>onereport: ".var_export($onereport,1),LOG_LEVEL_INFO);
      $this->session->log("static::extra: ".var_export(static::$extrapoints,1),LOG_LEVEL_INFO);
      // build the select and group clauses
      $fields = $this->_buildSelectFields($reportname, $onereport['fields']);
      $this->session->log("built select fields: ".var_export($fields,1),LOG_LEVEL_INFO);
      // create the common where and join clauses
      $where = "WHERE ".implode(' AND ',$this->clauses);
      $this->session->log("built where: ".var_export($where,1),LOG_LEVEL_INFO);
      $gb = $this->_buildGroupBy($reportname);
      $group = $gb ? "GROUP BY $gb" : '';
      $this->session->log("built group: ".var_export($group,1),LOG_LEVEL_INFO);
      $join = $this->getJoinClause();
      $this->session->log("built join: ".var_export($join,1),LOG_LEVEL_INFO);
      $limit = $this->_buildLimit();
      $this->session->log("built limit: ".var_export($limit,1),LOG_LEVEL_INFO);
      $query = "SELECT $fields FROM {$onereport['target_table']}_{$this->suffix} $join $where $group $limit";
      $this->session->log("Final query: $query",LOG_LEVEL_INFO);
      $result[$reportname] = $this->getAllRows($query, $this->bind_params);
    }
    return $result;
  }
}