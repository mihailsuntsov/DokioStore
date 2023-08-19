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

add_action( 'wp_ajax_test_crm_connection', 'f_test_crm_connection' );
function f_test_crm_connection() {
    $url = get_option( 'API_address' ).'/DokioCrmConnectionTest?key='.get_option( 'secret_key' );
    $request = curl_init($url); 
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($request, CURLOPT_HEADER, 0);
    logger('INFO--terms/c_get_crm_terms-- Connection test requesting...');
    $data = curl_exec($request);
    $httpcode = curl_getinfo($request, CURLINFO_HTTP_CODE);
    curl_close($request);
    status_header($httpcode);
    if($httpcode==200){
        echo(json_decode($data));
    } else {
        echo '<b>Server error with response code = '.$httpcode.' Connection test request failed!</b><br> Response = '.$response.', Received data = '.$data;
    }
    wp_die();    
}

add_action( 'wp_ajax_sync_products', 'f_sync_products' );
function f_sync_products() {

    if(!task_works('dokiocrm_products_cronjob')){
        try {
            logger('INFO--products/turn_on_cron_products-- Trying to run cronjob dokiocrm_products_cronjob...');
            wp_schedule_event( time(), 'every_1_minute', 'dokiocrm_products_cronjob');
            logger('INFO--products/turn_on_cron_products-- The cronjob dokiocrm_products_cronjob works');
        } catch (Exception $e) {
            logger ('ERROR--products/turn_on_cron_products-- The response: '.$e->getMessage());
        }
    } else {
        logger('--- Products, attributes, terms and categories cronjob deleting ---');
        try {
            logger('INFO--products/turn_off_cron_products-- Trying to delete cronjob dokiocrm_products_cronjob...');
            // On success an integer indicating number of events unscheduled (0 indicates no events were registered with the hook and arguments combination), false or WP_Error if unscheduling one or more events fail.
            $unschedule_result = wp_clear_scheduled_hook("dokiocrm_products_cronjob");
            if($unschedule_result != false && $unschedule_result > 0){
                logger('INFO--products/turn_off_cron_products-- The cronjob dokiocrm_products_cronjob deleted successfully');
                update_option( 'is_sync_task_executed', 'false', 'yes' ); 
            } else logger ('ERROR--products/turn_off_cron_products-- wp_clear_scheduled_hook returns '.$unschedule_result);
        } catch (Exception $e) {
            // echo 'ERROR--products/turn_off_cron_products-- Exception: ',  $e->getMessage(), "\n";
            logger ('ERROR--products/turn_off_cron_products-- The response: '.$e->getMessage());
        }
    }
    
    $result = array();
    echo json_encode($result);
    wp_die();    
}
add_action( 'wp_ajax_sync_orders', 'f_sync_orders' );
function f_sync_orders() {

    if(!task_works('dokiocrm_orders_cronjob')){
        try {
            logger('INFO--orders/turn_on_cron_orders-- Trying to run cronjob dokiocrm_orders_cronjob...');
            wp_schedule_event( time(), 'every_1_minute', 'dokiocrm_orders_cronjob');
            logger('INFO--orders/turn_on_cron_orders-- The cronjob dokiocrm_orders_cronjob works');
        } catch (Exception $e) {
            logger ('ERROR--orders/turn_on_cron_orders-- The response: '.$e->getMessage());
        }
    } else {
        logger('--- orders, attributes, terms and categories cronjob deleting ---');
        try {
            logger('INFO--orders/turn_off_cron_orders-- Trying to delete cronjob dokiocrm_orders_cronjob...');
            // On success an integer indicating number of events unscheduled (0 indicates no events were registered with the hook and arguments combination), false or WP_Error if unscheduling one or more events fail.
            $unschedule_result = wp_clear_scheduled_hook("dokiocrm_orders_cronjob");
            if($unschedule_result != false && $unschedule_result > 0){
                logger('INFO--orders/turn_off_cron_orders-- The cronjob dokiocrm_orders_cronjob deleted successfully');
                update_option( 'is_sync_task_executed', 'false', 'yes' ); 
            } else logger ('ERROR--orders/turn_off_cron_orders-- wp_clear_scheduled_hook returns '.$unschedule_result);
        } catch (Exception $e) {
            // echo 'ERROR--orders/turn_off_cron_orders-- Exception: ',  $e->getMessage(), "\n";
            logger ('ERROR--orders/turn_off_cron_orders-- The response: '.$e->getMessage());
        }
    }
    
    $result = array();
    echo json_encode($result);
    wp_die();    
}
add_action( 'wp_ajax_refresh_products_cron_status', 'f_refresh_products_cron_status' );
function f_refresh_products_cron_status() {
    $task_works = task_works('dokiocrm_products_cronjob');
    $result = array(
        "task_works" => $task_works
    );
    echo json_encode($result);
    wp_die();    
}
add_action( 'wp_ajax_refresh_orders_cron_status', 'f_refresh_orders_cron_status' );
function f_refresh_orders_cron_status() {
    $task_works = task_works('dokiocrm_orders_cronjob');
    $result = array(
        "task_works" => $task_works
    );
    echo json_encode($result);
    wp_die();    
}

