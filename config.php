<?php namespace freeseat;

$plugins = array( 'autopay', 'extendbooking', 'bookingnotes', 'groupdiscount' );
$unsecure_login = TRUE; 
$logfile = "logs/freeseat.log"; 
$ticket_logo = 'HCPAClogo250.jpg';
$lockingtime = 600;  // seat selections are locked for 10 minutes
$bookings_on_a_page = 25;
$format_time_12hr = true;
$ccard_provider = "paypal";
$paypal["site_url"]="http://tickets.hbg-cpac.org/";
$paypal["currency_code"]="USD"; // [USD,GBP,JPY,CAD,EUR]
$paypal["lc"]="US";
$paypal["url"]="https://www.paypal.com/cgi-bin/webscr";

ini_set("error_reporting",E_ALL);
ini_set("display_errors","1");

