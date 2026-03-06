<?php
/**
 * Competitor Benchmarking Page
 *
 * Admin page for tracking competitor citations in AI platforms.
 *
 * @package ThirdAudience
 * @since   3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$benchmarking = TA_Competitor_Benchmarking::get_instance();
$competitors  = $benchmarking->get_competitors();

// Get current tab.
$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'overview';

// Get filters.
$filters = array();
if ( ! empty( $_GET['competitor_url'] ) ) {
	$filters['competitor_url'] = sanitize_text_field( wp_unslash( $_GET['competitor_url'] ) );
}
if ( ! empty( $_GET['ai_platform'] ) ) {
	$filters['ai_platform'] = sanitize_text_field( wp_unslash( $_GET['ai_platform'] ) );
}
if ( ! empty( $_GET['date_from'] ) ) {
	$filters['date_from'] = sanitize_text_field( wp_unslash( $_GET['date_from'] ) );
}
if ( ! empty( $_GET['date_to'] ) ) {
	$filters['date_to'] = sanitize_text_field( wp_unslash( $_GET['date_to'] ) );
}

// Get insights.
$insights = $benchmarking->get_insights( $filters );

// Pagination.
$current_page = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$per_page     = 25;
$offset       = ( $current_page - 1 ) * $per_page;

$results      = $benchmarking->get_results( $filters, $per_page, $offset );
$total_results = $benchmarking->get_results_count( $filters );
$total_pages  = ceil( $total_results / $per_page );

?>

<div class="wrap ta-competitor-benchmarking">
	<h1><?php esc_html_e( 'Competitor Benchmarking', 'third-audience' ); ?></h1>

	<p class="description">
		<?php esc_html_e( 'Track which competitors get cited by AI platforms like ChatGPT, Perplexity, and Claude. Generate test prompts, record results, and analyze competitive positioning over time.', 'third-audience' ); ?>
	</p>

	<!-- Tabs Navigation -->
	<nav class="nav-tab-wrapper wp-clearfix">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-competitor-benchmarking&tab=overview' ) ); ?>"
		   class="nav-tab <?php echo 'overview' === $current_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Overview', 'third-audience' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-competitor-benchmarking&tab=competitors' ) ); ?>"
		   class="nav-tab <?php echo 'competitors' === $current_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Competitors', 'third-audience' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-competitor-benchmarking&tab=test' ) ); ?>"
		   class="nav-tab <?php echo 'test' === $current_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Run Test', 'third-audience' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-competitor-benchmarking&tab=results' ) ); ?>"
		   class="nav-tab <?php echo 'results' === $current_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Results', 'third-audience' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-competitor-benchmarking&tab=prompts' ) ); ?>"
		   class="nav-tab <?php echo 'prompts' === $current_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Prompt Templates', 'third-audience' ); ?>
		</a>
	</nav>

	<!-- Tab Content -->
	<div class="ta-tab-content">
		<?php
		switch ( $current_tab ) {
			case 'overview':
				include __DIR__ . '/competitor-benchmarking-overview.php';
				break;

			case 'competitors':
				include __DIR__ . '/competitor-benchmarking-competitors.php';
				break;

			case 'test':
				include __DIR__ . '/competitor-benchmarking-test.php';
				break;

			case 'results':
				include __DIR__ . '/competitor-benchmarking-results.php';
				break;

			case 'prompts':
				include __DIR__ . '/competitor-benchmarking-prompts.php';
				break;

			default:
				include __DIR__ . '/competitor-benchmarking-overview.php';
				break;
		}
		?>
	</div>
</div>
