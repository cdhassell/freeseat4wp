<?php namespace freeseat;

/** This file contains DEFAULT VALUES for configuration items.

 **** DO NOT MODIFY THIS FILE ****
 Most configuration is done in the options page.

 Copyright (C) 2010 Maxime Gamboni. See COPYING for copying/warranty
 info.

**/

$plugins = array( 'pdftickets', 'barcode', 'paypal' );
$unsecure_login = false; 
$language = 'english';
$logfile = "logs/freeseat.log";
$stylesheet = "general.css";
$lockingtime = 240;
$bookings_on_a_page = 25;
$format_time_12hr = true;
$default_charset = 'iso-8859-1';
$upload_path = 'files/';
$upload_url = 'files/';
$moneyfactor = 100;
$postaltax = false;
$ccp = '';
$admin_mail      = "postmaster@example.com"; 
$smtp_helo       = "example.com";
$smtp_user       = "user@example.com";
$default_server_name = "example.com";

