#!/bin/bash
# Master Test Script - Complete End-to-End Testing
# This orchestrates the entire testing process

set -e

echo "🎯 Third Audience - Full Test Suite"
echo "===================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
NC='\033[0m'

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

cd "$PROJECT_DIR"

echo "📋 Test Plan:"
echo "   1. Start Docker containers"
echo "   2. Setup WordPress & install plugin"
echo "   3. Create test content"
echo "   4. Run bot crawler simulation"
echo "   5. Verify analytics tracking"
echo "   6. Generate test report"
echo ""

read -p "Press Enter to start testing or Ctrl+C to cancel..."
echo ""

# Step 1: Start Docker
echo "=========================================="
echo "Step 1: Starting Docker Containers"
echo "=========================================="
echo ""

if docker ps | grep -q ta-wordpress; then
    echo -e "${YELLOW}⚠ Containers already running${NC}"
else
    echo "Starting containers..."
    docker-compose up -d
    echo "Waiting for services to be ready..."
    sleep 15
fi

echo -e "${GREEN}✓ Docker containers ready${NC}"
echo ""

# Step 2: Setup WordPress
echo "=========================================="
echo "Step 2: WordPress Setup"
echo "=========================================="
echo ""

bash "$SCRIPT_DIR/setup-wordpress.sh"

echo ""

# Step 3: Run Bot Crawler
echo "=========================================="
echo "Step 3: Bot Crawler Simulation"
echo "=========================================="
echo ""

echo "Running full crawl with multiple bots..."
sleep 2

# Run crawler with preset option (stress test)
echo "3" | bash "$SCRIPT_DIR/bot-crawler.sh"

echo ""

# Step 4: Verify Analytics
echo "=========================================="
echo "Step 4: Analytics Verification"
echo "=========================================="
echo ""

sleep 3
bash "$SCRIPT_DIR/verify-analytics.sh"

# Step 5: Generate Report
echo ""
echo "=========================================="
echo "Step 5: Test Report"
echo "=========================================="
echo ""

REPORT_FILE="$SCRIPT_DIR/test-report-$(date +%Y%m%d-%H%M%S).txt"

{
    echo "Third Audience - Test Report"
    echo "Generated: $(date)"
    echo ""
    echo "=========================================="
    echo ""

    # Docker status
    echo "Docker Containers:"
    docker ps --filter "name=ta-" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
    echo ""

    # WordPress info
    echo "WordPress Status:"
    docker exec -u 33:33 ta-wpcli wp core version
    docker exec -u 33:33 ta-wpcli wp plugin list --status=active
    echo ""

    # Analytics summary
    echo "Analytics Summary:"
    docker exec ta-mysql mysql -u wordpress -pwordpress wordpress -sN -e "
        SELECT
            COUNT(*) as total_visits,
            COUNT(DISTINCT bot_type) as unique_bots,
            COUNT(DISTINCT post_id) as unique_pages,
            ROUND(AVG(response_time), 2) as avg_response_time,
            ROUND((SUM(CASE WHEN cache_status IN ('HIT', 'PRE_GENERATED') THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as cache_hit_rate
        FROM wp_ta_bot_analytics
    " 2>/dev/null

    echo ""
    echo "Bot Distribution:"
    docker exec ta-mysql mysql -u wordpress -pwordpress wordpress -sN -e "
        SELECT bot_name, COUNT(*) as visits
        FROM wp_ta_bot_analytics
        GROUP BY bot_name
        ORDER BY visits DESC
    " 2>/dev/null

} | tee "$REPORT_FILE"

echo ""
echo "=========================================="
echo -e "${GREEN}✅ Full Test Suite Complete!${NC}"
echo "=========================================="
echo ""

echo "📊 Access Points:"
echo "   • WordPress:     http://localhost:8080"
echo "   • Admin:         http://localhost:8080/wp-admin"
echo "   • Analytics:     http://localhost:8080/wp-admin/admin.php?page=third-audience-bot-analytics"
echo "   • phpMyAdmin:    http://localhost:8081"
echo ""

echo "📄 Test report saved to:"
echo "   $REPORT_FILE"
echo ""

echo "🎉 Testing completed successfully!"
echo ""
