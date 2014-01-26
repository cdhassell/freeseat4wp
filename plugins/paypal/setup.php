<?php namespace freeseat;


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
<!-- PayPal Logo --><div class="partner-block"><table border="0" cellpadding="10" cellspacing="0" align="center"><tr><td align="right"><i><?php echo $lang["we_accept"]; ?> </i></td></tr><tr>
<td align="left"><a href="#" onclick="javascript:window.open('https://www.paypal.com/us/cgi-bin/webscr?cmd=xpt/cps/popup/OLCWhatIsPayPal-outside','olcwhatispaypal','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=400, height=350');"><img src="https://www.paypal.com/en_US/i/bnr/horizontal_solution_PPeCheck.gif" border="0" alt="Solution Graphics"></a></td></tr></table></div><!-- PayPal Logo -->
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

/** Displays a button/link (a form with hidden fields from _SESSION)
that will redirect the user to the ccard provider's payment form **/
function paypal_paymentform() {
	global $paypal,$lang;
	
    //Configuration Settings
    $paypal["success_url"]="finish.php?ok=yes";  // user is redirected here on success
    $paypal["cancel_url"]="finish.php";          // or here on cancel
    $paypal["notify_url"]="ccard_confirm.php";   // back door confirmation IPN
    $paypal["return_method"]="2"; //1=GET 2=POST
    $paypal["bn"]="toolkit-php";
    $paypal["cmd"]="_xclick";

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
	echo '<body onload="document.gopaypal.submit()">';
	echo '<form method="post" name="gopaypal" action="'.$paypal["url"].'">';
	//show paypal hidden variables
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
<input type="hidden" name="return" value="<?php echo freeseat_url($paypal['success_url']); ?>">
<input type="hidden" name="cancel_return" value="<?php echo freeseat_url($paypal['cancel_url']); ?>">
<input type="hidden" name="notify_url" value="<?php echo freeseat_url($paypal['notify_url'], false); ?>">
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
  global $paypal, $PDT_auth_token,$lang;

  if (!isset($_GET["ok"]) || !$_GET["ok"]) return;  // let main script deal with it 
  if (!isset($PDT_auth_token)) return; // nothing to check
  if ($_SESSION['payment']!=PAY_CCARD) return;  // wrong payment type
  if (isset($_GET['tx'])) {
    $tx_token = $_GET['tx'];
    $req = "cmd=_notify-synch&tx=$tx_token&at=$PDT_auth_token";
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

