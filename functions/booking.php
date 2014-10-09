<?php namespace freeseat;

/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.

$Id$

Booking related functions


* seat state transitions are as follows:

->2 when booking a seat
->4 when making an invitation
2->3 when reminding the user
2->4 when user pays without needing to be reminded
3->4 when user pays after being reminded
3->5 when user doesn't react to reminder
(can be deleted in any state, even booked, if admin want to)

*/

define("ST_FREE",0); // pseudo state - does not occur in the database
define("ST_LOCKED",1); // pseudo state - does not occur in the
		       // database seat has been selected through the
		       // web interface but not yet booked.
define("ST_BOOKED",2); // seat was booked but not paid
define("ST_SHAKEN",3); // user was reminded
define("ST_PAID",4); // seat was paid
define("ST_DELETED",5); // seat had been booked but expired or got deleted
define("ST_DISABLED",6); // the seat is disabled

/* various means of payment */
define("PAY_OTHER",0); // e.g. for free tickets
define("PAY_CCARD",1); // credit card
define("PAY_POSTAL",2); // "bulletin de versement"
define("PAY_CASH",3); // to be paid just before the show

define("CAT_REDUCED",1);
define("CAT_NORMAL",2);
define("CAT_FREE",3);

/* Pass a condition such as "booking.id=2". (Empty string to get all
   bookings) Returns an array of mysql records.
   $orderby is passed to mysql's ORDER BY
   and $offset $limit are passed to mysql's LIMIT. */
function get_bookings($cond,$orderby = "booking.id",$offset = 0,$limit = 999999999) {
  if ($cond) $cond = "( $cond ) and";
  if ($orderby) $orderby = "ORDER BY $orderby";

  return fetch_all( "SELECT booking.id as bookid, booking.*, seat, seats.row, seats.col, seats.extra, seats.zone, seats.class, showid, shows.date, shows.time, shows.spectacle as spectacleid, theatres.name as theatrename, theatres.id as theatreid, seats.x, seats.y FROM booking, shows, seats, theatres WHERE $cond booking.seat = seats.id and booking.showid=shows.id and shows.theatre = theatres.id $orderby LIMIT " . ( (int)$limit ) . " OFFSET " . ( (int)$offset ) );
}

/** filter all bookings according to the given filter, order them
    according to attr, take $cnt rows starting from $offset'th, and
    return an array of two elements, the value of the attribute $attr
    for the first and last row in the slice. Return false in case of
    problem (eg no rows match the given filter). NOTE conditions can
    only be done on the booking table (not shows, seats etc) */
function get_slice($attr,$filter="",$offset=0) {
	global $bookings_on_a_page;
	if ($filter) $filter = "where $filter";

	$cnt = $bookings_on_a_page;
	/*
	if (!($r = mysql_query("select $attr from booking $filter order by $attr limit $cnt offset $offset")))
		return myboom();
	$cnt = mysql_num_rows($r); // may be smaller than requested $cnt
	if (!($line = mysql_fetch_row($r)))
		return false; // no results
	$res = array($line[0]);
	if (!mysql_data_seek($r,$cnt-1)) return myboom();
	if (!($line = mysql_fetch_row($r))) return myboom("could not read slice's last row"); // ??
	$res[] = $line[0];
	*/
	// going with a less elegant but more WP-friendly approach
	$q = "select $attr from booking $filter order by $attr limit $cnt offset $offset";
	$slice = fetch_all_n( $q );
	if ( sizeof( $slice ) == 0 ) return false;
	$first = array_shift( $slice );
	$last = array_pop( $slice );
	return array( $first[0], $last[0] );
}

function get_booking($id) {
  if ($zou = get_bookings("booking.id=$id")) {
    //    print_r($zou);
    return $zou[0];
  } else return null;
}

/** this function MUST NOT FAIL (at least not after check_session(3)
returned true)
*/
function book($glob,$seat) {
	global $lang;
	
	if (get_seat_state($seat["id"],$seat['showid'],true)!=ST_FREE) {
		/* This may happen only in case the software has a bug ... I leave
		this check here because check_session may not have checked it
		in case our seat locks where still valid. Again, it should
		never be required but let's play safe. */
		kaboom($lang["err_occupied"]);
		return false;
	}

	/* fill in optional fields */
	if (!isset($glob["country"])) $glob["country"]="";
	if (!isset($glob["us_state"])) $glob["us_state"]="";
	if (isset($glob["groupid"]) && $glob["groupid"]!=0)
		$query = "insert into booking (groupid,showid,seat,state,cat,firstname,lastname,email,phone,timestamp, payment,address,postalcode,city,us_state,country) values (".$glob["groupid"].",";
	else 
		$query = "insert into booking (showid,seat,state,cat,firstname,lastname,email,phone,timestamp, payment,address,postalcode,city,us_state,country) values (";

	$state = isset($seat["state"])? $seat["state"]:
		($seat["cat"]==CAT_FREE?ST_PAID:ST_BOOKED);
	if (freeseat_query($query.
		$seat["showid"].','.$seat["id"].','.$state.','.$seat["cat"].',"'.
		mysql_real_escape_string($glob["firstname"]).'","'.
		mysql_real_escape_string($glob["lastname"]).'","'.
		mysql_real_escape_string($glob["email"]).'","'.
		mysql_real_escape_string($glob["phone"]).'",NOW(),'.
		$glob["payment"].',"'.
		mysql_real_escape_string($glob["address"]).'","'.
		mysql_real_escape_string($glob["postalcode"]).'","'.
		mysql_real_escape_string($glob["city"]).'","'.
		mysql_real_escape_string($glob["us_state"]).'","'.
		mysql_real_escape_string($glob["country"]).
		'")')) {
		$bookid = freeseat_insert_id();
		sys_log("creating $bookid as $state (".f_state($state).")");
		do_hook_function('book',$bookid);
		return $bookid;
	} else {
		kaboom( mysql_error() ); // TODO - don't display mysql errors to client
		return false;
	}
	/* WARN - do NOT remove the lock in seat_locks here - leave it until
	it expires normally, as protection agains some race conditions
	(cf. lock_seats()) depend on it staying for a while. (The removal
	found in e.g. logoff.php is safe, though - the entry in booking
	would then be old enough) */
}

/**
1) Records the given values in ccard_transactions.
2) Sets the corresponding booking entries to ST_PAID, verifying they
are supposed to cost the given amount.

In case the amount does not match, set as many tickets to ST_PAID as
possible, and then send a mail to $admin_mail.

If at least one seat was paid then a mail is sent to the user,
thanking him for it.

Returns true in case of success. In case of problem, a mail is
sent to $admin_mail and false is returned.

The parameters are assumed to be well-formed numbers. The amount is in
$currency, but the one inserted into the database is in hundredth of a $currency. **/
function process_ccard_transaction( $groupid, $transid, $amount ) {
	global $lang, $currency;
	$balance = $amount;

	$success = true; // let's be optimistic
	start_notifs();	
	$rows = m_eval( "SELECT count(groupid) FROM ccard_transactions WHERE groupid=$groupid AND numxp='$transid' " );
	if ($rows==0) {  // should be equal to 1 or 0
		freeseat_query( "INSERT INTO ccard_transactions (groupid,numxkp,amount) VALUES (%d, %s, %d)", $groupid, quoter($transid), $amount );
	} else {
		sys_log( sprintf( $lang["err_ccard_mysql"], freeseat_mysql_error() );
		$success = false;
		// could not log it but let's at least try to process it. (PANIC mail will be sent anyway)
	}
	$bs = get_bookings("booking.groupid=$groupid or booking.id=$groupid");
	if ($bs===FALSE) {
		myboom($lang["err_bookings"]);
		$success = false;
	} else foreach ($bs as $n => $b) {
		if ($b["cat"] != CAT_FREE) {
			if ($balance >= get_seat_price($b)) {
				if (!set_book_status($b,ST_PAID)) {
					myboom( sprintf( $lang[ "err_ccard_pay" ], $b[ "bookid" ] ) );
					$success = false;
				} else {
					if ( mysql_affected_rows() == 1 ) {
						$balance -= get_seat_price($b);
					} else {
						myboom(sprintf($lang["err_ccard_repay"],$b["bookid"]));
					}
				}
			} else {
				myboom(sprintf($lang["err_ccard_insuff"],
					$b["bookid"],
					price_to_string(get_seat_price($b)),
					price_to_string($balance),
					$currency
				));
				$success = false;
			}
		}
	}
	if ($balance>0) {
		kaboom(sprintf($lang["err_ccard_toomuch"],
			price_to_string($balance),
			price_to_string($amount),
			$currency
		));
		$success = false;
	}
	send_notifs();
	return $success;
}

/** update the status for a booking $b (sql record like returned by
get_booking), and extend the start_notifs() database.
Returns true if it had an effect, false otherwise. */
function set_book_status($b,$state) {
  global $smtp_sender,$lang, $now;
  sys_log("setting ".$b["bookid"]." to $state (".f_state($state).")");
  $q = "update booking set state=$state";
  if ( $rows = freeseat_query( "$q where state!=" . ST_DELETED . " and id=" . $b[ "bookid" ] ) ) {
    if ( ( $rows > 0 ) ) {
      // only notify changes about shows today or later
      if (strtotime($b["date"]." 23:59:59")>$now) {
        $b["newstate"] = $state;
	      // if there is no email then we do as if we "mail to the
	      // name". The actual email address for them will then be
	      // $admin_mail.
        if ($b["email"]) $mailto = $b["email"];
        else $mailto = $b["firstname"].' '.$b["lastname"];
        if (!isset($_SESSION["mailings"][$mailto]))
          $_SESSION["mailings"][$mailto] = array($b);
        else
          $_SESSION["mailings"][$mailto][] = $b;
        return true;
      }
    } else sys_log("no affected rows");
  } else myboom($lang["err_setbookstatus"]);
  return false;
}


