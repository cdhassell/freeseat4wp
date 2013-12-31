<?php

/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.

$Id: index.php 341 2011-04-25 19:03:48Z tendays $

Modifications for Wordpress are Copyright (C) 2013 twowheeler.
*/

/*
 * This function displays a page to make a show choice
 * Replaces the former repr.php
 */
function freeseat_repr( $page_url ) 
{
	global $lang, $spectacleid;
		
	db_connect();
	
	/* uncomment following lines to display debugging information  
	echo '<pre>POST: '; print_r($_POST); echo '</pre>';
	echo '<pre>GET: '; print_r($_GET); echo '</pre>'; 
	echo '<pre>SESSION: '; print_r($_SESSION); echo '</pre>';  */
	
	/* Set the $spec and $spectacleid variables either according to
	GET, POST or SESSION: */
	
	/* NOTE THAT if user comes here by post without having a session, and
	then reloads it with GET (i.e. presses enter in his browser address
	bar), then the site will complain about missing cookies. Oh well,
	user can go back to home page and click on the spectacle - that's
	not the end of the world */
	$spec = null;
	
	if ( isset( $_REQUEST[ "spectacleid" ] ) ) {
		$spectacleid = (int) ( $_REQUEST[ "spectacleid" ] );
		$spec        = get_spectacle( $spectacleid );
	} else if ( isset( $_SESSION[ "showid" ] ) ) {
		if ( $sh = get_show( $_SESSION[ "showid" ] ) ) {
			$spectacleid = $sh[ "spectacleid" ];
			$spec        = get_spectacle( $spectacleid );
		}
	} else
		fatal_error( $lang[ "err_session" ] ); // unspecified spectacle
	
	if ( !$spec )
		fatal_error( "Can't load the spectacle" ); // e.g. trying to get a nonexistent spectacle
	
	/* Process POST requests */
	// we detect a request for changing disabled status with the
	//  "reset_disabled" POST variable.
	// Just checking HTTP_REQUEST_METHOD would have been risky in case
	//  the user drops on that page in POST mode - that would remove the
	//  disabled flag everywhere.
	if ( admin_mode() && isset( $_POST[ "reset-disabled" ] ) ) {
		$ok = true;
		$ss = get_shows( "spectacle=$spectacleid" );
		if ( $ss )
			foreach ( $ss as $sh )
				$ok &= freeseat_query( "update shows set disabled=" . ( isset( $_POST[ "disable-" . $sh[ "id" ] ] ) ? 1 : 0 ) . " where id=" . $sh[ "id" ] );
		
		if ( $ok )
			kaboom( $lang[ "show_stored" ] );
		else
			myboom( $lang[ "err_connect" ] ); // couldn't update some show -
		// probably access right problem
	}

	/* note that we get_shows twice in case there was a POST. The goal is
	to have it immediately visible to the user in case there was a
	problem altering the "disabled" settings. */
	
	//$c = get_config();
	$ss = get_shows( "spectacle=$spectacleid" );
	// and date_sub(concat(date,time),interval ".$c["closing"]." minute) > now();");
	
	if ( $ss === false )
		fatal_error( $lang[ "err_connect" ] . mysql_error() );
	else if ( !count( $ss ) )
		fatal_error( $lang[ "err_spectacleid" ] ); // spectacle exists but it
	// has no shows
	do_hook_function( 'repr_process', $ss );
	
	show_head();
	
	echo '<h2>';
	printf( $lang[ "showlist" ], htmlspecialchars( $spec[ "name" ] ) );
	echo '</h2>';
	
	if ( admin_mode() ) {
		echo '<form action="' . $page_url . '&fsp=' . PAGE_REPR . '" method="post">';
		echo '<input type="hidden" name="reset-disabled" value="on">';
		echo '<input type="hidden" name="spectacleid" value="' . $spectacleid . '">';
	}
	echo '<ul>';
	foreach ( $ss as $sh ) {
		$remaining = show_closing_in( $sh );
		
		/* total seats */
		$tot = m_eval( "select count(*) from seats where theatre=" . $sh[ "theatre" ] );
		/* how many have been booked so far */
		$bk  = m_eval( "select count(*) from booking where showid=" . $sh[ "id" ] . " and state!=" . ST_DELETED );
		/* how many are disabled (included in above counts) */
		$ds  = m_eval( "select count(*) from booking where showid=" . $sh[ "id" ] . " and state=" . ST_DISABLED );
		
		// in progress   $bkds = m_eval($q="select *,1 as cnt,$cat as cat,seats.id as id from seats left join booking on booking.seat=seats.id and booking.showid=$showid and booking.state!=".ST_DELETED.
		//	   " where booking.id is null"));
		
		if ( ( $tot === null ) || ( $bk === null ) || ( $ds === null ) ) {
			$tot = "??";
			$bk  = "??";
		} else {
			/* note that we assume there aren't two non-deleted bookings for
			the same seat, same show, same spectacle, otherwise things get
			funky */
			$tot -= $ds;
			$bk -= $ds;
			// disable the book button if there are no free seats left
			if ( $bk >= $tot ) // (Heh - what would it mean if it were strictly bigger?) :-)
				$remaining = 0;
		}
		
		if ( $remaining <= 0 )
			echo "<li><p class='disabled'>";
		else
			echo "<li><p>";
		
		if ( admin_mode() )
			echo "($bk/$tot) [<a href='admin.php?page=freeseat-showlist&showid=" . $sh[ "id" ] . "'>" . $lang[ "link_bookinglist" ] . "</a>] ";
		
		if ( $remaining > 0 || admin_mode() )
			echo "[<a href='$page_url&fsp=" . PAGE_SEATS . "&showid=" . $sh[ "id" ] . "'>" . $lang[ "book" ] . "</a>]";
		else
			echo "[" . $lang[ "closed" ] . "]";
		
		if ( admin_mode() )
			echo ' [<input type="checkbox" name="disable-' . $sh[ "id" ] . ( $sh[ "disabled" ] ? '" checked>' : '">' ) . $lang[ "disabled" ] . ']';
		
		echo ' : ';
		
		show_show_info( $sh, false );
		
		if ( $remaining <= 0 && admin_mode() )
			echo " (" . $lang[ "book_adminonly" ] . ")";
		
		echo "</p>\n";
	}
	
	echo '</ul>';
	
	do_hook_function( 'repr_display', $page_url );
	if ( admin_mode() )
		echo '<p><input class="button button-primary" type="submit" value="Save changes"></p></form>';
	
	echo '<p class="main">';
	printf( $lang[ "backto" ], '[<a href="' . $page_url . '">' . $lang[ "link_index" ] . '</a>]' );
	echo '</p>';
	
	show_foot();
}	// end of freeseat_repr

