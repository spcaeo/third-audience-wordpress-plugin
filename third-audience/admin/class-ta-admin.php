<?php
/**
 * Admin - Settings page and admin functionality.
 *
 * Handles the admin interface, settings registration, and admin page rendering.
 * AJAX handlers are delegated to specialized handler classes (v3.3.1).
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
 * Admin functionality orchestrator for Third Audience plugin.
 * Delegates AJAX and settings operations to specialized handler classes.
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
	 * Post columns instance.
	 *
	 * @var TA_Post_Columns
	 */
	private $post_columns;

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
		$this->security      = TA_Security::get_instance();
		$this->logger        = TA_Logger::get_instance();
		$this->notifications = TA_Notifications::get_instance();
		$this->post_columns  = new TA_Post_Columns();
	}

	/**
	 * Initialize admin functionality.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		add_action( 'admin_init', array( $this, 'handle_export_request' ), 5 );
		add_action( 'admin_init', array( $this, 'handle_digest_download' ), 5 );
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_notices', array( $this, 'display_configuration_notices' ) );

		// Initialize sub-components.
		$this->post_columns->init();

		// AI-Friendliness Score meta box.
		add_action( 'add_meta_boxes', array( $this, 'add_ai_score_metabox' ) );
		add_action( 'save_post', array( $this, 'calculate_ai_score_on_save' ), 10, 2 );

		// Admin post handlers (non-settings).
		add_action( 'admin_post_ta_clear_cache', array( $this, 'handle_clear_cache' ) );
		add_action( 'admin_post_ta_test_smtp', array( $this, 'handle_test_smtp' ) );
		add_action( 'admin_post_ta_clear_errors', array( $this, 'handle_clear_errors' ) );
		add_action( 'admin_post_ta_export_errors', array( $this, 'handle_export_errors' ) );

		// Initialize delegated handler classes.
		$this->init_handler_classes();

		// Core AJAX handlers that remain in this class.
		add_action( 'wp_ajax_ta_test_smtp', array( $this, 'ajax_test_smtp' ) );
		add_action( 'wp_ajax_ta_clear_all_visits', array( $this, 'ajax_clear_all_visits' ) );
		add_action( 'wp_ajax_ta_get_recent_errors', array( $this, 'ajax_get_recent_errors' ) );
		add_action( 'wp_ajax_ta_update_robots_txt', array( $this, 'ajax_update_robots_txt' ) );
		add_action( 'wp_ajax_ta_dismiss_alert', array( $this, 'ajax_dismiss_alert' ) );
		add_action( 'wp_ajax_ta_recalculate_ai_score', array( $this, 'ajax_recalculate_ai_score' ) );
		add_action( 'wp_ajax_ta_test_ga4_connection', array( $this, 'ajax_test_ga4_connection' ) );
		add_action( 'wp_ajax_ta_send_test_digest', array( $this, 'ajax_send_test_digest' ) );
		add_action( 'wp_ajax_ta_redetect_environment', array( $this, 'ajax_redetect_environment' ) );
		add_action( 'wp_ajax_ta_force_rest_api_mode', array( $this, 'ajax_force_rest_api_mode' ) );
	}

	/**
	 * Initialize delegated handler classes.
	 *
	 * @since 3.3.1
	 * @return void
	 */
	private function init_handler_classes() {
		// Cache AJAX handlers.
		$cache_ajax = TA_Admin_AJAX_Cache::get_instance();
		$cache_ajax->register_hooks();

		// Analytics AJAX handlers.
		$analytics_ajax = TA_Admin_AJAX_Analytics::get_instance();
		$analytics_ajax->register_hooks();

		// Benchmark AJAX handlers.
		$benchmark_ajax = TA_Admin_AJAX_Benchmark::get_instance();
		$benchmark_ajax->register_hooks();

		// Settings handlers.
		$settings = TA_Admin_Settings::get_instance();
		$settings->register_hooks();
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
			'bot-analytics_page_third-audience-ai-citations',
			'bot-analytics_page_third-audience-cache-browser',
			'bot-analytics_page_third-audience-system-health',
			'bot-analytics_page_third-audience-about',
			'bot-analytics_page_third-audience-competitor-benchmarking',
		);

		if ( in_array( $hook, $ta_pages, true ) ) {
			wp_enqueue_style( 'ta-apple-theme', TA_PLUGIN_URL . 'admin/css/apple-theme.css', array(), TA_VERSION );
		}

		// Settings page.
		if ( 'settings_page_third-audience' === $hook ) {
			wp_enqueue_style( 'ta-admin', TA_PLUGIN_URL . 'admin/css/admin.css', array(), TA_VERSION );
			wp_enqueue_style( 'ta-settings', TA_PLUGIN_URL . 'admin/css/settings.css', array( 'ta-admin' ), TA_VERSION );
			wp_enqueue_script( 'ta-admin', TA_PLUGIN_URL . 'admin/js/admin.js', array( 'jquery' ), TA_VERSION, true );
			wp_localize_script( 'ta-admin', 'taAdmin', array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => $this->security->create_nonce( 'admin_ajax' ),
				'homeUrl' => trailingslashit( home_url() ),
				'i18n'    => array(
					'testing'            => __( 'Testing...', 'third-audience' ),
					'clearing'           => __( 'Clearing...', 'third-audience' ),
					'success'            => __( 'Success!', 'third-audience' ),
					'error'              => __( 'Error', 'third-audience' ),
					'confirmClear'       => __( 'Are you sure you want to clear all cached items?', 'third-audience' ),
					'confirmClearErrors' => __( 'Are you sure you want to clear all error logs?', 'third-audience' ),
				),
			) );
			wp_localize_script( 'ta-admin', 'wpAjax', array( 'nonce' => wp_create_nonce( 'ta_dismiss_alert' ) ) );
		}

		// Bot Analytics page.
		if ( 'toplevel_page_third-audience-bot-analytics' === $hook ) {
			wp_enqueue_script( 'chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', array(), '4.4.0', true );
			wp_enqueue_style( 'ta-bot-analytics', TA_PLUGIN_URL . 'admin/css/bot-analytics.css', array(), TA_VERSION );
			wp_enqueue_script( 'ta-bot-analytics', TA_PLUGIN_URL . 'admin/js/bot-analytics.js', array( 'jquery', 'chartjs' ), TA_VERSION, true );
		}

		// AI Citations page.
		if ( 'bot-analytics_page_third-audience-ai-citations' === $hook ) {
			wp_enqueue_style( 'ta-bot-analytics', TA_PLUGIN_URL . 'admin/css/bot-analytics.css', array(), TA_VERSION );
			wp_enqueue_script( 'ta-bot-analytics', TA_PLUGIN_URL . 'admin/js/bot-analytics.js', array( 'jquery' ), TA_VERSION, true );
		}

		// Cache Browser page.
		if ( 'bot-analytics_page_third-audience-cache-browser' === $hook ) {
			wp_enqueue_style( 'ta-bot-analytics', TA_PLUGIN_URL . 'admin/css/bot-analytics.css', array(), TA_VERSION );
			wp_enqueue_style( 'ta-cache-browser', TA_PLUGIN_URL . 'admin/css/cache-browser.css', array( 'ta-bot-analytics' ), TA_VERSION );
			wp_enqueue_script( 'ta-cache-browser', TA_PLUGIN_URL . 'admin/js/cache-browser.js', array( 'jquery' ), TA_VERSION, true );
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
			wp_enqueue_style( 'ta-bot-management', TA_PLUGIN_URL . 'admin/css/bot-management.css', array(), TA_VERSION );
		}

		// System Health page.
		if ( 'bot-analytics_page_third-audience-system-health' === $hook ) {
			wp_enqueue_style( 'ta-system-health', TA_PLUGIN_URL . 'admin/css/system-health.css', array(), TA_VERSION );
		}

		// Competitor Benchmarking page.
		if ( 'bot-analytics_page_third-audience-competitor-benchmarking' === $hook ) {
			wp_enqueue_style( 'ta-competitor-benchmarking', TA_PLUGIN_URL . 'admin/css/competitor-benchmarking.css', array( 'ta-apple-theme' ), TA_VERSION );
			wp_enqueue_script( 'ta-competitor-benchmarking', TA_PLUGIN_URL . 'admin/js/competitor-benchmarking.js', array( 'jquery' ), TA_VERSION, true );
			wp_localize_script( 'ta-competitor-benchmarking', 'taCompetitorBenchmarking', array(
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => $this->security->create_nonce( 'competitor_benchmarking' ),
				'resultsUrl' => admin_url( 'admin.php?page=third-audience-competitor-benchmarking&tab=results' ),
				'i18n'       => array(
					'saving'                  => __( 'Saving...', 'third-audience' ),
					'generating'              => __( 'Generating...', 'third-audience' ),
					'error'                   => __( 'An error occurred. Please try again.', 'third-audience' ),
					'selectCompetitor'        => __( 'Please select a competitor first.', 'third-audience' ),
					'confirmDeleteCompetitor' => __( 'Delete competitor "%s"? All associated test results will remain.', 'third-audience' ),
					'confirmDeleteResult'     => __( 'Delete this test result?', 'third-audience' ),
					'usePrompt'               => __( 'Use in Test', 'third-audience' ),
					'copied'                  => __( 'Copied!', 'third-audience' ),
					'copyFailed'              => __( 'Failed to copy. Please copy manually.', 'third-audience' ),
					'fillFields'              => __( 'Fill in the fields above to generate a custom prompt...', 'third-audience' ),
				),
			) );
		}

		// AI Score meta box (post editor).
		$screen = get_current_screen();
		if ( $screen && in_array( $screen->base, array( 'post', 'page' ), true ) ) {
			wp_enqueue_style( 'ta-ai-score', TA_PLUGIN_URL . 'admin/css/ai-score.css', array(), TA_VERSION );
			wp_enqueue_script( 'ta-ai-score', TA_PLUGIN_URL . 'admin/js/ai-score.js', array( 'jquery' ), TA_VERSION, true );
			wp_localize_script( 'ta-ai-score', 'taAIScore', array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => $this->security->create_nonce( 'ai_score' ),
			) );
		}
	}

	/**
	 * Handle export request (CSV or JSON) before any output.
	 *
	 * @since 2.0.5
	 * @return void
	 */
	public function handle_export_request() {
		if ( ! isset( $_GET['page'] ) ) {
			return;
		}

		$page = $_GET['page'];
		if ( ! in_array( $page, array( 'third-audience-bot-analytics', 'third-audience-ai-citations' ), true ) ) {
			return;
		}

		if ( ! isset( $_GET['action'] ) || 'export' !== $_GET['action'] ) {
			return;
		}

		$nonce_action = ( 'third-audience-ai-citations' === $page ) ? 'ta_export_citations' : 'ta_export_analytics';

		if ( ! check_admin_referer( $nonce_action ) ) {
			wp_die( esc_html__( 'Invalid security token.', 'third-audience' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'third-audience' ) );
		}

		$export_format = isset( $_GET['export_format'] ) ? sanitize_text_field( wp_unslash( $_GET['export_format'] ) ) : 'csv';
		$export_type   = isset( $_GET['export_type'] ) ? sanitize_text_field( wp_unslash( $_GET['export_type'] ) ) : 'detailed';

		if ( ! in_array( $export_format, array( 'csv', 'json' ), true ) ) {
			$export_format = 'csv';
		}
		if ( ! in_array( $export_type, array( 'detailed', 'summary' ), true ) ) {
			$export_type = 'detailed';
		}

		$analytics = TA_Bot_Analytics::get_instance();
		$filters   = array();

		if ( 'third-audience-ai-citations' === $page ) {
			if ( ! empty( $_GET['platform'] ) ) {
				$filters['platform'] = sanitize_text_field( wp_unslash( $_GET['platform'] ) );
			}
			if ( ! empty( $_GET['date_from'] ) ) {
				$filters['date_from'] = sanitize_text_field( wp_unslash( $_GET['date_from'] ) );
			}
			if ( ! empty( $_GET['date_to'] ) ) {
				$filters['date_to'] = sanitize_text_field( wp_unslash( $_GET['date_to'] ) );
			}
			if ( ! empty( $_GET['search'] ) ) {
				$filters['search'] = sanitize_text_field( wp_unslash( $_GET['search'] ) );
			}

			$this->export_citations_to_csv( $filters );
			exit;
		}

		if ( ! empty( $_GET['bot_type'] ) ) {
			$filters['bot_type'] = sanitize_text_field( wp_unslash( $_GET['bot_type'] ) );
		}
		if ( ! empty( $_GET['date_from'] ) ) {
			$filters['date_from'] = sanitize_text_field( wp_unslash( $_GET['date_from'] ) );
		}
		if ( ! empty( $_GET['date_to'] ) ) {
			$filters['date_to'] = sanitize_text_field( wp_unslash( $_GET['date_to'] ) );
		}

		if ( 'json' === $export_format ) {
			$analytics->export_to_json( $filters, $export_type );
		} else {
			$analytics->export_to_csv( $filters, $export_type );
		}
	}

	/**
	 * Export AI Citations data to CSV.
	 *
	 * @since 2.3.0
	 * @param array $filters Filter criteria.
	 * @return void
	 */
	private function export_citations_to_csv( $filters = array() ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ta_bot_analytics';

		$where_clauses = array( "traffic_type = 'citation_click'" );

		if ( ! empty( $filters['platform'] ) ) {
			$where_clauses[] = $wpdb->prepare( 'ai_platform = %s', $filters['platform'] );
		}
		if ( ! empty( $filters['date_from'] ) ) {
			$where_clauses[] = $wpdb->prepare( 'DATE(visit_timestamp) >= %s', $filters['date_from'] );
		}
		if ( ! empty( $filters['date_to'] ) ) {
			$where_clauses[] = $wpdb->prepare( 'DATE(visit_timestamp) <= %s', $filters['date_to'] );
		}
		if ( ! empty( $filters['search'] ) ) {
			$search_term     = '%' . $wpdb->esc_like( $filters['search'] ) . '%';
			$where_clauses[] = $wpdb->prepare( '(url LIKE %s OR post_title LIKE %s OR search_query LIKE %s)', $search_term, $search_term, $search_term );
		}

		$where_sql = implode( ' AND ', $where_clauses );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			"SELECT id, bot_type, bot_name, user_agent, url, post_id, post_type, post_title, request_method,
				cache_status, response_time, response_size, ip_address, referer, country_code, traffic_type,
				ai_platform, search_query, referer_source, referer_medium, detection_method, confidence_score,
				visit_timestamp, created_at
			FROM {$table_name}
			WHERE {$where_sql}
			ORDER BY visit_timestamp DESC",
			ARRAY_A
		);

		$filename = 'ai-citations-' . gmdate( 'Y-m-d-H-i-s' ) . '.csv';

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );
		fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

		fputcsv( $output, array( 'Third Audience AI Citations Export' ) );
		fputcsv( $output, array( 'Generated', gmdate( 'Y-m-d H:i:s' ) . ' UTC' ) );
		fputcsv( $output, array( 'Total Records', count( $results ) ) );
		fputcsv( $output, array() );

		fputcsv( $output, array(
			'ID', 'Bot Type', 'Bot Name', 'User Agent', 'URL', 'Post ID', 'Post Type', 'Post Title',
			'Request Method', 'Cache Status', 'Response Time (ms)', 'Response Size', 'IP Address', 'Referer',
			'Country Code', 'Traffic Type', 'AI Platform', 'Search Query', 'Referer Source', 'Referer Medium',
			'Detection Method', 'Confidence Score', 'Visit Time (UTC)', 'Created At (UTC)',
		) );

		foreach ( $results as $row ) {
			fputcsv( $output, array_values( $row ) );
		}

		fclose( $output );
	}

	/**
	 * Add settings page to admin menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_settings_page() {
		add_options_page( __( 'Third Audience Settings', 'third-audience' ), __( 'Third Audience', 'third-audience' ), 'manage_options', 'third-audience', array( $this, 'render_settings_page' ) );
		add_menu_page( __( 'Bot Analytics', 'third-audience' ), __( 'Bot Analytics', 'third-audience' ), 'manage_options', 'third-audience-bot-analytics', array( $this, 'render_bot_analytics_page' ), 'dashicons-chart-line', 30 );
		add_submenu_page( 'third-audience-bot-analytics', __( 'Bot Management', 'third-audience' ), __( 'Bot Management', 'third-audience' ), 'manage_options', 'third-audience-bot-management', array( $this, 'render_bot_management_page' ) );
		add_submenu_page( 'third-audience-bot-analytics', __( 'AI Citations', 'third-audience' ), __( 'AI Citations', 'third-audience' ), 'manage_options', 'third-audience-ai-citations', array( $this, 'render_ai_citations_page' ) );
		add_submenu_page( 'third-audience-bot-analytics', __( 'Cache Browser', 'third-audience' ), __( 'Cache Browser', 'third-audience' ), 'manage_options', 'third-audience-cache-browser', array( $this, 'render_cache_browser_page' ) );
		add_submenu_page( 'third-audience-bot-analytics', __( 'System Health', 'third-audience' ), __( 'System Health', 'third-audience' ), 'manage_options', 'third-audience-system-health', array( $this, 'render_system_health_page' ) );
		// Competitor Benchmarking removed from v3.3.2 - feature disabled.
		add_submenu_page( 'third-audience-bot-analytics', __( 'Email Digest', 'third-audience' ), __( 'Email Digest', 'third-audience' ), 'manage_options', 'third-audience-email-digest', array( $this, 'render_email_digest_page' ) );
		add_submenu_page( 'third-audience-bot-analytics', __( 'About', 'third-audience' ), __( 'About', 'third-audience' ), 'manage_options', 'third-audience-about', array( $this, 'render_about_page' ) );
		add_submenu_page( null, __( 'Citation Alerts', 'third-audience' ), __( 'Citation Alerts', 'third-audience' ), 'manage_options', 'third-audience-citation-alerts', array( $this, 'render_citation_alerts_page' ) );
	}

	/**
	 * Register settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_settings() {
		register_setting( 'ta_settings', 'ta_cache_ttl', array( 'type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 86400 ) );
		register_setting( 'ta_settings', 'ta_enabled_post_types', array( 'type' => 'array', 'sanitize_callback' => array( $this->security, 'sanitize_post_types' ), 'default' => array( 'post', 'page' ) ) );
		register_setting( 'ta_settings', 'ta_enable_content_negotiation', array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true ) );
		register_setting( 'ta_settings', 'ta_enable_discovery_tags', array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true ) );
		register_setting( 'ta_settings', 'ta_enable_pre_generation', array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true ) );
		register_setting( 'ta_settings', 'ta_homepage_md_pattern', array( 'type' => 'string', 'sanitize_callback' => array( $this->security, 'sanitize_text' ), 'default' => 'index.md' ) );
		register_setting( 'ta_settings', 'ta_homepage_md_pattern_custom', array( 'type' => 'string', 'sanitize_callback' => array( $this->security, 'sanitize_text' ), 'default' => '' ) );
		register_setting( 'ta_settings', 'ta_enable_enhanced_metadata', array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true ) );
		register_setting( 'ta_settings', 'ta_metadata_word_count', array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true ) );
		register_setting( 'ta_settings', 'ta_metadata_reading_time', array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true ) );
		register_setting( 'ta_settings', 'ta_metadata_summary', array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true ) );
		register_setting( 'ta_settings', 'ta_metadata_language', array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true ) );
		register_setting( 'ta_settings', 'ta_metadata_last_modified', array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true ) );
		register_setting( 'ta_settings', 'ta_metadata_schema_type', array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true ) );
		register_setting( 'ta_settings', 'ta_metadata_related_posts', array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => true ) );
		register_setting( 'ta_settings', 'ta_ga4_enabled', array( 'type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false ) );
		register_setting( 'ta_settings', 'ta_ga4_measurement_id', array( 'type' => 'string', 'sanitize_callback' => array( $this->security, 'sanitize_text' ), 'default' => '' ) );
		register_setting( 'ta_settings', 'ta_ga4_api_secret', array( 'type' => 'string', 'sanitize_callback' => array( $this->security, 'sanitize_text' ), 'default' => '' ) );
	}

	/**
	 * Display configuration notices if plugin is not properly set up.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function display_configuration_notices() {
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'third-audience' ) === false ) {
			return;
		}

		if ( ! TA_Local_Converter::is_library_available() ) {
			?>
			<div class="notice notice-error">
				<p><strong><?php esc_html_e( 'Third Audience - Library Missing', 'third-audience' ); ?></strong></p>
				<p><?php printf( esc_html__( 'The HTML to Markdown conversion library is not installed. Please check the %s page for installation instructions.', 'third-audience' ), '<a href="' . esc_url( admin_url( 'admin.php?page=third-audience-system-health' ) ) . '">' . esc_html__( 'System Health', 'third-audience' ) . '</a>' ); ?></p>
			</div>
			<?php
			return;
		}

		global $wpdb;
		$table_name   = $wpdb->prefix . 'ta_bot_analytics';
		$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) === $table_name;

		if ( ! $table_exists ) {
			?>
			<div class="notice notice-warning">
				<p><strong><?php esc_html_e( 'Third Audience Analytics Table Missing', 'third-audience' ); ?></strong></p>
				<p><?php esc_html_e( 'The bot analytics table was not created. Please deactivate and reactivate the plugin to create the required database tables.', 'third-audience' ); ?></p>
			</div>
			<?php
		}
	}

	// =========================================================================
	// RENDER METHODS
	// =========================================================================

	/**
	 * Render settings page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_settings_page() {
		$this->security->verify_admin_capability();
		settings_errors( 'ta_messages' );
		$cache_manager         = new TA_Cache_Manager();
		$cache_stats           = $cache_manager->get_stats();
		$error_stats           = $this->logger->get_stats();
		$recent_errors         = $this->logger->get_recent_errors( 10 );
		$smtp_settings         = $this->notifications->get_smtp_settings();
		$notification_settings = $this->notifications->get_notification_settings();
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
	 * Render AI Citations page.
	 *
	 * @since 2.2.0
	 * @return void
	 */
	public function render_ai_citations_page() {
		$this->security->verify_admin_capability();
		$analytics = TA_Bot_Analytics::get_instance();
		include TA_PLUGIN_DIR . 'admin/views/ai-citations-page.php';
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
	 * Render Competitor Benchmarking page.
	 *
	 * @since 3.1.0
	 * @return void
	 */
	public function render_competitor_benchmarking_page() {
		$this->security->verify_admin_capability();

		if ( isset( $_GET['action'] ) && 'export' === $_GET['action'] ) {
			check_admin_referer( 'ta_export_benchmarks' );
			$benchmarking = TA_Competitor_Benchmarking::get_instance();
			$filters      = array();
			if ( ! empty( $_GET['competitor_url'] ) ) {
				$filters['competitor_url'] = sanitize_text_field( wp_unslash( $_GET['competitor_url'] ) );
			}
			if ( ! empty( $_GET['ai_platform'] ) ) {
				$filters['ai_platform'] = sanitize_text_field( wp_unslash( $_GET['ai_platform'] ) );
			}
			if ( ! empty( $_GET['date_from'] ) ) {
				$filters['date_from'] = sanitize_text_field( wp_unslash( $_GET['date_from'] ) );
			}
			if ( ! empty( $_GET['date_to'] ) ) {
				$filters['date_to'] = sanitize_text_field( wp_unslash( $_GET['date_to'] ) );
			}
			$benchmarking->export_tests_to_csv( $filters );
			exit;
		}

		include TA_PLUGIN_DIR . 'admin/views/competitor-benchmarking-page.php';
	}

	/**
	 * Handle digest report download request.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	public function handle_digest_download() {
		if ( ! isset( $_GET['page'] ) || 'third-audience-email-digest' !== $_GET['page'] ) {
			return;
		}
		if ( ! isset( $_GET['action'] ) || 'download_report' !== $_GET['action'] ) {
			return;
		}
		if ( ! check_admin_referer( 'ta_download_digest_report' ) ) {
			wp_die( esc_html__( 'Invalid security token.', 'third-audience' ) );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'third-audience' ) );
		}

		// Get period from URL parameter (default: 24 hours).
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$period = isset( $_GET['period'] ) ? absint( $_GET['period'] ) : 24;

		// Generate report with specified period.
		$digest  = TA_Email_Digest::get_instance();
		$data    = $digest->gather_digest_data( $period );
		$content = $digest->generate_md_report( $data );

		$period_label = $period >= 168 ? '7days' : '24hours';
		$filename     = 'third-audience-report-' . $period_label . '-' . gmdate( 'Y-m-d-H-i' ) . '.md';
		header( 'Content-Type: text/markdown; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Content-Length: ' . strlen( $content ) );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
		echo $content;
		exit;
	}

	/**
	 * Render Email Digest page.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	public function render_email_digest_page() {
		$this->security->verify_admin_capability();
		include TA_PLUGIN_DIR . 'admin/views/email-digest-settings.php';
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
		$cache_stats   = $cache_manager->get_stats();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_page = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
		$per_page     = 50;
		$offset       = ( $current_page - 1 ) * $per_page;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$search = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$filters = array(
			'status'    => isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : 'all',
			'size_min'  => isset( $_GET['size_min'] ) ? absint( $_GET['size_min'] ) : 0,
			'size_max'  => isset( $_GET['size_max'] ) ? absint( $_GET['size_max'] ) : 0,
			'date_from' => isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '',
			'date_to'   => isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '',
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['size_preset'] ) && ! empty( $_GET['size_preset'] ) ) {
			$preset = sanitize_text_field( wp_unslash( $_GET['size_preset'] ) );
			switch ( $preset ) {
				case 'small':
					$filters['size_min'] = 0;
					$filters['size_max'] = 10240;
					break;
				case 'medium':
					$filters['size_min'] = 10240;
					$filters['size_max'] = 51200;
					break;
				case 'large':
					$filters['size_min'] = 51200;
					$filters['size_max'] = 102400;
					break;
			}
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
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

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'expiration';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'DESC';

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
		$cache_health  = $cache_manager->get_health();

		include TA_PLUGIN_DIR . 'admin/views/cache-browser-page.php';
	}

	/**
	 * Render Citation Alerts page.
	 *
	 * @since 2.8.0
	 * @return void
	 */
	public function render_citation_alerts_page() {
		$this->security->verify_admin_capability();

		if ( ! class_exists( 'TA_Citation_Alerts' ) ) {
			echo '<div class="wrap"><h1>' . esc_html__( 'Citation Alerts', 'third-audience' ) . '</h1>';
			echo '<p>' . esc_html__( 'Citation alerts system is not available.', 'third-audience' ) . '</p></div>';
			return;
		}

		$citation_alerts = TA_Citation_Alerts::get_instance();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_page = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
		$per_page     = 50;
		$offset       = ( $current_page - 1 ) * $per_page;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$filters = array(
			'alert_type' => isset( $_GET['alert_type'] ) ? sanitize_text_field( wp_unslash( $_GET['alert_type'] ) ) : '',
			'severity'   => isset( $_GET['severity'] ) ? sanitize_text_field( wp_unslash( $_GET['severity'] ) ) : '',
			'dismissed'  => isset( $_GET['dismissed'] ) ? absint( $_GET['dismissed'] ) : null,
		);

		$alert_history = $citation_alerts->get_alert_history( array(
			'limit'      => $per_page,
			'offset'     => $offset,
			'alert_type' => $filters['alert_type'],
			'severity'   => $filters['severity'],
			'dismissed'  => $filters['dismissed'],
		) );

		$statistics = $citation_alerts->get_statistics();

		include TA_PLUGIN_DIR . 'admin/views/citation-alerts-page.php';
	}

	// =========================================================================
	// POST HANDLERS
	// =========================================================================

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

		add_settings_error( 'ta_messages', 'ta_cache_cleared', sprintf( __( 'Cleared %d cached items.', 'third-audience' ), $cleared ), 'success' );
		set_transient( 'settings_errors', get_settings_errors(), 30 );

		wp_safe_redirect( add_query_arg( array( 'page' => 'third-audience', 'settings-updated' => 'true' ), admin_url( 'options-general.php' ) ) );
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
			add_settings_error( 'ta_messages', 'ta_smtp_test_failed', __( 'SMTP test failed: ', 'third-audience' ) . $result->get_error_message(), 'error' );
		} else {
			$this->logger->info( 'SMTP test successful.' );
			add_settings_error( 'ta_messages', 'ta_smtp_test_success', __( 'SMTP test email sent successfully!', 'third-audience' ), 'success' );
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

		add_settings_error( 'ta_messages', 'ta_errors_cleared', __( 'Error logs cleared.', 'third-audience' ), 'success' );

		$this->redirect_to_settings( 'logs' );
	}

	/**
	 * Handle export errors action.
	 *
	 * @since 3.3.2
	 * @return void
	 */
	public function handle_export_errors() {
		$this->security->verify_admin_capability();

		// Verify nonce.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'ta_export_errors' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'third-audience' ), esc_html__( 'Error', 'third-audience' ), array( 'response' => 403 ) );
		}

		// Get errors and stats.
		$errors = $this->logger->get_recent_errors( 100 ); // Get up to 100 errors.
		$stats  = $this->logger->get_stats();

		// Build export data.
		$export_data = array(
			'exported_at'   => gmdate( 'Y-m-d H:i:s' ) . ' UTC',
			'site_url'      => home_url(),
			'plugin_version' => TA_VERSION,
			'php_version'   => PHP_VERSION,
			'wp_version'    => get_bloginfo( 'version' ),
			'statistics'    => $stats,
			'errors'        => $errors,
		);

		// Set headers for JSON download.
		$filename = 'third-audience-logs-' . gmdate( 'Y-m-d-His' ) . '.json';
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wp_json_encode( $export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		exit;
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

		$args = array( 'page' => 'third-audience', 'settings-updated' => 'true' );
		if ( ! empty( $tab ) ) {
			$args['tab'] = $tab;
		}

		wp_safe_redirect( add_query_arg( $args, admin_url( 'options-general.php' ) ) );
		exit;
	}

	// =========================================================================
	// AJAX HANDLERS (Core - not delegated)
	// =========================================================================

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
			$this->logger->error( 'SMTP test failed (AJAX).', array( 'error' => $result->get_error_message() ) );
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => __( 'Test email sent successfully!', 'third-audience' ) ) );
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

		wp_send_json_success( array( 'errors' => $errors, 'stats' => $this->logger->get_stats() ) );
	}

	/**
	 * AJAX handler for updating robots.txt.
	 *
	 * @since 2.8.0
	 * @return void
	 */
	public function ajax_update_robots_txt() {
		check_ajax_referer( 'ta_update_robots_txt', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to update robots.txt.', 'third-audience' ) ) );
		}

		$rule = isset( $_POST['rule'] ) ? sanitize_text_field( wp_unslash( $_POST['rule'] ) ) : '';

		if ( empty( $rule ) ) {
			wp_send_json_error( array( 'message' => __( 'No rule specified.', 'third-audience' ) ) );
		}

		$robots_file    = ABSPATH . 'robots.txt';
		$robots_content = file_exists( $robots_file ) ? file_get_contents( $robots_file ) : '';

		if ( strpos( $robots_content, $rule ) !== false ) {
			wp_send_json_success( array( 'message' => __( 'Rule already exists in robots.txt.', 'third-audience' ) ) );
			return;
		}

		if ( strpos( $robots_content, 'User-agent: *' ) !== false ) {
			$robots_content = str_replace( 'User-agent: *', "User-agent: *\n" . $rule, $robots_content );
		} else {
			$robots_content .= "\n\nUser-agent: *\n" . $rule . "\n";
		}

		$result = file_put_contents( $robots_file, $robots_content );

		if ( false === $result ) {
			wp_send_json_error( array( 'message' => __( 'Failed to write to robots.txt. Check file permissions.', 'third-audience' ) ) );
		}

		$this->logger->info( 'Robots.txt updated via AJAX.', array( 'rule' => $rule ) );

		wp_send_json_success( array( 'message' => __( 'Robots.txt updated successfully!', 'third-audience' ) ) );
	}

	/**
	 * AJAX handler for dismissing citation alerts.
	 *
	 * @since 2.8.0
	 * @return void
	 */
	public function ajax_dismiss_alert() {
		check_ajax_referer( 'ta_dismiss_alert', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'third-audience' ) ) );
			return;
		}

		$alert_id = isset( $_POST['alert_id'] ) ? absint( $_POST['alert_id'] ) : 0;

		if ( empty( $alert_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid alert ID.', 'third-audience' ) ) );
			return;
		}

		if ( ! class_exists( 'TA_Citation_Alerts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Citation alerts system not available.', 'third-audience' ) ) );
			return;
		}

		$citation_alerts = TA_Citation_Alerts::get_instance();
		$result          = $citation_alerts->dismiss_alert( $alert_id );

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Alert dismissed.', 'third-audience' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to dismiss alert.', 'third-audience' ) ) );
		}
	}

	/**
	 * AJAX handler for sending test digest email.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	public function ajax_send_test_digest() {
		check_ajax_referer( 'ta_test_digest', 'nonce' );
		$this->security->verify_admin_capability();

		$digest = TA_Email_Digest::get_instance();
		$result = $digest->send_test_digest();

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Test email sent successfully.', 'third-audience' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to send test email. Check your SMTP settings.', 'third-audience' ) ) );
		}
	}

	/**
	 * AJAX handler: Test GA4 connection.
	 *
	 * @since 3.0.0
	 * @return void
	 */
	public function ajax_test_ga4_connection() {
		$this->security->verify_ajax_request( 'admin_ajax' );

		$measurement_id = isset( $_POST['measurement_id'] ) ? $this->security->sanitize_text( $_POST['measurement_id'] ) : '';
		$api_secret     = isset( $_POST['api_secret'] ) ? $this->security->sanitize_text( $_POST['api_secret'] ) : '';

		if ( empty( $measurement_id ) || empty( $api_secret ) ) {
			wp_send_json_error( array( 'message' => __( 'Measurement ID and API Secret are required.', 'third-audience' ) ) );
		}

		if ( ! class_exists( 'TA_GA4_Integration' ) ) {
			wp_send_json_error( array( 'message' => __( 'GA4 Integration class not found.', 'third-audience' ) ) );
		}

		$ga4    = TA_GA4_Integration::get_instance();
		$result = $ga4->test_connection( $measurement_id, $api_secret );

		if ( is_wp_error( $result ) ) {
			$this->logger->error( 'GA4 connection test failed (AJAX).', array( 'error' => $result->get_error_message() ) );
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => $result['message'] ) );
	}

	// =========================================================================
	// AI SCORE META BOX
	// =========================================================================

	/**
	 * Add AI-Friendliness Score meta box.
	 *
	 * @since 2.8.0
	 * @return void
	 */
	public function add_ai_score_metabox() {
		$enabled_post_types = get_option( 'ta_enabled_post_types', array( 'post', 'page' ) );

		foreach ( $enabled_post_types as $post_type ) {
			add_meta_box( 'ta_ai_score_metabox', __( 'AI-Friendliness Score', 'third-audience' ), array( $this, 'render_ai_score_metabox' ), $post_type, 'side', 'high' );
		}
	}

	/**
	 * Render AI-Friendliness Score meta box.
	 *
	 * @since 2.8.0
	 * @param WP_Post $post Post object.
	 * @return void
	 */
	public function render_ai_score_metabox( $post ) {
		$post_id         = $post->ID;
		$analyzer        = TA_Content_Analyzer::get_instance();
		$score           = $analyzer->get_cached_score( $post_id );
		$score_details   = get_post_meta( $post_id, '_ta_ai_score_details', true );
		$recommendations = array();

		if ( $score && $score_details ) {
			$recommendations = $analyzer->get_content_recommendations( $post_id, $score_details );
		}

		include TA_PLUGIN_DIR . 'admin/views/ai-score-metabox.php';
	}

	/**
	 * Calculate AI-Friendliness score when post is saved.
	 *
	 * @since 2.8.0
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @return void
	 */
	public function calculate_ai_score_on_save( $post_id, $post ) {
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		$enabled_post_types = get_option( 'ta_enabled_post_types', array( 'post', 'page' ) );
		if ( ! in_array( $post->post_type, $enabled_post_types, true ) ) {
			return;
		}

		if ( 'publish' !== $post->post_status ) {
			return;
		}

		$this->recalculate_ai_score( $post_id );
	}

	/**
	 * Recalculate AI-Friendliness score for a post.
	 *
	 * @since 2.8.0
	 * @param int $post_id Post ID.
	 * @return array|null Score data or null on failure.
	 */
	private function recalculate_ai_score( $post_id ) {
		$analyzer   = TA_Content_Analyzer::get_instance();
		$score_data = $analyzer->calculate_ai_friendliness_score( $post_id );

		if ( null === $score_data ) {
			return null;
		}

		update_post_meta( $post_id, '_ta_ai_score', $score_data['score'] );
		update_post_meta( $post_id, '_ta_ai_score_details', $score_data );

		return $score_data;
	}

	/**
	 * AJAX handler: Recalculate AI-Friendliness score.
	 *
	 * @since 2.8.0
	 * @return void
	 */
	public function ajax_recalculate_ai_score() {
		$this->security->verify_ajax_request( 'ai_score' );

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		if ( empty( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'third-audience' ) ) );
		}

		$score_data = $this->recalculate_ai_score( $post_id );

		if ( null === $score_data ) {
			wp_send_json_error( array( 'message' => __( 'Failed to calculate score.', 'third-audience' ) ) );
		}

		wp_send_json_success( array(
			'message' => __( 'Score recalculated successfully.', 'third-audience' ),
			'score'   => $score_data['score'],
		) );
	}

	/**
	 * AJAX handler to re-detect environment.
	 *
	 * Clears the stored environment detection and forces a fresh detection.
	 * This is useful when REST API status changes or needs to be rechecked.
	 *
	 * @since 3.4.1
	 * @return void
	 */
	public function ajax_redetect_environment() {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ta-redetect-env' ) ) {
			wp_send_json_error( __( 'Security check failed.', 'third-audience' ) );
		}

		// Verify user capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You do not have permission to perform this action.', 'third-audience' ) );
		}

		// Delete the old environment detection setting.
		delete_option( 'ta_environment_detection' );

		// IMPORTANT: Run security bypass BEFORE detection to ensure REST API is whitelisted.
		if ( class_exists( 'TA_Security_Bypass' ) ) {
			$security_bypass  = new TA_Security_Bypass();
			$security_results = $security_bypass->auto_configure_on_activation();

			if ( $this->logger ) {
				$this->logger->info( 'Security plugins reconfigured before re-detection', $security_results );
			}
		}

		// IMPORTANT: Give security plugins time to apply changes.
		// Same 2-second delay as activation hook to ensure cache is cleared.
		sleep( 2 );

		// Force re-detection using the Environment Detector class.
		if ( class_exists( 'TA_Environment_Detector' ) ) {
			$detector = new TA_Environment_Detector();
			$new_env  = $detector->detect_full_environment();
			update_option( 'ta_environment_detection', $new_env );

			$rest_api_accessible = ! empty( $new_env['rest_api']['accessible'] );

			// Log the action.
			if ( $this->logger ) {
				$this->logger->info(
					'Environment re-detected',
					array(
						'rest_api_accessible' => $rest_api_accessible,
						'user_id'             => get_current_user_id(),
					)
				);
			}

			wp_send_json_success( array(
				'message'              => __( 'Environment re-detected successfully! Page will reload to show updated status.', 'third-audience' ),
				'rest_api_accessible'  => $rest_api_accessible,
				'detection_time'       => $new_env['detection_time'],
			) );
		} else {
			wp_send_json_error( __( 'Environment Detector class not found. Please ensure the plugin is properly installed.', 'third-audience' ) );
		}
	}

	/**
	 * AJAX handler: Force REST API mode (bypass detection).
	 *
	 * This forcefully configures Solid Security and marks REST API as accessible
	 * without running the detection test. Use when automatic detection fails.
	 *
	 * @since 3.4.1
	 * @return void
	 */
	public function ajax_force_rest_api_mode() {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ta-force-rest-api' ) ) {
			wp_send_json_error( __( 'Security check failed.', 'third-audience' ) );
		}

		// Verify user capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You do not have permission to perform this action.', 'third-audience' ) );
		}

		// Step 1: Configure Solid Security to allow REST API (all 3 methods).
		$settings = get_site_option( 'itsec-storage', array() );
		if ( ! isset( $settings['rest-api'] ) ) {
			$settings['rest-api'] = array();
		}
		$settings['rest-api']['method']          = 'default';
		$settings['rest-api']['restrict-access'] = false;
		$settings['rest-api']['whitelist']       = array( 'third-audience/v1', 'wp/v2', 'wp/v2/types' );
		update_site_option( 'itsec-storage', $settings );

		$solid_options = get_option( 'itsec_global', array() );
		if ( is_array( $solid_options ) ) {
			$solid_options['rest_api'] = 'default';
			update_option( 'itsec_global', $solid_options );
		}

		update_option( 'itsec-rest-api-method', 'default' );
		update_option( 'itsec_rest_api_settings', array( 'method' => 'default' ) );

		// Clear Solid Security cache.
		if ( function_exists( 'itsec_reload' ) ) {
			itsec_reload();
		}
		global $wpdb;
		$wpdb->query(
			"DELETE FROM {$wpdb->options}
			WHERE option_name LIKE '_transient_itsec_%'
			OR option_name LIKE '_transient_timeout_itsec_%'"
		);
		wp_cache_flush();

		// Give security plugins time to fully reload settings.
		sleep( 1 );

		// Step 2: Force environment detection to show REST API as accessible (bypass test).
		$env_detection = array(
			'detection_time' => current_time( 'mysql' ),
			'rest_api'       => array(
				'accessible'    => true,
				'blocker'       => null,
				'test_endpoint' => home_url( '/wp-json/wp/v2/types' ),
				'test_result'   => 'forced (bypass)',
				'forced'        => true,
			),
		);
		update_option( 'ta_environment_detection', $env_detection );

		// Step 3: Disable fallback mode.
		update_option( 'ta_use_ajax_fallback', false );

		// Step 4: Flush rewrite rules to re-register all REST routes.
		flush_rewrite_rules( true );

		// Log the action.
		if ( $this->logger ) {
			$this->logger->info(
				'REST API mode forced (bypassed detection)',
				array(
					'user_id' => get_current_user_id(),
					'method'  => 'admin_ajax',
				)
			);
		}

		wp_send_json_success( array(
			'message' => __( 'REST API mode forced successfully! All endpoints are now registered. Page will reload.', 'third-audience' ),
		) );
	}
}
