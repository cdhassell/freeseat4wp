<?php namespace freeseat;

/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.

$Id: index.php 341 2011-04-25 19:03:48Z tendays $

Modifications for Wordpress are Copyright (C) 2013 twowheeler.
*/

/*
 * Helper functions needed for editing show data
 */

/** if an image file was uploaded, this will handle the file */
function get_upload( &$perf ) {
	global $upload_path, $lang;

	$permitted = array("jpeg","jpg","gif","png","bmp");
	foreach( $_FILES as $file_name => $file_array ) {
		if ($file_array['name'] != "") {
			// do nothing if user didn't submit a file
			$parts = pathinfo($file_array['name']);
			$target = $parts["basename"];
			if ( is_uploaded_file( $file_array['tmp_name'] )
				//&& strstr( $file_array['type'], "image" )
				&& isset($parts["extension"])
				&& in_array(strtolower($parts["extension"]),$permitted)) {
				if ( !move_uploaded_file( $file_array['tmp_name'], FS_PATH . $upload_path . $target ) ) {
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
	enhanced_list_box(array( 'table' => 'theatres', 'id_field' => 'id', 
		'value_field' => 'name', 'highlight_id' => $theatre), '', '', "theatre_$perf" );
	return "";
}

function choose_local_file($spec)
/* opens a file dialog to upload a file to the server
- Maximum allowable file size is curently 100K */
{
	echo '<input type="hidden" name="MAX_FILE_SIZE" value="100000">';
	echo '<input name="uploadedfile" type="file" accept="image/*"><br>';
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
		echo '<textarea name="'. htmlspecialchars( stripslashes( $name ) ).'" border=1 rows=18 cols=32>' . $value; 
		echo '</textarea>';
	} else {
		$size = ( ($name=="name") ? 30 : 12 );
		$escvalue = htmlspecialchars( stripslashes( $value ) );
		echo '<input size="'.$size.'" '.($ready?'type="hidden" ':'').' name="'.$name.'" value="'.$escvalue.'">';
	}
	if ($ready) {
		/* Note that we *don't* escape $value. The point is to let
			the admin enter HTML formatting in there if needed */
		echo '<p class="main">' . stripslashes($value) . '</p>';
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
	$values = array( stripslashes($perf['name']), stripslashes($perf['description']), $perf['imagesrc'] );
	if ( $spec > 0 ) {
		/* $q = "UPDATE spectacles set name=".quoter($perf["name"])
		    .", description=".quoter($perf["description"])
		    .", imagesrc=".quoter($perf["imagesrc"])." where id=$spec";  */
		$query = "UPDATE spectacles set name=%s, description=%s, imagesrc=%s where id=$spec";
		$result = freeseat_query( $query, $values );
	} else {
		/* $q = "INSERT into spectacles (name, imagesrc, description) values ("
		    .quoter($perf['name']).", "
		    .quoter($perf['imagesrc']).", "
		    .quoter($perf['description']).")";  */
		$query = "INSERT into spectacles (name, description, imagesrc ) values ( %s, %s, %s )";
		$result = freeseat_query( $query, $values );
		$spec = freeseat_insert_id();
	}
	if ( false === $result ) {
		kaboom(mysql_error());
		return false;
	}
	return $spec;
}

/*
 *  Displays a page to allow the admin user to create and edits shows
 *  Replaces the former showedit plugin - now part of core freeseat
 */
function freeseat_showedit()
{	/* This works in basically two modes that are selected by the $ready variable.
	When $ready is false, the data fields can be edited and POSTed back to this function.  
	If $ready is true, the data is displayed for the user to confirm and POSTed in 
	hidden fields.  If POST contains "save", the confirmed data is saved to the database, 
	and then the forms are opened for editing with $ready = false.  In either case,
	the data is carried in the $_POST array, and nothing is stored in $_SESSION.  
	The print_var() function sets up the HTML input fields based on the $ready variable. */
	
	global $lang, $upload_url, $messages, $plugins;
	if ( !admin_mode() ) { 
		wp_die( 'You do not have sufficient permissions to access this page.' );
	}
	$showedit_url = admin_url( 'admin.php?page=freeseat-showedit' );
	load_alerts();
	
	/** 1 - load db-stored data **/
	
	$totalbooked = 0;
	if (isset($_REQUEST["id"])) {	// we got something
		$spec = (int)$_REQUEST["id"];
	} else {	// otherwise get the latest spectacle from the DB
		$spec = m_eval( "select id from spectacles order by id desc limit 1" );
		if ( $spec === null ) { // empty spectacle table
			$spec = 0;
		}
	}
	
	if ($spec > 0) {  // if we have selected a saved spectacle, fetch data
		$dates = get_shows( "spectacle = $spec");
		foreach ($dates as $i => $dt)
			$totalbooked += ( $dates[ $i ][ "booked" ] = count_bookings( $dt[ "id" ] ) );
		$prices = get_spec_prices( $spec );
		$perf = get_spectacle( $spec );
	} else { 	// if we want a new spectacle, clear variables and use id 0 as marker
		$dates = array();
		$prices = array();
		$perf = array();
		$perf['id'] = 0;
	}
	
	/** 2 - load any POST-provided data **/
	
	$allisfine = true; // set to false in case something went wrong reading post data
	$permit_warn_booking = isset( $_POST[ "submit" ] ); // whether changes in
		// spectacle data may display that warn_booking message
	foreach (array( 'name', 'description', 'imagesrc' ) as $item) {
		if ( isset( $_POST[ $item ] ) )
			$perf[ $item ] = nogpc( $_POST[ $item ] );
	}
	//$prices = $_SESSION['prices'];
	for ( $i=1; $i<=4; $i++ ) { // class loop
		for ( $j=CAT_NORMAL; $j>=CAT_REDUCED; $j-- ) { // cat loop
			$item = "p_$i"."_$j";   //implode( "_", array( 'p', $i, $j ));
			if (isset( $_POST[ $item ] ) ) {
				if (
					$totalbooked && 
					$permit_warn_booking &&
					isset( $prices[ $i ] ) && 
					isset( $prices[ $i ][ $j ] ) &&
					$prices[ $i ][ $j ] != string_to_price( $_POST[ $item ] ) 
				) {
					$permit_warn_booking = false;
					kaboom( $lang[ "warn_bookings" ] );
	 			}
				$prices[ $i ][ $j ] = string_to_price( $_POST[ $item ] );
			}
		}
		if ( isset( $_POST[ "comment$i" ] ) )
			$prices[ $i ][ 'comment' ] = nogpc( $_POST[ "comment$i" ] );
	}
	
	/* performances with a simple number as here alter existing data ...*/
	for ( $i=0; isset($_POST["d$i"]); $i++ ) {
		// not isset()ting because they would only return false if user
		// alters the html before submitting a form
		
		// copytodates($i,'date',sanitise_date($_POST[ "d$i" ]));
		// copytodates($i,'time',sanitise_time($_POST[ "t$i" ]));
		
		$value = sanitise_date( $_POST[ "d$i" ] );
		if ( ( $permit_warn_booking ) && $dates[ $i ][ "booked" ] && ( $dates[ $i ][ 'date' ] != $value ) ) {
			kaboom($lang["warn_bookings"]);
			$permit_warn_booking = false;
		}
		$dates[ $i ][ 'date' ] = $value;		
		
		$value = sanitise_time( $_POST[ "t$i" ] );
		if ( ( $permit_warn_booking ) && $dates[ $i ][ "booked" ] && ( $dates[ $i ][ 'time' ] != $value ) ) {
			kaboom($lang["warn_bookings"]);
			$permit_warn_booking = false;
		}
		$dates[ $i ][ 'time' ] = $value;				
		
		if ( !isset( $dates[$i]["booked"] ) ) { // really don't allow changing theatre..
			// ..when seats have been sold..
			$dates[$i]['theatre'] = (int)($_POST[ "theatre_$i" ]);
		}
		$th = get_theatre( $dates[$i]['theatre'] );
		if (!$th) 
			$allisfine = kaboom($lang["err_spectacle"]);
	    else 
	    	$dates[$i]['theatrename'] = $th["name"];
	}
	/* ... while performances with an xnumber are new. */
	for ( $i=0; isset($_POST["dx$i"]); $i++ ) {
		// Déjà vu? RUN! They must have changed something in the Matrix
		// NOTE: no need to use the copytodates wrapper here because these
		// could not have sold tickets anyway
		$dates["x$i"]['date'] = sanitise_date($_POST[ "dx$i" ]);
		$dates["x$i"]['time'] = sanitise_time($_POST[ "tx$i" ]);
		$dates["x$i"]['theatre'] = (int)($_POST[ "theatre_x$i" ]);
		$th = get_theatre($dates["x$i"]['theatre']);
		if (!$th) 
			$allisfine = kaboom($lang["err_spectacle"]);
		else 
			$dates["x$i"]['theatrename'] = $th["name"];
	}
	$nextxtra = $i; // this is the first $i such that dx$i was NOT defined.
	
	get_upload($perf);
	
	// imagesrc has now been set as follows:
	// 1: if user uploads something then that is the value.
	// 2: otherwise, if POST gives a values then that is taken
	// 3: otherwise, any existing data in the database is used
	// print "<pre>Dates = " . print_r( $dates, 1 ) . "</pre>";
	// make sure all of the variables are initialized
	for ( $i=1; $i<=4; $i++ ) {   // class loop
		for ( $j=CAT_NORMAL; $j>=CAT_REDUCED; $j-- ) {   // cat loop
			if ( !isset( $prices[ $i ][ $j ] ) ) $prices[ $i ][ $j ] = 0.0;
		}
		if ( !isset( $prices[ $i ][ 'comment' ] ) ) $prices[ $i ][ 'comment' ] = "";
	}
	foreach ( array( 'name', 'description', 'imagesrc' ) as $item ) {
		if ( !isset( $perf[ $item ] ) ) $perf[ $item ] = "";
	}
	
	/** 3 - validate data **/
	$ready = ( isset( $_POST[ "submit" ] ) || isset( $_POST[ "save" ] ) )  // $ready:
		&& ( $allisfine );			       // When true, show readonly
	if ( isset( $_POST[ "edit" ] ) ) {
		$ready = false;            // data and a button to save
	}
	if ($ready) {			        // changes. When false show
		if ($perf["name"]=='') {	       // an editable form and a
			$ready = false;		      // button to confirm. It is
			kaboom($lang["err_nospec"]);     // set to true if user
		}				    // submitted a form and there were no mistakes
		$atleastone = false;
		foreach ($dates as $dt) {
			if (isset($dt['date']) && ($dt['date']!="0000-00-00")) $atleastone = true;
		}
		if (!$atleastone) {
	    	$ready = false;
	    	kaboom($lang["err_nodates"]);
		}
		$atleastone = false;
		for ( $i=1; $i<=4; $i++ ) {  // class loop
	    	if (isset($prices[$i][CAT_NORMAL] ) && ($prices[$i][CAT_NORMAL]>0))
	      		$atleastone = true;
	    }
		if (!$atleastone) {
			$ready = false;
			kaboom($lang["err_noprices"]);
		}
		if (!$ready) {   // something went wrong
			kaboom($lang["err_show_entry"]);
		}
	}
	
	// Note that the error message is set only if user requested saving
	// BUT storing failed. And in that case the if is not taken so we
	// proceed to user interface without redirection.
	if ($ready && isset($_POST["save"])) {
		
	/* 4 - data is valid and user requested saving */

	    $spec = set_perf( $perf );
	    if ($spec) {
			if (!(set_spec_prices( $spec, $prices ) &&
			      set_dates( $spec, $dates ))) {
			    kaboom($lang["show_not_stored"]);
		
			    // the set_*()ing failed so we set ready to false to make
			    // the interface editable again
			    $ready = false;
			} else {
			    // success
			    kaboom($lang["show_stored"]);
			    $_SESSION["messages"] = $messages;
			    $perf['id'] = $spec;
				$ready = false;
			    do_hook_function("showedit_save", $perf);
			    // clear variables and redisplay
			    $_POST = array();
			    $dates = get_shows( "spectacle = $spec");
				foreach ($dates as $i => $dt)
					$totalbooked += ( $dates[ $i ][ "booked" ] = count_bookings( $dt[ "id" ] ) );
				$prices = get_spec_prices( $spec );
				$perf = get_spectacle( $spec );
				for ( $i=1; $i<=4; $i++ ) {   // class loop
					for ( $j=CAT_NORMAL; $j>=CAT_REDUCED; $j-- ) {   // cat loop
						if ( !isset( $prices[ $i ][ $j ] ) ) $prices[ $i ][ $j ] = 0.0;
					}
					if ( !isset( $prices[ $i ][ 'comment' ] ) ) $prices[ $i ][ 'comment' ] = "";
				}
				foreach ( array( 'name', 'description', 'imagesrc' ) as $item ) {
					if ( !isset( $perf[ $item ] ) ) $perf[ $item ] = "";
				}
			}
	    } else {
			// failed creating a spectacle
			$ready = false;
	    }
	}
	
	/** 5 - show user interface **/
	
	show_head();
	
	/* uncomment following lines to display debugging information  
	echo '<pre>POST:';print_r($_POST);echo '</pre>';
	echo '<pre>dates:';print_r($dates);echo '</pre>'; 
	echo '<pre>perf:';print_r($perf);echo '</pre>'; 
	echo "<pre>spec=$spec</pre>";  */
	
	echo '<h2>' . $lang[$ready?'title_mconfirm':'title_maint'] . '</h2>';
	echo "<form action='$showedit_url' name='choose_spec' method='post'>";
	if (function_exists('wp_nonce_field')) wp_nonce_field('freeseat-showedit-choose-spectacle');
	// spectacle selection form depends on the onchange action in choose_spectacle()
	echo '<h3>' . $lang["spectacle_name"] . '</h3>';
	
	choose_spectacle( true, $spec );
	echo ' <input class="button button-primary" type="submit" value="'.$lang["select"].'">'; 
	// the following doesn't work, but don't know why
	// submit_button( $lang[ "select" ], 'primary', 'submit', false );
	echo '</form>';
	echo '<div class="form">'; // the big div
	echo "<form action='$showedit_url' method='post' enctype='multipart/form-data'>"; // data submission form
	if (function_exists('wp_nonce_field')) wp_nonce_field('freeseat-showedit-enter-data');
	echo '<input style="display : none;" type="submit" name="submit">';// default action when user presses enter in a field
	
	echo '<div class="name-selection"><input type="hidden" name="id" value="'.$spec.'">';
	print_var( "name", $perf['name'], $ready, $lang["name"], 40) ;
	print_var( "description", $perf['description'], $ready, $lang["description"], 75);
	echo '</div>';
	
	echo '<div class="image-selection"><h3>' . $lang['imagesrc'] . '</h3>' ; // image upload form
	 // imagesrc: default, to be used if user does not upload an image.
	echo '<input type="hidden" name="imagesrc" value="'.htmlspecialchars($perf["imagesrc"]).'">';
	if ($perf['imagesrc']) {
	    echo $lang['file'] . htmlspecialchars($perf['imagesrc']) . '<br>';
	    echo '<img src="' . htmlspecialchars( plugins_url( $upload_url.$perf['imagesrc'], __FILE__ ) ) . '"><br>';
	} else
		echo $lang['noimage'];
	if (!$ready) choose_local_file('image');
	echo '</div>';
	
	echo '<div class="form clear-both">';
	echo '<h3>' . $lang['datesandtimes'] . '</h3>';
	if (!$ready) echo '<p class="fine-print">' . $lang["warn_spectacle"] . '</p>';
	
	echo '<table BORDER="1" CELLPADDING="4">';
	echo '<tr><th>'.$lang["date_title"].'<th>'.$lang["time_title"].'<th>'.$lang["theatre_name"].'<th>'.$lang["seats_booked"].'</tr>';
	$dispperf = 0;
	foreach ( $dates as $i => $dt ) {
		echo '<tr><td>';
		print_var("d$i",(isset($dt['date']) ? $dt['date'] : ''), $ready);
		echo '</td><td>';
		print_var("t$i",(isset($dt['time']) ? $dt['time'] : ''), $ready);
		echo '</td><td>';
		if (!$ready && ((substr($i,0,1)=='x') || (!$dt["theatrename"])))
		  choose_seatmap( $i, $dt["theatre"] );
		else {
		  echo "<input type='hidden' name='theatre_$i' value='".htmlspecialchars($dt['theatre'])."'>";
		  echo htmlspecialchars($dt['theatrename']);
		}
		echo '</td><td>';
		echo isset($dt["id"])? count_bookings($dt["id"]): 'n/a';
		$dispperf++;
	}
	
	if (isset($_POST["perfcount"]))
	    $perfcount = max($dispperf,(int)($_POST["perfcount"]));
	else
	    $perfcount = $dispperf;
	
	if (isset($_POST["addperf"]))
	     $perfcount ++;
	
	if (!$ready) {
	  for ($i = $nextxtra; $dispperf<$perfcount; $i++) {
	    /* more lines to allow adding performances */
	    echo '<tr><td>';
	    print_var("dx$i",'', $ready);
	    echo '<td>';
	    print_var("tx$i",'', $ready);
	    echo '<td>';
	    choose_seatmap( "x$i" ); // x as in "extra" - i guess you got
				     // the..
	    echo '<td>n/a';
	    $dispperf++;		  // ..idea by now
	  }
	}
	  
	echo '</table>';
	echo '<input type="hidden" name="perfcount" value="'.$perfcount.'">';
	if (!$ready) {
		submit_button( "+ Add a Date", 'secondary', 'addperf' );
		// echo '<input type="submit" name="addperf" value="+ Add a Date">';
	}
	echo '</div><div class="form">';
	echo '<h3>' . $lang['prices'] . '</h3>';
	echo '<table BORDER="1" CELLPADDING="4" >';
	echo '<tr><th>'. $lang["class"] . '<th>'.$lang["price"].'<th>'.$lang["price_discount"].'<th>'.$lang["comment"].'</tr>';
	for ( $i=1; $i<=4; $i++ ) { // class loop
		echo '<tr><td>'. $i . '</td>';
		for ( $j=CAT_NORMAL; $j>=CAT_REDUCED; $j-- ) { // cat loop
			echo '<td>';
			print_var("p_$i"."_$j",price_to_string($prices[$i][$j]), $ready);
		}
		echo '<td>';
		print_var("comment$i", $prices[$i]['comment'], $ready);
		echo '</tr>';
	}
	
	echo '</table></div><p>';
	echo '<br style="clear:both;"></div>'; // a br to make the big div large enough
	
	if ($ready) {
	  echo '<p class="emph">' . $lang["warn_show_confirm"] . '</p>';
	  echo '<p class="main">';
	  submit_button( $lang[ "save" ], 'primary', 'save', false );
	  echo '  ';
	  submit_button( $lang[ "link_edit" ], 'primary', 'edit', false );
	  echo '</p>';
	} else {
	  echo '<p class="emph">' . $lang["are-you-ready"] . '</p>';
	  echo '<p class="main">';
	  submit_button( $lang["continue"] );
	  echo '</p>';
	}
	
	echo '</p></form>';
	show_foot(); 
} // end of freeseat-showedit

