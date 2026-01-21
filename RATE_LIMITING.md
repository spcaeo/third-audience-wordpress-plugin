# Rate Limiting Implementation

## Overview

The Third Audience plugin now includes advanced rate limiting per bot type. This feature prevents abuse and ensures fair usage of your markdown content by different AI bots.

## Features

### 1. Per-Priority Rate Limiting

Rate limits are configured based on bot priority levels:

- **High Priority** (e.g., ClaudeBot, GPTBot, PerplexityBot)
  - Default: Unlimited (0 requests/minute, 0 requests/hour)
  - These are premium AI services that typically respect content

- **Medium Priority** (e.g., Bytespider, Google-Extended, FacebookBot)
  - Default: 60 requests/minute, 1000 requests/hour
  - Balanced limits for regular AI crawlers

- **Low Priority** (e.g., Unknown bots, custom crawlers)
  - Default: 10 requests/minute, 100 requests/hour
  - Conservative limits for unverified bots

- **Blocked Priority**
  - No access allowed (403 Forbidden)
  - Handled before rate limiting

### 2. Dual Window Rate Limiting

- **Per-Minute Limits**: Short-term burst protection
- **Per-Hour Limits**: Long-term usage control
- Both limits are tracked independently
- Request is blocked if either limit is exceeded

### 3. Standard Rate Limit Headers

All responses include standard rate limit headers:

```
X-RateLimit-Limit: 60              # Requests allowed per window
X-RateLimit-Remaining: 45          # Requests remaining
X-RateLimit-Reset: 1705932000      # Unix timestamp when limit resets
```

429 (Too Many Requests) responses also include:

```
Retry-After: 42                    # Seconds until limit resets
```

### 4. Analytics Integration

- **Rate Limit Violations Tracking**: All rate limit violations are logged to analytics
- **Violation Statistics**: View violations by bot type and IP address
- **Recent Violations**: See the most recent rate limit violations
- **Dashboard Widget**: Rate limit violations appear in Bot Analytics page

## Configuration

### Admin Interface

1. Navigate to **Bot Analytics > Bot Management**
2. Scroll to the **Rate Limits** section
3. Configure limits per priority level:
   - Set to `0` for unlimited
   - Set specific numbers for requests per minute/hour

### Programmatic Configuration

Rate limits are stored in the `ta_bot_rate_limits` option:

```php
$rate_limits = array(
    'high' => array(
        'per_minute' => 0,    // Unlimited
        'per_hour'   => 0,
    ),
    'medium' => array(
        'per_minute' => 60,
        'per_hour'   => 1000,
    ),
    'low' => array(
        'per_minute' => 10,
        'per_hour'   => 100,
    ),
);

update_option( 'ta_bot_rate_limits', $rate_limits );
```

## Technical Implementation

### Architecture

```
Request Flow:
┌─────────────────────┐
│  Incoming Request   │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│   Bot Detection     │  (identify bot type and priority)
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Check Blocked?     │  (403 if blocked)
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Rate Limit Check   │  (check minute & hour limits)
└──────────┬──────────┘
           │
           ├─ Rate Limited? → 429 Response + Log Violation
           │
           ▼
┌─────────────────────┐
│  Increment Counter  │  (update minute & hour counters)
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Serve Content      │  (200 OK + rate limit headers)
└─────────────────────┘
```

### Storage Mechanism

Rate limits use WordPress transients for automatic expiration:

- **Minute counter**: `ta_ratelimit_{bot_type}_{ip}_minute` (TTL: 60 seconds)
- **Hour counter**: `ta_ratelimit_{bot_type}_{ip}_hour` (TTL: 3600 seconds)

This provides:
- Automatic cleanup (no manual maintenance needed)
- Fast access (no database overhead)
- Atomic operations (no race conditions)

### Key Classes

#### `TA_Rate_Limiter`

Located in: `includes/class-ta-rate-limiter.php`

Key methods:
- `get_bot_rate_limits($bot_type, $priority)` - Get limits for a bot
- `check_bot_rate_limit($bot_type, $priority, $ip)` - Check if rate limited
- `increment_bot_counter($bot_type, $ip)` - Increment request counters
- `get_rate_limit_violations($limit)` - Get recent violations
- `get_violation_stats()` - Get violation statistics

#### `TA_URL_Router`

Modified to integrate rate limiting:
- Check rate limits after bot detection
- Return 429 response when limited
- Add rate limit headers to all responses
- Log violations to analytics

## Testing

### Manual Testing with curl

Use the provided test script:

```bash
./test-rate-limiting.sh
```

### Manual curl Commands

Test low priority bot (should be limited at 10/min):

```bash
# Make 12 requests rapidly
for i in {1..12}; do
  curl -I http://your-site.com/test.md \
    -H "User-Agent: TestBot/1.0"
  sleep 0.1
done
```

Expected results:
- First 10 requests: 200 OK
- Requests 11-12: 429 Too Many Requests

### Verify Headers

```bash
curl -I http://your-site.com/test.md \
  -H "User-Agent: ClaudeBot/1.0"
```

Expected headers:
```
X-RateLimit-Limit: 0
X-RateLimit-Remaining: 999999
```

### View Violations

1. Go to WordPress Admin
2. Navigate to **Bot Analytics**
3. Scroll to **Rate Limit Violations** section
4. View statistics and recent violations

## Best Practices

### Recommended Limits

| Bot Type | Per Minute | Per Hour | Rationale |
|----------|-----------|----------|-----------|
| High Priority | 0 (unlimited) | 0 (unlimited) | Premium AI services, trusted |
| Medium Priority | 60 | 1000 | Regular crawlers, reasonable usage |
| Low Priority | 10 | 100 | Unknown bots, conservative approach |

### Monitoring

- Check **Rate Limit Violations** weekly
- Adjust limits based on legitimate bot behavior
- Block bots with excessive violations

### Custom Configuration

For specific high-traffic bots, you can:
1. Identify the bot in Bot Management
2. Set its priority to "High" for unlimited access
3. Or create custom limits via the `ta_bot_rate_limits` filter

## Troubleshooting

### Legitimate Bot Being Rate Limited

**Problem**: A legitimate bot (e.g., GPTBot) is getting 429 responses.

**Solution**:
1. Go to Bot Management
2. Find the bot in the list
3. Change its priority to "High" (unlimited)
4. Save configuration

### Rate Limits Not Working

**Check**:
1. Bot is correctly identified (check Bot Analytics)
2. Bot priority is not "High" (unlimited)
3. Rate limits are configured (Bot Management > Rate Limits)
4. WordPress transients are working (check System Health)

### High Memory Usage

If you have many bots and IPs being tracked:
1. Transients automatically expire (no manual cleanup needed)
2. Consider increasing rate limits to reduce tracking overhead
3. Use a persistent object cache (Redis/Memcached) for better performance

## Security Considerations

### IP Spoofing

Rate limits are enforced per IP address. Behind proxies/CDNs:
- Cloudflare: Uses `CF-Connecting-IP` header
- General: Fallback to `X-Forwarded-For`
- Local: Uses `REMOTE_ADDR`

### DDoS Protection

Rate limiting provides basic DDoS protection but is not a replacement for:
- WAF (Web Application Firewall)
- CDN-level DDoS protection (e.g., Cloudflare)
- Server-level rate limiting (e.g., nginx limit_req)

### Privacy

IP addresses are:
- Stored temporarily in transients (auto-expire)
- Logged in analytics for violations
- Can be anonymized via WordPress settings

## API Reference

### Filters

```php
// Customize rate limits for specific bot
add_filter( 'ta_bot_rate_limits', function( $limits, $bot_type, $priority ) {
    // Give GPTBot higher limits
    if ( $bot_type === 'GPTBot' ) {
        $limits['per_minute'] = 120;
        $limits['per_hour'] = 2000;
    }
    return $limits;
}, 10, 3 );
```

### Actions

```php
// Hook into rate limit violation
add_action( 'ta_rate_limit_violation', function( $bot_type, $ip, $limit_type ) {
    // Send alert, log to external service, etc.
}, 10, 3 );
```

## Changelog

### Version 2.1.0 (Issue #8)
- Added per-bot-type rate limiting
- Implemented dual window limits (per minute and per hour)
- Added rate limit headers to all responses
- Integrated violations tracking in analytics
- Added admin UI for configuring limits
- Added violation statistics dashboard

## Support

For issues or questions:
1. Check Bot Analytics for violation logs
2. Review System Health for transient functionality
3. Enable debug logging in settings
4. Check WordPress debug.log for errors
