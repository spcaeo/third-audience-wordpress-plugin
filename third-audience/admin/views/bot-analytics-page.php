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
						<th><?php esc_html_e( 'Cache', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Response', 'third-audience' ); ?></th>
					</tr>
				</thead>
				<tbody id="ta-activity-tbody">
					<?php if ( empty( $recent_visits ) ) : ?>
						<tr>
							<td colspan="6" class="ta-no-data"><?php esc_html_e( 'No activity yet', 'third-audience' ); ?></td>
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
