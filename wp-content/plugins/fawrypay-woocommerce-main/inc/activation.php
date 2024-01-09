<?php

function pageDifinitions() {
    return array(
        'fawrycallback' => array(
            'title' => __('fawrycallback', 'fawry_pay'),
            'content' => ''
        ),
    );
}

function fawry_pay_activate() {
    fawry_pay_check_currency();
    global $wpdb;
    $table_name = $wpdb->prefix . 'fawry_pay_callback';
    $wpdb_collate = $wpdb->collate;
    $createSQL = "CREATE TABLE IF NOT EXISTS {$table_name} (
         `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
	`date_called` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`data_rec`  TEXT NOT NULL ,
	PRIMARY KEY (`id`)
        )
         COLLATE {$wpdb_collate}";
    //echo $createSQL;die();
    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    dbDelta($createSQL);


    flush_rewrite_rules(); //automatic flushing of the WordPress rewrite rules for cpt
     //activate the schedule
    wp_schedule_event(time(), 'hourly', 'woocommerce_cancel_unpaid_submitted');
    //run immediate with http://localhost/wpshop/wp-cron.php?doing_wp_cron
}

function fawry_pay_deactivate() {
    //remove the schedule
    wp_clear_scheduled_hook( 'woocommerce_cancel_unpaid_submitted' );
}

function fawry_pay_is_currency_supported()
{
    return in_array(get_woocommerce_currency(), FAWRY_PAY_PAYMENT_SUPPORTED_CURRENCIES);
}

function fawry_pay_check_currency(){
    if ( !fawry_pay_is_currency_supported()) {
        $supportedCurrencies = implode(', ', FAWRY_PAY_PAYMENT_SUPPORTED_CURRENCIES);
        $errMsg = 'Your Woocommerce Currency is not supported, We Only support '.$supportedCurrencies;
        wp_die($errMsg);
    }
}