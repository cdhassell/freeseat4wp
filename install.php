<?php namespace freeseat;

register_activation_hook(   FS_PATH . 'freeseat.php', __NAMESPACE__ . '\\freeseat_install'   );
register_deactivation_hook( FS_PATH . 'freeseat.php', __NAMESPACE__ . '\\freeseat_deactivate');
add_action( 'wp_loaded', __NAMESPACE__ . '\\freeseat_check_data' );
add_filter( 'plugin_action_links_freeseat/freeseat.php', __NAMESPACE__ . '\\freeseat_sample_data_link' );
add_action( 'activated_plugin', __NAMESPACE__ . '\\save_error');

// this is for debugging purposes - captures startup error messages and saves them in the db
function save_error() {
    update_option('plugin_error',  ob_get_contents());
}

/**
 *  Adds a link to the plugin screen for installing sample data
 *  It disappears once there is data in the tables
 */
function freeseat_sample_data_link( $links ) {
	if (!get_option('freeseat_data_installed')) {
		$links[] = '<a href="'. admin_url( 'plugins.php?install=data&plugin=freeseat') .'">Add sample data</a>';
	}
	return $links;
}

/**
 *  Checks if we have a request to install sample data
 *  If so it calls freeseat_install_data()
 */
function freeseat_check_data() {
	if ( 
		!get_option('freeseat_data_installed') &&
		isset($_GET['install']) && $_GET['install']=='data' &&
		isset($_GET['plugin'] ) && $_GET['plugin' ]=='freeseat' 
	) {		
		freeseat_install_data();
	}
}

/**
 * Freeseat plugin install routine.
 * Creates tables and sets version number.
 * With two spaces after PRIMARY KEY!
 */
function freeseat_install() {
	global $wpdb, $freeseat_db_version;
	
   $table_name = 'freeseat_booking';
      
   $sql = "CREATE TABLE IF NOT EXISTS $table_name (
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
      
   $sql = "CREATE TABLE IF NOT EXISTS $table_name (
	spectacle int(7) DEFAULT NULL,
	cat int(2) DEFAULT NULL,
	class int(3) DEFAULT NULL,
	amount int(11) DEFAULT NULL,
	KEY spectacle (spectacle,cat,class)
    );";

   dbDelta( $sql );

   $table_name = 'freeseat_seats';
      
   $sql = "CREATE TABLE IF NOT EXISTS $table_name (
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
      
  $sql = "CREATE TABLE IF NOT EXISTS $table_name (
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
      
  $sql = "CREATE TABLE IF NOT EXISTS $table_name (
	id int(7) NOT NULL AUTO_INCREMENT,
	name varchar(64) DEFAULT NULL,
	imagesrc varchar(64) DEFAULT NULL,
	description text,
	castpw varchar(64) DEFAULT NULL,
	PRIMARY KEY  (id)
  );";

	dbDelta( $sql );
	
	$table_name = 'freeseat_theatres';
      
  $sql = "CREATE TABLE IF NOT EXISTS $table_name (
	id int(7) NOT NULL AUTO_INCREMENT,
	name varchar(64) DEFAULT NULL,
	imagesrc varchar(64) DEFAULT NULL,
	staggered_seating int(1) DEFAULT '0',
	PRIMARY KEY  (id)
  );";
  
	dbDelta( $sql );
	
	$table_name = 'freeseat_class_comment';
      
  $sql = "CREATE TABLE IF NOT EXISTS $table_name ( 
	spectacle int(7) NOT NULL DEFAULT '0',
 	class int(2) NOT NULL DEFAULT '0',
	comment varchar(64) DEFAULT NULL,
	description varchar(64) DEFAULT NULL,
	UNIQUE KEY spectacle (spectacle,class)
  );";
  
	dbDelta( $sql );  
	
	$table_name = 'freeseat_seat_locks';
	
  $sql = "CREATE TABLE IF NOT EXISTS $table_name (
	seatid int(10) NOT NULL DEFAULT '0',
	showid int(10) NOT NULL DEFAULT '0',
	sid varchar(50) NOT NULL DEFAULT '0',
	until int(10) DEFAULT NULL,
	PRIMARY KEY  (seatid, showid)
  );";
	
	dbDelta( $sql );
	
	add_option( "freeseat_db_version", $freeseat_db_version );
	
}

/**
 *  Install sample data.
 */
function freeseat_install_data() {
	global $wpdb;
	
	update_option('freeseat_data_installed', TRUE);
	// if there is data in the tables, don't do this
	if ( $wpdb->get_var( 'SELECT count( id ) FROM freeseat_seats' ) > 0 ) return;	
	
	// set sample show dates at two months in the future
	$showdate1 = strftime( "%F", time()+(60*60*24*60) );
	$showdate2 = strftime( "%F", time()+(60*60*24*61) );
	// set sample purchase dates at current date
	$buydate = strftime( "%F" ) . ' 00:00:00';
	
	$wpdb->query("INSERT INTO freeseat_booking (id, seat, state, cat, firstname, lastname, email, phone, timestamp, payment, groupid, showid, address, postalcode, city, us_state, country, notes, expiration) VALUES
(1, 14, 4, 2, 'Office', 'Sale', '', '', '$buydate', 0, NULL, 1, '', '', '', 'PA', 'US', NULL, '0000-00-00 00:00:00'),
(2, 15, 4, 2, 'Office', 'Sale', '', '', '$buydate', 0, 1, 1, '', '', '', 'PA', 'US', NULL, '0000-00-00 00:00:00');");
	$wpdb->query("INSERT INTO freeseat_class_comment (spectacle, class, comment, description) VALUES
(1, 1, 'Section A', NULL),
(1, 2, 'Section B', NULL);");
	$wpdb->query("INSERT INTO freeseat_price (spectacle, cat, class, amount) VALUES
(1, 2, 1, 2500),
(1, 1, 1, 2000),
(1, 2, 2, 1800),
(1, 1, 2, 1500),
(1, 2, 3, 0),
(1, 1, 3, 0),
(1, 2, 4, 0),
(1, 1, 4, 0);");
	$wpdb->query("INSERT INTO freeseat_seats (id, theatre, row, col, extra, zone, class, x, y) VALUES
(1, 1, 'A', '1', '', 'Main', 1, 2, 1),
(2, 1, 'A', '2', '', 'Main', 1, 3, 1),
(3, 1, 'A', '3', '', 'Main', 1, 4, 1),
(4, 1, 'A', '4', '', 'Main', 1, 5, 1),
(5, 1, 'A', '5', '', 'Main', 1, 6, 1),
(6, 1, 'A', '6', '', 'Main', 1, 7, 1),
(7, 1, 'A', '7', '', 'Main', 1, 8, 1),
(8, 1, 'A', '8', '', 'Main', 1, 9, 1),
(9, 1, 'A', '9', '', 'Main', 1, 10, 1),
(10, 1, 'A', '10', '', 'Main', 1, 11, 1),
(11, 1, 'A', '11', '', 'Main', 1, 12, 1),
(12, 1, 'A', '12', '', 'Main', 1, 13, 1),
(13, 1, 'A', '13', '', 'Main', 1, 14, 1),
(14, 1, 'A', '14', '', 'Main', 1, 15, 1),
(15, 1, 'A', '15', '', 'Main', 1, 16, 1),
(16, 1, 'A', '16', '', 'Main', 1, 19, 1),
(17, 1, 'A', '17', '', 'Main', 1, 20, 1),
(18, 1, 'A', '18', '', 'Main', 1, 21, 1),
(19, 1, 'A', '19', '', 'Main', 1, 22, 1),
(20, 1, 'A', '20', '', 'Main', 1, 23, 1),
(21, 1, 'A', '21', '', 'Main', 1, 24, 1),
(22, 1, 'A', '22', '', 'Main', 1, 25, 1),
(23, 1, 'A', '23', '', 'Main', 1, 26, 1),
(24, 1, 'A', '24', '', 'Main', 1, 27, 1),
(25, 1, 'A', '25', '', 'Main', 1, 28, 1),
(26, 1, 'A', '26', '', 'Main', 1, 29, 1),
(27, 1, 'A', '27', '', 'Main', 1, 30, 1),
(28, 1, 'A', '28', '', 'Main', 1, 31, 1),
(29, 1, 'A', '29', '', 'Main', 1, 32, 1),
(30, 1, 'A', '30', '', 'Main', 1, 33, 1),
(31, 1, 'B', '1', '', 'Main', 1, 2, 2),
(32, 1, 'B', '2', '', 'Main', 1, 3, 2),
(33, 1, 'B', '3', '', 'Main', 1, 4, 2),
(34, 1, 'B', '4', '', 'Main', 1, 5, 2),
(35, 1, 'B', '5', '', 'Main', 1, 6, 2),
(36, 1, 'B', '6', '', 'Main', 1, 7, 2),
(37, 1, 'B', '7', '', 'Main', 1, 8, 2),
(38, 1, 'B', '8', '', 'Main', 1, 9, 2),
(39, 1, 'B', '9', '', 'Main', 1, 10, 2),
(40, 1, 'B', '10', '', 'Main', 1, 11, 2),
(41, 1, 'B', '11', '', 'Main', 1, 12, 2),
(42, 1, 'B', '12', '', 'Main', 1, 13, 2),
(43, 1, 'B', '13', '', 'Main', 1, 14, 2),
(44, 1, 'B', '14', '', 'Main', 1, 15, 2),
(45, 1, 'B', '15', '', 'Main', 1, 16, 2),
(46, 1, 'B', '16', '', 'Main', 1, 19, 2),
(47, 1, 'B', '17', '', 'Main', 1, 20, 2),
(48, 1, 'B', '18', '', 'Main', 1, 21, 2),
(49, 1, 'B', '19', '', 'Main', 1, 22, 2),
(50, 1, 'B', '20', '', 'Main', 1, 23, 2),
(51, 1, 'B', '21', '', 'Main', 1, 24, 2),
(52, 1, 'B', '22', '', 'Main', 1, 25, 2),
(53, 1, 'B', '23', '', 'Main', 1, 26, 2),
(54, 1, 'B', '24', '', 'Main', 1, 27, 2),
(55, 1, 'B', '25', '', 'Main', 1, 28, 2),
(56, 1, 'B', '26', '', 'Main', 1, 29, 2),
(57, 1, 'B', '27', '', 'Main', 1, 30, 2),
(58, 1, 'B', '28', '', 'Main', 1, 31, 2),
(59, 1, 'B', '29', '', 'Main', 1, 32, 2),
(60, 1, 'B', '30', '', 'Main', 1, 33, 2),
(61, 1, 'C', '1', '', 'Main', 1, 2, 3),
(62, 1, 'C', '2', '', 'Main', 1, 3, 3),
(63, 1, 'C', '3', '', 'Main', 1, 4, 3),
(64, 1, 'C', '4', '', 'Main', 1, 5, 3),
(65, 1, 'C', '5', '', 'Main', 1, 6, 3),
(66, 1, 'C', '6', '', 'Main', 1, 7, 3),
(67, 1, 'C', '7', '', 'Main', 1, 8, 3),
(68, 1, 'C', '8', '', 'Main', 1, 9, 3),
(69, 1, 'C', '9', '', 'Main', 1, 10, 3),
(70, 1, 'C', '10', '', 'Main', 1, 11, 3),
(71, 1, 'C', '11', '', 'Main', 1, 12, 3),
(72, 1, 'C', '12', '', 'Main', 1, 13, 3),
(73, 1, 'C', '13', '', 'Main', 1, 14, 3),
(74, 1, 'C', '14', '', 'Main', 1, 15, 3),
(75, 1, 'C', '15', '', 'Main', 1, 16, 3),
(76, 1, 'C', '16', '', 'Main', 1, 19, 3),
(77, 1, 'C', '17', '', 'Main', 1, 20, 3),
(78, 1, 'C', '18', '', 'Main', 1, 21, 3),
(79, 1, 'C', '19', '', 'Main', 1, 22, 3),
(80, 1, 'C', '20', '', 'Main', 1, 23, 3),
(81, 1, 'C', '21', '', 'Main', 1, 24, 3),
(82, 1, 'C', '22', '', 'Main', 1, 25, 3),
(83, 1, 'C', '23', '', 'Main', 1, 26, 3),
(84, 1, 'C', '24', '', 'Main', 1, 27, 3),
(85, 1, 'C', '25', '', 'Main', 1, 28, 3),
(86, 1, 'C', '26', '', 'Main', 1, 29, 3),
(87, 1, 'C', '27', '', 'Main', 1, 30, 3),
(88, 1, 'C', '28', '', 'Main', 1, 31, 3),
(89, 1, 'C', '29', '', 'Main', 1, 32, 3),
(90, 1, 'C', '30', '', 'Main', 1, 33, 3),
(91, 1, 'D', '1', '', 'Main', 1, 2, 4),
(92, 1, 'D', '2', '', 'Main', 1, 3, 4),
(93, 1, 'D', '3', '', 'Main', 1, 4, 4),
(94, 1, 'D', '4', '', 'Main', 1, 5, 4),
(95, 1, 'D', '5', '', 'Main', 1, 6, 4),
(96, 1, 'D', '6', '', 'Main', 1, 7, 4),
(97, 1, 'D', '7', '', 'Main', 1, 8, 4),
(98, 1, 'D', '8', '', 'Main', 1, 9, 4),
(99, 1, 'D', '9', '', 'Main', 1, 10, 4),
(100, 1, 'D', '10', '', 'Main', 1, 11, 4),
(101, 1, 'D', '11', '', 'Main', 1, 12, 4),
(102, 1, 'D', '12', '', 'Main', 1, 13, 4),
(103, 1, 'D', '13', '', 'Main', 1, 14, 4),
(104, 1, 'D', '14', '', 'Main', 1, 15, 4),
(105, 1, 'D', '15', '', 'Main', 1, 16, 4),
(106, 1, 'D', '16', '', 'Main', 1, 19, 4),
(107, 1, 'D', '17', '', 'Main', 1, 20, 4),
(108, 1, 'D', '18', '', 'Main', 1, 21, 4),
(109, 1, 'D', '19', '', 'Main', 1, 22, 4),
(110, 1, 'D', '20', '', 'Main', 1, 23, 4),
(111, 1, 'D', '21', '', 'Main', 1, 24, 4),
(112, 1, 'D', '22', '', 'Main', 1, 25, 4),
(113, 1, 'D', '23', '', 'Main', 1, 26, 4),
(114, 1, 'D', '24', '', 'Main', 1, 27, 4),
(115, 1, 'D', '25', '', 'Main', 1, 28, 4),
(116, 1, 'D', '26', '', 'Main', 1, 29, 4),
(117, 1, 'D', '27', '', 'Main', 1, 30, 4),
(118, 1, 'D', '28', '', 'Main', 1, 31, 4),
(119, 1, 'D', '29', '', 'Main', 1, 32, 4),
(120, 1, 'D', '30', '', 'Main', 1, 33, 4),
(121, 1, 'E', '1', '', 'Main', 2, 1, 6),
(122, 1, 'E', '2', '', 'Main', 2, 2, 6),
(123, 1, 'E', '3', '', 'Main', 2, 3, 6),
(124, 1, 'E', '4', '', 'Main', 2, 4, 6),
(125, 1, 'E', '5', '', 'Main', 2, 5, 6),
(126, 1, 'E', '6', '', 'Main', 2, 6, 6),
(127, 1, 'E', '7', '', 'Main', 2, 7, 6),
(128, 1, 'E', '8', '', 'Main', 2, 8, 6),
(129, 1, 'E', '9', '', 'Main', 2, 9, 6),
(130, 1, 'E', '10', '', 'Main', 2, 10, 6),
(131, 1, 'E', '11', '', 'Main', 2, 11, 6),
(132, 1, 'E', '12', '', 'Main', 2, 12, 6),
(133, 1, 'E', '13', '', 'Main', 2, 13, 6),
(134, 1, 'E', '14', '', 'Main', 2, 14, 6),
(135, 1, 'E', '15', '', 'Main', 2, 15, 6),
(136, 1, 'E', '16', '', 'Main', 2, 16, 6),
(137, 1, 'E', '17', '', 'Main', 2, 19, 6),
(138, 1, 'E', '18', '', 'Main', 2, 20, 6),
(139, 1, 'E', '19', '', 'Main', 2, 21, 6),
(140, 1, 'E', '20', '', 'Main', 2, 22, 6),
(141, 1, 'E', '21', '', 'Main', 2, 23, 6),
(142, 1, 'E', '22', '', 'Main', 2, 24, 6),
(143, 1, 'E', '23', '', 'Main', 2, 25, 6),
(144, 1, 'E', '24', '', 'Main', 2, 26, 6),
(145, 1, 'E', '25', '', 'Main', 2, 27, 6),
(146, 1, 'E', '26', '', 'Main', 2, 28, 6),
(147, 1, 'E', '27', '', 'Main', 2, 29, 6),
(148, 1, 'E', '28', '', 'Main', 2, 30, 6),
(149, 1, 'E', '29', '', 'Main', 2, 31, 6),
(150, 1, 'E', '30', '', 'Main', 2, 32, 6),
(151, 1, 'E', '31', '', 'Main', 2, 33, 6),
(152, 1, 'E', '32', '', 'Main', 2, 34, 6),
(153, 1, 'F', '1', '', 'Main', 2, 1, 7),
(154, 1, 'F', '2', '', 'Main', 2, 2, 7),
(155, 1, 'F', '3', '', 'Main', 2, 3, 7),
(156, 1, 'F', '4', '', 'Main', 2, 4, 7),
(157, 1, 'F', '5', '', 'Main', 2, 5, 7),
(158, 1, 'F', '6', '', 'Main', 2, 6, 7),
(159, 1, 'F', '7', '', 'Main', 2, 7, 7),
(160, 1, 'F', '8', '', 'Main', 2, 8, 7),
(161, 1, 'F', '9', '', 'Main', 2, 9, 7),
(162, 1, 'F', '10', '', 'Main', 2, 10, 7),
(163, 1, 'F', '11', '', 'Main', 2, 11, 7),
(164, 1, 'F', '12', '', 'Main', 2, 12, 7),
(165, 1, 'F', '13', '', 'Main', 2, 13, 7),
(166, 1, 'F', '14', '', 'Main', 2, 14, 7),
(167, 1, 'F', '15', '', 'Main', 2, 15, 7),
(168, 1, 'F', '16', '', 'Main', 2, 16, 7),
(169, 1, 'F', '17', '', 'Main', 2, 19, 7),
(170, 1, 'F', '18', '', 'Main', 2, 20, 7),
(171, 1, 'F', '19', '', 'Main', 2, 21, 7),
(172, 1, 'F', '20', '', 'Main', 2, 22, 7),
(173, 1, 'F', '21', '', 'Main', 2, 23, 7),
(174, 1, 'F', '22', '', 'Main', 2, 24, 7),
(175, 1, 'F', '23', '', 'Main', 2, 25, 7),
(176, 1, 'F', '24', '', 'Main', 2, 26, 7),
(177, 1, 'F', '25', '', 'Main', 2, 27, 7),
(178, 1, 'F', '26', '', 'Main', 2, 28, 7),
(179, 1, 'F', '27', '', 'Main', 2, 29, 7),
(180, 1, 'F', '28', '', 'Main', 2, 30, 7),
(181, 1, 'F', '29', '', 'Main', 2, 31, 7),
(182, 1, 'F', '30', '', 'Main', 2, 32, 7),
(183, 1, 'F', '31', '', 'Main', 2, 33, 7),
(184, 1, 'F', '32', '', 'Main', 2, 34, 7),
(185, 1, 'G', '1', '', 'Main', 2, 1, 8),
(186, 1, 'G', '2', '', 'Main', 2, 2, 8),
(187, 1, 'G', '3', '', 'Main', 2, 3, 8),
(188, 1, 'G', '4', '', 'Main', 2, 4, 8),
(189, 1, 'G', '5', '', 'Main', 2, 5, 8),
(190, 1, 'G', '6', '', 'Main', 2, 6, 8),
(191, 1, 'G', '7', '', 'Main', 2, 7, 8),
(192, 1, 'G', '8', '', 'Main', 2, 8, 8),
(193, 1, 'G', '9', '', 'Main', 2, 9, 8),
(194, 1, 'G', '10', '', 'Main', 2, 10, 8),
(195, 1, 'G', '11', '', 'Main', 2, 11, 8),
(196, 1, 'G', '12', '', 'Main', 2, 12, 8),
(197, 1, 'G', '13', '', 'Main', 2, 13, 8),
(198, 1, 'G', '14', '', 'Main', 2, 14, 8),
(199, 1, 'G', '15', '', 'Main', 2, 15, 8),
(200, 1, 'G', '16', '', 'Main', 2, 16, 8),
(201, 1, 'G', '17', '', 'Main', 2, 19, 8),
(202, 1, 'G', '18', '', 'Main', 2, 20, 8),
(203, 1, 'G', '19', '', 'Main', 2, 21, 8),
(204, 1, 'G', '20', '', 'Main', 2, 22, 8),
(205, 1, 'G', '21', '', 'Main', 2, 23, 8),
(206, 1, 'G', '22', '', 'Main', 2, 24, 8),
(207, 1, 'G', '23', '', 'Main', 2, 25, 8),
(208, 1, 'G', '24', '', 'Main', 2, 26, 8),
(209, 1, 'G', '25', '', 'Main', 2, 27, 8),
(210, 1, 'G', '26', '', 'Main', 2, 28, 8),
(211, 1, 'G', '27', '', 'Main', 2, 29, 8),
(212, 1, 'G', '28', '', 'Main', 2, 30, 8),
(213, 1, 'G', '29', '', 'Main', 2, 31, 8),
(214, 1, 'G', '30', '', 'Main', 2, 32, 8),
(215, 1, 'G', '31', '', 'Main', 2, 33, 8),
(216, 1, 'G', '32', '', 'Main', 2, 34, 8),
(217, 1, 'H', '1', '', 'Main', 2, 1, 9),
(218, 1, 'H', '2', '', 'Main', 2, 2, 9),
(219, 1, 'H', '3', '', 'Main', 2, 3, 9),
(220, 1, 'H', '4', '', 'Main', 2, 4, 9),
(221, 1, 'H', '5', '', 'Main', 2, 5, 9),
(222, 1, 'H', '6', '', 'Main', 2, 6, 9),
(223, 1, 'H', '7', '', 'Main', 2, 7, 9),
(224, 1, 'H', '8', '', 'Main', 2, 8, 9),
(225, 1, 'H', '9', '', 'Main', 2, 9, 9),
(226, 1, 'H', '10', '', 'Main', 2, 10, 9),
(227, 1, 'H', '11', '', 'Main', 2, 11, 9),
(228, 1, 'H', '12', '', 'Main', 2, 12, 9),
(229, 1, 'H', '13', '', 'Main', 2, 13, 9),
(230, 1, 'H', '14', '', 'Main', 2, 14, 9),
(231, 1, 'H', '15', '', 'Main', 2, 15, 9),
(232, 1, 'H', '16', '', 'Main', 2, 16, 9),
(233, 1, 'H', '17', '', 'Main', 2, 19, 9),
(234, 1, 'H', '18', '', 'Main', 2, 20, 9),
(235, 1, 'H', '19', '', 'Main', 2, 21, 9),
(236, 1, 'H', '20', '', 'Main', 2, 22, 9),
(237, 1, 'H', '21', '', 'Main', 2, 23, 9),
(238, 1, 'H', '22', '', 'Main', 2, 24, 9),
(239, 1, 'H', '23', '', 'Main', 2, 25, 9),
(240, 1, 'H', '24', '', 'Main', 2, 26, 9),
(241, 1, 'H', '25', '', 'Main', 2, 27, 9),
(242, 1, 'H', '26', '', 'Main', 2, 28, 9),
(243, 1, 'H', '27', '', 'Main', 2, 29, 9),
(244, 1, 'H', '28', '', 'Main', 2, 30, 9),
(245, 1, 'H', '29', '', 'Main', 2, 31, 9),
(246, 1, 'H', '30', '', 'Main', 2, 32, 9),
(247, 1, 'H', '31', '', 'Main', 2, 33, 9),
(248, 1, 'H', '32', '', 'Main', 2, 34, 9),
(249, 1, 'I', '1', '', 'Main', 2, 1, 10),
(250, 1, 'I', '2', '', 'Main', 2, 2, 10),
(251, 1, 'I', '3', '', 'Main', 2, 3, 10),
(252, 1, 'I', '4', '', 'Main', 2, 4, 10),
(253, 1, 'I', '5', '', 'Main', 2, 5, 10),
(254, 1, 'I', '6', '', 'Main', 2, 6, 10),
(255, 1, 'I', '7', '', 'Main', 2, 7, 10),
(256, 1, 'I', '8', '', 'Main', 2, 8, 10),
(257, 1, 'I', '9', '', 'Main', 2, 9, 10),
(258, 1, 'I', '10', '', 'Main', 2, 10, 10),
(259, 1, 'I', '11', '', 'Main', 2, 11, 10),
(260, 1, 'I', '12', '', 'Main', 2, 12, 10),
(261, 1, 'I', '13', '', 'Main', 2, 13, 10),
(262, 1, 'I', '14', '', 'Main', 2, 14, 10),
(263, 1, 'I', '15', '', 'Main', 2, 15, 10),
(264, 1, 'I', '16', '', 'Main', 2, 16, 10),
(265, 1, 'I', '17', '', 'Main', 2, 19, 10),
(266, 1, 'I', '18', '', 'Main', 2, 20, 10),
(267, 1, 'I', '19', '', 'Main', 2, 21, 10),
(268, 1, 'I', '20', '', 'Main', 2, 22, 10),
(269, 1, 'I', '21', '', 'Main', 2, 23, 10),
(270, 1, 'I', '22', '', 'Main', 2, 24, 10),
(271, 1, 'I', '23', '', 'Main', 2, 25, 10),
(272, 1, 'I', '24', '', 'Main', 2, 26, 10),
(273, 1, 'I', '25', '', 'Main', 2, 27, 10),
(274, 1, 'I', '26', '', 'Main', 2, 28, 10),
(275, 1, 'I', '27', '', 'Main', 2, 29, 10),
(276, 1, 'I', '28', '', 'Main', 2, 30, 10),
(277, 1, 'I', '29', '', 'Main', 2, 31, 10),
(278, 1, 'I', '30', '', 'Main', 2, 32, 10),
(279, 1, 'I', '31', '', 'Main', 2, 33, 10),
(280, 1, 'I', '32', '', 'Main', 2, 34, 10),
(281, 1, 'J', '1', '', 'Main', 2, 1, 11),
(282, 1, 'J', '2', '', 'Main', 2, 2, 11),
(283, 1, 'J', '3', '', 'Main', 2, 3, 11),
(284, 1, 'J', '4', '', 'Main', 2, 4, 11),
(285, 1, 'J', '5', '', 'Main', 2, 5, 11),
(286, 1, 'J', '6', '', 'Main', 2, 6, 11),
(287, 1, 'J', '7', '', 'Main', 2, 7, 11),
(288, 1, 'J', '8', '', 'Main', 2, 8, 11),
(289, 1, 'J', '9', '', 'Main', 2, 9, 11),
(290, 1, 'J', '10', '', 'Main', 2, 10, 11),
(291, 1, 'J', '11', '', 'Main', 2, 11, 11),
(292, 1, 'J', '12', '', 'Main', 2, 12, 11),
(293, 1, 'J', '13', '', 'Main', 2, 13, 11),
(294, 1, 'J', '14', '', 'Main', 2, 14, 11),
(295, 1, 'J', '15', '', 'Main', 2, 15, 11),
(296, 1, 'J', '16', '', 'Main', 2, 16, 11),
(297, 1, 'J', '17', '', 'Main', 2, 19, 11),
(298, 1, 'J', '18', '', 'Main', 2, 20, 11),
(299, 1, 'J', '19', '', 'Main', 2, 21, 11),
(300, 1, 'J', '20', '', 'Main', 2, 22, 11),
(301, 1, 'J', '21', '', 'Main', 2, 23, 11),
(302, 1, 'J', '22', '', 'Main', 2, 24, 11),
(303, 1, 'J', '23', '', 'Main', 2, 25, 11),
(304, 1, 'J', '24', '', 'Main', 2, 26, 11),
(305, 1, 'J', '25', '', 'Main', 2, 27, 11),
(306, 1, 'J', '26', '', 'Main', 2, 28, 11),
(307, 1, 'J', '27', '', 'Main', 2, 29, 11),
(308, 1, 'J', '28', '', 'Main', 2, 30, 11),
(309, 1, 'J', '29', '', 'Main', 2, 31, 11),
(310, 1, 'J', '30', '', 'Main', 2, 32, 11),
(311, 1, 'J', '31', '', 'Main', 2, 33, 11),
(312, 1, 'J', '32', '', 'Main', 2, 34, 11);");
	$wpdb->query("INSERT INTO freeseat_shows (id, spectacle, theatre, date, time, disabled, civicrm_id) VALUES
(1, 1, 1, '$showdate1', '19:30:00', 0, NULL),
(2, 1, 1, '$showdate2', '19:30:00', 0, NULL);");
	$wpdb->query("INSERT INTO freeseat_spectacles (id, name, imagesrc, description, castpw) VALUES
(1, 'The Tempest', 'thetempest2.jpg', 'This classic Shakespearean play is set on a remote island, where Prospero, the rightful Duke of Milan, plots to restore his daughter Miranda to her rightful place using illusion and skillful manipulation. He conjures up a storm, to lure his usurping brother Antonio and the complicit King Alonso of Naples to the island. There, his machinations bring about the revelation of Antonio\'s lowly nature, the redemption of the King, and the marriage of Miranda to Alonso\'s son, Ferdinand.', '');");
	$wpdb->query("INSERT INTO freeseat_theatres (id, name, imagesrc, staggered_seating) VALUES
	(1, 'Two reserved classes 4x30&6x32, lettered rows', NULL, 0);");
}

/**
 *  Placeholder for plugin deactivation routine.
 */
function freeseat_deactivate()
{
	sys_log( "Freeseat deactivated" );
}



