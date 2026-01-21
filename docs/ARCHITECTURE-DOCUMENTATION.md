# Third Audience - Technical Architecture Documentation

**Version:** 1.0
**Author:** Solutions Architect
**Last Updated:** January 2026

---

## Table of Contents

1. [System Architecture](#1-system-architecture)
2. [Cloudflare Worker Design](#2-cloudflare-worker-design)
3. [Router/Load Balancer Design](#3-routerload-balancer-design)
4. [WordPress Plugin Design](#4-wordpress-plugin-design)
5. [Database/Storage Schema](#5-databasestorage-schema)
6. [API Specifications](#6-api-specifications)
7. [Caching Strategy](#7-caching-strategy)
8. [Security Considerations](#8-security-considerations)
9. [Deployment Guide](#9-deployment-guide)
10. [File/Folder Structure](#10-filefolder-structure)

---

## 1. System Architecture

### 1.1 High-Level Architecture

```
+------------------------------------------------------------------+
|                         AI CRAWLERS                               |
|           (ClaudeBot, GPTBot, PerplexityBot, etc.)               |
+------------------------------------------------------------------+
                              |
                              | HTTPS Request
                              | GET /page.md or Accept: text/markdown
                              v
+------------------------------------------------------------------+
|                    WORDPRESS SITE                                 |
|  +------------------------------------------------------------+  |
|  |              Third Audience Plugin                          |  |
|  |  +------------------+  +------------------+  +------------+ |  |
|  |  | URL Router       |  | Content Negot.   |  | Discovery  | |  |
|  |  | (.md handling)   |  | (Accept header)  |  | (<link>)   | |  |
|  |  +------------------+  +------------------+  +------------+ |  |
|  +------------------------------------------------------------+  |
+------------------------------------------------------------------+
                              |
                              | API Call (if external service mode)
                              v
+------------------------------------------------------------------+
|                     ROUTER SERVICE                                |
|  +------------------+  +------------------+  +------------------+ |
|  | Worker Selector  |  | Usage Tracker    |  | Health Monitor   | |
|  | (least-loaded)   |  | (per account)    |  | (circuit break)  | |
|  +------------------+  +------------------+  +------------------+ |
|                              |                                    |
|                         KV Storage                                |
|                    (usage, config, keys)                          |
+------------------------------------------------------------------+
                              |
         +--------------------+--------------------+
         v                    v                    v
+----------------+   +----------------+   +----------------+
| CF Account A   |   | CF Account B   |   | CF Account N   |
| Worker Instance|   | Worker Instance|   | Worker Instance|
| (100K/day free)|   | (100K/day free)|   | (100K/day free)|
+----------------+   +----------------+   +----------------+
         |                    |                    |
         +--------------------+--------------------+
                              |
                              | Fetch HTML
                              v
+------------------------------------------------------------------+
|                    ORIGIN WORDPRESS                               |
+------------------------------------------------------------------+
```

### 1.2 Request Flow

```
1. AI Crawler -> WordPress Site
   GET https://example.com/blog/my-post.md

2. WordPress Plugin intercepts .md URL
   - Check local cache (transient)
   - If HIT: return cached markdown
   - If MISS: continue to step 3

3. Plugin calls Router Service
   GET https://router.third-audience.dev/get-worker
   Headers: Authorization: Bearer {api_key}

4. Router returns best worker
   {
     "worker_url": "https://ta-worker-1.workers.dev",
     "worker_id": "worker-001"
   }

5. Plugin calls Worker
   POST https://ta-worker-1.workers.dev/convert
   Body: { "url": "https://example.com/blog/my-post" }

6. Worker fetches origin HTML
   GET https://example.com/blog/my-post

7. Worker converts HTML -> Markdown
   - Extract main content
   - Convert to markdown
   - Generate frontmatter

8. Worker returns markdown
   Content-Type: text/markdown

9. Plugin caches and returns to crawler
   - Store in transient
   - Return to AI crawler

10. Router tracks usage
    POST /track-usage
    { "worker_id": "worker-001", "success": true }
```

---

## 2. Cloudflare Worker Design

### 2.1 Worker Responsibilities

1. **Receive conversion requests** from router/plugins
2. **Fetch HTML** from origin WordPress sites
3. **Extract main content** (ignore nav, sidebar, footer)
4. **Convert HTML to Markdown** using Turndown
5. **Generate YAML frontmatter** with metadata
6. **Return markdown** with proper headers
7. **Report usage** back to router

### 2.2 API Endpoints

#### POST /convert

Convert an HTML page to Markdown.

**Request:**
```http
POST /convert HTTP/1.1
Host: ta-worker-1.workers.dev
Content-Type: application/json
X-Router-Token: {internal_token}

{
  "url": "https://example.com/blog/my-post",
  "options": {
    "include_frontmatter": true,
    "extract_main_content": true,
    "include_images": true,
    "max_content_length": 100000
  }
}
```

**Response (200 OK):**
```markdown
---
title: "My Blog Post Title"
description: "A comprehensive guide to..."
author: "John Doe"
date_published: "2024-01-15T10:00:00Z"
date_modified: "2024-01-20T15:30:00Z"
url: "https://example.com/blog/my-post"
word_count: 1523
reading_time: "8 min"
---

# My Blog Post Title

This is the main content of the blog post...
```

**Response Headers:**
```http
Content-Type: text/markdown; charset=utf-8
X-Conversion-Time-Ms: 234
X-Content-Length-In: 45000
X-Content-Length-Out: 12000
Cache-Control: public, max-age=3600
```

#### GET /health

Health check endpoint.

```http
GET /health HTTP/1.1
Host: ta-worker-1.workers.dev

HTTP/1.1 200 OK
{
  "status": "healthy",
  "version": "1.0.0",
  "timestamp": "2024-01-16T14:30:00Z"
}
```

### 2.3 Code Structure

```typescript
// src/index.ts - Main entry point
export default {
  async fetch(request: Request, env: Env): Promise<Response> {
    const url = new URL(request.url);

    if (url.pathname === '/health') {
      return handleHealth();
    }

    if (url.pathname === '/convert' && request.method === 'POST') {
      return handleConvert(request, env);
    }

    return new Response('Not Found', { status: 404 });
  }
};

// src/converter.ts - Conversion orchestration
async function convertToMarkdown(url: string, options: ConvertOptions): Promise<ConversionResult> {
  // 1. Fetch HTML
  const html = await fetchHTML(url);

  // 2. Extract main content
  const content = extractMainContent(html);

  // 3. Convert to markdown
  const markdown = htmlToMarkdown(content);

  // 4. Generate frontmatter
  const frontmatter = generateFrontmatter(html, url);

  // 5. Combine and return
  return {
    markdown: `---\n${yaml.stringify(frontmatter)}---\n\n${markdown}`,
    metadata: { bytesIn: html.length, bytesOut: markdown.length }
  };
}

// src/extractor.ts - Content extraction
function extractMainContent(html: string): string {
  // Priority order for content extraction:
  // 1. <article> tag
  // 2. <main> tag
  // 3. .entry-content, .post-content (WordPress classes)
  // 4. #content
  // 5. Readability algorithm fallback
}

// src/turndown-config.ts - HTML to Markdown rules
const turndownService = new TurndownService({
  headingStyle: 'atx',
  codeBlockStyle: 'fenced',
  bulletListMarker: '-'
});

// Custom rules for WordPress-specific elements
turndownService.addRule('wpGallery', { ... });
turndownService.addRule('wpCaption', { ... });
```

### 2.4 Error Handling

| Error Code | HTTP Status | Meaning |
|------------|-------------|---------|
| INVALID_URL | 400 | URL format invalid |
| URL_NOT_ALLOWED | 400 | URL blocked (private IP) |
| FETCH_FAILED | 502 | Cannot fetch origin |
| TIMEOUT | 504 | Request timed out |
| PARSE_ERROR | 500 | HTML parsing failed |
| CONTENT_NOT_FOUND | 404 | No main content found |

---

## 3. Router/Load Balancer Design

### 3.1 Worker Selection Algorithm

```typescript
async function selectWorker(kv: KVNamespace): Promise<Worker> {
  const workers = await getActiveWorkers(kv);
  const today = getTodayDateString();

  // Get usage for all workers
  const usages = await Promise.all(
    workers.map(w => getUsage(kv, w.id, today))
  );

  // Filter workers with capacity
  const available = workers.filter((w, i) =>
    usages[i] < w.daily_limit * 0.95  // 95% threshold
  );

  if (available.length === 0) {
    throw new NoCapacityError();
  }

  // Select least-loaded worker
  let minUsage = Infinity;
  let selected = available[0];

  for (let i = 0; i < available.length; i++) {
    const utilization = usages[i] / workers[i].daily_limit;
    if (utilization < minUsage) {
      minUsage = utilization;
      selected = available[i];
    }
  }

  return selected;
}
```

### 3.2 KV Storage Schema

```
Key Pattern                          | Value
-------------------------------------|--------------------------------
workers:list                         | ["worker-001", "worker-002", ...]
worker:{id}:config                   | { url, daily_limit, enabled, ... }
usage:{worker_id}:{YYYY-MM-DD}       | { count: 45230, bytes_in: ..., bytes_out: ... }
site:{domain}:config                 | { api_key_hash, daily_limit, ... }
site:{domain}:usage:{YYYY-MM-DD}     | { count: 120, ... }
ratelimit:{api_key}:{window}         | { count: 45 }
```

### 3.3 API Endpoints

#### GET /get-worker

```http
GET /get-worker HTTP/1.1
Authorization: Bearer ta_live_abc123
X-Site-URL: https://example.com

HTTP/1.1 200 OK
{
  "success": true,
  "worker": {
    "id": "worker-001",
    "url": "https://ta-worker-1.workers.dev",
    "convert_endpoint": "https://ta-worker-1.workers.dev/convert"
  },
  "usage": {
    "worker_today": 45230,
    "worker_limit": 100000,
    "worker_remaining": 54770
  }
}
```

#### POST /track-usage

```http
POST /track-usage HTTP/1.1
Authorization: Bearer ta_live_abc123
Content-Type: application/json

{
  "worker_id": "worker-001",
  "site_url": "https://example.com",
  "url_converted": "https://example.com/blog/my-post",
  "bytes_in": 45000,
  "bytes_out": 12000,
  "conversion_time_ms": 234,
  "cache_hit": false,
  "success": true
}

HTTP/1.1 200 OK
{
  "success": true,
  "usage": {
    "worker_today": 45231,
    "site_today": 121
  }
}
```

#### GET /stats (Admin)

```http
GET /stats?date=2024-01-16 HTTP/1.1
Authorization: Bearer ta_admin_xyz789

HTTP/1.1 200 OK
{
  "date": "2024-01-16",
  "summary": {
    "total_requests": 156789,
    "unique_sites": 423,
    "cache_hit_rate": 0.67,
    "error_rate": 0.02
  },
  "workers": [
    {
      "id": "worker-001",
      "usage_today": 45231,
      "limit": 100000,
      "utilization": 0.452
    }
  ]
}
```

### 3.4 Daily Reset Mechanism

Usage counters reset at midnight UTC. The KV keys include the date, so old entries naturally expire:

```typescript
// Keys are date-scoped
const usageKey = `usage:${workerId}:${format(new Date(), 'yyyy-MM-dd')}`;

// Set TTL for automatic cleanup
await kv.put(usageKey, JSON.stringify(usage), {
  expirationTtl: 86400 * 7  // Keep 7 days for analytics
});
```

---

## 4. WordPress Plugin Design

### 4.1 Plugin Architecture

```
third-audience/
├── third-audience.php           # Main plugin file
├── includes/
│   ├── class-third-audience.php      # Main class
│   ├── class-ta-url-router.php       # .md URL handling
│   ├── class-ta-content-negotiation.php  # Accept header
│   ├── class-ta-discovery.php        # <link> tags
│   ├── class-ta-cache-manager.php    # Caching
│   ├── class-ta-api-client.php       # Router/Worker API
│   └── class-ta-cache-invalidation.php   # Cache purge
├── admin/
│   ├── class-ta-admin.php            # Settings page
│   └── views/settings-page.php
└── uninstall.php               # Cleanup
```

### 4.2 WordPress Hooks

```php
// third-audience.php
class Third_Audience {
    public function __construct() {
        // URL Routing - intercept .md requests
        add_action('init', [$this, 'register_rewrite_rules']);
        add_action('template_redirect', [$this, 'handle_markdown_request']);

        // Content Negotiation
        add_action('template_redirect', [$this, 'handle_content_negotiation'], 5);

        // Discovery Tags
        add_action('wp_head', [$this, 'add_markdown_discovery_link']);

        // Cache Invalidation
        add_action('save_post', [$this, 'invalidate_post_cache']);
        add_action('delete_post', [$this, 'invalidate_post_cache']);
        add_action('edit_post', [$this, 'invalidate_post_cache']);

        // Admin
        add_action('admin_menu', [$this, 'add_settings_page']);
    }
}
```

### 4.3 .md URL Routing

```php
// class-ta-url-router.php
class TA_URL_Router {

    public function register_rewrite_rules() {
        // Match any URL ending in .md
        add_rewrite_rule(
            '(.+)\.md$',
            'index.php?ta_markdown=1&ta_path=$matches[1]',
            'top'
        );

        add_rewrite_tag('%ta_markdown%', '1');
        add_rewrite_tag('%ta_path%', '([^&]+)');
    }

    public function handle_markdown_request() {
        if (!get_query_var('ta_markdown')) {
            return;
        }

        $path = get_query_var('ta_path');
        $url = home_url('/' . $path);

        // Check cache first
        $cache_key = 'ta_md_' . md5($url);
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            $this->send_markdown_response($cached, true);
            exit;
        }

        // Get from worker
        $markdown = $this->fetch_markdown($url);

        if ($markdown) {
            // Cache for configured TTL
            $ttl = get_option('ta_cache_ttl', 86400);
            set_transient($cache_key, $markdown, $ttl);

            $this->send_markdown_response($markdown, false);
        } else {
            status_header(404);
            echo 'Markdown version not available';
        }
        exit;
    }

    private function send_markdown_response($markdown, $cache_hit) {
        header('Content-Type: text/markdown; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        header('X-Cache-Status: ' . ($cache_hit ? 'HIT' : 'MISS'));
        echo $markdown;
    }
}
```

### 4.4 Content Negotiation

```php
// class-ta-content-negotiation.php
class TA_Content_Negotiation {

    public function handle_content_negotiation() {
        if (!is_singular()) {
            return;
        }

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

        if (strpos($accept, 'text/markdown') !== false) {
            // Client prefers markdown
            $current_url = get_permalink();
            $markdown_url = $current_url . '.md';

            // Redirect to .md URL
            wp_redirect($markdown_url, 303);
            exit;
        }
    }
}
```

### 4.5 Discovery Tags

```php
// class-ta-discovery.php
class TA_Discovery {

    public function add_markdown_discovery_link() {
        if (!is_singular()) {
            return;
        }

        $post_types = get_option('ta_enabled_post_types', ['post', 'page']);

        if (!in_array(get_post_type(), $post_types)) {
            return;
        }

        $markdown_url = get_permalink() . '.md';

        printf(
            '<link rel="alternate" type="text/markdown" href="%s" title="Markdown version">',
            esc_url($markdown_url)
        );
    }
}
```

### 4.6 Cache Invalidation

```php
// class-ta-cache-invalidation.php
class TA_Cache_Invalidation {

    public function invalidate_post_cache($post_id) {
        $post = get_post($post_id);

        if (!$post || $post->post_status !== 'publish') {
            return;
        }

        $url = get_permalink($post_id);
        $cache_key = 'ta_md_' . md5($url);

        delete_transient($cache_key);

        // Also invalidate archive pages if needed
        $this->invalidate_related_caches($post);
    }
}
```

---

## 5. Database/Storage Schema

### 5.1 Cloudflare KV (Router)

| Key Pattern | Value Type | TTL | Purpose |
|-------------|------------|-----|---------|
| `workers:list` | JSON Array | None | Active worker IDs |
| `worker:{id}:config` | JSON Object | None | Worker configuration |
| `usage:{worker}:{date}` | JSON Object | 7 days | Daily usage counters |
| `site:{domain}:config` | JSON Object | None | Site settings |
| `ratelimit:{key}:{window}` | JSON Object | 2 min | Rate limit counters |

### 5.2 WordPress Options

| Option Name | Type | Default | Purpose |
|-------------|------|---------|---------|
| `ta_router_url` | string | '' | Router service URL |
| `ta_api_key` | string (encrypted) | '' | Site API key |
| `ta_cache_ttl` | int | 86400 | Cache duration in seconds |
| `ta_enabled_post_types` | array | ['post', 'page'] | Post types to enable |
| `ta_enable_content_negotiation` | bool | true | Accept header handling |
| `ta_enable_discovery_tags` | bool | true | Add `<link>` tags |

### 5.3 WordPress Transients

| Transient Key | Value | TTL | Purpose |
|---------------|-------|-----|---------|
| `ta_md_{url_hash}` | Markdown string | Configurable | Cached conversions |
| `ta_worker_health` | JSON | 5 min | Worker status cache |

---

## 6. API Specifications

### 6.1 Authentication

All router endpoints require Bearer token authentication:

```http
Authorization: Bearer ta_live_abc123def456
```

API keys are prefixed:
- `ta_live_` - Production keys
- `ta_test_` - Test/staging keys
- `ta_admin_` - Admin keys (stats access)

### 6.2 Rate Limiting

| Endpoint | Limit | Window |
|----------|-------|--------|
| `/get-worker` | 100 | 1 minute |
| `/track-usage` | 200 | 1 minute |
| `/stats` | 10 | 1 minute |
| `/convert` (worker) | 1000 | 1 minute |

Rate limit headers:
```http
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1705412400
```

### 6.3 Error Responses

```json
{
  "success": false,
  "error": {
    "code": "RATE_LIMITED",
    "message": "Too many requests",
    "retry_after": 60
  },
  "request_id": "req_abc123"
}
```

---

## 7. Caching Strategy

### 7.1 Multi-Level Caching

```
Request
   |
   v
+------------------------------------------+
| LAYER 1: WordPress Transients            |
| Key: ta_md_{md5(url)}                    |
| TTL: 24 hours (configurable)             |
| Invalidation: On post save/delete        |
+------------------------------------------+
   | MISS
   v
+------------------------------------------+
| LAYER 2: Cloudflare Edge Cache           |
| Caches worker responses at edge          |
| Cache-Control: public, max-age=3600      |
+------------------------------------------+
   | MISS
   v
+------------------------------------------+
| LAYER 3: Worker Processing               |
| Fetch HTML -> Convert -> Return          |
+------------------------------------------+
```

### 7.2 TTL Recommendations

| Content Type | TTL | Reason |
|--------------|-----|--------|
| Blog posts | 24 hours | Balance freshness vs load |
| Static pages | 7 days | Rarely change |
| Archive pages | 1 hour | Change on new posts |
| Home page | 1 hour | Shows latest content |
| 404 responses | 5 minutes | May become valid |

### 7.3 Cache Invalidation Triggers

| Event | WordPress Cache | Edge Cache |
|-------|-----------------|------------|
| Post save/update | Clear immediately | TTL expires |
| Post delete | Clear immediately | TTL expires |
| Permalink change | Clear all | Purge all |
| Manual clear | Clear all | Optional API purge |

---

## 8. Security Considerations

### 8.1 Input Validation

```typescript
function validateUrl(url: string): boolean {
  const parsed = new URL(url);

  // Only allow http/https
  if (!['http:', 'https:'].includes(parsed.protocol)) {
    return false;
  }

  // Block private IPs and localhost
  const blockedPatterns = [
    /^10\./,
    /^172\.(1[6-9]|2[0-9]|3[0-1])\./,
    /^192\.168\./,
    /^127\./,
    /^localhost$/,
    /^169\.254\.169\.254$/,  // AWS metadata
  ];

  return !blockedPatterns.some(p => p.test(parsed.hostname));
}
```

### 8.2 Rate Limiting

- Per-API-key limits at router level
- Per-worker limits to prevent single-account abuse
- Automatic blocking of suspicious patterns

### 8.3 API Key Security

```php
// WordPress - encrypted key storage
class TA_API_Key_Manager {
    public function store_key(string $key): void {
        $encrypted = base64_encode(
            $key ^ str_repeat(SECURE_AUTH_KEY, strlen($key))
        );
        update_option('ta_api_key_encrypted', $encrypted);
    }

    public function get_key(): string {
        $encrypted = get_option('ta_api_key_encrypted', '');
        if (empty($encrypted)) return '';

        $decoded = base64_decode($encrypted);
        return $decoded ^ str_repeat(SECURE_AUTH_KEY, strlen($decoded));
    }
}
```

---

## 9. Deployment Guide

### 9.1 Cloudflare Worker Deployment

```bash
# 1. Clone repository
git clone https://github.com/third-audience/ta-worker.git
cd ta-worker

# 2. Install dependencies
npm install

# 3. Login to Cloudflare
npx wrangler login

# 4. Configure wrangler.toml
cat > wrangler.toml << EOF
name = "ta-worker-1"
main = "src/index.ts"
compatibility_date = "2024-01-01"
account_id = "your-account-id"

[vars]
ROUTER_URL = "https://router.third-audience.dev"
EOF

# 5. Set secrets
npx wrangler secret put ROUTER_TOKEN

# 6. Deploy
npx wrangler deploy

# 7. Verify
curl https://ta-worker-1.your-subdomain.workers.dev/health
```

### 9.2 WordPress Plugin Installation

1. Download plugin ZIP from releases
2. WordPress Admin > Plugins > Add New > Upload Plugin
3. Activate the plugin
4. Go to Settings > Third Audience
5. Enter Router URL and API Key
6. Configure enabled post types
7. Save and flush permalinks

### 9.3 Router Service Deployment

The Router Service can be deployed as a Cloudflare Worker (recommended) or on Fly.io for more control.

#### Option A: Cloudflare Worker (Recommended)

```bash
# 1. Clone repository
git clone https://github.com/third-audience/ta-router.git
cd ta-router

# 2. Install dependencies
npm install

# 3. Login to Cloudflare
npx wrangler login

# 4. Create KV namespaces
npx wrangler kv:namespace create "TA_ROUTER_KV"
npx wrangler kv:namespace create "TA_ROUTER_KV" --preview

# Note the namespace IDs from the output

# 5. Configure wrangler.toml
cat > wrangler.toml << 'EOF'
name = "ta-router"
main = "src/index.ts"
compatibility_date = "2024-01-01"
account_id = "your-account-id"

[[kv_namespaces]]
binding = "KV"
id = "your-kv-namespace-id"
preview_id = "your-preview-kv-namespace-id"

[vars]
ENVIRONMENT = "production"
EOF

# 6. Set secrets
npx wrangler secret put ADMIN_TOKEN
# Enter a secure admin token when prompted

# 7. Initialize worker registry
# Create initial worker configuration
cat > init-workers.json << 'EOF'
{
  "workers": [
    {
      "id": "worker-001",
      "url": "https://ta-worker-1.your-subdomain.workers.dev",
      "daily_limit": 100000,
      "enabled": true
    }
  ]
}
EOF

# 8. Deploy
npx wrangler deploy

# 9. Initialize KV data (run once after deployment)
curl -X POST https://ta-router.your-subdomain.workers.dev/admin/init \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d @init-workers.json

# 10. Verify deployment
curl https://ta-router.your-subdomain.workers.dev/health
```

#### Option B: Fly.io Deployment

```bash
# 1. Clone and navigate
git clone https://github.com/third-audience/ta-router.git
cd ta-router

# 2. Install Fly CLI
curl -L https://fly.io/install.sh | sh

# 3. Login to Fly
fly auth login

# 4. Create app
fly launch --name ta-router --region ord

# 5. Set secrets
fly secrets set ADMIN_TOKEN=your-secure-admin-token
fly secrets set REDIS_URL=your-upstash-redis-url  # For KV storage

# 6. Deploy
fly deploy

# 7. Verify
curl https://ta-router.fly.dev/health
```

#### Adding Workers to Router

After deploying workers, register them with the router:

```bash
# Add a new worker
curl -X POST https://ta-router.your-subdomain.workers.dev/admin/workers \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "id": "worker-002",
    "url": "https://ta-worker-2.another-account.workers.dev",
    "daily_limit": 100000,
    "enabled": true
  }'

# List all workers
curl https://ta-router.your-subdomain.workers.dev/admin/workers \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"

# Disable a worker (maintenance)
curl -X PATCH https://ta-router.your-subdomain.workers.dev/admin/workers/worker-001 \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"enabled": false}'
```

### 9.4 Environment Variables

**Worker:**
| Variable | Required | Description |
|----------|----------|-------------|
| `ROUTER_URL` | Yes | Router service URL |
| `ROUTER_TOKEN` | Yes | Internal auth token |

**Router:**
| Variable | Required | Description |
|----------|----------|-------------|
| `ADMIN_TOKEN` | Yes | Admin API auth |
| `ENVIRONMENT` | No | `production` or `development` |
| `REDIS_URL` | Fly.io only | Upstash Redis connection string |

**WordPress Plugin:**
| Variable | Required | Description |
|----------|----------|-------------|
| `TA_ROUTER_URL` | Yes | Router service URL (wp-config.php constant) |
| `TA_API_KEY` | Yes | Site API key (stored encrypted in options) |

---

## 10. File/Folder Structure

### 10.1 Worker Project

```
ta-worker/
├── src/
│   ├── index.ts              # Entry point
│   ├── converter.ts          # Conversion orchestration
│   ├── fetcher.ts            # HTTP client
│   ├── extractor.ts          # Content extraction
│   ├── turndown-config.ts    # Markdown rules
│   ├── frontmatter.ts        # YAML generation
│   ├── validator.ts          # URL validation
│   └── types.ts              # TypeScript interfaces
├── test/
│   ├── converter.test.ts
│   └── fixtures/
├── wrangler.toml
├── package.json
└── tsconfig.json
```

### 10.2 Router Project

```
ta-router/
├── src/
│   ├── index.ts              # Entry point
│   ├── handlers/
│   │   ├── get-worker.ts
│   │   ├── track-usage.ts
│   │   └── stats.ts
│   ├── services/
│   │   ├── worker-selector.ts
│   │   └── usage-tracker.ts
│   └── middleware/
│       ├── auth.ts
│       └── rate-limiter.ts
├── wrangler.toml
└── package.json
```

### 10.3 WordPress Plugin

```
third-audience/
├── third-audience.php
├── includes/
│   ├── class-third-audience.php
│   ├── class-ta-url-router.php
│   ├── class-ta-content-negotiation.php
│   ├── class-ta-discovery.php
│   ├── class-ta-cache-manager.php
│   └── class-ta-api-client.php
├── admin/
│   ├── class-ta-admin.php
│   └── views/
├── uninstall.php
└── readme.txt
```

### 10.4 Naming Conventions

| Element | Convention | Example |
|---------|------------|---------|
| PHP Classes | Title_Case_Prefix | `TA_Cache_Manager` |
| PHP Files | lowercase-hyphen | `class-ta-cache-manager.php` |
| TypeScript Files | lowercase-hyphen | `worker-selector.ts` |
| TypeScript Classes | PascalCase | `WorkerSelector` |
| KV Keys | lowercase:colon | `usage:worker-001:2024-01-16` |
| API Endpoints | lowercase-hyphen | `/get-worker` |

---

## Appendix A: Error Codes

| Code | HTTP | Description |
|------|------|-------------|
| `INVALID_URL` | 400 | URL format invalid |
| `URL_NOT_ALLOWED` | 400 | Private IP blocked |
| `MISSING_API_KEY` | 401 | No auth header |
| `INVALID_API_KEY` | 401 | Key not found |
| `RATE_LIMITED` | 429 | Too many requests |
| `NO_CAPACITY` | 503 | All workers at limit |
| `FETCH_FAILED` | 502 | Origin unreachable |
| `TIMEOUT` | 504 | Request timed out |

---

## Appendix B: Sample Nginx Config

```nginx
# Handle .md requests
location ~ \.md$ {
    try_files $uri $uri/ /index.php?$args;
}
```

---

*End of Technical Architecture Documentation*
