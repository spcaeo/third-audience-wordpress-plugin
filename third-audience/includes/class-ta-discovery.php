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
        // Only on singular pages
        if (!is_singular()) {
            return;
        }

        // Check if post type is enabled
        $enabled_types = get_option('ta_enabled_post_types', array('post', 'page'));
        if (!in_array(get_post_type(), $enabled_types, true)) {
            return;
        }

        // Get markdown URL
        $current_url = get_permalink();
        $markdown_url = untrailingslashit($current_url) . '.md';

        // Output the link tag
        printf(
            '<link rel="alternate" type="text/markdown" href="%s" title="Markdown version" />' . "\n",
            esc_url($markdown_url)
        );
    }
}
