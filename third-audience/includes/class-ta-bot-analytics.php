<?php
/**
 * Bot Analytics - Tracks AI bot visits and generates reports.
 *
 * Provides comprehensive analytics for AI bot visits including tracking,
 * reporting, filtering, and export functionality without requiring server logs.
 *
 * @package ThirdAudience
 * @since   1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Bot_Analytics
 *
 * Tracks and analyzes AI bot visits to markdown content.
 *
 * @since 1.4.0
 */
class TA_Bot_Analytics {

	/**
	 * Database table name.
	 *
	 * @var string
	 */
	const TABLE_NAME = 'ta_bot_analytics';

	/**
	 * Database version for migrations.
	 *
	 * @var string
	 */
	const DB_VERSION = '3.2.0';

	/**
	 * Option name for database version.
	 *
	 * @var string
	 */
	const DB_VERSION_OPTION = 'ta_bot_analytics_db_version';

	/**
	 * Known AI bot user agents.
	 *
	 * @var array
	 */
	private static $known_bots = array(
		'ClaudeBot'         => array(
			'pattern'  => '/ClaudeBot/i',
			'name'     => 'Claude (Anthropic)',
			'color'    => '#D97757',
			'priority' => 'high',
		),
		'GPTBot'            => array(
			'pattern'  => '/GPTBot/i',
			'name'     => 'GPT (OpenAI)',
			'color'    => '#10A37F',
			'priority' => 'high',
		),
		'ChatGPT-User'      => array(
			'pattern'  => '/ChatGPT-User/i',
			'name'     => 'ChatGPT User',
			'color'    => '#10A37F',
			'priority' => 'high',
		),
		'PerplexityBot'     => array(
			'pattern'  => '/PerplexityBot/i',
			'name'     => 'Perplexity',
			'color'    => '#1FB6D0',
			'priority' => 'high',
		),
		'Bytespider'        => array(
			'pattern'  => '/Bytespider/i',
			'name'     => 'ByteDance AI',
			'color'    => '#FF4458',
			'priority' => 'medium',
		),
		'anthropic-ai'      => array(
			'pattern'  => '/anthropic-ai/i',
			'name'     => 'Anthropic AI',
			'color'    => '#D97757',
			'priority' => 'high',
		),
		'cohere-ai'         => array(
			'pattern'  => '/cohere-ai/i',
			'name'     => 'Cohere',
			'color'    => '#39594D',
			'priority' => 'medium',
		),
		'Google-Extended'   => array(
			'pattern'  => '/Google-Extended/i',
			'name'     => 'Google Gemini',
			'color'    => '#4285F4',
			'priority' => 'medium',
		),
		'FacebookBot'       => array(
			'pattern'  => '/FacebookBot/i',
			'name'     => 'Meta AI',
			'color'    => '#1877F2',
			'priority' => 'medium',
		),
		'Applebot-Extended' => array(
			'pattern'  => '/Applebot-Extended/i',
			'name'     => 'Apple Intelligence',
			'color'    => '#000000',
			'priority' => 'medium',
		),
	);

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Webhooks instance.
	 *
	 * @var TA_Webhooks
	 */
	private $webhooks;

	/**
	 * Detection pipeline instance.
	 *
	 * @var TA_Bot_Detection_Pipeline|null
	 */
	private $pipeline = null;

	/**
	 * IP Verifier instance.
	 *
	 * @var TA_IP_Verifier|null
	 */
	private $ip_verifier = null;

	/**
	 * Singleton instance.
	 *
	 * @var TA_Bot_Analytics|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 1.4.0
	 * @return TA_Bot_Analytics
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.4.0
	 */
	private function __construct() {
		global $wpdb;

		$this->logger = TA_Logger::get_instance();
		$this->webhooks = TA_Webhooks::get_instance();

		// Initialize detection pipeline if classes exist.
		if ( class_exists( 'TA_Bot_Detection_Pipeline' ) &&
		     class_exists( 'TA_Known_Pattern_Detector' ) &&
		     class_exists( 'TA_Heuristic_Detector' ) ) {
			$known_detector     = new TA_Known_Pattern_Detector( $wpdb );
			$heuristic_detector = new TA_Heuristic_Detector();
			$this->pipeline     = new TA_Bot_Detection_Pipeline( $known_detector, $heuristic_detector );
		}

		// Initialize IP verifier.
		if ( class_exists( 'TA_IP_Verifier' ) ) {
			$this->ip_verifier = TA_IP_Verifier::get_instance();
		}

		$this->maybe_create_table();
		$this->maybe_migrate_patterns();

		// Hook into template_redirect to track AI citation clicks.
		add_action( 'template_redirect', array( $this, 'maybe_track_citation_click' ), 5 );

		// Hook to track ALL bot crawls on every page (like FieldCamp's middleware).
		add_action( 'template_redirect', array( $this, 'maybe_track_bot_crawl' ), 1 );
	}

	/**
	 * Maybe track AI citation click on page load.
	 *
	 * Runs on every page load to check if the visitor came from an AI platform.
	 *
	 * @since 2.2.0
	 * @return void
	 */
	public function maybe_track_citation_click() {
		// Only track on front-end (not admin).
		if ( is_admin() ) {
			return;
		}

		// Only track GET requests (not POST/form submissions).
		if ( 'GET' !== $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		// Track citation click if detected.
		$this->track_citation_click();
	}

	/**
	 * Track bot crawl on any page visit (HTML or .md).
	 *
	 * Runs on EVERY page request and checks if the visitor is a known AI bot.
	 * Similar to FieldCamp's Next.js middleware approach.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	public function maybe_track_bot_crawl() {
		// Skip admin pages.
		if ( is_admin() ) {
			return;
		}

		// Skip AJAX requests.
		if ( wp_doing_ajax() ) {
			return;
		}

		// Skip cron requests.
		if ( wp_doing_cron() ) {
			return;
		}

		// Skip REST API requests.
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}

		// Skip if already tracked via .md request (avoid duplicates).
		if ( did_action( 'ta_bot_visit_tracked' ) ) {
			return;
		}

		// Get user agent.
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

		if ( empty( $user_agent ) ) {
			return;
		}

		// Detect if this is a known bot.
		$bot_info = $this->detect_bot( $user_agent );

		if ( ! $bot_info ) {
			return; // Not a bot, skip tracking.
		}

		// Get current URL and determine content type (HTML vs .md).
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$is_markdown = ( substr( $request_uri, -3 ) === '.md' );
		$content_type = $is_markdown ? 'markdown' : 'html';

		// Get post info if available.
		$post_id    = get_the_ID();
		$post_title = $post_id ? get_the_title( $post_id ) : '';

		// Get client IP (handles proxies like FieldCamp).
		$ip_address = $this->get_bot_client_ip();

		// Prepare tracking data.
		$tracking_data = array(
			'bot_type'         => $bot_info['bot_type'],
			'bot_name'         => $bot_info['name'],
			'user_agent'       => $user_agent,
			'url'              => home_url( $request_uri ),
			'post_id'          => $post_id ?: null,
			'post_title'       => $post_title,
			'ip_address'       => $ip_address,
			'cache_status'     => strtoupper( $content_type ), // 'HTML' or 'MARKDOWN'
			'response_time'    => 0,
			'traffic_type'     => 'bot_crawl',
			'content_type'     => $content_type, // Track HTML vs markdown
			'detection_method' => $bot_info['detection_method'] ?? 'pattern',
			'confidence_score' => $bot_info['confidence'] ?? 1.0,
		);

		// Track the visit.
		$result = $this->track_visit( $tracking_data );

		if ( $result ) {
			// Fire action to prevent duplicate tracking.
			do_action( 'ta_bot_visit_tracked' );

			$this->logger->debug( 'Bot crawl tracked.', array(
				'bot'          => $bot_info['name'],
				'url'          => $request_uri,
				'content_type' => $content_type,
			) );
		}
	}

	/**
	 * Get client IP address, handling proxies (like FieldCamp).
	 *
	 * @since 3.2.0
	 * @return string IP address.
	 */
	private function get_bot_client_ip() {
		$ip = '';

		// Check for proxy headers.
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			$ip  = trim( $ips[0] );
		} elseif ( ! empty( $_SERVER['HTTP_X_REAL_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) );
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return $ip ?: 'unknown';
	}

	/**
	 * Create database table if it doesn't exist.
	 *
	 * @since 1.4.0
	 * @return void
	 */
	private function maybe_create_table() {
		$installed_version = get_option( self::DB_VERSION_OPTION, '0.0.0' );

		if ( version_compare( $installed_version, self::DB_VERSION, '>=' ) ) {
			return;
		}

		global $wpdb;
		$table_name      = $wpdb->prefix . self::TABLE_NAME;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			bot_type varchar(50) NOT NULL,
			bot_name varchar(100) NOT NULL,
			user_agent text NOT NULL,
			url varchar(500) NOT NULL,
			post_id bigint(20) unsigned DEFAULT NULL,
			post_type varchar(50) DEFAULT NULL,
			post_title text DEFAULT NULL,
			request_method varchar(20) NOT NULL DEFAULT 'md_url',
			cache_status varchar(20) NOT NULL DEFAULT 'MISS',
			response_time int(11) DEFAULT NULL,
			response_size int(11) DEFAULT NULL,
			ip_address varchar(45) DEFAULT NULL,
			referer text DEFAULT NULL,
			country_code varchar(2) DEFAULT NULL,
			traffic_type varchar(20) DEFAULT 'bot_crawl',
			ai_platform varchar(50) DEFAULT NULL,
			search_query text DEFAULT NULL,
			referer_source varchar(100) DEFAULT NULL,
			referer_medium varchar(50) DEFAULT NULL,
			visit_timestamp datetime NOT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY bot_type (bot_type),
			KEY post_id (post_id),
			KEY visit_timestamp (visit_timestamp),
			KEY bot_type_timestamp (bot_type, visit_timestamp),
			KEY traffic_type (traffic_type),
			KEY ai_platform (ai_platform)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Migration for v1.2.0: Create dynamic bot detection tables and add detection columns.
		if ( version_compare( $installed_version, '1.2.0', '<' ) ) {
			$this->create_bot_detection_tables( $charset_collate );

			// Add detection_method and confidence_score columns to analytics table.
			$migration_success_v12 = true;

			$columns_to_add_v12 = array(
				'detection_method' => "ALTER TABLE {$table_name} ADD COLUMN detection_method varchar(50) DEFAULT 'legacy' AFTER referer_medium",
				'confidence_score' => "ALTER TABLE {$table_name} ADD COLUMN confidence_score decimal(3,2) DEFAULT NULL AFTER detection_method",
			);

			foreach ( $columns_to_add_v12 as $column_name => $alter_sql ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$column_exists = $wpdb->get_results(
					"SHOW COLUMNS FROM {$table_name} LIKE '{$column_name}'"
				);

				if ( empty( $column_exists ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
					$result = $wpdb->query( $alter_sql );

					if ( false === $result ) {
						$migration_success_v12 = false;
						$this->logger->error( "Failed to add column {$column_name}", array(
							'error' => $wpdb->last_error,
						) );
					}
				}
			}

			// Add index for detection_method.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$index_exists = $wpdb->get_results(
				"SHOW INDEX FROM {$table_name} WHERE Key_name = 'detection_method'"
			);

			if ( empty( $index_exists ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$result = $wpdb->query( "ALTER TABLE {$table_name} ADD INDEX detection_method (detection_method)" );

				if ( false === $result ) {
					$migration_success_v12 = false;
					$this->logger->error( 'Failed to add index detection_method', array(
						'error' => $wpdb->last_error,
					) );
				}
			}

			if ( ! $migration_success_v12 ) {
				$this->logger->error( 'Migration to v1.2.0 failed for detection columns.' );
			}
		}

		// Migration for v2.7.0: Add IP verification columns.
		if ( version_compare( $installed_version, '2.7.0', '<' ) ) {
			$migration_success_v27 = true;

			$columns_to_add_v27 = array(
				'ip_verified'            => "ALTER TABLE {$table_name} ADD COLUMN ip_verified tinyint(1) DEFAULT NULL AFTER confidence_score",
				'ip_verification_method' => "ALTER TABLE {$table_name} ADD COLUMN ip_verification_method varchar(50) DEFAULT NULL AFTER ip_verified",
				'content_word_count'     => "ALTER TABLE {$table_name} ADD COLUMN content_word_count int(11) DEFAULT NULL AFTER ip_verification_method",
				'content_heading_count'  => "ALTER TABLE {$table_name} ADD COLUMN content_heading_count int(11) DEFAULT NULL AFTER content_word_count",
				'content_image_count'    => "ALTER TABLE {$table_name} ADD COLUMN content_image_count int(11) DEFAULT NULL AFTER content_heading_count",
				'content_has_schema'     => "ALTER TABLE {$table_name} ADD COLUMN content_has_schema tinyint(1) DEFAULT 0 AFTER content_image_count",
				'content_freshness_days' => "ALTER TABLE {$table_name} ADD COLUMN content_freshness_days int(11) DEFAULT NULL AFTER content_has_schema",
			);

			foreach ( $columns_to_add_v27 as $column_name => $alter_sql ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$column_exists = $wpdb->get_results(
					"SHOW COLUMNS FROM {$table_name} LIKE '{$column_name}'"
				);

				if ( empty( $column_exists ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
					$result = $wpdb->query( $alter_sql );

					if ( false === $result ) {
						$migration_success_v27 = false;
						$this->logger->error( "Failed to add column {$column_name}", array(
							'error' => $wpdb->last_error,
						) );
					}
				}
			}

			// Add index for ip_verified.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$index_exists = $wpdb->get_results(
				"SHOW INDEX FROM {$table_name} WHERE Key_name = 'ip_verified'"
			);

			if ( empty( $index_exists ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$result = $wpdb->query( "ALTER TABLE {$table_name} ADD INDEX ip_verified (ip_verified)" );

				if ( false === $result ) {
					$migration_success_v27 = false;
					$this->logger->error( 'Failed to add index ip_verified', array(
						'error' => $wpdb->last_error,
					) );
				}
			}

			if ( $migration_success_v27 ) {
				$this->logger->info( 'Migration to v2.7.0 completed successfully (IP verification).' );
			} else {
				$this->logger->error( 'Migration to v2.7.0 failed for IP verification columns.' );
			}
		}

		// Migration for v3.2.0: Add content_type column for HTML vs Markdown tracking.
		if ( version_compare( $installed_version, '3.2.0', '<' ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$column_exists = $wpdb->get_results(
				"SHOW COLUMNS FROM {$table_name} LIKE 'content_type'"
			);

			if ( empty( $column_exists ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
				$result = $wpdb->query(
					"ALTER TABLE {$table_name} ADD COLUMN content_type varchar(20) DEFAULT 'html' AFTER traffic_type"
				);

				if ( false === $result ) {
					$this->logger->error( 'Migration to v3.2.0 failed: could not add content_type column.', array(
						'error' => $wpdb->last_error,
					) );
				} else {
					// Add index for content_type filtering.
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->query( "ALTER TABLE {$table_name} ADD INDEX content_type (content_type)" );

					$this->logger->info( 'Migration to v3.2.0 completed: content_type column added.' );
				}
			}
		}

		// Migration for v1.1.0: Add AI citation tracking columns if upgrading from v1.0.0
		if ( version_compare( $installed_version, '1.1.0', '<' ) ) {
			$migration_success = true;

			// Check if columns exist before adding (prevents errors on fresh installs)
			$columns_to_add = array(
				'traffic_type'    => "ALTER TABLE {$table_name} ADD COLUMN traffic_type varchar(20) DEFAULT 'bot_crawl' AFTER country_code",
				'ai_platform'     => "ALTER TABLE {$table_name} ADD COLUMN ai_platform varchar(50) DEFAULT NULL AFTER traffic_type",
				'search_query'    => "ALTER TABLE {$table_name} ADD COLUMN search_query text DEFAULT NULL AFTER ai_platform",
				'referer_source'  => "ALTER TABLE {$table_name} ADD COLUMN referer_source varchar(100) DEFAULT NULL AFTER search_query",
				'referer_medium'  => "ALTER TABLE {$table_name} ADD COLUMN referer_medium varchar(50) DEFAULT NULL AFTER referer_source",
			);

			foreach ( $columns_to_add as $column_name => $alter_sql ) {
				// Check if column exists using compatible syntax
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$column_exists = $wpdb->get_results(
					"SHOW COLUMNS FROM {$table_name} LIKE '{$column_name}'"
				);

				if ( empty( $column_exists ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
					$result = $wpdb->query( $alter_sql );

					if ( false === $result ) {
						$migration_success = false;
						$this->logger->error( "Failed to add column {$column_name}", array(
							'error' => $wpdb->last_error,
						) );
					}
				}
			}

			// Add indexes for new columns (compatible with MySQL 5.6+)
			$indexes_to_add = array(
				'traffic_type' => 'traffic_type',
				'ai_platform'  => 'ai_platform',
			);

			foreach ( $indexes_to_add as $index_name => $column_name ) {
				// Check if index exists (compatible with all MySQL versions)
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$index_exists = $wpdb->get_results(
					"SHOW INDEX FROM {$table_name} WHERE Key_name = '{$index_name}'"
				);

				if ( empty( $index_exists ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$result = $wpdb->query( "ALTER TABLE {$table_name} ADD INDEX {$index_name} ({$column_name})" );

					if ( false === $result ) {
						$migration_success = false;
						$this->logger->error( "Failed to add index {$index_name}", array(
							'error' => $wpdb->last_error,
						) );
					}
				}
			}

			// Only update version if migration succeeded
			if ( $migration_success ) {
				update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
				$this->logger->info( 'Migration to v1.1.0 completed successfully.' );
			} else {
				$this->logger->error( 'Migration to v1.1.0 failed. Will retry on next page load.' );
				return; // Don't update version, migration will retry
			}
		} else {
			// No migration needed, just update version
			update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
		}

		$this->logger->info( 'Bot analytics table created/updated.', array(
			'version' => self::DB_VERSION,
		) );
	}

	/**
	 * Create bot detection tables for dynamic pattern management.
	 *
	 * Creates 4 new tables for the dynamic bot detection system:
	 * - wp_ta_bot_patterns: Bot detection patterns (replaces hardcoded patterns)
	 * - wp_ta_unknown_bots: Unknown user agents awaiting classification
	 * - wp_ta_bot_db_sync: External bot database sync status
	 * - wp_ta_bot_fingerprints: Behavioral analysis data
	 *
	 * @since 1.2.0
	 * @param string $charset_collate Database charset collation.
	 * @return void
	 */
	private function create_bot_detection_tables( $charset_collate ) {
		global $wpdb;

		// Table 1: Bot Patterns - Dynamic bot detection patterns.
		$patterns_table = $wpdb->prefix . 'ta_bot_patterns';
		$sql_patterns = "CREATE TABLE IF NOT EXISTS {$patterns_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			pattern varchar(255) NOT NULL,
			pattern_type enum('exact','regex','contains','ml') NOT NULL DEFAULT 'regex',
			bot_name varchar(100) NOT NULL,
			bot_vendor varchar(100) DEFAULT NULL,
			bot_category enum('ai','search','social','seo','monitoring','other') DEFAULT NULL,
			priority varchar(20) NOT NULL DEFAULT 'medium',
			color varchar(7) DEFAULT '#999999',
			confidence_score decimal(3,2) DEFAULT 1.00,
			source enum('manual','heuristic','external_db','ml','community') NOT NULL DEFAULT 'manual',
			source_version varchar(50) DEFAULT NULL,
			is_active tinyint(1) NOT NULL DEFAULT 1,
			first_seen datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			last_seen datetime DEFAULT NULL,
			visit_count int(11) DEFAULT 0,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY pattern (pattern(100)),
			KEY is_active (is_active),
			KEY bot_category (bot_category),
			KEY confidence_score (confidence_score)
		) {$charset_collate};";

		// Table 2: Unknown Bots - User agents awaiting classification.
		$unknown_bots_table = $wpdb->prefix . 'ta_unknown_bots';
		$sql_unknown = "CREATE TABLE IF NOT EXISTS {$unknown_bots_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_agent text NOT NULL,
			user_agent_hash varchar(64) NOT NULL,
			first_seen datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			last_seen datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			visit_count int(11) NOT NULL DEFAULT 1,
			classification_status varchar(20) NOT NULL DEFAULT 'pending',
			suggested_bot_name varchar(100) DEFAULT NULL,
			confidence_score decimal(3,2) DEFAULT NULL,
			ip_addresses text DEFAULT NULL,
			referers text DEFAULT NULL,
			visited_urls text DEFAULT NULL,
			notes text DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY user_agent_hash (user_agent_hash),
			KEY classification_status (classification_status),
			KEY last_seen (last_seen),
			KEY visit_count (visit_count)
		) {$charset_collate};";

		// Table 3: Bot DB Sync - External database sync status.
		$sync_table = $wpdb->prefix . 'ta_bot_db_sync';
		$sql_sync = "CREATE TABLE IF NOT EXISTS {$sync_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			source_name varchar(100) NOT NULL,
			source_url varchar(500) NOT NULL,
			last_sync_at datetime DEFAULT NULL,
			next_sync_at datetime DEFAULT NULL,
			sync_frequency varchar(20) NOT NULL DEFAULT 'daily',
			sync_status varchar(20) NOT NULL DEFAULT 'pending',
			patterns_added int(11) DEFAULT 0,
			patterns_updated int(11) DEFAULT 0,
			error_message text DEFAULT NULL,
			is_active tinyint(1) NOT NULL DEFAULT 1,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY source_name (source_name),
			KEY sync_status (sync_status),
			KEY is_active (is_active),
			KEY next_sync_at (next_sync_at)
		) {$charset_collate};";

		// Table 4: Bot Fingerprints - Behavioral analysis data.
		$fingerprints_table = $wpdb->prefix . 'ta_bot_fingerprints';
		$sql_fingerprints = "CREATE TABLE IF NOT EXISTS {$fingerprints_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			fingerprint_hash varchar(64) NOT NULL,
			user_agent text NOT NULL,
			ip_address varchar(45) NOT NULL,
			request_interval_avg int(11) DEFAULT NULL,
			request_interval_stddev int(11) DEFAULT NULL,
			pages_per_session_avg decimal(5,2) DEFAULT NULL,
			session_duration_avg int(11) DEFAULT NULL,
			unique_paths_ratio decimal(3,2) DEFAULT NULL,
			robots_txt_checked tinyint(1) DEFAULT 0,
			respects_robots_txt tinyint(1) DEFAULT NULL,
			http_accept_header text DEFAULT NULL,
			http_accept_language text DEFAULT NULL,
			javascript_enabled tinyint(1) DEFAULT 0,
			cookies_enabled tinyint(1) DEFAULT 0,
			first_seen datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			last_seen datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			visit_count int(11) NOT NULL DEFAULT 1,
			bot_score decimal(3,2) DEFAULT NULL,
			classification varchar(50) DEFAULT 'unknown',
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY fingerprint_hash (fingerprint_hash),
			KEY ip_address (ip_address),
			KEY classification (classification),
			KEY last_seen (last_seen),
			KEY bot_score (bot_score)
		) {$charset_collate};";

		// Execute table creation.
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_patterns );
		dbDelta( $sql_unknown );
		dbDelta( $sql_sync );
		dbDelta( $sql_fingerprints );

		// Log migration completion.
		$this->logger->info( 'Bot detection tables created.', array(
			'tables' => array(
				$patterns_table,
				$unknown_bots_table,
				$sync_table,
				$fingerprints_table,
			),
		) );
	}

	/**
	 * Detect bot from user agent.
	 *
	 * Uses the detection pipeline if available, otherwise falls back to legacy detection.
	 * Returns array format for backward compatibility.
	 *
	 * @since 1.4.0
	 * @param string $user_agent The user agent string.
	 * @return array|false Bot information or false if not a known bot.
	 */
	public function detect_bot( $user_agent ) {
		if ( empty( $user_agent ) ) {
			return false;
		}

		// Use pipeline if available (new system).
		if ( null !== $this->pipeline ) {
			$detection_result = $this->pipeline->detect( $user_agent );

			if ( ! $detection_result->is_bot() ) {
				return false;
			}

			// Convert pipeline result to legacy array format for backward compatibility.
			$bot_name = $detection_result->get_bot_name();

			// Map bot name to known bot type for color.
			$bot_type = $this->get_bot_type_from_name( $bot_name );
			$color    = $this->get_bot_color( $bot_type );
			$priority = $this->get_bot_priority( $bot_type, 'medium' );

			return array(
				'type'     => $bot_type,
				'name'     => $bot_name,
				'color'    => $color,
				'priority' => $priority,
			);
		}

		// Legacy detection (fallback if pipeline not available).
		// Check known bots first.
		foreach ( self::$known_bots as $bot_type => $bot_info ) {
			if ( preg_match( $bot_info['pattern'], $user_agent ) ) {
				// Get custom priority from config, fallback to default.
				$priority = $this->get_bot_priority( $bot_type, $bot_info['priority'] );

				return array(
					'type'     => $bot_type,
					'name'     => $bot_info['name'],
					'color'    => $bot_info['color'],
					'priority' => $priority,
				);
			}
		}

		// Check custom bot patterns.
		$bot_config  = get_option( 'ta_bot_config', array() );
		$custom_bots = isset( $bot_config['custom_bots'] ) ? $bot_config['custom_bots'] : array();

		foreach ( $custom_bots as $custom_bot ) {
			if ( empty( $custom_bot['pattern'] ) || empty( $custom_bot['name'] ) ) {
				continue;
			}

			// Safely check pattern (suppress errors for invalid regex).
			$pattern = $custom_bot['pattern'];
			if ( @preg_match( $pattern, $user_agent ) ) {
				$bot_type = 'Custom_' . sanitize_title( $custom_bot['name'] );
				$priority = $this->get_bot_priority( $bot_type, 'low' );

				return array(
					'type'     => $bot_type,
					'name'     => $custom_bot['name'],
					'color'    => '#8B5CF6', // Purple color for custom bots.
					'priority' => $priority,
				);
			}
		}

		return false;
	}

	/**
	 * Get bot detection result object (new pipeline method).
	 *
	 * Returns detailed detection information including confidence score.
	 *
	 * @since 2.3.0
	 * @param string $user_agent The user agent string.
	 * @return TA_Bot_Detection_Result|null Detection result or null if pipeline unavailable.
	 */
	public function get_bot_detection_result( $user_agent ) {
		if ( null === $this->pipeline ) {
			return null;
		}

		return $this->pipeline->detect( $user_agent );
	}

	/**
	 * Get bot type from bot name.
	 *
	 * Maps bot names back to their types for backward compatibility.
	 *
	 * @since 2.3.0
	 * @param string $bot_name Bot name.
	 * @return string Bot type.
	 */
	private function get_bot_type_from_name( $bot_name ) {
		// Map known bot names to types.
		$name_to_type_map = array(
			'Claude (Anthropic)' => 'ClaudeBot',
			'GPT (OpenAI)'       => 'GPTBot',
			'ChatGPT User'       => 'ChatGPT-User',
			'Perplexity'         => 'PerplexityBot',
			'ByteDance AI'       => 'Bytespider',
			'Anthropic AI'       => 'anthropic-ai',
			'Cohere'             => 'cohere-ai',
			'Google Gemini'      => 'Google-Extended',
			'Meta AI'            => 'FacebookBot',
			'Apple Intelligence' => 'Applebot-Extended',
		);

		return $name_to_type_map[ $bot_name ] ?? 'Custom_' . sanitize_title( $bot_name );
	}

	/**
	 * Get bot color by type.
	 *
	 * @since 2.3.0
	 * @param string $bot_type Bot type.
	 * @return string Hex color code.
	 */
	private function get_bot_color( $bot_type ) {
		if ( isset( self::$known_bots[ $bot_type ]['color'] ) ) {
			return self::$known_bots[ $bot_type ]['color'];
		}

		// Default color for unknown/custom bots.
		return '#8B5CF6';
	}

	/**
	 * Check if a bot type is blocked.
	 *
	 * @since 1.5.0
	 * @param string $bot_type The bot type to check.
	 * @return bool True if blocked, false otherwise.
	 */
	public function is_bot_blocked( $bot_type ) {
		$bot_config   = get_option( 'ta_bot_config', array() );
		$blocked_bots = isset( $bot_config['blocked_bots'] ) ? $bot_config['blocked_bots'] : array();

		// Check if bot is explicitly blocked.
		if ( in_array( $bot_type, $blocked_bots, true ) ) {
			return true;
		}

		// Check if bot priority is set to 'blocked'.
		$priority = $this->get_bot_priority( $bot_type );
		return 'blocked' === $priority;
	}

	/**
	 * Migrate hardcoded bot patterns to database.
	 *
	 * Runs once to migrate the $known_bots array to wp_ta_bot_patterns table.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function maybe_migrate_patterns() {
		// Check if migration already done.
		if ( get_option( 'ta_bot_patterns_migrated', false ) ) {
			return;
		}

		global $wpdb;
		$patterns_table = $wpdb->prefix . 'ta_bot_patterns';

		// Check if table exists.
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$patterns_table}'" );
		if ( ! $table_exists ) {
			return;
		}

		// Migrate each known bot pattern.
		$migrated_count = 0;
		foreach ( self::$known_bots as $bot_type => $bot_info ) {
			// Use pattern as-is (already a regex).
			$pattern = $bot_info['pattern'];

			// Extract vendor from bot name.
			$bot_vendor = $this->get_vendor_from_name( $bot_info['name'] );

			// Determine category based on bot type.
			$category_map = array(
				'ClaudeBot'         => 'ai',
				'GPTBot'            => 'ai',
				'ChatGPT-User'      => 'ai',
				'PerplexityBot'     => 'ai',
				'Bytespider'        => 'ai',
				'anthropic-ai'      => 'ai',
				'cohere-ai'         => 'ai',
				'Google-Extended'   => 'ai',
				'FacebookBot'       => 'social',
				'Applebot-Extended' => 'ai',
			);
			$bot_category = $category_map[ $bot_type ] ?? 'other';

			// Map to new structure.
			$insert_data = array(
				'pattern'       => $pattern,
				'pattern_type'  => 'regex',
				'bot_name'      => $bot_info['name'],
				'bot_vendor'    => $bot_vendor,
				'bot_category'  => $bot_category,
				'priority'      => $bot_info['priority'],
				'color'         => $bot_info['color'],
				'source'        => 'manual',
				'is_active'     => 1,
				'first_seen'    => current_time( 'mysql' ),
			);

			// Add pattern.
			$result = $wpdb->insert( $patterns_table, $insert_data );
			if ( $result ) {
				$migrated_count++;
			}
		}

		// Mark migration as complete.
		update_option( 'ta_bot_patterns_migrated', true );

		$this->logger->info( 'Bot patterns migrated to database.', array(
			'count' => $migrated_count,
		) );
	}

	/**
	 * Extract vendor name from bot name.
	 *
	 * @since 2.3.0
	 * @param string $bot_name Bot name.
	 * @return string Vendor name.
	 */
	private function get_vendor_from_name( $bot_name ) {
		// Extract vendor from name (text in parentheses).
		if ( preg_match( '/\(([^)]+)\)/', $bot_name, $matches ) ) {
			return $matches[1];
		}

		// Default vendors.
		$vendor_map = array(
			'Claude'    => 'Anthropic',
			'GPT'       => 'OpenAI',
			'ChatGPT'   => 'OpenAI',
			'Perplexity' => 'Perplexity',
			'ByteDance' => 'ByteDance',
			'Google'    => 'Google',
			'Meta'      => 'Meta',
			'Apple'     => 'Apple',
		);

		foreach ( $vendor_map as $key => $vendor ) {
			if ( stripos( $bot_name, $key ) !== false ) {
				return $vendor;
			}
		}

		return 'Unknown';
	}

	/**
	 * Get bot priority level.
	 *
	 * @since 2.1.0
	 * @param string      $bot_type        The bot type.
	 * @param string|null $default_priority Default priority if not set.
	 * @return string Priority level: 'high', 'medium', 'low', or 'blocked'.
	 */
	public function get_bot_priority( $bot_type, $default_priority = 'medium' ) {
		$bot_config     = get_option( 'ta_bot_config', array() );
		$bot_priorities = isset( $bot_config['bot_priorities'] ) ? $bot_config['bot_priorities'] : array();

		// Return custom priority if set, otherwise return default.
		if ( isset( $bot_priorities[ $bot_type ] ) ) {
			return $bot_priorities[ $bot_type ];
		}

		return $default_priority;
	}

	/**
	 * Get cache TTL based on bot priority.
	 *
	 * @since 2.1.0
	 * @param string $priority Bot priority level.
	 * @return int Cache TTL in seconds.
	 */
	public static function get_cache_ttl_for_priority( $priority ) {
		$ttl_map = array(
			'high'    => 48 * HOUR_IN_SECONDS, // 48 hours.
			'medium'  => 24 * HOUR_IN_SECONDS, // 24 hours.
			'low'     => 6 * HOUR_IN_SECONDS,  // 6 hours.
			'blocked' => 0,                     // No cache for blocked bots.
		);

		return isset( $ttl_map[ $priority ] ) ? $ttl_map[ $priority ] : 24 * HOUR_IN_SECONDS;
	}

	/**
	 * Track a bot visit.
	 *
	 * @since 1.4.0
	 * @param array $data Visit data.
	 * @return int|false Insert ID or false on failure.
	 */
	public function track_visit( $data ) {
		global $wpdb;

		// Validate required fields.
		if ( empty( $data['bot_type'] ) || empty( $data['url'] ) ) {
			$this->logger->warning( 'Invalid bot tracking data.', $data );
			return false;
		}

		$table_name = $wpdb->prefix . self::TABLE_NAME;

		// Get IP address and lookup location.
		$ip_address   = $this->get_client_ip();
		$country_code = $ip_address ? $this->get_geolocation( $ip_address ) : null;

		// Verify bot IP (if verifier is available and IP exists).
		$ip_verification = array( 'verified' => null, 'method' => null );
		if ( null !== $this->ip_verifier && $ip_address ) {
			$ip_verification = $this->ip_verifier->verify_bot_ip( $data['bot_type'], $ip_address );
		}

		$insert_data = array(
			'bot_type'               => sanitize_text_field( $data['bot_type'] ),
			'bot_name'               => sanitize_text_field( $data['bot_name'] ?? '' ),
			'user_agent'             => sanitize_text_field( $data['user_agent'] ?? '' ),
			'url'                    => esc_url_raw( $data['url'] ),
			'post_id'                => isset( $data['post_id'] ) ? absint( $data['post_id'] ) : null,
			'post_type'              => isset( $data['post_type'] ) ? sanitize_text_field( $data['post_type'] ) : null,
			'post_title'             => isset( $data['post_title'] ) ? sanitize_text_field( $data['post_title'] ) : null,
			'request_method'         => sanitize_text_field( $data['request_method'] ?? 'md_url' ),
			'cache_status'           => sanitize_text_field( $data['cache_status'] ?? 'MISS' ),
			'response_time'          => isset( $data['response_time'] ) ? absint( $data['response_time'] ) : null,
			'response_size'          => isset( $data['response_size'] ) ? absint( $data['response_size'] ) : null,
			'ip_address'             => $ip_address,
			'referer'                => isset( $data['referer'] ) ? esc_url_raw( $data['referer'] ) : null,
			'country_code'           => $country_code,
			'traffic_type'           => sanitize_text_field( $data['traffic_type'] ?? 'bot_crawl' ),
			'content_type'           => sanitize_text_field( $data['content_type'] ?? 'html' ), // v3.2.0: HTML vs markdown.
			'ai_platform'            => isset( $data['ai_platform'] ) ? sanitize_text_field( $data['ai_platform'] ) : null,
			'search_query'           => isset( $data['search_query'] ) ? sanitize_text_field( $data['search_query'] ) : null,
			'referer_source'         => isset( $data['referer_source'] ) ? sanitize_text_field( $data['referer_source'] ) : null,
			'referer_medium'         => isset( $data['referer_medium'] ) ? sanitize_text_field( $data['referer_medium'] ) : null,
			'detection_method'       => isset( $data['detection_method'] ) ? sanitize_text_field( $data['detection_method'] ) : 'legacy',
			'confidence_score'       => isset( $data['confidence_score'] ) ? floatval( $data['confidence_score'] ) : null,
			'ip_verified'            => $ip_verification['verified'],
			'ip_verification_method' => $ip_verification['method'],
			'visit_timestamp'        => current_time( 'mysql' ),
		);

		$format = array(
			'%s', // bot_type
			'%s', // bot_name
			'%s', // user_agent
			'%s', // url
			'%d', // post_id
			'%s', // post_type
			'%s', // post_title
			'%s', // request_method
			'%s', // cache_status
			'%d', // response_time
			'%d', // response_size
			'%s', // ip_address
			'%s', // referer
			'%s', // country_code
			'%s', // traffic_type
			'%s', // content_type (v3.2.0)
			'%s', // ai_platform
			'%s', // search_query
			'%s', // referer_source
			'%s', // referer_medium
			'%s', // detection_method
			'%f', // confidence_score
			'%d', // ip_verified
			'%s', // ip_verification_method
			'%s', // visit_timestamp
		);

		// Analyze content metrics if post_id is available.
		$post_id = isset( $data['post_id'] ) ? absint( $data['post_id'] ) : null;
		if ( $post_id && class_exists( 'TA_Content_Analyzer' ) ) {
			$content_analyzer = TA_Content_Analyzer::get_instance();
			$metrics          = $content_analyzer->analyze_post( $post_id );

			if ( $metrics ) {
				$insert_data['content_word_count']     = $metrics['word_count'];
				$insert_data['content_heading_count']  = $metrics['heading_count'];
				$insert_data['content_image_count']    = $metrics['image_count'];
				$insert_data['content_has_schema']     = $metrics['has_schema'];
				$insert_data['content_freshness_days'] = $metrics['freshness_days'];

				// Add format specs for new columns.
				$format[] = '%d'; // content_word_count
				$format[] = '%d'; // content_heading_count
				$format[] = '%d'; // content_image_count
				$format[] = '%d'; // content_has_schema
				$format[] = '%d'; // content_freshness_days
			}
		}

	$result = $wpdb->insert( $table_name, $insert_data, $format );

		if ( false === $result ) {
			$this->logger->error( 'Failed to track bot visit.', array(
				'error' => $wpdb->last_error,
				'data'  => $insert_data,
			) );
			return false;
		}

		$this->logger->debug( 'Bot visit tracked.', array(
			'id'       => $wpdb->insert_id,
			'bot_type' => $data['bot_type'],
			'url'      => $data['url'],
		) );

		// Check if this is a new bot by counting previous visits for this bot type.
		$visit_count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table_name} WHERE bot_type = %s AND id != %d",
			$data['bot_type'],
			$wpdb->insert_id
		) );

		// If this is the first visit from this bot type, fire bot.detected webhook.
		if ( 0 === absint( $visit_count ) ) {
			$bot_info = $this->detect_bot( $data['user_agent'] ?? '' );
			if ( false !== $bot_info ) {
				$this->webhooks->fire_bot_detected( $bot_info );
			}
		}

		// Update session tracking after recording visit.
		$this->update_session_tracking( $insert_data );

		// Check for citation alerts (first citation, new platform).
		if ( 'citation_click' === $insert_data['traffic_type'] && class_exists( 'TA_Citation_Alerts' ) ) {
			$citation_alerts = TA_Citation_Alerts::get_instance();

			// Check for first citation from this platform.
			if ( ! empty( $insert_data['ai_platform'] ) ) {
				$citation_alerts->check_first_citation( $insert_data['ai_platform'] );
			}

			// Check for new platform.
			if ( ! empty( $insert_data['ai_platform'] ) ) {
				$citation_alerts->check_new_platform( $insert_data['ai_platform'] );
			}
		}

		// Send event to GA4 (async, non-blocking).
		if ( class_exists( 'TA_GA4_Integration' ) ) {
			$ga4 = TA_GA4_Integration::get_instance();
			if ( $ga4->is_enabled() ) {
				// Send appropriate event based on traffic type.
				if ( 'citation_click' === $insert_data['traffic_type'] ) {
					$result = $ga4->send_citation_click_event( $insert_data );
				} else {
					$result = $ga4->send_bot_crawl_event( $insert_data );
				}

				// Update sync stats.
				if ( ! is_wp_error( $result ) ) {
					$ga4->update_sync_stats( true );
				} else {
					$ga4->update_sync_stats( false );
					$this->logger->debug( 'GA4 event send failed.', array(
						'error' => $result->get_error_message(),
					) );
				}
			}
		}

		return $wpdb->insert_id;
	}

	/**
	 * Update session tracking for bot fingerprinting.
	 *
	 * Groups visits into sessions and calculates session metrics:
	 * - Pages per session
	 * - Session duration
	 * - Request intervals
	 * - Crawl budget utilization
	 *
	 * Session Definition: Visits from same bot+IP within 30-minute window.
	 *
	 * @since 2.6.0
	 * @param array $visit_data Current visit data.
	 * @return void
	 */
	private function update_session_tracking( $visit_data ) {
		global $wpdb;

		// Skip if no IP or user agent.
		if ( empty( $visit_data['ip_address'] ) || empty( $visit_data['user_agent'] ) ) {
			return;
		}

		// Generate fingerprint hash (bot identifier).
		$fingerprint = md5( $visit_data['user_agent'] . '|' . $visit_data['ip_address'] );

		$fingerprints_table = $wpdb->prefix . 'ta_bot_fingerprints';
		$analytics_table    = $wpdb->prefix . self::TABLE_NAME;

		// Get or create fingerprint record.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$fingerprint_record = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$fingerprints_table} WHERE fingerprint_hash = %s",
				$fingerprint
			)
		);

		$now = current_time( 'mysql' );

		if ( ! $fingerprint_record ) {
			// First visit from this bot+IP - create fingerprint record.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->insert(
				$fingerprints_table,
				array(
					'fingerprint_hash' => $fingerprint,
					'user_agent'       => $visit_data['user_agent'],
					'ip_address'       => $visit_data['ip_address'],
					'first_seen'       => $now,
					'last_seen'        => $now,
					'visit_count'      => 1,
					'classification'   => $visit_data['bot_type'],
				),
				array( '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
			);

			return; // First visit, no session metrics to calculate yet.
		}

		// Calculate time since last visit (in seconds).
		$last_seen_timestamp = strtotime( $fingerprint_record->last_seen );
		$current_timestamp   = strtotime( $now );
		$time_since_last     = $current_timestamp - $last_seen_timestamp;

		// Session window: 30 minutes (1800 seconds).
		$session_window = 1800;

		// Determine if this is same session or new session.
		$is_same_session = $time_since_last <= $session_window;

		// Get recent visits from this bot+IP for session calculation.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$recent_visits = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT visit_timestamp FROM {$analytics_table}
				WHERE user_agent = %s AND ip_address = %s
				ORDER BY visit_timestamp DESC LIMIT 100",
				$visit_data['user_agent'],
				$visit_data['ip_address']
			)
		);

		// Calculate session metrics.
		$total_visits = count( $recent_visits );
		$visit_count  = absint( $fingerprint_record->visit_count ) + 1;

		// Calculate request intervals (time between consecutive requests).
		$intervals = array();
		for ( $i = 0; $i < count( $recent_visits ) - 1; $i++ ) {
			$time1       = strtotime( $recent_visits[ $i ]->visit_timestamp );
			$time2       = strtotime( $recent_visits[ $i + 1 ]->visit_timestamp );
			$intervals[] = abs( $time1 - $time2 );
		}

		$request_interval_avg    = ! empty( $intervals ) ? array_sum( $intervals ) / count( $intervals ) : null;
		$request_interval_stddev = ! empty( $intervals ) ? $this->calculate_stddev( $intervals ) : null;

		// Calculate session metrics by grouping visits into sessions.
		$sessions           = $this->group_visits_into_sessions( $recent_visits, $session_window );
		$pages_per_session  = ! empty( $sessions ) ? array_sum( array_column( $sessions, 'page_count' ) ) / count( $sessions ) : null;
		$session_durations  = array_column( $sessions, 'duration' );
		$session_duration   = ! empty( $session_durations ) ? array_sum( $session_durations ) / count( $session_durations ) : null;

		// Calculate unique paths ratio.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$unique_urls = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT url) FROM {$analytics_table}
				WHERE user_agent = %s AND ip_address = %s",
				$visit_data['user_agent'],
				$visit_data['ip_address']
			)
		);
		$unique_paths_ratio = $visit_count > 0 ? round( absint( $unique_urls ) / $visit_count, 2 ) : null;

		// Update fingerprint record with session metrics.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->update(
			$fingerprints_table,
			array(
				'last_seen'              => $now,
				'visit_count'            => $visit_count,
				'request_interval_avg'   => $request_interval_avg,
				'request_interval_stddev' => $request_interval_stddev,
				'pages_per_session_avg'  => $pages_per_session,
				'session_duration_avg'   => $session_duration,
				'unique_paths_ratio'     => $unique_paths_ratio,
				'classification'         => $visit_data['bot_type'],
			),
			array( 'fingerprint_hash' => $fingerprint ),
			array( '%s', '%d', '%d', '%d', '%f', '%d', '%f', '%s' ),
			array( '%s' )
		);

		$this->logger->debug( 'Session tracking updated.', array(
			'fingerprint'       => $fingerprint,
			'visit_count'       => $visit_count,
			'is_same_session'   => $is_same_session,
			'pages_per_session' => $pages_per_session,
		) );
	}

	/**
	 * Group visits into sessions based on time windows.
	 *
	 * @since 2.6.0
	 * @param array $visits Array of visit records with timestamps.
	 * @param int   $session_window Session timeout in seconds (default: 1800 = 30 min).
	 * @return array Array of sessions with page_count and duration.
	 */
	private function group_visits_into_sessions( $visits, $session_window = 1800 ) {
		if ( empty( $visits ) ) {
			return array();
		}

		$sessions        = array();
		$current_session = array(
			'start'      => null,
			'end'        => null,
			'page_count' => 0,
			'duration'   => 0,
		);

		foreach ( $visits as $index => $visit ) {
			$timestamp = strtotime( $visit->visit_timestamp );

			if ( null === $current_session['start'] ) {
				// First visit in session.
				$current_session['start']      = $timestamp;
				$current_session['end']        = $timestamp;
				$current_session['page_count'] = 1;
			} else {
				$time_since_last = abs( $current_session['end'] - $timestamp );

				if ( $time_since_last <= $session_window ) {
					// Same session - update end time and increment page count.
					$current_session['end']        = $timestamp;
					$current_session['page_count']++;
				} else {
					// New session - save current and start new.
					$current_session['duration'] = abs( $current_session['end'] - $current_session['start'] );
					$sessions[]                  = $current_session;

					// Start new session.
					$current_session = array(
						'start'      => $timestamp,
						'end'        => $timestamp,
						'page_count' => 1,
						'duration'   => 0,
					);
				}
			}
		}

		// Add final session.
		if ( null !== $current_session['start'] ) {
			$current_session['duration'] = abs( $current_session['end'] - $current_session['start'] );
			$sessions[]                  = $current_session;
		}

		return $sessions;
	}

	/**
	 * Calculate standard deviation of an array of numbers.
	 *
	 * @since 2.6.0
	 * @param array $values Array of numeric values.
	 * @return float Standard deviation.
	 */
	private function calculate_stddev( $values ) {
		if ( empty( $values ) ) {
			return 0.0;
		}

		$mean     = array_sum( $values ) / count( $values );
		$variance = array_sum( array_map( function( $x ) use ( $mean ) {
			return pow( $x - $mean, 2 );
		}, $values ) ) / count( $values );

		return sqrt( $variance );
	}

	/**
	 * Track AI citation click from referrer.
	 *
	 * Detects when a user clicks a citation from an AI platform (ChatGPT,
	 * Perplexity, Claude, etc.) and logs it as citation traffic.
	 *
	 * @since 2.2.0
	 * @return int|false Insert ID or false if not citation traffic.
	 */
	public function track_citation_click() {
		// Detect AI citation traffic from referrer.
		$citation_data = TA_AI_Citation_Tracker::detect_citation_traffic();

		if ( false === $citation_data ) {
			return false; // Not citation traffic.
		}

		// Get current page info.
		$post_id    = get_queried_object_id();
		$post       = $post_id ? get_post( $post_id ) : null;
		$post_type  = $post ? $post->post_type : null;
		$post_title = $post ? $post->post_title : null;

		// Prepare tracking data.
		$tracking_data = array(
			'bot_type'         => 'AI_Citation', // Special bot type for citations.
			'bot_name'         => $citation_data['platform'],
			'user_agent'       => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
			'url'              => esc_url_raw( $_SERVER['REQUEST_URI'] ?? '/' ),
			'post_id'          => $post_id,
			'post_type'        => $post_type,
			'post_title'       => $post_title,
			'request_method'   => 'citation_click',
			'cache_status'     => 'N/A',
			'referer'          => $citation_data['referer'],
			'traffic_type'     => 'citation_click',
			'ai_platform'      => $citation_data['platform'],
			'search_query'     => $citation_data['search_query'],
			'referer_source'   => $citation_data['source'],
			'referer_medium'   => $citation_data['medium'],
			'detection_method' => $citation_data['detection_method'],
			'confidence_score' => $citation_data['confidence_score'],
		);

		// Track the visit.
		$result = $this->track_visit( $tracking_data );

		if ( false !== $result ) {
			$this->logger->info( 'AI citation click tracked.', array(
				'platform'     => $citation_data['platform'],
				'search_query' => $citation_data['search_query'],
				'url'          => $tracking_data['url'],
			) );
		}

		return $result;
	}

	/**
	 * Get geolocation data from IP address.
	 *
	 * Uses ip-api.com free service (no API key required).
	 * Rate limit: 45 requests/minute.
	 *
	 * @since 1.5.0
	 * @param string $ip The IP address to lookup.
	 * @return string|null Country code (ISO 3166-1 alpha-2) or null on failure.
	 */
	private function get_geolocation( $ip ) {
		if ( empty( $ip ) ) {
			return null;
		}

		// Validate IP address format.
		if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 ) ) {
			$this->logger->debug( 'Invalid IP address format for geolocation.', array( 'ip' => $ip ) );
			return null;
		}

		// Don't lookup local/private IPs.
		if ( $this->is_private_ip( $ip ) ) {
			return null;
		}

		// Check cache first (cache for 24 hours).
		$cache_key = 'ta_geo_' . md5( $ip );
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		// Lookup via ip-api.com (free, no API key required).
		$url = 'http://ip-api.com/json/' . $ip . '?fields=status,countryCode';

		$response = wp_remote_get( $url, array(
			'timeout' => 3,
		) );

		if ( is_wp_error( $response ) ) {
			$this->logger->debug( 'Geolocation lookup failed.', array(
				'ip'    => $ip,
				'error' => $response->get_error_message(),
			) );
			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! empty( $data['status'] ) && 'success' === $data['status'] && ! empty( $data['countryCode'] ) ) {
			$country_code = sanitize_text_field( $data['countryCode'] );

			// Cache for 24 hours.
			set_transient( $cache_key, $country_code, DAY_IN_SECONDS );

			$this->logger->debug( 'Geolocation lookup successful.', array(
				'ip'      => $ip,
				'country' => $country_code,
			) );

			return $country_code;
		}

		$this->logger->debug( 'Geolocation lookup returned no data.', array(
			'ip'       => $ip,
			'response' => $data,
		) );

		return null;
	}

	/**
	 * Check if an IP is private/local.
	 *
	 * @since 1.5.0
	 * @param string $ip The IP address to check.
	 * @return bool True if private, false otherwise.
	 */
	private function is_private_ip( $ip ) {
		// IPv4 private ranges.
		$private_ranges = array(
			'10.0.0.0|10.255.255.255',
			'172.16.0.0|172.31.255.255',
			'192.168.0.0|192.168.255.255',
			'127.0.0.0|127.255.255.255',
		);

		$ip_long = ip2long( $ip );
		if ( false === $ip_long ) {
			return false; // Invalid or IPv6.
		}

		foreach ( $private_ranges as $range ) {
			list( $start, $end ) = explode( '|', $range );
			if ( $ip_long >= ip2long( $start ) && $ip_long <= ip2long( $end ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get client IP address.
	 *
	 * @since 1.4.0
	 * @return string|null Client IP or null.
	 */
	private function get_client_ip() {
		$ip = null;

		if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			// Cloudflare.
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
			$ip = explode( ',', $ip )[0];
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return $ip;
	}

	/**
	 * Get analytics summary.
	 *
	 * @since 1.4.0
	 * @param array $filters Optional filters.
	 * @return array Summary statistics.
	 */
	public function get_summary( $filters = array() ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$where = $this->build_where_clause( $filters );

		$summary = array(
			'total_visits'           => 0,
			'unique_pages'           => 0,
			'unique_bots'            => 0,
			'cache_hit_rate'         => 0,
			'avg_response_time'      => 0,
			'total_bandwidth'        => 0,
			'visits_today'           => 0,
			'visits_yesterday'       => 0,
			'visits_this_week'       => 0,
			'visits_this_month'      => 0,
			'trend_percentage'       => 0,
			'ip_verified_percentage' => 0,
			'ip_verified_count'      => 0,
			'ip_failed_count'        => 0,
		);

		// Total visits.
		$summary['total_visits'] = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table_name} {$where}"
		);

		// IP Verification stats (v2.7.0).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$ip_verified_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table_name} {$where} AND ip_verified = 1"
		);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$ip_failed_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table_name} {$where} AND ip_verified = 0"
		);

		$summary['ip_verified_count'] = $ip_verified_count;
		$summary['ip_failed_count']   = $ip_failed_count;

		if ( $summary['total_visits'] > 0 ) {
			$summary['ip_verified_percentage'] = round( ( $ip_verified_count / $summary['total_visits'] ) * 100, 1 );
		}

		// Unique pages.
		$summary['unique_pages'] = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT url) FROM {$table_name} {$where}"
		);

		// Unique bots.
		$summary['unique_bots'] = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT bot_type) FROM {$table_name} {$where}"
		);

		// Cache hit rate.
		$cache_hits = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table_name} {$where} AND cache_status IN ('HIT', 'PRE_GENERATED')"
		);
		if ( $summary['total_visits'] > 0 ) {
			$summary['cache_hit_rate'] = round( ( $cache_hits / $summary['total_visits'] ) * 100, 1 );
		}

		// Average response time.
		$summary['avg_response_time'] = (int) $wpdb->get_var(
			"SELECT AVG(response_time) FROM {$table_name} {$where} AND response_time IS NOT NULL"
		);

		// Total bandwidth.
		$summary['total_bandwidth'] = (int) $wpdb->get_var(
			"SELECT SUM(response_size) FROM {$table_name} {$where} AND response_size IS NOT NULL"
		);

		// Time-based stats.
		$today           = gmdate( 'Y-m-d' );
		$yesterday       = gmdate( 'Y-m-d', strtotime( '-1 day' ) );
		$week_start      = gmdate( 'Y-m-d', strtotime( '-7 days' ) );
		$month_start     = gmdate( 'Y-m-d', strtotime( '-30 days' ) );

		$summary['visits_today'] = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE DATE(visit_timestamp) = %s",
				$today
			)
		);

		$summary['visits_yesterday'] = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE DATE(visit_timestamp) = %s",
				$yesterday
			)
		);

		$summary['visits_this_week'] = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE DATE(visit_timestamp) >= %s",
				$week_start
			)
		);

		$summary['visits_this_month'] = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE DATE(visit_timestamp) >= %s",
				$month_start
			)
		);

		// Calculate trend (today vs yesterday).
		if ( $summary['visits_yesterday'] > 0 ) {
			$summary['trend_percentage'] = round(
				( ( $summary['visits_today'] - $summary['visits_yesterday'] ) / $summary['visits_yesterday'] ) * 100,
				1
			);
		} elseif ( $summary['visits_today'] > 0 ) {
			$summary['trend_percentage'] = 100;
		}

		return $summary;
	}

	/**
	 * Get visits by bot type.
	 *
	 * @since 1.4.0
	 * @param array $filters Optional filters.
	 * @return array Bot type breakdown.
	 */
	public function get_visits_by_bot( $filters = array() ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$where = $this->build_where_clause( $filters );

		$results = $wpdb->get_results(
			"SELECT bot_type, bot_name, COUNT(*) as count
			FROM {$table_name}
			{$where}
			GROUP BY bot_type, bot_name
			ORDER BY count DESC",
			ARRAY_A
		);

		// Add color information.
		foreach ( $results as &$result ) {
			$result['color'] = self::$known_bots[ $result['bot_type'] ]['color'] ?? '#999999';
		}

		return $results;
	}

	/**
	 * Get top visited pages.
	 *
	 * @since 1.4.0
	 * @param array $filters Optional filters.
	 * @param int   $limit   Number of results.
	 * @return array Top pages.
	 */
	public function get_top_pages( $filters = array(), $limit = 10 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$where = $this->build_where_clause( $filters );

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT url, post_title, post_type, COUNT(*) as visits,
				COUNT(DISTINCT bot_type) as unique_bots,
				AVG(response_time) as avg_response_time
				FROM {$table_name}
				{$where}
				GROUP BY url, post_title, post_type
				ORDER BY visits DESC
				LIMIT %d",
				$limit
			),
			ARRAY_A
		);
	}

	/**
	 * Get citation-to-crawl ratio for pages.
	 *
	 * Shows which content is being crawled by bots but NOT cited by AI platforms.
	 * Higher ratio = better citation performance.
	 *
	 * @since 2.7.0
	 * @param array $filters Optional filters.
	 * @param int   $limit   Number of results.
	 * @return array Citation rate data by URL.
	 */
	public function get_citation_to_crawl_ratio( $filters = array(), $limit = 20 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		// Build WHERE clause (but we need to exclude traffic_type from base filters).
		$where_conditions = array( '1=1' );

		if ( ! empty( $filters['date_from'] ) ) {
			$where_conditions[] = $wpdb->prepare( 'DATE(visit_timestamp) >= %s', $filters['date_from'] );
		}

		if ( ! empty( $filters['date_to'] ) ) {
			$where_conditions[] = $wpdb->prepare( 'DATE(visit_timestamp) <= %s', $filters['date_to'] );
		}

		if ( ! empty( $filters['search'] ) ) {
			$search             = '%' . $wpdb->esc_like( $filters['search'] ) . '%';
			$where_conditions[] = $wpdb->prepare(
				'(url LIKE %s OR post_title LIKE %s)',
				$search,
				$search
			);
		}

		$where_sql = 'WHERE ' . implode( ' AND ', $where_conditions );

		// Query: Count crawls vs citations per URL.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					url,
					post_title,
					COUNT(CASE WHEN traffic_type = 'bot_crawl' THEN 1 END) as crawls,
					COUNT(CASE WHEN traffic_type = 'citation_click' THEN 1 END) as citations,
					(COUNT(CASE WHEN traffic_type = 'citation_click' THEN 1 END) * 1.0 /
					 NULLIF(COUNT(CASE WHEN traffic_type = 'bot_crawl' THEN 1 END), 0)) as citation_rate
				FROM {$table_name}
				{$where_sql}
				GROUP BY url, post_title
				HAVING crawls > 0
				ORDER BY crawls DESC
				LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		// Format results.
		foreach ( $results as &$result ) {
			$result['crawls']        = (int) $result['crawls'];
			$result['citations']     = (int) $result['citations'];
			$result['citation_rate'] = $result['citation_rate'] ? round( (float) $result['citation_rate'], 3 ) : 0;
		}

		return $results;
	}

	/**
	 * Get visits over time (for charts).
	 *
	 * @since 1.4.0
	 * @param array  $filters Optional filters.
	 * @param string $period  Period: 'hour', 'day', 'week', 'month'.
	 * @param int    $limit   Number of periods.
	 * @return array Time series data.
	 */
	public function get_visits_over_time( $filters = array(), $period = 'day', $limit = 30 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$where = $this->build_where_clause( $filters );

		switch ( $period ) {
			case 'hour':
				$date_format = '%Y-%m-%d %H:00:00';
				$group_by    = 'DATE_FORMAT(visit_timestamp, "%Y-%m-%d %H:00:00")';
				break;
			case 'week':
				$date_format = '%Y-%U';
				$group_by    = 'YEARWEEK(visit_timestamp)';
				break;
			case 'month':
				$date_format = '%Y-%m';
				$group_by    = 'DATE_FORMAT(visit_timestamp, "%Y-%m")';
				break;
			default: // day.
				$date_format = '%Y-%m-%d';
				$group_by    = 'DATE(visit_timestamp)';
				break;
		}

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE_FORMAT(visit_timestamp, %s) as period,
				COUNT(*) as visits,
				COUNT(DISTINCT bot_type) as unique_bots
				FROM {$table_name}
				{$where}
				GROUP BY {$group_by}
				ORDER BY period DESC
				LIMIT %d",
				$date_format,
				$limit
			),
			ARRAY_A
		);
	}

	/**
	 * Get recent visits.
	 *
	 * @since 1.4.0
	 * @param array $filters Optional filters.
	 * @param int   $limit   Number of results.
	 * @param int   $offset  Offset for pagination.
	 * @return array Recent visits.
	 */
	public function get_recent_visits( $filters = array(), $limit = 50, $offset = 0 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$where = $this->build_where_clause( $filters );

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table_name}
				{$where}
				ORDER BY visit_timestamp DESC
				LIMIT %d OFFSET %d",
				$limit,
				$offset
			),
			ARRAY_A
		);
	}

	/**
	 * Get session analytics summary.
	 *
	 * Returns aggregated session metrics from bot fingerprints table.
	 *
	 * @since 2.6.0
	 * @return array Session analytics summary.
	 */
	public function get_session_analytics() {
		global $wpdb;
		$fingerprints_table = $wpdb->prefix . 'ta_bot_fingerprints';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$stats = $wpdb->get_row(
			"SELECT
				COUNT(*) as total_bots,
				AVG(visit_count) as avg_visits_per_bot,
				AVG(pages_per_session_avg) as avg_pages_per_session,
				AVG(session_duration_avg) as avg_session_duration,
				AVG(request_interval_avg) as avg_request_interval,
				AVG(unique_paths_ratio) as avg_unique_paths_ratio
			FROM {$fingerprints_table}",
			ARRAY_A
		);

		return array(
			'total_bot_fingerprints'  => absint( $stats['total_bots'] ?? 0 ),
			'avg_visits_per_bot'      => round( floatval( $stats['avg_visits_per_bot'] ?? 0 ), 1 ),
			'avg_pages_per_session'   => round( floatval( $stats['avg_pages_per_session'] ?? 0 ), 1 ),
			'avg_session_duration'    => absint( $stats['avg_session_duration'] ?? 0 ), // seconds
			'avg_request_interval'    => absint( $stats['avg_request_interval'] ?? 0 ), // seconds
			'avg_unique_paths_ratio'  => round( floatval( $stats['avg_unique_paths_ratio'] ?? 0 ), 2 ),
		);
	}

	/**
	 * Get detailed bot fingerprints list for drill-down.
	 *
	 * Returns all bot fingerprints with full details for session analytics modal.
	 *
	 * @since 3.2.2
	 * @param string $sort_by Column to sort by.
	 * @param string $order   Sort order (ASC/DESC).
	 * @param int    $limit   Number of results.
	 * @return array List of bot fingerprints with all metrics.
	 */
	public function get_bot_fingerprints_list( $sort_by = 'last_seen', $order = 'DESC', $limit = 50 ) {
		global $wpdb;
		$fingerprints_table = $wpdb->prefix . 'ta_bot_fingerprints';

		$allowed_columns = array(
			'last_seen'            => 'last_seen',
			'first_seen'           => 'first_seen',
			'visit_count'          => 'visit_count',
			'pages_per_session'    => 'pages_per_session_avg',
			'session_duration'     => 'session_duration_avg',
			'request_interval'     => 'request_interval_avg',
			'classification'       => 'classification',
		);

		$order_column = $allowed_columns[ $sort_by ] ?? 'last_seen';
		$order_dir    = 'ASC' === strtoupper( $order ) ? 'ASC' : 'DESC';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					id,
					fingerprint_hash,
					classification as bot_type,
					user_agent,
					ip_address,
					visit_count,
					pages_per_session_avg,
					session_duration_avg,
					request_interval_avg,
					unique_paths_ratio,
					first_seen,
					last_seen,
					bot_score
				FROM {$fingerprints_table}
				ORDER BY {$order_column} {$order_dir}
				LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		// Format the data for display.
		foreach ( $results as &$row ) {
			$row['pages_per_session_avg']  = round( floatval( $row['pages_per_session_avg'] ?? 0 ), 1 );
			$row['session_duration_avg']   = absint( $row['session_duration_avg'] ?? 0 );
			$row['session_duration_human'] = $this->format_duration( $row['session_duration_avg'] );
			$row['request_interval_avg']   = absint( $row['request_interval_avg'] ?? 0 );
			$row['request_interval_human'] = $this->format_duration( $row['request_interval_avg'] );
			$row['unique_paths_ratio']     = round( floatval( $row['unique_paths_ratio'] ?? 0 ) * 100, 1 );
			$row['first_seen_human']       = human_time_diff( strtotime( $row['first_seen'] ), current_time( 'timestamp' ) ) . ' ago';
			$row['last_seen_human']        = human_time_diff( strtotime( $row['last_seen'] ), current_time( 'timestamp' ) ) . ' ago';
			$row['user_agent_short']       = wp_trim_words( $row['user_agent'], 10 );
		}

		return $results;
	}

	/**
	 * Format seconds into human-readable duration.
	 *
	 * @since 3.2.2
	 * @param int $seconds Duration in seconds.
	 * @return string Human-readable duration.
	 */
	private function format_duration( $seconds ) {
		if ( $seconds < 60 ) {
			return $seconds . 's';
		} elseif ( $seconds < 3600 ) {
			return round( $seconds / 60, 1 ) . ' min';
		} else {
			return round( $seconds / 3600, 1 ) . ' hr';
		}
	}

	/**
	 * Get top bots by session metrics.
	 *
	 * Returns bots sorted by pages per session, session duration, etc.
	 *
	 * @since 2.6.0
	 * @param string $metric Metric to sort by: 'pages_per_session', 'session_duration', 'visit_count'.
	 * @param int    $limit  Number of results to return.
	 * @return array Top bots with session metrics.
	 */
	public function get_top_bots_by_metric( $metric = 'pages_per_session', $limit = 10 ) {
		global $wpdb;
		$fingerprints_table = $wpdb->prefix . 'ta_bot_fingerprints';

		$allowed_metrics = array(
			'pages_per_session' => 'pages_per_session_avg',
			'session_duration'  => 'session_duration_avg',
			'visit_count'       => 'visit_count',
			'request_interval'  => 'request_interval_avg',
		);

		$order_by = $allowed_metrics[ $metric ] ?? 'pages_per_session_avg';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					classification as bot_type,
					user_agent,
					visit_count,
					pages_per_session_avg,
					session_duration_avg,
					request_interval_avg,
					unique_paths_ratio,
					first_seen,
					last_seen
				FROM {$fingerprints_table}
				WHERE {$order_by} IS NOT NULL
				ORDER BY {$order_by} DESC
				LIMIT %d",
				$limit
			),
			ARRAY_A
		);
	}

	/**
	 * Get crawl budget metrics.
	 *
	 * Analyzes requests per hour/day and bandwidth consumption.
	 *
	 * @since 2.6.0
	 * @param string $bot_type Optional bot type filter.
	 * @param string $period   Time period: 'hour', 'day', 'week'.
	 * @return array Crawl budget metrics.
	 */
	public function get_crawl_budget_metrics( $bot_type = null, $period = 'day' ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		// Determine time window.
		$time_windows = array(
			'hour' => '1 HOUR',
			'day'  => '1 DAY',
			'week' => '7 DAY',
		);
		$time_window = $time_windows[ $period ] ?? '1 DAY';

		$where = 'WHERE visit_timestamp >= DATE_SUB(NOW(), INTERVAL ' . $time_window . ')';
		if ( $bot_type ) {
			$where .= $wpdb->prepare( ' AND bot_type = %s', $bot_type );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$stats = $wpdb->get_row(
			"SELECT
				COUNT(*) as total_requests,
				COUNT(DISTINCT post_id) as unique_pages,
				SUM(response_size) as total_bandwidth,
				AVG(response_time) as avg_response_time,
				COUNT(CASE WHEN cache_status = 'HIT' THEN 1 END) as cache_hits,
				COUNT(CASE WHEN cache_status = 'MISS' THEN 1 END) as cache_misses
			FROM {$table_name}
			{$where}",
			ARRAY_A
		);

		$total_requests = absint( $stats['total_requests'] ?? 0 );
		$cache_hits     = absint( $stats['cache_hits'] ?? 0 );

		return array(
			'period'              => $period,
			'total_requests'      => $total_requests,
			'unique_pages'        => absint( $stats['unique_pages'] ?? 0 ),
			'total_bandwidth_mb'  => round( absint( $stats['total_bandwidth'] ?? 0 ) / 1024 / 1024, 2 ),
			'avg_response_time'   => absint( $stats['avg_response_time'] ?? 0 ),
			'cache_hit_rate'      => $total_requests > 0 ? round( ( $cache_hits / $total_requests ) * 100, 1 ) : 0,
			'requests_per_hour'   => $period === 'hour' ? $total_requests : round( $total_requests / ( $period === 'day' ? 24 : 168 ), 1 ),
		);
	}

	/**
	 * Build WHERE clause from filters.
	 *
	 * @since 1.4.0
	 * @param array $filters Filters array.
	 * @return string WHERE clause.
	 */
	private function build_where_clause( $filters ) {
		global $wpdb;
		$where_conditions = array( '1=1' );

		if ( ! empty( $filters['bot_type'] ) ) {
			$where_conditions[] = $wpdb->prepare( 'bot_type = %s', $filters['bot_type'] );
		}

		if ( ! empty( $filters['post_type'] ) ) {
			$where_conditions[] = $wpdb->prepare( 'post_type = %s', $filters['post_type'] );
		}

		if ( ! empty( $filters['cache_status'] ) ) {
			$where_conditions[] = $wpdb->prepare( 'cache_status = %s', $filters['cache_status'] );
		}

		if ( ! empty( $filters['content_type'] ) ) {
			$where_conditions[] = $wpdb->prepare( 'content_type = %s', $filters['content_type'] );
		}

		if ( ! empty( $filters['date_from'] ) ) {
			$where_conditions[] = $wpdb->prepare( 'DATE(visit_timestamp) >= %s', $filters['date_from'] );
		}

		if ( ! empty( $filters['date_to'] ) ) {
			$where_conditions[] = $wpdb->prepare( 'DATE(visit_timestamp) <= %s', $filters['date_to'] );
		}

		if ( ! empty( $filters['search'] ) ) {
			$search             = '%' . $wpdb->esc_like( $filters['search'] ) . '%';
			$where_conditions[] = $wpdb->prepare(
				'(url LIKE %s OR post_title LIKE %s OR user_agent LIKE %s)',
				$search,
				$search,
				$search
			);
		}

		return 'WHERE ' . implode( ' AND ', $where_conditions );
	}

	/**
	 * Export data to CSV.
	 *
	 * @since 1.4.0
	 * @param array  $filters Optional filters.
	 * @param string $format  Export format: 'detailed' or 'summary'.
	 * @return void
	 */
	public function export_to_csv( $filters = array(), $format = 'detailed' ) {
		$visits = $this->get_recent_visits( $filters, 10000 ); // Max 10k rows.

		$filename = 'bot-analytics-' . gmdate( 'Y-m-d-H-i-s' ) . '.csv';

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );

		// Export metadata header.
		fputcsv( $output, array( 'Third Audience Bot Analytics Export' ) );
		fputcsv( $output, array( 'Generated', gmdate( 'Y-m-d H:i:s' ) . ' UTC' ) );
		fputcsv( $output, array( 'Total Records', count( $visits ) ) );
		fputcsv( $output, array( 'Format', ucfirst( $format ) ) );
		fputcsv( $output, array() ); // Empty row.

		if ( 'summary' === $format ) {
			// Summary format: aggregated stats by bot type.
			$summary_data = $this->get_summary_export_data( $visits );

			// CSV headers for summary.
			fputcsv( $output, array(
				'Bot Type',
				'Bot Name',
				'Total Visits',
				'Unique Pages',
				'Cache Hit Rate (%)',
				'Avg Response Time (ms)',
				'Total Bandwidth',
			) );

			// CSV rows for summary.
			foreach ( $summary_data as $row ) {
				fputcsv( $output, $row );
			}
		} else {
			// Detailed format: all columns.
			// CSV headers.
			fputcsv( $output, array(
				'ID',
				'Bot Type',
				'Bot Name',
				'User Agent',
				'URL',
				'Post Title',
				'Post Type',
				'Request Method',
				'Cache Status',
				'Cache Explanation',
				'Response Time (ms)',
				'Response Size',
				'IP Address',
				'Referer',
				'Country Code',
				'Visit Time (UTC)',
			) );

			// Helper function for cache explanation.
			$get_cache_explanation = function( $status ) {
				$explanations = array(
					'HIT'            => 'Served from transient cache (fast)',
					'MISS'           => 'Generated fresh (first visit or cache expired)',
					'PRE_GENERATED'  => 'Served from pre-generated cache (fastest)',
					'FAILED'         => 'Content conversion failed',
					'BLOCKED'        => 'Bot was blocked from accessing content',
					'ERROR'          => 'An error occurred during processing',
				);
				return $explanations[ $status ] ?? 'Unknown status';
			};

			// CSV rows.
			foreach ( $visits as $visit ) {
				fputcsv( $output, array(
					$visit['id'],
					$visit['bot_type'],
					$visit['bot_name'],
					$visit['user_agent'] ?? '',
					$visit['url'],
					$visit['post_title'] ?? 'N/A',
					$visit['post_type'] ?? 'N/A',
					$visit['request_method'],
					$visit['cache_status'],
					$get_cache_explanation( $visit['cache_status'] ),
					$visit['response_time'],
					$visit['response_size'] ? size_format( $visit['response_size'], 2 ) : 'N/A',
					$visit['ip_address'] ?? 'N/A',
					$visit['referer'] ?? 'Direct',
					$visit['country_code'] ?? 'N/A',
					$visit['visit_timestamp'],
				) );
			}
		}

		fclose( $output );
		exit;
	}

	/**
	 * Export data to JSON.
	 *
	 * @since 2.0.6
	 * @param array  $filters Optional filters.
	 * @param string $format  Export format: 'detailed' or 'summary'.
	 * @return void
	 */
	public function export_to_json( $filters = array(), $format = 'detailed' ) {
		$visits = $this->get_recent_visits( $filters, 10000 ); // Max 10k rows.

		$filename = 'bot-analytics-' . gmdate( 'Y-m-d-H-i-s' ) . '.json';

		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$export_data = array(
			'metadata' => array(
				'plugin'        => 'Third Audience',
				'export_type'   => 'Bot Analytics',
				'generated_at'  => gmdate( 'Y-m-d H:i:s' ) . ' UTC',
				'total_records' => count( $visits ),
				'format'        => $format,
				'filters'       => $filters,
			),
		);

		if ( 'summary' === $format ) {
			// Summary format: aggregated stats by bot type.
			$export_data['data'] = $this->get_summary_export_data_json( $visits );
		} else {
			// Detailed format: all visit records.
			$export_data['data'] = array_map( function( $visit ) {
				return array(
					'id'              => (int) $visit['id'],
					'bot_type'        => $visit['bot_type'],
					'bot_name'        => $visit['bot_name'],
					'user_agent'      => $visit['user_agent'] ?? null,
					'url'             => $visit['url'],
					'post_id'         => isset( $visit['post_id'] ) ? (int) $visit['post_id'] : null,
					'post_title'      => $visit['post_title'] ?? null,
					'post_type'       => $visit['post_type'] ?? null,
					'request_method'  => $visit['request_method'],
					'cache_status'    => $visit['cache_status'],
					'response_time'   => isset( $visit['response_time'] ) ? (int) $visit['response_time'] : null,
					'response_size'   => isset( $visit['response_size'] ) ? (int) $visit['response_size'] : null,
					'ip_address'      => $visit['ip_address'] ?? null,
					'referer'         => $visit['referer'] ?? null,
					'country_code'    => $visit['country_code'] ?? null,
					'visit_timestamp' => $visit['visit_timestamp'],
				);
			}, $visits );
		}

		echo wp_json_encode( $export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		exit;
	}

	/**
	 * Get summary export data for CSV.
	 *
	 * @since 2.0.6
	 * @param array $visits Visit records.
	 * @return array Summary data grouped by bot type.
	 */
	private function get_summary_export_data( $visits ) {
		$summary = array();

		// Group by bot type.
		foreach ( $visits as $visit ) {
			$bot_type = $visit['bot_type'];

			if ( ! isset( $summary[ $bot_type ] ) ) {
				$summary[ $bot_type ] = array(
					'bot_type'       => $bot_type,
					'bot_name'       => $visit['bot_name'],
					'visits'         => 0,
					'unique_pages'   => array(),
					'cache_hits'     => 0,
					'total_response' => 0,
					'response_count' => 0,
					'total_size'     => 0,
				);
			}

			$summary[ $bot_type ]['visits']++;
			$summary[ $bot_type ]['unique_pages'][ $visit['url'] ] = true;

			if ( in_array( $visit['cache_status'], array( 'HIT', 'PRE_GENERATED' ), true ) ) {
				$summary[ $bot_type ]['cache_hits']++;
			}

			if ( isset( $visit['response_time'] ) && $visit['response_time'] > 0 ) {
				$summary[ $bot_type ]['total_response'] += (int) $visit['response_time'];
				$summary[ $bot_type ]['response_count']++;
			}

			if ( isset( $visit['response_size'] ) && $visit['response_size'] > 0 ) {
				$summary[ $bot_type ]['total_size'] += (int) $visit['response_size'];
			}
		}

		// Calculate aggregates.
		$result = array();
		foreach ( $summary as $bot_data ) {
			$cache_hit_rate = $bot_data['visits'] > 0
				? round( ( $bot_data['cache_hits'] / $bot_data['visits'] ) * 100, 1 )
				: 0;

			$avg_response = $bot_data['response_count'] > 0
				? round( $bot_data['total_response'] / $bot_data['response_count'] )
				: 0;

			$result[] = array(
				$bot_data['bot_type'],
				$bot_data['bot_name'],
				$bot_data['visits'],
				count( $bot_data['unique_pages'] ),
				$cache_hit_rate,
				$avg_response,
				size_format( $bot_data['total_size'], 2 ),
			);
		}

		return $result;
	}

	/**
	 * Get summary export data for JSON.
	 *
	 * @since 2.0.6
	 * @param array $visits Visit records.
	 * @return array Summary data grouped by bot type.
	 */
	private function get_summary_export_data_json( $visits ) {
		$summary = array();

		// Group by bot type.
		foreach ( $visits as $visit ) {
			$bot_type = $visit['bot_type'];

			if ( ! isset( $summary[ $bot_type ] ) ) {
				$summary[ $bot_type ] = array(
					'bot_type'       => $bot_type,
					'bot_name'       => $visit['bot_name'],
					'visits'         => 0,
					'unique_pages'   => array(),
					'cache_hits'     => 0,
					'total_response' => 0,
					'response_count' => 0,
					'total_size'     => 0,
				);
			}

			$summary[ $bot_type ]['visits']++;
			$summary[ $bot_type ]['unique_pages'][ $visit['url'] ] = true;

			if ( in_array( $visit['cache_status'], array( 'HIT', 'PRE_GENERATED' ), true ) ) {
				$summary[ $bot_type ]['cache_hits']++;
			}

			if ( isset( $visit['response_time'] ) && $visit['response_time'] > 0 ) {
				$summary[ $bot_type ]['total_response'] += (int) $visit['response_time'];
				$summary[ $bot_type ]['response_count']++;
			}

			if ( isset( $visit['response_size'] ) && $visit['response_size'] > 0 ) {
				$summary[ $bot_type ]['total_size'] += (int) $visit['response_size'];
			}
		}

		// Calculate aggregates.
		$result = array();
		foreach ( $summary as $bot_data ) {
			$cache_hit_rate = $bot_data['visits'] > 0
				? round( ( $bot_data['cache_hits'] / $bot_data['visits'] ) * 100, 1 )
				: 0;

			$avg_response = $bot_data['response_count'] > 0
				? round( $bot_data['total_response'] / $bot_data['response_count'] )
				: 0;

			$result[] = array(
				'bot_type'         => $bot_data['bot_type'],
				'bot_name'         => $bot_data['bot_name'],
				'total_visits'     => $bot_data['visits'],
				'unique_pages'     => count( $bot_data['unique_pages'] ),
				'cache_hit_rate'   => $cache_hit_rate,
				'avg_response_ms'  => $avg_response,
				'total_bandwidth'  => $bot_data['total_size'],
			);
		}

		return $result;
	}

	/**
	 * Delete old records.
	 *
	 * @since 1.4.0
	 * @param int $days Number of days to keep.
	 * @return int Number of deleted rows.
	 */
	public function cleanup_old_records( $days = 90 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$cutoff = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table_name} WHERE visit_timestamp < %s",
				$cutoff
			)
		);

		$this->logger->info( 'Old bot analytics records deleted.', array(
			'days'    => $days,
			'deleted' => $deleted,
		) );

		return (int) $deleted;
	}

	/**
	 * Clear all bot visit records.
	 *
	 * @since 2.0.5
	 * @return int Number of deleted rows.
	 */
	public function clear_all_visits() {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );

		$deleted = $wpdb->query( "TRUNCATE TABLE {$table_name}" );

		$this->logger->info( 'All bot analytics records cleared.', array(
			'deleted' => $count,
		) );

		return $count;
	}

	/**
	 * Get all known bot types.
	 *
	 * @since 1.4.0
	 * @return array Bot types.
	 */
	public static function get_known_bots() {
		return self::$known_bots;
	}

	/**
	 * Get content performance analysis.
	 *
	 * Correlates content metrics with citation rates to identify
	 * what content characteristics perform best.
	 *
	 * @since 2.7.0
	 * @param array $filters Optional filters.
	 * @return array Content performance analysis.
	 */
	public function get_content_performance_analysis( $filters = array() ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$where = $this->build_where_clause( $filters );

		// Get avg metrics for cited posts (AI citation traffic).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$cited_stats = $wpdb->get_row(
			"SELECT
				AVG(content_word_count) as avg_word_count,
				AVG(content_heading_count) as avg_heading_count,
				AVG(content_image_count) as avg_image_count,
				AVG(content_freshness_days) as avg_freshness_days,
				SUM(CASE WHEN content_has_schema = 1 THEN 1 ELSE 0 END) as schema_count,
				COUNT(*) as total_count
			FROM {$table_name}
			{$where} AND traffic_type = 'citation_click' AND content_word_count IS NOT NULL",
			ARRAY_A
		);

		// Get avg metrics for crawled posts (bot_crawl traffic).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$crawled_stats = $wpdb->get_row(
			"SELECT
				AVG(content_word_count) as avg_word_count,
				AVG(content_heading_count) as avg_heading_count,
				AVG(content_image_count) as avg_image_count,
				AVG(content_freshness_days) as avg_freshness_days,
				SUM(CASE WHEN content_has_schema = 1 THEN 1 ELSE 0 END) as schema_count,
				COUNT(*) as total_count
			FROM {$table_name}
			{$where} AND traffic_type = 'bot_crawl' AND content_word_count IS NOT NULL",
			ARRAY_A
		);

		// Calculate citation multiplier.
		$cited_count    = absint( $cited_stats['total_count'] ?? 0 );
		$crawled_count  = absint( $crawled_stats['total_count'] ?? 0 );
		$citation_rate  = $crawled_count > 0 ? round( ( $cited_count / $crawled_count ) * 100, 2 ) : 0;

		$cited_schema_rate = $cited_count > 0
			? round( ( absint( $cited_stats['schema_count'] ?? 0 ) / $cited_count ) * 100, 1 )
			: 0;

		$crawled_schema_rate = $crawled_count > 0
			? round( ( absint( $crawled_stats['schema_count'] ?? 0 ) / $crawled_count ) * 100, 1 )
			: 0;

		$schema_multiplier = $crawled_schema_rate > 0
			? round( $cited_schema_rate / $crawled_schema_rate, 1 )
			: 0;

		return array(
			'cited_posts'    => array(
				'avg_word_count'     => round( floatval( $cited_stats['avg_word_count'] ?? 0 ) ),
				'avg_heading_count'  => round( floatval( $cited_stats['avg_heading_count'] ?? 0 ), 1 ),
				'avg_image_count'    => round( floatval( $cited_stats['avg_image_count'] ?? 0 ), 1 ),
				'avg_freshness_days' => round( floatval( $cited_stats['avg_freshness_days'] ?? 0 ) ),
				'schema_percentage'  => $cited_schema_rate,
				'total_count'        => $cited_count,
			),
			'crawled_posts'  => array(
				'avg_word_count'     => round( floatval( $crawled_stats['avg_word_count'] ?? 0 ) ),
				'avg_heading_count'  => round( floatval( $crawled_stats['avg_heading_count'] ?? 0 ), 1 ),
				'avg_image_count'    => round( floatval( $crawled_stats['avg_image_count'] ?? 0 ), 1 ),
				'avg_freshness_days' => round( floatval( $crawled_stats['avg_freshness_days'] ?? 0 ) ),
				'schema_percentage'  => $crawled_schema_rate,
				'total_count'        => $crawled_count,
			),
			'citation_rate'  => $citation_rate,
			'schema_multiplier' => $schema_multiplier,
		);
	}

	/**
	 * Get optimal content length based on citation data.
	 *
	 * Analyzes word count ranges to find the ideal length for citations.
	 *
	 * @since 2.7.0
	 * @return array Optimal content length range.
	 */
	public function get_optimal_content_length() {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		// Group citations by word count ranges.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$results = $wpdb->get_results(
			"SELECT
				CASE
					WHEN content_word_count < 300 THEN '0-299'
					WHEN content_word_count < 600 THEN '300-599'
					WHEN content_word_count < 900 THEN '600-899'
					WHEN content_word_count < 1200 THEN '900-1199'
					WHEN content_word_count < 1500 THEN '1200-1499'
					WHEN content_word_count < 2000 THEN '1500-1999'
					ELSE '2000+'
				END as word_range,
				COUNT(*) as citation_count
			FROM {$table_name}
			WHERE traffic_type = 'citation_click' AND content_word_count IS NOT NULL
			GROUP BY word_range
			ORDER BY citation_count DESC",
			ARRAY_A
		);

		// Find range with most citations.
		$optimal_range = ! empty( $results ) ? $results[0] : array(
			'word_range'     => 'N/A',
			'citation_count' => 0,
		);

		return array(
			'optimal_range' => $optimal_range['word_range'],
			'citation_count' => absint( $optimal_range['citation_count'] ),
			'all_ranges' => $results,
		);
	}

	/**
	 * Uninstall - Drop table and delete options.
	 *
	 * @since 1.4.0
	 * @return void
	 */
	public static function uninstall() {
		global $wpdb;

		// Drop main analytics table.
		$table_name = $wpdb->prefix . self::TABLE_NAME;
		$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

		// Drop bot detection tables (added in v1.2.0).
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ta_bot_patterns" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ta_unknown_bots" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ta_bot_db_sync" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ta_bot_fingerprints" );

		delete_option( self::DB_VERSION_OPTION );
	}
}
