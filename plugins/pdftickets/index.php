<?php


// this code is called to download a PDF file to the user

db_connect();

ensure_plugin('pdftickets');

if (!isset($_GET["key"])) {
  /* This typically happens if the user opens the plugin directory in
   his browser. */
  fatal_error('This page is not meant to be accessed directly');
 }

$key = (int)($_GET["key"]);

if (!(isset($_SESSION["pdftickets"]) && isset($_SESSION["pdftickets"][$key]))) {
  /* This typically happens if the user's session expired and he tried
   to load his tickets once more. Should maybe let the remail plugin
   put a message that the user may retrieve his tickets through the
   frontpage remail link.. */
  fatal_error('Sorry, your ticket reference expired.');
 }

$allbookings = $_SESSION["pdftickets"][$key];

/*  We get here if this is the first ticket to be output.
 *  For PDF output we need to accumulate all of the html into one variable 
 *  and then call $dompdf->render(). This code stores the page in a global 
 *  variable $pdf_ticket as it is developed.
 *  Dompdf wants to get a complete page, including the <html>,<head> and
 *  <body> tags, so we can't rely on any functions like show_head(), 
 *  show_foot(), show_show_info(), etc. which echo to the screen.
 *  Dompdf supports only a subset of CSS2, so we have to be careful about 
 *  our markup.  For example, it does not handle floats well.  This code 
 *  expects to copy a file called ticket.css into the ticket page.  
 *  Also, be aware that dompdf can't handle .png images, so stick to jpeg.
 */
 
// how many shows are there?
$showids = array();
foreach ($allbookings as $n => $s) {
  $showids[$s['showid']][] = $n;
}
if (count($showids) == 0)
  // we shouldn't get here, but just in case
  sys_log("Error: no showids found in pdfticket/index.php");

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
  // $prices = get_spec_prices( $sp );
  // $cls = $showbookings[0]["class"];
  // $desc = m_eval("select description from class_comment where spectacle=$sp and class=$cls");
  if ($sp['imagesrc']) {
    $logo = freeseat_url($upload_url . $sp['imagesrc']);
  } else {
    $logo = freeseat_url($ticket_logo);
  }
  $html .= "<div class='wide'><p class='highlight'>".$lang["intro_finish"]."</p>";
  $html .= "<div class='ticket2'>";
  $html .= "<table><tr><td><div class='title'>";
  $html .= "<p class='temph'>" . $sp['name'] . "</p></div>";
  $html .= "<p>".show_info(get_show($sid))."</p>";
  $html .= "<td><div class='title'><img alt='Logo' src='$logo'></div></td>";
  $html .= "<td>";
  $html .= do_hook_concat('booking_return',$showbookings[0]);
  $html .= "</td></tr></table>";
  $html .= "<p class='main'><b>".$lang["name"].": </b>".$showbookings[0]["firstname"]." ".$showbookings[0]["lastname"]."</p>";
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

    
/* Now convert $html to $pdf */
$dompdf = new DOMPDF();  
$dompdf->load_html($html);
$dompdf->render();
$pdf = $dompdf->output();

if (isset($_GET["mode"]) && strcmp($_GET["mode"],'mail')==0) {
  // write contents to file
  $attach_path = FS_PATH . "files/mytickets" . $_SESSION["groupid"] . ".pdf" );
  $eml = $_SESSION['email'];
  sys_log("User emailing tickets to $eml");
  file_put_contents($attach_path,$pdf);
  // email file to user 
  $result = send_message($smtp_sender,$eml,$lang['pdftickets_subject'],$lang['pdftickets_body'],$attach_path);
  if ($result)
    $_SESSION['pdftickets_emailsent'] = true;
  else
    unset($_SESSION['pdftickets_emailsent']);
  // make sure we have a showid
  if (!isset($_SESSION["showid"])) {
    $_SESSION["showid"] = array_shift(array_keys($showids));
    // or $_SESSION["showid"] = $allbookings[0]["showid"];
  }
  // jump back to finish page
  header("Location: ".freeseat_url("finish.php"));
} else {
  sys_log("User downloading tickets");
  // fix for IE catching or PHP bug issue
  header("Pragma: public");
  // set expiration time
  header("Expires: 0");
  header("Content-Type: application/pdf");
  header("Content-Type: application/download");
  header('Content-Disposition: attachment; filename=mytickets.pdf');
  echo $pdf;
}

