<?php


/**
 * @link              https://fawry.com/
 * @since             1.0.0
 * @package           fawry_pay
 *
 * @wordpress-plugin
 * Plugin Name:       FawryPay
 * Plugin URI:        https://www.fawrydigital.com/
 * Description:       Fawry integration plugin.
 * Version:           4.0.0
 * Author:            Fawry Corporation
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

 ////////////////CONSTANTS//////////////////////
define('FAWRY_PAY_PAYMENT_METHOD','fawry_pay');
// you can add to this array the upcoming supporeted currencies such as USD
define('FAWRY_PAY_PAYMENT_SUPPORTED_CURRENCIES', array('EGP'));

if (!defined('FAWRY_PAY_URL')) {
    define('FAWRY_PAY_URL', plugin_dir_url(__FILE__));
}

$active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
if(fawry_payment_is_woocommerce_active()){
	add_filter('woocommerce_payment_gateways', 'add_other_payment_gateway');
	function add_other_payment_gateway( $gateways ){
		$gateways[] = 'wc_gateway_at_fawry_payment';	
		return $gateways;
	}
	add_action('plugins_loaded', 'init_other_payment_gateway');
	function init_other_payment_gateway(){
		require_once 'inc/wc_gateway_at_fawry_payment.php';
	}
	
}

/////////////////includes////////////////////////
require_once 'inc/thankyoupage_customizer.php';
require_once 'inc/cancel_unpaid_on_hold_schedule.php';
require_once 'inc/activation.php';
require_once 'inc/utils.php';

register_activation_hook( __FILE__, 'fawry_pay_activate' );
register_deactivation_hook(__FILE__, 'fawry_pay_deactivate');
/**
 * @return bool
 */

function fawry_payment_is_woocommerce_active()
{
	$active_plugins = (array) get_option('active_plugins', array());
	
	if (is_multisite()) {
		$active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
	}
	return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
}
