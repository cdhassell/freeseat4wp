<?php

$FS_PATH = plugin_dir_path( __FILE__ ) . '../../';

require_once ($FS_PATH . "vars.php");
require_once ($FS_PATH . "functions/format.php");

ensure_plugin('barcode');

db_connect();

if (!admin_mode()) fatal_error($lang["access_denied"]);

$sh = null;

if (isset($_REQUEST['id'])) {
  $id = (int)($_REQUEST['id']); // the show id

  // get the show info...
  $sh = get_show($id);
 }

if (!$sh) fatal_error($lang["err_showid"]);

$spectaclename = m_eval("select spectacles.name from spectacles, shows where shows.spectacle = spectacles.id and shows.id=$id");

if (isset($_REQUEST['scannerinput']))
  $scannerinput = (int)($_REQUEST['scannerinput']);
else
  $scannerinput = null;

if ($scannerinput) {
  
  $booking = get_booking($scannerinput);

  $state = $booking['state'];

  $ticketnumber = str_pad($scannerinput,6,"0",STR_PAD_LEFT);
  $showticketnumber = "Ticket# " . $ticketnumber . "-";
  $showticketnumber .= substr($booking['firstname'],0,1);
  $showticketnumber .= substr($booking['lastname'],0,1);

  $validTicket = false;

  $showid = $booking['showid']; // the id read on the ticket, to be
				// compared $id passed to the page

  if ($state == ST_PAID && $showid == $id) {
    $ticketstate = "BOOKED &amp; PAID";
    $validTicket = true;
  } else if (($state == ST_BOOKED || $state == ST_SHAKEN) && $showid == $id) {
    $ticketstate = "Booked but NOT PAID";
  } else if ($state == ST_PAID && $showid != $id) {
    $ticketstate = "Booked &amp Paid but WRONG SHOW";
  } else if (($state == ST_BOOKED || $state == ST_SHAKEN) && $showid != $id) {
    $ticketstate = "Booked but NOT PAID &amp WRONG SHOW";
  } else {
    $ticketstate = "Unknown Ticket";
  }

  $ticketstate = '<span class="'.($validTicket? "good_scan" : "bad_scan").'">'
    .$ticketstate . '</span>';

 }

show_head(false, true);
echo '<link rel="stylesheet" type="text/css" href="scan.css">';
close_head(false, 'onLoad="document.scanform.scannerinput.focus();"');

?> <h1>Barcode Scanner</h1>
Read ticket barcodes (EAN format) <?php

if ($scannerinput) {
/* Only play a sound if we got an id from the scanner */

  $sound = $validTicket? "true.wav" : "false.wav";
  echo "<audio src='$sound' autoplay=1>";
  echo "Your browser does not support the HTML5 audio element.";
  echo "</audio>";
 }

echo "<h2>$spectaclename</h2>";
show_show_info($sh,false);

?>

<form name="scanform" action="index.php?id=<?=$id?>" method="post">
  <p> Manual Input: <input type="text" name="scannerinput">
    <input type="submit" value="Enter">
  </p>
<div class="scan_result">
<?php
if ($scannerinput) { ?>
<p>
   <b><?=$showticketnumber?></b><br>
   <?=$ticketstate?>
</p><p>
   <?=$booking["firstname"]?>
   <?=$booking["lastname"]?> <a href="mailto:<?=$booking["email"]?>"><?=$booking["email"]?></a>
   <?=$booking["phone"]?><br> <?=$booking["notes"]?>
</p>
<?php } else { ?>
<p class="ready">Ready</p>
<?php } ?>
</div>
</form>
<?php
  show_foot();

