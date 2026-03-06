<?php
/**
 * Citation Alerts - Detection and notification system for significant citation events.
 *
 * Monitors AI citation traffic and generates alerts for:
 * - First citations from platforms
 * - New AI platforms detected
 * - Citation spikes and drops
 * - High-performing pages
 * - Verification failures
 *
 * @package ThirdAudience
 * @since   2.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Citation_Alerts
 *
 * Alert detection engine for AI citation traffic.
 *
 * @since 2.8.0
 */
class TA_Citation_Alerts {

	/**
	 * Database table name for alert history.
	 *
	 * @var string
	 */
	const TABLE_NAME = 'ta_citation_alerts';

	/**
	 * Database version for migrations.
	 *
	 * @var string
	 */
	const DB_VERSION = '1.0.0';

	/**
	 * Option name for database version.
	 *
	 * @var string
	 */
	const DB_VERSION_OPTION = 'ta_citation_alerts_db_version';

	/**
	 * Cache key for alert checks.
	 *
	 * @var string
	 */
	const CACHE_KEY = 'ta_citation_alerts_last_check';

	/**
	 * Alert check interval (1 hour).
	 *
	 * @var int
	 */
	const CHECK_INTERVAL = 3600;

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Analytics instance.
	 *
	 * @var TA_Bot_Analytics
	 */
	private $analytics;

	/**
	 * Singleton instance.
	 *
	 * @var TA_Citation_Alerts|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 2.8.0
	 * @return TA_Citation_Alerts
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
	 * @since 2.8.0
	 */
	private function __construct() {
		$this->logger    = TA_Logger::get_instance();
		$this->analytics = TA_Bot_Analytics::get_instance();

		$this->maybe_create_table();
	}

	/**
	 * Create alerts history table if it doesn't exist.
	 *
	 * @since 2.8.0
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
			alert_type varchar(50) NOT NULL,
			severity varchar(20) NOT NULL DEFAULT 'info',
			title varchar(255) NOT NULL,
			message text NOT NULL,
			metadata longtext DEFAULT NULL,
			dismissed tinyint(1) NOT NULL DEFAULT 0,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY alert_type (alert_type),
			KEY severity (severity),
			KEY dismissed (dismissed),
			KEY created_at (created_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( self::DB_VERSION_OPTION, self::DB_VERSION );

		$this->logger->info( 'Citation alerts table created.', array(
			'version' => self::DB_VERSION,
		) );
	}

	/**
	 * Check for alert conditions and return active alerts.
	 *
	 * @since 2.8.0
	 * @return array Array of alert objects.
	 */
	public function check_alerts() {
		// Check if we've run recently (transient cache).
		$last_check = get_transient( self::CACHE_KEY );
		if ( false !== $last_check ) {
			return $this->get_undismissed_alerts();
		}

		$alerts = array();

		// Get analytics for today and yesterday.
		$today_start    = gmdate( 'Y-m-d 00:00:00' );
		$yesterday_start = gmdate( 'Y-m-d 00:00:00', strtotime( '-1 day' ) );
		$yesterday_end   = gmdate( 'Y-m-d 23:59:59', strtotime( '-1 day' ) );

		$today_summary = $this->analytics->get_summary( array(
			'date_from' => $today_start,
			'traffic_type' => 'citation_click',
		) );

		$yesterday_summary = $this->analytics->get_summary( array(
			'date_from' => $yesterday_start,
			'date_to'   => $yesterday_end,
			'traffic_type' => 'citation_click',
		) );

		$today_citations     = isset( $today_summary['total_visits'] ) ? intval( $today_summary['total_visits'] ) : 0;
		$yesterday_citations = isset( $yesterday_summary['total_visits'] ) ? intval( $yesterday_summary['total_visits'] ) : 0;

		// Check for citation spike (2x increase).
		if ( $yesterday_citations > 0 && $today_citations >= $yesterday_citations * 2 ) {
			$increase_pct = round( ( $today_citations / $yesterday_citations - 1 ) * 100 );
			$alerts[] = array(
				'type'     => 'citation_spike',
				'severity' => 'info',
				'title'    => __( 'Citation Spike Detected', 'third-audience' ),
				'message'  => sprintf(
					/* translators: %d: percentage increase */
					__( 'Citations increased %d%% in the last 24 hours.', 'third-audience' ),
					$increase_pct
				),
				'metadata' => wp_json_encode( array(
					'today'     => $today_citations,
					'yesterday' => $yesterday_citations,
					'increase'  => $increase_pct,
				) ),
			);
		}

		// Check for citation drop (50% decrease).
		if ( $yesterday_citations >= 5 && $today_citations < $yesterday_citations * 0.5 ) {
			$decrease_pct = round( ( 1 - $today_citations / $yesterday_citations ) * 100 );
			$alerts[] = array(
				'type'     => 'citation_drop',
				'severity' => 'warning',
				'title'    => __( 'Citation Drop Detected', 'third-audience' ),
				'message'  => sprintf(
					/* translators: %d: percentage decrease */
					__( 'Citations dropped %d%% in the last 24 hours. Check content freshness or Google Search Console.', 'third-audience' ),
					$decrease_pct
				),
				'metadata' => wp_json_encode( array(
					'today'     => $today_citations,
					'yesterday' => $yesterday_citations,
					'decrease'  => $decrease_pct,
				) ),
			);
		}

		// Check for high-performance pages (10+ citations in 24h).
		$high_performers = $this->get_high_performance_pages( 10 );
		if ( ! empty( $high_performers ) ) {
			foreach ( $high_performers as $page ) {
				$alerts[] = array(
					'type'     => 'high_performance',
					'severity' => 'success',
					'title'    => __( 'High-Performance Content', 'third-audience' ),
					'message'  => sprintf(
						/* translators: 1: page title, 2: citation count */
						__( '"%1$s" received %2$d citations in the last 24 hours.', 'third-audience' ),
						$page['post_title'],
						$page['citation_count']
					),
					'metadata' => wp_json_encode( array(
						'post_id'        => $page['post_id'],
						'post_title'     => $page['post_title'],
						'citation_count' => $page['citation_count'],
					) ),
				);
			}
		}

		// Check for verification failures.
		$verification_failures = $this->check_verification_failures();
		if ( $verification_failures > 0 ) {
			$alerts[] = array(
				'type'     => 'verification_failure',
				'severity' => 'warning',
				'title'    => __( 'Bot IP Verification Failures', 'third-audience' ),
				'message'  => sprintf(
					/* translators: %d: number of failures */
					__( '%d bot IP verification failures detected in the last 24 hours. This may indicate spoofed bot traffic.', 'third-audience' ),
					$verification_failures
				),
				'metadata' => wp_json_encode( array(
					'failure_count' => $verification_failures,
				) ),
			);
		}

		// Save alerts to database.
		foreach ( $alerts as $alert ) {
			$this->save_alert( $alert );
		}

		// Set cache for 1 hour.
		set_transient( self::CACHE_KEY, time(), self::CHECK_INTERVAL );

		return $alerts;
	}

	/**
	 * Check if this is a first citation from a platform.
	 *
	 * @since 2.8.0
	 * @param string $ai_platform AI platform name.
	 * @return array|null Alert array or null if not first citation.
	 */
	public function check_first_citation( $ai_platform ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ta_bot_analytics';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$citation_count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE ai_platform = %s AND traffic_type = 'citation_click'",
			$ai_platform
		) );

		if ( 1 === intval( $citation_count ) ) {
			$alert = array(
				'type'     => 'first_citation',
				'severity' => 'success',
				'title'    => __( 'First Citation!', 'third-audience' ),
				'message'  => sprintf(
					/* translators: %s: AI platform name */
					__( 'Congratulations! You got your first citation from %s.', 'third-audience' ),
					$ai_platform
				),
				'metadata' => wp_json_encode( array(
					'platform' => $ai_platform,
				) ),
			);

			$this->save_alert( $alert );
			return $alert;
		}

		return null;
	}

	/**
	 * Check if this is a new platform (first visit).
	 *
	 * @since 2.8.0
	 * @param string $ai_platform AI platform name.
	 * @return array|null Alert array or null if not new platform.
	 */
	public function check_new_platform( $ai_platform ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ta_bot_analytics';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$visit_count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE ai_platform = %s",
			$ai_platform
		) );

		if ( 1 === intval( $visit_count ) ) {
			$alert = array(
				'type'     => 'new_platform',
				'severity' => 'info',
				'title'    => __( 'New AI Platform Detected', 'third-audience' ),
				'message'  => sprintf(
					/* translators: %s: AI platform name */
					__( 'First visit detected from %s. Your content is now discoverable on this platform.', 'third-audience' ),
					$ai_platform
				),
				'metadata' => wp_json_encode( array(
					'platform' => $ai_platform,
				) ),
			);

			$this->save_alert( $alert );
			return $alert;
		}

		return null;
	}

	/**
	 * Get high-performance pages from last 24 hours.
	 *
	 * @since 2.8.0
	 * @param int $min_citations Minimum citation count threshold.
	 * @return array Array of page data.
	 */
	private function get_high_performance_pages( $min_citations = 10 ) {
		global $wpdb;
		$table      = $wpdb->prefix . 'ta_bot_analytics';
		$yesterday  = gmdate( 'Y-m-d H:i:s', strtotime( '-24 hours' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results( $wpdb->prepare(
			"SELECT
				post_id,
				post_title,
				COUNT(*) as citation_count
			FROM {$table}
			WHERE traffic_type = 'citation_click'
				AND visit_timestamp >= %s
				AND post_id IS NOT NULL
			GROUP BY post_id, post_title
			HAVING citation_count >= %d
			ORDER BY citation_count DESC
			LIMIT 5",
			$yesterday,
			$min_citations
		), ARRAY_A );

		return $results;
	}

	/**
	 * Check for IP verification failures in last 24 hours.
	 *
	 * @since 2.8.0
	 * @return int Number of verification failures.
	 */
	private function check_verification_failures() {
		global $wpdb;
		$table     = $wpdb->prefix . 'ta_bot_analytics';
		$yesterday = gmdate( 'Y-m-d H:i:s', strtotime( '-24 hours' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$failure_count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table}
			WHERE ip_verified = 0
				AND visit_timestamp >= %s",
			$yesterday
		) );

		return intval( $failure_count );
	}

	/**
	 * Save alert to database.
	 *
	 * @since 2.8.0
	 * @param array $alert Alert data.
	 * @return int|false Alert ID or false on failure.
	 */
	private function save_alert( $alert ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_NAME;

		// Check if similar alert already exists (within last hour).
		$one_hour_ago = gmdate( 'Y-m-d H:i:s', strtotime( '-1 hour' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$existing = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM {$table}
			WHERE alert_type = %s
				AND created_at >= %s
			LIMIT 1",
			$alert['type'],
			$one_hour_ago
		) );

		if ( $existing ) {
			return false; // Don't create duplicate alerts.
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->insert(
			$table,
			array(
				'alert_type' => $alert['type'],
				'severity'   => $alert['severity'],
				'title'      => $alert['title'],
				'message'    => $alert['message'],
				'metadata'   => $alert['metadata'] ?? null,
				'dismissed'  => 0,
				'created_at' => gmdate( 'Y-m-d H:i:s' ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
		);

		if ( $result ) {
			$this->logger->info( 'Citation alert created.', array(
				'type'  => $alert['type'],
				'title' => $alert['title'],
			) );
			return $wpdb->insert_id;
		}

		return false;
	}

	/**
	 * Get undismissed alerts.
	 *
	 * @since 2.8.0
	 * @param int $limit Maximum number of alerts to return.
	 * @return array Array of alert objects.
	 */
	public function get_undismissed_alerts( $limit = 5 ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_NAME;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$alerts = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$table}
			WHERE dismissed = 0
			ORDER BY created_at DESC
			LIMIT %d",
			$limit
		), ARRAY_A );

		return $alerts;
	}

	/**
	 * Get alert history with pagination.
	 *
	 * @since 2.8.0
	 * @param array $args Query arguments.
	 * @return array Alert results.
	 */
	public function get_alert_history( $args = array() ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_NAME;

		$defaults = array(
			'limit'      => 50,
			'offset'     => 0,
			'alert_type' => '',
			'severity'   => '',
			'dismissed'  => null,
		);

		$args = wp_parse_args( $args, $defaults );

		$where = array( '1=1' );
		$params = array();

		if ( ! empty( $args['alert_type'] ) ) {
			$where[]  = 'alert_type = %s';
			$params[] = $args['alert_type'];
		}

		if ( ! empty( $args['severity'] ) ) {
			$where[]  = 'severity = %s';
			$params[] = $args['severity'];
		}

		if ( null !== $args['dismissed'] ) {
			$where[]  = 'dismissed = %d';
			$params[] = (int) $args['dismissed'];
		}

		$where_sql = implode( ' AND ', $where );

		$params[] = absint( $args['limit'] );
		$params[] = absint( $args['offset'] );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$alerts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE {$where_sql}
				ORDER BY created_at DESC
				LIMIT %d OFFSET %d",
				...$params
			),
			ARRAY_A
		);

		return $alerts;
	}

	/**
	 * Dismiss an alert.
	 *
	 * @since 2.8.0
	 * @param int $alert_id Alert ID.
	 * @return bool True on success, false on failure.
	 */
	public function dismiss_alert( $alert_id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_NAME;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->update(
			$table,
			array( 'dismissed' => 1 ),
			array( 'id' => absint( $alert_id ) ),
			array( '%d' ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Get alert statistics.
	 *
	 * @since 2.8.0
	 * @return array Statistics data.
	 */
	public function get_statistics() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_NAME;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$stats = $wpdb->get_row(
			"SELECT
				COUNT(*) as total_alerts,
				SUM(CASE WHEN dismissed = 0 THEN 1 ELSE 0 END) as active_alerts,
				SUM(CASE WHEN dismissed = 1 THEN 1 ELSE 0 END) as dismissed_alerts,
				SUM(CASE WHEN severity = 'warning' THEN 1 ELSE 0 END) as warning_alerts,
				SUM(CASE WHEN severity = 'success' THEN 1 ELSE 0 END) as success_alerts
			FROM {$table}
			WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
			ARRAY_A
		);

		return $stats ?: array();
	}

	/**
	 * Clear old alerts (older than 90 days).
	 *
	 * @since 2.8.0
	 * @return int Number of alerts deleted.
	 */
	public function cleanup_old_alerts() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_NAME;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$deleted = $wpdb->query(
			"DELETE FROM {$table}
			WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)"
		);

		return $deleted ?: 0;
	}
}
