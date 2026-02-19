#!/bin/bash
# Quick verification script for v3.5.0 fixes
# Run with: bash verify-fixes.sh

echo "Checking PHP files for v3.5.0 code..."

# Check if client_user_agent tracking exists
if grep -q "client_user_agent" third-audience/includes/Analytics/class-ta-visit-tracker.php; then
    echo "✓ client_user_agent tracking code found"
else
    echo "✗ client_user_agent tracking code NOT found"
fi

# Check if get_http_status exists
if grep -q "get_http_status" third-audience/includes/Analytics/class-ta-visit-tracker.php; then
    echo "✓ get_http_status() method found"
else
    echo "✗ get_http_status() method NOT found"
fi

# Check if detect_request_type exists
if grep -q "detect_request_type" third-audience/includes/Analytics/class-ta-visit-tracker.php; then
    echo "✓ detect_request_type() method found"
else
    echo "✗ detect_request_type() method NOT found"
fi

# Check JavaScript tracker
if grep -q "client_user_agent: navigator.userAgent" third-audience/public/js/citation-tracker.js; then
    echo "✓ JavaScript UA capture found"
else
    echo "✗ JavaScript UA capture NOT found"
fi

echo ""
echo "Code verification complete!"
echo "To test with real data, see TESTING_GUIDE.md"
