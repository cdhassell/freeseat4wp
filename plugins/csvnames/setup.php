<?php

function freeseat_plugin_init_csvnames() {
    global $freeseat_plugin_hooks;

    $freeseat_plugin_hooks['bookinglist_pagebottom']['csvnames'] = 'csvnames_link';
    init_language('csvnames');
}

function csvnames_link() {
  global $lang;
  echo '<ul><li><p class="main">';
  printf($lang['csvnames_download'] . ' <a href="'. FS_PATH .'plugins/csvnames/">['.$lang['csvnames_link'].']</a>');
  echo '</p></ul>';
}


