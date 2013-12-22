<?php

function make_legend($numberedseats) {
// Construct a legend table showing only classes which exist in the seats table
// for the selected theatre, spectacle and zone, and display the normal 
// and reduced (if any) price in each box
  global $sh,$lang,$currency, $zone, $show_price;

	$criterion = ($numberedseats ? "row !=-1" : "row=-1" );

	// $cat_price = array(); // this is (was) just used to detect ...
                                 // ... whether price depends on class.

	$class_price = array(); // same, but for cat

	$q = "select distinct price.class, price.cat, amount from price, seats where spectacle='".$sh["spectacleid"];
	$q .= "' and theatre='".$sh["theatre"]."' and price.class=seats.class and zone='".mysql_real_escape_string($zone)."' and $criterion order by class";

	$prices= array();

	$show_price= true; // set to true if prices depend on class
	$show_cat  = false;  // set to true if prices depend on cat

	if ( $list = fetch_all( $q ) ) {
	  foreach ( $list as $item ) {
	    $prices[$item['class']][$item['cat']] = $item['amount'];

/* 	    if (!isset($cat_price[$item['cat']])) $cat_price[$item['cat']]=$item['amount']; */
/* 	    else if ($cat_price[$item['cat']]!=$item['amount']) $show_price=true; */

	    if (!isset($class_price[$item['class']])) $class_price[$item['class']]=$item['amount'];
	    else if ($class_price[$item['class']]!=$item['amount']) $show_cat=true;
	  }

	  echo "<h4><p class='main'>" . $lang[$numberedseats?"reserved-header":"nnseat-header"] . "</p></h4>";
	  
	  echo '<p class="main">' . $lang["legend"];

	  echo $lang[($show_price ? "diffprice" : "sameprice")];  
	  echo '</p><p class="main"><table border="1" cellpadding="5"><tr>';
	  if ($show_price && $show_cat) {
	    echo "<td align='center'>" . $lang["cat_normal"];
	    echo "<br>".$lang["cat_reduced"];	// don't display reduced prices if there aren't any
	  }

	  $class='default';

	  foreach ($prices as $class => $val) {

	    echo "<td class='cls$class' align='center'><p>";
	    if ($show_price) {
		echo $currency .' '. price_to_string($val[CAT_NORMAL]);
	      if ($show_cat) echo '<br>'.$currency .' '. price_to_string($val[((isset($val[CAT_REDUCED])) ? CAT_REDUCED : CAT_NORMAL)]);
	    } else {
	      echo $lang["class"].' '.$class;
	    }
	    echo "</p></td>";
	  }
	  /* don't display the free/occupied part for unnumbered seats */
	  if ($numberedseats) {
	    /* if there is only one class, use the color for that
	       class, otherwise show as orange */
	    if (count($prices)>1) $class = 'default';
	    echo "<td>".$lang["seat_occupied"]."</td><td class='cls$class' align='center'> 1 </td>";
	    echo "<td>".$lang["seat_free"]."</td><td class='cls$class' align='center'><input type='checkbox'><br> 2 </td>";
	  }
	  echo "</tr></table></p>";
	} // else : don't output anything if there are no seats...
}

  /** print a form to let user pick unnumbered seats in the given
      zone. proto maps class numbers to seat ids with matching class
      and zone. **/
function unseatcallback($cls, $cnt, $proto) {
  global $sh, $lang, $show_price, $zone;

    /* How many are still available */
    $nnav = $cnt - m_eval("select count(*) from booking,seats where showid=".$sh["id"]." and row=-1 and state!=".ST_DELETED." and booking.seat=seats.id and zone='".mysql_real_escape_string($zone)."' and class=$cls");

    // fetch comment field from class_comment table and display it in the text
    // if blank show class number
    $comment= m_eval("select comment from class_comment where class=$cls and spectacle=".$sh["spectacleid"]);
    if ($comment==null) $comment = $lang["class"] . " $cls";
    if ($nnav == 1)
      printf('<p class="main">'.$lang["nnseat-avail"], $comment);
    else // $nnav != 1
      printf('<p class="main">'.$lang["nnseats-avail"],$nnav, $comment);
    if (isset($_SESSION["nncnt-".$proto])) $nncnt = (int)$_SESSION["nncnt-".$proto];
    else $nncnt=0;

    if ($nnav > 0) {
      // The following test will almost never be triggered because
      // load_seats bounds the nncnt (with a warning to the user).  It
      // may be triggered if a seat is booked/locked by someone else
      // between the call to load_seats above and the initialisation of $nnav
      if ($nncnt>$nnav) $nncnt = $nnav;
      echo "<input class='cls$cls' name='nncnt-".$proto."' value='$nncnt'>";
    }
    echo '</p>';
}

/** make_legend for numbered seats */
function keycallback() {
  make_legend(true);
}
/** renders one seat. */
function seatcallback($currseat) {
  global $sh;
  // in-session selected seats have already been checked and are
  // locked - so no need to check their state
  if (is_seat_checked($currseat['id'])) {
    $chkd = true;
    $st = ST_FREE;
  } else {
    $chkd = false;
    $st = get_seat_state($currseat['id'],$sh['id']);
  }

  if ($st==ST_DISABLED)
    $colour = "clsdisabled";
  else
    $colour = "cls".$currseat['class'];
  // uses title attributes to display summary
  // if extra column = 'Table' then row is treated as a table #
  if (strpos($currseat['extra'], 'Table')===false) {
    $tbl = false;
    $text = "Row ".$currseat['row']." Seat ".$currseat['col'];
  } else {
    $tbl = true;
    $text = "Table ".$currseat["row"]."-".$currseat["col"];
  }
  echo "<td colspan='2' style='padding: 2px; ' class='$colour' title='$text'><p>";
  if (($st==ST_FREE) || ($st==ST_DELETED)) {
    // if extra column = 'Blank' then hide details
    if ( strpos( $currseat[ 'extra' ], 'Blank' ) === false ) {
      echo '<input type="checkbox" name="'.$currseat['id'].'"';
      if ($chkd) echo ' checked="checked"';
      echo '>';   // <br>';
    } else {
    	echo "&nbsp;";  // <br>";
    }
  }
  // if (!$tbl) echo $currseat['col'];
}

/* make_legend for unnumbered seats. */
function unkeycallback() {
  make_legend(false);
}

