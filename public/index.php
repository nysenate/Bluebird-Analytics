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
  'version' => '1.1.3',
  'last_update' => fetch_last_update_time($session->db),
);

$release_notes = '
  <div class="alert alert-success alert-dismissable cookie" data-version="'.$product['version'].'">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    Welcome to <a class="alert-link" href="https://github.com/nysenate/Bluebird-Analytics">'.$product['name'].'</a> '.$product['version'].'<br/>
    If you have questions or comments about this service, please contact the <a href="mailto:bluebird.admin@nysenate.gov">Bluebird Administrator</a>.
  </div>';

$css_scripts = array(
    '/static/vendor/bootstrap-3.3.1/css/bootstrap.min.css',
    '/static/vendor/font-awesome-4.1.0/css/font-awesome.min.css',
    '/static/vendor/opensans-v6/css/opensans.css',
    '/static/vendor/ubuntu-v4/css/ubuntu.css',
    '/static/vendor/jquery.datatables-1.10.3/css/jquery.dataTables.css',
    '/static/vendor/bootstrap-daterangepicker-1.3.12/daterangepicker-bs3.css',
    '/static/vendor/silviomoreto-bootstrap-select-1.5.4/bootstrap-select.min.css',
    '/static/css/sb-admin.css'
);

$js_scripts = array(
    '/static/vendor/jquery-2.1.1.js',
    'http://code.jquery.com/ui/1.11.2/jquery-ui.js',
    '/static/vendor/bootstrap-3.3.1/js/bootstrap.min.js',
    'http://code.highcharts.com/highcharts.src.js',
    '/static/vendor/jquery.datatables-1.10.3/js/jquery.dataTables.min.js',
    '/static/vendor/jquery.cookie-1.4.1.js',
    '/static/vendor/moment-2.8.1.min.js',
    '/static/vendor/jquery.tablesorter.min.js',
    '/static/vendor/bootstrap-daterangepicker-1.3.12/daterangepicker.js',
    '/static/vendor/silviomoreto-bootstrap-select-1.5.4/bootstrap-select.min.js',
    '/static/js/utility.js',
    '/static/js/hashstorage.js',
    '/static/js/analytics.reports.js',
    '/static/js/NYSS.MessageBox.js',
    '/static/js/app.js'
);

// add custom JS if it exists
if (file_exists("static/js/$request.js")) {
  $js_scripts[] = "/static/js/$request.js";
}

/* default to prod install class - analytics is currently using only prod logs */
$sql = "SELECT DISTINCT name FROM instance WHERE install_class='prod' ORDER BY name";
$instances = $session->db->query($sql)->fetchAll(PDO::FETCH_COLUMN);

require_once 'layout.php';
?>
