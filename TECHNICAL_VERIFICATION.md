# Technical Verification Guide
## Database & Integration Tests for Third Audience v2.1.0

---

## Database Verification Commands

### 1. Verify Table Exists
```sql
SHOW TABLES LIKE 'wp_ta_bot_analytics';
-- Expected: wp_ta_bot_analytics
```

### 2. Check Table Structure
```sql
DESCRIBE wp_ta_bot_analytics;
-- Should return all 17 columns with proper types
```

### 3. Verify Column Definitions
```sql
SHOW FULL COLUMNS FROM wp_ta_bot_analytics;
```

**Expected Result:**
```
id - bigint(20) unsigned - NOT NULL - auto_increment
bot_type - varchar(50) - NOT NULL
bot_name - varchar(100) - NOT NULL
user_agent - text - NOT NULL
url - varchar(500) - NOT NULL
post_id - bigint(20) unsigned - YES
post_type - varchar(50) - YES
post_title - text - YES
request_method - varchar(20) - NOT NULL - DEFAULT 'md_url'
cache_status - varchar(20) - NOT NULL - DEFAULT 'MISS'
response_time - int(11) - YES
response_size - int(11) - YES
ip_address - varchar(45) - YES
referer - text - YES
country_code - varchar(2) - YES
visit_timestamp - datetime - NOT NULL
created_at - datetime - NOT NULL - DEFAULT CURRENT_TIMESTAMP
```

### 4. Check Indexes
```sql
SHOW INDEXES FROM wp_ta_bot_analytics;
```

**Expected Indexes:**
- PRIMARY KEY on `id`
- INDEX on `bot_type`
- INDEX on `post_id`
- INDEX on `visit_timestamp`
- INDEX on `bot_type, visit_timestamp` (composite)

### 5. Count Total Records
```sql
SELECT COUNT(*) AS total_records FROM wp_ta_bot_analytics;
```

### 6. Query Recent Entries
```sql
SELECT
    id, bot_type, bot_name, url, cache_status,
    response_time, visit_timestamp
FROM wp_ta_bot_analytics
ORDER BY visit_timestamp DESC
LIMIT 10;
```

### 7. Check for NULL Values in Required Fields
```sql
SELECT
    'bot_type NULL' as issue, COUNT(*) as count
FROM wp_ta_bot_analytics
WHERE bot_type IS NULL
UNION ALL
SELECT
    'url NULL', COUNT(*)
FROM wp_ta_bot_analytics
WHERE url IS NULL
UNION ALL
SELECT
    'bot_name NULL', COUNT(*)
FROM wp_ta_bot_analytics
WHERE bot_name IS NULL;
-- Should return 0 for all
```

### 8. Cache Status Distribution
```sql
SELECT
    cache_status,
    COUNT(*) as count,
    ROUND(COUNT(*) * 100 / (SELECT COUNT(*) FROM wp_ta_bot_analytics), 2) as percentage
FROM wp_ta_bot_analytics
GROUP BY cache_status
ORDER BY count DESC;
```

### 9. Bot Type Distribution
```sql
SELECT
    bot_type,
    bot_name,
    COUNT(*) as visits,
    COUNT(DISTINCT ip_address) as unique_ips,
    COUNT(DISTINCT url) as unique_urls,
    ROUND(AVG(response_time), 2) as avg_response_time
FROM wp_ta_bot_analytics
GROUP BY bot_type, bot_name
ORDER BY visits DESC;
```

### 10. Recent Visitor Countries
```sql
SELECT
    country_code,
    COUNT(*) as visits,
    COUNT(DISTINCT bot_type) as bot_types
FROM wp_ta_bot_analytics
WHERE country_code IS NOT NULL
GROUP BY country_code
ORDER BY visits DESC
LIMIT 10;
```

---

## WordPress Options Verification

### 1. Check Bot Config Option
```php
$bot_config = get_option('ta_bot_config', array());
echo '<pre>';
print_r($bot_config);
echo '</pre>';

// Expected structure:
// Array (
//     [blocked_bots] => Array (...)
//     [custom_bots] => Array (...)
//     [bot_priorities] => Array (...)
// )
```

### 2. Check Cache Stats Option
```php
$cache_stats = get_option('ta_cache_stats', array());
echo '<pre>';
print_r($cache_stats);
echo '</pre>';
```

### 3. Check Webhook Settings
```php
$webhooks_enabled = get_option('ta_webhooks_enabled', false);
$webhook_url = get_option('ta_webhook_url', '');

echo 'Webhooks Enabled: ' . ($webhooks_enabled ? 'Yes' : 'No') . "\n";
echo 'Webhook URL: ' . $webhook_url . "\n";
```

### 4. Check Rate Limit Settings
```php
$rate_limit_settings = get_option('ta_rate_limit_settings', array());
echo '<pre>';
print_r($rate_limit_settings);
echo '</pre>';

// Expected:
// Array (
//     [enabled] => 1
//     [window] => 60
//     [max_requests] => 100
//     [by_ip] => 1
//     [by_user] => 0
// )
```

### 5. Check Bot Rate Limits
```php
$bot_limits = get_option('ta_bot_rate_limits', array());
echo '<pre>';
print_r($bot_limits);
echo '</pre>';
```

### 6. Check Database Version
```php
$db_version = get_option('ta_bot_analytics_db_version');
echo 'Analytics DB Version: ' . $db_version . "\n";
// Expected: 1.0.0
```

### 7. List All Third Audience Options
```php
global $wpdb;
$ta_options = $wpdb->get_results(
    "SELECT option_name, option_value FROM {$wpdb->options}
     WHERE option_name LIKE 'ta_%'
     ORDER BY option_name"
);
foreach ($ta_options as $option) {
    echo $option->option_name . "\n";
}
```

---

## PHP Class Syntax Verification

### 1. Verify All Classes Compile
```bash
# Check Rate Limiter
php -l /path/to/third-audience/includes/class-ta-rate-limiter.php

# Check Webhooks
php -l /path/to/third-audience/includes/class-ta-webhooks.php

# Check Bot Analytics
php -l /path/to/third-audience/includes/class-ta-bot-analytics.php

# Check Admin
php -l /path/to/third-audience/admin/class-ta-admin.php
```

### 2. Verify Autoloader
```php
require_once 'third-audience/includes/autoload.php';

// Check if autoloader is registered
$autoloader = TA_Autoloader::get_instance();
echo 'Autoloader loaded\n';

// Verify classes exist
echo 'TA_Rate_Limiter: ' . (class_exists('TA_Rate_Limiter', false) ? 'Loaded' : 'Not loaded') . "\n";
echo 'TA_Webhooks: ' . (class_exists('TA_Webhooks', false) ? 'Loaded' : 'Not loaded') . "\n";
echo 'TA_Bot_Analytics: ' . (class_exists('TA_Bot_Analytics', false) ? 'Loaded' : 'Not loaded') . "\n";
```

### 3. Verify Class Methods
```php
// Check TA_Rate_Limiter methods
$rl = new TA_Rate_Limiter();
echo method_exists($rl, 'check') ? '✓ check()' : '✗ check()' . "\n";
echo method_exists($rl, 'is_rate_limited') ? '✓ is_rate_limited()' : '✗ is_rate_limited()' . "\n";
echo method_exists($rl, 'check_bot_rate_limit') ? '✓ check_bot_rate_limit()' : '✗ check_bot_rate_limit()' . "\n";

// Check TA_Webhooks methods
$webhooks = TA_Webhooks::get_instance();
echo method_exists($webhooks, 'fire_markdown_accessed') ? '✓ fire_markdown_accessed()' : '✗ fire_markdown_accessed()' . "\n";
echo method_exists($webhooks, 'fire_bot_detected') ? '✓ fire_bot_detected()' : '✗ fire_bot_detected()' . "\n";
echo method_exists($webhooks, 'test_webhook') ? '✓ test_webhook()' : '✗ test_webhook()' . "\n";

// Check TA_Bot_Analytics methods
$analytics = TA_Bot_Analytics::get_instance();
echo method_exists($analytics, 'detect_bot') ? '✓ detect_bot()' : '✗ detect_bot()' . "\n";
echo method_exists($analytics, 'track_visit') ? '✓ track_visit()' : '✗ track_visit()' . "\n";
echo method_exists($analytics, 'get_summary') ? '✓ get_summary()' : '✗ get_summary()' . "\n";
```

---

## Integration Test Scenarios

### Scenario 1: Bot Visit Detection and Tracking

```php
// Simulate a ClaudeBot request
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; ClaudeBot/1.0; +https://claude.ai)';

$analytics = TA_Bot_Analytics::get_instance();

// 1. Detect bot
$bot_info = $analytics->detect_bot($_SERVER['HTTP_USER_AGENT']);
echo "Bot Detected: " . ($bot_info ? 'Yes' : 'No') . "\n";
if ($bot_info) {
    echo "Bot Type: " . $bot_info['type'] . "\n";
    echo "Bot Name: " . $bot_info['name'] . "\n";
    echo "Priority: " . $bot_info['priority'] . "\n";
}

// 2. Track visit
$visit_id = $analytics->track_visit([
    'bot_type' => $bot_info['type'],
    'bot_name' => $bot_info['name'],
    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
    'url' => 'https://example.com/post/markdown',
    'post_id' => 123,
    'post_type' => 'post',
    'post_title' => 'Test Post',
    'cache_status' => 'HIT',
    'response_time' => 245,
    'response_size' => 5280,
]);

echo "Visit Tracked: " . ($visit_id ? 'ID=' . $visit_id : 'Failed') . "\n";

// 3. Verify in database
global $wpdb;
$record = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}ta_bot_analytics WHERE id = %d",
        $visit_id
    )
);
echo "Record Found: " . ($record ? 'Yes' : 'No') . "\n";
```

### Scenario 2: Webhook Firing on Markdown Access

```php
$webhooks = TA_Webhooks::get_instance();

// Enable webhooks
$webhooks->set_enabled(true);
$webhooks->set_webhook_url('https://webhook.site/unique-id');

// Simulate markdown access
$result = $webhooks->fire_markdown_accessed([
    'bot_type' => 'ClaudeBot',
    'bot_name' => 'Claude (Anthropic)',
    'url' => 'https://example.com/post/markdown',
    'post_id' => 123,
    'post_title' => 'Test Post',
    'cache_status' => 'HIT',
    'response_time' => 245,
]);

echo "Webhook Fired: " . ($result ? 'Success' : 'Failed') . "\n";
```

### Scenario 3: Rate Limiting Check

```php
// Simulate API requests
$_SERVER['REMOTE_ADDR'] = '192.168.1.1';
$rate_limiter = new TA_Rate_Limiter(
    60,   // window: 60 seconds
    5     // max_requests: 5 per window
);

// Enable rate limiting
$rate_limiter->save_settings([
    'enabled' => true,
    'window' => 60,
    'max_requests' => 5,
    'by_ip' => true,
]);

// Make requests
for ($i = 1; $i <= 7; $i++) {
    $limited = $rate_limiter->is_rate_limited('test-bot');
    echo "Request $i: " . ($limited ? 'RATE_LIMITED' : 'ALLOWED') . "\n";

    if (!$limited) {
        $rate_limiter->record_request('test-bot');
    }
}

// Check final status
$status = $rate_limiter->get_status('test-bot');
echo "\nFinal Status:\n";
echo "Limited: " . ($status['limited'] ? 'Yes' : 'No') . "\n";
echo "Count: " . $status['count'] . "\n";
echo "Remaining: " . $status['remaining'] . "\n";
echo "Reset in: " . $status['reset_in'] . " seconds\n";
```

### Scenario 4: Cache Status Dashboard

```php
$analytics = TA_Bot_Analytics::get_instance();

// Get summary stats
$summary = $analytics->get_summary([
    'date_from' => date('Y-m-d', strtotime('-7 days')),
    'date_to' => date('Y-m-d'),
]);

echo "Analytics Summary (Last 7 Days):\n";
echo "Total Visits: " . $summary['total_visits'] . "\n";
echo "Unique Pages: " . $summary['unique_pages'] . "\n";
echo "Unique Bots: " . $summary['unique_bots'] . "\n";
echo "Cache Hit Rate: " . $summary['cache_hit_rate'] . "%\n";
echo "Avg Response Time: " . $summary['avg_response_time'] . "ms\n";
echo "Total Bandwidth: " . $summary['total_bandwidth'] . " bytes\n";
echo "Trend: " . ($summary['trend_percentage'] > 0 ? '+' : '') . $summary['trend_percentage'] . "%\n";
```

### Scenario 5: Clear Cache

```php
$cache_manager = new TA_Cache_Manager();

// Get current count
global $wpdb;
$before = $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->options}
     WHERE option_name LIKE '_transient_%ta_markdown%'"
);

// Clear cache
$cleared = $cache_manager->clear_all();

// Get new count
$after = $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->options}
     WHERE option_name LIKE '_transient_%ta_markdown%'"
);

echo "Cache Cleared:\n";
echo "Before: " . $before . " items\n";
echo "Cleared: " . $cleared . " items\n";
echo "After: " . $after . " items\n";
```

---

## Error Log Checking

### 1. WordPress Debug Log Location
```bash
# Check if WP_DEBUG_LOG is set
grep -n "WP_DEBUG_LOG" /path/to/wp-config.php

# View WordPress debug log
tail -f /path/to/wp-content/debug.log | grep -i "third-audience\|ta_"
```

### 2. Check for PHP Errors
```bash
# Search for plugin errors
tail -100 /path/to/wp-content/debug.log | grep -i "error"
```

### 3. Check for Rate Limiter Issues
```sql
-- Find recent rate limit events in logs
SELECT * FROM wp_ta_logs
WHERE message LIKE '%rate limit%'
ORDER BY created_at DESC
LIMIT 10;
```

### 4. Check for Webhook Failures
```sql
-- Find webhook delivery issues
SELECT * FROM wp_ta_logs
WHERE message LIKE '%webhook%'
AND level IN ('warning', 'error')
ORDER BY created_at DESC
LIMIT 10;
```

---

## Performance Verification

### 1. Query Performance
```sql
-- Analyze bot_type index
SELECT SQL_CALC_FOUND_ROWS *
FROM wp_ta_bot_analytics
WHERE bot_type = 'ClaudeBot'
LIMIT 1000;
SHOW WARNINGS;

-- Analyze visit_timestamp index
SELECT SQL_CALC_FOUND_ROWS *
FROM wp_ta_bot_analytics
WHERE DATE(visit_timestamp) = CURDATE()
LIMIT 1000;
SHOW WARNINGS;
```

### 2. Rate Limiter Transient Count
```sql
SELECT COUNT(*) as active_rate_limits
FROM wp_options
WHERE option_name LIKE '_transient_ta_rate_limit_%'
AND option_name NOT LIKE '_transient_timeout_%';
```

### 3. Webhook Transient Count (Dedup)
```sql
SELECT COUNT(*) as webhook_dedup_entries
FROM wp_options
WHERE option_name LIKE '_transient_ta_webhook_bot_notified_%'
AND option_name NOT LIKE '_transient_timeout_%';
```

---

## Security Verification

### 1. Check Nonce Verification
```bash
# Search admin file for nonce checks
grep -n "wp_verify_nonce\|verify_nonce_or_die" \
  /path/to/third-audience/admin/class-ta-admin.php

# Should find multiple nonce verifications
```

### 2. Check Data Sanitization
```bash
# Check for sanitization functions
grep -n "sanitize_text_field\|esc_url_raw\|sanitize_email" \
  /path/to/third-audience/includes/class-ta-bot-analytics.php

# Should find consistent usage
```

### 3. Check Database Query Safety
```bash
# Check for prepared statements
grep -n "\$wpdb->prepare" \
  /path/to/third-audience/includes/class-ta-rate-limiter.php

# Should find prepared statements in all DB queries
```

---

## File Integrity Check

### 1. Verify File Permissions
```bash
ls -la /path/to/third-audience/includes/class-ta-*.php
ls -la /path/to/third-audience/admin/class-ta-admin.php

# Should all be readable
```

### 2. Check File Sizes
```bash
du -h /path/to/third-audience/includes/class-ta-rate-limiter.php
# Should be around 16KB

du -h /path/to/third-audience/includes/class-ta-webhooks.php
# Should be around 8KB
```

### 3. Check for Syntax Issues
```bash
# Run syntax check on all files
find /path/to/third-audience -name "*.php" -type f \
  -exec php -l {} \; | grep -i "error"

# Should return no errors
```

---

## Test Verification Checklist

- [ ] Database table exists with all columns
- [ ] All indexes present and optimal
- [ ] WordPress options created and populated
- [ ] Rate limiter class loads and compiles
- [ ] Webhooks class loads and compiles
- [ ] Bot analytics class loads and compiles
- [ ] Admin class loads and compiles
- [ ] Bot detection working (test with known user agents)
- [ ] Analytics tracking working (records inserted)
- [ ] Webhooks firing (check webhook.site or logs)
- [ ] Rate limiting working (429 response when limit exceeded)
- [ ] Cache clearing working (records deleted)
- [ ] Admin nonce verification enabled
- [ ] Data properly sanitized
- [ ] Database queries using prepared statements
- [ ] No PHP syntax errors
- [ ] No WordPress debug errors
- [ ] Autoloader functioning correctly
- [ ] All required methods present in classes
- [ ] Integration tests passing

---

## Command-Line Test Suite

```bash
#!/bin/bash

echo "=== Third Audience Plugin Verification ==="
echo ""

# Test 1: PHP Syntax
echo "Test 1: PHP Syntax Check"
php -l third-audience/includes/class-ta-rate-limiter.php && echo "✓ Rate Limiter" || echo "✗ Rate Limiter"
php -l third-audience/includes/class-ta-webhooks.php && echo "✓ Webhooks" || echo "✗ Webhooks"
php -l third-audience/includes/class-ta-bot-analytics.php && echo "✓ Bot Analytics" || echo "✗ Bot Analytics"
php -l third-audience/admin/class-ta-admin.php && echo "✓ Admin" || echo "✗ Admin"
echo ""

# Test 2: File Existence
echo "Test 2: File Existence"
[ -f "third-audience/includes/class-ta-rate-limiter.php" ] && echo "✓ Rate Limiter" || echo "✗ Rate Limiter"
[ -f "third-audience/includes/class-ta-webhooks.php" ] && echo "✓ Webhooks" || echo "✗ Webhooks"
[ -f "third-audience/includes/class-ta-bot-analytics.php" ] && echo "✓ Bot Analytics" || echo "✗ Bot Analytics"
[ -f "third-audience/admin/class-ta-admin.php" ] && echo "✓ Admin" || echo "✗ Admin"
echo ""

# Test 3: Nonce Verification
echo "Test 3: Security Checks"
grep -q "wp_verify_nonce\|verify_nonce_or_die" third-audience/admin/class-ta-admin.php && echo "✓ Nonce Verification" || echo "✗ Nonce Verification"
grep -q "sanitize_text_field" third-audience/includes/class-ta-bot-analytics.php && echo "✓ Data Sanitization" || echo "✗ Data Sanitization"
grep -q "\$wpdb->prepare" third-audience/includes/class-ta-rate-limiter.php && echo "✓ Prepared Statements" || echo "✗ Prepared Statements"
echo ""

echo "=== Verification Complete ==="
```

---

## Next Steps

1. **Run Database Verification:** Execute SQL queries from Section 1
2. **Check WordPress Options:** Run PHP code from Section 2
3. **Verify Syntax:** Run commands from Section 3
4. **Execute Integration Tests:** Run scenarios from Section 4
5. **Check Logs:** Review WordPress debug logs for any issues
6. **Run Security Checks:** Execute commands from Section 5
7. **File Integrity:** Run tests from Section 6
8. **Final Verification:** Check all items in the verification checklist

---

**Document Version:** 1.0
**Last Updated:** January 21, 2026
**Status:** Ready for Testing
