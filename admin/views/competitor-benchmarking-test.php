<?php
/**
 * Competitor Benchmarking - Run Test Tab
 *
 * @package ThirdAudience
 * @since   3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$selected_competitor_url = isset( $_GET['competitor_url'] ) ? sanitize_text_field( wp_unslash( $_GET['competitor_url'] ) ) : '';
$selected_competitor = null;

if ( $selected_competitor_url ) {
	foreach ( $competitors as $comp ) {
		if ( $comp['url'] === $selected_competitor_url ) {
			$selected_competitor = $comp;
			break;
		}
	}
}

?>

<div class="ta-test-section">
	<div class="ta-card">
		<h2><?php esc_html_e( 'Run Benchmark Test', 'third-audience' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'Test your prompts in AI platforms and record which competitors get cited.', 'third-audience' ); ?>
		</p>

		<?php if ( empty( $competitors ) ) : ?>
			<div class="notice notice-warning inline">
				<p>
					<?php esc_html_e( 'You need to add competitors first before running tests.', 'third-audience' ); ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-competitor-benchmarking&tab=competitors' ) ); ?>">
						<?php esc_html_e( 'Add Competitors', 'third-audience' ); ?>
					</a>
				</p>
			</div>
		<?php else : ?>
		<form id="ta-test-form" method="post">
			<?php wp_nonce_field( 'ta_record_test', 'ta_test_nonce' ); ?>

			<table class="form-table">
				<tr>
					<th><label for="competitor_url"><?php esc_html_e( 'Competitor', 'third-audience' ); ?></label></th>
					<td>
						<select id="competitor_url" name="competitor_url" class="regular-text" required>
							<option value=""><?php esc_html_e( '-- Select Competitor --', 'third-audience' ); ?></option>
							<?php foreach ( $competitors as $comp ) : ?>
							<option value="<?php echo esc_attr( $comp['url'] ); ?>"
							        data-name="<?php echo esc_attr( $comp['name'] ); ?>"
							        <?php selected( $selected_competitor_url, $comp['url'] ); ?>>
								<?php echo esc_html( $comp['name'] ); ?> - <?php echo esc_html( $comp['url'] ); ?>
							</option>
							<?php endforeach; ?>
						</select>
						<button type="button" class="button" id="ta-generate-prompts-btn">
							<?php esc_html_e( 'Generate Prompts for This Competitor', 'third-audience' ); ?>
						</button>
					</td>
				</tr>

				<tr>
					<th><label for="test_prompt"><?php esc_html_e( 'Test Prompt', 'third-audience' ); ?></label></th>
					<td>
						<textarea id="test_prompt" name="test_prompt" rows="4" class="large-text" required
						          placeholder="<?php esc_attr_e( 'Enter your test prompt here...', 'third-audience' ); ?>"></textarea>
						<p class="description">
							<?php esc_html_e( 'The prompt you will test in AI platforms. Use the Prompt Templates tab for ideas.', 'third-audience' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th><label for="ai_platform"><?php esc_html_e( 'AI Platform', 'third-audience' ); ?></label></th>
					<td>
						<select id="ai_platform" name="ai_platform" class="regular-text" required>
							<option value=""><?php esc_html_e( '-- Select Platform --', 'third-audience' ); ?></option>
							<?php foreach ( TA_Competitor_Benchmarking::get_ai_platforms() as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description">
							<?php esc_html_e( 'Which AI platform are you testing with?', 'third-audience' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th><label for="cited_rank"><?php esc_html_e( 'Citation Rank', 'third-audience' ); ?></label></th>
					<td>
						<select id="cited_rank" name="cited_rank" class="regular-text">
							<option value=""><?php esc_html_e( 'Not Cited', 'third-audience' ); ?></option>
							<option value="1"><?php esc_html_e( 'Rank 1 (First)', 'third-audience' ); ?></option>
							<option value="2"><?php esc_html_e( 'Rank 2', 'third-audience' ); ?></option>
							<option value="3"><?php esc_html_e( 'Rank 3', 'third-audience' ); ?></option>
							<option value="4"><?php esc_html_e( 'Rank 4', 'third-audience' ); ?></option>
							<option value="5"><?php esc_html_e( 'Rank 5+', 'third-audience' ); ?></option>
						</select>
						<p class="description">
							<?php esc_html_e( 'At what position was the competitor cited? Leave as "Not Cited" if they were not mentioned.', 'third-audience' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th><label for="test_notes"><?php esc_html_e( 'Notes', 'third-audience' ); ?></label></th>
					<td>
						<textarea id="test_notes" name="test_notes" rows="3" class="large-text"
						          placeholder="<?php esc_attr_e( 'Optional notes about this test...', 'third-audience' ); ?>"></textarea>
					</td>
				</tr>

				<tr>
					<th><label for="test_date"><?php esc_html_e( 'Test Date', 'third-audience' ); ?></label></th>
					<td>
						<input type="datetime-local" id="test_date" name="test_date" class="regular-text"
						       value="<?php echo esc_attr( gmdate( 'Y-m-d\TH:i', current_time( 'timestamp' ) ) ); ?>">
						<p class="description">
							<?php esc_html_e( 'When was this test performed?', 'third-audience' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<p class="submit">
				<button type="submit" class="button button-primary button-hero">
					<?php esc_html_e( 'Record Test Result', 'third-audience' ); ?>
				</button>
			</p>
		</form>
		<?php endif; ?>
	</div>

	<!-- Generated Prompts (hidden by default) -->
	<div class="ta-card ta-generated-prompts" style="display: none;">
		<h3><?php esc_html_e( 'Generated Prompts', 'third-audience' ); ?></h3>
		<p class="description">
			<?php esc_html_e( 'Click a prompt to use it in your test.', 'third-audience' ); ?>
		</p>
		<div id="ta-prompts-list"></div>
	</div>

	<!-- Testing Instructions -->
	<div class="ta-card">
		<h3><?php esc_html_e( 'How to Run a Test', 'third-audience' ); ?></h3>
		<ol class="ta-instructions">
			<li>
				<strong><?php esc_html_e( 'Select a competitor', 'third-audience' ); ?></strong><br>
				<?php esc_html_e( 'Choose which competitor you want to test from the dropdown.', 'third-audience' ); ?>
			</li>
			<li>
				<strong><?php esc_html_e( 'Enter or generate a prompt', 'third-audience' ); ?></strong><br>
				<?php esc_html_e( 'Write a test prompt or click "Generate Prompts" to use templates.', 'third-audience' ); ?>
			</li>
			<li>
				<strong><?php esc_html_e( 'Test in AI platform', 'third-audience' ); ?></strong><br>
				<?php esc_html_e( 'Copy your prompt and test it in ChatGPT, Perplexity, Claude, or other AI platforms.', 'third-audience' ); ?>
			</li>
			<li>
				<strong><?php esc_html_e( 'Record the results', 'third-audience' ); ?></strong><br>
				<?php esc_html_e( 'Note which platform you used, if the competitor was cited, and at what rank position.', 'third-audience' ); ?>
			</li>
			<li>
				<strong><?php esc_html_e( 'Save the test', 'third-audience' ); ?></strong><br>
				<?php esc_html_e( 'Click "Record Test Result" to save your findings to the database.', 'third-audience' ); ?>
			</li>
		</ol>
	</div>
</div>
