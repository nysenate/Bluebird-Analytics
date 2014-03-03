<?php
/**
 * started with a MIT template offered graciously by startbootstrap.com/sb-admin
 * This is our header stuff that gets included in every page
 */
require_once('../lib/utils.php');

global $config;
$config = load_config();
$request = 'dashboard';
$sub = 'overview';

date_default_timezone_set('America/New_York');

// Default view is the dashboard overview
if (!isset($_GET['req'])) {
  die("Please check your .htaccess file to confirm that rewrite rules are working.");
}

$req = explode('/', $_GET['req'], 2);
if (!empty($req[0])) {
  $request = $req[0];
}
if (!empty($req[1])) {
  $sub = $req[1];
}

$product = array(
  'name' => 'Bluebird Analytics',
  'version' => '1.0',
  'release' => 'alpha'
);

$release_notes = '
  <div class="alert alert-success alert-dismissable cookie" data-version="'.$product['version'].'">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    Welcome to the <a class="alert-link" href="https://github.com/nysenate/Bluebird-CRM">'. $product['name'].'</a> '. $product['version']." ".$product['release'].'<br/>
    Feel free to poke around but remember this is still an '. $product['release'].' release.
  </div>';


$navigation['dashboard']['overview'] = array('link'=>'/dashboard', 'content'=>'dashboard.php', 'view'=>'dashboard','name'=>'Dashboard','icon'=>'fa-inbox','about'=>'Statistics Overview');

// $navigation['performance']['overview'] = array('link'=>'/#', 'content'=>'#','view'=>'#','name'=>'Performance','icon'=>'fa-tachometer','about'=>'App & DB Reporting');

$navigation['performance']['overview'] = array('link'=>'/performance', 'content'=>'performance.php', 'view'=>'performance','name'=>'Performance','icon'=>'fa-inbox','about'=>'Statistics Overview');
// $navigation['performance']['dashboard2'] = array('link'=>'/performance/dashboard2', 'content'=>'performance.php', 'view'=>'dashboard','name'=>'Overview','icon'=>'fa-inbox','about'=>'Statistics Overview');
// $navigation['performance']['dashboard3'] = array('link'=>'/performance/dashboard3', 'content'=>'performance.php', 'view'=>'dashboard','name'=>'Overview','icon'=>'fa-inbox','about'=>'Statistics Overview');
// $navigation['performance']['dashboar3d'] = array('link'=>'/performance/dashboar3d', 'content'=>'performance.php', 'view'=>'dashboard','name'=>'Overview','icon'=>'fa-inbox','about'=>'Statistics Overview');
// $navigation['performance']['dashboard4'] = array('link'=>'/performance/dashboard4', 'content'=>'performance.php', 'view'=>'dashboard','name'=>'Overview','icon'=>'fa-inbox','about'=>'Statistics Overview');

$navigation['datatable']['overview'] =  array('link'=>'/datatable', 'content'=>'datatable.php','view'=>'datatable','name'=>'Datatable','icon'=>'fa-list','about'=>'Datatable');

// $navigation['users']['overview'] = array('link'=>'/users', 'content'=>'users.php','view'=>'users','name'=>'Users','icon'=>'fa-users','about'=>'User Overview');

// $navigation['actions']['overview'] = array('link'=>'/actions', 'content'=>'actions.php','view'=>'actions','name'=>'BB Actions','icon'=>'fa-edit','about'=>'Bluebird User Actions');

if (!isset($navigation[$request])) {
  die("Navigation failure: Request key [$request] invalid");
}
else if (!isset($navigation[$request][$sub])) {
  die("Navigation failure: Sub-request key [$sub] invalid");
}

$navigation[$request]['overview']['class'] = 'active open';
$navigation[$request][$sub]['class'] = 'active';
$layout_content = $navigation[$request][$sub]['content'];

$scripts = array(
  'css' => array(
    array('src'=>'/static/vendor/bootstrap-3.1.0/bootstrap.css'),
    array('src'=>'/static/vendor/font-awesome-4.0.3/css/font-awesome.min.css'),
    array('src'=>'/static/vendor/opensans-v6/css/opensans.css'),
    array('src'=>'/static/vendor/ubuntu-v4/css/ubuntu.css'),
    array('src'=>'/static/vendor/morris-0.4.3/morris.min.css'),
    array('src'=>'/static/vendor/jquery.datatables-1.9.4/css/jquery.dataTables.css'),
    array('src'=>'/static/vendor/lou-multi-select-0.9.11/css/multi-select.css'),
    array('src'=>'/static/vendor/bootstrap-daterangepicker-1.3.2/daterangepicker.css'),
    array('src'=>'/static/vendor/bootstrap-select-1.4.2/bootstrap-select.min.css'),
    array('src'=>'/static/css/sb-admin.css'),
  ),
  'js' => array(
    array('src'=>'/static/vendor/jquery-2.1.0.min.js'),
    array('src'=>'/static/vendor/bootstrap-3.1.0/bootstrap.min.js'),
    array('src'=>'/static/vendor/raphael-2.1.2.min.js'),
    array('src'=>'/static/vendor/morris-0.4.3/morris.min.js'),
    array('src'=>'/static/vendor/jquery.datatables-1.9.4/js/jquery.dataTables.min.js'),
    array('src'=>'/static/vendor/jquery.datatables-1.9.4/js/jquery.dataTables.plugins.js'),
    array('src'=>'/static/vendor/lou-multi-select-0.9.11/js/jquery.multi-select.js'),
    array('src'=>'/static/vendor/jquery.cookie-1.4.0.js'),
    array('src'=>'/static/vendor/bootstrap-daterangepicker-1.3.2/daterangepicker.js'),
    array('src'=>'/static/vendor/moment-2.5.1.min.js'),
    array('src'=>'/static/vendor/bootstrap-select-1.4.2/bootstrap-select.min.js'),
    array('src'=>'/static/js/app.js'),
  )
);

$instances = array('skelos', 'breslin', 'fuschillo', 'marchione');

require_once('../lib/template_helpers.php');
include('layout.php');
?>
