<?php namespace freeseat;

/*
 * This function replaces the former index.php file
 * It is the default starting page for freeseat
 */

/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.

$Id: index.php 341 2011-04-25 19:03:48Z tendays $
*/

function freeseat_frontpage( $page_url ) {
	global $lang, $upload_path, $post;
			
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
	// display all currently available shows with dates and times
	// with links to the show pages
	foreach ( $ss as $s ) {
		echo "<div class='container'>";	    
		if ( $s[ 'imagesrc' ] ) {
			echo '<div class="leftblock"><img src="' . $s['imagesrc'] . '"></div>';
		}
		echo '<div class="showlist">';
		echo '<h3>' . $s[ 'name' ] . '</h3>';
		/** WARN - we assume whoever filled the description field to be
		  trustworthy enough not to write malicious or malformed html */
		if ( $s[ "description" ] ) {
			echo '<p class="description"><i>' . $s[ 'description' ]  . '</i></p>';
		}
		echo "<!--nextpage-->";
		if ($s) {
			echo '<p>'.$lang['datesandtimes'].'</p><ul>';
			$shows = fetch_all( "select * from shows where date >= curdate() and spectacle='".$s['id']."' order by date" );
			foreach ($shows as $show) {	
				/* total seats */
				$tot = m_eval( "select count(*) from seats where theatre=" . $show[ "theatre" ] );
				/* how many have been booked so far */
				$bk  = m_eval( "select count(*) from booking where showid=" . $show[ "id" ] . " and state!=" . ST_DELETED );
				/* how many are disabled (included in above counts) */
				$ds  = m_eval( "select count(*) from booking where showid=" . $show[ "id" ] . " and state=" . ST_DISABLED );
				if ( ( $tot === null ) || ( $bk === null ) || ( $ds === null ) ) {
					$tot = "??";
					$bk  = "??";
				} else {
					$tot -= $ds;
					$bk -= $ds;
				}
				$summary = ( admin_mode() ? " ($bk/$tot)" : "" );
				$showid = $show['id'];
				$d = f_date($show['date']);
				$t = f_time($show['time']);
				$target = replace_fsp( $page_url, PAGE_SEATS ) . '&showid=' . $showid;
				echo "<li><a href='$target'>$d, $t</a>$summary</li>";
			}
			echo '</ul>';
		}
		echo '</div>';  // end of showlist
		echo '</div>';  // end of container
	}
	do_hook( 'front_page_end' );
	show_foot();
}	// end of freeseat_frontpage



