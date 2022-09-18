<?php

/**
 * Fired during plugin activation
 *
 * @link       https://dokio.me/
 * @since      1.0.0
 *
 * @package    Dokio_Store
 * @subpackage Dokio_Store/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Dokio_Store
 * @subpackage Dokio_Store/includes
 * @author     Mikhail Suntsov <mihail.suntsov@gmail.com>
 */
class Dokio_Store_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		// create a new cronjob
		// if ( ! wp_next_scheduled( 'my_one_min_event' ) ) {
		// 	wp_schedule_event( time(), 'daily', 'my_one_min_event');
		// }


	}

}
