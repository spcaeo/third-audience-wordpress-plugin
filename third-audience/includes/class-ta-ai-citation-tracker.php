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

		// Google AI Overview (heuristic detection)
		'www.google.com'   => array(
			'name'        => 'Google AI Overview',
			'query_param' => 'q',
			'color'       => '#4285F4',
			'heuristic'   => true, // Requires pattern matching
		),
		'google.com'       => array(
			'name'        => 'Google AI Overview',
			'query_param' => 'q',
			'color'       => '#4285F4',
			'heuristic'   => true,
		),

		// Microsoft Copilot
		'copilot.microsoft.com' => array(
			'name'        => 'Copilot',
			'query_param' => null,
			'color'       => '#00BCF2',
		),
		'www.bing.com'     => array(
			'name'        => 'Bing AI',
			'query_param' => 'q',
			'color'       => '#008373',
			'heuristic'   => true, // Bing Copilot
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
		'kagi.com'         => array(
			'name'        => 'Kagi',
			'query_param' => 'q',
			'color'       => '#FF6C5C',
		),
		'neeva.com'        => array(
			'name'        => 'Neeva',
			'query_param' => 'q',
			'color'       => '#7C3AED',
		),
	);

	/**
	 * Detect AI citation traffic from UTM parameters or referrer header.
	 *
	 * Enhanced detection with support for Google AI Overview and Bing AI
	 * using heuristic pattern matching. Includes confidence scoring.
	 *
	 * @since 2.5.0 Enhanced with heuristic detection and confidence scoring
	 * @since 2.2.0
	 * @return array|false Citation data or false if not AI platform traffic.
	 */
	public static function detect_citation_traffic() {
		$platform_config = null;
		$search_query    = null;
		$detection_method = null;
		$confidence_score = 1.0; // Default: high confidence

		// METHOD 1: Check UTM parameters (ChatGPT's method since June 2025).
		$utm_source = isset( $_GET['utm_source'] ) ? sanitize_text_field( wp_unslash( $_GET['utm_source'] ) ) : null;
		$utm_medium = isset( $_GET['utm_medium'] ) ? sanitize_text_field( wp_unslash( $_GET['utm_medium'] ) ) : null;

		if ( $utm_source ) {
			// ChatGPT uses utm_source=chatgpt.com.
			if ( strpos( $utm_source, 'chatgpt' ) !== false ) {
				$platform_config = array(
					'name'  => 'ChatGPT',
					'color' => '#10A37F',
				);
				$detection_method = 'utm_parameter';
				$confidence_score = 1.0; // High confidence
			}
			// Perplexity might use utm_source=perplexity.ai.
			elseif ( strpos( $utm_source, 'perplexity' ) !== false ) {
				$platform_config = array(
					'name'  => 'Perplexity',
					'color' => '#1FB6D0',
				);
				$detection_method = 'utm_parameter';
				$confidence_score = 1.0;
			}
			// Gemini might use utm_source=gemini.
			elseif ( strpos( $utm_source, 'gemini' ) !== false ) {
				$platform_config = array(
					'name'  => 'Gemini',
					'color' => '#4285F4',
				);
				$detection_method = 'utm_parameter';
				$confidence_score = 1.0;
			}
			// Claude might use utm_source=claude.
			elseif ( strpos( $utm_source, 'claude' ) !== false ) {
				$platform_config = array(
					'name'  => 'Claude',
					'color' => '#D97757',
				);
				$detection_method = 'utm_parameter';
				$confidence_score = 1.0;
			}
		}

		// METHOD 2: Fallback to HTTP_REFERER if no UTM detected.
		if ( ! $platform_config ) {
			$referer = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : null;

			if ( empty( $referer ) ) {
				return false; // No UTM, no referer = not AI traffic.
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

			// Extract search query if available from referer.
			$search_query = self::extract_search_query( $parsed_url, $platform_config );
			$detection_method = 'http_referer';

			// HEURISTIC DETECTION: For Google/Bing, use pattern matching.
			if ( ! empty( $platform_config['heuristic'] ) ) {
				$heuristic_result = self::apply_heuristics( $parsed_url, $search_query, $platform_config );

				if ( ! $heuristic_result['is_ai_traffic'] ) {
					return false; // Heuristics determined this is NOT AI traffic
				}

				$confidence_score = $heuristic_result['confidence'];
				$detection_method = 'heuristic_' . $detection_method;
			} else {
				// Direct platform match (Perplexity, Claude, etc.)
				$confidence_score = 0.95; // Very high confidence
			}
		}

		// Determine source and medium (similar to Google Analytics).
		$source = $platform_config['name'];
		$medium = 'ai_citation';

		// Get referer if available (may be null for UTM-only detection).
		$referer = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : null;

		return array(
			'platform'         => $platform_config['name'],
			'platform_color'   => $platform_config['color'],
			'referer'          => $referer,
			'search_query'     => $search_query,
			'source'           => $source,
			'medium'           => $medium,
			'traffic_type'     => 'citation_click',
			'detection_method' => $detection_method,
			'confidence_score' => $confidence_score, // 0.0 to 1.0
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

		// PERPLEXITY: Handle new slug-based URL format (/search/query-slug-id).
		if ( 'Perplexity' === $platform_config['name'] && ! empty( $parsed_url['path'] ) ) {
			// New format: /search/web-development-services-monoc-rdP_eGvpQUaIARh1XyKBvw
			if ( preg_match( '#^/search/(.+)$#', $parsed_url['path'], $matches ) ) {
				$slug = $matches[1];

				// Remove the ID part (everything after the last dash that looks like an ID).
				// IDs are typically: rdP_eGvpQUaIARh1XyKBvw (base64-like strings).
				$slug = preg_replace( '/-[a-zA-Z0-9_-]{15,}$/', '', $slug );

				// Convert slug to readable query: web-development-services -> web development services.
				$query = str_replace( array( '-', '_' ), ' ', $slug );
				$query = trim( $query );

				if ( ! empty( $query ) ) {
					return sanitize_text_field( $query );
				}
			}
		}

		// LEGACY: Try standard ?q= parameter (old Perplexity format, Google, Bing).
		if ( isset( $parsed_url['query'] ) ) {
			parse_str( $parsed_url['query'], $params );

			$query_param = $platform_config['query_param'];
			if ( isset( $params[ $query_param ] ) ) {
				return sanitize_text_field( wp_unslash( $params[ $query_param ] ) );
			}
		}

		return null;
	}

	/**
	 * Apply heuristic patterns to identify AI Overview traffic.
	 *
	 * Uses query patterns and URL characteristics to distinguish
	 * Google AI Overview / Bing Copilot from regular search traffic.
	 *
	 * @since 2.5.0
	 * @param array  $parsed_url Parsed referrer URL.
	 * @param string $search_query Extracted search query.
	 * @param array  $platform_config Platform configuration.
	 * @return array Result with 'is_ai_traffic' boolean and 'confidence' score.
	 */
	private static function apply_heuristics( $parsed_url, $search_query, $platform_config ) {
		$host = strtolower( $parsed_url['host'] );

		// GOOGLE AI OVERVIEW HEURISTICS
		if ( strpos( $host, 'google.com' ) !== false ) {
			return self::detect_google_ai_overview( $parsed_url, $search_query );
		}

		// BING AI HEURISTICS
		if ( strpos( $host, 'bing.com' ) !== false ) {
			return self::detect_bing_copilot( $parsed_url, $search_query );
		}

		// Default: treat as potential AI traffic with low confidence
		return array(
			'is_ai_traffic' => true,
			'confidence'    => 0.3,
		);
	}

	/**
	 * Detect Google AI Overview traffic using heuristic patterns.
	 *
	 * AI Overview typically appears for:
	 * - Informational queries (how, what, why, when)
	 * - Complex/detailed questions (multiple words)
	 * - Queries with conversational language
	 *
	 * @since 2.5.0
	 * @param array  $parsed_url Parsed referrer URL.
	 * @param string $search_query Search query string.
	 * @return array Result with 'is_ai_traffic' and 'confidence'.
	 */
	private static function detect_google_ai_overview( $parsed_url, $search_query ) {
		// If no query, we can't determine AI Overview vs regular search
		if ( empty( $search_query ) ) {
			return array(
				'is_ai_traffic' => false,
				'confidence'    => 0.0,
			);
		}

		$confidence = 0.0;
		$query_lower = strtolower( $search_query );
		$word_count = str_word_count( $query_lower );

		// PATTERN 1: Informational query indicators (high confidence)
		$informational_patterns = array(
			'how to', 'how do', 'how can', 'how does', 'how is',
			'what is', 'what are', 'what does', 'what can',
			'why is', 'why do', 'why does', 'why are',
			'when is', 'when do', 'when should',
			'where is', 'where can', 'where do',
			'can you tell me', 'explain', 'tutorial',
			'guide to', 'introduction to', 'overview of',
			'best practices', 'comparison between',
		);

		foreach ( $informational_patterns as $pattern ) {
			if ( strpos( $query_lower, $pattern ) !== false ) {
				$confidence += 0.4;
				break;
			}
		}

		// PATTERN 2: Query complexity (more words = higher likelihood)
		if ( $word_count >= 7 ) {
			$confidence += 0.3; // Very detailed query
		} elseif ( $word_count >= 5 ) {
			$confidence += 0.2; // Moderate detail
		} elseif ( $word_count >= 3 ) {
			$confidence += 0.1; // Basic detail
		}

		// PATTERN 3: Conversational/question structure
		if ( preg_match( '/\?$/', $query_lower ) ) {
			$confidence += 0.2; // Ends with question mark
		}

		// PATTERN 4: Technical/informational keywords
		$technical_keywords = array(
			'development', 'programming', 'framework', 'services',
			'optimization', 'portfolio', 'company', 'about',
			'integration', 'implementation', 'solution',
		);

		foreach ( $technical_keywords as $keyword ) {
			if ( strpos( $query_lower, $keyword ) !== false ) {
				$confidence += 0.1;
				break;
			}
		}

		// Cap confidence at 0.85 (heuristic can never be 100% certain)
		$confidence = min( $confidence, 0.85 );

		// Only consider it AI Overview if confidence is >= 0.4
		$is_ai_traffic = $confidence >= 0.4;

		return array(
			'is_ai_traffic' => $is_ai_traffic,
			'confidence'    => $confidence,
		);
	}

	/**
	 * Detect Bing Copilot traffic using heuristic patterns.
	 *
	 * @since 2.5.0
	 * @param array  $parsed_url Parsed referrer URL.
	 * @param string $search_query Search query string.
	 * @return array Result with 'is_ai_traffic' and 'confidence'.
	 */
	private static function detect_bing_copilot( $parsed_url, $search_query ) {
		// Similar patterns to Google AI Overview
		// Bing Copilot appears in chat mode or for conversational queries

		if ( empty( $search_query ) ) {
			return array(
				'is_ai_traffic' => false,
				'confidence'    => 0.0,
			);
		}

		// Check for Copilot-specific URL parameters
		$query_params = array();
		if ( isset( $parsed_url['query'] ) ) {
			parse_str( $parsed_url['query'], $query_params );
		}

		// Bing Copilot uses specific URL params like &form=MA13FV (Copilot form)
		if ( isset( $query_params['form'] ) && strpos( $query_params['form'], 'MA' ) !== false ) {
			return array(
				'is_ai_traffic' => true,
				'confidence'    => 0.9, // High confidence with form param
			);
		}

		// Fallback to query pattern analysis (same as Google)
		$query_lower = strtolower( $search_query );
		$confidence = 0.0;

		if ( strpos( $query_lower, 'how' ) !== false ||
		     strpos( $query_lower, 'what' ) !== false ||
		     strpos( $query_lower, 'why' ) !== false ) {
			$confidence += 0.4;
		}

		if ( str_word_count( $query_lower ) >= 5 ) {
			$confidence += 0.2;
		}

		$confidence = min( $confidence, 0.75 );
		$is_ai_traffic = $confidence >= 0.4;

		return array(
			'is_ai_traffic' => $is_ai_traffic,
			'confidence'    => $confidence,
		);
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
