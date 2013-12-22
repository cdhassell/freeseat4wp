<?php

/*
Syntax of common WP database calls

global $wpdb;
$wpdb->query('query');
$wpdb->get_var( 'query', column_offset, row_offset );
$wpdb->get_row('query', output_type, row_offset); 
$wpdb->get_col( 'query', column_offset );
$wpdb->prepare( 'query' , value_parameter[, value_parameter ... ] ); 
$wpdb->get_results( 'query', output_type );
$wpdb->insert( $table, $data, $format );
$wpdb->replace( $table, $data, $format );
$wpdb->insert_id
$wpdb->update( $table, $data, $where, $format = null, $where_format = null );
$wpdb->delete( $table, $where, $where_format = null ); 
$wpdb->show_errors(); 
$wpdb->hide_errors();
$wpdb->print_error();
$wpdb->flush();

Examples:
$wpdb->query( 
	$wpdb->prepare( 
		"DELETE FROM $wpdb->postmeta
		 WHERE post_id = %d
		 AND meta_key = %s",
	     13, 'gargle' ) 
);

$wpdb->update( 
	'table', 
	array( 
		'column1' => 'value1',	// string
		'column2' => 'value2'	// integer (number) 
	), 
	array( 'ID' => 1 ), 
	array( 
		'%s',	// value1
		'%d'	// value2
	), 
	array( '%d' ) 
);

*/

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

// to execute a query with no expected return
function freeseat_query( $s, $v = NULL ) {
	global $wpdb;

	$s = fs2wp( $s );
	if ( empty( $v ) ) {
		return $wpdb->query( $s );
	} else {
		// print "<pre>query = " . $wpdb->prepare( $s, $v ) . "</pre>";
		return $wpdb->query( $wpdb->prepare( $s, $v ) );
	}
}

// wrapper around WP insert_id function
function freeseat_insert_id() {
	global $wpdb;
	return $wpdb->insert_id;	
}

/*
 * Replaces freeseat table names with WP ready table names.
 * Lazy way to avoid rewriting every SQL statement.
 * But, to make this work price.price has been changed to price.amount.
 */
function fs2wp( $sql ) {
	$newtables = array(); 
	$oldtables = array( 'booking', 'price', 'seats', 'seat_locks', 'shows', 'spectacles', 'theatres', 'class_comment' );
	foreach ( $oldtables as $table ) {
		$newtables[] = 'freeseat_' . $table;			
	} 
	$sql = str_replace ( $oldtables, $newtables, $sql );
	return $sql;	
}



