<?php
/**
 * Citation Query helpers — single source of truth for citation drill-down filtering.
 *
 * The AI Citations dashboard shows a count per platform / country / browser /
 * device / page-type, then lets the user drill into the exact visits behind that
 * count. The summary count (GROUP BY in the page view) and the drill-down rows
 * (WHERE in the AJAX handler) MUST use identical bucketing, otherwise the two
 * disagree ("data mismatch"). Centralising the CASE expressions + base WHERE here
 * means the view and the AJAX handler derive from the same definitions, so the
 * numbers always reconcile by construction — and it stays versatile (works on any
 * site: browser/device buckets are UA-derived, page type uses each site's own
 * post types).
 *
 * @package ThirdAudience
 * @since   3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Citation_Query
 *
 * @since 3.6.0
 */
class TA_Citation_Query {

	/**
	 * Base WHERE clauses defining a real LLM citation visit (Google excluded —
	 * shown separately). Returned as an array to be joined with AND.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	public static function base_where_clauses() {
		return array(
			"traffic_type = 'citation_click'",
			"(client_user_agent IS NOT NULL OR content_type IN ('rest_api', 'ajax') OR user_agent NOT LIKE 'Headless%')",
			"url NOT LIKE '%/wp-admin%'",
			"url NOT LIKE '%/wp-login%'",
			"url NOT LIKE '%admin-ajax.php%'",
			"url NOT LIKE '%/wp-cron%'",
			"url NOT LIKE '%/xmlrpc%'",
			"ai_platform NOT IN ('Google Search', 'Google AI Mode')",
		);
	}

	/**
	 * SQL CASE expression that buckets a row into a browser name.
	 * Order matters (Edge/Opera UAs also contain "Chrome"; Chrome UA contains
	 * "Safari"). Buckets are mutually exclusive + exhaustive (ELSE 'Other'),
	 * so every bucket summed equals the total row count.
	 *
	 * @since 3.6.0
	 * @return string
	 */
	public static function browser_case() {
		$ua = 'COALESCE(client_user_agent, user_agent)';
		return "CASE
			WHEN {$ua} LIKE '%Edg%' THEN 'Edge'
			WHEN {$ua} LIKE '%OPR%' OR {$ua} LIKE '%Opera%' THEN 'Opera'
			WHEN {$ua} LIKE '%Chrome%' THEN 'Chrome'
			WHEN {$ua} LIKE '%Firefox%' THEN 'Firefox'
			WHEN {$ua} LIKE '%Safari%' THEN 'Safari'
			ELSE 'Other'
		END";
	}

	/**
	 * SQL CASE expression that buckets a row into 'mobile' or 'desktop'.
	 *
	 * @since 3.6.0
	 * @return string
	 */
	public static function device_case() {
		$ua = 'COALESCE(client_user_agent, user_agent)';
		return "CASE
			WHEN {$ua} LIKE '%Mobile%' OR {$ua} LIKE '%iPhone%' OR {$ua} LIKE '%Android%' THEN 'mobile'
			ELSE 'desktop'
		END";
	}

	/**
	 * SQL expression that buckets a row by page type (post_type column, with a
	 * stable 'other' fallback for headless/deleted URLs).
	 *
	 * @since 3.6.0
	 * @return string
	 */
	public static function pagetype_case() {
		return "COALESCE(NULLIF(post_type, ''), 'other')";
	}

	/**
	 * WHERE fragment selecting exactly one browser bucket (matches browser_case).
	 *
	 * @since 3.6.0
	 * @param string $bucket Browser bucket name.
	 * @return string Escaped SQL fragment.
	 */
	public static function browser_where( $bucket ) {
		return '(' . self::browser_case() . ") = '" . esc_sql( $bucket ) . "'";
	}

	/**
	 * WHERE fragment selecting mobile or desktop.
	 *
	 * @since 3.6.0
	 * @param string $value 'mobile' or 'desktop'.
	 * @return string Escaped SQL fragment.
	 */
	public static function device_where( $value ) {
		$value = ( 'mobile' === $value ) ? 'mobile' : 'desktop';
		return '(' . self::device_case() . ") = '" . esc_sql( $value ) . "'";
	}

	/**
	 * WHERE fragment selecting one page-type bucket (matches pagetype_case).
	 *
	 * @since 3.6.0
	 * @param string $bucket Post type slug, or 'other'.
	 * @return string Escaped SQL fragment.
	 */
	public static function pagetype_where( $bucket ) {
		return '(' . self::pagetype_case() . ") = '" . esc_sql( $bucket ) . "'";
	}

	/**
	 * Human-friendly label for a page-type bucket, using the site's own
	 * registered post types (so it auto-adapts on any install).
	 *
	 * @since 3.6.0
	 * @param string $bucket Post type slug or 'other'/'homepage'.
	 * @return string
	 */
	public static function pagetype_label( $bucket ) {
		if ( 'other' === $bucket || '' === $bucket || null === $bucket ) {
			return __( 'Other', 'third-audience' );
		}
		if ( 'homepage' === $bucket ) {
			return __( 'Homepage', 'third-audience' );
		}
		$obj = get_post_type_object( $bucket );
		if ( $obj && isset( $obj->labels->singular_name ) && $obj->labels->singular_name ) {
			return $obj->labels->singular_name;
		}
		return ucwords( str_replace( array( '-', '_' ), ' ', $bucket ) );
	}
}
