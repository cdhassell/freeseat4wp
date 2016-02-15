<?php namespace freeseat;

/*
FreeSeat for Wordpress integration with CiviCRM
On show creation: creates a new CiviEvent and records event ID 
On ticket sale: searches for a contact by name.  If not found,
a contact is created.  The purchaser is recorded as an 
event participant. 

Based on the example of Wordpress integration from 
http://wiki.civicrm.org/confluence/display/CRMDOC41/WordPress+and+CiviCRM+Integration

version 3:
Wordpress plugin with more links to Civicrm API
Use civicrm workflow to capture payment
No freeseat payment processor plugins required

Post-checkout hook:
Attach ticket PDF to confirmation email

Event setup: OK
Write dates, times to civicrm via API: OK
Write ticket prices to price sets: OK
Maintain showid to eventid link: OK
Adding contribution transact call: WIP

Registration maintenance:
Handle delete or edit registration
Use freeseat POS interface
http://localhost/wordpress/wp-admin/admin.php?page=CiviCRM&q=civicrm%2Fparticipant%2Fadd&reset=1&action=add&context=standalone&eid=18
http://localhost/wordpress/wp-admin/admin.php?page=CiviCRM&q=civicrm%2Fevent%2Finfo&id=18&reset=1


Comments:
Price set must have recognizable tags by cat and class
Must allow multiple signups on one email
No ccard table in freeseat
Booking table - Booking links to participant

Info page:
http://localhost/wordpress/civicrm?page=CiviCRM&q=civicrm/event/info&reset=1&id=16

Registration page:
http://localhost/wordpress/civicrm?page=CiviCRM&q=civicrm/event/register&reset=1&id=16

Sample
http://localhost/wordpress/?page=CiviCRM&q=civicrm/event/register&page_id=7&id=16&reset=1

*/

if ( !function_exists( 'is_plugin_inactive' ) ) 
	include_once ( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_inactive( 'civicrm/civicrm.php' ) ) 
	kaboom( "Aborting civicrm integration, civicrm not found" ); 

// FIXME make these configurable
define( 'PARTICIPANT_ROLE',	1 );	// attendee
define( 'FINANCIAL_TYPE',	4 );	// event fee
define( 'EVENT_TYPE',		5 );	// performance
define( 'SHOWLEN',			2 );	// std show length in hours

function freeseat_plugin_init_civicrm() {
	global $freeseat_plugin_hooks;
	
	$freeseat_plugin_hooks['ccard_exists']['civicrm'] = 'civicrm_true';
	$freeseat_plugin_hooks['ccard_paymentform']['civicrm'] = 'civicrm_sync';
	$freeseat_plugin_hooks['showedit_save']['civicrm'] = 'civicrm_showedit';
	$freeseat_plugin_hooks['ccard_confirm_button']['civicrm'] = 'civicrm_form';
	$freeseat_plugin_hooks['confirm_process']['civicrm'] = 'civicrm_process'; 	
	$freeseat_plugin_hooks['kill_booking_done']['civicrm'] = 'civicrm_cleanup';
	init_language('civicrm');	
}

function civicrm_true($void) {
	return TRUE;
}

/* 
 *  Save session user data from ticket purchase to civicrm
 *  Search for a matching contact, and if one is found, use it
 *  If no match is found, create a new contact first
 *  Called on do_hook('ccard_paymentform')
 */
function civicrm_sync() {
	global $currency, $post;
	
	if ( is_plugin_inactive( 'civicrm/civicrm.php' ) ) { 
		// if civicrm is not found, don't tell the user but write to log
		sys_log("Aborting civicrm integration, civicrm not found" ); 
		return; 
	}	
	sys_log("starting civicrm_sync");
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
	$country = $_SESSION['country'];
	$amount = get_total();
	$amtstr = $currency . price_to_string( $amount );
	$detail = "$sp_name on $date ticket #$groupid for $amtstr";
	sys_log("Recording ticket sale to $groupid for $amtstr ");
	$eventid = m_eval( "SELECT civicrm_id FROM shows WHERE id=$showid" );
	$detail .= ( isset($post) ? " at ".get_page_link() : '' );
	$seats = $_SESSION['seats'];
	$prices = get_spec_prices( $sh["spectacleid"] );
	// Check for reservations we don't want to keep
	if (strpos($firstname.' '.$lastname,'Disabled') !== FALSE ||
		strpos($firstname.' '.$lastname,'Office') !== FALSE ||
		strpos($firstname.' '.$lastname,'Reserved') !== FALSE ||
		strlen(trim($firstname.$lastname)) == 0) {
		return; 
	}
	// summarize the seats array into price field amounts
	$field_counts = array();
	foreach ($seats as $seat) {
		$field_id = $prices[$seat['class']][$seat['cat']]['fid'];
		if (!isset($field_counts[$field_id])) {
			$field_counts[$field_id] = array('total' => 0, 'price' => 0, 'count' => 0 );
		}
		$field_counts[$field_id]['total'] += $prices[$seat['class']][$seat['cat']]['amt'];
		$field_counts[$field_id]['price'] = $prices[$seat['class']][$seat['cat']]['amt'];
		$field_counts[$field_id]['count']++;
	}

  	// now do it the WordPress way
  	require_once ABSPATH."wp-content/plugins/civicrm/civicrm.settings.php";
	require_once 'CRM/Core/Config.php';
	$config = \CRM_Core_Config::singleton( );
	require_once 'api/api.php';
	sys_log("booting civicrm");
	// check if we have a saved contact id for the logged in user
	$cid = civicrm_getvalue('civicrm-contactid');
	if ($cid) { 
		// if so, use it
		$contact_count = 1;
	} else {
		// otherwise, search for contact by name
		$contact_count = civicrm_api3( "Contact", "getcount", 
			array (
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
			)
		);

		$contact_create=civicrm_api3("Contact","create", $params);
		if ($contact_create['is_error'] == 0) {
			$cid = $contact_create['id'];

			sys_log( "Created $cid in civicrm for $groupid" );
		} else sys_log( "Cannot create civicrm contact for $groupid: ".print_r($contact_create,1) );
	}
	// if one/more is found, get the contact_id 
	else {
		if (!$cid) { // skip the search if we have the id already
			$params = array (
				// search by name and email
				'first_name' => $firstname,
				'last_name' => $lastname, 
				'email' => $email
			);
			$contact_get=civicrm_api3( "Contact", "get", $params );
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
				civicrm_delvalue( 'civicrm-contactid' );
				return;
			}
		}
	}
	// at this point we have a contact id
	sys_log("Found contact $cid");
	// save the contact id for next time
	civicrm_setvalue( 'civicrm-contactid', $cid );
	
	if ( isset( $amtstr ) ) {
		// first get the payment processor id
		$result = civicrm_api3('PaymentProcessor', 'getsingle', array(
			'sequential' => 1,
			'is_active' => 1,
			// 'is_default' => 1,
			'is_test' => 1,  // test mode?
			'billing_mode' => 3,
		));
		$payment_processor = $result['id'];
		if (empty($payment_processor)) return FALSE;
		
		// now get the payment through the contribution transact API
		$locationTypes = \CRM_Core_PseudoConstant::get('CRM_Core_DAO_Address', 'location_type_id', array(), 'validate');
		$bltID = array_search('Billing', $locationTypes);
		$contributionparams = array();
		$isrecur = FALSE;
		$contributionparams = array(
			"billing_first_name" => $firstname,
			"first_name" => $firstname,
			"billing_last_name" => $lastname,
			"last_name" => $lastname,
			"billing_street_address-{$bltID}" => $street,
			"street_address" => $street,
			"billing_city-{$bltID}" => $city,
			"city" => $city,
			"billing_country_id-{$bltID}" => $country,
			"country_id" => $country,
			"country" => $country,
			"billing_state_province_id-{$bltID}" => $us_state,
			"state_province_id" => $us_state,
			"state_province" => \CRM_Core_PseudoConstant::stateProvince($us_state),
			"billing_postal_code-{$bltID}" => $postalcode,
			"postal_code" => $postalcode,
			"year" => $_SESSION["civicrm_card_expyear"],
			"month" => $_SESSION["civicrm_card_expmonth"],
			"email" => $email,
			"contribution_page_id" => "",
			"event_id" => $eventid,  // is this needed?
			"contribution_recur_id" => '',
			"payment_processor_id" => $payment_processor,
			"is_test" => TRUE,   // $_SESSION['isTest'],
			"is_pay_later" => FALSE, 
			"total_amount"=> $amtstr,
			"invoice_id" => $groupid,
			"financial_type_id" => FINANCIAL_TYPE,
			"currency" => 'USD',
			"skipLineItem" => 0,
			"skipRecentView" => 1,
			"is_email_receipt" => 1,
			"contact_id" => $cid,
			"source" => "Tickets",
			"currencyID" => 'USD',
			"ip_address" => $_SERVER['REMOTE_ADDR'],
			"payment_action" => "Sale",
			'api.ParticipantPayment.create' => array (
				'contact_id' => $cid,
				'event_id' => $eventid,
				'status_id' => 1,
				'role_id' => PARTICIPANT_ROLE,
				'note' => $detail,
				'contribution_id' => '$value.contribution_id',
			),
		);
		$priceparams = array();
		// now get the price set & price field data & create the line items
		foreach ($field_counts as $field_id => $fid) {
			$priceparams[] = array(
				'price_field_id' => array( 0 => $field_id ),
				'qty' => $fid['count'],
				'line_total' => $fid['total'],
				'unit_price' => $fid['price'],
				'financial_type_id' => FINANCIAL_TYPE,
			);
		}
		if (!empty($priceparams)) $contributionparams['api.line_item.create'] = $priceparams;
			
		$contributionparams['credit_card_number'] = $_SESSION['civicrm_card_account'];
		$contributionparams['cvv2'] = $_SESSION['civicrm_card_cvv2'];
		$contributionparams['credit_card_type'] = $_SESSION['civicrm_card_type'];
		//***************************************************************************
		//call transact api
		sys_log("Calling api with contribution record: ".print_r($contributionparams,1));
		try {
			$result = civicrm_api3('Contribution', 'transact', $contributionparams);
		}
		catch (CiviCRM_API3_Exception $e) {
			$error = $e->getMessage();
			sys_log( "Error in contribution transact ". $error );
			return $error;
		}
		//**************************************************************************
		// from wf_crm_webform_postprocess.inc webform-civicrm
		if ($result['error']) {
			return $result['error'];
		} else if ($result){
			$contributionID = $result['id'];
			sys_log("Contribution result = ".print_r($result,1));
			// Send receipt
			civicrm_api3('contribution', 'sendconfirmation', array('id' => $contributionID) + $contributionparams);
			
			$ok = process_ccard_transaction( $groupid, $contributionID, $amtstr );
			sys_log("Paypal process success");			
			
			return $result;
		}
		// make a participant record $result = civicrm_api3( "participant", "create", $params ); $pid = $result['participant_id'];
	}
	return $pid;
}

/*
 * Save show creation data to civicrm event
 * Does the show have a civicrm_id? If so use it
 * If not, create the event, capture the id and save it to civicrm_id
 * Events will be active and public
 */
function civicrm_showedit($spec) {
	global $lang;
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
	$prices = get_spec_prices( $spec['id'] );
	sys_log("Civicrm found ".count($dates)." shows of $name, $n with IDs");
	
	// now do it the WordPress way
	require_once ABSPATH."wp-content/plugins/civicrm/civicrm.settings.php";
	require_once 'CRM/Core/Config.php';
	$config = \CRM_Core_Config::singleton( );
	require_once 'api/api.php';  
	
	// for each show, create a civievent if it does not exist
	foreach($dates as &$date) {
		if (isset($date['civicrm_id']) && $date['civicrm_id']) {
			// if event exists, update it  
			$date['new'] = FALSE;
			$result=civicrm_api3("Event","get", 
				array (
					'id' => $date['civicrm_id'], 
					'sequential' => 1
				) 
			);
			if ($result['is_error'] == 0 && ($result['count'] > 0)) {
				// we got  one, update it with new values
				$found = $result['values'][0];
				// sys_log("Update of civievent {$date['civicrm_id']} with ".print_r($found,1));
				$found['title'] = $name;
				$found['summary'] = array_shift(preg_split('/[.?!]/',$desc));
				$found['start_date'] = $date['date'].' '.$date['time'];
				$found['end_date'] = $date['date'].' '. date('H:i', strtotime("{$date['time']} +".SHOWLEN." hours"));
				$found['event_type_id'] = EVENT_TYPE;
				$result = civicrm_api3("Event","create",$found);
				if ( $result['is_error'] == 0 )
					sys_log("Update of civievent {$date['civicrm_id']} OK. ");
				else {
					sys_log("Update civicrm {$date['civicrm_id']} failed: ".print_r($result,1));
				}
			} else {
				sys_log("Civirm find id {$date['civicrm_id']} not found");
			}
		} else {
			// create new event
			$template_id = civicrm_get_template();
			$date['new'] = TRUE;
			$params = array (
				'template_id' => $template_id,
				'is_public' => 1, 
				'title' => $name,
				'summary' => array_shift(preg_split('/[.?!]/',$desc)),
				'description' => $desc,
				'start_date' => $date['date'].' '.$date['time'],
				'end_date' => $date['date'].' '. date('H:i', strtotime("{$date['time']} +2 hours")),
				'registration_begin_date' => date("Y-m-d H:i"),
				'registration_end_date' => $date['date'].' '.date('H:i', strtotime("{$date['time']} -1 hours")),
			);
			$result=civicrm_api3( "Event", "create", $params );
			if ($result['is_error'] == 0) {
				$date['civicrm_id'] = $result['id'];
				sys_log("Created {$date['civicrm_id']} in civicrm. ");
			} else {
				sys_log("Civicrm create failed: ".$result['error_message']);
			}
		}
	}
	
	foreach ($dates as &$date) {
		if ( isset($date['civicrm_id']) && ($date['civicrm_id']) ) {
			// record civicrm_id in shows table
			$sql = "UPDATE shows set civicrm_id={$date['civicrm_id']} WHERE id={$date['id']}";
			freeseat_query($sql);
			
			// make a price set to handle these tickets
			$price_set = civicrm_price_set_get($spec['id']);
			$price_set = civicrm_price_set_create( $dates, $prices, $price_set, $date['civicrm_id'], $spec );		
		}
	}
	return;
}

function civicrm_price_set_get( $id ) {
	$params = array(
		'name' => 'tickets_'.$id
	);
	try{
		$result = civicrm_api3('PriceSet', 'get', $params);
	}
	catch (CiviCRM_API3_Exception $e) {
		return NULL;
	}
	if ($result['count'] == 0) return NULL;
	return $result['id'];
}

function civicrm_price_set_create( $dates, $prices, $price_set = NULL, $entity_id, $spec ) {
	global $lang;
	$params = array(
		'entity_table' => 'civicrm_event',
		'entity_id' => $entity_id,
		'name' => 'tickets_'.$spec['id'],
		'title' => 'Tickets for '.$spec['name'],
		'extends' => 1,
	);
	$ids = array();
	/*  Structure of $ids array
		[price field name] = price field id
		[price field value name] = price field value id
	*/
	if ($price_set) {
		$params['id'] = $price_set;
		//  price set exists, so get its price field ids
		$fields_params = array(
			'price_set_id' => $price_set,
		);
		try{
			$fields_result = civicrm_api3('PriceField', 'get', $fields_params);
		}
		catch (CiviCRM_API3_Exception $e) {}
		if (is_array($fields_result) && $fields_result['count'] > 0) {
			// we got the price fields
			foreach ($fields_result['values'] as $fieldid => $item) {
				$fieldname = $item['name'];
				$ids[$fieldname] = $fieldid;
				// now get the price field values
				$values_params = array(
					'price_field_id' => $fieldid,
				);
				try{
					$values_result = civicrm_api3('PriceFieldValue', 'get', $values_params);
				}
				catch (CiviCRM_API3_Exception $e) {}
				if (is_array($values_result) && $values_result['count'] > 0) {
					foreach ($values_result['values'] as $valueid => $value) {
						// sys_log("Found field value = ".print_r($value,1));
						$valuename = $value['name'];
						$ids[$valuename] = $valueid;
					}
				}
			}
		}
		
	}
	// sys_log("Array ids = ".print_r($ids,1));
	foreach ($prices as $class => $cats) {
		// one price field for each class of ticket
		if ( $cats[CAT_NORMAL]['amt'] == 0 && $cats[CAT_REDUCED]['amt'] == 0 ) continue;
		foreach ( array(CAT_NORMAL, CAT_REDUCED) as $cat ) {
			if ( $cats[$cat]['amt'] == 0 ) continue;
			$field_name = 'class_'.$class.'_category_'.$cat;		
			$field_label = (isset($cats['comment']) && strlen($cats['comment'])>0 ? $cats['comment'] : 'class_'.$class)." - ".($cat == CAT_NORMAL ? $lang['cat_normal'] : $lang['cat_reduced']);
			$fields[$field_name] = array(
				'price_set_id' => $price_set,
				'name' => $field_name,
				'label' => $field_label,
				'html_type' => 'Text',
				'is_enter_qty' => 1,
				'is_required' => 0,
				'is_active' => 1,
				// 'options_per_line' => '2',
				'api.price_field_value.create' => array(
					'name' => $field_name.'_value',
					'label' => $field_label.' Price',
					'amount' => price_to_string($cats[$cat]['amt']),
					'is_active' => 1,
					'financial_type_id' => FINANCIAL_TYPE,
				),
			);
			if (isset($ids[$field_name])) {
				$fields[$field_name]['id'] = $ids[$field_name];
				if (isset($ids[$field_name.'_value'])) {
					$fields[$field_name]['api.price_field_value.create']['id'] = $ids[$field_name.'_value'];
				}
			}
		}
	}
	// transfer field data to the params array, and run it
	foreach ($fields as $name => $field) {
		$params['api.price_field.create'][] = $field;
	}
	// sys_log( "creating price set ".print_r($params,1) );
	try{
		$result = civicrm_api3('PriceSet', 'create', $params);
	}
	catch (CiviCRM_API3_Exception $e) {
		return NULL;
	}
	// write field ids into the price table
	foreach ($prices as $class => $cats) {
		if ( $cats[CAT_NORMAL] == 0 && $cats[CAT_REDUCED] == 0 ) continue;
		foreach ( array(CAT_NORMAL, CAT_REDUCED) as $cat ) {
			$field_name = 'class_'.$class.'_category_'.$cat;
			if (isset($fields[$field_name]) && !empty($fields[$field_name])) { 
				$field_id = $fields[$field_name]['id'];
				$sql = "UPDATE price set field_id=$field_id WHERE cat=$cat AND class=$class and spectacle={$spec['id']}";
				// sys_log( "writing field IDs into the price table with ".print_r($field_id,1) );
				freeseat_query($sql);
			}			
		}
	}
	return $result['id'];
}

function civicrm_get_template() {
	// Search for a FreeSeat template
	// Use it if it exists, create one if not
	global $lang;
	$result = civicrm_api3("Event","get", 
		array (
			'title' => 'FreeSeat', 
			'is_template' => 1,
			'sequential' => 1
		) 
	);
	if (is_array($result) && $result['count']>0 && empty($result['is_error'])) {
		// we have an event template, let's use it
		// assumes that the user has not messed with it too much
		// sys_log("Found a template result = ".print_r($result,1));
		$found = $result['values'][0];
		return $found['id'];
	} else {
		// create new event template
		$params = array (
			'is_public' => 0, 
			'is_online_registration' => 1, 
			'is_map' => 0,
			'is_active' => 1,
			'is_monetary' => 1,
			'financial_type_id' => FINANCIAL_TYPE,
			// user can set the location if desired
			'is_show_location' => 0, 
			'requires_approval' => 0,
			'is_template' => 1,  
			'has_waitlist' => 0, 
			'is_pay_later' => 0, 
			'is_share' => 1,
			'is_email_confirm' => 1,
			'title' => 'FreeSeat',
			'template_title' => $lang["civicrm_template_title"],
			'summary' => '',
			'description' => $lang["civicrm_template_description"],
			// set dates in the past so this event does not show up on public lists
			'start_date' => date("Y-m-d H:i", strtotime("-24 hours")),
			'end_date' => date("Y-m-d H:i", strtotime("-22 hours")),
			'event_type_id' => EVENT_TYPE,
			'is_multiple_registrations' => 0,  // don't change this
			'allow_same_participant_emails' => 1, 
			'registration_begin_date' => date("Y-m-d H:i", strtotime("-48 hours")),
			'registration_end_date' => date("Y-m-d H:i", strtotime("-25 hours")),
		);
		// sys_log( "Creating event template ".print_r($params,1) );
		$result=civicrm_api3( "Event", "create", $params );
		if (is_array($result) && empty($result['is_error'])) {
			sys_log("Created {$result['id']} as template in civicrm. ");
			return $result['id'];
		} else {
			sys_log("Civicrm template create failed: ".print_r($result,1));
			return NULL;
		}
	}
}

// hook into civicrm_pre
// add_filter( 'civicrm_pre', __NAMESPACE__ . '\\freeseat_civicrm_pre_callback', 10, 4 );
function freeseat_civicrm_pre_callback( $op, $objectName, $objectId, &$objectRef ) {
	// your code here
	print("Civicrm pre callback: ");
	print("op = ".print_r($op,1));
	print("objectname = ".print_r($objectName,1));
	print("objectid = ".print_r($objectId,1));
	// sys_log("objectref = ".print_r($objectRef,1));
}

/**
 * Alter fields for an event registration to make them into a demo form.
 */
// add_filter( 'civicrm_alterContent', __NAMESPACE__ . '\\freeseat_civicrm_alterContent', 10, 4 );
function freeseat_civicrm_alterContent( &$content, $context, $tplName, &$object ) {
  if($context == "form") {
    if($tplName == "CRM/Event/Form/Registration/Register.tpl") {
      if(isset($object->_eventId)) {
        $content = "<p>Below is an example of an event registration.</p>".$content;
        $content = str_replace("<input ","<input disabled='disabled' ",$content);
        $content = str_replace("<select ","<select disabled='disabled' ",$content);
        $content = $content."<p>Above is an example of an event registration</p>";
      }
    }
  }
}

/**
 *  This hook is invoked when a CiviCRM form is submitted. If the module has injected
any form elements, this hook should save the values in the database.  This hook is not called when using the API, only when using the regular forms. If you want to have an action that is triggered no matter if it's a form or an API, use the pre and post hooks instead.

postProcess CRM_Event_Form_Registration_Register form = CRM_Event_Form_Registration_Register Object
    [_totalAmount] => 43
    [_eventId] => 16
    [_params:protected] => Array
        (
            [0] => Array
                (
                    [first_name] => Dan
                    [last_name] => Hassell
                    [email-Primary] => cdhassell@gmail.com
                    [priceSetId] => 24
                    [price_14] => Array
                        (
                            [16] => 1
                        )
                    [price_15] => 0
                    [price_16] => Array
                        (
                            [18] => 1
                        )
                    [price_17] => 0
                    [amount] => 43.00
                    [payment_action] => Sale

 */
// add_filter( 'civicrm_postProcess', __NAMESPACE__ . '\\freeseat_civicrm_postProcess', 10, 4 );
function freeseat_civicrm_postProcess( $formName, &$form ) {
	print("<pre>Civicrm postProcess callback formName = ".print_r($formName,1)." postProcess</pre>");
	sys_log("postProcess ".print_r($formName,1)." form = ".print_r($form,1));

	$values = $form->getVar( '_values' );
	if (isset($values['event']['template_title']) && stripos($values['event']['template_title'],'FreeSeat')!==FALSE) {
		if ( is_a( $form, 'CRM_Event_Form_Registration_Register' ) ) {
			// get event id
			$eventid = $form->getVar( '_eventId' );
			$params  = $form->getVar( '_params'  );
			// get freeseat spectacle
			$sql = "SELECT spectacle FROM shows WHERE civicrm_id=$eventid";
			$specid = m_eval($sql);
			if ($specid) {
				// get an array of price field ids
				$sql = "SELECT field_id FROM price WHERE spectacle=$specid";
				$fieldids = fetch_all($sql);
				foreach ($fieldids as $field) {
					$pricefield = $params[0][ "price_$field" ];
					
				}
			} else {
				// nothing to do, give up
				return;
			}
			// get user data
			$_SESSION['firstname'] = $params[0]['first_name'];
			$_SESSION['lastname'] = $params[0]['last_name'];
			$_SESSION['email'] = $params[0]['email-Primary'];
			$price_set = $params[0]['priceSetId'];
			$amount = $params[0]['amount'];			
			// capture seat choices from freeseat and lock seats
			
			// write totals into civi fields
			
		} elseif ( is_a( $form, 'CRM_Event_Form_Registration_Confirm' ) ) {
			// capture cat choices from freeseat
			
		}
	}	
}

add_filter( 'civicrm_preProcess', __NAMESPACE__ . '\\freeseat_civicrm_preProcess', 10, 4 );
function freeseat_civicrm_preProcess($formName, &$form) {
	global $page_url;
	// is this a freeseat event?
	$values = $form->getVar( '_values' );
	if (isset($values['event']['template_title']) && stripos($values['event']['template_title'],'FreeSeat')!==FALSE) {
		sys_log("preProcess ".print_r($formName,1)." form = ".print_r($form,1));
		if ( is_a( $form, 'CRM_Event_Form_Registration_Register' ) ) {
			// display seats
			$eventid = $form->getVar( '_eventId' );
			// get freeseat spectacle
			$sql = "SELECT id FROM shows WHERE civicrm_id=$eventid";
			$showid = m_eval($sql);
			if ($showid) {
				$_SESSION['showid'] = $showid;
				freeseat_seats($page_url);
			}
			// js to set fields
		} elseif ( is_a( $form, 'CRM_Event_Form_Registration_Confirm' ) ) {
			// 
			// display cat choices
		} elseif ( is_a( $form, 'CRM_Event_Form_Registration_ThankYou' ) ) {
			
			// make final booking and display or email ticket
		}
	}
}

/** 
 *  Called at the bottom of the confirm page
 *  Display the paypal credit card form 
 */
function civicrm_form() {
	global $lang;
	// displayed within the paymentinfo div
	// prompt for the credit card info
	$months = array();
	$years = array();
	for($i=0; $i<12; $i++) { 
		$mstr = sprintf('%02d', $i+1);
		$y = date('Y')+$i; 
		$months[(string)($i+1)] = "$mstr - ". date('M', mktime(0,0,0,$i+1,date('j'),date('Y')));
		$years["$y"]  = "$y";
	}
	?>
		<p class="main">
			<?php echo $lang['civicrm_message']; ?>
		</p>
		<p class="main">
			<input type="hidden" name="freeseat-form" value="1">
			<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif">
		</p>
		<hr />
		<p class="main emph">
			<?php echo $lang['civicrm_title']; ?>
		</p>
		<p class="main">
			<?php echo $lang['civicrm_nameoncard']; ?>&emsp;
			<?php input_field("firstname"); ?>
			<?php input_field("lastname"); ?>
		</p>
		<p class="main">
			<?php 
				civicrm_select_one("civicrm_card_type", array( 'visa'=> 'Visa','mastercard' => 'MasterCard', 'amex' => 'American Express', 'discover' => 'Discover' ));
				input_field("civicrm_card_account", "", " size=20");
			?>
		</p>
		<p class="main">
			<?php
				civicrm_select_one("civicrm_card_expmonth", $months);
				civicrm_select_one("civicrm_card_expyear", $years);
				input_field("civicrm_card_cvv2", "", " size=5 title='{$lang['civicrm_card_cvv2_text']}'");
			?>
		</p>
		<p class="main">
			<input class="button button-primary" type="submit" value="<?php echo $lang["continue"]; ?>">
		</p>
	<?php
	sys_log('civicrm_form called');
}

function civicrm_process() {
	global $lang;
	foreach (array("firstname", "lastname", "civicrm_card_type", "civicrm_card_account", "civicrm_card_expmonth", "civicrm_card_expyear", "civicrm_card_cvv2", "isTest" ) as $a) {
		if (isset($_POST[$a])) $_SESSION[$a] = sanitize_text_field(nogpc($_POST[$a]));
	}
	if (isset($_SESSION['civicrm_card_expmonth']) && isset($_SESSION['civicrm_card_expyear'])) {
		$_SESSION['civicrm_exp'] = $_SESSION['civicrm_card_expmonth'].$_SESSION['civicrm_card_expyear'];
		$currentYear = date("y");
		if (($_SESSION['civicrm_card_expyear'] < $currentYear) || 
			(($_SESSION['civicrm_card_expyear'] == $currentYear && $civicrm_card_expmonth < date("m")) )) {
			kaboom( "Card is Expired");
		}
	}
	sys_log('civicrm_process called');
}

function civicrm_select_one( $name, $options ) {
	global $lang;
	// $name = variable name
	// $options = array of slug => label options
	?>
	<label><?php echo $lang[$name]; ?>&nbsp;
		<select name="<?php echo $name; ?>">
			<?php foreach ($options as $key => $val) {  ?>
				<option value="<?php echo $key; ?>" <?php if (isset($_SESSION[$name]) && $_SESSION[$name]==$key) echo " selected"; ?> >
				<?php echo $val;  ?> 
				</option>
			<?php } ?>
		</select>
	</label>
	<?php
}

function civicrm_cleanup() {
	// clear credit card session variables after use
	unset($_SESSION['civicrm_card_type']);
	unset($_SESSION['civicrm_card_account']);
	unset($_SESSION['civicrm_exp']);
	unset($_SESSION['civicrm_card_expmonth']);
	unset($_SESSION['civicrm_card_expyear']);
	unset($_SESSION['civicrm_card_cvv2']);
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

