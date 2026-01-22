<?php
/**
 * Content Negotiation - Handles Accept: text/markdown requests
 *
 * @package ThirdAudience
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TA_Content_Negotiation
 */
class TA_Content_Negotiation {

    /**
     * Bot Analytics instance.
     *
     * @var TA_Bot_Analytics
     */
    private $bot_analytics;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->bot_analytics = TA_Bot_Analytics::get_instance();
    }

    /**
     * Handle content negotiation based on Accept header
     */
    public function handle_content_negotiation() {
        // Only on singular pages
        if (!is_singular()) {
            return;
        }

        // Check Accept header
        $accept = isset($_SERVER['HTTP_ACCEPT']) ? sanitize_text_field($_SERVER['HTTP_ACCEPT']) : '';

        if (strpos($accept, 'text/markdown') === false) {
            return;
        }

        // Check if post type is enabled
        $enabled_types = get_option('ta_enabled_post_types', array('post', 'page'));
        if (!in_array(get_post_type(), $enabled_types, true)) {
            return;
        }

        // Check if bot is blocked
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
        $bot_info = $this->bot_analytics->detect_bot($user_agent);

        if (false !== $bot_info && $this->bot_analytics->is_bot_blocked($bot_info['type'])) {
            // Track the blocked attempt
            $this->track_content_negotiation_request('BLOCKED');

            // Send 403 Forbidden
            status_header(403);
            header('Content-Type: text/plain; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
            echo 'Third Audience: Access forbidden for this bot';
            exit;
        }

        // Track this content negotiation request (before redirect).
        $this->track_content_negotiation_request();

        // Redirect to .md URL
        $current_url = get_permalink();

        // Bail if permalink is invalid
        if (empty($current_url) || !is_string($current_url)) {
            return;
        }

        $markdown_url = untrailingslashit($current_url) . '.md';

        // Use 303 See Other for content negotiation redirect
        wp_redirect($markdown_url, 303);
        exit;
    }

    /**
     * Track content negotiation request for analytics.
     *
     * @since 1.4.0
     * @param string $cache_status Optional. Cache status (REDIRECT or BLOCKED). Default 'REDIRECT'.
     * @return void
     */
    private function track_content_negotiation_request($cache_status = 'REDIRECT') {
        // Get user agent.
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';

        // Detect if this is a known bot.
        $bot_info = $this->bot_analytics->detect_bot($user_agent);

        // If not a known bot, track as Unknown Bot (track ALL requests).
        if (false === $bot_info) {
            $bot_info = array(
                'type'  => 'Unknown',
                'name'  => 'Unknown Bot',
                'color' => '#6C757D',
            );
        }

        // Get current post.
        $post = get_post();
        if (!$post) {
            return;
        }

        // Get referer.
        $referer = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'])) : null;

        // Track the visit.
        $this->bot_analytics->track_visit(array(
            'bot_type'       => $bot_info['type'],
            'bot_name'       => $bot_info['name'],
            'user_agent'     => $user_agent,
            'url'            => get_permalink($post->ID),
            'post_id'        => $post->ID,
            'post_type'      => $post->post_type,
            'post_title'     => $post->post_title,
            'request_method' => 'accept_header',
            'cache_status'   => $cache_status,
            'response_time'  => null,
            'response_size'  => null,
            'referer'        => $referer,
        ));
    }
}
