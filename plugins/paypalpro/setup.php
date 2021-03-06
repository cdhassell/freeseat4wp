<?php namespace freeseat;

/*  Based on the Payal Framework plugin 
	Copyright 2009  Aaron D. Campbell  (email : wp_plugins@xavisys.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

add_action( 'paypal-ipn', __NAMESPACE__ . '\\freeseat_ipn');
add_action( 'template_redirect', __NAMESPACE__ . '\\freeseat_express_checkout' );
add_action( 'template_redirect', __NAMESPACE__ . '\\freeseat_paypalpro_go' );
add_filter( 'query_vars', __NAMESPACE__ . '\\freeseat_express_checkout_query' );
add_filter( 'paypal_framework_sslverify', '__return_true' );


function freeseat_plugin_init_paypalpro() {
    global $freeseat_plugin_hooks;
    
	$freeseat_plugin_hooks['ccard_exists']['paypalpro'] = 'paypalpro_true';
	$freeseat_plugin_hooks['params_edit_ccard']['paypalpro'] = 'paypalpro_editparams';
	$freeseat_plugin_hooks['params_post']['paypalpro'] = 'paypalpro_postedit';
	$freeseat_plugin_hooks['ccard_confirm_button']['paypalpro'] = 'paypalpro_form';
	$freeseat_plugin_hooks['confirm_process']['paypalpro'] = 'paypalpro_process'; 	
	$freeseat_plugin_hooks['kill_booking_done']['paypalpro'] = 'paypalpro_cleanup';
	init_language('paypalpro');
}

function paypalpro_true($void) {
  return true;
}

function freeseat_express_checkout_query($vars) {
	$vars[] = 'freeseat-return';
	$vars[] = 'fsp';
	$vars[] = 'token';
	return $vars;
}

/**
 *  Handle a paypal IPN call
 */
function freeseat_ipn( $repost ) {
	// $repost is the $_POST response from IPN 
	prepare_log("ccard_ipn");
	sys_log('IPN received: '.print_r($repost,1));
	$ok = FALSE;
	if (isset($repost["item_number"])) {
		$groupid = (int)($repost["item_number"]);
		if (isset($repost["txn_id"]) && (strlen($repost["txn_id"])==17)) {
		    $transid = $repost["txn_id"]; 
			if (isset($repost["mc_gross"])) {
				$amount = string_to_price($repost["mc_gross"]);
				$ok = TRUE;
			}
		}
	}
	if ($ok) {
		switch ($repost["payment_status"]) {
			case "Completed":
				// ok
				$success = process_ccard_transaction($groupid,$transid,$amount);
				break;
			case "Pending": 
				// ok but status is pending, don't record it yet
				sys_log("Paypal IPN verified with status Pending GID=$groupid Amt=$unsafeamount ");
				paypalpro_extend( $groupid );
				break;
			default: 
				// wtf?
				sys_log("Paypal IPN verified but bad status GID=$groupid Status = ".$repost["payment_status"]);
		}
	}
	log_done();	
}

/**
 *  Handle the jump back from paypal express checkout
 */
function freeseat_express_checkout( $data ) {
	global $lang;
	// check to see if we are returning from paypal on express checkout
	// go through all of the steps to confirm the payment in this function
	// http://test.hbg-cpac.org/freeseat_1/?freeseat-return=1&fsp=5&token=EC-88441505LY3895544&PayerID=6L2CBG9GT6U6W
	$qv = get_query_var( 'freeseat-return' );
	if ( empty($qv) ) return;
	switch ( $qv ) {
		case 1:
			// payment successful
			$token = urldecode(get_query_var('token'));
			$version = 109.0;
			$args = array( 
				'TOKEN' => $token, 
				'VERSION' => $version,
				'METHOD' => "GetExpressCheckoutDetails",
			);
			$response = sendMessage($args);
			sys_log("Express checkout return value: ".$response);
			// if (isset($response['ACK']) && preg_match("/Success/i", urldecode($response['ACK']))) {
				// $postid = urldecode($response['CUSTOM']);
				$groupid = $_SESSION['groupid'];  // urldecode($response['INVNUM']);
				$args['PAYERID'] = $response['PAYERID'];
				$args['METHOD'] = 'DoExpressCheckoutPayment';
				$args['PAYMENTREQUEST_0_PAYMENTACTION'] = 'Sale';
				$args['PAYMENTREQUEST_0_AMT'] = price_to_string(get_total());
				$response2 = sendMessage($args);
				if (isset($response2['ACK']) && preg_match("/Success/i", urldecode($response2['ACK']))) {
					$status = urldecode($response2['PAYMENTREQUEST_0_PAYMENTSTATUS']);
					$amount = urldecode($rsponse2['PAYMENTREQUEST_0_AMT']);
					$transid = urldecode($response2['PAYMENTREQUEST_0_TRANSACTIONID']);
					sys_log("paypalpro_express_checkout success: status = $status, amount = $amount, transid = $transid");
					if ($status=='Completed')
						process_ccard_transaction($groupid,$transid,$amount);
					if ($status=='Pending')
						paypalpro_extend( $groupid );
				} else {
					sys_log("DoExpressCheckoutPayment failed ".print_r($response2,1));
				}
			// } else {
			//	sys_log("GetExpressCheckoutDetails failed ".print_r($response,1));	 
			// }
			break;
		case 2:
			// user cancelled payment
			paypalpro_cancel();
			break;
		default:
			// wtf?
			sys_log("Express checkout return value unknown: ".$qv);
	}
	$_GET['fsp'] = PAGE_FINISH;
}

function paypalpro_cancel($resp = NULL) {
	global $lang;
	
	if (isset($resp['L_LONGMESSAGE0'])) {
		$msg = $resp['L_ERRORCODE0']." ".$resp['L_SEVERITYCODE0'].": ".$resp['L_LONGMESSAGE0'];
		sys_log($msg);
	}	
	kaboom( $lang["paypalpro_failure_page"] );  // replace_fsp(get_permalink(), PAGE_PAY )) );
}

/** 
 *  Called at the bottom of the confirm page
 *  Display the paypal credit card form 
 */
function paypalpro_form() {
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
			<?php echo $lang['paypalpro_message']; ?>
		</p>
		<p class="main">
			<input type="hidden" name="freeseat-form" value="1">
			<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif">
		</p>
		<hr />
		<p class="main emph">
			<?php echo $lang['paypalpro_title']; ?>
		</p>
		<p class="main">
			<?php echo $lang['paypalpro_nameoncard']; ?>&emsp;
			<?php input_field("firstname"); ?>
			<?php input_field("lastname"); ?>
		</p>
		<p class="main">
			<?php 
				select_one("paypalpro_type", array( 'visa'=> 'Visa','mastercard' => 'MasterCard', 'amex' => 'American Express', 'discover' => 'Discover' ));
				input_field("paypalpro_account", "", " size=20");
			?>
		</p>
		<p class="main">
			<?php
				select_one("paypalpro_expmonth", $months);
				select_one("paypalpro_expyear", $years);
				input_field("paypalpro_cvv2", "", " size=5 title='{$lang['paypalpro_cvv2_text']}'");
			?>
		</p>
		<p class="main">
			<input class="button button-primary" type="submit" value="<?php echo $lang["continue"]; ?>">
		</p>
	<?php
	sys_log('paypalpro_form called');
}

function paypalpro_process() {
	global $lang;
	foreach (array("firstname", "lastname", "paypalpro_type", "paypalpro_account", "paypalpro_expmonth", "paypalpro_expyear", "paypalpro_cvv2") as $a) {
		if (isset($_POST[$a])) $_SESSION[$a] = sanitize_text_field(nogpc($_POST[$a]));
	}
	if (isset($_SESSION['paypalpro_expmonth']) && isset($_SESSION['paypalpro_expyear']))
		$_SESSION['paypalpro_exp'] = $_SESSION['paypalpro_expmonth'].$_SESSION['paypalpro_expyear'];
	sys_log('paypalpro_process called');
}

/**
 *  The function is called on the template_redirect action hook 
 *  If we have to redirect to the paypal site, we need to do it before the page is printed
 *  Uses the $_POST['x'] variable to distinguish between Express Checkout and Direct Payment
 */
function freeseat_paypalpro_go() {
	global $lang;
	
	if ( !isset( $_REQUEST['freeseat-form'] ) ) return;
	paypalpro_process();
	unset($_SESSION["groupid"]);
	foreach ($_SESSION["seats"] as $n => $s) {
		// book each seat here with status ST_BOOKED
		if (($bookid = book($_SESSION,$s))!==FALSE) { 
			// assign the groupid as the first booked seat in the group
			$_SESSION["seats"][$n]["bookid"] = $bookid;
			if (!(isset($_SESSION["groupid"]) && $_SESSION["groupid"]!=0))
				$_SESSION["groupid"] = $bookid;
		}
	}
	$_SESSION["booking_done"] = ST_BOOKED;
	if (isset( $_REQUEST['x'] )) {
		sys_log('paypalpro_sendtoexpress called');
		$ppParams = array(
			'METHOD'		=> 'SetExpressCheckout',
			'DESC'			=> paypalpro_get_memo(),
			'FIRSTNAME'		=> $_SESSION['firstname'],
			'LASTNAME'		=> $_SESSION['lastname'],
			'EMAIL'			=> $_SESSION['email'],
			'STREET'		=> $_SESSION['address'],
			'STREET2'		=> '',
			'CITY'			=> $_SESSION['city'],
			'STATE'			=> $_SESSION['us_state'],
			'ZIP'			=> $_SESSION['postalcode'],
			'COUNTRYCODE'	=> $_SESSION['country'],
			'INVNUM'		=> $_SESSION['groupid'],
			'RETURNURL'		=> add_query_arg( array( 'freeseat-return' => 1 ), get_permalink() ),
			'CANCELURL'		=> add_query_arg( array( 'freeseat-return' => 2 ), get_permalink() ),
			'PAYMENTREQUEST_0_AMT' => price_to_string(get_total()),
			'PAYMENTREQUEST_0_CURRENCYCODE' => $lang['paypalpro_currency'],
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
			'VERSION'		=> '109.0',
			'LOGOIMG'		=> '', 
			'CUSTOM'		=> get_the_ID(), // store the post ID to come back to
		);
		$response = hashCall($ppParams);
		if (isset($response['ACK']) && preg_match("/Success/i", $response['ACK'])) {
			$token = array( 'token' => $response['TOKEN'] );
			sys_log('sendToExpressCheckout called');
			sendToExpressCheckout($token);
		} else {
			sys_log('sendToExpressCheckout failed with '.print_r($response,1));
			paypalpro_cancel($response);
		} 
	} else {
		sys_log('paypalpro_calldirect called');
		$ppParams = array(
			'METHOD'		=> 'doDirectPayment',
			'PAYMENTACTION'	=> 'Sale',
			'IPADDRESS'		=> paypalpro_user_ip(),
			'AMT'			=> price_to_string(get_total()),
			'DESC'			=> paypalpro_get_memo(),
			'CREDITCARDTYPE' => $_SESSION['paypalpro_type'],
			'ACCT'			=> $_SESSION['paypalpro_account'],
			'EXPDATE'		=> $_SESSION['paypalpro_exp'],
			'CVV2'			=> $_SESSION['paypalpro_cvv2'],
			'FIRSTNAME'		=> $_SESSION['firstname'],
			'LASTNAME'		=> $_SESSION['lastname'],
			'EMAIL'			=> $_SESSION['email'],
			'STREET'		=> $_SESSION['address'],
			'STREET2'		=> '',
			'CITY'			=> $_SESSION['city'],
			'STATE'			=> $_SESSION['us_state'],
			'ZIP'			=> $_SESSION['postalcode'],
			'COUNTRYCODE'	=> $_SESSION['country'],
			'INVNUM'		=> $_SESSION['groupid'],
			'CURRENCYCODE'	=> $lang['paypalpro_currency'],
		);
		sys_log('paypalpro_calldirect called');
		$response = hashCall($ppParams);
		if (isset($response['ACK']) && preg_match("/Success/i", $response['ACK'])) {
			$_SESSION["booking_done"] = ST_PAID;
		} else {
			sys_log('paypalpro_calldirect failed with '.print_r($response,1));
			paypalpro_cancel($response);
		}
		$_GET['fsp'] = PAGE_FINISH;
	}
}

function paypalpro_get_memo() {
	global $sender_name;
	
	$sh = get_show($_SESSION["showid"]);
	$spec = get_spectacle($sh["spectacleid"]);
	$group = $_SESSION["groupid"];
	return "$sender_name {$spec['name']} REF:$group ";
}

function paypalpro_postedit( &$options ) {
	// use WP post-form validation
	// called in freeseat_validate_options()
	if ( is_array( $options ) ) {
		$options['paypalpro_username'] = wp_filter_nohtml_kses($options['paypalpro_username']); 
		$options['paypalpro_password'] = wp_filter_nohtml_kses($options['paypalpro_password']);
		$options['paypalpro_signature'] = wp_filter_nohtml_kses($options['paypalpro_signature']);
		$options['paypalpro_sandbox_username'] = wp_filter_nohtml_kses($options['paypalpro_sandbox_username']);
		$options['paypalpro_sandbox_password'] = wp_filter_nohtml_kses($options['paypalpro_sandbox_password']);
		$options['paypalpro_sandbox_signature'] = wp_filter_nohtml_kses($options['paypalpro_sandbox_signature']);
		if (!isset($options['paypalpro_sandbox'])) $options['paypal_sandbox'] = 0;
	}
	return $options;
}

function paypalpro_editparams($options) {
	global $lang;
	// the options parameter should be an array 
	if ( !is_array( $options ) ) return;
	if ( !isset( $options['paypalpro_username'] ) ) $options['paypalpro_username'] = '';
	if ( !isset( $options['paypalpro_password'] ) ) $options['paypalpro_password'] = '';
	if ( !isset( $options['paypalpro_signature'] ) ) $options['paypalpro_signature'] = '';
	if ( !isset( $options['paypalpro_sandbox_username'] ) ) $options['paypalpro_sandbox_username'] = '';
	if ( !isset( $options['paypalpro_sandbox_password'] ) ) $options['paypalpro_sandbox_password'] = '';
	if ( !isset( $options['paypalpro_sandbox_signature'] ) ) $options['paypalpro_sandbox_signature'] = '';
	if ( !isset( $options['paypalpro_sandbox'] ) ) $options['paypalpro_sandbox'] = 0;
?>  
<!-- paypal pro stuff -->
<tr>
	<td>
		&emsp;<?php _e( 'Paypal Pro Settings' ); ?>
	</td>
	<td>
		<?php _e( 'Paypal live username' ); ?><br />
		<input type="text" name="freeseat_options[paypalpro_username]" value="<?php echo $options['paypalpro_username']; ?>" />
	</td>
	<td>
		<?php _e( 'Paypal live password' ); ?><br />
		<input type="text" name="freeseat_options[paypalpro_password]" value="<?php echo $options['paypalpro_password']; ?>" />
	</td>	
	<td colspan="2">
		<?php _e( 'Paypal live signature' ); ?><br />
		<input type="text" size="30" name="freeseat_options[paypalpro_signature]" value="<?php echo $options['paypalpro_signature']; ?>" />
	</td>
</tr>
<tr>
	<td>
		&emsp;<label><input name="freeseat_options[paypalpro_sandbox]" type="checkbox" value="1" <?php if (isset($options['paypalpro_sandbox'])) { checked('1', $options['paypalpro_sandbox']); } ?> /> <?php _e( 'Sandbox mode' ); ?></label>	
	</td>
	<td>
		<?php _e( 'Paypal sandbox username' ); ?><br />
		<input type="text" name="freeseat_options[paypalpro_sandbox_username]" value="<?php echo $options['paypalpro_sandbox_username']; ?>" />
	</td>
	<td>
		<?php _e( 'Paypal sandbox password' ); ?><br />
		<input type="text" name="freeseat_options[paypalpro_sandbox_password]" value="<?php echo $options['paypalpro_sandbox_password']; ?>" />
	</td>	
	<td colspan="2">
		<?php _e( 'Paypal sandbox signature' ); ?><br />
		<input type="text" size="30" name="freeseat_options[paypalpro_sandbox_signature]" value="<?php echo $options['paypalpro_sandbox_signature']; ?>" />
	</td>
</tr>
<?php
}

function paypalpro_user_ip() {
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	sys_log("paypalpro_user_ip ip = $ip");
	return apply_filters( 'wpb_get_ip', $ip );
}

function select_one( $name, $options ) {
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

function paypalpro_cleanup() {
	// clear credit card session variables after use
	unset($_SESSION['paypalpro_type']);
	unset($_SESSION['paypalpro_account']);
	unset($_SESSION['paypalpro_exp']);
	unset($_SESSION['paypalpro_expmonth']);
	unset($_SESSION['paypalpro_expyear']);
	unset($_SESSION['paypalpro_cvv2']);
}

function paypalpro_extend($groupid) {
	// Extend the expiration of a pending payment 
	$extend_date = date("Y-m-d H:i:s",time()+86400*4);
	$q="UPDATE booking SET timestamp='$extend_date' WHERE booking.groupid=$groupid OR booking.id=$groupid";
	if (!freeseat_query( $q )) sys_log(freeseat_mysql_error());
}


class wpPayPalFramework {
	private $_settings;
	static  $instance = false;
	private $_optionsName = 'paypal-framework';
	private $_optionsGroup = 'paypal-framework-options';
	private $_endpoint = array(
		'sandbox'	=> 'https://api-3t.sandbox.paypal.com/nvp',
		'live'		=> 'https://api-3t.paypal.com/nvp'
	);
	private $_url = array(
		'sandbox'	=> 'https://www.sandbox.paypal.com/webscr',
		'live'		=> 'https://www.paypal.com/webscr'
	);
	private $_listener_query_var		= 'paypalListener';
	private $_listener_query_var_value	= 'IPN';
	
	private function __construct() {
		$this->_getSettings();
		
		/**
		 * Add filters and actions
		 */
		add_action( 'admin_init', array($this,'registerOptions') );
		add_action( 'wp_ajax_nopriv_paypal_listener', array( $this, 'listener' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		add_filter( 'query_vars', array( $this, 'addPaypalListenerVar' ) );
		add_filter( 'init', array( $this, 'init_locale' ) );

		if ( 'on' == $this->_settings['legacy_support'] )
			add_action( 'init', 'paypalFramework_legacy_function' );
	}

	public static function getInstance() {
		if ( !self::$instance )
			self::$instance = new self;
		return self::$instance;
	}
	
	public function init_locale() {
		load_plugin_textdomain( 'paypal-framework', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	private function _getSettings() {
		global $lang;
		if (empty($this->_settings))
			$this->_settings = array();

		$defaults = array(
			'sandbox'			=> get_config("paypalpro_sandbox") ? "sandbox" : "live",
			'username-sandbox'	=> get_config("paypalpro_sandbox_username"),
			'password-sandbox'	=> get_config("paypalpro_sandbox_password"),
			'signature-sandbox'	=> get_config("paypalpro_sandbox_signature"),
			'username-live'		=> get_config("paypalpro_username"),
			'password-live'		=> get_config("paypalpro_password"),
			'signature-live'	=> get_config("paypalpro_signature"),
			'version'			=> '58.0',
			'currency'			=> '',
			'debugging'			=> 'on',
			'debugging_email'	=> '',
			'legacy_support'	=> 'off',
		);
		$this->_settings = wp_parse_args( $this->_settings, $defaults );
	}

	public function getSetting( $settingName, $default = false ) {
		if (empty($this->_settings))
			$this->_getSettings();

		if ( isset($this->_settings[$settingName]) )
			return $this->_settings[$settingName];
		else
			return $default;
	}

	public function registerOptions() {
		register_setting( $this->_optionsGroup, $this->_optionsName );
	}
	
	/**
	 * This function creates a name value pair (nvp) string from a given array,
	 * object, or string.  It also makes sure that all "names" in the nvp are
	 * all caps (which PayPal requires) and that anything that's not specified
	 * uses the defaults
	 *
	 * @param array|object|string $req Request to format
	 *
	 * @return string NVP string
	 */
	private function _prepRequest($req) {
		$vars = array( $this->_listener_query_var => $this->_listener_query_var_value );
		$defaults = array(
			'VERSION'		=> $this->_settings['version'],
			'PWD'			=> $this->_settings["password-{$this->_settings['sandbox']}"],
			'USER'			=> $this->_settings["username-{$this->_settings['sandbox']}"],
			'SIGNATURE'		=> $this->_settings["signature-{$this->_settings['sandbox']}"],
			'CURRENCYCODE'	=> $this->_settings['currency'],
			'NOTIFYURL'		=> add_query_arg( $vars, admin_url('admin-ajax.php') ),
		);
		return wp_parse_args( $req, $defaults );
	}

	/**
	 * Convert an associative array into an NVP string
	 *
	 * @param array Associative array to create NVP string from
	 * @param string[optional] Used to separate arguments (defaults to &)
	 *
	 * @return string NVP string
	 */
	public function makeNVP( $reqArray, $sep = '&' ) {
		if ( !is_array($reqArray) )
			return $reqArray;
		return http_build_query( $reqArray, '', $sep );
	}

	public function getEndpoint() {
		return $this->_endpoint[$this->_settings['sandbox']];
	}
	
	/**
	 * hashCall: Function to perform the API call to PayPal using API signature
	 * @param string|array $args Parameters needed for call
	 *
	 * @return array On success return associtive array containing the response from the server.
	 */
	public function hashCall( $args ) {
		$params = array(
			'body'		=> $this->_prepRequest($args),
			'sslverify' => apply_filters( 'paypal_framework_sslverify', false ),
			'timeout' 	=> 30,
		);
		sys_log('hashCall called with '.print_r($params,1));
		// Send the request
		$resp = wp_remote_post( $this->_endpoint[$this->_settings['sandbox']], $params );

		// If the response was valid, decode it and return it.  Otherwise return a WP_Error
		if ( !is_wp_error($resp) && $resp['response']['code'] >= 200 && $resp['response']['code'] < 300 ) {
			// Used for debugging.
			return wp_parse_args($resp['body']);
		} else {
			if ( !is_wp_error($resp) )
				$resp = new WP_Error('http_request_failed', $resp['response']['message'], $resp['response']);
			return $resp;
		}
	}

	private function _sanitizeRequest($request) {
		/**
		 * If this is a live request, hide sensitive data in the debug
		 * E-Mails we send
		 */
		if ( $this->_settings['sandbox'] != 'sandbox' ) {
			if ( !empty( $request['ACCT'] ) )
				$request['ACCT']	= str_repeat('*', strlen($request['ACCT'])-4) . substr($request['ACCT'], -4);
			if ( !empty( $request['EXPDATE'] ) )
				$request['EXPDATE']	= str_repeat('*', strlen($request['EXPDATE']));
			if ( !empty( $request['CVV2'] ) )
				$request['CVV2']	= str_repeat('*', strlen($request['CVV2']));
		}
		return $request;
	}

	/**
	 * Used to direct the user to the Express Checkout
	 *
	 * @param string|array $args Parameters needed for call.  *token is REQUIRED*
	 */
	public function sendToExpressCheckout($args) {
		$args = $this->_prepRequest($args);
		$args['cmd'] = '_express-checkout';
		$nvpString = $this->makeNVP($args);
		wp_redirect($this->_url[$this->_settings['sandbox']] . "?{$nvpString}");
		exit;
	}

	public function template_redirect() {
		// Check that the query var is set and is the correct value.
		if ( get_query_var( $this->_listener_query_var ) == $this->_listener_query_var_value )
			$this->listener();
	}

	/**
	 * This is our listener.  If the proper query var is set correctly it will
	 * attempt to handle the response.
	 */
	public function listener() {
		$_POST = stripslashes_deep($_POST);
		// Try to validate the response to make sure it's from PayPal
		if ($this->_validateMessage())
			$this->_processMessage();
		// Stop WordPress entirely
		exit;
	}

	/**
	 * Get the PayPal URL based on current setting for sandbox vs live
	 */
	public function getUrl() {
		return $this->_url[$this->_settings['sandbox']];
	}

	/**
	 *  Post a message to PayPal and get response
	 *  Returns an array of responses or FALSE on error
	 */
	public function sendMessage($args) {
		$params = array(
			'body' => $this->_prepRequest($args),
			'sslverify' => apply_filters( 'paypal_framework_sslverify', false ),
			'timeout' 	=> 30,
		);
		// Send the request
		$resp = wp_remote_post( $this->_url[$this->_settings['sandbox']], $params );
		// If the response was valid, check to see if the request was valid
		if ( !is_wp_error($resp) && $resp['response']['code'] >= 200 && $resp['response']['code'] < 300 ) {
			return wp_parse_args($resp['body']);
		} else {
			// If we can't validate the message, assume it's bad
			sys_log( 'Paypal Validation Failed ' . print_r($resp));
			return false;
		}
	}

	/**
	 * Validate the message by checking with PayPal to make sure they really
	 * sent it
	 */
	private function _validateMessage() {
		// Set the command that is used to validate the message
		$_POST['cmd'] = "_notify-validate";

		// We need to send the message back to PayPal just as we received it
		$params = array(
			'body' => $_POST,
			'sslverify' => apply_filters( 'paypal_framework_sslverify', false ),
			'timeout' 	=> 30,
		);

		// Send the request
		$resp = wp_remote_post( $this->_url[$this->_settings['sandbox']], $params );
		// Put the $_POST data back to how it was so we can pass it to the action
		unset( $_POST['cmd'] );
		// If the response was valid, check to see if the request was valid
		if ( !is_wp_error($resp) && $resp['response']['code'] >= 200 && $resp['response']['code'] < 300 && (strcmp( $resp['body'], "VERIFIED") == 0)) {
			return true;
		} else {
			// If we can't validate the message, assume it's bad
			return false;
		}
	}

	/**
	 * Add our query var to the list of query vars
	 */
	public function addPaypalListenerVar($public_query_vars) {
		$public_query_vars[] = $this->_listener_query_var;
		return $public_query_vars;
	}

	/**
	 * Throw an action based off the transaction type of the message
	 */
	private function _processMessage() {
		do_action( 'paypal-ipn', $_POST );
		if ( !empty($_POST['txn_type']) ) {
			do_action("paypal-{$_POST['txn_type']}", $_POST);
		}
	}
}

/**
 * Helper functions
 */
function hashCall ($args) {
	$wpPayPalFramework = wpPayPalFramework::getInstance();
	return $wpPayPalFramework->hashCall($args);
}

function sendToExpressCheckout ($args) {
	$wpPayPalFramework = wpPayPalFramework::getInstance();
	return $wpPayPalFramework->sendToExpressCheckout($args);
}

function sendMessage ($args) {
	$wpPayPalFramework = wpPayPalFramework::getInstance();
	return $wpPayPalFramework->sendMessage($args);
}

function paypalFramework_legacy_function() {
	//Only load if the function doesn't already exist
	if ( !function_exists('hash_call') ) {
		/**
		 * Support the old method of using hash_call
		 */
		function hash_call($methodName, $nvpStr) {
			_deprecated_function(__FUNCTION__, '0.1', 'wpPayPalFramework::hashCall()');
			$nvpStr = wp_parse_args( $nvpStr );
			$nvpStr['METHOD'] = $methodName;
			$nvpStr = array_map('urldecode', $nvpStr);
			$wpPayPalFramework = wpPayPalFramework::getInstance();
			return $wpPayPalFramework->hashCall($nvpStr);
		}
	}
}

// Instantiate our class
$wpPayPalFramework = wpPayPalFramework::getInstance();
