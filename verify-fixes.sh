#!/bin/bash
# Verification script for Third Audience Plugin fixes
# Run this to verify all fixes were applied correctly

echo "=========================================="
echo "Third Audience Plugin - Fix Verification"
echo "=========================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

PLUGIN_DIR="/var/www/html/projects/third-audience-wordpress-plugin/third-audience"

echo "Checking fixes..."
echo ""

# Fix #1: Check migration file exists
echo -n "1. Migration file exists: "
if [ -f "$PLUGIN_DIR/includes/migrations/class-ta-migration-3-3-10.php" ]; then
    echo -e "${GREEN}✓ PASS${NC}"
else
    echo -e "${RED}✗ FAIL${NC} - Migration file not found"
fi

# Fix #2: Check DB version updated
echo -n "2. DB version updated to 3.3.10: "
if grep -q "define( 'TA_DB_VERSION', '3.3.10' )" "$PLUGIN_DIR/third-audience.php"; then
    echo -e "${GREEN}✓ PASS${NC}"
else
    echo -e "${RED}✗ FAIL${NC} - DB version not updated"
fi

# Fix #3: Check migration runner exists
echo -n "3. Migration runner added: "
if grep -q "function ta_run_migrations()" "$PLUGIN_DIR/third-audience.php"; then
    echo -e "${GREEN}✓ PASS${NC}"
else
    echo -e "${RED}✗ FAIL${NC} - Migration runner not found"
fi

# Fix #4: Check rate limiting added to notifications
echo -n "4. Email rate limiting added: "
if grep -q "ta_high_error_rate_notified" "$PLUGIN_DIR/includes/class-ta-notifications.php"; then
    echo -e "${GREEN}✓ PASS${NC}"
else
    echo -e "${RED}✗ FAIL${NC} - Rate limiting not added"
fi

# Fix #5: Check bot detector method fixed
echo -n "5. Bot detector method name fixed: "
if grep -q "get_method()" "$PLUGIN_DIR/includes/Analytics/class-ta-bot-detector.php" | grep -v "get_detection_method()"; then
    echo -e "${GREEN}✓ PASS${NC}"
else
    echo -e "${YELLOW}⚠ WARNING${NC} - Check manually"
fi

# Fix #6: Check headless nonce fixed
echo -n "6. Headless nonce verification fixed: "
if grep -q "verify_nonce_or_die( 'save_headless_settings', 'ta_nonce', 'POST' )" "$PLUGIN_DIR/admin/AJAX/class-ta-admin-settings.php"; then
    echo -e "${GREEN}✓ PASS${NC}"
else
    echo -e "${RED}✗ FAIL${NC} - Nonce verification not fixed"
fi

# Check notifications init
echo -n "7. Notifications initialized: "
if grep -q "\$notifications->init()" "$PLUGIN_DIR/third-audience.php"; then
    echo -e "${GREEN}✓ PASS${NC} (Already working)"
else
    echo -e "${RED}✗ FAIL${NC} - Notifications not initialized"
fi

# Check admin settings hooks
echo -n "8. Admin settings hooks registered: "
if grep -q "\$settings->register_hooks()" "$PLUGIN_DIR/admin/class-ta-admin.php"; then
    echo -e "${GREEN}✓ PASS${NC} (Already working)"
else
    echo -e "${RED}✗ FAIL${NC} - Settings hooks not registered"
fi

echo ""
echo "=========================================="
echo "Verification Complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Visit WordPress admin to trigger migration"
echo "2. Check System Health for errors"
echo "3. Test bot tracking with curl"
echo "4. Verify settings save correctly"
echo ""
echo "For detailed testing, see: FIXES_APPLIED.md"
echo ""
