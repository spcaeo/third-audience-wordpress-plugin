<?php
/**
 * Crawl Budget Analyzer
 *
 * Analyzes bot crawl patterns and provides actionable recommendations
 * to optimize crawl budget utilization following Google's best practices.
 *
 * @package Third_Audience
 * @since 2.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Crawl_Budget_Analyzer
 *
 * Detects crawl budget inefficiencies and generates recommendations.
 */
class TA_Crawl_Budget_Analyzer {

	/**
	 * Analyze crawl budget and generate recommendations.
	 *
	 * @param string|null $bot_type     Optional bot type to filter by.
	 * @param int         $period_days  Number of days to analyze (default: 7).
	 * @return array Array of recommendations.
	 */
	public function analyze_crawl_budget( $bot_type = null, $period_days = 7 ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ta_bot_analytics';

		$recommendations = array();

		// 1. Detect excessive recrawling
		$recrawl_analysis = $this->check_recrawl_frequency( $bot_type, $period_days );
		if ( $recrawl_analysis['has_issue'] ) {
			$recommendations[] = array(
				'type'     => 'recrawling',
				'severity' => 'high',
				'title'    => 'Excessive Recrawling Detected',
				'message'  => sprintf(
					'%s is recrawling %s pages %dx per day',
					$recrawl_analysis['bot_type'],
					$recrawl_analysis['url'],
					$recrawl_analysis['daily_crawls']
				),
				'action'   => 'Add longer cache headers or update robots.txt crawl-delay',
				'impact'   => 'Wasting ' . round( $recrawl_analysis['wasted_percentage'] ) . '% of crawl budget',
			);
		}

		// 2. Detect crawls of non-public content
		$admin_crawls = $this->check_admin_page_crawls( $bot_type, $period_days );
		if ( $admin_crawls['count'] > 0 ) {
			$recommendations[] = array(
				'type'     => 'admin_crawls',
				'severity' => 'high',
				'title'    => 'Bots Crawling Admin Pages',
				'message'  => sprintf( '%d crawls detected on admin/login pages', $admin_crawls['count'] ),
				'action'   => 'Block /wp-admin/ in robots.txt',
				'impact'   => round( $admin_crawls['percentage'] ) . '% of crawl budget wasted',
			);
		}

		// 3. Detect 404 errors
		$not_found_crawls = $this->check_404_crawls( $bot_type, $period_days );
		if ( $not_found_crawls['count'] > 0 ) {
			$recommendations[] = array(
				'type'     => 'not_found',
				'severity' => 'medium',
				'title'    => '404 Errors Consuming Crawl Budget',
				'message'  => sprintf( '%d requests returned 404', $not_found_crawls['count'] ),
				'action'   => 'Set up 301 redirects or remove broken links',
				'impact'   => round( $not_found_crawls['percentage'] ) . '% of crawl budget wasted',
			);
		}

		// 4. Detect low cache hit rate
		$cache_stats = $this->check_cache_performance( $bot_type, $period_days );
		if ( $cache_stats['hit_rate'] < 70 ) {
			$recommendations[] = array(
				'type'     => 'cache_performance',
				'severity' => 'medium',
				'title'    => 'Low Cache Hit Rate',
				'message'  => sprintf( 'Only %d%% of bot requests served from cache', $cache_stats['hit_rate'] ),
				'action'   => 'Enable cache warming for popular content',
				'impact'   => 'Slower response times reduce crawl efficiency',
			);
		}

		// 5. Detect crawl rate drop
		$crawl_trend = $this->check_crawl_rate_trend( $bot_type, $period_days );
		if ( $crawl_trend['drop_percentage'] > 30 ) {
			$recommendations[] = array(
				'type'     => 'crawl_drop',
				'severity' => 'warning',
				'title'    => 'Crawl Rate Dropped Significantly',
				'message'  => sprintf(
					'Crawls dropped %d%% in the last %d days',
					$crawl_trend['drop_percentage'],
					$period_days
				),
				'action'   => 'Check server performance, response times, and robots.txt',
				'impact'   => 'Reduced visibility in AI platforms',
			);
		}

		// 6. Analyze robots.txt configuration
		$robots_issues = $this->analyze_robots_txt();
		if ( ! empty( $robots_issues ) ) {
			foreach ( $robots_issues as $issue ) {
				$recommendations[] = array(
					'type'     => 'robots_txt',
					'severity' => 'medium',
					'title'    => 'Robots.txt Configuration Issue',
					'message'  => $issue,
					'action'   => 'Update robots.txt configuration',
					'impact'   => 'Suboptimal crawl budget allocation',
				);
			}
		}

		return $recommendations;
	}

	/**
	 * Check for excessive recrawling of the same URLs.
	 *
	 * @param string|null $bot_type     Bot type filter.
	 * @param int         $period_days  Analysis period.
	 * @return array Analysis results.
	 */
	private function check_recrawl_frequency( $bot_type, $period_days ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ta_bot_analytics';

		$threshold = $period_days * 10; // More than 10 crawls per day is excessive

		$where_clause = $wpdb->prepare(
			"WHERE visit_timestamp >= DATE_SUB(NOW(), INTERVAL %d DAY)
			AND traffic_type = 'bot_crawl'",
			$period_days
		);

		if ( $bot_type ) {
			$where_clause .= $wpdb->prepare( ' AND bot_type = %s', $bot_type );
		}

		$query = "SELECT url, bot_type, COUNT(*) as crawl_count
				FROM {$table}
				{$where_clause}
				GROUP BY url, bot_type
				HAVING crawl_count > %d
				ORDER BY crawl_count DESC
				LIMIT 1";

		$result = $wpdb->get_row( $wpdb->prepare( $query, $threshold ), ARRAY_A );

		if ( $result ) {
			$daily_crawls = $result['crawl_count'] / $period_days;
			$total_crawls = $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE visit_timestamp >= DATE_SUB(NOW(), INTERVAL %d DAY)",
				$period_days
			) );

			return array(
				'has_issue'          => true,
				'url'                => $result['url'],
				'bot_type'           => $result['bot_type'],
				'daily_crawls'       => round( $daily_crawls, 1 ),
				'wasted_percentage'  => ( $result['crawl_count'] / max( $total_crawls, 1 ) ) * 100,
			);
		}

		return array( 'has_issue' => false );
	}

	/**
	 * Check for bot crawls on admin/login pages.
	 *
	 * @param string|null $bot_type     Bot type filter.
	 * @param int         $period_days  Analysis period.
	 * @return array Analysis results.
	 */
	private function check_admin_page_crawls( $bot_type, $period_days ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ta_bot_analytics';

		$where_clause = $wpdb->prepare(
			"WHERE visit_timestamp >= DATE_SUB(NOW(), INTERVAL %d DAY)
			AND traffic_type = 'bot_crawl'
			AND (url LIKE '%%/wp-admin/%%' OR url LIKE '%%/wp-login.php%%')",
			$period_days
		);

		if ( $bot_type ) {
			$where_clause .= $wpdb->prepare( ' AND bot_type = %s', $bot_type );
		}

		$admin_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table} {$where_clause}" );

		$total_crawls = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table}
			WHERE visit_timestamp >= DATE_SUB(NOW(), INTERVAL %d DAY)
			AND traffic_type = 'bot_crawl'",
			$period_days
		) );

		return array(
			'count'      => (int) $admin_count,
			'percentage' => $total_crawls > 0 ? ( $admin_count / $total_crawls ) * 100 : 0,
		);
	}

	/**
	 * Check for 404 errors in bot crawls.
	 *
	 * @param string|null $bot_type     Bot type filter.
	 * @param int         $period_days  Analysis period.
	 * @return array Analysis results.
	 */
	private function check_404_crawls( $bot_type, $period_days ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ta_bot_analytics';

		$where_clause = $wpdb->prepare(
			"WHERE visit_timestamp >= DATE_SUB(NOW(), INTERVAL %d DAY)
			AND traffic_type = 'bot_crawl'
			AND status_code = 404",
			$period_days
		);

		if ( $bot_type ) {
			$where_clause .= $wpdb->prepare( ' AND bot_type = %s', $bot_type );
		}

		$not_found_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table} {$where_clause}" );

		$total_crawls = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table}
			WHERE visit_timestamp >= DATE_SUB(NOW(), INTERVAL %d DAY)
			AND traffic_type = 'bot_crawl'",
			$period_days
		) );

		return array(
			'count'      => (int) $not_found_count,
			'percentage' => $total_crawls > 0 ? ( $not_found_count / $total_crawls ) * 100 : 0,
		);
	}

	/**
	 * Check cache performance for bot requests.
	 *
	 * @param string|null $bot_type     Bot type filter.
	 * @param int         $period_days  Analysis period.
	 * @return array Cache statistics.
	 */
	private function check_cache_performance( $bot_type, $period_days ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ta_bot_analytics';

		$where_clause = $wpdb->prepare(
			"WHERE visit_timestamp >= DATE_SUB(NOW(), INTERVAL %d DAY)
			AND traffic_type = 'bot_crawl'",
			$period_days
		);

		if ( $bot_type ) {
			$where_clause .= $wpdb->prepare( ' AND bot_type = %s', $bot_type );
		}

		$cache_hits = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table} {$where_clause} AND cache_status = 'HIT'"
		);

		$total_requests = $wpdb->get_var( "SELECT COUNT(*) FROM {$table} {$where_clause}" );

		$hit_rate = $total_requests > 0 ? ( $cache_hits / $total_requests ) * 100 : 0;

		return array(
			'hit_rate'       => round( $hit_rate ),
			'cache_hits'     => (int) $cache_hits,
			'total_requests' => (int) $total_requests,
		);
	}

	/**
	 * Check for crawl rate trends (drops or increases).
	 *
	 * @param string|null $bot_type     Bot type filter.
	 * @param int         $period_days  Analysis period.
	 * @return array Trend analysis.
	 */
	private function check_crawl_rate_trend( $bot_type, $period_days ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ta_bot_analytics';

		$half_period = floor( $period_days / 2 );

		$where_clause_recent = $wpdb->prepare(
			"WHERE visit_timestamp >= DATE_SUB(NOW(), INTERVAL %d DAY)
			AND traffic_type = 'bot_crawl'",
			$half_period
		);

		$where_clause_older = $wpdb->prepare(
			"WHERE visit_timestamp >= DATE_SUB(NOW(), INTERVAL %d DAY)
			AND visit_timestamp < DATE_SUB(NOW(), INTERVAL %d DAY)
			AND traffic_type = 'bot_crawl'",
			$period_days,
			$half_period
		);

		if ( $bot_type ) {
			$bot_filter = $wpdb->prepare( ' AND bot_type = %s', $bot_type );
			$where_clause_recent .= $bot_filter;
			$where_clause_older  .= $bot_filter;
		}

		$recent_crawls = $wpdb->get_var( "SELECT COUNT(*) FROM {$table} {$where_clause_recent}" );
		$older_crawls  = $wpdb->get_var( "SELECT COUNT(*) FROM {$table} {$where_clause_older}" );

		$drop_percentage = 0;
		if ( $older_crawls > 0 ) {
			$drop_percentage = ( ( $older_crawls - $recent_crawls ) / $older_crawls ) * 100;
		}

		return array(
			'drop_percentage' => round( $drop_percentage ),
			'recent_crawls'   => (int) $recent_crawls,
			'older_crawls'    => (int) $older_crawls,
		);
	}

	/**
	 * Analyze robots.txt configuration.
	 *
	 * @return array List of issues found.
	 */
	public function analyze_robots_txt() {
		$robots_url = home_url( '/robots.txt' );
		$response   = wp_remote_get( $robots_url );

		if ( is_wp_error( $response ) ) {
			return array( 'Could not fetch robots.txt file' );
		}

		$robots_content = wp_remote_retrieve_body( $response );
		$issues         = array();

		// Check if admin is blocked
		if ( strpos( $robots_content, 'Disallow: /wp-admin/' ) === false ) {
			$issues[] = '/wp-admin/ not blocked - wasting crawl budget on admin pages';
		}

		// Check for crawl-delay
		if ( preg_match( '/Crawl-delay:\s*(\d+)/i', $robots_content, $matches ) ) {
			$delay = (int) $matches[1];
			if ( $delay > 10 ) {
				$issues[] = 'Crawl-delay too high (' . $delay . 's) - reduces crawl frequency';
			}
		}

		// Check if wp-includes is blocked (not recommended)
		if ( strpos( $robots_content, 'Disallow: /wp-includes/' ) !== false ) {
			$issues[] = '/wp-includes/ is blocked - may prevent crawling of necessary CSS/JS files';
		}

		return $issues;
	}

	/**
	 * Get quick fix actions for common issues.
	 *
	 * @return array Array of quick fix buttons.
	 */
	public function get_quick_fixes() {
		return array(
			array(
				'id'     => 'block_admin',
				'label'  => 'Block /wp-admin/',
				'action' => 'update_robots_txt',
				'data'   => array( 'rule' => 'Disallow: /wp-admin/' ),
			),
			array(
				'id'     => 'view_404s',
				'label'  => 'View Broken Links',
				'action' => 'filter_404',
				'data'   => array( 'status_code' => '404' ),
			),
			array(
				'id'     => 'cache_settings',
				'label'  => 'Cache Settings',
				'action' => 'navigate',
				'data'   => array( 'url' => admin_url( 'admin.php?page=third-audience-cache' ) ),
			),
		);
	}
}
