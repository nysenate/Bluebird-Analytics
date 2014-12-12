<?php
// TODO create a generic data model class for Regular/AJAX session

// configure some internal PHP settings
date_default_timezone_set('America/New_York');
set_include_path(get_include_path() . PATH_SEPARATOR . '../lib');

// for development only
error_reporting(-1);
ini_set('display_errors', 'On');

require_once 'utils.php';
require_once 'BB_Session.php';
require_once 'BB_Menu.php';

// bootstrap the environment
$session = BB_Session::getInstance();

// menu 1 is the main navigation
$navmenu = new BB_Menu(1);
$request = array_value('req',$_GET,'dashboard');
$activemenu = $navmenu->findActive(array('target'=>"/{$request}"));
if (!$activemenu) {
  http_response_code(404);
  die("Navigation failure: Request key [$request] invalid");
}
$navmenu->addClassToTree($activemenu->id,'active open');

$product = array(
  'name' => 'Bluebird Analytics',
  'version' => '1.1.1',
  'release' => 'alpha',
  'last_update' => fetch_last_update_time($session->db),
);

$release_notes = '
  <div class="alert alert-success alert-dismissable cookie" data-version="'.$product['version'].'">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    Welcome to the <a class="alert-link" href="https://github.com/nysenate/Bluebird-CRM">'. $product['name'].'</a> '. $product['version']." ".$product['release'].'<br/>
    Feel free to poke around but remember this is still an '. $product['release'].' release.
  </div>';

$scripts = array(
  'css' => array(
    array('src'=>'/static/vendor/bootstrap-3.1.0/bootstrap.css'),
    array('src'=>'/static/vendor/font-awesome-4.1.0/css/font-awesome.min.css'),
    array('src'=>'/static/vendor/opensans-v6/css/opensans.css'),
    array('src'=>'/static/vendor/ubuntu-v4/css/ubuntu.css'),
    array('src'=>'/static/vendor/morris-0.4.3/morris.min.css'),
    array('src'=>'/static/vendor/jquery.datatables-1.10.3/css/jquery.dataTables.css'),
    array('src'=>'/static/vendor/lou-multi-select-0.9.11/css/multi-select.css'),
    array('src'=>'/static/vendor/bootstrap-daterangepicker-1.3.12/daterangepicker-bs3.css'),
    array('src'=>'/static/vendor/silviomoreto-bootstrap-select-1.5.4/bootstrap-select.min.css'),
    array('src'=>'/static/css/sb-admin.css'),
  ),
  'js' => array(
    array('src'=>'/static/vendor/jquery-2.1.1.min.js'),
    array('src'=>'/static/vendor/bootstrap-3.1.0/bootstrap.min.js'),
    array('src'=>'/static/vendor/raphael-2.1.2.min.js'),
    array('src'=>'/static/vendor/morris-0.4.3/morris.js'),
    array('src'=>'/static/vendor/jquery.datatables-1.10.3/js/jquery.dataTables.min.js'),
    array('src'=>'/static/vendor/lou-multi-select-0.9.11/js/jquery.multi-select.js'),
    array('src'=>'/static/vendor/jquery.cookie-1.4.1.js'),
    array('src'=>'/static/vendor/moment-2.5.1.min.js'),
    array('src'=>'/static/vendor/jquery.tablesorter.min.js'),
    array('src'=>'/static/vendor/bootstrap-daterangepicker-1.3.12/daterangepicker.js'),
    array('src'=>'/static/vendor/silviomoreto-bootstrap-select-1.5.4/bootstrap-select.min.js'),
    array('src'=>'/static/js/utility.js'),
    array('src'=>'/static/js/hashstorage.js'),
    array('src'=>'/static/js/analytics.reports.js'),
    array('src'=>'/static/js/NYSS.MessageBox.js'),
    array('src'=>'/static/js/app.js'),
  )
);

/* default to prod install class - analytics is currently using only prod logs */
$sql = "SELECT DISTINCT name FROM instance WHERE install_class='prod' ORDER BY name";
$instances = $session->db->query($sql)->fetchAll(PDO::FETCH_COLUMN);

require_once 'layout.php';
?>
