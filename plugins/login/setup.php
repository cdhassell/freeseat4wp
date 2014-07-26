<?php namespace freeseat;

/*  Require user to log in.  Store and retrieve user data in the database.
 *  Add a new admin page showing users, and link to their reservations.
 *  Add a new page to allow users to view their purchases, with a link on the main page.
 */

function freeseat_plugin_init_login() {
	global $freeseat_plugin_hooks;
	
	$freeseat_plugin_hooks['seatmap_hide_button']['login'] = 'login_stop';
	$freeseat_plugin_hooks['pay_page_top']['login'] = 'login_getdata';
	$freeseat_plugin_hooks['finish_end']['login'] = 'login_setdata';
	// init_language('login');    
}

function login_stop() {
	// detects whether the current user is logged in 
	// and prevents the user from continuing 
	if ( !is_user_logged_in() ) {
		// FIXME move text to a language file
		echo '<div id="freeseat-dialog" title="Login Required"><p>Please log in before making a ticket purchase.</p></div>';
		return true;
	} else {
		echo "<!-- popup dialog div goes here -->";
		return false;
	}
}

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

add_action( 'admin_menu', __NAMESPACE__ . '\\freeseat_users_menu' );

/**
 *  Adds a submenu item for the user lookup on the admin menu
 */
function freeseat_users_menu() {
	// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
	add_submenu_page( 'freeseat-admin', 'Users', 'Users', 'administer_freeseat', 'freeseat-users', __NAMESPACE__ . '\\freeseat_users' );
}

/**
 *   Creates a user account lookup page 
 *   Generalized for either user or admin use
 */
function freeseat_users() {
	global $lang, $bookings_on_a_page, $now, $pref_state_code, $pref_country_code, $currency, $post;

	show_head();
	if (!admin_mode()) {
		$user = get_current_user_id();
		$userlist_url = (( isset( $post ) ) ? get_permalink() : $_SERVER['PHP_SELF'].'?page=freeseat-users' );
	} else {
		if ( isset($_REQUEST['user']) ) $user = $_REQUEST['user'];
		$userlist_url = admin_url( 'admin.php?page=freeseat-users' );
		// allow admin user to search for user names and see bookings for anyone
		// this uses jquery autocomplete and the ajax callback below
		?>		
		<h2>User Search</h2>
		<form action="<?php echo $userlist_url; ?>" method="POST" name="namesearchform" id="namesearchform">
		<p>Start typing the first few letters of the name until your selection appears</p>
		<input id="namesearchInput" name="namesearchInput" placeholder = "Name" type="text" />
		<input id="submit_button" value = "Submit" type="button" />
		</form>	
		<?php	
	}
	?>
	<div id='response_area' >
	<?php
	if ( isset($user) ) {
		$u = array();
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
			$u[$metakey] = get_user_meta( $user, "freeseat_$metakey", TRUE );
		}
		// display user account details
		?>
		<div>
		<h3>Account Details</h3>
		<p class="main"><?php echo '<b>'.$lang['name'].': </b>'.$u['firstname'] .' '. $u['lastname']; ?></p> 
		<p class="main"><?php echo '<b>'.$lang['phone'].': </b>'.$u['phone']; ?>&nbsp;&nbsp;
		<?php echo '<b>'.$lang['email'].': </b>'.$u['email']; ?></p>
		<p class="main">
		<?php echo ' <b>'.$lang['address'].': </b>'.$u['address']; ?>
		<?php if (!empty($u['address'])) echo ','; ?>
		<?php echo ' '.$u['city']; ?>
			<?php if ($pref_state_code != "")  {  ?>
				<?php echo ' '.$u['us_state']; ?>
			<?php } ?>		
		<?php echo ' '.$u['postalcode']; ?>
		<?php if ($pref_country_code != "")  {  
			echo $lang["country"].' '.$u['country']; 
		} ?>
		</p>
		</div>
		<div>
		<h3>Recent Ticket Purchases</h3>
		<?php
		// limit this list to recent shows
		$ss = get_shows( "date >= CURDATE() - INTERVAL 1 week" );
		$fulllist = $comma = '';
		if ( $ss ) {
			foreach ( $ss as $sh ) {
				$fulllist .= $comma . $sh[ 'id' ];
				$comma = ', ';
			}
		} 
		$cond = $and = "";
		if ( isset($fulllist) && !empty($fulllist) )
			$cond = "showid IN ($fulllist) and ";
		// get recent bookings and display in a list
		$ab = get_bookings( "$cond user_id=$user", "bookid desc", 0, $bookings_on_a_page );
		if ( $ab ) {
			$total = 0; // total price of displayed elements
			$html  = ""; 
			foreach ( $ab as $b ) {
				$id = $b[ 'bookid' ];
				$st = $b[ 'state' ];
				$html .= '<tr><td>';
				$itemprice = get_seat_price( $b );
				if ( $st != ST_PAID )
					$total += $itemprice;
				$url = admin_url( 'admin.php?page=freeseat-admin&fsp='.PAGE_SEATS.'&showid=' . $b[ 'showid' ] . '&amp;bookinglist');
				// TODO get URL for non-admin user
				$html .= $id . "<td bgcolor='#ffffb0'><a href='$url'>" . $b[ 'date' ] . ' ' . f_time( $b[ 'time' ] ) . '</a><td>' . ( $b[ 'row' ] == -1 ? '' : htmlspecialchars( $b[ 'col' ] ) . ', ' . $lang[ "row" ] . ' ' . htmlspecialchars( $b[ 'row' ] ) . ' ' ) . '(' . htmlspecialchars( $b[ 'zone' ] ) . ')' . '<td bgcolor="#ffffb0">' . f_cat( $b[ 'cat' ] ) . " (" . $currency . price_to_string( $itemprice ) . ")" . '<td> ' . f_state( $st ) . ' <td bgcolor="#ffffb0">';
				if ( ( $st == ST_BOOKED ) || ( $st == ST_SHAKEN ) ) {
					if ( $b[ 'payment' ] == PAY_CCARD )
						$exp = strtotime( $b[ 'timestamp' ] ) + 86400 * $c[ "paydelay_ccard" ];
					else if ( $b[ 'payment' ] == PAY_POSTAL )
						$exp = sub_open_time( strtotime( $b[ 'timestamp' ] ), -86400 * $c[ "paydelay_post" ] );
					else {
						$exp = FALSE;
						$html .= '<i>' . $lang[ "none" ] . '</i>';
					}
					if ( $exp !== FALSE ) {
						$delta = $exp - $now; 
						if ( $delta < 0 )
							$html .= $lang[ "expired" ];
						else if ( $delta < 5400 )
							$html .= sprintf( $lang[ "in" ], ( (int) ( $delta / 60 ) ) . ' ' . $lang[ "minute" ] );
						else if ( $delta < 129600 )
							$html .= sprintf( $lang[ "in" ], ( (int) ( $delta / 3600 ) ) . ' ' . $lang[ "hour" ] );
						else
							$html .= sprintf( $lang[ "in" ], ( (int) ( $delta / 86400 ) ) . ' ' . $lang[ "day" ] );
					}
				} else 
					$html .= '<i>' . $lang[ "none" ] . '</i>';
				if (admin_mode()) {
					$html .= do_hook_concat( 'bookinglist_tablerow', $b );
				} else {
					// TODO add print/pay/delete buttons
				}
			}
			$headers = '<tr><th>' . $lang[ "bookid" ] . '<th>' . $lang[ "date" ] . '<th>' . $lang[ "col" ] . '<th>' . $lang[ "cat" ] . '<th>' . $lang['state'] . '<th>' . $lang[ "expiration" ];
			if (admin_mode()) $headers .= do_hook_concat('bookinglist_tableheader');
			?>
			<table cellspacing=0 cellpadding=4 border=0 class="bookinglist">
			<?php echo $headers . $html; ?>
			</table>
			<?php
		} else {
			echo '<p class="warning">' . $lang[ "warn-nomatch" ] . '</p>';
		}   
	}
	?>
	</div>
	</div>
	<?php
	show_foot(); 
}

/**
 *  AJAX callback function for user name lookup
 */
function freeseat_namesearch() {
	$x = $_REQUEST['term'];
	sys_log( "Name search called with $x" );
	if (empty($x)) exit();
	$data2 = array(); 
	$userlist_url = admin_url( 'admin.php?page=freeseat-users' );
	$data = fetch_all( "SELECT DISTINCT firstname, lastname, user_id FROM booking WHERE lastname LIKE '$x%' or firstname LIKE '$x%' " );
	foreach ( $data as $item ) {
		$data2[] = array( 
			'label' => $item['firstname']." ".$item['lastname'],
			'link' => $userlist_url . '&user=' . $item['user_id'],
		);
	}
	sys_log( print_r($data2,1) );
	$response = $_GET["callback"] . "(" . json_encode($data2) . ")";
    echo $response;
	exit();
}

add_action( 'wp_ajax_freeseat_namesearch_action', __NAMESPACE__ . '\\freeseat_namesearch' );
add_action( 'wp_ajax_nopriv_freeseat_namesearch_action', __NAMESPACE__ . '\\freeseat_namesearch' );

/**
 *  Add a page for viewing the user account and bookings
 */
function freeseat_user_tickets() {
	$userid = get_current_user_id();
	if ( 0 == $userid ) return;
	add_users_page('My Tickets', 'My Tickets', 'read', 'freeseat-user-menu', __NAMESPACE__ . '\\freeseat_users');
}

add_action( 'admin_menu', __NAMESPACE__ . '\\freeseat_user_tickets');

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

add_action('admin_bar_menu', __NAMESPACE__ . '\\freeseat_customize_toolbar', 999); 

