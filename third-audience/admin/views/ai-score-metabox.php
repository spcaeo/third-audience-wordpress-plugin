<?php
/**
 * AI-Friendliness Score Meta Box View
 *
 * @package ThirdAudience
 * @since   2.8.0
 *
 * @var int   $post_id         Post ID.
 * @var int   $score           Overall AI-Friendliness score (0-100).
 * @var array $score_details   Detailed score breakdown.
 * @var array $recommendations Content recommendations.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Determine grade and color.
$grade = isset( $score_details['grade'] ) ? $score_details['grade'] : 'N/A';
$color = '#999';
if ( $score >= 80 ) {
	$color = '#34c759'; // Green.
	$grade_text = __( 'Excellent', 'third-audience' );
} elseif ( $score >= 70 ) {
	$color = '#5ac8fa'; // Blue.
	$grade_text = __( 'Good', 'third-audience' );
} elseif ( $score >= 60 ) {
	$color = '#ff9500'; // Orange.
	$grade_text = __( 'Fair', 'third-audience' );
} else {
	$color = '#ff3b30'; // Red.
	$grade_text = __( 'Needs Work', 'third-audience' );
}
?>

<div class="ta-ai-score-metabox">
	<?php if ( null === $score ) : ?>
		<!-- No score calculated yet -->
		<div class="ta-score-placeholder">
			<p><?php esc_html_e( 'AI-Friendliness score will be calculated after you save this post.', 'third-audience' ); ?></p>
			<p><em><?php esc_html_e( 'This score helps you optimize content for AI search engines like ChatGPT, Perplexity, and Claude.', 'third-audience' ); ?></em></p>
		</div>
	<?php else : ?>
		<!-- Score display -->
		<div class="ta-score-display">
			<div class="ta-score-circle" style="border-color: <?php echo esc_attr( $color ); ?>;">
				<span class="ta-score-value"><?php echo absint( $score ); ?></span>
				<span class="ta-score-label">/100</span>
			</div>
			<div class="ta-score-text">
				<strong style="color: <?php echo esc_attr( $color ); ?>;"><?php echo esc_html( $grade_text ); ?></strong>
				<p><?php esc_html_e( 'AI-Friendliness Score', 'third-audience' ); ?></p>
			</div>
		</div>

		<!-- Score breakdown -->
		<?php if ( isset( $score_details['structure'] ) ) : ?>
			<div class="ta-score-breakdown">
				<h4><?php esc_html_e( 'Score Breakdown', 'third-audience' ); ?></h4>
				<div class="ta-score-item">
					<span class="ta-score-label-text"><?php esc_html_e( 'Structure', 'third-audience' ); ?></span>
					<div class="ta-score-bar">
						<div class="ta-score-fill" style="width: <?php echo esc_attr( ( $score_details['structure']['total'] / $score_details['structure']['max'] ) * 100 ); ?>%;"></div>
					</div>
					<span class="ta-score-number"><?php echo absint( $score_details['structure']['total'] ); ?>/<?php echo absint( $score_details['structure']['max'] ); ?></span>
				</div>

				<div class="ta-score-item">
					<span class="ta-score-label-text"><?php esc_html_e( 'Metadata', 'third-audience' ); ?></span>
					<div class="ta-score-bar">
						<div class="ta-score-fill" style="width: <?php echo esc_attr( ( $score_details['metadata']['total'] / $score_details['metadata']['max'] ) * 100 ); ?>%;"></div>
					</div>
					<span class="ta-score-number"><?php echo absint( $score_details['metadata']['total'] ); ?>/<?php echo absint( $score_details['metadata']['max'] ); ?></span>
				</div>

				<div class="ta-score-item">
					<span class="ta-score-label-text"><?php esc_html_e( 'Readability', 'third-audience' ); ?></span>
					<div class="ta-score-bar">
						<div class="ta-score-fill" style="width: <?php echo esc_attr( ( $score_details['readability']['total'] / $score_details['readability']['max'] ) * 100 ); ?>%;"></div>
					</div>
					<span class="ta-score-number"><?php echo absint( $score_details['readability']['total'] ); ?>/<?php echo absint( $score_details['readability']['max'] ); ?></span>
				</div>

				<div class="ta-score-item">
					<span class="ta-score-label-text"><?php esc_html_e( 'Schema/Markup', 'third-audience' ); ?></span>
					<div class="ta-score-bar">
						<div class="ta-score-fill" style="width: <?php echo esc_attr( ( $score_details['schema']['total'] / $score_details['schema']['max'] ) * 100 ); ?>%;"></div>
					</div>
					<span class="ta-score-number"><?php echo absint( $score_details['schema']['total'] ); ?>/<?php echo absint( $score_details['schema']['max'] ); ?></span>
				</div>
			</div>
		<?php endif; ?>

		<!-- Recommendations -->
		<?php if ( ! empty( $recommendations ) ) : ?>
			<div class="ta-recommendations">
				<h4><?php esc_html_e( 'Recommendations', 'third-audience' ); ?></h4>
				<ul>
					<?php foreach ( $recommendations as $rec ) : ?>
						<li class="ta-rec-<?php echo esc_attr( $rec['severity'] ); ?>">
							<span class="dashicons dashicons-<?php echo 'high' === $rec['severity'] ? 'warning' : 'info'; ?>"></span>
							<div class="ta-rec-content">
								<strong><?php echo esc_html( $rec['message'] ); ?></strong>
								<p><?php echo esc_html( $rec['action'] ); ?></p>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php else : ?>
			<div class="ta-recommendations-empty">
				<p style="color: #34c759;">
					<span class="dashicons dashicons-yes-alt"></span>
					<?php esc_html_e( 'Great! No major issues found. Your content is well-optimized for AI search engines.', 'third-audience' ); ?>
				</p>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<!-- Actions -->
	<div class="ta-score-actions">
		<button type="button" class="button ta-recalculate-score" data-post-id="<?php echo absint( $post_id ); ?>">
			<span class="dashicons dashicons-update"></span>
			<?php esc_html_e( 'Recalculate Score', 'third-audience' ); ?>
		</button>
		<span class="ta-score-loading" style="display: none;">
			<span class="spinner is-active"></span>
			<?php esc_html_e( 'Calculating...', 'third-audience' ); ?>
		</span>
	</div>

	<?php if ( isset( $score_details['calculated_at'] ) ) : ?>
		<p class="ta-score-timestamp">
			<?php
			printf(
				/* translators: %s: last calculation time */
				esc_html__( 'Last calculated: %s', 'third-audience' ),
				esc_html( human_time_diff( strtotime( $score_details['calculated_at'] ) ) . ' ago' )
			);
			?>
		</p>
	<?php endif; ?>
</div>
