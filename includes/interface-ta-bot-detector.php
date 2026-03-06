<?php
/**
 * Bot Detector Interface
 *
 * Interface for bot detection implementations.
 *
 * @package ThirdAudience
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface TA_Bot_Detector
 *
 * Contract for bot detection implementations.
 *
 * @since 2.3.0
 */
interface TA_Bot_Detector {

	/**
	 * Detect if user agent is a bot.
	 *
	 * @since 2.3.0
	 * @param string $user_agent User agent string to analyze.
	 * @return TA_Bot_Detection_Result Detection result with confidence score.
	 */
	public function detect( string $user_agent ): TA_Bot_Detection_Result;
}
