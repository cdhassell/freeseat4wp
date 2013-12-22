<?php

define ('FS_PATH','');

require_once (FS_PATH . "vars.php");

require_once (FS_PATH . "functions/plugins.php");
require_once (FS_PATH . "functions/send.php");
require_once (FS_PATH . "functions/tools.php");

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
  
  if (!(mysql_connect($dbserv, $systemuser, $systempass) && mysql_select_db($dbdb))) {
    myboom($lang["err_connect"]);
  } else {
    if ($amount===TRUE) {
      sys_log("Pending payment GID=$groupid  TID=$transid  Amt=$unsafeamount ");
      // extend the booking timestamp by 4 days to allow for an echeck to clear
      $extend_date = date("Y-m-d H:i:s",time()+86400*4);
      $q="update booking set timestamp='$extend_date' where booking.groupid=$groupid or booking.id=$groupid";
      if (!mysql_query( $q )) sys_log(mysql_error());
      exit;
    } else if ($amount !== FALSE) {
    /* Thank You email will be sent at this point if things work well. */
    $success = process_ccard_transaction($groupid,$transid,$amount);
    } // else: checking amount failed. We set alert to true below.
  }
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

?>