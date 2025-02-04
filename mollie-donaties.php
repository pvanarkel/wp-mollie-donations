<?php
/**
 * Plugin Name: Mollie Donations
 * Description: Lets users do singular or recurring payments through Mollie.
 * Version: 1.0
 * Author: Peter van Arkel <peter@hippogrief.nl>
 */

if (!defined('ABSPATH')) exit;

// Inclusie van bestanden
require_once plugin_dir_path(__FILE__) . 'includes/settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/payment.php';
require_once plugin_dir_path(__FILE__) . 'includes/email.php';

// Plugin activeren
function mollie_donaties_activate() {
    add_option('mollie_api_key', '');
}
register_activation_hook(__FILE__, 'mollie_donaties_activate');

