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

Event registration:
Start in civicrm, redirect to seat selection and back
Return to civievent for confirmation and payment
Or, start in freeseat and jump to civievent URL after seat selection
	
Post-checkout hook:
Attach ticket PDF to confirmation email

Event setup: OK
Write dates, times to civicrm via API
Write ticket prices to price sets
Maintain showid to eventid link

Registration maintenance:
Handle delete or edit registration
Use freeseat POS interface

Comments:
Price set must have recognizable tags by cat and class
Must allow multiple signups on one email
No ccard table in freeseat
Booking table - Booking links to participant

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
		$contact_create=civicrm_api3("Contact","create", $params);
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
				return;
			}
		}
		// at this point we have a contact id
		$params = array (
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
		$result = civicrm_api3( "participant", "create", $params );
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
				// FIXME assumes that all shows are 2 hours - should make that configurable
				$found['end_date'] = $date['date'].' '. date('H:i', strtotime("{$date['time']} +2 hours"));
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
		if ( $cats[CAT_NORMAL] == 0 && $cats[CAT_REDUCED] == 0 ) continue;
		foreach ( array(CAT_NORMAL, CAT_REDUCED) as $cat ) {
			if ( $cats[$cat] == 0 ) continue;
			$field_name = 'class_'.$class.'_category_'.$cat;		
			$field_label = (isset($cats['comment']) && strlen($cats['comment'])>0 ? $cats['comment'] : 'class_'.$class)." - ".($cat == CAT_NORMAL ? $lang['cat_normal'] : $lang['cat_reduced']);
			$fields[$field_name] = array(
				'price_set_id' => $price_set,
				'name' => $field_name,
				'label' => $field_label,
				'html_type' => 'Text',
				'is_enter_qty' => 1,
				'is_active' => 1,
				// 'options_per_line' => '2',
				'api.price_field_value.create' => array(
					'name' => $field_name.'_value',
					'label' => $field_label.' Price',
					'amount' => price_to_string($cats[$cat]),
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
	return $result['id'];
}

function civicrm_get_template() {
	// Search for a FreeSeat template
	// Use it if it exists, create one if not
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
			'template_title' => 'FreeSeat show template',
			'summary' => 'FreeSeat Event Template',
			'description' => 'This is a FreeSeat event template. Please edit cautiously. Changes you make here will be repeated in all future FreeSeat events.',
			// set dates in the past so this event does not show up on public lists
			'start_date' => date("Y-m-d H:i", strtotime("-24 hours")),
			'end_date' => date("Y-m-d H:i", strtotime("-22 hours")),
			'event_type_id' => EVENT_TYPE,
			'is_multiple_registrations' => 1,
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
	sys_log("Civicrm pre callback: ");
	sys_log("op = ".print_r($op,1));
	sys_log("objectname = ".print_r($objectName,1));
	sys_log("objectid = ".print_r($objectId,1));
	// sys_log("objectref = ".print_r($objectRef,1));
}

/**
 * Alter fields for an event registration to make them into a demo form.
 */
add_filter( 'civicrm_alterContent', __NAMESPACE__ . '\\freeseat_civicrm_alterContent', 10, 4 );
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
 */
add_filter( 'civicrm_postProcess', __NAMESPACE__ . '\\freeseat_civicrm_postProcess', 10, 4 );
function freeseat_civicrm_postProcess( $formName, &$form ) {
	sys_log("Civicrm postProcess callback: ");
	sys_log("formName = ".print_r($formName,1));
	// sys_log("form = ".print_r($form,1));
}

/*
Parameters
    string $formName - the name of the form
    object $form - reference to the form object
 */
add_filter( 'civicrm_preProcess', __NAMESPACE__ . '\\freeseat_civicrm_preProcess', 10, 4 );
function freeseat_civicrm_preProcess($formName, &$form) {
	sys_log("Civicrm preProcess callback: ");
	sys_log("formName = ".print_r($formName,1));
	// sys_log("form = ".print_r($form,1));
}

/*
This hook is called when building the amount structure for a Contribution or Event Page. It allows you to modify the set of radio buttons representing amounts for contribution levels and event registration fees.
Parameters
    $pageType - is this a 'contribution', 'event', or 'membership'
    $form - reference to the form object
    $amount - the amount structure to be displayed
 */
add_filter( 'civicrm_buildAmount', __NAMESPACE__ . '\\freeseat_civicrm_buildAmount', 10, 4 );
function freeseat_civicrm_buildAmount( $pageType, &$form, &$amount ) {
	//sample to modify priceset fee
	$priceSetId = $form->get( 'priceSetId' );
	if ( !empty( $priceSetId ) ) {
		$feeBlock =& $amount;
		// if you use this in sample data, u'll see changes in
		// contrib page id = 1, event page id = 1 and
		if (!is_array( $feeBlock ) || empty( $feeBlock ) ) {
			return;
		}
		//in case of event we get eventId,
		if ( $pageType == 'event' ) {
			$eventid = $form->_eventId;
			sys_log("Civicrm buildAmount callback: ");
			sys_log("priceSetId = ".print_r($priceSetId,1));
			// sys_log("form = ".print_r($form,1));
			sys_log("amount = ".print_r($amount,1));
			// sys_log("feeBlock = ".print_r($feeBlock,1));
		}
	}
}


/* function civicrm_config_db($user) {
	return config_checksql_for('plugins/civicrm/setup.sql', $user);
} */

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

