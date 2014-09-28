<?php namespace freeseat;


/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.

$Id$

Tools to ease access to mysql
*/

/** pass a mysql query and get an
array of (0,1,...-indexed) mysql records **/
function fetch_all($s) {
	global $wpdb;
	
	$s = fs2wp( $s );	
	return $wpdb->get_results( $s, ARRAY_A );
}

function fetch_all_n( $s ) {
	global $wpdb;
	
	$s = fs2wp( $s );
	return $wpdb->get_results( $s, ARRAY_N );
}

/* For a mysql query supposed to return one row with one value,
return the contents of that row. If something went wrong then null is
returned */
function m_eval($s) {
	global $wpdb;
	
	$s = fs2wp( $s );
	return $wpdb->get_var( $s );
}

/** For a query supposed to return a number of rows with one value
 each, return an array of all those values. If something went wrong
 then null is returned. 

 $s: the mysql query. */
function m_eval_all($s) {
	global $wpdb;
	
	$s = fs2wp( $s );
	$results = $wpdb->get_results( $s, ARRAY_A );
	return $results[ 0 ];	
}

// to execute a query with no expected return of records
function freeseat_query( $s, $v = NULL ) {
	global $wpdb;

	$s = fs2wp( $s );
	if ( empty( $v ) ) {
		$sql = $s;
	} else {
		$sql = $wpdb->prepare( $s, $v );
	}
	// sys_log( "freeseat_query call with $sql" );
	return $wpdb->query( $sql );
}

// wrapper around WP insert_id function
function freeseat_insert_id() {
	global $wpdb;
	return $wpdb->insert_id;	
}

// wrapper around WP error function
function freeseat_mysql_error() {
	$wpdb->print_error();
}

/*
 * Replaces freeseat table names with WP ready table names.
 * Lazy way to avoid rewriting every SQL statement.
 * But, to make this work price.price has been changed to price.amount.
 */
function fs2wp( $sql ) {
	$newtables = array(); 
	$oldtables = array( 'booking', 'price', 'seats', 'seat_locks', 'shows', 'spectacles', 'theatres', 'class_comment', 'ccard_transactions' );
	foreach ( $oldtables as $table ) {
		$newtables[] = 'freeseat_' . $table;			
	} 
	$sql = str_replace ( $oldtables, $newtables, $sql );
	return $sql;	
}



