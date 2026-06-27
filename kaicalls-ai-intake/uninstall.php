<?php
/**
 * Uninstall handler for KaiCalls AI Intake.
 *
 * Runs only when the user deletes the plugin from the WordPress admin.
 * Removes the stored API credentials and any cached dashboard data.
 *
 * @package KaiCalls_AI_Intake
 */

// Exit if uninstall is not called from WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'kaicalls_ai_intake_api_details' );
delete_transient( 'kaicalls_ai_intake_widget_data' );
