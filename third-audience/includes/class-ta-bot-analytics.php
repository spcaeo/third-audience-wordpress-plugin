<?php
/**
 * Bot Analytics - Orchestrator for AI bot visit tracking and analytics.
 *
 * Provides comprehensive analytics for AI bot visits including tracking,
 * reporting, filtering, and export functionality.
 *
 * This class acts as an orchestrator, delegating to specialized classes:
 * - TA_Bot_Detector: Bot detection and classification
 * - TA_Visit_Tracker: Visit recording and session tracking
 * - TA_Analytics_Query: Reporting and data queries
 * - TA_Data_Exporter: Export to CSV/JSON
 * - TA_Performance_Stats: Performance analytics
 * - TA_Geolocation: IP address utilities
 *
 * @package ThirdAudience
 * @since   1.4.0
 * @version 3.3.1 Refactored into modular architecture
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
	const DB_VERSION = '3.5.0';

	/**
	 * Option name for database version.
	 *
	 * @var string
	 */
	const DB_VERSION_OPTION = 'ta_bot_analytics_db_version';

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Bot detector instance.
	 *
	 * @var TA_Bot_Detector
	 */
	private $detector;

	/**
	 * Visit tracker instance.
	 *
	 * @var TA_Visit_Tracker
	 */
	private $tracker;

	/**
	 * Analytics query instance.
	 *
	 * @var TA_Analytics_Query
	 */
	private $query;

	/**
	 * Data exporter instance.
	 *
	 * @var TA_Data_Exporter
	 */
	private $exporter;

	/**
	 * Performance stats instance.
	 *
	 * @var TA_Performance_Stats
	 */
	private $performance;

	/**
	 * Geolocation instance.
	 *
	 * @var TA_Geolocation
	 */
	private $geolocation;

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

		// Initialize delegated services.
		$this->geolocation = TA_Geolocation::get_instance();
		$this->detector    = TA_Bot_Detector::get_instance();
		$this->performance = TA_Performance_Stats::get_instance();
		$this->query       = TA_Analytics_Query::get_instance();
		$this->exporter    = TA_Data_Exporter::get_instance();
		$this->tracker     = TA_Visit_Tracker::get_instance();

		// Initialize database tables.
		$this->maybe_create_table();

		// Migrate bot patterns if needed.
		$this->detector->maybe_migrate_patterns();

		// Hook into template_redirect to track AI citation clicks.
		add_action( 'template_redirect', array( $this, 'maybe_track_citation_click' ), 5 );

		// Hook to track ALL bot crawls on every page.
		add_action( 'template_redirect', array( $this, 'maybe_track_bot_crawl' ), 1 );

		// Frontend JavaScript citation tracker (works with cached pages).
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_citation_tracker' ) );

		// AJAX handlers for JS-based citation tracking (both logged-in and public).
		add_action( 'wp_ajax_ta_track_citation_js', array( $this, 'ajax_track_citation_js' ) );
		add_action( 'wp_ajax_nopriv_ta_track_citation_js', array( $this, 'ajax_track_citation_js' ) );
	}

	// =========================================================================
	// TRACKING HOOKS
	// =========================================================================

	/**
	 * Detect request type to distinguish HTML pages from RSC prefetch and API calls.
	 *
	 * @since 3.5.0
	 * @return string Request type: html_page, rsc_prefetch, api_call, or unknown.
	 */
	private function detect_request_type() {
		// Check if this is an RSC prefetch request (Next.js client-side navigation).
		if ( isset( $_GET['_rsc'] ) ) {
			return 'rsc_prefetch';
		}

		// Check if it's an API or AJAX request.
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return 'api_call';
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return 'api_call';
		}

		// Check if it's a REST API endpoint.
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		if ( strpos( $request_uri, '/wp-json/' ) !== false ) {
			return 'api_call';
		}

		// Check request method - initial HTML loads are typically GET.
		if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
			return 'api_call';
		}

		// If none of the above, this is likely an initial HTML page load.
		return 'html_page';
	}

	/**
	 * Maybe track AI citation click on page load.
	 *
	 * @since 2.2.0
	 * @return void
	 */
	public function maybe_track_citation_click() {
		if ( is_admin() || 'GET' !== $_SERVER['REQUEST_METHOD'] ) {
			return;
		}
		$this->tracker->track_citation_click();
	}

	/**
	 * Enqueue frontend citation tracker script.
	 *
	 * This JavaScript-based tracker works even when pages are served from cache.
	 *
	 * @since 3.3.7
	 * @return void
	 */
	public function enqueue_citation_tracker() {
		// Don't load on admin pages.
		if ( is_admin() ) {
			return;
		}

		wp_enqueue_script(
			'ta-citation-tracker',
			TA_PLUGIN_URL . 'public/js/citation-tracker.js',
			array(),
			TA_VERSION,
			true // Load in footer.
		);

		// Pass AJAX URL and nonce to script.
		wp_localize_script(
			'ta-citation-tracker',
			'taCitationTracker',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ta_citation_tracker' ),
				'debug'   => defined( 'WP_DEBUG' ) && WP_DEBUG,
			)
		);
	}

	/**
	 * AJAX handler for JavaScript-based citation tracking.
	 *
	 * Receives citation data from frontend JS when pages are served from cache.
	 *
	 * @since 3.3.7
	 * @return void
	 */
	public function ajax_track_citation_js() {
		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'ta_citation_tracker' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid nonce' ), 403 );
		}

		// Get citation data from request.
		$platform          = isset( $_POST['platform'] ) ? sanitize_text_field( wp_unslash( $_POST['platform'] ) ) : '';
		$method            = isset( $_POST['method'] ) ? sanitize_text_field( wp_unslash( $_POST['method'] ) ) : '';
		$url               = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
		$path              = isset( $_POST['path'] ) ? sanitize_text_field( wp_unslash( $_POST['path'] ) ) : '';
		$referrer          = isset( $_POST['referrer'] ) ? esc_url_raw( wp_unslash( $_POST['referrer'] ) ) : '';
		$search_query      = isset( $_POST['search_query'] ) ? sanitize_text_field( wp_unslash( $_POST['search_query'] ) ) : '';
		$page_title        = isset( $_POST['page_title'] ) ? sanitize_text_field( wp_unslash( $_POST['page_title'] ) ) : '';
		$utm_source        = isset( $_POST['utm_source'] ) ? sanitize_text_field( wp_unslash( $_POST['utm_source'] ) ) : '';
		$client_user_agent = isset( $_POST['client_user_agent'] ) ? sanitize_text_field( wp_unslash( $_POST['client_user_agent'] ) ) : '';
		$request_type      = isset( $_POST['request_type'] ) ? sanitize_text_field( wp_unslash( $_POST['request_type'] ) ) : 'js_fallback';

		// Validate required fields.
		if ( empty( $platform ) || empty( $url ) ) {
			wp_send_json_error( array( 'message' => 'Missing required fields' ), 400 );
		}

		// Get post ID from URL.
		$post_id = url_to_postid( $url );

		// Find the session record created by server-side tracking (same IP + platform within 30 min).
		// UPDATE that record with client_user_agent to confirm it is a real browser visit.
		global $wpdb;
		$table_name = $wpdb->prefix . 'ta_bot_analytics';

		// Get client IP from the incoming AJAX request.
		$client_ip = '';
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$forwarded = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
			$ips       = explode( ',', $forwarded );
			$client_ip = trim( $ips[0] );
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$client_ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$recent_record = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, request_type FROM {$table_name}
				WHERE traffic_type = 'citation_click'
				AND ai_platform = %s
				AND ip_address = %s
				AND visit_timestamp >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
				AND client_user_agent IS NULL
				ORDER BY visit_timestamp DESC
				LIMIT 1",
				$platform,
				$client_ip
			)
		);

		if ( $recent_record ) {
			// Update existing record with client user agent.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$updated = $wpdb->update(
				$table_name,
				array(
					'client_user_agent' => $client_user_agent ?: null,
				),
				array( 'id' => $recent_record->id ),
				array( '%s' ),
				array( '%d' )
			);

			if ( false !== $updated ) {
				$this->logger->info( 'Updated citation record with client UA', array(
					'id'       => $recent_record->id,
					'platform' => $platform,
					'url'      => $path,
				) );

				wp_send_json_success( array(
					'message' => 'Updated existing record with client user agent',
					'id'      => $recent_record->id,
					'updated' => true,
				) );
			} else {
				$this->logger->error( 'Failed to update citation record', array(
					'id'    => $recent_record->id,
					'error' => $wpdb->last_error,
				) );
				wp_send_json_error( array( 'message' => 'Failed to update record' ), 500 );
			}
			return;
		}

		// No recent server-side record found.
		// This happens when page was served from cache (no server-side tracking).
		// Only create a new record for real HTML page visits.
		// Skip rsc_prefetch, api_call, js_fallback - server-side correctly filtered them out.
		if ( 'html_page' !== $request_type ) {
			wp_send_json_success( array( 'message' => 'Skipped: not a real HTML page visit', 'skipped' => true ) );
			return;
		}

		// Create a new record with JS data.

		// Prepare tracking data.
		$tracking_data = array(
			'bot_type'          => 'AI_Citation',
			'bot_name'          => $platform,
			'user_agent'        => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
			'client_user_agent' => $client_user_agent ?: null,
			'url'               => $path,
			'post_id'           => $post_id,
			'post_type'         => $post_id ? get_post_type( $post_id ) : null,
			'post_title'        => $post_id ? get_the_title( $post_id ) : $page_title,
			'request_method'    => 'citation_click_js',
			'request_type'      => $request_type,
			'cache_status'      => 'N/A',
			'referer'           => $referrer,
			'traffic_type'      => 'citation_click',
			'ai_platform'       => $platform,
			'search_query'      => $search_query ?: null,
			'referer_source'    => $utm_source ?: $platform,
			'referer_medium'    => 'ai_citation',
		);

		// Track the visit.
		$result = $this->tracker->track_visit( $tracking_data );

		if ( $result ) {
			$this->logger->info( 'JS Citation tracked', array(
				'platform' => $platform,
				'method'   => $method,
				'url'      => $url,
			) );
			wp_send_json_success( array( 'tracked' => true, 'id' => $result ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to track citation' ), 500 );
		}
	}

	/**
	 * Track bot crawl on any page visit.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	public function maybe_track_bot_crawl() {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}

		if ( did_action( 'ta_bot_visit_tracked' ) ) {
			return;
		}

		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] )
			? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) )
			: '';

		if ( empty( $user_agent ) ) {
			return;
		}

		$bot_info = $this->detector->detect_bot( $user_agent );

		if ( ! $bot_info ) {
			return;
		}

		$request_uri  = isset( $_SERVER['REQUEST_URI'] )
			? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) )
			: '';
		$is_markdown  = ( substr( $request_uri, -3 ) === '.md' );
		$content_type = $is_markdown ? 'markdown' : 'html';

		$post_id    = get_the_ID();
		$post_title = $post_id ? get_the_title( $post_id ) : '';

		$tracking_data = array(
			'bot_type'         => $bot_info['bot_type'] ?? $bot_info['type'],
			'bot_name'         => $bot_info['name'],
			'user_agent'       => $user_agent,
			'url'              => home_url( $request_uri ),
			'post_id'          => $post_id ?: null,
			'post_title'       => $post_title,
			'request_type'     => $this->detect_request_type(),
			'ip_address'       => $this->geolocation->get_bot_client_ip(),
			'cache_status'     => strtoupper( $content_type ),
			'response_time'    => 0,
			'traffic_type'     => 'bot_crawl',
			'content_type'     => $content_type,
			'detection_method' => $bot_info['detection_method'] ?? 'pattern',
			'confidence_score' => $bot_info['confidence'] ?? 1.0,
		);

		$result = $this->tracker->track_visit( $tracking_data );

		if ( $result ) {
			do_action( 'ta_bot_visit_tracked' );
			$this->logger->debug( 'Bot crawl tracked.', array(
				'bot'          => $bot_info['name'],
				'url'          => $request_uri,
				'content_type' => $content_type,
			) );
		}
	}

	// =========================================================================
	// DELEGATED METHODS - BOT DETECTION (TA_Bot_Detector)
	// =========================================================================

	/**
	 * Detect bot from user agent.
	 *
	 * @since 1.4.0
	 * @param string $user_agent The user agent string.
	 * @return array|false Bot information or false.
	 */
	public function detect_bot( $user_agent ) {
		return $this->detector->detect_bot( $user_agent );
	}

	/**
	 * Get bot detection result object.
	 *
	 * @since 2.3.0
	 * @param string $user_agent The user agent string.
	 * @return TA_Bot_Detection_Result|null Detection result or null.
	 */
	public function get_bot_detection_result( $user_agent ) {
		return $this->detector->get_bot_detection_result( $user_agent );
	}

	/**
	 * Check if a bot type is blocked.
	 *
	 * @since 1.5.0
	 * @param string $bot_type The bot type.
	 * @return bool True if blocked.
	 */
	public function is_bot_blocked( $bot_type ) {
		return $this->detector->is_bot_blocked( $bot_type );
	}

	/**
	 * Get bot priority.
	 *
	 * @since 2.1.0
	 * @param string $bot_type         Bot type.
	 * @param string $default_priority Default priority.
	 * @return string Priority level.
	 */
	public function get_bot_priority( $bot_type, $default_priority = 'medium' ) {
		return $this->detector->get_bot_priority( $bot_type, $default_priority );
	}

	/**
	 * Get cache TTL for priority.
	 *
	 * @since 2.1.0
	 * @param string $priority Priority level.
	 * @return int Cache TTL in seconds.
	 */
	public static function get_cache_ttl_for_priority( $priority ) {
		return TA_Bot_Detector::get_cache_ttl_for_priority( $priority );
	}

	/**
	 * Get known bots.
	 *
	 * @since 1.4.0
	 * @return array Known bots configuration.
	 */
	public static function get_known_bots() {
		return TA_Bot_Detector::get_known_bots();
	}

	// =========================================================================
	// DELEGATED METHODS - VISIT TRACKING (TA_Visit_Tracker)
	// =========================================================================

	/**
	 * Track a bot visit.
	 *
	 * @since 1.4.0
	 * @param array $data Visit data.
	 * @return int|false Insert ID or false.
	 */
	public function track_visit( $data ) {
		return $this->tracker->track_visit( $data );
	}

	/**
	 * Track AI citation click.
	 *
	 * @since 2.2.0
	 * @return int|false Insert ID or false.
	 */
	public function track_citation_click() {
		return $this->tracker->track_citation_click();
	}

	// =========================================================================
	// DELEGATED METHODS - ANALYTICS QUERIES (TA_Analytics_Query)
	// =========================================================================

	/**
	 * Get analytics summary.
	 *
	 * @since 1.4.0
	 * @param array $filters Optional filters.
	 * @return array Summary statistics.
	 */
	public function get_summary( $filters = array() ) {
		return $this->query->get_summary( $filters );
	}

	/**
	 * Get visits by bot type.
	 *
	 * @since 1.4.0
	 * @param array $filters Optional filters.
	 * @return array Bot type breakdown.
	 */
	public function get_visits_by_bot( $filters = array() ) {
		return $this->query->get_visits_by_bot( $filters );
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
		return $this->query->get_top_pages( $filters, $limit );
	}

	/**
	 * Get citation-to-crawl ratio.
	 *
	 * @since 2.7.0
	 * @param array $filters Optional filters.
	 * @param int   $limit   Number of results.
	 * @return array Citation rate data.
	 */
	public function get_citation_to_crawl_ratio( $filters = array(), $limit = 20 ) {
		return $this->query->get_citation_to_crawl_ratio( $filters, $limit );
	}

	/**
	 * Get visits over time.
	 *
	 * @since 1.4.0
	 * @param array  $filters Optional filters.
	 * @param string $period  Period type.
	 * @param int    $limit   Number of periods.
	 * @return array Time series data.
	 */
	public function get_visits_over_time( $filters = array(), $period = 'day', $limit = 30 ) {
		return $this->query->get_visits_over_time( $filters, $period, $limit );
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
		return $this->query->get_recent_visits( $filters, $limit, $offset );
	}

	/**
	 * Get session analytics.
	 *
	 * @since 2.6.0
	 * @return array Session analytics summary.
	 */
	public function get_session_analytics() {
		return $this->query->get_session_analytics();
	}

	/**
	 * Get bot fingerprints list.
	 *
	 * @since 3.2.2
	 * @param string $sort_by Column to sort by.
	 * @param string $order   Sort order.
	 * @param int    $limit   Number of results.
	 * @return array Bot fingerprints.
	 */
	public function get_bot_fingerprints_list( $sort_by = 'last_seen', $order = 'DESC', $limit = 50 ) {
		return $this->query->get_bot_fingerprints_list( $sort_by, $order, $limit );
	}

	/**
	 * Get top bots by metric.
	 *
	 * @since 2.6.0
	 * @param string $metric Metric to sort by.
	 * @param int    $limit  Number of results.
	 * @return array Top bots.
	 */
	public function get_top_bots_by_metric( $metric = 'pages_per_session', $limit = 10 ) {
		return $this->query->get_top_bots_by_metric( $metric, $limit );
	}

	/**
	 * Get crawl budget metrics.
	 *
	 * @since 2.7.0
	 * @param string|null $bot_type Bot type filter.
	 * @param string      $period   Time period.
	 * @return array Crawl budget metrics.
	 */
	public function get_crawl_budget_metrics( $bot_type = null, $period = 'day' ) {
		return $this->query->get_crawl_budget_metrics( $bot_type, $period );
	}

	/**
	 * Get content performance analysis.
	 *
	 * @since 2.7.0
	 * @param array $filters Optional filters.
	 * @return array Content performance data.
	 */
	public function get_content_performance_analysis( $filters = array() ) {
		return $this->query->get_content_performance_analysis( $filters );
	}

	/**
	 * Get comprehensive bot details.
	 *
	 * @since 3.3.0
	 * @param string $bot_type Bot type identifier.
	 * @param string $bot_name Bot display name.
	 * @return array Comprehensive bot details.
	 */
	public function get_bot_details( $bot_type, $bot_name ) {
		return $this->query->get_bot_details( $bot_type, $bot_name );
	}

	// =========================================================================
	// DELEGATED METHODS - DATA EXPORT (TA_Data_Exporter)
	// =========================================================================

	/**
	 * Export data to CSV.
	 *
	 * @since 1.4.0
	 * @param array  $filters Optional filters.
	 * @param string $format  Export format.
	 * @return void
	 */
	public function export_to_csv( $filters = array(), $format = 'detailed' ) {
		$this->exporter->export_to_csv( $filters, $format );
	}

	/**
	 * Export data to JSON.
	 *
	 * @since 2.0.6
	 * @param array  $filters Optional filters.
	 * @param string $format  Export format.
	 * @return void
	 */
	public function export_to_json( $filters = array(), $format = 'detailed' ) {
		$this->exporter->export_to_json( $filters, $format );
	}

	// =========================================================================
	// DELEGATED METHODS - PERFORMANCE STATS (TA_Performance_Stats)
	// =========================================================================

	/**
	 * Get optimal content length.
	 *
	 * @since 2.7.0
	 * @return array Optimal content length data.
	 */
	public function get_optimal_content_length() {
		return $this->performance->get_optimal_content_length();
	}

	/**
	 * Get cache performance stats.
	 *
	 * @since 3.2.3
	 * @return array Cache statistics.
	 */
	public function get_cache_performance_stats() {
		return $this->performance->get_cache_performance_stats();
	}

	/**
	 * Get response time distribution.
	 *
	 * @since 3.2.3
	 * @return array Response time ranges.
	 */
	public function get_response_time_distribution() {
		return $this->performance->get_response_time_distribution();
	}

	/**
	 * Get response time percentile.
	 *
	 * @since 3.2.3
	 * @param int $percentile Percentile to calculate.
	 * @return int Response time in ms.
	 */
	public function get_response_time_percentile( $percentile = 50 ) {
		return $this->performance->get_response_time_percentile( $percentile );
	}

	// =========================================================================
	// DATA MAINTENANCE
	// =========================================================================

	/**
	 * Delete old records.
	 *
	 * @since 1.4.0
	 * @param int $days Number of days to keep.
	 * @return int Number of deleted records.
	 */
	public function cleanup_old_records( $days = 90 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$cutoff_date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table_name} WHERE visit_timestamp < %s",
				$cutoff_date
			)
		);

		$this->logger->info( 'Cleaned up old analytics records.', array(
			'deleted' => $deleted,
			'days'    => $days,
		) );

		return (int) $deleted;
	}

	/**
	 * Clear all visits.
	 *
	 * @since 1.4.0
	 * @return bool True on success.
	 */
	public function clear_all_visits() {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$result = $wpdb->query( "TRUNCATE TABLE {$table_name}" );

		$this->logger->info( 'All bot analytics data cleared.' );

		return false !== $result;
	}

	// =========================================================================
	// DATABASE SCHEMA
	// =========================================================================

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
			PRIMARY KEY  (id),
			KEY bot_type (bot_type),
			KEY post_id (post_id),
			KEY visit_timestamp (visit_timestamp),
			KEY bot_type_timestamp (bot_type, visit_timestamp),
			KEY traffic_type (traffic_type),
			KEY ai_platform (ai_platform),
			KEY detection_method (detection_method),
			KEY ip_verified (ip_verified),
			KEY content_type (content_type),
			KEY request_type (request_type),
			KEY http_status (http_status)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Create supporting tables.
		$this->create_bot_detection_tables( $charset_collate );

		update_option( self::DB_VERSION_OPTION, self::DB_VERSION );

		$this->logger->info( 'Bot analytics table created/updated.', array(
			'version' => self::DB_VERSION,
		) );
	}

	/**
	 * Create bot detection tables.
	 *
	 * @since 1.2.0
	 * @param string $charset_collate Database charset collation.
	 * @return void
	 */
	private function create_bot_detection_tables( $charset_collate ) {
		global $wpdb;

		// Bot Patterns table.
		$patterns_table = $wpdb->prefix . 'ta_bot_patterns';
		$sql_patterns   = "CREATE TABLE IF NOT EXISTS {$patterns_table} (
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

		// Unknown Bots table.
		$unknown_bots_table = $wpdb->prefix . 'ta_unknown_bots';
		$sql_unknown        = "CREATE TABLE IF NOT EXISTS {$unknown_bots_table} (
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

		// Bot DB Sync table.
		$sync_table = $wpdb->prefix . 'ta_bot_db_sync';
		$sql_sync   = "CREATE TABLE IF NOT EXISTS {$sync_table} (
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

		// Bot Fingerprints table.
		$fingerprints_table = $wpdb->prefix . 'ta_bot_fingerprints';
		$sql_fingerprints   = "CREATE TABLE IF NOT EXISTS {$fingerprints_table} (
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

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_patterns );
		dbDelta( $sql_unknown );
		dbDelta( $sql_sync );
		dbDelta( $sql_fingerprints );

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
	 * Uninstall - drop all tables.
	 *
	 * @since 1.4.0
	 * @return void
	 */
	public static function uninstall() {
		global $wpdb;

		$tables = array(
			$wpdb->prefix . 'ta_bot_analytics',
			$wpdb->prefix . 'ta_bot_patterns',
			$wpdb->prefix . 'ta_unknown_bots',
			$wpdb->prefix . 'ta_bot_db_sync',
			$wpdb->prefix . 'ta_bot_fingerprints',
		);

		foreach ( $tables as $table ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
		}

		delete_option( self::DB_VERSION_OPTION );
		delete_option( 'ta_bot_patterns_migrated' );
	}
}
