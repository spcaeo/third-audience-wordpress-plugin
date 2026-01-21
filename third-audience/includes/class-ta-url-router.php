<?php
/**
 * URL Router - Handles .md URL requests.
 *
 * Intercepts requests for .md URLs, validates them, checks cache,
 * and fetches markdown from the conversion worker.
 *
 * @package ThirdAudience
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_URL_Router
 *
 * Handles URL routing for markdown (.md) requests.
 *
 * @since 1.0.0
 */
class TA_URL_Router {

	/**
	 * Cache Manager instance.
	 *
	 * @var TA_Cache_Manager
	 */
	private $cache_manager;

	/**
	 * Local Converter instance.
	 *
	 * @var TA_Local_Converter
	 */
	private $converter;

	/**
	 * Security instance.
	 *
	 * @var TA_Security
	 */
	private $security;

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Bot Analytics instance.
	 *
	 * @var TA_Bot_Analytics
	 */
	private $bot_analytics;

	/**
	 * Rate Limiter instance.
	 *
	 * @var TA_Rate_Limiter
	 */
	private $rate_limiter;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @param TA_Cache_Manager $cache_manager Cache manager instance.
	 */
	public function __construct( $cache_manager ) {
		$this->cache_manager = $cache_manager;
		$this->converter     = new TA_Local_Converter();
		$this->security      = TA_Security::get_instance();
		$this->logger        = TA_Logger::get_instance();
		$this->bot_analytics = TA_Bot_Analytics::get_instance();
		$this->rate_limiter  = new TA_Rate_Limiter();
	}

	/**
	 * Register rewrite rules for .md URLs.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_rewrite_rules() {
		// Match any URL ending in .md (including homepage patterns like /index.md).
		add_rewrite_rule(
			'(.*)\.md$',
			'index.php?ta_markdown=1&ta_path=$matches[1]',
			'top'
		);

		// Register query vars.
		add_rewrite_tag( '%ta_markdown%', '1' );
		add_rewrite_tag( '%ta_path%', '([^&]+)' );
	}

	/**
	 * Handle incoming markdown requests.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function handle_markdown_request() {
		if ( ! get_query_var( 'ta_markdown' ) ) {
			return;
		}

		// Start tracking request time for analytics.
		$request_start_time = microtime( true );

		$path = get_query_var( 'ta_path' );

		// Sanitize the path.
		$path = $this->security->sanitize_text( $path );

		// Check if this is a homepage pattern request
		$homepage_pattern = get_option( 'ta_homepage_md_pattern', 'index.md' );
		if ( $homepage_pattern === 'custom' ) {
			$homepage_pattern = get_option( 'ta_homepage_md_pattern_custom', 'index.md' );
		}
		// Remove .md extension for comparison
		$homepage_slug = str_replace( '.md', '', $homepage_pattern );

		$is_homepage_request = ( $path === $homepage_slug || empty( $path ) );

		// Handle homepage request
		if ( $is_homepage_request ) {
			$post_id = get_option( 'page_on_front' );
			if ( ! $post_id ) {
				// No static page set, fall back to latest post.
				$latest_posts = get_posts( array(
					'numberposts'      => 1,
					'post_status'      => 'publish',
					'suppress_filters' => false,
				) );

				if ( empty( $latest_posts ) ) {
					$this->logger->warning( 'Homepage markdown requested but no page_on_front set and no posts found.' );
					$this->send_error_response( 404, 'No content available for homepage' );
					return;
				}

				$post_id = $latest_posts[0]->ID;
				$this->logger->debug( 'Homepage markdown fallback to latest post.', array(
					'post_id' => $post_id,
					'title'   => $latest_posts[0]->post_title,
				) );
			}
			$original_url = home_url( '/' );
		} else {
			// Build the original URL (without .md).
			$original_url = home_url( '/' . $path );

			// Basic URL validation (no need for strict Worker validation in local mode).
			if ( empty( $original_url ) ) {
				$this->logger->warning( 'Empty URL in markdown request.' );
				$this->send_error_response( 400, 'Invalid URL' );
				return;
			}

			// Check if this URL exists as a post/page.
			$post_id = url_to_postid( $original_url );
			if ( ! $post_id ) {
				// Try with trailing slash.
				$post_id = url_to_postid( trailingslashit( $original_url ) );
			}

			if ( ! $post_id ) {
				$this->logger->debug( 'Post not found for markdown request.', array(
					'url'  => $original_url,
					'path' => $path,
				) );
				$this->send_error_response( 404, 'Post not found' );
				return;
			}
		}

		// Check if post type is enabled.
		$post          = get_post( $post_id );
		$enabled_types = get_option( 'ta_enabled_post_types', array( 'post', 'page' ) );
		if ( ! in_array( $post->post_type, $enabled_types, true ) ) {
			$this->logger->debug( 'Post type not enabled for markdown.', array(
				'post_id'   => $post_id,
				'post_type' => $post->post_type,
			) );

			// Track the failed attempt before sending error.
			$this->track_bot_visit( $original_url, $post, 'ERROR', $request_start_time, 0 );

			$this->send_error_response( 404, 'Post type not enabled' );
			return;
		}

		// Check if bot is blocked.
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		$bot_info   = $this->bot_analytics->detect_bot( $user_agent );

		if ( false !== $bot_info && $this->bot_analytics->is_bot_blocked( $bot_info['type'] ) ) {
			$this->logger->info( 'Bot blocked from accessing content.', array(
				'bot_type' => $bot_info['type'],
				'bot_name' => $bot_info['name'],
				'url'      => $original_url,
			) );

			// Track the blocked attempt.
			$this->track_bot_visit( $original_url, $post, 'BLOCKED', $request_start_time, 0 );

			$this->send_error_response( 403, 'Access forbidden for this bot' );
			return;
		}

		// Check rate limits for known bots.
		if ( false !== $bot_info ) {
			$client_ip   = $this->get_client_ip();
			$rate_status = $this->rate_limiter->check_bot_rate_limit(
				$bot_info['type'],
				$bot_info['priority'],
				$client_ip
			);

			if ( ! $rate_status['allowed'] ) {
				$this->logger->warning( 'Bot rate limit exceeded.', array(
					'bot_type'   => $bot_info['type'],
					'bot_name'   => $bot_info['name'],
					'ip'         => $client_ip,
					'limit_type' => $rate_status['limit_type'],
					'url'        => $original_url,
				) );

				// Track the rate limited attempt.
				$this->track_bot_visit( $original_url, $post, 'RATE_LIMITED', $request_start_time, 0 );

				// Send 429 response with rate limit headers.
				$this->send_rate_limited_response( $rate_status );
				return;
			}

			// Increment rate limit counters.
			$this->rate_limiter->increment_bot_counter( $bot_info['type'], $client_ip );
		}

		// Priority 1: Check pre-generated markdown in post_meta (fastest, permanent).
		$pre_generated = $this->cache_manager->get_pre_generated_markdown( $post_id );
		if ( false !== $pre_generated && $this->cache_manager->has_fresh_pre_generated( $post_id ) ) {
			$this->logger->debug( 'Pre-generated markdown hit.', array(
				'url'     => $original_url,
				'post_id' => $post_id,
			) );

			// Track bot visit.
			$this->track_bot_visit( $original_url, $post, 'PRE_GENERATED', $request_start_time, strlen( $pre_generated ) );

			$this->send_markdown_response( $pre_generated, true, 'PRE_GENERATED' );
			return;
		}

		// Priority 2: Check transient cache (fallback).
		$cache_key = $this->cache_manager->get_cache_key( $original_url );
		$cached    = $this->cache_manager->get( $cache_key );

		if ( false !== $cached ) {
			$this->logger->debug( 'Cache hit for markdown request.', array(
				'url' => $original_url,
			) );

			// Track bot visit.
			$this->track_bot_visit( $original_url, $post, 'HIT', $request_start_time, strlen( $cached ) );

			$this->send_markdown_response( $cached, true, 'HIT' );
			return;
		}

		// Convert locally.
		$start_time = microtime( true );
		$markdown   = $this->fetch_markdown( $post_id );

		if ( false === $markdown ) {
			$this->logger->error( 'Failed to convert content to markdown.', array(
				'post_id' => $post_id,
				'url'     => $original_url,
			) );

			// Track the failed conversion attempt.
			$this->track_bot_visit( $original_url, $post, 'FAILED', $request_start_time, 0 );

			$this->send_error_response( 500, 'Failed to convert content' );
			return;
		}

		// Get cache TTL based on bot priority.
		$cache_ttl = $this->get_cache_ttl_for_request( $user_agent );

		// Cache the result with priority-based TTL.
		$this->cache_manager->set( $cache_key, $markdown, $cache_ttl );

		$conversion_time = ( microtime( true ) - $start_time ) * 1000;
		$this->logger->debug( 'Markdown conversion successful.', array(
			'url'       => $original_url,
			'time'      => round( $conversion_time, 2 ) . 'ms',
			'size'      => strlen( $markdown ),
			'cache_ttl' => $cache_ttl,
		) );

		// Track bot visit.
		$this->track_bot_visit( $original_url, $post, 'MISS', $request_start_time, strlen( $markdown ) );

		$this->send_markdown_response( $markdown, false, 'MISS' );
	}

	/**
	 * Track bot visit for analytics.
	 *
	 * @since 1.4.0
	 * @param string      $url        The URL being accessed.
	 * @param WP_Post     $post       The post object.
	 * @param string      $cache_status Cache status (HIT, MISS, PRE_GENERATED).
	 * @param float       $start_time  Request start time.
	 * @param int         $size        Response size in bytes.
	 * @return void
	 */
	private function track_bot_visit( $url, $post, $cache_status, $start_time, $size ) {
		// Get user agent.
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : 'Unknown';

		// Detect if this is a known bot.
		$bot_info = $this->bot_analytics->detect_bot( $user_agent );

		// If not a known bot, detect generic bots by user agent patterns.
		if ( false === $bot_info ) {
			// Check if this looks like a bot (contains common bot keywords).
			$bot_keywords = array( 'bot', 'crawler', 'spider', 'scraper', 'curl', 'wget', 'python', 'java', 'go-http', 'okhttp' );
			$is_bot = false;

			foreach ( $bot_keywords as $keyword ) {
				if ( stripos( $user_agent, $keyword ) !== false ) {
					$is_bot = true;
					break;
				}
			}

			// Only track if it's a bot-like agent - skip regular browsers.
			if ( ! $is_bot ) {
				return;
			}

			// Extract bot name from user agent.
			$bot_name = $this->extract_bot_name_from_user_agent( $user_agent );

			// Create generic bot info for unknown bots.
			$bot_info = array(
				'type'  => 'Unknown_Bot',
				'name'  => $bot_name,
				'color' => '#6B7280', // Gray color for unknown bots.
			);
		}

		// Get referer.
		$referer = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : null;

		// Calculate response time.
		$response_time = round( ( microtime( true ) - $start_time ) * 1000 ); // milliseconds.

		// Track the visit.
		$this->bot_analytics->track_visit( array(
			'bot_type'       => $bot_info['type'],
			'bot_name'       => $bot_info['name'],
			'user_agent'     => $user_agent,
			'url'            => $url,
			'post_id'        => $post->ID,
			'post_type'      => $post->post_type,
			'post_title'     => $post->post_title,
			'request_method' => 'md_url',
			'cache_status'   => $cache_status,
			'response_time'  => $response_time,
			'response_size'  => $size,
			'referer'        => $referer,
		) );
	}

	/**
	 * Convert post to markdown locally.
	 *
	 * @since 2.0.0
	 * @param int $post_id The post ID to convert.
	 * @return string|false The markdown content or false on failure.
	 */
	private function fetch_markdown( $post_id ) {
		// Check if converter library is available.
		if ( ! TA_Local_Converter::is_library_available() ) {
			$this->logger->error( 'HTML to Markdown library is not installed.' );
			return false;
		}

		// Convert post to markdown.
		$markdown = $this->converter->convert_post( $post_id, array(
			'include_frontmatter'    => true,
			'extract_main_content'   => true,
			'include_title'          => true,
			'include_excerpt'        => true,
			'include_featured_image' => true,
		) );

		if ( is_wp_error( $markdown ) ) {
			$this->logger->error( 'Local conversion failed.', array(
				'post_id' => $post_id,
				'error'   => $markdown->get_error_message(),
			) );
			return false;
		}

		return $markdown;
	}

	/**
	 * Send markdown response.
	 *
	 * @since 1.0.0
	 * @param string      $markdown     The markdown content.
	 * @param bool        $cache_hit    Whether this was a cache hit.
	 * @param string|null $cache_status Optional. Explicit cache status (PRE_GENERATED, HIT, MISS).
	 * @return void
	 */
	private function send_markdown_response( $markdown, $cache_hit, $cache_status = null ) {
		// Determine cache status.
		if ( null === $cache_status ) {
			$cache_status = $cache_hit ? 'HIT' : 'MISS';
		}

		// Sanitize output - markdown is text, but we still escape for safety.
		// Note: We don't use esc_html here because markdown is meant to be rendered.
		// The markdown is generated from known sources (our conversion worker).

		status_header( 200 );
		header( 'Content-Type: text/markdown; charset=utf-8' );
		header( 'Cache-Control: public, max-age=3600' );
		header( 'X-Cache-Status: ' . $cache_status );
		header( 'X-Powered-By: Third Audience ' . TA_VERSION );
		header( 'X-Content-Type-Options: nosniff' );

		// Add rate limit headers for known bots.
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		$bot_info   = $this->bot_analytics->detect_bot( $user_agent );

		if ( false !== $bot_info ) {
			$client_ip   = $this->get_client_ip();
			$rate_status = $this->rate_limiter->check_bot_rate_limit(
				$bot_info['type'],
				$bot_info['priority'],
				$client_ip
			);

			if ( isset( $rate_status['minute_limit'] ) && $rate_status['minute_limit'] > 0 ) {
				header( 'X-RateLimit-Limit: ' . $rate_status['minute_limit'] );
				header( 'X-RateLimit-Remaining: ' . $rate_status['minute_remaining'] );
			}
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markdown content from trusted source.
		echo $markdown;
		exit;
	}

	/**
	 * Send rate limited response.
	 *
	 * @since 2.1.0
	 * @param array $rate_status Rate limit status from check_bot_rate_limit().
	 * @return void
	 */
	private function send_rate_limited_response( $rate_status ) {
		status_header( 429 );
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'Retry-After: ' . $rate_status['retry_after'] );
		header( 'X-RateLimit-Limit: ' . $rate_status['limit'] );
		header( 'X-RateLimit-Remaining: 0' );
		header( 'X-RateLimit-Reset: ' . $rate_status['reset'] );
		header( 'X-Content-Type-Options: nosniff' );

		$limit_type = $rate_status['limit_type'] === 'minute' ? 'per minute' : 'per hour';
		echo 'Third Audience Error: Rate limit exceeded (' . esc_html( $rate_status['limit'] ) . ' requests ' . esc_html( $limit_type ) . '). Please retry after ' . esc_html( $rate_status['retry_after'] ) . ' seconds.';
		exit;
	}

	/**
	 * Send error response.
	 *
	 * @since 1.0.0
	 * @param int    $status_code HTTP status code.
	 * @param string $message     Error message.
	 * @return void
	 */
	private function send_error_response( $status_code, $message ) {
		status_header( $status_code );
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'X-Content-Type-Options: nosniff' );

		echo 'Third Audience Error: ' . esc_html( $message );
		exit;
	}

	/**
	 * Get cache TTL based on bot priority from user agent.
	 *
	 * @since 2.1.0
	 * @param string $user_agent The user agent string.
	 * @return int Cache TTL in seconds.
	 */
	private function get_cache_ttl_for_request( $user_agent ) {
		$bot_info = $this->bot_analytics->detect_bot( $user_agent );

		if ( false !== $bot_info && isset( $bot_info['priority'] ) ) {
			return TA_Bot_Analytics::get_cache_ttl_for_priority( $bot_info['priority'] );
		}

		// Default to medium priority (24 hours) for unknown bots.
		return 24 * HOUR_IN_SECONDS;
	}

	/**
	 * Extract bot name from user agent string.
	 *
	 * @since 2.0.6
	 * @param string $user_agent The user agent string.
	 * @return string The extracted bot name.
	 */
	private function extract_bot_name_from_user_agent( $user_agent ) {
		// Common patterns for bot names:
		// 1. "BotName/version" -> extract "BotName"
		// 2. "BotName version" -> extract "BotName"
		// 3. "curl/7.68.0" -> extract "curl"

		// Pattern 1: Name/Version (most common)
		if ( preg_match( '/^([a-zA-Z0-9._-]+)\/[\d.]+/i', $user_agent, $matches ) ) {
			return ucfirst( strtolower( $matches[1] ) );
		}

		// Pattern 2: Name followed by space and version
		if ( preg_match( '/^([a-zA-Z0-9._-]+)\s+[\d.]+/i', $user_agent, $matches ) ) {
			return ucfirst( strtolower( $matches[1] ) );
		}

		// Pattern 3: Just the first word if it contains "bot", "crawler", "spider"
		if ( preg_match( '/^([a-zA-Z0-9._-]*(?:bot|crawler|spider)[a-zA-Z0-9._-]*)/i', $user_agent, $matches ) ) {
			return ucfirst( strtolower( $matches[1] ) );
		}

		// Pattern 4: Common tools (curl, wget, python-requests, etc.)
		$common_tools = array( 'curl', 'wget', 'python-requests', 'python', 'java', 'go-http', 'okhttp' );
		foreach ( $common_tools as $tool ) {
			if ( stripos( $user_agent, $tool ) !== false ) {
				return ucfirst( $tool );
			}
		}

		// Fallback: Use first word (up to 20 chars)
		if ( preg_match( '/^([a-zA-Z0-9._-]{1,20})/i', $user_agent, $matches ) ) {
			return ucfirst( strtolower( $matches[1] ) );
		}

		return 'Unknown Bot';
	}

	/**
	 * Get client IP address.
	 *
	 * @since 2.1.0
	 * @return string Client IP address.
	 */
	private function get_client_ip() {
		$ip = null;

		if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			// Cloudflare.
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
			$ip = explode( ',', $ip )[0];
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return $ip ?: '0.0.0.0';
	}
}
