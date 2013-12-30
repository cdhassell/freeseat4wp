<?php


/*
 *   Setup for group discounts in freeseat
 *   Requires groupdiscount and groupdiscount_min to be added to the config table.
 */

function freeseat_plugin_init_groupdiscount() {
    global $freeseat_plugin_hooks;

    $freeseat_plugin_hooks['extra_charges']['groupdiscount'] = 'groupdiscount_total';
    $freeseat_plugin_hooks['get_print']['groupdiscount'] = 'groupdiscount_print';
    $freeseat_plugin_hooks['params_post']['groupdiscount'] = 'groupdiscount_post';
    $freeseat_plugin_hooks['params_edit']['groupdiscount'] = 'groupdiscount_editparams';
    $freeseat_plugin_hooks['config_db']['groupdiscount'] = 'groupdiscount_config_db';
    init_language('groupdiscount');
}

function groupdiscount_total($data) {
  // how much the total price should be altered by a group discount
  if ($data==NULL) return 0;
  $paycount = 0;
  $group_discount = get_config("groupdiscount");
  $group_min = get_config("groupdiscount_min");  
  foreach ($data as $n => $s) 
    if ($s["cat"] != CAT_FREE) $paycount++;
  if (($group_discount) && ($paycount >= $group_min))
    return - $paycount * $group_discount;
  else
    return 0;
}

function groupdiscount_print($data) {
  global $lang;
  $line = "";
  // the order is important!
  $columns = array_pop($data);
  $fmt = array_pop($data);
  $discount = groupdiscount_total($data);
  if ($discount > 0) {
    $line = print_tablespecialrow($lang['groupdiscount'],price_to_string(-$discount),$columns,$fmt);
  }
  return $line;
}

function groupdiscount_post( &$options ) {
	// change to use WP post-form validation
	// called in freeseat_validate_options()
	if ( is_array( $options ) && isset( $options[ 'groupdiscount' ] ) ) 
		$options['groupdiscount'] = string_to_price($options['groupdiscount']);
	return $options;
}

function groupdiscount_editparams($options) {
	global $lang, $currency;
	// the options parameter should be an array 
	if ( !isset( $options['groupdiscount'] ) ) $options['groupdiscount'] = '100';
	if ( !isset( $options['groupdiscount_min'] ) ) $options['groupdiscount_min'] = 15;
?>  
<!-- group discount stuff -->
<tr>
	<td></td>
	<td>
		<?php echo $lang['groupdiscount_min']; ?><br /> 
		<input type="number" min="0" name="freeseat_options[groupdiscount_min]" size="6" value="<?php echo $options['groupdiscount_min'].'">&nbsp;'.$lang["seats"]; ?>
	</td>
	<td>
		<?php echo $lang['groupdiscount']; ?> 
		<?php echo $currency; ?> 
		<input type="number" min="0" name="freeseat_options[groupdiscount]" size="6" value="<?php echo price_to_string($options['groupdiscount']); ?>" />&nbsp;<?php echo $lang['groupdiscount_perseat']; ?>
	</td>
	<td></td>
</tr>
<?php
}

function groupdiscount_config_db($user) {
  return config_checksql_for('plugins/groupdiscount/setup.sql', $user);
}

