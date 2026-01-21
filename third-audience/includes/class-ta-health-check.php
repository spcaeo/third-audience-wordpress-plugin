<?php
/**
 * Health Check - Comprehensive system diagnostics.
 *
 * Provides detailed health check functionality including worker connectivity,
 * cache health, system requirements, and performance metrics.
 *
 * @package ThirdAudience
 * @since   1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Health_Check
 *
 * Comprehensive health checking and diagnostics.
 *
 * @since 1.2.0
 */
class TA_Health_Check {

	/**
	 * Health status constants.
	 */
	const STATUS_HEALTHY   = 'healthy';
	const STATUS_DEGRADED  = 'degraded';
	const STATUS_UNHEALTHY = 'unhealthy';

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Cache manager instance.
	 *
	 * @var TA_Cache_Manager
	 */
	private $cache_manager;

	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 */
	public function __construct() {
		$this->logger        = TA_Logger::get_instance();
		$this->cache_manager = new TA_Cache_Manager();
	}

	/**
	 * Run comprehensive health check.
	 *
	 * @since 1.2.0
	 * @param bool $detailed Whether to include detailed diagnostics.
	 * @return array Health check results.
	 */
	public function check( $detailed = false ) {
		$checks = array(
			'converter' => $this->check_converter(),
			'cache'     => $this->check_cache(),
			'system'    => $this->check_system(),
			'config'    => $this->check_configuration(),
		);

		// Determine overall status.
		$overall_status = self::STATUS_HEALTHY;
		$issues         = array();

		foreach ( $checks as $category => $result ) {
			if ( self::STATUS_UNHEALTHY === $result['status'] ) {
				$overall_status = self::STATUS_UNHEALTHY;
			} elseif ( self::STATUS_DEGRADED === $result['status'] && self::STATUS_HEALTHY === $overall_status ) {
				$overall_status = self::STATUS_DEGRADED;
			}

			if ( ! empty( $result['issues'] ) ) {
				$issues = array_merge( $issues, $result['issues'] );
			}
		}

		$response = array(
			'status'    => $overall_status,
			'version'   => TA_VERSION,
			'db_version' => get_option( 'ta_db_version', '1.0.0' ),
			'issues'    => $issues,
			'timestamp' => current_time( 'c' ),
		);

		if ( $detailed ) {
			$response['checks'] = $checks;
			$response['diagnostics'] = $this->get_diagnostics();
		}

		return $response;
	}

	/**
	 * Check worker connectivity.
	 *
	 * @since 1.2.0
	 * @return array Worker health status.
	 */
	public function check_converter() {
		$result = array(
			'status' => self::STATUS_HEALTHY,
			'issues' => array(),
			'data'   => array(),
		);

		// Check if library is installed.
		if ( ! TA_Local_Converter::is_library_available() ) {
			$result['status']   = self::STATUS_UNHEALTHY;
			$result['issues'][] = __( 'HTML to Markdown library is not installed.', 'third-audience' );
			return $result;
		}

		// Get library version.
		$library_version = TA_Local_Converter::get_library_version();
		$result['data']['library_version'] = $library_version;

		// Check system requirements.
		$requirements = TA_Local_Converter::check_system_requirements();
		$has_errors   = false;

		foreach ( $requirements as $key => $check ) {
			if ( 'error' === $check['status'] ) {
				$has_errors             = true;
				$result['status']       = self::STATUS_UNHEALTHY;
				$result['issues'][]     = $check['message'];
			} elseif ( 'warning' === $check['status'] ) {
				if ( self::STATUS_HEALTHY === $result['status'] ) {
					$result['status'] = self::STATUS_DEGRADED;
				}
				$result['issues'][] = $check['message'];
			}
		}

		if ( ! $has_errors ) {
			$result['data']['status'] = 'All system requirements met';
		}

		return $result;
	}

	/**
	 * Check cache health.
	 *
	 * @since 1.2.0
	 * @return array Cache health status.
	 */
	public function check_cache() {
		$result = array(
			'status' => self::STATUS_HEALTHY,
			'issues' => array(),
			'data'   => array(),
		);

		$stats       = $this->cache_manager->get_stats();
		$cache_health = $this->cache_manager->get_health();

		$result['data'] = array(
			'count'       => $stats['count'],
			'size'        => $stats['size_human'],
			'hit_rate'    => $stats['hit_rate'],
			'object_cache' => $stats['object_cache'],
		);

		// Check for issues.
		if ( ! empty( $cache_health['issues'] ) ) {
			$result['issues'] = $cache_health['issues'];
			if ( 'degraded' === $cache_health['status'] ) {
				$result['status'] = self::STATUS_DEGRADED;
			}
		}

		// Check if object cache is available.
		if ( ! $stats['object_cache'] ) {
			$result['issues'][] = __( 'No persistent object cache detected. Consider using Redis or Memcached.', 'third-audience' );
		}

		// Check hit rate.
		if ( $stats['hit_rate'] < 50 && ( $stats['total_hits'] + $stats['total_misses'] ) > 100 ) {
			$result['status']   = self::STATUS_DEGRADED;
			$result['issues'][] = sprintf(
				/* translators: %s: Hit rate percentage */
				__( 'Low cache hit rate: %s%%', 'third-audience' ),
				$stats['hit_rate']
			);
		}

		return $result;
	}

	/**
	 * Check system requirements.
	 *
	 * @since 1.2.0
	 * @return array System health status.
	 */
	public function check_system() {
		$result = array(
			'status' => self::STATUS_HEALTHY,
			'issues' => array(),
			'data'   => array(),
		);

		// PHP version.
		$result['data']['php_version'] = PHP_VERSION;
		if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
			$result['status']   = self::STATUS_UNHEALTHY;
			$result['issues'][] = sprintf(
				/* translators: %s: PHP version */
				__( 'PHP version %s is below minimum requirement (7.4).', 'third-audience' ),
				PHP_VERSION
			);
		}

		// WordPress version.
		global $wp_version;
		$result['data']['wp_version'] = $wp_version;
		if ( version_compare( $wp_version, '5.8', '<' ) ) {
			$result['status']   = self::STATUS_UNHEALTHY;
			$result['issues'][] = sprintf(
				/* translators: %s: WordPress version */
				__( 'WordPress version %s is below minimum requirement (5.8).', 'third-audience' ),
				$wp_version
			);
		}

		// PHP extensions.
		$required_extensions = array( 'curl', 'json', 'mbstring' );
		$missing_extensions  = array();

		foreach ( $required_extensions as $ext ) {
			if ( ! extension_loaded( $ext ) ) {
				$missing_extensions[] = $ext;
			}
		}

		if ( ! empty( $missing_extensions ) ) {
			$result['status']   = self::STATUS_DEGRADED;
			$result['issues'][] = sprintf(
				/* translators: %s: List of extensions */
				__( 'Missing PHP extensions: %s', 'third-audience' ),
				implode( ', ', $missing_extensions )
			);
		}

		$result['data']['extensions'] = array(
			'curl'     => extension_loaded( 'curl' ),
			'json'     => extension_loaded( 'json' ),
			'mbstring' => extension_loaded( 'mbstring' ),
			'openssl'  => extension_loaded( 'openssl' ),
		);

		// Memory limit.
		$memory_limit = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );
		$result['data']['memory_limit'] = size_format( $memory_limit );

		if ( $memory_limit < 64 * 1024 * 1024 ) { // 64MB.
			$result['issues'][] = __( 'PHP memory limit is low. Consider increasing to at least 64MB.', 'third-audience' );
		}

		// Max execution time.
		$max_execution_time = (int) ini_get( 'max_execution_time' );
		$result['data']['max_execution_time'] = $max_execution_time;

		if ( $max_execution_time > 0 && $max_execution_time < 30 ) {
			$result['issues'][] = __( 'PHP max execution time is low. Consider increasing to at least 30 seconds.', 'third-audience' );
		}

		return $result;
	}

	/**
	 * Check configuration.
	 *
	 * @since 1.2.0
	 * @return array Configuration health status.
	 */
	public function check_configuration() {
		$result = array(
			'status' => self::STATUS_HEALTHY,
			'issues' => array(),
			'data'   => array(),
		);

		// Check enabled post types.
		$enabled_types = get_option( 'ta_enabled_post_types', array( 'post', 'page' ) );
		$result['data']['enabled_post_types'] = $enabled_types;

		if ( empty( $enabled_types ) ) {
			$result['status']   = self::STATUS_DEGRADED;
			$result['issues'][] = __( 'No post types are enabled for markdown conversion.', 'third-audience' );
		}

		// Check cache TTL.
		$cache_ttl = get_option( 'ta_cache_ttl', 86400 );
		$result['data']['cache_ttl'] = $cache_ttl;

		if ( $cache_ttl < 3600 ) {
			$result['issues'][] = __( 'Cache TTL is very short. Consider increasing for better performance.', 'third-audience' );
		}

		// Check features.
		$result['data']['features'] = array(
			'content_negotiation' => get_option( 'ta_enable_content_negotiation', true ),
			'discovery_tags'      => get_option( 'ta_enable_discovery_tags', true ),
		);

		// Check notification settings.
		$notifications = TA_Notifications::get_instance();
		$notif_settings = $notifications->get_notification_settings();

		if ( empty( $notif_settings['alert_emails'] ) ) {
			$result['issues'][] = __( 'No alert email addresses configured.', 'third-audience' );
		}

		return $result;
	}

	/**
	 * Get detailed diagnostics.
	 *
	 * @since 1.2.0
	 * @return array Detailed diagnostics.
	 */
	public function get_diagnostics() {
		return array(
			'plugin'      => $this->get_plugin_diagnostics(),
			'environment' => $this->get_environment_diagnostics(),
			'database'    => $this->get_database_diagnostics(),
			'errors'      => $this->get_error_diagnostics(),
			'performance' => $this->get_performance_diagnostics(),
		);
	}

	/**
	 * Get plugin diagnostics.
	 *
	 * @since 1.2.0
	 * @return array Plugin diagnostics.
	 */
	private function get_plugin_diagnostics() {
		return array(
			'version'      => TA_VERSION,
			'db_version'   => get_option( 'ta_db_version', '1.0.0' ),
			'activated_at' => get_option( 'ta_activated_at', '' ),
			'plugin_dir'   => TA_PLUGIN_DIR,
			'plugin_url'   => TA_PLUGIN_URL,
			'autoloader'   => array(
				'loaded' => class_exists( 'TA_Autoloader', false ),
			),
		);
	}

	/**
	 * Get environment diagnostics.
	 *
	 * @since 1.2.0
	 * @return array Environment diagnostics.
	 */
	private function get_environment_diagnostics() {
		global $wp_version;

		return array(
			'php'       => array(
				'version'            => PHP_VERSION,
				'memory_limit'       => ini_get( 'memory_limit' ),
				'max_execution_time' => ini_get( 'max_execution_time' ),
				'upload_max'         => ini_get( 'upload_max_filesize' ),
				'sapi'               => php_sapi_name(),
			),
			'wordpress' => array(
				'version'    => $wp_version,
				'multisite'  => is_multisite(),
				'debug_mode' => defined( 'WP_DEBUG' ) && WP_DEBUG,
				'cron'       => defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ? 'disabled' : 'enabled',
			),
			'server'    => array(
				'software' => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : 'unknown',
				'os'       => PHP_OS,
			),
			'ssl'       => array(
				'available' => extension_loaded( 'openssl' ),
				'version'   => defined( 'OPENSSL_VERSION_TEXT' ) ? OPENSSL_VERSION_TEXT : 'unknown',
			),
		);
	}

	/**
	 * Get database diagnostics.
	 *
	 * @since 1.2.0
	 * @return array Database diagnostics.
	 */
	private function get_database_diagnostics() {
		global $wpdb;

		return array(
			'version'   => $wpdb->db_version(),
			'charset'   => $wpdb->charset,
			'collate'   => $wpdb->collate,
			'prefix'    => $wpdb->prefix,
			'ta_tables' => array(
				'options' => $wpdb->options,
			),
		);
	}

	/**
	 * Get error diagnostics.
	 *
	 * @since 1.2.0
	 * @return array Error diagnostics.
	 */
	private function get_error_diagnostics() {
		$stats  = $this->logger->get_stats();
		$errors = $this->logger->get_recent_errors( 5 );

		return array(
			'stats'         => $stats,
			'recent_errors' => $errors,
		);
	}

	/**
	 * Get performance diagnostics.
	 *
	 * @since 1.2.0
	 * @return array Performance diagnostics.
	 */
	private function get_performance_diagnostics() {
		$cache_stats = $this->cache_manager->get_stats();

		// Get rate limiter stats if available.
		$rate_limiter_stats = array();
		if ( class_exists( 'TA_Rate_Limiter' ) ) {
			$rate_limiter       = new TA_Rate_Limiter();
			$rate_limiter_stats = $rate_limiter->get_stats();
		}

		// Get queue stats if available.
		$queue_stats = array();
		if ( class_exists( 'TA_Request_Queue' ) ) {
			$queue       = new TA_Request_Queue();
			$queue_stats = $queue->get_stats();
		}

		return array(
			'cache'        => $cache_stats,
			'rate_limiter' => $rate_limiter_stats,
			'queue'        => $queue_stats,
		);
	}

	/**
	 * Run self-test.
	 *
	 * @since 1.2.0
	 * @return array Self-test results.
	 */
	public function self_test() {
		$results = array(
			'passed' => 0,
			'failed' => 0,
			'tests'  => array(),
		);

		// Test 1: Check if plugin files exist.
		$results['tests']['plugin_files'] = $this->test_plugin_files();

		// Test 2: Check if options can be read/written.
		$results['tests']['options'] = $this->test_options();

		// Test 3: Check if transients work.
		$results['tests']['transients'] = $this->test_transients();

		// Test 4: Check if HTTP requests work.
		$results['tests']['http'] = $this->test_http();

		// Test 5: Check if encryption works.
		$results['tests']['encryption'] = $this->test_encryption();

		// Count results.
		foreach ( $results['tests'] as $test ) {
			if ( $test['passed'] ) {
				$results['passed']++;
			} else {
				$results['failed']++;
			}
		}

		return $results;
	}

	/**
	 * Test plugin files.
	 *
	 * @since 1.2.0
	 * @return array Test result.
	 */
	private function test_plugin_files() {
		$required_files = array(
			'includes/class-third-audience.php',
			'includes/class-ta-cache-manager.php',
			'includes/class-ta-api-client.php',
			'admin/class-ta-admin.php',
		);

		$missing = array();
		foreach ( $required_files as $file ) {
			if ( ! file_exists( TA_PLUGIN_DIR . $file ) ) {
				$missing[] = $file;
			}
		}

		return array(
			'passed'  => empty( $missing ),
			'message' => empty( $missing )
				? __( 'All plugin files present.', 'third-audience' )
				: sprintf( __( 'Missing files: %s', 'third-audience' ), implode( ', ', $missing ) ),
		);
	}

	/**
	 * Test options.
	 *
	 * @since 1.2.0
	 * @return array Test result.
	 */
	private function test_options() {
		$test_key   = 'ta_health_test_' . time();
		$test_value = 'test_value_' . wp_generate_password( 8, false );

		// Write.
		$write_result = update_option( $test_key, $test_value, false );

		// Read.
		$read_value = get_option( $test_key );

		// Delete.
		delete_option( $test_key );

		$passed = $write_result && $read_value === $test_value;

		return array(
			'passed'  => $passed,
			'message' => $passed
				? __( 'Options read/write working.', 'third-audience' )
				: __( 'Options read/write failed.', 'third-audience' ),
		);
	}

	/**
	 * Test transients.
	 *
	 * @since 1.2.0
	 * @return array Test result.
	 */
	private function test_transients() {
		$test_key   = 'ta_health_test_transient';
		$test_value = 'test_value_' . wp_generate_password( 8, false );

		// Write.
		set_transient( $test_key, $test_value, 60 );

		// Read.
		$read_value = get_transient( $test_key );

		// Delete.
		delete_transient( $test_key );

		$passed = $read_value === $test_value;

		return array(
			'passed'  => $passed,
			'message' => $passed
				? __( 'Transients working.', 'third-audience' )
				: __( 'Transients not working correctly.', 'third-audience' ),
		);
	}

	/**
	 * Test HTTP requests.
	 *
	 * @since 1.2.0
	 * @return array Test result.
	 */
	private function test_http() {
		$response = wp_remote_get( 'https://httpbin.org/get', array(
			'timeout' => 10,
		) );

		$passed = ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response );

		return array(
			'passed'  => $passed,
			'message' => $passed
				? __( 'HTTP requests working.', 'third-audience' )
				: __( 'HTTP requests failed.', 'third-audience' ),
		);
	}

	/**
	 * Test encryption.
	 *
	 * @since 1.2.0
	 * @return array Test result.
	 */
	private function test_encryption() {
		$security   = TA_Security::get_instance();
		$test_value = 'test_encryption_' . wp_generate_password( 16, true );

		$encrypted = $security->encrypt( $test_value );
		$decrypted = $security->decrypt( $encrypted );

		$passed = $decrypted === $test_value;

		return array(
			'passed'  => $passed,
			'message' => $passed
				? __( 'Encryption working.', 'third-audience' )
				: __( 'Encryption not working correctly.', 'third-audience' ),
		);
	}

	/**
	 * Get HTTP status code based on health.
	 *
	 * @since 1.2.0
	 * @param string $status The health status.
	 * @return int HTTP status code.
	 */
	public function get_http_status( $status ) {
		switch ( $status ) {
			case self::STATUS_HEALTHY:
				return 200;
			case self::STATUS_DEGRADED:
				return 200; // Still operational.
			case self::STATUS_UNHEALTHY:
				return 503;
			default:
				return 500;
		}
	}
}
