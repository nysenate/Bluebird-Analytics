<?php
date_default_timezone_set('America/New_York');
set_time_limit(60);

// add the library directory to the search path
set_include_path(get_include_path() . PATH_SEPARATOR . '../lib');

require_once 'utils.php';
require_once 'AJAXSession.php';

// session contains: configuration, cleaned request, PDO connection, response, logger
$session = AJAXSession::getInstance();
$response = &$session->response;

// check for parameters required in all requests.  Fail if any are not found.
// TODO this should be consolidated into AJAXSession
$fail = false;
$request = $session->req('req');
if (!$request) {
  $fail = true;
}

// verify the controller is available
$controllerName = "AJAXController".ucfirst($request);
if (!(
      (include "{$controllerName}.php")
      && class_exists($controllerName)
      && $controller=new $controllerName
      )) {
  $response->sendFatal("Could not instantiate handler for request '$request'",400);
}

// run the action and return the response
try {
  $response->data=$controller->go();
} catch (Exception $e) {
  $response->sendDBException($e);
}
$session->log("DATA=\n".var_export($response->data,1),LOG_LEVEL_DEBUG);
$response->send($controller->response_code);
