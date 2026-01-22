<?php
/**
 * Competitor Benchmarking - Results Tab
 *
 * @package ThirdAudience
 * @since   3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="ta-results-section">
	<!-- Filters -->
	<div class="ta-card">
		<h3><?php esc_html_e( 'Filter Results', 'third-audience' ); ?></h3>
		<form method="get" action="">
			<input type="hidden" name="page" value="third-audience-competitor-benchmarking">
			<input type="hidden" name="tab" value="results">

			<div class="ta-filters-grid">
				<div class="ta-filter">
					<label for="filter_competitor"><?php esc_html_e( 'Competitor', 'third-audience' ); ?></label>
					<select id="filter_competitor" name="competitor_url">
						<option value=""><?php esc_html_e( 'All Competitors', 'third-audience' ); ?></option>
						<?php foreach ( $competitors as $comp ) : ?>
						<option value="<?php echo esc_attr( $comp['url'] ); ?>"
						        <?php selected( $filters['competitor_url'] ?? '', $comp['url'] ); ?>>
							<?php echo esc_html( $comp['name'] ); ?>
						</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="ta-filter">
					<label for="filter_platform"><?php esc_html_e( 'AI Platform', 'third-audience' ); ?></label>
					<select id="filter_platform" name="ai_platform">
						<option value=""><?php esc_html_e( 'All Platforms', 'third-audience' ); ?></option>
						<?php foreach ( TA_Competitor_Benchmarking::get_ai_platforms() as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>"
						        <?php selected( $filters['ai_platform'] ?? '', $key ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="ta-filter">
					<label for="filter_date_from"><?php esc_html_e( 'Date From', 'third-audience' ); ?></label>
					<input type="date" id="filter_date_from" name="date_from"
					       value="<?php echo esc_attr( $filters['date_from'] ?? '' ); ?>">
				</div>

				<div class="ta-filter">
					<label for="filter_date_to"><?php esc_html_e( 'Date To', 'third-audience' ); ?></label>
					<input type="date" id="filter_date_to" name="date_to"
					       value="<?php echo esc_attr( $filters['date_to'] ?? '' ); ?>">
				</div>
			</div>

			<p class="submit">
				<button type="submit" class="button"><?php esc_html_e( 'Apply Filters', 'third-audience' ); ?></button>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-competitor-benchmarking&tab=results' ) ); ?>"
				   class="button"><?php esc_html_e( 'Clear Filters', 'third-audience' ); ?></a>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=third-audience-competitor-benchmarking&action=export&' . http_build_query( $filters ) ), 'ta_export_benchmarks' ) ); ?>"
				   class="button"><?php esc_html_e( 'Export CSV', 'third-audience' ); ?></a>
			</p>
		</form>
	</div>

	<!-- Results Table -->
	<?php if ( ! empty( $results ) ) : ?>
	<div class="ta-card">
		<h3>
			<?php
			/* translators: %d: number of results */
			printf( esc_html__( 'Test Results (%d)', 'third-audience' ), absint( $total_results ) );
			?>
		</h3>

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Date', 'third-audience' ); ?></th>
					<th><?php esc_html_e( 'Competitor', 'third-audience' ); ?></th>
					<th><?php esc_html_e( 'Prompt', 'third-audience' ); ?></th>
					<th><?php esc_html_e( 'Platform', 'third-audience' ); ?></th>
					<th><?php esc_html_e( 'Citation', 'third-audience' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'third-audience' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $results as $result ) : ?>
				<tr>
					<td>
						<?php echo esc_html( gmdate( 'Y-m-d H:i', strtotime( $result['test_date'] ) ) ); ?>
					</td>
					<td>
						<strong><?php echo esc_html( $result['competitor_name'] ); ?></strong><br>
						<small class="description"><?php echo esc_html( $result['competitor_url'] ); ?></small>
					</td>
					<td>
						<div class="ta-prompt-preview">
							<?php echo esc_html( wp_trim_words( $result['test_prompt'], 15 ) ); ?>
							<?php if ( str_word_count( $result['test_prompt'] ) > 15 ) : ?>
								<button type="button" class="button-link ta-view-full-prompt" data-prompt="<?php echo esc_attr( $result['test_prompt'] ); ?>">
									<?php esc_html_e( 'View Full', 'third-audience' ); ?>
								</button>
							<?php endif; ?>
						</div>
						<?php if ( ! empty( $result['test_notes'] ) ) : ?>
						<small class="description"><?php echo esc_html( $result['test_notes'] ); ?></small>
						<?php endif; ?>
					</td>
					<td>
						<span class="ta-platform-badge ta-platform-<?php echo esc_attr( $result['ai_platform'] ); ?>">
							<?php echo esc_html( ucfirst( $result['ai_platform'] ) ); ?>
						</span>
					</td>
					<td>
						<?php if ( $result['cited_rank'] ) : ?>
							<span class="ta-badge ta-badge-success">
								<?php
								/* translators: %d: rank position */
								printf( esc_html__( 'Rank %d', 'third-audience' ), absint( $result['cited_rank'] ) );
								?>
							</span>
						<?php else : ?>
							<span class="ta-badge ta-badge-neutral"><?php esc_html_e( 'Not Cited', 'third-audience' ); ?></span>
						<?php endif; ?>
					</td>
					<td>
						<button type="button" class="button button-small ta-delete-result"
						        data-id="<?php echo esc_attr( $result['id'] ); ?>">
							<?php esc_html_e( 'Delete', 'third-audience' ); ?>
						</button>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<!-- Pagination -->
		<?php if ( $total_pages > 1 ) : ?>
		<div class="tablenav bottom">
			<div class="tablenav-pages">
				<?php
				$page_links = paginate_links( array(
					'base'      => add_query_arg( 'paged', '%#%' ),
					'format'    => '',
					'prev_text' => '&laquo;',
					'next_text' => '&raquo;',
					'total'     => $total_pages,
					'current'   => $current_page,
				) );

				if ( $page_links ) {
					echo '<span class="pagination-links">' . wp_kses_post( $page_links ) . '</span>';
				}
				?>
			</div>
		</div>
		<?php endif; ?>
	</div>
	<?php else : ?>
	<div class="ta-card ta-empty-state">
		<p><?php esc_html_e( 'No test results found. Run some tests to see data here.', 'third-audience' ); ?></p>
		<p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-competitor-benchmarking&tab=test' ) ); ?>"
			   class="button button-primary">
				<?php esc_html_e( 'Run Your First Test', 'third-audience' ); ?>
			</a>
		</p>
	</div>
	<?php endif; ?>
</div>

<!-- View Full Prompt Modal -->
<div id="ta-prompt-modal" class="ta-modal" style="display: none;">
	<div class="ta-modal-content">
		<span class="ta-modal-close">&times;</span>
		<h3><?php esc_html_e( 'Full Prompt', 'third-audience' ); ?></h3>
		<div id="ta-prompt-modal-text"></div>
	</div>
</div>
