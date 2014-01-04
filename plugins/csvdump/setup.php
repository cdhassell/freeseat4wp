<?php


function freeseat_plugin_init_csvdump() {
	add_action( 'admin_menu', 'freeseat_download_menu' );
	add_action( 'admin_init', 'freeseat_download_redirect');
	init_language('csvdump');
}

function freeseat_download_menu() {
	// add a new admin menu page for data downloads
	// this must be run *after* the function freeseat_admin_menu()
	add_submenu_page( 'freeseat-admin', 'Downloads', 'Downloads', 'administer_freeseat', 'freeseat-download', 'freeseat_download' );
}

function freeseat_download_redirect() {
	// wordpress is not helpful here
	$uri = $_SERVER["REQUEST_URI"];
	if( false !== strpos( $uri, 'freeseat-download' ) && isset( $_REQUEST['file'] ) ) {
		$mode = $_REQUEST['file'];
    	wp_redirect( plugins_url("index.php?file=$mode",__FILE__) );
    	exit();
    }
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
	printf($lang["csvdump_all"],'<a href="admin.php?page=freeseat-download&file=all">','</a>');
	echo '</p>';	
	echo '<p class="main">';
	printf($lang["csvdump_files"],'<a href="admin.php?page=freeseat-download&file=names">','</a>');
	echo '</p>';
	show_foot();
}

