# Third Audience — Installation Guide

**Version:** 3.5.3+
**Last updated:** March 2026

---

## Requirements

| Requirement | Minimum | Recommended |
|-------------|---------|-------------|
| WordPress   | 5.8     | 6.5+        |
| PHP         | 7.4     | 8.1–8.3     |
| MySQL       | 5.7     | 8.0+        |

---

## Installing the Plugin

### Method 1 — Upload via WordPress Admin (recommended)

1. Download the plugin ZIP file (e.g. `third-audience-3.5.4.zip`)
2. In your WordPress dashboard go to **Plugins → Add New Plugin**
3. Click **Upload Plugin** at the top
4. Choose the ZIP file and click **Install Now**
5. After installation click **Activate Plugin**
6. Go to **Third Audience** in the left menu to complete setup

> **Important:** Only use the ZIP files provided by Third Audience.
> Do **not** use a ZIP exported from GitHub — those have a wrapper folder
> (`third-audience-wordpress-plugin-x.x.x/`) that prevents WordPress from
> reading the plugin correctly and will show an "invalid plugin" error.
> The correct ZIP extracts directly to `third-audience/` at its root.

---

### Method 2 — FTP / File Manager

1. Unzip the plugin ZIP on your computer
2. Upload the `third-audience/` folder to `/wp-content/plugins/` on your server
3. In WordPress admin go to **Plugins** and click **Activate** next to Third Audience

---

### Method 3 — WP-CLI

```bash
wp plugin install /path/to/third-audience-3.5.4.zip --activate
```

---

## After Activation

On the **first admin page load** after activation the plugin will:

1. Create all required database tables automatically
2. Run a REST API health check to detect your server environment
3. Enable AJAX fallback mode automatically if REST API is blocked by a security plugin

You do not need to configure anything manually — the plugin self-configures.

---

## Troubleshooting

### "Plugin could not be activated because it triggered a fatal error"

This happened in versions before 3.5.4. It was caused by:

- A `sleep(2)` delay plus two HTTP requests (up to 12 seconds) inside the
  activation hook, which exceeded PHP's `max_execution_time` on restricted hosts
- Running `SHOW COLUMNS FROM wp_ta_bot_analytics` before the table existed
  on a fresh install — causing a MySQL error that WordPress treated as fatal
- The database auto-fixer creating a table with the wrong column names
  (`page_url`, `visited_at`, `cache_hit`) instead of what the application
  actually uses (`url`, `visit_timestamp`, `request_method`, etc.)

**Fix:** Update to version 3.5.4 or later. All three issues are resolved:
- Environment detection is deferred to the first `admin_init` after activation
- Table existence is checked before any `SHOW COLUMNS FROM` queries
- The database schema in `TA_Database_Auto_Fixer` matches the production schema

---

### "Plugin already installed" / Can't upload ZIP

Go to **Plugins** and check if Third Audience is already listed. If it is,
deactivate and delete it first, then re-upload the new ZIP.

---

### Database tables not created

Go to **Third Audience → System Health**. If any table is missing, click
**Run Auto-Fix**. The plugin checks and repairs database schema automatically
on every admin load (once per hour via transient cache).

---

### REST API blocked — AJAX fallback mode

If your host or a security plugin blocks the WordPress REST API, Third Audience
automatically switches to AJAX fallback mode (via `admin-ajax.php`). This is
detected on the first admin page load after activation and requires no manual
configuration.

If you install a security plugin *after* Third Audience is activated, go to
**Third Audience → Settings** and click **Re-run Environment Detection** to
re-check REST API access.

---

### White screen / 500 error after activation on PHP 8.x

Check your PHP error log. The most common cause is a missing Composer
`vendor/` directory. The plugin requires `league/html-to-markdown` (bundled
in the official ZIP). If you installed from a raw GitHub export the `vendor/`
folder may be absent. Use an official release ZIP which includes all dependencies.

---

## What Changed in v3.5.4 (Fresh Install Fix)

### Problem
The plugin failed to activate on fresh WordPress installs, showing:
*"Plugin could not be activated because it triggered a fatal error."*

This affected clean installs on hosts with PHP `max_execution_time` ≤ 30 seconds.

### Root Causes Fixed

**1. Activation hook blocked for 12+ seconds**
`ta_activate()` called `sleep(2)` then made two HTTP requests with 5-second
timeouts each to test REST API access. On a fresh install the REST API routes
aren't registered yet (they register on `rest_api_init` which runs after
`plugins_loaded` — a hook that never fires during plugin activation). Both
requests always timed out, blocking activation for up to 12 seconds.

*Fix:* Removed `sleep()` and all HTTP requests from the activation hook.
Environment detection is now scheduled via transient and runs on the first
`admin_init` after activation, when REST API routes are properly registered.

**2. `SHOW COLUMNS FROM` on a non-existent table**
Three functions (`ta_activation_hook`, `ta_auto_fix_database`,
`ta_admin_notice_db_fix`) ran `SHOW COLUMNS FROM wp_ta_bot_analytics` without
first checking whether the table existed. On a fresh install the table doesn't
exist when these functions run, producing a MySQL error.

*Fix:* Each function now runs `SHOW TABLES LIKE` first and returns early if
the table doesn't exist yet.

**3. Database auto-fixer created the wrong table schema**
`TA_Database_Auto_Fixer::create_bot_analytics_table()` created a table with
columns `page_url`, `page_title`, `cache_hit`, `is_citation`, `status_code`,
`visited_at` — none of which match what the application code actually inserts
into (`url`, `post_title`, `request_method`, `http_status`, `visit_timestamp`,
etc.). Every database insert after activation would fail with
"Unknown column 'url' in field list".

*Fix:* The schema in `TA_Database_Auto_Fixer` now matches the schema in
`TA_Bot_Analytics::maybe_create_table()` exactly.

**Files changed:**
- `third-audience/third-audience.php`
- `third-audience/includes/class-ta-database-auto-fixer.php`

---

## Uninstalling

1. Go to **Plugins** → Deactivate Third Audience
2. Click **Delete**

The plugin's database tables and stored options are **not** deleted automatically
on uninstall to protect your analytics data. To remove them permanently, run
the SQL commands listed in `uninstall.php` manually via phpMyAdmin or a DB client.
