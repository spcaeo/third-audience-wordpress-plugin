# Webhooks Quick Start Guide

## What Are Webhooks?

Webhooks are HTTP callbacks that send real-time notifications when events occur on your site. The Third Audience plugin fires webhooks for:

1. **markdown.accessed** - Every time a bot requests markdown content
2. **bot.detected** - When a new bot visits your site for the first time

## 5-Minute Setup

### Step 1: Get a Webhook URL

You have two options:

**Option A: Use webhook.site (Instant, No Code)**
1. Go to https://webhook.site
2. Copy the unique URL (e.g., `https://webhook.site/abc123def456`)
3. This is your webhook URL

**Option B: Self-hosted Endpoint**
1. Copy `webhook-test-endpoint.php` to your server
2. Your webhook URL is: `https://your-domain.com/webhook-test-endpoint.php`

### Step 2: Enable Webhooks

1. WordPress Admin > Settings > Third Audience > **Webhooks** tab
2. Check: **"Enable Webhooks"**
3. Paste your webhook URL into the **Webhook URL** field
4. Click **"Save Webhook Settings"**

### Step 3: Test It

1. Still on Webhooks tab, click **"Send Test Webhook"**
2. You should see: "Webhook delivered successfully (HTTP 200)"
3. If using webhook.site, refresh the page to see the test payload

### Step 4: Watch Events

Now whenever:
- A bot accesses your markdown content → `markdown.accessed` webhook fires
- A new bot visits for the first time → `bot.detected` webhook fires (once per 24h per bot)

## Example Payloads

### markdown.accessed Event
```json
{
  "event": "markdown.accessed",
  "timestamp": "2025-01-21T10:30:00+00:00",
  "site_url": "https://example.com",
  "data": {
    "bot_type": "GPTBot",
    "bot_name": "GPT (OpenAI)",
    "url": "https://example.com/my-article.md",
    "post_id": 123,
    "post_title": "How to Use Webhooks",
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

## Common Use Cases

### 1. Send Email Alert on New Bot
Use a service like Zapier, Make.com, or custom script:
```
When webhook.site receives POST with event=bot.detected
→ Send email "New bot: {bot_name} just visited!"
```

### 2. Log to Analytics Service
Forward webhooks to:
- Google Analytics (custom events)
- Mixpanel
- Amplitude
- Any analytics platform with webhooks

### 3. Slack Notifications
Use Slack's incoming webhooks:
```
POST to your Slack webhook:
{
  "text": "Bot {bot_name} accessed {url} (cache: {cache_status})"
}
```

### 4. Database Logging
Your own API endpoint that:
- Receives Third Audience webhooks
- Stores in your database
- Creates reports/dashboards

### 5. Automated Actions
- Auto-block suspicious bots
- Add to VIP list (Claude, GPT, etc.)
- Trigger cache warming
- Update firewall rules

## Webhook URLs Support

### Supported
- ✅ HTTPS (required for security)
- ✅ Dynamic URLs with query parameters
- ✅ Basic auth in URL: `https://user:pass@example.com/webhook`
- ✅ Any HTTP service accepting POST

### Not Supported
- ❌ HTTP (non-HTTPS) - rejected
- ❌ IP addresses - must use domain
- ❌ Authentication headers (use basic auth in URL instead)
- ❌ Custom headers (uses standard Content-Type: application/json)

## Testing & Debugging

### Using webhook.site
1. Go to https://webhook.site
2. Copy your unique URL
3. Enter in Third Audience Webhooks settings
4. Click "Send Test Webhook"
5. Refresh webhook.site to see payload

### Using curl (test local endpoint)
```bash
curl -X POST https://your-endpoint.com/webhook \
  -H "Content-Type: application/json" \
  -H "User-Agent: Third Audience/2.1.0" \
  -d '{"event":"markdown.accessed","timestamp":"2025-01-21T10:30:00Z","data":{...}}'
```

### Check Plugin Logs
1. Settings > Third Audience > **Logs** tab
2. Search for "Webhook"
3. View delivery status, errors, and timing

## Troubleshooting

| Issue | Solution |
|-------|----------|
| "Webhook delivery failed" | Check endpoint is accessible, using HTTPS, and responds with 200-299 |
| "Connection timeout" | Endpoint takes > 10 seconds - optimize your receiver |
| No webhooks being fired | Check "Enable Webhooks" is checked |
| Test works, but live events don't | Verify endpoint can handle POST with JSON body |
| Missing `User-Agent` | Expected header is present - verify your endpoint processes it |

## Security Best Practices

1. **Use HTTPS Always** - Never use HTTP
2. **Verify Origin** - Check User-Agent contains "Third Audience"
3. **Validate Timestamps** - Prevent replay attacks
4. **Keep URL Secret** - Treat like a password
5. **Set Timeout** - Respond within 10 seconds
6. **Rate Limiting** - Implement on receiver if needed

## Webhook Behavior

### Delivery Details
- **Method:** HTTP POST
- **Content-Type:** application/json
- **Timeout:** 10 seconds
- **Retry:** 1 automatic retry on failure
- **User-Agent:** Third Audience/2.x.x

### Deduplication
- `bot.detected` fires once per bot per 24 hours
- Uses WordPress transients for tracking
- Prevents webhook spam for repeated bot visits

### Logging
- All delivery attempts logged to TA_Logger
- Check Settings > Logs tab for details
- Includes request/response times, status codes, errors

## Advanced: Custom Receiver

### Simple PHP Endpoint
```php
<?php
// Verify source
if ( stripos( $_SERVER['HTTP_USER_AGENT'] ?? '', 'Third Audience' ) === false ) {
    http_response_code( 403 );
    exit;
}

// Get payload
$payload = json_decode( file_get_contents( 'php://input' ), true );

// Process
switch ( $payload['event'] ) {
    case 'markdown.accessed':
        $bot = $payload['data'];
        file_put_contents( 'bots.log', $bot['bot_name'] . " visited " . date( 'Y-m-d H:i:s' ) . "\n", FILE_APPEND );
        break;

    case 'bot.detected':
        echo "New bot: {$payload['data']['bot_name']}";
        break;
}

http_response_code( 200 );
?>
```

### Using Zapier
1. Create Zapier account (zapier.com)
2. Create "Webhook by Zapier" trigger
3. Copy webhook URL from Zapier
4. Use in Third Audience settings
5. Create Zapier actions (email, Slack, etc.)

## FAQ

**Q: What if my endpoint goes down?**
A: Webhooks will fail and be logged. Fix the endpoint, they'll work again.

**Q: Can I have multiple webhook URLs?**
A: Not yet - currently only 1 URL supported. Workaround: use a webhook distributor service.

**Q: How often does bot.detected fire?**
A: Once per bot type per 24-hour period (deduplicated).

**Q: Do webhooks fire for every page request?**
A: No - only for `.md` (markdown) URLs that trigger bot analytics.

**Q: What if I disable webhooks?**
A: All events are silently skipped. No errors or logs.

**Q: Can I sign webhooks (HMAC)?**
A: Not yet - verify using User-Agent header instead.

**Q: How do I disable a webhook temporarily?**
A: Uncheck "Enable Webhooks" in settings, or set URL to empty.

**Q: Where's my webhook delivery history?**
A: Check Settings > Logs tab, search for "Webhook".

## Need Help?

1. Check the logs: Settings > Logs tab
2. Try webhook.site to see the actual payload
3. Review this guide's troubleshooting section
4. Check plugin's GitHub issues for similar problems

## Next Steps

- Set up your webhook endpoint
- Test with "Send Test Webhook"
- Monitor first few events in plugin logs
- Build your custom automation on top!
