<?php
/**
 * Security Utilities - Nonce verification, sanitization, encryption, and capability checks.
 *
 * Provides centralized security utilities for the Third Audience plugin following
 * WordPress VIP coding standards and best practices.
 *
 * @package ThirdAudience
 * @since   1.1.0
 */

// phpcs:disable WordPress.Security.ValidatedSanitizedInput -- This class provides sanitization utilities.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Security
 *
 * Centralized security utilities for Third Audience plugin.
 *
 * @since 1.1.0
 */
class TA_Security {

	/**
	 * Required capability for admin operations.
	 *
	 * @var string
	 */
	const ADMIN_CAPABILITY = 'manage_options';

	/**
	 * Nonce action prefix.
	 *
	 * @var string
	 */
	const NONCE_PREFIX = 'ta_';

	/**
	 * Encryption cipher method.
	 *
	 * @var string
	 */
	const CIPHER_METHOD = 'aes-256-cbc';

	/**
	 * Singleton instance.
	 *
	 * @var TA_Security|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 1.1.0
	 * @return TA_Security
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor for singleton.
	 *
	 * @since 1.1.0
	 */
	private function __construct() {}

	/**
	 * Check if current user has admin capability.
	 *
	 * @since 1.1.0
	 * @return bool True if user has capability.
	 */
	public function current_user_can_manage() {
		return current_user_can( self::ADMIN_CAPABILITY );
	}

	/**
	 * Verify user has admin capability and die if not.
	 *
	 * @since 1.1.0
	 * @param string $message Optional. Error message to display.
	 * @return void
	 */
	public function verify_admin_capability( $message = '' ) {
		if ( ! $this->current_user_can_manage() ) {
			$message = $message ?: __( 'You do not have permission to access this page.', 'third-audience' );
			wp_die(
				esc_html( $message ),
				esc_html__( 'Permission Denied', 'third-audience' ),
				array( 'response' => 403 )
			);
		}
	}

	/**
	 * Create a nonce for a specific action.
	 *
	 * @since 1.1.0
	 * @param string $action The action name (without prefix).
	 * @return string The nonce value.
	 */
	public function create_nonce( $action ) {
		return wp_create_nonce( self::NONCE_PREFIX . $action );
	}

	/**
	 * Verify a nonce for a specific action.
	 *
	 * @since 1.1.0
	 * @param string $nonce  The nonce value to verify.
	 * @param string $action The action name (without prefix).
	 * @return bool True if valid.
	 */
	public function verify_nonce( $nonce, $action ) {
		return (bool) wp_verify_nonce( $nonce, self::NONCE_PREFIX . $action );
	}

	/**
	 * Verify nonce from request and die if invalid.
	 *
	 * @since 1.1.0
	 * @param string $action     The action name (without prefix).
	 * @param string $nonce_name The name of the nonce field in the request.
	 * @param string $method     The request method ('GET' or 'POST').
	 * @return void
	 */
	public function verify_nonce_or_die( $action, $nonce_name = '_wpnonce', $method = 'REQUEST' ) {
		$nonce = '';

		switch ( strtoupper( $method ) ) {
			case 'GET':
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$nonce = isset( $_GET[ $nonce_name ] ) ? sanitize_text_field( wp_unslash( $_GET[ $nonce_name ] ) ) : '';
				break;
			case 'POST':
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$nonce = isset( $_POST[ $nonce_name ] ) ? sanitize_text_field( wp_unslash( $_POST[ $nonce_name ] ) ) : '';
				break;
			default:
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$nonce = isset( $_REQUEST[ $nonce_name ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $nonce_name ] ) ) : '';
				break;
		}

		if ( ! $this->verify_nonce( $nonce, $action ) ) {
			wp_die(
				esc_html__( 'Security check failed. Please refresh the page and try again.', 'third-audience' ),
				esc_html__( 'Security Error', 'third-audience' ),
				array( 'response' => 403 )
			);
		}
	}

	/**
	 * Output a nonce field for forms.
	 *
	 * @since 1.1.0
	 * @param string $action      The action name (without prefix).
	 * @param string $nonce_name  The name of the nonce field.
	 * @param bool   $referer     Whether to include referer field.
	 * @param bool   $echo        Whether to echo or return.
	 * @return string|void The nonce field HTML if $echo is false.
	 */
	public function nonce_field( $action, $nonce_name = '_wpnonce', $referer = true, $echo = true ) {
		return wp_nonce_field( self::NONCE_PREFIX . $action, $nonce_name, $referer, $echo );
	}

	/**
	 * Sanitize a text input.
	 *
	 * @since 1.1.0
	 * @param mixed $value The value to sanitize.
	 * @return string Sanitized string.
	 */
	public function sanitize_text( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}
		return sanitize_text_field( wp_unslash( $value ) );
	}

	/**
	 * Sanitize an email address.
	 *
	 * @since 1.1.0
	 * @param mixed $value The email to sanitize.
	 * @return string Sanitized email or empty string.
	 */
	public function sanitize_email( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}
		return sanitize_email( $value );
	}

	/**
	 * Sanitize a URL.
	 *
	 * @since 1.1.0
	 * @param mixed  $value     The URL to sanitize.
	 * @param array  $protocols Allowed protocols.
	 * @return string Sanitized URL or empty string.
	 */
	public function sanitize_url( $value, $protocols = array( 'http', 'https' ) ) {
		if ( ! is_string( $value ) ) {
			return '';
		}
		$url = esc_url_raw( trim( $value ), $protocols );

		// Additional validation.
		if ( ! empty( $url ) && ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return '';
		}

		return $url;
	}

	/**
	 * Sanitize and validate a URL before sending to external service.
	 *
	 * @since 1.1.0
	 * @param string $url The URL to validate.
	 * @return string|WP_Error Sanitized URL or WP_Error on failure.
	 */
	public function validate_url_for_worker( $url ) {
		$sanitized = $this->sanitize_url( $url );

		if ( empty( $sanitized ) ) {
			return new WP_Error( 'invalid_url', __( 'Invalid URL format.', 'third-audience' ) );
		}

		// Parse URL.
		$parsed = wp_parse_url( $sanitized );
		if ( ! $parsed || empty( $parsed['host'] ) ) {
			return new WP_Error( 'invalid_url', __( 'Could not parse URL.', 'third-audience' ) );
		}

		// Block internal/private IPs (prevent SSRF).
		$ip = gethostbyname( $parsed['host'] );
		if ( $ip !== $parsed['host'] ) { // If resolved.
			if ( $this->is_private_ip( $ip ) ) {
				return new WP_Error( 'blocked_url', __( 'Internal URLs are not allowed.', 'third-audience' ) );
			}
		}

		// Block localhost variations.
		$blocked_hosts = array(
			'localhost',
			'127.0.0.1',
			'0.0.0.0',
			'::1',
			'[::1]',
		);
		if ( in_array( strtolower( $parsed['host'] ), $blocked_hosts, true ) ) {
			return new WP_Error( 'blocked_url', __( 'Localhost URLs are not allowed.', 'third-audience' ) );
		}

		// Ensure it's not a file:// or other dangerous protocol.
		if ( ! in_array( $parsed['scheme'] ?? '', array( 'http', 'https' ), true ) ) {
			return new WP_Error( 'invalid_protocol', __( 'Only HTTP and HTTPS URLs are allowed.', 'third-audience' ) );
		}

		return $sanitized;
	}

	/**
	 * Check if an IP address is private/internal.
	 *
	 * @since 1.1.0
	 * @param string $ip The IP address to check.
	 * @return bool True if private.
	 */
	private function is_private_ip( $ip ) {
		return ! filter_var(
			$ip,
			FILTER_VALIDATE_IP,
			FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
		);
	}

	/**
	 * Sanitize an integer.
	 *
	 * @since 1.1.0
	 * @param mixed $value The value to sanitize.
	 * @param int   $min   Minimum allowed value.
	 * @param int   $max   Maximum allowed value.
	 * @return int Sanitized integer.
	 */
	public function sanitize_int( $value, $min = PHP_INT_MIN, $max = PHP_INT_MAX ) {
		$value = absint( $value );
		return max( $min, min( $max, $value ) );
	}

	/**
	 * Sanitize a boolean.
	 *
	 * @since 1.1.0
	 * @param mixed $value The value to sanitize.
	 * @return bool Sanitized boolean.
	 */
	public function sanitize_bool( $value ) {
		return rest_sanitize_boolean( $value );
	}

	/**
	 * Sanitize an array of post types.
	 *
	 * @since 1.1.0
	 * @param mixed $value The value to sanitize.
	 * @return array Sanitized array of post type slugs.
	 */
	public function sanitize_post_types( $value ) {
		if ( ! is_array( $value ) ) {
			return array( 'post', 'page' );
		}

		$valid_types = get_post_types( array( 'public' => true ) );
		$sanitized   = array();

		foreach ( $value as $type ) {
			$type = sanitize_key( $type );
			if ( in_array( $type, $valid_types, true ) ) {
				$sanitized[] = $type;
			}
		}

		return ! empty( $sanitized ) ? $sanitized : array( 'post', 'page' );
	}

	/**
	 * Sanitize textarea content.
	 *
	 * @since 1.1.0
	 * @param mixed $value The value to sanitize.
	 * @return string Sanitized string.
	 */
	public function sanitize_textarea( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}
		return sanitize_textarea_field( wp_unslash( $value ) );
	}

	/**
	 * Escape text for HTML output.
	 *
	 * @since 1.1.0
	 * @param mixed $value The value to escape.
	 * @return string Escaped string.
	 */
	public function escape_html( $value ) {
		return esc_html( (string) $value );
	}

	/**
	 * Escape an attribute for HTML output.
	 *
	 * @since 1.1.0
	 * @param mixed $value The value to escape.
	 * @return string Escaped string.
	 */
	public function escape_attr( $value ) {
		return esc_attr( (string) $value );
	}

	/**
	 * Escape a URL for HTML output.
	 *
	 * @since 1.1.0
	 * @param mixed $value The value to escape.
	 * @return string Escaped URL.
	 */
	public function escape_url( $value ) {
		return esc_url( (string) $value );
	}

	/**
	 * Get the encryption key for secure storage.
	 *
	 * @since 1.1.0
	 * @return string The encryption key.
	 */
	private function get_encryption_key() {
		// Use SECURE_AUTH_KEY if available, fall back to AUTH_KEY.
		$key = defined( 'SECURE_AUTH_KEY' ) && SECURE_AUTH_KEY ? SECURE_AUTH_KEY : '';
		if ( empty( $key ) ) {
			$key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		}

		// Ensure key is at least 32 bytes for AES-256.
		if ( strlen( $key ) < 32 ) {
			$key = str_pad( $key, 32, $key );
		}

		return substr( hash( 'sha256', $key, true ), 0, 32 );
	}

	/**
	 * Encrypt a value using AES-256-CBC.
	 *
	 * @since 1.1.0
	 * @param string $value The value to encrypt.
	 * @return string|false Base64 encoded encrypted value or false on failure.
	 */
	public function encrypt( $value ) {
		if ( empty( $value ) || ! is_string( $value ) ) {
			return false;
		}

		if ( ! function_exists( 'openssl_encrypt' ) ) {
			// Fall back to XOR encryption if OpenSSL not available.
			return $this->xor_encrypt( $value );
		}

		$key = $this->get_encryption_key();
		$iv  = openssl_random_pseudo_bytes( openssl_cipher_iv_length( self::CIPHER_METHOD ) );

		$encrypted = openssl_encrypt( $value, self::CIPHER_METHOD, $key, OPENSSL_RAW_DATA, $iv );

		if ( false === $encrypted ) {
			return false;
		}

		// Prepend IV to encrypted data and base64 encode.
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return base64_encode( $iv . $encrypted );
	}

	/**
	 * Decrypt a value encrypted with AES-256-CBC.
	 *
	 * @since 1.1.0
	 * @param string $encrypted The base64 encoded encrypted value.
	 * @return string|false The decrypted value or false on failure.
	 */
	public function decrypt( $encrypted ) {
		if ( empty( $encrypted ) || ! is_string( $encrypted ) ) {
			return false;
		}

		if ( ! function_exists( 'openssl_decrypt' ) ) {
			// Fall back to XOR decryption if OpenSSL not available.
			return $this->xor_decrypt( $encrypted );
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$data = base64_decode( $encrypted, true );
		if ( false === $data ) {
			return false;
		}

		$iv_length = openssl_cipher_iv_length( self::CIPHER_METHOD );
		if ( strlen( $data ) < $iv_length ) {
			return false;
		}

		$iv        = substr( $data, 0, $iv_length );
		$encrypted = substr( $data, $iv_length );

		$key = $this->get_encryption_key();

		$decrypted = openssl_decrypt( $encrypted, self::CIPHER_METHOD, $key, OPENSSL_RAW_DATA, $iv );

		return $decrypted;
	}

	/**
	 * XOR encrypt as fallback when OpenSSL is not available.
	 *
	 * @since 1.1.0
	 * @param string $value The value to encrypt.
	 * @return string Base64 encoded encrypted value.
	 */
	private function xor_encrypt( $value ) {
		$key = defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : AUTH_KEY;
		$key = str_repeat( $key, (int) ceil( strlen( $value ) / strlen( $key ) ) );

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return base64_encode( 'xor:' . ( $value ^ substr( $key, 0, strlen( $value ) ) ) );
	}

	/**
	 * XOR decrypt as fallback when OpenSSL is not available.
	 *
	 * @since 1.1.0
	 * @param string $encrypted The base64 encoded encrypted value.
	 * @return string|false The decrypted value or false on failure.
	 */
	private function xor_decrypt( $encrypted ) {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$decoded = base64_decode( $encrypted, true );
		if ( false === $decoded ) {
			return false;
		}

		// Check for XOR prefix.
		if ( strpos( $decoded, 'xor:' ) === 0 ) {
			$decoded = substr( $decoded, 4 );
			$key     = defined( 'SECURE_AUTH_KEY' ) ? SECURE_AUTH_KEY : AUTH_KEY;
			$key     = str_repeat( $key, (int) ceil( strlen( $decoded ) / strlen( $key ) ) );
			return $decoded ^ substr( $key, 0, strlen( $decoded ) );
		}

		return false;
	}

	/**
	 * Securely store a sensitive value (encrypted in options table).
	 *
	 * @since 1.1.0
	 * @param string $option_name The option name.
	 * @param string $value       The value to store.
	 * @return bool True on success.
	 */
	public function store_encrypted_option( $option_name, $value ) {
		if ( empty( $value ) ) {
			delete_option( $option_name );
			delete_option( $option_name . '_encrypted' );
			return true;
		}

		$encrypted = $this->encrypt( $value );
		if ( false === $encrypted ) {
			return false;
		}

		// Store encrypted value, delete any plaintext version.
		delete_option( $option_name );
		return update_option( $option_name . '_encrypted', $encrypted, false );
	}

	/**
	 * Retrieve a securely stored encrypted value.
	 *
	 * @since 1.1.0
	 * @param string $option_name The option name.
	 * @return string The decrypted value or empty string.
	 */
	public function get_encrypted_option( $option_name ) {
		// Check for encrypted version first.
		$encrypted = get_option( $option_name . '_encrypted', '' );
		if ( ! empty( $encrypted ) ) {
			$decrypted = $this->decrypt( $encrypted );
			if ( false !== $decrypted ) {
				return $decrypted;
			}
		}

		// Fall back to plaintext option (for backwards compatibility).
		return get_option( $option_name, '' );
	}

	/**
	 * Verify AJAX request with nonce and capability.
	 *
	 * @since 1.1.0
	 * @param string $action     The nonce action (without prefix).
	 * @param string $nonce_name The name of the nonce field.
	 * @return void Dies on failure.
	 */
	public function verify_ajax_request( $action, $nonce_name = 'nonce' ) {
		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$nonce = isset( $_REQUEST[ $nonce_name ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $nonce_name ] ) ) : '';

		if ( ! $this->verify_nonce( $nonce, $action ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Security check failed.', 'third-audience' ) ),
				403
			);
		}

		// Verify capability.
		if ( ! $this->current_user_can_manage() ) {
			wp_send_json_error(
				array( 'message' => __( 'Permission denied.', 'third-audience' ) ),
				403
			);
		}
	}

	/**
	 * Generate a secure random token.
	 *
	 * @since 1.1.0
	 * @param int $length The token length in bytes.
	 * @return string Hexadecimal token string.
	 */
	public function generate_token( $length = 32 ) {
		if ( function_exists( 'random_bytes' ) ) {
			return bin2hex( random_bytes( $length ) );
		}
		return wp_generate_password( $length * 2, false );
	}

	/**
	 * Sanitize and validate SMTP settings.
	 *
	 * @since 1.1.0
	 * @param array $settings The settings array.
	 * @return array Sanitized settings.
	 */
	public function sanitize_smtp_settings( $settings ) {
		if ( ! is_array( $settings ) ) {
			return array();
		}

		$sanitized = array();

		if ( isset( $settings['host'] ) ) {
			$sanitized['host'] = $this->sanitize_text( $settings['host'] );
		}

		if ( isset( $settings['port'] ) ) {
			$sanitized['port'] = $this->sanitize_int( $settings['port'], 1, 65535 );
		}

		if ( isset( $settings['encryption'] ) ) {
			$sanitized['encryption'] = in_array( $settings['encryption'], array( '', 'ssl', 'tls' ), true )
				? $settings['encryption']
				: 'tls';
		}

		if ( isset( $settings['username'] ) ) {
			$sanitized['username'] = $this->sanitize_text( $settings['username'] );
		}

		if ( isset( $settings['password'] ) ) {
			$sanitized['password'] = $settings['password']; // Will be encrypted separately.
		}

		if ( isset( $settings['from_email'] ) ) {
			$sanitized['from_email'] = $this->sanitize_email( $settings['from_email'] );
		}

		if ( isset( $settings['from_name'] ) ) {
			$sanitized['from_name'] = $this->sanitize_text( $settings['from_name'] );
		}

		return $sanitized;
	}

	/**
	 * Sanitize notification settings.
	 *
	 * @since 1.1.0
	 * @param array $settings The settings array.
	 * @return array Sanitized settings.
	 */
	public function sanitize_notification_settings( $settings ) {
		if ( ! is_array( $settings ) ) {
			return array();
		}

		$sanitized = array();

		// Alert email addresses (comma-separated).
		if ( isset( $settings['alert_emails'] ) ) {
			$emails = array_map( 'trim', explode( ',', $settings['alert_emails'] ) );
			$valid  = array_filter( array_map( 'sanitize_email', $emails ) );
			$sanitized['alert_emails'] = implode( ', ', $valid );
		}

		// Boolean triggers.
		$bool_fields = array(
			'on_worker_failure',
			'on_high_error_rate',
			'on_cache_issues',
			'daily_digest',
		);
		foreach ( $bool_fields as $field ) {
			if ( isset( $settings[ $field ] ) ) {
				$sanitized[ $field ] = $this->sanitize_bool( $settings[ $field ] );
			}
		}

		// Error rate threshold (percentage).
		if ( isset( $settings['error_rate_threshold'] ) ) {
			$sanitized['error_rate_threshold'] = $this->sanitize_int( $settings['error_rate_threshold'], 1, 100 );
		}

		return $sanitized;
	}
}
