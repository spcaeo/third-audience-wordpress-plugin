<?php
/**
 * Data Exporter - Export bot analytics data to CSV and JSON.
 *
 * Handles export functionality for bot analytics data in various formats.
 *
 * @package ThirdAudience
 * @since   3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Data_Exporter
 *
 * Exports bot analytics data to CSV and JSON formats.
 *
 * @since 3.3.1
 */
class TA_Data_Exporter {

	/**
	 * Analytics query instance.
	 *
	 * @var TA_Analytics_Query
	 */
	private $query;

	/**
	 * Singleton instance.
	 *
	 * @var TA_Data_Exporter|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 3.3.1
	 * @return TA_Data_Exporter
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
		$this->query = TA_Analytics_Query::get_instance();
	}

	/**
	 * Export data to CSV.
	 *
	 * @since 2.0.6
	 * @param array  $filters Optional filters.
	 * @param string $format  Export format: 'detailed' or 'summary'.
	 * @return void Outputs CSV file and exits.
	 */
	public function export_to_csv( $filters = array(), $format = 'detailed' ) {
		$visits = $this->query->get_recent_visits( $filters, 10000 ); // Max 10k rows.

		$filename = 'bot-analytics-' . gmdate( 'Y-m-d-H-i-s' ) . '.csv';

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );

		// Export metadata header.
		fputcsv( $output, array( 'Third Audience Bot Analytics Export' ) );
		fputcsv( $output, array( 'Generated', gmdate( 'Y-m-d H:i:s' ) . ' UTC' ) );
		fputcsv( $output, array( 'Total Records', count( $visits ) ) );
		fputcsv( $output, array( 'Format', ucfirst( $format ) ) );
		fputcsv( $output, array() ); // Empty row.

		if ( 'summary' === $format ) {
			// Summary format: aggregated stats by bot type.
			$summary_data = $this->get_summary_export_data( $visits );

			// CSV headers for summary.
			fputcsv( $output, array(
				'Bot Type',
				'Bot Name',
				'Total Visits',
				'Unique Pages',
				'Cache Hit Rate (%)',
				'Avg Response Time (ms)',
				'Total Bandwidth',
			) );

			// CSV rows for summary.
			foreach ( $summary_data as $row ) {
				fputcsv( $output, $row );
			}
		} else {
			// Detailed format: all columns.
			// CSV headers.
			fputcsv( $output, array(
				'ID',
				'Bot Type',
				'Bot Name',
				'User Agent',
				'URL',
				'Post Title',
				'Post Type',
				'Request Method',
				'Cache Status',
				'Cache Explanation',
				'Response Time (ms)',
				'Response Size',
				'IP Address',
				'Referer',
				'Country Code',
				'Visit Time (UTC)',
			) );

			// Helper function for cache explanation.
			$get_cache_explanation = function( $status ) {
				$explanations = array(
					'HIT'            => 'Served from transient cache (fast)',
					'MISS'           => 'Generated fresh (first visit or cache expired)',
					'PRE_GENERATED'  => 'Served from pre-generated cache (fastest)',
					'FAILED'         => 'Content conversion failed',
					'BLOCKED'        => 'Bot was blocked from accessing content',
					'ERROR'          => 'An error occurred during processing',
				);
				return $explanations[ $status ] ?? 'Unknown status';
			};

			// CSV rows.
			foreach ( $visits as $visit ) {
				fputcsv( $output, array(
					$visit['id'],
					$visit['bot_type'],
					$visit['bot_name'],
					$visit['user_agent'] ?? '',
					$visit['url'],
					$visit['post_title'] ?? 'N/A',
					$visit['post_type'] ?? 'N/A',
					$visit['request_method'],
					$visit['cache_status'],
					$get_cache_explanation( $visit['cache_status'] ),
					$visit['response_time'],
					$visit['response_size'] ? size_format( $visit['response_size'], 2 ) : 'N/A',
					$visit['ip_address'] ?? 'N/A',
					$visit['referer'] ?? 'Direct',
					$visit['country_code'] ?? 'N/A',
					$visit['visit_timestamp'],
				) );
			}
		}

		fclose( $output );
		exit;
	}

	/**
	 * Export data to JSON.
	 *
	 * @since 2.0.6
	 * @param array  $filters Optional filters.
	 * @param string $format  Export format: 'detailed' or 'summary'.
	 * @return void Outputs JSON file and exits.
	 */
	public function export_to_json( $filters = array(), $format = 'detailed' ) {
		$visits = $this->query->get_recent_visits( $filters, 10000 ); // Max 10k rows.

		$filename = 'bot-analytics-' . gmdate( 'Y-m-d-H-i-s' ) . '.json';

		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$export_data = array(
			'metadata' => array(
				'plugin'        => 'Third Audience',
				'export_type'   => 'Bot Analytics',
				'generated_at'  => gmdate( 'Y-m-d H:i:s' ) . ' UTC',
				'total_records' => count( $visits ),
				'format'        => $format,
				'filters'       => $filters,
			),
		);

		if ( 'summary' === $format ) {
			// Summary format: aggregated stats by bot type.
			$export_data['data'] = $this->get_summary_export_data_json( $visits );
		} else {
			// Detailed format: all visit records.
			$export_data['data'] = array_map( function( $visit ) {
				return array(
					'id'              => (int) $visit['id'],
					'bot_type'        => $visit['bot_type'],
					'bot_name'        => $visit['bot_name'],
					'user_agent'      => $visit['user_agent'] ?? null,
					'url'             => $visit['url'],
					'post_id'         => isset( $visit['post_id'] ) ? (int) $visit['post_id'] : null,
					'post_title'      => $visit['post_title'] ?? null,
					'post_type'       => $visit['post_type'] ?? null,
					'request_method'  => $visit['request_method'],
					'cache_status'    => $visit['cache_status'],
					'response_time'   => isset( $visit['response_time'] ) ? (int) $visit['response_time'] : null,
					'response_size'   => isset( $visit['response_size'] ) ? (int) $visit['response_size'] : null,
					'ip_address'      => $visit['ip_address'] ?? null,
					'referer'         => $visit['referer'] ?? null,
					'country_code'    => $visit['country_code'] ?? null,
					'visit_timestamp' => $visit['visit_timestamp'],
				);
			}, $visits );
		}

		echo wp_json_encode( $export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		exit;
	}

	/**
	 * Get summary export data for CSV.
	 *
	 * @since 2.0.6
	 * @param array $visits Visit records.
	 * @return array Summary data grouped by bot type.
	 */
	private function get_summary_export_data( $visits ) {
		$summary = array();

		// Group by bot type.
		foreach ( $visits as $visit ) {
			$bot_type = $visit['bot_type'];

			if ( ! isset( $summary[ $bot_type ] ) ) {
				$summary[ $bot_type ] = array(
					'bot_type'       => $bot_type,
					'bot_name'       => $visit['bot_name'],
					'visits'         => 0,
					'unique_pages'   => array(),
					'cache_hits'     => 0,
					'total_response' => 0,
					'response_count' => 0,
					'total_size'     => 0,
				);
			}

			$summary[ $bot_type ]['visits']++;
			$summary[ $bot_type ]['unique_pages'][ $visit['url'] ] = true;

			if ( in_array( $visit['cache_status'], array( 'HIT', 'PRE_GENERATED' ), true ) ) {
				$summary[ $bot_type ]['cache_hits']++;
			}

			if ( isset( $visit['response_time'] ) && $visit['response_time'] > 0 ) {
				$summary[ $bot_type ]['total_response'] += (int) $visit['response_time'];
				$summary[ $bot_type ]['response_count']++;
			}

			if ( isset( $visit['response_size'] ) && $visit['response_size'] > 0 ) {
				$summary[ $bot_type ]['total_size'] += (int) $visit['response_size'];
			}
		}

		// Calculate aggregates.
		$result = array();
		foreach ( $summary as $bot_data ) {
			$cache_hit_rate = $bot_data['visits'] > 0
				? round( ( $bot_data['cache_hits'] / $bot_data['visits'] ) * 100, 1 )
				: 0;

			$avg_response = $bot_data['response_count'] > 0
				? round( $bot_data['total_response'] / $bot_data['response_count'] )
				: 0;

			$result[] = array(
				$bot_data['bot_type'],
				$bot_data['bot_name'],
				$bot_data['visits'],
				count( $bot_data['unique_pages'] ),
				$cache_hit_rate,
				$avg_response,
				size_format( $bot_data['total_size'], 2 ),
			);
		}

		return $result;
	}

	/**
	 * Get summary export data for JSON.
	 *
	 * @since 2.0.6
	 * @param array $visits Visit records.
	 * @return array Summary data grouped by bot type.
	 */
	private function get_summary_export_data_json( $visits ) {
		$summary = array();

		// Group by bot type.
		foreach ( $visits as $visit ) {
			$bot_type = $visit['bot_type'];

			if ( ! isset( $summary[ $bot_type ] ) ) {
				$summary[ $bot_type ] = array(
					'bot_type'       => $bot_type,
					'bot_name'       => $visit['bot_name'],
					'visits'         => 0,
					'unique_pages'   => array(),
					'cache_hits'     => 0,
					'total_response' => 0,
					'response_count' => 0,
					'total_size'     => 0,
				);
			}

			$summary[ $bot_type ]['visits']++;
			$summary[ $bot_type ]['unique_pages'][ $visit['url'] ] = true;

			if ( in_array( $visit['cache_status'], array( 'HIT', 'PRE_GENERATED' ), true ) ) {
				$summary[ $bot_type ]['cache_hits']++;
			}

			if ( isset( $visit['response_time'] ) && $visit['response_time'] > 0 ) {
				$summary[ $bot_type ]['total_response'] += (int) $visit['response_time'];
				$summary[ $bot_type ]['response_count']++;
			}

			if ( isset( $visit['response_size'] ) && $visit['response_size'] > 0 ) {
				$summary[ $bot_type ]['total_size'] += (int) $visit['response_size'];
			}
		}

		// Calculate aggregates.
		$result = array();
		foreach ( $summary as $bot_data ) {
			$cache_hit_rate = $bot_data['visits'] > 0
				? round( ( $bot_data['cache_hits'] / $bot_data['visits'] ) * 100, 1 )
				: 0;

			$avg_response = $bot_data['response_count'] > 0
				? round( $bot_data['total_response'] / $bot_data['response_count'] )
				: 0;

			$result[] = array(
				'bot_type'         => $bot_data['bot_type'],
				'bot_name'         => $bot_data['bot_name'],
				'total_visits'     => $bot_data['visits'],
				'unique_pages'     => count( $bot_data['unique_pages'] ),
				'cache_hit_rate'   => $cache_hit_rate,
				'avg_response_ms'  => $avg_response,
				'total_bandwidth'  => $bot_data['total_size'],
			);
		}

		return $result;
	}
}
