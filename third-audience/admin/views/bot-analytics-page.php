<?php
/**
 * Bot Analytics Dashboard Page
 *
 * @package ThirdAudience
 * @since   1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$analytics = TA_Bot_Analytics::get_instance();

// Get filters from request.
$filters = array();
if ( ! empty( $_GET['bot_type'] ) ) {
	$filters['bot_type'] = sanitize_text_field( wp_unslash( $_GET['bot_type'] ) );
}
if ( ! empty( $_GET['post_type'] ) ) {
	$filters['post_type'] = sanitize_text_field( wp_unslash( $_GET['post_type'] ) );
}
if ( ! empty( $_GET['cache_status'] ) ) {
	$filters['cache_status'] = sanitize_text_field( wp_unslash( $_GET['cache_status'] ) );
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

// Get time period filter.
$time_period = isset( $_GET['period'] ) ? sanitize_text_field( wp_unslash( $_GET['period'] ) ) : 'day';

// Get data.
$summary     = $analytics->get_summary( $filters );
$bot_stats   = $analytics->get_visits_by_bot( $filters );
$top_pages   = $analytics->get_top_pages( $filters, 10 );
$visits_time = $analytics->get_visits_over_time( $filters, $time_period, 30 );

// Get current page for pagination.
$current_page = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$per_page     = 50;
$offset       = ( $current_page - 1 ) * $per_page;
$recent_visits = $analytics->get_recent_visits( $filters, $per_page, $offset );
?>

<div class="wrap ta-bot-analytics">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Bot Analytics', 'third-audience' ); ?>
		<span style="font-size: 0.6em; color: #646970; font-weight: 400;">v<?php echo esc_html( TA_VERSION ); ?></span>
	</h1>

	<p class="description">
		<?php esc_html_e( 'Track and analyze AI bot visits to your markdown content without requiring server logs.', 'third-audience' ); ?>
	</p>

	<!-- Live .md Access Feed -->
	<div class="ta-live-feed-widget">
		<div class="ta-live-feed-header">
			<div class="ta-live-feed-title">
				<span class="ta-live-pulse"></span>
				<h2><?php esc_html_e( 'Recent .md Accesses (Live)', 'third-audience' ); ?></h2>
			</div>
			<button type="button" class="ta-feed-toggle-btn" data-paused="false">
				<span class="dashicons dashicons-media-pause"></span>
				<span><?php esc_html_e( 'Pause', 'third-audience' ); ?></span>
			</button>
		</div>
		<div class="ta-live-feed-content">
			<table class="ta-live-feed-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Time', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'URL', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Bot', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Cache', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Response', 'third-audience' ); ?></th>
					</tr>
				</thead>
				<tbody id="ta-live-feed-tbody">
					<tr class="ta-feed-loading">
						<td colspan="5">
							<span class="ta-feed-spinner"></span>
							<?php esc_html_e( 'Loading...', 'third-audience' ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="ta-live-feed-footer">
			<span class="ta-feed-status"><?php esc_html_e( 'Auto-refreshing every 10 seconds', 'third-audience' ); ?></span>
		</div>
	</div>

	<!-- Filter Bar -->
	<div class="ta-analytics-filters">
		<form method="get" id="ta-analytics-filters-form">
			<input type="hidden" name="page" value="third-audience-bot-analytics">

			<div class="ta-filter-row">
				<div class="ta-filter-group">
					<label for="date_from"><?php esc_html_e( 'From', 'third-audience' ); ?></label>
					<input type="date" id="date_from" name="date_from" value="<?php echo esc_attr( $filters['date_from'] ?? '' ); ?>">
				</div>

				<div class="ta-filter-group">
					<label for="date_to"><?php esc_html_e( 'To', 'third-audience' ); ?></label>
					<input type="date" id="date_to" name="date_to" value="<?php echo esc_attr( $filters['date_to'] ?? '' ); ?>">
				</div>

				<div class="ta-filter-group">
					<label for="bot_type"><?php esc_html_e( 'Bot Type', 'third-audience' ); ?></label>
					<select id="bot_type" name="bot_type">
						<option value=""><?php esc_html_e( 'All Bots', 'third-audience' ); ?></option>
						<?php foreach ( TA_Bot_Analytics::get_known_bots() as $bot_type => $bot_info ) : ?>
							<option value="<?php echo esc_attr( $bot_type ); ?>" <?php selected( $filters['bot_type'] ?? '', $bot_type ); ?>>
								<?php echo esc_html( $bot_info['name'] ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="ta-filter-group">
					<label for="post_type"><?php esc_html_e( 'Post Type', 'third-audience' ); ?></label>
					<select id="post_type" name="post_type">
						<option value=""><?php esc_html_e( 'All Types', 'third-audience' ); ?></option>
						<?php
						$post_types = get_post_types( array( 'public' => true ), 'objects' );
						foreach ( $post_types as $post_type ) :
							?>
							<option value="<?php echo esc_attr( $post_type->name ); ?>" <?php selected( $filters['post_type'] ?? '', $post_type->name ); ?>>
								<?php echo esc_html( $post_type->label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="ta-filter-group">
					<label for="cache_status"><?php esc_html_e( 'Cache Status', 'third-audience' ); ?></label>
					<select id="cache_status" name="cache_status">
						<option value=""><?php esc_html_e( 'All', 'third-audience' ); ?></option>
						<option value="HIT" <?php selected( $filters['cache_status'] ?? '', 'HIT' ); ?>><?php esc_html_e( 'Hit', 'third-audience' ); ?></option>
						<option value="MISS" <?php selected( $filters['cache_status'] ?? '', 'MISS' ); ?>><?php esc_html_e( 'Miss', 'third-audience' ); ?></option>
						<option value="PRE_GENERATED" <?php selected( $filters['cache_status'] ?? '', 'PRE_GENERATED' ); ?>><?php esc_html_e( 'Pre-Generated', 'third-audience' ); ?></option>
					</select>
				</div>

				<div class="ta-filter-group">
					<label for="search"><?php esc_html_e( 'Search', 'third-audience' ); ?></label>
					<input type="text" id="search" name="search" placeholder="<?php esc_attr_e( 'URL, title, or user agent...', 'third-audience' ); ?>" value="<?php echo esc_attr( $filters['search'] ?? '' ); ?>">
				</div>

				<div class="ta-filter-actions">
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Apply Filters', 'third-audience' ); ?></button>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-bot-analytics' ) ); ?>" class="button">
						<?php esc_html_e( 'Reset Filters', 'third-audience' ); ?>
					</a>
					<div class="ta-export-dropdown">
						<button type="button" class="button ta-export-dropdown-toggle">
							<span class="dashicons dashicons-download"></span>
							<?php esc_html_e( 'Export', 'third-audience' ); ?>
							<span class="dashicons dashicons-arrow-down-alt2"></span>
						</button>
						<div class="ta-export-dropdown-menu">
							<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array_merge( $_GET, array( 'action' => 'export', 'export_format' => 'csv', 'export_type' => 'detailed' ) ) ), 'ta_export_analytics' ) ); ?>" class="ta-export-option">
								<span class="dashicons dashicons-media-spreadsheet"></span>
								<div>
									<strong><?php esc_html_e( 'CSV - Detailed', 'third-audience' ); ?></strong>
									<span><?php esc_html_e( 'All columns with visit details', 'third-audience' ); ?></span>
								</div>
							</a>
							<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array_merge( $_GET, array( 'action' => 'export', 'export_format' => 'csv', 'export_type' => 'summary' ) ) ), 'ta_export_analytics' ) ); ?>" class="ta-export-option">
								<span class="dashicons dashicons-chart-bar"></span>
								<div>
									<strong><?php esc_html_e( 'CSV - Summary', 'third-audience' ); ?></strong>
									<span><?php esc_html_e( 'Aggregated stats by bot type', 'third-audience' ); ?></span>
								</div>
							</a>
							<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array_merge( $_GET, array( 'action' => 'export', 'export_format' => 'json', 'export_type' => 'detailed' ) ) ), 'ta_export_analytics' ) ); ?>" class="ta-export-option">
								<span class="dashicons dashicons-media-code"></span>
								<div>
									<strong><?php esc_html_e( 'JSON - Detailed', 'third-audience' ); ?></strong>
									<span><?php esc_html_e( 'All data in JSON format', 'third-audience' ); ?></span>
								</div>
							</a>
							<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array_merge( $_GET, array( 'action' => 'export', 'export_format' => 'json', 'export_type' => 'summary' ) ) ), 'ta_export_analytics' ) ); ?>" class="ta-export-option">
								<span class="dashicons dashicons-analytics"></span>
								<div>
									<strong><?php esc_html_e( 'JSON - Summary', 'third-audience' ); ?></strong>
									<span><?php esc_html_e( 'Aggregated stats in JSON', 'third-audience' ); ?></span>
								</div>
							</a>
						</div>
					</div>
					<button type="button" class="button button-secondary ta-clear-all-visits" style="margin-left: 10px; color: #d63638; border-color: #d63638;">
						<?php esc_html_e( 'Clear All Visits', 'third-audience' ); ?>
					</button>
				</div>
			</div>
		</form>
	</div>

	<!-- Summary Cards -->
	<div class="ta-summary-cards">
		<div class="ta-summary-card">
			<div class="ta-summary-icon">
				<span class="dashicons dashicons-admin-users"></span>
			</div>
			<div class="ta-summary-content">
				<h3><?php echo number_format( $summary['total_visits'] ); ?></h3>
				<p><?php esc_html_e( 'Total Bot Visits', 'third-audience' ); ?></p>
				<span class="ta-summary-meta">
					<?php
					printf(
						/* translators: %d: number of visits today */
						esc_html__( '%d today', 'third-audience' ),
						$summary['visits_today']
					);
					?>
					<?php if ( $summary['trend_percentage'] != 0 ) : ?>
						<span class="ta-trend <?php echo $summary['trend_percentage'] > 0 ? 'ta-trend-up' : 'ta-trend-down'; ?>">
							<?php echo $summary['trend_percentage'] > 0 ? '↑' : '↓'; ?>
							<?php echo abs( $summary['trend_percentage'] ); ?>%
						</span>
					<?php endif; ?>
				</span>
			</div>
		</div>

		<div class="ta-summary-card">
			<div class="ta-summary-icon">
				<span class="dashicons dashicons-admin-page"></span>
			</div>
			<div class="ta-summary-content">
				<h3><?php echo number_format( $summary['unique_pages'] ); ?></h3>
				<p><?php esc_html_e( 'Unique Pages Crawled', 'third-audience' ); ?></p>
				<span class="ta-summary-meta">
					<?php
					printf(
						/* translators: %d: number of unique bots */
						esc_html__( 'By %d bots', 'third-audience' ),
						$summary['unique_bots']
					);
					?>
				</span>
			</div>
		</div>

		<div class="ta-summary-card">
			<div class="ta-summary-icon">
				<span class="dashicons dashicons-performance"></span>
			</div>
			<div class="ta-summary-content">
				<h3><?php echo $summary['cache_hit_rate']; ?>%</h3>
				<p><?php esc_html_e( 'Cache Hit Rate', 'third-audience' ); ?></p>
				<span class="ta-summary-meta">
					<?php
					printf(
						/* translators: %d: average response time */
						esc_html__( 'Avg: %dms', 'third-audience' ),
						$summary['avg_response_time']
					);
					?>
				</span>
			</div>
		</div>

		<div class="ta-summary-card">
			<div class="ta-summary-icon">
				<span class="dashicons dashicons-cloud"></span>
			</div>
			<div class="ta-summary-content">
				<h3><?php echo size_format( $summary['total_bandwidth'], 2 ); ?></h3>
				<p><?php esc_html_e( 'Total Bandwidth', 'third-audience' ); ?></p>
				<span class="ta-summary-meta">
					<?php
					printf(
						/* translators: %s: bandwidth per visit */
						esc_html__( '%s per visit', 'third-audience' ),
						$summary['total_visits'] > 0 ? size_format( $summary['total_bandwidth'] / $summary['total_visits'], 2 ) : '0 B'
					);
					?>
				</span>
			</div>
		</div>
	</div>

	<!-- Charts Row -->
	<div class="ta-charts-row">
		<!-- Visits Over Time Chart -->
		<div class="ta-chart-card ta-chart-full">
			<div class="ta-chart-header">
				<h2><?php esc_html_e( 'Visits Over Time', 'third-audience' ); ?></h2>
				<div class="ta-chart-controls">
					<select id="ta-period-selector" onchange="location.href='<?php echo esc_url( admin_url( 'admin.php?page=third-audience-bot-analytics&period=' ) ); ?>'+this.value">
						<option value="hour" <?php selected( $time_period, 'hour' ); ?>><?php esc_html_e( 'Hourly', 'third-audience' ); ?></option>
						<option value="day" <?php selected( $time_period, 'day' ); ?>><?php esc_html_e( 'Daily', 'third-audience' ); ?></option>
						<option value="week" <?php selected( $time_period, 'week' ); ?>><?php esc_html_e( 'Weekly', 'third-audience' ); ?></option>
						<option value="month" <?php selected( $time_period, 'month' ); ?>><?php esc_html_e( 'Monthly', 'third-audience' ); ?></option>
					</select>
				</div>
			</div>
			<div class="ta-chart-body">
				<canvas id="ta-visits-chart"></canvas>
			</div>
		</div>
	</div>

	<div class="ta-charts-row">
		<!-- Bot Distribution Chart -->
		<div class="ta-chart-card ta-chart-half">
			<div class="ta-chart-header">
				<h2><?php esc_html_e( 'Bot Distribution', 'third-audience' ); ?></h2>
			</div>
			<div class="ta-chart-body">
				<canvas id="ta-bot-distribution-chart"></canvas>
			</div>
			<div class="ta-chart-legend">
				<?php
				$bot_analytics_inst = TA_Bot_Analytics::get_instance();
				$priority_colors_map = array(
					'high'    => '#0073aa',
					'medium'  => '#46b450',
					'low'     => '#ffb900',
					'blocked' => '#dc3232',
				);
				?>
				<?php foreach ( $bot_stats as $bot ) : ?>
					<?php
					$bot_priority_val = $bot_analytics_inst->get_bot_priority( $bot['bot_type'], 'medium' );
					$priority_badge_color = isset( $priority_colors_map[ $bot_priority_val ] ) ? $priority_colors_map[ $bot_priority_val ] : '#999';
					?>
					<div class="ta-legend-item">
						<span class="ta-legend-color" style="background-color: <?php echo esc_attr( $bot['color'] ); ?>"></span>
						<span class="ta-legend-label">
							<?php echo esc_html( $bot['bot_name'] ); ?>
							<span style="background: <?php echo esc_attr( $priority_badge_color ); ?>; color: white; padding: 1px 5px; border-radius: 3px; font-size: 9px; margin-left: 4px; text-transform: uppercase; font-weight: 600;">
								<?php echo esc_html( $bot_priority_val ); ?>
							</span>
						</span>
						<span class="ta-legend-value"><?php echo number_format( $bot['count'] ); ?> visits</span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Top Pages -->
		<div class="ta-chart-card ta-chart-half">
			<div class="ta-chart-header">
				<h2><?php esc_html_e( 'Top Crawled Pages', 'third-audience' ); ?></h2>
			</div>
			<div class="ta-chart-body">
				<table class="ta-top-pages-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Page', 'third-audience' ); ?></th>
							<th><?php esc_html_e( 'Type', 'third-audience' ); ?></th>
							<th><?php esc_html_e( 'Visits', 'third-audience' ); ?></th>
							<th><?php esc_html_e( 'Bots', 'third-audience' ); ?></th>
							<th><?php esc_html_e( 'Avg Time', 'third-audience' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $top_pages ) ) : ?>
							<tr>
								<td colspan="5" class="ta-no-data">
									<?php esc_html_e( 'No data available', 'third-audience' ); ?>
								</td>
							</tr>
						<?php else : ?>
							<?php foreach ( $top_pages as $page ) : ?>
								<tr>
									<td class="ta-page-title">
										<a href="<?php echo esc_url( $page['url'] ); ?>" target="_blank" title="<?php echo esc_attr( $page['url'] ); ?>">
											<?php echo esc_html( wp_trim_words( $page['post_title'] ?? $page['url'], 8 ) ); ?>
										</a>
									</td>
									<td><?php echo esc_html( $page['post_type'] ?? 'N/A' ); ?></td>
									<td><strong><?php echo number_format( $page['visits'] ); ?></strong></td>
									<td><?php echo number_format( $page['unique_bots'] ); ?></td>
									<td><?php echo round( $page['avg_response_time'] ); ?>ms</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<!-- Rate Limit Violations -->
	<?php
	$rate_limiter = new TA_Rate_Limiter();
	$violation_stats = $rate_limiter->get_violation_stats();
	$recent_violations = $rate_limiter->get_rate_limit_violations( 10 );
	?>

	<?php if ( ! empty( $violation_stats ) || ! empty( $recent_violations ) ) : ?>
		<div class="ta-charts-row" style="margin-top: 30px;">
			<div class="ta-chart-card ta-chart-full">
				<div class="ta-chart-header">
					<h2><?php esc_html_e( 'Rate Limit Violations', 'third-audience' ); ?></h2>
				</div>
				<div class="ta-chart-body">
					<?php if ( ! empty( $violation_stats ) ) : ?>
						<h3 style="margin: 0 0 15px 0; font-size: 14px;"><?php esc_html_e( 'Violations by Bot Type', 'third-audience' ); ?></h3>
						<table class="wp-list-table widefat fixed striped">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Bot Name', 'third-audience' ); ?></th>
									<th><?php esc_html_e( 'Bot Type', 'third-audience' ); ?></th>
									<th><?php esc_html_e( 'Total Violations', 'third-audience' ); ?></th>
									<th><?php esc_html_e( 'Unique IPs', 'third-audience' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $violation_stats as $stat ) : ?>
									<tr>
										<td><strong><?php echo esc_html( $stat['bot_name'] ); ?></strong></td>
										<td><code><?php echo esc_html( $stat['bot_type'] ); ?></code></td>
										<td><span style="color: #dc3232; font-weight: 600;"><?php echo number_format_i18n( $stat['violations'] ); ?></span></td>
										<td><?php echo number_format_i18n( $stat['unique_ips'] ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>

					<?php if ( ! empty( $recent_violations ) ) : ?>
						<h3 style="margin: 20px 0 15px 0; font-size: 14px;"><?php esc_html_e( 'Recent Rate Limit Violations', 'third-audience' ); ?></h3>
						<table class="wp-list-table widefat fixed striped">
							<thead>
								<tr>
									<th style="width: 130px;"><?php esc_html_e( 'Bot', 'third-audience' ); ?></th>
									<th><?php esc_html_e( 'URL', 'third-audience' ); ?></th>
									<th style="width: 120px;"><?php esc_html_e( 'IP Address', 'third-audience' ); ?></th>
									<th style="width: 100px;"><?php esc_html_e( 'Country', 'third-audience' ); ?></th>
									<th style="width: 150px;"><?php esc_html_e( 'Time', 'third-audience' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $recent_violations as $violation ) : ?>
									<tr>
										<td><strong><?php echo esc_html( $violation['bot_name'] ); ?></strong></td>
										<td><code style="font-size: 11px;"><?php echo esc_html( wp_trim_words( $violation['url'], 10 ) ); ?></code></td>
										<td><?php echo esc_html( $violation['ip_address'] ?: 'N/A' ); ?></td>
										<td><?php echo esc_html( $violation['country_code'] ?: '—' ); ?></td>
										<td><?php echo esc_html( human_time_diff( strtotime( $violation['visit_timestamp'] ), current_time( 'timestamp' ) ) ); ?> <?php esc_html_e( 'ago', 'third-audience' ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>

					<?php if ( empty( $violation_stats ) && empty( $recent_violations ) ) : ?>
						<p style="text-align: center; padding: 40px; color: #666;">
							<?php esc_html_e( 'No rate limit violations recorded.', 'third-audience' ); ?>
						</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<!-- Recent Visits Table -->
	<div class="ta-recent-visits">
		<div class="ta-section-header">
			<h2><?php esc_html_e( 'Recent Bot Visits', 'third-audience' ); ?></h2>
			<button type="button" class="button button-secondary ta-cache-help-toggle">
				<span class="dashicons dashicons-info-outline"></span>
				<?php esc_html_e( 'Cache Status Guide', 'third-audience' ); ?>
			</button>
		</div>

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th style="width: 80px;"><?php esc_html_e( 'ID', 'third-audience' ); ?></th>
					<th style="width: 130px;"><?php esc_html_e( 'Bot', 'third-audience' ); ?></th>
					<th><?php esc_html_e( 'Page', 'third-audience' ); ?></th>
					<th style="width: 100px;"><?php esc_html_e( 'Type', 'third-audience' ); ?></th>
					<th style="width: 80px;"><?php esc_html_e( 'Country', 'third-audience' ); ?></th>
					<th style="width: 100px;"><?php esc_html_e( 'Cache', 'third-audience' ); ?></th>
					<th style="width: 100px;"><?php esc_html_e( 'Response', 'third-audience' ); ?></th>
					<th style="width: 150px;"><?php esc_html_e( 'Time', 'third-audience' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $recent_visits ) ) : ?>
					<tr>
						<td colspan="8" class="ta-no-data">
							<?php esc_html_e( 'No bot visits recorded yet.', 'third-audience' ); ?>
						</td>
					</tr>
				<?php else : ?>
					<?php foreach ( $recent_visits as $visit ) : ?>
						<?php
						// Get bot priority for color coding.
						$bot_analytics_instance = TA_Bot_Analytics::get_instance();
						$bot_priority = $bot_analytics_instance->get_bot_priority( $visit['bot_type'], 'medium' );
						$priority_colors = array(
							'high'    => '#0073aa',
							'medium'  => '#46b450',
							'low'     => '#ffb900',
							'blocked' => '#dc3232',
						);
						$priority_color = isset( $priority_colors[ $bot_priority ] ) ? $priority_colors[ $bot_priority ] : '#999';
						?>
						<tr>
							<td><?php echo esc_html( $visit['id'] ); ?></td>
							<td>
								<span class="ta-bot-badge" style="border-left-color: <?php echo esc_attr( $priority_color ); ?>; border-left-width: 4px;">
									<?php echo esc_html( $visit['bot_name'] ); ?>
									<span class="ta-priority-indicator" style="background: <?php echo esc_attr( $priority_color ); ?>; color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px; margin-left: 6px; text-transform: uppercase; font-weight: 600;">
										<?php echo esc_html( $bot_priority ); ?>
									</span>
								</span>
							</td>
							<td class="ta-page-cell">
								<a href="<?php echo esc_url( $visit['url'] ); ?>" target="_blank" title="<?php echo esc_attr( $visit['url'] ); ?>">
									<?php echo esc_html( wp_trim_words( $visit['post_title'] ?? $visit['url'], 10 ) ); ?>
								</a>
							</td>
							<td><?php echo esc_html( $visit['post_type'] ?? 'N/A' ); ?></td>
							<td>
								<?php if ( ! empty( $visit['country_code'] ) ) : ?>
									<span class="ta-country-badge" title="<?php echo esc_attr( $visit['ip_address'] ?? 'Unknown IP' ); ?>">
										<?php echo esc_html( $visit['country_code'] ); ?>
									</span>
								<?php else : ?>
									<span class="ta-country-badge" title="Unknown location">-</span>
								<?php endif; ?>
							</td>
							<td>
								<span class="ta-cache-badge ta-cache-<?php echo esc_attr( strtolower( $visit['cache_status'] ) ); ?>">
									<?php echo esc_html( $visit['cache_status'] ); ?>
								</span>
							</td>
							<td>
								<?php if ( $visit['response_time'] ) : ?>
									<?php echo esc_html( $visit['response_time'] ); ?>ms
								<?php else : ?>
									-
								<?php endif; ?>
							</td>
							<td>
								<span title="<?php echo esc_attr( $visit['visit_timestamp'] ); ?>">
									<?php echo esc_html( human_time_diff( strtotime( $visit['visit_timestamp'] ), current_time( 'timestamp' ) ) ); ?> ago
								</span>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>

		<!-- Pagination -->
		<?php if ( $summary['total_visits'] > $per_page ) : ?>
			<div class="tablenav">
				<div class="tablenav-pages">
					<?php
					$total_pages = ceil( $summary['total_visits'] / $per_page );
					$pagination = paginate_links( array(
						'base'      => add_query_arg( 'paged', '%#%' ),
						'format'    => '',
						'current'   => $current_page,
						'total'     => $total_pages,
						'prev_text' => '&laquo;',
						'next_text' => '&raquo;',
					) );
					echo wp_kses_post( $pagination );
					?>
				</div>
			</div>
		<?php endif; ?>
	</div>

	<!-- Pass data to JavaScript -->
	<script type="text/javascript">
		var taAnalyticsData = {
			visitsOverTime: <?php echo wp_json_encode( array_reverse( $visits_time ) ); ?>,
			botDistribution: <?php echo wp_json_encode( $bot_stats ); ?>,
			period: <?php echo wp_json_encode( $time_period ); ?>,
			nonce: <?php echo wp_json_encode( wp_create_nonce( 'ta_bot_analytics' ) ); ?>,
			feedNonce: <?php echo wp_json_encode( wp_create_nonce( 'ta_bot_analytics_feed' ) ); ?>
		};
	</script>
</div>

<!-- Cache Status Guide Modal -->
<div class="ta-cache-modal-overlay" style="display: none;">
	<div class="ta-cache-modal">
		<div class="ta-cache-modal-header">
			<h2><?php esc_html_e( 'Understanding Cache Status', 'third-audience' ); ?></h2>
			<button type="button" class="ta-cache-modal-close" aria-label="<?php esc_attr_e( 'Close', 'third-audience' ); ?>">
				<span class="dashicons dashicons-no-alt"></span>
			</button>
		</div>
		<div class="ta-cache-modal-body">
			<p class="ta-cache-modal-intro"><?php esc_html_e( 'Learn what each cache status means and how to optimize performance.', 'third-audience' ); ?></p>

			<div class="ta-cache-status-grid">
				<div class="ta-cache-status-card ta-cache-card-success">
					<div class="ta-cache-card-icon">
						<span class="dashicons dashicons-yes-alt"></span>
					</div>
					<div class="ta-cache-card-content">
						<div class="ta-cache-badge ta-cache-hit">HIT</div>
						<h4><?php esc_html_e( 'Cache Hit', 'third-audience' ); ?></h4>
						<p><?php esc_html_e( 'Content served from transient cache. Fast response! Cache was already available from a previous request.', 'third-audience' ); ?></p>
						<div class="ta-cache-performance">
							<span class="dashicons dashicons-performance"></span>
							<span><?php esc_html_e( 'Fast (1-5ms)', 'third-audience' ); ?></span>
						</div>
					</div>
				</div>

				<div class="ta-cache-status-card ta-cache-card-success">
					<div class="ta-cache-card-icon">
						<span class="dashicons dashicons-dashboard"></span>
					</div>
					<div class="ta-cache-card-content">
						<div class="ta-cache-badge ta-cache-pre_generated">PRE_GENERATED</div>
						<h4><?php esc_html_e( 'Pre-generated Cache', 'third-audience' ); ?></h4>
						<p><?php esc_html_e( 'Content served from pre-generated post meta. Fastest response! Cache was created during post save or warmup.', 'third-audience' ); ?></p>
						<div class="ta-cache-performance">
							<span class="dashicons dashicons-star-filled"></span>
							<span><?php esc_html_e( 'Fastest (<1ms)', 'third-audience' ); ?></span>
						</div>
					</div>
				</div>

				<div class="ta-cache-status-card ta-cache-card-warning">
					<div class="ta-cache-card-icon">
						<span class="dashicons dashicons-info"></span>
					</div>
					<div class="ta-cache-card-content">
						<div class="ta-cache-badge ta-cache-miss">MISS</div>
						<h4><?php esc_html_e( 'Cache Miss', 'third-audience' ); ?></h4>
						<p><?php esc_html_e( 'Content generated fresh. Slower response. Happens on first visit, after cache expiry, or when cache was cleared.', 'third-audience' ); ?></p>
						<div class="ta-cache-performance">
							<span class="dashicons dashicons-clock"></span>
							<span><?php esc_html_e( 'Slow (10-50ms)', 'third-audience' ); ?></span>
						</div>
						<div class="ta-cache-tip">
							<span class="dashicons dashicons-lightbulb"></span>
							<span>
								<?php
								printf(
									/* translators: %s: link to cache browser */
									esc_html__( 'Reduce MISS by running %s', 'third-audience' ),
									'<a href="' . esc_url( admin_url( 'admin.php?page=third-audience-cache-browser' ) ) . '">' . esc_html__( 'Cache Warmup', 'third-audience' ) . '</a>'
								);
								?>
							</span>
						</div>
					</div>
				</div>

				<div class="ta-cache-status-card ta-cache-card-error">
					<div class="ta-cache-card-icon">
						<span class="dashicons dashicons-warning"></span>
					</div>
					<div class="ta-cache-card-content">
						<div class="ta-cache-badge ta-cache-failed">FAILED</div>
						<h4><?php esc_html_e( 'Generation Failed', 'third-audience' ); ?></h4>
						<p><?php esc_html_e( 'Content conversion failed. Check System Health for issues with the HTML-to-Markdown converter.', 'third-audience' ); ?></p>
						<div class="ta-cache-performance">
							<span class="dashicons dashicons-dismiss"></span>
							<span><?php esc_html_e( 'Error', 'third-audience' ); ?></span>
						</div>
						<div class="ta-cache-tip ta-cache-tip-error">
							<span class="dashicons dashicons-sos"></span>
							<span>
								<?php
								printf(
									/* translators: %s: link to system health */
									esc_html__( 'Review %s for errors', 'third-audience' ),
									'<a href="' . esc_url( admin_url( 'admin.php?page=third-audience-system-health' ) ) . '">' . esc_html__( 'System Health', 'third-audience' ) . '</a>'
								);
								?>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
