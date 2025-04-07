<?php
/**
 * Plugin Name: WC Custom Meta REST Support
 * Description: Allows updating meta fields via WooCommerce REST API.
 * Version: 1.0
 * Author: https://github.com/gerigor
 */

if (!defined('ABSPATH')) exit;

// Register meta fields from settings
add_action('rest_api_init', function () {
    $fields = get_option('wc_custom_meta_fields_v2', []);
    if (!is_array($fields)) return;

    foreach ($fields as $field) {
        if (!isset($field['key']) || empty($field['key'])) continue;

        register_post_meta('product', $field['key'], [
            'type'         => $field['type'] ?? 'string',
            'single'       => isset($field['single']) ? (bool)$field['single'] : true,
            'show_in_rest' => true,
            'auth_callback' => function () {
                return current_user_can('edit_products');
            },
        ]);
    }
});

add_action('admin_menu', function () {
    add_submenu_page(
        'woocommerce',
        'REST Meta Fields',
        'REST Meta Fields',
        'manage_options',
        'wc-custom-meta-fields',
        'wc_custom_meta_fields_page'
    );
});

// Admin UI
function wc_custom_meta_fields_page() {
    if (!current_user_can('manage_options')) return;

    if (isset($_POST['meta_keys'])) {
        check_admin_referer('wc_custom_meta_fields_save');

        $new_fields = [];
        foreach ($_POST['meta_keys'] as $i => $key) {
            $key = sanitize_text_field($key);
            if (!$key) continue;

            $new_fields[] = [
                'key'    => $key,
                'type'   => in_array($_POST['meta_types'][$i], ['string', 'boolean', 'number']) ? $_POST['meta_types'][$i] : 'string',
                'single' => isset($_POST['meta_single'][$i]) ? true : false,
            ];
        }

        update_option('wc_custom_meta_fields_v2', $new_fields);
        echo '<div class="updated"><p>Fields saved!</p></div>';
    }

    $fields = get_option('wc_custom_meta_fields_v2', []);
    ?>
    <div class="wrap">
        <h1>WooCommerce Custom Meta Fields (REST API)</h1>
        <form method="post">
            <?php wp_nonce_field('wc_custom_meta_fields_save'); ?>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Meta Key</th>
                        <th>Type</th>
                        <th>Single?</th>
                    </tr>
                </thead>
                <tbody id="meta-fields-table">
                <?php foreach ($fields as $field): ?>
                    <tr>
                        <td><input type="text" name="meta_keys[]" value="<?php echo esc_attr($field['key']); ?>" style="width:100%"></td>
                        <td>
                            <select name="meta_types[]">
                                <option value="string" <?php selected($field['type'], 'string'); ?>>String</option>
                                <option value="boolean" <?php selected($field['type'], 'boolean'); ?>>Boolean</option>
                                <option value="number" <?php selected($field['type'], 'number'); ?>>Number</option>
                            </select>
                        </td>
                        <td><input type="checkbox" name="meta_single[<?php echo esc_attr($field['key']); ?>]" <?php checked($field['single'], true); ?>></td>
                    </tr>
                <?php endforeach; ?>
                <!-- empty row for new input -->
                <tr>
                    <td><input type="text" name="meta_keys[]" value="" style="width:100%"></td>
                    <td>
                        <select name="meta_types[]">
                            <option value="string">String</option>
                            <option value="boolean">Boolean</option>
                            <option value="number">Number</option>
                        </select>
                    </td>
                    <td><input type="checkbox" name="meta_single[]"></td>
                </tr>
                </tbody>
            </table>
            <p><input type="submit" class="button button-primary" value="Save Fields"></p>
        </form>
    </div>
    <?php
}
