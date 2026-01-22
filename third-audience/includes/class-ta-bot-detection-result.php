<?php
/**
 * Bot Detection Result Value Object
 *
 * Represents the result of bot detection with confidence scoring.
 *
 * @package ThirdAudience
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Bot_Detection_Result
 *
 * Value object for bot detection results.
 *
 * @since 2.3.0
 */
class TA_Bot_Detection_Result {

	/**
	 * Whether the user agent is identified as a bot.
	 *
	 * @var bool
	 */
	private $is_bot;

	/**
	 * Confidence score (0.0 to 1.0).
	 *
	 * @var float
	 */
	private $confidence;

	/**
	 * Detected bot name.
	 *
	 * @var string|null
	 */
	private $bot_name;

	/**
	 * Detected bot vendor/company.
	 *
	 * @var string|null
	 */
	private $bot_vendor;

	/**
	 * Bot category (ai, search, social, seo, monitoring, other).
	 *
	 * @var string|null
	 */
	private $bot_category;

	/**
	 * Detection method used.
	 *
	 * @var string
	 */
	private $method;

	/**
	 * Additional indicators found during detection.
	 *
	 * @var array
	 */
	private $indicators;

	/**
	 * Whether this result needs manual review.
	 *
	 * @var bool
	 */
	private $needs_review;

	/**
	 * Constructor.
	 *
	 * @since 2.3.0
	 * @param array $data Detection result data.
	 */
	public function __construct( array $data ) {
		$this->is_bot       = $data['is_bot'] ?? false;
		$this->confidence   = (float) ( $data['confidence'] ?? 0.0 );
		$this->bot_name     = $data['bot_name'] ?? null;
		$this->bot_vendor   = $data['bot_vendor'] ?? null;
		$this->bot_category = $data['bot_category'] ?? null;
		$this->method       = $data['method'] ?? 'unknown';
		$this->indicators   = $data['indicators'] ?? array();
		$this->needs_review = $data['needs_review'] ?? false;
	}

	/**
	 * Check if identified as bot.
	 *
	 * @since 2.3.0
	 * @return bool
	 */
	public function is_bot(): bool {
		return $this->is_bot;
	}

	/**
	 * Get confidence score.
	 *
	 * @since 2.3.0
	 * @return float
	 */
	public function get_confidence(): float {
		return $this->confidence;
	}

	/**
	 * Check if detection is confident (>= 0.7).
	 *
	 * @since 2.3.0
	 * @return bool
	 */
	public function is_confident(): bool {
		return $this->confidence >= 0.7;
	}

	/**
	 * Get bot name.
	 *
	 * @since 2.3.0
	 * @return string|null
	 */
	public function get_bot_name(): ?string {
		return $this->bot_name;
	}

	/**
	 * Get bot vendor.
	 *
	 * @since 2.3.0
	 * @return string|null
	 */
	public function get_bot_vendor(): ?string {
		return $this->bot_vendor;
	}

	/**
	 * Get bot category.
	 *
	 * @since 2.3.0
	 * @return string|null
	 */
	public function get_bot_category(): ?string {
		return $this->bot_category;
	}

	/**
	 * Get detection method.
	 *
	 * @since 2.3.0
	 * @return string
	 */
	public function get_method(): string {
		return $this->method;
	}

	/**
	 * Get detection indicators.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	public function get_indicators(): array {
		return $this->indicators;
	}

	/**
	 * Check if needs manual review.
	 *
	 * @since 2.3.0
	 * @return bool
	 */
	public function needs_review(): bool {
		return $this->needs_review;
	}

	/**
	 * Convert to array.
	 *
	 * @since 2.3.0
	 * @return array
	 */
	public function to_array(): array {
		return array(
			'is_bot'       => $this->is_bot,
			'confidence'   => $this->confidence,
			'bot_name'     => $this->bot_name,
			'bot_vendor'   => $this->bot_vendor,
			'bot_category' => $this->bot_category,
			'method'       => $this->method,
			'indicators'   => $this->indicators,
			'needs_review' => $this->needs_review,
		);
	}
}
