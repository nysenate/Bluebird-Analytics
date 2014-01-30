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

$g_log_file = get_log_file($config['debug']);
$g_log_level = get_log_level($config['debug']);
$dbcon = get_db_connection($config['database']);
if ($dbcon === false) {
  send_response(500, "An internal error has occurred.");
}

///////////////////////////////
// Validate $_GET parameters
///////////////////////////////
foreach(array('type', 'instance_name', 'install_class', 'view', 'start_datetime', 'end_datetime') as $key) {
  if (!isset($_GET[$key])) {
    send_response(400, "A '$key' parameter is required for all queries.");
  }
  $$key = clean_string($_GET[$key]);
}

///////////////////////////////
// Dispatch request
///////////////////////////////
switch ($type) {
  case 'list': // list views show tables of data.
    do_list($view, $install_class, $instance_name, $start_datetime, $end_datetime, $dbcon);
    break;
  case 'summary': // summary are snippets show at the top of the page.
    do_summary($view, $install_class, $instance_name, $start_datetime, $end_datetime, $dbcon);
    break;
  case 'chart': // chart views are graphed.
    do_chart($view, $install_class, $instance_name, $start_datetime, $end_datetime, $dbcon);
    break;
  default: // Send back an error code.
    send_response(400, "Type '$type' must be one of 'list', 'summary', or 'chart'.");
    break;
}

exit(0);


function do_list($view, $install_class, $instance_name, $start_datetime, $end_datetime, $dbcon) {
  foreach(array('list_size', 'list_offset') as $key) {
    if (!isset($_GET[$key])) {
      send_response(400, "A '$key' parameter is required for all chart queries.");
    }
    $$key = clean_string($_GET[$key]);
  }

  // All queries share a similar basic scoping
  $WHERE = "
        WHERE instance_type = '$install_class'
          AND time BETWEEN '$start_datetime' AND '$end_datetime'
          ".($instance_name != 'ALL' ? "AND instance_name = '$instance_name'" : "");

  switch ($view) {
    case 'dashboard':
      // top 10 active instances, users
      $result = $dbcon->query("
        SELECT
          instance_name,
          count(distinct remote_ip) as users,
          count(*) as requests
        FROM request
        $WHERE
        GROUP BY instance_name
        ORDER BY requests DESC
        LIMIT $list_size OFFSET $list_offset
      ");
      $top_instances = $result->fetchAll(PDO::FETCH_ASSOC);
      $result->closeCursor();

      $result = $dbcon->query("
        SELECT
          remote_ip,
          instance_name,
          count(*) as requests
        FROM request
        $WHERE
        GROUP BY remote_ip
        ORDER BY requests DESC
        LIMIT $list_size OFFSET $list_offset
      ");
      $top_users = $result->fetchAll(PDO::FETCH_ASSOC);
      $result->closeCursor();

      send_response(200, "success", array('top_instances' => $top_instances, 'top_users' => $top_users));
      break;
    case 'performance':
      // slow page log
      $result = $dbcon->query("
        SELECT
          path,
          count(*) as path_views,
          CAST(IFNULL(avg(response_time)/1000000, 0) AS DECIMAL(12,2)) as avg_response_time
        FROM request
        $WHERE
        GROUP BY request.path
        HAVING avg_response_time >= 2
        ORDER BY avg_response_time DESC
        LIMIT $list_size OFFSET $list_offset
      ");
      send_response(200, "success", array('slow_queries' => $result->fetchAll(PDO::FETCH_ASSOC)));
      break;
    case 'content':
      // list pages, visit count, average render speed
      $result = $dbcon->query("
        SELECT
          path,
          count(*) as path_views,
          CAST(IFNULL(avg(response_time)/1000000, 0) AS DECIMAL(12,2)) as avg_response_time
        FROM request
        $WHERE
        GROUP BY request.path
        ORDER BY path_views DESC
        LIMIT $list_size OFFSET $list_offset
      ");
      send_response(200, "success", array('most_viewed' => $result->fetchAll(PDO::FETCH_ASSOC)));
    case 'audience':
      // list of active instances, number of active users, total page views,

      break;
    case 'behavior':
      // list of instance

      break;
  }
  echo "do_list for $view [$start_datetime TO $end_datetime]";
}

function do_summary($view, $install_class, $instance_name, $start_datetime, $end_datetime, $dbcon) {
  // All queries share a similar basic scoping
  $WHERE = "
        WHERE instance_type = '$install_class'
          AND time BETWEEN '$start_datetime' AND '$end_datetime'
          ".($instance_name != 'ALL' ? "AND instance_name = '$instance_name'" : "");

  switch ($view) {
    case 'dashboard':
      $result = $dbcon->query("
        SELECT
          count(*) as page_views,
          count(distinct path) as _distinct_pages,
          count(distinct remote_ip) as unique_ips,
          IFNULL(sum(response_code = 503),0) as 503_errors,
          IFNULL(sum(response_code = 500),0) as 500_errors,
          count(distinct instance_name) as active_instances
        FROM request
        $WHERE
      ");
      send_response(200, "success", $result->fetch(PDO::FETCH_ASSOC));
      break;
    case 'performance':
      $result = $dbcon->query("
        SELECT
          count(*) as page_views,
          IFNULL(sum(response_code = 503),0) as 503_errors,
          IFNULL(sum(response_code = 500),0) as 500_errors,
          IFNULL(avg(response_time),0) as avg_response_time
        FROM request
        $WHERE
      ");
      send_response(200, "success", $result->fetch(PDO::FETCH_ASSOC));
      break;
    case 'content':
      // total pages served
      // average render time
      break;
    default:
      send_response(400, "View '$view' must be one of 'dashboard', 'performance', 'content'.");
      break;
  }
  echo "do_summary for $view [$start_datetime TO $end_datetime]";
}

function do_chart($view, $install_class, $instance_name, $start_datetime, $end_datetime, $dbcon) {
  foreach(array('granularity') as $key) {
    if (!isset($_GET[$key])) {
      send_response(400, "A '$key' parameter is required for all chart queries.");
    }
    $$key = clean_string($_GET[$key]);
  }

  // Granularity works by chopping off the date to the specified granularity
  // This means that 11:23 - 13:23 will cover 11-12, 12-13, and 13-14 hour blocks.
  // But 11:00 - 13:00 will only cover 11-12, 12-13.
  switch ($granularity) {
    case 'minute': $date_format = "%Y-%m-%d %H:%i:00"; break;
    case 'hour': $date_format = "%Y-%m-%d %H:00:00"; break;
    case 'day': $date_format = "%Y-%m-%d 00:00:00"; break;
    case 'month': $date_format = "%Y-%m-01 00:00:00"; break;
    default: send_response(400, "Granularity '$granularity' must be one of 'minute', 'hour', 'day', 'month'.");
  }

  // All queries share a similar basic scoping
  $WHERE = "
        WHERE instance_type = '$install_class'
          AND time BETWEEN '$start_datetime' AND '$end_datetime'
          ".($instance_name != 'ALL' ? "AND instance_name = '$instance_name'" : "");

  switch ($view) {
    case 'dashboard':
      $result = $dbcon->query("
        SELECT
          DATE_FORMAT(time, '$date_format') as chart_time,
          count(*) as page_views
        FROM request
        $WHERE
        GROUP BY chart_time
      ");
      send_response(200, "success", $result->fetchAll(PDO::FETCH_ASSOC));
      // pageviews
      //
    case 'performance':
      $result = $dbcon->query("
        SELECT
          DATE_FORMAT(time, '$date_format') as chart_time,
          IFNULL(avg(response_time), 0) as avg_response_time,
          IFNULL(sum(response_code = 500),0) as 500_errors,
          IFNULL(sum(response_code = 503),0) as 503_errors,
          count(*) as page_views
        FROM request
        $WHERE
        GROUP BY chart_time
      ");
      send_response(200, "success", $result->fetchAll(PDO::FETCH_ASSOC));
      // render speed
      // uptime (absence of 500 / db offline )
      // application error
      // db offline

    case 'content':
      // pageviews

    case 'audience':
      // pageviews

    case 'behavior':
      // pageviews
  }
  echo "do_chart for $view [$start_datetime TO $end_datetime]";
}


function clean_string($input) {
  return preg_replace('/[^-a-zA-Z0-9: _,]/', '', $input);
}


function send_response($code, $message, $data=NULL) {
  header("Content-Type: application/json; charset=UTF-8");
  http_response_code($code);
  echo json_encode(array(
      'code' => $code,
      'message' => $message,
      'data' => $data,
  ));
  exit(0);
}
