<?php
/**
 * Bluebird Analytics API
 *
 */
date_default_timezone_set('America/New_York');
require(realpath(dirname(__FILE__).'/../lib/utils.php'));

///////////////////////////////
// Bootstrap the environment
///////////////////////////////
$config = load_config();
if ($config === false) {
  send_response(500, "An internal error has occurred.");
}

$g_log_file = get_log_file($config['debug']['file']);
$g_log_level = $config['debug']['level'];
$dbcon = get_db_connection($config['database']);
if ($dbcon === false) {
  send_response(500, "An internal error has occurred.");
}




