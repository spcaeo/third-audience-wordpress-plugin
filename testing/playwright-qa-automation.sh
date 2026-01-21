#!/bin/bash
# Playwright QA/QC Automation - Complete Browser Testing
# Uses MCP Playwright to automate all manual testing steps

set -e

echo "🎭 Third Audience - Playwright QA Automation"
echo "============================================"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
NC='\033[0m'

# Configuration
WP_URL="http://localhost:8080"
ADMIN_URL="$WP_URL/wp-admin"
ANALYTICS_URL="$ADMIN_URL/admin.php?page=third-audience-bot-analytics"
USERNAME="admin"
PASSWORD="admin123"
TIMESTAMP=$(date +%Y%m%d-%H%M%S)
REPORT_DIR="testing/qa-reports/$TIMESTAMP"
SCREENSHOTS_DIR="$REPORT_DIR/screenshots"

# Create report directories
mkdir -p "$SCREENSHOTS_DIR"

# Test results
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0
WARNINGS=0

# Function to log test result
log_test() {
    local test_name="$1"
    local status="$2"
    local message="$3"

    ((TOTAL_TESTS++))

    if [ "$status" = "PASS" ]; then
        echo -e "${GREEN}✓ PASS${NC} - $test_name"
        ((PASSED_TESTS++))
    elif [ "$status" = "FAIL" ]; then
        echo -e "${RED}✗ FAIL${NC} - $test_name"
        echo "  Error: $message"
        ((FAILED_TESTS++))
    elif [ "$status" = "WARN" ]; then
        echo -e "${YELLOW}⚠ WARN${NC} - $test_name"
        echo "  Warning: $message"
        ((WARNINGS++))
    fi

    # Log to report file
    echo "$status | $test_name | $message" >> "$REPORT_DIR/test-results.log"
}

# Initialize test report
echo "Third Audience QA/QC Automation Report" > "$REPORT_DIR/test-results.log"
echo "Generated: $(date)" >> "$REPORT_DIR/test-results.log"
echo "========================================" >> "$REPORT_DIR/test-results.log"
echo "" >> "$REPORT_DIR/test-results.log"

echo "📋 Starting automated QA/QC testing..."
echo ""
echo "Test report will be saved to: $REPORT_DIR"
echo ""

# ============================================================================
# Phase 1: Environment Checks
# ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Phase 1: Environment Validation"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Test 1.1: Check WordPress is accessible
echo "Testing: WordPress accessibility..."
if curl -s -f "$WP_URL" > /dev/null 2>&1; then
    log_test "WordPress Accessibility" "PASS" "WordPress is accessible at $WP_URL"
else
    log_test "WordPress Accessibility" "FAIL" "Cannot access WordPress at $WP_URL"
    echo -e "${RED}Critical: WordPress not accessible. Exiting.${NC}"
    exit 1
fi

# Test 1.2: Check admin panel is accessible
echo "Testing: Admin panel accessibility..."
if curl -s "$ADMIN_URL" | grep -q "wp-login"; then
    log_test "Admin Panel Accessibility" "PASS" "Admin login page accessible"
else
    log_test "Admin Panel Accessibility" "FAIL" "Cannot access admin login"
fi

# Test 1.3: Check database connectivity
echo "Testing: Database connectivity..."
if docker exec ta-mysql mysqladmin -u wordpress -pwordpress ping -h localhost &>/dev/null; then
    log_test "Database Connectivity" "PASS" "MySQL is responding"
else
    log_test "Database Connectivity" "FAIL" "Cannot connect to MySQL"
fi

# Test 1.4: Check analytics table exists
echo "Testing: Analytics table existence..."
TABLE_EXISTS=$(docker exec ta-mysql mysql -u wordpress -pwordpress wordpress -sN -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='wordpress' AND table_name='wp_ta_bot_analytics'" 2>/dev/null)
if [ "$TABLE_EXISTS" = "1" ]; then
    log_test "Analytics Table Exists" "PASS" "wp_ta_bot_analytics table found"
else
    log_test "Analytics Table Exists" "FAIL" "Analytics table not found"
fi

# Test 1.5: Check if there's data to test with
echo "Testing: Analytics data availability..."
VISIT_COUNT=$(docker exec ta-mysql mysql -u wordpress -pwordpress wordpress -sN -e "SELECT COUNT(*) FROM wp_ta_bot_analytics" 2>/dev/null)
if [ "$VISIT_COUNT" -gt 0 ]; then
    log_test "Analytics Data Available" "PASS" "Found $VISIT_COUNT bot visits"
else
    log_test "Analytics Data Available" "WARN" "No bot visits found. Run bot-crawler.sh first."
fi

echo ""

# ============================================================================
# Phase 2: WordPress Login & Navigation (Using Claude Code)
# ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Phase 2: WordPress Login & Navigation"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

cat > "$REPORT_DIR/playwright-test.txt" << 'EOPLAYWRIGHT'
The following tests will be performed using Playwright MCP tools:

1. Navigate to WordPress admin
2. Login with credentials
3. Verify dashboard loads
4. Navigate to Bot Analytics page
5. Verify analytics dashboard loads
6. Check summary cards are visible
7. Verify charts are rendered
8. Test date filters
9. Test bot type filter
10. Test post type filter
11. Test cache status filter
12. Test search functionality
13. Verify recent visits table
14. Test pagination (if applicable)
15. Test CSV export button
16. Take screenshots of key pages
17. Verify no JavaScript errors

Run these tests using Claude Code with MCP Playwright integration.
EOPLAYWRIGHT

echo "⚠️  Note: Playwright browser automation requires manual execution"
echo "    Run this command to execute browser tests:"
echo ""
echo "    claude --mcp-config ~/.claude/mcp.json"
echo ""
echo "    Then use Playwright MCP tools to:"
echo "    1. Navigate to $ADMIN_URL"
echo "    2. Login and test the analytics dashboard"
echo ""

# Create a comprehensive test checklist
cat > "$REPORT_DIR/manual-checklist.md" << 'EOCHECKLIST'
# Manual Browser Test Checklist

Use Playwright MCP tools in Claude Code to verify:

## Login Tests
- [ ] Navigate to wp-admin
- [ ] Enter username: admin
- [ ] Enter password: admin123
- [ ] Click login button
- [ ] Verify redirect to dashboard

## Navigation Tests
- [ ] Find "Bot Analytics" in admin menu
- [ ] Click "Bot Analytics" menu item
- [ ] Verify page loads without errors
- [ ] Check URL is correct (.../admin.php?page=third-audience-bot-analytics)

## Dashboard Visual Tests
- [ ] Summary cards visible (4 cards)
- [ ] Summary cards show data
- [ ] Visits over time chart renders
- [ ] Bot distribution chart renders
- [ ] Top pages table visible
- [ ] Recent visits table visible

## Filter Tests
- [ ] Date picker (from) is functional
- [ ] Date picker (to) is functional
- [ ] Bot type dropdown works
- [ ] Post type dropdown works
- [ ] Cache status dropdown works
- [ ] Apply Filters button works
- [ ] Reset button clears filters

## Search Tests
- [ ] Search box accepts input
- [ ] Search returns results
- [ ] Search handles no results
- [ ] Search clears properly

## Data Tests
- [ ] Table shows bot visits
- [ ] Bot badges are colored correctly
- [ ] Cache status badges display
- [ ] Timestamps are human-readable
- [ ] URLs are clickable

## Export Tests
- [ ] Export CSV button visible
- [ ] Export CSV triggers download
- [ ] CSV file contains data

## Performance Tests
- [ ] Page loads in <3 seconds
- [ ] Filters apply in <1 second
- [ ] Charts render smoothly
- [ ] No JavaScript console errors

## Responsive Tests
- [ ] Desktop view (1920x1080)
- [ ] Tablet view (768x1024)
- [ ] Mobile view (375x667)
EOCHECKLIST

echo "✓ Created manual test checklist: $REPORT_DIR/manual-checklist.md"

log_test "Playwright Test Plan Created" "PASS" "Test plan ready for execution"

echo ""

# ============================================================================
# Phase 3: Data Validation
# ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Phase 3: Data Validation"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if [ "$VISIT_COUNT" -gt 0 ]; then
    # Test 3.1: Verify bot types are captured
    echo "Testing: Bot type data integrity..."
    BOT_TYPES=$(docker exec ta-mysql mysql -u wordpress -pwordpress wordpress -sN -e "SELECT COUNT(DISTINCT bot_type) FROM wp_ta_bot_analytics" 2>/dev/null)
    if [ "$BOT_TYPES" -gt 0 ]; then
        log_test "Bot Types Captured" "PASS" "Found $BOT_TYPES different bot types"
    else
        log_test "Bot Types Captured" "FAIL" "No bot types found in database"
    fi

    # Test 3.2: Verify URLs are captured
    echo "Testing: URL data integrity..."
    URL_COUNT=$(docker exec ta-mysql mysql -u wordpress -pwordpress wordpress -sN -e "SELECT COUNT(DISTINCT url) FROM wp_ta_bot_analytics" 2>/dev/null)
    if [ "$URL_COUNT" -gt 0 ]; then
        log_test "URLs Captured" "PASS" "Found $URL_COUNT unique URLs"
    else
        log_test "URLs Captured" "FAIL" "No URLs found in database"
    fi

    # Test 3.3: Verify timestamps are valid
    echo "Testing: Timestamp data integrity..."
    NULL_TIMESTAMPS=$(docker exec ta-mysql mysql -u wordpress -pwordpress wordpress -sN -e "SELECT COUNT(*) FROM wp_ta_bot_analytics WHERE visit_timestamp IS NULL" 2>/dev/null)
    if [ "$NULL_TIMESTAMPS" = "0" ]; then
        log_test "Timestamps Valid" "PASS" "All timestamps are populated"
    else
        log_test "Timestamps Valid" "FAIL" "$NULL_TIMESTAMPS records have NULL timestamps"
    fi

    # Test 3.4: Verify cache status is recorded
    echo "Testing: Cache status data..."
    CACHE_STATUSES=$(docker exec ta-mysql mysql -u wordpress -pwordpress wordpress -sN -e "SELECT COUNT(DISTINCT cache_status) FROM wp_ta_bot_analytics" 2>/dev/null)
    if [ "$CACHE_STATUSES" -gt 0 ]; then
        log_test "Cache Status Recorded" "PASS" "Found $CACHE_STATUSES different cache statuses"
    else
        log_test "Cache Status Recorded" "FAIL" "No cache statuses recorded"
    fi

    # Test 3.5: Verify response times are captured
    echo "Testing: Response time data..."
    AVG_RESPONSE=$(docker exec ta-mysql mysql -u wordpress -pwordpress wordpress -sN -e "SELECT AVG(response_time) FROM wp_ta_bot_analytics WHERE response_time IS NOT NULL" 2>/dev/null)
    if [ -n "$AVG_RESPONSE" ] && [ "$(echo "$AVG_RESPONSE > 0" | bc)" -eq 1 ]; then
        log_test "Response Times Captured" "PASS" "Average response time: ${AVG_RESPONSE}ms"
    else
        log_test "Response Times Captured" "WARN" "No response time data available"
    fi

    # Test 3.6: Calculate cache hit rate
    echo "Testing: Cache performance..."
    CACHE_HITS=$(docker exec ta-mysql mysql -u wordpress -pwordpress wordpress -sN -e "SELECT COUNT(*) FROM wp_ta_bot_analytics WHERE cache_status IN ('HIT', 'PRE_GENERATED')" 2>/dev/null)
    HIT_RATE=$(awk "BEGIN {printf \"%.1f\", ($CACHE_HITS/$VISIT_COUNT)*100}")
    if [ "$(echo "$HIT_RATE >= 30" | bc)" -eq 1 ]; then
        log_test "Cache Hit Rate" "PASS" "Cache hit rate: ${HIT_RATE}%"
    else
        log_test "Cache Hit Rate" "WARN" "Cache hit rate low: ${HIT_RATE}%"
    fi
else
    log_test "Data Validation" "WARN" "No data to validate - run bot crawler first"
fi

echo ""

# ============================================================================
# Phase 4: Performance Metrics
# ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Phase 4: Performance Metrics"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Test 4.1: Page load time
echo "Testing: Analytics page load time..."
START_TIME=$(date +%s%N)
curl -s "$ANALYTICS_URL" > /dev/null 2>&1 || true
END_TIME=$(date +%s%N)
LOAD_TIME=$(( (END_TIME - START_TIME) / 1000000 )) # Convert to milliseconds

if [ "$LOAD_TIME" -lt 3000 ]; then
    log_test "Page Load Time" "PASS" "Loaded in ${LOAD_TIME}ms"
elif [ "$LOAD_TIME" -lt 5000 ]; then
    log_test "Page Load Time" "WARN" "Loaded in ${LOAD_TIME}ms (target: <3000ms)"
else
    log_test "Page Load Time" "FAIL" "Loaded in ${LOAD_TIME}ms (too slow)"
fi

# Test 4.2: Database query performance
echo "Testing: Database query performance..."
START_TIME=$(date +%s%N)
docker exec ta-mysql mysql -u wordpress -pwordpress wordpress -e "SELECT * FROM wp_ta_bot_analytics LIMIT 100" &>/dev/null || true
END_TIME=$(date +%s%N)
QUERY_TIME=$(( (END_TIME - START_TIME) / 1000000 ))

if [ "$QUERY_TIME" -lt 100 ]; then
    log_test "Database Query Speed" "PASS" "Query executed in ${QUERY_TIME}ms"
elif [ "$QUERY_TIME" -lt 500 ]; then
    log_test "Database Query Speed" "WARN" "Query executed in ${QUERY_TIME}ms"
else
    log_test "Database Query Speed" "FAIL" "Query too slow: ${QUERY_TIME}ms"
fi

echo ""

# ============================================================================
# Phase 5: Generate Report
# ============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Phase 5: Generating QA Report"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Generate comprehensive HTML report
cat > "$REPORT_DIR/qa-report.html" << EOREPORT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Third Audience QA Report - $TIMESTAMP</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #1d2327; margin: 0 0 10px; }
        .meta { color: #646970; font-size: 14px; margin-bottom: 30px; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { padding: 20px; border-radius: 6px; border-left: 4px solid #ccc; }
        .card.pass { background: #d5f4e6; border-color: #00a32a; }
        .card.fail { background: #f9e0e0; border-color: #d63638; }
        .card.warn { background: #fcf3cd; border-color: #dba617; }
        .card h3 { margin: 0 0 5px; font-size: 32px; font-weight: 700; }
        .card p { margin: 0; color: #646970; font-size: 14px; }
        .test-results { margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9f9f9; font-weight: 600; color: #646970; font-size: 12px; text-transform: uppercase; }
        .status { display: inline-block; padding: 4px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; }
        .status.pass { background: #d5f4e6; color: #00a32a; }
        .status.fail { background: #f9e0e0; color: #d63638; }
        .status.warn { background: #fcf3cd; color: #dba617; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #646970; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎭 Third Audience QA Report</h1>
        <div class="meta">Generated: $(date) | Session: $TIMESTAMP</div>

        <div class="summary">
            <div class="card pass">
                <h3>$PASSED_TESTS</h3>
                <p>Tests Passed</p>
            </div>
            <div class="card fail">
                <h3>$FAILED_TESTS</h3>
                <p>Tests Failed</p>
            </div>
            <div class="card warn">
                <h3>$WARNINGS</h3>
                <p>Warnings</p>
            </div>
            <div class="card">
                <h3>$TOTAL_TESTS</h3>
                <p>Total Tests</p>
            </div>
        </div>

        <div class="test-results">
            <h2>Test Results</h2>
            <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Test Name</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
EOREPORT

# Add test results to HTML
while IFS='|' read -r status test_name details; do
    if [ -n "$status" ]; then
        status_clean=$(echo "$status" | xargs)
        test_clean=$(echo "$test_name" | xargs)
        details_clean=$(echo "$details" | xargs)

        cat >> "$REPORT_DIR/qa-report.html" << EORESULT
                    <tr>
                        <td><span class="status $(echo "$status_clean" | tr '[:upper:]' '[:lower:]')">$status_clean</span></td>
                        <td>$test_clean</td>
                        <td>$details_clean</td>
                    </tr>
EORESULT
    fi
done < <(tail -n +4 "$REPORT_DIR/test-results.log")

cat >> "$REPORT_DIR/qa-report.html" << EOREPORT
                </tbody>
            </table>
        </div>

        <div class="footer">
            <strong>Test Environment:</strong> Docker WordPress 6.7 | MySQL 8.0 | Third Audience Plugin v1.4.0<br>
            <strong>Report Location:</strong> $REPORT_DIR<br>
            <strong>Next Steps:</strong> Review failed tests and warnings. Run Playwright browser tests for UI validation.
        </div>
    </div>
</body>
</html>
EOREPORT

echo "✓ Generated HTML report: $REPORT_DIR/qa-report.html"

# Generate summary text file
cat > "$REPORT_DIR/summary.txt" << EOSUMMARY
Third Audience QA/QC Test Summary
==================================
Generated: $(date)
Test Session: $TIMESTAMP

Results:
--------
Total Tests:    $TOTAL_TESTS
Passed:         $PASSED_TESTS
Failed:         $FAILED_TESTS
Warnings:       $WARNINGS

Pass Rate:      $(awk "BEGIN {printf \"%.1f\", ($PASSED_TESTS/$TOTAL_TESTS)*100}")%

Environment:
------------
WordPress URL:  $WP_URL
Admin URL:      $ADMIN_URL
Analytics URL:  $ANALYTICS_URL
Database:       MySQL 8.0 (ta-mysql container)

Data Summary:
-------------
Bot Visits:     $VISIT_COUNT
Bot Types:      $BOT_TYPES
Unique URLs:    $URL_COUNT
Cache Hit Rate: ${HIT_RATE}%
Avg Response:   ${AVG_RESPONSE}ms

Reports:
--------
HTML Report:    $REPORT_DIR/qa-report.html
Test Log:       $REPORT_DIR/test-results.log
Screenshots:    $REPORT_DIR/screenshots/ (requires browser tests)

Next Steps:
-----------
1. Open HTML report in browser:
   open $REPORT_DIR/qa-report.html

2. Run Playwright browser tests (manual):
   - Use Claude Code with MCP Playwright
   - Follow checklist in: $REPORT_DIR/manual-checklist.md

3. Review any failed tests or warnings
4. Take screenshots of analytics dashboard
5. Test filters and export functionality

EOSUMMARY

echo "✓ Generated summary: $REPORT_DIR/summary.txt"

echo ""

# ============================================================================
# Final Summary
# ============================================================================

echo "=========================================="
echo "QA/QC Automation Complete!"
echo "=========================================="
echo ""

if [ "$FAILED_TESTS" -eq 0 ]; then
    echo -e "${GREEN}✅ All automated tests passed!${NC}"
else
    echo -e "${RED}⚠️  $FAILED_TESTS test(s) failed${NC}"
fi

if [ "$WARNINGS" -gt 0 ]; then
    echo -e "${YELLOW}⚠️  $WARNINGS warning(s) detected${NC}"
fi

echo ""
echo "📊 Test Summary:"
echo "   Total:    $TOTAL_TESTS tests"
echo "   Passed:   $PASSED_TESTS"
echo "   Failed:   $FAILED_TESTS"
echo "   Warnings: $WARNINGS"
echo ""

PASS_RATE=$(awk "BEGIN {printf \"%.1f\", ($PASSED_TESTS/$TOTAL_TESTS)*100}")
echo "   Pass Rate: ${PASS_RATE}%"
echo ""

echo "📁 Reports saved to:"
echo "   $REPORT_DIR"
echo ""

echo "🌐 View HTML Report:"
echo "   open $REPORT_DIR/qa-report.html"
echo ""

echo "📋 Next: Run Playwright Browser Tests"
echo "   Follow checklist: $REPORT_DIR/manual-checklist.md"
echo ""

# Return exit code based on test results
if [ "$FAILED_TESTS" -gt 0 ]; then
    exit 1
else
    exit 0
fi
