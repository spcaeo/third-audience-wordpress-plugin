# WordPress Server Debugging Guide 🔧

## Quick Fix Checklist

### 1. **Use HTTP, not HTTPS**
```
❌ WRONG: https://localhost:8080
✅ CORRECT: http://localhost:8080
```

Your Docker setup doesn't have SSL configured, so use `http://` (no 's').

### 2. **Check if Docker is running**
```bash
docker ps
```

You should see these containers:
- `ta-wordpress`
- `ta-mysql`
- `ta-phpmyadmin`
- `ta-wpcli`

### 3. **Start Docker containers if not running**
```bash
cd /Users/rakesh/Desktop/Projects/third-audience-jeel
docker-compose up -d
```

Wait 30-60 seconds for services to start, then check:
```bash
docker ps
```

### 4. **Test WordPress accessibility**
```bash
curl -I http://localhost:8080
```

Should return `HTTP/1.1 200 OK` or `HTTP/1.1 302 Found`

### 5. **Check WordPress logs**
```bash
docker logs ta-wordpress
```

Look for errors like:
- Database connection errors
- PHP errors
- Permission issues

### 6. **Check MySQL**
```bash
docker logs ta-mysql
```

Should see: `ready for connections`

## Step-by-Step Troubleshooting

### Problem: Port 8080 already in use

**Check what's using port 8080:**
```bash
lsof -i :8080
```

**Solution 1: Stop the other service**
```bash
# If it's another Docker container
docker stop <container-id>
```

**Solution 2: Change the port**
Edit `docker-compose.yml`:
```yaml
ports:
  - "8081:80"  # Change 8080 to 8081
```

Then restart:
```bash
docker-compose down
docker-compose up -d
```

Access at: http://localhost:8081

### Problem: Containers won't start

**Check for errors:**
```bash
docker-compose logs
```

**Common issues:**
1. **Docker not running** - Start Docker Desktop
2. **Port conflicts** - Change ports in docker-compose.yml
3. **Volume permission issues** - Clear volumes and restart:
   ```bash
   docker-compose down -v
   docker-compose up -d
   ```

### Problem: WordPress shows database connection error

**Fix database connection:**
```bash
# Restart just the database
docker-compose restart db

# Wait 10 seconds
sleep 10

# Restart WordPress
docker-compose restart wordpress
```

**Verify database is accessible:**
```bash
docker exec ta-mysql mysqladmin -u wordpress -pwordpress ping
```

Should return: `mysqld is alive`

### Problem: WordPress not installed

**Install WordPress:**
```bash
./testing/setup-wordpress.sh
```

Or manually:
```bash
docker exec -u 33:33 ta-wpcli wp core install \
  --url="http://localhost:8080" \
  --title="Third Audience Test" \
  --admin_user="admin" \
  --admin_password="admin123" \
  --admin_email="admin@example.com" \
  --skip-email
```

### Problem: Plugin not activated

**Activate plugin:**
```bash
docker exec -u 33:33 ta-wpcli wp plugin activate third-audience
```

**Verify plugin is active:**
```bash
docker exec -u 33:33 ta-wpcli wp plugin list
```

Should show `third-audience` as `active`.

### Problem: Analytics page not found (404)

**Flush rewrite rules:**
```bash
docker exec -u 33:33 ta-wpcli wp rewrite flush --hard
```

**Check if analytics table exists:**
```bash
docker exec ta-mysql mysql -u wordpress -pwordpress wordpress -e "SHOW TABLES LIKE '%analytics%'"
```

Should show: `wp_ta_bot_analytics`

### Problem: Blank white page

**Check PHP errors:**
```bash
docker exec ta-wordpress cat /var/www/html/wp-content/debug.log
```

**Enable WordPress debug mode** (if not already):
Edit via WP-CLI:
```bash
docker exec -u 33:33 ta-wpcli wp config set WP_DEBUG true --raw
docker exec -u 33:33 ta-wpcli wp config set WP_DEBUG_LOG true --raw
docker exec -u 33:33 ta-wpcli wp config set WP_DEBUG_DISPLAY false --raw
```

### Problem: Permission denied errors

**Fix WordPress file permissions:**
```bash
docker exec ta-wordpress chown -R www-data:www-data /var/www/html
```

## Complete Reset (Nuclear Option)

If nothing works, start fresh:

```bash
cd /Users/rakesh/Desktop/Projects/third-audience-jeel

# Stop and remove everything
docker-compose down -v

# Remove any orphaned containers
docker container prune -f

# Remove any orphaned volumes
docker volume prune -f

# Start fresh
docker-compose up -d

# Wait 60 seconds
sleep 60

# Setup WordPress
./testing/setup-wordpress.sh

# Test accessibility
curl -I http://localhost:8080
```

## Verification Commands

After fixing, run these to verify everything works:

```bash
# 1. Check containers are running
docker ps | grep ta-

# 2. Check WordPress is accessible
curl -s http://localhost:8080 | grep -q "WordPress" && echo "✓ WordPress accessible"

# 3. Check admin is accessible
curl -s http://localhost:8080/wp-admin | grep -q "wp-login" && echo "✓ Admin accessible"

# 4. Check database is responding
docker exec ta-mysql mysqladmin -u wordpress -pwordpress ping && echo "✓ Database OK"

# 5. Check plugin is active
docker exec -u 33:33 ta-wpcli wp plugin list | grep third-audience | grep active && echo "✓ Plugin active"

# 6. Check analytics table exists
docker exec ta-mysql mysql -u wordpress -pwordpress wordpress -e "SHOW TABLES LIKE '%analytics%'" | grep analytics && echo "✓ Analytics table exists"
```

## Quick Health Check Script

Run this to check everything at once:

```bash
./testing/health-check.sh
```

(I'll create this script next)

## Common URLs

Make sure you're using the correct URLs:

| Service | URL | Notes |
|---------|-----|-------|
| WordPress Home | http://localhost:8080 | Use HTTP not HTTPS |
| Admin Login | http://localhost:8080/wp-admin | |
| Bot Analytics | http://localhost:8080/wp-admin/admin.php?page=third-audience-bot-analytics | Login first |
| Settings | http://localhost:8080/wp-admin/options-general.php?page=third-audience | |
| phpMyAdmin | http://localhost:8081 | wordpress / wordpress |

## Get Help

If still not working, gather this info:

```bash
# Save diagnostic info
{
  echo "=== Docker PS ==="
  docker ps

  echo -e "\n=== Docker Logs (WordPress) ==="
  docker logs --tail 50 ta-wordpress

  echo -e "\n=== Docker Logs (MySQL) ==="
  docker logs --tail 50 ta-mysql

  echo -e "\n=== Port Check ==="
  lsof -i :8080

  echo -e "\n=== WordPress Version ==="
  docker exec -u 33:33 ta-wpcli wp core version 2>/dev/null || echo "WP-CLI not responding"

  echo -e "\n=== Plugin Status ==="
  docker exec -u 33:33 ta-wpcli wp plugin list 2>/dev/null || echo "Cannot get plugin status"

} > testing/diagnostics.txt

cat testing/diagnostics.txt
```

Then share the `testing/diagnostics.txt` file.

## Next Steps After Fixing

Once WordPress is accessible:

1. **Login to admin:**
   http://localhost:8080/wp-admin
   (admin / admin123)

2. **Verify plugin is active:**
   Plugins → Installed Plugins → Third Audience should be active

3. **Check Bot Analytics page:**
   Look for "Bot Analytics" in the left sidebar

4. **Run bot crawler:**
   ```bash
   ./testing/bot-crawler.sh
   ```

5. **Verify analytics:**
   ```bash
   ./testing/verify-analytics.sh
   ```

6. **Run QA automation:**
   ```bash
   ./testing/playwright-qa-automation.sh
   ```
