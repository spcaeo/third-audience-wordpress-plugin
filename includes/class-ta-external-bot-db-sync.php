<?php
/**
 * External Bot Database Sync
 *
 * Syncs bot patterns from external sources (Crawler-Detect, Device-Detector, etc).
 *
 * @package ThirdAudience
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_External_Bot_DB_Sync
 *
 * Syncs bot patterns from external sources to local database.
 *
 * @since 2.3.0
 */
class TA_External_Bot_DB_Sync {

	/**
	 * Bot patterns table name.
	 *
	 * @var string
	 */
	const PATTERNS_TABLE = 'ta_bot_patterns';

	/**
	 * Bot DB sync log table name.
	 *
	 * @var string
	 */
	const SYNC_LOG_TABLE = 'ta_bot_db_sync';

	/**
	 * Cron hook name for weekly sync.
	 *
	 * @var string
	 */
	const CRON_HOOK = 'ta_external_bot_db_sync';

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Singleton instance.
	 *
	 * @var TA_External_Bot_DB_Sync|null
	 */
	private static $instance = null;

	/**
	 * External bot pattern sources.
	 *
	 * @var array
	 */
	private $sources = array(
		'crawler-detect' => array(
			'name'   => 'Crawler-Detect',
			'url'    => 'https://raw.githubusercontent.com/JayBizzle/Crawler-Detect/master/src/Fixtures/Crawlers.php',
			'format' => 'php_array',
		),
	);

	/**
	 * Get singleton instance.
	 *
	 * @since 2.3.0
	 * @return TA_External_Bot_DB_Sync
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
	 * @since 2.3.0
	 */
	private function __construct() {
		$this->logger = TA_Logger::get_instance();
		$this->create_tables();
	}

	/**
	 * Create database tables for bot patterns and sync log.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function create_tables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		// Bot patterns table.
		$patterns_table = $wpdb->prefix . self::PATTERNS_TABLE;
		$sql_patterns = "CREATE TABLE IF NOT EXISTS {$patterns_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			bot_name varchar(100) NOT NULL,
			pattern varchar(500) NOT NULL,
			source varchar(50) NOT NULL,
			source_version varchar(20) DEFAULT NULL,
			confidence_score decimal(3,2) DEFAULT 0.90,
			is_active tinyint(1) DEFAULT 1,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY pattern (pattern(255)),
			KEY source (source),
			KEY is_active (is_active)
		) {$charset_collate};";

		// Sync log table.
		$sync_log_table = $wpdb->prefix . self::SYNC_LOG_TABLE;
		$sql_sync_log = "CREATE TABLE IF NOT EXISTS {$sync_log_table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			source varchar(50) NOT NULL,
			source_version varchar(20) DEFAULT NULL,
			patterns_added int(11) DEFAULT 0,
			patterns_updated int(11) DEFAULT 0,
			patterns_total int(11) DEFAULT 0,
			status varchar(20) NOT NULL,
			error_message text DEFAULT NULL,
			sync_timestamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY source (source),
			KEY sync_timestamp (sync_timestamp)
		) {$charset_collate};";

		$wpdb->query( $sql_patterns );
		$wpdb->query( $sql_sync_log );
	}

	/**
	 * Fetch bot patterns from external URL.
	 *
	 * @since 2.3.0
	 * @param string $url The URL to fetch patterns from.
	 * @return string|WP_Error The fetched content or WP_Error on failure.
	 */
	public function fetch_patterns_from_url( $url ) {
		$response = wp_remote_get( $url, array(
			'timeout' => 30,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			return new WP_Error( 'http_error', sprintf( 'HTTP %d error', $response_code ) );
		}

		$body = wp_remote_retrieve_body( $response );
		return $body;
	}

	/**
	 * Parse PHP array format from content.
	 *
	 * @since 2.3.0
	 * @param string $content The PHP content containing a return statement with an array.
	 * @return array|WP_Error The parsed patterns array or WP_Error on failure.
	 */
	public function parse_php_array( $content ) {
		// Remove PHP tags and extract array.
		$content = str_replace( array( '<?php', '<?', '?>' ), '', $content );
		
		// Try to evaluate the content safely.
		$patterns = null;
		try {
			// Extract the return statement.
			if ( preg_match( '/return\s+array\s*\((.*)\);/s', $content, $matches ) ) {
				$array_content = 'array(' . $matches[1] . ')';
				$patterns = eval( 'return ' . $array_content . ';' );
			}
		} catch ( Exception $e ) {
			return new WP_Error( 'parse_failed', 'Failed to parse PHP array: ' . $e->getMessage() );
		}

		if ( ! is_array( $patterns ) ) {
			return new WP_Error( 'parse_failed', 'Content is not a valid PHP array' );
		}

		return $patterns;
	}

	/**
	 * Insert or update a bot pattern.
	 *
	 * @since 2.3.0
	 * @param string $bot_name       The bot name.
	 * @param string $pattern        The regex pattern.
	 * @param string $source         The source identifier.
	 * @param string $source_version The source version.
	 * @return bool True on success, false on failure.
	 */
	public function insert_or_update_pattern( $bot_name, $pattern, $source, $source_version ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::PATTERNS_TABLE;

		// Check if pattern already exists.
		$existing = $wpdb->get_row( $wpdb->prepare(
			"SELECT id FROM {$table_name} WHERE pattern = %s LIMIT 1",
			$pattern
		) );

		if ( $existing ) {
			// Update existing pattern.
			$result = $wpdb->update(
				$table_name,
				array(
					'bot_name'       => $bot_name,
					'source_version' => $source_version,
					'updated_at'     => current_time( 'mysql' ),
				),
				array( 'id' => $existing->id ),
				array( '%s', '%s', '%s' ),
				array( '%d' )
			);
			return false !== $result;
		} else {
			// Insert new pattern.
			$result = $wpdb->insert(
				$table_name,
				array(
					'bot_name'       => $bot_name,
					'pattern'        => $pattern,
					'source'         => 'external_db',
					'source_version' => $source_version,
					'created_at'     => current_time( 'mysql' ),
				),
				array( '%s', '%s', '%s', '%s', '%s' )
			);
			return false !== $result;
		}
	}

	/**
	 * Sync bot patterns from a source.
	 *
	 * @since 2.3.0
	 * @param string $url            The URL to fetch patterns from.
	 * @param string $source         The source identifier.
	 * @param string $source_version The source version.
	 * @return array Sync results with counts.
	 */
	public function sync_from_source( $url, $source, $source_version ) {
		$content = $this->fetch_patterns_from_url( $url );
		
		if ( is_wp_error( $content ) ) {
			return array(
				'error'           => $content->get_error_message(),
				'patterns_added'  => 0,
				'patterns_updated' => 0,
				'patterns_total'   => 0,
			);
		}

		$patterns = $this->parse_php_array( $content );
		
		if ( is_wp_error( $patterns ) ) {
			return array(
				'error'           => $patterns->get_error_message(),
				'patterns_added'  => 0,
				'patterns_updated' => 0,
				'patterns_total'   => 0,
			);
		}

		$added = 0;
		$updated = 0;

		foreach ( $patterns as $bot_name => $pattern ) {
			global $wpdb;
			$table_name = $wpdb->prefix . self::PATTERNS_TABLE;
			
			// Check if exists.
			$existing = $wpdb->get_row( $wpdb->prepare(
				"SELECT id FROM {$table_name} WHERE pattern = %s LIMIT 1",
				$pattern
			) );

			if ( $existing ) {
				$updated++;
			} else {
				$added++;
			}

			$this->insert_or_update_pattern( $bot_name, $pattern, $source, $source_version );
		}

		return array(
			'patterns_added'  => $added,
			'patterns_updated' => $updated,
			'patterns_total'   => count( $patterns ),
		);
	}

	/**
	 * Log sync status to database.
	 *
	 * @since 2.3.0
	 * @param string $source          The source identifier.
	 * @param string $source_version  The source version.
	 * @param int    $patterns_added  Number of patterns added.
	 * @param int    $patterns_updated Number of patterns updated.
	 * @param int    $patterns_total   Total patterns processed.
	 * @param string $status          Sync status (success/failed).
	 * @param string $error_message   Error message if failed.
	 * @return bool True on success, false on failure.
	 */
	public function log_sync_status( $source, $source_version, $patterns_added, $patterns_updated, $patterns_total, $status, $error_message = null ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::SYNC_LOG_TABLE;

		$result = $wpdb->insert(
			$table_name,
			array(
				'source'           => $source,
				'source_version'   => $source_version,
				'patterns_added'   => $patterns_added,
				'patterns_updated' => $patterns_updated,
				'patterns_total'   => $patterns_total,
				'status'           => $status,
				'error_message'    => $error_message,
				'sync_timestamp'   => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s' )
		);

		return false !== $result;
	}

	/**
	 * Schedule weekly sync via WP-Cron.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function schedule_weekly_sync() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time(), 'weekly', self::CRON_HOOK );
		}
	}

	/**
	 * Unschedule sync.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function unschedule_sync() {
		wp_clear_scheduled_hook( self::CRON_HOOK );
	}

	/**
	 * Run sync for all configured sources.
	 *
	 * @since 2.3.0
	 * @return array Results for each source.
	 */
	public function run_sync() {
		$results = array();

		foreach ( $this->sources as $source_id => $source_config ) {
			$result = $this->sync_from_source(
				$source_config['url'],
				$source_id,
				'latest'
			);

			$status = isset( $result['error'] ) ? 'failed' : 'success';
			$error_message = isset( $result['error'] ) ? $result['error'] : null;

			$this->log_sync_status(
				$source_id,
				'latest',
				$result['patterns_added'] ?? 0,
				$result['patterns_updated'] ?? 0,
				$result['patterns_total'] ?? 0,
				$status,
				$error_message
			);

			$results[ $source_id ] = $result;
		}

		return $results;
	}
}
