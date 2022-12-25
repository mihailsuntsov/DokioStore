<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://dokio.me/
 * @since      1.0.0
 *
 * @package    Dokio_Store
 * @subpackage Dokio_Store/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Dokio_Store
 * @subpackage Dokio_Store/public
 * @author     Mikhail Suntsov <mihail.suntsov@gmail.com>
 */
class Dokio_Store_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/dokio-store-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/dokio-store-public.js', array( 'jquery' ), $this->version, false );

	}

	public function helloworld(){
		// get the general settings options
		$API_address = get_option('API_address');
		$secret_key = get_option('secret_key');
		$woo_address = get_option('siteurl');
		$woo_consumer_key = get_option('woo_consumer_key');
		$woo_consumer_secret = get_option('woo_consumer_secret');

		return('API_address = ' . $API_address . '<br>secret_key = ' . $secret_key . '<br>woo_address = ' . $woo_address . '<br>woo_consumer_key = ' . $woo_consumer_key . '<br>woo_consumer_secret = ' . $woo_consumer_secret);
	}
}
