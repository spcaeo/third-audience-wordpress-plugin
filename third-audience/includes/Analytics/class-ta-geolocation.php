<?php
/**
 * Geolocation - IP address utilities for bot analytics.
 *
 * Handles IP address extraction, validation, and geolocation lookup.
 *
 * @package ThirdAudience
 * @since   3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Geolocation
 *
 * Provides IP address utilities including geolocation lookup.
 *
 * @since 3.3.1
 */
class TA_Geolocation {

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Singleton instance.
	 *
	 * @var TA_Geolocation|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 3.3.1
	 * @return TA_Geolocation
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
		$this->logger = TA_Logger::get_instance();
	}

	/**
	 * Get geolocation (country code) from IP address.
	 *
	 * Uses ip-api.com free service with 24-hour caching.
	 *
	 * @since 1.4.0
	 * @param string $ip The IP address to lookup.
	 * @return string|null Country code or null on failure.
	 */
	public function get_geolocation( $ip ) {
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
	public function is_private_ip( $ip ) {
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
	 * Get client IP address from request headers.
	 *
	 * Handles Cloudflare, X-Forwarded-For, and direct connections.
	 *
	 * @since 1.4.0
	 * @return string|null Client IP or null.
	 */
	public function get_client_ip() {
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
	 * Get client IP address for bot tracking.
	 *
	 * Similar to get_client_ip but returns 'unknown' instead of null.
	 *
	 * @since 3.2.0
	 * @return string IP address or 'unknown'.
	 */
	public function get_bot_client_ip() {
		$ip = '';

		// Check for proxy headers.
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			$ip  = trim( $ips[0] );
		} elseif ( ! empty( $_SERVER['HTTP_X_REAL_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) );
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return $ip ?: 'unknown';
	}
}
