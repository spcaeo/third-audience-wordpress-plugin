# About Third Audience

## Serving the Third Audience

For two decades, we built websites for two audiences: **humans** and **search engines**. Today, there's a third audience that's rapidly growing: **AI agents and crawlers**.

AI systems like Claude (Anthropic), ChatGPT (OpenAI), Perplexity, and Google Gemini are actively consuming web content to answer questions, generate summaries, and provide recommendations. However, most websites aren't optimized for these AI agents—they receive the same HTML designed for human browsers, not the clean, structured data AI systems prefer.

## What is Third Audience?

**Third Audience** is a WordPress plugin that automatically serves AI-optimized Markdown versions of your content to AI crawlers, while humans continue to see your beautiful website design.

Think of it as **SEO for the AI era**—just as you optimize for Google, you can now optimize for Claude, ChatGPT, Perplexity, and other AI agents that are becoming the primary way people discover and consume content.

## How It Works

Third Audience intelligently detects when AI bots visit your site and automatically serves them clean Markdown versions of your content:

1. **AI crawlers request `.md` URLs** (like `yoursite.com/post-name.md`)
2. **Plugin intercepts the request** and identifies the bot
3. **Converts HTML to Markdown locally** on your server (no external APIs!)
4. **Caches for instant delivery** (1-4ms response times)
5. **Tracks analytics** so you know which AI bots are visiting

## Key Features

### 🚀 **Lightning Fast Local Conversion**
- All HTML-to-Markdown conversion happens on your server
- Zero network latency (1-4ms response times)
- No external API dependencies or API keys required
- Pre-generation option for instant delivery

### 🤖 **Comprehensive Bot Detection & Analytics**
- Tracks visits from Claude, ChatGPT, Perplexity, Google Gemini, and more
- Real-time analytics dashboard showing bot behavior
- Bot blocking capabilities with granular control
- IP address and country tracking

### 🔒 **Privacy-First Architecture**
- Your content never leaves your server
- No data sent to external services
- Self-contained PHP-based conversion
- Enterprise-grade for deployment at scale

### 🎯 **Enterprise-Grade Health Monitoring**
- Real-time system health checks
- Automatic library detection
- User-friendly error messages for non-technical users
- Comprehensive troubleshooting guides

### ⚙️ **Flexible Configuration**
- Choose which post types to optimize (posts, pages, custom types)
- Content negotiation via `Accept: text/markdown` headers
- Discovery tags help AI crawlers find your content
- Configurable cache duration

### 📊 **Rich Markdown Output**
- YAML frontmatter with metadata (title, date, author, categories, tags)
- Clean, readable Markdown formatting
- Featured images included
- Proper heading hierarchy and code blocks

## Why It Matters

AI agents are becoming the dominant way people discover and consume content. When someone asks Claude, ChatGPT, or Perplexity a question, these systems search the web and synthesize answers from multiple sources.

**By optimizing for AI agents, you ensure:**
- ✅ Your content is accurately represented in AI-generated responses
- ✅ AI systems can easily parse and understand your content
- ✅ You're positioned for the future of content discovery
- ✅ Your expertise reaches audiences through AI-powered recommendations

This is **Generative Engine Optimization (GEO)**—the next evolution of SEO.

## Technical Architecture

### Version 2.0: Local Conversion
Third Audience v2.0 uses a fully self-contained architecture:

- **PHP Library:** `league/html-to-markdown` for high-quality conversion
- **Dependency Management:** Composer for clean dependency handling
- **Content Extraction:** DOMDocument for parsing and cleaning HTML
- **Caching Strategy:** Two-tier caching (pre-generated + transient)
- **Performance:** Sub-5ms conversion, 1ms cache hits

### No External Dependencies
Unlike other solutions, Third Audience doesn't rely on:
- ❌ External API services
- ❌ Cloudflare Workers
- ❌ Third-party conversion tools
- ❌ API keys or authentication

Everything runs locally on your WordPress server—fast, private, and reliable.

## Installation

1. Upload the plugin to your WordPress site
2. Run `composer install --no-dev` in the plugin directory (or download the pre-packaged version)
3. Activate the plugin
4. Visit **Bot Analytics → System Health** to verify everything is working

That's it! The plugin works automatically with sensible defaults.

## Requirements

- **PHP:** 7.4 or higher (8.0+ recommended)
- **WordPress:** 5.8 or higher
- **Composer:** For installing dependencies (or use pre-packaged version)
- **DOMDocument:** PHP extension (usually included by default)

## Use Cases

### Content Publishers
Ensure your articles are accurately cited and represented in AI-generated summaries.

### Documentation Sites
Help AI assistants accurately answer technical questions about your products.

### E-commerce
Make your product descriptions easily discoverable through AI shopping assistants.

### Blogs & Portfolios
Increase your reach as AI systems recommend your expertise to users.

### News & Media
Position your reporting to be included in AI-powered news summaries.

## Analytics & Insights

Third Audience provides detailed analytics about AI bot behavior:

- **Total bot visits** and trends over time
- **Bot distribution** (which AI services are visiting)
- **Top crawled pages** and their performance
- **Cache hit rates** and response times
- **Geographic data** (IP addresses and countries)
- **Response size tracking** for bandwidth monitoring

## Security & Privacy

- **No data collection:** We don't collect or transmit any data about your site
- **Self-hosted:** All processing happens on your server
- **Bot verification:** Proper User-Agent detection and validation
- **Rate limiting:** Built-in protection against excessive requests
- **Bot blocking:** Granular control over which bots can access your content

## Roadmap

Future enhancements planned:
- Structured data output (JSON-LD)
- Custom Markdown templates
- A/B testing for AI optimization
- Integration with analytics platforms
- Automatic content enhancement suggestions

## Support

For questions, issues, or feature requests:
- **Website:** https://labsmedia.com
- **Documentation:** Visit the System Health page in your WordPress admin
- **GitHub:** [Report issues and contribute](https://github.com/third-audience/wordpress-plugin)

---

## Credits

**Developed by:** [Labs Media](https://labsmedia.com)
**Inspired by:** Dries Buytaert's article ["The Third Audience"](https://dri.es/the-third-audience)

This plugin was created to make the "third audience" concept accessible to all WordPress users. Special thanks to Dries for introducing this important shift in how we think about web content optimization.

---

## License

GPL-3.0-or-later

Copyright (c) 2026 Labs Media

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
