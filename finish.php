<?php namespace freeseat;

/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.

$Id: index.php 341 2011-04-25 19:03:48Z tendays $

Modifications for Wordpress are Copyright (C) 2013 twowheeler.
*/

/*
 *  End of the ticket purchase process.  If tickets are available they are
 *  downloaded to the user.  The sale is recorded as final in the database.
 *  Replaces the former finish.php
 */
function freeseat_finish( $page_url ) {
	global $lang, $messages, $sh, $auto_mail_signature, $smtp_sender, $admin_mail;
	
	prepare_log((admin_mode()?"admin":"user")." buying from ".$_SERVER["REMOTE_ADDR"]);
	// sys_log("finish session: ".print_r($_SESSION,1));

	if ((!isset($_SESSION["booking_done"])) || ($_SESSION["booking_done"]===false)) {
		// first trip through finish.php, booking is not recorded yet
		$_SESSION["groupid"] = 0;
		// process any extra parameters from confirm.php
		do_hook('confirm_process'); 
		check_session(4,true); 
		foreach ($_SESSION["seats"] as $n => $s) {
			// book each seat here with status ST_BOOKED
			if (($bookid = book($_SESSION,$s))===false) { 
				cant_book();
			} else {
				// seats are now booked, so capture the groupid
				$_SESSION["seats"][$n]["bookid"] = $bookid;
				if (!(isset($_SESSION["groupid"]) && $_SESSION["groupid"]!=0))
					$_SESSION["groupid"] = $bookid;
			}
		}
		$_SESSION["booking_done"] = ST_BOOKED;
	}
	
	if ( $_SESSION["payment"]==PAY_CCARD) {
		// returning from credit card site, check for payment
		$sql = "SELECT count(numxkp) FROM ccard_transactions WHERE groupid={$_SESSION['groupid']}";	
		if (m_eval($sql)) $_SESSION['booking_done'] = ST_PAID;
		// check_session(4); // a valid user name and payment method must exist at this point
	// } else {
	//	$_SESSION["booking_done"] = ST_PAID;
	// not sure if any of this is necessary 
	}
	
	if (($_SESSION["payment"]==PAY_CCARD) && ($_SESSION["booking_done"]!=ST_PAID) && (get_total()>0)) {
		// expecting credit card payment so display the payment form and jump to credit card site
		show_head();
		echo $lang["intro_ccard"];  // Thank you for your reservation, please wait ...
		do_hook('ccard_paymentform');  // If using a credit card, here is where we make the jump
	} else { 
		// not a credit card sale, or coming back from credit card processor (Pass #2)
		$config = get_config();
		if (($_SESSION["payment"]==PAY_CCARD) && (get_total()>0)) {
			/* if the payment is done by credit card, check if tickets have
			already been paid (they should), and only mail the user if not. */
			$bs = get_bookings("booking.groupid=".$_SESSION["groupid"]." or booking.id=".$_SESSION["groupid"]);
			if ($bs) {
				$allpaid = true;
				foreach ($bs as $n => $b) {
					if ($b["state"]!=ST_PAID) $allpaid = false;
				}
				if ($allpaid) {
					/* get_total() is non-zero but all tickets are marked PAID
					then a thank you/confirmation message has already been sent
					by set_book_status/send_notifs. */
					$_SESSION["mail_sent"] = true;
				}
			} else {
				/* the correct value for allpaid is not known because things
				went wrong. I think it's best to set it to FALSE as (I think)
	 			it's best to tell people they should pay when actually they
				don't, than not tell them when they should. */
				$allpaid = false;
				myboom();
			}
		} else { /* not paying by ccard, or all tickets free. */
			$allpaid = (get_total() == 0);
		}
		// make the ticket output page
		show_head(true);
		/* Ticket-printing plugins may request to override ticket rendering
		from other plugins by implementing the _override hooks below, and
		returning true in ticket_prepare_override. Most ticket printing
		routines should only implement the non-override hooks. Of course if
		more than one plugin requests overriding ticket rendering, all such
		plugins will be run side by side. */
		$hide_tickets = do_hook_exists('ticket_prepare_override');
		foreach ($_SESSION["seats"] as $n => $s) {
			do_hook_function('ticket_render_override', array_union($_SESSION,$s));
		}
		do_hook('ticket_finalise_override');
		if (!$hide_tickets) {
			do_hook('ticket_prepare');
			foreach ($_SESSION["seats"] as $n => $s) {
				do_hook_function('ticket_render', array_union($_SESSION,$s));
			}
			do_hook('ticket_finalise');
		}
		do_hook('finish_end');
		echo '<p class="main"><b>'.$lang["mail-thankee"].'</b></p>';
		$seats_copy = $_SESSION["seats"];
		$top = array_shift( $seats_copy );
		// sys_log("top = " . print_r($top,1));
		$spec = get_spectacle( $top["spectacleid"] );
			
		/* Now send a confirmation message if that hasn't been done already. */
		if (($_SESSION["email"]!="") && (!isset($_SESSION["mail_sent"]))) {
			$body  = sprintf($lang["mail-booked"],$spec["name"]);
			$body .= $lang["name"].": ".$_SESSION["firstname"]." ".$_SESSION["lastname"]."\n";
			$body .= "\n";
			// TODO - BUG - $_SESSION["seats"] don't have the correct
			// date/time/theatrename fields
			$body .= print_booked_seats(null,FMT_PRICE|FMT_SHOWID|FMT_SHOWINFO);
			$body .= "\n";
			if (!$allpaid) {
				$body .= $lang["mail-notconfirmed"];
				if ($_SESSION["payment"] != PAY_CASH) {
					$body .= $lang["mail-secondmail"];
				}
				$body .= "\n";
				// TODO - show exactly the same stuff both in mail and in page, and code it only once .. 
				switch ($_SESSION["payment"]) {
					case PAY_POSTAL:
						$body .= sprintf($lang["payinfo_postal"],$ccp,$config["shakedelay_post"]);
						break;
					case PAY_CCARD:
						$body .= sprintf($lang["payinfo_ccard"],$config["shakedelay_ccard"]);
						break;
					case PAY_CASH:
						$body .= $lang["payinfo_cash"];
						break;
				}
			}
			$body.=$lang["mail-thankee"]."\n$auto_mail_signature\n";
			send_message($smtp_sender,$_SESSION["email"],$lang["mail-sub-booked"],$body);
			$_SESSION["mail_sent"] = true; // avoid sending mail every time the user clicks reload
			//  echo $messages;
			echo '<p class="main">'.$lang["mail-sent"].'</p>';
		}
		print_legal_info();
		if (!$allpaid) {
			echo '<p class="main">'.$lang["mail-notconfirmed"].'</p>';
			// Now display some information about how to pay 
			echo '<p class="bwemph">'; 
			switch ($_SESSION["payment"]) { // "other" (i.e. not covered in the
				case PAY_POSTAL:	           // below list)
					printf($lang["payinfo_postal"],$ccp,$config["shakedelay_post"]);
					break;
				case PAY_CCARD:
					printf($lang["payinfo_ccard"],$config["shakedelay_ccard"]);
					break;
				case PAY_CASH:
					echo $lang["payinfo_cash"];
					break;
			}
			echo '</p>';
		}
		echo '<div class="dontprint"><p class="main">';
		$url = replace_fsp( $page_url, PAGE_REPR );
		$url = add_query_arg( 'spectacleid', $spec['id'], $url ); 
		printf($lang["bookagain"],"[<a href='$url'>","</a>]");
		echo '</p></div>';
	} // end of block run when not credit card or already gone through credit card processor
	show_foot();
	log_done();
}	// end of freeseat_finish

function cant_book() {
	global $smtp_sender, $admin_mail, $lang;
	// booking failed so send message to admin and bail
	$body = " \$_SESSION = \n".print_r($_SESSION,true)." \$messages = \n";
	$body .= flush_messages_text();
	send_message( $smtp_sender, $admin_mail, "PANIC", $body );
	show_head(true);
	echo $lang["panic"];
	show_foot();
	log_done();
	exit;
}