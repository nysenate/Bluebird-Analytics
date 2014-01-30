<?php
/**
 * started with a MIT template offered graciously by startbootstrap.com/sb-admin
 * This is our header stuff that gets included in every page
 */

date_default_timezone_set('America/New_York');

// Default view is the dashboard

$url = explode('/', $_GET['request']);
// var_dump($url);
$request  = ($url[0] == 'index.php') ? 'dashboard' : $url[0] ;
$sub = (isset($url[1])) ? $url[1] : 'overview' ;

$product = array(
  'name' => 'BlueBird Analytics',
  'version' => '0.01',
  'release' => 'alpha'
);

$release_notes = '
  <div class="alert alert-success alert-dismissable cookie" data-version="'.$product['version'].'">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    Welcome to the <a class="alert-link" href="https://github.com/nysenate/Bluebird-CRM">'. $product['name'].'</a> '. $product['version']." ".$product['release'].'<br/>
    Feel free to poke around but remember this is still an '. $product['release'].' release.
  </div>';


$navigation['dashboard']['overview'] = array('link'=>'/dashboard', 'content'=>'dashboard.php', 'view'=>'dashboard','name'=>'Dashboard','icon'=>'fa-inbox','about'=>'Statistics Overview');

$navigation['performance']['overview'] = array('link'=>'/#', 'content'=>'#','view'=>'#','name'=>'Performance','icon'=>'fa-tachometer','about'=>'App & DB Reporting');

$navigation['performance']['dashboard'] = array('link'=>'/performance/dashboard', 'content'=>'performance.php', 'view'=>'dashboard','name'=>'Overview','icon'=>'fa-inbox','about'=>'Statistics Overview');
$navigation['performance']['dashboard2'] = array('link'=>'/performance/dashboard2', 'content'=>'performance.php', 'view'=>'dashboard','name'=>'Overview','icon'=>'fa-inbox','about'=>'Statistics Overview');
$navigation['performance']['dashboard3'] = array('link'=>'/performance/dashboard3', 'content'=>'performance.php', 'view'=>'dashboard','name'=>'Overview','icon'=>'fa-inbox','about'=>'Statistics Overview');
$navigation['performance']['dashboar3d'] = array('link'=>'/performance/dashboar3d', 'content'=>'performance.php', 'view'=>'dashboard','name'=>'Overview','icon'=>'fa-inbox','about'=>'Statistics Overview');
$navigation['performance']['dashboard4'] = array('link'=>'/performance/dashboard4', 'content'=>'performance.php', 'view'=>'dashboard','name'=>'Overview','icon'=>'fa-inbox','about'=>'Statistics Overview');


$navigation['content']['overview'] =  array('link'=>'/content', 'content'=>'content.php','view'=>'content','name'=>'Site Content','icon'=>'fa-list','about'=>'Content Consumption');

$navigation['users']['overview'] = array('link'=>'/users', 'content'=>'users.php','view'=>'users','name'=>'Users','icon'=>'fa-users','about'=>'User Overview');

$navigation['actions']['overview'] = array('link'=>'/actions', 'content'=>'actions.php','view'=>'actions','name'=>'BB Actions','icon'=>'fa-edit','about'=>'Bluebird User Actions');

$navigation[$request]['overview']['class'] = 'active open';
$navigation[$request][$sub]['class'] = 'active';


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

include('layout.php')
?>
