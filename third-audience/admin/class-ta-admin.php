<?php
/**
 * Admin - Settings page and admin functionality.
 *
 * Handles the admin interface, settings registration, AJAX handlers,
 * and SMTP/notification configuration.
 *
 * @package ThirdAudience
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Admin
 *
 * Admin functionality for Third Audience plugin.
 *
 * @since 1.0.0
 */
class TA_Admin {

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
	 * Constructor.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
		$this->security      = TA_Security::get_instance();
		$this->logger        = TA_Logger::get_instance();
		$this->notifications = TA_Notifications::get_instance();
	}

	/**
	 * Initialize admin functionality.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_notices', array( $this, 'display_configuration_notices' ) );

		// Admin post handlers.
		add_action( 'admin_post_ta_clear_cache', array( $this, 'handle_clear_cache' ) );
		add_action( 'admin_post_ta_test_smtp', array( $this, 'handle_test_smtp' ) );
		add_action( 'admin_post_ta_clear_errors', array( $this, 'handle_clear_errors' ) );
		add_action( 'admin_post_ta_save_smtp_settings', array( $this, 'handle_save_smtp_settings' ) );
		add_action( 'admin_post_ta_save_notification_settings', array( $this, 'handle_save_notification_settings' ) );
		add_action( 'admin_post_ta_save_bot_config', array( $this, 'handle_save_bot_config' ) );
		add_action( 'admin_post_ta_save_headless_settings', array( $this, 'handle_save_headless_settings' ) );

		// AJAX handlers.
		add_action( 'wp_ajax_ta_test_smtp', array( $this, 'ajax_test_smtp' ) );
		add_action( 'wp_ajax_ta_clear_cache', array( $this, 'ajax_clear_cache' ) );
		add_action( 'wp_ajax_ta_get_recent_errors', array( $this, 'ajax_get_recent_errors' ) );
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @since 1.1.0
	 * @param string $hook The current admin page hook.
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		// Settings page.
		if ( 'settings_page_third-audience' === $hook ) {
			wp_enqueue_style(
				'ta-admin',
				TA_PLUGIN_URL . 'admin/css/admin.css',
				array(),
				TA_VERSION
			);

			wp_enqueue_script(
				'ta-admin',
				TA_PLUGIN_URL . 'admin/js/admin.js',
				array( 'jquery' ),
				TA_VERSION,
				true
			);

			wp_localize_script( 'ta-admin', 'taAdmin', array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => $this->security->create_nonce( 'admin_ajax' ),
			'homeUrl' => trailingslashit( home_url() ),
				'i18n'    => array(
					'testing'        => __( 'Testing...', 'third-audience' ),
					'clearing'       => __( 'Clearing...', 'third-audience' ),
					'success'        => __( 'Success!', 'third-audience' ),
					'error'          => __( 'Error', 'third-audience' ),
					'confirmClear'   => __( 'Are you sure you want to clear all cached items?', 'third-audience' ),
					'confirmClearErrors' => __( 'Are you sure you want to clear all error logs?', 'third-audience' ),
				),
			) );
		}

		// Bot Analytics page.
		if ( 'toplevel_page_third-audience-bot-analytics' === $hook ) {
			// Enqueue Chart.js from CDN.
			wp_enqueue_script(
				'chartjs',
				'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
				array(),
				'4.4.0',
				true
			);

			// Enqueue bot analytics styles.
			wp_enqueue_style(
				'ta-bot-analytics',
				TA_PLUGIN_URL . 'admin/css/bot-analytics.css',
				array(),
				TA_VERSION
			);

			// Enqueue bot analytics scripts.
			wp_enqueue_script(
				'ta-bot-analytics',
				TA_PLUGIN_URL . 'admin/js/bot-analytics.js',
				array( 'jquery', 'chartjs' ),
				TA_VERSION,
				true
			);
		}
	}

	/**
	 * Add settings page to admin menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_settings_page() {
		// Main settings page.
		add_options_page(
			__( 'Third Audience Settings', 'third-audience' ),
			__( 'Third Audience', 'third-audience' ),
			'manage_options',
			'third-audience',
			array( $this, 'render_settings_page' )
		);

		// Bot Analytics page (as top-level menu item).
		add_menu_page(
			__( 'Bot Analytics', 'third-audience' ),
			__( 'Bot Analytics', 'third-audience' ),
			'manage_options',
			'third-audience-bot-analytics',
			array( $this, 'render_bot_analytics_page' ),
			'dashicons-chart-line',
			30
		);

		// Bot Management submenu.
		add_submenu_page(
			'third-audience-bot-analytics',
			__( 'Bot Management', 'third-audience' ),
			__( 'Bot Management', 'third-audience' ),
			'manage_options',
			'third-audience-bot-management',
			array( $this, 'render_bot_management_page' )
		);

		// System Health submenu.
		add_submenu_page(
			'third-audience-bot-analytics',
			__( 'System Health', 'third-audience' ),
			__( 'System Health', 'third-audience' ),
			'manage_options',
			'third-audience-system-health',
			array( $this, 'render_system_health_page' )
		);

		// About submenu.
		add_submenu_page(
			'third-audience-bot-analytics',
			__( 'About', 'third-audience' ),
			__( 'About', 'third-audience' ),
			'manage_options',
			'third-audience-about',
			array( $this, 'render_about_page' )
		);
	}

	/**
	 * Register settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_settings() {
		// Cache settings.
		register_setting( 'ta_settings', 'ta_cache_ttl', array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 86400,
		) );

		// Feature settings.
		register_setting( 'ta_settings', 'ta_enabled_post_types', array(
			'type'              => 'array',
			'sanitize_callback' => array( $this->security, 'sanitize_post_types' ),
			'default'           => array( 'post', 'page' ),
		) );

		register_setting( 'ta_settings', 'ta_enable_content_negotiation', array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default'           => true,
		) );

		register_setting( 'ta_settings', 'ta_enable_discovery_tags', array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default'           => true,
		) );

		register_setting( 'ta_settings', 'ta_enable_pre_generation', array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default'           => true,
		) );

		// Homepage markdown pattern.
		register_setting( 'ta_settings', 'ta_homepage_md_pattern', array(
			'type'              => 'string',
			'sanitize_callback' => array( $this->security, 'sanitize_text' ),
			'default'           => 'index.md',
		) );

		register_setting( 'ta_settings', 'ta_homepage_md_pattern_custom', array(
			'type'              => 'string',
			'sanitize_callback' => array( $this->security, 'sanitize_text' ),
			'default'           => '',
		) );
	}

	/**
	 * Display configuration notices if plugin is not properly set up.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function display_configuration_notices() {
		// Only show on plugin pages.
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'third-audience' ) === false ) {
			return;
		}

		// Check if HTML to Markdown library is installed.
		if ( ! TA_Local_Converter::is_library_available() ) {
			?>
			<div class="notice notice-error">
				<p>
					<strong><?php esc_html_e( 'Third Audience - Library Missing', 'third-audience' ); ?></strong>
				</p>
				<p>
					<?php
					printf(
						/* translators: %s: System Health page URL */
						esc_html__( 'The HTML to Markdown conversion library is not installed. Please check the %s page for installation instructions.', 'third-audience' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=third-audience-system-health' ) ) . '">' . esc_html__( 'System Health', 'third-audience' ) . '</a>'
					);
					?>
				</p>
			</div>
			<?php
			return;
		}

		// Check if bot analytics table exists.
		global $wpdb;
		$table_name  = $wpdb->prefix . 'ta_bot_analytics';
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) === $table_name;

		if ( ! $table_exists ) {
			?>
			<div class="notice notice-warning">
				<p>
					<strong><?php esc_html_e( 'Third Audience Analytics Table Missing', 'third-audience' ); ?></strong>
				</p>
				<p>
					<?php esc_html_e( 'The bot analytics table was not created. Please deactivate and reactivate the plugin to create the required database tables.', 'third-audience' ); ?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Render settings page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_settings_page() {
		$this->security->verify_admin_capability();

		// Show admin notices.
		settings_errors( 'ta_messages' );

		// Get cache stats.
		$cache_manager = new TA_Cache_Manager();
		$cache_stats   = $cache_manager->get_stats();

		// Get error stats.
		$error_stats   = $this->logger->get_stats();
		$recent_errors = $this->logger->get_recent_errors( 10 );

		// Get notification settings.
		$smtp_settings         = $this->notifications->get_smtp_settings();
		$notification_settings = $this->notifications->get_notification_settings();

		// Get current tab.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_tab = isset( $_GET['tab'] ) ? $this->security->sanitize_text( $_GET['tab'] ) : 'general';

		include TA_PLUGIN_DIR . 'admin/views/settings-page.php';
	}

	/**
	 * Render bot analytics page.
	 *
	 * @since 1.4.0
	 * @return void
	 */
	public function render_bot_analytics_page() {
		$this->security->verify_admin_capability();

		include TA_PLUGIN_DIR . 'admin/views/bot-analytics-page.php';
	}

	/**
	 * Render bot management page.
	 *
	 * @since 1.5.0
	 * @return void
	 */
	public function render_bot_management_page() {
		$this->security->verify_admin_capability();

		include TA_PLUGIN_DIR . 'admin/views/bot-management-page.php';
	}

	/**
	 * Render system health page.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_system_health_page() {
		$this->security->verify_admin_capability();

		include TA_PLUGIN_DIR . 'admin/views/system-health-page.php';
	}

	/**
	 * Render About page.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function render_about_page() {
		$this->security->verify_admin_capability();

		include TA_PLUGIN_DIR . 'admin/views/about-page.php';
	}

	/**
	 * Handle clear cache action.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function handle_clear_cache() {
		$this->security->verify_admin_capability();
		$this->security->verify_nonce_or_die( 'clear_cache' );

		$cache_manager = new TA_Cache_Manager();
		$cleared       = $cache_manager->clear_all();

		$this->logger->info( 'Cache cleared.', array( 'items' => $cleared ) );

		add_settings_error(
			'ta_messages',
			'ta_cache_cleared',
			/* translators: %d: Number of cached items cleared */
			sprintf( __( 'Cleared %d cached items.', 'third-audience' ), $cleared ),
			'success'
		);

		set_transient( 'settings_errors', get_settings_errors(), 30 );

		wp_safe_redirect( add_query_arg(
			array(
				'page'             => 'third-audience',
				'settings-updated' => 'true',
			),
			admin_url( 'options-general.php' )
		) );
		exit;
	}

	/**
	 * Handle test SMTP action.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function handle_test_smtp() {
		$this->security->verify_admin_capability();
		$this->security->verify_nonce_or_die( 'test_smtp' );

		$result = $this->notifications->test_smtp();

		if ( is_wp_error( $result ) ) {
			$this->logger->error( 'SMTP test failed.', array( 'error' => $result->get_error_message() ) );
			add_settings_error(
				'ta_messages',
				'ta_smtp_test_failed',
				__( 'SMTP test failed: ', 'third-audience' ) . $result->get_error_message(),
				'error'
			);
		} else {
			$this->logger->info( 'SMTP test successful.' );
			add_settings_error(
				'ta_messages',
				'ta_smtp_test_success',
				__( 'SMTP test email sent successfully!', 'third-audience' ),
				'success'
			);
		}

		$this->redirect_to_settings( 'notifications' );
	}

	/**
	 * Handle clear errors action.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function handle_clear_errors() {
		$this->security->verify_admin_capability();
		$this->security->verify_nonce_or_die( 'clear_errors' );

		$this->logger->clear_errors();
		$this->logger->reset_stats();
		$this->logger->info( 'Error logs cleared by admin.' );

		add_settings_error(
			'ta_messages',
			'ta_errors_cleared',
			__( 'Error logs cleared.', 'third-audience' ),
			'success'
		);

		$this->redirect_to_settings( 'logs' );
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

		// Save configuration.
		$bot_config = array(
			'track_unknown' => $track_unknown,
			'blocked_bots'  => $blocked_bots,
			'custom_bots'   => $sanitized_custom_bots,
		);

		update_option( 'ta_bot_config', $bot_config );

		// Log the change.
		$this->logger->info( 'Bot configuration updated.', array(
			'track_unknown' => $track_unknown,
			'blocked_count' => count( $blocked_bots ),
			'custom_count'  => count( $sanitized_custom_bots ),
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
		$this->security->verify_nonce_or_die( 'save_headless_settings' );

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
	 * AJAX handler for testing SMTP.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function ajax_test_smtp() {
		$this->security->verify_ajax_request( 'admin_ajax' );

		$result = $this->notifications->test_smtp();

		if ( is_wp_error( $result ) ) {
			$this->logger->error( 'SMTP test failed (AJAX).', array(
				'error' => $result->get_error_message(),
			) );
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array(
			'message' => __( 'Test email sent successfully!', 'third-audience' ),
		) );
	}

	/**
	 * AJAX handler for clearing cache.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function ajax_clear_cache() {
		$this->security->verify_ajax_request( 'admin_ajax' );

		$cache_manager = new TA_Cache_Manager();
		$cleared       = $cache_manager->clear_all();

		$this->logger->info( 'Cache cleared (AJAX).', array( 'items' => $cleared ) );

		wp_send_json_success( array(
			/* translators: %d: Number of cached items cleared */
			'message' => sprintf( __( 'Cleared %d cached items.', 'third-audience' ), $cleared ),
			'count'   => $cleared,
		) );
	}

	/**
	 * AJAX handler for getting recent errors.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function ajax_get_recent_errors() {
		$this->security->verify_ajax_request( 'admin_ajax' );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$limit  = isset( $_REQUEST['limit'] ) ? absint( $_REQUEST['limit'] ) : 20;
		$errors = $this->logger->get_recent_errors( $limit );

		wp_send_json_success( array(
			'errors' => $errors,
			'stats'  => $this->logger->get_stats(),
		) );
	}

	/**
	 * Redirect to settings page with optional tab.
	 *
	 * @since 1.1.0
	 * @param string $tab Optional tab to redirect to.
	 * @return void
	 */
	private function redirect_to_settings( $tab = '' ) {
		set_transient( 'settings_errors', get_settings_errors(), 30 );

		$args = array(
			'page'             => 'third-audience',
			'settings-updated' => 'true',
		);

		if ( ! empty( $tab ) ) {
			$args['tab'] = $tab;
		}

		wp_safe_redirect( add_query_arg( $args, admin_url( 'options-general.php' ) ) );
		exit;
	}
}
