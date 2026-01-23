# HANDOFF: Fix Bot Tracking Gap - Track ALL Page Visits

## Problem
Third Audience only tracks 3-4 bot visits while FieldCamp tracks 223+ visits per day.

**Root Cause:** Third Audience ONLY tracks bot visits when:
1. Bot sends `Accept: text/markdown` header (content negotiation)
2. Bot requests `.md` URL directly

But 95%+ of bot crawls are to **regular HTML pages** which are NOT tracked!

## Solution
Add a new hook to track bot visits on EVERY page request, not just .md requests.

## Implementation

### File: `/includes/class-ta-bot-analytics.php`

#### Step 1: Add hook in `__construct()` after line 191

```php
// Hook into template_redirect to track AI citation clicks.
add_action( 'template_redirect', array( $this, 'maybe_track_citation_click' ), 5 );

// NEW: Hook to track ALL bot crawls on every page (like FieldCamp's middleware)
add_action( 'template_redirect', array( $this, 'maybe_track_bot_crawl' ), 1 );
```

#### Step 2: Add new method after `maybe_track_citation_click()` (around line 215)

```php
/**
 * Track bot crawl on any page visit.
 *
 * This runs on EVERY page request and checks if the visitor is a known AI bot.
 * Similar to FieldCamp's Next.js middleware approach.
 *
 * @since 3.2.0
 * @return void
 */
public function maybe_track_bot_crawl() {
    // Skip admin pages.
    if ( is_admin() ) {
        return;
    }

    // Skip AJAX requests.
    if ( wp_doing_ajax() ) {
        return;
    }

    // Skip cron requests.
    if ( wp_doing_cron() ) {
        return;
    }

    // Skip REST API requests (handled separately).
    if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
        return;
    }

    // Skip if already tracked via .md request (avoid duplicates).
    // Check for a flag set by URL router or content negotiation.
    if ( did_action( 'ta_bot_visit_tracked' ) ) {
        return;
    }

    // Get user agent.
    $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

    if ( empty( $user_agent ) ) {
        return;
    }

    // Detect if this is a known bot.
    $bot_info = $this->detect_bot( $user_agent );

    if ( ! $bot_info ) {
        return; // Not a bot, skip tracking.
    }

    // Get current URL.
    $current_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

    // Get post info if available.
    $post_id    = get_the_ID();
    $post_title = $post_id ? get_the_title( $post_id ) : '';

    // Get client IP.
    $ip_address = $this->get_client_ip();

    // Prepare tracking data.
    $tracking_data = array(
        'bot_type'      => $bot_info['bot_type'],
        'bot_name'      => $bot_info['name'],
        'user_agent'    => $user_agent,
        'url'           => home_url( $current_url ),
        'post_id'       => $post_id ?: null,
        'post_title'    => $post_title,
        'ip_address'    => $ip_address,
        'cache_status'  => 'HTML', // Regular HTML page, not markdown
        'response_time' => 0,      // Can't measure easily here
        'traffic_type'  => 'bot_crawl',
        'detection_method' => $bot_info['detection_method'] ?? 'pattern',
        'confidence_score' => $bot_info['confidence'] ?? 1.0,
    );

    // Track the visit.
    $result = $this->track_visit( $tracking_data );

    if ( $result ) {
        // Fire action to prevent duplicate tracking.
        do_action( 'ta_bot_visit_tracked' );

        $this->logger->debug( 'Bot crawl tracked on HTML page.', array(
            'bot'  => $bot_info['name'],
            'url'  => $current_url,
        ) );
    }
}

/**
 * Get client IP address, handling proxies.
 *
 * @since 3.2.0
 * @return string IP address.
 */
private function get_client_ip() {
    $ip = '';

    // Check for proxy headers (like FieldCamp does).
    if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
        $ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
        $ip  = trim( $ips[0] );
    } elseif ( ! empty( $_SERVER['HTTP_X_REAL_IP'] ) ) {
        $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) );
    } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
        $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
    }

    return $ip ?: 'unknown';
}
```

#### Step 3: Update URL Router and Content Negotiation to fire the action

In `class-ta-url-router.php` after `track_visit()` call (around line 376):
```php
$this->bot_analytics->track_visit( array( ... ) );
do_action( 'ta_bot_visit_tracked' ); // Prevent duplicate tracking
```

In `class-ta-content-negotiation.php` after `track_visit()` call (around line 120):
```php
$this->bot_analytics->track_visit( array( ... ) );
do_action( 'ta_bot_visit_tracked' ); // Prevent duplicate tracking
```

## Verification

After implementing, test with:
```bash
# Simulate ChatGPT-User visiting an HTML page
curl -H "User-Agent: Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; ChatGPT-User/1.0" \
     http://localhost:8080/hello-world/

# Check Bot Analytics dashboard - should show the visit
```

## Why This Matches FieldCamp

FieldCamp's Next.js middleware:
```javascript
export async function middleware(request: NextRequest) {
  const userAgent = request.headers.get('user-agent');
  const crawlerName = detectAICrawler(userAgent);
  if (crawlerName) {
    logCrawlerVisit(crawlerName, request);  // Tracks ALL requests
  }
}
```

Our new WordPress approach:
```php
add_action('template_redirect', 'maybe_track_bot_crawl', 1);
// Runs on every page request, checks user-agent, tracks if bot
```

## Additional Improvement: Search Query Explanation

The UI says "Search queries extracted from Perplexity referrer URLs. Other platforms don't include query data in referrers."

This is **technically accurate**:
- Perplexity: `https://perplexity.ai/search?q=your+query` (query in URL)
- ChatGPT: Uses `utm_source=chatgpt.com` (no query)
- Claude/Gemini: Just domain in referrer (no query)

No change needed for this - it's correct behavior.

## Files to Modify

1. `/includes/class-ta-bot-analytics.php` - Main tracking logic
2. `/includes/class-ta-url-router.php` - Add duplicate prevention action
3. `/includes/class-ta-content-negotiation.php` - Add duplicate prevention action

## Version

This should be version **3.2.0** - Major tracking improvement.
