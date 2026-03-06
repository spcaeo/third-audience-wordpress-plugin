<?php
/**
 * Singleton Trait
 *
 * Provides singleton pattern implementation for classes.
 *
 * @package ThirdAudience
 * @since   1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait TA_Trait_Singleton
 *
 * Provides singleton functionality.
 *
 * @since 1.2.0
 */
trait TA_Trait_Singleton {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 1.2.0
	 * @return self
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
		$this->init();
	}

	/**
	 * Prevent cloning.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	private function __clone() {}

	/**
	 * Prevent unserialization.
	 *
	 * @since 1.2.0
	 * @return void
	 * @throws Exception When trying to unserialize.
	 */
	public function __wakeup() {
		throw new Exception( 'Cannot unserialize singleton.' );
	}

	/**
	 * Initialize the instance.
	 *
	 * Override this method in using class for initialization logic.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	protected function init() {
		// Override in using class.
	}

	/**
	 * Reset the singleton instance (for testing purposes).
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public static function reset_instance() {
		self::$instance = null;
	}
}
