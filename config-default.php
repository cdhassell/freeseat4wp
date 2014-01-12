<?php namespace freeseat;

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

$plugins = array('bookingmap');
//$sec_area = 'https://example.com/reservations/';
//$normal_area = 'http://example.com/reservations/';
$language = 'english';
$unsecure_login = false;
$logfile = null;
$stylesheet = "style/general.css";
$lockingtime = 240;
$bookings_on_a_page = 20;
$format_time_12hr = false;
$default_charset = 'iso-8859-1';
$upload_path = 'files/';
$upload_url = 'files/';
$moneyfactor = 100;
$postaltax = false;
$ccp = '';
$paypal["url"]="https://www.sandbox.paypal.com/cgi-bin/webscr";
