<?php
/**
 * Plugin Name:       KaiCalls AI Intake
 * Plugin URI:        https://www.kaicalls.com/integrations/wordpress
 * Description:       Connects your WordPress site to KaiCalls to capture leads from your forms automatically.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            KaiCalls
 * Author URI:        https://www.kaicalls.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       kaicalls-ai-intake
 *
 * @package KaiCalls_AI_Intake
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'KAICALLS_AI_INTAKE_VERSION', '1.0.0' );
define( 'KAICALLS_AI_INTAKE_API_URL', 'https://www.kaicalls.com' );
define( 'KAICALLS_AI_INTAKE_OPTIONS_GROUP', 'kaicalls_ai_intake_options' );
define( 'KAICALLS_AI_INTAKE_OPTION_NAME', 'kaicalls_ai_intake_api_details' );
define( 'KAICALLS_AI_INTAKE_WIDGET_TRANSIENT', 'kaicalls_ai_intake_widget_data' );

require_once plugin_dir_path( __FILE__ ) . 'class-kaicalls-ai-intake-plugin.php';

new Kaicalls_AI_Intake_Plugin();
