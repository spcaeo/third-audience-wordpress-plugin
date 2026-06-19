<?php
/**
 * Discovery - Adds <link rel="alternate"> tags for markdown discovery
 *
 * @package ThirdAudience
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TA_Discovery
 */
class TA_Discovery {

    /**
     * Add markdown discovery link to <head>
     */
    public function add_markdown_discovery_link() {
        // Show on singular pages or homepage
        if (!is_singular() && !is_front_page()) {
            return;
        }

        // For homepage (static page OR blog page)
        if (is_front_page()) {
            $pattern = get_option('ta_homepage_md_pattern', 'index.md');
            // Handle custom pattern
            if ($pattern === 'custom') {
                $pattern = get_option('ta_homepage_md_pattern_custom', 'index.md');
            }
            // Ensure pattern ends with .md
            if (substr($pattern, -3) !== '.md') {
                $pattern .= '.md';
            }
            $markdown_url = trailingslashit(home_url()) . $pattern;
        }
        // For singular posts/pages
        else {
            // Check if post type is enabled
            $enabled_types = get_option('ta_enabled_post_types', array('post', 'page'));
            if (!in_array(get_post_type(), $enabled_types, true)) {
                return;
            }

            // Get current URL and generate markdown URL
            $current_url = get_permalink();

            // Bail early if permalink is invalid
            if (empty($current_url) || !is_string($current_url)) {
                return;
            }

            // Parse URL to handle edge cases
            $parsed = wp_parse_url($current_url);

            // Bail if URL parsing failed
            if (false === $parsed || !isset($parsed['scheme']) || !isset($parsed['host'])) {
                return;
            }

            $path = isset($parsed['path']) ? $parsed['path'] : '/';

            // Remove trailing slash and add .md
            $path = untrailingslashit($path);

            // Reconstruct URL without query params or fragments
            $markdown_url = $parsed['scheme'] . '://' . $parsed['host'];
            if (isset($parsed['port'])) {
                $markdown_url .= ':' . $parsed['port'];
            }
            $markdown_url .= $path . '.md';
        }

        // Derive the .txt URL (same content, plain-text type) from the .md URL.
        // Some AI crawlers look for .txt instead of .md — advertise both.
        $text_url = preg_replace('/\.md$/', '.txt', $markdown_url);

        // Output the markdown alternate link tag.
        printf(
            '<link rel="alternate" type="text/markdown" href="%s" title="Markdown version" />' . "\n",
            esc_url($markdown_url)
        );

        // Output the text alternate link tag.
        printf(
            '<link rel="alternate" type="text/plain" href="%s" title="Text version" />' . "\n",
            esc_url($text_url)
        );

        // Phase 5: advertise the OKF bundle index so agents can discover it.
        if (get_option('ta_enable_okf', true)) {
            printf(
                '<link rel="alternate" type="text/markdown" href="%s" title="OKF bundle" />' . "\n",
                esc_url(home_url('/okf/index.md'))
            );
        }
    }
}
