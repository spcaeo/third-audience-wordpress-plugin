# Webhook Features - Complete Reference

## Feature Overview

The Third Audience webhook system provides real-time event notifications with minimal configuration.

## Core Features

### ✅ Feature #1: Webhook URL Configuration
- **Location:** Settings > Webhooks tab
- **Configuration:** Simple HTTPS URL input field
- **Validation:** HTTPS required, sanitized URL
- **Storage:** WordPress options (`ta_webhook_url`)
- **Display:** Current status shown on page

### ✅ Feature #2: Enable/Disable Toggle
- **Location:** Settings > Webhooks tab
- **Default:** Disabled (safe default)
- **Storage:** WordPress options (`ta_webhooks_enabled`)
- **Behavior:** Webhooks completely skipped when disabled
- **No Performance Impact:** When disabled, zero overhead

### ✅ Feature #3: markdown.accessed Event
- **Trigger:** Every bot markdown access via `.md` URL
- **Data Includes:**
  - Bot type and name
  - Accessed URL
  - Post ID and title
  - Cache status (HIT, MISS, PRE_GENERATED)
  - Response time in milliseconds
  - Site URL
  - ISO 8601 timestamp
- **Frequency:** Per request (no deduplication)
- **Use Case:** Real-time bot activity monitoring

### ✅ Feature #4: bot.detected Event
- **Trigger:** First visit from a new bot type (24h window)
- **Data Includes:**
  - Bot type identifier
  - Bot name
  - Bot color (for UI display)
  - Site URL
  - ISO 8601 timestamp
- **Deduplication:** Once per bot type per 24 hours
- **Use Case:** Alert on new bot discovery

### ✅ Feature #5: Test Webhook
- **Location:** Settings > Webhooks tab > "Send Test Webhook" button
- **Functionality:** Sends test payload to verify endpoint
- **Feedback:** Success/failure message with HTTP status
- **Payload:** Special `webhook.test` event
- **Inline Result:** Shows result directly in settings page

### ✅ Feature #6: Webhook Delivery
- **Protocol:** HTTP POST
- **Content-Type:** application/json
- **User-Agent:** "Third Audience/version"
- **Timeout:** 10 seconds
- **Automatic Retry:** 1 retry on failure
- **Success Codes:** 200-299

### ✅ Feature #7: Error Logging
- **Integration:** TA_Logger system
- **Location:** Settings > Logs tab
- **Details Logged:**
  - Delivery status (success/failure)
  - HTTP status codes
  - Error messages
  - Response times
  - Event type
- **Search:** Filter logs for "Webhook" keyword

### ✅ Feature #8: Admin Interface
- **Tab Name:** "Webhooks"
- **Sections:**
  1. Webhook Configuration (form)
  2. Webhook Events (descriptions)
  3. Example Payload (JSON display)
  4. Security Notes (best practices)
  5. Test Webhook (sidebar)
  6. Webhook Status (sidebar)
- **Design:** Apple-theme consistent styling

### ✅ Feature #9: Security Features
- **HTTPS-Only:** Non-HTTPS URLs rejected
- **User-Agent Header:** "Third Audience/version" for verification
- **Timeout Protection:** 10-second max execution
- **Payload Validation:** JSON well-formed
- **Error Handling:** Graceful failure handling
- **Logging:** All attempts logged for audit trail

### ✅ Feature #10: Settings Persistence
- **Storage:** WordPress options table
- **Keys:**
  - `ta_webhooks_enabled` (boolean)
  - `ta_webhook_url` (URL string)
- **Sanitization:** URL sanitized, boolean coerced
- **Default:** Webhooks disabled by default (safe)

## Implementation Details

### Event Payload Structure

**All Events Include:**
```json
{
  "event": "event_name",
  "timestamp": "ISO 8601 string",
  "site_url": "site base URL",
  "data": { /* event-specific data */ }
}
```

**markdown.accessed Data:**
- `bot_type` - Internal bot identifier
- `bot_name` - Display name
- `url` - Requested URL
- `post_id` - Post ID
- `post_title` - Post title
- `cache_status` - HIT|MISS|PRE_GENERATED
- `response_time` - Milliseconds

**bot.detected Data:**
- `bot_type` - Internal bot identifier
- `bot_name` - Display name
- `bot_color` - Hex color code

**webhook.test Data:**
- `test` - Boolean true
- `plugin_name` - "Third Audience"
- `plugin_version` - Current version

## Integration Points

### URL Router Integration
- **File:** `class-ta-url-router.php`
- **Method:** `track_bot_visit()`
- **Hook Point:** After analytics tracking
- **Fires:** `markdown.accessed` event
- **Data Source:** Bot info, URL, cache status, response time

### Bot Analytics Integration
- **File:** `class-ta-bot-analytics.php`
- **Method:** `track_visit()`
- **Hook Point:** After visit stored, before return
- **Fires:** `bot.detected` event
- **Logic:** Checks if first visit, counts previous visits
- **Deduplication:** Uses WordPress transients

### Admin Integration
- **File:** `class-ta-admin.php`
- **Registration:** Settings API
- **Handlers:**
  - `handle_save_webhook_settings()` - Save config
  - `handle_test_webhook()` - Test delivery
- **Settings Saved:** Via standard WordPress options

### Autoloader Integration
- **File:** `includes/autoload.php`
- **Class Map:** `TA_Webhooks` entry
- **Preload:** Added to frontend context
- **Load Strategy:** Lazy-loaded on first use

## API Reference

### TA_Webhooks Class

**Singleton Methods:**
```php
$webhooks = TA_Webhooks::get_instance();
```

**Configuration Methods:**
```php
$webhooks->is_enabled();                 // bool
$webhooks->get_webhook_url();            // string
$webhooks->set_webhook_url( $url );      // bool
$webhooks->set_enabled( $enabled );      // bool
```

**Event Methods:**
```php
$webhooks->fire_markdown_accessed( [
    'bot_type'      => 'string',
    'bot_name'      => 'string',
    'url'           => 'string',
    'post_id'       => int,
    'post_title'    => 'string',
    'cache_status'  => 'string',
    'response_time' => int,
] );

$webhooks->fire_bot_detected( [
    'type'  => 'string',
    'name'  => 'string',
    'color' => 'string',
] );
```

**Testing Methods:**
```php
$result = $webhooks->test_webhook();
// Returns: [
//     'success' => bool,
//     'message' => string,
//     'status_code' => int (optional)
// ]
```

## Performance Impact

### Negligible Overhead
- **No database queries** (webhooks disabled by default)
- **No blocking calls** (asynchronous in nature)
- **10-second timeout max** (protects against slow endpoints)
- **1 automatic retry** (limited retry strategy)
- **Logging only** (fire-and-forget delivery)

### When Enabled
- **Per request:** ~1-10ms overhead (HTTP POST)
- **Network-dependent:** Timeout is 10 seconds max
- **On failure:** Logged but doesn't affect page response

## Comparison: With/Without Webhooks

| Aspect | Without | With |
|--------|---------|------|
| Request Processing | Normal | +~5ms per bot request |
| Memory Usage | Standard | +1kb per webhook |
| Database Queries | Standard | No additional queries |
| Logging | Standard errors | Webhook delivery logs |
| External Calls | None | 1-2 per event (1 retry) |

## Deployment Checklist

- [ ] Update plugin code to v2.1.0+
- [ ] Verify PHP syntax (php -l)
- [ ] Set up webhook endpoint
- [ ] Test with webhook.site or similar
- [ ] Configure webhook URL in Settings
- [ ] Enable webhooks checkbox
- [ ] Send test webhook from admin
- [ ] Verify test payload received
- [ ] Monitor Logs tab for delivery status
- [ ] Set up external webhook handling

## Monitoring Dashboard (Proposed Future)

Would show:
- Total webhook events sent
- Success/failure rate
- Average response time
- Last delivery timestamp
- Endpoint status health
- Error frequency chart

## Related Features

- **Bot Analytics:** Provides the visitor data
- **Logger System:** Logs all webhook activity
- **Settings API:** Stores configuration
- **Autoloader:** Loads class dynamically
- **Security:** Validates HTTPS URLs

## Extensibility

### Hook Points for Future Features
- Before webhook send
- After webhook sent
- On webhook failure
- On webhook success

### Enhancement Opportunities
- Multiple webhook URLs
- Event filtering/routing
- Custom headers
- HMAC signing
- Delivery history UI
- Dead-letter queue
- Batch delivery
- Rate limiting

## Compliance & Standards

- **REST:** Uses standard HTTP POST
- **JSON:** RFC 8259 compliant
- **HTTPS:** Security best practice
- **Timestamps:** ISO 8601 format
- **User-Agent:** Standard header verification
- **Timeouts:** Reasonable 10-second limit

## Troubleshooting Reference

**Common Issues:**
1. Webhook not firing → Check enabled checkbox
2. Test fails → Verify endpoint exists, HTTPS, responds 200-299
3. Timeout → Endpoint takes >10 seconds, optimize receiver
4. No logs → Check Settings > Logs tab, filter "Webhook"
5. Missing data → Review payload structure in docs

**Debug Commands:**
```bash
# Check if webhooks enabled
wp option get ta_webhooks_enabled

# Get webhook URL
wp option get ta_webhook_url

# Check recent logs
wp db query "SELECT * FROM wp_ta_logs WHERE message LIKE '%Webhook%' LIMIT 10"
```

## FAQ for Features

**Q: Will webhooks slow down my site?**
A: No - minimal overhead (~1-10ms), non-blocking, 10-second timeout protection.

**Q: Can I test webhooks?**
A: Yes - "Send Test Webhook" button with inline result display.

**Q: What if my endpoint fails?**
A: Automatic 1 retry, all failures logged, page response unaffected.

**Q: Are webhooks logged?**
A: Yes - all delivery attempts logged to Settings > Logs tab.

**Q: How often does bot.detected fire?**
A: Once per bot type per 24 hours (deduplicated).

**Q: Can I use HTTP (non-HTTPS)?**
A: No - HTTPS required for security.

**Q: What format is the data?**
A: JSON with ISO 8601 timestamps.

## Version Information

- **Feature Added:** v2.1.0
- **Requires:** WordPress 5.0+
- **PHP:** 7.4+
- **Status:** Stable
- **Last Updated:** 2026-01-21

---

For questions or issues, refer to:
- WEBHOOK_QUICKSTART.md - User guide
- WEBHOOK_IMPLEMENTATION.md - Technical details
- Plugin Logs tab - Activity monitoring
