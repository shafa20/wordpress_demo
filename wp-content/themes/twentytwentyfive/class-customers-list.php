<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}


class Customers_List extends WP_List_Table {

    public function __construct() {
        parent::__construct( [
            'singular' => __( 'Customer', 'sp' ),
            'plural'   => __( 'Customers', 'sp' ),
            'ajax'     => false
        ] );
    }

	 // Checkbox column for bulk actions
	 function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID'] );
    }
    // Define the columns for the table
	function get_columns() {
        $columns = [
            'cb'      => '<input type="checkbox" />',
            'name'    => __( 'Name', 'sp' ),
            'address' => __( 'Address', 'sp' ),
            'city'    => __( 'City', 'sp' )
        ];

        return $columns;
    }

    // Fetch customer data from the database
	public static function get_customers( $per_page = 5, $page_number = 1, $search = '' ) {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}customers";

        if ( ! empty( $search ) ) {
            $sql .= $wpdb->prepare( " WHERE name LIKE %s", '%' . $wpdb->esc_like( $search ) . '%' );
        }

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
            $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
        }

        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

        return $wpdb->get_results( $sql, 'ARRAY_A' );
    }

    
	public static function delete_customer( $id ) {
		global $wpdb;
		
		$wpdb->delete(
		"{$wpdb->prefix}customers",
		[ 'ID' => $id ],
		[ '%d' ]
		);
		}

	public static function record_count() {
        global $wpdb;
        return $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}customers" );
    }
	public function no_items() {
        _e( 'No customers available.', 'sp' );
    }
	function column_name( $item ) {

		// create a nonce
		$delete_nonce = wp_create_nonce( 'sp_delete_customer' );
		
		$title = '<strong>' . $item['name'] . '</strong>';
		
		$actions = [
		'delete' => sprintf( '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce )
		];
		
		return $title . $this->row_actions( $actions );
		}
		public function get_bulk_actions() {
			$actions = [
			'bulk-delete' => 'Delete'
			];
			
			return $actions;
			}
    // Prepare the items to display
    public function prepare_items() {
		$this->_column_headers = $this->get_column_info();
        $this->process_bulk_action();
		$per_page = $this->get_items_per_page( 'customers_per_page', 5 );
        $current_page = $this->get_pagenum();
		 // Get search input
		$search = isset( $_REQUEST['s'] ) ? sanitize_text_field( $_REQUEST['s'] ) : '';
        $total_items = self::record_count();

        // Fetch data and record count
       
        //$total_items = self::record_count( $search );

        // Set pagination and column headers
        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ] );
		$this->items = self::get_customers( $per_page, $current_page, $search );
        $this->_column_headers = [ $this->get_columns(), [], $this->get_sortable_columns() ];
    }
	// Process bulk actions like deleting
	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {
		
		// In our file that handles the request, verify the nonce.
		$nonce = esc_attr( $_REQUEST['_wpnonce'] );
		
		if ( ! wp_verify_nonce( $nonce, 'sp_delete_customer' ) ) {
		die( 'Go get a life script kiddies' );
		}
		else {
		self::delete_customer( absint( $_GET['customer'] ) );
		
		wp_redirect( esc_url( add_query_arg() ) );
		exit;
		}
		
		}
		
		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		|| ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {
		
		$delete_ids = esc_sql( $_POST['bulk-delete'] );
		
		// loop over the array of record IDs and delete them
		foreach ( $delete_ids as $id ) {
		self::delete_customer( $id );
		
		}
		
		wp_redirect( esc_url( add_query_arg() ) );
		exit;
		}
		}

    // Define default column behavior
	public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'address':
            case 'city':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ); // For troubleshooting
        }
    }
    // Add sortable columns (optional)
	public function get_sortable_columns() {
        return [
            'name' => ['name', true],
            'city' => ['city', false]
        ];
    }

	 // Define bulk actions
	

    // Message to show when no data is available
   
}