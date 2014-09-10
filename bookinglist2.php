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

    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
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
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
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
            case 'name':
            	return $item['firstname'].' '.$item['lastname'];
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
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_bookid($item){
        
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
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['firstname'] .' '.$item['lastname'],
            /*$2%s*/ $item['bookid'],
            /*$3%s*/ $this->row_actions($actions)
        );
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
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'bookid'	=> 'ID',
            'date'		=> 'Date',
            'col'		=> 'Seat',
            'cat'		=> 'Rate',
            'name'		=> 'Name',
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
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
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
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete',
            'print'		=> 'Print',
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
		//Detect when a bulk action is being triggered...  FIXME
		if( 'delete'===$this->current_action() ) {
			start_notifs();
			foreach ( $_GET['booking'] as $book ) {
				set_book_status( $book, ST_DELETED );
			}
			send_notifs( ST_DELETED );
		}
		if( 'print'===$this->current_action() ) {
		
			if (count($ab) && isset($_POST["print"])) {
				show_head(true);
				do_hook('adminprint_process');  // process parameters from bookinglist
				$hide_tickets = do_hook_exists('ticket_prepare_override');
				foreach ($ab as $x) {
					do_hook_function('ticket_render_override', $x);
				}
				do_hook('ticket_finalise_override');
		
				if (!$hide_tickets) {
					do_hook('ticket_prepare');
					foreach ($ab as $x) {
						do_hook_function('ticket_render', $x);
					}
					do_hook('ticket_finalise');
				}
				// print_legal_info();
				$showid = $x['showid'];
				$bookinglist_url = admin_url( 'admin.php?page=freeseat-reservations' );
				echo "<p class='main'>";
				printf($lang['backto'],"[<a href='$bookinglist_url'>".$lang["link_bookinglist"]."</a>] ");
				echo "</p>";
				show_foot();
				exit;
			}		
		
		}
        if( 'extend'===$this->current_action() ) {
		    foreach ($_GET['booking'] as $booking) {
				$st = $booking['state'];
				$id = $booking['bookid'];
				if ($st == ST_SHAKEN || $st == ST_BOOKED) {
					$sql = "update booking set timestamp=NOW(), state=".ST_BOOKED." where id=$id";
        			if (!freeseat_query($sql)) myboom();
				}
			}        
        }
		if( 'confirm'===$this->current_action() ) {
			start_notifs();
			foreach ( $_GET['booking'] as $book ) {
				set_book_status( $book, ST_PAID );
			}
			send_notifs( ST_PAID );
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
        
        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
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
        
        $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc';
        $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id';
        $orderby .= " $order";
        /***********************************************************************
         * In a real-world situation, this is where you would place your query.
         **********************************************************************/
        $data = get_bookings( $cond, ( $orderby == "id" ? "bookid" : "$orderby,bookid" ), 0, $per_page );
                
        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */
        $current_page = $this->get_pagenum();
        
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = count($data);
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
}

/** ************************ REGISTER THE TEST PAGE ****************************
 *******************************************************************************
 * Now we just need to define an admin page. For this example, we'll add a top-level
 * menu item to the bottom of the admin menus.
 */
function freeseat_add_bookinglist_menu(){
    // add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
    add_submenu_page( 'freeseat-admin', 'Manage Bookings', 'Manage Bookings', 'administer_freeseat', 'freeseat-listtable', __NAMESPACE__ . '\\freeseat_render_list' );
} 

add_action('admin_menu', __NAMESPACE__ . '\\freeseat_add_bookinglist_menu');


/** *************************** RENDER TEST PAGE ********************************
 *******************************************************************************
 * This function renders the admin page and the example list table. Although it's
 * possible to call prepare_items() and display() from the constructor, there
 * are often times where you may need to include logic here between those steps,
 * so we've instead called those methods explicitly. It keeps things flexible, and
 * it's the way the list tables are used in the WordPress core.
 */
function freeseat_render_list() {
	global $filterst, $filtershow, $lang;
	$bookinglist_url = sprintf('?page=%s&action=%s',$_REQUEST['page'],'filter');
	?>
	<div class="wrap">
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
		echo '</div>';
		
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
    </div>
    <?php
}

