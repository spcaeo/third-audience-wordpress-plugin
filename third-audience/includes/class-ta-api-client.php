<?php
/**
 * API Client - Communicates with Router/Worker services.
 *
 * Handles secure communication with the markdown conversion worker,
 * including URL validation, encrypted API key retrieval, usage tracking,
 * retry logic with exponential backoff, and connection pooling hints.
 *
 * @package ThirdAudience
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_API_Client
 *
 * Manages communication with Third Audience router and worker services.
 *
 * @since 1.0.0
 */
class TA_API_Client {

	/**
	 * Default worker URL (direct, bypassing router for simplicity).
	 *
	 * @var string
	 */
	const DEFAULT_WORKER_URL = 'https://ta-worker.rp-2ae.workers.dev';

	/**
	 * Default router URL.
	 *
	 * @var string
	 */
	const DEFAULT_ROUTER_URL = 'https://ta-router.rp-2ae.workers.dev';

	/**
	 * Default request timeout in seconds.
	 *
	 * @var int
	 */
	const DEFAULT_TIMEOUT = 30;

	/**
	 * Health check timeout in seconds.
	 *
	 * @var int
	 */
	const HEALTH_TIMEOUT = 10;

	/**
	 * Maximum retry attempts.
	 *
	 * @var int
	 */
	const MAX_RETRIES = 3;

	/**
	 * Base delay for exponential backoff (milliseconds).
	 *
	 * @var int
	 */
	const RETRY_BASE_DELAY_MS = 1000;

	/**
	 * Maximum delay for exponential backoff (milliseconds).
	 *
	 * @var int
	 */
	const RETRY_MAX_DELAY_MS = 10000;

	/**
	 * HTTP status codes that should trigger a retry.
	 *
	 * @var array
	 */
	const RETRYABLE_STATUS_CODES = array( 408, 429, 500, 502, 503, 504 );

	/**
	 * Security instance.
	 *
	 * @var TA_Security
	 */
	private $security;

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Cached worker URL.
	 *
	 * @var string|null
	 */
	private $cached_worker_url = null;

	/**
	 * Request timeout (configurable).
	 *
	 * @var int
	 */
	private $timeout;

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 * @param int $timeout Optional. Custom timeout in seconds.
	 */
	public function __construct( $timeout = null ) {
		$this->security = TA_Security::get_instance();
		$this->logger   = TA_Logger::get_instance();
		$this->timeout  = $timeout ?? $this->get_configured_timeout();
	}

	/**
	 * Get configured timeout from options.
	 *
	 * @since 1.2.0
	 * @return int Timeout in seconds.
	 */
	private function get_configured_timeout() {
		return (int) get_option( 'ta_api_timeout', self::DEFAULT_TIMEOUT );
	}

	/**
	 * Set request timeout.
	 *
	 * @since 1.2.0
	 * @param int $timeout Timeout in seconds.
	 * @return $this
	 */
	public function set_timeout( $timeout ) {
		$this->timeout = max( 1, min( 120, (int) $timeout ) );
		return $this;
	}

	/**
	 * Get the worker URL to use for conversion.
	 *
	 * @since 1.0.0
	 * @return string|false The worker URL or false on failure.
	 */
	public function get_worker_url() {
		// Return cached URL if available.
		if ( null !== $this->cached_worker_url ) {
			return $this->cached_worker_url;
		}

		// Check if router is configured.
		$router_url = get_option( 'ta_router_url', '' );
		$api_key    = $this->get_api_key();

		// If we have a router URL and API key, use the router.
		if ( ! empty( $router_url ) && ! empty( $api_key ) ) {
			$worker = $this->get_worker_from_router( $router_url, $api_key );
			if ( $worker ) {
				$this->cached_worker_url = $worker['url'];
				return $this->cached_worker_url;
			}
		}

		// Fallback to direct worker URL.
		$worker_url = get_option( 'ta_worker_url', self::DEFAULT_WORKER_URL );
		$this->cached_worker_url = ! empty( $worker_url ) ? $worker_url : self::DEFAULT_WORKER_URL;

		return $this->cached_worker_url;
	}

	/**
	 * Get a worker from the router service.
	 *
	 * @since 1.0.0
	 * @param string $router_url Router service URL.
	 * @param string $api_key    API key.
	 * @return array|false Worker info or false on failure.
	 */
	private function get_worker_from_router( $router_url, $api_key ) {
		// Validate router URL.
		$validated_url = $this->security->validate_url_for_worker( $router_url );
		if ( is_wp_error( $validated_url ) ) {
			$this->logger->error( 'Invalid router URL.', array(
				'url'   => $router_url,
				'error' => $validated_url->get_error_message(),
			) );
			return false;
		}

		$response = $this->make_request(
			$validated_url . '/get-worker',
			array(
				'method'  => 'GET',
				'timeout' => self::HEALTH_TIMEOUT,
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'X-Site-URL'    => home_url(),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			$this->logger->error( 'Router request failed.', array(
				'url'   => $router_url,
				'error' => $response->get_error_message(),
			) );

			// Trigger notification for worker failure.
			do_action( 'ta_worker_connection_failed', $router_url, $response );

			return false;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			$this->logger->warning( 'Router returned non-200 status.', array(
				'url'    => $router_url,
				'status' => $status_code,
			) );
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( empty( $data['success'] ) || empty( $data['worker'] ) ) {
			$this->logger->warning( 'Router returned invalid response.', array(
				'url'  => $router_url,
				'body' => substr( $body, 0, 500 ),
			) );
			return false;
		}

		$this->logger->debug( 'Got worker from router.', array(
			'worker_url' => $data['worker']['url'] ?? 'unknown',
		) );

		return $data['worker'];
	}

	/**
	 * Make an HTTP request with retry logic and exponential backoff.
	 *
	 * @since 1.2.0
	 * @param string $url     The URL to request.
	 * @param array  $args    Request arguments.
	 * @param int    $attempt Current attempt number (for recursion).
	 * @return array|WP_Error Response or WP_Error.
	 */
	public function make_request( $url, $args = array(), $attempt = 1 ) {
		$defaults = array(
			'method'      => 'GET',
			'timeout'     => $this->timeout,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking'    => true,
			'headers'     => array(
				'Accept-Encoding' => 'gzip, deflate', // Request compression.
				'Connection'      => 'keep-alive',    // Connection pooling hint.
			),
			'body'        => null,
			'compress'    => true, // Enable response compression.
			'decompress'  => true, // Auto-decompress.
		);

		$args = wp_parse_args( $args, $defaults );

		// Make the request.
		if ( 'POST' === $args['method'] ) {
			$response = wp_remote_post( $url, $args );
		} else {
			$response = wp_remote_get( $url, $args );
		}

		// Check if we should retry.
		if ( $this->should_retry( $response, $attempt ) ) {
			$delay = $this->calculate_retry_delay( $attempt );

			$this->logger->debug( 'Retrying request.', array(
				'url'     => $url,
				'attempt' => $attempt,
				'delay'   => $delay . 'ms',
			) );

			// Wait before retrying.
			usleep( $delay * 1000 ); // Convert to microseconds.

			return $this->make_request( $url, $args, $attempt + 1 );
		}

		return $response;
	}

	/**
	 * Determine if a request should be retried.
	 *
	 * @since 1.2.0
	 * @param array|WP_Error $response The response.
	 * @param int            $attempt  Current attempt number.
	 * @return bool Whether to retry.
	 */
	private function should_retry( $response, $attempt ) {
		// Don't exceed max retries.
		if ( $attempt >= self::MAX_RETRIES ) {
			return false;
		}

		// Retry on WP_Error (connection failures).
		if ( is_wp_error( $response ) ) {
			$error_code = $response->get_error_code();
			// Retry on timeout or connection errors.
			$retryable_errors = array( 'http_request_failed', 'http_failure', 'timeout' );
			return in_array( $error_code, $retryable_errors, true );
		}

		// Retry on specific HTTP status codes.
		$status_code = wp_remote_retrieve_response_code( $response );
		return in_array( $status_code, self::RETRYABLE_STATUS_CODES, true );
	}

	/**
	 * Calculate delay for exponential backoff with jitter.
	 *
	 * @since 1.2.0
	 * @param int $attempt Current attempt number.
	 * @return int Delay in milliseconds.
	 */
	private function calculate_retry_delay( $attempt ) {
		// Exponential backoff: base_delay * 2^(attempt-1).
		$exponential_delay = self::RETRY_BASE_DELAY_MS * pow( 2, $attempt - 1 );

		// Add random jitter (0-25% of delay) to prevent thundering herd.
		$jitter = (int) ( $exponential_delay * ( mt_rand( 0, 25 ) / 100 ) );

		// Cap at max delay.
		return min( $exponential_delay + $jitter, self::RETRY_MAX_DELAY_MS );
	}

	/**
	 * Convert a URL to markdown.
	 *
	 * @since 1.2.0
	 * @param string $url     The URL to convert.
	 * @param array  $options Optional. Conversion options.
	 * @return string|WP_Error The markdown content or WP_Error.
	 */
	public function convert_url( $url, $options = array() ) {
		$worker_url = $this->get_worker_url();
		if ( ! $worker_url ) {
			return new WP_Error( 'no_worker', __( 'No worker URL available.', 'third-audience' ) );
		}

		// Validate worker URL.
		$validated_worker = $this->security->validate_url_for_worker( $worker_url );
		if ( is_wp_error( $validated_worker ) ) {
			return $validated_worker;
		}

		$default_options = array(
			'include_frontmatter'  => true,
			'extract_main_content' => true,
		);

		$options = wp_parse_args( $options, $default_options );

		$response = $this->make_request(
			$validated_worker . '/convert',
			array(
				'method'  => 'POST',
				'headers' => array(
					'Content-Type' => 'application/json',
					'Accept'       => 'text/markdown',
				),
				'body'    => wp_json_encode( array(
					'url'     => $url,
					'options' => $options,
				) ),
			)
		);

		if ( is_wp_error( $response ) ) {
			$this->logger->error( 'Conversion request failed.', array(
				'url'   => $url,
				'error' => $response->get_error_message(),
			) );
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			return new WP_Error(
				'conversion_failed',
				/* translators: %d: HTTP status code */
				sprintf( __( 'Conversion failed with status %d', 'third-audience' ), $status_code )
			);
		}

		$body         = wp_remote_retrieve_body( $response );
		$content_type = wp_remote_retrieve_header( $response, 'content-type' );

		// Handle JSON response.
		if ( false !== strpos( $content_type, 'application/json' ) ) {
			$data = json_decode( $body, true );
			if ( ! empty( $data['markdown'] ) ) {
				return $data['markdown'];
			}
			return new WP_Error(
				'invalid_response',
				$data['error']['message'] ?? __( 'Invalid response from worker.', 'third-audience' )
			);
		}

		return $body;
	}

	/**
	 * Track usage with the router.
	 *
	 * @since 1.0.0
	 * @param string $worker_id         Worker ID.
	 * @param string $url_converted     URL that was converted.
	 * @param int    $bytes_in          Input bytes.
	 * @param int    $bytes_out         Output bytes.
	 * @param int    $conversion_time_ms Conversion time in milliseconds.
	 * @param bool   $cache_hit         Whether it was a cache hit.
	 * @param bool   $success           Whether conversion succeeded.
	 * @return void
	 */
	public function track_usage( $worker_id, $url_converted, $bytes_in, $bytes_out, $conversion_time_ms, $cache_hit, $success ) {
		$router_url = get_option( 'ta_router_url', '' );
		$api_key    = $this->get_api_key();

		if ( empty( $router_url ) || empty( $api_key ) ) {
			return;
		}

		// Fire and forget - don't wait for response.
		wp_remote_post(
			$router_url . '/track-usage',
			array(
				'timeout'  => 1,
				'blocking' => false,
				'headers'  => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'body'     => wp_json_encode( array(
					'worker_id'          => $worker_id,
					'site_url'           => home_url(),
					'url_converted'      => $url_converted,
					'bytes_in'           => $bytes_in,
					'bytes_out'          => $bytes_out,
					'conversion_time_ms' => $conversion_time_ms,
					'cache_hit'          => $cache_hit,
					'success'            => $success,
				) ),
			)
		);
	}

	/**
	 * Get the API key (decrypted).
	 *
	 * @since 1.0.0
	 * @return string The API key.
	 */
	public function get_api_key() {
		return $this->security->get_encrypted_option( 'ta_api_key' );
	}

	/**
	 * Store the API key (encrypted).
	 *
	 * @since 1.0.0
	 * @param string $api_key The API key to store.
	 * @return bool Whether the key was stored successfully.
	 */
	public function store_api_key( $api_key ) {
		if ( empty( $api_key ) ) {
			delete_option( 'ta_api_key_encrypted' );
			delete_option( 'ta_api_key' );
			$this->logger->info( 'API key cleared.' );
			return true;
		}

		$result = $this->security->store_encrypted_option( 'ta_api_key', $api_key );

		if ( $result ) {
			$this->logger->info( 'API key stored securely.' );
		} else {
			$this->logger->error( 'Failed to store API key.' );
		}

		return $result;
	}

	/**
	 * Test connection to the worker.
	 *
	 * @since 1.1.0
	 * @return array|WP_Error Result array or WP_Error on failure.
	 */
	public function test_connection() {
		$worker_url = $this->get_worker_url();

		if ( empty( $worker_url ) ) {
			return new WP_Error( 'no_worker_url', __( 'No worker URL configured.', 'third-audience' ) );
		}

		// Validate URL.
		$validated_url = $this->security->validate_url_for_worker( $worker_url );
		if ( is_wp_error( $validated_url ) ) {
			return $validated_url;
		}

		$start_time = microtime( true );

		$response = $this->make_request(
			$validated_url . '/health',
			array(
				'timeout' => self::HEALTH_TIMEOUT,
			)
		);

		$response_time = round( ( microtime( true ) - $start_time ) * 1000, 2 );

		if ( is_wp_error( $response ) ) {
			$this->logger->error( 'Worker health check failed.', array(
				'url'   => $worker_url,
				'error' => $response->get_error_message(),
			) );
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );

		if ( 200 !== $status_code ) {
			return new WP_Error(
				'bad_status',
				/* translators: %d: HTTP status code */
				sprintf( __( 'Worker returned status %d', 'third-audience' ), $status_code )
			);
		}

		$data = json_decode( $body, true );

		$this->logger->info( 'Worker health check successful.', array(
			'url'           => $worker_url,
			'status'        => $status_code,
			'response_time' => $response_time . 'ms',
		) );

		return array(
			'status'        => 'healthy',
			'worker_url'    => $worker_url,
			'response_time' => $response_time,
			'response'      => $data,
		);
	}

	/**
	 * Check if rate limited by the worker.
	 *
	 * @since 1.2.0
	 * @param array|WP_Error $response The response.
	 * @return bool|int False if not rate limited, or retry-after seconds.
	 */
	public function is_rate_limited( $response ) {
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 429 !== $status_code ) {
			return false;
		}

		// Check for Retry-After header.
		$retry_after = wp_remote_retrieve_header( $response, 'retry-after' );
		if ( $retry_after ) {
			return (int) $retry_after;
		}

		// Default to 60 seconds.
		return 60;
	}

	/**
	 * Clear cached worker URL.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function clear_worker_cache() {
		$this->cached_worker_url = null;
	}

	/**
	 * Batch convert multiple URLs.
	 *
	 * @since 1.2.0
	 * @param array $urls    Array of URLs to convert.
	 * @param array $options Optional. Conversion options.
	 * @return array Array of url => result pairs.
	 */
	public function batch_convert( $urls, $options = array() ) {
		$results = array();

		foreach ( $urls as $url ) {
			$results[ $url ] = $this->convert_url( $url, $options );

			// Small delay between requests to avoid overwhelming the worker.
			usleep( 100000 ); // 100ms.
		}

		return $results;
	}

	/**
	 * Get request statistics.
	 *
	 * @since 1.2.0
	 * @return array Request statistics.
	 */
	public function get_stats() {
		return array(
			'worker_url'     => $this->get_worker_url(),
			'timeout'        => $this->timeout,
			'max_retries'    => self::MAX_RETRIES,
			'base_delay_ms'  => self::RETRY_BASE_DELAY_MS,
		);
	}
}
