<?php
/**
 * Bot Detection Pipeline Tests
 *
 * Tests for the TA_Bot_Detection_Pipeline class.
 *
 * @package ThirdAudience
 * @since   2.3.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Class BotDetectionPipelineTest
 *
 * @since 2.3.0
 */
class BotDetectionPipelineTest extends TestCase {

    /**
     * Mock known pattern detector.
     *
     * @var object
     */
    private $known_detector;

    /**
     * Mock heuristic detector.
     *
     * @var object
     */
    private $heuristic_detector;

    /**
     * Pipeline instance.
     *
     * @var TA_Bot_Detection_Pipeline
     */
    private $pipeline;

    /**
     * Mock wpdb.
     *
     * @var object
     */
    private $wpdb;

    /**
     * Set up test fixtures.
     */
    protected function setUp(): void {
        parent::setUp();

        // Create mock detectors
        $this->known_detector = $this->createMock( 'TA_Known_Pattern_Detector' );
        $this->heuristic_detector = $this->createMock( 'TA_Heuristic_Detector' );

        // Create a custom mock wpdb object
        $this->wpdb = new class {
            public $prefix = 'wp_';
            private $insert_called = false;
            private $insert_data = array();

            public function insert( $table, $data, $format = array() ) {
                $this->insert_called = true;
                $this->insert_data = array(
                    'table' => $table,
                    'data' => $data,
                    'format' => $format,
                );
                return true;
            }

            public function was_insert_called() {
                return $this->insert_called;
            }

            public function get_insert_data() {
                return $this->insert_data;
            }

            public function reset() {
                $this->insert_called = false;
                $this->insert_data = array();
            }
        };

        $GLOBALS['wpdb'] = $this->wpdb;
        $this->wpdb->reset();

        // Create pipeline with mocked detectors
        $this->pipeline = new TA_Bot_Detection_Pipeline(
            $this->known_detector,
            $this->heuristic_detector
        );
    }

    /**
     * Test that pipeline executes known pattern detector first.
     *
     * @since 2.3.0
     */
    public function test_executes_known_pattern_detector_first() {
        $user_agent = 'GPTBot/1.0';

        // Known detector should return confident result
        $confident_result = new TA_Bot_Detection_Result(
            array(
                'is_bot'       => true,
                'confidence'   => 0.9,
                'bot_name'     => 'GPTBot',
                'bot_vendor'   => 'OpenAI',
                'bot_category' => 'ai',
                'method'       => 'known_pattern',
                'indicators'   => array( 'exact_match' ),
                'needs_review' => false,
            )
        );

        $this->known_detector
            ->expects( $this->once() )
            ->method( 'detect' )
            ->with( $user_agent )
            ->willReturn( $confident_result );

        // Heuristic detector should NOT be called (fast path)
        $this->heuristic_detector
            ->expects( $this->never() )
            ->method( 'detect' );

        $result = $this->pipeline->detect( $user_agent );

        $this->assertTrue( $result->is_bot() );
        $this->assertEquals( 0.9, $result->get_confidence() );
        $this->assertEquals( 'known_pattern', $result->get_method() );
    }

    /**
     * Test that pipeline executes heuristic detector second when needed.
     *
     * @since 2.3.0
     */
    public function test_executes_heuristic_detector_second() {
        $user_agent = 'SomeBot/1.0';

        // Known detector returns low confidence
        $low_confidence_result = new TA_Bot_Detection_Result(
            array(
                'is_bot'       => false,
                'confidence'   => 0.3,
                'bot_name'     => null,
                'bot_vendor'   => null,
                'bot_category' => null,
                'method'       => 'known_pattern',
                'indicators'   => array(),
                'needs_review' => false,
            )
        );

        // Heuristic detector returns confident result
        $heuristic_result = new TA_Bot_Detection_Result(
            array(
                'is_bot'       => true,
                'confidence'   => 0.8,
                'bot_name'     => 'Unknown Bot',
                'bot_vendor'   => null,
                'bot_category' => 'other',
                'method'       => 'heuristic',
                'indicators'   => array( 'bot_keyword', 'crawl_pattern' ),
                'needs_review' => true,
            )
        );

        $this->known_detector
            ->expects( $this->once() )
            ->method( 'detect' )
            ->with( $user_agent )
            ->willReturn( $low_confidence_result );

        $this->heuristic_detector
            ->expects( $this->once() )
            ->method( 'detect' )
            ->with( $user_agent )
            ->willReturn( $heuristic_result );

        $result = $this->pipeline->detect( $user_agent );

        $this->assertTrue( $result->is_bot() );
        $this->assertEquals( 0.8, $result->get_confidence() );
        $this->assertEquals( 'heuristic', $result->get_method() );
    }

    /**
     * Test that pipeline stops at first confident result (fast path).
     *
     * @since 2.3.0
     */
    public function test_stops_at_first_confident_result() {
        $user_agent = 'Claude-Web/1.0';

        // Known detector returns confident result
        $confident_result = new TA_Bot_Detection_Result(
            array(
                'is_bot'       => true,
                'confidence'   => 0.95,
                'bot_name'     => 'Claude',
                'bot_vendor'   => 'Anthropic',
                'bot_category' => 'ai',
                'method'       => 'known_pattern',
                'indicators'   => array( 'exact_match' ),
                'needs_review' => false,
            )
        );

        $this->known_detector
            ->expects( $this->once() )
            ->method( 'detect' )
            ->with( $user_agent )
            ->willReturn( $confident_result );

        // Heuristic detector should NOT be called (early exit)
        $this->heuristic_detector
            ->expects( $this->never() )
            ->method( 'detect' );

        $result = $this->pipeline->detect( $user_agent );

        $this->assertTrue( $result->is_confident() );
        $this->assertEquals( 'known_pattern', $result->get_method() );
    }

    /**
     * Test that pipeline falls through all layers if no confident match.
     *
     * @since 2.3.0
     */
    public function test_falls_through_all_layers_if_no_confident_match() {
        $user_agent = 'WeirdUA/1.0';

        // Known detector returns low confidence
        $known_result = new TA_Bot_Detection_Result(
            array(
                'is_bot'       => false,
                'confidence'   => 0.2,
                'bot_name'     => null,
                'bot_vendor'   => null,
                'bot_category' => null,
                'method'       => 'known_pattern',
                'indicators'   => array(),
                'needs_review' => false,
            )
        );

        // Heuristic detector also returns low confidence
        $heuristic_result = new TA_Bot_Detection_Result(
            array(
                'is_bot'       => false,
                'confidence'   => 0.4,
                'bot_name'     => null,
                'bot_vendor'   => null,
                'bot_category' => null,
                'method'       => 'heuristic',
                'indicators'   => array(),
                'needs_review' => false,
            )
        );

        $this->known_detector
            ->expects( $this->once() )
            ->method( 'detect' )
            ->willReturn( $known_result );

        $this->heuristic_detector
            ->expects( $this->once() )
            ->method( 'detect' )
            ->willReturn( $heuristic_result );

        $result = $this->pipeline->detect( $user_agent );

        // Should return the best result (heuristic with 0.4)
        $this->assertFalse( $result->is_confident() );
        $this->assertEquals( 0.4, $result->get_confidence() );
        $this->assertEquals( 'heuristic', $result->get_method() );
    }

    /**
     * Test that unknown bots are queued for learning.
     *
     * @since 2.3.0
     */
    public function test_queues_unknown_bots_to_database() {
        $user_agent = 'MysteryBot/2.0';

        // Both detectors return low confidence
        $known_result = new TA_Bot_Detection_Result(
            array(
                'is_bot'       => false,
                'confidence'   => 0.3,
                'bot_name'     => null,
                'bot_vendor'   => null,
                'bot_category' => null,
                'method'       => 'known_pattern',
                'indicators'   => array(),
                'needs_review' => false,
            )
        );

        $heuristic_result = new TA_Bot_Detection_Result(
            array(
                'is_bot'       => true,
                'confidence'   => 0.6,
                'bot_name'     => null,
                'bot_vendor'   => null,
                'bot_category' => null,
                'method'       => 'heuristic',
                'indicators'   => array( 'bot_keyword' ),
                'needs_review' => true,
            )
        );

        $this->known_detector
            ->method( 'detect' )
            ->willReturn( $known_result );

        $this->heuristic_detector
            ->method( 'detect' )
            ->willReturn( $heuristic_result );

        $this->pipeline->detect( $user_agent );

        // Verify wpdb insert was called
        $this->assertTrue( $this->wpdb->was_insert_called(), 'Expected wpdb insert to be called' );

        $insert_data = $this->wpdb->get_insert_data();
        $this->assertEquals( 'wp_ta_unknown_bots', $insert_data['table'] );
        $this->assertEquals( $user_agent, $insert_data['data']['user_agent'] );
        $this->assertEquals( 1, $insert_data['data']['is_bot'] );
        $this->assertEquals( 0.6, $insert_data['data']['confidence'] );
        $this->assertEquals( 'heuristic', $insert_data['data']['method'] );
        $this->assertArrayHasKey( 'detected_at', $insert_data['data'] );
    }

    /**
     * Test that confident results are NOT queued to unknown bots table.
     *
     * @since 2.3.0
     */
    public function test_does_not_queue_confident_results() {
        $user_agent = 'Googlebot/2.1';

        // Known detector returns confident result
        $confident_result = new TA_Bot_Detection_Result(
            array(
                'is_bot'       => true,
                'confidence'   => 0.99,
                'bot_name'     => 'Googlebot',
                'bot_vendor'   => 'Google',
                'bot_category' => 'search',
                'method'       => 'known_pattern',
                'indicators'   => array( 'exact_match' ),
                'needs_review' => false,
            )
        );

        $this->known_detector
            ->method( 'detect' )
            ->willReturn( $confident_result );

        $this->pipeline->detect( $user_agent );

        // Verify wpdb insert was NOT called for confident results
        $this->assertFalse( $this->wpdb->was_insert_called(), 'Expected wpdb insert NOT to be called for confident results' );
    }

    /**
     * Test that pipeline handles empty user agent.
     *
     * @since 2.3.0
     */
    public function test_handles_empty_user_agent() {
        $user_agent = '';

        $unknown_result = new TA_Bot_Detection_Result(
            array(
                'is_bot'       => false,
                'confidence'   => 0.0,
                'bot_name'     => null,
                'bot_vendor'   => null,
                'bot_category' => null,
                'method'       => 'unknown',
                'indicators'   => array(),
                'needs_review' => false,
            )
        );

        $this->known_detector
            ->method( 'detect' )
            ->willReturn( $unknown_result );

        $this->heuristic_detector
            ->method( 'detect' )
            ->willReturn( $unknown_result );

        $result = $this->pipeline->detect( $user_agent );

        $this->assertFalse( $result->is_bot() );
        $this->assertEquals( 0.0, $result->get_confidence() );
    }
}
