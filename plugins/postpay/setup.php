<?php namespace freeseat;

  /** postpay/setup.php
   *
   * Copyright (c) 2010 by twowheeler
   * Licensed under the GNU GPL 2. For full terms see the file COPYING.
   *
   * Lets the user pay for previously booked tickets.
   *
   * $Id$
   *
   */

function freeseat_plugin_init_postpay() {
  global $freeseat_plugin_hooks;

  $freeseat_plugin_hooks['front_page_public']['postpay'] = 'postpay_fplink';  
  $freeseat_plugin_hooks['config_db']['postpay'] = 'postpay_config_db';
  init_language('postpay');
}

function postpay_fplink() {
    global $take_down, $lang;
    echo '<p class="main">';
    if ($take_down)
	printf($lang["remail"],'[',']');
    else
	printf($lang["remail"],'[<a href="plugins/postpay/index.php">','</a>]');
    echo '</p>';
}

function postpay_config_db($user) {
  return config_checksql_for('plugins/postpay/setup.sql', $user);
}

