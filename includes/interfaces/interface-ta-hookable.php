<?php
/**
 * Hookable Interface
 *
 * Interface for components that register WordPress hooks.
 *
 * @package ThirdAudience
 * @since   1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface TA_Hookable
 *
 * Defines the contract for hookable components.
 *
 * @since 1.2.0
 */
interface TA_Hookable {

	/**
	 * Register WordPress hooks.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function register_hooks();

	/**
	 * Unregister WordPress hooks.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function unregister_hooks();
}
