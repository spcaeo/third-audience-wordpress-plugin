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

	// Initialize priority select colors
	$('.ta-priority-select').each(function() {
		const priority = $(this).data('priority');
		$(this).addClass('ta-priority-' + priority);
	});

	// Update priority select color on change
	$('.ta-priority-select').on('change', function() {
		const priority = $(this).val();
		// Remove all priority classes
		$(this).removeClass('ta-priority-high ta-priority-medium ta-priority-low ta-priority-blocked');
		// Add new priority class
		$(this).addClass('ta-priority-' + priority);
	});

	// Add custom bot pattern
	$('#add-custom-bot').on('click', function() {
		// Remove empty row if it exists
		$('.ta-empty-row').remove();

		const row = `
			<tr>
				<td>
					<input
						type="text"
						name="custom_bots[${customBotIndex}][pattern]"
						class="ta-form-input"
						placeholder="/YourBot/i"
					>
				</td>
				<td>
					<input
						type="text"
						name="custom_bots[${customBotIndex}][name]"
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
		`;
		$('#custom-bots-list').append(row);
		customBotIndex++;
	});

	// Remove custom bot pattern
	$(document).on('click', '.remove-custom-bot', function() {
		const $tbody = $('#custom-bots-list');
		$(this).closest('tr').remove();

		// If no rows left, show empty state
		if ($tbody.find('tr').length === 0) {
			$tbody.html(`
				<tr class="ta-empty-row">
					<td colspan="3" class="ta-no-data">
						<?php esc_html_e( 'No custom bot patterns added yet. Click "Add Custom Bot Pattern" to get started.', 'third-audience' ); ?>
					</td>
				</tr>
			`);
		}
	});
});
</script>
