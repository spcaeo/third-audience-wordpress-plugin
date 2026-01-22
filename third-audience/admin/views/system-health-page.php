<?php
/**
 * System Health Page - Clean, organized system status dashboard.
 *
 * @package ThirdAudience
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get system requirements.
$requirements = TA_Local_Converter::check_system_requirements();

// Get version info.
$update_checker = new TA_Update_Checker();
$version_info   = $update_checker->get_version_info();

// Calculate overall health.
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
	$overall_status  = 'error';
	$overall_message = __( 'Action Required: System requirements not met.', 'third-audience' );
} elseif ( $has_warnings ) {
	$overall_status  = 'warning';
	$overall_message = __( 'Warning: Some optional features may be limited.', 'third-audience' );
} else {
	$overall_status  = 'ok';
	$overall_message = __( 'All systems operational!', 'third-audience' );
}

$library_version = TA_Local_Converter::get_library_version();
?>

<div class="wrap ta-system-health">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'System Health', 'third-audience' ); ?>
		<span style="font-size: 0.6em; color: #646970; font-weight: 400;">v<?php echo esc_html( TA_VERSION ); ?></span>
	</h1>
	<p class="description"><?php esc_html_e( 'Monitor system status and diagnostics', 'third-audience' ); ?></p>

	<!-- Overall Status -->
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

	<!-- Row 1: Version & Diagnostics -->
	<div class="ta-cards-container">
		<!-- Version & Updates -->
		<div class="ta-card">
			<div class="ta-card-header">
				<h2><?php esc_html_e( 'Version & Updates', 'third-audience' ); ?></h2>
			</div>
			<div class="ta-card-body">
				<div class="ta-info-grid">
					<div class="ta-info-item">
						<span class="ta-info-label"><?php esc_html_e( 'Current', 'third-audience' ); ?></span>
						<span class="ta-info-value"><code><?php echo esc_html( $version_info['current_version'] ); ?></code></span>
					</div>
					<div class="ta-info-item">
						<span class="ta-info-label"><?php esc_html_e( 'Latest', 'third-audience' ); ?></span>
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
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ta_check_updates' ), 'ta_check_updates' ) ); ?>" class="button button-secondary">
						<span class="dashicons dashicons-update"></span>
						<?php esc_html_e( 'Check Updates', 'third-audience' ); ?>
					</a>
					<?php if ( $version_info['update_available'] && ! empty( $version_info['release_url'] ) ) : ?>
						<a href="<?php echo esc_url( $version_info['release_url'] ); ?>" class="button button-primary" target="_blank" rel="noopener">
							<span class="dashicons dashicons-download"></span>
							<?php esc_html_e( 'Download', 'third-audience' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>

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
	</div>

	<!-- Row 2: Plugin Info & Features -->
	<div class="ta-cards-container">
		<!-- Plugin Information -->
		<div class="ta-card">
			<div class="ta-card-header">
				<h2><?php esc_html_e( 'Plugin Information', 'third-audience' ); ?></h2>
			</div>
			<div class="ta-card-body">
				<div class="ta-info-grid">
					<div class="ta-info-item">
						<span class="ta-info-label"><?php esc_html_e( 'Version', 'third-audience' ); ?></span>
						<span class="ta-info-value"><code><?php echo esc_html( TA_VERSION ); ?></code></span>
					</div>
					<div class="ta-info-item">
						<span class="ta-info-label"><?php esc_html_e( 'Library', 'third-audience' ); ?></span>
						<span class="ta-info-value">
							<?php if ( $library_version ) : ?>
								<code><?php echo esc_html( $library_version ); ?></code>
							<?php else : ?>
								<span class="ta-status-badge ta-status-error"><?php esc_html_e( 'Not installed', 'third-audience' ); ?></span>
							<?php endif; ?>
						</span>
					</div>
					<div class="ta-info-item">
						<span class="ta-info-label"><?php esc_html_e( 'Conversion', 'third-audience' ); ?></span>
						<span class="ta-info-value">
							<strong><?php esc_html_e( 'Local PHP', 'third-audience' ); ?></strong>
							<span class="ta-status-badge ta-status-success">
								<span class="dashicons dashicons-yes-alt"></span>
								<?php esc_html_e( 'No external dependencies', 'third-audience' ); ?>
							</span>
						</span>
					</div>
					<div class="ta-info-item">
						<span class="ta-info-label"><?php esc_html_e( 'Directory', 'third-audience' ); ?></span>
						<span class="ta-info-value"><code><?php echo esc_html( TA_PLUGIN_DIR ); ?></code></span>
					</div>
					<div class="ta-info-item">
						<span class="ta-info-label"><?php esc_html_e( 'Autoloader', 'third-audience' ); ?></span>
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

		<!-- Key Features -->
		<div class="ta-card ta-features-card">
			<div class="ta-card-header">
				<h2><?php esc_html_e( 'Key Features', 'third-audience' ); ?></h2>
			</div>
			<div class="ta-card-body">
				<div class="ta-features-grid">
					<div class="ta-feature-item">
						<div class="ta-feature-icon"><span class="dashicons dashicons-admin-site"></span></div>
						<div class="ta-feature-content">
							<h3><?php esc_html_e( 'Local Conversion', 'third-audience' ); ?></h3>
							<p><?php esc_html_e( 'All conversion happens locally on your server', 'third-audience' ); ?></p>
						</div>
					</div>
					<div class="ta-feature-item">
						<div class="ta-feature-icon"><span class="dashicons dashicons-performance"></span></div>
						<div class="ta-feature-content">
							<h3><?php esc_html_e( 'Fast Performance', 'third-audience' ); ?></h3>
							<p><?php esc_html_e( 'Zero network latency, instant generation', 'third-audience' ); ?></p>
						</div>
					</div>
					<div class="ta-feature-item">
						<div class="ta-feature-icon"><span class="dashicons dashicons-lock"></span></div>
						<div class="ta-feature-content">
							<h3><?php esc_html_e( 'Private', 'third-audience' ); ?></h3>
							<p><?php esc_html_e( 'Content never leaves your server', 'third-audience' ); ?></p>
						</div>
					</div>
					<div class="ta-feature-item">
						<div class="ta-feature-icon"><span class="dashicons dashicons-yes-alt"></span></div>
						<div class="ta-feature-content">
							<h3><?php esc_html_e( 'Reliable', 'third-audience' ); ?></h3>
							<p><?php esc_html_e( 'No external service dependencies', 'third-audience' ); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Troubleshooting (only shown when needed) -->
	<?php if ( $has_errors || $has_warnings ) : ?>
		<div class="ta-card ta-troubleshooting-card">
			<div class="ta-card-header">
				<h2><?php esc_html_e( 'Troubleshooting', 'third-audience' ); ?></h2>
			</div>
			<div class="ta-card-body">
				<?php if ( ! TA_Local_Converter::is_library_available() ) : ?>
					<div class="ta-troubleshoot-section ta-troubleshoot-error">
						<div class="ta-troubleshoot-icon"><span class="dashicons dashicons-warning"></span></div>
						<div class="ta-troubleshoot-content">
							<h3><?php esc_html_e( 'HTML to Markdown Library Missing', 'third-audience' ); ?></h3>
							<p><?php esc_html_e( 'Required library not installed. Install via Composer:', 'third-audience' ); ?></p>
							<pre>cd <?php echo esc_html( WP_PLUGIN_DIR ); ?>/third-audience
composer install --no-dev --optimize-autoloader</pre>
							<p><?php esc_html_e( 'Or download the complete plugin package from GitHub Releases.', 'third-audience' ); ?></p>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( version_compare( PHP_VERSION, '7.4.0', '<' ) ) : ?>
					<div class="ta-troubleshoot-section ta-troubleshoot-error">
						<div class="ta-troubleshoot-icon"><span class="dashicons dashicons-warning"></span></div>
						<div class="ta-troubleshoot-content">
							<h3><?php esc_html_e( 'PHP Version Too Old', 'third-audience' ); ?></h3>
							<p>
								<?php
								printf(
									esc_html__( 'Your server is running PHP %1$s, but this plugin requires PHP %2$s or higher.', 'third-audience' ),
									'<strong>' . esc_html( PHP_VERSION ) . '</strong>',
									'<strong>7.4.0</strong>'
								);
								?>
							</p>
							<p><?php esc_html_e( 'Contact your hosting provider to upgrade PHP.', 'third-audience' ); ?></p>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( ! class_exists( 'DOMDocument' ) ) : ?>
					<div class="ta-troubleshoot-section ta-troubleshoot-warning">
						<div class="ta-troubleshoot-icon"><span class="dashicons dashicons-info"></span></div>
						<div class="ta-troubleshoot-content">
							<h3><?php esc_html_e( 'DOMDocument Extension Missing', 'third-audience' ); ?></h3>
							<p><?php esc_html_e( 'Plugin will work but content quality may be reduced. Contact your hosting provider to enable the DOM extension.', 'third-audience' ); ?></p>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
