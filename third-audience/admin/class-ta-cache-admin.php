<?php
/**
 * Cache Administration Class
 *
 * Handles Cache Browser and Cache Warmup admin functionality.
 *
 * @package ThirdAudience
 * @since   1.6.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cache Administration Class.
 *
 * @since 1.6.0
 */
class TA_Cache_Admin {

	/**
	 * Security manager instance.
	 *
	 * @var TA_Security
	 */
	private $security;

	/**
	 * Cache manager instance.
	 *
	 * @var TA_Cache_Manager
	 */
	private $cache_manager;

	/**
	 * Constructor.
	 *
	 * @since 1.6.0
	 * @param TA_Security $security Security manager instance.
	 */
	public function __construct( $security ) {
		$this->security      = $security;
		$this->cache_manager = new TA_Cache_Manager();
	}

	/**
	 * Initialize hooks.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function init() {
		// Cache Browser AJAX handlers.
		add_action( 'wp_ajax_ta_delete_cache_entry', array( $this, 'ajax_delete_cache_entry' ) );
		add_action( 'wp_ajax_ta_bulk_delete_cache', array( $this, 'ajax_bulk_delete_cache' ) );
		add_action( 'wp_ajax_ta_clear_expired_cache', array( $this, 'ajax_clear_expired_cache' ) );
		add_action( 'wp_ajax_ta_regenerate_cache', array( $this, 'ajax_regenerate_cache' ) );
		add_action( 'wp_ajax_ta_view_cache_content', array( $this, 'ajax_view_cache_content' ) );

		// Cache Warmup AJAX handlers.
		add_action( 'wp_ajax_ta_get_warmup_stats', array( $this, 'ajax_get_warmup_stats' ) );
		add_action( 'wp_ajax_ta_start_warmup_batch', array( $this, 'ajax_start_warmup_batch' ) );
	}

	/**
	 * AJAX handler: Delete single cache entry.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function ajax_delete_cache_entry() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$cache_key = isset( $_POST['cache_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cache_key'] ) ) : '';
		if ( empty( $cache_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid cache key.', 'third-audience' ) ) );
		}

		$deleted = $this->cache_manager->delete( $cache_key );

		if ( $deleted ) {
			wp_send_json_success( array( 'message' => __( 'Cache entry deleted.', 'third-audience' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to delete cache entry.', 'third-audience' ) ) );
		}
	}

	/**
	 * AJAX handler: Bulk delete cache entries.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function ajax_bulk_delete_cache() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$cache_keys = isset( $_POST['cache_keys'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['cache_keys'] ) ) : array();
		if ( empty( $cache_keys ) ) {
			wp_send_json_error( array( 'message' => __( 'No cache keys provided.', 'third-audience' ) ) );
		}

		$deleted = $this->cache_manager->delete_many( $cache_keys );

		wp_send_json_success( array(
			'message' => sprintf(
				/* translators: %d: Number of entries deleted */
				__( 'Deleted %d cache entries.', 'third-audience' ),
				count( $deleted )
			),
			'deleted' => count( $deleted ),
		) );
	}

	/**
	 * AJAX handler: Clear expired cache entries.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function ajax_clear_expired_cache() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$expired_keys = $this->cache_manager->get_expired_entries();
		if ( empty( $expired_keys ) ) {
			wp_send_json_success( array( 'message' => __( 'No expired entries found.', 'third-audience' ) ) );
		}

		$deleted = $this->cache_manager->delete_many( $expired_keys );

		wp_send_json_success( array(
			'message' => sprintf(
				/* translators: %d: Number of entries deleted */
				__( 'Cleared %d expired entries.', 'third-audience' ),
				count( $deleted )
			),
			'deleted' => count( $deleted ),
		) );
	}

	/**
	 * AJAX handler: Regenerate cache for a post.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function ajax_regenerate_cache() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		if ( ! $post_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'third-audience' ) ) );
		}

		$result = $this->cache_manager->regenerate_markdown( $post_id );

		if ( false !== $result ) {
			wp_send_json_success( array( 'message' => __( 'Cache regenerated successfully.', 'third-audience' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to regenerate cache.', 'third-audience' ) ) );
		}
	}

	/**
	 * AJAX handler: View cache content.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function ajax_view_cache_content() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$cache_key = isset( $_POST['cache_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cache_key'] ) ) : '';
		if ( empty( $cache_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid cache key.', 'third-audience' ) ) );
		}

		$content = $this->cache_manager->get( $cache_key );

		if ( false === $content ) {
			wp_send_json_error( array( 'message' => __( 'Cache entry not found.', 'third-audience' ) ) );
		}

		wp_send_json_success( array( 'content' => $content, 'size' => size_format( strlen( $content ) ) ) );
	}

	/**
	 * AJAX handler: Get warmup statistics.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function ajax_get_warmup_stats() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$stats = $this->cache_manager->get_warmup_stats();

		wp_send_json_success( $stats );
	}

	/**
	 * AJAX handler: Start warmup batch.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function ajax_start_warmup_batch() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$batch_size = isset( $_POST['batch_size'] ) ? absint( $_POST['batch_size'] ) : 10;
		$offset     = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;
		$post_types = isset( $_POST['post_types'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['post_types'] ) ) : array();

		$args = array(
			'batch_size' => min( $batch_size, 50 ), // Max 50 per batch for safety.
			'offset'     => $offset,
		);

		if ( ! empty( $post_types ) ) {
			$args['post_type'] = $post_types;
		}

		$results = $this->cache_manager->warm_cache_batch( $args );

		// Get updated stats.
		$stats = $this->cache_manager->get_warmup_stats();

		wp_send_json_success( array(
			'results' => $results,
			'stats'   => $stats,
			'message' => sprintf(
				/* translators: 1: Warmed count, 2: Total processed */
				__( 'Warmed %1$d of %2$d entries.', 'third-audience' ),
				$results['warmed'],
				$results['processed']
			),
		) );
	}
}
