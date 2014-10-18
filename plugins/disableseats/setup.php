<?php namespace freeseat;

  /** disableseats/setup.php
   *
   * Copyright (c) 2010 by twowheeler
   * Licensed under the GNU GPL 2. For full terms see the file COPYING.
   *
   * Disabled seating map
   *
   * $Id$
   *
   */

function freeseat_plugin_init_disableseats() {
	add_action( 'admin_menu', __NAMESPACE__ . '\\freeseat_disableseats_menu' );
    init_language('disableseats');
}

function freeseat_disableseats_menu() {
	// add a new admin menu page for disabled seat administration
	// this must be run *after* the function freeseat_admin_menu()
	add_submenu_page( 'freeseat-admin', 'Disable Seats', 'Disable Seats', 'administer_freeseat', 'freeseat-disableseats', __NAMESPACE__ . '\\freeseat_disableseats' );
}

/*
 *  Calculates totals by class for this show  
 */
function disableseats_summarize( $zone ) {
	// Returns an array with number of seats, total and disabled, 
	// by class, for the zone $zone. We will call this only once per zone.
	// $cls == 0 is the total for all classes
	global $sh;
	$counts = array('total'    => array(0,0,0,0,0),
					'disabled' => array(0,0,0,0,0));
	$theatre = $sh['theatre'];
	for ($cls = 1; $cls <= 4; $cls++ ) {
		$counts['total'][$cls] = m_eval("select count(*) from seats where theatre=$theatre and class=$cls and zone='$zone'");
		$counts['total'][0] += $counts['total'][$cls];
		$counts['disabled'][$cls] = m_eval("select count(*) from booking,seats where showid=".$sh["id"]." and row!=-1 and class=$cls and state=".ST_DISABLED." and booking.seat=seats.id and zone='$zone'");
		$counts['disabled'][0] += $counts['disabled'][$cls]; 
	}
	return $counts;
}

/*
 *  Callback function for render_seatmap()
 *  Output one seat for the disabled seatmap
 */
function disableseats_seat($currseat) {
	global $sh;
	// colours are based on class, and disabled seats are checked
	$st = get_seat_state($currseat['id'],$sh['id']);
	$colour = "cls".$currseat['class'];
	if (strpos($currseat['extra'], 'Table')===false) {
		$text = "Row ".$currseat['row']." Seat ".$currseat['col'];
	} else {
		$text = "Table ".$currseat["row"]."-".$currseat["col"];
	}
	echo "<td colspan='2' align='center' class='$colour' title='$text'>";
	if (($st==ST_FREE) || ($st==ST_DELETED) || ($st==ST_DISABLED)) {
		echo '<input type="checkbox" name="'.$currseat['id'].'" title="'.$text.'" ';
		echo ( ($st==ST_DISABLED) ? ' checked="checked">' : '>' );
	}
	echo "</td>";
}

/*
 *  Callback function for render_seatmap()
 *  Prints a summary table of the show instead of the normal legend table
 */
function disableseats_key() {
	global $zone;
	$counts = disableseats_summarize($zone);  // calculates all totals at once for this zone
	echo '<p class="main"><table class="seatmap" cellpadding="5">';
	echo '<th colspan="2" style="text-align: center; font-weight:bold;">Summary</th>';
	for ($cls = 0; $cls<5; $cls++) {
		if ($cls) {
			if ($counts['total'][$cls]) {
				echo "<tr><td>Total class $cls seats = ". $counts['total'][$cls] . "    <td>";
				echo "Disabled class $cls seats = ". $counts['disabled'][$cls] . "</tr>";
			}
		} else {
			echo "<tr><td>Total seats = " . $counts['total'][$cls] . "    <td>";
			echo "Disabled seats = " . $counts['disabled'][$cls] . "</tr>";
		}
	}
	echo "</table></p>";
}

// Empty functions for the other callbacks
function disableseats_unkey() {}
function disableseats_unseat($a, $b, $c) {}

/* 
 *  Prints a page for the administrator to mark seats for a show as disabled
 *  
 */
function freeseat_disableseats() {
	global $lang, $zone, $sh;

	kill_booking_done();
	unlock_seats(false);
	if (isset($_POST["showid"])) {
		// if a showid has been POSTed to us, use it
		$_SESSION["showid"] = (int)($_POST["showid"]);
	} elseif (isset($_GET["showid"])) {
		// if a showid has been passed to us in GET, use it
		$_SESSION["showid"] = (int)($_GET["showid"]);
	} elseif (isset($_SESSION['showid'])) {
		// do nothing
	} else {
		// default to the last show in the database
		$sql = "SELECT id from shows order by id desc limit 1";
		$_SESSION['showid'] = m_eval($sql);
	}
	$sh = get_show((int)($_SESSION["showid"]));
	
	check_session(1);
	
	if (isset($_POST["load_seats"])) {
		// We are re-entering with a seat selection here.
		//  This code follows an abbreviated booking process 
		//  with no consideration of prices, payments, etc
		//  to create "bookings" for seats we want to disable.
	
		if (!load_seats($_POST,FALSE)) {
			kaboom("Failed to find seats, please try again");
		} else {
	    	// first mark as deleted all current disabled seat records for this show
			$bs = get_bookings("booking.showid=".$_SESSION["showid"]." and booking.state=".ST_DISABLED);
			foreach ($bs as $n => $b) 
				set_book_status($b,ST_DELETED);    
			
			// if any seats were selected, create session variables
			if (isset($_SESSION["seats"])) {
				$_SESSION["payment"]=PAY_OTHER;
				$_SESSION["firstname"] = "Disabled";
				$_SESSION["lastname"] = "Seat";
				foreach (array("phone","email","address","postalcode","city","us_state","country") as $a) {
					$_SESSION[$a] = "";
				}
				// make the bookings by calling book()
				foreach ($_SESSION["seats"] as $n => $s) {
					// (tendays) force selected seats to be in state disabled at booking time.
					$s['state'] = ST_DISABLED;
					if (($bookid = book($_SESSION,$s))!==false) {
						$_SESSION["seats"][$n]["bookid"] = $bookid;
						if (!(isset($_SESSION["groupid"]) && $_SESSION["groupid"]!=0))
							$_SESSION["groupid"] = $bookid;
					}
				}
				// now we set_book_status() to disabled
				$_SESSION["booking_done"] = ST_DISABLED;
			}  // if no seats are selected, just report success
			kaboom($lang['show_stored']);
		}
	}
	
	show_head(true);
	echo '<h2>'.$lang["disableseats"].'</h2><p class="main">';

	echo '<form action="'.admin_url('admin.php?page=freeseat-disableseats&showid='.$_SESSION["showid"]).'" name="showform" method="post">';
	if (function_exists('wp_nonce_field')) wp_nonce_field('freeseat-disableseats-showform');
	// limit this list to future shows 
	$ss = get_shows( "date >= CURDATE()" );
	if ( $ss ) {
		echo '<p class="main"><select name="showid" onchange="showform.submit();">';
		foreach ( $ss as $s ) {
			echo '<option value="' . $s[ "id" ] . '"';
			echo ( ( $_SESSION['showid'] == $s[ "id" ] ) ? 'selected >' : '>' );
			show_show_info( $s, false );
			echo '</option>';
		}
		echo '</select> ';
		submit_button($lang['select'],'primary','select',false);
		echo "</p>";
	} else echo mysql_error();  // should not happen
	echo "</form>";
	
	/* Now display the seatmaps */

	echo '<form action="'.admin_url('admin.php?page=freeseat-disableseats&showid='.$_SESSION["showid"]).'" name="changeform" method="post">';
	if (function_exists('wp_nonce_field')) wp_nonce_field('freeseat-disableseats-load-seats');
	echo '<input type="hidden" name="load_seats">';
	echo '<p class="main">'.$lang['nowdisplaying'];
	show_show_info($sh,false);	
	$zonelist = get_zones($sh['theatre']);
	if ($zonelist) {
		foreach ($zonelist as $zone) {
		    render_seatmap($sh['theatre'], $zone,
			   'disableseats_key', 'disableseats_seat',
			   'disableseats_unkey', 'disableseats_unseat');
		}
	} else {
		// either the theatre has no seats or there was an error obtaining them.
		kaboom($lang['err_noseats']);
		$currseat = false;
	}
	submit_button($lang['save']);
	// echo '<input type="submit" value="'.$lang["save"].'">';
	echo '<p></form>';
	echo '<p class="main">';
	printf($lang["backto"],'[<a href="'.admin_url().'?page=freeseat-admin&fsp='.PAGE_SEATS.'&showid='.$_SESSION['showid'].'">'.$lang["link_seats"].'</a>]');
	echo '</p>';
	show_foot(); 
}

