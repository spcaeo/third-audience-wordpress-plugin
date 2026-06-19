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
// NEW: Browser, Country, Device filters
if ( ! empty( $_GET['browser'] ) ) {
	$filters['browser'] = sanitize_text_field( wp_unslash( $_GET['browser'] ) );
}
if ( ! empty( $_GET['country'] ) ) {
	$filters['country'] = sanitize_text_field( wp_unslash( $_GET['country'] ) );
}
if ( ! empty( $_GET['device'] ) ) {
	$filters['device'] = sanitize_text_field( wp_unslash( $_GET['device'] ) );
}
// Single-date filter (overrides date_from/date_to when set).
if ( ! empty( $_GET['date'] ) ) {
	$filters['date'] = sanitize_text_field( wp_unslash( $_GET['date'] ) );
}

// Build WHERE clause based on filters.
// Include all real citation visits across both direct WordPress and headless setups:
//   - client_user_agent IS NOT NULL  → browser UA confirmed (direct WP via JS, or headless after fix)
//   - OR content_type = 'rest_api'   → headless Next.js via REST API (all records, new + old)
//   - OR content_type = 'ajax'       → headless Next.js via AJAX fallback (all records, new + old)
//   - OR user_agent NOT LIKE 'Headless%' → old direct WP records without client_user_agent
$where_clauses = array(
	"traffic_type = 'citation_click'",
	"(client_user_agent IS NOT NULL OR content_type IN ('rest_api', 'ajax') OR user_agent NOT LIKE 'Headless%')",
	"url NOT LIKE '%/wp-admin%'",
	"url NOT LIKE '%/wp-login%'",
	"url NOT LIKE '%admin-ajax.php%'",
	"url NOT LIKE '%/wp-cron%'",
	"url NOT LIKE '%/xmlrpc%'",
	// Google Search and Google AI Mode are shown separately — not in LLM/AI citation counts.
	"ai_platform NOT IN ('Google Search', 'Google AI Mode')",
);

if ( ! empty( $filters['platform'] ) ) {
	$where_clauses[] = $wpdb->prepare( 'ai_platform = %s', $filters['platform'] );
}

if ( ! empty( $filters['date_from'] ) ) {
	$where_clauses[] = $wpdb->prepare( 'DATE(visit_timestamp) >= %s', $filters['date_from'] );
}

if ( ! empty( $filters['date_to'] ) ) {
	$where_clauses[] = $wpdb->prepare( 'DATE(visit_timestamp) <= %s', $filters['date_to'] );
}

// Single-date filter (exact day match).
if ( ! empty( $filters['date'] ) ) {
	$where_clauses[] = $wpdb->prepare( 'DATE(visit_timestamp) = %s', $filters['date'] );
}

if ( ! empty( $filters['search'] ) ) {
	$search_term = '%' . $wpdb->esc_like( $filters['search'] ) . '%';
	$where_clauses[] = $wpdb->prepare( '(url LIKE %s OR post_title LIKE %s OR search_query LIKE %s)', $search_term, $search_term, $search_term );
}

// Filter by browser — same bucket logic as the Browsers card + drill-down
// (TA_Citation_Query), so the count and its detail rows always reconcile.
if ( ! empty( $filters['browser'] ) ) {
	$where_clauses[] = TA_Citation_Query::browser_where( $filters['browser'] );
}

// Filter by country code.
if ( ! empty( $filters['country'] ) ) {
	$where_clauses[] = $wpdb->prepare( 'country_code = %s', $filters['country'] );
}

// Filter by device type — same bucket logic as the Devices card / drill-down.
if ( ! empty( $filters['device'] ) ) {
	$where_clauses[] = TA_Citation_Query::device_where( $filters['device'] );
}

// Filter by page type (post_type bucket) — Page Type card / drill-down.
if ( ! empty( $_GET['pagetype'] ) ) {
	$filters['pagetype'] = sanitize_text_field( wp_unslash( $_GET['pagetype'] ) );
	$where_clauses[]     = TA_Citation_Query::pagetype_where( $filters['pagetype'] );
}

// Filter by exact page URL — Top Cited Pages drill-down.
if ( ! empty( $_GET['url'] ) ) {
	$filters['url']  = esc_url_raw( wp_unslash( $_GET['url'] ) );
	$where_clauses[] = $wpdb->prepare( 'url = %s', $filters['url'] );
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

$citations_yesterday = $wpdb->get_var(
	$wpdb->prepare(
		"SELECT COUNT(*) FROM {$table_name} WHERE {$where_sql} AND DATE(visit_timestamp) = %s",
		gmdate( 'Y-m-d', strtotime( '-1 day' ) )
	)
);

$unique_platforms = $wpdb->get_var(
	"SELECT COUNT(DISTINCT ai_platform) FROM {$table_name} WHERE {$where_sql} AND ai_platform IS NOT NULL"
);

$queries_captured = $wpdb->get_var(
	"SELECT COUNT(*) FROM {$table_name} WHERE {$where_sql} AND search_query IS NOT NULL"
);

// Perplexity-specific: count captured search queries (only Perplexity sends query data).
$perplexity_queries = $wpdb->get_var(
	$wpdb->prepare(
		"SELECT COUNT(*) FROM {$table_name} WHERE {$where_sql} AND ai_platform = %s AND search_query IS NOT NULL AND search_query != ''",
		'Perplexity'
	)
);

// Top countries by citation count.
$top_countries = $wpdb->get_results(
	"SELECT
		country_code,
		COUNT(*) as count
	FROM {$table_name}
	WHERE {$where_sql} AND country_code IS NOT NULL AND country_code != ''
	GROUP BY country_code
	ORDER BY count DESC
	LIMIT 10",
	ARRAY_A
);

// Perplexity search queries list (actual query text).
$perplexity_query_list = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT
			search_query,
			url,
			post_title,
			visit_timestamp,
			COUNT(*) as frequency
		FROM {$table_name}
		WHERE {$where_sql} AND ai_platform = %s AND search_query IS NOT NULL AND search_query != ''
		GROUP BY search_query
		ORDER BY frequency DESC, visit_timestamp DESC
		LIMIT 25",
		'Perplexity'
	),
	ARRAY_A
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
// Correct formula: citations as % of all AI interactions (crawls + citations).
// This is always 0-100%. Old formula (citations/crawls) could exceed 100%.
$overall_citation_rate = ( $total_citations + $total_crawls ) > 0
	? round( ( $total_citations / ( $total_citations + $total_crawls ) ) * 100, 1 )
	: 0;

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

// Citations by browser bucket — same CASE as the drill-down, so each card count
// and its detail rows reconcile, and all buckets sum to $total_citations.
$browser_case_sql     = TA_Citation_Query::browser_case();
$citations_by_browser = $wpdb->get_results(
	"SELECT {$browser_case_sql} AS browser, COUNT(*) as count
	FROM {$table_name} WHERE {$where_sql}
	GROUP BY browser ORDER BY count DESC",
	ARRAY_A
);

// Citations by device bucket (mobile / desktop) — sums to $total_citations.
$device_case_sql     = TA_Citation_Query::device_case();
$citations_by_device = $wpdb->get_results(
	"SELECT {$device_case_sql} AS device, COUNT(*) as count
	FROM {$table_name} WHERE {$where_sql}
	GROUP BY device ORDER BY count DESC",
	ARRAY_A
);

// Citations by page type (post_type bucket) — auto-adapts to the site's post types.
$pagetype_case_sql     = TA_Citation_Query::pagetype_case();
$citations_by_pagetype = $wpdb->get_results(
	"SELECT {$pagetype_case_sql} AS pagetype, COUNT(*) as count
	FROM {$table_name} WHERE {$where_sql}
	GROUP BY pagetype ORDER BY count DESC",
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

// Recent LLMs visits (ALL - not just those with queries).
$recent_citations = $wpdb->get_results(
	"SELECT
		ai_platform,
		url,
		post_title,
		search_query,
		referer,
		user_agent,
		client_user_agent,
		ip_address,
		country_code,
		content_type,
		detection_method,
		visit_timestamp
	FROM {$table_name}
	WHERE {$where_sql}
	ORDER BY visit_timestamp DESC
	LIMIT 15",
	ARRAY_A
);

// Organic Search Traffic — Google Search visits (separate from AI citations).
$google_search_base = array(
	"traffic_type = 'citation_click'",
	"ai_platform IN ('Google Search', 'Google AI Mode')",
	"(client_user_agent IS NOT NULL OR content_type IN ('rest_api', 'ajax') OR user_agent NOT LIKE 'Headless%')",
	"url NOT LIKE '%/wp-admin%'",
	"url NOT LIKE '%/wp-login%'",
);
$google_search_sql = implode( ' AND ', $google_search_base );

$google_search_total = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} WHERE {$google_search_sql}" );

$google_search_today = $wpdb->get_var(
	$wpdb->prepare(
		"SELECT COUNT(*) FROM {$table_name} WHERE {$google_search_sql} AND DATE(visit_timestamp) = %s",
		current_time( 'Y-m-d' )
	)
);

$google_ai_mode_total = $wpdb->get_var(
	"SELECT COUNT(*) FROM {$table_name} WHERE traffic_type = 'citation_click' AND ai_platform = 'Google AI Mode'"
);
$google_organic_total = $wpdb->get_var(
	"SELECT COUNT(*) FROM {$table_name} WHERE traffic_type = 'citation_click' AND ai_platform = 'Google Search'"
);

$google_search_visits = $wpdb->get_results(
	"SELECT ai_platform, url, post_title, referer, user_agent, client_user_agent, country_code, content_type, visit_timestamp
	FROM {$table_name}
	WHERE {$google_search_sql}
	ORDER BY visit_timestamp DESC
	LIMIT 15",
	ARRAY_A
);

$llm_per_page       = 15;
$llm_total_pages    = max( 1, (int) ceil( $total_citations / $llm_per_page ) );
$google_per_page    = 15;
$google_total_pages = max( 1, (int) ceil( $google_search_total / $google_per_page ) );

// Crawl → Citation chains: for each recent citation click, find pages the same
// platform's own bot crawled in the 30 minutes BEFORE the click. This links a
// bot crawl session to the human visit it produced — topic-level insight into
// what the AI read before citing us (the user's prompt itself is never sent).
$crawl_chains = $wpdb->get_results(
	"SELECT
		c.id,
		c.ai_platform,
		c.url AS cited_url,
		c.post_title AS cited_title,
		c.visit_timestamp AS click_time,
		GROUP_CONCAT(DISTINCT b.url ORDER BY b.visit_timestamp DESC SEPARATOR '||') AS crawled_urls,
		MAX(b.visit_timestamp) AS last_crawl_time,
		COUNT(DISTINCT b.id) AS crawl_count
	FROM {$table_name} c
	INNER JOIN {$table_name} b
		ON b.traffic_type = 'bot_crawl'
		AND b.visit_timestamp BETWEEN DATE_SUB(c.visit_timestamp, INTERVAL 30 MINUTE) AND c.visit_timestamp
		AND (
			( c.ai_platform = 'ChatGPT' AND ( b.bot_type IN ('ChatGPT-User', 'GPTBot') OR b.user_agent LIKE '%OAI-SearchBot%' ) )
			OR ( c.ai_platform = 'Perplexity' AND b.bot_type = 'PerplexityBot' )
			OR ( c.ai_platform = 'Claude' AND b.bot_type IN ('ClaudeBot', 'anthropic-ai') )
			OR ( c.ai_platform IN ('Gemini', 'Bard (Gemini)') AND b.bot_type = 'Google-Extended' )
			OR ( c.ai_platform IN ('Copilot', 'Bing AI') AND b.bot_type LIKE '%bing%' )
		)
	WHERE c.traffic_type = 'citation_click'
		AND c.ai_platform NOT IN ('Google Search', 'Google AI Mode')
		AND c.visit_timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
		AND c.url NOT LIKE '%/wp-admin%'
		AND c.url NOT LIKE '%admin-ajax.php%'
	GROUP BY c.id, c.ai_platform, c.url, c.post_title, c.visit_timestamp
	ORDER BY c.visit_timestamp DESC
	LIMIT 10",
	ARRAY_A
);

// Broken citation URLs — cited by AI but no matching WP post (likely deleted/renamed pages or pure Next.js routes).
// Used in Fix 2 redirect helper section.
// Broken citations: URLs cited by AI but with no matching WP post resolved at tracking time.
// Uses post_id = 0 (unresolvable URL) AND empty post_title for accuracy — avoids false positives
// where a valid page was tracked but title was temporarily empty.
$broken_citations = $wpdb->get_results(
	"SELECT
		url,
		COUNT(*) as hit_count,
		GROUP_CONCAT(DISTINCT ai_platform ORDER BY ai_platform SEPARATOR ', ') as platforms,
		MAX(visit_timestamp) as last_seen
	FROM {$table_name}
	WHERE traffic_type = 'citation_click'
		AND (post_id IS NULL OR post_id = 0)
		AND (post_title IS NULL OR post_title = '')
		AND url IS NOT NULL AND url != ''
		AND url NOT LIKE '%/wp-admin%'
		AND url NOT LIKE '%/wp-login%'
		AND url NOT LIKE '%admin-ajax.php%'
		AND url NOT LIKE '%/wp-cron%'
		AND url NOT LIKE '%/xmlrpc%'
	GROUP BY url
	ORDER BY hit_count DESC
	LIMIT 20",
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

// Daily Google (organic + AI Mode) for last 30 days — for the trend chart line.
$daily_google = $wpdb->get_results(
	"SELECT DATE(visit_timestamp) as date, COUNT(*) as google
	FROM {$table_name}
	WHERE traffic_type = 'citation_click'
		AND ai_platform IN ('Google Search', 'Google AI Mode')
		AND visit_timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
	GROUP BY DATE(visit_timestamp)
	ORDER BY date ASC",
	ARRAY_A
);

// Daily traffic by request type (HTML / MD / Other) — last 30 days.
// REST API and AJAX fallback records are real browser HTML page visits.
$daily_by_type_rows = $wpdb->get_results(
	"SELECT
		DATE(visit_timestamp) as date,
		CASE
			WHEN request_type = 'html_page' OR request_type = 'js_fallback' OR request_type IS NULL
				OR content_type IN ('rest_api', 'ajax') THEN 'html'
			WHEN request_type LIKE '%markdown%' THEN 'md'
			ELSE 'other'
		END as req_type,
		COUNT(*) as count
	FROM {$table_name}
	WHERE traffic_type = 'citation_click'
		AND visit_timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
	GROUP BY date, req_type
	ORDER BY date ASC",
	ARRAY_A
);

// Build chart data arrays.
$chart_labels      = array();
$chart_citations   = array();
$chart_crawls      = array();
$chart_google      = array();
$chart_html        = array();
$chart_md          = array();
$chart_other       = array();
$citations_by_date = array();
$crawls_by_date    = array();
$google_by_date    = array();
$html_by_date      = array();
$md_by_date        = array();
$other_by_date     = array();

foreach ( $daily_by_type_rows as $row ) {
	if ( 'html' === $row['req_type'] ) {
		$html_by_date[ $row['date'] ] = (int) $row['count'];
	} elseif ( 'md' === $row['req_type'] ) {
		$md_by_date[ $row['date'] ] = (int) $row['count'];
	} else {
		$other_by_date[ $row['date'] ] = isset( $other_by_date[ $row['date'] ] )
			? $other_by_date[ $row['date'] ] + (int) $row['count']
			: (int) $row['count'];
	}
}

// Index by date for easy lookup.
foreach ( $daily_citations as $row ) {
	$citations_by_date[ $row['date'] ] = (int) $row['citations'];
}
foreach ( $daily_crawls as $row ) {
	$crawls_by_date[ $row['date'] ] = (int) $row['crawls'];
}
foreach ( $daily_google as $row ) {
	$google_by_date[ $row['date'] ] = (int) $row['google'];
}

// Generate labels for last 30 days.
for ( $i = 29; $i >= 0; $i-- ) {
	$date              = gmdate( 'Y-m-d', strtotime( "-{$i} days" ) );
	$chart_labels[]    = gmdate( 'M j', strtotime( $date ) );
	$chart_citations[] = isset( $citations_by_date[ $date ] ) ? $citations_by_date[ $date ] : 0;
	$chart_crawls[]    = isset( $crawls_by_date[ $date ] ) ? $crawls_by_date[ $date ] : 0;
	$chart_google[]    = isset( $google_by_date[ $date ] ) ? $google_by_date[ $date ] : 0;
	$chart_html[]      = isset( $html_by_date[ $date ] ) ? $html_by_date[ $date ] : 0;
	$chart_md[]        = isset( $md_by_date[ $date ] ) ? $md_by_date[ $date ] : 0;
	$chart_other[]     = isset( $other_by_date[ $date ] ) ? $other_by_date[ $date ] : 0;
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

// Weekly HTML vs MD traffic (last 4 weeks).
$weekly_data = array();
for ( $week = 3; $week >= 0; $week-- ) {
	// Calculate Monday of X weeks ago.
	$week_start = gmdate( 'Y-m-d', strtotime( "monday -{$week} weeks" ) );
	// Calculate Sunday of that same week (Monday + 6 days).
	$week_end   = gmdate( 'Y-m-d', strtotime( $week_start . ' +6 days' ) );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	// HTML: covers direct WP visits, REST API (headless), AJAX fallback, and legacy nulls.
	$week_html = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$table_name}
			WHERE traffic_type = 'citation_click'
			AND (request_type = 'html_page' OR request_type = 'js_fallback' OR request_type IS NULL
				OR content_type IN ('rest_api', 'ajax'))
			AND DATE(visit_timestamp) BETWEEN %s AND %s",
			$week_start,
			$week_end
		)
	);

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	// MD: bot crawls to .md format URLs (traffic_type = bot_crawl, content_type = markdown).
	$week_md = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$table_name}
			WHERE traffic_type = 'bot_crawl'
			AND (content_type = 'markdown' OR request_method = 'md_url')
			AND DATE(visit_timestamp) BETWEEN %s AND %s",
			$week_start,
			$week_end
		)
	);

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$week_other = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$table_name}
			WHERE traffic_type = 'citation_click'
			AND request_type IS NOT NULL
			AND request_type NOT IN ('html_page', 'js_fallback')
			AND request_type NOT LIKE %s
			AND content_type NOT IN ('rest_api', 'ajax')
			AND DATE(visit_timestamp) BETWEEN %s AND %s",
			'%markdown%',
			$week_start,
			$week_end
		)
	);

	$weekly_data[] = array(
		'label' => 'Week of ' . gmdate( 'M j', strtotime( $week_start ) ),
		'html'  => (int) $week_html,
		'md'    => (int) $week_md,
		'other' => (int) $week_other,
	);
}

/**
 * Parse user agent string to extract browser, OS, and device type.
 *
 * @param string $user_agent Full user agent string.
 * @return array Parsed data with browser, os, device.
 */
function ta_parse_user_agent( $user_agent ) {
	if ( empty( $user_agent ) ) {
		return array(
			'browser' => 'Unknown',
			'os'      => 'Unknown',
			'device'  => 'unknown',
			'icon'    => '❓',
		);
	}

	$browser = 'Unknown';
	$os      = 'Unknown';
	$device  = 'desktop';
	$icon    = '🖥️';

	// Detect Browser
	if ( strpos( $user_agent, 'Edg' ) !== false ) {
		$browser = 'Edge';
	} elseif ( strpos( $user_agent, 'Chrome' ) !== false && strpos( $user_agent, 'Edg' ) === false ) {
		$browser = 'Chrome';
	} elseif ( strpos( $user_agent, 'Firefox' ) !== false ) {
		$browser = 'Firefox';
	} elseif ( strpos( $user_agent, 'Safari' ) !== false && strpos( $user_agent, 'Chrome' ) === false ) {
		$browser = 'Safari';
	} elseif ( strpos( $user_agent, 'Opera' ) !== false || strpos( $user_agent, 'OPR' ) !== false ) {
		$browser = 'Opera';
	}

	// Detect OS - Check specific OS before generic (Android contains "Linux")
	if ( strpos( $user_agent, 'Windows NT 10' ) !== false ) {
		$os = 'Windows 10';
	} elseif ( strpos( $user_agent, 'Windows NT 11' ) !== false ) {
		$os = 'Windows 11';
	} elseif ( strpos( $user_agent, 'Windows' ) !== false ) {
		$os = 'Windows';
	} elseif ( strpos( $user_agent, 'Android' ) !== false ) {
		$os = 'Android';
	} elseif ( strpos( $user_agent, 'iPhone' ) !== false ) {
		$os = 'iOS (iPhone)';
	} elseif ( strpos( $user_agent, 'iPad' ) !== false ) {
		$os = 'iOS (iPad)';
	} elseif ( strpos( $user_agent, 'Mac OS X' ) !== false || strpos( $user_agent, 'Macintosh' ) !== false ) {
		$os = 'macOS';
	} elseif ( strpos( $user_agent, 'Linux' ) !== false ) {
		$os = 'Linux';
	}

	// Detect Device Type
	if ( strpos( $user_agent, 'Mobile' ) !== false ||
	     strpos( $user_agent, 'iPhone' ) !== false ||
	     strpos( $user_agent, 'Android' ) !== false ) {
		$device = 'mobile';
		$icon   = '📱';
	}

	return array(
		'browser' => $browser,
		'os'      => $os,
		'device'  => $device,
		'icon'    => $icon,
	);
}

/**
 * Get country flag emoji from country code.
 *
 * @param string $country_code 2-letter country code (US, GB, etc).
 * @return string Flag emoji or empty string.
 */
function ta_get_country_flag( $country_code ) {
	if ( empty( $country_code ) || strlen( $country_code ) !== 2 ) {
		return '';
	}

	// Convert country code to flag emoji
	// Flag emojis use Regional Indicator Symbols (U+1F1E6 to U+1F1FF)
	$code = strtoupper( $country_code );
	$flag = '';

	foreach ( str_split( $code ) as $char ) {
		$flag .= mb_chr( 0x1F1E6 + ord( $char ) - ord( 'A' ), 'UTF-8' );
	}

	return $flag;
}

/**
 * Format a URL path into a human-readable page title.
 * Used as fallback when post_title is empty (e.g. pure Next.js routes or deleted pages).
 *
 * @param string $url Full URL or path.
 * @return string Formatted title.
 */
function ta_format_url_as_title( $url ) {
	$path = trim( wp_parse_url( $url, PHP_URL_PATH ), '/' );
	if ( empty( $path ) ) {
		return 'Home';
	}
	$last_segment = basename( $path );
	$title        = str_replace( array( '-', '_' ), ' ', $last_segment );
	return ucwords( $title );
}

/**
 * Get available browsers from database for filter dropdown.
 * Use COALESCE(client_user_agent, user_agent) so headless records
 * with a real browser UA in client_user_agent are counted correctly.
 */
$available_browsers = $wpdb->get_results(
	"SELECT
		CASE
			WHEN COALESCE(client_user_agent, user_agent) LIKE '%Edg%' THEN 'Edge'
			WHEN COALESCE(client_user_agent, user_agent) LIKE '%Chrome%' AND COALESCE(client_user_agent, user_agent) NOT LIKE '%Edg%' THEN 'Chrome'
			WHEN COALESCE(client_user_agent, user_agent) LIKE '%Firefox%' THEN 'Firefox'
			WHEN COALESCE(client_user_agent, user_agent) LIKE '%Safari%' AND COALESCE(client_user_agent, user_agent) NOT LIKE '%Chrome%' THEN 'Safari'
			WHEN COALESCE(client_user_agent, user_agent) LIKE '%Opera%' OR COALESCE(client_user_agent, user_agent) LIKE '%OPR%' THEN 'Opera'
			ELSE 'Other'
		END as browser,
		COUNT(*) as count
	FROM {$table_name}
	WHERE traffic_type = 'citation_click' AND COALESCE(client_user_agent, user_agent) != ''
	GROUP BY browser
	ORDER BY count DESC",
	ARRAY_A
);

/**
 * Get available countries from database for filter dropdown.
 */
$available_countries = $wpdb->get_results(
	"SELECT country_code, COUNT(*) as count
	FROM {$table_name}
	WHERE traffic_type = 'citation_click' AND country_code IS NOT NULL
	GROUP BY country_code
	ORDER BY count DESC",
	ARRAY_A
);

/**
 * Get available dates for the single-date filter dropdown.
 */
$available_dates = $wpdb->get_results(
	"SELECT DATE(visit_timestamp) as date, COUNT(*) as count
	FROM {$table_name}
	WHERE traffic_type = 'citation_click'
		AND (client_user_agent IS NOT NULL OR content_type IN ('rest_api', 'ajax') OR user_agent NOT LIKE 'Headless%')
	GROUP BY DATE(visit_timestamp)
	ORDER BY date DESC
	LIMIT 90",
	ARRAY_A
);
?>

<div class="wrap ta-bot-analytics">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'LLM Traffic', 'third-audience' ); ?>
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
				<strong style="display: block; margin-bottom: 8px; color: #92400e;"><?php esc_html_e( 'How Citations Are Captured (Automatically):', 'third-audience' ); ?></strong>
				<ul style="margin: 0 0 12px 20px; padding: 0; color: #78350f; line-height: 1.8;">
					<li><strong>ChatGPT:</strong> <?php esc_html_e( 'Automatically adds utm_source=chatgpt.com when users click links', 'third-audience' ); ?></li>
					<li><strong>Perplexity:</strong> <?php esc_html_e( 'Sends referrer header with search query', 'third-audience' ); ?></li>
					<li><strong>Claude, Gemini:</strong> <?php esc_html_e( 'Sends referrer header identifying the platform', 'third-audience' ); ?></li>
				</ul>
				<p style="margin: 0; padding: 8px 12px; background: #fef3c7; border-radius: 4px; font-size: 12px; color: #92400e;">
					<span class="dashicons dashicons-info" style="font-size: 14px; width: 14px; height: 14px; margin-right: 4px;"></span>
					<?php esc_html_e( 'Only traffic from recognized AI platforms is tracked. Random UTM parameters are ignored.', 'third-audience' ); ?>
				</p>
			</div>
			<p style="margin: 0; color: #78350f; font-size: 13px;">
				<strong><?php esc_html_e( 'Note:', 'third-audience' ); ?></strong>
				<?php esc_html_e( 'Citation tracking uses UTM parameters and HTTP referrers. If your site uses full-page caching, ensure cache is bypassed for URLs with UTM parameters.', 'third-audience' ); ?>
			</p>
		</div>
	</div>
	<?php endif; ?>

	<!-- Date range filter (presets + custom) -->
	<?php
	$ta_today = current_time( 'Y-m-d' );
	$ta_d7    = gmdate( 'Y-m-d', strtotime( '-6 days', current_time( 'timestamp' ) ) );
	$ta_d30   = gmdate( 'Y-m-d', strtotime( '-29 days', current_time( 'timestamp' ) ) );
	$ta_from  = $filters['date_from'] ?? '';
	$ta_to    = $filters['date_to'] ?? '';
	$ta_keep  = array( 'platform', 'browser', 'country', 'device', 'search', 'pagetype' );
	$ta_base  = array( 'page' => 'third-audience-ai-citations' );
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
	$ta_is_all   = ( empty( $ta_from ) && empty( $ta_to ) && empty( $filters['date'] ) );
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
			<input type="hidden" name="page" value="third-audience-ai-citations">
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

	<!-- Hero Metrics -->
	<div class="ta-hero-metrics">
		<div class="ta-hero-card">
			<div class="ta-hero-icon" style="background: linear-gradient(135deg, #007aff, #0a5fd6);">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1"/><path d="M14 11a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1"/></svg>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Total LLMs Traffic', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo number_format( $total_citations ); ?></div>
				<div class="ta-hero-meta">
					<?php echo esc_html( number_format( $citations_today ) . ' today' ); ?>
				</div>
			</div>
		</div>

		<div class="ta-hero-card">
			<div class="ta-hero-icon" style="background: linear-gradient(135deg, #5856d6, #7a3ff0);">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M9 2v2M15 2v2M9 20v2M15 20v2M2 9h2M2 15h2M20 9h2M20 15h2"/></svg>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'AI Platforms', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo number_format( $unique_platforms ); ?></div>
				<div class="ta-hero-meta">Unique sources</div>
			</div>
		</div>

		<div class="ta-hero-card">
			<div class="ta-hero-icon" style="background: linear-gradient(135deg, #1FB6D0, #00a3bb);">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 0 1 0 18a14 14 0 0 1 0-18"/></svg>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Top Country', 'third-audience' ); ?></div>
				<?php
				$ta_top_country = ! empty( $top_countries ) ? $top_countries[0] : null;
				$ta_tc_pct      = ( $ta_top_country && $total_citations > 0 ) ? round( ( $ta_top_country['count'] / $total_citations ) * 100, 1 ) : 0;
				?>
				<div class="ta-hero-value">
					<?php if ( $ta_top_country ) : ?>
						<?php echo ta_get_country_flag( $ta_top_country['country_code'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo esc_html( $ta_top_country['country_code'] ); ?>
					<?php else : ?>
						&mdash;
					<?php endif; ?>
				</div>
				<div class="ta-hero-meta">
					<?php
					if ( $ta_top_country ) {
						/* translators: 1: visit count, 2: percentage share. */
						printf( esc_html__( '%1$s visits (%2$s%%)', 'third-audience' ), esc_html( number_format( $ta_top_country['count'] ) ), esc_html( $ta_tc_pct ) );
					} else {
						esc_html_e( 'No location data yet', 'third-audience' );
					}
					?>
				</div>
			</div>
		</div>

		<div class="ta-hero-card">
			<div class="ta-hero-icon" style="background: linear-gradient(135deg, #34c759, #28a745);">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15.5A9 9 0 1 1 8.5 3"/><path d="M21 12A9 9 0 0 0 12 3v9z"/></svg>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Avg Citations / Day', 'third-audience' ); ?></div>
				<?php
				// Average LLM citations per active day — respects the current filter ($where_sql).
				$ta_days    = max( 1, (int) $wpdb->get_var( "SELECT COUNT(DISTINCT DATE(visit_timestamp)) FROM {$table_name} WHERE {$where_sql}" ) );
				$ta_avg_day = round( $total_citations / $ta_days, 1 );
				?>
				<div class="ta-hero-value"><?php echo esc_html( $ta_avg_day ); ?></div>
				<div class="ta-hero-meta">
					<?php
					/* translators: %s = number of days with citation traffic. */
					printf( esc_html__( 'across %s active days', 'third-audience' ), '<strong>' . esc_html( number_format( $ta_days ) ) . '</strong>' );
					?>
				</div>
			</div>
		</div>

	</div>

	<!-- Recent LLMs Visits -->
	<?php if ( ! empty( $recent_citations ) ) : ?>
		<?php $ta_recent_collapsed = ( ! empty( $filters['date'] ) || ! empty( $filters['date_from'] ) || ! empty( $filters['date_to'] ) ); ?>
		<style>
			.ta-recent-head{cursor:pointer;}
			.ta-recent-pill{margin-left:auto;font-size:12px;font-weight:600;color:#646970;background:#f2f2f7;border-radius:20px;padding:2px 10px;}
			.ta-recent-twist{font-size:14px;color:#9b9ba0;transition:transform .18s;margin-left:8px;}
			.ta-recent-collapsed .ta-recent-twist{transform:rotate(-90deg);}
			.ta-recent-collapsed > *:not(.ta-card-header){display:none !important;}
			.ta-recent-collapsed .ta-card-body{display:none !important;}
			.ta-recent-collapsed .ta-card-header{border-bottom:none !important;padding:14px 20px !important;}
			.ta-recent-collapsed .ta-card-header .description{display:none !important;}
		</style>
		<div class="ta-card ta-recent-card<?php echo $ta_recent_collapsed ? ' ta-recent-collapsed' : ''; ?>" id="ta-recent-card" style="margin-top: 20px;">
			<div class="ta-card-header ta-recent-head" id="ta-recent-head">
				<h2 style="display:flex;align-items:center;gap:10px;margin:0;">
					🕒 <?php esc_html_e( 'Recent LLMs Visits', 'third-audience' ); ?>
					<span class="ta-recent-pill"><?php echo esc_html( number_format( $total_citations ) ); ?> <?php esc_html_e( 'total', 'third-audience' ); ?></span>
					<span class="ta-recent-twist">&#9662;</span>
				</h2>
				<p class="description" style="margin-top: 6px; font-size: 13px; color: #646970;">
					<?php esc_html_e( 'Every visit from an AI platform (ChatGPT, Claude, Perplexity, Gemini, etc.) that clicked a link to your site. Google Search is tracked separately below.', 'third-audience' ); ?>
				</p>
			</div>
			<div class="ta-card-body" style="overflow-x: auto; padding: 0;">
				<table class="wp-list-table widefat fixed striped" style="min-width: 900px;">
					<thead>
						<tr>
							<th style="width: 160px;"><?php esc_html_e( 'Platform', 'third-audience' ); ?></th>
							<th style="width: 180px;"><?php esc_html_e( 'Page', 'third-audience' ); ?></th>
							<th style="width: 180px;">🌐 <?php esc_html_e( 'Browser & Device', 'third-audience' ); ?></th>
							<th style="width: 70px; text-align: center;">🗺️ <?php esc_html_e( 'Location', 'third-audience' ); ?></th>
							<th style="width: 90px; text-align: center;"><?php esc_html_e( 'Date', 'third-audience' ); ?></th>
							<th style="width: 75px; text-align: center;"><?php esc_html_e( 'Time', 'third-audience' ); ?></th>
							<th style="width: 65px; text-align: center;"><?php esc_html_e( 'Ago', 'third-audience' ); ?></th>
							<th><?php esc_html_e( 'Referrer', 'third-audience' ); ?></th>
						</tr>
					</thead>
					<tbody id="ta-llm-tbody">
						<?php foreach ( $recent_citations as $citation ) : ?>
							<?php
							$ts = strtotime( $citation['visit_timestamp'] );
							$time_ago   = human_time_diff( $ts, current_time( 'timestamp' ) );
							$short_url  = strlen( $citation['url'] ) > 30 ? substr( $citation['url'], 0, 27 ) . '...' : $citation['url'];
							$short_ref  = ! empty( $citation['referer'] ) ? ( strlen( $citation['referer'] ) > 35 ? substr( $citation['referer'], 0, 32 ) . '...' : $citation['referer'] ) : '—';

							// Prefer client_user_agent (real browser UA from JS navigator.userAgent)
							// over user_agent (server-side HTTP_USER_AGENT, may be a bot/proxy UA).
							$ua_string    = ! empty( $citation['client_user_agent'] ) ? $citation['client_user_agent'] : ( $citation['user_agent'] ?? '' );
							$ua_data      = ta_parse_user_agent( $ua_string );
							$content_type = $citation['content_type'] ?? '';

							// NEW: Get country flag
							$country_flag = ta_get_country_flag( $citation['country_code'] ?? '' );
							?>
							<tr>
								<td><span class="ta-bot-badge" style="white-space: normal; word-break: break-word;"><?php echo esc_html( $citation['ai_platform'] ); ?></span></td>
								<?php
								$display_title   = ! empty( $citation['post_title'] )
									? $citation['post_title']
									: ta_format_url_as_title( $citation['url'] );
								$is_broken_title = empty( $citation['post_title'] );
								?>
								<td title="<?php echo esc_attr( $citation['url'] ); ?>">
									<strong style="font-size: 12px;">
										<?php echo esc_html( $display_title ); ?>
										<?php if ( $is_broken_title ) : ?>
											<span title="No matching WordPress page found — may need a redirect" style="color: #f59e0b; font-size: 9px; font-weight: normal; margin-left: 3px;">&#9888;</span>
										<?php endif; ?>
									</strong>
									<br><code style="font-size: 9px; color: #8e8e93;"><?php echo esc_html( $short_url ); ?></code>
								</td>
								<!-- Browser & Device Column -->
								<td style="font-size: 11px;">
									<?php if ( ! empty( $citation['client_user_agent'] ) ) : ?>
										<div style="line-height: 1.4;">
											<strong><?php echo esc_html( 'Unknown' !== $ua_data['browser'] ? $ua_data['browser'] : 'Browser' ); ?></strong> on <?php echo esc_html( 'Unknown' !== $ua_data['os'] ? $ua_data['os'] : 'Unknown OS' ); ?>
											<br>
											<span style="color: #8e8e93; font-size: 10px;">
												<?php echo esc_html( $ua_data['icon'] ); ?> <?php echo esc_html( ucfirst( $ua_data['device'] ) ); ?>
											</span>
										</div>
									<?php elseif ( in_array( $content_type, array( 'rest_api', 'ajax' ), true ) ) : ?>
										<div style="line-height: 1.4;">
											<strong style="color: #8b5cf6;">Headless</strong>
											<br>
											<span style="color: #8e8e93; font-size: 10px;">&#x1F5A5; Next.js server</span>
										</div>
									<?php elseif ( 'graphql' === $content_type ) : ?>
										<div style="line-height: 1.4;">
											<strong style="color: #059669;">GraphQL</strong>
											<br>
											<span style="color: #8e8e93; font-size: 10px;">&#x1F5A5; API call</span>
										</div>
									<?php elseif ( 'Unknown' === $ua_data['browser'] && 'Unknown' === $ua_data['os'] ) : ?>
										<span style="color: #d1d1d6; font-size: 11px;">&#8212;</span>
									<?php else : ?>
										<div style="line-height: 1.4;">
											<strong><?php echo esc_html( $ua_data['browser'] ); ?></strong> on <?php echo esc_html( $ua_data['os'] ); ?>
											<br>
											<span style="color: #8e8e93; font-size: 10px;">
												<?php echo esc_html( $ua_data['icon'] ); ?> <?php echo esc_html( ucfirst( $ua_data['device'] ) ); ?>
											</span>
										</div>
									<?php endif; ?>
								</td>

								<!-- NEW: Location Column -->
								<td style="text-align: center; font-size: 14px;" title="<?php echo esc_attr( $citation['country_code'] ?: 'Unknown' ); ?>">
									<?php if ( ! empty( $country_flag ) ) : ?>
										<?php echo $country_flag; ?> <span style="font-size: 11px; color: #646970;"><?php echo esc_html( $citation['country_code'] ); ?></span>
									<?php else : ?>
										<span style="color: #d1d1d6; font-size: 11px;">—</span>
									<?php endif; ?>
								</td>

								<td style="text-align: center; font-size: 11px;"><?php echo esc_html( gmdate( 'M j, Y', $ts ) ); ?></td>
								<td style="text-align: center; font-size: 11px; color: #646970;"><?php echo esc_html( gmdate( 'g:i A', $ts ) ); ?></td>
								<td style="text-align: center; font-size: 10px; color: #8e8e93;"><?php echo esc_html( $time_ago ); ?></td>
								<td style="font-size: 10px; color: #8e8e93;" title="<?php echo esc_attr( $citation['referer'] ); ?>">
									<?php echo esc_html( $short_ref ); ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<div style="display:flex; align-items:center; justify-content:center; gap:14px; padding:14px 0; border-top:1px solid #f0f0f1;">
				<button id="ta-llm-prev" class="button" disabled>&larr; <?php esc_html_e( 'Prev', 'third-audience' ); ?></button>
				<span style="font-size:13px; color:#646970;">
					<?php esc_html_e( 'Page', 'third-audience' ); ?> <strong id="ta-llm-current-page">1</strong> <?php esc_html_e( 'of', 'third-audience' ); ?> <strong id="ta-llm-total-pages"><?php echo esc_html( $llm_total_pages ); ?></strong>
					<span style="color:#ccd0d4; margin:0 6px;">&middot;</span>
					<span style="font-size:12px;"><?php echo esc_html( number_format( $total_citations ) ); ?> <?php esc_html_e( 'total', 'third-audience' ); ?></span>
				</span>
				<button id="ta-llm-next" class="button" <?php echo $llm_total_pages <= 1 ? 'disabled' : ''; ?>><?php esc_html_e( 'Next', 'third-audience' ); ?> &rarr;</button>
				<span id="ta-llm-spinner" style="display:none; margin-left:4px; vertical-align:middle;"><span class="spinner is-active" style="float:none;"></span></span>
			</div>
		</div>
	<?php endif; ?>

	<?php
	// ===== Drill-down cards (click a row → modal with the exact visits) =====
	$ta_max = function ( $items ) {
		$m = 0;
		foreach ( $items as $it ) {
			$m = max( $m, (int) $it['count'] );
		}
		return $m ? $m : 1;
	};
	// Distinct palette by rank (no repeats) so platform variants don't collapse
	// to the same brand colour; matches the doughnut, which uses $platform_colors.
	$ta_platforms = array();
	$ta_pal_i     = 0;
	foreach ( $citations_by_platform as $r ) {
		$ta_platforms[] = array(
			'key'   => $r['ai_platform'],
			'label' => $r['ai_platform'],
			'count' => (int) $r['count'],
			'color' => $platform_colors[ $ta_pal_i % count( $platform_colors ) ],
		);
		$ta_pal_i++;
	}
	$ta_countries = array();
	foreach ( $top_countries as $r ) {
		$ta_countries[] = array( 'key' => $r['country_code'], 'label' => trim( ta_get_country_flag( $r['country_code'] ) . ' ' . $r['country_code'] ), 'count' => (int) $r['count'], 'color' => '#007aff' );
	}
	$ta_browsers = array();
	foreach ( $citations_by_browser as $r ) {
		$ta_browsers[] = array( 'key' => $r['browser'], 'label' => $r['browser'], 'count' => (int) $r['count'], 'color' => '#007aff' );
	}
	$ta_pages = array();
	foreach ( $top_cited_pages as $r ) {
		$ta_pages[] = array( 'key' => $r['url'], 'label' => ta_page_display_title( $r['post_title'], $r['url'] ), 'count' => (int) $r['citation_count'], 'color' => '#007aff' );
	}
	$ta_devices = array();
	foreach ( $citations_by_device as $r ) {
		$ta_devices[] = array( 'key' => $r['device'], 'label' => ( 'mobile' === $r['device'] ? '📱 Mobile' : '🖥️ Desktop' ), 'count' => (int) $r['count'], 'color' => '#007aff' );
	}
	$ta_pagetypes = array();
	foreach ( $citations_by_pagetype as $r ) {
		$ta_pagetypes[] = array( 'key' => $r['pagetype'], 'label' => TA_Citation_Query::pagetype_label( $r['pagetype'] ), 'count' => (int) $r['count'], 'color' => '#007aff' );
	}
	$ta_psum = 0;
	foreach ( $ta_platforms as $p ) {
		$ta_psum += $p['count'];
	}

	// Per-card accent colours + clean SVG icon badges (match the approved demo).
	$ta_icon_bg = array(
		'platform' => '#5856d6',
		'country'  => '#1FB6D0',
		'browser'  => '#007aff',
		'page'     => '#ff9500',
		'device'   => '#34c759',
		'pagetype' => '#af52de',
	);
	$ta_icons = array(
		'platform' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M9 2v2M15 2v2M9 20v2M15 20v2M2 9h2M2 15h2M20 9h2M20 15h2"/></svg>',
		'country'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 0 1 0 18a14 14 0 0 1 0-18"/></svg>',
		'browser'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18"/></svg>',
		'page'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg>',
		'device'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/></svg>',
		'pagetype' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0l-7.2-7.2A2 2 0 0 1 2.8 11V5a2 2 0 0 1 2-2h6.2a2 2 0 0 1 1.4.6l7.2 7.2a2 2 0 0 1 0 2.6z"/><circle cx="7.5" cy="7.5" r="1.4" fill="currentColor"/></svg>',
	);

	$ta_render_card = function ( $title, $dim, $items, $hint ) use ( $ta_max, $total_citations, $ta_icon_bg, $ta_icons ) {
		$max      = $ta_max( $items );
		$accent   = isset( $ta_icon_bg[ $dim ] ) ? $ta_icon_bg[ $dim ] : '#007aff';
		$icon_svg = isset( $ta_icons[ $dim ] ) ? $ta_icons[ $dim ] : '';
		ob_start();
		?>
		<div class="ta-dd-card">
			<h2><span class="ta-dd-ic" style="background:<?php echo esc_attr( $accent ); ?>"><?php echo $icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static inline SVG ?></span><?php echo esc_html( $title ); ?></h2>
			<div class="ta-dd-rows">
				<?php if ( empty( $items ) ) : ?>
					<div class="ta-dd-hint"><?php esc_html_e( 'No data in this range.', 'third-audience' ); ?></div>
				<?php else : ?>
					<?php
					foreach ( array_slice( $items, 0, 8 ) as $it ) :
						$w        = $max ? round( $it['count'] / $max * 100 ) : 0;
						$pct      = $total_citations ? round( $it['count'] / $total_citations * 100, 1 ) : 0;
						// Platforms keep their brand colour; every other card uses its accent.
						$rowcolor = ( 'platform' === $dim ) ? $it['color'] : $accent;
						?>
						<div class="ta-dd-row" data-dim="<?php echo esc_attr( $dim ); ?>" data-val="<?php echo esc_attr( $it['key'] ); ?>" data-label="<?php echo esc_attr( $it['label'] ); ?>">
							<span class="ta-dd-dot" style="background:<?php echo esc_attr( $rowcolor ); ?>"></span>
							<span class="ta-dd-name"><?php echo esc_html( $it['label'] ); ?></span>
							<span class="ta-dd-bar"><span style="width:<?php echo esc_attr( $w ); ?>%;background:<?php echo esc_attr( $rowcolor ); ?>"></span></span>
							<span class="ta-dd-num"><?php echo esc_html( number_format( $it['count'] ) ); ?></span>
							<span class="ta-dd-pct"><?php echo esc_html( $pct ); ?>%</span>
							<span class="ta-dd-chev">›</span>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<div class="ta-dd-hint"><?php echo esc_html( $hint ); ?></div>
		</div>
		<?php
		return ob_get_clean();
	};
	?>

	<style>
		.ta-dd-reconcile{font-size:12px;color:#11823b;background:#eafaf0;border:1px solid #c9efd6;border-radius:8px;padding:8px 12px;margin:6px 0 16px;}
		.ta-dd-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;}
		.ta-dd-card{background:#fff;border:1px solid #e5e5ea;border-radius:12px;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,.04);}
		.ta-dd-card h2{font-size:14px;margin:0;padding:13px 16px;border-bottom:1px solid #e5e5ea;display:flex;align-items:center;gap:10px;}
		.ta-dd-ic{width:27px;height:27px;border-radius:7px;display:inline-flex;align-items:center;justify-content:center;color:#fff;flex:none;}
		.ta-dd-ic svg{width:15px;height:15px;}
		.ta-dd-rows{padding:6px 8px;}
		.ta-dd-row{display:flex;align-items:center;gap:10px;padding:9px 8px;border-radius:8px;cursor:pointer;}
		.ta-dd-row:hover{background:#f5f8ff;}
		.ta-dd-dot{width:9px;height:9px;border-radius:50%;flex:none;}
		.ta-dd-name{font-weight:500;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:150px;}
		.ta-dd-bar{flex:1;height:6px;background:#eef0f2;border-radius:4px;overflow:hidden;}
		.ta-dd-bar>span{display:block;height:100%;border-radius:4px;}
		.ta-dd-num{font-weight:700;width:48px;text-align:right;}
		.ta-dd-pct{width:46px;text-align:right;color:#646970;font-size:12px;}
		.ta-dd-chev{color:#c7c7cc;}
		.ta-dd-hint{font-size:11px;color:#9b9ba0;padding:4px 14px 12px;}
		.ta-dd-scrim{position:fixed;inset:0;background:rgba(15,17,21,.45);opacity:0;pointer-events:none;transition:opacity .18s;z-index:99998;display:flex;align-items:center;justify-content:center;padding:24px;}
		.ta-dd-scrim.open{opacity:1;pointer-events:auto;}
		.ta-dd-modal{position:relative;background:#fff;width:min(1080px,96vw);max-height:90vh;border-radius:16px;overflow:hidden;display:flex;flex-direction:column;transform:scale(.96);transition:transform .18s;box-shadow:0 30px 80px rgba(0,0,0,.35);}
		.ta-dd-scrim.open .ta-dd-modal{transform:scale(1);}
		.ta-dd-mhead{padding:18px 24px 16px;border-bottom:1px solid #e5e5ea;background:linear-gradient(180deg,#f6f9ff,#fff);}
		.ta-dd-crumb{font-size:11px;color:#646970;text-transform:uppercase;letter-spacing:.06em;font-weight:700;}
		.ta-dd-trow{display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-top:7px;padding-right:40px;}
		.ta-dd-mactions{margin-left:auto;display:flex;gap:8px;align-items:center;}
		.ta-dd-exp{border:0;background:#007aff;color:#fff;width:36px;height:36px;border-radius:9px;padding:0;font-size:16px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;}
		.ta-dd-exp:hover{background:#0a5fd6;}
		.ta-dd-close{position:absolute;top:13px;right:13px;z-index:3;border:0;background:#eceef2;width:36px;height:36px;border-radius:10px;font-size:20px;cursor:pointer;color:#646970;line-height:1;}
		.ta-dd-close:hover{background:#e1e4ea;color:#1d1d1f;}
		.ta-dd-modal h3{margin:0;font-size:23px;}
		.ta-dd-chips{display:flex;gap:8px;align-items:center;}
		.ta-dd-chip{border:1px solid #e5e5ea;border-radius:9px;padding:5px 12px;text-align:center;white-space:nowrap;}
		.ta-dd-chip .l{font-size:9.5px;text-transform:uppercase;display:block;opacity:.8;}
		.ta-dd-chip .v{font-size:15px;font-weight:800;line-height:1.1;}
		.ta-dd-chip:nth-child(1){background:#eaf2ff;border-color:#cfe0ff;color:#0a3a86;}
		.ta-dd-chip:nth-child(2){background:#eafaf0;border-color:#c9efd6;color:#0a6b2d;}
		.ta-dd-mbody{overflow:auto;}
		.ta-dd-mbody table{width:100%;border-collapse:collapse;}
		.ta-dd-mbody th,.ta-dd-mbody td{text-align:left;padding:11px 16px;font-size:12.5px;border-bottom:1px solid #f0f0f1;}
		.ta-dd-mbody th{position:sticky;top:0;background:#fafafa;color:#646970;text-transform:uppercase;font-size:11px;}
		.ta-dd-mbody tbody tr:nth-child(even){background:#fafbfd;}
		.ta-dd-mbody tbody tr:hover{background:#f0f5ff;}
		.ta-dd-mbody code{font-size:10px;color:#8e8e93;}
		.ta-dd-badge{display:inline-block;border:1px solid #e5e5ea;border-radius:20px;padding:3px 11px;font-size:11px;font-weight:600;white-space:nowrap;background:#fafafa;}
		.ta-dd-mbody td:first-child{width:150px;}
		.ta-dd-mbody tbody td{vertical-align:middle;}
		.ta-dd-foot{display:flex;align-items:center;justify-content:center;gap:14px;padding:13px;border-top:1px solid #e5e5ea;font-size:13px;color:#646970;}
		.ta-dd-foot button{border:1px solid #e5e5ea;background:#fff;border-radius:7px;padding:6px 13px;cursor:pointer;}
		.ta-dd-foot button:disabled{opacity:.4;cursor:default;}
		@media(max-width:980px){.ta-dd-grid{grid-template-columns:repeat(2,1fr)}}
	</style>

	<div class="ta-dd-grid">
		<?php
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- card markup is escaped inside the closure.
		echo $ta_render_card( __( 'Platforms', 'third-audience' ), 'platform', $ta_platforms, __( 'Citations grouped by AI platform. Click a row → details.', 'third-audience' ) );
		echo $ta_render_card( __( 'Countries', 'third-audience' ), 'country', $ta_countries, __( 'Where the traffic came from.', 'third-audience' ) );
		echo $ta_render_card( __( 'Browsers', 'third-audience' ), 'browser', $ta_browsers, __( 'Visitor browser (real navigator UA).', 'third-audience' ) );
		echo $ta_render_card( __( 'Top Cited Pages', 'third-audience' ), 'page', $ta_pages, __( 'Most-clicked pages from AI answers.', 'third-audience' ) );
		echo $ta_render_card( __( 'Devices', 'third-audience' ), 'device', $ta_devices, __( 'Desktop vs mobile split.', 'third-audience' ) );
		echo $ta_render_card( __( 'Page Type', 'third-audience' ), 'pagetype', $ta_pagetypes, __( 'By post type (auto-adapts to your post types).', 'third-audience' ) );
		// phpcs:enable
		?>
	</div>

	<!-- Drill-down modal -->
	<div class="ta-dd-scrim" id="ta-dd-scrim">
		<div class="ta-dd-modal">
			<button class="ta-dd-close" id="ta-dd-close" aria-label="Close">×</button>
			<div class="ta-dd-mhead">
				<span class="ta-dd-crumb" id="ta-dd-crumb"></span>
				<div class="ta-dd-trow">
					<h3 id="ta-dd-title"></h3>
					<div class="ta-dd-mactions">
						<div class="ta-dd-chips" id="ta-dd-chips"></div>
						<button class="ta-dd-exp" id="ta-dd-export" title="<?php esc_attr_e( 'Export CSV', 'third-audience' ); ?>" aria-label="<?php esc_attr_e( 'Export CSV', 'third-audience' ); ?>">⬇</button>
					</div>
				</div>
			</div>
			<div class="ta-dd-mbody"><table><thead id="ta-dd-thead"></thead><tbody id="ta-dd-tbody"></tbody></table></div>
			<div class="ta-dd-foot" id="ta-dd-foot"></div>
		</div>
	</div>

	<script>
	(function(){
		'use strict';
		var nonce     = <?php echo wp_json_encode( wp_create_nonce( 'ta_analytics_nonce' ) ); ?>;
		var taFilters = <?php echo wp_json_encode( array( 'date_from' => $ta_from, 'date_to' => $ta_to, 'date' => $filters['date'] ?? '' ) ); ?>;
		var taTotal   = <?php echo (int) $total_citations; ?>;
		var cur = { dim:'', val:'', label:'', page:1, pages:1, total:0 };

		function esc(s){ return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
		var taDecEl = document.createElement('textarea');
		function dec(s){ taDecEl.innerHTML = String(s==null?'':s); return taDecEl.value; } // decode HTML entities so titles aren't double-escaped (AT&amp;T → AT&T)
		function flag(cc){ if(!cc||cc.length!==2)return ''; try{ return String.fromCodePoint(cc.toUpperCase().charCodeAt(0)+127397, cc.toUpperCase().charCodeAt(1)+127397); }catch(e){ return ''; } }
		function uaParse(ua){ var u=(ua||'').toLowerCase(), b='Unknown', os='Unknown', dev='Desktop';
			if(/edg/.test(u))b='Edge'; else if(/opr|opera/.test(u))b='Opera'; else if(/chrome/.test(u))b='Chrome'; else if(/firefox/.test(u))b='Firefox'; else if(/safari/.test(u))b='Safari';
			if(/windows/.test(u))os='Windows'; else if(/mac os|macintosh/.test(u))os='macOS'; else if(/iphone|ipad|ios/.test(u))os='iOS'; else if(/android/.test(u))os='Android'; else if(/linux/.test(u))os='Linux';
			if(/mobile|iphone|android/.test(u))dev='Mobile';
			return {b:b,os:os,dev:dev}; }
		function fmtDate(ts){ var d=new Date(String(ts).replace(' ','T')+'Z'); return d.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}); }
		function fmtTime(ts){ var d=new Date(String(ts).replace(' ','T')+'Z'); return d.toLocaleTimeString('en-US',{hour:'numeric',minute:'2-digit'}); }

		function ddFetch(exportAll){
			var body=new URLSearchParams({ action:'ta_citations_drilldown', nonce:nonce, dim:cur.dim, val:cur.val, page:exportAll?1:cur.page });
			if(exportAll) body.append('export','1');
			Object.keys(taFilters).forEach(function(k){ if(taFilters[k]) body.append(k,taFilters[k]); });
			return fetch(ajaxurl,{method:'POST',body:body}).then(function(r){return r.json();});
		}
		function rowHtml(r){
			var ua = r.client_user_agent || r.user_agent || '';
			var p = uaParse(ua);
			var headless = (!r.client_user_agent && (r.content_type==='rest_api'||r.content_type==='ajax'));
			var bd = headless ? '<strong style="color:#8b5cf6;">Headless</strong><br><span style="color:#8e8e93;font-size:10px;">Next.js server</span>'
				: '<strong>'+esc(p.b)+'</strong> on '+esc(p.os)+'<br><span style="color:#8e8e93;font-size:10px;">'+esc(p.dev)+'</span>';
			var title = dec( r.post_title || r.url || '—' );
			var su = (r.url && r.url.length>34) ? r.url.substring(0,31)+'...' : (r.url||'');
			var loc = r.country_code ? (flag(r.country_code)+' '+esc(r.country_code)) : '—';
			return '<tr><td><span class="ta-dd-badge">'+esc(dec(r.ai_platform))+'</span></td>'
				+'<td><strong>'+esc(title)+'</strong><br><code>'+esc(su)+'</code></td>'
				+'<td>'+bd+'</td><td>'+loc+'</td>'
				+'<td>'+esc(fmtDate(r.visit_timestamp))+'</td><td style="color:#646970">'+esc(fmtTime(r.visit_timestamp))+'</td></tr>';
		}
		function render(data){
			cur.page=data.page; cur.pages=data.total_pages; cur.total=data.total;
			var share = taTotal ? Math.round(data.total/taTotal*1000)/10 : 0;
			document.getElementById('ta-dd-chips').innerHTML=
				'<div class="ta-dd-chip"><span class="l">Visits</span><span class="v">'+data.total.toLocaleString()+'</span></div>'
				+'<div class="ta-dd-chip"><span class="l">Share of total</span><span class="v">'+share+'%</span></div>';
			document.getElementById('ta-dd-thead').innerHTML='<tr><th>Platform</th><th>Page</th><th>Browser &amp; device</th><th>Location</th><th>Date</th><th>Time</th></tr>';
			document.getElementById('ta-dd-tbody').innerHTML=(data.rows||[]).map(rowHtml).join('') || '<tr><td colspan="6" style="text-align:center;color:#8e8e93;padding:24px">No visits.</td></tr>';
			document.getElementById('ta-dd-foot').innerHTML='<button id="ta-dd-prev" '+(cur.page<=1?'disabled':'')+'>← Prev</button><span>Page <b>'+cur.page+'</b> of <b>'+cur.pages+'</b> · '+data.total.toLocaleString()+' total</span><button id="ta-dd-next" '+(cur.page>=cur.pages?'disabled':'')+'>Next →</button>';
			document.getElementById('ta-dd-prev').addEventListener('click',function(){ if(cur.page>1){cur.page--;load();} });
			document.getElementById('ta-dd-next').addEventListener('click',function(){ if(cur.page<cur.pages){cur.page++;load();} });
		}
		function load(){ ddFetch(false).then(function(res){ if(res&&res.success) render(res.data); }); }
		function open(dim,val,label){
			cur.dim=dim; cur.val=val; cur.label=label; cur.page=1;
			document.getElementById('ta-dd-crumb').textContent=dim.toUpperCase()+' · DRILL-DOWN';
			document.getElementById('ta-dd-title').textContent=label;
			document.getElementById('ta-dd-scrim').classList.add('open');
			load();
		}
		function close(){ document.getElementById('ta-dd-scrim').classList.remove('open'); }

		function csvCell(v){ v=String(v==null?'':v); return /[",\n]/.test(v)?'"'+v.replace(/"/g,'""')+'"':v; }
		function exportCsv(){
			ddFetch(true).then(function(res){
				if(!res||!res.success)return;
				var rows=res.data.rows||[]; var head=['Platform','Page','URL','Browser','OS','Device','Location','Date','Time'];
				var lines=[head.join(',')];
				rows.forEach(function(r){ var p=uaParse(r.client_user_agent||r.user_agent||'');
					lines.push([r.ai_platform,(r.post_title||''),r.url,p.b,p.os,p.dev,(r.country_code||''),fmtDate(r.visit_timestamp),fmtTime(r.visit_timestamp)].map(csvCell).join(',')); });
				var b=new Blob([lines.join('\n')],{type:'text/csv'}); var u=URL.createObjectURL(b); var a=document.createElement('a');
				a.href=u; a.download=(cur.dim+'-'+cur.val).toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,'')+'.csv';
				document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(u);
			});
		}

		document.querySelectorAll('.ta-dd-row').forEach(function(el){
			el.addEventListener('click',function(){ open(el.getAttribute('data-dim'), el.getAttribute('data-val'), el.getAttribute('data-label')); });
		});
		document.getElementById('ta-dd-close').addEventListener('click',close);
		document.getElementById('ta-dd-export').addEventListener('click',exportCsv);
		document.getElementById('ta-dd-scrim').addEventListener('click',function(e){ if(e.target===this)close(); });
		document.addEventListener('keydown',function(e){ if(e.key==='Escape')close(); });
	})();
	</script>

	<!-- Organic Search Traffic -->
	<?php if ( ! empty( $google_search_visits ) ) : ?>
	<div class="ta-card" style="margin-top: 20px; border-left: 4px solid #4285F4;">
		<div class="ta-card-header" style="background: #f0f4ff; display:block;">
			<h2 style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
				<span style="font-size:20px;">&#x1F50D;</span>
				<?php esc_html_e( 'Organic Search Traffic', 'third-audience' ); ?>
				<span style="background:#4285F4; color:#fff; font-size:11px; font-weight:600; padding:2px 8px; border-radius:10px; margin-left:4px;">
					<?php echo esc_html( number_format( intval( $google_search_total ) ) ); ?> <?php esc_html_e( 'total', 'third-audience' ); ?>
				</span>
				<?php if ( intval( $google_organic_total ) > 0 ) : ?>
				<span style="background:#e8f0fe; color:#1a73e8; font-size:11px; font-weight:500; padding:2px 8px; border-radius:10px;">
					&#x1F50D; <?php echo esc_html( number_format( intval( $google_organic_total ) ) ); ?> <?php esc_html_e( 'Organic', 'third-audience' ); ?>
				</span>
				<?php endif; ?>
				<?php if ( intval( $google_ai_mode_total ) > 0 ) : ?>
				<span style="background:#f3e8ff; color:#7c3aed; font-size:11px; font-weight:500; padding:2px 8px; border-radius:10px;">
					<?php echo esc_html( number_format( intval( $google_ai_mode_total ) ) ); ?> <?php esc_html_e( 'AI Mode', 'third-audience' ); ?>
				</span>
				<?php endif; ?>
			</h2>
			<p style="margin: 6px 0 0; font-size: 13px; color: #646970;">
				<?php esc_html_e( '— Google, tracked separately from AI platforms.', 'third-audience' ); ?>
			</p>
		</div>
		<div class="ta-card-body" style="overflow-x: auto; padding: 0;">
			<table class="wp-list-table widefat fixed striped" style="min-width: 900px;">
				<thead>
					<tr>
						<th style="width:160px;"><?php esc_html_e( 'Platform', 'third-audience' ); ?></th>
						<th style="width:200px;"><?php esc_html_e( 'Page', 'third-audience' ); ?></th>
						<th style="width:180px;">&#x1F310; <?php esc_html_e( 'Browser & Device', 'third-audience' ); ?></th>
						<th style="width:70px; text-align:center;">&#x1F5FA;&#xFE0F; <?php esc_html_e( 'Location', 'third-audience' ); ?></th>
						<th style="width:90px; text-align:center;"><?php esc_html_e( 'Date', 'third-audience' ); ?></th>
						<th style="width:75px; text-align:center;"><?php esc_html_e( 'Time', 'third-audience' ); ?></th>
						<th style="width:65px; text-align:center;"><?php esc_html_e( 'Ago', 'third-audience' ); ?></th>
					</tr>
				</thead>
				<tbody id="ta-google-tbody">
					<?php foreach ( $google_search_visits as $gs ) : ?>
						<?php
						$ts            = strtotime( $gs['visit_timestamp'] );
						$time_ago      = human_time_diff( $ts, current_time( 'timestamp' ) );
						$short_url     = strlen( $gs['url'] ) > 30 ? substr( $gs['url'], 0, 27 ) . '...' : $gs['url'];
						$display_title = ! empty( $gs['post_title'] ) ? $gs['post_title'] : $gs['url'];
						$ua_string     = ! empty( $gs['client_user_agent'] ) ? $gs['client_user_agent'] : ( $gs['user_agent'] ?? '' );
						$ua_data       = ta_parse_user_agent( $ua_string );
						$country_flag  = ta_get_country_flag( $gs['country_code'] ?? '' );
						?>
						<tr>
							<td>
								<?php if ( 'Google AI Mode' === $gs['ai_platform'] ) : ?>
									<span class="ta-bot-badge" style="background:#f3e8ff; color:#7c3aed; border-color:#d8b4fe; white-space:nowrap;" title="<?php esc_attr_e( 'Click from Google AI Overview (AI Mode)', 'third-audience' ); ?>">
										<?php esc_html_e( 'Google AI Mode', 'third-audience' ); ?>
									</span>
								<?php else : ?>
									<span class="ta-bot-badge" style="background:#e8f0fe; color:#1a73e8; border-color:#c5d8fd; white-space:nowrap;" title="<?php esc_attr_e( 'Traditional organic Google Search click', 'third-audience' ); ?>">
										<?php esc_html_e( 'Google Search', 'third-audience' ); ?>
									</span>
								<?php endif; ?>
							</td>
							<td title="<?php echo esc_attr( $gs['url'] ); ?>">
								<strong style="font-size:12px;"><?php echo esc_html( $display_title ); ?></strong>
								<br><code style="font-size:9px; color:#8e8e93;"><?php echo esc_html( $short_url ); ?></code>
							</td>
							<td style="font-size:11px;">
								<?php if ( ! empty( $gs['client_user_agent'] ) ) : ?>
									<div style="line-height:1.4;">
										<strong><?php echo esc_html( 'Unknown' !== $ua_data['browser'] ? $ua_data['browser'] : 'Browser' ); ?></strong>
										on <?php echo esc_html( 'Unknown' !== $ua_data['os'] ? $ua_data['os'] : 'Unknown OS' ); ?>
										<br><span style="color:#8e8e93; font-size:10px;"><?php echo esc_html( $ua_data['icon'] ); ?> <?php echo esc_html( ucfirst( $ua_data['device'] ) ); ?></span>
									</div>
								<?php else : ?>
									<span style="color:#d1d1d6;">&#8212;</span>
								<?php endif; ?>
							</td>
							<td style="text-align:center; font-size:14px;" title="<?php echo esc_attr( $gs['country_code'] ?: 'Unknown' ); ?>">
								<?php if ( ! empty( $country_flag ) ) : ?>
									<?php echo $country_flag; ?> <span style="font-size:11px; color:#646970;"><?php echo esc_html( $gs['country_code'] ); ?></span>
								<?php else : ?>
									<span style="color:#d1d1d6; font-size:11px;">&#8212;</span>
								<?php endif; ?>
							</td>
							<td style="text-align:center; font-size:11px;"><?php echo esc_html( gmdate( 'M j, Y', $ts ) ); ?></td>
							<td style="text-align:center; font-size:11px; color:#646970;"><?php echo esc_html( gmdate( 'g:i A', $ts ) ); ?></td>
							<td style="text-align:center; font-size:10px; color:#8e8e93;"><?php echo esc_html( $time_ago ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div style="display:flex; align-items:center; justify-content:center; gap:14px; padding:14px 0; border-top:1px solid #f0f0f1;">
			<button id="ta-google-prev" class="button" disabled>&larr; <?php esc_html_e( 'Prev', 'third-audience' ); ?></button>
			<span style="font-size:13px; color:#646970;">
				<?php esc_html_e( 'Page', 'third-audience' ); ?> <strong id="ta-google-current-page">1</strong> <?php esc_html_e( 'of', 'third-audience' ); ?> <strong id="ta-google-total-pages"><?php echo esc_html( $google_total_pages ); ?></strong>
				<span style="color:#ccd0d4; margin:0 6px;">&middot;</span>
				<span style="font-size:12px;"><?php echo esc_html( number_format( $google_search_total ) ); ?> <?php esc_html_e( 'total', 'third-audience' ); ?></span>
			</span>
			<button id="ta-google-next" class="button" <?php echo $google_total_pages <= 1 ? 'disabled' : ''; ?>><?php esc_html_e( 'Next', 'third-audience' ); ?> &rarr;</button>
			<span id="ta-google-spinner" style="display:none; margin-left:4px; vertical-align:middle;"><span class="spinner is-active" style="float:none;"></span></span>
		</div>
	</div>
	<?php endif; ?>

	<!-- Crawl → Citation Chains -->
	<div class="ta-card" style="margin-top: 20px; border-left: 4px solid #8b5cf6;">
		<div class="ta-card-header" style="background: #faf5ff; display:block;">
			<h2 style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin:0;">
				<span style="font-size:20px;">&#x1F517;</span>
				<?php esc_html_e( 'Crawl → Citation Chains', 'third-audience' ); ?>
				<span style="background:#8b5cf6; color:#fff; font-size:11px; font-weight:600; padding:2px 8px; border-radius:10px; margin-left:4px;">
					<?php echo esc_html( number_format( count( $crawl_chains ) ) ); ?> <?php esc_html_e( 'chains', 'third-audience' ); ?>
				</span>
			</h2>
			<p style="margin: 6px 0 0; font-size: 13px; color: #646970;">
				<?php esc_html_e( 'When an AI platform\'s bot crawls your pages and a real user clicks a citation from that same platform within 30 minutes — that\'s one conversation chain. The user\'s exact prompt is never sent by any platform, but the crawled pages reveal the topic the AI researched before citing you. Works best for ChatGPT and Perplexity (live web search); Claude and Gemini often answer from older cached crawls, so chains are rarer.', 'third-audience' ); ?>
			</p>
		</div>
		<div class="ta-card-body" style="overflow-x: auto; padding: 0;">
			<?php if ( ! empty( $crawl_chains ) ) : ?>
			<table class="wp-list-table widefat fixed striped" style="min-width: 900px;">
				<thead>
					<tr>
						<th style="width:120px;"><?php esc_html_e( 'Platform', 'third-audience' ); ?></th>
						<th style="width:220px;"><?php esc_html_e( 'Cited Page (User Clicked)', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Pages Bot Crawled Before', 'third-audience' ); ?></th>
						<th style="width:80px; text-align:center;" title="<?php esc_attr_e( 'Time between last bot crawl and the user click', 'third-audience' ); ?>"><?php esc_html_e( 'Gap', 'third-audience' ); ?></th>
						<th style="width:100px; text-align:center;"><?php esc_html_e( 'When', 'third-audience' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $crawl_chains as $chain ) : ?>
						<?php
						$click_ts  = strtotime( $chain['click_time'] );
						$crawl_ts  = strtotime( $chain['last_crawl_time'] );
						$gap_secs  = max( 0, $click_ts - $crawl_ts );
						if ( $gap_secs < 60 ) {
							$gap_label = $gap_secs . 's';
						} elseif ( $gap_secs < 3600 ) {
							$gap_label = floor( $gap_secs / 60 ) . 'm ' . ( $gap_secs % 60 ) . 's';
						} else {
							$gap_label = floor( $gap_secs / 3600 ) . 'h ' . floor( ( $gap_secs % 3600 ) / 60 ) . 'm';
						}
						$chain_ago     = human_time_diff( $click_ts, current_time( 'timestamp' ) );
						$cited_display = ! empty( $chain['cited_title'] ) ? $chain['cited_title'] : $chain['cited_url'];
						$cited_short   = strlen( $cited_display ) > 45 ? substr( $cited_display, 0, 42 ) . '...' : $cited_display;
						$crawled_list  = array_filter( explode( '||', (string) $chain['crawled_urls'] ) );
						$crawled_show  = array_slice( $crawled_list, 0, 3 );
						$crawled_more  = count( $crawled_list ) - count( $crawled_show );
						?>
						<tr>
							<td>
								<span class="ta-bot-badge" style="background:#f3e8ff; color:#7c3aed; border-color:#d8b4fe;">
									<?php echo esc_html( $chain['ai_platform'] ); ?>
								</span>
							</td>
							<td>
								<a href="<?php echo esc_url( $chain['cited_url'] ); ?>" target="_blank" title="<?php echo esc_attr( $chain['cited_url'] ); ?>" style="font-weight:600;">
									<?php echo esc_html( $cited_short ); ?>
								</a>
							</td>
							<td style="font-size:12px; line-height:1.7;">
								<?php foreach ( $crawled_show as $crawled_url ) : ?>
									<?php
									$cpath  = wp_parse_url( $crawled_url, PHP_URL_PATH );
									$cpath  = $cpath ? $cpath : $crawled_url;
									$cshort = strlen( $cpath ) > 50 ? substr( $cpath, 0, 47 ) . '...' : $cpath;
									?>
									<span style="display:inline-block; background:#f6f7f7; border:1px solid #dcdcde; border-radius:4px; padding:1px 7px; margin:1px 3px 1px 0; font-family:monospace; font-size:11px;" title="<?php echo esc_attr( $crawled_url ); ?>">
										<?php echo esc_html( $cshort ); ?>
									</span>
								<?php endforeach; ?>
								<?php if ( $crawled_more > 0 ) : ?>
									<span style="color:#8e8e93; font-size:11px;">+<?php echo esc_html( $crawled_more ); ?> <?php esc_html_e( 'more', 'third-audience' ); ?></span>
								<?php endif; ?>
							</td>
							<td style="text-align:center;">
								<span style="font-weight:600; color:<?php echo $gap_secs <= 120 ? '#16a34a' : '#646970'; ?>; font-size:12px;">
									<?php echo esc_html( $gap_label ); ?>
								</span>
							</td>
							<td style="text-align:center; font-size:11px; color:#646970;">
								<?php echo esc_html( $chain_ago ); ?> <?php esc_html_e( 'ago', 'third-audience' ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php else : ?>
			<div style="padding: 24px; text-align: center;">
				<p style="margin: 0 0 6px; font-size: 14px; color: #646970;">
					<?php esc_html_e( 'No crawl-to-citation chains detected yet.', 'third-audience' ); ?>
				</p>
				<p style="margin: 0; font-size: 12px; color: #8e8e93;">
					<?php esc_html_e( 'A chain appears when a platform\'s bot (e.g. ChatGPT-User, PerplexityBot) crawls a page and a citation click from that same platform lands within 30 minutes. This requires live web search by the AI — answers served from cached training data won\'t create chains.', 'third-audience' ); ?>
				</p>
			</div>
			<?php endif; ?>
		</div>
	</div>

	<script>
	(function() {
		'use strict';
		var nonce   = <?php echo wp_json_encode( wp_create_nonce( 'ta_analytics_nonce' ) ); ?>;
		var filters = <?php echo wp_json_encode( $filters ); ?>;

		function taEsc(s) {
			if (!s) return '';
			return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
		}
		function taParseUA(ua) {
			if (!ua) return {browser:'Unknown',os:'Unknown',device:'desktop',icon:'🖥️'};
			var u=ua.toLowerCase(), browser='Unknown', os='Unknown', device='desktop', icon='🖥️';
			if (/ipad/.test(u))                  { os='iPadOS'; device='tablet'; icon='📱'; }
			else if (/iphone|ipod/.test(u))      { os='iOS'; device='mobile'; icon='📱'; }
			else if (/android/.test(u))          { os='Android'; device=/mobile/.test(u)?'mobile':'tablet'; icon='📱'; }
			else if (/windows/.test(u))          { os='Windows'; }
			else if (/macintosh|mac os/.test(u)) { os='macOS'; }
			else if (/linux/.test(u))            { os='Linux'; }
			if (/edg\//.test(u))                 { browser='Edge'; }
			else if (/opr\/|opera/.test(u))      { browser='Opera'; }
			else if (/chrome\//.test(u))         { browser='Chrome'; }
			else if (/firefox\//.test(u))        { browser='Firefox'; }
			else if (/safari\//.test(u))         { browser='Safari'; }
			return {browser:browser, os:os, device:device, icon:icon};
		}
		function taCountryFlag(code) {
			if (!code||code.length!==2) return '';
			try { return String.fromCodePoint(code.toUpperCase().charCodeAt(0)+127397,code.toUpperCase().charCodeAt(1)+127397); }
			catch(e) { return ''; }
		}
		function taTimeAgo(ts) {
			var d=Math.floor((Date.now()-ts.getTime())/1000);
			if(d<60)    return d+' secs';
			if(d<3600)  return Math.floor(d/60)+' mins';
			if(d<86400) return Math.floor(d/3600)+' hours';
			return Math.floor(d/86400)+' days';
		}
		function taBrowserCell(c) {
			var ua=c.client_user_agent||c.user_agent||'', p=taParseUA(ua);
			if(c.client_user_agent) {
				return '<div style="line-height:1.4;"><strong>'+taEsc(p.browser!=='Unknown'?p.browser:'Browser')+'</strong> on '+taEsc(p.os!=='Unknown'?p.os:'Unknown OS')+'<br><span style="color:#8e8e93;font-size:10px;">'+taEsc(p.icon)+' '+taEsc(p.device.charAt(0).toUpperCase()+p.device.slice(1))+'</span></div>';
			} else if(c.content_type==='rest_api'||c.content_type==='ajax') {
				return '<div style="line-height:1.4;"><strong style="color:#8b5cf6;">Headless</strong><br><span style="color:#8e8e93;font-size:10px;">🖥️ Next.js server</span></div>';
			} else if(ua) {
				return '<div style="line-height:1.4;"><strong>'+taEsc(p.browser)+'</strong> on '+taEsc(p.os)+'<br><span style="color:#8e8e93;font-size:10px;">'+taEsc(p.icon)+' '+taEsc(p.device.charAt(0).toUpperCase()+p.device.slice(1))+'</span></div>';
			}
			return '<span style="color:#d1d1d6;font-size:11px;">&#8212;</span>';
		}
		function taLocCell(c) {
			var f=taCountryFlag(c.country_code);
			return f ? f+' <span style="font-size:11px;color:#646970;">'+taEsc(c.country_code)+'</span>' : '<span style="color:#d1d1d6;font-size:11px;">&#8212;</span>';
		}
		function taRenderLlmRow(c) {
			var ts=new Date(c.visit_timestamp.replace(' ','T')+'Z');
			var su=c.url&&c.url.length>30?c.url.substring(0,27)+'...':(c.url||'—');
			var sr=c.referer&&c.referer.length>35?c.referer.substring(0,32)+'...':(c.referer||'—');
			var title=c.post_title||su;
			return '<td><span class="ta-bot-badge" style="white-space:normal;word-break:break-word;">'+taEsc(c.ai_platform)+'</span></td>'
				+'<td title="'+taEsc(c.url)+'"><strong style="font-size:12px;">'+taEsc(title)+'</strong><br><code style="font-size:9px;color:#8e8e93;">'+taEsc(su)+'</code></td>'
				+'<td style="font-size:11px;">'+taBrowserCell(c)+'</td>'
				+'<td style="text-align:center;font-size:14px;" title="'+taEsc(c.country_code||'Unknown')+'">'+taLocCell(c)+'</td>'
				+'<td style="text-align:center;font-size:11px;">'+taEsc(ts.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}))+'</td>'
				+'<td style="text-align:center;font-size:11px;color:#646970;">'+taEsc(ts.toLocaleTimeString('en-US',{hour:'numeric',minute:'2-digit'}))+'</td>'
				+'<td style="text-align:center;font-size:10px;color:#8e8e93;">'+taEsc(taTimeAgo(ts))+'</td>'
				+'<td style="font-size:10px;color:#8e8e93;" title="'+taEsc(c.referer||'')+'">'+taEsc(sr)+'</td>';
		}
		function taRenderGoogleRow(c) {
			var ts=new Date(c.visit_timestamp.replace(' ','T')+'Z');
			var su=c.url&&c.url.length>30?c.url.substring(0,27)+'...':(c.url||'—');
			var title=c.post_title||c.url||'—';
			var badge = c.ai_platform === 'Google AI Mode'
				? '<span class="ta-bot-badge" style="background:#f3e8ff;color:#7c3aed;border-color:#d8b4fe;white-space:nowrap;" title="Google AI Overview click">Google AI Mode</span>'
				: '<span class="ta-bot-badge" style="background:#e8f0fe;color:#1a73e8;border-color:#c5d8fd;white-space:nowrap;" title="Traditional organic search click">Google Search</span>';
			return '<td>'+badge+'</td>'
				+'<td title="'+taEsc(c.url)+'"><strong style="font-size:12px;">'+taEsc(title)+'</strong><br><code style="font-size:9px;color:#8e8e93;">'+taEsc(su)+'</code></td>'
				+'<td style="font-size:11px;">'+taBrowserCell(c)+'</td>'
				+'<td style="text-align:center;font-size:14px;" title="'+taEsc(c.country_code||'Unknown')+'">'+taLocCell(c)+'</td>'
				+'<td style="text-align:center;font-size:11px;">'+taEsc(ts.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}))+'</td>'
				+'<td style="text-align:center;font-size:11px;color:#646970;">'+taEsc(ts.toLocaleTimeString('en-US',{hour:'numeric',minute:'2-digit'}))+'</td>'
				+'<td style="text-align:center;font-size:10px;color:#8e8e93;">'+taEsc(taTimeAgo(ts))+'</td>';
		}

		function taPaginationInit(opts) {
			var cur=1, tot=parseInt(opts.totEl.textContent,10)||1;
			function sync() { opts.prev.disabled=(cur<=1); opts.next.disabled=(cur>=tot); }
			function go(page) {
				opts.prev.disabled=true; opts.next.disabled=true;
				opts.spin.style.display='inline-block';
				var body=new URLSearchParams({action:'ta_citations_paginate',nonce:nonce,section:opts.section,page:page});
				if(opts.fil) Object.keys(opts.fil).forEach(function(k){ if(opts.fil[k]) body.append(k,opts.fil[k]); });
				fetch(ajaxurl,{method:'POST',body:body})
					.then(function(r){return r.json();})
					.then(function(res){
						if(!res.success){opts.spin.style.display='none';sync();return;}
						cur=res.data.page; tot=res.data.total_pages;
						opts.curEl.textContent=cur; opts.totEl.textContent=tot;
						opts.tbody.innerHTML='';
						res.data.rows.forEach(function(row){
							var tr=document.createElement('tr');
							tr.innerHTML=opts.render(row);
							opts.tbody.appendChild(tr);
						});
						opts.spin.style.display='none'; sync();
					})
					.catch(function(){opts.spin.style.display='none';sync();});
			}
			opts.prev.addEventListener('click',function(){if(cur>1) go(cur-1);});
			opts.next.addEventListener('click',function(){if(cur<tot) go(cur+1);});
			sync();
		}

		// Recent LLMs Visits — collapse/expand toggle (auto-collapsed when a date filter is active).
		var recHead=document.getElementById('ta-recent-head');
		if(recHead) {
			recHead.addEventListener('click',function(){
				document.getElementById('ta-recent-card').classList.toggle('ta-recent-collapsed');
			});
		}

		// LLM pagination
		var llmT=document.getElementById('ta-llm-tbody'),llmPr=document.getElementById('ta-llm-prev'),llmNx=document.getElementById('ta-llm-next');
		if(llmT&&llmPr&&llmNx) {
			taPaginationInit({section:'llm',tbody:llmT,prev:llmPr,next:llmNx,curEl:document.getElementById('ta-llm-current-page'),totEl:document.getElementById('ta-llm-total-pages'),spin:document.getElementById('ta-llm-spinner'),render:taRenderLlmRow,fil:filters});
		}
		// Google Search pagination
		var gsT=document.getElementById('ta-google-tbody'),gsPr=document.getElementById('ta-google-prev'),gsNx=document.getElementById('ta-google-next');
		if(gsT&&gsPr&&gsNx) {
			taPaginationInit({section:'google',tbody:gsT,prev:gsPr,next:gsNx,curEl:document.getElementById('ta-google-current-page'),totEl:document.getElementById('ta-google-total-pages'),spin:document.getElementById('ta-google-spinner'),render:taRenderGoogleRow,fil:{}});
		}
	})();
	</script>

	<!-- Charts Section (v3.2.1) -->
	<div class="ta-charts-section" style="margin-top: 20px;">
		<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
			<!-- Daily Trend Chart -->
			<div class="ta-card">
				<div class="ta-card-header">
					<h2><?php esc_html_e( 'Traffic Trend (Last 30 Days)', 'third-audience' ); ?></h2>
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


	</div>


	<!-- Perplexity Search Queries (full width) -->
	<div style="margin-top: 20px;">

		<!-- Perplexity Search Queries -->
		<div class="ta-card">
			<div class="ta-card-header">
				<h2>🔍 <?php esc_html_e( 'Perplexity Search Queries', 'third-audience' ); ?></h2>
				<p class="description" style="margin-top: 6px;"><?php esc_html_e( 'Actual search queries users typed in Perplexity before clicking your link.', 'third-audience' ); ?></p>
			</div>
			<div class="ta-card-body" style="overflow-x: auto;">
				<?php if ( empty( $perplexity_query_list ) ) : ?>
					<p style="text-align: center; color: #646970; padding: 30px 0;">
						<?php esc_html_e( 'No Perplexity queries captured yet.', 'third-audience' ); ?>
					</p>
				<?php else : ?>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Search Query', 'third-audience' ); ?></th>
								<th style="width: 60px; text-align: center;"><?php esc_html_e( 'Times', 'third-audience' ); ?></th>
								<th style="width: 120px;"><?php esc_html_e( 'Page Visited', 'third-audience' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $perplexity_query_list as $q ) : ?>
								<tr>
									<td style="font-size: 12px; font-weight: 500;">
										"<?php echo esc_html( $q['search_query'] ); ?>"
									</td>
									<td style="text-align: center;">
										<span style="background: #1FB6D0; color: #fff; padding: 2px 7px; border-radius: 10px; font-size: 11px; font-weight: 600;">
											<?php echo esc_html( $q['frequency'] ); ?>
										</span>
									</td>
									<td style="font-size: 11px; color: #646970;" title="<?php echo esc_attr( $q['url'] ); ?>">
										<?php echo esc_html( ta_page_display_title( $q['post_title'], $q['url'] ) ); ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		</div>

	</div>


	<!-- Help Section -->
	<div class="ta-card" style="margin-top: 20px; background: #f5f5f7;">
		<div class="ta-card-header">
			<h2><?php esc_html_e( 'Understanding LLM Traffic', 'third-audience' ); ?></h2>
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
	var chartLabels    = <?php echo wp_json_encode( $chart_labels ); ?>;
	var chartCitations = <?php echo wp_json_encode( $chart_citations ); ?>;
	var chartCrawls    = <?php echo wp_json_encode( $chart_crawls ); ?>;
	var chartGoogle    = <?php echo wp_json_encode( $chart_google ); ?>;
	var chartHtml      = <?php echo wp_json_encode( $chart_html ); ?>;
	var chartMd        = <?php echo wp_json_encode( $chart_md ); ?>;
	var chartOther     = <?php echo wp_json_encode( $chart_other ); ?>;
	var platformLabels = <?php echo wp_json_encode( $platform_labels ); ?>;
	var platformData   = <?php echo wp_json_encode( $platform_data ); ?>;
	var weeklyData     = <?php echo wp_json_encode( $weekly_data ); ?>;
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
						label: 'LLM Citations',
						data: chartCitations,
						borderColor: '#007aff',
						backgroundColor: 'rgba(0, 122, 255, 0.1)',
						fill: true,
						tension: 0.4,
						pointRadius: 2,
						pointHoverRadius: 5
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

	// 1b. Daily Traffic by Type (HTML / MD / Other) Line Chart
	var dailyTypeCtx = document.getElementById('ta-daily-type-chart');
	if (dailyTypeCtx) {
		new Chart(dailyTypeCtx, {
			type: 'line',
			data: {
				labels: chartLabels,
				datasets: [
					{
						label: 'HTML Clicks',
						data: chartHtml,
						borderColor: '#007aff',
						backgroundColor: 'rgba(0, 122, 255, 0.08)',
						fill: true,
						tension: 0.4,
						pointRadius: 2,
						pointHoverRadius: 5
					},
					{
						label: 'MD Clicks',
						data: chartMd,
						borderColor: '#ff9500',
						backgroundColor: 'rgba(255, 149, 0, 0.08)',
						fill: true,
						tension: 0.4,
						pointRadius: 2,
						pointHoverRadius: 5
					},
					{
						label: 'Other',
						data: chartOther,
						borderColor: '#8e8e93',
						backgroundColor: 'transparent',
						borderDash: [4, 4],
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
						ticks: { maxTicksLimit: 10, font: { size: 11 } }
					}
				},
				interaction: { intersect: false, mode: 'index' }
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

	// 3. Weekly HTML vs MD Bar Chart
	var weeklyTypeCtx = document.getElementById('ta-weekly-type-chart');
	if (weeklyTypeCtx) {
		new Chart(weeklyTypeCtx, {
			type: 'bar',
			data: {
				labels: weeklyData.map(function(w) { return w.label; }),
				datasets: [
					{
						label: 'HTML Clicks',
						data: weeklyData.map(function(w) { return w.html; }),
						backgroundColor: '#007aff',
						borderRadius: 4
					},
					{
						label: 'MD Clicks',
						data: weeklyData.map(function(w) { return w.md; }),
						backgroundColor: '#ff9500',
						borderRadius: 4
					},
					{
						label: 'Other',
						data: weeklyData.map(function(w) { return w.other; }),
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
					x: { grid: { display: false } }
				},
				barPercentage: 0.7,
				categoryPercentage: 0.8
			})
		});
	}

});
</script>
