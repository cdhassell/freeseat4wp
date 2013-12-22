<?php

/* 
Copyright (C) 2010 Maxime Gamboni. See COPYING for copying/warranty
info. 

$Id: index.php 372 2011-08-28 10:35:23Z tendays $
*/

$FS_PATH = plugin_dir_path( __FILE__ ) . '../../';
require_once ( $FS_PATH . "vars.php");

require_once ( $FS_PATH . "functions/plugins.php");
require_once ( $FS_PATH . "functions/booking.php");
require_once ( $FS_PATH . "functions/session.php");
require_once ( $FS_PATH . "functions/tools.php");

db_connect();

ensure_plugin('csvdump');

if (!admin_mode()) {
  kaboom($lang["access_denied"]);
  show_head(true);
  show_foot();
  exit;
}

header("Content-Type: text/x-csv");

$list = get_bookings("");

$first = true;
foreach ($list as $n => $l) {
  if ($first) {
    $sep = '';
    foreach ($l as $k => $v) {
      echo "$sep\"$k\"";
      $sep = ',';
    }
    echo "\n";
    $first = false;
  }
  $sep = '';
  foreach ($l as $k => $v) {
    echo "$sep\"$v\"";
    $sep = ',';
  }
  echo "\n";
}

