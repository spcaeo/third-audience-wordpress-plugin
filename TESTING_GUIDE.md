# Testing Guide: Citation Tracking Fixes (v3.5.0)

## Overview
This guide helps you verify that the 3 bug fixes are working correctly:
1. ✅ Client User Agent tracking (real browser vs "Headless Frontend")
2. ✅ HTTP Status Code tracking (200, 404, 500)
3. ✅ Request Type Classification (html_page vs rsc_prefetch vs api_call)

---

## Step 1: Verify Database Schema

### Check if new columns exist
Run this SQL query in phpMyAdmin or MySQL CLI:

```sql
DESCRIBE wp_ta_bot_analytics;
```

**Expected columns (added in v3.5.0):**
- `client_user_agent` (TEXT, nullable)
- `http_status` (INT, nullable)
- `request_type` (VARCHAR, nullable)

### Alternative: Check column existence
```sql
SHOW COLUMNS FROM wp_ta_bot_analytics
WHERE Field IN ('client_user_agent', 'http_status', 'request_type');
```

---

## Step 2: Test Citation Click Tracking

### Test Scenario: Simulate AI Citation Click

**Method 1: Manual Test (Using Browser DevTools)**

1. Open your website in Chrome
2. Open DevTools (F12) → Console
3. Paste this code to simulate a ChatGPT citation:

```javascript
// Simulate ChatGPT referrer
window.history.replaceState({}, '', '/?utm_source=chatgpt.com');
document.referrer = 'https://chat.openai.com/';

// Reload page
location.reload();
```

4. Check database after page load:

```sql
SELECT
    id,
    bot_name AS platform,
    user_agent AS server_ua,
    client_user_agent AS real_browser_ua,
    http_status,
    request_type,
    traffic_type,
    url,
    visit_timestamp
FROM wp_ta_bot_analytics
WHERE traffic_type = 'citation_click'
ORDER BY visit_timestamp DESC
LIMIT 5;
```

**Expected Results:**
- `server_ua`: May show "Headless Frontend" or "Mozilla/5.0..."
- `client_user_agent`: Should show real browser like "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0"
- `http_status`: Should show `200`
- `request_type`: Should show `html_page` or `js_fallback`

---

### Method 2: Use Real AI Platform

1. Go to ChatGPT/Perplexity/Claude
2. Ask a question that would cite your website
3. Click the citation link
4. Check database as above

---

## Step 3: Compare User Agents

### Query: Show User Agent Differences

```sql
SELECT
    id,
    bot_name,
    -- Server-side UA (may be "Headless Frontend")
    SUBSTRING(user_agent, 1, 50) AS server_ua_preview,
    -- Client-side UA (real browser)
    SUBSTRING(client_user_agent, 1, 50) AS client_ua_preview,
    -- Are they different?
    CASE
        WHEN user_agent != client_user_agent THEN '❌ DIFFERENT'
        WHEN client_user_agent IS NULL THEN '⚠️  NO CLIENT UA'
        ELSE '✅ SAME'
    END AS ua_comparison,
    visit_timestamp
FROM wp_ta_bot_analytics
WHERE traffic_type = 'citation_click'
ORDER BY visit_timestamp DESC
LIMIT 10;
```

**What to Look For:**
- ✅ **DIFFERENT**: Good! Server sees "Headless", Client sees real browser
- ⚠️ **NO CLIENT UA**: JavaScript didn't run (caching issue or JS disabled)
- ✅ **SAME**: Both captured correctly (some platforms send real UA to server)

---

## Step 4: Check HTTP Status Codes

### Test 404 Errors (Broken Citations)

1. Create a test citation to a non-existent page:
   - Visit: `https://yoursite.com/non-existent-page?utm_source=chatgpt.com`

2. Check database:

```sql
SELECT
    id,
    url,
    http_status,
    request_type,
    CASE
        WHEN http_status = 200 THEN '✅ Success'
        WHEN http_status = 404 THEN '❌ Broken Link'
        WHEN http_status = 500 THEN '🔥 Server Error'
        WHEN http_status IS NULL THEN '⚠️  No Status'
        ELSE CONCAT('ℹ️  Status: ', http_status)
    END AS status_label,
    visit_timestamp
FROM wp_ta_bot_analytics
WHERE traffic_type = 'citation_click'
ORDER BY visit_timestamp DESC
LIMIT 10;
```

**Expected:**
- Valid pages: `http_status = 200`
- Missing pages: `http_status = 404`

---

## Step 5: Check Request Type Classification

### Query: Request Type Breakdown

```sql
SELECT
    request_type,
    COUNT(*) AS count,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM wp_ta_bot_analytics WHERE traffic_type = 'citation_click'), 2) AS percentage,
    -- Description
    CASE request_type
        WHEN 'html_page' THEN '✅ Initial page load (matches Nginx)'
        WHEN 'rsc_prefetch' THEN '🔄 Next.js internal navigation'
        WHEN 'js_fallback' THEN '📄 Cached page (JS tracker)'
        WHEN 'api_call' THEN '🔌 REST API call'
        ELSE '❓ Unknown'
    END AS description
FROM wp_ta_bot_analytics
WHERE traffic_type = 'citation_click'
GROUP BY request_type
ORDER BY count DESC;
```

**What to Expect:**
- `html_page`: Initial page loads from AI platforms
- `js_fallback`: Cached pages (where server-side tracking was skipped)
- `rsc_prefetch`: Should be RARE or ZERO for citations (this is internal Next.js navigation)

---

## Step 6: Find RSC Prefetch Requests (The Bug We Fixed)

### Query: Identify RSC Prefetch vs Real Page Loads

```sql
SELECT
    id,
    url,
    request_type,
    user_agent,
    http_status,
    CASE
        WHEN url LIKE '%?_rsc=%' THEN '🚨 RSC Prefetch (Internal)'
        ELSE '✅ Real Page Load'
    END AS request_nature,
    visit_timestamp
FROM wp_ta_bot_analytics
WHERE traffic_type = 'citation_click'
AND (
    request_type = 'rsc_prefetch'
    OR url LIKE '%?_rsc=%'
)
ORDER BY visit_timestamp DESC
LIMIT 10;
```

**Expected:**
- Before v3.5.0: RSC prefetch marked as `rest_api` (WRONG)
- After v3.5.0: RSC prefetch marked as `rsc_prefetch` (CORRECT)

---

## Step 7: Full Diagnostic Query

### Complete Health Check

```sql
SELECT
    -- Identification
    id,
    bot_name AS platform,
    url,

    -- User Agent Comparison
    CASE
        WHEN client_user_agent IS NULL THEN '⚠️  Missing'
        WHEN user_agent LIKE '%Headless%' AND client_user_agent LIKE '%Chrome%' THEN '✅ Fixed'
        ELSE '✓ OK'
    END AS ua_status,

    -- HTTP Status
    CASE
        WHEN http_status IS NULL THEN '⚠️  Missing'
        WHEN http_status = 200 THEN '✅ 200'
        WHEN http_status = 404 THEN '❌ 404'
        ELSE CONCAT('ℹ️  ', http_status)
    END AS http_check,

    -- Request Type
    CASE
        WHEN request_type IS NULL THEN '⚠️  Missing'
        WHEN request_type = 'html_page' THEN '✅ HTML'
        WHEN request_type = 'rsc_prefetch' THEN '🔄 RSC'
        WHEN request_type = 'js_fallback' THEN '📄 JS'
        ELSE request_type
    END AS req_type,

    -- Timestamp
    visit_timestamp

FROM wp_ta_bot_analytics
WHERE traffic_type = 'citation_click'
ORDER BY visit_timestamp DESC
LIMIT 20;
```

---

## Step 8: JavaScript Tracker Verification

### Check if JavaScript is sending client_user_agent

1. Open browser DevTools → Network tab
2. Visit your site with UTM parameter: `/?utm_source=chatgpt.com`
3. Look for AJAX request to `/wp-admin/admin-ajax.php`
4. Check **Request Payload** should include:

```
action: ta_track_citation_js
platform: ChatGPT
client_user_agent: Mozilla/5.0 (Windows NT 10.0; ...) Chrome/144...
request_type: js_fallback
```

---

## Step 9: Check Browser/Device/OS Parsing

### Query: Parse Client User Agents

```sql
SELECT
    id,
    bot_name,
    client_user_agent,
    -- Extract Browser
    CASE
        WHEN client_user_agent LIKE '%Chrome/%' AND client_user_agent NOT LIKE '%Edge/%' THEN 'Chrome'
        WHEN client_user_agent LIKE '%Edge/%' THEN 'Edge'
        WHEN client_user_agent LIKE '%Safari/%' AND client_user_agent NOT LIKE '%Chrome/%' THEN 'Safari'
        WHEN client_user_agent LIKE '%Firefox/%' THEN 'Firefox'
        ELSE 'Other'
    END AS browser,
    -- Extract OS
    CASE
        WHEN client_user_agent LIKE '%Windows NT 10%' THEN 'Windows 10/11'
        WHEN client_user_agent LIKE '%Macintosh%' THEN 'macOS'
        WHEN client_user_agent LIKE '%Android%' THEN 'Android'
        WHEN client_user_agent LIKE '%iPhone%' THEN 'iOS'
        ELSE 'Other'
    END AS os,
    visit_timestamp
FROM wp_ta_bot_analytics
WHERE traffic_type = 'citation_click'
AND client_user_agent IS NOT NULL
ORDER BY visit_timestamp DESC
LIMIT 10;
```

**Expected:**
- Before fix: All show "Unknown" or "Linux"
- After fix: Real browsers (Chrome, Safari, Edge, Firefox) and OS (Windows, macOS, Android, iOS)

---

## Step 10: Compare with Nginx Logs

### Match Plugin Data to Nginx Access Logs

**Nginx Log Format:**
```
192.168.1.1 - - [16/Feb/2026:10:30:45] "GET /page HTTP/1.1" 200 45678 "https://chat.openai.com/" "Mozilla/5.0 ... Chrome/144..."
```

**Plugin Query:**
```sql
SELECT
    DATE_FORMAT(visit_timestamp, '%d/%b/%Y:%H:%i:%s') AS timestamp_nginx_format,
    request_method,
    url,
    http_status,
    LENGTH(response_size) AS response_size,
    referer,
    client_user_agent
FROM wp_ta_bot_analytics
WHERE traffic_type = 'citation_click'
ORDER BY visit_timestamp DESC
LIMIT 5;
```

**Compare:**
- ✅ HTTP Status should match (200, 404)
- ✅ User Agent should match (real browser)
- ⚠️ Response time may differ (Nginx includes full stack, plugin is PHP only)

---

## Expected Results Summary

### ✅ All Working Correctly:
- `client_user_agent` column populated with real browser UA
- `http_status` shows 200, 404, or other codes
- `request_type` shows `html_page` or `js_fallback` (NOT `rest_api` or `rsc_prefetch` for citations)
- Browser/Device/OS parsing works correctly

### ⚠️ Potential Issues:
- `client_user_agent` NULL → JavaScript not running (caching issue)
- `http_status` NULL → Status code capture failed
- `request_type` NULL → Detection method failed

---

## Troubleshooting

### Issue: client_user_agent is NULL
**Causes:**
- Page is cached (full-page caching bypasses PHP)
- JavaScript disabled
- citation-tracker.js not loaded

**Fix:**
- Check if JS file exists: `public/js/citation-tracker.js`
- Check browser console for errors
- Clear cache and test again

### Issue: http_status is NULL
**Cause:** Status code already sent before tracking

**Fix:** Tracking must happen during request, not in `shutdown` hook

### Issue: request_type shows 'rest_api' for citations
**Cause:** Old code still running (v3.5.0 not active)

**Check:**
```bash
grep -n "detect_request_type" third-audience/includes/Analytics/class-ta-visit-tracker.php
```
Should show method at line ~274

---

## Success Criteria

Run this final validation query:

```sql
SELECT
    COUNT(*) AS total_citations,
    SUM(CASE WHEN client_user_agent IS NOT NULL THEN 1 ELSE 0 END) AS has_client_ua,
    SUM(CASE WHEN http_status IS NOT NULL THEN 1 ELSE 0 END) AS has_http_status,
    SUM(CASE WHEN request_type IN ('html_page', 'js_fallback') THEN 1 ELSE 0 END) AS correct_request_type,
    -- Percentages
    ROUND(SUM(CASE WHEN client_user_agent IS NOT NULL THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1) AS client_ua_percentage,
    ROUND(SUM(CASE WHEN http_status IS NOT NULL THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1) AS http_status_percentage,
    ROUND(SUM(CASE WHEN request_type IN ('html_page', 'js_fallback') THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1) AS correct_type_percentage
FROM wp_ta_bot_analytics
WHERE traffic_type = 'citation_click'
AND visit_timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY);
```

**Target Goals:**
- `client_ua_percentage`: ≥ 80% (some cached pages won't have it)
- `http_status_percentage`: ≥ 95%
- `correct_type_percentage`: ≥ 95%

---

## Report Issues

If tests fail, provide:
1. Database query results
2. Browser console logs (F12 → Console)
3. Network tab showing AJAX request
4. PHP error logs
