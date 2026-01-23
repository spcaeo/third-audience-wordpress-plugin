---
date: 2026-01-23T07:31:01-05:00
session_name: general
researcher: Claude
git_commit: 74e2676c3b631686b07702f4bee5d2356cde2b4d
branch: main
repository: third-audience-wordpress-plugin
topic: "Email Digest and Bot Tracking Implementation (v3.2.0)"
tags: [implementation, email-digest, bot-tracking, wordpress-plugin]
status: complete
last_updated: 2026-01-23
last_updated_by: Claude
type: implementation_strategy
root_span_id: ""
turn_span_id: ""
---

# Handoff: Third Audience v3.2.0 - Email Digest & Bot Tracking

## Task(s)

### Completed
1. **Comprehensive Bot Tracking** - Track ALL bot page visits (not just .md requests)
   - Added `template_redirect` hook at priority 1 to intercept all page requests
   - Added `content_type` column to distinguish HTML vs Markdown visits
   - Added Content Type filter and Type column to dashboard
   - Added duplicate prevention via `ta_bot_visit_tracked` action

2. **Email Digest Feature** - Full implementation like FieldCamp
   - Created `TA_Email_Digest` class with WordPress cron scheduling
   - Created settings page with frequency, recipients, content customization
   - Supports .md report attachments
   - On-demand report downloads (24h and 7-day periods)

3. **Menu Reorganization**
   - Hidden Competitor Benchmarking from menu (still accessible via direct URL)
   - Moved About to end of submenu

### Pending (User's Last Request)
4. **AI Citations Page** - User reported that `http://localhost:8080/wp-admin/admin.php?page=third-audience-ai-citations` is not showing data, filters, summaries, or graphs. This needs investigation.

## Critical References
- `admin/views/ai-citations-page.php` - The AI Citations view file to investigate
- `includes/class-ta-bot-analytics.php` - Bot analytics data methods
- `admin/class-ta-admin.php:952-965` - AI Citations render method

## Recent changes

### New Files Created
- `includes/class-ta-email-digest.php` - Email digest functionality (527 lines)
- `admin/views/email-digest-settings.php` - Settings page UI (191 lines)

### Modified Files
- `admin/class-ta-admin.php:81` - Added `handle_digest_download` to admin_init
- `admin/class-ta-admin.php:136` - Added AJAX handler for test digest
- `admin/class-ta-admin.php:690-728` - Reordered menu, hidden Competitor Benchmarking
- `admin/class-ta-admin.php:1036-1090` - Added render and download methods
- `admin/css/bot-analytics.css:end` - Added HTML/MD badge styles
- `admin/views/bot-analytics-page.php` - Added Content Type filter and Type column
- `includes/autoload.php:135` - Added TA_Email_Digest to class map
- `includes/class-ta-bot-analytics.php:229-333` - Added `maybe_track_bot_crawl()` and `get_bot_client_ip()`
- `includes/class-ta-content-negotiation.php:137` - Added duplicate prevention action
- `includes/class-ta-url-router.php` - Added duplicate prevention action
- `includes/class-third-audience.php:53` - Initialize Email Digest

## Learnings

1. **WordPress File Downloads** - Must handle downloads on `admin_init` hook BEFORE WordPress sends headers. The `render_*_page()` method is too late - headers already sent.

2. **Menu Order** - WordPress admin submenus appear in order they're registered. To reorder, just change the order of `add_submenu_page()` calls.

3. **Hidden Menu Pages** - Set parent to `null` in `add_submenu_page()` to hide from menu while keeping page accessible via direct URL.

4. **Bot Tracking Pattern** - Use `template_redirect` at priority 1 to intercept ALL page requests. Use `did_action('ta_bot_visit_tracked')` to prevent duplicate tracking from multiple hooks.

## Post-Mortem

### What Worked
- **Singleton pattern** for Email Digest class - consistent with existing codebase
- **WordPress Cron** for scheduled digests - reliable, standard approach
- **admin_init hook** for file downloads - bypasses WordPress output buffering
- **Playwright MCP** for testing - quick visual verification of UI changes

### What Failed
- Tried: File download in `render_email_digest_page()` → Failed because: WordPress headers already sent
- Fixed by: Moving download logic to `handle_digest_download()` on `admin_init` hook

### Key Decisions
- Decision: Use WordPress transients for cron scheduling (not custom DB table)
  - Alternatives: Custom cron table, external scheduler
  - Reason: Simpler, follows WordPress patterns, sufficient for email scheduling

- Decision: Track ALL page visits (HTML + Markdown) instead of just .md
  - Alternatives: Continue tracking only .md requests
  - Reason: FieldCamp tracks all requests, gives complete picture of bot activity

## Artifacts

- `includes/class-ta-email-digest.php` - NEW: Complete email digest class
- `admin/views/email-digest-settings.php` - NEW: Settings page
- `admin/class-ta-admin.php` - Modified: Menu, handlers, render methods
- `includes/class-ta-bot-analytics.php` - Modified: Tracking methods
- `admin/views/bot-analytics-page.php` - Modified: Content Type filter
- `.playwright-mcp/email-digest-settings-page.png` - Screenshot of settings page

## Action Items & Next Steps

1. **ENHANCE AI Citations Page** - Add graphs, charts, and more useful features:
   `http://localhost:8080/wp-admin/admin.php?page=third-audience-ai-citations`

   Current state: Page works, shows data (6 citations, 5 platforms, filters work)

   **Features to add:**
   - Line chart: Citations over time (daily/weekly trend)
   - Bar chart: Citations by platform comparison
   - Pie chart: Platform distribution
   - Time-based filters (Last 7 days, 30 days, etc.)
   - Citation velocity metrics (growth rate)
   - Top referring queries analysis
   - Page performance comparison chart
   - Export to PDF option

   Reference: `admin/views/ai-citations-page.php`

2. **Test Email Digest** - Verify scheduled emails work on production
   - Test SMTP configuration
   - Verify cron jobs fire correctly

3. **Production Deployment** - Clear cache on www.monocubed.com after deploying v3.2.0

## Other Notes

### Menu Structure (current)
```
Bot Analytics (parent)
├── Bot Analytics
├── Bot Management
├── AI Citations  ← NEEDS INVESTIGATION
├── Cache Browser
├── System Health
├── Email Digest  ← NEW
└── About
[Hidden: Competitor Benchmarking, Citation Alerts]
```

### Docker Environment
- WordPress running at `http://localhost:8080`
- Login: admin/admin
- MySQL at port 3306, PHPMyAdmin at 8081

### Key Files for AI Citations Investigation
- `admin/views/ai-citations-page.php` - View template
- `admin/class-ta-admin.php:952-965` - Render method
- `includes/class-ta-bot-analytics.php` - Data methods (check for citation-specific queries)
