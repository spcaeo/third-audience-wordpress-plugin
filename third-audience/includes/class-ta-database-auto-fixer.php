<?php
/**
 * Database Auto-Fixer.
 *
 * Automatically detects and fixes database issues without user intervention.
 * Handles missing tables, columns, indexes, and type mismatches.
 *
 * @package ThirdAudience
 * @since   3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Database_Auto_Fixer
 *
 * Auto-detects and repairs database issues.
 *
 * @since 3.4.0
 */
class TA_Database_Auto_Fixer {

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( class_exists( 'TA_Logger' ) ) {
			$this->logger = TA_Logger::get_instance();
		}
	}

	/**
	 * Fix all database issues automatically.
	 *
	 * @return array Results of each fix operation.
	 */
	public function fix_all_issues() {
		$results = array(
			'tables_created'  => $this->create_missing_tables(),
			'columns_added'   => $this->add_missing_columns(),
			'types_fixed'     => $this->fix_column_types(),
			'indexes_added'   => $this->add_missing_indexes(),
		);

		if ( $this->logger ) {
			$this->logger->info( 'Database auto-fix completed', $results );
		}

		return $results;
	}

	/**
	 * Create any missing tables.
	 *
	 * @return array Results with created table names.
	 */
	private function create_missing_tables() {
		global $wpdb;
		$created = array();

		// Check and create main table.
		$table  = $wpdb->prefix . 'ta_bot_analytics';
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

		if ( $exists !== $table ) {
			if ( $this->create_bot_analytics_table() ) {
				$created[] = $table;
			}
		}

		// Check and create citation alerts table.
		$alerts_table = $wpdb->prefix . 'ta_citation_alerts';
		$exists       = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $alerts_table ) );

		if ( $exists !== $alerts_table ) {
			if ( $this->create_citation_alerts_table() ) {
				$created[] = $alerts_table;
			}
		}

		// Check and create bot patterns table.
		$patterns_table = $wpdb->prefix . 'ta_bot_patterns';
		$exists         = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $patterns_table ) );

		if ( $exists !== $patterns_table ) {
			if ( $this->create_bot_patterns_table() ) {
				$created[] = $patterns_table;
			}
		}

		return $created;
	}

	/**
	 * Create bot analytics table.
	 *
	 * @return bool True if successful.
	 */
	private function create_bot_analytics_table() {
		global $wpdb;

		$table           = $wpdb->prefix . 'ta_bot_analytics';
		$charset_collate = $wpdb->get_charset_collate();

		// Schema must match TA_Bot_Analytics::maybe_create_table() exactly.
		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			bot_type varchar(50) NOT NULL,
			bot_name varchar(100) NOT NULL,
			user_agent text NOT NULL,
			client_user_agent text DEFAULT NULL,
			url varchar(500) NOT NULL,
			post_id bigint(20) unsigned DEFAULT NULL,
			post_type varchar(50) DEFAULT NULL,
			post_title text DEFAULT NULL,
			request_method varchar(20) NOT NULL DEFAULT 'md_url',
			request_type varchar(20) DEFAULT 'unknown',
			cache_status varchar(20) NOT NULL DEFAULT 'MISS',
			response_time int(11) DEFAULT NULL,
			response_size int(11) DEFAULT NULL,
			http_status int(3) DEFAULT NULL,
			ip_address varchar(45) DEFAULT NULL,
			referer text DEFAULT NULL,
			country_code varchar(2) DEFAULT NULL,
			traffic_type varchar(20) DEFAULT 'bot_crawl',
			content_type varchar(20) DEFAULT 'html',
			ai_platform varchar(50) DEFAULT NULL,
			search_query text DEFAULT NULL,
			referer_source varchar(100) DEFAULT NULL,
			referer_medium varchar(50) DEFAULT NULL,
			detection_method varchar(50) DEFAULT 'legacy',
			confidence_score decimal(3,2) DEFAULT NULL,
			ip_verified tinyint(1) DEFAULT NULL,
			ip_verification_method varchar(50) DEFAULT NULL,
			content_word_count int(11) DEFAULT NULL,
			content_heading_count int(11) DEFAULT NULL,
			content_image_count int(11) DEFAULT NULL,
			content_has_schema tinyint(1) DEFAULT 0,
			content_freshness_days int(11) DEFAULT NULL,
			visit_timestamp datetime NOT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY bot_name (bot_name),
			KEY bot_type (bot_type),
			KEY traffic_type (traffic_type),
			KEY visit_timestamp (visit_timestamp),
			KEY ai_platform (ai_platform),
			KEY content_type (content_type)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Verify creation.
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

		if ( $exists === $table ) {
			if ( $this->logger ) {
				$this->logger->info( "Created table: {$table}" );
			}
			return true;
		}

		return false;
	}

	/**
	 * Create citation alerts table.
	 *
	 * @return bool True if successful.
	 */
	private function create_citation_alerts_table() {
		global $wpdb;

		$table           = $wpdb->prefix . 'ta_citation_alerts';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			alert_type varchar(50) NOT NULL,
			platform varchar(50) DEFAULT NULL,
			message text NOT NULL,
			metadata text DEFAULT NULL,
			dismissed tinyint(1) DEFAULT 0,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY alert_type (alert_type),
			KEY dismissed (dismissed),
			KEY created_at (created_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Verify creation.
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

		if ( $exists === $table ) {
			if ( $this->logger ) {
				$this->logger->info( "Created table: {$table}" );
			}
			return true;
		}

		return false;
	}

	/**
	 * Create bot patterns table.
	 *
	 * @return bool True if successful.
	 */
	private function create_bot_patterns_table() {
		global $wpdb;

		$table           = $wpdb->prefix . 'ta_bot_patterns';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			pattern varchar(255) NOT NULL,
			pattern_type enum('exact','regex','contains','ml') NOT NULL DEFAULT 'regex',
			bot_name varchar(100) NOT NULL,
			bot_vendor varchar(100) DEFAULT NULL,
			priority int(11) DEFAULT 100,
			is_ai_bot tinyint(1) DEFAULT 0,
			is_active tinyint(1) DEFAULT 1,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY pattern (pattern),
			KEY bot_name (bot_name),
			KEY is_ai_bot (is_ai_bot),
			KEY is_active (is_active)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Verify creation.
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

		if ( $exists === $table ) {
			if ( $this->logger ) {
				$this->logger->info( "Created table: {$table}" );
			}
			return true;
		}

		return false;
	}

	/**
	 * Add any missing columns to existing tables.
	 *
	 * @return array List of added columns.
	 */
	private function add_missing_columns() {
		global $wpdb;

		$table = $wpdb->prefix . 'ta_bot_analytics';
		$added = array();

		// Check if table exists first.
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $table_exists !== $table ) {
			return $added; // Table doesn't exist yet.
		}

		// Get existing columns.
		$columns = $wpdb->get_col( "SHOW COLUMNS FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// Columns required by the current application code that may be missing
		// from tables created by older plugin versions.
		$required_columns = array(
			'content_type'      => "ALTER TABLE {$table} ADD COLUMN content_type varchar(20) DEFAULT 'html' AFTER traffic_type",
			'client_user_agent' => "ALTER TABLE {$table} ADD COLUMN client_user_agent text DEFAULT NULL AFTER user_agent",
			'request_type'      => "ALTER TABLE {$table} ADD COLUMN request_type varchar(20) DEFAULT 'unknown' AFTER request_method",
			'http_status'       => "ALTER TABLE {$table} ADD COLUMN http_status int(3) DEFAULT NULL AFTER response_size",
		);

		foreach ( $required_columns as $column => $sql ) {
			if ( ! in_array( $column, $columns, true ) ) {
				$result = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

				if ( false !== $result ) {
					$added[] = $column;

					if ( $this->logger ) {
						$this->logger->info( "Added missing column: {$column} to {$table}" );
					}
				} else {
					if ( $this->logger ) {
						$this->logger->error( "Failed to add column: {$column}", array( 'error' => $wpdb->last_error ) );
					}
				}
			}
		}

		return $added;
	}

	/**
	 * Fix column types if incorrect.
	 *
	 * @return array List of fixed columns.
	 */
	private function fix_column_types() {
		global $wpdb;

		$table = $wpdb->prefix . 'ta_bot_analytics';
		$fixed = array();

		// Check if table exists.
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $table_exists !== $table ) {
			return $fixed;
		}

		// Check column definitions.
		$columns = $wpdb->get_results( "SHOW FULL COLUMNS FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		foreach ( $columns as $column ) {
			// Fix content_type if it was created too small (varchar(20) → varchar(50)).
			if ( 'content_type' === $column->Field && stripos( $column->Type, 'varchar(20)' ) !== false ) {
				$result = $wpdb->query( "ALTER TABLE {$table} MODIFY COLUMN content_type varchar(50) DEFAULT 'html'" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

				if ( false !== $result ) {
					$fixed[] = 'content_type';

					if ( $this->logger ) {
						$this->logger->info( 'Fixed content_type column size (20→50)' );
					}
				}
			}

			// Fix url if it's too small (varchar(255) → varchar(500)).
			if ( 'url' === $column->Field && stripos( $column->Type, 'varchar(255)' ) !== false ) {
				$result = $wpdb->query( "ALTER TABLE {$table} MODIFY COLUMN url varchar(500) NOT NULL" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

				if ( false !== $result ) {
					$fixed[] = 'url';

					if ( $this->logger ) {
						$this->logger->info( 'Fixed url column size (255→500)' );
					}
				}
			}
		}

		return $fixed;
	}

	/**
	 * Add missing indexes for performance.
	 *
	 * @return array List of added indexes.
	 */
	private function add_missing_indexes() {
		global $wpdb;

		$table = $wpdb->prefix . 'ta_bot_analytics';
		$added = array();

		// Check if table exists.
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		if ( $table_exists !== $table ) {
			return $added;
		}

		// Get existing indexes.
		$indexes     = $wpdb->get_results( "SHOW INDEX FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$index_names = wp_list_pluck( $indexes, 'Key_name' );

		// Add missing indexes.
		$required_indexes = array(
			'bot_name'       => "ALTER TABLE {$table} ADD KEY bot_name (bot_name)",
			'bot_type'       => "ALTER TABLE {$table} ADD KEY bot_type (bot_type)",
			'content_type'   => "ALTER TABLE {$table} ADD KEY content_type (content_type)",
			'ai_platform'    => "ALTER TABLE {$table} ADD KEY ai_platform (ai_platform)",
			'visit_timestamp' => "ALTER TABLE {$table} ADD KEY visit_timestamp (visit_timestamp)",
		);

		foreach ( $required_indexes as $index_name => $sql ) {
			if ( ! in_array( $index_name, $index_names, true ) ) {
				$result = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

				if ( false !== $result ) {
					$added[] = $index_name;

					if ( $this->logger ) {
						$this->logger->info( "Added missing index: {$index_name}" );
					}
				}
			}
		}

		return $added;
	}

	/**
	 * Verify all fixes were successful.
	 *
	 * @return array Status of each fix.
	 */
	public function verify_fixes() {
		global $wpdb;

		$status = array();

		// Check main table.
		$table                       = $wpdb->prefix . 'ta_bot_analytics';
		$status['main_table_exists'] = ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table );

		if ( $status['main_table_exists'] ) {
			// Check columns.
			$columns = $wpdb->get_col( "SHOW COLUMNS FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			$status['url_exists']                = in_array( 'url', $columns, true );
			$status['visit_timestamp_exists']    = in_array( 'visit_timestamp', $columns, true );
			$status['content_type_exists']       = in_array( 'content_type', $columns, true );
			$status['client_user_agent_exists']  = in_array( 'client_user_agent', $columns, true );
			$status['request_type_exists']       = in_array( 'request_type', $columns, true );
			$status['http_status_exists']        = in_array( 'http_status', $columns, true );
		} else {
			$status['url_exists']               = false;
			$status['visit_timestamp_exists']   = false;
			$status['content_type_exists']      = false;
			$status['client_user_agent_exists'] = false;
			$status['request_type_exists']      = false;
			$status['http_status_exists']       = false;
		}

		// Check citation alerts table.
		$alerts_table                        = $wpdb->prefix . 'ta_citation_alerts';
		$status['citation_alerts_table_exists'] = ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $alerts_table ) ) === $alerts_table );

		return $status;
	}

	/**
	 * Generate SQL commands for manual execution if auto-fix fails.
	 *
	 * @return string SQL commands.
	 */
	public function generate_manual_sql() {
		global $wpdb;

		$table = $wpdb->prefix . 'ta_bot_analytics';

		$sql = "-- Manual SQL commands for Third Audience database fixes\n\n";

		$sql .= "-- 1. Add missing content_type column\n";
		$sql .= "ALTER TABLE {$table} ADD COLUMN content_type varchar(50) DEFAULT 'html' AFTER traffic_type;\n\n";

		$sql .= "-- 2. Add missing is_citation column\n";
		$sql .= "ALTER TABLE {$table} ADD COLUMN is_citation tinyint(1) DEFAULT 0 AFTER cache_hit;\n\n";

		$sql .= "-- 3. Add missing page_title column\n";
		$sql .= "ALTER TABLE {$table} ADD COLUMN page_title text DEFAULT NULL AFTER page_url;\n\n";

		$sql .= "-- 4. Add indexes for performance\n";
		$sql .= "ALTER TABLE {$table} ADD KEY is_citation (is_citation);\n";
		$sql .= "ALTER TABLE {$table} ADD KEY content_type (content_type);\n\n";

		$sql .= "-- 5. Fix column sizes\n";
		$sql .= "ALTER TABLE {$table} MODIFY COLUMN content_type varchar(50) DEFAULT 'html';\n";
		$sql .= "ALTER TABLE {$table} MODIFY COLUMN page_url varchar(500) NOT NULL;\n\n";

		$sql .= "-- 6. Add LLM Traffic tracking columns (v3.5.0)\n";
		$sql .= "ALTER TABLE {$table} ADD COLUMN client_user_agent text DEFAULT NULL AFTER user_agent;\n";
		$sql .= "ALTER TABLE {$table} ADD COLUMN request_type varchar(20) DEFAULT 'unknown' AFTER request_method;\n";

		return $sql;
	}
}
