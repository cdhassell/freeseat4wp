<?php namespace freeseat;

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
 *  May 2013 - twowheeler
 *  Added JS to use jzebra applet to do raw printing
 *  See http://code.google.com/p/jzebra/
 *   
 */

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\bocatickets_load_js' );

function freeseat_plugin_init_bocatickets() {
	global $freeseat_plugin_hooks;

	$freeseat_plugin_hooks['ticket_prepare_override']['bocatickets'] = 'bocatickets_start';
	$freeseat_plugin_hooks['ticket_render_override']['bocatickets'] = 'bocatickets_body';
	$freeseat_plugin_hooks['ticket_finalise_override']['bocatickets'] = 'bocatickets_end';
	$freeseat_plugin_hooks['confirm_bottom']['bocatickets'] = 'bocatickets_checkbox';
	$freeseat_plugin_hooks['confirm_process']['bocatickets'] = 'bocatickets_process';
	$freeseat_plugin_hooks['adminprint_line']['bocatickets'] = 'bocatickets_checkbox';
	$freeseat_plugin_hooks['adminprint_process']['bocatickets'] = 'bocatickets_process';
    $freeseat_plugin_hooks['params_post']['bocatickets'] = 'bocatickets_postedit';
    $freeseat_plugin_hooks['params_edit']['bocatickets'] = 'bocatickets_editparams';    
}

function bocatickets_load_js() {
	wp_enqueue_script( 'boca-deploy', plugins_url( 'js/deployJava.js', __FILE__ ), '0.1.0', TRUE );
	wp_enqueue_script( 'boca-script', plugins_url( 'bocaQZ.js', __FILE__ ), '0.1.0', TRUE );
}

function bocatickets_postedit( &$options ) {
	// use WP post-form validation
	// called in freeseat_validate_options()
	if ( is_array( $options ) ) {
		$options['tickettext_opening'] = wp_filter_nohtml_kses($options['tickettext_opening']); 
		$options['tickettext_closing'] = explode( PHP_EOL, $options['tickettext_closing'] );
		$arr = array();
		foreach ( $options['tickettext_closing'] as $line ) 
			$arr[] = wp_filter_nohtml_kses( $line );
		$options['tickettext_closing'] = $arr; 		
	}
	return $options;
}

function bocatickets_editparams($options) {
	global $lang;
	// the options parameter should be an array 
	if ( !is_array( $options ) ) return;
	if ( !isset( $options['tickettext_opening'] ) ) $options['tickettext_opening'] = '';
	if ( !isset( $options['tickettext_closing'] ) ) $options['tickettext_closing'] = '';
?>  
<!-- bocatickets stuff -->
<tr>
	<td>
	</td>
	<td>
		<?php _e( 'Ticket top line text' ); ?><br />
		<input type="text" size="25" name="freeseat_options[tickettext_opening]" value="<?php echo $options['tickettext_opening']; ?>" />
	</td>
	<td colspan="2">
		<br /><?php _e( 'Ticket closing lines' ); ?><br />
		<textarea name="freeseat_options[tickettext_closing]" rows="3" cols="35" type='textarea'><?php echo ( is_array( $options['tickettext_closing'] ) ? implode( PHP_EOL, $options['tickettext_closing'] ) : $options['tickettext_closing'] ); ?></textarea>
	</td>
</tr>
<?php
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

	literal("<PP><RC5,5><LT4><BX380,880>");	// big box
	literal("<RC50,75><HW1,1><F2>".$lang["col"]);	// Seat
	literal("<RC74,75><HW2,2><F2>$seat");		// Seat number
	literal("<RC68,34><LT2><BX36,140>");		// small box
	literal("<RC138,75><HW1,1>".$lang["row"]);	// Row
	literal("<RC162,75><HW2,2>$row");						// Row number
	literal("<RC156,34><LT2><BX36,140>");		// small box

	literal("<RC230,70><HW1,1>".$lang["price"]);	// Price
	literal("<RC254,45><HW2,2>$price");		// Amount
	literal("<RC248,34><LT2><BX36,140>");		// small box
	literal("<RC333,45><F3><HW1,1>#$bookid");	// Booking ID
	literal("<RC328,34><LT2><BX36,140>");		// small box

	literal("<RC30,220><LT2><BX200,620>");	// medium box
	literal("<F2><HW2,1><RC40,230>".centerin($tickettext_opening,66));
	literal("<F3><HW2,1><RC87,230>$namelong");	// these should already 
	literal("<F9><HW1,1><RC165,230>$datelong");	// be centered & 
	literal("<RC195,234>$classlong");             // cut to length
	
	literal("<RC252,350><F2><HW2,1>".$tickettext_closing[0]); 
	if (isset($tickettext_closing[1])) 
		literal("<RC285,370><HW2,2><F1>".$tickettext_closing[1]);
	if (isset($tickettext_closing[2])) 
		literal("<RC305,370>".$tickettext_closing[2]); 	// address box
	if (isset($tickettext_closing[3])) 
		literal("<RC325,370>".$tickettext_closing[3]);
	literal("<RC345,370>".$legal_info[0]);

	literal("<F2><HW1,1><RR><RC40,1036>$name");	// tear-off part
	literal("<RC40,1018>$date");			// sideways & short
	literal("<RC40,1000>#$bookid");
	literal("<RC40,982>$pay");
	literal("<RC40,964>$class");
	literal("<RC40,946>".$lang["col"]. " ".$seat.", ".$lang["row"]." ".$row."<NR>");
	literal("<p>");
}

function literal($text) {
	global $printfile;
	// capture all output in the global $printfile variable
	$printfile .= $text;
}

 
function bocatickets_end() {
	global $printfile;
	
	if (isset($_SESSION['boca']) && $_SESSION['boca']) {
		$printfile = str_replace(array('.', "\n", "\t", "\r"), '', $printfile);
		// this doesn't work as WP escapes the data and makes it useless
		// wp_localize_script( 'boca-script', 'bocaticketsText', array('output' => $printfile ) );
		// so we directly write the data out where javascript can find it
		?>
<script type='text/javascript'>
/* <![CDATA[ */
var bocaticketsText = {"output":"<?php echo $printfile; ?>"};
/* ]]> */
</script> 

<applet id="qz" archive="<?php echo plugins_url('qz-print.jar', __FILE__); ?>" name="QZ Print Plugin" code="qz.PrintApplet.class" width="1" height="1"><param name="jnlp_href" value="<?php echo plugins_url('qz-print_jnlp.jnlp',__FILE__); ?>"><param name="cache_option" value="plugin"><param name="disable_logging" value="false"><param name="initial_focus" value="false"></applet>
<div style="margin-left:2em;">
<h2>Boca Ticket Printing</h2><br />
<div style="font-size:large; font-weight:bold;" id="bocawaiting"><img src="<?php echo plugins_url('spinner.gif', __FILE__); ?>"> Please wait ...</div>
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
		echo '<p class="main"><input type="checkbox" name="boca"';
		if (isset($_SESSION["boca"]) && $_SESSION["boca"]) {
			echo ' checked="checked"';
		}
		echo '>Use BOCA ticket printer&nbsp; ';
	}
}

function bocatickets_process() {
	$_SESSION["boca"] = isset($_POST["boca"]) and admin_mode();
}

