#!/usr/bin/php5
<?php namespace freeseat;

// import wordpress stuff
require_once( '../../../wp-load.php' );

// All paths in FreeSeat are relative to FS_PATH
//define( 'FS_PATH', plugin_dir_path( __FILE__ ) );

// import freeseat stuff
require_once (FS_PATH . "vars.php");

require_once (FS_PATH . "functions/booking.php");
require_once (FS_PATH . "functions/format.php");
require_once (FS_PATH . "functions/session.php");
require_once (FS_PATH . "functions/send.php");
require_once (FS_PATH . "functions/tools.php");
require_once (FS_PATH . "functions/plugins.php");

// import code to access freeseat options
require_once( FS_PATH . "options.php" );
// Get the global variables from the database
$freeseat_vars = get_config();
if ( is_array($freeseat_vars) ) {
	foreach ( $freeseat_vars as $var => $value ) {
		if (false === strpos($var,'chk_')) {
			$$var = $value;
		}	
	}
}

/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.

$Id: cron.php 388 2012-03-30 21:07:47Z tendays $
*/

/** This is meant to be ran by a nightly cron job and does the following :
 * 1 - clean up lock_seats from expired locks (has no other effect
 * than performance though)
 *
 * 2 - send reminders and cancel old unpaid bookings
 **/

if (isset($_SERVER["REQUEST_METHOD"]) && !$unsecure_login) {
	db_connect(); // (just to make sure the admin state is written properly)
	fatal_error($lang["err_shellonly"]);
} else {

	prepare_log("cronjob");

	/* 1 - clean up seat_locks */

	$q = "delete from seat_locks where until < ".$now;
	print $q."\n";
	freeseat_query($q);
	$q = "delete from booking where state=".ST_DELETED." and firstname='Disabled' and lastname='Seat'";
	print $q."\n";
	freeseat_query($q);

	/* 2 - send reminders+cancellation notices */

	$c = get_config();

	start_notifs();

	$del_count = 0;
	$shake_count = 0;

	/* We use strict timestamp comparison because it works on dates. So	
	e.g. exactly paydelay+1 days after the day it was made on, the
	booking is cancelled. The +1 is because cron is meant to be ran
	shortly *after* midnight. */

	//  $now = time();
	/** First delete very old bookings (note, $c["Xdelay_Y"] are days
	 * so we multiply by number of seconds in a day **/
	foreach (array(PAY_CCARD => date("Y-m-d H:i:s",$now-86400*$c["paydelay_ccard"]),
		PAY_POSTAL => date("Y-m-d H:i:s",sub_open_time($now,86400*$c["paydelay_post"]))) as $val => $dl) {
		echo "\ndeleting\n";
		echo ("state=".ST_SHAKEN." and '$dl' > timestamp and payment=$val");
		$toexpire = get_bookings("state=".ST_SHAKEN." and '$dl' > timestamp and payment=$val", "shows.date, shows.time, booking.id");

		if ($toexpire) {
			foreach ($toexpire as $n => $bk) {
				set_book_status($bk,ST_DELETED);
				$del_count ++;
			}
		}
	}

	/** Now for bookings that have not been deleted, shake the ones that are fairly old */
	foreach (array(PAY_CCARD => date("Y-m-d H:i:s",$now-86400*$c["shakedelay_ccard"]),
		PAY_POSTAL => date("Y-m-d H:i:s",sub_open_time($now,86400*$c["shakedelay_post"]))) as $val => $dl) {
		echo "\nshaking\n";
		echo ("state=".ST_BOOKED." and '$dl' > timestamp and payment=$val");
		$toshake = get_bookings("state=".ST_BOOKED." and '$dl' > timestamp and payment=$val", "shows.date, shows.time, booking.id");
		if ($toshake) {
			foreach ($toshake as $n => $bk) {
				set_book_status($bk,ST_SHAKEN);
				$shake_count ++;;
			}
		}
	}
	$mail_count = send_notifs();

	if ($del_count)
		echo "delete $del_count ";
	if ($shake_count)
		echo "shake $shake_count ";
	if ($mail_count)
		echo "mail $mail_count";

	do_hook('cron');
	echo "\nDone.\n";
	log_done();
}

?>
