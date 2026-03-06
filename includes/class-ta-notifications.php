<?php
/**
 * Notifications - Email notification system with SMTP support.
 *
 * Handles email alerts for worker failures, high error rates, cache issues,
 * and daily digests. Supports SMTP configuration via plugin settings.
 *
 * @package ThirdAudience
 * @since   1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Notifications
 *
 * Email notification system for Third Audience plugin.
 *
 * @since 1.1.0
 */
class TA_Notifications {

	/**
	 * Option name for SMTP settings.
	 *
	 * @var string
	 */
	const SMTP_OPTION = 'ta_smtp_settings';

	/**
	 * Option name for notification settings.
	 *
	 * @var string
	 */
	const NOTIFICATION_OPTION = 'ta_notification_settings';

	/**
	 * Option name for last digest sent timestamp.
	 *
	 * @var string
	 */
	const LAST_DIGEST_OPTION = 'ta_last_digest_sent';

	/**
	 * Singleton instance.
	 *
	 * @var TA_Notifications|null
	 */
	private static $instance = null;

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger|null
	 */
	private $logger;

	/**
	 * Security instance.
	 *
	 * @var TA_Security|null
	 */
	private $security;

	/**
	 * Whether SMTP is configured.
	 *
	 * @var bool|null
	 */
	private $smtp_configured = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 1.1.0
	 * @return TA_Notifications
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
		$this->logger   = TA_Logger::get_instance();
		$this->security = TA_Security::get_instance();
	}

	/**
	 * Initialize notification hooks.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function init() {
		// Register hooks for various events.
		add_action( 'ta_critical_error', array( $this, 'on_critical_error' ), 10, 1 );
		add_action( 'ta_high_error_rate', array( $this, 'on_high_error_rate' ), 10, 2 );
		add_action( 'ta_worker_connection_failed', array( $this, 'on_worker_failure' ), 10, 2 );
		add_action( 'ta_cache_issue', array( $this, 'on_cache_issue' ), 10, 2 );

		// Schedule daily digest cron.
		if ( ! wp_next_scheduled( 'ta_daily_digest_cron' ) ) {
			wp_schedule_event( strtotime( 'tomorrow 9:00' ), 'daily', 'ta_daily_digest_cron' );
		}
		add_action( 'ta_daily_digest_cron', array( $this, 'send_daily_digest' ) );

		// Configure SMTP if settings exist.
		add_action( 'phpmailer_init', array( $this, 'configure_smtp' ) );
	}

	/**
	 * Get default SMTP settings.
	 *
	 * @since 1.1.0
	 * @return array Default settings.
	 */
	public function get_default_smtp_settings() {
		return array(
			'enabled'    => false,
			'host'       => '',
			'port'       => 587,
			'encryption' => 'tls',
			'username'   => '',
			'password'   => '', // Stored encrypted.
			'from_email' => get_option( 'admin_email' ),
			'from_name'  => get_bloginfo( 'name' ),
		);
	}

	/**
	 * Get default notification settings.
	 *
	 * @since 1.1.0
	 * @return array Default settings.
	 */
	public function get_default_notification_settings() {
		return array(
			'alert_emails'         => get_option( 'admin_email' ),
			'on_worker_failure'    => true,
			'on_high_error_rate'   => true,
			'on_cache_issues'      => false,
			'daily_digest'         => false,
			'error_rate_threshold' => 10,
		);
	}

	/**
	 * Get SMTP settings.
	 *
	 * @since 1.1.0
	 * @return array SMTP settings.
	 */
	public function get_smtp_settings() {
		$settings = get_option( self::SMTP_OPTION, array() );
		$defaults = $this->get_default_smtp_settings();

		$merged = wp_parse_args( $settings, $defaults );

		// Decrypt password if stored.
		if ( ! empty( $merged['password'] ) ) {
			$decrypted = $this->security->decrypt( $merged['password'] );
			if ( false !== $decrypted ) {
				$merged['password'] = $decrypted;
			}
		}

		return $merged;
	}

	/**
	 * Save SMTP settings.
	 *
	 * @since 1.1.0
	 * @param array $settings The settings to save.
	 * @return bool Whether the settings were saved.
	 */
	public function save_smtp_settings( $settings ) {
		$sanitized = $this->security->sanitize_smtp_settings( $settings );

		// Encrypt password before storing.
		if ( ! empty( $sanitized['password'] ) ) {
			$encrypted = $this->security->encrypt( $sanitized['password'] );
			if ( false !== $encrypted ) {
				$sanitized['password'] = $encrypted;
			}
		}

		$this->smtp_configured = null; // Reset cache.

		return update_option( self::SMTP_OPTION, $sanitized, false );
	}

	/**
	 * Get notification settings.
	 *
	 * @since 1.1.0
	 * @return array Notification settings.
	 */
	public function get_notification_settings() {
		$settings = get_option( self::NOTIFICATION_OPTION, array() );
		return wp_parse_args( $settings, $this->get_default_notification_settings() );
	}

	/**
	 * Save notification settings.
	 *
	 * @since 1.1.0
	 * @param array $settings The settings to save.
	 * @return bool Whether the settings were saved.
	 */
	public function save_notification_settings( $settings ) {
		$sanitized = $this->security->sanitize_notification_settings( $settings );
		return update_option( self::NOTIFICATION_OPTION, $sanitized, false );
	}

	/**
	 * Check if SMTP is configured and enabled.
	 *
	 * @since 1.1.0
	 * @return bool Whether SMTP is configured.
	 */
	public function is_smtp_configured() {
		if ( null !== $this->smtp_configured ) {
			return $this->smtp_configured;
		}

		$settings = $this->get_smtp_settings();

		$this->smtp_configured = ! empty( $settings['enabled'] )
			&& ! empty( $settings['host'] )
			&& ! empty( $settings['port'] );

		return $this->smtp_configured;
	}

	/**
	 * Configure PHPMailer with SMTP settings.
	 *
	 * @since 1.1.0
	 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer The PHPMailer instance.
	 * @return void
	 */
	public function configure_smtp( $phpmailer ) {
		if ( ! $this->is_smtp_configured() ) {
			return;
		}

		$settings = $this->get_smtp_settings();

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$phpmailer->isSMTP();
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$phpmailer->Host = $settings['host'];
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$phpmailer->Port = (int) $settings['port'];

		// Set encryption.
		if ( ! empty( $settings['encryption'] ) ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$phpmailer->SMTPSecure = $settings['encryption'];
		}

		// Set authentication if credentials provided.
		if ( ! empty( $settings['username'] ) && ! empty( $settings['password'] ) ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$phpmailer->SMTPAuth = true;
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$phpmailer->Username = $settings['username'];
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$phpmailer->Password = $settings['password'];
		}

		// Set from address.
		if ( ! empty( $settings['from_email'] ) ) {
			$phpmailer->setFrom(
				$settings['from_email'],
				$settings['from_name'] ?? get_bloginfo( 'name' )
			);
		}
	}

	/**
	 * Get alert email addresses.
	 *
	 * @since 1.1.0
	 * @return array Array of email addresses.
	 */
	public function get_alert_emails() {
		$settings = $this->get_notification_settings();
		$emails   = array_map( 'trim', explode( ',', $settings['alert_emails'] ) );
		return array_filter( $emails, 'is_email' );
	}

	/**
	 * Send an email notification.
	 *
	 * @since 1.1.0
	 * @param string       $subject The email subject.
	 * @param string       $message The email body.
	 * @param string|array $to      Optional. Email recipient(s). Defaults to alert emails.
	 * @param array        $headers Optional. Additional headers.
	 * @return bool Whether the email was sent.
	 */
	public function send_email( $subject, $message, $to = null, $headers = array() ) {
		if ( null === $to ) {
			$to = $this->get_alert_emails();
		}

		if ( empty( $to ) ) {
			$this->logger->warning( 'No recipient emails configured for notification.' );
			return false;
		}

		// Format subject with site name.
		$subject = sprintf( '[%s] %s', get_bloginfo( 'name' ), $subject );

		// Set content type to HTML if not specified.
		if ( ! in_array( 'Content-Type: text/html; charset=UTF-8', $headers, true ) ) {
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
		}

		// Wrap message in basic HTML template.
		$html_message = $this->get_email_template( $subject, $message );

		$result = wp_mail( $to, $subject, $html_message, $headers );

		if ( ! $result ) {
			$this->logger->error( 'Failed to send notification email.', array(
				'subject' => $subject,
				'to'      => $to,
			) );
		} else {
			$this->logger->info( 'Notification email sent.', array(
				'subject' => $subject,
				'to'      => $to,
			) );
		}

		return $result;
	}

	/**
	 * Get email HTML template.
	 *
	 * @since 1.1.0
	 * @param string $subject The email subject.
	 * @param string $content The email content.
	 * @return string HTML email content.
	 */
	private function get_email_template( $subject, $content ) {
		$site_name = get_bloginfo( 'name' );
		$site_url  = home_url();

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?php echo esc_html( $subject ); ?></title>
			<style>
				body {
					font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
					line-height: 1.6;
					color: #333;
					max-width: 600px;
					margin: 0 auto;
					padding: 20px;
				}
				.header {
					background: #0073aa;
					color: #fff;
					padding: 20px;
					text-align: center;
					border-radius: 5px 5px 0 0;
				}
				.content {
					background: #f9f9f9;
					padding: 20px;
					border: 1px solid #ddd;
					border-top: none;
				}
				.footer {
					background: #f1f1f1;
					padding: 15px;
					text-align: center;
					font-size: 12px;
					color: #666;
					border: 1px solid #ddd;
					border-top: none;
					border-radius: 0 0 5px 5px;
				}
				.alert {
					background: #fff3cd;
					border: 1px solid #ffc107;
					padding: 10px;
					border-radius: 3px;
					margin-bottom: 15px;
				}
				.error {
					background: #f8d7da;
					border: 1px solid #dc3545;
				}
				.success {
					background: #d4edda;
					border: 1px solid #28a745;
				}
				table {
					width: 100%;
					border-collapse: collapse;
					margin: 10px 0;
				}
				th, td {
					padding: 8px;
					text-align: left;
					border-bottom: 1px solid #ddd;
				}
				th {
					background: #f1f1f1;
				}
				code {
					background: #f1f1f1;
					padding: 2px 5px;
					border-radius: 3px;
					font-family: monospace;
				}
			</style>
		</head>
		<body>
			<div class="header">
				<h1>Third Audience Alert</h1>
			</div>
			<div class="content">
				<?php echo wp_kses_post( $content ); ?>
			</div>
			<div class="footer">
				<p>This notification was sent from <a href="<?php echo esc_url( $site_url ); ?>"><?php echo esc_html( $site_name ); ?></a></p>
				<p>Third Audience Plugin v<?php echo esc_html( TA_VERSION ); ?></p>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Handle critical error event.
	 *
	 * @since 1.1.0
	 * @param array $log_entry The log entry.
	 * @return void
	 */
	public function on_critical_error( $log_entry ) {
		$settings = $this->get_notification_settings();

		// Critical errors always trigger notification if worker failure is enabled.
		if ( empty( $settings['on_worker_failure'] ) ) {
			return;
		}

		// Rate limit: only one notification per hour for same error type.
		$error_hash = md5( $log_entry['message'] );
		$cache_key  = 'ta_critical_notified_' . $error_hash;

		if ( get_transient( $cache_key ) ) {
			return;
		}

		$subject = __( 'Critical Error Detected', 'third-audience' );
		$message = sprintf(
			'<div class="alert error"><strong>%s</strong></div>
			<p>%s</p>
			<table>
				<tr><th>%s</th><td>%s</td></tr>
				<tr><th>%s</th><td>%s</td></tr>
			</table>
			%s',
			esc_html__( 'A critical error has occurred in the Third Audience plugin.', 'third-audience' ),
			esc_html( $log_entry['message'] ),
			esc_html__( 'Time', 'third-audience' ),
			esc_html( $log_entry['timestamp'] ),
			esc_html__( 'Level', 'third-audience' ),
			esc_html( $log_entry['level_name'] ),
			! empty( $log_entry['context'] )
				? '<p><strong>' . esc_html__( 'Context:', 'third-audience' ) . '</strong></p><pre>' . esc_html( wp_json_encode( $log_entry['context'], JSON_PRETTY_PRINT ) ) . '</pre>'
				: ''
		);

		if ( $this->send_email( $subject, $message ) ) {
			set_transient( $cache_key, true, HOUR_IN_SECONDS );
		}
	}

	/**
	 * Handle high error rate event.
	 *
	 * @since 1.1.0
	 * @param int   $count The error count.
	 * @param array $stats The error statistics.
	 * @return void
	 */
	public function on_high_error_rate( $count, $stats ) {
		$settings = $this->get_notification_settings();

		if ( empty( $settings['on_high_error_rate'] ) ) {
			return;
		}

		// Rate limit: only one notification per hour for high error rate.
		$cache_key = 'ta_high_error_rate_notified';
		if ( get_transient( $cache_key ) ) {
			return;
		}

		$threshold = $settings['error_rate_threshold'] ?? 10;

		$subject = __( 'High Error Rate Alert', 'third-audience' );
		$message = sprintf(
			'<div class="alert error"><strong>%s</strong></div>
			<p>%s</p>
			<table>
				<tr><th>%s</th><td>%d</td></tr>
				<tr><th>%s</th><td>%d</td></tr>
				<tr><th>%s</th><td>%d</td></tr>
				<tr><th>%s</th><td>%s</td></tr>
			</table>',
			esc_html__( 'The error rate has exceeded the configured threshold.', 'third-audience' ),
			/* translators: %1$d: error count, %2$d: threshold */
			sprintf( esc_html__( '%1$d errors in the last hour (threshold: %2$d)', 'third-audience' ), $count, $threshold ),
			esc_html__( 'Errors This Hour', 'third-audience' ),
			$count,
			esc_html__( 'Errors Today', 'third-audience' ),
			$stats['errors_today'],
			esc_html__( 'Total Errors', 'third-audience' ),
			$stats['total_errors'],
			esc_html__( 'Last Error', 'third-audience' ),
			esc_html( $stats['last_error'] )
		);

		if ( $this->send_email( $subject, $message ) ) {
			set_transient( $cache_key, true, HOUR_IN_SECONDS );
		}
	}

	/**
	 * Handle worker connection failure event.
	 *
	 * @since 1.1.0
	 * @param string   $worker_url The worker URL.
	 * @param WP_Error $error      The error object.
	 * @return void
	 */
	public function on_worker_failure( $worker_url, $error ) {
		$settings = $this->get_notification_settings();

		if ( empty( $settings['on_worker_failure'] ) ) {
			return;
		}

		// Rate limit: only one notification per 15 minutes for worker failures.
		$cache_key = 'ta_worker_failure_notified';
		if ( get_transient( $cache_key ) ) {
			return;
		}

		$subject = __( 'Worker Connection Failed', 'third-audience' );
		$message = sprintf(
			'<div class="alert error"><strong>%s</strong></div>
			<table>
				<tr><th>%s</th><td><code>%s</code></td></tr>
				<tr><th>%s</th><td>%s</td></tr>
				<tr><th>%s</th><td>%s</td></tr>
			</table>
			<p>%s</p>',
			esc_html__( 'Failed to connect to the markdown conversion worker.', 'third-audience' ),
			esc_html__( 'Worker URL', 'third-audience' ),
			esc_html( $worker_url ),
			esc_html__( 'Error Code', 'third-audience' ),
			esc_html( $error->get_error_code() ),
			esc_html__( 'Error Message', 'third-audience' ),
			esc_html( $error->get_error_message() ),
			esc_html__( 'Please check the worker URL and ensure the service is running.', 'third-audience' )
		);

		if ( $this->send_email( $subject, $message ) ) {
			set_transient( $cache_key, true, 15 * MINUTE_IN_SECONDS );
		}
	}

	/**
	 * Handle cache issue event.
	 *
	 * @since 1.1.0
	 * @param string $issue      Description of the issue.
	 * @param array  $context    Additional context.
	 * @return void
	 */
	public function on_cache_issue( $issue, $context = array() ) {
		$settings = $this->get_notification_settings();

		if ( empty( $settings['on_cache_issues'] ) ) {
			return;
		}

		// Rate limit: only one notification per hour for cache issues.
		$cache_key = 'ta_cache_issue_notified';
		if ( get_transient( $cache_key ) ) {
			return;
		}

		$subject = __( 'Cache Issue Detected', 'third-audience' );
		$message = sprintf(
			'<div class="alert"><strong>%s</strong></div>
			<p>%s</p>
			%s',
			esc_html__( 'A cache issue has been detected.', 'third-audience' ),
			esc_html( $issue ),
			! empty( $context )
				? '<table>' . implode( '', array_map( function ( $key, $value ) {
					return sprintf( '<tr><th>%s</th><td>%s</td></tr>', esc_html( $key ), esc_html( $value ) );
				}, array_keys( $context ), $context ) ) . '</table>'
				: ''
		);

		if ( $this->send_email( $subject, $message ) ) {
			set_transient( $cache_key, true, HOUR_IN_SECONDS );
		}
	}

	/**
	 * Send daily digest email.
	 *
	 * @since 1.1.0
	 * @return bool Whether the digest was sent.
	 */
	public function send_daily_digest() {
		$settings = $this->get_notification_settings();

		if ( empty( $settings['daily_digest'] ) ) {
			return false;
		}

		// Check if we've already sent today.
		$last_sent = get_option( self::LAST_DIGEST_OPTION, '' );
		$today     = gmdate( 'Y-m-d' );

		if ( $last_sent === $today ) {
			return false;
		}

		// Get statistics.
		$stats         = $this->logger->get_stats();
		$recent_errors = $this->logger->get_recent_errors( 10 );
		$cache_manager = new TA_Cache_Manager();
		$cache_stats   = $cache_manager->get_stats();

		$subject = __( 'Daily Digest', 'third-audience' );

		// Build digest content.
		$message = sprintf(
			'<h2>%s</h2>
			<table>
				<tr><th>%s</th><td>%d</td></tr>
				<tr><th>%s</th><td>%d</td></tr>
				<tr><th>%s</th><td>%s</td></tr>
			</table>

			<h2>%s</h2>
			<table>
				<tr><th>%s</th><td>%d</td></tr>
				<tr><th>%s</th><td>%s</td></tr>
			</table>',
			esc_html__( 'Error Statistics', 'third-audience' ),
			esc_html__( 'Errors Yesterday', 'third-audience' ),
			$stats['errors_today'],
			esc_html__( 'Total Errors', 'third-audience' ),
			$stats['total_errors'],
			esc_html__( 'Last Error', 'third-audience' ),
			$stats['last_error'] ? esc_html( $stats['last_error'] ) : esc_html__( 'None', 'third-audience' ),
			esc_html__( 'Cache Statistics', 'third-audience' ),
			esc_html__( 'Cached Items', 'third-audience' ),
			$cache_stats['count'],
			esc_html__( 'Cache Size', 'third-audience' ),
			esc_html( $cache_stats['size_human'] )
		);

		// Add recent errors if any.
		if ( ! empty( $recent_errors ) ) {
			$message .= sprintf( '<h2>%s</h2><table><tr><th>%s</th><th>%s</th><th>%s</th></tr>',
				esc_html__( 'Recent Errors', 'third-audience' ),
				esc_html__( 'Time', 'third-audience' ),
				esc_html__( 'Level', 'third-audience' ),
				esc_html__( 'Message', 'third-audience' )
			);

			foreach ( $recent_errors as $error ) {
				$message .= sprintf(
					'<tr><td>%s</td><td>%s</td><td>%s</td></tr>',
					esc_html( $error['timestamp'] ),
					esc_html( $error['level'] ),
					esc_html( substr( $error['message'], 0, 100 ) )
				);
			}

			$message .= '</table>';
		}

		$result = $this->send_email( $subject, $message );

		if ( $result ) {
			update_option( self::LAST_DIGEST_OPTION, $today, false );
		}

		return $result;
	}

	/**
	 * Test SMTP configuration by sending a test email.
	 *
	 * @since 1.1.0
	 * @param string $to Optional. Test email recipient.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function test_smtp( $to = null ) {
		if ( null === $to ) {
			$emails = $this->get_alert_emails();
			$to     = ! empty( $emails ) ? $emails[0] : get_option( 'admin_email' );
		}

		if ( ! is_email( $to ) ) {
			return new WP_Error( 'invalid_email', __( 'Invalid email address.', 'third-audience' ) );
		}

		$subject = __( 'SMTP Test Email', 'third-audience' );
		$message = sprintf(
			'<div class="alert success"><strong>%s</strong></div>
			<p>%s</p>
			<table>
				<tr><th>%s</th><td>%s</td></tr>
				<tr><th>%s</th><td>%s</td></tr>
			</table>',
			esc_html__( 'SMTP configuration is working correctly!', 'third-audience' ),
			esc_html__( 'This is a test email from the Third Audience plugin.', 'third-audience' ),
			esc_html__( 'Sent To', 'third-audience' ),
			esc_html( $to ),
			esc_html__( 'Time', 'third-audience' ),
			esc_html( current_time( 'mysql' ) )
		);

		$result = $this->send_email( $subject, $message, $to );

		if ( ! $result ) {
			return new WP_Error( 'send_failed', __( 'Failed to send test email. Please check your SMTP settings.', 'third-audience' ) );
		}

		return true;
	}

	/**
	 * Clear notification cron on deactivation.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'ta_daily_digest_cron' );
	}

	/**
	 * Delete all notification data (for uninstall).
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public static function uninstall() {
		delete_option( self::SMTP_OPTION );
		delete_option( self::NOTIFICATION_OPTION );
		delete_option( self::LAST_DIGEST_OPTION );

		// Clear any notification-related transients.
		delete_transient( 'ta_error_rate_notified' );
		delete_transient( 'ta_worker_failure_notified' );
		delete_transient( 'ta_cache_issue_notified' );

		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ta_critical_notified_%'" );

		// Clear scheduled cron.
		wp_clear_scheduled_hook( 'ta_daily_digest_cron' );
	}
}
