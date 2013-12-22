<?php

$FS_PATH = plugin_dir_path( __FILE__ ) . '../../';

require_once ( $FS_PATH . "functions/plugins.php" );
require_once ( $FS_PATH . "functions/money.php" );
require_once ( $FS_PATH . "functions/spectacle.php" );
require_once ( $FS_PATH . 'plugins/config/functions.php' );

  /** bocatickets/setup.php
   *
   * Copyright (c) 2010 by twowheeler
   * Licensed under the GNU GPL 2. For full terms see the file COPYING.
   *
   * Render tickets for a Boca ticket printer.
   *
   * $Id$
   *
   */

/* 
 * May 2013 - twowheeler
 * Added JS to use jzebra applet to do raw printing
 * See http://code.google.com/p/jzebra/
 * 
 */

function freeseat_plugin_init_bocatickets() {
  global $freeseat_plugin_hooks;

  $freeseat_plugin_hooks['config_form']['bocatickets'] = 'bocatickets_config_form';

  $freeseat_plugin_hooks['ticket_prepare_override']['bocatickets'] = 'bocatickets_start';
  $freeseat_plugin_hooks['ticket_render_override']['bocatickets'] = 'bocatickets_body';
  $freeseat_plugin_hooks['ticket_finalise_override']['bocatickets'] = 'bocatickets_end';
    
  $freeseat_plugin_hooks['confirm_bottom']['bocatickets'] = 'bocatickets_checkbox';
  $freeseat_plugin_hooks['confirm_process']['bocatickets'] = 'bocatickets_process';

  $freeseat_plugin_hooks['adminprint_line']['bocatickets'] = 'bocatickets_checkbox';
  $freeseat_plugin_hooks['adminprint_process']['bocatickets'] = 'bocatickets_process';
  
  // init_language('');
}

function bocatickets_config_form($form) {
  return config_form('plugins/bocatickets/config-dist.php', $form);
}

function bocatickets_start() {
  if (isset($_SESSION['boca']) && $_SESSION['boca']) {
    echo "</div>";  // close the regular page div
    return true;    // return true to suppress other ticket output 
  }
}    

function bocatickets_body($booking) {
  global $tickettext_opening,$tickettext_closing,$lang,$legal_info,$currency;
  if (!isset($_SESSION['boca']) || !$_SESSION['boca']) return;
  /*	Prints one ticket in a standard format on a Boca ghostscript ticket printer
    Boca FGL21 printer commands used here:
    PURGE PRINTER OF REMAINING TICKETS COMMAND - <PP>

    ROW/COLUMN COMMAND - <RCx,y>

    HEIGHT/WIDTH COMMAND - <HWx,y>

    FONT SIZE COMMANDS -	
			<F1>	Font1 characters (5x7)
			<F2>	Font2 characters (8x16)
			<F3>	OCRB (17x31)
			<F4>	OCRA (5x9)
			<F6>	large OCRB (30x52)
			<F7>	OCRA (15x29)
			<F8>	Courier (20x40)(20x33)
			<F9>	small OCRB (13x20)
			<F10>	Prestige (25x41)
			<F11>	Script (25x49)
			<F12>	Orator (46x91)
			<F13>	Courier (20x40)(20X42)

    ROTATION COMMAND -	<NR> No rotation
			<RR> Rotate right (+90)

    BOXSIZE COMMAND - <BSx,y>

    LINE THICKNESS COMMAND - <LT#>

    NORMAL PRINT / CUT COMMAND - 0CH (FF) or <p>
    */

  $sp = $booking["spectacleid"];
  $cat = $booking["cat"];
  $pay = f_payment($booking["payment"]);  //?
  $cls = $booking["class"];
  $class = $lang["class"] . " " . $cls;
  $prices = get_spec_prices( $sp );
  $com = $prices[$cls]['comment'];
  $classlong = centerin( "$class $com", 45 );	// for F9 font
  $price = $currency . price_to_string($prices[$cls][$cat]);
  $seat = ($booking["row"]!=-1 ? $booking["col"] : "GEN");
  $row = ($booking["row"]!=-1 ? $booking["row"] : "ADM");
  $sh = get_show($booking["showid"]);  //?
  $date = f_date($sh["date"]) . " " . f_time($sh["time"]);	
  $datelong = centerin( $date, 46 );		// for F9 font
  if (isset($booking["id"])) $bookid =  $booking["id"];
  elseif (isset($booking["bookid"])) $bookid = $booking["bookid"];
  $spectacle = get_spectacle($sp);
  $name = $spectacle['name'];	
  $namelong = centerin( $name, 32);	// for F3 font

  literal("<PP><RC5,5><LT4><BX380,880>");			// big box
  literal("<RC50,75><HW1,1><F2>".$lang["col"]);	// Seat
  literal("<RC74,75><HW2,2><F2>$seat");				// Seat number
  literal("<RC68,34><LT2><BX36,140>");			// small box
  literal("<RC138,75><HW1,1>".$lang["row"]);			// Row
  literal("<RC162,75><HW2,2>$row");						// Row number
  literal("<RC156,34><LT2><BX36,140>");		// small box

  literal("<RC230,70><HW1,1>".$lang["price"]);		// Price
  literal("<RC254,45><HW2,2>$price");					// Amount
  literal("<RC248,34><LT2><BX36,140>");		// small box
  literal("<RC333,45><F3><HW1,1>#$bookid");		// Booking ID
  literal("<RC328,34><LT2><BX36,140>");		// small box

  literal("<RC30,220><LT2><BX200,620>");		// medium box
  literal("<F2><HW2,1><RC40,230>".centerin($tickettext_opening,66));
  literal("<F3><HW2,1><RC87,230>$namelong");	// these should already 
  literal("<F9><HW1,1><RC165,230>$datelong");	// be centered & 
  literal("<RC195,234>$classlong");  // cut to length

  literal("<RC252,350><F2><HW2,1>".$tickettext_closing[0]); 
  literal("<RC285,370><HW2,2><F1>".$tickettext_closing[1]);
  literal("<RC305,370>".$tickettext_closing[2]); 		// address box
  literal("<RC325,370>".$tickettext_closing[3]);	
  literal("<RC345,370>".$legal_info[0]);

  literal("<F2><HW1,1><RR><RC40,1036>$name");	// tear-off part
  literal("<RC40,1018>$date");				// sideways & short
  literal("<RC40,1000>#$bookid");	
  literal("<RC40,982>$pay");
  literal("<RC40,964>$class");
  literal("<RC40,946>".$lang["col"]. " ".$seat.", ".$lang["row"]." ".$row."<NR>");
  literal("<p>");
}

function literal($text) {	
  global $printfile;
  // print to the screen with no html translation
  // $printfile .= str_replace(" ","&nbsp;",htmlspecialchars($text))."<br>";
  $printfile .= $text;
}

 
function bocatickets_end() {
  global $printfile;
  
  if (isset($_SESSION['boca']) && $_SESSION['boca']) {
?>
   <script type="text/javascript">
      function print() {
         var applet = document.jzebra;
         if (applet != null) {
            // Searches for locally installed printer with "Officejet" in the name
            applet.findPrinter("Officejet_6600");
            
            // Send characters/raw commands to applet using "append"
            // Hint:  Carriage Return = \r, New Line = \n, Escape Double Quotes= \"
            applet.append( <?php echo '"'.$printfile.'"'; ?> );
            
            // Send characters/raw commands to printer
            applet.print();
         } else {
            alert("Printer driver not found");
         }
      }
      function jzebraReady() {
          // Change title when applet is ready
          var applet = document.jzebra;
          var title = document.getElementById("bocawaiting");
          if (applet != null) {
              title.innerHTML = "<input type=button onClick='print()' value='Print' >";
          }
      }      
   </script>
   <applet name="jzebra" code="jzebra.PrintApplet.class" archive="plugins/bocatickets/jzebra.jar" width="5px" height="5px">
      <!-- Note:  It is recommended to use applet.findPrinter() instead for ajax heavy applications -->
      <param name="printer" value="Officejet_6600">
      <!-- Optional, these "cache_" params enable faster loading "caching" of the applet -->
      <param name="cache_option" value="plugin">
      <!-- Change "cache_archive" to point to relative URL of jzebra.jar -->
      <param name="cache_archive" value="plugins/bocatickets/jzebra.jar">
      <!-- Change "cache_version" to reflect current jZebra version -->
      <param name="cache_version" value="1.4.8.0">
   </applet>
   <div style="margin-left:2em;">
   <h2>Boca Ticket Printing</h2><br />
   <div style="font-size:large; font-weight:bold;" id="bocawaiting">Please wait ...</div>
   </div>
<?php 
    $printfile = '';
  }
}

function centerin($message,$length) {
  // helper function for bocatickets
  // centers $message within a field $length long, padded with spaces
  // if $length is shorter than $message, truncate from the right to fit
  $messlen = strlen($message);
  if ($length > $messlen) {
    return rtrim(str_pad( $message, $length, " ", STR_PAD_BOTH ));
  } else {
    return substr($message, 0, $length);
  }
}

function bocatickets_checkbox() {
  echo '<!-- bocatickets -->';
  if (admin_mode()) {
    echo '<input type="checkbox" name="boca" style="margin: 0 1em 0 2em;"'; 
    if (isset($_SESSION["boca"]) && $_SESSION["boca"]) {
      echo ' checked="checked"';
    }
    echo '>Use BOCA ticket printer ';
  }
}

function bocatickets_process() {
  $_SESSION["boca"] = isset($_POST["boca"]) and admin_mode();
}


