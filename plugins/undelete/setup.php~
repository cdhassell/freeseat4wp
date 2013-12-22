<?php

require_once (FS_PATH . "functions/seat.php");
require_once (FS_PATH . "functions/shows.php");
require_once (FS_PATH . "functions/booking.php");
require_once (FS_PATH . "functions/tools.php");

function freeseat_plugin_init_undelete() {
    global $freeseat_plugin_hooks;

    $freeseat_plugin_hooks['bookinglist_process']['undelete'] = 'undelete_run';
    $freeseat_plugin_hooks['bookinglist_pagebottom']['undelete'] = 'undelete_buttons';
}

function undelete_run() {
  global $lang;
  if (isset($_POST["rebook"])) {
    $ap = $_SESSION["adminpass"];

    unlock_seats();

    $_SESSION = array(); // we make sure there is no stall personal info
    // or seat selection or whatever (that could
    // give weird errors)

    $_SESSION["adminpass"] = $ap;

    $seats = array();
    $nred = 0;
    $ninvite = 0; // (tendays) TODO migrate this to use a cat-associative array as in pay.php
    $warn = array();

    $failure = false;
    foreach ($_POST as $key => $value) {
      if (is_numeric($key)) {
	$b = get_booking((int)$key);
	if ($b && ($b["state"]==ST_DELETED)) {
	  if (isset($_SESSION["showid"]) && ($_SESSION["showid"]!=$b["showid"])) {
	    kaboom($lang["err_ic_showid"]);
	    $failure = true;
	    break;
	  } else {
	    $_SESSION["showid"] = $b["showid"];
	  }
	  foreach (array('lastname','firstname','email','phone','payment') as $fld) {
	    if ($b[$fld]) {
	      if (isset($_SESSION[$fld]) && $_SESSION[$fld] && ($_SESSION[$fld]!=$b[$fld])) {
		if (!$warn[$fld]) {
		  $warn[$fld] = true;
		  kaboom($lang["err_ic_$fld"]);
		}
	      } else {
		$_SESSION[$fld] = $b[$fld];
	      }
	    }
	  }

	  $s = get_seat($b["seat"]); // (can't we just pass $b ?)
	  switch ($s["cat"] = $b["cat"]) {
	  case CAT_REDUCED:
	    $nred++;
	    break;
	  case CAT_FREE:
	    $ninvite++;
	    break;
	  }
	  $seats[$s['id']] = $s;
	}
      }
    }
    if (!$failure && (count($seats)>0)) {

      $sh = get_show($_SESSION["showid"]);
      array_setall($seats,"date",$sh["date"]);
      array_setall($seats,"time",$sh["time"]);
      array_setall($seats,"theatrename",$sh["theatrename"]);
      array_setall($seats,"spectacleid",$sh["spectacleid"]);


      $_SESSION["seats"] = $seats;
      $_SESSION["nreduced"] = $nred;
      $_SESSION["ninvite"] = $ninvite;
      $_SESSION["messages"] = $messages;
      header("Location: ". FS_PATH ."confirm.php");
      exit;
    }
  }
}

function undelete_buttons() {
  global $filterst, $lang;

  switch ($filterst) {
  case ST_DELETED:
    echo '<ul><li><p class="main">';
    printf($lang["rebook"],'<input type="submit" name="rebook" value="','">');
    echo '</p></ul>';
    break;
  case 0:
    echo '<ul><li><p class="main">('.$lang["rebook-info"].')</p></ul>';
  }
}

?>