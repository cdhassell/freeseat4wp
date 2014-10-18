<?php namespace freeseat;

/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.

$Id: index.php 341 2011-04-25 19:03:48Z tendays $

Modifications for Wordpress are Copyright (C) 2013 twowheeler.
*/

/*
 * Displays the seating chart during the sale transaction
 * Replaces the former seats.php
 */
function freeseat_seats( $page_url )
{
	global $lang, $sh, $zone;
	
	/* seat selection housekeeping */
	kill_booking_done();
	if ( isset( $_GET[ "showid" ] ) && ( !isset( $_SESSION[ "showid" ] ) || 
		( $_SESSION[ "showid" ] != (int)( $_GET[ "showid"] ) ) ) ) {
		// $prevSelected = ( isset( $_SESSION[ "seats" ] ) ? $_SESSION["seats"] : array() );
		
		// We must unlock the seats before changing the show id otherwise the
		// things will get confused.
		// unlock_seats();
		$_SESSION["showid"] = (int)($_GET["showid"]);
		
		check_session(1); // check showid
		// note that if check_session fails then any previous seat selection is lost.
		
		$sh = get_show($_SESSION["showid"]);
		/* The following call makes sure all seats are in the theatre
			corresponding to the current show, but not whether they're
			available. check_seats() below does that work. */
		// load_seats($prevSelected);
		if (!check_seats())
			kaboom($lang["err_occupied"]);
		/* load_seats lost any existing category selection so we need to
			put it back. (Maybe that should be done by load_seats itself?) */
		// compute_cats();
	} else { // showid unchanged
		check_session(1); // check showid
		$sh = get_show($_SESSION["showid"]);
		if (!check_seats())
			kaboom($lang["err_occupied"]);
	}
	
	/* Decide whether to show the prices or not (i.e. whether they
		depend on category or not) */
	
	$show_price = price_depends_on_cat($sh["spectacleid"]);
	
	show_head(true);
	echo '<h2>'.ucwords($lang["link_seats"]).'</h2>';
	echo '<p class="main">';
	show_show_info($sh);
	$imgsrc = plugins_url( 'i2020.png' , __FILE__ );
	echo "<img class='infolink' src='$imgsrc' title='".$lang["intro_seats"]."'>";
	echo '</p>';
	do_hook("seatmap_top");
	
	echo '<form action="' . replace_fsp( $page_url, PAGE_PAY ) . '" method="post">';
	if (function_exists('wp_nonce_field')) wp_nonce_field('freeseat-seats-render-seatmap');
	echo '<input type="hidden" name="load_seats">';
	
	if ($sh["imagesrc"]!="") {
		echo '<img src="'.$sh["imagesrc"].'">';
	}
	
	/* Now display the seatmaps */
	
	$zonelist = get_zones($sh['theatre']);
	if ($zonelist) {
		foreach ($zonelist as $zone) {
			render_seatmap($sh['theatre'], $zone,
				'keycallback', 'seatcallback',
				'unkeycallback', 'unseatcallback');
		}
	} else {
		// either the theatre has no seats or there was an error obtaining them.
		kaboom($lang['err_noseats']);
		$currseat = false;
	}
	if ( !do_hook_exists("seatmap_hide_button") ) { 
		echo '<p><input class="button button-primary" type="submit" value="'.$lang["addtocart"].'"></p>';
	}
	echo '</form>';
	show_foot();
}	// end of freeseat_seats

