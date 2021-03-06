<?php namespace freeseat;

/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.

$Id: index.php 341 2011-04-25 19:03:48Z tendays $

Modifications for Wordpress are Copyright (C) 2013 twowheeler.
*/

/*
 * Displays a page to confirm the sale
 * Replaces the former confirm.php
 */
function freeseat_confirm( $page_url )
{
	global $lang, $sh;
		
	load_alerts();
	kill_booking_done();
	
	// capture the user data from the pay form
	foreach (array("firstname","lastname","phone","email","address","postalcode","city","us_state","country") as $n => $a) {
		if (isset($_POST[$a])) $_SESSION[$a] = sanitize_text_field(nogpc($_POST[$a]));
	}

	// remove any seats checked by the user for removal
	foreach ($_SESSION['seats'] as $id => $seat) {
		if (isset($_POST[$id])) unset($_SESSION['seats'][$id]);
	}
	
	/* See how many seats must be marked reduced/invitation. This map maps
		CAT_xyz entries to the number of requested seats. */
	$hook_catmap = array();
	
	if (isset($_POST["ncat".CAT_REDUCED]))
		$hook_catmap[CAT_REDUCED] = ceil(abs($_POST["ncat".CAT_REDUCED]));
	else if (isset($_POST["cat"])) {
		switch ($_POST["cat"]) {
			case CAT_REDUCED:
				$hook_catmap[CAT_REDUCED] = 1;
				break;
			case CAT_FREE:
				if (admin_mode()) $hook_catmap[CAT_FREE] = 1;
				break;
		}
	}
	
	if (admin_mode() && isset($_POST["ncat".CAT_FREE])) {
		$hook_catmap[CAT_FREE] = ceil(abs($_POST["ncat".CAT_FREE]));
	}
	
	$_SESSION["autopay"] = isset($_POST["autopay"]) and admin_mode() and ($_SESSION["payment"] == PAY_OTHER);
	if ($_SESSION["autopay"]) {
		array_setall($_SESSION["seats"], "state", ST_PAID);
		/* This is picked up by book() */
	}
	do_hook('pay_process'); // this may modify the $hook_catmap variable
	
	/* Note: Should not be necessary to check the value is valid because
	check_session will anyway fail if given an illegal payment method but
	let's play safe */
	if ( isset( $_POST[ "payment" ] ) ) {
		switch ( $_POST["payment"] ) {
			case PAY_CCARD:
				$_SESSION[ "payment" ] = PAY_CCARD;
				break;
			case PAY_CASH:
				$_SESSION[ "payment" ] = PAY_CASH;
				break;
			case PAY_OTHER:
				$_SESSION[ "payment" ] = PAY_OTHER;
				// allow a sale from the office to proceed even if we have no user data
				if ( ( !isset( $_SESSION[ "lastname" ] ) ) || $_SESSION[ "lastname" ] == "" )
					$_SESSION[ "lastname" ] = $lang[ "pay_other" ];
				break;
			default: // case PAY_POSTAL:
				$_SESSION[ "payment" ] = PAY_POSTAL;
				break;
		}
	}
	check_session( 4 );
	if ( !empty( $hook_catmap ) ) {
		foreach ($hook_catmap as $cat => $n) {
			$_SESSION["ncat$cat"] = $n;
		}
	
		/* This is the only place where we pass true to that function. The
		reason is that it is the only place where the user explicitly gave
		those ncatxyz values, just below the ticket list, so it makes sense
		to
		1. shout at him for giving nonsensical values
		2. correct them in-session
	
		In contrast, if for instance the user reduced the number of selected
		seats to get below the number of requested reduced seats, we are not
		going to shout at him or change the in-session $ncatX behind his
		back. */
		compute_cats(true);
	}
	
	if ($_SESSION[ "payment" ]!=PAY_OTHER)  {	
		if ( !$_SESSION["email" ] ) {
			if ( !$_SESSION[ "phone" ] )
				kaboom( $lang[ "warn-nocontact" ] );
			else
				kaboom( $lang[ "warn-nomail" ] );
		}
	}
	
	show_head();
	echo '<h2>' . $lang[ "confirmation" ] . '</h2>';
	echo '<p class="main">'.$lang[ "intro_confirm" ].'</p>';	
	echo print_booked_seats(null,FMT_PRICE|FMT_CORRECTLINK|FMT_SHOWINFO|FMT_FORM);
	echo '<div class="user-info">';	
	echo '<h3>'.$lang['youare'].'</h3>';
	show_user_info();
	if (get_total() > 0) show_pay_info();
	echo '<p class="main">';
	$url = replace_fsp( $page_url, PAGE_PAY );
	printf( $lang[ "change_pay" ], "[<a href='$url'>", "</a>]" );
	echo '</p>';
	echo '</div>';
	echo '<div class="user-info">';
	echo '<form action="' . replace_fsp( $page_url, PAGE_FINISH ) . '" method="post">';
	if (function_exists('wp_nonce_field')) wp_nonce_field('freeseat-confirm-purchase');
	do_hook('confirm_bottom');
	echo '<!-- autopay -->';
	if (admin_mode() and ($_SESSION["payment"] == PAY_OTHER)) {
    	echo '<p class="main"><input type="checkbox" name="autopay" checked="checked"> Mark tickets as paid</p>';
	}
	// let's check that the user actually owes us something
	if ( $_SESSION[ "payment" ] == PAY_CCARD && get_total() > 0 ) {
		echo '<h2>'.$lang["make_payment"].'</h2>';
		do_hook( 'ccard_confirm_button' );
	} else {
		echo '<input class="button button-primary" type="submit" value="'.$lang["book_submit"].'">';
	}
	echo '</form>';
	echo '</div>';
	show_foot();
}	// end of freeseat_confirm

