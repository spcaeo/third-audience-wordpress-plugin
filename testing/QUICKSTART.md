# Quick Start Guide 🚀

## TL;DR - Run This One Command

```bash
cd /Users/rakesh/Desktop/Projects/third-audience-jeel
./testing/run-full-test.sh
```

That's it! This will:
1. Start Docker containers ✅
2. Install WordPress ✅
3. Activate the plugin ✅
4. Create test content ✅
5. Simulate bot visits ✅
6. Verify analytics ✅
7. Generate report ✅

## After Testing

### View Analytics Dashboard
```bash
open http://localhost:8080/wp-admin
# Login: admin / admin123
# Navigate to: Bot Analytics menu
```

### View Database (phpMyAdmin)
```bash
open http://localhost:8081
# Login: wordpress / wordpress
# Table: wp_ta_bot_analytics
```

## Test Individual Components

### Just Setup WordPress
```bash
./testing/setup-wordpress.sh
```

### Just Run Bot Crawler
```bash
./testing/bot-crawler.sh
# Choose option 3 for stress test
```

### Just Verify Analytics
```bash
./testing/verify-analytics.sh
```

## Custom Bot Testing

### Create Your Own Bot
Edit `bot-crawler.sh` and add to the BOTS array:
```bash
["YourBot"]="Mozilla/5.0 (compatible; YourBot/1.0; +https://yourbot.ai) 🤖"
```

Then run:
```bash
./testing/bot-crawler.sh
# Select option 4 (Custom Bot Simulation)
```

## Cleanup

### Stop everything (keep data)
```bash
docker-compose stop
```

### Remove everything (fresh start)
```bash
docker-compose down -v
rm -rf testing/test-report-*.txt
```

## Troubleshooting

### "Port 8080 already in use"
```bash
# Find what's using the port
lsof -i :8080

# Stop other containers
docker ps
docker stop <container-id>

# Or change the port in docker-compose.yml
```

### "Permission denied"
```bash
chmod +x testing/*.sh
```

### "WordPress not accessible"
```bash
# Wait a bit longer (60 seconds)
sleep 60

# Check logs
docker logs ta-wordpress
```

## What You'll See

### Analytics Dashboard Features:
- 📊 **Summary Cards**: Total visits, unique pages, cache hit rate, bandwidth
- 📈 **Line Chart**: Visits over time (hourly/daily/weekly/monthly)
- 🍩 **Doughnut Chart**: Bot distribution with percentages
- 📋 **Top Pages Table**: Most crawled content
- 🔍 **Filters**: Date range, bot type, post type, cache status
- 🔎 **Search**: Find specific URLs or bot visits
- 📤 **Export**: Download CSV of all data

### Sample Data You'll Get:
- SneakyBot 🕵️ (custom test bot)
- ClaudeBot (Anthropic)
- GPTBot (OpenAI)
- PerplexityBot
- LazyBot 😴
- HungryBot 🍕
- CuriousBot 🤔

## URLs Cheat Sheet

| What | URL |
|------|-----|
| WordPress | http://localhost:8080 |
| Admin | http://localhost:8080/wp-admin |
| **Bot Analytics** | http://localhost:8080/wp-admin/admin.php?page=third-audience-bot-analytics |
| Settings | http://localhost:8080/wp-admin/options-general.php?page=third-audience |
| phpMyAdmin | http://localhost:8081 |

## Credentials

| Service | Username | Password |
|---------|----------|----------|
| WordPress Admin | admin | admin123 |
| Database (phpMyAdmin) | wordpress | wordpress |
| MySQL Root | root | rootpassword |

## Next Steps After Testing

1. ✅ Verify all charts are rendering
2. ✅ Test filters (date range, bot type, etc.)
3. ✅ Export CSV and check data
4. ✅ Create more test content
5. ✅ Run different bot crawler modes
6. ✅ Check cache performance
7. ✅ Monitor response times

## Pro Tips 💡

1. **Run stress test** to generate realistic data:
   ```bash
   echo "3" | ./testing/bot-crawler.sh
   ```

2. **Check real-time database**:
   ```bash
   docker exec ta-mysql mysql -u wordpress -pwordpress wordpress -e "SELECT * FROM wp_ta_bot_analytics ORDER BY visit_timestamp DESC LIMIT 10"
   ```

3. **Monitor live logs**:
   ```bash
   docker logs -f ta-wordpress
   ```

4. **Quick restart**:
   ```bash
   docker-compose restart
   ```

5. **Access WP-CLI**:
   ```bash
   docker exec -u 33:33 ta-wpcli wp --info
   ```

## Questions?

Check the full README.md in this directory for detailed documentation.

---

**Happy Testing! 🎉**
