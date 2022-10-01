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
 * @since             1.0.0
 * @package           Dokio_Store
 *
 * @wordpress-plugin
 * Plugin Name:       DokioStore
 * Plugin URI:        https://dokio.me/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
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
define( 'DOKIO_STORE_VERSION', '1.0.0' );





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

// require plugin_dir_path( __FILE__ ) . '/admin/partials/tutsplus-actions.php';

// require plugin_dir_path( __FILE__ ) . '/interactions/test.php';


// function tutsplus_action() {
// 	do_action( 'tutsplus_action' );
//   }
  
  /**
   * Register the action with WordPress.
   */
//  add_action( 'tutsplus_action', 'tutsplus_action_example' );
//  function tutsplus_action_example() {
//    echo 'This is a custom action hook - 1.';
//  }



// require __DIR__ . '/vendor/autoload.php';

// use Automattic\WooCommerce\Client;
// use Automattic\WooCommerce\HttpClient\HttpClientException;

//   $woocommerce = new Client(
//   get_option('woo_address'),
//   get_option('woo_consumer_key'),
//   get_option('woo_consumer_secret'),
//   get_option('API_address'),
//   get_option('secret_key'),
//   [
//     'version' => 'wc/v3',
//   ]
// );


 require plugin_dir_path( __FILE__ ) . '/interactions/taxes.php';
 require plugin_dir_path( __FILE__ ) . '/interactions/c_taxes.php';
 require plugin_dir_path( __FILE__ ) . '/interactions/c_categories.php';
 require plugin_dir_path( __FILE__ ) . '/interactions/ajax/ajax.php';
 require plugin_dir_path( __FILE__ ) . '/logger/logger.php';



 function github16702_allow_unsafe_urls($args, $url) {
    $args['reject_unsafe_urls'] = false;
    return $args;
}
add_filter('http_request_args', 'github16702_allow_unsafe_urls', 20, 2 );


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
