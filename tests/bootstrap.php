<?php
/**
 * PHPUnit Bootstrap File
 *
 * @package ThirdAudience
 */

// Define ABSPATH for WordPress compatibility.
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

// Load Composer autoloader.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Define WordPress constants
if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}

if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}

if ( ! defined( 'WEEK_IN_SECONDS' ) ) {
	define( 'WEEK_IN_SECONDS', 604800 );
}

if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes' );
}

// Initialize global arrays for mocks.
global $mock_options, $mock_transients;
$mock_options    = array();
$mock_transients = array();

// Mock WordPress functions
if ( ! function_exists( 'get_option' ) ) {
	function get_option( $option, $default = false ) {
		global $mock_options;
		return $mock_options[ $option ] ?? $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( $option, $value ) {
		global $mock_options;
		$mock_options[ $option ] = $value;
		return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	function delete_option( $option ) {
		global $mock_options;
		unset( $mock_options[ $option ] );
		return true;
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $str ) {
		return strip_tags( $str );
	}
}

if ( ! function_exists( 'sanitize_title' ) ) {
	function sanitize_title( $str ) {
		return strtolower( str_replace( ' ', '-', $str ) );
	}
}

if ( ! function_exists( 'current_time' ) ) {
	function current_time( $type ) {
		return ( 'mysql' === $type ) ? date( 'Y-m-d H:i:s' ) : time();
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $value ) {
		return is_array( $value ) ? array_map( 'wp_unslash', $value ) : stripslashes( $value );
	}
}

if ( ! function_exists( 'esc_sql' ) ) {
	function esc_sql( $data ) {
		if ( ! is_array( $data ) ) {
			return addslashes( $data );
		}
		return array_map( 'esc_sql', $data );
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	function esc_url_raw( $url ) {
		return $url;
	}
}

if ( ! function_exists( 'absint' ) ) {
	function absint( $maybeint ) {
		return abs( (int) $maybeint );
	}
}

if ( ! function_exists( 'get_transient' ) ) {
	function get_transient( $transient ) {
		global $mock_transients;
		return $mock_transients[ $transient ] ?? false;
	}
}

if ( ! function_exists( 'set_transient' ) ) {
	function set_transient( $transient, $value, $expiration = 0 ) {
		global $mock_transients;
		$mock_transients[ $transient ] = $value;
		return true;
	}
}

if ( ! function_exists( 'wp_remote_get' ) ) {
	function wp_remote_get( $url, $args = array() ) {
		global $_mock_remote_responses;
		if ( isset( $_mock_remote_responses[ $url ] ) ) {
			return $_mock_remote_responses[ $url ];
		}
		return new WP_Error( 'http_request_failed', 'Request failed' );
	}
}

if ( ! function_exists( 'wp_remote_retrieve_body' ) ) {
	function wp_remote_retrieve_body( $response ) {
		return $response['body'] ?? '';
	}
}

if ( ! function_exists( 'wp_remote_retrieve_response_code' ) ) {
	function wp_remote_retrieve_response_code( $response ) {
		if ( is_array( $response ) && isset( $response['response']['code'] ) ) {
			return $response['response']['code'];
		}
		return 0;
	}
}

if ( ! function_exists( 'wp_schedule_event' ) ) {
	function wp_schedule_event( $timestamp, $recurrence, $hook, $args = array() ) {
		global $_mock_scheduled_events;
		$_mock_scheduled_events[ $hook ] = array(
			'timestamp'   => $timestamp,
			'recurrence'  => $recurrence,
			'args'        => $args,
		);
		return true;
	}
}

if ( ! function_exists( 'wp_next_scheduled' ) ) {
	function wp_next_scheduled( $hook, $args = array() ) {
		global $_mock_scheduled_events;
		return isset( $_mock_scheduled_events[ $hook ] ) ? $_mock_scheduled_events[ $hook ]['timestamp'] : false;
	}
}

if ( ! function_exists( 'wp_clear_scheduled_hook' ) ) {
	function wp_clear_scheduled_hook( $hook, $args = array() ) {
		global $_mock_scheduled_events;
		unset( $_mock_scheduled_events[ $hook ] );
		return true;
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ) {
		return $thing instanceof WP_Error;
	}
}

if ( ! function_exists( 'size_format' ) ) {
	function size_format( $bytes, $decimals = 0 ) {
		return number_format( $bytes / 1024, $decimals ) . ' KB';
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data, $options = 0, $depth = 512 ) {
		return json_encode( $data, $options, $depth );
	}
}

if ( ! function_exists( 'get_queried_object_id' ) ) {
	function get_queried_object_id() {
		return 1;
	}
}

if ( ! function_exists( 'get_post' ) ) {
	function get_post( $post_id = null ) {
		if ( ! $post_id ) {
			return null;
		}
		return (object) array(
			'ID'         => $post_id,
			'post_type'  => 'post',
			'post_title' => 'Test Post',
		);
	}
}

if ( ! function_exists( 'is_admin' ) ) {
	function is_admin() {
		return false;
	}
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		return true;
	}
}

// Define constants for wpdb.
if ( ! defined( 'OBJECT' ) ) {
	define( 'OBJECT', 'OBJECT' );
}

if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}

// Mock wpdb class.
class wpdb {
	public $prefix = 'wp_';
	public $last_error = '';
	public $insert_id = 0;
	private $data = array();
	private $next_id = 1;
	private $queries = array();
	private $mock_results = array();

	public function prepare( $query, ...$args ) {
		if ( empty( $args ) ) {
			return $query;
		}
		return vsprintf( str_replace( array( '%s', '%d', '%f' ), array( "'%s'", '%d', '%f' ), $query ), $args );
	}

	public function insert( $table, $data, $format = null ) {
		$this->insert_id = $this->next_id++;
		$data['id'] = $this->insert_id;
		if ( ! isset( $this->data[ $table ] ) ) {
			$this->data[ $table ] = array();
		}
		$this->data[ $table ][ $this->insert_id ] = $data;
		return true;
	}

	public function get_var( $query = null, $x = 0, $y = 0 ) {
		$this->queries[] = $query;

		// Check mock results first
		if ( isset( $this->mock_results[ $query ] ) ) {
			return $this->mock_results[ $query ];
		}

		// Mock COUNT queries.
		if ( strpos( $query, 'COUNT(*)' ) !== false ) {
			if ( strpos( $query, 'ta_bot_patterns' ) !== false ) {
				return 0; // Default to no duplicates
			}
			return 5;
		}
		return '1';
	}

	public function get_results( $query = null, $output = OBJECT ) {
		$this->queries[] = $query;

		// Check mock results first
		if ( array_key_exists( $query, $this->mock_results ) ) {
			return $this->mock_results[ $query ];
		}

		if ( strpos( $query, 'SHOW COLUMNS' ) !== false ) {
			return array(
				(object) array( 'Field' => 'id' ),
				(object) array( 'Field' => 'bot_type' ),
				(object) array( 'Field' => 'detection_method' ),
				(object) array( 'Field' => 'confidence_score' ),
			);
		}
		return array();
	}

	public function get_row( $query = null, $output = OBJECT, $y = 0 ) {
		// Check mock results first
		if ( array_key_exists( $query, $this->mock_results ) ) {
			return $this->mock_results[ $query ];
		}

		if ( $output === ARRAY_A ) {
			return array(
				'id'               => 1,
				'bot_type'         => 'GPTBot',
				'detection_method' => 'database_pattern',
				'confidence_score' => '0.95',
			);
		}
		return (object) array(
			'id'               => 1,
			'bot_type'         => 'GPTBot',
			'detection_method' => 'database_pattern',
			'confidence_score' => '0.95',
		);
	}

	public function query( $query ) {
		$this->queries[] = $query;
		return true;
	}

	public function esc_like( $text ) {
		return addslashes( $text );
	}

	public function get_charset_collate() {
		return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
	}

	public function update( $table, $data, $where, $format = null, $where_format = null ) {
		return 1;
	}

	public function set_mock_result( $query, $result ) {
		$this->mock_results[ $query ] = $result;
	}

	public function get_queries() {
		return $this->queries;
	}

	public function clear_queries() {
		$this->queries = array();
	}
}

// Create global wpdb instance.
global $wpdb;
$wpdb = new wpdb();

// Load plugin includes.
require_once dirname( __DIR__ ) . '/includes/class-ta-bot-detection-result.php';

// Mock maybe_serialize if not already defined
if ( ! function_exists( 'maybe_serialize' ) ) {
	function maybe_serialize( $data ) {
		if ( is_array( $data ) || is_object( $data ) ) {
			return serialize( $data );
		}
		return $data;
	}
}

// Load bot detection classes
require_once dirname( __DIR__ ) . '/includes/interface-ta-bot-detector.php';
require_once dirname( __DIR__ ) . '/includes/detectors/class-ta-known-pattern-detector.php';
require_once dirname( __DIR__ ) . '/includes/detectors/class-ta-heuristic-detector.php';
require_once dirname( __DIR__ ) . '/includes/class-ta-bot-detection-pipeline.php';

// Mock TA_Logger class for testing
if ( ! class_exists( 'TA_Logger' ) ) {
	class TA_Logger {
		private static $instance = null;
		public static $logs = array();

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function info( $message, $context = array() ) {
			self::$logs[] = array( 'level' => 'info', 'message' => $message, 'context' => $context );
		}

		public function error( $message, $context = array() ) {
			self::$logs[] = array( 'level' => 'error', 'message' => $message, 'context' => $context );
		}

		public function warning( $message, $context = array() ) {
			self::$logs[] = array( 'level' => 'warning', 'message' => $message, 'context' => $context );
		}

		public function debug( $message, $context = array() ) {
			self::$logs[] = array( 'level' => 'debug', 'message' => $message, 'context' => $context );
		}

		public static function clear_logs() {
			self::$logs = array();
		}
	}
}

// Load auto-learner class
require_once dirname( __DIR__ ) . '/includes/class-ta-bot-auto-learner.php';

// Mock TA_Webhooks class.
if ( ! class_exists( 'TA_Webhooks' ) ) {
	class TA_Webhooks {
		private static $instance = null;

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function fire_bot_detected( $bot_info ) {
			return true;
		}
	}
}

// Mock TA_AI_Citation_Tracker class.
if ( ! class_exists( 'TA_AI_Citation_Tracker' ) ) {
	class TA_AI_Citation_Tracker {
		public static function detect_citation_traffic() {
			return false;
		}
	}
}

// Load bot analytics class.
require_once dirname( __DIR__ ) . '/includes/class-ta-bot-analytics.php';

// Mock WP_Error class
if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		private $errors = array();
		private $error_data = array();

		public function __construct( $code = '', $message = '', $data = '' ) {
			if ( ! empty( $code ) ) {
				$this->errors[ $code ][] = $message;
				if ( ! empty( $data ) ) {
					$this->error_data[ $code ] = $data;
				}
			}
		}

		public function get_error_message( $code = '' ) {
			if ( empty( $code ) ) {
				$code = $this->get_error_code();
			}
			return $this->errors[ $code ][0] ?? '';
		}

		public function get_error_code() {
			$codes = array_keys( $this->errors );
			return $codes[0] ?? '';
		}
	}
}

// Load External Bot DB Sync class
require_once dirname( __DIR__ ) . '/includes/class-ta-external-bot-db-sync.php';

// Initialize mock globals
global $_mock_scheduled_events, $_mock_remote_responses, $_mock_options;
$_mock_scheduled_events = array();
$_mock_remote_responses = array();
$_mock_options = array();
