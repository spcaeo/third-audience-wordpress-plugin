<?php
/**
 * Post Columns - Add AI-Friendliness score column to post list.
 *
 * @package ThirdAudience
 * @since   2.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Post_Columns
 *
 * Displays AI-Friendliness score in WordPress post list table.
 *
 * @since 2.8.0
 */
class TA_Post_Columns {

	/**
	 * Initialize post columns.
	 *
	 * @since 2.8.0
	 * @return void
	 */
	public function init() {
		// Get enabled post types.
		$enabled_post_types = get_option( 'ta_enabled_post_types', array( 'post', 'page' ) );

		foreach ( $enabled_post_types as $post_type ) {
			add_filter( "manage_{$post_type}_posts_columns", array( $this, 'add_score_column' ) );
			add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'render_score_column' ), 10, 2 );
		}

		// Make column sortable.
		add_filter( 'manage_edit-post_sortable_columns', array( $this, 'make_column_sortable' ) );
		add_filter( 'manage_edit-page_sortable_columns', array( $this, 'make_column_sortable' ) );
	}

	/**
	 * Add AI score column to post list.
	 *
	 * @since 2.8.0
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_score_column( $columns ) {
		// Insert after title column.
		$new_columns = array();
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			if ( 'title' === $key ) {
				$new_columns['ta_ai_score'] = __( 'AI Score', 'third-audience' );
			}
		}
		return $new_columns;
	}

	/**
	 * Render AI score column content.
	 *
	 * @since 2.8.0
	 * @param string $column_name Column name.
	 * @param int    $post_id     Post ID.
	 * @return void
	 */
	public function render_score_column( $column_name, $post_id ) {
		if ( 'ta_ai_score' !== $column_name ) {
			return;
		}

		$analyzer = TA_Content_Analyzer::get_instance();
		$score    = $analyzer->get_cached_score( $post_id );

		if ( null === $score ) {
			echo '<span style="color: #999;">—</span>';
			return;
		}

		// Color coding.
		$color = '#999';
		if ( $score >= 80 ) {
			$color = '#34c759'; // Green.
		} elseif ( $score >= 60 ) {
			$color = '#ff9500'; // Orange.
		} else {
			$color = '#ff3b30'; // Red.
		}

		printf(
			'<strong style="color: %s; font-size: 14px;">%d</strong><span style="color: #999;">/100</span>',
			esc_attr( $color ),
			absint( $score )
		);
	}

	/**
	 * Make AI score column sortable.
	 *
	 * @since 2.8.0
	 * @param array $columns Sortable columns.
	 * @return array Modified columns.
	 */
	public function make_column_sortable( $columns ) {
		$columns['ta_ai_score'] = 'ta_ai_score';
		return $columns;
	}
}
