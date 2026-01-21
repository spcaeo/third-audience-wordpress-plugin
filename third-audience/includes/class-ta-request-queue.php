<?php
/**
 * Request Queue - Implements request queuing for high-traffic scenarios.
 *
 * Provides a queue system for conversion requests to prevent overwhelming
 * the worker service during traffic spikes.
 *
 * @package ThirdAudience
 * @since   1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Request_Queue
 *
 * Request queuing for Third Audience conversion requests.
 *
 * @since 1.2.0
 */
class TA_Request_Queue {

	/**
	 * Option key for queue data.
	 *
	 * @var string
	 */
	const QUEUE_OPTION = 'ta_request_queue';

	/**
	 * Option key for queue settings.
	 *
	 * @var string
	 */
	const SETTINGS_OPTION = 'ta_queue_settings';

	/**
	 * Maximum queue size.
	 *
	 * @var int
	 */
	const DEFAULT_MAX_SIZE = 50;

	/**
	 * Batch processing size.
	 *
	 * @var int
	 */
	const DEFAULT_BATCH_SIZE = 5;

	/**
	 * Queue item statuses.
	 */
	const STATUS_PENDING    = 'pending';
	const STATUS_PROCESSING = 'processing';
	const STATUS_COMPLETED  = 'completed';
	const STATUS_FAILED     = 'failed';

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Cache manager instance.
	 *
	 * @var TA_Cache_Manager
	 */
	private $cache_manager;

	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 */
	public function __construct() {
		$this->logger        = TA_Logger::get_instance();
		$this->cache_manager = new TA_Cache_Manager();
	}

	/**
	 * Get queue settings.
	 *
	 * @since 1.2.0
	 * @return array Queue settings.
	 */
	public function get_settings() {
		return get_option( self::SETTINGS_OPTION, array(
			'enabled'    => false, // Disabled by default.
			'max_size'   => self::DEFAULT_MAX_SIZE,
			'batch_size' => self::DEFAULT_BATCH_SIZE,
			'auto_process' => true,
		) );
	}

	/**
	 * Save queue settings.
	 *
	 * @since 1.2.0
	 * @param array $settings The settings to save.
	 * @return bool Whether the settings were saved.
	 */
	public function save_settings( $settings ) {
		$sanitized = array(
			'enabled'      => ! empty( $settings['enabled'] ),
			'max_size'     => absint( $settings['max_size'] ?? self::DEFAULT_MAX_SIZE ),
			'batch_size'   => absint( $settings['batch_size'] ?? self::DEFAULT_BATCH_SIZE ),
			'auto_process' => ! empty( $settings['auto_process'] ),
		);

		return update_option( self::SETTINGS_OPTION, $sanitized, false );
	}

	/**
	 * Check if queue is enabled.
	 *
	 * @since 1.2.0
	 * @return bool Whether the queue is enabled.
	 */
	public function is_enabled() {
		$settings = $this->get_settings();
		return ! empty( $settings['enabled'] );
	}

	/**
	 * Get the queue.
	 *
	 * @since 1.2.0
	 * @return array The queue items.
	 */
	public function get_queue() {
		return get_option( self::QUEUE_OPTION, array() );
	}

	/**
	 * Save the queue.
	 *
	 * @since 1.2.0
	 * @param array $queue The queue to save.
	 * @return bool Whether the queue was saved.
	 */
	private function save_queue( $queue ) {
		return update_option( self::QUEUE_OPTION, $queue, false );
	}

	/**
	 * Add a URL to the queue.
	 *
	 * @since 1.2.0
	 * @param string $url      The URL to queue.
	 * @param array  $options  Optional. Conversion options.
	 * @param int    $priority Optional. Priority (lower = higher priority).
	 * @return string|WP_Error Queue item ID or error.
	 */
	public function add( $url, $options = array(), $priority = 10 ) {
		$settings = $this->get_settings();
		$queue    = $this->get_queue();

		// Check queue size.
		if ( count( $queue ) >= $settings['max_size'] ) {
			$this->logger->warning( 'Request queue full.', array(
				'url'      => $url,
				'max_size' => $settings['max_size'],
			) );
			return new WP_Error( 'queue_full', __( 'Request queue is full. Please try again later.', 'third-audience' ) );
		}

		// Check for duplicate URL.
		foreach ( $queue as $item ) {
			if ( $item['url'] === $url && self::STATUS_PENDING === $item['status'] ) {
				return $item['id']; // Return existing item ID.
			}
		}

		// Generate unique ID.
		$id = wp_generate_uuid4();

		// Create queue item.
		$item = array(
			'id'         => $id,
			'url'        => $url,
			'options'    => $options,
			'priority'   => $priority,
			'status'     => self::STATUS_PENDING,
			'created_at' => current_time( 'mysql' ),
			'updated_at' => current_time( 'mysql' ),
			'attempts'   => 0,
			'error'      => null,
		);

		$queue[] = $item;

		// Sort by priority.
		usort( $queue, function ( $a, $b ) {
			return $a['priority'] - $b['priority'];
		} );

		$this->save_queue( $queue );

		$this->logger->debug( 'URL added to queue.', array(
			'id'  => $id,
			'url' => $url,
		) );

		// Schedule processing if auto-process is enabled.
		if ( $settings['auto_process'] && ! wp_next_scheduled( 'ta_process_queue' ) ) {
			wp_schedule_single_event( time() + 10, 'ta_process_queue' );
		}

		return $id;
	}

	/**
	 * Get a queue item by ID.
	 *
	 * @since 1.2.0
	 * @param string $id The item ID.
	 * @return array|null The queue item or null.
	 */
	public function get_item( $id ) {
		$queue = $this->get_queue();

		foreach ( $queue as $item ) {
			if ( $item['id'] === $id ) {
				return $item;
			}
		}

		return null;
	}

	/**
	 * Update a queue item.
	 *
	 * @since 1.2.0
	 * @param string $id      The item ID.
	 * @param array  $updates The updates to apply.
	 * @return bool Whether the item was updated.
	 */
	private function update_item( $id, $updates ) {
		$queue = $this->get_queue();

		foreach ( $queue as $index => $item ) {
			if ( $item['id'] === $id ) {
				$queue[ $index ] = array_merge( $item, $updates, array(
					'updated_at' => current_time( 'mysql' ),
				) );
				return $this->save_queue( $queue );
			}
		}

		return false;
	}

	/**
	 * Remove a queue item.
	 *
	 * @since 1.2.0
	 * @param string $id The item ID.
	 * @return bool Whether the item was removed.
	 */
	public function remove( $id ) {
		$queue = $this->get_queue();

		foreach ( $queue as $index => $item ) {
			if ( $item['id'] === $id ) {
				unset( $queue[ $index ] );
				return $this->save_queue( array_values( $queue ) );
			}
		}

		return false;
	}

	/**
	 * Process the queue.
	 *
	 * @since 1.2.0
	 * @param int|null $batch_size Optional. Number of items to process.
	 * @return array Processing results.
	 */
	public function process( $batch_size = null ) {
		$settings   = $this->get_settings();
		$batch_size = $batch_size ?? $settings['batch_size'];
		$queue      = $this->get_queue();

		$results = array(
			'processed' => 0,
			'succeeded' => 0,
			'failed'    => 0,
			'items'     => array(),
		);

		// Get pending items.
		$pending = array_filter( $queue, function ( $item ) {
			return self::STATUS_PENDING === $item['status'];
		} );

		// Process up to batch_size items.
		$to_process = array_slice( $pending, 0, $batch_size );

		foreach ( $to_process as $item ) {
			$result = $this->process_item( $item );

			$results['processed']++;
			$results['items'][ $item['id'] ] = $result;

			if ( $result['success'] ) {
				$results['succeeded']++;
			} else {
				$results['failed']++;
			}
		}

		// Clean up completed/failed items older than 1 hour.
		$this->cleanup();

		$this->logger->info( 'Queue processing completed.', $results );

		return $results;
	}

	/**
	 * Process a single queue item.
	 *
	 * @since 1.2.0
	 * @param array $item The queue item.
	 * @return array Processing result.
	 */
	private function process_item( $item ) {
		$id = $item['id'];

		// Mark as processing.
		$this->update_item( $id, array(
			'status'   => self::STATUS_PROCESSING,
			'attempts' => $item['attempts'] + 1,
		) );

		// Try to convert locally.
		$post_id = url_to_postid( $item['url'] );
		if ( ! $post_id ) {
			$this->update_item( $id, array(
				'status' => self::STATUS_FAILED,
				'error'  => 'Post not found for URL',
			) );
			return array(
				'success' => false,
				'error'   => 'Post not found for URL',
			);
		}

		$converter = new TA_Local_Converter();
		$markdown  = $converter->convert_post( $post_id, array_merge(
			array(
				'include_frontmatter'    => true,
				'extract_main_content'   => true,
				'include_title'          => true,
				'include_excerpt'        => true,
				'include_featured_image' => true,
			),
			$item['options'] ?? array()
		) );

		if ( is_wp_error( $markdown ) ) {
			// Check if we should retry.
			if ( $item['attempts'] < 3 ) {
				$this->update_item( $id, array(
					'status' => self::STATUS_PENDING,
					'error'  => $markdown->get_error_message(),
				) );
			} else {
				$this->update_item( $id, array(
					'status' => self::STATUS_FAILED,
					'error'  => $markdown->get_error_message(),
				) );
			}

			return array(
				'success' => false,
				'error'   => $markdown->get_error_message(),
			);
		}

		// Cache the result.
		$cache_key = $this->cache_manager->generate_key( $item['url'] );
		$this->cache_manager->set( $cache_key, $markdown );

		// Mark as completed.
		$this->update_item( $id, array(
			'status' => self::STATUS_COMPLETED,
		) );

		return array(
			'success'  => true,
			'markdown' => $markdown,
		);
	}

	/**
	 * Clean up old completed/failed items.
	 *
	 * @since 1.2.0
	 * @param int $max_age Optional. Maximum age in seconds.
	 * @return int Number of items removed.
	 */
	public function cleanup( $max_age = 3600 ) {
		$queue      = $this->get_queue();
		$cutoff     = gmdate( 'Y-m-d H:i:s', time() - $max_age );
		$initial    = count( $queue );

		$queue = array_filter( $queue, function ( $item ) use ( $cutoff ) {
			// Keep pending and processing items.
			if ( in_array( $item['status'], array( self::STATUS_PENDING, self::STATUS_PROCESSING ), true ) ) {
				return true;
			}
			// Keep completed/failed items newer than cutoff.
			return $item['updated_at'] > $cutoff;
		} );

		$this->save_queue( array_values( $queue ) );

		return $initial - count( $queue );
	}

	/**
	 * Get queue statistics.
	 *
	 * @since 1.2.0
	 * @return array Queue statistics.
	 */
	public function get_stats() {
		$queue    = $this->get_queue();
		$settings = $this->get_settings();

		$stats = array(
			'enabled'    => $this->is_enabled(),
			'total'      => count( $queue ),
			'max_size'   => $settings['max_size'],
			'batch_size' => $settings['batch_size'],
			'pending'    => 0,
			'processing' => 0,
			'completed'  => 0,
			'failed'     => 0,
		);

		foreach ( $queue as $item ) {
			switch ( $item['status'] ) {
				case self::STATUS_PENDING:
					$stats['pending']++;
					break;
				case self::STATUS_PROCESSING:
					$stats['processing']++;
					break;
				case self::STATUS_COMPLETED:
					$stats['completed']++;
					break;
				case self::STATUS_FAILED:
					$stats['failed']++;
					break;
			}
		}

		return $stats;
	}

	/**
	 * Clear the entire queue.
	 *
	 * @since 1.2.0
	 * @return bool Whether the queue was cleared.
	 */
	public function clear() {
		$this->logger->info( 'Request queue cleared.' );
		return delete_option( self::QUEUE_OPTION );
	}

	/**
	 * Get position of an item in the queue.
	 *
	 * @since 1.2.0
	 * @param string $id The item ID.
	 * @return int|false Position (1-based) or false if not found.
	 */
	public function get_position( $id ) {
		$queue   = $this->get_queue();
		$pending = array_filter( $queue, function ( $item ) {
			return self::STATUS_PENDING === $item['status'];
		} );

		$position = 1;
		foreach ( $pending as $item ) {
			if ( $item['id'] === $id ) {
				return $position;
			}
			$position++;
		}

		return false;
	}

	/**
	 * Retry failed items.
	 *
	 * @since 1.2.0
	 * @return int Number of items reset for retry.
	 */
	public function retry_failed() {
		$queue = $this->get_queue();
		$count = 0;

		foreach ( $queue as $index => $item ) {
			if ( self::STATUS_FAILED === $item['status'] ) {
				$queue[ $index ]['status']   = self::STATUS_PENDING;
				$queue[ $index ]['attempts'] = 0;
				$queue[ $index ]['error']    = null;
				$count++;
			}
		}

		if ( $count > 0 ) {
			$this->save_queue( $queue );
			$this->logger->info( 'Failed queue items reset for retry.', array( 'count' => $count ) );
		}

		return $count;
	}

	/**
	 * Schedule queue processing cron.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function schedule_processing() {
		if ( ! wp_next_scheduled( 'ta_process_queue' ) ) {
			wp_schedule_event( time(), 'ta_every_minute', 'ta_process_queue' );
		}
	}

	/**
	 * Unschedule queue processing cron.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function unschedule_processing() {
		wp_clear_scheduled_hook( 'ta_process_queue' );
	}

	/**
	 * Check if should queue or process immediately.
	 *
	 * @since 1.2.0
	 * @param TA_Rate_Limiter $rate_limiter Optional. Rate limiter instance.
	 * @return bool True if should queue, false if process immediately.
	 */
	public function should_queue( $rate_limiter = null ) {
		// If queueing is disabled, never queue.
		if ( ! $this->is_enabled() ) {
			return false;
		}

		// If rate limited, should queue.
		if ( null !== $rate_limiter && $rate_limiter->is_rate_limited() ) {
			return true;
		}

		// If queue already has items, add to queue for fairness.
		$stats = $this->get_stats();
		if ( $stats['pending'] > 0 ) {
			return true;
		}

		return false;
	}
}
