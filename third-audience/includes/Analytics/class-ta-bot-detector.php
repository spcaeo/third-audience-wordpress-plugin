<?php
/**
 * Bot Detector - Identifies and classifies AI bots from user agents.
 *
 * Handles bot detection using patterns, pipeline detection, and custom patterns.
 *
 * @package ThirdAudience
 * @since   3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Bot_Detector
 *
 * Detects and classifies AI bots from user agent strings.
 *
 * @since 3.3.1
 */
class TA_Bot_Detector {

	/**
	 * Known AI bot user agents.
	 *
	 * @var array
	 */
	private static $known_bots = array(
		'ClaudeBot'         => array(
			'pattern'  => '/ClaudeBot/i',
			'name'     => 'Claude (Anthropic)',
			'color'    => '#D97757',
			'priority' => 'high',
		),
		'GPTBot'            => array(
			'pattern'  => '/GPTBot/i',
			'name'     => 'GPT (OpenAI)',
			'color'    => '#10A37F',
			'priority' => 'high',
		),
		'ChatGPT-User'      => array(
			'pattern'  => '/ChatGPT-User/i',
			'name'     => 'ChatGPT User',
			'color'    => '#10A37F',
			'priority' => 'high',
		),
		'PerplexityBot'     => array(
			'pattern'  => '/PerplexityBot/i',
			'name'     => 'Perplexity',
			'color'    => '#1FB6D0',
			'priority' => 'high',
		),
		'Bytespider'        => array(
			'pattern'  => '/Bytespider/i',
			'name'     => 'ByteDance AI',
			'color'    => '#FF4458',
			'priority' => 'medium',
		),
		'anthropic-ai'      => array(
			'pattern'  => '/anthropic-ai/i',
			'name'     => 'Anthropic AI',
			'color'    => '#D97757',
			'priority' => 'high',
		),
		'cohere-ai'         => array(
			'pattern'  => '/cohere-ai/i',
			'name'     => 'Cohere',
			'color'    => '#39594D',
			'priority' => 'medium',
		),
		'Google-Extended'   => array(
			'pattern'  => '/Google-Extended/i',
			'name'     => 'Google Gemini',
			'color'    => '#4285F4',
			'priority' => 'medium',
		),
		'FacebookBot'       => array(
			'pattern'  => '/FacebookBot/i',
			'name'     => 'Meta AI',
			'color'    => '#1877F2',
			'priority' => 'medium',
		),
		'Applebot-Extended' => array(
			'pattern'  => '/Applebot-Extended/i',
			'name'     => 'Apple Intelligence',
			'color'    => '#000000',
			'priority' => 'medium',
		),
	);

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Detection pipeline instance.
	 *
	 * @var TA_Bot_Detection_Pipeline|null
	 */
	private $pipeline = null;

	/**
	 * Singleton instance.
	 *
	 * @var TA_Bot_Detector|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 3.3.1
	 * @return TA_Bot_Detector
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
		global $wpdb;

		$this->logger = TA_Logger::get_instance();

		// Initialize detection pipeline if classes exist.
		if ( class_exists( 'TA_Bot_Detection_Pipeline' ) &&
		     class_exists( 'TA_Known_Pattern_Detector' ) &&
		     class_exists( 'TA_Heuristic_Detector' ) ) {
			$known_detector     = new TA_Known_Pattern_Detector( $wpdb );
			$heuristic_detector = new TA_Heuristic_Detector();
			$this->pipeline     = new TA_Bot_Detection_Pipeline( $known_detector, $heuristic_detector );
		}
	}

	/**
	 * Detect bot from user agent.
	 *
	 * Uses the detection pipeline if available, otherwise falls back to legacy detection.
	 * Returns array format for backward compatibility.
	 *
	 * @since 1.4.0
	 * @param string $user_agent The user agent string.
	 * @return array|false Bot information or false if not a known bot.
	 */
	public function detect_bot( $user_agent ) {
		if ( empty( $user_agent ) ) {
			return false;
		}

		// Use pipeline if available (new system).
		if ( null !== $this->pipeline ) {
			$detection_result = $this->pipeline->detect( $user_agent );

			if ( ! $detection_result->is_bot() ) {
				return false;
			}

			// Convert pipeline result to legacy array format for backward compatibility.
			$bot_name = $detection_result->get_bot_name();

			// Map bot name to known bot type for color.
			$bot_type = $this->get_bot_type_from_name( $bot_name );
			$color    = $this->get_bot_color( $bot_type );
			$priority = $this->get_bot_priority( $bot_type, 'medium' );

			return array(
				'type'             => $bot_type,
				'bot_type'         => $bot_type,
				'name'             => $bot_name,
				'color'            => $color,
				'priority'         => $priority,
				'detection_method' => $detection_result->get_method(),
				'confidence'       => $detection_result->get_confidence(),
			);
		}

		// Legacy detection (fallback if pipeline not available).
		// Check known bots first.
		foreach ( self::$known_bots as $bot_type => $bot_info ) {
			if ( preg_match( $bot_info['pattern'], $user_agent ) ) {
				// Get custom priority from config, fallback to default.
				$priority = $this->get_bot_priority( $bot_type, $bot_info['priority'] );

				return array(
					'type'             => $bot_type,
					'bot_type'         => $bot_type,
					'name'             => $bot_info['name'],
					'color'            => $bot_info['color'],
					'priority'         => $priority,
					'detection_method' => 'pattern',
					'confidence'       => 1.0,
				);
			}
		}

		// Check custom bot patterns.
		$bot_config  = get_option( 'ta_bot_config', array() );
		$custom_bots = isset( $bot_config['custom_bots'] ) ? $bot_config['custom_bots'] : array();

		foreach ( $custom_bots as $custom_bot ) {
			if ( empty( $custom_bot['pattern'] ) || empty( $custom_bot['name'] ) ) {
				continue;
			}

			// Safely check pattern (suppress errors for invalid regex).
			$pattern = $custom_bot['pattern'];
			if ( @preg_match( $pattern, $user_agent ) ) {
				$bot_type = 'Custom_' . sanitize_title( $custom_bot['name'] );
				$priority = $this->get_bot_priority( $bot_type, 'low' );

				return array(
					'type'             => $bot_type,
					'bot_type'         => $bot_type,
					'name'             => $custom_bot['name'],
					'color'            => '#8B5CF6', // Purple color for custom bots.
					'priority'         => $priority,
					'detection_method' => 'custom_pattern',
					'confidence'       => 0.9,
				);
			}
		}

		return false;
	}

	/**
	 * Get bot detection result object (new pipeline method).
	 *
	 * Returns detailed detection information including confidence score.
	 *
	 * @since 2.3.0
	 * @param string $user_agent The user agent string.
	 * @return TA_Bot_Detection_Result|null Detection result or null if pipeline unavailable.
	 */
	public function get_bot_detection_result( $user_agent ) {
		if ( null === $this->pipeline ) {
			return null;
		}

		return $this->pipeline->detect( $user_agent );
	}

	/**
	 * Get bot type from bot name.
	 *
	 * Maps bot names back to their types for backward compatibility.
	 *
	 * @since 2.3.0
	 * @param string $bot_name Bot name.
	 * @return string Bot type.
	 */
	public function get_bot_type_from_name( $bot_name ) {
		// Map known bot names to types.
		$name_to_type_map = array(
			'Claude (Anthropic)' => 'ClaudeBot',
			'GPT (OpenAI)'       => 'GPTBot',
			'ChatGPT User'       => 'ChatGPT-User',
			'Perplexity'         => 'PerplexityBot',
			'ByteDance AI'       => 'Bytespider',
			'Anthropic AI'       => 'anthropic-ai',
			'Cohere'             => 'cohere-ai',
			'Google Gemini'      => 'Google-Extended',
			'Meta AI'            => 'FacebookBot',
			'Apple Intelligence' => 'Applebot-Extended',
		);

		return $name_to_type_map[ $bot_name ] ?? 'Custom_' . sanitize_title( $bot_name );
	}

	/**
	 * Get bot color by type.
	 *
	 * @since 2.3.0
	 * @param string $bot_type Bot type.
	 * @return string Hex color code.
	 */
	public function get_bot_color( $bot_type ) {
		if ( isset( self::$known_bots[ $bot_type ]['color'] ) ) {
			return self::$known_bots[ $bot_type ]['color'];
		}

		// Default color for unknown/custom bots.
		return '#8B5CF6';
	}

	/**
	 * Check if a bot type is blocked.
	 *
	 * @since 1.5.0
	 * @param string $bot_type The bot type to check.
	 * @return bool True if blocked, false otherwise.
	 */
	public function is_bot_blocked( $bot_type ) {
		$bot_config   = get_option( 'ta_bot_config', array() );
		$blocked_bots = isset( $bot_config['blocked_bots'] ) ? $bot_config['blocked_bots'] : array();

		// Check if bot is explicitly blocked.
		if ( in_array( $bot_type, $blocked_bots, true ) ) {
			return true;
		}

		// Check if bot priority is set to 'blocked'.
		$priority = $this->get_bot_priority( $bot_type );
		return 'blocked' === $priority;
	}

	/**
	 * Get bot priority from config.
	 *
	 * @since 2.1.0
	 * @param string $bot_type         Bot type.
	 * @param string $default_priority Default priority if not configured.
	 * @return string Priority level (high, medium, low, blocked).
	 */
	public function get_bot_priority( $bot_type, $default_priority = 'medium' ) {
		$bot_config     = get_option( 'ta_bot_config', array() );
		$bot_priorities = isset( $bot_config['bot_priorities'] ) ? $bot_config['bot_priorities'] : array();

		// Return custom priority if set, otherwise return default.
		if ( isset( $bot_priorities[ $bot_type ] ) ) {
			return $bot_priorities[ $bot_type ];
		}

		return $default_priority;
	}

	/**
	 * Get cache TTL based on bot priority.
	 *
	 * @since 2.1.0
	 * @param string $priority Bot priority level.
	 * @return int Cache TTL in seconds.
	 */
	public static function get_cache_ttl_for_priority( $priority ) {
		$ttl_map = array(
			'high'    => 48 * HOUR_IN_SECONDS, // 48 hours.
			'medium'  => 24 * HOUR_IN_SECONDS, // 24 hours.
			'low'     => 6 * HOUR_IN_SECONDS,  // 6 hours.
			'blocked' => 0,                     // No cache for blocked bots.
		);

		return isset( $ttl_map[ $priority ] ) ? $ttl_map[ $priority ] : 24 * HOUR_IN_SECONDS;
	}

	/**
	 * Migrate hardcoded bot patterns to database.
	 *
	 * Runs once to migrate the $known_bots array to wp_ta_bot_patterns table.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function maybe_migrate_patterns() {
		// Check if migration already done.
		if ( get_option( 'ta_bot_patterns_migrated', false ) ) {
			return;
		}

		global $wpdb;
		$patterns_table = $wpdb->prefix . 'ta_bot_patterns';

		// Check if table exists.
		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$patterns_table}'" );
		if ( ! $table_exists ) {
			return;
		}

		// Migrate each known bot pattern.
		$migrated_count = 0;
		foreach ( self::$known_bots as $bot_type => $bot_info ) {
			// Use pattern as-is (already a regex).
			$pattern = $bot_info['pattern'];

			// Extract vendor from bot name.
			$bot_vendor = $this->get_vendor_from_name( $bot_info['name'] );

			// Determine category based on bot type.
			$category_map = array(
				'ClaudeBot'         => 'ai',
				'GPTBot'            => 'ai',
				'ChatGPT-User'      => 'ai',
				'PerplexityBot'     => 'ai',
				'Bytespider'        => 'ai',
				'anthropic-ai'      => 'ai',
				'cohere-ai'         => 'ai',
				'Google-Extended'   => 'ai',
				'FacebookBot'       => 'social',
				'Applebot-Extended' => 'ai',
			);
			$bot_category = $category_map[ $bot_type ] ?? 'other';

			// Map to new structure.
			$insert_data = array(
				'pattern'       => $pattern,
				'pattern_type'  => 'regex',
				'bot_name'      => $bot_info['name'],
				'bot_vendor'    => $bot_vendor,
				'bot_category'  => $bot_category,
				'priority'      => $bot_info['priority'],
				'color'         => $bot_info['color'],
				'source'        => 'manual',
				'is_active'     => 1,
				'first_seen'    => current_time( 'mysql' ),
			);

			// Add pattern.
			$result = $wpdb->insert( $patterns_table, $insert_data );
			if ( $result ) {
				$migrated_count++;
			}
		}

		// Mark migration as complete.
		update_option( 'ta_bot_patterns_migrated', true );

		$this->logger->info( 'Bot patterns migrated to database.', array(
			'count' => $migrated_count,
		) );
	}

	/**
	 * Extract vendor name from bot name.
	 *
	 * @since 2.3.0
	 * @param string $bot_name Bot name.
	 * @return string Vendor name.
	 */
	private function get_vendor_from_name( $bot_name ) {
		// Extract vendor from name (text in parentheses).
		if ( preg_match( '/\(([^)]+)\)/', $bot_name, $matches ) ) {
			return $matches[1];
		}

		// Default vendors.
		$vendor_map = array(
			'Claude'       => 'Anthropic',
			'GPT'          => 'OpenAI',
			'ChatGPT'      => 'OpenAI',
			'Perplexity'   => 'Perplexity',
			'ByteDance'    => 'ByteDance',
			'Anthropic'    => 'Anthropic',
			'Cohere'       => 'Cohere',
			'Google'       => 'Google',
			'Meta'         => 'Meta',
			'Apple'        => 'Apple',
		);

		foreach ( $vendor_map as $keyword => $vendor ) {
			if ( stripos( $bot_name, $keyword ) !== false ) {
				return $vendor;
			}
		}

		return 'Unknown';
	}

	/**
	 * Get known bots array.
	 *
	 * @since 1.4.0
	 * @return array Known bots configuration.
	 */
	public static function get_known_bots() {
		return self::$known_bots;
	}
}
