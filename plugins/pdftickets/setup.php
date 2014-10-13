<?php namespace freeseat;

if (!class_exists('DOMPDF')) {
	require_once( "dompdf/dompdf_config.inc.php" );
}
$dompdf = new \DOMPDF();

/** pdftickets/setup.php
  *
  * Copyright (c) 2010 by twowheeler
  * Licensed under the GNU GPL 2. For full terms see the file COPYING.
  *
  * Render tickets in pdf.
  *
  * $Id$
  *
  */

/*
 *  Rewritten for Wordpress, with everything in this file.
 *  In WP, we have to prevent any output prior to issuing the header
 *  call for the download.  That is done with ob_start() hooked on 
 *  the add_action('init') call.
 *  
 *  This plugin stores a series of tickets into SESSION["pdftickets"], 
 *  and on a second pass returns here to generate the pdf file or
 *  email the tickets to the user if an email address is present. 
 */
function freeseat_plugin_init_pdftickets() {
    global $freeseat_plugin_hooks;
    
    $freeseat_plugin_hooks['ticket_prepare']['pdftickets'] = 'pdftickets_prepare';
    $freeseat_plugin_hooks['ticket_render']['pdftickets'] = 'pdftickets_render';
    $freeseat_plugin_hooks['ticket_finalise']['pdftickets'] = 'pdftickets_finalise';
    $freeseat_plugin_hooks['kill_booking_done']['pdftickets'] = 'pdftickets_cleanup';
    add_action( 'init', __NAMESPACE__ . '\\freeseat_pdftickets_redirect' );
    init_language('pdftickets');
}

function pdftickets_prepare() {}

function pdftickets_render($booking) {}

function pdftickets_finalise() {
	global $lang, $page_url, $dompdf;

	if (isset($_GET['freeseat-ticket-mode'])) {
		// create the html for the ticket output
		if ( !isset($_SESSION['pdftickets'] )) {
			$_SESSION['pdftickets'] = pdftickets_maketickets();
		}
		$html = $_SESSION['pdftickets']; 

		/* Now convert $html to $pdf */
		$dompdf = new \DOMPDF();  
		$dompdf->load_html($html);
		$dompdf->render();
		if ( $_GET["freeseat-ticket-mode"] == 'pdf-mail' && !isset($_SESSION['pdftickets_emailsent'])) {
			// ready to send by email
			$pdf = $dompdf->output();
			pdftickets_sendtickets($pdf);
		} elseif ($_GET["freeseat-ticket-mode"] == 'pdf-file' ) {
			// ready to download
			ob_end_clean();
			$dompdf->stream( 'mytickets.pdf' );
			exit();
		}
	}
	// display the page to the user
	echo "<div class='dontprint'>";
	echo "<h2>".$lang['pdftickets_thankyou']."</h2><p class='main'></p>";  
	echo "<p class='main'><table><tr><td>";
	echo "<div id='download-image'>";
	echo "<img src='".plugins_url( "down.png", __FILE__ )."'></div></td><td><p>";
	printf($lang["pdftickets_download_link"],'[<a href="'.replace_fsp($page_url,PAGE_FINISH).'&freeseat-ticket-mode=pdf-file">','</a>]');
	echo '</p>';
	if (isset($_SESSION['email']) && is_email_ok($_SESSION['email'])) {
		echo '<p>';
		printf($lang["pdftickets_email_link"],'[<a href="'.replace_fsp($page_url,PAGE_FINISH).'&freeseat-ticket-mode=pdf-mail">','</a>]',$_SESSION['email'])."<div id='mailsent'></div>";
		if (isset($_SESSION['pdftickets_emailsent'])) echo "&nbsp;&nbsp;&nbsp;<b> Sent!</b>";
		echo '</p>';
	}
	echo "</td></tr></table></p></div>";
}

function pdftickets_maketickets() {
	global $upload_url, $ticket_logo, $lang, $legal_info;
	
	$allbookings = $_SESSION['seats'];	 
	// how many shows are there?
	$showids = array();
	foreach ($allbookings as $n => $s) {
		$showids[$s['showid']][] = $n;
	}
	if (count($showids) == 0)
		// we shouldn't get here, but just in case
		sys_log("Error: no showids found in pdfticket/setup.php");
		
	// start accumulating html with the header and css
	$html = "<html><head><style>";
	$html .= file_get_contents( FS_PATH . "plugins/pdftickets/ticket.css", FILE_USE_INCLUDE_PATH);
	$html .= "</style></head><body>";
	// create a separate page with tickets for each show in the pdf
	// tickets.css should have a div.wide { page-break-after: always; }
	foreach ($showids as $sid => $sarray) {
		$showbookings = array();
		foreach ($sarray as $n) {
			$showbookings[] = $allbookings[$n];
		}
		$sp = get_spectacle( $showbookings[0]["spectacleid"] );
		if ($sp['imagesrc']) {
			$logo = $sp['imagesrc'];
		} else {
			$logo = freeseat_url($ticket_logo);
		}
		$html .= "<div class='wide'><p class='highlight'>".$lang["intro_finish"]."</p>";
		$html .= "<div class='ticket2'>";
		$html .= "<table><tr><td><div class='title'>";
		$html .= "<p class='temph'>" . $sp['name'] . "</p></div>";
		$html .= "<p>".show_info(get_show($sid))."</p>";
		$html .= "<td><div class='title'><img alt='Logo' width='200' src='$logo'></div></td>";
		$html .= "<td>";
		$html .= do_hook_concat('booking_return',$showbookings[0]);
		$html .= "</td></tr></table>";
		$html .= "<p class='main'><b>".$lang["name"].": </b>".$_SESSION["firstname"]." ".$_SESSION["lastname"]."</p>";
		if (get_total() > 0) {
			$html .= "<p class='main'><b>".$lang["payment"].": </b>".f_payment($_SESSION["payment"])."</p>";
		}
		$html .= "<p class='main'>";
		$html .= print_booked_seats($showbookings,FMT_SHOWID|FMT_PRICE|FMT_HTML|FMT_NOCOUNT);
		$html .= "</p><p class='main'><b>".$lang["mail-thankee"]."</b></p>";
		$html .= "<p class='main'>";
		foreach ($legal_info as $n => $line) $html .= $line . "<br>";
		$html .= "</p></div></div>";
	} // end of foreach showid
	$html .= "</body></html>";
	return $html;
}

function pdftickets_sendtickets($pdf) {
	global $smtp_sender, $lang;
	// write contents to file
	$attach_path = FS_PATH . "files/mytickets" . $_SESSION["groupid"] . ".pdf" ;
	$eml = $_SESSION['email'];
	sys_log("User emailing tickets to $eml");
	file_put_contents($attach_path,$pdf);
	// email file to user 
	$result = send_message($smtp_sender,$eml,$lang['pdftickets_subject'],$lang['pdftickets_body'],$attach_path);
	// set a session var so we only mail them once
	if ($result)
		$_SESSION['pdftickets_emailsent'] = true;
	else
		unset($_SESSION['pdftickets_emailsent']);
}

function freeseat_pdftickets_redirect() {
	if( isset( $_REQUEST['freeseat-ticket-mode'] ) && $_REQUEST['freeseat-ticket-mode'] == 'pdf-file' ) {
		// In order to download a file to the user, we have to hook to wordpress
		// prior to any output being issued.  This call will suppress output until we are ready.
		ob_start();
    }
}

function pdftickets_cleanup() {
	// clear session variables after use
	unset($_SESSION['pdftickets']);
	unset($_SESSION['pdftickets_emailsent']);
}

