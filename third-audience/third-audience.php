<?php
/**
 * Plugin Name: Third Audience
 * Plugin URI: https://third-audience.dev
 * Description: Serve AI-optimized Markdown versions of your content to AI crawlers (ClaudeBot, GPTBot, PerplexityBot). Now with local conversion - no external dependencies!
 * Version: 2.1.0
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
define( 'TA_VERSION', '2.1.0' );

/**
 * Database version for migrations.
 *
 * @since 1.1.0
 */
define( 'TA_DB_VERSION', '2.0.0' );

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
 * Activation hook.
 *
 * @since 1.0.0
 * @return void
 */
function ta_activate() {
	// Load classes needed for activation.
	require_once TA_PLUGIN_DIR . 'includes/class-ta-constants.php';
	require_once TA_PLUGIN_DIR . 'includes/class-ta-security.php';
	require_once TA_PLUGIN_DIR . 'includes/class-ta-logger.php';

	$security = TA_Security::get_instance();
	$logger   = TA_Logger::get_instance();

	// Log activation.
	$logger->info( 'Plugin activated.', array( 'version' => TA_VERSION ) );

	// Flush rewrite rules on activation.
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

	// Schedule cache warming.
	if ( ! wp_next_scheduled( 'ta_cache_warm_cron' ) ) {
		wp_schedule_event( time() + 3600, 'twicedaily', 'ta_cache_warm_cron' );
	}
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
 * Register REST API endpoints.
 *
 * @since 1.1.0
 * @return void
 */
function ta_register_rest_routes() {
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
