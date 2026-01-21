# Webhook Implementation - Issue #10

## Overview

Basic webhooks have been implemented for the Third Audience plugin with support for two key events:
1. **markdown.accessed** - Fires when a bot accesses markdown content
2. **bot.detected** - Fires when a new bot is detected for the first time

## Features Implemented

### 1. Webhook Manager Class (`TA_Webhooks`)
Location: `/third-audience/includes/class-ta-webhooks.php`

**Core Methods:**
- `is_enabled()` - Check if webhooks are enabled
- `get_webhook_url()` - Get configured webhook URL
- `set_webhook_url($url)` - Set/update webhook URL
- `set_enabled($enabled)` - Enable/disable webhooks
- `fire_markdown_accessed($data)` - Fire markdown access event
- `fire_bot_detected($bot_info)` - Fire bot detection event
- `test_webhook()` - Test webhook delivery to endpoint

**Features:**
- Singleton pattern for global access
- Simple POST delivery with JSON payloads
- 1 retry on delivery failure
- 10-second request timeout
- Proper error logging and reporting

### 2. Settings UI

**Location:** Settings > Webhooks tab (`admin/views/settings-page.php`)

**Configuration:**
- Enable/disable webhooks toggle
- Webhook URL input field (HTTPS validated)
- List of webhook events with descriptions
- Example webhook payload display
- Security notes and best practices
- Test webhook button with inline result display

**Settings Registered:**
- `ta_webhooks_enabled` (boolean)
- `ta_webhook_url` (URL)

### 3. Admin Handlers

**Location:** `admin/class-ta-admin.php`

**New Handlers:**
- `handle_save_webhook_settings()` - Save webhook configuration
- `handle_test_webhook()` - Test webhook delivery

**Settings Registration:**
```php
register_setting( 'ta_settings', 'ta_webhooks_enabled', ... );
register_setting( 'ta_settings', 'ta_webhook_url', ... );
```

### 4. Event Integration

#### markdown.accessed Event
**Fired From:** `TA_URL_Router::track_bot_visit()`

**Payload Structure:**
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

**Triggers:** Every time a bot accesses markdown content via `.md` URL

#### bot.detected Event
**Fired From:** `TA_Bot_Analytics::track_visit()`

**Payload Structure:**
```json
{
  "event": "bot.detected",
  "timestamp": "2025-01-21T10:30:00+00:00",
  "site_url": "https://example.com",
  "data": {
    "bot_type": "GPTBot",
    "bot_name": "GPT (OpenAI)",
    "bot_color": "#10A37F"
  }
}
```

**Triggers:** Once per 24 hours when a new bot is first detected (uses transient-based deduplication)

### 5. Webhook Delivery

**HTTP Details:**
- Method: POST
- Content-Type: application/json
- User-Agent: Third Audience/2.x.x
- Timeout: 10 seconds
- Retry: 1 automatic retry on failure
- Accepted Status Codes: 200-299

**Error Handling:**
- Network errors trigger one automatic retry
- Non-2xx responses are logged as warnings
- All delivery attempts are logged via TA_Logger
- Failures don't interrupt request processing

### 6. Bot Detection Deduplication

**bot.detected Event Deduplication:**
- Uses WordPress transients for tracking recently notified bots
- Transient key: `ta_webhook_bot_notified_{bot_type}`
- Expiration: 24 hours
- Only one notification per bot type per 24-hour period

## File Changes

### New Files
- `/third-audience/includes/class-ta-webhooks.php` - Webhook manager class

### Modified Files
1. **admin/views/settings-page.php**
   - Added "Webhooks" tab to tab list
   - Added complete webhooks tab UI with configuration form
   - Includes example payload, security notes, and test functionality

2. **admin/class-ta-admin.php**
   - Added webhook settings registration
   - Added `handle_save_webhook_settings()` method
   - Added `handle_test_webhook()` method
   - Registered admin_post hooks for webhook handlers

3. **includes/class-ta-url-router.php**
   - Added TA_Webhooks instance to constructor
   - Updated `track_bot_visit()` to fire `markdown.accessed` webhook

4. **includes/class-ta-bot-analytics.php**
   - Added TA_Webhooks instance to constructor
   - Updated `track_visit()` to:
     - Check if bot is new (first visit)
     - Fire `bot.detected` webhook for new bots
     - Query count of previous visits to determine if new

5. **includes/autoload.php**
   - Added TA_Webhooks to class map for fast lookup
   - Added TA_Webhooks to frontend preload context

## Usage

### Configuration
1. Go to Settings > Webhooks
2. Enable webhooks checkbox
3. Enter webhook URL (must be HTTPS)
4. Click "Save Webhook Settings"
5. Use "Send Test Webhook" to verify connectivity

### Testing
1. Click "Send Test Webhook" button
2. Check webhook endpoint for test payload
3. Verify response code displayed in admin

### Monitoring
- All webhook activity is logged to TA_Logger
- Check Logs tab for delivery status
- Search logs for "Webhook" keyword to filter events

## Security Considerations

1. **HTTPS Required:** Webhook URLs should always use HTTPS
2. **Endpoint Verification:** Recipients should verify User-Agent header contains "Third Audience"
3. **Timeout Handling:** Endpoints should respond within 10 seconds
4. **Secret Storage:** Webhook URL is stored unencrypted - treat like a password
5. **Rate Limiting:** No built-in rate limiting; implement on receiver side if needed

## Example Webhook Receiver (PHP)

```php
<?php
// Verify webhook origin
if ( strpos( $_SERVER['HTTP_USER_AGENT'] ?? '', 'Third Audience' ) === false ) {
    http_response_code( 403 );
    exit;
}

// Get payload
$payload = json_decode( file_get_contents( 'php://input' ), true );

// Log event
error_log( 'Webhook received: ' . $payload['event'] );

// Process based on event type
switch ( $payload['event'] ) {
    case 'markdown.accessed':
        // Handle bot access
        $bot = $payload['data'];
        error_log( "Bot {$bot['bot_name']} accessed {$bot['url']}" );
        break;

    case 'bot.detected':
        // Handle new bot detected
        $bot = $payload['data'];
        error_log( "New bot detected: {$bot['bot_name']}" );
        break;
}

// Return success
http_response_code( 200 );
echo 'OK';
?>
```

## Limitations & Future Enhancements

### Current Limitations
- Only 1 webhook URL (no multiple webhooks)
- No webhook history/log viewer
- No webhook event filtering (all/nothing)
- No signature/HMAC verification
- No dead-letter queue for failed webhooks

### Potential Enhancements
1. Multiple webhook URLs with individual enable/disable
2. Event-specific filtering (only receive certain events)
3. HMAC-SHA256 signing for webhook authenticity
4. Webhook delivery history viewer
5. Exponential backoff retry strategy
6. Custom headers support
7. Webhook test history
8. Rate limiting configuration

## Testing Checklist

- [ ] Enable webhooks in settings
- [ ] Enter valid HTTPS webhook URL
- [ ] Click "Send Test Webhook"
- [ ] Verify test payload received at endpoint
- [ ] Access a .md URL from a bot (test with curl, GPTBot header, etc.)
- [ ] Verify markdown.accessed webhook fired
- [ ] Check response time, cache status, and other data
- [ ] Wait 24 hours, access with same bot again
- [ ] Verify bot.detected NOT fired (deduplication working)
- [ ] Access with new bot user-agent
- [ ] Verify bot.detected webhook fired
- [ ] Check TA_Logger for webhook delivery logs

## Commit Information

**Commit:** `3f8f03c`
**Message:** "Implement Issue #10: Basic Webhooks for key events"
**Changed Files:** 8 modified, 1 created
**Lines:** +450 approx new lines, well-documented with phpdoc
