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
		add_action( 'wp_ajax_ta_export_cache_entries', array( $this, 'ajax_export_cache_entries' ) );

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

	/**
	 * AJAX handler: Export cache entries to CSV.
	 *
	 * Supports exporting selected entries, filtered view, or all entries.
	 *
	 * @since 2.0.7
	 * @return void
	 */
	public function ajax_export_cache_entries() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$export_type = isset( $_POST['export_type'] ) ? sanitize_text_field( wp_unslash( $_POST['export_type'] ) ) : 'filtered';
		$cache_keys = isset( $_POST['cache_keys'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['cache_keys'] ) ) : array();

		// Validate export type.
		if ( ! in_array( $export_type, array( 'selected', 'filtered', 'all' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid export type.', 'third-audience' ) ) );
		}

		$csv_data = array();

		// Add CSV header.
		$csv_data[] = array(
			'Third Audience Cache Export',
		);
		$csv_data[] = array(
			'Generated',
			gmdate( 'Y-m-d H:i:s' ) . ' UTC',
		);
		$csv_data[] = array(
			'Export Type',
			ucfirst( $export_type ),
		);
		$csv_data[] = array(); // Empty row for spacing.

		// CSV column headers.
		$csv_data[] = array(
			'URL',
			'Title',
			'Size',
			'Size (bytes)',
			'Expires In',
			'Cache Key',
		);

		// Collect cache entries based on export type.
		$cache_entries = array();

		if ( 'selected' === $export_type ) {
			// Export selected entries only.
			if ( empty( $cache_keys ) ) {
				wp_send_json_error( array( 'message' => __( 'No entries selected for export.', 'third-audience' ) ) );
			}

			foreach ( $cache_keys as $cache_key ) {
				$entry = $this->cache_manager->get_entry_details( $cache_key );
				if ( $entry ) {
					$cache_entries[] = $entry;
				}
			}
		} elseif ( 'filtered' === $export_type ) {
			// Export filtered/sorted view.
			$search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
			$filters = isset( $_POST['filters'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['filters'] ) ) : array();
			$orderby = isset( $_POST['orderby'] ) ? sanitize_text_field( wp_unslash( $_POST['orderby'] ) ) : 'expiration';
			$order = isset( $_POST['order'] ) ? sanitize_text_field( wp_unslash( $_POST['order'] ) ) : 'DESC';

			// Get all matching entries (no pagination limit).
			$cache_entries = $this->cache_manager->get_cache_entries( 10000, 0, $search, $filters, $orderby, $order );
		} else {
			// Export all entries.
			$cache_entries = $this->cache_manager->get_cache_entries( 10000, 0, '', array(), 'expiration', 'DESC' );
		}

		// Add entry data to CSV.
		foreach ( $cache_entries as $entry ) {
			$csv_data[] = array(
				$entry['url'] ?? '',
				$entry['title'] ?? '',
				$entry['size_human'] ?? '',
				$entry['size'] ?? 0,
				$entry['expires_in'] ?? '',
				$entry['cache_key'] ?? '',
			);
		}

		// Generate CSV output.
		$filename = 'cache-export-' . gmdate( 'Y-m-d-H-i-s' ) . '.csv';

		wp_send_json_success( array(
			'filename' => $filename,
			'csv_data' => $csv_data,
			'count'    => count( $cache_entries ),
		) );
	}
}
