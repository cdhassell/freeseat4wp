<?php



/*
 *  Fetches all booking data from the database.
 */
function freeseat_csvdump() {	
	$list = get_bookings("");
	header("Content-Type: text/x-csv");
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
	} elseif (isset($params['showid'])) {
		$show = get_show($params['showid']);
		$id = $show['spectacleid'];
	}
	// if no spectacle is specified, get them all
	$spec = (isset($id) ? "and shows.spectacle=$id" : "");
	$sql = "SELECT DISTINCT firstname, lastname, email, phone, address, city, us_state, postalcode FROM booking,shows WHERE booking.showid=shows.id $spec ORDER BY lastname, firstname";
	$list = fetch_all(freeseat_query($sql));
	header("Content-Type: text/x-csv");
	header("Content-Type: application/download");
	header('Content-Disposition: attachment; filename=names.csv');
	freeseat_csv_output( $list );
}

/* 
 *  Prints the data in $list in the form of a CSV file
 */
function freeseat_csv_output($list) {
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

db_connect();
ensure_plugin('csvdump');
if (!admin_mode()) {
	wp_die( $lang["access_denied"] );
}

if ( isset( $_REQUEST['file'] ) ) {
	$type = $_REQUEST['file'];
	switch( $file ) {
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

	