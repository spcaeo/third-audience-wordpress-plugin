<?php
/**
 * Cache Trait
 *
 * Provides caching functionality for classes.
 *
 * @package ThirdAudience
 * @since   1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait TA_Trait_Cache
 *
 * Provides caching helpers.
 *
 * @since 1.2.0
 */
trait TA_Trait_Cache {

	/**
	 * Memory cache (static variables).
	 *
	 * @var array
	 */
	private static $memory_cache = array();

	/**
	 * Memory cache hits counter.
	 *
	 * @var int
	 */
	private static $memory_hits = 0;

	/**
	 * Memory cache misses counter.
	 *
	 * @var int
	 */
	private static $memory_misses = 0;

	/**
	 * Maximum memory cache items.
	 *
	 * @var int
	 */
	private static $max_memory_items = 100;

	/**
	 * Get from memory cache.
	 *
	 * @since 1.2.0
	 * @param string $key The cache key.
	 * @return mixed|null The cached value or null.
	 */
	protected function memory_get( $key ) {
		if ( isset( self::$memory_cache[ $key ] ) ) {
			self::$memory_hits++;
			return self::$memory_cache[ $key ]['value'];
		}
		self::$memory_misses++;
		return null;
	}

	/**
	 * Set in memory cache.
	 *
	 * @since 1.2.0
	 * @param string $key   The cache key.
	 * @param mixed  $value The value.
	 * @return void
	 */
	protected function memory_set( $key, $value ) {
		// Evict oldest if at capacity.
		if ( count( self::$memory_cache ) >= self::$max_memory_items ) {
			$this->memory_evict_oldest();
		}

		self::$memory_cache[ $key ] = array(
			'value'     => $value,
			'timestamp' => time(),
		);
	}

	/**
	 * Delete from memory cache.
	 *
	 * @since 1.2.0
	 * @param string $key The cache key.
	 * @return void
	 */
	protected function memory_delete( $key ) {
		unset( self::$memory_cache[ $key ] );
	}

	/**
	 * Clear memory cache.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	protected function memory_clear() {
		self::$memory_cache  = array();
		self::$memory_hits   = 0;
		self::$memory_misses = 0;
	}

	/**
	 * Evict oldest memory cache item.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	private function memory_evict_oldest() {
		$oldest_key  = null;
		$oldest_time = PHP_INT_MAX;

		foreach ( self::$memory_cache as $key => $data ) {
			if ( $data['timestamp'] < $oldest_time ) {
				$oldest_time = $data['timestamp'];
				$oldest_key  = $key;
			}
		}

		if ( null !== $oldest_key ) {
			unset( self::$memory_cache[ $oldest_key ] );
		}
	}

	/**
	 * Get memory cache stats.
	 *
	 * @since 1.2.0
	 * @return array Memory cache statistics.
	 */
	protected function get_memory_stats() {
		return array(
			'items'    => count( self::$memory_cache ),
			'hits'     => self::$memory_hits,
			'misses'   => self::$memory_misses,
			'hit_rate' => self::$memory_hits + self::$memory_misses > 0
				? round( self::$memory_hits / ( self::$memory_hits + self::$memory_misses ) * 100, 2 )
				: 0,
		);
	}

	/**
	 * Get from object cache (wp_cache).
	 *
	 * @since 1.2.0
	 * @param string $key   The cache key.
	 * @param string $group Optional. Cache group.
	 * @return mixed|false The cached value or false.
	 */
	protected function object_cache_get( $key, $group = 'third_audience' ) {
		// Try memory cache first.
		$memory_key = $group . ':' . $key;
		$memory_val = $this->memory_get( $memory_key );

		if ( null !== $memory_val ) {
			return $memory_val;
		}

		// Try object cache.
		$found = false;
		$value = wp_cache_get( $key, $group, false, $found );

		if ( $found ) {
			// Store in memory for faster subsequent access.
			$this->memory_set( $memory_key, $value );
			return $value;
		}

		return false;
	}

	/**
	 * Set in object cache (wp_cache).
	 *
	 * @since 1.2.0
	 * @param string $key    The cache key.
	 * @param mixed  $value  The value.
	 * @param string $group  Optional. Cache group.
	 * @param int    $expire Optional. Expiration in seconds.
	 * @return bool Whether the value was set.
	 */
	protected function object_cache_set( $key, $value, $group = 'third_audience', $expire = 0 ) {
		// Set in memory cache.
		$memory_key = $group . ':' . $key;
		$this->memory_set( $memory_key, $value );

		// Set in object cache.
		return wp_cache_set( $key, $value, $group, $expire );
	}

	/**
	 * Delete from object cache.
	 *
	 * @since 1.2.0
	 * @param string $key   The cache key.
	 * @param string $group Optional. Cache group.
	 * @return bool Whether the value was deleted.
	 */
	protected function object_cache_delete( $key, $group = 'third_audience' ) {
		// Delete from memory.
		$memory_key = $group . ':' . $key;
		$this->memory_delete( $memory_key );

		// Delete from object cache.
		return wp_cache_delete( $key, $group );
	}

	/**
	 * Get or set cached value.
	 *
	 * @since 1.2.0
	 * @param string   $key      The cache key.
	 * @param callable $callback Callback to generate value if not cached.
	 * @param int      $ttl      Optional. Time to live in seconds.
	 * @param string   $group    Optional. Cache group.
	 * @return mixed The cached or generated value.
	 */
	protected function remember( $key, $callback, $ttl = 0, $group = 'third_audience' ) {
		$value = $this->object_cache_get( $key, $group );

		if ( false !== $value ) {
			return $value;
		}

		$value = call_user_func( $callback );
		$this->object_cache_set( $key, $value, $group, $ttl );

		return $value;
	}

	/**
	 * Invalidate cache by tag/pattern.
	 *
	 * @since 1.2.0
	 * @param string $pattern The pattern to match.
	 * @return int Number of items invalidated.
	 */
	protected function invalidate_by_pattern( $pattern ) {
		$count = 0;

		// Invalidate memory cache.
		foreach ( array_keys( self::$memory_cache ) as $key ) {
			if ( fnmatch( $pattern, $key ) ) {
				unset( self::$memory_cache[ $key ] );
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Add cache tags for selective invalidation.
	 *
	 * @since 1.2.0
	 * @param string $key  The cache key.
	 * @param array  $tags The tags to associate.
	 * @return void
	 */
	protected function tag_cache( $key, $tags ) {
		$tag_key = 'ta_cache_tags';
		$all_tags = get_option( $tag_key, array() );

		foreach ( $tags as $tag ) {
			if ( ! isset( $all_tags[ $tag ] ) ) {
				$all_tags[ $tag ] = array();
			}
			if ( ! in_array( $key, $all_tags[ $tag ], true ) ) {
				$all_tags[ $tag ][] = $key;
			}
		}

		update_option( $tag_key, $all_tags, false );
	}

	/**
	 * Invalidate cache by tag.
	 *
	 * @since 1.2.0
	 * @param string $tag The tag to invalidate.
	 * @return int Number of items invalidated.
	 */
	protected function invalidate_by_tag( $tag ) {
		$tag_key  = 'ta_cache_tags';
		$all_tags = get_option( $tag_key, array() );
		$count    = 0;

		if ( isset( $all_tags[ $tag ] ) ) {
			foreach ( $all_tags[ $tag ] as $key ) {
				$this->object_cache_delete( $key );
				delete_transient( $key );
				$count++;
			}
			unset( $all_tags[ $tag ] );
			update_option( $tag_key, $all_tags, false );
		}

		return $count;
	}
}
