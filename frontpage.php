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
	// echo '</div><div id="front-container">';
	if ( !empty($lang['index_head'] ) ) 
		echo '<h2>'. $lang[ "index_head" ] . '</h2>';
	
	// output a table showing all currently available shows with dates and times
	// with links to the show pages
	// echo '<table>';
	foreach ( $ss as $s ) {
		echo "<div class='container'>";
		$url = replace_fsp( $page_url, PAGE_REPR ). '&spectacleid=' . $s[ "id" ];
		$linkl = "<a href='$url'>";
		$linkr = '</a>';
				  
		// echo '<tr>';
		if ( $s[ 'imagesrc' ] ) {
			$img = freeseat_url( $upload_path . $s[ 'imagesrc' ] );
			echo '<div class="leftblock">' . $linkl . '<img src="' . $img . '">' . $linkr . '</div>';
		} else {
			echo '<div class="leftblock"></div>';
		}
		echo '<div class="showlist">' . $linkl . '<h3>' . $s[ 'name' ] . '</h3>' . $linkr;
		/** WARN - we assume whoever filled the description field to be
		  trustworthy enough not to write malicious or malformed html */
		if ( $s[ "description" ] ) {
			echo '<p><i>' . stripslashes( $s[ 'description' ] ) . '</i></p>';
		}
		if ($s) {
			echo '<p>'.$lang['datesandtimes'].'</p><ul>';
			$shows = fetch_all( "select * from shows where date >= curdate() and spectacle='".$s['id']."' order by date" );
			foreach ($shows as $show) {
				$showid = $show['id'];
				$d = f_date($show['date']);
				$t = f_time($show['time']);
				$target = replace_fsp( $page_url, PAGE_SEATS ) . '&showid=' . $showid;
				echo "<li><a href='$target'>$d, $t</a></li>";
			}
			echo '</ul>';
		}
		echo '</div>';  // end of showlist
		echo '</div>';  // end of container
	}
	
	do_hook( 'front_page_end' );
	show_foot();
}	// end of freeseat_frontpage



