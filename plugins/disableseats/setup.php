<?php

  /** disableseats/setup.php
   *
   * Copyright (c) 2010 by twowheeler
   * Licensed under the GNU GPL 2. For full terms see the file COPYING.
   *
   * Disabled seating map
   *
   * $Id$
   *
   */


function freeseat_plugin_init_disableseats() {
    global $freeseat_plugin_hooks;

    $freeseat_plugin_hooks['seatmap_top']['disableseats'] = 'disableseats_linkfromseats';

    init_language('disableseats');
}

function disableseats_linkfromseats() {
  global $lang, $sh, $FS_PATH;

  if (admin_mode()) {
    echo '<p class="main">';
    printf($lang["seedisabledseats"],
'[<a href="'. $FS_PATH . 'plugins/disableseats/index.php?showid='.$sh['id'].'">','</a>]');
    echo '</p>';
  }
}

