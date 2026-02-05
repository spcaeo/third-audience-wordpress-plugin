<?php
/**
 * Email Digest Settings Page
 *
 * @package ThirdAudience
 * @since   3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Ensure is_plugin_active() is available.
if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Handle form submission.
if ( isset( $_POST['ta_save_digest_settings'] ) && check_admin_referer( 'ta_digest_settings' ) ) {
	update_option( 'ta_email_digest_enabled', isset( $_POST['ta_digest_enabled'] ) );
	update_option( 'ta_email_digest_frequency', sanitize_text_field( wp_unslash( $_POST['ta_digest_frequency'] ?? 'daily' ) ) );
	update_option( 'ta_email_digest_time', sanitize_text_field( wp_unslash( $_POST['ta_digest_time'] ?? '09:00' ) ) );
	update_option( 'ta_email_digest_recipients', sanitize_text_field( wp_unslash( $_POST['ta_digest_recipients'] ?? '' ) ) );
	update_option( 'ta_email_digest_attach_md', isset( $_POST['ta_digest_attach_md'] ) );
	update_option( 'ta_digest_include_bots', isset( $_POST['ta_include_bots'] ) );
	update_option( 'ta_digest_include_pages', isset( $_POST['ta_include_pages'] ) );
	update_option( 'ta_digest_include_citations', isset( $_POST['ta_include_citations'] ) );
	update_option( 'ta_digest_include_new_bots', isset( $_POST['ta_include_new_bots'] ) );
	update_option( 'ta_digest_include_content_type', isset( $_POST['ta_include_content_type'] ) );
	update_option( 'ta_email_alerts_enabled', isset( $_POST['ta_alerts_enabled'] ) );

	// Reschedule cron.
	wp_clear_scheduled_hook( 'ta_send_email_digest' );
	TA_Email_Digest::get_instance()->schedule_digest();

	echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved.', 'third-audience' ) . '</p></div>';
}

// Get current values.
$enabled     = get_option( 'ta_email_digest_enabled', false );
$frequency   = get_option( 'ta_email_digest_frequency', 'daily' );
$time        = get_option( 'ta_email_digest_time', '09:00' );
$recipients  = get_option( 'ta_email_digest_recipients', get_option( 'admin_email' ) );
$attach_md   = get_option( 'ta_email_digest_attach_md', false );
$inc_bots    = get_option( 'ta_digest_include_bots', true );
$inc_pages   = get_option( 'ta_digest_include_pages', true );
$inc_cites   = get_option( 'ta_digest_include_citations', true );
$inc_new     = get_option( 'ta_digest_include_new_bots', true );
$inc_content = get_option( 'ta_digest_include_content_type', true );
$alerts      = get_option( 'ta_email_alerts_enabled', false );

// Check email configuration status.
$wp_mail_smtp_installed = class_exists( 'WPMailSMTP\Core' ) || is_plugin_active( 'wp-mail-smtp/wp_mail_smtp.php' );
$wp_mail_smtp_configured = get_option( 'wp_mail_smtp', array() );
$smtp_host = isset( $wp_mail_smtp_configured['smtp']['host'] ) ? $wp_mail_smtp_configured['smtp']['host'] : '';
$is_smtp_configured = ! empty( $smtp_host );

// Check for other popular SMTP plugins.
$other_smtp_plugins = array(
	'easy-wp-smtp/easy-wp-smtp.php' => 'Easy WP SMTP',
	'post-smtp/postman-smtp.php' => 'Post SMTP',
	'smtp-mailer/main.php' => 'SMTP Mailer',
	'fluent-smtp/fluent-smtp.php' => 'FluentSMTP',
);
$active_smtp_plugin = '';
foreach ( $other_smtp_plugins as $plugin_path => $plugin_name ) {
	if ( is_plugin_active( $plugin_path ) ) {
		$active_smtp_plugin = $plugin_name;
		$is_smtp_configured = true;
		break;
	}
}
if ( $wp_mail_smtp_installed && $is_smtp_configured ) {
	$active_smtp_plugin = 'WP Mail SMTP';
}
?>

<div class="wrap ta-email-digest-settings">
	<h1>
		<span class="dashicons dashicons-email-alt"></span>
		<?php esc_html_e( 'Email Digest Settings', 'third-audience' ); ?>
	</h1>
	<p class="description"><?php esc_html_e( 'Configure automated email reports of AI bot activity.', 'third-audience' ); ?></p>

	<!-- Email Configuration Status -->
	<div class="ta-email-status-box <?php echo $is_smtp_configured ? 'status-ok' : 'status-warning'; ?>">
		<div class="ta-status-icon">
			<span class="dashicons <?php echo $is_smtp_configured ? 'dashicons-yes-alt' : 'dashicons-warning'; ?>"></span>
		</div>
		<div class="ta-status-content">
			<?php if ( $is_smtp_configured ) : ?>
				<strong><?php esc_html_e( 'Email is configured', 'third-audience' ); ?></strong>
				<p>
					<?php
					printf(
						/* translators: %s: SMTP plugin name */
						esc_html__( 'Using: %s', 'third-audience' ),
						'<strong>' . esc_html( $active_smtp_plugin ) . '</strong>'
					);
					?>
					<?php if ( $wp_mail_smtp_installed ) : ?>
						&mdash; <a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-mail-smtp' ) ); ?>"><?php esc_html_e( 'View Settings', 'third-audience' ); ?></a>
					<?php endif; ?>
				</p>
			<?php else : ?>
				<strong><?php esc_html_e( 'Email not configured', 'third-audience' ); ?></strong>
				<p><?php esc_html_e( 'WordPress cannot send emails reliably without SMTP configuration. Digest emails may not be delivered.', 'third-audience' ); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( ! $is_smtp_configured ) : ?>
	<!-- SMTP Setup Instructions -->
	<div class="ta-settings-section ta-setup-guide">
		<h2>
			<span class="dashicons dashicons-admin-tools"></span>
			<?php esc_html_e( 'How to Configure Email', 'third-audience' ); ?>
		</h2>
		<p><?php esc_html_e( 'WordPress needs an SMTP plugin to send emails reliably. Follow these steps:', 'third-audience' ); ?></p>

		<div class="ta-setup-steps">
			<div class="ta-step">
				<div class="ta-step-number">1</div>
				<div class="ta-step-content">
					<h4><?php esc_html_e( 'Install WP Mail SMTP Plugin', 'third-audience' ); ?></h4>
					<p><?php esc_html_e( 'This is the most popular and reliable email plugin for WordPress.', 'third-audience' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=wp+mail+smtp&tab=search&type=term' ) ); ?>" class="button button-primary">
						<span class="dashicons dashicons-admin-plugins"></span>
						<?php esc_html_e( 'Install WP Mail SMTP', 'third-audience' ); ?>
					</a>
				</div>
			</div>

			<div class="ta-step">
				<div class="ta-step-number">2</div>
				<div class="ta-step-content">
					<h4><?php esc_html_e( 'Configure SMTP Settings', 'third-audience' ); ?></h4>
					<p><?php esc_html_e( 'After installation, go to WP Mail SMTP settings and choose your email provider:', 'third-audience' ); ?></p>
					<ul class="ta-provider-list">
						<li><strong>Gmail / Google Workspace</strong> &mdash; <?php esc_html_e( 'Best for personal sites', 'third-audience' ); ?></li>
						<li><strong>SendGrid / Mailgun</strong> &mdash; <?php esc_html_e( 'Best for high-volume sites', 'third-audience' ); ?></li>
						<li><strong>Amazon SES</strong> &mdash; <?php esc_html_e( 'Best for AWS users', 'third-audience' ); ?></li>
						<li><strong>Other SMTP</strong> &mdash; <?php esc_html_e( 'Any SMTP server (hosting provider, etc.)', 'third-audience' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="ta-step">
				<div class="ta-step-number">3</div>
				<div class="ta-step-content">
					<h4><?php esc_html_e( 'Send a Test Email', 'third-audience' ); ?></h4>
					<p><?php esc_html_e( 'Use WP Mail SMTP\'s built-in test feature to verify emails are working, then return here to enable digests.', 'third-audience' ); ?></p>
				</div>
			</div>
		</div>

		<div class="ta-help-box">
			<span class="dashicons dashicons-info"></span>
			<div>
				<strong><?php esc_html_e( 'Gmail SMTP Quick Setup', 'third-audience' ); ?></strong>
				<p><?php esc_html_e( 'For Gmail, you\'ll need to create an "App Password" in your Google Account security settings. Regular Gmail passwords won\'t work with SMTP.', 'third-audience' ); ?></p>
				<a href="https://wpmailsmtp.com/docs/how-to-set-up-the-gmail-mailer-in-wp-mail-smtp/" target="_blank" rel="noopener"><?php esc_html_e( 'View Gmail Setup Guide', 'third-audience' ); ?> &rarr;</a>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<form method="post" class="ta-settings-form">
		<?php wp_nonce_field( 'ta_digest_settings' ); ?>

		<div class="ta-settings-section">
			<h2><?php esc_html_e( 'Email Schedule', 'third-audience' ); ?></h2>

			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Enable Email Digest', 'third-audience' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="ta_digest_enabled" value="1" <?php checked( $enabled ); ?>>
							<?php esc_html_e( 'Send scheduled email reports', 'third-audience' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Frequency', 'third-audience' ); ?></th>
					<td>
						<select name="ta_digest_frequency">
							<option value="hourly" <?php selected( $frequency, 'hourly' ); ?>><?php esc_html_e( 'Hourly', 'third-audience' ); ?></option>
							<option value="daily" <?php selected( $frequency, 'daily' ); ?>><?php esc_html_e( 'Daily', 'third-audience' ); ?></option>
							<option value="weekly" <?php selected( $frequency, 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'third-audience' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Send Time', 'third-audience' ); ?></th>
					<td>
						<input type="time" name="ta_digest_time" value="<?php echo esc_attr( $time ); ?>">
						<p class="description"><?php esc_html_e( 'Time to send the digest (server timezone).', 'third-audience' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Recipients', 'third-audience' ); ?></th>
					<td>
						<input type="text" name="ta_digest_recipients" value="<?php echo esc_attr( $recipients ); ?>" class="regular-text">
						<p class="description"><?php esc_html_e( 'Comma-separated email addresses.', 'third-audience' ); ?></p>
					</td>
				</tr>
			</table>
		</div>

		<div class="ta-settings-section">
			<h2><?php esc_html_e( 'Report Content', 'third-audience' ); ?></h2>

			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Include in Report', 'third-audience' ); ?></th>
					<td>
						<fieldset>
							<label><input type="checkbox" name="ta_include_bots" value="1" <?php checked( $inc_bots ); ?>> <?php esc_html_e( 'Bot Visit Summary (by bot type)', 'third-audience' ); ?></label><br>
							<label><input type="checkbox" name="ta_include_pages" value="1" <?php checked( $inc_pages ); ?>> <?php esc_html_e( 'Top Crawled Pages', 'third-audience' ); ?></label><br>
							<label><input type="checkbox" name="ta_include_citations" value="1" <?php checked( $inc_cites ); ?>> <?php esc_html_e( 'Citation Clicks', 'third-audience' ); ?></label><br>
							<label><input type="checkbox" name="ta_include_new_bots" value="1" <?php checked( $inc_new ); ?>> <?php esc_html_e( 'New Bots Detected', 'third-audience' ); ?></label><br>
							<label><input type="checkbox" name="ta_include_content_type" value="1" <?php checked( $inc_content ); ?>> <?php esc_html_e( 'HTML vs Markdown Breakdown', 'third-audience' ); ?></label>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Attach Report', 'third-audience' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="ta_digest_attach_md" value="1" <?php checked( $attach_md ); ?>>
							<?php esc_html_e( 'Attach .md report file to email', 'third-audience' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Includes a downloadable markdown report with full details.', 'third-audience' ); ?></p>
					</td>
				</tr>
			</table>
		</div>

		<div class="ta-settings-section">
			<h2><?php esc_html_e( 'Real-time Alerts', 'third-audience' ); ?></h2>

			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Enable Alerts', 'third-audience' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="ta_alerts_enabled" value="1" <?php checked( $alerts ); ?>>
							<?php esc_html_e( 'Send immediate alerts for important events', 'third-audience' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Alerts: First visit from new bot, Citation spikes, Unusual patterns.', 'third-audience' ); ?></p>
					</td>
				</tr>
			</table>
		</div>

		<p class="submit">
			<input type="submit" name="ta_save_digest_settings" class="button button-primary" value="<?php esc_attr_e( 'Save Settings', 'third-audience' ); ?>">
			<button type="button" id="ta-send-test-digest" class="button"><?php esc_html_e( 'Send Test Email', 'third-audience' ); ?></button>
		</p>
	</form>

	<div class="ta-settings-section" style="margin-top: 30px;">
		<h2><?php esc_html_e( 'Download Report Now', 'third-audience' ); ?></h2>
		<p><?php esc_html_e( 'Generate and download a bot activity report immediately.', 'third-audience' ); ?></p>
		<p>
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=third-audience-email-digest&action=download_report&period=24' ), 'ta_download_digest_report' ) ); ?>" class="button">
				<?php esc_html_e( 'Download Last 24 Hours (.md)', 'third-audience' ); ?>
			</a>
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=third-audience-email-digest&action=download_report&period=168' ), 'ta_download_digest_report' ) ); ?>" class="button">
				<?php esc_html_e( 'Download Last 7 Days (.md)', 'third-audience' ); ?>
			</a>
		</p>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	$('#ta-send-test-digest').on('click', function() {
		var $btn = $(this);
		$btn.prop('disabled', true).text('<?php esc_html_e( 'Sending...', 'third-audience' ); ?>');

		$.post(ajaxurl, {
			action: 'ta_send_test_digest',
			nonce: '<?php echo esc_js( wp_create_nonce( 'ta_test_digest' ) ); ?>'
		}, function(response) {
			if (response.success) {
				alert('<?php esc_html_e( 'Test email sent successfully!', 'third-audience' ); ?>');
			} else {
				alert('<?php esc_html_e( 'Failed to send test email.', 'third-audience' ); ?>');
			}
			$btn.prop('disabled', false).text('<?php esc_html_e( 'Send Test Email', 'third-audience' ); ?>');
		});
	});
});
</script>

<style>
.ta-email-digest-settings h1 .dashicons { vertical-align: middle; margin-right: 8px; }
.ta-settings-section { background: #fff; padding: 20px; border: 1px solid #e5e5e5; border-radius: 4px; margin: 20px 0; }
.ta-settings-section h2 { margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #e5e5e5; }
.ta-settings-section h2 .dashicons { vertical-align: middle; margin-right: 5px; color: #2271b1; }
.ta-settings-form .form-table th { width: 200px; }

/* Email Status Box */
.ta-email-status-box {
	display: flex;
	align-items: flex-start;
	gap: 15px;
	padding: 15px 20px;
	border-radius: 4px;
	margin: 20px 0;
}
.ta-email-status-box.status-ok {
	background: #e7f8ed;
	border: 1px solid #00a32a;
}
.ta-email-status-box.status-warning {
	background: #fff8e5;
	border: 1px solid #dba617;
}
.ta-status-icon .dashicons {
	font-size: 24px;
	width: 24px;
	height: 24px;
}
.status-ok .ta-status-icon .dashicons { color: #00a32a; }
.status-warning .ta-status-icon .dashicons { color: #dba617; }
.ta-status-content p { margin: 5px 0 0; }
.ta-status-content strong { font-size: 14px; }

/* Setup Guide */
.ta-setup-guide { background: #f8f9fa; }
.ta-setup-steps { margin: 20px 0; }
.ta-step {
	display: flex;
	gap: 15px;
	margin-bottom: 25px;
	padding-bottom: 25px;
	border-bottom: 1px dashed #ddd;
}
.ta-step:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.ta-step-number {
	flex-shrink: 0;
	width: 32px;
	height: 32px;
	background: #2271b1;
	color: #fff;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	font-weight: 600;
	font-size: 14px;
}
.ta-step-content h4 { margin: 0 0 8px; font-size: 14px; }
.ta-step-content p { margin: 0 0 10px; color: #50575e; }
.ta-step-content .button .dashicons {
	font-size: 16px;
	width: 16px;
	height: 16px;
	vertical-align: middle;
	margin-right: 3px;
	margin-top: -2px;
}
.ta-provider-list {
	margin: 10px 0 0;
	padding-left: 20px;
	color: #50575e;
}
.ta-provider-list li { margin-bottom: 5px; }
.ta-provider-list strong { color: #1d2327; }

/* Help Box */
.ta-help-box {
	display: flex;
	gap: 12px;
	background: #e7f6ff;
	border: 1px solid #72aee6;
	border-radius: 4px;
	padding: 15px;
	margin-top: 20px;
}
.ta-help-box > .dashicons {
	color: #2271b1;
	flex-shrink: 0;
}
.ta-help-box strong { display: block; margin-bottom: 5px; }
.ta-help-box p { margin: 0 0 8px; color: #50575e; }
.ta-help-box a { font-weight: 500; }
</style>
