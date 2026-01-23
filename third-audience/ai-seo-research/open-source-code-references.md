# Open Source Code References for AI Citation Tracking

**Research Date**: 2026-01-21
**Purpose**: Reusable code, libraries, and implementations for tracking AI bot traffic and citations

---

## 🔥 TOP PRIORITY - Direct WordPress Plugin Code

### 1. **LLMS Central AI Bot Tracker** - WordPress Plugin (OPEN SOURCE!)
**GitHub**: [https://github.com/llmscentral/llms-central-ai-bot-tracker](https://github.com/llmscentral/llms-central-ai-bot-tracker)

**What it does**:
- Tracks AI crawlers like ChatGPT, Claude, and Gemini
- Detects bot visits, logs URLs
- Provides dashboard insights
- Lightweight and privacy-focused

**Key Features We Can Reuse**:
```php
// Bot detection patterns
// Database schema for bot tracking
// Admin dashboard implementation
// Privacy-focused local storage
```

**License**: Check GitHub repository
**Status**: Active WordPress plugin with source code available

---

## 🎯 CRITICAL LIBRARY - Referrer URL Parser

### 2. **Snowplow Referer-Parser** (Multi-Language)
**Main Repo**: [https://github.com/snowplow-referer-parser/referer-parser](https://github.com/snowplow-referer-parser/referer-parser)

**PHP Implementation**:
[https://github.com/snowplow-referer-parser/php-referer-parser](https://github.com/snowplow-referer-parser/php-referer-parser)

**Node.js Implementation**:
[https://github.com/snowplow-referer-parser/nodejs-referer-parser](https://github.com/snowplow-referer-parser/nodejs-referer-parser)

**What it does**:
- Extracts marketing attribution data from referrer URLs
- **Extracts search terms from search engine URLs**
- Identifies referrer source (search engine, social media, etc.)

**Install (PHP)**:
```bash
composer require snowplow/referer-parser
```

**Usage Example (PHP)**:
```php
use Snowplow\RefererParser\Parser;

$parser = new Parser();
$referer = 'https://www.google.com/search?q=wordpress+ai+bot';

$result = $parser->parse(
    $referer,
    'http://yoursite.com/page'
);

// Result contains:
// - source: 'Google'
// - medium: 'search'
// - term: 'wordpress ai bot'  ← THIS IS THE SEARCH QUERY!
```

**Why This is Perfect**:
✅ Already extracts search queries from referrer URLs
✅ Multi-language support (PHP + Node.js)
✅ Actively maintained
✅ Production-ready
✅ Can extend with AI platform patterns

**How to Extend for AI Platforms**:
Add patterns for:
- `perplexity.ai` → extract from `?q=` parameter
- `chat.openai.com` → detect ChatGPT referrer
- `claude.ai` → detect Claude referrer
- `gemini.google.com` → detect Gemini referrer

---

## 📊 Referrer Parsing Libraries

### 3. **Segment.io Inbound** (Node.js)
**GitHub**: [https://github.com/segmentio/inbound](https://github.com/segmentio/inbound)

**What it does**:
- URL and referrer parsing library for Node.js
- Express.js middleware integration
- Extracts referrer source and campaign data

**Usage Example**:
```javascript
var inbound = require('inbound');

var ref = inbound.referrer.parse('http://google.com/search?q=test');
// { source: 'Google', medium: 'search', term: 'test' }
```

### 4. **query-string** by sindresorhus (JavaScript)
**GitHub**: [https://github.com/sindresorhus/query-string](https://github.com/sindresorhus/query-string)

**What it does**:
- Parse and stringify URL query strings
- Extract parameters from URLs

**Usage Example**:
```javascript
const queryString = require('query-string');

const parsed = queryString.parse('?q=wordpress+ai&source=chatgpt');
console.log(parsed);
// { q: 'wordpress ai', source: 'chatgpt' }

// Extract from full URL
const url = 'https://perplexity.ai/search?q=best+wordpress+cache';
const params = queryString.parseUrl(url);
console.log(params.query.q);
// 'best wordpress cache'
```

---

## 🤖 User Agent & Bot Detection

### 5. **WhichBrowser/Parser-PHP**
**GitHub**: [https://github.com/WhichBrowser/Parser-PHP](https://github.com/WhichBrowser/Parser-PHP)

**What it does**:
- Comprehensive user agent parser for PHP
- **Bot detection capabilities**
- Detects browser, OS, device type

**Install**:
```bash
composer require whichbrowser/parser
```

**Usage Example**:
```php
use WhichBrowser\Parser;

$parser = new Parser($_SERVER['HTTP_USER_AGENT']);

if ($parser->isBot()) {
    echo "Bot detected: " . $parser->browser->name;
    // e.g., "Bot detected: GPTBot"
}
```

**Bot Detection Patterns**:
- Includes patterns for major bots
- Can be extended with AI bot patterns
- Returns bot name, type, version

### 6. **Bottica** - Bot Traffic Verification (Python)
**GitHub**: [https://github.com/JohnPaton/bottica](https://github.com/JohnPaton/bottica)

**What it does**:
- Open source bot & crawler traffic verification
- DNS lookup-based verification
- Can verify if bot is legitimate

**Concept We Can Apply**:
```python
# Verify bot by reverse DNS lookup
# Example: GPTBot claims to be from OpenAI
# Verify IP address actually belongs to OpenAI's network
```

---

## 🔍 AI Platform Referrer Detection Patterns

### Known AI Platform Referrer URLs

Based on research, here are the referrer patterns to detect:

```php
// AI Platform Referrer Patterns
$ai_platforms = [
    // ChatGPT
    'chat.openai.com' => [
        'name' => 'ChatGPT',
        'query_param' => null, // Uses UTM parameters instead
    ],
    'chatgpt.com' => [
        'name' => 'ChatGPT Search',
        'query_param' => null, // Auto-adds utm_source=chatgpt.com
    ],

    // Perplexity (BEST for query extraction)
    'perplexity.ai' => [
        'name' => 'Perplexity',
        'query_param' => 'q', // Search query in ?q= parameter
    ],
    'www.perplexity.ai' => [
        'name' => 'Perplexity',
        'query_param' => 'q',
    ],

    // Claude
    'claude.ai' => [
        'name' => 'Claude',
        'query_param' => null,
    ],

    // Gemini
    'gemini.google.com' => [
        'name' => 'Gemini',
        'query_param' => null,
    ],
    'bard.google.com' => [
        'name' => 'Bard (Gemini)',
        'query_param' => null,
    ],

    // Copilot
    'copilot.microsoft.com' => [
        'name' => 'Copilot',
        'query_param' => null,
    ],
    'edgepilot.com' => [
        'name' => 'Edge Copilot',
        'query_param' => null,
    ],

    // Others
    'you.com' => [
        'name' => 'You.com',
        'query_param' => 'q',
    ],
    'search.brave.com' => [
        'name' => 'Brave Search',
        'query_param' => 'q',
    ],
    'nimble.ai' => [
        'name' => 'Nimble AI',
        'query_param' => null,
    ],
    'iask.ai' => [
        'name' => 'iAsk AI',
        'query_param' => null,
    ],
];
```

### AI Bot User Agent Patterns

```php
// AI Bot User Agents (from Dark Visitors research)
$ai_bot_patterns = [
    // ChatGPT
    '/ChatGPT-User/i' => 'ChatGPT Browser',
    '/GPTBot/i' => 'GPTBot (OpenAI Crawler)',

    // Perplexity
    '/PerplexityBot/i' => 'PerplexityBot',
    '/Perplexity\\/1\\.0/i' => 'Perplexity Assistant',

    // Claude
    '/ClaudeBot/i' => 'ClaudeBot (Anthropic)',
    '/Claude-Web/i' => 'Claude Web Browser',

    // Gemini
    '/Google-Extended/i' => 'Google Gemini',
    '/Googlebot/i' => 'Googlebot (may include Gemini)',

    // Microsoft
    '/BingBot/i' => 'BingBot (Copilot)',

    // Others
    '/Amazonbot/i' => 'Amazonbot',
    '/meta-externalagent/i' => 'Meta AI',
    '/anthropic-ai/i' => 'Anthropic AI',
];
```

---

## 💡 Implementation Strategy Using Open Source Code

### Step 1: Use Snowplow Referer-Parser (PHP)

**Install**:
```bash
cd third-audience
composer require snowplow/referer-parser
```

**Implementation**:
```php
<?php
use Snowplow\RefererParser\Parser;

class TA_AI_Citation_Tracker {

    private $referer_parser;

    public function __construct() {
        $this->referer_parser = new Parser();
    }

    public function detect_ai_citation_traffic() {
        $referer = $_SERVER['HTTP_REFERER'] ?? null;

        if (!$referer) {
            return false;
        }

        // Parse referrer
        $parsed = $this->referer_parser->parse($referer, get_permalink());

        // Check if it's from an AI platform
        $ai_platform = $this->identify_ai_platform($referer);

        if ($ai_platform) {
            return [
                'platform' => $ai_platform['name'],
                'referer' => $referer,
                'search_query' => $this->extract_search_query($referer, $ai_platform),
                'source' => $parsed['source'] ?? 'Unknown',
                'medium' => $parsed['medium'] ?? 'referral',
            ];
        }

        return false;
    }

    private function identify_ai_platform($referer) {
        $patterns = [
            'perplexity.ai' => ['name' => 'Perplexity', 'query_param' => 'q'],
            'chat.openai.com' => ['name' => 'ChatGPT', 'query_param' => null],
            'chatgpt.com' => ['name' => 'ChatGPT Search', 'query_param' => null],
            'claude.ai' => ['name' => 'Claude', 'query_param' => null],
            'gemini.google.com' => ['name' => 'Gemini', 'query_param' => null],
        ];

        foreach ($patterns as $domain => $config) {
            if (strpos($referer, $domain) !== false) {
                return $config;
            }
        }

        return null;
    }

    private function extract_search_query($referer, $platform_config) {
        if (!$platform_config['query_param']) {
            return null;
        }

        // Parse URL
        $parsed_url = parse_url($referer);
        if (!isset($parsed_url['query'])) {
            return null;
        }

        // Extract query parameter
        parse_str($parsed_url['query'], $params);
        return $params[$platform_config['query_param']] ?? null;
    }
}
```

### Step 2: Database Schema

**Extend existing `wp_ta_bot_analytics` table**:
```sql
ALTER TABLE wp_ta_bot_analytics
ADD COLUMN traffic_type varchar(20) DEFAULT 'bot_crawl'
    COMMENT 'bot_crawl or citation_click',
ADD COLUMN ai_platform varchar(50) DEFAULT NULL
    COMMENT 'ChatGPT, Perplexity, Claude, etc.',
ADD COLUMN search_query text DEFAULT NULL
    COMMENT 'Extracted search query from referrer',
ADD COLUMN referer_source varchar(100) DEFAULT NULL
    COMMENT 'Parsed source from Snowplow',
ADD COLUMN referer_medium varchar(50) DEFAULT NULL
    COMMENT 'Parsed medium from Snowplow';
```

### Step 3: Track Citation Traffic

**Hook into WordPress request**:
```php
add_action('template_redirect', function() {
    $tracker = new TA_AI_Citation_Tracker();

    // Check for AI citation traffic
    $citation_data = $tracker->detect_ai_citation_traffic();

    if ($citation_data) {
        // Log to database
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'ta_bot_analytics',
            [
                'traffic_type' => 'citation_click',
                'ai_platform' => $citation_data['platform'],
                'search_query' => $citation_data['search_query'],
                'referer' => $citation_data['referer'],
                'referer_source' => $citation_data['source'],
                'referer_medium' => $citation_data['medium'],
                'url' => get_permalink(),
                'post_id' => get_the_ID(),
                'visit_timestamp' => current_time('mysql'),
                // ... other fields
            ]
        );
    }
});
```

---

## 🎨 Admin Dashboard Code References

### WordPress Plugin Dashboard Examples

**From LLMS Central AI Bot Tracker**:
- Check their GitHub repo for dashboard implementation
- WP Admin table rendering
- Chart.js integration for graphs
- Filter/search UI

**Best Practices from Open Source Plugins**:
1. Use WordPress `WP_List_Table` class for data tables
2. Chart.js for visualizations
3. WordPress Admin UI components (metaboxes, panels)
4. AJAX for real-time updates

---

## 📦 Dependencies to Install

### For PHP Implementation:

**Composer packages**:
```json
{
    "require": {
        "snowplow/referer-parser": "^0.2",
        "whichbrowser/parser": "^2.0"
    }
}
```

**Install**:
```bash
cd third-audience
composer require snowplow/referer-parser
composer require whichbrowser/parser
```

### For JavaScript/Node.js (if needed):

**NPM packages**:
```json
{
    "dependencies": {
        "query-string": "^7.0.0",
        "inbound": "^0.2.0"
    }
}
```

---

## 🔗 All Source Links

### WordPress Plugins (Open Source)
- [LLMS Central AI Bot Tracker](https://github.com/llmscentral/llms-central-ai-bot-tracker)
- [GPTrends Agent Analytics](https://wordpress.org/plugins/gptrends-agent-analytics/)
- [AlmaWeb AI Visitor Analytics](https://wordpress.org/plugins/almaweb-ai-visitor-analytics/)
- [LLM Bot Tracker by Hueston](https://wordpress.org/plugins/llm-bot-tracker-by-hueston/)
- [Dark Visitors](https://wordpress.org/plugins/dark-visitors/)

### Referrer Parsing Libraries
- [Snowplow Referer-Parser (Main)](https://github.com/snowplow-referer-parser/referer-parser)
- [Snowplow Referer-Parser (PHP)](https://github.com/snowplow-referer-parser/php-referer-parser)
- [Snowplow Referer-Parser (Node.js)](https://github.com/snowplow-referer-parser/nodejs-referer-parser)
- [Segment.io Inbound (Node.js)](https://github.com/segmentio/inbound)
- [query-string by sindresorhus](https://github.com/sindresorhus/query-string)

### Bot Detection Libraries
- [WhichBrowser/Parser-PHP](https://github.com/WhichBrowser/Parser-PHP)
- [Bottica - Bot Verification (Python)](https://github.com/JohnPaton/bottica)

### Research Resources
- [Dark Visitors - PerplexityBot User Agent](https://darkvisitors.com/agents/perplexitybot)
- [Perplexity Crawlers Documentation](https://docs.perplexity.ai/guides/bots)
- [List of Top AI Search Crawlers + User Agents](https://momenticmarketing.com/blog/ai-search-crawlers-bots)

---

## ✅ Recommended Implementation Path

### Phase 1: Basic Citation Tracking (Week 1)
1. Install Snowplow Referer-Parser via Composer
2. Create `TA_AI_Citation_Tracker` class
3. Add database columns for citation tracking
4. Hook into `template_redirect` to detect AI referrers
5. Log citation traffic to database

### Phase 2: Search Query Extraction (Week 2)
1. Implement query parameter extraction for Perplexity
2. Handle UTM parameters for ChatGPT
3. Test with real AI platform referrers
4. Validate data accuracy

### Phase 3: Admin Dashboard (Week 3)
1. Create "AI Citations" admin menu
2. Display citation traffic table
3. Show extracted search queries
4. Add charts for trends

### Phase 4: Content Insights (Week 4)
1. Citation rate calculator (clicks / crawls)
2. Most cited content report
3. AI platform comparison
4. Search query trends

---

## 🚀 Quick Start: Install Dependencies

```bash
cd /Users/rakesh/Desktop/Projects/third-audience-jeel/third-audience

# Install Snowplow Referer-Parser
composer require snowplow/referer-parser

# Optional: Install bot detection
composer require whichbrowser/parser

# Test installation
php -r "require 'vendor/autoload.php'; use Snowplow\RefererParser\Parser; echo 'Snowplow installed successfully';"
```

---

## 💡 Key Takeaways

1. **Don't Reinvent the Wheel**: Snowplow Referer-Parser already extracts search queries from URLs
2. **Focus on AI Platform Detection**: Extend existing library with AI platform patterns
3. **Leverage WordPress Ecosystem**: Use WP_List_Table, Admin UI components
4. **Learn from LLMS Central**: Study their open source WordPress plugin code
5. **Start Simple**: Detection → Logging → Dashboard → Insights

**We have ALL the code we need to build this feature!**
