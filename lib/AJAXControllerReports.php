<?php
/*
  AJAX Controller class for BlueBird analytics
  Provides a base abstract class for controllers
*/

require_once 'AJAXController.php';
require_once 'ModelReportSummary.php';
require_once 'ModelReportChart.php';
require_once 'ModelReportList.php';

class AJAXControllerReports extends AJAXController {
  // response-generating method to call if no action is included in the request
  protected static $_default_action = 'get';
  // filter properties required to be in each request
  protected static $_required_filters = array('instance', 'starttime', 'endtime', 'granularity');
  // a pointer to the global session object
  protected $session;
  // the page name referring the current request
  public $page_name;
  // an array of report data objects
  public $reports;
  // an array of filter items
  public $filters;
  // if a request fails validation, this is status code to return
  public $response_code;
  // indicates if reports have been loaded already
  public $is_loaded = false;

  public function __construct($pagename=NULL) {
    parent::__construct();
    $this->reports = array();
    $this->response_code = NULL;
    $pagename = $pagename ? $pagename : $this->session->req('view');
    $this->getReportsForPage($pagename);
  }

  public function getReportsForPage($pagename=NULL, $refresh=false) {
    if ($refresh || !$this->is_loaded) {
      // get the base report information
      $this->reports = array();
      if (!$pagename) { $pagename = $this->page_name; }
      if ($pagename) {
        $param = array(':pagename'=>$pagename);
        $query = "SELECT a.report_name, c.property_value " .
                 "FROM reports a INNER JOIN report_display b ON a.id=b.report_id " .
                 "INNER JOIN report_properties c ON a.id=c.report_id AND c.property_type_id=1 " .
                 "WHERE b.page_name = :pagename";
        $load_reports = Model::getDataObject($query, $param);
        // for each report, retrieve the properties and fields
        foreach ($load_reports as $k=>$v) {
          $type = "ModelReport".ucfirst($v->property_value);
          $this->session->log("Instancing $type object",LOG_LEVEL_DEBUG);
          $this->reports[] = new $type($v->report_name, $this->session->req('filters'));
        }
        $this->page_name = $pagename;
      }
      $this->is_loaded=true;
    }
  }

  public function go() {

    // Reports only have one available action, but leave room for possibilities
    $this->action = $this->session->req('action',self::$_default_action);
    $this->session->response->setIdentifiers($this->session->req('req'), $this->action);

    // find the page name and load its reports
    $this->getReportsForPage($this->page_name);

    // validate any requirements before moving forward
    $fail = $this->validate();
    if ($fail) {
      $this->session->log('Request validation failed in '.__CLASS__,LOG_LEVEL_ERROR);
      return NULL;
    }

    // for each report, instantiate class for report type
    // and pull the data.  Add data object to response.
    $ret = array();
    foreach ($this->reports as $k=>$v) {
      $v->run();
      $ret[] = $v;
    }

    return $ret;
  }

  public function validate() {
    $ret = false;
    if (!$this->page_name) {
      $ret=true;
      $this->session->dualLog("No view found in request data",LOG_LEVEL_FATAL);
    } else {
      foreach(static::$_required_filters as $k) {
        if (!($this->session->req('filters')[$k])) {
          $this->session->dualLog("Required parameter '$k' is missing.",LOG_LEVEL_FATAL);
          $ret = true;
        } else {
          $this->filters[$k] = $this->session->req('filters')[$k];
        }
      }
      if (!count($this->reports)) {
        $this->session->dualLog("No report definitions available.",LOG_LEVEL_FATAL);
        $ret = true;
      }
    }
    if ($ret) { $this->response_code=400; }
    return $ret;
  }

}