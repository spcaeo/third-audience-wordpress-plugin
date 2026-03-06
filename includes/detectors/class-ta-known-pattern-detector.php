<?php
/**
 * Known Pattern Detector
 *
 * Detects bots by matching user agents against known patterns in the database.
 *
 * @package ThirdAudience
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Known_Pattern_Detector
 *
 * Queries the wp_ta_bot_patterns database table to match user agents
 * against known bot patterns.
 *
 * @since 2.3.0
 */
class TA_Known_Pattern_Detector {

	/**
	 * WordPress database object.
	 *
	 * @var wpdb|object
	 */
	private $wpdb;

	/**
	 * Constructor.
	 *
	 * @since 2.3.0
	 * @param wpdb|object $wpdb WordPress database object.
	 */
	public function __construct( $wpdb ) {
		$this->wpdb = $wpdb;
	}

	/**
	 * Detect if the user agent matches any known bot patterns.
	 *
	 * @since 2.3.0
	 * @param string $user_agent The user agent string to check.
	 * @return TA_Bot_Detection_Result Detection result with confidence score.
	 */
	public function detect( string $user_agent ): TA_Bot_Detection_Result {
		// Query the database for all bot patterns
		$table_name = $this->wpdb->prefix . 'ta_bot_patterns';
		$query      = "SELECT pattern, pattern_type, bot_name, bot_vendor, bot_category FROM {$table_name}";
		$patterns   = $this->wpdb->get_results( $query );

		// If no patterns found, return low confidence result
		if ( empty( $patterns ) ) {
			return new TA_Bot_Detection_Result(
				array(
					'is_bot'       => false,
					'confidence'   => 0.0,
					'method'       => 'database_pattern',
					'needs_review' => false,
				)
			);
		}

		// Check each pattern
		foreach ( $patterns as $pattern_data ) {
			$match = false;

			if ( 'exact' === $pattern_data->pattern_type ) {
				// Case-insensitive exact match (substring match)
				$match = stripos( $user_agent, $pattern_data->pattern ) !== false;
			} elseif ( 'regex' === $pattern_data->pattern_type ) {
				// Regex match with error suppression for invalid regex
				$match = @preg_match( $pattern_data->pattern, $user_agent ) === 1;
			}

			// If we found a match, return immediately with high confidence
			if ( $match ) {
				return new TA_Bot_Detection_Result(
					array(
						'is_bot'       => true,
						'confidence'   => 1.0,
						'bot_name'     => $pattern_data->bot_name,
						'bot_vendor'   => $pattern_data->bot_vendor,
						'bot_category' => $pattern_data->bot_category,
						'method'       => 'known_pattern',
						'indicators'   => array(
							'matched_pattern' => $pattern_data->pattern,
							'pattern_type'    => $pattern_data->pattern_type,
						),
						'needs_review' => false,
					)
				);
			}
		}

		// No match found - return low confidence result
		return new TA_Bot_Detection_Result(
			array(
				'is_bot'       => false,
				'confidence'   => 0.0,
				'method'       => 'database_pattern',
				'needs_review' => false,
			)
		);
	}
}
