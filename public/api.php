<?php
header('Content-type: application/json');
date_default_timezone_set('America/New_York');
require(realpath(dirname(__FILE__).'/../lib/utils.php'));
// error_reporting(-1);
// ini_set('display_errors', 'On');
///////////////////////////////
// Bootstrap the environment
///////////////////////////////
$config = load_config();
if ($config === false) {
  send_response(500, "An internal error has occurred.");
}

$g_log_file = get_log_file($config['debug']['log_file']);
$g_log_level = (int)$config['debug']['debug_level'];
$dbcon = get_db_connection($config['database']);
if ($dbcon === false) {
  send_response(500, "An internal error has occurred.");
}

$GLOBALS['DATA_PARAMS'] = array('instance_name', 'install_class', 'view', 'start_datetime', 'end_datetime');

// Default view is the dashboard overview
if (!isset($_REQUEST['req'])) {
  die("Please check your .htaccess file to confirm that rewrite rules are working.");
}

$request = $_REQUEST['req'];
switch($request) {
    case 'save_query': save_query($_REQUEST, $dbcon); break;
    case 'delete_query': delete_query($_REQUEST, $dbcon); break;
    case 'get_list': get_list($_REQUEST, $dbcon); break;
    case 'get_summary': get_summary($_REQUEST, $dbcon); break;
    case 'get_chart': get_chart($_REQUEST, $dbcon); break;
    case 'get_datatable': get_datatable($_REQUEST, $dbcon); break;
    default: send_response(400, "Unknown request '$request'");
}

function save_query($args, $dbcon)
{
  foreach(array('id', 'name', 'dimensions', 'observations') as $key) {
    if (!isset($args[$key])) {
      send_response(400, "A '$key' parameter is required for 'save_query' chart queries.");
    }
    $$key = clean_string($args[$key]);
  }
  $id = intval($id);

  if ($id == 0) {
    $stmt = $dbcon->prepare("REPLACE INTO datatable (name, dimensions, observations) VALUES (?, ?, ?)");
    $stmt->execute(array($name, $dimensions, $observations));
    $result = $dbcon->query("SELECT LAST_INSERT_ID()");
    list($id) = $result->fetch();
  }
  else {
      $stmt = $dbcon->prepare("UPDATE datatable SET name=?, dimensions=?, observations=? WHERE id=?");
      $stmt->execute(array($name, $dimensions, $observations, $id));
  }
  echo json_encode(array(
    'id' => $id,
    'name' => $name,
    'dimensions' => $dimensions,
    'observations' => $observations,
  ));
}

function delete_query($args, $dbcon)
{
  foreach(array('id') as $key) {
    if (!isset($args[$key])) {
      send_response(400, "A '$key' parameter is required for 'save_query' chart queries.");
    }
    $$key = clean_string($args[$key]);
  }

  $stmt = $dbcon->prepare("DELETE FROM datatable WHERE id = ?");
  $stmt->execute(array(intval($id)));
  echo json_encode(array(
    'id' => $id,
  ));
}

function get_list($args, $dbcon)
{
  $table_suffix = get_table_suffix($args);
  foreach(array_merge($GLOBALS['DATA_PARAMS'], array('list_size', 'list_offset')) as $key) {
    if (!isset($args[$key])) {
      send_response(400, "A '$key' parameter is required for all chart queries.");
    }
    $$key = clean_string($args[$key]);
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
    case 'users':
      $WHERE = "
        WHERE time BETWEEN '$start_datetime' AND '$end_datetime'
        ".($instance_name != 'ALL' ? "AND ins.name = '$instance_name'" : "");

      // top 10 active instances, users
      $result = $dbcon->query("
        SELECT
          sum(sum.page_views) as requests,
          loc.name 'connection',
          ins.name 'server-name',
          sum.remote_ip,
          sum.location_id,
          sum.instance_id
        FROM summary_$table_suffix sum
        JOIN location loc on loc.id = sum.location_id
        JOIN instance ins on ins.id = sum.instance_id
        $WHERE
        GROUP BY loc.name, ins.name, sum.remote_ip ;
      ");
      // echo"
      // SELECT
      //     sum(sum.page_views) as requests,
      //     loc.name 'connection',
      //     ins.name 'server-name',
      //     sum.remote_ip,
      //     sum.location_id,
      //     sum.instance_id
      //   FROM summary_$table_suffix sum
      //   JOIN location loc on loc.id = sum.location_id
      //   JOIN instance ins on ins.id = sum.instance_id
      //   $WHERE
      //   GROUP BY loc.name, ins.name, sum.remote_ip ;

      // ";
      // exit();

      $user_records = $result->fetchAll(PDO::FETCH_ASSOC);
      $result->closeCursor();
      send_response(200, "success", array('user_overview' => $user_records));
      break;
    case 'userdetails':
      $filter = preg_replace("/\[|\]/", "", explode(",", $_GET['filter']));
      $WHERE = "
        WHERE r.time BETWEEN '$start_datetime' AND '$end_datetime'
        AND r.remote_ip = '$filter[0]'
        AND r.location_id = '$filter[1]'
        AND r.instance_id = '$filter[2]'

        ".($instance_name != 'ALL' ? "AND ins.name = '$instance_name'" : "");

      // top 10 active instances, users
      $result = $dbcon->query("
        SELECT
          u.name,
          r.path,
          r.time,
          CAST(IFNULL((r.response_time)/1000000, 0) AS DECIMAL(12,2)) as response_time
        FROM request r
        JOIN url u on u.id = r.url_id
        $WHERE
        ;
      ");
      // echo"
      //   SELECT
      //     u.name,
      //     r.path,
      //     r.time,
      //     CAST(IFNULL((r.response_time)/1000000, 0) AS DECIMAL(12,2)) as response_time
      //   FROM request r
      //   JOIN url u on u.id = r.url_id
      //   $WHERE;
      // ";
      // exit();
      $user_records = $result->fetchAll(PDO::FETCH_ASSOC);
      $result->closeCursor();
      foreach ($user_records as $key => $value) {
        if ($key != 0) {
          $timeDifference = strtotime($value['time'])-(strtotime($user_records[$key-1]['time'])+$user_records[$key-1]['response_time']);
          $user_records[$key]['diff'] = number_format($timeDifference, 2, '.', '');
        }else{
          $user_records[$key]['diff'] =0;
        }
        // if ($user_records[$key]['response_time']) {
        //   unset($user_records[$key]['response_time']);
        // }
      }
      send_response(200, "success", array('user_detail' => $user_records));
      break;
    case 'behavior':
      // list of instance

      break;
  }
  echo "do_list for $view [$start_datetime TO $end_datetime]";
}


function get_summary($args, $dbcon)
{
  foreach($GLOBALS['DATA_PARAMS'] as $key) {
    if (!isset($args[$key])) {
      send_response(400, "A '$key' parameter is required for all queries.");
    }
    $$key = clean_string($args[$key]);
  }
  $table_suffix = get_table_suffix($args);

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
        'distinct_pages' => array_value('path',$uniques),
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
          CAST(IFNULL((sum(response_time)/sum(page_views))/1000000, 0) AS DECIMAL(12,2)) as avg_response_time
        FROM summary_$table_suffix, instance
        $WHERE
      ");
      $row = $result->fetch(PDO::FETCH_ASSOC);
      send_response(200, "success", $row);
      break;
    case 'userdetails':
      $filter = preg_replace("/\[|\]/", "", explode(",", $_GET['filter']));
      $WHERE = "
        WHERE sum.time BETWEEN '$start_datetime' AND '$end_datetime'
        AND sum.remote_ip = '$filter[0]'
        AND sum.location_id = '$filter[1]'
        AND sum.instance_id = '$filter[2]'

        ".($instance_name != 'ALL' ? "AND instance.name = '$instance_name'" : "");

      $result = $dbcon->query("
        SELECT
          IFNULL(sum(page_views), 0) as page_views,
          IFNULL(sum(503_errors), 0) as 503_errors,
          IFNULL(sum(500_errors), 0) as 500_errors,
          CAST(IFNULL((sum(response_time)/sum(page_views))/1000000, 0) AS DECIMAL(12,2)) as avg_response_time
        FROM summary_$table_suffix sum
        $WHERE
      ");

      $numbers = $result->fetch(PDO::FETCH_ASSOC);
      $result->closeCursor();

      $result = $dbcon->query("
        SELECT type, count(distinct value) as total
        FROM uniques_$table_suffix sum
        $WHERE
        GROUP BY type
      ");
      $uniques = uniques_to_row($result->fetchAll(PDO::FETCH_ASSOC));
      $result->closeCursor();






      // $result = $dbcon->query("
      //   SELECT
      //     count(distinct instance_id) as active_instances,
      //     count(distinct remote_ip) as active_users
      //   FROM uniques_$table_suffix, instance
      //   $WHERE"
      // );
      // $instances = $result->fetch(PDO::FETCH_ASSOC);
      // $result->closeCursor();

      send_response(200, "success", array(
        'page_views' => $numbers['page_views'],
        'distinct_pages' => $uniques['path'],
        '503_errors' => $numbers['503_errors'],
        '500_errors' => $numbers['500_errors'],
        'avg_response_time' => $numbers['avg_response_time']
      ));
      break;
    case 'offices':
      //////////////////////////////////////////////////////////////////
      // select count(r.id) as count,u.name,r.path from request r //
      // JOIN url u on u.id = r.url_id                                //
      // group by u.name ORDER BY count DESC limit 100;               //
      //////////////////////////////////////////////////////////////////
      $result = $dbcon->query("
        SELECT
          sum(page_views) as page_views,
          IFNULL(sum(503_errors),0) as 503_errors,
          IFNULL(sum(500_errors),0) as 500_errors,
          IFNULL(sum(response_time),0) as response_time,
          u.name
        FROM summary_$table_suffix, instance
        $WHERE
        JOIN url u on u.id = r.url_id
      ");
      $row = $result->fetch(PDO::FETCH_ASSOC);
      $row['avg_response_time'] = (((float)$row['response_time'])/$row['page_views'])/1000000;
      send_response(200, "success", $row);
      break;
    case 'users':
      // top 10 active instances, users
      $result = $dbcon->query("
        SELECT
          count(sum.id) as requests,
          loc.name,
          ins.name,
          sum.remote_ip
        FROM summary_$table_suffix sum
        JOIN location loc on loc.id = sum.location_id
        JOIN instance ins on ins.id = sum.instance_id
        $WHERE
        GROUP BY loc.name, sum.remote_ip;
        ORDER BY requests DESC
        LIMIT $list_size OFFSET $list_offset
      ");
      echo"
        SELECT
          count(sum.id) as requests,
          loc.name,
          ins.name,
          sum.remote_ip
        FROM summary_$table_suffix sum
        JOIN location loc on loc.id = sum.location_id
        JOIN instance ins on ins.id = sum.instance_id
        $WHERE
        GROUP BY loc.name, sum.remote_ip;
        ORDER BY requests DESC
        LIMIT $list_size OFFSET $list_offset
      ";
      exit();
      $user_records = $result->fetchAll(PDO::FETCH_ASSOC);
      $result->closeCursor();
      send_response(200, "success", array('user_records' => $user_records));
      break;
    case 'content':
      // total pages served
      // average render time
      break;
    default:
      send_response(400, "View '$view' must be one of 'dashboard', 'performance', 'offices', 'content','users'.");
      break;
  }
  echo "do_summary for $view [$start_datetime TO $end_datetime]";
}

function get_chart($args, $dbcon)
{
  foreach($GLOBALS['DATA_PARAMS'] as $key) {
    if (!isset($args[$key])) {
      send_response(400, "A '$key' parameter is required for all queries.");
    }
    $$key = clean_string($args[$key]);
  }
  $table_suffix = get_table_suffix($args);

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
          CAST(IFNULL((sum(response_time)/sum(page_views))/1000000, 0) AS DECIMAL(12,2)) as avg_response_time,
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


function get_datatable($args, $dbcon)
{
  $COLUMN_LOOKUP = array(
    // Dimensions
    'path' => 'path',
    'query' => 'query',
    'remote_ip' => 'remote_ip',
    'action' => 'url.name as `action`',
    'office' => 'location.name as `office`',
    'instance.name' => 'instance.name as `instance.name`',

    // Observations
    'time' => 'time',
    'total_views' => 'count(*) as total_views',
    'avg_response_time' => 'CAST(IFNULL(avg(response_time)/1000000, 0) AS DECIMAL(12,2)) as avg_response_time',
    '503_errors' => 'IFNULL(sum(response_code = 503), 0) as 503_errors',
    '500_errors' => 'IFNULL(sum(response_code = 500), 0) as 500_errors',
  );

  foreach(array_merge($GLOBALS['DATA_PARAMS'], array('dimensions', 'observations')) as $key) {
    if (!isset($args[$key])) {
      send_response(400, "A '$key' parameter is required for all datatable queries.");
    }
    $$key = clean_string($args[$key]);
  }

  $dimensions = explode(',', $dimensions);
  $observations = explode(',', $observations);
  $columns = array_merge($dimensions, $observations);

  // Validate the dimensions
  foreach($dimensions as $dimension) {
    if (!array_key_exists($dimension, $COLUMN_LOOKUP)) {
      send_response(400, "Dimension '$dimension' must be one of ".implode(',', $valid_dimensions));
    }
  }

  // Validate the observations
  foreach($observations as $observation) {
    if (!array_key_exists($observation, $COLUMN_LOOKUP)) {
      send_response(400, "Observation '$observation' must be one of ".implode(',', $valid_observations));
    }
  }

  // Construct the standard query parts based on the requested dimensions/observations
  $selectColumns = array();
  foreach($columns as $column) {
    $selectColumns[] = $COLUMN_LOOKUP[$column];
  }
  $select = implode(', ', $selectColumns);

  $from = "request, instance, url";
  $where = "instance.id = instance_id
        AND url.id = url_id
        AND instance.install_class = '$install_class'
        AND time BETWEEN '$start_datetime' AND '$end_datetime'
        ".($instance_name != 'ALL' ? "AND instance.name = '$instance_name'" : "");
  $groupby = implode(', ', $dimensions);
  $countColumn = array_pop($dimensions);
  $countby = implode(', ', $dimensions);

  // Multiple column ordering rules
  $dataOrderingRules = array();
  if (isset($args['iSortCol_0'])) {
    $dataSortingCols = intval($args['iSortingCols']);
    for ($i=0; $i<$dataSortingCols; $i++) {
      $dataSortingCol = intval($args["iSortCol_$i"]);
      if ($args["bSortable_$dataSortingCol"] == 'true' ) {
        $dataOrderingRules[] = $columns[$dataSortingCol].($args['sSortDir_'.$i]==='asc' ? ' ASC' : ' DESC');
      }
    }
  }

  // General table filtering
  $dataFilteringRules = array();
  if (isset($args['sSearch']) && $args['sSearch'] != "") {
    for ($i=0; $i<count($columns); $i++) {
      if (isset($args['bSearchable_'.$i]) && $args['bSearchable_'.$i] == 'true') {
        $dataFilteringRules[] = $columns[$i]." LIKE ".$dbcon->quote('%'.$args['sSearch'].'%');
      }
    }
    if (!empty($dataFilteringRules)) {
        $dataFilteringRules = array('('.implode(" OR ", $dataFilteringRules).')');
    }
  }

  // Individual column filtering
  for ( $i=0 ; $i<count($columns) ; $i++ ) {
    if ( isset($args['bSearchable_'.$i]) && $args['bSearchable_'.$i] == 'true' && $args['sSearch_'.$i] != '' ) {
      $dataFilteringRules[] = $columns[$i]." LIKE ".$dbcon->quote('%'.$args['sSearch_'.$i].'%');
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
  if ( isset( $args['iDisplayStart'] ) && $args['iDisplayLength'] != '-1' ) {
    $dataLimit = " LIMIT ".intval( $args['iDisplayStart'] ).", ".intval( $args['iDisplayLength'] );
  }

  // Gather the response data
  $data = array();
  $query = "SELECT SQL_CALC_FOUND_ROWS $select FROM $from WHERE $where $dataWhere GROUP BY $groupby $dataOrderBy $dataLimit;";
  // echo($query);
  // exit();
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
    "sEcho"                => intval($args['sEcho']),
    "iTotalRecords"        => $iTotal,
    "iTotalDisplayRecords" => $iFilteredTotal,
    "aaData"               => $data,
  );
  echo json_encode( $output );
}

function get_table_suffix($args)
{
  if (!isset($args['granularity'])) {
    send_response(400, "A 'granularity' parameter is required for all list queries.");
  }

  $granularity = clean_string($args['granularity']);
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

?>
