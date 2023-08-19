<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://dokio.me/
 * @since             1.1.1
 * @package           Dokio_Store
 *
 * @wordpress-plugin
 * Plugin Name:       DokioStore
 * Plugin URI:        https://dokio.me/
 * Description:       This plugin is designed to synchronize products, categories, attributes from DokioCRM to WooCommerce and orders from WooCommerce to DokioCRM.
 * Version:           1.3.0-1
 * Author:            Mikhail Suntsov
 * Author URI:        https://dokio.me/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       dokio-store
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'DOKIO_STORE_VERSION', '1.3.0-1' );


// echo json_encode($woocommerce->get('orders'));
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-dokio-store-activator.php
 */
function activate_dokio_store() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-dokio-store-activator.php';
	Dokio_Store_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-dokio-store-deactivator.php
 */
function deactivate_dokio_store() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-dokio-store-deactivator.php';
	Dokio_Store_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_dokio_store' );
register_deactivation_hook( __FILE__, 'deactivate_dokio_store' );


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-dokio-store.php';



require plugin_dir_path( __FILE__ ) . '/interactions/automatic/crontasks.php';


require plugin_dir_path( __FILE__ ) . '/interactions/additional/annasta_filter.php';

 require plugin_dir_path( __FILE__ ) . '/interactions/taxes.php';
 require plugin_dir_path( __FILE__ ) . '/interactions/test_woo.php';
 require plugin_dir_path( __FILE__ ) . '/interactions/c_taxes.php';
 require plugin_dir_path( __FILE__ ) . '/interactions/c_categories.php';
 require plugin_dir_path( __FILE__ ) . '/interactions/c_attributes.php';
 require plugin_dir_path( __FILE__ ) . '/interactions/c_orders.php';
 require plugin_dir_path( __FILE__ ) . '/interactions/c_terms.php';
 require plugin_dir_path( __FILE__ ) . '/interactions/c_products.php';
 require plugin_dir_path( __FILE__ ) . '/interactions/c_variations.php';
 require plugin_dir_path( __FILE__ ) . '/interactions/ajax/ajax.php';
 require plugin_dir_path( __FILE__ ) . '/logger/logger.php';


function github16702_allow_unsafe_urls($args, $url) {
    $args['reject_unsafe_urls'] = false;
    return $args;
}
add_filter('http_request_args', 'github16702_allow_unsafe_urls', 20, 2 );

function add_dokiocrm_intervals( $schedules ) {
	// add a 'Every 1 minute' schedule to the existing set
	$schedules['every_1_minute'] = array(
		'interval' => 60,
		'display' => __('Every 1 minute (CRM)')
	);
	$schedules['every_60s'] = array(
		'interval' => 61,
		'display' => __('Every 61 S (CRM)')
	);
	// $schedules['every_5_seconds'] = array(
	// 	'interval' => 5,
	// 	'display' => __('Every 5 seconds test (CRM)')
	// );	
	return $schedules;
}
add_filter( 'cron_schedules', 'add_dokiocrm_intervals');

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_dokio_store() {

	$plugin = new Dokio_Store();
	$plugin->run();
}
run_dokio_store();
