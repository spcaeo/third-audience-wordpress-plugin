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
	 * Cache admin instance.
	 *
	 * @var TA_Cache_Admin
	 */
	private $cache_admin;

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
		$this->security      = TA_Security::get_instance();
		$this->logger        = TA_Logger::get_instance();
		$this->notifications = TA_Notifications::get_instance();
		$this->cache_admin   = new TA_Cache_Admin( $this->security );
	}

	/**
	 * Initialize admin functionality.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		add_action( 'admin_init', array( $this, 'handle_export_request' ), 5 ); // Priority 5 to run early.
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_notices', array( $this, 'display_configuration_notices' ) );

		// Initialize cache admin (handles Cache Browser and Warmup).
		$this->cache_admin->init();

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
		add_action( 'wp_ajax_ta_clear_all_visits', array( $this, 'ajax_clear_all_visits' ) );
		add_action( 'wp_ajax_ta_delete_cache_entry', array( $this, 'ajax_delete_cache_entry' ) );
		add_action( 'wp_ajax_ta_bulk_delete_cache', array( $this, 'ajax_bulk_delete_cache' ) );
		add_action( 'wp_ajax_ta_clear_expired_cache', array( $this, 'ajax_clear_expired_cache' ) );
		add_action( 'wp_ajax_ta_regenerate_cache', array( $this, 'ajax_regenerate_cache' ) );
		add_action( 'wp_ajax_ta_view_cache_content', array( $this, 'ajax_view_cache_content' ) );
		add_action( 'wp_ajax_ta_get_warmup_stats', array( $this, 'ajax_get_warmup_stats' ) );
		add_action( 'wp_ajax_ta_warm_cache_batch', array( $this, 'ajax_warm_cache_batch' ) );
		add_action( 'wp_ajax_ta_get_recent_accesses', array( $this, 'ajax_get_recent_accesses' ) );
		add_action( 'wp_ajax_ta_regenerate_all_markdown', array( $this, 'ajax_regenerate_all_markdown' ) );

		// Metadata settings hooks - clear pre-generated markdown when settings change.
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
	 * Enqueue admin scripts and styles.
	 *
	 * @since 1.1.0
	 * @param string $hook The current admin page hook.
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		// Enqueue Apple theme globally for all Third Audience pages.
		$ta_pages = array(
			'settings_page_third-audience',
			'toplevel_page_third-audience-bot-analytics',
			'bot-analytics_page_third-audience-bot-management',
			'bot-analytics_page_third-audience-cache-browser',
			'bot-analytics_page_third-audience-system-health',
			'bot-analytics_page_third-audience-about',
		);

		if ( in_array( $hook, $ta_pages, true ) ) {
			wp_enqueue_style(
				'ta-apple-theme',
				TA_PLUGIN_URL . 'admin/css/apple-theme.css',
				array(),
				TA_VERSION
			);
		}

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

		// Cache Browser page.
		if ( 'bot-analytics_page_third-audience-cache-browser' === $hook ) {
			// Enqueue shared bot analytics styles first.
			wp_enqueue_style(
				'ta-bot-analytics',
				TA_PLUGIN_URL . 'admin/css/bot-analytics.css',
				array(),
				TA_VERSION
			);

			wp_enqueue_style(
				'ta-cache-browser',
				TA_PLUGIN_URL . 'admin/css/cache-browser.css',
				array( 'ta-bot-analytics' ),
				TA_VERSION
			);

			wp_enqueue_script(
				'ta-cache-browser',
				TA_PLUGIN_URL . 'admin/js/cache-browser.js',
				array( 'jquery' ),
				TA_VERSION,
				true
			);

			wp_localize_script( 'ta-cache-browser', 'taCacheBrowser', array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => $this->security->create_nonce( 'cache_browser' ),
				'i18n'    => array(
					'confirmDelete'       => __( 'Delete this cache entry?', 'third-audience' ),
					'confirmBulkDelete'   => __( 'Delete selected entries?', 'third-audience' ),
					'confirmClearExpired' => __( 'Clear all expired entries?', 'third-audience' ),
					'confirmWarmup'       => __( 'Start warming all cache? This may take a few minutes.', 'third-audience' ),
					'selectEntries'       => __( 'Select at least one entry.', 'third-audience' ),
					'success'             => __( 'Success!', 'third-audience' ),
					'error'               => __( 'Error', 'third-audience' ),
				),
			) );
		}

		// Bot Management page.
		if ( 'bot-analytics_page_third-audience-bot-management' === $hook ) {
			wp_enqueue_style(
				'ta-bot-management',
				TA_PLUGIN_URL . 'admin/css/bot-management.css',
				array(),
				TA_VERSION
			);
		}

		// System Health page.
		if ( 'bot-analytics_page_third-audience-system-health' === $hook ) {
			wp_enqueue_style(
				'ta-system-health',
				TA_PLUGIN_URL . 'admin/css/system-health.css',
				array(),
				TA_VERSION
			);
		}
	}

	/**
	 * Handle export request (CSV or JSON) before any output.
	 *
	 * @since 2.0.5
	 * @return void
	 */
	public function handle_export_request() {
		// Only handle on bot analytics page with export action.
		if ( ! isset( $_GET['page'] ) || 'third-audience-bot-analytics' !== $_GET['page'] ) {
			return;
		}

		if ( ! isset( $_GET['action'] ) || 'export' !== $_GET['action'] ) {
			return;
		}

		// Verify nonce and capability.
		if ( ! check_admin_referer( 'ta_export_analytics' ) ) {
			wp_die( esc_html__( 'Invalid security token.', 'third-audience' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'third-audience' ) );
		}

		// Get export format and type.
		$export_format = isset( $_GET['export_format'] ) ? sanitize_text_field( wp_unslash( $_GET['export_format'] ) ) : 'csv';
		$export_type   = isset( $_GET['export_type'] ) ? sanitize_text_field( wp_unslash( $_GET['export_type'] ) ) : 'detailed';

		// Validate format and type.
		if ( ! in_array( $export_format, array( 'csv', 'json' ), true ) ) {
			$export_format = 'csv';
		}
		if ( ! in_array( $export_type, array( 'detailed', 'summary' ), true ) ) {
			$export_type = 'detailed';
		}

		// Prepare filters.
		$analytics = TA_Bot_Analytics::get_instance();
		$filters   = array();

		if ( ! empty( $_GET['bot_type'] ) ) {
			$filters['bot_type'] = sanitize_text_field( wp_unslash( $_GET['bot_type'] ) );
		}
		if ( ! empty( $_GET['date_from'] ) ) {
			$filters['date_from'] = sanitize_text_field( wp_unslash( $_GET['date_from'] ) );
		}
		if ( ! empty( $_GET['date_to'] ) ) {
			$filters['date_to'] = sanitize_text_field( wp_unslash( $_GET['date_to'] ) );
		}

		// Export and exit.
		if ( 'json' === $export_format ) {
			$analytics->export_to_json( $filters, $export_type );
		} else {
			$analytics->export_to_csv( $filters, $export_type );
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

		// Cache Browser submenu.
		add_submenu_page(
			'third-audience-bot-analytics',
			__( 'Cache Browser', 'third-audience' ),
			__( 'Cache Browser', 'third-audience' ),
			'manage_options',
			'third-audience-cache-browser',
			array( $this, 'render_cache_browser_page' )
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

		// AI-Optimized Metadata settings.
		register_setting( 'ta_settings', 'ta_enable_enhanced_metadata', array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default'           => true,
		) );

		register_setting( 'ta_settings', 'ta_metadata_word_count', array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default'           => true,
		) );

		register_setting( 'ta_settings', 'ta_metadata_reading_time', array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default'           => true,
		) );

		register_setting( 'ta_settings', 'ta_metadata_summary', array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default'           => true,
		) );

		register_setting( 'ta_settings', 'ta_metadata_language', array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default'           => true,
		) );

		register_setting( 'ta_settings', 'ta_metadata_last_modified', array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default'           => true,
		) );

		register_setting( 'ta_settings', 'ta_metadata_schema_type', array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default'           => true,
		) );

		register_setting( 'ta_settings', 'ta_metadata_related_posts', array(
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'default'           => true,
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
	 * AJAX handler for clearing all bot visits.
	 *
	 * @since 2.0.5
	 * @return void
	 */
	public function ajax_clear_all_visits() {
		$this->security->verify_ajax_request( 'bot_analytics' );

		$bot_analytics = TA_Bot_Analytics::get_instance();
		$deleted       = $bot_analytics->clear_all_visits();

		$this->logger->info( 'All bot visits cleared (AJAX).', array( 'count' => $deleted ) );

		wp_send_json_success( array(
			/* translators: %d: Number of bot visits cleared */
			'message' => sprintf( __( 'Cleared %d bot visit records.', 'third-audience' ), $deleted ),
			'count'   => $deleted,
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

	/**
	 * Render Cache Browser page.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function render_cache_browser_page() {
		$this->security->verify_admin_capability();

		$cache_manager = new TA_Cache_Manager();
		$cache_stats = $cache_manager->get_stats();

		// Pagination.
		$current_page = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
		$per_page = 50;
		$offset = ( $current_page - 1 ) * $per_page;

		// Search.
		$search = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';

		// Filters.
		$filters = array(
			'status'    => isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : 'all',
			'size_min'  => isset( $_GET['size_min'] ) ? absint( $_GET['size_min'] ) : 0,
			'size_max'  => isset( $_GET['size_max'] ) ? absint( $_GET['size_max'] ) : 0,
			'date_from' => isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '',
			'date_to'   => isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '',
		);

		// Size presets.
		if ( isset( $_GET['size_preset'] ) && ! empty( $_GET['size_preset'] ) ) {
			$preset = sanitize_text_field( wp_unslash( $_GET['size_preset'] ) );
			switch ( $preset ) {
				case 'small':
					$filters['size_min'] = 0;
					$filters['size_max'] = 10240; // 10KB.
					break;
				case 'medium':
					$filters['size_min'] = 10240;
					$filters['size_max'] = 51200; // 50KB.
					break;
				case 'large':
					$filters['size_min'] = 51200;
					$filters['size_max'] = 102400; // 100KB.
					break;
			}
		}

		// Date presets.
		if ( isset( $_GET['date_preset'] ) && ! empty( $_GET['date_preset'] ) ) {
			$preset = sanitize_text_field( wp_unslash( $_GET['date_preset'] ) );
			switch ( $preset ) {
				case '24h':
					$filters['date_from'] = gmdate( 'Y-m-d', strtotime( '-1 day' ) );
					$filters['date_to']   = gmdate( 'Y-m-d' );
					break;
				case '7d':
					$filters['date_from'] = gmdate( 'Y-m-d', strtotime( '-7 days' ) );
					$filters['date_to']   = gmdate( 'Y-m-d' );
					break;
				case '30d':
					$filters['date_from'] = gmdate( 'Y-m-d', strtotime( '-30 days' ) );
					$filters['date_to']   = gmdate( 'Y-m-d' );
					break;
			}
		}

		// Sorting.
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'expiration';
		$order   = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'DESC';

		// Count active filters.
		$active_filters = 0;
		if ( ! empty( $filters['status'] ) && 'all' !== $filters['status'] ) {
			$active_filters++;
		}
		if ( ! empty( $filters['size_min'] ) || ! empty( $filters['size_max'] ) ) {
			$active_filters++;
		}
		if ( ! empty( $filters['date_from'] ) || ! empty( $filters['date_to'] ) ) {
			$active_filters++;
		}

		$cache_entries = $cache_manager->get_cache_entries( $per_page, $offset, $search, $filters, $orderby, $order );
		$total_entries = $cache_manager->get_cache_entries_count( $search, $filters );
		$expired_count = count( $cache_manager->get_expired_entries() );
		$cache_health = $cache_manager->get_health();

		include TA_PLUGIN_DIR . 'admin/views/cache-browser-page.php';
	}

	/**
	 * AJAX handler: Delete single cache entry.
	 *
	 * @since 2.0.6
	 * @return void
	 */
	public function ajax_delete_cache_entry() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$cache_key = isset( $_POST['cache_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cache_key'] ) ) : '';

		if ( empty( $cache_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid cache key.', 'third-audience' ) ) );
		}

		$cache_manager = new TA_Cache_Manager();
		$result = $cache_manager->delete( $cache_key );

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Cache entry deleted.', 'third-audience' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to delete cache entry.', 'third-audience' ) ) );
		}
	}

	/**
	 * AJAX handler: Bulk delete cache entries.
	 *
	 * @since 2.0.6
	 * @return void
	 */
	public function ajax_bulk_delete_cache() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$cache_keys = isset( $_POST['cache_keys'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['cache_keys'] ) ) : array();

		if ( empty( $cache_keys ) ) {
			wp_send_json_error( array( 'message' => __( 'No cache keys provided.', 'third-audience' ) ) );
		}

		$cache_manager = new TA_Cache_Manager();
		$result = $cache_manager->delete_many( $cache_keys );

		wp_send_json_success( array(
			'message' => sprintf(
				/* translators: %d: number of entries deleted */
				__( '%d cache entries deleted.', 'third-audience' ),
				count( $cache_keys )
			),
		) );
	}

	/**
	 * AJAX handler: Clear expired cache entries.
	 *
	 * @since 2.0.6
	 * @return void
	 */
	public function ajax_clear_expired_cache() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$cache_manager = new TA_Cache_Manager();
		$count = $cache_manager->cleanup_expired();

		wp_send_json_success( array(
			'message' => sprintf(
				/* translators: %d: number of expired entries */
				__( '%d expired entries cleared.', 'third-audience' ),
				$count
			),
		) );
	}

	/**
	 * AJAX handler: Regenerate cache for a post.
	 *
	 * @since 2.0.6
	 * @return void
	 */
	public function ajax_regenerate_cache() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		if ( empty( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'third-audience' ) ) );
		}

		$cache_manager = new TA_Cache_Manager();
		$result = $cache_manager->regenerate_markdown( $post_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => __( 'Cache regenerated successfully.', 'third-audience' ) ) );
	}

	/**
	 * AJAX handler: View cache content.
	 *
	 * @since 2.0.6
	 * @return void
	 */
	public function ajax_view_cache_content() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$cache_key = isset( $_POST['cache_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cache_key'] ) ) : '';

		if ( empty( $cache_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid cache key.', 'third-audience' ) ) );
		}

		$cache_manager = new TA_Cache_Manager();
		$content = $cache_manager->get( $cache_key );

		if ( false === $content ) {
			wp_send_json_error( array( 'message' => __( 'Cache entry not found.', 'third-audience' ) ) );
		}

		wp_send_json_success( array(
			'content' => $content,
			'size'    => size_format( strlen( $content ) ),
		) );
	}

	/**
	 * AJAX handler: Get cache warmup statistics.
	 *
	 * @since 2.0.6
	 * @return void
	 */
	public function ajax_get_warmup_stats() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$cache_manager = new TA_Cache_Manager();
		$stats = $cache_manager->get_warmup_stats();

		wp_send_json_success( $stats );
	}

	/**
	 * AJAX handler: Warm cache batch.
	 *
	 * @since 2.0.6
	 * @return void
	 */
	public function ajax_warm_cache_batch() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$batch_size = isset( $_POST['batch_size'] ) ? absint( $_POST['batch_size'] ) : 5;
		$offset = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;

		$cache_manager = new TA_Cache_Manager();
		$result = $cache_manager->warm_cache_batch( array(
			'limit'  => $batch_size,
			'offset' => $offset,
		) );

		wp_send_json_success( $result );
	}

	/**
	 * Get recent .md access attempts for live feed.
	 *
	 * @since 2.1.0
	 * @return void
	 */
	public function ajax_get_recent_accesses() {
		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'ta_bot_analytics_feed' ) ) {
			wp_send_json_error( array( 'message' => 'Nonce verification failed' ), 403 );
		}

		// Check capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ), 403 );
		}

		$analytics = TA_Bot_Analytics::get_instance();
		$accesses = $analytics->get_recent_visits( array(), 20, 0 );

		if ( empty( $accesses ) ) {
			wp_send_json_success( array( 'accesses' => array() ) );
		}

		// Format data for frontend.
		$formatted = array();
		foreach ( $accesses as $access ) {
			$formatted[] = array(
				'id'              => intval( $access['id'] ),
				'timestamp'       => $access['visit_timestamp'],
				'url'             => $access['url'],
				'bot_name'        => $access['bot_name'],
				'bot_type'        => $access['bot_type'],
				'cache_status'    => $access['cache_status'],
				'response_time'   => intval( $access['response_time'] ?? 0 ),
				'post_title'      => $access['post_title'] ?? 'Untitled',
			);
		}

		wp_send_json_success( array( 'accesses' => $formatted ) );
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
	 * AJAX handler for regenerating all markdown.
	 *
	 * Clears all pre-generated markdown, forcing regeneration on next access.
	 *
	 * @since 2.1.0
	 * @return void
	 */
	public function ajax_regenerate_all_markdown() {
		$this->security->verify_ajax_request( 'admin_ajax' );

		$cache_manager = new TA_Cache_Manager();
		$cleared       = $cache_manager->clear_pregenerated_markdown();

		$this->logger->info( 'All markdown regenerated (AJAX).', array( 'count' => $cleared ) );

		wp_send_json_success( array(
			/* translators: %d: Number of posts cleared */
			'message' => sprintf( __( 'Cleared pre-generated markdown for %d posts. New markdown will be generated with current settings on next access.', 'third-audience' ), $cleared ),
			'count'   => $cleared,
		) );
	}
}
