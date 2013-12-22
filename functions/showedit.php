<?php

/*
 * Helper functions needed for editing show data
 */


/** if an image file was uploaded, this will handle the file */
function get_upload( &$perf ) {
	global $upload_path, $lang, $FS_PATH;

	$permitted = array("jpeg","jpg" /*,"gif","png","bmp"*/);
	foreach( $_FILES as $file_name => $file_array ) {
		if ($file_array['name'] != "") {
			// do nothing if user didn't submit a file
			$parts = pathinfo($file_array['name']);
			$target = $parts["basename"];
			if ( is_uploaded_file( $file_array['tmp_name'] )
				//&& strstr( $file_array['type'], "image" )
				&& isset($parts["extension"])
				&& in_array(strtolower($parts["extension"]),$permitted)) {
				if ( !move_uploaded_file( $file_array['tmp_name'], $FS_PATH . $upload_path . $target ) ) {
					kaboom( $lang['err_upload'] ) ;
					/* (keep old value if upload failed) */
				} else {
					$perf['imagesrc'] = $target;
				}
			} else  {
				kaboom( $lang['err_filetype'] . "image" );
				$perf['imagesrc'] = "";
			}
		}
	}
}

/* displays a combo box allowing to choose a theatre for the given
   performance. Pass currently selected value as second parameter */
function choose_seatmap( $perf, $theatre=null )
{
	global $lang;
	
	enhanced_list_box(array( 'table' => 'theatres', 'id_field' => 'id', 
		'value_field' => 'name', 'highlight_id' => $theatre), '', '', "theatre_$perf" );
	return "";
}

function choose_local_file($spec)
/* opens a file dialog to upload a file to the server
- Maximum allowable file size is curently 100K */
{
	global $lang;
	
	echo '<input type="hidden" name="MAX_FILE_SIZE" value="100000">';
	echo '<input name="uploadedfile" type="file"><br>';
}

function choose_spectacle( $new, $spec )
// displays a list box of available spectacles in the database to choose from
{
	global $lang;
	
	enhanced_list_box(array( 'table' => 'spectacles', 'id_field' => 'id', 
		'value_field' => 'name', 'highlight_id' => $spec), 'onchange="choose_spec.submit();"',
		($new ? $lang['create_show'] : '' ), 'id' );
	return ''; 
}

function enhanced_list_box($options, $params, $text_new, $resultname) {
// From http://www.cgi-interactive-uk.com/populate_combo_box_function_php.html
// creates a list box from data in a mysql field
	$sql  = "select " . $options['id_field'];
	$sql .= ", " . $options['value_field'];
	$sql .= " from " . $options['table'];
	/* append any where criteria to the sql */
	if(isset($options['where_statement'])) {
    $sql .= " where " . $options['where_statement'] ;
	}  
	/* set the sort order of the list */
	$sql .= " order by " . $options['value_field'];
	$result = fetch_all_n($sql);
	if (!$result) {
		kaboom(mysql_error()); 
		return;
	}
	echo '<select name="', $resultname, '" ', $params, ' size="1">';
	foreach ( $result as $row ) {
		if($row[0] == $options['highlight_id']) {
			echo '<option value="', $row[0], '" SELECTED>', $row[1], '</option>';
		} else {
			echo '<option value="', $row[0], '">', $row[1], '</option>';
		}
	}
	if ($text_new)  {
		echo '<option value="0" ' . (($options['highlight_id']==0)?'SELECTED':'') . '>' . 
			$text_new . '</option>';
	}
	echo '</select>';
}

function get_tname( $theatre ) {
	if (!$theatre) return null;
	return m_eval("select name from theatres where id=$theatre");
}

/** How many seats have been booked or are in the process of being
    booked for the given show. */
function count_bookings( $showid ) {
  global $now;
  $tot = m_eval( "select count(booking.id) from booking where" . 
		 " showid=$showid" .
		 " and (state=" . ST_LOCKED . " or state=" . ST_BOOKED . 
		 " or state=" . ST_SHAKEN . " or state=" . ST_PAID . ")" );
    return $tot;
}

/** print a form input setting $name to $value. If global $ready is
true then print it in a non-editable form.

$name: the form input name

$value: the default value

$headername: if set, show that as a title over the field

**/
function print_var( $name, $value, $ready=false, $headername=null, $width=12 ) {
	global $lang;

	if ($headername) echo '<h3>' . $headername . '</h3>';
	if ($name=="description" && !$ready) { // i am so sorry
		echo '<textarea name="'.$name.'" border=1 rows=18 cols=32>' . $value; 
		echo '</textarea>';
	} else {
		$escvalue = htmlspecialchars($value);
		echo '<input '.($ready?'type="hidden" ':'').' name="'.$name.'" value="'.$escvalue.'">';
	}
	if ($ready) {
		/* Note that we *don't* escape $value. The point is to let
			the admin enter HTML formatting in there if needed */
		echo '<p class="main">' . $value . '</p>';
	}
}

/* show storing functions */

function set_dates( $spec, $dates )
// set dates/times from $dates into shows table
{
	foreach ( $dates as $id => $val ) {   
		$d = $val[ 'date' ];
		$t = $val[ 'time' ];
		$th = $val[ 'theatre' ];
		if ( !isset( $val['id' ] ) ) { // INSERT
			if ( $d == "0000-00-00" ) continue;
			$query = "INSERT into shows (spectacle, theatre, date, time) values (%d, %d, %s, %s )";
			$values = array( $spec, $th, $d, $t );
		} else {
			$i = (int)$val['id'];
			if ($d == "0000-00-00" ) continue; // should maybe do a DELETE instead?
			// $q ="UPDATE shows set time=$t, date=$d, theatre=$th where id=$i";
			$query = "UPDATE shows set time=%s, date=%s, theatre=%d where id=%d ";
			$values = array( $t, $d, $th, $i );
		}
		if ( false === freeseat_query( $query, $values ) ) {
			return myboom( "Problem when creating or updating show information." );   
		} 
	}
	return true;
}

function set_perf( $perf ) 
// set spectacle description info into tables, returning the id of the
// spectacle - unchanged if it was an existing one, bigger than zero
// if this is a new spectacle.
{
	$spec = ( isset( $perf[ "id" ] ) ? (int) $perf[ "id" ] : 0 );
	if ( $spec > 0 ) {
		/* $q = "UPDATE spectacles set name=".quoter($perf["name"])
		    .", description=".quoter($perf["description"])
		    .", imagesrc=".quoter($perf["imagesrc"])." where id=$spec";  */
		$query = "UPDATE spectacles set name=%s, description=%s, imagesrc=%s where id=$spec";
		$values = array( $perf["name"], $perf["description"], $perf["imagesrc"] );
		$result = freeseat_query( $query, $values );
	} else {
		/* $q = "INSERT into spectacles (name, imagesrc, description) values ("
		    .quoter($perf['name']).", "
		    .quoter($perf['imagesrc']).", "
		    .quoter($perf['description']).")";  */
		$query = "INSERT into spectacles (name, imagesrc, description) values ( %s, %s, %s )";
		$values = array( $perf['name'], $perf['imagesrc'], $perf['description'] );
		$result = freeseat_query( $query, $values );
		$spec = freeseat_insert_id();
	}
	if ( false === $result ) {
		kaboom(mysql_error());
		return false;
	}
	return $spec;
}
