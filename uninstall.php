<?php
/**
 * Uninstall script.
 *
 * Handles complete cleanup when the plugin is uninstalled through WordPress.
 * Removes all plugin options, transients, scheduled events, and log files.
 *
 * @package ThirdAudience
 * @since   1.0.0
 */

// Exit if uninstall not called from WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clean up all Third Audience plugin data.
 *
 * @since 1.1.0
 */
function ta_uninstall_cleanup() {
	global $wpdb;

	// =========================================================================
	// Delete Plugin Options
	// =========================================================================

	// Core settings.
	$options_to_delete = array(
		// Service configuration.
		'ta_router_url',
		'ta_worker_url',
		'ta_api_key',
		'ta_api_key_encrypted',

		// Cache settings.
		'ta_cache_ttl',

		// Feature settings.
		'ta_enabled_post_types',
		'ta_enable_content_negotiation',
		'ta_enable_discovery_tags',

		// Version tracking.
		'ta_version',
		'ta_db_version',
		'ta_activated_at',

		// SMTP settings.
		'ta_smtp_settings',

		// Notification settings.
		'ta_notification_settings',
		'ta_last_digest_sent',

		// Logger data.
		'ta_recent_errors',
		'ta_error_stats',

		// Bot Analytics.
		'ta_bot_analytics_db_version',
	);

	foreach ( $options_to_delete as $option ) {
		delete_option( $option );
	}

	// =========================================================================
	// Delete Bot Analytics Database Table
	// =========================================================================

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ta_bot_analytics" );

	// =========================================================================
	// Delete Transients
	// =========================================================================

	// Delete all cached markdown transients.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query(
		"DELETE FROM {$wpdb->options}
		WHERE option_name LIKE '_transient_ta_md_%'
		OR option_name LIKE '_transient_timeout_ta_md_%'"
	);

	// Delete notification rate-limiting transients.
	delete_transient( 'ta_error_rate_notified' );
	delete_transient( 'ta_worker_failure_notified' );
	delete_transient( 'ta_cache_issue_notified' );

	// Delete critical error notification transients.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query(
		"DELETE FROM {$wpdb->options}
		WHERE option_name LIKE '_transient_ta_critical_notified_%'
		OR option_name LIKE '_transient_timeout_ta_critical_notified_%'"
	);

	// Delete settings error transients.
	delete_transient( 'settings_errors' );

	// =========================================================================
	// Clear Scheduled Events (Cron)
	// =========================================================================

	wp_clear_scheduled_hook( 'ta_daily_digest_cron' );

	// =========================================================================
	// Delete Log Files
	// =========================================================================

	$upload_dir = wp_upload_dir();
	$log_dir    = $upload_dir['basedir'] . '/third-audience-logs';

	if ( is_dir( $log_dir ) ) {
		// Get all files in the log directory.
		$files = glob( $log_dir . '/*' );

		if ( is_array( $files ) ) {
			foreach ( $files as $file ) {
				if ( is_file( $file ) ) {
					// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
					unlink( $file );
				}
			}
		}

		// Remove the directory itself.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
		rmdir( $log_dir );
	}

	// =========================================================================
	// Clean Up Rewrite Rules
	// =========================================================================

	// Flush rewrite rules to remove our custom rules.
	flush_rewrite_rules();

	// =========================================================================
	// Multisite Cleanup (if applicable)
	// =========================================================================

	if ( is_multisite() ) {
		// Get all site IDs.
		$site_ids = get_sites( array( 'fields' => 'ids' ) );

		foreach ( $site_ids as $site_id ) {
			switch_to_blog( $site_id );

			// Delete options for this site.
			foreach ( $options_to_delete as $option ) {
				delete_option( $option );
			}

			// Delete transients for this site.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->query(
				"DELETE FROM {$wpdb->options}
				WHERE option_name LIKE '_transient_ta_%'
				OR option_name LIKE '_transient_timeout_ta_%'"
			);

			// Clear scheduled events.
			wp_clear_scheduled_hook( 'ta_daily_digest_cron' );

			// Delete bot analytics table.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ta_bot_analytics" );

			// Delete log files for this site.
			$upload_dir = wp_upload_dir();
			$log_dir    = $upload_dir['basedir'] . '/third-audience-logs';

			if ( is_dir( $log_dir ) ) {
				$files = glob( $log_dir . '/*' );
				if ( is_array( $files ) ) {
					foreach ( $files as $file ) {
						if ( is_file( $file ) ) {
							// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
							unlink( $file );
						}
					}
				}
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
				rmdir( $log_dir );
			}

			restore_current_blog();
		}
	}
}

// Run the cleanup.
ta_uninstall_cleanup();
