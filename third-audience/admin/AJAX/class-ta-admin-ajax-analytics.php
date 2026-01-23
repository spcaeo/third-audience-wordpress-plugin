<?php
/**
 * Admin AJAX Analytics Handlers - Analytics drill-down AJAX operations.
 *
 * Handles session analytics, hero metrics, and bot details drill-down modals.
 *
 * @package ThirdAudience
 * @since   3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Admin_AJAX_Analytics
 *
 * Handles analytics drill-down AJAX operations for the admin interface.
 *
 * @since 3.3.1
 */
class TA_Admin_AJAX_Analytics {

	/**
	 * Security instance.
	 *
	 * @var TA_Security
	 */
	private $security;

	/**
	 * Singleton instance.
	 *
	 * @var TA_Admin_AJAX_Analytics|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 3.3.1
	 * @return TA_Admin_AJAX_Analytics
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
	}

	/**
	 * Register AJAX hooks.
	 *
	 * @since 3.3.1
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_ta_get_session_details', array( $this, 'ajax_get_session_details' ) );
		add_action( 'wp_ajax_ta_get_hero_metric_details', array( $this, 'ajax_get_hero_metric_details' ) );
		add_action( 'wp_ajax_ta_get_bot_details', array( $this, 'ajax_get_bot_details' ) );
	}

	/**
	 * AJAX handler for session analytics drill-down.
	 *
	 * Returns detailed bot fingerprint data for the session analytics modal.
	 *
	 * @since 3.2.2
	 * @return void
	 */
	public function ajax_get_session_details() {
		check_ajax_referer( 'ta_bot_analytics', 'nonce' );
		$this->security->verify_admin_capability();

		$sort_by = isset( $_POST['sort_by'] ) ? sanitize_text_field( wp_unslash( $_POST['sort_by'] ) ) : 'last_seen';
		$order   = isset( $_POST['order'] ) ? sanitize_text_field( wp_unslash( $_POST['order'] ) ) : 'DESC';

		$analytics    = TA_Bot_Analytics::get_instance();
		$fingerprints = $analytics->get_bot_fingerprints_list( $sort_by, $order, 50 );

		// Get summary stats too.
		$session_stats = $analytics->get_session_analytics();

		wp_send_json_success(
			array(
				'fingerprints' => $fingerprints,
				'summary'      => $session_stats,
			)
		);
	}

	/**
	 * AJAX handler for hero metrics drill-down.
	 *
	 * @since 3.2.3
	 * @return void
	 */
	public function ajax_get_hero_metric_details() {
		check_ajax_referer( 'ta_bot_analytics', 'nonce' );
		$this->security->verify_admin_capability();

		$metric = isset( $_POST['metric'] ) ? sanitize_text_field( wp_unslash( $_POST['metric'] ) ) : '';

		$analytics = TA_Bot_Analytics::get_instance();
		$summary   = $analytics->get_summary( array() );
		$bot_stats = $analytics->get_visits_by_bot( array() );
		$top_pages = $analytics->get_top_pages( array(), 20 );

		$response = array();

		switch ( $metric ) {
			case 'total_visits':
				$response = $this->get_total_visits_details( $summary, $bot_stats );
				break;
			case 'pages_crawled':
				$response = $this->get_pages_crawled_details( $summary, $top_pages );
				break;
			case 'cache_hit_rate':
				$response = $this->get_cache_details( $summary, $analytics );
				break;
			case 'avg_response':
				$response = $this->get_response_time_details( $summary, $analytics );
				break;
			case 'verified_bots':
				$response = $this->get_verification_details( $summary, $bot_stats );
				break;
			default:
				wp_send_json_error( array( 'message' => 'Invalid metric' ) );
		}

		wp_send_json_success( $response );
	}

	/**
	 * AJAX handler for bot diagnostic drill-down modal.
	 *
	 * Returns comprehensive bot details for the bot management diagnostic modal.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public function ajax_get_bot_details() {
		check_ajax_referer( 'ta_bot_management', 'nonce' );
		$this->security->verify_admin_capability();

		$bot_type = isset( $_POST['bot_type'] ) ? sanitize_text_field( wp_unslash( $_POST['bot_type'] ) ) : '';
		$bot_name = isset( $_POST['bot_name'] ) ? sanitize_text_field( wp_unslash( $_POST['bot_name'] ) ) : '';

		if ( empty( $bot_type ) ) {
			wp_send_json_error( array( 'message' => __( 'Bot type is required.', 'third-audience' ) ) );
		}

		$analytics = TA_Bot_Analytics::get_instance();
		$details   = $analytics->get_bot_details( $bot_type, $bot_name );

		wp_send_json_success( $details );
	}

	/**
	 * Get total visits breakdown data.
	 *
	 * @param array $summary Bot analytics summary.
	 * @param array $bot_stats Bot visit statistics.
	 * @return array Response data.
	 */
	private function get_total_visits_details( $summary, $bot_stats ) {
		$total  = $summary['total_visits'];
		$labels = array();
		$values = array();
		$pcts   = array();
		$rows   = array();

		foreach ( $bot_stats as $bot ) {
			$labels[] = $bot['bot_type'];
			$values[] = (int) $bot['visit_count'];
			$pct      = $total > 0 ? round( ( $bot['visit_count'] / $total ) * 100, 1 ) . '%' : '0%';
			$pcts[]   = $pct;
			$rows[]   = array(
				'<span class="ta-bot-name">' . esc_html( $bot['bot_type'] ) . '</span>',
				'<strong>' . number_format( $bot['visit_count'] ) . '</strong>',
				$pct,
				esc_html( $bot['last_visit_human'] ?? '-' ),
			);
		}

		return array(
			'stats'         => array(
				array( 'label' => __( 'Total Visits', 'third-audience' ), 'value' => number_format( $total ) ),
				array( 'label' => __( 'Today', 'third-audience' ), 'value' => number_format( $summary['visits_today'] ) ),
				array( 'label' => __( 'Unique Bots', 'third-audience' ), 'value' => number_format( $summary['unique_bots'] ) ),
			),
			'chart_title'   => __( 'Visits by Bot Type', 'third-audience' ),
			'chart_type'    => 'doughnut',
			'chart_data'    => array(
				'labels'      => $labels,
				'values'      => $values,
				'percentages' => $pcts,
			),
			'table_title'   => __( 'Bot Visit Details', 'third-audience' ),
			'table_headers' => array(
				array( 'label' => __( 'Bot', 'third-audience' ), 'align' => 'left' ),
				array( 'label' => __( 'Visits', 'third-audience' ), 'align' => 'right' ),
				array( 'label' => __( 'Share', 'third-audience' ), 'align' => 'right' ),
				array( 'label' => __( 'Last Visit', 'third-audience' ), 'align' => 'left' ),
			),
			'table_rows'    => $rows,
		);
	}

	/**
	 * Get pages crawled breakdown data.
	 *
	 * @param array $summary Bot analytics summary.
	 * @param array $top_pages Top crawled pages.
	 * @return array Response data.
	 */
	private function get_pages_crawled_details( $summary, $top_pages ) {
		$labels = array();
		$values = array();
		$rows   = array();

		foreach ( array_slice( $top_pages, 0, 10 ) as $page ) {
			$title    = $page['page_title'] ?: $page['page_url'];
			$labels[] = strlen( $title ) > 25 ? substr( $title, 0, 25 ) . '...' : $title;
			$values[] = (int) $page['visit_count'];
		}

		foreach ( $top_pages as $page ) {
			$title  = $page['page_title'] ?: $page['page_url'];
			$rows[] = array(
				'<a href="' . esc_url( $page['page_url'] ) . '" target="_blank">' . esc_html( $title ) . '</a>',
				'<strong>' . number_format( $page['visit_count'] ) . '</strong>',
				number_format( $page['unique_bots'] ),
			);
		}

		return array(
			'stats'         => array(
				array( 'label' => __( 'Total Pages', 'third-audience' ), 'value' => number_format( $summary['unique_pages'] ) ),
				array( 'label' => __( 'Total Visits', 'third-audience' ), 'value' => number_format( $summary['total_visits'] ) ),
				array( 'label' => __( 'Avg Visits/Page', 'third-audience' ), 'value' => $summary['unique_pages'] > 0 ? number_format( $summary['total_visits'] / $summary['unique_pages'], 1 ) : '0' ),
			),
			'chart_title'   => __( 'Top 10 Pages by Visits', 'third-audience' ),
			'chart_type'    => 'bar',
			'chart_data'    => array(
				'labels' => $labels,
				'values' => $values,
			),
			'table_title'   => __( 'All Crawled Pages', 'third-audience' ),
			'table_headers' => array(
				array( 'label' => __( 'Page', 'third-audience' ), 'align' => 'left' ),
				array( 'label' => __( 'Visits', 'third-audience' ), 'align' => 'right' ),
				array( 'label' => __( 'Unique Bots', 'third-audience' ), 'align' => 'right' ),
			),
			'table_rows'    => $rows,
		);
	}

	/**
	 * Get cache performance breakdown data.
	 *
	 * @param array            $summary Bot analytics summary.
	 * @param TA_Bot_Analytics $analytics Analytics instance.
	 * @return array Response data.
	 */
	private function get_cache_details( $summary, $analytics ) {
		$cache_stats = $analytics->get_cache_performance_stats();

		$hits   = $cache_stats['hits'] ?? 0;
		$misses = $cache_stats['misses'] ?? 0;
		$pregen = $cache_stats['pre_generated'] ?? 0;
		$na     = $cache_stats['not_applicable'] ?? 0;
		$total  = $hits + $misses + $pregen + $na;

		$labels = array( 'Cache Hit', 'Cache Miss', 'Pre-generated', 'N/A' );
		$values = array( $hits, $misses, $pregen, $na );
		$pcts   = array();
		foreach ( $values as $v ) {
			$pcts[] = $total > 0 ? round( ( $v / $total ) * 100, 1 ) . '%' : '0%';
		}

		$rows = array();
		for ( $i = 0; $i < count( $labels ); $i++ ) {
			$rows[] = array(
				esc_html( $labels[ $i ] ),
				'<strong>' . number_format( $values[ $i ] ) . '</strong>',
				$pcts[ $i ],
			);
		}

		return array(
			'stats'         => array(
				array( 'label' => __( 'Hit Rate', 'third-audience' ), 'value' => $summary['cache_hit_rate'] . '%' ),
				array( 'label' => __( 'Cache Hits', 'third-audience' ), 'value' => number_format( $hits ) ),
				array( 'label' => __( 'Cache Misses', 'third-audience' ), 'value' => number_format( $misses ) ),
			),
			'chart_title'   => __( 'Cache Status Distribution', 'third-audience' ),
			'chart_type'    => 'doughnut',
			'chart_data'    => array(
				'labels'      => $labels,
				'values'      => $values,
				'percentages' => $pcts,
			),
			'table_title'   => __( 'Cache Breakdown', 'third-audience' ),
			'table_headers' => array(
				array( 'label' => __( 'Status', 'third-audience' ), 'align' => 'left' ),
				array( 'label' => __( 'Count', 'third-audience' ), 'align' => 'right' ),
				array( 'label' => __( 'Percentage', 'third-audience' ), 'align' => 'right' ),
			),
			'table_rows'    => $rows,
		);
	}

	/**
	 * Get response time breakdown data.
	 *
	 * @param array            $summary Bot analytics summary.
	 * @param TA_Bot_Analytics $analytics Analytics instance.
	 * @return array Response data.
	 */
	private function get_response_time_details( $summary, $analytics ) {
		$time_stats = $analytics->get_response_time_distribution();

		$labels = array();
		$values = array();
		$rows   = array();

		foreach ( $time_stats as $range => $count ) {
			$labels[] = $range;
			$values[] = (int) $count;
			$rows[]   = array(
				esc_html( $range ),
				'<strong>' . number_format( $count ) . '</strong>',
			);
		}

		// Calculate percentiles if available.
		$p50 = $analytics->get_response_time_percentile( 50 );
		$p95 = $analytics->get_response_time_percentile( 95 );

		return array(
			'stats'         => array(
				array( 'label' => __( 'Average', 'third-audience' ), 'value' => $summary['avg_response_time'] . 'ms' ),
				array( 'label' => __( 'Median (P50)', 'third-audience' ), 'value' => $p50 . 'ms' ),
				array( 'label' => __( '95th Percentile', 'third-audience' ), 'value' => $p95 . 'ms' ),
			),
			'chart_title'   => __( 'Response Time Distribution', 'third-audience' ),
			'chart_type'    => 'bar',
			'chart_data'    => array(
				'labels' => $labels,
				'values' => $values,
			),
			'table_title'   => __( 'Response Time Ranges', 'third-audience' ),
			'table_headers' => array(
				array( 'label' => __( 'Range', 'third-audience' ), 'align' => 'left' ),
				array( 'label' => __( 'Requests', 'third-audience' ), 'align' => 'right' ),
			),
			'table_rows'    => $rows,
		);
	}

	/**
	 * Get bot verification breakdown data.
	 *
	 * @param array $summary Bot analytics summary.
	 * @param array $bot_stats Bot visit statistics.
	 * @return array Response data.
	 */
	private function get_verification_details( $summary, $bot_stats ) {
		$verified   = $summary['ip_verified_count'];
		$total      = $summary['total_visits'];
		$unverified = $total - $verified;

		$labels = array( __( 'Verified', 'third-audience' ), __( 'Unverified', 'third-audience' ) );
		$values = array( $verified, $unverified );
		$pcts   = array(
			$total > 0 ? round( ( $verified / $total ) * 100, 1 ) . '%' : '0%',
			$total > 0 ? round( ( $unverified / $total ) * 100, 1 ) . '%' : '0%',
		);

		$rows = array();
		foreach ( $bot_stats as $bot ) {
			$rows[] = array(
				'<span class="ta-bot-name">' . esc_html( $bot['bot_type'] ) . '</span>',
				'<strong>' . number_format( $bot['visit_count'] ) . '</strong>',
				isset( $bot['verified_count'] ) ? number_format( $bot['verified_count'] ) : '0',
				isset( $bot['verified_count'] ) && $bot['visit_count'] > 0
					? round( ( $bot['verified_count'] / $bot['visit_count'] ) * 100, 1 ) . '%'
					: '0%',
			);
		}

		return array(
			'stats'         => array(
				array( 'label' => __( 'Verification Rate', 'third-audience' ), 'value' => $summary['ip_verified_percentage'] . '%' ),
				array( 'label' => __( 'Verified Visits', 'third-audience' ), 'value' => number_format( $verified ) ),
				array( 'label' => __( 'Unverified Visits', 'third-audience' ), 'value' => number_format( $unverified ) ),
			),
			'chart_title'   => __( 'Verification Status', 'third-audience' ),
			'chart_type'    => 'doughnut',
			'chart_data'    => array(
				'labels'      => $labels,
				'values'      => $values,
				'percentages' => $pcts,
			),
			'table_title'   => __( 'Verification by Bot', 'third-audience' ),
			'table_headers' => array(
				array( 'label' => __( 'Bot', 'third-audience' ), 'align' => 'left' ),
				array( 'label' => __( 'Total Visits', 'third-audience' ), 'align' => 'right' ),
				array( 'label' => __( 'Verified', 'third-audience' ), 'align' => 'right' ),
				array( 'label' => __( 'Rate', 'third-audience' ), 'align' => 'right' ),
			),
			'table_rows'    => $rows,
		);
	}
}
