<?php
/*
  MenuItem class for BlueBird analytics
  Class for configurable navigation menu item.
*/
class BB_MenuItem {
  protected $_db = NULL;
  protected $_data = array();
  protected $_classes = array();

  public function __construct($d = array(), $load = false) {
    $this->_db = BB_Session::getInstance()->db;
    if ($load) {
      $this->loadFromDb($load);
    }
    if ($d) {
      $this->loadFromArray($d);
    }
  }

  public function __get($n) {
    return array_value($n, $this->_data, NULL);
  }

  public function __set($n, $v) {
    $x = array_value($n, $this->_data);
    switch($n) {
      case 'id':
      case 'menu_id':
      case 'parent_id':
      case 'is_link':
        break;
      case 'menu_title':
      case 'content_title':
      case 'data_name':
      case 'icon_name':
      case 'target':
        $this->_data[$n] = $v;
        break;
      case 'weight':
        $this->_data[$n] = (int)$v;
        break;
      case 'active':
        $this->_data[$n] = (boolean)$v;
        break;
      default:
        $this->$n = $v;
        break;
    }
    $this->_processData();
  }

  protected function _processData() {
    $this->_data['is_link'] = (boolean)$this->_data['target'];
    if (!$this->_data['data_name']) {
      $tname = $this->_data['menu_title'] . $this->_data['content_title'];
      $this->_data['data_name'] = strtolower(preg_replace('/[^-a-z_0-9]/i','',$tname));
    }
  }

  public function addClass($v) {
    $this->_classes[] = $v;
  }

  public function loadFromArray($d = array()) {
    if (is_array($d)) {
      foreach ($d as $k=>$v) {
        $this->_data[$k] = $v;
      }
      $this->_processData();
    }
  }

  public function loadFromDb($id = NULL) {
    $id=(int)$id;
    $arr = array();
    if ($id) {
      $sql = "SELECT * FROM menuitem WHERE id=:id";
      $stmt = $this->_db->prepare($sql);
      $stmt->execute(array(':id'=>$id));
      $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    $this->loadFromArray($arr);
  }

  public function removeClass($v) {
    if ($k = array_search($v, $this->_classes)) {
      unset($this->_classes[$k]);
    }
  }

  public function render($is_parent = false) {
    $is_link = ($this->is_link && $this->target);
    if ($is_parent) { $this->_classes[] = 'dropdown'; }
    $ret = '<li' . (count($this->_classes) ? ' class="'.implode(' ',$this->_classes).'"' : '') . '>' .
            '<a' . ($is_parent ? ' data-toggle="dropdown" class="dropdown-toggle"' : '') .
            ' href="' . ($is_link ? $this->target : '#') . '">' .
            ($this->icon_name ? '<i class="fa '.$this->icon_name.'"></i>' : '') . $this->menu_title .
            ($is_parent ? '<b class="caret"></b>' : '') .
            '</a></li>';
    return $ret;
  }
}
