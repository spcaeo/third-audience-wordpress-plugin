<?php
/**
 * Admin AJAX Cache Handlers - Cache-related AJAX operations.
 *
 * Handles cache browser operations, warmup, and cache management AJAX requests.
 *
 * @package ThirdAudience
 * @since   3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Admin_AJAX_Cache
 *
 * Handles cache-related AJAX operations for the admin interface.
 *
 * @since 3.3.1
 */
class TA_Admin_AJAX_Cache {

	/**
	 * Security instance.
	 *
	 * @var TA_Security
	 */
	private $security;

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Singleton instance.
	 *
	 * @var TA_Admin_AJAX_Cache|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 3.3.1
	 * @return TA_Admin_AJAX_Cache
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 3.3.1
	 */
	private function __construct() {
		$this->security = TA_Security::get_instance();
		$this->logger   = TA_Logger::get_instance();
	}

	/**
	 * Register AJAX hooks.
	 *
	 * @since 3.3.1
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_ta_delete_cache_entry', array( $this, 'ajax_delete_cache_entry' ) );
		add_action( 'wp_ajax_ta_bulk_delete_cache', array( $this, 'ajax_bulk_delete_cache' ) );
		add_action( 'wp_ajax_ta_clear_expired_cache', array( $this, 'ajax_clear_expired_cache' ) );
		add_action( 'wp_ajax_ta_regenerate_cache', array( $this, 'ajax_regenerate_cache' ) );
		add_action( 'wp_ajax_ta_view_cache_content', array( $this, 'ajax_view_cache_content' ) );
		add_action( 'wp_ajax_ta_get_warmup_stats', array( $this, 'ajax_get_warmup_stats' ) );
		add_action( 'wp_ajax_ta_warm_cache_batch', array( $this, 'ajax_warm_cache_batch' ) );
		add_action( 'wp_ajax_ta_get_recent_accesses', array( $this, 'ajax_get_recent_accesses' ) );
		add_action( 'wp_ajax_ta_clear_cache', array( $this, 'ajax_clear_cache' ) );
		add_action( 'wp_ajax_ta_regenerate_all_markdown', array( $this, 'ajax_regenerate_all_markdown' ) );
	}

	/**
	 * AJAX handler: Delete single cache entry.
	 *
	 * @since 2.0.6
	 * @return void
	 */
	public function ajax_delete_cache_entry() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$cache_key = isset( $_POST['cache_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cache_key'] ) ) : '';

		if ( empty( $cache_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid cache key.', 'third-audience' ) ) );
		}

		$cache_manager = new TA_Cache_Manager();
		$result = $cache_manager->delete( $cache_key );

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Cache entry deleted.', 'third-audience' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to delete cache entry.', 'third-audience' ) ) );
		}
	}

	/**
	 * AJAX handler: Bulk delete cache entries.
	 *
	 * @since 2.0.6
	 * @return void
	 */
	public function ajax_bulk_delete_cache() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$cache_keys = isset( $_POST['cache_keys'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['cache_keys'] ) ) : array();

		if ( empty( $cache_keys ) ) {
			wp_send_json_error( array( 'message' => __( 'No cache keys provided.', 'third-audience' ) ) );
		}

		$cache_manager = new TA_Cache_Manager();
		$cache_manager->delete_many( $cache_keys );

		wp_send_json_success( array(
			'message' => sprintf(
				/* translators: %d: number of entries deleted */
				__( '%d cache entries deleted.', 'third-audience' ),
				count( $cache_keys )
			),
		) );
	}

	/**
	 * AJAX handler: Clear expired cache entries.
	 *
	 * @since 2.0.6
	 * @return void
	 */
	public function ajax_clear_expired_cache() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$cache_manager = new TA_Cache_Manager();
		$count = $cache_manager->cleanup_expired();

		wp_send_json_success( array(
			'message' => sprintf(
				/* translators: %d: number of expired entries */
				__( '%d expired entries cleared.', 'third-audience' ),
				$count
			),
		) );
	}

	/**
	 * AJAX handler: Regenerate cache for a post.
	 *
	 * @since 2.0.6
	 * @return void
	 */
	public function ajax_regenerate_cache() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		if ( empty( $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'third-audience' ) ) );
		}

		$cache_manager = new TA_Cache_Manager();
		$result = $cache_manager->regenerate_markdown( $post_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => __( 'Cache regenerated successfully.', 'third-audience' ) ) );
	}

	/**
	 * AJAX handler: View cache content.
	 *
	 * @since 2.0.6
	 * @return void
	 */
	public function ajax_view_cache_content() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$cache_key = isset( $_POST['cache_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cache_key'] ) ) : '';

		if ( empty( $cache_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid cache key.', 'third-audience' ) ) );
		}

		$cache_manager = new TA_Cache_Manager();
		$content = $cache_manager->get( $cache_key );

		if ( false === $content ) {
			wp_send_json_error( array( 'message' => __( 'Cache entry not found.', 'third-audience' ) ) );
		}

		wp_send_json_success( array(
			'content' => $content,
			'size'    => size_format( strlen( $content ) ),
		) );
	}

	/**
	 * AJAX handler: Get cache warmup statistics.
	 *
	 * @since 2.0.6
	 * @return void
	 */
	public function ajax_get_warmup_stats() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$cache_manager = new TA_Cache_Manager();
		$stats = $cache_manager->get_warmup_stats();

		wp_send_json_success( $stats );
	}

	/**
	 * AJAX handler: Warm cache batch.
	 *
	 * @since 2.0.6
	 * @return void
	 */
	public function ajax_warm_cache_batch() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$batch_size = isset( $_POST['batch_size'] ) ? absint( $_POST['batch_size'] ) : 5;
		$offset     = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;

		try {
			$cache_manager = new TA_Cache_Manager();

			// Check if TA_Local_Converter is available.
			if ( ! class_exists( 'TA_Local_Converter' ) ) {
				wp_send_json_error( array(
					'message' => __( 'Local Converter class not found. Please ensure the plugin is properly installed.', 'third-audience' ),
				) );
			}

			$results = $cache_manager->warm_cache_batch( array(
				'limit'  => $batch_size,
				'offset' => $offset,
			) );

			// Get updated stats after warming.
			$stats = $cache_manager->get_warmup_stats();

			// Check if there were any failures.
			if ( isset( $results['failed'] ) && $results['failed'] > 0 ) {
				$this->logger->warning( 'Cache warmup batch had failures', array(
					'results' => $results,
					'offset'  => $offset,
				) );
			}

			wp_send_json_success( array(
				'results' => $results,
				'stats'   => $stats,
			) );

		} catch ( Exception $e ) {
			$this->logger->error( 'Cache warmup batch failed', array(
				'error'   => $e->getMessage(),
				'offset'  => $offset,
			) );

			wp_send_json_error( array(
				'message' => sprintf(
					/* translators: %s: error message */
					__( 'Cache warmup failed: %s', 'third-audience' ),
					$e->getMessage()
				),
			) );
		}
	}

	/**
	 * Get recent .md access attempts for live feed.
	 *
	 * @since 2.1.0
	 * @return void
	 */
	public function ajax_get_recent_accesses() {
		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'ta_bot_analytics_feed' ) ) {
			wp_send_json_error( array( 'message' => 'Nonce verification failed' ), 403 );
		}

		// Check capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ), 403 );
		}

		$analytics = TA_Bot_Analytics::get_instance();
		$accesses = $analytics->get_recent_visits( array(), 20, 0 );

		if ( empty( $accesses ) ) {
			wp_send_json_success( array( 'accesses' => array() ) );
		}

		// Format data for frontend.
		$formatted = array();
		foreach ( $accesses as $access ) {
			$formatted[] = array(
				'id'              => intval( $access['id'] ),
				'timestamp'       => $access['visit_timestamp'],
				'url'             => $access['url'],
				'bot_name'        => $access['bot_name'],
				'bot_type'        => $access['bot_type'],
				'cache_status'    => $access['cache_status'],
				'response_time'   => intval( $access['response_time'] ?? 0 ),
				'post_title'      => $access['post_title'] ?? 'Untitled',
			);
		}

		wp_send_json_success( array( 'accesses' => $formatted ) );
	}

	/**
	 * AJAX handler for clearing cache.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function ajax_clear_cache() {
		$this->security->verify_ajax_request( 'admin_ajax' );

		$cache_manager = new TA_Cache_Manager();
		$cleared       = $cache_manager->clear_all();

		$this->logger->info( 'Cache cleared (AJAX).', array( 'items' => $cleared ) );

		wp_send_json_success( array(
			/* translators: %d: Number of cached items cleared */
			'message' => sprintf( __( 'Cleared %d cached items.', 'third-audience' ), $cleared ),
			'count'   => $cleared,
		) );
	}

	/**
	 * AJAX handler for regenerating all markdown.
	 *
	 * Clears all pre-generated markdown, forcing regeneration on next access.
	 *
	 * @since 2.1.0
	 * @return void
	 */
	public function ajax_regenerate_all_markdown() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$cache_manager = new TA_Cache_Manager();
		$cleared       = $cache_manager->clear_pregenerated_markdown();

		$this->logger->info( 'All markdown regenerated (AJAX).', array( 'count' => $cleared ) );

		wp_send_json_success( array(
			/* translators: %d: Number of posts cleared */
			'message' => sprintf( __( 'Cleared pre-generated markdown for %d posts. New markdown will be generated with current settings on next access.', 'third-audience' ), $cleared ),
			'count'   => $cleared,
		) );
	}
}
