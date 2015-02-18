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
  public $join_url_table = false;

  public function __construct() {
    $this->session = AJAXSession::getInstance();
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


}