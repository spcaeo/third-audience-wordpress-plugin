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
			$options = array(
				'header_style'    => 'atx',              // Use # for headers
				'bold_style'      => '**',               // Use ** for bold
				'italic_style'    => '_',                // Use _ for italic
				'strip_tags'      => true,               // Strip all HTML tags for pure markdown
				'remove_nodes'    => 'script style nav header footer aside form',  // Remove UI chrome
				'hard_break'      => false,              // Use double space for line breaks
				'list_item_style' => '-',                // Use - for unordered lists
				'table_pipe_escape' => '\\',             // Escape pipes in tables
				'use_autolinks'   => true,               // Convert URLs to autolinks
			);

			$this->converter = new HtmlConverter( $options );

			$this->logger->debug( 'Local HTML converter initialized successfully.' );

		} catch ( Exception $e ) {
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
				$markdown .= '# ' . $this->security->sanitize_text( $post->post_title ) . "\n\n";
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

		} catch ( Exception $e ) {
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
		$frontmatter .= 'title: "' . addslashes( $post->post_title ) . "\"\n";
		$frontmatter .= 'url: "' . get_permalink( $post->ID ) . "\"\n";
		$frontmatter .= 'date: "' . get_the_date( 'c', $post->ID ) . "\"\n";
		$frontmatter .= 'modified: "' . get_the_modified_date( 'c', $post->ID ) . "\"\n";
		$frontmatter .= 'author: "' . get_the_author_meta( 'display_name', $post->post_author ) . "\"\n";

		// Add categories
		$categories = get_the_category( $post->ID );
		if ( ! empty( $categories ) ) {
			$cat_names = array_map( function( $cat ) {
				return $cat->name;
			}, $categories );
			$frontmatter .= 'categories: [' . implode( ', ', array_map( function( $name ) {
				return '"' . addslashes( $name ) . '"';
			}, $cat_names ) ) . "]\n";
		}

		// Add tags
		$tags = get_the_tags( $post->ID );
		if ( ! empty( $tags ) ) {
			$tag_names = array_map( function( $tag ) {
				return $tag->name;
			}, $tags );
			$frontmatter .= 'tags: [' . implode( ', ', array_map( function( $name ) {
				return '"' . addslashes( $name ) . '"';
			}, $tag_names ) ) . "]\n";
		}

		$frontmatter .= "---\n\n";

		return $frontmatter;
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
	 * Generate footer section.
	 *
	 * @since 2.0.0
	 * @param WP_Post $post The post object.
	 * @return string Footer markdown.
	 */
	private function generate_footer( $post ) {
		$footer = "\n\n---\n\n";
		$footer .= '_View the original post at: [' . get_permalink( $post->ID ) . '](' . get_permalink( $post->ID ) . ')_  ' . "\n";
		$footer .= '_Served as markdown by [Third Audience](https://github.com/third-audience)_  ' . "\n";

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
