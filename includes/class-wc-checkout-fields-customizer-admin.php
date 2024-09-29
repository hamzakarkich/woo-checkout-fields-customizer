<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WC_Checkout_Fields_Customizer_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Checkout Fields Customizer', 'woo-checkout-fields-customizer'),
            __('Checkout Fields', 'woo-checkout-fields-customizer'),
            'manage_woocommerce',
            'wc-checkout-fields-customizer',
            array($this, 'admin_page')
        );
    }

    public function register_settings() {
        register_setting('wc_checkout_fields_customizer', 'wc_checkout_fields_customizer', array($this, 'sanitize_settings'));
    }

    public function sanitize_settings($input) {
        $sanitized_input = array();

        foreach ($input as $field_key => $field_data) {
            $sanitized_input[$field_key] = array(
                'enabled'     => isset($field_data['enabled']) ? 'yes' : 'no',
                'type'        => sanitize_text_field($field_data['type']),
                'label'       => sanitize_text_field($field_data['label']),
                'placeholder' => sanitize_text_field($field_data['placeholder']),
                'required'    => isset($field_data['required']) ? 'yes' : 'no',
                'priority'    => absint($field_data['priority']),
                'options'     => isset($field_data['options']) ? sanitize_text_field($field_data['options']) : '',
            );
        }

        return $sanitized_input;
    }

    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'woocommerce_page_wc-checkout-fields-customizer') {
            return;
        }

        wp_enqueue_style('wc-checkout-fields-customizer-admin-css', plugin_dir_url(dirname(__FILE__)) . 'assets/css/admin.css', array(), '2.0');
        wp_enqueue_script('wc-checkout-fields-customizer-admin-js', plugin_dir_url(dirname(__FILE__)) . 'assets/js/admin.js', array('jquery', 'jquery-ui-sortable'), '2.0', true);
    }

    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('WooCommerce Checkout Fields Customizer', 'woo-checkout-fields-customizer'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('wc_checkout_fields_customizer');
                $custom_fields = get_option('wc_checkout_fields_customizer', array());
                ?>
                <table class="widefat" id="wc-checkout-fields-customizer-table">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Enabled', 'woo-checkout-fields-customizer'); ?></th>
                            <th><?php echo esc_html__('Type', 'woo-checkout-fields-customizer'); ?></th>
                            <th><?php echo esc_html__('Label', 'woo-checkout-fields-customizer'); ?></th>
                            <th><?php echo esc_html__('Placeholder', 'woo-checkout-fields-customizer'); ?></th>
                            <th><?php echo esc_html__('Required', 'woo-checkout-fields-customizer'); ?></th>
                            <th><?php echo esc_html__('Priority', 'woo-checkout-fields-customizer'); ?></th>
                            <th><?php echo esc_html__('Options', 'woo-checkout-fields-customizer'); ?></th>
                            <th><?php echo esc_html__('Actions', 'woo-checkout-fields-customizer'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($custom_fields)) {
                            foreach ($custom_fields as $field_key => $field_data) {
                                $this->render_field_row($field_key, $field_data);
                            }
                        }
                        ?>
                    </tbody>
                </table>
                <button type="button" class="button" id="add-new-field"><?php echo esc_html__('Add New Field', 'woo-checkout-fields-customizer'); ?></button>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    private function render_field_row($field_key, $field_data) {
        ?>
        <tr class="wc-checkout-fields-customizer-field">
            <td>
                <input type="checkbox" name="wc_checkout_fields_customizer[<?php echo esc_attr($field_key); ?>][enabled]" <?php checked($field_data['enabled'], 'yes'); ?>>
            </td>
            <td>
                <select name="wc_checkout_fields_customizer[<?php echo esc_attr($field_key); ?>][type]" class="field-type">
                    <option value="text" <?php selected($field_data['type'], 'text'); ?>><?php echo esc_html__('Text', 'woo-checkout-fields-customizer'); ?></option>
                    <option value="textarea" <?php selected($field_data['type'], 'textarea'); ?>><?php echo esc_html__('Textarea', 'woo-checkout-fields-customizer'); ?></option>
                    <option value="select" <?php selected($field_data['type'], 'select'); ?>><?php echo esc_html__('Select', 'woo-checkout-fields-customizer'); ?></option>
                    <option value="radio" <?php selected($field_data['type'], 'radio'); ?>><?php echo esc_html__('Radio', 'woo-checkout-fields-customizer'); ?></option>
                    <option value="checkbox" <?php selected($field_data['type'], 'checkbox'); ?>><?php echo esc_html__('Checkbox', 'woo-checkout-fields-customizer'); ?></option>
                </select>
            </td>
            <td>
                <input type="text" name="wc_checkout_fields_customizer[<?php echo esc_attr($field_key); ?>][label]" value="<?php echo esc_attr($field_data['label']); ?>">
            </td>
            <td>
                <input type="text" name="wc_checkout_fields_customizer[<?php echo esc_attr($field_key); ?>][placeholder]" value="<?php echo esc_attr($field_data['placeholder']); ?>">
            </td>
            <td>
                <input type="checkbox" name="wc_checkout_fields_customizer[<?php echo esc_attr($field_key); ?>][required]" <?php checked($field_data['required'], 'yes'); ?>>
            </td>
            <td>
                <input type="number" name="wc_checkout_fields_customizer[<?php echo esc_attr($field_key); ?>][priority]" value="<?php echo esc_attr($field_data['priority']); ?>" min="0" step="1">
            </td>
            <td>
                <input type="text" name="wc_checkout_fields_customizer[<?php echo esc_attr($field_key); ?>][options]" value="<?php echo esc_attr($field_data['options']); ?>" class="field-options" <?php echo ($field_data['type'] === 'select' || $field_data['type'] === 'radio') ? '' : 'style="display:none;"'; ?> placeholder="Option 1, Option 2, Option 3">
            </td>
            <td>
                <button type="button" class="button remove-field"><?php echo esc_html__('Remove', 'woo-checkout-fields-customizer'); ?></button>
            </td>
        </tr>
        <?php
    }
}