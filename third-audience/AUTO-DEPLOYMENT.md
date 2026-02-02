# Third Audience - Zero-Configuration Auto-Deployment

## Overview

Version 3.4.0+ includes **automatic environment detection** and **self-configuration** that makes the plugin work on **ANY server** without manual configuration.

## What Happens Automatically

When you activate the plugin, it automatically:

1. ✅ **Detects your hosting environment**
2. ✅ **Configures security plugins** (Wordfence, iThemes, Sucuri, etc.)
3. ✅ **Creates and fixes database tables**
4. ✅ **Enables fallback modes** if REST API is blocked
5. ✅ **Tests all systems** and reports status
6. ✅ **Schedules daily health checks** and auto-repair

## Installation (Zero Configuration Required)

### Method 1: Via WordPress Admin
```
1. Upload third-audience.zip
2. Click "Activate"
3. Done! ✅
```

### Method 2: Via FTP
```
1. Upload /third-audience/ to /wp-content/plugins/
2. Activate in WordPress Admin → Plugins
3. Done! ✅
```

**No .htaccess editing, no wp-config.php changes, no SQL commands needed!**

## What Gets Detected Automatically

### 1. REST API Accessibility
- Tests if `/wp-json/` endpoints are accessible
- If blocked, automatically switches to AJAX fallback mode
- Everything works the same regardless of mode

### 2. Security Plugins
Automatically whitelists endpoints in:
- ✅ Wordfence
- ✅ iThemes Security
- ✅ Sucuri Security
- ✅ All In One WP Security
- ✅ Others via generic detection

### 3. Server Type
- Detects Apache, Nginx, LiteSpeed
- Adapts configuration accordingly
- No manual server configuration needed

### 4. Database Permissions
- Tests CREATE, ALTER, INSERT permissions
- Automatically fixes missing tables and columns
- Provides manual SQL if auto-fix fails

### 5. Caching Plugins
Detects and adapts to:
- WP Rocket
- W3 Total Cache
- WP Super Cache
- LiteSpeed Cache
- And more...

## After Activation

### You'll See This Notice:

```
🎉 Third Audience Activated Successfully

Auto-configuration completed:

✅ REST API: Accessible - Using standard endpoints
   OR
⚠️ REST API: Blocked - Using AJAX fallback mode
   • Blocker: Wordfence
   • Fallback: admin-ajax.php endpoints activated
   • Everything still works normally!

🔒 Security Plugin Detected: Wordfence
   • ✅ Auto-whitelisted Third Audience endpoints

✅ Database: All permissions OK - Tables created successfully

💾 Caching Plugin Detected: WP Rocket
   • ✅ Admin pages excluded from cache automatically

🖥️ Server: nginx
🐘 PHP: 8.1
📦 WordPress: 6.4

✨ No server configuration needed - everything is configured automatically!
```

## Frontend Integration

### For Headless Sites (Next.js, Gatsby, etc)

The plugin includes an **auto-detecting JavaScript client** that works regardless of your server configuration.

#### Install in your frontend:

```bash
# Copy the JS file from the plugin:
cp wp-content/plugins/third-audience/assets/js/ta-auto-endpoint-detector.js \
   your-frontend/lib/third-audience.js
```

#### Usage Example (Next.js):

```javascript
import ThirdAudienceTracker from '@/lib/third-audience';

// Initialize tracker
const tracker = new ThirdAudienceTracker(
    process.env.WORDPRESS_URL,
    process.env.THIRD_AUDIENCE_API_KEY
);

// Enable debug mode (optional)
tracker.enableDebug();

// Track citation - automatically uses best endpoint
async function trackCitation() {
    try {
        const result = await tracker.trackCitation({
            url: window.location.pathname,
            platform: 'ChatGPT',
            searchQuery: 'optional search query'
        });

        console.log('Citation tracked:', result);
    } catch (error) {
        console.error('Tracking failed:', error);
    }
}
```

#### The tracker automatically:
1. Tests REST API first
2. Falls back to AJAX if REST is blocked
3. Handles all security configurations
4. Retries with fallback on failure

## Troubleshooting

### Check System Health

Go to: **Settings → Third Audience → System Health**

You'll see:
- Current environment status
- REST API accessibility
- Database status
- Security plugin configuration
- Any detected issues
- Auto-fix buttons

### If Something Doesn't Work

#### REST API Blocked?
- **Status:** Plugin automatically switches to AJAX fallback
- **Action:** Everything works normally, no action needed
- **Optional:** Contact hosting to enable REST API for better performance

#### Database Permission Issues?
- **Status:** Plugin shows manual SQL commands
- **Action:** Run provided SQL in phpMyAdmin, or contact hosting

#### Security Plugin Blocking?
- **Status:** Plugin auto-whitelists on activation
- **Action:** If still blocked, see manual configuration instructions in System Health

## Advanced: Test Connection from Frontend

```javascript
const tracker = new ThirdAudienceTracker(
    'https://your-wordpress-site.com',
    'your-api-key'
);

tracker.enableDebug();

// Test both REST and AJAX endpoints
const results = await tracker.testConnection();

console.log('Connection test:', results);
// Output:
// {
//   rest: { available: true, data: {...} },
//   ajax: { available: true, data: {...} }
// }
```

## Supported Hosting Providers

The plugin auto-configures on **ALL** hosting providers:

### Shared Hosting
- ✅ Bluehost
- ✅ HostGator
- ✅ GoDaddy
- ✅ SiteGround
- ✅ DreamHost

### Managed WordPress
- ✅ WP Engine
- ✅ Kinsta
- ✅ Flywheel
- ✅ Pagely
- ✅ Pressable

### Cloud/VPS
- ✅ DigitalOcean
- ✅ AWS (Lightsail/EC2)
- ✅ Google Cloud
- ✅ Linode
- ✅ Vultr

### Local Development
- ✅ XAMPP
- ✅ MAMP
- ✅ Local by Flywheel
- ✅ Docker
- ✅ Vagrant

## Daily Auto-Maintenance

The plugin runs a **daily health check** that:

1. Re-detects environment (in case something changed)
2. Auto-fixes any database issues
3. Switches from fallback to REST if API becomes available
4. Logs all actions

**No manual maintenance required!**

## Environment Detection API

For advanced users who want to check environment programmatically:

```php
// Get environment detection results
$env = get_option('ta_environment_detection');

echo "REST API: " . ($env['rest_api']['accessible'] ? 'Yes' : 'No') . "\n";
echo "Server: " . $env['server_type'] . "\n";
echo "PHP: " . $env['php_version'] . "\n";
echo "Security Plugin: " . ($env['security_plugins'] ?: 'None') . "\n";
```

## Manual Override (If Needed)

In rare cases, you can force specific behavior:

```php
// In wp-config.php (before "That's all, stop editing!")

// Force REST API mode (disable fallback)
define('TA_FORCE_REST_API', true);

// Force AJAX fallback mode
define('TA_FORCE_AJAX_FALLBACK', true);

// Disable auto-whitelisting of security plugins
define('TA_DISABLE_AUTO_WHITELIST', true);

// Disable daily health checks
define('TA_DISABLE_HEALTH_CHECKS', true);
```

## API Endpoints

The plugin provides two sets of endpoints that work identically:

### REST API Endpoints (if accessible)
```
POST https://your-site.com/wp-json/third-audience/v1/track-citation
GET  https://your-site.com/wp-json/third-audience/v1/health
```

### AJAX Fallback Endpoints (if REST blocked)
```
POST https://your-site.com/wp-admin/admin-ajax.php?action=ta_track_citation
POST https://your-site.com/wp-admin/admin-ajax.php?action=ta_health_check
```

**The JavaScript client auto-selects the correct one!**

## Security

All auto-configuration is done securely:
- ✅ No external API calls
- ✅ All detection runs locally
- ✅ No data sent to third parties
- ✅ Security plugin whitelisting uses their official APIs
- ✅ Database operations use WordPress's dbDelta
- ✅ All inputs sanitized and validated

## Performance

Auto-detection runs:
- ✅ Once on activation (~2 seconds)
- ✅ Once daily via cron (in background)
- ✅ Does not slow down your site
- ✅ Results are cached

## Support

If you encounter any issues:

1. Check **System Health** page first
2. Enable debug mode in tracker (see examples above)
3. Check WordPress debug.log
4. Report issues with environment details

## Summary

**You don't need to configure anything!**

The plugin:
- Detects everything automatically
- Configures itself
- Fixes issues automatically
- Adapts to your server
- Works on any hosting
- Requires zero technical knowledge

Just activate and use! 🎉
