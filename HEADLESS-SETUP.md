# Third Audience - Headless WordPress Setup Guide

## Overview

This guide shows how to integrate **Third Audience citation tracking** with headless WordPress sites (Next.js, Gatsby, Nuxt, etc.).

**Key Feature:** AJAX-first architecture works with **ALL security plugins** - no REST API issues!

---

## Why AJAX-First?

Most production WordPress sites use security plugins (Solid Security, Wordfence, etc.) that **block REST API**.

### Method Comparison:

| Method | Security Plugin Friendly? | Configuration Needed? |
|--------|---------------------------|----------------------|
| **AJAX** | ✅ **100%** | ❌ None |
| REST API | ❌ Blocked 80%+ | ✅ Manual whitelist |
| GraphQL | ⚠️ Varies | ✅ Requires WPGraphQL |

**AJAX (`admin-ajax.php`)** has been the WordPress standard since 2.8 (2008). Security plugins NEVER block it.

---

## Setup Steps

### 1. Install Plugin on WordPress

```bash
# In your WordPress installation
cd wp-content/plugins/
git clone https://github.com/your-repo/third-audience.git
# Or upload via WordPress Admin → Plugins → Add New
```

Activate the plugin - it will auto-configure everything!

### 2. Get API Key

Go to: **WordPress Admin → Settings → Third Audience**

Copy your API key:
```
ta_xxxxxxxxxxxxxxxxxxxxxxxxxx
```

### 3. Add to Environment Variables

In your headless site (Next.js example):

```bash
# .env.local or .env.production
WORDPRESS_URL=https://your-wordpress-site.com
TA_CITATION_API_KEY=ta_xxxxxxxxxxxxxxxxxxxxxxxxxx
```

---

## Next.js Integration (App Router or Pages Router)

### Method 1: Middleware (Recommended)

Create or update `src/middleware.ts`:

```typescript
import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

// AI platforms that cite content
const AI_CITATION_SOURCES = [
  { pattern: /chatgpt/i, name: 'ChatGPT' },
  { pattern: /perplexity/i, name: 'Perplexity' },
  { pattern: /claude/i, name: 'Claude' },
  { pattern: /gemini/i, name: 'Gemini' },
  { pattern: /copilot/i, name: 'Copilot' },
  { pattern: /bing/i, name: 'Bing AI' },
];

/**
 * Detect if request came from an AI citation
 */
function detectAICitation(request: NextRequest): { platform: string; query?: string } | null {
  const url = request.nextUrl;
  const referer = request.headers.get('referer') || '';

  // Check utm_source parameter (e.g., ?utm_source=chatgpt.com)
  const utmSource = url.searchParams.get('utm_source');
  if (utmSource) {
    for (const source of AI_CITATION_SOURCES) {
      if (source.pattern.test(utmSource)) {
        return { platform: source.name };
      }
    }
  }

  // Check referer header
  for (const source of AI_CITATION_SOURCES) {
    if (source.pattern.test(referer)) {
      // Extract search query from Perplexity
      if (source.name === 'Perplexity' && referer.includes('?q=')) {
        const match = referer.match(/[?&]q=([^&]+)/);
        return {
          platform: source.name,
          query: match ? decodeURIComponent(match[1]) : undefined
        };
      }
      return { platform: source.name };
    }
  }

  return null;
}

/**
 * Track citation using AJAX-FIRST architecture
 *
 * Why AJAX first?
 * - ✅ Works with ALL security plugins (Solid Security, Wordfence, etc.)
 * - ✅ Standard WordPress API method since WP 2.8
 * - ✅ No REST API conflicts or blocks
 * - ✅ Same speed and features as REST API
 *
 * Method priority:
 * 1. AJAX (primary - always works)
 * 2. REST API (fallback - may be blocked)
 * 3. GraphQL (last resort - requires WPGraphQL plugin)
 */
async function trackCitation(request: NextRequest, citation: { platform: string; query?: string }) {
  const wordpressUrl = process.env.WORDPRESS_URL || 'https://your-site.com';
  const apiKey = process.env.TA_CITATION_API_KEY || '';

  const data = {
    url: request.nextUrl.pathname,
    platform: citation.platform,
    referer: request.headers.get('referer') || '',
    search_query: citation.query || '',
    ip: request.headers.get('x-forwarded-for')?.split(',')[0] || 'unknown',
  };

  try {
    // METHOD 1: Try AJAX first (most reliable - works with ALL security plugins)
    const ajaxResponse = await fetch(`${wordpressUrl}/wp-admin/admin-ajax.php`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        action: 'ta_track_citation',
        api_key: apiKey,
        ...data,
      }),
    });

    if (ajaxResponse.ok) {
      console.log('[Citation Tracking] ✅ Tracked via AJAX (Primary method)');
      return;
    }

    console.log('[Citation Tracking] AJAX failed (unusual), trying REST API...');

    // METHOD 2: Try REST API (backup - may be blocked by security plugins)
    const restResponse = await fetch(`${wordpressUrl}/wp-json/third-audience/v1/track-citation`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-TA-Api-Key': apiKey,
      },
      body: JSON.stringify(data),
    });

    if (restResponse.ok) {
      console.log('[Citation Tracking] ✅ Tracked via REST API (Fallback)');
      return;
    }

    console.log('[Citation Tracking] REST API also failed, trying GraphQL...');

    // METHOD 3: Try GraphQL (if WPGraphQL is available)
    const graphqlResponse = await fetch(`${wordpressUrl}/graphql`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        query: `
          mutation TrackCitation($url: String!, $platform: String!, $referer: String, $searchQuery: String, $apiKey: String!) {
            trackCitation(input: {
              url: $url
              platform: $platform
              referer: $referer
              searchQuery: $searchQuery
              apiKey: $apiKey
            }) {
              success
              message
            }
          }
        `,
        variables: {
          url: data.url,
          platform: data.platform,
          referer: data.referer,
          searchQuery: data.search_query,
          apiKey: apiKey,
        },
      }),
    });

    if (graphqlResponse.ok) {
      const result = await graphqlResponse.json();
      if (result.data?.trackCitation?.success) {
        console.log('[Citation Tracking] ✅ Tracked via GraphQL (Last resort fallback)');
        return;
      }
    }

    console.error('[Citation Tracking] ❌ All 3 methods failed (AJAX, REST, GraphQL) - check WordPress plugin');
  } catch (error) {
    console.error('[Citation Tracking] ❌ Network error:', error);
  }
}

export async function middleware(request: NextRequest) {
  // 1. AI Citation Tracking (fire and forget, non-blocking)
  const citation = detectAICitation(request);
  if (citation) {
    // Track asynchronously (non-blocking)
    trackCitation(request, citation);
  }

  // 2. Continue with your existing middleware logic
  return NextResponse.next();
}

export const config = {
  matcher: [
    // Match all paths except static files and API routes
    '/((?!api|_next/static|_next/image|favicon.ico).*)',
  ],
};
```

### Method 2: API Route (Alternative)

If you prefer API routes, create `app/api/track-citation/route.ts`:

```typescript
import { NextRequest, NextResponse } from 'next/server';

export async function POST(request: NextRequest) {
  const body = await request.json();
  const wordpressUrl = process.env.WORDPRESS_URL || '';
  const apiKey = process.env.TA_CITATION_API_KEY || '';

  try {
    // AJAX-first approach
    const response = await fetch(`${wordpressUrl}/wp-admin/admin-ajax.php`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        action: 'ta_track_citation',
        api_key: apiKey,
        ...body,
      }),
    });

    const data = await response.json();
    return NextResponse.json(data);
  } catch (error) {
    return NextResponse.json({ error: 'Failed to track citation' }, { status: 500 });
  }
}
```

---

## Gatsby Integration

### gatsby-node.js

```javascript
exports.onCreatePage = async ({ page, actions }) => {
  const { createPage, deletePage } = actions;

  // Track citation on page visit
  if (typeof window !== 'undefined') {
    const referer = document.referrer;
    const aiPlatforms = ['perplexity', 'chatgpt', 'claude', 'gemini'];

    const isAICitation = aiPlatforms.some(platform =>
      referer.toLowerCase().includes(platform)
    );

    if (isAICitation) {
      fetch(`${process.env.WORDPRESS_URL}/wp-admin/admin-ajax.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'ta_track_citation',
          api_key: process.env.TA_CITATION_API_KEY,
          platform: referer.match(/perplexity|chatgpt|claude|gemini/i)[0],
          url: window.location.pathname,
          referer: referer,
          ip: 'client-side',
        })
      });
    }
  }
};
```

---

## Nuxt 3 Integration

### server/middleware/citation-tracking.ts

```typescript
export default defineEventHandler(async (event) => {
  const referer = getHeader(event, 'referer') || '';
  const aiPlatforms = ['perplexity', 'chatgpt', 'claude', 'gemini'];

  const matchedPlatform = aiPlatforms.find(platform =>
    referer.toLowerCase().includes(platform)
  );

  if (matchedPlatform) {
    const config = useRuntimeConfig();

    try {
      await $fetch(`${config.public.wordpressUrl}/wp-admin/admin-ajax.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'ta_track_citation',
          api_key: config.taCitationApiKey,
          platform: matchedPlatform,
          url: event.path,
          referer: referer,
          ip: getHeader(event, 'x-forwarded-for') || 'unknown',
        })
      });
    } catch (error) {
      console.error('Citation tracking failed:', error);
    }
  }
});
```

---

## Testing Your Setup

### 1. Test AJAX Endpoint (Manual)

```bash
# Test AJAX health check
curl -X POST "https://your-wordpress-site.com/wp-admin/admin-ajax.php" \
  -d "action=ta_health_check"

# Expected: {"success":true,"data":{"status":"healthy","version":"3.4.3","method":"ajax_fallback"}}
# If returns "0" = AJAX action not registered (plugin not activated)
```

```bash
# Test AJAX citation tracking
curl -X POST "https://your-wordpress-site.com/wp-admin/admin-ajax.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=ta_track_citation" \
  -d "api_key=YOUR_API_KEY" \
  -d "platform=Perplexity" \
  -d "url=/test-page/" \
  -d "referer=https://www.perplexity.ai" \
  -d "search_query=test" \
  -d "ip=8.8.8.8"

# Expected: {"success":true,"data":{"message":"Citation tracked successfully","platform":"Perplexity"}}
```

### 2. Test from Frontend

Visit your site with AI platform referer:

```
https://your-site.com/blog/some-post?utm_source=perplexity
```

Check browser console:
```
[Citation Tracking] ✅ Tracked via AJAX (Primary method)
```

### 3. Verify in WordPress

Go to: **WordPress Admin → Third Audience → AI Citations**

You should see the tracked citation!

---

## Troubleshooting

### AJAX Returns "0"

**Cause:** AJAX action not registered
**Fix:**
1. Check plugin is activated in WordPress Admin → Plugins
2. Deactivate and reactivate the plugin
3. Check WordPress debug.log for errors

### "Invalid API key" Error

**Cause:** API key mismatch
**Fix:**
1. Get API key from WordPress Admin → Settings → Third Audience
2. Update your `.env` file with correct key
3. Restart your dev server

### Citations Not Appearing in Dashboard

**Cause:** Database issue or old plugin version
**Fix:**
1. Go to WordPress Admin → Settings → Third Audience → System Health
2. Check Database Status
3. Click "Run Database Migration" if available
4. Ensure plugin version is 3.4.0+

### "All 3 methods failed" Error

**Cause:** Network/firewall issue
**Fix:**
1. Check WordPress URL is correct in `.env`
2. Test direct access: `curl https://your-wp-site.com/wp-admin/admin-ajax.php`
3. Check firewall rules (Cloudflare, server firewall)

---

## API Reference

### AJAX Endpoint

**Endpoint:** `POST /wp-admin/admin-ajax.php`

**Parameters:**
```
action=ta_track_citation (required)
api_key=YOUR_API_KEY (required)
platform=ChatGPT|Perplexity|Claude|Gemini (required)
url=/page-path/ (required)
referer=https://perplexity.ai (optional)
search_query=user search query (optional)
ip=8.8.8.8 (optional)
```

**Response (Success):**
```json
{
  "success": true,
  "data": {
    "message": "Citation tracked successfully",
    "platform": "Perplexity",
    "url": "/page-path/",
    "method": "ajax_fallback"
  }
}
```

**Response (Error):**
```json
{
  "success": false,
  "data": {
    "message": "Invalid or missing API key"
  }
}
```

### REST API Endpoint (Fallback)

**Endpoint:** `POST /wp-json/third-audience/v1/track-citation`

**Headers:**
```
Content-Type: application/json
X-TA-Api-Key: YOUR_API_KEY
```

**Body:**
```json
{
  "url": "/page-path/",
  "platform": "Perplexity",
  "referer": "https://perplexity.ai",
  "search_query": "optional query",
  "ip": "8.8.8.8"
}
```

**Note:** REST API may be blocked by security plugins. AJAX is recommended.

---

## Security Best Practices

### 1. Protect API Key

```bash
# .env.local (never commit to git)
TA_CITATION_API_KEY=ta_xxxxxxxxxxxxx

# Add to .gitignore
echo ".env.local" >> .gitignore
```

### 2. Rate Limiting

The plugin includes built-in rate limiting:
- **30 requests per minute per IP** (AJAX)
- **60 requests per minute per IP** (REST API)

### 3. API Key Rotation

To rotate your API key:
1. Go to WordPress Admin → Settings → Third Audience
2. Click "Regenerate API Key"
3. Update your `.env` file
4. Redeploy your frontend

---

## Performance Optimization

### 1. Non-Blocking Tracking

The middleware code runs tracking asynchronously - it **never blocks** your page load:

```typescript
// Track asynchronously (fire and forget)
trackCitation(request, citation);

// Page continues loading immediately
return NextResponse.next();
```

### 2. Duplicate Prevention

Citations are deduplicated within 60-second window to prevent double-tracking.

### 3. Minimal Overhead

- AJAX request: ~50-100ms
- No database queries on frontend
- Runs only when AI referer detected

---

## Advanced Configuration

### Custom Platform Detection

Add custom AI platforms:

```typescript
const AI_CITATION_SOURCES = [
  { pattern: /chatgpt/i, name: 'ChatGPT' },
  { pattern: /perplexity/i, name: 'Perplexity' },
  { pattern: /your-custom-ai/i, name: 'CustomAI' }, // Add yours
];
```

### Custom IP Detection

Override IP detection logic:

```typescript
const data = {
  // ...other fields
  ip: request.headers.get('cf-connecting-ip') || // Cloudflare
      request.headers.get('x-real-ip') ||         // Nginx
      request.headers.get('x-forwarded-for')?.split(',')[0] ||
      'unknown',
};
```

### Logging and Monitoring

Enable detailed logging:

```typescript
console.log('[Citation Tracking] Detection:', citation);
console.log('[Citation Tracking] Request data:', data);
console.log('[Citation Tracking] Response:', response.status);
```

---

## WordPress Configuration

### Check Current Mode

Go to: **WordPress Admin → Settings → Third Audience → System Health**

You'll see:

```
✅ Running in AJAX Mode (Secure & Reliable)

Third Audience is using AJAX endpoints - the standard WordPress API
method that works with ALL security plugins.

Why this is better for production sites:
✅ Compatible with security plugins: Solid Security
✅ Works on headless WordPress sites
✅ No security plugin conflicts
✅ Same features as REST API, zero compromises

No action required - your site is configured optimally!
```

### Force REST API Mode (Optional)

If you want to use REST API despite security plugins:

1. Go to System Health page
2. Click "Force REST API Mode"
3. This will attempt to whitelist REST endpoints in security plugins

**Note:** Not recommended - AJAX works better universally.

---

## Support

### Common Issues

**Issue:** "0" response from AJAX
**Solution:** Plugin not activated or AJAX actions not registered

**Issue:** "Invalid API key"
**Solution:** Check API key matches WordPress settings

**Issue:** Citations not showing in dashboard
**Solution:** Run database migration from System Health page

### Debug Mode

Enable WordPress debug logging:

```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Check logs:
```bash
tail -f wp-content/debug.log
```

### Get Help

1. Check System Health page first
2. Review debug.log for errors
3. Test AJAX endpoint manually (see Testing section)
4. Report issues with full environment details

---

## Summary

### ✅ What You Get

- Universal compatibility (ALL security plugins)
- Zero configuration needed
- Automatic fallback to REST/GraphQL
- Non-blocking, async tracking
- Built-in rate limiting
- Duplicate prevention
- IP geolocation
- Full analytics dashboard

### 🚀 Quick Start Checklist

- [ ] Install plugin on WordPress
- [ ] Activate plugin (auto-configures everything)
- [ ] Get API key from Settings
- [ ] Add to your `.env` file
- [ ] Copy middleware code to your project
- [ ] Test with `?utm_source=perplexity`
- [ ] Check WordPress dashboard for citation

**That's it!** Your headless site now tracks AI citations automatically. 🎉
