<?php
/**
 * Tests for External Bot Database Sync
 *
 * @package ThirdAudience
 */

use PHPUnit\Framework\TestCase;

/**
 * Test External Bot DB Sync
 */
class ExternalBotDBSyncTest extends TestCase {

	/**
	 * Setup before each test.
	 */
	protected function setUp(): void {
		parent::setUp();
		global $_mock_remote_responses, $_mock_scheduled_events, $_mock_options, $wpdb;
		$_mock_remote_responses = array();
		$_mock_scheduled_events = array();
		$_mock_options = array();
		$wpdb->clear_queries();
		TA_Logger::clear_logs();
	}

	/**
	 * Test class exists and can be instantiated.
	 */
	public function test_class_exists() {
		$this->assertTrue( class_exists( 'TA_External_Bot_DB_Sync' ) );
	}

	/**
	 * Test singleton pattern.
	 */
	public function test_singleton_pattern() {
		$instance1 = TA_External_Bot_DB_Sync::get_instance();
		$instance2 = TA_External_Bot_DB_Sync::get_instance();
		
		$this->assertInstanceOf( 'TA_External_Bot_DB_Sync', $instance1 );
		$this->assertSame( $instance1, $instance2 );
	}

	/**
	 * Test fetch patterns from external URL.
	 */
	public function test_fetch_patterns_from_url() {
		global $_mock_remote_responses;

		// Mock successful response with PHP array content.
		$mock_content = "<?php\nreturn array(\n    'ClaudeBot' => '/ClaudeBot/i',\n    'GPTBot' => '/GPTBot/i'\n);";
		$_mock_remote_responses['https://example.com/bots.php'] = array(
			'response' => array( 'code' => 200 ),
			'body'     => $mock_content,
		);

		$sync = TA_External_Bot_DB_Sync::get_instance();
		$result = $sync->fetch_patterns_from_url( 'https://example.com/bots.php' );

		$this->assertNotInstanceOf( 'WP_Error', $result );
		$this->assertIsString( $result );
		$this->assertStringContainsString( 'ClaudeBot', $result );
	}

	/**
	 * Test fetch patterns handles HTTP errors.
	 */
	public function test_fetch_patterns_handles_http_error() {
		global $_mock_remote_responses;

		$_mock_remote_responses['https://example.com/404.php'] = new WP_Error( 'http_request_failed', 'Not found' );

		$sync = TA_External_Bot_DB_Sync::get_instance();
		$result = $sync->fetch_patterns_from_url( 'https://example.com/404.php' );

		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( 'http_request_failed', $result->get_error_code() );
	}

	/**
	 * Test parse PHP array format.
	 */
	public function test_parse_php_array() {
		$php_content = "<?php\nreturn array(\n    'ClaudeBot' => '/ClaudeBot/i',\n    'GPTBot' => '/GPTBot/i',\n    'PerplexityBot' => '/PerplexityBot/i'\n);";

		$sync = TA_External_Bot_DB_Sync::get_instance();
		$patterns = $sync->parse_php_array( $php_content );

		$this->assertIsArray( $patterns );
		$this->assertCount( 3, $patterns );
		$this->assertArrayHasKey( 'ClaudeBot', $patterns );
		$this->assertEquals( '/ClaudeBot/i', $patterns['ClaudeBot'] );
	}

	/**
	 * Test parse PHP array handles invalid content.
	 */
	public function test_parse_php_array_handles_invalid_content() {
		$invalid_content = "This is not PHP";

		$sync = TA_External_Bot_DB_Sync::get_instance();
		$patterns = $sync->parse_php_array( $invalid_content );

		$this->assertInstanceOf( 'WP_Error', $patterns );
		$this->assertEquals( 'parse_failed', $patterns->get_error_code() );
	}

	/**
	 * Test insert new pattern to database.
	 */
	public function test_insert_new_pattern() {
		global $wpdb;

		// Mock database check for existing pattern (returns null = not exists).
		$wpdb->set_mock_result( "SELECT id FROM wp_ta_bot_patterns WHERE pattern = '/TestBot/i' LIMIT 1", null );

		$sync = TA_External_Bot_DB_Sync::get_instance();
		$result = $sync->insert_or_update_pattern( 'TestBot', '/TestBot/i', 'crawler-detect', '1.0.0' );

		$this->assertTrue( $result );
		
		// Verify insert was called with correct data.
		$this->assertGreaterThan( 0, $wpdb->insert_id );
	}

	/**
	 * Test update existing pattern.
	 */
	public function test_update_existing_pattern() {
		global $wpdb;

		// Mock database check for existing pattern (returns ID = exists).
		$existing_pattern = (object) array( 'id' => 5 );
		$wpdb->set_mock_result( "SELECT id FROM wp_ta_bot_patterns WHERE pattern = '/TestBot/i' LIMIT 1", $existing_pattern );

		$sync = TA_External_Bot_DB_Sync::get_instance();
		$result = $sync->insert_or_update_pattern( 'TestBot', '/TestBot/i', 'crawler-detect', '1.0.1' );

		$this->assertTrue( $result );
	}

	/**
	 * Test sync from source.
	 */
	public function test_sync_from_source() {
		global $_mock_remote_responses, $wpdb;

		// Mock successful fetch.
		$mock_content = "<?php\nreturn array(\n    'TestBot1' => '/TestBot1/i',\n    'TestBot2' => '/TestBot2/i'\n);";
		$_mock_remote_responses['https://example.com/bots.php'] = array(
			'response' => array( 'code' => 200 ),
			'body'     => $mock_content,
		);

		// Mock existing patterns check (both don't exist).
		$wpdb->set_mock_result( "SELECT id FROM wp_ta_bot_patterns WHERE pattern = '/TestBot1/i' LIMIT 1", null );
		$wpdb->set_mock_result( "SELECT id FROM wp_ta_bot_patterns WHERE pattern = '/TestBot2/i' LIMIT 1", null );

		$sync = TA_External_Bot_DB_Sync::get_instance();
		$result = $sync->sync_from_source( 'https://example.com/bots.php', 'test-source', '1.0.0' );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'patterns_added', $result );
		$this->assertEquals( 2, $result['patterns_added'] );
	}

	/**
	 * Test log sync status.
	 */
	public function test_log_sync_status() {
		global $wpdb;

		$sync = TA_External_Bot_DB_Sync::get_instance();
		$result = $sync->log_sync_status( 'test-source', '1.0.0', 10, 5, 15, 'success' );

		$this->assertTrue( $result );
		$this->assertGreaterThan( 0, $wpdb->insert_id );
	}

	/**
	 * Test schedule weekly sync.
	 */
	public function test_schedule_weekly_sync() {
		global $_mock_scheduled_events;

		$sync = TA_External_Bot_DB_Sync::get_instance();
		$sync->schedule_weekly_sync();

		$this->assertArrayHasKey( 'ta_external_bot_db_sync', $_mock_scheduled_events );
		$this->assertEquals( 'weekly', $_mock_scheduled_events['ta_external_bot_db_sync']['recurrence'] );
	}

	/**
	 * Test unschedule sync.
	 */
	public function test_unschedule_sync() {
		global $_mock_scheduled_events;

		// First schedule it.
		$sync = TA_External_Bot_DB_Sync::get_instance();
		$sync->schedule_weekly_sync();
		$this->assertArrayHasKey( 'ta_external_bot_db_sync', $_mock_scheduled_events );

		// Then unschedule.
		$sync->unschedule_sync();
		$this->assertArrayNotHasKey( 'ta_external_bot_db_sync', $_mock_scheduled_events );
	}

	/**
	 * Test run sync with Crawler-Detect source.
	 */
	public function test_run_sync_crawler_detect() {
		global $_mock_remote_responses, $wpdb;

		// Mock Crawler-Detect response.
		$mock_content = "<?php\nreturn array(\n    'Googlebot' => '/Googlebot/i',\n    'Bingbot' => '/bingbot/i'\n);";
		$_mock_remote_responses['https://raw.githubusercontent.com/JayBizzle/Crawler-Detect/master/src/Fixtures/Crawlers.php'] = array(
			'response' => array( 'code' => 200 ),
			'body'     => $mock_content,
		);

		$sync = TA_External_Bot_DB_Sync::get_instance();
		$result = $sync->run_sync();

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'crawler-detect', $result );
	}

	/**
	 * Test create database tables.
	 */
	public function test_create_tables() {
		global $wpdb;

		$sync = TA_External_Bot_DB_Sync::get_instance();
		$sync->create_tables();

		$queries = $wpdb->get_queries();
		
		// Should create two tables: bot_patterns and bot_db_sync.
		$this->assertGreaterThanOrEqual( 2, count( $queries ) );
	}
}
