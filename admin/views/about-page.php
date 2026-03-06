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

<div class="wrap ta-about-page">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'About Third Audience', 'third-audience' ); ?>
		<span style="font-size: 0.6em; color: #646970; font-weight: 400;">v<?php echo esc_html( TA_VERSION ); ?></span>
	</h1>

	<p class="description">
		<?php esc_html_e( 'AI-optimized content delivery for WordPress. Serving the third audience with lightning-fast, privacy-first Markdown conversion.', 'third-audience' ); ?>
	</p>

	<div class="ta-about-container">
		<!-- Hero Section -->
		<div class="ta-card ta-hero">
			<div class="ta-hero-icon">
				<span class="dashicons dashicons-admin-users"></span>
			</div>
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
			<pre style="background: #f5f5f5; padding: 15px; border-left: 3px solid #007aff; overflow-x: auto;"><code>&lt;link rel="alternate" type="text/markdown" href="https://yoursite.com/post-name.md" /&gt;</code></pre>

			<h3><?php esc_html_e( '2. Direct .md URL Access', 'third-audience' ); ?></h3>
			<p><?php esc_html_e( 'AI agents can append .md to any post URL:', 'third-audience' ); ?></p>
			<pre style="background: #f5f5f5; padding: 15px; border-left: 3px solid #007aff; overflow-x: auto;"><code>https://yoursite.com/my-post       → HTML version (for humans)
https://yoursite.com/my-post.md    → Markdown version (for AI)</code></pre>

			<h3><?php esc_html_e( '3. Content Negotiation (HTTP Headers)', 'third-audience' ); ?></h3>
			<p><?php esc_html_e( 'Advanced AI crawlers can request Markdown using HTTP headers:', 'third-audience' ); ?></p>
			<pre style="background: #f5f5f5; padding: 15px; border-left: 3px solid #007aff; overflow-x: auto;"><code>GET /my-post HTTP/1.1
Accept: text/markdown

→ Returns Markdown instead of HTML</code></pre>
		</div>

		<!-- Technical Flow -->
		<div class="ta-card">
			<h2><?php esc_html_e( 'Technical Flow', 'third-audience' ); ?></h2>
			<p><?php esc_html_e( 'Here\'s what happens when an AI bot visits your site:', 'third-audience' ); ?></p>

			<div style="background: #f9f9f9; padding: 6px; margin: 6px 0; font-family: monospace; font-size: 13px; line-height: 1.8; border: 1px solid #ddd;">
				<div style="margin-bottom: 15px;">
					<strong style="color: #007aff;">┌─ AI Bot Visit ────────────────────────────────────┐</strong>
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
					<strong style="color: #007aff;">└───────────────────────────────────────────────────┘</strong>
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

			<div class="ta-features-grid">
				<div class="ta-feature-item">
					<div class="ta-feature-icon ta-feature-icon-performance">
						<span class="dashicons dashicons-performance"></span>
					</div>
					<h3><?php esc_html_e( 'Lightning Fast Local Conversion', 'third-audience' ); ?></h3>
					<ul>
						<li><?php esc_html_e( 'All HTML-to-Markdown conversion happens on your server', 'third-audience' ); ?></li>
						<li><?php esc_html_e( 'Zero network latency (1-4ms response times)', 'third-audience' ); ?></li>
						<li><?php esc_html_e( 'No external API dependencies or API keys required', 'third-audience' ); ?></li>
						<li><?php esc_html_e( 'Pre-generation option for instant delivery', 'third-audience' ); ?></li>
					</ul>
				</div>

				<div class="ta-feature-item">
					<div class="ta-feature-icon ta-feature-icon-bot">
						<span class="dashicons dashicons-admin-users"></span>
					</div>
					<h3><?php esc_html_e( 'Comprehensive Bot Detection & Analytics', 'third-audience' ); ?></h3>
					<ul>
						<li><?php esc_html_e( 'Tracks visits from Claude, ChatGPT, Perplexity, Google Gemini, and more', 'third-audience' ); ?></li>
						<li><?php esc_html_e( 'Real-time analytics dashboard showing bot behavior', 'third-audience' ); ?></li>
						<li><?php esc_html_e( 'Bot blocking capabilities with granular control', 'third-audience' ); ?></li>
						<li><?php esc_html_e( 'IP address and country tracking', 'third-audience' ); ?></li>
					</ul>
				</div>

				<div class="ta-feature-item">
					<div class="ta-feature-icon ta-feature-icon-privacy">
						<span class="dashicons dashicons-lock"></span>
					</div>
					<h3><?php esc_html_e( 'Privacy-First Architecture', 'third-audience' ); ?></h3>
					<ul>
						<li><?php esc_html_e( 'Your content never leaves your server', 'third-audience' ); ?></li>
						<li><?php esc_html_e( 'No data sent to external services', 'third-audience' ); ?></li>
						<li><?php esc_html_e( 'Self-contained PHP-based conversion', 'third-audience' ); ?></li>
						<li><?php esc_html_e( 'Enterprise-grade for deployment at scale', 'third-audience' ); ?></li>
					</ul>
				</div>

				<div class="ta-feature-item">
					<div class="ta-feature-icon ta-feature-icon-health">
						<span class="dashicons dashicons-heart"></span>
					</div>
					<h3><?php esc_html_e( 'Enterprise-Grade Health Monitoring', 'third-audience' ); ?></h3>
					<ul>
						<li><?php esc_html_e( 'Real-time system health checks', 'third-audience' ); ?></li>
						<li><?php esc_html_e( 'Automatic library detection', 'third-audience' ); ?></li>
						<li><?php esc_html_e( 'User-friendly error messages for non-technical users', 'third-audience' ); ?></li>
						<li><?php esc_html_e( 'Comprehensive troubleshooting guides', 'third-audience' ); ?></li>
					</ul>
				</div>
			</div>
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
			<div class="ta-credits-icon">
				<span class="dashicons dashicons-heart"></span>
			</div>
			<h2><?php esc_html_e( 'Credits', 'third-audience' ); ?></h2>
			<div class="ta-credits-grid">
				<div class="ta-credit-item">
					<h3><?php esc_html_e( 'Developed by', 'third-audience' ); ?></h3>
					<a href="https://www.spaceotechnologies.com/" target="_blank" rel="noopener"><?php esc_html_e( 'Space-O Technologies', 'third-audience' ); ?></a>
				</div>
				<div class="ta-credit-item">
					<h3><?php esc_html_e( 'Inspired by', 'third-audience' ); ?></h3>
					<a href="https://dri.es/the-third-audience" target="_blank" rel="noopener"><?php esc_html_e( 'Dries Buytaert\'s article "The Third Audience"', 'third-audience' ); ?></a>
				</div>
			</div>
			<p class="ta-credits-message">
				<?php esc_html_e( 'This plugin was created to make the "third audience" concept accessible to all WordPress users. Special thanks to Dries for introducing this important shift in how we think about web content optimization.', 'third-audience' ); ?>
			</p>

			<h3 style="margin: 30px 0 16px 0; color: #1d1d1f; font-size: 18px; border-bottom: 1px solid rgba(0,0,0,0.06); padding-bottom: 16px;">
				<?php esc_html_e( 'Built with Open Source', 'third-audience' ); ?>
			</h3>
			<p style="color: #86868b; margin-bottom: 20px;">
				<?php esc_html_e( 'Third Audience is powered by this amazing open source library:', 'third-audience' ); ?>
			</p>
			<div class="ta-credits-grid">
				<div class="ta-credit-item">
					<h3><?php esc_html_e( 'HTML to Markdown', 'third-audience' ); ?></h3>
					<a href="https://github.com/thephpleague/html-to-markdown" target="_blank" rel="noopener"><?php esc_html_e( 'league/html-to-markdown', 'third-audience' ); ?></a>
					<p style="font-size: 13px; color: #86868b; margin-top: 8px;">
						<?php esc_html_e( 'Fast, clean HTML to Markdown conversion for serving AI-optimized content.', 'third-audience' ); ?>
					</p>
				</div>
			</div>
			<p style="color: #86868b; margin-top: 20px; font-size: 13px;">
				<?php esc_html_e( 'AI citation tracking and referrer parsing is handled by our custom lightweight implementation - zero external dependencies!', 'third-audience' ); ?>
			</p>
		</div>

		<!-- Version History -->
		<div class="ta-card">
			<h2><?php esc_html_e( 'Version History & Changelog', 'third-audience' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Track all updates and improvements to Third Audience.', 'third-audience' ); ?>
			</p>

			<!-- Version 1.1.1 -->
			<div class="ta-version-entry" style="border-left: 4px solid #007aff; padding-left: 20px; margin: 6px 0;">
				<h3 style="margin: 0 0 10px 0; color: #007aff;">
					<?php esc_html_e( 'Version 1.1.1', 'third-audience' ); ?>
					<span style="font-size: 13px; color: #646970; font-weight: normal;">— January 21, 2026</span>
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
			<div class="ta-version-entry" style="border-left: 4px solid #2271b1; padding-left: 20px; margin: 6px 0;">
				<h3 style="margin: 0 0 10px 0; color: #2271b1;">
					<?php esc_html_e( 'Version 1.1.0', 'third-audience' ); ?>
					<span style="font-size: 13px; color: #646970; font-weight: normal;">— January 21, 2026</span>
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
			<div class="ta-version-entry" style="border-left: 4px solid #00a32a; padding-left: 20px; margin: 6px 0;">
				<h3 style="margin: 0 0 10px 0; color: #00a32a;">
					<?php esc_html_e( 'Version 1.0.0', 'third-audience' ); ?>
					<span style="font-size: 13px; color: #646970; font-weight: normal;">— January 16, 2026</span>
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
		<div class="ta-card ta-quick-links">
			<h2><?php esc_html_e( 'Quick Links', 'third-audience' ); ?></h2>
			<div class="ta-links-grid">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-bot-analytics' ) ); ?>" class="ta-link-card">
					<div class="ta-link-icon" style="background: linear-gradient(135deg, #007aff 0%, #0051d5 100%);">
						<span class="dashicons dashicons-chart-bar"></span>
					</div>
					<div class="ta-link-content">
						<strong><?php esc_html_e( 'Bot Analytics', 'third-audience' ); ?></strong>
						<span><?php esc_html_e( 'View bot visits and statistics', 'third-audience' ); ?></span>
					</div>
					<span class="dashicons dashicons-arrow-right-alt2"></span>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=third-audience-system-health' ) ); ?>" class="ta-link-card">
					<div class="ta-link-icon" style="background: linear-gradient(135deg, #34c759 0%, #30a14e 100%);">
						<span class="dashicons dashicons-heart"></span>
					</div>
					<div class="ta-link-content">
						<strong><?php esc_html_e( 'System Health', 'third-audience' ); ?></strong>
						<span><?php esc_html_e( 'Check plugin health status', 'third-audience' ); ?></span>
					</div>
					<span class="dashicons dashicons-arrow-right-alt2"></span>
				</a>
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=third-audience' ) ); ?>" class="ta-link-card">
					<div class="ta-link-icon" style="background: linear-gradient(135deg, #5856d6 0%, #5e5ce6 100%);">
						<span class="dashicons dashicons-admin-settings"></span>
					</div>
					<div class="ta-link-content">
						<strong><?php esc_html_e( 'Settings', 'third-audience' ); ?></strong>
						<span><?php esc_html_e( 'Configure plugin options', 'third-audience' ); ?></span>
					</div>
					<span class="dashicons dashicons-arrow-right-alt2"></span>
				</a>
			</div>
		</div>
	</div>
</div>

<style>
/* Apple-Style About Page - Matching Bot Analytics Design */

/* Container */
.ta-about-page {
	margin: 6px 0;
}

.ta-about-page .wp-heading-inline {
	font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'SF Pro Display', 'Helvetica Neue', Arial, sans-serif;
}

.ta-about-page .description {
	margin-top: 6px;
	font-size: 13px;
	color: #86868b;
	line-height: 1.5;
}

.ta-about-container {
	max-width: 100%;
	margin: 6px 0;
}

/* Card Base - Apple Style */
.ta-card {
	background: #ffffff;
	border: none;
	border-radius: 8px;
	padding: 6px 10px;
	margin-bottom: 10px;
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
	transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.ta-card:hover {
	box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
	transform: translateY(-1px);
}

/* Hero Section - Apple Style */
.ta-hero {
	background: linear-gradient(135deg, #f5f5f7 0%, #e8e8ed 100%);
	border: none;
	padding: 6px 10px;
	position: relative;
	overflow: hidden;
	text-align: center;
}

.ta-hero-icon {
	display: inline-flex;
	width: 38px;
	height: 38px;
	background: linear-gradient(135deg, #007aff 0%, #0051d5 100%);
	border-radius: 8px;
	align-items: center;
	justify-content: center;
	margin-bottom: 10px;
}

.ta-hero-icon .dashicons {
	color: #fff;
	font-size: 13px;
	width: 28px;
	height: 28px;
}

.ta-hero h2 {
	margin: 0 0 12px 0;
	font-size: 13px;
	font-weight: 600;
	color: #1d1d1f;
	letter-spacing: -0.5px;
	font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'SF Pro Display', 'Helvetica Neue', Arial, sans-serif;
}

.ta-lead {
	font-size: 13px;
	line-height: 1.6;
	margin-bottom: 8px;
	font-weight: 500;
	color: #1d1d1f;
}

.ta-hero p {
	color: #86868b;
	line-height: 1.6;
	font-size: 13px;
	margin: 6px auto;
	max-width: 100%;
}

/* Typography - Apple SF Pro Style */
.ta-card h2 {
	margin: 0 0 16px 0;
	font-size: 13px;
	font-weight: 600;
	color: #1d1d1f;
	letter-spacing: -0.4px;
	font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'SF Pro Display', 'Helvetica Neue', Arial, sans-serif;
	padding-bottom: 16px;
	border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

.ta-card h3 {
	margin: 6px 0 12px 0;
	font-size: 13px;
	font-weight: 600;
	color: #1d1d1f;
	letter-spacing: -0.3px;
	font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'SF Pro Display', 'Helvetica Neue', Arial, sans-serif;
}

.ta-card p {
	color: #86868b;
	line-height: 1.6;
	font-size: 13px;
	margin: 6px 0;
}

.ta-card ul,
.ta-card ol {
	margin: 6px 0 12px 24px;
	line-height: 1.7;
}

.ta-card li {
	margin-bottom: 8px;
	color: #86868b;
	font-size: 13px;
}

.ta-card pre {
	background: #f5f5f7 !important;
	border: 1px solid rgba(0, 0, 0, 0.08) !important;
	padding: 6px !important;
	border-radius: 8px !important;
	font-size: 13px;
	line-height: 1.6;
	color: #1d1d1f !important;
	font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
	overflow-x: auto;
}

.ta-card code {
	color: #1d1d1f;
	background: #f5f5f7;
	padding: 3px 6px;
	border-radius: 5px;
	font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
	font-size: 13px;
}

/* Features Grid - Apple Style */
.ta-features-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
	gap: 6px;
	margin-top: 24px;
}

.ta-feature-item {
	background: #f5f5f7;
	border-radius: 8px;
	padding: 6px;
	transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.ta-feature-item:hover {
	background: #ffffff;
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.ta-feature-icon {
	width: 48px;
	height: 48px;
	border-radius: 8px;
	display: flex;
	align-items: center;
	justify-content: center;
	margin-bottom: 10px;
}

.ta-feature-icon .dashicons {
	color: #fff;
	font-size: 13px;
	width: 28px;
	height: 28px;
}

.ta-feature-icon-performance {
	background: linear-gradient(135deg, #ff9500 0%, #ff6b00 100%);
}

.ta-feature-icon-bot {
	background: linear-gradient(135deg, #007aff 0%, #0051d5 100%);
}

.ta-feature-icon-privacy {
	background: linear-gradient(135deg, #34c759 0%, #30a14e 100%);
}

.ta-feature-icon-health {
	background: linear-gradient(135deg, #ff3b30 0%, #d70015 100%);
}

.ta-feature-item h3 {
	margin: 0 0 12px 0;
	font-size: 13px;
}

.ta-feature-item ul {
	margin-left: 20px;
}

/* Credits Section - Apple Style */
.ta-credits {
	background: #f5f5f7;
	text-align: center;
	position: relative;
}

.ta-credits-icon {
	width: 56px;
	height: 56px;
	background: linear-gradient(135deg, #ff3b30 0%, #d70015 100%);
	border-radius: 14px;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	margin-bottom: 20px;
}

.ta-credits-icon .dashicons {
	color: #fff;
	font-size: 32px;
	width: 32px;
	height: 32px;
}

.ta-credits-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 6px;
	margin: 6px 0;
}

.ta-credit-item h3 {
	font-size: 13px;
	color: #86868b;
	margin: 0 0 8px 0;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	font-weight: 500;
}

.ta-credit-item a {
	color: #007aff;
	text-decoration: none;
	font-size: 13px;
	font-weight: 600;
	transition: opacity 0.2s;
}

.ta-credit-item a:hover {
	opacity: 0.7;
}

.ta-credits-message {
	color: #86868b !important;
	font-size: 13px !important;
	line-height: 1.6 !important;
	margin-top: 20px !important;
}

/* Quick Links - Apple Style */
.ta-quick-links {
	background: #ffffff;
}

.ta-links-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
	gap: 6px;
	margin-top: 20px;
}

.ta-link-card {
	display: flex;
	align-items: center;
	gap: 6px;
	padding: 6px;
	background: #f5f5f7;
	border-radius: 8px;
	text-decoration: none;
	transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.ta-link-card:hover {
	background: #ffffff;
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.ta-link-icon {
	width: 48px;
	height: 48px;
	border-radius: 8px;
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
}

.ta-link-icon .dashicons {
	color: #fff;
	font-size: 13px;
	width: 28px;
	height: 28px;
}

.ta-link-content {
	flex: 1;
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.ta-link-content strong {
	font-size: 13px;
	font-weight: 600;
	color: #1d1d1f;
	letter-spacing: -0.2px;
}

.ta-link-content span {
	font-size: 13px;
	color: #86868b;
	font-weight: 400;
}

.ta-link-card .dashicons-arrow-right-alt2 {
	color: #86868b;
	font-size: 13px;
	width: 20px;
	height: 20px;
	flex-shrink: 0;
	transition: transform 0.2s;
}

.ta-link-card:hover .dashicons-arrow-right-alt2 {
	transform: translateX(4px);
}

/* Version History */
.ta-version-entry {
	border-left: 4px solid #007aff;
	padding-left: 20px;
	margin: 6px 0;
	transition: all 0.2s;
}

.ta-version-entry:hover {
	border-left-color: #0051d5;
}

.ta-version-entry h3 {
	margin: 0 0 12px 0;
	color: #1d1d1f;
	font-size: 13px;
	font-weight: 600;
}

.ta-version-entry h3 span {
	font-size: 13px;
	color: #86868b;
	font-weight: 400;
}

.ta-version-entry p strong {
	color: #1d1d1f;
}

.ta-version-entry ul {
	margin-top: 8px;
}

/* Buttons - Apple Style */
.button-primary {
	background: #007aff !important;
	border: none !important;
	border-radius: 8px !important;
	box-shadow: 0 2px 4px rgba(0, 122, 255, 0.3) !important;
	font-weight: 500 !important;
	padding: 6px 20px !important;
	height: auto !important;
	line-height: 1.4 !important;
	transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.button-primary:hover {
	background: #0051d5 !important;
	box-shadow: 0 4px 12px rgba(0, 122, 255, 0.4) !important;
	transform: translateY(-1px);
}

.button-secondary {
	border: 1px solid #d1d1d6 !important;
	border-radius: 8px !important;
	color: #1d1d1f !important;
	background: #ffffff !important;
	font-weight: 500 !important;
	padding: 6px 20px !important;
	height: auto !important;
	line-height: 1.4 !important;
	transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.button-secondary:hover {
	background: #f5f5f7 !important;
	border-color: #007aff !important;
	transform: translateY(-1px);
}

/* Technical flow diagram */
.ta-card div[style*="font-family: monospace"] {
	background: #f5f5f7 !important;
	border: 1px solid rgba(0, 0, 0, 0.08) !important;
	border-radius: 8px !important;
	font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace !important;
}

/* Responsive Design */
@media (max-width: 782px) {
	.ta-card {
		padding: 6px 10px;
		border-radius: 8px;
	}

	.ta-hero {
		padding: 32px 20px;
	}

	.ta-hero h2 {
		font-size: 13px;
	}

	.ta-lead {
		font-size: 13px;
	}

	.ta-features-grid {
		grid-template-columns: 1fr;
	}

	.ta-links-grid {
		grid-template-columns: 1fr;
	}

	.ta-credits-grid {
		grid-template-columns: 1fr;
	}
}
</style>
