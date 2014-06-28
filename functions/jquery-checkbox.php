<?php namespace freeseat;

/*
 *  Loads the simpleImageCheck code to make nice checkbox graphics on the seatmap
 */

add_action( 'init', __NAMESPACE__ . '\\freeseat_jquery_checkbox' ); 

function freeseat_jquery_checkbox () {
	wp_enqueue_script( 'checkbox-script', plugins_url( 'js/jquery.simpleImageCheck-0.4.min.js', dirname(__FILE__) ),
		array( 'jquery' ) );

	wp_register_script( 'mycheckbox-script', plugins_url( 'js/mycheckbox_script.js', dirname(__FILE__) ),
		array( 'jquery' ) );
	$path_array = array( 'imageChecked' => plugins_url( 'js/check.png', dirname(__FILE__)), 'image' => plugins_url( 'js/unchecked.png', dirname(__FILE__)));
	wp_localize_script( 'mycheckbox-script', 'image_path', $path_array );
	wp_enqueue_script( 'mycheckbox-script' );
}
