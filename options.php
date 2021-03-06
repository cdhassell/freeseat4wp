<?php namespace freeseat;

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

/* Modified for FreeSeat by twowheeler */

// ------------------------------------------------------------------------
// REGISTER HOOKS & CALLBACK FUNCTIONS:
// ------------------------------------------------------------------------
// HOOKS TO SETUP DEFAULT PLUGIN OPTIONS, HANDLE CLEAN-UP OF OPTIONS WHEN
// PLUGIN IS DEACTIVATED AND DELETED, INITIALISE PLUGIN, ADD OPTIONS PAGE.
// ------------------------------------------------------------------------

// Set-up Action and Filter Hooks
register_activation_hook(  FS_PATH . 'freeseat.php', __NAMESPACE__ . '\\freeseat_add_defaults');
add_action( 'admin_init', __NAMESPACE__ . '\\freeseat_init' );

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
	global $upload_path;
	
	$tmp = get_option('freeseat_options');
    if( (isset($tmp['chk_default_options_db']) && $tmp['chk_default_options_db']=='1') || (!is_array($tmp)) ) {
		delete_option('freeseat_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
		$arr = array(	
			'max_seats' => 30,
			'paydelay_ccard' => 3,
			'shakedelay_ccard' => 2,
			'closing_ccard' => 60,
			'disabled_ccard' => true,
			// 'opening_cash' => 0,
			'closing_cash' => 60,
			'disabled_cash' => false,
			'paydelay_post' => 5,
			'shakedelay_post' => 3,
			'closing_post' => 1440,
			'disabled_post' => true,
			'groupdiscount' => '100',
			'groupdiscount_min' => 15,
			'language' => 'english',
			'ticket_logo' => plugins_url( 'files/ticket-big.png', __FILE__ ),
			'websitename' => 'The Globe Theatre',
			'auto_email_signature' => 'Sincerely, Will',
			'pref_country_code' => 'US',
			'pref_state_code' => 'PA',
			'lowpriceconditions' => 'Children\'s discount up to 12 years old.',
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
			'plugins' => array( 'pdftickets', 'barcode', 'paypal' ),
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
	register_setting( 'freeseat_plugin_options', 'freeseat_options', __NAMESPACE__ . '\\freeseat_validate_options' );
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
	global $freeseat_available_plugins, $us_state, $country, $moneyfactor, $freeseat_plugin_hooks, $upload_path, $options;
	
	$freeseat_plugin_groups = array();
	foreach ( $freeseat_available_plugins as $name => $details ) {
		$freeseat_plugin_groups[$details['category']][$name] = $details;
	}

	?>
	<div class="wrap">
		<!-- Display Plugin Icon, Header, and Description -->
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>FreeSeat System Settings</h2>
		<div id="multiCheck">
		<!-- Beginning of the Plugin Options Form -->
		<form method="post" action="options.php" enctype="multipart/form-data">
			<?php 
				$options = get_option('freeseat_options');
				if (is_array($options)) {
					foreach($options['plugins'] as $name) {
						if (!isset($options['chk_'.$name])) $options['chk_'.$name] = 1;
					}
				} 
			?>
			<?php
			settings_fields('freeseat_plugin_options'); 
			$options = get_option('freeseat_options');
			if (is_array($options)) {
				foreach($options['plugins'] as $name) {
					if (!isset($options['chk_'.$name])) $options['chk_'.$name] = 1;
				}
			} 
			?>
			
			<!-- Table Structure Containing Form Controls -->
			<table class="form-table">
				<tr valign="top" style="border-top:#dddddd 1px solid;"><!-- Major Heading -->
					<th scope="col" colspan="4" style="padding:0;">
						<h3>
							<?php _e( 'General Options' ); ?>
						</h3>
					</th>
				</tr>
				<tr>
					<td>
					</td>
					<td>
						<?php _e( 'Name of this theatre' ); ?><br />
						<input type="text" size="25" name="freeseat_options[websitename]" value="<?php echo $options['websitename']; ?>" />
					</td>
					<td>
						<?php _e( 'Signature for automatic emails' ); ?><br />
						<input type="text" size="25" name="freeseat_options[auto_email_signature]" value="<?php echo $options['auto_email_signature']; ?>" />
						
					</td>
					<td>
						<?php _e( 'Maximum seats in one sale' ); ?><br />
						<input type="number" min="0" name="freeseat_options[max_seats]" value="<?php echo $options['max_seats']; ?>" />
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td>
						<?php _e( 'Email from address' ); ?><br />
						<input type="text" size="25" name="freeseat_options[smtp_sender]" value="<?php echo $options['smtp_sender']; ?>" />
					</td>
					<td>
						<?php _e( 'Email from name' ); ?><br />
						<input type="text" size="25" name="freeseat_options[sender_name]" value="<?php echo $options['sender_name']; ?>" />
					</td>
					<td>
						<?php _e( 'Administrator email' ); ?><br />
						<input type="text" size="25" name="freeseat_options[admin_mail]" value="<?php echo $options['admin_mail']; ?>" />
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td>
						<?php _e( 'Discount price conditions' ); ?><br />
						<input type="textarea" size="25" name="freeseat_options[lowpriceconditions]" value="<?php echo stripslashes( $options['lowpriceconditions'] ); ?>" />
					</td>
					<td>
						<?php _e( 'Legal notices' ); ?><br />
						<input type="textarea" size="25" name="freeseat_options[legal_info]" value="<?php echo implode('\n', $options['legal_info']); ?>" />
					</td>
					<td>
						<?php _e( 'Currency symbol' ); ?><br />
						<input type="text" size="1" name="freeseat_options[currency]" value="<?php echo $options['currency']; ?>" />
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td>
						<?php _e( 'Interface language' ); ?><br />
						<select name='freeseat_options[language]'>
							<?php 
								foreach (language_list() as $l) {
									echo "<option value='$l' ".selected($l,$options['language']).">".ucwords($l)."</option>";
								}
							?>
						</select>
					</td>
					<td>
						<?php _e( 'Default US state' ); ?><br />
						<?php _e( 'Select "None" to hide this option' ); ?><br />
						<select name='freeseat_options[pref_state_code]' size='1'>
						<option value="" ><?php _e( 'None' ); ?></option>
						<?php
						foreach ($us_state as $code => $fullname) {
							echo '<option value="'.$code.'" '. selected($code,$options['pref_state_code']). '>'. $fullname . '</option>';
						}
						?>
						</select>
					</td>
					<td>
						<?php _e( 'Default country' ); ?><br />
						<?php _e( 'Select "None" to hide this option' ); ?><br />
						<select name='freeseat_options[pref_country_code]' size='1'>
						<option value="" ><?php _e( 'None' ); ?></option>
						<?php
						foreach ($country as $code => $fullname) {
							echo '<option value="'.$code.'" '. selected($code,$options['pref_country_code']). '>'. $fullname . '</option>';
						}
						?>
						</select>
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td colspan="2">
						<?php _e( 'Default ticket logo image' ); ?><br />
						<label for="freeseat_options[ticket_logo]">Filename: </label>
						<input type="hidden" name="MAX_FILE_SIZE" value="500000" />
						<input type="file" size="40" name="freeseat_options[ticket_logo]" accept="image/*" />&nbsp;
					</td>
					<td>						
						<?php echo htmlspecialchars($options['ticket_logo']); ?>&nbsp;&nbsp;
						<input type="hidden" name="freeseat_options[old_logo]" value="<?php echo htmlspecialchars($options['ticket_logo']); ?>" />
						<img src="<?php echo plugins_url($upload_path.$options['ticket_logo'],__FILE__); ?>" width="50" height="50">
					</td>
				</tr>
				<tr valign="top" style="border-top:#dddddd 1px solid;"><!-- Major Heading -->
					<th scope="col" colspan="4" style="padding:0;">
						<h3>
							<?php _e( 'Payment Options' ); ?>
						</h3>
					</th>
				</tr>
				<tr>
					<th scope="row">
						<?php _e( 'Credit Card Payments' ); ?><br />
						<label><input name="freeseat_options[disabled_ccard]" type="checkbox" value="1" <?php if (isset($options['disabled_ccard'])) { checked('1', $options['disabled_ccard']); } ?> /> <?php _e( 'Disable' ); ?></label>
					</th>
					<td> 
						<?php _e( 'Close credit card reservations before show starts' ); ?><br />
						<input type="number" min="0" name="freeseat_options[closing_ccard]" value="<?php echo $options['closing_ccard']; ?>" /> Minutes
					</td>
					<td>
						<?php _e( 'Send reminders about unpaid reservations for' ); ?><br />
						<input type="number" min="0" name="freeseat_options[shakedelay_ccard]" value="<?php echo $options['shakedelay_ccard']; ?>" /> Days
					</td>
					<td>
						<?php _e( 'Cancel unpaid reservations after' ); ?><br />
						<input type="number" min="0" name="freeseat_options[paydelay_ccard]" value="<?php echo $options['paydelay_ccard']; ?>" /> Days
					</td>
				</tr>
				<!-- Freeseat plugins can add parameters here -->
				<?php do_hook_function('params_edit_ccard', $options ); ?>
				<tr>
					<th scope="row">
						<?php _e( 'Will-call Orders' ); ?><br />
						<label><input name="freeseat_options[disabled_cash]" type="checkbox" value="1" <?php if (isset($options['disabled_cash'])) { checked('1', $options['disabled_cash']); } ?> /> <?php _e( 'Disable' ); ?></label>
					</th>
					<?php /*  <td> 
						<?php _e( 'Open will-call reservations' ); ?><br />
						<input type="number" min="0" name="freeseat_options[opening_cash]" value="<?php echo $options['opening_cash']; ?>" /> Minutes
					</td> */ ?>
					<td>
						<?php _e( 'Close will-call reservations before show starts' ); ?><br />
						<input type="number" min="0" name="freeseat_options[closing_cash]" value="<?php echo $options['closing_cash']; ?>" /> Minutes
					</td>
					<td>
					</td>
				</tr>
				<tr valign="top" style="border-top:#dddddd 1px solid;"><!-- Major Heading -->
					<th scope="col" style="padding:0;">
						<h3>
							<?php _e( 'FreeSeat Plugins' ); ?>
						</h3>
					</th>
					<td colspan="3">
						<p><i><?php _e( "Hover over a checkbox to get a full explanation of each plugin" ); ?></i></p>
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td colspan="3">
						<?php
							foreach( $freeseat_plugin_groups as $group => $list ) {
								echo "<h4>Plugin Category: ".ucwords($group)."</h4>";
								echo "<div class='indent'>";
								foreach( $list as $name => $details ) {
									echo " <p class='main'> <label> <input name='freeseat_options[chk_$name]' type='checkbox' value='1' ";
									if (isset( $details['details'] ) ) {
										echo "title='".$details['details']."' ";
									}
									if ( in_array( $name, $options['plugins'] ) ) { 
										echo checked('1', $options['chk_'.$name]); 
									}
									echo "/>".$details['english_name']."</label>";
									echo " - <i>".$details['summary']."</i></p>";
								}
								echo "</div>";
							}
						?>
					</td>
				</tr>
				<tr valign="top" style="border-top:#dddddd 1px solid;"><!-- Major Heading -->
					<th scope="col" colspan="4" style="padding:0;">
						<h3>
							<?php _e( 'Other Options' ); ?>
						</h3>
					</th>
				</tr>
				<!-- Freeseat plugins can add parameters here -->
				<?php do_hook_function('params_edit', $options ); ?>
				<tr>
					<th scope="row">
						<strong><?php _e('Clear Settings'); ?></strong>
					</th>
					<td colspan="3">
						<label><input name="freeseat_options[chk_default_options_db]" type="checkbox" value="1" 
						<?php 
							if (isset($options['chk_default_options_db'])) { 
								checked('1', $options['chk_default_options_db']); 
							}
							$text = __("Only check this if you want to reset all settings to defaults when FreeSeat is deactivated and reactivated.  Otherwise, settings will be retained.");
							echo " title='$text'";
						?> /> Restore all default settings</label>
					</td>
				</tr>
			</table>
			<hr />
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
		</div><!-- end of id multiCheck -->
	</div><!-- end of class wrap -->
	<?php	
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function freeseat_validate_options($input) {
	global $freeseat_available_plugins, $freeseat_plugin_hooks, $upload_path, $lang;
	do_hook_function('params_post', $input);
	// if checkboxes are not checked, they are missing
	if (!isset($input['disabled_cash'])) $input['disabled_cash'] = 0;
	if (!isset($input['disabled_ccard'])) $input['disabled_ccard'] = 0;
	if (!isset($input['disabled_post'])) $input['disabled_post'] = 0;
	$input['websitename'] = wp_filter_nohtml_kses($input['websitename']); 
	$input['ticket_logo'] = $input['old_logo']; 
	// get the ticket logo image file if the user submitted one
	$permitted = array( "jpeg", "jpg", "gif", "png", "bmp" );
	foreach( $_FILES as $file_name => $file_array ) {
		if (!empty($file_array['name'])) {
			// do nothing if user didn't submit a file
			$parts = pathinfo($file_array['name']['ticket_logo']);
			$target = $parts["basename"];
			if ( is_uploaded_file( $file_array['tmp_name']['ticket_logo'] )
				&& isset($parts["extension"])
				&& in_array(strtolower($parts["extension"]),$permitted)) {
				if ( !move_uploaded_file( $file_array['tmp_name']['ticket_logo'], FS_PATH . $upload_path . $target ) ) {
					kaboom( $lang['err_upload'] ) ;
				} else {
					/* file upload succeeded, so overwrite the old value  */
					$input['ticket_logo'] = $target;
				}
			} else  {
				kaboom( $lang['err_filetype'] . "image" );
			}
		}
	}
	$input['auto_email_signature'] = wp_filter_nohtml_kses($input['auto_email_signature']); 
	$input['lowpriceconditions'] = wp_filter_nohtml_kses($input['lowpriceconditions']); 
	$input['legal_info'] =  array( wp_filter_nohtml_kses($input['legal_info']) ); 
	$input['smtp_sender'] = wp_filter_nohtml_kses($input['smtp_sender']);
	$input['sender_name'] = wp_filter_nohtml_kses($input['sender_name']);
	$input['admin_mail'] = wp_filter_nohtml_kses($input['admin_mail']); 
	$input['currency'] = trim( wp_filter_nohtml_kses($input['currency']) ); 
	$input['language'] = wp_filter_nohtml_kses($input['language']);
	$input['plugins'] = array();
	foreach( $freeseat_available_plugins as $name => $details ) {
		if ( isset( $input['chk_'.$name] ) && $input['chk_'.$name]==1 ) {
			$input['plugins'][] = $name;
		}
	}  
	return $input;
}

function get_config( $key = null ) {
	$config = get_option('freeseat_options');
	if (isset($key)) {
		if (isset($config[$key]))
			return $config[ $key ];
		else 
			return 0;  // just in case
	} else {
		return $config;
	}
}

function set_config( $config ) {
	if ( !empty( $config ) && is_array( $config ) ) {
		$arr = array_union( get_config(), $config );
		update_option('freeseat_options', $arr);  
		// returns false if option did not change or update failed
		return true;
	}
	return false;
}

// this constructs a global array with details on available plugins
$freeseat_available_plugins = array();
$freeseat_plugin_path = plugin_dir_path( __FILE__ )."plugins/";
foreach ( glob( $freeseat_plugin_path."*", GLOB_ONLYDIR ) as $directory ) {
	$file = $directory."/info.php";
	if ( file_exists( $file ) ) {
		include( $file );
		$basename = basename($directory);
		$fn_name = "\\freeseat\\".$basename."_info";
		$freeseat_available_plugins[$basename] = $fn_name();
	}
}

