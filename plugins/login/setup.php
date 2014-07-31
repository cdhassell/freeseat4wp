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
	$freeseat_plugin_hooks['book']['login'] = 'login_setuserid';
	init_language('login');    
}

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
	freeseat_query( "UPDATE booking SET user_id=$userid WHERE id=$bookid" );
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
	global $lang, $now, $pref_state_code, $pref_country_code, $currency, $post, $page_url;
	
	if (isset($_REQUEST['action'])) {
		// validate this request
		if (!admin_mode()) {
			if ( 
				$_REQUEST['user'] != get_current_user_id()  ||
				$_REQUEST['action'] == 'delete' && login_is_paid($_REQUEST['item']) ||
				$_REQUEST['action'] == 'print' && !login_is_paid($_REQUEST['item']) ||
				$_REQUEST['action'] == 'pay' && login_is_paid($_REQUEST['item']) ) {
				kaboom("Invalid action request");
				unset($_REQUEST['action']);
				unset($_REQUEST['item']);
			}
		}
	}
	if (isset($_REQUEST['action'])) {
		// take the action
		login_user_action( $_REQUEST['action'], $_REQUEST['item'] );
	}
	show_head();
	db_connect();
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
	<div id='multiCheck' >
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
		$spnames = array();
		if ( $ss ) {
			foreach ( $ss as $sh ) {
				$fulllist .= $comma . $sh[ 'id' ];
				$comma = ', ';
				$sp = get_spectacle( $sh['spectacleid'] );
				$spnames[ $sh['id'] ] = $sp[ 'name' ];
			}
		} 
		$cond = $and = "";
		if ( isset($fulllist) && !empty($fulllist) )
			$cond = "showid IN ($fulllist) and ";
		// get recent bookings and display in a list
		$ab = get_bookings( "$cond user_id=$user and groupid is NULL and state in (2,3,4)", "bookid desc" );
		if ( $ab ) {
			$total = 0; // total price of displayed elements
			$html  = ""; 
			foreach ( $ab as $b ) {
				$id = $b[ 'bookid' ];
				$st = $b[ 'state' ];
				$showid = $b['showid'];
				$spname = $spnames[ $showid ];
				$html .= '<tr><td>';
				$total = 0;
				$count = 0;
				$group = get_bookings( "booking.groupid=$id or booking.id=$id", "bookid desc" );
				$description = '';
				$sep = '';
				foreach ($group as $g) {
					$itemprice = get_seat_price( $g );
					$total += $itemprice;
					$count++;
					$description .= $sep . ((strpos($g['extra'], 'Table')===false) ? "Row {$g['row']} Seat {$g['col']}" : "Table {$g['row']}-{$g['col']}" );
					$sep = ', ';
				}	
				$html .= $id . "<td bgcolor='#ffffb0'>" . f_date($b['timestamp']).' '.f_time($b['timestamp']);
				$html .= '<td>' . htmlspecialchars( $spname );
				$html .= '<td bgcolor="#ffffb0">' . $currency . price_to_string( $total );
				$html .= '<td title="'.$description.'">' . $count . ' '.$lang['tickets'] ;
				$html .= '<td bgcolor="#ffffb0"> ' . f_state( $st ) . ' <td>';
				if ( ( $st == ST_BOOKED ) || ( $st == ST_SHAKEN ) ) {
					if ( $b[ 'payment' ] == PAY_CCARD )
						$exp = strtotime( $b[ 'timestamp' ] ) + 86400 * get_config( "paydelay_ccard" );
					else if ( $b[ 'payment' ] == PAY_POSTAL )
						$exp = sub_open_time( strtotime( $b[ 'timestamp' ] ), -86400 * get_config( "paydelay_post" ));
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
					// $html .= do_hook_concat( 'bookinglist_tablerow', $b );
				}
				$html .= login_page_buttons( $id, $st, $user );				
			}
			$headers = '<tr><th>' . $lang[ "bookid" ] . '<th>' . $lang[ "date" ] . '<th>' . $lang[ "show_name" ] . '<th>' . $lang["price"] . '<th>' . $lang['tickets'] . '<th>' . $lang['state'] . '<th>' . $lang[ "expiration" ];
			// if (admin_mode()) $headers .= do_hook_concat('bookinglist_tableheader');
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
 *  Helper function to display action buttons on the user account page
 */
function login_page_buttons( $id, $st, $user ) {
	global $lang, $post;
	// ordinary user can print or pay
	// admin user can print or delete
	$url = (( isset( $post ) ) ? get_permalink() : $_SERVER['PHP_SELF'].'?page=freeseat-user-menu' );
	$params = array( 
		'user' => $user,
		'action' => 'print',
		'item' => $id 
	);
	$qp = add_query_arg( $params, $url );
	$qd = add_query_arg( 'action', 'delete', $qp );
	$qy = add_query_arg( 'action', 'pay', $qp );
	$qa = add_query_arg( 'action', 'acknowledge', $qp );
	if (admin_mode()) {
		$html = "<td><span class='textbuttons'>";
		if ( $st == ST_PAID ) {
			$html .= "<a class='textbutton' href='$qp'>Print</a>";
		} else if ( $st == ST_BOOKED || $st == ST_SHAKEN ) {
			$html .= "&nbsp;<a class='textbutton' href='$qa'>".$lang['acknowledge']."</a>";
		}
		$html .= "<div id='confirmDialog' title='".$lang['confirmation']."'>".$lang['login_delete_confirm']."</div>";
		$html .= "&nbsp;<a class='textbutton confirmLink' href='$qd'>".$lang['DELETE']."</a>";
		
		$html .= "</span></td>";
	} else {
		$html = "<td><span class='textbuttons'>";
		if ( $st == ST_PAID ) {
			$html .= "<a class='textbutton' href='$qp'>Print</a>";
		} else if ( $st == ST_BOOKED || $st == ST_SHAKEN ) {
			$html .= "<a class='textbutton' href='$qy'>Pay</a>";
			$html .= "<div id='confirmDialog' title='".$lang['confirmation']."'>".$lang['login_delete_confirm']."</div>";
			$html .= "&nbsp;<a class='textbutton confirmLink' href='$qd'>".$lang['DELETE']."</a>";
		}	
		$html .= "</span></td>";
	}
	return $html;
}

/**
 *   Takes the action specified in $action for the ticket order $gid
 */
function login_user_action( $action, $gid ) {
	global $lang, $lockingtime, $page_url, $dompdf;
	$bookings = get_bookings("booking.id=$gid or booking.groupid=$gid","shows.date,shows.time,booking.id");
	if (!count($bookings)) return;
	 
	switch ($action) {
		case 'print':
			$page_url = ( (!admin_mode()) ? get_permalink() : $_SERVER['PHP_SELF'] );
			$page_url = add_query_arg( array( 'page' => 'freeseat-user-menu', 'action' => 'print', 'item' => $gid ), $page_url ); 
			login_setup_session( $bookings, $gid );
			$hide_tickets = do_hook_exists('ticket_prepare_override');
	  		foreach ($bookings as $n => $s) {
	    		do_hook_function('ticket_render_override', array_union($_SESSION,$s));
	  		}
	  		do_hook('ticket_finalise_override');
			if (!$hide_tickets) {
				do_hook('ticket_prepare');
				foreach ($bookings as $n => $s) {
	 				do_hook_function('ticket_render', array_union($_SESSION,$s));
				}
				do_hook('ticket_finalise');
			}
			// FIXME this is clumsy
			if (function_exists('pdf_tickets_cleanup')) pdf_tickets_cleanup();
			break;
		case 'pay':
			if ( isset($_SESSION["lastname"]) && !empty($_SESSION["lastname"]) && 
			isset($_SESSION["firstname"]) && !empty($_SESSION["firstname"]) && 
			isset($_SESSION["email"]) && !empty($_SESSION["email"]) && is_email_ok($_SESSION["email"])) {
				// we are ready to confirm and go
				$default_fsp = 4;
			} else {
				// need user details, go back 
				$default_fsp =3;
			}
			$fsp = ( isset( $_REQUEST['fsp'] ) ? $_REQUEST['fsp'] : $default_fsp );
			$page_url = ( (!admin_mode()) ? get_permalink() : $_SERVER['PHP_SELF'] );
			$page_url = add_query_arg( array( 'page' => 'freeseat-user-menu' ), $page_url ); 
			$page_url = replace_fsp( $page_url, $fsp );
			login_setup_session( $bookings, $gid );
			freeseat_switch( $fsp );
			exit;
			break;
		case 'delete':
			foreach ( $bookings as $b ) {
				set_book_status( $b, ST_DELETED );
			}
			break;
		case 'acknowledge':
			foreach ( $bookings as $b ) {
				set_book_status( $b, ST_PAID );
			}
			break;
	}
}

/** 
 *  Set up SESSION vars based on an array of bookings from the database
 */
function login_setup_session( $bookings, $gid ) {
	global $lockingtime;
	$showid = $bookings[0]["showid"];	
	$seats = array();
	$ninvite = 0; 
	$nreduced = 0;
	$_SESSION["until"] = time()+$lockingtime;
	foreach (array("firstname","lastname","phone","email", "payment", "address", "city", "us_state", "postalcode") as $n => $a) {
		if (isset($bookings[0][$a])) $_SESSION[$a] = make_reasonable($bookings[0][$a]);
	}
	foreach ($bookings as $i => $data ) {
		$seat = $data["seat"];
		$seats[$seat] = array( "id" => $seat, "theatre" => $data["theatreid"], "cnt" => 1 );
		foreach ( array("bookid", "row", "col", "extra", "zone", "class", "cat", "date", "time", "theatrename", "spectacleid", "showid", "x", "y" ) as $n => $a) {
			if (isset($data[$a]))  $seats[$seat][$a] = $data[$a];
		}
		$seats[$seat]['cnt'] = 1;
		if ($data['cat']==CAT_REDUCED) $nreduced++;
		if ($data['cat']==CAT_FREE) $ninvite++; 
	}
	$_SESSION["seats"] = $seats;
	$_SESSION["showid"] = $showid;
	$_SESSION["groupid"] = $gid;
	$_SESSION["ninvite"] = $ninvite;
	$_SESSION["nreduced"] = $nreduced;
	if (!isset($_SESSION["payment"])) $_SESSION["payment"]= PAY_CCARD;
}

function login_is_paid( $gid ) {
	return ( m_eval( "SELECT state from booking where id=$gid" ) == ST_PAID );
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

function freeseat_confirm_js() {
	wp_enqueue_script( 'confirm-script', plugins_url( 'confirm.js', __FILE__ ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-dialog' ) );
}

add_action( 'init', __NAMESPACE__ . '\\freeseat_confirm_js' );
