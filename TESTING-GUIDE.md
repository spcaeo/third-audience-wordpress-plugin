# Third Audience - Complete Testing Guide for monocubed.com

## Pre-Testing Checklist

Before you begin, ensure:
- [ ] You have WordPress admin access to monocubed.com
- [ ] You have FTP or file manager access
- [ ] You can access phpMyAdmin or database

---

## PHASE 1: Plugin Upload & Activation

### Step 1.1: Upload Plugin

**Option A: Via WordPress Admin**
```
1. Go to: https://www.monocubed.com/wp-admin/
2. Navigate to: Plugins → Add New → Upload Plugin
3. Choose: third-audience.zip
4. Click: Install Now
5. Click: Activate Plugin
```

**Option B: Via FTP**
```
1. Connect to FTP
2. Upload /third-audience/ folder to:
   /wp-content/plugins/third-audience/
3. Go to WordPress Admin → Plugins
4. Click: Activate on "Third Audience"
```

### Step 1.2: Check Activation Notice

After activation, you should see this notice:

```
🎉 Third Audience Activated Successfully

Auto-configuration completed:
✅ or ⚠️ REST API: [Status]
✅ or 🔒 Security Plugin: [Name if detected]
✅ Database: [Status]
🖥️ Server: [Type]
🐘 PHP: [Version]

✨ No server configuration needed!
```

**Screenshot this notice and save it!**

---

## PHASE 2: Environment Detection Verification

### Step 2.1: Check System Health

```
1. Go to: Settings → Third Audience → System Health
2. You should see a full environment report
```

**Expected Results:**

| Item | Expected |
|------|----------|
| REST API Status | ✓ Accessible OR ⚠ Fallback Mode |
| Database Tables | ✓ All created |
| PHP Version | 7.4+ |
| Server Type | nginx/apache/litespeed |

### Step 2.2: Verify Database Tables Created

```
1. Open phpMyAdmin
2. Select your WordPress database
3. Check for these tables:

Required Tables:
- wp_ta_bot_analytics (main tracking table)
- wp_ta_citation_alerts
- wp_ta_bot_patterns

4. Click on wp_ta_bot_analytics
5. Check Structure tab
6. Verify these columns exist:
   - id
   - bot_name
   - page_url
   - page_title
   - referer
   - search_query
   - content_type ← IMPORTANT (new in 3.4.0)
   - is_citation ← IMPORTANT
   - traffic_type
   - visited_at
```

**If any column is missing, the auto-fixer will add it within 24 hours, or you can run:**
```sql
ALTER TABLE wp_ta_bot_analytics
ADD COLUMN content_type varchar(50) DEFAULT 'html' AFTER traffic_type;

ALTER TABLE wp_ta_bot_analytics
ADD COLUMN is_citation tinyint(1) DEFAULT 0 AFTER cache_hit;
```

---

## PHASE 3: Test REST API Endpoints

### Step 3.1: Test Health Endpoint (REST API)

Open terminal and run:

```bash
curl https://www.monocubed.com/wp-json/third-audience/v1/health
```

**Expected Response (if REST API works):**
```json
{
  "status": "healthy",
  "version": "3.4.0",
  "converter": "available",
  "cache": "operational"
}
```

**If you get 403 or 404:**
- REST API is blocked ✓ (Expected on some servers)
- Plugin will use AJAX fallback automatically
- Everything still works!

### Step 3.2: Test AJAX Fallback Endpoint

```bash
curl -X POST https://www.monocubed.com/wp-admin/admin-ajax.php \
  -d "action=ta_health_check"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "status": "healthy",
    "version": "3.4.0",
    "method": "ajax_fallback"
  }
}
```

**This should ALWAYS work, even if REST API is blocked!**

---

## PHASE 4: Test Citation Tracking

### Step 4.1: Get API Key

```
1. Go to: Settings → Third Audience → Headless Setup
2. Find: "API Key" field
3. Copy the key (looks like: abc123def456...)
4. Save this key - you'll need it!
```

### Step 4.2: Test Citation Tracking (REST API)

```bash
# Replace YOUR_API_KEY with the actual key from Step 4.1
curl -X POST https://www.monocubed.com/wp-json/third-audience/v1/track-citation \
  -H "Content-Type: application/json" \
  -H "X-TA-Api-Key: YOUR_API_KEY" \
  -d '{
    "url": "/test-citation-rest",
    "platform": "ChatGPT",
    "referer": "https://chat.openai.com",
    "search_query": "test query from REST API"
  }'
```

**Expected Response (Success):**
```json
{
  "success": true,
  "message": "Citation tracked successfully",
  "platform": "Chatgpt",
  "url": "/test-citation-rest"
}
```

**If you get error 401:**
- Check API key is correct
- Try regenerating API key in WordPress admin

**If you get error 403:**
- REST API is blocked
- Try AJAX fallback test (Step 4.3)

### Step 4.3: Test Citation Tracking (AJAX Fallback)

```bash
# Replace YOUR_API_KEY with the actual key
curl -X POST https://www.monocubed.com/wp-admin/admin-ajax.php \
  -d "action=ta_track_citation" \
  -d "api_key=YOUR_API_KEY" \
  -d "url=/test-citation-ajax" \
  -d "platform=Perplexity" \
  -d "referer=https://www.perplexity.ai" \
  -d "search_query=test query from AJAX"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "message": "Citation tracked successfully",
    "platform": "Perplexity",
    "url": "/test-citation-ajax",
    "method": "ajax_fallback"
  }
}
```

### Step 4.4: Verify Citations in WordPress Admin

```
1. Go to: Bot Analytics → AI Citations
2. You should see 2 new entries:

Date/Time | Platform    | Page                  | Query
----------|-------------|-----------------------|-------------------------
Just now  | ChatGPT     | /test-citation-rest   | test query from REST API
Just now  | Perplexity  | /test-citation-ajax   | test query from AJAX

3. If you see these entries: ✅ Citation tracking works!
```

---

## PHASE 5: Test Bot Analytics

### Step 5.1: Simulate Bot Visits

```bash
# Test 1: ClaudeBot
curl -A "ClaudeBot/1.0" https://www.monocubed.com/sample-page.md

# Test 2: GPTBot
curl -A "GPTBot/1.0" https://www.monocubed.com/sample-page.md

# Test 3: PerplexityBot
curl -A "PerplexityBot/1.0" https://www.monocubed.com/sample-page.md

# Test 4: Regular browser (should NOT be tracked)
curl -A "Mozilla/5.0" https://www.monocubed.com/sample-page
```

### Step 5.2: Verify Bot Visits in Admin

```
1. Go to: Bot Analytics → Overview
2. You should see 3 bot visits:

Bot Name        | Page              | User Agent      | Time
----------------|-------------------|-----------------|----------
ClaudeBot       | /sample-page.md   | ClaudeBot/1.0   | Just now
GPTBot          | /sample-page.md   | GPTBot/1.0      | Just now
PerplexityBot   | /sample-page.md   | PerplexityBot/1.0| Just now

3. The Mozilla visit should NOT appear (it's a regular browser)
```

---

## PHASE 6: Test Admin Buttons

### Step 6.1: Test Clear Cache Button

```
1. Go to: Settings → Third Audience → General
2. Find: "Clear Cache" button
3. Click it
4. Expected: "Cleared X cached items" success message
5. If error: Check browser console for details
```

**If button doesn't work:**
```
- Check browser console (F12) → Network tab
- Click button again
- Look for POST request to admin-post.php
- Check response for error message
```

### Step 6.2: Test Clear Errors Button

```
1. Go to: Settings → Third Audience → Logs
2. Find: "Clear Errors" button
3. Click it
4. Expected: "Error logs cleared" success message
```

**Both buttons should work on monocubed.com now!**

---

## PHASE 7: Test Markdown Serving

### Step 7.1: Test .md URL Rewriting

```bash
# Test if .md URLs work
curl -I https://www.monocubed.com/sample-page.md
```

**Expected Response:**
```
HTTP/1.1 200 OK
Content-Type: text/markdown
...
```

**If you get 404:**
- Rewrite rules not flushed
- Go to: Settings → Permalinks → Save Changes
- Try again

### Step 7.2: Test Markdown Content

```bash
# Get markdown content
curl https://www.monocubed.com/sample-page.md
```

**Expected Response:**
```markdown
---
title: Sample Page
date: 2026-02-02
author: Your Name
---

# Sample Page

This is the markdown version of your page...
```

---

## PHASE 8: Test Frontend Integration (If Using Headless)

### Step 8.1: Copy JavaScript Client

```bash
# From plugin directory:
cp wp-content/plugins/third-audience/assets/js/ta-auto-endpoint-detector.js \
   your-frontend/lib/third-audience.js
```

### Step 8.2: Create Test Page

Create `test-tracking.html` in your frontend:

```html
<!DOCTYPE html>
<html>
<head>
    <title>Test Third Audience Tracking</title>
    <script src="ta-auto-endpoint-detector.js"></script>
</head>
<body>
    <h1>Test Third Audience Citation Tracking</h1>
    <button onclick="testTracking()">Track Test Citation</button>
    <pre id="result"></pre>

    <script>
        const tracker = new ThirdAudienceTracker(
            'https://www.monocubed.com',
            'YOUR_API_KEY_HERE'  // Replace with actual API key
        );

        tracker.enableDebug();

        async function testTracking() {
            try {
                // Test connection first
                document.getElementById('result').textContent = 'Testing connection...\n';

                const connectionTest = await tracker.testConnection();
                document.getElementById('result').textContent +=
                    'REST API: ' + (connectionTest.rest.available ? 'Available ✓' : 'Blocked ✗') + '\n' +
                    'AJAX: ' + (connectionTest.ajax.available ? 'Available ✓' : 'Blocked ✗') + '\n\n';

                // Track citation
                document.getElementById('result').textContent += 'Tracking citation...\n';

                const result = await tracker.trackCitation({
                    url: '/test-from-frontend',
                    platform: 'ChatGPT',
                    searchQuery: 'test from JavaScript client'
                });

                document.getElementById('result').textContent +=
                    'Success! ✓\n' + JSON.stringify(result, null, 2);

            } catch (error) {
                document.getElementById('result').textContent +=
                    'Error: ' + error.message;
            }
        }
    </script>
</body>
</html>
```

### Step 8.3: Test Frontend Tracking

```
1. Open test-tracking.html in browser
2. Click "Track Test Citation"
3. Check console for debug logs
4. Should see:
   - Connection test results
   - Citation tracked successfully

5. Go to WordPress Admin → Bot Analytics → AI Citations
6. Should see new entry:
   Platform: ChatGPT
   Page: /test-from-frontend
   Query: test from JavaScript client
```

---

## PHASE 9: Verify Environment Detection

### Step 9.1: Check Stored Environment Data

Run this in WordPress database:

```sql
SELECT option_value
FROM wp_options
WHERE option_name = 'ta_environment_detection';
```

**You should see JSON with:**
```json
{
  "rest_api": {
    "accessible": true/false,
    "blocker": "none" or "wordfence" etc
  },
  "security_plugins": "none" or "wordfence" etc,
  "server_type": "nginx" or "apache",
  "db_permissions": {
    "create": true,
    "alter": true,
    "insert": true
  },
  "php_version": "8.1" etc,
  "detection_time": "2026-02-02 ..."
}
```

### Step 9.2: Check Fallback Mode Status

```sql
SELECT option_value
FROM wp_options
WHERE option_name = 'ta_use_ajax_fallback';
```

**Result:**
- `0` or empty = Using REST API ✓
- `1` = Using AJAX fallback ✓

**Both are fine! The plugin works either way.**

---

## PHASE 10: Performance Testing

### Step 10.1: Test Response Times

```bash
# Measure REST API response time
time curl https://www.monocubed.com/wp-json/third-audience/v1/health

# Measure AJAX response time
time curl -X POST https://www.monocubed.com/wp-admin/admin-ajax.php \
  -d "action=ta_health_check"

# Both should respond in < 1 second
```

### Step 10.2: Test Rate Limiting

```bash
# Try 35 requests in 1 minute (should get rate limited after 30)
for i in {1..35}; do
    curl -X POST https://www.monocubed.com/wp-admin/admin-ajax.php \
      -d "action=ta_track_citation" \
      -d "api_key=YOUR_API_KEY" \
      -d "url=/test-rate-limit-$i" \
      -d "platform=ChatGPT"
    echo "Request $i"
    sleep 1
done

# Requests 1-30: Should succeed (200 OK)
# Requests 31-35: Should fail (429 Too Many Requests)
```

---

## PHASE 11: Cross-Browser Testing

### Test in Multiple Browsers:

**Chrome:**
```
1. Open: https://www.monocubed.com/test-tracking.html
2. Click track button
3. Check console (F12)
4. Verify citation appears in WordPress admin
```

**Firefox:**
```
(Same steps as Chrome)
```

**Safari:**
```
(Same steps as Chrome)
```

**Mobile (Chrome/Safari):**
```
(Same steps as Chrome)
```

**All browsers should work identically!**

---

## PHASE 12: Security Testing

### Step 12.1: Test API Key Validation

```bash
# Test with wrong API key (should fail)
curl -X POST https://www.monocubed.com/wp-json/third-audience/v1/track-citation \
  -H "Content-Type: application/json" \
  -H "X-TA-Api-Key: WRONG_KEY" \
  -d '{"url":"/test","platform":"ChatGPT"}'

# Expected: 401 Unauthorized
```

### Step 12.2: Test Without API Key

```bash
# Test without API key (should fail)
curl -X POST https://www.monocubed.com/wp-json/third-audience/v1/track-citation \
  -H "Content-Type: application/json" \
  -d '{"url":"/test","platform":"ChatGPT"}'

# Expected: 401 Unauthorized
```

### Step 12.3: Test Invalid Platform

```bash
# Test with invalid platform (should fail)
curl -X POST https://www.monocubed.com/wp-json/third-audience/v1/track-citation \
  -H "Content-Type: application/json" \
  -H "X-TA-Api-Key: YOUR_API_KEY" \
  -d '{"url":"/test","platform":"HackerBot"}'

# Expected: 400 Bad Request - Invalid platform
```

**All security tests should properly reject invalid requests!**

---

## FINAL CHECKLIST

After completing all tests, verify:

### Database:
- [ ] wp_ta_bot_analytics table exists
- [ ] content_type column exists
- [ ] is_citation column exists
- [ ] Test entries visible in table

### REST API:
- [ ] Health endpoint responds (200 or 403/404 is fine)
- [ ] Citation tracking works OR fallback works

### AJAX Fallback:
- [ ] Health check responds successfully
- [ ] Citation tracking works

### Admin Interface:
- [ ] Activation notice appeared
- [ ] System Health shows environment
- [ ] Bot Analytics shows test entries
- [ ] AI Citations shows test entries
- [ ] Clear Cache button works
- [ ] Clear Errors button works

### Frontend:
- [ ] JavaScript client detects endpoint
- [ ] testConnection() works
- [ ] trackCitation() works
- [ ] Citations appear in WordPress admin

### Bot Tracking:
- [ ] ClaudeBot visit tracked
- [ ] GPTBot visit tracked
- [ ] PerplexityBot visit tracked
- [ ] Regular browsers NOT tracked

### Security:
- [ ] Wrong API key rejected
- [ ] Missing API key rejected
- [ ] Invalid platform rejected
- [ ] Rate limiting works (30 req/min)

---

## Expected Results Summary

| Test | Expected Result |
|------|-----------------|
| Plugin Activation | ✓ Activation notice shown |
| Database Tables | ✓ All tables created automatically |
| REST API | ✓ Works OR ⚠️ Fallback enabled |
| AJAX Endpoints | ✓ Always works |
| Citation Tracking (REST) | ✓ Works OR falls back to AJAX |
| Citation Tracking (AJAX) | ✓ Always works |
| Bot Analytics | ✓ Tracks AI bot visits |
| AI Citations | ✓ Shows in admin |
| Clear Cache | ✓ Button works |
| Clear Errors | ✓ Button works |
| Markdown URLs | ✓ .md URLs work |
| Frontend Tracking | ✓ JavaScript client works |
| Security | ✓ Validates API keys |
| Rate Limiting | ✓ Limits to 30 req/min |

---

## Troubleshooting

### If REST API Returns 403:
```
✓ This is NORMAL on many servers
✓ Plugin automatically uses AJAX fallback
✓ Everything works the same
✓ No action needed
```

### If Database Tables Missing:
```
1. Go to: Settings → Third Audience → System Health
2. Click: "Fix Database Now" button
3. Refresh page
4. Check phpMyAdmin again
```

### If Citations Not Appearing:
```
1. Check API key is correct
2. Test with curl commands above
3. Check WordPress debug.log for errors
4. Verify database has is_citation column
```

### If Buttons Don't Work:
```
1. Open browser console (F12)
2. Click button
3. Check Network tab for errors
4. Look for nonce or permission errors
```

---

## Save Your Test Results

Create a test report:

```
THIRD AUDIENCE TESTING REPORT
Site: www.monocubed.com
Date: [DATE]
Version: 3.4.0

Environment:
- REST API: [Accessible/Fallback]
- Server: [nginx/apache]
- PHP: [version]
- Security Plugin: [name or none]

Test Results:
✓ Plugin activated successfully
✓ Database tables created
✓ Citation tracking works
✓ Bot analytics works
✓ Admin buttons work
✓ Frontend integration works

Issues Found:
[List any issues]

Notes:
[Any additional notes]
```

---

## Need Help?

If any test fails:
1. Check the error message
2. Look in WordPress debug.log
3. Check browser console
4. Review System Health page
5. Check database for missing columns

**Most common issue:** REST API blocked → Plugin uses AJAX fallback → Everything works! ✓
