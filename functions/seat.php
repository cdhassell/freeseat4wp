<?php namespace freeseat;


/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.

$Id$

Tools to access seatmaps, and query seat availability
*/

function get_seat($id) {
  if ($zou = m_eval_all("select *, 1 as cnt from seats where id=$id"))
    return $zou;
  else
    return false;
}

/** Return an array of zones in the theatre with the given id. */
function get_zones($theatre) {
  return m_eval_all("select distinct zone from seats where seats.theatre = $theatre");
}

/* Return the state (ST_something) of the given seat for the given
   show.

   WARN: locked seats will return ST_LOCKED, even from the thread that
   locked them.

   Set $ignorelocks to true to not check for the ST_LOCKED state (and
   return ST_FREE even for locked but not booked seats) */

function get_seat_state($seatid,$showid,$ignorelocks = false) {
  global $lang, $now;
  /* We for now never return ST_DELETED - if we do we'll need to be
     careful to prefer non-deleted bookings over deleted ones in case both
     exist for that seat. BUT we check anyway this function's return
     value for ST_DELETED for when it is updated :-) */

  $state= m_eval("select state from booking where seat=$seatid and state!=".ST_DELETED." and showid=$showid limit 1");
  if ($state !== null) return $state;

  if ($ignorelocks) return ST_FREE;
  /*      echo "checking ".$seatid for locks<br>"; */

  // $zou = m_eval("select until from seat_locks where seatid=$seatid and showid=$showid");
  $zou = freeseat_get_lock( $seatid, $showid );
  if ( $zou )
    return ST_LOCKED;
  else
    return ST_FREE;
}

/** whether current _SESSION has checked the given seat **/
function is_seat_checked($id) {
  return isset($_SESSION["seats"][$id]);
}

/** Session-register seats of which the key is in the given array
    (sets the $SESSION["seats"] variable and *adds* relevant
    $SESSION["nncnt-".$n] entries).

    Returns true if the number of requested seats is below the
    max_seats configuration option.

    WARN - make sure $sh is set to the in-session show before calling this

     **/
function load_seats($k,$check_limit = TRUE) {
  global $lang, $sh;

  $seatcount = 0; // how many seats were *requested*.
  $seats = array();
  //  $sh = get_show($_SESSION["showid"]);

  // better way would be to make check_seats have a parameter telling
  // if to do the filtering, instead of load_seats
  foreach ($k as $key => $value) {
    if (is_numeric($key)) {
	$n = (int)$key;
	$cnt = 1;
    } else if (substr($key,0,6)=='nncnt-') {
      $n = (int)(substr($key,6));
      $cnt = (int)$value;
      $_SESSION["nncnt-$n"] = $cnt;
    } else $cnt=0; // other data is ignored
    if ($cnt) {
      $proto = get_seat($n);
      /* we just leave a single entry mentioning it counts as $cnt
	 seats. */
      if ($proto && ($proto["theatre"]==$sh["theatre"])) {
	// we have to set ["cat"] here because lock_seats expects it
	// (probably not for a good reason but let's leave it like
	// this)
	$proto["cat"] = CAT_NORMAL; // default category
	$proto["cnt"] = $cnt;
	$seatcount += $cnt;
	
	$seats[$n] = $proto;
      } // else: we ignore requests for unexistant seats
    }
  }
  if ($check_limit && $seatcount > get_config("max_seats"))
    return kaboom($lang["err_seatcount"]);

  // we copy show information into each seat (for generic
  // functions that also work with booking entries that are
  // not all for the same show)
  array_setall($seats,"date",$sh["date"]);
  array_setall($seats,"time",$sh["time"]);
  array_setall($seats,"theatrename",$sh["theatrename"]);
  array_setall($seats,"spectacleid",$sh["spectacleid"]);
  
  $_SESSION["seats"] = $seats;
  return true;
}


/** Attempts to lock the given seat for the given show. Returns an
    array of seat structures.

    The given seat structure must provide a ["cnt"] entry specifying how
    many seats are required. A value bigger than one only makes sense for
    unnumbered seats. The returned seats will all be equivalent to the one
    given, and have a cnt set to 1.

    A seat is *equivalent* to itself plus, in case of unnumbered seats, to
    all other unnumbered seats in the same theatre, same class and same
    zone.

    The returned seat structure might be for another seat in case the
    requested one was not available but "equivalent" ones were found.

    Note that when the $cnt request could not be satisfied, the
    returned array will contain only the seats that were successfully
    locked. You are expected to check the size of the returned array
    to detect this condition.

    The expiration time of the lock is set by the $lockingtime
    configuration variable.

    This function replaces the locking mechanism used in previous versions,
    the locking primitive being provided by primary key uniqueness in the
    seat_locks table.

    The "check seats are free then book them" in finish.php is thread-safe
    even without LOCK TABLE because check seats calls lock_seat which
    guarantees exclusive access to this session for $lockingtime seconds.
**/
function lock_seats($seat,$showid) {
  global $now,$lockingtime;

  $res = array(); // result seats will be put here  
  
  /*   echo "<pre>"; */
  /*   echo "locking ".$seat["id"]." * ".$seat["cnt"]; */
  /*   print_r($seat); */
  /*   echo '</pre>'; */

  $cnt = $seat["cnt"];

  if ($cnt==0) return $res; // just in case :-)

  /* 1. Try to lock $seat itself */

  // TODO We used not to have to check the state if a lock is known to
  // be there (SESSION["until"]...) We should put that back in at some
  // point...

  /* First check the seat state - we don't try locking it if it's taken */
  $st = get_seat_state($seat["id"],$showid,true);
  if (($st==ST_FREE) || ($st==ST_DELETED)) { // seat still available
    /* We first remove any stale lock, or our own if there's any. We
       don't use update because if our lock is too old, this might
       create a race condition */

    /* Note that at this point the seat might no longer be available
    (i.e. the expression in the if-condition might no longer hold, if
    another thread booked the seat precisely now). However in that
    case the other thread would still have its lock in place, the
    delete below would not apply, and so the insert after it would
    fail */

    /* WARN - do not replace the below delete-insert pair by an update! It
       would create a race condition! As explained above, two
       concurrent updates would both succeed, but two concurrent
       delete+insert would have one fail and one succeed. (Actually,
       in some cases update is safe ... Oh well) */
    /* if (!freeseat_query("delete from seat_locks where seatid=".$seat['id']." and showid=$showid and (until<".$now." or sid='".mysql_real_escape_string(session_id())."')")) {
      myboom("Failed deleting seat lock");
    }
    $rows = freeseat_query("insert into seat_locks (seatid,showid,sid,until) values (".$seat['id']." , $showid,'".mysql_real_escape_string(session_id())."',".($now+$lockingtime).")");
    */
    freeseat_delete_lock( $seat[ 'id' ], $showid );  
    if ( freeseat_set_lock( $seat[ 'id' ], $showid ) ) {
		// echo "<pre> set lock success ".$seat['id']."</pre>"; 
      $s = $seat;
      $s["cnt"]=1;
      $res[$s["id"]] = $s;
      $cnt--;
      if ($cnt==0) return $res;
    } // else print "<pre>set lock failed: ".$seat['id']."</pre>";
  }

  /* 2. Fetch into $pot(ential) all seats equivalent to the requested
     one. */

  if ($seat["row"]!=-1) return $res; // (there are no equivalent
				     // seats if $seat isn't
  else {			     // unnumbered)
    $pot = fetch_all( $q = "select *,1 as cnt,".((int)$seat["cat"]).
				   " as cat,seats.id as id from seats left join booking on booking.seat=seats.id and booking.showid=$showid and booking.state!=".ST_DELETED.
				   " where booking.id is null and seats.row=-1 and seats.class=".$seat["class"].
				   " and seats.theatre=".$seat["theatre"].         // Lets put all the seats in the same theatre  :-)
				   " and seats.zone='".mysql_real_escape_string($seat["zone"])."'" );
    if ($pot===false) {
      myboom($q.": couldn't get set of equivalent seats");
      return $res;
    }
  }

  /* 3. Now loop through the set of potential seats until the $cnt
     request is satisfied */
  /*     echo "<pre>"; */
  foreach ($pot as $s) {
    $potid = $s["id"];
    /* We've already dealt with that one so it wouldn't give more
       result than we got at step one */
    if ($potid==$seat["id"]) continue;
    
    $st = get_seat_state($potid,$showid,true);
    if (($st!=ST_FREE) && ($st!=ST_DELETED)) continue; // seat is
						       // booked. Try next
	/*
    freeseat_query("delete from seat_locks where seatid=$potid and showid=$showid and (until<$now or sid='".mysql_real_escape_string(session_id())."')");
    
    $rows = freeseat_query("insert into seat_locks (seatid,showid,sid,until) values ($potid , $showid,'".mysql_real_escape_string(session_id())."',".($now+$lockingtime).")");
    */
	if ( !freeseat_set_lock( $potid, $showid ) ) continue; // seat was locked. Try next
    /*     echo "..success"; */
    /* if this point is reached then lock was successful */
    $res[$s["id"]] = $s;
    $cnt--;
    if ($cnt==0) return $res;
  }
  /*   echo "</pre>"; */
  return $res;
}

/** Call this function to discard a seat selection - use this
 * *INSTEAD* of setting $_SESSION["seats"] to the empty array.
 */
function unlock_seats($freesession = true) {
	if ( isset( $_SESSION["seats"] ) ) {
		if (isset($_SESSION['showid']))
			$showid = $_SESSION[ 'showid' ];
		elseif (isset($_GET['showid']))
			$showid = $_SESSION['showid'] = $_GET['showid'];
		foreach ($_SESSION["seats"] as $s) {
			freeseat_delete_lock( $s[ 'id' ], $showid );
			// freeseat_query("delete from seat_locks where sid=".quoter(session_id()));
		}
	}
	if ( $freesession ) {
		$_SESSION[ "seats" ] = array();
	}
}

/** see if the _SESSION-selected seats are (still) available.  If not,
 * just return false. If ok then extend their lock and return true.
 */
function check_seats() {
	global $lang;
	
	$success = true;
	$seats = array();
	$sh = get_show($_SESSION["showid"]);
	if ( !isset($_SESSION['seats']) ) return true;
	foreach ($_SESSION["seats"] as $s) {
		$expanded = lock_seats($s,$_SESSION["showid"]);
		array_setall($expanded,"date",$sh["date"]);
		array_setall($expanded,"time",$sh["time"]);
		array_setall($expanded,"theatrename",$sh["theatrename"]);
		array_setall($expanded,"spectacleid",$sh["spectacleid"]);
		//    echo "<pre>MERGING EXPANDED INTO SEATS\nseats = "; print_r($seats); 
		//    echo "\nexpanded = ";  print_r($expanded); 
		$seats = array_union($seats,$expanded);
		//    echo "\nunion = "; print_r($seats); echo "</pre>";
		if ( count($expanded) < $s["cnt"] ) {
			$success = false;
		}
	}
	$_SESSION["seats"] = $seats;
	return $success;
}

/* helper function for compute_cats.
 */
function seat_reduce(&$s,$n) {
  global $ncls,$ccls,$cats,$counts,$total; // "global"? eeek - what abt passin 'em as third "userdata" parameter?
  if ($total==0) return; // there's no way to abort the walk()ing ..
  if ($s["class"]==$ccls) {

    foreach ($cats as $cat) {
      if ($counts[$cat] > 0) {
	$s["cat"]=$cat;
	$counts[$cat] --;
	$total--;
	break;
      }
    }
  } else if (($s["cat"]==CAT_NORMAL) && $s["class"]<$ncls) {
    $ncls = $s["class"];
  }
}

/** add category information (i.e. whether reduced or not reduced) to $seats

    Set the ["cat"] attribute of seats in $_SESSION according to the
    in-SESSION "ncatXYZ" attributes.

    If their sum is larger than the number of seats, the behaviour of
    this function depends on the $truncate parameter. If it is true,
    these two SESSION attributes are first modified to satisfy the
    constraint and a warning is emitted. If it is false they are silently
    truncated for the algorithm, but not modified in session, and no
    warning is emitted.
    
    Because I am nice to my users, and don't want to let clever people
    pay less than others, this algorithm allocates invitations and
    reduced places in a way that gives the lowest possible total.
    
    Invitations are allocated to the seats whose normal price is
    highest, and reduced tickets are allocated to those which have not
    yet been used as invitations, and such that the difference between
    normal and reduced rate is highest. 

    This assumes that reduced prices are monotonic with respect to
    normal prices. Otherwise we could have the following situation:

    Price table:

    Class   1 2
    Normal  9 8
    Reduced 1 7

    User takes one of each class, one reduced, one
    invitation. Allocating invitations first makes class 1 invitation,
    and reduced takes the remaining one, class 2, for a total of 7,
    even though the minimal price is 1.
*/
function compute_cats($truncate=false) {
  global $ccls,$ncls,$cats,$counts,$lang,$total; // made global so that seat_reduce sees them.
  $seatcount = count($_SESSION["seats"]);

  $cats = array_merge
    (array(CAT_REDUCED, CAT_NORMAL, CAT_FREE),
     do_hook_array('get_cats'));

  // counts as taken from SESSION. They will be decremented by
  // seat_reduce until they're both zero
  $counts = array();
  $total = 0; // the sum of everything in $counts.
  foreach ($cats as $cat) {
    $counts[$cat] =  isset($_SESSION["ncat$cat"])?
      $_SESSION["ncat$cat"] : 0;
    $total += $counts[$cat];
  }

  if ($total > $seatcount) {
    /* NOTE: duplicate code from pay.php!! */
    $skip = $total - $seatcount;
    $total = $seatcount;

    foreach ($cats as $cat) {
      if ($counts[$cat] > $skip) {
	$counts[$cat] -= $skip;
	break;
      } else {
	$skip -= $counts[$cat];
	$counts[$cat] = 0;
      }
    }
    
    if ($truncate) {
      foreach ($cats as $cat) {
	$_SESSION["ncat$cat"] = $counts[$cat];
      }
	kaboom($lang["warn-nonsensicalcat"]);
    }
  }

  $cats = array_reverse($cats); // seat_reduce wants to traverse from cheap to expensive...

  foreach ($_SESSION["seats"] as $n => $s)
    { $_SESSION["seats"][$n]["cat"]=CAT_NORMAL; } // default
  
  $ccls = 0; // current seat class
  while ($total > 0) {
    $ncls = 99; // next seat class
    array_walk($_SESSION["seats"],'seat_reduce'); 
    $ccls = $ncls;
  }
}

function freeseat_set_lock( $seatid, $showid ) {
	global $lockingtime;
	$name = implode( '-', array( $showid, $seatid ) );
	$sid = get_transient( $name );
	if ( ($sid === false ) || ( $sid == session_id() ) ) {
		// no lock or lock is ours
		$ok = set_transient( $name, session_id(), $lockingtime );
		return true;
	}
	return false;  // locked by someone else
}

function freeseat_get_lock( $seatid, $showid ) {
	$name = implode( '-', array( $showid, $seatid ) );
	$sid = get_transient( $name );
	if ( $sid === false ) return false;  // not locked or expired
	return true;  // locked by someone (maybe us)
}

function freeseat_delete_lock( $seatid, $showid ) {
	if ( freeseat_get_lock( $seatid, $showid ) ) {
		$name = implode( '-', array( $seatid, $showid ) );
		$sid = get_transient( $name );
		if ( $sid == session_id() ) {
			return delete_transient( $name );  // deleting lock
		} else {
			return false;  // locked by someone else
		} 
	} else {
		return true;  // nothing to do
	} 
}


