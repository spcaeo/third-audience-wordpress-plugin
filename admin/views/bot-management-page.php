<?php
/**
 * Bot Management Page - Configure bot tracking and blocking.
 *
 * @package ThirdAudience
 * @since   1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get bot analytics instance.
$bot_analytics = TA_Bot_Analytics::get_instance();

// Get bot configuration.
$bot_config = get_option( 'ta_bot_config', array(
	'track_unknown' => true,
	'blocked_bots'  => array(),
	'custom_bots'   => array(),
) );

// Get bot statistics.
global $wpdb;
$table_name = $wpdb->prefix . 'ta_bot_analytics';

$bot_stats = $wpdb->get_results(
	"SELECT
		bot_type,
		bot_name,
		COUNT(*) as total_visits,
		COUNT(DISTINCT post_id) as unique_pages,
		COUNT(DISTINCT ip_address) as unique_ips,
		COUNT(DISTINCT country_code) as unique_countries,
		AVG(response_time) as avg_response_time,
		SUM(response_size) as total_bandwidth,
		MAX(visit_timestamp) as last_seen,
		GROUP_CONCAT(DISTINCT country_code ORDER BY country_code SEPARATOR ', ') as countries
	FROM {$table_name}
	GROUP BY bot_type, bot_name
	ORDER BY total_visits DESC",
	ARRAY_A
);

?>

<div class="wrap ta-bot-management">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Bot Management', 'third-audience' ); ?>
		<span style="font-size: 0.6em; color: #646970; font-weight: 400;">v<?php echo esc_html( TA_VERSION ); ?></span>
	</h1>

	<p class="description">
		<?php esc_html_e( 'Configure which bots to track and block. All bots accessing .md URLs are tracked by default.', 'third-audience' ); ?>
	</p>

	<?php
	// Show success message if settings were updated.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['updated'] ) && 'true' === $_GET['updated'] ) :
		?>
		<div class="ta-notice ta-notice-success">
			<span class="dashicons dashicons-yes-alt"></span>
			<p><?php esc_html_e( 'Bot configuration saved successfully.', 'third-audience' ); ?></p>
		</div>
		<?php
	endif;
	?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'ta_ta_save_bot_config', 'ta_bot_config_nonce' ); ?>
		<input type="hidden" name="action" value="ta_save_bot_config">

		<!-- Global Settings Card -->
		<div class="ta-card">
			<div class="ta-card-header">
				<div>
					<h2 class="ta-card-title"><?php esc_html_e( 'Global Settings', 'third-audience' ); ?></h2>
					<p class="ta-card-subtitle"><?php esc_html_e( 'Configure default bot tracking behavior', 'third-audience' ); ?></p>
				</div>
			</div>

			<div class="ta-form-group">
				<label class="ta-checkbox-label">
					<input
						type="checkbox"
						id="track_unknown"
						name="track_unknown"
						value="1"
						<?php checked( $bot_config['track_unknown'], true ); ?>
					>
					<span class="ta-checkbox-text">
						<strong><?php esc_html_e( 'Track Unknown Bots', 'third-audience' ); ?></strong>
						<span class="ta-form-help"><?php esc_html_e( 'Track bots not in the known bot list (categorized as "Unknown Bot"). Recommended to track all bot activity including new AI bots and custom crawlers.', 'third-audience' ); ?></span>
					</span>
				</label>
			</div>
		</div>

		<!-- Detected Bots Card -->
		<div class="ta-card">
			<div class="ta-card-header">
				<div>
					<h2 class="ta-card-title"><?php esc_html_e( 'Detected Bots', 'third-audience' ); ?></h2>
					<p class="ta-card-subtitle"><?php esc_html_e( 'Manage bots that have accessed your site. Block bots to prevent them from accessing markdown content.', 'third-audience' ); ?></p>
				</div>
			</div>

			<table class="ta-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Bot Name', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Bot Type', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Priority', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Cache TTL', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Total Visits', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Unique Pages', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Unique IPs', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Countries', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Avg Response', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Last Seen', 'third-audience' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $bot_stats ) ) : ?>
						<tr>
							<td colspan="10" class="ta-no-data">
								<?php esc_html_e( 'No bots detected yet. Bot activity will appear here once bots access your .md URLs.', 'third-audience' ); ?>
							</td>
						</tr>
					<?php else : ?>
						<?php foreach ( $bot_stats as $bot ) : ?>
							<?php
							$bot_priorities = isset( $bot_config['bot_priorities'] ) ? $bot_config['bot_priorities'] : array();
							$current_priority = isset( $bot_priorities[ $bot['bot_type'] ] ) ? $bot_priorities[ $bot['bot_type'] ] : 'medium';
							$cache_ttl = TA_Bot_Analytics::get_cache_ttl_for_priority( $current_priority );
							$bandwidth  = size_format( $bot['total_bandwidth'], 2 );

							// Priority color mapping.
							$priority_colors = array(
								'high'    => '#007aff',
								'medium'  => '#34c759',
								'low'     => '#ff9500',
								'blocked' => '#ff3b30',
							);
							$priority_color = isset( $priority_colors[ $current_priority ] ) ? $priority_colors[ $current_priority ] : '#666';
							?>
							<tr>
								<td><strong><?php echo esc_html( $bot['bot_name'] ); ?></strong></td>
								<td><code class="ta-code-badge"><?php echo esc_html( $bot['bot_type'] ); ?></code></td>
								<td>
									<select name="bot_priorities[<?php echo esc_attr( $bot['bot_type'] ); ?>]" class="ta-priority-select" data-priority="<?php echo esc_attr( $current_priority ); ?>">
										<option value="high" <?php selected( $current_priority, 'high' ); ?>>
											<?php esc_html_e( 'High', 'third-audience' ); ?>
										</option>
										<option value="medium" <?php selected( $current_priority, 'medium' ); ?>>
											<?php esc_html_e( 'Medium', 'third-audience' ); ?>
										</option>
										<option value="low" <?php selected( $current_priority, 'low' ); ?>>
											<?php esc_html_e( 'Low', 'third-audience' ); ?>
										</option>
										<option value="blocked" <?php selected( $current_priority, 'blocked' ); ?>>
											<?php esc_html_e( 'Blocked', 'third-audience' ); ?>
										</option>
									</select>
								</td>
								<td>
									<?php
									if ( 'blocked' === $current_priority ) {
										echo '<span class="ta-badge ta-badge-error">403 Forbidden</span>';
									} else {
										echo '<span class="ta-ttl-badge">' . esc_html( human_time_diff( 0, $cache_ttl ) ) . '</span>';
									}
									?>
								</td>
								<td><strong><?php echo number_format_i18n( $bot['total_visits'] ); ?></strong></td>
								<td><?php echo number_format_i18n( $bot['unique_pages'] ); ?></td>
								<td><?php echo number_format_i18n( $bot['unique_ips'] ); ?></td>
								<td>
									<?php
									if ( ! empty( $bot['countries'] ) ) {
										$countries = explode( ', ', $bot['countries'] );
										$countries = array_filter( $countries );
										if ( ! empty( $countries ) ) {
											echo '<span class="ta-country-list">' . esc_html( implode( ', ', $countries ) ) . '</span>';
											echo ' <span class="ta-text-gray">(' . count( $countries ) . ')</span>';
										} else {
											echo '—';
										}
									} else {
										echo '—';
									}
									?>
								</td>
								<td><span class="ta-response-time"><?php echo round( $bot['avg_response_time'] ); ?>ms</span></td>
								<td>
									<span class="ta-time-ago">
										<?php
										echo esc_html(
											human_time_diff(
												strtotime( $bot['last_seen'] ),
												current_time( 'timestamp' )
											)
										);
										?>
										<?php esc_html_e( 'ago', 'third-audience' ); ?>
									</span>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<!-- Rate Limits Card -->
		<div class="ta-card">
			<div class="ta-card-header">
				<div>
					<h2 class="ta-card-title"><?php esc_html_e( 'Rate Limits', 'third-audience' ); ?></h2>
					<p class="ta-card-subtitle"><?php esc_html_e( 'Configure rate limits per bot priority level. Set to 0 for unlimited.', 'third-audience' ); ?></p>
				</div>
			</div>

			<table class="ta-table ta-rate-limits-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Priority Level', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Requests per Minute', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Requests per Hour', 'third-audience' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$rate_limiter = new TA_Rate_Limiter();
					$priorities = array(
						'high'    => __( 'High Priority', 'third-audience' ),
						'medium'  => __( 'Medium Priority', 'third-audience' ),
						'low'     => __( 'Low Priority', 'third-audience' ),
					);

					foreach ( $priorities as $priority => $label ) :
						$limits = $rate_limiter->get_bot_rate_limits( 'default', $priority );
						?>
						<tr>
							<td>
								<strong><?php echo esc_html( $label ); ?></strong>
							</td>
							<td>
								<div class="ta-rate-limit-input">
									<input
										type="number"
										name="rate_limits[<?php echo esc_attr( $priority ); ?>][per_minute]"
										value="<?php echo esc_attr( $limits['per_minute'] ); ?>"
										min="0"
										class="ta-form-input"
										placeholder="0"
									>
									<span class="ta-input-help">
										<?php
										if ( 0 === $limits['per_minute'] ) {
											esc_html_e( 'Unlimited', 'third-audience' );
										} else {
											printf(
												esc_html__( 'Current: %d requests/min', 'third-audience' ),
												$limits['per_minute']
											);
										}
										?>
									</span>
								</div>
							</td>
							<td>
								<div class="ta-rate-limit-input">
									<input
										type="number"
										name="rate_limits[<?php echo esc_attr( $priority ); ?>][per_hour]"
										value="<?php echo esc_attr( $limits['per_hour'] ); ?>"
										min="0"
										class="ta-form-input"
										placeholder="0"
									>
									<span class="ta-input-help">
										<?php
										if ( 0 === $limits['per_hour'] ) {
											esc_html_e( 'Unlimited', 'third-audience' );
										} else {
											printf(
												esc_html__( 'Current: %d requests/hour', 'third-audience' ),
												$limits['per_hour']
											);
										}
										?>
									</span>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<div class="ta-notice ta-notice-info" style="margin-top: 20px;">
				<span class="dashicons dashicons-info"></span>
				<p>
					<strong><?php esc_html_e( 'Note:', 'third-audience' ); ?></strong>
					<?php esc_html_e( 'Blocked bots (priority: blocked) cannot access content regardless of rate limits. Rate limits only apply to allowed bots.', 'third-audience' ); ?>
				</p>
			</div>
		</div>

		<!-- Custom Bot Patterns Card -->
		<div class="ta-card">
			<div class="ta-card-header">
				<div>
					<h2 class="ta-card-title"><?php esc_html_e( 'Custom Bot Patterns', 'third-audience' ); ?></h2>
					<p class="ta-card-subtitle"><?php esc_html_e( 'Add custom bot patterns to identify and name specific bots. Uses regular expressions to match User-Agent strings.', 'third-audience' ); ?></p>
				</div>
			</div>

			<table class="ta-table ta-custom-bots-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Pattern', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Bot Name', 'third-audience' ); ?></th>
						<th style="width: 120px;"><?php esc_html_e( 'Actions', 'third-audience' ); ?></th>
					</tr>
				</thead>
				<tbody id="custom-bots-list">
					<?php if ( ! empty( $bot_config['custom_bots'] ) ) : ?>
						<?php foreach ( $bot_config['custom_bots'] as $index => $custom_bot ) : ?>
							<tr>
								<td>
									<input
										type="text"
										name="custom_bots[<?php echo esc_attr( $index ); ?>][pattern]"
										value="<?php echo esc_attr( $custom_bot['pattern'] ); ?>"
										class="ta-form-input"
										placeholder="/YourBot/i"
									>
								</td>
								<td>
									<input
										type="text"
										name="custom_bots[<?php echo esc_attr( $index ); ?>][name]"
										value="<?php echo esc_attr( $custom_bot['name'] ); ?>"
										class="ta-form-input"
										placeholder="Your Bot Name"
									>
								</td>
								<td>
									<button type="button" class="button button-secondary ta-btn remove-custom-bot">
										<?php esc_html_e( 'Remove', 'third-audience' ); ?>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr class="ta-empty-row">
							<td colspan="3" class="ta-no-data">
								<?php esc_html_e( 'No custom bot patterns added yet. Click "Add Custom Bot Pattern" to get started.', 'third-audience' ); ?>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>

			<div style="margin-top: 20px;">
				<button type="button" id="add-custom-bot" class="button button-secondary ta-btn">
					<?php esc_html_e( 'Add Custom Bot Pattern', 'third-audience' ); ?>
				</button>
			</div>
		</div>

		<!-- Submit Button -->
		<div style="margin-top: 24px;">
			<button type="submit" class="button button-primary ta-btn" style="padding: 12px 32px; font-size: 15px;">
				<?php esc_html_e( 'Save Bot Configuration', 'third-audience' ); ?>
			</button>
		</div>
	</form>

	<!-- Bot Diagnostic Modal -->
	<div class="ta-bot-modal-overlay" style="display: none;">
		<div class="ta-session-modal" style="max-width: 1100px;">
			<div class="ta-session-modal-header">
				<h2 id="ta-bot-modal-title"><?php esc_html_e( 'Bot Diagnostics', 'third-audience' ); ?></h2>
				<button type="button" class="ta-bot-modal-close">
					<span class="dashicons dashicons-no-alt"></span>
				</button>
			</div>
			<div class="ta-session-modal-body">
				<!-- Loading State -->
				<div class="ta-bot-loading" style="text-align: center; padding: 40px;">
					<span class="spinner is-active" style="float: none;"></span>
					<p><?php esc_html_e( 'Loading bot data...', 'third-audience' ); ?></p>
				</div>

				<!-- Content -->
				<div class="ta-bot-content" style="display: none;">
					<!-- Summary Stats Row -->
					<div class="ta-bot-summary" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 24px;">
						<div class="ta-session-stat">
							<span class="ta-stat-value" id="ta-bot-total-visits">-</span>
							<span class="ta-stat-label"><?php esc_html_e( 'Total Visits', 'third-audience' ); ?></span>
						</div>
						<div class="ta-session-stat">
							<span class="ta-stat-value" id="ta-bot-unique-pages">-</span>
							<span class="ta-stat-label"><?php esc_html_e( 'Unique Pages', 'third-audience' ); ?></span>
						</div>
						<div class="ta-session-stat">
							<span class="ta-stat-value" id="ta-bot-unique-ips">-</span>
							<span class="ta-stat-label"><?php esc_html_e( 'Unique IPs', 'third-audience' ); ?></span>
						</div>
						<div class="ta-session-stat">
							<span class="ta-stat-value" id="ta-bot-cache-rate">-</span>
							<span class="ta-stat-label"><?php esc_html_e( 'Cache Hit Rate', 'third-audience' ); ?></span>
						</div>
						<div class="ta-session-stat">
							<span class="ta-stat-value" id="ta-bot-avg-response">-</span>
							<span class="ta-stat-label"><?php esc_html_e( 'Avg Response', 'third-audience' ); ?></span>
						</div>
					</div>

					<!-- Tab Navigation -->
					<div class="ta-bot-tabs" style="border-bottom: 1px solid #ddd; margin-bottom: 20px;">
						<button class="ta-bot-tab active" data-tab="behavior"><?php esc_html_e( 'Behavior', 'third-audience' ); ?></button>
						<button class="ta-bot-tab" data-tab="activity"><?php esc_html_e( 'Activity Log', 'third-audience' ); ?></button>
						<button class="ta-bot-tab" data-tab="content"><?php esc_html_e( 'Content Affinity', 'third-audience' ); ?></button>
						<button class="ta-bot-tab" data-tab="impact"><?php esc_html_e( 'Business Impact', 'third-audience' ); ?></button>
						<button class="ta-bot-tab" data-tab="raw"><?php esc_html_e( 'Raw Data', 'third-audience' ); ?></button>
					</div>

					<!-- Tab Content: Behavior -->
					<div class="ta-bot-tab-content" id="ta-bot-behavior">
						<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
							<div class="ta-crawl-budget-section">
								<h4><span class="dashicons dashicons-admin-users"></span> <?php esc_html_e( 'Session Metrics', 'third-audience' ); ?></h4>
								<table class="ta-table-borderless">
									<tbody>
										<tr><td><?php esc_html_e( 'Pages/Session:', 'third-audience' ); ?></td><td id="ta-bot-pages-session">-</td></tr>
										<tr><td><?php esc_html_e( 'Avg Duration:', 'third-audience' ); ?></td><td id="ta-bot-duration">-</td></tr>
										<tr><td><?php esc_html_e( 'Request Interval:', 'third-audience' ); ?></td><td id="ta-bot-interval">-</td></tr>
									</tbody>
								</table>
							</div>
							<div class="ta-crawl-budget-section">
								<h4><span class="dashicons dashicons-shield"></span> <?php esc_html_e( 'Crawl Politeness', 'third-audience' ); ?></h4>
								<table class="ta-table-borderless">
									<tbody>
										<tr><td><?php esc_html_e( 'Checks robots.txt:', 'third-audience' ); ?></td><td id="ta-bot-robots-check">-</td></tr>
										<tr><td><?php esc_html_e( 'Respects robots.txt:', 'third-audience' ); ?></td><td id="ta-bot-robots-respect">-</td></tr>
									</tbody>
								</table>
							</div>
						</div>
						<div class="ta-crawl-budget-section" style="margin-top: 20px;">
							<h4><span class="dashicons dashicons-search"></span> <?php esc_html_e( 'Detection Methods', 'third-audience' ); ?></h4>
							<table class="ta-table ta-table-compact">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Method', 'third-audience' ); ?></th>
										<th style="text-align: right;"><?php esc_html_e( 'Count', 'third-audience' ); ?></th>
										<th style="text-align: right;"><?php esc_html_e( 'Avg Confidence', 'third-audience' ); ?></th>
									</tr>
								</thead>
								<tbody id="ta-bot-detection-tbody">
								</tbody>
							</table>
						</div>
					</div>

					<!-- Tab Content: Activity Log -->
					<div class="ta-bot-tab-content" id="ta-bot-activity" style="display: none;">
						<div style="max-height: 400px; overflow-y: auto;">
							<table class="ta-table ta-table-compact">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Timestamp', 'third-audience' ); ?></th>
										<th><?php esc_html_e( 'Page', 'third-audience' ); ?></th>
										<th><?php esc_html_e( 'IP', 'third-audience' ); ?></th>
										<th><?php esc_html_e( 'Cache', 'third-audience' ); ?></th>
										<th style="text-align: right;"><?php esc_html_e( 'Response', 'third-audience' ); ?></th>
									</tr>
								</thead>
								<tbody id="ta-bot-activity-tbody">
								</tbody>
							</table>
						</div>
					</div>

					<!-- Tab Content: Content Affinity -->
					<div class="ta-bot-tab-content" id="ta-bot-content" style="display: none;">
						<table class="ta-table ta-table-compact">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Page', 'third-audience' ); ?></th>
									<th style="text-align: right;"><?php esc_html_e( 'Visits', 'third-audience' ); ?></th>
									<th style="text-align: right;"><?php esc_html_e( 'Avg Words', 'third-audience' ); ?></th>
									<th style="text-align: right;"><?php esc_html_e( 'Avg Headings', 'third-audience' ); ?></th>
									<th style="text-align: right;"><?php esc_html_e( 'Freshness', 'third-audience' ); ?></th>
								</tr>
							</thead>
							<tbody id="ta-bot-content-tbody">
							</tbody>
						</table>
					</div>

					<!-- Tab Content: Business Impact -->
					<div class="ta-bot-tab-content" id="ta-bot-impact" style="display: none;">
						<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
							<div class="ta-crawl-budget-section">
								<h4><span class="dashicons dashicons-megaphone"></span> <?php esc_html_e( 'Citation Performance', 'third-audience' ); ?></h4>
								<table class="ta-table-borderless">
									<tbody>
										<tr><td><?php esc_html_e( 'Total Crawls:', 'third-audience' ); ?></td><td id="ta-bot-total-crawls">-</td></tr>
										<tr><td><?php esc_html_e( 'Total Citations:', 'third-audience' ); ?></td><td id="ta-bot-total-citations">-</td></tr>
										<tr><td><?php esc_html_e( 'Citation Rate:', 'third-audience' ); ?></td><td id="ta-bot-citation-rate">-</td></tr>
									</tbody>
								</table>
							</div>
							<div class="ta-crawl-budget-section">
								<h4><span class="dashicons dashicons-performance"></span> <?php esc_html_e( 'Response Time Distribution', 'third-audience' ); ?></h4>
								<table class="ta-table-borderless">
									<tbody id="ta-bot-response-dist">
									</tbody>
								</table>
							</div>
						</div>
					</div>

					<!-- Tab Content: Raw Data -->
					<div class="ta-bot-tab-content" id="ta-bot-raw" style="display: none;">
						<div class="ta-crawl-budget-section">
							<h4><span class="dashicons dashicons-admin-network"></span> <?php esc_html_e( 'IP Addresses', 'third-audience' ); ?></h4>
							<div style="max-height: 200px; overflow-y: auto;">
								<table class="ta-table ta-table-compact">
									<thead>
										<tr>
											<th><?php esc_html_e( 'IP Address', 'third-audience' ); ?></th>
											<th><?php esc_html_e( 'Country', 'third-audience' ); ?></th>
											<th style="text-align: right;"><?php esc_html_e( 'Visits', 'third-audience' ); ?></th>
											<th><?php esc_html_e( 'Verified', 'third-audience' ); ?></th>
										</tr>
									</thead>
									<tbody id="ta-bot-ip-tbody">
									</tbody>
								</table>
							</div>
						</div>
						<div class="ta-crawl-budget-section" style="margin-top: 20px;">
							<h4><span class="dashicons dashicons-info"></span> <?php esc_html_e( 'User Agent Strings', 'third-audience' ); ?></h4>
							<div id="ta-bot-user-agents" style="max-height: 150px; overflow-y: auto; font-family: monospace; font-size: 11px; background: #f5f5f7; padding: 12px; border-radius: 4px;">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


<script>
jQuery(document).ready(function($) {
	let customBotIndex = <?php echo count( $bot_config['custom_bots'] ); ?>;

	// Priority color mapping
	const priorityColors = {
		'high': '#007aff',
		'medium': '#34c759',
		'low': '#ff9500',
		'blocked': '#ff3b30'
	};

	// Bot colors
	const botColors = {
		'ClaudeBot': '#D97757',
		'GPTBot': '#10A37F',
		'ChatGPT-User': '#10A37F',
		'PerplexityBot': '#1FB6D0',
		'Bytespider': '#FF4458',
		'anthropic-ai': '#D97757',
		'Google-Extended': '#4285F4',
		'FacebookBot': '#1877F2'
	};

	// Initialize priority select colors
	$('.ta-priority-select').each(function() {
		const priority = $(this).data('priority');
		$(this).addClass('ta-priority-' + priority);
	});

	// Update priority select color on change
	$('.ta-priority-select').on('change', function() {
		const priority = $(this).val();
		$(this).removeClass('ta-priority-high ta-priority-medium ta-priority-low ta-priority-blocked');
		$(this).addClass('ta-priority-' + priority);
	});

	// Add custom bot pattern
	$('#add-custom-bot').on('click', function() {
		$('.ta-empty-row').remove();
		const row = `
			<tr>
				<td><input type="text" name="custom_bots[${customBotIndex}][pattern]" class="ta-form-input" placeholder="/YourBot/i"></td>
				<td><input type="text" name="custom_bots[${customBotIndex}][name]" class="ta-form-input" placeholder="Your Bot Name"></td>
				<td><button type="button" class="button button-secondary ta-btn remove-custom-bot"><?php esc_html_e( 'Remove', 'third-audience' ); ?></button></td>
			</tr>
		`;
		$('#custom-bots-list').append(row);
		customBotIndex++;
	});

	// Remove custom bot pattern
	$(document).on('click', '.remove-custom-bot', function() {
		const $tbody = $('#custom-bots-list');
		$(this).closest('tr').remove();
		if ($tbody.find('tr').length === 0) {
			$tbody.html('<tr class="ta-empty-row"><td colspan="3" class="ta-no-data"><?php esc_html_e( 'No custom bot patterns added yet.', 'third-audience' ); ?></td></tr>');
		}
	});

	// ==============================
	// Bot Diagnostic Modal
	// ==============================

	// Make Detected Bots table rows clickable
	$('.ta-card:has(h2:contains("Detected Bots")) .ta-table tbody tr').each(function() {
		const $row = $(this);
		const botName = $row.find('td:first strong').text();
		const botType = $row.find('.ta-code-badge').text();

		if (botName && botType) {
			$row.css('cursor', 'pointer');
			$row.attr('data-bot-type', botType);
			$row.attr('data-bot-name', botName);
			$row.attr('title', '<?php echo esc_js( __( 'Click to view bot diagnostics', 'third-audience' ) ); ?>');
		}
	});

	// Click handler for bot rows
	$(document).on('click', '.ta-table tbody tr[data-bot-type]', function(e) {
		if ($(e.target).is('select, option, input')) return;
		const botType = $(this).data('bot-type');
		const botName = $(this).data('bot-name');
		openBotModal(botType, botName);
	});

	// Close modal handlers
	$('.ta-bot-modal-close').on('click', function() {
		$('.ta-bot-modal-overlay').fadeOut(200);
	});

	$('.ta-bot-modal-overlay').on('click', function(e) {
		if (e.target === this) {
			$(this).fadeOut(200);
		}
	});

	$(document).on('keydown', function(e) {
		if (e.key === 'Escape' && $('.ta-bot-modal-overlay').is(':visible')) {
			$('.ta-bot-modal-overlay').fadeOut(200);
		}
	});

	// Tab navigation
	$(document).on('click', '.ta-bot-tab', function() {
		const tab = $(this).data('tab');
		$('.ta-bot-tab').removeClass('active');
		$(this).addClass('active');
		$('.ta-bot-tab-content').hide();
		$('#ta-bot-' + tab).show();
	});

	function openBotModal(botType, botName) {
		$('.ta-bot-modal-overlay').fadeIn(200);
		$('.ta-bot-loading').show();
		$('.ta-bot-content').hide();

		const color = botColors[botType] || '#999999';
		$('#ta-bot-modal-title').html(
			'<span style="display:inline-block;width:10px;height:10px;border-radius:50%;margin-right:8px;background:' + color + '"></span>' +
			escapeHtml(botName) + ' - <?php echo esc_js( __( 'Diagnostics', 'third-audience' ) ); ?>'
		);

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'ta_get_bot_details',
				nonce: '<?php echo wp_create_nonce( 'ta_bot_management' ); ?>',
				bot_type: botType,
				bot_name: botName
			},
			success: function(response) {
				if (response.success) {
					populateBotModal(response.data);
					$('.ta-bot-loading').hide();
					$('.ta-bot-content').show();
					$('.ta-bot-tab').removeClass('active').first().addClass('active');
					$('.ta-bot-tab-content').hide().first().show();
				} else {
					alert(response.data.message || '<?php echo esc_js( __( 'Error loading bot data', 'third-audience' ) ); ?>');
					$('.ta-bot-modal-overlay').fadeOut(200);
				}
			},
			error: function() {
				alert('<?php echo esc_js( __( 'Server error', 'third-audience' ) ); ?>');
				$('.ta-bot-modal-overlay').fadeOut(200);
			}
		});
	}

	function populateBotModal(data) {
		// Summary stats
		$('#ta-bot-total-visits').text(formatNumber(data.summary.total_visits));
		$('#ta-bot-unique-pages').text(formatNumber(data.summary.unique_pages));
		$('#ta-bot-unique-ips').text(formatNumber(data.summary.unique_ips));
		$('#ta-bot-cache-rate').text(Math.round(data.summary.cache_hit_rate || 0) + '%');
		$('#ta-bot-avg-response').text(Math.round(data.summary.avg_response_time || 0) + 'ms');

		// Behavior tab
		$('#ta-bot-pages-session').text(data.behavior.avg_pages_per_session ? parseFloat(data.behavior.avg_pages_per_session).toFixed(1) : '-');
		$('#ta-bot-duration').text(data.behavior.avg_session_duration ? formatDuration(data.behavior.avg_session_duration) : '-');
		$('#ta-bot-interval').text(data.behavior.avg_request_interval ? Math.round(data.behavior.avg_request_interval) + 's' : '-');

		const fpCount = parseInt(data.behavior.fingerprint_count) || 0;
		const robotsChecked = fpCount > 0 ? Math.round(data.behavior.robots_checked_count / fpCount * 100) : 0;
		const robotsRespected = fpCount > 0 ? Math.round(data.behavior.respects_robots_count / fpCount * 100) : 0;
		$('#ta-bot-robots-check').html(robotsChecked + '%' + (fpCount > 0 ? ' <span style="color:#86868b;">(' + data.behavior.robots_checked_count + '/' + fpCount + ')</span>' : ''));
		$('#ta-bot-robots-respect').html(robotsRespected + '%' + (fpCount > 0 ? ' <span style="color:#86868b;">(' + data.behavior.respects_robots_count + '/' + fpCount + ')</span>' : ''));

		// Detection methods
		let detectionHtml = '';
		if (data.detection_methods && data.detection_methods.length) {
			data.detection_methods.forEach(function(method) {
				detectionHtml += '<tr><td>' + escapeHtml(method.detection_method || 'legacy') + '</td><td style="text-align:right;">' + formatNumber(method.count) + '</td><td style="text-align:right;">' + (method.avg_confidence ? (parseFloat(method.avg_confidence) * 100).toFixed(0) + '%' : '-') + '</td></tr>';
			});
		} else {
			detectionHtml = '<tr><td colspan="3" class="ta-no-data"><?php echo esc_js( __( 'No detection data', 'third-audience' ) ); ?></td></tr>';
		}
		$('#ta-bot-detection-tbody').html(detectionHtml);

		// Activity log
		let activityHtml = '';
		if (data.recent_visits && data.recent_visits.length) {
			data.recent_visits.forEach(function(visit) {
				const cacheClass = 'ta-cache-' + (visit.cache_status || 'miss').toLowerCase();
				activityHtml += '<tr>';
				activityHtml += '<td style="white-space:nowrap;">' + formatDate(visit.visit_timestamp) + '</td>';
				activityHtml += '<td><a href="' + escapeHtml(visit.url) + '" target="_blank">' + escapeHtml(visit.post_title || visit.url.split('/').pop() || '-') + '</a></td>';
				activityHtml += '<td><code style="font-size:11px;">' + escapeHtml(visit.ip_address) + '</code>' + (visit.country_code ? ' <span style="color:#86868b;">' + visit.country_code + '</span>' : '') + '</td>';
				activityHtml += '<td><span class="' + cacheClass + '" style="padding:2px 6px;border-radius:3px;font-size:11px;">' + (visit.cache_status || '-') + '</span></td>';
				activityHtml += '<td style="text-align:right;">' + (visit.response_time ? visit.response_time + 'ms' : '-') + '</td>';
				activityHtml += '</tr>';
			});
		} else {
			activityHtml = '<tr><td colspan="5" class="ta-no-data"><?php echo esc_js( __( 'No activity data', 'third-audience' ) ); ?></td></tr>';
		}
		$('#ta-bot-activity-tbody').html(activityHtml);

		// Content affinity
		let contentHtml = '';
		if (data.top_pages && data.top_pages.length) {
			data.top_pages.forEach(function(page) {
				contentHtml += '<tr>';
				contentHtml += '<td><a href="' + escapeHtml(page.url) + '" target="_blank">' + escapeHtml(page.post_title || page.url.split('/').pop() || '-') + '</a></td>';
				contentHtml += '<td style="text-align:right;"><strong>' + formatNumber(page.visits) + '</strong></td>';
				contentHtml += '<td style="text-align:right;">' + (page.avg_word_count ? formatNumber(Math.round(page.avg_word_count)) : '-') + '</td>';
				contentHtml += '<td style="text-align:right;">' + (page.avg_headings ? parseFloat(page.avg_headings).toFixed(1) : '-') + '</td>';
				contentHtml += '<td style="text-align:right;">' + (page.avg_freshness_days ? Math.round(page.avg_freshness_days) + 'd' : '-') + '</td>';
				contentHtml += '</tr>';
			});
		} else {
			contentHtml = '<tr><td colspan="5" class="ta-no-data"><?php echo esc_js( __( 'No content data', 'third-audience' ) ); ?></td></tr>';
		}
		$('#ta-bot-content-tbody').html(contentHtml);

		// Business impact
		const totalCrawls = parseInt(data.citations.total_crawls) || 0;
		const totalCitations = parseInt(data.citations.total_citations) || 0;
		$('#ta-bot-total-crawls').text(formatNumber(totalCrawls));
		$('#ta-bot-total-citations').text(formatNumber(totalCitations));
		const citationRate = totalCrawls > 0 ? (totalCitations / totalCrawls * 100).toFixed(2) : 0;
		$('#ta-bot-citation-rate').text(citationRate + '%');

		// Response time distribution
		const responseLabels = {
			'fast_under_50': '<?php echo esc_js( __( 'Fast (<50ms)', 'third-audience' ) ); ?>',
			'good_50_100': '<?php echo esc_js( __( 'Good (50-100ms)', 'third-audience' ) ); ?>',
			'ok_100_200': '<?php echo esc_js( __( 'OK (100-200ms)', 'third-audience' ) ); ?>',
			'slow_over_200': '<?php echo esc_js( __( 'Slow (>200ms)', 'third-audience' ) ); ?>'
		};
		let responseHtml = '';
		if (data.response_distribution && data.response_distribution.length) {
			data.response_distribution.forEach(function(item) {
				responseHtml += '<tr><td>' + (responseLabels[item.speed_category] || item.speed_category) + '</td><td style="text-align:right;"><strong>' + formatNumber(item.count) + '</strong></td></tr>';
			});
		} else {
			responseHtml = '<tr><td colspan="2"><?php echo esc_js( __( 'No data', 'third-audience' ) ); ?></td></tr>';
		}
		$('#ta-bot-response-dist').html(responseHtml);

		// IP addresses
		let ipHtml = '';
		if (data.ip_data && data.ip_data.length) {
			data.ip_data.forEach(function(ip) {
				const verifiedIcon = ip.verified == 1 ? '<span class="dashicons dashicons-yes" style="color:#00a32a;"></span>' : '<span class="dashicons dashicons-minus" style="color:#86868b;"></span>';
				ipHtml += '<tr>';
				ipHtml += '<td><code style="font-size:11px;">' + escapeHtml(ip.ip_address) + '</code></td>';
				ipHtml += '<td>' + (ip.country_code || '-') + '</td>';
				ipHtml += '<td style="text-align:right;">' + formatNumber(ip.visit_count) + '</td>';
				ipHtml += '<td>' + verifiedIcon + '</td>';
				ipHtml += '</tr>';
			});
		} else {
			ipHtml = '<tr><td colspan="4" class="ta-no-data"><?php echo esc_js( __( 'No IP data', 'third-audience' ) ); ?></td></tr>';
		}
		$('#ta-bot-ip-tbody').html(ipHtml);

		// User agents
		let uaHtml = '';
		if (data.user_agents && data.user_agents.length) {
			data.user_agents.forEach(function(ua) {
				uaHtml += '<div style="margin-bottom:8px;padding:8px;background:white;border-radius:4px;word-break:break-all;">';
				uaHtml += '<button class="button button-small ta-copy-ua" data-ua="' + escapeHtml(ua) + '" style="float:right;margin-left:8px;">Copy</button>';
				uaHtml += escapeHtml(ua);
				uaHtml += '</div>';
			});
		} else {
			uaHtml = '<em><?php echo esc_js( __( 'No user agent data', 'third-audience' ) ); ?></em>';
		}
		$('#ta-bot-user-agents').html(uaHtml);
	}

	// Copy user agent
	$(document).on('click', '.ta-copy-ua', function() {
		const ua = $(this).data('ua');
		navigator.clipboard.writeText(ua).then(function() {
			alert('<?php echo esc_js( __( 'User agent copied to clipboard', 'third-audience' ) ); ?>');
		});
	});

	// Utility functions
	function formatNumber(num) {
		return parseInt(num || 0).toLocaleString();
	}

	function formatDuration(seconds) {
		seconds = parseInt(seconds) || 0;
		if (seconds < 60) return seconds + 's';
		if (seconds < 3600) return Math.round(seconds / 60) + 'm';
		return (seconds / 3600).toFixed(1) + 'h';
	}

	function formatDate(timestamp) {
		if (!timestamp) return '-';
		const date = new Date(timestamp);
		return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
	}

	function escapeHtml(text) {
		const div = document.createElement('div');
		div.textContent = text || '';
		return div.innerHTML;
	}
});
</script>
