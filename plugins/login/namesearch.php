<?php

/*
 *   This is for ajax queries for the name search in the login plugin. 
 */

// import wordpress stuff
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );

// import freeseat stuff
define( 'FS_PATH', dirname( dirname( plugin_dir_path( __FILE__ ) ) ) );
// Load the default values of key global variables
require_once( FS_PATH . "vars.php" );
// Include the function files
require_once( FS_PATH . "functions/mysql.php" );
require_once( FS_PATH . "functions/tools.php" );
// import code to access freeseat options
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

function freeseat_namesearch() {
	$x = $_REQUEST['user_name'];
	if (empty($x)) exit();
	$data2 = array(); 
	$data = fetch_all( "SELECT DISTINCT firstname, lastname, user_id FROM booking WHERE lastname LIKE '$x%' or firstname LIKE '$x%' " );
	foreach ( $data as $item ) {
		$data2[] = array( 
			'label' => $item['firstname']." ".$item['lastname'],
			'link' => $item['user_id'] 
		);
	}
	sys_log( print_r($data2,1) );
	wp_send_json( $data2 );	
	exit();
}

add_action( 'wp_ajax_freeseat_search', 'freeseat_namesearch' );
add_action( 'wp_ajax_nopriv_freeseat_search', 'freeseat_namesearch' );

