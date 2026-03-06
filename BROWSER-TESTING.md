# Browser-Based Testing Guide for AI Citations

This guide shows you **exactly** how to test if AI citations are being tracked on monocubed.com using just your web browser - no command line needed!

---

## 🎯 What You'll Test

When someone searches in ChatGPT/Perplexity and clicks a link to monocubed.com, the plugin should:
1. Detect the AI platform (from HTTP Referer header)
2. Save the citation to the database
3. Show it in WordPress Admin → Bot Analytics → AI Citations page

---

## ✅ Method 1: Real ChatGPT Testing (Recommended)

### Step 1: Open ChatGPT
Go to https://chat.openai.com/ and log in

### Step 2: Use This Exact Search Query
Copy and paste this into ChatGPT:

```
Tell me about MonoCubed software development company. Visit https://www.monocubed.com/ for more information.
```

Or try these alternatives:
```
What services does MonoCubed offer? Check https://www.monocubed.com/services
```

```
I need information about MonoCubed's portfolio. See https://www.monocubed.com/portfolio
```

### Step 3: Click the Link in ChatGPT's Response
- ChatGPT will generate a response with a link to monocubed.com
- **Click the link** directly in ChatGPT (don't copy-paste the URL!)
- This ensures the HTTP Referer header contains "chat.openai.com"

### Step 4: Verify in WordPress Admin

1. **Open WordPress Admin** in a new tab:
   ```
   https://www.monocubed.com/wp-admin/
   ```

2. **Navigate to AI Citations page**:
   - Left sidebar → **Bot Analytics**
   - Click **AI Citations**
   - Or go directly to:
   ```
   https://www.monocubed.com/wp-admin/admin.php?page=third-audience-ai-citations
   ```

3. **Look for your citation**:
   - You should see a new entry with:
     - Platform: **ChatGPT**
     - Page URL: The page you clicked to (e.g., `/` or `/services`)
     - Visited At: Current date/time
     - Referer: `https://chat.openai.com`

### Expected Result
✅ **Success**: Citation appears in the table within 5 seconds
❌ **Failed**: No citation appears after refreshing

---

## ✅ Method 2: Perplexity Testing

### Step 1: Open Perplexity
Go to https://www.perplexity.ai/

### Step 2: Use This Exact Search Query
```
What does MonoCubed specialize in? Visit https://www.monocubed.com/
```

### Step 3: Click the Link in Perplexity's Response
- Perplexity will show sources/citations with monocubed.com
- **Click the link** directly in Perplexity
- Referer will be "perplexity.ai"

### Step 4: Verify in WordPress Admin
Same as Method 1, but look for:
- Platform: **Perplexity**
- Referer: `https://www.perplexity.ai`

---

## ✅ Method 3: Browser DevTools Simulation (For Testing Without AI Platforms)

This method simulates an AI platform visit using your browser's developer tools.

### Step 1: Open Browser DevTools
1. Open https://www.monocubed.com/ in Chrome/Firefox
2. Press **F12** or **Right-click → Inspect**
3. Go to **Console** tab

### Step 2: Run This JavaScript Code
Paste this code into the console and press Enter:

```javascript
// Simulate ChatGPT citation tracking
fetch('/wp-json/third-audience/v1/track-citation', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-TA-Api-Key': 'YOUR_API_KEY_HERE'  // Get from WordPress → Settings → Third Audience → Headless Setup
    },
    body: JSON.stringify({
        url: window.location.pathname,
        platform: 'ChatGPT',
        search_query: 'test search from browser',
        referer: 'https://chat.openai.com'
    })
})
.then(response => response.json())
.then(data => {
    console.log('✅ Citation tracked:', data);
    alert('Citation tracked! Check WordPress Admin → Bot Analytics → AI Citations');
})
.catch(error => {
    console.error('❌ Error:', error);
    alert('Error tracking citation. Check console for details.');
});
```

### Step 3: Replace YOUR_API_KEY_HERE

1. Get your API key:
   - Go to https://www.monocubed.com/wp-admin/
   - Navigate to **Settings → Third Audience → Headless Setup**
   - Copy the API Key

2. Replace `YOUR_API_KEY_HERE` in the code above

3. Run the code again

### Step 4: Check the Response
You should see in the console:
```json
✅ Citation tracked: {
  "success": true,
  "message": "Citation tracked successfully"
}
```

### Step 5: Verify in WordPress Admin
Go to **Bot Analytics → AI Citations** and look for your test entry.

---

## ✅ Method 4: ReqBin API Testing (No Code!)

If you don't want to use DevTools, use ReqBin.com for visual API testing.

### Step 1: Open ReqBin
Go to https://reqbin.com/

### Step 2: Configure the Request

**Method:** POST

**URL:**
```
https://www.monocubed.com/wp-json/third-audience/v1/track-citation
```

**Headers:** (Click "+ Header" button)
```
Content-Type: application/json
X-TA-Api-Key: YOUR_API_KEY_HERE
```

**Body:** (Select "JSON" tab)
```json
{
  "url": "/test-page",
  "platform": "ChatGPT",
  "search_query": "test query from ReqBin",
  "referer": "https://chat.openai.com"
}
```

### Step 3: Click "Send"

You should get a response:
```json
{
  "success": true,
  "message": "Citation tracked successfully"
}
```

### Step 4: Verify in WordPress Admin
Check **Bot Analytics → AI Citations** for the new entry.

---

## ✅ Method 5: AJAX Fallback Testing (If REST API is Blocked)

If the REST API returns 403 or 404 errors, the plugin automatically uses AJAX fallback.

### Test AJAX in Browser DevTools

```javascript
// Create form data
const formData = new FormData();
formData.append('action', 'ta_track_citation');
formData.append('api_key', 'YOUR_API_KEY_HERE');
formData.append('url', window.location.pathname);
formData.append('platform', 'Perplexity');
formData.append('search_query', 'test from AJAX');
formData.append('referer', 'https://www.perplexity.ai');

// Send via AJAX
fetch('/wp-admin/admin-ajax.php', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => {
    console.log('✅ AJAX citation tracked:', data);
    alert('AJAX citation tracked! Check WordPress Admin.');
})
.catch(error => {
    console.error('❌ AJAX Error:', error);
});
```

---

## 📊 What to Expect in WordPress Admin

After any test method, go to:
```
https://www.monocubed.com/wp-admin/admin.php?page=third-audience-ai-citations
```

### You Should See a Table With:

| Platform | Page URL | Search Query | Referer | Visited At |
|----------|----------|--------------|---------|------------|
| ChatGPT | /test-page | test query from... | https://chat.openai.com | 2026-02-02 14:30:22 |
| Perplexity | /services | test from AJAX | https://www.perplexity.ai | 2026-02-02 14:31:45 |

### If No Data Appears:

1. **Wait 5 seconds and refresh** the page
2. **Check the date range filter** at the top (set to "Last 30 Days")
3. **Try the AJAX method** (Method 5) if REST API is blocked
4. **Check database directly** (see Troubleshooting below)

---

## 🔍 Troubleshooting

### Issue: Citation Not Appearing

**Check 1: API Key is Correct**
```javascript
// In DevTools console, verify API key works:
fetch('/wp-json/third-audience/v1/health', {
    headers: { 'X-TA-Api-Key': 'YOUR_API_KEY_HERE' }
})
.then(r => r.json())
.then(d => console.log(d));
```

**Check 2: Database Has the Entry**
Go to phpMyAdmin and run:
```sql
SELECT * FROM wp_ta_bot_analytics
WHERE is_citation = 1
ORDER BY visited_at DESC
LIMIT 10;
```

**Check 3: Duplicate Prevention**
The plugin prevents duplicate citations within 5 minutes. Wait 5 minutes and try again.

**Check 4: REST API Blocked**
If you get 403/404 errors, use Method 5 (AJAX fallback)

---

## 🎯 Quick Win Test (2 Minutes)

The fastest way to verify everything works:

1. **Open ChatGPT** → Paste: `Tell me about MonoCubed. Visit https://www.monocubed.com/`

2. **Click the link** in ChatGPT's response

3. **Open WordPress Admin** → Bot Analytics → AI Citations

4. **Look for ChatGPT entry** with current timestamp

✅ **If you see the entry: Plugin is working perfectly!**

---

## 📈 Real-World Usage Scenarios

### Scenario 1: User Asks ChatGPT for Recommendations
```
User: "Who are the best software development companies?"
ChatGPT: "MonoCubed is highly rated. Learn more at https://www.monocubed.com/"
User: *clicks link*
Result: ✅ Citation tracked
```

### Scenario 2: User Searches in Perplexity
```
User: "React Native development companies"
Perplexity: Shows monocubed.com as a source
User: *clicks source*
Result: ✅ Citation tracked
```

### Scenario 3: User Asks Claude
```
User: "Tell me about MonoCubed"
Claude: "Visit https://www.monocubed.com/ for details"
User: *clicks link*
Result: ✅ Citation tracked (Referer: claude.ai)
```

---

## 🎨 Visual Testing Flow

```
┌─────────────────┐
│   ChatGPT       │
│   Ask question  │
│   about company │
└────────┬────────┘
         │
         │ ChatGPT responds with link
         │
         ▼
┌─────────────────┐
│   User clicks   │
│   the link      │
│   in ChatGPT    │
└────────┬────────┘
         │
         │ HTTP Request with Referer: chat.openai.com
         │
         ▼
┌─────────────────┐
│  monocubed.com  │
│  Page loads     │
│  Plugin detects │
│  ChatGPT referer│
└────────┬────────┘
         │
         │ Saves to database
         │
         ▼
┌─────────────────┐
│ WordPress Admin │
│ Bot Analytics   │
│ → AI Citations  │
│ ✅ Shows entry  │
└─────────────────┘
```

---

## 💡 Pro Tips

1. **Test with multiple platforms**: ChatGPT, Perplexity, Claude, Gemini
2. **Use real searches**: More authentic than API testing
3. **Check timestamps**: Verify real-time tracking
4. **Test different pages**: Try homepage, blog posts, service pages
5. **Monitor database**: Watch entries being created in real-time

---

## ✅ Success Checklist

- [ ] ChatGPT citation tracked and visible in WordPress admin
- [ ] Perplexity citation tracked and visible in WordPress admin
- [ ] Browser DevTools test successful
- [ ] ReqBin API test successful
- [ ] AJAX fallback test successful (if REST API blocked)
- [ ] Database shows entries in wp_ta_bot_analytics
- [ ] Timestamps are current (within 5 minutes)
- [ ] Duplicate prevention working (same citation within 5 min blocked)

---

## 🚀 Next Steps After Successful Testing

Once you've verified citations are being tracked:

1. **Share the good news**: Data is being tracked correctly! ✅
2. **Monitor real traffic**: Watch for real citations from ChatGPT/Perplexity users
3. **Analyze patterns**: See which pages AI platforms cite most
4. **Export data**: Use the CSV export feature for analysis
5. **Configure alerts**: Set up email notifications for high citation days

---

## 📞 Need Help?

If tests are still failing:
1. Check `DEBUGGING.md` for diagnostic steps
2. Run `test-data-tracking.sh` for automated verification
3. Review `DEPLOYMENT.md` for deployment troubleshooting
4. Check plugin error logs in WordPress Admin

---

**Remember**: The plugin automatically detects the environment and uses the best available method (REST API or AJAX fallback). You don't need to configure anything manually!

**Just activate, test, and start tracking AI citations!** 🎉
