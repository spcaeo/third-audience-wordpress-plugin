<?php
/**
 * Heuristic Bot Detector
 *
 * Detects bots using pattern matching and heuristic analysis of user agent strings.
 *
 * @package ThirdAudience
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Heuristic_Detector
 *
 * Uses pattern matching to auto-detect bots from user agent strings.
 * Analyzes various indicators like keywords, version patterns, and documentation URLs.
 *
 * @since 2.3.0
 */
class TA_Heuristic_Detector {

	/**
	 * Bot keywords to detect in user agents.
	 *
	 * @var array
	 */
	private $bot_keywords = array( 'bot', 'crawler', 'spider', 'scraper' );

	/**
	 * Detect if user agent is a bot using heuristic analysis.
	 *
	 * @since 2.3.0
	 * @param string $user_agent User agent string to analyze.
	 * @return TA_Bot_Detection_Result Detection result with confidence score.
	 */
	public function detect( string $user_agent ): TA_Bot_Detection_Result {
		// Handle empty user agent.
		if ( empty( $user_agent ) ) {
			return new TA_Bot_Detection_Result(
				array(
					'is_bot'     => false,
					'confidence' => 0.0,
					'bot_name'   => null,
					'method'     => 'heuristic',
					'indicators' => array(),
				)
			);
		}

		$indicators    = array();
		$bot_name      = null;
		$user_agent_lc = strtolower( $user_agent );

		// Check for "compatible" pattern: Mozilla/5.0 (compatible; BotName/version).
		if ( preg_match( '/\(compatible;\s*([^\/;]+)\/[\d\.]+/i', $user_agent, $matches ) ) {
			$indicators[] = 'compatible_pattern';
			$bot_name     = trim( $matches[1] );
		}

		// Check for documentation URL pattern: +http or +https.
		if ( preg_match( '/\(\+https?:\/\/[^\)]+\)|\s+\(\+https?:\/\/[^\)]+\)/i', $user_agent ) ) {
			$indicators[] = 'documentation_url';
		}

		// Alternative documentation URL pattern without parentheses.
		if ( preg_match( '/\+https?:\/\/\S+/i', $user_agent ) ) {
			if ( ! in_array( 'documentation_url', $indicators, true ) ) {
				$indicators[] = 'documentation_url';
			}
		}

		// Check for bot keywords first.
		$has_bot_keyword = false;
		foreach ( $this->bot_keywords as $keyword ) {
			if ( strpos( $user_agent_lc, $keyword ) !== false ) {
				$indicators[]    = 'keyword_' . $keyword;
				$has_bot_keyword = true;

				// Try to extract bot name around the keyword if not already set.
				if ( null === $bot_name ) {
					if ( preg_match( '/([A-Za-z0-9_\-]*' . $keyword . '[A-Za-z0-9_\-]*)\/[\d\.]+/i', $user_agent, $matches ) ) {
						$bot_name = trim( $matches[1] );
					} elseif ( preg_match( '/([A-Za-z0-9_\-]*' . $keyword . '[A-Za-z0-9_\-]*)/i', $user_agent, $matches ) ) {
						// Fallback: just the keyword match.
						$candidate = trim( $matches[1] );
						// Only use if it's a reasonable length (not just "bot").
						if ( strlen( $candidate ) > 3 ) {
							$bot_name = $candidate;
						}
					}
				}
			}
		}

		// Check for version number pattern: BotName/x.y.z.
		if ( preg_match( '/([A-Za-z0-9_\-]+)\/[\d\.]+(?:\-[a-z]+)?/i', $user_agent, $matches ) ) {
			$indicators[] = 'version_pattern';

			// Extract bot name if not already extracted.
			if ( null === $bot_name ) {
				$potential_name = trim( $matches[1] );
				// Use as bot name if it has a bot keyword, documentation URL, or if this looks like a bot name.
				if ( $has_bot_keyword || in_array( 'documentation_url', $indicators, true ) || $this->contains_bot_keyword( $potential_name ) ) {
					$bot_name = $potential_name;
				}
			}
		}

		// Calculate confidence based on number of indicators.
		$confidence = $this->calculate_confidence( $indicators, $user_agent );

		// Determine if this is a bot based on confidence threshold.
		$is_bot = $confidence >= 0.5;

		return new TA_Bot_Detection_Result(
			array(
				'is_bot'     => $is_bot,
				'confidence' => $confidence,
				'bot_name'   => $bot_name,
				'method'     => 'heuristic',
				'indicators' => $indicators,
			)
		);
	}

	/**
	 * Calculate confidence score based on indicators found.
	 *
	 * @since 2.3.0
	 * @param array  $indicators List of detected indicators.
	 * @param string $user_agent Original user agent string.
	 * @return float Confidence score between 0.0 and 1.0.
	 */
	private function calculate_confidence( array $indicators, string $user_agent ): float {
		if ( empty( $indicators ) ) {
			return 0.0;
		}

		$score = 0.0;

		// High value indicators.
		$has_compatible = in_array( 'compatible_pattern', $indicators, true );
		$has_doc_url    = in_array( 'documentation_url', $indicators, true );
		$has_version    = in_array( 'version_pattern', $indicators, true );

		if ( $has_compatible ) {
			$score += 0.4;
		}

		if ( $has_doc_url ) {
			$score += 0.4;
		}

		// Version pattern is medium value.
		if ( $has_version ) {
			$score += 0.3;
		}

		// Bot keywords.
		$keyword_count = 0;
		foreach ( $this->bot_keywords as $keyword ) {
			if ( in_array( 'keyword_' . $keyword, $indicators, true ) ) {
				$keyword_count++;
			}
		}

		if ( $keyword_count > 0 ) {
			// Base score for having keywords.
			$score += 0.35;
			// Bonus for multiple keywords.
			if ( $keyword_count > 1 ) {
				$score += 0.15 * min( $keyword_count - 1, 2 );
			}
		}

		// Small bonus: keyword + version pattern together (but keep total below 0.7 without other indicators).
		if ( $keyword_count > 0 && $has_version && ! $has_doc_url && ! $has_compatible ) {
			$score += 0.03;
		} elseif ( $keyword_count > 0 && $has_version ) {
			$score += 0.1;
		}

		// Cap at 1.0.
		return min( $score, 1.0 );
	}

	/**
	 * Check if a string contains any bot keyword.
	 *
	 * @since 2.3.0
	 * @param string $string String to check.
	 * @return bool True if contains bot keyword.
	 */
	private function contains_bot_keyword( string $string ): bool {
		$string_lc = strtolower( $string );
		foreach ( $this->bot_keywords as $keyword ) {
			if ( strpos( $string_lc, $keyword ) !== false ) {
				return true;
			}
		}
		return false;
	}
}
