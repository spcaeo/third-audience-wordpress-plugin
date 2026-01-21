# Third Audience - Testing Suite 🧪

Complete testing environment for the Third Audience WordPress plugin with automated setup, bot simulation, and analytics verification.

## 📋 What's Included

### 1. **Docker WordPress Environment**
- WordPress 6.7 (latest)
- MySQL 8.0
- phpMyAdmin
- WP-CLI for automation
- Health checks for all services

### 2. **Test Scripts**

#### `run-full-test.sh` - Master Test Suite
Complete end-to-end testing automation:
- Starts Docker containers
- Sets up WordPress
- Installs & activates plugin
- Creates test content
- Runs bot crawler simulation
- Verifies analytics
- Generates test report

```bash
./testing/run-full-test.sh
```

#### `setup-wordpress.sh` - WordPress Setup
Automated WordPress installation and configuration:
- Installs WordPress
- Activates Third Audience plugin
- Configures plugin settings
- Creates 5 test posts
- Creates 3 test pages
- Flushes rewrite rules

```bash
./testing/setup-wordpress.sh
```

#### `bot-crawler.sh` - SneakyBot Crawler 🕵️
Simulates AI bot visits with multiple funny bot names:
- **SneakyBot** 🕵️ - Custom test bot
- **ClaudeBot** - Anthropic's crawler
- **GPTBot** - OpenAI's crawler
- **PerplexityBot** - Perplexity's crawler
- **LazyBot** 😴 - Slow crawler
- **HungryBot** 🍕 - Hungry for content
- **CuriousBot** 🤔 - Always asking questions

**Modes:**
1. Quick Test (5 requests)
2. Full Crawl (All content)
3. Stress Test (All bots × all content)
4. Custom Bot Simulation

```bash
./testing/bot-crawler.sh
```

#### `verify-analytics.sh` - Analytics Verification
Checks that bot tracking is working correctly:
- Verifies database table exists
- Shows total visits count
- Lists bot types detected
- Displays request methods used
- Shows cache performance
- Calculates response time stats
- Lists top visited pages
- Shows recent visits

```bash
./testing/verify-analytics.sh
```

## 🚀 Quick Start

### Complete Test (Recommended)
Run everything in one command:
```bash
./testing/run-full-test.sh
```

### Manual Step-by-Step

1. **Start Docker containers:**
```bash
docker-compose up -d
```

2. **Setup WordPress:**
```bash
./testing/setup-wordpress.sh
```

3. **Run bot crawler:**
```bash
./testing/bot-crawler.sh
# Select option 3 for stress test
```

4. **Verify analytics:**
```bash
./testing/verify-analytics.sh
```

## 🌐 Access URLs

After running the setup:

| Service | URL | Credentials |
|---------|-----|-------------|
| WordPress | http://localhost:8080 | - |
| Admin Dashboard | http://localhost:8080/wp-admin | admin / admin123 |
| Bot Analytics | http://localhost:8080/wp-admin/admin.php?page=third-audience-bot-analytics | admin / admin123 |
| phpMyAdmin | http://localhost:8081 | wordpress / wordpress |

## 📊 Test Data

### Content Created
- **5 Posts**: "Test Post X - AI Bot Tracking Demo"
- **3 Pages**: "About Page X"
- All published and publicly accessible

### Bot User Agents
Each bot has a unique user agent pattern that gets detected by the analytics system:

```
SneakyBot/1.0         - Custom test bot
ClaudeBot/1.0         - Anthropic AI
GPTBot/1.0            - OpenAI
PerplexityBot/1.0     - Perplexity
LazyBot/1.0           - Slow crawler
HungryBot/1.0         - Content hungry
CuriousBot/1.0        - Question asking
```

### Request Methods Tested
1. **Direct .md URLs**: `http://localhost:8080/post-title.md`
2. **Accept Header**: `Accept: text/markdown`

## 🔍 What Gets Tracked

The bot analytics system captures:
- ✅ Bot type & name
- ✅ User agent string
- ✅ URL visited
- ✅ Post ID, type, and title
- ✅ Request method (md_url or accept_header)
- ✅ Cache status (HIT, MISS, PRE_GENERATED)
- ✅ Response time (milliseconds)
- ✅ Response size (bytes)
- ✅ IP address
- ✅ Referer
- ✅ Timestamp

## 🧹 Cleanup

### Stop containers (keep data):
```bash
docker-compose stop
```

### Remove containers (keep data):
```bash
docker-compose down
```

### Complete cleanup (removes ALL data):
```bash
docker-compose down -v
```

### Clean test reports:
```bash
rm testing/test-report-*.txt
```

## 🐛 Troubleshooting

### WordPress not accessible
```bash
# Check container status
docker ps

# Check WordPress logs
docker logs ta-wordpress

# Restart containers
docker-compose restart
```

### Plugin not activating
```bash
# Check plugin files
docker exec ta-wpcli wp plugin list

# Manually activate
docker exec -u 33:33 ta-wpcli wp plugin activate third-audience
```

### Database connection issues
```bash
# Check MySQL status
docker logs ta-mysql

# Verify connection
docker exec ta-mysql mysqladmin -u wordpress -pwordpress ping
```

### Analytics not tracking
```bash
# Verify table exists
docker exec ta-mysql mysql -u wordpress -pwordpress wordpress -e "SHOW TABLES LIKE '%analytics%'"

# Check for errors
docker exec -u 33:33 ta-wpcli wp option get ta_recent_errors --format=json
```

## 📝 Sample Bot Crawler Output

```
🕵️  SneakyBot - Third Audience Bot Crawler
==========================================

Select crawling mode:
  1) Quick Test (5 requests)
  2) Full Crawl (All content)
  3) Stress Test (Multiple bots, all content)
  4) Custom Bot Simulation

Enter choice [1-4]: 1

🚀 Running Quick Test...

   → SneakyBot requesting: http://localhost:8080/test-post-1.md
   ✓ Success (200) - Markdown returned
   → ClaudeBot requesting with Accept header: http://localhost:8080/test-post-2
   ✓ Success (200) - Content negotiation worked
   ...

✅ Quick test complete! Check analytics dashboard.
```

## 📈 Sample Analytics Verification Output

```
🔍 Third Audience - Analytics Verification
==========================================

1️⃣  Checking if analytics table exists...
   ✓ Analytics table exists

2️⃣  Checking recorded visits...
   ✓ Found 56 bot visits

3️⃣  Bot types detected:
   • SneakyBot: 16 visits
   • ClaudeBot: 12 visits
   • GPTBot: 10 visits
   • PerplexityBot: 8 visits
   ...

5️⃣  Cache performance:
   • HIT: 32 (57.1%)
   • MISS: 24 (42.9%)
   Overall cache hit rate: 57.1%

6️⃣  Response time statistics:
   • Average: 145.32ms
   • Minimum: 12ms
   • Maximum: 567ms
```

## 🎯 Testing Checklist

- [ ] Docker containers running
- [ ] WordPress accessible at :8080
- [ ] Plugin activated successfully
- [ ] Test content created (8 items)
- [ ] Bot crawler executed
- [ ] Analytics table populated
- [ ] Analytics dashboard accessible
- [ ] Charts rendering correctly
- [ ] Filters working
- [ ] CSV export functional
- [ ] Cache hit rate showing
- [ ] Response times captured

## 💡 Tips

1. **Run full test first** to ensure everything works
2. **Use stress test** to generate realistic analytics data
3. **Check phpMyAdmin** to inspect raw database records
4. **Monitor response times** to verify performance
5. **Test filters** in the analytics dashboard
6. **Export CSV** to verify data completeness
7. **Create custom bots** to test edge cases

## 🔗 Related Files

- `../docker-compose.yml` - Docker configuration
- `../docker/php.ini` - PHP settings
- `../third-audience/` - Plugin source code
- `../third-audience/includes/class-ta-bot-analytics.php` - Analytics engine
- `../third-audience/admin/views/bot-analytics-page.php` - Dashboard UI

## 📚 Additional Resources

- [WordPress CLI Documentation](https://developer.wordpress.org/cli/)
- [Docker Compose Docs](https://docs.docker.com/compose/)
- [Third Audience Plugin Docs](../docs/)

---

**Need help?** Open an issue or check the troubleshooting section above.
