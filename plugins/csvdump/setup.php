<?php

function freeseat_download_redirect() {
    if( is_page( 'freeseat-download' ) && isset( $_REQUEST['file'] ) && admin_mode() ) {
    	wp_redirect( plugins_url("index.php",__FILE__) );
    	exit();
    }
}

function freeseat_plugin_init_csvdump() {
	add_action( 'admin_menu', 'freeseat_download_menu' );
	add_action( 'template_redirect', 'freeseat_download_redirect');
	init_language('csvdump');
}

function freeseat_download_menu() {
	// add a new admin menu page for data downloads
	// this must be run *after* the function freeseat_admin_menu()
	add_submenu_page( 'freeseat-admin', 'Downloads', 'Downloads', 'administer_freeseat', 'freeseat-download', 'freeseat_download' );
}

function freeseat_download() {
	global $lang;

	if (!admin_mode()) {
		wp_die( $lang["access_denied"] );
	}
	show_head();
	echo '<h2>'.$lang['csvdump_download'].'</h2>';
	echo '<p class="main">'.$lang["csvdump_intro"].'</p>';
	echo '<p class="main">';
	printf($lang["csvdump_all"],'<a href="'. plugins_url("setup.php",__FILE__) .'?file=all">','</a>');
	echo '</p>';	
	echo '<p class="main">';
	printf($lang["csvdump_files"],'<a href="'. plugins_url("setup.php", __FILE__) .'?file=names">','</a>');
	echo '</p>';
	show_foot();
}

