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
if ( ! empty( $_GET['content_type'] ) ) {
	$filters['content_type'] = sanitize_text_field( wp_unslash( $_GET['content_type'] ) );
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
		<div class="ta-hero-card ta-hero-card-clickable" data-metric="total_visits" title="<?php esc_attr_e( 'Click to see visit breakdown by bot type', 'third-audience' ); ?>">
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
			<span class="ta-card-arrow"><span class="dashicons dashicons-arrow-right-alt2"></span></span>
		</div>

		<div class="ta-hero-card ta-hero-card-clickable" data-metric="pages_crawled" title="<?php esc_attr_e( 'Click to see all crawled pages', 'third-audience' ); ?>">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-admin-page"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Pages Crawled', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo number_format( $summary['unique_pages'] ); ?></div>
				<div class="ta-hero-meta"><?php echo number_format( $summary['unique_bots'] ); ?> unique bots</div>
			</div>
			<span class="ta-card-arrow"><span class="dashicons dashicons-arrow-right-alt2"></span></span>
		</div>

		<div class="ta-hero-card ta-hero-card-clickable" data-metric="cache_hit_rate" title="<?php esc_attr_e( 'Click to see cache performance details', 'third-audience' ); ?>">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-performance"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Cache Hit Rate', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo $summary['cache_hit_rate']; ?>%</div>
				<div class="ta-hero-meta">Performance metric</div>
			</div>
			<span class="ta-card-arrow"><span class="dashicons dashicons-arrow-right-alt2"></span></span>
		</div>

		<div class="ta-hero-card ta-hero-card-clickable" data-metric="avg_response" title="<?php esc_attr_e( 'Click to see response time breakdown', 'third-audience' ); ?>">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-dashboard"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Avg Response', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo $summary['avg_response_time']; ?><span style="font-size: 14px;">ms</span></div>
				<div class="ta-hero-meta">Average response time</div>
			</div>
			<span class="ta-card-arrow"><span class="dashicons dashicons-arrow-right-alt2"></span></span>
		</div>

		<div class="ta-hero-card ta-hero-card-clickable" data-metric="verified_bots" title="<?php esc_attr_e( 'Click to see verification breakdown', 'third-audience' ); ?>">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-yes-alt"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Verified Bots', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo $summary['ip_verified_percentage']; ?>%</div>
				<div class="ta-hero-meta"><?php echo number_format( $summary['ip_verified_count'] ); ?> verified visits</div>
			</div>
			<span class="ta-card-arrow"><span class="dashicons dashicons-arrow-right-alt2"></span></span>
		</div>
	</div>

	<!-- Session Metrics (v2.6.0) -->
	<div class="ta-section-header">
		<h2>
			<span class="dashicons dashicons-groups"></span>
			<?php esc_html_e( 'Session Analytics', 'third-audience' ); ?>
		</h2>
		<p class="description">
			<?php esc_html_e( 'Bot crawl sessions grouped by 30-minute windows', 'third-audience' ); ?>
		</p>
	</div>

	<div class="ta-hero-metrics ta-hero-metrics-secondary">
		<div class="ta-hero-card ta-hero-card-clickable" data-metric="fingerprints" title="<?php esc_attr_e( 'Click to see all bot fingerprints', 'third-audience' ); ?>">
			<div class="ta-hero-icon ta-hero-icon-purple">
				<span class="dashicons dashicons-admin-users"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Bot Fingerprints', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo number_format( $session_stats['total_bot_fingerprints'] ); ?></div>
				<div class="ta-hero-meta">Unique bot+IP combinations</div>
			</div>
			<span class="ta-click-hint"><span class="dashicons dashicons-arrow-right-alt2"></span></span>
		</div>

		<div class="ta-hero-card ta-hero-card-clickable" data-metric="pages_per_session" title="<?php esc_attr_e( 'Click to see pages per session breakdown', 'third-audience' ); ?>">
			<div class="ta-hero-icon ta-hero-icon-blue">
				<span class="dashicons dashicons-admin-page"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Pages Per Session', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo number_format( $session_stats['avg_pages_per_session'], 1 ); ?></div>
				<div class="ta-hero-meta"><?php echo number_format( $session_stats['avg_visits_per_bot'], 1 ); ?> visits/bot avg</div>
			</div>
			<span class="ta-click-hint"><span class="dashicons dashicons-arrow-right-alt2"></span></span>
		</div>

		<div class="ta-hero-card ta-hero-card-clickable" data-metric="session_duration" title="<?php esc_attr_e( 'Click to see session duration breakdown', 'third-audience' ); ?>">
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
			<span class="ta-click-hint"><span class="dashicons dashicons-arrow-right-alt2"></span></span>
		</div>

		<div class="ta-hero-card ta-hero-card-clickable" data-metric="request_interval" title="<?php esc_attr_e( 'Click to see request interval breakdown', 'third-audience' ); ?>">
			<div class="ta-hero-icon ta-hero-icon-orange">
				<span class="dashicons dashicons-update"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Request Interval', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo number_format( $session_stats['avg_request_interval'] ); ?><span style="font-size: 14px;">s</span></div>
				<div class="ta-hero-meta">Time between requests</div>
			</div>
			<span class="ta-click-hint"><span class="dashicons dashicons-arrow-right-alt2"></span></span>
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
						<label><?php esc_html_e( 'Content Type', 'third-audience' ); ?></label>
						<select name="content_type">
							<option value=""><?php esc_html_e( 'All Content', 'third-audience' ); ?></option>
							<option value="html" <?php selected( $filters['content_type'] ?? '', 'html' ); ?>><?php esc_html_e( 'HTML Pages', 'third-audience' ); ?></option>
							<option value="markdown" <?php selected( $filters['content_type'] ?? '', 'markdown' ); ?>><?php esc_html_e( 'Markdown (.md)', 'third-audience' ); ?></option>
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
				<?php if ( ! empty( $bot_stats ) ) : ?>
				<button type="button" class="button button-small ta-export-btn" data-export="bot-distribution" title="<?php esc_attr_e( 'Export to CSV', 'third-audience' ); ?>">
					<span class="dashicons dashicons-download"></span>
				</button>
				<?php endif; ?>
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
				<?php if ( ! empty( $top_pages ) ) : ?>
				<button type="button" class="button button-small ta-export-btn" data-export="top-content" title="<?php esc_attr_e( 'Export to CSV', 'third-audience' ); ?>">
					<span class="dashicons dashicons-download"></span>
				</button>
				<?php endif; ?>
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
										<a href="<?php echo esc_url( $page['url'] ); ?>" target="_blank" class="ta-page-link" title="<?php esc_attr_e( 'View page', 'third-audience' ); ?>">
											<?php echo esc_html( wp_trim_words( $page['post_title'] ?? $page['url'], 8 ) ); ?>
										</a>
										<a href="<?php echo esc_url( untrailingslashit( $page['url'] ) . '.md' ); ?>" target="_blank" class="ta-md-link" title="<?php esc_attr_e( 'View as Markdown (what bots see)', 'third-audience' ); ?>">
											<span class="dashicons dashicons-media-text"></span>
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
				<?php if ( ! empty( $top_bots_session ) ) : ?>
				<button type="button" class="button button-small ta-export-btn" data-export="session-activity" title="<?php esc_attr_e( 'Export to CSV', 'third-audience' ); ?>">
					<span class="dashicons dashicons-download"></span>
				</button>
				<?php endif; ?>
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
				<button type="button" class="button button-small ta-export-btn" data-export="crawl-budget" title="<?php esc_attr_e( 'Export to CSV', 'third-audience' ); ?>">
					<span class="dashicons dashicons-download"></span>
				</button>
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
			<?php if ( ! empty( $citation_data ) ) : ?>
			<button type="button" class="button button-small ta-export-btn" data-export="citation-performance" title="<?php esc_attr_e( 'Export to CSV', 'third-audience' ); ?>">
				<span class="dashicons dashicons-download"></span>
			</button>
			<?php endif; ?>
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
		<?php if ( $content_performance['cited_posts']['total_count'] > 0 || $content_performance['crawled_posts']['total_count'] > 0 ) : ?>
		<button type="button" class="button button-small ta-export-btn" data-export="content-insights" title="<?php esc_attr_e( 'Export to CSV', 'third-audience' ); ?>">
			<span class="dashicons dashicons-download"></span>
		</button>
		<?php endif; ?>
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
			<div class="ta-header-actions">
				<select id="ta-period-selector" onchange="location.href='<?php echo esc_url( admin_url( 'admin.php?page=third-audience-bot-analytics&period=' ) ); ?>'+this.value">
					<option value="hour" <?php selected( $time_period, 'hour' ); ?>><?php esc_html_e( 'Hourly', 'third-audience' ); ?></option>
					<option value="day" <?php selected( $time_period, 'day' ); ?>><?php esc_html_e( 'Daily', 'third-audience' ); ?></option>
					<option value="week" <?php selected( $time_period, 'week' ); ?>><?php esc_html_e( 'Weekly', 'third-audience' ); ?></option>
					<option value="month" <?php selected( $time_period, 'month' ); ?>><?php esc_html_e( 'Monthly', 'third-audience' ); ?></option>
				</select>
				<button type="button" class="button button-small ta-export-btn" data-export="activity-timeline" title="<?php esc_attr_e( 'Export to CSV', 'third-audience' ); ?>">
					<span class="dashicons dashicons-download"></span>
				</button>
			</div>
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
				<button type="button" class="button button-small ta-export-btn" data-export="live-activity" title="<?php esc_attr_e( 'Export to CSV', 'third-audience' ); ?>">
					<span class="dashicons dashicons-download"></span>
				</button>
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
						<th><?php esc_html_e( 'Type', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Location', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'IP Status', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Cache', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Response', 'third-audience' ); ?></th>
					</tr>
				</thead>
				<tbody id="ta-activity-tbody">
					<?php if ( empty( $recent_visits ) ) : ?>
						<tr>
							<td colspan="8" class="ta-no-data"><?php esc_html_e( 'No activity yet', 'third-audience' ); ?></td>
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
									<?php
									$content_type = $visit['content_type'] ?? 'html';
									$type_class   = 'markdown' === $content_type ? 'ta-content-type-md' : 'ta-content-type-html';
									$type_label   = 'markdown' === $content_type ? 'MD' : 'HTML';
									?>
									<span class="ta-content-type-badge <?php echo esc_attr( $type_class ); ?>">
										<?php echo esc_html( $type_label ); ?>
									</span>
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
			ajaxUrl: <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,
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

<?php
// Include modal components.
require_once __DIR__ . '/components/modals/cache-guide-modal.php';
require_once __DIR__ . '/components/modals/session-analytics-modal.php';
require_once __DIR__ . '/components/modals/hero-metrics-modal.php';
?>

<script type="text/javascript">
// Session Analytics Drill-Down
jQuery(document).ready(function($) {
	var sessionChart = null;
	var heroChart = null;

	// Hero metrics (top 5 cards)
	var heroMetrics = ['total_visits', 'pages_crawled', 'cache_hit_rate', 'avg_response', 'verified_bots'];

	// Click handler for all clickable cards
	$('.ta-hero-card-clickable').on('click', function() {
		var metric = $(this).data('metric');
		if (heroMetrics.indexOf(metric) !== -1) {
			openHeroModal(metric);
		} else {
			openSessionModal(metric);
		}
	});

	// Close session modal
	$('.ta-session-modal-close, .ta-session-modal-overlay').on('click', function(e) {
		if (e.target === this || $(this).hasClass('ta-session-modal-close')) {
			$('.ta-session-modal-overlay').fadeOut(200);
		}
	});

	// Close hero modal
	$('.ta-hero-modal-close, .ta-hero-modal-overlay').on('click', function(e) {
		if (e.target === this || $(this).hasClass('ta-hero-modal-close')) {
			$('.ta-hero-modal-overlay').fadeOut(200);
		}
	});

	// Hero Modal Functions
	function openHeroModal(metric) {
		$('.ta-hero-modal-overlay').fadeIn(200);
		$('.ta-hero-loading').show();
		$('.ta-hero-content').hide();

		// Set titles based on metric
		var titles = {
			'total_visits': '<?php echo esc_js( __( 'Total Bot Visits - Activity Breakdown', 'third-audience' ) ); ?>',
			'pages_crawled': '<?php echo esc_js( __( 'Pages Crawled - Content Analysis', 'third-audience' ) ); ?>',
			'cache_hit_rate': '<?php echo esc_js( __( 'Cache Performance - Hit/Miss Analysis', 'third-audience' ) ); ?>',
			'avg_response': '<?php echo esc_js( __( 'Response Time - Performance Analysis', 'third-audience' ) ); ?>',
			'verified_bots': '<?php echo esc_js( __( 'Bot Verification - Status Breakdown', 'third-audience' ) ); ?>'
		};
		$('#ta-hero-modal-title').text(titles[metric] || '<?php echo esc_js( __( 'Metric Details', 'third-audience' ) ); ?>');

		loadHeroData(metric);
	}

	function loadHeroData(metric) {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'ta_get_hero_metric_details',
				nonce: taAnalyticsData.nonce,
				metric: metric
			},
			success: function(response) {
				if (response.success) {
					renderHeroData(metric, response.data);
				} else {
					alert('<?php echo esc_js( __( 'Failed to load metric data', 'third-audience' ) ); ?>');
					$('.ta-hero-modal-overlay').fadeOut(200);
				}
			},
			error: function() {
				alert('<?php echo esc_js( __( 'Error loading metric data', 'third-audience' ) ); ?>');
				$('.ta-hero-modal-overlay').fadeOut(200);
			}
		});
	}

	function renderHeroData(metric, data) {
		// Update summary stats
		$('#ta-hero-stat1').text(data.stats[0].value);
		$('#ta-hero-label1').text(data.stats[0].label);
		$('#ta-hero-stat2').text(data.stats[1].value);
		$('#ta-hero-label2').text(data.stats[1].label);
		$('#ta-hero-stat3').text(data.stats[2].value);
		$('#ta-hero-label3').text(data.stats[2].label);

		// Update chart title
		$('#ta-hero-chart-title').text(data.chart_title);
		$('#ta-hero-table-title').text(data.table_title);

		// Render chart
		renderHeroChart(data.chart_data, data.chart_type);

		// Render table
		renderHeroTable(data.table_headers, data.table_rows);

		$('.ta-hero-loading').hide();
		$('.ta-hero-content').show();
	}

	function renderHeroChart(chartData, chartType) {
		var ctx = document.getElementById('ta-hero-chart').getContext('2d');
		var colors = ['#007aff', '#34c759', '#ff9500', '#ff3b30', '#af52de', '#5856d6', '#00c7be', '#ff2d55', '#a2845e', '#8e8e93'];

		if (heroChart) {
			heroChart.destroy();
		}

		var config = {
			type: chartType || 'doughnut',
			data: {
				labels: chartData.labels,
				datasets: [{
					data: chartData.values,
					backgroundColor: colors.slice(0, chartData.labels.length),
					borderWidth: 0
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: true,
				plugins: {
					legend: {
						display: false
					},
					tooltip: {
						backgroundColor: 'rgba(0, 0, 0, 0.8)',
						padding: 10,
						cornerRadius: 6
					}
				}
			}
		};

		// For bar charts, adjust options
		if (chartType === 'bar') {
			config.options.plugins.legend.display = false;
			config.options.scales = {
				y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
				x: { grid: { display: false } }
			};
			config.data.datasets[0].borderRadius = 4;
		}

		heroChart = new Chart(ctx, config);

		// Render legend
		var legendHtml = '';
		chartData.labels.forEach(function(label, i) {
			var value = chartData.values[i];
			var percent = chartData.percentages ? chartData.percentages[i] : '';
			legendHtml += '<div style="display: flex; align-items: center; margin-bottom: 8px;">' +
				'<span style="width: 12px; height: 12px; border-radius: 3px; background: ' + colors[i] + '; margin-right: 8px;"></span>' +
				'<span style="flex: 1;">' + escapeHtml(label) + '</span>' +
				'<span style="font-weight: 600;">' + value + (percent ? ' (' + percent + ')' : '') + '</span>' +
			'</div>';
		});
		$('#ta-hero-chart-legend').html(legendHtml);
	}

	function renderHeroTable(headers, rows) {
		// Build header row
		var thead = '<tr>';
		headers.forEach(function(h) {
			var align = h.align ? ' style="text-align: ' + h.align + ';"' : '';
			thead += '<th' + align + '>' + escapeHtml(h.label) + '</th>';
		});
		thead += '</tr>';
		$('#ta-hero-thead').html(thead);

		// Build body rows
		var tbody = '';
		if (rows.length === 0) {
			tbody = '<tr><td colspan="' + headers.length + '" style="text-align: center; color: #646970; padding: 20px;"><?php echo esc_js( __( 'No data available', 'third-audience' ) ); ?></td></tr>';
		} else {
			rows.forEach(function(row) {
				tbody += '<tr>';
				row.forEach(function(cell, i) {
					var align = headers[i] && headers[i].align ? ' style="text-align: ' + headers[i].align + ';"' : '';
					tbody += '<td' + align + '>' + cell + '</td>';
				});
				tbody += '</tr>';
			});
		}
		$('#ta-hero-tbody').html(tbody);
	}

	// Sort change
	$('#ta-session-sort').on('change', function() {
		loadSessionData($(this).val());
	});

	function openSessionModal(metric) {
		$('.ta-session-modal-overlay').fadeIn(200);
		$('.ta-session-loading').show();
		$('.ta-session-content').hide();

		// Set title based on metric
		var titles = {
			'fingerprints': '<?php echo esc_js( __( 'Bot Fingerprints - All Unique Bot+IP Combinations', 'third-audience' ) ); ?>',
			'pages_per_session': '<?php echo esc_js( __( 'Pages Per Session - Crawl Depth Analysis', 'third-audience' ) ); ?>',
			'session_duration': '<?php echo esc_js( __( 'Session Duration - Time Spent Crawling', 'third-audience' ) ); ?>',
			'request_interval': '<?php echo esc_js( __( 'Request Interval - Time Between Requests', 'third-audience' ) ); ?>'
		};
		$('#ta-session-modal-title').text(titles[metric] || '<?php echo esc_js( __( 'Session Analytics Details', 'third-audience' ) ); ?>');

		// Set default sort based on metric
		var sortMap = {
			'fingerprints': 'last_seen',
			'pages_per_session': 'pages_per_session',
			'session_duration': 'session_duration',
			'request_interval': 'request_interval'
		};
		$('#ta-session-sort').val(sortMap[metric] || 'last_seen');

		loadSessionData(sortMap[metric] || 'last_seen');
	}

	function loadSessionData(sortBy) {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'ta_get_session_details',
				nonce: taAnalyticsData.nonce,
				sort_by: sortBy,
				order: 'DESC'
			},
			success: function(response) {
				if (response.success) {
					renderSessionData(response.data);
				} else {
					alert('<?php echo esc_js( __( 'Failed to load session data', 'third-audience' ) ); ?>');
				}
			},
			error: function() {
				alert('<?php echo esc_js( __( 'Error loading session data', 'third-audience' ) ); ?>');
			}
		});
	}

	function renderSessionData(data) {
		var summary = data.summary;
		var fingerprints = data.fingerprints;

		// Update summary stats
		$('#ta-modal-fingerprints').text(summary.total_bot_fingerprints);
		$('#ta-modal-pages').text(summary.avg_pages_per_session.toFixed(1));
		$('#ta-modal-duration').text(formatDuration(summary.avg_session_duration));
		$('#ta-modal-interval').text(formatDuration(summary.avg_request_interval));

		// Render table
		var tbody = $('#ta-session-tbody');
		tbody.empty();

		if (fingerprints.length === 0) {
			tbody.append('<tr><td colspan="7" class="ta-no-data"><?php echo esc_js( __( 'No session data yet', 'third-audience' ) ); ?></td></tr>');
		} else {
			fingerprints.forEach(function(fp) {
				var row = '<tr>' +
					'<td><span class="ta-bot-name">' + escapeHtml(fp.bot_type) + '</span>' +
					'<div style="font-size: 11px; color: #646970; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">' + escapeHtml(fp.user_agent_short) + '</div></td>' +
					'<td><code style="font-size: 11px;">' + escapeHtml(fp.ip_address) + '</code></td>' +
					'<td style="text-align: right;"><strong>' + fp.visit_count + '</strong></td>' +
					'<td style="text-align: right;">' + fp.pages_per_session_avg + '</td>' +
					'<td style="text-align: right;">' + fp.session_duration_human + '</td>' +
					'<td style="text-align: right;">' + fp.request_interval_human + '</td>' +
					'<td>' + fp.last_seen_human + '</td>' +
				'</tr>';
				tbody.append(row);
			});
		}

		// Render chart
		renderSessionChart(fingerprints);

		$('.ta-session-loading').hide();
		$('.ta-session-content').show();
	}

	function renderSessionChart(fingerprints) {
		var ctx = document.getElementById('ta-session-chart').getContext('2d');

		// Aggregate by bot type
		var botCounts = {};
		fingerprints.forEach(function(fp) {
			var botType = fp.bot_type || 'Unknown';
			botCounts[botType] = (botCounts[botType] || 0) + fp.visit_count;
		});

		var labels = Object.keys(botCounts);
		var values = Object.values(botCounts);
		var colors = ['#007aff', '#34c759', '#ff9500', '#ff3b30', '#af52de', '#5856d6', '#00c7be', '#ff2d55'];

		if (sessionChart) {
			sessionChart.destroy();
		}

		sessionChart = new Chart(ctx, {
			type: 'bar',
			data: {
				labels: labels,
				datasets: [{
					label: '<?php echo esc_js( __( 'Total Visits', 'third-audience' ) ); ?>',
					data: values,
					backgroundColor: colors.slice(0, labels.length),
					borderRadius: 4
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: { display: false }
				},
				scales: {
					y: { beginAtZero: true }
				}
			}
		});
	}

	function formatDuration(seconds) {
		if (seconds < 60) return seconds + 's';
		if (seconds < 3600) return (seconds / 60).toFixed(1) + ' min';
		return (seconds / 3600).toFixed(1) + ' hr';
	}

	function escapeHtml(text) {
		if (!text) return '';
		var div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}
});

// Activity Timeline Chart
(function() {
	function initTimelineChart() {
		var timelineData = <?php echo wp_json_encode( array_reverse( $visits_time ) ); ?>;

		if (timelineData && timelineData.length > 0) {
			var ctx = document.getElementById('ta-visits-chart');
			if (ctx) {
			var labels = timelineData.map(function(d) {
				// Format label based on period type
				var period = d.period;
				if (period.length === 10) { // Daily: 2026-01-23
					var parts = period.split('-');
					return parts[1] + '/' + parts[2];
				} else if (period.length === 7) { // Monthly: 2026-01
					var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
					var parts = period.split('-');
					return months[parseInt(parts[1], 10) - 1] + ' ' + parts[0];
				} else if (period.includes(':')) { // Hourly: 2026-01-23 14:00:00
					var parts = period.split(' ');
					var timePart = parts[1].split(':');
					return timePart[0] + ':00';
				}
				return period;
			});

			var visits = timelineData.map(function(d) { return parseInt(d.visits, 10); });
			var uniqueBots = timelineData.map(function(d) { return parseInt(d.unique_bots, 10); });

			new Chart(ctx, {
				type: 'line',
				data: {
					labels: labels,
					datasets: [
						{
							label: '<?php echo esc_js( __( 'Bot Visits', 'third-audience' ) ); ?>',
							data: visits,
							borderColor: '#007aff',
							backgroundColor: 'rgba(0, 122, 255, 0.1)',
							fill: true,
							tension: 0.3,
							borderWidth: 2,
							pointRadius: 4,
							pointHoverRadius: 6
						},
						{
							label: '<?php echo esc_js( __( 'Unique Bots', 'third-audience' ) ); ?>',
							data: uniqueBots,
							borderColor: '#34c759',
							backgroundColor: 'rgba(52, 199, 89, 0.1)',
							fill: true,
							tension: 0.3,
							borderWidth: 2,
							pointRadius: 4,
							pointHoverRadius: 6
						}
					]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					interaction: {
						intersect: false,
						mode: 'index'
					},
					plugins: {
						legend: {
							position: 'top',
							labels: {
								usePointStyle: true,
								padding: 15
							}
						},
						tooltip: {
							backgroundColor: 'rgba(0, 0, 0, 0.8)',
							padding: 12,
							titleFont: { size: 14 },
							bodyFont: { size: 13 },
							cornerRadius: 8
						}
					},
					scales: {
						y: {
							beginAtZero: true,
							ticks: {
								stepSize: 1,
								callback: function(value) {
									if (Number.isInteger(value)) return value;
								}
							},
							grid: {
								color: 'rgba(0, 0, 0, 0.05)'
							}
						},
						x: {
							grid: {
								display: false
							}
						}
					}
				}
			});
			}
		} else {
			// Show "no data" message if empty
			var chartContainer = document.getElementById('ta-visits-chart');
			if (chartContainer) {
				chartContainer.parentNode.innerHTML = '<div style="text-align: center; padding: 60px 20px; color: #646970;">' +
					'<span class="dashicons dashicons-chart-area" style="font-size: 48px; width: 48px; height: 48px; margin-bottom: 15px; opacity: 0.3;"></span>' +
					'<p style="margin: 0; font-size: 14px;"><?php echo esc_js( __( 'No activity data yet. Chart will appear when bots start visiting your site.', 'third-audience' ) ); ?></p>' +
				'</div>';
			}
		}
	}

	// Wait for Chart.js to load
	function waitForChart() {
		if (typeof Chart !== 'undefined') {
			initTimelineChart();
		} else {
			setTimeout(waitForChart, 100);
		}
	}
	waitForChart();
})();
</script>
