<?php
// Voorkom directe toegang
if (!defined('ABSPATH')) exit;

// Functie om instellingenpagina toe te voegen in admin-menu
function mollie_donaties_menu() {
    add_menu_page(
        'Mollie Donaties Instellingen',
        'Mollie Donaties',
        'manage_options',
        'mollie-donaties',
        'mollie_donaties_settings_page'
    );
}
add_action('admin_menu', 'mollie_donaties_menu');

// Functie om instellingen op te slaan
function save_mollie_donaties_settings() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['mollie_settings_save'])) {
        update_option('mollie_live_api_key', sanitize_text_field($_POST['mollie_live_api_key']));
        update_option('mollie_test_api_key', sanitize_text_field($_POST['mollie_test_api_key']));
        update_option('mollie_mode', sanitize_text_field($_POST['mollie_mode'])); // live of test
    }
}
add_action('admin_init', 'save_mollie_donaties_settings');

// Instellingenpagina HTML
function mollie_donaties_settings_page() {
    ?>
    <div class="wrap">
        <h1>Mollie Donaties Instellingen</h1>
        <form method="post" action="">
            <?php settings_fields('mollie_donaties_options_group'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="mollie_live_api_key">Live API Key</label></th>
                    <td><input type="text" id="mollie_live_api_key" name="mollie_live_api_key" value="<?php echo esc_attr(get_option('mollie_live_api_key')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="mollie_test_api_key">Test API Key</label></th>
                    <td><input type="text" id="mollie_test_api_key" name="mollie_test_api_key" value="<?php echo esc_attr(get_option('mollie_test_api_key')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="mollie_mode">Mollie Modus</label></th>
                    <td>
                        <select id="mollie_mode" name="mollie_mode">
                            <option value="live" <?php selected(get_option('mollie_mode'), 'live'); ?>>Live</option>
                            <option value="test" <?php selected(get_option('mollie_mode'), 'test'); ?>>Test</option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button('Opslaan', 'primary', 'mollie_settings_save'); ?>
        </form>
    </div>
    <?php
}

