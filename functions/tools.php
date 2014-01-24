<?php namespace freeseat;


/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.
$Id: tools.php 350 2011-06-05 08:32:03Z tendays $
*/

/** Append FS_PATH to the given variable if it is a relative url.
 *
 * $path: an absolute url or an url relative to the FreeSeat root, of
 *  the form 'some/relative/path', '/an/absolute/path' or
 * 'scheme://hostname/etc'. Windows-style paths 'C:\something' or
 * 'relative\path' are not supported.
 *
 * returns $path updated to work from the current directory, relying
 * on FS_PATH being correctly set.
 */
function apply_fspath($path) {
  if (preg_match('@^([^:]*://|/)@', $path)) {
    /* $path is absolute */
    return $path;
  } else {
    return FS_PATH . $path;
  }
}

/** Replace strange chars by underscores in $s.**/
function make_reasonable($s) {
  return just_keep($s," -+.,@0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZÀÁÃÂÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòôõö÷øùúûüýþÿ");
}

/** replace all chars of $x that are NOT in $ok by underscores **/
function just_keep($x,$ok) {

  for ($p=0 ; $p<strlen($x) ; $p++ ) {
    if (strpos($ok,$x{$p})===FALSE) {
      $x{$p} = '_';
    }
  }
  return $x;
  
}

/** cancel magic quotes on the given data if needed. YAY for PHP's
    worst design decision ever. **/
function nogpc($x) {
  return get_magic_quotes_gpc()?stripslashes($x):$x;
}

/** turn the given string into a (mysql-compliant) date. Returns
    "0000-00-00" if not happy. **/
function sanitise_date($x) {
  // When passed an empty or 0000-00-00 string, keep the 0000-00-00
  return ((!$x) || ($x=="0000-00-00") || (($t = strtotime($x))===false))?
    "0000-00-00":
    date("Y-m-d",$t);
}

/** turn the given string into a (mysql-compliant) time. Returns
    "00:00:00" if not happy. **/
function sanitise_time($x) {
  return ((!$x) || (($t = strtotime($x))===false))?
    "00:00:00":
    date("H:i:s",$t);
}

/** name should be self-explaining.
I won't piss off users with über-elaborated checks - if a user really
wants to enter an invalid address, then just let him do it. */
function is_email_ok($m) {
	// wrapper for WP is_email()
	return is_email($m);
}

function kaboom($s) {
  global $messages;
  $messages[] = "$s";
  return false; // to allow doing nice things like..
  // ..return kaboom("scary error message") or $success = kaboom("sorry dude");
}

/** Abort current script (with the given error message, if any) */
function fatal_error($s='',$print_header=true) {
  global $lang, $page_url;
  if ($s) kaboom($s);
  if ($print_header) show_head();
  else {
    flush_messages_html();
  }

  echo '<p class="main">';
  $url = ( empty( $page_url ) ? $_SERVER['PHP_SELF'].'?page=freeseat-admin' : $page_url );
  printf($lang["backto"],'[<a href="'.$page_url.'&fsp=0">'.$lang["link_index"].'</a>]');
  echo '</p>';
  
  show_foot();
  exit;
}

/** You must call this function if you intend to use a function that
alters ticket bookings, i.e. book() or set_book_status().
Pass a "role" that identifies who's responsible for the change */
function prepare_log($setrole) {
  global $role,$logopen;
  $role = $setrole;
  //  $loghandle = null;  // does not work concurrently
  //  define_syslog_variables(); // does not work ...
  //  openlog("freeseat",LOG_ODELAY|LOG_PID,LOG_USER);
}

function sys_log($s) {
  global $role,$logfile,$loghandle;
  if ($logfile) {
    $loghandle = fopen(apply_fspath($logfile),"a");
    $timestamp = date('D d M Y h:i A');
    fwrite($loghandle,"$timestamp : $role : $s\n"); 
    fclose($loghandle);
  } else {
    trigger_error("freeseat notice : $role : $s");
  }
}

function log_done() {
  /*  global $loghandle;
  if ($loghandle) fclose($loghandle);
  $loghandle = null; */
}

/* Whether the given timestamp is a holiday. For now it is assumed not
 to depend on time, but only on date */
function is_holiday($t) {
  /* WARN For now only the weekend is considered holiday */
  return (($t+262800) % 604800) > 432000;
}

/** Pass two timestamps ($a<$z) ; this function returns how many
seconds of holidays there are between the two timestamps */
function holidays_between($a,$z) {
  /* WARN For now only the weekend is considered holiday */

  // 604800: seconds in a week
  // 262800: shift to have Monday at 0am have weektime=0
  // (instead of Thursday 1am)
  $weektimea = ($a+262800) % 604800;
  $weektimez = ($z+262800) % 604800;
  $weeka = $a-$weektimea; // timestamp for beginning of week
  $weekz = $z-$weektimez;
  // holiday seconds until the end of the week
  $lefta = ($weektimea>432000)?604800-$weektimea:172800;
  $leftz = ($weektimez>432000)?604800-$weektimez:172800;

  return ($weekz-$weeka)*2/7-($leftz-$lefta);
}

/** return the timestamp corresponding to $delta seconds before $timestamp
(which is a unix timestamp), skipping any bank holiday. */
function sub_open_time($timestamp,$delta) {
  //  echo "X"; // DEBUG (see how much recursion there is)
  if ($delta==0)
    return $timestamp;

  /* following lines are an optimisation and don't change actual value returned. */
  if ($delta<0) {
    if (is_holiday($timestamp)) $timestamp -= (($timestamp+3600)%86400)-86399;
  } else {
    if (is_holiday($timestamp)) $timestamp -= ($timestamp+3600)%86400;
  }

  return sub_open_time($timestamp-$delta,holidays_between($timestamp-$delta,$timestamp));
}

/** first output mysql error then the optional parameter. Returns
false to be used as: return myboom(..) */
function myboom($s = false) {
  if ($s==false)
    kaboom(mysql_error());
  else
    kaboom("$s (".mysql_error().")");

  return false;
}

/* Given two arrays a and b, return an array c such that $c[$x] =
$a[$x] whenever $b[$x] is unset and $b[$x] otherwise. Why PHP doesn't
provide an array merging function that behaves *predictably* is beyond
me */
function array_union($a,$b) {
  $c = $a;
  foreach ($b as $k=>$v) $c[$k] = $v;
  return $c;
}

/** Same as array_flip (flip keys and values) but trims and down-case
 the value *before* the flip. */
function array_fliplowtrim($a) {
  $r = array();
  foreach ($a as $k => $v) {
    $r[strtolower(trim($v))] = $k;
  }
  return $r;
}

/* aa being an array of arrays, Set key k to value v for each array in
   aa */
function array_setall(&$aa,$k,$v) {
  foreach ($aa as $n => $a) $aa[$n][$k] = $v;
}

/** I moved the anti-magic quotes functionality into no_gpc, which
   must now be called immediately when reading stuff from gpc */
function quoter($value) {
    return "'" . mysql_real_escape_string($value) . "'";
}

/** path being a url relative to the freeseat directory, returns the corresponding absolute url.
 *
 * Set $secure to true or false if you want to for use of respectively
   $sec_area or $normal_area, for links meant only for the admin or
   only for non-admins. Leave unset (null) to use the current status.
 */
function freeseat_url($path, $secure=null) {
	/*global $normal_area, $sec_area;
	if ($secure === null) $secure = admin_mode();
	return ($secure? $sec_area : $normal_area) . '/' . $path; */
	return plugins_url() . '/freeseat/' . $path;	
}

function enhanced_list_box($options, $params, $text_new, $resultname) {
// From http://www.cgi-interactive-uk.com/populate_combo_box_function_php.html
// creates a list box from data in a mysql field
	$sql  = "select " . $options['id_field'];
	$sql .= ", " . $options['value_field'];
	$sql .= " from " . $options['table'];
	/* append any where criteria to the sql */
	if(isset($options['where_statement'])) {
    $sql .= " where " . $options['where_statement'] ;
	}  
	/* set the sort order of the list */
	$sql .= " order by " . $options['value_field'];
	$result = fetch_all_n($sql);
	if (!$result) {
		kaboom(mysql_error()); 
		return;
	}
	echo '<select name="', $resultname, '" ', $params, ' size="1">';
	foreach ( $result as $row ) {
		if($row[0] == $options['highlight_id']) {
			echo '<option value="', $row[0], '" SELECTED>', $row[1], '</option>';
		} else {
			echo '<option value="', $row[0], '">', $row[1], '</option>';
		}
	}
	if ($text_new)  {
		echo '<option value="0" ' . (($options['highlight_id']==0)?'SELECTED':'') . '>' . 
			$text_new . '</option>';
	}
	echo '</select>';
}



