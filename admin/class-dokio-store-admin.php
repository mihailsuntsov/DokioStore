<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://dokio.me/
 * @since      1.0.0
 *
 * @package    Dokio_Store
 * @subpackage Dokio_Store/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Dokio_Store
 * @subpackage Dokio_Store/admin
 * @author     Mikhail Suntsov <mihail.suntsov@gmail.com>
 */
class Dokio_Store_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Dokio_Store_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Dokio_Store_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/dokio-store-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'bootstrap-css', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Dokio_Store_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Dokio_Store_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/dokio-store-admin.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'bootstrap-js', plugin_dir_url( __FILE__ ) . 'js/bootstrap.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'bootstrap-growl', plugin_dir_url( __FILE__ ) . 'js/jquery.bootstrap-growl.min.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Add a custom menu
	 *
	 * @since    1.0.0
	 */
	public function admin_menu(){
		add_menu_page('DokioStore Settings','DokioStore','manage_options','dokiostore/ds-mainsettings.php',array($this, 'plugin_admin_page'),'dashicons-tickets', 250 );
		// add_submenu_page('dokiostore/ds-mainsettings.php','My Sub level menu example','Sub Level Menu','manage_options','dokiostore/ds-importer.php', array($this, 'plugin_admin_sub_page'), 1 );
	}
	
	public function plugin_admin_page(){
		// return views
		require_once 'partials/dokio-store-admin-display.php';
	}

	public function plugin_admin_sub_page(){
		// return subpage views
		require_once 'partials/submenu-page.php';
	}

	/**
	 * Register custom fields for plugin settings
	 *
	 * @since    1.0.0
	 */
	public function register_general_settings(){
		register_setting( 'ds_custom_settitgs', 'API_address' );
		register_setting( 'ds_custom_settitgs', 'secret_key' );
		register_setting( 'ds_custom_settitgs', 'woo_address' );
		register_setting( 'ds_custom_settitgs', 'woo_consumer_key' );
		register_setting( 'ds_custom_settitgs', 'woo_consumer_secret' );
		register_setting( 'ds_custom_settitgs', 'save_crm_taxes' );
	}

	public function add_product(){
		$data = [
			'name' => 'Premium Quality',
			'type' => 'simple',
			'regular_price' => '999.99',
			'description' => 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.',
			'short_description' => 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.',
			'categories' => [
				[
					'id' => 9
				],
				[
					'id' => 14
				]
			],
			'images' => [
				[
					'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg'
				],
				[
					'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_back.jpg'
				]
			]
		];
		
		print_r($woocommerce->post('products', $data));
	}



	//this creates custom post type for videos
	/*public function custom_youtube_api(){
		//
		// Creating a function to create our CPT
		//
		$labels = array(
			'name'                => _x( 'YouTube Videos', 'Post Type General Name'),
			'singular_name'       => _x( 'YouTube Video', 'Post Type Singular Name'),
			'menu_name'           => __( 'YouTube Video'),
			'parent_item_colon'   => __( 'Parent Video'),
			'all_items'           => __( 'All Videos'),
			'view_item'           => __( 'View Videos'),
			'add_new_item'        => __( 'Add New YouTube Video'),
			'add_new'             => __( 'Add New'),
			'edit_item'           => __( 'Edit'),
			'update_item'         => __( 'Update'),
			'search_items'        => __( 'Search'),
			'not_found'           => __( 'Not Found'),
			'not_found_in_trash'  => __( 'Not found in Trash'),
		);
		
		// Set other options for Custom Post Type
		
		$args = array(
			'label'               => __( 'wp10yvids'),
			'description'         => __( 'YouTube Videos from our Channel'),
			'labels'              => $labels,
			// Features this CPT supports in Post Editor
			'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
			// You can associate this CPT with a taxonomy or custom taxonomy. 
			'taxonomies'          => array( 'genres' ),
			// A hierarchical CPT is like Pages and can have
			// Parent and child items. A non-hierarchical CPT
			// is like Posts.
			// 
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => false,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'can_export'          => false,
			'has_archive'         => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'show_in_rest' 		  => true,
	
		);
		
		// Registering your Custom Post Type
		register_post_type( 'wp10yvids', $args );
	}*/

	
	

}
