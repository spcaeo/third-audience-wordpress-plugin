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
	const DB_VERSION = '1.0.0';

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
			'pattern' => '/ClaudeBot/i',
			'name'    => 'Claude (Anthropic)',
			'color'   => '#D97757',
		),
		'GPTBot'            => array(
			'pattern' => '/GPTBot/i',
			'name'    => 'GPT (OpenAI)',
			'color'   => '#10A37F',
		),
		'ChatGPT-User'      => array(
			'pattern' => '/ChatGPT-User/i',
			'name'    => 'ChatGPT User',
			'color'   => '#10A37F',
		),
		'PerplexityBot'     => array(
			'pattern' => '/PerplexityBot/i',
			'name'    => 'Perplexity',
			'color'   => '#1FB6D0',
		),
		'Bytespider'        => array(
			'pattern' => '/Bytespider/i',
			'name'    => 'ByteDance AI',
			'color'   => '#FF4458',
		),
		'anthropic-ai'      => array(
			'pattern' => '/anthropic-ai/i',
			'name'    => 'Anthropic AI',
			'color'   => '#D97757',
		),
		'cohere-ai'         => array(
			'pattern' => '/cohere-ai/i',
			'name'    => 'Cohere',
			'color'   => '#39594D',
		),
		'Google-Extended'   => array(
			'pattern' => '/Google-Extended/i',
			'name'    => 'Google Gemini',
			'color'   => '#4285F4',
		),
		'FacebookBot'       => array(
			'pattern' => '/FacebookBot/i',
			'name'    => 'Meta AI',
			'color'   => '#1877F2',
		),
		'Applebot-Extended' => array(
			'pattern' => '/Applebot-Extended/i',
			'name'    => 'Apple Intelligence',
			'color'   => '#000000',
		),
	);

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

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
		$this->logger = TA_Logger::get_instance();
		$this->maybe_create_table();
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
			visit_timestamp datetime NOT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY bot_type (bot_type),
			KEY post_id (post_id),
			KEY visit_timestamp (visit_timestamp),
			KEY bot_type_timestamp (bot_type, visit_timestamp)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( self::DB_VERSION_OPTION, self::DB_VERSION );

		$this->logger->info( 'Bot analytics table created/updated.', array(
			'version' => self::DB_VERSION,
		) );
	}

	/**
	 * Detect bot from user agent.
	 *
	 * @since 1.4.0
	 * @param string $user_agent The user agent string.
	 * @return array|false Bot information or false if not a known bot.
	 */
	public function detect_bot( $user_agent ) {
		if ( empty( $user_agent ) ) {
			return false;
		}

		// Check known bots first.
		foreach ( self::$known_bots as $bot_type => $bot_info ) {
			if ( preg_match( $bot_info['pattern'], $user_agent ) ) {
				return array(
					'type'  => $bot_type,
					'name'  => $bot_info['name'],
					'color' => $bot_info['color'],
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
				return array(
					'type'  => 'Custom_' . sanitize_title( $custom_bot['name'] ),
					'name'  => $custom_bot['name'],
					'color' => '#8B5CF6', // Purple color for custom bots.
				);
			}
		}

		return false;
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

		return in_array( $bot_type, $blocked_bots, true );
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

		$insert_data = array(
			'bot_type'        => sanitize_text_field( $data['bot_type'] ),
			'bot_name'        => sanitize_text_field( $data['bot_name'] ?? '' ),
			'user_agent'      => sanitize_text_field( $data['user_agent'] ?? '' ),
			'url'             => esc_url_raw( $data['url'] ),
			'post_id'         => isset( $data['post_id'] ) ? absint( $data['post_id'] ) : null,
			'post_type'       => isset( $data['post_type'] ) ? sanitize_text_field( $data['post_type'] ) : null,
			'post_title'      => isset( $data['post_title'] ) ? sanitize_text_field( $data['post_title'] ) : null,
			'request_method'  => sanitize_text_field( $data['request_method'] ?? 'md_url' ),
			'cache_status'    => sanitize_text_field( $data['cache_status'] ?? 'MISS' ),
			'response_time'   => isset( $data['response_time'] ) ? absint( $data['response_time'] ) : null,
			'response_size'   => isset( $data['response_size'] ) ? absint( $data['response_size'] ) : null,
			'ip_address'      => $ip_address,
			'referer'         => isset( $data['referer'] ) ? esc_url_raw( $data['referer'] ) : null,
			'country_code'    => $country_code,
			'visit_timestamp' => current_time( 'mysql' ),
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
			'%s', // visit_timestamp
		);

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

		return $wpdb->insert_id;
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
			'total_visits'         => 0,
			'unique_pages'         => 0,
			'unique_bots'          => 0,
			'cache_hit_rate'       => 0,
			'avg_response_time'    => 0,
			'total_bandwidth'      => 0,
			'visits_today'         => 0,
			'visits_yesterday'     => 0,
			'visits_this_week'     => 0,
			'visits_this_month'    => 0,
			'trend_percentage'     => 0,
		);

		// Total visits.
		$summary['total_visits'] = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table_name} {$where}"
		);

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
	 * Uninstall - Drop table and delete options.
	 *
	 * @since 1.4.0
	 * @return void
	 */
	public static function uninstall() {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;
		$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
		delete_option( self::DB_VERSION_OPTION );
	}
}
