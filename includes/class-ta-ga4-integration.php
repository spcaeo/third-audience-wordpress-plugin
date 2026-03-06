<?php
/**
 * GA4 Integration - Syncs AI bot traffic data to Google Analytics 4.
 *
 * Sends bot visit data to GA4 using the Measurement Protocol API.
 * Tracks bot crawls, citation clicks, and session data.
 *
 * @package ThirdAudience
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_GA4_Integration
 *
 * Handles GA4 Measurement Protocol integration for AI bot traffic.
 *
 * @since 3.0.0
 */
class TA_GA4_Integration {

	/**
	 * GA4 Measurement Protocol API endpoint.
	 *
	 * @var string
	 */
	const API_ENDPOINT = 'https://www.google-analytics.com/mp/collect';

	/**
	 * Validation endpoint for testing connection.
	 *
	 * @var string
	 */
	const VALIDATION_ENDPOINT = 'https://www.google-analytics.com/debug/mp/collect';

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Security instance.
	 *
	 * @var TA_Security
	 */
	private $security;

	/**
	 * Singleton instance.
	 *
	 * @var TA_GA4_Integration|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 3.0.0
	 * @return TA_GA4_Integration
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
	 * @since 3.0.0
	 */
	private function __construct() {
		$this->logger   = TA_Logger::get_instance();
		$this->security = TA_Security::get_instance();
	}

	/**
	 * Check if GA4 integration is enabled and configured.
	 *
	 * @since 3.0.0
	 * @return bool True if enabled and configured.
	 */
	public function is_enabled() {
		$enabled        = get_option( 'ta_ga4_enabled', false );
		$measurement_id = get_option( 'ta_ga4_measurement_id', '' );
		$api_secret     = get_option( 'ta_ga4_api_secret', '' );

		return $enabled && ! empty( $measurement_id ) && ! empty( $api_secret );
	}

	/**
	 * Send bot crawl event to GA4.
	 *
	 * @since 3.0.0
	 * @param array $visit_data Visit data from bot analytics.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function send_bot_crawl_event( $visit_data ) {
		if ( ! $this->is_enabled() ) {
			return new WP_Error( 'ga4_disabled', 'GA4 integration is not enabled.' );
		}

		$event_params = array(
			'bot_name'          => $visit_data['bot_name'] ?? 'Unknown',
			'bot_type'          => $visit_data['bot_type'] ?? 'unknown',
			'url'               => $visit_data['url'] ?? '',
			'post_title'        => $visit_data['post_title'] ?? '',
			'post_type'         => $visit_data['post_type'] ?? '',
			'cache_status'      => $visit_data['cache_status'] ?? 'miss',
			'response_time'     => intval( $visit_data['response_time'] ?? 0 ),
			'detection_method'  => $visit_data['detection_method'] ?? '',
			'confidence_score'  => floatval( $visit_data['confidence_score'] ?? 0 ),
			'content_type'      => 'markdown',
		);

		// Add AI score if available.
		if ( ! empty( $visit_data['post_id'] ) ) {
			$ai_score = get_post_meta( $visit_data['post_id'], '_ta_ai_score', true );
			if ( $ai_score ) {
				$event_params['ai_score'] = floatval( $ai_score );
			}
		}

		return $this->send_event( 'bot_crawl', $event_params, $visit_data );
	}

	/**
	 * Send citation click event to GA4.
	 *
	 * @since 3.0.0
	 * @param array $citation_data Citation data from AI citation tracker.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function send_citation_click_event( $citation_data ) {
		if ( ! $this->is_enabled() ) {
			return new WP_Error( 'ga4_disabled', 'GA4 integration is not enabled.' );
		}

		$event_params = array(
			'platform'          => $citation_data['ai_platform'] ?? 'unknown',
			'search_query'      => $citation_data['search_query'] ?? '',
			'url'               => $citation_data['url'] ?? '',
			'post_title'        => $citation_data['post_title'] ?? '',
			'referer_source'    => $citation_data['referer_source'] ?? '',
			'referer_medium'    => $citation_data['referer_medium'] ?? '',
			'bot_name'          => $citation_data['bot_name'] ?? '',
		);

		// Add citation rate if available.
		if ( ! empty( $citation_data['post_id'] ) ) {
			$citation_rate = $this->calculate_citation_rate( $citation_data['post_id'] );
			if ( $citation_rate ) {
				$event_params['citation_rate'] = floatval( $citation_rate );
			}
		}

		return $this->send_event( 'ai_citation_click', $event_params, $citation_data );
	}

	/**
	 * Send session event to GA4.
	 *
	 * @since 3.0.0
	 * @param array $session_data Session data.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function send_session_event( $session_data ) {
		if ( ! $this->is_enabled() ) {
			return new WP_Error( 'ga4_disabled', 'GA4 integration is not enabled.' );
		}

		$event_params = array(
			'bot_name'           => $session_data['bot_name'] ?? 'Unknown',
			'pages_per_session'  => intval( $session_data['pages_per_session'] ?? 1 ),
			'session_duration'   => intval( $session_data['duration'] ?? 0 ),
			'total_crawl_budget' => intval( $session_data['crawl_budget'] ?? 0 ),
		);

		return $this->send_event( 'bot_session', $event_params, $session_data );
	}

	/**
	 * Send event to GA4 Measurement Protocol.
	 *
	 * @since 3.0.0
	 * @param string $event_name Event name.
	 * @param array  $event_params Event parameters.
	 * @param array  $visit_data Original visit data for client_id generation.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	private function send_event( $event_name, $event_params, $visit_data ) {
		$measurement_id = get_option( 'ta_ga4_measurement_id', '' );
		$api_secret     = get_option( 'ta_ga4_api_secret', '' );

		if ( empty( $measurement_id ) || empty( $api_secret ) ) {
			return new WP_Error( 'ga4_config_missing', 'GA4 Measurement ID or API Secret is missing.' );
		}

		// Generate client_id from IP address or use a consistent identifier.
		$client_id = $this->generate_client_id( $visit_data );

		// Build the payload.
		$payload = array(
			'client_id' => $client_id,
			'events'    => array(
				array(
					'name'   => $event_name,
					'params' => array_merge(
						$event_params,
						array(
							'engagement_time_msec' => 100, // Required for GA4.
							'session_id'           => time(),
						)
					),
				),
			),
		);

		// Build API URL with query parameters.
		$url = add_query_arg(
			array(
				'measurement_id' => $measurement_id,
				'api_secret'     => $api_secret,
			),
			self::API_ENDPOINT
		);

		// Send async request to avoid blocking.
		$args = array(
			'method'      => 'POST',
			'timeout'     => 5,
			'blocking'    => false, // Non-blocking for performance.
			'headers'     => array(
				'Content-Type' => 'application/json',
			),
			'body'        => wp_json_encode( $payload ),
			'data_format' => 'body',
		);

		$response = wp_remote_post( $url, $args );

		// Log errors (only if blocking mode used during testing).
		if ( is_wp_error( $response ) ) {
			$this->logger->error( 'GA4 event send failed.', array(
				'event'   => $event_name,
				'error'   => $response->get_error_message(),
			) );
			return $response;
		}

		return true;
	}

	/**
	 * Test GA4 connection.
	 *
	 * Sends a test event to the validation endpoint to check configuration.
	 *
	 * @since 3.0.0
	 * @param string $measurement_id GA4 Measurement ID.
	 * @param string $api_secret GA4 API Secret.
	 * @return array|WP_Error Array with validation results or WP_Error.
	 */
	public function test_connection( $measurement_id, $api_secret ) {
		if ( empty( $measurement_id ) || empty( $api_secret ) ) {
			return new WP_Error( 'ga4_config_missing', 'Measurement ID and API Secret are required.' );
		}

		// Build test payload.
		$payload = array(
			'client_id' => 'test_client_' . time(),
			'events'    => array(
				array(
					'name'   => 'test_connection',
					'params' => array(
						'test_param'           => 'test_value',
						'engagement_time_msec' => 100,
					),
				),
			),
		);

		// Build validation URL.
		$url = add_query_arg(
			array(
				'measurement_id' => $measurement_id,
				'api_secret'     => $api_secret,
			),
			self::VALIDATION_ENDPOINT
		);

		// Send blocking request for validation.
		$args = array(
			'method'      => 'POST',
			'timeout'     => 10,
			'blocking'    => true, // Blocking for validation response.
			'headers'     => array(
				'Content-Type' => 'application/json',
			),
			'body'        => wp_json_encode( $payload ),
			'data_format' => 'body',
		);

		$response = wp_remote_post( $url, $args );

		if ( is_wp_error( $response ) ) {
			$this->logger->error( 'GA4 connection test failed.', array(
				'error' => $response->get_error_message(),
			) );
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		if ( 200 !== $response_code ) {
			return new WP_Error(
				'ga4_connection_failed',
				sprintf( 'GA4 API returned error code: %d', $response_code )
			);
		}

		// Parse validation response.
		$validation_result = json_decode( $response_body, true );

		// Check for validation messages (errors/warnings from GA4).
		if ( ! empty( $validation_result['validationMessages'] ) ) {
			$errors = array();
			foreach ( $validation_result['validationMessages'] as $message ) {
				$errors[] = $message['description'] ?? 'Unknown validation error';
			}
			return new WP_Error( 'ga4_validation_failed', implode( '; ', $errors ) );
		}

		return array(
			'success' => true,
			'message' => 'GA4 connection test successful!',
		);
	}

	/**
	 * Generate consistent client_id for bot visits.
	 *
	 * @since 3.0.0
	 * @param array $visit_data Visit data.
	 * @return string Client ID.
	 */
	private function generate_client_id( $visit_data ) {
		// Use IP address + bot name as basis for client_id.
		$ip_address = $visit_data['ip_address'] ?? '';
		$bot_name   = $visit_data['bot_name'] ?? 'unknown';

		// Create hash for anonymization.
		$hash = hash( 'sha256', $ip_address . $bot_name . AUTH_SALT );

		// GA4 client_id format: numeric or UUID-like string.
		return substr( $hash, 0, 32 );
	}

	/**
	 * Calculate citation rate for a post.
	 *
	 * @since 3.0.0
	 * @param int $post_id Post ID.
	 * @return float|null Citation rate or null.
	 */
	private function calculate_citation_rate( $post_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ta_bot_analytics';

		// Get total bot visits.
		$total_visits = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE post_id = %d AND traffic_type = 'bot_crawl'",
				$post_id
			)
		);

		if ( ! $total_visits ) {
			return null;
		}

		// Get citation clicks.
		$citation_clicks = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE post_id = %d AND traffic_type = 'citation_click'",
				$post_id
			)
		);

		// Calculate rate as percentage.
		return ( $citation_clicks / $total_visits ) * 100;
	}

	/**
	 * Get GA4 settings.
	 *
	 * @since 3.0.0
	 * @return array GA4 settings.
	 */
	public function get_settings() {
		return array(
			'enabled'        => (bool) get_option( 'ta_ga4_enabled', false ),
			'measurement_id' => get_option( 'ta_ga4_measurement_id', '' ),
			'api_secret'     => get_option( 'ta_ga4_api_secret', '' ),
		);
	}

	/**
	 * Save GA4 settings.
	 *
	 * @since 3.0.0
	 * @param array $settings Settings to save.
	 * @return bool True on success.
	 */
	public function save_settings( $settings ) {
		$sanitized = array(
			'enabled'        => isset( $settings['enabled'] ),
			'measurement_id' => $this->security->sanitize_text( $settings['measurement_id'] ?? '' ),
			'api_secret'     => $this->security->sanitize_text( $settings['api_secret'] ?? '' ),
		);

		update_option( 'ta_ga4_enabled', $sanitized['enabled'] );
		update_option( 'ta_ga4_measurement_id', $sanitized['measurement_id'] );
		update_option( 'ta_ga4_api_secret', $sanitized['api_secret'] );

		$this->logger->info( 'GA4 settings updated.', array(
			'enabled' => $sanitized['enabled'],
		) );

		return true;
	}

	/**
	 * Get recent GA4 sync status.
	 *
	 * @since 3.0.0
	 * @return array Sync statistics.
	 */
	public function get_sync_stats() {
		$stats = get_transient( 'ta_ga4_sync_stats' );

		if ( false === $stats ) {
			$stats = array(
				'total_events_sent'   => 0,
				'last_sync_time'      => null,
				'success_count'       => 0,
				'error_count'         => 0,
			);
		}

		return $stats;
	}

	/**
	 * Update sync statistics.
	 *
	 * @since 3.0.0
	 * @param bool $success Whether the sync was successful.
	 * @return void
	 */
	public function update_sync_stats( $success ) {
		$stats = $this->get_sync_stats();

		$stats['total_events_sent']++;
		$stats['last_sync_time'] = current_time( 'mysql' );

		if ( $success ) {
			$stats['success_count']++;
		} else {
			$stats['error_count']++;
		}

		// Store for 24 hours.
		set_transient( 'ta_ga4_sync_stats', $stats, DAY_IN_SECONDS );
	}
}
