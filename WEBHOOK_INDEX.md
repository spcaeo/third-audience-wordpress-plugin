# Webhook Implementation - Complete Documentation Index

## Quick Navigation

### For Users/Admins
1. **[WEBHOOK_QUICKSTART.md](WEBHOOK_QUICKSTART.md)** - Start here! 5-minute setup guide
2. **[webhook-test-endpoint.php](webhook-test-endpoint.php)** - Simple test endpoint for verification
3. **Settings > Webhooks** in WordPress admin - Configuration interface

### For Developers
1. **[WEBHOOK_IMPLEMENTATION.md](WEBHOOK_IMPLEMENTATION.md)** - Technical architecture and integration
2. **[WEBHOOK_FEATURES.md](WEBHOOK_FEATURES.md)** - Complete feature reference
3. **[IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md)** - Implementation details and checklist
4. **[/third-audience/includes/class-ta-webhooks.php](third-audience/includes/class-ta-webhooks.php)** - Source code

---

## What Are Webhooks?

Webhooks send real-time HTTP POST notifications when events occur. Third Audience fires webhooks for:

- **markdown.accessed** - When a bot requests markdown content
- **bot.detected** - When a new bot visits for the first time (24h dedupe)

---

## Quick Start (60 seconds)

1. Go to **Settings > Third Audience > Webhooks**
2. Check **"Enable Webhooks"**
3. Enter your webhook URL (e.g., `https://webhook.site/your-unique-id`)
4. Click **"Send Test Webhook"**
5. Verify success message ✅

Done! Webhooks now fire automatically.

---

## Implementation Details

### Files Changed
- ✨ Created: `third-audience/includes/class-ta-webhooks.php` (354 lines)
- 📝 Modified:
  - `admin/views/settings-page.php` (UI)
  - `admin/class-ta-admin.php` (handlers)
  - `includes/class-ta-url-router.php` (markdown.accessed)
  - `includes/class-ta-bot-analytics.php` (bot.detected)
  - `includes/autoload.php` (class registration)

### Git Commit
```
3f8f03c - Implement Issue #10: Basic Webhooks for key events
```

---

## Event Payloads

### markdown.accessed Event
```json
{
  "event": "markdown.accessed",
  "timestamp": "2025-01-21T10:30:00+00:00",
  "site_url": "https://example.com",
  "data": {
    "bot_type": "GPTBot",
    "bot_name": "GPT (OpenAI)",
    "url": "https://example.com/my-post.md",
    "post_id": 123,
    "post_title": "My Article",
    "cache_status": "MISS",
    "response_time": 245
  }
}
```

### bot.detected Event
```json
{
  "event": "bot.detected",
  "timestamp": "2025-01-21T14:20:00+00:00",
  "site_url": "https://example.com",
  "data": {
    "bot_type": "ClaudeBot",
    "bot_name": "Claude (Anthropic)",
    "bot_color": "#D97757"
  }
}
```

---

## API Reference

### TA_Webhooks Class

```php
$webhooks = TA_Webhooks::get_instance();

// Check status
$webhooks->is_enabled();              // Returns: bool
$webhooks->get_webhook_url();         // Returns: string

// Configure
$webhooks->set_enabled( true );       // Returns: bool
$webhooks->set_webhook_url( $url );   // Returns: bool

// Fire events
$webhooks->fire_markdown_accessed( [
    'bot_type' => 'string',
    'bot_name' => 'string',
    'url' => 'string',
    'post_id' => int,
    'post_title' => 'string',
    'cache_status' => 'string',
    'response_time' => int,
] );

$webhooks->fire_bot_detected( [
    'type' => 'string',
    'name' => 'string',
    'color' => 'string',
] );

// Test
$result = $webhooks->test_webhook();
// Returns: ['success' => bool, 'message' => string, 'status_code' => int]
```

---

## Common Use Cases

### 1. Slack Notifications
Use Zapier or Make.com to forward webhooks to Slack

### 2. Analytics Dashboard
Log to Google Analytics, Mixpanel, or custom service

### 3. Bot Allowlist
Forward bot.detected to your firewall rules

### 4. Email Alerts
Send email when new bot detected

### 5. Database Logging
Store webhooks in your database for analysis

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Test webhook fails | Verify endpoint exists, uses HTTPS, responds 200-299 |
| No webhooks firing | Check "Enable Webhooks" is checked |
| Timeout error | Endpoint takes >10 seconds, optimize receiver |
| Can't find logs | Check Settings > Logs tab, search "Webhook" |

---

## Documentation Files

| File | Purpose | Audience |
|------|---------|----------|
| [WEBHOOK_QUICKSTART.md](WEBHOOK_QUICKSTART.md) | 5-min setup guide | Users |
| [WEBHOOK_IMPLEMENTATION.md](WEBHOOK_IMPLEMENTATION.md) | Technical details | Developers |
| [WEBHOOK_FEATURES.md](WEBHOOK_FEATURES.md) | Complete reference | Everyone |
| [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md) | Summary & checklist | Developers |
| [webhook-test-endpoint.php](webhook-test-endpoint.php) | Test endpoint | Developers/Testing |
| [WEBHOOK_INDEX.md](WEBHOOK_INDEX.md) | This file | Navigation |

---

## Integration Points

The webhook system integrates at:

1. **URL Router** (`class-ta-url-router.php`)
   - Fires `markdown.accessed` on bot markdown access
   - Includes cache status, response time, and post details

2. **Bot Analytics** (`class-ta-bot-analytics.php`)
   - Fires `bot.detected` when new bot first visits
   - Uses 24-hour deduplication to prevent spam

3. **Admin Settings** (`admin/class-ta-admin.php`)
   - Manages webhook configuration
   - Handles test webhook requests

4. **Autoloader** (`includes/autoload.php`)
   - Registers TA_Webhooks class
   - Preloads for frontend context

---

## Security

- **HTTPS Required** - Non-HTTPS URLs rejected
- **User-Agent** - Include "Third Audience" for verification
- **Timeout** - 10-second max (protects against slow endpoints)
- **Retry** - 1 automatic retry on failure
- **Logging** - All attempts logged to Settings > Logs

---

## Performance

**When disabled (default):**
- Zero overhead
- No impact on page load

**When enabled:**
- ~5-10ms per bot request (network-dependent)
- 10-second timeout max
- Non-blocking delivery

---

## Features

✅ Webhook URL configuration
✅ Enable/disable toggle
✅ Two key events (markdown.accessed, bot.detected)
✅ Test webhook button
✅ JSON payload delivery
✅ Automatic retry on failure
✅ Comprehensive logging
✅ HTTPS-only URLs
✅ Bot deduplication (24h)
✅ Admin settings UI

---

## Settings

**WordPress Options:**
- `ta_webhooks_enabled` - Enable/disable webhooks
- `ta_webhook_url` - Webhook endpoint URL

**Admin Page:**
- Location: Settings > Webhooks tab
- Group: ta_settings

---

## Testing Checklist

- [ ] Enable webhooks in Settings
- [ ] Enter test webhook URL (webhook.site)
- [ ] Click "Send Test Webhook"
- [ ] Verify test payload received
- [ ] Access .md URL from bot
- [ ] Check markdown.accessed webhook fired
- [ ] Wait 24h, access with same bot
- [ ] Verify bot.detected NOT fired (dedup working)
- [ ] Access with new bot
- [ ] Verify bot.detected fired
- [ ] Check Settings > Logs for delivery status

---

## FAQ

**Q: Will this slow down my site?**
A: No. Webhooks are non-blocking, max 10-second timeout, disabled by default.

**Q: Can I use multiple webhook URLs?**
A: Not yet - currently 1 URL only. Workaround: use webhook distributor service.

**Q: How often do webhooks fire?**
A: `markdown.accessed` on every bot access. `bot.detected` once per bot per 24h.

**Q: Are webhooks logged?**
A: Yes - all delivery attempts logged to Settings > Logs tab.

**Q: What if my endpoint is down?**
A: Webhooks fail gracefully, logged as warnings, site continues working.

---

## Future Enhancements

- Multiple webhook URLs
- Event filtering
- HMAC signing
- Delivery history UI
- Advanced retry strategies
- Custom headers
- Batch delivery
- Dead-letter queue

---

## Support

1. Check [WEBHOOK_QUICKSTART.md](WEBHOOK_QUICKSTART.md) for setup help
2. Review [WEBHOOK_FEATURES.md](WEBHOOK_FEATURES.md) for detailed reference
3. Check Settings > Logs tab for delivery status
4. Use [webhook-test-endpoint.php](webhook-test-endpoint.php) for testing

---

## Version Info

- **Feature Added:** v2.1.0
- **Requires:** WordPress 5.0+, PHP 7.4+
- **Status:** Stable
- **Last Updated:** 2026-01-21

---

**Ready to get started?** → [WEBHOOK_QUICKSTART.md](WEBHOOK_QUICKSTART.md)
