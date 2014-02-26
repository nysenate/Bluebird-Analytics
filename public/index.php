<?php
/**
 * started with a MIT template offered graciously by startbootstrap.com/sb-admin
 * This is our header stuff that gets included in every page
 */
require_once('../lib/utils.php');

global $config;
$config = load_config();
$request = 'overview';
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
  'version' => '1.0.1',
  'release' => 'alpha'
);

$release_notes = '
  <div class="alert alert-success alert-dismissable cookie" data-version="'.$product['version'].'">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    Welcome to the <a class="alert-link" href="https://github.com/nysenate/Bluebird-CRM">'. $product['name'].'</a> '. $product['version']." ".$product['release'].'<br/>
    Feel free to poke around but remember this is still an '. $product['release'].' release.
  </div>';

$navigation['overview']['overview'] = array('link'=>'/overview', 'content'=>'overview.php', 'view'=>'overview','name'=>'Overview','icon'=>'fa-lightbulb-o','about'=>'Bluebird Overview');

$navigation['performance']['overview'] = array('link'=>'#', 'content'=>'performance.php', 'view'=>'performance','name'=>'Performance','icon'=>'fa-caret-square-o-down','about'=>'Statistics Overview');
$navigation['performance']['statistics'] = array('link'=>'/performance/statistics', 'content'=>'performance.php', 'view'=>'statistics','name'=>'Statistics','icon'=>'fa-tachometer','about'=>'Statistics Overview');
$navigation['performance']['pagespeed'] = array('link'=>'/performance/speed', 'content'=>'pagespeed.php', 'view'=>'pagespeed','name'=>'Page Speed','icon'=>'fa-flash','about'=>'How are the pages performing');

$navigation['content']['overview'] =  array('link'=>'/content', 'content'=>'content.php','view'=>'content','name'=>'Site Content','icon'=>'fa-list','about'=>'Content Consumption');

$navigation['users']['overview'] = array('link'=>'/users', 'content'=>'users.php','view'=>'users','name'=>'Users','icon'=>'fa-users','about'=>'User Overview');


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
    array('src'=>'/css/bootstrap.css'),
    array('src'=>'/css/sb-admin.css'),
    array('src'=>'/css/font-awesome.min.css'),
    array('src'=>'/css/openSans.css'),
    array('src'=>'/css/ubuntu.css'),
    array('src'=>'/css/morris-0.4.3.min.css'),
    array('src'=>'/css/jquery.dataTables-1.9.4.css'),
    array('src'=>'/css/daterangepicker-bs3.css'),
    array('src'=>'/css/bootstrap-select.min.css'),
  ),
  'js' => array(
    array('src'=>'/js/jquery-1.10.2.js'),
    array('src'=>'/js/bootstrap.js'),
    array('src'=>'/js/raphael-min.js'),
    array('src'=>'/js/morris-0.4.3.min.js'),
    array('src'=>'/js/morris/chart-data-morris.js'),
    array('src'=>'/js/jquery.dataTables-1.9.4.min.js'),
    array('src'=>'/js/app.js'),
    array('src'=>'/js/jquery.cookie.js'),
    array('src'=>'/js/daterangepicker.js'),
    array('src'=>'/js/moment.min.js'),
    array('src'=>'/js/bootstrap-select.min.js'),
  )
);

$instances = array('skelos', 'breslin', 'fuschillo', 'marchione');

require_once('../lib/template_helpers.php');
include('layout.php');
?>
