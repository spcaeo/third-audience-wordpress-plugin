<?php
/**
 * Competitor Benchmarking - Competitors Management Tab
 *
 * @package ThirdAudience
 * @since   3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="ta-competitors-section">
	<div class="ta-section-header">
		<h2><?php esc_html_e( 'Manage Competitors', 'third-audience' ); ?></h2>
		<button type="button" class="button button-primary" id="ta-add-competitor-btn">
			<?php esc_html_e( 'Add Competitor', 'third-audience' ); ?>
		</button>
	</div>

	<!-- Add Competitor Form (Hidden by default) -->
	<div class="ta-card ta-add-competitor-form" style="display: none;">
		<h3><?php esc_html_e( 'Add New Competitor', 'third-audience' ); ?></h3>
		<form id="ta-competitor-form">
			<table class="form-table">
				<tr>
					<th><label for="competitor_url"><?php esc_html_e( 'Competitor URL', 'third-audience' ); ?></label></th>
					<td>
						<input type="url" id="competitor_url" name="competitor_url" class="regular-text" required
						       placeholder="https://example.com">
						<p class="description"><?php esc_html_e( 'Full URL of the competitor website.', 'third-audience' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="competitor_name"><?php esc_html_e( 'Competitor Name', 'third-audience' ); ?></label></th>
					<td>
						<input type="text" id="competitor_name" name="competitor_name" class="regular-text" required
						       placeholder="Competitor Inc.">
						<p class="description"><?php esc_html_e( 'Display name for this competitor.', 'third-audience' ); ?></p>
					</td>
				</tr>
			</table>
			<p class="submit">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Add Competitor', 'third-audience' ); ?></button>
				<button type="button" class="button" id="ta-cancel-add-competitor"><?php esc_html_e( 'Cancel', 'third-audience' ); ?></button>
			</p>
		</form>
	</div>

	<!-- Competitors List -->
	<?php if ( ! empty( $competitors ) ) : ?>
	<div class="ta-card">
		<h3><?php esc_html_e( 'Tracked Competitors', 'third-audience' ); ?></h3>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Competitor', 'third-audience' ); ?></th>
					<th><?php esc_html_e( 'URL', 'third-audience' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'third-audience' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $competitors as $competitor ) : ?>
				<tr>
					<td><strong><?php echo esc_html( $competitor['name'] ); ?></strong></td>
					<td>
						<a href="<?php echo esc_url( $competitor['url'] ); ?>" target="_blank" rel="noopener">
							<?php echo esc_html( $competitor['url'] ); ?>
						</a>
					</td>
					<td>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-competitor-benchmarking&tab=test&competitor_url=' . urlencode( $competitor['url'] ) ) ); ?>"
						   class="button button-small">
							<?php esc_html_e( 'Run Test', 'third-audience' ); ?>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-competitor-benchmarking&tab=results&competitor_url=' . urlencode( $competitor['url'] ) ) ); ?>"
						   class="button button-small">
							<?php esc_html_e( 'View Results', 'third-audience' ); ?>
						</a>
						<button type="button" class="button button-small button-link-delete ta-delete-competitor"
						        data-url="<?php echo esc_attr( $competitor['url'] ); ?>"
						        data-name="<?php echo esc_attr( $competitor['name'] ); ?>">
							<?php esc_html_e( 'Delete', 'third-audience' ); ?>
						</button>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php else : ?>
	<div class="ta-card ta-empty-state">
		<p><?php esc_html_e( 'No competitors added yet. Add your first competitor to start tracking.', 'third-audience' ); ?></p>
		<p>
			<button type="button" class="button button-primary button-hero" onclick="document.getElementById('ta-add-competitor-btn').click();">
				<?php esc_html_e( 'Add Competitor', 'third-audience' ); ?>
			</button>
		</p>
	</div>
	<?php endif; ?>
</div>
