<?php
/**
 * Bot Detection Pipeline
 *
 * Orchestrates multiple detection layers in order (fast to slow).
 *
 * @package ThirdAudience
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Bot_Detection_Pipeline
 *
 * Multi-layer bot detection with early exit optimization.
 *
 * Detection layers in order:
 * 1. Known Pattern Detector (database lookup - fastest)
 * 2. Heuristic Detector (pattern matching - slower)
 *
 * Pipeline returns as soon as it gets a confident result (>= 0.7 confidence).
 * If no confident result, queues unknown bot to wp_ta_unknown_bots for learning.
 *
 * @since 2.3.0
 */
class TA_Bot_Detection_Pipeline {

	/**
	 * Known pattern detector.
	 *
	 * @var TA_Known_Pattern_Detector
	 */
	private $known_detector;

	/**
	 * Heuristic detector.
	 *
	 * @var TA_Heuristic_Detector
	 */
	private $heuristic_detector;

	/**
	 * Confidence threshold for early exit.
	 *
	 * @var float
	 */
	private const CONFIDENCE_THRESHOLD = 0.7;

	/**
	 * Constructor.
	 *
	 * @since 2.3.0
	 * @param TA_Known_Pattern_Detector $known_detector Known pattern detector.
	 * @param TA_Heuristic_Detector     $heuristic_detector Heuristic detector.
	 */
	public function __construct(
		TA_Known_Pattern_Detector $known_detector,
		TA_Heuristic_Detector $heuristic_detector
	) {
		$this->known_detector     = $known_detector;
		$this->heuristic_detector = $heuristic_detector;
	}

	/**
	 * Detect if user agent is a bot using multi-layer pipeline.
	 *
	 * Executes detection layers in order (fast to slow) and returns
	 * as soon as a confident result is found.
	 *
	 * @since 2.3.0
	 * @param string $user_agent User agent string to analyze.
	 * @return TA_Bot_Detection_Result Detection result with confidence score.
	 */
	public function detect( string $user_agent ): TA_Bot_Detection_Result {
		// Layer 1: Known Pattern Detector (fastest)
		$result = $this->known_detector->detect( $user_agent );

		// Fast path: return confident result immediately
		if ( $result->is_confident() ) {
			return $result;
		}

		// Layer 2: Heuristic Detector (slower but broader)
		$heuristic_result = $this->heuristic_detector->detect( $user_agent );

		// Use better result (higher confidence)
		if ( $heuristic_result->get_confidence() > $result->get_confidence() ) {
			$result = $heuristic_result;
		}

		// Queue unknown bots for learning if not confident
		if ( ! $result->is_confident() ) {
			$this->queue_unknown_bot( $user_agent, $result );
		}

		return $result;
	}

	/**
	 * Queue unknown bot to database for later learning.
	 *
	 * @since 2.3.0
	 * @param string                   $user_agent User agent string.
	 * @param TA_Bot_Detection_Result $result Detection result.
	 */
	private function queue_unknown_bot( string $user_agent, TA_Bot_Detection_Result $result ): void {
		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix . 'ta_unknown_bots',
			array(
				'user_agent'   => $user_agent,
				'is_bot'       => $result->is_bot() ? 1 : 0,
				'confidence'   => $result->get_confidence(),
				'method'       => $result->get_method(),
				'bot_name'     => $result->get_bot_name(),
				'bot_vendor'   => $result->get_bot_vendor(),
				'bot_category' => $result->get_bot_category(),
				'indicators'   => maybe_serialize( $result->get_indicators() ),
				'detected_at'  => current_time( 'mysql' ),
			),
			array(
				'%s',
				'%d',
				'%f',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);
	}
}
