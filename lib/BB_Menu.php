<?php
/*
  Menu class for BlueBird analytics
  Class for configurable navigation menu.

  TODO:implement this class to enable storage of multiple independent menus
*/

require_once 'BB_MenuItem.php';

class BB_Menu {
  protected $_db = NULL;
  protected $_classes = array();
  public $items = array();
  public $menu_id = '';
  protected $_is_cached = false;

  public function __construct($id = NULL, $active_only = true) {
    $this->_db = BB_Session::getInstance()->db;
    $this->load($id, $active_only);
    $this->_classes = array('nav','navbar-nav','side-nav');
  }

  public function addClassToTree($id, $c) {
    $all = $this->getArray($this->_is_cached);
    foreach ($all as $k=>$v) {
      if ($k==$id) {
        $v->addClass($c);
        if ($v->parent_id != 0) { $this->addClassToTree($v->parent_id,$c); }
      }
    }
  }

  public function findActive($a = array()) {
    $ret=NULL;
    $all = $this->getArray($this->_is_cached);
    foreach ($all as $k=>$v) {
      $found = true;
      foreach ($a as $kk=>$vv) {
        if ($v->$kk != $vv) { $found = false; }
      }
      if ($found) {
        $ret = $v;
        break;
      }
    }
    return $ret;
  }

  public function getArray($usecache=true) {
    static $cached = array();

    if (!count($cached) || !$usecache) {
      $cached = array();
      foreach ($this->items as $lvl=>$lvlv) {
        foreach ($lvlv as $k=>$v) {
          $cached[$v->id] = $v;
        }
      }
      $this->_is_cached = true;
    }
    return $cached;
  }

  public function load($id = 0, $active_only = true) {
    $id = (int)$id;
    $arr = array();
    if ($id) {
      $sql = "SELECT * FROM menuitem WHERE menu_id=:id " .
              ($active_only ? "AND active=1 " : '') .
              "ORDER BY parent_id, weight, menu_title";
      $stmt = $this->_db->prepare($sql);
      $stmt->execute(array(':id'=>$id));
      $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    $this->setitems($arr);
  }

  public function render() {
    $ret = '<ul' .
            ($this->menu_id ? ' id="' . $this->menu_id . '"' : '') .
            (count($this->_classes) ? ' class="' . implode(' ',$this->_classes) . '"' : '') .
            '>' .
            self::renderLevel($this->items,0) .
            '</ul>';
    return $ret;
  }
  public static function renderLevel($items, $lvl=0) {
    $ret = '';
    if (array_key_exists($lvl, $items) && count($items[$lvl])) {
      foreach ($items[$lvl] as $k=>$v) {
        $is_parent = (array_key_exists($v->id, $items) && count($items[$v->id]));
        $ret .= $v->render($is_parent);
        if ($is_parent) {
          $ret = preg_replace('/\<\/li\>$/','',$ret);
          $ret .= '<ul class="dropdown-menu">' . self::renderLevel($items,$v->id) . '</ul></li>';
        }
      }
    }
    return $ret;
  }

  public function setItems($items) {
    $this->_is_cached = false;
    $this->items = array();
    foreach ($items as $k=>$v) {
      $lvl = (int)$v['parent_id'];
      if (!array_value($lvl,$this->items)) {
        $this->items[$lvl] = array();
      }
      $this->items[$lvl][] = new BB_MenuItem($v);
    }
    foreach ($this->items as $k=>$v) {
      usort($v, function($a,$b) {
        $order = ($a->weight == $b->weight ? 0 : ($a->weight < $b->weight ? -1 : 1));
        if (!$order) {
          $order = ($a->menu_title == $b->menu_title ? 0 : ($a->menu_title < $b->menu_title ? -1 : 1));
        }
        if (!$order) {
          $order = ($a->id == $b->id ? 0 : ($a->id < $b->id ? -1 : 1));
        }
        return $order;
      });
    }
  }

}