# Fixes Applied - Citation Tracking Issues

**Date:** 2026-02-17
**Status:** ✅ Fixed, Ready for Review (NOT committed yet)

---

## Summary

Fixed 2 critical issues that prevented proper citation tracking data capture:
1. ✅ HTTP Status Code not explicitly captured
2. ✅ Client User Agent blocked by deduplication logic

---

## Fix #1: Explicit HTTP Status Capture

### File: `third-audience/includes/Analytics/class-ta-visit-tracker.php`

### Changes:
- Added 3 lines at line 370
- Explicitly capture HTTP status before preparing tracking data

### Code Change:
```diff
+ // Capture HTTP status code.
+ $http_status = $this->get_http_status();
+
  // Prepare tracking data.
  $tracking_data = array(
      ...
      'request_type'   => $request_type,
+     'http_status'    => $http_status,
      'cache_status'   => 'N/A',
      ...
  );
```

---

## Fix #2: Smart Update Instead of Duplicate Blocking

### File: `third-audience/includes/class-ta-bot-analytics.php`

### Changes:
- Added 61 lines after line 279
- JavaScript tracker now UPDATES existing records instead of being blocked

### Before vs After:

**BEFORE:**
```
Server tracks → client_ua = NULL
JS runs → BLOCKED by deduplication
Result: client_ua stays NULL ❌
```

**AFTER:**
```
Server tracks → client_ua = NULL, http_status = 200
JS runs → UPDATES same record with real browser UA
Result: Both fields populated ✅
```

---

## Testing

### Quick Test:
```bash
# 1. Visit with UTM
https://yoursite.com/?utm_source=chatgpt.com

# 2. Check database
SELECT client_user_agent, http_status FROM wp_ta_bot_analytics ORDER BY id DESC LIMIT 1;
```

**Expected:** Both fields should have values

### Verify Update:
Check browser DevTools → Network → Look for AJAX response:
```json
{
  "success": true,
  "data": {
    "message": "Updated existing record with client user agent",
    "updated": true
  }
}
```

---

## Next Steps

**DO NOT push yet!** Review changes first:

```bash
# See what changed
git diff third-audience/includes/Analytics/class-ta-visit-tracker.php
git diff third-audience/includes/class-ta-bot-analytics.php

# Test locally first
# Then commit when ready
```

---

## Files Modified

1. `third-audience/includes/Analytics/class-ta-visit-tracker.php` (+3 lines)
2. `third-audience/includes/class-ta-bot-analytics.php` (+61 lines)

Total: 2 files, 64 lines added
