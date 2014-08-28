<?php namespace freeseat;

/*
FreeSeat for Wordpress integration with CiviCRM
On show creation: creates a new CiviEvent and records event ID 
On ticket sale: searches for a contact by name.  If not found,
a contact is created.  The purchaser is recorded as an 
event participant. 

Based on the example of Wordpress integration from 
http://wiki.civicrm.org/confluence/display/CRMDOC41/WordPress+and+CiviCRM+Integration

version 2
*/

if ( !function_exists( 'is_plugin_inactive' ) ) 
	include_once ( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_inactive( 'civicrm/civicrm.php' ) ) 
	kaboom( "Aborting civicrm integration, civicrm not found" ); 

// FIXME make these configurable
define( 'PARTICIPANT_ROLE',	1 );	// attendee
define( 'FINANCIAL_TYPE',	4 );	// event fee
define( 'EVENT_TYPE',		5 );	// performance
define( 'DESCRIPTION_TEXT', "<p>For tickets please visit <a>".home_url()."</a></p>" );

function freeseat_plugin_init_civicrm() {
	global $freeseat_plugin_hooks;
	
	$freeseat_plugin_hooks['finish_end']['civicrm'] = 'civicrm_sync';
	$freeseat_plugin_hooks['showedit_save']['civicrm'] = 'civicrm_showedit';
	$freeseat_plugin_hooks['config_db']['civicrm'] = 'civicrm_config_db';
}

/* 
 *  Save session user data from ticket purchase to civicrm
 *  Search for a matching contact, and if one is found, use it
 *  If no match is found, create a new contact first
 *  
 */
function civicrm_sync() {
	global $currency, $post;
	
	if ( is_plugin_inactive( 'civicrm/civicrm.php' ) ) { 
		// if civicrm is not found, don't tell the user but write to log
		sys_log("Aborting civicrm integration, civicrm not found" ); 
		return; 
	}	
	
	// get freeseat booking data from session
	$showid = $_SESSION["showid"];
	$sh = get_show( $showid ); 
	$spectacle = get_spectacle($sh["spectacleid"]);
	$sp_name = $spectacle['name'];
	$date = f_date($sh["date"]) . " " . f_time($sh["time"]);
	$firstname = $_SESSION['firstname'];
	$lastname = $_SESSION['lastname'];
	$street = $_SESSION['address'];
	$city = $_SESSION['city'];
	$us_state = $_SESSION['us_state'];
	$postalcode = $_SESSION['postalcode'];
	$groupid = $_SESSION['groupid'];
	$phone = $_SESSION['phone'];
	$email = $_SESSION['email'];
	$amount = get_total();
	$amtstr = $currency . price_to_string( $amount );
	$detail = "$sp_name on $date ticket #$groupid for $amtstr";
	sys_log("Recording ticket sale to $groupid for $amtstr ");
	$eventid = m_eval( "SELECT civicrm_id FROM shows WHERE id=$showid" );
	$detail .= ( isset($post) ? " at ".get_page_link() : '' );
	// Check for reservations we don't want to keep
	if (strpos($firstname.' '.$lastname,'Disabled') !== FALSE ||
		strpos($firstname.' '.$lastname,'Office') !== FALSE ||
		strpos($firstname.' '.$lastname,'Reserved') !== FALSE ||
		strlen(trim($firstname.$lastname)) == 0) {
		return; 
	}
	
  	// now do it the WordPress way
  	require_once ABSPATH."wp-content/plugins/civicrm/civicrm.settings.php";
	require_once 'CRM/Core/Config.php';
	$config = \CRM_Core_Config::singleton( );
	require_once 'api/api.php';
	
	// check if we have a saved contact id for the logged in user
	$cid = civicrm_getvalue('civicrm-contactid');
	if ($cid) { 
		// if so, use it
		$contact_count = 1;
	} else {
		// otherwise, search for contact by name
		$contact_count = civicrm_api( "Contact", "getcount", 
			array (
				'version' =>'3', 
				'first_name' => $firstname,
				'last_name' => $lastname, 
				'email' => $email 
			) 
		);
		$cid = NULL;
	} 
	// if none found, create a contact using chained API calls
	if ($contact_count == 0) {  
		$params = array (
			'version' =>'3', 
			'contact_type' =>'Individual', 
			'source' =>'Tickets', 
			'first_name' =>$firstname, 
			'last_name' =>$lastname, 
			'email' =>$email,
			'api.address.create' => array(
				'location_type_id' =>'1', 
				'street_address' =>$street, 
				'city' =>$city, 
				'state_province' =>$us_state, 
				'postalcode' =>$postalcode,
			),
 			'api.phone.create' => array(
				'phone' => $phone,
				'location_type_id' => 1,
				'phone_type_id' => 1,
			),			
			'api.participant' => array(
				'event_id' => $eventid,
				'status_id' => 1,
				'role_id' => PARTICIPANT_ROLE,
				'note' => $detail,
				// 'format.only_id' => 1,
			)
		);
		/*  We could record the money transaction but that is too complex
			Requires setup of price sets for ticket prices
		if ( isset( $amtstr ) ) {
			$params[ 'api.contribution.create' ] = array(
				'financial_type_id' => FINANCIAL_TYPE,
				'total_amount' => $amtstr,
				'format.only_id' => 1,
				'receive_date' => date("Y-m-d H:i:s"),
				'payment_instrument_id' => 1,
				'invoice_id' => $groupid,
				'source' => 'Tickets',
				'contribution_status_id' => 1,				
			);
			$params[ 'api.participant_payment.create' ] = array(
				'contribution_id' => '$value.api.contribution.create',
				'participant_id' => '$value.api.participant'
			);
		}
		*/
		$contact_create=civicrm_api("Contact","create", $params);
		if ($contact_create['is_error'] == 0) {
			$cid = $contact_create['id'];
			sys_log( "Created $cid in civicrm for $groupid" );
		} else sys_log( "Cannot create civicrm contact for $groupid: ".print_r($contact_create,1) );
	}
	// if one/more is found, get the contact_id then add a participant
	else {
		if (!$cid) { // skip the search if we have the id already
			$params = array (
				// search by name and email
				'version' =>'3', 
				'first_name' => $firstname,
				'last_name' => $lastname, 
				'email' => $email
			);
			$contact_get=civicrm_api( "Contact", "get", $params );
			if ($contact_get['is_error'] == 0) {
				// how many did we find?
				if ($contact_count == 1) {  
					$cid = $contact_get['id'];
					sys_log( "Found $cid in civicrm for $groupid ");
				} else {
					// if more than one, take the first one
					$cid = array_shift(array_keys($contact_get['values']));
					sys_log( "Selected $cid from multiple contacts in civicrm for $groupid " );
				}
			} else {
				sys_log( "Cannot get civicrm contact for $groupid ".print_($contact_get,1) );
				return;
			}
		}
		// at this point we have a contact id
		$params = array (
			'version' =>'3',
			'contact_id' => $cid,
			'event_id' => $eventid,
			'status_id' => 1,
			'role_id' => PARTICIPANT_ROLE,
			'note' => $detail,
		);
		/*
		if ( isset( $amtstr ) ) {
			$params[ 'api.contribution.create' ] = array(
				'financial_type_id' => FINANCIAL_TYPE,
				'total_amount' => $amtstr,
				'format.only_id' => 1,
				'receive_date' => date("Y-m-d H:i:s"),
				'payment_instrument_id' => 1,
				'invoice_id' => $groupid,
				'source' => 'Tickets',
				'contribution_status_id' => 1,				
			);
			$params[ 'api.participant_payment.create' ] = array(
				'contribution_id' => '$value.api.contribution.create',
				'participant_id' => '$value.api.participant'
			);
		}
		*/
		// make a participant record
		$result = civicrm_api( "participant", "create", $params );
		if ($result['is_error'] != 0)  // if there was an error then delete the id
			civicrm_delvalue( 'civicrm-contactid' );
		else  // otherwise save the contact id for next time
			civicrm_setvalue( 'civicrm-contactid', $cid );
	}
	return;
}

/*
 * Save show creation data to civicrm event
 * Does the show have a civicrm_id? If so use it
 * If not, create the event, capture the id and save it to civicrm_id
 * Events will be active and public
 */
function civicrm_showedit($spec) {
	if ( is_plugin_inactive( 'civicrm/civicrm.php' ) ) 
		// only the administrator should be doing this, so we will tell him
		return kaboom( "Aborting civicrm integration, civicrm not found" ); 
	
	// get freeseat data
	$name = $spec['name']; 
	$desc = $spec['description']; 
	$dates = get_shows("spectacle = ".$spec['id']);
	$n = 0;
	foreach($dates as &$date) {
		// is there a civicrm_id?
		$sql = "SELECT civicrm_id from shows WHERE id={$date['id']}";
		if ($value = m_eval($sql)) {
			$date['civicrm_id'] = $value;
			$n++;
		}
	}
	sys_log("Civicrm found ".count($dates)." shows of $name, $n with IDs");
	
	// now do it the WordPress way
	require_once ABSPATH."wp-content/plugins/civicrm/civicrm.settings.php";
	require_once 'CRM/Core/Config.php';
	$config = \CRM_Core_Config::singleton( );
	require_once 'api/api.php';  
	
	// for each show, create a civievent if it does not exist
	foreach($dates as &$date) {
		if (isset($date['civicrm_id']) && $date['civicrm_id']) {
			// event exists, update it  
			$date['new'] = FALSE;
			$result=civicrm_api("Event","get", 
				array (
					'version' =>'3', 
					'id' => $date['civicrm_id'], 
					'sequential' => 1
				) 
			);
			if ($result['is_error'] == 0 && isset($result['values'][0]) ) {
				$found = $result['values'][0];
				$found['version'] = '3';
				$found['title'] = $name;
				$found['summary'] = mysql_real_escape_string( $desc );
				$found['start_date'] = $date['date'].' '.$date['time'];
				// FIXME assumes that all shows are 2 hours - should make that configurable
				$found['end_date'] = $date['date'].' '. date('H:i', strtotime("{$date['time']} +2 hours"));
				$found['event_type_id'] = EVENT_TYPE;
				$result = civicrm_api("Event","create",$found);
				if ( $result['is_error'] == 0 )
					sys_log("Update of civievent {$date['civicrm_id']} OK. ");
				else {
					sys_log("Update civicrm {$date['civicrm_id']} failed: ".$result['error_message']);
				}
			} else {
				sys_log("Civirm find id {$date['civicrm_id']} failed: ".$result['error_message']);
			}
		} else {
			// create new event
			$date['new'] = TRUE;
			$params = array (
				'version' =>'3',
				'is_public' => 1, 
				'is_online_registration' => 0, 
				'is_map' => 0,
				'is_active' => 1, 
				'is_show_location' => 0, 
				'requires_approval' => 0,
				'is_template' => 0, 
				'has_waitlist' => 0, 
				'is_pay_later' => 0, 
				'is_share' => 0,
				'title' => $name,
				'summary' => DESCRIPTION_TEXT,
				'description' => $desc,
				'start_date' => $date['date'].' '.$date['time'],
				'end_date' => $date['date'].' '. date('H:i', strtotime("{$date['time']} +2 hours")),
				'event_type_id' => EVENT_TYPE      
			);
			$result=civicrm_api( "Event", "create", $params );
			if ($result['is_error'] == 0) {
				$date['civicrm_id'] = $result['id'];
				sys_log("Created {$date['civicrm_id']} in civicrm. ");
			} else {
				sys_log("Civicrm create failed: ".$result['error_message']);
			}
		}
	}
	
	// record civicrm_id in shows table
	foreach ($dates as &$date) {
		if (isset($date['civicrm_id']) && ($date['civicrm_id']) && ($date['new'])) {
			$sql = "UPDATE shows set civicrm_id={$date['civicrm_id']} WHERE id={$date['id']}";
			freeseat_query($sql);
		}  
	}
	return;
}

function civicrm_config_db($user) {
	return config_checksql_for('plugins/civicrm/setup.sql', $user);
}

function civicrm_getvalue( $metakey ) {
	// retrieves an arbitrary value from the usermeta table
	$userid = get_current_user_id();
	if ( 0 == $userid ) return FALSE;
	if ( empty( $metakey ) ) return FALSE;
	return get_user_meta( $userid, "freeseat_$metakey", TRUE );
}

function civicrm_setvalue( $metakey, $metavalue ) {
	// saves an arbitrary value to the usermeta table
	$userid = get_current_user_id();
	if ( 0 == $userid ) return FALSE;
	if ( empty( $metakey ) ) return FALSE;
	return add_user_meta( $userid, "freeseat_$metakey", $metavalue, TRUE ); 
}

function civicrm_delvalue( $metakey ) {
	// deletes an arbitrary value from the usermeta table
	$userid = get_current_user_id();
	if ( 0 == $userid ) return FALSE;
	if ( empty( $metakey ) ) return FALSE;
	return delete_user_meta( $userid, "freeseat_$metakey" );
}

