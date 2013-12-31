<?php


function freeseat_plugin_init_extendbooking() {
    global $freeseat_plugin_hooks;

    $freeseat_plugin_hooks['bookinglist_process']['extendbooking'] = 'extendbooking_process';
    $freeseat_plugin_hooks['bookinglist_pagebottom']['extendbooking'] = 'extendbooking_button';  
    init_language('extendbooking');    
}

function extendbooking_process() {
  global $ab;

  if (count($ab) && isset($_POST["extend"])) {
    // process parameters from bookinglist
    foreach ($ab as $booking) {
      $st = $booking['state'];
      $id = $booking['bookid'];
      if ($st == ST_SHAKEN || $st == ST_BOOKED) {
        $sql = "update booking set timestamp=NOW(), state=".ST_BOOKED." where id=$id";
        if (!freeseat_query($sql)) myboom();
      }
    }
  }
}

function extendbooking_button() {
	echo ' <input class="button button-primary" type="submit" name="extend" value="'.__('Extend Expiration').'" />';
}

