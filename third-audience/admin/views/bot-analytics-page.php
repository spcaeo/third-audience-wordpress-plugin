<?php
/**
 * Bot Analytics v2.0 - Modern analytics dashboard
 *
 * @package ThirdAudience
 * @since   2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$analytics = TA_Bot_Analytics::get_instance();

// Get filters.
$filters = array();
if ( ! empty( $_GET['bot_type'] ) ) {
	$filters['bot_type'] = sanitize_text_field( wp_unslash( $_GET['bot_type'] ) );
}
if ( ! empty( $_GET['date_from'] ) ) {
	$filters['date_from'] = sanitize_text_field( wp_unslash( $_GET['date_from'] ) );
}
if ( ! empty( $_GET['date_to'] ) ) {
	$filters['date_to'] = sanitize_text_field( wp_unslash( $_GET['date_to'] ) );
}
if ( ! empty( $_GET['search'] ) ) {
	$filters['search'] = sanitize_text_field( wp_unslash( $_GET['search'] ) );
}

$time_period = isset( $_GET['period'] ) ? sanitize_text_field( wp_unslash( $_GET['period'] ) ) : 'day';

// Get analytics data.
$summary       = $analytics->get_summary( $filters );
$bot_stats     = $analytics->get_visits_by_bot( $filters );
$top_pages     = $analytics->get_top_pages( $filters, 10 );
$visits_time   = $analytics->get_visits_over_time( $filters, $time_period, 30 );

// Pagination.
$current_page  = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$per_page      = 30;
$offset        = ( $current_page - 1 ) * $per_page;
$recent_visits = $analytics->get_recent_visits( $filters, $per_page, $offset );

// Session Analytics (v2.6.0).
$session_stats     = $analytics->get_session_analytics();
$top_bots_session  = $analytics->get_top_bots_by_metric( 'pages_per_session', 10 );
$crawl_budget_day  = $analytics->get_crawl_budget_metrics( null, 'day' );
$crawl_budget_hour = $analytics->get_crawl_budget_metrics( null, 'hour' );

// Citation Performance (v2.7.0).
$citation_data = $analytics->get_citation_to_crawl_ratio( $filters, 10 );

// Content Performance Analysis (v2.7.0).
$content_performance = $analytics->get_content_performance_analysis( $filters );
$optimal_length      = $analytics->get_optimal_content_length();

// Rate limiting.
$rate_limiter      = new TA_Rate_Limiter();
$recent_violations = $rate_limiter->get_rate_limit_violations( 10 );
?>

<div class="wrap ta-bot-analytics">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Bot Analytics', 'third-audience' ); ?>
		<span style="font-size: 0.6em; color: #646970; font-weight: 400;">v<?php echo esc_html( TA_VERSION ); ?></span>
	</h1>
	<p class="description"><?php esc_html_e( 'Real-time insights into AI bot activity', 'third-audience' ); ?></p>

	<!-- Real-time Metrics Hero -->
	<div class="ta-hero-metrics">
		<div class="ta-hero-card">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-chart-line"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Total Bot Visits', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo number_format( $summary['total_visits'] ); ?></div>
				<div class="ta-hero-meta">
					<?php echo number_format( $summary['visits_today'] ); ?> today
				</div>
			</div>
		</div>

		<div class="ta-hero-card">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-admin-page"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Pages Crawled', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo number_format( $summary['unique_pages'] ); ?></div>
				<div class="ta-hero-meta"><?php echo number_format( $summary['unique_bots'] ); ?> unique bots</div>
			</div>
		</div>

		<div class="ta-hero-card">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-performance"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Cache Hit Rate', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo $summary['cache_hit_rate']; ?>%</div>
				<div class="ta-hero-meta">Performance metric</div>
			</div>
		</div>

		<div class="ta-hero-card">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-dashboard"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Avg Response', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo $summary['avg_response_time']; ?><span style="font-size: 14px;">ms</span></div>
				<div class="ta-hero-meta">Average response time</div>
			</div>
		</div>

		<div class="ta-hero-card">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-yes-alt"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Verified Bots', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo $summary['ip_verified_percentage']; ?>%</div>
				<div class="ta-hero-meta"><?php echo number_format( $summary['ip_verified_count'] ); ?> verified visits</div>
			</div>
		</div>
	</div>

	<!-- Session Metrics (v2.6.0) -->
	<div class="ta-section-header">
		<h2>
			<span class="dashicons dashicons-groups"></span>
			<?php esc_html_e( 'Session Analytics', 'third-audience' ); ?>
		</h2>
		<p class="description"><?php esc_html_e( 'Bot crawl sessions grouped by 30-minute windows', 'third-audience' ); ?></p>
	</div>

	<div class="ta-hero-metrics ta-hero-metrics-secondary">
		<div class="ta-hero-card">
			<div class="ta-hero-icon ta-hero-icon-purple">
				<span class="dashicons dashicons-admin-users"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Bot Fingerprints', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo number_format( $session_stats['total_bot_fingerprints'] ); ?></div>
				<div class="ta-hero-meta">Unique bot+IP combinations</div>
			</div>
		</div>

		<div class="ta-hero-card">
			<div class="ta-hero-icon ta-hero-icon-blue">
				<span class="dashicons dashicons-admin-page"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Pages Per Session', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo number_format( $session_stats['avg_pages_per_session'], 1 ); ?></div>
				<div class="ta-hero-meta"><?php echo number_format( $session_stats['avg_visits_per_bot'], 1 ); ?> visits/bot avg</div>
			</div>
		</div>

		<div class="ta-hero-card">
			<div class="ta-hero-icon ta-hero-icon-green">
				<span class="dashicons dashicons-clock"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Avg Session Duration', 'third-audience' ); ?></div>
				<div class="ta-hero-value">
					<?php
					$duration_mins = round( $session_stats['avg_session_duration'] / 60, 1 );
					echo number_format( $duration_mins, 1 );
					?>
					<span style="font-size: 14px;">min</span>
				</div>
				<div class="ta-hero-meta"><?php echo number_format( $session_stats['avg_session_duration'] ); ?>s average</div>
			</div>
		</div>

		<div class="ta-hero-card">
			<div class="ta-hero-icon ta-hero-icon-orange">
				<span class="dashicons dashicons-update"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Request Interval', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo number_format( $session_stats['avg_request_interval'] ); ?><span style="font-size: 14px;">s</span></div>
				<div class="ta-hero-meta">Time between requests</div>
			</div>
		</div>
	</div>

	<!-- Filters (Collapsible) -->
	<div class="ta-filters-section">
		<button type="button" class="ta-filters-toggle">
			<span class="dashicons dashicons-filter"></span>
			<?php esc_html_e( 'Filters & Export', 'third-audience' ); ?>
			<span class="dashicons dashicons-arrow-down-alt2"></span>
		</button>
		<div class="ta-filters-content" style="display: none;">
			<form method="get" id="ta-analytics-filters-form">
				<input type="hidden" name="page" value="third-audience-bot-analytics">
				<div class="ta-filter-grid">
					<div class="ta-filter-item">
						<label><?php esc_html_e( 'Date Range', 'third-audience' ); ?></label>
						<div class="ta-date-range">
							<input type="date" name="date_from" value="<?php echo esc_attr( $filters['date_from'] ?? '' ); ?>">
							<span>—</span>
							<input type="date" name="date_to" value="<?php echo esc_attr( $filters['date_to'] ?? '' ); ?>">
						</div>
					</div>
					<div class="ta-filter-item">
						<label><?php esc_html_e( 'Bot Type', 'third-audience' ); ?></label>
						<select name="bot_type">
							<option value=""><?php esc_html_e( 'All Bots', 'third-audience' ); ?></option>
							<?php foreach ( TA_Bot_Analytics::get_known_bots() as $bot_type => $bot_info ) : ?>
								<option value="<?php echo esc_attr( $bot_type ); ?>" <?php selected( $filters['bot_type'] ?? '', $bot_type ); ?>>
									<?php echo esc_html( $bot_info['name'] ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="ta-filter-item">
						<label><?php esc_html_e( 'Search', 'third-audience' ); ?></label>
						<input type="text" name="search" placeholder="<?php esc_attr_e( 'URL or title...', 'third-audience' ); ?>" value="<?php echo esc_attr( $filters['search'] ?? '' ); ?>">
					</div>
					<div class="ta-filter-item ta-filter-actions">
						<label>&nbsp;</label>
						<div>
							<button type="submit" class="button button-primary"><?php esc_html_e( 'Apply', 'third-audience' ); ?></button>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-bot-analytics' ) ); ?>" class="button"><?php esc_html_e( 'Reset', 'third-audience' ); ?></a>
							<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array_merge( $_GET, array( 'action' => 'export', 'export_format' => 'csv', 'export_type' => 'detailed' ) ) ), 'ta_export_analytics' ) ); ?>" class="button"><?php esc_html_e( 'Export', 'third-audience' ); ?></a>
							<button type="button" class="button ta-clear-all-visits" style="color: #d63638;"><?php esc_html_e( 'Clear All', 'third-audience' ); ?></button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>

	<!-- Bot Performance Matrix -->
	<div class="ta-cards-container">
		<!-- Bot Distribution -->
		<div class="ta-card">
			<div class="ta-card-header">
				<h2><?php esc_html_e( 'Bot Activity Distribution', 'third-audience' ); ?></h2>
			</div>
			<div class="ta-card-body">
				<?php if ( ! empty( $bot_stats ) ) : ?>
					<table class="ta-table ta-table-compact">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Bot', 'third-audience' ); ?></th>
								<th><?php esc_html_e( 'Visits', 'third-audience' ); ?></th>
								<th><?php esc_html_e( 'Share', 'third-audience' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$total = array_sum( wp_list_pluck( $bot_stats, 'count' ) );
							$priority_colors = array(
								'high'    => '#007aff',
								'medium'  => '#34c759',
								'low'     => '#ff9500',
								'blocked' => '#ff3b30',
							);
							foreach ( $bot_stats as $bot ) :
								$percentage = $total > 0 ? round( ( $bot['count'] / $total ) * 100 ) : 0;
								$bot_priority = $analytics->get_bot_priority( $bot['bot_type'], 'medium' );
								$color = $priority_colors[ $bot_priority ] ?? '#999';
								?>
								<tr>
									<td>
										<span class="ta-bot-name">
											<span class="ta-bot-dot" style="background: <?php echo esc_attr( $color ); ?>"></span>
											<?php echo esc_html( $bot['bot_name'] ); ?>
										</span>
									</td>
									<td><strong><?php echo number_format( $bot['count'] ); ?></strong></td>
									<td>
										<div class="ta-progress-bar">
											<div class="ta-progress-fill" style="width: <?php echo $percentage; ?>%; background: <?php echo esc_attr( $color ); ?>"></div>
										</div>
										<span class="ta-progress-label"><?php echo $percentage; ?>%</span>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p class="ta-no-data"><?php esc_html_e( 'No bot activity yet', 'third-audience' ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<!-- Top Content -->
		<div class="ta-card">
			<div class="ta-card-header">
				<h2><?php esc_html_e( 'Most Crawled Content', 'third-audience' ); ?></h2>
			</div>
			<div class="ta-card-body">
				<?php if ( ! empty( $top_pages ) ) : ?>
					<table class="ta-table ta-table-compact">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Page', 'third-audience' ); ?></th>
								<th><?php esc_html_e( 'Visits', 'third-audience' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( array_slice( $top_pages, 0, 10 ) as $page ) : ?>
								<tr>
									<td>
										<a href="<?php echo esc_url( $page['url'] ); ?>" target="_blank" class="ta-page-link">
											<?php echo esc_html( wp_trim_words( $page['post_title'] ?? $page['url'], 8 ) ); ?>
										</a>
									</td>
									<td><strong><?php echo number_format( $page['visits'] ); ?></strong></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p class="ta-no-data"><?php esc_html_e( 'No pages crawled yet', 'third-audience' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Session Activity Cards -->
	<div class="ta-cards-container">
		<!-- Top Bots by Session Activity -->
		<div class="ta-card">
			<div class="ta-card-header">
				<h2><?php esc_html_e( 'Top Bots by Session Activity', 'third-audience' ); ?></h2>
			</div>
			<div class="ta-card-body">
				<?php if ( ! empty( $top_bots_session ) ) : ?>
					<table class="ta-table ta-table-compact">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Bot', 'third-audience' ); ?></th>
								<th><?php esc_html_e( 'Pages/Session', 'third-audience' ); ?></th>
								<th><?php esc_html_e( 'Duration', 'third-audience' ); ?></th>
								<th><?php esc_html_e( 'Total Visits', 'third-audience' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $top_bots_session as $bot ) : ?>
								<tr>
									<td>
										<span class="ta-bot-name">
											<?php echo esc_html( $bot['bot_type'] ); ?>
										</span>
										<div style="font-size: 11px; color: #646970; margin-top: 2px;">
											<?php echo esc_html( wp_trim_words( $bot['user_agent'], 8 ) ); ?>
										</div>
									</td>
									<td>
										<strong><?php echo number_format( $bot['pages_per_session_avg'], 1 ); ?></strong> pages
									</td>
									<td>
										<?php
										$duration_mins = round( $bot['session_duration_avg'] / 60, 1 );
										echo number_format( $duration_mins, 1 );
										?> min
									</td>
									<td><?php echo number_format( $bot['visit_count'] ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p class="ta-no-data"><?php esc_html_e( 'No session data yet. Data appears after multiple visits from same bot+IP.', 'third-audience' ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<!-- Crawl Budget Metrics -->
		<div class="ta-card">
			<div class="ta-card-header">
				<h2><?php esc_html_e( 'Crawl Budget Analysis', 'third-audience' ); ?></h2>
			</div>
			<div class="ta-card-body">
				<div class="ta-crawl-budget-grid">
					<!-- Last 24 Hours -->
					<div class="ta-crawl-budget-section">
						<h4>
							<span class="dashicons dashicons-calendar-alt"></span>
							<?php esc_html_e( 'Last 24 Hours', 'third-audience' ); ?>
						</h4>
						<table class="ta-table ta-table-borderless">
							<tbody>
								<tr>
									<td><?php esc_html_e( 'Total Requests', 'third-audience' ); ?></td>
									<td><strong><?php echo number_format( $crawl_budget_day['total_requests'] ); ?></strong></td>
								</tr>
								<tr>
									<td><?php esc_html_e( 'Unique Pages', 'third-audience' ); ?></td>
									<td><strong><?php echo number_format( $crawl_budget_day['unique_pages'] ); ?></strong></td>
								</tr>
								<tr>
									<td><?php esc_html_e( 'Bandwidth Used', 'third-audience' ); ?></td>
									<td><strong><?php echo number_format( $crawl_budget_day['total_bandwidth_mb'], 2 ); ?> MB</strong></td>
								</tr>
								<tr>
									<td><?php esc_html_e( 'Cache Hit Rate', 'third-audience' ); ?></td>
									<td>
										<span class="ta-cache-hit-rate" style="color: <?php echo $crawl_budget_day['cache_hit_rate'] >= 80 ? '#34c759' : '#ff9500'; ?>">
											<strong><?php echo number_format( $crawl_budget_day['cache_hit_rate'], 1 ); ?>%</strong>
										</span>
									</td>
								</tr>
								<tr>
									<td><?php esc_html_e( 'Avg Response Time', 'third-audience' ); ?></td>
									<td><strong><?php echo number_format( $crawl_budget_day['avg_response_time'] ); ?> ms</strong></td>
								</tr>
							</tbody>
						</table>
					</div>

					<!-- Last Hour -->
					<div class="ta-crawl-budget-section">
						<h4>
							<span class="dashicons dashicons-clock"></span>
							<?php esc_html_e( 'Last Hour', 'third-audience' ); ?>
						</h4>
						<table class="ta-table ta-table-borderless">
							<tbody>
								<tr>
									<td><?php esc_html_e( 'Total Requests', 'third-audience' ); ?></td>
									<td><strong><?php echo number_format( $crawl_budget_hour['total_requests'] ); ?></strong></td>
								</tr>
								<tr>
									<td><?php esc_html_e( 'Unique Pages', 'third-audience' ); ?></td>
									<td><strong><?php echo number_format( $crawl_budget_hour['unique_pages'] ); ?></strong></td>
								</tr>
								<tr>
									<td><?php esc_html_e( 'Bandwidth Used', 'third-audience' ); ?></td>
									<td><strong><?php echo number_format( $crawl_budget_hour['total_bandwidth_mb'], 2 ); ?> MB</strong></td>
								</tr>
								<tr>
									<td><?php esc_html_e( 'Cache Hit Rate', 'third-audience' ); ?></td>
									<td>
										<span class="ta-cache-hit-rate" style="color: <?php echo $crawl_budget_hour['cache_hit_rate'] >= 80 ? '#34c759' : '#ff9500'; ?>">
											<strong><?php echo number_format( $crawl_budget_hour['cache_hit_rate'], 1 ); ?>%</strong>
										</span>
									</td>
								</tr>
								<tr>
									<td><?php esc_html_e( 'Avg Response Time', 'third-audience' ); ?></td>
									<td><strong><?php echo number_format( $crawl_budget_hour['avg_response_time'] ); ?> ms</strong></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<!-- Crawl Efficiency Insights -->
				<div class="ta-crawl-insights">
					<h4 style="margin-top: 20px; margin-bottom: 10px;">
						<span class="dashicons dashicons-lightbulb"></span>
						<?php esc_html_e( 'Insights', 'third-audience' ); ?>
					</h4>
					<ul class="ta-insights-list">
						<?php if ( $crawl_budget_day['cache_hit_rate'] >= 90 ) : ?>
							<li class="ta-insight-good">
								<span class="dashicons dashicons-yes-alt"></span>
								<?php esc_html_e( 'Excellent cache performance - 90%+ hit rate!', 'third-audience' ); ?>
							</li>
						<?php elseif ( $crawl_budget_day['cache_hit_rate'] >= 70 ) : ?>
							<li class="ta-insight-ok">
								<span class="dashicons dashicons-info"></span>
								<?php esc_html_e( 'Good cache performance - consider pre-warming popular content.', 'third-audience' ); ?>
							</li>
						<?php else : ?>
							<li class="ta-insight-warning">
								<span class="dashicons dashicons-warning"></span>
								<?php esc_html_e( 'Low cache hit rate - enable cache warming for better performance.', 'third-audience' ); ?>
							</li>
						<?php endif; ?>

						<?php
						$unique_ratio = $crawl_budget_day['total_requests'] > 0
							? ( $crawl_budget_day['unique_pages'] / $crawl_budget_day['total_requests'] ) * 100
							: 0;
						?>
						<?php if ( $unique_ratio > 80 ) : ?>
							<li class="ta-insight-ok">
								<span class="dashicons dashicons-info"></span>
								<?php
								printf(
									/* translators: %s: percentage of unique pages */
									esc_html__( 'High content diversity - %s%% of requests are unique pages.', 'third-audience' ),
									number_format( $unique_ratio, 1 )
								);
								?>
							</li>
						<?php else : ?>
							<li class="ta-insight-good">
								<span class="dashicons dashicons-yes-alt"></span>
								<?php
								printf(
									/* translators: %s: percentage of repeated page visits */
									esc_html__( 'Bots are re-crawling content (%s%% unique) - good for freshness!', 'third-audience' ),
									number_format( $unique_ratio, 1 )
								);
								?>
							</li>
						<?php endif; ?>

						<?php if ( $session_stats['avg_pages_per_session'] >= 5 ) : ?>
							<li class="ta-insight-good">
								<span class="dashicons dashicons-yes-alt"></span>
								<?php
								printf(
									/* translators: %s: average pages per session */
									esc_html__( 'Deep crawling detected - %s pages per session average.', 'third-audience' ),
									number_format( $session_stats['avg_pages_per_session'], 1 )
								);
								?>
							</li>
						<?php endif; ?>
					</ul>
				</div>
			</div>
		</div>
	</div>

	<!-- Citation Performance (v2.7.0) -->
	<div class="ta-card" style="margin-top: 20px;">
		<div class="ta-card-header">
			<h2>
				<span class="dashicons dashicons-admin-links"></span>
				<?php esc_html_e( 'Citation Performance', 'third-audience' ); ?>
			</h2>
			<p class="description" style="margin: 8px 0 0 0;">
				<?php esc_html_e( 'Pages crawled by AI bots vs. cited by AI platforms', 'third-audience' ); ?>
			</p>
		</div>
		<div class="ta-card-body">
			<?php if ( empty( $citation_data ) ) : ?>
				<p class="ta-no-data"><?php esc_html_e( 'No citation data yet. Citations are tracked when users click links from AI platforms (ChatGPT, Perplexity, etc.).', 'third-audience' ); ?></p>
			<?php else : ?>
				<table class="ta-table ta-table-compact">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Page', 'third-audience' ); ?></th>
							<th style="text-align: right;"><?php esc_html_e( 'Crawls', 'third-audience' ); ?></th>
							<th style="text-align: right;"><?php esc_html_e( 'Citations', 'third-audience' ); ?></th>
							<th style="text-align: right;"><?php esc_html_e( 'Rate', 'third-audience' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $citation_data as $page ) : ?>
							<?php
							$citation_rate_percent = round( $page['citation_rate'] * 100, 1 );

							// Color coding based on citation rate.
							if ( $citation_rate_percent >= 50 ) {
								$rate_class = 'ta-citation-rate-high';
								$rate_color = '#34c759';
							} elseif ( $citation_rate_percent >= 20 ) {
								$rate_class = 'ta-citation-rate-medium';
								$rate_color = '#ff9500';
							} else {
								$rate_class = 'ta-citation-rate-low';
								$rate_color = '#ff3b30';
							}
							?>
							<tr>
								<td>
									<a href="<?php echo esc_url( $page['url'] ); ?>" target="_blank" class="ta-page-link">
										<?php echo esc_html( wp_trim_words( $page['post_title'] ?? $page['url'], 8 ) ); ?>
									</a>
								</td>
								<td style="text-align: right;">
									<strong><?php echo number_format( $page['crawls'] ); ?></strong>
								</td>
								<td style="text-align: right;">
									<?php echo number_format( $page['citations'] ); ?>
								</td>
								<td style="text-align: right;">
									<span class="ta-citation-rate-badge <?php echo esc_attr( $rate_class ); ?>" style="color: <?php echo esc_attr( $rate_color ); ?>;">
										<?php echo esc_html( $citation_rate_percent ); ?>%
									</span>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<!-- Insight Box -->
				<div style="margin-top: 16px; padding: 12px; background: #f9f9fb; border-left: 3px solid #007aff; border-radius: 4px;">
					<p style="margin: 0; font-size: 13px; color: #646970;">
						<span class="dashicons dashicons-info" style="font-size: 16px; vertical-align: middle;"></span>
						<strong><?php esc_html_e( 'What does this mean?', 'third-audience' ); ?></strong>
						<?php esc_html_e( ' A low citation rate means your content is being crawled but not cited. Focus on improving content quality, adding structured data, or building topical authority.', 'third-audience' ); ?>
					</p>
				</div>
			<?php endif; ?>
		</div>
	</div>

<!-- Content Performance Insights (v2.7.0) -->
<div class="ta-card" style="margin-top: 20px;">
	<div class="ta-card-header">
		<h2>
			<span class="dashicons dashicons-media-document"></span>
			<?php esc_html_e( 'Content Performance Insights', 'third-audience' ); ?>
		</h2>
		<p class="description" style="margin: 8px 0 0 0;">
			<?php esc_html_e( 'Content characteristics correlated with citation rates', 'third-audience' ); ?>
		</p>
	</div>
	<div class="ta-card-body">
		<?php if ( $content_performance['cited_posts']['total_count'] === 0 && $content_performance['crawled_posts']['total_count'] === 0 ) : ?>
			<p class="ta-no-data"><?php esc_html_e( 'No content metrics yet. Content analysis happens automatically when AI bots crawl your site.', 'third-audience' ); ?></p>
		<?php else : ?>
			<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
				<!-- Cited Posts Stats -->
				<div style="padding: 16px; background: #f0f9ff; border-radius: 8px; border: 1px solid #bae6fd;">
					<h4 style="margin: 0 0 12px 0; font-size: 14px; color: #0369a1;">
						<span class="dashicons dashicons-yes-alt" style="font-size: 16px; vertical-align: middle;"></span>
						<?php esc_html_e( 'Cited Posts', 'third-audience' ); ?>
					</h4>
					<table class="ta-table ta-table-borderless" style="margin: 0;">
						<tbody>
							<tr>
								<td><?php esc_html_e( 'Avg Word Count', 'third-audience' ); ?></td>
								<td><strong><?php echo number_format( $content_performance['cited_posts']['avg_word_count'] ); ?></strong> words</td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Avg Headings', 'third-audience' ); ?></td>
								<td><strong><?php echo number_format( $content_performance['cited_posts']['avg_heading_count'], 1 ); ?></strong></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Avg Images', 'third-audience' ); ?></td>
								<td><strong><?php echo number_format( $content_performance['cited_posts']['avg_image_count'], 1 ); ?></strong></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Schema Markup', 'third-audience' ); ?></td>
								<td><strong><?php echo number_format( $content_performance['cited_posts']['schema_percentage'], 1 ); ?>%</strong></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Avg Freshness', 'third-audience' ); ?></td>
								<td><strong><?php echo number_format( $content_performance['cited_posts']['avg_freshness_days'] ); ?></strong> days</td>
							</tr>
						</tbody>
					</table>
				</div>

				<!-- Crawled Posts Stats -->
				<div style="padding: 16px; background: #fef9f3; border-radius: 8px; border: 1px solid #fed7aa;">
					<h4 style="margin: 0 0 12px 0; font-size: 14px; color: #c2410c;">
						<span class="dashicons dashicons-search" style="font-size: 16px; vertical-align: middle;"></span>
						<?php esc_html_e( 'Crawled Posts', 'third-audience' ); ?>
					</h4>
					<table class="ta-table ta-table-borderless" style="margin: 0;">
						<tbody>
							<tr>
								<td><?php esc_html_e( 'Avg Word Count', 'third-audience' ); ?></td>
								<td><strong><?php echo number_format( $content_performance['crawled_posts']['avg_word_count'] ); ?></strong> words</td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Avg Headings', 'third-audience' ); ?></td>
								<td><strong><?php echo number_format( $content_performance['crawled_posts']['avg_heading_count'], 1 ); ?></strong></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Avg Images', 'third-audience' ); ?></td>
								<td><strong><?php echo number_format( $content_performance['crawled_posts']['avg_image_count'], 1 ); ?></strong></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Schema Markup', 'third-audience' ); ?></td>
								<td><strong><?php echo number_format( $content_performance['crawled_posts']['schema_percentage'], 1 ); ?>%</strong></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Avg Freshness', 'third-audience' ); ?></td>
								<td><strong><?php echo number_format( $content_performance['crawled_posts']['avg_freshness_days'] ); ?></strong> days</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<!-- Key Insights -->
			<div style="margin-top: 16px;">
				<h4 style="margin: 0 0 12px 0; font-size: 14px;">
					<span class="dashicons dashicons-lightbulb"></span>
					<?php esc_html_e( 'Key Insights', 'third-audience' ); ?>
				</h4>
				<ul class="ta-insights-list">
					<?php if ( $optimal_length['optimal_range'] !== 'N/A' ) : ?>
						<li class="ta-insight-good">
							<span class="dashicons dashicons-yes-alt"></span>
							<?php
							printf(
								/* translators: 1: word count range, 2: citation count */
								esc_html__( 'Optimal content length: %1$s words (%2$s citations)', 'third-audience' ),
								'<strong>' . esc_html( $optimal_length['optimal_range'] ) . '</strong>',
								'<strong>' . number_format( $optimal_length['citation_count'] ) . '</strong>'
							);
							?>
						</li>
					<?php endif; ?>

					<?php if ( $content_performance['cited_posts']['avg_word_count'] > 0 && $content_performance['crawled_posts']['avg_word_count'] > 0 ) : ?>
						<?php
						$word_count_diff = $content_performance['cited_posts']['avg_word_count'] - $content_performance['crawled_posts']['avg_word_count'];
						?>
						<li class="<?php echo $word_count_diff > 0 ? 'ta-insight-good' : 'ta-insight-warning'; ?>">
							<span class="dashicons dashicons-<?php echo $word_count_diff > 0 ? 'yes-alt' : 'info'; ?>"></span>
							<?php
							if ( $word_count_diff > 0 ) {
								printf(
									/* translators: %s: word count difference */
									esc_html__( 'Cited posts are %s words longer on average', 'third-audience' ),
									'<strong>' . number_format( abs( $word_count_diff ) ) . '</strong>'
								);
							} else {
								esc_html_e( 'Consider writing longer, more comprehensive content', 'third-audience' );
							}
							?>
						</li>
					<?php endif; ?>

					<?php if ( $content_performance['schema_multiplier'] > 1.5 ) : ?>
						<li class="ta-insight-good">
							<span class="dashicons dashicons-yes-alt"></span>
							<?php
							printf(
								/* translators: %s: schema multiplier */
								esc_html__( 'Posts with schema markup get %sx more citations', 'third-audience' ),
								'<strong>' . number_format( $content_performance['schema_multiplier'], 1 ) . '</strong>'
							);
							?>
						</li>
					<?php elseif ( $content_performance['cited_posts']['schema_percentage'] < 50 ) : ?>
						<li class="ta-insight-warning">
							<span class="dashicons dashicons-warning"></span>
							<?php esc_html_e( 'Consider adding schema.org markup to improve citation rates', 'third-audience' ); ?>
						</li>
					<?php endif; ?>

					<?php if ( $content_performance['cited_posts']['avg_freshness_days'] < $content_performance['crawled_posts']['avg_freshness_days'] ) : ?>
						<li class="ta-insight-good">
							<span class="dashicons dashicons-yes-alt"></span>
							<?php esc_html_e( 'Fresh content performs better - keep your posts updated!', 'third-audience' ); ?>
						</li>
					<?php endif; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
</div>

	<!-- Crawl Budget Recommendations (v2.8.0) -->
	<?php include __DIR__ . '/crawl-recommendations-card.php'; ?>

	<!-- Activity Timeline Chart -->
	<div class="ta-card">
		<div class="ta-card-header">
			<h2><?php esc_html_e( 'Activity Timeline', 'third-audience' ); ?></h2>
			<select id="ta-period-selector" onchange="location.href='<?php echo esc_url( admin_url( 'admin.php?page=third-audience-bot-analytics&period=' ) ); ?>'+this.value">
				<option value="hour" <?php selected( $time_period, 'hour' ); ?>><?php esc_html_e( 'Hourly', 'third-audience' ); ?></option>
				<option value="day" <?php selected( $time_period, 'day' ); ?>><?php esc_html_e( 'Daily', 'third-audience' ); ?></option>
				<option value="week" <?php selected( $time_period, 'week' ); ?>><?php esc_html_e( 'Weekly', 'third-audience' ); ?></option>
				<option value="month" <?php selected( $time_period, 'month' ); ?>><?php esc_html_e( 'Monthly', 'third-audience' ); ?></option>
			</select>
		</div>
		<div class="ta-card-body">
			<canvas id="ta-visits-chart" style="max-height: 300px;"></canvas>
		</div>
	</div>

	<!-- Live Activity Feed -->
	<div class="ta-card">
		<div class="ta-card-header">
			<h2>
				<span class="ta-live-indicator"></span>
				<?php esc_html_e( 'Live Activity Feed', 'third-audience' ); ?>
			</h2>
			<div class="ta-card-actions">
				<button type="button" class="button button-secondary ta-cache-help-toggle"><?php esc_html_e( 'Cache Guide', 'third-audience' ); ?></button>
				<button type="button" class="button ta-feed-toggle-btn" data-paused="false"><?php esc_html_e( 'Pause', 'third-audience' ); ?></button>
			</div>
		</div>
		<div class="ta-card-body">
			<table class="ta-table" id="ta-activity-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Time', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Bot', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Page', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Location', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'IP Status', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Cache', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Response', 'third-audience' ); ?></th>
					</tr>
				</thead>
				<tbody id="ta-activity-tbody">
					<?php if ( empty( $recent_visits ) ) : ?>
						<tr>
							<td colspan="7" class="ta-no-data"><?php esc_html_e( 'No activity yet', 'third-audience' ); ?></td>
						</tr>
					<?php else : ?>
						<?php
						$priority_colors = array(
							'high'    => '#007aff',
							'medium'  => '#34c759',
							'low'     => '#ff9500',
							'blocked' => '#ff3b30',
						);
						foreach ( $recent_visits as $visit ) :
							$bot_priority = $analytics->get_bot_priority( $visit['bot_type'], 'medium' );
							$priority_color = $priority_colors[ $bot_priority ] ?? '#999';
							?>
							<tr>
								<td class="ta-time-cell">
									<?php echo esc_html( human_time_diff( strtotime( $visit['visit_timestamp'] ), current_time( 'timestamp' ) ) ); ?> ago
								</td>
								<td>
									<span class="ta-bot-tag" style="border-left-color: <?php echo esc_attr( $priority_color ); ?>;">
										<?php echo esc_html( $visit['bot_name'] ); ?>
									</span>
								</td>
								<td>
									<a href="<?php echo esc_url( $visit['url'] ); ?>" target="_blank" class="ta-page-link">
										<?php echo esc_html( wp_trim_words( $visit['post_title'] ?? $visit['url'], 6 ) ); ?>
									</a>
								</td>
								<td>
									<?php if ( ! empty( $visit['country_code'] ) ) : ?>
										<span class="ta-location" title="<?php echo esc_attr( $visit['ip_address'] ?? '' ); ?>">
											<?php echo esc_html( $visit['country_code'] ); ?>
										</span>
									<?php else : ?>
										-
									<?php endif; ?>
								</td>
								<td>
									<?php
									// IP Verification Badge (v2.7.0).
									if ( isset( $visit['ip_verified'] ) ) {
										if ( 1 === (int) $visit['ip_verified'] ) {
											$verify_class = 'ta-ip-verified';
											$verify_icon  = 'dashicons-yes-alt';
											$verify_title = sprintf(
												/* translators: %s: verification method */
												esc_attr__( 'Verified via %s', 'third-audience' ),
												$visit['ip_verification_method'] ?? 'IP'
											);
										} elseif ( 0 === (int) $visit['ip_verified'] ) {
											$verify_class = 'ta-ip-failed';
											$verify_icon  = 'dashicons-dismiss';
											$verify_title = esc_attr__( 'Failed verification', 'third-audience' );
										} else {
											$verify_class = 'ta-ip-unverified';
											$verify_icon  = 'dashicons-minus';
											$verify_title = esc_attr__( 'Not verified', 'third-audience' );
										}
										?>
									<span class="ta-ip-verify-badge <?php echo esc_attr( $verify_class ); ?>" title="<?php echo $verify_title; ?>">
										<span class="dashicons <?php echo esc_attr( $verify_icon ); ?>"></span>
									</span>
									<?php } else { ?>
										-
									<?php } ?>
								</td>
								<td>
									<span class="ta-cache-badge ta-cache-<?php echo esc_attr( strtolower( $visit['cache_status'] ) ); ?>">
										<?php echo esc_html( $visit['cache_status'] ); ?>
									</span>
								</td>
								<td>
									<?php if ( $visit['response_time'] ) : ?>
										<span class="ta-response-time"><?php echo esc_html( $visit['response_time'] ); ?>ms</span>
									<?php else : ?>
										-
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

			<!-- Pagination -->
			<?php if ( $summary['total_visits'] > $per_page ) : ?>
				<div class="ta-pagination">
					<?php
					$total_pages = ceil( $summary['total_visits'] / $per_page );
					$pagination = paginate_links( array(
						'base'      => add_query_arg( 'paged', '%#%' ),
						'format'    => '',
						'current'   => $current_page,
						'total'     => $total_pages,
						'prev_text' => '‹',
						'next_text' => '›',
					) );
					echo wp_kses_post( $pagination );
					?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Rate Limit Violations (only if any) -->
	<?php if ( ! empty( $recent_violations ) ) : ?>
		<div class="ta-card ta-card-alert">
			<div class="ta-card-header">
				<h2>
					<span class="dashicons dashicons-warning"></span>
					<?php esc_html_e( 'Rate Limit Violations', 'third-audience' ); ?>
				</h2>
			</div>
			<div class="ta-card-body">
				<table class="ta-table ta-table-compact">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Bot', 'third-audience' ); ?></th>
							<th><?php esc_html_e( 'IP Address', 'third-audience' ); ?></th>
							<th><?php esc_html_e( 'URL', 'third-audience' ); ?></th>
							<th><?php esc_html_e( 'Time', 'third-audience' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $recent_violations as $violation ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $violation['bot_name'] ); ?></strong></td>
								<td><code><?php echo esc_html( $violation['ip_address'] ?: 'N/A' ); ?></code></td>
								<td class="ta-url-cell"><?php echo esc_html( wp_trim_words( $violation['url'], 8 ) ); ?></td>
								<td><?php echo esc_html( human_time_diff( strtotime( $violation['visit_timestamp'] ), current_time( 'timestamp' ) ) ); ?> ago</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	<?php endif; ?>

	<!-- JavaScript Data -->
	<script type="text/javascript">
		var taAnalyticsData = {
			visitsOverTime: <?php echo wp_json_encode( array_reverse( $visits_time ) ); ?>,
			botDistribution: <?php echo wp_json_encode( $bot_stats ); ?>,
			period: <?php echo wp_json_encode( $time_period ); ?>,
			nonce: <?php echo wp_json_encode( wp_create_nonce( 'ta_bot_analytics' ) ); ?>,
			feedNonce: <?php echo wp_json_encode( wp_create_nonce( 'ta_bot_analytics_feed' ) ); ?>
		};

		// Filters toggle
		jQuery(document).ready(function($) {
			$('.ta-filters-toggle').on('click', function() {
				$(this).toggleClass('active');
				$('.ta-filters-content').slideToggle(200);
				$(this).find('.dashicons-arrow-down-alt2').toggleClass('rotated');
			});
		});
	</script>
</div>

<!-- Cache Guide Modal -->
<div class="ta-cache-modal-overlay" style="display: none;">
	<div class="ta-cache-modal">
		<div class="ta-cache-modal-header">
			<h2><?php esc_html_e( 'Cache Status Guide', 'third-audience' ); ?></h2>
			<button type="button" class="ta-cache-modal-close">
				<span class="dashicons dashicons-no-alt"></span>
			</button>
		</div>
		<div class="ta-cache-modal-body">
			<div class="ta-cache-guide-grid">
				<div class="ta-cache-guide-item">
					<span class="ta-cache-badge ta-cache-hit">HIT</span>
					<h4><?php esc_html_e( 'Cache Hit', 'third-audience' ); ?></h4>
					<p><?php esc_html_e( 'Served from cache (1-5ms). Best performance.', 'third-audience' ); ?></p>
				</div>
				<div class="ta-cache-guide-item">
					<span class="ta-cache-badge ta-cache-pre_generated">PRE_GENERATED</span>
					<h4><?php esc_html_e( 'Pre-generated', 'third-audience' ); ?></h4>
					<p><?php esc_html_e( 'Served from post meta (<1ms). Fastest.', 'third-audience' ); ?></p>
				</div>
				<div class="ta-cache-guide-item">
					<span class="ta-cache-badge ta-cache-miss">MISS</span>
					<h4><?php esc_html_e( 'Cache Miss', 'third-audience' ); ?></h4>
					<p><?php esc_html_e( 'Generated fresh (10-50ms). Slower.', 'third-audience' ); ?></p>
				</div>
				<div class="ta-cache-guide-item">
					<span class="ta-cache-badge ta-cache-failed">FAILED</span>
					<h4><?php esc_html_e( 'Failed', 'third-audience' ); ?></h4>
					<p><?php esc_html_e( 'Generation error. Check System Health.', 'third-audience' ); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>
