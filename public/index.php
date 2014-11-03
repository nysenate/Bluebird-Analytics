<?php
/**
 * started with a MIT template offered graciously by startbootstrap.com/sb-admin
 * This is our header stuff that gets included in every page
 */
require_once('../lib/utils.php');
date_default_timezone_set('America/New_York');
error_reporting(-1);
ini_set('display_errors', 'On');

///////////////////////////////
// Bootstrap the environment
///////////////////////////////
$g_log_level = WARN;
$g_log_file = null;

$config = load_config();
if ($config === false) {
  die("Unable to load the configuration.");
}

if (isset($config['debug']['level'])) {
  $g_log_level = (int)$config['debug']['level'];
}

if (isset($config['debug']['file'])) {
  $g_log_file = get_log_file($config['debug']['file']);
}

$dbcon = get_db_connection($config['database']);
if ($dbcon === false) {
  die("Unable to connect to the database.");
}

///////////////////////////////
// Validate $_GET parameters
///////////////////////////////
if (!isset($_GET['req'])) {
  die("Please check your .htaccess file to confirm that rewrite rules are working.");
}

// Default view is the dashboard overview
$request = 'dashboard';
$sub = 'overview';
$req = explode('/', $_GET['req'], 2);

// our url rewriting wasn't allowing me to access GET vars
// so here's a hack
$uri = parse_url($_SERVER['REQUEST_URI']);
if (isset($uri['query'])) {
  parse_str($uri['query'], $tmp);
  foreach ($tmp as $key => $value) {
    $_GET[$key]=$value;
  }
}
unset($tmp,$uri);


if (!empty($req[0])) {
  $request = $req[0];
}
if (!empty($req[1])) {
  $sub = $req[1];
}

$product = array(
  'name' => 'Bluebird Analytics',
  'version' => '1.0',
  'release' => 'alpha',
  'last_update' => fetch_last_update_time($dbcon),
);

$release_notes = '
  <div class="alert alert-success alert-dismissable cookie" data-version="'.$product['version'].'">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    Welcome to the <a class="alert-link" href="https://github.com/nysenate/Bluebird-CRM">'. $product['name'].'</a> '. $product['version']." ".$product['release'].'<br/>
    Feel free to poke around but remember this is still an '. $product['release'].' release.
  </div>';

/* TODO: This should be database driven.  this is unmanageable */
$navigation['dashboard']['overview']   = array('link'=>'/dashboard',
                                               'content'=>'dashboard.php',
                                               'view'=>'dashboard',
                                               'name'=>'Dashboard',
                                               'icon'=>'fa-inbox',
                                               'about'=>'Statistics Overview'
                                               );
$navigation['performance']['overview'] = array('link'=>'/performance',
                                               'content'=>'performance.php',
                                               'view'=>'performance',
                                               'name'=>'Performance',
                                               'icon'=>'fa-inbox',
                                               'about'=>'Statistics Overview'
                                               );
$navigation['users']['overview']       = array('link'=>'#',
                                               'content'=>'none',
                                               'view'=>'none',
                                               'name'=>'Users',
                                               'icon'=>'fa-users',
                                               'about'=>'#'
                                               );
$navigation['users']['list']           = array('link'=>'/users/list',
                                               'content'=>'users.php',
                                               'view'=>'users',
                                               'name'=>'User Overview',
                                               'icon'=>'fa-users',
                                               'about'=>'User Overview List'
                                               );
$navigation['users']['details']        = array('link'=>'/users/details',
                                               'content'=>'userdetails.php',
                                               'view'=>'userdetails',
                                               'name'=>'User Details',
                                               'icon'=>'fa-sitemap',
                                               'about'=>'User Details'
                                               );
$navigation['datatable']['overview']   = array('link'=>'/datatable',
                                               'content'=>'datatable.php',
                                               'view'=>'datatable',
                                               'name'=>'Datatable',
                                               'icon'=>'fa-list',
                                               'about'=>'Datatable'
                                               );

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
    array('src'=>'/static/js/analytics.widgets.js'),
    array('src'=>'/static/js/app.js'),
  )
);

/* default to prod install class - analytics is currently using only prod logs */
$sql = "SELECT DISTINCT name FROM instance WHERE install_class='prod' ORDER BY name";
$instances = $dbcon->query($sql)->fetchAll(PDO::FETCH_COLUMN);

require_once '../lib/template_helpers.php';
include 'layout.php';
?>
