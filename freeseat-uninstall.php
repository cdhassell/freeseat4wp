<?php 

/*
 *  get sheaffer hall
 *  get volunteers
 *  get materials
 */

function freeseat_uninstall {
	global $wpdb;
 	$tables = array( 'freeseat_booking', 'freeseat_price', 'freeseat_seats', 'freeseat_shows', 
 		'freeseat_spectacles', 'freeseat_theatres', 'freeseat_class_comment' );
	foreach( $tables as $table_name ) {
		$sql = "DROP TABLE $table_name ;";
		$wpdb->query( $sql );
	}
	delete_option( "freeseat_db_version" );
}

freeseat_uninstall();
