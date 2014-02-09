<?php namespace freeseat;


/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.

$Id: format.php 400 2013-01-06 17:54:04Z tendays $

Functions related to formatting raw information for display to the
user
*/

// these are options for print_booked_seats

define("FMT_PRICE",1); // whether to display the prices
define("FMT_CORRECTLINK",10); /* whether to offer a link to change the
			       list of selected seats (implies html) */
define("FMT_SHOWID",4); // whether reservation id should be displayed
define("FMT_HTML",8); /* set to have html code and unset for plain
		         text */
define("FMT_NOCOUNT",16); /* Don't show seat count */
define("FMT_SHOWINFO",32); /* show show info and group according to */

/** set $wide to true to have a full width body. Set $half to true to
 just print the header up to, but not including, </head>, so that you
 can add stuff in that tag. When you're done, call close_head(). If
 leaving $half to false, DO NOT call close_head()! (It's already been
 done).

 And yes, the $wide parameter is not used when $half is set to
 true. I'm leaving it for legacy reasons.
  **/
function show_head($wide = false,$half=false) {
	  echo '<div class="wrap">';
	if (!$half) close_head($wide);
}

/** Print the messages in html format and empty the message array. */
function flush_messages_html() {
  global $messages;
  
  if ( !empty($messages) && count($messages) ) {
  	echo '<div id="freeseat-dialog" title="System Message">';
    foreach ($messages as $message) {
      echo "<p class='warning'>" . htmlspecialchars($message) . "</p>";
    }
    echo '</div>';
  }
  $messages = array();
}

/** Return a string with all messages, separated by newlines, and
 empty the messages array. */
function flush_messages_text() {
  global $messages;

  $result = "";
  foreach ($messages as $message) {
    $result .= "$message\n";
  }
  $messages = array();
  return $result;
}

/** See show_head() */
function close_head($wide = false,$bodyparm = "") {
  flush_messages_html();
  echo '<div id="freeseat-'.( $wide?'wide':'narrow' ).'">';
}

function show_foot() {
  global $lang,$freeseat_vars;

  /** In case messages had been generated after the head was closed... */
  flush_messages_html();

?>
</div>
<div class='dontprint'>
<br style="clear: both;">
<div id="trailerboard">
 <div class="ad"><p class="fine-print">
(<?php
 /* NOTE - Please don't remove the link to the project page when
 running your ticketing site (You may move the link around though).

 Leaving that tiny ad in place just increases chances more people use
 it and therefore motivates me to support it further :) Thanks */
    printf($lang["poweredby"],'<a href="http://freeseat.sf.net" target="_blank">FreeSeat</a>');
?>)</p></div></div></div>
</div><!--end of wrap div from header-->
<?php
}

/* converts a mysql date into a user friendly date */
function f_date($d) {
  global $lang;

  $t=strtotime($d);

  /* can't use strftime directly because openbsd only has English
 locale :-( Don't feel like digging into citrus project for now */

  $weekday = $lang["weekdays"][(int)strftime("%w",$t)];
  $day = (int)substr($d, 8, 2);
  $month = $lang["months"][(int)substr($d,5,2)];
  $year = (int)substr($d, 0, 4);

  return "$weekday $day $month $year";
}

/* converts a mysql time into a user friendly time */
function f_time($d) {
  global $format_time_12hr;
  if ($d=="") return $d;

  if ($format_time_12hr) {  
    return strftime("%I:%M %p",strtotime($d)); 
  } else {
  $x = explode(':',$d);
  if ($x[1]=='00')
    return (int)$x[0]."h";
  else
    return (int)$x[0]."h".$x[1];
  }
}

function f_cat($k) {
  global $lang;
  $cats = array_merge(
    array('', $lang['cat_reduced'], $lang['cat_normal'], $lang['cat_free']),
    do_hook_array('get_cat_names'));
  return $cats[$k];
}

function f_state($s) {
  global $lang;
  switch ($s) {
  case ST_DELETED:
    return $lang["st_deleted"];
  case ST_LOCKED:
    return $lang["st_locked"];
  case ST_BOOKED:
    return $lang["st_booked"];
  case ST_SHAKEN:
    return $lang["st_shaken"];
  case ST_PAID:
    return $lang["st_paid"];
  case ST_FREE:
    return $lang["st_free"];
  case ST_DISABLED:
    return $lang["st_disabled"];
  default:
    return $s.'(?)';
  }
}


function f_payment($p) {
  global $lang;
  switch ($p) {
  case PAY_CCARD:
    return $lang["pay_ccard"];
  case PAY_POSTAL:
    return $lang["pay_postal"];
  case PAY_CASH:
    return $lang["pay_cash"];
  case PAY_OTHER:
    return $lang["pay_other"];
  default:
    return $p.'(?)';
  }
}

function f_mail($m) {
  if ($m) {
    return "<a href='mailto:$m'>$m</a>";
  } else {
    return "n/a";
  }
}

/** outputs an html input for $k, already filled with whatever
_SESSION has to say about it. $d is an optional default value and $a
is optional stuff to add to the html tag
 **/
function input_field($k,$d='',$a='') {
  global $lang;
  if (isset($lang[$k])) echo $lang[$k]."&nbsp;:&nbsp;";
  if (empty($a)) $a = " size=10";
  echo "<input name='$k'";
  if (isset($_SESSION[$k])) $d=htmlspecialchars($_SESSION[$k]);
  if ($d!='')
    echo ' value="'.$d.'"'; // do NOT swap the quote styles as ' in $d
			    // is not quoted
  echo "$a>";
}

/* Return the css class to use when displaying a booking in the given
 state */
function state2css($st) {
  switch ($st) {
  case ST_DELETED: case ST_FREE: return 'stfree';
  case ST_LOCKED: return 'stlocked';
  case ST_BOOKED: return 'stbooked';
  case ST_SHAKEN: return 'stshaken';
  case ST_PAID:   return 'stpaid';
  case ST_DISABLED: default: return 'stdisabled';
  }
}

function show_info($sh) {
  global $lang;
  return sprintf($lang["show_info"],f_date($sh["date"]),f_time($sh["time"]),lang_at_the($sh["theatrename"]));
  // . $sh["theatrelocation"];
 
}

/** first show is a verb, second is a noun
 *
 * returns information about given (or currently selected) show.
 *
 * pass false as second parameter if you don't want a link allowing
 * the user to change the selected show. */
function show_show_info($sh=null,$correctlink=true) {
  global $lang, $page_url;
  if (!$sh) $sh = get_show($_SESSION["showid"]);
  if ($sh) {
    echo htmlspecialchars(show_info($sh));
  } // else : get_show failed
  if ($correctlink) echo " [<a href='".replace_fsp( $page_url, PAGE_REPR )."'>".$lang["change_date"]."</a>]";
}

function print_line($s,$fmt) {
  if ($fmt & FMT_HTML) {
    return "<p class='main'>".htmlentities($s)."</p>";
  } else return "$s\n";
}

// Takes header names from $lang
function print_tableheader($columns,$fmt) {
  global $lang;
  if ($fmt & FMT_HTML) {
    $result = '<table class="summary"><tr>';
    foreach ($columns as $h => $w) $result .= "<th align='center' class='ticket'>".$lang[$h];
  } else {
    $result = '';
    foreach ($columns as $h => $w) $result .= str_pad($lang[$h],$w);
  }
  return $result."\n";
}

function print_tablerow($line,$columns,$fmt) {
  if ($fmt & FMT_HTML) {
    $result = '<tr>';
    foreach ($columns as $h => $w) $result .= "<td align='right' class='$h'>".$line[$h];
  } else {
    $result = '';
    foreach ($columns as $h => $w) $result .= str_pad($line[$h],$w);
  }
  return $result."\n";
}

/** For an entry that has one label and one price, where the label is
spanning all column other than the last.
$label the text written on $numcols-1 columns
$price the *text* written on the last column (for consistency with
    print_tablerow, you must call price_to_string yourself!)
$numcols how many columns there are in total
$fmt controls whether html or rawtext is to be returned */
function print_tablespecialrow($label,$price,$columns,$fmt) {
  if ($fmt & FMT_HTML) {
      return "<tr><td class='price' colspan=".(count($columns)-1)."><b>$label</b><td class='price'>$price\n";
  } else {
    $padding = 0;
    $prev = 0;
    foreach ($columns as $h => $w) {
      // weird trick to avoid counting the last column when padding
      $padding += $prev;
      $prev = $w;
    }
    return str_pad($label,$padding)."$price\n";
  }
}

function print_tablefooter($fmt) {
  if ($fmt & FMT_HTML) return "</table>\n";
  else return "";
}

/** RETURNS a formatted list of selected seats. (i.e. doesn't echo anything)

$fmt: OR-mask of FMT_ constants to control list display.
*/
function print_booked_seats($data = null,$fmt=FMT_CORRECTLINK) {
  global $postaltax, $lang, $page_url;
  if (!$data) {
    $data = $_SESSION["seats"];
    if ($fmt & FMT_PRICE)
      $payment = $_SESSION["payment"];
  } else if ($fmt & FMT_PRICE) {
    if (isset($_SESSION["payment"]))
      $payment = $_SESSION["payment"];
    else {
      reset($data);$bk = current($data); // get first item
      $payment = $bk["payment"];
      /* WARN will not work if user ordered heterogeneous payment */
    }
  }
  $result = '';

  $seatcount = count($data);
  $total = 0; // price
  if (!($fmt & FMT_NOCOUNT)) {
    if ($seatcount==1)
      $result .= print_line($lang["selected_1"],$fmt);
    else
      $result .= print_line(sprintf($lang["selected_n"],$seatcount),$fmt);
  }
  // $seatcount=0 "should" not happen as session_check has already
  // dealt with it.

  $columns = array(); // associate column header to column width in chars
  if ($fmt & FMT_SHOWID) $columns["bookid"] = 8;  // give us some room here
  $columns = array_merge($columns,array("row" => 4,"zoneextra" => 21,
					"col" => 6,"class" => 12));
  if ($fmt & FMT_PRICE) $columns = array_merge($columns,array("cat" => 11,"price" => 6));

  if ($fmt & FMT_SHOWINFO) {
    reset($data);$bk = current($data); // get first item
    $bksh = array("date" => $bk["date"],
		  "time" => $bk["time"],
		  "theatrename" => $bk["theatrename"]);
    /* \n has no effect in html but no <br> is needed there because
 <p></p> has appropriate spaces already */
    $result .= print_line("\n".$lang["date"].": ".show_info($bksh),$fmt);
  }

  $result .= print_tableheader($columns,$fmt);

  foreach ($data as $s) {
    if ($s["zone"]!="" && $s["extra"]!="")
      $s["zoneextra"] = "(".$s["zone"]." / ".$s["extra"].")";
    else
      $s["zoneextra"] = "(".$s["zone"].$s["extra"].")";
    if ($s["row"] == -1) {
      $s["row"] = "";
      $s["col"] = "";
    }
    if ($fmt & FMT_PRICE) {
      $itemprice = get_seat_price($s);
      $s["cat"] = f_cat($s["cat"]);
      $s["price"] = price_to_string($itemprice);
      $total += $itemprice;
    }

    if ($fmt & FMT_SHOWINFO) {
      if (($bksh["date"] != $s["date"]) ||
	  ($bksh["time"] != $s["time"]) ||
	  ($bksh["theatrename"] != $s["theatrename"])) {
	$bksh["date"] = $s["date"];
	$bksh["time"] = $s["time"];
	$bksh["theatrename"] = $s["theatrename"];
	$result .= print_tablefooter($fmt);
	$result .= print_line("\n".$lang["date"].": ".show_info($bksh),$fmt);
	$result .= print_tableheader($columns,$fmt);
      }
    }

    $result .= print_tablerow($s,$columns,$fmt);
  }

  if (($fmt & FMT_PRICE) && !($fmt&FMT_NOCOUNT)) {
    if ($postaltax) {
      if ($payment==PAY_POSTAL) {
	  $tax = postaltax($total);
	$result .= print_tablespecialrow($lang["postaltax"],price_to_string($tax),$columns,$fmt);
	$total += $tax;
      }
    }
    $result .= do_hook_concat('get_print',array_merge($data,array($fmt,$columns)));
    $extra_charges = do_hook_sum('extra_charges', $data);
    if ($extra_charges != 0) {
      $result .= print_tablespecialrow($lang["reduction_or_charges"],price_to_string($extra_charges),$columns,$fmt);
    }
    $total += $extra_charges;
    $result .= print_tablespecialrow($lang["total"],price_to_string($total),$columns,$fmt);
  }

  $result .= print_tablefooter($fmt);

  if (($fmt & FMT_CORRECTLINK)==FMT_CORRECTLINK) {
  	$url = replace_fsp( $page_url, PAGE_REPR );
  	$result .= '<p class="main">'.sprintf($lang["change_seats"],"[<a href='$url'>",'</a>]').'</p>';
  }
  return $result;
}

/** to be shown whenever tickets are put on screen */
function print_legal_info() {
  global $legal_info;
  foreach ($legal_info as $n => $line) {
    echo "<p class='main'>$line</p>";
  }
}


function show_optional($data,$f) {
  global $lang;
  if (isset($data[$f]) && $data[$f]!="") {
    echo "<b>".$lang[$f]."&nbsp;:</b> ".$data[$f];
  }
}

/** name, contact **/
function show_user_info($full = true,$data = null) {
  global $lang;
  if (!$data) $data = $_SESSION;

  echo "<p class='main'><b>".$lang["name"]."&nbsp;:</b> ".$data["firstname"]." ".$data["lastname"]."</p>";
  if ($full) {
    echo '<p class="main">';
    show_optional($data,"phone");
    echo ' ';
    show_optional($data,"email");
    echo "</p>";

    echo '<p class="main">';
    show_optional($data,"address");
    echo "</p>";
    echo '<p class="main">';
    show_optional($data,"postalcode");
    echo ' ';
    show_optional($data,"city");
    echo ' ';
    show_optional($data,"country");
    echo "</p>";
  }
}

function show_pay_info() {
  global $lang;
  echo "<p class='main'><b>".$lang["payment"]."</b> ";
  echo f_payment($_SESSION["payment"]);
  echo "</p>";
}

/** open a sequence of book status update. Usage:
start_notifs();
set_book_status1;
set_book_status2;
set_book_status3;
...
set_book_status4;
send_notifs();

This is to have a single email send to a given email address in case
it is affected by many changes.
**/
function start_notifs() {
  $_SESSION["mailings"] = array();
}

/** (Returns the number of emails sent) */
function send_notifs() {
  global $smtp_sender,$admin_mail,$unsecure_login,$auto_mail_signature,$lang;
  $sentmails = 0;
  $c = get_config();

  foreach ($_SESSION["mailings"] as $email => $bs) {
    $somepaid = false;
    $somedeleted = false;
    if (!$bs[0]["email"]) {
      $body  = $lang["mail-anon"];
      foreach (array("firstname","lastname", "address", "phone") as $n=>$fld) {
	$body .= $lang[$fld].": ".$bs[0][$fld]."\n";
      }
      $body .= "\n";
      $crit = "firstname"; // according to what bookings are grouped (i.e. what
		      // is in $email)
      $mailto = $admin_mail;
    } else {
      $body = sprintf($lang["hello"],$bs[0]['firstname'].' '.$bs[0]['lastname'])."\n";
      $crit = "email";
      $mailto = $bs[0]['email'];
    }
    $paidnow = filter_seats($bs,ST_PAID);
    if (count($paidnow)) {
      $somepaid = true;
      $body.= "\n";
      //      $plur=count($paidnow)>1?"s":"";
      $body.= (count($paidnow)>1?$lang["mail-gotmoney-p"]:$lang["mail-gotmoney"])."\n";
      $body.= print_booked_seats($paidnow,FMT_SHOWID|FMT_SHOWINFO);
      $subject = $lang["mail-sub-gotmoney"];
    }
    $dltd = filter_seats($bs,ST_DELETED);
    $countdltd = count($dltd);
    if ($countdltd) {
      $somedeleted = true;
      $body.= "\n";
      if ($somepaid) {
	$body.= ($countdltd>1?$lang["mail-cancel-however-p"]:$lang["mail-cancel-however"]);
      } else {
	$body.= ($countdltd>1?$lang["mail-cancel-p"]:$lang["mail-cancel"]);
      }
      /* Should we distinguish between manual deletion and automatic
       * deletion? (Automatic is done when it stayed unpaid for too
       * long) */
      $body.= "\n\n";
      $body.= print_booked_seats($dltd,FMT_SHOWID|FMT_SHOWINFO);
      $body.= $lang["mail-oops"]."\n";
      if (!$somepaid) $subject = $lang["mail-sub-cancel"];
    }

    if (!($somepaid || $somedeleted)) {
      /** We get here if the only thing to say is a reminder */
      $countbs=count($bs);
      $body.= sprintf(($countbs>1?$lang["mail-heywakeup-p"]:$lang["mail-heywakeup"]),
		      print_booked_seats(filter_seats($bs,ST_SHAKEN),FMT_PRICE|FMT_SHOWID|FMT_SHOWINFO));

      $subject = $lang["mail-sub-heywakeup"];
    } else {
      /** Now that the main topic of the mail is done we also show
      some reminders. First, what has already been booked and paid */
      $allpaid = get_bookings("booking.$crit='".$bs[0][$crit].($crit=="firstname"?"' and booking.lastname='".$bs[0]["lastname"]:"")."' and state=".ST_PAID." and date >= curdate()");
      $countpaid = count($allpaid);
      if ($countpaid>count($paidnow)) {
	/* (Otherwise it would just amount to showing the same info
	twice) */
	$body.= "\n";
	if ($somedeleted)
	  $body.= $countpaid>1?$lang["mail-notdeleted-p"]:$lang["mail-notdeleted"];
	else // implies $countpaid>1 so no need for that plural mess
	  $body.= $lang["mail-summary-p"];
	$body.= "\n\n";
	$body.= print_booked_seats($allpaid,FMT_SHOWID|FMT_SHOWINFO);
      }
      $unpaid = get_bookings("booking.$crit='".$bs[0][$crit].($crit=="firstname"?"' and booking.lastname='".$bs[0]["lastname"]:"")."' and (state=".ST_BOOKED." or state=".ST_SHAKEN.") and date >= curdate()");
      //      echo mysql_error();
      $cnt=count($unpaid);
      if ($cnt>0) {
	$body.= "\n";
	$body.= sprintf($cnt>1?$lang["mail-reminder-p"]:$lang["mail-reminder"],
			print_booked_seats($unpaid,FMT_PRICE|FMT_SHOWID|FMT_SHOWINFO));
      } else if ($somepaid) {
	$body.= "\n";
	$body.= $lang["mail-thankee"];
      }
    }
    $body.= "\n";
    $body.= "$auto_mail_signature\n";

    send_message($smtp_sender,$mailto,$subject,$body);
    $sentmails ++;
  }

  unset($_SESSION["mailings"]);
  return $sentmails;
}

/** Helper function: Return a portion of an email to be sent to the
user, notifying him of changes in his bookings. Only the part of $data
that has "newstate" $state is used. An empty string is returned in
case no booking in $data has "newstate" equal to $state */
function filter_seats($data,$state) {
  $filtered = array();
  foreach ($data as $n=>$b)
    if ($b["newstate"]==$state)
      $filtered[] = $b;
  return $filtered;
}

/** output a <select> allowing to choose a US state **/
function select_state() {
    global $us_state, $pref_state_code;
	
    echo '<select name="us_state" size="1">';

    $selected = isset($_SESSION["us_state"])?
	$_SESSION["us_state"]
	: $pref_state_code;

    foreach ($us_state as $code => $fullname) {
	echo '<option value="'.$code.'"'.
	    (($code == $selected)?
	     ' SELECTED>'
	     : '>'
	     ). $fullname . '</option>';
	echo '</option>';
    }
    echo '</select>';
}


/** output a <select> allowing to choose a country **/
function select_country() {
    global $country, $top_countries, $pref_country_code;
	
    echo '<select name="country" size="1">';

    $selected = isset($_SESSION["country"])?
	$_SESSION["country"]
	: $pref_country_code;

    /* First show countries pre-selected in config.php */
    if ($top_countries) {
	foreach ($top_countries as $code) {
	    echo '<option value="'.$code.'"';
	    if ($code == $selected) {
		echo ' SELECTED>';
		$selected = ''; // to avoid selecting it again in the full list
	    } else {
		echo '>';
	    }
	    echo $country[$code] . '</option>';
	}
	echo '<option value="" disabled>-----------------------------------</option>';
    }

    /* Now show all countries (note that pre-selected countries are
       shown twice but that doesn't hurt */
    foreach ($country as $code => $fullname) {
	echo '<option value="'.$code.'"'.
	    (($code == $selected)?
	     ' SELECTED>'
	     : '>'
	     ). $fullname . '</option>';
	echo '</option>';
    }
    echo '</select>';
}

function pay_option($p) {
  global $sh;
  if (payment_open($sh,$p)) {
    echo '&nbsp;&nbsp;&nbsp;' . f_payment($p);
    echo "<input type='radio' name='payment' value='$p'";
    if ($_SESSION["payment"]==$p) echo ' checked="checked"';
    echo " /><br />";
  }
}
