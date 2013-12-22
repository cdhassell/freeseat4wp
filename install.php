<?php

register_activation_hook( __FILE__, 'freeseat_install' );
register_activation_hook( __FILE__, 'freeseat_install_data' );
register_deactivation_hook(__FILE__, 'freeseat_deactivate');
register_uninstall_hook('freeseat-uninstall.php', 'uninstall');


/**
 * Freeseat plugin install routine.
 * Creates tables and sets version number.
 * With two spaces after PRIMARY KEY!
 */
function freeseat_install() {
	global $wpdb, $freeseat_db_version;
	
   $table_name = 'freeseat_booking';
      
   $sql = "CREATE TABLE $table_name (
	id int(10) NOT NULL AUTO_INCREMENT,
	seat int(10) NOT NULL DEFAULT '0',
	state int(2) NOT NULL DEFAULT '0',
	cat int(2) NOT NULL DEFAULT '0',
	firstname varchar(128) NOT NULL DEFAULT '',
	lastname varchar(128) NOT NULL DEFAULT '',
	email varchar(128) DEFAULT NULL,
	phone varchar(128) DEFAULT NULL,
	timestamp datetime DEFAULT NULL,
	payment int(2) NOT NULL DEFAULT '0',
	groupid int(10) DEFAULT NULL,
	showid int(10) NOT NULL DEFAULT '0',
	address varchar(255) DEFAULT NULL,
	postalcode varchar(15) DEFAULT NULL,
	city varchar(127) DEFAULT NULL,
	us_state varchar(2) DEFAULT NULL,
	country varchar(2) DEFAULT NULL,
	notes varchar(255) DEFAULT NULL,
	expiration datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	contact_id int(11) NOT NULL,
	PRIMARY KEY  (id)
	);";

   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
   dbDelta( $sql );
 
   $table_name = 'freeseat_price';
      
   $sql = "CREATE TABLE $table_name (
	spectacle int(7) DEFAULT NULL,
	cat int(2) DEFAULT NULL,
	class int(3) DEFAULT NULL,
	amount int(11) DEFAULT NULL,
	KEY spectacle (spectacle,cat,class)
    );";

   dbDelta( $sql );

   $table_name = 'freeseat_seats';
      
   $sql = "CREATE TABLE $table_name (
	id int(10) NOT NULL AUTO_INCREMENT,
	theatre int(7) NOT NULL DEFAULT '0',
	row varchar(5) NOT NULL DEFAULT '',
	col varchar(5) NOT NULL DEFAULT '',
	extra varchar(64) DEFAULT NULL,
	zone varchar(32) DEFAULT NULL,
	class int(3) NOT NULL DEFAULT '0',
	x int(3) NOT NULL DEFAULT '0',
	y int(3) NOT NULL DEFAULT '0',
	PRIMARY KEY  (id)
	);";

	dbDelta( $sql );
	
	$table_name = 'freeseat_shows';
      
  $sql = "CREATE TABLE $table_name (
	id int(10) NOT NULL AUTO_INCREMENT,
	spectacle int(7) DEFAULT NULL,
 	theatre int(7) DEFAULT NULL,
	date date DEFAULT NULL,
	time time DEFAULT NULL,
	disabled int(1) NOT NULL DEFAULT '0',
	civicrm_id int(10) unsigned DEFAULT NULL,
	PRIMARY KEY  (id)
  );";
	
	dbDelta( $sql );
	
	$table_name = 'freeseat_spectacles';
      
  $sql = "CREATE TABLE $table_name (
	id int(7) NOT NULL AUTO_INCREMENT,
	name varchar(64) DEFAULT NULL,
	imagesrc varchar(64) DEFAULT NULL,
	description text,
	castpw varchar(64) DEFAULT NULL,
	PRIMARY KEY  (id)
  );";

	dbDelta( $sql );
	
	$table_name = 'freeseat_theatres';
      
  $sql = "CREATE TABLE $table_name (
	id int(7) NOT NULL AUTO_INCREMENT,
	name varchar(64) DEFAULT NULL,
	imagesrc varchar(64) DEFAULT NULL,
	staggered_seating int(1) DEFAULT '0',
	PRIMARY KEY  (id)
  );";
  
	dbDelta( $sql );
	
	$table_name = 'freeseat_class_comment';
      
  $sql = "CREATE TABLE $table_name ( 
	spectacle int(7) NOT NULL DEFAULT '0',
 	class int(2) NOT NULL DEFAULT '0',
	comment varchar(64) DEFAULT NULL,
	description varchar(64) DEFAULT NULL,
	UNIQUE KEY spectacle (spectacle,class)
  );";
  
	dbDelta( $sql );  
  
	add_option( "freeseat_db_version", $freeseat_db_version );
}

/**
 * Install default data.
 */
function freeseat_install_data() {
	global $wpdb;
	
	if ( ($cnt = $wpdb->get_var( 'SELECT count( id ) FROM freeseat_seats' )) > 0 ) {
		print "<pre>Found $cnt records in the seats table</pre>"; 
		return;
	}		 
	$wpdb->query("INSERT INTO freeseat_booking (id, seat, state, cat, firstname, lastname, email, phone, timestamp, payment, groupid, showid, address, postalcode, city, us_state, country, notes, expiration) VALUES
(82666, 5877, 4, 2, 'Office', 'Sale', '', '', '2013-11-08 16:19:05', 0, NULL, 258, '', '', '', 'AL', 'AF', NULL, '0000-00-00 00:00:00'),
(82667, 5878, 4, 2, 'Office', 'Sale', '', '', '2013-11-08 16:19:05', 0, 82666, 258, '', '', '', 'AL', 'AF', NULL, '0000-00-00 00:00:00');");
	$wpdb->query("INSERT INTO freeseat_class_comment (spectacle, class, comment, description) VALUES
(76, 1, 'Red Seating', NULL),
(76, 2, 'Silver Seating', NULL);");
	$wpdb->query("INSERT INTO freeseat_price (spectacle, cat, class, amount) VALUES
(76, 2, 1, 1500),
(76, 1, 1, 1300),
(76, 2, 2, 1300),
(76, 1, 2, 1100),
(76, 2, 3, 0),
(76, 1, 3, 0),
(76, 2, 4, 0),
(76, 1, 4, 0);");
	$wpdb->query("INSERT INTO freeseat_seats (id, theatre, row, col, extra, zone, class, x, y) VALUES
(5877, 14, 'A', '1', '', 'Main', 1, 2, 1),
(5878, 14, 'A', '2', '', 'Main', 1, 3, 1),
(5879, 14, 'A', '3', '', 'Main', 1, 4, 1),
(5880, 14, 'A', '4', '', 'Main', 1, 5, 1),
(5881, 14, 'A', '5', '', 'Main', 1, 6, 1),
(5882, 14, 'A', '6', '', 'Main', 1, 7, 1),
(5883, 14, 'A', '7', '', 'Main', 1, 8, 1),
(5884, 14, 'A', '8', '', 'Main', 1, 9, 1),
(5885, 14, 'A', '9', '', 'Main', 1, 10, 1),
(5886, 14, 'A', '10', '', 'Main', 1, 11, 1),
(5887, 14, 'A', '11', '', 'Main', 1, 12, 1),
(5888, 14, 'A', '12', '', 'Main', 1, 13, 1),
(5889, 14, 'A', '13', '', 'Main', 1, 14, 1),
(5890, 14, 'A', '14', '', 'Main', 1, 15, 1),
(5891, 14, 'A', '15', '', 'Main', 1, 16, 1),
(5892, 14, 'A', '16', '', 'Main', 1, 19, 1),
(5893, 14, 'A', '17', '', 'Main', 1, 20, 1),
(5894, 14, 'A', '18', '', 'Main', 1, 21, 1),
(5895, 14, 'A', '19', '', 'Main', 1, 22, 1),
(5896, 14, 'A', '20', '', 'Main', 1, 23, 1),
(5897, 14, 'A', '21', '', 'Main', 1, 24, 1),
(5898, 14, 'A', '22', '', 'Main', 1, 25, 1),
(5899, 14, 'A', '23', '', 'Main', 1, 26, 1),
(5900, 14, 'A', '24', '', 'Main', 1, 27, 1),
(5901, 14, 'A', '25', '', 'Main', 1, 28, 1),
(5902, 14, 'A', '26', '', 'Main', 1, 29, 1),
(5903, 14, 'A', '27', '', 'Main', 1, 30, 1),
(5904, 14, 'A', '28', '', 'Main', 1, 31, 1),
(5905, 14, 'A', '29', '', 'Main', 1, 32, 1),
(5906, 14, 'A', '30', '', 'Main', 1, 33, 1),
(5907, 14, 'B', '1', '', 'Main', 1, 2, 2),
(5908, 14, 'B', '2', '', 'Main', 1, 3, 2),
(5909, 14, 'B', '3', '', 'Main', 1, 4, 2),
(5910, 14, 'B', '4', '', 'Main', 1, 5, 2),
(5911, 14, 'B', '5', '', 'Main', 1, 6, 2),
(5912, 14, 'B', '6', '', 'Main', 1, 7, 2),
(5913, 14, 'B', '7', '', 'Main', 1, 8, 2),
(5914, 14, 'B', '8', '', 'Main', 1, 9, 2),
(5915, 14, 'B', '9', '', 'Main', 1, 10, 2),
(5916, 14, 'B', '10', '', 'Main', 1, 11, 2),
(5917, 14, 'B', '11', '', 'Main', 1, 12, 2),
(5918, 14, 'B', '12', '', 'Main', 1, 13, 2),
(5919, 14, 'B', '13', '', 'Main', 1, 14, 2),
(5920, 14, 'B', '14', '', 'Main', 1, 15, 2),
(5921, 14, 'B', '15', '', 'Main', 1, 16, 2),
(5922, 14, 'B', '16', '', 'Main', 1, 19, 2),
(5923, 14, 'B', '17', '', 'Main', 1, 20, 2),
(5924, 14, 'B', '18', '', 'Main', 1, 21, 2),
(5925, 14, 'B', '19', '', 'Main', 1, 22, 2),
(5926, 14, 'B', '20', '', 'Main', 1, 23, 2),
(5927, 14, 'B', '21', '', 'Main', 1, 24, 2),
(5928, 14, 'B', '22', '', 'Main', 1, 25, 2),
(5929, 14, 'B', '23', '', 'Main', 1, 26, 2),
(5930, 14, 'B', '24', '', 'Main', 1, 27, 2),
(5931, 14, 'B', '25', '', 'Main', 1, 28, 2),
(5932, 14, 'B', '26', '', 'Main', 1, 29, 2),
(5933, 14, 'B', '27', '', 'Main', 1, 30, 2),
(5934, 14, 'B', '28', '', 'Main', 1, 31, 2),
(5935, 14, 'B', '29', '', 'Main', 1, 32, 2),
(5936, 14, 'B', '30', '', 'Main', 1, 33, 2),
(5937, 14, 'C', '1', '', 'Main', 1, 2, 3),
(5938, 14, 'C', '2', '', 'Main', 1, 3, 3),
(5939, 14, 'C', '3', '', 'Main', 1, 4, 3),
(5940, 14, 'C', '4', '', 'Main', 1, 5, 3),
(5941, 14, 'C', '5', '', 'Main', 1, 6, 3),
(5942, 14, 'C', '6', '', 'Main', 1, 7, 3),
(5943, 14, 'C', '7', '', 'Main', 1, 8, 3),
(5944, 14, 'C', '8', '', 'Main', 1, 9, 3),
(5945, 14, 'C', '9', '', 'Main', 1, 10, 3),
(5946, 14, 'C', '10', '', 'Main', 1, 11, 3),
(5947, 14, 'C', '11', '', 'Main', 1, 12, 3),
(5948, 14, 'C', '12', '', 'Main', 1, 13, 3),
(5949, 14, 'C', '13', '', 'Main', 1, 14, 3),
(5950, 14, 'C', '14', '', 'Main', 1, 15, 3),
(5951, 14, 'C', '15', '', 'Main', 1, 16, 3),
(5952, 14, 'C', '16', '', 'Main', 1, 19, 3),
(5953, 14, 'C', '17', '', 'Main', 1, 20, 3),
(5954, 14, 'C', '18', '', 'Main', 1, 21, 3),
(5955, 14, 'C', '19', '', 'Main', 1, 22, 3),
(5956, 14, 'C', '20', '', 'Main', 1, 23, 3),
(5957, 14, 'C', '21', '', 'Main', 1, 24, 3),
(5958, 14, 'C', '22', '', 'Main', 1, 25, 3),
(5959, 14, 'C', '23', '', 'Main', 1, 26, 3),
(5960, 14, 'C', '24', '', 'Main', 1, 27, 3),
(5961, 14, 'C', '25', '', 'Main', 1, 28, 3),
(5962, 14, 'C', '26', '', 'Main', 1, 29, 3),
(5963, 14, 'C', '27', '', 'Main', 1, 30, 3),
(5964, 14, 'C', '28', '', 'Main', 1, 31, 3),
(5965, 14, 'C', '29', '', 'Main', 1, 32, 3),
(5966, 14, 'C', '30', '', 'Main', 1, 33, 3),
(5967, 14, 'D', '1', '', 'Main', 1, 2, 4),
(5968, 14, 'D', '2', '', 'Main', 1, 3, 4),
(5969, 14, 'D', '3', '', 'Main', 1, 4, 4),
(5970, 14, 'D', '4', '', 'Main', 1, 5, 4),
(5971, 14, 'D', '5', '', 'Main', 1, 6, 4),
(5972, 14, 'D', '6', '', 'Main', 1, 7, 4),
(5973, 14, 'D', '7', '', 'Main', 1, 8, 4),
(5974, 14, 'D', '8', '', 'Main', 1, 9, 4),
(5975, 14, 'D', '9', '', 'Main', 1, 10, 4),
(5976, 14, 'D', '10', '', 'Main', 1, 11, 4),
(5977, 14, 'D', '11', '', 'Main', 1, 12, 4),
(5978, 14, 'D', '12', '', 'Main', 1, 13, 4),
(5979, 14, 'D', '13', '', 'Main', 1, 14, 4),
(5980, 14, 'D', '14', '', 'Main', 1, 15, 4),
(5981, 14, 'D', '15', '', 'Main', 1, 16, 4),
(5982, 14, 'D', '16', '', 'Main', 1, 19, 4),
(5983, 14, 'D', '17', '', 'Main', 1, 20, 4),
(5984, 14, 'D', '18', '', 'Main', 1, 21, 4),
(5985, 14, 'D', '19', '', 'Main', 1, 22, 4),
(5986, 14, 'D', '20', '', 'Main', 1, 23, 4),
(5987, 14, 'D', '21', '', 'Main', 1, 24, 4),
(5988, 14, 'D', '22', '', 'Main', 1, 25, 4),
(5989, 14, 'D', '23', '', 'Main', 1, 26, 4),
(5990, 14, 'D', '24', '', 'Main', 1, 27, 4),
(5991, 14, 'D', '25', '', 'Main', 1, 28, 4),
(5992, 14, 'D', '26', '', 'Main', 1, 29, 4),
(5993, 14, 'D', '27', '', 'Main', 1, 30, 4),
(5994, 14, 'D', '28', '', 'Main', 1, 31, 4),
(5995, 14, 'D', '29', '', 'Main', 1, 32, 4),
(5996, 14, 'D', '30', '', 'Main', 1, 33, 4),
(5997, 14, 'E', '1', '', 'Main', 2, 1, 6),
(5998, 14, 'E', '2', '', 'Main', 2, 2, 6),
(5999, 14, 'E', '3', '', 'Main', 2, 3, 6),
(6000, 14, 'E', '4', '', 'Main', 2, 4, 6),
(6001, 14, 'E', '5', '', 'Main', 2, 5, 6),
(6002, 14, 'E', '6', '', 'Main', 2, 6, 6),
(6003, 14, 'E', '7', '', 'Main', 2, 7, 6),
(6004, 14, 'E', '8', '', 'Main', 2, 8, 6),
(6005, 14, 'E', '9', '', 'Main', 2, 9, 6),
(6006, 14, 'E', '10', '', 'Main', 2, 10, 6),
(6007, 14, 'E', '11', '', 'Main', 2, 11, 6),
(6008, 14, 'E', '12', '', 'Main', 2, 12, 6),
(6009, 14, 'E', '13', '', 'Main', 2, 13, 6),
(6010, 14, 'E', '14', '', 'Main', 2, 14, 6),
(6011, 14, 'E', '15', '', 'Main', 2, 15, 6),
(6012, 14, 'E', '16', '', 'Main', 2, 16, 6),
(6013, 14, 'E', '17', '', 'Main', 2, 19, 6),
(6014, 14, 'E', '18', '', 'Main', 2, 20, 6),
(6015, 14, 'E', '19', '', 'Main', 2, 21, 6),
(6016, 14, 'E', '20', '', 'Main', 2, 22, 6),
(6017, 14, 'E', '21', '', 'Main', 2, 23, 6),
(6018, 14, 'E', '22', '', 'Main', 2, 24, 6),
(6019, 14, 'E', '23', '', 'Main', 2, 25, 6),
(6020, 14, 'E', '24', '', 'Main', 2, 26, 6),
(6021, 14, 'E', '25', '', 'Main', 2, 27, 6),
(6022, 14, 'E', '26', '', 'Main', 2, 28, 6),
(6023, 14, 'E', '27', '', 'Main', 2, 29, 6),
(6024, 14, 'E', '28', '', 'Main', 2, 30, 6),
(6025, 14, 'E', '29', '', 'Main', 2, 31, 6),
(6026, 14, 'E', '30', '', 'Main', 2, 32, 6),
(6027, 14, 'E', '31', '', 'Main', 2, 33, 6),
(6028, 14, 'E', '32', '', 'Main', 2, 34, 6),
(6029, 14, 'F', '1', '', 'Main', 2, 1, 7),
(6030, 14, 'F', '2', '', 'Main', 2, 2, 7),
(6031, 14, 'F', '3', '', 'Main', 2, 3, 7),
(6032, 14, 'F', '4', '', 'Main', 2, 4, 7),
(6033, 14, 'F', '5', '', 'Main', 2, 5, 7),
(6034, 14, 'F', '6', '', 'Main', 2, 6, 7),
(6035, 14, 'F', '7', '', 'Main', 2, 7, 7),
(6036, 14, 'F', '8', '', 'Main', 2, 8, 7),
(6037, 14, 'F', '9', '', 'Main', 2, 9, 7),
(6038, 14, 'F', '10', '', 'Main', 2, 10, 7),
(6039, 14, 'F', '11', '', 'Main', 2, 11, 7),
(6040, 14, 'F', '12', '', 'Main', 2, 12, 7),
(6041, 14, 'F', '13', '', 'Main', 2, 13, 7),
(6042, 14, 'F', '14', '', 'Main', 2, 14, 7),
(6043, 14, 'F', '15', '', 'Main', 2, 15, 7),
(6044, 14, 'F', '16', '', 'Main', 2, 16, 7),
(6045, 14, 'F', '17', '', 'Main', 2, 19, 7),
(6046, 14, 'F', '18', '', 'Main', 2, 20, 7),
(6047, 14, 'F', '19', '', 'Main', 2, 21, 7),
(6048, 14, 'F', '20', '', 'Main', 2, 22, 7),
(6049, 14, 'F', '21', '', 'Main', 2, 23, 7),
(6050, 14, 'F', '22', '', 'Main', 2, 24, 7),
(6051, 14, 'F', '23', '', 'Main', 2, 25, 7),
(6052, 14, 'F', '24', '', 'Main', 2, 26, 7),
(6053, 14, 'F', '25', '', 'Main', 2, 27, 7),
(6054, 14, 'F', '26', '', 'Main', 2, 28, 7),
(6055, 14, 'F', '27', '', 'Main', 2, 29, 7),
(6056, 14, 'F', '28', '', 'Main', 2, 30, 7),
(6057, 14, 'F', '29', '', 'Main', 2, 31, 7),
(6058, 14, 'F', '30', '', 'Main', 2, 32, 7),
(6059, 14, 'F', '31', '', 'Main', 2, 33, 7),
(6060, 14, 'F', '32', '', 'Main', 2, 34, 7),
(6061, 14, 'G', '1', '', 'Main', 2, 1, 8),
(6062, 14, 'G', '2', '', 'Main', 2, 2, 8),
(6063, 14, 'G', '3', '', 'Main', 2, 3, 8),
(6064, 14, 'G', '4', '', 'Main', 2, 4, 8),
(6065, 14, 'G', '5', '', 'Main', 2, 5, 8),
(6066, 14, 'G', '6', '', 'Main', 2, 6, 8),
(6067, 14, 'G', '7', '', 'Main', 2, 7, 8),
(6068, 14, 'G', '8', '', 'Main', 2, 8, 8),
(6069, 14, 'G', '9', '', 'Main', 2, 9, 8),
(6070, 14, 'G', '10', '', 'Main', 2, 10, 8),
(6071, 14, 'G', '11', '', 'Main', 2, 11, 8),
(6072, 14, 'G', '12', '', 'Main', 2, 12, 8),
(6073, 14, 'G', '13', '', 'Main', 2, 13, 8),
(6074, 14, 'G', '14', '', 'Main', 2, 14, 8),
(6075, 14, 'G', '15', '', 'Main', 2, 15, 8),
(6076, 14, 'G', '16', '', 'Main', 2, 16, 8),
(6077, 14, 'G', '17', '', 'Main', 2, 19, 8),
(6078, 14, 'G', '18', '', 'Main', 2, 20, 8),
(6079, 14, 'G', '19', '', 'Main', 2, 21, 8),
(6080, 14, 'G', '20', '', 'Main', 2, 22, 8),
(6081, 14, 'G', '21', '', 'Main', 2, 23, 8),
(6082, 14, 'G', '22', '', 'Main', 2, 24, 8),
(6083, 14, 'G', '23', '', 'Main', 2, 25, 8),
(6084, 14, 'G', '24', '', 'Main', 2, 26, 8),
(6085, 14, 'G', '25', '', 'Main', 2, 27, 8),
(6086, 14, 'G', '26', '', 'Main', 2, 28, 8),
(6087, 14, 'G', '27', '', 'Main', 2, 29, 8),
(6088, 14, 'G', '28', '', 'Main', 2, 30, 8),
(6089, 14, 'G', '29', '', 'Main', 2, 31, 8),
(6090, 14, 'G', '30', '', 'Main', 2, 32, 8),
(6091, 14, 'G', '31', '', 'Main', 2, 33, 8),
(6092, 14, 'G', '32', '', 'Main', 2, 34, 8),
(6093, 14, 'H', '1', '', 'Main', 2, 1, 9),
(6094, 14, 'H', '2', '', 'Main', 2, 2, 9),
(6095, 14, 'H', '3', '', 'Main', 2, 3, 9),
(6096, 14, 'H', '4', '', 'Main', 2, 4, 9),
(6097, 14, 'H', '5', '', 'Main', 2, 5, 9),
(6098, 14, 'H', '6', '', 'Main', 2, 6, 9),
(6099, 14, 'H', '7', '', 'Main', 2, 7, 9),
(6100, 14, 'H', '8', '', 'Main', 2, 8, 9),
(6101, 14, 'H', '9', '', 'Main', 2, 9, 9),
(6102, 14, 'H', '10', '', 'Main', 2, 10, 9),
(6103, 14, 'H', '11', '', 'Main', 2, 11, 9),
(6104, 14, 'H', '12', '', 'Main', 2, 12, 9),
(6105, 14, 'H', '13', '', 'Main', 2, 13, 9),
(6106, 14, 'H', '14', '', 'Main', 2, 14, 9),
(6107, 14, 'H', '15', '', 'Main', 2, 15, 9),
(6108, 14, 'H', '16', '', 'Main', 2, 16, 9),
(6109, 14, 'H', '17', '', 'Main', 2, 19, 9),
(6110, 14, 'H', '18', '', 'Main', 2, 20, 9),
(6111, 14, 'H', '19', '', 'Main', 2, 21, 9),
(6112, 14, 'H', '20', '', 'Main', 2, 22, 9),
(6113, 14, 'H', '21', '', 'Main', 2, 23, 9),
(6114, 14, 'H', '22', '', 'Main', 2, 24, 9),
(6115, 14, 'H', '23', '', 'Main', 2, 25, 9),
(6116, 14, 'H', '24', '', 'Main', 2, 26, 9),
(6117, 14, 'H', '25', '', 'Main', 2, 27, 9),
(6118, 14, 'H', '26', '', 'Main', 2, 28, 9),
(6119, 14, 'H', '27', '', 'Main', 2, 29, 9),
(6120, 14, 'H', '28', '', 'Main', 2, 30, 9),
(6121, 14, 'H', '29', '', 'Main', 2, 31, 9),
(6122, 14, 'H', '30', '', 'Main', 2, 32, 9),
(6123, 14, 'H', '31', '', 'Main', 2, 33, 9),
(6124, 14, 'H', '32', '', 'Main', 2, 34, 9),
(6125, 14, 'I', '1', '', 'Main', 2, 1, 10),
(6126, 14, 'I', '2', '', 'Main', 2, 2, 10),
(6127, 14, 'I', '3', '', 'Main', 2, 3, 10),
(6128, 14, 'I', '4', '', 'Main', 2, 4, 10),
(6129, 14, 'I', '5', '', 'Main', 2, 5, 10),
(6130, 14, 'I', '6', '', 'Main', 2, 6, 10),
(6131, 14, 'I', '7', '', 'Main', 2, 7, 10),
(6132, 14, 'I', '8', '', 'Main', 2, 8, 10),
(6133, 14, 'I', '9', '', 'Main', 2, 9, 10),
(6134, 14, 'I', '10', '', 'Main', 2, 10, 10),
(6135, 14, 'I', '11', '', 'Main', 2, 11, 10),
(6136, 14, 'I', '12', '', 'Main', 2, 12, 10),
(6137, 14, 'I', '13', '', 'Main', 2, 13, 10),
(6138, 14, 'I', '14', '', 'Main', 2, 14, 10),
(6139, 14, 'I', '15', '', 'Main', 2, 15, 10),
(6140, 14, 'I', '16', '', 'Main', 2, 16, 10),
(6141, 14, 'I', '17', '', 'Main', 2, 19, 10),
(6142, 14, 'I', '18', '', 'Main', 2, 20, 10),
(6143, 14, 'I', '19', '', 'Main', 2, 21, 10),
(6144, 14, 'I', '20', '', 'Main', 2, 22, 10),
(6145, 14, 'I', '21', '', 'Main', 2, 23, 10),
(6146, 14, 'I', '22', '', 'Main', 2, 24, 10),
(6147, 14, 'I', '23', '', 'Main', 2, 25, 10),
(6148, 14, 'I', '24', '', 'Main', 2, 26, 10),
(6149, 14, 'I', '25', '', 'Main', 2, 27, 10),
(6150, 14, 'I', '26', '', 'Main', 2, 28, 10),
(6151, 14, 'I', '27', '', 'Main', 2, 29, 10),
(6152, 14, 'I', '28', '', 'Main', 2, 30, 10),
(6153, 14, 'I', '29', '', 'Main', 2, 31, 10),
(6154, 14, 'I', '30', '', 'Main', 2, 32, 10),
(6155, 14, 'I', '31', '', 'Main', 2, 33, 10),
(6156, 14, 'I', '32', '', 'Main', 2, 34, 10),
(6157, 14, 'J', '1', '', 'Main', 2, 1, 11),
(6158, 14, 'J', '2', '', 'Main', 2, 2, 11),
(6159, 14, 'J', '3', '', 'Main', 2, 3, 11),
(6160, 14, 'J', '4', '', 'Main', 2, 4, 11),
(6161, 14, 'J', '5', '', 'Main', 2, 5, 11),
(6162, 14, 'J', '6', '', 'Main', 2, 6, 11),
(6163, 14, 'J', '7', '', 'Main', 2, 7, 11),
(6164, 14, 'J', '8', '', 'Main', 2, 8, 11),
(6165, 14, 'J', '9', '', 'Main', 2, 9, 11),
(6166, 14, 'J', '10', '', 'Main', 2, 10, 11),
(6167, 14, 'J', '11', '', 'Main', 2, 11, 11),
(6168, 14, 'J', '12', '', 'Main', 2, 12, 11),
(6169, 14, 'J', '13', '', 'Main', 2, 13, 11),
(6170, 14, 'J', '14', '', 'Main', 2, 14, 11),
(6171, 14, 'J', '15', '', 'Main', 2, 15, 11),
(6172, 14, 'J', '16', '', 'Main', 2, 16, 11),
(6173, 14, 'J', '17', '', 'Main', 2, 19, 11),
(6174, 14, 'J', '18', '', 'Main', 2, 20, 11),
(6175, 14, 'J', '19', '', 'Main', 2, 21, 11),
(6176, 14, 'J', '20', '', 'Main', 2, 22, 11),
(6177, 14, 'J', '21', '', 'Main', 2, 23, 11),
(6178, 14, 'J', '22', '', 'Main', 2, 24, 11),
(6179, 14, 'J', '23', '', 'Main', 2, 25, 11),
(6180, 14, 'J', '24', '', 'Main', 2, 26, 11),
(6181, 14, 'J', '25', '', 'Main', 2, 27, 11),
(6182, 14, 'J', '26', '', 'Main', 2, 28, 11),
(6183, 14, 'J', '27', '', 'Main', 2, 29, 11),
(6184, 14, 'J', '28', '', 'Main', 2, 30, 11),
(6185, 14, 'J', '29', '', 'Main', 2, 31, 11),
(6186, 14, 'J', '30', '', 'Main', 2, 32, 11),
(6187, 14, 'J', '31', '', 'Main', 2, 33, 11),
(6188, 14, 'J', '32', '', 'Main', 2, 34, 11);");
	$wpdb->query("INSERT INTO freeseat_shows (id, spectacle, theatre, date, time, disabled, civicrm_id) VALUES
(258, 76, 14, '2013-12-13', '19:30:00', 0, 196),
(259, 76, 14, '2013-12-14', '19:30:00', 0, 197),
(260, 76, 14, '2013-12-15', '14:30:00', 0, 198);");
	$wpdb->query("INSERT INTO freeseat_spectacles (id, name, imagesrc, description, castpw) VALUES
(76, 'The Merchant of Venice', '', 'The famous stage play by the Bard', '');");
	$wpdb->query("INSERT INTO freeseat_theatres (id, name, imagesrc, staggered_seating) VALUES
	(14, 'Two reserved classes 4x30&6x32, lettered rows', NULL, 0);");
}

/**
 * Placeholder for plugin deactivation routine.
 */
function freeseat_deactivate()
{
	print "<pre>Freeseat deactivated</pre>";
}


add_action('activated_plugin','save_error');

function save_error(){
    update_option('plugin_error',  ob_get_contents());
}
 
// echo get_option('plugin_error');


