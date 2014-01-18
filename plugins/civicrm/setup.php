<?php namespace freeseat;

/*
FreeSeat for Wordpress integration with CiviCRM

Example from http://wiki.civicrm.org/confluence/display/CRMDOC41/WordPress+and+CiviCRM+Integration

require_once "INSERT-YOUR-WEB-ROOT-HERE/wp-content/plugins/civicrm/civicrm.settings.php";
require_once 'CRM/Core/Config.php';
$config = CRM_Core_Config::singleton( );
require_once 'api/api.php';
$contact = civicrm_api('Contact','Get',array('first_name' => 'Wool', 'last_name' => 'Man', 'version' =>3));
*/

if ( !function_exists( 'is_plugin_inactive' ) ) 
	include_once ( ABSPATH . '/wp-admin/includes/plugin.php' );
if ( is_plugin_inactive( 'civicrm/civicrm.php' ) ) 
	kaboom( "Aborting civicrm integration, civicrm not found" ); 

// FIXME make these configurable
define( 'CIVICRM_ACTIVITY_TYPE', 58 );
define( 'CIVICRM_GROUP1',     2 );	// newsletter subscribers
define( 'CIVICRM_GROUP2',     6 );	// ticket buyers
define( 'CIVICRM_EVENT_TYPE', 5 );	// type = performance
define( 'LOCAL_WP_ROOT', FS_PATH."../../../" );
define( 'DESCRIPTION_TEXT', "<p>For tickets please visit <a>".home_url()."</a></p>" );

function freeseat_plugin_init_civicrm() {
	global $freeseat_plugin_hooks;
	
	$freeseat_plugin_hooks['config_form']['civicrm'] = 'civicrm_config_form';
	$freeseat_plugin_hooks['finish_end']['civicrm'] = 'civicrm_sync';
	$freeseat_plugin_hooks['showedit_save']['civicrm'] = 'civicrm_showedit';
	$freeseat_plugin_hooks['config_db']['civicrm'] = 'civicrm_config_db';
}

function civicrm_config_form($form) {
	return config_form('plugins/civicrm/config-dist.php', $form);
}

/* 
 * Save session user data from ticket purchase to civicrm
 * Search for a matching contact, and if one is found, use it
 * If no match is found, create a new contact first
 * Stores ticket details in an activity posted to the contact
 * Contacts are joined to two groups specified above
 */
function civicrm_sync() {
	if ( is_plugin_inactive( 'civicrm/civicrm.php' ) ) { 
		// if civicrm is not found, don't tell the user but write to log
		sys_log("Aborting civicrm integration, civicrm not found" ); 
		return; 
	}	
	
	// get freeseat booking data from session
	$sh = get_show($_SESSION["showid"]); 
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
	$detail = "$sp_name $date #$groupid";
	  
	// Check for reservations we don't want to keep
	/*  comment this out for testing */
	if (strpos($firstname.' '.$lastname,'Disabled') !== FALSE ||
		strpos($firstname.' '.$lastname,'Office') !== FALSE ||
		strpos($firstname.' '.$lastname,'Reserved') !== FALSE ||
		strlen(trim($firstname.$lastname)) == 0) {
		return; 
	}
	
  	// now do it the WordPress way
  	require_once LOCAL_WP_ROOT."wp-content/plugins/civicrm/civicrm.settings.php";
	require_once 'CRM/Core/Config.php';
	$config = \CRM_Core_Config::singleton( );
	require_once 'api/api.php';
	
	// how many matching contacts?
	$contact_count=civicrm_api("Contact","getcount", array ('version' =>'3', 'first_name' => $firstname,
		'last_name' => $lastname, 'email' => $email));
	$cid = NULL;
	
	// if none found, create a contact then add an activity
	if ($contact_count == 0) {  
		$contact_create=civicrm_api("Contact","create", array ('version' =>'3', 'contact_type' =>'Individual', 
			'source' =>'Tickets', 'first_name' =>$firstname, 'last_name' =>$lastname, 'email' =>$email ));
		if ($contact_create['is_error'] == 0) {
			$cid = $contact_create['id'];
			sys_log( "Created $cid in civicrm for $groupid" );
			civicrm_api("Email","create", array('version' =>'3', 'contact_id' =>$cid, 'email' =>$email, 'location_type_id' =>'1'));
			civicrm_api("Address","create", array('version' =>'3', 'contact_id' =>$cid, 'location_type_id' =>'1', 'street_address' =>$street, 'city' =>$city, 'state_province' =>$us_state, 'postalcode' =>$postalcode));
			civicrm_api("Phone","create", array('version' =>'3', 'contact_id' =>$cid, 'phone' =>$phone));
			civicrm_api("GroupContact","create", array('version' =>'3', 'group_id' =>CIVICRM_GROUP1, 'contact_id' =>$cid));
			civicrm_api("GroupContact","create", array('version' =>'3', 'group_id' =>CIVICRM_GROUP2, 'contact_id' =>$cid));
			civicrm_api("Activity","create", array ('version' =>'3', 
				'activity_type_id' => CIVICRM_ACTIVITY_TYPE, 'source_contact_id' => $cid, 'activity_date_time' => $date,
				'status_id' => 2, 'details' => "Contact created for: $detail", 'subject' => "Ticket purchase $groupid"));
		} else sys_log( "Cannot create civicrm contact for $groupid: ".print_r($contact_create,1) );
	}
	
	// if one found, get the contact_id then add an activity
	else if ($contact_count == 1) {  
		$contact_get=civicrm_api("Contact","get", array ('version' =>'3', 'first_name' => $firstname,
			'last_name' => $lastname, 'email' => $email));
		if ($contact_get['is_error'] == 0) {
			$cid = $contact_get['id'];
			sys_log( "Found $cid in civicrm for $groupid ");
			civicrm_api("GroupContact","create", array('version' =>'3', 'group_id' =>CIVICRM_GROUP2, 'contact_id' =>$cid));
			civicrm_api("Activity","create", array ('version' =>'3', 
				'activity_type_id' => CIVICRM_ACTIVITY_TYPE, 'source_contact_id' => $cid, 'activity_date_time' => $date,
				'status_id' => 2, 'details' => $detail, 'subject' => "Ticket purchase $groupid"));  
		} else sys_log( "Cannot get civicrm contact for $groupid ".print_($contact_get,1) );
	}
	
	// if more than one, get the contact_id of the first one
	else {
		$contact_get=civicrm_api("Contact","get", array ('version' =>'3', 'first_name' => $firstname,
			'last_name' => $lastname, 'email' => $email));
		if ($contact_get['is_error'] == 0) {
			$cid = array_shift(array_keys($contact_get['values']));
			sys_log( "Selected $cid from multiple contacts in civicrm for $groupid " );
			if ($cid) {
				civicrm_api("GroupContact","create", array('version' =>'3', 'group_id' =>CIVICRM_GROUP2, 'contact_id' =>$cid));
				civicrm_api("Activity","create", array ('version' =>'3', 
					'activity_type_id' => CIVICRM_ACTIVITY_TYPE, 'source_contact_id' => $cid, 
					'activity_date_time' => $date,
					'status_id' => 2, 'details' => $detail, 'subject' => "Ticket purchase $groupid"));
			}
		} else sys_log( "Cannot get civicrm contact for $groupid ".print_r($contact_get,1) );  
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
	require_once LOCAL_WP_ROOT."wp-content/plugins/civicrm/civicrm.settings.php";
	require_once 'CRM/Core/Config.php';
	$config = \CRM_Core_Config::singleton( );
	require_once 'api/api.php';  
	
	// for each show, create a civievent if it does not exist
	foreach($dates as &$date) {
		if (isset($date['civicrm_id']) && $date['civicrm_id']) {
			// event exists, update it  
			$date['new'] = FALSE;
			$result=civicrm_api("Event","get", array ('version' =>'3', 
				'id' => $date['civicrm_id'], 'sequential' => 1));
			if ($result['is_error'] == 0) {
				$found = $result['values'][0];
				$found['version'] = '3';
				$found['title'] = $name;
				$found['summary'] = $desc;
				$found['start_date'] = $date['date'].' '.$date['time'];
				// FIXME assumes that all shows are 2 hours - should make that configurable
				$found['end_date'] = $date['date'].' '. date('H:i', strtotime("{$date['time']} +2 hours"));
				$found['event_type_id'] = CIVICRM_EVENT_TYPE;
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
			$result=civicrm_api("Event","create", array ('version' =>'3', 
				'is_public' => 1, 'is_online_registration' => 0, 'is_map' => 0,
				'is_active' => 1, 'is_show_location' => 0, 'requires_approval' => 0,
				'is_template' => 0, 'has_waitlist' => 0, 'is_pay_later' => 0, 'is_share' => 0,
				'title' => $name,
				'summary' => $desc,
				'description' => DESCRIPTION_TEXT,
				'start_date' => $date['date'].' '.$date['time'],
				'end_date' => $date['date'].' '. date('H:i', strtotime("{$date['time']} +2 hours")),
				'event_type_id' => CIVICRM_EVENT_TYPE      
			) );
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


