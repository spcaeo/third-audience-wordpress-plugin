<?php
/**
 * Loggable Interface
 *
 * Interface for components that support logging functionality.
 *
 * @package ThirdAudience
 * @since   1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface TA_Loggable
 *
 * Defines the contract for loggable components.
 *
 * @since 1.2.0
 */
interface TA_Loggable {

	/**
	 * Log a debug message.
	 *
	 * @since 1.2.0
	 * @param string $message The message.
	 * @param array  $context Additional context.
	 * @return bool Whether logged successfully.
	 */
	public function debug( $message, $context = array() );

	/**
	 * Log an info message.
	 *
	 * @since 1.2.0
	 * @param string $message The message.
	 * @param array  $context Additional context.
	 * @return bool Whether logged successfully.
	 */
	public function info( $message, $context = array() );

	/**
	 * Log a warning message.
	 *
	 * @since 1.2.0
	 * @param string $message The message.
	 * @param array  $context Additional context.
	 * @return bool Whether logged successfully.
	 */
	public function warning( $message, $context = array() );

	/**
	 * Log an error message.
	 *
	 * @since 1.2.0
	 * @param string $message The message.
	 * @param array  $context Additional context.
	 * @return bool Whether logged successfully.
	 */
	public function error( $message, $context = array() );

	/**
	 * Log a critical message.
	 *
	 * @since 1.2.0
	 * @param string $message The message.
	 * @param array  $context Additional context.
	 * @return bool Whether logged successfully.
	 */
	public function critical( $message, $context = array() );

	/**
	 * Log a message with specified level.
	 *
	 * @since 1.2.0
	 * @param string $message The message.
	 * @param int    $level   The log level.
	 * @param array  $context Additional context.
	 * @return bool Whether logged successfully.
	 */
	public function log( $message, $level, $context = array() );
}
