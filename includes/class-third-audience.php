<?php
/**
 * Main Third Audience Plugin Class
 *
 * @package ThirdAudience
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Third_Audience
 */
class Third_Audience {

    /**
     * URL Router instance
     *
     * @var TA_URL_Router
     */
    private $url_router;

    /**
     * Content Negotiation instance
     *
     * @var TA_Content_Negotiation
     */
    private $content_negotiation;

    /**
     * Discovery instance
     *
     * @var TA_Discovery
     */
    private $discovery;

    /**
     * Cache Manager instance
     *
     * @var TA_Cache_Manager
     */
    private $cache_manager;

    /**
     * Initialize the plugin
     */
    public function init() {
        // Initialize components
        $this->cache_manager = new TA_Cache_Manager();
        $this->url_router = new TA_URL_Router($this->cache_manager);
        $this->content_negotiation = new TA_Content_Negotiation();
        $this->discovery = new TA_Discovery();

        // Initialize Email Digest (schedules cron).
        TA_Email_Digest::get_instance();

        // Register hooks
        $this->register_hooks();

        // Admin hooks
        if (is_admin()) {
            $admin = new TA_Admin();
            $admin->init();
        }
    }

    /**
     * Register WordPress hooks
     */
    private function register_hooks() {
        // URL Routing - intercept .md requests
        add_action('init', array($this->url_router, 'register_rewrite_rules'));
        add_action('template_redirect', array($this->url_router, 'handle_markdown_request'), 1);

        // Content Negotiation
        if (get_option('ta_enable_content_negotiation', true)) {
            add_action('template_redirect', array($this->content_negotiation, 'handle_content_negotiation'), 5);
        }

        // Discovery Tags
        if (get_option('ta_enable_discovery_tags', true)) {
            add_action('wp_head', array($this->discovery, 'add_markdown_discovery_link'));
        }

        // Cache Invalidation
        add_action('save_post', array($this->cache_manager, 'invalidate_post_cache'));
        add_action('delete_post', array($this->cache_manager, 'invalidate_post_cache'));
        add_action('edit_post', array($this->cache_manager, 'invalidate_post_cache'));

        // Pre-generate markdown on post save (runs after cache invalidation)
        if (get_option('ta_enable_pre_generation', true)) {
            add_action('save_post', array($this->cache_manager, 'pre_generate_markdown'), 20, 2);
        }

        // Register query vars
        add_filter('query_vars', array($this, 'add_query_vars'));
    }

    /**
     * Add custom query vars
     *
     * @param array $vars Existing query vars.
     * @return array Modified query vars.
     */
    public function add_query_vars($vars) {
        $vars[] = 'ta_markdown';
        $vars[] = 'ta_path';
        return $vars;
    }
}
