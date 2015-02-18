<?php
/*
  Model class for Report objects
*/

require_once 'Model.php';
require_once 'ModelField.php';
require_once 'ModelReportProperty.php';

class ModelReport extends Model {
  public $report_name = '';
  public $rows = array();
  protected $filters = array();
  protected $allow_groups = true;
  protected $table_suffix = '';

  protected $_select = '';
  protected $_tables = '';
  protected $_groups = '';
  protected $_where = '';
  protected $_order = '';
  protected $_limit = '';
  protected $_bind_params = array();
  protected $maximum_index_range = 185;
  protected $force_instance_index = false;
  protected $force_time_index = false;
  protected $_bad_config = false;

  // copy the datapoint

  // follows the convention array('fieldname'=>'sqlmodifier')
  protected static $extrapoints = array();

  public function __construct($name, $filters) {
    $this->report_name=$name;
    $this->filters = $filters;
    $this->_bad_config = false;
    $this->loadReport();
  }

  protected function analyzeIndexes() {
    $st = strtotime($this->filters['starttime']);
    $et = strtotime($this->filters['endtime']);
    $instance = (string)$this->filters['instance'];

    // check for minimum range.  If yes, force use of the index
    if (abs($st-$et) < (86400*$this->maximum_index_range)) {
      $this->force_time_index = true;
    }

    if ($instance!=='ALL') {
      $this->force_instance_index = true;
    }
  }

  protected function buildGroupClauses() {
    if ($this->allow_groups) {
      $grp = $this->listGroupFields();
      usort($grp, function($a,$b) {
        if ($a->sort_order==$b->sort_order) {
          return 0;
        }
        return ($a->sort_order < $b->sort_order) ? -1 : 1;
      });
      $this->_groups = implode(',',array_unique(array_map(function($v) {return $v->name;}, $grp)));
    } else {
      $this->_groups = '';
    }
  }

  protected function buildIndexClause() {
    $idx = array();
    if ($this->force_instance_index) { $idx[] = 'instance_id'; }
    if ($this->force_time_index) { $idx[] = 'timerange'; }
    return count($idx) ? 'FORCE INDEX (' . implode(',',$idx) . ')' : '';
  }

  protected function buildQuery() {
    $q = '';
    if ($this->_select && $this->_tables) {
      $q = "SELECT {$this->_select} FROM {$this->_tables}";
      if ($this->_where) {
        $q .= " WHERE {$this->_where}";
      }
      if ($this->_groups && $this->allow_groups) {
        $q .= " GROUP BY {$this->_groups}";
      }
      if ($this->_order) {
        $q .= " ORDER BY {$this->_order}";
      }
      if ($this->_limit) {
        $q .= " LIMIT {$this->_limit}";
      }
    }
    return $q;
  }

  protected function buildSelectClauses() {
    $sel = array();
    foreach ($this->fields as $k=>$v) {
      array_push($sel,$v->generateSelect());
    }
    $this->_select = implode(',',$sel);
  }

  protected function buildWhereClauses() {
    // initialize the starting values
    $vals = array(
                'starttime' => array_value('starttime',$this->filters,NULL),
                'endtime'   => array_value('endtime',$this->filters,NULL),
                'instance'  => array_value('instance',$this->filters,'ALL'),
                );
    // build the where clause array
    $where = array("`ts` BETWEEN :starttime AND :endtime");
    if ($this->force_instance_index) {
      $where[] = "instance.name = :instance";
    } else {
      // if not using an instance filter, unset it so it doesn't bind as a parameter
      unset($vals['instance']);
    }
    // bind the query parameters
    $this->_where = implode(' AND ',$where);
    $this->_bind_params = $this->getBindParams($vals);
  }

  protected function buildTableClauses() {
    $primary_count = 0;
    $primary = '';
    $lookups = array();
    // find the "primary" table and catalog all lookups
    foreach($this->fields as $k=>$v) {
      if (in_array($v->target,array('request','uniques','summary'))) {
        if ($v->target!=$primary) {
          $primary = $v->target;
          $primary_count++;
        }
      } else {
        $lookups[$v->target] = true;
      }
    }
    // be sure to include the instance filter
    if ($this->force_instance_index) {
      $lookups['instance'] = true;
    }
    // some sanity checks
    if ($primary_count > 1) {
      $this->_bad_config = true;
      AJAXSession::getInstance()->dualLog(
          LOG_LEVEL_FATAL,
          "Cannot have more than 1 primary data source (request, uniques, summary)"
      );
    } elseif ($primary_count == 0 && count($lookups)>1) {
      $this->_bad_config = true;
      AJAXSession::getInstance()->dualLog(
          LOG_LEVEL_FATAL,
          "Cannot have more than one lookup table with no primary"
      );
    }
    if ($this->_bad_config) { return false; }
    if ($primary) {
      $is_request_table = ($primary=='request');
      $this->table_suffix = $this->getTableSuffix($this->filters['granularity']);
      $this->source_table = $this->getTableName($primary);
      $primary = "{$this->source_table} dt " . $this->buildIndexClause();
    }
    $this->_tables = $primary;
    if (count($lookups)) {
      if (!$primary) { $this->_tables = current(array_keys($lookups)); }
      else {
        foreach ($lookups as $k=>$v) {
          if ($k=='instance' || $k=='location') {
            $this->_tables .= " INNER JOIN `$k` ON dt.`{$k}_id`=`$k`.id";
          } elseif ($k=='url') {
            $this->_tables .= " INNER JOIN url ON dt." .
                              ($is_request_table ? 'path' : 'value') .
                              "=url.path";
          }
        }
      }
    }
  }

  public function filterFields($type, $match) {
    return array_filter($this->fields, function($v) use ($type,$match){
      return $v->{$type}==$match;
    });
  }

  public function filterFieldsByMod($mod) {
    return $this->filterFields('mod', $mod);
  }

  public function filterFieldsBySource($source) {
    return $this->filterFields('target', $source);
  }

  public function getTableName($table) {
    $ret = (string)($table=='request' ? $table : "{$table}_{$this->table_suffix}");
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
      default:
        $msg = "Granularity '$gran' not found, defaulting to 'month'";
        AJAXSession::getInstance()->dualLog($msg,LOG_LEVEL_WARN);
        $ret = "1d";
        break;
    }
    return $ret;
  }

  public function listGroupFields() {
    return $this->filterFieldsByMod('group');
  }

  /* load report information from the database.
     $pagename = the name of the report
     $load_type = (0, 1, 2, 3)
                  0 = basic report information (always loaded)
                  1 = report properties only
                  2 = report fields only
                  3 = everything (default)
     */
  public function loadReport($name = NULL, $load_type = 3) {
    if (!$name) { $name = $this->report_name; }
    foreach (self::loadReportInfo($name) as $k=>$v) {
      $this->$k = $v;
    }
    if ($load_type && 1) {
      $this->properties = ModelReportProperty::loadAllProperties($this->id);
    }
    if ($load_type && 2) {
      $this->fields = ModelField::loadAllFields($this->id);
    }
  }

  public static function loadReportInfo($name) {
    if ($name) {
      $param = array(':name'=>$name);
      $query = "SELECT * FROM reports WHERE report_name = :name";
      $ret=self::getDataObjectRow($query, $param);
    } else {
      $ret = NULL;
    }
    return $ret;
  }

  public function prepareReport() {
    $this->analyzeIndexes();
    $this->buildSelectClauses();
    AJAXSession::getInstance()->log('all selects = '.print_r($this->_select,1),LOG_LEVEL_DEBUG);
    $this->buildTableClauses();
    AJAXSession::getInstance()->log('all tables = '.print_r($this->_tables,1),LOG_LEVEL_DEBUG);
    $this->buildGroupClauses();
    AJAXSession::getInstance()->log('all groups = '.print_r($this->_groups,1),LOG_LEVEL_DEBUG);
    $this->buildWhereClauses();
    AJAXSession::getInstance()->log('all where = '.print_r($this->_where,1),LOG_LEVEL_DEBUG);
  }

  public function run() {
    $this->prepareReport();
    $this->runReport();
  }

  public function runReport() {
    $this->rows = array();
    $q = $this->buildQuery();
    AJAXSession::getInstance()->log("full query: $q",LOG_LEVEL_DEBUG);
    AJAXSession::getInstance()->log("all params = ".print_r($this->_bind_params,1),LOG_LEVEL_DEBUG);
    $this->rows = $this->getDataObject($this->buildQuery(),$this->_bind_params);
  }
}