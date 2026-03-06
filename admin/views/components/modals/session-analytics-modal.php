<?php
/**
 * Session Analytics Drill-Down Modal Component.
 *
 * @package ThirdAudience
 * @since   3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- Session Analytics Drill-Down Modal -->
<div class="ta-session-modal-overlay" style="display: none;">
	<div class="ta-session-modal">
		<div class="ta-session-modal-header">
			<h2 id="ta-session-modal-title"><?php esc_html_e( 'Session Analytics Details', 'third-audience' ); ?></h2>
			<button type="button" class="ta-session-modal-close">
				<span class="dashicons dashicons-no-alt"></span>
			</button>
		</div>
		<div class="ta-session-modal-body">
			<div class="ta-session-loading" style="text-align: center; padding: 40px;">
				<span class="spinner is-active" style="float: none;"></span>
				<p><?php esc_html_e( 'Loading session data...', 'third-audience' ); ?></p>
			</div>
			<div class="ta-session-content" style="display: none;">
				<!-- Summary Stats -->
				<div class="ta-session-summary" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px;">
					<div class="ta-session-stat">
						<span class="ta-stat-value" id="ta-modal-fingerprints">-</span>
						<span class="ta-stat-label"><?php esc_html_e( 'Total Fingerprints', 'third-audience' ); ?></span>
					</div>
					<div class="ta-session-stat">
						<span class="ta-stat-value" id="ta-modal-pages">-</span>
						<span class="ta-stat-label"><?php esc_html_e( 'Avg Pages/Session', 'third-audience' ); ?></span>
					</div>
					<div class="ta-session-stat">
						<span class="ta-stat-value" id="ta-modal-duration">-</span>
						<span class="ta-stat-label"><?php esc_html_e( 'Avg Duration', 'third-audience' ); ?></span>
					</div>
					<div class="ta-session-stat">
						<span class="ta-stat-value" id="ta-modal-interval">-</span>
						<span class="ta-stat-label"><?php esc_html_e( 'Avg Interval', 'third-audience' ); ?></span>
					</div>
				</div>

				<!-- Chart Section -->
				<div class="ta-session-chart-section" style="margin-bottom: 20px;">
					<h4 style="margin: 0 0 12px 0;">
						<span class="dashicons dashicons-chart-bar"></span>
						<?php esc_html_e( 'Bot Activity Distribution', 'third-audience' ); ?>
					</h4>
					<canvas id="ta-session-chart" style="max-height: 200px;"></canvas>
				</div>

				<!-- Detailed Table -->
				<div class="ta-session-table-section">
					<h4 style="margin: 0 0 12px 0; display: flex; justify-content: space-between; align-items: center;">
						<span>
							<span class="dashicons dashicons-list-view"></span>
							<?php esc_html_e( 'Bot Fingerprints Detail', 'third-audience' ); ?>
						</span>
						<select id="ta-session-sort" style="font-weight: normal; font-size: 13px;">
							<option value="last_seen"><?php esc_html_e( 'Last Seen (Recent)', 'third-audience' ); ?></option>
							<option value="visit_count"><?php esc_html_e( 'Total Visits', 'third-audience' ); ?></option>
							<option value="pages_per_session"><?php esc_html_e( 'Pages/Session', 'third-audience' ); ?></option>
							<option value="session_duration"><?php esc_html_e( 'Session Duration', 'third-audience' ); ?></option>
							<option value="request_interval"><?php esc_html_e( 'Request Interval', 'third-audience' ); ?></option>
						</select>
					</h4>
					<div style="max-height: 350px; overflow-y: auto;">
						<table class="ta-table ta-table-compact" id="ta-session-table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Bot', 'third-audience' ); ?></th>
									<th><?php esc_html_e( 'IP Address', 'third-audience' ); ?></th>
									<th style="text-align: right;"><?php esc_html_e( 'Visits', 'third-audience' ); ?></th>
									<th style="text-align: right;"><?php esc_html_e( 'Pages/Session', 'third-audience' ); ?></th>
									<th style="text-align: right;"><?php esc_html_e( 'Duration', 'third-audience' ); ?></th>
									<th style="text-align: right;"><?php esc_html_e( 'Interval', 'third-audience' ); ?></th>
									<th><?php esc_html_e( 'Last Seen', 'third-audience' ); ?></th>
								</tr>
							</thead>
							<tbody id="ta-session-tbody">
								<!-- Populated by JavaScript -->
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
