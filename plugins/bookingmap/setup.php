<?php namespace freeseat;

  /** bookingmap/setup.php
   *
   * Copyright (c) 2010 by Maxime Gamboni
   * Licensed under the GNU GPL 2. For full terms see the file COPYING.
   *
   * Displays seatmap with reservation status
   *
   * $Id$
   *
   * Rewritten for Wordpress by twowheeler
   * 
   */

function freeseat_plugin_init_bookingmap() {
    global $freeseat_plugin_hooks;

    $freeseat_plugin_hooks['seatmap_top']['bookingmap'] = 'bookingmap_linkfromseats';
    $freeseat_plugin_hooks['bookinglist_pagebottom']['bookingmap'] = 'bookingmap_linkfromlist';
    add_action( 'admin_menu', __NAMESPACE__ . '\\freeseat_bookingmap_menu' );
}

function freeseat_bookingmap_menu() {
	// add a new admin menu page for viewing reservations on a map
	// this must be run *after* the function freeseat_admin_menu()
	add_submenu_page( 'freeseat-admin', 'Reservation Map', 'Reservation Map', 'administer_freeseat', 'freeseat-bookingmap', __NAMESPACE__ . '\\freeseat_bookingmap' );
}

function bookingmap_linkfromseats() {
	global $lang, $sh;
	
	if (admin_mode()) {
		echo '<p class="main">';
		printf($lang["seeasamap"],'[<a href="'. admin_url('admin.php?page=freeseat-bookingmap&showid='.$sh['id']).'">','</a>]');
		echo '</p>';
	}
}

function bookingmap_linkfromlist() {
	global $filtershow, $lang;
	if ($filtershow) {
		echo '<ul><li><p class="main">';
		printf($lang["seeasamap"],"[<a href='". admin_url('admin.php?page=freeseat-bookingmap&showid='.$filtershow) . "'>",'</a>]');
		echo '</p></ul>';
	}
}

function print_end_zone() {
	global $table, $lang, $maxlen;
	if ($table) echo "</table>";
}

/*
 *  Callback function for render_seatmap()
 *  Output one seat for the booking map
 */
function bookingmap_seat($currseat) {
	global $sh, $lang;
	
	$st = get_seat_state($currseat['id'],$sh['id']);
	$colour = state2css($st);
	// Build a title tag with the details - replaces javascript bubbles etc.
	$text = "{$lang["row"]}: {$currseat['row']} {$lang["col"]}: {$currseat['col']} ( {$currseat["zone"]} ";
	$text .= "{$currseat["extra"]} ) {$lang["class"]}: {$currseat["class"]}";
	if ($st==ST_LOCKED)	{
		$text .= "{$lang["state"]}: {f_state($st)}";
	} else {
		if (($st!=ST_FREE) && ($st!=ST_DELETED)) {
			$bk = get_bookings("seat=".$currseat["id"]." and state!=".ST_DELETED." and showid=".$sh['id']);
			if ($bk[0]) {
				$text .= "\n{$lang["bookid"]}: {$bk[0]["bookid"]} {$lang["cat"]}: ".f_cat($bk[0]["cat"]);
				$text .= "\n{$lang["state"]}: ".f_state($st)." {$lang["payment"]}: ".f_payment($bk[0]["payment"]);
				$text .= "\n{$lang["email"]}: {$bk[0]["email"]} {$lang["phone"]}: {$bk[0]["phone"]}";
				$text .= "\n{$lang["name"]}: {$bk[0]["firstname"]} {$bk[0]["lastname"]}";
				$text .= "\n{$lang["timestamp"]}: {$bk[0]["timestamp"]}";	
			}
		}
	}
	echo "<td colspan='2' class='$colour' title='$text'>";
	if (strlen($currseat['col']) == 1) echo "&nbsp;";
	print $currseat['col'];
	echo "</td>";
}

/*
 *  Callback function for render_seatmap()
 *  Prints a summary table of the show instead of the normal legend table
 */
function bookingmap_key() {
	global $lang;
	echo '<p class="main"><table class="seatmap" cellpadding="5"><tr><td><p class="main">';
	echo $lang["legend"];
	echo '</p>';
	for ($i=0;$i<7;$i++) {
		echo "<td class='".state2css($i)."'><p>".f_state($i)."</p>";
	}
	echo "</table></p>";
}

// Empty functions for the other callbacks
function bookingmap_unkey() {}
function bookingmap_unseat($a, $b, $c) {}

function freeseat_bookingmap() {
	global $lang, $sh;
	
	if (!admin_mode()) fatal_error($lang["access_denied"]);

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
	
	// construct the page
	show_head(true);
	echo '<h2>'.$lang["bookingmap"].'</h2><p class="main">';
	
	// display a select box with shows to choose from
	echo '<form action="'.admin_url('admin.php?page=freeseat-bookingmap&showid='.$_SESSION["showid"]).'" name="showform" method="post">';
	if (function_exists('wp_nonce_field')) wp_nonce_field('freeseat-bookingmap-showform');
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
	if ($sh["imagesrc"]!="") {
		echo '<img src="'.$sh["imagesrc"].'">';
	} 
	
	/* Now display the seatmaps */
	
	echo '<p class="main">Now displaying: ';
	show_show_info($sh,false);	
	echo '</p><p class="main">';
	printf($lang["seeasalist"],'[<a href="'.admin_url('admin.php?page=freeseat-reservations&showid='.$sh['id']).'">','</a>]');
	echo '</p>';
	if (isset($sh['theatre'])) {
		$zonelist = get_zones($sh['theatre']);
		if (!empty($zonelist)) {
			foreach ($zonelist as $zone) {
		    	render_seatmap($sh['theatre'], $zone,
			   		'bookingmap_key', 'bookingmap_seat',
			   		'bookingmap_unkey', 'bookingmap_unseat');
			}
		} else {
			// either the theatre has no seats or there was an error obtaining them.
			kaboom($lang['err_noseats']);
			$currseat = false;
		}
	}
	echo '<p class="main">';
	printf($lang["backto"],'[<a href="'.admin_url().'?page=freeseat-admin&fsp='.PAGE_SEATS.'&showid='.$_SESSION['showid'].'">'.$lang["link_seats"].'</a>]');
	echo '</p>';
	show_foot();
}

