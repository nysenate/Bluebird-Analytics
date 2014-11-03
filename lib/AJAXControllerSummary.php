<?php
/*
  AJAX Controller class for BlueBird analytics summary reports
*/
require_once 'AJAXController.php';

class AJAXControllerSummary extends AJAXController {
  protected $allows_groups = false;

  /* need a different report parsing here
     Since summary reports are single datapoints with no grouping, we only need
     to query each table once.  Iterate through the requested reports, and combine
     them by table. */
  protected function _parseReports() {
    $reps = $this->session->reports;
    $tables = array();
    $this->session->log("Examining reports=\n".var_export($reps,1),LOG_LEVEL_DEBUG);
    foreach ($reps as $key=>$val) {
      // a report definition should be an array
      if (!is_array($val)) {
        $this->addError(AJAX_ERR_ERROR,"Invalid report definition has been ignored");
        continue;
      }
      // the report should come from one of the tables in static::$datapoints
      $repname = array_value('report_name',$val,"<no-name>");
      $reptable = clean_string(array_value('target_table',$val));
      if (!in_array($reptable,array_keys(static::$datapoints))) {
        $this->addError(AJAX_ERR_ERROR,"Invalid target table requested, report '$repname' has been ignored");
        continue;
      }
      // the report needs to have field definitions
      $fields = array_value('datapoints',$val);
      if (!count($val['datapoints'])) {
        $this->addError(AJAX_ERR_ERROR,"No datapoints requested, report '$repname' has been ignored");
        continue;
      }
      // if extrapoints is populated, add those entries to the field list
      if (count(static::$extrapoints)) {
        foreach (static::$extrapoints as $fld=>$mod) {
          $fields[] = array('field'=>$fld,'mod'=>$mod);
        }
      }
      // parse the fields, and verify valid fields have been requested
      $fields = $this->_parseFields($reptable,$fields);
      if (!(is_array($fields) && count($fields))) {
        $this->addError(AJAX_ERR_ERROR,"Invalid/missing field definitions, report '$repname' has been ignored");
        continue;
      }
      if (!array_value($reptable, $tables)) {
        $tables[$reptable] = array();
      }
      $this->session->log("Adding new list of fields to $reptable=\n".var_export($fields,1),LOG_LEVEL_DEBUG);
      $tables[$reptable] = array_merge($tables[$reptable], $fields);
    }
    $this->session->log("Final parsed reports=\n".var_export($tables,1),LOG_LEVEL_DEBUG);
    return $tables;
  }

  public function get() {
    $this->session->log("Received data:\n".var_export($this->reports,1),LOG_LEVEL_DEBUG);
    // iterate through the tables to build each query and get results
    $result = array();
    foreach ($this->reports as $table => $tfields) {
      // build the select and group clauses
      $fields = $this->_buildSelectFields($table,$tfields);
      // create the common where and join clauses
      $where = "WHERE ".implode(' AND ',$this->clauses);
      $join = $this->getJoinClause();
      $query = "SELECT $fields FROM {$table}_{$this->suffix} $join $where";
      $this->session->log("Final query: $query",LOG_LEVEL_DEBUG);
      $result += $this->getSingleRow($query, $this->bind_params);
    }
    return $result;
  }

/* This is obsolete code from the original api.php
   Keep this until all reports are re-created using new structures */
  function someotherfnuction() {
    switch ($view) {
      case 'dashboard':
        return $result;
        break;
      case 'performance':
        $result = $dbcon->query("
          SELECT
            sum(page_views) as page_views,
            IFNULL(sum(503_errors),0) as 503_errors,
            IFNULL(sum(500_errors),0) as 500_errors,
            CAST(IFNULL((sum(response_time)/sum(page_views))/1000000, 0) AS DECIMAL(12,2)) as avg_response_time
          FROM summary_$suffix, instance
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
          FROM summary_$suffix sum
          $WHERE
        ");

        $numbers = $result->fetch(PDO::FETCH_ASSOC);
        $result->closeCursor();

        $result = $dbcon->query("
          SELECT type, count(distinct value) as total
          FROM uniques_$suffix sum
          $WHERE
          GROUP BY type
        ");
        $uniques = uniques_to_row($result->fetchAll(PDO::FETCH_ASSOC));
        $result->closeCursor();






        // $result = $dbcon->query("
        //   SELECT
        //     count(distinct instance_id) as active_instances,
        //     count(distinct remote_ip) as active_users
        //   FROM uniques_$suffix, instance
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
          FROM summary_$suffix, instance
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
          FROM summary_$suffix sum
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
          FROM summary_$suffix sum
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
  }
}