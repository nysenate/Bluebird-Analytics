<?php

function url_for($path) {
  global $config;
  return $config['website']['context'].$path;
}

?>
