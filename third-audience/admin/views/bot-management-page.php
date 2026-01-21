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

<div class="wrap">
	<h1><?php esc_html_e( 'Bot Management', 'third-audience' ); ?></h1>
	<p class="description">
		<?php esc_html_e( 'Configure which bots to track and block. All bots accessing .md URLs are tracked by default.', 'third-audience' ); ?>
	</p>

	<?php
	// Show success message if settings were updated.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['updated'] ) && 'true' === $_GET['updated'] ) :
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Bot configuration saved successfully.', 'third-audience' ); ?></p>
		</div>
		<?php
	endif;
	?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'ta_ta_save_bot_config', 'ta_bot_config_nonce' ); ?>
		<input type="hidden" name="action" value="ta_save_bot_config">

		<!-- Global Settings -->
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="track_unknown">
						<?php esc_html_e( 'Track Unknown Bots', 'third-audience' ); ?>
					</label>
				</th>
				<td>
					<label>
						<input
							type="checkbox"
							id="track_unknown"
							name="track_unknown"
							value="1"
							<?php checked( $bot_config['track_unknown'], true ); ?>
						>
						<?php esc_html_e( 'Track bots not in the known bot list (categorized as "Unknown Bot")', 'third-audience' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'Recommended: Leave enabled to track all bot activity including new AI bots and custom crawlers.', 'third-audience' ); ?>
					</p>
				</td>
			</tr>
		</table>

		<!-- Bot Statistics and Management -->
		<h2><?php esc_html_e( 'Detected Bots', 'third-audience' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'Manage bots that have accessed your site. Block bots to prevent them from accessing markdown content.', 'third-audience' ); ?>
		</p>

		<table class="wp-list-table widefat fixed striped">
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
						<td colspan="10" style="text-align: center; padding: 40px;">
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
							'high'    => '#0073aa',
							'medium'  => '#46b450',
							'low'     => '#ffb900',
							'blocked' => '#dc3232',
						);
						$priority_color = isset( $priority_colors[ $current_priority ] ) ? $priority_colors[ $current_priority ] : '#666';
						?>
						<tr>
							<td><strong><?php echo esc_html( $bot['bot_name'] ); ?></strong></td>
							<td><code><?php echo esc_html( $bot['bot_type'] ); ?></code></td>
							<td>
								<select name="bot_priorities[<?php echo esc_attr( $bot['bot_type'] ); ?>]" class="ta-priority-select" style="color: <?php echo esc_attr( $priority_color ); ?>; font-weight: 600;">
									<option value="high" <?php selected( $current_priority, 'high' ); ?> style="color: #0073aa;">
										<?php esc_html_e( 'High', 'third-audience' ); ?>
									</option>
									<option value="medium" <?php selected( $current_priority, 'medium' ); ?> style="color: #46b450;">
										<?php esc_html_e( 'Medium', 'third-audience' ); ?>
									</option>
									<option value="low" <?php selected( $current_priority, 'low' ); ?> style="color: #ffb900;">
										<?php esc_html_e( 'Low', 'third-audience' ); ?>
									</option>
									<option value="blocked" <?php selected( $current_priority, 'blocked' ); ?> style="color: #dc3232;">
										<?php esc_html_e( 'Blocked', 'third-audience' ); ?>
									</option>
								</select>
							</td>
							<td>
								<?php
								if ( 'blocked' === $current_priority ) {
									echo '<span style="color: #dc3232; font-weight: 600;">403 Forbidden</span>';
								} else {
									echo esc_html( human_time_diff( 0, $cache_ttl ) );
								}
								?>
							</td>
							<td><?php echo number_format_i18n( $bot['total_visits'] ); ?></td>
							<td><?php echo number_format_i18n( $bot['unique_pages'] ); ?></td>
							<td><?php echo number_format_i18n( $bot['unique_ips'] ); ?></td>
							<td>
								<?php
								if ( ! empty( $bot['countries'] ) ) {
									$countries = explode( ', ', $bot['countries'] );
									$countries = array_filter( $countries ); // Remove empty values.
									if ( ! empty( $countries ) ) {
										echo esc_html( implode( ', ', $countries ) );
										echo ' <span style="color: #666;">(' . count( $countries ) . ')</span>';
									} else {
										echo '—';
									}
								} else {
									echo '—';
								}
								?>
							</td>
							<td><?php echo round( $bot['avg_response_time'] ); ?>ms</td>
							<td>
								<?php
								echo esc_html(
									human_time_diff(
										strtotime( $bot['last_seen'] ),
										current_time( 'timestamp' )
									)
								);
								?>
								<?php esc_html_e( 'ago', 'third-audience' ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>

		<!-- Custom Bot Patterns -->
		<h2 style="margin-top: 40px;"><?php esc_html_e( 'Custom Bot Patterns', 'third-audience' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'Add custom bot patterns to identify and name specific bots. Uses regular expressions to match User-Agent strings.', 'third-audience' ); ?>
		</p>

		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'Pattern', 'third-audience' ); ?></th>
				<th><?php esc_html_e( 'Bot Name', 'third-audience' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'third-audience' ); ?></th>
			</tr>
			<tbody id="custom-bots-list">
				<?php if ( ! empty( $bot_config['custom_bots'] ) ) : ?>
					<?php foreach ( $bot_config['custom_bots'] as $index => $custom_bot ) : ?>
						<tr>
							<td>
								<input
									type="text"
									name="custom_bots[<?php echo esc_attr( $index ); ?>][pattern]"
									value="<?php echo esc_attr( $custom_bot['pattern'] ); ?>"
									class="regular-text"
									placeholder="/YourBot/i"
								>
							</td>
							<td>
								<input
									type="text"
									name="custom_bots[<?php echo esc_attr( $index ); ?>][name]"
									value="<?php echo esc_attr( $custom_bot['name'] ); ?>"
									class="regular-text"
									placeholder="Your Bot Name"
								>
							</td>
							<td>
								<button type="button" class="button remove-custom-bot">
									<?php esc_html_e( 'Remove', 'third-audience' ); ?>
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>

		<p>
			<button type="button" id="add-custom-bot" class="button">
				<?php esc_html_e( '+ Add Custom Bot Pattern', 'third-audience' ); ?>
			</button>
		</p>

		<p class="submit">
			<button type="submit" class="button button-primary">
				<?php esc_html_e( 'Save Bot Configuration', 'third-audience' ); ?>
			</button>
		</p>
	</form>
</div>

<style>
.ta-bot-status {
	display: inline-block;
	padding: 4px 12px;
	border-radius: 12px;
	font-size: 12px;
	font-weight: 600;
	text-transform: uppercase;
}
.ta-bot-allowed {
	background: #d4edda;
	color: #155724;
}
.ta-bot-blocked {
	background: #f8d7da;
	color: #721c24;
}
.ta-priority-select {
	padding: 4px 8px;
	border-radius: 4px;
	border: 1px solid #ddd;
	font-size: 13px;
	min-width: 100px;
}
.ta-priority-select:focus {
	border-color: #0073aa;
	outline: none;
	box-shadow: 0 0 0 1px #0073aa;
}
</style>

<script>
jQuery(document).ready(function($) {
	let customBotIndex = <?php echo count( $bot_config['custom_bots'] ); ?>;

	// Priority color mapping
	const priorityColors = {
		'high': '#0073aa',
		'medium': '#46b450',
		'low': '#ffb900',
		'blocked': '#dc3232'
	};

	// Update priority select color on change
	$('.ta-priority-select').on('change', function() {
		const priority = $(this).val();
		$(this).css('color', priorityColors[priority]);
	});

	// Add custom bot pattern
	$('#add-custom-bot').on('click', function() {
		const row = `
			<tr>
				<td>
					<input
						type="text"
						name="custom_bots[${customBotIndex}][pattern]"
						class="regular-text"
						placeholder="/YourBot/i"
					>
				</td>
				<td>
					<input
						type="text"
						name="custom_bots[${customBotIndex}][name]"
						class="regular-text"
						placeholder="Your Bot Name"
					>
				</td>
				<td>
					<button type="button" class="button remove-custom-bot">
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
		$(this).closest('tr').remove();
	});
});
</script>
