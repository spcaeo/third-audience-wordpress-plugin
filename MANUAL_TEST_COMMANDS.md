# Manual Testing Commands - Third Audience Plugin

Use these commands to manually verify the QA findings.

## Test 1: Verify Settings UI

1. Open browser and navigate to:
   ```
   http://localhost:8080/wp-admin/options-general.php?page=third-audience
   ```

2. Verify all metadata checkboxes are present:
   - [ ] Enable Enhanced Metadata (master switch)
   - [ ] Word Count
   - [ ] Reading Time
   - [ ] Summary
   - [ ] Language
   - [ ] Last Modified Date
   - [ ] Schema Type
   - [ ] Related Posts

## Test 2: Verify Markdown Generation (CRITICAL BUG)

Test the AI-optimized metadata in generated markdown:

```bash
# Test with Hello World post
curl -s "http://localhost:8080/hello-world.md" | head -40

# Expected to see (CURRENTLY MISSING):
# word_count: 25
# reading_time: "1 min read"
# summary: "Welcome to WordPress..."
# language: "en"
# last_modified: "2026-01-21T..."
# schema_type: "Article"
# related_posts: [...]

# Test with another post
curl -s "http://localhost:8080/ai-optimized-markdown-test.md" | head -40
```

## Test 3: Verify Response Headers

Check that markdown is served with correct headers:

```bash
curl -I "http://localhost:8080/hello-world.md"

# Expected headers:
# Content-Type: text/markdown; charset=UTF-8
# X-Cache-Status: HIT or MISS
# X-Powered-By: Third Audience v2.0.5
```

## Test 4: Test Cache Clear

```bash
# Before clear - check cache status
# Navigate to: http://localhost:8080/wp-admin/options-general.php?page=third-audience
# Look for "Cache Status" card showing item count

# Click "Clear All Cache" button
# Confirm the dialog
# Verify cache count drops to 0

# Re-request markdown
curl -s "http://localhost:8080/hello-world.md" | head -40

# Cache should regenerate (X-Cache-Status: MISS on first hit, then HIT)
```

## Test 5: Test Webhooks Tab

1. Navigate to Webhooks tab:
   ```
   http://localhost:8080/wp-admin/options-general.php?page=third-audience&tab=webhooks
   ```

2. Verify visible elements:
   - [ ] Enable Webhooks checkbox
   - [ ] Webhook URL input field
   - [ ] Event documentation (markdown.accessed, bot.detected)
   - [ ] Example JSON payload
   - [ ] Security notes
   - [ ] Webhook status card
   - [ ] Send Test Webhook button

## Test 6: Settings Persistence

1. Change a setting (e.g., disable Word Count metadata)
2. Click "Save Changes"
3. Reload the page
4. Verify the setting remained disabled

## Test 7: Homepage Markdown Pattern

1. Navigate to General tab
2. Look for "Homepage Markdown Pattern" dropdown
3. Current value should show: `home.md`
4. Test the live preview link:
   ```bash
   curl -s "http://localhost:8080/home.md" | head -20
   ```

## Test 8: Check Pre-Generated Markdown in Database

Use WP-CLI to inspect post meta:

```bash
# Check if markdown is pre-generated and stored
docker exec ta-wpcli wp post meta get 1 _ta_markdown

# Check generation timestamp
docker exec ta-wpcli wp post meta get 1 _ta_markdown_generated

# List all posts with pre-generated markdown
docker exec ta-wpcli wp post list --fields=ID,post_title,post_status --meta_key=_ta_markdown
```

## Test 9: Force Regeneration

Try to force markdown regeneration for a specific post:

```bash
# Using WP-CLI (if regenerate command exists)
docker exec ta-wpcli wp third-audience regenerate 1

# Or delete the pre-generated meta to force fresh generation
docker exec ta-wpcli wp post meta delete 1 _ta_markdown
docker exec ta-wpcli wp post meta delete 1 _ta_markdown_generated

# Then request the markdown again
curl -s "http://localhost:8080/hello-world.md" | head -40
```

## Test 10: Check Plugin Version

Verify the plugin version matches expectations:

```bash
docker exec ta-wpcli wp plugin list | grep third-audience

# Expected output should show version 2.0.5
```

## Test 11: Check Error Logs

Look for any PHP errors or warnings:

```bash
# Check WordPress debug log
docker exec ta-wordpress tail -50 /var/log/apache2/error.log

# Check for Third Audience specific logs
docker exec ta-wordpress tail -50 /var/log/apache2/error.log | grep -i "third.audience\|TA_"
```

## Test 12: Verify Settings Options in Database

Check that metadata settings are actually saved:

```bash
# Check if enhanced metadata is enabled
docker exec ta-wpcli wp option get ta_enable_enhanced_metadata

# Check each metadata field setting
docker exec ta-wpcli wp option get ta_metadata_word_count
docker exec ta-wpcli wp option get ta_metadata_reading_time
docker exec ta-wpcli wp option get ta_metadata_summary
docker exec ta-wpcli wp option get ta_metadata_language
docker exec ta-wpcli wp option get ta_metadata_last_modified
docker exec ta-wpcli wp option get ta_metadata_schema_type
docker exec ta-wpcli wp option get ta_metadata_related_posts

# All should return "1" (enabled)
```

## Expected Results Summary

### ✓ Should Work:
- Settings UI displays all controls
- Webhooks tab loads correctly
- Settings persist after save
- Cache can be cleared
- Markdown files are generated and served
- Response headers are correct

### ✗ Currently Broken:
- AI-optimized metadata fields NOT appearing in frontmatter
- Pre-generated markdown doesn't update when settings change
- No automatic regeneration on settings change

## Quick Validation Script

Save this as `quick-test.sh`:

```bash
#!/bin/bash

echo "=== THIRD AUDIENCE QUICK TEST ==="
echo ""

echo "1. Testing markdown generation..."
RESULT=$(curl -s "http://localhost:8080/hello-world.md" | head -20)
echo "$RESULT"
echo ""

echo "2. Checking for metadata fields..."
if echo "$RESULT" | grep -q "word_count:"; then
    echo "✓ word_count found"
else
    echo "✗ word_count MISSING"
fi

if echo "$RESULT" | grep -q "reading_time:"; then
    echo "✓ reading_time found"
else
    echo "✗ reading_time MISSING"
fi

if echo "$RESULT" | grep -q "summary:"; then
    echo "✓ summary found"
else
    echo "✗ summary MISSING"
fi

echo ""
echo "3. Checking settings in database..."
docker exec ta-wpcli wp option get ta_enable_enhanced_metadata
docker exec ta-wpcli wp option get ta_metadata_word_count
```

Run with: `chmod +x quick-test.sh && ./quick-test.sh`

---

## Contact & Support

If you encounter issues not covered in the QA report, check:
- Plugin error logs
- WordPress debug.log
- Browser console (for admin UI issues)
- Network tab in DevTools (for AJAX issues)
