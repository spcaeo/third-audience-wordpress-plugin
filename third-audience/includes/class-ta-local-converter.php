<?php
/**
 * Local Converter - Converts HTML to Markdown locally using PHP.
 *
 * Replaces the Cloudflare Worker dependency with local PHP-based conversion
 * using the league/html-to-markdown library. This makes the plugin fully
 * self-contained and removes external dependencies.
 *
 * @package ThirdAudience
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use League\HTMLToMarkdown\HtmlConverter;
use League\HTMLToMarkdown\Environment;

/**
 * Class TA_Local_Converter
 *
 * Handles local HTML-to-Markdown conversion.
 *
 * @since 2.0.0
 */
class TA_Local_Converter {

	/**
	 * HtmlConverter instance.
	 *
	 * @var HtmlConverter
	 */
	private $converter;

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Security instance.
	 *
	 * @var TA_Security
	 */
	private $security;

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->logger   = TA_Logger::get_instance();
		$this->security = TA_Security::get_instance();
		$this->init_converter();
	}

	/**
	 * Initialize the HTML-to-Markdown converter with custom configuration.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	private function init_converter() {
		try {
			// Enhanced options for production-grade markdown
			$options = array(
				'header_style'              => 'atx',     // Use # for headers
				'bold_style'                => '**',      // Use ** for bold
				'italic_style'              => '*',       // Use * for italic (GFM standard)
				'strip_tags'                => false,     // Selective stripping with remove_nodes
				'strip_placeholder_links'   => true,      // Remove # placeholder links
				'remove_nodes'              => 'script style nav header footer aside form iframe', // Remove UI chrome
				'hard_break'                => false,     // <br> → two trailing spaces (Daring Fireball spec)
				'list_item_style'           => '-',       // Use - for unordered lists
				'table_pipe_escape'         => '\\|',     // Proper table escaping
				'table_caption_side'        => 'top',     // Table captions above
				'use_autolinks'             => true,      // Convert URLs to autolinks
				'suppress_errors'           => true,      // Graceful error handling
				'preserve_comments'         => false,     // Remove HTML comments
			);

			$this->converter = new HtmlConverter( $options );

			// Add custom converters for enhanced formatting
			$environment = $this->converter->getEnvironment();

			// Add table converter for GitHub Flavored Markdown tables
			$environment->addConverter( new \League\HTMLToMarkdown\Converter\TableConverter() );

			// Add code block converters with language hints
			$environment->addConverter( new \League\HTMLToMarkdown\Converter\CodeConverter() );
			$environment->addConverter( new \League\HTMLToMarkdown\Converter\PreformattedConverter() );

			// Add blockquote converter for better quote formatting
			$environment->addConverter( new \League\HTMLToMarkdown\Converter\BlockquoteConverter() );

			$this->logger->debug( 'Enhanced HTML converter initialized successfully with custom converters.' );

		} catch ( \Throwable $e ) {
			$this->logger->error( 'Failed to initialize HTML converter.', array(
				'error' => $e->getMessage(),
			) );
			throw $e;
		}
	}

	/**
	 * Convert a WordPress post/page to Markdown.
	 *
	 * @since 2.0.0
	 * @param int|WP_Post $post    Post ID or WP_Post object.
	 * @param array       $options Optional. Conversion options.
	 * @return string|WP_Error The markdown content or WP_Error on failure.
	 */
	public function convert_post( $post, $options = array() ) {
		$post = get_post( $post );

		if ( ! $post ) {
			return new WP_Error( 'invalid_post', __( 'Invalid post.', 'third-audience' ) );
		}

		// Check content size to prevent timeouts (1MB limit).
		$content_size = strlen( $post->post_content );
		if ( $content_size > 1048576 ) {
			$this->logger->warning( 'Content exceeds maximum size for conversion.', array(
				'post_id' => $post->ID,
				'size'    => $content_size,
			) );
			return new WP_Error(
				'content_too_large',
				sprintf(
					/* translators: %s: Content size in KB */
					__( 'Content exceeds maximum size for conversion (%s KB). Maximum allowed is 1024 KB.', 'third-audience' ),
					number_format( $content_size / 1024, 2 )
				)
			);
		}

		$default_options = array(
			'include_frontmatter'  => true,
			'extract_main_content' => true,
			'include_title'        => true,
			'include_excerpt'      => true,
			'include_featured_image' => true,
		);

		$options = wp_parse_args( $options, $default_options );

		try {
			$start_time = microtime( true );

			// Build the markdown content
			$markdown = '';

			// Add YAML frontmatter
			if ( $options['include_frontmatter'] ) {
				$markdown .= $this->generate_frontmatter( $post );
			}

			// Add title
			if ( $options['include_title'] ) {
				$markdown .= '# ' . $this->security->sanitize_text( html_entity_decode( $post->post_title, ENT_QUOTES, 'UTF-8' ) ) . "\n\n";
			}

			// Add post meta
			$markdown .= $this->generate_post_meta( $post );

			// Add featured image
			if ( $options['include_featured_image'] && has_post_thumbnail( $post->ID ) ) {
				$markdown .= $this->generate_featured_image( $post );
			}

			// Add excerpt
			if ( $options['include_excerpt'] && ! empty( $post->post_excerpt ) ) {
				$markdown .= '> ' . $this->security->sanitize_text( $post->post_excerpt ) . "\n\n";
			}

			// Get post content
			$html_content = apply_filters( 'the_content', $post->post_content );

			// Extract main content if requested
			if ( $options['extract_main_content'] ) {
				$html_content = $this->extract_main_content( $html_content );
			}

			// Convert HTML to Markdown
			$body_markdown = $this->converter->convert( $html_content );

			// Clean up the markdown
			$body_markdown = $this->clean_markdown( $body_markdown );

			$markdown .= $body_markdown;

			// Add post footer
			$markdown .= $this->generate_footer( $post );

			$conversion_time = round( ( microtime( true ) - $start_time ) * 1000, 2 );

			$this->logger->debug( 'Post converted to markdown successfully.', array(
				'post_id'         => $post->ID,
				'conversion_time' => $conversion_time . 'ms',
				'size'            => strlen( $markdown ) . ' bytes',
			) );

			return $markdown;

		} catch ( \Throwable $e ) {
			$this->logger->error( 'Failed to convert post to markdown.', array(
				'post_id' => $post->ID,
				'error'   => $e->getMessage(),
			) );

			return new WP_Error(
				'conversion_failed',
				__( 'Failed to convert content to markdown.', 'third-audience' ),
				array( 'exception' => $e->getMessage() )
			);
		}
	}

	/**
	 * Generate YAML frontmatter for the markdown.
	 *
	 * @since 2.0.0
	 * @param WP_Post $post The post object.
	 * @return string YAML frontmatter.
	 */
	private function generate_frontmatter( $post ) {
		$frontmatter = "---\n";
		$frontmatter .= 'title: "' . addslashes( html_entity_decode( $post->post_title, ENT_QUOTES, 'UTF-8' ) ) . "\"\n";
		$frontmatter .= 'url: "' . get_permalink( $post->ID ) . "\"\n";
		$frontmatter .= 'date: "' . get_the_date( 'c', $post->ID ) . "\"\n";
		$frontmatter .= 'modified: "' . get_the_modified_date( 'c', $post->ID ) . "\"\n";
		// Author as object with name and url (matches Dries' format).
		$author_name = get_the_author_meta( 'display_name', $post->post_author );
		$author_url  = get_the_author_meta( 'user_url', $post->post_author );
		$frontmatter .= "author:\n";
		$frontmatter .= '  name: "' . addslashes( $author_name ) . "\"\n";
		if ( ! empty( $author_url ) ) {
			$frontmatter .= '  url: "' . esc_url( $author_url ) . "\"\n";
		}

		// Categories as multi-line YAML array.
		$categories = get_the_category( $post->ID );
		if ( ! empty( $categories ) ) {
			$frontmatter .= "categories:\n";
			foreach ( $categories as $cat ) {
				$frontmatter .= '  - "' . addslashes( $cat->name ) . "\"\n";
			}
		}

		// Tags as multi-line YAML array.
		$tags = get_the_tags( $post->ID );
		if ( ! empty( $tags ) ) {
			$frontmatter .= "tags:\n";
			foreach ( $tags as $tag ) {
				$frontmatter .= '  - "' . addslashes( $tag->name ) . "\"\n";
			}
		}

		// AI-Optimized Metadata (configurable)
		$enable_metadata = get_option( 'ta_enable_enhanced_metadata', true );

		if ( $enable_metadata ) {
			// Word count
			if ( get_option( 'ta_metadata_word_count', true ) ) {
				$word_count = $this->calculate_word_count( $post->post_content );
				$frontmatter .= 'word_count: ' . $word_count . "\n";
			}

			// Reading time
			if ( get_option( 'ta_metadata_reading_time', true ) ) {
				$reading_time = $this->calculate_reading_time( $post->post_content );
				$frontmatter .= 'reading_time: "' . $reading_time . ' min read"' . "\n";
			}

			// Summary (excerpt)
			if ( get_option( 'ta_metadata_summary', true ) ) {
				$summary = $this->generate_summary( $post );
				if ( ! empty( $summary ) ) {
					$frontmatter .= 'summary: "' . addslashes( $summary ) . "\"\n";
				}
			}

			// SEO Description (full, not truncated like summary)
			if ( get_option( 'ta_metadata_description', true ) ) {
				$description = $this->get_seo_description( $post );
				if ( ! empty( $description ) ) {
					$frontmatter .= 'description: "' . addslashes( $description ) . "\"\n";
				}
			}

			// SEO Keywords
			if ( get_option( 'ta_metadata_keywords', true ) ) {
				$keywords = $this->get_seo_keywords( $post );
				if ( ! empty( $keywords ) ) {
					$frontmatter .= 'keywords: "' . addslashes( $keywords ) . "\"\n";
				}
			}

			// Language
			if ( get_option( 'ta_metadata_language', true ) ) {
				$language = $this->get_language();
				$frontmatter .= 'language: "' . $language . "\"\n";
			}

			// Schema type
			if ( get_option( 'ta_metadata_schema_type', true ) ) {
				$schema_type = ( 'post' === $post->post_type ) ? 'Article' : 'WebPage';
				$frontmatter .= 'schema_type: "' . $schema_type . "\"\n";
			}

			// Related posts
			if ( get_option( 'ta_metadata_related_posts', true ) ) {
				$related_posts = $this->get_related_posts( $post );
				if ( ! empty( $related_posts ) ) {
					$frontmatter .= 'related_posts:' . "\n";
					foreach ( $related_posts as $related_post ) {
						$frontmatter .= '  - title: "' . addslashes( $related_post['title'] ) . "\"\n";
						$frontmatter .= '    url: "' . $related_post['url'] . "\"\n";
					}
				}
			}
		}

		$frontmatter .= "---\n\n";

		return $frontmatter;
	}

	/**
	 * Calculate word count for content.
	 *
	 * @since 2.0.7
	 * @param string $content Post content.
	 * @return int Word count.
	 */
	private function calculate_word_count( $content ) {
		// Strip HTML tags and shortcodes
		$text = wp_strip_all_tags( strip_shortcodes( $content ) );
		// Remove extra whitespace
		$text = trim( preg_replace( '/\s+/', ' ', $text ) );
		// Count words
		if ( empty( $text ) ) {
			return 0;
		}
		return str_word_count( $text );
	}

	/**
	 * Calculate reading time based on word count.
	 *
	 * @since 2.0.7
	 * @param string $content Post content.
	 * @return int Reading time in minutes.
	 */
	private function calculate_reading_time( $content ) {
		$word_count = $this->calculate_word_count( $content );
		$words_per_minute = 200; // Average reading speed
		$reading_time = ceil( $word_count / $words_per_minute );
		return max( 1, $reading_time ); // Minimum 1 minute
	}

	/**
	 * Generate a summary from post excerpt or first paragraph.
	 *
	 * @since 2.0.7
	 * @param WP_Post $post The post object.
	 * @return string Summary text (max 200 characters).
	 */
	private function generate_summary( $post ) {
		// Use post excerpt if available
		if ( ! empty( $post->post_excerpt ) ) {
			$summary = wp_strip_all_tags( $post->post_excerpt );
		} else {
			// Extract first paragraph from content
			$content = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
			$paragraphs = preg_split( '/\n\n+/', $content );
			$summary = ! empty( $paragraphs[0] ) ? $paragraphs[0] : '';
		}

		// Limit to 200 characters
		if ( strlen( $summary ) > 200 ) {
			$summary = substr( $summary, 0, 197 ) . '...';
		}

		return trim( $summary );
	}

	/**
	 * Get site language from WordPress locale.
	 *
	 * @since 2.0.7
	 * @return string Language code (e.g., 'en', 'es', 'fr').
	 */
	private function get_language() {
		$locale = get_locale();
		// Extract language code (e.g., 'en' from 'en_US')
		$language = substr( $locale, 0, 2 );
		return $language;
	}

	/**
	 * Get SEO description from various sources.
	 *
	 * @since 2.4.0
	 * @param WP_Post $post The post object.
	 * @return string SEO description.
	 */
	private function get_seo_description( $post ) {
		// Try Yoast SEO meta description
		$description = get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );

		// Try RankMath meta description
		if ( empty( $description ) ) {
			$description = get_post_meta( $post->ID, 'rank_math_description', true );
		}

		// Fallback to excerpt
		if ( empty( $description ) ) {
			$description = get_the_excerpt( $post );
		}

		// Fallback to first paragraph of content
		if ( empty( $description ) && ! empty( $post->post_content ) ) {
			$content = wp_strip_all_tags( $post->post_content );
			$paragraphs = explode( "\n\n", $content );
			$description = ! empty( $paragraphs[0] ) ? $paragraphs[0] : '';
		}

		// Trim to reasonable length (160 characters for SEO)
		if ( strlen( $description ) > 160 ) {
			$description = substr( $description, 0, 157 ) . '...';
		}

		return trim( $description );
	}

	/**
	 * Get SEO keywords from various sources.
	 *
	 * @since 2.4.0
	 * @param WP_Post $post The post object.
	 * @return string Comma-separated keywords.
	 */
	private function get_seo_keywords( $post ) {
		$keywords = array();

		// Try Yoast SEO focus keyword
		$focus_keyword = get_post_meta( $post->ID, '_yoast_wpseo_focuskw', true );
		if ( ! empty( $focus_keyword ) ) {
			$keywords[] = $focus_keyword;
		}

		// Try RankMath focus keyword
		if ( empty( $keywords ) ) {
			$rank_math_keyword = get_post_meta( $post->ID, 'rank_math_focus_keyword', true );
			if ( ! empty( $rank_math_keyword ) ) {
				$keywords[] = $rank_math_keyword;
			}
		}

		// Add tags as keywords
		$tags = get_the_tags( $post->ID );
		if ( ! empty( $tags ) ) {
			foreach ( $tags as $tag ) {
				$keywords[] = $tag->name;
			}
		}

		// Add categories as keywords if we still don't have enough
		if ( count( $keywords ) < 3 ) {
			$categories = get_the_category( $post->ID );
			if ( ! empty( $categories ) ) {
				foreach ( $categories as $category ) {
					$keywords[] = $category->name;
				}
			}
		}

		// Remove duplicates and limit to 10 keywords
		$keywords = array_unique( $keywords );
		$keywords = array_slice( $keywords, 0, 10 );

		return implode( ', ', $keywords );
	}

	/**
	 * Get related posts by category and tags.
	 *
	 * @since 2.0.7
	 * @param WP_Post $post The post object.
	 * @return array Related posts (up to 3).
	 */
	private function get_related_posts( $post ) {
		$related = array();

		// Get categories
		$categories = wp_get_post_categories( $post->ID );
		// Get tags
		$tags = wp_get_post_tags( $post->ID, array( 'fields' => 'ids' ) );

		if ( empty( $categories ) && empty( $tags ) ) {
			return $related;
		}

		// Build query args
		$args = array(
			'post_type'      => $post->post_type,
			'post_status'    => 'publish',
			'posts_per_page' => 3,
			'post__not_in'   => array( $post->ID ),
			'orderby'        => 'rand',
		);

		// Prioritize posts with same categories
		if ( ! empty( $categories ) ) {
			$args['category__in'] = $categories;
		}

		// Add tag filter if available
		if ( ! empty( $tags ) ) {
			$args['tag__in'] = $tags;
		}

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$related[] = array(
					'title' => get_the_title(),
					'url'   => get_permalink(),
				);
			}
			wp_reset_postdata();
		}

		return $related;
	}

	/**
	 * Generate post metadata section.
	 *
	 * @since 2.0.0
	 * @param WP_Post $post The post object.
	 * @return string Post meta markdown.
	 */
	private function generate_post_meta( $post ) {
		$meta = '';
		$meta .= '_Published: ' . get_the_date( '', $post->ID ) . '_  ' . "\n";
		$meta .= '_Author: ' . get_the_author_meta( 'display_name', $post->post_author ) . '_  ' . "\n";
		$meta .= "\n";

		return $meta;
	}

	/**
	 * Generate featured image markdown.
	 *
	 * @since 2.0.0
	 * @param WP_Post $post The post object.
	 * @return string Featured image markdown.
	 */
	private function generate_featured_image( $post ) {
		$thumbnail_id  = get_post_thumbnail_id( $post->ID );
		$thumbnail_url = wp_get_attachment_image_url( $thumbnail_id, 'large' );
		$alt_text      = get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true );

		if ( $thumbnail_url ) {
			return '![' . esc_attr( $alt_text ) . '](' . esc_url( $thumbnail_url ) . ')' . "\n\n";
		}

		return '';
	}

	/**
	 * Extract main content from HTML (remove sidebars, headers, footers, etc.).
	 *
	 * This function attempts to extract only the primary content area from rendered HTML,
	 * removing navigation, sidebars, forms, comments, and other UI chrome that isn't
	 * relevant for AI agents consuming the content.
	 *
	 * @since 2.0.0
	 * @param string $html The HTML content.
	 * @return string Cleaned HTML containing only main content.
	 */
	private function extract_main_content( $html ) {
		// DOMDocument is required for content extraction.
		if ( ! class_exists( 'DOMDocument' ) ) {
			$this->logger->warning( 'DOMDocument not available, skipping content extraction.' );
			return $html;
		}

		// Use DOMDocument to parse HTML
		$dom = new DOMDocument();
		libxml_use_internal_errors( true ); // Suppress HTML5 warnings
		@$dom->loadHTML( '<?xml encoding="utf-8" ?><html><body>' . $html . '</body></html>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();

		$xpath = new DOMXPath( $dom );

		// Strategy 1: Look for <article> tag (HTML5 semantic markup)
		$article = $xpath->query( '//article' )->item( 0 );
		if ( $article ) {
			$this->logger->debug( 'Found <article> tag, using as main content.' );
			return $this->get_inner_html( $article );
		}

		// Strategy 2: Look for common WordPress content class names
		$content_selectors = array(
			'entry-content',
			'post-content',
			'main-content',
			'content-area',
			'article-content',
			'page-content',
		);

		foreach ( $content_selectors as $class ) {
			$elements = $xpath->query( "//*[contains(@class, '{$class}')]" );
			if ( $elements->length > 0 ) {
				$this->logger->debug( "Found content via class: {$class}" );
				return $this->get_inner_html( $elements->item( 0 ) );
			}
		}

		// Strategy 3: Look for <main> tag
		$main = $xpath->query( '//main' )->item( 0 );
		if ( $main ) {
			$this->logger->debug( 'Found <main> tag, using as main content.' );
			return $this->get_inner_html( $main );
		}

		// Strategy 4: Remove known UI chrome elements and return what's left
		$this->logger->debug( 'Using fallback: removing known UI chrome elements.' );
		$remove_selectors = array(
			'//script',
			'//style',
			'//nav',
			'//header',
			'//footer',
			'//aside',
			'//form',
			'//*[contains(@class, "sidebar")]',
			'//*[contains(@class, "widget")]',
			'//*[contains(@class, "navigation")]',
			'//*[contains(@class, "menu")]',
			'//*[contains(@class, "nav")]',
			'//*[contains(@class, "comment")]',
			'//*[contains(@class, "related")]',
			'//*[contains(@class, "footer")]',
			'//*[contains(@class, "header")]',
			'//*[contains(@id, "sidebar")]',
			'//*[contains(@id, "footer")]',
			'//*[contains(@id, "header")]',
			'//*[contains(@id, "nav")]',
			'//*[contains(@id, "menu")]',
		);

		$nodes_to_remove = array();
		foreach ( $remove_selectors as $selector ) {
			$elements = $xpath->query( $selector );
			foreach ( $elements as $element ) {
				$nodes_to_remove[] = $element;
			}
		}

		// Remove collected nodes
		foreach ( $nodes_to_remove as $node ) {
			if ( $node->parentNode ) {
				$node->parentNode->removeChild( $node );
			}
		}

		// Get cleaned HTML
		$body = $xpath->query( '//body' )->item( 0 );
		if ( $body ) {
			return $this->get_inner_html( $body );
		}

		// Fallback: return the original HTML (better than nothing)
		$this->logger->warning( 'Could not extract main content, returning original HTML.' );
		return $html;
	}

	/**
	 * Get inner HTML of a DOMNode.
	 *
	 * @since 2.0.0
	 * @param DOMNode $node The DOM node.
	 * @return string The inner HTML.
	 */
	private function get_inner_html( $node ) {
		$innerHTML = '';
		$children  = $node->childNodes;

		foreach ( $children as $child ) {
			$innerHTML .= $node->ownerDocument->saveHTML( $child );
		}

		return trim( $innerHTML );
	}

	/**
	 * Clean up the generated markdown.
	 *
	 * @since 2.0.0
	 * @param string $markdown The markdown content.
	 * @return string Cleaned markdown.
	 */
	private function clean_markdown( $markdown ) {
		// Convert strikethrough tags to GFM ~~ syntax before stripping HTML.
		// The library has no built-in <del>/<s>/<strike> converter.
		$markdown = preg_replace( '/<(del|s|strike)[^>]*>(.*?)<\/\1>/is', '~~$2~~', $markdown );

		// Convert Gutenberg FAQ/accordion blocks to readable Q&A markdown.
		// Gutenberg renders these as <details><summary>Q</summary></details>Answer
		// where the answer paragraph sits outside the </details> tag.
		// Convert <summary> content to a bold question heading.
		$markdown = preg_replace( '/<summary[^>]*>(.*?)<\/summary>/is', "\n\n**$1**\n\n", $markdown );
		// Strip <details> opening tag and convert </details> to a blank line separator.
		$markdown = preg_replace( '/<details[^>]*>/i', '', $markdown );
		$markdown = preg_replace( '/<\/details>/i', "\n", $markdown );

		// Strip remaining block-level HTML wrapper tags left over after conversion.
		// The library converts semantic tags (h1-h6, p, a, img, ul, li, etc.) to
		// Markdown syntax, but leaves structural wrappers (Gutenberg block divs,
		// figure, section, span, details, summary, etc.) as raw HTML since they
		// have no Markdown equivalent. Remove those wrappers while keeping their
		// text content.
		$markdown = preg_replace(
			'/<\/?(div|section|article|aside|figure|figcaption|span|header|footer|main|ul|li)[^>]*>\n?/i',
			'',
			$markdown
		);

		// Decode HTML entities left over from Gutenberg block content.
		$markdown = str_replace(
			array( '&amp;', '&lt;', '&gt;', '&quot;', '&#039;' ),
			array( '&',     '<',    '>',    '"',      "'"      ),
			$markdown
		);

		// Convert Gutenberg button blocks to plain text list items.
		// Buttons often render as image-links after conversion:
		//   [![icon](img_url) Label](#)[![icon2](img_url2)Label2](#)
		// Strip the icon/link wrapper and output each button on its own line.
		$markdown = preg_replace_callback(
			'/\[!\[[^\]]*\]\([^)]*\)\s*([^\]]+)\]\(#\)/u',
			function ( $m ) {
				return "\n- " . trim( $m[1] );
			},
			$markdown
		);

		// Fix merged lines: ensure a blank line before headings that run directly
		// into preceding content (e.g. "some text## Heading" → "some text\n\n## Heading").
		$markdown = preg_replace( '/([^#\n])(#{1,6} )/m', "$1\n\n$2", $markdown );

		// Strip excess bold markers wrapping entire heading text added by Gutenberg
		// (e.g. ### **Heading Text** or ### ****Heading Text**** → ### Heading Text).
		// Uses \s*$ to handle any trailing whitespace before end of line.
		$markdown = preg_replace( '/^(#{1,6} +)\*{2,}(.*?)\*{2,}\s*$/m', '$1$2', $markdown );

		// Remove excessive blank lines (more than 2 consecutive)
		$markdown = preg_replace( "/\n{3,}/", "\n\n", $markdown );

		// Trim whitespace from each line
		$lines = explode( "\n", $markdown );
		$lines = array_map( 'rtrim', $lines );
		$markdown = implode( "\n", $lines );

		// Ensure file ends with single newline
		$markdown = trim( $markdown ) . "\n";

		return $markdown;
	}

	/**
	 * Generate footer section with tracking metadata.
	 *
	 * @since 2.0.0
	 * @param WP_Post $post The post object.
	 * @return string Footer markdown.
	 */
	private function generate_footer( $post, $cache_status = null ) {
		$footer = "\n\n---\n\n";
		$footer .= '_View the original post at: [' . get_permalink( $post->ID ) . '](' . get_permalink( $post->ID ) . ')_  ' . "\n";
		$footer .= '_Served as markdown by [Third Audience](https://github.com/third-audience) v' . TA_VERSION . '_  ' . "\n";
		$footer .= '_Generated: ' . gmdate( 'Y-m-d H:i:s' ) . ' UTC_  ' . "\n";

		// Add cache status if provided
		if ( ! empty( $cache_status ) ) {
			$footer .= '_Cache Status: ' . strtoupper( $cache_status ) . '_  ' . "\n";
		}

		return $footer;
	}

	/**
	 * Check if the converter library is available.
	 *
	 * @since 2.0.0
	 * @return bool True if available, false otherwise.
	 */
	public static function is_library_available() {
		return class_exists( 'League\HTMLToMarkdown\HtmlConverter' );
	}

	/**
	 * Get library version.
	 *
	 * @since 2.0.0
	 * @return string|null Version string or null if not available.
	 */
	public static function get_library_version() {
		if ( ! self::is_library_available() ) {
			return null;
		}

		// Try to get version from composer.lock
		$lock_file = TA_PLUGIN_DIR . 'composer.lock';
		if ( file_exists( $lock_file ) ) {
			$lock_data = json_decode( file_get_contents( $lock_file ), true );
			if ( isset( $lock_data['packages'] ) ) {
				foreach ( $lock_data['packages'] as $package ) {
					if ( 'league/html-to-markdown' === $package['name'] ) {
						return $package['version'];
					}
				}
			}
		}

		return 'unknown';
	}

	/**
	 * Get system requirements status.
	 *
	 * @since 2.0.0
	 * @return array System requirements check results.
	 */
	public static function check_system_requirements() {
		$checks = array();

		// Check PHP version
		$checks['php_version'] = array(
			'required' => '7.4.0',
			'current'  => PHP_VERSION,
			'status'   => version_compare( PHP_VERSION, '7.4.0', '>=' ) ? 'ok' : 'error',
			'message'  => version_compare( PHP_VERSION, '7.4.0', '>=' )
				? 'PHP version is compatible'
				: 'PHP 7.4 or higher is required',
		);

		// Check if library is installed
		$checks['html_to_markdown'] = array(
			'required' => 'league/html-to-markdown ^5.1',
			'current'  => self::get_library_version(),
			'status'   => self::is_library_available() ? 'ok' : 'error',
			'message'  => self::is_library_available()
				? 'HTML to Markdown library is installed'
				: 'HTML to Markdown library is missing. Please run: composer install --no-dev in the plugin directory.',
		);

		// Check if DOMDocument is available
		$checks['dom_document'] = array(
			'required' => 'DOMDocument class',
			'current'  => class_exists( 'DOMDocument' ) ? 'Available' : 'Not Available',
			'status'   => class_exists( 'DOMDocument' ) ? 'ok' : 'warning',
			'message'  => class_exists( 'DOMDocument' )
				? 'DOMDocument is available'
				: 'DOMDocument extension is not available. Content extraction may be limited.',
		);

		// Check if wp_remote_get is available (for future enhancements)
		$checks['http_api'] = array(
			'required' => 'WordPress HTTP API',
			'current'  => function_exists( 'wp_remote_get' ) ? 'Available' : 'Not Available',
			'status'   => function_exists( 'wp_remote_get' ) ? 'ok' : 'error',
			'message'  => function_exists( 'wp_remote_get' )
				? 'WordPress HTTP API is available'
				: 'WordPress HTTP API is not available.',
		);

		return $checks;
	}
}
