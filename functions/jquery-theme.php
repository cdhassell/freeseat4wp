<?php

/*
 *  Wordpress contains the full set of jquery code libraries
 *  but does not provide any CSS themes so a plugin must provide
 *  its own.  Fortunately theme files are available on the web.
 *  This function determines the version number of the jquery
 *  library in use and loads a compatible CSS theme from google.
 *  
 */
function load_jquery_ui() {
    global $wp_scripts;
 	// credit to Ross McKay at http://snippets.webaware.com.au/snippets/load-a-nice-jquery-ui-theme-in-wordpress/
    // tell WordPress to load jQuery UI dialog
    wp_enqueue_script('jquery-ui-dialog');
 
    // get registered script object for jquery-ui
    $ui = $wp_scripts->query('jquery-ui-core');
 
    // tell WordPress to load the Smoothness theme from Google CDN
    $protocol = is_ssl() ? 'https' : 'http';
    $url = "$protocol://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";
    wp_enqueue_style('jquery-ui-smoothness', $url, false, null);
}

