# Third Audience — WordPress Plugin

**Serve AI-optimized Markdown to LLM crawlers. Track who's citing your content. Get discovered by ChatGPT, Perplexity, Claude, and Gemini.**

![Version](https://img.shields.io/badge/version-3.5.2-blue)
![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue)
![License](https://img.shields.io/badge/license-GPL%20v2-green)

---

## What Is Third Audience?

For two decades, websites were built for two audiences: **humans** and **search engines**. Today there is a third audience rapidly growing: **AI agents and LLM crawlers**.

AI systems like ChatGPT, Perplexity, Claude, and Google Gemini actively crawl the web to answer questions, generate summaries, and provide recommendations. Most websites serve these bots the same HTML built for humans — full of navigation menus, scripts, ads, and layout markup that adds noise and reduces accuracy.

**Third Audience** automatically serves clean, structured Markdown versions of your content to AI crawlers, while humans continue to see your normal website. Think of it as **SEO for the AI era** — Generative Engine Optimization (GEO).

---

## How It Works

```
AI Bot visits yoursite.com/page-name.md
        │
        ▼
Plugin intercepts the request
        │
        ▼
Bot Detection Pipeline
  ├── Known Pattern Detector (database lookup)
  └── Heuristic Detector (user agent rules)
        │
        ▼
HTML → Markdown conversion (local, no external APIs)
        │
        ▼
Cached Markdown served (1–4ms response time)
        │
        ▼
Visit recorded in analytics dashboard
```

1. AI crawlers request `.md` URLs (e.g. `yoursite.com/my-post.md`)
2. Plugin intercepts and identifies the bot
3. Converts your HTML to clean Markdown **locally on your server**
4. Caches the result for instant future delivery
5. Tracks the visit in your analytics dashboard

---

## Features

### Local HTML to Markdown Conversion
- Zero external API calls — everything runs on your server
- Uses `league/html-to-markdown` PHP library
- Response times of 1–4ms from cache
- YAML frontmatter with title, date, author, categories, tags
- Pre-generation option to warm cache before bots arrive

### Bot Detection & Analytics
- Detects ChatGPT (GPTBot), Claude (ClaudeBot), Perplexity, Google Extended, Bing, Facebook, and Cohere bots
- Multi-layer detection pipeline: pattern matching + heuristic rules
- Real-time analytics dashboard — visits, trends, top pages
- Country tracking, IP geolocation, session tracking
- Browser & device tracking for citation clicks
- HTTP status code tracking to identify broken citations (404s, 500s)

### LLM Traffic Dashboard
- See which AI platforms are citing your content
- Filter by date, platform, page, and request type
- CSV and JSON export
- Weekly and daily trend charts

### Citation Alerts
- Get notified on citation from a AI platform

### Multi-tier Caching
- Memory cache (static variables)
- Object cache (Redis/Memcached if available)
- Transient cache (database fallback)
- Tag-based cache invalidation
- Cache browser UI to view and manage cached entries

### Headless WordPress Support
- AJAX-first architecture — works even when REST API is blocked by security plugins
- Auto-detects headless environments (Next.js, Gatsby, Nuxt)
- Client-side citation tracker (`citation-tracker.js`) captures real browser user agents
- Request type classification: `html_page`, `rsc_prefetch`, `js_fallback`, `api_call`

### Google Analytics 4 Integration
- Sends bot visit data to GA4 via Measurement Protocol
- Connection testing from admin panel
- Optional — works without GA4

### System Health & Auto-Repair
- Real-time health check dashboard
- Automatic database schema migration
- Daily health check cron with auto-repair
- Security plugin auto-whitelisting (Wordfence, Solid Security, Sucuri, AIOS)

---

## Requirements

| Requirement | Minimum | Recommended |
|---|---|---|
| PHP | 7.4 | 8.0+ |
| WordPress | 5.8 | Latest |
| PHP Extension | DOMDocument | (usually included by default) |
| Composer | Required for dev install | — |

---

## Installation

### Option 1 — Upload ZIP (Easiest)
1. Download the latest release ZIP from the [Releases page](../../releases)
2. Go to **WordPress Admin → Plugins → Add New → Upload Plugin**
3. Upload the ZIP and click **Activate**

### Option 2 — Composer
```bash
cd wp-content/plugins/
git clone https://github.com/spcaeo/third-audience-wordpress-plugin.git third-audience
cd third-audience/third-audience
composer install --no-dev
```
Then activate the plugin from **WordPress Admin → Plugins**.

### Option 3 — Manual (Development)
```bash
cd wp-content/plugins/
git clone https://github.com/spcaeo/third-audience-wordpress-plugin.git third-audience
cd third-audience/third-audience
composer install
```

After activation, visit **Bot Analytics → System Health** to verify everything is working correctly.

---

## Configuration

### Basic Setup
1. Activate the plugin
2. Go to **WordPress Admin → Bot Analytics → Settings**
3. Choose which post types to optimize (Posts, Pages, custom types)
4. Enable **Discovery Tags** — adds `<link>` tags so AI crawlers can find your `.md` URLs
5. Set **Cache TTL** (default: 24 hours)

### Headless WordPress
If you run a headless frontend (Next.js, Gatsby, etc.):
1. Go to **Bot Analytics → Settings → Headless Setup**
2. The plugin auto-detects your environment
3. Add the citation tracker script to your frontend — see [HEADLESS-SETUP.md](third-audience/HEADLESS-SETUP.md)

### Google Analytics 4 (Optional)
1. Go to **Bot Analytics → Settings → GA4 Integration**
2. Enter your GA4 Property ID and Measurement Protocol Secret
3. Click **Test Connection** to verify

### Email Digest (Optional)
1. Go to **Bot Analytics → Email Digest**
2. Configure SMTP settings and recipient email
3. Choose daily digest frequency

---

## REST API

**Namespace:** `third-audience/v1`

| Endpoint | Method | Auth | Description |
|---|---|---|---|
| `/health` | GET | Public | System health check |
| `/analytics` | POST | Nonce | Submit bot visit data |
| `/diagnostics` | GET | Admin only | Detailed system diagnostics |

The plugin auto-falls back to AJAX endpoints if REST API is blocked by security plugins.

---

## Hooks & Filters

### Actions

```php
// Fires after a bot visit is tracked
do_action( 'ta_bot_visit_tracked', $post_id, $bot_name, $traffic_type );

// Fires when cache is cleared for a post
do_action( 'ta_cache_cleared', $post_id );

// Fires on daily health check cron
do_action( 'ta_daily_health_check' );

// Fires on cache warm cron
do_action( 'ta_cache_warm_cron' );
```

### Filters

```php
// Filter the markdown content before it is cached and served
add_filter( 'ta_markdown_content', function( $markdown, $post_id ) {
    // Modify $markdown here
    return $markdown;
}, 10, 2 );

// Filter which post types are enabled for markdown serving
add_filter( 'ta_enabled_post_types', function( $post_types ) {
    $post_types[] = 'my_custom_type';
    return $post_types;
} );

// Filter the YAML frontmatter before it is added to markdown
add_filter( 'ta_frontmatter', function( $frontmatter, $post_id ) {
    $frontmatter['custom_field'] = get_post_meta( $post_id, 'my_field', true );
    return $frontmatter;
}, 10, 2 );
```

---

## Database Tables

| Table | Purpose |
|---|---|
| `wp_ta_bot_analytics` | All bot visits and citation tracking (main table) |
| `wp_ta_citation_alerts` | Citation alert history |
| `wp_ta_bot_patterns` | External bot pattern database |
| `wp_ta_competitor_benchmarking` | Competitor benchmark results |
| `wp_ta_unknown_bots` | Queue for unknown bot auto-learning |

The plugin handles all database migrations automatically on activation and upgrade.

---

## FAQ

**Does this slow down my website for human visitors?**
No. The plugin only intercepts requests for `.md` URLs or requests with `Accept: text/markdown` headers. Human visitors are completely unaffected.

**Does my content get sent to any external service?**
No. All HTML-to-Markdown conversion happens locally on your server using the `league/html-to-markdown` PHP library. Your content never leaves your server.

**Does it work with security plugins like Wordfence or Solid Security?**
Yes. The plugin auto-detects blocked REST APIs and switches to AJAX endpoints automatically. It also whitelists itself in common security plugins on activation.

**Does it work with headless WordPress (Next.js, Gatsby)?**
Yes. The headless setup includes a client-side JavaScript tracker and AJAX-first architecture that works regardless of how your frontend is built.

**Which AI bots are detected?**
GPTBot (ChatGPT), ClaudeBot (Anthropic), PerplexityBot, Google Extended, Bingbot, FacebookBot, Cohere AI, and more via the heuristic detection pipeline.

**Can I block specific bots?**
Yes. Go to **Bot Analytics → Bot Management** to block or allow specific bots granularly.

**What happens when I uninstall the plugin?**
All plugin data is removed cleanly: database tables, options, transients, log files, and cron jobs are all deleted on uninstall.

---

## Contributing

Contributions are welcome. To get started:

1. Fork the repository
2. Clone your fork locally
3. Install dependencies: `composer install` inside the `third-audience/` folder
4. Make your changes on a new branch
5. Run tests: `vendor/bin/phpunit` inside the `third-audience/` folder
6. Submit a pull request

Please follow WordPress coding standards. For major changes, open an issue first to discuss what you would like to change.

### Running Tests

```bash
cd third-audience
vendor/bin/phpunit
```

Test files are in the `tests/` directory. See [TESTING-GUIDE.md](third-audience/TESTING-GUIDE.md) for the full testing guide.

---

## Changelog

See [CHANGELOG.md](third-audience/CHANGELOG.md) for the full version history.

**Latest: v3.5.2**
- Dashboard enhancements, session dedup, headless data fixes
- Client user agent tracking for real browser data
- HTTP status code tracking to identify broken citations
- Request type classification (html_page, rsc_prefetch, js_fallback, api_call)

---

## Credits

**Author:** [Labs Media](https://labsmedia.com)

**Support:** Rahul Tank — Sr. WordPress Headless Engineer
📧 rahul.tank@spaceo.in

---

## License

This plugin is licensed under the [GNU General Public License v2.0 or later](https://www.gnu.org/licenses/gpl-2.0.html).

Copyright (c) 2026 [Labs Media](https://labsmedia.com)

You are free to use, modify, and distribute this plugin under the terms of the GPL v2 license.
