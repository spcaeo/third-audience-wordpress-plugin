<?php
/**
 * Migration for v3.3.10 - Add content_type column to ta_bot_analytics table
 *
 * Fixes: "Unknown column 'content_type' in 'field list'" error
 *
 * @package ThirdAudience
 * @since   3.3.10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Migration_3_3_10
 *
 * Adds missing content_type column to bot analytics table.
 *
 * @since 3.3.10
 */
class TA_Migration_3_3_10 {

	/**
	 * Run migration.
	 *
	 * @since 3.3.10
	 * @return bool Success status.
	 */
	public static function migrate() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'ta_bot_analytics';

		// Check if table exists first.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$table_exists = $wpdb->get_var(
			$wpdb->prepare(
				'SHOW TABLES LIKE %s',
				$table_name
			)
		);

		if ( ! $table_exists ) {
			if ( class_exists( 'TA_Logger' ) ) {
				TA_Logger::get_instance()->warning( 'Migration 3.3.10: ta_bot_analytics table does not exist.' );
			}
			return false;
		}

		// Check if content_type column already exists.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$column_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA = %s
				AND TABLE_NAME = %s
				AND COLUMN_NAME = 'content_type'",
				DB_NAME,
				$table_name
			)
		);

		if ( ! empty( $column_exists ) ) {
			if ( class_exists( 'TA_Logger' ) ) {
				TA_Logger::get_instance()->info( 'Migration 3.3.10: content_type column already exists.' );
			}
			return true;
		}

		// Add content_type column after traffic_type.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$result = $wpdb->query(
			"ALTER TABLE {$table_name}
			ADD COLUMN content_type VARCHAR(50) DEFAULT 'html'
			AFTER traffic_type"
		);

		if ( false === $result ) {
			if ( class_exists( 'TA_Logger' ) ) {
				TA_Logger::get_instance()->error( 'Migration 3.3.10 failed: ' . $wpdb->last_error );
			}
			return false;
		}

		if ( class_exists( 'TA_Logger' ) ) {
			TA_Logger::get_instance()->info( 'Migration 3.3.10: Successfully added content_type column to ta_bot_analytics table.' );
		}

		return true;
	}
}
