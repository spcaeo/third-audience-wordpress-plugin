<?php
/**
 * Tests for TA_Heuristic_Detector
 *
 * @package ThirdAudience
 * @since   2.3.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class TestHeuristicDetector
 *
 * Tests for heuristic bot detection based on user agent patterns.
 *
 * @since 2.3.0
 */
class TestHeuristicDetector extends TestCase {

	/**
	 * Detector instance.
	 *
	 * @var TA_Heuristic_Detector
	 */
	private $detector;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->detector = new TA_Heuristic_Detector();
	}

	/**
	 * Test detection of "compatible; BotName/1.0" pattern.
	 */
	public function test_detects_compatible_bot_pattern() {
		$user_agent = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
		
		$result = $this->detector->detect( $user_agent );
		
		$this->assertTrue( $result->is_bot(), 'Should detect compatible bot pattern as bot' );
		$this->assertGreaterThanOrEqual( 0.7, $result->get_confidence(), 'Should have confidence >= 0.7' );
		$this->assertEquals( 'Googlebot', $result->get_bot_name(), 'Should extract bot name' );
		$this->assertEquals( 'heuristic', $result->get_method(), 'Should use heuristic method' );
		$this->assertContains( 'compatible_pattern', $result->get_indicators(), 'Should detect compatible pattern' );
	}

	/**
	 * Test detection of "BotName/1.0 (+http...)" pattern.
	 */
	public function test_detects_bot_with_documentation_url() {
		$user_agent = 'CustomBot/1.0 (+https://example.com/bot-info)';
		
		$result = $this->detector->detect( $user_agent );
		
		$this->assertTrue( $result->is_bot(), 'Should detect bot with documentation URL' );
		$this->assertGreaterThanOrEqual( 0.7, $result->get_confidence(), 'Should have high confidence' );
		$this->assertEquals( 'CustomBot', $result->get_bot_name(), 'Should extract bot name' );
		$this->assertContains( 'documentation_url', $result->get_indicators(), 'Should detect documentation URL' );
	}

	/**
	 * Test extraction of bot name from various patterns.
	 */
	public function test_extracts_bot_name_from_patterns() {
		$test_cases = array(
			array(
				'user_agent'   => 'Mozilla/5.0 (compatible; BingBot/2.0; +http://www.bing.com/bingbot.htm)',
				'expected_bot' => 'BingBot',
			),
			array(
				'user_agent'   => 'YandexBot/3.0 (+http://yandex.com/bots)',
				'expected_bot' => 'YandexBot',
			),
			array(
				'user_agent'   => 'Slackbot-LinkExpanding 1.0 (+https://api.slack.com/robots)',
				'expected_bot' => 'Slackbot-LinkExpanding',
			),
		);

		foreach ( $test_cases as $case ) {
			$result = $this->detector->detect( $case['user_agent'] );
			
			$this->assertEquals(
				$case['expected_bot'],
				$result->get_bot_name(),
				sprintf( 'Should extract "%s" from "%s"', $case['expected_bot'], $case['user_agent'] )
			);
		}
	}

	/**
	 * Test confidence scoring based on indicators found.
	 */
	public function test_scores_confidence_based_on_indicators() {
		// High confidence: multiple indicators.
		$high_confidence_ua = 'MyCustomBot/1.0 (crawler; +https://example.com/bot)';
		$result             = $this->detector->detect( $high_confidence_ua );
		
		$this->assertGreaterThanOrEqual( 0.7, $result->get_confidence(), 'Multiple indicators should yield high confidence' );
		$indicators = $result->get_indicators();
		$this->assertGreaterThanOrEqual( 2, count( $indicators ), 'Should detect multiple indicators' );

		// Medium confidence: single indicator.
		$medium_confidence_ua = 'Mozilla/5.0 CustomSpider/1.0';
		$result               = $this->detector->detect( $medium_confidence_ua );
		
		$this->assertGreaterThan( 0, $result->get_confidence(), 'Single indicator should yield some confidence' );
		$this->assertLessThan( 0.7, $result->get_confidence(), 'Single indicator should not reach high confidence' );
	}

	/**
	 * Test detection returns high confidence for clear bot patterns.
	 */
	public function test_returns_high_confidence_for_clear_patterns() {
		$clear_bots = array(
			'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
			'AhrefsBot/7.0; +http://ahrefs.com/robot/',
			'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
		);

		foreach ( $clear_bots as $user_agent ) {
			$result = $this->detector->detect( $user_agent );
			
			$this->assertTrue(
				$result->is_bot() && $result->get_confidence() >= 0.7,
				sprintf( 'Should detect "%s" as bot with confidence >= 0.7', $user_agent )
			);
		}
	}

	/**
	 * Test keyword detection (bot, crawler, spider, scraper, agent).
	 */
	public function test_detects_bot_keywords() {
		$keywords = array(
			'bot'     => 'CustomBot/1.0',
			'crawler' => 'MyCrawler/2.0',
			'spider'  => 'WebSpider/1.5',
			'scraper' => 'ContentScraper/1.0',
		);

		foreach ( $keywords as $keyword => $user_agent ) {
			$result = $this->detector->detect( $user_agent );
			
			$this->assertTrue( $result->is_bot(), sprintf( 'Should detect "%s" keyword', $keyword ) );
			$this->assertContains( 'keyword_' . $keyword, $result->get_indicators(), sprintf( 'Should include %s indicator', $keyword ) );
		}
	}

	/**
	 * Test version number pattern detection.
	 */
	public function test_detects_version_number_pattern() {
		$user_agents_with_version = array(
			'CustomBot/1.0',
			'MyBot/2.5.3',
			'TestBot/1.0.0-beta',
		);

		foreach ( $user_agents_with_version as $user_agent ) {
			$result = $this->detector->detect( $user_agent );
			
			$this->assertContains( 'version_pattern', $result->get_indicators(), 'Should detect version pattern' );
		}
	}

	/**
	 * Test documentation URL pattern detection.
	 */
	public function test_detects_documentation_url_pattern() {
		$user_agents = array(
			'Bot/1.0 (+http://example.com)',
			'Crawler/2.0 (+https://example.com/info)',
		);

		foreach ( $user_agents as $user_agent ) {
			$result = $this->detector->detect( $user_agent );
			
			$this->assertContains( 'documentation_url', $result->get_indicators(), 'Should detect documentation URL' );
		}
	}

	/**
	 * Test that regular browsers are not detected as bots.
	 */
	public function test_does_not_detect_regular_browsers() {
		$regular_browsers = array(
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
			'Mozilla/5.0 (X11; Linux x86_64; rv:89.0) Gecko/20100101 Firefox/89.0',
		);

		foreach ( $regular_browsers as $user_agent ) {
			$result = $this->detector->detect( $user_agent );
			
			$this->assertFalse(
				$result->is_bot() && $result->get_confidence() >= 0.7,
				sprintf( 'Should not detect regular browser as bot: "%s"', substr( $user_agent, 0, 50 ) )
			);
		}
	}

	/**
	 * Test that empty user agent returns negative result.
	 */
	public function test_handles_empty_user_agent() {
		$result = $this->detector->detect( '' );
		
		$this->assertFalse( $result->is_bot(), 'Empty user agent should not be detected as bot' );
		$this->assertEquals( 0.0, $result->get_confidence(), 'Empty user agent should have zero confidence' );
	}

	/**
	 * Test case insensitive detection.
	 */
	public function test_case_insensitive_detection() {
		$user_agents = array(
			'CUSTOMBOT/1.0',
			'CustomBot/1.0',
			'custombot/1.0',
		);

		foreach ( $user_agents as $user_agent ) {
			$result = $this->detector->detect( $user_agent );
			
			$this->assertTrue( $result->is_bot(), sprintf( 'Should detect bot regardless of case: "%s"', $user_agent ) );
		}
	}

	/**
	 * Test detection result structure.
	 */
	public function test_returns_valid_detection_result() {
		$user_agent = 'TestBot/1.0 (+https://example.com)';
		$result     = $this->detector->detect( $user_agent );
		
		$this->assertInstanceOf( TA_Bot_Detection_Result::class, $result, 'Should return TA_Bot_Detection_Result instance' );
		$this->assertIsString( $result->get_method(), 'Method should be string' );
		$this->assertIsArray( $result->get_indicators(), 'Indicators should be array' );
		$this->assertIsFloat( $result->get_confidence(), 'Confidence should be float' );
		$this->assertGreaterThanOrEqual( 0.0, $result->get_confidence(), 'Confidence should be >= 0.0' );
		$this->assertLessThanOrEqual( 1.0, $result->get_confidence(), 'Confidence should be <= 1.0' );
	}
}
