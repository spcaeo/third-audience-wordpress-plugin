<?php
/**
 * Content Analyzer - Analyzes post content characteristics.
 *
 * Tracks content metrics to correlate with AI bot citation performance:
 * - Word count
 * - Heading count (H1-H6)
 * - Image count
 * - Schema.org markup presence
 * - Content freshness (days since last update)
 *
 * @package ThirdAudience
 * @since   2.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Content_Analyzer
 *
 * Analyzes content characteristics for correlation with citation performance.
 *
 * @since 2.7.0
 */
class TA_Content_Analyzer {

	/**
	 * Singleton instance.
	 *
	 * @var TA_Content_Analyzer|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @since 2.7.0
	 * @return TA_Content_Analyzer
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
	 * @since 2.7.0
	 */
	private function __construct() {
		// Private constructor for singleton.
	}

	/**
	 * Analyze post content and return metrics.
	 *
	 * @since 2.7.0
	 * @param int $post_id Post ID.
	 * @return array|null Content metrics or null if post not found.
	 */
	public function analyze_post( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return null;
		}

		return array(
			'word_count'     => $this->get_word_count( $post->post_content ),
			'heading_count'  => $this->count_headings( $post->post_content ),
			'image_count'    => $this->count_images( $post->post_content ),
			'has_schema'     => $this->has_schema_markup( $post_id ),
			'freshness_days' => $this->get_freshness_days( $post ),
		);
	}

	/**
	 * Get word count from content.
	 *
	 * Strips all HTML tags and counts words in the clean text.
	 *
	 * @since 2.7.0
	 * @param string $content Post content.
	 * @return int Word count.
	 */
	private function get_word_count( $content ) {
		$text = wp_strip_all_tags( $content );
		return str_word_count( $text );
	}

	/**
	 * Count headings (H1-H6) in content.
	 *
	 * @since 2.7.0
	 * @param string $content Post content.
	 * @return int Heading count.
	 */
	private function count_headings( $content ) {
		preg_match_all( '/<h[1-6][^>]*>/i', $content, $matches );
		return count( $matches[0] );
	}

	/**
	 * Count images in content.
	 *
	 * @since 2.7.0
	 * @param string $content Post content.
	 * @return int Image count.
	 */
	private function count_images( $content ) {
		preg_match_all( '/<img[^>]*>/i', $content, $matches );
		return count( $matches[0] );
	}

	/**
	 * Check if post has schema.org markup.
	 *
	 * Checks for:
	 * - Schema.org URL in content
	 * - Yoast SEO schema output
	 * - Rank Math schema output
	 * - Schema Pro plugin
	 *
	 * @since 2.7.0
	 * @param int $post_id Post ID.
	 * @return int 1 if has schema, 0 if not.
	 */
	private function has_schema_markup( $post_id ) {
		// Check content for schema.org markup.
		$content = get_post_field( 'post_content', $post_id );
		if ( strpos( $content, 'schema.org' ) !== false ) {
			return 1;
		}

		// Check if Yoast SEO is active and generating schema.
		if ( function_exists( 'YoastSEO' ) ) {
			return 1;
		}

		// Check if Rank Math is active and generating schema.
		if ( defined( 'RANK_MATH_VERSION' ) ) {
			return 1;
		}

		// Check if Schema Pro is active.
		if ( defined( 'BSF_AIOSRS_SCHEMA_PRO_VER' ) ) {
			return 1;
		}

		// Check post meta for common schema keys.
		$schema_meta_keys = array(
			'_yoast_wpseo_schema_article_type',
			'rank_math_schema_Article',
			'schema_type',
		);

		foreach ( $schema_meta_keys as $meta_key ) {
			if ( get_post_meta( $post_id, $meta_key, true ) ) {
				return 1;
			}
		}

		return 0;
	}

	/**
	 * Get content freshness in days.
	 *
	 * Calculates days since post was last modified.
	 *
	 * @since 2.7.0
	 * @param WP_Post $post Post object.
	 * @return int Days since last update.
	 */
	private function get_freshness_days( $post ) {
		$modified_time = strtotime( $post->post_modified );
		$current_time  = current_time( 'timestamp' );
		return floor( ( $current_time - $modified_time ) / DAY_IN_SECONDS );
	}
}
