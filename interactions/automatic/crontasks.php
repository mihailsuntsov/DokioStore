<?php

    // add_action('dokiocrm_taxes_cronjob', 'c_cron_taxes');
    // add_action("admin_post_turn_off_cron_taxes", "turn_off_cron_taxes");
    // add_action('admin_post_turn_on_cron_taxes', 'turn_on_cron_taxes');
    // 
    add_action('dokiocrm_products_cronjob', 'c_cron_products');
    add_action("admin_post_turn_off_cron_products", "turn_off_cron_products");
    add_action('admin_post_turn_on_cron_products', 'turn_on_cron_products');
    // add_action('dokiocrm_orders_cronjob', 'c_cron_orders');
    // add_action("admin_post_turn_off_cron_orders", "turn_off_cron_orders");
    // add_action('admin_post_turn_on_cron_orders', 'turn_on_cron_orders');

    // function turn_on_cron_taxes() {
    //     logger('--- Taxes cronjob running ---');
    //     try {
    //         logger('INFO--taxes/turn_on_cron_taxes-- Trying to run cronjob dokiocrm_taxes_cronjob...');
    //         if ( !task_works( 'dokiocrm_taxes_cronjob' ) ) {
    //             wp_schedule_event( time(), 'hourly', 'dokiocrm_taxes_cronjob');
    //             logger('INFO--taxes/turn_on_cron_taxes-- The cronjob dokiocrm_taxes_cronjob works');
    //         } else {
    //             logger('INFO--taxes/turn_on_cron_taxes-- The cronjob dokiocrm_taxes_cronjob is already working');
    //         }
    //     } catch (Exception $e) {
    //         echo 'ERROR--taxes/turn_on_cron_taxes-- Exception: ',  $e->getMessage(), "\n";
    //         logger ('ERROR--taxes/turn_on_cron_taxes-- The response: '.$e->getMessage());
    //     }
    //     wp_redirect($_POST['backpage'],302 ); 
    // }
    // function turn_off_cron_taxes() {
    //     logger('--- Taxes cronjob deleting ---');
    //     logger('INFO--taxes/admin_post_turn_off_cron_taxes-- Trying to delete cronjob dokiocrm_taxes_cronjob...');
    //     try {
    //         wp_clear_scheduled_hook("dokiocrm_taxes_cronjob"); 
    //         logger('INFO--taxes/admin_post_turn_off_cron_taxes-- The cronjob dokiocrm_taxes_cronjob deleted successfully');
    //     } catch (Exception $e) {
    //         echo 'ERROR--taxes/admin_post_turn_off_cron_taxes-- Exception: ',  $e->getMessage(), "\n";
    //         logger ('ERROR--taxes/admin_post_turn_off_cron_taxes-- The response: '.$e->getMessage());
    //     }
    //     wp_redirect($_POST['backpage'],302 ); 
    // }

    function turn_on_cron_products() {
        logger('--- Products, attributes, terms and categories cronjob running ---');
        try {
            logger('INFO--products/turn_on_cron_products-- Trying to run cronjob dokiocrm_products_cronjob...');
            if ( !task_works( 'dokiocrm_products_cronjob' ) ) {
                wp_schedule_event( time(), 'every_1_minute', 'dokiocrm_products_cronjob');
                logger('INFO--products/turn_on_cron_products-- The cronjob dokiocrm_products_cronjob works');
            } else {
                logger('INFO--products/turn_on_cron_products-- The cronjob dokiocrm_products_cronjob is already working');
            }
        } catch (Exception $e) {
            echo 'ERROR--products/turn_on_cron_products-- Exception: ',  $e->getMessage(), "\n";
            logger ('ERROR--products/turn_on_cron_products-- The response: '.$e->getMessage());
        }
        wp_redirect($_POST['backpage'],302 ); 
    }
    function turn_off_cron_products() {
        logger('--- Products, attributes, terms and categories cronjob deleting ---');
        logger('INFO--products/turn_off_cron_products-- Trying to delete cronjob dokiocrm_products_cronjob...');
        try {
            // On success an integer indicating number of events unscheduled (0 indicates no events were registered with the hook and arguments combination), false or WP_Error if unscheduling one or more events fail.
            $unschedule_result = wp_clear_scheduled_hook("dokiocrm_products_cronjob");
            if($unschedule_result != false && $unschedule_result > 0){
                logger('INFO--products/turn_off_cron_products-- The cronjob dokiocrm_products_cronjob deleted successfully');
                update_option( 'is_sync_task_executed', 'false', 'yes' ); 
            } else logger ('ERROR--products/turn_off_cron_products-- wp_clear_scheduled_hook returns '.$unschedule_result);
            
        } catch (Exception $e) {
            echo 'ERROR--products/turn_off_cron_products-- Exception: ',  $e->getMessage(), "\n";
            logger ('ERROR--products/turn_off_cron_products-- The response: '.$e->getMessage());
        }
        wp_redirect($_POST['backpage'],302 ); 
    }

    // function turn_on_cron_orders() {
    //     logger('--- Orders cronjob running ---');
    //     try {
    //         logger('INFO--orders/turn_on_cron_orders-- Trying to run cronjob dokiocrm_orders_cronjob...');
    //         if ( !task_works( 'dokiocrm_orders_cronjob' ) ) {
    //             wp_schedule_event( time(), 'hourly', 'dokiocrm_orders_cronjob');
    //             logger('INFO--orders/turn_on_cron_orders-- The cronjob dokiocrm_orders_cronjob works');
    //         } else {
    //             logger('INFO--orders/turn_on_cron_orders-- The cronjob dokiocrm_orders_cronjob is already working');
    //         }
    //     } catch (Exception $e) {
    //         echo 'ERROR--orders/turn_on_cron_orders-- Exception: ',  $e->getMessage(), "\n";
    //         logger ('ERROR--orders/turn_on_cron_orders-- The response: '.$e->getMessage());
    //     }
    //     wp_redirect($_POST['backpage'],302 ); 
    // }
    // function turn_off_cron_orders() {
    //     logger('--- Orders cronjob deleting ---');
    //     logger('INFO--orders/admin_post_turn_off_cron_orders-- Trying to delete cronjob dokiocrm_orders_cronjob...');
    //     try {
    //         wp_clear_scheduled_hook("dokiocrm_orders_cronjob"); 
    //         logger('INFO--orders/admin_post_turn_off_cron_orders-- The cronjob dokiocrm_orders_cronjob deleted successfully');
    //     } catch (Exception $e) {
    //         echo 'ERROR--orders/admin_post_turn_off_cron_orders-- Exception: ',  $e->getMessage(), "\n";
    //         logger ('ERROR--orders/admin_post_turn_off_cron_orders-- The response: '.$e->getMessage());
    //     }
    //     wp_redirect($_POST['backpage'],302 ); 
    // }

    // function c_cron_taxes() {
    //     echo(111);
    //     c_get_crm_tax_rates();
    // }


    // function c_cron_taxes() {
    //     // do something every hour
    //     echo(111);
    //     c_get_crm_tax_rates();
    // }

    function c_cron_products() {
    
        check_sync_task_executed_option_existed();
    
        if(get_option( 'is_sync_task_executed' ) == 'false'){
          update_option( 'is_sync_task_executed', 'true', 'yes' );
          logger('INFO >>>>>>>>>>>>    STARTING NEW SYNCHRONIZATION CYCLE     >>>>>>>>>>>>');
          c_get_crm_categories();
          c_get_crm_attributes();
          c_get_crm_terms();
          c_get_crm_products();
          c_get_crm_variations();
          c_get_crm_orders();
          logger('INFO <<<<<<<<<<<<    FINISHING SYNCHRONIZATION CYCLE     <<<<<<<<<<<<');
          update_option( 'is_sync_task_executed', 'false', 'yes' );
        } else {
          logger('INFO ******   New synchronization cycle is not started because of previous task is still executed   ******');
        }
    }

    function check_sync_task_executed_option_existed(){
    
       if(!get_option('is_sync_task_executed')){           
             add_option('is_sync_task_executed', 'false');
        }
    
    }



    // function c_cron_orders() {
    //     c_get_crm_orders();
    // }


    function task_works($name){
        if ( wp_next_scheduled($name) ) {
            return true;
        } else return false;
    }