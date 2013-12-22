<?php

/** Configuration file. Rename as config.php and make appropriate
changes.

The config-dist.php has three types of entries:

1. Uncommented variables (for instance, $sec_area). These MUST be set
   to something before going live.
2. Commented values tagged "default" (for instance $stylesheet):
   If you leave them as is they will take the commented value (for
   instance if you don't touch the entry for $stylesheet then
   $stylesheet will be set to "style.css". In other words uncommenting
   an entry marked "default" has no effects.
3. Commented values tagged "example" (for instance $titlepage) will be
   blank unless you set them to something (typically taking
   inspiration by what's given in the example). For instance if you
   don't touch $titlepage then FreeSeat will not show any header/title
   in its pages, but if you uncomment the line $titlepage =
   "myheader.php // example" then FreeSeat will include "myheader.php"
   on the top of its pages.

Copyright (C) 2010 Maxime Gamboni. See COPYING for copying/warranty
info.

$Id: config-dist.php 372 2011-08-28 10:35:23Z tendays $
**/

// note, the comment below uses two stars instead of three to avoid
// having it appear in the configuration page.
/** 0 - Plugin selection **/

// Which plugins to enable
// @type plugins
// $plugins = array('adminprint', 'bookingmap', 'htmltickets', 'remail', 'seatedit', 'showedit'); // default

/*** 1 - Webserver configuration ***/

// Secure area is used in admin mode, and normal area is used in
// normal mode. These same php files, including configuration, should
// be accessible under both. This must start with https://. If you are
// just testing FreeSeat you can have a plain http-url if you check
// the "unsecure_box" parameter below. If you want to use insecure
// connections in production, look for the relevant FAQ entry at
// http://freeseat.sf.net/support.html.
// @type string
$sec_area = 'https://example.com/reservations'; // example
// @type string
$normal_area = 'http://example.com/reservations'; // example

/** Allow admin login over unsecure connections (I need that for the
development version on my laptop that has no HTTPS server - can't
conceive of a valid reason for you to set it to true). NOTE - when
this is true, no e-mails are ever sent by the application ; instead,
they are shown on the webpage itself - again, that is when testing the
application, you do not want it in production. */
// @type boolean
// $unsecure_login = false; // default

/** Location of log file writable by freeseat. Leave blank to use
    syslog. **/
// @type string
// $logfile = "logs/freeseat.log"; // example


/*** 2 - User Interface ***/

// @no-echo
// (no-echo prevents display of this bit in the config plugin, as language selection has its very own page).
/** Choose here the interface language (named like one of the files in
   the languages directory, *without* the ".php".

   Do NOT pick "default"! If you want English, put "english".) **/
$language = "english";
// @echo

/** What stylesheet to use (relative to normal_area and secure_area). **/
// @type string
//$stylesheet = "style/general.css"; // default

/** File to be included just after the <body> tag. May be an html
include or a php document. Leave empty if you don't want any
header. **/
// @type string
// $titlepage = "myheader.php"; // example

/** Code to be added just before the </body> tag. May be an html
include or a php document. Leave empty if you don't want any footer.
**/
// @type string
// $footerpage = "myfooter.php"; // default

/** URL of an image to be displayed at the right of tickets
(sponsors, theatre company logo, etc). */
// @type string
// $ticket_logo = '/images/sponsors.png'; // default

/* How many seconds a seat is locked after having been selected. */
// @type num
 // $lockingtime = 240; // default

/** How emails should refer to this website, as in "you made a booking
on $websitename". **/
// @type string
 // $websitename = 'our booking website'; // default

/* How automatic mails are signed. */
// @type multline
 // $auto_mail_signature = "Kind Regards,\n\nThe Ticketing Department"; // default

/** Default state and country code and name, -- leave blank/unset to
    not display the fields. Refer to functions/countries.php and
    function/us_states.php for complete set of supported countries and
    US states. **/
// @type string
 // $pref_country_code = "US";  // example
// @type string
 // $pref_state_code = "PA";	// example

/** A list of countries from which you expect most of your users to
   come. The countries in this list will be shown at the top of the
   list for easy selection.

   If you use this feature you MUST set $pref_country_code as well or
   it will be ignored. */
// @type array
 // $top_countries = array("CH","FR","DE"); // example

/* This message is put in the welcome page - You are invited to
replace it by something appropriate for your application. E.g. in case
you have an information page, you may put a link to it at this
point */
// @type string
 // $helpmessage = 'For information, go to <a href="http://freeseat.sf.net" target="_blank">freeseat</a>'; // default

/** When non-empty, the contents of this variable is displayed instead
    of controls allowing people to book tickets. E.g. for taking down
    the site temporarily. (has no effects in admin-mode). You may use
    html tags in it. */
// @type string
// $take_down = 'Thanks for supporting us.<br>Booking is currently closed.<br>Come back later for news about our next show!'; // example

/** You should put a short sentence here telling the conditions for
being eligible for reduced tariff. Gets inserted in pay.php. **/
// @type string
 // $lowpriceconditions = 'Children up to sixteen year old are eligible to reduced price.'; // example

/** You may pass here an array of legal informations concerning
tickets. They will be displayed as a list at the bottom of the page
every time printable tickets are displayed. Leave empty in case you
don't have legal conditions. **/
// @type multarray
 // $legal_info = array("Except in special cases, tickets are neither refunded nor exchanged."); // example

/** How many bookings should be shown simultaneously on bookinglist.php. **/
// @type num
 // $bookings_on_a_page = 20; // default

/** Whether to display time using a 12-hour or 24-hour format.
Default (false) is 24 hours.  Set to true for 12 hours. **/
// @type boolean
 // $format_time_12hr = false; // default

/*** 3 - Database Server ***/

// Database server. Both domain name and IP address is okay, as long as the server can resolve them.
// @type string
 // $dbserv = "localhost"; // default
// database name (default, as created by tables.sql : "ticketing")
// @type string
 // $dbdb = "ticketing"; // default

/* there are three users: */

/** 1. One representing anonymous users (only able to place bookings
and readonly on theatres etc) **/
// @type string
 // $dbuser = "bookinguser"; // default
// @type password
$dbpass = "ticketing"; // example

/** 2. One representing the operator. This one is also allowed to
 * modify bookings. No password is needed, as it is provided by the
 * operator through the HTML form. **/
// @type string
// $adminuser = "bookingadmin"; // default

/** 3. One representing automated operations, such as setting tickets as paid
 upon notification from the credit card server or automatic maintenance. */
// @type string
// $systemuser = "bookingsystem"; // default
/* The System password is required for marking ticket as paid
 automatically when getting feedback from the credit card company. If
 you don't use that feature you may (and should) leave this blank. The
 password is in all cases to be provided as a parameter to cron.php. */
// @type password
$systempass = "bookingsystempassword"; // example


/*** 4 - E-mail Server ***/

// @type string
// $smtp_server     = "localhost"; // default
/** Who will be sending status mailing. */
// @type string
// $smtp_sender     = "reservations@example.com"; // default
/** What name appears in the "From" field. */
// @type string
// $sender_name     = "Booking Office"; // default
/** Where to send system panic messages. */
// @type string
$admin_mail      = "postmaster@example.com"; // example
// @type string
$smtp_helo       = "example.com"; // example
/** Set to true if your smtp server requires authentication. */
// @type boolean
// $smtp_auth       = false; // default
/** If you set smtp_auth to true, set this to your smtp username. */
// @type string
$smtp_user       = "user@example.com"; // example
/** If you set smtp_auth to true, set this to your smtp password. */
// @type password
// $smtp_pass       = "smtp-password"; // example
/* Used when running from cron, when host name is not available (why?). */
// @type string
$default_server_name = "example.com"; // example

/*** 5 - Paths ***/

/* This section specifies where uploaded files (images) are kept. The
   directory must be writable by the webserver process. The two
   following variables may be different if you use absolute paths,
   e.g. '/var/www/htdocs/reservations/files/' and
   '/reservations/files/'. NOTE THAT they MUST end in a slash no
   matter what otherwise it will not work. */
/* First, the location according to the web server. If your webserver
   is chrooted it will probably look more like
   '/htdocs/reservations/files/'. */
// @type string
// $upload_path = 'files/'; // default
/* Now the location as accessed in an href= tag. */
// @type string
// $upload_url = 'files/'; // default


/*** 6 - Money ***/

/** Name or abbreviation of preferred currency to display 
Could be for example 'CHF' or 'USD' or 'dollars' or '$'. **/
// @type string
// $currency = 'dollars'; // example

/** The inverse of the smallest money unit you require, i.e. all
    money amounts must be of the form

    $some_integer_number / $moneyfactor. The default 100 is correct
    for all currencies I know of, i.e. all prices are of the form
    xxxxxx.yy. */
// @type num
// $moneyfactor = 100; // default

// If postal payment should compute extra tax. Note that the actual
// computed value is valid for Swiss post, as of October 2010. In case
// you need this feature, you probably want to change the code in
// money.php/function postaltax().
// @type boolean
// $postaltax = false; // default

/* Postal account number. You may leave blank in case you don't offer
 this kind of payment (ccp means "Compte de Cheques Postaux" - that's
 the French Swiss name for postal money transfer). */
// @type string
// $ccp = 'CCP 12-345678-9 Our-Company-Name-Comes-Here (City)'; // example

?>
