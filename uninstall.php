<?php 

function freeseat_uninstall {
	global $wpdb;
 	$tables = array( 
 			'freeseat_booking', 
 			'freeseat_price', 
 			'freeseat_seats', 
 			'freeseat_shows', 
 			'freeseat_spectacles', 
 			'freeseat_theatres', 
 			'freeseat_class_comment', 
 			'freeseat_seat_locks' 
 	);
	foreach( $tables as $table_name ) {
		$sql = "DROP TABLE $table_name ;";
		$wpdb->query( $sql );
	}
	delete_option( "freeseat_db_version" );
	delete_option( "freeseat_options" );
	delete_option( "freeseat_data_installed" );
}

freeseat_uninstall();
