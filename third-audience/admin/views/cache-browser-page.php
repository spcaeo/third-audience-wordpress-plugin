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

<div class="wrap ta-cache-browser">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Cache Browser', 'third-audience' ); ?>
		<span style="font-size: 0.6em; color: #646970; font-weight: 400;">v<?php echo esc_html( TA_VERSION ); ?></span>
	</h1>

	<p class="description">
		<?php esc_html_e( 'Browse and manage markdown cache entries generated for AI bot visits.', 'third-audience' ); ?>
	</p>

	<!-- Summary Cards -->
	<div class="ta-summary-cards">
		<div class="ta-summary-card">
			<div class="ta-summary-icon">
				<span class="dashicons dashicons-database"></span>
			</div>
			<div class="ta-summary-content">
				<h3><?php echo esc_html( number_format( $cache_stats['count'] ) ); ?></h3>
				<p><?php esc_html_e( 'Cached Items', 'third-audience' ); ?></p>
				<span class="ta-summary-meta"><?php esc_html_e( 'Total entries', 'third-audience' ); ?></span>
			</div>
		</div>

		<div class="ta-summary-card">
			<div class="ta-summary-icon">
				<span class="dashicons dashicons-archive"></span>
			</div>
			<div class="ta-summary-content">
				<h3><?php echo esc_html( $cache_stats['size_human'] ); ?></h3>
				<p><?php esc_html_e( 'Cache Size', 'third-audience' ); ?></p>
				<span class="ta-summary-meta"><?php esc_html_e( 'Total storage', 'third-audience' ); ?></span>
			</div>
		</div>

		<div class="ta-summary-card">
			<div class="ta-summary-icon">
				<span class="dashicons dashicons-performance"></span>
			</div>
			<div class="ta-summary-content">
				<h3><?php echo esc_html( round( $cache_stats['hit_rate'] ) ); ?>%</h3>
				<p><?php esc_html_e( 'Hit Rate', 'third-audience' ); ?></p>
				<span class="ta-summary-meta"><?php esc_html_e( 'Cache effectiveness', 'third-audience' ); ?></span>
			</div>
		</div>

		<div class="ta-summary-card">
			<div class="ta-summary-icon">
				<span class="dashicons dashicons-clock"></span>
			</div>
			<div class="ta-summary-content">
				<h3><?php echo esc_html( $expired_count ); ?></h3>
				<p><?php esc_html_e( 'Expired Items', 'third-audience' ); ?></p>
				<span class="ta-summary-meta"><?php esc_html_e( 'Need regeneration', 'third-audience' ); ?></span>
			</div>
		</div>
	</div>

	<!-- Cache Warmup Section -->
	<div class="ta-warmup-card">
		<div class="ta-warmup-header">
			<div class="ta-warmup-title">
				<h2><?php esc_html_e( 'Cache Warmup', 'third-audience' ); ?></h2>
				<p><?php esc_html_e( 'Pre-generate markdown cache for all published posts to ensure fast response times for AI bots.', 'third-audience' ); ?></p>
			</div>
			<div class="ta-warmup-stats">
				<div class="ta-warmup-stat">
					<span class="label"><?php esc_html_e( 'Coverage', 'third-audience' ); ?></span>
					<span class="value" id="ta-warmup-coverage">--</span>
				</div>
				<div class="ta-warmup-stat">
					<span class="label"><?php esc_html_e( 'Uncached', 'third-audience' ); ?></span>
					<span class="value" id="ta-warmup-uncached">--</span>
				</div>
			</div>
		</div>

		<div class="ta-warmup-body">
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
	</div>

	<!-- Filters Panel -->
	<div class="ta-cache-filters">
		<form method="get" action="" id="ta-filters-form">
			<input type="hidden" name="page" value="third-audience-cache-browser">

			<div class="ta-filter-row">
				<!-- Status Filter -->
				<div class="ta-filter-group">
					<label for="ta-filter-status"><?php esc_html_e( 'Status', 'third-audience' ); ?></label>
					<select name="status" id="ta-filter-status">
						<option value="all" <?php selected( $filters['status'], 'all' ); ?>><?php esc_html_e( 'All Status', 'third-audience' ); ?></option>
						<option value="active" <?php selected( $filters['status'], 'active' ); ?>><?php esc_html_e( 'Active', 'third-audience' ); ?></option>
						<option value="expired" <?php selected( $filters['status'], 'expired' ); ?>><?php esc_html_e( 'Expired', 'third-audience' ); ?></option>
					</select>
				</div>

				<!-- Size Range -->
				<div class="ta-filter-group">
					<label for="ta-filter-size-min"><?php esc_html_e( 'Size Range', 'third-audience' ); ?></label>
					<div class="ta-filter-range">
						<input type="number" name="size_min" id="ta-filter-size-min" placeholder="<?php esc_attr_e( 'Min (bytes)', 'third-audience' ); ?>" value="<?php echo esc_attr( $filters['size_min'] ); ?>">
						<span>-</span>
						<input type="number" name="size_max" id="ta-filter-size-max" placeholder="<?php esc_attr_e( 'Max (bytes)', 'third-audience' ); ?>" value="<?php echo esc_attr( $filters['size_max'] ); ?>">
					</div>
				</div>

				<!-- Date Range -->
				<div class="ta-filter-group">
					<label for="ta-filter-date-from"><?php esc_html_e( 'Date Range', 'third-audience' ); ?></label>
					<div class="ta-filter-range">
						<input type="date" name="date_from" id="ta-filter-date-from" value="<?php echo esc_attr( $filters['date_from'] ); ?>">
						<span>-</span>
						<input type="date" name="date_to" id="ta-filter-date-to" value="<?php echo esc_attr( $filters['date_to'] ); ?>">
					</div>
				</div>

				<!-- Search -->
				<div class="ta-filter-group">
					<label for="ta-cache-search"><?php esc_html_e( 'Search', 'third-audience' ); ?></label>
					<input type="text" id="ta-cache-search" name="search" placeholder="<?php esc_attr_e( 'Search URL...', 'third-audience' ); ?>" value="<?php echo esc_attr( $search ); ?>">
				</div>

				<div class="ta-filter-actions">
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Apply Filters', 'third-audience' ); ?></button>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-cache-browser' ) ); ?>" class="button">
						<?php esc_html_e( 'Reset', 'third-audience' ); ?>
					</a>
				</div>
			</div>
		</form>
	</div>

	<!-- Bulk Actions & Export -->
	<div class="ta-cache-actions">
		<div class="ta-actions-left">
			<button id="ta-bulk-delete-btn" class="button">
				<span class="dashicons dashicons-trash"></span>
				<?php esc_html_e( 'Delete Selected', 'third-audience' ); ?>
			</button>
			<button id="ta-clear-expired-btn" class="button">
				<span class="dashicons dashicons-dismiss"></span>
				<?php esc_html_e( 'Clear Expired', 'third-audience' ); ?>
			</button>
		</div>
		<div class="ta-actions-right">
			<div class="ta-export-dropdown">
				<button type="button" class="button ta-export-dropdown-toggle">
					<span class="dashicons dashicons-download"></span>
					<?php esc_html_e( 'Export', 'third-audience' ); ?>
					<span class="dashicons dashicons-arrow-down-alt2"></span>
				</button>
				<div class="ta-export-dropdown-menu">
					<button id="ta-export-selected-btn" class="ta-export-option" title="<?php esc_attr_e( 'Export only selected cache entries', 'third-audience' ); ?>">
						<span class="dashicons dashicons-yes-alt"></span>
						<div>
							<strong><?php esc_html_e( 'Export Selected', 'third-audience' ); ?></strong>
							<span><?php esc_html_e( 'Only checked items', 'third-audience' ); ?></span>
						</div>
					</button>
					<button id="ta-export-filtered-btn" class="ta-export-option" title="<?php esc_attr_e( 'Export current filtered and sorted view', 'third-audience' ); ?>">
						<span class="dashicons dashicons-filter"></span>
						<div>
							<strong><?php esc_html_e( 'Export View', 'third-audience' ); ?></strong>
							<span><?php esc_html_e( 'Current filtered view', 'third-audience' ); ?></span>
						</div>
					</button>
					<button id="ta-export-all-btn" class="ta-export-option" title="<?php esc_attr_e( 'Export all cache entries', 'third-audience' ); ?>">
						<span class="dashicons dashicons-database"></span>
						<div>
							<strong><?php esc_html_e( 'Export All', 'third-audience' ); ?></strong>
							<span><?php esc_html_e( 'All cache entries', 'third-audience' ); ?></span>
						</div>
					</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Cache Table -->
	<div class="ta-cache-table-card">
		<table class="wp-list-table widefat fixed striped ta-cache-table">
			<thead>
				<tr>
					<th class="check-column"><input type="checkbox" id="ta-select-all"></th>
					<th class="ta-sortable <?php echo ( 'url' === $orderby ) ? 'sorted ' . strtolower( $order ) : ''; ?>" data-column="url">
						<?php esc_html_e( 'URL', 'third-audience' ); ?>
						<span class="ta-sort-indicator">
							<span class="dashicons dashicons-arrow-up"></span>
							<span class="dashicons dashicons-arrow-down"></span>
						</span>
					</th>
					<th class="ta-sortable <?php echo ( 'size' === $orderby ) ? 'sorted ' . strtolower( $order ) : ''; ?>" data-column="size">
						<?php esc_html_e( 'Size', 'third-audience' ); ?>
						<span class="ta-sort-indicator">
							<span class="dashicons dashicons-arrow-up"></span>
							<span class="dashicons dashicons-arrow-down"></span>
						</span>
					</th>
					<th class="ta-sortable <?php echo ( 'expiration' === $orderby ) ? 'sorted ' . strtolower( $order ) : ''; ?>" data-column="expiration">
						<?php esc_html_e( 'Expires', 'third-audience' ); ?>
						<span class="ta-sort-indicator">
							<span class="dashicons dashicons-arrow-up"></span>
							<span class="dashicons dashicons-arrow-down"></span>
						</span>
					</th>
					<th><?php esc_html_e( 'Actions', 'third-audience' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $cache_entries ) ) : ?>
					<tr>
						<td colspan="5" class="ta-no-data">
							<?php esc_html_e( 'No cache entries found.', 'third-audience' ); ?>
						</td>
					</tr>
				<?php else : ?>
					<?php foreach ( $cache_entries as $entry ) : ?>
						<tr>
							<td><input type="checkbox" class="ta-cache-checkbox" value="<?php echo esc_attr( $entry['cache_key'] ); ?>"></td>
							<td class="ta-cache-url">
								<strong><?php echo esc_html( $entry['title'] ); ?></strong><br>
								<small><?php echo esc_html( $entry['url'] ); ?></small>
							</td>
							<td><strong><?php echo esc_html( $entry['size_human'] ); ?></strong></td>
							<td><?php echo esc_html( $entry['expires_in'] ); ?></td>
							<td class="ta-cache-row-actions">
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
	</div>

	<!-- Cache Content Modal -->
	<div id="ta-modal" class="ta-cache-content-modal">
		<div class="ta-modal-content">
			<div class="ta-modal-header">
				<h2><?php esc_html_e( 'Cache Content', 'third-audience' ); ?></h2>
				<button class="ta-modal-close" id="ta-modal-close" aria-label="<?php esc_attr_e( 'Close', 'third-audience' ); ?>">
					<span class="dashicons dashicons-no-alt"></span>
				</button>
			</div>
			<div class="ta-modal-body">
				<pre id="ta-modal-content"></pre>
			</div>
		</div>
	</div>
</div>
