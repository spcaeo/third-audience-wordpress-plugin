<?php
/**
 * Admin Settings Handlers - Settings save operations.
 *
 * Handles saving SMTP, notifications, bot config, headless, and GA4 settings.
 *
 * @package ThirdAudience
 * @since   3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Admin_Settings
 *
 * Handles settings save operations for the admin interface.
 *
 * @since 3.3.1
 */
class TA_Admin_Settings {

	/**
	 * Security instance.
	 *
	 * @var TA_Security
	 */
	private $security;

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Notifications instance.
	 *
	 * @var TA_Notifications
	 */
	private $notifications;

	/**
	 * Singleton instance.
	 *
	 * @var TA_Admin_Settings|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 3.3.1
	 * @return TA_Admin_Settings
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
		$this->security      = TA_Security::get_instance();
		$this->logger        = TA_Logger::get_instance();
		$this->notifications = TA_Notifications::get_instance();
	}

	/**
	 * Register action hooks.
	 *
	 * @since 3.3.1
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'admin_post_ta_save_smtp_settings', array( $this, 'handle_save_smtp_settings' ) );
		add_action( 'admin_post_ta_save_notification_settings', array( $this, 'handle_save_notification_settings' ) );
		add_action( 'admin_post_ta_save_bot_config', array( $this, 'handle_save_bot_config' ) );
		add_action( 'admin_post_ta_save_headless_settings', array( $this, 'handle_save_headless_settings' ) );
		add_action( 'admin_post_ta_save_ga4_settings', array( $this, 'handle_save_ga4_settings' ) );

		// Metadata settings change hooks.
		add_action( 'update_option_ta_enable_enhanced_metadata', array( $this, 'on_metadata_settings_change' ), 10, 2 );
		add_action( 'update_option_ta_metadata_word_count', array( $this, 'on_metadata_settings_change' ), 10, 2 );
		add_action( 'update_option_ta_metadata_reading_time', array( $this, 'on_metadata_settings_change' ), 10, 2 );
		add_action( 'update_option_ta_metadata_summary', array( $this, 'on_metadata_settings_change' ), 10, 2 );
		add_action( 'update_option_ta_metadata_language', array( $this, 'on_metadata_settings_change' ), 10, 2 );
		add_action( 'update_option_ta_metadata_last_modified', array( $this, 'on_metadata_settings_change' ), 10, 2 );
		add_action( 'update_option_ta_metadata_schema_type', array( $this, 'on_metadata_settings_change' ), 10, 2 );
		add_action( 'update_option_ta_metadata_related_posts', array( $this, 'on_metadata_settings_change' ), 10, 2 );
	}

	/**
	 * Handle save SMTP settings action.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function handle_save_smtp_settings() {
		$this->security->verify_admin_capability();
		$this->security->verify_nonce_or_die( 'save_smtp_settings' );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$settings = isset( $_POST['ta_smtp'] ) && is_array( $_POST['ta_smtp'] ) ? $_POST['ta_smtp'] : array();

		// Sanitize settings.
		$sanitized = array(
			'enabled'    => isset( $settings['enabled'] ),
			'host'       => $this->security->sanitize_text( $settings['host'] ?? '' ),
			'port'       => absint( $settings['port'] ?? 587 ),
			'encryption' => in_array( $settings['encryption'] ?? 'tls', array( '', 'ssl', 'tls' ), true )
				? $settings['encryption']
				: 'tls',
			'username'   => $this->security->sanitize_text( $settings['username'] ?? '' ),
			'password'   => $settings['password'] ?? '', // Will be encrypted in save_smtp_settings.
			'from_email' => $this->security->sanitize_email( $settings['from_email'] ?? '' ),
			'from_name'  => $this->security->sanitize_text( $settings['from_name'] ?? '' ),
		);

		// Only update password if a new one was provided.
		if ( empty( $sanitized['password'] ) ) {
			$existing              = $this->notifications->get_smtp_settings();
			$sanitized['password'] = $existing['password'] ?? '';
		}

		$this->notifications->save_smtp_settings( $sanitized );
		$this->logger->info( 'SMTP settings updated.' );

		add_settings_error(
			'ta_messages',
			'ta_smtp_saved',
			__( 'SMTP settings saved.', 'third-audience' ),
			'success'
		);

		$this->redirect_to_settings( 'notifications' );
	}

	/**
	 * Handle save notification settings action.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function handle_save_notification_settings() {
		$this->security->verify_admin_capability();
		$this->security->verify_nonce_or_die( 'save_notification_settings' );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$settings = isset( $_POST['ta_notifications'] ) && is_array( $_POST['ta_notifications'] ) ? $_POST['ta_notifications'] : array();

		// Sanitize settings.
		$sanitized = array(
			'alert_emails'         => $this->security->sanitize_text( $settings['alert_emails'] ?? '' ),
			'on_worker_failure'    => isset( $settings['on_worker_failure'] ),
			'on_high_error_rate'   => isset( $settings['on_high_error_rate'] ),
			'on_cache_issues'      => isset( $settings['on_cache_issues'] ),
			'daily_digest'         => isset( $settings['daily_digest'] ),
			'error_rate_threshold' => absint( $settings['error_rate_threshold'] ?? 10 ),
		);

		$this->notifications->save_notification_settings( $sanitized );
		$this->logger->info( 'Notification settings updated.' );

		add_settings_error(
			'ta_messages',
			'ta_notifications_saved',
			__( 'Notification settings saved.', 'third-audience' ),
			'success'
		);

		$this->redirect_to_settings( 'notifications' );
	}

	/**
	 * Handle save bot configuration action.
	 *
	 * @since 1.5.0
	 * @return void
	 */
	public function handle_save_bot_config() {
		$this->security->verify_admin_capability();
		$this->security->verify_nonce_or_die( 'ta_save_bot_config', 'ta_bot_config_nonce', 'POST' );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$track_unknown = isset( $_POST['track_unknown'] ) && '1' === $_POST['track_unknown'];
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$blocked_bots = isset( $_POST['blocked_bots'] ) && is_array( $_POST['blocked_bots'] ) ? array_map( 'sanitize_text_field', $_POST['blocked_bots'] ) : array();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$custom_bots = isset( $_POST['custom_bots'] ) && is_array( $_POST['custom_bots'] ) ? $_POST['custom_bots'] : array();
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$bot_priorities = isset( $_POST['bot_priorities'] ) && is_array( $_POST['bot_priorities'] ) ? $_POST['bot_priorities'] : array();

		// Validate and sanitize custom bots.
		$sanitized_custom_bots = array();
		foreach ( $custom_bots as $custom_bot ) {
			if ( ! empty( $custom_bot['pattern'] ) && ! empty( $custom_bot['name'] ) ) {
				$sanitized_custom_bots[] = array(
					'pattern' => $this->security->sanitize_text( $custom_bot['pattern'] ),
					'name'    => $this->security->sanitize_text( $custom_bot['name'] ),
				);
			}
		}

		// Validate and sanitize bot priorities.
		$sanitized_bot_priorities = array();
		$valid_priorities = array( 'high', 'medium', 'low', 'blocked' );
		foreach ( $bot_priorities as $bot_type => $priority ) {
			if ( in_array( $priority, $valid_priorities, true ) ) {
				$sanitized_bot_priorities[ sanitize_text_field( $bot_type ) ] = sanitize_text_field( $priority );
			}
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$rate_limits = isset( $_POST['rate_limits'] ) && is_array( $_POST['rate_limits'] ) ? $_POST['rate_limits'] : array();

		// Validate and sanitize rate limits.
		$sanitized_rate_limits = array();
		$valid_rate_priorities = array( 'high', 'medium', 'low' );
		foreach ( $rate_limits as $priority => $limits ) {
			if ( in_array( $priority, $valid_rate_priorities, true ) ) {
				$sanitized_rate_limits[ $priority ] = array(
					'per_minute' => isset( $limits['per_minute'] ) ? absint( $limits['per_minute'] ) : 0,
					'per_hour'   => isset( $limits['per_hour'] ) ? absint( $limits['per_hour'] ) : 0,
				);
			}
		}

		// Save bot configuration.
		$bot_config = array(
			'track_unknown'  => $track_unknown,
			'blocked_bots'   => $blocked_bots,
			'custom_bots'    => $sanitized_custom_bots,
			'bot_priorities' => $sanitized_bot_priorities,
		);

		update_option( 'ta_bot_config', $bot_config );

		// Save rate limits separately.
		if ( ! empty( $sanitized_rate_limits ) ) {
			update_option( 'ta_bot_rate_limits', $sanitized_rate_limits );
		}

		// Log the change.
		$this->logger->info( 'Bot configuration updated.', array(
			'track_unknown'    => $track_unknown,
			'blocked_count'    => count( $blocked_bots ),
			'custom_count'     => count( $sanitized_custom_bots ),
			'priorities_count' => count( $sanitized_bot_priorities ),
			'rate_limits'      => $sanitized_rate_limits,
		) );

		add_settings_error(
			'ta_messages',
			'ta_bot_config_saved',
			__( 'Bot configuration saved successfully.', 'third-audience' ),
			'success'
		);

		set_transient( 'settings_errors', get_settings_errors(), 30 );

		// Redirect back to bot management page.
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'third-audience-bot-management',
					'updated' => 'true',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Handle save headless settings action.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function handle_save_headless_settings() {
		$this->security->verify_admin_capability();
		$this->security->verify_nonce_or_die( 'save_headless_settings', 'ta_nonce', 'POST' );

		// Get wizard instance.
		$wizard = new TA_Headless_Wizard();

		// Sanitize and prepare settings.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$settings = array(
			'enabled'      => ! empty( $_POST['ta_headless_enabled'] ),
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			'frontend_url' => isset( $_POST['ta_headless_frontend_url'] ) ? esc_url_raw( wp_unslash( $_POST['ta_headless_frontend_url'] ) ) : '',
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			'framework'    => isset( $_POST['ta_headless_framework'] ) ? $this->security->sanitize_text( wp_unslash( $_POST['ta_headless_framework'] ) ) : 'nextjs',
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			'server_type'  => isset( $_POST['ta_headless_server_type'] ) ? $this->security->sanitize_text( wp_unslash( $_POST['ta_headless_server_type'] ) ) : 'nginx',
		);

		// Validate frontend URL if enabled.
		if ( $settings['enabled'] && empty( $settings['frontend_url'] ) ) {
			add_settings_error(
				'ta_messages',
				'ta_headless_url_required',
				__( 'Frontend URL is required when headless mode is enabled.', 'third-audience' ),
				'error'
			);
			$this->redirect_to_settings( 'headless' );
			return;
		}

		// Save settings.
		$wizard->save_settings( $settings );

		add_settings_error(
			'ta_messages',
			'ta_headless_saved',
			__( 'Headless settings saved successfully.', 'third-audience' ),
			'success'
		);

		$this->redirect_to_settings( 'headless' );
	}

	/**
	 * Handle save GA4 settings action.
	 *
	 * @since 3.0.0
	 * @return void
	 */
	public function handle_save_ga4_settings() {
		$this->security->verify_admin_capability();
		$this->security->verify_nonce_or_die( 'save_ga4_settings' );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$settings = isset( $_POST['ta_ga4'] ) && is_array( $_POST['ta_ga4'] ) ? $_POST['ta_ga4'] : array();

		// Sanitize settings.
		$sanitized = array(
			'enabled'        => isset( $settings['enabled'] ),
			'measurement_id' => $this->security->sanitize_text( $settings['measurement_id'] ?? '' ),
			'api_secret'     => $this->security->sanitize_text( $settings['api_secret'] ?? '' ),
		);

		// Validate Measurement ID format (should be G-XXXXXXXXXX).
		if ( ! empty( $sanitized['measurement_id'] ) && ! preg_match( '/^G-[A-Z0-9]+$/', $sanitized['measurement_id'] ) ) {
			add_settings_error(
				'ta_messages',
				'ta_ga4_invalid_id',
				__( 'Invalid GA4 Measurement ID format. Should be G-XXXXXXXXXX.', 'third-audience' ),
				'error'
			);
			$this->redirect_to_settings( 'ga4' );
			return;
		}

		if ( class_exists( 'TA_GA4_Integration' ) ) {
			$ga4 = TA_GA4_Integration::get_instance();
			$ga4->save_settings( $sanitized );
		}

		add_settings_error(
			'ta_messages',
			'ta_ga4_saved',
			__( 'GA4 settings saved successfully.', 'third-audience' ),
			'success'
		);

		$this->redirect_to_settings( 'ga4' );
	}

	/**
	 * Handle metadata settings changes - clear pre-generated markdown.
	 *
	 * When any AI-optimized metadata setting changes, we need to clear
	 * all pre-generated markdown so it regenerates with new settings.
	 *
	 * @since 2.1.0
	 * @param mixed $old_value The old option value.
	 * @param mixed $new_value The new option value.
	 * @return void
	 */
	public function on_metadata_settings_change( $old_value, $new_value ) {
		// Only clear if value actually changed.
		if ( $old_value === $new_value ) {
			return;
		}

		$cache_manager = new TA_Cache_Manager();
		$cleared       = $cache_manager->clear_pregenerated_markdown();

		$this->logger->info( 'Pre-generated markdown cleared due to metadata settings change.', array(
			'posts_cleared' => $cleared,
		) );
	}

	/**
	 * Redirect to settings page with a specific tab.
	 *
	 * @since 1.1.0
	 * @param string $tab The tab to redirect to.
	 * @return void
	 */
	private function redirect_to_settings( $tab = '' ) {
		set_transient( 'settings_errors', get_settings_errors(), 30 );

		$redirect_url = add_query_arg(
			array(
				'page'             => 'third-audience',
				'tab'              => $tab,
				'settings-updated' => 'true',
			),
			admin_url( 'options-general.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}
}
