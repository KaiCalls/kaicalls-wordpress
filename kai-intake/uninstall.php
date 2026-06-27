<?php
/**
 * Uninstall handler for AI Intake for Kai Calls.
 *
 * Runs only when the user deletes the plugin from the WordPress admin.
 * Removes the stored API credentials and any cached dashboard data.
 */

// Exit if uninstall is not called from WordPress.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('kai_intake_api_details');
delete_transient('kai_intake_widget_data');
