<?php

/*** PayPal Configuration ***/

// @no-echo
/* Leave this section alone it you are not using Paypal */

// $plugins[]="paypal"; // uncomment this if you want to use PayPal
// @echo

// @type string
$paypal["business"]="yourpaypalid";

/* The following is relative to $normal_area. */
// @type string
$paypal["image_url"]="path/to/yourimage.jpg";
// choose one of USD,GBP,JPY,CAD,EUR
// @type string
$paypal["currency_code"]="USD";
// language code, see paypal site for details
// @type string
$paypal["lc"]="US";

/* Set the following to the url to which paypal payment must be posted. It may be one of the following:<br>

For testing (default), transactions may be directed to the PayPal
sandbox:
"https://www.sandbox.paypal.com/cgi-bin/webscr"<br>
See the PayPal site for details on how to use the sandbox.<br>

For production use with secure transactions use https:
"https://www.paypal.com/cgi-bin/webscr"<br>

For production use without SSL, use this address:
"http://www.paypal.com/cgi-bin/webscr" */
// @type string
//$paypal["url"]="https://www.sandbox.paypal.com/cgi-bin/webscr"; // default

