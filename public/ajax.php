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
  case 'datatable': // data table views are called by datatables
    do_datatable($view, $install_class, $instance_name, $start_datetime, $end_datetime, $dbcon);
    break;
  default: // Send back an error code.
    send_response(400, "Type '$type' must be one of 'list', 'summary', or 'chart'.");
    break;
}

exit(0);


function do_list($view, $install_class, $instance_name, $start_datetime, $end_datetime, $dbcon)
{
  $table_suffix = get_table_suffix();

  foreach(array('list_size', 'list_offset') as $key) {
    if (!isset($_GET[$key])) {
      send_response(400, "A '$key' parameter is required for all chart queries.");
    }
    $$key = clean_string($_GET[$key]);
  }

  // All queries share a similar basic scoping
  $WHERE = "
        WHERE instance.id = instance_id
          AND instance.install_class = '$install_class'
          AND time BETWEEN '$start_datetime' AND '$end_datetime'
          ".($instance_name != 'ALL' ? "AND instance.name = '$instance_name'" : "");

  switch ($view) {
    case 'dashboard':
      // top 10 active instances, users
      $result = $dbcon->query("
        SELECT
          instance.name,
          count(distinct remote_ip) as users,
          sum(page_views) as requests
        FROM summary_$table_suffix, instance
        $WHERE
        GROUP BY instance.name
        ORDER BY requests DESC
        LIMIT $list_size OFFSET $list_offset
      ");
      $top_instances = $result->fetchAll(PDO::FETCH_ASSOC);
      $result->closeCursor();

      $result = $dbcon->query("
        SELECT
          remote_ip,
          instance.name,
          sum(page_views) as requests
        FROM summary_$table_suffix, instance
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
        FROM request, instance
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
        FROM request, instance
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


function do_summary($view, $install_class, $instance_name, $start_datetime, $end_datetime, $dbcon)
{
  $table_suffix = get_table_suffix();

  // All queries share a similar basic scoping
  $WHERE = "
        WHERE instance.id = instance_id
          AND instance.install_class = '$install_class'
          AND time BETWEEN '$start_datetime' AND '$end_datetime'
          ".($instance_name != 'ALL' ? "AND instance.name = '$instance_name'" : "");

  switch ($view) {
    case 'dashboard':
      $result = $dbcon->query("
        SELECT
          IFNULL(sum(page_views), 0) as page_views,
          IFNULL(sum(503_errors), 0) as 503_errors,
          IFNULL(sum(500_errors), 0) as 500_errors
        FROM summary_$table_suffix, instance
        $WHERE
      ");
      $numbers = $result->fetch(PDO::FETCH_ASSOC);
      $result->closeCursor();

      $result = $dbcon->query("
        SELECT type, count(distinct value) as total
        FROM uniques_$table_suffix, instance
        $WHERE
        GROUP BY type
      ");
      $uniques = uniques_to_row($result->fetchAll(PDO::FETCH_ASSOC));
      $result->closeCursor();

      $result = $dbcon->query("
        SELECT
          count(distinct instance_id) as active_instances,
          count(distinct remote_ip) as active_users
        FROM uniques_$table_suffix, instance
        $WHERE"
      );
      $instances = $result->fetch(PDO::FETCH_ASSOC);
      $result->closeCursor();

      send_response(200, "success", array(
        'page_views' => $numbers['page_views'],
        'distinct_pages' => $uniques['path'],
        'unique_ips' => $uniques['remote_ip'],
        '503_errors' => $numbers['503_errors'],
        '500_errors' => $numbers['500_errors'],
        'active_instances' => $instances['active_instances'],
        'unique_ips' => $instances['active_users'],
      ));
      break;
    case 'performance':
      $result = $dbcon->query("
        SELECT
          sum(page_views) as page_views,
          IFNULL(sum(503_errors),0) as 503_errors,
          IFNULL(sum(500_errors),0) as 500_errors,
          IFNULL(sum(response_time),0) as response_time
        FROM summary_$table_suffix, instance
        $WHERE
      ");
      $row = $result->fetch(PDO::FETCH_ASSOC);
      $row['avg_response_time'] = ((float)$row['response_time'])/$row['page_views'];
      send_response(200, "success", $row);
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

function do_chart($view, $install_class, $instance_name, $start_datetime, $end_datetime, $dbcon)
{
  $table_suffix = get_table_suffix();

  // All queries share a similar basic scoping
  $WHERE = "
        WHERE instance.id = instance_id
          AND instance.install_class = '$install_class'
          AND time BETWEEN '$start_datetime' AND '$end_datetime'
          ".($instance_name != 'ALL' ? "AND instance.name = '$instance_name'" : "");

  switch ($view) {
    case 'dashboard':
      $result = $dbcon->query("
        SELECT
          time as chart_time, sum(page_views) as page_views
        FROM summary_$table_suffix, instance
        $WHERE
        GROUP BY chart_time
      ");
      send_response(200, "success", $result->fetchAll(PDO::FETCH_ASSOC));
      // pageviews
      //
    case 'performance':
      $result = $dbcon->query("
        SELECT
          time as chart_time,
          CAST(IFNULL(sum(response_time)/sum(page_views)/1000000, 0) AS DECIMAL(12,2)) as avg_response_time,
          sum(503_errors) as 503_errors,
          sum(500_errors) as 500_errors,
          sum(response_time) as response_time,
          CAST(IFNULL(sum(page_views)/1000, 0) AS DECIMAL(12,1))  as page_view
        FROM summary_$table_suffix, instance
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


function do_datatable($view, $install_class, $instance_name, $start_datetime, $end_datetime, $dbcon)
{
  $OBSERVATION_LOOKUP = array(
    'total_views' => 'count(*) as total_views',
    'avg_response_time' => 'CAST(IFNULL(avg(response_time)/1000000, 0) AS DECIMAL(12,2)) as avg_response_time',
  );
  $valid_observations = array_keys($OBSERVATION_LOOKUP);
  $valid_dimensions = array('path', 'instance', 'remote_ip');

  foreach(array('dimensions', 'observations') as $key) {
    if (!isset($_GET[$key])) {
      send_response(400, "A '$key' parameter is required for all datatable queries.");
    }
    $$key = clean_string($_GET[$key]);
  }

  $dimensions = explode(',', $dimensions);
  $observations = explode(',', $observations);
  $columns = array_merge($dimensions, $observations);

  // Validate the dimensions
  foreach($dimensions as $dimension) {
    if (!in_array($dimension, $valid_dimensions)) {
      send_response(400, "Dimension '$dimension' must be one of ".implode(',', $valid_dimensions));
    }
  }

  // Validate the observations
  foreach($observations as $observation) {
    if (!in_array($observation, $valid_observations)) {
      send_response(400, "Observation '$observation' must be one of ".implode(',', $valid_observations));
    }
  }

  // Construct the standard query parts based on the requested dimensions/observations
  $selectColumns = $dimensions;
  foreach($observations as $observation) {
    $selectColumns[] = $OBSERVATION_LOOKUP[$observation];
  }
  $select = implode(', ', $selectColumns);
  $from = "request, instance";
  $where = "instance.id = instance_id
        AND instance.install_class = '$install_class'
        AND time BETWEEN '$start_datetime' AND '$end_datetime'
        ".($instance_name != 'ALL' ? "AND instance.name = '$instance_name'" : "");
  $groupby = implode(', ', $dimensions);
  $countColumn = array_pop($dimensions);
  $countby = implode(', ', $dimensions);

  // Multiple column ordering rules
  $dataOrderingRules = array();
  if (isset($_GET['iSortCol_0'])) {
    $dataSortingCols = intval($_GET['iSortingCols']);
    for ($i=0; $i<$dataSortingCols; $i++) {
      $dataSortingCol = intval($_GET["iSortCol_$i"]);
      if ($_GET["bSortable_$dataSortingCol"] == 'true' ) {
        $dataOrderingRules[] = $columns[$dataSortingCol].($_GET['sSortDir_'.$i]==='asc' ? ' ASC' : ' DESC');
      }
    }
  }

  // General table filtering
  $dataFilteringRules = array();
  if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
      for ($i=0; $i<count($columns); $i++) {
          if (isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == 'true') {
              $dataFilteringRules[] = "`".$columns[$i]."` LIKE ".$dbcon->quote('%'.$_GET['sSearch'].'%');
          }
      }
      if (!empty($dataFilteringRules)) {
          $dataFilteringRules = array('('.implode(" OR ", $dataFilteringRules).')');
      }
  }

  // Individual column filtering
  for ( $i=0 ; $i<count($columns) ; $i++ ) {
      if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == 'true' && $_GET['sSearch_'.$i] != '' ) {
          $dataFilteringRules[] = "`".$columns[$i]."` LIKE ".$dbcon->quote('%'.$_GET['sSearch_'.$i].'%');
      }
  }

  $dataWhere = "";
  if (!empty($dataFilteringRules)) {
      $dataWhere = " AND ".implode(" AND ", $dataFilteringRules);
  }

  $dataOrderBy = "";
  if (!empty($dataOrderingRules)) {
      $dataOrderBy = " ORDER BY ".implode(", ", $dataOrderingRules);
  }

  $dataLimit = "";
  if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' ) {
    $dataLimit = " LIMIT ".intval( $_GET['iDisplayStart'] ).", ".intval( $_GET['iDisplayLength'] );
  }

  // Gather the response data
  $data = array();
  $query = "SELECT SQL_CALC_FOUND_ROWS $select FROM $from WHERE $where $dataWhere GROUP BY $groupby $dataOrderBy $dataLimit";
  $result = $dbcon->query($query) or send_response(500, $dbcon->error);
  while ( $result_row = $result->fetch(PDO::FETCH_ASSOC) ) {
      $data_row = array();
      foreach ($columns as $viewColumn) {
        $data_row[] = $result_row[$viewColumn];
      }
      $data[] = $data_row;
  }

  // Data set length after filtering
  $result = $dbcon->query("SELECT FOUND_ROWS()") or send_response(500, $dbcon->error);
  list($iFilteredTotal) = $result->fetch();

  // Total data set length
  $query = "SELECT count(distinct $countColumn) FROM $from WHERE $where ".(strlen($countby) != 0 ? "GROUP BY $countby" : "");
  $result = $dbcon->query($query) or send_response(500, $dbcon->error);
  list($iTotal) = $result->fetch();

  $output = array(
      "sEcho"                => intval($_GET['sEcho']),
      "iTotalRecords"        => $iTotal,
      "iTotalDisplayRecords" => $iFilteredTotal,
      "aaData"               => $data,
  );
  echo json_encode( $output );
}


function clean_string($input)
{
  return preg_replace('/[^-a-zA-Z0-9: _,]/', '', $input);
}


function get_table_suffix()
{
  if (!isset($_GET['granularity'])) {
    send_response(400, "A 'granularity' parameter is required for all list queries.");
  }

  $granularity = clean_string($_GET['granularity']);
  switch ($granularity) {
    case 'minute': $table_suffix = "1m"; break;
    case '15minute': $table_suffix = "15m"; break;
    case 'hour': $table_suffix = "1h"; break;
    case 'day': $table_suffix = "1d"; break;
    case 'month': $table_suffix = "1d"; break;
    default: send_response(400, "Granularity '$granularity' must be one of 'minute', '15minute', 'hour', 'day', 'month'.");
  }

  return $table_suffix;
}


function uniques_to_row($rows)
{
  $data = array();
  foreach($rows as $row) {
    $data[$row['type']] = $row['total'];
  }
  return $data;
}


function send_response($code, $message, $data=NULL)
{
  header("Content-Type: application/json; charset=UTF-8");
  http_response_code($code);
  echo json_encode(array(
      'code' => $code,
      'message' => $message,
      'data' => $data,
  ));
  exit(0);
}
