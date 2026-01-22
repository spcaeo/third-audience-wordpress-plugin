<?php
/**
 * System Health Page - Display system requirements and library status.
 *
 * @package ThirdAudience
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get system requirements status.
$requirements = TA_Local_Converter::check_system_requirements();

// Get version info.
$update_checker = new TA_Update_Checker();
$version_info   = $update_checker->get_version_info();

// Calculate overall health status.
$has_errors   = false;
$has_warnings = false;

foreach ( $requirements as $check ) {
	if ( 'error' === $check['status'] ) {
		$has_errors = true;
	} elseif ( 'warning' === $check['status'] ) {
		$has_warnings = true;
	}
}

// Determine overall status.
if ( $has_errors ) {
	$overall_status = 'error';
	$overall_message = __( 'Action Required: System requirements are not met. The plugin may not function correctly.', 'third-audience' );
	$overall_class = 'error';
} elseif ( $has_warnings ) {
	$overall_status = 'warning';
	$overall_message = __( 'Warning: Some optional features may be limited.', 'third-audience' );
	$overall_class = 'warning';
} else {
	$overall_status = 'ok';
	$overall_message = __( 'All systems operational! Your plugin is ready to serve markdown content.', 'third-audience' );
	$overall_class = 'ok';
}

// Get library version.
$library_version = TA_Local_Converter::get_library_version();

?>

<div class="wrap ta-system-health">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'System Health', 'third-audience' ); ?>
		<span style="font-size: 0.6em; color: #646970; font-weight: 400;">v<?php echo esc_html( TA_VERSION ); ?></span>
	</h1>

	<p class="description">
		<?php esc_html_e( 'Monitor system requirements, version updates, and diagnostic information.', 'third-audience' ); ?>
	</p>

	<!-- Overall Status Card -->
	<div class="ta-card ta-status-card ta-status-<?php echo esc_attr( $overall_status ); ?>">
		<div class="ta-status-icon">
			<?php if ( 'ok' === $overall_status ) : ?>
				<span class="dashicons dashicons-yes-alt"></span>
			<?php elseif ( 'warning' === $overall_status ) : ?>
				<span class="dashicons dashicons-warning"></span>
			<?php else : ?>
				<span class="dashicons dashicons-dismiss"></span>
			<?php endif; ?>
		</div>
		<div class="ta-status-content">
			<h2><?php esc_html_e( 'System Status', 'third-audience' ); ?></h2>
			<p><?php echo esc_html( $overall_message ); ?></p>
		</div>
	</div>

	<!-- Card Grid: Version & System Info -->
	<div class="ta-cards-container">
	<!-- Version Information -->
	<div class="ta-card">
		<div class="ta-card-header">
			<h2><?php esc_html_e( 'Version Information', 'third-audience' ); ?></h2>
		</div>
		<div class="ta-card-body">
			<div class="ta-info-grid">
				<div class="ta-info-item">
					<span class="ta-info-label"><?php esc_html_e( 'Current Version', 'third-audience' ); ?></span>
					<span class="ta-info-value"><code><?php echo esc_html( $version_info['current_version'] ); ?></code></span>
				</div>
				<div class="ta-info-item">
					<span class="ta-info-label"><?php esc_html_e( 'Latest Version', 'third-audience' ); ?></span>
					<span class="ta-info-value">
						<code><?php echo esc_html( $version_info['latest_version'] ); ?></code>
						<?php if ( $version_info['update_available'] ) : ?>
							<span class="ta-status-badge ta-status-warning">
								<span class="dashicons dashicons-warning"></span>
								<?php esc_html_e( 'Update Available', 'third-audience' ); ?>
							</span>
						<?php else : ?>
							<span class="ta-status-badge ta-status-success">
								<span class="dashicons dashicons-yes-alt"></span>
								<?php esc_html_e( 'Up to date', 'third-audience' ); ?>
							</span>
						<?php endif; ?>
					</span>
				</div>
				<div class="ta-info-item">
					<span class="ta-info-label"><?php esc_html_e( 'Last Checked', 'third-audience' ); ?></span>
					<span class="ta-info-value"><?php echo esc_html( $version_info['last_checked'] ); ?></span>
				</div>
			</div>
			<div class="ta-card-actions">
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ta_check_updates' ), 'ta_check_updates' ) ); ?>"
				   class="button button-secondary">
					<span class="dashicons dashicons-update"></span>
					<?php esc_html_e( 'Check for Updates', 'third-audience' ); ?>
				</a>
				<?php if ( $version_info['update_available'] && ! empty( $version_info['release_url'] ) ) : ?>
					<a href="<?php echo esc_url( $version_info['release_url'] ); ?>"
					   class="button button-primary"
					   target="_blank"
					   rel="noopener">
						<span class="dashicons dashicons-download"></span>
						<?php esc_html_e( 'Download Latest Version', 'third-audience' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Changelog & Version History -->
	<?php if ( ! empty( $version_info['changelog'] ) || $version_info['update_available'] ) : ?>
	<div class="ta-card">
		<div class="ta-card-header">
			<h2>
				<?php esc_html_e( 'What\'s New', 'third-audience' ); ?>
				<?php if ( $version_info['update_available'] ) : ?>
					<span class="ta-version-tag">
						v<?php echo esc_html( $version_info['latest_version'] ); ?>
					</span>
				<?php endif; ?>
			</h2>
		</div>
		<div class="ta-card-body">
			<?php if ( ! empty( $version_info['changelog'] ) ) : ?>
				<div class="ta-changelog">
					<?php echo wp_kses_post( wpautop( $version_info['changelog'] ) ); ?>
				</div>
			<?php else : ?>
				<p class="ta-loading-text"><?php esc_html_e( 'Loading changelog...', 'third-audience' ); ?></p>
			<?php endif; ?>
			<?php if ( $version_info['update_available'] && ! empty( $version_info['release_url'] ) ) : ?>
				<div class="ta-card-actions">
					<a href="<?php echo esc_url( $version_info['release_url'] ); ?>"
					   class="button button-primary"
					   target="_blank"
					   rel="noopener">
						<?php esc_html_e( 'View Full Release Notes on GitHub', 'third-audience' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>

	<!-- Current Version Changelog -->
	<div class="ta-card">
		<div class="ta-card-header">
			<h2>
				<?php esc_html_e( 'Current Version', 'third-audience' ); ?>
				<span class="ta-version-tag ta-version-current">
					v<?php echo esc_html( $version_info['current_version'] ); ?>
				</span>
			</h2>
		</div>
		<div class="ta-card-body">
			<div class="ta-changelog ta-changelog-current">
				<?php
				// Display current version changelog
				$current_changelog = '';
				switch ( $version_info['current_version'] ) {
					case '1.1.1':
						$current_changelog = '
**Bug Fix:**
- Homepage URLs now generate correct .md URLs (e.g., `/index.md` instead of `.md`)

**New Features:**
- Customizable homepage markdown pattern (index.md, home.md, root.md, or custom)
- Automatic fallback to latest post when no static homepage is set

**Improvements:**
- Better URL parsing to handle edge cases
- Settings UI with pattern examples and warnings
- JavaScript for custom pattern toggle';
						break;
					case '1.1.0':
						$current_changelog = '
**Major Features:**
- Headless WordPress Configuration Wizard
- Auto-detect headless setup and generate configuration snippets
- Support for Nginx, Apache, Cloudflare, Vercel, and Next.js
- One-click copy for server configurations

**Performance:**
- 1-hour transient cache for auto-detection
- Rate limiting (3 tests per 5 minutes)
- Filter hooks for extensibility';
						break;
					case '1.0.0':
						$current_changelog = '
**Initial Release:**
- Local HTML to Markdown conversion (no external dependencies!)
- Support for all post types
- Pre-generation on post save
- Bot-specific blocking and analytics
- Cache management
- Discovery tags for AI crawlers';
						break;
					default:
						$current_changelog = esc_html__( 'Version information not available.', 'third-audience' );
				}
				echo wp_kses_post( wpautop( $current_changelog ) );
				?>
			</div>
			<div class="ta-card-actions">
				<a href="https://github.com/spcaeo/third-audience-wordpress-plugin/releases" class="button button-secondary" target="_blank" rel="noopener">
					<?php esc_html_e( 'View All Releases on GitHub', 'third-audience' ); ?>
				</a>
			</div>
		</div>
	</div>
	</div><!-- /ta-cards-container -->

	<!-- Card Grid: Diagnostics & Troubleshooting -->
	<div class="ta-cards-container">
	<!-- System Diagnostics -->
	<div class="ta-card">
		<div class="ta-card-header">
			<h2><?php esc_html_e( 'System Diagnostics', 'third-audience' ); ?></h2>
		</div>
		<div class="ta-card-body">
			<div class="ta-diagnostics-grid">
				<?php foreach ( $requirements as $key => $check ) : ?>
					<div class="ta-diagnostic-item ta-diagnostic-<?php echo esc_attr( $check['status'] ); ?>">
						<div class="ta-diagnostic-icon">
							<?php if ( 'ok' === $check['status'] ) : ?>
								<span class="dashicons dashicons-yes-alt"></span>
							<?php elseif ( 'warning' === $check['status'] ) : ?>
								<span class="dashicons dashicons-warning"></span>
							<?php else : ?>
								<span class="dashicons dashicons-dismiss"></span>
							<?php endif; ?>
						</div>
						<div class="ta-diagnostic-content">
							<h3><?php echo esc_html( ucwords( str_replace( '_', ' ', $key ) ) ); ?></h3>
							<div class="ta-diagnostic-details">
								<div class="ta-diagnostic-row">
									<span class="ta-label"><?php esc_html_e( 'Required:', 'third-audience' ); ?></span>
									<span class="ta-value"><?php echo esc_html( $check['required'] ); ?></span>
								</div>
								<div class="ta-diagnostic-row">
									<span class="ta-label"><?php esc_html_e( 'Current:', 'third-audience' ); ?></span>
									<span class="ta-value"><code><?php echo esc_html( $check['current'] ?? 'N/A' ); ?></code></span>
								</div>
							</div>
							<p class="ta-diagnostic-message"><?php echo esc_html( $check['message'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<!-- Troubleshooting -->
	<?php if ( $has_errors || $has_warnings ) : ?>
		<div class="ta-card ta-troubleshooting-card">
			<div class="ta-card-header">
				<h2><?php esc_html_e( 'Troubleshooting', 'third-audience' ); ?></h2>
			</div>
			<div class="ta-card-body">
				<?php if ( ! TA_Local_Converter::is_library_available() ) : ?>
					<div class="ta-troubleshoot-section ta-troubleshoot-error">
						<div class="ta-troubleshoot-icon">
							<span class="dashicons dashicons-warning"></span>
						</div>
						<div class="ta-troubleshoot-content">
							<h3><?php esc_html_e( 'HTML to Markdown Library Missing', 'third-audience' ); ?></h3>
							<p><?php esc_html_e( 'The required HTML to Markdown conversion library is not installed. This library is essential for the plugin to function.', 'third-audience' ); ?></p>

							<h4><?php esc_html_e( 'Installation Methods:', 'third-audience' ); ?></h4>

							<div class="ta-install-method">
								<div class="ta-method-header">
									<span class="ta-method-badge ta-method-recommended"><?php esc_html_e( 'Recommended', 'third-audience' ); ?></span>
									<strong><?php esc_html_e( 'Method 1: Using Composer', 'third-audience' ); ?></strong>
								</div>
								<p><?php esc_html_e( 'Run the following command in your plugin directory:', 'third-audience' ); ?></p>
								<pre>cd <?php echo esc_html( WP_PLUGIN_DIR ); ?>/third-audience
composer install --no-dev --optimize-autoloader</pre>
							</div>

							<div class="ta-install-method">
								<div class="ta-method-header">
									<strong><?php esc_html_e( 'Method 2: Download Pre-packaged Version', 'third-audience' ); ?></strong>
								</div>
								<p>
									<?php
									printf(
										/* translators: %s: Plugin download URL */
										esc_html__( 'Download the complete plugin package with all dependencies included from %s', 'third-audience' ),
										'<a href="https://github.com/third-audience/wordpress-plugin/releases" target="_blank">GitHub Releases</a>'
									);
									?>
								</p>
							</div>

							<div class="ta-help-note">
								<span class="dashicons dashicons-sos"></span>
								<p>
									<?php esc_html_e( 'If you\'re not comfortable with command-line tools, contact your system administrator or hosting provider for assistance with installing Composer dependencies.', 'third-audience' ); ?>
								</p>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( version_compare( PHP_VERSION, '7.4.0', '<' ) ) : ?>
					<div class="ta-troubleshoot-section ta-troubleshoot-error">
						<div class="ta-troubleshoot-icon">
							<span class="dashicons dashicons-warning"></span>
						</div>
						<div class="ta-troubleshoot-content">
							<h3><?php esc_html_e( 'PHP Version Too Old', 'third-audience' ); ?></h3>
							<p>
								<?php
								printf(
									/* translators: 1: Current PHP version, 2: Required PHP version */
									esc_html__( 'Your server is running PHP %1$s, but this plugin requires PHP %2$s or higher.', 'third-audience' ),
									'<strong>' . esc_html( PHP_VERSION ) . '</strong>',
									'<strong>7.4.0</strong>'
								);
								?>
							</p>
							<p><?php esc_html_e( 'Contact your hosting provider to upgrade PHP to a supported version.', 'third-audience' ); ?></p>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( ! class_exists( 'DOMDocument' ) ) : ?>
					<div class="ta-troubleshoot-section ta-troubleshoot-warning">
						<div class="ta-troubleshoot-icon">
							<span class="dashicons dashicons-info"></span>
						</div>
						<div class="ta-troubleshoot-content">
							<h3><?php esc_html_e( 'DOMDocument Extension Missing', 'third-audience' ); ?></h3>
							<p><?php esc_html_e( 'The DOMDocument PHP extension is not available. While the plugin will still work, content extraction quality may be reduced.', 'third-audience' ); ?></p>
							<p><?php esc_html_e( 'Contact your hosting provider to enable the DOM extension for PHP.', 'third-audience' ); ?></p>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
	</div><!-- /ta-cards-container -->

	<!-- Plugin Information -->
	<div class="ta-card">
		<div class="ta-card-header">
			<h2><?php esc_html_e( 'Plugin Information', 'third-audience' ); ?></h2>
		</div>
		<div class="ta-card-body">
			<div class="ta-info-grid">
				<div class="ta-info-item">
					<span class="ta-info-label"><?php esc_html_e( 'Plugin Version', 'third-audience' ); ?></span>
					<span class="ta-info-value"><code><?php echo esc_html( TA_VERSION ); ?></code></span>
				</div>
				<div class="ta-info-item">
					<span class="ta-info-label"><?php esc_html_e( 'Library Version', 'third-audience' ); ?></span>
					<span class="ta-info-value">
						<?php if ( $library_version ) : ?>
							<code><?php echo esc_html( $library_version ); ?></code>
						<?php else : ?>
							<span class="ta-status-badge ta-status-error">
								<?php esc_html_e( 'Not installed', 'third-audience' ); ?>
							</span>
						<?php endif; ?>
					</span>
				</div>
				<div class="ta-info-item">
					<span class="ta-info-label"><?php esc_html_e( 'Conversion Method', 'third-audience' ); ?></span>
					<span class="ta-info-value">
						<strong><?php esc_html_e( 'Local PHP Conversion', 'third-audience' ); ?></strong>
						<span class="ta-status-badge ta-status-success">
							<span class="dashicons dashicons-yes-alt"></span>
							<?php esc_html_e( 'No external dependencies', 'third-audience' ); ?>
						</span>
					</span>
				</div>
				<div class="ta-info-item">
					<span class="ta-info-label"><?php esc_html_e( 'Plugin Directory', 'third-audience' ); ?></span>
					<span class="ta-info-value"><code><?php echo esc_html( TA_PLUGIN_DIR ); ?></code></span>
				</div>
				<div class="ta-info-item">
					<span class="ta-info-label"><?php esc_html_e( 'Composer Autoloader', 'third-audience' ); ?></span>
					<span class="ta-info-value">
						<?php if ( file_exists( TA_PLUGIN_DIR . 'vendor/autoload.php' ) ) : ?>
							<span class="ta-status-badge ta-status-success">
								<span class="dashicons dashicons-yes-alt"></span>
								<?php esc_html_e( 'Loaded', 'third-audience' ); ?>
							</span>
						<?php else : ?>
							<span class="ta-status-badge ta-status-error">
								<span class="dashicons dashicons-dismiss"></span>
								<?php esc_html_e( 'Not found', 'third-audience' ); ?>
							</span>
						<?php endif; ?>
					</span>
				</div>
			</div>
		</div>
	</div>

	<!-- What's New in 2.0 -->
	<div class="ta-card ta-features-card">
		<div class="ta-card-header">
			<h2><?php esc_html_e( 'What\'s New in Version 2.0', 'third-audience' ); ?></h2>
		</div>
		<div class="ta-card-body">
			<div class="ta-features-grid">
				<div class="ta-feature-item">
					<div class="ta-feature-icon">
						<span class="dashicons dashicons-admin-site"></span>
					</div>
					<div class="ta-feature-content">
						<h3><?php esc_html_e( 'Local Conversion', 'third-audience' ); ?></h3>
						<p><?php esc_html_e( 'All HTML to Markdown conversion now happens locally on your server. No external API calls required!', 'third-audience' ); ?></p>
					</div>
				</div>
				<div class="ta-feature-item">
					<div class="ta-feature-icon">
						<span class="dashicons dashicons-performance"></span>
					</div>
					<div class="ta-feature-content">
						<h3><?php esc_html_e( 'Faster Performance', 'third-audience' ); ?></h3>
						<p><?php esc_html_e( 'Zero network latency - instant markdown generation.', 'third-audience' ); ?></p>
					</div>
				</div>
				<div class="ta-feature-item">
					<div class="ta-feature-icon">
						<span class="dashicons dashicons-lock"></span>
					</div>
					<div class="ta-feature-content">
						<h3><?php esc_html_e( 'More Private', 'third-audience' ); ?></h3>
						<p><?php esc_html_e( 'Your content never leaves your server.', 'third-audience' ); ?></p>
					</div>
				</div>
				<div class="ta-feature-item">
					<div class="ta-feature-icon">
						<span class="dashicons dashicons-admin-tools"></span>
					</div>
					<div class="ta-feature-content">
						<h3><?php esc_html_e( 'Simpler Setup', 'third-audience' ); ?></h3>
						<p><?php esc_html_e( 'No API keys or external Worker configuration needed.', 'third-audience' ); ?></p>
					</div>
				</div>
				<div class="ta-feature-item">
					<div class="ta-feature-icon">
						<span class="dashicons dashicons-yes-alt"></span>
					</div>
					<div class="ta-feature-content">
						<h3><?php esc_html_e( 'More Reliable', 'third-audience' ); ?></h3>
						<p><?php esc_html_e( 'No dependency on external services means no downtime from third-party issues.', 'third-audience' ); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
