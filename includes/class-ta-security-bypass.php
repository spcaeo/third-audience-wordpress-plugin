<?php
/**
 * Security Plugin Auto-Whitelister.
 *
 * Automatically configures common security plugins to allow Third Audience
 * endpoints without manual configuration.
 *
 * @package ThirdAudience
 * @since   3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Security_Bypass
 *
 * Auto-configures security plugins to whitelist Third Audience.
 *
 * @since 3.4.0
 */
class TA_Security_Bypass {

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
	 * Initialize on plugin activation.
	 */
	public function auto_configure_on_activation() {
		$results = array(
			'wordfence'    => $this->whitelist_in_wordfence(),
			'ithemes'      => $this->whitelist_in_ithemes(),
			'sucuri'       => $this->whitelist_in_sucuri(),
			'aio_security' => $this->whitelist_in_aio_security(),
		);

		// Log results.
		if ( $this->logger ) {
			$this->logger->info( 'Security plugin auto-configuration completed', $results );
		}

		// Store results for verification.
		update_option( 'ta_security_whitelist_results', $results );

		return $results;
	}

	/**
	 * Auto-whitelist in Wordfence.
	 *
	 * @return bool|string True if successful, error message if failed.
	 */
	private function whitelist_in_wordfence() {
		if ( ! in_array( 'wordfence/wordfence.php', get_option( 'active_plugins', array() ), true ) ) {
			return 'not_installed';
		}

		try {
			// Method 1: Add to Wordfence whitelist option.
			$whitelist = get_option( 'wordfenceWhitelist', array() );

			$ta_patterns = array(
				'/wp-json/third-audience/v1/track-citation',
				'/wp-json/third-audience/v1/health',
				'/wp-admin/admin-post.php?action=ta_clear_cache',
				'/wp-admin/admin-post.php?action=ta_clear_errors',
			);

			$added = false;
			foreach ( $ta_patterns as $pattern ) {
				if ( ! in_array( $pattern, $whitelist, true ) ) {
					$whitelist[] = $pattern;
					$added       = true;
				}
			}

			if ( $added ) {
				update_option( 'wordfenceWhitelist', $whitelist );
			}

			// Method 2: Add to WAF whitelist (if WAF is available).
			if ( class_exists( 'wfWAF' ) && method_exists( 'wfWAF', 'getInstance' ) ) {
				// Wordfence WAF configuration.
				$waf_whitelist = get_option( 'wfWafWhitelist', array() );
				if ( ! in_array( '/wp-json/third-audience/*', $waf_whitelist, true ) ) {
					$waf_whitelist[] = '/wp-json/third-audience/*';
					update_option( 'wfWafWhitelist', $waf_whitelist );
				}
			}

			if ( $this->logger ) {
				$this->logger->info( 'Wordfence whitelist updated', array( 'patterns' => $ta_patterns ) );
			}

			return true;

		} catch ( Exception $e ) {
			if ( $this->logger ) {
				$this->logger->error( 'Wordfence whitelist failed', array( 'error' => $e->getMessage() ) );
			}
			return $e->getMessage();
		}
	}

	/**
	 * Auto-whitelist in iThemes Security / Solid Security.
	 *
	 * @return bool|string True if successful, error message if failed.
	 */
	private function whitelist_in_ithemes() {
		// Check for plugin (multiple possible paths).
		$ithemes_active = false;
		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $active_plugins as $plugin ) {
			if ( strpos( $plugin, 'better-wp-security' ) !== false || strpos( $plugin, 'solid-security' ) !== false ) {
				$ithemes_active = true;
				break;
			}
		}

		if ( ! $ithemes_active ) {
			return 'not_installed';
		}

		try {
			// Method 1: Try new Solid Security options structure.
			$solid_options = get_option( 'itsec_global', array() );
			if ( is_array( $solid_options ) ) {
				// Disable REST API restrictions entirely.
				if ( isset( $solid_options['rest_api'] ) ) {
					$solid_options['rest_api'] = 'default'; // Set to default (unrestricted).
					update_option( 'itsec_global', $solid_options );

					if ( $this->logger ) {
						$this->logger->info( 'Solid Security: Disabled REST API restrictions (itsec_global)' );
					}
				}
			}

			// Method 2: Try legacy itsec-storage structure.
			$settings = get_site_option( 'itsec-storage', array() );

			// Force REST API to default (unrestricted).
			if ( ! isset( $settings['rest-api'] ) ) {
				$settings['rest-api'] = array();
			}

			// FORCE method to default - this is the key setting that enables REST API.
			$settings['rest-api']['method'] = 'default';

			// Also ensure 'restrict-access' is disabled if it exists.
			if ( isset( $settings['rest-api']['restrict-access'] ) ) {
				$settings['rest-api']['restrict-access'] = false;
			}

			// Add Third Audience and WordPress core to whitelist (belt and suspenders).
			if ( ! isset( $settings['rest-api']['whitelist'] ) ) {
				$settings['rest-api']['whitelist'] = array();
			}

			$required_namespaces = array(
				'third-audience/v1',  // Third Audience endpoints.
				'wp/v2',               // WordPress core REST API (for environment detection).
				'wp/v2/types',         // Specific endpoint for environment test.
			);

			foreach ( $required_namespaces as $namespace ) {
				if ( ! in_array( $namespace, $settings['rest-api']['whitelist'], true ) ) {
					$settings['rest-api']['whitelist'][] = $namespace;
				}
			}

			// Save settings.
			update_site_option( 'itsec-storage', $settings );

			// Method 3: Try individual REST API setting option.
			update_option( 'itsec-rest-api-method', 'default' );
			update_option( 'itsec_rest_api_settings', array( 'method' => 'default' ) );

			if ( $this->logger ) {
				$this->logger->info( 'Solid Security fully configured', array(
					'method'     => 'default',
					'whitelist'  => $required_namespaces,
					'attempts'   => 3,
				) );
			}

			// Clear Solid Security cache to force immediate reload of settings.
			// This ensures REST API test sees the updated whitelist immediately.
			$this->clear_solid_security_cache();

			return true;

		} catch ( Exception $e ) {
			if ( $this->logger ) {
				$this->logger->error( 'Solid Security configuration failed', array( 'error' => $e->getMessage() ) );
			}
			return $e->getMessage();
		}
	}

	/**
	 * Auto-whitelist in Sucuri.
	 *
	 * @return bool|string True if successful, error message if failed.
	 */
	private function whitelist_in_sucuri() {
		if ( ! in_array( 'sucuri-scanner/sucuri.php', get_option( 'active_plugins', array() ), true ) ) {
			return 'not_installed';
		}

		try {
			// Add Third Audience endpoints to Sucuri whitelist.
			$whitelist = get_option( 'sucuri_whitelist', array() );

			$ta_patterns = array(
				'/wp-json/third-audience/*',
				'/wp-admin/admin-post.php?action=ta_*',
			);

			$added = false;
			foreach ( $ta_patterns as $pattern ) {
				if ( ! in_array( $pattern, $whitelist, true ) ) {
					$whitelist[] = $pattern;
					$added       = true;
				}
			}

			if ( $added ) {
				update_option( 'sucuri_whitelist', array_unique( $whitelist ) );
			}

			if ( $this->logger ) {
				$this->logger->info( 'Sucuri whitelist updated' );
			}

			return true;

		} catch ( Exception $e ) {
			if ( $this->logger ) {
				$this->logger->error( 'Sucuri whitelist failed', array( 'error' => $e->getMessage() ) );
			}
			return $e->getMessage();
		}
	}

	/**
	 * Auto-whitelist in All In One WP Security.
	 *
	 * @return bool|string True if successful, error message if failed.
	 */
	private function whitelist_in_aio_security() {
		if ( ! in_array( 'all-in-one-wp-security-and-firewall/wp-security.php', get_option( 'active_plugins', array() ), true ) ) {
			return 'not_installed';
		}

		try {
			$settings = get_option( 'aio_wp_security_configs', array() );

			// Add whitelist rules.
			if ( ! isset( $settings['aiowps_whitelist_rules'] ) ) {
				$settings['aiowps_whitelist_rules'] = array();
			}

			$settings['aiowps_whitelist_rules']['third_audience'] = array(
				'path'   => '/wp-json/third-audience/*',
				'action' => 'allow',
			);

			update_option( 'aio_wp_security_configs', $settings );

			if ( $this->logger ) {
				$this->logger->info( 'All In One WP Security whitelist updated' );
			}

			return true;

		} catch ( Exception $e ) {
			if ( $this->logger ) {
				$this->logger->error( 'All In One WP Security whitelist failed', array( 'error' => $e->getMessage() ) );
			}
			return $e->getMessage();
		}
	}

	/**
	 * Check if auto-whitelisting was successful.
	 *
	 * @return array Status of each security plugin.
	 */
	public function verify_whitelisting() {
		return array(
			'wordfence'    => $this->is_whitelisted_in_wordfence(),
			'ithemes'      => $this->is_whitelisted_in_ithemes(),
			'sucuri'       => $this->is_whitelisted_in_sucuri(),
			'aio_security' => $this->is_whitelisted_in_aio_security(),
		);
	}

	/**
	 * Verify Wordfence whitelist.
	 *
	 * @return bool True if whitelisted.
	 */
	private function is_whitelisted_in_wordfence() {
		$whitelist = get_option( 'wordfenceWhitelist', array() );
		return in_array( '/wp-json/third-audience/v1/track-citation', $whitelist, true );
	}

	/**
	 * Verify iThemes Security whitelist.
	 *
	 * @return bool True if whitelisted.
	 */
	private function is_whitelisted_in_ithemes() {
		$settings = get_site_option( 'itsec-storage', array() );
		if ( isset( $settings['rest-api']['whitelist'] ) ) {
			return in_array( 'third-audience/v1', $settings['rest-api']['whitelist'], true );
		}
		return false;
	}

	/**
	 * Verify Sucuri whitelist.
	 *
	 * @return bool True if whitelisted.
	 */
	private function is_whitelisted_in_sucuri() {
		$whitelist = get_option( 'sucuri_whitelist', array() );
		return in_array( '/wp-json/third-audience/*', $whitelist, true );
	}

	/**
	 * Verify All In One WP Security whitelist.
	 *
	 * @return bool True if whitelisted.
	 */
	private function is_whitelisted_in_aio_security() {
		$settings = get_option( 'aio_wp_security_configs', array() );
		return isset( $settings['aiowps_whitelist_rules']['third_audience'] );
	}

	/**
	 * Generate manual configuration instructions if auto-whitelisting failed.
	 *
	 * @param string $plugin_name Security plugin name.
	 * @return string HTML formatted instructions.
	 */
	public function generate_manual_instructions( $plugin_name ) {
		$instructions = array(
			'wordfence'    => '
				<h4>Wordfence Manual Configuration:</h4>
				<ol>
					<li>Go to: <strong>Wordfence → Firewall → Manage Rate Limiting</strong></li>
					<li>Add whitelist rule:
						<ul>
							<li>Path: <code>/wp-json/third-audience/*</code></li>
							<li>Action: Whitelist</li>
						</ul>
					</li>
					<li>Click <strong>Save</strong></li>
				</ol>
			',
			'ithemes'      => '
				<h4>iThemes Security Manual Configuration:</h4>
				<ol>
					<li>Go to: <strong>Security → Settings → WordPress Tweaks</strong></li>
					<li>Find: <strong>REST API</strong></li>
					<li>Set to: <strong>Default Access</strong></li>
					<li>Or add exception: <code>third-audience/v1</code></li>
				</ol>
			',
			'sucuri'       => '
				<h4>Sucuri Security Manual Configuration:</h4>
				<ol>
					<li>Go to: <strong>Sucuri Security → Settings → Hardening</strong></li>
					<li>Ensure REST API is set to: <strong>Default</strong></li>
					<li>If using Sucuri Firewall (online), login to Sucuri Dashboard</li>
					<li>Go to: <strong>Firewall → Settings → Whitelist</strong></li>
					<li>Add: <code>/wp-json/third-audience/*</code></li>
				</ol>
			',
			'aio_security' => '
				<h4>All In One WP Security Manual Configuration:</h4>
				<ol>
					<li>Go to: <strong>WP Security → Firewall</strong></li>
					<li>Add whitelist rule:
						<ul>
							<li>Path: <code>/wp-json/third-audience/*</code></li>
							<li>Action: Allow</li>
						</ul>
					</li>
					<li>Click <strong>Save Settings</strong></li>
				</ol>
			',
		);

		return isset( $instructions[ $plugin_name ] ) ? $instructions[ $plugin_name ] : '<p>No manual instructions available for this plugin.</p>';
	}

	/**
	 * Clear Solid Security cache to force immediate settings reload.
	 *
	 * Solid Security caches its settings, which can cause REST API whitelist
	 * changes to not take effect immediately. This method clears all caches.
	 *
	 * @since 3.4.1
	 * @return void
	 */
	private function clear_solid_security_cache() {
		// Method 1: Call Solid Security reload function if available.
		if ( function_exists( 'itsec_reload' ) ) {
			itsec_reload();
		}

		// Method 2: Clear all Solid Security transients from database.
		global $wpdb;
		$wpdb->query(
			"DELETE FROM {$wpdb->options}
			WHERE option_name LIKE '_transient_itsec_%'
			OR option_name LIKE '_transient_timeout_itsec_%'"
		);

		// Method 3: Clear WordPress object cache (if using persistent cache).
		wp_cache_flush();

		if ( $this->logger ) {
			$this->logger->info( 'Solid Security cache cleared' );
		}
	}
}
