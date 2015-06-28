<?php namespace freeseat;

/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.

$Id: confirm.php 279 2010-10-30 16:38:43Z tendays $
*/

/** Normal credit card transaction processing goes as follows.

1) User loads finish.php, confirming personal data etc
2) finish.php books the seats, obtains groupid and returns it to user
    as a form to send to credit card payment website
3) user sends groupid and other data to credit card payment website,
   fills credit card information and does the transaction. Credit card
   transaction succeeds.
4) credit card payment website loads ccard_confirm.php with the
   groupid it received along with transaction id and received amount.
5) ccard_confirm.php contacts credit card payment website to check the
   id and amounts are valid.
6) Reply is received - transaction id is valid ; ccard_confirm adds an
   entry to ccard_transactions, and sets the corresponding tickets to
   ST_PAID, send a mail to user. If the amount does not match, send a
   mail to admin?
7) ccard_confirm.php returns success code.
8) credit card payment website redirects user to finish.php with success GET parameter.
9) finish.php displays tickets and checks if tickets have been paid or
   not (checking booking state) and prints an on-screen message
   accordingly. Sends a mail in case they have NOT yet been paid.
**/

/** Where things can go wrong:

2) finish.php can't book the seats for some reason - process aborts
   here, no harm done.
3) user sends wrong groupid to credit card payment website - he will
   either pay for someone else or waste his money - that's his fault
   anyway.
3) user sends wrong amount to credit card payment website - admin will
   get notified and he will either donate money or get less tickets
   than he ordered.
3) credit card transaction fails - user gets redirected to point 1
   (except that seats are not booked again at 2 because
   $_SESSION["booking_done"] == true). If it always fails then booking
   will expire after a few days, or get deleted by admin after user
   mailed him.
4) evil user loads ccard_confirm.php with correct transaction (guess
   work) id but different groupid - if transaction id had already been
   used then nothing is done. - if transaction id had not yet been
   sent by credit card payment website then WE HAVE A PROBLEM. Someone
   can steal money from someone else if this is done. But then the
   other one will not get his tickets and will complain and then the
   detailed email sent to admin will show who's the bad guy and who's
   the good guy.
8) user sends success GET though transaction had failed - he gets his
   tickets but is told that they won't be valid until the money comes
   (i.e., never)

*/


add_action( 'wp_ajax_nopriv_freeseat_ipn_action', __NAMESPACE__ . '\\freeseat_ipn_listener' );
add_filter( 'query_vars', __NAMESPACE__ . '\\freeseat_query_vars' );
// add_action( 'wp_loaded', __NAMESPACE__ . '\\freeseat_paypal_return' );

// add an action to auto-click the button
add_action( 'init', __NAMESPACE__ . '\\freeseat_paypal_jquery' ); 
 
function freeseat_paypal_jquery() {
	wp_enqueue_script( 'freeseat-paypal', plugins_url( 'freeseat-paypal.js', __FILE__ ), array( 'jquery' ), FALSE, TRUE );
}

function freeseat_query_vars($vars) {
	// add to the valid list of variables
	$new_vars = array('freeseat-ipn', 'freeseat-return');
	$vars = $new_vars + $vars;
    return $vars;
}

function freeseat_plugin_init_paypal() {
    global $freeseat_plugin_hooks, $paypal, $paypal_sandbox, $paypal_account;

	$freeseat_plugin_hooks['ccard_confirm_button']['paypal'] = 'paypal_confirm_button';
	$freeseat_plugin_hooks['ccard_exists']['paypal'] = 'paypal_true';
	$freeseat_plugin_hooks['ccard_partner']['paypal'] = 'paypal_partner';
	$freeseat_plugin_hooks['ccard_paymentform']['paypal'] = 'paypal_paymentform';
	$freeseat_plugin_hooks['check_session']['paypal'] = 'paypal_checksession';
	// $freeseat_plugin_hooks['finish_post_booking']['paypal'] = 'paypal_pdt_check';
	$freeseat_plugin_hooks['params_post']['paypal'] = 'paypal_postedit';
	$freeseat_plugin_hooks['params_edit_ccard']['paypal'] = 'paypal_editparams';
	// $freeseat_plugin_hooks['finish_ccard_failure']['paypal'] = 'paypal_failure';  
	init_language('paypal');
	$paypal = array();
	$paypal["currency_code"]="USD"; // FIXME should be configurable [USD,GBP,JPY,CAD,EUR]
	$paypal["lc"]="US";
	$paypal["url"] = ( $paypal_sandbox ? 
		"https://www.sandbox.paypal.com/cgi-bin/webscr" :	// for the sandbox
		"https://www.paypal.com/cgi-bin/webscr"				// for the real thing
	);
	$paypal["business"] = $paypal_account;
	// freeseat_paypal_return();
}

function paypal_true($void) {
  return true;
}

function paypal_postedit( &$options ) {
	// use WP post-form validation
	// called in freeseat_validate_options()
	if ( is_array( $options ) ) {
		$options['paypal_account'] = wp_filter_nohtml_kses($options['paypal_account']); 
		$options['paypal_auth_token'] = wp_filter_nohtml_kses($options['paypal_auth_token']);
		if (!isset($options['paypal_sandbox'])) $options['paypal_sandbox'] = 0;
	}
	return $options;
}

function paypal_editparams($options) {
	global $lang;
	// the options parameter should be an array 
	if ( !is_array( $options ) ) return;
	if ( !isset( $options['paypal_account'] ) ) $options['paypal_account'] = 'Paypal account email';
	if ( !isset( $options['paypal_auth_token'] ) ) $options['paypal_auth_token'] = '';
	if ( !isset( $options['paypal_sandbox'] ) ) $options['paypal_sandbox'] = 0;
?>  
<!-- paypal stuff -->
<tr>
	<td>
	</td>
	<td>
		<?php _e( 'Paypal account email' ); ?><br />
		<input type="text" size="25" name="freeseat_options[paypal_account]" value="<?php echo $options['paypal_account']; ?>" />
	</td>
	<td colspan="2">
		<?php _e( 'Paypal account API token (optional)' ); ?><br />
		<input type="text" size="60" name="freeseat_options[paypal_auth_token]" value="<?php echo $options['paypal_auth_token']; ?>" />
	</td>
	<td>
		<label><input name="freeseat_options[paypal_sandbox]" type="checkbox" value="1" <?php if (isset($options['paypal_sandbox'])) { checked('1', $options['paypal_sandbox']); } ?> /> <?php _e( 'Sandbox mode' ); ?></label>
	</td>
</tr>
<?php
}


function paypal_partner() {
  global $lang;
  ?>
<!-- PayPal Logo --><div class="partner-block"><table border="0" cellpadding="10" cellspacing="0" align="center"><tr><td align="center"><i><?php echo $lang["we_accept"]; ?> </i></td></tr><tr>
<td align="center"><a href="#" onclick="javascript:window.open('https://www.paypal.com/us/cgi-bin/webscr?cmd=xpt/cps/popup/OLCWhatIsPayPal-outside','olcwhatispaypal','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=400, height=350');"><img src="https://www.paypal.com/en_US/i/bnr/horizontal_solution_PPeCheck.gif" border="0" alt="Solution Graphics"></a></td></tr></table></div><!-- PayPal Logo -->
<?php

}

function paypal_failure() {
	global $lang;
	show_head();
	printf($lang["paypal_failure_page"], replace_fsp(get_permalink(), PAGE_PAY ));
	show_foot();
}

function paypal_get_memo() {
	global $sender_name;
	
	$sh = get_show($_SESSION["showid"]);
	$spec = get_spectacle($sh["spectacleid"]);
	$group = $_SESSION["groupid"];
	$memo = $sender_name; 
	$memo .= ' ' . $spec['name'];
	$memo .= " REF:$group ";
    // $memo .= price_to_string(get_total());
	return $memo;
}

/* print the submit (or image) button to be displayed in confirm.php */
function paypal_confirm_button() {
	global $lang;
	echo '<p class="emph">' . $lang['paypal_lastchance'] . '</p>';
    echo '<div align="center"><input type="image" src="'.plugins_url("express-checkout-hero.png", __FILE__).'" border="0" name="submit" alt="Make payments with PayPal - it\'s fast, free and secure!"></div>';
}

/** Displays a button/link (a form with hidden fields from _SESSION)
that will redirect the user to the ccard provider's payment form **/
function paypal_paymentform() {
	global $paypal, $lang, $paypal_account, $ticket_logo;
	
	//Configuration Settings
	$paypal["business"] = $paypal_account;
	// details will be determined by the freeseat_paypal_return() function
	$url = home_url('/?page=freeseat-paypal-return');
	$paypal["cancel_return" ] = $url;
	$paypal["return"] = $url;
	
	$vars = array( 'freeseat-ipn' => 'erfolg', 'action' => 'freeseat_ipn_action' );
	$paypal["notify_url"] = add_query_arg( $vars, admin_url('admin-ajax.php') );
	$paypal["custom"] = $_SESSION['groupid'];
	$paypal["rm"] = "2"; 						//return method 1=GET 2=POST
	/* Return method. The FORM METHOD used to send data to the URL specified by the return variable.
		Allowable values are:
		0 – all shopping cart payments use the GET method (default)
		1 – the buyer's browser is redirected to the return URL by using the GET method, but no payment variables are included
		2 – the buyer's browser is redirected to the return URL by using the POST method, and all payment variables are included */
	$paypal["bn"] = "toolkit-php";
	$paypal["cmd"] = "_xclick";

	//Payment Page Settings
	$paypal["no_note"]="1"; 					//display comments 0=yes 1=no
	$paypal["cn"]="";							//comment header
	$paypal["cbt"]=$lang['paypal_button_text'];	//continue button text
	$paypal["cs"]=""; 							//background colour ""=white 1=black
	$paypal["no_shipping"]="1";					//display shipping address ""=yes 1=no

	// fill in paypal variables
	$paypal['first_name'] = $_SESSION['firstname'];
	$paypal['last_name'] = $_SESSION['lastname'];	
	$paypal['address1'] = $_SESSION['address'];	
	$paypal['city'] = $_SESSION['city'];	
	$paypal['state'] = $_SESSION['us_state'];	
	$paypal['zip'] = $_SESSION['postalcode'];	
	$paypal['email'] = $_SESSION['email'];
	$paypal['phone_1'] = $_SESSION['phone'];
	$paypal['item_number'] = $_SESSION['groupid'];
	$paypal['item_name'] = paypal_get_memo();		// construct memo field with summary
	$paypal['amount'] = price_to_string(get_total());
	// $paypal['image_url'] = $ticket_logo;
	sys_log("paypal return = {$paypal['return']}");
	sys_log("paypal cancel = {$paypal['cancel_return']}");
	echo '<div id="freeseat-paypal-click">';
	echo '<form method="post" name="gopaypal" action="'.$paypal["url"].'">';
	// if (function_exists('wp_nonce_field')) wp_nonce_field('freeseat-paypal-paymentform');
	// show paypal hidden variables
	// don't require another click, just go
	foreach ($paypal as $key=>$value) {
		echo "<input type='hidden' name='$key' value='$value'>";
	}
	echo '<p class="main">';
	printf($lang["paybutton"],'<input type="submit" value="','">');
	echo '</p>';
	echo '</form>';
	echo '</div>';
}

function paypal_checksession($level) {
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

/**
 *  If we do not have a verification from IPN yet, we can check via PDT
 *  Requires that the paypal_auth_token be set
 *  Called on the finish_post_booking hook
 *  Accepts a pending status for eCheck transactions as ok
 *  Pass $groupid as parameter.
 **/
function paypal_pdt_check($groupid) { 
	global $paypal, $freeseat_vars, $lang, $page_url, $transid, $smtp_sender, $admin_mail;
	
	// Did we get the IPN? If so nothing further is needed
	$sql = "SELECT count(numxkp) FROM ccard_transactions WHERE groupid=$groupid";
	if ( m_eval($sql) ) return TRUE;
	sys_log("No IPN record found");
	// If not, make a call to paypal to verify sale
	$success = FALSE;
	$paypal_auth_token = $freeseat_vars['paypal_auth_token'];
	if (!isset($paypal_auth_token)) return FALSE; // nothing to check
	if (isset($_GET['tx'])) {
		$tx_token = $_GET['tx'];
		$cmd = "cmd=_notify-synch&tx=$tx_token&at=$paypal_auth_token";
		$reply = fsockPost( $paypal["url"], $cmd );	// returns an array of strings
 		$keyarray = array();		
		foreach ($reply as $line) {
			$line = trim( $line );
			if (strcmp ($line, "SUCCESS") == 0) {
				$success = TRUE;
			}
			if ( strpos( "=", $line ) !== FALSE ) {
				list($key,$val) = explode("=", $line);
				$keyarray[urldecode($key)] = urldecode($val);
			}
		}
		if ($success) {
			$amount = string_to_price($keyarray["mc_gross"]);
			$transid  = nogpc($keyarray["txn_id"]);
			if ((strcmp("Completed",$keyarray["payment_status"]) == 0) &&
				strcmp($keyarray["receiver_email"],$paypal["business"]) == 0) {
				$ok = process_ccard_transaction( $groupid, $transid, $amount );
				sys_log("Paypal process success");
				return $ok;
			} elseif ((strcmp("Pending",$keyarray["payment_status"]) == 0) &&	
				(strcmp($keyarray["receiver_email"],$paypal["business"]) == 0)) {
				paypal_extend($groupid);
				sys_log("Pending payment GID=$groupid  TID=$transid  Amt=$amount ");
				return TRUE;
			} else {
				sys_log(sprintf($lang["err_scriptauth"],'Paypal PDT success'));
				sys_log("Reply: ".implode(",",$reply));
			}
		} else {
			sys_log(sprintf($lang["err_scriptauth"],'Paypal PDT failed'));
			sys_log("Reply: ".implode(",",$reply));
		}
	}
	// something is wrong, mail the admin
	$subject = ($success?$lang["alert"]:$lang["failure"]);
	$body = "\n".sprintf($lang["ccard_failed"],$subject);
	$body .= flush_messages_text();
	send_message($smtp_sender,$admin_mail,$subject,$body);
	return FALSE;
}

function paypal_extend($groupid) {
	// Extend the expiration of a pending payment 
	$extend_date = date("Y-m-d H:i:s",time()+86400*4);
	$q="UPDATE booking SET timestamp='$extend_date' WHERE booking.groupid=$groupid OR booking.id=$groupid";
	if (!freeseat_query( $q )) sys_log(freeseat_mysql_error());
}

function fsockPost($url,$postdata) {
	// Posts transaction data using fsockopen back to Paypal
	// Either freeseat_paypal_ipn_handler() or paypal_pdt_check() will check the response
	$web = parse_url($url);
	$info = array();

	if ($web["scheme"] == "https") { 		//set the port number
		$web["port"]="443";  
		$ssl="ssl://"; 
	} else { 
		$web["port"]="80";  
		$ssl=""; 
	}
	$fp=@fsockopen($ssl . $web["host"],$web["port"],$errnum,$errstr,30);
	if(!$fp) { 
		kaboom("$errnum: $errstr at $url ");
	} else {
		fputs($fp, "POST ".$web["path"]." HTTP/1.1\r\n");
		fputs($fp, "Host: ".$web["host"]."\r\n");
		fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
		fputs($fp, "Content-length: ".strlen($postdata)."\r\n");
		fputs($fp, "Connection: close\r\n\r\n");
		fputs($fp, $postdata . "\r\n\r\n");
		while(!feof($fp)) {    	//loop through the response from the server
			$info[] = trim( fgets($fp, 1024) );
		}
		fclose($fp);  //close fp - we are done with it
	}
	return $info;
}

/**
 *  AJAX callback function for paypal IPN 
 */
function freeseat_ipn_listener() {
	global $lang, $transid, $unsafeamount, $groupid, $paypal;

	if (!isset($_REQUEST['freeseat-ipn'])) return;
	prepare_log("ccard_ipn");
	$repost = stripslashes_deep($_POST);
	$cmd = "cmd=_notify-validate";
	foreach ($repost as $i=>$v) { 	//build post string
		$cmd .= '&' . $i . "=" . urlencode($v);
	}
	$ok = false;
	if (isset($repost["item_number"])) {
		$groupid = (int)($repost["item_number"]);
		if (isset($repost["txn_id"]) && (strlen($repost["txn_id"])==17)) {
		    $transid  = $repost["txn_id"]; 
			if (isset($repost["mc_gross"]))  {
				$unsafeamount = string_to_price($repost["mc_gross"]);
				if (isset($repost["receiver_email"]) && ($repost["receiver_email"]== $paypal["business"] )) {
					$ok = true;
				}
			}
		}
	}
	if ($ok) {
		$reply = fsockPost( $paypal["url"], $cmd );
		$replystr = implode( ", ", $reply );
		if ( preg_match( '/VERIFIED/i', $replystr ) )  {
			switch ($repost["payment_status"]) {
				case "Completed":
					// ok
					$amount = string_to_price($repost["mc_gross"]);
					$success = process_ccard_transaction($groupid,$transid,$amount);
					break;
				case "Pending": 
					// ok but status is pending, don't record it yet
					sys_log("Paypal IPN verified with status Pending GID=$groupid Amt=$unsafeamount ");
					paypal_extend( $groupid );
					break;
				default: 
					// wtf?
					sys_log("Paypal IPN verified but bad status GID=$groupid Status = ".$repost["payment_status"]);
			}
		} else {
			// payment failed
			sys_log(sprintf($lang["err_scriptauth"],'Paypal IPN')." Reply: ".print_r($reply,1));
		}
	}
	log_done();	
	exit();
}

/*

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

*/