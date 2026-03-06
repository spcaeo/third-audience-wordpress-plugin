<?php
/**
 * Competitor Benchmarking - Track and analyze AI platform citations of competitors.
 *
 * Provides tools to track which competitors get cited by AI platforms like ChatGPT,
 * Perplexity, Claude, etc. Helps identify competitive positioning and content gaps.
 *
 * @package ThirdAudience
 * @since   3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Competitor_Benchmarking
 *
 * Manages competitor tracking, prompt generation, and citation benchmarking.
 *
 * @since 3.1.0
 */
class TA_Competitor_Benchmarking {

	/**
	 * Database table name.
	 *
	 * @var string
	 */
	const TABLE_NAME = 'ta_competitor_benchmarks';

	/**
	 * Database version for migrations.
	 *
	 * @var string
	 */
	const DB_VERSION = '1.0.0';

	/**
	 * Option name for database version.
	 *
	 * @var string
	 */
	const DB_VERSION_OPTION = 'ta_competitor_benchmarks_db_version';

	/**
	 * AI platforms for testing.
	 *
	 * @var array
	 */
	private static $ai_platforms = array(
		'chatgpt'    => 'ChatGPT',
		'perplexity' => 'Perplexity',
		'claude'     => 'Claude',
		'gemini'     => 'Google Gemini',
		'copilot'    => 'Microsoft Copilot',
		'you'        => 'You.com',
		'other'      => 'Other',
	);

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Security instance.
	 *
	 * @var TA_Security
	 */
	private $security;

	/**
	 * Singleton instance.
	 *
	 * @var TA_Competitor_Benchmarking|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 3.1.0
	 * @return TA_Competitor_Benchmarking
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 3.1.0
	 */
	private function __construct() {
		$this->logger   = TA_Logger::get_instance();
		$this->security = TA_Security::get_instance();
		$this->maybe_create_table();
	}

	/**
	 * Create database table if it doesn't exist.
	 *
	 * @since 3.1.0
	 * @return void
	 */
	private function maybe_create_table() {
		$installed_version = get_option( self::DB_VERSION_OPTION, '0.0.0' );

		if ( version_compare( $installed_version, self::DB_VERSION, '>=' ) ) {
			return;
		}

		global $wpdb;
		$table_name      = $wpdb->prefix . self::TABLE_NAME;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			competitor_url varchar(255) NOT NULL,
			competitor_name varchar(100) NOT NULL,
			test_prompt text NOT NULL,
			ai_platform varchar(50) NOT NULL,
			cited_rank tinyint(2) DEFAULT NULL COMMENT 'Rank position 1-5, NULL if not cited',
			test_date datetime NOT NULL,
			test_notes text,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY competitor_url (competitor_url),
			KEY ai_platform (ai_platform),
			KEY test_date (test_date),
			KEY cited_rank (cited_rank)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( self::DB_VERSION_OPTION, self::DB_VERSION );

		$this->logger->info( 'Competitor Benchmarking table created.', array(
			'version' => self::DB_VERSION,
		) );
	}

	/**
	 * Get all AI platforms.
	 *
	 * @since 3.1.0
	 * @return array
	 */
	public static function get_ai_platforms() {
		return self::$ai_platforms;
	}

	/**
	 * Add a competitor.
	 *
	 * @since 3.1.0
	 * @param string $url  Competitor URL.
	 * @param string $name Competitor name.
	 * @return int|WP_Error Competitor ID or error.
	 */
	public function add_competitor( $url, $name ) {
		$url  = esc_url_raw( $url );
		$name = sanitize_text_field( $name );

		if ( empty( $url ) || empty( $name ) ) {
			return new WP_Error( 'invalid_data', __( 'Competitor URL and name are required.', 'third-audience' ) );
		}

		// Check if competitor already exists.
		$competitors = $this->get_competitors();
		foreach ( $competitors as $competitor ) {
			if ( $competitor['competitor_url'] === $url ) {
				return new WP_Error( 'duplicate_competitor', __( 'This competitor URL already exists.', 'third-audience' ) );
			}
		}

		// Store in option (we'll use the benchmarks table for actual test results).
		$competitors[] = array(
			'url'  => $url,
			'name' => $name,
		);

		update_option( 'ta_competitors_list', $competitors );

		$this->logger->info( 'Competitor added.', array(
			'name' => $name,
			'url'  => $url,
		) );

		return count( $competitors );
	}

	/**
	 * Get all competitors.
	 *
	 * @since 3.1.0
	 * @return array
	 */
	public function get_competitors() {
		return get_option( 'ta_competitors_list', array() );
	}

	/**
	 * Delete a competitor.
	 *
	 * @since 3.1.0
	 * @param string $url Competitor URL.
	 * @return bool
	 */
	public function delete_competitor( $url ) {
		$competitors = $this->get_competitors();
		$new_list    = array();

		foreach ( $competitors as $competitor ) {
			if ( $competitor['url'] !== $url ) {
				$new_list[] = $competitor;
			}
		}

		update_option( 'ta_competitors_list', $new_list );

		$this->logger->info( 'Competitor deleted.', array( 'url' => $url ) );

		return true;
	}

	/**
	 * Record a benchmark test result.
	 *
	 * @since 3.1.0
	 * @param array $data Test result data.
	 * @return int|false Insert ID or false on failure.
	 */
	public function record_test( $data ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$sanitized = array(
			'competitor_url'  => esc_url_raw( $data['competitor_url'] ),
			'competitor_name' => sanitize_text_field( $data['competitor_name'] ),
			'test_prompt'     => sanitize_textarea_field( $data['test_prompt'] ),
			'ai_platform'     => sanitize_text_field( $data['ai_platform'] ),
			'cited_rank'      => isset( $data['cited_rank'] ) && $data['cited_rank'] !== '' ? absint( $data['cited_rank'] ) : null,
			'test_date'       => isset( $data['test_date'] ) ? sanitize_text_field( $data['test_date'] ) : current_time( 'mysql' ),
			'test_notes'      => isset( $data['test_notes'] ) ? sanitize_textarea_field( $data['test_notes'] ) : '',
		);

		$result = $wpdb->insert(
			$table_name,
			$sanitized,
			array( '%s', '%s', '%s', '%s', '%d', '%s', '%s' )
		);

		if ( $result ) {
			$this->logger->info( 'Benchmark test recorded.', $sanitized );
			return $wpdb->insert_id;
		}

		return false;
	}

	/**
	 * Get benchmark results with filters.
	 *
	 * @since 3.1.0
	 * @param array $filters Filter criteria.
	 * @param int   $limit   Results limit.
	 * @param int   $offset  Results offset.
	 * @return array
	 */
	public function get_results( $filters = array(), $limit = 50, $offset = 0 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$where_clauses = array( '1=1' );

		if ( ! empty( $filters['competitor_url'] ) ) {
			$where_clauses[] = $wpdb->prepare( 'competitor_url = %s', $filters['competitor_url'] );
		}

		if ( ! empty( $filters['ai_platform'] ) ) {
			$where_clauses[] = $wpdb->prepare( 'ai_platform = %s', $filters['ai_platform'] );
		}

		if ( ! empty( $filters['date_from'] ) ) {
			$where_clauses[] = $wpdb->prepare( 'DATE(test_date) >= %s', $filters['date_from'] );
		}

		if ( ! empty( $filters['date_to'] ) ) {
			$where_clauses[] = $wpdb->prepare( 'DATE(test_date) <= %s', $filters['date_to'] );
		}

		if ( isset( $filters['cited_only'] ) && $filters['cited_only'] ) {
			$where_clauses[] = 'cited_rank IS NOT NULL';
		}

		$where_sql = implode( ' AND ', $where_clauses );

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table_name}
				WHERE {$where_sql}
				ORDER BY test_date DESC
				LIMIT %d OFFSET %d",
				$limit,
				$offset
			),
			ARRAY_A
		);

		return $results ? $results : array();
	}

	/**
	 * Get total results count with filters.
	 *
	 * @since 3.1.0
	 * @param array $filters Filter criteria.
	 * @return int
	 */
	public function get_results_count( $filters = array() ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$where_clauses = array( '1=1' );

		if ( ! empty( $filters['competitor_url'] ) ) {
			$where_clauses[] = $wpdb->prepare( 'competitor_url = %s', $filters['competitor_url'] );
		}

		if ( ! empty( $filters['ai_platform'] ) ) {
			$where_clauses[] = $wpdb->prepare( 'ai_platform = %s', $filters['ai_platform'] );
		}

		if ( ! empty( $filters['date_from'] ) ) {
			$where_clauses[] = $wpdb->prepare( 'DATE(test_date) >= %s', $filters['date_from'] );
		}

		if ( ! empty( $filters['date_to'] ) ) {
			$where_clauses[] = $wpdb->prepare( 'DATE(test_date) <= %s', $filters['date_to'] );
		}

		if ( isset( $filters['cited_only'] ) && $filters['cited_only'] ) {
			$where_clauses[] = 'cited_rank IS NOT NULL';
		}

		$where_sql = implode( ' AND ', $where_clauses );

		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name} WHERE {$where_sql}" );

		return absint( $count );
	}

	/**
	 * Get competitive insights.
	 *
	 * @since 3.1.0
	 * @param array $filters Filter criteria.
	 * @return array
	 */
	public function get_insights( $filters = array() ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$where_clauses = array( '1=1' );

		if ( ! empty( $filters['date_from'] ) ) {
			$where_clauses[] = $wpdb->prepare( 'DATE(test_date) >= %s', $filters['date_from'] );
		}

		if ( ! empty( $filters['date_to'] ) ) {
			$where_clauses[] = $wpdb->prepare( 'DATE(test_date) <= %s', $filters['date_to'] );
		}

		$where_sql = implode( ' AND ', $where_clauses );

		// Get citation rates by competitor.
		$citation_rates = $wpdb->get_results(
			"SELECT
				competitor_name,
				competitor_url,
				COUNT(*) as total_tests,
				SUM(CASE WHEN cited_rank IS NOT NULL THEN 1 ELSE 0 END) as citations,
				ROUND(SUM(CASE WHEN cited_rank IS NOT NULL THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as citation_rate,
				AVG(CASE WHEN cited_rank IS NOT NULL THEN cited_rank ELSE NULL END) as avg_rank
			FROM {$table_name}
			WHERE {$where_sql}
			GROUP BY competitor_url, competitor_name
			ORDER BY citation_rate DESC",
			ARRAY_A
		);

		// Get platform performance.
		$platform_performance = $wpdb->get_results(
			"SELECT
				ai_platform,
				COUNT(*) as total_tests,
				SUM(CASE WHEN cited_rank IS NOT NULL THEN 1 ELSE 0 END) as citations,
				ROUND(SUM(CASE WHEN cited_rank IS NOT NULL THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as citation_rate
			FROM {$table_name}
			WHERE {$where_sql}
			GROUP BY ai_platform
			ORDER BY total_tests DESC",
			ARRAY_A
		);

		// Get top performing prompts.
		$top_prompts = $wpdb->get_results(
			"SELECT
				test_prompt,
				COUNT(*) as test_count,
				SUM(CASE WHEN cited_rank IS NOT NULL THEN 1 ELSE 0 END) as citation_count,
				ROUND(SUM(CASE WHEN cited_rank IS NOT NULL THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as citation_rate
			FROM {$table_name}
			WHERE {$where_sql}
			GROUP BY test_prompt
			HAVING test_count >= 2
			ORDER BY citation_rate DESC
			LIMIT 10",
			ARRAY_A
		);

		// Get trend data (last 30 days, grouped by date).
		$trend_data = $wpdb->get_results(
			"SELECT
				DATE(test_date) as date,
				COUNT(*) as total_tests,
				SUM(CASE WHEN cited_rank IS NOT NULL THEN 1 ELSE 0 END) as citations
			FROM {$table_name}
			WHERE {$where_sql}
			GROUP BY DATE(test_date)
			ORDER BY date DESC
			LIMIT 30",
			ARRAY_A
		);

		return array(
			'citation_rates'       => $citation_rates ? $citation_rates : array(),
			'platform_performance' => $platform_performance ? $platform_performance : array(),
			'top_prompts'          => $top_prompts ? $top_prompts : array(),
			'trend_data'           => $trend_data ? $trend_data : array(),
		);
	}

	/**
	 * Get prompt templates.
	 *
	 * @since 3.1.0
	 * @return array
	 */
	public function get_prompt_templates() {
		return array(
			'general' => array(
				'name'      => __( 'General Recommendations', 'third-audience' ),
				'templates' => array(
					__( 'Best {category} tools for {use_case}', 'third-audience' ),
					__( 'Top {category} companies in {location}', 'third-audience' ),
					__( 'Compare {competitor_a} vs {competitor_b} vs {your_company}', 'third-audience' ),
					__( 'What are the leading {category} solutions for {target_audience}?', 'third-audience' ),
					__( 'Which {category} provider is best for {specific_need}?', 'third-audience' ),
				),
			),
			'saas'    => array(
				'name'      => __( 'SaaS Products', 'third-audience' ),
				'templates' => array(
					__( 'Best project management software for remote teams', 'third-audience' ),
					__( 'Top CRM platforms for small businesses', 'third-audience' ),
					__( 'Most affordable email marketing tools', 'third-audience' ),
					__( 'Best alternatives to {competitor_name}', 'third-audience' ),
					__( 'Compare {your_product} vs {competitor_product} features', 'third-audience' ),
				),
			),
			'ecommerce' => array(
				'name'      => __( 'E-commerce', 'third-audience' ),
				'templates' => array(
					__( 'Best places to buy {product_category} online', 'third-audience' ),
					__( 'Top {product_type} retailers in {location}', 'third-audience' ),
					__( 'Where to find affordable {product_category}', 'third-audience' ),
					__( 'Most reliable {product_type} suppliers', 'third-audience' ),
					__( 'Compare prices for {product_name} across retailers', 'third-audience' ),
				),
			),
			'consulting' => array(
				'name'      => __( 'Consulting & Services', 'third-audience' ),
				'templates' => array(
					__( 'Best {service_type} consultants for {industry}', 'third-audience' ),
					__( 'Top {service_type} agencies in {location}', 'third-audience' ),
					__( 'How to choose a {service_type} provider', 'third-audience' ),
					__( 'What makes a good {service_type} consultant?', 'third-audience' ),
					__( '{Service_type} agency recommendations for {business_size}', 'third-audience' ),
				),
			),
			'problem_solving' => array(
				'name'      => __( 'Problem Solving', 'third-audience' ),
				'templates' => array(
					__( 'How to {solve_problem} with {solution_type}', 'third-audience' ),
					__( 'Best way to {achieve_goal} using {method}', 'third-audience' ),
					__( 'Solutions for {common_problem} in {industry}', 'third-audience' ),
					__( 'How do {industry_leaders} handle {challenge}?', 'third-audience' ),
					__( 'What is the most effective approach to {problem}?', 'third-audience' ),
				),
			),
		);
	}

	/**
	 * Generate test prompts based on competitor data.
	 *
	 * @since 3.1.0
	 * @param string $competitor_url  Competitor URL.
	 * @param string $competitor_name Competitor name.
	 * @return array
	 */
	public function generate_prompts( $competitor_url, $competitor_name ) {
		$templates = $this->get_prompt_templates();
		$prompts   = array();

		// Extract domain for context.
		$domain = wp_parse_url( $competitor_url, PHP_URL_HOST );
		$domain = str_replace( 'www.', '', $domain );

		// Generate prompts from templates.
		foreach ( $templates as $category => $data ) {
			$category_prompts = array();

			foreach ( $data['templates'] as $template ) {
				// Simple variable replacement.
				$prompt = str_replace(
					array(
						'{competitor_name}',
						'{your_company}',
						'{competitor_a}',
						'{competitor_b}',
					),
					array(
						$competitor_name,
						get_bloginfo( 'name' ),
						$competitor_name,
						get_bloginfo( 'name' ),
					),
					$template
				);

				$category_prompts[] = $prompt;
			}

			$prompts[ $category ] = array(
				'name'    => $data['name'],
				'prompts' => $category_prompts,
			);
		}

		return $prompts;
	}

	/**
	 * Export results to CSV.
	 *
	 * @since 3.1.0
	 * @param array $filters Filter criteria.
	 * @return void
	 */
	public function export_to_csv( $filters = array() ) {
		$results = $this->get_results( $filters, 10000, 0 );

		// Generate filename.
		$filename = 'competitor-benchmarks-' . gmdate( 'Y-m-d-H-i-s' ) . '.csv';

		// Set headers.
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// Open output stream.
		$output = fopen( 'php://output', 'w' );

		// Add BOM for UTF-8.
		fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

		// Add header metadata.
		fputcsv( $output, array( 'Third Audience Competitor Benchmarking Export' ) );
		fputcsv( $output, array( 'Generated', gmdate( 'Y-m-d H:i:s' ) . ' UTC' ) );
		fputcsv( $output, array( 'Total Records', count( $results ) ) );
		fputcsv( $output, array() );

		// CSV headers.
		fputcsv(
			$output,
			array(
				'ID',
				'Competitor Name',
				'Competitor URL',
				'Test Prompt',
				'AI Platform',
				'Cited Rank',
				'Test Date',
				'Test Notes',
				'Created At',
			)
		);

		// Export data rows.
		foreach ( $results as $row ) {
			fputcsv(
				$output,
				array(
					$row['id'],
					$row['competitor_name'],
					$row['competitor_url'],
					$row['test_prompt'],
					$row['ai_platform'],
					$row['cited_rank'],
					$row['test_date'],
					$row['test_notes'],
					$row['created_at'],
				)
			);
		}

		fclose( $output );
		exit;
	}

	/**
	 * Delete a test result.
	 *
	 * @since 3.1.0
	 * @param int $id Test ID.
	 * @return bool
	 */
	public function delete_test( $id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$result = $wpdb->delete(
			$table_name,
			array( 'id' => absint( $id ) ),
			array( '%d' )
		);

		if ( $result ) {
			$this->logger->info( 'Benchmark test deleted.', array( 'id' => $id ) );
			return true;
		}

		return false;
	}

	/**
	 * Clear all benchmark data.
	 *
	 * @since 3.1.0
	 * @return int Number of rows deleted.
	 */
	public function clear_all_data() {
		global $wpdb;
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		$count = $wpdb->query( "DELETE FROM {$table_name}" );

		$this->logger->info( 'All benchmark data cleared.', array( 'count' => $count ) );

		return absint( $count );
	}
}
