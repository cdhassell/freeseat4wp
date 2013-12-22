<?php

/*  Copyright 2009 David Gwyer (email : d.v.gwyer@presscoders.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// ------------------------------------------------------------------------
// REGISTER HOOKS & CALLBACK FUNCTIONS:
// ------------------------------------------------------------------------
// HOOKS TO SETUP DEFAULT PLUGIN OPTIONS, HANDLE CLEAN-UP OF OPTIONS WHEN
// PLUGIN IS DEACTIVATED AND DELETED, INITIALISE PLUGIN, ADD OPTIONS PAGE.
// ------------------------------------------------------------------------

// Set-up Action and Filter Hooks
register_activation_hook(__FILE__, 'freeseat_add_defaults');
register_uninstall_hook(__FILE__, 'freeseat_delete_plugin_options');
add_action('admin_init', 'freeseat_init' );
add_filter( 'plugin_action_links', 'freeseat_plugin_action_links', 10, 2 );

// --------------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_uninstall_hook(__FILE__, 'freeseat_delete_plugin_options')
// --------------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE USER DEACTIVATES AND DELETES THE PLUGIN. IT SIMPLY DELETES
// THE PLUGIN OPTIONS DB ENTRY (WHICH IS AN ARRAY STORING ALL THE PLUGIN OPTIONS).
// --------------------------------------------------------------------------------------

// Delete options table entries ONLY when plugin deactivated AND deleted
function freeseat_delete_plugin_options() {
	delete_option('freeseat_options');
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: register_activation_hook(__FILE__, 'freeseat_add_defaults')
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE PLUGIN IS ACTIVATED. IF THERE ARE NO THEME OPTIONS
// CURRENTLY SET, OR THE USER HAS SELECTED THE CHECKBOX TO RESET OPTIONS TO THEIR
// DEFAULTS THEN THE OPTIONS ARE SET/RESET.
//
// OTHERWISE, THE PLUGIN OPTIONS REMAIN UNCHANGED.
// ------------------------------------------------------------------------------

// Define default option settings
function freeseat_add_defaults() {
	$tmp = get_option('freeseat_options');
    if(($tmp['chk_default_options_db']=='1')||(!is_array($tmp))) {
		delete_option('freeseat_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
		$arr = array(	
			'max_seats' => 30,
			'paydelay_ccard' => 3,
			'shakedelay_ccard' => 2,
			'closing_ccard' => 60,
			'disabled_ccard' => false,
			'opening_cash' => 0,
			'closing_cash' => 0,
			'disabled_cash' => false,
			'paydelay_post' => 3,
			'closing_post' => 60,
			'disabled_post' => true,
			'groupdiscount' => 1.00,
			'groupdiscount_min' => 15,
			'language' => 'english',
			'ticket_logo' => '',
			'websitename' => 'The Globe Theatre',
			'auto_email_signature' => 'Sincerely, Will',
			'pref_country_code' => 'US',
			'pref_state_code' => 'PA',
			'lowpriceconditions' => 'Children up to 12 years old are eligible for a reduced price.',
			'legal_info' => array( 'No refunds or exchanges' ),
			'tickettext_opening' => 'The Globe Theatre Company Presents',
			'tickettext_closing' => array( 'The Globe Theatre', '21 New Globe Walk', 'Bankside, London' ),
			'smtp_sender' => 'tickets@theglobe.org',
			'sender_name' => 'William Shakespeare',
			'admin_mail' => 'webmaster@theglobe.org',
			'currency' => '$',
			'default_area_code' => '717',
			'USPS_user' => 'my-USPS-account',
			'paypal_account' => 'tickets@theglobe.org',
			'paypal_auth_token' => 'a very long string of characters from your paypal account',
		);
		update_option('freeseat_options', $arr);
	}
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_init', 'freeseat_init' )
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE 'admin_init' HOOK FIRES, AND REGISTERS YOUR PLUGIN
// SETTING WITH THE WORDPRESS SETTINGS API. YOU WON'T BE ABLE TO USE THE SETTINGS
// API UNTIL YOU DO.
// ------------------------------------------------------------------------------

// Init plugin options to white list our options
function freeseat_init(){
	register_setting( 'freeseat_plugin_options', 'freeseat_options', 'freeseat_validate_options' );
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION FOR: add_action('admin_menu', 'freeseat_add_options_page');
// ------------------------------------------------------------------------------
// THIS FUNCTION RUNS WHEN THE 'admin_menu' HOOK FIRES, AND ADDS A NEW OPTIONS
// PAGE FOR YOUR PLUGIN TO THE SETTINGS MENU.
// ------------------------------------------------------------------------------

// Add menu page
function freeseat_add_options_page() {
	add_options_page('Settings', 'Settings', 'manage_options', __FILE__, 'freeseat_params');
}

// ------------------------------------------------------------------------------
// CALLBACK FUNCTION SPECIFIED IN: add_options_page()
// ------------------------------------------------------------------------------
// THIS FUNCTION IS SPECIFIED IN add_options_page() AS THE CALLBACK FUNCTION THAT
// ACTUALLY RENDER THE PLUGIN OPTIONS FORM AS A SUB-MENU UNDER THE EXISTING
// SETTINGS ADMIN MENU.
// ------------------------------------------------------------------------------

// Render the options form
function freeseat_params() {
	?>
	<div class="wrap">
		
		<!-- Display Plugin Icon, Header, and Description -->
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>FreeSeat System Settings</h2>
		
		<!-- Beginning of the Plugin Options Form -->
		<form method="post" action="options.php">
			<?php settings_fields('freeseat_plugin_options'); ?>
			<?php $options = get_option('freeseat_options'); ?>

			<!-- Table Structure Containing Form Controls -->
			<!-- Each Plugin Option Defined on a New Table Row -->
			<table class="form-table">
				<tr>
					<th scope="row">
						<?php _e( 'Minutes before the show to close online reservations' ); ?>
					</th>
					<td> 
						<input type="number" min="0" size="6" name="freeseat_options[closing_ccard]" value="<?php echo $options['closing_ccard']; ?>" />
					</td>
				</tr>			

				<tr>
					<th scope="row">
						<?php _e( 'How many reminders to send about an unpaid reservation' ); ?>
					</th>
					<td> 
						<input type="number" min="0" size="6" name="freeseat_options[shakedelay_ccard]" value="<?php echo $options['shakedelay_ccard']; ?>" />
					</td>
				</tr>			
							
				<tr>
					<th scope="row">
						<?php _e( 'Days before an unpaid reservation is cancelled' ); ?>
					</th>
					<td> 
						<input type="number" min="0" size="6" name="freeseat_options[paydelay_ccard]" value="<?php echo $options['paydelay_ccard']; ?>" />
					</td>
				</tr>			
							
				<tr>
					<th scope="row">
						<?php _e( 'Maximum number of seats allowed in one sale' ); ?>
					</th>
					<td> 
						<input type="number" min="0" size="6" name="freeseat_options[max_seats]" value="<?php echo $options['max_seats']; ?>" />
					</td>
				</tr>			
							
				<tr>
					<th scope="row">
						<?php _e( 'Minimum number of seats to qualify for a group discount' ); ?>
					</th>
					<td> 
						<input type="number" min="0" size="6" name="freeseat_options[groupdiscount_min]" value="<?php echo $options['groupdiscount_min']; ?>" />
					</td>
				</tr>			

				<tr>
					<th scope="row">
						<?php _e( 'Group discount value per seat' ); ?>
					</th>
					<td> 
						<input type="number" min="0" size="6" name="freeseat_options[groupdiscount]" value="<?php echo $options['groupdiscount']; ?>" />
					</td>
				</tr>			
		
				<?php /*
				<!-- Text Area Control -->
				<tr>
					<th scope="row">Sample Text Area</th>
					<td>
						<textarea name="freeseat_options[textarea_one]" rows="7" cols="50" type='textarea'><?php echo $options['textarea_one']; ?></textarea><br /><span style="color:#666666;margin-left:2px;">Add a comment here to give extra information to Plugin users</span>
					</td>
				</tr>

				<!-- Text Area Using the Built-in WP Editor -->
				<tr>
					<th scope="row">Sample Text Area WP Editor 1</th>
					<td>
						<?php
							$args = array("textarea_name" => "freeseat_options[textarea_two]");
							wp_editor( $options['textarea_two'], "freeseat_options[textarea_two]", $args );
						?>
						<br /><span style="color:#666666;margin-left:2px;">Add a comment here to give extra information to Plugin users</span>
					</td>
				</tr>

				<!-- Text Area Using the Built-in WP Editor -->
				<tr>
					<th scope="row">Sample Text Area WP Editor 2</th>
					<td>
						<?php
							$args = array("textarea_name" => "freeseat_options[textarea_three]");
							wp_editor( $options['textarea_three'], "freeseat_options[textarea_three]", $args );
						?>
						<br /><span style="color:#666666;margin-left:2px;">Add a comment here to give extra information to Plugin users</span>
					</td>
				</tr>
				
				<!-- Textbox Control -->
				<tr>
					<th scope="row">Enter Some Information</th>
					<td>
						<input type="text" size="57" name="freeseat_options[txt_one]" value="<?php echo $options['txt_one']; ?>" />
					</td>
				</tr>
				
				<!-- Radio Button Group -->
				<tr valign="top">
					<th scope="row">Radio Button Group #1</th>
					<td>
						<!-- First radio button -->
						<label><input name="freeseat_options[rdo_group_one]" type="radio" value="one" <?php checked('one', $options['rdo_group_one']); ?> /> Radio Button #1 <span style="color:#666666;margin-left:32px;">[option specific comment could go here]</span></label><br />

						<!-- Second radio button -->
						<label><input name="freeseat_options[rdo_group_one]" type="radio" value="two" <?php checked('two', $options['rdo_group_one']); ?> /> Radio Button #2 <span style="color:#666666;margin-left:32px;">[option specific comment could go here]</span></label><br /><span style="color:#666666;">General comment to explain more about this Plugin option.</span>
					</td>
				</tr>

				<!-- Checkbox Buttons -->
				<tr valign="top">
					<th scope="row">Group of Checkboxes</th>
					<td>
						<!-- First checkbox button -->
						<label><input name="freeseat_options[chk_button1]" type="checkbox" value="1" <?php if (isset($options['chk_button1'])) { checked('1', $options['chk_button1']); } ?> /> Checkbox #1</label><br />

						<!-- Second checkbox button -->
						<label><input name="freeseat_options[chk_button2]" type="checkbox" value="1" <?php if (isset($options['chk_button2'])) { checked('1', $options['chk_button2']); } ?> /> Checkbox #2 <em>(useful extra information can be added here)</em></label><br />

						<!-- Third checkbox button -->
						<label><input name="freeseat_options[chk_button3]" type="checkbox" value="1" <?php if (isset($options['chk_button3'])) { checked('1', $options['chk_button3']); } ?> /> Checkbox #3 <em>(useful extra information can be added here)</em></label><br />

						<!-- Fourth checkbox button -->
						<label><input name="freeseat_options[chk_button4]" type="checkbox" value="1" <?php if (isset($options['chk_button4'])) { checked('1', $options['chk_button4']); } ?> /> Checkbox #4 </label><br />

						<!-- Fifth checkbox button -->
						<label><input name="freeseat_options[chk_button5]" type="checkbox" value="1" <?php if (isset($options['chk_button5'])) { checked('1', $options['chk_button5']); } ?> /> Checkbox #5 </label>
					</td>
				</tr>

				<!-- Another Radio Button Group -->
				<tr valign="top">
					<th scope="row">Radio Button Group #2</th>
					<td>
						<!-- First radio button -->
						<label><input name="freeseat_options[rdo_group_two]" type="radio" value="one" <?php checked('one', $options['rdo_group_two']); ?> /> Radio Button #1</label><br />

						<!-- Second radio button -->
						<label><input name="freeseat_options[rdo_group_two]" type="radio" value="two" <?php checked('two', $options['rdo_group_two']); ?> /> Radio Button #2</label><br />

						<!-- Third radio button -->
						<label><input name="freeseat_options[rdo_group_two]" type="radio" value="three" <?php checked('three', $options['rdo_group_two']); ?> /> Radio Button #3</label>
					</td>
				</tr>

				<!-- Select Drop-Down Control -->
				<tr>
					<th scope="row">Sample Select Box</th>
					<td>
						<select name='freeseat_options[drp_select_box]'>
							<option value='one' <?php selected('one', $options['drp_select_box']); ?>>One</option>
							<option value='two' <?php selected('two', $options['drp_select_box']); ?>>Two</option>
							<option value='three' <?php selected('three', $options['drp_select_box']); ?>>Three</option>
							<option value='four' <?php selected('four', $options['drp_select_box']); ?>>Four</option>
							<option value='five' <?php selected('five', $options['drp_select_box']); ?>>Five</option>
							<option value='six' <?php selected('six', $options['drp_select_box']); ?>>Six</option>
							<option value='seven' <?php selected('seven', $options['drp_select_box']); ?>>Seven</option>
							<option value='eight' <?php selected('eight', $options['drp_select_box']); ?>>Eight</option>
						</select>
						<span style="color:#666666;margin-left:2px;">Add a comment here to explain more about how to use the option above</span>
					</td>
				</tr>
				*/ ?>
				<tr><td colspan="2"><div style="margin-top:10px;"></div></td></tr>
				<tr valign="top" style="border-top:#dddddd 1px solid;">
					<th scope="row">Database Options</th>
					<td>
						<label><input name="freeseat_options[chk_default_options_db]" type="checkbox" value="1" <?php if (isset($options['chk_default_options_db'])) { checked('1', $options['chk_default_options_db']); } ?> /> Restore defaults upon plugin deactivation/reactivation</label>
						<br /><span style="color:#666666;margin-left:2px;">Only check this if you want to reset plugin settings upon Plugin reactivation</span>
					</td>
				</tr>
			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
	</div>
	<?php	
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function freeseat_validate_options($input) {
	/*  TODO 
	$input['textarea_one'] =  wp_filter_nohtml_kses($input['textarea_one']); // Sanitize textarea input (strip html tags, and escape characters)
	$input['txt_one'] =  wp_filter_nohtml_kses($input['txt_one']); // Sanitize textbox input (strip html tags, and escape characters)
	*/
	return $input;
}

// Display a Settings link on the main Plugins page
function freeseat_plugin_action_links( $links, $file ) {

	if ( $file == plugin_basename( __FILE__ ) ) {
		$freeseat_links = '<a href="'.get_admin_url().'options-general.php?page=freeseat-admin/options.php">'.__('Settings').'</a>';
		// make the 'Settings' link appear first
		array_unshift( $links, $freeseat_links );
	}

	return $links;
}

function get_config( $key = null ) {
	$config = get_option('freeseat_options');
	if (isset($key)) {
  		return $config[ $key ];
	} else {
		return $config;
	}
}

function set_config( $config ) {
	// assumes that parameter $config is an array of config options
	$arr = get_config();
	foreach ( array( 	
		'max_seats',
		'paydelay_ccard',
		'shakedelay_ccard',
		'closing_ccard',
		'disabled_ccard',
		'opening_cash',
		'closing_cash',
		'disabled_cash',
		'disabled_cash',
		'paydelay_post',
		'closing_post',
		'disabled_post',
		'groupdiscount',
		'groupdiscount_min',
		'language',
		'ticket_logo',
		'websitename',
		'auto_email_signature',
		'pref_country_code',
		'pref_state_code',
		'lowpriceconditions',
		'legal_info',
		'tickettext_opening',
		'tickettext_closing',
		'smtp_sender',
		'sender_name',
		'admin_mail',
		'currency',
		'default_area_code',
		'USPS_user',
		'paypal_account',
		'paypal_auth_token',
	) as $item ) {
		if ( isset( $config[ $item ] ) ) {
			$arr[ $item ] = $config[ $item ];
		}
	}
	update_option('freeseat_options', $arr);
	return true;
}

