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
	'headless'      => __( 'Headless Setup', 'third-audience' ),
	'webhooks'      => __( 'Webhooks', 'third-audience' ),
	'ga4'           => __( 'GA4 Integration', 'third-audience' ),
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
									<tr>
										<th scope="row">
											<label for="ta_homepage_md_pattern">
												<?php esc_html_e( 'Homepage Markdown Pattern', 'third-audience' ); ?>
											</label>
										</th>
										<td>
											<?php
											$current_pattern = get_option( 'ta_homepage_md_pattern', 'index.md' );
											$custom_pattern = get_option( 'ta_homepage_md_pattern_custom', '' );
											?>
											<select name="ta_homepage_md_pattern" id="ta_homepage_md_pattern" class="regular-text">
												<option value="index.md" <?php selected( $current_pattern, 'index.md' ); ?>><?php esc_html_e( 'index.md (Recommended)', 'third-audience' ); ?></option>
												<option value="home.md" <?php selected( $current_pattern, 'home.md' ); ?>><?php esc_html_e( 'home.md', 'third-audience' ); ?></option>
												<option value="root.md" <?php selected( $current_pattern, 'root.md' ); ?>><?php esc_html_e( 'root.md', 'third-audience' ); ?></option>
												<option value="custom" <?php selected( $current_pattern, 'custom' ); ?>><?php esc_html_e( 'Custom...', 'third-audience' ); ?></option>
											</select>

											<input type="text"
												   name="ta_homepage_md_pattern_custom"
												   id="ta_homepage_md_pattern_custom"
												   class="regular-text"
												   value="<?php echo esc_attr( $custom_pattern ); ?>"
												   placeholder="<?php esc_attr_e( 'e.g., frontpage.md', 'third-audience' ); ?>"
												   style="display:<?php echo ( 'custom' === $current_pattern ) ? 'inline-block' : 'none'; ?>; margin-top: 5px;" />

											<p class="description">
												<strong><?php esc_html_e( 'Choose how the homepage markdown URL is generated.', 'third-audience' ); ?></strong><br>
												<?php esc_html_e( 'Examples:', 'third-audience' ); ?><br>
												• <code>index.md</code> → <code><?php echo esc_html( home_url( '/index.md' ) ); ?></code><br>
												• <code>home.md</code> → <code><?php echo esc_html( home_url( '/home.md' ) ); ?></code><br>
												• <code><?php esc_html_e( 'Custom: Your own pattern', 'third-audience' ); ?></code>
											</p>
											<p class="description" style="color: #d63638;">
												<strong><?php esc_html_e( 'Important:', 'third-audience' ); ?></strong>
												<?php esc_html_e( 'After changing this setting, go to Settings → Permalinks and click "Save Changes" to flush rewrite rules.', 'third-audience' ); ?>
											</p>

											<!-- Live Preview & Validation -->
											<div style="margin-top: 15px; padding: 12px; background: #f0f6fc; border: 1px solid #d0e4f5; border-radius: 4px;">
												<h4 style="margin: 0 0 10px 0; font-size: 13px; color: #0073aa;">
													<span class="dashicons dashicons-visibility" style="font-size: 16px; vertical-align: middle;"></span>
													<?php esc_html_e( 'Live Preview & Validation', 'third-audience' ); ?>
												</h4>

												<?php
												// Calculate current homepage markdown URL
												$preview_pattern = $current_pattern;
												if ( $preview_pattern === 'custom' && ! empty( $custom_pattern ) ) {
													$preview_pattern = $custom_pattern;
												}
												// Ensure .md extension
												if ( substr( $preview_pattern, -3 ) !== '.md' ) {
													$preview_pattern .= '.md';
												}
												$preview_url = trailingslashit( home_url() ) . $preview_pattern;
												?>

												<p style="margin: 0 0 8px 0;">
													<strong><?php esc_html_e( 'Your homepage markdown URL:', 'third-audience' ); ?></strong><br>
													<code class="ta-homepage-preview-url" style="font-size: 13px; background: white; padding: 4px 8px; border-radius: 3px; display: inline-block; margin-top: 5px;">
														<?php echo esc_html( $preview_url ); ?>
													</code>
													<a href="<?php echo esc_url( $preview_url ); ?>"
													   target="_blank"
													   rel="noopener"
													   class="button button-secondary ta-homepage-test-link"
													   style="margin-left: 8px; vertical-align: middle;">
														<span class="dashicons dashicons-external" style="font-size: 16px; vertical-align: middle; margin-top: 3px;"></span>
														<?php esc_html_e( 'Test URL', 'third-audience' ); ?>
													</a>
												</p>

												<p style="margin: 8px 0 0 0; font-size: 12px; color: #646970;">
													<span class="dashicons dashicons-info" style="font-size: 14px; vertical-align: middle;"></span>
													<?php esc_html_e( 'Click "Test URL" to open the markdown version in a new tab and verify it works correctly.', 'third-audience' ); ?>
												</p>
											</div>
										</td>
									</tr>
								</table>
							</div>

							<div class="ta-card">
								<h2><?php esc_html_e( 'AI-Optimized Metadata', 'third-audience' ); ?></h2>
								<p class="description" style="margin-bottom: 15px;">
									<?php esc_html_e( 'Add enhanced metadata to markdown frontmatter to help AI agents better understand and process your content.', 'third-audience' ); ?>
								</p>
								<table class="form-table" role="presentation">
									<tr>
										<th scope="row"><?php esc_html_e( 'Enable Enhanced Metadata', 'third-audience' ); ?></th>
										<td>
											<label class="ta-checkbox-label">
												<input type="checkbox" name="ta_enable_enhanced_metadata" value="1"
													   <?php checked( get_option( 'ta_enable_enhanced_metadata', true ) ); ?> />
												<?php esc_html_e( 'Enable AI-optimized metadata in markdown frontmatter', 'third-audience' ); ?>
											</label>
											<p class="description"><?php esc_html_e( 'Master switch for all enhanced metadata features below.', 'third-audience' ); ?></p>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Metadata Fields', 'third-audience' ); ?></th>
										<td>
											<fieldset>
												<label class="ta-checkbox-label" style="display: block; margin-bottom: 8px;">
													<input type="checkbox" name="ta_metadata_word_count" value="1"
														   <?php checked( get_option( 'ta_metadata_word_count', true ) ); ?> />
													<strong><?php esc_html_e( 'Word Count', 'third-audience' ); ?></strong>
													<span class="description" style="display: block; margin-left: 24px;">
														<?php esc_html_e( 'Total number of words in the content', 'third-audience' ); ?>
													</span>
												</label>

												<label class="ta-checkbox-label" style="display: block; margin-bottom: 8px;">
													<input type="checkbox" name="ta_metadata_reading_time" value="1"
														   <?php checked( get_option( 'ta_metadata_reading_time', true ) ); ?> />
													<strong><?php esc_html_e( 'Reading Time', 'third-audience' ); ?></strong>
													<span class="description" style="display: block; margin-left: 24px;">
														<?php esc_html_e( 'Estimated reading time based on 200 words/minute', 'third-audience' ); ?>
													</span>
												</label>

												<label class="ta-checkbox-label" style="display: block; margin-bottom: 8px;">
													<input type="checkbox" name="ta_metadata_summary" value="1"
														   <?php checked( get_option( 'ta_metadata_summary', true ) ); ?> />
													<strong><?php esc_html_e( 'Summary', 'third-audience' ); ?></strong>
													<span class="description" style="display: block; margin-left: 24px;">
														<?php esc_html_e( 'Post excerpt or first paragraph (max 200 characters)', 'third-audience' ); ?>
													</span>
												</label>

												<label class="ta-checkbox-label" style="display: block; margin-bottom: 8px;">
													<input type="checkbox" name="ta_metadata_language" value="1"
														   <?php checked( get_option( 'ta_metadata_language', true ) ); ?> />
													<strong><?php esc_html_e( 'Language', 'third-audience' ); ?></strong>
													<span class="description" style="display: block; margin-left: 24px;">
														<?php esc_html_e( 'Content language from WordPress locale', 'third-audience' ); ?>
													</span>
												</label>

												<label class="ta-checkbox-label" style="display: block; margin-bottom: 8px;">
													<input type="checkbox" name="ta_metadata_last_modified" value="1"
														   <?php checked( get_option( 'ta_metadata_last_modified', true ) ); ?> />
													<strong><?php esc_html_e( 'Last Modified Date', 'third-audience' ); ?></strong>
													<span class="description" style="display: block; margin-left: 24px;">
														<?php esc_html_e( 'ISO 8601 formatted date when content was last updated', 'third-audience' ); ?>
													</span>
												</label>

												<label class="ta-checkbox-label" style="display: block; margin-bottom: 8px;">
													<input type="checkbox" name="ta_metadata_schema_type" value="1"
														   <?php checked( get_option( 'ta_metadata_schema_type', true ) ); ?> />
													<strong><?php esc_html_e( 'Schema Type', 'third-audience' ); ?></strong>
													<span class="description" style="display: block; margin-left: 24px;">
														<?php esc_html_e( 'Schema.org type (Article for posts, WebPage for pages)', 'third-audience' ); ?>
													</span>
												</label>

												<label class="ta-checkbox-label" style="display: block; margin-bottom: 8px;">
													<input type="checkbox" name="ta_metadata_related_posts" value="1"
														   <?php checked( get_option( 'ta_metadata_related_posts', true ) ); ?> />
													<strong><?php esc_html_e( 'Related Posts', 'third-audience' ); ?></strong>
													<span class="description" style="display: block; margin-left: 24px;">
														<?php esc_html_e( 'Up to 3 related posts by category and tags', 'third-audience' ); ?>
													</span>
												</label>
											</fieldset>
											<p class="description" style="margin-top: 12px;">
												<strong><?php esc_html_e( 'Example frontmatter output:', 'third-audience' ); ?></strong><br>
												<code style="display: block; margin-top: 8px; padding: 12px; background: #f6f7f7; border-left: 4px solid #0073aa;">
													word_count: 1250<br>
													reading_time: "7 min read"<br>
													summary: "This is a brief summary of the article content..."<br>
													language: "en"<br>
													last_modified: "2025-01-21T10:30:00+00:00"<br>
													schema_type: "Article"<br>
													related_posts:<br>
													&nbsp;&nbsp;- title: "Related Article 1"<br>
													&nbsp;&nbsp;&nbsp;&nbsp;url: "https://example.com/article1"
												</code>
											</p>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Regenerate Markdown', 'third-audience' ); ?></th>
										<td>
											<button type="button" id="ta-regenerate-all-markdown" class="button button-secondary">
												<?php esc_html_e( 'Regenerate All Markdown', 'third-audience' ); ?>
											</button>
											<p class="description" style="margin-top: 8px;">
												<?php esc_html_e( 'Clear all pre-generated markdown to force regeneration with current metadata settings. Use this after changing metadata options above.', 'third-audience' ); ?>
											</p>
											<div id="ta-regenerate-markdown-result" style="margin-top: 10px;"></div>
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
								$permalink = get_permalink( $sample_post[0] );
								if ( $permalink && is_string( $permalink ) ) :
									$md_url = untrailingslashit( $permalink ) . '.md';
									?>
								<p>
									<a href="<?php echo esc_url( $md_url ); ?>" target="_blank" class="button button-secondary">
										<?php esc_html_e( 'View Sample .md', 'third-audience' ); ?>
									</a>
								</p>
								<p class="description">
									<code class="ta-url-display"><?php echo esc_html( $md_url ); ?></code>
								</p>
							<?php
								else :
									?>
									<p class="description"><?php esc_html_e( 'Could not generate sample URL.', 'third-audience' ); ?></p>
									<?php
								endif;
							else :
								?>
								<p class="description"><?php esc_html_e( 'No published posts found.', 'third-audience' ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>

		<?php elseif ( 'headless' === $current_tab ) : ?>
			<!-- Headless Setup Tab -->
			<?php require_once TA_PLUGIN_DIR . 'admin/views/headless-setup-tab.php'; ?>

		<?php elseif ( 'webhooks' === $current_tab ) : ?>
			<!-- Webhooks Tab -->
			<div class="ta-tab-content ta-tab-webhooks">
				<div class="ta-settings-layout">
					<div class="ta-settings-main">
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<?php $security->nonce_field( 'save_webhook_settings' ); ?>
							<input type="hidden" name="action" value="ta_save_webhook_settings" />

							<div class="ta-card">
								<h2><?php esc_html_e( 'Webhook Configuration', 'third-audience' ); ?></h2>
								<p class="description">
									<?php esc_html_e( 'Configure webhooks to receive real-time notifications about AI bot visits and events.', 'third-audience' ); ?>
								</p>

								<table class="form-table" role="presentation">
									<tr>
										<th scope="row"><?php esc_html_e( 'Enable Webhooks', 'third-audience' ); ?></th>
										<td>
											<label class="ta-checkbox-label">
												<input type="checkbox" name="ta_webhooks_enabled" value="1"
													   <?php checked( get_option( 'ta_webhooks_enabled', false ) ); ?> />
												<?php esc_html_e( 'Enable webhook notifications', 'third-audience' ); ?>
											</label>
											<p class="description"><?php esc_html_e( 'Check this box to enable webhooks. You must also configure a webhook URL below.', 'third-audience' ); ?></p>
										</td>
									</tr>
									<tr>
										<th scope="row">
											<label for="ta_webhook_url"><?php esc_html_e( 'Webhook URL', 'third-audience' ); ?></label>
										</th>
										<td>
											<input type="url" name="ta_webhook_url" id="ta_webhook_url" class="regular-text"
												   value="<?php echo esc_attr( get_option( 'ta_webhook_url', '' ) ); ?>"
												   placeholder="https://example.com/webhook" />
											<p class="description">
												<?php esc_html_e( 'The URL where webhooks will be sent. Should be a valid HTTPS endpoint.', 'third-audience' ); ?>
											</p>
										</td>
									</tr>
								</table>

								<?php submit_button( __( 'Save Webhook Settings', 'third-audience' ) ); ?>
							</div>
						</form>

						<div class="ta-card">
							<h2><?php esc_html_e( 'Webhook Events', 'third-audience' ); ?></h2>
							<p class="description"><?php esc_html_e( 'The following events will trigger webhooks when enabled:', 'third-audience' ); ?></p>

							<table class="ta-info-table" style="margin-top: 15px;">
								<tr>
									<td style="font-weight: 600; width: 30%;">markdown.accessed</td>
									<td><?php esc_html_e( 'Fired when a bot accesses markdown content', 'third-audience' ); ?></td>
								</tr>
								<tr style="background: #f9f9f9;">
									<td colspan="2">
										<code style="font-size: 12px; background: #f6f7f7; padding: 4px 8px; display: inline-block;">
											bot_type, bot_name, url, post_id, post_title, cache_status, response_time
										</code>
									</td>
								</tr>
								<tr style="height: 10px;"></tr>
								<tr>
									<td style="font-weight: 600;">bot.detected</td>
									<td><?php esc_html_e( 'Fired when a new bot visits for the first time (once per 24 hours per bot)', 'third-audience' ); ?></td>
								</tr>
								<tr style="background: #f9f9f9;">
									<td colspan="2">
										<code style="font-size: 12px; background: #f6f7f7; padding: 4px 8px; display: inline-block;">
											bot_type, bot_name, bot_color
										</code>
									</td>
								</tr>
							</table>

							<h3 style="margin-top: 20px; margin-bottom: 10px;"><?php esc_html_e( 'Example Webhook Payload', 'third-audience' ); ?></h3>
							<pre style="background: #f6f7f7; padding: 12px; border-radius: 4px; overflow-x: auto; font-size: 12px;"><code>{
  "event": "markdown.accessed",
  "timestamp": "2025-01-21T10:30:00+00:00",
  "site_url": "https://example.com",
  "data": {
    "bot_type": "GPTBot",
    "bot_name": "GPT (OpenAI)",
    "url": "https://example.com/my-post.md",
    "post_id": 123,
    "post_title": "My Article",
    "cache_status": "MISS",
    "response_time": 245
  }
}</code></pre>
						</div>

						<div class="ta-card">
							<h2><?php esc_html_e( 'Security Notes', 'third-audience' ); ?></h2>
							<ul style="padding-left: 20px;">
								<li><?php esc_html_e( 'Always use HTTPS for webhook URLs', 'third-audience' ); ?></li>
								<li><?php esc_html_e( 'Verify the origin of webhook requests (User-Agent header contains "Third Audience")', 'third-audience' ); ?></li>
								<li><?php esc_html_e( 'Implement request timeout handling (webhooks timeout after 10 seconds)', 'third-audience' ); ?></li>
								<li><?php esc_html_e( 'The webhook URL is stored unencrypted - keep it secret like a password', 'third-audience' ); ?></li>
							</ul>
						</div>
					</div>

					<div class="ta-settings-sidebar">
						<!-- Test Webhook Card -->
						<div class="ta-card">
							<h2><?php esc_html_e( 'Test Webhook', 'third-audience' ); ?></h2>
							<p><?php esc_html_e( 'Send a test webhook to your endpoint.', 'third-audience' ); ?></p>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ta-form-inline">
								<?php $security->nonce_field( 'test_webhook' ); ?>
								<input type="hidden" name="action" value="ta_test_webhook" />
								<button type="submit" class="button button-secondary" id="ta-test-webhook-btn">
									<?php esc_html_e( 'Send Test Webhook', 'third-audience' ); ?>
								</button>
							</form>
							<div id="ta-webhook-test-result" style="margin-top: 15px; display: none;">
								<div id="ta-webhook-test-message" style="padding: 10px; border-radius: 4px;"></div>
							</div>
						</div>

						<!-- Webhook Status Card -->
						<div class="ta-card">
							<h2><?php esc_html_e( 'Webhook Status', 'third-audience' ); ?></h2>
							<table class="ta-info-table">
								<tr>
									<td><?php esc_html_e( 'Status:', 'third-audience' ); ?></td>
									<td>
										<?php if ( get_option( 'ta_webhooks_enabled', false ) ) : ?>
											<span class="ta-status-badge ta-status-success"><?php esc_html_e( 'Enabled', 'third-audience' ); ?></span>
										<?php else : ?>
											<span class="ta-status-badge"><?php esc_html_e( 'Disabled', 'third-audience' ); ?></span>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<td><?php esc_html_e( 'Webhook URL:', 'third-audience' ); ?></td>
									<td>
										<?php
										$webhook_url = get_option( 'ta_webhook_url', '' );
										if ( ! empty( $webhook_url ) ) {
											echo '<code style="word-break: break-all; font-size: 11px;">' . esc_html( $webhook_url ) . '</code>';
										} else {
											echo '<span class="ta-status-badge ta-status-warning">' . esc_html__( 'Not configured', 'third-audience' ) . '</span>';
										}
										?>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>

		<?php elseif ( 'ga4' === $current_tab ) : ?>
			<!-- GA4 Integration Tab -->
			<div class="ta-tab-content ta-tab-ga4">
				<div class="ta-settings-layout">
					<div class="ta-settings-main">
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<?php $security->nonce_field( 'save_ga4_settings' ); ?>
							<input type="hidden" name="action" value="ta_save_ga4_settings" />

							<div class="ta-card">
								<h2><?php esc_html_e( 'Google Analytics 4 Integration', 'third-audience' ); ?></h2>
								<p class="description">
									<?php esc_html_e( 'Send AI bot traffic data to Google Analytics 4 using the Measurement Protocol. Track bot crawls, AI citations, and session metrics in your GA4 dashboard.', 'third-audience' ); ?>
								</p>

								<?php
								$ga4_settings = array();
								if ( class_exists( 'TA_GA4_Integration' ) ) {
									$ga4 = TA_GA4_Integration::get_instance();
									$ga4_settings = $ga4->get_settings();
								}
								?>

								<table class="form-table" role="presentation">
									<tr>
										<th scope="row"><?php esc_html_e( 'Enable GA4 Integration', 'third-audience' ); ?></th>
										<td>
											<label class="ta-checkbox-label">
												<input type="checkbox" name="ta_ga4[enabled]" value="1"
													   <?php checked( ! empty( $ga4_settings['enabled'] ) ); ?> />
												<?php esc_html_e( 'Send bot traffic data to Google Analytics 4', 'third-audience' ); ?>
											</label>
											<p class="description">
												<?php esc_html_e( 'Events are sent asynchronously in the background without affecting page load performance.', 'third-audience' ); ?>
											</p>
										</td>
									</tr>

									<tr>
										<th scope="row">
											<label for="ta_ga4_measurement_id"><?php esc_html_e( 'Measurement ID', 'third-audience' ); ?></label>
										</th>
										<td>
											<input type="text" name="ta_ga4[measurement_id]" id="ta_ga4_measurement_id" class="regular-text"
												   value="<?php echo esc_attr( $ga4_settings['measurement_id'] ?? '' ); ?>"
												   placeholder="G-XXXXXXXXXX" />
											<p class="description">
												<?php
												printf(
													/* translators: %s: Link to GA4 admin */
													esc_html__( 'Find this in your GA4 property settings under Admin > Data Streams. Format: G-XXXXXXXXXX', 'third-audience' )
												);
												?>
											</p>
										</td>
									</tr>

									<tr>
										<th scope="row">
											<label for="ta_ga4_api_secret"><?php esc_html_e( 'API Secret', 'third-audience' ); ?></label>
										</th>
										<td>
											<input type="text" name="ta_ga4[api_secret]" id="ta_ga4_api_secret" class="regular-text"
												   value="<?php echo esc_attr( $ga4_settings['api_secret'] ?? '' ); ?>"
												   placeholder="<?php esc_attr_e( 'Your API Secret', 'third-audience' ); ?>" />
											<p class="description">
												<?php esc_html_e( 'Create this in GA4: Admin > Data Streams > Select Stream > Measurement Protocol API secrets > Create', 'third-audience' ); ?>
											</p>
										</td>
									</tr>

									<tr>
										<th scope="row"><?php esc_html_e( 'Test Connection', 'third-audience' ); ?></th>
										<td>
											<button type="button" id="ta-test-ga4-btn" class="button button-secondary">
												<?php esc_html_e( 'Test Connection', 'third-audience' ); ?>
											</button>
											<span id="ta-ga4-test-result"></span>
											<p class="description">
												<?php esc_html_e( 'Verify your Measurement ID and API Secret are correct before saving.', 'third-audience' ); ?>
											</p>
										</td>
									</tr>
								</table>

								<p class="submit">
									<input type="submit" name="submit" id="submit" class="button button-primary"
										   value="<?php esc_attr_e( 'Save GA4 Settings', 'third-audience' ); ?>" />
								</p>
							</div>
						</form>

						<!-- Events Tracked -->
						<div class="ta-card">
							<h2><?php esc_html_e( 'Events Tracked', 'third-audience' ); ?></h2>
							<p class="description">
								<?php esc_html_e( 'The following events are automatically sent to GA4 when enabled:', 'third-audience' ); ?>
							</p>

							<table class="widefat">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Event Name', 'third-audience' ); ?></th>
										<th><?php esc_html_e( 'Description', 'third-audience' ); ?></th>
										<th><?php esc_html_e( 'Parameters', 'third-audience' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td><code>bot_crawl</code></td>
										<td><?php esc_html_e( 'Triggered when an AI bot accesses markdown content', 'third-audience' ); ?></td>
										<td>
											<code>bot_name</code>, <code>bot_type</code>, <code>url</code>, <code>cache_status</code>,
											<code>response_time</code>, <code>ai_score</code>, <code>content_type</code>
										</td>
									</tr>
									<tr>
										<td><code>ai_citation_click</code></td>
										<td><?php esc_html_e( 'Triggered when a user clicks through from an AI platform', 'third-audience' ); ?></td>
										<td>
											<code>platform</code>, <code>search_query</code>, <code>url</code>, <code>citation_rate</code>
										</td>
									</tr>
									<tr>
										<td><code>bot_session</code></td>
										<td><?php esc_html_e( 'Session summary for bot visits', 'third-audience' ); ?></td>
										<td>
											<code>bot_name</code>, <code>pages_per_session</code>, <code>session_duration</code>
										</td>
									</tr>
								</tbody>
							</table>
						</div>

						<?php if ( class_exists( 'TA_GA4_Integration' ) && ! empty( $ga4_settings['enabled'] ) ) : ?>
							<!-- Sync Statistics -->
							<?php
							$ga4 = TA_GA4_Integration::get_instance();
							$sync_stats = $ga4->get_sync_stats();
							?>
							<div class="ta-card">
								<h2><?php esc_html_e( 'Sync Statistics', 'third-audience' ); ?></h2>
								<table class="widefat">
									<tbody>
										<tr>
											<th><?php esc_html_e( 'Total Events Sent', 'third-audience' ); ?></th>
											<td><?php echo esc_html( number_format_i18n( $sync_stats['total_events_sent'] ) ); ?></td>
										</tr>
										<tr>
											<th><?php esc_html_e( 'Successful Syncs', 'third-audience' ); ?></th>
											<td><?php echo esc_html( number_format_i18n( $sync_stats['success_count'] ) ); ?></td>
										</tr>
										<tr>
											<th><?php esc_html_e( 'Failed Syncs', 'third-audience' ); ?></th>
											<td><?php echo esc_html( number_format_i18n( $sync_stats['error_count'] ) ); ?></td>
										</tr>
										<tr>
											<th><?php esc_html_e( 'Last Sync', 'third-audience' ); ?></th>
											<td>
												<?php
												if ( ! empty( $sync_stats['last_sync_time'] ) ) {
													echo esc_html( $sync_stats['last_sync_time'] );
												} else {
													esc_html_e( 'No syncs yet', 'third-audience' );
												}
												?>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						<?php endif; ?>
					</div>

					<div class="ta-settings-sidebar">
						<div class="ta-card">
							<h3><?php esc_html_e( 'GA4 Integration Guide', 'third-audience' ); ?></h3>
							<ol>
								<li><?php esc_html_e( 'Log in to your Google Analytics 4 account', 'third-audience' ); ?></li>
								<li><?php esc_html_e( 'Go to Admin > Data Streams', 'third-audience' ); ?></li>
								<li><?php esc_html_e( 'Select your web data stream', 'third-audience' ); ?></li>
								<li><?php esc_html_e( 'Copy the Measurement ID (G-XXXXXXXXXX)', 'third-audience' ); ?></li>
								<li><?php esc_html_e( 'Go to Measurement Protocol API secrets', 'third-audience' ); ?></li>
								<li><?php esc_html_e( 'Create a new API secret and copy it', 'third-audience' ); ?></li>
								<li><?php esc_html_e( 'Paste both values above and test the connection', 'third-audience' ); ?></li>
								<li><?php esc_html_e( 'Save your settings to start sending events', 'third-audience' ); ?></li>
							</ol>
						</div>

						<div class="ta-card">
							<h3><?php esc_html_e( 'Viewing Your Data', 'third-audience' ); ?></h3>
							<p>
								<?php esc_html_e( 'Once configured, bot traffic data will appear in GA4 under:', 'third-audience' ); ?>
							</p>
							<ul>
								<li><strong><?php esc_html_e( 'Reports > Engagement > Events', 'third-audience' ); ?></strong></li>
								<li><strong><?php esc_html_e( 'Explore > Free Form', 'third-audience' ); ?></strong></li>
							</ul>
							<p>
								<?php esc_html_e( 'Look for events: bot_crawl, ai_citation_click, and bot_session.', 'third-audience' ); ?>
							</p>
						</div>

						<div class="ta-card">
							<h3><?php esc_html_e( 'Privacy Note', 'third-audience' ); ?></h3>
							<p>
								<?php esc_html_e( 'Bot traffic data is anonymized using hashed client IDs. No personally identifiable information is sent to Google Analytics.', 'third-audience' ); ?>
							</p>
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
								<div class="ta-header-actions">
									<?php if ( ! empty( $recent_errors ) ) : ?>
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ta_export_errors' ), 'ta_export_errors', '_wpnonce' ) ); ?>" class="button button-secondary" id="ta-export-errors-btn">
										<span class="dashicons dashicons-download" style="vertical-align: middle; margin-right: 4px;"></span>
										<?php esc_html_e( 'Export Logs', 'third-audience' ); ?>
									</a>
									<?php endif; ?>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ta-form-inline" style="display: inline;">
										<?php $security->nonce_field( 'clear_errors' ); ?>
										<input type="hidden" name="action" value="ta_clear_errors" />
										<button type="submit" class="button button-secondary" id="ta-clear-errors-btn">
											<?php esc_html_e( 'Clear Errors', 'third-audience' ); ?>
										</button>
									</form>
								</div>
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

<?php
// Settings page CSS is loaded via wp_enqueue_style in TA_Admin.
?>
