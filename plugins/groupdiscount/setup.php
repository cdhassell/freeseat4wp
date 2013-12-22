<?php

$FS_PATH = plugin_dir_path( __FILE__ ) . '../../';

require_once ( $FS_PATH . "vars.php");

require_once ( $FS_PATH . "functions/tools.php");
require_once ( $FS_PATH . "functions/money.php");
require_once ( $FS_PATH . "functions/format.php");
include_once ( $FS_PATH . 'plugins/config/functions.php');

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

function groupdiscount_post() {
  global $config;
  if (isset($_POST['groupdiscount_min']))
    $config['groupdiscount_min'] = abs($_POST['groupdiscount_min']);

  if (isset($_POST['groupdiscount']))
      $config['groupdiscount'] = string_to_price($_POST['groupdiscount']);
}

function groupdiscount_editparams() {
  global $lang, $currency, $config;
?>  
<!-- group discount stuff -->
<tr><td><p><?php echo $lang['groupdiscount_min']; ?></p>
  <td><p><input name="groupdiscount_min" size="3" value="<?php  //"; // emacs cookie
  echo $config['groupdiscount_min'].'">&nbsp;'.$lang["seats"];
?>
</p>
<tr><td><p><?php echo $lang['groupdiscount']; ?></p>
<td><p><?php echo $currency; ?>
<input name="groupdiscount" size="3" value="<?php echo price_to_string($config['groupdiscount']); ?>" />
&nbsp;<?php echo $lang['groupdiscount_perseat']; ?>
</p>
<?php
}

function groupdiscount_config_db($user) {
  return config_checksql_for('plugins/groupdiscount/setup.sql', $user);
}

