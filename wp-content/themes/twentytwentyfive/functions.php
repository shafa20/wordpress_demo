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


// Include the Customers_List class file
require_once get_template_directory() . '/class-customers-list.php';

// Add the menu to the admin dashboard
// add_action( 'admin_menu', function() {
//     add_menu_page(
//         'Customers List',
//         'Customers',
//         'manage_options',
//         'customers-list',
//         function() {
//             echo '<div class="wrap">';
//             echo '<h1 class="wp-heading-inline">Customers</h1>';
//             echo '<hr class="wp-header-end">';

//             $customers_list = new Customers_List();
//             $customers_list->prepare_items();
//             echo '<form method="get">';
//             echo '<input type="hidden" name="page" value="customers-list">';
//             $customers_list->search_box( 'Search Customers', 'customer_search' );
//             $customers_list->display();
//             echo '</form>';

//             echo '</div>';
//         },
//         'dashicons-admin-users',
//         20
//     );
// } );

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
