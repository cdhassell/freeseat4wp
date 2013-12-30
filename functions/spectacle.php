<?php


/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.

$Id$

Spectacle and Theatre information
*/

/** Returns the mysql record corresponding to the spectacle. **/
function get_spectacle($spectacleid) {
  global $lang;
  
  $zou = fetch_all( "select * from spectacles where id=$spectacleid" );
  if ($zou) return $zou[0]; else {
    kaboom($lang["err_spectacleid"]);
    return false;
  }
}

function get_theatre($theatreid) {
  $zou = fetch_all( "select * from theatres where id=". (int)$theatreid );
  if ($zou) return $zou[0]; else return false;
}

function is_staggered( $theatreid ) {
  // (convert to boolean)
  return m_eval("select staggered_seating from theatres where id=$theatreid")?true:false;
}

