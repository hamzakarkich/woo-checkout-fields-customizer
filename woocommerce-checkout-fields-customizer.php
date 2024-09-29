<?php
/**
 * Plugin Name: WooCommerce Checkout Fields Customizer
 * Description: Add, edit, delete, and re-arrange WooCommerce checkout fields with an easy-to-use admin interface.
 * Version: 2.0
 * Author: Hamza
 * Text Domain: woo-checkout-fields-customizer
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WC_Checkout_Fields_Customizer {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
    }

    public function init() {
        // Load text domain for translations
        load_plugin_textdomain('woo-checkout-fields-customizer', false, dirname(plugin_basename(__FILE__)) . '/languages');

        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }

        // Include required files
        $this->include_files();

        // Add settings link to plugins page
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));

        // Initialize the admin settings
        if (is_admin()) {
            new WC_Checkout_Fields_Customizer_Admin();
        }

        // Hook to customize checkout fields
        add_filter('woocommerce_checkout_fields', array($this, 'customize_checkout_fields'));

        // Save custom field data
        add_action('woocommerce_checkout_update_order_meta', array($this, 'save_custom_checkout_fields'));

        // Display custom fields in the order admin
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'display_custom_checkout_fields_in_admin'));

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function include_files() {
        require_once plugin_dir_path(__FILE__) . 'includes/class-wc-checkout-fields-customizer-admin.php';
    }

    public function woocommerce_missing_notice() {
        echo '<div class="error"><p>' . sprintf(__('WooCommerce Checkout Fields Customizer requires WooCommerce to be installed and active. You can download %s here.', 'woo-checkout-fields-customizer'), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>') . '</p></div>';
    }

    public function add_settings_link($links) {
        $settings_link = '<a href="admin.php?page=wc-checkout-fields-customizer">' . __('Settings', 'woo-checkout-fields-customizer') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public function customize_checkout_fields($fields) {
        $custom_fields = get_option('wc_checkout_fields_customizer', array());

        foreach ($custom_fields as $field_key => $field_data) {
            if ($field_data['enabled'] === 'yes') {
                $fields['billing'][$field_key] = array(
                    'type'        => $field_data['type'],
                    'label'       => $field_data['label'],
                    'placeholder' => $field_data['placeholder'],
                    'required'    => ($field_data['required'] === 'yes'),
                    'class'       => array('form-row-wide'),
                    'priority'    => $field_data['priority'],
                );
            } elseif (isset($fields['billing'][$field_key])) {
                unset($fields['billing'][$field_key]);
            }
        }

        return $fields;
    }

    public function save_custom_checkout_fields($order_id) {
        $custom_fields = get_option('wc_checkout_fields_customizer', array());

        foreach ($custom_fields as $field_key => $field_data) {
            if ($field_data['enabled'] === 'yes' && !empty($_POST[$field_key])) {
                update_post_meta($order_id, '_' . $field_key, sanitize_text_field($_POST[$field_key]));
            }
        }
    }

    public function display_custom_checkout_fields_in_admin($order) {
        $custom_fields = get_option('wc_checkout_fields_customizer', array());

        foreach ($custom_fields as $field_key => $field_data) {
            if ($field_data['enabled'] === 'yes') {
                $field_value = get_post_meta($order->get_id(), '_' . $field_key, true);
                if ($field_value) {
                    echo '<p><strong>' . esc_html($field_data['label']) . ':</strong> ' . esc_html($field_value) . '</p>';
                }
            }
        }
    }

    public function enqueue_frontend_scripts() {
        wp_enqueue_style('wc-checkout-fields-customizer-css', plugin_dir_url(__FILE__) . 'assets/css/frontend.css', array(), '2.0');
        wp_enqueue_script('wc-checkout-fields-customizer-js', plugin_dir_url(__FILE__) . 'assets/js/frontend.js', array('jquery'), '2.0', true);
    }

    public function enqueue_admin_scripts($hook) {
        if ($hook === 'woocommerce_page_wc-checkout-fields-customizer') {
            wp_enqueue_style('wc-checkout-fields-customizer-admin-css', plugin_dir_url(__FILE__) . 'assets/css/admin.css', array(), '2.0');
            wp_enqueue_script('wc-checkout-fields-customizer-admin-js', plugin_dir_url(__FILE__) . 'assets/js/admin.js', array('jquery', 'jquery-ui-sortable'), '2.0', true);
        }
    }
}

// Initialize the plugin
WC_Checkout_Fields_Customizer::get_instance();