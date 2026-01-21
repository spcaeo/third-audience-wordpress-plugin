<?php
/**
 * Headless Setup Tab - Configuration wizard for headless WordPress setups.
 *
 * @package ThirdAudience
 * @since   1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Initialize headless wizard.
$wizard = new TA_Headless_Wizard();
$settings = $wizard->get_settings();
$detection = $wizard->detect_headless_mode();

// Security instance for nonces.
$security = TA_Security::get_instance();
?>

<div class="ta-tab-content ta-tab-headless">
	<div class="ta-headless-wizard">

		<!-- Introduction -->
		<div class="ta-card">
			<h2><?php esc_html_e( 'Headless WordPress Setup', 'third-audience' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'If you\'re using WordPress as a headless CMS with a separate frontend (Next.js, Nuxt, Gatsby, etc.), use this wizard to configure Third Audience to work seamlessly with your setup.', 'third-audience' ); ?>
			</p>
		</div>

		<!-- Auto-Detection -->
		<div class="ta-card">
			<h2>
				<span class="dashicons dashicons-search"></span>
				<?php esc_html_e( 'Auto-Detection', 'third-audience' ); ?>
			</h2>

			<?php if ( $detection['is_headless'] ) : ?>
				<div class="notice notice-success inline">
					<p>
						<span class="dashicons dashicons-yes-alt"></span>
						<strong><?php esc_html_e( 'Headless WordPress Detected!', 'third-audience' ); ?></strong>
						<?php
						printf(
							/* translators: %s: confidence level */
							esc_html__( 'Confidence: %s', 'third-audience' ),
							'<span class="ta-confidence-' . esc_attr( $detection['confidence'] ) . '">' . esc_html( ucfirst( $detection['confidence'] ) ) . '</span>'
						);
						?>
					</p>
				</div>

				<div class="ta-detection-details">
					<h4><?php esc_html_e( 'Detection Indicators:', 'third-audience' ); ?></h4>
					<ul>
						<?php if ( $detection['indicators']['rest_api_heavy'] ) : ?>
							<li>✓ <?php esc_html_e( 'High REST API usage detected', 'third-audience' ); ?></li>
						<?php endif; ?>
						<?php if ( $detection['indicators']['headless_theme'] ) : ?>
							<li>✓ <?php esc_html_e( 'Headless theme detected', 'third-audience' ); ?></li>
						<?php endif; ?>
						<?php if ( $detection['indicators']['headless_plugins'] ) : ?>
							<li>✓ <?php
							printf(
								/* translators: %s: plugin name */
								esc_html__( 'Headless plugin detected: %s', 'third-audience' ),
								'<code>' . esc_html( $detection['indicators']['detected_plugin'] ?? 'WPGraphQL' ) . '</code>'
							);
							?></li>
						<?php endif; ?>
						<?php if ( $detection['indicators']['separate_frontend'] ) : ?>
							<li>✓ <?php esc_html_e( 'Separate frontend URL configured', 'third-audience' ); ?></li>
						<?php endif; ?>
					</ul>
				</div>
			<?php else : ?>
				<div class="notice notice-info inline">
					<p>
						<span class="dashicons dashicons-info"></span>
						<?php esc_html_e( 'No headless setup detected. If you\'re using a headless frontend, configure it below.', 'third-audience' ); ?>
					</p>
				</div>
			<?php endif; ?>
		</div>

		<!-- Configuration Form -->
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php $security->nonce_field( 'save_headless_settings' ); ?>
			<input type="hidden" name="action" value="ta_save_headless_settings" />

			<div class="ta-card">
				<h2><?php esc_html_e( 'Headless Configuration', 'third-audience' ); ?></h2>

				<table class="form-table" role="presentation">
					<!-- Enable Headless Mode -->
					<tr>
						<th scope="row">
							<label for="ta_headless_enabled">
								<?php esc_html_e( 'Enable Headless Mode', 'third-audience' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input type="checkbox"
								       name="ta_headless_enabled"
								       id="ta_headless_enabled"
								       value="1"
								       <?php checked( $settings['enabled'], true ); ?> />
								<?php esc_html_e( 'Enable headless WordPress configuration', 'third-audience' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'When enabled, Third Audience will generate configuration snippets for your headless setup.', 'third-audience' ); ?>
							</p>
						</td>
					</tr>

					<!-- Frontend URL -->
					<tr>
						<th scope="row">
							<label for="ta_headless_frontend_url">
								<?php esc_html_e( 'Frontend URL', 'third-audience' ); ?>
								<span class="required">*</span>
							</label>
						</th>
						<td>
							<input type="url"
							       name="ta_headless_frontend_url"
							       id="ta_headless_frontend_url"
							       class="regular-text"
							       value="<?php echo esc_attr( $settings['frontend_url'] ); ?>"
							       placeholder="https://example.com" />
							<p class="description">
								<?php esc_html_e( 'The URL where your frontend application is hosted (e.g., https://example.com).', 'third-audience' ); ?>
							</p>
						</td>
					</tr>

					<!-- Frontend Framework -->
					<tr>
						<th scope="row">
							<label for="ta_headless_framework">
								<?php esc_html_e( 'Frontend Framework', 'third-audience' ); ?>
							</label>
						</th>
						<td>
							<select name="ta_headless_framework" id="ta_headless_framework">
								<option value="nextjs" <?php selected( $settings['framework'], 'nextjs' ); ?>>Next.js</option>
								<option value="nuxtjs" <?php selected( $settings['framework'], 'nuxtjs' ); ?>>Nuxt.js</option>
								<option value="gatsby" <?php selected( $settings['framework'], 'gatsby' ); ?>>Gatsby</option>
								<option value="static" <?php selected( $settings['framework'], 'static' ); ?>>Static HTML</option>
								<option value="other" <?php selected( $settings['framework'], 'other' ); ?>>Other</option>
							</select>
							<p class="description">
								<?php esc_html_e( 'The framework you\'re using for your frontend application.', 'third-audience' ); ?>
							</p>
						</td>
					</tr>

					<!-- Server Type -->
					<tr>
						<th scope="row">
							<label for="ta_headless_server_type">
								<?php esc_html_e( 'Web Server', 'third-audience' ); ?>
							</label>
						</th>
						<td>
							<select name="ta_headless_server_type" id="ta_headless_server_type">
								<option value="nginx" <?php selected( $settings['server_type'], 'nginx' ); ?>>Nginx</option>
								<option value="apache" <?php selected( $settings['server_type'], 'apache' ); ?>>Apache</option>
								<option value="cloudflare" <?php selected( $settings['server_type'], 'cloudflare' ); ?>>Cloudflare Workers</option>
								<option value="vercel" <?php selected( $settings['server_type'], 'vercel' ); ?>>Vercel</option>
							</select>
							<p class="description">
								<?php esc_html_e( 'The web server or platform hosting your application.', 'third-audience' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="submit" class="button button-primary">
						<span class="dashicons dashicons-saved"></span>
						<?php esc_html_e( 'Save Configuration', 'third-audience' ); ?>
					</button>
				</p>
			</div>
		</form>

		<!-- Configuration Snippets -->
		<?php if ( $settings['enabled'] && ! empty( $settings['frontend_url'] ) ) : ?>
			<div class="ta-card">
				<h2>
					<span class="dashicons dashicons-media-code"></span>
					<?php esc_html_e( 'Configuration Snippets', 'third-audience' ); ?>
				</h2>
				<p class="description">
					<?php esc_html_e( 'Copy and apply the appropriate configuration snippet to enable .md URL routing for AI bots.', 'third-audience' ); ?>
				</p>

				<!-- Server Configuration -->
				<div class="ta-config-section">
					<h3>
						<?php
						printf(
							/* translators: %s: server type */
							esc_html__( '%s Configuration', 'third-audience' ),
							esc_html( ucfirst( $settings['server_type'] ) )
						);
						?>
					</h3>
					<pre class="ta-config-snippet"><code><?php
					echo esc_html( $wizard->generate_server_config( $settings['server_type'], array(
						'frontend_url'   => $settings['frontend_url'],
						'wp_backend_url' => site_url(),
					) ) );
					?></code></pre>
					<button type="button" class="button ta-copy-config" data-clipboard-target=".ta-config-snippet code">
						<span class="dashicons dashicons-clipboard"></span>
						<?php esc_html_e( 'Copy to Clipboard', 'third-audience' ); ?>
					</button>
				</div>

				<!-- Framework-Specific Configuration -->
				<?php if ( 'nextjs' === $settings['framework'] ) : ?>
					<div class="ta-config-section" style="margin-top: 30px;">
						<h3><?php esc_html_e( 'Next.js Rewrites (next.config.js)', 'third-audience' ); ?></h3>
						<pre class="ta-config-snippet-nextjs"><code><?php
						echo esc_html( $wizard->generate_nextjs_config( array(
							'wp_backend_url' => site_url(),
						) ) );
						?></code></pre>
						<button type="button" class="button ta-copy-config-nextjs" data-clipboard-target=".ta-config-snippet-nextjs code">
							<span class="dashicons dashicons-clipboard"></span>
							<?php esc_html_e( 'Copy to Clipboard', 'third-audience' ); ?>
						</button>
					</div>
				<?php endif; ?>
			</div>

			<!-- Test Configuration -->
			<div class="ta-card">
				<h2>
					<span class="dashicons dashicons-yes-alt"></span>
					<?php esc_html_e( 'Test Configuration', 'third-audience' ); ?>
				</h2>
				<p class="description">
					<?php esc_html_e( 'After applying the configuration, test it to ensure .md URLs work correctly.', 'third-audience' ); ?>
				</p>

				<button type="button" class="button button-secondary ta-test-headless-config">
					<span class="dashicons dashicons-cloud"></span>
					<?php esc_html_e( 'Test Headless Configuration', 'third-audience' ); ?>
				</button>

				<div class="ta-test-results" style="margin-top: 20px; display: none;"></div>
			</div>
		<?php endif; ?>

	</div>
</div>

<style>
.ta-headless-wizard {
	max-width: 1200px;
}

.ta-card {
	background: #fff;
	border: 1px solid #ccd0d4;
	border-radius: 4px;
	padding: 20px 30px;
	margin-bottom: 20px;
	box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.ta-card h2 {
	margin-top: 0;
	display: flex;
	align-items: center;
	gap: 10px;
}

.ta-card h2 .dashicons {
	color: #667eea;
}

.ta-detection-details {
	margin-top: 15px;
}

.ta-detection-details ul {
	list-style: none;
	margin: 10px 0;
	padding: 0;
}

.ta-detection-details li {
	padding: 5px 0;
	color: #00a32a;
}

.ta-confidence-high {
	color: #00a32a;
	font-weight: 600;
}

.ta-confidence-medium {
	color: #dba617;
	font-weight: 600;
}

.ta-confidence-low {
	color: #d63638;
	font-weight: 600;
}

.ta-config-snippet,
.ta-config-snippet-nextjs {
	background: #f5f5f5;
	border: 1px solid #ddd;
	border-left: 3px solid #667eea;
	padding: 15px;
	overflow-x: auto;
	margin: 15px 0;
	font-family: Monaco, Consolas, monospace;
	font-size: 13px;
	line-height: 1.6;
}

.ta-copy-config,
.ta-copy-config-nextjs {
	margin-left: 0;
}

.ta-copy-config.copied,
.ta-copy-config-nextjs.copied {
	background: #00a32a;
	border-color: #00a32a;
	color: #fff;
}

.ta-test-results {
	background: #f9f9f9;
	border: 1px solid #ddd;
	border-radius: 4px;
	padding: 15px;
}

.ta-test-results h4 {
	margin-top: 0;
}

.ta-test-results ul {
	list-style: none;
	margin: 0;
	padding: 0;
}

.ta-test-results li {
	padding: 8px 0;
	border-bottom: 1px solid #eee;
}

.ta-test-results li:last-child {
	border-bottom: none;
}

.ta-test-pass {
	color: #00a32a;
}

.ta-test-fail {
	color: #d63638;
}

.required {
	color: #d63638;
}
</style>

<script>
jQuery(document).ready(function($) {
	// Copy to clipboard functionality
	$('.ta-copy-config, .ta-copy-config-nextjs').on('click', function() {
		var button = $(this);
		var target = $(button.data('clipboard-target'));
		var text = target.text();

		// Copy to clipboard
		navigator.clipboard.writeText(text).then(function() {
			button.addClass('copied');
			button.find('.dashicons').removeClass('dashicons-clipboard').addClass('dashicons-yes');
			button.append(' Copied!');

			setTimeout(function() {
				button.removeClass('copied');
				button.find('.dashicons').removeClass('dashicons-yes').addClass('dashicons-clipboard');
				button.find('span:last').remove();
			}, 2000);
		});
	});

	// Test configuration
	$('.ta-test-headless-config').on('click', function() {
		var button = $(this);
		var resultsDiv = $('.ta-test-results');

		button.prop('disabled', true);
		button.find('.dashicons').removeClass('dashicons-cloud').addClass('dashicons-update');

		resultsDiv.html('<p>Testing configuration...</p>').show();

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'ta_test_headless_config',
				_ajax_nonce: '<?php echo esc_js( wp_create_nonce( 'ta_test_headless' ) ); ?>'
			},
			success: function(response) {
				if (response.success) {
					var html = '<h4 style="color: #00a32a;"><span class="dashicons dashicons-yes-alt"></span> Configuration Test Results</h4><ul>';

					$.each(response.data.tests, function(key, test) {
						var icon = test.status ? '✓' : '✗';
						var className = test.status ? 'ta-test-pass' : 'ta-test-fail';
						html += '<li class="' + className + '">' + icon + ' ' + test.label + ': ' + test.message + '</li>';

						if (test.test_url) {
							html += '<li style="margin-left: 20px; color: #666;"><code>' + test.test_url + '</code></li>';
						}
					});

					html += '</ul>';
					resultsDiv.html(html);
				} else {
					resultsDiv.html('<p style="color: #d63638;">Test failed: ' + response.data.message + '</p>');
				}
			},
			error: function() {
				resultsDiv.html('<p style="color: #d63638;">Test failed. Please try again.</p>');
			},
			complete: function() {
				button.prop('disabled', false);
				button.find('.dashicons').removeClass('dashicons-update').addClass('dashicons-cloud');
			}
		});
	});
});
</script>
