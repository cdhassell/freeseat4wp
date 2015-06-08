<?php namespace freeseat;

/*  Copyright 2014  Matthew Van Andel  (email : matt@mattvanandel.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class freeseat_list_table extends \WP_List_Table {
	
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'booking',     //singular name of the listed records
            'plural'    => 'bookings',    //plural name of the listed records
            'ajax'      => false          //does this table support ajax?
        ) );
        
    }
    
    function column_date($item) {
 		return $item['date'].' '.f_time($item['time']);
    }
	
	function column_col($item) {
		global $lang;
 		return ($item['row'] == -1 ? '' : $lang['col'].' '.$item['col'].', '.$lang["row"].' '.$item['row'].' ').'('.$item['zone'].')';
    }

	function column_cat($item) {
		$itemprice = get_seat_price( $item );
		return f_cat( $item[ 'cat' ] ) . " (" . price_to_string( $itemprice ) . ")" ;
    }
    
	function column_email($item) {
		return f_mail( $item[ 'email' ] );
	}
	
	function column_state($item) {
		if ( $item['state'] == ST_BOOKED || $item['state'] == ST_SHAKEN ) {
			if (admin_mode()) {
				$actions = array( 'confirm' => sprintf('<a href="?page=%s&action=%s&booking=%s">Paid</a>',$_REQUEST['page'],'confirm',$item['bookid']));
			} else {
				$actions = array( 'pay' => sprintf('<a href="?page=%s&action=%s&booking=%s">Pay</a>',$_REQUEST['page'],'pay',$item['bookid']));
			}
			return f_state( $item[ 'state' ] ) .' '. $this->row_actions($actions);
		} else {
			return f_state( $item[ 'state' ] );
		}
	}
	
	function column_phone($item) {
		return $item['phone'];
	}	
	
	function column_bookid($item) {
		return $item['bookid'];
	}
	
	function column_expiration($item) {
		global $lang, $now;
		$st = $item[ 'state' ];
		$paydelay_ccard = get_config('paydelay_ccard');
		$paydelay_post = get_config('paydelay_post');
		$exp = TRUE;
		if ( ( $st == ST_BOOKED ) || ( $st == ST_SHAKEN ) ) {
			if ( $item[ 'payment' ] == PAY_CCARD )
				$exp = strtotime( $item[ 'timestamp' ] ) + 86400 * $paydelay_ccard;
			else if ( $item[ 'payment' ] == PAY_POSTAL )
				$exp = sub_open_time( strtotime( $item[ 'timestamp' ] ), -86400 * $paydelay_post );
			else {
				$exp = FALSE;
				$html = '<i>' . $lang[ "none" ] . '</i>';
			}
			if ( $exp !== FALSE ) {
				$delta = $exp - $now; 
				if ( $delta < 0 )
					$html = $lang[ "expired" ];
				else if ( $delta < 5400 )
					$html = sprintf( $lang[ "in" ], ( (int) ( $delta / 60 ) ) . ' ' . $lang[ "minute" ] );
				else if ( $delta < 129600 )
					$html = sprintf( $lang[ "in" ], ( (int) ( $delta / 3600 ) ) . ' ' . $lang[ "hour" ] );
				else
					$html = sprintf( $lang[ "in" ], ( (int) ( $delta / 86400 ) ) . ' ' . $lang[ "day" ] );
			}
		} else $html = '<i>' . $lang[ "none" ] . '</i>';			
		if (admin_mode() && ( $item['state'] == ST_BOOKED || $item['state'] == ST_SHAKEN )) {
			$actions = array( 'extend' => sprintf('<a href="?page=%s&action=%s&booking=%s">Extend</a>', $_REQUEST['page'],'extend', $item['bookid']) );
			$html .= $this->row_actions($actions);
		}
    	return $html;
	}	
	
	/** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. 
     *
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        return $item[$column_name];
    }

    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'name'.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_name($item){
        //Build row actions
        $actions = array(
            'print'  => sprintf('<a class="freeseat-print" href="?page=%s&action=%s&booking=%s">Print</a>',$_REQUEST['page'],'print',$item['bookid']),
            'delete' => sprintf('<a href="?page=%s&action=%s&booking=%s">Delete</a>',$_REQUEST['page'],'delete',$item['bookid'])
        );
        //Return the title contents
        return sprintf('%1$s %2$s', $item['firstname'] .' '.$item['lastname'], $this->row_actions($actions) );
    }

    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['bookid']            //The value of the checkbox should be the record's id
        );
    }

    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'bookid'	=> 'ID',
            'name'		=> 'Name',
            'date'		=> 'Date',
            'col'		=> 'Seat',
            'cat'		=> 'Rate',
            'email'		=> 'Email',
            'phone'		=> 'Phone',
            'state'		=> 'Status',
            'expiration' => 'Expiration'
        );
        return $columns;
    }

    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. 
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'bookid'	=> array('bookid',false),     //true means it's already sorted
            'name' 		=> array('name',false),
            'email'		=> array('email',false)
        );
        return $sortable_columns;
    }

    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        if (admin_mode()) {
            $actions = array(
    	        'print'		=> 'Print',
        	    'delete'    => 'Delete',
            	'extend'	=> 'Extend Expiration',
            	'confirm'	=> 'Confirm Payment',
        	);
		} else {
        	$actions = array(
	            'print'		=> 'Print',
            	'pay'		=> 'Make Payment'
        	);
        }
        return $actions;
    }

    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
	function process_bulk_action() {        
		//Detect when a bulk action is being triggered... 
		if( 'print' ===  $this->current_action() ) {
			bookinglist_print($_GET['booking']);
		}
		if( 'delete' === $this->current_action() ) {
			bookinglist_delete($_GET['booking']);
		}
        if( 'extend' === $this->current_action() ) {
        	bookinglist_extend($_GET['booking']);
        }
		if( 'confirm'=== $this->current_action() ) {
			bookinglist_confirm($_GET['booking']);
        }
		if( 'pay'  ===   $this->current_action() ) {
			bookinglist_pay($_GET['booking']);
		}
    }

    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items($search = NULL) {
        global $wpdb, $bookings_on_a_page; //This is used only if making any database queries

        $per_page = $bookings_on_a_page;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
       
		/** BUILD QUERY ACCORDING TO filter settings **/
		if ( isset( $_REQUEST[ "showid" ] ) )
			$filtershow = (int) ( $_REQUEST[ "showid" ] );
		else
			$filtershow = null;
			
		/* valid values for $filterst are 0 (means show everything)
		 * -ST_DELETED (means everything except deleted)
		 * ST_BOOKED (means booked or shaken but not paid)
		 * ST_DELETED (means show only deleted)
		 * ST_DISABLED (means not available)
		 * ST_PAID (means paid) */
		if ( isset( $_REQUEST[ "st" ] ) )
			$filterst = (int) ( $_REQUEST[ "st" ] );
		else
			$filterst = -ST_DELETED;		
		
		$cond = "";
		if ( $filtershow )
			$cond = "showid=$filtershow ";
		else if ( isset($fulllist) && !empty($fulllist) )
			$cond = "showid IN ($fulllist) ";
		else
			$cond = "booking.showid=shows.id ";

		if (!admin_mode()) {
			$user = get_current_user_id();
			$cond .= "and user_id=$user ";
		}
		
		switch ( $filterst ) {
			case ST_BOOKED:
				$cond .= "and (state=" . ST_BOOKED . " or state=" . ST_SHAKEN . ") ";
				break;
			case ST_PAID:
			case ST_DELETED:
			case ST_DISABLED:
				$cond .= "and state=$filterst ";
				break;
			case 0:
				$cond .= "and (state=" . ST_BOOKED . " or state=" . ST_SHAKEN . " or state=" . ST_PAID . " or state=" . ST_DELETED . ") ";
				break;
			default: //  -ST_DELETED
				$cond .= "and (state=" . ST_BOOKED . " or state=" . ST_SHAKEN . " or state=" . ST_PAID . ") ";
		}
		$orderby = (isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'id' ).' '. (isset($_REQUEST['order']) ? $_REQUEST['order'] : 'asc');
		
		// handle the search box
    	if( $search != NULL ){
        	$search = trim($search);
        	$and = ($cond ? " and" : "" );
        	$cond .= "$and `lastname` LIKE '%%%s%%' OR `firstname` LIKE '%%%s%%'";
			// get_bookings query is duplicated here so search terms can be sanitized :-p
			if ($cond) $cond = "( $cond ) and";
			if ($orderby) $orderby = "ORDER BY " . ($orderby == "id" ? "bookid" : "$orderby,bookid" );
			$sql = "SELECT booking.id as bookid, booking.*, seat, seats.row, seats.col, seats.extra, seats.zone, seats.class, showid, shows.date, shows.time, shows.spectacle as spectacleid, theatres.name as theatrename, theatres.id as theatreid, seats.x, seats.y FROM booking, shows, seats, theatres WHERE $cond booking.seat = seats.id and booking.showid=shows.id and shows.theatre = theatres.id $orderby"; // LIMIT $per_page OFFSET 0";
			$sql = fs2wp( $sql );
			$data = $wpdb->get_results($wpdb->prepare( $sql, $search, $search), ARRAY_A);
		} else{
        	$data = get_bookings( $cond, ( $orderby == "id" ? "bookid" : "$orderby,bookid" )); // , 0, $per_page );
		}

        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        $this->items = $data;
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
}

/******************************* RENDER THE PAGE ********************************
 ********************************************************************************/
function freeseat_render_list() {
	global $filterst, $filtershow, $lang;
	
	// if user clicked a row action link, deal with it here
	if (isset($_REQUEST['action'])) {
		switch ($_REQUEST['action']) {
			case 'print':
				bookinglist_print((isset($_REQUEST['booking']) ? $_REQUEST['booking'] : NULL));
				break;
			case 'delete':
				bookinglist_delete($_REQUEST['booking']);
				break;
			case 'extend':
				bookinglist_extend($_REQUEST['booking']);
				break;
			case 'confirm':
				bookinglist_confirm($_REQUEST['booking']);
				break;
			case 'pay':
				bookinglist_pay($_REQUEST['booking']);
				break;
		}
	}
	// start the main page with select boxes for booking status and showid
	$bookinglist_url = sprintf('?page=%s&action=%s',$_REQUEST['page'],'filter');
	?>
	<div class="wrap">
		<div id="freeseat-wide">
		<?php if (!admin_mode()) { ?>
			<h2>
				<?php echo $lang['login_recent_purchases']; ?>
			</h2>
			<p class="main">
				<?php echo $lang['login_greeting']; ?>
			</p>
		<?php } else { ?>
			<h2><?php echo $lang['manage_bookings']; ?></h2>
		<?php } ?>
		<form action="<?php echo $bookinglist_url; ?>" method="POST" name="filterform">
			<?php if (function_exists('wp_nonce_field')) wp_nonce_field('freeseat-bookinglist-filterform'); ?>
			<p class="main"><?php if (admin_mode()) echo $lang[ "filter" ]; ?>
			<select name="st" onchange="filterform.submit();"
			<?php if (!admin_mode()) echo " style='display:none;' "; ?>	>
				<?php
				foreach ( array(
					 -ST_DELETED => "st_notdeleted",
					ST_BOOKED => "st_tobepaid",
					ST_PAID => "st_paid",
					ST_DELETED => "st_deleted",
					ST_DISABLED => "st_disabled",
					0 => "st_any" 
				) as $opt => $lab ) {
					echo '<option value="' . $opt . '" ';
					if ( $filterst == $opt )
						echo "selected ";
					echo '>' . $lang[ $lab ] . '</option>';
				}
				?>
			</select>
			<?php
			// limit this list to shows no more than a week ago
			// it prevents trying to summarize the entire database 
			$ss = get_shows( "date >= CURDATE() - INTERVAL 1 week" );
			if ( $ss ) {
				echo '<select name="showid" onchange="filterform.submit();"';
				if (!admin_mode()) echo " style='display:none;' "; 
				echo '>';
				echo '<option value="">' . $lang[ "show_any" ] . '</option>';
				$fulllist = $comma = '';
				foreach ( $ss as $sh ) {
					echo '<option value="' . $sh[ "id" ] . '"';
					if ( $filtershow == $sh[ "id" ] )
						echo 'selected >';
					else
						echo '>';
					show_show_info( $sh, false );
					echo '</option>';
					$fulllist .= $comma . $sh[ 'id' ];
					$comma = ', ';
				}
				echo '</select> ';
			} else {
				echo mysql_error();
			}
			echo ' <input class="button button-primary" type="submit" value="'.$lang[ "update" ].'"';
			if (!admin_mode()) echo ' style="display:none;" '; 
			echo '></form>';
			// now create the WP_List_Table object
			$ListTable = new freeseat_list_table();
				
			//Fetch, prepare, sort, and filter our data...
			if( isset($_POST['s']) ){
				$ListTable->prepare_items($_POST['s']);
			} else {
				$ListTable->prepare_items();
			}
				
			?>
			<div id="icon-users" class="icon32"><br/></div>
			<!-- Form to create a search box -->
			<?php if (admin_mode()) { ?>
				<form method="post">
					<?php $ListTable->search_box('Search by name', 'name'); ?>
					<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				</form>
			<?php } ?>
			<!-- Wrap the table in a form to use features like bulk actions -->
			<form id="bookings-filter" method="get">
				<?php do_hook( 'bookinglist_line' ); ?>
				<!-- For plugins, we also need to ensure that the form posts back to our current page -->
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<!-- Now we can render the completed list table -->
				<?php $ListTable->display() ?>
			</form>
		</div>
	</div>
	<?php
}

function bookinglist_print($list) {
	global $lang, $page_url;
	// handle a request to print one or more reservations
	// creates a page for the print routine and then exits
	do_hook('bookinglist_process');  // process parameters 	
	$bookings = array();
	if (!is_array($list)) { $list = array($list); }
	foreach ($list as $bookid) {
		$bookings[] = get_booking($bookid);
		if (!isset($gid)) $gid = $bookid;
	}
	bookinglist_setup_session( $bookings, $gid );
	$page_url = $_SERVER['PHP_SELF'];
	
	show_head(true);
	$hide_tickets = do_hook_exists('ticket_prepare_override');
	foreach ($bookings as $x) {
		do_hook_function('ticket_render_override', $x);
	}
	do_hook('ticket_finalise_override');
	if (!$hide_tickets) {
		do_hook('ticket_prepare');
		foreach ($bookings as $x) {
			do_hook_function('ticket_render', $x);
		}
		do_hook('ticket_finalise');
	}
	$bookinglist_url = sprintf('?page=%s',$_REQUEST['page']);
	echo "<p class='main'>";
	printf($lang['backto'],"[<a href='$bookinglist_url'>".$lang["link_bookinglist"]."</a>] ");
	echo "</p>";
	show_foot();
	exit;
}

function bookinglist_delete($list) {
	// handle a request to delete one or more reservations
	$bookings = array();
	if (!is_array($list)) { $list = array($list); }
	foreach ($list as $bookid) {
		$bookings[] = get_booking($bookid);
	}
	start_notifs();
	foreach ( $bookings as $booking ) {
		set_book_status( $booking, ST_DELETED );
	}
	send_notifs( ST_DELETED );	
}

function bookinglist_extend($list) {
	// handle a request to extend one or more unpaid reservations
	$bookings = array();
	if (!is_array($list)) { $list = array($list); }
	foreach ($list as $bookid) {
		$bookings[] = get_booking($bookid);
	}
	foreach ($bookings as $booking) {
		$st = $booking['state'];
		$id = $booking['bookid'];
		if ($st == ST_SHAKEN || $st == ST_BOOKED) {
			$extend_date = date("Y-m-d H:i:s",time()+86400*4);
			$sql = "update booking set timestamp='$extend_date', state=".ST_BOOKED." where id=$id";
			if (!freeseat_query($sql)) myboom();
		}
	}
}

function bookinglist_confirm($list) {
	// handle a request to confirm one or more reservation payments
	$bookings = array();
	if (!is_array($list)) { $list = array($list); }
	foreach ($list as $bookid) {
		$bookings[] = get_booking($bookid);
	}
	start_notifs();
	foreach ( $bookings as $booking ) {
		set_book_status( $booking, ST_PAID );
	}
	send_notifs( ST_PAID );
}

function bookinglist_pay($list) {
	// handle a request to confirm one or more reservation payments
	global $lang, $page_url;
	
	$bookings = array();
	if (!is_array($list)) { $list = array($list); }
	foreach ($list as $bookid) {
		$bookings[] = get_booking($bookid);
		if (!isset($gid)) $gid = $bookid;
	}

	if ( isset($_SESSION["lastname"]) && !empty($_SESSION["lastname"]) && 
	isset($_SESSION["firstname"]) && !empty($_SESSION["firstname"]) && 
	isset($_SESSION["email"]) && !empty($_SESSION["email"]) && is_email_ok($_SESSION["email"])) {
		// we are ready to confirm and go
		$default_fsp = PAGE_CONFIRM;
	} else {
		// need user details, go back 
		$default_fsp = PAGE_PAY;
	}
	$fsp = ( isset( $_REQUEST['fsp'] ) ? $_REQUEST['fsp'] : $default_fsp );
	$page_url = $_SERVER['PHP_SELF'];
	$page_url = add_query_arg( array( 'page' => 'freeseat-user-menu' ), $page_url ); 
	$page_url = replace_fsp( $page_url, $fsp );
	bookinglist_setup_session( $bookings, $gid );
	freeseat_switch( $fsp );
	exit;
}

/** 
 *  Set up SESSION vars based on an array of bookings from the database
 */
function bookinglist_setup_session( $bookings, $gid ) {
	global $lockingtime;
		
	$seats = array();
	$ninvite = 0; 
	$nreduced = 0;
	$_SESSION["until"] = time()+$lockingtime;
	$_SESSION["showid"] = $bookings[0]["showid"];
	foreach (array("firstname","lastname","phone","email", "payment", "address", "city", "us_state", "postalcode") as $n => $a) {
		if (isset($bookings[0][$a])) $_SESSION[$a] = sanitize_text_field($bookings[0][$a]);
	}
	foreach ($bookings as $i => $data ) {
		$code = "carts{$data['showid']}s{$data['seat']}";
		$seats[$code] = array( "id" => $data['seat'], "theatre" => $data["theatreid"], "cnt" => 1 );
		foreach ( array("bookid", "row", "col", "extra", "zone", "class", "cat", "date", "time", "theatrename", "spectacleid", "showid", "x", "y" ) as $n => $a) {
			if (isset($data[$a]))  $seats[$code][$a] = $data[$a];
		}
		if ($data['cat']==CAT_REDUCED) $nreduced++;
		if ($data['cat']==CAT_FREE) $ninvite++; 
	}
	$_SESSION["seats"] = $seats;
	$_SESSION["groupid"] = $gid;
	$_SESSION["ninvite"] = $ninvite;
	$_SESSION["nreduced"] = $nreduced;
	if (!isset($_SESSION["payment"])) $_SESSION["payment"]= PAY_CCARD;
}
