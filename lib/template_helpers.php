<?php

function url_for($path) {
  return $_SERVER['CONTEXT_PREFIX'].$path;
}

?>
