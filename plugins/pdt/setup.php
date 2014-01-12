<?php namespace freeseat;

/** 
This script will check the Payment Data Transfer (PDT) returned by PayPal for a 
valid transaction and decide whether to print tickets based on the response.  
A notice of failure is displayed if not successful, and the user has the option 
to try again. Failures are logged but no other action is taken.

PayPal requires that Auto return must be enabled in the PayPal account, 
so there will not be a button to click for returning to our site after payment.

Requires that Payment Data Return and Automatic Return are set to ON in your 
PayPal account, and that you set $PDT_auth_token in config.php.
**/


function freeseat_plugin_init_pdt() {
    global $freeseat_plugin_hooks;

    $freeseat_plugin_hooks['config_form']['pdt'] = 'pdt_config_form';
    $freeseat_plugin_hooks['confirm_process']['pdt'] = 'pdt_check';
    init_language('pdt');
}

function pdt_config_form($form) {
  return config_form('plugins/pdt/config-dist.php', $form);
}

function pdt_check() { 
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

