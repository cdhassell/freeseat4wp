<?php

  /** bookingmap/setup.php
   *
   * Copyright (c) 2010 by Maxime Gamboni
   * Licensed under the GNU GPL 2. For full terms see the file COPYING.
   *
   * Displays seatmap with reservation status
   *
   * $Id$
   *
   */


function freeseat_plugin_init_bookingmap() {
    global $freeseat_plugin_hooks;

    $freeseat_plugin_hooks['seatmap_top']['bookingmap'] = 'bookingmap_linkfromseats';
    $freeseat_plugin_hooks['bookinglist_pagebottom']['bookingmap'] = 'bookingmap_linkfromlist';
}

function bookingmap_linkfromseats() {
  global $lang, $sh;

  if (admin_mode()) {
    echo '<p class="main">';
    printf($lang["seeasamap"],
'[<a href="'. FS_PATH . 'plugins/bookingmap/?showid='.$sh['id'].'">','</a>]');
    echo '</p>';
  }
}

function bookingmap_linkfromlist() {
  global $filtershow, $lang;
  if ($filtershow) {
    echo '<ul><li><p class="main">';
    printf($lang["seeasamap"],"[<a href='". FS_PATH . "plugins/bookingmap/?showid=$filtershow'>",'</a>]');
   echo '</p></ul>';
  }
}

