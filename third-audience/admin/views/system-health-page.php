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
	$overall_class = 'notice-error';
} elseif ( $has_warnings ) {
	$overall_status = 'warning';
	$overall_message = __( 'Warning: Some optional features may be limited.', 'third-audience' );
	$overall_class = 'notice-warning';
} else {
	$overall_status = 'ok';
	$overall_message = __( 'All systems operational! Your plugin is ready to serve markdown content.', 'third-audience' );
	$overall_class = 'notice-success';
}

// Get library version.
$library_version = TA_Local_Converter::get_library_version();

?>

<div class="wrap">
	<h1><?php esc_html_e( 'System Health', 'third-audience' ); ?></h1>
	<p class="description">
		<?php esc_html_e( 'Check system requirements and verify that all dependencies are installed correctly.', 'third-audience' ); ?>
	</p>

	<!-- Overall Status -->
	<div class="notice <?php echo esc_attr( $overall_class ); ?> inline">
		<p>
			<strong><?php esc_html_e( 'Overall Status:', 'third-audience' ); ?></strong>
			<?php echo esc_html( $overall_message ); ?>
		</p>
	</div>

	<!-- System Information -->
	<h2><?php esc_html_e( 'System Information', 'third-audience' ); ?></h2>
	<table class="widefat striped">
		<thead>
			<tr>
				<th style="width: 30%;"><?php esc_html_e( 'Component', 'third-audience' ); ?></th>
				<th style="width: 20%;"><?php esc_html_e( 'Required', 'third-audience' ); ?></th>
				<th style="width: 20%;"><?php esc_html_e( 'Current', 'third-audience' ); ?></th>
				<th style="width: 10%;"><?php esc_html_e( 'Status', 'third-audience' ); ?></th>
				<th style="width: 20%;"><?php esc_html_e( 'Message', 'third-audience' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $requirements as $key => $check ) : ?>
				<tr>
					<td><strong><?php echo esc_html( ucwords( str_replace( '_', ' ', $key ) ) ); ?></strong></td>
					<td><?php echo esc_html( $check['required'] ); ?></td>
					<td>
						<code><?php echo esc_html( $check['current'] ?? 'N/A' ); ?></code>
					</td>
					<td>
						<?php if ( 'ok' === $check['status'] ) : ?>
							<span style="color: #46b450; font-size: 20px;" title="<?php esc_attr_e( 'OK', 'third-audience' ); ?>">✓</span>
						<?php elseif ( 'warning' === $check['status'] ) : ?>
							<span style="color: #ffb900; font-size: 20px;" title="<?php esc_attr_e( 'Warning', 'third-audience' ); ?>">⚠</span>
						<?php else : ?>
							<span style="color: #dc3232; font-size: 20px;" title="<?php esc_attr_e( 'Error', 'third-audience' ); ?>">✗</span>
						<?php endif; ?>
					</td>
					<td><?php echo esc_html( $check['message'] ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<!-- Installation Instructions -->
	<?php if ( $has_errors || $has_warnings ) : ?>
		<div class="ta-health-instructions" style="margin-top: 30px;">
			<h2><?php esc_html_e( 'Troubleshooting', 'third-audience' ); ?></h2>

			<?php if ( ! TA_Local_Converter::is_library_available() ) : ?>
				<div class="notice notice-error inline">
					<h3><?php esc_html_e( 'HTML to Markdown Library Missing', 'third-audience' ); ?></h3>
					<p><?php esc_html_e( 'The required HTML to Markdown conversion library is not installed. This library is essential for the plugin to function.', 'third-audience' ); ?></p>

					<h4><?php esc_html_e( 'Installation Methods:', 'third-audience' ); ?></h4>

					<div style="background: #fff; padding: 15px; border-left: 4px solid #dc3232; margin: 10px 0;">
						<p><strong><?php esc_html_e( 'Method 1: Using Composer (Recommended)', 'third-audience' ); ?></strong></p>
						<p><?php esc_html_e( 'Run the following command in your plugin directory:', 'third-audience' ); ?></p>
						<pre style="background: #2c3338; color: #f0f0f0; padding: 10px; overflow-x: auto;">cd <?php echo esc_html( WP_PLUGIN_DIR ); ?>/third-audience
composer install --no-dev --optimize-autoloader</pre>
					</div>

					<div style="background: #fff; padding: 15px; border-left: 4px solid #72aee6; margin: 10px 0;">
						<p><strong><?php esc_html_e( 'Method 2: Download Pre-packaged Version', 'third-audience' ); ?></strong></p>
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

					<div style="background: #fff; padding: 15px; border-left: 4px solid #f0b849; margin: 10px 0;">
						<p><strong><?php esc_html_e( 'Need Help?', 'third-audience' ); ?></strong></p>
						<p>
							<?php esc_html_e( 'If you\'re not comfortable with command-line tools, contact your system administrator or hosting provider for assistance with installing Composer dependencies.', 'third-audience' ); ?>
						</p>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( version_compare( PHP_VERSION, '7.4.0', '<' ) ) : ?>
				<div class="notice notice-error inline">
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
			<?php endif; ?>

			<?php if ( ! class_exists( 'DOMDocument' ) ) : ?>
				<div class="notice notice-warning inline">
					<h3><?php esc_html_e( 'DOMDocument Extension Missing', 'third-audience' ); ?></h3>
					<p><?php esc_html_e( 'The DOMDocument PHP extension is not available. While the plugin will still work, content extraction quality may be reduced.', 'third-audience' ); ?></p>
					<p><?php esc_html_e( 'Contact your hosting provider to enable the DOM extension for PHP.', 'third-audience' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<!-- Plugin Information -->
	<h2 style="margin-top: 40px;"><?php esc_html_e( 'Plugin Information', 'third-audience' ); ?></h2>
	<table class="widefat striped">
		<tbody>
			<tr>
				<th style="width: 30%;"><?php esc_html_e( 'Plugin Version', 'third-audience' ); ?></th>
				<td><code><?php echo esc_html( TA_VERSION ); ?></code></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Library Version', 'third-audience' ); ?></th>
				<td>
					<?php if ( $library_version ) : ?>
						<code><?php echo esc_html( $library_version ); ?></code>
					<?php else : ?>
						<span style="color: #dc3232;"><?php esc_html_e( 'Not installed', 'third-audience' ); ?></span>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Conversion Method', 'third-audience' ); ?></th>
				<td>
					<strong><?php esc_html_e( 'Local PHP Conversion', 'third-audience' ); ?></strong>
					<span style="color: #46b450;"> ✓ <?php esc_html_e( 'No external dependencies!', 'third-audience' ); ?></span>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Plugin Directory', 'third-audience' ); ?></th>
				<td><code><?php echo esc_html( TA_PLUGIN_DIR ); ?></code></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Composer Autoloader', 'third-audience' ); ?></th>
				<td>
					<?php if ( file_exists( TA_PLUGIN_DIR . 'vendor/autoload.php' ) ) : ?>
						<span style="color: #46b450;">✓ <?php esc_html_e( 'Loaded', 'third-audience' ); ?></span>
					<?php else : ?>
						<span style="color: #dc3232;">✗ <?php esc_html_e( 'Not found', 'third-audience' ); ?></span>
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>

	<!-- What's New in 2.0 -->
	<div class="ta-whats-new" style="margin-top: 40px; background: #fff; border-left: 4px solid #46b450; padding: 20px;">
		<h2><?php esc_html_e( 'What\'s New in Version 2.0', 'third-audience' ); ?></h2>
		<ul style="list-style: disc; margin-left: 20px;">
			<li><strong><?php esc_html_e( 'Local Conversion:', 'third-audience' ); ?></strong> <?php esc_html_e( 'All HTML to Markdown conversion now happens locally on your server. No external API calls required!', 'third-audience' ); ?></li>
			<li><strong><?php esc_html_e( 'Faster Performance:', 'third-audience' ); ?></strong> <?php esc_html_e( 'Zero network latency - instant markdown generation.', 'third-audience' ); ?></li>
			<li><strong><?php esc_html_e( 'More Private:', 'third-audience' ); ?></strong> <?php esc_html_e( 'Your content never leaves your server.', 'third-audience' ); ?></li>
			<li><strong><?php esc_html_e( 'Simpler Setup:', 'third-audience' ); ?></strong> <?php esc_html_e( 'No API keys or external Worker configuration needed.', 'third-audience' ); ?></li>
			<li><strong><?php esc_html_e( 'More Reliable:', 'third-audience' ); ?></strong> <?php esc_html_e( 'No dependency on external services means no downtime from third-party issues.', 'third-audience' ); ?></li>
		</ul>
	</div>
</div>

<style>
.ta-health-instructions h4 {
	margin-top: 20px;
	margin-bottom: 10px;
}

.ta-health-instructions pre {
	border-radius: 4px;
	font-family: 'Courier New', Courier, monospace;
	font-size: 14px;
}

.ta-whats-new ul li {
	margin-bottom: 10px;
	line-height: 1.6;
}
</style>
