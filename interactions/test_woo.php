<?php

require __DIR__ . '/../vendor/autoload.php';
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

add_action( 'wp_ajax_test_woo_connection', 'f_test_woo_connection' );
function f_test_woo_connection() {
    $woocommerce = new Client(get_option('siteurl'),get_option('woo_consumer_key'),get_option('woo_consumer_secret'),['version' => 'wc/v3']);
	status_header(200);
    echo json_encode($woocommerce->get('taxes'));
    wp_die();
    
}