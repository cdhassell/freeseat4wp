<?php

require_once (FS_PATH . "vars.php");

require_once (FS_PATH . "functions/money.php");
require_once (FS_PATH . "functions/tools.php");
include_once (FS_PATH . 'plugins/config/functions.php');

/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.

$Id: setup.php 352 2011-06-05 18:38:28Z tendays $
*/

/** Bindings for credit card payment system "klikandpay.com" (tm) */

function freeseat_plugin_init_klikandpay() {
    global $freeseat_plugin_hooks;

    $freeseat_plugin_hooks['config_form']['klikandpay'] = 'klikandpay_config_form';

    $freeseat_plugin_hooks['ccard_exists']['klikandpay'] = 'klikandpay_exists';

    $freeseat_plugin_hooks['ccard_partner']['klikandpay'] = 'klikandpay_partner';
    $freeseat_plugin_hooks['ccard_confirm_button']['klikandpay'] = 'klikandpay_confirm_button';
    $freeseat_plugin_hooks['ccard_paymentform']['klikandpay'] = 'klikandpay_paymentform';
    $freeseat_plugin_hooks['ccard_ipn_auth']['klikandpay'] = 'klikandpay_ipn_auth';
    $freeseat_plugin_hooks['ccard_readparams']['klikandpay'] = 'klikandpay_readparams';
    $freeseat_plugin_hooks['ccard_checkamount']['klikandpay'] = 'klikandpay_checkamount';
    $freeseat_plugin_hooks['check_session']['klikandpay'] = 
      'klikandpay_checksession';
}

function klikandpay_config_form($form) {
  return config_form('plugins/klikandpay/config-dist.php', $form);
}

function klikandpay_exists($void) {
  return true;
}

function klikandpay_partner() {
  global $lang;
  printf($lang["ccard_partner"],'<img border="0" src="http://www.klikandpay.com/images/logokp3.gif" width="86" height="23">');
}

/** print the submit (or image) button to be displayed in confirm.php */
function klikandpay_confirm_button() {
  echo '<input type="submit" value="'.$lang["book_submit"].'">';
}

/** Displays a button/link (a form with hidden fields from _SESSION)
that will redirect the user to the ccard provider's payment form **/
function klikandpay_paymentform() {
  global $klikandpayid,$lang;
?>
<form method="POST" action="https://www.klikandpay.com/paiement/check.pl">
<input type="hidden" name="NOM" value="<?php echo $_SESSION["lastname"]; ?>">
<input type="hidden" name="PRENOM" value="<?php echo $_SESSION["firstname"]; ?>">
<input type="hidden" name="TEL" value="<?php echo $_SESSION["phone"]; ?>">
<input type="hidden" name="EMAIL" value="<?php echo $_SESSION["email"]; ?>">
<input type="hidden" name="MONTANT" value="<?php echo price_to_string(get_total()); ?>">
<input type="hidden" name="ID" value="<?php echo $klikandpayid; ?>">
<input type="hidden" name="ADRESSE" value="<?php echo $_SESSION["address"]; ?>">
<input type="hidden" name="VILLE" value="<?php echo $_SESSION["city"]; ?>">
<input type="hidden" name="CODEPOSTAL" value="<?php echo $_SESSION["postalcode"]; ?>">
<input type="hidden" name="PAYS" value="<?php echo $_SESSION["country"]; ?>">
<input type="hidden" name="DETAIL" value="<?php // ";

 $data = $_SESSION["seats"];
 foreach ($data as $n => $s) {
   echo "REF:".$s["bookid"]."%Q:1%PRIX:";
   echo price_to_string(get_seat_price($s)).'|';
 }
 
?>">
<input type="hidden" name="RETOUR" value="<?php echo $_SESSION["groupid"]; ?>">

<p class="main"><?
//";
 printf($lang["paybutton"],'<input type="submit" value="','">');
?>
</p>
</form>
<hr width="25%">

<p class="fine-print">&nbsp;<a href="https://www.klikandpay.com/info.html" target="_blank"><?php
			       printf($lang["ccard_partner"],'<img border="0" src="http://www.klikandpay.com/images/logokp3.gif" width="86" height="23">');
?>
</a></p>

<?php
}

function klikandpay_ipn_auth($void) {
  return (ip2long($_SERVER["REMOTE_ADDR"]) & ip2long($klikconfirm_netmask)) ==
    ip2long($klikconfirm_clients);
}

/** Get _GET parameters and write them into global vars (eek!)
$groupid, $transid and $unsafeamount **/
function klikandpay_readparams($void) {
  global $groupid,$transid,$unsafeamount;
  $groupid = (int)($_GET["groupid"]);
  $transid = nogpc($_GET["NUMXKP"]);
  $unsafeamount = string_to_price($_GET["MONTANTXKP"]);
  return true;
}

/** Requests and returns the amount of given transaction id, or FALSE
in case there was a problem */
function klikandpay_checkamount($transid) {
  global $lang;

  /*** NOTE - If your webserver does not have access to a dns resolver
   *** then you will have to replace the hostname by the ip address
   ***/
  
  $conn = fopen("https://www.klikandpay.com/paiement/veriftransaction.pl?ID=$klikandpayid&NUMXKP=".urlencode($transid),"r");

  if (!$conn) {
    kaboom(sprintf($lang["err_scriptconnect"],'veriftransaction.pl'));
    return false;
  }

  $line = fgets ($conn, 1024);
    
  fclose($conn);

  $reply = explode ('|',$line);
  
  if ($reply[0]!="VALIDATIONYES") {
    kaboom(sprintf($lang["err_scriptauth"],'veriftransaction.pl'));
    kaboom($line);
    return false;
  }
    
  /* 6) Reply is received - transaction id is valid ; klikconfirm adds
    an entry to ccard_transactions, and sets the corresponding tickets
    to ST_PAID, send a mail to user. If the amount does not match,
    send a mail to admin */
  kaboom(" age=".$reply[2]."\n");
  
  return string_to_price($reply[1]);
}

function klikandpay_checksession($level) {
  if ($level == 4) {
    if (($_SESSION["payment"]==PAY_CCARD) && !
	(isset($_SESSION["lastname"]) && ($_SESSION["lastname"]!='') &&
	 isset($_SESSION["email"]) && ($_SESSION["email"]!='') &&
	 isset($_SESSION["address"]) && ($_SESSION["address"]!='') &&
	 isset($_SESSION["postalcode"]) && ($_SESSION["postalcode"]!='') &&
	 isset($_SESSION["city"]) && ($_SESSION["city"]!='')  &&
	 isset($_SESSION["country"]) && ($_SESSION["country"]!='')
	 )) {
      kaboom($lang["err_noaddress"]);
      return true;
    }
  }
  return false;
}
?>
