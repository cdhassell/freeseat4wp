<?php

// import wordpress stuff
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );

// import freeseat stuff
define ('FS_PATH','../../');

require_once (FS_PATH . "vars.php");
require_once (FS_PATH . "functions/plugins.php");
require_once (FS_PATH . "functions/booking.php");
require_once (FS_PATH . "functions/session.php");
require_once (FS_PATH . "functions/tools.php");
require_once (FS_PATH . "functions/format.php");
require_once (FS_PATH . "functions/mysql.php");

// import code to access freeseat options
require_once( FS_PATH . "options.php" );
// Get the global variables from the database
$freeseat_vars = get_config();
if ( is_array($freeseat_vars) ) {
	foreach ( $freeseat_vars as $var => $value ) {
		if (false === strpos($var,'chk_')) {
			$$var = $value;
		}	
	}
}

$messages = array();

/*
 *  Fetches all booking data from the database.
 */
function freeseat_csvdump() {	
	$list = get_bookings("");
	freeseat_csv_output( $list );
}

/*
 *  Fetches only unique names and address records, ignoring booking details.
 */
function freeseat_csvnames() {	
	if (isset($_SESSION['showid'])) {
		$show = get_show($_SESSION['showid']);
		$id = $show['spectacleid'];
	} elseif (isset($_SESSION['spectacleid'])) {
		$id = $_SESSION['spectacleid'];
	}
	// if no spectacle is specified, get them all
	$spec = (isset($id) ? "and shows.spectacle=$id" : "");
	$sql = "SELECT DISTINCT firstname, lastname, email, phone, address, city, us_state, postalcode FROM booking,shows WHERE booking.showid=shows.id $spec ORDER BY lastname, firstname";
	$list = fetch_all($sql);
	freeseat_csv_output( $list );
}

/* 
 *  Prints the data in $list in the form of a CSV file
 */
function freeseat_csv_output($list) {
	header("Content-Type: text/x-csv");
	header("Content-Type: application/download");
	header('Content-Disposition: attachment; filename=data.csv');
	$first = true;
	foreach ($list as $n => $l) {
		if ($first) {
			$sep = '';
			foreach ($l as $k => $v) {
				echo "$sep\"$k\"";
				$sep = ',';
			}
			echo "\n";
			$first = false;
		}
		$sep = '';
		foreach ($l as $k => $v) {
			echo "$sep\"$v\"";
			$sep = ',';
		}
		echo "\n";
	}
}

ensure_plugin('csvdump');
if (!admin_mode()) {
	fatal_error( $lang["access_denied"] );
}

if ( isset( $_REQUEST['file'] ) ) {
	switch( $_REQUEST['file'] ) {
		case 'all':
			freeseat_csvdump();
			break;
		case 'names':
			freeseat_csvnames();
			break;
		default:
			break;	
	}
}

	
