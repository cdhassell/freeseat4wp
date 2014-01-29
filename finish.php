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
function freeseat_finish( $page_url )
{
	global $lang, $messages, $sh, $auto_mail_signature, $smtp_sender;
	prepare_log((admin_mode()?"admin":"user")." buying from ".$_SERVER["REMOTE_ADDR"]);
	
	$sh = get_show($_SESSION["showid"]);
	$spec = get_spectacle($sh["spectacleid"]);
	
	//$bookid = 0;
	
	if ((!isset($_SESSION["booking_done"])) || ($_SESSION["booking_done"]===false)) {
	
	  $_SESSION["groupid"] = 0;
	
	  do_hook('confirm_process'); // process any extra parameters from confirm.php
	
	  check_session(4,true);
	  
	  foreach ($_SESSION["seats"] as $n => $s) {
	    /* $_GET["panic"] is for debugging purposes */
	    if (($bookid = book($_SESSION,$s))===false) { // || $_GET["panic"]=="NOW") {
	      /* okay, now what?? :-( */
	      
	      $body  = " \$_SESSION = \n";
	      $body .= print_r($_SESSION,true);
	      $body .= " \$messages = \n";
	      $body .= flush_messages_text();
	      
	      send_message($smtp_sender,$admin_mail,"PANIC",$body);
	      
	      show_head(true);
	
	      echo $lang["panic"];
	      
	      show_foot();
	      log_done();
	      exit;
	    } else {
	      $_SESSION["seats"][$n]["bookid"] = $bookid;
	      if (!(isset($_SESSION["groupid"]) && $_SESSION["groupid"]!=0))
		$_SESSION["groupid"] = $bookid;
	    }
	  }
	
	  $_SESSION["booking_done"] = ST_BOOKED;
	
	} else check_session(4);
	
	if (isset($_GET["ok"])) {
	  if ($_GET["ok"]=="yes") {
	    $_SESSION["booking_done"] = ST_PAID;
	  } else {
	    kaboom(sprintf($lang["err_ccard_user"],$smtp_sender));
	  }
	}
	
	if (($_SESSION["payment"]==PAY_CCARD) && ($_SESSION["booking_done"]!=ST_PAID)
	  && (get_total()>0)) {
	
	  show_head();
	
	  echo $lang["intro_ccard"];
	  
	  do_hook('ccard_paymentform');
	
	} else { // not credit card or coming back from credit card processor
	
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
	
	    /* TODO - show exactly the same stuff both in mail and in page,
	 and code it only once .. */ 
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
	
	  $body.=$lang["mail-thankee"];
	
	  $body.= "\n";
	  $body.= "$auto_mail_signature\n";
	
	  send_message($smtp_sender,$_SESSION["email"],$lang["mail-sub-booked"],$body);
	  $_SESSION["mail_sent"] = true; // last minute kludge to avoid sending the mail every time the user clicks reload
	  //  echo $messages;
	  echo '<p class="main">'.$lang["mail-sent"].'</p>';
	} 
	 print_legal_info();
	 if (!$allpaid) {
	   echo '<p class="main">'.$lang["mail-notconfirmed"].'</p>';
	
	/* Now display some information about how to pay */
	   echo '<p class="bwemph">'; // note, we will have an empty <p></p>
				      // in case payment mode is chosen to be
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
	 $url = add_query_arg( array('fsp' => PAGE_REPR, 'spectacleid' => $spec['id'] ), $page_url ); 
	 printf($lang["bookagain"],"[<a href='$url'>","</a>]");
	 echo '</p></div>';
	
	} // end of block run when not credit card or already gone through
	     // credit card processor
	
	show_foot();
	log_done();
}	// end of freeseat_finish

