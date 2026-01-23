<?php
/**
 * Analytics Query - Reporting queries for bot analytics.
 *
 * Handles all database queries for analytics reporting and summaries.
 *
 * @package ThirdAudience
 * @since   3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Analytics_Query
 *
 * Provides reporting and analytics queries for bot visit data.
 *
 * @since 3.3.1
 */
class TA_Analytics_Query {

	/**
	 * Analytics table name.
	 *
	 * @var string
	 */
	const TABLE_NAME = 'ta_bot_analytics';

	/**
	 * Bot detector instance.
	 *
	 * @var TA_Bot_Detector
	 */
	private $detector;

	/**
	 * Singleton instance.
	 *
	 * @var TA_Analytics_Query|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 3.3.1
	 * @return TA_Analytics_Query
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
	 * @since 3.3.1
	 */
	private function __construct() {
		$this->detector = TA_Bot_Detector::get_instance();
	}

	/**
	 * Build WHERE clause from filters.
	 *
	 * @since 1.4.0
	 * @param array $filters Filter parameters.
	 * @return string SQL WHERE clause.
	 */
	public function build_where_clause( $filters ) {
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
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$summary['total_visits'] = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table_name} {$where}"
		);

		// IP Verification stats.
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
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$summary['unique_pages'] = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT url) FROM {$table_name} {$where}"
		);

		// Unique bots.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$summary['unique_bots'] = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT bot_type) FROM {$table_name} {$where}"
		);

		// Cache hit rate.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$cache_hits = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table_name} {$where} AND cache_status IN ('HIT', 'PRE_GENERATED')"
		);
		if ( $summary['total_visits'] > 0 ) {
			$summary['cache_hit_rate'] = round( ( $cache_hits / $summary['total_visits'] ) * 100, 1 );
		}

		// Average response time.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$summary['avg_response_time'] = (int) $wpdb->get_var(
			"SELECT AVG(response_time) FROM {$table_name} {$where} AND response_time IS NOT NULL"
		);

		// Total bandwidth.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$summary['total_bandwidth'] = (int) $wpdb->get_var(
			"SELECT SUM(response_size) FROM {$table_name} {$where} AND response_size IS NOT NULL"
		);

		// Time-based stats.
		$today       = gmdate( 'Y-m-d' );
		$yesterday   = gmdate( 'Y-m-d', strtotime( '-1 day' ) );
		$week_start  = gmdate( 'Y-m-d', strtotime( '-7 days' ) );
		$month_start = gmdate( 'Y-m-d', strtotime( '-30 days' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$summary['visits_today'] = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE DATE(visit_timestamp) = %s",
				$today
			)
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$summary['visits_yesterday'] = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE DATE(visit_timestamp) = %s",
				$yesterday
			)
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$summary['visits_this_week'] = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE DATE(visit_timestamp) >= %s",
				$week_start
			)
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
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

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
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
			$result['color'] = $this->detector->get_bot_color( $result['bot_type'] );
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

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
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
	 * @since 2.7.0
	 * @param array $filters Optional filters.
	 * @param int   $limit   Number of results.
	 * @return array Citation rate data by URL.
	 */
	public function get_citation_to_crawl_ratio( $filters = array(), $limit = 20 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

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

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
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

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
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

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
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
			'total_bot_fingerprints' => absint( $stats['total_bots'] ?? 0 ),
			'avg_visits_per_bot'     => round( floatval( $stats['avg_visits_per_bot'] ?? 0 ), 1 ),
			'avg_pages_per_session'  => round( floatval( $stats['avg_pages_per_session'] ?? 0 ), 1 ),
			'avg_session_duration'   => absint( $stats['avg_session_duration'] ?? 0 ),
			'avg_request_interval'   => absint( $stats['avg_request_interval'] ?? 0 ),
			'avg_unique_paths_ratio' => round( floatval( $stats['avg_unique_paths_ratio'] ?? 0 ), 2 ),
		);
	}

	/**
	 * Get detailed bot fingerprints list.
	 *
	 * @since 3.2.2
	 * @param string $sort_by Column to sort by.
	 * @param string $order   Sort order (ASC/DESC).
	 * @param int    $limit   Number of results.
	 * @return array List of bot fingerprints.
	 */
	public function get_bot_fingerprints_list( $sort_by = 'last_seen', $order = 'DESC', $limit = 50 ) {
		global $wpdb;
		$fingerprints_table = $wpdb->prefix . 'ta_bot_fingerprints';

		$allowed_columns = array(
			'last_seen'         => 'last_seen',
			'first_seen'        => 'first_seen',
			'visit_count'       => 'visit_count',
			'pages_per_session' => 'pages_per_session_avg',
			'session_duration'  => 'session_duration_avg',
			'request_interval'  => 'request_interval_avg',
			'classification'    => 'classification',
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
					robots_txt_checked,
					respects_robots_txt,
					first_seen,
					last_seen
				FROM {$fingerprints_table}
				ORDER BY {$order_column} {$order_dir}
				LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		return $results ?: array();
	}

	/**
	 * Get top bots by session metrics.
	 *
	 * @since 2.6.0
	 * @param string $metric Metric to sort by.
	 * @param int    $limit  Number of results.
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

		$order_column = $allowed_metrics[ $metric ] ?? 'pages_per_session_avg';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					classification as bot_type,
					user_agent,
					visit_count,
					pages_per_session_avg,
					session_duration_avg,
					request_interval_avg,
					first_seen,
					last_seen
				FROM {$fingerprints_table}
				WHERE {$order_column} IS NOT NULL
				ORDER BY {$order_column} DESC
				LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		return $results ?: array();
	}

	/**
	 * Get crawl budget metrics.
	 *
	 * @since 2.7.0
	 * @param string|null $bot_type Optional bot type filter.
	 * @param string      $period   Time period.
	 * @return array Crawl budget metrics.
	 */
	public function get_crawl_budget_metrics( $bot_type = null, $period = 'day' ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$where_conditions = array( '1=1' );

		if ( $bot_type ) {
			$where_conditions[] = $wpdb->prepare( 'bot_type = %s', $bot_type );
		}

		// Time period filter.
		switch ( $period ) {
			case 'week':
				$date_threshold = gmdate( 'Y-m-d', strtotime( '-7 days' ) );
				break;
			case 'month':
				$date_threshold = gmdate( 'Y-m-d', strtotime( '-30 days' ) );
				break;
			default: // day.
				$date_threshold = gmdate( 'Y-m-d', strtotime( '-1 day' ) );
				break;
		}

		$where_conditions[] = $wpdb->prepare( 'DATE(visit_timestamp) >= %s', $date_threshold );
		$where_sql          = 'WHERE ' . implode( ' AND ', $where_conditions );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$metrics = $wpdb->get_row(
			"SELECT
				COUNT(*) as total_requests,
				COUNT(DISTINCT url) as unique_pages,
				AVG(response_time) as avg_response_time,
				SUM(response_size) as total_bandwidth,
				SUM(CASE WHEN cache_status IN ('HIT', 'PRE_GENERATED') THEN 1 ELSE 0 END) as cache_hits
			FROM {$table_name}
			{$where_sql}",
			ARRAY_A
		);

		$total = absint( $metrics['total_requests'] ?? 0 );

		return array(
			'total_requests'   => $total,
			'unique_pages'     => absint( $metrics['unique_pages'] ?? 0 ),
			'avg_response_ms'  => round( floatval( $metrics['avg_response_time'] ?? 0 ) ),
			'total_bandwidth'  => absint( $metrics['total_bandwidth'] ?? 0 ),
			'cache_hit_rate'   => $total > 0 ? round( ( absint( $metrics['cache_hits'] ?? 0 ) / $total ) * 100, 1 ) : 0,
			'period'           => $period,
		);
	}

	/**
	 * Get content performance analysis.
	 *
	 * @since 2.7.0
	 * @param array $filters Optional filters.
	 * @return array Content performance data.
	 */
	public function get_content_performance_analysis( $filters = array() ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$where = $this->build_where_clause( $filters );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$cited_posts = $wpdb->get_results(
			"SELECT
				AVG(content_word_count) as avg_word_count,
				AVG(content_heading_count) as avg_headings,
				AVG(content_image_count) as avg_images,
				SUM(CASE WHEN content_has_schema = 1 THEN 1 ELSE 0 END) as with_schema,
				COUNT(*) as total
			FROM {$table_name}
			{$where} AND traffic_type = 'citation_click' AND content_word_count IS NOT NULL",
			ARRAY_A
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$crawled_posts = $wpdb->get_results(
			"SELECT
				AVG(content_word_count) as avg_word_count,
				AVG(content_heading_count) as avg_headings,
				AVG(content_image_count) as avg_images,
				SUM(CASE WHEN content_has_schema = 1 THEN 1 ELSE 0 END) as with_schema,
				COUNT(*) as total
			FROM {$table_name}
			{$where} AND traffic_type = 'bot_crawl' AND content_word_count IS NOT NULL",
			ARRAY_A
		);

		$cited   = $cited_posts[0] ?? array();
		$crawled = $crawled_posts[0] ?? array();

		return array(
			'cited'   => array(
				'avg_word_count'   => round( floatval( $cited['avg_word_count'] ?? 0 ) ),
				'avg_headings'     => round( floatval( $cited['avg_headings'] ?? 0 ), 1 ),
				'avg_images'       => round( floatval( $cited['avg_images'] ?? 0 ), 1 ),
				'schema_rate'      => absint( $cited['total'] ?? 0 ) > 0
					? round( ( absint( $cited['with_schema'] ?? 0 ) / absint( $cited['total'] ) ) * 100, 1 )
					: 0,
				'sample_size'      => absint( $cited['total'] ?? 0 ),
			),
			'crawled' => array(
				'avg_word_count'   => round( floatval( $crawled['avg_word_count'] ?? 0 ) ),
				'avg_headings'     => round( floatval( $crawled['avg_headings'] ?? 0 ), 1 ),
				'avg_images'       => round( floatval( $crawled['avg_images'] ?? 0 ), 1 ),
				'schema_rate'      => absint( $crawled['total'] ?? 0 ) > 0
					? round( ( absint( $crawled['with_schema'] ?? 0 ) / absint( $crawled['total'] ) ) * 100, 1 )
					: 0,
				'sample_size'      => absint( $crawled['total'] ?? 0 ),
			),
		);
	}

	/**
	 * Get comprehensive bot details for diagnostic modal.
	 *
	 * @since 3.3.0
	 * @param string $bot_type Bot type identifier.
	 * @param string $bot_name Bot display name.
	 * @return array Comprehensive bot details.
	 */
	public function get_bot_details( $bot_type, $bot_name ) {
		global $wpdb;
		$table_name         = $wpdb->prefix . self::TABLE_NAME;
		$fingerprints_table = $wpdb->prefix . 'ta_bot_fingerprints';

		// 1. Summary Stats.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$summary = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
					COUNT(*) as total_visits,
					COUNT(DISTINCT post_id) as unique_pages,
					COUNT(DISTINCT ip_address) as unique_ips,
					AVG(response_time) as avg_response_time,
					SUM(response_size) as total_bandwidth,
					SUM(CASE WHEN cache_status IN ('HIT', 'PRE_GENERATED') THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(*), 0) as cache_hit_rate,
					MIN(visit_timestamp) as first_seen,
					MAX(visit_timestamp) as last_seen
				FROM {$table_name}
				WHERE bot_type = %s",
				$bot_type
			),
			ARRAY_A
		);

		// 2. Behavior metrics from fingerprints.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$behavior = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
					AVG(pages_per_session_avg) as pages_per_session,
					AVG(session_duration_avg) as session_duration,
					AVG(request_interval_avg) as request_interval,
					AVG(respects_robots_txt) as robots_compliance
				FROM {$fingerprints_table}
				WHERE classification = %s",
				$bot_type
			),
			ARRAY_A
		);

		// 3. Detection methods.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$detection_methods = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					detection_method,
					COUNT(*) as count,
					AVG(confidence_score) as avg_confidence
				FROM {$table_name}
				WHERE bot_type = %s AND detection_method IS NOT NULL
				GROUP BY detection_method
				ORDER BY count DESC",
				$bot_type
			),
			ARRAY_A
		);

		// 4. Recent visits.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$recent_visits = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					visit_timestamp,
					url,
					post_title,
					ip_address,
					cache_status,
					response_time
				FROM {$table_name}
				WHERE bot_type = %s
				ORDER BY visit_timestamp DESC
				LIMIT 50",
				$bot_type
			),
			ARRAY_A
		);

		// 5. Top pages.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$top_pages = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					url,
					post_title,
					COUNT(*) as visits,
					AVG(content_word_count) as avg_word_count,
					AVG(content_heading_count) as avg_headings
				FROM {$table_name}
				WHERE bot_type = %s
				GROUP BY url, post_title
				ORDER BY visits DESC
				LIMIT 20",
				$bot_type
			),
			ARRAY_A
		);

		// 6. Citation data.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$citations = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
					COUNT(CASE WHEN traffic_type = 'bot_crawl' THEN 1 END) as total_crawls,
					COUNT(CASE WHEN traffic_type = 'citation_click' THEN 1 END) as total_citations
				FROM {$table_name}
				WHERE bot_type = %s",
				$bot_type
			),
			ARRAY_A
		);

		// 7. Response time distribution.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$response_dist = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
					SUM(CASE WHEN response_time < 50 THEN 1 ELSE 0 END) as fast_under_50,
					SUM(CASE WHEN response_time >= 50 AND response_time < 100 THEN 1 ELSE 0 END) as good_50_100,
					SUM(CASE WHEN response_time >= 100 AND response_time < 200 THEN 1 ELSE 0 END) as ok_100_200,
					SUM(CASE WHEN response_time >= 200 THEN 1 ELSE 0 END) as slow_over_200
				FROM {$table_name}
				WHERE bot_type = %s AND response_time IS NOT NULL",
				$bot_type
			),
			ARRAY_A
		);

		// 8. IP data.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$ip_data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					ip_address,
					country_code,
					COUNT(*) as visit_count,
					MAX(ip_verified) as verified
				FROM {$table_name}
				WHERE bot_type = %s AND ip_address IS NOT NULL
				GROUP BY ip_address, country_code
				ORDER BY visit_count DESC
				LIMIT 20",
				$bot_type
			),
			ARRAY_A
		);

		// 9. User agents.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$user_agents = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT user_agent
				FROM {$table_name}
				WHERE bot_type = %s
				LIMIT 10",
				$bot_type
			)
		);

		return array(
			'summary'               => $summary,
			'behavior'              => $behavior,
			'detection_methods'     => $detection_methods,
			'recent_visits'         => $recent_visits,
			'top_pages'             => $top_pages,
			'citations'             => $citations,
			'response_distribution' => $response_dist,
			'ip_data'               => $ip_data,
			'user_agents'           => $user_agents,
			'bot_info'              => array(
				'type'  => $bot_type,
				'name'  => $bot_name,
				'color' => $this->detector->get_bot_color( $bot_type ),
			),
		);
	}
}
