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

// Bot-only filters: exclude human AI-referral clicks (citation_click) from all
// bot analytics metrics so hero cards, distribution, and page lists show only
// actual bot crawls — not user visits that arrived from AI platforms.
$bot_filters = array_merge(
	$filters,
	array( 'exclude_traffic_type' => 'citation_click' )
);

// Get analytics data.
$summary       = $analytics->get_summary( $bot_filters );
$bot_stats     = $analytics->get_visits_by_bot( $bot_filters );
$top_pages     = $analytics->get_top_pages( $bot_filters, 10 );
$visits_time   = $analytics->get_visits_over_time( $bot_filters, $time_period, 30 );

// Pagination.
$current_page  = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$per_page      = 30;
$offset        = ( $current_page - 1 ) * $per_page;
// Bot Crawl Feed shows bot crawls only — human AI-referral clicks
// (citation_click) live on the LLM Traffic page, not here. Plain HTML page
// crawls are excluded too, so the feed shows only .md / .txt requests.
$feed_filters  = array_merge(
	$filters,
	array(
		'exclude_traffic_type' => 'citation_click',
		'exclude_content_type' => 'html',
	)
);
$recent_visits = $analytics->get_recent_visits( $feed_filters, $per_page, $offset );

// Session Analytics (v2.6.0).
$session_stats     = $analytics->get_session_analytics();
$top_bots_session  = $analytics->get_top_bots_by_metric( 'pages_per_session', 10 );
$crawl_budget_day  = $analytics->get_crawl_budget_metrics( null, 'day' );
$crawl_budget_hour = $analytics->get_crawl_budget_metrics( null, 'hour' );

// Citation Performance (v2.7.0).
$citation_data = $analytics->get_citation_to_crawl_ratio( $filters, 10 );

// Rate limiting.
$rate_limiter      = new TA_Rate_Limiter();
$recent_violations = $rate_limiter->get_rate_limit_violations( 10 );
?>

<div class="wrap ta-bot-analytics">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Bot Analytics', 'third-audience' ); ?>
		<span style="font-size: 0.6em; color: #646970; font-weight: 400;">v<?php echo esc_html( TA_VERSION ); ?></span>
	</h1>
	<p class="description"><?php esc_html_e( 'Track which AI bots are crawling your site, how deeply they read your content, and how often that crawling turns into real user traffic from AI platforms.', 'third-audience' ); ?></p>

	<!-- Real-time Metrics Hero -->
	<div class="ta-hero-metrics">
		<div class="ta-hero-card ta-hero-card-clickable ta-hero-card--blue" data-metric="total_visits" title="<?php esc_attr_e( 'Click to see visit breakdown by bot type', 'third-audience' ); ?>">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-chart-line"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Total Bot Visits', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo number_format( $summary['total_visits'] ); ?></div>
				<div class="ta-hero-meta">
					<?php echo number_format( $summary['visits_today'] ); ?> today
				</div>
				<div class="ta-hero-desc">AI bots that crawled your pages to read &amp; index content</div>
			</div>
			<span class="ta-card-arrow"><span class="dashicons dashicons-arrow-right-alt2"></span></span>
		</div>

		<div class="ta-hero-card ta-hero-card-clickable ta-hero-card--teal" data-metric="pages_crawled" title="<?php esc_attr_e( 'Click to see all crawled pages', 'third-audience' ); ?>">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-admin-page"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Pages Crawled', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo number_format( $summary['unique_pages'] ); ?></div>
				<div class="ta-hero-meta"><?php echo number_format( $summary['unique_bots'] ); ?> unique bots detected</div>
				<div class="ta-hero-desc">Unique pages discovered &amp; read across all bot visits</div>
			</div>
			<span class="ta-card-arrow"><span class="dashicons dashicons-arrow-right-alt2"></span></span>
		</div>

		<div class="ta-hero-card ta-hero-card-clickable ta-hero-card--green" data-metric="cache_hit_rate" title="<?php esc_attr_e( 'Click to see cache performance details', 'third-audience' ); ?>">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-performance"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Cache Hit Rate', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo $summary['cache_hit_rate']; ?>%</div>
				<div class="ta-hero-meta">Higher = faster bot response</div>
				<div class="ta-hero-desc">% of bot requests served from pre-generated cache — saves server load</div>
			</div>
			<span class="ta-card-arrow"><span class="dashicons dashicons-arrow-right-alt2"></span></span>
		</div>

		<div class="ta-hero-card ta-hero-card-clickable ta-hero-card--purple" data-metric="avg_response" title="<?php esc_attr_e( 'Click to see response time breakdown', 'third-audience' ); ?>">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-dashboard"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Avg Response', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo $summary['avg_response_time']; ?><span style="font-size: 14px;">ms</span></div>
				<div class="ta-hero-meta">Lower = faster server responses</div>
				<div class="ta-hero-desc">How fast your server responds when a bot requests a page</div>
			</div>
			<span class="ta-card-arrow"><span class="dashicons dashicons-arrow-right-alt2"></span></span>
		</div>

		<div class="ta-hero-card ta-hero-card-clickable ta-hero-card--orange" data-metric="verified_bots" title="<?php esc_attr_e( 'Click to see verification breakdown', 'third-audience' ); ?>">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-yes-alt"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Verified Bots', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo $summary['ip_verified_percentage']; ?>%</div>
				<div class="ta-hero-meta"><?php echo number_format( $summary['ip_verified_count'] ); ?> confirmed legitimate visits</div>
				<div class="ta-hero-desc">Bot IPs cross-checked against official provider IP ranges (e.g. OpenAI, Google)</div>
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
			<?php esc_html_e( 'How bots behave during each visit — how many pages they read, how long they stay, and how often they return. A bot that reads more pages per session is indexing your content more thoroughly.', 'third-audience' ); ?>
		</p>
	</div>

	<div class="ta-hero-metrics ta-hero-metrics-secondary">
		<div class="ta-hero-card ta-hero-card-clickable ta-hero-card--purple" data-metric="fingerprints" title="<?php esc_attr_e( 'Click to see all bot fingerprints', 'third-audience' ); ?>">
			<div class="ta-hero-icon ta-hero-icon-purple">
				<span class="dashicons dashicons-admin-users"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Bot Fingerprints', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo number_format( $session_stats['total_bot_fingerprints'] ); ?></div>
				<div class="ta-hero-meta">Unique bot + IP combinations</div>
				<div class="ta-hero-desc">Same bot crawling from 3 different IPs = 3 fingerprints. Helps identify distributed crawlers.</div>
			</div>
			<span class="ta-click-hint"><span class="dashicons dashicons-arrow-right-alt2"></span></span>
		</div>

		<div class="ta-hero-card ta-hero-card-clickable ta-hero-card--blue" data-metric="pages_per_session" title="<?php esc_attr_e( 'Click to see pages per session breakdown', 'third-audience' ); ?>">
			<div class="ta-hero-icon ta-hero-icon-blue">
				<span class="dashicons dashicons-admin-page"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Pages Per Session', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo number_format( $session_stats['avg_pages_per_session'], 1 ); ?></div>
				<div class="ta-hero-meta"><?php echo number_format( $session_stats['avg_visits_per_bot'], 1 ); ?> visits/bot avg</div>
				<div class="ta-hero-desc">Avg pages a bot reads per crawl session — 5+ means deep, thorough indexing</div>
			</div>
			<span class="ta-click-hint"><span class="dashicons dashicons-arrow-right-alt2"></span></span>
		</div>

		<div class="ta-hero-card ta-hero-card-clickable ta-hero-card--green" data-metric="session_duration" title="<?php esc_attr_e( 'Click to see session duration breakdown', 'third-audience' ); ?>">
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
				<div class="ta-hero-meta"><?php echo number_format( $session_stats['avg_session_duration'] ); ?>s per crawl session</div>
				<div class="ta-hero-desc">How long a bot spends on your site in one visit — longer = more pages read</div>
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
							<option value=""><?php esc_html_e( 'All (.md + .txt)', 'third-audience' ); ?></option>
							<option value="markdown" <?php selected( $filters['content_type'] ?? '', 'markdown' ); ?>><?php esc_html_e( 'Markdown (.md)', 'third-audience' ); ?></option>
							<option value="text" <?php selected( $filters['content_type'] ?? '', 'text' ); ?>><?php esc_html_e( 'Text (.txt)', 'third-audience' ); ?></option>
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
			<div class="ta-card-header ta-card-header--blue">
				<h2><?php esc_html_e( 'Bot Activity Distribution', 'third-audience' ); ?></h2>
				<?php if ( ! empty( $bot_stats ) ) : ?>
				<button type="button" class="button button-small ta-export-btn" data-export="bot-distribution" title="<?php esc_attr_e( 'Export to CSV', 'third-audience' ); ?>">
					<span class="dashicons dashicons-download"></span>
				</button>
				<?php endif; ?>
			</div>
			<div class="ta-card-body">
				<p style="margin: 0 0 14px 0; font-size: 13px; color: #646970;">
					Which AI bots visited your site and how many times. Hover over a bot name to see what it does. Click any row to see a full breakdown.
				</p>
				<?php if ( ! empty( $bot_stats ) ) : ?>
					<?php
					// Known bot descriptions shown on row hover.
					$known_bot_descriptions = array(
						'ChatGPT-User'           => 'OpenAI ChatGPT crawler — reads your pages to answer user queries in ChatGPT',
						'ChatGPT User'           => 'OpenAI ChatGPT crawler — reads your pages to answer user queries in ChatGPT',
						'Chatgpt'                => 'OpenAI ChatGPT — same crawler, detected via an alternate user-agent pattern',
						'GPTBot'                 => 'OpenAI training crawler — indexes your content for future GPT model training',
						'OAI-SearchBot'          => 'OpenAI search-focused crawler — used for real-time web search in ChatGPT',
						'Oai-searchbot'          => 'OpenAI search-focused crawler — used for real-time web search in ChatGPT',
						'PerplexityBot'          => 'Perplexity AI crawler — indexes content to power Perplexity search answers',
						'Perplexity'             => 'Perplexity AI — user arrived from a Perplexity AI link to your site',
						'Googlebot'              => 'Google search crawler — indexes your pages for Google Search results',
						'Google-Extended'        => 'Google AI training crawler — indexes content for Gemini AI models',
						'Google search'          => 'Google Search referral — a user clicked your link in Google Search results',
						'Google AI Mode'         => 'Google AI Mode referral — user clicked your link from Google AI-assisted search results',
						'Bingbot'                => 'Microsoft Bing crawler — indexes your pages for Bing Search',
						'Bing ai'                => 'Microsoft Bing AI referral — user arrived from a Bing AI search result',
						'Bing AI'                => 'Microsoft Bing AI referral — user arrived from a Bing AI search result',
						'ClaudeBot'              => 'Anthropic training crawler — indexes content for Claude AI model training',
						'Claude [ Referrer ]'    => 'Anthropic Claude referral — user clicked your link from Claude.ai',
						'Claude [Referrer]'      => 'Anthropic Claude referral — user clicked your link from Claude.ai',
						'Hidden Referrer (Claude)' => 'Likely Claude.ai — referer header was stripped (privacy mode), detected via Sec-Fetch headers',
						'Amazonbot'              => 'Amazon web crawler — used for Amazon product search and Alexa data',
						'Bytespider'             => 'ByteDance AI crawler — TikTok parent company, indexes content for AI products',
						'ByteDance AI'           => 'ByteDance AI crawler — TikTok parent company, indexes content for AI products',
						'Barkrowler'             => 'Babbar.tech SEO crawler — audits site structure for SEO analysis tools',
						'Webscraperbot'          => 'WebScraper.io bot — general content extraction tool',
						'Duckassistbot'          => 'DuckDuckGo AI crawler — indexes content for DuckDuckGo AI features',
						'TikTokSpider'           => 'TikTok content crawler — indexes pages for TikTok recommendations',
						'Unknown Bot'            => 'Detected as a bot but identity not confirmed — unusual user-agent or unrecognized pattern',
						'Unknown'                => 'Detected as a bot but identity not confirmed — unusual user-agent or unrecognized pattern',
					);
					?>
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
								$bot_desc = $known_bot_descriptions[ $bot['bot_name'] ] ?? $known_bot_descriptions[ $bot['bot_type'] ] ?? 'Click to see detailed breakdown';
								?>
								<tr class="ta-bot-dist-row"
									style="cursor: pointer;"
									data-bot-type="<?php echo esc_attr( $bot['bot_type'] ); ?>"
									data-bot-name="<?php echo esc_attr( $bot['bot_name'] ); ?>"
									title="<?php echo esc_attr( $bot_desc ); ?>">
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
			<div class="ta-card-header ta-card-header--green">
				<h2><?php esc_html_e( 'Most Crawled Content', 'third-audience' ); ?></h2>
				<?php if ( ! empty( $top_pages ) ) : ?>
				<button type="button" class="button button-small ta-export-btn" data-export="top-content" title="<?php esc_attr_e( 'Export to CSV', 'third-audience' ); ?>">
					<span class="dashicons dashicons-download"></span>
				</button>
				<?php endif; ?>
			</div>
			<div class="ta-card-body">
				<p style="margin: 0 0 14px 0; font-size: 13px; color: #646970;">
					Pages AI bots have visited most — these are the pages being indexed for AI answers. The <span class="dashicons dashicons-media-text" style="font-size: 14px; vertical-align: middle;"></span> icon shows what bots actually see (Markdown version).
				</p>
				<?php if ( ! empty( $top_pages ) ) : ?>
					<table class="ta-table ta-table-compact">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Page', 'third-audience' ); ?></th>
								<th><?php esc_html_e( 'Visits', 'third-audience' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php $rank = 0; foreach ( array_slice( $top_pages, 0, 10 ) as $page ) : $rank++; $rank_extra = $rank <= 3 ? " ta-rank-badge--{$rank}" : ''; ?>
								<tr>
									<td>
										<span class="ta-rank-badge<?php echo esc_attr( $rank_extra ); ?>"><?php echo $rank; ?></span>
										<a href="<?php echo esc_url( ta_citation_public_url( $page['url'] ) ); ?>" target="_blank" class="ta-page-link" title="<?php esc_attr_e( 'View page', 'third-audience' ); ?>">
											<?php echo esc_html( wp_trim_words( ta_page_display_title( $page['post_title'] ?? '', $page['url'] ), 8 ) ); ?>
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
		<!-- How Deeply Bots Read Your Content (was: Top Bots by Session Activity) -->
		<style>
			.ta-engagement-badge {
				display: inline-flex;
				align-items: center;
				gap: 4px;
				padding: 3px 8px;
				border-radius: 10px;
				font-size: 11px;
				font-weight: 600;
				white-space: nowrap;
				position: relative;
				cursor: default;
			}
			.ta-engagement-badge.deep  { background: #dcfce7; color: #166534; }
			.ta-engagement-badge.normal { background: #fef9c3; color: #854d0e; }
			.ta-engagement-badge.shallow { background: #fee2e2; color: #991b1b; }

			/* JS tooltip — see #ta-engagement-tooltip below */
		</style>
		<div class="ta-card">
			<div class="ta-card-header ta-card-header--teal">
				<h2><?php esc_html_e( 'How Deeply Bots Read Your Content', 'third-audience' ); ?></h2>
			</div>
			<div class="ta-card-body">
				<p style="margin: 0 0 14px 0; font-size: 13px; color: #646970;">
					<?php esc_html_e( 'Higher pages per visit = bot is indexing your content more thoroughly. More depth means better chance of being cited.', 'third-audience' ); ?>
				</p>
				<?php if ( ! empty( $top_bots_session ) ) : ?>
					<table class="ta-table ta-table-compact">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Bot', 'third-audience' ); ?></th>
								<th><?php esc_html_e( 'Pages per Visit', 'third-audience' ); ?></th>
								<th><?php esc_html_e( 'Time Spent', 'third-audience' ); ?></th>
								<th><?php esc_html_e( 'Total Visits', 'third-audience' ); ?></th>
								<th><?php esc_html_e( 'Engagement', 'third-audience' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $top_bots_session as $bot ) :
								$pps           = (float) $bot['pages_per_session_avg'];
								$duration_mins = round( $bot['session_duration_avg'] / 60, 1 );

								if ( $pps >= 5 ) {
									$badge_class = 'deep';
									$badge_icon  = '🟢';
									$badge_label = __( 'Deep Crawl', 'third-audience' );
									$badge_tip   = __( 'Deep Crawl — This bot reads 5+ pages per visit. It is thoroughly indexing your content across multiple topics. High engagement increases the chance of being cited by this AI platform.', 'third-audience' );
								} elseif ( $pps >= 2 ) {
									$badge_class = 'normal';
									$badge_icon  = '🟡';
									$badge_label = __( 'Normal', 'third-audience' );
									$badge_tip   = __( 'Normal Crawl — This bot reads 2–5 pages per visit. Standard indexing depth. Your content is being discovered but not exhaustively.', 'third-audience' );
								} else {
									$badge_class = 'shallow';
									$badge_icon  = '🔴';
									$badge_label = __( 'Shallow', 'third-audience' );
									$badge_tip   = __( 'Shallow Crawl — This bot reads fewer than 2 pages per visit. Brief visits only. Consider improving internal linking to encourage deeper crawling.', 'third-audience' );
								}
							?>
								<tr>
									<td>
										<span class="ta-bot-name">
											<?php echo esc_html( $bot['bot_type'] ); ?>
										</span>
									</td>
									<td>
										<strong><?php echo number_format( $pps, 1 ); ?></strong>
										<span style="color: #646970; font-size: 11px;"> pages</span>
									</td>
									<td>
										<strong><?php echo number_format( $duration_mins, 1 ); ?></strong>
										<span style="color: #646970; font-size: 11px;"> min</span>
									</td>
									<td><?php echo number_format( $bot['visit_count'] ); ?></td>
									<td>
										<span class="ta-engagement-badge <?php echo esc_attr( $badge_class ); ?>"
											data-tip="<?php echo esc_attr( $badge_tip ); ?>">
											<?php echo $badge_icon; ?> <?php echo esc_html( $badge_label ); ?>
										</span>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p class="ta-no-data"><?php esc_html_e( 'No data yet. Appears after multiple visits from the same bot.', 'third-audience' ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<!-- Crawl Budget Metrics -->
		<div class="ta-card">
			<div class="ta-card-header ta-card-header--orange">
				<h2><?php esc_html_e( 'Crawl Budget Analysis', 'third-audience' ); ?></h2>
				<button type="button" class="button button-small ta-export-btn" data-export="crawl-budget" title="<?php esc_attr_e( 'Export to CSV', 'third-audience' ); ?>">
					<span class="dashicons dashicons-download"></span>
				</button>
			</div>
			<div class="ta-card-body">
				<p style="margin: 0 0 14px 0; font-size: 13px; color: #646970;">
					How much server capacity AI bots consumed. <strong>Last 24 Hours</strong> = full previous day's crawl activity. <strong>Last Hour</strong> = most recent 60 minutes. Unique Pages = distinct URLs requested; Total Requests = total crawl calls including re-crawls.
				</p>
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
		<div class="ta-card-header ta-card-header--purple">
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
			<p style="margin: 0 0 14px 0; font-size: 13px; color: #646970;">
				For each page: <strong>Crawls</strong> = how many times AI bots read it &nbsp;|&nbsp; <strong>Citations</strong> = how many real users then arrived from an AI platform &nbsp;|&nbsp; <strong>Rate</strong> = Citations ÷ Crawls. A high rate means bots are actively recommending this page to users.
			</p>
			<?php if ( empty( $citation_data ) ) : ?>
				<p class="ta-no-data"><?php esc_html_e( 'No citation data yet. Citations are tracked when users click links from AI platforms (ChatGPT, Perplexity, etc.).', 'third-audience' ); ?></p>
			<?php else : ?>
				<table class="ta-table ta-table-compact">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Page', 'third-audience' ); ?></th>
							<th style="text-align: right;" title="How many times AI bots crawled this page"><?php esc_html_e( 'Crawls', 'third-audience' ); ?> ↓</th>
							<th style="text-align: right;" title="How many real users clicked to this page from an AI platform (ChatGPT, Perplexity, etc.)"><?php esc_html_e( 'Citations', 'third-audience' ); ?></th>
							<th style="text-align: right;" title="Citation Rate = Citations ÷ Crawls — how often crawling converts to actual user traffic"><?php esc_html_e( 'Rate', 'third-audience' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $citation_data as $page ) : ?>
							<?php
							$citation_rate_percent = round( $page['citation_rate'] * 100, 1 );

							// 3-tier color: > 10% = strong (green), 2–10% = average (blue), < 2% = low (gray).
							if ( $citation_rate_percent > 10 ) {
								$rate_class = 'ta-citation-rate-strong';
							} elseif ( $citation_rate_percent >= 2 ) {
								$rate_class = 'ta-citation-rate-avg';
							} else {
								$rate_class = 'ta-citation-rate-low';
							}
							$rate_color = '';
							?>
							<tr>
								<td>
									<a href="<?php echo esc_url( ta_citation_public_url( $page['url'] ) ); ?>" target="_blank" class="ta-page-link">
										<?php echo esc_html( wp_trim_words( ta_page_display_title( $page['post_title'] ?? '', $page['url'] ), 8 ) ); ?>
									</a>
								</td>
								<td style="text-align: right;">
									<strong><?php echo number_format( $page['crawls'] ); ?></strong>
								</td>
								<td style="text-align: right;">
									<?php echo number_format( $page['citations'] ); ?>
								</td>
								<td style="text-align: right;">
									<span class="ta-citation-rate-badge <?php echo esc_attr( $rate_class ); ?>">
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

	<!-- Activity Timeline Chart -->
	<div class="ta-card">
		<div class="ta-card-header ta-card-header--blue">
			<h2><?php esc_html_e( 'Bot Activity Over Time', 'third-audience' ); ?></h2>
			<button type="button" class="button button-small ta-export-btn" data-export="activity-timeline" title="<?php esc_attr_e( 'Export to CSV', 'third-audience' ); ?>">
				<span class="dashicons dashicons-download"></span>
			</button>
		</div>
		<div class="ta-card-body">
			<p style="margin: 0 0 14px 0; font-size: 13px; color: #646970;">
				Daily bot crawl volume over the last 30 days. Spikes indicate active indexing periods — useful for seeing when AI platforms are most actively crawling your site.
			</p>
			<canvas id="ta-visits-chart" style="max-height: 300px;"></canvas>
		</div>
	</div>

	<!-- Live Activity Feed -->
	<div class="ta-card">
		<div class="ta-card-header ta-card-header--green">
			<h2>
				<span class="ta-live-indicator"></span>
				<?php esc_html_e( 'Bot Crawl Feed', 'third-audience' ); ?>
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
			<p style="margin: 0 0 14px 0; font-size: 13px; color: #646970;">
				Live feed of AI bot crawl requests — only <strong>MD</strong> (Markdown) and <strong>TXT</strong> requests are shown here, which are the AI-targeted formats. <strong>Type</strong> = file format served &nbsp;|&nbsp; <strong>Cache</strong> = whether it was served instantly from cache or generated fresh &nbsp;|&nbsp; <strong>Location</strong> = country the bot IP is registered in.
			</p>
			<table class="ta-table" id="ta-activity-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Time', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Bot', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Page', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Type', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Location', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'IP Address', 'third-audience' ); ?></th>
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
						foreach ( $recent_visits as $visit ) :
							// Per-bot brand color from the detector (ChatGPT teal, Claude
							// orange, Perplexity cyan…). Unknown/custom bots fall back to
							// a stable name-hash color so every distinct bot name gets its
							// own color — same name always renders the same color.
							$brand_color = $analytics->get_bot_color( $visit['bot_type'] );
							if ( '#8B5CF6' === $brand_color ) {
								$hue        = absint( crc32( strtolower( (string) $visit['bot_name'] ) ) % 360 );
								$bot_color  = "hsl({$hue}, 62%, 38%)";
								$bot_bg     = "hsla({$hue}, 62%, 38%, 0.09)";
							} else {
								$bot_color = $brand_color;
								$bot_bg    = $brand_color . '18';
							}
							?>
							<tr>
								<td class="ta-time-cell">
									<?php echo esc_html( human_time_diff( strtotime( $visit['visit_timestamp'] ), current_time( 'timestamp' ) ) ); ?> ago
								</td>
								<td>
									<span class="ta-bot-tag" style="border-left-color:<?php echo esc_attr( $bot_color ); ?>;background:<?php echo esc_attr( $bot_bg ); ?>;color:<?php echo esc_attr( $bot_color ); ?>;">
										<?php echo esc_html( $visit['bot_name'] ); ?>
									</span>
								</td>
								<td>
									<?php
									// Prefer the captured post title. '??' alone fails here because some
									// citation rows store an empty string (not NULL), so fall back to the
									// URL path / 'Homepage' / '(no title)' to avoid a blank PAGE cell.
									if ( ! empty( $visit['post_title'] ) ) {
										$page_label = $visit['post_title'];
									} elseif ( ! empty( $visit['url'] ) ) {
										$url_path   = wp_parse_url( $visit['url'], PHP_URL_PATH ) ?: $visit['url'];
										$page_label = ( '' === $url_path || '/' === $url_path ) ? __( 'Homepage', 'third-audience' ) : trim( $url_path, '/' );
									} else {
										$page_label = __( '(no title)', 'third-audience' );
									}
									?>
									<a href="<?php echo esc_url( ta_citation_public_url( $visit['url'] ) ); ?>" target="_blank" class="ta-page-link">
										<?php echo esc_html( wp_trim_words( $page_label, 6 ) ); ?>
									</a>
								</td>
								<td>
									<?php
									$content_type = $visit['content_type'] ?? 'html';
									if ( 'markdown' === $content_type ) {
										$type_class = 'ta-content-type-md';
										$type_label = 'MD';
									} elseif ( 'text' === $content_type ) {
										$type_class = 'ta-content-type-txt';
										$type_label = 'TXT';
									} else {
										$type_class = 'ta-content-type-html';
										$type_label = 'HTML';
									}
									?>
									<span class="ta-content-type-badge <?php echo esc_attr( $type_class ); ?>">
										<?php echo esc_html( $type_label ); ?>
									</span>
								</td>
								<td>
									<?php
									$cc = strtoupper( (string) ( $visit['country_code'] ?? '' ) );
									if ( 2 === strlen( $cc ) && ctype_alpha( $cc ) ) :
										// Country code → flag emoji (regional indicator pair).
										$flag_html = '&#' . ( 127397 + ord( $cc[0] ) ) . ';&#' . ( 127397 + ord( $cc[1] ) ) . ';';
										?>
										<span class="ta-location">
											<?php echo $flag_html; // phpcs:ignore WordPress.Security.EscapeOutput -- numeric HTML entities built from validated A-Z chars. ?>
											<?php echo esc_html( $cc ); ?>
										</span>
									<?php else : ?>
										-
									<?php endif; ?>
								</td>
								<td>
									<?php if ( ! empty( $visit['ip_address'] ) ) : ?>
										<code style="font-size:11px; background:#f6f7f7; border:1px solid #dcdcde; border-radius:4px; padding:1px 6px;"><?php echo esc_html( $visit['ip_address'] ); ?></code>
									<?php else : ?>
										-
									<?php endif; ?>
								</td>
								<td>
									<?php
									// Cache status → speed icon + friendly label + tooltip (hover).
									$cs = strtoupper( (string) ( $visit['cache_status'] ?? '' ) );
									$cache_map = array(
										'PRE_GENERATED' => array( '⚡', __( 'Instant', 'third-audience' ), __( 'Pre-generated — served from a saved copy (<1ms)', 'third-audience' ), '#34c759' ),
										'HIT'           => array( '⚡', __( 'Cached', 'third-audience' ),  __( 'Served from cache (1–5ms)', 'third-audience' ), '#34c759' ),
										'MISS'          => array( '🕐', __( 'Fresh', 'third-audience' ),   __( 'Generated fresh on this request (10–50ms)', 'third-audience' ), '#ff9500' ),
										'FAILED'        => array( '⚠', __( 'Failed', 'third-audience' ),  __( 'Generation failed — check System Health', 'third-audience' ), '#ff3b30' ),
									);
									if ( isset( $cache_map[ $cs ] ) ) {
										list( $c_icon, $c_label, $c_tip, $c_color ) = $cache_map[ $cs ];
										printf(
											'<span class="ta-cache-badge" style="color: %s;" title="%s">%s %s</span>',
											esc_attr( $c_color ),
											esc_attr( $c_tip ),
											esc_html( $c_icon ),
											esc_html( $c_label )
										);
									} elseif ( '' === $cs || 'N/A' === $cs ) {
										echo '<span class="ta-cache-badge" style="color:#8e8e93;">—</span>';
									} else {
										// Legacy/other statuses (e.g. MARKDOWN) — show neutrally.
										printf(
											'<span class="ta-cache-badge" style="color:#8e8e93;" title="%s">%s</span>',
											esc_attr( $cs ),
											esc_html( ucfirst( strtolower( $cs ) ) )
										);
									}
									?>
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
			feedNonce: <?php echo wp_json_encode( wp_create_nonce( 'ta_bot_analytics_feed' ) ); ?>,
			managementNonce: <?php echo wp_json_encode( wp_create_nonce( 'ta_bot_management' ) ); ?>
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
require_once __DIR__ . '/components/modals/bot-detail-modal.php';
?>

<script type="text/javascript">
// Session Analytics Drill-Down
jQuery(document).ready(function($) {
	var sessionChart = null;
	var heroChart = null;

	// Hero metrics (top 5 cards + pages_per_session which has its own
	// crawl-depth-by-bot modal instead of the shared fingerprints modal)
	var heroMetrics = ['total_visits', 'pages_crawled', 'cache_hit_rate', 'avg_response', 'verified_bots', 'pages_per_session'];

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
			'verified_bots': '<?php echo esc_js( __( 'Bot Verification - Status Breakdown', 'third-audience' ) ); ?>',
			'pages_per_session': '<?php echo esc_js( __( 'Pages Per Session - Crawl Depth by Bot', 'third-audience' ) ); ?>'
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
			'session_duration': '<?php echo esc_js( __( 'Session Duration - Time Spent Crawling', 'third-audience' ) ); ?>',
			'request_interval': '<?php echo esc_js( __( 'Request Interval - Time Between Requests', 'third-audience' ) ); ?>'
		};
		$('#ta-session-modal-title').text(titles[metric] || '<?php echo esc_js( __( 'Session Analytics Details', 'third-audience' ) ); ?>');

		// Set default sort based on metric
		var sortMap = {
			'fingerprints': 'classification',
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
			// Number() — visit_count arrives as a string from JSON; without this it
			// string-concatenates ("0"+"23"+"160"...) and the chart shows huge values.
			botCounts[botType] = (botCounts[botType] || 0) + Number(fp.visit_count || 0);
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

	// ── Bot Detail Modal (Bot Activity Distribution row click) ────────────────
	var botDetailChart = null;

	$('.ta-bot-dist-row').on('click', function() {
		openBotDetailModal($(this).data('bot-type'), $(this).data('bot-name'));
	});
	$('.ta-bot-dist-row').on('mouseenter', function() {
		$(this).css('background', '#f0f6fc');
	}).on('mouseleave', function() {
		$(this).css('background', '');
	});

	$('.ta-bot-detail-close').on('click', function() { closeBotDetailModal(); });
	$('.ta-bot-detail-overlay').on('click', function(e) {
		if (e.target === this) { closeBotDetailModal(); }
	});
	$(document).on('keydown.botdetail', function(e) {
		if (e.key === 'Escape' && $('.ta-bot-detail-overlay').is(':visible')) { closeBotDetailModal(); }
	});

	function openBotDetailModal(botType, botName) {
		$('.ta-bot-detail-overlay').css('display', 'flex').hide().fadeIn(200);
		$('.ta-bot-detail-loading').show();
		$('.ta-bot-detail-content').hide();
		$('#ta-bot-detail-title').text(botName + ' — Activity');

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'ta_get_bot_details',
				nonce: taAnalyticsData.managementNonce,
				bot_type: botType,
				bot_name: botName
			},
			success: function(r) {
				if (r.success) { renderBotDetail(r.data); }
				else { closeBotDetailModal(); }
			},
			error: function() { closeBotDetailModal(); }
		});
	}

	function closeBotDetailModal() {
		$('.ta-bot-detail-overlay').fadeOut(200);
		if (botDetailChart) { botDetailChart.destroy(); botDetailChart = null; }
	}

	function renderBotDetail(data) {
		var s     = data.summary || {};
		var pages = data.top_pages || [];
		var ipData = data.ip_data || [];
		var resp  = data.response_distribution || {};

		// 3 hero stats
		$('#ta-bot-detail-stat1').text(parseInt(s.total_visits || 0).toLocaleString());
		$('#ta-bot-detail-stat2').text(parseInt(s.unique_pages || 0).toLocaleString());
		$('#ta-bot-detail-stat3').text(parseFloat(s.cache_hit_rate || 0).toFixed(1) + '%');

		// Top pages doughnut — top 5 + Others bucket
		var topPages   = pages.slice(0, 5);
		var othersSum  = pages.slice(5).reduce(function(sum, p) { return sum + parseInt(p.visits); }, 0);
		var chartLabels = topPages.map(function(p) {
			var t = p.post_title || (p.url.split('/').filter(Boolean).pop()) || p.url;
			return t.length > 28 ? t.substring(0, 25) + '…' : t;
		});
		var chartValues = topPages.map(function(p) { return parseInt(p.visits); });
		if (othersSum > 0) { chartLabels.push('Others'); chartValues.push(othersSum); }

		var colors = ['#007aff', '#34c759', '#ff9500', '#ff3b30', '#af52de', '#8e8e93'];
		var ctx = document.getElementById('ta-bot-detail-chart');
		if (ctx) {
			if (botDetailChart) { botDetailChart.destroy(); }
			botDetailChart = new Chart(ctx, {
				type: 'doughnut',
				data: {
					labels: chartLabels,
					datasets: [{ data: chartValues, backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }]
				},
				options: {
					responsive: true,
					maintainAspectRatio: true,
					plugins: { legend: { display: false } },
					cutout: '55%'
				}
			});
		}

		// Legend
		var total = chartValues.reduce(function(a, b) { return a + b; }, 0);
		var legendHtml = chartLabels.map(function(label, i) {
			var pct = total > 0 ? Math.round(chartValues[i] / total * 100) : 0;
			return '<div style="display:flex;align-items:center;gap:6px;margin-bottom:5px;">' +
				'<span style="width:8px;height:8px;border-radius:50%;background:' + colors[i] + ';flex-shrink:0;display:inline-block;"></span>' +
				'<span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="' + escapeHtml(label) + '">' + escapeHtml(label) + '</span>' +
				'<strong style="flex-shrink:0;">' + chartValues[i].toLocaleString() + '</strong>' +
				'<span style="color:#8e8e93;font-size:10px;flex-shrink:0;">(' + pct + '%)</span>' +
				'</div>';
		}).join('');
		$('#ta-bot-detail-legend').html(legendHtml || '<span style="color:#8e8e93;">No page data</span>');

		// Countries from ip_data
		var countryMap = {};
		ipData.forEach(function(ip) {
			var cc = ip.country_code || 'Unknown';
			countryMap[cc] = (countryMap[cc] || 0) + parseInt(ip.visit_count);
		});
		var countryEntries = Object.entries ? Object.entries(countryMap) : Object.keys(countryMap).map(function(k) { return [k, countryMap[k]]; });
		countryEntries.sort(function(a, b) { return b[1] - a[1]; });
		countryEntries = countryEntries.slice(0, 6);
		var maxC = countryEntries.length > 0 ? countryEntries[0][1] : 1;
		var countryHtml = countryEntries.map(function(e) {
			var barW = Math.round(e[1] / maxC * 100);
			return '<div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;">' +
				'<span style="width:32px;text-align:right;font-size:11px;color:#646970;flex-shrink:0;">' + escapeHtml(e[0]) + '</span>' +
				'<div style="flex:1;background:#e5e5ea;border-radius:3px;height:6px;overflow:hidden;">' +
				'<div style="background:#34c759;width:' + barW + '%;height:100%;border-radius:3px;"></div></div>' +
				'<strong style="width:28px;text-align:right;font-size:11px;flex-shrink:0;">' + e[1].toLocaleString() + '</strong>' +
				'</div>';
		}).join('');
		$('#ta-bot-detail-countries').html(countryHtml || '<span style="color:#8e8e93;font-size:12px;">No country data</span>');

		// Activity dates
		var firstSeen = s.first_seen ? new Date(s.first_seen).toLocaleDateString('en-US', {month:'short',day:'numeric',year:'numeric'}) : '—';
		var lastSeen  = s.last_seen  ? new Date(s.last_seen).toLocaleDateString('en-US',  {month:'short',day:'numeric',year:'numeric'}) : '—';
		var avgResp   = s.avg_response_time ? Math.round(parseFloat(s.avg_response_time)) + 'ms' : '—';
		$('#ta-bot-detail-activity').html(
			'<div>📅 First seen: <strong>' + firstSeen + '</strong></div>' +
			'<div>🕐 Last visit: <strong>' + lastSeen + '</strong></div>' +
			'<div>⚡ Avg response: <strong>' + avgResp + '</strong></div>'
		);

		// Response time 4-box grid
		var respItems = [
			{ label: 'Under 50ms',  value: parseInt(resp.fast_under_50  || 0), color: '#34c759' },
			{ label: '50–100ms',    value: parseInt(resp.good_50_100    || 0), color: '#007aff' },
			{ label: '100–200ms',   value: parseInt(resp.ok_100_200     || 0), color: '#ff9500' },
			{ label: '200ms+',      value: parseInt(resp.slow_over_200  || 0), color: '#ff3b30' }
		];
		var totalResp = respItems.reduce(function(sum, r) { return sum + r.value; }, 0);
		var respHtml = respItems.map(function(item) {
			var pct = totalResp > 0 ? Math.round(item.value / totalResp * 100) : 0;
			return '<div style="text-align:center;background:#f5f5f7;border-radius:8px;padding:12px 8px;">' +
				'<div style="font-size:22px;font-weight:700;color:' + item.color + ';">' + pct + '%</div>' +
				'<div style="font-size:11px;color:#3c434a;margin-top:3px;font-weight:500;">' + item.label + '</div>' +
				'<div style="font-size:10px;color:#8e8e93;margin-top:2px;">' + item.value.toLocaleString() + ' req</div>' +
				'</div>';
		}).join('');
		$('#ta-bot-detail-response').html(respHtml);

		$('.ta-bot-detail-loading').hide();
		$('.ta-bot-detail-content').show();
	}
	// ─────────────────────────────────────────────────────────────────────────

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
				if (period.startsWith('Week ')) { // Weekly: Week 05, 2026
					return period; // Already formatted nicely
				} else if (period.length === 10) { // Daily: 2026-01-23
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

// JS-based engagement badge tooltip (fixed position — avoids table overflow clipping)
(function() {
	var tip = document.createElement('div');
	tip.id = 'ta-engagement-tooltip';
	tip.style.cssText = 'position:fixed;z-index:999999;background:#1d2327;color:#fff;font-size:12px;line-height:1.5;padding:8px 12px;border-radius:6px;width:260px;white-space:normal;text-align:left;pointer-events:none;box-shadow:0 4px 12px rgba(0,0,0,0.3);display:none;';
	document.body.appendChild(tip);

	document.addEventListener('mouseover', function(e) {
		var badge = e.target.closest ? e.target.closest('.ta-engagement-badge') : null;
		if (!badge) return;
		var text = badge.getAttribute('data-tip');
		if (!text) return;
		tip.textContent = text;
		tip.style.display = 'block';
	});

	document.addEventListener('mousemove', function(e) {
		if (tip.style.display === 'none') return;
		var x = e.clientX;
		var y = e.clientY;
		var tw = 260;
		var th = tip.offsetHeight;
		// keep within viewport horizontally
		var left = Math.min(x - tw + 10, window.innerWidth - tw - 10);
		left = Math.max(left, 10);
		var top = y - th - 12;
		if (top < 10) top = y + 16;
		tip.style.left = left + 'px';
		tip.style.top  = top  + 'px';
	});

	document.addEventListener('mouseout', function(e) {
		var badge = e.target.closest ? e.target.closest('.ta-engagement-badge') : null;
		if (badge) tip.style.display = 'none';
	});
})();
</script>
