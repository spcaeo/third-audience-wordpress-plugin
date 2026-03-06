<?php
/**
 * Tests for Bot Analytics Integration with Detection Pipeline.
 *
 * @package ThirdAudience
 * @since   2.3.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class BotAnalyticsIntegrationTest
 *
 * Tests the integration between TA_Bot_Analytics and TA_Bot_Detection_Pipeline.
 */
class BotAnalyticsIntegrationTest extends TestCase {

	/**
	 * Bot analytics instance.
	 *
	 * @var TA_Bot_Analytics
	 */
	private $analytics;

	/**
	 * Set up test fixtures.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->analytics = TA_Bot_Analytics::get_instance();
	}

	/**
	 * Test detect_bot() returns compatible array format.
	 */
	public function test_detect_bot_returns_compatible_array_format() {
		$user_agent = 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko) ChatGPT-User/1.0 Chrome/120.0.0.0 Safari/537.36';

		$result = $this->analytics->detect_bot( $user_agent );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'type', $result );
		$this->assertArrayHasKey( 'name', $result );
		$this->assertArrayHasKey( 'color', $result );
		$this->assertArrayHasKey( 'priority', $result );
	}

	/**
	 * Test detect_bot() with known AI bot (ClaudeBot).
	 */
	public function test_detect_bot_with_claudebot() {
		$user_agent = 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; ClaudeBot/1.0; +claudebot@anthropic.com)';

		$result = $this->analytics->detect_bot( $user_agent );

		$this->assertIsArray( $result );
		$this->assertEquals( 'ClaudeBot', $result['type'] );
		$this->assertEquals( 'Claude (Anthropic)', $result['name'] );
		$this->assertEquals( '#D97757', $result['color'] );
		$this->assertEquals( 'high', $result['priority'] );
	}

	/**
	 * Test detect_bot() with known AI bot (GPTBot).
	 */
	public function test_detect_bot_with_gptbot() {
		$user_agent = 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.0; +https://openai.com/gptbot)';

		$result = $this->analytics->detect_bot( $user_agent );

		$this->assertIsArray( $result );
		$this->assertEquals( 'GPTBot', $result['type'] );
		$this->assertEquals( 'GPT (OpenAI)', $result['name'] );
		$this->assertEquals( '#10A37F', $result['color'] );
		$this->assertEquals( 'high', $result['priority'] );
	}

	/**
	 * Test detect_bot() with non-bot user agent.
	 */
	public function test_detect_bot_with_normal_browser() {
		$user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

		$result = $this->analytics->detect_bot( $user_agent );

		$this->assertFalse( $result );
	}

	/**
	 * Test detect_bot() with empty user agent.
	 */
	public function test_detect_bot_with_empty_user_agent() {
		$result = $this->analytics->detect_bot( '' );

		$this->assertFalse( $result );
	}

	/**
	 * Test get_bot_detection_result() returns TA_Bot_Detection_Result object.
	 */
	public function test_get_bot_detection_result_returns_object() {
		$user_agent = 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.0)';

		$result = $this->analytics->get_bot_detection_result( $user_agent );

		$this->assertInstanceOf( 'TA_Bot_Detection_Result', $result );
		$this->assertTrue( $result->is_bot() );
		$this->assertGreaterThan( 0.5, $result->get_confidence() );
		$this->assertEquals( 'GPT (OpenAI)', $result->get_bot_name() );
	}

	/**
	 * Test track_visit() stores detection method and confidence.
	 */
	public function test_track_visit_stores_detection_data() {
		global $wpdb;

		$data = array(
			'bot_type'          => 'GPTBot',
			'bot_name'          => 'GPT (OpenAI)',
			'user_agent'        => 'Mozilla/5.0 (compatible; GPTBot/1.0)',
			'url'               => 'https://example.com/test',
			'detection_method'  => 'database_pattern',
			'confidence_score'  => 0.95,
		);

		$insert_id = $this->analytics->track_visit( $data );

		$this->assertIsInt( $insert_id );
		$this->assertGreaterThan( 0, $insert_id );

		// Verify data was stored correctly.
		$table_name = $wpdb->prefix . TA_Bot_Analytics::TABLE_NAME;
		$row        = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $insert_id ),
			ARRAY_A
		);

		$this->assertEquals( 'database_pattern', $row['detection_method'] );
		$this->assertEquals( 0.95, (float) $row['confidence_score'] );
	}

	/**
	 * Test pattern migration runs only once.
	 */
	public function test_pattern_migration_runs_once() {
		global $wpdb;

		// Check if migration flag is set after first run.
		$patterns_table = $wpdb->prefix . 'ta_bot_patterns';

		// Count patterns migrated.
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$patterns_table}" );

		$this->assertGreaterThan( 0, $count, 'Patterns should be migrated' );

		// Check migration flag.
		$migrated = get_option( 'ta_bot_patterns_migrated', false );
		$this->assertTrue( (bool) $migrated, 'Migration flag should be set' );

		// Run migration again - should not duplicate.
		$this->analytics->maybe_migrate_patterns();

		$new_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$patterns_table}" );
		$this->assertEquals( $count, $new_count, 'Pattern count should not change on second run' );
	}

	/**
	 * Test database columns exist for detection tracking.
	 */
	public function test_analytics_table_has_detection_columns() {
		global $wpdb;
		$table_name = $wpdb->prefix . TA_Bot_Analytics::TABLE_NAME;

		// Check if columns exist.
		$columns = $wpdb->get_results( "SHOW COLUMNS FROM {$table_name}" );
		$column_names = array_column( $columns, 'Field' );

		$this->assertContains( 'detection_method', $column_names );
		$this->assertContains( 'confidence_score', $column_names );
	}

	/**
	 * Test backward compatibility with existing code.
	 */
	public function test_backward_compatibility() {
		// Old code that expects specific array format.
		$user_agent = 'Mozilla/5.0 (compatible; ClaudeBot/1.0)';
		$bot_info = $this->analytics->detect_bot( $user_agent );

		// Should still work with old code expecting these keys.
		if ( $bot_info ) {
			$bot_type = $bot_info['type'];
			$bot_name = $bot_info['name'];
			$color    = $bot_info['color'];
			$priority = $bot_info['priority'];

			$this->assertEquals( 'ClaudeBot', $bot_type );
			$this->assertEquals( 'Claude (Anthropic)', $bot_name );
			$this->assertNotEmpty( $color );
			$this->assertNotEmpty( $priority );
		}
	}

	/**
	 * Test all known bots are detected correctly.
	 */
	public function test_all_known_bots_detected() {
		$test_cases = array(
			'ClaudeBot'         => 'Mozilla/5.0 (compatible; ClaudeBot/1.0)',
			'GPTBot'            => 'Mozilla/5.0 (compatible; GPTBot/1.0)',
			'ChatGPT-User'      => 'Mozilla/5.0 (compatible; ChatGPT-User/1.0)',
			'PerplexityBot'     => 'Mozilla/5.0 (compatible; PerplexityBot/1.0)',
			'Bytespider'        => 'Mozilla/5.0 (compatible; Bytespider/1.0)',
			'anthropic-ai'      => 'Mozilla/5.0 (compatible; anthropic-ai/1.0)',
			'cohere-ai'         => 'Mozilla/5.0 (compatible; cohere-ai/1.0)',
			'Google-Extended'   => 'Mozilla/5.0 (compatible; Google-Extended/1.0)',
			'FacebookBot'       => 'Mozilla/5.0 (compatible; FacebookBot/1.0)',
			'Applebot-Extended' => 'Mozilla/5.0 (compatible; Applebot-Extended/1.0)',
		);

		foreach ( $test_cases as $expected_type => $user_agent ) {
			$result = $this->analytics->detect_bot( $user_agent );

			$this->assertIsArray( $result, "Failed to detect: {$user_agent}" );
			$this->assertEquals( $expected_type, $result['type'], "Wrong bot type for: {$user_agent}" );
		}
	}

	/**
	 * Test detection pipeline integration.
	 */
	public function test_pipeline_integration() {
		$user_agent = 'Mozilla/5.0 (compatible; GPTBot/1.0)';

		// Get detection result from pipeline.
		$detection_result = $this->analytics->get_bot_detection_result( $user_agent );

		$this->assertInstanceOf( 'TA_Bot_Detection_Result', $detection_result );
		$this->assertTrue( $detection_result->is_bot() );

		// Verify detect_bot() uses pipeline internally.
		$bot_info = $this->analytics->detect_bot( $user_agent );

		$this->assertIsArray( $bot_info );
		$this->assertEquals( 'GPTBot', $bot_info['type'] );
	}

	/**
	 * Test custom bot patterns from database.
	 */
	public function test_custom_bot_patterns_from_database() {
		global $wpdb;

		// Insert a custom pattern.
		$patterns_table = $wpdb->prefix . 'ta_bot_patterns';
		$wpdb->insert(
			$patterns_table,
			array(
				'pattern'      => 'CustomBot',
				'bot_name'     => 'Custom Test Bot',
				'bot_vendor'   => 'Test Vendor',
				'bot_category' => 'ai',
				'priority'     => 'medium',
				'is_active'    => 1,
			)
		);

		$user_agent = 'Mozilla/5.0 (compatible; CustomBot/1.0)';
		$result = $this->analytics->detect_bot( $user_agent );

		$this->assertIsArray( $result );
		$this->assertEquals( 'Custom Test Bot', $result['name'] );
	}
}
