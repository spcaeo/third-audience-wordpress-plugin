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

        // For homepage
        if (is_front_page() && is_home()) {
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

            // Parse URL to handle edge cases
            $parsed = wp_parse_url($current_url);
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

        // Output the link tag
        printf(
            '<link rel="alternate" type="text/markdown" href="%s" title="Markdown version" />' . "\n",
            esc_url($markdown_url)
        );
    }
}
