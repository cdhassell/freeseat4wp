<?php namespace freeseat;


  /** castpw/setup.php
   *
   * Copyright (c) 2010 by twowheeler
   * Licensed under the GNU GPL 2. For full terms see the file COPYING.
   *
   * Allow access to disabled shows with a password.
   * 
   * $Id$
   *
   */

function freeseat_plugin_init_castpw() {
  global $freeseat_plugin_hooks;

  $freeseat_plugin_hooks['repr_process']['castpw'] = 'castpw_process';
  $freeseat_plugin_hooks['repr_display']['castpw'] = 'castpw_display';
  $freeseat_plugin_hooks['show_unlocked']['castpw'] = 'castpw_unlocked';
  $freeseat_plugin_hooks['config_db']['castpw'] = 'castpw_config_db';
  init_language('castpw');
}

function castpw_process( $ss ) {
  global $lang, $castpw_countdisabled, $castpw_saved, $spectacleid;
  $count_saved = 0;
  foreach ($ss as $sh) 
    if ($sh['disabled']) $count_saved++;

  if (admin_mode() && isset($_POST["reset-disabled"])) {
    $ok = true;
    $castpw_countdisabled = 0;
    $ss = get_shows("spectacle=$spectacleid");
    if ($ss) foreach ($ss as $sh)
      if (isset($_POST["disable-".$sh["id"]])) $castpw_countdisabled++;
    if ($castpw_countdisabled == 0) {
      $ok = freeseat_query("update spectacles set castpw='' where id='$spectacleid'");
      unset($_SESSION['castpw']);
    }
  } else $castpw_countdisabled = $count_saved;

  // process any cast password that was posted to us
  $castpw_saved = trim(m_eval("select castpw from spectacles where id='$spectacleid'"));
  if (isset($_POST['castpw'])) {
    // $castpw = make_reasonable(trim($_POST['castpw']));
    $castpw = preg_replace("/[^A-Za-z0-9]/", "", trim($_POST['castpw']));

    if (!empty($castpw)) {
      if (admin_mode()) {
        // we are creating a new password for this spectacle
        $len = strlen($castpw);
        if ($len < 6) {
          kaboom($lang['castpw_length']);
        } else {
          $res = freeseat_query("update spectacles set castpw='$castpw' where id='$spectacleid'");
          kaboom($lang['castpw_saved'].": $castpw");
        }
      } else {
        // if password matches, unlock the disabled shows
        if (strcasecmp($castpw_saved,$castpw) == 0) {
          // open the disabled shows
          $_SESSION['castpw'] = $castpw;
        } else {
          unset($_SESSION['castpw']);
          kaboom($lang['castpw_incorrect']);
        }
      }
    }
  } else {
    // users get only one shot at this
    unset($_SESSION['castpw']);
  }
}

function castpw_display( $page_url ) {
	global $lang, $castpw_countdisabled,$castpw_saved, $spectacleid;
	// print a form to accept a cast password
	// only show this form if we have shows disabled
	echo '<div id="accordion" style="max-width: 400px; padding: 25px;">';
	echo '<h4>'.$lang['castpw_header'].'</h4>';
	if (admin_mode()) {
		// for the admin user, there is already a form open
		echo '<div>';
		echo $lang['castpw_prompt'] . "<input class='password' type='text' name='castpw' value='$castpw_saved' title='".$lang['castpw_help']."'>";
		echo '</div>';      
	} elseif ($castpw_countdisabled > 0 && $castpw_saved) {
		// for the regular user, create a form to accept the password
		echo "<div>";
		echo "<form action='$page_url&spectacleid=$spectacleid' method='post'>";
		if (function_exists('wp_nonce_field')) wp_nonce_field('freeseat-castpw-password');
		echo "<p class='main fine-print'>&nbsp;&nbsp;";
		echo $lang['castpw_input'] . "<input class='password fine-print' type='password' name='castpw'>&nbsp;";
		echo "<input class='fine-print' type='submit' value='Ok'></p></form>";
		echo '</div>';
	}
	echo '</div>';
}

function castpw_unlocked() {
  return isset($_SESSION['castpw']);
}

function castpw_config_db($user) {
  return config_checksql_for('plugins/castpw/setup.sql', $user);
}

function freeseat_accordion_js() {
	wp_enqueue_script( 'accordion-script', plugins_url( 'accordion.js', __FILE__ ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-accordion' ) );
}

add_action( 'init', __NAMESPACE__ . '\\freeseat_accordion_js' );

