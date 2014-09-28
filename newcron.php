<?php namespace freeseat;

/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.

$Id: cron.php 388 2012-03-30 21:07:47Z tendays $
*/

/** This does the following :
 * 1 - clean up lock_seats from expired locks (has no other effect
 * than performance though)
 *
 * 2 - send reminders and cancel old unpaid bookings
 **/

function freeseat_cron() {
	global $wpdb;
	
	prepare_log("cronjob");
	$now = time();
	
	/* 1 - clean up seat_locks */

	$q = "delete from seat_locks where until < ".$now;
	$output = $q."\n";
	freeseat_query($q);
	$q = "delete from booking where state=".ST_DELETED." and firstname='Disabled' and lastname='Seat'";
	$output .= $q."\n";
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

	/* 3 - delete very old bookings (note, $c["Xdelay_Y"] are days
	 * so we multiply by number of seconds in a day **/
	foreach (array(PAY_CCARD => date("Y-m-d H:i:s",$now-86400*$c["paydelay_ccard"]),
		PAY_POSTAL => date("Y-m-d H:i:s",sub_open_time($now,86400*$c["paydelay_post"]))) as $val => $dl) {
		$output .= "\nDeleting ";
		$output .= ("state=".ST_SHAKEN." and '$dl' > timestamp and payment=$val");
		$toexpire = get_bookings("state=".ST_SHAKEN." and '$dl' > timestamp and payment=$val", "shows.date, shows.time, booking.id");

		if ($toexpire) {
			foreach ($toexpire as $n => $bk) {
				set_book_status($bk,ST_DELETED);
				$del_count ++;
			}
		}
	}

	/* 4 - now for bookings that have not been deleted, shake the ones that are fairly old */
	foreach (array(PAY_CCARD => date("Y-m-d H:i:s",$now-86400*$c["shakedelay_ccard"]),
		PAY_POSTAL => date("Y-m-d H:i:s",sub_open_time($now,86400*$c["shakedelay_post"]))) as $val => $dl) {
		$output .= "\nShaking ";
		$output .= ("state=".ST_BOOKED." and '$dl' > timestamp and payment=$val");
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
		$output .= "\nDelete $del_count ";
	if ($shake_count)
		$output .= "\nShake $shake_count ";
	if ($mail_count)
		$output .= "\nMail $mail_count";
	
	// expire posts after all dates have passed
	$sql = "select distinct spectacle from shows where date > curdate()";
	$blob = fetch_all($sql);
	$list = array();
	foreach($blob as $item) {
		$list[] = $item['spectacle'];
	}
	$query = "SELECT * FROM $wpdb->posts WHERE $wpdb->posts.post_name LIKE 'freeseat_%' ";
	$posts = $wpdb->get_results($query);
	
	foreach ($posts as $post) {
		$pid = $post->ID;
		$n = $post->post_name;
		$t = explode("_",$n);
		$sid = $t[1];
		if (!in_array($sid,$list)) {
			$newpost = array(
				'ID'  => $pid,
				'post_status' => 'draft'
			);
			wp_update_post($newpost);
		}
	}
	do_hook('cron');
	$output .= "\nDone.";
	sys_log( $output );
	echo $output;
	log_done();
}

add_action( 'init', __NAMESPACE__ . '\\freeseat_setup_schedule' );

/**
 * On an early action hook, check if the hook is scheduled - if not, schedule it.
 */
function freeseat_setup_schedule() {
	if ( ! $next = wp_next_scheduled( 'freeseat_hourly_event' ) ) {
		wp_schedule_event( time(), 'hourly', 'freeseat_hourly_event');
	}
}

add_action( 'freeseat_hourly_event', __NAMESPACE__ . '\\freeseat_cron' );
