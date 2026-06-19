<?php
/**
 * OKF Bundle - Serves an Open Knowledge Format (OKF v0.1) bundle at /okf/.
 *
 * Builds a self-contained bundle of clean Markdown files (one concept document
 * per published post/page, plus an index.md manifest and a log.md changelog)
 * and serves it live at `/okf/`. Internal links between posts are rewritten to
 * point at sibling `.md` files so the bundle is a navigable graph rather than a
 * set of isolated documents.
 *
 * The bundle is cached in a single non-autoloaded option and regenerated when
 * content changes. Concept bodies reuse the existing local converter (and its
 * pre-generated cache where available), so this layer adds the OKF wrapper
 * without duplicating the HTML-to-Markdown logic.
 *
 * @package ThirdAudience
 * @since   3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_OKF_Bundle
 *
 * @since 3.6.0
 */
class TA_OKF_Bundle {

	/**
	 * Option key for the cached bundle.
	 */
	const OPTION_BUNDLE = 'ta_okf_bundle';

	/**
	 * Option key tracking the version the /okf/ rewrite rules were flushed for.
	 */
	const OPTION_REWRITE_VERSION = 'ta_okf_rewrite_version';

	/**
	 * Cache Manager instance.
	 *
	 * @var TA_Cache_Manager
	 */
	private $cache_manager;

	/**
	 * Local Converter instance.
	 *
	 * @var TA_Local_Converter
	 */
	private $converter;

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Constructor.
	 *
	 * @since 3.6.0
	 * @param TA_Cache_Manager|null $cache_manager Optional cache manager to reuse.
	 */
	public function __construct( $cache_manager = null ) {
		$this->cache_manager = $cache_manager instanceof TA_Cache_Manager ? $cache_manager : new TA_Cache_Manager();
		$this->converter     = new TA_Local_Converter();
		$this->logger        = TA_Logger::get_instance();
	}

	/* ---------------------------------------------------------------------
	 * Routing
	 * ------------------------------------------------------------------ */

	/**
	 * Register rewrite rules for the /okf/ bundle.
	 *
	 * Hooked early (priority 5) on `init` so these `top` rules are inserted
	 * before the generic `(.*)\.md` rule registered by TA_URL_Router; otherwise
	 * a request for `/okf/some-post.md` would be captured by the generic rule.
	 *
	 * @since 3.6.0
	 * @return void
	 */
	public function register_rewrite_rules() {
		add_rewrite_rule( '^okf/?$', 'index.php?ta_okf=index.md', 'top' );
		add_rewrite_rule( '^okf/([A-Za-z0-9._\-]+)/?$', 'index.php?ta_okf=$matches[1]', 'top' );
		add_rewrite_tag( '%ta_okf%', '([^&]+)' );
	}

	/**
	 * Flush rewrite rules once per plugin version, after all rules are
	 * registered (hooked on `wp_loaded`), so the /okf/ rules self-heal on
	 * update without requiring a manual reactivation.
	 *
	 * @since 3.6.0
	 * @return void
	 */
	public function maybe_flush_rules() {
		if ( get_option( self::OPTION_REWRITE_VERSION ) !== TA_VERSION ) {
			flush_rewrite_rules();
			update_option( self::OPTION_REWRITE_VERSION, TA_VERSION );
		}
	}

	/**
	 * Serve a bundle file when the ta_okf query var is present.
	 *
	 * @since 3.6.0
	 * @return void
	 */
	public function handle_request() {
		$file = get_query_var( 'ta_okf' );
		if ( '' === $file || null === $file ) {
			return;
		}

		// Feature can be toggled off from the admin without removing the rewrite
		// rules; when off we let the request fall through to a normal 404.
		if ( ! get_option( 'ta_enable_okf', true ) ) {
			return;
		}

		// Flatten to a bare filename: no directory traversal is ever possible.
		$file = basename( (string) $file );

		if ( ! preg_match( '/^[A-Za-z0-9._\-]+\.md$/', $file ) ) {
			$this->send_not_found( $file );
		}

		$store = get_option( self::OPTION_BUNDLE );
		if ( ! is_array( $store ) || empty( $store['files'] ) ) {
			$store = $this->build_bundle(); // Lazy build on first request.
		}

		if ( ! isset( $store['files'][ $file ] ) ) {
			$this->send_not_found( $file );
		}

		status_header( 200 );
		header( 'Content-Type: text/markdown; charset=utf-8' );
		header( 'X-Robots-Tag: all', true );
		header( 'X-Powered-By: Third Audience ' . TA_VERSION );
		header( 'X-Content-Type-Options: nosniff' );
		header( 'Cache-Control: public, max-age=300' );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Raw markdown is the response body by design (text/markdown).
		echo $store['files'][ $file ];
		exit;
	}

	/**
	 * Send a 404 for an unknown bundle file and stop.
	 *
	 * @since 3.6.0
	 * @param string $file The requested filename.
	 * @return void
	 */
	private function send_not_found( $file ) {
		status_header( 404 );
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'X-Content-Type-Options: nosniff' );
		echo 'Not found in OKF bundle: ' . esc_html( $file );
		exit;
	}

	/* ---------------------------------------------------------------------
	 * Bundle generation
	 * ------------------------------------------------------------------ */

	/**
	 * Regenerate the bundle when a post changes.
	 *
	 * @since 3.6.0
	 * @param int          $post_id Optional. The post ID (from save_post).
	 * @param WP_Post|null $post    Optional. The post object (from save_post).
	 * @return void
	 */
	public function regenerate( $post_id = 0, $post = null ) {
		if ( ! get_option( 'ta_enable_okf', true ) ) {
			return;
		}
		if ( $post_id && ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		$this->build_bundle();
	}

	/**
	 * Append an OKF bundle pointer to robots.txt (Phase 5 discovery).
	 *
	 * @since 3.6.0
	 * @param string $output The robots.txt content.
	 * @param bool   $public Whether the site is public.
	 * @return string The (possibly) augmented robots.txt content.
	 */
	public function add_robots_txt( $output, $public ) {
		if ( ! $public || ! get_option( 'ta_enable_okf', true ) ) {
			return $output;
		}
		$output .= "\n# Open Knowledge Format bundle for AI agents\n";
		$output .= '# ' . esc_url_raw( home_url( '/okf/index.md' ) ) . "\n";
		return $output;
	}

	/**
	 * Build the whole OKF bundle from published content and cache it.
	 *
	 * @since 3.6.0
	 * @return array The stored bundle (files, graph, stats, generated).
	 */
	public function build_bundle() {
		$enabled_types = get_option( 'ta_enabled_post_types', array( 'post', 'page' ) );

		$query = new WP_Query( array(
			'post_type'      => $enabled_types,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		) );
		$posts = $query->posts;

		// First pass: assign a stable, unique concept id (filename) per post and
		// map post ID -> id so internal links can be resolved in the second pass.
		$used        = array( 'index' => true, 'log' => true ); // Reserve OKF root filenames.
		$id_by_post  = array();
		$url_to_id   = array(); // Normalized permalink/path => concept id (headless-safe link resolution).
		$concepts    = array();
		foreach ( $posts as $p ) {
			$raw = $p->post_name ? $p->post_name : ( 'post-' . $p->ID );
			$id  = $this->safe_id( $raw, $used );

			$used[ $id ]            = true;
			$id_by_post[ $p->ID ]   = $id;
			$concepts[]             = array( 'post' => $p, 'id' => $id );

			// Map every form of this post's URL to its concept id. On headless
			// sites, content links point at the frontend domain while
			// url_to_postid() only resolves against the backend home_url — so we
			// match against the actual frontend AND backend permalinks (and their
			// paths) instead, which survives the domain split and any permalink
			// structure difference between front and back.
			$backend  = get_permalink( $p->ID );
			$frontend = function_exists( 'ta_frontend_permalink' ) ? ta_frontend_permalink( $p->ID ) : $backend;
			foreach ( array( $frontend, $backend ) as $u ) {
				if ( ! $u ) {
					continue;
				}
				$url_to_id[ $this->normalize_link( $u ) ] = $id;
				$path = wp_parse_url( $u, PHP_URL_PATH );
				if ( $path ) {
					$url_to_id[ untrailingslashit( $path ) ] = $id;
				}
			}
		}

		$files   = array();
		$nodes   = array();
		$edges   = array();
		$entries = array();

		foreach ( $concepts as $c ) {
			$p        = $c['post'];
			$markdown = $this->get_concept_markdown( $p->ID );
			if ( false === $markdown || '' === $markdown ) {
				continue;
			}

			$markdown = $this->rewrite_links( $markdown, $c['id'], $url_to_id, $edges );

			$files[ $c['id'] . '.md' ] = $markdown;

			$type      = ( 'post' === $p->post_type ) ? 'Article' : 'WebPage';
			$permalink = function_exists( 'ta_frontend_permalink' ) ? ta_frontend_permalink( $p->ID ) : get_permalink( $p->ID );

			$nodes[]   = array(
				'id'    => $c['id'],
				'title' => get_the_title( $p ),
				'type'  => $type,
				'url'   => $permalink,
			);
			$entries[] = array(
				'id'    => $c['id'],
				'title' => get_the_title( $p ),
				'desc'  => $this->concept_description( $p ),
				'date'  => substr( (string) get_post_modified_time( 'c', true, $p ), 0, 10 ),
			);
		}

		$files['index.md'] = $this->build_index_md( $entries );
		$log               = $this->build_log_md( $entries );
		if ( '' !== $log ) {
			$files['log.md'] = $log;
		}

		$store = array(
			'files'     => $files,
			'graph'     => array( 'nodes' => $nodes, 'edges' => $edges ),
			'stats'     => array(
				'posts'    => count( $posts ),
				'concepts' => count( $nodes ),
				'edges'    => count( $edges ),
			),
			'generated' => gmdate( 'Y-m-d H:i:s' ),
		);

		update_option( self::OPTION_BUNDLE, $store, false ); // Do not autoload; it can be large.

		return $store;
	}

	/**
	 * Get the OKF markdown for one concept.
	 *
	 * Reuses the pre-generated markdown when it already carries OKF frontmatter
	 * (a `type:` line), otherwise converts fresh so the concept file is always
	 * OKF-conformant.
	 *
	 * @since 3.6.0
	 * @param int $post_id The post ID.
	 * @return string|false The markdown, or false on failure.
	 */
	private function get_concept_markdown( $post_id ) {
		$pre = $this->cache_manager->get_pre_generated_markdown( $post_id );
		if ( is_string( $pre ) && '' !== $pre && false !== strpos( $pre, "\ntype:" ) ) {
			return $pre;
		}

		if ( ! TA_Local_Converter::is_library_available() ) {
			$this->logger->error( 'OKF: HTML to Markdown library is not available.' );
			return false;
		}

		$markdown = $this->converter->convert_post( $post_id, array(
			'include_frontmatter'    => true,
			'extract_main_content'   => true,
			'include_title'          => true,
			'include_excerpt'        => true,
			'include_featured_image' => true,
		) );

		if ( is_wp_error( $markdown ) ) {
			$this->logger->error( 'OKF: conversion failed.', array(
				'post_id' => $post_id,
				'error'   => $markdown->get_error_message(),
			) );
			return false;
		}

		return $markdown;
	}

	/**
	 * Rewrite internal links to sibling <id>.md files and collect graph edges.
	 *
	 * Resolves links against a precomputed map of the bundle posts' frontend and
	 * backend permalinks (and their paths), rather than url_to_postid(). This is
	 * headless-safe: on a headless site content links point at the frontend
	 * domain, which url_to_postid() (matching only the backend home_url) cannot
	 * resolve — so those edges were silently dropped and nodes looked isolated.
	 *
	 * @since 3.6.0
	 * @param string $markdown   The concept markdown.
	 * @param string $source_id  The concept id of this document.
	 * @param array  $url_to_id  Map of normalized URL/path => concept id.
	 * @param array  $edges      Collected edges, appended by reference.
	 * @return string The markdown with internal links rewritten.
	 */
	private function rewrite_links( $markdown, $source_id, $url_to_id, &$edges ) {
		return preg_replace_callback(
			'/\]\((https?:\/\/[^)\s]+|\/[^)\s]+)\)/',
			function ( $m ) use ( $source_id, $url_to_id, &$edges ) {
				// Try the full normalized URL first, then the path alone (covers
				// relative links and front/back domain differences).
				$candidates = array( $this->normalize_link( $m[1] ) );
				$path = wp_parse_url( $m[1], PHP_URL_PATH );
				if ( $path ) {
					$candidates[] = untrailingslashit( $path );
				}
				foreach ( $candidates as $key ) {
					if ( '' !== $key && isset( $url_to_id[ $key ] ) && $url_to_id[ $key ] !== $source_id ) {
						$target   = $url_to_id[ $key ];
						$edges[]  = array( 'source' => $source_id, 'target' => $target );
						return '](' . $target . '.md)';
					}
				}
				return $m[0];
			},
			$markdown
		);
	}

	/**
	 * Normalize a URL for link matching: strip the fragment and query string,
	 * and the trailing slash.
	 *
	 * @since 3.6.0
	 * @param string $url The URL.
	 * @return string The normalized URL.
	 */
	private function normalize_link( $url ) {
		$url = preg_replace( '/[#?].*$/', '', (string) $url );
		return untrailingslashit( $url );
	}

	/**
	 * A concept description: Rank Math, then Yoast, then the excerpt.
	 *
	 * @since 3.6.0
	 * @param WP_Post $p The post.
	 * @return string The single-line description.
	 */
	private function concept_description( $p ) {
		$d = get_post_meta( $p->ID, 'rank_math_description', true );
		if ( ! $d ) {
			$d = get_post_meta( $p->ID, '_yoast_wpseo_metadesc', true );
		}
		if ( ! $d ) {
			$d = get_the_excerpt( $p );
		}
		return trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $d ) ) );
	}

	/**
	 * Build the root index.md (lists every concept). Per the OKF spec, index.md
	 * carries no frontmatter.
	 *
	 * @since 3.6.0
	 * @param array $entries Concept entries (id, title, desc, date).
	 * @return string The index.md content.
	 */
	private function build_index_md( $entries ) {
		$site  = wp_parse_url( home_url(), PHP_URL_HOST );
		$title = (string) get_option( 'ta_okf_bundle_title', '' );
		if ( '' === $title ) {
			$title = 'Knowledge Bundle for ' . $site;
		}
		$desc = (string) get_option( 'ta_okf_bundle_desc', '' );
		if ( '' === $desc ) {
			$desc = 'An OKF v0.1 bundle generated from ' . $site . ' by the Third Audience plugin.';
		}

		$lines = array( '# ' . $title, '', $desc, '', '# Concepts', '' );

		usort( $entries, function ( $a, $b ) {
			return strcmp( $a['id'], $b['id'] );
		} );
		foreach ( $entries as $e ) {
			$lines[] = '* [' . $e['title'] . '](' . $e['id'] . '.md)' . ( $e['desc'] ? ' - ' . $e['desc'] : '' );
		}

		return implode( "\n", $lines ) . "\n";
	}

	/**
	 * Build log.md, grouped by modified date, newest first.
	 *
	 * @since 3.6.0
	 * @param array $entries Concept entries (id, title, desc, date).
	 * @return string The log.md content, or '' if there is nothing to log.
	 */
	private function build_log_md( $entries ) {
		$by_date = array();
		foreach ( $entries as $e ) {
			if ( $e['date'] ) {
				$by_date[ $e['date'] ][] = $e;
			}
		}
		if ( empty( $by_date ) ) {
			return '';
		}

		krsort( $by_date );
		$lines = array( '# Update Log', '' );
		foreach ( $by_date as $date => $items ) {
			$lines[] = '## ' . $date;
			foreach ( $items as $e ) {
				$lines[] = '* **Update**: [' . $e['title'] . '](' . $e['id'] . '.md)';
			}
			$lines[] = '';
		}

		return implode( "\n", $lines );
	}

	/**
	 * Build a unique, filesystem-safe concept id from a slug.
	 *
	 * @since 3.6.0
	 * @param string $raw  Raw slug or fallback.
	 * @param array  $used Map of already-used ids.
	 * @return string The unique concept id.
	 */
	private function safe_id( $raw, $used ) {
		$id = sanitize_title( $raw );
		if ( ! $id ) {
			$id = 'page';
		}
		$base = $id;
		$n    = 2;
		while ( isset( $used[ $id ] ) ) {
			$id = $base . '-' . $n;
			$n++;
		}
		return $id;
	}

	/* ---------------------------------------------------------------------
	 * Admin page + knowledge graph (Phase 4)
	 * ------------------------------------------------------------------ */

	/**
	 * Register the "OKF Bundle" page as a submenu of the main Third Audience
	 * (Bot Analytics) menu, placed directly under "LLM Traffic".
	 *
	 * Hooked at priority 11 (after TA_Admin registers its submenus at 10) and
	 * inserted at position 2 — i.e. third in the list, right after "LLM Traffic"
	 * (position 0 is the parent "Bot Analytics", position 1 is "LLM Traffic").
	 *
	 * @since 3.6.0
	 * @return void
	 */
	public function register_admin_menu() {
		add_submenu_page(
			'third-audience-bot-analytics',
			__( 'OKF Bundle', 'third-audience' ),
			__( 'OKF Bundle', 'third-audience' ),
			'manage_options',
			'third-audience-okf',
			array( $this, 'render_admin_page' ),
			2
		);
	}

	/**
	 * Render the OKF admin page: settings, stats, serving URLs and the graph.
	 *
	 * @since 3.6.0
	 * @return void
	 */
	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$notice = '';

		if ( isset( $_POST['ta_okf_save'] ) && check_admin_referer( 'ta_okf_settings' ) ) {
			update_option( 'ta_enable_okf', isset( $_POST['ta_enable_okf'] ) ? 1 : 0 );
			update_option( 'ta_okf_bundle_title', sanitize_text_field( wp_unslash( isset( $_POST['ta_okf_bundle_title'] ) ? $_POST['ta_okf_bundle_title'] : '' ) ) );
			update_option( 'ta_okf_bundle_desc', sanitize_text_field( wp_unslash( isset( $_POST['ta_okf_bundle_desc'] ) ? $_POST['ta_okf_bundle_desc'] : '' ) ) );
			if ( get_option( 'ta_enable_okf', true ) ) {
				$this->build_bundle();
			}
			$notice = __( 'Settings saved and bundle regenerated.', 'third-audience' );
		}

		if ( isset( $_POST['ta_okf_generate'] ) && check_admin_referer( 'ta_okf_generate' ) ) {
			$this->build_bundle();
			$notice = __( 'Bundle regenerated.', 'third-audience' );
		}

		$enabled   = (bool) get_option( 'ta_enable_okf', true );
		$store     = get_option( self::OPTION_BUNDLE );
		$index_url = home_url( '/okf/' );
		$title     = (string) get_option( 'ta_okf_bundle_title', '' );
		$desc      = (string) get_option( 'ta_okf_bundle_desc', '' );

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'OKF Bundle', 'third-audience' ) . '</h1>';
		echo '<p>' . esc_html__( 'Serve an Open Knowledge Format (OKF v0.1) bundle of your content for AI agents at /okf/.', 'third-audience' ) . '</p>';

		if ( '' !== $notice ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $notice ) . '</p></div>';
		}

		if ( ! $enabled ) {
			echo '<div class="notice notice-warning"><p>' . esc_html__( 'OKF bundle serving is currently disabled. Enable it below to serve /okf/.', 'third-audience' ) . '</p></div>';
		}

		// Knowledge graph.
		if ( is_array( $store ) && ! empty( $store['graph']['nodes'] ) ) {
			echo $this->render_graph( $store['graph'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped within.
		}

		// Stats + serving.
		echo '<div class="card" style="max-width:none;padding:4px 20px 16px">';
		if ( is_array( $store ) && ! empty( $store['stats'] ) ) {
			$s = $store['stats'];
			echo '<h2>' . esc_html__( 'Bundle', 'third-audience' ) . '</h2>';
			echo '<p>';
			echo esc_html( sprintf(
				/* translators: 1: concept count, 2: post count, 3: internal link count */
				__( '%1$d concepts from %2$d posts/pages, %3$d internal links.', 'third-audience' ),
				(int) $s['concepts'],
				(int) $s['posts'],
				(int) $s['edges']
			) );
			if ( ! empty( $store['generated'] ) ) {
				echo ' ' . esc_html( sprintf( __( 'Last generated %s UTC.', 'third-audience' ), $store['generated'] ) );
			}
			echo '</p>';
			echo '<p><strong>' . esc_html__( 'Served at:', 'third-audience' ) . '</strong> <a href="' . esc_url( $index_url ) . '" target="_blank" rel="noopener"><code>' . esc_html( $index_url ) . '</code></a></p>';

			// Conformance: every concept file must carry a non-empty `type`.
			$non_conformant = 0;
			foreach ( $store['files'] as $path => $content ) {
				if ( 'index.md' === $path || 'log.md' === $path ) {
					continue;
				}
				if ( ! preg_match( '/^---\s.*\btype:\s*\S/s', (string) $content ) ) {
					$non_conformant++;
				}
			}
			if ( 0 === $non_conformant ) {
				echo '<p style="color:#1a7f37">&#10003; ' . esc_html__( 'All concepts conform to OKF v0.1.', 'third-audience' ) . '</p>';
			} else {
				echo '<p style="color:#bb2124">&#9888; ' . esc_html( sprintf(
					/* translators: %d: number of non-conformant files */
					__( '%d concept file(s) are missing a "type" field.', 'third-audience' ),
					$non_conformant
				) ) . '</p>';
			}
		} else {
			echo '<p>' . esc_html__( 'No bundle generated yet. Use the button below to build the first one.', 'third-audience' ) . '</p>';
		}

		echo '<form method="post" style="margin:0">';
		wp_nonce_field( 'ta_okf_generate' );
		echo '<button class="button button-secondary" name="ta_okf_generate" value="1">' . esc_html__( 'Generate bundle now', 'third-audience' ) . '</button>';
		echo '</form>';
		echo '</div>';

		// Settings form.
		echo '<div class="card" style="max-width:none;padding:4px 20px 16px;margin-top:18px">';
		echo '<h2>' . esc_html__( 'Settings', 'third-audience' ) . '</h2>';
		echo '<form method="post">';
		wp_nonce_field( 'ta_okf_settings' );
		echo '<table class="form-table" role="presentation"><tbody>';

		echo '<tr><th scope="row">' . esc_html__( 'Enable OKF bundle', 'third-audience' ) . '</th><td>';
		echo '<label><input type="checkbox" name="ta_enable_okf" value="1"' . checked( $enabled, true, false ) . '> ' . esc_html__( 'Serve the bundle at /okf/ and add OKF fields to markdown.', 'third-audience' ) . '</label>';
		echo '</td></tr>';

		echo '<tr><th scope="row"><label for="ta_okf_bundle_title">' . esc_html__( 'Bundle title', 'third-audience' ) . '</label></th><td>';
		echo '<input type="text" class="regular-text" id="ta_okf_bundle_title" name="ta_okf_bundle_title" value="' . esc_attr( $title ) . '" placeholder="' . esc_attr( 'Knowledge Bundle for ' . wp_parse_url( home_url(), PHP_URL_HOST ) ) . '">';
		echo '<p class="description">' . esc_html__( 'Heading at the top of index.md. Leave blank for the default.', 'third-audience' ) . '</p>';
		echo '</td></tr>';

		echo '<tr><th scope="row"><label for="ta_okf_bundle_desc">' . esc_html__( 'Bundle description', 'third-audience' ) . '</label></th><td>';
		echo '<textarea class="large-text" rows="2" id="ta_okf_bundle_desc" name="ta_okf_bundle_desc" placeholder="' . esc_attr( 'An OKF v0.1 bundle generated from ' . wp_parse_url( home_url(), PHP_URL_HOST ) . '.' ) . '">' . esc_textarea( $desc ) . '</textarea>';
		echo '</td></tr>';

		echo '</tbody></table>';
		echo '<p><button class="button button-primary" name="ta_okf_save" value="1">' . esc_html__( 'Save settings', 'third-audience' ) . '</button></p>';
		echo '</form>';
		echo '</div>';

		// Preview of index.md so the bundle can be inspected without leaving the page.
		if ( is_array( $store ) && ! empty( $store['files']['index.md'] ) ) {
			$preview = $store['files']['index.md'];
			if ( strlen( $preview ) > 4000 ) {
				$preview = substr( $preview, 0, 4000 ) . "\n…";
			}
			echo '<div class="card" style="max-width:none;padding:4px 20px 16px;margin-top:18px">';
			echo '<h2>' . esc_html__( 'index.md preview', 'third-audience' ) . '</h2>';
			echo '<pre style="background:#1e2127;color:#e6e6e6;border-radius:8px;padding:15px;overflow:auto;max-height:340px;white-space:pre-wrap;font-size:12.5px;line-height:1.55">' . esc_html( $preview ) . '</pre>';
			echo '</div>';
		}

		echo '</div>'; // .wrap
	}

	/**
	 * Render the knowledge-graph card: SVG canvas, JSON payload and renderer.
	 *
	 * Nodes are capped for display (most-connected first); the full set always
	 * stays in the bundle.
	 *
	 * @since 3.6.0
	 * @param array $graph The graph (nodes, edges).
	 * @return string The card HTML.
	 */
	private function render_graph( $graph ) {
		$cap   = 100; // Most-connected concepts shown; fewer = far more readable on link-dense sites.
		$nodes = isset( $graph['nodes'] ) && is_array( $graph['nodes'] ) ? $graph['nodes'] : array();
		$edges = isset( $graph['edges'] ) && is_array( $graph['edges'] ) ? $graph['edges'] : array();
		$total = count( $nodes );

		// Degree per node so we keep the most-connected concepts when capping.
		$deg = array();
		foreach ( $edges as $e ) {
			$deg[ $e['source'] ] = ( isset( $deg[ $e['source'] ] ) ? $deg[ $e['source'] ] : 0 ) + 1;
			$deg[ $e['target'] ] = ( isset( $deg[ $e['target'] ] ) ? $deg[ $e['target'] ] : 0 ) + 1;
		}

		$truncated = false;
		if ( $total > $cap ) {
			$truncated = true;
			usort( $nodes, function ( $a, $b ) use ( $deg ) {
				$da = isset( $deg[ $a['id'] ] ) ? $deg[ $a['id'] ] : 0;
				$db = isset( $deg[ $b['id'] ] ) ? $deg[ $b['id'] ] : 0;
				return $db - $da;
			} );
			$nodes = array_slice( $nodes, 0, $cap );
		}

		$keep    = array();
		$payload = array( 'nodes' => array(), 'edges' => array() );
		foreach ( $nodes as $n ) {
			$keep[ $n['id'] ]   = true;
			$payload['nodes'][] = array(
				'id'    => $n['id'],
				'title' => $n['title'],
				'type'  => $n['type'],
				'url'   => isset( $n['url'] ) ? $n['url'] : '',
			);
		}
		foreach ( $edges as $e ) {
			if ( isset( $keep[ $e['source'] ], $keep[ $e['target'] ] ) ) {
				$payload['edges'][] = array( 'source' => $e['source'], 'target' => $e['target'] );
			}
		}

		$json  = wp_json_encode( $payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );
		$shown = count( $payload['nodes'] );
		$links = count( $payload['edges'] );

		if ( $truncated ) {
			$note = sprintf(
				/* translators: 1: shown count, 2: total count */
				__( 'Showing the %1$d most connected of %2$d concepts. The full set is always in your bundle.', 'third-audience' ),
				(int) $shown,
				(int) $total
			);
		} else {
			$note = sprintf(
				/* translators: 1: concept count, 2: link count */
				__( '%1$d concepts and %2$d internal links.', 'third-audience' ),
				(int) $shown,
				(int) $links
			);
		}

		$html  = '<div class="card" style="max-width:none;padding:4px 20px 16px">';
		$html .= '<h2>' . esc_html__( 'Knowledge graph', 'third-audience' ) . '</h2>';
		$html .= '<p class="description">' . esc_html( $note ) . ' ' . esc_html__( 'Drag a node, scroll to zoom, drag the background to pan, click a node to open the page.', 'third-audience' ) . '</p>';
		$html .= '<svg id="ta-okf-graph" style="width:100%;height:72vh;min-height:560px;display:block;background:#0f1115;border-radius:8px;cursor:grab;touch-action:none" role="img" aria-label="' . esc_attr__( 'Knowledge graph of your content', 'third-audience' ) . '"></svg>';
		$html .= '<script>window.TA_OKF_GRAPH=' . $json . ';</script>';
		$html .= '<script>' . $this->graph_js() . '</script>';
		$html .= '</div>';
		return $html;
	}

	/**
	 * Self-contained force-directed graph renderer (vanilla JS, no dependencies).
	 *
	 * @since 3.6.0
	 * @return string The renderer script body.
	 */
	private function graph_js() {
		return <<<'JS'
(function () {
  var data = window.TA_OKF_GRAPH;
  var svg = document.getElementById('ta-okf-graph');
  if (!svg || !data || !data.nodes || !data.nodes.length) { return; }
  var NS = 'http://www.w3.org/2000/svg';
  var rect = svg.getBoundingClientRect();
  var W = Math.max(320, Math.round(rect.width)) || 900;
  var H = Math.max(360, Math.round(rect.height)) || 560;
  svg.setAttribute('viewBox', '0 0 ' + W + ' ' + H);
  var showLabels = data.nodes.length <= 40;
  var spread = Math.min(W, H) * 0.42;
  var nodes = data.nodes.map(function (n, i) {
    var ang = (i / data.nodes.length) * Math.PI * 2;
    return { id: n.id, title: n.title || n.id, type: n.type, url: n.url,
      x: W / 2 + Math.cos(ang) * spread, y: H / 2 + Math.sin(ang) * spread, vx: 0, vy: 0, deg: 0, fixed: false, moved: false };
  });
  var byId = {}; nodes.forEach(function (n) { byId[n.id] = n; });
  var edges = (data.edges || []).filter(function (e) { return byId[e.source] && byId[e.target] && e.source !== e.target; });
  edges.forEach(function (e) { byId[e.source].deg++; byId[e.target].deg++; });
  function radius(n) { return 6 + Math.min(10, n.deg * 1.7); }
  // Stronger repulsion + longer, softer springs + gentler centering spreads a
  // link-dense graph out instead of collapsing it into a hairball.
  function tick() {
    var i, j, a, b, dx, dy, d, d2, f, fx, fy;
    for (i = 0; i < nodes.length; i++) {
      a = nodes[i];
      for (j = i + 1; j < nodes.length; j++) {
        b = nodes[j];
        dx = a.x - b.x; dy = a.y - b.y; d2 = dx * dx + dy * dy || 0.01; d = Math.sqrt(d2);
        f = 9000 / d2; fx = f * dx / d; fy = f * dy / d;
        a.vx += fx; a.vy += fy; b.vx -= fx; b.vy -= fy;
      }
      a.vx += (W / 2 - a.x) * 0.005; a.vy += (H / 2 - a.y) * 0.005;
    }
    for (i = 0; i < edges.length; i++) {
      a = byId[edges[i].source]; b = byId[edges[i].target];
      dx = b.x - a.x; dy = b.y - a.y; d = Math.sqrt(dx * dx + dy * dy) || 0.01;
      f = (d - 150) * 0.012; fx = f * dx / d; fy = f * dy / d;
      a.vx += fx; a.vy += fy; b.vx -= fx; b.vy -= fy;
    }
    for (i = 0; i < nodes.length; i++) {
      a = nodes[i];
      if (a.fixed) { a.vx = 0; a.vy = 0; continue; }
      a.vx *= 0.86; a.vy *= 0.86;
      a.x += Math.max(-12, Math.min(12, a.vx));
      a.y += Math.max(-12, Math.min(12, a.vy));
    }
  }
  var view = document.createElementNS(NS, 'g'); svg.appendChild(view);
  var gE = document.createElementNS(NS, 'g'); var gN = document.createElementNS(NS, 'g');
  view.appendChild(gE); view.appendChild(gN);
  var tx = 0, ty = 0, sc = 1;
  function applyView() { view.setAttribute('transform', 'translate(' + tx + ',' + ty + ') scale(' + sc + ')'); }
  var edgeEls = edges.map(function () {
    var ln = document.createElementNS(NS, 'line');
    ln.setAttribute('stroke', 'rgba(130,175,230,0.10)'); ln.setAttribute('stroke-width', '0.7');
    gE.appendChild(ln); return ln;
  });
  nodes.forEach(function (n) {
    var g = document.createElementNS(NS, 'g'); g.setAttribute('cursor', n.url ? 'pointer' : 'grab');
    var c = document.createElementNS(NS, 'circle'); c.setAttribute('r', radius(n));
    c.setAttribute('fill', n.type === 'WebPage' || n.type === 'Page' ? '#8ab4e8' : '#5291d7');
    c.setAttribute('stroke', '#0f1115'); c.setAttribute('stroke-width', '1.5');
    var t = document.createElementNS(NS, 'text');
    t.textContent = n.title.length > 26 ? n.title.slice(0, 24) + '…' : n.title;
    t.setAttribute('fill', '#c9d4e0'); t.setAttribute('font-size', '11'); t.setAttribute('text-anchor', 'middle');
    t.setAttribute('pointer-events', 'none'); t.setAttribute('opacity', showLabels ? '1' : '0');
    var ti = document.createElementNS(NS, 'title'); ti.textContent = n.title; // native hover tooltip
    g.appendChild(c); g.appendChild(t); g.appendChild(ti);
    g.addEventListener('mouseenter', function () { highlight(n); });
    g.addEventListener('mouseleave', clearHi);
    if (n.url) { g.addEventListener('click', function () { if (n.moved) { n.moved = false; return; } window.open(n.url, '_blank', 'noopener'); }); }
    gN.appendChild(g); n.g = g; n.c = c; n.t = t;
  });
  function highlight(node) {
    var on = {}; on[node.id] = true;
    edges.forEach(function (e, i) {
      var hit = e.source === node.id || e.target === node.id;
      edgeEls[i].setAttribute('stroke', hit ? 'rgba(146,190,240,0.95)' : 'rgba(130,175,230,0.07)');
      if (hit) { on[e.source] = true; on[e.target] = true; }
    });
    nodes.forEach(function (m) {
      m.g.setAttribute('opacity', on[m.id] ? '1' : '0.22');
      m.t.setAttribute('opacity', on[m.id] ? '1' : (showLabels ? '1' : '0'));
    });
  }
  function clearHi() {
    edgeEls.forEach(function (el) { el.setAttribute('stroke', 'rgba(130,175,230,0.10)'); });
    nodes.forEach(function (m) {
      m.g.setAttribute('opacity', '1');
      if (!showLabels) { m.t.setAttribute('opacity', '0'); }
    });
  }
  function paint() {
    for (var i = 0; i < edges.length; i++) {
      var a = byId[edges[i].source], b = byId[edges[i].target];
      edgeEls[i].setAttribute('x1', a.x); edgeEls[i].setAttribute('y1', a.y);
      edgeEls[i].setAttribute('x2', b.x); edgeEls[i].setAttribute('y2', b.y);
    }
    nodes.forEach(function (n) {
      n.c.setAttribute('cx', n.x); n.c.setAttribute('cy', n.y);
      n.t.setAttribute('x', n.x); n.t.setAttribute('y', n.y - radius(n) - 5);
    });
  }
  function pt(el, ev) {
    var m = el.getScreenCTM(); if (!m) { return { x: ev.clientX, y: ev.clientY }; }
    var inv = m.inverse();
    return { x: inv.a * ev.clientX + inv.c * ev.clientY + inv.e, y: inv.b * ev.clientX + inv.d * ev.clientY + inv.f };
  }
  var drag = null, pan = null;
  svg.addEventListener('mousedown', function (ev) {
    var p = pt(view, ev), best = 900, hit = null;
    nodes.forEach(function (n) { var dx = n.x - p.x, dy = n.y - p.y, dd = dx * dx + dy * dy; if (dd < best) { best = dd; hit = n; } });
    if (hit) { drag = hit; hit.fixed = true; hit.moved = false; }
    else { var r = pt(svg, ev); pan = { x: r.x, y: r.y, tx: tx, ty: ty }; svg.style.cursor = 'grabbing'; }
  });
  window.addEventListener('mousemove', function (ev) {
    if (drag) { var p = pt(view, ev); drag.x = p.x; drag.y = p.y; drag.moved = true; tick(); drag.x = p.x; drag.y = p.y; paint(); }
    else if (pan) { var r = pt(svg, ev); tx = pan.tx + (r.x - pan.x); ty = pan.ty + (r.y - pan.y); applyView(); }
  });
  window.addEventListener('mouseup', function () {
    if (drag) { drag.fixed = false; drag = null; var f = 0; (function r() { tick(); paint(); if (f++ < 60) { requestAnimationFrame(r); } })(); }
    if (pan) { pan = null; svg.style.cursor = ''; }
  });
  svg.addEventListener('wheel', function (ev) {
    ev.preventDefault(); var r = pt(svg, ev); var lx = (r.x - tx) / sc, ly = (r.y - ty) / sc;
    sc = Math.max(0.15, Math.min(4, sc * (ev.deltaY < 0 ? 1.12 : 1 / 1.12)));
    tx = r.x - lx * sc; ty = r.y - ly * sc; applyView();
  }, { passive: false });
  // Frame the whole graph in the viewport (zoom/pan to fit the bounding box).
  function fitView() {
    var minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
    nodes.forEach(function (n) {
      var r = radius(n) + 24;
      if (n.x - r < minX) { minX = n.x - r; }
      if (n.y - r < minY) { minY = n.y - r; }
      if (n.x + r > maxX) { maxX = n.x + r; }
      if (n.y + r > maxY) { maxY = n.y + r; }
    });
    var bw = Math.max(1, maxX - minX), bh = Math.max(1, maxY - minY), pad = 30;
    sc = Math.max(0.15, Math.min(2, Math.min((W - pad * 2) / bw, (H - pad * 2) / bh)));
    tx = (W - bw * sc) / 2 - minX * sc;
    ty = (H - bh * sc) / 2 - minY * sc;
    applyView();
  }
  var k; for (k = 0; k < 500; k++) { tick(); } paint(); fitView();
})();
JS;
	}
}
