<?php
/**
 * IP Verification Service - Verifies bot identity by IP address.
 *
 * Validates bot claims by checking if IP matches official bot IP ranges.
 * Catches masked bots like ChatGPT Atlas (browser UA but OpenAI IP).
 *
 * @package ThirdAudience
 * @since   2.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_IP_Verifier
 *
 * Verifies bot identity using IP ranges and reverse DNS.
 *
 * @since 2.7.0
 */
class TA_IP_Verifier {

	/**
	 * Known bot IP ranges.
	 *
	 * @var array
	 */
	private static $bot_ip_ranges = array(
		'GPTBot'            => array(
			'23.98.142.0/24',   // OpenAI.
			'40.84.180.0/22',   // OpenAI Azure.
			'13.66.11.96/28',   // OpenAI.
		),
		'ChatGPT-User'      => array(
			'23.98.142.0/24',   // OpenAI (shares with GPTBot).
			'40.84.180.0/22',   // OpenAI Azure.
		),
		'ClaudeBot'         => array(
			'3.128.0.0/9',      // Anthropic AWS US-East-2.
			'52.15.0.0/16',     // Anthropic AWS.
			'18.216.0.0/14',    // Anthropic AWS.
		),
		'PerplexityBot'     => array(
			'44.214.0.0/16',    // Perplexity AWS.
			'52.20.0.0/14',     // Perplexity AWS.
		),
		'GoogleBot'         => array(
			'66.249.64.0/19',   // Google.
			'66.102.0.0/20',    // Google.
		),
		'Google-Extended'   => array(
			'66.249.64.0/19',   // Google (shares with GoogleBot).
			'66.102.0.0/20',    // Google.
		),
		'Bytespider'        => array(
			'110.249.0.0/16',   // ByteDance.
			'111.225.0.0/16',   // ByteDance.
		),
		'FacebookBot'       => array(
			'69.63.176.0/20',   // Meta.
			'31.13.24.0/21',    // Meta.
			'66.220.144.0/20',  // Meta.
		),
		'Applebot-Extended' => array(
			'17.0.0.0/8',       // Apple.
		),
	);

	/**
	 * Reverse DNS hostname patterns.
	 *
	 * @var array
	 */
	private static $hostname_patterns = array(
		'GPTBot'            => 'openai.com',
		'ChatGPT-User'      => 'openai.com',
		'ClaudeBot'         => 'anthropic.com',
		'PerplexityBot'     => 'perplexity.ai',
		'GoogleBot'         => 'googlebot.com',
		'Google-Extended'   => 'google.com',
		'Bytespider'        => 'bytedance.com',
		'FacebookBot'       => 'facebook.com',
		'Applebot-Extended' => 'apple.com',
		'anthropic-ai'      => 'anthropic.com',
		'cohere-ai'         => 'cohere.ai',
	);

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Singleton instance.
	 *
	 * @var TA_IP_Verifier|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 2.7.0
	 * @return TA_IP_Verifier
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
	 * @since 2.7.0
	 */
	private function __construct() {
		$this->logger = TA_Logger::get_instance();
	}

	/**
	 * Verify bot IP address.
	 *
	 * Checks if IP matches official bot IP ranges and validates via reverse DNS.
	 *
	 * @since 2.7.0
	 * @param string $bot_type   Bot type (e.g., 'GPTBot', 'ClaudeBot').
	 * @param string $ip_address IP address to verify.
	 * @return array Verification result with 'verified' (bool) and 'method' (string).
	 */
	public function verify_bot_ip( $bot_type, $ip_address ) {
		// Check if IP verification is enabled.
		if ( ! get_option( 'ta_enable_ip_verification', true ) ) {
			return array(
				'verified' => null,
				'method'   => null,
			);
		}

		// Validate IP format.
		if ( ! filter_var( $ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 ) ) {
			$this->logger->debug( 'Invalid IP address format for verification.', array(
				'ip'       => $ip_address,
				'bot_type' => $bot_type,
			) );
			return array(
				'verified' => false,
				'method'   => null,
			);
		}

		// Check cache first (cache for 24 hours).
		$cache_key = 'ta_ip_verify_' . md5( $bot_type . '|' . $ip_address );
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		// Method 1: IP range check (fast, no network call).
		$range_result = $this->verify_by_ip_range( $bot_type, $ip_address );
		if ( $range_result ) {
			$result = array(
				'verified' => true,
				'method'   => 'ip_range',
			);
			set_transient( $cache_key, $result, DAY_IN_SECONDS );
			return $result;
		}

		// Method 2: Reverse DNS lookup (slower, requires network call).
		$hostname = gethostbyaddr( $ip_address );
		if ( $hostname !== $ip_address && $this->verify_by_hostname( $bot_type, $hostname ) ) {
			// Forward DNS confirmation (verify hostname resolves back to IP).
			$forward_ips = gethostbynamel( $hostname );
			if ( is_array( $forward_ips ) && in_array( $ip_address, $forward_ips, true ) ) {
				$result = array(
					'verified' => true,
					'method'   => 'reverse_dns',
				);
				set_transient( $cache_key, $result, DAY_IN_SECONDS );

				$this->logger->debug( 'Bot IP verified via reverse DNS.', array(
					'bot_type' => $bot_type,
					'ip'       => $ip_address,
					'hostname' => $hostname,
				) );

				return $result;
			}
		}

		// Verification failed.
		$result = array(
			'verified' => false,
			'method'   => null,
		);
		set_transient( $cache_key, $result, HOUR_IN_SECONDS * 6 ); // Cache failures shorter.

		$this->logger->warning( 'Bot IP verification failed.', array(
			'bot_type' => $bot_type,
			'ip'       => $ip_address,
			'hostname' => $hostname ?? 'N/A',
		) );

		return $result;
	}

	/**
	 * Verify by IP range.
	 *
	 * Checks if IP is within known bot IP ranges.
	 *
	 * @since 2.7.0
	 * @param string $bot_type   Bot type.
	 * @param string $ip_address IP address.
	 * @return bool True if IP is in range, false otherwise.
	 */
	private function verify_by_ip_range( $bot_type, $ip_address ) {
		if ( ! isset( self::$bot_ip_ranges[ $bot_type ] ) ) {
			return false;
		}

		foreach ( self::$bot_ip_ranges[ $bot_type ] as $cidr_range ) {
			if ( $this->ip_in_range( $ip_address, $cidr_range ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Verify by reverse DNS hostname.
	 *
	 * Checks if hostname matches expected pattern for bot.
	 *
	 * @since 2.7.0
	 * @param string $bot_type Bot type.
	 * @param string $hostname Hostname from reverse DNS.
	 * @return bool True if hostname matches, false otherwise.
	 */
	private function verify_by_hostname( $bot_type, $hostname ) {
		if ( ! isset( self::$hostname_patterns[ $bot_type ] ) ) {
			return false;
		}

		$pattern = self::$hostname_patterns[ $bot_type ];
		return strpos( $hostname, $pattern ) !== false;
	}

	/**
	 * Check if IP is within CIDR range.
	 *
	 * @since 2.7.0
	 * @param string $ip   IP address to check.
	 * @param string $cidr CIDR range (e.g., '10.0.0.0/8').
	 * @return bool True if IP is in range, false otherwise.
	 */
	private function ip_in_range( $ip, $cidr ) {
		// Parse CIDR.
		list( $subnet, $mask ) = explode( '/', $cidr );

		// Convert IPs to long format.
		$ip_long     = ip2long( $ip );
		$subnet_long = ip2long( $subnet );

		// Invalid format.
		if ( false === $ip_long || false === $subnet_long ) {
			return false;
		}

		// Calculate network mask.
		$mask_long = -1 << ( 32 - (int) $mask );

		// Check if IP is in subnet.
		return ( $ip_long & $mask_long ) === ( $subnet_long & $mask_long );
	}

	/**
	 * Get bot IP ranges.
	 *
	 * Returns all known bot IP ranges (for admin display).
	 *
	 * @since 2.7.0
	 * @return array Bot IP ranges.
	 */
	public static function get_bot_ip_ranges() {
		return self::$bot_ip_ranges;
	}

	/**
	 * Add custom IP range for bot.
	 *
	 * Allows admins to add custom IP ranges via settings.
	 *
	 * @since 2.7.0
	 * @param string $bot_type Bot type.
	 * @param string $cidr_range CIDR range to add.
	 * @return bool True on success, false on failure.
	 */
	public function add_custom_ip_range( $bot_type, $cidr_range ) {
		$custom_ranges = get_option( 'ta_custom_bot_ip_ranges', array() );

		if ( ! isset( $custom_ranges[ $bot_type ] ) ) {
			$custom_ranges[ $bot_type ] = array();
		}

		if ( ! in_array( $cidr_range, $custom_ranges[ $bot_type ], true ) ) {
			$custom_ranges[ $bot_type ][] = $cidr_range;
			update_option( 'ta_custom_bot_ip_ranges', $custom_ranges );

			$this->logger->info( 'Custom IP range added.', array(
				'bot_type' => $bot_type,
				'range'    => $cidr_range,
			) );

			return true;
		}

		return false;
	}

	/**
	 * Get custom IP ranges.
	 *
	 * @since 2.7.0
	 * @return array Custom IP ranges by bot type.
	 */
	public function get_custom_ip_ranges() {
		return get_option( 'ta_custom_bot_ip_ranges', array() );
	}
}
