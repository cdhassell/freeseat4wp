<?php namespace freeseat;

/* Store and retrieve user data in the database */

function freeseat_plugin_init_login() {
	global $freeseat_plugin_hooks;
	
	$freeseat_plugin_hooks['seatmap_top']['login'] = 'login_stop';
	$freeseat_plugin_hooks['pay_page_top']['login'] = 'login_getdata';
	$freeseat_plugin_hooks['finish_end']['login'] = 'login_setdata';
	// init_language('login');    
	add_action( 'wp_enqueue_scripts', 'login_script' );
}

function login_script() {
	wp_enqueue_script(
		'popup-script',
		plugins_url( 'js/popup_script.js', __FILE__ ),
		array( 'jquery' )
	);
}

function login_stop() {
	// detects whether the current user is logged in 
	// and prevents the user from continuing 
	if ( !is_user_logged_in() ) {
		// FIXME display a warning
		echo '<div id="dialog" title="Please Login"><p>Please register and log in before making a ticket purchase.</p></div>';
		exit();
	}
}

function login_getdata() {
	// checks for user data saved in the usermeta table
	// and retrieves it to pre-fill the payment form
	$userid = get_current_user_id();
	if ( 0 == $userid ) return;
	$userdata = get_userdata( $userid );
	if ( FALSE===$userdata ) return;
	if (isset($userdata->email) && empty($_SESSION['email']))
		$_SESSION['email'] = $userdata->email;
	foreach( array( 
					'firstname', 
					'lastname', 
					'phone', 
					'address', 
					'postalcode', 
					'city', 
					'us_state', 
					'country' 
				) as $metakey ) {
		if ( empty( $_SESSION[ $metakey ] ) ) {
			$item = get_user_meta( $userid, "freeseat_$metakey", TRUE );
			if ( !empty( $item ) ) $_SESSION[ $metakey ] = $item;
		}
	}
}

function login_setdata() {
	// saves the user data to the usermeta table
	$userid = get_current_user_id();
	if ( 0 == $userid ) return;
	foreach( array( 
					'firstname', 
					'lastname', 
					'phone', 
					'address', 
					'postalcode', 
					'city', 
					'us_state', 
					'country' 
				) as $metakey ) {
		if ( !empty( $_SESSION[ $metakey ] ) ) {
			$item = $_SESSION[ $metakey ];
			add_user_meta( $userid, "freeseat_$metakey", $item, TRUE ); 
		}
	}
}

