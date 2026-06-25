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

// Drill-down dimension breakdowns (Layout A 6-card grid).
$dim_format    = $analytics->get_breakdown( 'format', $bot_filters );
$dim_pagetype  = $analytics->get_breakdown( 'pagetype', $bot_filters );
$dim_country   = $analytics->get_breakdown( 'country', $bot_filters );
$dim_status    = $analytics->get_breakdown( 'status', $bot_filters );

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


// Rate limiting.
$rate_limiter      = new TA_Rate_Limiter();
$recent_violations = $rate_limiter->get_rate_limit_violations( 10 );
?>

<div class="wrap ta-bot-analytics">
	<?php // Place WP admin notices ABOVE our heading (this marker tells core where the header ends), so update notices don't break the title/button row. ?>
	<hr class="wp-header-end" style="margin:0;border:0;height:0;">
	<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
		<h1 class="wp-heading-inline">
			<?php esc_html_e( 'Bot Analytics', 'third-audience' ); ?>
			<span style="font-size: 0.6em; color: #646970; font-weight: 400;">v<?php echo esc_html( TA_VERSION ); ?></span>
		</h1>
		<button type="button" class="ta-clear-bot-crawls" style="display:inline-flex;align-items:center;gap:6px;background:#d63638;color:#fff;border:0;padding:8px 14px;border-radius:6px;font-size:13px;font-weight:600;line-height:1;cursor:pointer;white-space:nowrap;box-shadow:0 1px 2px rgba(214,54,56,.35);" onmouseover="this.style.background='#b32d2e'" onmouseout="this.style.background='#d63638'">
			<span class="dashicons dashicons-trash" style="font-size:16px;width:16px;height:16px;"></span>
			<?php esc_html_e( 'Clear Bot Crawl Data', 'third-audience' ); ?>
		</button>
	</div>
	<p class="description"><?php esc_html_e( 'Track which AI bots are crawling your site, how deeply they read your content, and how often that crawling turns into real user traffic from AI platforms.', 'third-audience' ); ?></p>

	<!-- Date range bar (presets + custom) — matches LLM Traffic -->
	<?php
	$ta_today = current_time( 'Y-m-d' );
	$ta_d7    = gmdate( 'Y-m-d', strtotime( '-6 days', current_time( 'timestamp' ) ) );
	$ta_d30   = gmdate( 'Y-m-d', strtotime( '-29 days', current_time( 'timestamp' ) ) );
	$ta_from  = $filters['date_from'] ?? '';
	$ta_to    = $filters['date_to'] ?? '';
	$ta_keep  = array( 'bot_type', 'content_type', 'search' );
	$ta_base  = array( 'page' => 'third-audience-bot-analytics' );
	foreach ( $ta_keep as $ta_k ) {
		if ( ! empty( $filters[ $ta_k ] ) ) {
			$ta_base[ $ta_k ] = $filters[ $ta_k ];
		}
	}
	$ta_url = function ( $from, $to ) use ( $ta_base ) {
		$args = $ta_base;
		if ( $from ) {
			$args['date_from'] = $from;
		}
		if ( $to ) {
			$args['date_to'] = $to;
		}
		return esc_url( add_query_arg( $args, admin_url( 'admin.php' ) ) );
	};
	$ta_is_all   = ( empty( $ta_from ) && empty( $ta_to ) );
	$ta_is_today = ( $ta_from === $ta_today && $ta_to === $ta_today );
	$ta_is_7     = ( $ta_from === $ta_d7 && $ta_to === $ta_today );
	$ta_is_30    = ( $ta_from === $ta_d30 && $ta_to === $ta_today );
	$ta_btn      = 'display:inline-block;padding:6px 13px;border-radius:7px;font-size:13px;font-weight:500;text-decoration:none;color:#646970;';
	$ta_act      = 'background:#fff;color:#1d1d1f;box-shadow:0 1px 3px rgba(0,0,0,.12);';
	?>
	<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;background:#fff;border:1px solid #e5e5ea;border-radius:12px;padding:12px 14px;margin:12px 0 16px;">
		<span style="font-weight:600;color:#646970;">📅 <?php esc_html_e( 'Date', 'third-audience' ); ?></span>
		<span style="display:inline-flex;background:#f2f2f7;border-radius:9px;padding:3px;gap:2px;">
			<a href="<?php echo $ta_url( '', '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>" style="<?php echo esc_attr( $ta_btn . ( $ta_is_all ? $ta_act : '' ) ); ?>"><?php esc_html_e( 'All Time', 'third-audience' ); ?></a>
			<a href="<?php echo $ta_url( $ta_today, $ta_today ); // phpcs:ignore WordPress.Security.EscapeOutput ?>" style="<?php echo esc_attr( $ta_btn . ( $ta_is_today ? $ta_act : '' ) ); ?>"><?php esc_html_e( 'Today', 'third-audience' ); ?></a>
			<a href="<?php echo $ta_url( $ta_d7, $ta_today ); // phpcs:ignore WordPress.Security.EscapeOutput ?>" style="<?php echo esc_attr( $ta_btn . ( $ta_is_7 ? $ta_act : '' ) ); ?>"><?php esc_html_e( 'Last 7 Days', 'third-audience' ); ?></a>
			<a href="<?php echo $ta_url( $ta_d30, $ta_today ); // phpcs:ignore WordPress.Security.EscapeOutput ?>" style="<?php echo esc_attr( $ta_btn . ( $ta_is_30 ? $ta_act : '' ) ); ?>"><?php esc_html_e( 'Last 30 Days', 'third-audience' ); ?></a>
		</span>
		<form method="get" style="display:flex;align-items:center;gap:6px;margin-left:auto;">
			<input type="hidden" name="page" value="third-audience-bot-analytics">
			<?php foreach ( $ta_keep as $ta_k ) : ?>
				<?php if ( ! empty( $filters[ $ta_k ] ) ) : ?>
					<input type="hidden" name="<?php echo esc_attr( $ta_k ); ?>" value="<?php echo esc_attr( $filters[ $ta_k ] ); ?>">
				<?php endif; ?>
			<?php endforeach; ?>
			<span style="width:1px;height:24px;background:#e5e5ea;margin:0 4px;"></span>
			<span style="font-size:12px;color:#646970;font-weight:500;display:flex;align-items:center;gap:5px;">📆 <?php esc_html_e( 'Pick a range', 'third-audience' ); ?></span>
			<input type="date" name="date_from" value="<?php echo esc_attr( $ta_from ); ?>" style="border:1px solid #e5e5ea;border-radius:7px;padding:5px 8px;font-size:13px;">
			<span>—</span>
			<input type="date" name="date_to" value="<?php echo esc_attr( $ta_to ); ?>" style="border:1px solid #e5e5ea;border-radius:7px;padding:5px 8px;font-size:13px;">
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Apply', 'third-audience' ); ?></button>
		</form>
	</div>

	<!-- Ops/Monitor Console — Crawl Health + vitals + request load (Layout A) -->
	<?php
	// ===== Crawl Health score (cache 45% · speed 30% · error-free 25%) =====
	$ta_cache_s = (float) $summary['cache_hit_rate'];
	$ta_speed_s = max( 0, min( 100, 100 - ( $summary['avg_response_time'] - 2 ) / 3 ) );
	$ta_err_s   = max( 0, 100 - $summary['error_rate'] * 8 );
	$ta_health  = (int) round( $ta_cache_s * 0.45 + $ta_speed_s * 0.30 + $ta_err_s * 0.25 );
	$ta_verdict = $ta_health >= 85 ? 'GOOD' : ( $ta_health >= 65 ? 'FAIR' : 'POOR' );
	$ta_hcolor  = $ta_health >= 85 ? '#5fe39a' : ( $ta_health >= 65 ? '#ffc15e' : '#ff8a7d' );
	$ta_circ    = 389.557; // 2 * pi * 62
	$ta_offset  = $ta_circ * ( 1 - $ta_health / 100 );
	$ta_band_mb = round( $summary['total_bandwidth'] / 1048576, 1 );
	$ta_depth   = number_format( (float) $session_stats['avg_pages_per_session'], 1 );

	// Request-load sparkline (chronological).
	$ta_load     = array_reverse( $visits_time );
	$ta_load_max = 1;
	foreach ( $ta_load as $ta_r ) {
		$ta_load_max = max( $ta_load_max, (int) $ta_r['visits'] );
	}

	// Vital tile helper.
	$ta_vital = function ( $label, $value, $tone ) {
		return '<div class="ta-vital ta-vital--' . esc_attr( $tone ) . '"><div class="ta-vl">' . esc_html( $label ) . '</div><div class="ta-vv">' . wp_kses_post( $value ) . '</div></div>';
	};
	?>
	<style>
		.ta-console{background:linear-gradient(135deg,#0b1220,#11203a);border-radius:16px;padding:20px 22px;color:#dbe6f5;margin:16px 0;display:grid;grid-template-columns:230px 1fr;gap:24px;box-shadow:0 8px 30px rgba(8,18,40,.35);}
		.ta-gauge{display:flex;flex-direction:column;align-items:center;justify-content:center;position:relative;}
		.ta-gauge svg{transform:rotate(-90deg);}
		.ta-gauge-mid{margin-top:-104px;text-align:center;display:flex;flex-direction:column;align-items:center;}
		.ta-gauge-score{font-size:42px;font-weight:800;letter-spacing:-.03em;line-height:1;}
		.ta-gauge-verdict{font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;margin-top:2px;}
		.ta-gauge-cap{font-size:11px;color:#8aa0c4;margin-top:46px;text-align:center;line-height:1.4;}
		.ta-console-right{display:flex;flex-direction:column;gap:14px;}
		.ta-vitals{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;}
		.ta-vital{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:11px;padding:11px 13px;}
		.ta-vl{font-size:10px;color:#8aa0c4;text-transform:uppercase;letter-spacing:.06em;font-weight:700;}
		.ta-vv{font-size:21px;font-weight:800;margin-top:4px;letter-spacing:-.01em;}
		.ta-vv small{font-size:12px;color:#8aa0c4;font-weight:600;}
		.ta-vital--good .ta-vv{color:#5fe39a;}.ta-vital--warn .ta-vv{color:#ffc15e;}.ta-vital--bad .ta-vv{color:#ff8a7d;}
		.ta-loadstrip h3{margin:0 0 6px;font-size:11px;color:#8aa0c4;text-transform:uppercase;letter-spacing:.06em;font-weight:700;display:flex;justify-content:space-between;}
		.ta-spark{display:flex;align-items:flex-end;gap:3px;height:46px;}
		.ta-spark span{flex:1;background:linear-gradient(180deg,#4d9bff,#2d6fd6);border-radius:3px 3px 0 0;min-height:3px;}
		.ta-spark span.peak{background:linear-gradient(180deg,#ffc15e,#ff9500);}
		@media(max-width:980px){.ta-console{grid-template-columns:1fr}.ta-vitals{grid-template-columns:repeat(2,1fr)}}
	</style>
	<div class="ta-console">
		<div class="ta-gauge">
			<svg width="150" height="150" viewBox="0 0 150 150">
				<circle cx="75" cy="75" r="62" fill="none" stroke="rgba(255,255,255,.10)" stroke-width="14"/>
				<circle cx="75" cy="75" r="62" fill="none" stroke="<?php echo esc_attr( $ta_hcolor ); ?>" stroke-width="14" stroke-linecap="round" stroke-dasharray="<?php echo esc_attr( $ta_circ ); ?>" stroke-dashoffset="<?php echo esc_attr( $ta_offset ); ?>"/>
			</svg>
			<div class="ta-gauge-mid">
				<div class="ta-gauge-score"><?php echo esc_html( $ta_health ); ?></div>
				<div class="ta-gauge-verdict" style="color:<?php echo esc_attr( $ta_hcolor ); ?>"><?php echo esc_html( $ta_verdict ); ?></div>
			</div>
			<div class="ta-gauge-cap"><?php esc_html_e( 'Crawl Health Score', 'third-audience' ); ?><br>cache + speed + error-free</div>
		</div>
		<div class="ta-console-right">
			<div class="ta-vitals">
				<?php
				// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- helper escapes internally.
				echo $ta_vital( __( 'Total Bot Visits', 'third-audience' ), number_format( $summary['total_visits'] ), 'good' );
				echo $ta_vital( __( 'Cache hit', 'third-audience' ), $summary['cache_hit_rate'] . '<small>%</small>', $summary['cache_hit_rate'] >= 90 ? 'good' : ( $summary['cache_hit_rate'] >= 70 ? 'warn' : 'bad' ) );
				echo $ta_vital( __( 'Avg response', 'third-audience' ), $summary['avg_response_time'] . '<small>ms</small>', $summary['avg_response_time'] <= 60 ? 'good' : ( $summary['avg_response_time'] <= 150 ? 'warn' : 'bad' ) );
				echo $ta_vital( __( 'Verified bots', 'third-audience' ), $summary['ip_verified_percentage'] . '<small>%</small>', $summary['ip_verified_percentage'] >= 70 ? 'good' : ( $summary['ip_verified_percentage'] >= 40 ? 'warn' : 'bad' ) );
				echo $ta_vital( __( '.md served', 'third-audience' ), $summary['md_served_rate'] . '<small>%</small>', 'good' );
				echo $ta_vital( __( 'Crawl depth', 'third-audience' ), $ta_depth . '<small> pg</small>', (float) $session_stats['avg_pages_per_session'] >= 3 ? 'good' : 'warn' );
				echo $ta_vital( __( 'Bandwidth', 'third-audience' ), $ta_band_mb . '<small> MB</small>', 'good' );
				echo $ta_vital( __( 'Error rate', 'third-audience' ), $summary['error_rate'] . '<small>%</small>', $summary['error_rate'] <= 2 ? 'good' : ( $summary['error_rate'] <= 6 ? 'warn' : 'bad' ) );
				// phpcs:enable
				?>
			</div>
			<div class="ta-loadstrip">
				<h3><span><?php esc_html_e( 'Request load — crawls / day', 'third-audience' ); ?></span><span style="color:#ffc15e;"><?php echo esc_html( 'peak ' . number_format( $ta_load_max ) ); ?></span></h3>
				<div class="ta-spark">
					<?php
					foreach ( $ta_load as $ta_r ) {
						$ta_v = (int) $ta_r['visits'];
						$ta_h = max( 3, (int) round( $ta_v / $ta_load_max * 46 ) );
						$ta_peak = ( $ta_v === $ta_load_max ) ? ' class="peak"' : '';
						echo '<span' . $ta_peak . ' style="height:' . esc_attr( $ta_h ) . 'px" title="' . esc_attr( $ta_r['period'] . ': ' . number_format( $ta_v ) ) . '"></span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?>
				</div>
			</div>
		</div>
	</div>

	<!-- Live Activity Feed (collapses when a date filter is active — like LLM Traffic) -->
	<?php $ta_feed_collapsed = ( ! empty( $filters['date_from'] ) || ! empty( $filters['date_to'] ) ); ?>
	<style>
		.ta-feed-card .ta-card-header h2{cursor:pointer;}
		.ta-feed-twist{transition:transform .18s;color:#9b9ba0;}
		.ta-feed-card.ta-feed-collapsed .ta-feed-twist{transform:rotate(-90deg);}
		.ta-feed-card.ta-feed-collapsed .ta-card-body{display:none !important;}
		.ta-feed-note{display:none;font-size:12px;color:#9b7a17;background:#fff7e6;border:1px solid #ffe6a8;border-radius:20px;padding:2px 10px;margin-left:8px;font-weight:500;}
		.ta-feed-card.ta-feed-collapsed .ta-feed-note{display:inline-block;}
	</style>
	<div class="ta-card ta-feed-card<?php echo $ta_feed_collapsed ? ' ta-feed-collapsed' : ''; ?>" id="ta-feed-card">
		<div class="ta-card-header ta-card-header--green">
			<h2>
				<span class="ta-live-indicator"></span>
				<?php esc_html_e( 'Bot Crawl Feed', 'third-audience' ); ?>
				<span class="ta-feed-twist dashicons dashicons-arrow-down-alt2"></span>
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

	<!-- Drill-down dimension cards (6) — Layout A grid, unified click → popup -->
	<?php
	$ta_total      = max( 1, (int) $summary['total_visits'] );
	$ta_fmt_labels = array(
		'markdown' => __( 'Markdown (.md)', 'third-audience' ),
		'text'     => __( 'Text (.txt)', 'third-audience' ),
		'html'     => __( 'HTML (full page)', 'third-audience' ),
	);

	// Normalise every dimension to { key, label, count }.
	$ta_card_bots = array();
	foreach ( (array) $bot_stats as $b ) {
		$ta_card_bots[] = array( 'key' => $b['bot_type'], 'label' => $b['bot_name'], 'count' => (int) $b['count'] );
	}
	$ta_card_pages = array();
	foreach ( (array) $top_pages as $p ) {
		$ta_card_pages[] = array( 'key' => $p['url'], 'label' => ta_page_display_title( $p['post_title'] ?? '', $p['url'] ), 'count' => (int) $p['visits'] );
	}
	$ta_card_format = array();
	foreach ( (array) $dim_format as $r ) {
		$ta_card_format[] = array( 'key' => $r['key'], 'label' => $ta_fmt_labels[ strtolower( $r['key'] ) ] ?? ucfirst( $r['key'] ), 'count' => $r['count'] );
	}
	$ta_card_pagetype = array();
	foreach ( (array) $dim_pagetype as $r ) {
		$ta_card_pagetype[] = array( 'key' => $r['key'], 'label' => ucwords( str_replace( array( '-', '_' ), ' ', $r['key'] ) ), 'count' => $r['count'] );
	}
	$ta_card_country = array();
	foreach ( (array) $dim_country as $r ) {
		$ta_card_country[] = array( 'key' => $r['key'], 'label' => strtoupper( $r['key'] ), 'count' => $r['count'] );
	}
	$ta_card_status = array();
	foreach ( (array) $dim_status as $r ) {
		$ta_card_status[] = array( 'key' => $r['key'], 'label' => 'HTTP ' . $r['key'], 'count' => $r['count'] );
	}

	$ta_cards = array(
		array( 'icon' => '🤖', 'title' => __( 'Bots', 'third-audience' ),               'dim' => 'bot',      'accent' => '#5856d6', 'items' => $ta_card_bots,     'hint' => __( 'Crawls grouped by AI bot.', 'third-audience' ) ),
		array( 'icon' => '📄', 'title' => __( 'Most Crawled Pages', 'third-audience' ),  'dim' => 'page',     'accent' => '#ff9500', 'items' => $ta_card_pages,    'hint' => __( 'Most-read pages.', 'third-audience' ) ),
		array( 'icon' => '🧾', 'title' => __( 'Content Format', 'third-audience' ),      'dim' => 'format',   'accent' => '#1FB6D0', 'items' => $ta_card_format,   'hint' => __( '.md / .txt vs HTML served.', 'third-audience' ) ),
		array( 'icon' => '🏠', 'title' => __( 'Page Type', 'third-audience' ),           'dim' => 'pagetype', 'accent' => '#af52de', 'items' => $ta_card_pagetype, 'hint' => __( 'By post type.', 'third-audience' ) ),
		array( 'icon' => '🌍', 'title' => __( 'Crawler Location', 'third-audience' ),    'dim' => 'country',  'accent' => '#007aff', 'items' => $ta_card_country,  'hint' => __( 'Datacenter the bot IP is in.', 'third-audience' ) ),
		array( 'icon' => '🚦', 'title' => __( 'Response Status', 'third-audience' ),     'dim' => 'status',   'accent' => '#34c759', 'items' => $ta_card_status,   'hint' => __( '200 / 304 / 404 served.', 'third-audience' ) ),
	);
	?>
	<style>
		.ta-ddc-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin:8px 0 4px;}
		.ta-ddc-card{background:#fff;border:1px solid #e5e5ea;border-radius:14px;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,.04);}
		.ta-ddc-card h2{font-size:14px;margin:0;padding:13px 16px;border-bottom:1px solid #e5e5ea;display:flex;align-items:center;gap:8px;}
		.ta-ddc-ico{font-size:15px;}
		.ta-ddc-cnt{margin-left:auto;font-size:12px;font-weight:600;color:#646970;background:#f2f2f7;border-radius:20px;padding:2px 10px;}
		.ta-ddc-rows{padding:6px 8px;}
		.ta-ddc-row{display:flex;align-items:center;gap:10px;padding:9px 8px;border-radius:8px;cursor:pointer;}
		.ta-ddc-row:hover{background:#f5f8ff;}
		.ta-ddc-dot{width:9px;height:9px;border-radius:50%;flex:none;}
		.ta-ddc-name{font-weight:500;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:130px;}
		.ta-ddc-bar{flex:1;height:6px;background:#eef0f2;border-radius:4px;overflow:hidden;}
		.ta-ddc-bar>span{display:block;height:100%;border-radius:4px;}
		.ta-ddc-num{font-weight:700;width:52px;text-align:right;}
		.ta-ddc-pct{width:46px;text-align:right;color:#646970;font-size:12px;}
		.ta-ddc-chev{color:#c7c7cc;}
		.ta-ddc-hint{font-size:11px;color:#9b9ba0;padding:4px 14px 12px;}
		.ta-ddc-scrim{position:fixed;inset:0;background:rgba(15,17,21,.45);opacity:0;pointer-events:none;transition:opacity .18s;z-index:99998;display:flex;align-items:center;justify-content:center;padding:24px;}
		.ta-ddc-scrim.open{opacity:1;pointer-events:auto;}
		.ta-ddc-modal{position:relative;background:#fff;width:min(1000px,96vw);max-height:88vh;border-radius:16px;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 30px 80px rgba(0,0,0,.35);}
		.ta-ddc-mhead{padding:18px 24px 16px;border-bottom:1px solid #e5e5ea;background:linear-gradient(180deg,#f6f9ff,#fff);}
		.ta-ddc-crumb{font-size:11px;color:#646970;text-transform:uppercase;letter-spacing:.06em;font-weight:700;}
		.ta-ddc-modal h3{margin:6px 0 0;font-size:21px;padding-right:40px;}
		.ta-ddc-close{position:absolute;top:13px;right:13px;border:0;background:#eceef2;width:34px;height:34px;border-radius:9px;font-size:18px;cursor:pointer;color:#646970;line-height:1;}
		.ta-ddc-close:hover{background:#e1e4ea;}
		.ta-ddc-mbody{overflow:auto;}
		.ta-ddc-load{padding:34px;text-align:center;color:#8e8e93;}
		.ta-ddc-table{width:100%;border-collapse:collapse;}
		.ta-ddc-table th,.ta-ddc-table td{text-align:left;padding:9px 16px;font-size:12.5px;border-bottom:1px solid #f0f0f1;}
		.ta-ddc-table th{position:sticky;top:0;background:#fafafa;color:#646970;text-transform:uppercase;font-size:11px;}
		.ta-ddc-foot{display:flex;align-items:center;justify-content:center;gap:14px;padding:13px;border-top:1px solid #e5e5ea;font-size:13px;color:#646970;}
		.ta-ddc-foot button{border:1px solid #e5e5ea;background:#fff;border-radius:7px;padding:6px 13px;cursor:pointer;}
		.ta-ddc-foot button:disabled{opacity:.4;cursor:default;}
		@media(max-width:980px){.ta-ddc-grid{grid-template-columns:repeat(2,1fr)}}
		@media(max-width:680px){.ta-ddc-grid{grid-template-columns:1fr}}
	</style>
	<div class="ta-ddc-grid">
		<?php
		foreach ( $ta_cards as $c ) :
			$ta_max = 1;
			foreach ( $c['items'] as $it ) {
				$ta_max = max( $ta_max, $it['count'] );
			}
			?>
			<div class="ta-ddc-card">
				<h2><span class="ta-ddc-ico"><?php echo esc_html( $c['icon'] ); ?></span><?php echo esc_html( $c['title'] ); ?><span class="ta-ddc-cnt"><?php echo (int) count( $c['items'] ); ?></span></h2>
				<div class="ta-ddc-rows">
					<?php if ( empty( $c['items'] ) ) : ?>
						<div class="ta-ddc-hint"><?php esc_html_e( 'No data in range.', 'third-audience' ); ?></div>
					<?php else : ?>
						<?php
						foreach ( array_slice( $c['items'], 0, 8 ) as $it ) :
							$ta_w   = (int) round( $it['count'] / $ta_max * 100 );
							$ta_pct = round( $it['count'] / $ta_total * 100, 1 );
							?>
							<div class="ta-ddc-row" data-dim="<?php echo esc_attr( $c['dim'] ); ?>" data-val="<?php echo esc_attr( $it['key'] ); ?>" data-label="<?php echo esc_attr( $it['label'] ); ?>">
								<span class="ta-ddc-dot" style="background:<?php echo esc_attr( $c['accent'] ); ?>"></span>
								<span class="ta-ddc-name" title="<?php echo esc_attr( $it['label'] ); ?>"><?php echo esc_html( $it['label'] ); ?></span>
								<span class="ta-ddc-bar"><span style="width:<?php echo esc_attr( $ta_w ); ?>%;background:<?php echo esc_attr( $c['accent'] ); ?>"></span></span>
								<span class="ta-ddc-num"><?php echo esc_html( number_format( $it['count'] ) ); ?></span>
								<span class="ta-ddc-pct"><?php echo esc_html( $ta_pct ); ?>%</span>
								<span class="ta-ddc-chev">›</span>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
				<div class="ta-ddc-hint"><?php echo esc_html( $c['hint'] ); ?> <?php esc_html_e( 'Click a row → popup.', 'third-audience' ); ?></div>
			</div>
		<?php endforeach; ?>
	</div>

	<!-- drill-down popup -->
	<div class="ta-ddc-scrim" id="ta-ddc-scrim">
		<div class="ta-ddc-modal">
			<button class="ta-ddc-close" id="ta-ddc-close" aria-label="Close">&times;</button>
			<div class="ta-ddc-mhead">
				<span class="ta-ddc-crumb" id="ta-ddc-crumb"></span>
				<h3 id="ta-ddc-title"></h3>
			</div>
			<div class="ta-ddc-mbody" id="ta-ddc-body"></div>
		</div>
	</div>

	<script>
	(function($){
		var DD = { dates: <?php echo wp_json_encode( array( 'date_from' => $filters['date_from'] ?? '', 'date_to' => $filters['date_to'] ?? '' ) ); ?> };
		function esc(s){ return $('<div>').text( s == null ? '' : String(s) ).html(); }
		function nf(n){ return ( parseInt(n,10) || 0 ).toLocaleString(); }
		function openDD(dim,val,label){
			$('#ta-ddc-crumb').text( String(dim).toUpperCase() + ' · DRILL-DOWN' );
			$('#ta-ddc-title').text(label);
			$('#ta-ddc-body').html('<div class="ta-ddc-load">Loading…</div>');
			$('#ta-ddc-scrim').addClass('open');
			loadDD(dim,val,1);
		}
		function loadDD(dim,val,page){
			$.post( taAnalyticsData.ajaxUrl, $.extend({ action:'ta_bot_drilldown', nonce: taAnalyticsData.nonce, dim:dim, val:val, page:page }, DD.dates), function(res){
				if(!res || !res.success){ $('#ta-ddc-body').html('<div class="ta-ddc-load">Could not load.</div>'); return; }
				renderDD(res.data,dim,val);
			}).fail(function(){ $('#ta-ddc-body').html('<div class="ta-ddc-load">Could not load.</div>'); });
		}
		function renderDD(d,dim,val){
			var h = '<table class="ta-ddc-table"><thead><tr><th>Bot</th><th>Page</th><th>Format</th><th>Loc</th><th>Cache</th><th>Status</th><th>Resp</th><th>Date</th></tr></thead><tbody>';
			if(!d.rows || !d.rows.length){ h += '<tr><td colspan="8" style="text-align:center;color:#8e8e93;padding:24px">No crawls in this range.</td></tr>'; }
			else { d.rows.forEach(function(r){
				h += '<tr><td>'+esc(r.bot_name||r.bot_type)+'</td><td>'+esc(r.post_title||r.url)+'</td><td>'+esc(r.content_type)+'</td><td>'+esc(r.country_code)+'</td><td>'+esc(r.cache_status)+'</td><td>'+esc(r.http_status)+'</td><td>'+esc(r.response_time)+'ms</td><td>'+esc(r.visit_timestamp)+'</td></tr>';
			}); }
			h += '</tbody></table><div class="ta-ddc-foot"><button class="ta-ddc-prev" '+(d.page<=1?'disabled':'')+'>← Prev</button><span>Page '+d.page+' / '+d.total_pages+' · '+nf(d.total)+' total</span><button class="ta-ddc-next" '+(d.page>=d.total_pages?'disabled':'')+'>Next →</button></div>';
			$('#ta-ddc-body').html(h);
			$('#ta-ddc-body .ta-ddc-prev').on('click',function(){ if(d.page>1) loadDD(dim,val,d.page-1); });
			$('#ta-ddc-body .ta-ddc-next').on('click',function(){ if(d.page<d.total_pages) loadDD(dim,val,d.page+1); });
		}
		$(document).on('click','.ta-ddc-row',function(){ openDD( $(this).data('dim'), String( $(this).data('val') ), $(this).data('label') ); });
		$(document).on('click','#ta-ddc-close',function(){ $('#ta-ddc-scrim').removeClass('open'); });
		$('#ta-ddc-scrim').on('click',function(e){ if(e.target===this) $(this).removeClass('open'); });
		$(document).on('keydown',function(e){ if(e.key==='Escape') $('#ta-ddc-scrim').removeClass('open'); });
	})(jQuery);
	</script>

	<!-- Session Activity Cards -->
	<div class="ta-cards-container">
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

		jQuery(document).ready(function($) {
			// Bot Crawl Feed — click the title to collapse/expand (auto-collapsed when a date filter is active).
			$('#ta-feed-card').on('click', '.ta-card-header h2', function() {
				$('#ta-feed-card').toggleClass('ta-feed-collapsed');
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
							fill: false,
							tension: 0.3,
							borderWidth: 2,
							pointRadius: 4,
							pointHoverRadius: 6,
							yAxisID: 'y2'
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
							y2: {
								beginAtZero: true,
								position: 'right',
								title: { display: true, text: '<?php echo esc_js( __( 'Unique Bots', 'third-audience' ) ); ?>' },
								ticks: { precision: 0 },
								grid: { drawOnChartArea: false }
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
