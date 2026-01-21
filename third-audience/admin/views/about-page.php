<?php
/**
 * About Page - Plugin information and documentation.
 *
 * @package ThirdAudience
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php esc_html_e( 'About Third Audience', 'third-audience' ); ?></h1>

	<div class="ta-about-container">
		<!-- Hero Section -->
		<div class="ta-card ta-hero">
			<h2><?php esc_html_e( 'Serving the Third Audience', 'third-audience' ); ?></h2>
			<p class="ta-lead">
				<?php esc_html_e( 'For two decades, we built websites for two audiences: humans and search engines. Today, there\'s a third audience that\'s rapidly growing: AI agents and crawlers.', 'third-audience' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'AI systems like Claude (Anthropic), ChatGPT (OpenAI), Perplexity, and Google Gemini are actively consuming web content to answer questions, generate summaries, and provide recommendations. However, most websites aren\'t optimized for these AI agents—they receive the same HTML designed for human browsers, not the clean, structured data AI systems prefer.', 'third-audience' ); ?>
			</p>
		</div>

		<!-- What is Third Audience -->
		<div class="ta-card">
			<h2><?php esc_html_e( 'What is Third Audience?', 'third-audience' ); ?></h2>
			<p>
				<strong><?php esc_html_e( 'Third Audience', 'third-audience' ); ?></strong>
				<?php esc_html_e( ' is a WordPress plugin that automatically serves AI-optimized Markdown versions of your content to AI crawlers, while humans continue to see your beautiful website design.', 'third-audience' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'Think of it as SEO for the AI era—just as you optimize for Google, you can now optimize for Claude, ChatGPT, Perplexity, and other AI agents that are becoming the primary way people discover and consume content.', 'third-audience' ); ?>
			</p>
		</div>

		<!-- How It Works -->
		<div class="ta-card">
			<h2><?php esc_html_e( 'How It Works', 'third-audience' ); ?></h2>
			<p><?php esc_html_e( 'Third Audience uses two complementary approaches to serve AI-optimized content:', 'third-audience' ); ?></p>

			<h3><?php esc_html_e( '1. Auto-Discovery (Recommended by Crawlers)', 'third-audience' ); ?></h3>
			<p><?php esc_html_e( 'Every HTML page includes a discovery tag that tells AI crawlers where to find the Markdown version:', 'third-audience' ); ?></p>
			<pre style="background: #f5f5f5; padding: 15px; border-left: 3px solid #667eea; overflow-x: auto;"><code>&lt;link rel="alternate" type="text/markdown" href="https://yoursite.com/post-name.md" /&gt;</code></pre>

			<h3><?php esc_html_e( '2. Direct .md URL Access', 'third-audience' ); ?></h3>
			<p><?php esc_html_e( 'AI agents can append .md to any post URL:', 'third-audience' ); ?></p>
			<pre style="background: #f5f5f5; padding: 15px; border-left: 3px solid #667eea; overflow-x: auto;"><code>https://yoursite.com/my-post       → HTML version (for humans)
https://yoursite.com/my-post.md    → Markdown version (for AI)</code></pre>

			<h3><?php esc_html_e( '3. Content Negotiation (HTTP Headers)', 'third-audience' ); ?></h3>
			<p><?php esc_html_e( 'Advanced AI crawlers can request Markdown using HTTP headers:', 'third-audience' ); ?></p>
			<pre style="background: #f5f5f5; padding: 15px; border-left: 3px solid #667eea; overflow-x: auto;"><code>GET /my-post HTTP/1.1
Accept: text/markdown

→ Returns Markdown instead of HTML</code></pre>
		</div>

		<!-- Technical Flow -->
		<div class="ta-card">
			<h2><?php esc_html_e( 'Technical Flow', 'third-audience' ); ?></h2>
			<p><?php esc_html_e( 'Here\'s what happens when an AI bot visits your site:', 'third-audience' ); ?></p>

			<div style="background: #f9f9f9; padding: 20px; margin: 20px 0; font-family: monospace; font-size: 14px; line-height: 1.8; border: 1px solid #ddd;">
				<div style="margin-bottom: 15px;">
					<strong style="color: #667eea;">┌─ AI Bot Visit ────────────────────────────────────┐</strong>
				</div>
				<div style="margin-left: 10px;">
					<div>│ 1. Request: GET /blog-post.md</div>
					<div style="color: #666;">│    User-Agent: ClaudeBot/1.0</div>
					<div style="margin-top: 10px;">│ ↓</div>
					<div style="margin-top: 10px;">│ 2. WordPress routes to Third Audience</div>
					<div style="margin-top: 10px;">│ ↓</div>
					<div style="margin-top: 10px;">│ 3. Check cache (post_meta)</div>
					<div style="color: #00a32a;">│    ✓ Found in 1ms → Skip to step 7</div>
					<div style="color: #666;">│    ✗ Not found → Continue to step 4</div>
					<div style="margin-top: 10px;">│ ↓</div>
					<div style="margin-top: 10px;">│ 4. Load post from WordPress</div>
					<div style="margin-top: 10px;">│ ↓</div>
					<div style="margin-top: 10px;">│ 5. Convert HTML → Markdown (locally)</div>
					<div style="color: #666;">│    - Extract main content (DOMDocument)</div>
					<div style="color: #666;">│    - Convert with league/html-to-markdown</div>
					<div style="color: #666;">│    - Add YAML frontmatter (title, date, author)</div>
					<div style="color: #666;">│    - Clean and format</div>
					<div style="color: #666;">│    Time: ~4ms</div>
					<div style="margin-top: 10px;">│ ↓</div>
					<div style="margin-top: 10px;">│ 6. Cache result (post_meta + transient)</div>
					<div style="margin-top: 10px;">│ ↓</div>
					<div style="margin-top: 10px;">│ 7. Track analytics</div>
					<div style="color: #666;">│    - Bot name, IP, country</div>
					<div style="color: #666;">│    - Response time, cache status</div>
					<div style="margin-top: 10px;">│ ↓</div>
					<div style="margin-top: 10px;">│ 8. Return Markdown</div>
					<div style="color: #666;">│    Content-Type: text/markdown; charset=UTF-8</div>
					<div style="color: #00a32a;">│    HTTP 200 OK</div>
				</div>
				<div style="margin-top: 15px;">
					<strong style="color: #667eea;">└───────────────────────────────────────────────────┘</strong>
				</div>
			</div>

			<p><strong><?php esc_html_e( 'Performance:', 'third-audience' ); ?></strong></p>
			<ul>
				<li><?php esc_html_e( 'First request (cache miss): 4-6ms', 'third-audience' ); ?></li>
				<li><?php esc_html_e( 'Subsequent requests (cache hit): 1-2ms', 'third-audience' ); ?></li>
				<li><?php esc_html_e( 'Pre-generated posts: <1ms', 'third-audience' ); ?></li>
			</ul>
		</div>

		<!-- Key Features -->
		<div class="ta-card">
			<h2><?php esc_html_e( 'Key Features', 'third-audience' ); ?></h2>

			<h3>🚀 <?php esc_html_e( 'Lightning Fast Local Conversion', 'third-audience' ); ?></h3>
			<ul>
				<li><?php esc_html_e( 'All HTML-to-Markdown conversion happens on your server', 'third-audience' ); ?></li>
				<li><?php esc_html_e( 'Zero network latency (1-4ms response times)', 'third-audience' ); ?></li>
				<li><?php esc_html_e( 'No external API dependencies or API keys required', 'third-audience' ); ?></li>
				<li><?php esc_html_e( 'Pre-generation option for instant delivery', 'third-audience' ); ?></li>
			</ul>

			<h3>🤖 <?php esc_html_e( 'Comprehensive Bot Detection & Analytics', 'third-audience' ); ?></h3>
			<ul>
				<li><?php esc_html_e( 'Tracks visits from Claude, ChatGPT, Perplexity, Google Gemini, and more', 'third-audience' ); ?></li>
				<li><?php esc_html_e( 'Real-time analytics dashboard showing bot behavior', 'third-audience' ); ?></li>
				<li><?php esc_html_e( 'Bot blocking capabilities with granular control', 'third-audience' ); ?></li>
				<li><?php esc_html_e( 'IP address and country tracking', 'third-audience' ); ?></li>
			</ul>

			<h3>🔒 <?php esc_html_e( 'Privacy-First Architecture', 'third-audience' ); ?></h3>
			<ul>
				<li><?php esc_html_e( 'Your content never leaves your server', 'third-audience' ); ?></li>
				<li><?php esc_html_e( 'No data sent to external services', 'third-audience' ); ?></li>
				<li><?php esc_html_e( 'Self-contained PHP-based conversion', 'third-audience' ); ?></li>
				<li><?php esc_html_e( 'Enterprise-grade for deployment at scale', 'third-audience' ); ?></li>
			</ul>

			<h3>🎯 <?php esc_html_e( 'Enterprise-Grade Health Monitoring', 'third-audience' ); ?></h3>
			<ul>
				<li><?php esc_html_e( 'Real-time system health checks', 'third-audience' ); ?></li>
				<li><?php esc_html_e( 'Automatic library detection', 'third-audience' ); ?></li>
				<li><?php esc_html_e( 'User-friendly error messages for non-technical users', 'third-audience' ); ?></li>
				<li><?php esc_html_e( 'Comprehensive troubleshooting guides', 'third-audience' ); ?></li>
			</ul>
		</div>

		<!-- Technical Architecture -->
		<div class="ta-card">
			<h2><?php esc_html_e( 'Technical Architecture', 'third-audience' ); ?></h2>
			<p><?php esc_html_e( 'Version 2.0 uses a fully self-contained architecture:', 'third-audience' ); ?></p>
			<ul>
				<li><strong><?php esc_html_e( 'PHP Library:', 'third-audience' ); ?></strong> <?php esc_html_e( 'league/html-to-markdown for high-quality conversion', 'third-audience' ); ?></li>
				<li><strong><?php esc_html_e( 'Dependency Management:', 'third-audience' ); ?></strong> <?php esc_html_e( 'Composer for clean dependency handling', 'third-audience' ); ?></li>
				<li><strong><?php esc_html_e( 'Content Extraction:', 'third-audience' ); ?></strong> <?php esc_html_e( 'DOMDocument for parsing and cleaning HTML', 'third-audience' ); ?></li>
				<li><strong><?php esc_html_e( 'Caching Strategy:', 'third-audience' ); ?></strong> <?php esc_html_e( 'Two-tier caching (pre-generated + transient)', 'third-audience' ); ?></li>
				<li><strong><?php esc_html_e( 'Performance:', 'third-audience' ); ?></strong> <?php esc_html_e( 'Sub-5ms conversion, 1ms cache hits', 'third-audience' ); ?></li>
			</ul>

			<h3><?php esc_html_e( 'No External Dependencies', 'third-audience' ); ?></h3>
			<p><?php esc_html_e( 'Unlike other solutions, Third Audience doesn\'t rely on:', 'third-audience' ); ?></p>
			<ul>
				<li>❌ <?php esc_html_e( 'External API services', 'third-audience' ); ?></li>
				<li>❌ <?php esc_html_e( 'Cloudflare Workers', 'third-audience' ); ?></li>
				<li>❌ <?php esc_html_e( 'Third-party conversion tools', 'third-audience' ); ?></li>
				<li>❌ <?php esc_html_e( 'API keys or authentication', 'third-audience' ); ?></li>
			</ul>
			<p><strong><?php esc_html_e( 'Everything runs locally on your WordPress server—fast, private, and reliable.', 'third-audience' ); ?></strong></p>
		</div>

		<!-- Requirements -->
		<div class="ta-card">
			<h2><?php esc_html_e( 'Requirements', 'third-audience' ); ?></h2>
			<ul>
				<li><strong><?php esc_html_e( 'PHP:', 'third-audience' ); ?></strong> <?php esc_html_e( '7.4 or higher (8.0+ recommended)', 'third-audience' ); ?></li>
				<li><strong><?php esc_html_e( 'WordPress:', 'third-audience' ); ?></strong> <?php esc_html_e( '5.8 or higher', 'third-audience' ); ?></li>
				<li><strong><?php esc_html_e( 'Composer:', 'third-audience' ); ?></strong> <?php esc_html_e( 'For installing dependencies (or use pre-packaged version)', 'third-audience' ); ?></li>
				<li><strong><?php esc_html_e( 'DOMDocument:', 'third-audience' ); ?></strong> <?php esc_html_e( 'PHP extension (usually included by default)', 'third-audience' ); ?></li>
			</ul>
		</div>

		<!-- Use Cases -->
		<div class="ta-card">
			<h2><?php esc_html_e( 'Use Cases', 'third-audience' ); ?></h2>

			<h3><?php esc_html_e( 'Content Publishers', 'third-audience' ); ?></h3>
			<p><?php esc_html_e( 'Ensure your articles are accurately cited and represented in AI-generated summaries.', 'third-audience' ); ?></p>

			<h3><?php esc_html_e( 'Documentation Sites', 'third-audience' ); ?></h3>
			<p><?php esc_html_e( 'Help AI assistants accurately answer technical questions about your products.', 'third-audience' ); ?></p>

			<h3><?php esc_html_e( 'E-commerce', 'third-audience' ); ?></h3>
			<p><?php esc_html_e( 'Make your product descriptions easily discoverable through AI shopping assistants.', 'third-audience' ); ?></p>

			<h3><?php esc_html_e( 'Blogs & Portfolios', 'third-audience' ); ?></h3>
			<p><?php esc_html_e( 'Increase your reach as AI systems recommend your expertise to users.', 'third-audience' ); ?></p>
		</div>

		<!-- Credits -->
		<div class="ta-card ta-credits">
			<h2><?php esc_html_e( 'Credits', 'third-audience' ); ?></h2>
			<p>
				<strong><?php esc_html_e( 'Developed by:', 'third-audience' ); ?></strong>
				<a href="https://labsmedia.com" target="_blank" rel="noopener"><?php esc_html_e( 'Labs Media', 'third-audience' ); ?></a>
			</p>
			<p>
				<strong><?php esc_html_e( 'Inspired by:', 'third-audience' ); ?></strong>
				<a href="https://dri.es/the-third-audience" target="_blank" rel="noopener"><?php esc_html_e( 'Dries Buytaert\'s article "The Third Audience"', 'third-audience' ); ?></a>
			</p>
			<p>
				<?php esc_html_e( 'This plugin was created to make the "third audience" concept accessible to all WordPress users. Special thanks to Dries for introducing this important shift in how we think about web content optimization.', 'third-audience' ); ?>
			</p>
		</div>

		<!-- Links -->
		<div class="ta-card">
			<h2><?php esc_html_e( 'Quick Links', 'third-audience' ); ?></h2>
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-bot-analytics' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'View Bot Analytics', 'third-audience' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-system-health' ) ); ?>" class="button">
					<?php esc_html_e( 'System Health', 'third-audience' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=third-audience' ) ); ?>" class="button">
					<?php esc_html_e( 'Settings', 'third-audience' ); ?>
				</a>
			</p>
		</div>
	</div>
</div>

<style>
.ta-about-container {
	max-width: 1200px;
	margin: 20px 0;
}

.ta-card {
	background: #fff;
	border: 1px solid #ccd0d4;
	border-radius: 4px;
	padding: 20px 30px;
	margin-bottom: 20px;
	box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.ta-hero {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
	border: none;
}

.ta-hero h2 {
	color: white;
	margin-top: 0;
}

.ta-lead {
	font-size: 1.2em;
	line-height: 1.6;
	margin-bottom: 20px;
}

.ta-card h2 {
	margin-top: 0;
	font-size: 1.5em;
	border-bottom: 2px solid #667eea;
	padding-bottom: 10px;
}

.ta-card h3 {
	margin-top: 20px;
	margin-bottom: 10px;
	font-size: 1.2em;
	color: #667eea;
}

.ta-card ul,
.ta-card ol {
	margin-left: 20px;
	line-height: 1.8;
}

.ta-card li {
	margin-bottom: 8px;
}

.ta-credits {
	background: #f9f9f9;
	border-left: 4px solid #667eea;
}

.ta-credits p {
	margin: 10px 0;
}

.ta-credits a {
	color: #667eea;
	text-decoration: none;
	font-weight: 500;
}

.ta-credits a:hover {
	text-decoration: underline;
}

.button-primary {
	background: #667eea !important;
	border-color: #667eea !important;
	text-shadow: none !important;
	box-shadow: none !important;
}

.button-primary:hover {
	background: #5568d3 !important;
	border-color: #5568d3 !important;
}
</style>
