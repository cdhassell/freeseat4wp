<?php namespace freeseat;

/*
 *  Loads the simpleImageCheck code to make nice checkbox graphics on the seatmap
 */

add_action( 'init', __NAMESPACE__ . '\\freeseat_jquery_seatmap' ); 

function freeseat_jquery_seatmap () {
	wp_enqueue_script( 'checkbox-script', plugins_url( 'js/jquery.simpleImageCheck-0.4cdh.min.js', dirname(__FILE__) ),
		array( 'jquery' ) );

	wp_register_script( 'seatmap-script', plugins_url( 'js/seatmap_script.js', dirname(__FILE__) ),
		array( 'jquery', 'jquery-ui-tooltip' ) );
	$path_array = array( 'imageChecked' => plugins_url( 'js/check.png', dirname(__FILE__)), 'image' => plugins_url( 'js/unchecked.png', dirname(__FILE__)));
	wp_localize_script( 'seatmap-script', 'image_path', $path_array );
	wp_enqueue_script( 'seatmap-script' );
}
