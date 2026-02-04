<?php
/**
 * Environment Detector - Auto-detects hosting environment and restrictions.
 *
 * This class detects:
 * - Security plugins and their configurations
 * - REST API accessibility
 * - Server type (Apache/Nginx/LiteSpeed)
 * - PHP limitations and available extensions
 * - Database permissions
 * - File system permissions
 * - Caching plugins
 *
 * @package ThirdAudience
 * @since   3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Environment_Detector
 *
 * Detects hosting environment and automatically adapts plugin behavior.
 *
 * @since 3.4.0
 */
class TA_Environment_Detector {

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( class_exists( 'TA_Logger' ) ) {
			$this->logger = TA_Logger::get_instance();
		}
	}

	/**
	 * Run full environment detection.
	 *
	 * @return array Complete environment profile.
	 */
	public function detect_full_environment() {
		$environment = array(
			'rest_api'         => $this->detect_rest_api_access(),
			'security_plugins' => $this->detect_security_plugin_blocks(),
			'server_type'      => $this->detect_server_type(),
			'caching_plugins'  => $this->detect_caching_plugins(),
			'db_permissions'   => $this->test_database_permissions(),
			'php_version'      => PHP_VERSION,
			'php_extensions'   => $this->check_php_extensions(),
			'wp_version'       => get_bloginfo( 'version' ),
			'detection_time'   => current_time( 'mysql' ),
		);

		if ( $this->logger ) {
			$this->logger->info( 'Environment detected', $environment );
		}

		return $environment;
	}

	/**
	 * Detect if REST API is accessible.
	 *
	 * @return array Detection result with status and method.
	 */
	public function detect_rest_api_access() {
		// Test 1: Try internal WordPress REST API call.
		$test_url = rest_url( 'wp/v2/types/post' );
		$response = wp_remote_get(
			$test_url,
			array(
				'timeout'     => 5,
				'sslverify'   => false,
				'httpversion' => '1.1',
			)
		);

		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			return array(
				'accessible' => true,
				'method'     => 'standard',
				'message'    => 'REST API fully accessible',
				'blocker'    => null,
			);
		}

		// Test 2: Check if blocked by security plugin.
		$security_block = $this->detect_security_plugin_blocks();
		if ( $security_block ) {
			return array(
				'accessible' => false,
				'method'     => 'blocked_by_security',
				'message'    => 'REST API blocked by security plugin',
				'blocker'    => $security_block,
				'fallback'   => 'use_admin_ajax',
			);
		}

		// Test 3: Check if blocked by server config.
		if ( $this->is_blocked_by_server() ) {
			return array(
				'accessible' => false,
				'method'     => 'blocked_by_server',
				'message'    => 'REST API blocked at server level',
				'blocker'    => 'server_firewall',
				'fallback'   => 'use_admin_ajax',
			);
		}

		return array(
			'accessible' => false,
			'method'     => 'unknown',
			'message'    => 'REST API not accessible - reason unknown',
			'blocker'    => 'unknown',
			'fallback'   => 'use_admin_ajax',
		);
	}

	/**
	 * Detect active security plugins and their REST API blocks.
	 *
	 * @return string|false Name of blocking plugin or false.
	 */
	public function detect_security_plugin_blocks() {
		$active_plugins = get_option( 'active_plugins', array() );

		// Wordfence detection.
		if ( in_array( 'wordfence/wordfence.php', $active_plugins, true ) ) {
			if ( class_exists( 'wfConfig' ) ) {
				// Check if Wordfence firewall is enabled.
				if ( method_exists( 'wfConfig', 'get' ) ) {
					$firewall_enabled = wfConfig::get( 'firewallEnabled' );
					if ( $firewall_enabled ) {
						return 'wordfence';
					}
				}
			}
			return 'wordfence';
		}

		// iThemes Security detection.
		if ( in_array( 'better-wp-security/better-wp-security.php', $active_plugins, true ) ) {
			$ithemes_settings = get_site_option( 'itsec-storage', array() );
			if ( isset( $ithemes_settings['rest-api']['method'] ) && 'default' !== $ithemes_settings['rest-api']['method'] ) {
				return 'ithemes-security';
			}
			// If method is 'default', it's not blocking - return false.
			return false;
		}

		// Sucuri detection.
		if ( in_array( 'sucuri-scanner/sucuri.php', $active_plugins, true ) ) {
			return 'sucuri';
		}

		// All In One WP Security.
		if ( in_array( 'all-in-one-wp-security-and-firewall/wp-security.php', $active_plugins, true ) ) {
			$aiowps_settings = get_option( 'aio_wp_security_configs', array() );
			if ( isset( $aiowps_settings['aiowps_enable_6g_firewall'] ) && '1' === $aiowps_settings['aiowps_enable_6g_firewall'] ) {
				return 'all-in-one-wp-security';
			}
			return 'all-in-one-wp-security';
		}

		return false;
	}

	/**
	 * Detect server type (Apache, Nginx, LiteSpeed).
	 *
	 * @return string Server type.
	 */
	public function detect_server_type() {
		$server = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';

		if ( stripos( $server, 'apache' ) !== false ) {
			return 'apache';
		} elseif ( stripos( $server, 'nginx' ) !== false ) {
			return 'nginx';
		} elseif ( stripos( $server, 'litespeed' ) !== false ) {
			return 'litespeed';
		}

		return 'unknown';
	}

	/**
	 * Check if we can use .htaccess modifications.
	 *
	 * @return bool True if .htaccess is writable.
	 */
	public function can_use_htaccess() {
		$server = $this->detect_server_type();

		// Only Apache and LiteSpeed use .htaccess.
		if ( ! in_array( $server, array( 'apache', 'litespeed' ), true ) ) {
			return false;
		}

		// Check if .htaccess is writable.
		$htaccess_path = ABSPATH . '.htaccess';
		return file_exists( $htaccess_path ) && is_writable( $htaccess_path );
	}

	/**
	 * Detect if server blocks REST API at firewall level.
	 *
	 * @return bool True if blocked.
	 */
	private function is_blocked_by_server() {
		// Check for common server-level blocks.
		// This is heuristic-based.

		// Check if mod_security is active and blocking.
		if ( function_exists( 'apache_get_modules' ) ) {
			$modules = apache_get_modules();
			if ( in_array( 'mod_security2', $modules, true ) || in_array( 'mod_security', $modules, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Detect caching plugins that might interfere.
	 *
	 * @return array List of active caching plugins.
	 */
	public function detect_caching_plugins() {
		$active_plugins  = get_option( 'active_plugins', array() );
		$caching_plugins = array();

		$known_cache_plugins = array(
			'wp-super-cache/wp-cache.php'               => 'WP Super Cache',
			'w3-total-cache/w3-total-cache.php'         => 'W3 Total Cache',
			'wp-rocket/wp-rocket.php'                   => 'WP Rocket',
			'litespeed-cache/litespeed-cache.php'       => 'LiteSpeed Cache',
			'wp-fastest-cache/wpFastestCache.php'       => 'WP Fastest Cache',
			'cache-enabler/cache-enabler.php'           => 'Cache Enabler',
			'comet-cache/comet-cache.php'               => 'Comet Cache',
			'autoptimize/autoptimize.php'               => 'Autoptimize',
			'wp-optimize/wp-optimize.php'               => 'WP-Optimize',
			'sg-cachepress/sg-cachepress.php'           => 'SG Optimizer',
			'swift-performance-lite/performance.php'    => 'Swift Performance',
		);

		foreach ( $known_cache_plugins as $plugin => $name ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$caching_plugins[] = $name;
			}
		}

		return $caching_plugins;
	}

	/**
	 * Test database permissions (CREATE, ALTER, INSERT).
	 *
	 * @return array Permission status.
	 */
	public function test_database_permissions() {
		global $wpdb;

		$permissions = array(
			'create' => false,
			'alter'  => false,
			'insert' => false,
			'errors' => array(),
		);

		// Test CREATE TABLE.
		$test_table = $wpdb->prefix . 'ta_permission_test';
		$charset_collate = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE IF NOT EXISTS {$test_table} (id INT) {$charset_collate}";
		$result  = $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( false !== $result ) {
			$permissions['create'] = true;

			// Test ALTER TABLE.
			$alter_result            = $wpdb->query( "ALTER TABLE {$test_table} ADD COLUMN test_col VARCHAR(50)" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$permissions['alter'] = ( false !== $alter_result );

			// Test INSERT.
			$insert_result           = $wpdb->insert( $test_table, array( 'id' => 1 ) );
			$permissions['insert'] = ( false !== $insert_result );

			// Cleanup.
			$wpdb->query( "DROP TABLE IF EXISTS {$test_table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		} else {
			$permissions['errors'][] = $wpdb->last_error;
		}

		return $permissions;
	}

	/**
	 * Check PHP extensions required by the plugin.
	 *
	 * @return array Status of required extensions.
	 */
	public function check_php_extensions() {
		$required = array( 'openssl', 'mysqli', 'curl', 'json', 'mbstring' );
		$status   = array();

		foreach ( $required as $ext ) {
			$status[ $ext ] = extension_loaded( $ext );
		}

		return $status;
	}

	/**
	 * Get hosting provider name (if detectable).
	 *
	 * @return string Hosting provider name or 'unknown'.
	 */
	public function detect_hosting_provider() {
		// Check for common hosting providers by server signature.
		if ( defined( 'WPE_APIKEY' ) ) {
			return 'WP Engine';
		}

		if ( defined( 'KINSTAMU_VERSION' ) ) {
			return 'Kinsta';
		}

		if ( defined( 'FLYWHEEL_CONFIG_DIR' ) ) {
			return 'Flywheel';
		}

		if ( defined( 'GD_SYSTEM_PLUGIN_DIR' ) ) {
			return 'GoDaddy';
		}

		if ( class_exists( 'SG_CachePress_Supercacher' ) ) {
			return 'SiteGround';
		}

		$server = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';

		if ( stripos( $server, 'bluehost' ) !== false ) {
			return 'Bluehost';
		}

		if ( stripos( $server, 'hostgator' ) !== false ) {
			return 'HostGator';
		}

		return 'unknown';
	}

	/**
	 * Check if running on localhost.
	 *
	 * @return bool True if localhost.
	 */
	public function is_localhost() {
		$localhost_ips = array( '127.0.0.1', '::1' );
		$server_addr   = isset( $_SERVER['SERVER_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) ) : '';

		if ( in_array( $server_addr, $localhost_ips, true ) ) {
			return true;
		}

		$site_url = get_site_url();
		$localhost_domains = array( 'localhost', '127.0.0.1', '.local', '.test', '.dev' );

		foreach ( $localhost_domains as $domain ) {
			if ( stripos( $site_url, $domain ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Generate environment report for display in admin.
	 *
	 * @return string HTML formatted report.
	 */
	public function generate_report_html() {
		$env = get_option( 'ta_environment_detection', array() );

		if ( empty( $env ) ) {
			return '<p>No environment data available. Please activate the plugin to run detection.</p>';
		}

		ob_start();
		?>
		<div class="ta-environment-report">
			<h3>Environment Detection Report</h3>

			<table class="widefat">
				<tr>
					<th>Server Type</th>
					<td><?php echo esc_html( $env['server_type'] ?? 'unknown' ); ?></td>
				</tr>
				<tr>
					<th>PHP Version</th>
					<td><?php echo esc_html( $env['php_version'] ?? 'unknown' ); ?></td>
				</tr>
				<tr>
					<th>WordPress Version</th>
					<td><?php echo esc_html( $env['wp_version'] ?? 'unknown' ); ?></td>
				</tr>
				<tr>
					<th>REST API Status</th>
					<td>
						<?php if ( ! empty( $env['rest_api']['accessible'] ) ) : ?>
							<span style="color: green;">✓ Accessible</span>
						<?php else : ?>
							<span style="color: orange;">⚠ Blocked</span>
							<br><small>Blocker: <?php echo esc_html( $env['rest_api']['blocker'] ?? 'Unknown' ); ?></small>
							<br><small>Using fallback: admin-ajax.php</small>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th>Security Plugins</th>
					<td>
						<?php if ( $env['security_plugins'] ) : ?>
							<?php echo esc_html( $env['security_plugins'] ); ?>
						<?php else : ?>
							<span style="color: green;">None detected</span>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th>Caching Plugins</th>
					<td>
						<?php if ( ! empty( $env['caching_plugins'] ) ) : ?>
							<?php echo esc_html( implode( ', ', $env['caching_plugins'] ) ); ?>
						<?php else : ?>
							<span style="color: green;">None detected</span>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th>Database Permissions</th>
					<td>
						<?php if ( ! empty( $env['db_permissions']['create'] ) && ! empty( $env['db_permissions']['alter'] ) ) : ?>
							<span style="color: green;">✓ Full permissions</span>
						<?php else : ?>
							<span style="color: red;">✗ Limited permissions</span>
							<br><small>CREATE: <?php echo ! empty( $env['db_permissions']['create'] ) ? '✓' : '✗'; ?></small>
							<br><small>ALTER: <?php echo ! empty( $env['db_permissions']['alter'] ) ? '✓' : '✗'; ?></small>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th>Detection Time</th>
					<td><?php echo esc_html( $env['detection_time'] ?? 'unknown' ); ?></td>
				</tr>
			</table>
		</div>
		<?php
		return ob_get_clean();
	}
}
