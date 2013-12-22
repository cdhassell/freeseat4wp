<?php

function freeseat_plugin_init_csvdump() {
    global $freeseat_plugin_hooks;

    $freeseat_plugin_hooks['bookinglist_pagebottom']['csvdump'] = 'csvdump_link';
}

function csvdump_link() {
  global $lang;
  echo '<ul><li><p class="main">';
  printf($lang["dump_csv"],'<a href="'. FS_PATH .'plugins/csvdump/">','</a>');
  echo '</p></ul>';
}

?>