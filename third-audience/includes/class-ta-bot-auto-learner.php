<?php
/**
 * Bot Auto-Learner - Automatically learns bot patterns from high-confidence unknown bots.
 *
 * Processes unknown bots with high heuristic confidence (>= 0.85) and automatically
 * creates bot patterns, enabling self-improving bot detection.
 *
 * @package ThirdAudience
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Bot_Auto_Learner
 *
 * Automatically creates bot patterns from high-confidence unknown bots.
 *
 * @since 2.3.0
 */
class TA_Bot_Auto_Learner {

	/**
	 * Confidence threshold for auto-learning.
	 *
	 * @var float
	 */
	const CONFIDENCE_THRESHOLD = 0.85;

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger|null
	 */
	private $logger;

	/**
	 * Singleton instance.
	 *
	 * @var TA_Bot_Auto_Learner|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 2.3.0
	 * @return TA_Bot_Auto_Learner
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
	 * @since 2.3.0
	 */
	public function __construct() {
		// Initialize logger if available.
		if ( class_exists( 'TA_Logger' ) ) {
			$this->logger = TA_Logger::get_instance();
		}
	}

	/**
	 * Initialize WP-Cron scheduling.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function init() {
		// Schedule daily cron job if not already scheduled.
		if ( ! wp_next_scheduled( 'ta_auto_learn_bots' ) ) {
			wp_schedule_event( time(), 'daily', 'ta_auto_learn_bots' );
		}

		// Hook into cron event.
		add_action( 'ta_auto_learn_bots', array( $this, 'process_pending_bots' ) );
	}

	/**
	 * Process pending unknown bots with high confidence.
	 *
	 * Queries wp_ta_unknown_bots for bots with:
	 * - classification_status = 'pending'
	 * - heuristic_bot_probability >= 0.85
	 *
	 * For each bot:
	 * 1. Check if pattern already exists (prevent duplicates)
	 * 2. Create new pattern in wp_ta_bot_patterns
	 * 3. Update classification_status to 'auto_classified'
	 *
	 * @since 2.3.0
	 * @return int Number of bots processed.
	 */
	public function process_pending_bots() {
		global $wpdb;

		$unknown_bots_table = $wpdb->prefix . 'ta_unknown_bots';
		$bot_patterns_table = $wpdb->prefix . 'ta_bot_patterns';

		// Query high-confidence unknown bots.
		$unknown_bots = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, user_agent, heuristic_bot_probability
				FROM {$unknown_bots_table}
				WHERE classification_status = 'pending'
				AND heuristic_bot_probability >= %f
				ORDER BY heuristic_bot_probability DESC
				LIMIT 100",
				self::CONFIDENCE_THRESHOLD
			)
		);

		if ( empty( $unknown_bots ) ) {
			if ( $this->logger ) {
				$this->logger->debug( 'No high-confidence unknown bots to process.' );
			}
			return 0;
		}

		$processed_count = 0;

		foreach ( $unknown_bots as $bot ) {
			// Generate pattern and bot name.
			$pattern  = $this->generate_pattern( $bot->user_agent );
			$bot_name = $this->extract_bot_name( $bot->user_agent );

			// Check if pattern already exists.
			$existing = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$bot_patterns_table} WHERE pattern = %s",
					$pattern
				)
			);

			if ( $existing > 0 ) {
				// Pattern already exists, mark as duplicate.
				$wpdb->update(
					$unknown_bots_table,
					array( 'classification_status' => 'duplicate_pattern' ),
					array( 'id' => $bot->id ),
					array( '%s' ),
					array( '%d' )
				);

				if ( $this->logger ) {
					$this->logger->debug( 'Duplicate pattern detected, skipping.', array(
						'bot_id'     => $bot->id,
						'user_agent' => $bot->user_agent,
						'pattern'    => $pattern,
					) );
				}

				continue;
			}

			// Create new bot pattern.
			$result = $wpdb->insert(
				$bot_patterns_table,
				array(
					'pattern'    => $pattern,
					'bot_name'   => $bot_name,
					'category'   => 'auto_learned',
					'confidence' => $bot->heuristic_bot_probability,
					'created_by' => 'auto_learner',
					'created_at' => current_time( 'mysql' ),
				),
				array( '%s', '%s', '%s', '%f', '%s', '%s' )
			);

			if ( false === $result ) {
				if ( $this->logger ) {
					$this->logger->error( 'Failed to create bot pattern.', array(
						'bot_id'     => $bot->id,
						'user_agent' => $bot->user_agent,
						'error'      => $wpdb->last_error,
					) );
				}
				continue;
			}

			// Update unknown bot status to auto_classified.
			$wpdb->update(
				$unknown_bots_table,
				array( 'classification_status' => 'auto_classified' ),
				array( 'id' => $bot->id ),
				array( '%s' ),
				array( '%d' )
			);

			$processed_count++;

			if ( $this->logger ) {
				$this->logger->info( 'Bot pattern auto-learned.', array(
					'bot_id'     => $bot->id,
					'bot_name'   => $bot_name,
					'pattern'    => $pattern,
					'confidence' => $bot->heuristic_bot_probability,
				) );
			}
		}

		if ( $this->logger ) {
			$this->logger->info( 'Auto-learning complete.', array(
				'processed' => $processed_count,
				'total'     => count( $unknown_bots ),
			) );
		}

		return $processed_count;
	}

	/**
	 * Generate regex pattern from user agent.
	 *
	 * Extracts the bot identifier and creates a case-insensitive regex pattern.
	 *
	 * Examples:
	 * - "TestBot/1.0" -> "/TestBot/i"
	 * - "Mozilla/5.0 (compatible; ExampleBot/2.0)" -> "/ExampleBot/i"
	 * - "CustomCrawler/3.5.2" -> "/CustomCrawler/i"
	 *
	 * @since 2.3.0
	 * @param string $user_agent The user agent string.
	 * @return string Regex pattern.
	 */
	public function generate_pattern( $user_agent ) {
		// Extract bot name (anything before "/" or ending with "Bot", "Crawler", "Spider").
		$bot_name = $this->extract_bot_name( $user_agent );

		// Escape special regex characters.
		$escaped = preg_quote( $bot_name, '/' );

		// Return case-insensitive pattern.
		return '/' . $escaped . '/i';
	}

	/**
	 * Extract bot name from user agent.
	 *
	 * Attempts to identify the bot name from the user agent string.
	 *
	 * Examples:
	 * - "TestBot/1.0" -> "TestBot"
	 * - "Mozilla/5.0 (compatible; ExampleBot/2.0)" -> "ExampleBot"
	 * - "CustomCrawler/3.5.2" -> "CustomCrawler"
	 *
	 * @since 2.3.0
	 * @param string $user_agent The user agent string.
	 * @return string Bot name.
	 */
	public function extract_bot_name( $user_agent ) {
		// Try to match: "BotName/version"
		if ( preg_match( '/([A-Za-z0-9_-]+(?:Bot|Crawler|Spider|bot|crawler|spider))\/[\d.]+/', $user_agent, $matches ) ) {
			return $matches[1];
		}

		// Try to match: "compatible; BotName/version"
		if ( preg_match( '/compatible;\s*([A-Za-z0-9_-]+)\//', $user_agent, $matches ) ) {
			return $matches[1];
		}

		// Try to match: any word before "/"
		if ( preg_match( '/([A-Za-z0-9_-]+)\//', $user_agent, $matches ) ) {
			return $matches[1];
		}

		// Fallback: use first word
		$parts = explode( ' ', $user_agent );
		return sanitize_text_field( $parts[0] );
	}

	/**
	 * Clear WP-Cron schedule on deactivation.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public static function deactivate() {
		$timestamp = wp_next_scheduled( 'ta_auto_learn_bots' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'ta_auto_learn_bots' );
		}
	}
}
