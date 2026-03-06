<?php
/**
 * Rate Limiter - Implements rate limiting for conversion requests.
 *
 * Provides sliding window rate limiting to prevent API abuse and
 * ensure fair usage of the conversion service.
 *
 * @package ThirdAudience
 * @since   1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Rate_Limiter
 *
 * Rate limiting for Third Audience conversion requests.
 *
 * @since 1.2.0
 */
class TA_Rate_Limiter {

	/**
	 * Default rate limit window in seconds.
	 *
	 * @var int
	 */
	const DEFAULT_WINDOW = 60;

	/**
	 * Default max requests per window.
	 *
	 * @var int
	 */
	const DEFAULT_MAX_REQUESTS = 100;

	/**
	 * Transient prefix for rate limit data.
	 *
	 * @var string
	 */
	const TRANSIENT_PREFIX = 'ta_rate_limit_';

	/**
	 * Option key for rate limit settings.
	 *
	 * @var string
	 */
	const SETTINGS_OPTION = 'ta_rate_limit_settings';

	/**
	 * Rate limit window in seconds.
	 *
	 * @var int
	 */
	private $window;

	/**
	 * Maximum requests per window.
	 *
	 * @var int
	 */
	private $max_requests;

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 * @param int|null $window       Optional. Rate limit window in seconds.
	 * @param int|null $max_requests Optional. Maximum requests per window.
	 */
	public function __construct( $window = null, $max_requests = null ) {
		$this->logger = TA_Logger::get_instance();

		$settings = $this->get_settings();

		$this->window       = $window ?? $settings['window'];
		$this->max_requests = $max_requests ?? $settings['max_requests'];
	}

	/**
	 * Get rate limit settings.
	 *
	 * @since 1.2.0
	 * @return array Rate limit settings.
	 */
	public function get_settings() {
		return get_option( self::SETTINGS_OPTION, array(
			'enabled'      => true,
			'window'       => self::DEFAULT_WINDOW,
			'max_requests' => self::DEFAULT_MAX_REQUESTS,
			'by_ip'        => true,
			'by_user'      => false,
		) );
	}

	/**
	 * Save rate limit settings.
	 *
	 * @since 1.2.0
	 * @param array $settings The settings to save.
	 * @return bool Whether the settings were saved.
	 */
	public function save_settings( $settings ) {
		$sanitized = array(
			'enabled'      => ! empty( $settings['enabled'] ),
			'window'       => absint( $settings['window'] ?? self::DEFAULT_WINDOW ),
			'max_requests' => absint( $settings['max_requests'] ?? self::DEFAULT_MAX_REQUESTS ),
			'by_ip'        => ! empty( $settings['by_ip'] ),
			'by_user'      => ! empty( $settings['by_user'] ),
		);

		return update_option( self::SETTINGS_OPTION, $sanitized, false );
	}

	/**
	 * Check if rate limiting is enabled.
	 *
	 * @since 1.2.0
	 * @return bool Whether rate limiting is enabled.
	 */
	public function is_enabled() {
		$settings = $this->get_settings();
		return ! empty( $settings['enabled'] );
	}

	/**
	 * Generate a rate limit key for the current request.
	 *
	 * @since 1.2.0
	 * @param string|null $identifier Optional. Custom identifier.
	 * @return string The rate limit key.
	 */
	public function get_key( $identifier = null ) {
		if ( null !== $identifier ) {
			return self::TRANSIENT_PREFIX . md5( $identifier );
		}

		$settings = $this->get_settings();
		$parts    = array( 'global' );

		// Add IP to key if configured.
		if ( ! empty( $settings['by_ip'] ) ) {
			$parts[] = $this->get_client_ip();
		}

		// Add user ID to key if configured and user is logged in.
		if ( ! empty( $settings['by_user'] ) && is_user_logged_in() ) {
			$parts[] = 'user_' . get_current_user_id();
		}

		return self::TRANSIENT_PREFIX . md5( implode( ':', $parts ) );
	}

	/**
	 * Get the client IP address.
	 *
	 * @since 1.2.0
	 * @return string The client IP.
	 */
	private function get_client_ip() {
		$headers = array(
			'HTTP_CF_CONNECTING_IP', // Cloudflare.
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_REAL_IP',
			'REMOTE_ADDR',
		);

		foreach ( $headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );
				// Handle comma-separated IPs (X-Forwarded-For).
				if ( false !== strpos( $ip, ',' ) ) {
					$ips = explode( ',', $ip );
					$ip  = trim( $ips[0] );
				}
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return '0.0.0.0';
	}

	/**
	 * Check if the current request is rate limited.
	 *
	 * @since 1.2.0
	 * @param string|null $identifier Optional. Custom identifier.
	 * @return bool Whether the request is rate limited.
	 */
	public function is_rate_limited( $identifier = null ) {
		if ( ! $this->is_enabled() ) {
			return false;
		}

		$key  = $this->get_key( $identifier );
		$data = $this->get_rate_data( $key );

		return $data['count'] >= $this->max_requests;
	}

	/**
	 * Record a request for rate limiting.
	 *
	 * @since 1.2.0
	 * @param string|null $identifier Optional. Custom identifier.
	 * @return array Updated rate limit data.
	 */
	public function record_request( $identifier = null ) {
		if ( ! $this->is_enabled() ) {
			return array(
				'count'     => 0,
				'remaining' => $this->max_requests,
				'reset'     => time() + $this->window,
			);
		}

		$key  = $this->get_key( $identifier );
		$data = $this->get_rate_data( $key );

		// Increment count.
		$data['count']++;
		$data['remaining'] = max( 0, $this->max_requests - $data['count'] );

		// Save updated data.
		$ttl = $data['reset'] - time();
		if ( $ttl > 0 ) {
			set_transient( $key, $data, $ttl );
		}

		// Log if rate limited.
		if ( $data['count'] >= $this->max_requests ) {
			$this->logger->warning( 'Rate limit reached.', array(
				'identifier' => $identifier ?? 'default',
				'ip'         => $this->get_client_ip(),
				'count'      => $data['count'],
				'max'        => $this->max_requests,
			) );
		}

		return $data;
	}

	/**
	 * Get rate limit data for a key.
	 *
	 * @since 1.2.0
	 * @param string $key The rate limit key.
	 * @return array Rate limit data.
	 */
	private function get_rate_data( $key ) {
		$data = get_transient( $key );

		if ( false === $data || ! is_array( $data ) ) {
			// Initialize new window.
			return array(
				'count'     => 0,
				'remaining' => $this->max_requests,
				'reset'     => time() + $this->window,
			);
		}

		// Check if window has expired.
		if ( time() >= $data['reset'] ) {
			// Start new window.
			return array(
				'count'     => 0,
				'remaining' => $this->max_requests,
				'reset'     => time() + $this->window,
			);
		}

		return $data;
	}

	/**
	 * Get current rate limit status.
	 *
	 * @since 1.2.0
	 * @param string|null $identifier Optional. Custom identifier.
	 * @return array Rate limit status.
	 */
	public function get_status( $identifier = null ) {
		$key  = $this->get_key( $identifier );
		$data = $this->get_rate_data( $key );

		return array(
			'limited'        => $data['count'] >= $this->max_requests,
			'count'          => $data['count'],
			'remaining'      => $data['remaining'],
			'reset'          => $data['reset'],
			'reset_in'       => max( 0, $data['reset'] - time() ),
			'max_requests'   => $this->max_requests,
			'window'         => $this->window,
		);
	}

	/**
	 * Reset rate limit for an identifier.
	 *
	 * @since 1.2.0
	 * @param string|null $identifier Optional. Custom identifier.
	 * @return bool Whether the reset was successful.
	 */
	public function reset( $identifier = null ) {
		$key = $this->get_key( $identifier );
		return delete_transient( $key );
	}

	/**
	 * Get rate limit headers for HTTP response.
	 *
	 * @since 1.2.0
	 * @param string|null $identifier Optional. Custom identifier.
	 * @return array Array of header name => value pairs.
	 */
	public function get_headers( $identifier = null ) {
		$status = $this->get_status( $identifier );

		return array(
			'X-RateLimit-Limit'     => $this->max_requests,
			'X-RateLimit-Remaining' => $status['remaining'],
			'X-RateLimit-Reset'     => $status['reset'],
		);
	}

	/**
	 * Send rate limit headers.
	 *
	 * @since 1.2.0
	 * @param string|null $identifier Optional. Custom identifier.
	 * @return void
	 */
	public function send_headers( $identifier = null ) {
		if ( headers_sent() ) {
			return;
		}

		$headers = $this->get_headers( $identifier );

		foreach ( $headers as $name => $value ) {
			header( $name . ': ' . $value );
		}
	}

	/**
	 * Send rate limited response.
	 *
	 * @since 1.2.0
	 * @param string|null $identifier Optional. Custom identifier.
	 * @return void
	 */
	public function send_rate_limited_response( $identifier = null ) {
		$status = $this->get_status( $identifier );

		status_header( 429 );
		header( 'Retry-After: ' . $status['reset_in'] );
		$this->send_headers( $identifier );
		header( 'Content-Type: application/json' );

		echo wp_json_encode( array(
			'error'   => true,
			'message' => __( 'Rate limit exceeded. Please try again later.', 'third-audience' ),
			'retry_after' => $status['reset_in'],
		) );
		exit;
	}

	/**
	 * Middleware to check rate limiting.
	 *
	 * @since 1.2.0
	 * @param string|null $identifier Optional. Custom identifier.
	 * @return bool True if allowed, exits with 429 if rate limited.
	 */
	public function check( $identifier = null ) {
		if ( $this->is_rate_limited( $identifier ) ) {
			$this->send_rate_limited_response( $identifier );
			return false; // Never reached.
		}

		$this->record_request( $identifier );
		return true;
	}

	/**
	 * Get statistics about rate limiting.
	 *
	 * @since 1.2.0
	 * @return array Rate limiting statistics.
	 */
	public function get_stats() {
		global $wpdb;

		// Count active rate limit entries.
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options}
				 WHERE option_name LIKE %s",
				'_transient_' . self::TRANSIENT_PREFIX . '%'
			)
		);

		return array(
			'enabled'      => $this->is_enabled(),
			'window'       => $this->window,
			'max_requests' => $this->max_requests,
			'active_keys'  => (int) $count,
		);
	}

	/**
	 * Get rate limits for a specific bot type based on priority.
	 *
	 * @since 2.1.0
	 * @param string $bot_type Bot type identifier.
	 * @param string $priority Bot priority level.
	 * @return array Rate limits with 'per_minute' and 'per_hour' keys.
	 */
	public function get_bot_rate_limits( $bot_type, $priority ) {
		// Get custom limits from settings if configured.
		$saved_limits = get_option( 'ta_bot_rate_limits', array() );

		// Check if limits are set for this specific priority level.
		if ( isset( $saved_limits[ $priority ] ) ) {
			return $saved_limits[ $priority ];
		}

		// Default limits based on priority.
		$default_limits = array(
			'high'    => array(
				'per_minute' => 0, // Unlimited (0 = no limit).
				'per_hour'   => 0,
			),
			'medium'  => array(
				'per_minute' => 60,
				'per_hour'   => 1000,
			),
			'low'     => array(
				'per_minute' => 10,
				'per_hour'   => 100,
			),
			'blocked' => array(
				'per_minute' => 0,
				'per_hour'   => 0,
			),
		);

		return isset( $default_limits[ $priority ] ) ? $default_limits[ $priority ] : $default_limits['medium'];
	}

	/**
	 * Check rate limit for a specific bot type and IP.
	 *
	 * @since 2.1.0
	 * @param string $bot_type Bot type identifier.
	 * @param string $priority Bot priority level.
	 * @param string $ip       IP address.
	 * @return array Array with 'allowed' (bool), 'limit_type' (string), 'retry_after' (int).
	 */
	public function check_bot_rate_limit( $bot_type, $priority, $ip ) {
		$limits = $this->get_bot_rate_limits( $bot_type, $priority );

		// If both limits are 0 (unlimited or blocked), allow the request.
		// Note: Blocked bots should be handled before rate limiting.
		if ( $limits['per_minute'] === 0 && $limits['per_hour'] === 0 ) {
			return array(
				'allowed'     => true,
				'limit_type'  => null,
				'retry_after' => 0,
			);
		}

		// Check minute limit.
		if ( $limits['per_minute'] > 0 ) {
			$minute_key  = 'ta_ratelimit_' . md5( $bot_type . '_' . $ip ) . '_minute';
			$minute_data = get_transient( $minute_key );

			if ( false === $minute_data ) {
				$minute_data = array( 'count' => 0, 'reset' => time() + 60 );
			}

			if ( $minute_data['count'] >= $limits['per_minute'] ) {
				return array(
					'allowed'     => false,
					'limit_type'  => 'minute',
					'retry_after' => $minute_data['reset'] - time(),
					'limit'       => $limits['per_minute'],
					'remaining'   => 0,
					'reset'       => $minute_data['reset'],
				);
			}
		}

		// Check hour limit.
		if ( $limits['per_hour'] > 0 ) {
			$hour_key  = 'ta_ratelimit_' . md5( $bot_type . '_' . $ip ) . '_hour';
			$hour_data = get_transient( $hour_key );

			if ( false === $hour_data ) {
				$hour_data = array( 'count' => 0, 'reset' => time() + 3600 );
			}

			if ( $hour_data['count'] >= $limits['per_hour'] ) {
				return array(
					'allowed'     => false,
					'limit_type'  => 'hour',
					'retry_after' => $hour_data['reset'] - time(),
					'limit'       => $limits['per_hour'],
					'remaining'   => 0,
					'reset'       => $hour_data['reset'],
				);
			}
		}

		// Allowed - calculate remaining counts for headers.
		$minute_key       = 'ta_ratelimit_' . md5( $bot_type . '_' . $ip ) . '_minute';
		$hour_key         = 'ta_ratelimit_' . md5( $bot_type . '_' . $ip ) . '_hour';
		$minute_data      = get_transient( $minute_key ) ?: array( 'count' => 0 );
		$hour_data        = get_transient( $hour_key ) ?: array( 'count' => 0 );
		$minute_remaining = $limits['per_minute'] > 0 ? max( 0, $limits['per_minute'] - $minute_data['count'] ) : 999999;
		$hour_remaining   = $limits['per_hour'] > 0 ? max( 0, $limits['per_hour'] - $hour_data['count'] ) : 999999;

		return array(
			'allowed'          => true,
			'limit_type'       => null,
			'retry_after'      => 0,
			'minute_limit'     => $limits['per_minute'],
			'minute_remaining' => $minute_remaining,
			'hour_limit'       => $limits['per_hour'],
			'hour_remaining'   => $hour_remaining,
		);
	}

	/**
	 * Increment rate limit counters for a bot.
	 *
	 * @since 2.1.0
	 * @param string $bot_type Bot type identifier.
	 * @param string $ip       IP address.
	 * @return void
	 */
	public function increment_bot_counter( $bot_type, $ip ) {
		$minute_key = 'ta_ratelimit_' . md5( $bot_type . '_' . $ip ) . '_minute';
		$hour_key   = 'ta_ratelimit_' . md5( $bot_type . '_' . $ip ) . '_hour';

		// Increment minute counter.
		$minute_data = get_transient( $minute_key );
		if ( false === $minute_data ) {
			$minute_data = array( 'count' => 0, 'reset' => time() + 60 );
		}
		$minute_data['count']++;
		set_transient( $minute_key, $minute_data, 60 );

		// Increment hour counter.
		$hour_data = get_transient( $hour_key );
		if ( false === $hour_data ) {
			$hour_data = array( 'count' => 0, 'reset' => time() + 3600 );
		}
		$hour_data['count']++;
		set_transient( $hour_key, $hour_data, 3600 );
	}

	/**
	 * Get rate limit violations from analytics.
	 *
	 * @since 2.1.0
	 * @param int $limit Number of recent violations to fetch.
	 * @return array Array of violation records.
	 */
	public function get_rate_limit_violations( $limit = 50 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . TA_Bot_Analytics::TABLE_NAME;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table_name}
				WHERE cache_status = 'RATE_LIMITED'
				ORDER BY visit_timestamp DESC
				LIMIT %d",
				$limit
			),
			ARRAY_A
		);
	}

	/**
	 * Get rate limit violation statistics by bot type.
	 *
	 * @since 2.1.0
	 * @return array Statistics grouped by bot type.
	 */
	public function get_violation_stats() {
		global $wpdb;
		$table_name = $wpdb->prefix . TA_Bot_Analytics::TABLE_NAME;

		$results = $wpdb->get_results(
			"SELECT bot_type, bot_name, COUNT(*) as violations,
			COUNT(DISTINCT ip_address) as unique_ips
			FROM {$table_name}
			WHERE cache_status = 'RATE_LIMITED'
			GROUP BY bot_type, bot_name
			ORDER BY violations DESC",
			ARRAY_A
		);

		return $results;
	}

	/**
	 * Clear all rate limit data.
	 *
	 * @since 1.2.0
	 * @return int Number of entries cleared.
	 */
	public function clear_all() {
		global $wpdb;

		$count = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options}
				 WHERE option_name LIKE %s OR option_name LIKE %s",
				'_transient_' . self::TRANSIENT_PREFIX . '%',
				'_transient_timeout_' . self::TRANSIENT_PREFIX . '%'
			)
		);

		$this->logger->info( 'Rate limit data cleared.', array( 'count' => $count / 2 ) );

		return (int) ( $count / 2 );
	}
}
