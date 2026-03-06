<?php
/**
 * Citation Alerts Page - Alert history and management.
 *
 * @package ThirdAudience
 * @since   2.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap ta-admin-wrap">
	<h1><?php esc_html_e( 'Citation Alerts', 'third-audience' ); ?></h1>

	<!-- Statistics Cards -->
	<div class="ta-stats-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
		<div class="ta-stat-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
			<h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;"><?php esc_html_e( 'Total Alerts (30d)', 'third-audience' ); ?></h3>
			<p style="margin: 0; font-size: 32px; font-weight: 600; color: #0073aa;">
				<?php echo esc_html( $statistics['total_alerts'] ?? 0 ); ?>
			</p>
		</div>

		<div class="ta-stat-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
			<h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;"><?php esc_html_e( 'Active Alerts', 'third-audience' ); ?></h3>
			<p style="margin: 0; font-size: 32px; font-weight: 600; color: #46b450;">
				<?php echo esc_html( $statistics['active_alerts'] ?? 0 ); ?>
			</p>
		</div>

		<div class="ta-stat-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
			<h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;"><?php esc_html_e( 'Warning Alerts', 'third-audience' ); ?></h3>
			<p style="margin: 0; font-size: 32px; font-weight: 600; color: #dc3232;">
				<?php echo esc_html( $statistics['warning_alerts'] ?? 0 ); ?>
			</p>
		</div>

		<div class="ta-stat-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
			<h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;"><?php esc_html_e( 'Success Alerts', 'third-audience' ); ?></h3>
			<p style="margin: 0; font-size: 32px; font-weight: 600; color: #46b450;">
				<?php echo esc_html( $statistics['success_alerts'] ?? 0 ); ?>
			</p>
		</div>
	</div>

	<!-- Filters -->
	<div class="ta-filters" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 20px 0;">
		<form method="get" action="">
			<input type="hidden" name="page" value="third-audience-citation-alerts" />

			<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
				<div>
					<label for="alert_type" style="display: block; margin-bottom: 5px; font-weight: 600;">
						<?php esc_html_e( 'Alert Type', 'third-audience' ); ?>
					</label>
					<select name="alert_type" id="alert_type" style="width: 100%;">
						<option value=""><?php esc_html_e( 'All Types', 'third-audience' ); ?></option>
						<option value="first_citation" <?php selected( $filters['alert_type'], 'first_citation' ); ?>>
							<?php esc_html_e( 'First Citation', 'third-audience' ); ?>
						</option>
						<option value="new_platform" <?php selected( $filters['alert_type'], 'new_platform' ); ?>>
							<?php esc_html_e( 'New Platform', 'third-audience' ); ?>
						</option>
						<option value="citation_spike" <?php selected( $filters['alert_type'], 'citation_spike' ); ?>>
							<?php esc_html_e( 'Citation Spike', 'third-audience' ); ?>
						</option>
						<option value="citation_drop" <?php selected( $filters['alert_type'], 'citation_drop' ); ?>>
							<?php esc_html_e( 'Citation Drop', 'third-audience' ); ?>
						</option>
						<option value="high_performance" <?php selected( $filters['alert_type'], 'high_performance' ); ?>>
							<?php esc_html_e( 'High Performance', 'third-audience' ); ?>
						</option>
						<option value="verification_failure" <?php selected( $filters['alert_type'], 'verification_failure' ); ?>>
							<?php esc_html_e( 'Verification Failure', 'third-audience' ); ?>
						</option>
					</select>
				</div>

				<div>
					<label for="severity" style="display: block; margin-bottom: 5px; font-weight: 600;">
						<?php esc_html_e( 'Severity', 'third-audience' ); ?>
					</label>
					<select name="severity" id="severity" style="width: 100%;">
						<option value=""><?php esc_html_e( 'All Severities', 'third-audience' ); ?></option>
						<option value="info" <?php selected( $filters['severity'], 'info' ); ?>>
							<?php esc_html_e( 'Info', 'third-audience' ); ?>
						</option>
						<option value="warning" <?php selected( $filters['severity'], 'warning' ); ?>>
							<?php esc_html_e( 'Warning', 'third-audience' ); ?>
						</option>
						<option value="success" <?php selected( $filters['severity'], 'success' ); ?>>
							<?php esc_html_e( 'Success', 'third-audience' ); ?>
						</option>
					</select>
				</div>

				<div>
					<label for="dismissed" style="display: block; margin-bottom: 5px; font-weight: 600;">
						<?php esc_html_e( 'Status', 'third-audience' ); ?>
					</label>
					<select name="dismissed" id="dismissed" style="width: 100%;">
						<option value=""><?php esc_html_e( 'All Alerts', 'third-audience' ); ?></option>
						<option value="0" <?php selected( $filters['dismissed'], 0 ); ?>>
							<?php esc_html_e( 'Active', 'third-audience' ); ?>
						</option>
						<option value="1" <?php selected( $filters['dismissed'], 1 ); ?>>
							<?php esc_html_e( 'Dismissed', 'third-audience' ); ?>
						</option>
					</select>
				</div>

				<div style="display: flex; align-items: flex-end;">
					<button type="submit" class="button button-primary" style="margin-right: 10px;">
						<?php esc_html_e( 'Apply Filters', 'third-audience' ); ?>
					</button>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-citation-alerts' ) ); ?>" class="button">
						<?php esc_html_e( 'Clear Filters', 'third-audience' ); ?>
					</a>
				</div>
			</div>
		</form>
	</div>

	<!-- Alerts Table -->
	<div class="ta-alerts-table" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
		<?php if ( empty( $alert_history ) ) : ?>
			<p style="text-align: center; color: #666; padding: 40px 0;">
				<?php esc_html_e( 'No alerts found.', 'third-audience' ); ?>
			</p>
		<?php else : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width: 40px;"><?php esc_html_e( 'ID', 'third-audience' ); ?></th>
						<th style="width: 120px;"><?php esc_html_e( 'Type', 'third-audience' ); ?></th>
						<th style="width: 80px;"><?php esc_html_e( 'Severity', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Title', 'third-audience' ); ?></th>
						<th><?php esc_html_e( 'Message', 'third-audience' ); ?></th>
						<th style="width: 150px;"><?php esc_html_e( 'Created', 'third-audience' ); ?></th>
						<th style="width: 80px;"><?php esc_html_e( 'Status', 'third-audience' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $alert_history as $alert ) : ?>
						<tr>
							<td><?php echo esc_html( $alert['id'] ); ?></td>
							<td>
								<code style="background: #f0f0f0; padding: 3px 6px; border-radius: 3px;">
									<?php echo esc_html( $alert['alert_type'] ); ?>
								</code>
							</td>
							<td>
								<?php
								$severity_color = '#0073aa';
								if ( 'warning' === $alert['severity'] ) {
									$severity_color = '#dc3232';
								} elseif ( 'success' === $alert['severity'] ) {
									$severity_color = '#46b450';
								}
								?>
								<span style="display: inline-block; padding: 3px 8px; background: <?php echo esc_attr( $severity_color ); ?>; color: white; border-radius: 3px; font-size: 12px;">
									<?php echo esc_html( ucfirst( $alert['severity'] ) ); ?>
								</span>
							</td>
							<td><strong><?php echo esc_html( $alert['title'] ); ?></strong></td>
							<td><?php echo esc_html( $alert['message'] ); ?></td>
							<td>
								<?php
								$created_timestamp = strtotime( $alert['created_at'] );
								$time_ago = human_time_diff( $created_timestamp, current_time( 'timestamp' ) );
								echo esc_html( sprintf(
									/* translators: %s: time ago */
									__( '%s ago', 'third-audience' ),
									$time_ago
								) );
								?>
							</td>
							<td>
								<?php if ( 1 === intval( $alert['dismissed'] ) ) : ?>
									<span style="color: #999;"><?php esc_html_e( 'Dismissed', 'third-audience' ); ?></span>
								<?php else : ?>
									<span style="color: #46b450; font-weight: 600;"><?php esc_html_e( 'Active', 'third-audience' ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<!-- Pagination -->
			<?php
			$total_alerts = count( $alert_history );
			if ( $total_alerts >= $per_page ) :
				$total_pages = ceil( $total_alerts / $per_page );
				?>
				<div class="tablenav" style="margin-top: 20px;">
					<div class="tablenav-pages">
						<?php
						echo paginate_links( array(
							'base'      => add_query_arg( 'paged', '%#%' ),
							'format'    => '',
							'prev_text' => __( '&laquo;', 'third-audience' ),
							'next_text' => __( '&raquo;', 'third-audience' ),
							'total'     => $total_pages,
							'current'   => $current_page,
						) );
						?>
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>

	<!-- Back Link -->
	<p style="margin-top: 20px;">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-bot-analytics' ) ); ?>" class="button">
			<?php esc_html_e( '← Back to Bot Analytics', 'third-audience' ); ?>
		</a>
	</p>
</div>
