<?php namespace freeseat;
/*
Plugin Name: FreeSeat
Plugin URI: http://github.com/cdhassell/freeseat4wp
Description: FreeSeat for Wordpress implements a theatre ticketing system with optional links to CiviCRM.
Version: 0.2
Author: twowheeler
Author URI: 
Text Domain: freeseat4wp
Domain Path: /languages
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
$messages = array();	
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
add_shortcode( 'freeseat-single', __NAMESPACE__ . '\\freeseat_single' );
add_shortcode( 'freeseat-direct', __NAMESPACE__ . '\\freeseat_direct' );
add_shortcode( 'freeseat-finish', __NAMESPACE__ . '\\freeseat_return' );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\freeseat_user_styles' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\freeseat_admin_styles' );

add_action('init', __NAMESPACE__ . '\\freeseat_start_session', 1);
add_action('wp_logout', __NAMESPACE__ . '\\freeseat_kill_session');
add_action('wp_login', __NAMESPACE__ . '\\freeseat_kill_session');

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), __NAMESPACE__ . '\\freeseat_sample_data_link' );
add_filter( 'plugin_action_links_'. plugin_basename(__FILE__), __NAMESPACE__ . '\\freeseat_plugin_settings_link', 10, 2 );
add_action( 'activated_plugin', __NAMESPACE__ . '\\save_error');
remove_action( "admin_color_scheme_picker", "admin_color_scheme_picker");  // just because it is annoying


function freeseat_start_session() {
	session_name("freeseat4wp");
	if (isset($_COOKIE['PHPSESSID'])) {
		$sessid = $_COOKIE['PHPSESSID'];
		session_id($sessid);
	} else if (isset($_GET['PHPSESSID'])) {
		$sessid = $_GET['PHPSESSID'];
		session_id($sessid);
	} else {
		if(!isset($_SESSION)) session_start();
	}
	// sys_log("session id = ".session_id());
}

function freeseat_kill_session() {
	sys_log("Session destroyed");
    session_destroy();
}

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
require_once( FS_PATH . "frontpage.php" );
// require_once( FS_PATH . "repr.php" );
require_once( FS_PATH . "seats.php" );
require_once( FS_PATH . "pay.php" );
require_once( FS_PATH . "confirm.php" );
require_once( FS_PATH . "finish.php" );
require_once( FS_PATH . "seatmaps.php" );
require_once( FS_PATH . "showedit.php" );
require_once( FS_PATH . "newcron.php" );
require_once( FS_PATH . "bookinglist2.php" );

/*
 *  Switching station for entry to ticket workflow
 *  Depending on GET vars to select next step
 *  Using the defines PAGE_*
 */
function freeseat_switch( $shortcode_fsp = 0 ) {
	// Switch to the right freeseat page based on fsp
	global $page_url;
	// if we are passed a page number from a shortcode call, use it
	$fsp = ( $shortcode_fsp ? $shortcode_fsp : 0 );
	// however a page number from GET will override that
	$fsp = (( isset( $_GET[ 'fsp' ] ) ) ? $_GET[ 'fsp' ] : $fsp );
	if (isset($_POST['clearcart'])) {
		$fsp = 0;
		kill_booking_done();
	}
	// build a page URL
	if ( !isset( $page_url ) ) 
		$page_url = get_permalink();  // add_query_arg( 'page', 'freeseat-admin', get_permalink() );  
	$page_url = replace_fsp( $page_url, $fsp );
	// now call the right function and pass this url to it
	switch( $fsp ) {
		case PAGE_FINISH:
			freeseat_finish( $page_url );
			break;
		case PAGE_CONFIRM:
			freeseat_confirm( $page_url );
			break;
		case PAGE_PAY:
			freeseat_pay( $page_url );
			break;
		case PAGE_SEATS:
			freeseat_seats( $page_url );
			break;
		/* case PAGE_REPR:
			freeseat_repr( $page_url );
			break; */
		default:
			freeseat_frontpage( $page_url );
			break;
			
	}
}

/*
 *  A small helper function to replace the 'fsp' page number
 *  $url is the url with a ?fsp=something embedded
 *  $newpage is one of the PAGE_* constants for a redirect
 *
 */
function replace_fsp( $url, $newpage ) {
	$url = remove_query_arg( 'fsp', $url );
	$url = add_query_arg( array( 'fsp'=>$newpage ), $url);
	return $url;
}

/** Returns the list of available languages. */
function language_list() {
	$langs = array();
	if ($dh = opendir(FS_PATH . 'languages')) {
		while (($file = readdir($dh)) !== false) {
			if (preg_match('/^(.*)\.php$/',$file,$matches)) {
				if ($matches[1] != 'default') {
					$langs[] = $matches[1];
				}
			}
		}
		closedir($dh);
	}
	return $langs;
}

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
	// Add menus - available only for Managers or Administrators 
	// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
	// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );	
	add_menu_page( 'Current Shows', 'FreeSeat', 'read', 'freeseat-admin', __NAMESPACE__ . '\\freeseat_switch', plugins_url( 'ticket.png', __FILE__ ) );
	add_submenu_page( 'freeseat-admin', 'Current Shows', 'Current Shows', 'manage_freeseat', 'freeseat-admin', __NAMESPACE__ . '\\freeseat_switch' );
	add_submenu_page( 'freeseat-admin', 'View Reservations', 'Reservations', 'manage_freeseat', 'freeseat-listtable', __NAMESPACE__ . '\\freeseat_render_list' );
	add_submenu_page( 'freeseat-admin', 'Show Setup', 'Show Setup', 'manage_freeseat', 'freeseat-showedit', __NAMESPACE__ . '\\freeseat_showedit' );
	add_submenu_page( 'freeseat-admin', 'Seatmaps', 'Seatmaps', 'manage_freeseat', 'freeseat-upload', __NAMESPACE__ . '\\freeseat_upload' );
	add_submenu_page( 'freeseat-admin', 'Settings', 'Settings', 'administer_freeseat', 'freeseat-system', __NAMESPACE__ . '\\freeseat_params' );
	// add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function);
	add_options_page('Settings', 'FreeSeat', 'administer_freeseat', 'freeseat-system', __NAMESPACE__ . '\\freeseat_params');
}

/*
 *  Handler for the shortcode call freeseat-finish
 *  Must return output rather than echo it
 */
function freeseat_return( $atts ) {
	extract( shortcode_atts( array(
		'groupid'  => '0',
	), $atts ) );
	ob_start();
	$_SESSION['groupid'] = $groupid;
	freeseat_switch('5');
	return ob_get_clean();
}

/*
 *  Handler for the shortcode call freeseat-direct
 *  Must return output rather than echo it
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
 *  Handler for the shortcode call freeseat-single
 *  Must return output rather than echo it
 */
function freeseat_single( $atts ) {
	extract( shortcode_atts( array(
		'spectacleid' => '0',
	), $atts ) );
	ob_start();
	if ($spectacleid) $_GET['spectacleid'] = $spectacleid;
	freeseat_switch('1');
	return ob_get_clean();
}

/*
 *  Handler for the shortcode call freeseat-shows
 *  Must return output rather than echo it
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
	
	wp_register_style( 'freeseat_styles', plugins_url( $stylesheet, __FILE__ ) );
	wp_enqueue_style( 'freeseat_styles' );
}

/**
 * Register style sheet for administrator
 */
function freeseat_admin_styles( $hook ) {
	global $stylesheet;
	
	if ( false === strpos( $hook, 'page_freeseat' ) )
		return;
	wp_register_style( 'freeseat_styles', plugins_url( $stylesheet, __FILE__ ) );
	wp_enqueue_style( 'freeseat_styles' );
}

/**
 *  Add freeseat administration capability to editor and administrator roles.
 *  Creates a role for a Freeseat Manager, with capabilities to run the box office
 *  But not to change the system settings. 
 */
function freeseat_add_caps() {
	add_role( 'freeseat_manager', 'Freeseat Manager', array('manage_freeseat', 'use_freeseat') );
	$role = get_role( 'administrator' );
	$role->add_cap( 'administer_freeseat' );
	$role->add_cap( 'manage_freeseat' );
	$role->add_cap( 'use_freeseat' );
	$role = get_role( 'subscriber' );
	$role->add_cap( 'use_freeseat' );
}

/**
 *  Adds a link to the plugin screen for installing sample data
 *  It disappears once there is data in the tables
 */
function freeseat_sample_data_link( $links ) {
	if (!get_option('freeseat_data_installed')) {
		$links[] = '<a href="'. admin_url( 'plugins.php?install=data&plugin=freeseat') .'">Add sample data</a>';
	}
	return $links;
}

// Display a Settings link on the main Plugins page
function freeseat_plugin_settings_link( $links ) {
	$freeseat_links = '<a href="'.admin_url( 'admin.php?page=freeseat-system' ).'">'.__('Settings').'</a>';
	// make the 'Settings' link appear first
	array_unshift( $links, $freeseat_links );
	return $links;
}

// this is for debugging purposes - captures startup error messages and saves them in the db
function save_error() {
    update_option('plugin_error',  ob_get_contents());
}
