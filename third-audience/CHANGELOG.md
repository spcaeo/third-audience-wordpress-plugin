# Changelog

All notable changes to Third Audience plugin will be documented in this file.

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
