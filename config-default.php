<?php

/** This file contains DEFAULT VALUES for configuration items.

 **** DO NOT MODIFY THIS FILE ****

 See config-dist.php instead (and Installation
 instructions)

 Copyright (C) 2010 Maxime Gamboni. See COPYING for copying/warranty
 info.

$Id: config-dist.php 253 2010-01-24 14:52:23Z tendays $
**/

  /* Commented items are those for which there's no sensible default
   and that MUST be set in config.php */

$plugins = array('bookingmap', 'remail', 'htmltickets', 'config', 'seatedit', 'showedit');
//$sec_area = 'https://example.com/reservations/';
//$normal_area = 'http://example.com/reservations/';
$unsecure_login = false;
$logfile = null;
$language = "english";
$stylesheet = "style/general.css";
$titlepage = "";
$footerpage = "";
$ticket_logo = 'images/sponsors.png';
$lockingtime = 240;
$websitename = 'our booking website';
$auto_mail_signature = "Kind Regards,\n\nThe Ticketing Department";
$pref_country_code	= "";
$top_countries = array();
$pref_state_code = "";
$helpmessage = 'For information, go to <a href="http://freeseat.sf.net" target="_blank">freeseat</a>';
$take_down = null;
$lowpriceconditions = '';
$legal_info = array();
$bookings_on_a_page = 20;
$format_time_12hr = false;
$dbserv = "localhost";
$dbdb = "ticketing";
$dbuser = "bookinguser";
$adminuser = "bookingadmin";
$systemuser = "bookingsystem";
$smtp_server     = "localhost";
$smtp_sender     = "reservations@example.com";
$sender_name     = "Booking Office";
//$admin_mail      = "postmaster@example.com";
//$smtp_helo       = "example.com";
$smtp_auth       = false;
//$smtp_user       = "user@example.com";
$smtp_pass       = "";
//$default_server_name = "example.com";
$default_charset = 'iso-8859-1';
$upload_path = 'files/';
$upload_url = 'files/';
$currency = "";
$moneyfactor = 100;
$postaltax = false;
$ccp = '';
//$ccard_provider="paypal";
//$paypal["business"]="yourpaypalid";
//$paypal["image_url"]="yourimage.jpg";
// $paypal["currency_code"]="USD";
$paypal["lc"]="";
$paypal["url"]="https://www.sandbox.paypal.com/cgi-bin/webscr";

?>