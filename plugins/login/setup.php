<?php namespace freeseat;

/*  Store and retrieve user data in the database.
 *  Add a new admin page showing users, and link to their reservations.
 *  Add a new page to allow users to view their purchases, with a link on the main page.
 *
 *  Edit: Removed forced log in, now it is optional.  
 */

add_action( 'admin_menu', __NAMESPACE__ . '\\freeseat_user_tickets');
add_action( 'admin_bar_menu', __NAMESPACE__ . '\\freeseat_customize_toolbar', 999); 
add_action( 'init', __NAMESPACE__ . '\\freeseat_confirm_js' );


function freeseat_plugin_init_login() {
	global $freeseat_plugin_hooks;
	
	// $freeseat_plugin_hooks['seatmap_hide_button']['login'] = 'login_stop';
	$freeseat_plugin_hooks['pay_page_top']['login'] = 'login_getdata';
	$freeseat_plugin_hooks['finish_end']['login'] = 'login_setdata';
	$freeseat_plugin_hooks['book']['login'] = 'login_setuserid';
	init_language('login');    
}

/*
function login_stop() {
	// detects whether the current user is logged in 
	// and prevents the user from continuing 
	global $lang;
	if ( !is_user_logged_in() ) {
		echo '<div id="freeseat-dialog" title="'.$lang['login_required'].'"><p></p>'.$lang['login_reminder'].'</div>';
		return true;
	} else {
		echo "<!-- popup dialog div goes here -->";
		return false;
	}
}
*/

function login_getdata() {
	// checks for user data saved in the usermeta table
	// and retrieves it to pre-fill the payment form
	$userid = get_current_user_id();
	if ( 0 == $userid ) return;
	$userdata = get_userdata( $userid );
	if ( FALSE===$userdata ) return;
	foreach( array( 
					'firstname', 
					'lastname', 
					'email',
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
					'email',
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

function login_setuserid( $bookid ) {
	// inserts the wordpress user id into the booking record
	$userid = get_current_user_id();
	if ( 0 == $userid ) return;
	freeseat_query( "UPDATE booking SET user_id=$userid WHERE id=$bookid" );
}

/**
 *  Add a page for viewing the user account and bookings
 */
function freeseat_user_tickets() {
	$userid = get_current_user_id();
	if ( 0 == $userid ) return;
	add_users_page('My Tickets', 'My Tickets', 'read', 'freeseat-user-menu', __NAMESPACE__ . '\\freeseat_render_list');
}

/**
 *  Add a link to the user account page to the toolbar
 */
function freeseat_customize_toolbar($wp_toolbar){
	$userid = get_current_user_id();
	if ( 0 == $userid ) return;
	$wp_toolbar->add_node( array(
		'id' => 'freeseat_user_link',
		'title' => 'My Tickets',
		'parent' => false, 
		'href' => admin_url( 'profile.php?page=freeseat-user-menu' )
	) );
}

function freeseat_confirm_js() {
	wp_enqueue_script( 'confirm-script', plugins_url( 'confirm.js', __FILE__ ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-dialog' ) );
}


