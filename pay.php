<?php namespace freeseat;

/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.

$Id: index.php 341 2011-04-25 19:03:48Z tendays $

Modifications for Wordpress are Copyright (C) 2013 twowheeler.
*/

/*
 * Displays a page to collect payment info
 * Replaces the former pay.php
 */
function freeseat_pay( $page_url )
{
	global $lang, $sh, $pref_country_code, $pref_state_code, $lowpriceconditions;
	load_alerts();
	
	// MIGHT be enough to put it next to the load_seats below but I won't take chances.
	if (!do_hook_exists('pay_page_top'))
		kill_booking_done();
	if ( isset( $_GET[ 'showid' ] ) && !isset( $_SESSION[ 'showid' ] ) ) {
		$_SESSION[ 'showid' ] = $_GET[ 'showid' ];
	}
	// print '<pre>Get = '.print_r($_GET,1).'</pre>';
	// print '<pre>Post = '.print_r($_POST,1).'</pre>';
	check_session(1); // just to avoid warnings on missing show id
	
	$sh = get_show($_SESSION["showid"]); // needed by load_seats
	
	/** if no set of seats is provided then just keep the one in session **/
	if (isset($_POST["load_seats"])) {
		/* (if the following fails it will be handled by check_session) */
		unlock_seats();
		load_seats($_POST);
		check_session(3);
		compute_cats();
	} else check_session(3);
	
	$seatcount = count($_SESSION["seats"]);
	
	show_head();
	
	echo '<h2>'.$lang["summary"].'</h2>';
	echo "<p>";
	show_show_info($sh);
	echo "</p>";
	echo print_booked_seats();
	echo '<h2>'.$lang["payment"].'</h2>';
	echo '<form action="' . $page_url . '&fsp=' . PAGE_CONFIRM . '" method="post">';
	
	if (!isset($_SESSION["payment"])) $_SESSION["payment"]= PAY_CCARD;
	
	/* If the price doesn't depend on the category, then don't offer
	 discout option. */
	$discount_option = price_depends_on_cat($sh["spectacleid"]);
	
	/* All categories from which the user may choose, mapped to their $lang key. */
	$cats = array();
	
	$cats[CAT_NORMAL] = "cat_normal";
	
	if ($discount_option) {
		$cats[CAT_REDUCED] = "cat_reduced";
		// only display lowpriceconditions if there are reduced prices available
		echo '<p class="main">'.$lowpriceconditions.'</p>';
	}
	
	if (admin_mode())
		$cats[CAT_FREE] = "cat_free";
	
	if (count($cats) > 1) {
		/* If neither of those two hold, the only option is normal mode... */
	
		if ($seatcount==1) {
			/* see which one to select by defaut */
			$def=CAT_NORMAL;
			foreach ($cats as $cat => $label) {
				if (isset($_SESSION["ncat$cat"]) && $_SESSION["ncat$cat"] > 0) {
					$def = $cat;
				}
			}
			echo "<p class='main'>".$lang["cat"]."&nbsp;: ";
			echo "<select name='cat'>";
			foreach ($cats as $cat => $label) {
				echo "<option value=".$cat;
				if ($def == $cat) echo " selected='true'";
				echo ">".$lang[$label]."</option>";
			}
			echo '</select>';
		} else { // more than one seat selected
			/* We make sure the default values for discounted seats don't
			total to a larger number than the numer of selected seats. */
			$total = 0; // how many seats were previously set to a discount
			foreach ($cats as $cat => $label) {
				if (isset($_SESSION["ncat$cat"]))
					$total += $_SESSION["ncat$cat"];
			}
	    	if ($total > $seatcount) {
				$skip = $total - $seatcount;
				foreach ($cats as $cat => $label) {
					if (isset($_SESSION["ncat$cat"])) {
						if ($_SESSION["ncat$cat"] > $skip) {
							$_SESSION["ncat$cat"] -= $skip;
							break;
						} else {
							$skip -= $_SESSION["ncat$cat"];
							$_SESSION["ncat$cat"] = 0;
						}
					}
				}
			}
		    echo "<p class='main'>".sprintf($lang["howmanyare"],$seatcount). ":</p>\n<ul>";
			foreach ($cats as $cat => $label) {
				if ($cat == CAT_NORMAL) continue;
				echo "<li><p> ".$lang[$label]."&nbsp;:&nbsp;";
				input_field("ncat$cat", '0', ' size="2"');
				echo "</p>";
			}
			echo '</ul>';
		}
	}
	
	echo'<p class="main">'.$lang["select_payment"] . '<br />';
	pay_option(PAY_CCARD);
	pay_option(PAY_POSTAL);
	pay_option(PAY_CASH);
	pay_option(PAY_OTHER);
	echo '</p>';
	
	do_hook('other_payment_info');
	
	if (payment_open($sh,PAY_CCARD)) {
	  do_hook('ccard_partner');
	}
	
	echo '<h2>'.$lang["youare"].'</h2>';
	echo '<p class="main">'.$lang["reqd_info"].'</p>';
	echo '<p class="main">';
	input_field("firstname");
	echo ' ';
	input_field("lastname");
	echo '</p><p class="main">';
	input_field("phone");
	echo ' ';
	input_field("email");
	echo '</p><p class="main">';
	input_field("address",""," size=60");
	echo '</p><p class="main">';
	input_field("postalcode",""," size=8");
	echo ' ';
	input_field("city",""," size=20");
	// we will skip the us_state and/or country fields if the defaults are not set in config.php
	if ($pref_state_code != "")  {
		echo '</p><p class="main">';
		echo $lang["us_state"].'&nbsp;:&nbsp;';
		select_state();
	}
	if ($pref_country_code != "")  {
		echo '</p><p class="main">';
		echo $lang["country"].'&nbsp;:&nbsp;';
		select_country();
	}
	echo '</p><p class="main"><input class="button button-primary" type="submit" value="'.$lang["continue"].'"></p></form>';
	
	show_foot();
}	// end of freeseat_pay

