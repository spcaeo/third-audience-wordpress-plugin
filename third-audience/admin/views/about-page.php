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

		<!-- Version History -->
		<div class="ta-card">
			<h2><?php esc_html_e( 'Version History & Changelog', 'third-audience' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Track all updates and improvements to Third Audience.', 'third-audience' ); ?>
			</p>

			<!-- Version 1.1.1 -->
			<div class="ta-version-entry" style="border-left: 4px solid #667eea; padding-left: 20px; margin: 20px 0;">
				<h3 style="margin: 0 0 10px 0; color: #667eea;">
					<?php esc_html_e( 'Version 1.1.1', 'third-audience' ); ?>
					<span style="font-size: 14px; color: #646970; font-weight: normal;">— January 21, 2026</span>
				</h3>
				<p><strong><?php esc_html_e( 'Bug Fix:', 'third-audience' ); ?></strong></p>
				<ul>
					<li><?php esc_html_e( 'Homepage URLs now generate correct .md URLs (e.g., /index.md instead of .md)', 'third-audience' ); ?></li>
				</ul>
				<p><strong><?php esc_html_e( 'New Features:', 'third-audience' ); ?></strong></p>
				<ul>
					<li><?php esc_html_e( 'Customizable homepage markdown pattern (index.md, home.md, root.md, or custom)', 'third-audience' ); ?></li>
					<li><?php esc_html_e( 'Automatic fallback to latest post when no static homepage is set', 'third-audience' ); ?></li>
				</ul>
				<p><strong><?php esc_html_e( 'Improvements:', 'third-audience' ); ?></strong></p>
				<ul>
					<li><?php esc_html_e( 'Better URL parsing to handle edge cases', 'third-audience' ); ?></li>
					<li><?php esc_html_e( 'Settings UI with pattern examples and warnings', 'third-audience' ); ?></li>
					<li><?php esc_html_e( 'JavaScript for custom pattern toggle', 'third-audience' ); ?></li>
				</ul>
			</div>

			<!-- Version 1.1.0 -->
			<div class="ta-version-entry" style="border-left: 4px solid #2271b1; padding-left: 20px; margin: 20px 0;">
				<h3 style="margin: 0 0 10px 0; color: #2271b1;">
					<?php esc_html_e( 'Version 1.1.0', 'third-audience' ); ?>
					<span style="font-size: 14px; color: #646970; font-weight: normal;">— January 21, 2026</span>
				</h3>
				<p><strong><?php esc_html_e( 'Major Features:', 'third-audience' ); ?></strong></p>
				<ul>
					<li><?php esc_html_e( 'Headless WordPress Configuration Wizard', 'third-audience' ); ?></li>
					<li><?php esc_html_e( 'Auto-detect headless setup and generate configuration snippets', 'third-audience' ); ?></li>
					<li><?php esc_html_e( 'Support for Nginx, Apache, Cloudflare, Vercel, and Next.js', 'third-audience' ); ?></li>
					<li><?php esc_html_e( 'One-click copy for server configurations', 'third-audience' ); ?></li>
				</ul>
				<p><strong><?php esc_html_e( 'Performance:', 'third-audience' ); ?></strong></p>
				<ul>
					<li><?php esc_html_e( '1-hour transient cache for auto-detection', 'third-audience' ); ?></li>
					<li><?php esc_html_e( 'Rate limiting (3 tests per 5 minutes)', 'third-audience' ); ?></li>
					<li><?php esc_html_e( 'Filter hooks for extensibility', 'third-audience' ); ?></li>
				</ul>
			</div>

			<!-- Version 1.0.0 -->
			<div class="ta-version-entry" style="border-left: 4px solid #00a32a; padding-left: 20px; margin: 20px 0;">
				<h3 style="margin: 0 0 10px 0; color: #00a32a;">
					<?php esc_html_e( 'Version 1.0.0', 'third-audience' ); ?>
					<span style="font-size: 14px; color: #646970; font-weight: normal;">— January 16, 2026</span>
				</h3>
				<p><strong><?php esc_html_e( 'Initial Release:', 'third-audience' ); ?></strong></p>
				<ul>
					<li><?php esc_html_e( 'Local HTML to Markdown conversion (no external dependencies!)', 'third-audience' ); ?></li>
					<li><?php esc_html_e( 'Support for all post types', 'third-audience' ); ?></li>
					<li><?php esc_html_e( 'Pre-generation on post save', 'third-audience' ); ?></li>
					<li><?php esc_html_e( 'Bot-specific blocking and analytics', 'third-audience' ); ?></li>
					<li><?php esc_html_e( 'Cache management', 'third-audience' ); ?></li>
					<li><?php esc_html_e( 'Discovery tags for AI crawlers', 'third-audience' ); ?></li>
				</ul>
			</div>

			<p style="margin-top: 30px;">
				<a href="https://github.com/spcaeo/third-audience-wordpress-plugin/releases" class="button button-secondary" target="_blank" rel="noopener">
					<span class="dashicons dashicons-external" style="vertical-align: middle;"></span>
					<?php esc_html_e( 'View All Releases on GitHub', 'third-audience' ); ?>
				</a>
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
	border: 1px solid #e2e8f0;
	border-radius: 8px;
	padding: 30px 40px;
	margin-bottom: 24px;
	box-shadow: 0 1px 3px rgba(0,0,0,.08);
	transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.ta-card:hover {
	transform: translateY(-1px);
	box-shadow: 0 4px 12px rgba(0,0,0,.12);
}

.ta-hero {
	background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
	color: #1e293b;
	border: 1px solid #cbd5e1;
	border-left: 6px solid #3b82f6;
	padding: 50px 40px;
	position: relative;
	overflow: hidden;
}

.ta-hero::before {
	content: '';
	position: absolute;
	top: -50px;
	right: -50px;
	width: 200px;
	height: 200px;
	background: radial-gradient(circle, rgba(59,130,246,0.08) 0%, transparent 70%);
	border-radius: 50%;
}

.ta-hero h2 {
	color: #1e40af;
	margin-top: 0;
	font-size: 2.2em;
	font-weight: 700;
	letter-spacing: -0.5px;
}

.ta-lead {
	font-size: 1.25em;
	line-height: 1.7;
	margin-bottom: 20px;
	font-weight: 500;
	color: #334155;
}

.ta-card h2 {
	margin-top: 0;
	font-size: 1.6em;
	font-weight: 700;
	color: #0f172a;
	border-bottom: 3px solid #3b82f6;
	padding-bottom: 12px;
	letter-spacing: -0.3px;
}

.ta-card h3 {
	margin-top: 24px;
	margin-bottom: 12px;
	font-size: 1.15em;
	font-weight: 600;
	color: #0f172a;
	display: flex;
	align-items: center;
	gap: 8px;
}

.ta-card ul,
.ta-card ol {
	margin-left: 24px;
	line-height: 1.9;
}

.ta-card li {
	margin-bottom: 10px;
	color: #475569;
}

.ta-card p {
	color: #475569;
	line-height: 1.8;
}

.ta-card pre {
	background: #f1f5f9 !important;
	border: 1px solid #cbd5e1 !important;
	border-left: 4px solid #3b82f6 !important;
	padding: 20px !important;
	border-radius: 6px !important;
	font-size: 0.9em;
	line-height: 1.6;
	color: #1e293b !important;
}

.ta-card code {
	color: #0f172a;
	background: #f1f5f9;
	padding: 2px 6px;
	border-radius: 3px;
	font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
	font-size: 0.9em;
}

.ta-credits {
	background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
	border-left: 4px solid #3b82f6;
	border-radius: 8px;
}

.ta-credits p {
	margin: 12px 0;
	color: #475569;
}

.ta-credits a {
	color: #2563eb;
	text-decoration: none;
	font-weight: 600;
	transition: color 0.2s ease;
}

.ta-credits a:hover {
	color: #1d4ed8;
	text-decoration: underline;
}

.ta-version-entry {
	border-left: 4px solid #3b82f6;
	padding-left: 24px;
	margin: 24px 0;
	transition: border-color 0.2s ease;
}

.ta-version-entry:hover {
	border-left-color: #2563eb;
}

.ta-version-entry h3 {
	margin: 0 0 12px 0;
	color: #1e40af;
	font-weight: 600;
}

.ta-version-entry ul {
	margin-top: 8px;
}

.button-primary {
	background: #3b82f6 !important;
	border-color: #3b82f6 !important;
	text-shadow: none !important;
	box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2) !important;
	font-weight: 500 !important;
	padding: 8px 20px !important;
	transition: all 0.2s ease !important;
}

.button-primary:hover {
	background: #2563eb !important;
	border-color: #2563eb !important;
	box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3) !important;
	transform: translateY(-1px);
}

.button-secondary {
	border-color: #cbd5e1 !important;
	color: #475569 !important;
	transition: all 0.2s ease !important;
}

.button-secondary:hover {
	border-color: #94a3b8 !important;
	color: #1e293b !important;
}

/* Icon badges for features */
.ta-card h3::before {
	content: attr(data-icon);
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 28px;
	height: 28px;
	background: #eff6ff;
	border-radius: 6px;
	font-size: 16px;
}

/* Smooth scroll */
html {
	scroll-behavior: smooth;
}

/* Technical flow diagram enhancement */
.ta-card div[style*="font-family: monospace"] {
	background: #f8fafc;
	border: 1px solid #e2e8f0;
	border-radius: 8px;
	font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace !important;
}

/* Responsive design */
@media (max-width: 782px) {
	.ta-card {
		padding: 20px 24px;
	}

	.ta-hero {
		padding: 30px 24px;
	}

	.ta-hero h2 {
		font-size: 1.8em;
	}

	.ta-lead {
		font-size: 1.1em;
	}
}
</style>
