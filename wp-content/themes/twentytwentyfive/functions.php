<?php
/**
 * Twenty Twenty-Five functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_Five
 * @since Twenty Twenty-Five 1.0
 */

// Adds theme support for post formats.
if ( ! function_exists( 'twentytwentyfive_post_format_setup' ) ) :
	/**
	 * Adds theme support for post formats.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_post_format_setup() {
		add_theme_support( 'post-formats', array( 'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video' ) );
	}
endif;
add_action( 'after_setup_theme', 'twentytwentyfive_post_format_setup' );

// Enqueues editor-style.css in the editors.
if ( ! function_exists( 'twentytwentyfive_editor_style' ) ) :
	/**
	 * Enqueues editor-style.css in the editors.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_editor_style() {
		add_editor_style( get_parent_theme_file_uri( 'assets/css/editor-style.css' ) );
	}
endif;
add_action( 'after_setup_theme', 'twentytwentyfive_editor_style' );

// Enqueues style.css on the front.
if ( ! function_exists( 'twentytwentyfive_enqueue_styles' ) ) :
	/**
	 * Enqueues style.css on the front.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_enqueue_styles() {
		wp_enqueue_style(
			'twentytwentyfive-style',
			get_parent_theme_file_uri( 'style.css' ),
			array(),
			wp_get_theme()->get( 'Version' )
		);
	}
endif;
add_action( 'wp_enqueue_scripts', 'twentytwentyfive_enqueue_styles' );

// Registers custom block styles.
if ( ! function_exists( 'twentytwentyfive_block_styles' ) ) :
	/**
	 * Registers custom block styles.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_block_styles() {
		register_block_style(
			'core/list',
			array(
				'name'         => 'checkmark-list',
				'label'        => __( 'Checkmark', 'twentytwentyfive' ),
				'inline_style' => '
				ul.is-style-checkmark-list {
					list-style-type: "\2713";
				}

				ul.is-style-checkmark-list li {
					padding-inline-start: 1ch;
				}',
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_block_styles' );

// Registers pattern categories.
if ( ! function_exists( 'twentytwentyfive_pattern_categories' ) ) :
	/**
	 * Registers pattern categories.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_pattern_categories() {

		register_block_pattern_category(
			'twentytwentyfive_page',
			array(
				'label'       => __( 'Pages', 'twentytwentyfive' ),
				'description' => __( 'A collection of full page layouts.', 'twentytwentyfive' ),
			)
		);

		register_block_pattern_category(
			'twentytwentyfive_post-format',
			array(
				'label'       => __( 'Post formats', 'twentytwentyfive' ),
				'description' => __( 'A collection of post format patterns.', 'twentytwentyfive' ),
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_pattern_categories' );

// Registers block binding sources.
if ( ! function_exists( 'twentytwentyfive_register_block_bindings' ) ) :
	/**
	 * Registers the post format block binding source.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_register_block_bindings() {
		register_block_bindings_source(
			'twentytwentyfive/format',
			array(
				'label'              => _x( 'Post format name', 'Label for the block binding placeholder in the editor', 'twentytwentyfive' ),
				'get_value_callback' => 'twentytwentyfive_format_binding',
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_register_block_bindings' );



if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Users_Report_Table extends WP_List_Table {
    public function __construct() {
        parent::__construct([
            'singular' => 'user',    // Singular name for a single item
            'plural'   => 'users',   // Plural name for multiple items
            'ajax'     => false,     // Disable AJAX support
        ]);
    }

    // Define the columns for the table
    public function get_columns() {
        return [
            'id'              => 'ID',
            'user_login'      => 'Username',
            'user_email'      => 'Email',
            'user_registered' => 'Registered Date',
			'role'            => 'Role', // New column
        ];
    }

    // Prepare the items to display in the table
    public function prepare_items() {
        global $wpdb;
        $per_page = 10; // Number of items per page
        $current_page = $this->get_pagenum();

        // Query to fetch data from wp_users
        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}");
        $users = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT u.ID AS id, u.user_login, u.user_email, u.user_registered, 
                        (SELECT meta_value FROM {$wpdb->usermeta} 
                         WHERE user_id = u.ID AND meta_key = '{$wpdb->prefix}capabilities') AS role_meta
                 FROM {$wpdb->users} u
                 LIMIT %d OFFSET %d",
                $per_page,
                ($current_page - 1) * $per_page
            ),
            ARRAY_A
        );

		 // Parse the role from the serialized role_meta data
		 foreach ($users as &$user) {
            $roles = maybe_unserialize($user['role_meta']);
            $user['role'] = is_array($roles) ? implode(', ', array_keys($roles)) : 'None';
        }

        // Set column headers
        $columns = $this->get_columns();
        $hidden = []; // Hidden columns
        $sortable = $this->get_sortable_columns(); // Sortable columns

        $this->_column_headers = [$columns, $hidden, $sortable];

        // Set pagination and items
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ]);
        $this->items = $users;
    }

    // Define what data to display in each column
    public function column_default($item, $column_name) {
        return isset($item[$column_name]) ? esc_html($item[$column_name]) : '-';
    }

    // Add a sortable functionality (optional)
    public function get_sortable_columns() {
        return [
            'id'              => ['id', false],
            'user_login'      => ['user_login', false],
            'user_email'      => ['user_email', false],
            'user_registered' => ['user_registered', false],
			'role'            => ['role', false],
        ];
    }
}

// Add the admin menu
add_action('admin_menu', function() {
    add_menu_page(
        'Test Menu Page',    // Page title
        'Test Menu',         // Menu title
        'manage_options',    // Capability required
        'test-menu-slug',    // Menu slug
        function() {         // Callback function
            echo '<div class="wrap">';
            echo '<h1 class="wp-heading-inline">Users Report</h1>';
            echo '<hr class="wp-header-end">';

            // Create an instance of the custom table class
            $users_table = new Users_Report_Table();
            $users_table->prepare_items(); // Fetch data and pagination
            $users_table->display();       // Render the table

            echo '</div>';
        },
        'dashicons-admin-site', // Icon
        20                     // Position
    );
});

// Ensure WP_List_Table is available
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

    // Count the total number of records
    // public static function record_count( $search = '' ) {
    //     global $wpdb;

    //     $query = "SELECT COUNT(*) FROM {$wpdb->prefix}customers";
    //     if ( ! empty( $search ) ) {
    //         $query .= $wpdb->prepare( " WHERE name LIKE %s", '%' . $wpdb->esc_like( $search ) . '%' );
    //     }

    //     return $wpdb->get_var( $query );
    // }

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

// Add the menu to the admin dashboard
add_action( 'admin_menu', function() {
    add_menu_page(
        'Customers List',
        'Customers',
        'manage_options',
        'customers-list',
        function() {
            echo '<div class="wrap">';
            echo '<h1 class="wp-heading-inline">Customers</h1>';
            echo '<hr class="wp-header-end">';

            $customers_list = new Customers_List();
            $customers_list->prepare_items();
			echo '<form method="get">';
            echo '<input type="hidden" name="page" value="customers-list">';
            $customers_list->search_box( 'Search Customers', 'customer_search' ); // Add the search box
            $customers_list->display(); // Display the table
            echo '</form>';
			

            echo '</div>';
        },
        'dashicons-admin-users',
        20
    );
} );




// Registers block binding callback function for the post format name.
if ( ! function_exists( 'twentytwentyfive_format_binding' ) ) :
	/**
	 * Callback function for the post format name block binding source.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return string|void Post format name, or nothing if the format is 'standard'.
	 */
	function twentytwentyfive_format_binding() {
		$post_format_slug = get_post_format();

		if ( $post_format_slug && 'standard' !== $post_format_slug ) {
			return get_post_format_string( $post_format_slug );
		}
	}
endif;
