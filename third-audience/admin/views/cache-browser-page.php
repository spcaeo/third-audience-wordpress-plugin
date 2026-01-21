<?php
/**
 * Cache Browser Page
 *
 * @package ThirdAudience
 * @since   1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Cache Browser', 'third-audience' ); ?></h1>

	<!-- Help Panel -->
	<div class="ta-help-panel">
		<h3><span class="dashicons dashicons-info"></span> <?php esc_html_e( 'What is Cache?', 'third-audience' ); ?></h3>
		<p><?php esc_html_e( 'Cache stores "saved copies" of your pages in markdown format. When AI bots visit, they get instant cached versions instead of slow conversions.', 'third-audience' ); ?></p>
		<ul>
			<li><strong><?php esc_html_e( 'Faster:', 'third-audience' ); ?></strong> <?php esc_html_e( 'Cached pages load instantly', 'third-audience' ); ?></li>
			<li><strong><?php esc_html_e( 'Auto-update:', 'third-audience' ); ?></strong> <?php esc_html_e( 'Cache clears when you edit posts', 'third-audience' ); ?></li>
		</ul>
	</div>

	<!-- Summary Cards -->
	<div class="ta-summary-cards">
		<div class="ta-summary-card">
			<div class="ta-summary-icon"><span class="dashicons dashicons-database"></span></div>
			<div class="ta-summary-info">
				<h3><?php echo esc_html( number_format( $cache_stats['count'] ) ); ?></h3>
				<p><?php esc_html_e( 'Cached Items', 'third-audience' ); ?></p>
			</div>
		</div>

		<div class="ta-summary-card">
			<div class="ta-summary-icon"><span class="dashicons dashicons-archive"></span></div>
			<div class="ta-summary-info">
				<h3><?php echo esc_html( $cache_stats['size_human'] ); ?></h3>
				<p><?php esc_html_e( 'Cache Size', 'third-audience' ); ?></p>
			</div>
		</div>

		<div class="ta-summary-card">
			<div class="ta-summary-icon"><span class="dashicons dashicons-performance"></span></div>
			<div class="ta-summary-info">
				<h3><?php echo esc_html( round( $cache_stats['hit_rate'] ) ); ?>%</h3>
				<p><?php esc_html_e( 'Hit Rate', 'third-audience' ); ?></p>
			</div>
		</div>

		<div class="ta-summary-card">
			<div class="ta-summary-icon"><span class="dashicons dashicons-clock"></span></div>
			<div class="ta-summary-info">
				<h3><?php echo esc_html( $expired_count ); ?></h3>
				<p><?php esc_html_e( 'Expired', 'third-audience' ); ?></p>
			</div>
		</div>
	</div>

	<!-- Cache Warmup Section -->
	<div class="ta-warmup-section">
		<h3><span class="dashicons dashicons-performance"></span> <?php esc_html_e( 'Cache Warmup', 'third-audience' ); ?></h3>
		<p class="description"><?php esc_html_e( 'Pre-generate markdown cache for all published posts to ensure fast response times for AI bots.', 'third-audience' ); ?></p>

		<div class="ta-warmup-stats">
			<div class="ta-warmup-stat">
				<span class="label"><?php esc_html_e( 'Cache Coverage:', 'third-audience' ); ?></span>
				<span class="value" id="ta-warmup-coverage">--</span>
			</div>
			<div class="ta-warmup-stat">
				<span class="label"><?php esc_html_e( 'Uncached Posts:', 'third-audience' ); ?></span>
				<span class="value" id="ta-warmup-uncached">--</span>
			</div>
		</div>

		<div class="ta-warmup-controls">
			<button id="ta-warmup-all-btn" class="button button-primary">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Warm All Cache', 'third-audience' ); ?>
			</button>
			<button id="ta-warmup-cancel-btn" class="button" style="display:none;">
				<?php esc_html_e( 'Cancel', 'third-audience' ); ?>
			</button>
		</div>

		<div id="ta-warmup-progress" class="ta-warmup-progress" style="display:none;">
			<div class="progress-bar">
				<div class="progress-fill" style="width: 0%"></div>
			</div>
			<div class="progress-text">
				<span id="ta-warmup-status"><?php esc_html_e( 'Starting...', 'third-audience' ); ?></span>
				<span id="ta-warmup-percentage">0%</span>
			</div>
		</div>
	</div>

	<!-- Bulk Actions -->
	<div class="ta-bulk-actions-bar">
		<div>
			<button id="ta-bulk-delete-btn" class="button"><?php esc_html_e( 'Delete Selected', 'third-audience' ); ?></button>
			<button id="ta-clear-expired-btn" class="button"><?php esc_html_e( 'Clear Expired', 'third-audience' ); ?></button>
		</div>
		<div>
			<input type="search" id="ta-cache-search" placeholder="<?php esc_attr_e( 'Search URL...', 'third-audience' ); ?>" value="<?php echo esc_attr( $search ); ?>">
			<button class="button" onclick="location.href='?page=third-audience-cache-browser&search='+document.getElementById('ta-cache-search').value"><?php esc_html_e( 'Search', 'third-audience' ); ?></button>
		</div>
	</div>

	<!-- Cache Table -->
	<table class="wp-list-table widefat fixed striped ta-cache-table">
		<thead>
			<tr>
				<th class="check-column"><input type="checkbox" id="ta-select-all"></th>
				<th><?php esc_html_e( 'URL', 'third-audience' ); ?></th>
				<th><?php esc_html_e( 'Size', 'third-audience' ); ?></th>
				<th><?php esc_html_e( 'Expires', 'third-audience' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'third-audience' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $cache_entries ) ) : ?>
				<tr><td colspan="5"><?php esc_html_e( 'No cache entries found.', 'third-audience' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $cache_entries as $entry ) : ?>
					<tr>
						<td><input type="checkbox" class="ta-cache-checkbox" value="<?php echo esc_attr( $entry['cache_key'] ); ?>"></td>
						<td>
							<strong><?php echo esc_html( $entry['title'] ); ?></strong><br>
							<small><?php echo esc_html( $entry['url'] ); ?></small>
						</td>
						<td><?php echo esc_html( $entry['size_human'] ); ?></td>
						<td><?php echo esc_html( $entry['expires_in'] ); ?></td>
						<td class="ta-cache-actions">
							<button class="button button-small ta-view-btn" data-key="<?php echo esc_attr( $entry['cache_key'] ); ?>"><?php esc_html_e( 'View', 'third-audience' ); ?></button>
							<?php if ( $entry['post_id'] ) : ?>
								<button class="button button-small ta-regen-btn" data-id="<?php echo esc_attr( $entry['post_id'] ); ?>"><?php esc_html_e( 'Regenerate', 'third-audience' ); ?></button>
							<?php endif; ?>
							<button class="button button-small ta-delete-btn" data-key="<?php echo esc_attr( $entry['cache_key'] ); ?>"><?php esc_html_e( 'Delete', 'third-audience' ); ?></button>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>

	<!-- Modal -->
	<div id="ta-modal" class="ta-modal-overlay">
		<div class="ta-modal-content">
			<h2><?php esc_html_e( 'Cache Content', 'third-audience' ); ?></h2>
			<pre id="ta-modal-content"></pre>
			<button class="button" id="ta-modal-close"><?php esc_html_e( 'Close', 'third-audience' ); ?></button>
		</div>
	</div>
</div>
