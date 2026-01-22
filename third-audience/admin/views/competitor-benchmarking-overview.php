<?php
/**
 * Competitor Benchmarking - Overview Tab
 *
 * @package ThirdAudience
 * @since   3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="ta-overview-section">
	<!-- Quick Stats -->
	<div class="ta-stats-grid">
		<div class="ta-stat-card">
			<div class="ta-stat-icon">📊</div>
			<div class="ta-stat-content">
				<h3><?php echo esc_html( count( $competitors ) ); ?></h3>
				<p><?php esc_html_e( 'Competitors Tracked', 'third-audience' ); ?></p>
			</div>
		</div>

		<div class="ta-stat-card">
			<div class="ta-stat-icon">🧪</div>
			<div class="ta-stat-content">
				<h3><?php echo esc_html( $total_results ); ?></h3>
				<p><?php esc_html_e( 'Total Tests Run', 'third-audience' ); ?></p>
			</div>
		</div>

		<div class="ta-stat-card">
			<div class="ta-stat-icon">✅</div>
			<div class="ta-stat-content">
				<?php
				$total_citations = 0;
				foreach ( $insights['citation_rates'] as $rate ) {
					$total_citations += intval( $rate['citations'] );
				}
				?>
				<h3><?php echo esc_html( $total_citations ); ?></h3>
				<p><?php esc_html_e( 'Total Citations', 'third-audience' ); ?></p>
			</div>
		</div>

		<div class="ta-stat-card">
			<div class="ta-stat-icon">🎯</div>
			<div class="ta-stat-content">
				<?php
				$avg_citation_rate = 0;
				if ( count( $insights['citation_rates'] ) > 0 ) {
					$sum = array_sum( array_column( $insights['citation_rates'], 'citation_rate' ) );
					$avg_citation_rate = round( $sum / count( $insights['citation_rates'] ), 1 );
				}
				?>
				<h3><?php echo esc_html( $avg_citation_rate ); ?>%</h3>
				<p><?php esc_html_e( 'Avg Citation Rate', 'third-audience' ); ?></p>
			</div>
		</div>
	</div>

	<!-- Competitor Performance Comparison -->
	<?php if ( ! empty( $insights['citation_rates'] ) ) : ?>
	<div class="ta-card">
		<h2><?php esc_html_e( 'Competitor Performance Comparison', 'third-audience' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Compare citation rates across tracked competitors.', 'third-audience' ); ?></p>

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Competitor', 'third-audience' ); ?></th>
					<th><?php esc_html_e( 'Total Tests', 'third-audience' ); ?></th>
					<th><?php esc_html_e( 'Citations', 'third-audience' ); ?></th>
					<th><?php esc_html_e( 'Citation Rate', 'third-audience' ); ?></th>
					<th><?php esc_html_e( 'Avg Rank', 'third-audience' ); ?></th>
					<th><?php esc_html_e( 'Status', 'third-audience' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $insights['citation_rates'] as $rate ) : ?>
				<tr>
					<td>
						<strong><?php echo esc_html( $rate['competitor_name'] ); ?></strong><br>
						<small class="description"><?php echo esc_html( $rate['competitor_url'] ); ?></small>
					</td>
					<td><?php echo esc_html( $rate['total_tests'] ); ?></td>
					<td><?php echo esc_html( $rate['citations'] ); ?></td>
					<td>
						<div class="ta-progress-bar">
							<div class="ta-progress-fill" style="width: <?php echo esc_attr( $rate['citation_rate'] ); ?>%;"></div>
							<span class="ta-progress-label"><?php echo esc_html( $rate['citation_rate'] ); ?>%</span>
						</div>
					</td>
					<td>
						<?php
						if ( $rate['avg_rank'] ) {
							echo esc_html( number_format( $rate['avg_rank'], 1 ) );
						} else {
							echo '—';
						}
						?>
					</td>
					<td>
						<?php
						if ( $rate['citation_rate'] >= 50 ) {
							echo '<span class="ta-badge ta-badge-success">' . esc_html__( 'Strong', 'third-audience' ) . '</span>';
						} elseif ( $rate['citation_rate'] >= 25 ) {
							echo '<span class="ta-badge ta-badge-warning">' . esc_html__( 'Moderate', 'third-audience' ) . '</span>';
						} else {
							echo '<span class="ta-badge ta-badge-error">' . esc_html__( 'Weak', 'third-audience' ) . '</span>';
						}
						?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>

	<!-- Platform Performance -->
	<?php if ( ! empty( $insights['platform_performance'] ) ) : ?>
	<div class="ta-card">
		<h2><?php esc_html_e( 'AI Platform Performance', 'third-audience' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Which AI platforms are citing competitors most frequently?', 'third-audience' ); ?></p>

		<div class="ta-platforms-grid">
			<?php foreach ( $insights['platform_performance'] as $platform ) : ?>
			<div class="ta-platform-card">
				<h3><?php echo esc_html( ucfirst( $platform['ai_platform'] ) ); ?></h3>
				<div class="ta-platform-stats">
					<div class="ta-platform-stat">
						<span class="label"><?php esc_html_e( 'Tests:', 'third-audience' ); ?></span>
						<span class="value"><?php echo esc_html( $platform['total_tests'] ); ?></span>
					</div>
					<div class="ta-platform-stat">
						<span class="label"><?php esc_html_e( 'Citations:', 'third-audience' ); ?></span>
						<span class="value"><?php echo esc_html( $platform['citations'] ); ?></span>
					</div>
					<div class="ta-platform-stat">
						<span class="label"><?php esc_html_e( 'Rate:', 'third-audience' ); ?></span>
						<span class="value"><?php echo esc_html( $platform['citation_rate'] ); ?>%</span>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

	<!-- Top Performing Prompts -->
	<?php if ( ! empty( $insights['top_prompts'] ) ) : ?>
	<div class="ta-card">
		<h2><?php esc_html_e( 'Top Performing Prompts', 'third-audience' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Prompts that generate the highest citation rates.', 'third-audience' ); ?></p>

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Prompt', 'third-audience' ); ?></th>
					<th><?php esc_html_e( 'Tests', 'third-audience' ); ?></th>
					<th><?php esc_html_e( 'Citations', 'third-audience' ); ?></th>
					<th><?php esc_html_e( 'Citation Rate', 'third-audience' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $insights['top_prompts'] as $prompt ) : ?>
				<tr>
					<td><?php echo esc_html( $prompt['test_prompt'] ); ?></td>
					<td><?php echo esc_html( $prompt['test_count'] ); ?></td>
					<td><?php echo esc_html( $prompt['citation_count'] ); ?></td>
					<td>
						<strong><?php echo esc_html( $prompt['citation_rate'] ); ?>%</strong>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>

	<!-- Getting Started -->
	<?php if ( empty( $competitors ) ) : ?>
	<div class="ta-card ta-getting-started">
		<h2><?php esc_html_e( 'Getting Started with Competitor Benchmarking', 'third-audience' ); ?></h2>
		<p><?php esc_html_e( 'Track how your competitors appear in AI platform responses and identify opportunities to improve your AI visibility.', 'third-audience' ); ?></p>

		<ol class="ta-steps">
			<li>
				<strong><?php esc_html_e( 'Add Competitors', 'third-audience' ); ?></strong><br>
				<?php esc_html_e( 'Go to the Competitors tab and add URLs of companies you want to track.', 'third-audience' ); ?>
			</li>
			<li>
				<strong><?php esc_html_e( 'Generate Test Prompts', 'third-audience' ); ?></strong><br>
				<?php esc_html_e( 'Use our prompt templates or create custom queries relevant to your industry.', 'third-audience' ); ?>
			</li>
			<li>
				<strong><?php esc_html_e( 'Test in AI Platforms', 'third-audience' ); ?></strong><br>
				<?php esc_html_e( 'Manually test prompts in ChatGPT, Perplexity, Claude, and other AI platforms.', 'third-audience' ); ?>
			</li>
			<li>
				<strong><?php esc_html_e( 'Record Results', 'third-audience' ); ?></strong><br>
				<?php esc_html_e( 'Track which competitors get cited and at what rank position.', 'third-audience' ); ?>
			</li>
			<li>
				<strong><?php esc_html_e( 'Analyze Trends', 'third-audience' ); ?></strong><br>
				<?php esc_html_e( 'Identify patterns, win/loss trends, and content opportunities.', 'third-audience' ); ?>
			</li>
		</ol>

		<p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-competitor-benchmarking&tab=competitors' ) ); ?>" class="button button-primary button-hero">
				<?php esc_html_e( 'Add Your First Competitor', 'third-audience' ); ?>
			</a>
		</p>
	</div>
	<?php endif; ?>
</div>
