<?php namespace freeseat;


function freeseat_plugin_init_postal() {
    global $freeseat_plugin_hooks;

	$freeseat_plugin_hooks['params_post']['postal'] = 'postal_postedit';
	$freeseat_plugin_hooks['params_edit_ccard']['postal'] = 'postal_editparams';
}

function postal_postedit( &$options ) {
	// use WP post-form validation
	// called in freeseat_validate_options()
	if ( is_array( $options ) ) {
		$options['closing_post'] = wp_filter_nohtml_kses($options['closing_post']);
		$options['shakedelay_post'] = wp_filter_nohtml_kses($options['shakedelay_post']);
		$options['paydelay_post'] = wp_filter_nohtml_kses($options['paydelay_post']);
		if (!isset($options['disabled_post'])) $options['disabled_post'] = 0;
	}
	return $options;
}

function postal_editparams($options) {
	global $lang;
	// the options parameter should be an array 
	if ( !is_array( $options ) ) return;
	if (!isset($options['disabled_post'])) $options['disabled_post'] = 1;
	if (!isset($options['closing_post'])) $options['closing_post'] = 1440;
	if (!isset($options['shakedelay_post'])) $options['shakedelay_post'] = 3;
	if (!isset($options['paydelay_post'])) $options['paydelay_post'] = 5;
?>  
<!-- swiss postal stuff -->
<tr>
	<th scope="row">
		<?php _e( 'Swiss Postal Payments' ); ?><br />
		<label><input name="freeseat_options[disabled_post]" type="checkbox" value="1" <?php if (isset($options['disabled_post'])) { checked('1', $options['disabled_post']); } ?> /> <?php _e( 'Disable' ); ?></label>
	</th>
	<td> 
		<?php _e( 'Close postal reservations before show starts' ); ?><br />
		<input type="number" min="0" name="freeseat_options[closing_post]" value="<?php echo $options['closing_post']; ?>" /> Minutes
	</td>
	<td>
		<?php _e( 'Send reminders about unpaid reservations for' ); ?><br />
		<input type="number" min="0" width="3" name="freeseat_options[shakedelay_post]" value="<?php echo $options['shakedelay_post']; ?>" /> Days
	</td>
	<td>
		<?php _e( 'Cancel unpaid postal reservations after' ); ?><br />
		<input type="number" min="0" name="freeseat_options[paydelay_post]" value="<?php echo $options['paydelay_post']; ?>" /> Days
	</td>
	<td>
	</td>
</tr>	
<?php
}

