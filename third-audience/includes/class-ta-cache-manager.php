<?php
/**
 * Cache Manager - Advanced caching with multiple layers.
 *
 * Implements a multi-tier caching strategy:
 * 1. Memory cache (static variables) - fastest
 * 2. Object cache (wp_cache_*) - fast, persistent if Redis/Memcached available
 * 3. Transient cache (database) - fallback
 *
 * Also provides cache warming, statistics, and tag-based invalidation.
 *
 * @package ThirdAudience
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Cache_Manager
 *
 * Multi-tier cache management for markdown content.
 *
 * @since 1.0.0
 */
class TA_Cache_Manager implements TA_Cacheable {

	use TA_Trait_Cache;

	/**
	 * Cache key prefix for markdown content.
	 *
	 * @var string
	 */
	const CACHE_PREFIX = 'ta_md_';

	/**
	 * Cache group for object caching.
	 *
	 * @var string
	 */
	const CACHE_GROUP = 'third_audience';

	/**
	 * Stats option name.
	 *
	 * @var string
	 */
	const STATS_OPTION = 'ta_cache_stats';

	/**
	 * Cache tags option name.
	 *
	 * @var string
	 */
	const TAGS_OPTION = 'ta_cache_tags';

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Whether object cache is available.
	 *
	 * @var bool
	 */
	private $object_cache_available;

	/**
	 * Cache statistics for current request.
	 *
	 * @var array
	 */
	private $request_stats = array(
		'hits'   => 0,
		'misses' => 0,
		'writes' => 0,
	);

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->logger = TA_Logger::get_instance();
		$this->object_cache_available = $this->check_object_cache();
	}

	/**
	 * Check if persistent object cache is available.
	 *
	 * @since 1.2.0
	 * @return bool
	 */
	private function check_object_cache() {
		return wp_using_ext_object_cache();
	}

	/**
	 * Generate a cache key for a URL.
	 *
	 * @since 1.0.0
	 * @param string $identifier The URL or identifier.
	 * @return string The cache key.
	 */
	public function generate_key( $identifier ) {
		return self::CACHE_PREFIX . md5( $identifier );
	}

	/**
	 * Alias for generate_key for backward compatibility.
	 *
	 * @since 1.0.0
	 * @param string $url The URL.
	 * @return string The cache key.
	 */
	public function get_cache_key( $url ) {
		return $this->generate_key( $url );
	}

	/**
	 * Get cached markdown content.
	 *
	 * Uses multi-tier lookup: memory -> object cache -> transient.
	 *
	 * @since 1.0.0
	 * @param string $key The cache key.
	 * @return mixed The cached content or false.
	 */
	public function get( $key ) {
		// Tier 1: Memory cache (fastest).
		$memory_val = $this->memory_get( $key );
		if ( null !== $memory_val ) {
			$this->record_hit( 'memory' );
			return $memory_val;
		}

		// Tier 2: Object cache (if available).
		if ( $this->object_cache_available ) {
			$found = false;
			$value = wp_cache_get( $key, self::CACHE_GROUP, false, $found );

			if ( $found && false !== $value ) {
				// Promote to memory cache.
				$this->memory_set( $key, $value );
				$this->record_hit( 'object' );
				return $value;
			}
		}

		// Tier 3: Transient (database fallback).
		$value = get_transient( $key );

		if ( false !== $value ) {
			// Promote to higher tiers.
			$this->memory_set( $key, $value );
			if ( $this->object_cache_available ) {
				wp_cache_set( $key, $value, self::CACHE_GROUP, $this->get_ttl() );
			}
			$this->record_hit( 'transient' );
			return $value;
		}

		$this->record_miss();
		return false;
	}

	/**
	 * Set cached markdown content.
	 *
	 * Writes to all cache tiers for consistency.
	 *
	 * @since 1.0.0
	 * @param string $key   The cache key.
	 * @param mixed  $value The content to cache.
	 * @param int    $ttl   Optional. Time to live in seconds.
	 * @return bool Success.
	 */
	public function set( $key, $value, $ttl = 0 ) {
		$ttl = $ttl > 0 ? $ttl : $this->get_ttl();

		// Tier 1: Memory cache.
		$this->memory_set( $key, $value );

		// Tier 2: Object cache.
		if ( $this->object_cache_available ) {
			wp_cache_set( $key, $value, self::CACHE_GROUP, $ttl );
		}

		// Tier 3: Transient (persistent).
		$result = set_transient( $key, $value, $ttl );

		$this->request_stats['writes']++;
		$this->update_global_stats( 'write' );

		return $result;
	}

	/**
	 * Delete cached markdown content.
	 *
	 * Removes from all cache tiers.
	 *
	 * @since 1.0.0
	 * @param string $key The cache key.
	 * @return bool Success.
	 */
	public function delete( $key ) {
		// Tier 1: Memory cache.
		$this->memory_delete( $key );

		// Tier 2: Object cache.
		if ( $this->object_cache_available ) {
			wp_cache_delete( $key, self::CACHE_GROUP );
		}

		// Tier 3: Transient.
		return delete_transient( $key );
	}

	/**
	 * Check if cache key exists.
	 *
	 * @since 1.2.0
	 * @param string $key The cache key.
	 * @return bool Whether the key exists.
	 */
	public function has( $key ) {
		return false !== $this->get( $key );
	}

	/**
	 * Get the configured TTL.
	 *
	 * @since 1.2.0
	 * @return int TTL in seconds.
	 */
	private function get_ttl() {
		return (int) get_option( 'ta_cache_ttl', 86400 );
	}

	/**
	 * Invalidate cache for a post.
	 *
	 * @since 1.0.0
	 * @param int $post_id The post ID.
	 * @return void
	 */
	public function invalidate_post_cache( $post_id ) {
		// Avoid auto-saves and revisions.
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		$post = get_post( $post_id );

		if ( ! $post || 'publish' !== $post->post_status ) {
			return;
		}

		// Get the post URL.
		$url = get_permalink( $post_id );
		if ( ! $url ) {
			return;
		}

		// Delete all URL variations.
		$urls = array(
			$url,
			trailingslashit( $url ),
			untrailingslashit( $url ),
		);

		foreach ( $urls as $u ) {
			$cache_key = $this->generate_key( $u );
			$this->delete( $cache_key );
		}

		// Invalidate by post tag.
		$this->invalidate_by_tag( 'post_' . $post_id );

		$this->logger->debug( 'Cache invalidated for post.', array(
			'post_id' => $post_id,
			'url'     => $url,
		) );
	}

	// =========================================================================
	// Pre-generation (Save on Publish)
	// =========================================================================

	/**
	 * Post meta key for stored markdown.
	 *
	 * @var string
	 */
	const META_MARKDOWN = '_ta_markdown';

	/**
	 * Post meta key for generation timestamp.
	 *
	 * @var string
	 */
	const META_GENERATED = '_ta_markdown_generated';

	/**
	 * Pre-generate markdown when a post is published.
	 *
	 * @since 1.3.0
	 * @param int     $post_id The post ID.
	 * @param WP_Post $post    The post object.
	 * @return bool True on success, false on failure.
	 */
	public function pre_generate_markdown( $post_id, $post ) {
		// Skip autosaves and revisions.
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return false;
		}

		// Only generate for published posts.
		if ( 'publish' !== $post->post_status ) {
			delete_post_meta( $post_id, self::META_MARKDOWN );
			delete_post_meta( $post_id, self::META_GENERATED );
			return false;
		}

		// Check if post type is enabled.
		$enabled_types = get_option( 'ta_enabled_post_types', array( 'post', 'page' ) );
		if ( ! in_array( $post->post_type, $enabled_types, true ) ) {
			return false;
		}

		// Get the post URL.
		$url = get_permalink( $post_id );
		if ( ! $url ) {
			return false;
		}

		// Convert locally.
		$converter = new TA_Local_Converter();
		$markdown  = $converter->convert_post( $post_id, array(
			'include_frontmatter'    => true,
			'extract_main_content'   => true,
			'include_title'          => true,
			'include_excerpt'        => true,
			'include_featured_image' => true,
		) );

		if ( is_wp_error( $markdown ) ) {
			$this->logger->warning( 'Pre-generation failed.', array(
				'post_id' => $post_id,
				'url'     => $url,
				'error'   => $markdown->get_error_message(),
			) );
			do_action( 'ta_pre_generation_failed', $post_id, $url, $markdown );
			return false;
		}

		// Store in post meta (permanent).
		update_post_meta( $post_id, self::META_MARKDOWN, $markdown );
		update_post_meta( $post_id, self::META_GENERATED, time() );

		// Also store in transient cache for faster access.
		$cache_key = $this->generate_key( $url );
		$this->set( $cache_key, $markdown );

		$this->logger->debug( 'Markdown pre-generated.', array(
			'post_id' => $post_id,
			'url'     => $url,
			'size'    => strlen( $markdown ),
		) );

		do_action( 'ta_markdown_pre_generated', $post_id, $url, $markdown );
		return true;
	}

	/**
	 * Get pre-generated markdown for a post.
	 *
	 * @since 1.3.0
	 * @param int $post_id The post ID.
	 * @return string|false The markdown or false if not found.
	 */
	public function get_pre_generated_markdown( $post_id ) {
		$markdown = get_post_meta( $post_id, self::META_MARKDOWN, true );
		return empty( $markdown ) ? false : $markdown;
	}

	/**
	 * Get the timestamp when markdown was pre-generated.
	 *
	 * @since 1.3.0
	 * @param int $post_id The post ID.
	 * @return int|false The timestamp or false if not found.
	 */
	public function get_pre_generated_timestamp( $post_id ) {
		$timestamp = get_post_meta( $post_id, self::META_GENERATED, true );
		return empty( $timestamp ) ? false : (int) $timestamp;
	}

	/**
	 * Check if pre-generated markdown is fresh (newer than post modification).
	 *
	 * @since 1.3.0
	 * @param int $post_id The post ID.
	 * @return bool True if fresh, false otherwise.
	 */
	public function has_fresh_pre_generated( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}

		$generated_time = $this->get_pre_generated_timestamp( $post_id );
		if ( ! $generated_time ) {
			return false;
		}

		$modified_time = strtotime( $post->post_modified_gmt );
		return $generated_time >= $modified_time;
	}

	/**
	 * Manually regenerate markdown for a post.
	 *
	 * @since 1.3.0
	 * @param int $post_id The post ID.
	 * @return bool True on success, false on failure.
	 */
	public function regenerate_markdown( $post_id ) {
		$post = get_post( $post_id );
		return $post ? $this->pre_generate_markdown( $post_id, $post ) : false;
	}

	/**
	 * Batch invalidate multiple posts.
	 *
	 * @since 1.2.0
	 * @param array $post_ids Array of post IDs.
	 * @return int Number of caches invalidated.
	 */
	public function batch_invalidate( $post_ids ) {
		$count = 0;

		foreach ( $post_ids as $post_id ) {
			$this->invalidate_post_cache( $post_id );
			$count++;
		}

		return $count;
	}

	/**
	 * Clear all Third Audience cache.
	 *
	 * @since 1.0.0
	 * @return int Number of items cleared.
	 */
	public function clear_all() {
		global $wpdb;

		// Clear memory cache.
		$this->memory_clear();

		// Clear object cache group.
		if ( $this->object_cache_available && function_exists( 'wp_cache_flush_group' ) ) {
			wp_cache_flush_group( self::CACHE_GROUP );
		}

		// Delete all transients with our prefix.
		$count = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				'_transient_' . self::CACHE_PREFIX . '%',
				'_transient_timeout_' . self::CACHE_PREFIX . '%'
			)
		);

		// Clear cache tags.
		delete_option( self::TAGS_OPTION );

		// Reset stats.
		$this->reset_stats();

		$this->logger->info( 'All cache cleared.', array( 'count' => $count / 2 ) );

		return (int) ( $count / 2 ); // Each transient has two entries.
	}

	/**
	 * Get cache statistics.
	 *
	 * @since 1.0.0
	 * @return array Cache statistics.
	 */
	public function get_stats() {
		global $wpdb;

		// Get transient count and size.
		$transient_stats = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT COUNT(*) as count, COALESCE(SUM(LENGTH(option_value)), 0) as size
				 FROM {$wpdb->options}
				 WHERE option_name LIKE %s",
				'_transient_' . self::CACHE_PREFIX . '%'
			),
			ARRAY_A
		);

		// Get global stats.
		$global_stats = get_option( self::STATS_OPTION, array(
			'total_hits'   => 0,
			'total_misses' => 0,
			'total_writes' => 0,
			'last_reset'   => current_time( 'mysql' ),
		) );

		// Calculate hit rate.
		$total_requests = $global_stats['total_hits'] + $global_stats['total_misses'];
		$hit_rate       = $total_requests > 0
			? round( ( $global_stats['total_hits'] / $total_requests ) * 100, 2 )
			: 0;

		return array(
			'count'            => (int) $transient_stats['count'],
			'size_bytes'       => (int) $transient_stats['size'],
			'size_human'       => size_format( (int) $transient_stats['size'] ),
			'total_hits'       => $global_stats['total_hits'],
			'total_misses'     => $global_stats['total_misses'],
			'total_writes'     => $global_stats['total_writes'],
			'hit_rate'         => $hit_rate,
			'object_cache'     => $this->object_cache_available,
			'memory_stats'     => $this->get_memory_stats(),
			'request_stats'    => $this->request_stats,
			'last_reset'       => $global_stats['last_reset'],
		);
	}

	/**
	 * Record a cache hit.
	 *
	 * @since 1.2.0
	 * @param string $tier The cache tier that was hit.
	 * @return void
	 */
	private function record_hit( $tier ) {
		$this->request_stats['hits']++;
		$this->update_global_stats( 'hit' );
	}

	/**
	 * Record a cache miss.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	private function record_miss() {
		$this->request_stats['misses']++;
		$this->update_global_stats( 'miss' );
	}

	/**
	 * Update global statistics.
	 *
	 * @since 1.2.0
	 * @param string $type The stat type (hit, miss, write).
	 * @return void
	 */
	private function update_global_stats( $type ) {
		// Use non-autoload option to minimize overhead.
		$stats = get_option( self::STATS_OPTION, array(
			'total_hits'   => 0,
			'total_misses' => 0,
			'total_writes' => 0,
			'last_reset'   => current_time( 'mysql' ),
		) );

		switch ( $type ) {
			case 'hit':
				$stats['total_hits']++;
				break;
			case 'miss':
				$stats['total_misses']++;
				break;
			case 'write':
				$stats['total_writes']++;
				break;
		}

		// Batch updates - only persist every 10 operations to reduce DB writes.
		$total = $stats['total_hits'] + $stats['total_misses'] + $stats['total_writes'];
		if ( 0 === $total % 10 ) {
			update_option( self::STATS_OPTION, $stats, false );
		}
	}

	/**
	 * Reset cache statistics.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function reset_stats() {
		update_option( self::STATS_OPTION, array(
			'total_hits'   => 0,
			'total_misses' => 0,
			'total_writes' => 0,
			'last_reset'   => current_time( 'mysql' ),
		), false );
	}

	// =========================================================================
	// Cache Warming
	// =========================================================================

	/**
	 * Warm cache for popular posts.
	 *
	 * @since 1.2.0
	 * @param int $limit Number of posts to warm.
	 * @return array Results of warming operation.
	 */
	public function warm_cache( $limit = 10 ) {
		$enabled_types = get_option( 'ta_enabled_post_types', array( 'post', 'page' ) );

		$posts = get_posts( array(
			'post_type'      => $enabled_types,
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'orderby'        => 'modified',
			'order'          => 'DESC',
			'fields'         => 'ids',
		) );

		$results = array(
			'total'   => count( $posts ),
			'warmed'  => 0,
			'skipped' => 0,
			'failed'  => 0,
		);

		$converter = new TA_Local_Converter();

		foreach ( $posts as $post_id ) {
			$url       = get_permalink( $post_id );
			$cache_key = $this->generate_key( $url );

			// Skip if already cached.
			if ( $this->has( $cache_key ) ) {
				$results['skipped']++;
				continue;
			}

			// Fetch and cache.
			$markdown = $this->fetch_and_cache( $url, $converter );

			if ( false !== $markdown ) {
				$results['warmed']++;
			} else {
				$results['failed']++;
			}

			// Small delay to avoid overwhelming the worker.
			usleep( 100000 ); // 100ms.
		}

		$this->logger->info( 'Cache warming completed.', $results );

		return $results;
	}

	/**
	 * Schedule cache warming via cron.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function schedule_warming() {
		if ( ! wp_next_scheduled( 'ta_cache_warm_cron' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'ta_cache_warm_cron' );
		}
	}

	/**
	 * Fetch markdown and cache it (using local converter).
	 *
	 * @since 2.0.0
	 * @param string             $url       The URL to fetch.
	 * @param TA_Local_Converter $converter Optional. Local converter instance.
	 * @return string|false The markdown or false on failure.
	 */
	private function fetch_and_cache( $url, $converter = null ) {
		if ( null === $converter ) {
			$converter = new TA_Local_Converter();
		}

		// Get post ID from URL.
		$post_id = url_to_postid( $url );
		if ( ! $post_id ) {
			return false;
		}

		// Convert locally.
		$markdown = $converter->convert_post( $post_id, array(
			'include_frontmatter'    => true,
			'extract_main_content'   => true,
			'include_title'          => true,
			'include_excerpt'        => true,
			'include_featured_image' => true,
		) );

		if ( is_wp_error( $markdown ) ) {
			return false;
		}

		// Cache the result.
		$cache_key = $this->generate_key( $url );
		$this->set( $cache_key, $markdown );

		// Tag with post ID.
		$this->tag_cache( $cache_key, array( 'post_' . $post_id ) );

		return $markdown;
	}

	// =========================================================================
	// Batch Operations
	// =========================================================================

	/**
	 * Get multiple cached items at once.
	 *
	 * @since 1.2.0
	 * @param array $keys Array of cache keys.
	 * @return array Array of key => value pairs.
	 */
	public function get_many( $keys ) {
		$results = array();

		// Try object cache multi-get if available.
		if ( $this->object_cache_available && function_exists( 'wp_cache_get_multiple' ) ) {
			$results = wp_cache_get_multiple( $keys, self::CACHE_GROUP );
		}

		// Fill in missing from transients.
		foreach ( $keys as $key ) {
			if ( ! isset( $results[ $key ] ) || false === $results[ $key ] ) {
				$results[ $key ] = $this->get( $key );
			}
		}

		return $results;
	}

	/**
	 * Set multiple cached items at once.
	 *
	 * @since 1.2.0
	 * @param array $items Array of key => value pairs.
	 * @param int   $ttl   Optional. Time to live in seconds.
	 * @return int Number of items cached.
	 */
	public function set_many( $items, $ttl = 0 ) {
		$count = 0;

		foreach ( $items as $key => $value ) {
			if ( $this->set( $key, $value, $ttl ) ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Delete multiple cached items at once.
	 *
	 * @since 1.2.0
	 * @param array $keys Array of cache keys.
	 * @return int Number of items deleted.
	 */
	public function delete_many( $keys ) {
		$count = 0;

		foreach ( $keys as $key ) {
			if ( $this->delete( $key ) ) {
				$count++;
			}
		}

		return $count;
	}

	// =========================================================================
	// Utilities
	// =========================================================================

	/**
	 * Get expired cache entries.
	 *
	 * @since 1.2.0
	 * @return array Array of expired cache keys.
	 */
	public function get_expired_entries() {
		global $wpdb;

		$expired = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT REPLACE(option_name, '_transient_timeout_', '')
				 FROM {$wpdb->options}
				 WHERE option_name LIKE %s
				 AND option_value < %d",
				'_transient_timeout_' . self::CACHE_PREFIX . '%',
				time()
			)
		);

		return $expired;
	}

	/**
	 * Clean up expired entries.
	 *
	 * @since 1.2.0
	 * @return int Number of entries cleaned.
	 */
	public function cleanup_expired() {
		$expired = $this->get_expired_entries();
		$count   = 0;

		foreach ( $expired as $key ) {
			$this->delete( $key );
			$count++;
		}

		if ( $count > 0 ) {
			$this->logger->debug( 'Cleaned up expired cache entries.', array( 'count' => $count ) );
		}

		return $count;
	}

	/**
	 * Get cache health information.
	 *
	 * @since 1.2.0
	 * @return array Cache health information.
	 */
	public function get_health() {
		$stats = $this->get_stats();

		$health = array(
			'status'  => 'healthy',
			'issues'  => array(),
		);

		// Check hit rate.
		if ( $stats['hit_rate'] < 50 && ( $stats['total_hits'] + $stats['total_misses'] ) > 100 ) {
			$health['status']   = 'degraded';
			$health['issues'][] = 'Low cache hit rate: ' . $stats['hit_rate'] . '%';
		}

		// Check cache size.
		if ( $stats['size_bytes'] > 50 * 1024 * 1024 ) { // 50MB.
			$health['issues'][] = 'Large cache size: ' . $stats['size_human'];
		}

		// Check object cache.
		if ( ! $this->object_cache_available ) {
			$health['issues'][] = 'No persistent object cache (Redis/Memcached recommended)';
		}

		return $health;
	}
}
