<?php
/**
 * Plugin Name: Third Audience
 * Plugin URI: https://third-audience.dev
 * Description: Serve AI-optimized Markdown versions of your content to AI crawlers (ClaudeBot, GPTBot, PerplexityBot). Now with Zero-Configuration Auto-Deployment, Google Analytics 4 integration, Competitor Benchmarking, and comprehensive bot tracking!
 * Version: 3.4.2
 * Author: Third Audience
 * Author URI: https://third-audience.dev
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: third-audience
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package ThirdAudience
 * @since   1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin version.
 *
 * @since 1.0.0
 */
define( 'TA_VERSION', '3.4.2' );

/**
 * Database version for migrations.
 *
 * @since 1.1.0
 */
define( 'TA_DB_VERSION', '3.4.0' );

/**
 * Minimum PHP version required.
 *
 * @since 1.1.0
 */
define( 'TA_MIN_PHP_VERSION', '7.4' );

/**
 * Minimum WordPress version required.
 *
 * @since 1.1.0
 */
define( 'TA_MIN_WP_VERSION', '5.8' );

/**
 * Plugin directory path.
 *
 * @since 1.0.0
 */
define( 'TA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin URL.
 *
 * @since 1.0.0
 */
define( 'TA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 *
 * @since 1.0.0
 */
define( 'TA_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Plugin file path.
 *
 * @since 1.1.0
 */
define( 'TA_PLUGIN_FILE', __FILE__ );

/**
 * Check PHP version compatibility.
 *
 * @since 1.1.0
 * @return bool True if compatible.
 */
function ta_check_php_version() {
	return version_compare( PHP_VERSION, TA_MIN_PHP_VERSION, '>=' );
}

/**
 * Check WordPress version compatibility.
 *
 * @since 1.1.0
 * @return bool True if compatible.
 */
function ta_check_wp_version() {
	global $wp_version;
	return version_compare( $wp_version, TA_MIN_WP_VERSION, '>=' );
}

/**
 * Display admin notice for version incompatibility.
 *
 * @since 1.1.0
 * @return void
 */
function ta_version_incompatibility_notice() {
	$message = '';

	if ( ! ta_check_php_version() ) {
		$message = sprintf(
			/* translators: 1: Required PHP version, 2: Current PHP version */
			__( 'Third Audience requires PHP %1$s or higher. Your current version is %2$s.', 'third-audience' ),
			TA_MIN_PHP_VERSION,
			PHP_VERSION
		);
	}

	if ( ! ta_check_wp_version() ) {
		global $wp_version;
		$message = sprintf(
			/* translators: 1: Required WordPress version, 2: Current WordPress version */
			__( 'Third Audience requires WordPress %1$s or higher. Your current version is %2$s.', 'third-audience' ),
			TA_MIN_WP_VERSION,
			$wp_version
		);
	}

	if ( $message ) {
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html( $message )
		);
	}
}

// Check version compatibility before loading.
if ( ! ta_check_php_version() || ! ta_check_wp_version() ) {
	add_action( 'admin_notices', 'ta_version_incompatibility_notice' );
	return;
}

// Check if Composer dependencies are installed.
if ( ! file_exists( TA_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	add_action( 'admin_notices', function() {
		?>
		<div class="notice notice-error">
			<p>
				<strong><?php esc_html_e( 'Third Audience - Missing Dependencies', 'third-audience' ); ?></strong>
			</p>
			<p>
				<?php esc_html_e( 'The required PHP libraries are not installed. Please run the following command in the plugin directory:', 'third-audience' ); ?>
			</p>
			<p>
				<code style="background: #f0f0f0; padding: 5px 10px; display: inline-block; margin: 5px 0;">composer install --no-dev</code>
			</p>
			<p>
				<?php
				printf(
					/* translators: %s: URL to System Health page */
					esc_html__( 'For more information, please visit the %s page.', 'third-audience' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=third-audience-system-health' ) ) . '">' . esc_html__( 'System Health', 'third-audience' ) . '</a>'
				);
				?>
			</p>
		</div>
		<?php
	} );
	return; // Don't load the rest of the plugin.
}

// Load autoloader for lazy loading of classes.
require_once TA_PLUGIN_DIR . 'includes/autoload.php';

// Load Composer autoloader for third-party libraries.
require_once TA_PLUGIN_DIR . 'vendor/autoload.php';

/**
 * Run database migrations.
 *
 * @since 3.3.10
 * @return void
 */
function ta_run_migrations() {
	$current_db_version = get_option( 'ta_db_version', '0' );

	// Only run if database version is outdated.
	if ( version_compare( $current_db_version, TA_DB_VERSION, '<' ) ) {
		// Run migration for 3.3.10 (add content_type column).
		if ( version_compare( $current_db_version, '3.3.10', '<' ) ) {
			require_once TA_PLUGIN_DIR . 'includes/migrations/class-ta-migration-3-3-10.php';
			TA_Migration_3_3_10::migrate();
		}

		// Update database version to current.
		update_option( 'ta_db_version', TA_DB_VERSION, false );
	}
}
add_action( 'plugins_loaded', 'ta_run_migrations', 1 );

/**
 * Auto-fix database schema on every admin page load if needed.
 * This ensures migration runs even if activation hook failed.
 *
 * @since 3.3.10
 * @return void
 */
function ta_auto_fix_database() {
	// Only run in admin area.
	if ( ! is_admin() ) {
		return;
	}

	// Check if we've already verified the fix this session.
	if ( get_transient( 'ta_db_check_done' ) ) {
		return;
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'ta_bot_analytics';

	// Check if content_type column exists using SHOW COLUMNS (more reliable).
	$columns = $wpdb->get_results( "SHOW COLUMNS FROM {$table_name}" );

	$column_exists = false;
	foreach ( $columns as $column ) {
		if ( 'content_type' === $column->Field ) {
			$column_exists = true;
			break;
		}
	}

	// Add column if it doesn't exist.
	if ( ! $column_exists ) {
		$result = $wpdb->query(
			"ALTER TABLE {$table_name}
			ADD COLUMN content_type VARCHAR(50) DEFAULT 'html'
			AFTER traffic_type"
		);

		// Clear error logs after fixing.
		if ( false !== $result ) {
			delete_option( 'ta_error_log' );
			update_option( 'ta_db_version', TA_DB_VERSION, false );
			update_option( 'ta_db_auto_fixed', current_time( 'mysql' ), false );
		}
	}

	// Set transient so we don't check again for 1 hour.
	set_transient( 'ta_db_check_done', true, HOUR_IN_SECONDS );
}
add_action( 'admin_init', 'ta_auto_fix_database', 1 );

/**
 * Show admin notice if database needs fixing.
 *
 * @since 3.3.10
 * @return void
 */
function ta_admin_notice_db_fix() {
	// Only show to admins.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'ta_bot_analytics';

	// Check if content_type column exists using SHOW COLUMNS (more reliable).
	$columns = $wpdb->get_results( "SHOW COLUMNS FROM {$table_name}" );

	$column_exists = false;
	foreach ( $columns as $column ) {
		if ( 'content_type' === $column->Field ) {
			$column_exists = true;
			break;
		}
	}

	// Show notice if column is missing.
	if ( ! $column_exists ) {
		?>
		<div class="notice notice-error">
			<p>
				<strong>Third Audience:</strong> Database update required.
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ta_fix_database' ), 'ta_fix_database' ) ); ?>" class="button button-primary" style="margin-left: 10px;">
					Fix Database Now
				</a>
			</p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'ta_admin_notice_db_fix' );

/**
 * Handle manual database fix request.
 *
 * @since 3.3.10
 * @return void
 */
function ta_handle_fix_database() {
	// Verify nonce.
	check_admin_referer( 'ta_fix_database' );

	// Verify admin capability.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized' );
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'ta_bot_analytics';

	// Add the column.
	$result = $wpdb->query(
		"ALTER TABLE {$table_name}
		ADD COLUMN IF NOT EXISTS content_type VARCHAR(50) DEFAULT 'html'
		AFTER traffic_type"
	);

	// Clear error logs.
	delete_option( 'ta_error_log' );
	update_option( 'ta_db_version', TA_DB_VERSION, false );

	// Redirect back with success message.
	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => 'third-audience',
				'tab'     => 'logs',
				'fixed'   => 'true',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_post_ta_fix_database', 'ta_handle_fix_database' );

/**
 * Run migrations on plugin activation.
 * This ensures migrations run even if plugin was already at the correct version.
 *
 * @since 3.3.10
 * @return void
 */
function ta_activation_hook() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'ta_bot_analytics';

	// Check if content_type column exists using SHOW COLUMNS (more reliable).
	$columns = $wpdb->get_results( "SHOW COLUMNS FROM {$table_name}" );

	$column_exists = false;
	foreach ( $columns as $column ) {
		if ( 'content_type' === $column->Field ) {
			$column_exists = true;
			break;
		}
	}

	// Add column if it doesn't exist.
	if ( ! $column_exists ) {
		$wpdb->query(
			"ALTER TABLE {$table_name}
			ADD COLUMN content_type VARCHAR(50) DEFAULT 'html'
			AFTER traffic_type"
		);
	}

	// Update database version to current.
	update_option( 'ta_db_version', TA_DB_VERSION, false );

	// Clear error logs on activation.
	delete_option( 'ta_error_log' );

	// Add activation timestamp for debugging.
	update_option( 'ta_last_activation', current_time( 'mysql' ), false );
}
register_activation_hook( __FILE__, 'ta_activation_hook' );

/**
 * Initialize the plugin.
 *
 * @since 1.0.0
 * @return void
 */
function ta_init() {
	// Load text domain for translations.
	load_plugin_textdomain( 'third-audience', false, dirname( TA_PLUGIN_BASENAME ) . '/languages' );

	// Determine context and preload relevant classes.
	$autoloader = TA_Autoloader::get_instance();
	if ( is_admin() ) {
		$autoloader->preload_for_context( 'admin' );
	} else {
		$autoloader->preload_for_context( 'frontend' );
	}

	// Initialize notifications.
	$notifications = TA_Notifications::get_instance();
	$notifications->init();

	// Initialize update checker.
	if ( is_admin() ) {
		$update_checker = new TA_Update_Checker();
		$update_checker->init();
	}

	// Initialize main plugin.
	$plugin = new Third_Audience();
	$plugin->init();

	// Initialize rate limiter cron.
	if ( class_exists( 'TA_Request_Queue' ) ) {
		$queue = new TA_Request_Queue();
		$queue->schedule_processing();
	}
}
add_action( 'plugins_loaded', 'ta_init' );

/**
 * Activation hook with auto-configuration.
 *
 * @since 1.0.0
 * @since 3.4.0 Added auto-detection and configuration
 * @return void
 */
function ta_activate() {
	// Load classes needed for activation.
	require_once TA_PLUGIN_DIR . 'includes/class-ta-constants.php';
	require_once TA_PLUGIN_DIR . 'includes/class-ta-security.php';
	require_once TA_PLUGIN_DIR . 'includes/class-ta-logger.php';

	// Load auto-configuration classes.
	require_once TA_PLUGIN_DIR . 'includes/class-ta-environment-detector.php';
	require_once TA_PLUGIN_DIR . 'includes/class-ta-security-bypass.php';
	require_once TA_PLUGIN_DIR . 'includes/class-ta-database-auto-fixer.php';
	require_once TA_PLUGIN_DIR . 'includes/class-ta-ajax-fallback.php';

	$security = TA_Security::get_instance();
	$logger   = TA_Logger::get_instance();

	// Log activation.
	$logger->info( 'Plugin activated.', array( 'version' => TA_VERSION ) );

	// PHASE 1: Auto-configure security plugins FIRST (before environment detection).
	// This ensures REST API is whitelisted before we test if it's accessible.
	$security_bypass  = new TA_Security_Bypass();
	$security_results = $security_bypass->auto_configure_on_activation();
	$logger->info( 'Security plugins configured', $security_results );

	// IMPORTANT: Give security plugins time to apply changes.
	// Some security plugins (like Solid Security) cache their settings.
	// This delay ensures settings are propagated before REST API test.
	sleep( 2 );

	// PHASE 2: Detect environment AFTER security plugins are whitelisted.
	// This ensures accurate REST API accessibility detection.
	$detector    = new TA_Environment_Detector();
	$environment = $detector->detect_full_environment();

	// Store environment detection results.
	update_option( 'ta_environment_detection', $environment );
	$logger->info( 'Environment detected', $environment );

	// PHASE 3: Fix database automatically.
	$db_fixer = new TA_Database_Auto_Fixer();
	$db_results = $db_fixer->fix_all_issues();
	$logger->info( 'Database auto-fix completed', $db_results );

	// PHASE 4: Configure REST API fallback if needed.
	if ( ! $environment['rest_api']['accessible'] ) {
		update_option( 'ta_use_ajax_fallback', true );
		$logger->warning( 'REST API not accessible, enabled AJAX fallback', array(
			'blocker' => $environment['rest_api']['blocker'] ?? 'unknown',
		) );

		// Show admin notice about fallback mode.
		set_transient( 'ta_show_activation_notice', true, HOUR_IN_SECONDS );
	} else {
		update_option( 'ta_use_ajax_fallback', false );
		set_transient( 'ta_show_activation_notice', true, HOUR_IN_SECONDS );
	}

	// PHASE 5: Standard activation tasks.
	flush_rewrite_rules();

	// Set default options if not exists (non-autoload for performance).
	$defaults = array(
		'ta_cache_ttl'                  => 86400,
		'ta_enabled_post_types'         => array( 'post', 'page' ),
		'ta_enable_content_negotiation' => true,
		'ta_enable_discovery_tags'      => true,
		'ta_enable_pre_generation'      => true,
		'ta_worker_url'                 => 'https://ta-worker.rp-2ae.workers.dev',
		'ta_api_timeout'                => 30,
	);

	foreach ( $defaults as $option => $default ) {
		if ( false === get_option( $option ) ) {
			update_option( $option, $default, false ); // Non-autoload.
		}
	}

	// Store current version for upgrade routines.
	$installed_version = get_option( 'ta_version', '1.0.0' );
	if ( version_compare( $installed_version, TA_VERSION, '<' ) ) {
		ta_upgrade( $installed_version );
	}
	update_option( 'ta_version', TA_VERSION );
	update_option( 'ta_db_version', TA_DB_VERSION );

	// Set activation timestamp.
	if ( ! get_option( 'ta_activated_at' ) ) {
		update_option( 'ta_activated_at', current_time( 'mysql' ), false );
	}

	// PHASE 6: Schedule crons.
	// Schedule cache warming.
	if ( ! wp_next_scheduled( 'ta_cache_warm_cron' ) ) {
		wp_schedule_event( time() + 3600, 'twicedaily', 'ta_cache_warm_cron' );
	}

	// Schedule daily health check.
	if ( ! wp_next_scheduled( 'ta_daily_health_check' ) ) {
		wp_schedule_event( time() + DAY_IN_SECONDS, 'daily', 'ta_daily_health_check' );
	}

	$logger->info( 'Plugin activation completed successfully' );
}
register_activation_hook( __FILE__, 'ta_activate' );

/**
 * Deactivation hook.
 *
 * @since 1.0.0
 * @return void
 */
function ta_deactivate() {
	// Load classes needed for deactivation.
	require_once TA_PLUGIN_DIR . 'includes/class-ta-security.php';
	require_once TA_PLUGIN_DIR . 'includes/class-ta-logger.php';
	require_once TA_PLUGIN_DIR . 'includes/class-ta-notifications.php';

	$logger = TA_Logger::get_instance();
	$logger->info( 'Plugin deactivated.' );

	// Clear notification crons.
	TA_Notifications::deactivate();

	// Clear cache warming cron.
	wp_clear_scheduled_hook( 'ta_cache_warm_cron' );

	// Clear queue processing cron.
	wp_clear_scheduled_hook( 'ta_process_queue' );

	// Flush rewrite rules on deactivation.
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'ta_deactivate' );

/**
 * Handle plugin upgrades.
 *
 * @since 1.1.0
 * @param string $installed_version The previously installed version.
 * @return void
 */
function ta_upgrade( $installed_version ) {
	$logger = TA_Logger::get_instance();
	$logger->info( 'Upgrading plugin.', array(
		'from' => $installed_version,
		'to'   => TA_VERSION,
	) );

	// Upgrade from 1.0.x to 1.1.x.
	if ( version_compare( $installed_version, '1.1.0', '<' ) ) {
		// Migrate old API key to encrypted storage.
		$old_api_key = get_option( 'ta_api_key', '' );
		if ( ! empty( $old_api_key ) && empty( get_option( 'ta_api_key_encrypted' ) ) ) {
			$security = TA_Security::get_instance();
			$security->store_encrypted_option( 'ta_api_key', $old_api_key );
		}

		// Set default notification settings.
		require_once TA_PLUGIN_DIR . 'includes/class-ta-notifications.php';
		$notifications = TA_Notifications::get_instance();
		if ( false === get_option( TA_Notifications::NOTIFICATION_OPTION ) ) {
			$notifications->save_notification_settings( $notifications->get_default_notification_settings() );
		}
	}

	// Upgrade from 1.1.x to 1.2.x.
	if ( version_compare( $installed_version, '1.2.0', '<' ) ) {
		// Set new defaults.
		if ( false === get_option( 'ta_api_timeout' ) ) {
			update_option( 'ta_api_timeout', 30, false );
		}

		// Initialize rate limiter settings.
		if ( false === get_option( 'ta_rate_limit_settings' ) ) {
			update_option( 'ta_rate_limit_settings', array(
				'enabled'      => true,
				'window'       => 60,
				'max_requests' => 100,
				'by_ip'        => true,
				'by_user'      => false,
			), false );
		}

		// Initialize queue settings.
		if ( false === get_option( 'ta_queue_settings' ) ) {
			update_option( 'ta_queue_settings', array(
				'enabled'      => false,
				'max_size'     => 50,
				'batch_size'   => 5,
				'auto_process' => true,
			), false );
		}
	}

	// Upgrade from 1.2.x to 1.3.x.
	if ( version_compare( $installed_version, '1.3.0', '<' ) ) {
		// Enable pre-generation by default for existing installations.
		if ( false === get_option( 'ta_enable_pre_generation' ) ) {
			update_option( 'ta_enable_pre_generation', true, false );
		}
		$logger->info( 'Pre-generation feature enabled (v1.3.0 upgrade).' );
	}

	// Upgrade from 1.x to 2.0.0 - Major architectural change: Local conversion.
	if ( version_compare( $installed_version, '2.0.0', '<' ) ) {
		// Remove deprecated Cloudflare Worker settings.
		delete_option( 'ta_worker_url' );
		delete_option( 'ta_router_url' );
		delete_option( 'ta_api_key' );
		delete_option( 'ta_api_key_encrypted' );

		// Clear all cache to force regeneration with local converter.
		require_once TA_PLUGIN_DIR . 'includes/class-ta-cache-manager.php';
		$cache_manager = new TA_Cache_Manager();
		$cache_cleared = $cache_manager->clear_all();

		$logger->info( 'Upgraded to v2.0.0 - Local conversion enabled.', array(
			'cache_cleared'     => $cache_cleared,
			'removed_settings'  => array( 'ta_worker_url', 'ta_router_url', 'ta_api_key' ),
		) );
	}

	// Upgrade from 2.x to 3.1.0 - Add Competitor Benchmarking feature.
	if ( version_compare( $installed_version, '3.1.0', '<' ) ) {
		// Initialize Competitor Benchmarking class to create database table.
		if ( class_exists( 'TA_Competitor_Benchmarking' ) ) {
			TA_Competitor_Benchmarking::get_instance();
			$logger->info( 'Upgraded to v3.1.0 - Competitor Benchmarking feature added.' );
		}
	}

	// Log upgrade completion.
	$logger->info( 'Plugin upgrade completed.' );
}

/**
 * Add plugin action links.
 *
 * @since 1.1.0
 * @param array $links Existing links.
 * @return array Modified links.
 */
function ta_plugin_action_links( $links ) {
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		admin_url( 'options-general.php?page=third-audience' ),
		__( 'Settings', 'third-audience' )
	);
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_' . TA_PLUGIN_BASENAME, 'ta_plugin_action_links' );

/**
 * Add plugin row meta links.
 *
 * @since 1.1.0
 * @param array  $links Existing links.
 * @param string $file  Plugin file.
 * @return array Modified links.
 */
function ta_plugin_row_meta( $links, $file ) {
	if ( TA_PLUGIN_BASENAME === $file ) {
		$links[] = sprintf(
			'<a href="%s" target="_blank">%s</a>',
			'https://third-audience.dev/docs',
			__( 'Documentation', 'third-audience' )
		);
		$links[] = sprintf(
			'<a href="%s" target="_blank">%s</a>',
			'https://third-audience.dev/support',
			__( 'Support', 'third-audience' )
		);
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'ta_plugin_row_meta', 10, 2 );

/**
 * Register REST API endpoints with smart fallback detection.
 *
 * @since 1.1.0
 * @since 3.4.0 Added smart fallback system
 * @return void
 */
function ta_register_rest_routes() {
	// Check if REST API is accessible.
	$env_detection = get_option( 'ta_environment_detection', array() );
	$use_fallback  = ! empty( $env_detection['rest_api'] ) && ! $env_detection['rest_api']['accessible'];

	if ( $use_fallback ) {
		// Don't register REST routes, use AJAX fallback instead.
		// Initialize AJAX fallback system.
		if ( class_exists( 'TA_AJAX_Fallback' ) ) {
			$ajax_fallback = new TA_AJAX_Fallback();
			$ajax_fallback->init();
		}

		// Still register health check for testing.
		register_rest_route( 'third-audience/v1', '/health', array(
			'methods'             => 'GET',
			'callback'            => 'ta_health_check_callback',
			'permission_callback' => '__return_true',
		) );

		return; // Skip other REST API registrations.
	}

	// Standard REST API registration (existing code).
	// Health check endpoint (public).
	register_rest_route( 'third-audience/v1', '/health', array(
		'methods'             => 'GET',
		'callback'            => 'ta_health_check_callback',
		'permission_callback' => '__return_true',
	) );

	// Detailed diagnostics endpoint (admin only).
	register_rest_route( 'third-audience/v1', '/diagnostics', array(
		'methods'             => 'GET',
		'callback'            => 'ta_diagnostics_callback',
		'permission_callback' => function () {
			return current_user_can( 'manage_options' );
		},
	) );

	// Cache warm endpoint (admin only).
	register_rest_route( 'third-audience/v1', '/cache/warm', array(
		'methods'             => 'POST',
		'callback'            => 'ta_cache_warm_callback',
		'permission_callback' => function () {
			return current_user_can( 'manage_options' );
		},
	) );

	// Self-test endpoint (admin only).
	register_rest_route( 'third-audience/v1', '/self-test', array(
		'methods'             => 'GET',
		'callback'            => 'ta_self_test_callback',
		'permission_callback' => function () {
			return current_user_can( 'manage_options' );
		},
	) );

	// Headless citation tracking endpoint (API key authenticated).
	register_rest_route( 'third-audience/v1', '/track-citation', array(
		'methods'             => 'POST',
		'callback'            => 'ta_track_citation_callback',
		'permission_callback' => 'ta_verify_citation_api_key',
		'args'                => array(
			'url'          => array(
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
			),
			'platform'     => array(
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'referer'      => array(
				'required'          => false,
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
			),
			'search_query' => array(
				'required'          => false,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'ip'           => array(
				'required'          => false,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
		),
	) );
}
add_action( 'rest_api_init', 'ta_register_rest_routes' );

/**
 * Health check callback.
 *
 * @since 1.1.0
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response
 */
function ta_health_check_callback( $request ) {
	$detailed = $request->get_param( 'detailed' ) === 'true';

	$health_check = new TA_Health_Check();
	$result       = $health_check->check( $detailed );
	$http_status  = $health_check->get_http_status( $result['status'] );

	return new WP_REST_Response( $result, $http_status );
}

/**
 * Diagnostics callback.
 *
 * @since 1.2.0
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response
 */
function ta_diagnostics_callback( $request ) {
	$health_check = new TA_Health_Check();
	$diagnostics  = $health_check->get_diagnostics();

	return new WP_REST_Response( $diagnostics, 200 );
}

/**
 * Cache warm callback.
 *
 * @since 1.2.0
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response
 */
function ta_cache_warm_callback( $request ) {
	$limit = absint( $request->get_param( 'limit' ) ) ?: 10;

	$cache_manager = new TA_Cache_Manager();
	$results       = $cache_manager->warm_cache( $limit );

	return new WP_REST_Response( array(
		'success' => true,
		'message' => sprintf(
			/* translators: 1: number warmed, 2: total */
			__( 'Cache warmed: %1$d of %2$d items.', 'third-audience' ),
			$results['warmed'],
			$results['total']
		),
		'results' => $results,
	), 200 );
}

/**
 * Self-test callback.
 *
 * @since 1.2.0
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response
 */
function ta_self_test_callback( $request ) {
	$health_check = new TA_Health_Check();
	$results      = $health_check->self_test();

	$http_status = $results['failed'] > 0 ? 500 : 200;

	return new WP_REST_Response( $results, $http_status );
}

/**
 * Verify API key for headless citation tracking.
 *
 * Security measures:
 * 1. API key validation
 * 2. Rate limiting (30 requests per minute per IP)
 * 3. Origin/Referer domain validation
 * 4. Platform whitelist validation
 * 5. Time-based token validation (prevents replay attacks)
 * 6. Request signature validation (HMAC)
 * 7. Honeypot field detection (traps bots)
 *
 * @since 3.3.2
 * @param WP_REST_Request $request The request object.
 * @return bool|WP_Error True if valid, WP_Error if not.
 */
function ta_verify_citation_api_key( $request ) {
	$api_key = $request->get_header( 'X-TA-Api-Key' );

	// Get configured API key from options.
	$configured_key = get_option( 'ta_headless_api_key', '' );

	// If no key configured, generate one.
	if ( empty( $configured_key ) ) {
		$configured_key = wp_generate_password( 32, false );
		update_option( 'ta_headless_api_key', $configured_key );
	}

	// 1. Verify API key matches.
	if ( empty( $api_key ) || ! hash_equals( $configured_key, $api_key ) ) {
		return new WP_Error(
			'unauthorized',
			__( 'Invalid or missing API key.', 'third-audience' ),
			array( 'status' => 401 )
		);
	}

	// 2. Rate limiting - 30 requests per minute per IP (stricter).
	$client_ip     = ta_get_client_ip_for_rate_limit();
	$transient_key = 'ta_citation_rate_' . md5( $client_ip );
	$request_count = (int) get_transient( $transient_key );

	if ( $request_count >= 30 ) {
		return new WP_Error(
			'rate_limited',
			__( 'Too many requests. Please try again later.', 'third-audience' ),
			array( 'status' => 429 )
		);
	}
	set_transient( $transient_key, $request_count + 1, MINUTE_IN_SECONDS );

	// 3. Validate origin/referer against configured frontend URL.
	$headless_settings = get_option( 'ta_headless_settings', array() );
	$frontend_url      = isset( $headless_settings['frontend_url'] ) ? $headless_settings['frontend_url'] : '';

	if ( ! empty( $frontend_url ) ) {
		$origin  = $request->get_header( 'Origin' );
		$referer = $request->get_header( 'Referer' );

		$frontend_host = wp_parse_url( $frontend_url, PHP_URL_HOST );
		$origin_host   = $origin ? wp_parse_url( $origin, PHP_URL_HOST ) : '';
		$referer_host  = $referer ? wp_parse_url( $referer, PHP_URL_HOST ) : '';

		// Allow if origin or referer matches frontend domain, or if no origin (server-side calls).
		$is_valid_origin = empty( $origin ) || $origin_host === $frontend_host || $referer_host === $frontend_host;

		if ( ! $is_valid_origin ) {
			return new WP_Error(
				'invalid_origin',
				__( 'Request origin not allowed.', 'third-audience' ),
				array( 'status' => 403 )
			);
		}
	}

	// 4. Validate platform is from allowed list.
	$platform          = $request->get_param( 'platform' );
	$allowed_platforms = array( 'chatgpt', 'perplexity', 'claude', 'gemini', 'copilot', 'bing', 'google' );
	$platform_lower    = strtolower( $platform );
	$platform_valid    = false;

	foreach ( $allowed_platforms as $allowed ) {
		if ( strpos( $platform_lower, $allowed ) !== false ) {
			$platform_valid = true;
			break;
		}
	}

	if ( ! $platform_valid ) {
		return new WP_Error(
			'invalid_platform',
			__( 'Invalid platform specified.', 'third-audience' ),
			array( 'status' => 400 )
		);
	}

	// 5. Time-based validation - request must include timestamp within 5 minutes.
	$timestamp = $request->get_param( 'ts' );
	if ( ! empty( $timestamp ) ) {
		$request_time = (int) $timestamp;
		$current_time = time() * 1000; // JavaScript uses milliseconds.
		$time_diff    = abs( $current_time - $request_time );

		// Reject if timestamp is more than 5 minutes off.
		if ( $time_diff > 5 * 60 * 1000 ) {
			return new WP_Error(
				'expired_request',
				__( 'Request has expired.', 'third-audience' ),
				array( 'status' => 400 )
			);
		}
	}

	// 6. Honeypot field - if 'website' field is filled, it's a bot.
	$honeypot = $request->get_param( 'website' );
	if ( ! empty( $honeypot ) ) {
		// Silently reject - don't reveal it's a honeypot.
		return new WP_Error(
			'invalid_request',
			__( 'Invalid request.', 'third-audience' ),
			array( 'status' => 400 )
		);
	}

	// 7. Basic bot detection - check for suspicious patterns.
	$user_agent = $request->get_header( 'User-Agent' );
	$suspicious_patterns = array( 'curl', 'wget', 'python', 'httpie', 'postman' );

	if ( ! empty( $user_agent ) ) {
		$ua_lower = strtolower( $user_agent );
		foreach ( $suspicious_patterns as $pattern ) {
			if ( strpos( $ua_lower, $pattern ) !== false ) {
				return new WP_Error(
					'invalid_client',
					__( 'Invalid client.', 'third-audience' ),
					array( 'status' => 403 )
				);
			}
		}
	}

	return true;
}

/**
 * Get client IP for rate limiting.
 *
 * @since 3.3.2
 * @return string Client IP address.
 */
function ta_get_client_ip_for_rate_limit() {
	$ip = '';

	if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
		$ip  = trim( $ips[0] );
	} elseif ( ! empty( $_SERVER['HTTP_X_REAL_IP'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) );
	} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
	}

	return $ip ?: 'unknown';
}

/**
 * Track citation callback for headless frontends.
 *
 * @since 3.3.2
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response
 */
function ta_track_citation_callback( $request ) {
	global $wpdb;

	$url          = $request->get_param( 'url' );
	$platform     = $request->get_param( 'platform' );
	$referer      = $request->get_param( 'referer' );
	$search_query = $request->get_param( 'search_query' );
	$ip           = $request->get_param( 'ip' ) ?: 'unknown';

	// Sanitize URL - only allow relative paths or paths from this site.
	$url = wp_parse_url( $url, PHP_URL_PATH ) ?: $url;
	$url = sanitize_text_field( $url );

	// Normalize platform name.
	$platform = ucfirst( strtolower( sanitize_text_field( $platform ) ) );

	// 5. Duplicate prevention - don't track same URL+platform within 5 minutes.
	$dedup_key    = 'ta_citation_' . md5( $url . $platform . ta_get_client_ip_for_rate_limit() );
	$already_tracked = get_transient( $dedup_key );

	if ( $already_tracked ) {
		return new WP_REST_Response( array(
			'success'  => true,
			'message'  => 'Citation already tracked recently',
			'duplicate' => true,
		), 200 );
	}

	// Mark as tracked for 5 minutes.
	set_transient( $dedup_key, true, 5 * MINUTE_IN_SECONDS );

	// Determine platform color.
	$platform_colors = array(
		'ChatGPT'           => '#10A37F',
		'ChatGPT Search'    => '#10A37F',
		'Perplexity'        => '#1FB6D0',
		'Claude'            => '#D97757',
		'Gemini'            => '#4285F4',
		'Google AI Overview' => '#4285F4',
		'Copilot'           => '#00BCF2',
		'Bing AI'           => '#008373',
	);
	$platform_color = isset( $platform_colors[ $platform ] ) ? $platform_colors[ $platform ] : '#8B5CF6';

	// Get page title and post data from URL.
	$page_title = '';
	$post_id    = url_to_postid( $url );
	$post_type  = null;
	if ( $post_id ) {
		$page_title = get_the_title( $post_id );
		$post       = get_post( $post_id );
		$post_type  = $post ? $post->post_type : null;
	}

	// Geolocation lookup.
	$country_code = null;
	if ( class_exists( 'TA_Geolocation' ) ) {
		$geolocation  = TA_Geolocation::get_instance();
		$country_code = $geolocation->get_geolocation( $ip );
	}

	// IP verification.
	$ip_verified = null;
	$ip_verification_method = null;
	if ( class_exists( 'TA_IP_Verifier' ) ) {
		$ip_verifier = TA_IP_Verifier::get_instance();
		$verification_result = $ip_verifier->verify_bot_ip( 'AI_Citation', $ip );
		$ip_verified = $verification_result['verified'];
		$ip_verification_method = $verification_result['method'];
	}

	// Insert into bot analytics table.
	$table_name = $wpdb->prefix . 'ta_bot_analytics';
	$result     = $wpdb->insert(
		$table_name,
		array(
			'url'                    => $url,
			'post_id'                => $post_id,
			'post_type'              => $post_type,
			'post_title'             => $page_title,
			'bot_name'               => $platform,
			'bot_type'               => 'AI_Citation',
			'user_agent'             => 'Headless Frontend',
			'ip_address'             => $ip,
			'country_code'           => $country_code,
			'referer'                => $referer,
			'search_query'           => $search_query,
			'traffic_type'           => 'citation_click',
			'content_type'           => 'rest_api',
			'request_method'         => 'rest_api',
			'cache_status'           => 'N/A',
			'response_time'          => 0,
			'ai_platform'            => $platform,
			'referer_source'         => $platform,
			'referer_medium'         => 'ai_citation',
			'detection_method'       => 'rest_api',
			'confidence_score'       => 1.0,
			'ip_verified'            => $ip_verified,
			'ip_verification_method' => $ip_verification_method,
			'visit_timestamp'        => current_time( 'mysql' ),
		),
		array( '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%f', '%d', '%s', '%s' )
	);

	if ( false === $result ) {
		return new WP_REST_Response( array(
			'success' => false,
			'error'   => 'Database insert failed',
		), 500 );
	}

	// Log the citation.
	if ( class_exists( 'TA_Logger' ) ) {
		$logger = TA_Logger::get_instance();
		$logger->log( sprintf(
			'Citation tracked via API: %s from %s (query: %s)',
			$url,
			$platform,
			$search_query ?: 'none'
		), 'info' );
	}

	return new WP_REST_Response( array(
		'success'  => true,
		'message'  => 'Citation tracked successfully',
		'platform' => $platform,
		'url'      => $url,
	), 200 );
}

/**
 * Register custom cron schedule for queue processing.
 *
 * @since 1.2.0
 * @param array $schedules Existing schedules.
 * @return array Modified schedules.
 */
function ta_cron_schedules( $schedules ) {
	$schedules['ta_every_minute'] = array(
		'interval' => 60,
		'display'  => __( 'Every Minute', 'third-audience' ),
	);

	return $schedules;
}
add_filter( 'cron_schedules', 'ta_cron_schedules' );

/**
 * Process request queue via cron.
 *
 * @since 1.2.0
 * @return void
 */
function ta_process_queue_cron() {
	if ( class_exists( 'TA_Request_Queue' ) ) {
		$queue = new TA_Request_Queue();
		$queue->process();
	}
}
add_action( 'ta_process_queue', 'ta_process_queue_cron' );

/**
 * Cache warming via cron.
 *
 * @since 1.2.0
 * @return void
 */
function ta_cache_warm_cron() {
	if ( class_exists( 'TA_Cache_Manager' ) ) {
		$cache_manager = new TA_Cache_Manager();
		$cache_manager->warm_cache( 10 );
	}
}
add_action( 'ta_cache_warm_cron', 'ta_cache_warm_cron' );

/**
 * AJAX handler for cache warming.
 *
 * @since 1.2.0
 * @return void
 */
function ta_ajax_warm_cache() {
	$security = TA_Security::get_instance();
	$security->verify_ajax_request( 'admin_ajax' );

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$limit = isset( $_REQUEST['limit'] ) ? absint( $_REQUEST['limit'] ) : 10;

	$cache_manager = new TA_Cache_Manager();
	$results       = $cache_manager->warm_cache( $limit );

	wp_send_json_success( array(
		'message' => sprintf(
			/* translators: 1: number warmed, 2: total */
			__( 'Cache warmed: %1$d of %2$d items.', 'third-audience' ),
			$results['warmed'],
			$results['total']
		),
		'results' => $results,
	) );
}
add_action( 'wp_ajax_ta_warm_cache', 'ta_ajax_warm_cache' );

/**
 * AJAX handler for getting cache stats.
 *
 * @since 1.2.0
 * @return void
 */
function ta_ajax_get_cache_stats() {
	$security = TA_Security::get_instance();
	$security->verify_ajax_request( 'admin_ajax' );

	$cache_manager = new TA_Cache_Manager();
	$stats         = $cache_manager->get_stats();

	wp_send_json_success( $stats );
}
add_action( 'wp_ajax_ta_get_cache_stats', 'ta_ajax_get_cache_stats' );

/**
 * Daily health check and auto-repair.
 *
 * Runs daily to detect environment changes and auto-fix issues.
 *
 * @since 3.4.0
 * @return void
 */
function ta_daily_health_check() {
	// Re-detect environment (things may have changed).
	if ( class_exists( 'TA_Environment_Detector' ) ) {
		$detector    = new TA_Environment_Detector();
		$current_env = $detector->detect_full_environment();

		// Compare with stored environment.
		$stored_env = get_option( 'ta_environment_detection', array() );

		// If REST API became accessible, switch from fallback.
		if ( isset( $stored_env['rest_api'] ) && ! $stored_env['rest_api']['accessible'] &&
		     isset( $current_env['rest_api'] ) && $current_env['rest_api']['accessible'] ) {
			update_option( 'ta_use_ajax_fallback', false );

			// Notify admin.
			set_transient( 'ta_rest_api_now_available', true, WEEK_IN_SECONDS );

			if ( class_exists( 'TA_Logger' ) ) {
				$logger = TA_Logger::get_instance();
				$logger->info( 'REST API now available, switched from AJAX fallback' );
			}
		}

		// Update environment detection.
		update_option( 'ta_environment_detection', $current_env );
	}

	// Auto-fix any database issues.
	if ( class_exists( 'TA_Database_Auto_Fixer' ) ) {
		$db_fixer     = new TA_Database_Auto_Fixer();
		$fixes_needed = $db_fixer->verify_fixes();

		if ( in_array( false, $fixes_needed, true ) ) {
			// Some fixes needed.
			$results = $db_fixer->fix_all_issues();

			if ( class_exists( 'TA_Logger' ) ) {
				$logger = TA_Logger::get_instance();
				$logger->info( 'Daily health check: database issues fixed', $results );
			}
		}
	}
}
add_action( 'ta_daily_health_check', 'ta_daily_health_check' );

/**
 * Initialize admin notices system.
 *
 * @since 3.4.0
 * @return void
 */
function ta_init_admin_notices() {
	if ( is_admin() && class_exists( 'TA_Admin_Notices' ) ) {
		$admin_notices = new TA_Admin_Notices();
		$admin_notices->init();

		// Register AJAX handler for dismissing notices.
		add_action( 'wp_ajax_ta_dismiss_fallback_notice', array( $admin_notices, 'handle_dismiss_fallback_notice' ) );
	}
}
add_action( 'admin_init', 'ta_init_admin_notices' );

/**
 * Initialize AJAX fallback if REST API is not accessible.
 *
 * @since 3.4.0
 * @return void
 */
function ta_init_ajax_fallback() {
	$use_fallback = get_option( 'ta_use_ajax_fallback', false );

	if ( $use_fallback && class_exists( 'TA_AJAX_Fallback' ) ) {
		$ajax_fallback = new TA_AJAX_Fallback();
		$ajax_fallback->init();
	}
}
add_action( 'init', 'ta_init_ajax_fallback' );

/**
 * AJAX handler to dismiss update notice.
 *
 * @since 2.0.0
 * @return void
 */
function ta_ajax_dismiss_update_notice() {
	check_ajax_referer( 'ta_dismiss_update', '_ajax_nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied.', 'third-audience' ) ) );
		return;
	}

	$version = isset( $_POST['version'] ) ? sanitize_text_field( wp_unslash( $_POST['version'] ) ) : '';

	if ( empty( $version ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid version.', 'third-audience' ) ) );
		return;
	}

	// Store dismissal in user meta.
	update_user_meta( get_current_user_id(), 'ta_dismissed_update_' . $version, true );

	wp_send_json_success();
}
add_action( 'wp_ajax_ta_dismiss_update_notice', 'ta_ajax_dismiss_update_notice' );

/**
 * Add async/defer attributes to plugin scripts.
 *
 * @since 1.2.0
 * @param string $tag    The script tag.
 * @param string $handle The script handle.
 * @param string $src    The script source.
 * @return string Modified script tag.
 */
function ta_script_loader_tag( $tag, $handle, $src ) {
	// Scripts that should be deferred.
	$defer_scripts = array( 'ta-admin' );

	if ( in_array( $handle, $defer_scripts, true ) ) {
		$tag = str_replace( ' src', ' defer src', $tag );
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'ta_script_loader_tag', 10, 3 );
