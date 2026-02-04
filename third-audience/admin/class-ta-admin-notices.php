<?php
/**
 * Admin Notices - Show auto-configuration results to admin.
 *
 * Displays informative notices about environment detection and
 * auto-configuration results after plugin activation.
 *
 * @package ThirdAudience
 * @since   3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Admin_Notices
 *
 * Manages admin notices for auto-configuration feedback.
 *
 * @since 3.4.0
 */
class TA_Admin_Notices {

	/**
	 * Initialize admin notices.
	 */
	public function init() {
		add_action( 'admin_notices', array( $this, 'show_activation_notices' ) );
		add_action( 'admin_notices', array( $this, 'show_environment_warnings' ) );
		add_action( 'admin_notices', array( $this, 'show_fallback_mode_notice' ) );
	}

	/**
	 * Show notices after activation.
	 */
	public function show_activation_notices() {
		// Only show on Third Audience pages or immediately after activation.
		if ( ! $this->is_ta_admin_page() && ! get_transient( 'ta_show_activation_notice' ) ) {
			return;
		}

		// Get environment detection results.
		$env = get_option( 'ta_environment_detection', array() );

		if ( empty( $env ) ) {
			return;
		}

		// Only show once.
		if ( ! get_transient( 'ta_show_activation_notice' ) ) {
			return;
		}

		?>
		<div class="notice notice-success is-dismissible" style="padding: 15px;">
			<h2 style="margin-top: 0;">🎉 Third Audience Activated Successfully</h2>
			<p><strong>Auto-configuration completed:</strong></p>
			<ul style="list-style: disc; margin-left: 20px;">
				<?php
				// REST API status.
				if ( ! empty( $env['rest_api']['accessible'] ) ) :
					?>
					<li>✅ <strong>REST API:</strong> Accessible - Using standard endpoints</li>
				<?php else : ?>
					<li>✅ <strong>API Mode:</strong> Using AJAX endpoints (Standard mode for headless/secured sites)
						<ul style="list-style: circle; margin-left: 20px; margin-top: 5px;">
							<li><strong>Why:</strong> <?php echo esc_html( $env['rest_api']['blocker'] ?? 'Security plugin detected' ); ?></li>
							<li><strong>Benefit:</strong> admin-ajax.php endpoints are security-plugin friendly</li>
							<li><em>✅ This is the recommended mode for production sites with security plugins!</em></li>
						</ul>
					</li>
				<?php endif; ?>

				<?php
				// Security plugins.
				if ( $env['security_plugins'] ) :
					?>
					<li>🔒 <strong>Security Plugin Detected:</strong> <?php echo esc_html( $env['security_plugins'] ); ?>
						<ul style="list-style: circle; margin-left: 20px; margin-top: 5px;">
							<li>✅ Auto-whitelisted Third Audience endpoints</li>
						</ul>
					</li>
				<?php endif; ?>

				<?php
				// Database.
				if ( ! empty( $env['db_permissions']['create'] ) && ! empty( $env['db_permissions']['alter'] ) ) :
					?>
					<li>✅ <strong>Database:</strong> All permissions OK - Tables created successfully</li>
				<?php else : ?>
					<li>⚠️ <strong>Database:</strong> Limited permissions
						<ul style="list-style: circle; margin-left: 20px; margin-top: 5px;">
							<?php if ( empty( $env['db_permissions']['create'] ) ) : ?>
								<li>❌ Cannot CREATE tables</li>
							<?php endif; ?>
							<?php if ( empty( $env['db_permissions']['alter'] ) ) : ?>
								<li>❌ Cannot ALTER tables</li>
							<?php endif; ?>
							<li><a href="<?php echo esc_url( admin_url( 'options-general.php?page=third-audience&tab=system-health' ) ); ?>">View manual SQL commands</a></li>
						</ul>
					</li>
				<?php endif; ?>

				<?php
				// Caching plugins.
				if ( ! empty( $env['caching_plugins'] ) ) :
					?>
					<li>💾 <strong>Caching Plugin Detected:</strong> <?php echo esc_html( implode( ', ', $env['caching_plugins'] ) ); ?>
						<ul style="list-style: circle; margin-left: 20px; margin-top: 5px;">
							<li>✅ Admin pages excluded from cache automatically</li>
						</ul>
					</li>
				<?php endif; ?>

				<li>🖥️ <strong>Server:</strong> <?php echo esc_html( $env['server_type'] ?? 'unknown' ); ?></li>
				<li>🐘 <strong>PHP:</strong> <?php echo esc_html( $env['php_version'] ?? 'unknown' ); ?></li>
				<li>📦 <strong>WordPress:</strong> <?php echo esc_html( $env['wp_version'] ?? 'unknown' ); ?></li>
			</ul>

			<p style="margin-top: 15px; padding: 10px; background: #f0f7ff; border-left: 4px solid #0073aa;">
				<strong>✨ No server configuration needed - everything is configured automatically!</strong>
			</p>

			<p style="margin-bottom: 0;">
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=third-audience' ) ); ?>" class="button button-primary">
					Configure Settings
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-bot-analytics' ) ); ?>" class="button">
					View Analytics
				</a>
			</p>
		</div>
		<?php

		// Clear the transient after showing.
		delete_transient( 'ta_show_activation_notice' );
	}

	/**
	 * Show environment warnings.
	 */
	public function show_environment_warnings() {
		if ( ! $this->is_ta_admin_page() ) {
			return;
		}

		$env = get_option( 'ta_environment_detection', array() );

		// Info about AJAX mode (not a warning - this is good!).
		if ( isset( $env['rest_api'] ) && ! $env['rest_api']['accessible'] ) {
			// Only show if not dismissed.
			if ( ! get_user_meta( get_current_user_id(), 'ta_dismiss_fallback_notice', true ) ) {
				?>
				<div class="notice notice-info is-dismissible" data-notice="fallback-mode">
					<h3 style="margin-top: 10px;">✅ Running in AJAX Mode (Secure & Reliable)</h3>
					<p><strong>Third Audience is using AJAX endpoints</strong> - the standard WordPress API method that works with ALL security plugins.</p>
					<p><strong>Why this is better for production sites:</strong></p>
					<ul style="list-style: disc; margin-left: 20px;">
						<li>✅ Compatible with security plugins: <strong><?php echo esc_html( $env['security_plugins'] ?? 'All security plugins' ); ?></strong></li>
						<li>✅ Works on headless WordPress sites</li>
						<li>✅ No security plugin conflicts</li>
						<li>✅ Same features as REST API, zero compromises</li>
					</ul>
					<p><em>No action required - your site is configured optimally!</em></p>
				</div>
				<script>
				jQuery(document).ready(function($) {
					$('.notice[data-notice="fallback-mode"]').on('click', '.notice-dismiss', function() {
						$.post(ajaxurl, {
							action: 'ta_dismiss_fallback_notice',
							nonce: '<?php echo esc_js( wp_create_nonce( 'ta_dismiss_notice' ) ); ?>'
						});
					});
				});
				</script>
				<?php
			}
		}

		// Warning if database permissions limited.
		if ( isset( $env['db_permissions'] ) ) {
			if ( empty( $env['db_permissions']['create'] ) || empty( $env['db_permissions']['alter'] ) ) {
				?>
				<div class="notice notice-error">
					<h3 style="margin-top: 10px;">❌ Database Permission Issue</h3>
					<p>Your database user has limited permissions. Some features may not work correctly.</p>
					<p><strong>Missing permissions:</strong></p>
					<ul style="list-style: disc; margin-left: 20px;">
						<?php if ( empty( $env['db_permissions']['create'] ) ) : ?>
							<li>CREATE TABLE</li>
						<?php endif; ?>
						<?php if ( empty( $env['db_permissions']['alter'] ) ) : ?>
							<li>ALTER TABLE</li>
						<?php endif; ?>
					</ul>
					<p>
						<a href="<?php echo esc_url( admin_url( 'options-general.php?page=third-audience&tab=system-health' ) ); ?>" class="button">
							View Manual SQL Commands
						</a>
						<a href="https://docs.thirdaudience.com/database-permissions" class="button" target="_blank">
							Contact Hosting Support
						</a>
					</p>
				</div>
				<?php
			}
		}
	}

	/**
	 * Show notice when REST API becomes available.
	 */
	public function show_fallback_mode_notice() {
		if ( ! $this->is_ta_admin_page() ) {
			return;
		}

		// Check if REST API became available.
		if ( get_transient( 'ta_rest_api_now_available' ) ) {
			?>
			<div class="notice notice-success is-dismissible">
				<h3 style="margin-top: 10px;">✅ REST API Now Available!</h3>
				<p>Good news! REST API is now accessible on your server.</p>
				<p>Third Audience has automatically switched from AJAX fallback to standard REST API endpoints for better performance.</p>
			</div>
			<?php
			delete_transient( 'ta_rest_api_now_available' );
		}
	}

	/**
	 * Check if current page is a Third Audience admin page.
	 *
	 * @return bool True if on TA admin page.
	 */
	private function is_ta_admin_page() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return false;
		}
		return strpos( $screen->id, 'third-audience' ) !== false;
	}

	/**
	 * AJAX handler to dismiss fallback notice.
	 */
	public function handle_dismiss_fallback_notice() {
		check_ajax_referer( 'ta_dismiss_notice', 'nonce' );

		update_user_meta( get_current_user_id(), 'ta_dismiss_fallback_notice', true );

		wp_send_json_success();
	}

	/**
	 * Generate configuration summary for display.
	 *
	 * @return string HTML formatted summary.
	 */
	public function get_configuration_summary() {
		$env = get_option( 'ta_environment_detection', array() );

		if ( empty( $env ) ) {
			return '<p><em>Environment detection not run yet. Activate plugin to run detection.</em></p>';
		}

		ob_start();
		?>
		<div class="ta-config-summary" style="background: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 4px;">
			<h3 style="margin-top: 0;">Current Configuration</h3>

			<table class="widefat" style="margin-top: 15px;">
				<tr>
					<th style="width: 200px;">Feature</th>
					<th>Status</th>
					<th>Details</th>
				</tr>
				<tr>
					<td><strong>API Method</strong></td>
					<td>
						<?php if ( ! empty( $env['rest_api']['accessible'] ) ) : ?>
							<span style="color: green; font-weight: bold;">✓ REST API</span>
						<?php else : ?>
							<span style="color: green; font-weight: bold;">✓ AJAX (Standard)</span>
						<?php endif; ?>
					</td>
					<td>
						<?php if ( ! empty( $env['rest_api']['accessible'] ) ) : ?>
							Using REST API endpoints
						<?php else : ?>
							Using AJAX endpoints (security-plugin friendly, recommended for production)
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td><strong>Security Plugin</strong></td>
					<td>
						<?php if ( $env['security_plugins'] ) : ?>
							<span style="color: green; font-weight: bold;">✓ Configured</span>
						<?php else : ?>
							<span style="color: green; font-weight: bold;">—</span>
						<?php endif; ?>
					</td>
					<td>
						<?php if ( $env['security_plugins'] ) : ?>
							<?php echo esc_html( $env['security_plugins'] ); ?> - endpoints whitelisted
						<?php else : ?>
							No security plugin detected
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td><strong>Database</strong></td>
					<td>
						<?php if ( ! empty( $env['db_permissions']['create'] ) && ! empty( $env['db_permissions']['alter'] ) ) : ?>
							<span style="color: green; font-weight: bold;">✓ Full Access</span>
						<?php else : ?>
							<span style="color: red; font-weight: bold;">✗ Limited</span>
						<?php endif; ?>
					</td>
					<td>
						<?php if ( ! empty( $env['db_permissions']['create'] ) && ! empty( $env['db_permissions']['alter'] ) ) : ?>
							All tables and columns created successfully
						<?php else : ?>
							Manual SQL commands may be required
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td><strong>Server Type</strong></td>
					<td><span style="font-weight: bold;">—</span></td>
					<td><?php echo esc_html( ucfirst( $env['server_type'] ?? 'unknown' ) ); ?></td>
				</tr>
				<tr>
					<td><strong>PHP Version</strong></td>
					<td>
						<?php if ( version_compare( $env['php_version'] ?? '0', '7.4', '>=' ) ) : ?>
							<span style="color: green; font-weight: bold;">✓</span>
						<?php else : ?>
							<span style="color: red; font-weight: bold;">✗</span>
						<?php endif; ?>
					</td>
					<td><?php echo esc_html( $env['php_version'] ?? 'unknown' ); ?></td>
				</tr>
			</table>

			<p style="margin-top: 15px; margin-bottom: 0;">
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=third-audience&tab=system-health' ) ); ?>" class="button">
					View Full System Health Report
				</a>
			</p>
		</div>
		<?php
		return ob_get_clean();
	}
}
