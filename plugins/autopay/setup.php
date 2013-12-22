<?php

  /** autopay/setup.php
   *
   * Copyright (c) 2010 by Maxime Gamboni
   * Licensed under the GNU GPL 2. For full terms see the file COPYING.
   *
   * Lets the admin mark tickets as paid at time of booking.
   *
   * $Id$
   *
   */

function freeseat_plugin_init_autopay() {
    global $freeseat_plugin_hooks;

    $freeseat_plugin_hooks['confirm_bottom']['autopay'] = 'autopay_checkbox';
    $freeseat_plugin_hooks['confirm_process']['autopay'] = 'autopay_process';
}

/** Add a checkbox if payment mode is "other". */
function autopay_checkbox() {
  echo '<!-- autopay -->';
  if (admin_mode() and ($_SESSION["payment"] == PAY_OTHER)) {
    /* Note, the "other" payment method can normally only be selected
     by the admin, but keeping the check in case other plugins
     interfere... */
    echo '<p class="main"><input type="checkbox" name="autopay"'; 
    // if (isset($_SESSION["autopay"]) and $_SESSION["autopay"]) {
      echo ' checked="checked"';
    // }  always default to checked, for safety
    echo '> Mark tickets as paid</p>';
  }
}

function autopay_process() {
  $_SESSION["autopay"] = isset($_POST["autopay"])
    and admin_mode() and ($_SESSION["payment"] == PAY_OTHER);
  
  if ($_SESSION["autopay"]) {
    array_setall($_SESSION["seats"], "state", ST_PAID);
    /* This is picked up by book() */
  }
}
