<?php

/* 
Copyright (C) 2010 Maxime Gamboni. See COPYING for copying/warranty
info. 

$Id$
*/

$FS_PATH = plugin_dir_path( __FILE__ ) . '../../';
require_once ( $FS_PATH . "vars.php");

require_once ( $FS_PATH . "functions/plugins.php");
require_once ( $FS_PATH . "functions/booking.php");
require_once ( $FS_PATH . "functions/session.php");
require_once ( $FS_PATH . "functions/tools.php");
require_once ( $FS_PATH . "functions/shows.php");

// Fetches only unique name and address records, ignoring booking details.  

function csvnames_get() {
  global $params;
  if (isset($_SESSION['showid'])) {
    $show = get_show($_SESSION['showid']);
    $id = $show['spectacleid'];
  } elseif (isset($_SESSION['spectacleid'])) {
    $id = $_SESSION['spectacleid'];
  } elseif (isset($params['showid'])) {
    $show = get_show($params['showid']);
    $id = $show['spectacleid'];
  }
  // if no spectacle is specified, get them all
  $spec = (isset($id) ? "and shows.spectacle=$id" : "and shows.spectacle > 41");
  $sql = "SELECT DISTINCT firstname, lastname, email, phone, address, city, us_state, postalcode 
  FROM booking,shows WHERE booking.showid=shows.id $spec ORDER BY lastname, firstname";
  $list = fetch_all(mysql_query($sql)); 
  return $list;
}

function csvnames_output($list) {
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
}

db_connect();

ensure_plugin('csvnames');

if (!admin_mode()) {
  kaboom($lang["access_denied"]);
  show_head(true);
  show_foot();
  exit;
}

header("Content-Type: text/x-csv");
header("Content-Type: application/download");
header('Content-Disposition: attachment; filename=names.csv');

csvnames_output(csvnames_get());

?>
