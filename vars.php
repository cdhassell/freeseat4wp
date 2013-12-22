<?php

define("FS_VERSION", "1.4.1b");

// $FS_PATH = plugin_dir_path( __FILE__ );

require_once ($FS_PATH . "config-default.php");

/* Override the above defaults with user's config file: */
@include_once ($FS_PATH . "config.php");

require_once ($FS_PATH . "languages/$language.php");


