<?php namespace freeseat;

$unsecure_login = TRUE; 
$paypal["url"]="https://www.paypal.com/cgi-bin/webscr";			// for the real thing
// $paypal["url"]="https://www.sandbox.paypal.com/cgi-bin/webscr"; // for the sandbox

// for development only
ini_set("error_reporting",E_ALL);
ini_set("display_errors","1");
