#!/bin/bash
# Test bot script to verify Third Audience functionality

# Test 1: Simulate FunkyBot visiting a page
echo "=== Test 1: FunkyBot requesting .md ==="
echo "Bot User-Agent: FunkyBot/1.0"
response=$(curl -A "FunkyBot/1.0 (AI Researcher; +http://funkybot.example.com)" \
  -s -w "\nHTTP Status: %{http_code}\nContent-Type: %{content_type}\n" \
  "http://localhost:8080/hello-world.md")

echo "$response" | head -20
echo ""

# Check if markdown was served
if echo "$response" | grep -q "^---"; then
  echo "✓ Markdown served successfully"
else
  echo "✗ Markdown NOT served"
fi

echo ""
echo "=== Test 2: Regular browser requesting .md ==="
echo "User-Agent: Mozilla/5.0 (Regular Browser)"
response=$(curl -A "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36" \
  -s -w "\nHTTP Status: %{http_code}\nContent-Type: %{content_type}\n" \
  "http://localhost:8080/hello-world.md")

echo "$response" | head -20
echo ""

# Check if markdown was served
if echo "$response" | grep -q "^---"; then
  echo "✓ Markdown served to browser"
else
  echo "✗ Markdown NOT served to browser (expected)"
fi

echo ""
echo "=== Checking Database for Bot Visits ==="
docker exec ta-mysql mysql -u wordpress -pwordpress -D wordpress -e \
  "SELECT id, bot_name, LEFT(user_agent, 30) as user_agent, url, cache_status, created_at FROM wp_ta_bot_analytics ORDER BY id DESC LIMIT 3;" 2>&1 | grep -v "Warning"
