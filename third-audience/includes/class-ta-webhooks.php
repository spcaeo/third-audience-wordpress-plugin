<?php
/**
 * Webhooks - Sends webhooks on key events.
 *
 * Handles webhook configuration, firing, and delivery for key events:
 * - markdown.accessed: When markdown content is accessed by bots
 * - bot.detected: When a new bot is detected for the first time
 *
 * @package ThirdAudience
 * @since   2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Webhooks
 *
 * Manages webhook configuration and delivery.
 *
 * @since 2.1.0
 */
class TA_Webhooks {

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Singleton instance.
	 *
	 * @var TA_Webhooks|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 2.1.0
	 * @return TA_Webhooks
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
	 * @since 2.1.0
	 */
	private function __construct() {
		$this->logger = TA_Logger::get_instance();
	}

	/**
	 * Check if webhooks are enabled.
	 *
	 * @since 2.1.0
	 * @return bool True if webhooks are enabled.
	 */
	public function is_enabled() {
		return (bool) get_option( 'ta_webhooks_enabled', false );
	}

	/**
	 * Get webhook URL.
	 *
	 * @since 2.1.0
	 * @return string The webhook URL or empty string if not set.
	 */
	public function get_webhook_url() {
		$url = get_option( 'ta_webhook_url', '' );
		return ! empty( $url ) ? sanitize_url( $url ) : '';
	}

	/**
	 * Set webhook URL.
	 *
	 * @since 2.1.0
	 * @param string $url The webhook URL.
	 * @return bool True if successfully saved.
	 */
	public function set_webhook_url( $url ) {
		$url = sanitize_url( $url );

		if ( empty( $url ) ) {
			delete_option( 'ta_webhook_url' );
			return true;
		}

		return update_option( 'ta_webhook_url', $url );
	}

	/**
	 * Enable webhooks.
	 *
	 * @since 2.1.0
	 * @param bool $enabled Whether to enable webhooks.
	 * @return bool True if successfully saved.
	 */
	public function set_enabled( $enabled ) {
		return update_option( 'ta_webhooks_enabled', (bool) $enabled );
	}

	/**
	 * Fire a webhook for markdown access event.
	 *
	 * @since 2.1.0
	 * @param array $data Event data.
	 *   - bot_type: string The bot type
	 *   - bot_name: string The bot name
	 *   - url: string The URL accessed
	 *   - post_id: int The post ID
	 *   - post_title: string The post title
	 *   - cache_status: string Cache status (HIT, MISS, etc)
	 *   - response_time: int Response time in milliseconds
	 * @return bool True if webhook was sent successfully or skipped.
	 */
	public function fire_markdown_accessed( $data ) {
		if ( ! $this->is_enabled() ) {
			return true;
		}

		$payload = array(
			'event'      => 'markdown.accessed',
			'timestamp'  => current_time( 'c' ),
			'site_url'   => get_site_url(),
			'data'       => $data,
		);

		return $this->send_webhook( $payload );
	}

	/**
	 * Fire a webhook for bot detected event.
	 *
	 * This event is fired when a new bot is detected for the first time.
	 * We track recently seen bots to avoid duplicate events.
	 *
	 * @since 2.1.0
	 * @param array $bot_info Bot information.
	 *   - type: string The bot type
	 *   - name: string The bot name
	 *   - color: string The bot color
	 * @return bool True if webhook was sent successfully or skipped.
	 */
	public function fire_bot_detected( $bot_info ) {
		if ( ! $this->is_enabled() ) {
			return true;
		}

		// Check if we've already notified about this bot recently (within 24 hours).
		$recently_notified = get_transient( 'ta_webhook_bot_notified_' . $bot_info['type'] );

		if ( $recently_notified ) {
			// Already notified about this bot in the last 24 hours.
			return true;
		}

		// Mark this bot as recently notified.
		set_transient( 'ta_webhook_bot_notified_' . $bot_info['type'], true, DAY_IN_SECONDS );

		$payload = array(
			'event'      => 'bot.detected',
			'timestamp'  => current_time( 'c' ),
			'site_url'   => get_site_url(),
			'data'       => array(
				'bot_type'  => $bot_info['type'],
				'bot_name'  => $bot_info['name'],
				'bot_color' => $bot_info['color'],
			),
		);

		return $this->send_webhook( $payload );
	}

	/**
	 * Send a webhook with retry logic.
	 *
	 * @since 2.1.0
	 * @param array $payload The webhook payload.
	 * @return bool True if webhook was sent successfully.
	 */
	private function send_webhook( $payload ) {
		$url = $this->get_webhook_url();

		if ( empty( $url ) ) {
			$this->logger->warning( 'Webhook URL not configured.' );
			return false;
		}

		// Prepare the POST request.
		$args = array(
			'method'      => 'POST',
			'timeout'     => 10,
			'redirection' => 0,
			'blocking'    => true,
			'httpversion' => '1.1',
			'headers'     => array(
				'Content-Type' => 'application/json',
				'User-Agent'   => 'Third Audience/' . TA_VERSION,
			),
			'body'        => wp_json_encode( $payload ),
		);

		// Send the webhook.
		$response = wp_remote_post( $url, $args );

		// Check for errors.
		if ( is_wp_error( $response ) ) {
			$this->logger->warning(
				'Webhook delivery failed.',
				array(
					'url'         => $url,
					'event'       => $payload['event'],
					'error'       => $response->get_error_message(),
				)
			);

			// Retry once on failure.
			$response = wp_remote_post( $url, $args );

			if ( is_wp_error( $response ) ) {
				$this->logger->error(
					'Webhook delivery failed after retry.',
					array(
						'url'         => $url,
						'event'       => $payload['event'],
						'error'       => $response->get_error_message(),
					)
				);
				return false;
			}
		}

		// Check response status code.
		$status_code = wp_remote_retrieve_response_code( $response );

		if ( $status_code >= 200 && $status_code < 300 ) {
			$this->logger->debug(
				'Webhook delivered successfully.',
				array(
					'url'         => $url,
					'event'       => $payload['event'],
					'status_code' => $status_code,
				)
			);
			return true;
		}

		$this->logger->warning(
			'Webhook delivery received non-success status code.',
			array(
				'url'         => $url,
				'event'       => $payload['event'],
				'status_code' => $status_code,
			)
		);

		return false;
	}

	/**
	 * Test webhook delivery.
	 *
	 * Sends a test webhook to the configured URL.
	 *
	 * @since 2.1.0
	 * @return array Response data with 'success' and 'message' keys.
	 */
	public function test_webhook() {
		$url = $this->get_webhook_url();

		if ( empty( $url ) ) {
			return array(
				'success' => false,
				'message' => __( 'No webhook URL configured.', 'third-audience' ),
			);
		}

		// Create a test payload.
		$payload = array(
			'event'      => 'webhook.test',
			'timestamp'  => current_time( 'c' ),
			'site_url'   => get_site_url(),
			'message'    => __( 'This is a test webhook from Third Audience plugin.', 'third-audience' ),
			'data'       => array(
				'test'          => true,
				'plugin_name'   => 'Third Audience',
				'plugin_version' => TA_VERSION,
			),
		);

		// Send the test webhook.
		$args = array(
			'method'      => 'POST',
			'timeout'     => 10,
			'redirection' => 0,
			'blocking'    => true,
			'httpversion' => '1.1',
			'headers'     => array(
				'Content-Type' => 'application/json',
				'User-Agent'   => 'Third Audience/' . TA_VERSION,
			),
			'body'        => wp_json_encode( $payload ),
		);

		$response = wp_remote_post( $url, $args );

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %s: error message */
					__( 'Webhook delivery failed: %s', 'third-audience' ),
					$response->get_error_message()
				),
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		if ( $status_code >= 200 && $status_code < 300 ) {
			return array(
				'success' => true,
				'message' => sprintf(
					/* translators: %d: HTTP status code */
					__( 'Webhook delivered successfully (HTTP %d)', 'third-audience' ),
					$status_code
				),
				'status_code' => $status_code,
			);
		}

		return array(
			'success' => false,
			'message' => sprintf(
				/* translators: %d: HTTP status code, %s: response body */
				__( 'Webhook delivery failed (HTTP %d): %s', 'third-audience' ),
				$status_code,
				substr( $response_body, 0, 100 )
			),
			'status_code' => $status_code,
		);
	}
}
