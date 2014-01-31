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

add_action( 'init', __NAMESPACE__ . '\\freeseat_paypal_init_handler' );

function freeseat_plugin_init_paypal() {
    global $freeseat_plugin_hooks;

    $freeseat_plugin_hooks['ccard_checkamount']['paypal'] = 'paypal_checkamount';
    $freeseat_plugin_hooks['ccard_confirm_button']['paypal'] = 'paypal_confirm_button';
    // validation is done by checkamount.
    $freeseat_plugin_hooks['ccard_ipn_auth']['paypal'] = 'paypal_true';
    $freeseat_plugin_hooks['ccard_exists']['paypal'] = 'paypal_true';
    $freeseat_plugin_hooks['ccard_partner']['paypal'] = 'paypal_partner';
    $freeseat_plugin_hooks['ccard_paymentform']['paypal'] = 'paypal_paymentform';
    $freeseat_plugin_hooks['ccard_readparams']['paypal'] = 'paypal_readparams';
    $freeseat_plugin_hooks['check_session']['paypal'] = 'paypal_checksession';
    $freeseat_plugin_hooks['confirm_process']['paypal'] = 'paypal_pdt_check';
    $freeseat_plugin_hooks['params_post']['paypal'] = 'paypal_postedit';
    $freeseat_plugin_hooks['params_edit']['paypal'] = 'paypal_editparams';    
    init_language('paypal');
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
	}
	return $options;
}

function paypal_editparams($options) {
	global $lang;
	// the options parameter should be an array 
	if ( !is_array( $options ) ) return;
	if ( !isset( $options['paypal_account'] ) ) $options['paypal_account'] = 'Paypal account email';
	if ( !isset( $options['paypal_auth_token'] ) ) $options['paypal_auth_token'] = '';
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
		<?php _e( 'Paypal account authorization token' ); ?><br />
		<input type="text" size="60" name="freeseat_options[paypal_auth_token]" value="<?php echo $options['paypal_auth_token']; ?>" />
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

function paypal_readparams($void) {
	// get data back from transaction if it worked, or false if not
	global $transid, $unsafeamount, $groupid;

if (isset($_POST["item_number"])) {
		$groupid = (int)($_POST["item_number"]);
		if (isset($_POST["txn_id"]) && (strlen($_POST["txn_id"])==17)) {
		    $transid  = nogpc($_POST["txn_id"]); 
			if (isset($_POST["mc_gross"]))  {
				$unsafeamount = string_to_price($_POST["mc_gross"]);
				return true;
			}
		}
	}
	return false;
}

function get_memo() {
	global $sender_name;
	
	$sh = get_show($_SESSION["showid"]);
	$spec = get_spectacle($sh["spectacleid"]);
	$group = $_SESSION["groupid"];
	$memo = $sender_name; 
	$memo .= ' ' . $spec['name'];
	$memo .= " REF:$group: ";
    $memo .= price_to_string(get_total());
	return $memo;
}

/* print the submit (or image) button to be displayed in confirm.php */
function paypal_confirm_button() {
  echo '<div align="center"><input type="image" ' . 
    'src="https://www.paypal.com/en_US/i/btn/x-click-but03.gif" border="0" ' . 
    'name="submit" alt="Make payments with PayPal - it\'s fast, free and secure!">' .
    '</div>';
}

function paypal_modify_url( $mod, $remove = FALSE ) {
	// modifies or replaces pieces of the url query
	// accepts an array of key-value pairs to build a URL
	// retrieves the current host and URI from the server 
	$scheme = ( is_ssl() ? 'https://' : 'http://' );
    $url = $scheme.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $query = explode("&", $_SERVER['QUERY_STRING']);
    // modify/delete data
    foreach($query as $q) {
        list($key, $value) = explode("=", $q);
        if(array_key_exists($key, $mod)) {
            if($mod[$key]) {
                $url = preg_replace('/'.$key.'='.$value.'/', $key.'='.$mod[$key], $url);
            } elseif ($remove)  {  // this removes keys not in $mod
                $url = preg_replace('/&?'.$key.'='.$value.'/', '', $url);
            }
        }
    }
    // add new data, using ? for the first item
    $glue = ((strpos($url,'?') === FALSE) ? '?' : '&');
    foreach($mod as $key => $value) {
        if($value && !preg_match('/'.$key.'=/', $url)) {
            $url .= $glue.$key.'='.$value;
            $glue = '&';
        }
    }
    return $url;
}

/** Displays a button/link (a form with hidden fields from _SESSION)
that will redirect the user to the ccard provider's payment form **/
function paypal_paymentform() {
	global $paypal, $lang, $freeseat_vars;
	
    //Configuration Settings
    $paypal["business"] = $freeseat_vars['paypal_account'];
    // paypal is picky about the urls passed here
    $paypal["success_url"] = paypal_modify_url( array( 'fsp' => PAGE_FINISH, 'ok' => 'yes' )); 
    $paypal["cancel_url" ] = paypal_modify_url( array( 'fsp' => PAGE_FINISH ));
    $paypal["notify_url" ] = paypal_modify_url( array( 'freeseat_ipn' => '1' )); // back door confirmation IPN
    $paypal["return_method"] = "2"; //1=GET 2=POST
    $paypal["bn"] = "toolkit-php";
    $paypal["cmd"] = "_xclick";

    //Payment Page Settings
    $paypal["display_comment"]="1"; //0=yes 1=no
    $paypal["comment_header"]="Comments";
    $paypal["continue_button_text"]="Finish Ticket Purchase";
    $paypal["background_color"]=""; //""=white 1=black
    $paypal["display_shipping_address"]="1"; //""=yes 1=no

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
	$paypal['item_name'] = get_memo();		// construct memo field with summary
	$paypal['amount'] = price_to_string(get_total());
	sys_log( "paypal vars = " . print_r($paypal,1) );
	echo '<body onload="document.gopaypal.submit()">';
	echo '<form method="post" name="gopaypal" action="'.$paypal["url"].'">';
	if (function_exists('wp_nonce_field')) wp_nonce_field('freeseat-paypal-paymentform');
	// show paypal hidden variables
	// don't require another click, just go
	paypal_show_variables(); 
	// echo '<p class="main">';
	// printf($lang["paybutton"],'<input type="submit" value="','">');
	// echo '<input type="submit" value=" Pay ">';
	// echo '</p>';
	echo '</form>';
}

/** Requests and returns the amount of given transaction id,
 TRUE if the transaction is still pending, FALSE
in case there was a problem */
function paypal_checkamount($transid) {
  global $lang, $transid, $paypal;
  $repost = array();
  if (!isset($_POST["txn_id"])) return FALSE;

  /* Cancel magic quotes before resending the query... */
  foreach ($_POST as $key => $value) {
      $repost[$key] = nogpc($value);
  }
	$reply=fsockPost($paypal["url"],$repost);
	if (eregi("VERIFIED",$reply))  {
		if (($_POST["payment_status"]=="Completed") &&
			($repost["txn_id"]==$transid )  &&
			($repost["receiver_email"]== $paypal["business"] )) {
			//ok it checks out
			return string_to_price($_POST["mc_gross"]);
		} elseif ($_POST["payment_status"]=="Pending") {
			// ok but status is still pending
			kaboom(sprintf($lang["err_scriptstatus"],"Pending"));
			return TRUE;
		} else {
			kaboom(sprintf($lang["err_scriptauth"],'Paypal IPN verified'));
			kaboom("Reply: ".$reply);
			kaboom("Payment status: ".$repost["payment_status"]);
			kaboom("Txn_id: ".$repost["txn_id"]." $transid");
			kaboom("Receiver: ".$repost["receiver_email"]." ".$paypal["business"]);
		}
	} else {
		kaboom(sprintf($lang["err_scriptauth"],'Paypal IPN invalid'));
		kaboom("Reply: ".$reply);
		kaboom("URL: ".$paypal["url"]);
	}
	return FALSE;  
}

function fsockPost($url,$data) {
	//posts transaction data using fsockopen.
	$web = parse_url($url);

	$postdata = "cmd=_notify-validate";
	foreach ($data as $i=>$v) { //build post string
		$postdata.= '&' . $i . "=" . urlencode($v);
	}

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
		while(!feof($fp))     	//loop through the response from the server
			$info[]=@fgets($fp, 1024);
		fclose($fp);                //close fp - we are done with it
		$info=implode(",",$info);  //collapse results into a string
	}
	return $info;
}

function paypal_show_variables() {
//Display Paypal Hidden Variables
    global $paypal;
?>

<!-- PayPal Configuration -->
<input type="hidden" name="business" value="<?php echo $paypal["business"];?>">
<input type="hidden" name="cmd" value="<?php echo $paypal["cmd"];?>">
<input type="hidden" name="return" value="<?php echo $paypal['success_url']; ?>">
<input type="hidden" name="cancel_return" value="<?php echo $paypal['cancel_url']; ?>">
<input type="hidden" name="notify_url" value="<?php echo $paypal['notify_url']; ?>">
<input type="hidden" name="rm" value="<?php echo $paypal["return_method"];?>">
<input type="hidden" name="currency_code" value="<?php echo $paypal["currency_code"];?>">
<input type="hidden" name="lc" value="<?php echo $paypal["lc"];?>">
<input type="hidden" name="bn" value="<?php echo $paypal["bn"];?>">
<input type="hidden" name="cbt" value="<?php echo $paypal["continue_button_text"];?>">

<!-- Payment Page Information -->
<input type="hidden" name="no_shipping" value="<?php echo $paypal["display_shipping_address"];?>">
<input type="hidden" name="no_note" value="<?php echo $paypal["display_comment"];?>">
<input type="hidden" name="cn" value="<?php echo $paypal["comment_header"];?>">
<input type="hidden" name="cs" value="<?php echo $paypal["background_color"];?>">

<!-- Product Information -->
<input type="hidden" name="item_name" value="<?php echo $paypal["item_name"];?>">
<input type="hidden" name="amount" value="<?php echo $paypal["amount"];?>">
<input type="hidden" name="item_number" value="<?php echo $paypal["item_number"];?>">

<!-- Customer Information -->
<input type="hidden" name="first_name" value="<?php echo $paypal["first_name"];?>">
<input type="hidden" name="last_name" value="<?php echo $paypal["last_name"];?>">
<input type="hidden" name="address1" value="<?php echo $paypal["address1"];?>">
<input type="hidden" name="city" value="<?php echo $paypal["city"];?>">
<input type="hidden" name="state" value="<?php echo $paypal["state"];?>">
<input type="hidden" name="zip" value="<?php echo $paypal["zip"];?>">
<input type="hidden" name="email" value="<?php echo $paypal["email"];?>">
<?php 
if (isset($paypal["image_url"]))
  echo '<input type="hidden" name="image_url" value="' . freeseat_url($paypal['image_url']) . '">'; 
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

function paypal_pdt_check() { 
	// On success, saves an array with all PDT data variables in $_SESSION['PDT'].
	// Accepts a pending status for eCheck transactions as ok.
	// On failure, the user is shown a failure message and we exit.
	global $paypal, $freeseat_vars, $lang;
	
	$paypal_auth_token = $freeseat_vars['paypal_auth_token'];
	if (!isset($_GET["ok"]) || !$_GET["ok"]) return;  // let main script deal with it 
	if (!isset($paypal_auth_token)) return; // nothing to check
	if ($_SESSION['payment']!=PAY_CCARD) return;  // wrong payment type
	if (isset($_GET['tx'])) {
		$tx_token = $_GET['tx'];
		$req = "cmd=_notify-synch&tx=$tx_token&at=$paypal_auth_token";
		// post back to PayPal system to validate
		$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		// If possible, securely post back to paypal using HTTPS
		// $fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
		$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
		if (!$fp) {
			sys_log("Unable to reach PayPal site to verify transaction");
			return;
		} 
		fputs ($fp, $header . $req);
		$res = '';
		$headerdone = false;
		while (!feof($fp)) {
			$line = fgets ($fp, 1024);
			if (strcmp($line, "\r\n") == 0) 
				$headerdone = true;
			elseif ($headerdone) 
				$res .= $line;
		}
		// parse the data
		$lines = explode("\n", $res);
 		$keyarray = array();
		if (strcmp ($lines[0], "SUCCESS") == 0) {
			foreach ($lines as $i => $j){
				list($key,$val) = explode("=", $j);
				$keyarray[urldecode($key)] = urldecode($val);
			}
			if (((strcmp("Completed",$keyarray["payment_status"]) == 0) ||
				(strcmp("Pending",$keyarray["payment_status"]) == 0)) &&	
				(strcmp($keyarray["receiver_email"],$paypal["business"]) == 0)) {
				$keyarray["txnok"] = TRUE;
				$_SESSION['PDT'] = $keyarray;
				return;
			} 
		}
		sys_log("Paypal transaction failed\nHeader: ".$header."\nReq: ".$req."\nRes: ".$res);
		fclose ($fp);
	}
	show_head();
	echo sprintf( $lang["pdt_failure_page"], 'seats.php' ); 
	show_foot();
	exit;
}

function freeseat_paypal_init_handler() {
	global $lang, $transid, $paypal, $groupid, $unsafeamount, $smtp_sender, $admin_mail;
	/*
	
	[31-Jan-2014 11:56:38] PHP Notice:  Undefined index: business in /home/danhassell/wordpress/wp-content/plugins/freeseat/plugins/paypal/setup.php on line 260
[31-Jan-2014 11:56:38] PHP Notice:  Undefined index: business in /home/danhassell/wordpress/wp-content/plugins/freeseat/plugins/paypal/setup.php on line 272
	
	*/
	if ( isset( $_REQUEST[ 'freeseat_ipn' ] ) ) {
		// paypal is loading this page with an IPN

		$messages = array();
		$success = false; // set to true once the transaction is successfully processed.
		$alert = false; // set to true if the contents of $messages should be
				// sent to admin
		
		// !$success && $alert   means the thing completely failed
		// $success && $alert    means we managed but there was a problem.
		// $success && !$alert   means all went fine
		// !$success && !$alert  does not make sense
		
		prepare_log("ccard_ipn");
		
		if (! do_hook_exists("ccard_ipn_auth")) {   
			header("Status: 403 Forbidden");
			echo "<html><body><h1>403 Forbidden</h1><p>".$lang["err_badip"]."</p></body></html>";
			sys_log($lang["warn_badlogin"]);
			exit;
		}  
		
		if (do_hook_exists("ccard_readparams")) {
			/** This will be put in the mail in case something goes wrong,
			otherwise it gets discarded */
			kaboom("groupid=$groupid transid=$transid unsafeamount=$unsafeamount");
			
			if ($unsafeamount<0) {
				/* This is probably a notification about a refund, FreeSeat
					doesn't handle those. */
				exit;
			}
			
			$amount = do_hook_function("ccard_checkamount", $transid);
			
			if ($amount===TRUE || $amount===FALSE) {   
				// do nothing
			} else if ($unsafeamount!=$amount) {
				// user provided incorrect amount but transaction id is valid.
				// We'll use the (safe) $amount. 
				kaboom(sprintf($lang["err_ccard_nomatch"],
					"unsafeamount=".price_to_string($unsafeamount).
					", amount=". price_to_string($amount)));
				$alert = true;
			} // else: amounts match.
			
			if ($amount===TRUE) {
				sys_log("Pending payment GID=$groupid  TID=$transid  Amt=$unsafeamount ");
				// extend the booking timestamp by 4 days to allow for an echeck to clear
				$extend_date = date("Y-m-d H:i:s",time()+86400*4);
				$q="update booking set timestamp='$extend_date' where booking.groupid=$groupid or booking.id=$groupid";
				if (!freeseat_query( $q )) sys_log(freeseat_mysql_error());
				exit;
			} else if ($amount !== FALSE) {
				/* Thank You email will be sent at this point if things work well. */
				$success = process_ccard_transaction($groupid,$transid,$amount);
			} // else: checking amount failed. We set alert to true below.
		}
		
		if ($success)
			echo "success";
		else
			$alert = true;
		
		if ($alert) {
			$subject = ($success?$lang["alert"]:$lang["failure"]);
			$body = "\n".sprintf($lang["ccard_failed"],$subject);
			$body .= flush_messages_text();
			send_message($smtp_sender,$admin_mail,$subject,$body);
		}
		
		log_done();
		exit();
	}
}

