<?php namespace freeseat;


add_action( 'wp_loaded', __NAMESPACE__ . '\\freeseat_stripe_return' );

function freeseat_stripe_return() {
	// we land here when returning from stripe
	if (is_page('freeseat-stripe-return')) {
		
		do_shortcode( '[freeseat-finish groupid="'.$_SESSION['groupid'].'"]' );
	}
}

function freeseat_plugin_init_stripe() {
    global $freeseat_plugin_hooks, $stripe, $stripe_sandbox, $stripe_account;

	$freeseat_plugin_hooks['ccard_exists']['stripe'] = 'stripe_true';
	$freeseat_plugin_hooks['ccard_confirm_button']['stripe'] = 'stripe_confirm_button';
	$freeseat_plugin_hooks['ccard_partner']['stripe'] = 'stripe_partner';
	$freeseat_plugin_hooks['ccard_paymentform']['stripe'] = 'stripe_paymentform';
	$freeseat_plugin_hooks['check_session']['stripe'] = 'stripe_checksession';
	$freeseat_plugin_hooks['params_post']['stripe'] = 'stripe_postedit';
	$freeseat_plugin_hooks['params_edit_ccard']['stripe'] = 'stripe_editparams';
	// $freeseat_plugin_hooks['finish_ccard_failure']['stripe'] = 'stripe_failure';  
	init_language('stripe');
}

function stripe_true($void) {
  return true;
}

function stripe_postedit( &$options ) {
	// use WP post-form validation
	// called in freeseat_validate_options()
	if ( is_array( $options ) ) {
		$options['stripe_account'] = wp_filter_nohtml_kses($options['stripe_account']); 
		$options['stripe_auth_token'] = wp_filter_nohtml_kses($options['stripe_auth_token']);
		if (!isset($options['stripe_sandbox'])) $options['stripe_sandbox'] = 0;
	}
	return $options;
}

function stripe_editparams($options) {
	global $lang;
	// the options parameter should be an array 
	if ( !is_array( $options ) ) return;
	if ( !isset( $options['stripe_account'] ) ) $options['stripe_account'] = 'stripe account email';
	if ( !isset( $options['stripe_auth_token'] ) ) $options['stripe_auth_token'] = '';
	if ( !isset( $options['stripe_sandbox'] ) ) $options['stripe_sandbox'] = 0;
?>  
<!-- stripe stuff -->
<tr>
	<td>
	</td>
	<td>
		<?php _e( 'stripe account email' ); ?><br />
		<input type="text" size="25" name="freeseat_options[stripe_account]" value="<?php echo $options['stripe_account']; ?>" />
	</td>
	<td colspan="2">
		<?php _e( 'stripe account API token (optional)' ); ?><br />
		<input type="text" size="60" name="freeseat_options[stripe_auth_token]" value="<?php echo $options['stripe_auth_token']; ?>" />
	</td>
	<td>
		<label><input name="freeseat_options[stripe_sandbox]" type="checkbox" value="1" <?php if (isset($options['stripe_sandbox'])) { checked('1', $options['stripe_sandbox']); } ?> /> <?php _e( 'Sandbox mode' ); ?></label>
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
<img src="<?php echo plugins_url('solid.png', __FILE__); ?>" border="0" alt="Stripe Logo">
</a>
<?php $imgsrc = plugins_url( 'i2020.png' , dirname(dirname(__FILE__)) );  ?>
<img class='infolink' src='<?php echo $imgsrc; ?>' title='<?php echo $lang["stripe_about"]; ?>'>
</div><!-- stripe Logo -->
<?php
}

function stripe_failure() {
	global $lang;
	show_head();
	printf($lang["stripe_failure_page"], replace_fsp(get_permalink(), PAGE_PAY ));
	show_foot();
}

function stripe_get_memo() {
	global $websitename;
	
	$sh = get_show($_SESSION["showid"]);
	$spec = get_spectacle($sh["spectacleid"]);
	$group = $_SESSION["groupid"];
	$memo = $websitename; 
	$memo .= ' ' . $spec['name'];
	$memo .= " REF:$group ";
    return $memo;
}

/* print the submit (or image) button to be displayed in confirm.php */
function stripe_confirm_button() {
	global $lang;
	echo '<p class="emph">' . $lang['stripe_lastchance'] . '</p>';
	submit_button( $lang[ "stripe_checkout" ], 'primary', 'submit', false );
}

/** Triggers the Stripe payment form overlay **/
function stripe_paymentform() {
	global $lang, $ticket_logo, $websitename;

	// details will be determined by the freeseat_stripe_return() function
	$url = home_url('/?page=freeseat-stripe-return');
	do_shortcode( $shortcode =
		"[stripe]".
		" name=\"$websitename\"".
		" description=\"".stripe_get_memo()."\"".
		" amount=\"".get_total()."\"".
		" image_url=\"".freeseat_url($ticket_logo)."\"".
		" prefill_email=\"true\"".
		" success_redirect_url=\"$url\"".
		" failure_redirect_url=\"$url\"".
		" [/stripe]"
	);
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

function stripe_extend($groupid) {
	// Extend the expiration of a pending payment 
	$extend_date = date("Y-m-d H:i:s",time()+86400*4);
	$q="UPDATE booking SET timestamp='$extend_date' WHERE booking.groupid=$groupid OR booking.id=$groupid";
	if (!freeseat_query( $q )) sys_log(freeseat_mysql_error());
}

