<?php
/**
 * Plugin Name:       AI Intake for Kai Calls
 * Plugin URI:        https://www.kaicalls.com
 * Description:       Connects your WordPress site to KaiCalls to capture leads from your forms automatically.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            KaiCalls
 * Author URI:        https://www.kaicalls.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       kai-intake
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('KAI_INTAKE_VERSION', '1.0.0');
define('KAI_INTAKE_API_URL', 'https://www.kaicalls.com');
define('KAI_INTAKE_OPTIONS_GROUP', 'kai_intake_options');

class Kai_Intake_Plugin {

    public function __construct() {
        add_action('init', [$this, 'load_textdomain']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widget']);
        add_shortcode('kai_intake_form', [$this, 'render_intake_form']);
        add_action('wp_ajax_kai_intake_submit', [$this, 'handle_form_submission']);
        add_action('wp_ajax_nopriv_kai_intake_submit', [$this, 'handle_form_submission']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
        add_action('wp_ajax_kai_fetch_widget_data', [$this, 'handle_widget_data_request']);
    }

    public function load_textdomain() {
        load_plugin_textdomain('kai-intake', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function add_admin_menu() {
        add_options_page(
            __('AI Intake Settings', 'kai-intake'),
            __('AI Intake', 'kai-intake'),
            'manage_options',
            'kai-intake-settings',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting(KAI_INTAKE_OPTIONS_GROUP, 'kai_intake_api_details', [$this, 'sanitize_and_test_connection']);
    }

    public function sanitize_and_test_connection($input) {
        $input['api_key'] = sanitize_text_field($input['api_key']);
        $input['public_key'] = sanitize_text_field($input['public_key']);

        $api_key = $input['api_key'];
        $public_key = $input['public_key'];

        if (empty($api_key) || empty($public_key)) {
            add_settings_error('kai_intake_messages', 'kai_intake_message', __('API Key and Public Key cannot be empty.', 'kai-intake'), 'error');
            return $input;
        }

        $api_url = trailingslashit(KAI_INTAKE_API_URL) . 'api/v1/wordpress/auth/verify';

        $response = wp_remote_post($api_url, [
            'method'  => 'POST',
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode(['apiKey' => $api_key, 'publicKey' => $public_key]),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            add_settings_error('kai_intake_messages', 'kai_intake_message', __('Connection failed: ', 'kai-intake') . $response->get_error_message(), 'error');
        } else {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($body['success']) && $body['success'] === true) {
                add_settings_error('kai_intake_messages', 'kai_intake_message', __('Connection successful!', 'kai-intake'), 'updated');
            } else {
                $error_message = isset($body['error']) ? $body['error'] : __('Invalid API credentials.', 'kai-intake');
                add_settings_error('kai_intake_messages', 'kai_intake_message', __('Connection failed: ', 'kai-intake') . $error_message, 'error');
            }
        }
        return $input;
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('AI Intake Settings', 'kai-intake'); ?></h1>
            <?php settings_errors('kai_intake_messages'); ?>
            <form action="options.php" method="post">
                <?php
                settings_fields(KAI_INTAKE_OPTIONS_GROUP);
                $options = get_option('kai_intake_api_details');
                $api_key = isset($options['api_key']) ? $options['api_key'] : '';
                $public_key = isset($options['public_key']) ? $options['public_key'] : '';
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('Public Key', 'kai-intake'); ?></th>
                        <td>
                            <input type="text" name="kai_intake_api_details[public_key]" value="<?php echo esc_attr($public_key); ?>" class="regular-text" />
                            <p class="description"><?php echo esc_html__('Your public API identifier (starts with wp_pk_).', 'kai-intake'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('Secret API Key', 'kai-intake'); ?></th>
                        <td>
                            <input type="password" name="kai_intake_api_details[api_key]" value="<?php echo esc_attr($api_key); ?>" class="regular-text" autocomplete="off" />
                            <p class="description"><?php echo esc_html__('Your secret API key (starts with wp_sk_).', 'kai-intake'); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(__('Save & Test Connection', 'kai-intake')); ?>
            </form>
            <p class="description">
                <?php echo esc_html__('Add the lead form to any page or post with this shortcode:', 'kai-intake'); ?>
                <code>[kai_intake_form]</code>
            </p>
        </div>
        <?php
    }

    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'kai_intake_dashboard_widget',
            __('Recent AI Intake Leads', 'kai-intake'),
            [$this, 'render_dashboard_widget']
        );
    }

    public function render_dashboard_widget() {
        echo '<div id="kai-widget-content"><p>' . esc_html__('Loading...', 'kai-intake') . '</p></div>';
    }

    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'index.php') {
            return;
        }
        wp_enqueue_script('kai-intake-admin', plugin_dir_url(__FILE__) . 'kai-admin.js', ['jquery'], KAI_INTAKE_VERSION, true);
        wp_localize_script('kai-intake-admin', 'kai_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('kai-dashboard-widget-nonce'),
        ]);
    }

    public function enqueue_frontend_scripts() {
        // Only enqueue if the shortcode is used on the current page.
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'kai_intake_form')) {
            wp_enqueue_style('kai-intake-frontend', plugin_dir_url(__FILE__) . 'kai-intake.css', [], KAI_INTAKE_VERSION);
            wp_enqueue_script('kai-intake-frontend', plugin_dir_url(__FILE__) . 'kai-frontend.js', ['jquery'], KAI_INTAKE_VERSION, true);
            wp_localize_script('kai-intake-frontend', 'kai_intake_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('kai_intake_form_nonce'),
                'messages' => [
                    'success'        => __('Thank you for your submission!', 'kai-intake'),
                    'error'          => __('An error occurred. Please try again.', 'kai-intake'),
                    'network_error'  => __('A network error occurred. Please try again.', 'kai-intake'),
                    'invalid_token'  => __('Invalid security token.', 'kai-intake'),
                    'not_configured' => __('Plugin not configured.', 'kai-intake'),
                ],
            ]);
        }
    }

    public function render_intake_form() {
        ob_start();
        ?>
        <form id="kai-intake-form" class="kai-intake-form">
            <?php wp_nonce_field('kai_intake_form_nonce', 'kai_nonce'); ?>
            <div class="form-group">
                <label for="kai_name"><?php echo esc_html__('Name', 'kai-intake'); ?> *</label>
                <input type="text" id="kai_name" name="name" required>
            </div>
            <div class="form-group">
                <label for="kai_email"><?php echo esc_html__('Email', 'kai-intake'); ?> *</label>
                <input type="email" id="kai_email" name="email" required>
            </div>
            <div class="form-group">
                <label for="kai_phone"><?php echo esc_html__('Phone', 'kai-intake'); ?></label>
                <input type="tel" id="kai_phone" name="phone">
            </div>
            <div class="form-group">
                <label for="kai_message"><?php echo esc_html__('Message', 'kai-intake'); ?> *</label>
                <textarea id="kai_message" name="message" rows="4" required></textarea>
            </div>
            <button type="submit" class="kai-submit-btn"><?php echo esc_html__('Submit', 'kai-intake'); ?></button>
            <div id="kai-form-message" class="kai-form-message"></div>
        </form>
        <?php
        return ob_get_clean();
    }

    public function handle_form_submission() {
        if (!check_ajax_referer('kai_intake_form_nonce', 'nonce', false)) {
            wp_send_json_error(['error' => __('Invalid security token.', 'kai-intake')], 403);
            return;
        }

        $required_fields = ['name', 'email', 'message'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(['error' => sprintf(__('Field "%s" is required.', 'kai-intake'), $field)], 400);
                return;
            }
        }

        $email = sanitize_email(wp_unslash($_POST['email']));
        if (!is_email($email)) {
            wp_send_json_error(['error' => __('Please enter a valid email address.', 'kai-intake')], 400);
            return;
        }

        $options = get_option('kai_intake_api_details');
        $api_key = isset($options['api_key']) ? $options['api_key'] : '';
        $public_key = isset($options['public_key']) ? $options['public_key'] : '';

        if (empty($api_key) || empty($public_key)) {
            wp_send_json_error(['error' => __('Plugin not configured.', 'kai-intake')], 500);
            return;
        }

        $lead_data = [
            'name'    => sanitize_text_field(wp_unslash($_POST['name'])),
            'email'   => $email,
            'phone'   => isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '',
            'message' => sanitize_textarea_field(wp_unslash($_POST['message'])),
        ];

        $api_url = trailingslashit(KAI_INTAKE_API_URL) . 'api/v1/wordpress/intake';
        $auth_token = $public_key . ':' . $api_key;

        $response = wp_remote_post($api_url, [
            'method'  => 'POST',
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $auth_token,
            ],
            'body'    => wp_json_encode($lead_data),
            'timeout' => 20,
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['error' => $response->get_error_message()], 500);
        } else {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($body['success']) && $body['success'] === true) {
                wp_send_json_success(['leadId' => isset($body['leadId']) ? $body['leadId'] : null]);
            } else {
                $error = isset($body['error']) ? $body['error'] : __('Failed to submit lead.', 'kai-intake');
                wp_send_json_error(['error' => $error], 400);
            }
        }
    }

    public function handle_widget_data_request() {
        if (!check_ajax_referer('kai-dashboard-widget-nonce', 'nonce', false)) {
            wp_send_json_error(['error' => 'Invalid nonce'], 403);
            return;
        }

        $options = get_option('kai_intake_api_details');
        $api_key = isset($options['api_key']) ? $options['api_key'] : '';
        $public_key = isset($options['public_key']) ? $options['public_key'] : '';

        if (empty($api_key) || empty($public_key)) {
            wp_send_json_error(['error' => 'Plugin not configured.'], 400);
            return;
        }

        $cached_data = get_transient('kai_intake_widget_data');
        if ($cached_data !== false) {
            wp_send_json_success($cached_data);
            return;
        }

        $api_url = trailingslashit(KAI_INTAKE_API_URL) . 'api/v1/wordpress/dashboard-widget';
        $auth_token = $public_key . ':' . $api_key;

        $response = wp_remote_get($api_url, [
            'headers' => ['Authorization' => 'Bearer ' . $auth_token],
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error(['error' => $response->get_error_message()], 500);
        } else {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($body['success']) && $body['success'] === true) {
                $data_to_cache = [
                    'recentLeads'   => $body['recentLeads'],
                    'leadsThisWeek' => $body['leadsThisWeek'],
                ];
                set_transient('kai_intake_widget_data', $data_to_cache, HOUR_IN_SECONDS);
                wp_send_json_success($data_to_cache);
            } else {
                wp_send_json_error(['error' => isset($body['error']) ? $body['error'] : 'Could not fetch data.'], 400);
            }
        }
    }
}

new Kai_Intake_Plugin();
