<?php

/* 	Implements a process to upload a new theatre seatmap to FreeSeat in the form of a CSV file, such as from 
a spreadsheet program.  Cells in the spreadsheet represent theatre seats, arranged in rows. 
	
Spreadsheet Format:
The first column should contain a command name as follows.
"Row" entries should contain a row of seats with a class number in each cell, or a blank cell for empty spots 
	like aisles. For example, this creates a simple row of ten class one seats:
		Row,1,1,1,1,1,1,1,1,1,1
	The same row with a blank space for an aisle between seats 5 and 6 looks like:
		Row,1,1,1,1,1,,1,1,1,1,1
	By default, the Row and Seat labels will be numeric, starting from the top left of the seatmap.
	To specify the row and column labels, row entries can optionally be entered with seat codes in the form C-R-L, 
	where C=class, R=row, and L=column.  Row and Column entries can be alpha or numeric.
	For example, this creates row A with ten class two seats with an aisle between the seats 5 and 6.
		Row,2-A-1,2-A-2,2-A-3,2-A-4,2-A-5,,2-A-6,2-A-7,2-A-8,2-A-9,2-A-10
"General" entries are followed by a number of general admission seats, and then a class number in the next cell.
	No layout is required for general admission seats. Here is an example creating 50 class 2 general admission seats:
		General,50,2
"Theatre" entries specify a theatre ID number, if you are adding seats to an existing theatre (optional). See the
	Caution information below.
"Zone" entries specify a name for the zone you are entering (optional, the default is "Main"). All Rows following a 
	Zone command will be placed in that zone, until a new Zone command is found.
"Extra" entries specify a label to place in the Extra column (optional, the default is blank).  All Rows following 
	the Extra command will use that Extra label, until a new Extra command is found.
"Note" entries are for documentation, and will be ignored.
Blank rows will also be ignored.
Save this file as a Comma Separated Values or CSV file.
	
CAUTION: Care should be taken when changing an existing seatmap. Existing seats in a map cannot be deleted by this
process.  A new seat that matches an existing seat will be used to update the existing seat record.  If a match is 
not found, a new seat will be added.  Check for duplicates after updating a seatmap!  
	
If you need to delete seat records from a map, you will have to do it by hand, very carefully.  If tickets exist 
in any show for those seats, the program will report an error.  The preferred method is to create a new map without 
those seats.

*/
function freeseat_upload() {
	global $lang, $name, $staggered;

	if (!admin_mode()) {
		wp_die( $lang[ "access_denied" ] );
	}

	$view_map = 0;	
	// $upload_map = '';
	if (isset($_SESSION['list'])) $list = $_SESSION['list'];
	if (isset($_SESSION['map'])) $upload_map = $_SESSION['map'];
	
	if (isset($_POST['theatre'])) {
		// we are re-entering with a previously saved map to display
		$view_map = nogpc($_POST['theatre']);
		if ( !empty( $view_map ) ) {
			$list = read_theatre($view_map);
			unset( $upload_map );
			unset( $_SESSION[ 'list' ] );
			unset( $_SESSION[ 'map'  ] );
		}
	} elseif (isset($_FILES['uploadedfile'])) {
		// we are re-entering with an uploaded file 
		$upload_map = get_upload_csv( 'uploadedfile' );
		if ($upload_map) {
			$list = read_csv( $upload_map );
			$_SESSION['list'] = $list;
			$_SESSION['map'] = $upload_map;
		} else {
			kaboom("No file found");
		}
	} elseif (isset($_POST['name'])) {
		// we are re-entering with a file ready to save to database
		$staggered = isset($_POST['staggered']);
		$name = substr($_POST['name'], 0, 30);
		if (save_theatre($list, $name, $staggered)) {
			echo '<p class="emph">Seat map was successfully saved.</p>';
		} else {
			echo '<p class="emph">Operation failed: please check the file format.</p>';
		}
		// clear the values so we dont do it again
		$list = array();
		unset($_SESSION['list']);
		$upload_map = '';
	} else {
		// if all else fails load the first seatmap from the database
		$view_map = m_eval( "select id from theatres order by id desc limit 1" );
		$list = read_theatre($view_map);
	}
	show_head(true);
	echo '<h2>Theatre Seating Maintenance</h2>';
	// Form for viewing a seat map
	echo "<h3>View a seat map</h3>";
	$url = admin_url( 'admin.php?page=freeseat-upload' );
	echo "<form action='$url' name='view_map' method='POST'>";
	echo '<p class="main">';
	enhanced_list_box(array( 'table' => 'theatres', 'id_field' => 'id', 
		'value_field' => 'name', 'highlight_id' => $view_map), '', '', "theatre" );
	echo ' <input type="submit" value="'.$lang["book"].'">';
	echo '</p></form>';
	if ($view_map && isset($list)) display_theatre($list, FALSE);
	print '<br>';
	
	// Form for uploading a new seat map CSV file
	echo '<h3>Upload a new seat map</h3>';
	echo '<h3><p style="font-weight:bold;" >Use with caution! Please see the README file for instructions.</p></h3>';
	echo "<form action='$url' enctype='multipart/form-data' name='upload_map' method='POST'>";
	echo '<p class="main">';
	echo '<input type="hidden" name="MAX_FILE_SIZE" value="100000">';
	echo '<input name="uploadedfile" type="file">';  //name in FILES array
	echo '&nbsp;<input type="submit" value="'.$lang["book"].'">';
	echo '</p></form>';
	if ( !empty($upload_map) && isset($list)) {
		display_theatre($list, TRUE);
		// Form for entering name and confirmation
		echo "<form action='$url' name='save_map' method='POST'>";
		echo '<p class="main">Enter a short name for this seat map: <input name="name" width=15></p>';
		echo '<p class="main">Should the rows be staggered? <input type="checkbox" name="staggered"></p>';
		echo '<p class="emph">Save this seat map to the database?<br>';
		echo 'Warning: This action cannot be undone!</p>';
		echo '<p class="main"><input type="submit" value="'.$lang["save"].'"></p>';
		echo '</form>';
	}
	show_foot(); 
}

// Displays the selected theatre seatmap

function display_theatre( $list, $checkdup ) {
	global $name;
	if (isset($name)) {
		print "$name<br>";
	}
	if ($checkdup) {
		$listthe = $list[0]['theatre'];
		if ($listthe) {
			$sql = "SELECT COUNT(*) from theatres where id=$listthe";
			if ( m_eval( $sql ) ) {  // its an existing theatre id
				print "<p class='emph'>Theatre ID number $listthe already exists! These seats will be added to the current map.</p>";
			}
		}
	}
	$map = array();
	$gen_template = array( 1=>0, 2=>0, 3=>0, 4=>0 );
	$gen = array();
	$maxx = array();
	$maxy = array();
	$zone = "";
	$zones = array();
	foreach ($list as $item) {
		if ($zone !== $item['zone']) {
			$zone = $item['zone'];
			$zones[] = $zone;
			$gen[$zone] = $gen_template;
			$maxx[$zone] = 0;
			$maxy[$zone] = 0;
		}
		if ($item['row'] == -1) {
			$gen[$zone][$item['class']]++;
		} else {
			$map[$zone][$item['y']][$item['x']] = $item;
			if (isset($item['row'])) {
				$map[$zone][$item['y']]['row'] = $item['row'];
			}
			$maxx[$zone] = max($item['x'], $maxx[$zone]);
			$maxy[$zone] = max($item['y'], $maxy[$zone]); 
		}
	}
	foreach ($zones as $z) {
		print "<p>Zone '$z'</p>";
		print "<table class='seatmap' style='border-collapse: collapse; ' >";
		for($y = 1; $y<=$maxy[$z]; $y++) {
			if (isset($map[$z][$y]['row'])) {
				$row = $map[$z][$y]['row'];
				print "<tr><td>Row $row</td>";
				for($x = 1; $x<=$maxx[$z]; $x++) {
					if (isset($map[$z][$y][$x])) {
						$c = $map[$z][$y][$x]['class'];
						$col = $map[$z][$y][$x]['col'];
						$col = ( $col<10 ? "&nbsp;$col" : "$col" );
						print "<td class='cls$c' style='border: 1px solid gray;'>$col</td>";
					} else {
						print "<td>&nbsp;&nbsp;&nbsp;</td>";
					}
				}
				print "</tr>";
			}
		}
		print "</table>";
		print "<br><table class='summary'>";  
		foreach ($gen[$z] as $class => $count) {
			if ($count) {
				print "<tr><td class='seat$class'>General admission class $class: $count seats </td></tr>";
			}
		}
		print "</table>";
	}
}

// Saves a theatre seatmap to the database

function save_theatre( $list, $name, $staggered = 0 ) {
	$listthe = $list[0]['theatre'];
	if (strlen($name) == 0) {
		if ($listthe) {
			$name = "Theatre " . $listthe;
		} else {
			return kaboom("Cannot create the theatre without a name");
		}
	}
	if ($listthe) {
		$sql = "SELECT COUNT(*) from theatres where id=$listthe";
		if ( freeseat_get_var( $sql ) ) {  // its an existing theatre id
			$sql = "UPDATE theatres set name='%s', staggered_seating='%d' where id='%d'";
			freeseat_query( $sql, array( $name, $staggered, $listthe ) );
			$theatre = $listthe;
		}
	}
	if (!isset($theatre)) {
		$sql = "INSERT into theatres (name, staggered_seating, imagesrc) values ('%s','%d',NULL)";
		freeseat_query( $sql, array( $name, $staggered ) );
		$theatre = freeseat_insert_id();
	}
	if (!$theatre) {
		return kaboom("Cannot create the theatre");
	} else {
		$sql = "INSERT into seats (theatre,row,col,extra,zone,class,x,y) " .
			"values ('%d','%s','%s','%s','%s','%d','%d','%d')";
		foreach( $list as $seat ) {
			freeseat_query( $sql, array( $theatre, $seat['row'], $seat['col'],
			$seat['extra'], $seat['zone'], $seat['class'], $seat['x'], $seat['y'] ) );
		}
		return TRUE;
	}
}

// Reads an existing theatre seatmap and returns it in $list

function read_theatre( $id ) {
	global $name, $staggered;
	$sql = "SELECT name, staggered_seating from theatres WHERE id=%d";
	$theatre = fetch_all( sprintf( $sql, $id ) );
	$name = $theatre[0]['name'];
	$staggered = $theatre[0]['staggered_seating'];
	$sql = "SELECT theatre,row,col,extra,zone,class,x,y from seats WHERE theatre=%d";
	$list = fetch_all( sprintf( $sql, $id ) );
	return $list;
}

// Reads a CSV file and returns the filename

function get_upload_csv( $name ) {
	global $upload_path, $lang;
	// handle uploaded file
	$permitted = array("csv","txt");
	if (!isset($_FILES[$name])) {
		// nothing to do
		return "";
	}
	$file_array = $_FILES[$name];
	if ($file_array['name'] == "") {
		// user didn't submit a file
		return "";
	}
	$parts = pathinfo($file_array['name']);
	$target = $parts["basename"];
	if ( !is_uploaded_file( $file_array['tmp_name'] )
		|| !isset($parts["extension"])
		|| !in_array(strtolower($parts["extension"]),$permitted)) {
		kaboom( $lang['err_filetype'] . "CSV" );
		return "";
	}
	$path = plugin_dir_path( __FILE__ ) . $upload_path . $target;
	if ( !move_uploaded_file( $file_array['tmp_name'], $path ) ) {
		kaboom( $lang['err_upload'] ) ;
		return "";
	}
	// got one, let's go with it
	return $path;
}

// Parses the uploaded CSV file and returns the contents in $list array

function read_csv( $filename ) {
	// open a CSV file and process it
	$class = $theatre = $y = $row = 0;
	$note = $extra = "";
	$zone = "Main";
	$handle = fopen($filename, 'r');
	if (!$handle) {
		kaboom("File error, cannot proceed");
		exit;
	}
	$list = array();
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$num = count($data);
		if ($num) {
			$cell = array_shift($data);
			if (stripos( $cell, "Note" ) !== FALSE) {
				// a documentation note, ignore it
				continue;
			} elseif (stripos( $cell, "Zone" ) !== FALSE) {
				// a zone label
				$nextzone = trim(array_shift($data));
				if ($zone != $nextzone) {
					$zone = $nextzone;
					$y = $row = 0;
				}
				continue;
			} elseif (stripos( $cell, "Theat" ) !== FALSE) {
				// set the theatre id if we know it
				$theatre = (int)array_shift($data);
				continue;
			} elseif (stripos( $cell, "Extra" ) !== FALSE) {
				// set extra description 
				$extra = trim(array_shift($data));
				continue;
			} elseif (stripos( $cell, "General" ) !== FALSE) {
				// create general admission seats
				$genx = (int)array_shift($data);
				$class = max( array_shift($data), 1 );
				for ($i = 0; $i < $genx; $i++) {
					$list[] = array('x' => 0, 'y' => 0, 'class' => $class, 'row' => -1, 'col' => 1,
						'zone' => $zone, 'extra' => $extra, 'theatre' => $theatre);
				}
				continue;
			} elseif (stripos( $cell, "Row" ) !== FALSE) {
				// create a row of reserved seats
				$y++;
				$empty = TRUE;
				foreach($data as $item) {
					if (!empty($item)) $empty = FALSE;
				}
				if ($empty) continue;
				$row++;
				$x = 0;
				$col = 0;
				foreach( $data as $cell) {
					$x++;
					if (strpos($cell, '-' ) !== FALSE) {
						// its a seat code so we are specifying the labels
						// assumed format class-row-col
						list($class, $alpharow, $alphacol ) = explode('-', $cell);
						if ($class) {
							$list[] = array('x' => $x, 'y' => $y, 'class' => $class, 'row' => $alpharow, 							'col' => $alphacol,
							'zone' => $zone, 'extra' => $extra, 'theatre' => $theatre);
						}
					} elseif (intval( $cell )) {
						// its a simple class number so calculate the labels
						$col++;
						$list[] = array('x' => $x, 'y' => $y, 'class' => $cell, 'row' => $row, 'col' => $col,
							'zone' => $zone, 'extra' => $extra, 'theatre' => $theatre);
					} 
				}
			}
		}
	}
	fclose($handle);
	// print_r( $list );
	return( $list );
}


