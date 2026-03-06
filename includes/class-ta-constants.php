<?php
/**
 * Constants Class - Centralized plugin constants.
 *
 * Provides all magic strings, configuration values, and constant definitions
 * used throughout the Third Audience plugin.
 *
 * @package ThirdAudience
 * @since   1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Constants
 *
 * Centralized constants and configuration values.
 *
 * @since 1.2.0
 */
final class TA_Constants {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.2.0';

	/**
	 * Database version for migrations.
	 *
	 * @var string
	 */
	const DB_VERSION = '1.2.0';

	/**
	 * Minimum PHP version required.
	 *
	 * @var string
	 */
	const MIN_PHP_VERSION = '7.4';

	/**
	 * Minimum WordPress version required.
	 *
	 * @var string
	 */
	const MIN_WP_VERSION = '5.8';

	// =========================================================================
	// Option Keys
	// =========================================================================

	/**
	 * Option key for plugin version.
	 *
	 * @var string
	 */
	const OPTION_VERSION = 'ta_version';

	/**
	 * Option key for database version.
	 *
	 * @var string
	 */
	const OPTION_DB_VERSION = 'ta_db_version';

	/**
	 * Option key for cache TTL.
	 *
	 * @var string
	 */
	const OPTION_CACHE_TTL = 'ta_cache_ttl';

	/**
	 * Option key for enabled post types.
	 *
	 * @var string
	 */
	const OPTION_ENABLED_POST_TYPES = 'ta_enabled_post_types';

	/**
	 * Option key for content negotiation.
	 *
	 * @var string
	 */
	const OPTION_ENABLE_CONTENT_NEGOTIATION = 'ta_enable_content_negotiation';

	/**
	 * Option key for discovery tags.
	 *
	 * @var string
	 */
	const OPTION_ENABLE_DISCOVERY_TAGS = 'ta_enable_discovery_tags';

	/**
	 * Option key for worker URL.
	 *
	 * @var string
	 */
	const OPTION_WORKER_URL = 'ta_worker_url';

	/**
	 * Option key for router URL.
	 *
	 * @var string
	 */
	const OPTION_ROUTER_URL = 'ta_router_url';

	/**
	 * Option key for encrypted API key.
	 *
	 * @var string
	 */
	const OPTION_API_KEY_ENCRYPTED = 'ta_api_key_encrypted';

	/**
	 * Option key for activation timestamp.
	 *
	 * @var string
	 */
	const OPTION_ACTIVATED_AT = 'ta_activated_at';

	// =========================================================================
	// Cache Constants
	// =========================================================================

	/**
	 * Cache key prefix for markdown content.
	 *
	 * @var string
	 */
	const CACHE_PREFIX = 'ta_md_';

	/**
	 * Cache key prefix for object cache.
	 *
	 * @var string
	 */
	const OBJECT_CACHE_PREFIX = 'ta_obj_';

	/**
	 * Cache group for object caching.
	 *
	 * @var string
	 */
	const CACHE_GROUP = 'third_audience';

	/**
	 * Default cache TTL in seconds (24 hours).
	 *
	 * @var int
	 */
	const DEFAULT_CACHE_TTL = 86400;

	/**
	 * Maximum cache items for memory cache.
	 *
	 * @var int
	 */
	const MAX_MEMORY_CACHE_ITEMS = 100;

	/**
	 * Cache warming batch size.
	 *
	 * @var int
	 */
	const CACHE_WARM_BATCH_SIZE = 10;

	// =========================================================================
	// API Constants
	// =========================================================================

	/**
	 * Default worker URL.
	 *
	 * @var string
	 */
	const DEFAULT_WORKER_URL = 'https://ta-worker.rp-2ae.workers.dev';

	/**
	 * Default router URL.
	 *
	 * @var string
	 */
	const DEFAULT_ROUTER_URL = 'https://ta-router.rp-2ae.workers.dev';

	/**
	 * API request timeout in seconds.
	 *
	 * @var int
	 */
	const API_TIMEOUT = 30;

	/**
	 * API health check timeout in seconds.
	 *
	 * @var int
	 */
	const API_HEALTH_TIMEOUT = 10;

	/**
	 * Maximum retry attempts.
	 *
	 * @var int
	 */
	const MAX_RETRY_ATTEMPTS = 3;

	/**
	 * Base delay for exponential backoff (milliseconds).
	 *
	 * @var int
	 */
	const RETRY_BASE_DELAY_MS = 1000;

	/**
	 * Maximum delay for exponential backoff (milliseconds).
	 *
	 * @var int
	 */
	const RETRY_MAX_DELAY_MS = 10000;

	// =========================================================================
	// Rate Limiting Constants
	// =========================================================================

	/**
	 * Rate limit window in seconds.
	 *
	 * @var int
	 */
	const RATE_LIMIT_WINDOW = 60;

	/**
	 * Maximum requests per window.
	 *
	 * @var int
	 */
	const RATE_LIMIT_MAX_REQUESTS = 100;

	/**
	 * Request queue max size.
	 *
	 * @var int
	 */
	const REQUEST_QUEUE_MAX_SIZE = 50;

	/**
	 * Queue processing batch size.
	 *
	 * @var int
	 */
	const QUEUE_BATCH_SIZE = 5;

	// =========================================================================
	// Security Constants
	// =========================================================================

	/**
	 * Required capability for admin operations.
	 *
	 * @var string
	 */
	const ADMIN_CAPABILITY = 'manage_options';

	/**
	 * Nonce action prefix.
	 *
	 * @var string
	 */
	const NONCE_PREFIX = 'ta_';

	/**
	 * Encryption cipher method.
	 *
	 * @var string
	 */
	const CIPHER_METHOD = 'aes-256-cbc';

	// =========================================================================
	// Logger Constants
	// =========================================================================

	/**
	 * Log level - Debug.
	 *
	 * @var int
	 */
	const LOG_DEBUG = 100;

	/**
	 * Log level - Info.
	 *
	 * @var int
	 */
	const LOG_INFO = 200;

	/**
	 * Log level - Warning.
	 *
	 * @var int
	 */
	const LOG_WARNING = 300;

	/**
	 * Log level - Error.
	 *
	 * @var int
	 */
	const LOG_ERROR = 400;

	/**
	 * Log level - Critical.
	 *
	 * @var int
	 */
	const LOG_CRITICAL = 500;

	/**
	 * Maximum stored errors in database.
	 *
	 * @var int
	 */
	const MAX_STORED_ERRORS = 100;

	/**
	 * Log file name.
	 *
	 * @var string
	 */
	const LOG_FILE_NAME = 'third-audience.log';

	/**
	 * Maximum log file size (5MB).
	 *
	 * @var int
	 */
	const MAX_LOG_FILE_SIZE = 5242880;

	// =========================================================================
	// Notification Constants
	// =========================================================================

	/**
	 * High error rate threshold default.
	 *
	 * @var int
	 */
	const DEFAULT_ERROR_RATE_THRESHOLD = 10;

	/**
	 * Worker failure notification cooldown (15 minutes).
	 *
	 * @var int
	 */
	const WORKER_FAILURE_COOLDOWN = 900;

	/**
	 * Critical error notification cooldown (1 hour).
	 *
	 * @var int
	 */
	const CRITICAL_ERROR_COOLDOWN = 3600;

	// =========================================================================
	// HTTP Headers
	// =========================================================================

	/**
	 * Content type for markdown.
	 *
	 * @var string
	 */
	const CONTENT_TYPE_MARKDOWN = 'text/markdown; charset=utf-8';

	/**
	 * Content type for JSON.
	 *
	 * @var string
	 */
	const CONTENT_TYPE_JSON = 'application/json';

	/**
	 * Accept header for markdown.
	 *
	 * @var string
	 */
	const ACCEPT_MARKDOWN = 'text/markdown';

	// =========================================================================
	// REST API Constants
	// =========================================================================

	/**
	 * REST API namespace.
	 *
	 * @var string
	 */
	const REST_NAMESPACE = 'third-audience/v1';

	/**
	 * Health check endpoint.
	 *
	 * @var string
	 */
	const REST_HEALTH_ENDPOINT = '/health';

	/**
	 * Diagnostics endpoint.
	 *
	 * @var string
	 */
	const REST_DIAGNOSTICS_ENDPOINT = '/diagnostics';

	// =========================================================================
	// Cron Constants
	// =========================================================================

	/**
	 * Cache warm cron hook.
	 *
	 * @var string
	 */
	const CRON_CACHE_WARM = 'ta_cache_warm_cron';

	/**
	 * Queue process cron hook.
	 *
	 * @var string
	 */
	const CRON_QUEUE_PROCESS = 'ta_queue_process_cron';

	/**
	 * Daily digest cron hook.
	 *
	 * @var string
	 */
	const CRON_DAILY_DIGEST = 'ta_daily_digest_cron';

	// =========================================================================
	// AI Bot User Agents
	// =========================================================================

	/**
	 * Known AI crawler user agents.
	 *
	 * @var array
	 */
	const AI_BOT_USER_AGENTS = array(
		'claudebot',
		'claude-web',
		'gptbot',
		'chatgpt-user',
		'perplexitybot',
		'cohere-ai',
		'anthropic-ai',
		'google-extended',
		'bingbot',
		'facebookbot',
	);

	// =========================================================================
	// Default Values
	// =========================================================================

	/**
	 * Default enabled post types.
	 *
	 * @var array
	 */
	const DEFAULT_POST_TYPES = array( 'post', 'page' );

	/**
	 * Blocked hosts for SSRF prevention.
	 *
	 * @var array
	 */
	const BLOCKED_HOSTS = array(
		'localhost',
		'127.0.0.1',
		'0.0.0.0',
		'::1',
		'[::1]',
	);

	/**
	 * Private constructor to prevent instantiation.
	 */
	private function __construct() {}
}
