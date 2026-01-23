<?php
/**
 * AI Citations - Analytics dashboard for AI platform citation traffic
 *
 * @package ThirdAudience
 * @since   2.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'ta_bot_analytics';

// Get filters.
$filters = array();
if ( ! empty( $_GET['platform'] ) ) {
	$filters['platform'] = sanitize_text_field( wp_unslash( $_GET['platform'] ) );
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

// Build WHERE clause based on filters.
$where_clauses = array( "traffic_type = 'citation_click'" );

if ( ! empty( $filters['platform'] ) ) {
	$where_clauses[] = $wpdb->prepare( 'ai_platform = %s', $filters['platform'] );
}

if ( ! empty( $filters['date_from'] ) ) {
	$where_clauses[] = $wpdb->prepare( 'DATE(visit_timestamp) >= %s', $filters['date_from'] );
}

if ( ! empty( $filters['date_to'] ) ) {
	$where_clauses[] = $wpdb->prepare( 'DATE(visit_timestamp) <= %s', $filters['date_to'] );
}

if ( ! empty( $filters['search'] ) ) {
	$search_term = '%' . $wpdb->esc_like( $filters['search'] ) . '%';
	$where_clauses[] = $wpdb->prepare( '(url LIKE %s OR post_title LIKE %s OR search_query LIKE %s)', $search_term, $search_term, $search_term );
}

$where_sql = implode( ' AND ', $where_clauses );

// Summary stats for citation traffic.
$total_citations = $wpdb->get_var(
	"SELECT COUNT(*) FROM {$table_name} WHERE {$where_sql}"
);

$citations_today = $wpdb->get_var(
	$wpdb->prepare(
		"SELECT COUNT(*) FROM {$table_name} WHERE {$where_sql} AND DATE(visit_timestamp) = %s",
		current_time( 'Y-m-d' )
	)
);

$unique_platforms = $wpdb->get_var(
	"SELECT COUNT(DISTINCT ai_platform) FROM {$table_name} WHERE {$where_sql} AND ai_platform IS NOT NULL"
);

$queries_captured = $wpdb->get_var(
	"SELECT COUNT(*) FROM {$table_name} WHERE {$where_sql} AND search_query IS NOT NULL"
);

// Overall Citation Rate (v2.7.0) - Citations vs Crawls.
// Build WHERE clause without traffic_type filter for counting crawls.
$crawl_where_clauses = array();
foreach ( $where_clauses as $clause ) {
	if ( strpos( $clause, 'traffic_type' ) === false ) {
		$crawl_where_clauses[] = $clause;
	}
}
$crawl_where_sql = empty( $crawl_where_clauses ) ? '1=1' : implode( ' AND ', $crawl_where_clauses );
$total_crawls = $wpdb->get_var(
	"SELECT COUNT(*) FROM {$table_name} WHERE {$crawl_where_sql} AND traffic_type = 'bot_crawl'"
);
$overall_citation_rate = $total_crawls > 0 ? round( ( $total_citations / $total_crawls ) * 100, 1 ) : 0;

// Get all available platforms for filter dropdown.
$available_platforms = $wpdb->get_col(
	"SELECT DISTINCT ai_platform FROM {$table_name} WHERE traffic_type = 'citation_click' AND ai_platform IS NOT NULL ORDER BY ai_platform"
);

// Citations by platform with date info.
$citations_by_platform = $wpdb->get_results(
	"SELECT
		ai_platform,
		COUNT(*) as count,
		COUNT(CASE WHEN search_query IS NOT NULL THEN 1 END) as queries_captured,
		MAX(visit_timestamp) as last_citation,
		MIN(visit_timestamp) as first_citation
	FROM {$table_name}
	WHERE {$where_sql} AND ai_platform IS NOT NULL
	GROUP BY ai_platform
	ORDER BY count DESC",
	ARRAY_A
);

// Recent search queries.
$recent_queries = $wpdb->get_results(
	"SELECT
		ai_platform,
		search_query,
		url,
		visit_timestamp
	FROM {$table_name}
	WHERE {$where_sql} AND search_query IS NOT NULL
	ORDER BY visit_timestamp DESC
	LIMIT 20",
	ARRAY_A
);

// Top cited pages with date info.
$top_cited_pages = $wpdb->get_results(
	"SELECT
		url,
		post_title,
		COUNT(*) as citation_count,
		COUNT(DISTINCT ai_platform) as platforms,
		MAX(visit_timestamp) as last_cited,
		MIN(visit_timestamp) as first_cited
	FROM {$table_name}
	WHERE {$where_sql}
	GROUP BY url, post_title
	ORDER BY citation_count DESC
	LIMIT 10",
	ARRAY_A
);

// Recent citations (ALL - not just those with queries).
$recent_citations = $wpdb->get_results(
	"SELECT
		ai_platform,
		url,
		post_title,
		search_query,
		referer,
		visit_timestamp
	FROM {$table_name}
	WHERE {$where_sql}
	ORDER BY visit_timestamp DESC
	LIMIT 30",
	ARRAY_A
);

// === CHART DATA (v3.2.1) ===

// Daily citations for last 30 days (for trend chart).
$daily_citations = $wpdb->get_results(
	"SELECT
		DATE(visit_timestamp) as date,
		COUNT(*) as citations
	FROM {$table_name}
	WHERE traffic_type = 'citation_click'
		AND visit_timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
	GROUP BY DATE(visit_timestamp)
	ORDER BY date ASC",
	ARRAY_A
);

// Daily crawls for last 30 days (for comparison chart).
$daily_crawls = $wpdb->get_results(
	"SELECT
		DATE(visit_timestamp) as date,
		COUNT(*) as crawls
	FROM {$table_name}
	WHERE traffic_type = 'bot_crawl'
		AND visit_timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
	GROUP BY DATE(visit_timestamp)
	ORDER BY date ASC",
	ARRAY_A
);

// Build chart data arrays.
$chart_labels      = array();
$chart_citations   = array();
$chart_crawls      = array();
$citations_by_date = array();
$crawls_by_date    = array();

// Index by date for easy lookup.
foreach ( $daily_citations as $row ) {
	$citations_by_date[ $row['date'] ] = (int) $row['citations'];
}
foreach ( $daily_crawls as $row ) {
	$crawls_by_date[ $row['date'] ] = (int) $row['crawls'];
}

// Generate labels for last 30 days.
for ( $i = 29; $i >= 0; $i-- ) {
	$date            = gmdate( 'Y-m-d', strtotime( "-{$i} days" ) );
	$chart_labels[]  = gmdate( 'M j', strtotime( $date ) );
	$chart_citations[] = isset( $citations_by_date[ $date ] ) ? $citations_by_date[ $date ] : 0;
	$chart_crawls[]    = isset( $crawls_by_date[ $date ] ) ? $crawls_by_date[ $date ] : 0;
}

// Platform chart data (for pie/doughnut chart).
$platform_labels = array();
$platform_data   = array();
$platform_colors = array(
	'#007aff', // Blue
	'#34c759', // Green
	'#ff9500', // Orange
	'#ff3b30', // Red
	'#5856d6', // Purple
	'#af52de', // Magenta
	'#00c7be', // Teal
	'#ff2d55', // Pink
);

foreach ( $citations_by_platform as $index => $platform ) {
	$platform_labels[] = $platform['ai_platform'];
	$platform_data[]   = (int) $platform['count'];
}

// Weekly comparison data (last 4 weeks).
$weekly_data = array();
for ( $week = 3; $week >= 0; $week-- ) {
	$week_start = gmdate( 'Y-m-d', strtotime( "-{$week} weeks monday" ) );
	$week_end   = gmdate( 'Y-m-d', strtotime( "-{$week} weeks sunday" ) );

	$week_citations = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$table_name}
			WHERE traffic_type = 'citation_click'
			AND DATE(visit_timestamp) BETWEEN %s AND %s",
			$week_start,
			$week_end
		)
	);

	$week_crawls = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$table_name}
			WHERE traffic_type = 'bot_crawl'
			AND DATE(visit_timestamp) BETWEEN %s AND %s",
			$week_start,
			$week_end
		)
	);

	$weekly_data[] = array(
		'label'     => 'Week of ' . gmdate( 'M j', strtotime( $week_start ) ),
		'citations' => (int) $week_citations,
		'crawls'    => (int) $week_crawls,
	);
}
?>

<div class="wrap ta-bot-analytics">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'AI Citations', 'third-audience' ); ?>
		<span style="font-size: 0.6em; color: #646970; font-weight: 400;">v<?php echo esc_html( TA_VERSION ); ?></span>
	</h1>
	<p class="description"><?php esc_html_e( 'Track citation clicks from ChatGPT, Perplexity, Claude, and other AI platforms', 'third-audience' ); ?></p>

	<?php if ( 0 === (int) $total_citations ) : ?>
	<!-- Getting Started / Debug Card -->
	<div class="ta-card" style="margin-top: 20px; background: linear-gradient(135deg, #fef3c7 0%, #fef9c3 100%); border-left: 4px solid #f59e0b;">
		<div class="ta-card-body" style="padding: 24px;">
			<h3 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 600; color: #92400e;">
				<span class="dashicons dashicons-info-outline" style="margin-right: 8px;"></span>
				<?php esc_html_e( 'No Citations Detected Yet', 'third-audience' ); ?>
			</h3>
			<p style="margin: 0 0 16px 0; color: #78350f;">
				<?php esc_html_e( 'Citation tracking will capture clicks when users visit your site from AI platforms.', 'third-audience' ); ?>
			</p>
			<div style="background: rgba(255,255,255,0.7); padding: 16px; border-radius: 6px; margin-bottom: 16px;">
				<strong style="display: block; margin-bottom: 8px; color: #92400e;"><?php esc_html_e( 'How to Test Citation Tracking:', 'third-audience' ); ?></strong>
				<ol style="margin: 0 0 0 20px; padding: 0; color: #78350f; line-height: 1.8;">
					<li><?php printf( esc_html__( 'Open a new incognito/private browser window', 'third-audience' ) ); ?></li>
					<li><?php printf( esc_html__( 'Visit any page on your site with a UTM parameter: %s', 'third-audience' ), '<code style="background: #fef3c7; padding: 2px 6px; border-radius: 3px;">?utm_source=chatgpt.com</code>' ); ?></li>
					<li><?php printf( esc_html__( 'Example: %s', 'third-audience' ), '<code style="background: #fef3c7; padding: 2px 6px; border-radius: 3px;">' . esc_html( home_url( '/?utm_source=chatgpt.com' ) ) . '</code>' ); ?></li>
					<li><?php esc_html_e( 'Refresh this page to see the citation appear', 'third-audience' ); ?></li>
				</ol>
			</div>
			<p style="margin: 0; color: #78350f; font-size: 13px;">
				<strong><?php esc_html_e( 'Note:', 'third-audience' ); ?></strong>
				<?php esc_html_e( 'Citation tracking uses UTM parameters and HTTP referrers. If your site uses full-page caching, ensure cache is bypassed for URLs with UTM parameters.', 'third-audience' ); ?>
			</p>
		</div>
	</div>
	<?php endif; ?>

	<!-- Hero Metrics -->
	<div class="ta-hero-metrics">
		<div class="ta-hero-card">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-admin-links"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Total Citations', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo number_format( $total_citations ); ?></div>
				<div class="ta-hero-meta">
					<?php echo number_format( $citations_today ); ?> today
				</div>
			</div>
		</div>

		<div class="ta-hero-card">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-networking"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'AI Platforms', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo number_format( $unique_platforms ); ?></div>
				<div class="ta-hero-meta">Unique sources</div>
			</div>
		</div>

		<div class="ta-hero-card">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-search"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Queries Captured', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo number_format( $queries_captured ); ?></div>
				<div class="ta-hero-meta">From Perplexity searches</div>
			</div>
		</div>

		<div class="ta-hero-card">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-chart-area"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Capture Rate', 'third-audience' ); ?></div>
				<div class="ta-hero-value">
					<?php
					$capture_rate = $total_citations > 0 ? round( ( $queries_captured / $total_citations ) * 100 ) : 0;
					echo esc_html( $capture_rate );
					?>%
				</div>
				<div class="ta-hero-meta">Queries / Citations</div>
			</div>
		</div>
	</div>

	<!-- Overall Citation Rate Summary (v2.7.0) -->
	<div class="ta-card" style="margin-top: 20px; background: linear-gradient(135deg, #ffffff 0%, #f9f9fb 100%); border-left: 4px solid #007aff;">
		<div class="ta-card-body" style="padding: 24px;">
			<div style="display: flex; align-items: center; gap: 20px;">
				<div style="flex-shrink: 0;">
					<div style="width: 80px; height: 80px; background: rgba(0, 122, 255, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
						<span class="dashicons dashicons-chart-area" style="font-size: 36px; width: 36px; height: 36px; color: #007aff;"></span>
					</div>
				</div>
				<div style="flex: 1;">
					<h3 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 600; color: #1d1d1f;">
						<?php esc_html_e( 'Overall Citation Rate', 'third-audience' ); ?>
					</h3>
					<div style="display: flex; align-items: baseline; gap: 12px; margin-bottom: 8px;">
						<span style="font-size: 42px; font-weight: 700; color: <?php echo $overall_citation_rate >= 50 ? '#34c759' : ( $overall_citation_rate >= 20 ? '#ff9500' : '#ff3b30' ); ?>;">
							<?php echo esc_html( $overall_citation_rate ); ?>%
						</span>
						<span style="font-size: 13px; color: #646970;">
							<?php
							printf(
								/* translators: 1: citations count, 2: crawls count */
								esc_html__( '%1$s citations from %2$s crawls', 'third-audience' ),
								'<strong>' . number_format( $total_citations ) . '</strong>',
								'<strong>' . number_format( $total_crawls ) . '</strong>'
							);
							?>
						</span>
					</div>
					<p style="margin: 0; font-size: 13px; color: #646970; line-height: 1.5;">
						<?php
						if ( $overall_citation_rate >= 50 ) {
							esc_html_e( 'Excellent! Your content has strong citation performance. Over half of AI bot crawls result in citations.', 'third-audience' );
						} elseif ( $overall_citation_rate >= 20 ) {
							esc_html_e( 'Good citation rate. Consider optimizing content quality and structured data to improve visibility in AI responses.', 'third-audience' );
						} elseif ( $overall_citation_rate > 0 ) {
							esc_html_e( 'Low citation rate. Your content is being crawled but not cited. Focus on improving content depth, authority, and relevance.', 'third-audience' );
						} else {
							esc_html_e( 'No citations yet. When users click links from AI platforms (ChatGPT, Perplexity, etc.), citations will be tracked here.', 'third-audience' );
						}
						?>
					</p>
				</div>
			</div>
		</div>
	</div>

	<!-- Charts Section (v3.2.1) -->
	<div class="ta-charts-section" style="margin-top: 20px;">
		<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
			<!-- Daily Trend Chart -->
			<div class="ta-card">
				<div class="ta-card-header">
					<h2><?php esc_html_e( 'Citation Trend (Last 30 Days)', 'third-audience' ); ?></h2>
				</div>
				<div class="ta-card-body" style="padding: 20px;">
					<canvas id="ta-citations-trend-chart" height="200"></canvas>
				</div>
			</div>

			<!-- Platform Distribution Chart -->
			<div class="ta-card">
				<div class="ta-card-header">
					<h2><?php esc_html_e( 'Platform Distribution', 'third-audience' ); ?></h2>
				</div>
				<div class="ta-card-body" style="padding: 20px; display: flex; justify-content: center; align-items: center;">
					<?php if ( ! empty( $platform_data ) ) : ?>
						<canvas id="ta-platform-chart" height="200" style="max-width: 300px;"></canvas>
					<?php else : ?>
						<p style="color: #646970; text-align: center;"><?php esc_html_e( 'No platform data yet', 'third-audience' ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Weekly Comparison Chart -->
		<div class="ta-card" style="margin-top: 20px;">
			<div class="ta-card-header">
				<h2><?php esc_html_e( 'Citations vs Crawls (Weekly)', 'third-audience' ); ?></h2>
				<p class="description" style="margin-top: 8px;">
					<?php esc_html_e( 'Compare how many AI bot crawls resulted in actual citation clicks from users.', 'third-audience' ); ?>
				</p>
			</div>
			<div class="ta-card-body" style="padding: 20px;">
				<canvas id="ta-weekly-comparison-chart" height="120"></canvas>
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
		<div class="ta-filters-content" style="display: block;">
			<form method="get" id="ta-citations-filters-form">
				<input type="hidden" name="page" value="third-audience-ai-citations">
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
						<label><?php esc_html_e( 'AI Platform', 'third-audience' ); ?></label>
						<select name="platform">
							<option value=""><?php esc_html_e( 'All Platforms', 'third-audience' ); ?></option>
							<?php foreach ( $available_platforms as $platform ) : ?>
								<option value="<?php echo esc_attr( $platform ); ?>" <?php selected( $filters['platform'] ?? '', $platform ); ?>>
									<?php echo esc_html( $platform ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="ta-filter-item">
						<label><?php esc_html_e( 'Search', 'third-audience' ); ?></label>
						<input type="text" name="search" placeholder="<?php esc_attr_e( 'URL, title, or query...', 'third-audience' ); ?>" value="<?php echo esc_attr( $filters['search'] ?? '' ); ?>">
					</div>
					<div class="ta-filter-item ta-filter-actions">
						<label>&nbsp;</label>
						<div>
							<button type="submit" class="button button-primary"><?php esc_html_e( 'Apply', 'third-audience' ); ?></button>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-ai-citations' ) ); ?>" class="button"><?php esc_html_e( 'Reset', 'third-audience' ); ?></a>
							<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array_merge( $_GET, array( 'action' => 'export', 'export_format' => 'csv' ) ) ), 'ta_export_citations' ) ); ?>" class="button"><?php esc_html_e( 'Export CSV', 'third-audience' ); ?></a>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>

	<!-- Citations by Platform -->
	<div class="ta-card" style="margin-top: 20px;">
		<div class="ta-card-header">
			<h2><?php esc_html_e( 'Citations by AI Platform', 'third-audience' ); ?></h2>
		</div>
		<div class="ta-card-body">
			<?php if ( empty( $citations_by_platform ) ) : ?>
				<p style="text-align: center; color: #646970; padding: 40px 0;">
					<?php esc_html_e( 'No citations detected yet. When users click citations from AI platforms, they will appear here.', 'third-audience' ); ?>
				</p>
			<?php else : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Platform', 'third-audience' ); ?></th>
							<th style="text-align: right;"><?php esc_html_e( 'Total Citations', 'third-audience' ); ?></th>
							<th style="text-align: right;"><?php esc_html_e( 'Queries Captured', 'third-audience' ); ?></th>
							<th style="text-align: right;"><?php esc_html_e( 'Capture Rate', 'third-audience' ); ?></th>
							<th style="width: 200px;"><?php esc_html_e( 'Last Citation', 'third-audience' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $citations_by_platform as $platform ) : ?>
							<?php
							$platform_capture_rate = $platform['count'] > 0 ? round( ( $platform['queries_captured'] / $platform['count'] ) * 100 ) : 0;
							$last_citation_time = human_time_diff( strtotime( $platform['last_citation'] ), current_time( 'timestamp' ) );
							?>
							<tr>
								<td>
									<span class="ta-bot-badge">
										<?php echo esc_html( $platform['ai_platform'] ); ?>
									</span>
								</td>
								<td style="text-align: right;"><strong><?php echo number_format( $platform['count'] ); ?></strong></td>
								<td style="text-align: right;"><?php echo number_format( $platform['queries_captured'] ); ?></td>
								<td style="text-align: right;"><?php echo esc_html( $platform_capture_rate ); ?>%</td>
								<td style="color: #646970;" title="<?php echo esc_attr( $platform['last_citation'] ); ?>">
									<?php echo esc_html( $last_citation_time ); ?> ago
									<br>
									<small><?php echo esc_html( gmdate( 'M j, Y g:i A', strtotime( $platform['last_citation'] ) ) ); ?></small>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>

	<!-- Recent Search Queries -->
	<?php if ( ! empty( $recent_queries ) ) : ?>
		<div class="ta-card" style="margin-top: 20px;">
			<div class="ta-card-header">
				<h2><?php esc_html_e( 'Recent Search Queries', 'third-audience' ); ?></h2>
				<p class="description" style="margin-top: 8px;">
					<?php esc_html_e( 'Search queries extracted from Perplexity referrer URLs. Other platforms don\'t include query data in referrers.', 'third-audience' ); ?>
				</p>
			</div>
			<div class="ta-card-body">
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th style="width: 15%;"><?php esc_html_e( 'Platform', 'third-audience' ); ?></th>
							<th style="width: 35%;"><?php esc_html_e( 'Search Query', 'third-audience' ); ?></th>
							<th style="width: 30%;"><?php esc_html_e( 'Landing Page', 'third-audience' ); ?></th>
							<th style="width: 20%;"><?php esc_html_e( 'When', 'third-audience' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $recent_queries as $query ) : ?>
							<?php
							$time_ago   = human_time_diff( strtotime( $query['visit_timestamp'] ), current_time( 'timestamp' ) );
							$short_url  = strlen( $query['url'] ) > 50 ? substr( $query['url'], 0, 47 ) . '...' : $query['url'];
							?>
							<tr>
								<td>
									<span class="ta-bot-badge">
										<?php echo esc_html( $query['ai_platform'] ); ?>
									</span>
								</td>
								<td>
									<strong style="color: #007aff;">
										<?php echo esc_html( $query['search_query'] ); ?>
									</strong>
								</td>
								<td>
									<code style="font-size: 11px; color: #646970;">
										<?php echo esc_html( $short_url ); ?>
									</code>
								</td>
								<td style="color: #646970;">
									<?php echo esc_html( $time_ago ); ?> ago
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	<?php endif; ?>

	<!-- Recent Citations (ALL) -->
	<?php if ( ! empty( $recent_citations ) ) : ?>
		<div class="ta-card" style="margin-top: 20px;">
			<div class="ta-card-header">
				<h2><?php esc_html_e( 'Recent Citations', 'third-audience' ); ?></h2>
				<p class="description" style="margin-top: 8px;">
					<?php esc_html_e( 'All recent citation clicks from AI platforms, regardless of whether a search query was captured.', 'third-audience' ); ?>
				</p>
			</div>
			<div class="ta-card-body">
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th style="width: 12%;"><?php esc_html_e( 'Platform', 'third-audience' ); ?></th>
							<th style="width: 28%;"><?php esc_html_e( 'Page', 'third-audience' ); ?></th>
							<th style="width: 20%;"><?php esc_html_e( 'Search Query', 'third-audience' ); ?></th>
							<th style="width: 20%;"><?php esc_html_e( 'Date & Time', 'third-audience' ); ?></th>
							<th style="width: 20%;"><?php esc_html_e( 'Referrer', 'third-audience' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $recent_citations as $citation ) : ?>
							<?php
							$time_ago   = human_time_diff( strtotime( $citation['visit_timestamp'] ), current_time( 'timestamp' ) );
							$short_url  = strlen( $citation['url'] ) > 40 ? substr( $citation['url'], 0, 37 ) . '...' : $citation['url'];
							$short_ref  = ! empty( $citation['referer'] ) ? ( strlen( $citation['referer'] ) > 30 ? substr( $citation['referer'], 0, 27 ) . '...' : $citation['referer'] ) : '—';
							?>
							<tr>
								<td>
									<span class="ta-bot-badge">
										<?php echo esc_html( $citation['ai_platform'] ); ?>
									</span>
								</td>
								<td>
									<strong><?php echo esc_html( $citation['post_title'] ?: 'Untitled' ); ?></strong>
									<br>
									<code style="font-size: 10px; color: #646970;">
										<?php echo esc_html( $short_url ); ?>
									</code>
								</td>
								<td>
									<?php if ( ! empty( $citation['search_query'] ) ) : ?>
										<span style="color: #007aff;">
											<?php echo esc_html( $citation['search_query'] ); ?>
										</span>
									<?php else : ?>
										<span style="color: #8e8e93;">—</span>
									<?php endif; ?>
								</td>
								<td>
									<strong><?php echo esc_html( gmdate( 'M j, Y', strtotime( $citation['visit_timestamp'] ) ) ); ?></strong>
									<br>
									<span style="color: #646970; font-size: 12px;">
										<?php echo esc_html( gmdate( 'g:i:s A', strtotime( $citation['visit_timestamp'] ) ) ); ?>
										<br>
										(<?php echo esc_html( $time_ago ); ?> ago)
									</span>
								</td>
								<td>
									<code style="font-size: 10px; color: #646970;" title="<?php echo esc_attr( $citation['referer'] ); ?>">
										<?php echo esc_html( $short_ref ); ?>
									</code>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	<?php endif; ?>

	<!-- Top Cited Pages -->
	<?php if ( ! empty( $top_cited_pages ) ) : ?>
		<div class="ta-card" style="margin-top: 20px;">
			<div class="ta-card-header">
				<h2><?php esc_html_e( 'Most Cited Pages', 'third-audience' ); ?></h2>
			</div>
			<div class="ta-card-body">
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Page', 'third-audience' ); ?></th>
							<th style="width: 12%; text-align: right;"><?php esc_html_e( 'Citations', 'third-audience' ); ?></th>
							<th style="width: 12%; text-align: right;"><?php esc_html_e( 'Platforms', 'third-audience' ); ?></th>
							<th style="width: 20%;"><?php esc_html_e( 'Last Cited', 'third-audience' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $top_cited_pages as $page ) : ?>
							<?php
							$last_cited_time = human_time_diff( strtotime( $page['last_cited'] ), current_time( 'timestamp' ) );
							?>
							<tr>
								<td>
									<strong><?php echo esc_html( $page['post_title'] ?: 'Untitled' ); ?></strong>
									<br>
									<code style="font-size: 11px; color: #646970;">
										<?php echo esc_html( $page['url'] ); ?>
									</code>
								</td>
								<td style="text-align: right;"><strong><?php echo number_format( $page['citation_count'] ); ?></strong></td>
								<td style="text-align: right;"><?php echo number_format( $page['platforms'] ); ?></td>
								<td style="color: #646970;">
									<?php echo esc_html( gmdate( 'M j, Y g:i A', strtotime( $page['last_cited'] ) ) ); ?>
									<br>
									<small>(<?php echo esc_html( $last_cited_time ); ?> ago)</small>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	<?php endif; ?>

	<!-- Help Section -->
	<div class="ta-card" style="margin-top: 20px; background: #f5f5f7;">
		<div class="ta-card-header">
			<h2><?php esc_html_e( 'Understanding AI Citations', 'third-audience' ); ?></h2>
		</div>
		<div class="ta-card-body">
			<h3><?php esc_html_e( 'What is Citation Traffic?', 'third-audience' ); ?></h3>
			<p>
				<?php esc_html_e( 'Citation traffic occurs when real users click links from AI platform responses (ChatGPT, Perplexity, Claude, etc.) and land on your site. This is different from bot crawl traffic, which is when AI bots visit to index your content.', 'third-audience' ); ?>
			</p>

			<h3><?php esc_html_e( 'Search Query Extraction', 'third-audience' ); ?></h3>
			<p>
				<strong><?php esc_html_e( 'Perplexity:', 'third-audience' ); ?></strong>
				<?php esc_html_e( ' Includes search queries in referrer URLs (https://perplexity.ai/search?q=your+query). We can extract these!', 'third-audience' ); ?>
			</p>
			<p>
				<strong><?php esc_html_e( 'ChatGPT, Claude, Gemini:', 'third-audience' ); ?></strong>
				<?php esc_html_e( ' Only provide platform detection - no query data in referrers.', 'third-audience' ); ?>
			</p>

			<h3><?php esc_html_e( 'Privacy-First Tracking', 'third-audience' ); ?></h3>
			<p>
				<?php esc_html_e( 'All citation data is stored locally in your WordPress database. Nothing is sent to external servers. This tracking uses standard HTTP referrer headers that browsers send automatically.', 'third-audience' ); ?>
			</p>
		</div>
	</div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
	// Chart data from PHP
	var chartLabels = <?php echo wp_json_encode( $chart_labels ); ?>;
	var chartCitations = <?php echo wp_json_encode( $chart_citations ); ?>;
	var chartCrawls = <?php echo wp_json_encode( $chart_crawls ); ?>;
	var platformLabels = <?php echo wp_json_encode( $platform_labels ); ?>;
	var platformData = <?php echo wp_json_encode( $platform_data ); ?>;
	var weeklyData = <?php echo wp_json_encode( $weekly_data ); ?>;
	var platformColors = <?php echo wp_json_encode( array_slice( $platform_colors, 0, count( $platform_labels ) ) ); ?>;

	// Common chart options
	var commonOptions = {
		responsive: true,
		maintainAspectRatio: true,
		plugins: {
			legend: {
				display: true,
				position: 'top',
				labels: {
					usePointStyle: true,
					padding: 15,
					font: { size: 12 }
				}
			}
		}
	};

	// 1. Daily Trend Line Chart
	var trendCtx = document.getElementById('ta-citations-trend-chart');
	if (trendCtx) {
		new Chart(trendCtx, {
			type: 'line',
			data: {
				labels: chartLabels,
				datasets: [
					{
						label: 'Citations',
						data: chartCitations,
						borderColor: '#007aff',
						backgroundColor: 'rgba(0, 122, 255, 0.1)',
						fill: true,
						tension: 0.4,
						pointRadius: 2,
						pointHoverRadius: 5
					},
					{
						label: 'Crawls',
						data: chartCrawls,
						borderColor: '#8e8e93',
						backgroundColor: 'transparent',
						borderDash: [5, 5],
						tension: 0.4,
						pointRadius: 0,
						pointHoverRadius: 3
					}
				]
			},
			options: Object.assign({}, commonOptions, {
				scales: {
					y: {
						beginAtZero: true,
						grid: { color: 'rgba(0, 0, 0, 0.05)' },
						ticks: { stepSize: 1 }
					},
					x: {
						grid: { display: false },
						ticks: {
							maxTicksLimit: 10,
							font: { size: 11 }
						}
					}
				},
				interaction: {
					intersect: false,
					mode: 'index'
				}
			})
		});
	}

	// 2. Platform Distribution Doughnut Chart
	var platformCtx = document.getElementById('ta-platform-chart');
	if (platformCtx && platformData.length > 0) {
		new Chart(platformCtx, {
			type: 'doughnut',
			data: {
				labels: platformLabels,
				datasets: [{
					data: platformData,
					backgroundColor: platformColors,
					borderWidth: 2,
					borderColor: '#ffffff'
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: true,
				plugins: {
					legend: {
						position: 'bottom',
						labels: {
							usePointStyle: true,
							padding: 12,
							font: { size: 11 }
						}
					}
				},
				cutout: '60%'
			}
		});
	}

	// 3. Weekly Comparison Bar Chart
	var weeklyCtx = document.getElementById('ta-weekly-comparison-chart');
	if (weeklyCtx) {
		var weekLabels = weeklyData.map(function(w) { return w.label; });
		var weekCitations = weeklyData.map(function(w) { return w.citations; });
		var weekCrawls = weeklyData.map(function(w) { return w.crawls; });

		new Chart(weeklyCtx, {
			type: 'bar',
			data: {
				labels: weekLabels,
				datasets: [
					{
						label: 'Citations',
						data: weekCitations,
						backgroundColor: '#007aff',
						borderRadius: 4
					},
					{
						label: 'Crawls',
						data: weekCrawls,
						backgroundColor: '#e5e5ea',
						borderRadius: 4
					}
				]
			},
			options: Object.assign({}, commonOptions, {
				scales: {
					y: {
						beginAtZero: true,
						grid: { color: 'rgba(0, 0, 0, 0.05)' },
						ticks: { stepSize: 1 }
					},
					x: {
						grid: { display: false }
					}
				},
				barPercentage: 0.7,
				categoryPercentage: 0.8
			})
		});
	}
});
</script>
