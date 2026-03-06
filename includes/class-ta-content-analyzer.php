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

	/**
	 * Calculate AI-Friendliness score for a post.
	 *
	 * Scores content on a 0-100 scale based on:
	 * - Structure Score (40 points)
	 * - Metadata Score (25 points)
	 * - Readability Score (20 points)
	 * - Schema/Markup Score (15 points)
	 *
	 * @since 2.8.0
	 * @param int $post_id Post ID.
	 * @return array|null Score and details, or null if post not found.
	 */
	public function calculate_ai_friendliness_score( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return null;
		}

		$content = $post->post_content;

		// Calculate individual scores.
		$structure_score  = $this->calculate_structure_score( $content );
		$metadata_score   = $this->calculate_metadata_score( $post );
		$readability_score = $this->calculate_readability_score( $content );
		$schema_score     = $this->calculate_schema_score( $post_id );

		// Total score (max 100).
		$total_score = $structure_score['total'] + $metadata_score['total'] +
		               $readability_score['total'] + $schema_score['total'];

		return array(
			'score'            => min( 100, round( $total_score ) ),
			'structure'        => $structure_score,
			'metadata'         => $metadata_score,
			'readability'      => $readability_score,
			'schema'           => $schema_score,
			'grade'            => $this->get_score_grade( $total_score ),
			'calculated_at'    => current_time( 'mysql' ),
		);
	}

	/**
	 * Calculate structure score (max 40 points).
	 *
	 * @since 2.8.0
	 * @param string $content Post content.
	 * @return array Score details.
	 */
	private function calculate_structure_score( $content ) {
		$score = 0;
		$details = array();

		// Heading hierarchy (15 points).
		$heading_score = $this->score_heading_hierarchy( $content );
		$score += $heading_score['score'];
		$details['heading_hierarchy'] = $heading_score;

		// First paragraph quality (10 points).
		$first_para_score = $this->score_first_paragraph( $content );
		$score += $first_para_score['score'];
		$details['first_paragraph'] = $first_para_score;

		// Paragraph length consistency (10 points).
		$para_length_score = $this->score_paragraph_lengths( $content );
		$score += $para_length_score['score'];
		$details['paragraph_length'] = $para_length_score;

		// Lists presence (5 points).
		$lists_score = $this->score_lists_presence( $content );
		$score += $lists_score['score'];
		$details['lists'] = $lists_score;

		return array(
			'total'   => $score,
			'max'     => 40,
			'details' => $details,
		);
	}

	/**
	 * Calculate metadata score (max 25 points).
	 *
	 * @since 2.8.0
	 * @param WP_Post $post Post object.
	 * @return array Score details.
	 */
	private function calculate_metadata_score( $post ) {
		$score = 0;
		$details = array();

		// SEO title (10 points).
		$title_score = $this->score_seo_title( $post );
		$score += $title_score['score'];
		$details['seo_title'] = $title_score;

		// Meta description (10 points).
		$description_score = $this->score_meta_description( $post->ID );
		$score += $description_score['score'];
		$details['meta_description'] = $description_score;

		// Focus keyword in first paragraph (5 points).
		$keyword_score = $this->score_keyword_placement( $post );
		$score += $keyword_score['score'];
		$details['keyword_placement'] = $keyword_score;

		return array(
			'total'   => $score,
			'max'     => 25,
			'details' => $details,
		);
	}

	/**
	 * Calculate readability score (max 20 points).
	 *
	 * @since 2.8.0
	 * @param string $content Post content.
	 * @return array Score details.
	 */
	private function calculate_readability_score( $content ) {
		$score = 0;
		$details = array();

		$text = wp_strip_all_tags( $content );

		// Flesch reading ease (10 points).
		$flesch_score = $this->score_flesch_reading_ease( $text );
		$score += $flesch_score['score'];
		$details['flesch_reading_ease'] = $flesch_score;

		// Average sentence length (5 points).
		$sentence_score = $this->score_sentence_length( $text );
		$score += $sentence_score['score'];
		$details['sentence_length'] = $sentence_score;

		// Passive voice (5 points).
		$passive_score = $this->score_passive_voice( $text );
		$score += $passive_score['score'];
		$details['passive_voice'] = $passive_score;

		return array(
			'total'   => $score,
			'max'     => 20,
			'details' => $details,
		);
	}

	/**
	 * Calculate schema/markup score (max 15 points).
	 *
	 * @since 2.8.0
	 * @param int $post_id Post ID.
	 * @return array Score details.
	 */
	private function calculate_schema_score( $post_id ) {
		$score = 0;
		$details = array();

		// Schema.org markup (10 points).
		$has_schema = $this->has_schema_markup( $post_id );
		$schema_score = $has_schema ? 10 : 0;
		$score += $schema_score;
		$details['schema_markup'] = array(
			'score'   => $schema_score,
			'present' => (bool) $has_schema,
		);

		// Table of contents for long posts (5 points).
		$post = get_post( $post_id );
		$word_count = $this->get_word_count( $post->post_content );
		$toc_score = 0;
		if ( $word_count > 1500 ) {
			// Check for TOC plugins or TOC-like structures.
			$has_toc = $this->has_table_of_contents( $post->post_content );
			$toc_score = $has_toc ? 5 : 0;
		} else {
			// Not needed for short posts.
			$toc_score = 5;
		}
		$score += $toc_score;
		$details['table_of_contents'] = array(
			'score'      => $toc_score,
			'word_count' => $word_count,
			'required'   => $word_count > 1500,
		);

		return array(
			'total'   => $score,
			'max'     => 15,
			'details' => $details,
		);
	}

	/**
	 * Score heading hierarchy.
	 *
	 * @since 2.8.0
	 * @param string $content Post content.
	 * @return array Score and details.
	 */
	private function score_heading_hierarchy( $content ) {
		preg_match_all( '/<h([1-6])[^>]*>/i', $content, $matches );
		$headings = isset( $matches[1] ) ? array_map( 'intval', $matches[1] ) : array();

		if ( empty( $headings ) ) {
			return array( 'score' => 0, 'reason' => 'No headings found' );
		}

		$score = 15;
		$issues = array();

		// Check for proper nesting (H2 -> H3, not H2 -> H4).
		$prev_level = 0;
		foreach ( $headings as $level ) {
			if ( $prev_level > 0 && $level > $prev_level + 1 ) {
				$score -= 5;
				$issues[] = 'Skipped heading level (H' . $prev_level . ' to H' . $level . ')';
				break;
			}
			$prev_level = $level;
		}

		// Prefer H2 as top-level (H1 should be post title).
		if ( in_array( 1, $headings, true ) ) {
			$score -= 3;
			$issues[] = 'H1 found in content (should be post title only)';
		}

		return array(
			'score'  => max( 0, $score ),
			'reason' => empty( $issues ) ? 'Proper heading hierarchy' : implode( ', ', $issues ),
		);
	}

	/**
	 * Score first paragraph quality.
	 *
	 * @since 2.8.0
	 * @param string $content Post content.
	 * @return array Score and details.
	 */
	private function score_first_paragraph( $content ) {
		// Extract first paragraph.
		$text = wp_strip_all_tags( $content );
		$paragraphs = preg_split( '/\n\s*\n/', trim( $text ) );

		if ( empty( $paragraphs ) ) {
			return array( 'score' => 0, 'reason' => 'No paragraphs found' );
		}

		$first_para = $paragraphs[0];
		$word_count = str_word_count( $first_para );

		$score = 10;
		$reason = 'Good first paragraph';

		// Ideal: 50-150 words.
		if ( $word_count < 30 ) {
			$score = 3;
			$reason = 'First paragraph too short (' . $word_count . ' words)';
		} elseif ( $word_count > 200 ) {
			$score = 5;
			$reason = 'First paragraph too long (' . $word_count . ' words)';
		} elseif ( $word_count >= 50 && $word_count <= 150 ) {
			$score = 10;
		} else {
			$score = 8;
		}

		return array(
			'score'      => $score,
			'reason'     => $reason,
			'word_count' => $word_count,
		);
	}

	/**
	 * Score paragraph length consistency.
	 *
	 * @since 2.8.0
	 * @param string $content Post content.
	 * @return array Score and details.
	 */
	private function score_paragraph_lengths( $content ) {
		$text = wp_strip_all_tags( $content );
		$paragraphs = preg_split( '/\n\s*\n/', trim( $text ) );
		$paragraphs = array_filter( $paragraphs );

		if ( count( $paragraphs ) < 2 ) {
			return array( 'score' => 5, 'reason' => 'Too few paragraphs to assess' );
		}

		$lengths = array();
		foreach ( $paragraphs as $para ) {
			$lengths[] = str_word_count( $para );
		}

		$avg = array_sum( $lengths ) / count( $lengths );

		$score = 10;
		if ( $avg >= 50 && $avg <= 100 ) {
			$score = 10;
			$reason = 'Ideal paragraph length (avg ' . round( $avg ) . ' words)';
		} elseif ( $avg >= 30 && $avg <= 150 ) {
			$score = 7;
			$reason = 'Acceptable paragraph length (avg ' . round( $avg ) . ' words)';
		} else {
			$score = 3;
			$reason = 'Paragraph length needs work (avg ' . round( $avg ) . ' words)';
		}

		return array(
			'score'  => $score,
			'reason' => $reason,
			'avg'    => round( $avg ),
		);
	}

	/**
	 * Score lists presence.
	 *
	 * @since 2.8.0
	 * @param string $content Post content.
	 * @return array Score and details.
	 */
	private function score_lists_presence( $content ) {
		$has_ul = stripos( $content, '<ul' ) !== false;
		$has_ol = stripos( $content, '<ol' ) !== false;

		if ( $has_ul || $has_ol ) {
			return array( 'score' => 5, 'reason' => 'Lists present' );
		}

		return array( 'score' => 0, 'reason' => 'No lists found' );
	}

	/**
	 * Score SEO title.
	 *
	 * @since 2.8.0
	 * @param WP_Post $post Post object.
	 * @return array Score and details.
	 */
	private function score_seo_title( $post ) {
		// Check Yoast SEO.
		$yoast_title = get_post_meta( $post->ID, '_yoast_wpseo_title', true );

		// Check Rank Math.
		$rankmath_title = get_post_meta( $post->ID, 'rank_math_title', true );

		$seo_title = $yoast_title ?: $rankmath_title;

		// Fallback to post title.
		if ( empty( $seo_title ) ) {
			$seo_title = $post->post_title;
		}

		$length = mb_strlen( $seo_title );

		$score = 10;
		if ( $length >= 50 && $length <= 60 ) {
			$score = 10;
			$reason = 'Ideal SEO title length (' . $length . ' chars)';
		} elseif ( $length >= 40 && $length <= 70 ) {
			$score = 7;
			$reason = 'Acceptable SEO title length (' . $length . ' chars)';
		} else {
			$score = 3;
			$reason = 'SEO title needs optimization (' . $length . ' chars)';
		}

		return array(
			'score'  => $score,
			'reason' => $reason,
			'length' => $length,
		);
	}

	/**
	 * Score meta description.
	 *
	 * @since 2.8.0
	 * @param int $post_id Post ID.
	 * @return array Score and details.
	 */
	private function score_meta_description( $post_id ) {
		// Check Yoast SEO.
		$yoast_desc = get_post_meta( $post_id, '_yoast_wpseo_metadesc', true );

		// Check Rank Math.
		$rankmath_desc = get_post_meta( $post_id, 'rank_math_description', true );

		$description = $yoast_desc ?: $rankmath_desc;

		if ( empty( $description ) ) {
			return array( 'score' => 0, 'reason' => 'No meta description found' );
		}

		$length = mb_strlen( $description );

		$score = 10;
		if ( $length >= 150 && $length <= 160 ) {
			$score = 10;
			$reason = 'Ideal meta description length (' . $length . ' chars)';
		} elseif ( $length >= 120 && $length <= 180 ) {
			$score = 7;
			$reason = 'Acceptable meta description length (' . $length . ' chars)';
		} else {
			$score = 3;
			$reason = 'Meta description needs optimization (' . $length . ' chars)';
		}

		return array(
			'score'  => $score,
			'reason' => $reason,
			'length' => $length,
		);
	}

	/**
	 * Score keyword placement in first paragraph.
	 *
	 * @since 2.8.0
	 * @param WP_Post $post Post object.
	 * @return array Score and details.
	 */
	private function score_keyword_placement( $post ) {
		// Get focus keyword from Yoast or Rank Math.
		$yoast_keyword = get_post_meta( $post->ID, '_yoast_wpseo_focuskw', true );
		$rankmath_keyword = get_post_meta( $post->ID, 'rank_math_focus_keyword', true );

		$keyword = $yoast_keyword ?: $rankmath_keyword;

		if ( empty( $keyword ) ) {
			return array( 'score' => 5, 'reason' => 'No focus keyword set (not critical)' );
		}

		// Check if keyword appears in first paragraph.
		$text = wp_strip_all_tags( $post->post_content );
		$paragraphs = preg_split( '/\n\s*\n/', trim( $text ) );

		if ( empty( $paragraphs ) ) {
			return array( 'score' => 0, 'reason' => 'No content found' );
		}

		$first_para = strtolower( $paragraphs[0] );
		$keyword_lower = strtolower( $keyword );

		if ( stripos( $first_para, $keyword_lower ) !== false ) {
			return array( 'score' => 5, 'reason' => 'Focus keyword in first paragraph' );
		}

		return array( 'score' => 0, 'reason' => 'Focus keyword not in first paragraph' );
	}

	/**
	 * Calculate Flesch Reading Ease score.
	 *
	 * @since 2.8.0
	 * @param string $text Plain text content.
	 * @return array Score and details.
	 */
	private function score_flesch_reading_ease( $text ) {
		$sentences = preg_split( '/[.!?]+/', $text );
		$sentences = array_filter( $sentences );
		$sentence_count = count( $sentences );

		$word_count = str_word_count( $text );
		$syllable_count = $this->count_syllables( $text );

		if ( $sentence_count === 0 || $word_count === 0 ) {
			return array( 'score' => 0, 'reason' => 'Insufficient content' );
		}

		// Flesch Reading Ease formula.
		$flesch = 206.835 - 1.015 * ( $word_count / $sentence_count ) - 84.6 * ( $syllable_count / $word_count );
		$flesch = max( 0, min( 100, $flesch ) );

		$score = 0;
		if ( $flesch >= 60 ) {
			$score = 10;
			$reason = 'Excellent readability (Flesch: ' . round( $flesch ) . ')';
		} elseif ( $flesch >= 50 ) {
			$score = 7;
			$reason = 'Good readability (Flesch: ' . round( $flesch ) . ')';
		} else {
			$score = 3;
			$reason = 'Difficult to read (Flesch: ' . round( $flesch ) . ')';
		}

		return array(
			'score'  => $score,
			'reason' => $reason,
			'flesch' => round( $flesch ),
		);
	}

	/**
	 * Score sentence length.
	 *
	 * @since 2.8.0
	 * @param string $text Plain text content.
	 * @return array Score and details.
	 */
	private function score_sentence_length( $text ) {
		$sentences = preg_split( '/[.!?]+/', $text );
		$sentences = array_filter( $sentences );

		if ( empty( $sentences ) ) {
			return array( 'score' => 0, 'reason' => 'No sentences found' );
		}

		$total_words = 0;
		foreach ( $sentences as $sentence ) {
			$total_words += str_word_count( $sentence );
		}

		$avg_length = $total_words / count( $sentences );

		$score = 5;
		if ( $avg_length < 20 ) {
			$score = 5;
			$reason = 'Good sentence length (avg ' . round( $avg_length ) . ' words)';
		} elseif ( $avg_length < 25 ) {
			$score = 3;
			$reason = 'Acceptable sentence length (avg ' . round( $avg_length ) . ' words)';
		} else {
			$score = 0;
			$reason = 'Sentences too long (avg ' . round( $avg_length ) . ' words)';
		}

		return array(
			'score'  => $score,
			'reason' => $reason,
			'avg'    => round( $avg_length ),
		);
	}

	/**
	 * Score passive voice usage.
	 *
	 * @since 2.8.0
	 * @param string $text Plain text content.
	 * @return array Score and details.
	 */
	private function score_passive_voice( $text ) {
		// Simple passive voice detection (be verbs + past participle).
		$passive_indicators = array(
			'is being', 'was being', 'has been', 'have been', 'had been',
			'will be', 'is', 'was', 'were', 'been', 'be',
		);

		$sentences = preg_split( '/[.!?]+/', $text );
		$sentences = array_filter( $sentences );
		$passive_count = 0;

		foreach ( $sentences as $sentence ) {
			$sentence_lower = strtolower( $sentence );
			foreach ( $passive_indicators as $indicator ) {
				if ( stripos( $sentence_lower, $indicator ) !== false ) {
					$passive_count++;
					break;
				}
			}
		}

		$total_sentences = count( $sentences );
		if ( $total_sentences === 0 ) {
			return array( 'score' => 5, 'reason' => 'No sentences to analyze' );
		}

		$passive_percentage = ( $passive_count / $total_sentences ) * 100;

		$score = 5;
		if ( $passive_percentage < 10 ) {
			$score = 5;
			$reason = 'Low passive voice usage (' . round( $passive_percentage ) . '%)';
		} elseif ( $passive_percentage < 20 ) {
			$score = 3;
			$reason = 'Moderate passive voice usage (' . round( $passive_percentage ) . '%)';
		} else {
			$score = 0;
			$reason = 'High passive voice usage (' . round( $passive_percentage ) . '%)';
		}

		return array(
			'score'      => $score,
			'reason'     => $reason,
			'percentage' => round( $passive_percentage ),
		);
	}

	/**
	 * Count syllables in text (approximate).
	 *
	 * @since 2.8.0
	 * @param string $text Text to analyze.
	 * @return int Syllable count.
	 */
	private function count_syllables( $text ) {
		$words = str_word_count( strtolower( $text ), 1 );
		$syllables = 0;

		foreach ( $words as $word ) {
			// Approximate syllable count based on vowel groups.
			$word = preg_replace( '/[^a-z]/', '', $word );
			$syllables += preg_match_all( '/[aeiouy]+/', $word, $matches );
		}

		return max( 1, $syllables );
	}

	/**
	 * Check if content has table of contents.
	 *
	 * @since 2.8.0
	 * @param string $content Post content.
	 * @return bool True if TOC found.
	 */
	private function has_table_of_contents( $content ) {
		// Check for common TOC patterns.
		$toc_patterns = array(
			'table of contents',
			'toc',
			'ez-toc',
			'lwptoc',
			'id="toc"',
			'class="toc"',
		);

		$content_lower = strtolower( $content );
		foreach ( $toc_patterns as $pattern ) {
			if ( stripos( $content_lower, $pattern ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get score grade (A-F).
	 *
	 * @since 2.8.0
	 * @param int $score Score 0-100.
	 * @return string Grade.
	 */
	private function get_score_grade( $score ) {
		if ( $score >= 90 ) {
			return 'A';
		} elseif ( $score >= 80 ) {
			return 'B';
		} elseif ( $score >= 70 ) {
			return 'C';
		} elseif ( $score >= 60 ) {
			return 'D';
		} else {
			return 'F';
		}
	}

	/**
	 * Get cached AI-Friendliness score.
	 *
	 * @since 2.8.0
	 * @param int $post_id Post ID.
	 * @return int|null Score or null if not cached.
	 */
	public function get_cached_score( $post_id ) {
		$cached = get_post_meta( $post_id, '_ta_ai_score', true );
		return $cached ? absint( $cached ) : null;
	}

	/**
	 * Get content recommendations based on score details.
	 *
	 * @since 2.8.0
	 * @param int   $post_id Post ID.
	 * @param array $score_details Score calculation results.
	 * @return array Recommendations array.
	 */
	public function get_content_recommendations( $post_id, $score_details ) {
		$recommendations = array();

		// Structure recommendations.
		$structure = $score_details['structure']['details'];

		if ( isset( $structure['heading_hierarchy'] ) && $structure['heading_hierarchy']['score'] < 10 ) {
			$recommendations[] = array(
				'type'     => 'structure',
				'severity' => 'high',
				'message'  => 'Add proper heading structure (H2 → H3 hierarchy)',
				'action'   => 'Break content into clear sections with H2 headings, use H3 for subsections',
			);
		}

		if ( isset( $structure['first_paragraph'] ) && $structure['first_paragraph']['score'] < 8 ) {
			$recommendations[] = array(
				'type'     => 'structure',
				'severity' => 'high',
				'message'  => 'First paragraph needs improvement',
				'action'   => 'Use inverted pyramid: answer the question first (50-150 words), details later',
			);
		}

		if ( isset( $structure['paragraph_length'] ) && $structure['paragraph_length']['score'] < 7 ) {
			$recommendations[] = array(
				'type'     => 'structure',
				'severity' => 'medium',
				'message'  => 'Paragraph length inconsistent',
				'action'   => 'Aim for 50-100 words per paragraph for better readability',
			);
		}

		if ( isset( $structure['lists'] ) && $structure['lists']['score'] === 0 ) {
			$recommendations[] = array(
				'type'     => 'structure',
				'severity' => 'low',
				'message'  => 'No lists found',
				'action'   => 'Add bullet or numbered lists to break up content and improve scannability',
			);
		}

		// Metadata recommendations.
		$metadata = $score_details['metadata']['details'];

		if ( isset( $metadata['meta_description'] ) && $metadata['meta_description']['score'] < 8 ) {
			$recommendations[] = array(
				'type'     => 'metadata',
				'severity' => 'medium',
				'message'  => 'Missing or poor meta description',
				'action'   => 'Add 150-160 character summary with focus keyword using Yoast/Rank Math',
			);
		}

		if ( isset( $metadata['seo_title'] ) && $metadata['seo_title']['score'] < 7 ) {
			$recommendations[] = array(
				'type'     => 'metadata',
				'severity' => 'medium',
				'message'  => 'SEO title needs optimization',
				'action'   => 'Optimize title to 50-60 characters with focus keyword',
			);
		}

		if ( isset( $metadata['keyword_placement'] ) && $metadata['keyword_placement']['score'] === 0 ) {
			$recommendations[] = array(
				'type'     => 'metadata',
				'severity' => 'low',
				'message'  => 'Focus keyword not in first paragraph',
				'action'   => 'Include your focus keyword naturally in the opening paragraph',
			);
		}

		// Readability recommendations.
		$readability = $score_details['readability']['details'];

		if ( isset( $readability['flesch_reading_ease'] ) && $readability['flesch_reading_ease']['score'] < 7 ) {
			$recommendations[] = array(
				'type'     => 'readability',
				'severity' => 'high',
				'message'  => 'Content is difficult to read',
				'action'   => 'Simplify language, use shorter sentences, and break up complex ideas',
			);
		}

		if ( isset( $readability['sentence_length'] ) && $readability['sentence_length']['score'] < 3 ) {
			$recommendations[] = array(
				'type'     => 'readability',
				'severity' => 'medium',
				'message'  => 'Sentences are too long',
				'action'   => 'Break long sentences into shorter ones (aim for < 20 words average)',
			);
		}

		if ( isset( $readability['passive_voice'] ) && $readability['passive_voice']['score'] < 3 ) {
			$recommendations[] = array(
				'type'     => 'readability',
				'severity' => 'low',
				'message'  => 'High passive voice usage',
				'action'   => 'Use active voice for clearer, more engaging writing',
			);
		}

		// Schema recommendations.
		$schema = $score_details['schema']['details'];

		if ( isset( $schema['schema_markup'] ) && ! $schema['schema_markup']['present'] ) {
			$recommendations[] = array(
				'type'     => 'markup',
				'severity' => 'medium',
				'message'  => 'No schema.org markup detected',
				'action'   => 'Add FAQ, Article, or HowTo schema using Yoast SEO or Rank Math',
			);
		}

		if ( isset( $schema['table_of_contents'] ) && $schema['table_of_contents']['required'] && $schema['table_of_contents']['score'] === 0 ) {
			$recommendations[] = array(
				'type'     => 'markup',
				'severity' => 'low',
				'message'  => 'No table of contents for long post',
				'action'   => 'Add a table of contents for posts over 1500 words (use Easy TOC or similar)',
			);
		}

		return $recommendations;
	}
}
