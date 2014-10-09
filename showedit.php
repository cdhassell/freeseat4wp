<?php namespace freeseat;

/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.

$Id: index.php 341 2011-04-25 19:03:48Z tendays $

Modifications for Wordpress are Copyright (C) 2013 twowheeler.
*/

/*
 * Helper functions needed for editing show data
 */

/* displays a combo box allowing to choose a theatre for the given
   performance. Pass currently selected value as second parameter */
function choose_seatmap( $perf, $theatre=null )
{
	enhanced_list_box(array( 'table' => 'theatres', 'id_field' => 'id', 
		'value_field' => 'name', 'highlight_id' => $theatre), '', '', "theatre_$perf" );
	return "";
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
		echo '<textarea name="'. htmlspecialchars( $name, ENT_QUOTES ).'" border=1 rows=18 cols=32>' . $value . '</textarea>';
	} else {
		$size = ( ($name=="name") ? 30 : 12 );
		$escvalue = htmlspecialchars( stripslashes( $value ), ENT_QUOTES );
		echo '<input size="'.$size.'" '.($ready?'type="hidden" ':'').' name="'.$name.'" value="'.$escvalue.'">';
	}
	if ($ready) {
		/* Note that we *don't* escape $value. The point is to let
			the admin enter HTML formatting in there if needed */
		echo "<p class='main' style='max-width:225px;'>$value</p>";
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
// Using WP $wpdb->prepare() method to sanitize data
{
	$spec = ( isset( $perf[ "id" ] ) ? (int) $perf[ "id" ] : 0 );
	$values = array( stripslashes($perf['name']), stripslashes($perf['description']), $perf['imagesrc'] );
	if ( $spec > 0 ) {
		$query = "UPDATE spectacles set name=%s, description=%s, imagesrc=%s where id=$spec";
		$result = freeseat_query( $query, $values );
	} else {
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

function show_post($spec) {
	global $lang, $upload_path, $page_url;
	
	$wpcat = wp_create_category( 'Shows' );
	// does this post already exist?
	$args=array(
		'name' => 'freeseat_'.$spec['id'],
		'post_type' => 'post',
		'post_status' => 'publish',
		'numberposts' => 1
	);
	$old_posts = get_posts($args);
	$ID = ( $old_posts ? $old_posts[0]->ID : '' );
	// the only content is a shortcode call to freeseat-single
	$content = "[freeseat-single spectacleid=\"{$spec['id']}\"]";
	// set up the post array
	$post = array(
		'ID'             => $ID,  
		'post_content'   => $content, 
		'post_name'      => 'freeseat_'.$spec['id'], 
		'post_title'     => $spec['name'], 
		'post_status'    => 'publish',
		'post_type'      => 'post',
		'comment_status' => 'closed', 
		'tags_input'     => 'shows',  
		'post_category'  => array($wpcat)
	);
	if(empty($ID)) {
		$ID = wp_insert_post( $post );
	} else {
		$ID = wp_update_post( $post );
	}
	/* if (isset($spec['imageid']) && $spec['imageid']) {
		set_post_thumbnail( $ID, $spec['imageid'] );
	} */
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
	foreach (array( 'name', 'description', 'imagesrc', 'imageid' ) as $item) {
		if ( isset( $_POST[ $item ] ) )
			$perf[ $item ] = nogpc( $_POST[ $item ] );
	}
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
		if ($perf["name"]=='') {	// an editable form and a
			$ready = false;		    // button to confirm. It is
			kaboom($lang["err_nospec"]);     // set to true if user
		}							// submitted a form and there were no mistakes
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
			    // $_SESSION["messages"] = $messages;
			    $perf['id'] = $spec;
				$ready = false;
			    do_hook_function("showedit_save", $perf);
			    show_post($perf);
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
	echo "<pre>spec=$spec</pre>";   */
	
	echo '<h2>' . $lang[$ready?'title_mconfirm':'title_maint'] . '</h2>';
	echo "<form action='$showedit_url' name='choose_spec' method='post'>";
	if (function_exists('wp_nonce_field')) wp_nonce_field('freeseat-showedit-choose-spectacle');
	// spectacle selection form depends on the onchange action in choose_spectacle()
	echo '<h3>' . $lang["spectacle_name"] . '</h3>';
	choose_spectacle( true, $spec );
	submit_button( $lang[ "select" ], 'primary', 'submit', false );
	echo '</form>';
	echo '<div class="form">'; // the big div
	echo '<div class="form">'; // smaller div grouping the spectacle items together
	echo "<form action='$showedit_url' method='post' enctype='multipart/form-data'>"; // data submission form
	if (function_exists('wp_nonce_field')) wp_nonce_field('freeseat-showedit-enter-data');
	echo '<input style="display : none;" type="submit" name="submit">';// default action when user presses enter in a field
	
	echo '<div class="name-selection"><input type="hidden" name="id" value="'.$spec.'">';
	print_var( "name", $perf['name'], $ready, $lang["name"], 40) ;
	print_var( "description", $perf['description'], $ready, $lang["description"], 75);
	echo '</div>';
	
	echo '<div class="image-selection"><h3>' . $lang['imagesrc'] . '</h3>' ; // image upload form
	// imagesrc: default, to be used if user does not upload an image.
	echo '<input type="hidden" name="imagesrc" value="'.$perf["imagesrc"].'">';
	// imageid holds the WP id value that will be used to access the post
	// the value is added to this hidden field by fileupload.js
	$imageid = ( isset($perf['imageid']) ? $perf['imageid'] : 0 ); 
	echo "<input type='hidden' name='imageid' value='$imageid' id='imageid'>";
	if ($perf['imagesrc']) {
	    echo $lang['file'] . basename(parse_url( $perf['imagesrc'], PHP_URL_PATH)). '<br>';
	    echo '<img src="' . $perf['imagesrc'] . '"><br>';
	} else
		echo $lang['noimage'];
	if (!$ready) {
		// now using the standard wordpress file uploader.  files are copied to the media library.
		?><label for="upload_image">
			<input id="upload_image" type="text" size="25" name="imagesrc" value="<?php echo $perf['imagesrc']; ?>" />
			<input id="upload_image_button" class="button" type="button" value="Upload Image" />
			<br />Enter a URL or upload an image
		</label><?php
	}
	echo '</div>';  // closing the image selection div
	echo '</div>';  // closing the spectacle div
	
	echo '<div class="form clear-both">';
	echo '<h3>' . $lang['datesandtimes'] . '</h3>';
	if (!$ready) echo '<p class="fine-print">' . $lang["warn_spectacle"] . '</p>';
	
	echo '<table>';
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
	}
	echo '</div><div class="form">';
	echo '<h3>' . $lang['prices'] . '</h3>';
	echo '<table>';
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
	echo '</table></div>';
	
	echo '<div class="clear-both"></div></div>'; 
	
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
	  submit_button( $lang["continue"], 'primary', 'submit', false );
	  echo '</p>';
	}
	
	echo '</p></form>';
	show_foot(); 
} // end of freeseat-showedit

add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\freeseat_fileupload');
 
function freeseat_fileupload() {
    if (isset($_GET['page']) && $_GET['page'] == 'freeseat-showedit') {
        wp_enqueue_media();
        wp_register_script('freeseat-admin-js', plugins_url( 'fileupload.js', __FILE__ ), array('jquery'));
        wp_enqueue_script('freeseat-admin-js');
    }
}