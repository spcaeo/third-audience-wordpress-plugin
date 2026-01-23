<?php
/**
 * Admin AJAX Analytics Handlers - Analytics drill-down AJAX operations.
 *
 * Handles session analytics, hero metrics, and bot details drill-down modals.
 *
 * @package ThirdAudience
 * @since   3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Admin_AJAX_Analytics
 *
 * Handles analytics drill-down AJAX operations for the admin interface.
 *
 * @since 3.3.1
 */
class TA_Admin_AJAX_Analytics {

	/**
	 * Security instance.
	 *
	 * @var TA_Security
	 */
	private $security;

	/**
	 * Singleton instance.
	 *
	 * @var TA_Admin_AJAX_Analytics|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 3.3.1
	 * @return TA_Admin_AJAX_Analytics
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
		$this->security = TA_Security::get_instance();
	}

	/**
	 * Register AJAX hooks.
	 *
	 * @since 3.3.1
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_ta_get_session_details', array( $this, 'ajax_get_session_details' ) );
		add_action( 'wp_ajax_ta_get_hero_metric_details', array( $this, 'ajax_get_hero_metric_details' ) );
		add_action( 'wp_ajax_ta_get_bot_details', array( $this, 'ajax_get_bot_details' ) );
		add_action( 'wp_ajax_ta_export_analytics_data', array( $this, 'ajax_export_analytics_data' ) );
	}

	/**
	 * AJAX handler for exporting analytics data as CSV.
	 *
	 * @since 3.3.3
	 * @return void
	 */
	public function ajax_export_analytics_data() {
		$this->security->verify_ajax_request( 'bot_analytics' );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$export_type = isset( $_POST['export_type'] ) ? sanitize_text_field( wp_unslash( $_POST['export_type'] ) ) : '';

		if ( empty( $export_type ) ) {
			wp_send_json_error( array( 'message' => __( 'Export type is required.', 'third-audience' ) ) );
		}

		$analytics = TA_Bot_Analytics::get_instance();
		$csv_data  = '';

		switch ( $export_type ) {
			case 'bot-distribution':
				$csv_data = $this->export_bot_distribution( $analytics );
				break;

			case 'top-content':
				$csv_data = $this->export_top_content( $analytics );
				break;

			case 'session-activity':
				$csv_data = $this->export_session_activity( $analytics );
				break;

			case 'crawl-budget':
				$csv_data = $this->export_crawl_budget( $analytics );
				break;

			case 'citation-performance':
				$csv_data = $this->export_citation_performance( $analytics );
				break;

			case 'content-insights':
				$csv_data = $this->export_content_insights( $analytics );
				break;

			case 'activity-timeline':
				$csv_data = $this->export_activity_timeline( $analytics );
				break;

			case 'live-activity':
				$csv_data = $this->export_live_activity( $analytics );
				break;

			default:
				wp_send_json_error( array( 'message' => __( 'Unknown export type.', 'third-audience' ) ) );
		}

		wp_send_json_success( array( 'csv' => $csv_data ) );
	}

	/**
	 * Export bot distribution data - Full raw data export.
	 *
	 * @since 3.3.3
	 * @since 3.3.9 Enhanced with all columns and unlimited rows.
	 * @param TA_Bot_Analytics $analytics Analytics instance.
	 * @return string CSV data.
	 */
	private function export_bot_distribution( $analytics ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ta_bot_analytics';

		// Get comprehensive bot stats with all metrics - no limit.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$bot_stats = $wpdb->get_results(
			"SELECT
				bot_type,
				bot_name,
				COUNT(*) as total_visits,
				COUNT(DISTINCT url) as unique_pages,
				COUNT(DISTINCT ip_address) as unique_ips,
				COUNT(DISTINCT DATE(visit_timestamp)) as active_days,
				MIN(visit_timestamp) as first_seen,
				MAX(visit_timestamp) as last_seen,
				ROUND(AVG(response_time), 0) as avg_response_ms,
				SUM(response_size) as total_bandwidth,
				SUM(CASE WHEN cache_status IN ('HIT', 'PRE_GENERATED') THEN 1 ELSE 0 END) as cache_hits,
				SUM(CASE WHEN cache_status = 'MISS' THEN 1 ELSE 0 END) as cache_misses,
				COUNT(DISTINCT country_code) as countries_count,
				GROUP_CONCAT(DISTINCT country_code) as countries_list,
				SUM(CASE WHEN traffic_type = 'citation_click' THEN 1 ELSE 0 END) as citation_clicks,
				SUM(CASE WHEN traffic_type = 'bot_crawl' THEN 1 ELSE 0 END) as crawl_visits
			FROM {$table_name}
			GROUP BY bot_type, bot_name
			ORDER BY total_visits DESC",
			ARRAY_A
		);

		$total = array_sum( wp_list_pluck( $bot_stats, 'total_visits' ) );

		$csv = "Bot Name,Bot Type,Total Visits,Share %,Unique Pages,Unique IPs,Active Days,First Seen,Last Seen,Avg Response (ms),Total Bandwidth,Cache Hits,Cache Misses,Cache Hit Rate %,Countries,Countries List,Citation Clicks,Crawl Visits\n";

		foreach ( $bot_stats as $bot ) {
			$share          = $total > 0 ? round( ( $bot['total_visits'] / $total ) * 100, 2 ) : 0;
			$cache_total    = $bot['cache_hits'] + $bot['cache_misses'];
			$cache_hit_rate = $cache_total > 0 ? round( ( $bot['cache_hits'] / $cache_total ) * 100, 1 ) : 0;

			$csv .= sprintf(
				"\"%s\",\"%s\",%d,%.2f,%d,%d,%d,\"%s\",\"%s\",%d,%d,%d,%d,%.1f,%d,\"%s\",%d,%d\n",
				str_replace( '"', '""', $bot['bot_name'] ),
				str_replace( '"', '""', $bot['bot_type'] ),
				$bot['total_visits'],
				$share,
				$bot['unique_pages'],
				$bot['unique_ips'],
				$bot['active_days'],
				$bot['first_seen'],
				$bot['last_seen'],
				$bot['avg_response_ms'] ?? 0,
				$bot['total_bandwidth'] ?? 0,
				$bot['cache_hits'],
				$bot['cache_misses'],
				$cache_hit_rate,
				$bot['countries_count'],
				$bot['countries_list'] ?? '',
				$bot['citation_clicks'],
				$bot['crawl_visits']
			);
		}

		return $csv;
	}

	/**
	 * Export top content data - Full raw data export.
	 *
	 * @since 3.3.3
	 * @since 3.3.9 Enhanced with all columns and unlimited rows.
	 * @param TA_Bot_Analytics $analytics Analytics instance.
	 * @return string CSV data.
	 */
	private function export_top_content( $analytics ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ta_bot_analytics';

		// Get comprehensive page stats - no limit.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$top_pages = $wpdb->get_results(
			"SELECT
				url,
				post_id,
				MAX(post_title) as post_title,
				MAX(post_type) as post_type,
				COUNT(*) as total_visits,
				COUNT(DISTINCT bot_name) as unique_bots,
				COUNT(DISTINCT bot_type) as unique_bot_types,
				COUNT(DISTINCT ip_address) as unique_ips,
				COUNT(DISTINCT DATE(visit_timestamp)) as active_days,
				MIN(visit_timestamp) as first_crawl,
				MAX(visit_timestamp) as last_crawl,
				ROUND(AVG(response_time), 0) as avg_response_ms,
				SUM(response_size) as total_bandwidth,
				SUM(CASE WHEN cache_status IN ('HIT', 'PRE_GENERATED') THEN 1 ELSE 0 END) as cache_hits,
				SUM(CASE WHEN cache_status = 'MISS' THEN 1 ELSE 0 END) as cache_misses,
				SUM(CASE WHEN traffic_type = 'citation_click' THEN 1 ELSE 0 END) as citations,
				SUM(CASE WHEN traffic_type = 'bot_crawl' THEN 1 ELSE 0 END) as crawls,
				GROUP_CONCAT(DISTINCT bot_type) as bot_types_list,
				MAX(content_word_count) as word_count,
				MAX(content_heading_count) as heading_count,
				MAX(content_has_schema) as has_schema
			FROM {$table_name}
			GROUP BY url, post_id
			ORDER BY total_visits DESC",
			ARRAY_A
		);

		$csv = "Page URL,Post ID,Post Title,Post Type,Total Visits,Unique Bots,Unique Bot Types,Unique IPs,Active Days,First Crawl,Last Crawl,Avg Response (ms),Total Bandwidth,Cache Hits,Cache Misses,Cache Hit Rate %,Citations,Crawls,Citation Rate %,Bot Types,Word Count,Heading Count,Has Schema\n";

		foreach ( $top_pages as $page ) {
			$cache_total    = $page['cache_hits'] + $page['cache_misses'];
			$cache_hit_rate = $cache_total > 0 ? round( ( $page['cache_hits'] / $cache_total ) * 100, 1 ) : 0;
			$citation_rate  = $page['crawls'] > 0 ? round( ( $page['citations'] / $page['crawls'] ) * 100, 1 ) : 0;

			$csv .= sprintf(
				"\"%s\",%d,\"%s\",\"%s\",%d,%d,%d,%d,%d,\"%s\",\"%s\",%d,%d,%d,%d,%.1f,%d,%d,%.1f,\"%s\",%d,%d,%d\n",
				str_replace( '"', '""', $page['url'] ),
				$page['post_id'] ?? 0,
				str_replace( '"', '""', $page['post_title'] ?? '' ),
				str_replace( '"', '""', $page['post_type'] ?? '' ),
				$page['total_visits'],
				$page['unique_bots'],
				$page['unique_bot_types'],
				$page['unique_ips'],
				$page['active_days'],
				$page['first_crawl'],
				$page['last_crawl'],
				$page['avg_response_ms'] ?? 0,
				$page['total_bandwidth'] ?? 0,
				$page['cache_hits'],
				$page['cache_misses'],
				$cache_hit_rate,
				$page['citations'],
				$page['crawls'],
				$citation_rate,
				str_replace( '"', '""', $page['bot_types_list'] ?? '' ),
				$page['word_count'] ?? 0,
				$page['heading_count'] ?? 0,
				$page['has_schema'] ?? 0
			);
		}

		return $csv;
	}

	/**
	 * Export session activity data - Full raw data export.
	 *
	 * @since 3.3.3
	 * @since 3.3.9 Enhanced with all columns and unlimited rows.
	 * @param TA_Bot_Analytics $analytics Analytics instance.
	 * @return string CSV data.
	 */
	private function export_session_activity( $analytics ) {
		global $wpdb;
		$fingerprints_table = $wpdb->prefix . 'ta_bot_fingerprints';
		$analytics_table    = $wpdb->prefix . 'ta_bot_analytics';

		// Check if fingerprints table exists.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$table_exists = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s',
				DB_NAME,
				$fingerprints_table
			)
		);

		if ( $table_exists ) {
			// Get from fingerprints table - no limit.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$fingerprints = $wpdb->get_results(
				"SELECT
					f.*,
					(SELECT COUNT(*) FROM {$analytics_table} a WHERE a.user_agent = f.user_agent) as total_visits,
					(SELECT COUNT(DISTINCT url) FROM {$analytics_table} a WHERE a.user_agent = f.user_agent) as unique_pages,
					(SELECT MAX(visit_timestamp) FROM {$analytics_table} a WHERE a.user_agent = f.user_agent) as last_activity
				FROM {$fingerprints_table} f
				ORDER BY f.total_requests DESC",
				ARRAY_A
			);

			$csv = "Fingerprint Hash,User Agent,IP Address,Bot Type,Classification,Total Requests,Unique Pages,First Seen,Last Seen,Last Activity,Request Interval Avg (sec),Request Interval StdDev,Typical Request Hour,Common Referer,Verification Status,Verification Method,Risk Score,Notes\n";

			foreach ( $fingerprints as $fp ) {
				$csv .= sprintf(
					"\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",%d,%d,\"%s\",\"%s\",\"%s\",%d,%d,%d,\"%s\",\"%s\",\"%s\",%.2f,\"%s\"\n",
					$fp['fingerprint_hash'] ?? '',
					str_replace( '"', '""', $fp['user_agent'] ?? '' ),
					$fp['ip_address'] ?? '',
					str_replace( '"', '""', $fp['bot_type'] ?? '' ),
					$fp['classification'] ?? '',
					$fp['total_requests'] ?? 0,
					$fp['unique_pages'] ?? 0,
					$fp['first_seen'] ?? '',
					$fp['last_seen'] ?? '',
					$fp['last_activity'] ?? '',
					$fp['request_interval_avg'] ?? 0,
					$fp['request_interval_stddev'] ?? 0,
					$fp['typical_request_hour'] ?? 0,
					str_replace( '"', '""', $fp['common_referer'] ?? '' ),
					$fp['verification_status'] ?? '',
					$fp['verification_method'] ?? '',
					$fp['risk_score'] ?? 0,
					str_replace( '"', '""', $fp['notes'] ?? '' )
				);
			}
		} else {
			// Fallback: aggregate from analytics table.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$sessions = $wpdb->get_results(
				"SELECT
					bot_type,
					bot_name,
					user_agent,
					ip_address,
					COUNT(*) as total_visits,
					COUNT(DISTINCT url) as unique_pages,
					COUNT(DISTINCT DATE(visit_timestamp)) as active_days,
					MIN(visit_timestamp) as first_seen,
					MAX(visit_timestamp) as last_seen,
					ROUND(AVG(response_time), 0) as avg_response_ms,
					SUM(response_size) as total_bandwidth,
					TIMESTAMPDIFF(SECOND, MIN(visit_timestamp), MAX(visit_timestamp)) as session_duration_sec,
					GROUP_CONCAT(DISTINCT country_code) as countries
				FROM {$analytics_table}
				GROUP BY bot_type, bot_name, user_agent, ip_address
				ORDER BY total_visits DESC",
				ARRAY_A
			);

			$csv = "Bot Type,Bot Name,User Agent,IP Address,Total Visits,Unique Pages,Active Days,First Seen,Last Seen,Session Duration (sec),Pages/Day,Avg Response (ms),Total Bandwidth,Countries\n";

			foreach ( $sessions as $s ) {
				$pages_per_day = $s['active_days'] > 0 ? round( $s['total_visits'] / $s['active_days'], 1 ) : 0;

				$csv .= sprintf(
					"\"%s\",\"%s\",\"%s\",\"%s\",%d,%d,%d,\"%s\",\"%s\",%d,%.1f,%d,%d,\"%s\"\n",
					str_replace( '"', '""', $s['bot_type'] ),
					str_replace( '"', '""', $s['bot_name'] ),
					str_replace( '"', '""', $s['user_agent'] ),
					$s['ip_address'] ?? '',
					$s['total_visits'],
					$s['unique_pages'],
					$s['active_days'],
					$s['first_seen'],
					$s['last_seen'],
					$s['session_duration_sec'] ?? 0,
					$pages_per_day,
					$s['avg_response_ms'] ?? 0,
					$s['total_bandwidth'] ?? 0,
					str_replace( '"', '""', $s['countries'] ?? '' )
				);
			}
		}

		return $csv;
	}

	/**
	 * Export crawl budget data - Full raw data export by time period.
	 *
	 * @since 3.3.3
	 * @since 3.3.9 Enhanced with hourly breakdown and bot-level detail.
	 * @param TA_Bot_Analytics $analytics Analytics instance.
	 * @return string CSV data.
	 */
	private function export_crawl_budget( $analytics ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ta_bot_analytics';

		// Get hourly crawl budget breakdown for last 7 days.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$hourly_data = $wpdb->get_results(
			"SELECT
				DATE(visit_timestamp) as date,
				HOUR(visit_timestamp) as hour,
				bot_type,
				COUNT(*) as requests,
				COUNT(DISTINCT url) as unique_pages,
				COUNT(DISTINCT ip_address) as unique_ips,
				SUM(response_size) as bandwidth_bytes,
				ROUND(AVG(response_time), 0) as avg_response_ms,
				SUM(CASE WHEN cache_status IN ('HIT', 'PRE_GENERATED') THEN 1 ELSE 0 END) as cache_hits,
				SUM(CASE WHEN cache_status = 'MISS' THEN 1 ELSE 0 END) as cache_misses
			FROM {$table_name}
			WHERE visit_timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
			GROUP BY DATE(visit_timestamp), HOUR(visit_timestamp), bot_type
			ORDER BY date DESC, hour DESC, requests DESC",
			ARRAY_A
		);

		$csv = "Date,Hour,Bot Type,Requests,Unique Pages,Unique IPs,Bandwidth (bytes),Bandwidth (MB),Avg Response (ms),Cache Hits,Cache Misses,Cache Hit Rate %\n";

		foreach ( $hourly_data as $row ) {
			$bandwidth_mb   = round( ( $row['bandwidth_bytes'] ?? 0 ) / 1048576, 2 );
			$cache_total    = $row['cache_hits'] + $row['cache_misses'];
			$cache_hit_rate = $cache_total > 0 ? round( ( $row['cache_hits'] / $cache_total ) * 100, 1 ) : 0;

			$csv .= sprintf(
				"\"%s\",%d,\"%s\",%d,%d,%d,%d,%.2f,%d,%d,%d,%.1f\n",
				$row['date'],
				$row['hour'],
				str_replace( '"', '""', $row['bot_type'] ),
				$row['requests'],
				$row['unique_pages'],
				$row['unique_ips'],
				$row['bandwidth_bytes'] ?? 0,
				$bandwidth_mb,
				$row['avg_response_ms'] ?? 0,
				$row['cache_hits'],
				$row['cache_misses'],
				$cache_hit_rate
			);
		}

		return $csv;
	}

	/**
	 * Export citation performance data - Full raw data export.
	 *
	 * @since 3.3.3
	 * @since 3.3.9 Enhanced with all columns and unlimited rows.
	 * @param TA_Bot_Analytics $analytics Analytics instance.
	 * @return string CSV data.
	 */
	private function export_citation_performance( $analytics ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ta_bot_analytics';

		// Get comprehensive citation/crawl data per page - no limit.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$citation_data = $wpdb->get_results(
			"SELECT
				url,
				post_id,
				MAX(post_title) as post_title,
				MAX(post_type) as post_type,
				SUM(CASE WHEN traffic_type = 'bot_crawl' THEN 1 ELSE 0 END) as crawls,
				SUM(CASE WHEN traffic_type = 'citation_click' THEN 1 ELSE 0 END) as citations,
				COUNT(DISTINCT CASE WHEN traffic_type = 'citation_click' THEN ai_platform END) as citation_platforms,
				GROUP_CONCAT(DISTINCT CASE WHEN traffic_type = 'citation_click' THEN ai_platform END) as platforms_list,
				COUNT(DISTINCT CASE WHEN traffic_type = 'bot_crawl' THEN bot_type END) as crawl_bot_types,
				GROUP_CONCAT(DISTINCT CASE WHEN traffic_type = 'bot_crawl' THEN bot_type END) as crawl_bots_list,
				MIN(CASE WHEN traffic_type = 'bot_crawl' THEN visit_timestamp END) as first_crawl,
				MAX(CASE WHEN traffic_type = 'bot_crawl' THEN visit_timestamp END) as last_crawl,
				MIN(CASE WHEN traffic_type = 'citation_click' THEN visit_timestamp END) as first_citation,
				MAX(CASE WHEN traffic_type = 'citation_click' THEN visit_timestamp END) as last_citation,
				MAX(content_word_count) as word_count,
				MAX(content_heading_count) as heading_count,
				MAX(content_image_count) as image_count,
				MAX(content_has_schema) as has_schema,
				MAX(content_freshness_days) as freshness_days,
				COUNT(DISTINCT search_query) as unique_queries,
				GROUP_CONCAT(DISTINCT search_query SEPARATOR ' | ') as search_queries
			FROM {$table_name}
			GROUP BY url, post_id
			HAVING crawls > 0 OR citations > 0
			ORDER BY citations DESC, crawls DESC",
			ARRAY_A
		);

		$csv = "Page URL,Post ID,Post Title,Post Type,Crawls,Citations,Citation Rate %,Citation Platforms,Platforms List,Crawl Bot Types,Crawl Bots List,First Crawl,Last Crawl,First Citation,Last Citation,Days to First Citation,Word Count,Heading Count,Image Count,Has Schema,Freshness (days),Unique Search Queries,Search Queries\n";

		foreach ( $citation_data as $page ) {
			$citation_rate      = $page['crawls'] > 0 ? round( ( $page['citations'] / $page['crawls'] ) * 100, 2 ) : 0;
			$days_to_citation   = '';
			if ( $page['first_crawl'] && $page['first_citation'] ) {
				$crawl_date       = strtotime( $page['first_crawl'] );
				$citation_date    = strtotime( $page['first_citation'] );
				$days_to_citation = max( 0, round( ( $citation_date - $crawl_date ) / 86400 ) );
			}

			$csv .= sprintf(
				"\"%s\",%d,\"%s\",\"%s\",%d,%d,%.2f,%d,\"%s\",%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",%s,%d,%d,%d,%d,%d,%d,\"%s\"\n",
				str_replace( '"', '""', $page['url'] ),
				$page['post_id'] ?? 0,
				str_replace( '"', '""', $page['post_title'] ?? '' ),
				str_replace( '"', '""', $page['post_type'] ?? '' ),
				$page['crawls'],
				$page['citations'],
				$citation_rate,
				$page['citation_platforms'],
				str_replace( '"', '""', $page['platforms_list'] ?? '' ),
				$page['crawl_bot_types'],
				str_replace( '"', '""', $page['crawl_bots_list'] ?? '' ),
				$page['first_crawl'] ?? '',
				$page['last_crawl'] ?? '',
				$page['first_citation'] ?? '',
				$page['last_citation'] ?? '',
				$days_to_citation,
				$page['word_count'] ?? 0,
				$page['heading_count'] ?? 0,
				$page['image_count'] ?? 0,
				$page['has_schema'] ?? 0,
				$page['freshness_days'] ?? 0,
				$page['unique_queries'] ?? 0,
				str_replace( '"', '""', substr( $page['search_queries'] ?? '', 0, 500 ) )
			);
		}

		return $csv;
	}

	/**
	 * Export content insights data - Full raw data export per post.
	 *
	 * @since 3.3.3
	 * @since 3.3.9 Enhanced with per-post breakdown.
	 * @param TA_Bot_Analytics $analytics Analytics instance.
	 * @return string CSV data.
	 */
	private function export_content_insights( $analytics ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ta_bot_analytics';

		// Get per-post content analysis data - no limit.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$content_data = $wpdb->get_results(
			"SELECT
				url,
				post_id,
				MAX(post_title) as post_title,
				MAX(post_type) as post_type,
				MAX(content_word_count) as word_count,
				MAX(content_heading_count) as heading_count,
				MAX(content_image_count) as image_count,
				MAX(content_has_schema) as has_schema,
				MAX(content_freshness_days) as freshness_days,
				COUNT(*) as total_visits,
				SUM(CASE WHEN traffic_type = 'bot_crawl' THEN 1 ELSE 0 END) as crawls,
				SUM(CASE WHEN traffic_type = 'citation_click' THEN 1 ELSE 0 END) as citations,
				COUNT(DISTINCT bot_type) as unique_bot_types,
				GROUP_CONCAT(DISTINCT bot_type) as bot_types,
				MIN(visit_timestamp) as first_visit,
				MAX(visit_timestamp) as last_visit,
				ROUND(AVG(response_time), 0) as avg_response_ms,
				SUM(CASE WHEN cache_status IN ('HIT', 'PRE_GENERATED') THEN 1 ELSE 0 END) as cache_hits
			FROM {$table_name}
			WHERE post_id IS NOT NULL AND post_id > 0
			GROUP BY url, post_id
			ORDER BY citations DESC, crawls DESC",
			ARRAY_A
		);

		$csv = "Page URL,Post ID,Post Title,Post Type,Word Count,Heading Count,Image Count,Has Schema,Freshness (days),Total Visits,Crawls,Citations,Citation Rate %,Unique Bot Types,Bot Types,First Visit,Last Visit,Avg Response (ms),Cache Hits,Cache Hit Rate %\n";

		foreach ( $content_data as $row ) {
			$citation_rate  = $row['crawls'] > 0 ? round( ( $row['citations'] / $row['crawls'] ) * 100, 2 ) : 0;
			$cache_hit_rate = $row['total_visits'] > 0 ? round( ( $row['cache_hits'] / $row['total_visits'] ) * 100, 1 ) : 0;

			$csv .= sprintf(
				"\"%s\",%d,\"%s\",\"%s\",%d,%d,%d,%d,%d,%d,%d,%d,%.2f,%d,\"%s\",\"%s\",\"%s\",%d,%d,%.1f\n",
				str_replace( '"', '""', $row['url'] ),
				$row['post_id'] ?? 0,
				str_replace( '"', '""', $row['post_title'] ?? '' ),
				str_replace( '"', '""', $row['post_type'] ?? '' ),
				$row['word_count'] ?? 0,
				$row['heading_count'] ?? 0,
				$row['image_count'] ?? 0,
				$row['has_schema'] ?? 0,
				$row['freshness_days'] ?? 0,
				$row['total_visits'],
				$row['crawls'],
				$row['citations'],
				$citation_rate,
				$row['unique_bot_types'],
				str_replace( '"', '""', $row['bot_types'] ?? '' ),
				$row['first_visit'] ?? '',
				$row['last_visit'] ?? '',
				$row['avg_response_ms'] ?? 0,
				$row['cache_hits'],
				$cache_hit_rate
			);
		}

		return $csv;
	}

	/**
	 * Export activity timeline data - Full raw data export.
	 *
	 * @since 3.3.3
	 * @since 3.3.9 Enhanced with hourly granularity and all metrics.
	 * @param TA_Bot_Analytics $analytics Analytics instance.
	 * @return string CSV data.
	 */
	private function export_activity_timeline( $analytics ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ta_bot_analytics';

		// Get comprehensive timeline data - all available history.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$timeline = $wpdb->get_results(
			"SELECT
				DATE(visit_timestamp) as date,
				HOUR(visit_timestamp) as hour,
				COUNT(*) as total_visits,
				COUNT(DISTINCT bot_name) as unique_bots,
				COUNT(DISTINCT bot_type) as unique_bot_types,
				COUNT(DISTINCT url) as unique_pages,
				COUNT(DISTINCT ip_address) as unique_ips,
				SUM(response_size) as total_bandwidth,
				ROUND(AVG(response_time), 0) as avg_response_ms,
				SUM(CASE WHEN cache_status IN ('HIT', 'PRE_GENERATED') THEN 1 ELSE 0 END) as cache_hits,
				SUM(CASE WHEN cache_status = 'MISS' THEN 1 ELSE 0 END) as cache_misses,
				SUM(CASE WHEN traffic_type = 'citation_click' THEN 1 ELSE 0 END) as citations,
				SUM(CASE WHEN traffic_type = 'bot_crawl' THEN 1 ELSE 0 END) as crawls,
				GROUP_CONCAT(DISTINCT bot_type) as bot_types,
				GROUP_CONCAT(DISTINCT country_code) as countries
			FROM {$table_name}
			GROUP BY DATE(visit_timestamp), HOUR(visit_timestamp)
			ORDER BY date DESC, hour DESC",
			ARRAY_A
		);

		$csv = "Date,Hour,Total Visits,Unique Bots,Unique Bot Types,Unique Pages,Unique IPs,Total Bandwidth (bytes),Bandwidth (MB),Avg Response (ms),Cache Hits,Cache Misses,Cache Hit Rate %,Citations,Crawls,Bot Types,Countries\n";

		foreach ( $timeline as $row ) {
			$bandwidth_mb   = round( ( $row['total_bandwidth'] ?? 0 ) / 1048576, 2 );
			$cache_total    = $row['cache_hits'] + $row['cache_misses'];
			$cache_hit_rate = $cache_total > 0 ? round( ( $row['cache_hits'] / $cache_total ) * 100, 1 ) : 0;

			$csv .= sprintf(
				"\"%s\",%d,%d,%d,%d,%d,%d,%d,%.2f,%d,%d,%d,%.1f,%d,%d,\"%s\",\"%s\"\n",
				$row['date'],
				$row['hour'],
				$row['total_visits'],
				$row['unique_bots'],
				$row['unique_bot_types'],
				$row['unique_pages'],
				$row['unique_ips'],
				$row['total_bandwidth'] ?? 0,
				$bandwidth_mb,
				$row['avg_response_ms'] ?? 0,
				$row['cache_hits'],
				$row['cache_misses'],
				$cache_hit_rate,
				$row['citations'],
				$row['crawls'],
				str_replace( '"', '""', $row['bot_types'] ?? '' ),
				str_replace( '"', '""', $row['countries'] ?? '' )
			);
		}

		return $csv;
	}

	/**
	 * Export live activity data - Full raw data export (ALL records).
	 *
	 * @since 3.3.3
	 * @since 3.3.9 Enhanced with all columns and all records (no limit).
	 * @param TA_Bot_Analytics $analytics Analytics instance.
	 * @return string CSV data.
	 */
	private function export_live_activity( $analytics ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ta_bot_analytics';

		// Get ALL raw visit records - complete export.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$visits = $wpdb->get_results(
			"SELECT
				id,
				bot_type,
				bot_name,
				user_agent,
				url,
				post_id,
				post_type,
				post_title,
				request_method,
				cache_status,
				response_time,
				response_size,
				ip_address,
				referer,
				country_code,
				traffic_type,
				content_type,
				ai_platform,
				search_query,
				referer_source,
				referer_medium,
				detection_method,
				confidence_score,
				content_word_count,
				content_heading_count,
				content_image_count,
				content_has_schema,
				content_freshness_days,
				visit_timestamp,
				created_at
			FROM {$table_name}
			ORDER BY visit_timestamp DESC",
			ARRAY_A
		);

		$csv = "ID,Bot Type,Bot Name,User Agent,URL,Post ID,Post Type,Post Title,Request Method,Cache Status,Response Time (ms),Response Size (bytes),IP Address,Referer,Country Code,Traffic Type,Content Type,AI Platform,Search Query,Referer Source,Referer Medium,Detection Method,Confidence Score,Word Count,Heading Count,Image Count,Has Schema,Freshness (days),Visit Timestamp,Created At\n";

		foreach ( $visits as $v ) {
			$csv .= sprintf(
				"%d,\"%s\",\"%s\",\"%s\",\"%s\",%d,\"%s\",\"%s\",\"%s\",\"%s\",%d,%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",%.2f,%d,%d,%d,%d,%d,\"%s\",\"%s\"\n",
				$v['id'],
				str_replace( '"', '""', $v['bot_type'] ?? '' ),
				str_replace( '"', '""', $v['bot_name'] ?? '' ),
				str_replace( '"', '""', substr( $v['user_agent'] ?? '', 0, 200 ) ),
				str_replace( '"', '""', $v['url'] ?? '' ),
				$v['post_id'] ?? 0,
				str_replace( '"', '""', $v['post_type'] ?? '' ),
				str_replace( '"', '""', $v['post_title'] ?? '' ),
				str_replace( '"', '""', $v['request_method'] ?? '' ),
				str_replace( '"', '""', $v['cache_status'] ?? '' ),
				$v['response_time'] ?? 0,
				$v['response_size'] ?? 0,
				$v['ip_address'] ?? '',
				str_replace( '"', '""', substr( $v['referer'] ?? '', 0, 200 ) ),
				$v['country_code'] ?? '',
				str_replace( '"', '""', $v['traffic_type'] ?? '' ),
				str_replace( '"', '""', $v['content_type'] ?? '' ),
				str_replace( '"', '""', $v['ai_platform'] ?? '' ),
				str_replace( '"', '""', substr( $v['search_query'] ?? '', 0, 200 ) ),
				str_replace( '"', '""', $v['referer_source'] ?? '' ),
				str_replace( '"', '""', $v['referer_medium'] ?? '' ),
				str_replace( '"', '""', $v['detection_method'] ?? '' ),
				$v['confidence_score'] ?? 0,
				$v['content_word_count'] ?? 0,
				$v['content_heading_count'] ?? 0,
				$v['content_image_count'] ?? 0,
				$v['content_has_schema'] ?? 0,
				$v['content_freshness_days'] ?? 0,
				$v['visit_timestamp'] ?? '',
				$v['created_at'] ?? ''
			);
		}

		return $csv;
	}

	/**
	 * AJAX handler for session analytics drill-down.
	 *
	 * Returns detailed bot fingerprint data for the session analytics modal.
	 *
	 * @since 3.2.2
	 * @return void
	 */
	public function ajax_get_session_details() {
		check_ajax_referer( 'ta_bot_analytics', 'nonce' );
		$this->security->verify_admin_capability();

		$sort_by = isset( $_POST['sort_by'] ) ? sanitize_text_field( wp_unslash( $_POST['sort_by'] ) ) : 'last_seen';
		$order   = isset( $_POST['order'] ) ? sanitize_text_field( wp_unslash( $_POST['order'] ) ) : 'DESC';

		$analytics    = TA_Bot_Analytics::get_instance();
		$fingerprints = $analytics->get_bot_fingerprints_list( $sort_by, $order, 50 );

		// Get summary stats too.
		$session_stats = $analytics->get_session_analytics();

		wp_send_json_success(
			array(
				'fingerprints' => $fingerprints,
				'summary'      => $session_stats,
			)
		);
	}

	/**
	 * AJAX handler for hero metrics drill-down.
	 *
	 * @since 3.2.3
	 * @return void
	 */
	public function ajax_get_hero_metric_details() {
		check_ajax_referer( 'ta_bot_analytics', 'nonce' );
		$this->security->verify_admin_capability();

		$metric = isset( $_POST['metric'] ) ? sanitize_text_field( wp_unslash( $_POST['metric'] ) ) : '';

		$analytics = TA_Bot_Analytics::get_instance();
		$summary   = $analytics->get_summary( array() );
		$bot_stats = $analytics->get_visits_by_bot( array() );
		$top_pages = $analytics->get_top_pages( array(), 20 );

		$response = array();

		switch ( $metric ) {
			case 'total_visits':
				$response = $this->get_total_visits_details( $summary, $bot_stats );
				break;
			case 'pages_crawled':
				$response = $this->get_pages_crawled_details( $summary, $top_pages );
				break;
			case 'cache_hit_rate':
				$response = $this->get_cache_details( $summary, $analytics );
				break;
			case 'avg_response':
				$response = $this->get_response_time_details( $summary, $analytics );
				break;
			case 'verified_bots':
				$response = $this->get_verification_details( $summary, $bot_stats );
				break;
			default:
				wp_send_json_error( array( 'message' => 'Invalid metric' ) );
		}

		wp_send_json_success( $response );
	}

	/**
	 * AJAX handler for bot diagnostic drill-down modal.
	 *
	 * Returns comprehensive bot details for the bot management diagnostic modal.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public function ajax_get_bot_details() {
		check_ajax_referer( 'ta_bot_management', 'nonce' );
		$this->security->verify_admin_capability();

		$bot_type = isset( $_POST['bot_type'] ) ? sanitize_text_field( wp_unslash( $_POST['bot_type'] ) ) : '';
		$bot_name = isset( $_POST['bot_name'] ) ? sanitize_text_field( wp_unslash( $_POST['bot_name'] ) ) : '';

		if ( empty( $bot_type ) ) {
			wp_send_json_error( array( 'message' => __( 'Bot type is required.', 'third-audience' ) ) );
		}

		$analytics = TA_Bot_Analytics::get_instance();
		$details   = $analytics->get_bot_details( $bot_type, $bot_name );

		wp_send_json_success( $details );
	}

	/**
	 * Get total visits breakdown data.
	 *
	 * @param array $summary Bot analytics summary.
	 * @param array $bot_stats Bot visit statistics.
	 * @return array Response data.
	 */
	private function get_total_visits_details( $summary, $bot_stats ) {
		$total  = $summary['total_visits'];
		$labels = array();
		$values = array();
		$pcts   = array();
		$rows   = array();

		foreach ( $bot_stats as $bot ) {
			$labels[] = $bot['bot_type'];
			$values[] = (int) $bot['visit_count'];
			$pct      = $total > 0 ? round( ( $bot['visit_count'] / $total ) * 100, 1 ) . '%' : '0%';
			$pcts[]   = $pct;
			$rows[]   = array(
				'<span class="ta-bot-name">' . esc_html( $bot['bot_type'] ) . '</span>',
				'<strong>' . number_format( $bot['visit_count'] ) . '</strong>',
				$pct,
				esc_html( $bot['last_visit_human'] ?? '-' ),
			);
		}

		return array(
			'stats'         => array(
				array( 'label' => __( 'Total Visits', 'third-audience' ), 'value' => number_format( $total ) ),
				array( 'label' => __( 'Today', 'third-audience' ), 'value' => number_format( $summary['visits_today'] ) ),
				array( 'label' => __( 'Unique Bots', 'third-audience' ), 'value' => number_format( $summary['unique_bots'] ) ),
			),
			'chart_title'   => __( 'Visits by Bot Type', 'third-audience' ),
			'chart_type'    => 'doughnut',
			'chart_data'    => array(
				'labels'      => $labels,
				'values'      => $values,
				'percentages' => $pcts,
			),
			'table_title'   => __( 'Bot Visit Details', 'third-audience' ),
			'table_headers' => array(
				array( 'label' => __( 'Bot', 'third-audience' ), 'align' => 'left' ),
				array( 'label' => __( 'Visits', 'third-audience' ), 'align' => 'right' ),
				array( 'label' => __( 'Share', 'third-audience' ), 'align' => 'right' ),
				array( 'label' => __( 'Last Visit', 'third-audience' ), 'align' => 'left' ),
			),
			'table_rows'    => $rows,
		);
	}

	/**
	 * Get pages crawled breakdown data.
	 *
	 * @param array $summary Bot analytics summary.
	 * @param array $top_pages Top crawled pages.
	 * @return array Response data.
	 */
	private function get_pages_crawled_details( $summary, $top_pages ) {
		$labels = array();
		$values = array();
		$rows   = array();

		foreach ( array_slice( $top_pages, 0, 10 ) as $page ) {
			$title    = $page['page_title'] ?: $page['page_url'];
			$labels[] = strlen( $title ) > 25 ? substr( $title, 0, 25 ) . '...' : $title;
			$values[] = (int) $page['visit_count'];
		}

		foreach ( $top_pages as $page ) {
			$title  = $page['page_title'] ?: $page['page_url'];
			$rows[] = array(
				'<a href="' . esc_url( $page['page_url'] ) . '" target="_blank">' . esc_html( $title ) . '</a>',
				'<strong>' . number_format( $page['visit_count'] ) . '</strong>',
				number_format( $page['unique_bots'] ),
			);
		}

		return array(
			'stats'         => array(
				array( 'label' => __( 'Total Pages', 'third-audience' ), 'value' => number_format( $summary['unique_pages'] ) ),
				array( 'label' => __( 'Total Visits', 'third-audience' ), 'value' => number_format( $summary['total_visits'] ) ),
				array( 'label' => __( 'Avg Visits/Page', 'third-audience' ), 'value' => $summary['unique_pages'] > 0 ? number_format( $summary['total_visits'] / $summary['unique_pages'], 1 ) : '0' ),
			),
			'chart_title'   => __( 'Top 10 Pages by Visits', 'third-audience' ),
			'chart_type'    => 'bar',
			'chart_data'    => array(
				'labels' => $labels,
				'values' => $values,
			),
			'table_title'   => __( 'All Crawled Pages', 'third-audience' ),
			'table_headers' => array(
				array( 'label' => __( 'Page', 'third-audience' ), 'align' => 'left' ),
				array( 'label' => __( 'Visits', 'third-audience' ), 'align' => 'right' ),
				array( 'label' => __( 'Unique Bots', 'third-audience' ), 'align' => 'right' ),
			),
			'table_rows'    => $rows,
		);
	}

	/**
	 * Get cache performance breakdown data.
	 *
	 * @param array            $summary Bot analytics summary.
	 * @param TA_Bot_Analytics $analytics Analytics instance.
	 * @return array Response data.
	 */
	private function get_cache_details( $summary, $analytics ) {
		$cache_stats = $analytics->get_cache_performance_stats();

		$hits   = $cache_stats['hits'] ?? 0;
		$misses = $cache_stats['misses'] ?? 0;
		$pregen = $cache_stats['pre_generated'] ?? 0;
		$na     = $cache_stats['not_applicable'] ?? 0;
		$total  = $hits + $misses + $pregen + $na;

		$labels = array( 'Cache Hit', 'Cache Miss', 'Pre-generated', 'N/A' );
		$values = array( $hits, $misses, $pregen, $na );
		$pcts   = array();
		foreach ( $values as $v ) {
			$pcts[] = $total > 0 ? round( ( $v / $total ) * 100, 1 ) . '%' : '0%';
		}

		$rows = array();
		for ( $i = 0; $i < count( $labels ); $i++ ) {
			$rows[] = array(
				esc_html( $labels[ $i ] ),
				'<strong>' . number_format( $values[ $i ] ) . '</strong>',
				$pcts[ $i ],
			);
		}

		return array(
			'stats'         => array(
				array( 'label' => __( 'Hit Rate', 'third-audience' ), 'value' => $summary['cache_hit_rate'] . '%' ),
				array( 'label' => __( 'Cache Hits', 'third-audience' ), 'value' => number_format( $hits ) ),
				array( 'label' => __( 'Cache Misses', 'third-audience' ), 'value' => number_format( $misses ) ),
			),
			'chart_title'   => __( 'Cache Status Distribution', 'third-audience' ),
			'chart_type'    => 'doughnut',
			'chart_data'    => array(
				'labels'      => $labels,
				'values'      => $values,
				'percentages' => $pcts,
			),
			'table_title'   => __( 'Cache Breakdown', 'third-audience' ),
			'table_headers' => array(
				array( 'label' => __( 'Status', 'third-audience' ), 'align' => 'left' ),
				array( 'label' => __( 'Count', 'third-audience' ), 'align' => 'right' ),
				array( 'label' => __( 'Percentage', 'third-audience' ), 'align' => 'right' ),
			),
			'table_rows'    => $rows,
		);
	}

	/**
	 * Get response time breakdown data.
	 *
	 * @param array            $summary Bot analytics summary.
	 * @param TA_Bot_Analytics $analytics Analytics instance.
	 * @return array Response data.
	 */
	private function get_response_time_details( $summary, $analytics ) {
		$time_stats = $analytics->get_response_time_distribution();

		$labels = array();
		$values = array();
		$rows   = array();

		foreach ( $time_stats as $range => $count ) {
			$labels[] = $range;
			$values[] = (int) $count;
			$rows[]   = array(
				esc_html( $range ),
				'<strong>' . number_format( $count ) . '</strong>',
			);
		}

		// Calculate percentiles if available.
		$p50 = $analytics->get_response_time_percentile( 50 );
		$p95 = $analytics->get_response_time_percentile( 95 );

		return array(
			'stats'         => array(
				array( 'label' => __( 'Average', 'third-audience' ), 'value' => $summary['avg_response_time'] . 'ms' ),
				array( 'label' => __( 'Median (P50)', 'third-audience' ), 'value' => $p50 . 'ms' ),
				array( 'label' => __( '95th Percentile', 'third-audience' ), 'value' => $p95 . 'ms' ),
			),
			'chart_title'   => __( 'Response Time Distribution', 'third-audience' ),
			'chart_type'    => 'bar',
			'chart_data'    => array(
				'labels' => $labels,
				'values' => $values,
			),
			'table_title'   => __( 'Response Time Ranges', 'third-audience' ),
			'table_headers' => array(
				array( 'label' => __( 'Range', 'third-audience' ), 'align' => 'left' ),
				array( 'label' => __( 'Requests', 'third-audience' ), 'align' => 'right' ),
			),
			'table_rows'    => $rows,
		);
	}

	/**
	 * Get bot verification breakdown data.
	 *
	 * @param array $summary Bot analytics summary.
	 * @param array $bot_stats Bot visit statistics.
	 * @return array Response data.
	 */
	private function get_verification_details( $summary, $bot_stats ) {
		$verified   = $summary['ip_verified_count'];
		$total      = $summary['total_visits'];
		$unverified = $total - $verified;

		$labels = array( __( 'Verified', 'third-audience' ), __( 'Unverified', 'third-audience' ) );
		$values = array( $verified, $unverified );
		$pcts   = array(
			$total > 0 ? round( ( $verified / $total ) * 100, 1 ) . '%' : '0%',
			$total > 0 ? round( ( $unverified / $total ) * 100, 1 ) . '%' : '0%',
		);

		$rows = array();
		foreach ( $bot_stats as $bot ) {
			$rows[] = array(
				'<span class="ta-bot-name">' . esc_html( $bot['bot_type'] ) . '</span>',
				'<strong>' . number_format( $bot['visit_count'] ) . '</strong>',
				isset( $bot['verified_count'] ) ? number_format( $bot['verified_count'] ) : '0',
				isset( $bot['verified_count'] ) && $bot['visit_count'] > 0
					? round( ( $bot['verified_count'] / $bot['visit_count'] ) * 100, 1 ) . '%'
					: '0%',
			);
		}

		return array(
			'stats'         => array(
				array( 'label' => __( 'Verification Rate', 'third-audience' ), 'value' => $summary['ip_verified_percentage'] . '%' ),
				array( 'label' => __( 'Verified Visits', 'third-audience' ), 'value' => number_format( $verified ) ),
				array( 'label' => __( 'Unverified Visits', 'third-audience' ), 'value' => number_format( $unverified ) ),
			),
			'chart_title'   => __( 'Verification Status', 'third-audience' ),
			'chart_type'    => 'doughnut',
			'chart_data'    => array(
				'labels'      => $labels,
				'values'      => $values,
				'percentages' => $pcts,
			),
			'table_title'   => __( 'Verification by Bot', 'third-audience' ),
			'table_headers' => array(
				array( 'label' => __( 'Bot', 'third-audience' ), 'align' => 'left' ),
				array( 'label' => __( 'Total Visits', 'third-audience' ), 'align' => 'right' ),
				array( 'label' => __( 'Verified', 'third-audience' ), 'align' => 'right' ),
				array( 'label' => __( 'Rate', 'third-audience' ), 'align' => 'right' ),
			),
			'table_rows'    => $rows,
		);
	}
}
