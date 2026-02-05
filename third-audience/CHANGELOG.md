# Changelog

All notable changes to Third Audience plugin will be documented in this file.

## [3.4.5] - 2026-02-05

### Fixed
- **Email Digest Settings Page Blank:** Fixed filename mismatch causing email digest settings page to be completely blank (`email-digest-page.php` → `email-digest-settings.php`)
- **Download Report Not Working:** Fixed 5 bugs preventing report download:
  - Action name mismatch (`download_md` → `download_report`)
  - Nonce name mismatch (`ta_download_report` → `ta_download_digest_report`)
  - Wrong method called (`generate_markdown_report()` → `generate_md_report()`)
  - Missing data gathering step (now calls `gather_digest_data()`)
  - Period parameter now properly used (24 hours vs 7 days)

## [3.4.4] - 2026-02-05

### Fixed
- **Bot Activity Distribution Display:** Fixed field name mismatch causing all bots to show 0 visits (query returned `visit_count` but display expected `count`)
- **Content Performance Insights:** Fixed multiple field name mismatches preventing content metrics from displaying (`avg_headings` → `avg_heading_count`, `avg_images` → `avg_image_count`, `schema_rate` → `schema_percentage`, `sample_size` → `total_count`)
- **Content Performance Insights:** Added missing `avg_freshness_days` field to content analysis queries
- **Duplicate Citation Tracking:** Improved deduplication logic to prevent same citation from being tracked twice by server-side and client-side tracking systems
- **Perplexity Search Query Extraction:** Updated to support new Perplexity URL format (`/search/query-slug-id` instead of `?q=query`)

### Changed
- **Search Query Column:** Added informative tooltip icons explaining that search queries are only available from Perplexity, Google AI Overview, and Bing Copilot
- **Deduplication Algorithm:** Now strips query parameters from URLs and matches base paths to catch duplicates with/without UTM parameters

## [3.4.0] - 2026-02-02

### Added - Zero-Configuration Auto-Deployment System 🎉
- **Environment Auto-Detection:** Automatically detects hosting environment, server type, security plugins, caching plugins, and database permissions on activation
- **Security Plugin Auto-Whitelisting:** Automatically configures Wordfence, iThemes Security, Sucuri, and All In One WP Security to allow Third Audience endpoints
- **AJAX Fallback System:** Automatically switches to admin-ajax.php endpoints when REST API is blocked by security plugins or server firewalls
- **Database Auto-Fixer:** Automatically creates missing tables, adds missing columns, fixes column types, and adds indexes without user intervention
- **Daily Health Checks:** Scheduled daily cron to re-detect environment changes and auto-fix issues
- **Smart Endpoint Detection:** JavaScript client that automatically detects and uses best available endpoint (REST or AJAX)
- **Admin Notices System:** Informative notices showing auto-configuration results and environment status

### Changed
- **Activation Hook:** Enhanced to run full environment detection and auto-configuration
- **REST API Registration:** Now conditionally registers endpoints based on accessibility
- **Plugin Architecture:** Added modular auto-deployment classes for maintainability

### Technical Details
- **New Files:**
  - `includes/class-ta-environment-detector.php` - Environment detection engine
  - `includes/class-ta-ajax-fallback.php` - AJAX fallback endpoint system
  - `includes/class-ta-security-bypass.php` - Security plugin auto-whitelisting
  - `includes/class-ta-database-auto-fixer.php` - Database auto-repair system
  - `admin/class-ta-admin-notices.php` - Admin notification system
  - `assets/js/ta-auto-endpoint-detector.js` - Frontend auto-detection client
  - `AUTO-DEPLOYMENT.md` - Comprehensive deployment guide
  - `BROWSER-TESTING.md` - Browser-based testing guide for non-technical users
  - `test-citations-browser.html` - Interactive HTML testing tool with visual interface
  - `test-data-tracking.sh` - Automated bash script for command-line testing

### Deployment Benefits
- ✅ **Zero server configuration required** - No .htaccess, wp-config.php, or SQL changes needed
- ✅ **Works on any hosting provider** - Shared hosting, managed WordPress, VPS, local development
- ✅ **Bypasses security restrictions** - Auto-configures security plugins and uses fallbacks
- ✅ **Self-healing** - Automatically detects and fixes issues daily
- ✅ **Developer-friendly** - Smart JavaScript client requires no endpoint configuration

---

## [3.3.10] - 2026-02-02

### Fixed
- **Critical Database Error:** Fixed "Unknown column 'content_type' in 'field list'" error that prevented bot visits from being tracked (100+ errors per day eliminated)
- **Email Notification Spam:** Added rate limiting to high error rate notifications (maximum 1 email per hour instead of unlimited duplicates)
- **Bot Detection Method Error:** Fixed incorrect method name `get_detection_method()` → `get_method()` that caused PHP errors in bot detection pipeline
- **Headless Settings Not Saving:** Fixed nonce verification issue that prevented headless mode checkbox from saving properly

### Added
- Database migration system for automated schema updates
- Migration class for v3.3.10 to add missing `content_type` column
- Comprehensive deployment and testing documentation

### Changed
- Database version updated from 3.2.0 to 3.3.10
- Improved error handling in visit tracking system

### Technical Details
- **Files Modified:**
  - `third-audience.php` - Added migration runner, updated version constants
  - `includes/class-ta-notifications.php` - Added rate limiting to `on_high_error_rate()`
  - `includes/Analytics/class-ta-bot-detector.php` - Fixed method name
  - `admin/AJAX/class-ta-admin-settings.php` - Fixed headless settings nonce verification
- **Files Added:**
  - `includes/migrations/class-ta-migration-3-3-10.php` - Database migration for content_type column
  - `FIXES_APPLIED.md` - Detailed technical documentation
  - `DEPLOYMENT_GUIDE.md` - Deployment and testing guide
  - `verify-fixes.sh` - Automated verification script

### Migration Notes
- Migration runs automatically on first admin page load after update
- Adds `content_type VARCHAR(50)` column to `wp_ta_bot_analytics` table
- Migration is idempotent (safe to run multiple times)

---

## [3.3.9] - Previous Release

### Added
- Comprehensive raw data exports for all Bot Analytics tabs
- Real-time version check from GitHub main branch
- JavaScript-based citation tracking for cached pages

### Fixed
- Enhanced AI Citations page with date/time columns
- Improved citation tracking for cached pages

---

## Version History

For older versions, see commit history in Git repository.

---

**Note:** This changelog follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) format and adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).
