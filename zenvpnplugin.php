<?php
/*
  Plugin Name: zenVPN
  Description: Secure access to your WordPress wp-admin directory
  Version: 1.0.0
  License: GPLv3
  Author: zenvpn, alexeyrads
 */

// Define constants for plugin prefix and text domain
const ZV_PREFIX = 'zv_';
const ZV_VERSION = '1.0.0';
const ZV_TEXT_DOMAIN = 'zenvpn';
const ZEN_VPN_API_URL = 'https://app.zenvpn.net';
const ZEN_VPN_WEBSITE = 'https://zenvpn.net';
const ZEN_VPN_APP = 'https://app.zenvpn.net';

// Require the Autoloader class file
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Autoloader.php';

use zenVPN\Autoloader;
use zenVPN\Ajax\ZV_AJAX;
use zenVPN\Blocker\ZV_IP_Blocker;
use zenVPN\Enqueuer\ZV_Scripts;
use zenVPN\Settings\ZV_Settings;

// Create an instance of the Autoloader class
$autoloader = new Autoloader();

// Register the autoloader
$autoloader->register();

$settings = [
    [
        'name' => ZV_PREFIX . 'token',
        'callback' => 'token_field_callback',
        'section' => ZV_PREFIX . 'main_section',
        'args' => [
            'label_for' => ZV_PREFIX . 'token',
            'class' => ZV_PREFIX . 'row',
            'description' => __('Enter your zenVPN API key', ZV_TEXT_DOMAIN )
        ]
    ],
    [
        'name' => ZV_PREFIX . 'protect_wp_admin',
        'callback' => 'protect_admin_field_callback',
        'section' => ZV_PREFIX . 'main_section',
        'args' => [
            'label_for' => ZV_PREFIX . 'protect_wp_admin',
            'class' => ZV_PREFIX . 'row',
            'description' => __('Protect your /wp-admin now', ZV_TEXT_DOMAIN )
        ]
    ]
];

// Create instances of the classes and hook them to the appropriate actions
$zv_settings = ZV_Settings::init($settings);
$zv_scripts = new ZV_Scripts();
$zv_ip_blocker = new ZV_IP_Blocker();
$zv_ajax = new ZV_AJAX();

// Register deactivation hook
register_deactivation_hook(__FILE__, array($zv_settings, 'unregister_settings'));

// Add action hook to enqueue scripts and styles
add_action('admin_enqueue_scripts', array($zv_scripts, 'enqueue_scripts'));

// Register settings
add_action('admin_menu', array($zv_settings, 'register_settings'));

// Add options page
add_action('admin_menu', array($zv_settings, 'add_options_page'));

// Block access to WP files from external IPs
add_action('init', array($zv_ip_blocker, 'block_wp_file_access'));

// Hook your PHP function to the wp_ajax_ action
add_action('wp_ajax_zv_save_plugin_settings', array($zv_ajax, 'save_plugin_settings'));
// Hook your PHP function to the wp_ajax_nopriv_ action (optional)
add_action('wp_ajax_nopriv_zv_save_plugin_settings', array($zv_ajax, 'save_plugin_settings'));

// Hook your PHP function to the wp_ajax_ action
add_action('wp_ajax_zv_test_connection', array($zv_ajax, 'test_connection'));
// Hook your PHP function to the wp_ajax_nopriv_ action (optional)
add_action('wp_ajax_nopriv_zv_test_connection', array($zv_ajax, 'test_connection'));

// Hook your PHP function to the wp_ajax_ action
add_action('wp_ajax_zv_load_token_value', array($zv_ajax, 'load_token_value'));
// Hook your PHP function to the wp_ajax_nopriv_ action (optional)
add_action('wp_ajax_nopriv_zv_load_token_value', array($zv_ajax, 'load_token_value'));