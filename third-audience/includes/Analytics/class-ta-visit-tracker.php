<?php
/**
 * Visit Tracker - Records and tracks bot visits.
 *
 * Handles visit recording, session tracking, and citation detection.
 *
 * @package ThirdAudience
 * @since   3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Visit_Tracker
 *
 * Records bot visits and manages session tracking.
 *
 * @since 3.3.1
 */
class TA_Visit_Tracker {

	/**
	 * Analytics table name.
	 *
	 * @var string
	 */
	const TABLE_NAME = 'ta_bot_analytics';

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
	 * Geolocation instance.
	 *
	 * @var TA_Geolocation
	 */
	private $geolocation;

	/**
	 * Bot detector instance.
	 *
	 * @var TA_Bot_Detector
	 */
	private $detector;

	/**
	 * IP Verifier instance.
	 *
	 * @var TA_IP_Verifier|null
	 */
	private $ip_verifier = null;

	/**
	 * Singleton instance.
	 *
	 * @var TA_Visit_Tracker|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 3.3.1
	 * @return TA_Visit_Tracker
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
		$this->logger      = TA_Logger::get_instance();
		$this->webhooks    = TA_Webhooks::get_instance();
		$this->geolocation = TA_Geolocation::get_instance();
		$this->detector    = TA_Bot_Detector::get_instance();

		// Initialize IP verifier.
		if ( class_exists( 'TA_IP_Verifier' ) ) {
			$this->ip_verifier = TA_IP_Verifier::get_instance();
		}
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
		$ip_address   = $this->geolocation->get_client_ip();
		$country_code = $ip_address ? $this->geolocation->get_geolocation( $ip_address ) : null;

		// Verify bot IP (if verifier is available and IP exists).
		$ip_verification = array( 'verified' => null, 'method' => null );
		if ( null !== $this->ip_verifier && $ip_address ) {
			$ip_verification = $this->ip_verifier->verify_bot_ip( $data['bot_type'], $ip_address );
		}

		$insert_data = array(
			'bot_type'               => sanitize_text_field( $data['bot_type'] ),
			'bot_name'               => sanitize_text_field( $data['bot_name'] ?? '' ),
			'user_agent'             => sanitize_text_field( $data['user_agent'] ?? '' ),
			'client_user_agent'      => isset( $data['client_user_agent'] ) ? sanitize_text_field( $data['client_user_agent'] ) : null,
			'url'                    => esc_url_raw( $data['url'] ),
			'post_id'                => isset( $data['post_id'] ) ? absint( $data['post_id'] ) : null,
			'post_type'              => isset( $data['post_type'] ) ? sanitize_text_field( $data['post_type'] ) : null,
			'post_title'             => isset( $data['post_title'] ) ? sanitize_text_field( $data['post_title'] ) : null,
			'request_method'         => sanitize_text_field( $data['request_method'] ?? 'md_url' ),
			'request_type'           => sanitize_text_field( $data['request_type'] ?? 'unknown' ),
			'cache_status'           => sanitize_text_field( $data['cache_status'] ?? 'MISS' ),
			'response_time'          => isset( $data['response_time'] ) ? absint( $data['response_time'] ) : null,
			'response_size'          => isset( $data['response_size'] ) ? absint( $data['response_size'] ) : null,
			'http_status'            => isset( $data['http_status'] ) ? absint( $data['http_status'] ) : $this->get_http_status(),
			'ip_address'             => $ip_address,
			'referer'                => isset( $data['referer'] ) ? esc_url_raw( $data['referer'] ) : null,
			'country_code'           => $country_code,
			'traffic_type'           => sanitize_text_field( $data['traffic_type'] ?? 'bot_crawl' ),
			'content_type'           => sanitize_text_field( $data['content_type'] ?? 'html' ),
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
			'%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s',
			'%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s',
			'%s', '%s', '%s', '%s', '%f', '%d', '%s', '%s',
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

				$format[] = '%d';
				$format[] = '%d';
				$format[] = '%d';
				$format[] = '%d';
				$format[] = '%d';
			}
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$result = $wpdb->insert( $table_name, $insert_data, $format );

		if ( false === $result ) {
			$this->logger->error( 'Failed to track bot visit.', array(
				'error' => $wpdb->last_error,
				'data'  => $insert_data,
			) );
			return false;
		}

		$insert_id = $wpdb->insert_id;

		$this->logger->debug( 'Bot visit tracked.', array(
			'id'       => $insert_id,
			'bot_type' => $data['bot_type'],
			'url'      => $data['url'],
		) );

		// Check if this is a new bot.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$visit_count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table_name} WHERE bot_type = %s AND id != %d",
			$data['bot_type'],
			$insert_id
		) );

		// If first visit from this bot type, fire webhook.
		if ( 0 === absint( $visit_count ) ) {
			$bot_info = $this->detector->detect_bot( $data['user_agent'] ?? '' );
			if ( false !== $bot_info ) {
				$this->webhooks->fire_bot_detected( $bot_info );
			}
		}

		// Update session tracking.
		$this->update_session_tracking( $insert_data );

		// Check for citation alerts.
		if ( 'citation_click' === $insert_data['traffic_type'] && class_exists( 'TA_Citation_Alerts' ) ) {
			$citation_alerts = TA_Citation_Alerts::get_instance();

			if ( ! empty( $insert_data['ai_platform'] ) ) {
				$citation_alerts->check_first_citation( $insert_data['ai_platform'] );
				$citation_alerts->check_new_platform( $insert_data['ai_platform'] );
			}
		}

		// Send event to GA4.
		if ( class_exists( 'TA_GA4_Integration' ) ) {
			$ga4 = TA_GA4_Integration::get_instance();
			if ( $ga4->is_enabled() ) {
				if ( 'citation_click' === $insert_data['traffic_type'] ) {
					$ga4_result = $ga4->send_citation_click_event( $insert_data );
				} else {
					$ga4_result = $ga4->send_bot_crawl_event( $insert_data );
				}

				if ( ! is_wp_error( $ga4_result ) ) {
					$ga4->update_sync_stats( true );
				} else {
					$ga4->update_sync_stats( false );
				}
			}
		}

		return $insert_id;
	}

	/**
	 * Get current HTTP response status code.
	 *
	 * @since 3.5.0
	 * @return int|null HTTP status code or null if not available.
	 */
	private function get_http_status() {
		$status = http_response_code();
		return $status ? $status : null;
	}

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
	 * Track AI citation click from referrer.
	 *
	 * @since 2.2.0
	 * @return int|false Insert ID or false if not citation traffic.
	 */
	public function track_citation_click() {
		// Detect AI citation traffic from referrer.
		if ( ! class_exists( 'TA_AI_Citation_Tracker' ) ) {
			return false;
		}

		$citation_data = TA_AI_Citation_Tracker::detect_citation_traffic();

		if ( false === $citation_data ) {
			return false;
		}

		// Session deduplication: same IP + same platform within 30 minutes = same session.
		// Matches GA4 session behaviour — only the landing page of each AI session is counted.
		global $wpdb;
		$table_name  = $wpdb->prefix . self::TABLE_NAME;
		$platform    = $citation_data['platform'];
		$ip_address  = $this->geolocation->get_client_ip();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$recent_duplicate = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table_name}
				WHERE traffic_type = 'citation_click'
				AND ai_platform = %s
				AND ip_address = %s
				AND visit_timestamp >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
				LIMIT 1",
				$platform,
				$ip_address
			)
		);

		if ( $recent_duplicate ) {
			$this->logger->debug( 'Duplicate citation detected, skipping.', array(
				'platform' => $platform,
				'url' => $current_url,
				'existing_id' => $recent_duplicate,
			) );
			return false;
		}

		// Get current page info.
		$post_id    = get_queried_object_id();
		$post       = $post_id ? get_post( $post_id ) : null;
		$post_type  = $post ? $post->post_type : null;
		$post_title = $post ? $post->post_title : null;

		// Detect request type.
		$request_type = $this->detect_request_type();

		// Only track real HTML page visits.
		// Skip rsc_prefetch (Next.js background fetch), api_call, js_fallback, and any other non-page requests.
		if ( 'html_page' !== $request_type ) {
			return false;
		}

		// Only track real WordPress content pages: posts, pages, homepage, blog listing, archives.
		// This filters out feeds (/feed/), sitemaps, wp-login, wp-cron, trackbacks etc.
		if ( ! is_singular() && ! is_front_page() && ! is_home() && ! is_archive() ) {
			return false;
		}

		// Capture HTTP status code.
		$http_status = $this->get_http_status();

		// Prepare tracking data.
		$tracking_data = array(
			'bot_type'       => 'AI_Citation',
			'bot_name'       => $citation_data['platform'],
			'user_agent'     => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
			'url'            => esc_url_raw( $_SERVER['REQUEST_URI'] ?? '/' ),
			'post_id'        => $post_id,
			'post_type'      => $post_type,
			'post_title'     => $post_title,
			'request_method' => 'citation_click',
			'request_type'   => $request_type,
			'http_status'    => $http_status,
			'cache_status'   => 'N/A',
			'referer'        => $citation_data['referer'],
			'traffic_type'   => 'citation_click',
			'ai_platform'    => $citation_data['platform'],
			'search_query'   => $citation_data['search_query'] ?? null,
			'referer_source' => $citation_data['source'] ?? null,
			'referer_medium' => $citation_data['medium'] ?? null,
		);

		return $this->track_visit( $tracking_data );
	}

	/**
	 * Update session tracking for bot fingerprinting.
	 *
	 * @since 2.6.0
	 * @param array $visit_data Current visit data.
	 * @return void
	 */
	private function update_session_tracking( $visit_data ) {
		global $wpdb;

		if ( empty( $visit_data['ip_address'] ) || empty( $visit_data['user_agent'] ) ) {
			return;
		}

		$fingerprint        = md5( $visit_data['user_agent'] . '|' . $visit_data['ip_address'] );
		$fingerprints_table = $wpdb->prefix . 'ta_bot_fingerprints';
		$analytics_table    = $wpdb->prefix . self::TABLE_NAME;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$fingerprint_record = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$fingerprints_table} WHERE fingerprint_hash = %s",
				$fingerprint
			)
		);

		$now = current_time( 'mysql' );

		if ( ! $fingerprint_record ) {
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
			return;
		}

		$session_window = 1800;

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

		$visit_count = absint( $fingerprint_record->visit_count ) + 1;

		// Calculate request intervals.
		$intervals = array();
		for ( $i = 0; $i < count( $recent_visits ) - 1; $i++ ) {
			$time1       = strtotime( $recent_visits[ $i ]->visit_timestamp );
			$time2       = strtotime( $recent_visits[ $i + 1 ]->visit_timestamp );
			$intervals[] = abs( $time1 - $time2 );
		}

		$request_interval_avg    = ! empty( $intervals ) ? array_sum( $intervals ) / count( $intervals ) : null;
		$request_interval_stddev = ! empty( $intervals ) ? $this->calculate_stddev( $intervals ) : null;

		// Calculate session metrics.
		$sessions          = $this->group_visits_into_sessions( $recent_visits, $session_window );
		$pages_per_session = ! empty( $sessions ) ? array_sum( array_column( $sessions, 'page_count' ) ) / count( $sessions ) : null;
		$session_durations = array_column( $sessions, 'duration' );
		$session_duration  = ! empty( $session_durations ) ? array_sum( $session_durations ) / count( $session_durations ) : null;

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

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->update(
			$fingerprints_table,
			array(
				'last_seen'               => $now,
				'visit_count'             => $visit_count,
				'request_interval_avg'    => $request_interval_avg,
				'request_interval_stddev' => $request_interval_stddev,
				'pages_per_session_avg'   => $pages_per_session,
				'session_duration_avg'    => $session_duration,
				'unique_paths_ratio'      => $unique_paths_ratio,
				'classification'          => $visit_data['bot_type'],
			),
			array( 'fingerprint_hash' => $fingerprint ),
			array( '%s', '%d', '%d', '%d', '%f', '%d', '%f', '%s' ),
			array( '%s' )
		);
	}

	/**
	 * Group visits into sessions.
	 *
	 * @since 2.6.0
	 * @param array $visits         Visit records.
	 * @param int   $session_window Session timeout in seconds.
	 * @return array Sessions with page_count and duration.
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

		foreach ( $visits as $visit ) {
			$timestamp = strtotime( $visit->visit_timestamp );

			if ( null === $current_session['start'] ) {
				$current_session['start']      = $timestamp;
				$current_session['end']        = $timestamp;
				$current_session['page_count'] = 1;
			} else {
				$time_since_last = abs( $current_session['end'] - $timestamp );

				if ( $time_since_last <= $session_window ) {
					$current_session['end'] = $timestamp;
					$current_session['page_count']++;
				} else {
					$current_session['duration'] = abs( $current_session['end'] - $current_session['start'] );
					$sessions[]                  = $current_session;

					$current_session = array(
						'start'      => $timestamp,
						'end'        => $timestamp,
						'page_count' => 1,
						'duration'   => 0,
					);
				}
			}
		}

		if ( null !== $current_session['start'] ) {
			$current_session['duration'] = abs( $current_session['end'] - $current_session['start'] );
			$sessions[]                  = $current_session;
		}

		return $sessions;
	}

	/**
	 * Calculate standard deviation.
	 *
	 * @since 2.6.0
	 * @param array $values Numeric values.
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
}
