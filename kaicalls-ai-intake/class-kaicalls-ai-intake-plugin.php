<?php
/**
 * Main plugin controller.
 *
 * @package KaiCalls_AI_Intake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin controller.
 */
class Kaicalls_AI_Intake_Plugin {

	/**
	 * Register WordPress hooks.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
		add_shortcode( 'kaicalls_ai_intake_form', array( $this, 'render_intake_form' ) );
		add_action( 'wp_ajax_kaicalls_ai_intake_submit', array( $this, 'handle_form_submission' ) );
		add_action( 'wp_ajax_nopriv_kaicalls_ai_intake_submit', array( $this, 'handle_form_submission' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
		add_action( 'wp_ajax_kaicalls_ai_intake_fetch_widget_data', array( $this, 'handle_widget_data_request' ) );
	}

	/**
	 * Add the settings page under the Settings menu.
	 */
	public function add_admin_menu() {
		add_options_page(
			__( 'AI Intake Settings', 'kaicalls-ai-intake' ),
			__( 'AI Intake', 'kaicalls-ai-intake' ),
			'manage_options',
			'kaicalls-ai-intake-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register API credential settings.
	 */
	public function register_settings() {
		register_setting( KAICALLS_AI_INTAKE_OPTIONS_GROUP, KAICALLS_AI_INTAKE_OPTION_NAME, array( $this, 'sanitize_and_test_connection' ) );
	}

	/**
	 * Sanitize submitted credentials and verify them against KaiCalls.
	 *
	 * @param array<string, mixed> $input Raw settings input.
	 * @return array<string, string>
	 */
	public function sanitize_and_test_connection( $input ) {
		$input = is_array( $input ) ? $input : array();

		$api_key    = isset( $input['api_key'] ) && is_scalar( $input['api_key'] ) ? sanitize_text_field( wp_unslash( $input['api_key'] ) ) : '';
		$public_key = isset( $input['public_key'] ) && is_scalar( $input['public_key'] ) ? sanitize_text_field( wp_unslash( $input['public_key'] ) ) : '';
		$sanitized  = array(
			'api_key'    => $api_key,
			'public_key' => $public_key,
		);

		if ( empty( $api_key ) || empty( $public_key ) ) {
			add_settings_error( 'kaicalls_ai_intake_messages', 'kaicalls_ai_intake_message', __( 'API Key and Public Key cannot be empty.', 'kaicalls-ai-intake' ), 'error' );
			return $sanitized;
		}

		$api_url = trailingslashit( KAICALLS_AI_INTAKE_API_URL ) . 'api/v1/wordpress/auth/verify';

		$response = wp_remote_post(
			$api_url,
			array(
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode(
					array(
						'apiKey'    => $api_key,
						'publicKey' => $public_key,
					)
				),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			add_settings_error(
				'kaicalls_ai_intake_messages',
				'kaicalls_ai_intake_message',
				__( 'Connection failed: ', 'kaicalls-ai-intake' ) . sanitize_text_field( $response->get_error_message() ),
				'error'
			);
			return $sanitized;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( is_array( $body ) && isset( $body['success'] ) && true === $body['success'] ) {
			add_settings_error( 'kaicalls_ai_intake_messages', 'kaicalls_ai_intake_message', __( 'Connection successful!', 'kaicalls-ai-intake' ), 'updated' );
			return $sanitized;
		}

		$error_message = is_array( $body ) && isset( $body['error'] ) && is_scalar( $body['error'] )
			? sanitize_text_field( $body['error'] )
			: __( 'Invalid API credentials.', 'kaicalls-ai-intake' );
		add_settings_error(
			'kaicalls_ai_intake_messages',
			'kaicalls_ai_intake_message',
			__( 'Connection failed: ', 'kaicalls-ai-intake' ) . $error_message,
			'error'
		);

		return $sanitized;
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page() {
		$options    = $this->get_api_options();
		$api_key    = isset( $options['api_key'] ) ? $options['api_key'] : '';
		$public_key = isset( $options['public_key'] ) ? $options['public_key'] : '';
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'AI Intake Settings', 'kaicalls-ai-intake' ); ?></h1>
			<?php settings_errors( 'kaicalls_ai_intake_messages' ); ?>
			<form action="options.php" method="post">
				<?php settings_fields( KAICALLS_AI_INTAKE_OPTIONS_GROUP ); ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php echo esc_html__( 'Public Key', 'kaicalls-ai-intake' ); ?></th>
						<td>
							<input type="text" name="kaicalls_ai_intake_api_details[public_key]" value="<?php echo esc_attr( $public_key ); ?>" class="regular-text" />
							<p class="description"><?php echo esc_html__( 'Your public API identifier (starts with wp_pk_).', 'kaicalls-ai-intake' ); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php echo esc_html__( 'Secret API Key', 'kaicalls-ai-intake' ); ?></th>
						<td>
							<input type="password" name="kaicalls_ai_intake_api_details[api_key]" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text" autocomplete="off" />
							<p class="description"><?php echo esc_html__( 'Your secret API key (starts with wp_sk_).', 'kaicalls-ai-intake' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Save & Test Connection', 'kaicalls-ai-intake' ) ); ?>
			</form>
			<p class="description">
				<?php echo esc_html__( 'Add the lead form to any page or post with this shortcode:', 'kaicalls-ai-intake' ); ?>
				<code>[kaicalls_ai_intake_form]</code>
			</p>
		</div>
		<?php
	}

	/**
	 * Add the dashboard widget for recent intake leads.
	 */
	public function add_dashboard_widget() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'kaicalls_ai_intake_dashboard_widget',
			__( 'Recent AI Intake Leads', 'kaicalls-ai-intake' ),
			array( $this, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render the dashboard widget container.
	 */
	public function render_dashboard_widget() {
		echo '<div id="kaicalls-ai-intake-widget-content"><p>' . esc_html__( 'Loading...', 'kaicalls-ai-intake' ) . '</p></div>';
	}

	/**
	 * Enqueue dashboard widget assets.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( 'index.php' !== $hook || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_enqueue_script( 'kaicalls-ai-intake-admin', plugin_dir_url( __FILE__ ) . 'kaicalls-ai-intake-admin.js', array( 'jquery' ), KAICALLS_AI_INTAKE_VERSION, true );
		wp_localize_script(
			'kaicalls-ai-intake-admin',
			'kaicallsAiIntakeAdmin',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'kaicalls_ai_intake_dashboard_widget_nonce' ),
				'messages' => array(
					/* translators: %d: number of leads captured this week. */
					'leadsThisWeek' => __( '%d leads this week', 'kaicalls-ai-intake' ),
					'noRecentLeads' => __( 'No recent leads.', 'kaicalls-ai-intake' ),
					'unknownLead'   => __( 'Unknown lead', 'kaicalls-ai-intake' ),
					'fetchError'    => __( 'Error fetching data.', 'kaicalls-ai-intake' ),
					'fetchTryLater' => __( 'Error fetching data. Please try again later.', 'kaicalls-ai-intake' ),
				),
			)
		);
	}

	/**
	 * Enqueue frontend form assets when the shortcode is present.
	 */
	public function enqueue_frontend_scripts() {
		global $post;

		if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'kaicalls_ai_intake_form' ) ) {
			return;
		}

		wp_enqueue_style( 'kaicalls-ai-intake-frontend', plugin_dir_url( __FILE__ ) . 'kaicalls-ai-intake.css', array(), KAICALLS_AI_INTAKE_VERSION );
		wp_enqueue_script( 'kaicalls-ai-intake-frontend', plugin_dir_url( __FILE__ ) . 'kaicalls-ai-intake-frontend.js', array( 'jquery' ), KAICALLS_AI_INTAKE_VERSION, true );
		wp_localize_script(
			'kaicalls-ai-intake-frontend',
			'kaicallsAiIntakeFrontend',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'messages' => array(
					'success'         => __( 'Thank you for your submission!', 'kaicalls-ai-intake' ),
					'error'           => __( 'An error occurred. Please try again.', 'kaicalls-ai-intake' ),
					'networkError'    => __( 'A network error occurred. Please try again.', 'kaicalls-ai-intake' ),
					'invalidToken'    => __( 'Invalid security token.', 'kaicalls-ai-intake' ),
					'notConfigured'   => __( 'Plugin not configured.', 'kaicalls-ai-intake' ),
					'nameRequired'    => __( 'Please enter your name.', 'kaicalls-ai-intake' ),
					'emailRequired'   => __( 'Please enter your email address.', 'kaicalls-ai-intake' ),
					'invalidEmail'    => __( 'Please enter a valid email address.', 'kaicalls-ai-intake' ),
					'messageRequired' => __( 'Please enter a message.', 'kaicalls-ai-intake' ),
					'submitting'      => __( 'Submitting your information...', 'kaicalls-ai-intake' ),
					'timeout'         => __( 'Request timed out. Please try again.', 'kaicalls-ai-intake' ),
				),
			)
		);
	}

	/**
	 * Render the public lead intake form.
	 *
	 * @return string
	 */
	public function render_intake_form() {
		ob_start();
		?>
		<form id="kaicalls-ai-intake-form" class="kaicalls-ai-intake-form">
			<?php wp_nonce_field( 'kaicalls_ai_intake_form_nonce', 'kaicalls_ai_intake_nonce' ); ?>
			<div class="form-group">
				<label for="kaicalls_ai_intake_name"><?php echo esc_html__( 'Name', 'kaicalls-ai-intake' ); ?> *</label>
				<input type="text" id="kaicalls_ai_intake_name" name="name" required>
			</div>
			<div class="form-group">
				<label for="kaicalls_ai_intake_email"><?php echo esc_html__( 'Email', 'kaicalls-ai-intake' ); ?> *</label>
				<input type="email" id="kaicalls_ai_intake_email" name="email" required>
			</div>
			<div class="form-group">
				<label for="kaicalls_ai_intake_phone"><?php echo esc_html__( 'Phone', 'kaicalls-ai-intake' ); ?></label>
				<input type="tel" id="kaicalls_ai_intake_phone" name="phone">
			</div>
			<div class="form-group">
				<label for="kaicalls_ai_intake_message"><?php echo esc_html__( 'Message', 'kaicalls-ai-intake' ); ?> *</label>
				<textarea id="kaicalls_ai_intake_message" name="message" rows="4" required></textarea>
			</div>
			<button type="submit" class="kaicalls-ai-intake-submit-btn"><?php echo esc_html__( 'Submit', 'kaicalls-ai-intake' ); ?></button>
			<div id="kaicalls-ai-intake-form-message" class="kaicalls-ai-intake-form-message"></div>
		</form>
		<?php
		return ob_get_clean();
	}

	/**
	 * Handle public lead form AJAX submissions.
	 */
	public function handle_form_submission() {
		if ( ! check_ajax_referer( 'kaicalls_ai_intake_form_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'error' => __( 'Invalid security token.', 'kaicalls-ai-intake' ) ), 403 );
			return;
		}

		$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$phone   = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
		$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

		$field_labels = array(
			'name'    => array(
				'label' => __( 'Name', 'kaicalls-ai-intake' ),
				'value' => $name,
			),
			'email'   => array(
				'label' => __( 'Email', 'kaicalls-ai-intake' ),
				'value' => $email,
			),
			'message' => array(
				'label' => __( 'Message', 'kaicalls-ai-intake' ),
				'value' => $message,
			),
		);

		foreach ( $field_labels as $field_data ) {
			if ( '' === trim( $field_data['value'] ) ) {
				/* translators: %s: form field label. */
				wp_send_json_error( array( 'error' => sprintf( __( 'Field "%s" is required.', 'kaicalls-ai-intake' ), $field_data['label'] ) ), 400 );
				return;
			}
		}

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'error' => __( 'Please enter a valid email address.', 'kaicalls-ai-intake' ) ), 400 );
			return;
		}

		$options    = $this->get_api_options();
		$api_key    = isset( $options['api_key'] ) ? $options['api_key'] : '';
		$public_key = isset( $options['public_key'] ) ? $options['public_key'] : '';

		if ( empty( $api_key ) || empty( $public_key ) ) {
			wp_send_json_error( array( 'error' => __( 'Plugin not configured.', 'kaicalls-ai-intake' ) ), 500 );
			return;
		}

		$lead_data = array(
			'name'    => $name,
			'email'   => $email,
			'phone'   => $phone,
			'message' => $message,
		);

		$api_url    = trailingslashit( KAICALLS_AI_INTAKE_API_URL ) . 'api/v1/wordpress/intake';
		$auth_token = $public_key . ':' . $api_key;

		$response = wp_remote_post(
			$api_url,
			array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $auth_token,
				),
				'body'    => wp_json_encode( $lead_data ),
				'timeout' => 20,
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'error' => sanitize_text_field( $response->get_error_message() ) ), 500 );
			return;
		}

		$body        = json_decode( wp_remote_retrieve_body( $response ), true );
		$status_code = wp_remote_retrieve_response_code( $response );

		if ( $status_code >= 200 && $status_code < 300 && is_array( $body ) && isset( $body['success'] ) && true === $body['success'] ) {
			$lead_id = isset( $body['leadId'] ) && is_scalar( $body['leadId'] ) ? sanitize_text_field( $body['leadId'] ) : null;
			wp_send_json_success( array( 'leadId' => $lead_id ) );
			return;
		}

		$error = is_array( $body ) && isset( $body['error'] ) && is_scalar( $body['error'] ) ? sanitize_text_field( $body['error'] ) : __( 'Failed to submit lead.', 'kaicalls-ai-intake' );
		wp_send_json_error( array( 'error' => $error ), 400 );
	}

	/**
	 * Handle dashboard widget data requests.
	 */
	public function handle_widget_data_request() {
		if ( ! check_ajax_referer( 'kaicalls_ai_intake_dashboard_widget_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'error' => __( 'Invalid security token.', 'kaicalls-ai-intake' ) ), 403 );
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to access this data.', 'kaicalls-ai-intake' ) ), 403 );
			return;
		}

		$options    = $this->get_api_options();
		$api_key    = isset( $options['api_key'] ) ? $options['api_key'] : '';
		$public_key = isset( $options['public_key'] ) ? $options['public_key'] : '';

		if ( empty( $api_key ) || empty( $public_key ) ) {
			wp_send_json_error( array( 'error' => __( 'Plugin not configured.', 'kaicalls-ai-intake' ) ), 400 );
			return;
		}

		$cached_data = get_transient( KAICALLS_AI_INTAKE_WIDGET_TRANSIENT );
		if ( false !== $cached_data ) {
			wp_send_json_success( $cached_data );
			return;
		}

		$api_url    = trailingslashit( KAICALLS_AI_INTAKE_API_URL ) . 'api/v1/wordpress/dashboard-widget';
		$auth_token = $public_key . ':' . $api_key;

		$response = wp_remote_get(
			$api_url,
			array(
				'headers' => array( 'Authorization' => 'Bearer ' . $auth_token ),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'error' => sanitize_text_field( $response->get_error_message() ) ), 500 );
			return;
		}

		$body        = json_decode( wp_remote_retrieve_body( $response ), true );
		$status_code = wp_remote_retrieve_response_code( $response );

		if ( $status_code >= 200 && $status_code < 300 && is_array( $body ) && isset( $body['success'] ) && true === $body['success'] ) {
			$data_to_cache = array(
				'recentLeads'   => $this->sanitize_recent_leads( isset( $body['recentLeads'] ) ? $body['recentLeads'] : array() ),
				'leadsThisWeek' => isset( $body['leadsThisWeek'] ) && is_scalar( $body['leadsThisWeek'] ) ? absint( $body['leadsThisWeek'] ) : 0,
			);
			set_transient( KAICALLS_AI_INTAKE_WIDGET_TRANSIENT, $data_to_cache, HOUR_IN_SECONDS );
			wp_send_json_success( $data_to_cache );
			return;
		}

		$error = is_array( $body ) && isset( $body['error'] ) && is_scalar( $body['error'] ) ? sanitize_text_field( $body['error'] ) : __( 'Could not fetch data.', 'kaicalls-ai-intake' );
		wp_send_json_error( array( 'error' => $error ), 400 );
	}

	/**
	 * Get saved API credentials.
	 *
	 * @return array<string, mixed>
	 */
	private function get_api_options() {
		$options = get_option( KAICALLS_AI_INTAKE_OPTION_NAME, array() );

		return is_array( $options ) ? $options : array();
	}

	/**
	 * Sanitize recent lead data before caching or returning it.
	 *
	 * @param array<int, mixed> $recent_leads Recent leads from the KaiCalls API.
	 * @return array<int, array{name: string, created_at: string}>
	 */
	private function sanitize_recent_leads( $recent_leads ) {
		if ( ! is_array( $recent_leads ) ) {
			return array();
		}

		$sanitized = array();
		foreach ( $recent_leads as $lead ) {
			if ( ! is_array( $lead ) ) {
				continue;
			}

			$sanitized[] = array(
				'name'       => isset( $lead['name'] ) && is_scalar( $lead['name'] ) ? sanitize_text_field( $lead['name'] ) : '',
				'created_at' => isset( $lead['created_at'] ) && is_scalar( $lead['created_at'] ) ? sanitize_text_field( $lead['created_at'] ) : '',
			);
		}

		return $sanitized;
	}
}
