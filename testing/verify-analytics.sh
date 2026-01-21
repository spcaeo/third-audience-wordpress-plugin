#!/bin/bash
# Analytics Verification Script
# Verifies that bot visits are being tracked correctly

set -e

echo "🔍 Third Audience - Analytics Verification"
echo "=========================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Function to run WP-CLI commands
wp_cli() {
    docker exec -u 33:33 ta-wpcli wp "$@"
}

# Function to query analytics database
query_analytics() {
    local query="$1"
    docker exec ta-mysql mysql -u wordpress -pwordpress wordpress -sN -e "$query" 2>/dev/null
}

echo "📊 Checking Analytics Data..."
echo ""

# Check if analytics table exists
echo "1️⃣  Checking if analytics table exists..."
table_count=$(query_analytics "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='wordpress' AND table_name='wp_ta_bot_analytics'")

if [ "$table_count" = "1" ]; then
    echo -e "${GREEN}   ✓ Analytics table exists${NC}"
else
    echo -e "${RED}   ✗ Analytics table not found${NC}"
    echo "   Run setup-wordpress.sh first"
    exit 1
fi

# Check total visits
echo ""
echo "2️⃣  Checking recorded visits..."
total_visits=$(query_analytics "SELECT COUNT(*) FROM wp_ta_bot_analytics")

if [ "$total_visits" -gt 0 ]; then
    echo -e "${GREEN}   ✓ Found $total_visits bot visits${NC}"
else
    echo -e "${YELLOW}   ⚠ No bot visits recorded yet${NC}"
    echo "   Run bot-crawler.sh to generate test data"
    exit 0
fi

# Check bot types
echo ""
echo "3️⃣  Bot types detected:"
query_analytics "SELECT bot_name, COUNT(*) as visits FROM wp_ta_bot_analytics GROUP BY bot_name ORDER BY visits DESC" | while read line; do
    bot_name=$(echo "$line" | awk '{print $1}')
    count=$(echo "$line" | awk '{print $2}')
    echo -e "   ${BLUE}• $bot_name:${NC} $count visits"
done

# Check request methods
echo ""
echo "4️⃣  Request methods used:"
query_analytics "SELECT request_method, COUNT(*) as count FROM wp_ta_bot_analytics GROUP BY request_method" | while read line; do
    method=$(echo "$line" | awk '{print $1}')
    count=$(echo "$line" | awk '{print $2}')
    echo -e "   ${BLUE}• $method:${NC} $count requests"
done

# Check cache performance
echo ""
echo "5️⃣  Cache performance:"
query_analytics "SELECT cache_status, COUNT(*) as count FROM wp_ta_bot_analytics GROUP BY cache_status" | while read line; do
    status=$(echo "$line" | awk '{print $1}')
    count=$(echo "$line" | awk '{print $2}')

    percentage=$(awk "BEGIN {printf \"%.1f\", ($count/$total_visits)*100}")

    if [ "$status" = "HIT" ] || [ "$status" = "PRE_GENERATED" ]; then
        echo -e "   ${GREEN}• $status:${NC} $count ($percentage%)"
    else
        echo -e "   ${YELLOW}• $status:${NC} $count ($percentage%)"
    fi
done

# Calculate overall cache hit rate
cache_hits=$(query_analytics "SELECT COUNT(*) FROM wp_ta_bot_analytics WHERE cache_status IN ('HIT', 'PRE_GENERATED')")
hit_rate=$(awk "BEGIN {printf \"%.1f\", ($cache_hits/$total_visits)*100}")
echo -e "   ${BLUE}Overall cache hit rate:${NC} ${hit_rate}%"

# Check response times
echo ""
echo "6️⃣  Response time statistics:"
avg_response=$(query_analytics "SELECT AVG(response_time) FROM wp_ta_bot_analytics WHERE response_time IS NOT NULL")
min_response=$(query_analytics "SELECT MIN(response_time) FROM wp_ta_bot_analytics WHERE response_time IS NOT NULL")
max_response=$(query_analytics "SELECT MAX(response_time) FROM wp_ta_bot_analytics WHERE response_time IS NOT NULL")

if [ -n "$avg_response" ]; then
    echo -e "   ${BLUE}• Average:${NC} ${avg_response}ms"
    echo -e "   ${BLUE}• Minimum:${NC} ${min_response}ms"
    echo -e "   ${BLUE}• Maximum:${NC} ${max_response}ms"
else
    echo -e "   ${YELLOW}⚠ No response time data available${NC}"
fi

# Check most visited pages
echo ""
echo "7️⃣  Top 5 visited pages:"
query_analytics "SELECT post_title, COUNT(*) as visits FROM wp_ta_bot_analytics WHERE post_title IS NOT NULL GROUP BY post_title ORDER BY visits DESC LIMIT 5" | while read line; do
    title=$(echo "$line" | awk '{$NF=""; print $0}' | sed 's/ $//')
    count=$(echo "$line" | awk '{print $NF}')
    echo -e "   ${BLUE}• $title:${NC} $count visits"
done

# Check recent visits
echo ""
echo "8️⃣  Recent visits (last 5):"
query_analytics "SELECT bot_name, post_title, visit_timestamp FROM wp_ta_bot_analytics ORDER BY visit_timestamp DESC LIMIT 5" | while read line; do
    bot=$(echo "$line" | awk '{print $1}')
    title=$(echo "$line" | awk '{$1=""; $(NF-1)=""; $NF=""; print $0}' | sed 's/^  *//' | sed 's/  *$//')
    time=$(echo "$line" | awk '{print $(NF-1), $NF}')
    echo -e "   ${BLUE}$bot${NC} → $title (${time})"
done

# Summary
echo ""
echo "=========================================="
echo -e "${GREEN}✅ Verification Complete!${NC}"
echo "=========================================="
echo ""

if [ "$total_visits" -gt 0 ]; then
    echo "📈 Analytics Summary:"
    echo "   • Total Visits: $total_visits"
    echo "   • Cache Hit Rate: ${hit_rate}%"
    echo "   • Avg Response Time: ${avg_response}ms"
    echo ""
    echo "🌐 View full dashboard:"
    echo "   http://localhost:8080/wp-admin/admin.php?page=third-audience-bot-analytics"
else
    echo "💡 Next Steps:"
    echo "   Run: ./testing/bot-crawler.sh"
    echo "   Then run this script again"
fi
echo ""
