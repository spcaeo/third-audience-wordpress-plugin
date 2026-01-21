<?php
/**
 * Cacheable Interface
 *
 * Interface for components that support caching functionality.
 *
 * @package ThirdAudience
 * @since   1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface TA_Cacheable
 *
 * Defines the contract for cacheable components.
 *
 * @since 1.2.0
 */
interface TA_Cacheable {

	/**
	 * Get cached data.
	 *
	 * @since 1.2.0
	 * @param string $key The cache key.
	 * @return mixed The cached data or false if not found.
	 */
	public function get( $key );

	/**
	 * Set cached data.
	 *
	 * @since 1.2.0
	 * @param string $key   The cache key.
	 * @param mixed  $value The value to cache.
	 * @param int    $ttl   Optional. Time to live in seconds.
	 * @return bool Whether the data was cached successfully.
	 */
	public function set( $key, $value, $ttl = 0 );

	/**
	 * Delete cached data.
	 *
	 * @since 1.2.0
	 * @param string $key The cache key.
	 * @return bool Whether the data was deleted successfully.
	 */
	public function delete( $key );

	/**
	 * Check if cache key exists.
	 *
	 * @since 1.2.0
	 * @param string $key The cache key.
	 * @return bool Whether the key exists.
	 */
	public function has( $key );

	/**
	 * Clear all cached data.
	 *
	 * @since 1.2.0
	 * @return int Number of items cleared.
	 */
	public function clear_all();

	/**
	 * Get cache statistics.
	 *
	 * @since 1.2.0
	 * @return array Cache statistics.
	 */
	public function get_stats();

	/**
	 * Generate a cache key.
	 *
	 * @since 1.2.0
	 * @param string $identifier The identifier to generate key for.
	 * @return string The cache key.
	 */
	public function generate_key( $identifier );
}
