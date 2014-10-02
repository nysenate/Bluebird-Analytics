<?php
date_default_timezone_set('America/New_York');
require(realpath(dirname(__FILE__).'/../lib/utils.php'));

/* set up logging */
global $g_debug_level, $utils__print_log;

/*
    !!! WARNING !!!
    Setting $g_debug_level to DEBUG will result in a HUGE log (several
    entries per request row processed).  DON'T DO IT unless you have a really
    good reason, and intend to actively monitor it.
*/
$g_debug_level = WARN;
$utils__print_log = false;

/* *****************************
     Bootstrap the environment
   ***************************** */
$config = load_config();
if ($config === false) {
  log_(FATAL,"Could not load config, exiting");
  exit(1);
}

$dbcon = get_db_connection($config['database']);
if ($dbcon === false) {
  log_(FATAL,"Could not connect to database using:\n".var_export($config['database'],1));
  exit(1);
}

/* let us begin */

/*
  cache all the location URLs
  If a URL does not require a query string match, just use the path as the array key
  Otherwise, use "query:<path>" as the array key, and sub-element is an array
*/
log_(DEBUG,"Loading URL list");
$urls = array();
$query = "SELECT id,path,match_full,search FROM url";
$pdostmt = $dbcon->query($query);
log_(INFO,"Found ".$pdostmt->rowCount()." URLs to load");
$fullcount = 0;
while ($obj = $pdostmt->fetch(PDO::FETCH_OBJ)) {
  if ($obj->match_full) {
    $key = "query:{$obj->path}";
    if (!array_key_exists($key,$urls)) {
      $urls[$key]=array();
    }
    $urls[$key][] = $obj;
  } else {
    $urls[$obj->path] = $obj;
  }
  $fullcount++;
}
log_(INFO,"Loaded " .count($urls). " paths in $fullcount URL objects");

/* Get the total number of records to assist with time tracking */
$query = "SELECT COUNT(*) AS TotalRecs FROM request WHERE url_id IS NULL";
$pdostmt = $dbcon->query($query);
$obj = $pdostmt->fetch(PDO::FETCH_OBJ);
$totalloops = ((int)($obj->TotalRecs / 10000)) + 1;
log_(INFO,"Found {$obj->TotalRecs} records ($totalloops batches) to populate");

/* This will pull records from request in "batches" of 10000 records.  The
   regex replacement will be applied to each requested path.  The "clean"
   path will either be found in $urls, or default to 1 ("No Match") */
$query = "SELECT id,path,query FROM request WHERE url_id IS NULL LIMIT 10000";

$regpatterns = array('#(.+)/(?:[0-9]+)?$#',
                     '#([a-z0-9]+),.*$#',
                     '#(/_vti_).*#',
                     '#(/user)/[0-9]+#',
                     );
$regreplace =  array('$1',
                     '$1',
                     '$1',
                     '$1',
                     );

log_(DEBUG,"Beginning url loop");
/* loop* and microtime() variables are for logging time statistics only */
$loopcount=0;
$loopsum = 0;
$totalstart = microtime(true);
do {
  /* get the next batch */
  $qstart = microtime(true);
  $pdostmt = $dbcon->query($query);
  $qend = microtime(true);
  $loopstart = microtime(true);
  /* FOR EACH OF 10000 RECORDS! */
  while ($obj = $pdostmt->fetch(PDO::FETCH_OBJ)) {
    $updquery = '';
    // default "No Match" id
    $match_id = 1;
    // clean the URL
    $tpath = preg_replace($regpatterns, $regreplace, $obj->path);
    log_(DEBUG, "\nChecking:\n    path={$obj->path}\n   tpath={$tpath}");
    if (array_key_exists($tpath,$urls)) {
      // found an exact path match, update the row
      $match_id = $urls[$tpath]->id;
      log_(DEBUG,"\n -> matched to $match_id:".$urls[$tpath]->path);
    } elseif (array_key_exists("query:$tpath",$urls)) {
      // found a potential querystring match, process
      $key = "query:$tpath";
      foreach ($urls[$key] as $kobj) {
        if (preg_match("/{$kobj->search}/", $obj->query)) {
          $match_id = $kobj->id;
          log_(DEBUG,"\n -> query matched to $match_id:".$kobj->search.":".$kobj->path);
          break;
        }
      }
    } else {
      // no match found, update to '1'
      log_(DEBUG," -> no match, setting to 1");
      $match_id = 1;
    }
    // update the row and carry on
    if ($match_id) {
      $updsql = "UPDATE request SET url_id=? WHERE id=?";
      $updstmt = $dbcon->prepare($updsql);
      $updstmt->execute(array($match_id,$obj->id));
    }
  }
  /* statistics for the last loop just completed */
  $loopend = microtime(true);
  $thislooptime = $loopend - $loopstart;
  $loopsum += $thislooptime;
  $loopcount++;
  $loopavg = $loopsum/$loopcount;
  log_(DEBUG,"Loop,Time,Avg,Query: " . sprintf('%u',$loopcount) . "," . sprintf('%.3f',$thislooptime) .
            "," . sprintf('%.3f',($loopavg)) .
            "," . sprintf('%.3f',($qend - $qstart)));
  $esttime = $loopavg * $totalloops / 3600;
  $elapsed = (microtime(true) - $totalstart) / 3600;
  log_(INFO, "Loops: " .sprintf('%u',$loopcount).
             ", Est. time: ".sprintf('%.3f',$esttime)." hours, ".
             "elapsed: ".sprintf('%.3f',$elapsed));
} while ($pdostmt->rowCount());
