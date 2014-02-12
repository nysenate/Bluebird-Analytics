<?php

function url_for($path) {
  global $config;
  return return $config['website']['context'].$path;
}

?>
