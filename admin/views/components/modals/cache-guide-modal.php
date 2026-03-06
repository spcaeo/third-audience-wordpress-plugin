<?php
/**
 * Cache Guide Modal Component.
 *
 * @package ThirdAudience
 * @since   3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- Cache Guide Modal -->
<div class="ta-cache-modal-overlay" style="display: none;">
	<div class="ta-cache-modal">
		<div class="ta-cache-modal-header">
			<h2><?php esc_html_e( 'Cache Status Guide', 'third-audience' ); ?></h2>
			<button type="button" class="ta-cache-modal-close">
				<span class="dashicons dashicons-no-alt"></span>
			</button>
		</div>
		<div class="ta-cache-modal-body">
			<div class="ta-cache-guide-grid">
				<div class="ta-cache-guide-item">
					<span class="ta-cache-badge ta-cache-hit">HIT</span>
					<h4><?php esc_html_e( 'Cache Hit', 'third-audience' ); ?></h4>
					<p><?php esc_html_e( 'Served from cache (1-5ms). Best performance.', 'third-audience' ); ?></p>
				</div>
				<div class="ta-cache-guide-item">
					<span class="ta-cache-badge ta-cache-pre_generated">PRE_GENERATED</span>
					<h4><?php esc_html_e( 'Pre-generated', 'third-audience' ); ?></h4>
					<p><?php esc_html_e( 'Served from post meta (<1ms). Fastest.', 'third-audience' ); ?></p>
				</div>
				<div class="ta-cache-guide-item">
					<span class="ta-cache-badge ta-cache-miss">MISS</span>
					<h4><?php esc_html_e( 'Cache Miss', 'third-audience' ); ?></h4>
					<p><?php esc_html_e( 'Generated fresh (10-50ms). Slower.', 'third-audience' ); ?></p>
				</div>
				<div class="ta-cache-guide-item">
					<span class="ta-cache-badge ta-cache-failed">FAILED</span>
					<h4><?php esc_html_e( 'Failed', 'third-audience' ); ?></h4>
					<p><?php esc_html_e( 'Generation error. Check System Health.', 'third-audience' ); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>
