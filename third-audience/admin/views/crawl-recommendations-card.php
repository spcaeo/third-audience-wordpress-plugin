<?php
/**
 * Crawl Budget Recommendations Card View
 *
 * Displays actionable recommendations for optimizing crawl budget.
 *
 * @package Third_Audience
 * @since 2.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get recommendations from analyzer
$analyzer        = new TA_Crawl_Budget_Analyzer();
$bot_type        = isset( $_GET['bot_type'] ) ? sanitize_text_field( wp_unslash( $_GET['bot_type'] ) ) : null;
$period_days     = isset( $_GET['period'] ) ? (int) $_GET['period'] : 7;
$recommendations = $analyzer->analyze_crawl_budget( $bot_type, $period_days );
$quick_fixes     = $analyzer->get_quick_fixes();
?>

<div class="ta-card ta-recommendations-card">
	<div class="ta-card-header">
		<h2>
			<span class="dashicons dashicons-lightbulb"></span>
			<?php esc_html_e( 'Crawl Budget Recommendations', 'third-audience' ); ?>
		</h2>
		<p class="ta-card-description">
			<?php esc_html_e( 'Optimize your crawl budget based on actual bot behavior patterns', 'third-audience' ); ?>
		</p>
	</div>
	<div class="ta-card-body">
		<?php if ( empty( $recommendations ) ) : ?>
			<div class="ta-all-good">
				<span class="dashicons dashicons-yes-alt"></span>
				<p><?php esc_html_e( 'Your crawl budget is optimized! No issues detected.', 'third-audience' ); ?></p>
				<p class="ta-all-good-subtext">
					<?php esc_html_e( 'Continue monitoring for changes in bot behavior.', 'third-audience' ); ?>
				</p>
			</div>
		<?php else : ?>
			<ul class="ta-recommendations-list">
				<?php foreach ( $recommendations as $rec ) : ?>
					<li class="ta-rec-item ta-rec-<?php echo esc_attr( $rec['severity'] ); ?>">
						<div class="ta-rec-header">
							<?php
							$icon = 'warning';
							if ( $rec['severity'] === 'medium' ) {
								$icon = 'info';
							} elseif ( $rec['severity'] === 'warning' ) {
								$icon = 'flag';
							}
							?>
							<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
							<strong><?php echo esc_html( $rec['title'] ); ?></strong>
							<span class="ta-rec-severity-badge ta-severity-<?php echo esc_attr( $rec['severity'] ); ?>">
								<?php echo esc_html( ucfirst( $rec['severity'] ) ); ?>
							</span>
						</div>
						<div class="ta-rec-content">
							<p class="ta-rec-message"><?php echo esc_html( $rec['message'] ); ?></p>
							<div class="ta-rec-action">
								<strong><?php esc_html_e( 'Recommended Action:', 'third-audience' ); ?></strong>
								<?php echo esc_html( $rec['action'] ); ?>
							</div>
							<div class="ta-rec-impact">
								<span class="dashicons dashicons-chart-line"></span>
								<span class="ta-impact-text"><?php echo esc_html( $rec['impact'] ); ?></span>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php if ( ! empty( $quick_fixes ) ) : ?>
				<div class="ta-quick-fixes">
					<h3><?php esc_html_e( 'Quick Fixes', 'third-audience' ); ?></h3>
					<div class="ta-quick-fixes-buttons">
						<?php foreach ( $quick_fixes as $fix ) : ?>
							<button
								type="button"
								class="button button-secondary ta-quick-fix-btn"
								data-action="<?php echo esc_attr( $fix['action'] ); ?>"
								data-fix-data="<?php echo esc_attr( wp_json_encode( $fix['data'] ) ); ?>"
							>
								<?php echo esc_html( $fix['label'] ); ?>
							</button>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
	// Handle quick fix button clicks
	$('.ta-quick-fix-btn').on('click', function() {
		const action = $(this).data('action');
		const fixData = $(this).data('fix-data');

		if (action === 'navigate' && fixData.url) {
			window.location.href = fixData.url;
			return;
		}

		if (action === 'filter_404' && fixData.status_code) {
			// Update filter and reload
			const urlParams = new URLSearchParams(window.location.search);
			urlParams.set('status_code', fixData.status_code);
			window.location.search = urlParams.toString();
			return;
		}

		if (action === 'update_robots_txt') {
			// Show confirmation dialog
			if (confirm('This will update your robots.txt file. Continue?')) {
				// AJAX call to update robots.txt
				$.post(ajaxurl, {
					action: 'ta_update_robots_txt',
					nonce: '<?php echo wp_create_nonce( 'ta_update_robots_txt' ); ?>',
					rule: fixData.rule
				}, function(response) {
					if (response.success) {
						alert('Robots.txt updated successfully!');
						location.reload();
					} else {
						alert('Error updating robots.txt: ' + response.data.message);
					}
				});
			}
		}
	});
});
</script>
