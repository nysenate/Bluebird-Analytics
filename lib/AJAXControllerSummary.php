<?php
/*
  AJAX Controller class for BlueBird analytics summary reports
*/
require_once 'AJAXController.php';

class AJAXControllerSummary extends AJAXController {
  protected $allows_groups = false;

  /* need a different report parsing here
     Since summary reports are single datapoints with no grouping, we only need
     to query each table once.  Iterate through the requested reports, and combine
     them by table. */
  protected function _parseReports() {
    $reps = $this->session->reports;
    $tables = array();
    $this->session->log("Examining reports=\n".var_export($reps,1),LOG_LEVEL_DEBUG);
    foreach ($reps as $key=>$val) {
      // a report definition should be an array
      if (!is_array($val)) {
        $this->addError(AJAX_ERR_ERROR,"Invalid report definition has been ignored");
        continue;
      }
      // the report should come from one of the tables in static::$datapoints
      $repname = array_value('report_name',$val,"<no-name>");
      $reptable = clean_string(array_value('target_table',$val));
      if (!in_array($reptable,array_keys(static::$datapoints))) {
        $this->addError(AJAX_ERR_ERROR,"Invalid target table requested, report '$repname' has been ignored");
        continue;
      }
      // the report needs to have field definitions
      $fields = array_value('datapoints',$val);
      if (!count($val['datapoints'])) {
        $this->addError(AJAX_ERR_ERROR,"No datapoints requested, report '$repname' has been ignored");
        continue;
      }
      // if extrapoints is populated, add those entries to the field list
      if (count(static::$extrapoints)) {
        foreach (static::$extrapoints as $fld=>$mod) {
          $fields[] = array('field'=>$fld,'mod'=>$mod);
        }
      }
      // parse the fields, and verify valid fields have been requested
      $fields = $this->_parseFields($reptable,$fields);
      if (!(is_array($fields) && count($fields))) {
        $this->addError(AJAX_ERR_ERROR,"Invalid/missing field definitions, report '$repname' has been ignored");
        continue;
      }
      if (!array_value($reptable, $tables)) {
        $tables[$reptable] = array();
      }
      $this->session->log("Adding new list of fields to $reptable=\n".var_export($fields,1),LOG_LEVEL_DEBUG);
      $tables[$reptable] = array_merge($tables[$reptable], $fields);
    }
    $this->session->log("Final parsed reports=\n".var_export($tables,1),LOG_LEVEL_DEBUG);
    return $tables;
  }

  public function get() {
    $this->session->log("Received data:\n".var_export($this->reports,1),LOG_LEVEL_DEBUG);
    // iterate through the tables to build each query and get results
    $result = array();
    foreach ($this->reports as $table => $tfields) {
      // build the select and group clauses
      $fields = $this->_buildSelectFields($table,$tfields);
      // create the common where and join clauses
      $where = "WHERE ".implode(' AND ',$this->clauses);
      $join = $this->getJoinClause();
      $ttable = $this->getTableName($table);
      $query = "SELECT $fields FROM {$ttable} $join $where";
      $this->session->log("Final query: $query",LOG_LEVEL_DEBUG);
      $result += $this->getSingleRow($query, $this->bind_params);
    }
    return $result;
  }
}