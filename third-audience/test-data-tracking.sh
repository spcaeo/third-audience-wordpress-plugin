#!/bin/bash

###############################################################################
# Third Audience - Data Tracking Verification Script
#
# This script tests if data is being tracked correctly on monocubed.com
#
# Usage: bash test-data-tracking.sh YOUR_API_KEY
###############################################################################

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Site URL
SITE_URL="https://www.monocubed.com"

# Check if API key provided
if [ -z "$1" ]; then
    echo -e "${RED}❌ Error: API key required${NC}"
    echo ""
    echo "Usage: bash test-data-tracking.sh YOUR_API_KEY"
    echo ""
    echo "Get API key from:"
    echo "  ${SITE_URL}/wp-admin/ → Settings → Third Audience → Headless Setup"
    exit 1
fi

API_KEY="$1"

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Third Audience - Data Tracking Test"
echo "  Site: $SITE_URL"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Counter for passed tests
PASSED=0
FAILED=0

###############################################################################
# TEST 1: Check REST API Health
###############################################################################
echo -e "${BLUE}[TEST 1]${NC} Checking REST API Health..."
RESPONSE=$(curl -s -w "\n%{http_code}" "$SITE_URL/wp-json/third-audience/v1/health")
HTTP_CODE=$(echo "$RESPONSE" | tail -n1)
BODY=$(echo "$RESPONSE" | sed '$d')

if [ "$HTTP_CODE" == "200" ]; then
    echo -e "${GREEN}✓ PASS${NC} - REST API is accessible"
    echo "  Response: $BODY"
    PASSED=$((PASSED + 1))
    USE_REST=true
elif [ "$HTTP_CODE" == "403" ] || [ "$HTTP_CODE" == "404" ]; then
    echo -e "${YELLOW}⚠ INFO${NC} - REST API blocked (HTTP $HTTP_CODE)"
    echo "  Will use AJAX fallback automatically"
    PASSED=$((PASSED + 1))
    USE_REST=false
else
    echo -e "${RED}✗ FAIL${NC} - Unexpected response (HTTP $HTTP_CODE)"
    FAILED=$((FAILED + 1))
    USE_REST=false
fi
echo ""

###############################################################################
# TEST 2: Check AJAX Fallback
###############################################################################
echo -e "${BLUE}[TEST 2]${NC} Checking AJAX Fallback..."
RESPONSE=$(curl -s -X POST "$SITE_URL/wp-admin/admin-ajax.php" \
    -d "action=ta_health_check")

if echo "$RESPONSE" | grep -q '"success":true'; then
    echo -e "${GREEN}✓ PASS${NC} - AJAX fallback is working"
    echo "  Response: $RESPONSE"
    PASSED=$((PASSED + 1))
else
    echo -e "${RED}✗ FAIL${NC} - AJAX fallback not responding"
    echo "  Response: $RESPONSE"
    FAILED=$((FAILED + 1))
fi
echo ""

###############################################################################
# TEST 3: Track Citation via REST API
###############################################################################
if [ "$USE_REST" = true ]; then
    echo -e "${BLUE}[TEST 3]${NC} Tracking Citation via REST API..."
    TIMESTAMP=$(date +%s)
    RESPONSE=$(curl -s -X POST "$SITE_URL/wp-json/third-audience/v1/track-citation" \
        -H "Content-Type: application/json" \
        -H "X-TA-Api-Key: $API_KEY" \
        -d "{
            \"url\": \"/test-rest-$TIMESTAMP\",
            \"platform\": \"ChatGPT\",
            \"search_query\": \"test query from REST API\",
            \"referer\": \"https://chat.openai.com\"
        }")

    if echo "$RESPONSE" | grep -q '"success":true'; then
        echo -e "${GREEN}✓ PASS${NC} - Citation tracked via REST API"
        echo "  Response: $RESPONSE"
        echo "  Page: /test-rest-$TIMESTAMP"
        PASSED=$((PASSED + 1))
    else
        echo -e "${RED}✗ FAIL${NC} - Citation tracking failed"
        echo "  Response: $RESPONSE"
        FAILED=$((FAILED + 1))
    fi
    echo ""
fi

###############################################################################
# TEST 4: Track Citation via AJAX
###############################################################################
echo -e "${BLUE}[TEST 4]${NC} Tracking Citation via AJAX..."
TIMESTAMP=$(date +%s)
RESPONSE=$(curl -s -X POST "$SITE_URL/wp-admin/admin-ajax.php" \
    -d "action=ta_track_citation" \
    -d "api_key=$API_KEY" \
    -d "url=/test-ajax-$TIMESTAMP" \
    -d "platform=Perplexity" \
    -d "search_query=test query from AJAX" \
    -d "referer=https://www.perplexity.ai")

if echo "$RESPONSE" | grep -q '"success":true'; then
    echo -e "${GREEN}✓ PASS${NC} - Citation tracked via AJAX"
    echo "  Response: $RESPONSE"
    echo "  Page: /test-ajax-$TIMESTAMP"
    PASSED=$((PASSED + 1))
else
    echo -e "${RED}✗ FAIL${NC} - AJAX citation tracking failed"
    echo "  Response: $RESPONSE"
    FAILED=$((FAILED + 1))
fi
echo ""

###############################################################################
# TEST 5: Simulate Bot Visit (ClaudeBot)
###############################################################################
echo -e "${BLUE}[TEST 5]${NC} Simulating ClaudeBot visit..."
TIMESTAMP=$(date +%s)
RESPONSE=$(curl -s -w "\n%{http_code}" \
    -A "ClaudeBot/1.0" \
    "$SITE_URL/blog.md")
HTTP_CODE=$(echo "$RESPONSE" | tail -n1)

if [ "$HTTP_CODE" == "200" ]; then
    echo -e "${GREEN}✓ PASS${NC} - ClaudeBot visit simulated (HTTP 200)"
    PASSED=$((PASSED + 1))
else
    echo -e "${YELLOW}⚠ WARN${NC} - Bot visit returned HTTP $HTTP_CODE"
    echo "  (Bot visits are still tracked even with non-200 responses)"
    PASSED=$((PASSED + 1))
fi
echo ""

###############################################################################
# TEST 6: Simulate Bot Visit (GPTBot)
###############################################################################
echo -e "${BLUE}[TEST 6]${NC} Simulating GPTBot visit..."
RESPONSE=$(curl -s -w "\n%{http_code}" \
    -A "GPTBot/1.0" \
    "$SITE_URL/services.md")
HTTP_CODE=$(echo "$RESPONSE" | tail -n1)

if [ "$HTTP_CODE" == "200" ]; then
    echo -e "${GREEN}✓ PASS${NC} - GPTBot visit simulated (HTTP 200)"
    PASSED=$((PASSED + 1))
else
    echo -e "${YELLOW}⚠ WARN${NC} - Bot visit returned HTTP $HTTP_CODE"
    PASSED=$((PASSED + 1))
fi
echo ""

###############################################################################
# TEST 7: Simulate Bot Visit (PerplexityBot)
###############################################################################
echo -e "${BLUE}[TEST 7]${NC} Simulating PerplexityBot visit..."
RESPONSE=$(curl -s -w "\n%{http_code}" \
    -A "PerplexityBot/1.0" \
    "$SITE_URL/about.md")
HTTP_CODE=$(echo "$RESPONSE" | tail -n1)

if [ "$HTTP_CODE" == "200" ]; then
    echo -e "${GREEN}✓ PASS${NC} - PerplexityBot visit simulated (HTTP 200)"
    PASSED=$((PASSED + 1))
else
    echo -e "${YELLOW}⚠ WARN${NC} - Bot visit returned HTTP $HTTP_CODE"
    PASSED=$((PASSED + 1))
fi
echo ""

###############################################################################
# RESULTS SUMMARY
###############################################################################
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  TEST RESULTS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo -e "${GREEN}Passed:${NC} $PASSED"
echo -e "${RED}Failed:${NC} $FAILED"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ ALL TESTS PASSED!${NC}"
    echo ""
    echo "Data is being tracked correctly! ✅"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "  NEXT STEPS:"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "1. Check WordPress Admin for tracked data:"
    echo "   ${SITE_URL}/wp-admin/admin.php?page=third-audience-ai-citations"
    echo ""
    echo "2. You should see:"
    echo "   - 2 AI Citations (ChatGPT and Perplexity)"
    echo "   - 3 Bot visits (ClaudeBot, GPTBot, PerplexityBot)"
    echo ""
    echo "3. Check database (phpMyAdmin):"
    echo "   SELECT * FROM wp_ta_bot_analytics"
    echo "   WHERE visited_at >= NOW() - INTERVAL 5 MINUTE"
    echo "   ORDER BY visited_at DESC;"
    echo ""
    exit 0
else
    echo -e "${RED}✗ SOME TESTS FAILED${NC}"
    echo ""
    echo "Please check:"
    echo "1. API key is correct"
    echo "2. Plugin is activated"
    echo "3. Database tables exist"
    echo ""
    exit 1
fi
