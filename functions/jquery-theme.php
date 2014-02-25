<?php namespace freeseat;

/*
 *  Wordpress contains the full set of jquery code libraries
 *  but does not provide any CSS themes so a plugin must provide
 *  its own.  Fortunately theme files are available on the web.
 *  This function determines the version number of the jquery
 *  library in use and loads a compatible CSS theme from google.
 *  
 *  Credit to Ross McKay at 
 *  http://snippets.webaware.com.au/snippets/load-a-nice-jquery-ui-theme-in-wordpress/
 *  
 */

add_action( 'init', __NAMESPACE__ . '\\freeseat_jquery_dialog' ); 
 
function freeseat_jquery_dialog() {
	global $wp_scripts;
	
	// tell WordPress to load jQuery UI dialog
    wp_enqueue_script('jquery-ui-dialog');
 
    // get registered script object for jquery-ui
    $ui = $wp_scripts->query('jquery-ui-core');
 
    // tell WordPress to load a theme from Google CDN
    $protocol = is_ssl() ? 'https' : 'http';
    $url = "$protocol://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/sunny/jquery-ui.min.css";
    wp_enqueue_style('jquery-ui-sunny', $url, false, null);
    
    // load popup_script.js which makes the jquery call
	wp_enqueue_script( 'popup-script', plugins_url( 'js/popup_script.js', dirname(__FILE__) ),
		array( 'jquery', 'jquery-ui-core', 'jquery-ui-dialog' ) );
}

