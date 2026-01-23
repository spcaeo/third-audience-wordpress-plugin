<?php
/**
 * Admin AJAX Benchmark Handlers - Competitor Benchmarking AJAX operations.
 *
 * Handles competitor management and test result recording AJAX requests.
 *
 * @package ThirdAudience
 * @since   3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Admin_AJAX_Benchmark
 *
 * Handles competitor benchmarking AJAX operations for the admin interface.
 *
 * @since 3.3.1
 */
class TA_Admin_AJAX_Benchmark {

	/**
	 * Security instance.
	 *
	 * @var TA_Security
	 */
	private $security;

	/**
	 * Singleton instance.
	 *
	 * @var TA_Admin_AJAX_Benchmark|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 3.3.1
	 * @return TA_Admin_AJAX_Benchmark
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
	 * @since 3.3.1
	 */
	private function __construct() {
		$this->security = TA_Security::get_instance();
	}

	/**
	 * Register AJAX hooks.
	 *
	 * @since 3.3.1
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_ta_add_competitor', array( $this, 'ajax_add_competitor' ) );
		add_action( 'wp_ajax_ta_delete_competitor', array( $this, 'ajax_delete_competitor' ) );
		add_action( 'wp_ajax_ta_generate_prompts', array( $this, 'ajax_generate_prompts' ) );
		add_action( 'wp_ajax_ta_record_test', array( $this, 'ajax_record_test' ) );
		add_action( 'wp_ajax_ta_delete_test', array( $this, 'ajax_delete_test' ) );
	}

	/**
	 * AJAX handler: Add competitor.
	 *
	 * @since 3.1.0
	 * @return void
	 */
	public function ajax_add_competitor() {
		$this->security->verify_ajax_request( 'competitor_benchmarking' );

		$url  = isset( $_POST['competitor_url'] ) ? esc_url_raw( wp_unslash( $_POST['competitor_url'] ) ) : '';
		$name = isset( $_POST['competitor_name'] ) ? sanitize_text_field( wp_unslash( $_POST['competitor_name'] ) ) : '';

		if ( empty( $url ) || empty( $name ) ) {
			wp_send_json_error( array(
				'message' => __( 'Competitor URL and name are required.', 'third-audience' ),
			) );
		}

		$benchmarking = TA_Competitor_Benchmarking::get_instance();
		$result       = $benchmarking->add_competitor( $url, $name );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array(
				'message' => $result->get_error_message(),
			) );
		}

		wp_send_json_success( array(
			'message' => __( 'Competitor added successfully.', 'third-audience' ),
		) );
	}

	/**
	 * AJAX handler: Delete competitor.
	 *
	 * @since 3.1.0
	 * @return void
	 */
	public function ajax_delete_competitor() {
		$this->security->verify_ajax_request( 'competitor_benchmarking' );

		$url = isset( $_POST['competitor_url'] ) ? esc_url_raw( wp_unslash( $_POST['competitor_url'] ) ) : '';

		if ( empty( $url ) ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid competitor URL.', 'third-audience' ),
			) );
		}

		$benchmarking = TA_Competitor_Benchmarking::get_instance();
		$result       = $benchmarking->delete_competitor( $url );

		if ( $result ) {
			wp_send_json_success( array(
				'message' => __( 'Competitor deleted successfully.', 'third-audience' ),
			) );
		} else {
			wp_send_json_error( array(
				'message' => __( 'Failed to delete competitor.', 'third-audience' ),
			) );
		}
	}

	/**
	 * AJAX handler: Generate prompts for competitor.
	 *
	 * @since 3.1.0
	 * @return void
	 */
	public function ajax_generate_prompts() {
		$this->security->verify_ajax_request( 'competitor_benchmarking' );

		$url  = isset( $_POST['competitor_url'] ) ? esc_url_raw( wp_unslash( $_POST['competitor_url'] ) ) : '';
		$name = isset( $_POST['competitor_name'] ) ? sanitize_text_field( wp_unslash( $_POST['competitor_name'] ) ) : '';

		if ( empty( $url ) || empty( $name ) ) {
			wp_send_json_error( array(
				'message' => __( 'Competitor URL and name are required.', 'third-audience' ),
			) );
		}

		$benchmarking = TA_Competitor_Benchmarking::get_instance();
		$prompts      = $benchmarking->generate_prompts( $url, $name );

		wp_send_json_success( array(
			'prompts' => $prompts,
			'message' => __( 'Prompts generated successfully.', 'third-audience' ),
		) );
	}

	/**
	 * AJAX handler: Record test result.
	 *
	 * @since 3.1.0
	 * @return void
	 */
	public function ajax_record_test() {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ta_record_test' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Security verification failed.', 'third-audience' ),
			) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Permission denied.', 'third-audience' ),
			) );
		}

		$data = array(
			'competitor_url'  => isset( $_POST['competitor_url'] ) ? esc_url_raw( wp_unslash( $_POST['competitor_url'] ) ) : '',
			'competitor_name' => isset( $_POST['competitor_name'] ) ? sanitize_text_field( wp_unslash( $_POST['competitor_name'] ) ) : '',
			'test_prompt'     => isset( $_POST['test_prompt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['test_prompt'] ) ) : '',
			'ai_platform'     => isset( $_POST['ai_platform'] ) ? sanitize_text_field( wp_unslash( $_POST['ai_platform'] ) ) : '',
			'cited_rank'      => isset( $_POST['cited_rank'] ) && '' !== $_POST['cited_rank'] ? absint( $_POST['cited_rank'] ) : null,
			'test_date'       => isset( $_POST['test_date'] ) ? sanitize_text_field( wp_unslash( $_POST['test_date'] ) ) : current_time( 'mysql' ),
			'test_notes'      => isset( $_POST['test_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['test_notes'] ) ) : '',
		);

		// Validate required fields.
		if ( empty( $data['competitor_url'] ) || empty( $data['competitor_name'] ) || empty( $data['test_prompt'] ) || empty( $data['ai_platform'] ) ) {
			wp_send_json_error( array(
				'message' => __( 'All required fields must be filled.', 'third-audience' ),
			) );
		}

		$benchmarking = TA_Competitor_Benchmarking::get_instance();
		$result       = $benchmarking->record_test( $data );

		if ( $result ) {
			wp_send_json_success( array(
				'message' => __( 'Test result recorded successfully.', 'third-audience' ),
				'test_id' => $result,
			) );
		} else {
			wp_send_json_error( array(
				'message' => __( 'Failed to record test result.', 'third-audience' ),
			) );
		}
	}

	/**
	 * AJAX handler: Delete test result.
	 *
	 * @since 3.1.0
	 * @return void
	 */
	public function ajax_delete_test() {
		$this->security->verify_ajax_request( 'competitor_benchmarking' );

		$test_id = isset( $_POST['test_id'] ) ? absint( $_POST['test_id'] ) : 0;

		if ( empty( $test_id ) ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid test ID.', 'third-audience' ),
			) );
		}

		$benchmarking = TA_Competitor_Benchmarking::get_instance();
		$result       = $benchmarking->delete_test( $test_id );

		if ( $result ) {
			wp_send_json_success( array(
				'message' => __( 'Test result deleted successfully.', 'third-audience' ),
			) );
		} else {
			wp_send_json_error( array(
				'message' => __( 'Failed to delete test result.', 'third-audience' ),
			) );
		}
	}
}
