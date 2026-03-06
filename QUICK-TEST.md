# Quick Test Commands for monocubed.com

## 🚀 Run These Commands After Activation

### 1. Test REST API Health
```bash
curl https://www.monocubed.com/wp-json/third-audience/v1/health
```
**Expected:** JSON with `"status": "healthy"`
**If 403/404:** Plugin will use AJAX fallback (still works!)

---

### 2. Test AJAX Fallback
```bash
curl -X POST https://www.monocubed.com/wp-admin/admin-ajax.php \
  -d "action=ta_health_check"
```
**Expected:** `{"success":true,"data":{"status":"healthy"}}`
**This should ALWAYS work**

---

### 3. Get API Key
```
1. Go to: https://www.monocubed.com/wp-admin/
2. Navigate to: Settings → Third Audience → Headless Setup
3. Copy the API Key (save it!)
```

---

### 4. Test Citation Tracking (REST)
```bash
# Replace YOUR_API_KEY with actual key from step 3
curl -X POST https://www.monocubed.com/wp-json/third-audience/v1/track-citation \
  -H "Content-Type: application/json" \
  -H "X-TA-Api-Key: YOUR_API_KEY" \
  -d '{"url":"/test-1","platform":"ChatGPT","search_query":"test query"}'
```
**Expected:** `{"success":true,"message":"Citation tracked successfully"}`

---

### 5. Test Citation Tracking (AJAX)
```bash
# Replace YOUR_API_KEY
curl -X POST https://www.monocubed.com/wp-admin/admin-ajax.php \
  -d "action=ta_track_citation" \
  -d "api_key=YOUR_API_KEY" \
  -d "url=/test-2" \
  -d "platform=Perplexity" \
  -d "search_query=another test"
```
**Expected:** `{"success":true,"data":{"message":"Citation tracked successfully"}}`

---

### 6. Simulate Bot Visits
```bash
# ClaudeBot
curl -A "ClaudeBot/1.0" https://www.monocubed.com/sample-page.md

# GPTBot
curl -A "GPTBot/1.0" https://www.monocubed.com/sample-page.md

# PerplexityBot
curl -A "PerplexityBot/1.0" https://www.monocubed.com/sample-page.md
```

---

### 7. Check Results in WordPress Admin

**AI Citations:**
```
https://www.monocubed.com/wp-admin/admin.php?page=third-audience-ai-citations

Should show 2 entries:
- ChatGPT: /test-1
- Perplexity: /test-2
```

**Bot Analytics:**
```
https://www.monocubed.com/wp-admin/admin.php?page=third-audience-bot-analytics

Should show 3 bot visits:
- ClaudeBot
- GPTBot
- PerplexityBot
```

---

### 8. Check Database

**Connect to phpMyAdmin and run:**
```sql
-- Check if tables exist
SHOW TABLES LIKE 'wp_ta_%';

-- Check citations
SELECT bot_name, page_url, search_query, visited_at
FROM wp_ta_bot_analytics
WHERE is_citation = 1
ORDER BY visited_at DESC
LIMIT 10;

-- Check bot visits
SELECT bot_name, page_url, user_agent, visited_at
FROM wp_ta_bot_analytics
WHERE traffic_type = 'bot_crawl'
ORDER BY visited_at DESC
LIMIT 10;
```

---

## ✅ Success Criteria

| Test | Pass Criteria |
|------|---------------|
| REST Health | Returns JSON OR gives 403 (both OK) |
| AJAX Health | Returns `{"success":true}` |
| Citation REST | Returns `{"success":true}` OR 403 (then use AJAX) |
| Citation AJAX | Returns `{"success":true}` |
| Bot Visits | 3 entries in wp_ta_bot_analytics |
| Admin Display | Citations visible in AI Citations page |

---

## 🎯 What You're Testing

1. **Environment Detection** - Plugin detects monocubed.com server
2. **Endpoint Selection** - Uses REST or AJAX automatically
3. **Citation Tracking** - Records AI platform citations
4. **Bot Analytics** - Tracks AI bot visits
5. **Database** - Stores all data correctly
6. **Admin Interface** - Shows data in WordPress admin

---

## 📊 Expected Timeline

- **Activation:** < 5 seconds
- **Environment Detection:** < 2 seconds
- **Database Creation:** < 3 seconds
- **First Citation:** < 1 second response time
- **Admin Display:** Instant

---

## 🔥 Quick Win Test (30 seconds)

```bash
# 1. Test health (5 sec)
curl https://www.monocubed.com/wp-json/third-audience/v1/health

# 2. Test AJAX health (5 sec)
curl -X POST https://www.monocubed.com/wp-admin/admin-ajax.php -d "action=ta_health_check"

# 3. Simulate bot visit (5 sec)
curl -A "ClaudeBot/1.0" https://www.monocubed.com/sample-page.md

# 4. Check admin (15 sec)
Open: https://www.monocubed.com/wp-admin/admin.php?page=third-audience-bot-analytics
Should see: ClaudeBot visit
```

**If all 4 steps work: Plugin is working perfectly! ✅**

---

## 💡 Pro Tips

1. **Keep terminal open** - Run commands multiple times to generate test data
2. **Use different platforms** - Test ChatGPT, Perplexity, Claude, Gemini
3. **Check timestamps** - Verify real-time tracking
4. **Screenshot results** - Save evidence of working features
5. **Test both endpoints** - Verify REST and AJAX both work

---

## 🚨 Common Issues & Solutions

### Issue: REST API returns 403
```
✓ This is NORMAL on many servers
✓ Plugin automatically uses AJAX fallback
✓ Try step 5 (AJAX test) instead
✓ Everything still works!
```

### Issue: Citation not appearing in admin
```
1. Check API key is correct
2. Wait 5 seconds and refresh page
3. Check database directly (step 8)
4. Look for duplicate prevention (5 min cooldown)
```

### Issue: No bot visits showing
```
1. Make sure you used .md URL: /page.md
2. Check User-Agent contains "Bot"
3. Try with different bot names
4. Check database directly
```

---

## 📝 Share Your Results

After testing, share:
```
✅ REST API: [Working/Fallback]
✅ AJAX Endpoints: [Working]
✅ Citation Tracking: [Working]
✅ Bot Analytics: [Working]
✅ Database: [X entries created]
```

---

## Need Full Testing Guide?

See: `TESTING-GUIDE.md` for comprehensive 12-phase testing with 50+ test cases.
