<?php
/**
 * Cache Browser Page v2.0 - Clean Modern Design
 *
 * @package ThirdAudience
 * @since   2.1.0
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
		<?php esc_html_e( 'Manage markdown cache for AI bot responses', 'third-audience' ); ?>
	</p>

	<!-- Hero Metrics -->
	<div class="ta-hero-metrics">
		<div class="ta-hero-card">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-database"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Cached Items', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo number_format( $cache_stats['count'] ); ?></div>
				<div class="ta-hero-meta"><?php esc_html_e( 'Total entries', 'third-audience' ); ?></div>
			</div>
		</div>

		<div class="ta-hero-card">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-archive"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Cache Size', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo esc_html( $cache_stats['size_human'] ); ?></div>
				<div class="ta-hero-meta"><?php esc_html_e( 'Total storage', 'third-audience' ); ?></div>
			</div>
		</div>

		<div class="ta-hero-card">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-performance"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Hit Rate', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo esc_html( round( $cache_stats['hit_rate'] ) ); ?>%</div>
				<div class="ta-hero-meta"><?php esc_html_e( 'Cache effectiveness', 'third-audience' ); ?></div>
			</div>
		</div>

		<div class="ta-hero-card">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-clock"></span>
			</div>
			<div class="ta-hero-content">
				<div class="ta-hero-label"><?php esc_html_e( 'Expired Items', 'third-audience' ); ?></div>
				<div class="ta-hero-value"><?php echo esc_html( $expired_count ); ?></div>
				<div class="ta-hero-meta"><?php esc_html_e( 'Need regeneration', 'third-audience' ); ?></div>
			</div>
		</div>
	</div>

	<!-- Pre-generate Cache Card -->
	<div class="ta-card">
		<div class="ta-card-header">
			<h2><?php esc_html_e( 'Pre-generate Cache', 'third-audience' ); ?></h2>
			<div class="ta-card-actions">
				<button id="ta-warmup-all-btn" class="button button-primary"><?php esc_html_e( 'Generate All', 'third-audience' ); ?></button>
				<button id="ta-warmup-cancel-btn" class="button" style="display:none;"><?php esc_html_e( 'Cancel', 'third-audience' ); ?></button>
			</div>
		</div>
		<div class="ta-card-body">
			<p><?php esc_html_e( 'Create saved copies of all your pages now, so AI bots get instant responses later.', 'third-audience' ); ?></p>

			<div class="ta-warmup-stats-inline">
				<div class="ta-warmup-stat">
					<span class="label"><?php esc_html_e( 'Coverage:', 'third-audience' ); ?></span>
					<span class="value" id="ta-warmup-coverage">--</span>
				</div>
				<div class="ta-warmup-stat">
					<span class="label"><?php esc_html_e( 'Uncached:', 'third-audience' ); ?></span>
					<span class="value" id="ta-warmup-uncached">--</span>
				</div>
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

	<!-- Filters Section -->
	<div class="ta-filters-section">
		<button type="button" class="ta-filters-toggle">
			<span class="dashicons dashicons-filter"></span>
			<?php esc_html_e( 'Filters & Search', 'third-audience' ); ?>
			<span class="dashicons dashicons-arrow-down-alt2"></span>
		</button>

		<div class="ta-filters-content" style="display:none;">
			<form method="get" action="" id="ta-filters-form">
				<input type="hidden" name="page" value="third-audience-cache-browser">

				<div class="ta-filter-grid">
					<!-- Status Filter -->
					<div class="ta-filter-item">
						<label for="ta-filter-status"><?php esc_html_e( 'Status', 'third-audience' ); ?></label>
						<select name="status" id="ta-filter-status">
							<option value="all" <?php selected( $filters['status'], 'all' ); ?>><?php esc_html_e( 'All Status', 'third-audience' ); ?></option>
							<option value="active" <?php selected( $filters['status'], 'active' ); ?>><?php esc_html_e( 'Active', 'third-audience' ); ?></option>
							<option value="expired" <?php selected( $filters['status'], 'expired' ); ?>><?php esc_html_e( 'Expired', 'third-audience' ); ?></option>
						</select>
					</div>

					<!-- Search -->
					<div class="ta-filter-item">
						<label for="ta-cache-search"><?php esc_html_e( 'Search URL', 'third-audience' ); ?></label>
						<input type="text" id="ta-cache-search" name="search" placeholder="<?php esc_attr_e( 'Search...', 'third-audience' ); ?>" value="<?php echo esc_attr( $search ); ?>">
					</div>

					<!-- Date Range -->
					<div class="ta-filter-item">
						<label for="ta-filter-date-from"><?php esc_html_e( 'Date Range', 'third-audience' ); ?></label>
						<div class="ta-date-range">
							<input type="date" name="date_from" id="ta-filter-date-from" value="<?php echo esc_attr( $filters['date_from'] ); ?>">
							<span>-</span>
							<input type="date" name="date_to" id="ta-filter-date-to" value="<?php echo esc_attr( $filters['date_to'] ); ?>">
						</div>
					</div>

					<!-- Actions -->
					<div class="ta-filter-item ta-filter-actions">
						<label>&nbsp;</label>
						<div>
							<button type="submit" class="button button-primary"><?php esc_html_e( 'Apply', 'third-audience' ); ?></button>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-cache-browser' ) ); ?>" class="button"><?php esc_html_e( 'Reset', 'third-audience' ); ?></a>
							<button type="button" id="ta-clear-expired-btn" class="button" style="color: #d63638;"><?php esc_html_e( 'Clear Expired', 'third-audience' ); ?></button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>

	<!-- Cache Entries Table -->
	<div class="ta-card">
		<div class="ta-card-header">
			<h2><?php esc_html_e( 'Cache Entries', 'third-audience' ); ?></h2>
			<div class="ta-card-actions">
				<button id="ta-bulk-delete-btn" class="button"><?php esc_html_e( 'Delete Selected', 'third-audience' ); ?></button>
				<button type="button" class="button ta-export-toggle"><?php esc_html_e( 'Export', 'third-audience' ); ?></button>
			</div>
		</div>
		<div class="ta-card-body">
			<table class="ta-table">
				<thead>
					<tr>
						<th style="width: 30px;"><input type="checkbox" id="ta-select-all"></th>
						<th class="ta-sortable <?php echo ( 'url' === $orderby ) ? 'sorted ' . strtolower( $order ) : ''; ?>" data-column="url"><?php esc_html_e( 'URL', 'third-audience' ); ?></th>
						<th style="width: 100px;" class="ta-sortable <?php echo ( 'size' === $orderby ) ? 'sorted ' . strtolower( $order ) : ''; ?>" data-column="size"><?php esc_html_e( 'Size', 'third-audience' ); ?></th>
						<th style="width: 120px;" class="ta-sortable <?php echo ( 'expiration' === $orderby ) ? 'sorted ' . strtolower( $order ) : ''; ?>" data-column="expiration"><?php esc_html_e( 'Expires', 'third-audience' ); ?></th>
						<th style="width: 250px;"><?php esc_html_e( 'Actions', 'third-audience' ); ?></th>
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
									<small style="color: #86868b;"><?php echo esc_html( $entry['url'] ); ?></small>
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
	</div>

	<!-- Export Dropdown (hidden, shown via JS) -->
	<div class="ta-export-dropdown-menu" style="display:none;">
		<button id="ta-export-selected-btn" class="ta-export-option">
			<div>
				<strong><?php esc_html_e( 'Export Selected', 'third-audience' ); ?></strong>
				<span><?php esc_html_e( 'Only checked items', 'third-audience' ); ?></span>
			</div>
		</button>
		<button id="ta-export-filtered-btn" class="ta-export-option">
			<div>
				<strong><?php esc_html_e( 'Export View', 'third-audience' ); ?></strong>
				<span><?php esc_html_e( 'Current filtered view', 'third-audience' ); ?></span>
			</div>
		</button>
		<button id="ta-export-all-btn" class="ta-export-option">
			<div>
				<strong><?php esc_html_e( 'Export All', 'third-audience' ); ?></strong>
				<span><?php esc_html_e( 'All cache entries', 'third-audience' ); ?></span>
			</div>
		</button>
	</div>

	<!-- Cache Content Modal -->
	<div id="ta-modal" class="ta-cache-content-modal">
		<div class="ta-modal-content">
			<div class="ta-modal-header">
				<h2><?php esc_html_e( 'Cache Content', 'third-audience' ); ?></h2>
				<button class="ta-modal-close" id="ta-modal-close" aria-label="<?php esc_attr_e( 'Close', 'third-audience' ); ?>">
					×
				</button>
			</div>
			<div class="ta-modal-body">
				<pre id="ta-modal-content"></pre>
			</div>
		</div>
	</div>
</div>
