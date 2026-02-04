<?php
/**
 * AJAX Fallback System - Alternative to REST API when blocked.
 *
 * Provides admin-ajax.php based endpoints that bypass REST API restrictions
 * imposed by security plugins or server firewalls.
 *
 * @package ThirdAudience
 * @since   3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_AJAX_Fallback
 *
 * Provides AJAX-based endpoints as fallback when REST API is blocked.
 *
 * @since 3.4.0
 */
class TA_AJAX_Fallback {

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
	 * Initialize AJAX fallback endpoints.
	 */
	public function init() {
		if ( class_exists( 'TA_Logger' ) ) {
			$this->logger = TA_Logger::get_instance();
		}

		if ( class_exists( 'TA_Security' ) ) {
			$this->security = TA_Security::get_instance();
		}

		// Public AJAX actions (no authentication required).
		add_action( 'wp_ajax_nopriv_ta_track_citation', array( $this, 'handle_track_citation' ) );
		add_action( 'wp_ajax_ta_track_citation', array( $this, 'handle_track_citation' ) );

		// Health check endpoint (public).
		add_action( 'wp_ajax_nopriv_ta_health_check', array( $this, 'handle_health_check' ) );
		add_action( 'wp_ajax_ta_health_check', array( $this, 'handle_health_check' ) );

		// Admin AJAX endpoints (authenticated).
		add_action( 'wp_ajax_ta_clear_cache_ajax', array( $this, 'handle_clear_cache' ) );
		add_action( 'wp_ajax_ta_clear_errors_ajax', array( $this, 'handle_clear_errors' ) );

		if ( $this->logger ) {
			$this->logger->debug( 'AJAX fallback endpoints initialized' );
		}
	}

	/**
	 * Handle citation tracking via AJAX (fallback for REST API).
	 */
	public function handle_track_citation() {
		// Get API key from header or POST data.
		$api_key = '';
		if ( isset( $_SERVER['HTTP_X_TA_API_KEY'] ) ) {
			$api_key = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_TA_API_KEY'] ) );
		} elseif ( isset( $_POST['api_key'] ) ) {
			$api_key = sanitize_text_field( wp_unslash( $_POST['api_key'] ) );
		}

		// Verify API key.
		if ( ! $this->verify_api_key( $api_key ) ) {
			wp_send_json_error( array( 'message' => 'Invalid or missing API key' ), 401 );
		}

		// Rate limiting.
		if ( $this->is_rate_limited() ) {
			wp_send_json_error( array( 'message' => 'Too many requests. Please try again later.' ), 429 );
		}

		// Validate platform.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$platform = isset( $_POST['platform'] ) ? sanitize_text_field( wp_unslash( $_POST['platform'] ) ) : '';
		if ( ! $this->validate_platform( $platform ) ) {
			wp_send_json_error( array( 'message' => 'Invalid platform specified' ), 400 );
		}

		// Process citation.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$url = isset( $_POST['url'] ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$referer = isset( $_POST['referer'] ) ? esc_url_raw( wp_unslash( $_POST['referer'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$search_query = isset( $_POST['search_query'] ) ? sanitize_text_field( wp_unslash( $_POST['search_query'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$ip = isset( $_POST['ip'] ) ? sanitize_text_field( wp_unslash( $_POST['ip'] ) ) : $this->get_client_ip();

		// Duplicate prevention.
		$dedup_key = 'ta_citation_' . md5( $url . $platform . $ip );
		$already_tracked = get_transient( $dedup_key );

		if ( $already_tracked ) {
			wp_send_json_success(
				array(
					'message'   => 'Citation already tracked recently',
					'duplicate' => true,
					'method'    => 'ajax_fallback',
				)
			);
		}

		// Mark as tracked for 5 minutes.
		set_transient( $dedup_key, true, 5 * MINUTE_IN_SECONDS );

		// Normalize platform name.
		$platform = ucfirst( strtolower( $platform ) );

		// Get page title and post data from URL.
		$page_title = '';
		$post_id    = url_to_postid( $url );
		$post_type  = null;
		if ( $post_id ) {
			$page_title = get_the_title( $post_id );
			$post       = get_post( $post_id );
			$post_type  = $post ? $post->post_type : null;
		}

		// Geolocation lookup.
		$country_code = null;
		if ( class_exists( 'TA_Geolocation' ) ) {
			$geolocation  = TA_Geolocation::get_instance();
			$country_code = $geolocation->get_geolocation( $ip );
		}

		// IP verification.
		$ip_verified = null;
		$ip_verification_method = null;
		if ( class_exists( 'TA_IP_Verifier' ) ) {
			$ip_verifier = TA_IP_Verifier::get_instance();
			// For citations, we don't have a specific bot_type, so use generic verification
			$verification_result = $ip_verifier->verify_bot_ip( 'AI_Citation', $ip );
			$ip_verified = $verification_result['verified'];
			$ip_verification_method = $verification_result['method'];
		}

		// Insert into database.
		global $wpdb;
		$table = $wpdb->prefix . 'ta_bot_analytics';

		$result = $wpdb->insert(
			$table,
			array(
				'url'                    => $url,
				'post_id'                => $post_id,
				'post_type'              => $post_type,
				'post_title'             => $page_title,
				'bot_name'               => $platform,
				'bot_type'               => 'AI_Citation',
				'user_agent'             => 'AJAX Fallback',
				'ip_address'             => $ip,
				'country_code'           => $country_code,
				'referer'                => $referer,
				'search_query'           => $search_query,
				'traffic_type'           => 'citation_click',
				'content_type'           => 'ajax',
				'request_method'         => 'ajax_fallback',
				'cache_status'           => 'N/A',
				'response_time'          => 0,
				'ai_platform'            => $platform,
				'referer_source'         => $platform,
				'referer_medium'         => 'ai_citation',
				'detection_method'       => 'ajax_api',
				'confidence_score'       => 1.0,
				'ip_verified'            => $ip_verified,
				'ip_verification_method' => $ip_verification_method,
				'visit_timestamp'        => current_time( 'mysql' ),
			),
			array( '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%f', '%d', '%s', '%s' )
		);

		if ( $result ) {
			if ( $this->logger ) {
				$this->logger->info(
					sprintf(
						'Citation tracked via AJAX: %s from %s (query: %s)',
						$url,
						$platform,
						$search_query ?: 'none'
					)
				);
			}

			wp_send_json_success(
				array(
					'message'  => 'Citation tracked successfully',
					'platform' => $platform,
					'url'      => $url,
					'method'   => 'ajax_fallback',
				)
			);
		} else {
			if ( $this->logger ) {
				$this->logger->error( 'AJAX citation tracking failed', array( 'error' => $wpdb->last_error ) );
			}

			wp_send_json_error(
				array(
					'message' => 'Database error',
					'error'   => $wpdb->last_error,
				),
				500
			);
		}
	}

	/**
	 * Handle health check via AJAX.
	 */
	public function handle_health_check() {
		$health_check = array(
			'status'  => 'healthy',
			'version' => defined( 'TA_VERSION' ) ? TA_VERSION : 'unknown',
			'method'  => 'ajax_fallback',
			'time'    => current_time( 'mysql' ),
		);

		// Check converter availability.
		if ( class_exists( 'TA_Local_Converter' ) ) {
			$health_check['converter'] = 'available';
		} else {
			$health_check['converter'] = 'unavailable';
			$health_check['status']    = 'degraded';
		}

		// Check cache.
		if ( class_exists( 'TA_Cache_Manager' ) ) {
			$health_check['cache'] = 'operational';
		} else {
			$health_check['cache'] = 'unavailable';
		}

		wp_send_json_success( $health_check );
	}

	/**
	 * Handle clear cache via AJAX (admin only).
	 */
	public function handle_clear_cache() {
		// Verify user has admin capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Permission denied' ), 403 );
		}

		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( $this->security && ! $this->security->verify_nonce( $nonce, 'admin_ajax' ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed' ), 403 );
		}

		// Clear cache.
		if ( class_exists( 'TA_Cache_Manager' ) ) {
			$cache_manager = new TA_Cache_Manager();
			$cleared       = $cache_manager->clear_all();

			if ( $this->logger ) {
				$this->logger->info( 'Cache cleared via AJAX', array( 'items' => $cleared ) );
			}

			wp_send_json_success(
				array(
					'message' => sprintf( 'Cleared %d cached items', $cleared ),
					'count'   => $cleared,
				)
			);
		} else {
			wp_send_json_error( array( 'message' => 'Cache manager not available' ), 500 );
		}
	}

	/**
	 * Handle clear errors via AJAX (admin only).
	 */
	public function handle_clear_errors() {
		// Verify user has admin capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Permission denied' ), 403 );
		}

		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( $this->security && ! $this->security->verify_nonce( $nonce, 'admin_ajax' ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed' ), 403 );
		}

		// Clear errors.
		if ( $this->logger ) {
			$this->logger->clear_errors();
			$this->logger->reset_stats();
			$this->logger->info( 'Error logs cleared via AJAX' );

			wp_send_json_success( array( 'message' => 'Error logs cleared successfully' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Logger not available' ), 500 );
		}
	}

	/**
	 * Verify API key.
	 *
	 * @param string $provided_key The API key to verify.
	 * @return bool True if valid.
	 */
	private function verify_api_key( $provided_key ) {
		$configured_key = get_option( 'ta_headless_api_key', '' );

		// Generate key if not exists.
		if ( empty( $configured_key ) ) {
			$configured_key = wp_generate_password( 32, false );
			update_option( 'ta_headless_api_key', $configured_key );
		}

		return hash_equals( $configured_key, $provided_key );
	}

	/**
	 * Validate platform name.
	 *
	 * @param string $platform Platform name.
	 * @return bool True if valid.
	 */
	private function validate_platform( $platform ) {
		$allowed_platforms = array( 'chatgpt', 'perplexity', 'claude', 'gemini', 'copilot', 'bing', 'google' );
		$platform_lower    = strtolower( $platform );

		foreach ( $allowed_platforms as $allowed ) {
			if ( strpos( $platform_lower, $allowed ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check rate limiting.
	 *
	 * @return bool True if rate limited.
	 */
	private function is_rate_limited() {
		$ip            = $this->get_client_ip();
		$transient_key = 'ta_ajax_rate_' . md5( $ip );
		$count         = (int) get_transient( $transient_key );

		if ( $count >= 30 ) {
			return true;
		}

		set_transient( $transient_key, $count + 1, MINUTE_IN_SECONDS );
		return false;
	}

	/**
	 * Get client IP address.
	 *
	 * @return string Client IP.
	 */
	private function get_client_ip() {
		$ip = '';

		if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
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
	 * Get AJAX endpoint URL for frontend use.
	 *
	 * @param string $action The AJAX action name.
	 * @return string AJAX endpoint URL.
	 */
	public static function get_endpoint_url( $action = 'ta_track_citation' ) {
		return admin_url( 'admin-ajax.php?action=' . $action );
	}
}
