<?php namespace freeseat;

/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.

$Id$

Session and booking session related functions
*/

/** Call this after creating the session, for passing on messages that
were created after show_head(). WARNING on register_globals
systems you MUST reset $messages yourself at the beginning (db_connect
does it) **/
function load_alerts() {
	global $messages;
	if ( isset( $_SESSION[ "messages" ] ) ) {
		$messages = array_merge( $_SESSION[ "messages" ], $messages );
	}
	$_SESSION[ "messages" ] = array( );
}

function admin_mode() {
    return current_user_can( 'manage_freeseat' );
}

/** check that the session is sufficiently defined. When you pass a
    value $n all conditions from 1 to $n must be satisfied
    
    $n = 0: booking must be enabled.
    $n = 1: a valid date must be specified
    $n = 2: seats must be specified
    $n = 3: seats must still be available
    $n = 4: we must be ready to book (a name must be provided, and
              more in case of ccard payment)

    set $quiet to true to hide non critical warnings
*/
function check_session( $n, $quiet=false ) {
	global $lang, $page_url;
	/* 
	We are checking each time that the show exists - that's maybe a little
	overkill (checking @ the end would suffice but anyway we are more
	consistent this way)
	*/

	/* snb = still-not-booked. Note, if this is false then we should
	 "normally" always succeed. The checks done here are useful in
	 case there is a bug in the code. */

	$snb = (!isset($_SESSION["booking_done"])) || !$_SESSION["booking_done"];
	if ( !isset( $_SESSION[ "showid" ] ) ) {
		kaboom($lang["err_session"]." ($n)");
		sys_log( "Lost Session!  Session = ".print_r($_SESSION,1));
		sys_log( "Post = ".print_r($_POST,1));
		$url = PAGE_INDEX;  // "index";
	} else if (do_hook_exists("check_session", 0)) {
		$url = PAGE_INDEX;  // "index";
	} else if ($n >= 1 && !($sh = get_show($_SESSION["showid"]))) {
		$url = PAGE_REPR; // "repr";
	} else {
		$remaining = show_closing_in($sh);
		if ($n >= 1 && $remaining<=0 && (!admin_mode()) && $snb) {
			kaboom($lang["err_closed"]);
			$url = PAGE_REPR; // "repr";
		} else if ($n>=1 && do_hook_exists("check_session", 1)) {
			$url = PAGE_REPR;  // "repr";
		} else {
			if ((!admin_mode()) && $snb && 
				($remaining<=15) && !$quiet) {
				/* This is a warning but not a failure */
				if ($remaining==1)
					kaboom($lang["warn_close_in_1"]);
				else
					kaboom(sprintf($lang["warn_close_in_n"],$remaining));
			}
			if ($n>=2 && ((!isset($_SESSION["seats"])) || count($_SESSION["seats"])==0)) {
				kaboom($lang["err_checkseats"]);
				$url = PAGE_SEATS;  // "seats";
			} else if ($n>=2 && do_hook_exists("check_session", 2)) {
				$url = PAGE_SEATS;  // "seats";
			} else if ( $n >= 3 && $snb && !check_seats() ) {
				unlock_seats(false); // this one is probably not needed?
				kaboom($lang["err_occupied"]);
				$url = PAGE_SEATS;  // "seats";
			} else if ( $n >= 3 && do_hook_exists("check_session", 3)) {
				$url = PAGE_SEATS;  // "seats";
			} else if ( $n >= 4 ) {
				if ((!isset($_SESSION["lastname"])) || $_SESSION["lastname"]=='') {
					kaboom($lang["err_noname"]);
					$url = PAGE_PAY;  // "pay";
				} else if (isset($_SESSION["email"]) && $_SESSION["email"] && !is_email_ok($_SESSION["email"])) {
					kaboom($lang["err_bademail"]);
					$url = PAGE_PAY;  // "pay";
				} else if ( $snb && !payment_open($sh,$_SESSION["payment"])) {
					/* WARN: if someone handcrafts a PAY_CASH payment method
					*before* it is available he'll still get this error message,
					that it is "no longer" available but this bug doesn't occur
					with normal client use */
					kaboom(sprintf($lang["err_paymentclosed"],f_payment($_SESSION["payment"])));
					$url = PAGE_PAY;  // "pay";
				} else if (do_hook_exists("check_session", 4)) {
					$url = PAGE_PAY;  // pay";
				} else return; // success
			} else return; // success
		}
	}

	/* at this point we have failed */
	show_head();
	echo "<p class='main'>";
	$pagelist = array( 
		0 => 'index', 
		1 => 'repr', 
		2 => 'seats', 
		3 => 'pay' );
	$page = $pagelist[ $url ]; 
	$newpage_url = replace_fsp( $page_url, $url );
	printf( $lang["backto"], "[<a href='$newpage_url'>".$lang["link_$page"]."</a>]" );
?>
<script type='text/javascript'>
/* <![CDATA[ */
var freeseatPopupUrl = "<?php echo $newpage_url; ?>";
/* ]]> */
</script>
<?php
	echo "</p>\n";
	show_foot();  
	exit();  
}

function kill_booking_done() {
	if (isset($_SESSION["booking_done"]) && $_SESSION["booking_done"] || 
		isset($_POST["clearcart"])) {
		unlock_seats();
		$_SESSION["booking_done"] = false;
		// foreach'ing and unset'ting on the same array tends to create instabilities.
		// cf. http://bugs.php.net/bug.php?id=36646
		$sessioncopy = $_SESSION; // ... so looping on a copy instead
		foreach ($sessioncopy as $key => $v) if (substr($key,0,6)=='nncnt-') {
			unset($_SESSION[$key]);
		}
		unset($_SESSION["showid"]);
		unset($_SESSION["mail_sent"]);
		unset($_SESSION["groupid"]);
		foreach (array(CAT_REDUCED, CAT_NORMAL, CAT_FREE) as $cat) { 
			if (isset($_SESSION["ncat$cat"])) 
				unset($_SESSION["ncat$cat"]);
		}
		do_hook('kill_booking_done');
		sys_log("kill booking done");
	}
}


