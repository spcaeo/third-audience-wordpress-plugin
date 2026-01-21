<?php
/**
 * Settings page template.
 *
 * Displays the admin settings page with tabs for General, Notifications, and Logs.
 *
 * @package ThirdAudience
 * @since   1.0.0
 *
 * @var TA_Security       $this->security          Security instance (via TA_Admin).
 * @var array             $cache_stats             Cache statistics.
 * @var array             $error_stats             Error statistics.
 * @var array             $recent_errors           Recent error entries.
 * @var array             $smtp_settings           SMTP configuration.
 * @var array             $notification_settings   Notification preferences.
 * @var string            $current_tab             Current active tab.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define tabs.
$tabs = array(
	'general'       => __( 'General', 'third-audience' ),
	'notifications' => __( 'Notifications', 'third-audience' ),
	'logs'          => __( 'Logs', 'third-audience' ),
);

// Security instance for nonces.
$security = TA_Security::get_instance();
?>
<div class="wrap ta-settings-wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<!-- Tab Navigation -->
	<nav class="nav-tab-wrapper ta-nav-tabs">
		<?php foreach ( $tabs as $tab_id => $tab_name ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'tab', $tab_id, admin_url( 'options-general.php?page=third-audience' ) ) ); ?>"
			   class="nav-tab <?php echo $current_tab === $tab_id ? 'nav-tab-active' : ''; ?>">
				<?php echo esc_html( $tab_name ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<div class="ta-admin-container">
		<?php if ( 'general' === $current_tab ) : ?>
			<!-- General Settings Tab -->
			<div class="ta-tab-content ta-tab-general">
				<div class="ta-settings-layout">
					<div class="ta-settings-main">
						<form method="post" action="options.php">
							<?php settings_fields( 'ta_settings' ); ?>

							<div class="ta-card">
								<h2><?php esc_html_e( 'Cache Settings', 'third-audience' ); ?></h2>
								<table class="form-table" role="presentation">
									<tr>
										<th scope="row">
											<label for="ta_cache_ttl"><?php esc_html_e( 'Cache Duration', 'third-audience' ); ?></label>
										</th>
										<td>
											<select name="ta_cache_ttl" id="ta_cache_ttl">
												<option value="3600" <?php selected( get_option( 'ta_cache_ttl', 86400 ), 3600 ); ?>><?php esc_html_e( '1 Hour', 'third-audience' ); ?></option>
												<option value="21600" <?php selected( get_option( 'ta_cache_ttl', 86400 ), 21600 ); ?>><?php esc_html_e( '6 Hours', 'third-audience' ); ?></option>
												<option value="43200" <?php selected( get_option( 'ta_cache_ttl', 86400 ), 43200 ); ?>><?php esc_html_e( '12 Hours', 'third-audience' ); ?></option>
												<option value="86400" <?php selected( get_option( 'ta_cache_ttl', 86400 ), 86400 ); ?>><?php esc_html_e( '24 Hours', 'third-audience' ); ?></option>
												<option value="604800" <?php selected( get_option( 'ta_cache_ttl', 86400 ), 604800 ); ?>><?php esc_html_e( '7 Days', 'third-audience' ); ?></option>
											</select>
											<p class="description"><?php esc_html_e( 'How long to cache converted markdown.', 'third-audience' ); ?></p>
										</td>
									</tr>
								</table>
							</div>

							<div class="ta-card">
								<h2><?php esc_html_e( 'Feature Settings', 'third-audience' ); ?></h2>
								<table class="form-table" role="presentation">
									<tr>
										<th scope="row"><?php esc_html_e( 'Enabled Post Types', 'third-audience' ); ?></th>
										<td>
											<?php
											$enabled_types = get_option( 'ta_enabled_post_types', array( 'post', 'page' ) );
											$post_types    = get_post_types( array( 'public' => true ), 'objects' );
											foreach ( $post_types as $post_type ) :
												if ( 'attachment' === $post_type->name ) {
													continue;
												}
												?>
												<label class="ta-checkbox-label">
													<input type="checkbox" name="ta_enabled_post_types[]"
														   value="<?php echo esc_attr( $post_type->name ); ?>"
														   <?php checked( in_array( $post_type->name, $enabled_types, true ) ); ?> />
													<?php echo esc_html( $post_type->labels->name ); ?>
												</label>
											<?php endforeach; ?>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Content Negotiation', 'third-audience' ); ?></th>
										<td>
											<label class="ta-checkbox-label">
												<input type="checkbox" name="ta_enable_content_negotiation" value="1"
													   <?php checked( get_option( 'ta_enable_content_negotiation', true ) ); ?> />
												<?php esc_html_e( 'Enable Accept: text/markdown header support', 'third-audience' ); ?>
											</label>
											<p class="description"><?php esc_html_e( 'Redirect to .md URL when client sends Accept: text/markdown header.', 'third-audience' ); ?></p>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Discovery Tags', 'third-audience' ); ?></th>
										<td>
											<label class="ta-checkbox-label">
												<input type="checkbox" name="ta_enable_discovery_tags" value="1"
													   <?php checked( get_option( 'ta_enable_discovery_tags', true ) ); ?> />
												<?php
												/* translators: HTML code element for the link rel tag */
												printf( esc_html__( 'Add %s tags', 'third-audience' ), '<code>&lt;link rel="alternate"&gt;</code>' );
												?>
											</label>
											<p class="description"><?php esc_html_e( 'Help AI crawlers discover markdown versions of your content.', 'third-audience' ); ?></p>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Pre-generate Markdown', 'third-audience' ); ?></th>
										<td>
											<label class="ta-checkbox-label">
												<input type="checkbox" name="ta_enable_pre_generation" value="1"
													   <?php checked( get_option( 'ta_enable_pre_generation', true ) ); ?> />
												<?php esc_html_e( 'Generate markdown when posts are published', 'third-audience' ); ?>
											</label>
											<p class="description"><?php esc_html_e( 'Recommended: Pre-generates markdown on save so it\'s always instantly available for AI crawlers.', 'third-audience' ); ?></p>
										</td>
									</tr>
								</table>
							</div>

							<?php submit_button(); ?>
						</form>
					</div>

					<div class="ta-settings-sidebar">
						<!-- Cache Status Card -->
						<div class="ta-card">
							<h2><?php esc_html_e( 'Cache Status', 'third-audience' ); ?></h2>
							<div class="ta-stat-grid">
								<div class="ta-stat">
									<span class="ta-stat-value"><?php echo esc_html( $cache_stats['count'] ); ?></span>
									<span class="ta-stat-label"><?php esc_html_e( 'Cached Items', 'third-audience' ); ?></span>
								</div>
								<div class="ta-stat">
									<span class="ta-stat-value"><?php echo esc_html( $cache_stats['size_human'] ); ?></span>
									<span class="ta-stat-label"><?php esc_html_e( 'Cache Size', 'third-audience' ); ?></span>
								</div>
							</div>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ta-form-inline">
								<?php $security->nonce_field( 'clear_cache' ); ?>
								<input type="hidden" name="action" value="ta_clear_cache" />
								<button type="submit" class="button button-secondary" id="ta-clear-cache-btn">
									<?php esc_html_e( 'Clear All Cache', 'third-audience' ); ?>
								</button>
							</form>
						</div>

						<!-- How It Works Card -->
						<div class="ta-card">
							<h2><?php esc_html_e( 'How It Works', 'third-audience' ); ?></h2>
							<ol class="ta-steps-list">
								<li><?php esc_html_e( 'AI crawlers request page.md URLs', 'third-audience' ); ?></li>
								<li><?php esc_html_e( 'Plugin intercepts the request', 'third-audience' ); ?></li>
								<li><?php esc_html_e( 'Checks local cache first', 'third-audience' ); ?></li>
								<li><?php esc_html_e( 'If miss, converts HTML to Markdown locally', 'third-audience' ); ?></li>
								<li><?php esc_html_e( 'Caches and returns clean Markdown', 'third-audience' ); ?></li>
							</ol>
							<p class="description" style="margin-top: 10px;">
								<strong><?php esc_html_e( 'All conversion happens on your server - no external dependencies!', 'third-audience' ); ?></strong>
							</p>
						</div>

						<!-- Test Your Setup Card -->
						<div class="ta-card">
							<h2><?php esc_html_e( 'Test Your Setup', 'third-audience' ); ?></h2>
							<p><?php esc_html_e( 'Try requesting a markdown version:', 'third-audience' ); ?></p>
							<?php
							$sample_post = get_posts( array(
								'numberposts' => 1,
								'post_status' => 'publish',
							) );
							if ( $sample_post ) :
								$md_url = untrailingslashit( get_permalink( $sample_post[0] ) ) . '.md';
								?>
								<p>
									<a href="<?php echo esc_url( $md_url ); ?>" target="_blank" class="button button-secondary">
										<?php esc_html_e( 'View Sample .md', 'third-audience' ); ?>
									</a>
								</p>
								<p class="description">
									<code class="ta-url-display"><?php echo esc_html( $md_url ); ?></code>
								</p>
							<?php else : ?>
								<p class="description"><?php esc_html_e( 'No published posts found.', 'third-audience' ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>

		<?php elseif ( 'notifications' === $current_tab ) : ?>
			<!-- Notifications Tab -->
			<div class="ta-tab-content ta-tab-notifications">
				<div class="ta-settings-layout">
					<div class="ta-settings-main">
						<!-- SMTP Settings -->
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<?php $security->nonce_field( 'save_smtp_settings' ); ?>
							<input type="hidden" name="action" value="ta_save_smtp_settings" />

							<div class="ta-card">
								<h2><?php esc_html_e( 'SMTP Configuration', 'third-audience' ); ?></h2>
								<p class="description"><?php esc_html_e( 'Configure SMTP settings for sending notification emails. Leave empty to use WordPress default mail settings.', 'third-audience' ); ?></p>

								<table class="form-table" role="presentation">
									<tr>
										<th scope="row"><?php esc_html_e( 'Enable SMTP', 'third-audience' ); ?></th>
										<td>
											<label class="ta-checkbox-label">
												<input type="checkbox" name="ta_smtp[enabled]" value="1"
													   <?php checked( ! empty( $smtp_settings['enabled'] ) ); ?> />
												<?php esc_html_e( 'Use custom SMTP settings', 'third-audience' ); ?>
											</label>
										</td>
									</tr>
									<tr>
										<th scope="row">
											<label for="ta_smtp_host"><?php esc_html_e( 'SMTP Host', 'third-audience' ); ?></label>
										</th>
										<td>
											<input type="text" name="ta_smtp[host]" id="ta_smtp_host" class="regular-text"
												   value="<?php echo esc_attr( $smtp_settings['host'] ?? '' ); ?>"
												   placeholder="smtp.gmail.com" />
										</td>
									</tr>
									<tr>
										<th scope="row">
											<label for="ta_smtp_port"><?php esc_html_e( 'SMTP Port', 'third-audience' ); ?></label>
										</th>
										<td>
											<input type="number" name="ta_smtp[port]" id="ta_smtp_port" class="small-text"
												   value="<?php echo esc_attr( $smtp_settings['port'] ?? 587 ); ?>"
												   min="1" max="65535" />
											<p class="description"><?php esc_html_e( 'Common ports: 25, 465 (SSL), 587 (TLS)', 'third-audience' ); ?></p>
										</td>
									</tr>
									<tr>
										<th scope="row">
											<label for="ta_smtp_encryption"><?php esc_html_e( 'Encryption', 'third-audience' ); ?></label>
										</th>
										<td>
											<select name="ta_smtp[encryption]" id="ta_smtp_encryption">
												<option value="" <?php selected( $smtp_settings['encryption'] ?? '', '' ); ?>><?php esc_html_e( 'None', 'third-audience' ); ?></option>
												<option value="ssl" <?php selected( $smtp_settings['encryption'] ?? '', 'ssl' ); ?>>SSL</option>
												<option value="tls" <?php selected( $smtp_settings['encryption'] ?? 'tls', 'tls' ); ?>>TLS</option>
											</select>
										</td>
									</tr>
									<tr>
										<th scope="row">
											<label for="ta_smtp_username"><?php esc_html_e( 'SMTP Username', 'third-audience' ); ?></label>
										</th>
										<td>
											<input type="text" name="ta_smtp[username]" id="ta_smtp_username" class="regular-text"
												   value="<?php echo esc_attr( $smtp_settings['username'] ?? '' ); ?>"
												   autocomplete="username" />
										</td>
									</tr>
									<tr>
										<th scope="row">
											<label for="ta_smtp_password"><?php esc_html_e( 'SMTP Password', 'third-audience' ); ?></label>
										</th>
										<td>
											<input type="password" name="ta_smtp[password]" id="ta_smtp_password" class="regular-text"
												   value="" placeholder="<?php echo ! empty( $smtp_settings['password'] ) ? '********' : ''; ?>"
												   autocomplete="new-password" />
											<p class="description"><?php esc_html_e( 'Leave empty to keep existing password. For Gmail, use an App Password.', 'third-audience' ); ?></p>
										</td>
									</tr>
									<tr>
										<th scope="row">
											<label for="ta_smtp_from_email"><?php esc_html_e( 'From Email', 'third-audience' ); ?></label>
										</th>
										<td>
											<input type="email" name="ta_smtp[from_email]" id="ta_smtp_from_email" class="regular-text"
												   value="<?php echo esc_attr( $smtp_settings['from_email'] ?? '' ); ?>"
												   placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" />
										</td>
									</tr>
									<tr>
										<th scope="row">
											<label for="ta_smtp_from_name"><?php esc_html_e( 'From Name', 'third-audience' ); ?></label>
										</th>
										<td>
											<input type="text" name="ta_smtp[from_name]" id="ta_smtp_from_name" class="regular-text"
												   value="<?php echo esc_attr( $smtp_settings['from_name'] ?? '' ); ?>"
												   placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
										</td>
									</tr>
								</table>

								<?php submit_button( __( 'Save SMTP Settings', 'third-audience' ) ); ?>
							</div>
						</form>

						<!-- Notification Settings -->
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<?php $security->nonce_field( 'save_notification_settings' ); ?>
							<input type="hidden" name="action" value="ta_save_notification_settings" />

							<div class="ta-card">
								<h2><?php esc_html_e( 'Notification Triggers', 'third-audience' ); ?></h2>
								<p class="description"><?php esc_html_e( 'Configure when to receive email notifications.', 'third-audience' ); ?></p>

								<table class="form-table" role="presentation">
									<tr>
										<th scope="row">
											<label for="ta_alert_emails"><?php esc_html_e( 'Alert Email Addresses', 'third-audience' ); ?></label>
										</th>
										<td>
											<input type="text" name="ta_notifications[alert_emails]" id="ta_alert_emails" class="large-text"
												   value="<?php echo esc_attr( $notification_settings['alert_emails'] ?? get_option( 'admin_email' ) ); ?>"
												   placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" />
											<p class="description"><?php esc_html_e( 'Comma-separated list of email addresses to receive alerts.', 'third-audience' ); ?></p>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Notification Triggers', 'third-audience' ); ?></th>
										<td>
											<fieldset>
												<label class="ta-checkbox-label">
													<input type="checkbox" name="ta_notifications[on_high_error_rate]" value="1"
														   <?php checked( ! empty( $notification_settings['on_high_error_rate'] ) ); ?> />
													<?php esc_html_e( 'High error rate alerts', 'third-audience' ); ?>
												</label>
												<label class="ta-checkbox-label">
													<input type="checkbox" name="ta_notifications[on_cache_issues]" value="1"
														   <?php checked( ! empty( $notification_settings['on_cache_issues'] ) ); ?> />
													<?php esc_html_e( 'Cache issues', 'third-audience' ); ?>
												</label>
												<label class="ta-checkbox-label">
													<input type="checkbox" name="ta_notifications[on_daily_digest]" value="1"
														   <?php checked( ! empty( $notification_settings['daily_digest'] ) ); ?> />
													<?php esc_html_e( 'Daily digest summary', 'third-audience' ); ?>
												</label>
											</fieldset>
										</td>
									</tr>
									<tr>
										<th scope="row">
											<label for="ta_error_threshold"><?php esc_html_e( 'Error Rate Threshold', 'third-audience' ); ?></label>
										</th>
										<td>
											<input type="number" name="ta_notifications[error_rate_threshold]" id="ta_error_threshold"
												   class="small-text" min="1" max="100"
												   value="<?php echo esc_attr( $notification_settings['error_rate_threshold'] ?? 10 ); ?>" />
											<span class="description"><?php esc_html_e( 'errors per hour', 'third-audience' ); ?></span>
										</td>
									</tr>
								</table>

								<?php submit_button( __( 'Save Notification Settings', 'third-audience' ) ); ?>
							</div>
						</form>
					</div>

					<div class="ta-settings-sidebar">
						<!-- SMTP Test Card -->
						<div class="ta-card">
							<h2><?php esc_html_e( 'Test SMTP', 'third-audience' ); ?></h2>
							<p><?php esc_html_e( 'Send a test email to verify SMTP configuration.', 'third-audience' ); ?></p>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ta-form-inline">
								<?php $security->nonce_field( 'test_smtp' ); ?>
								<input type="hidden" name="action" value="ta_test_smtp" />
								<button type="submit" class="button button-secondary" id="ta-test-smtp-btn">
									<?php esc_html_e( 'Send Test Email', 'third-audience' ); ?>
								</button>
							</form>
						</div>

						<!-- Notification Status Card -->
						<div class="ta-card">
							<h2><?php esc_html_e( 'Notification Status', 'third-audience' ); ?></h2>
							<table class="ta-info-table">
								<tr>
									<td><?php esc_html_e( 'SMTP Configured:', 'third-audience' ); ?></td>
									<td>
										<?php if ( ! empty( $smtp_settings['enabled'] ) && ! empty( $smtp_settings['host'] ) ) : ?>
											<span class="ta-status-badge ta-status-success"><?php esc_html_e( 'Yes', 'third-audience' ); ?></span>
										<?php else : ?>
											<span class="ta-status-badge ta-status-warning"><?php esc_html_e( 'Using Default', 'third-audience' ); ?></span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><?php esc_html_e( 'Alert Emails:', 'third-audience' ); ?></td>
									<td><?php echo esc_html( count( array_filter( explode( ',', $notification_settings['alert_emails'] ?? '' ) ) ) ); ?></td>
								</tr>
								<tr>
									<td><?php esc_html_e( 'Daily Digest:', 'third-audience' ); ?></td>
									<td>
										<?php if ( ! empty( $notification_settings['daily_digest'] ) ) : ?>
											<span class="ta-status-badge ta-status-success"><?php esc_html_e( 'Enabled', 'third-audience' ); ?></span>
										<?php else : ?>
											<span class="ta-status-badge"><?php esc_html_e( 'Disabled', 'third-audience' ); ?></span>
										<?php endif; ?>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>

		<?php elseif ( 'logs' === $current_tab ) : ?>
			<!-- Logs Tab -->
			<div class="ta-tab-content ta-tab-logs">
				<div class="ta-settings-layout">
					<div class="ta-settings-main">
						<div class="ta-card">
							<div class="ta-card-header">
								<h2><?php esc_html_e( 'Recent Errors', 'third-audience' ); ?></h2>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ta-form-inline">
									<?php $security->nonce_field( 'clear_errors' ); ?>
									<input type="hidden" name="action" value="ta_clear_errors" />
									<button type="submit" class="button button-secondary" id="ta-clear-errors-btn">
										<?php esc_html_e( 'Clear Errors', 'third-audience' ); ?>
									</button>
								</form>
							</div>

							<?php if ( empty( $recent_errors ) ) : ?>
								<div class="ta-empty-state">
									<span class="dashicons dashicons-yes-alt"></span>
									<p><?php esc_html_e( 'No errors recorded.', 'third-audience' ); ?></p>
								</div>
							<?php else : ?>
								<table class="widefat ta-errors-table">
									<thead>
										<tr>
											<th><?php esc_html_e( 'Time', 'third-audience' ); ?></th>
											<th><?php esc_html_e( 'Level', 'third-audience' ); ?></th>
											<th><?php esc_html_e( 'Message', 'third-audience' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ( $recent_errors as $error ) : ?>
											<tr class="ta-error-row ta-error-<?php echo esc_attr( strtolower( $error['level'] ) ); ?>">
												<td class="ta-error-time">
													<?php echo esc_html( $error['timestamp'] ); ?>
												</td>
												<td class="ta-error-level">
													<span class="ta-level-badge ta-level-<?php echo esc_attr( strtolower( $error['level'] ) ); ?>">
														<?php echo esc_html( $error['level'] ); ?>
													</span>
												</td>
												<td class="ta-error-message">
													<?php echo esc_html( $error['message'] ); ?>
													<?php if ( ! empty( $error['context'] ) ) : ?>
														<details class="ta-error-context">
															<summary><?php esc_html_e( 'Context', 'third-audience' ); ?></summary>
															<pre><?php echo esc_html( wp_json_encode( $error['context'], JSON_PRETTY_PRINT ) ); ?></pre>
														</details>
													<?php endif; ?>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							<?php endif; ?>
						</div>
					</div>

					<div class="ta-settings-sidebar">
						<!-- Error Statistics Card -->
						<div class="ta-card">
							<h2><?php esc_html_e( 'Error Statistics', 'third-audience' ); ?></h2>
							<div class="ta-stat-grid">
								<div class="ta-stat">
									<span class="ta-stat-value"><?php echo esc_html( $error_stats['errors_today'] ?? 0 ); ?></span>
									<span class="ta-stat-label"><?php esc_html_e( 'Errors Today', 'third-audience' ); ?></span>
								</div>
								<div class="ta-stat">
									<span class="ta-stat-value"><?php echo esc_html( $error_stats['total_errors'] ?? 0 ); ?></span>
									<span class="ta-stat-label"><?php esc_html_e( 'Total Errors', 'third-audience' ); ?></span>
								</div>
							</div>
							<?php if ( ! empty( $error_stats['last_error'] ) ) : ?>
								<p class="ta-last-error">
									<strong><?php esc_html_e( 'Last Error:', 'third-audience' ); ?></strong><br />
									<?php echo esc_html( $error_stats['last_error'] ); ?>
								</p>
							<?php endif; ?>
						</div>

						<!-- Plugin Info Card -->
						<div class="ta-card">
							<h2><?php esc_html_e( 'Plugin Information', 'third-audience' ); ?></h2>
							<table class="ta-info-table">
								<tr>
									<td><?php esc_html_e( 'Version:', 'third-audience' ); ?></td>
									<td><?php echo esc_html( TA_VERSION ); ?></td>
								</tr>
								<tr>
									<td><?php esc_html_e( 'DB Version:', 'third-audience' ); ?></td>
									<td><?php echo esc_html( get_option( 'ta_db_version', '1.0.0' ) ); ?></td>
								</tr>
								<tr>
									<td><?php esc_html_e( 'PHP Version:', 'third-audience' ); ?></td>
									<td><?php echo esc_html( PHP_VERSION ); ?></td>
								</tr>
								<tr>
									<td><?php esc_html_e( 'WordPress:', 'third-audience' ); ?></td>
									<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
								</tr>
							</table>
						</div>

						<!-- Health Check Card -->
						<div class="ta-card">
							<h2><?php esc_html_e( 'Health Check', 'third-audience' ); ?></h2>
							<p><?php esc_html_e( 'REST API endpoint for monitoring:', 'third-audience' ); ?></p>
							<code class="ta-url-display"><?php echo esc_html( rest_url( 'third-audience/v1/health' ) ); ?></code>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>

<style>
/* Third Audience Admin Styles */
.ta-settings-wrap {
	max-width: 1400px;
}

.ta-nav-tabs {
	margin-bottom: 20px;
}

.ta-admin-container {
	margin-top: 20px;
}

.ta-settings-layout {
	display: flex;
	gap: 20px;
	flex-wrap: wrap;
}

.ta-settings-main {
	flex: 2;
	min-width: 500px;
}

.ta-settings-sidebar {
	flex: 1;
	min-width: 300px;
	max-width: 400px;
}

.ta-card {
	background: #fff;
	border: 1px solid #c3c4c7;
	border-radius: 4px;
	padding: 20px;
	margin-bottom: 20px;
	box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.ta-card h2 {
	margin-top: 0;
	padding-bottom: 10px;
	border-bottom: 1px solid #eee;
}

.ta-card-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 15px;
}

.ta-card-header h2 {
	margin: 0;
	padding: 0;
	border: none;
}

.ta-checkbox-label {
	display: block;
	margin-bottom: 8px;
}

.ta-stat-grid {
	display: grid;
	grid-template-columns: repeat(2, 1fr);
	gap: 15px;
	margin-bottom: 15px;
}

.ta-stat {
	text-align: center;
	padding: 15px;
	background: #f6f7f7;
	border-radius: 4px;
}

.ta-stat-value {
	display: block;
	font-size: 24px;
	font-weight: 600;
	color: #1d2327;
}

.ta-stat-label {
	display: block;
	font-size: 12px;
	color: #50575e;
	margin-top: 5px;
}

.ta-steps-list {
	padding-left: 20px;
	margin: 0;
}

.ta-steps-list li {
	margin-bottom: 8px;
}

.ta-url-display {
	display: block;
	padding: 8px;
	background: #f6f7f7;
	border-radius: 3px;
	word-break: break-all;
	font-size: 12px;
}

.ta-form-inline {
	margin-top: 10px;
}

.ta-info-table {
	width: 100%;
}

.ta-info-table td {
	padding: 5px 0;
}

.ta-info-table td:first-child {
	font-weight: 500;
	width: 45%;
}

.ta-status-badge {
	display: inline-block;
	padding: 2px 8px;
	border-radius: 3px;
	font-size: 12px;
	background: #f0f0f1;
	color: #50575e;
}

.ta-status-success {
	background: #d4edda;
	color: #155724;
}

.ta-status-warning {
	background: #fff3cd;
	color: #856404;
}

.ta-status-error {
	background: #f8d7da;
	color: #721c24;
}

/* Error Log Styles */
.ta-errors-table {
	margin-top: 10px;
}

.ta-errors-table th {
	text-align: left;
}

.ta-error-time {
	white-space: nowrap;
	width: 150px;
}

.ta-error-level {
	width: 80px;
}

.ta-level-badge {
	display: inline-block;
	padding: 2px 8px;
	border-radius: 3px;
	font-size: 11px;
	font-weight: 500;
	text-transform: uppercase;
}

.ta-level-error {
	background: #f8d7da;
	color: #721c24;
}

.ta-level-critical {
	background: #721c24;
	color: #fff;
}

.ta-level-warning {
	background: #fff3cd;
	color: #856404;
}

.ta-level-info {
	background: #d1ecf1;
	color: #0c5460;
}

.ta-level-debug {
	background: #f0f0f1;
	color: #50575e;
}

.ta-error-context {
	margin-top: 8px;
}

.ta-error-context summary {
	cursor: pointer;
	font-size: 12px;
	color: #2271b1;
}

.ta-error-context pre {
	background: #f6f7f7;
	padding: 10px;
	border-radius: 3px;
	font-size: 11px;
	overflow-x: auto;
	margin-top: 5px;
}

.ta-empty-state {
	text-align: center;
	padding: 40px 20px;
	color: #50575e;
}

.ta-empty-state .dashicons {
	font-size: 48px;
	width: 48px;
	height: 48px;
	color: #00a32a;
}

.ta-last-error {
	font-size: 12px;
	color: #50575e;
	margin-top: 10px;
	padding-top: 10px;
	border-top: 1px solid #eee;
}

/* Responsive */
@media screen and (max-width: 782px) {
	.ta-settings-layout {
		flex-direction: column;
	}

	.ta-settings-main,
	.ta-settings-sidebar {
		min-width: 100%;
		max-width: 100%;
	}
}
</style>
