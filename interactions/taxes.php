<?php

    require __DIR__ . '/../vendor/autoload.php';

    use Automattic\WooCommerce\Client;
    use Automattic\WooCommerce\HttpClient\HttpClientException;

// add_action( 'admin_post_get_tax_rates', 'f_get_tax_rates' );
// add_action( 'wp_ajax_get_tax_rates', 'f_get_tax_rates' );

add_action( 'wp_ajax_create_tax_rate', 'f_create_tax_rate' );
function f_create_tax_rate() {
    $woocommerce = new Client(get_option('siteurl'),get_option('woo_consumer_key'),get_option('woo_consumer_secret'),['version' => 'wc/v3']);
	status_header(200);
    $data = [
        'rate' => $_POST['rate'],
        'name' => $_POST['name']
    ];    
    echo json_encode($woocommerce->post('taxes', $data));
    wp_die();
}

add_action( 'wp_ajax_update_tax_rate', 'f_update_tax_rate' );
function f_update_tax_rate() {
    $woocommerce = new Client(get_option('siteurl'),get_option('woo_consumer_key'),get_option('woo_consumer_secret'),['version' => 'wc/v3']);
	status_header(200);
    $data = [
        'rate' => $_POST['rate'],
        'name' => $_POST['name']
    ];    
    echo json_encode($woocommerce->put('taxes/'.$_POST['id'], $data));
    wp_die();
}

add_action( 'wp_ajax_list_all_taxes', 'f_list_all_taxes' );
function f_list_all_taxes() {
    $woocommerce = new Client(get_option('siteurl'),get_option('woo_consumer_key'),get_option('woo_consumer_secret'),['version' => 'wc/v3']);
	status_header(200);
    echo json_encode($woocommerce->get('taxes'));
    wp_die();
}

// add_action( 'admin_post_c_get_crm_tax_rates', 'c_get_crm_tax_rates' );
// function c_get_tax_rates() {
//     echo('<br><br><br>1111111111111111111111111');
// }