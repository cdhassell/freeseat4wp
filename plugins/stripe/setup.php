<?php namespace freeseat;

/*
Integration for the Stripe Checkout plugin for accepting stripe payments

Fatal error: Uncaught exception 'Stripe\Error\InvalidRequest' with message 'Invalid source object: must be a dictionary or a non-empty string. See API docs at https://stripe.com/docs'' in /home/danhassell/wordpress/wp-content/plugins/stripe-checkout-pro/libraries/stripe-php/lib/ApiRequestor.php:97 Stack trace: #0 /home/danhassell/wordpress/wp-content/plugins/stripe-checkout-pro/libraries/stripe-php/lib/ApiRequestor.php(209): Stripe\ApiRequestor->handleApiError('{? "error": {?...', 400, Array) #1 /home/danhassell/wordpress/wp-content/plugins/stripe-checkout-pro/libraries/stripe-php/lib/ApiRequestor.php(60): Stripe\ApiRequestor->_interpretResponse('{? "error": {?...', 400) #2 /home/danhassell/wordpress/wp-content/plugins/stripe-checkout-pro/libraries/stripe-php/lib/ApiResource.php(105): Stripe\ApiRequestor->request('post', '/v1/customers', Array, Array) #3 /home/danhassell/wordpress/wp-content/plugins/stripe-checkout-pro/libraries/stripe-php/lib/ApiResource.php(137): Stripe\ApiResource::_staticRequest('post', '/v1/custome in /home/danhassell/wordpress/wp-content/plugins/stripe-checkout-pro/libraries/stripe-php/lib/ApiRequestor.php on line 97

*/

if ( !function_exists( 'is_plugin_inactive' ) ) 
	include_once ( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_inactive( 'stripe-checkout-pro/stripe-checkout-pro.php' ) &&
	 is_plugin_inactive( 'stripe-checkout/stripe-checkout.php' )) 
	kaboom( "Aborting stripe integration, stripe not found" ); 


add_action( 'wp_loaded', __NAMESPACE__ . '\\freeseat_stripe_return' );

function freeseat_stripe_return() {
	// we land here when returning from stripe with success
	if (is_page('freeseat-stripe-return')) {
		$charge_id = esc_html( $_GET['charge'] );
		// https://stripe.com/docs/api/php#charges
		$charge_response = \Stripe\Charge::retrieve( $charge_id );
		$amount = $charge_response->amount;  // in cents as usual in freeseat
		if ( isset( $_SESSION[ 'groupid' ] ) ) {
			$groupid = $_SESSION['groupid'];
		} else {
			// this depends on the format of stripe_get_memo() being correct
			$gary = explode( ":", $charge_response->description );
			$_SESSION['groupid'] = $groupid = (int)$array_pop($gary);
		}
		$transid = $charge_response->id;
		// or $transid = $_GET['charge'];
		$ok = process_ccard_transaction( $groupid, $transid, $amount );
		sys_log( "Stripe process success = $ok" );
		echo do_shortcode( '[freeseat-finish groupid="'.$groupid.'" ]' );
	}
}

add_action( 'wp_loaded', __NAMESPACE__ . '\\freeseat_stripe_review' );

function freeseat_stripe_review() {
	// we land here when returning from stripe with failure
	if (is_page('freeseat-stripe-review')) {
		$charge = esc_html( $_GET['charge'] );
		$charge_response = \Stripe\Charge::retrieve( $charge );
		sys_log( "Stripe process failure" . $charge_response->failure_message );
		stripe_failure( $charge_response->failure_message );
	}
}

add_filter( 'sc_payment_details', __NAMESPACE__ . '\\freeseat_stripe_details', 20, 2 );

function freeseat_stripe_details( $html, $charge_response ) {
	// This is copied from the original output so that we can grab the payment details
	// FIXME
	$html = '<div class="sc-payment-details-wrap">';
    $html .= '<p>' . __( 'Congratulations. Your payment went through!', 'sc' ) . '</p>' . "\n";
	if( ! empty( $charge_response->description ) ) {
		$html .= '<p>' . __( "Ticket Code:", 'sc' ) . '</p>';
		$html .= $charge_response->description . '<br>' . "\n";
	}
	$html .= '<br><strong>' . __( 'Total Paid: ', 'sc' ) . sc_stripe_to_formatted_amount( $charge_response->amount, $charge_response->currency ) . ' ' . strtoupper( $charge_response->currency ) . '</strong>' . "\n";
	$html .= '<p>Your transaction ID is: ' . $charge_response->id . '</p>';
	$html .= '</div>';
	return $html;
}

function freeseat_plugin_init_stripe() {
    global $freeseat_plugin_hooks, $stripe, $stripe_sandbox, $stripe_account;

	$freeseat_plugin_hooks['ccard_exists']['stripe'] = 'stripe_true';
	$freeseat_plugin_hooks['ccard_confirm_button']['stripe'] = 'stripe_confirm_button';
	$freeseat_plugin_hooks['ccard_partner']['stripe'] = 'stripe_partner';
	$freeseat_plugin_hooks['ccard_paymentform']['stripe'] = 'stripe_paymentform';
	$freeseat_plugin_hooks['check_session']['stripe'] = 'stripe_checksession';
	$freeseat_plugin_hooks['params_edit_ccard']['stripe'] = 'stripe_editparams';
	init_language('stripe');
}

function stripe_true($void) {
  return true;
}

function stripe_editparams($options) {
	global $lang;
	// the options parameter should be an array 
	if ( !is_array( $options ) ) return;
?>  
<!-- stripe stuff -->
<tr>
	<td>
	</td>
	<td colspan="3">
		<?php _e( 'Please visit the Stripe Checkout settings page to set up Stripe payments' ); ?><br />
	</td>
	<td>
	</td>
</tr>
<?php
}

function stripe_partner() {
	global $lang;
  ?>
<!-- stripe Logo -->
<div class="partner-block">
<a href="#" onclick="javascript:window.open('https://www.stripe.com/');">
<img src="<?php echo plugins_url('solid@2x.png', __FILE__); ?>" border="0" alt="Stripe Logo">
</a>
<?php $imgsrc = plugins_url( 'i2020.png' , dirname(dirname(__FILE__)) );  ?>
<img class='infolink' src='<?php echo $imgsrc; ?>' title='<?php echo $lang["stripe_about"]; ?>'>
</div><!-- stripe Logo -->
<?php
}

function stripe_failure( $msg = "" ) {
	global $lang;
	show_head();
	printf($lang["stripe_failure_page"], replace_fsp(get_permalink(), PAGE_PAY ));
	print "<p class='main'>$msg</p>";
	show_foot();
	return;
}

function stripe_get_memo() {
	global $websitename;
	
	$sh = get_show($_SESSION["showid"]);
	$spec = get_spectacle($sh["spectacleid"]);
	$group = $_SESSION["groupid"];
	$memo = $websitename; 
	$memo .= ' ' . $spec['name'];
	$memo .= " REF:$group";
    return $memo;
}

/* print the submit (or image) button to be displayed in confirm.php */
function stripe_confirm_button() {
	global $lang;
	echo '<p class="emph">' . $lang['stripe_lastchance'] . '</p>';
	submit_button( $lang[ "stripe_checkout" ], 'primary', 'submit', false );
	// stripe_paymentform();
}

/** Triggers the Stripe payment form overlay **/
function stripe_paymentform() {
	global $lang, $ticket_logo, $upload_path, $websitename;

	// details will be determined by the freeseat_stripe_return() function
	$url1 = home_url('/?page=freeseat-stripe-return');
	$url2 = home_url('/?page=freeseat-stripe-review');
	echo "<div id='freeseat-stripe-click'>";
	echo do_shortcode( $shortcode =
		"[stripe".
		" name=\"$websitename\"".
		" description=\"".stripe_get_memo()."\"".
		" amount=\"".get_total()."\"".
		" image_url=\"".freeseat_url($upload_path . $ticket_logo)."\"".
		" prefill_email=\"true\"".
		" success_redirect_url=\"$url1\"".
		" failure_redirect_url=\"$url2\"".
		" payment_button_label=\"{$lang["stripe_checkout"]}\"".
		"][/stripe]"
	);
	echo "</div>";
	sys_log("stripe paymentform called with: $shortcode");
}

function stripe_checksession($level) {
  global $lang;
  if ($level == 4) {
    if (($_SESSION["payment"]==PAY_CCARD) && !
	(isset($_SESSION["lastname"]) && ($_SESSION["lastname"]!='') &&
	 isset($_SESSION["email"]) && ($_SESSION["email"]!='') &&
	 isset($_SESSION["address"]) && ($_SESSION["address"]!='') &&
	 isset($_SESSION["postalcode"]) && ($_SESSION["postalcode"]!='') &&
	 isset($_SESSION["city"]) && ($_SESSION["city"]!='') 
	 )) {
      kaboom($lang["err_noaddress"]);
      return true; // not good.
    }
  }
  return false; // all is fine
}

// add an action to auto-click the stripe button
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\freeseat_stripe_jquery' ); 
 
function freeseat_stripe_jquery() {
	wp_enqueue_script( 'freeseat-stripe', plugins_url( 'freeseat-stripe.js', __FILE__ ), array( 'jquery' ), FALSE, TRUE );
}

