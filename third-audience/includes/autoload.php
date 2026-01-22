<?php
/**
 * PSR-4 Style Autoloader for Third Audience Plugin.
 *
 * Implements lazy loading of classes only when needed, improving
 * memory usage and load time.
 *
 * @package ThirdAudience
 * @since   1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Autoloader
 *
 * PSR-4 style autoloader with lazy loading support.
 *
 * @since 1.2.0
 */
class TA_Autoloader {

	/**
	 * Singleton instance.
	 *
	 * @var TA_Autoloader|null
	 */
	private static $instance = null;

	/**
	 * Plugin directory path.
	 *
	 * @var string
	 */
	private $plugin_dir;

	/**
	 * Class map for known classes (for faster lookups).
	 *
	 * @var array
	 */
	private $class_map = array();

	/**
	 * Directory map for class prefixes.
	 *
	 * @var array
	 */
	private $directory_map = array();

	/**
	 * Loaded classes tracking.
	 *
	 * @var array
	 */
	private $loaded_classes = array();

	/**
	 * Classes that should be loaded early (core dependencies).
	 *
	 * @var array
	 */
	private $early_load = array(
		'TA_Constants',
		'TA_Security',
		'TA_Logger',
	);

	/**
	 * Get singleton instance.
	 *
	 * @since 1.2.0
	 * @return TA_Autoloader
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor for singleton.
	 *
	 * @since 1.2.0
	 */
	private function __construct() {
		$this->plugin_dir = dirname( __DIR__ ) . '/';
		$this->setup_class_map();
		$this->setup_directory_map();
	}

	/**
	 * Register the autoloader.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function register() {
		spl_autoload_register( array( $this, 'autoload' ) );
	}

	/**
	 * Setup the class map for known classes.
	 *
	 * This provides fast O(1) lookups for commonly used classes.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	private function setup_class_map() {
		$this->class_map = array(
			// Core classes
			'TA_Constants'           => 'includes/class-ta-constants.php',
			'TA_Security'            => 'includes/class-ta-security.php',
			'TA_Logger'              => 'includes/class-ta-logger.php',
			'TA_Notifications'       => 'includes/class-ta-notifications.php',
			'Third_Audience'         => 'includes/class-third-audience.php',

			// Feature classes
			'TA_URL_Router'          => 'includes/class-ta-url-router.php',
			'TA_Content_Negotiation' => 'includes/class-ta-content-negotiation.php',
			'TA_Discovery'           => 'includes/class-ta-discovery.php',
			'TA_Cache_Manager'       => 'includes/class-ta-cache-manager.php',
			'TA_Headless_Wizard'     => 'includes/class-ta-headless-wizard.php',

			// Bot Analytics and Webhooks
			'TA_Bot_Analytics'              => 'includes/class-ta-bot-analytics.php',
			'TA_Crawl_Budget_Analyzer'      => 'includes/class-ta-crawl-budget-analyzer.php',
			'TA_Webhooks'                   => 'includes/class-ta-webhooks.php',
			'TA_GA4_Integration'            => 'includes/class-ta-ga4-integration.php',
			'TA_Competitor_Benchmarking'    => 'includes/class-ta-competitor-benchmarking.php',

			// Rate limiting and queue
			'TA_Rate_Limiter'        => 'includes/class-ta-rate-limiter.php',
			'TA_Request_Queue'       => 'includes/class-ta-request-queue.php',

			// Health check and updates
			'TA_Health_Check'        => 'includes/class-ta-health-check.php',
			'TA_Update_Checker'      => 'includes/class-ta-update-checker.php',

			// Admin classes
			'TA_Admin'               => 'admin/class-ta-admin.php',

			// Interfaces
			'TA_Cacheable'           => 'includes/interfaces/interface-ta-cacheable.php',
			'TA_Loggable'            => 'includes/interfaces/interface-ta-loggable.php',
			'TA_Hookable'            => 'includes/interfaces/interface-ta-hookable.php',

			// Traits
			'TA_Trait_Singleton'     => 'includes/traits/trait-ta-singleton.php',
			'TA_Trait_Hooks'         => 'includes/traits/trait-ta-hooks.php',
			'TA_Trait_Cache'         => 'includes/traits/trait-ta-cache.php',
		);
	}

	/**
	 * Setup directory map for prefix-based lookups.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	private function setup_directory_map() {
		$this->directory_map = array(
			'TA_Admin_'     => 'admin/',
			'TA_Interface_' => 'includes/interfaces/',
			'TA_Trait_'     => 'includes/traits/',
			'TA_'           => 'includes/',
		);
	}

	/**
	 * Autoload a class.
	 *
	 * @since 1.2.0
	 * @param string $class The class name.
	 * @return bool Whether the class was loaded.
	 */
	public function autoload( $class ) {
		// Skip if not a Third Audience class.
		if ( 0 !== strpos( $class, 'TA_' ) && 'Third_Audience' !== $class ) {
			return false;
		}

		// Check if already loaded.
		if ( isset( $this->loaded_classes[ $class ] ) ) {
			return true;
		}

		// Try class map first (fastest).
		if ( isset( $this->class_map[ $class ] ) ) {
			$file = $this->plugin_dir . $this->class_map[ $class ];
			if ( $this->load_file( $file ) ) {
				$this->loaded_classes[ $class ] = true;
				return true;
			}
		}

		// Try directory map.
		$file = $this->find_file_by_directory_map( $class );
		if ( $file && $this->load_file( $file ) ) {
			$this->loaded_classes[ $class ] = true;
			return true;
		}

		// Try convention-based loading.
		$file = $this->find_file_by_convention( $class );
		if ( $file && $this->load_file( $file ) ) {
			$this->loaded_classes[ $class ] = true;
			return true;
		}

		return false;
	}

	/**
	 * Find file by directory map.
	 *
	 * @since 1.2.0
	 * @param string $class The class name.
	 * @return string|null The file path or null.
	 */
	private function find_file_by_directory_map( $class ) {
		foreach ( $this->directory_map as $prefix => $directory ) {
			if ( 0 === strpos( $class, $prefix ) ) {
				$relative_class = substr( $class, strlen( $prefix ) );
				$file_name      = $this->class_to_filename( $relative_class );
				$file           = $this->plugin_dir . $directory . $file_name;

				if ( file_exists( $file ) ) {
					return $file;
				}
			}
		}

		return null;
	}

	/**
	 * Find file by WordPress naming convention.
	 *
	 * @since 1.2.0
	 * @param string $class The class name.
	 * @return string|null The file path or null.
	 */
	private function find_file_by_convention( $class ) {
		$file_name = $this->class_to_filename( $class );

		// Check common locations.
		$locations = array(
			'includes/',
			'admin/',
			'includes/interfaces/',
			'includes/traits/',
		);

		foreach ( $locations as $location ) {
			$file = $this->plugin_dir . $location . $file_name;
			if ( file_exists( $file ) ) {
				return $file;
			}
		}

		return null;
	}

	/**
	 * Convert class name to filename (WordPress convention).
	 *
	 * @since 1.2.0
	 * @param string $class The class name.
	 * @return string The filename.
	 */
	private function class_to_filename( $class ) {
		// Handle interfaces.
		if ( 0 === strpos( $class, 'TA_Interface_' ) || 'TA_Cacheable' === $class || 'TA_Loggable' === $class || 'TA_Hookable' === $class ) {
			$interface_name = str_replace( array( 'TA_Interface_', 'TA_' ), '', $class );
			$interface_name = strtolower( str_replace( '_', '-', $interface_name ) );
			return 'interface-ta-' . $interface_name . '.php';
		}

		// Handle traits.
		if ( 0 === strpos( $class, 'TA_Trait_' ) ) {
			$trait_name = str_replace( 'TA_Trait_', '', $class );
			$trait_name = strtolower( str_replace( '_', '-', $trait_name ) );
			return 'trait-ta-' . $trait_name . '.php';
		}

		// Standard class.
		$file_name = strtolower( str_replace( '_', '-', $class ) );
		return 'class-' . $file_name . '.php';
	}

	/**
	 * Load a file.
	 *
	 * @since 1.2.0
	 * @param string $file The file path.
	 * @return bool Whether the file was loaded.
	 */
	private function load_file( $file ) {
		if ( file_exists( $file ) ) {
			require_once $file;
			return true;
		}
		return false;
	}

	/**
	 * Load early/core classes.
	 *
	 * These are classes that must be loaded before the autoloader
	 * can handle other classes.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function load_early_classes() {
		foreach ( $this->early_load as $class ) {
			if ( ! class_exists( $class, false ) && isset( $this->class_map[ $class ] ) ) {
				$this->load_file( $this->plugin_dir . $this->class_map[ $class ] );
				$this->loaded_classes[ $class ] = true;
			}
		}
	}

	/**
	 * Get all loaded classes.
	 *
	 * @since 1.2.0
	 * @return array List of loaded class names.
	 */
	public function get_loaded_classes() {
		return array_keys( $this->loaded_classes );
	}

	/**
	 * Get the count of loaded classes.
	 *
	 * @since 1.2.0
	 * @return int Number of loaded classes.
	 */
	public function get_loaded_count() {
		return count( $this->loaded_classes );
	}

	/**
	 * Check if a class is loaded.
	 *
	 * @since 1.2.0
	 * @param string $class The class name.
	 * @return bool Whether the class is loaded.
	 */
	public function is_loaded( $class ) {
		return isset( $this->loaded_classes[ $class ] );
	}

	/**
	 * Preload classes for specific context.
	 *
	 * @since 1.2.0
	 * @param string $context The context ('admin', 'frontend', 'api').
	 * @return void
	 */
	public function preload_for_context( $context ) {
		$preload_map = array(
			'admin'    => array(
				'TA_Admin',
				'TA_Cache_Manager',
				'TA_Notifications',
				'TA_Bot_Analytics',
			),
			'frontend' => array(
				'TA_URL_Router',
				'TA_Content_Negotiation',
				'TA_Discovery',
				'TA_Cache_Manager',
				'TA_Webhooks',
			),
			'api'      => array(
				'TA_Rate_Limiter',
				'TA_Request_Queue',
				'TA_Health_Check',
			),
		);

		if ( isset( $preload_map[ $context ] ) ) {
			foreach ( $preload_map[ $context ] as $class ) {
				if ( isset( $this->class_map[ $class ] ) ) {
					$this->load_file( $this->plugin_dir . $this->class_map[ $class ] );
					$this->loaded_classes[ $class ] = true;
				}
			}
		}
	}
}

// Initialize autoloader.
$autoloader = TA_Autoloader::get_instance();
$autoloader->register();
$autoloader->load_early_classes();
