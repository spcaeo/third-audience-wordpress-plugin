#!/bin/bash

# Test Rate Limiting for Third Audience Plugin
# This script tests the rate limiting functionality by simulating bot requests

echo "========================================="
echo "Third Audience Rate Limiting Test"
echo "========================================="
echo ""

# Configuration
SITE_URL="http://localhost:8080"  # Update this to your WordPress site URL
MD_PATH="sample-post.md"           # Update this to an existing .md path
USER_AGENT="ClaudeBot/1.0"        # Simulating ClaudeBot (high priority, unlimited by default)

echo "Configuration:"
echo "  Site URL: $SITE_URL"
echo "  Test Path: $MD_PATH"
echo "  User Agent: $USER_AGENT"
echo ""

# Test 1: Verify rate limit headers are present
echo "Test 1: Checking rate limit headers..."
echo "----------------------------------------"
RESPONSE=$(curl -s -I "$SITE_URL/$MD_PATH" -H "User-Agent: $USER_AGENT")
echo "$RESPONSE" | grep -i "x-ratelimit"
echo ""

# Test 2: Test with low priority bot (should have limits)
LOW_PRIORITY_BOT="TestBot/1.0"
echo "Test 2: Testing with low priority bot ($LOW_PRIORITY_BOT)..."
echo "----------------------------------------"
echo "Making 12 requests (limit is 10/min for low priority)..."
echo ""

for i in {1..12}; do
    echo -n "Request $i: "
    RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" "$SITE_URL/$MD_PATH" -H "User-Agent: $LOW_PRIORITY_BOT")

    if [ "$RESPONSE" == "200" ]; then
        echo "✓ Success (200 OK)"
    elif [ "$RESPONSE" == "429" ]; then
        echo "✗ Rate Limited (429 Too Many Requests)"
    else
        echo "? Unexpected response ($RESPONSE)"
    fi

    # Small delay between requests
    sleep 0.1
done

echo ""
echo "Test 3: Verify 429 response includes retry headers..."
echo "----------------------------------------"
RATE_LIMITED_RESPONSE=$(curl -s -I "$SITE_URL/$MD_PATH" -H "User-Agent: $LOW_PRIORITY_BOT")
echo "$RATE_LIMITED_RESPONSE" | grep -E "^(HTTP|Retry-After|X-RateLimit)"
echo ""

# Test 4: Test with medium priority bot
MEDIUM_PRIORITY_BOT="Bytespider/1.0"
echo "Test 4: Testing medium priority bot ($MEDIUM_PRIORITY_BOT)..."
echo "----------------------------------------"
echo "Making 5 requests (should succeed, limit is 60/min)..."
echo ""

for i in {1..5}; do
    echo -n "Request $i: "
    RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" "$SITE_URL/$MD_PATH" -H "User-Agent: $MEDIUM_PRIORITY_BOT")

    if [ "$RESPONSE" == "200" ]; then
        echo "✓ Success (200 OK)"
    elif [ "$RESPONSE" == "429" ]; then
        echo "✗ Rate Limited (429 Too Many Requests)"
    else
        echo "? Unexpected response ($RESPONSE)"
    fi

    sleep 0.1
done

echo ""
echo "========================================="
echo "Test Summary"
echo "========================================="
echo "1. Rate limit headers should be present in responses"
echo "2. Low priority bots should be limited at 10 requests/minute"
echo "3. 429 responses should include Retry-After and X-RateLimit-* headers"
echo "4. Medium priority bots should allow 60 requests/minute"
echo ""
echo "To view violations:"
echo "  - Go to Bot Analytics page in WordPress admin"
echo "  - Scroll to 'Rate Limit Violations' section"
echo ""
echo "To configure rate limits:"
echo "  - Go to Bot Management page"
echo "  - Scroll to 'Rate Limits' section"
echo "  - Adjust limits per priority level"
echo ""
