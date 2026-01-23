<?php
/**
 * Email Digest - Sends scheduled reports of bot activity.
 *
 * @package ThirdAudience
 * @since   3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Email_Digest
 */
class TA_Email_Digest {

	/**
	 * Singleton instance.
	 *
	 * @var TA_Email_Digest|null
	 */
	private static $instance = null;

	/**
	 * Bot Analytics instance.
	 *
	 * @var TA_Bot_Analytics
	 */
	private $analytics;

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Get singleton instance.
	 *
	 * @return TA_Email_Digest
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->analytics = TA_Bot_Analytics::get_instance();
		$this->logger    = TA_Logger::get_instance();

		// Schedule cron if enabled.
		add_action( 'init', array( $this, 'schedule_digest' ) );
		add_action( 'ta_send_email_digest', array( $this, 'send_digest' ) );

		// Real-time alerts.
		add_action( 'ta_bot_visit_tracked', array( $this, 'maybe_send_alert' ) );
	}

	/**
	 * Schedule the digest cron job.
	 */
	public function schedule_digest() {
		if ( ! get_option( 'ta_email_digest_enabled', false ) ) {
			wp_clear_scheduled_hook( 'ta_send_email_digest' );
			return;
		}

		if ( ! wp_next_scheduled( 'ta_send_email_digest' ) ) {
			$time = get_option( 'ta_email_digest_time', '09:00' );
			$timestamp = strtotime( 'today ' . $time );
			if ( $timestamp < time() ) {
				$timestamp = strtotime( 'tomorrow ' . $time );
			}
			wp_schedule_event( $timestamp, $this->get_frequency(), 'ta_send_email_digest' );
		}
	}

	/**
	 * Get digest frequency.
	 *
	 * @return string WordPress cron schedule name.
	 */
	private function get_frequency() {
		$freq = get_option( 'ta_email_digest_frequency', 'daily' );
		$map = array(
			'daily'  => 'daily',
			'weekly' => 'weekly',
			'hourly' => 'hourly',
		);
		return $map[ $freq ] ?? 'daily';
	}

	/**
	 * Send the email digest.
	 */
	public function send_digest() {
		$recipients = $this->get_recipients();
		if ( empty( $recipients ) ) {
			$this->logger->warning( 'Email digest: No recipients configured.' );
			return;
		}

		$period = $this->get_period_hours();
		$data   = $this->gather_digest_data( $period );

		if ( empty( $data['total_visits'] ) ) {
			$this->logger->debug( 'Email digest: No visits in period, skipping.' );
			return;
		}

		$subject = $this->build_subject( $data );
		$body    = $this->build_html_body( $data );
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		// Attach .md report if enabled.
		$attachments = array();
		if ( get_option( 'ta_email_digest_attach_md', false ) ) {
			$md_file = $this->generate_md_report( $data );
			if ( $md_file ) {
				$attachments[] = $md_file;
			}
		}

		$sent = wp_mail( $recipients, $subject, $body, $headers, $attachments );

		// Clean up temp file.
		foreach ( $attachments as $file ) {
			if ( file_exists( $file ) ) {
				wp_delete_file( $file );
			}
		}

		if ( $sent ) {
			$this->logger->info( 'Email digest sent.', array(
				'recipients' => count( $recipients ),
				'visits'     => $data['total_visits'],
			) );
		} else {
			$this->logger->error( 'Email digest failed to send.' );
		}
	}

	/**
	 * Get configured recipients.
	 *
	 * @return array Email addresses.
	 */
	private function get_recipients() {
		$emails = get_option( 'ta_email_digest_recipients', get_option( 'admin_email' ) );
		if ( is_string( $emails ) ) {
			$emails = array_map( 'trim', explode( ',', $emails ) );
		}
		return array_filter( $emails, 'is_email' );
	}

	/**
	 * Get period in hours based on frequency.
	 *
	 * @return int Hours to look back.
	 */
	private function get_period_hours() {
		$freq = get_option( 'ta_email_digest_frequency', 'daily' );
		$map = array(
			'hourly' => 1,
			'daily'  => 24,
			'weekly' => 168,
		);
		return $map[ $freq ] ?? 24;
	}

	/**
	 * Gather all data for the digest.
	 *
	 * @param int $hours Hours to look back.
	 * @return array Digest data.
	 */
	public function gather_digest_data( $hours = 24 ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ta_bot_analytics';
		$since = gmdate( 'Y-m-d H:i:s', strtotime( "-{$hours} hours" ) );

		// Total visits.
		$total = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE visit_timestamp >= %s",
			$since
		) );

		// By bot type.
		$by_bot = $wpdb->get_results( $wpdb->prepare(
			"SELECT bot_name, bot_type, COUNT(*) as count
			 FROM {$table}
			 WHERE visit_timestamp >= %s
			 GROUP BY bot_type, bot_name
			 ORDER BY count DESC",
			$since
		), ARRAY_A );

		// By content type.
		$by_content = $wpdb->get_results( $wpdb->prepare(
			"SELECT content_type, COUNT(*) as count
			 FROM {$table}
			 WHERE visit_timestamp >= %s
			 GROUP BY content_type",
			$since
		), ARRAY_A );

		// Top pages.
		$top_pages = $wpdb->get_results( $wpdb->prepare(
			"SELECT url, post_title, COUNT(*) as count
			 FROM {$table}
			 WHERE visit_timestamp >= %s
			 GROUP BY url
			 ORDER BY count DESC
			 LIMIT 10",
			$since
		), ARRAY_A );

		// Citations.
		$citations = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table}
			 WHERE visit_timestamp >= %s AND traffic_type = 'citation_click'",
			$since
		) );

		// New bots (first seen in this period).
		$new_bots = $wpdb->get_results( $wpdb->prepare(
			"SELECT DISTINCT bot_name, bot_type FROM {$table}
			 WHERE visit_timestamp >= %s
			 AND bot_type NOT IN (
				 SELECT DISTINCT bot_type FROM {$table} WHERE visit_timestamp < %s
			 )",
			$since,
			$since
		), ARRAY_A );

		// Recent visits detail.
		$recent = $wpdb->get_results( $wpdb->prepare(
			"SELECT bot_name, url, post_title, content_type, visit_timestamp, ip_address
			 FROM {$table}
			 WHERE visit_timestamp >= %s
			 ORDER BY visit_timestamp DESC
			 LIMIT 50",
			$since
		), ARRAY_A );

		return array(
			'period_hours'  => $hours,
			'since'         => $since,
			'total_visits'  => (int) $total,
			'by_bot'        => $by_bot,
			'by_content'    => $by_content,
			'top_pages'     => $top_pages,
			'citations'     => (int) $citations,
			'new_bots'      => $new_bots,
			'recent_visits' => $recent,
		);
	}

	/**
	 * Build email subject.
	 *
	 * @param array $data Digest data.
	 * @return string Subject line.
	 */
	private function build_subject( $data ) {
		$site = get_bloginfo( 'name' );
		$date = wp_date( 'F j, Y' );
		return sprintf(
			'[%s] AI Bot Digest - %d visits on %s',
			$site,
			$data['total_visits'],
			$date
		);
	}

	/**
	 * Build HTML email body.
	 *
	 * @param array $data Digest data.
	 * @return string HTML content.
	 */
	private function build_html_body( $data ) {
		$site = get_bloginfo( 'name' );
		$date = wp_date( 'F j, Y' );
		$options = $this->get_include_options();

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<style>
				body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #1d1d1f; }
				.container { max-width: 600px; margin: 0 auto; padding: 20px; }
				h1 { color: #007aff; font-size: 24px; border-bottom: 2px solid #007aff; padding-bottom: 10px; }
				h2 { color: #1d1d1f; font-size: 18px; margin-top: 30px; }
				.stat-box { background: #f5f5f7; padding: 15px; border-radius: 8px; margin: 10px 0; }
				.stat-value { font-size: 32px; font-weight: 700; color: #007aff; }
				.stat-label { color: #86868b; font-size: 12px; text-transform: uppercase; }
				table { width: 100%; border-collapse: collapse; margin: 15px 0; }
				th, td { padding: 10px; text-align: left; border-bottom: 1px solid #e5e5e5; }
				th { background: #f5f5f7; font-weight: 600; }
				.badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
				.badge-html { background: #e5e5e5; color: #646970; }
				.badge-md { background: #dbeafe; color: #007aff; }
				.footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e5e5; color: #86868b; font-size: 12px; }
			</style>
		</head>
		<body>
			<div class="container">
				<h1>AI Bot Activity Report</h1>
				<p><?php echo esc_html( $site ); ?> — <?php echo esc_html( $date ); ?></p>

				<div class="stat-box">
					<div class="stat-value"><?php echo number_format( $data['total_visits'] ); ?></div>
					<div class="stat-label">Total Bot Visits (Last <?php echo $data['period_hours']; ?> hours)</div>
				</div>

				<?php if ( $options['bot_summary'] && ! empty( $data['by_bot'] ) ) : ?>
				<h2>Visits by Bot</h2>
				<table>
					<tr><th>Bot</th><th>Visits</th></tr>
					<?php foreach ( $data['by_bot'] as $bot ) : ?>
					<tr>
						<td><?php echo esc_html( $bot['bot_name'] ); ?></td>
						<td><strong><?php echo number_format( $bot['count'] ); ?></strong></td>
					</tr>
					<?php endforeach; ?>
				</table>
				<?php endif; ?>

				<?php if ( $options['content_breakdown'] && ! empty( $data['by_content'] ) ) : ?>
				<h2>Content Type Breakdown</h2>
				<table>
					<tr><th>Type</th><th>Visits</th></tr>
					<?php foreach ( $data['by_content'] as $ct ) : ?>
					<tr>
						<td>
							<span class="badge badge-<?php echo 'markdown' === $ct['content_type'] ? 'md' : 'html'; ?>">
								<?php echo esc_html( strtoupper( $ct['content_type'] ?: 'HTML' ) ); ?>
							</span>
						</td>
						<td><strong><?php echo number_format( $ct['count'] ); ?></strong></td>
					</tr>
					<?php endforeach; ?>
				</table>
				<?php endif; ?>

				<?php if ( $options['top_pages'] && ! empty( $data['top_pages'] ) ) : ?>
				<h2>Top Crawled Pages</h2>
				<table>
					<tr><th>Page</th><th>Visits</th></tr>
					<?php foreach ( $data['top_pages'] as $page ) : ?>
					<tr>
						<td><?php echo esc_html( $page['post_title'] ?: $page['url'] ); ?></td>
						<td><strong><?php echo number_format( $page['count'] ); ?></strong></td>
					</tr>
					<?php endforeach; ?>
				</table>
				<?php endif; ?>

				<?php if ( $options['citations'] && $data['citations'] > 0 ) : ?>
				<h2>Citation Clicks</h2>
				<div class="stat-box">
					<div class="stat-value"><?php echo number_format( $data['citations'] ); ?></div>
					<div class="stat-label">Users clicked links from AI platforms</div>
				</div>
				<?php endif; ?>

				<?php if ( $options['new_bots'] && ! empty( $data['new_bots'] ) ) : ?>
				<h2>New Bots Detected</h2>
				<ul>
					<?php foreach ( $data['new_bots'] as $bot ) : ?>
					<li><strong><?php echo esc_html( $bot['bot_name'] ); ?></strong></li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>

				<div class="footer">
					<p>Generated by Third Audience v<?php echo esc_html( TA_VERSION ); ?></p>
					<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-bot-analytics' ) ); ?>">View Full Dashboard</a></p>
				</div>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	/**
	 * Generate markdown report file.
	 *
	 * @param array $data Digest data.
	 * @return string|false File path or false on failure.
	 */
	public function generate_md_report( $data ) {
		$site = get_bloginfo( 'name' );
		$date = wp_date( 'Y-m-d' );

		$md = "# AI Bot Activity Report\n\n";
		$md .= "**Site:** {$site}  \n";
		$md .= "**Date:** {$date}  \n";
		$md .= "**Period:** Last {$data['period_hours']} hours  \n\n";

		$md .= "---\n\n";
		$md .= "## Summary\n\n";
		$md .= "| Metric | Value |\n";
		$md .= "|--------|-------|\n";
		$md .= "| Total Bot Visits | {$data['total_visits']} |\n";
		$md .= "| Citation Clicks | {$data['citations']} |\n";
		$md .= "| New Bots | " . count( $data['new_bots'] ) . " |\n\n";

		$md .= "## Visits by Bot\n\n";
		$md .= "| Bot | Visits |\n";
		$md .= "|-----|--------|\n";
		foreach ( $data['by_bot'] as $bot ) {
			$md .= "| {$bot['bot_name']} | {$bot['count']} |\n";
		}
		$md .= "\n";

		$md .= "## Content Type Breakdown\n\n";
		$md .= "| Type | Visits |\n";
		$md .= "|------|--------|\n";
		foreach ( $data['by_content'] as $ct ) {
			$type = strtoupper( $ct['content_type'] ?: 'HTML' );
			$md .= "| {$type} | {$ct['count']} |\n";
		}
		$md .= "\n";

		$md .= "## Top Crawled Pages\n\n";
		$md .= "| Page | Visits |\n";
		$md .= "|------|--------|\n";
		foreach ( $data['top_pages'] as $page ) {
			$title = $page['post_title'] ?: $page['url'];
			$md .= "| {$title} | {$page['count']} |\n";
		}
		$md .= "\n";

		if ( ! empty( $data['new_bots'] ) ) {
			$md .= "## New Bots Detected\n\n";
			foreach ( $data['new_bots'] as $bot ) {
				$md .= "- {$bot['bot_name']}\n";
			}
			$md .= "\n";
		}

		$md .= "## Recent Visits (Last 50)\n\n";
		$md .= "| Time | Bot | Page | Type |\n";
		$md .= "|------|-----|------|------|\n";
		foreach ( array_slice( $data['recent_visits'], 0, 50 ) as $visit ) {
			$time = wp_date( 'Y-m-d H:i', strtotime( $visit['visit_timestamp'] ) );
			$type = strtoupper( $visit['content_type'] ?: 'HTML' );
			$title = $visit['post_title'] ?: $visit['url'];
			$md .= "| {$time} | {$visit['bot_name']} | {$title} | {$type} |\n";
		}

		$md .= "\n---\n\n";
		$md .= "_Generated by Third Audience v" . TA_VERSION . "_\n";

		// Write to temp file.
		$filename = 'bot-report-' . $date . '.md';
		$filepath = wp_upload_dir()['basedir'] . '/' . $filename;

		if ( file_put_contents( $filepath, $md ) ) {
			return $filepath;
		}

		return false;
	}

	/**
	 * Get include options for digest.
	 *
	 * @return array Options.
	 */
	private function get_include_options() {
		return array(
			'bot_summary'       => get_option( 'ta_digest_include_bots', true ),
			'top_pages'         => get_option( 'ta_digest_include_pages', true ),
			'citations'         => get_option( 'ta_digest_include_citations', true ),
			'new_bots'          => get_option( 'ta_digest_include_new_bots', true ),
			'content_breakdown' => get_option( 'ta_digest_include_content_type', true ),
		);
	}

	/**
	 * Maybe send real-time alert.
	 */
	public function maybe_send_alert() {
		// Check if alerts are enabled.
		if ( ! get_option( 'ta_email_alerts_enabled', false ) ) {
			return;
		}

		// Implementation for real-time alerts (new bot, spike detection).
		// This runs on every tracked visit - keep it lightweight.
	}

	/**
	 * Send test digest email.
	 *
	 * @return bool Success.
	 */
	public function send_test_digest() {
		$data = $this->gather_digest_data( 168 ); // Last 7 days for test.
		$recipients = $this->get_recipients();

		if ( empty( $recipients ) ) {
			return false;
		}

		$subject = '[TEST] ' . $this->build_subject( $data );
		$body    = $this->build_html_body( $data );
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		return wp_mail( $recipients, $subject, $body, $headers );
	}
}
