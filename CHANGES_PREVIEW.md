# AI Citations Browser & Country Tracking - Implementation Plan

**Date:** 2026-02-16
**Status:** AWAITING APPROVAL - NO CHANGES MADE YET

---

## 📋 SUMMARY OF CHANGES

This document outlines all modifications to add **Browser/Device tracking** and **Country/Location** display to the AI Citations page, plus **CSV Export** with all data and **Filter dropdowns** for Browser, Country, and Device.

---

## 🎯 WHAT WILL BE ADDED

### ✅ New Features:
1. **Browser & Device Column** - Shows browser name, OS, and device type (Desktop/Mobile)
2. **Location/Country Column** - Shows country flag and code
3. **CSV Export Button** - Downloads all citation data including user agent and IP
4. **Filter Dropdowns** - Filter by Browser, Country, and Device Type

### 📊 Data Already Being Captured (No Changes Needed):
- ✅ `user_agent` - Full browser string
- ✅ `ip_address` - Visitor IP
- ✅ `country_code` - 2-letter country code
- ✅ All existing citation data

---

## 📁 FILES THAT WILL BE MODIFIED

### 1. `/admin/views/ai-citations-page.php` (PRIMARY FILE)
**Lines to modify:** ~30, ~140-153, ~450-488, ~614-670
**Action:** Add browser/country columns, filters, and helper functions

### 2. `/admin/class-ta-admin.php`
**Lines to add:** After line ~90 (in `handle_export_request` or new function)
**Action:** Add CSV export handler for citations

### 3. `/includes/class-ta-bot-analytics.php` (OPTIONAL - only if you want reusable function)
**Action:** Add user agent parser function (can also go directly in view file)

---

## 🔧 DETAILED CHANGES

---

## FILE 1: `/admin/views/ai-citations-page.php`

### CHANGE 1: Add New Filters (Lines 16-30)

**CURRENT CODE:**
```php
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
```

**NEW CODE (ADD AFTER LINE 29):**
```php
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
```

---

### CHANGE 2: Add Browser/Country/Device to WHERE Clause (Lines 32-50)

**CURRENT CODE (Line 32-49):**
```php
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
```

**ADD AFTER LINE 49:**
```php
// NEW: Filter by browser (partial match in user_agent)
if ( ! empty( $filters['browser'] ) ) {
	$browser_term = '%' . $wpdb->esc_like( $filters['browser'] ) . '%';
	$where_clauses[] = $wpdb->prepare( 'user_agent LIKE %s', $browser_term );
}

// NEW: Filter by country code
if ( ! empty( $filters['country'] ) ) {
	$where_clauses[] = $wpdb->prepare( 'country_code = %s', $filters['country'] );
}

// NEW: Filter by device type (Mobile vs Desktop)
if ( ! empty( $filters['device'] ) ) {
	if ( 'mobile' === $filters['device'] ) {
		$where_clauses[] = "(user_agent LIKE '%Mobile%' OR user_agent LIKE '%iPhone%' OR user_agent LIKE '%Android%')";
	} elseif ( 'desktop' === $filters['device'] ) {
		$where_clauses[] = "(user_agent NOT LIKE '%Mobile%' AND user_agent NOT LIKE '%iPhone%' AND user_agent NOT LIKE '%Android%')";
	}
}
```

---

### CHANGE 3: Update Recent Citations Query to Include user_agent, ip_address, country_code (Lines 138-153)

**CURRENT CODE:**
```php
// Recent citations (ALL - not just those with queries).
// Use only core columns that exist in all database versions.
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
```

**NEW CODE (REPLACE Lines 138-153):**
```php
// Recent citations (ALL - not just those with queries).
// UPDATED: Include user_agent, ip_address, country_code for display
$recent_citations = $wpdb->get_results(
	"SELECT
		ai_platform,
		url,
		post_title,
		search_query,
		referer,
		user_agent,
		ip_address,
		country_code,
		visit_timestamp
	FROM {$table_name}
	WHERE {$where_sql}
	ORDER BY visit_timestamp DESC
	LIMIT 30",
	ARRAY_A
);
```

---

### CHANGE 4: Add Helper Functions for User Agent Parsing (ADD AFTER LINE 260, BEFORE HTML)

**ADD NEW CODE:**
```php
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

	// Detect OS
	if ( strpos( $user_agent, 'Windows NT 10' ) !== false ) {
		$os = 'Windows 10';
	} elseif ( strpos( $user_agent, 'Windows NT 11' ) !== false ) {
		$os = 'Windows 11';
	} elseif ( strpos( $user_agent, 'Windows' ) !== false ) {
		$os = 'Windows';
	} elseif ( strpos( $user_agent, 'Mac OS X' ) !== false || strpos( $user_agent, 'Macintosh' ) !== false ) {
		$os = 'macOS';
	} elseif ( strpos( $user_agent, 'Linux' ) !== false ) {
		$os = 'Linux';
	} elseif ( strpos( $user_agent, 'iPhone' ) !== false ) {
		$os = 'iOS (iPhone)';
	} elseif ( strpos( $user_agent, 'iPad' ) !== false ) {
		$os = 'iOS (iPad)';
	} elseif ( strpos( $user_agent, 'Android' ) !== false ) {
		$os = 'Android';
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
 * Get available browsers from database for filter dropdown.
 */
$available_browsers = $wpdb->get_results(
	"SELECT
		CASE
			WHEN user_agent LIKE '%Edg%' THEN 'Edge'
			WHEN user_agent LIKE '%Chrome%' AND user_agent NOT LIKE '%Edg%' THEN 'Chrome'
			WHEN user_agent LIKE '%Firefox%' THEN 'Firefox'
			WHEN user_agent LIKE '%Safari%' AND user_agent NOT LIKE '%Chrome%' THEN 'Safari'
			WHEN user_agent LIKE '%Opera%' OR user_agent LIKE '%OPR%' THEN 'Opera'
			ELSE 'Other'
		END as browser,
		COUNT(*) as count
	FROM {$table_name}
	WHERE traffic_type = 'citation_click' AND user_agent != ''
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
```

---

### CHANGE 5: Update Filter Section HTML (Lines 450-488)

**CURRENT CODE:**
```php
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
```

**NEW CODE (REPLACE):**
```php
<div class="ta-filter-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px;">
	<!-- Row 1: Date, Platform, Search -->
	<div class="ta-filter-item">
		<label><?php esc_html_e( 'Date Range', 'third-audience' ); ?></label>
		<div class="ta-date-range" style="display: flex; align-items: center; gap: 8px;">
			<input type="date" name="date_from" value="<?php echo esc_attr( $filters['date_from'] ?? '' ); ?>" style="flex: 1;">
			<span>—</span>
			<input type="date" name="date_to" value="<?php echo esc_attr( $filters['date_to'] ?? '' ); ?>" style="flex: 1;">
		</div>
	</div>
	<div class="ta-filter-item">
		<label><?php esc_html_e( 'AI Platform', 'third-audience' ); ?></label>
		<select name="platform" style="width: 100%;">
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
		<input type="text" name="search" placeholder="<?php esc_attr_e( 'URL, title, or query...', 'third-audience' ); ?>" value="<?php echo esc_attr( $filters['search'] ?? '' ); ?>" style="width: 100%;">
	</div>

	<!-- Row 2: NEW - Browser, Country, Device -->
	<div class="ta-filter-item">
		<label>🌐 <?php esc_html_e( 'Browser', 'third-audience' ); ?></label>
		<select name="browser" style="width: 100%;">
			<option value=""><?php esc_html_e( 'All Browsers', 'third-audience' ); ?></option>
			<?php foreach ( $available_browsers as $browser_row ) : ?>
				<option value="<?php echo esc_attr( $browser_row['browser'] ); ?>" <?php selected( $filters['browser'] ?? '', $browser_row['browser'] ); ?>>
					<?php echo esc_html( $browser_row['browser'] ); ?> (<?php echo esc_html( $browser_row['count'] ); ?>)
				</option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="ta-filter-item">
		<label>🗺️ <?php esc_html_e( 'Country', 'third-audience' ); ?></label>
		<select name="country" style="width: 100%;">
			<option value=""><?php esc_html_e( 'All Countries', 'third-audience' ); ?></option>
			<?php foreach ( $available_countries as $country_row ) : ?>
				<option value="<?php echo esc_attr( $country_row['country_code'] ); ?>" <?php selected( $filters['country'] ?? '', $country_row['country_code'] ); ?>>
					<?php echo ta_get_country_flag( $country_row['country_code'] ); ?> <?php echo esc_html( $country_row['country_code'] ); ?> (<?php echo esc_html( $country_row['count'] ); ?>)
				</option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="ta-filter-item">
		<label>📱 <?php esc_html_e( 'Device Type', 'third-audience' ); ?></label>
		<select name="device" style="width: 100%;">
			<option value=""><?php esc_html_e( 'All Devices', 'third-audience' ); ?></option>
			<option value="desktop" <?php selected( $filters['device'] ?? '', 'desktop' ); ?>>🖥️ <?php esc_html_e( 'Desktop', 'third-audience' ); ?></option>
			<option value="mobile" <?php selected( $filters['device'] ?? '', 'mobile' ); ?>>📱 <?php esc_html_e( 'Mobile', 'third-audience' ); ?></option>
		</select>
	</div>

	<!-- Row 3: Actions -->
	<div class="ta-filter-item ta-filter-actions" style="grid-column: 1 / -1;">
		<label>&nbsp;</label>
		<div style="display: flex; gap: 10px; align-items: center;">
			<button type="submit" class="button button-primary">
				<span class="dashicons dashicons-filter" style="margin-top: 3px;"></span> <?php esc_html_e( 'Apply Filters', 'third-audience' ); ?>
			</button>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-ai-citations' ) ); ?>" class="button">
				<span class="dashicons dashicons-dismiss" style="margin-top: 3px;"></span> <?php esc_html_e( 'Reset', 'third-audience' ); ?>
			</a>
			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array_merge( $_GET, array( 'action' => 'ta_export_citations_csv', 'export_format' => 'csv' ) ) ), 'ta_export_citations' ) ); ?>" class="button button-secondary">
				<span class="dashicons dashicons-download" style="margin-top: 3px;"></span> <?php esc_html_e( 'Export CSV', 'third-audience' ); ?>
			</a>
			<span style="color: #646970; font-size: 12px; margin-left: 10px;">
				<?php
				printf(
					esc_html__( 'Showing %s of %s citations', 'third-audience' ),
					'<strong>' . number_format( count( $recent_citations ) ) . '</strong>',
					'<strong>' . number_format( $total_citations ) . '</strong>'
				);
				?>
			</span>
		</div>
	</div>
</div>
```

---

### CHANGE 6: Update Recent Citations Table with Browser & Country Columns (Lines 614-670)

**CURRENT TABLE HEADER (Line 615-628):**
```php
<thead>
	<tr>
		<th style="width: 90px;"><?php esc_html_e( 'Platform', 'third-audience' ); ?></th>
		<th style="width: 60px; text-align: center;"><?php esc_html_e( 'Type', 'third-audience' ); ?></th>
		<th style="width: 200px;"><?php esc_html_e( 'Page', 'third-audience' ); ?></th>
		<th style="width: 160px;"><?php esc_html_e( 'Search Query', 'third-audience' ); ?></th>
		<th style="width: 95px; text-align: center;"><?php esc_html_e( 'Date', 'third-audience' ); ?></th>
		<th style="width: 80px; text-align: center;"><?php esc_html_e( 'Time', 'third-audience' ); ?></th>
		<th style="width: 70px; text-align: center;"><?php esc_html_e( 'Ago', 'third-audience' ); ?></th>
		<th><?php esc_html_e( 'Referrer', 'third-audience' ); ?></th>
	</tr>
</thead>
```

**NEW TABLE HEADER (REPLACE):**
```php
<thead>
	<tr>
		<th style="width: 90px;"><?php esc_html_e( 'Platform', 'third-audience' ); ?></th>
		<th style="width: 60px; text-align: center;"><?php esc_html_e( 'Type', 'third-audience' ); ?></th>
		<th style="width: 180px;"><?php esc_html_e( 'Page', 'third-audience' ); ?></th>
		<th style="width: 140px;"><?php esc_html_e( 'Search Query', 'third-audience' ); ?></th>
		<!-- NEW COLUMNS -->
		<th style="width: 180px;">🌐 <?php esc_html_e( 'Browser & Device', 'third-audience' ); ?></th>
		<th style="width: 70px; text-align: center;">🗺️ <?php esc_html_e( 'Location', 'third-audience' ); ?></th>
		<!-- END NEW -->
		<th style="width: 90px; text-align: center;"><?php esc_html_e( 'Date', 'third-audience' ); ?></th>
		<th style="width: 75px; text-align: center;"><?php esc_html_e( 'Time', 'third-audience' ); ?></th>
		<th style="width: 65px; text-align: center;"><?php esc_html_e( 'Ago', 'third-audience' ); ?></th>
		<th><?php esc_html_e( 'Referrer', 'third-audience' ); ?></th>
	</tr>
</thead>
```

**CURRENT TABLE BODY (Lines 630-667):**
```php
<tbody>
	<?php foreach ( $recent_citations as $citation ) : ?>
		<?php
		$ts = strtotime( $citation['visit_timestamp'] );
		$time_ago   = human_time_diff( $ts, current_time( 'timestamp' ) );
		$short_url  = strlen( $citation['url'] ) > 30 ? substr( $citation['url'], 0, 27 ) . '...' : $citation['url'];
		$short_ref  = ! empty( $citation['referer'] ) ? ( strlen( $citation['referer'] ) > 35 ? substr( $citation['referer'], 0, 32 ) . '...' : $citation['referer'] ) : '—';
		// Detect method from URL (UTM) or referrer
		$has_utm = strpos( $citation['url'], 'utm_source=' ) !== false;
		$method_color = $has_utm ? '#34c759' : '#007aff';
		$method_label = $has_utm ? 'UTM' : 'Ref';
		?>
		<tr>
			<td><span class="ta-bot-badge"><?php echo esc_html( $citation['ai_platform'] ); ?></span></td>
			<td style="text-align: center;">
				<span style="background: <?php echo esc_attr( $method_color ); ?>; color: #fff; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: 600;">
					<?php echo esc_html( $method_label ); ?>
				</span>
			</td>
			<td title="<?php echo esc_attr( $citation['url'] ); ?>">
				<strong style="font-size: 12px;"><?php echo esc_html( $citation['post_title'] ?: 'Untitled' ); ?></strong>
				<br><code style="font-size: 9px; color: #8e8e93;"><?php echo esc_html( $short_url ); ?></code>
			</td>
			<td style="font-size: 11px;">
				<?php if ( ! empty( $citation['search_query'] ) ) : ?>
					<span style="color: #007aff;"><?php echo esc_html( substr( $citation['search_query'], 0, 35 ) ); ?><?php echo strlen( $citation['search_query'] ) > 35 ? '...' : ''; ?></span>
				<?php else : ?>
					<span style="color: #d1d1d6;">—</span>
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
```

**NEW TABLE BODY (REPLACE):**
```php
<tbody>
	<?php foreach ( $recent_citations as $citation ) : ?>
		<?php
		$ts = strtotime( $citation['visit_timestamp'] );
		$time_ago   = human_time_diff( $ts, current_time( 'timestamp' ) );
		$short_url  = strlen( $citation['url'] ) > 30 ? substr( $citation['url'], 0, 27 ) . '...' : $citation['url'];
		$short_ref  = ! empty( $citation['referer'] ) ? ( strlen( $citation['referer'] ) > 35 ? substr( $citation['referer'], 0, 32 ) . '...' : $citation['referer'] ) : '—';

		// Detect method from URL (UTM) or referrer
		$has_utm = strpos( $citation['url'], 'utm_source=' ) !== false;
		$method_color = $has_utm ? '#34c759' : '#007aff';
		$method_label = $has_utm ? 'UTM' : 'Ref';

		// NEW: Parse user agent
		$ua_data = ta_parse_user_agent( $citation['user_agent'] ?? '' );

		// NEW: Get country flag
		$country_flag = ta_get_country_flag( $citation['country_code'] ?? '' );
		?>
		<tr>
			<td><span class="ta-bot-badge"><?php echo esc_html( $citation['ai_platform'] ); ?></span></td>
			<td style="text-align: center;">
				<span style="background: <?php echo esc_attr( $method_color ); ?>; color: #fff; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: 600;">
					<?php echo esc_html( $method_label ); ?>
				</span>
			</td>
			<td title="<?php echo esc_attr( $citation['url'] ); ?>">
				<strong style="font-size: 12px;"><?php echo esc_html( $citation['post_title'] ?: 'Untitled' ); ?></strong>
				<br><code style="font-size: 9px; color: #8e8e93;"><?php echo esc_html( $short_url ); ?></code>
			</td>
			<td style="font-size: 11px;">
				<?php if ( ! empty( $citation['search_query'] ) ) : ?>
					<span style="color: #007aff;"><?php echo esc_html( substr( $citation['search_query'], 0, 30 ) ); ?><?php echo strlen( $citation['search_query'] ) > 30 ? '...' : ''; ?></span>
				<?php else : ?>
					<span style="color: #d1d1d6;">—</span>
				<?php endif; ?>
			</td>

			<!-- NEW: Browser & Device Column -->
			<td style="font-size: 11px;">
				<div style="line-height: 1.4;">
					<strong><?php echo esc_html( $ua_data['browser'] ); ?></strong> on <?php echo esc_html( $ua_data['os'] ); ?>
					<br>
					<span style="color: #8e8e93; font-size: 10px;">
						<?php echo esc_html( $ua_data['icon'] ); ?> <?php echo esc_html( ucfirst( $ua_data['device'] ) ); ?>
					</span>
				</div>
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
```

---

## FILE 2: `/admin/class-ta-admin.php`

### ADD NEW METHOD: CSV Export Handler (Add after line ~640 in class)

**NEW CODE TO ADD:**
```php
/**
 * Handle AI Citations CSV export.
 *
 * @since 3.4.8
 * @return void
 */
public function handle_citations_export() {
	// Check for export action.
	if ( empty( $_GET['action'] ) || 'ta_export_citations_csv' !== $_GET['action'] ) {
		return;
	}

	// Verify nonce.
	if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'ta_export_citations' ) ) {
		wp_die( esc_html__( 'Security check failed', 'third-audience' ) );
	}

	// Check permissions.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to export data.', 'third-audience' ) );
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'ta_bot_analytics';

	// Build WHERE clause from filters (same as main page).
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
	if ( ! empty( $_GET['browser'] ) ) {
		$filters['browser'] = sanitize_text_field( wp_unslash( $_GET['browser'] ) );
	}
	if ( ! empty( $_GET['country'] ) ) {
		$filters['country'] = sanitize_text_field( wp_unslash( $_GET['country'] ) );
	}
	if ( ! empty( $_GET['device'] ) ) {
		$filters['device'] = sanitize_text_field( wp_unslash( $_GET['device'] ) );
	}

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
	if ( ! empty( $filters['browser'] ) ) {
		$browser_term = '%' . $wpdb->esc_like( $filters['browser'] ) . '%';
		$where_clauses[] = $wpdb->prepare( 'user_agent LIKE %s', $browser_term );
	}
	if ( ! empty( $filters['country'] ) ) {
		$where_clauses[] = $wpdb->prepare( 'country_code = %s', $filters['country'] );
	}
	if ( ! empty( $filters['device'] ) ) {
		if ( 'mobile' === $filters['device'] ) {
			$where_clauses[] = "(user_agent LIKE '%Mobile%' OR user_agent LIKE '%iPhone%' OR user_agent LIKE '%Android%')";
		} elseif ( 'desktop' === $filters['device'] ) {
			$where_clauses[] = "(user_agent NOT LIKE '%Mobile%' AND user_agent NOT LIKE '%iPhone%' AND user_agent NOT LIKE '%Android%')";
		}
	}

	$where_sql = implode( ' AND ', $where_clauses );

	// Query all matching citations with ALL fields.
	$citations = $wpdb->get_results(
		"SELECT
			id,
			ai_platform,
			url,
			post_title,
			search_query,
			referer,
			user_agent,
			ip_address,
			country_code,
			traffic_type,
			content_type,
			detection_method,
			confidence_score,
			visit_timestamp
		FROM {$table_name}
		WHERE {$where_sql}
		ORDER BY visit_timestamp DESC",
		ARRAY_A
	);

	// Generate filename with timestamp.
	$filename = 'ai-citations-export-' . gmdate( 'Y-m-d-His' ) . '.csv';

	// Set headers for CSV download.
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' );

	// Open output stream.
	$output = fopen( 'php://output', 'w' );

	// Add UTF-8 BOM for Excel compatibility.
	fprintf( $output, chr(0xEF).chr(0xBB).chr(0xBF) );

	// CSV Headers.
	fputcsv( $output, array(
		'ID',
		'Platform',
		'URL',
		'Page Title',
		'Search Query',
		'Referrer',
		'Browser (Parsed)',
		'OS (Parsed)',
		'Device Type',
		'User Agent (Full)',
		'IP Address',
		'Country Code',
		'Traffic Type',
		'Content Type',
		'Detection Method',
		'Confidence Score',
		'Timestamp',
	) );

	// Helper function for parsing user agent (same as in view).
	require_once TA_PLUGIN_DIR . 'includes/class-ta-ai-citation-tracker.php';

	function parse_ua_simple( $ua ) {
		if ( empty( $ua ) ) {
			return array( 'browser' => 'Unknown', 'os' => 'Unknown', 'device' => 'Unknown' );
		}

		$browser = 'Unknown';
		$os = 'Unknown';
		$device = 'Desktop';

		if ( strpos( $ua, 'Edg' ) !== false ) $browser = 'Edge';
		elseif ( strpos( $ua, 'Chrome' ) !== false ) $browser = 'Chrome';
		elseif ( strpos( $ua, 'Firefox' ) !== false ) $browser = 'Firefox';
		elseif ( strpos( $ua, 'Safari' ) !== false && strpos( $ua, 'Chrome' ) === false ) $browser = 'Safari';

		if ( strpos( $ua, 'Windows' ) !== false ) $os = 'Windows';
		elseif ( strpos( $ua, 'Mac' ) !== false ) $os = 'macOS';
		elseif ( strpos( $ua, 'Linux' ) !== false ) $os = 'Linux';
		elseif ( strpos( $ua, 'iPhone' ) !== false ) $os = 'iOS';
		elseif ( strpos( $ua, 'Android' ) !== false ) $os = 'Android';

		if ( strpos( $ua, 'Mobile' ) !== false || strpos( $ua, 'iPhone' ) !== false || strpos( $ua, 'Android' ) !== false ) {
			$device = 'Mobile';
		}

		return array( 'browser' => $browser, 'os' => $os, 'device' => $device );
	}

	// Add data rows.
	foreach ( $citations as $citation ) {
		$ua_parsed = parse_ua_simple( $citation['user_agent'] );

		fputcsv( $output, array(
			$citation['id'],
			$citation['ai_platform'],
			$citation['url'],
			$citation['post_title'],
			$citation['search_query'],
			$citation['referer'],
			$ua_parsed['browser'],
			$ua_parsed['os'],
			$ua_parsed['device'],
			$citation['user_agent'], // Full user agent string
			$citation['ip_address'],
			$citation['country_code'],
			$citation['traffic_type'],
			$citation['content_type'],
			$citation['detection_method'],
			$citation['confidence_score'],
			$citation['visit_timestamp'],
		) );
	}

	fclose( $output );
	exit;
}
```

**REGISTER THIS HANDLER in init() method (around line 74):**

**FIND:**
```php
add_action( 'admin_init', array( $this, 'handle_export_request' ), 5 );
add_action( 'admin_init', array( $this, 'handle_digest_download' ), 5 );
```

**ADD BELOW IT:**
```php
add_action( 'admin_init', array( $this, 'handle_citations_export' ), 5 );
```

---

## 📊 EXPECTED RESULTS AFTER IMPLEMENTATION

### 1. **New Table Display:**
```
Platform | Type | Page | Query | Browser & Device | Location | Date | Time | Ago | Referrer
---------|------|------|-------|------------------|----------|------|------|-----|----------
Copilot  | Ref  | ...  | —     | Chrome on Win    | 🇺🇸 US   | ...  | ...  | 1hr | ...
         |      |      |       | 🖥️ Desktop       |          |      |      |     |
---------|------|------|-------|------------------|----------|------|------|-----|----------
Chatgpt  | Ref  | ...  | —     | Safari on macOS  | 🇬🇧 GB   | ...  | ...  | 2hr | ...
         |      |      |       | 📱 Mobile        |          |      |      |     |
```

### 2. **New Filter Dropdowns:**
- **Browser:** All Browsers, Chrome (15), Safari (8), Firefox (3), Edge (2)
- **Country:** All Countries, 🇺🇸 US (20), 🇬🇧 GB (5), 🇮🇳 IN (3)
- **Device:** All Devices, 🖥️ Desktop, 📱 Mobile

### 3. **CSV Export Columns:**
```
ID | Platform | URL | Page Title | Search Query | Referrer | Browser | OS | Device | User Agent (Full) | IP Address | Country | Timestamp
```

---

## ⚠️ TESTING REQUIRED AFTER IMPLEMENTATION

1. ✅ Check that browser/country columns display correctly
2. ✅ Test all 3 new filters (Browser, Country, Device)
3. ✅ Test CSV export downloads with proper data
4. ✅ Verify country flags show correctly (emoji support)
5. ✅ Test with missing data (no user_agent, no country_code)
6. ✅ Test with different browsers (Chrome, Safari, Firefox, Edge)
7. ✅ Test mobile vs desktop detection
8. ✅ Verify existing filters still work (Date, Platform, Search)

---

## 🚀 READY TO IMPLEMENT?

**NO CHANGES HAVE BEEN MADE YET!**

Please review this document and confirm:
- ✅ You understand all the changes
- ✅ The layout looks correct
- ✅ You want the CSV export to include IP addresses
- ✅ You're ready for me to implement these changes

**Reply with:** "YES, implement all changes" or ask questions about any section.

---

## 📝 NOTES

- User Agent parsing is simplified (not using external library)
- Country flags use Unicode Regional Indicator Symbols (requires Unicode support)
- CSV export uses UTF-8 BOM for Excel compatibility
- All queries use existing database fields (no schema changes needed)
- Filters use LIKE for browser matching (Chrome matches "Chrome", "Chromium", etc.)

---

**END OF DOCUMENT**
