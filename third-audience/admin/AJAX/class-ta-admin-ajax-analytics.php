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
		add_action( 'wp_ajax_ta_export_analytics_data', array( $this, 'ajax_export_analytics_data' ) );
	}

	/**
	 * AJAX handler for exporting analytics data as CSV.
	 *
	 * @since 3.3.3
	 * @return void
	 */
	public function ajax_export_analytics_data() {
		$this->security->verify_ajax_request( 'bot_analytics' );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$export_type = isset( $_POST['export_type'] ) ? sanitize_text_field( wp_unslash( $_POST['export_type'] ) ) : '';

		if ( empty( $export_type ) ) {
			wp_send_json_error( array( 'message' => __( 'Export type is required.', 'third-audience' ) ) );
		}

		$analytics = TA_Bot_Analytics::get_instance();
		$csv_data  = '';

		switch ( $export_type ) {
			case 'bot-distribution':
				$csv_data = $this->export_bot_distribution( $analytics );
				break;

			case 'top-content':
				$csv_data = $this->export_top_content( $analytics );
				break;

			case 'session-activity':
				$csv_data = $this->export_session_activity( $analytics );
				break;

			case 'crawl-budget':
				$csv_data = $this->export_crawl_budget( $analytics );
				break;

			case 'citation-performance':
				$csv_data = $this->export_citation_performance( $analytics );
				break;

			case 'content-insights':
				$csv_data = $this->export_content_insights( $analytics );
				break;

			case 'activity-timeline':
				$csv_data = $this->export_activity_timeline( $analytics );
				break;

			case 'live-activity':
				$csv_data = $this->export_live_activity( $analytics );
				break;

			default:
				wp_send_json_error( array( 'message' => __( 'Unknown export type.', 'third-audience' ) ) );
		}

		wp_send_json_success( array( 'csv' => $csv_data ) );
	}

	/**
	 * Export bot distribution data.
	 *
	 * @since 3.3.3
	 * @param TA_Bot_Analytics $analytics Analytics instance.
	 * @return string CSV data.
	 */
	private function export_bot_distribution( $analytics ) {
		$bot_stats = $analytics->get_visits_by_bot( array(), 50 );
		$csv       = "Bot Name,Bot Type,Visits,Share %\n";
		$total     = array_sum( wp_list_pluck( $bot_stats, 'count' ) );

		foreach ( $bot_stats as $bot ) {
			$share = $total > 0 ? round( ( $bot['count'] / $total ) * 100, 1 ) : 0;
			$csv  .= sprintf(
				"\"%s\",\"%s\",%d,%.1f\n",
				str_replace( '"', '""', $bot['bot_name'] ),
				str_replace( '"', '""', $bot['bot_type'] ),
				$bot['count'],
				$share
			);
		}

		return $csv;
	}

	/**
	 * Export top content data.
	 *
	 * @since 3.3.3
	 * @param TA_Bot_Analytics $analytics Analytics instance.
	 * @return string CSV data.
	 */
	private function export_top_content( $analytics ) {
		$top_pages = $analytics->get_top_pages( array(), 100 );
		$csv       = "Page URL,Post Title,Visits\n";

		foreach ( $top_pages as $page ) {
			$csv .= sprintf(
				"\"%s\",\"%s\",%d\n",
				str_replace( '"', '""', $page['url'] ),
				str_replace( '"', '""', $page['post_title'] ?? '' ),
				$page['visit_count']
			);
		}

		return $csv;
	}

	/**
	 * Export session activity data.
	 *
	 * @since 3.3.3
	 * @param TA_Bot_Analytics $analytics Analytics instance.
	 * @return string CSV data.
	 */
	private function export_session_activity( $analytics ) {
		$top_bots = $analytics->get_top_bots_by_metric( 'session_depth', 20 );
		$csv      = "Bot Type,User Agent,Pages/Session,Session Duration (min),Total Visits\n";

		foreach ( $top_bots as $bot ) {
			$duration_mins = round( $bot['session_duration_avg'] / 60, 1 );
			$csv          .= sprintf(
				"\"%s\",\"%s\",%.1f,%.1f,%d\n",
				str_replace( '"', '""', $bot['bot_type'] ),
				str_replace( '"', '""', $bot['user_agent'] ),
				$bot['pages_per_session_avg'],
				$duration_mins,
				$bot['visit_count']
			);
		}

		return $csv;
	}

	/**
	 * Export crawl budget data.
	 *
	 * @since 3.3.3
	 * @param TA_Bot_Analytics $analytics Analytics instance.
	 * @return string CSV data.
	 */
	private function export_crawl_budget( $analytics ) {
		$day_metrics  = $analytics->get_crawl_budget_metrics( null, 'day' );
		$hour_metrics = $analytics->get_crawl_budget_metrics( null, 'hour' );

		$csv  = "Period,Total Requests,Unique Pages,Bandwidth (MB),Cache Hit Rate %,Avg Response Time (ms)\n";
		$csv .= sprintf(
			"\"Last 24 Hours\",%d,%d,%.2f,%.1f,%d\n",
			$day_metrics['total_requests'],
			$day_metrics['unique_pages'],
			$day_metrics['total_bandwidth_mb'],
			$day_metrics['cache_hit_rate'],
			$day_metrics['avg_response_time']
		);
		$csv .= sprintf(
			"\"Last Hour\",%d,%d,%.2f,%.1f,%d\n",
			$hour_metrics['total_requests'],
			$hour_metrics['unique_pages'],
			$hour_metrics['total_bandwidth_mb'],
			$hour_metrics['cache_hit_rate'],
			$hour_metrics['avg_response_time']
		);

		return $csv;
	}

	/**
	 * Export citation performance data.
	 *
	 * @since 3.3.3
	 * @param TA_Bot_Analytics $analytics Analytics instance.
	 * @return string CSV data.
	 */
	private function export_citation_performance( $analytics ) {
		$citation_data = $analytics->get_citation_to_crawl_ratio( array(), 100 );
		$csv           = "Page URL,Post Title,Crawls,Citations,Citation Rate %\n";

		foreach ( $citation_data as $page ) {
			$rate = round( $page['citation_rate'] * 100, 1 );
			$csv .= sprintf(
				"\"%s\",\"%s\",%d,%d,%.1f\n",
				str_replace( '"', '""', $page['url'] ),
				str_replace( '"', '""', $page['post_title'] ?? '' ),
				$page['crawls'],
				$page['citations'],
				$rate
			);
		}

		return $csv;
	}

	/**
	 * Export content insights data.
	 *
	 * @since 3.3.3
	 * @param TA_Bot_Analytics $analytics Analytics instance.
	 * @return string CSV data.
	 */
	private function export_content_insights( $analytics ) {
		$content_perf = $analytics->get_content_performance_analysis( array() );

		$csv  = "Metric,Cited Posts,Crawled Posts\n";
		$csv .= sprintf(
			"\"Total Count\",%d,%d\n",
			$content_perf['cited_posts']['total_count'],
			$content_perf['crawled_posts']['total_count']
		);
		$csv .= sprintf(
			"\"Avg Word Count\",%d,%d\n",
			$content_perf['cited_posts']['avg_word_count'],
			$content_perf['crawled_posts']['avg_word_count']
		);
		$csv .= sprintf(
			"\"Avg Headings\",%.1f,%.1f\n",
			$content_perf['cited_posts']['avg_heading_count'],
			$content_perf['crawled_posts']['avg_heading_count']
		);
		$csv .= sprintf(
			"\"Avg Images\",%.1f,%.1f\n",
			$content_perf['cited_posts']['avg_image_count'],
			$content_perf['crawled_posts']['avg_image_count']
		);
		$csv .= sprintf(
			"\"Schema Markup %%\",%.1f,%.1f\n",
			$content_perf['cited_posts']['schema_percentage'],
			$content_perf['crawled_posts']['schema_percentage']
		);
		$csv .= sprintf(
			"\"Avg Freshness (days)\",%d,%d\n",
			$content_perf['cited_posts']['avg_freshness_days'],
			$content_perf['crawled_posts']['avg_freshness_days']
		);

		return $csv;
	}

	/**
	 * Export activity timeline data.
	 *
	 * @since 3.3.3
	 * @param TA_Bot_Analytics $analytics Analytics instance.
	 * @return string CSV data.
	 */
	private function export_activity_timeline( $analytics ) {
		$timeline = $analytics->get_visits_over_time( array(), 'day', 30 );
		$csv      = "Period,Total Visits,Unique Bots\n";

		foreach ( $timeline as $item ) {
			$csv .= sprintf(
				"\"%s\",%d,%d\n",
				$item['period'],
				$item['visits'],
				$item['unique_bots']
			);
		}

		return $csv;
	}

	/**
	 * Export live activity data.
	 *
	 * @since 3.3.3
	 * @param TA_Bot_Analytics $analytics Analytics instance.
	 * @return string CSV data.
	 */
	private function export_live_activity( $analytics ) {
		$recent = $analytics->get_recent_visits( array(), 100, 0 );
		$csv    = "Timestamp,Bot Name,Bot Type,Page URL,IP Address,Country,Cache Status,Response Time (ms)\n";

		foreach ( $recent as $visit ) {
			$csv .= sprintf(
				"\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",%d\n",
				$visit['visit_time'],
				str_replace( '"', '""', $visit['bot_name'] ),
				str_replace( '"', '""', $visit['bot_type'] ),
				str_replace( '"', '""', $visit['url'] ),
				$visit['ip_address'] ?? '',
				$visit['country'] ?? '',
				$visit['cache_status'] ?? '',
				$visit['response_time'] ?? 0
			);
		}

		return $csv;
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
