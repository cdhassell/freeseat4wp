<?php

  /** barcode/setup.php
   *
   * Copyright (c) 2010 by Leif Harmsen and Maxime Gamboni
   * Licensed under the GNU GPL 2. For full terms see the file COPYING.
   *
   * Puts a barcode on tickets and interfaces with USB barcode readers
   *
   * $Id$
   *
   */

$FS_PATH = plugin_dir_path( __FILE__ ) . '../../';

function freeseat_plugin_init_barcode() {
    global $freeseat_plugin_hooks;

    $freeseat_plugin_hooks['ticket_left']['barcode'] = 'barcode_ticket_left';
    $freeseat_plugin_hooks['bookinglist_pagebottom']['barcode'] = 'barcode_link';
    $freeseat_plugin_hooks['booking_return']['barcode'] = 'barcode_forpdf';

    /* This one isn't used yet. */
    //    $freeseat_plugin_hooks['ticket_render']['barcode'] = 'barcode_ticket';
}

function barcode_link() {
  global $filtershow;
  if ($filtershow) {
    echo "<ul><li><p class='main'><a href='plugins/barcode/?id=" . $filtershow . "' target='barcodescanner'>Scan Tickets</a> for this show.</p></ul>";
  }
}

function barcode_ticket_left($booking) {
  echo "<img height=150 src='plugins/barcode/barcode.php?code="
    .str_pad($booking["bookid"],12,"0",STR_PAD_LEFT)
    ."&amp;encoding=EAN&amp;scale=2&amp;mode=png'>";

  return false;
}

function barcode_ticket($booking) {
 global $ticket_logo;
 $ticketnumber = str_pad($booking["bookid"],6,"0",STR_PAD_LEFT);
 $initials = substr($booking["firstname"],0,1) .
   substr($booking["lastname"],0,1);
 $sh = get_show($booking["showid"]);
 $spec = get_spectacle($sh["spectacleid"]);
?>
<div class='ticket'>
<table width=750 height=150 cellpadding=0 cellspacing=0 border=1 bgcolor=ffffff ><tr><td>
<table width=100% height=150 cellpadding=0 cellspacing=0 border=0 bgcolor=ffffff >
<tr><td valign=top border=0>
   <?php 
   barcode_ticket_left($booking);
 echo "</td>";
 echo "<td align=center valign=middle border=0>";
 echo "<span class='tickettitle'>" . htmlspecialchars($spec["name"]) . "</span><br>";
 echo "<span class='ticketdate'>";
 show_show_info($sh, false);
 echo '</span><br>';
 /* <tendays> that line had a $transid, but that isn't defined
  anywhere... Is it supposed to be the credit card transaction id? */
 echo "<span class='ticketnumber'>#$ticketnumber-$initials "
   . $booking["firstname"] . "&nbsp;" . $booking["lastname"] . '</span><br>';
 $oneline = array($booking);
 echo "<span class='ticketseating'>" . print_booked_seats($oneline,FMT_NOCOUNT|FMT_PRICE|FMT_HTML) . '</span>';
?>
 </td><td valign=center align=right><img src='<?php echo $FS_PATH . $ticket_logo; ?>' height=150 vspace=2 hspace=2></td>
 </td></tr></table>
 </td></tr></table>
 </div>
<?php
}

function barcode_forpdf($booking) {
  // must return html that dompdf can handle
  global $upload_url;
  $bookid = $booking['bookid'];
  $url = freeseat_url("plugins/barcode/barcode.php?code=".str_pad($bookid,12,"0",STR_PAD_LEFT)."&scale=2&mode=jpg");
  $barcode = file_get_contents($url);
  if (!$barcode) {
    return "<p class='temph'>$bookid</p>";
  } else {
    $filename = $upload_url . "barcode$bookid.jpg";
    $fp = fopen( $FS_PATH . $filename,'w+');
    fwrite($fp,$barcode);
    fclose($fp);
    return "<img src='".freeseat_url($filename)."'>";
  }
}


