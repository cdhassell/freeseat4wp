<?php namespace freeseat;


function freeseat_plugin_init_bookingnotes() {
    global $freeseat_plugin_hooks;

    $freeseat_plugin_hooks['bookinglist_process']['bookingnotes'] = 'bookingnotes_process';
    $freeseat_plugin_hooks['bookinglist_tableheader']['bookingnotes'] = 'bookingnotes_tableheader';
    $freeseat_plugin_hooks['bookinglist_tablerow']['bookingnotes'] = 'bookingnotes_tablerow';
    $freeseat_plugin_hooks['config_db']['bookingnotes'] = 'bookingnotes_config_db';
}

function bookingnotes_process() {
  /* Update notes... */
  global $lang;

  $affected = 0;

  foreach ($_POST as $key => $value) {
    if ($key{0}=='n' && strlen($value)>0) { 
      $noteid = substr($key,1);
      if (is_numeric($noteid)) {
	mysql_query('update booking set notes="'.mysql_real_escape_string($value).'" where id='.((int)$noteid));
	if (mysql_affected_rows()==1) {
	  $affected ++;
	}
      }
    }
  }

  if ($affected==1) kaboom($lang["notes-changed"]);
  else if ($affected) kaboom(sprintf($lang["notes-changed-p"],$affected));
}

function bookingnotes_tableheader() {
  global $lang;

  return '<th>'.$lang["notes"];
}

function bookingnotes_tablerow($b) {
  global $checkboxes;

  if ($checkboxes) {
    return'<td><input name="n'.$b['bookid'].'" value="'.htmlspecialchars($b['notes']).'" maxlength=255>';
  } else {
    return '<td>'.htmlspecialchars($b['notes']);
  }
}

function bookingnotes_config_db($user) {
  return config_checksql_for('plugins/bookingnotes/setup.sql', $user);
}


