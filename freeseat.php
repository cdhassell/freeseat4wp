<?php
/*
Plugin Name: FreeSeat
Plugin URI: http://lostinspace.com
Description: FreeSeat for Wordpress implements a theatre ticketing system with optional links to CiviCRM.
Version: 0.1
Author: twowheeler
Author URI: 
*/
/*  This is a modified version of the stand-alone ticketing application
FreeSeat written by Maxime Gamboni. See http://freeseat.sourceforge.net

The modifications for WordPress are Copyright 2013 by twowheeler 
(email : webmaster@hbg-cpac.org).

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

global $freeseat_db_version;
$freeseat_db_version = "0.1";

// ------------------------------------------------------------------------
// REQUIRE MINIMUM VERSION OF WORDPRESS:                                               
// ------------------------------------------------------------------------
// THIS IS USEFUL IF YOU REQUIRE A MINIMUM VERSION OF WORDPRESS TO RUN YOUR
// PLUGIN. IN THIS PLUGIN THE WP_EDITOR() FUNCTION REQUIRES WORDPRESS 3.3 
// OR ABOVE. ANYTHING LESS SHOWS A WARNING AND THE PLUGIN IS DEACTIVATED.                    
// ------------------------------------------------------------------------

function freeseat_wordpress_version() {
	global $wp_version;
	$plugin = plugin_basename( __FILE__ );
	$plugin_data = get_plugin_data( __FILE__, false );

	if ( version_compare($wp_version, "3.3", "<" ) ) {
		if( is_plugin_active($plugin) ) {
			deactivate_plugins( $plugin );
			wp_die( "'".$plugin_data['Name']."' requires WordPress 3.3 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
		}
	}
}
add_action( 'admin_init', 'freeseat_wordpress_version' );

// load everything up
$FS_PATH = plugin_dir_path( __FILE__ );

require_once( $FS_PATH . "vars.php" );

require_once( $FS_PATH . "functions/booking.php" );
require_once( $FS_PATH . "functions/callbacks.php" );
require_once( $FS_PATH . "functions/format.php" );
require_once( $FS_PATH . "functions/money.php" );
require_once( $FS_PATH . "functions/mysql.php" );
require_once( $FS_PATH . "functions/plugins.php" );
require_once( $FS_PATH . "functions/seat.php" );
require_once( $FS_PATH . "functions/seatmap.php" );
require_once( $FS_PATH . "functions/send.php" );
require_once( $FS_PATH . "functions/session.php" );
require_once( $FS_PATH . "functions/showedit.php" );
require_once( $FS_PATH . "functions/shows.php" );
require_once( $FS_PATH . "functions/spectacle.php" );
require_once( $FS_PATH . "functions/tools.php" );
require_once( $FS_PATH . "seatmaps.php" );
require_once( $FS_PATH . "options.php" );
require_once( $FS_PATH . "install.php" );

$freeseat_vars = get_config();
foreach ( $freeseat_vars as $var => $value ) {
	$$var = $value;
}

// DEFINEs for workflow control
define( 'PAGE_INDEX',	0 );
define( 'PAGE_REPR',	1 );
define( 'PAGE_SEATS',	2 );
define( 'PAGE_PAY', 	3 );
define( 'PAGE_CONFIRM',	4 );
define( 'PAGE_FINISH',	5 );


/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.

$Id: index.php 341 2011-04-25 19:03:48Z tendays $
*/

db_connect();

/*
 * Switching station for entry to ticket workflow
 * Depending on GET vars to select next step
 * Using the defines PAGE_*
 */
function freeseat_switch() {
	// Where are we?  Assemble a URL from the GET fields
	// Seems awful clumsy, isn't there a better WP way?
	global $post, $page_url;
	$fsp = (( isset( $_GET[ 'fsp' ] ) ) ? $_GET[ 'fsp' ] : 0 );  
	$page_url = (( isset( $post ) ) ? get_page_link() : $_SERVER['PHP_SELF'].'?page=freeseat-admin' );
	$page_url = str_replace('page_id','p',$page_url);
	$args = '';
	$and = '';
	foreach( array( 'fsp', 'showid', 'spectacleid', 'offset', 'st', 'sort', 'ok' ) as $key => $value ) {
		if ( isset( $_GET[ $key ] ) ) {
		    $args .= $and . $key . '=' . $value;
		    if ( empty( $and ) ) $and = '&';
		}
	}
	if ( !empty( $args ) ) {
		$page_url .= (( false === strpos( $page_url, '?')) ? '?' : '&' ) . $args;
	}
	switch( $fsp ) {
		case 5:
			freeseat_finish( $page_url );
			break;
		case 4:
			freeseat_confirm( $page_url );
			break;
		case 3:
			freeseat_pay( $page_url );
			break;
		case 2:
			freeseat_seats( $page_url );
			break;
		case 1:
			freeseat_repr( $page_url );
			break;
		default:
			freeseat_frontpage( $page_url );
			break;
			
	}
}

function freeseat_frontpage( $page_url ) {
	global $lang, $upload_path;
			
	/** With no parameters, we show spectacles that have a representation
	today or later. With a spectacleid parameter, show that spectacle
	only 
	**/
	
	if ( isset( $_GET[ "spectacleid" ] ) && ( $s = get_spectacle( (int) ( $_GET[ "spectacleid" ] ) ) ) ) {
		$ss = array( $s );
	} else {
		$ss = fetch_all( "select spectacles.* from shows, spectacles where date >= curdate() and spectacles.id = shows.spectacle group by spectacles.id order by date asc" );
	}

	if ( $ss === false )
		fatal_error( $lang[ "err_connect" ] . mysql_error() );
	else if ( !count( $ss ) )
		kaboom( $lang[ "err_noavailspec" ] );

	/* displays an image and text description of spectacles on opening page */
	
	show_head();
	$out = '</div><div id="front-container"><h2>'. $lang[ "index_head" ] . '</h2>';
	
	// output a table showing all currently available shows with dates and times
	// with links to the show pages
	$out .= '<table>';
	foreach ( $ss as $s ) {
		$url = $page_url . '&fsp=' . PAGE_REPR . '&spectacleid=' . $s[ "id" ];
		$linkl = "<a href='$url'>";
		$linkr = '</a>';
				  
		$out .= '<tr>';
		if ( $s[ 'imagesrc' ] ) {
			$img = freeseat_url( $upload_path . $s[ 'imagesrc' ] );
			$out .= '<td class="showlist">' . $linkl . '<img src="' . $img . '">' . $linkr . '</td>';
		} else {
			$out .= '<td class="showlist"></td>';
		}
		$out .= '<td class="showlist">' . $linkl . '<h3>' . $s[ 'name' ] . '</h3>' . $linkr;
		/** WARN - we assume whoever filled the description field to be
		  trustworthy enough not to write malicious or malformed html */
		if ( $s[ "description" ] ) {
			$out .= '<p><i>' . $s[ 'description' ] . '</i></p>';
		}
		if ($s) {
			$out .= '<p>'.$lang['datesandtimes'].'</p><ul>';
			$shows = fetch_all( "select * from shows where date >= curdate() and spectacle='".$s['id']."' order by date" );
			foreach ($shows as $show) {
				$showid = $show['id'];
				$d = f_date($show['date']);
				$t = f_time($show['time']);
				$target = $page_url . '&fsp=' . PAGE_SEATS . '&showid=' . $showid;
				$out .= "<li><p><a style='color: #303030;' href='$target'>$d, $t</a></p></li>";
			}
			$out .= '</ul>';
		}
		$out .= '</td></tr>';
	}
	$out .= '</table>';
	print $out;
	do_hook( 'front_page_end' );
	show_foot();
}	// end of freeseat_frontpage

function freeseat_repr( $page_url ) 
{
	global $lang;
		
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
		fatal_error(); // e.g. trying to get a nonexistent spectacle
	
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
	do_hook( 'repr_process' );
	
	show_head();
	
	echo '<h2>';
	printf( $lang[ "showlist" ], htmlspecialchars( $spec[ "name" ] ) );
	echo '</h2>';
	
	if ( admin_mode() ) {
		echo '<form action="' . $page_url . '&fsp=' . PAGE_SEATS . '" method="post">';
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
	
	do_hook( 'repr_display' );
	if ( admin_mode() )
		echo '<p><input type="submit" value="Save changes"></p></form>';
	
	echo '<p class="main">';
	printf( $lang[ "backto" ], '[<a href="' . $page_url . '">' . $lang[ "link_index" ] . '</a>]' );
	echo '</p>';
	
	show_foot();
}	// end of freeseat_repr

function freeseat_seats( $page_url )
{
	global $lang, $sh;
	db_connect();
	
	/* seat selection housekeeping */
	kill_booking_done();
	if ( isset( $_GET[ "showid" ] ) && ( !isset( $_SESSION[ "showid" ] ) || 
		( $_SESSION[ "showid" ] != (int)( $_GET[ "showid"] ) ) ) ) {
		$prevSelected = ( isset( $_SESSION[ "seats" ] ) ? $_SESSION["seats"] : array() );
		
		// We must unlock the seats before changing the show id otherwise the
		// things will get confused.
		unlock_seats();
		$_SESSION["showid"] = (int)($_GET["showid"]);
		
		check_session(1); // check showid
		// note that if check_session fails then any previous seat selection is lost.
		
		$sh = get_show($_SESSION["showid"]);
		/* The following call makes sure all seats are in the theatre
			corresponding to the current show, but not whether they're
			available. check_seats() below does that work. */
		load_seats($prevSelected);
		if (!check_seats())
			kaboom($lang["err_occupied"]);
			/* load_seats lost any existing category selection so we need to
			put it back. (Maybe that should be done by load_seats itself?) */
		compute_cats();
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
	echo '<h2>'.$lang["err_checkseats"].'</h2>'; // not an error - lang item is a bit misnamed
	echo '<p class="main">';
	show_show_info($sh);
	echo '</p><p class="main">'.$lang["intro_seats"].'</p>';
	
	do_hook("seatmap_top");
	
	echo '<form action="' . $page_url . '&fsp=' . PAGE_PAY . '" method="post">';
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
	echo '<input type="submit" value="'.$lang["continue"].'">';
	echo '</form>';
	show_foot();
}	// end of freeseat_seats

function freeseat_pay( $page_url )
{
	global $lang, $sh, $pref_country_code, $pref_state_code, $lowpriceconditions;
	load_alerts();
	
	// MIGHT be enough to put it next to the load_seats below but I won't take chances.
	if (!do_hook_exists('pay_page_top'))
		kill_booking_done();
	if ( isset( $_GET[ 'showid' ] ) && !isset( $_SESSION[ 'showid' ] ) ) {
		$_SESSION[ 'showid' ] = $_GET[ 'showid' ];
	}
	// print '<pre>Get = '.print_r($_GET,1).'</pre>';
	// print '<pre>Post = '.print_r($_POST,1).'</pre>';
	check_session(1); // just to avoid warnings on missing show id
	
	$sh = get_show($_SESSION["showid"]); // needed by load_seats
	
	/** if no set of seats is provided then just keep the one in session **/
	if (isset($_POST["load_seats"])) {
		/* (if the following fails it will be handled by check_session) */
		unlock_seats();
		load_seats($_POST);
		check_session(3);
		compute_cats();
	} else check_session(3);
	
	$seatcount = count($_SESSION["seats"]);
	
	show_head();
	
	echo '<h2>'.$lang["summary"].'</h2>';
	echo "<p>";
	show_show_info($sh);
	echo "</p>";
	echo print_booked_seats();
	echo '<h2>'.$lang["payment"].'</h2>';
	echo '<form action="' . $page_url . '&fsp=' . PAGE_CONFIRM . '" method="post">';
	
	if (!isset($_SESSION["payment"])) $_SESSION["payment"]= PAY_CCARD;
	
	/* If the price doesn't depend on the category, then don't offer
	 discout option. */
	$discount_option = price_depends_on_cat($sh["spectacleid"]);
	
	/* All categories from which the user may choose, mapped to their $lang key. */
	$cats = array();
	
	$cats[CAT_NORMAL] = "cat_normal";
	
	if ($discount_option) {
		$cats[CAT_REDUCED] = "cat_reduced";
		// only display lowpriceconditions if there are reduced prices available
		echo '<p class="main">'.$lowpriceconditions.'</p>';
	}
	
	if (admin_mode())
		$cats[CAT_FREE] = "cat_free";
	
	if (count($cats) > 1) {
		/* If neither of those two hold, the only option is normal mode... */
	
		if ($seatcount==1) {
			/* see which one to select by defaut */
			$def=CAT_NORMAL;
			foreach ($cats as $cat => $label) {
				if (isset($_SESSION["ncat$cat"]) && $_SESSION["ncat$cat"] > 0) {
					$def = $cat;
				}
			}
			echo "<p class='main'>".$lang["cat"]."&nbsp;: ";
			echo "<select name='cat'>";
			foreach ($cats as $cat => $label) {
				echo "<option value=".$cat;
				if ($def == $cat) echo " selected='true'";
				echo ">".$lang[$label]."</option>";
			}
			echo '</select>';
		} else { // more than one seat selected
			/* We make sure the default values for discounted seats don't
			total to a larger number than the numer of selected seats. */
			$total = 0; // how many seats were previously set to a discount
			foreach ($cats as $cat => $label) {
				if (isset($_SESSION["ncat$cat"]))
					$total += $_SESSION["ncat$cat"];
			}
	    	if ($total > $seatcount) {
				$skip = $total - $seatcount;
				foreach ($cats as $cat => $label) {
					if (isset($_SESSION["ncat$cat"])) {
						if ($_SESSION["ncat$cat"] > $skip) {
							$_SESSION["ncat$cat"] -= $skip;
							break;
						} else {
							$skip -= $_SESSION["ncat$cat"];
							$_SESSION["ncat$cat"] = 0;
						}
					}
				}
			}
		    echo "<p class='main'>".sprintf($lang["howmanyare"],$seatcount). ":</p>\n<ul>";
			foreach ($cats as $cat => $label) {
				if ($cat == CAT_NORMAL) continue;
				echo "<li><p> ".$lang[$label]."&nbsp;:&nbsp;";
				input_field("ncat$cat", '0', ' size="2"');
				echo "</p>";
			}
			echo '</ul>';
		}
	}
	
	echo'<p class="main">'.$lang["select_payment"] . '<br />';
	pay_option(PAY_CCARD);
	pay_option(PAY_POSTAL);
	pay_option(PAY_CASH);
	pay_option(PAY_OTHER);
	echo '</p>';
	
	do_hook('other_payment_info');
	
	if (payment_open($sh,PAY_CCARD)) {
	  do_hook('ccard_partner');
	}
	
	echo '<h2>'.$lang["youare"].'</h2>';
	echo '<p class="main">'.$lang["reqd_info"].'</p>';
	echo '<p class="main">';
	input_field("firstname");
	echo ' ';
	input_field("lastname");
	echo '</p><p class="main">';
	input_field("phone");
	echo ' ';
	input_field("email");
	echo '</p><p class="main">';
	input_field("address",""," size=60");
	echo '</p><p class="main">';
	input_field("postalcode",""," size=8");
	echo ' ';
	input_field("city",""," size=20");
	// we will skip the us_state and/or country fields if the defaults are not set in config.php
	if ($pref_state_code != "")  {
		echo '</p><p class="main">';
		echo $lang["us_state"].'&nbsp;:&nbsp;';
		select_state();
	}
	if ($pref_country_code != "")  {
		echo '</p><p class="main">';
		echo $lang["country"].'&nbsp;:&nbsp;';
		select_country();
	}
	echo '</p><p class="main"><input type="submit" value="'.$lang["continue"].'"></p></form>';
	
	show_foot();
}	// end of freeseat_pay

function freeseat_confirm( $page_url )
{
	global $lang, $sh;
		
	load_alerts();
	kill_booking_done();
	
	foreach (array("firstname","lastname","phone","email","address","postalcode","city","us_state","country") as $n => $a) {
		if (isset($_POST[$a]))
		$_SESSION[$a] = make_reasonable(nogpc($_POST[$a]));
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
	
	echo '<p class="main">'.$lang[ "intro_confirm" ].'</p>';
	echo '<h2>' . $lang[ "summary" ] . '</h2>';
	echo '<p class="main">';
	show_show_info();
	echo '</p>';
	
	echo print_booked_seats(null,FMT_PRICE|FMT_CORRECTLINK);
	show_user_info();
	if (get_total() > 0) show_pay_info();
	
	echo '<p class="main">';
	$url = $page_url . '&fsp=' . PAGE_PAY;
	printf( $lang[ "change_pay" ], "[<a href='$url'>", "</a>]" );
	echo '</p>';
	echo '<form action="' . $page_url . '&fsp=' . PAGE_FINISH . '" method="post">';
		
	do_hook('confirm_bottom');
	// let's check that the user actually owes us something
	if ( $_SESSION[ "payment" ] == PAY_CCARD && get_total() > 0 ) {
		echo '<h2>'.$lang["make_payment"].'</h2>';
		echo '<p class="emph">' . $lang['paypal_lastchance'] . '</p>';
		
		do_hook( 'ccard_confirm_button' );
		
		echo '</form>';
	} else {
		echo '<input type="submit" value="'.$lang["book_submit"].'">';
	}
	echo '</form>';
	
	show_foot();
}	// end of freeseat_confirm

function freeseat_finish( $page_url )
{
	global $lang, $messages, $sh;
	prepare_log((admin_mode()?"admin":"user")." buying from ".$_SERVER["REMOTE_ADDR"]);
	
	$sh = get_show($_SESSION["showid"]);
	$spec = get_spectacle($sh["spectacleid"]);
	
	//$bookid = 0;
	
	if ((!isset($_SESSION["booking_done"])) || ($_SESSION["booking_done"]===false)) {
	
	  $_SESSION["groupid"] = 0;
	
	  do_hook('confirm_process'); // process any extra parameters from confirm.php
	
	  check_session(4,true);
	  
	  foreach ($_SESSION["seats"] as $n => $s) {
	    /* $_GET["panic"] is for debugging purposes */
	    if (($bookid = book($_SESSION,$s))===false) { // || $_GET["panic"]=="NOW") {
	      /* okay, now what?? :-( */
	      
	      $body  = " \$_SESSION = \n";
	      $body .= print_r($_SESSION,true);
	      $body .= " \$messages = \n";
	      $body .= flush_messages_text();
	      
	      send_message($smtp_sender,$admin_mail,"PANIC",$body);
	      
	      show_head(true);
	
	      echo $lang["panic"];
	      
	      show_foot();
	      log_done();
	      exit;
	    } else {
	      $_SESSION["seats"][$n]["bookid"] = $bookid;
	      if (!(isset($_SESSION["groupid"]) && $_SESSION["groupid"]!=0))
		$_SESSION["groupid"] = $bookid;
	    }
	  }
	
	  $_SESSION["booking_done"] = ST_BOOKED;
	
	} else check_session(4);
	
	if (isset($_GET["ok"])) {
	  if ($_GET["ok"]=="yes") {
	    $_SESSION["booking_done"] = ST_PAID;
	  } else {
	    kaboom(sprintf($lang["err_ccard_user"],$smtp_sender));
	  }
	}
	
	if (($_SESSION["payment"]==PAY_CCARD) && ($_SESSION["booking_done"]!=ST_PAID)
	  && (get_total()>0)) {
	
	  show_head();
	
	  echo $lang["intro_ccard"];
	  
	  do_hook('ccard_paymentform');
	
	} else { // not credit card or coming back from credit card processor
	
	  $config = get_config();
	
	  if (($_SESSION["payment"]==PAY_CCARD) && (get_total()>0)) {
	    /* if the payment is done by credit card, check if tickets have
	      already been paid (they should), and only mail the user if not. */
	
	    $bs = get_bookings("booking.groupid=".$_SESSION["groupid"]." or booking.id=".$_SESSION["groupid"]);
	    if ($bs) {
	      $allpaid = true;
	      foreach ($bs as $n => $b) {
		if ($b["state"]!=ST_PAID) $allpaid = false;
	      }
	      if ($allpaid) {
		/* get_total() is non-zero but all tickets are marked PAID
		 then a thank you/confirmation message has already been sent
		 by set_book_status/send_notifs. */
	
	        $_SESSION["mail_sent"] = true;
	  }
	
	    } else {
	      /* the correct value for allpaid is not known because things
	       went wrong. I think it's best to set it to FALSE as (I think)
	       it's best to tell people they should pay when actually they
	       don't, than not tell them when they should. */
	      $allpaid = false;
	      myboom();
	    }
	  } else { /* not paying by ccard, or all tickets free. */
	    $allpaid = (get_total() == 0);
	  }
	  // make the ticket output page
	  show_head(true);
	
	  /* Ticket-printing plugins may request to override ticket rendering
	   from other plugins by implementing the _override hooks below, and
	   returning true in ticket_prepare_override. Most ticket printing
	   routines should only implement the non-override hooks. Of course if
	   more than one plugin requests overriding ticket rendering, all such
	   plugins will be run side by side. */
	  $hide_tickets = do_hook_exists('ticket_prepare_override');
	  foreach ($_SESSION["seats"] as $n => $s) {
	    do_hook_function('ticket_render_override', array_union($_SESSION,$s));
	  }
	  do_hook('ticket_finalise_override');
	
	  if (!$hide_tickets) {
	    do_hook('ticket_prepare');
	    foreach ($_SESSION["seats"] as $n => $s) {
	      do_hook_function('ticket_render', array_union($_SESSION,$s));
	    }
	    do_hook('ticket_finalise');
	  }
	  do_hook('finish_end');
	  echo '<p class="main"><b>'.$lang["mail-thankee"].'</b></p>';
	
	
	  /* Now send a confirmation message if that hasn't been done already. */
	if (($_SESSION["email"]!="") && (!isset($_SESSION["mail_sent"]))) {
	  $body  = sprintf($lang["mail-booked"],$spec["name"]);
	  $body .= $lang["name"].": ".$_SESSION["firstname"]." ".$_SESSION["lastname"]."\n";
	  $body .= "\n";
	  // TODO - BUG - $_SESSION["seats"] don't have the correct
	  // date/time/theatrename fields
	  $body .= print_booked_seats(null,FMT_PRICE|FMT_SHOWID|FMT_SHOWINFO);
	  $body .= "\n";
	  if (!$allpaid) {
	    $body .= $lang["mail-notconfirmed"];
	
	    if ($_SESSION["payment"] != PAY_CASH) {
	      $body .= $lang["mail-secondmail"];
	    }
	
	    $body .= "\n";
	
	    /* TODO - show exactly the same stuff both in mail and in page,
	 and code it only once .. */ 
	    switch ($_SESSION["payment"]) {
	    case PAY_POSTAL:
	      $body .= sprintf($lang["payinfo_postal"],$ccp,$config["shakedelay_post"]);
	      break;
	    case PAY_CCARD:
	      $body .= sprintf($lang["payinfo_ccard"],$config["shakedelay_ccard"]);
	      break;
	    case PAY_CASH:
	      $body .= $lang["payinfo_cash"];
	      break;  
	    }
	    
	  }
	
	  $body.=$lang["mail-thankee"];
	
	  $body.= "\n";
	  $body.= "$auto_mail_signature\n";
	
	  send_message($smtp_sender,$_SESSION["email"],$lang["mail-sub-booked"],$body);
	  $_SESSION["mail_sent"] = true; // last minute kludge to avoid sending the mail every time the user clicks reload
	  //  echo $messages;
	  echo '<p class="main">'.$lang["mail-sent"].'</p>';
	} 
	 print_legal_info();
	 if (!$allpaid) {
	   echo '<p class="main">'.$lang["mail-notconfirmed"].'</p>';
	
	/* Now display some information about how to pay */
	   echo '<p class="bwemph">'; // note, we will have an empty <p></p>
				      // in case payment mode is chosen to be
	   switch ($_SESSION["payment"]) { // "other" (i.e. not covered in the
	   case PAY_POSTAL:	           // below list)
	     printf($lang["payinfo_postal"],$ccp,$config["shakedelay_post"]);
	     break;
	   case PAY_CCARD:
	     printf($lang["payinfo_ccard"],$config["shakedelay_ccard"]);
	      break;
	    case PAY_CASH:
	      echo $lang["payinfo_cash"];
	      break;  
	   }
	   echo '</p>';
	 } 
	 echo '<div class="dontprint"><p class="main">';
	 $url = $page_url . '&fsp=' . PAGE_REPR . '&spectacleid=' . $spec[ "id" ];
	 printf($lang["bookagain"],"[<a href='$url'>","</a>]");
	 echo '</p></div>';
	
	} // end of block run when not credit card or already gone through
	     // credit card processor
	
	show_foot();
	log_done();
}	// end of freeseat_finish

function freeseat_bookinglist()
{
	global $FS_PATH, $lang, $bookings_on_a_page;
	if ( !current_user_can('administer_freeseat' ) ) { 
		wp_die( __( 'You do not have sufficient permissions to access this page. 1' ) );
	}	
	echo '<h2>View Reservations</h2>';
		
	prepare_log( "booking administration" );
	
	/* READ FILTER PARAMETERS
	
	either from GET (when filter settings changed) or from POST (previous
	filter settings carried through form submission) */
	
	$params = array( );
	foreach ( array(
		 "offset",
		/*"from","to",*/
		"st",
		"showid",
		"sort" 
	) as $n => $f ) {
		if ( isset( $_GET[ $f ] ) )
			$params[ $f ] = nogpc( $_GET[ $f ] );
		else if ( isset( $_POST[ $f ] ) )
			$params[ $f ] = nogpc( $_POST[ $f ] );
		// note - the default values are null, not empty strings !
	}
	
	$c = get_config();
	
	if ( isset( $params[ "sort" ] ) && ( $params[ "sort" ] == "email" || $params[ "sort" ] == "lastname" ) )
		$orderby = $params[ "sort" ];
	else
		$orderby = "id";
	
	// "selected offset", affects displayed bookings. Don't mistake with
	// $coffset which is used in a loop for pagelist.
	if ( isset( $params[ "offset" ] ) )
		$soffset = (int) $params[ "offset" ];
	else
		$soffset = 0;
	
	/* valid values for $filterst are 0 (means show everything)
	 * -ST_DELETED (means everything except deleted)
	 * ST_BOOKED (means booked or shaken but not paid)
	 * ST_DELETED (means show only deleted)
	 * ST_DISABLED (means not available)
	 * ST_PAID (means paid) */
	if ( isset( $params[ "st" ] ) )
		$filterst = (int) ( $params[ "st" ] );
	else
		$filterst = -ST_DELETED;
	
	if ( isset( $params[ "showid" ] ) )
		$filtershow = (int) ( $params[ "showid" ] );
	else
		$filtershow = null;
	
	/** DONE WITH parsing filter parameters */
	
	/* Now see if we have a command to execute */
	
	/** First see if bookings were selected */
	$ab = array( );
	foreach ( $_POST as $key => $value ) {
		if ( is_numeric( $key ) ) {
			$ab[ ] = get_booking( (int) $key );
		}
	}
	
	$setstate = 0;
	if ( isset( $_POST[ "setstate" ] ) && ( ( $_POST[ "setstate" ] == ST_DELETED ) || ( $_POST[ "setstate" ] == ST_PAID ) ) ) {
		$setstate = (int) $_POST[ "setstate" ];
		start_notifs();
		
		foreach ( $ab as $book ) {
			set_book_status( $book, $setstate );
		}
		
		send_notifs( $setstate );
		$setstate = 0;
	} else if ( isset( $_POST[ "confirm" ] ) ) {
		$setstate = ST_PAID;
	} else if ( isset( $_POST[ "delete" ] ) ) {
		$setstate = ST_DELETED;
	}
	
	do_hook( 'bookinglist_process' );
	
	show_head( true );
	
	$bookinglist_url = admin_url( 'admin.php?page=freeseat-reservations' );
	
	if ( $setstate && ( count( $ab ) > 0 ) ) { // state 2
		$checkboxes = false; // i.e. select everything on screen
		echo '<h2>';
		printf( $lang[ "check_st_update" ], ( $setstate == ST_DELETED ) ? $lang[ "DELETE" ] : $lang[ "acknowledge" ] );
		echo '</h2>';
	} else { // state 1 or 3
		$setstate   = 0;
		$checkboxes = true;
		
	?>
	<form action="<?php echo $bookinglist_url; ?>" method="POST" name="filterform">
	<p class="main"><?php
		echo $lang[ "filter" ];
	?>
	<select name="st" onchange="filterform.submit();">
	<?php //'
		foreach ( array(
			 -ST_DELETED => "st_notdeleted",
			ST_BOOKED => "st_tobepaid",
			ST_PAID => "st_paid",
			ST_DELETED => "st_deleted",
			ST_DISABLED => "st_disabled",
			0 => "st_any" 
		) as $opt => $lab ) {
			echo '<option value="' . $opt . '" ';
			if ( $filterst == $opt )
				echo "selected ";
			echo '>' . $lang[ $lab ] . '</option>';
		}
	?>
	</select>
	<?php
		$ss = get_shows( "date >= CURDATE() - INTERVAL 1 week" );
		// and date_sub(concat(date,time),interval ".$c["closing"]." minute) > now();");
		if ( $ss ) {
			
			echo '<select name="showid" onchange="filterform.submit();">';
			echo '<option value="">' . $lang[ "show_any" ] . '</option>';
			$comma = '';
			foreach ( $ss as $sh ) {
				echo '<option value="' . $sh[ "id" ] . '"';
				if ( $filtershow == $sh[ "id" ] )
					echo 'selected >';
				else
					echo '>';
				show_show_info( $sh, false );
				echo '</option>';
				$fulllist .= $comma . $sh[ 'id' ];
				$comma = ', ';
			}
			echo '</select> ';
		} else
			echo mysql_error();
		echo '<select name="sort" onchange="filterform.submit();">';
		
		foreach ( array(
			 "id",
			"email",
			"lastname" 
		) as $h ) {
			echo "<option value='$h' ";
			if ( $orderby == $h )
				echo "selected";
			echo '>';
			printf( $lang[ "orderby" ], $lang[ $h ] );
			echo '</option>';
		}
		echo '</select> <input type="submit" value="' . $lang[ "update" ] . '"></form></p>';
		
		/** BUILD QUERY ACCORDING TO filter settings **/
		$cond = "";
		$and  = ""; // set to "and" once $cond is non empty
		if ( $filtershow )
			$cond = "showid=$filtershow and";
		else if ( $fulllist )
			$cond = "showid IN ($fulllist) and";
		else
			$cond = "showid is null";
		
		switch ( $filterst ) {
			case ST_BOOKED:
				$cond .= " $and (state=" . ST_BOOKED . " or state=" . ST_SHAKEN . ")";
				$and = "and";
				break;
			case ST_PAID:
			case ST_DELETED:
			case ST_DISABLED:
				$cond .= " $and state=$filterst";
				$and = "and";
				break;
			case 0:
				$cond .= " $and (state=" . ST_BOOKED . " or state=" . ST_SHAKEN . " or state=" . ST_PAID . " or state=" . ST_DELETED . ")";
				$and = "and";
				break;
			default: //  -ST_DELETED
				$cond .= " $and (state=" . ST_BOOKED . " or state=" . ST_SHAKEN . " or state=" . ST_PAID . ")";
				$and = "and";
		}
		
		/** Print page list **/
		$firstloop = true;
		
		// "current offset", changes while looping through pagelist
		$coffset = 0;
		
		// echo "<p>$cond</p>"; // DEBUG
		
		$prevz   = null; // value of $z in the previous loop
		$closing = ''; // what to print to close previous page link
		$subpage = 1; // if there's many links with same label we display
		// them as a/1, a/2, a/3, etc, b/1, b/2, etc, c, d, etc
		// and $subpage indicates /number.
		
		if ( $cond )
			$condAnd = "$cond and";
		else
			$condAnd = "";
		$slice = get_slice( $orderby, $cond, $coffset );
		while ( $slice !== false ) {
			list( $a, $z ) = $slice;
			if ( $orderby != "id" ) {
				$a = $a{0};
				$z = $z{0};
			}
			if ( $firstloop ) {
				echo '<p>' . $lang[ $orderby ] . '&nbsp;: ';
				$firstloop = false;
			}
			
			if ( trim( $a ) == "" )
				$a = '<i>' . $lang[ "none" ] . '</i>';
			if ( trim( $z ) == "" )
				$z = '<i>' . $lang[ "none" ] . '</i>';
			
			/** Print and calculate /subpagenumbers **/
			
			if ( $a == $prevz || $subpage > 1 )
				echo "<span class='subpage'>$subpage</span>";
			echo $closing; // close previous link
			
			if ( $a == $prevz )
				$subpage++;
			else
				$subpage = 1;
			
			if ( $soffset == $coffset ) {
				echo "(";
				$closing = ") ";
			} else {
				echo "[<a href='" . $bookinglist_url . "?offset=$coffset&amp;st=$filterst&amp;showid=$filtershow&amp;sort=$orderby'>";
				$closing = "</a>] ";
			}
			if ( $a != $z ) {
				echo ( $subpage > 1 ) ? "$a<span class='subpage'>$subpage</span>-$z" : "$a-$z";
				$subpage = 1;
			} else {
				echo "$z";
			}
			
			$prevz = $z;
			$coffset += $bookings_on_a_page;
			$slice = get_slice( $orderby, $cond, $coffset );
		}
		
		/* See if last link needed a /pagenumber */
		if ( $subpage > 1 )
			echo "<span class='subpage'>$subpage</span>";
		echo $closing; // close last link
		if ( !$firstloop )
			echo "</p>"; // There was at least one page
		
		echo '<input type="hidden" name="resetab" value="kaboom">';
		
		$ab = get_bookings( $cond, ( $orderby == "id" ? "bookid" : "$orderby,bookid" ), $soffset, $bookings_on_a_page );
		if ( !$ab )
			kaboom( mysql_error() );
	} // end of state 1 or 3
	
	/* At this point $ab contains the possibly (state2) partial booking
	list that is to be displayed to the user */
	
	?>
	
	<form action="<?php echo $bookinglist_url; ?>" method="post">
	<!-- default action : just save notes. Does not need any visible button -->
	<input type="hidden" name="save">
	
	<?php
	/* this is to persist filter settings accross links. We DON'T use
	session variables so that e.g. using "back" will work as expected,
	so that many views can be opened simultaneously, so that starting
	from main page will always show default view etc etc */
	
	foreach ( array(
		 "offset",
		"st",
		"showid",
		"sort" 
	) as $n => $f ) {
		if ( isset( $params[ $f ] ) )
			echo "<input type='hidden' name='$f' value='" . htmlspecialchars( $params[ $f ], ENT_QUOTES ) . "'>";
	}
	
	if ( $setstate )
		echo '<input type="hidden" name="setstate" value="' . $setstate . '">';
	
	if ( $ab ) {
		$total = 0; // total price of displayed elements
		$html  = array( ); // maps states to the html for bookings in said states.
		foreach ( $ab as $b ) {
			$id = $b[ 'bookid' ];
			$st = $b[ 'state' ];
			
			if ( !isset( $html[ $st ] ) ) {
				/* Make a header if this is the first booking in that state */
				$html[ $st ] = '<tr><td colspan=9><p class="main">';
				$html[ $st ] .= sprintf( $lang[ "booking_st" ], '<b>' . f_state( $st ) . '</b>' );
				$html[ $st ] .= '</p>';
			}
			
			$html[ $st ] .= '<tr><td>';
			$itemprice = get_seat_price( $b );
			if ( $st != ST_PAID )
				$total += $itemprice;
			if ( $checkboxes ) {
				if ( ( $filterst == ST_DELETED ) || ( $st != ST_DELETED ) )
					$html[ $st ] .= '<input type="checkbox" name="' . $id . '">';
			} else {
				// when no checkboxes we secretly check them all
				$html[ $st ] .= '<input type="hidden" name="' . $id . '">';
			}
			$url = admin_url( 'admin.php&fsp='.PAGE_SEATS.'&showid=' . $b[ 'showid' ] . '&amp;bookinglist');
			$html[ $st ] .= $id . "<td bgcolor='#ffffb0'><a href='$url'>" . $b[ 'date' ] . ' ' . f_time( $b[ 'time' ] ) . 
			// $html[ $st ] .= $id . '<td bgcolor="#ffffb0"><a href="seats.php?showid=' . $b[ 'showid' ] . '&amp;bookinglist">' . $b[ 'date' ] . ' ' . f_time( $b[ 'time' ] ) . 
			// check for -1: don't display row/col information for
			// unnumbered seats.
				'</a><td>' . ( $b[ 'row' ] == -1 ? '' : htmlspecialchars( $b[ 'col' ] ) . ', ' . $lang[ "row" ] . ' ' . htmlspecialchars( $b[ 'row' ] ) . ' ' ) . '(' . htmlspecialchars( $b[ 'zone' ] ) . ')' . '<td bgcolor="#ffffb0">' . f_cat( $b[ 'cat' ] ) . " (" . price_to_string( $itemprice ) . ")" . '<td>' . $b[ 'firstname' ] . ' <i>' . $b[ 'lastname' ] . '</i>' . '<td bgcolor="#ffffb0">' . f_mail( $b[ 'email' ] ) . '<td>' . $b[ 'phone' ] . "\n" . '<td bgcolor="#ffffb0">';
			if ( ( $st == ST_BOOKED ) || ( $st == ST_SHAKEN ) ) {
				if ( $b[ 'payment' ] == PAY_CCARD )
					$exp = strtotime( $b[ 'timestamp' ] ) + 86400 * $c[ "paydelay_ccard" ];
				else if ( $b[ 'payment' ] == PAY_POSTAL )
					$exp = sub_open_time( strtotime( $b[ 'timestamp' ] ), -86400 * $c[ "paydelay_post" ] );
				else {
					$exp = FALSE;
					$html[ $st ] .= '<i>' . $lang[ "none" ] . '</i>';
				}
				if ( $exp !== FALSE ) {
					$delta = $exp - $now; // ($now=time() is in tools.php)
					//	echo date("D d F H:i",$exp); // DEBUG
					
					if ( $delta < 0 )
						$html[ $st ] .= $lang[ "expired" ];
					else if ( $delta < 5400 )
						$html[ $st ] .= sprintf( $lang[ "in" ], ( (int) ( $delta / 60 ) ) . ' ' . $lang[ "minute" ] );
					else if ( $delta < 129600 )
						$html[ $st ] .= sprintf( $lang[ "in" ], ( (int) ( $delta / 3600 ) ) . ' ' . $lang[ "hour" ] );
					else
						$html[ $st ] .= sprintf( $lang[ "in" ], ( (int) ( $delta / 86400 ) ) . ' ' . $lang[ "day" ] );
				}
			} else
				$html[ $st ] .= '<i>' . $lang[ "none" ] . '</i>';
			
			$html[ $st ] .= do_hook_concat( 'bookinglist_tablerow', $b );
		}
		
		/** WARN - update colspan=9 where needed if you change columns **/
		$headers = '<tr><th>' . $lang[ "bookid" ] . '<th>' . $lang[ "date" ] . '<th>' . $lang[ "col" ] . '<th>' . $lang[ "cat" ] . '<th>' . $lang[ "name" ] . '<th>' . $lang[ "email" ] . '<th>' . $lang[ "phone" ] . '<th>' . $lang[ "expiration" ] . do_hook_concat( 'bookinglist_tableheader' );
		
		echo '<table cellspacing=0 cellpadding=4 border=0 class="bookinglist">' . $headers;
		
		/* Foreaching on the states rather than on $html itself to preserve
		state ordering */
		foreach ( array(
			 ST_BOOKED,
			ST_SHAKEN,
			ST_PAID,
			ST_DELETED,
			ST_DISABLED 
		) as $st ) {
			if ( isset( $html[ $st ] ) )
				echo $html[ $st ];
		}
		
		echo $headers . '</table>';
		
		if ( $checkboxes ) {
			if ( $filterst != ST_DELETED ) {
				echo '<ul><li><p class="main">' . $lang[ "set_status_to" ];
				submit_button( $lang['acknowledge'], 'primary', 'confirm', false );
				echo ' ';
				submit_button( $lang['DELETE'], 'primary', 'delete', false );
				// echo '<input type="submit" name="confirm" value="' . $lang[ "acknowledge" ] . '"> ';
				// echo '<input type="submit" name="delete" value="' . $lang[ "DELETE" ] . '">
				echo '</p></ul>';
			}
			do_hook( 'bookinglist_pagebottom' );
		} else {
			if ( $setstate == ST_PAID )
				echo '<p class="main">' . $lang[ "total" ] . '&nbsp;:' . price_to_string( $total ) . '</p>';
			
			// submit_button( $lang['confirmation'], 'primary', 'confirm', false );
			echo '<p class="main"><input type="submit" value="' . $lang[ "confirmation" ] . '">';
			echo '<a href="' . $bookinglist_url . '"';
			$sep = "?"; // what comes between params
			foreach ( array(
				 "offset",
				"st",
				"showid",
				"sort" 
			) as $n => $f ) {
				if ( isset( $params[ $f ] ) ) {
					echo "$sep$f=" . htmlspecialchars( $params[ $f ], ENT_QUOTES );
					$sep = "&amp;";
				}
			}
			echo '"> ' . $lang[ "cancel" ] . '</a></p>';
		}
	} else {
		echo '<p class="warning">' . $lang[ "warn-nomatch" ] . '</p>';
	}   
	echo '</form>';
	show_foot();
}  // end of freeseat_bookinglist

function freeseat_showedit()
{	/* This works in basically two modes that are selected by the $ready variable.
	When $ready is false, the data fields can be edited and POSTed back to this function.  
	If $ready is true, the data is displayed for the user to confirm and POSTed in 
	hidden fields.  If POST contains "save", the confirmed data is saved to the database, 
	and then the forms are opened for editing with $ready = false.  In either case,
	the data is carried in the $_POST array, and nothing is stored in $_SESSION.  
	The print_var() function in showedit.php sets up the HTML input fields based on
	the $ready variable.   */
	
	global $lang, $upload_url, $messages;
	if ( !current_user_can('administer_freeseat' ) ) { 
		wp_die( __( 'You do not have sufficient permissions to access this page. 2' ) );
	}
	$showedit_url = admin_url( 'admin.php?page=freeseat-showedit' );
	load_alerts();
	
	/** 1 - load db-stored data **/
	
	$totalbooked = 0;
	if (isset($_REQUEST["id"])) {	// we got something
		$spec = (int)$_REQUEST["id"];
	} else {	// otherwise get the latest spectacle from the DB
		$spec = m_eval( "select id from spectacles order by id desc limit 1" );
		if ( $spec === null ) { // empty spectacle table
			$spec = 0;
		}
	}
	
	if ($spec > 0) {  // if we have selected a saved spectacle, fetch data
		$dates = get_shows( "spectacle = $spec");
		foreach ($dates as $i => $dt)
			$totalbooked += ( $dates[ $i ][ "booked" ] = count_bookings( $dt[ "id" ] ) );
		$prices = get_spec_prices( $spec );
		$perf = get_spectacle( $spec );
	} else { 	// if we want a new spectacle, clear variables and use id 0 as marker
		$dates = array();
		$prices = array();
		$perf = array();
		$perf['id'] = 0;
	}
	
	/** 2 - load any POST-provided data **/
	
	$allisfine = true; // set to false in case something went wrong reading post data
	$permit_warn_booking = isset( $_POST[ "submit" ] ); // whether changes in
		// spectacle data may display that warn_booking message
	foreach (array( 'name', 'description', 'imagesrc' ) as $item) {
		if ( isset( $_POST[ $item ] ) )
			$perf[ $item ] = nogpc( $_POST[ $item ] );
	}
	//$prices = $_SESSION['prices'];
	for ( $i=1; $i<=4; $i++ ) { // class loop
		for ( $j=CAT_NORMAL; $j>=CAT_REDUCED; $j-- ) { // cat loop
			$item = "p_$i"."_$j";   //implode( "_", array( 'p', $i, $j ));
			if (isset( $_POST[ $item ] ) ) {
				if (
				$totalbooked && 
				$permit_warn_booking &&
				isset( $prices[ $i ] ) && 
				isset( $prices[ $i ][ $j ] ) &&
				$prices[ $i ][ $j ] != string_to_price( $_POST[ $item ] ) 
				) {
					$permit_warn_booking = false;
					kaboom( $lang[ "warn_bookings" ] );
	 			}
				$prices[ $i ][ $j ] = string_to_price( $_POST[ $item ] );
			}
		}
		if ( isset( $_POST[ "comment$i" ] ) )
			$prices[ $i ][ 'comment' ] = nogpc( $_POST[ "comment$i" ] );
	}
	
	/* performances with a simple number as here alter existing data ...*/
	for ( $i=0; isset($_POST["d$i"]); $i++ ) {
		// not isset()ting because they would only return false if user
		// alters the html before submitting a form
		
		// copytodates($i,'date',sanitise_date($_POST[ "d$i" ]));
		// copytodates($i,'time',sanitise_time($_POST[ "t$i" ]));
		
		$value = sanitise_date( $_POST[ "d$i" ] );
		if ( ( $permit_warn_booking ) && $dates[ $i ][ "booked" ] && ( $dates[ $i ][ 'date' ] != $value ) ) {
			kaboom($lang["warn_bookings"]);
			$permit_warn_booking = false;
		}
		$dates[ $i ][ 'date' ] = $value;		
		
		$value = sanitise_time( $_POST[ "t$i" ] );
		if ( ( $permit_warn_booking ) && $dates[ $i ][ "booked" ] && ( $dates[ $i ][ 'time' ] != $value ) ) {
			kaboom($lang["warn_bookings"]);
			$permit_warn_booking = false;
		}
		$dates[ $i ][ 'time' ] = $value;				
		
		if ( !isset( $dates[$i]["booked"] ) ) { // really don't allow changing theatre..
			// ..when seats have been sold..
			$dates[$i]['theatre'] = (int)($_POST[ "theatre_$i" ]);
		}
		$th = get_theatre( $dates[$i]['theatre'] );
		if (!$th) 
			$allisfine = kaboom($lang["err_spectacle"]);
	    else 
	    	$dates[$i]['theatrename'] = $th["name"];
	}
	/* ... while performances with an xnumber are new. */
	for ( $i=0; isset($_POST["dx$i"]); $i++ ) {
		// D�j� vu? RUN! They must have changed something in the Matrix
		// NOTE: no need to use the copytodates wrapper here because these
		// could not have sold tickets anyway
		$dates["x$i"]['date'] = sanitise_date($_POST[ "dx$i" ]);
		$dates["x$i"]['time'] = sanitise_time($_POST[ "tx$i" ]);
		$dates["x$i"]['theatre'] = (int)($_POST[ "theatre_x$i" ]);
		$th = get_theatre($dates["x$i"]['theatre']);
		if (!$th) 
			$allisfine = kaboom($lang["err_spectacle"]);
		else 
			$dates["x$i"]['theatrename'] = $th["name"];
	}
	$nextxtra = $i; // this is the first $i such that dx$i was NOT defined.
	
	get_upload($perf);
	
	// imagesrc has now been set as follows:
	// 1: if user uploads something then that is the value.
	// 2: otherwise, if POST gives a values then that is taken
	// 3: otherwise, any existing data in the database is used
	// print "<pre>Dates = " . print_r( $dates, 1 ) . "</pre>";
	// make sure all of the variables are initialized
	for ( $i=1; $i<=4; $i++ ) {   // class loop
		for ( $j=CAT_NORMAL; $j>=CAT_REDUCED; $j-- ) {   // cat loop
			if ( !isset( $prices[ $i ][ $j ] ) ) $prices[ $i ][ $j ] = 0.0;
		}
		if ( !isset( $prices[ $i ][ 'comment' ] ) ) $prices[ $i ][ 'comment' ] = "";
	}
	foreach ( array( 'name', 'description', 'imagesrc' /*, 'active'*/ ) as $item ) {
		if ( !isset( $perf[ $item ] ) ) $perf[ $item ] = "";
	}
	
	/** 3 - validate data **/
	$ready = ( isset( $_POST[ "submit" ] ) || isset( $_POST[ "save" ] ) )  // $ready:
		&& ( $allisfine );			       // When true, show readonly
	if ( isset( $_POST[ "edit" ] ) ) {
		$ready = false;            // data and a button to save
	}
	if ($ready) {			        // changes. When false show
		if ($perf["name"]=='') {	       // an editable form and a
			$ready = false;		      // button to confirm. It is
			kaboom($lang["err_nospec"]);     // set to true if user
		}				    // submitted a form and there were no mistakes
		$atleastone = false;
		foreach ($dates as $dt) {
			if (isset($dt['date']) && ($dt['date']!="0000-00-00")) $atleastone = true;
		}
		if (!$atleastone) {
	    	$ready = false;
	    	kaboom($lang["err_nodates"]);
		}
		$atleastone = false;
		for ( $i=1; $i<=4; $i++ ) {  // class loop
	    	if (isset($prices[$i][CAT_NORMAL] ) && ($prices[$i][CAT_NORMAL]>0))
	      		$atleastone = true;
	    }
		if (!$atleastone) {
			$ready = false;
			kaboom($lang["err_noprices"]);
		}
		if (!$ready) {   // something went wrong
			kaboom($lang["err_show_entry"]);
		}
	}
	
	// Note that the error message is set only if user requested saving
	// BUT storing failed. And in that case the if is not taken so we
	// proceed to user interface without redirection.
	if ($ready && isset($_POST["save"])) {
		
	/* 4 - data is valid and user requested saving */

	    $spec = set_perf( $perf );
	    if ($spec) {
			if (!(set_spec_prices( $spec, $prices ) &&
			      set_dates( $spec, $dates ))) {
			    kaboom($lang["show_not_stored"]);
		
			    // the set_*()ing failed so we set ready to false to make
			    // the interface editable again
			    $ready = false;
			} else {
			    // success
			    kaboom($lang["show_stored"]);
			    $_SESSION["messages"] = $messages;
			    $perf['id'] = $spec;
				$ready = false;
			    do_hook_function("showedit_save", $perf);
			    // clear variables and redisplay
			    $_POST = array();
			    $dates = get_shows( "spectacle = $spec");
				foreach ($dates as $i => $dt)
					$totalbooked += ( $dates[ $i ][ "booked" ] = count_bookings( $dt[ "id" ] ) );
				$prices = get_spec_prices( $spec );
				$perf = get_spectacle( $spec );
				for ( $i=1; $i<=4; $i++ ) {   // class loop
					for ( $j=CAT_NORMAL; $j>=CAT_REDUCED; $j-- ) {   // cat loop
						if ( !isset( $prices[ $i ][ $j ] ) ) $prices[ $i ][ $j ] = 0.0;
					}
					if ( !isset( $prices[ $i ][ 'comment' ] ) ) $prices[ $i ][ 'comment' ] = "";
				}
				foreach ( array( 'name', 'description', 'imagesrc' ) as $item ) {
					if ( !isset( $perf[ $item ] ) ) $perf[ $item ] = "";
				}
			}
	    } else {
			// failed creating a spectacle
			$ready = false;
	    }
	}
	
	/** 5 - show user interface **/
	
	show_head();
	
	/* uncomment following lines to display debugging information  
	echo '<pre>POST:';print_r($_POST);echo '</pre>';
	echo '<pre>dates:';print_r($dates);echo '</pre>'; 
	echo '<pre>perf:';print_r($perf);echo '</pre>'; 
	echo "<pre>spec=$spec</pre>";  */
	
	echo '<h2>' . $lang[$ready?'title_mconfirm':'title_maint'] . '</h2>';
	echo "<form action='$showedit_url' name='choose_spec' method='post'>";
	// spectacle selection form depends on the onchange action in choose_spectacle()
	echo '<h3>' . $lang["spectacle_name"] . '</h3>';
	choose_spectacle( true, $spec );
	echo ' <input class="button button-primary" type="submit" value="'.$lang["select"].'">'; 
	// the following doesn't work, but don't know why
	// submit_button( $lang[ "select" ], 'primary', 'submit', false );
	echo '</form>';
	echo '<div class="form">'; // the big div
	echo "<form action='$showedit_url' method='post' enctype='multipart/form-data'>"; // data submission form
	
	echo '<input style="display : none;" type="submit" name="submit">';// default action when user presses enter in a field
	
	echo '<div class="image-selection"><h3>' . $lang['imagesrc'] . '</h3>' ;    // image upload form
	 // imagesrc: default, to be used if user does not upload an image.
	echo '<input type="hidden" name="imagesrc" value="'.htmlspecialchars($perf["imagesrc"]).'">';
	if ($perf['imagesrc']) {
	    echo $lang['file'] . htmlspecialchars($perf['imagesrc']) . '<br>';
	    echo '<img src="' . htmlspecialchars( plugins_url( $upload_url.$perf['imagesrc'], __FILE__ ) ) . '"><br>';
	} else
		echo $lang['noimage'];
	if (!$ready) choose_local_file('image');
	echo '</div>';
	echo '<input type="hidden" name="id" value="'.$spec.'">';
	print_var( "name", $perf['name'], $ready, $lang["name"], 40) ;
	print_var( "description", $perf['description'], $ready, $lang["description"], 75);
	
	echo '<div class="form">';
	echo '<h3>' . $lang['datesandtimes'] . '</h3>';
	if (!$ready) echo '<p class="fine-print">' . $lang["warn_spectacle"] . '</p>';
	
	echo '<table BORDER="1" CELLPADDING="4">';
	echo '<tr><th>'.$lang["date_title"].'<th>'.$lang["time_title"].'<th>'.$lang["theatre_name"].'<th>'.$lang["seats_booked"].'</tr>';
	$dispperf = 0;
	foreach ( $dates as $i => $dt ) {
		echo '<tr><td>';
		print_var("d$i",(isset($dt['date']) ? $dt['date'] : ''), $ready);
		echo '</td><td>';
		print_var("t$i",(isset($dt['time']) ? $dt['time'] : ''), $ready);
		echo '</td><td>';
		if (!$ready && ((substr($i,0,1)=='x') || (!$dt["theatrename"])))
		  choose_seatmap( $i, $dt["theatre"] );
		else {
		  echo "<input type='hidden' name='theatre_$i' value='".htmlspecialchars($dt['theatre'])."'>";
		  echo htmlspecialchars($dt['theatrename']);
		}
		echo '</td><td>';
		echo isset($dt["id"])? count_bookings($dt["id"]): 'n/a';
		$dispperf++;
	}
	
	if (isset($_POST["perfcount"]))
	    $perfcount = max($dispperf,(int)($_POST["perfcount"]));
	else
	    $perfcount = $dispperf;
	
	if (isset($_POST["addperf"]))
	     $perfcount ++;
	
	if (!$ready) {
	  for ($i = $nextxtra; $dispperf<$perfcount; $i++) {
	    /* more lines to allow adding performances */
	    echo '<tr><td>';
	    print_var("dx$i",'', $ready);
	    echo '<td>';
	    print_var("tx$i",'', $ready);
	    echo '<td>';
	    choose_seatmap( "x$i" ); // x as in "extra" - i guess you got
				     // the..
	    echo '<td>n/a';
	    $dispperf++;		  // ..idea by now
	  }
	}
	  
	echo '</table>';
	echo '<input type="hidden" name="perfcount" value="'.$perfcount.'">';
	if (!$ready) {
		submit_button( "+ Add a Date", 'secondary', 'addperf' );
		// echo '<input type="submit" name="addperf" value="+ Add a Date">';
	}
	echo '</div><div class="form">';
	echo '<h3>' . $lang['prices'] . '</h3>';
	echo '<table BORDER="1" CELLPADDING="4" >';
	echo '<tr><th>'. $lang["class"] . '<th>'.$lang["price"].'<th>'.$lang["price_discount"].'<th>'.$lang["comment"].'</tr>';
	for ( $i=1; $i<=4; $i++ ) { // class loop
		echo '<tr><td>'. $i . '</td>';
		for ( $j=CAT_NORMAL; $j>=CAT_REDUCED; $j-- ) { // cat loop
			echo '<td>';
			print_var("p_$i"."_$j",price_to_string($prices[$i][$j]), $ready);
		}
		echo '<td>';
		print_var("comment$i", $prices[$i]['comment'], $ready);
		echo '</tr>';
	}
	
	echo '</table></div><p>';
	echo '<br style="clear:both;"></div>'; // a br to make the big div large enough
	
	if ($ready) {
	  echo '<p class="emph">' . $lang["warn_show_confirm"] . '</p>';
	  echo '<p class="main">';
	  submit_button( $lang[ "save" ], 'primary', 'save', false );
	  // echo '<p class="main"><input type="submit" name="save" value="' . $lang["save"] . '"></p>';
	  // echo '<p class="main">';
	  // printf($lang["backto"],'<input type="submit" name="edit" value="'.$lang["link_edit"].'">');
	  // echo '</p>';
	  echo '  ';
	  submit_button( $lang[ "link_edit" ], 'primary', 'edit', false );
	  echo '</p>';
	} else {
	  echo '<p class="emph">' . $lang["are-you-ready"] . '</p>';
	  echo '<p class="main">';
	  submit_button( $lang["continue"] );
	  echo '</p>';
	}
	
	echo '</p></form>';
	show_foot(); 
	echo '</div>';
} // end of freeseat-showedit

/**
 * Adds extra submenus and menu options to the admin panel's menu structure
 */
function freeseat_admin_menu() {
	// Add menus - available only for Administrators
	// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
	// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
	add_menu_page( 'Current Shows', 'FreeSeat', 'administer_freeseat', 'freeseat-admin', 'freeseat_switch', plugins_url( 'freeseat/ticket.png' ) );
	add_submenu_page( 'freeseat-admin', 'Current Shows', 'Current Shows', 'administer_freeseat', 'freeseat-admin', 'freeseat_switch' );
	add_submenu_page( 'freeseat-admin', 'View Reservations', 'Reservations', 'administer_freeseat', 'freeseat-reservations', 'freeseat_bookinglist' );
	add_submenu_page( 'freeseat-admin', 'Show Setup', 'Show Setup', 'administer_freeseat', 'freeseat-showedit', 'freeseat_showedit' );
	add_submenu_page( 'freeseat-admin', 'Edit Settings', 'Settings', 'administer_freeseat', 'freeseat-system', 'freeseat_params' );
	add_submenu_page( 'freeseat-admin', 'Seatmaps', 'Seatmaps', 'administer_freeseat', 'freeseat-upload', 'freeseat_upload' );
}

function freeseat_update_db_check() {
	global $freeseat_db_version;
	if (get_option( 'freeseat_db_version' ) != $freeseat_db_version) {
		freeseat_install();
	}
}

/**
 * Register style sheet.
 */
function freeseat_user_styles() {
	global $stylesheet;
	
	wp_register_style( 'freeseat_styles', plugins_url( 'freeseat/' . $stylesheet ) );
	wp_enqueue_style( 'freeseat_styles' );
}

function freeseat_admin_styles( $hook ) {
	global $stylesheet;
	
	if ( false === strpos( $hook, 'page_freeseat' ) )
		return;
	wp_register_style( 'freeseat_styles', plugins_url( 'freeseat/' . $stylesheet ) );
	wp_enqueue_style( 'freeseat_styles' );
}

/**
 * Add freeseat administration capability to editor and administrator roles.
 */
function freeseat_add_caps() {
	$role = get_role( 'editor' );
	$role->add_cap( 'administer_freeseat' );
	$role = get_role( 'administrator' );
	$role->add_cap( 'administer_freeseat' );
}
register_activation_hook( __FILE__, 'freeseat_add_caps');

/**
 * Insert menus and shortcode calls.
 */
add_action( 'admin_menu', 'freeseat_admin_menu' );
add_action( 'plugins_loaded', 'freeseat_update_db_check' );
add_shortcode( 'freeseat-shows', 'freeseat_switch' );

/**
 * Insert style sheet calls.
 */
add_action( 'wp_enqueue_scripts', 'freeseat_user_styles' );
add_action( 'admin_enqueue_scripts', 'freeseat_admin_styles' );

// delete_option('freeseat_options');
// freeseat_add_defaults();

