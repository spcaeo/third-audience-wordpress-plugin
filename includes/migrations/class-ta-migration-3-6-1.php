<?php
/**
 * Migration for v3.6.1 - Reclassify mislabelled "Bing AI" traffic as organic "Bing".
 *
 * Earlier versions labelled EVERY click from www.bing.com as "Bing AI" and counted
 * it as LLM/AI traffic. Plain Bing is an ordinary search engine, not AI — only Bing
 * Copilot (the &form=MA… signature) is AI. This migration renames the historical
 * rows (both "Bing AI" and the lowercase "Bing ai" variant) to "Bing" so they move
 * out of LLM Traffic and into the Organic Search section, matching the fixed detector.
 *
 * @package ThirdAudience
 * @since   3.6.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Migration_3_6_1
 *
 * Reclassifies legacy "Bing AI" citation rows to organic "Bing".
 *
 * @since 3.6.1
 */
class TA_Migration_3_6_1 {

	/**
	 * Run migration.
	 *
	 * @since 3.6.1
	 * @return bool Success status.
	 */
	public static function migrate() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'ta_bot_analytics';

		// Bail if the table doesn't exist yet (fresh install in progress).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );
		if ( $table_exists !== $table_name ) {
			if ( class_exists( 'TA_Logger' ) ) {
				TA_Logger::get_instance()->warning( 'Migration 3.6.1: ta_bot_analytics table does not exist.' );
			}
			return false;
		}

		// Rename every casing variant of "Bing AI" to "Bing". LOWER() matches both
		// "Bing AI" and "Bing ai"; rows already named "Bing" are left untouched.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result = $wpdb->query(
			"UPDATE {$table_name} SET ai_platform = 'Bing' WHERE LOWER(ai_platform) = 'bing ai'"
		);

		if ( false === $result ) {
			if ( class_exists( 'TA_Logger' ) ) {
				TA_Logger::get_instance()->error( 'Migration 3.6.1 failed: ' . $wpdb->last_error );
			}
			return false;
		}

		if ( class_exists( 'TA_Logger' ) ) {
			TA_Logger::get_instance()->info( 'Migration 3.6.1: Reclassified ' . (int) $result . ' "Bing AI" row(s) to organic "Bing".' );
		}

		return true;
	}
}
