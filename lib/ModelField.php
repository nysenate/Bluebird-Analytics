<?php
/*
  Model class for Report Field objects
*/

require_once 'Model.php';

class ModelField extends Model {
  public $target;
  protected static $_field_definitions = NULL;

  public function __construct($init=NULL) {
    if ($init) {
      $this->id = $init->id;
      $this->report_id = $init->report_id;
      $this->def_id = $init->field_def_id;
      $this->mod = $init->aggregate;
      $this->format = $init->fmtcode;
      $this->sort_order = $init->sort_order;
      $this->abs_order = abs($init->sort_order);
      $this->name = $init->name;
      $this->target = $init->source_table;
      $this->sql = $init->select_sql;
      $this->calculated = (boolean)$init->calculated;
    }
  }

  public function generateSelect() {
    $sel = $this->applyMod($this->generateTableAlias() . $this->sql);
    return $this->applyFormat($sel) . " AS `{$this->name}`";
  }

  public function generateTableAlias() {
    $table_prefix = '';
    if (!$this->calculated) {
      $table_prefix = in_array($this->target, array('request','uniques','summary'))
                      ? 'dt.'
                      : "{$this->target}.";
    }
    return $table_prefix;
  }

  public function applyFormat($str) {
    $ret = $str;
    // get precision, if provided
    $p = $has_p = 0;
    $f = $this->format;
    if (preg_match('/(.+)\|([0-9]+)$/',$f,$p)) {
      $has_p = true;
      $f = $p[1];
      $p = $p[2];
    }
    if ($f && $f!='none') { $ret="IFNULL($ret,0)"; }
    switch($f) {
      case 'int':        $ret = "ROUND($ret,0)"; break;
      case 'intperk':    $ret = "ROUND($ret/1000,0)"; break;
      case 'intcomma':   $ret = "FORMAT($ret,0)"; break;
      case 'floatcomma': $ret = "FORMAT($ret,".($has_p ? $p : 4).")"; break;
      case 'floatperk':  $ret = "FORMAT($ret/1000,".($has_p ? $p : 4).")"; break;
      case 'percent':    $ret = "CONCAT(FORMAT($ret,".($has_p ? $p : 2)."),'%')"; break;
      // old microsec, but 's' suffix screws up datatables sorting on front end
      //case 'microsec':   $ret = "CONCAT(FORMAT($ret/1000000,".($has_p ? $p : 2)."),'s')"; break;
      case 'microsec':   $ret = "FORMAT($ret/1000000,".($has_p ? $p : 2).")"; break;
      case 'none':       break;
      default:
        $ret = $str;
        if ($f) {
          AJAXSession::getInstance()->log(
              "Invalid format '$f' for field select {$ret}",LOG_LEVEL_WARN
          );
          AJAXSession::getInstance()->response->addError(
              AJAX_ERR_WARN,"Invalid format '$f' ignored for field select {$ret}"
          );
        }
    }
    return $ret;
  }

  public function applyMod($str) {
    $agg = $str;
    if ($str) {
      switch($this->mod) {
        case 'count': $agg = "COUNT({$str})"; break;
        case 'countd':$agg = "COUNT(DISTINCT {$str})"; break;
        case 'sum':   $agg = "SUM({$str})"; break;
        case 'avg':   $agg = "AVG({$str})"; break;
        case 'calc':
        case 'none':  $agg = $str; break;
      }
    }
    return $agg;
  }

  public static function loadAllFields($id) {
    $param = array(':report_id'=>(int)$id);
    $query = "SELECT a.id, a.report_id, a.field_def_id, a.aggregate, a.fmtcode, " .
             "a.sort_order, b.name, b.source_table, b.select_sql, b.calculated " .
             "FROM report_fields a INNER JOIN report_field_defs b ON " .
             "a.field_def_id=b.id WHERE a.report_id = :report_id";
    if ((int)$id) {
      $tret = self::getDataObject($query, $param);
      $ret = array();
      foreach ($tret as $k=>$v) {
        $ret[] = new ModelField($v);
      }
    } else {
      $ret = NULL;
    }
    return $ret;
  }

}