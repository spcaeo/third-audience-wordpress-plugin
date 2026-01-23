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

// Handle form submission.
if ( isset( $_POST['ta_save_digest_settings'] ) && check_admin_referer( 'ta_digest_settings' ) ) {
	update_option( 'ta_email_digest_enabled', isset( $_POST['ta_digest_enabled'] ) );
	update_option( 'ta_email_digest_frequency', sanitize_text_field( $_POST['ta_digest_frequency'] ?? 'daily' ) );
	update_option( 'ta_email_digest_time', sanitize_text_field( $_POST['ta_digest_time'] ?? '09:00' ) );
	update_option( 'ta_email_digest_recipients', sanitize_text_field( $_POST['ta_digest_recipients'] ?? '' ) );
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
?>

<div class="wrap ta-email-digest-settings">
	<h1>
		<span class="dashicons dashicons-email-alt"></span>
		<?php esc_html_e( 'Email Digest Settings', 'third-audience' ); ?>
	</h1>
	<p class="description"><?php esc_html_e( 'Configure automated email reports of AI bot activity.', 'third-audience' ); ?></p>

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
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=third-audience-email-digest&action=download_md&period=24' ), 'ta_download_report' ) ); ?>" class="button">
				<?php esc_html_e( 'Download Last 24 Hours (.md)', 'third-audience' ); ?>
			</a>
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=third-audience-email-digest&action=download_md&period=168' ), 'ta_download_report' ) ); ?>" class="button">
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
.ta-settings-form .form-table th { width: 200px; }
</style>
