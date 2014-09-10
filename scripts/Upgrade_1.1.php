<?php
date_default_timezone_set('America/New_York');
require(realpath(dirname(__FILE__).'/../lib/utils.php'));

// for ($i=0; $i <= 600; $i++) {
//   echo".";
//   sleep(60);
// }
// echo "\n";
$maxrows = 100000;
$maxrows = 450000;

///////////////////////////////
// Bootstrap the environment
///////////////////////////////
$config = load_config();
if ($config === false) {
  exit(1);
}

$dbcon = get_db_connection($config['database']);
if ($dbcon === false) {
  exit(1);
}

echo "Startup Memory : ". convert(memory_get_usage()) . "\n";

// SET innodb_lock_wait_timeout = 120;
//
// get a smallish list of office locations
$query  = $dbcon->query("SELECT * FROM location WHERE id != 1 ORDER BY ipv4_start ASC");
$locations_raw = $query->fetchAll(PDO::FETCH_ASSOC);
foreach ($locations_raw as $key => $value) {
  // use a key for faster "searching"
  // 3 is the most we can use in our case
  $locations[substr($value['ipv4_end'], 0,3)][] = $value;
}
unset($locations_raw);
echo "Post Location Fetch Memory : ".convert(memory_get_usage()) . "\n";

function ip_match($ip)
{
  global $locations;
  $ip_long = ip2long($ip);
  $index = substr($ip_long, 0,3);
  foreach ($locations[$index] as $id => $value) {
    if ($ip_long < $value['ipv4_end'] && $ip_long > $value['ipv4_start'] ) {
      $output = $value["id"];
      break;
    }
  }
  return (empty($output)) ? "1" : $output ;
}

$query = $dbcon->query("SELECT id,path_hash,search FROM url");
$urls_raw = $query->fetchAll(PDO::FETCH_ASSOC);
foreach ($urls_raw as $key => $value) {
  if ($value['search'] === NULL) {
    $urls[$value['path_hash']] = array('id'=>$value['id'])  ;
  }else{
    $urlArray[$value['path_hash']][] = array('search'=>$value['search'],'id'=>$value['id']) ;
  }
}
unset($urls_raw);
echo "Post url Fetch Memory : ".convert(memory_get_usage()) . "\n";

function url_match($path,$search)
{
  global $urls,$urlArray;
  $s = $path;
  if (strlen($path) > 1) {
    $path  = preg_replace('/\/$|(\/user\/)[0-9]+|\/[0-9]+$|([a-z]+),.*|\/[0-9]+\,.*|\&.*/', '$1', $path);
    $path  = preg_replace('/(_vti).*/', '$1', $path);
  }
  $hash = md5($path);
  if (isset($urls[$hash]['id'])) {
    $output = intval($urls[$hash]['id']);
    $method = 'urls';
  }else if(isset($urlArray[$hash][0]['search'])){
    foreach ($urlArray[$hash] as $key => $value) {
      if(isset($value['search'])){
        $method = 'urlArray';
        if (preg_match('/.*'.$value['search'].'.*/', $search)){
          $output = $value['id'];
        }
      }
    }
  }
  if (empty($output)) {
    $output = "1";
    echo "----\nUnknown path :\"".$s."\" -> \"".$path."\"\nhash: \"".$hash."\"\nsearch: \"".$search."\"\n----\n";
  }
  return $output;
  unset($s,$path,$hash,$output);
}

// get count of all of the unset records
$query = $dbcon->query("SELECT count(id) as count  FROM request Where location_id IS NULL AND `url_id` IS NULL");
$notSet = $query->fetch(PDO::FETCH_ASSOC);
echo "Found # ".$notSet['count']." records needing conversion \n";

$batches = ceil($notSet['count'] / $maxrows);
echo "Iterating over ".$batches." groups of ".$maxrows." records that have unset locations : " .convert(memory_get_usage()) . "\n";

for ($i=0; $i < $batches; $i++) {
  echo "- - - - -\n";
  $time = -microtime(true);

  echo "Round #".($i+1)."\n";
  $query = $dbcon->query("SELECT id,remote_ip,path,query FROM request WHERE location_id IS NULL AND `url_id` IS NULL LIMIT $maxrows");
  $updateLocations = $query->fetchAll(PDO::FETCH_ASSOC);
  echo "Request Fetch Memory : ".convert(memory_get_usage()) . "\n";


  try {
    $dbcon->beginTransaction();
    foreach ($updateLocations as $id => $value) {
      $dbcon->exec("UPDATE `request` SET `location_id` = ".intval(ip_match($value['remote_ip'])).", `url_id` = ".intval(url_match($value['path'],$value['query']))." WHERE `id` = ".intval($value['id']));
      // $trash = "UPDATE `request` SET `location_id` = ".intval(ip_match($value['remote_ip'])).", `url_id` = ".intval(url_match($value['path'],$value['query']))." WHERE `id` = ".intval($value['id']);
    }
    $insert = $dbcon->commit();
  } catch (Exception $e) {
    $dbcon->rollBack();
    echo "Failed: " . $e->getMessage();
  }

  $time += microtime(true);
  echo "Rows Per Second ".number_format((count($updateLocations)/ $time),2). "\n";
  unset($updateLocations);
  echo "Finished Insert in ".number_format($time,3)." : ".convert(memory_get_usage()) . "\n";

}
unset($updateLocations,$urlArray,$urls);
echo "Post Request Fetch Memory : ".convert(memory_get_usage()) . "\n";

$query = $dbcon->query("SELECT count(id) FROM request WHERE location_id IS NULL");
$notSet = $query->fetch(PDO::FETCH_ASSOC);
echo $notSet['count(id)']." Remaining Unmatched records \n";
echo "- - - - -\n";
echo "- - - - -\n";
echo "- - - - -\n";

$tables = array('summary_1d','summary_1h','summary_15m','summary_1m','uniques_1d','uniques_1h','uniques_15m','uniques_1m');
// $tables = array('summary_1d','uniques_1d');
foreach ($tables as $key => $table) {
  $counter=0;

  // get count of all of the unset records
  $query = $dbcon->query("SELECT remote_ip FROM {$table} Where location_id IS NULL group by remote_ip");
  $Count = $query->rowCount();
  echo "Found # ".$Count." records needing conversion \n";

  $batches = ceil($Count / $maxrows);
  echo "Iterating over ".$batches." groups of ".$maxrows." records that have unset locations : " .convert(memory_get_usage()) . "\n";
  for ($i=0; $i < $batches; $i++) {
    echo "- - - - -\n";
    $time = -microtime(true);

    echo "Round #".($i+1)."\n";
    $query = $dbcon->query("SELECT remote_ip FROM {$table} WHERE location_id IS NULL group by remote_ip LIMIT $maxrows");
    $updateLocations = $query->fetchAll(PDO::FETCH_ASSOC);
    echo "Request Fetch Memory : ".convert(memory_get_usage()) . "\n";

    try {
      $sql = "UPDATE `{$table}` SET `location_id` = ? WHERE `remote_ip` = ?";
      $q = $dbcon->prepare($sql);
      foreach ($updateLocations as $id => $value) {
        $q->execute(array(intval(ip_match($value['remote_ip'])),$value['remote_ip']));
        $counter=$counter+$q->rowCount();
      }
    } catch (Exception $e) {
      echo "Failed: " . $e->getMessage();
    }
    $time += microtime(true);
    echo "Rows Per Second ".number_format(($counter / $time),2). "\n";
    unset($updateLocations);
    echo "Finished Insert in ".number_format($time,3)." : ".convert(memory_get_usage()) . "\n";

  }
  unset($updateLocations);
  echo "Post Request Fetch Memory : ".convert(memory_get_usage()) . "\n";

  $query = $dbcon->query("SELECT remote_ip FROM {$table} Where location_id IS NULL group by remote_ip");
  $Count = $query->rowCount();
  echo $Count." Remaining Unmatched records \n";
  echo "- - - - -\n";
  echo "- - - - -\n";
  echo "- - - - -\n";


}







# -- 1hour 42 mins - 6158
// UPDATE `request` SET `location_id` = NULL, `url_id` = NULL where location_id IS NOT NULL;
// UPDATE `summary_1d` SET `location_id` = NULL where location_id IS NOT NULL;

# how did we do ?

// select count(r.id) as count, r.url_id,u.name from request r
// JOIN url u on u.id = r.url_id
// group by url_id ORDER BY count DESC limit 100;
//
// select count(r.id) as count,u.name from request r
// JOIN url u on u.id = r.url_id
// group by url_id ORDER BY count DESC limit 100;
//
//
// select count(r.id) as count,u.name,r.path from request r
// JOIN url u on u.id = r.url_id
// group by u.name ORDER BY count DESC limit 100;
//
// Select r.remote_ip, r.location_id, u.name, r.time from request r
// JOIN url u on u.id = r.url_id
// WHERE time BETWEEN '2014-08-04 03:10' AND '2014-08-06 04:10'

// Select count(r.id) as requests,loc.name,ins.name,r.remote_ip from request r
// JOIN location loc on loc.id = r.location_id
// JOIN instance ins on ins.id = r.instance_id
// WHERE time BETWEEN '2014-08-04 03:10' AND '2014-08-06 04:10'
// Group by loc.name, r.remote_ip;

// Select loc.name, u.name, r.time from request r
// JOIN url u on u.id = r.url_id
// JOIN location loc on loc.id = r.location_id
// WHERE time BETWEEN '2014-08-04 03:10' AND '2014-08-06 04:10'
// AND r.remote_ip = '10.23.9.28'
