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
$config = load_config();
if ($config === false) {
  die("Unable to load the configuration.");
}

$g_log_file = get_log_file($config['debug']);
$g_log_level = get_log_level($config['debug']);
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

$navigation['users']['overview'] =  array('link'=>'#', 'content'=>'none','view'=>'noe','name'=>'Users','icon'=>'fa-users','about'=>'#');

$navigation['users']['list'] =  array('link'=>'/users/list', 'content'=>'users.php','view'=>'users','name'=>'User Overview','icon'=>'fa-users','about'=>'User Overview List');
$navigation['users']['details'] = array('link'=>'/users/details', 'content'=>'userdetails.php', 'view'=>'userdetails','name'=>'User Details','icon'=>'fa-sitemap','about'=>'User Details');

// $navigation['offices']['overview'] =  array('link'=>'/offices', 'content'=>'offices.php','view'=>'offices','name'=>'Offices','icon'=>'fa-building','about'=>'Offices');

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
    array('src'=>'/static/vendor/font-awesome-4.1.0/css/font-awesome.min.css'),
    array('src'=>'/static/vendor/opensans-v6/css/opensans.css'),
    array('src'=>'/static/vendor/ubuntu-v4/css/ubuntu.css'),
    array('src'=>'/static/vendor/morris-0.4.3/morris.min.css'),
    array('src'=>'/static/vendor/jquery.datatables-1.10.0/css/jquery.dataTables.css'),
    array('src'=>'/static/vendor/lou-multi-select-0.9.11/css/multi-select.css'),
    array('src'=>'/static/vendor/bootstrap-daterangepicker-1.3.12/daterangepicker-bs3.css'),
    array('src'=>'/static/vendor/silviomoreto-bootstrap-select-1.5.4/bootstrap-select.min.css'),
    array('src'=>'/static/css/sb-admin.css'),
  ),
  'js' => array(
    array('src'=>'/static/vendor/jquery-2.1.1.min.js'),
    array('src'=>'/static/vendor/bootstrap-3.1.0/bootstrap.min.js'),
    array('src'=>'/static/vendor/raphael-2.1.2.min.js'),
    array('src'=>'/static/vendor/morris-0.4.3/morris.min.js'),
    array('src'=>'/static/vendor/jquery.datatables-1.10.0/js/jquery.dataTables.min.js'),
    // array('src'=>'/static/vendor/jquery.datatables-1.10.0/js/jquery.dataTables.plugins.js'),
    array('src'=>'/static/vendor/lou-multi-select-0.9.11/js/jquery.multi-select.js'),
    array('src'=>'/static/vendor/jquery.cookie-1.4.1.js'),
    array('src'=>'/static/vendor/moment-2.5.1.min.js'),
    array('src'=>'/static/vendor/jquery.tablesorter.min.js'),
    array('src'=>'/static/vendor/bootstrap-daterangepicker-1.3.12/daterangepicker.js'),
    array('src'=>'/static/vendor/silviomoreto-bootstrap-select-1.5.4/bootstrap-select.min.js'),
    array('src'=>'/static/js/app.js'),
  )
);

$instances = array('123click','3rdparty','3rdpartystatewide','adams','addabbo','alesi','avella','ball','bonacic','boyle','breslin','carlucci','defrancisco','demcomms','demo','diaz','dilan','espaillat','example','farley','felder','flanagan','fuschillo','gallivan','gianaris','gipson','golden','griffo','grisanti','hannon','hassellthompson','hoylman','huntley','kennedy','klein','krueger','lanza','larkin','latimer','lavalle','libous','little','marcellino','marchione','martins','maziarz','montgomery','neison','nozzolio','obrien','omara','parker','peralta','perkins','ranzenhofer','ritchie','rivera','robach','ruralresources','saland','sampson','sanders','savino','sd83','sd95','sd98','sd99','serrano','seward','skelos','smith','squadron','stavisky','stewartcousins','template','tkaczyk','training1','training2','training3','training4','valesky','young','zeldin');

require_once('../lib/template_helpers.php');
include('layout.php');
?>
