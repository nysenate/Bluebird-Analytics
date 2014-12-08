<?php
/*
  AJAX Controller class for BlueBird analytics
  Provides a base abstract class for controllers
*/

abstract class AJAXController {
  protected $session;
  protected $allows_groups = true;
  protected $group_fields = array();

  // a default action if no action is in the request
  protected static $default_action = 'get';

  // an array of all fields that can be used as a pre-calculated field
  // i.e., not subject to an aggregate modifier or grouping
  protected static $calcfields = array(
      'summary' => array('uptime','avg_resp_time'),
      'request' => array('avg_resp_time'),
  );

  // all possible datapoints to be requested
  // formed as array( tableName => array( accessName => fieldName ) )
  protected static $datapoints = array(
      /* data points in the summary tables */
      'summary' => array(
          'timerange'     => 'dt.ts',
          'instance_id'   => 'dt.instance_id',
          'remote_ip'     => 'INET_NTOA(dt.trans_ip)',
          'http_500'      => 'dt.500_errors',
          'http_503'      => 'dt.503_errors',
          'page_views'    => 'dt.page_views',
          'resp_time'     => 'dt.response_time',
          'uptime'        => 'IFNULL((1-((SUM(dt.500_errors)+SUM(dt.503_errors))/SUM(dt.page_views)))*100,0)',
          'avg_resp_time' => 'IFNULL(SUM(dt.response_time),0)/IFNULL(SUM(dt.page_views),1)',
          ),
      /* data points in the uniques tables */
      'uniques' => array(
          'timerange'     => 'dt.ts',
          'instance_id'   => 'dt.instance_id',
          'remote_ip'     => 'INET_NTOA(dt.trans_ip)',
          'path'          => 'dt.value',
          ),
      /* data points in the request table */
      'request' => array(
          'timerange'     => 'dt.ts',
          'instance_id'   => 'dt.instance_id',
          'remote_ip'     => 'INET_NTOA(dt.trans_ip)',
          'resp_code'     => 'dt.response_code',
          'resp_time'     => 'dt.response_time',
          'rcvd_xfer'     => 'dt.transfer_rx',
          'send_xfer'     => 'dt.transfer_tx',
          'method'        => 'dt.method',
          'path'          => 'dt.path',
          'query'         => 'dt.query',
          'avg_resp_time' => 'IFNULL(SUM(dt.response_time),0)/IFNULL(COUNT(dt.response_time),1)',
          ),
      );
  protected static $optionpoints = array(
      /* data points available from joined tables */
      'instance' => array(
          'instance_name' => 'name',
          'server_name'   => 'servername',
          ),
      'location' => array(
          'location_name' => 'name',
          ),
      );

  // follows the convention array('fieldname'=>'sqlmodifier')
  protected static $extrapoints = array();

  public $errors = array();
  public $clauses = array();
  public $bind_params = array();
  public $field_list = array();
  public $suffix = '';
  /* if the number of days in a requested range is less than this number,
     then the query will receive a FORCE INDEX (`timerange`) clause
     */
  public $maximum_index_range = 185;
  /* these flags indicate the applicability of indexes timerange and instance_id, respectively
     these flags will be calculated while parsing the request, but can be changed before the
     query is actually built, if necessary */
  public $force_time_index = false;
  public $force_instance_index = false;
  /* determines if a join to other tables is necessary */
  public $join_instance_table = false;
  public $join_location_table = false;

  public function __construct() {
    $this->session = AJAXSession::getInstance();
    if (!($this->validate())) {
      $this->session->response->send();
    }
  }

  protected function _addFieldFormat($f, $fmt) {
    $ret=$f;
    // get precision, if provided
    $p = $has_p = 0;
    if (preg_match('/(.+)\|([0-9]+)$/',$fmt,$p)) {
      $has_p = true;
      $fmt = $p[1];
      $p = $p[2];
    }
    switch($fmt) {
      case 'int':        $ret = "ROUND(IFNULL($ret,0),0)"; break;
      case 'intperk':    $ret = "ROUND(IFNULL($ret,0)/1000,0)"; break;
      case 'intcomma':   $ret = "FORMAT(IFNULL($ret,0),0)"; break;
      case 'floatcomma': $ret = "FORMAT(IFNULL($ret,0),".($has_p ? $p : 4).")"; break;
      case 'percent':    $ret = "CONCAT(FORMAT(IFNULL($ret,0),".($has_p ? $p : 2)."),'%')"; break;
      case 'microsec':   $ret = "CONCAT(FORMAT(IFNULL($ret,0)/1000000,".($has_p ? $p : 2)."),'s')"; break;
      default:
        $this->session->log("Invalid format '$fmt'",LOG_LEVEL_WARN);
        $this->session->addError("Invalid format '$fmt' ignored",LOG_LEVEL_WARN);
    }
    return $ret;
  }

  protected function _buildGroupBy($reportname) {
    $flds = '';
    if ($this->allows_groups) {
      if (array_key_exists($reportname,$this->group_fields)) {
        $flds = implode(',',$this->group_fields[$reportname]);
      }
    }
    return $flds;
  }

  protected function _buildSelectFields($report_name, $fields) {
    $groups = array_value($report_name,$this->group_fields,array());
    $groupsel = array();
    foreach ($groups as $k=>$v) {
      $groupsel[] = "$v as $k";
    }
    $ret= implode(',',array_unique(array_merge($groupsel,$fields)));
    $this->session->log("build select for report {$report_name} return=".var_export($ret,1),LOG_LEVEL_DEBUG);
    return $ret;
  }

  protected function _constructField($table,$field) {
    // initialize return
    $ret='';
    // verify field name and modifier
    $fld = array_value('field',$field);
    $mod = array_value('mod',$field);
    $fmt = array_value('fmt',$field);
    // the default "found" table alias is 'dt'
    $found_table='';
    // verify table exists
    $thistable = array_value($table, static::$datapoints);
    /*if (!(is_array($thistable) && count($thistable))) {
      return $ret;
    }*/
    // verify field exists in table
    $sqlfld = array_value($fld, $thistable);
    if (!$sqlfld) {
      $this->session->log("Could not find $fld in $table, looking for options",LOG_LEVEL_DEBUG);
      // the field was not found, check the optional tables *IF* groups are allowed
      if ($this->allows_groups && $mod=='group') {
        if ($sqlfld = array_value($fld, static::$optionpoints['instance'])) {
          $this->session->log("Found $fld in instance",LOG_LEVEL_DEBUG);
          $found_table = 'instance.';
          $this->join_instance_table = true;
        } elseif ($sqlfld = array_value($fld, static::$optionpoints['location'])) {
          $this->session->log("Found $fld in location",LOG_LEVEL_DEBUG);
          $found_table = 'location.';
          $this->join_location_table = true;
        }
      }
    }
    $this->session->log("Found report field $fld,$mod = $sqlfld",LOG_LEVEL_DEBUG);
    // initialize formatted field
    $agg='';
    // if a good field has been found, build the formatted field
    if ($sqlfld) {
      switch($mod) {
        case 'count': $agg = "COUNT({$found_table}{$sqlfld})"; break;
        case 'countd':$agg = "COUNT(DISTINCT {$found_table}{$sqlfld})"; break;
        case 'sum':   $agg = "SUM({$found_table}{$sqlfld})"; break;
        case 'avg':   $agg = "AVG({$found_table}{$sqlfld})"; break;
        case 'none':  $agg = $sqlfld; break;
        case 'calc':
          if (in_array($fld, array_value($table,static::$calcfields,array()))) {
            $agg = $sqlfld;
          }
          break;
      }
      if ($fmt) { $agg = $this->_addFieldFormat($agg, $fmt); }
    }
    $this->session->log("Aggregate for $fld,$mod = $agg",LOG_LEVEL_DEBUG);
    if ($agg) { $ret = "$agg as $fld"; }
    $this->session->log("Final $fld,$mod = $ret",LOG_LEVEL_DEBUG);
    return $ret;
  }

  protected function _parseFields($table, $fields) {
    // dealing with all fields in a single report, which targets a specific table
    $ret = array();
    // the table must exist in static::$datapoints
    $checktable = array_value($table, static::$datapoints);
    if (!(is_array($checktable) && count($checktable))) {
      return $ret;
    }
    $this->session->log("parsing fields:\n".var_export($fields,1),LOG_LEVEL_DEBUG);
    // iterate through all fields and construct the SQL select entry
    if (is_array($fields) && count($fields)) {
      foreach ($fields as $k=>$v) {
        if ($v['mod']!='group') {
          $newfield = $this->_constructField($table, $v);
          if ($newfield) {
            $ret[] = $newfield;
          } else {
            $this->session->log("Could not construct field '".array_value('field',$v)."', it has been ignored",LOG_LEVEL_DEBUG);
          }
        }
      }
    }
    return $ret;
  }

  protected function _parseGroupBy() {
    $reps = $this->session->reports;
    $this->group_fields=array();
    if ($this->allows_groups) {
      $this->session->log(get_called_class()." allows groups, parsing reps=".var_export($reps,1),LOG_LEVEL_DEBUG);
      foreach ($reps as $k=>$report) {
        $report_name = $report['report_name'];
        $table = $report['target_table'];
        $thistable = array_value($table, static::$datapoints);
        $this->group_fields[$report_name]=array();
        $fields = $report['datapoints'];
        // if extrapoints is populated, add those entries to the field list
        if (count(static::$extrapoints)) {
          foreach (static::$extrapoints as $fld=>$mod) {
            $fields[] = array('field'=>$fld,'mod'=>$mod);
          }
        }
        foreach ($fields as $kk=>$onefield) {
          $fld=$onefield['field'];
          $mod=$onefield['mod'];
          if ($mod=='group') {
            $found_table='';
            $sqlfld = array_value($fld, $thistable);
            if (!$sqlfld) {
              $this->session->log("Could not find $fld in $table, looking for options",LOG_LEVEL_DEBUG);
              // the field was not found, check the optional tables *IF* groups are allowed
              if ($sqlfld = array_value($fld, static::$optionpoints['instance'])) {
                $this->session->log("Found $fld in instance",LOG_LEVEL_DEBUG);
                $found_table = 'instance.';
                $this->join_instance_table = true;
              } elseif ($sqlfld = array_value($fld, static::$optionpoints['location'])) {
                $this->session->log("Found $fld in location",LOG_LEVEL_DEBUG);
                $found_table = 'location.';
                $this->join_location_table = true;
              }
            }
            if ($sqlfld) { $this->group_fields[$report_name][$fld] = "{$found_table}{$sqlfld}"; }
          }
        }
      }
    }
    $this->session->log("final parsed groups=\n".var_export($this->group_fields,1),LOG_LEVEL_DEBUG);
  }

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
      $this->session->log("Adding new list of fields for $repname=\n".var_export($fields,1),LOG_LEVEL_DEBUG);
      $tables[$repname] = array('target_table'=>$reptable,
                                'fields'=>$fields,
                                'sortorder'=>array_value('sortorder',$val,array())
                               );
    }
    $this->session->log("Final parsed reports=\n".var_export($tables,1),LOG_LEVEL_DEBUG);
    return $tables;
  }

  public function addError($errtype, $value, $st=NULL) {
    $this->session->response->addError($errtype, $value);
    $this->session->response->setStatus($st);
  }

  protected function analyzeIndexes($vals) {
    $st = strtotime(array_value('starttime',$vals));
    $et = strtotime(array_value('endtime',$vals));
    $instance = array_value('instance',$vals,'ALL');

    // check for minimum range.  If yes, force use of the index
    if (abs(strtotime($vals['starttime'])-strtotime($vals['endtime'])) < (86400*$this->maximum_index_range)) {
      $this->force_time_index = true;
    }

    if ($instance!=='ALL') {
      $this->force_instance_index = true;
    }
  }

  public function validate() {
    $ret = true;
    // verify the granularity
    $granularity = $this->session->req('filters')['granularity'];
    $this->suffix = $this->getTableSuffix($granularity);
    if (!$this->suffix) {
      $this->addError(AJAX_ERR_FATAL,"Invalid granularity '$granularity' received",400);
      $ret = false;
    }

    // hook to parse reports in a special manner
    $this->reports = $this->_parseReports();
    $this->_parseGroupBy();

    // build the common where clause predicates and bind parameters
    $this->getCommonClauses();

    return $ret;
  }

  public function getAllRows($query, $params) {
    return $this->getData($query, $params, 'fetchAll');
  }

  public function getBindParams($arr) {
    // set the filter parameters for PDO binding
    $PDOBindParams = array();
    foreach ($arr as $k=>$v) {
      $PDOBindParams[":{$k}"]=$v;
    }
    return $PDOBindParams;
  }

  public function getControllerName() {
    $ret = strtolower(str_replace('AJAXController','',get_called_class()));
    if (!$ret) { $ret = "<abstract>"; }
    return $ret;
  }

  protected function getCommonClauses() {
    $filters = $this->session->req('filters');
    // initialize the starting values
    $vals = array(
                'starttime' => array_value('starttime',$filters,NULL),
                'endtime'   => array_value('endtime',$filters,NULL),
                'instance'  => array_value('instance',$filters,'ALL'),
                );

    // analyze the need for indexes
    $this->analyzeIndexes($vals);

    // build the where clause array
    $where = array("`ts` BETWEEN :starttime AND :endtime");
    if ($this->force_instance_index) {
      $where[] = "instance.name = :instance";
    } else {
      // if not using an instance filter, unset it so it doesn't bind as a parameter
      unset($vals['instance']);
    }

    // bind the common query parameters
    $this->clauses = $where;
    $this->bind_params = $this->getBindParams($vals);
  }

  public function getData($query, $params, $fetch_type='fetch', $fetch_style=PDO::FETCH_ASSOC) {
    $stmt = $this->session->db->prepare($query);
    $stmt->execute($params);
    $result = $stmt->$fetch_type($fetch_style);
    if (!$result) { $result = array(); }
    $stmt->closeCursor();
    return $result;
  }

  public function getDatapointFields() {
    // get the requested data points
    $datapoints = $this->session->fetchReq('datapoints');
    if (!is_array($datapoints)) { $datapoints = array($datapoints); }

    // for each requested data point, add the field construct to the field list
    $fields = static::parseFields($datapoints, static::$datapoints[$this->target]);

    if (!count($fields)) { $fields = NULL; }

    return $fields;
  }

  protected function getIndexClause() {
    $idx = array();
    if ($this->force_instance_index) { $idx[] = 'instance_id'; }
    if ($this->force_time_index) { $idx[] = 'timerange'; }
    return count($idx) ? 'FORCE INDEX (' . implode(',',$idx) . ')' : '';
  }

  public function getJoinClause() {
    $join = "dt " . $this->getIndexClause();
    if ($this->force_instance_index || $this->join_instance_table) {
      $join .= " INNER JOIN instance ON dt.instance_id=instance.id";
    }
    if ($this->join_location_table) {
      $join .= " INNER JOIN location ON dt.location_id=location.id";
    }
    return $join;
  }

  public function getSingleRow($query, $params) {
    return $this->getData($query, $params);
  }

  public function getTableName($table) {
    $ret = $table=='request' ? $table : "{$table}_{$this->suffix}";
    return $ret;
  }

  public function getTableSuffix($gran) {
    $ret='';
    switch ($gran) {
      case 'minute': $ret = "1m"; break;
      case '15minute': $ret = "15m"; break;
      case 'hour': $ret = "1h"; break;
      case 'day': $ret = "1d"; break;
      case 'month': $ret = "1d"; break;
    }
    return $ret;
  }

  protected function parseFields($fields,$source) {
    $ret = array();
    foreach ($fields as $k) {
      $thispoint = array_value($k, $source, NULL);
      if ($thispoint) {
        if (!array_key_exists($thispoint['table'],$ret)) {
          $ret[$thispoint['table']] = array();
        }
        $ret[$thispoint['table']][]="{$thispoint['field']} AS $k";
      }
    }
    return $ret;
  }

  public function route() {
    // Initialize response data object
    $data = new stdClass();
    $controller = $this->getControllerName();
    // verify the action is available
    $action = strtolower($this->session->req('action'));
    if (!$action) { $action = static::$default_action; }
    $this->session->response->setIdentifiers($controller, $action);
    // if action does not exist, fail and exit
    if (!method_exists($this, $action)) {
      $this->addError(LOG_LEVEL_FATAL,"Handler '{$controller}' could not find action '$action'",400);
    } else {
      $output = $this->$action();
      $this->session->log("result of action=".var_export($output,1),LOG_LEVEL_DEBUG);
      switch(gettype($output)) {
        case 'object':
        case 'array':
          foreach ($output as $k=>$v) { $data->$k=$v; }
          break;
        default:
          $this->addError(LOG_LEVEL_DEBUG,"Handler {$controller}::{$action} did not return an object");
          $data->result=$output;
          break;
      }
    }
    $this->session->log("result of route=".var_export($data,1),LOG_LEVEL_DEBUG);
    return $data;
  }
}