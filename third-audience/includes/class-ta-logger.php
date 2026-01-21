<?php
/**
 * Logger - Centralized logging system with multiple levels and destinations.
 *
 * Provides logging to WordPress debug.log, custom log file, and database
 * for admin display. Follows WordPress VIP coding standards.
 *
 * @package ThirdAudience
 * @since   1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Logger
 *
 * Centralized logging system for Third Audience plugin.
 *
 * @since 1.1.0
 */
class TA_Logger {

	/**
	 * Log level constants.
	 */
	const LEVEL_DEBUG    = 100;
	const LEVEL_INFO     = 200;
	const LEVEL_WARNING  = 300;
	const LEVEL_ERROR    = 400;
	const LEVEL_CRITICAL = 500;

	/**
	 * Log level names.
	 *
	 * @var array
	 */
	private static $level_names = array(
		self::LEVEL_DEBUG    => 'DEBUG',
		self::LEVEL_INFO     => 'INFO',
		self::LEVEL_WARNING  => 'WARNING',
		self::LEVEL_ERROR    => 'ERROR',
		self::LEVEL_CRITICAL => 'CRITICAL',
	);

	/**
	 * Maximum number of errors to store in database.
	 *
	 * @var int
	 */
	const MAX_STORED_ERRORS = 100;

	/**
	 * Option name for stored errors.
	 *
	 * @var string
	 */
	const ERRORS_OPTION = 'ta_recent_errors';

	/**
	 * Option name for error statistics.
	 *
	 * @var string
	 */
	const STATS_OPTION = 'ta_error_stats';

	/**
	 * Custom log file name.
	 *
	 * @var string
	 */
	const LOG_FILE_NAME = 'third-audience.log';

	/**
	 * Singleton instance.
	 *
	 * @var TA_Logger|null
	 */
	private static $instance = null;

	/**
	 * Minimum log level to record.
	 *
	 * @var int
	 */
	private $min_level = self::LEVEL_INFO;

	/**
	 * Whether logging is enabled.
	 *
	 * @var bool
	 */
	private $enabled = true;

	/**
	 * Custom log file path.
	 *
	 * @var string
	 */
	private $log_file_path;

	/**
	 * Get singleton instance.
	 *
	 * @since 1.1.0
	 * @return TA_Logger
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor for singleton.
	 *
	 * @since 1.1.0
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize the logger.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	private function init() {
		// Set minimum level based on debug mode.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$this->min_level = self::LEVEL_DEBUG;
		}

		// Allow filtering the minimum level.
		$this->min_level = apply_filters( 'ta_log_min_level', $this->min_level );

		// Set up log file path.
		$upload_dir = wp_upload_dir();
		$log_dir    = $upload_dir['basedir'] . '/third-audience-logs';

		// Create log directory if it doesn't exist.
		if ( ! file_exists( $log_dir ) ) {
			wp_mkdir_p( $log_dir );

			// Add .htaccess to protect logs.
			$htaccess = $log_dir . '/.htaccess';
			if ( ! file_exists( $htaccess ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
				file_put_contents( $htaccess, 'Deny from all' );
			}

			// Add index.php for additional protection.
			$index = $log_dir . '/index.php';
			if ( ! file_exists( $index ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
				file_put_contents( $index, '<?php // Silence is golden.' );
			}
		}

		$this->log_file_path = $log_dir . '/' . self::LOG_FILE_NAME;
	}

	/**
	 * Set the minimum log level.
	 *
	 * @since 1.1.0
	 * @param int $level The minimum level.
	 * @return void
	 */
	public function set_min_level( $level ) {
		$this->min_level = $level;
	}

	/**
	 * Enable or disable logging.
	 *
	 * @since 1.1.0
	 * @param bool $enabled Whether logging is enabled.
	 * @return void
	 */
	public function set_enabled( $enabled ) {
		$this->enabled = (bool) $enabled;
	}

	/**
	 * Log a message.
	 *
	 * @since 1.1.0
	 * @param string $message The log message.
	 * @param int    $level   The log level.
	 * @param array  $context Additional context data.
	 * @return bool Whether the message was logged.
	 */
	public function log( $message, $level = self::LEVEL_INFO, $context = array() ) {
		if ( ! $this->enabled || $level < $this->min_level ) {
			return false;
		}

		$level_name = isset( self::$level_names[ $level ] ) ? self::$level_names[ $level ] : 'UNKNOWN';
		$timestamp  = current_time( 'mysql' );

		// Format the log entry.
		$log_entry = array(
			'timestamp'  => $timestamp,
			'level'      => $level,
			'level_name' => $level_name,
			'message'    => $message,
			'context'    => $context,
		);

		// Log to WordPress debug.log.
		$this->log_to_debug( $log_entry );

		// Log to custom file.
		$this->log_to_file( $log_entry );

		// Store errors in database for admin display.
		if ( $level >= self::LEVEL_ERROR ) {
			$this->store_error( $log_entry );
			$this->update_stats( $level );

			// Trigger notification hook for critical errors.
			if ( $level >= self::LEVEL_CRITICAL ) {
				do_action( 'ta_critical_error', $log_entry );
			}
		}

		// Allow external handlers.
		do_action( 'ta_log_entry', $log_entry );

		return true;
	}

	/**
	 * Log a debug message.
	 *
	 * @since 1.1.0
	 * @param string $message The message.
	 * @param array  $context Additional context.
	 * @return bool
	 */
	public function debug( $message, $context = array() ) {
		return $this->log( $message, self::LEVEL_DEBUG, $context );
	}

	/**
	 * Log an info message.
	 *
	 * @since 1.1.0
	 * @param string $message The message.
	 * @param array  $context Additional context.
	 * @return bool
	 */
	public function info( $message, $context = array() ) {
		return $this->log( $message, self::LEVEL_INFO, $context );
	}

	/**
	 * Log a warning message.
	 *
	 * @since 1.1.0
	 * @param string $message The message.
	 * @param array  $context Additional context.
	 * @return bool
	 */
	public function warning( $message, $context = array() ) {
		return $this->log( $message, self::LEVEL_WARNING, $context );
	}

	/**
	 * Log an error message.
	 *
	 * @since 1.1.0
	 * @param string $message The message.
	 * @param array  $context Additional context.
	 * @return bool
	 */
	public function error( $message, $context = array() ) {
		return $this->log( $message, self::LEVEL_ERROR, $context );
	}

	/**
	 * Log a critical message.
	 *
	 * @since 1.1.0
	 * @param string $message The message.
	 * @param array  $context Additional context.
	 * @return bool
	 */
	public function critical( $message, $context = array() ) {
		return $this->log( $message, self::LEVEL_CRITICAL, $context );
	}

	/**
	 * Log to WordPress debug.log.
	 *
	 * @since 1.1.0
	 * @param array $entry The log entry.
	 * @return void
	 */
	private function log_to_debug( $entry ) {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		$formatted = sprintf(
			'[Third Audience][%s] %s',
			$entry['level_name'],
			$entry['message']
		);

		if ( ! empty( $entry['context'] ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			$formatted .= ' | Context: ' . print_r( $entry['context'], true );
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( $formatted );
	}

	/**
	 * Log to custom file.
	 *
	 * @since 1.1.0
	 * @param array $entry The log entry.
	 * @return void
	 */
	private function log_to_file( $entry ) {
		$formatted = sprintf(
			"[%s] [%s] %s\n",
			$entry['timestamp'],
			$entry['level_name'],
			$entry['message']
		);

		if ( ! empty( $entry['context'] ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			$formatted .= 'Context: ' . wp_json_encode( $entry['context'] ) . "\n";
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $this->log_file_path, $formatted, FILE_APPEND | LOCK_EX );

		// Rotate log if too large (> 5MB).
		$this->maybe_rotate_log();
	}

	/**
	 * Rotate log file if it exceeds size limit.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	private function maybe_rotate_log() {
		$max_size = 5 * 1024 * 1024; // 5MB.

		if ( file_exists( $this->log_file_path ) && filesize( $this->log_file_path ) > $max_size ) {
			$rotated = $this->log_file_path . '.' . gmdate( 'Y-m-d-H-i-s' ) . '.bak';
			// phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename
			rename( $this->log_file_path, $rotated );

			// Keep only last 5 rotated logs.
			$this->cleanup_old_logs();
		}
	}

	/**
	 * Clean up old rotated log files.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	private function cleanup_old_logs() {
		$log_dir = dirname( $this->log_file_path );
		$pattern = $log_dir . '/' . self::LOG_FILE_NAME . '.*.bak';
		$files   = glob( $pattern );

		if ( is_array( $files ) && count( $files ) > 5 ) {
			// Sort by modification time.
			usort( $files, function ( $a, $b ) {
				return filemtime( $a ) - filemtime( $b );
			} );

			// Remove oldest files.
			$to_delete = array_slice( $files, 0, count( $files ) - 5 );
			foreach ( $to_delete as $file ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
				unlink( $file );
			}
		}
	}

	/**
	 * Store error in database for admin display.
	 *
	 * @since 1.1.0
	 * @param array $entry The log entry.
	 * @return void
	 */
	private function store_error( $entry ) {
		$errors = get_option( self::ERRORS_OPTION, array() );

		// Add new error at the beginning.
		array_unshift( $errors, array(
			'timestamp'  => $entry['timestamp'],
			'level'      => $entry['level_name'],
			'message'    => $entry['message'],
			'context'    => $entry['context'],
		) );

		// Keep only the last N errors.
		$errors = array_slice( $errors, 0, self::MAX_STORED_ERRORS );

		update_option( self::ERRORS_OPTION, $errors, false );
	}

	/**
	 * Update error statistics.
	 *
	 * @since 1.1.0
	 * @param int $level The error level.
	 * @return void
	 */
	private function update_stats( $level ) {
		$stats = get_option( self::STATS_OPTION, array(
			'total_errors'   => 0,
			'errors_today'   => 0,
			'last_error'     => '',
			'last_reset'     => gmdate( 'Y-m-d' ),
			'hourly_errors'  => array(),
		) );

		$today = gmdate( 'Y-m-d' );
		$hour  = (int) gmdate( 'G' );

		// Reset daily counter if new day.
		if ( $stats['last_reset'] !== $today ) {
			$stats['errors_today']  = 0;
			$stats['hourly_errors'] = array();
			$stats['last_reset']    = $today;
		}

		$stats['total_errors']++;
		$stats['errors_today']++;
		$stats['last_error'] = current_time( 'mysql' );

		// Track hourly errors for rate monitoring.
		if ( ! isset( $stats['hourly_errors'][ $hour ] ) ) {
			$stats['hourly_errors'][ $hour ] = 0;
		}
		$stats['hourly_errors'][ $hour ]++;

		update_option( self::STATS_OPTION, $stats, false );

		// Check for high error rate and trigger notification.
		$this->check_error_rate( $stats );
	}

	/**
	 * Check if error rate is too high and trigger notification.
	 *
	 * @since 1.1.0
	 * @param array $stats The error statistics.
	 * @return void
	 */
	private function check_error_rate( $stats ) {
		$hour          = (int) gmdate( 'G' );
		$hourly_errors = isset( $stats['hourly_errors'][ $hour ] ) ? $stats['hourly_errors'][ $hour ] : 0;

		// Trigger notification if more than 10 errors in the last hour.
		$threshold = apply_filters( 'ta_high_error_rate_threshold', 10 );

		if ( $hourly_errors >= $threshold ) {
			// Check if we've already notified this hour.
			$last_notification = get_transient( 'ta_error_rate_notified' );
			if ( ! $last_notification ) {
				do_action( 'ta_high_error_rate', $hourly_errors, $stats );
				set_transient( 'ta_error_rate_notified', true, HOUR_IN_SECONDS );
			}
		}
	}

	/**
	 * Get recent errors for admin display.
	 *
	 * @since 1.1.0
	 * @param int $limit Maximum number of errors to return.
	 * @return array Array of error entries.
	 */
	public function get_recent_errors( $limit = 20 ) {
		$errors = get_option( self::ERRORS_OPTION, array() );
		return array_slice( $errors, 0, $limit );
	}

	/**
	 * Get error statistics.
	 *
	 * @since 1.1.0
	 * @return array Error statistics.
	 */
	public function get_stats() {
		return get_option( self::STATS_OPTION, array(
			'total_errors'  => 0,
			'errors_today'  => 0,
			'last_error'    => '',
			'last_reset'    => gmdate( 'Y-m-d' ),
			'hourly_errors' => array(),
		) );
	}

	/**
	 * Clear all stored errors.
	 *
	 * @since 1.1.0
	 * @return bool Whether the errors were cleared.
	 */
	public function clear_errors() {
		delete_option( self::ERRORS_OPTION );
		return true;
	}

	/**
	 * Reset error statistics.
	 *
	 * @since 1.1.0
	 * @return bool Whether the stats were reset.
	 */
	public function reset_stats() {
		return update_option( self::STATS_OPTION, array(
			'total_errors'  => 0,
			'errors_today'  => 0,
			'last_error'    => '',
			'last_reset'    => gmdate( 'Y-m-d' ),
			'hourly_errors' => array(),
		), false );
	}

	/**
	 * Get the log file path.
	 *
	 * @since 1.1.0
	 * @return string The log file path.
	 */
	public function get_log_file_path() {
		return $this->log_file_path;
	}

	/**
	 * Get the log file contents (last N lines).
	 *
	 * @since 1.1.0
	 * @param int $lines Number of lines to retrieve.
	 * @return string The log content.
	 */
	public function get_log_contents( $lines = 100 ) {
		if ( ! file_exists( $this->log_file_path ) ) {
			return '';
		}

		// Read last N lines efficiently.
		$file = new SplFileObject( $this->log_file_path, 'r' );
		$file->seek( PHP_INT_MAX );
		$total_lines = $file->key();

		$start = max( 0, $total_lines - $lines );
		$file->seek( $start );

		$content = '';
		while ( ! $file->eof() ) {
			$content .= $file->fgets();
		}

		return $content;
	}

	/**
	 * Clear the log file.
	 *
	 * @since 1.1.0
	 * @return bool Whether the file was cleared.
	 */
	public function clear_log_file() {
		if ( file_exists( $this->log_file_path ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			return file_put_contents( $this->log_file_path, '' ) !== false;
		}
		return true;
	}

	/**
	 * Log a WP_Error object.
	 *
	 * @since 1.1.0
	 * @param WP_Error $error   The WP_Error object.
	 * @param int      $level   The log level.
	 * @param array    $context Additional context.
	 * @return bool
	 */
	public function log_wp_error( $error, $level = self::LEVEL_ERROR, $context = array() ) {
		if ( ! is_wp_error( $error ) ) {
			return false;
		}

		$context = array_merge( $context, array(
			'error_code'     => $error->get_error_code(),
			'error_codes'    => $error->get_error_codes(),
			'error_messages' => $error->get_error_messages(),
			'error_data'     => $error->get_error_data(),
		) );

		return $this->log( $error->get_error_message(), $level, $context );
	}

	/**
	 * Log an exception.
	 *
	 * @since 1.1.0
	 * @param Exception|Throwable $exception The exception.
	 * @param array               $context   Additional context.
	 * @return bool
	 */
	public function log_exception( $exception, $context = array() ) {
		$context = array_merge( $context, array(
			'exception_class' => get_class( $exception ),
			'file'            => $exception->getFile(),
			'line'            => $exception->getLine(),
			'trace'           => $exception->getTraceAsString(),
		) );

		return $this->critical( $exception->getMessage(), $context );
	}

	/**
	 * Delete all log data (for uninstall).
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public static function uninstall() {
		delete_option( self::ERRORS_OPTION );
		delete_option( self::STATS_OPTION );

		// Delete log files.
		$upload_dir = wp_upload_dir();
		$log_dir    = $upload_dir['basedir'] . '/third-audience-logs';

		if ( is_dir( $log_dir ) ) {
			$files = glob( $log_dir . '/*' );
			if ( is_array( $files ) ) {
				foreach ( $files as $file ) {
					if ( is_file( $file ) ) {
						// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
						unlink( $file );
					}
				}
			}
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
			rmdir( $log_dir );
		}
	}
}
