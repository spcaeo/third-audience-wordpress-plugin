<?php
/**
 * AI Citation Tracker - Tracks citation clicks from AI platforms.
 *
 * Lightweight tracker that detects when users click citations from AI platforms
 * like ChatGPT, Perplexity, Claude, and Gemini. Extracts search queries from
 * referrer URLs and logs to local database.
 *
 * @package ThirdAudience
 * @since   2.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_AI_Citation_Tracker
 *
 * Detects and tracks citation traffic from AI platforms.
 *
 * @since 2.2.0
 */
class TA_AI_Citation_Tracker {

	/**
	 * Known AI platform patterns.
	 *
	 * @var array
	 */
	private static $ai_platforms = array(
		// ChatGPT (OpenAI)
		'chat.openai.com'  => array(
			'name'        => 'ChatGPT',
			'query_param' => null,
			'color'       => '#10A37F',
		),
		'chatgpt.com'      => array(
			'name'        => 'ChatGPT Search',
			'query_param' => null, // Uses UTM parameters instead
			'color'       => '#10A37F',
		),

		// Perplexity (BEST for query extraction)
		'perplexity.ai'    => array(
			'name'        => 'Perplexity',
			'query_param' => 'q',
			'color'       => '#1FB6D0',
		),
		'www.perplexity.ai' => array(
			'name'        => 'Perplexity',
			'query_param' => 'q',
			'color'       => '#1FB6D0',
		),

		// Claude (Anthropic)
		'claude.ai'        => array(
			'name'        => 'Claude',
			'query_param' => null,
			'color'       => '#D97757',
		),

		// Gemini (Google)
		'gemini.google.com' => array(
			'name'        => 'Gemini',
			'query_param' => null,
			'color'       => '#4285F4',
		),
		'bard.google.com'  => array(
			'name'        => 'Bard (Gemini)',
			'query_param' => null,
			'color'       => '#4285F4',
		),

		// Microsoft Copilot
		'copilot.microsoft.com' => array(
			'name'        => 'Copilot',
			'query_param' => null,
			'color'       => '#00BCF2',
		),

		// Other AI platforms
		'you.com'          => array(
			'name'        => 'You.com',
			'query_param' => 'q',
			'color'       => '#8B5CF6',
		),
		'search.brave.com' => array(
			'name'        => 'Brave Search',
			'query_param' => 'q',
			'color'       => '#FB542B',
		),
	);

	/**
	 * Detect AI citation traffic from referrer header.
	 *
	 * @since 2.2.0
	 * @return array|false Citation data or false if not AI platform traffic.
	 */
	public static function detect_citation_traffic() {
		// Get referrer from HTTP headers.
		$referer = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : null;

		if ( empty( $referer ) ) {
			return false;
		}

		// Parse referrer URL.
		$parsed_url = wp_parse_url( $referer );
		if ( ! isset( $parsed_url['host'] ) ) {
			return false;
		}

		$host = strtolower( $parsed_url['host'] );

		// Check if referrer matches known AI platform.
		$platform_config = self::identify_ai_platform( $host );
		if ( ! $platform_config ) {
			return false;
		}

		// Extract search query if available.
		$search_query = self::extract_search_query( $parsed_url, $platform_config );

		// Determine source and medium (similar to Google Analytics).
		$source = $platform_config['name'];
		$medium = 'ai_citation';

		return array(
			'platform'       => $platform_config['name'],
			'platform_color' => $platform_config['color'],
			'referer'        => $referer,
			'search_query'   => $search_query,
			'source'         => $source,
			'medium'         => $medium,
			'traffic_type'   => 'citation_click',
		);
	}

	/**
	 * Identify AI platform from referrer host.
	 *
	 * @since 2.2.0
	 * @param string $host The referrer hostname.
	 * @return array|false Platform config or false if not recognized.
	 */
	private static function identify_ai_platform( $host ) {
		// Direct match.
		if ( isset( self::$ai_platforms[ $host ] ) ) {
			return self::$ai_platforms[ $host ];
		}

		// Check for subdomain matches (e.g., www.perplexity.ai).
		foreach ( self::$ai_platforms as $domain => $config ) {
			if ( strpos( $host, $domain ) !== false ) {
				return $config;
			}
		}

		return false;
	}

	/**
	 * Extract search query from referrer URL parameters.
	 *
	 * @since 2.2.0
	 * @param array $parsed_url Parsed referrer URL.
	 * @param array $platform_config Platform configuration.
	 * @return string|null Search query or null if not available.
	 */
	private static function extract_search_query( $parsed_url, $platform_config ) {
		// No query parameter defined for this platform.
		if ( empty( $platform_config['query_param'] ) ) {
			return null;
		}

		// No query string in referrer.
		if ( ! isset( $parsed_url['query'] ) ) {
			return null;
		}

		// Parse query string.
		parse_str( $parsed_url['query'], $params );

		// Extract query parameter.
		$query_param = $platform_config['query_param'];
		if ( isset( $params[ $query_param ] ) ) {
			return sanitize_text_field( wp_unslash( $params[ $query_param ] ) );
		}

		return null;
	}

	/**
	 * Get list of all tracked AI platforms.
	 *
	 * @since 2.2.0
	 * @return array List of platform names.
	 */
	public static function get_tracked_platforms() {
		$platforms = array();
		foreach ( self::$ai_platforms as $config ) {
			$platforms[] = $config['name'];
		}
		return array_unique( $platforms );
	}

	/**
	 * Get platform color for display.
	 *
	 * @since 2.2.0
	 * @param string $platform_name Platform name.
	 * @return string Hex color code.
	 */
	public static function get_platform_color( $platform_name ) {
		foreach ( self::$ai_platforms as $config ) {
			if ( $config['name'] === $platform_name ) {
				return $config['color'];
			}
		}
		return '#8B5CF6'; // Default purple.
	}
}
