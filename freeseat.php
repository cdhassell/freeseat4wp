<?php namespace freeseat;
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

/*  Reminder about using namespaces:
<?php namespace NS;
    define(__NAMESPACE__ .'\foo','111');
    define('foo','222');
    echo foo;  		// 111.
    echo \foo;  	// 222.
    echo \NS\foo  	// 111.
    echo NS\foo  	// fatal error. assumes \NS\NS\foo.
?>
*/

// All paths in FreeSeat are relative to FS_PATH
define( 'FS_PATH', plugin_dir_path( __FILE__ ) );
// Load the default values of key global variables
require_once( FS_PATH . "vars.php" );
// Include all of the function files
foreach ( glob( plugin_dir_path( __FILE__ )."functions/*.php" ) as $file ) {
	include_once $file;
}

/* We keep time in a global variable so that if we need time more than
 * once in a single script execution, the same value will be returned
 */
$now = time();
global $freeseat_db_version;
$freeseat_db_version = "0.1";


// Most of the configuration globals can be edited by the user
// and are stored in the database so config.php does not have to be touched.
// This code retrieves the stored variable data.
require_once( FS_PATH . "options.php" );
// Get the global variables from the database
$freeseat_vars = get_config();
if ( is_array($freeseat_vars) ) {
	foreach ( $freeseat_vars as $var => $value ) {
		if (false === strpos($var,'chk_')) {
			$$var = $value;
		}	
	}
}

// These are the entry points from wordpress hooks
add_action( 'admin_init', __NAMESPACE__ . '\\freeseat_wordpress_version' );
register_activation_hook( __FILE__, __NAMESPACE__ . '\\freeseat_add_caps');
add_action( 'admin_menu', __NAMESPACE__ . '\\freeseat_admin_menu' );
add_action( 'plugins_loaded', __NAMESPACE__ . '\\freeseat_update_db_check' );
add_shortcode( 'freeseat-shows', __NAMESPACE__ . '\\freeseat_front' );
add_shortcode( 'freeseat-direct', __NAMESPACE__ . '\\freeseat_direct' );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\freeseat_user_styles' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\freeseat_admin_styles' );

// Set up all of the active freeseat plugins
$freeseat_plugin_hooks = array();
if (isset($plugins) && is_array($plugins)) {
    foreach ($plugins as $name) {
		use_plugin($name);
    }
}

// DEFINEs for workflow control
define( 'PAGE_INDEX',	0 );
define( 'PAGE_REPR',	1 );
define( 'PAGE_SEATS',	2 );
define( 'PAGE_PAY', 	3 );
define( 'PAGE_CONFIRM',	4 );
define( 'PAGE_FINISH',	5 );

// FreeSeat pages are now constructed by functions
// rather than by global code so we have to include them
require_once( FS_PATH . "install.php" );
require_once( FS_PATH . "repr.php" );
require_once( FS_PATH . "seats.php" );
require_once( FS_PATH . "pay.php" );
require_once( FS_PATH . "confirm.php" );
require_once( FS_PATH . "finish.php" );
require_once( FS_PATH . "seatmaps.php" );
require_once( FS_PATH . "bookinglist.php" );
require_once( FS_PATH . "showedit.php" );

db_connect();

/*
 * Switching station for entry to ticket workflow
 * Depending on GET vars to select next step
 * Using the defines PAGE_*
 */
function freeseat_switch( $shortcode_fsp = 0 ) {
	// Where are we?  Switch to the right freeseat page based on fsp
	global $post, $page_url;
	// if we are passed a page number from a shortcode call, use it
	$fsp = ( $shortcode_fsp ? $shortcode_fsp : 0 );
	// however a page number from GET will override that
	$fsp = (( isset( $_GET[ 'fsp' ] ) ) ? $_GET[ 'fsp' ] : $fsp );
	// build a page URL
	$page_url = (( isset( $post ) ) ? get_permalink() : $_SERVER['PHP_SELF'].'?page=freeseat-admin' );
	$page_url = add_query_arg( 'fsp', $fsp, $page_url);
	sys_log( "permalink = ".get_permalink()." page = $page_url" );
	// adjust for WP's strange inconsistent way of passing a post number
	/* $page_url = str_replace('page_id','p',$page_url);
	$args = '';
	$and = '';
	// keep relevant GET parameters
	foreach( array( 'fsp', 'showid', 'spectacleid', 'offset', 'st', 'sort', 'ok', 'mode' ) as $key => $value ) {
		if ( isset( $_GET[ $key ] ) ) {
		    $args .= $and . $key . '=' . $value;
		    if ( empty( $and ) ) $and = '&';
		}
	}
	if ( !empty( $args ) ) {
		$page_url .= (( false === strpos( $page_url, '?')) ? '?' : '&' ) . $args;
	} */
	// now call the right function and pass this url to it
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

/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.

$Id: index.php 341 2011-04-25 19:03:48Z tendays $

Modifications for Wordpress are Copyright (C) 2013 twowheeler.
*/

/*
 * This function replaces the former index.php file
 * It is the default starting page for freeseat
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
	echo '</div><div id="front-container">';
	if ( !empty($lang['index_head'] ) ) 
		echo '<h2>'. $lang[ "index_head" ] . '</h2>';
	
	// output a table showing all currently available shows with dates and times
	// with links to the show pages
	echo '<table>';
	foreach ( $ss as $s ) {
		$url = $page_url . '&fsp=' . PAGE_REPR . '&spectacleid=' . $s[ "id" ];
		$linkl = "<a href='$url'>";
		$linkr = '</a>';
				  
		echo '<tr>';
		if ( $s[ 'imagesrc' ] ) {
			$img = freeseat_url( $upload_path . $s[ 'imagesrc' ] );
			echo '<td class="showlist">' . $linkl . '<img src="' . $img . '">' . $linkr . '</td>';
		} else {
			echo '<td class="showlist"></td>';
		}
		echo '<td class="showlist">' . $linkl . '<h3>' . $s[ 'name' ] . '</h3>' . $linkr;
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
				$target = $page_url . '&fsp=' . PAGE_SEATS . '&showid=' . $showid;
				echo "<li><p><a style='color: #303030;' href='$target'>$d, $t</a></p></li>";
			}
			echo '</ul>';
		}
		echo '</td></tr>';
	}
	echo '</table>';
	do_hook( 'front_page_end' );
	show_foot();
}	// end of freeseat_frontpage

/*
 * Checks the WP version and deactiviates FreeSeat if the version is too old
 */
function freeseat_wordpress_version() {
	global $wp_version;
	$plugin = plugin_basename( __FILE__ );
	$plugin_data = get_plugin_data( __FILE__, false );

	if ( version_compare($wp_version, "3.0", "<" ) ) {
		if( is_plugin_active($plugin) ) {
			deactivate_plugins( $plugin );
			wp_die( "'".$plugin_data['Name']."' requires WordPress 3 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
		}
	}
}

/**
 * Adds extra submenus and menu options to the admin panel's menu structure
 */
function freeseat_admin_menu() {
	// Add menus - available only for Administrators
	// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
	// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
	add_menu_page( 'Current Shows', 'FreeSeat', 'administer_freeseat', 'freeseat-admin', __NAMESPACE__ . '\\freeseat_switch', plugins_url( 'freeseat/ticket.png' ) );
	add_submenu_page( 'freeseat-admin', 'Current Shows', 'Current Shows', 'administer_freeseat', 'freeseat-admin', __NAMESPACE__ . '\\freeseat_switch' );
	add_submenu_page( 'freeseat-admin', 'View Reservations', 'Reservations', 'administer_freeseat', 'freeseat-reservations', __NAMESPACE__ . '\\freeseat_bookinglist' );
	add_submenu_page( 'freeseat-admin', 'Show Setup', 'Show Setup', 'administer_freeseat', 'freeseat-showedit', __NAMESPACE__ . '\\freeseat_showedit' );
	add_submenu_page( 'freeseat-admin', 'Edit Settings', 'Settings', 'administer_freeseat', 'freeseat-system', __NAMESPACE__ . '\\freeseat_params' );
	add_submenu_page( 'freeseat-admin', 'Seatmaps', 'Seatmaps', 'administer_freeseat', 'freeseat-upload', __NAMESPACE__ . '\\freeseat_upload' );
}

/*
 *  Handler for the shortcode call freeseat-direct
 */
function freeseat_direct( $atts ) {
	extract( shortcode_atts( array(
		'showid' => '0',
	), $atts ) );
	ob_start();
	$_SESSION['showid'] = $showid;
	freeseat_switch('2');
	return ob_get_clean();
}

/*
 *  Handler for the shortcode call freeseat-shows
 */
function freeseat_front() {
	ob_start();
	freeseat_switch();
	return ob_get_clean();
}

/*
 * If the FreeSeat database version has changed, run the installer again
 */
function freeseat_update_db_check() {
	global $freeseat_db_version;
	if (get_option( 'freeseat_db_version' ) != $freeseat_db_version) {
		freeseat_install();
	}
}

/**
 * Register style sheet for users
 */
function freeseat_user_styles() {
	global $stylesheet;
	
	wp_register_style( 'freeseat_styles', plugins_url( 'freeseat/' . $stylesheet ) );
	wp_enqueue_style( 'freeseat_styles' );
}

/**
 * Register style sheet for administrator
 */
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

// these can be uncommented to quickly create the default options in the database
// normally that only happens on first install
// delete_option('freeseat_options');
// freeseat_add_defaults();

