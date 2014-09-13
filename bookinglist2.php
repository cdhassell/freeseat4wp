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

    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     *
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
    	global $lang, $now;
    	
        switch($column_name){
        	case 'date':
        		return $item['date'].' '.f_time($item['time']);
            case 'col':
            	return ($item['row'] == -1 ? '' : $lang['col'].' '.$item['col'].', '.$lang["row"].' '.$item['row'].' ').'('.$item['zone'].')';
			case 'cat':
				$itemprice = get_seat_price( $item );
				return f_cat( $item[ 'cat' ] ) . " (" . price_to_string( $itemprice ) . ")" ;
			case 'email':
				return f_mail( $item[ 'email' ] );
			case 'phone':
                return $item[$column_name];
            case 'expiration':
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
						$delta = $exp - $now; // ($now=time() is in tools.php)
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
				// FIXME $html .= do_hook_concat( 'bookinglist_tablerow', $item );    
            	return $html;
            case 'bookid':
            	return $item['bookid'];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_name($item){
        
        //Build row actions
        $actions = array(
            'print'  => sprintf('<a href="?page=%s&action=%s&booking=%s">Print</a>',$_REQUEST['page'],'print',$item['bookid']),
            'delete' => sprintf('<a href="?page=%s&action=%s&booking=%s">Delete</a>',$_REQUEST['page'],'delete',$item['bookid'])
        );
        if ( $item['state'] == ST_BOOKED || $item['state'] == ST_SHAKEN ) {
			$actions['extend'] = sprintf('<a href="?page=%s&action=%s&booking=%s">Extend</a>',$_REQUEST['page'],'extend',$item['bookid']);
			$actions['confirm'] = sprintf('<a href="?page=%s&action=%s&booking=%s">Confirm</a>',$_REQUEST['page'],'confirm',$item['bookid']);
		}
        
        //Return the title contents
        return sprintf('%1$s %2$s', $item['firstname'] .' '.$item['lastname'], $this->row_actions($actions) );
    }

    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
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
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
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
            'expiration' => 'Expiration'
        );
        return $columns;
    }

    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
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
        $actions = array(
            'print'		=> 'Print',
            'delete'    => 'Delete',
            'extend'	=> 'Extend Expiration',
            'confirm'	=> 'Confirm Payment'
        );
        return $actions;
    }

    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
	function process_bulk_action() {        
		//Detect when a bulk action is being triggered... 
		if( 'print'===$this->current_action() ) {
			bookinglist_print($_GET['booking']);
		}
		if( 'delete'===$this->current_action() ) {
			bookinglist_delete($_GET['booking']);
		}
        if( 'extend'===$this->current_action() ) {
        	bookinglist_extend($_GET['booking']);
        }
		if( 'confirm'===$this->current_action() ) {
			bookinglist_confirm($_GET['booking']);
        }
		// FIXME do_hook( 'bookinglist_process' );
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
    function prepare_items() {
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
		$and  = ""; // set to "and" once $cond is non empty
		if ( $filtershow )
			$cond = "showid=$filtershow and";
		else if ( isset($fulllist) && !empty($fulllist) )
			$cond = "showid IN ($fulllist) and";
		else
			$cond = " ";
		
		switch ( $filterst ) {
			case ST_BOOKED:
				$cond .= " $and (state=" . ST_BOOKED . " or state=" . ST_SHAKEN . ")";
				$and = "and";
				break;
			case ST_PAID:
			case ST_DELETED:
			case ST_DISABLED:
				$cond .= " $and state=$filterst";
				$and = "and";
				break;
			case 0:
				$cond .= " $and (state=" . ST_BOOKED . " or state=" . ST_SHAKEN . " or state=" . ST_PAID . " or state=" . ST_DELETED . ")";
				$and = "and";
				break;
			default: //  -ST_DELETED
				$cond .= " $and (state=" . ST_BOOKED . " or state=" . ST_SHAKEN . " or state=" . ST_PAID . ")";
				$and = "and";
		}
        
        $orderby = (isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'id' ).' '.
        (isset($_REQUEST['order']) ? $_REQUEST['order'] : 'asc');
        $data = get_bookings( $cond, ( $orderby == "id" ? "bookid" : "$orderby,bookid" ), 0, $per_page );
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

/**************************** REGISTER THE PAGE ****************************
 ********************************************************************************/
function freeseat_add_bookinglist_menu(){
    // add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
    add_submenu_page( 'freeseat-admin', 'Manage Bookings', 'Manage Bookings', 'administer_freeseat', 'freeseat-listtable', __NAMESPACE__ . '\\freeseat_render_list' );
} 

add_action('admin_menu', __NAMESPACE__ . '\\freeseat_add_bookinglist_menu');


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
		}
	}
	// start the main page with select boxes for booking status and showid
	$bookinglist_url = sprintf('?page=%s&action=%s',$_REQUEST['page'],'filter');
	?>
	<div class="wrap"><div id="freeseat-wide">
		<h2>Manage Bookings</h2>        
		<form action="<?php echo $bookinglist_url; ?>" method="POST" name="filterform">
			<?php if (function_exists('wp_nonce_field')) wp_nonce_field('freeseat-bookinglist-filterform'); ?>
			<p class="main"><?php echo $lang[ "filter" ]; ?>
			<select name="st" onchange="filterform.submit();">
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
			echo '<select name="showid" onchange="filterform.submit();">';
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
		} else
			echo mysql_error();
		echo ' <input class="button button-primary" type="submit" value="' . $lang[ "update" ] . '"></form>';
		
		// now create the WP_List_Table object
		$ListTable = new freeseat_list_table();
		$ListTable->prepare_items();
    ?>
    <div id="icon-users" class="icon32"><br/></div>
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="bookings-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $ListTable->display() ?>
        </form>
    </div></div>
    <?php
}

function bookinglist_print($list) {
	global $lang, $page_url;
	// handle a request to print one or more reservations
	// creates a page for the print routine and then exits
	
	$bookings = array();
	if (!empty($list)) {
		if (!is_array($list)) { $list = array($list); }
		foreach ($list as $bookid) {
			$booking = get_booking($bookid);
			$bookings[$booking['seat']] = $booking;
			$_SESSION['firstname'] = $booking['firstname'];
			$_SESSION['lastname'] = $booking['lastname'];
			$_SESSION['payment'] = $booking['payment'];
		}
		$_SESSION['seats'] = $bookings;
	} else {
		$bookings = $_SESSION['seats'];
	}
	$page_url = admin_url( 'admin.php?action=print&page='.$_REQUEST['page'] );
	show_head(true);
	do_hook('adminprint_process');  // process parameters 
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
	$showid = $x['showid'];
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
			$sql = "update booking set timestamp=NOW(), state=".ST_BOOKED." where id=$id";
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

