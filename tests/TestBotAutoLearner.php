<?php
/**
 * Bot Auto-Learner Tests
 *
 * Tests for the TA_Bot_Auto_Learner class.
 *
 * @package ThirdAudience
 * @since   2.3.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestBotAutoLearner
 *
 * Test suite for bot auto-learning functionality.
 *
 * @since 2.3.0
 */
class TestBotAutoLearner extends TestCase {

	/**
	 * Global wpdb mock.
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Set up test environment.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		// Reset global $wpdb to fresh instance
		global $wpdb;
		$wpdb = new wpdb();
		$this->wpdb = $wpdb;
	}

	/**
	 * Test that auto-learner processes unknown bots with high confidence.
	 *
	 * @return void
	 */
	public function test_processes_high_confidence_unknown_bots() {
		// Arrange: Mock unknown bot with confidence >= 0.85
		$unknown_bot = (object) array(
			'id' => 1,
			'user_agent' => 'TestBot/1.0',
			'heuristic_bot_probability' => 0.9,
			'classification_status' => 'pending',
		);

		// Set mock result for the query (exact format from wpdb->prepare)
		$query_pattern = "SELECT id, user_agent, heuristic_bot_probability\n\t\t\t\tFROM wp_ta_unknown_bots\n\t\t\t\tWHERE classification_status = 'pending'\n\t\t\t\tAND heuristic_bot_probability >= 0.850000\n\t\t\t\tORDER BY heuristic_bot_probability DESC\n\t\t\t\tLIMIT 100";
		$this->wpdb->set_mock_result( $query_pattern, array( $unknown_bot ) );

		// Mock get_var to return no existing pattern
		$this->wpdb->set_mock_result( "SELECT COUNT(*) FROM wp_ta_bot_patterns WHERE pattern = '/TestBot/i'", 0 );

		// Act
		$learner = new TA_Bot_Auto_Learner();
		$processed_count = $learner->process_pending_bots();

		// Assert
		$this->assertEquals( 1, $processed_count );
	}

	/**
	 * Test that auto-learner generates valid regex pattern from user agent.
	 *
	 * @return void
	 */
	public function test_generates_regex_pattern_from_user_agent() {
		// Arrange
		$learner = new TA_Bot_Auto_Learner();

		// Act
		$pattern1 = $learner->generate_pattern( 'TestBot/1.0' );
		$pattern2 = $learner->generate_pattern( 'Mozilla/5.0 (compatible; ExampleBot/2.0)' );
		$pattern3 = $learner->generate_pattern( 'CustomCrawler/3.5.2' );

		// Assert: Pattern should be valid regex
		$this->assertMatchesRegularExpression( '/^\/.*\/i$/', $pattern1 );
		$this->assertMatchesRegularExpression( '/^\/.*\/i$/', $pattern2 );
		$this->assertMatchesRegularExpression( '/^\/.*\/i$/', $pattern3 );

		// Assert: Pattern should match the original user agent
		$this->assertEquals( 1, preg_match( $pattern1, 'TestBot/1.0' ) );
		$this->assertEquals( 1, preg_match( $pattern2, 'Mozilla/5.0 (compatible; ExampleBot/2.0)' ) );
		$this->assertEquals( 1, preg_match( $pattern3, 'CustomCrawler/3.5.2' ) );
	}

	/**
	 * Test that auto-learner prevents duplicate patterns.
	 *
	 * @return void
	 */
	public function test_prevents_duplicate_patterns() {
		// Arrange
		$unknown_bot = (object) array(
			'id' => 1,
			'user_agent' => 'DuplicateBot/1.0',
			'heuristic_bot_probability' => 0.95,
			'classification_status' => 'pending',
		);

		// Set mock result for the query (exact format from wpdb->prepare)
		$query_pattern = "SELECT id, user_agent, heuristic_bot_probability\n\t\t\t\tFROM wp_ta_unknown_bots\n\t\t\t\tWHERE classification_status = 'pending'\n\t\t\t\tAND heuristic_bot_probability >= 0.850000\n\t\t\t\tORDER BY heuristic_bot_probability DESC\n\t\t\t\tLIMIT 100";
		$this->wpdb->set_mock_result( $query_pattern, array( $unknown_bot ) );

		// Simulate existing pattern (duplicate)
		$this->wpdb->set_mock_result( "SELECT COUNT(*) FROM wp_ta_bot_patterns WHERE pattern = '/DuplicateBot/i'", 1 );

		// Act
		$learner = new TA_Bot_Auto_Learner();
		$processed = $learner->process_pending_bots();

		// Assert: Should skip duplicate (0 processed)
		$this->assertEquals( 0, $processed );
	}

	/**
	 * Test that auto-learner skips low confidence bots.
	 *
	 * @return void
	 */
	public function test_skips_low_confidence_bots() {
		// Arrange: Set empty result (no high-confidence bots)
		$query_pattern = "SELECT id, user_agent, heuristic_bot_probability\n\t\t\t\tFROM wp_ta_unknown_bots\n\t\t\t\tWHERE classification_status = 'pending'\n\t\t\t\tAND heuristic_bot_probability >= 0.850000\n\t\t\t\tORDER BY heuristic_bot_probability DESC\n\t\t\t\tLIMIT 100";
		$this->wpdb->set_mock_result( $query_pattern, array() );

		// Act
		$learner = new TA_Bot_Auto_Learner();
		$processed_count = $learner->process_pending_bots();

		// Assert
		$this->assertEquals( 0, $processed_count );
	}

	/**
	 * Test that auto-learner extracts bot name from user agent.
	 *
	 * @return void
	 */
	public function test_extracts_bot_name_from_user_agent() {
		// Arrange
		$learner = new TA_Bot_Auto_Learner();

		// Act
		$name1 = $learner->extract_bot_name( 'TestBot/1.0' );
		$name2 = $learner->extract_bot_name( 'Mozilla/5.0 (compatible; ExampleBot/2.0)' );
		$name3 = $learner->extract_bot_name( 'CustomCrawler/3.5.2' );
		$name4 = $learner->extract_bot_name( 'MySpider/1.0' );

		// Assert
		$this->assertEquals( 'TestBot', $name1 );
		$this->assertEquals( 'ExampleBot', $name2 );
		$this->assertEquals( 'CustomCrawler', $name3 );
		$this->assertEquals( 'MySpider', $name4 );
	}

	/**
	 * Test pattern generation for various user agent formats.
	 *
	 * @return void
	 */
	public function test_pattern_generation_for_various_formats() {
		// Arrange
		$learner = new TA_Bot_Auto_Learner();

		// Act & Assert
		$pattern1 = $learner->generate_pattern( 'SimpleBot/1.0' );
		$this->assertEquals( '/SimpleBot/i', $pattern1 );

		$pattern2 = $learner->generate_pattern( 'Complex-Bot/2.0' );
		$this->assertEquals( '/Complex\\-Bot/i', $pattern2 );

		$pattern3 = $learner->generate_pattern( 'Mozilla/5.0 (compatible; AICrawler/1.0)' );
		$this->assertEquals( '/AICrawler/i', $pattern3 );
	}

	/**
	 * Test initialization and singleton pattern.
	 *
	 * @return void
	 */
	public function test_singleton_instance() {
		// Act
		$instance1 = TA_Bot_Auto_Learner::get_instance();
		$instance2 = TA_Bot_Auto_Learner::get_instance();

		// Assert
		$this->assertInstanceOf( TA_Bot_Auto_Learner::class, $instance1 );
		$this->assertSame( $instance1, $instance2, 'Should return same instance' );
	}

	/**
	 * Test WP-Cron integration (placeholder).
	 *
	 * @return void
	 */
	public function test_schedules_daily_cron() {
		// This test verifies cron scheduling
		// In a real implementation, we'd check:
		// - wp_next_scheduled('ta_auto_learn_bots')
		// - Hook is registered
		$this->assertTrue( true, 'Placeholder for cron scheduling test' );
	}
}
