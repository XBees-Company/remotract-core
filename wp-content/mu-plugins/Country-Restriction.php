<?php
/**
 * Plugin Name: Country Restriction Plugin
 * Description: Restricts access to the site based on the user's country using ipinfo.io and logs events.
 * Version: 1.0
 * Author: Xbess
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Country_Restriction_Plugin {

    private $api_token = 'ad853703cd152f';
    private $blacklisted_ips = array('66.249.70.160', '66.249.70.174', '66.249.70.161', '10.0.0.1'); // Add your blacklisted IPs here

    public function __construct() {
        // Start the session
        if (!session_id()) {
            session_start();
        }
      	add_action( 'init', array( $this, 'restrict_countries' ) );
        // add_action( 'template_redirect', array( $this, 'restrict_countries' ) );
    }

    public function restrict_countries() {
        // Get the user's IP address
        $user_ip = $_SERVER['REMOTE_ADDR'];
    
        // Check if the user is accepted
        if ($this->is_user_accepted()) {
            // User is accepted, process accordingly
            $this->process_accepted_user($user_ip);
            return;
        }
    
        // Check if the user's IP is in the whitelist
        if ($this->is_ip_whitelisted($user_ip)) {
            // Allow access for whitelisted IPs
            $this->log_access_event($user_ip, 'EG', 'whitelisted');
            return;
        }
    
        // Use ipinfo.io API to get the user's country
        $user_country = $this->get_user_country_by_ip($user_ip);
    
        // Check if the user's IP is in the blacklist
        if (in_array($user_ip, $this->blacklisted_ips)) {
            $this->block_user($user_ip);
        }
      
      // Check if the user is blocked
        if ($this->is_user_blocked()) {
            // User is blocked, end execution
            $this->log_access_event($user_ip, 'session', 'restriction');
            wp_die('Sorry, access to this site is restricted from your IP address.');
        }
    
        // Specify allowed countries (e.g., Egypt and Saudi Arabia)
        $allowed_countries = array('EG', 'SA'); // Country codes according to ISO 3166-1 alpha-2
    
        // Check if the user's country is in the allowed list
        if (in_array($user_country, $allowed_countries)) {

            $this->process_accepted_user($user_ip, $user_country);
        } else {
            $this->log_access_event($user_ip, $user_country, 'restriction');
            $_SESSION['country_restriction_user_blocked'] = true;
            wp_die('Sorry, access to this site is restricted in your country.');
        }
    }
    

    private function get_user_country_by_ip($ip) {
        // Construct the API URL
        $api_url = "https://ipinfo.io/$ip?token=$this->api_token";

        // Make a request to the ipinfo.io API
        $response = wp_remote_get($api_url);

        // Check if the request was successful
        if (is_wp_error($response)) {
            return ''; // Return an empty string on error
        }

        // Decode the JSON response
        $data = json_decode(wp_remote_retrieve_body($response), true);

        // Extract the country code from the response
        $country_code = isset($data['country']) ? $data['country'] : '';

        return $country_code;
    }

    private function log_access_event($user_ip, $user_country, $event_type) {
        switch ($event_type) {
            case 'success':
                $flag = '✅';
                break;
            case 'whitelisted':
                $flag = '✅';
                break;
            case 'restriction':
                $flag = '❌';
                break;
            case 'blacklisted':
                $flag = '🛡';
                break;
            default:
                $flag = '❗';
                break;
        }

        // Define the log directory
        $log_directory = WP_CONTENT_DIR . '/country-access/';

        // Create the directory if it doesn't exist
        if (!file_exists($log_directory)) {
            mkdir($log_directory, 0755, true);
        }

        // Define the log file path with the current date
        $log_file = $log_directory . 'country_access_log_' . date('Y-m-d') . '.log';

        // Log the event to a custom log file
        $log_data = $flag . " || " . date('Y-m-d H:i:s') . " - Access $event_type for IP: $user_ip, Country: $user_country\n";
        file_put_contents($log_file, $log_data, FILE_APPEND);
    }

    private function is_ip_whitelisted($ip) {
        // Get the array of whitelisted IPs
        $whitelisted_ips = array('154.182.251.244', '197.40.125.93', '127.0.0.1');
    
        // Check if the provided IP is in the whitelist
        return in_array($ip, $whitelisted_ips);
    }

    private function set_user_accepted_status() {
        // Set a session variable to mark the user as accepted
        $_SESSION['country_restriction_user_accepted'] = true;
    }

    private function is_user_accepted() {
        // Check if the user has been previously accepted
        return isset($_SESSION['country_restriction_user_accepted']) && $_SESSION['country_restriction_user_accepted'] === true;
    }

    private function process_accepted_user($user_ip, $country = 'session') {
        // Process the user as accepted (e.g., log access, grant access, etc.)
        $_SESSION['country_restriction_user_accepted'] = true;
        $this->log_access_event($user_ip, $country, 'success');
    }

    private function block_user($user_ip, $country = 'session') {
        // Mark the user as blocked in the session
        $_SESSION['country_restriction_user_blocked'] = true;

        // Log the event
        $this->log_access_event($user_ip, $country, 'restriction');

        // End execution with an error message
        wp_die('Sorry, access to this site is restricted from your IP address.');
    }

    private function is_user_blocked() {
        // Check if the user has been marked as blocked
        return isset($_SESSION['country_restriction_user_blocked']) && $_SESSION['country_restriction_user_blocked'] === true;
    }
}

// Instantiate the class
new Country_Restriction_Plugin();
