<?php

    add_action('dokiocrm_taxes_cronjob', 'c_cron_taxes');
    add_action("admin_post_turn_off_cron_taxes", "turn_off_cron_taxes"); 

    add_action('admin_post_turn_on_cron_taxes', 'turn_on_cron_taxes');
    function turn_on_cron_taxes() {
        logger('--- Taxes cronjob running ---');
        try {
            logger('INFO--taxes/turn_on_cron_taxes-- Trying to run cronjob dokiocrm_taxes_cronjob...');
            if ( !task_works( 'dokiocrm_taxes_cronjob' ) ) {
                wp_schedule_event( time(), 'hourly', 'dokiocrm_taxes_cronjob');
                logger('INFO--taxes/turn_on_cron_taxes-- The cronjob dokiocrm_taxes_cronjob works');
            } else {
                logger('INFO--taxes/turn_on_cron_taxes-- The cronjob dokiocrm_taxes_cronjob is already working');
            }
        } catch (Exception $e) {
            echo 'ERROR--taxes/turn_on_cron_taxes-- Exception: ',  $e->getMessage(), "\n";
            logger ('ERROR--taxes/turn_on_cron_taxes-- The response: '.$e->getMessage());
        }
        wp_redirect($_POST['backpage'],302 ); 
    }

    function turn_off_cron_taxes() {
        logger('--- Taxes cronjob deleting ---');
        logger('INFO--taxes/admin_post_turn_off_cron_taxes-- Trying to delete cronjob dokiocrm_taxes_cronjob...');
        try {
            wp_clear_scheduled_hook("dokiocrm_taxes_cronjob"); 
            logger('INFO--taxes/admin_post_turn_off_cron_taxes-- The cronjob dokiocrm_taxes_cronjob deleted successfully');
        } catch (Exception $e) {
            echo 'ERROR--taxes/admin_post_turn_off_cron_taxes-- Exception: ',  $e->getMessage(), "\n";
            logger ('ERROR--taxes/admin_post_turn_off_cron_taxes-- The response: '.$e->getMessage());
        }
        wp_redirect($_POST['backpage'],302 ); 
    } 

    function c_cron_taxes() {
        // do something every hour
        echo(111);
        c_get_crm_tax_rates();
    }

    function task_works($name){
        if ( wp_next_scheduled($name) ) {
            return true;
        } else return false;
    }

