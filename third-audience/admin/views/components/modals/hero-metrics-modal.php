<?php
/**
 * Hero Metrics Drill-Down Modal Component.
 *
 * @package ThirdAudience
 * @since   3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- Hero Metrics Drill-Down Modal -->
<div class="ta-hero-modal-overlay" style="display: none;">
	<div class="ta-session-modal" style="max-width: 900px;">
		<div class="ta-session-modal-header">
			<h2 id="ta-hero-modal-title"><?php esc_html_e( 'Metric Details', 'third-audience' ); ?></h2>
			<button type="button" class="ta-hero-modal-close">
				<span class="dashicons dashicons-no-alt"></span>
			</button>
		</div>
		<div class="ta-session-modal-body">
			<div class="ta-hero-loading" style="text-align: center; padding: 40px;">
				<span class="spinner is-active" style="float: none;"></span>
				<p><?php esc_html_e( 'Loading data...', 'third-audience' ); ?></p>
			</div>
			<div class="ta-hero-content" style="display: none;">
				<!-- Summary Row -->
				<div class="ta-hero-summary" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px;">
					<div class="ta-session-stat">
						<span class="ta-stat-value" id="ta-hero-stat1">-</span>
						<span class="ta-stat-label" id="ta-hero-label1">-</span>
					</div>
					<div class="ta-session-stat">
						<span class="ta-stat-value" id="ta-hero-stat2">-</span>
						<span class="ta-stat-label" id="ta-hero-label2">-</span>
					</div>
					<div class="ta-session-stat">
						<span class="ta-stat-value" id="ta-hero-stat3">-</span>
						<span class="ta-stat-label" id="ta-hero-label3">-</span>
					</div>
				</div>

				<!-- Chart Section -->
				<div class="ta-hero-chart-section" style="margin-bottom: 20px;">
					<h4 style="margin: 0 0 12px 0;">
						<span class="dashicons dashicons-chart-pie"></span>
						<span id="ta-hero-chart-title"><?php esc_html_e( 'Distribution', 'third-audience' ); ?></span>
					</h4>
					<div style="display: flex; gap: 20px; align-items: flex-start;">
						<div style="flex: 1; max-height: 250px;">
							<canvas id="ta-hero-chart"></canvas>
						</div>
						<div id="ta-hero-chart-legend" style="flex: 0 0 200px; font-size: 13px;"></div>
					</div>
				</div>

				<!-- Detailed Table -->
				<div class="ta-hero-table-section">
					<h4 style="margin: 0 0 12px 0;">
						<span class="dashicons dashicons-list-view"></span>
						<span id="ta-hero-table-title"><?php esc_html_e( 'Detailed Breakdown', 'third-audience' ); ?></span>
					</h4>
					<div style="max-height: 300px; overflow-y: auto;">
						<table class="ta-table ta-table-compact" id="ta-hero-table">
							<thead id="ta-hero-thead">
								<!-- Dynamic headers -->
							</thead>
							<tbody id="ta-hero-tbody">
								<!-- Populated by JavaScript -->
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
