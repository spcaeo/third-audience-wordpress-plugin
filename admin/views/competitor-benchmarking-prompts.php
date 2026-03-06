<?php
/**
 * Competitor Benchmarking - Prompt Templates Tab
 *
 * @package ThirdAudience
 * @since   3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$templates = $benchmarking->get_prompt_templates();

?>

<div class="ta-prompts-section">
	<div class="ta-card">
		<h2><?php esc_html_e( 'Prompt Templates', 'third-audience' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'Use these templates to generate effective test prompts for your competitors. Click any template to copy it, then customize the variables for your specific use case.', 'third-audience' ); ?>
		</p>
	</div>

	<!-- Template Categories -->
	<?php foreach ( $templates as $category => $data ) : ?>
	<div class="ta-card ta-prompt-category">
		<h3><?php echo esc_html( $data['name'] ); ?></h3>

		<div class="ta-prompts-list">
			<?php foreach ( $data['templates'] as $index => $template ) : ?>
			<div class="ta-prompt-template">
				<div class="ta-prompt-text">
					<code><?php echo esc_html( $template ); ?></code>
				</div>
				<div class="ta-prompt-actions">
					<button type="button" class="button button-small ta-copy-prompt"
					        data-prompt="<?php echo esc_attr( $template ); ?>">
						<?php esc_html_e( 'Copy', 'third-audience' ); ?>
					</button>
					<button type="button" class="button button-small button-primary ta-use-prompt"
					        data-prompt="<?php echo esc_attr( $template ); ?>">
						<?php esc_html_e( 'Use in Test', 'third-audience' ); ?>
					</button>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endforeach; ?>

	<!-- Template Variables Guide -->
	<div class="ta-card">
		<h3><?php esc_html_e( 'Template Variables', 'third-audience' ); ?></h3>
		<p class="description">
			<?php esc_html_e( 'Replace these placeholder variables with specific values relevant to your industry and competitors:', 'third-audience' ); ?>
		</p>

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Variable', 'third-audience' ); ?></th>
					<th><?php esc_html_e( 'Description', 'third-audience' ); ?></th>
					<th><?php esc_html_e( 'Example', 'third-audience' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code>{category}</code></td>
					<td><?php esc_html_e( 'Product or service category', 'third-audience' ); ?></td>
					<td><?php esc_html_e( 'project management, CRM, email marketing', 'third-audience' ); ?></td>
				</tr>
				<tr>
					<td><code>{use_case}</code></td>
					<td><?php esc_html_e( 'Specific use case or need', 'third-audience' ); ?></td>
					<td><?php esc_html_e( 'remote teams, small businesses, enterprise', 'third-audience' ); ?></td>
				</tr>
				<tr>
					<td><code>{location}</code></td>
					<td><?php esc_html_e( 'Geographic location', 'third-audience' ); ?></td>
					<td><?php esc_html_e( 'United States, Europe, San Francisco', 'third-audience' ); ?></td>
				</tr>
				<tr>
					<td><code>{competitor_name}</code></td>
					<td><?php esc_html_e( 'Name of competitor', 'third-audience' ); ?></td>
					<td><?php esc_html_e( 'Competitor Inc., Brand Name', 'third-audience' ); ?></td>
				</tr>
				<tr>
					<td><code>{your_company}</code></td>
					<td><?php esc_html_e( 'Your company name', 'third-audience' ); ?></td>
					<td><?php echo esc_html( get_bloginfo( 'name' ) ); ?></td>
				</tr>
				<tr>
					<td><code>{target_audience}</code></td>
					<td><?php esc_html_e( 'Target customer segment', 'third-audience' ); ?></td>
					<td><?php esc_html_e( 'developers, marketers, sales teams', 'third-audience' ); ?></td>
				</tr>
				<tr>
					<td><code>{product_type}</code></td>
					<td><?php esc_html_e( 'Type of product', 'third-audience' ); ?></td>
					<td><?php esc_html_e( 'SaaS tool, hardware device, consulting service', 'third-audience' ); ?></td>
				</tr>
				<tr>
					<td><code>{industry}</code></td>
					<td><?php esc_html_e( 'Industry vertical', 'third-audience' ); ?></td>
					<td><?php esc_html_e( 'healthcare, finance, e-commerce', 'third-audience' ); ?></td>
				</tr>
				<tr>
					<td><code>{problem}</code></td>
					<td><?php esc_html_e( 'Problem to solve', 'third-audience' ); ?></td>
					<td><?php esc_html_e( 'improve productivity, reduce costs, automate workflows', 'third-audience' ); ?></td>
				</tr>
				<tr>
					<td><code>{solution_type}</code></td>
					<td><?php esc_html_e( 'Type of solution', 'third-audience' ); ?></td>
					<td><?php esc_html_e( 'software platform, consulting, training', 'third-audience' ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>

	<!-- Tips for Effective Prompts -->
	<div class="ta-card">
		<h3><?php esc_html_e( 'Tips for Effective Test Prompts', 'third-audience' ); ?></h3>
		<ul class="ta-tips-list">
			<li>
				<strong><?php esc_html_e( 'Be specific', 'third-audience' ); ?></strong>
				<?php esc_html_e( 'Use detailed, realistic queries that actual users would ask.', 'third-audience' ); ?>
			</li>
			<li>
				<strong><?php esc_html_e( 'Vary your prompts', 'third-audience' ); ?></strong>
				<?php esc_html_e( 'Test different angles: comparisons, recommendations, problem-solving, etc.', 'third-audience' ); ?>
			</li>
			<li>
				<strong><?php esc_html_e( 'Include context', 'third-audience' ); ?></strong>
				<?php esc_html_e( 'Add relevant details like business size, location, or specific needs.', 'third-audience' ); ?>
			</li>
			<li>
				<strong><?php esc_html_e( 'Test regularly', 'third-audience' ); ?></strong>
				<?php esc_html_e( 'Run the same prompts monthly to track changes in citation patterns.', 'third-audience' ); ?>
			</li>
			<li>
				<strong><?php esc_html_e( 'Mix branded and generic', 'third-audience' ); ?></strong>
				<?php esc_html_e( 'Test both competitor-specific queries and broader category searches.', 'third-audience' ); ?>
			</li>
			<li>
				<strong><?php esc_html_e( 'Document thoroughly', 'third-audience' ); ?></strong>
				<?php esc_html_e( 'Use the notes field to capture insights about the AI responses.', 'third-audience' ); ?>
			</li>
		</ul>
	</div>

	<!-- Custom Prompt Builder -->
	<div class="ta-card">
		<h3><?php esc_html_e( 'Custom Prompt Builder', 'third-audience' ); ?></h3>
		<p class="description">
			<?php esc_html_e( 'Build a custom prompt by filling in the fields below:', 'third-audience' ); ?>
		</p>

		<form id="ta-custom-prompt-form">
			<table class="form-table">
				<tr>
					<th><label for="custom_category"><?php esc_html_e( 'Category', 'third-audience' ); ?></label></th>
					<td>
						<input type="text" id="custom_category" class="regular-text"
						       placeholder="<?php esc_attr_e( 'e.g., project management software', 'third-audience' ); ?>">
					</td>
				</tr>
				<tr>
					<th><label for="custom_use_case"><?php esc_html_e( 'Use Case', 'third-audience' ); ?></label></th>
					<td>
						<input type="text" id="custom_use_case" class="regular-text"
						       placeholder="<?php esc_attr_e( 'e.g., remote teams', 'third-audience' ); ?>">
					</td>
				</tr>
				<tr>
					<th><label for="custom_template"><?php esc_html_e( 'Template', 'third-audience' ); ?></label></th>
					<td>
						<select id="custom_template" class="regular-text">
							<option value="Best {category} for {use_case}"><?php esc_html_e( 'Best X for Y', 'third-audience' ); ?></option>
							<option value="Top {category} companies in {location}"><?php esc_html_e( 'Top X companies in Y', 'third-audience' ); ?></option>
							<option value="Compare {competitor_a} vs {competitor_b}"><?php esc_html_e( 'Compare X vs Y', 'third-audience' ); ?></option>
							<option value="How to {problem} with {solution_type}"><?php esc_html_e( 'How to X with Y', 'third-audience' ); ?></option>
						</select>
					</td>
				</tr>
			</table>

			<div class="ta-custom-prompt-output">
				<h4><?php esc_html_e( 'Generated Prompt:', 'third-audience' ); ?></h4>
				<div id="ta-custom-prompt-result" class="ta-prompt-preview">
					<code><?php esc_html_e( 'Fill in the fields above to generate a custom prompt...', 'third-audience' ); ?></code>
				</div>
				<p>
					<button type="button" class="button button-primary" id="ta-use-custom-prompt" disabled>
						<?php esc_html_e( 'Use in Test', 'third-audience' ); ?>
					</button>
				</p>
			</div>
		</form>
	</div>
</div>
