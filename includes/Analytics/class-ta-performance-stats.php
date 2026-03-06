<?php
/**
 * Performance Stats - Response time and cache performance analytics.
 *
 * Provides performance metrics including response times, cache stats, and content length analysis.
 *
 * @package ThirdAudience
 * @since   3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Performance_Stats
 *
 * Calculates performance-related statistics for bot analytics.
 *
 * @since 3.3.1
 */
class TA_Performance_Stats {

	/**
	 * Analytics table name.
	 *
	 * @var string
	 */
	const TABLE_NAME = 'ta_bot_analytics';

	/**
	 * Singleton instance.
	 *
	 * @var TA_Performance_Stats|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 3.3.1
	 * @return TA_Performance_Stats
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
		// No dependencies needed.
	}

	/**
	 * Get optimal content length based on citation data.
	 *
	 * Analyzes which content length ranges get the most citations.
	 *
	 * @since 2.7.0
	 * @return array Optimal content length data.
	 */
	public function get_optimal_content_length() {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		// Group citations by word count ranges.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$results = $wpdb->get_results(
			"SELECT
				CASE
					WHEN content_word_count < 300 THEN '0-299'
					WHEN content_word_count < 600 THEN '300-599'
					WHEN content_word_count < 900 THEN '600-899'
					WHEN content_word_count < 1200 THEN '900-1199'
					WHEN content_word_count < 1500 THEN '1200-1499'
					WHEN content_word_count < 2000 THEN '1500-1999'
					ELSE '2000+'
				END as word_range,
				COUNT(*) as citation_count
			FROM {$table_name}
			WHERE traffic_type = 'citation_click' AND content_word_count IS NOT NULL
			GROUP BY word_range
			ORDER BY citation_count DESC",
			ARRAY_A
		);

		// Find range with most citations.
		$optimal_range = ! empty( $results ) ? $results[0] : array(
			'word_range'     => 'N/A',
			'citation_count' => 0,
		);

		return array(
			'optimal_range'  => $optimal_range['word_range'],
			'citation_count' => absint( $optimal_range['citation_count'] ),
			'all_ranges'     => $results,
		);
	}

	/**
	 * Get cache performance statistics for drill-down.
	 *
	 * @since 3.2.3
	 * @return array Cache statistics with hits, misses, pre_generated, not_applicable.
	 */
	public function get_cache_performance_stats() {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$results = $wpdb->get_results(
			"SELECT
				cache_status,
				COUNT(*) as count
			FROM {$table_name}
			WHERE cache_status IS NOT NULL
			GROUP BY cache_status",
			ARRAY_A
		);

		$stats = array(
			'hits'           => 0,
			'misses'         => 0,
			'pre_generated'  => 0,
			'not_applicable' => 0,
		);

		foreach ( $results as $row ) {
			$status = strtoupper( $row['cache_status'] );
			$count  = absint( $row['count'] );

			if ( 'HIT' === $status ) {
				$stats['hits'] = $count;
			} elseif ( 'MISS' === $status ) {
				$stats['misses'] = $count;
			} elseif ( 'PRE_GENERATED' === $status ) {
				$stats['pre_generated'] = $count;
			} else {
				$stats['not_applicable'] += $count;
			}
		}

		return $stats;
	}

	/**
	 * Get response time distribution for drill-down.
	 *
	 * @since 3.2.3
	 * @return array Response time ranges with counts.
	 */
	public function get_response_time_distribution() {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$results = $wpdb->get_results(
			"SELECT
				CASE
					WHEN response_time <= 10 THEN '0-10ms'
					WHEN response_time <= 50 THEN '11-50ms'
					WHEN response_time <= 100 THEN '51-100ms'
					WHEN response_time <= 500 THEN '101-500ms'
					WHEN response_time <= 1000 THEN '501-1000ms'
					ELSE '1000ms+'
				END as range_label,
				COUNT(*) as count
			FROM {$table_name}
			WHERE response_time IS NOT NULL AND response_time > 0
			GROUP BY range_label
			ORDER BY MIN(response_time)",
			ARRAY_A
		);

		$distribution = array();
		foreach ( $results as $row ) {
			$distribution[ $row['range_label'] ] = absint( $row['count'] );
		}

		// Ensure all ranges are present.
		$all_ranges = array( '0-10ms', '11-50ms', '51-100ms', '101-500ms', '501-1000ms', '1000ms+' );
		foreach ( $all_ranges as $range ) {
			if ( ! isset( $distribution[ $range ] ) ) {
				$distribution[ $range ] = 0;
			}
		}

		// Return in correct order.
		$ordered = array();
		foreach ( $all_ranges as $range ) {
			$ordered[ $range ] = $distribution[ $range ];
		}

		return $ordered;
	}

	/**
	 * Get response time percentile.
	 *
	 * @since 3.2.3
	 * @param int $percentile Percentile to calculate (e.g., 50, 95).
	 * @return int Response time in ms at the given percentile.
	 */
	public function get_response_time_percentile( $percentile = 50 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		// Get total count.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} WHERE response_time IS NOT NULL AND response_time > 0" );

		if ( ! $total ) {
			return 0;
		}

		// Calculate offset for percentile.
		$offset = (int) floor( ( $percentile / 100 ) * $total );
		$offset = max( 0, min( $offset, $total - 1 ) );

		// Get the value at that position.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$value = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT response_time FROM {$table_name}
				WHERE response_time IS NOT NULL AND response_time > 0
				ORDER BY response_time
				LIMIT 1 OFFSET %d",
				$offset
			)
		);

		return absint( $value );
	}

	/**
	 * Calculate standard deviation of values.
	 *
	 * @since 2.6.0
	 * @param array $values Numeric values.
	 * @return float Standard deviation.
	 */
	public function calculate_stddev( $values ) {
		if ( empty( $values ) ) {
			return 0.0;
		}

		$mean     = array_sum( $values ) / count( $values );
		$variance = array_sum( array_map( function( $x ) use ( $mean ) {
			return pow( $x - $mean, 2 );
		}, $values ) ) / count( $values );

		return sqrt( $variance );
	}

	/**
	 * Format duration in human-readable format.
	 *
	 * @since 2.6.0
	 * @param int $seconds Duration in seconds.
	 * @return string Formatted duration.
	 */
	public function format_duration( $seconds ) {
		if ( $seconds < 60 ) {
			return $seconds . 's';
		} elseif ( $seconds < 3600 ) {
			return round( $seconds / 60, 1 ) . ' min';
		} else {
			return round( $seconds / 3600, 1 ) . ' hr';
		}
	}
}
