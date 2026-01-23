# Citation Alert System (v2.8.0)

## Overview

The Citation Alert System automatically monitors AI citation traffic and notifies site owners when significant events occur. This helps site owners stay informed about changes in AI platform visibility without manual checking.

## Features

### Alert Types

1. **First Citation** - Congratulations when receiving first citation from any AI platform
2. **New Platform** - Notification when a new AI platform discovers your content
3. **Citation Spike** - Alert when citations increase by 2x in 24 hours
4. **Citation Drop** - Warning when citations drop by 50% in 24 hours (min 5 citations)
5. **High Performance** - Notification when a page gets 10+ citations in 24 hours
6. **Verification Failure** - Warning when bot IP verification failures spike

### Alert Display

- **Admin Notices**: Alerts appear as WordPress admin notices on Third Audience pages
- **Dismissible**: Users can dismiss alerts with a single click
- **Smart Caching**: Alert checks run hourly to minimize performance impact
- **Severity Levels**:
  - `success` - Positive events (first citation, high performance)
  - `info` - Informational (citation spike, new platform)
  - `warning` - Issues requiring attention (citation drop, verification failures)

### Alert History

Access the full alert history at: `wp-admin/admin.php?page=third-audience-citation-alerts`

Features:
- View all alerts with pagination
- Filter by type, severity, and status
- See statistics (30-day totals, active alerts, warning counts)
- Track dismissed vs. active alerts

## Technical Implementation

### Files Created

1. **includes/class-ta-citation-alerts.php** - Alert detection engine
   - `check_alerts()` - Runs hourly checks for all alert conditions
   - `check_first_citation()` - Detects first citations from platforms
   - `check_new_platform()` - Detects new AI platforms
   - `save_alert()` - Stores alerts in database
   - `dismiss_alert()` - Handles alert dismissal

2. **admin/views/citation-alerts-page.php** - Alert history page view
   - Statistics dashboard
   - Filterable alert table
   - Pagination support

### Files Modified

1. **admin/class-ta-admin.php**
   - Added `display_citation_alerts()` - Shows alerts as admin notices
   - Added `ajax_dismiss_alert()` - AJAX handler for dismissal
   - Added `render_citation_alerts_page()` - Renders alert history page
   - Registered new menu item (hidden submenu)

2. **includes/class-ta-bot-analytics.php**
   - Modified `track_visit()` to trigger citation alerts
   - Calls `check_first_citation()` and `check_new_platform()` on citation clicks

3. **admin/js/admin.js**
   - Added `dismissCitationAlert()` - AJAX dismissal handler
   - Bound event listener for dismiss buttons

4. **third-audience.php**
   - Updated plugin version to 2.8.0

### Database Schema

New table: `wp_ta_citation_alerts`

```sql
CREATE TABLE wp_ta_citation_alerts (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    alert_type varchar(50) NOT NULL,
    severity varchar(20) NOT NULL DEFAULT 'info',
    title varchar(255) NOT NULL,
    message text NOT NULL,
    metadata longtext DEFAULT NULL,
    dismissed tinyint(1) NOT NULL DEFAULT 0,
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY alert_type (alert_type),
    KEY severity (severity),
    KEY dismissed (dismissed),
    KEY created_at (created_at)
);
```

### Performance Optimization

- **Hourly Checks**: Alert conditions checked once per hour (transient cache)
- **Duplicate Prevention**: Same alert type won't fire within 1 hour
- **Query Optimization**: Indexed columns for fast filtering
- **Automatic Cleanup**: Alerts older than 90 days are automatically purged

## Usage

### For End Users

1. **View Active Alerts**: Alerts automatically appear as WordPress admin notices on Third Audience pages
2. **Dismiss Alerts**: Click the dismiss (X) button on any alert
3. **View History**: Navigate to the alerts page via direct link or settings
4. **Filter Alerts**: Use filters to find specific types or severities

### For Developers

**Manually Trigger Alert Check:**

```php
if ( class_exists( 'TA_Citation_Alerts' ) ) {
    $citation_alerts = TA_Citation_Alerts::get_instance();
    $alerts = $citation_alerts->check_alerts();
}
```

**Check for First Citation:**

```php
$alert = $citation_alerts->check_first_citation( 'ChatGPT' );
if ( $alert ) {
    // Handle first citation
}
```

**Get Alert Statistics:**

```php
$stats = $citation_alerts->get_statistics();
// Returns: total_alerts, active_alerts, dismissed_alerts, warning_alerts, success_alerts
```

**Cleanup Old Alerts:**

```php
$deleted = $citation_alerts->cleanup_old_alerts();
// Removes alerts older than 90 days
```

## Future Enhancements

Potential improvements for future versions:

1. **Email Notifications**
   - Send email alerts for critical events
   - Configurable frequency (immediate, daily digest, weekly)
   - User preference for which alert types to receive

2. **Webhook Integration**
   - Fire webhooks on alert creation
   - Integration with Slack, Discord, or custom endpoints

3. **Custom Thresholds**
   - Allow users to customize spike/drop percentages
   - Adjustable citation count minimums
   - Platform-specific thresholds

4. **Dashboard Widget**
   - Show recent alerts on WordPress dashboard
   - Quick stats overview
   - One-click navigation to alert history

5. **Alert Scheduling**
   - User-configurable check frequency
   - Quiet hours (don't show alerts during certain times)
   - Time zone support

## Changelog

### v2.8.0 (2026-01-22)

- Initial release of Citation Alert System
- Support for 6 alert types
- Admin notice integration
- Alert history page
- Hourly automatic checks
- AJAX dismissal
- Database table creation with migration support
