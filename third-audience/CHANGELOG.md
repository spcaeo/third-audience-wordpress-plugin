# Changelog

All notable changes to Third Audience plugin will be documented in this file.

## [3.5.0] - 2026-02-16

### Added
- **Client User Agent Tracking:** Added separate `client_user_agent` column to capture real browser user agent from JavaScript (Chrome, Safari, Edge, Firefox) instead of server-side "Headless Frontend" UA
- **HTTP Status Code Tracking:** Added `http_status` column to record HTTP response codes (200, 404, 500, etc.) to identify broken citations and error pages
- **Request Type Classification:** Added `request_type` column to distinguish between:
  - `html_page` - Initial HTML page loads (matches Nginx logs)
  - `rsc_prefetch` - Next.js React Server Component prefetch requests (internal navigation)
  - `js_fallback` - Client-side JavaScript tracker events (cached pages)
  - `api_call` - REST API and AJAX requests
- **Enhanced JavaScript Tracker:** Updated citation-tracker.js to capture and send real browser user agent via `navigator.userAgent`
- **Request Type Detection:** Added helper methods to automatically detect and classify request types based on URL parameters, request method, and context

### Changed
- **Database Schema:** Added 3 new nullable columns: `client_user_agent` (TEXT), `http_status` (INT), `request_type` (VARCHAR)
- **Database Version:** Bumped from 3.2.0 to 3.5.0 to trigger schema migration
- **Visit Tracking:** Updated `track_visit()` to accept and store new fields with proper format specifiers
- **AJAX Handler:** Enhanced to accept `client_user_agent` and `request_type` from JavaScript tracker
- **Bot Crawl Tracking:** Added request type detection to bot crawl tracking
- **Citation Tracking:** Added request type detection to citation click tracking

### Technical Details
- **Database Migration:** Uses WordPress `dbDelta()` for automatic column addition - non-breaking, backward compatible
- **Default Values:** All new columns are nullable with sensible defaults (NULL for UA, 'unknown' for request_type)
- **Performance:** Added database indexes for `request_type` and `http_status` columns for efficient querying
- **HTTP Status Capture:** Added `get_http_status()` helper that calls `http_response_code()` during request processing
- **RSC Detection:** Detects Next.js RSC prefetch via `?_rsc=` URL parameter
- **Dual UA Storage:** Server UA from `$_SERVER['HTTP_USER_AGENT']`, Client UA from JavaScript `navigator.userAgent`

### Benefits
- ✅ **Complete Visibility:** Track both initial HTML page loads AND internal Next.js navigation
- ✅ **Real Browser Data:** See actual Chrome/144, Safari, Edge versions instead of "Headless Frontend"
- ✅ **Error Detection:** Identify broken citations (404s) and server errors (500s)
- ✅ **Accurate Device Analytics:** Real client UA enables proper browser/device/OS reporting
- ✅ **Matches Nginx Logs:** html_page entries now align with access log structure

### Breaking Changes
- None - all changes are backward compatible with nullable columns and default values

## [3.4.9] - 2026-02-16

### Fixed
- **Android OS Detection:** Fixed user agent parser to correctly identify Android devices as "Android" instead of "Linux" by reordering OS detection logic to check for Android before Linux (since Android user agents contain both strings)
- **CSV Export Parser:** Applied same Android detection fix to CSV export function to ensure consistent OS reporting across both display and export features

### Technical Details
- Android user agents contain "Linux; Android" pattern, so specific OS checks (Android, iOS) must run before generic OS checks (Linux)
- Fix applied in two locations: `ta_parse_user_agent()` helper function and CSV export `$parse_ua()` closure
- No database or query changes required - only parser logic updated

## [3.4.8] - 2026-02-16

### Added
- **Browser & Device Tracking Display:** Added new columns to AI Citations page showing browser name, operating system, and device type (Desktop/Mobile) parsed from user agent data
- **Location/Country Display:** Added country flag and code column showing visitor geographic location
- **Advanced Filtering:** Added three new filter dropdowns for Browser, Country, and Device Type to filter citation data
- **CSV Export with Full Data:** Added comprehensive CSV export button that includes all visible data plus full user agent strings and IP addresses for detailed analysis
- **User Agent Parser:** Added helper function to parse user agent strings and extract browser, OS, and device information
- **Country Flag Generator:** Added helper function to convert 2-letter country codes to Unicode flag emojis

### Changed
- **Filter Layout:** Updated filter section to 3x3 grid layout with better organization
- **Table Columns:** Expanded Recent Citations table from 8 to 10 columns with new browser and location data
- **Export Button:** Changed CSV export action name from generic 'export' to 'ta_export_citations_csv' for better specificity
- **Query Enhancement:** Updated SQL queries to include user_agent, ip_address, and country_code fields

### Technical Details
- All data was already being captured in database - only display layer was updated
- No database schema changes required
- Backward compatible with existing citation data
- CSV export includes 17 columns with full technical details for analysis

## [3.4.7] - 2026-02-09

### Fixed
- **Category Permalink Resolution:** Enhanced URL resolution for category-based permalinks

## [3.4.6] - 2026-02-05

### Removed
- **Crawl Budget Recommendations:** Removed entire section from Bot Analytics page (advisory feature, not essential)
- **Download Report Now Buttons:** Removed manual download buttons from Email Digest Settings page (scheduled digests work independently)
- **Content Performance Insights Export:** Removed CSV export button (exported per-post data instead of summary, causing confusion)
- **Activity Timeline Period Selector:** Removed hourly/daily/weekly/monthly dropdown (weekly/monthly not functioning correctly, now defaults to daily view)
- **Top Bots by Session Activity Export:** Removed CSV export button (not functioning properly)

### Changed
- **Bot Analytics UI Cleanup:** Streamlined interface by removing non-essential export buttons and problematic filtering options
- **Email Digest Settings:** Simplified page by removing redundant manual download options

## [3.4.5] - 2026-02-05

### Fixed
- **Email Digest Settings Page Blank:** Fixed filename mismatch causing email digest settings page to be completely blank (`email-digest-page.php` → `email-digest-settings.php`)
- **Download Report Not Working:** Fixed 5 bugs preventing report download:
  - Action name mismatch (`download_md` → `download_report`)
  - Nonce name mismatch (`ta_download_report` → `ta_download_digest_report`)
  - Wrong method called (`generate_markdown_report()` → `generate_md_report()`)
  - Missing data gathering step (now calls `gather_digest_data()`)
  - Period parameter now properly used (24 hours vs 7 days)

### Changed
- **Notifications Tab Cleanup:** Removed duplicate "Daily digest summary" checkbox from Settings → Notifications tab
- **Email Digest Redirect:** Added prominent redirect card in Notifications tab directing users to Email Digest page for bot activity reports
- **Clearer Separation:** Notifications tab now focused on system/error alerts only, Email Digest page handles all bot report settings

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
