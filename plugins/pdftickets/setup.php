<?php


// Dompdf will throw errors on PHP 4.x. 
if (version_compare(PHP_VERSION, '5.0.0', '>')) 
  require_once FS_PATH . "plugins/pdftickets/dompdf/dompdf_config.inc.php";
else 
  wp_die("PHP version 5 is required for the Freeseat-pdfticket plugin");

  /** pdftickets/setup.php
   *
   * Copyright (c) 2010 by twowheeler
   * Licensed under the GNU GPL 2. For full terms see the file COPYING.
   *
   * Render tickets in pdf.
   *
   * $Id$
   *
   */

function freeseat_plugin_init_pdftickets() {
    global $freeseat_plugin_hooks;
    
    $freeseat_plugin_hooks['ticket_prepare']['pdftickets'] = 'pdftickets_prepare';
    $freeseat_plugin_hooks['ticket_render']['pdftickets'] = 'pdftickets_render';
    $freeseat_plugin_hooks['ticket_finalise']['pdftickets'] = 'pdftickets_finalise';
    $freeseat_plugin_hooks['kill_booking_done']['pdftickets'] = 'pdftickets_cleanup';
    
    init_language('pdftickets');
}

function pdftickets_prepare() {
  global $pdftickets_id;

  /** This plugin stores series of tickets into SESSION["pdftickets"], and index.php generates the corresponding pdf file. */

  if (!isset($_SESSION["pdftickets"])) {
    $_SESSION["pdftickets"]["next"] = 0; // identifier of the next batch for this session.
  }

  $pdftickets_id = $_SESSION["pdftickets"]["next"];
  $_SESSION["pdftickets"][$pdftickets_id] = array(); // identifiers of the tickets to generate.
  $_SESSION["pdftickets"]["next"] = $pdftickets_id + 1;
}

function pdftickets_render($booking) {
  global $pdftickets_id;

  $_SESSION["pdftickets"][$pdftickets_id][] = $booking;
}

function pdftickets_finalise() {
  global $lang;
  global $pdftickets_id;

  echo "<div class='dontprint'>";
  echo "<h2>".$lang['pdftickets_thankyou']."</h2><p class='main'></p>";  
  echo "<p class='main'><table><tr><td>";
  echo "<div id='download-image'>";
  echo "<img src='".FS_PATH."plugins/pdftickets/down.png"."'></div></td><td><p>";
  printf($lang["pdftickets_download_link"],'[<a href="'.FS_PATH.'plugins/pdftickets/?key='.$pdftickets_id.'">','</a>]');
  echo '</p>';
  if (isset($_SESSION['email']) && is_email_ok($_SESSION['email'])) {
    echo '<p>';
    printf($lang["pdftickets_email_link"],'[<a href="'.FS_PATH.'plugins/pdftickets/?key='.$pdftickets_id. '&mode=mail">','</a>]',$_SESSION['email'])."<div id='mailsent'></div>";
    if (isset($_SESSION['pdftickets_emailsent'])) echo "&nbsp;&nbsp;&nbsp;<b> Sent!</b>";
    echo '</p>';
  }
  echo "</td></tr></table></p></div>";
}

function pdftickets_cleanup() {
  // clear session variables after use
  unset($_SESSION['pdftickets']);
}

