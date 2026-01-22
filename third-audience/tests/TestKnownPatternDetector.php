<?php
/**
 * Tests for Known Pattern Detector
 *
 * @package ThirdAudience
 * @since   2.3.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestKnownPatternDetector
 *
 * @since 2.3.0
 */
class TestKnownPatternDetector extends TestCase {

	/**
	 * Mock WordPress database object.
	 *
	 * @var object
	 */
	private $wpdb;

	/**
	 * Instance of detector.
	 *
	 * @var TA_Known_Pattern_Detector
	 */
	private $detector;

	/**
	 * Set up test environment.
	 */
	protected function setUp(): void {
		parent::setUp();

		// Create a mock wpdb object with the methods we need
		$this->wpdb = $this->getMockBuilder( stdClass::class )
			->addMethods( [ 'prepare', 'get_results' ] )
			->getMock();
		$this->wpdb->prefix = 'wp_';

		// Mock the prepare method
		$this->wpdb->method( 'prepare' )
			->willReturnCallback( function( $query, ...$args ) {
				// Simple mock that returns the query with placeholders replaced
				return vsprintf( str_replace( '%s', "'%s'", $query ), $args );
			} );

		// Load the known pattern detector class if it exists
		$detector_file = __DIR__ . '/../includes/detectors/class-ta-known-pattern-detector.php';
		if ( file_exists( $detector_file ) ) {
			require_once $detector_file;
		}

		$this->detector = new TA_Known_Pattern_Detector( $this->wpdb );
	}

	/**
	 * Test exact pattern matching.
	 *
	 * @since 2.3.0
	 */
	public function test_exact_pattern_match() {
		// Arrange - Mock database to return an exact match
		$this->wpdb->expects( $this->once() )
			->method( 'get_results' )
			->willReturn( [
				(object) [
					'pattern'      => 'Googlebot',
					'pattern_type' => 'exact',
					'bot_name'     => 'Googlebot',
					'bot_vendor'   => 'Google',
					'bot_category' => 'search',
				],
			] );

		// Act
		$result = $this->detector->detect( 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)' );

		// Assert
		$this->assertInstanceOf( TA_Bot_Detection_Result::class, $result );
		$this->assertTrue( $result->is_bot() );
		$this->assertEquals( 1.0, $result->get_confidence() );
		$this->assertEquals( 'Googlebot', $result->get_bot_name() );
		$this->assertEquals( 'Google', $result->get_bot_vendor() );
		$this->assertEquals( 'search', $result->get_bot_category() );
		$this->assertEquals( 'known_pattern', $result->get_method() );
	}

	/**
	 * Test regex pattern matching.
	 *
	 * @since 2.3.0
	 */
	public function test_regex_pattern_match() {
		// Arrange - Mock database to return a regex pattern
		$this->wpdb->expects( $this->once() )
			->method( 'get_results' )
			->willReturn( [
				(object) [
					'pattern'      => '/bingbot\/[\d\.]+/i',
					'pattern_type' => 'regex',
					'bot_name'     => 'Bingbot',
					'bot_vendor'   => 'Microsoft',
					'bot_category' => 'search',
				],
			] );

		// Act
		$result = $this->detector->detect( 'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)' );

		// Assert
		$this->assertInstanceOf( TA_Bot_Detection_Result::class, $result );
		$this->assertTrue( $result->is_bot() );
		$this->assertEquals( 1.0, $result->get_confidence() );
		$this->assertEquals( 'Bingbot', $result->get_bot_name() );
		$this->assertEquals( 'Microsoft', $result->get_bot_vendor() );
		$this->assertEquals( 'search', $result->get_bot_category() );
		$this->assertEquals( 'known_pattern', $result->get_method() );
	}

	/**
	 * Test case-insensitive matching for exact patterns.
	 *
	 * @since 2.3.0
	 */
	public function test_case_insensitive_exact_match() {
		// Arrange - Mock database to return an exact match pattern
		$this->wpdb->expects( $this->once() )
			->method( 'get_results' )
			->willReturn( [
				(object) [
					'pattern'      => 'googlebot',
					'pattern_type' => 'exact',
					'bot_name'     => 'Googlebot',
					'bot_vendor'   => 'Google',
					'bot_category' => 'search',
				],
			] );

		// Act - User agent has uppercase 'Googlebot'
		$result = $this->detector->detect( 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)' );

		// Assert
		$this->assertInstanceOf( TA_Bot_Detection_Result::class, $result );
		$this->assertTrue( $result->is_bot() );
		$this->assertEquals( 1.0, $result->get_confidence() );
	}

	/**
	 * Test that confidence is always 1.0 for database matches.
	 *
	 * @since 2.3.0
	 */
	public function test_database_match_returns_high_confidence() {
		// Arrange
		$this->wpdb->expects( $this->once() )
			->method( 'get_results' )
			->willReturn( [
				(object) [
					'pattern'      => 'ChatGPT-User',
					'pattern_type' => 'exact',
					'bot_name'     => 'ChatGPT',
					'bot_vendor'   => 'OpenAI',
					'bot_category' => 'ai',
				],
			] );

		// Act
		$result = $this->detector->detect( 'ChatGPT-User/1.0' );

		// Assert
		$this->assertEquals( 1.0, $result->get_confidence() );
	}

	/**
	 * Test returns null for no matches.
	 *
	 * @since 2.3.0
	 */
	public function test_no_match_returns_null() {
		// Arrange - Mock database to return no results
		$this->wpdb->expects( $this->once() )
			->method( 'get_results' )
			->willReturn( [] );

		// Act
		$result = $this->detector->detect( 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36' );

		// Assert
		$this->assertNull( $result );
	}

	/**
	 * Test multiple patterns with first match priority.
	 *
	 * @since 2.3.0
	 */
	public function test_multiple_patterns_returns_first_match() {
		// Arrange - Mock database to return multiple patterns
		$this->wpdb->expects( $this->once() )
			->method( 'get_results' )
			->willReturn( [
				(object) [
					'pattern'      => 'bot',
					'pattern_type' => 'exact',
					'bot_name'     => 'Generic Bot',
					'bot_vendor'   => 'Unknown',
					'bot_category' => 'other',
				],
				(object) [
					'pattern'      => 'Googlebot',
					'pattern_type' => 'exact',
					'bot_name'     => 'Googlebot',
					'bot_vendor'   => 'Google',
					'bot_category' => 'search',
				],
			] );

		// Act
		$result = $this->detector->detect( 'Mozilla/5.0 (compatible; Googlebot/2.1)' );

		// Assert - Should match 'bot' first since it appears first in results
		$this->assertEquals( 'Generic Bot', $result->get_bot_name() );
	}

	/**
	 * Test regex pattern that doesn't match.
	 *
	 * @since 2.3.0
	 */
	public function test_regex_pattern_no_match() {
		// Arrange
		$this->wpdb->expects( $this->once() )
			->method( 'get_results' )
			->willReturn( [
				(object) [
					'pattern'      => '/specificbot\/[\d\.]+/i',
					'pattern_type' => 'regex',
					'bot_name'     => 'SpecificBot',
					'bot_vendor'   => 'Vendor',
					'bot_category' => 'other',
				],
			] );

		// Act - User agent doesn't match the regex
		$result = $this->detector->detect( 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)' );

		// Assert
		$this->assertNull( $result );
	}

	/**
	 * Test invalid regex pattern handling.
	 *
	 * @since 2.3.0
	 */
	public function test_invalid_regex_pattern_is_skipped() {
		// Arrange - Mock database with invalid regex
		$this->wpdb->expects( $this->once() )
			->method( 'get_results' )
			->willReturn( [
				(object) [
					'pattern'      => '/[invalid(regex/i',
					'pattern_type' => 'regex',
					'bot_name'     => 'BadBot',
					'bot_vendor'   => 'Vendor',
					'bot_category' => 'other',
				],
			] );

		// Act
		$result = $this->detector->detect( 'Some User Agent' );

		// Assert - Should return null and not throw error
		$this->assertNull( $result );
	}
}
