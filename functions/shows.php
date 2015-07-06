<?php namespace freeseat;


/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.

$Id$

Information about shows (a.k.a performances)
*/

function get_shows($cond="") {
  if ($cond) $cond = "( $cond ) and ";
  return fetch_all( "select shows.id, shows.theatre, shows.disabled, theatres.name as theatrename, theatres.imagesrc, date, time, spectacle as spectacleid from shows,theatres where $cond theatres.id=shows.theatre order by date,time" );
}

function get_show($id) {
  global $lang;
  if (empty($id)) return false;
  $zou = get_shows("shows.id=$id");
  if (count($zou))
    return $zou[0];
  else {
    kaboom($lang["err_showid"]);
    return false;
  }
}

/** in: mysql record returned by get_show().

out: timestamp of the show*/
function show_at($sh) {
  $dt = explode("-",$sh["date"]);
  $tm = explode(":",$sh["time"]);
  return mktime($tm[0],$tm[1],$tm[2],$dt[1],$dt[2],$dt[0]);
}

/** Returns whether the given payment method is open for the given
    show at the present time */
function payment_open($sh,$mode) {
	global $now;
	
	$c = get_config();
	$show_at = show_at($sh);
	switch ($mode) {
		case PAY_POSTAL:
			if (!isset($c["closing_post"])) return false;
			return (!$c["disabled_post"]) && ($now<=sub_open_time($show_at,60*$c["closing_post"]));
		case PAY_CCARD:
			// if (!isset($c["closing_ccard"])) return false;
			return do_hook_exists('ccard_exists') && (!$c["disabled_ccard"]) && ($now<=$show_at-60*$c["closing_ccard"]);
		case PAY_CASH:
			// if (!isset($c["closing_cash"])) return false;
			return ((!$c["disabled_cash"]) && ($now<=$show_at-60*$c["closing_cash"]) 
					// && ($c["opening_cash"] && ($now>=sub_open_time($show_at,60*$c["opening_cash"])))
				);
		case PAY_OTHER:
			return admin_mode();
		default:
			return false;
  }
}

/** in: mysql record returned by get_show().

out: in how much minutes the ticketing will be closed (returns zero in
case it is closed already)

Changing from previous versions, having no free seats left doesn't
force zero to be returned */
function show_closing_in($sh) {
	global $now;
	
	if ($sh["disabled"]) return 0;
	$show_at = show_at($sh);
	$closing = $now;
	$c = get_config();
	if (payment_open($sh,PAY_CCARD))
		$closing = max($closing,$show_at-60*$c["closing_ccard"]);
	if (payment_open($sh,PAY_CASH))
		$closing = max($closing,$show_at-60*$c["closing_cash"]);
	if (payment_open($sh,PAY_POSTAL))
		$closing = max($closing,sub_open_time($show_at,60*$c["closing_post"]));
	return ($closing-$now)/60;
}

/** Returns whether the prices of the given spectacle depend on category. */
function price_depends_on_cat($spectacleid) {

  if ( m_eval( "select count(distinct class)*count(distinct cat) from price where spectacle=$spectacleid" ) !=
      m_eval( "select count(*) from price where spectacle=$spectacleid" ) ) {
    // price table is incomplete, so don't bother doing guess work
    return true;
  } else {
    foreach (fetch_all( "select count(distinct amount) as cnt from price where spectacle=$spectacleid group by class" ) as $l ) {
      if ($l["cnt"]>1) 	{
	    return true;
	  }
    }
    return false; /* didn't find any class for which the price was different. */
  }
}

