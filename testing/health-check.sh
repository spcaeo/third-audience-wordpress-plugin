#!/bin/bash
# Health Check Script - Verify entire WordPress setup
# Quick diagnostic tool to check if everything is working

set -e

echo "🏥 Third Audience - Health Check"
echo "================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

ISSUES=0
WARNINGS=0

check() {
    local name="$1"
    local command="$2"
    local expected="$3"

    printf "%-40s" "$name"

    if eval "$command" > /dev/null 2>&1; then
        echo -e "${GREEN}✓ PASS${NC}"
    else
        echo -e "${RED}✗ FAIL${NC}"
        if [ -n "$expected" ]; then
            echo -e "  ${YELLOW}Expected: $expected${NC}"
        fi
        ((ISSUES++))
    fi
}

check_with_output() {
    local name="$1"
    local command="$2"

    printf "%-40s" "$name"

    output=$(eval "$command" 2>&1)
    if [ $? -eq 0 ] && [ -n "$output" ]; then
        echo -e "${GREEN}✓ PASS${NC} ($output)"
    else
        echo -e "${RED}✗ FAIL${NC}"
        ((ISSUES++))
    fi
}

warn() {
    local name="$1"
    local message="$2"

    printf "%-40s" "$name"
    echo -e "${YELLOW}⚠ WARN${NC} - $message"
    ((WARNINGS++))
}

echo "1. Docker Environment"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

check "Docker daemon running" "docker info"
check "WordPress container running" "docker ps | grep -q ta-wordpress"
check "MySQL container running" "docker ps | grep -q ta-mysql"
check "phpMyAdmin container running" "docker ps | grep -q ta-phpmyadmin"
check "WP-CLI container running" "docker ps | grep -q ta-wpcli"

echo ""
echo "2. Network Accessibility"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

check "WordPress accessible (8080)" "curl -s -f http://localhost:8080 > /dev/null"
check "Admin panel accessible" "curl -s http://localhost:8080/wp-admin | grep -q wp-login"
check "phpMyAdmin accessible (8081)" "curl -s -f http://localhost:8081 > /dev/null"

echo ""
echo "3. Database"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

check "MySQL responding" "docker exec ta-mysql mysqladmin -u wordpress -pwordpress ping 2>/dev/null | grep -q alive"
check "WordPress database exists" "docker exec ta-mysql mysql -u wordpress -pwordpress -e 'USE wordpress' 2>/dev/null"
check "Can query database" "docker exec ta-mysql mysql -u wordpress -pwordpress wordpress -e 'SHOW TABLES' 2>/dev/null"

echo ""
echo "4. WordPress Status"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if docker exec -u 33:33 ta-wpcli wp core is-installed 2>/dev/null; then
    printf "%-40s" "WordPress installed"
    echo -e "${GREEN}✓ PASS${NC}"

    check_with_output "WordPress version" "docker exec -u 33:33 ta-wpcli wp core version --extra 2>/dev/null | head -1"
    check "Admin user exists" "docker exec -u 33:33 ta-wpcli wp user get admin 2>/dev/null"
else
    printf "%-40s" "WordPress installed"
    echo -e "${RED}✗ FAIL${NC} - Run ./testing/setup-wordpress.sh"
    ((ISSUES++))
fi

echo ""
echo "5. Plugin Status"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if docker exec -u 33:33 ta-wpcli wp core is-installed 2>/dev/null; then
    check "Plugin directory exists" "docker exec ta-wordpress test -d /var/www/html/wp-content/plugins/third-audience"
    check "Plugin detected by WordPress" "docker exec -u 33:33 ta-wpcli wp plugin list 2>/dev/null | grep -q third-audience"

    if docker exec -u 33:33 ta-wpcli wp plugin is-active third-audience 2>/dev/null; then
        printf "%-40s" "Plugin activated"
        echo -e "${GREEN}✓ PASS${NC}"
    else
        printf "%-40s" "Plugin activated"
        echo -e "${RED}✗ FAIL${NC} - Run: docker exec -u 33:33 ta-wpcli wp plugin activate third-audience"
        ((ISSUES++))
    fi
else
    warn "Plugin checks" "WordPress not installed"
fi

echo ""
echo "6. Bot Analytics"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

table_exists=$(docker exec ta-mysql mysql -u wordpress -pwordpress wordpress -sN -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='wordpress' AND table_name='wp_ta_bot_analytics'" 2>/dev/null || echo "0")

if [ "$table_exists" = "1" ]; then
    printf "%-40s" "Analytics table exists"
    echo -e "${GREEN}✓ PASS${NC}"

    visit_count=$(docker exec ta-mysql mysql -u wordpress -pwordpress wordpress -sN -e "SELECT COUNT(*) FROM wp_ta_bot_analytics" 2>/dev/null || echo "0")

    if [ "$visit_count" -gt 0 ]; then
        printf "%-40s" "Analytics data present"
        echo -e "${GREEN}✓ PASS${NC} ($visit_count visits)"
    else
        warn "Analytics data" "No bot visits recorded yet. Run: ./testing/bot-crawler.sh"
    fi
else
    printf "%-40s" "Analytics table exists"
    echo -e "${RED}✗ FAIL${NC} - Table not created"
    ((ISSUES++))
fi

echo ""
echo "7. File Permissions"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

check "wp-content writable" "docker exec ta-wordpress test -w /var/www/html/wp-content"
check "plugins directory writable" "docker exec ta-wordpress test -w /var/www/html/wp-content/plugins"
check "third-audience plugin readable" "docker exec ta-wordpress test -r /var/www/html/wp-content/plugins/third-audience/third-audience.php"

echo ""
echo "8. Configuration"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if docker exec -u 33:33 ta-wpcli wp core is-installed 2>/dev/null; then
    check "Rewrite rules flushed" "docker exec -u 33:33 ta-wpcli wp rewrite list 2>/dev/null | grep -q 'ta_markdown'"
    check "Permalink structure set" "docker exec -u 33:33 ta-wpcli wp option get permalink_structure 2>/dev/null | grep -q /"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Health Check Summary"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if [ "$ISSUES" -eq 0 ]; then
    echo -e "${GREEN}✅ All checks passed!${NC}"
    echo ""
    echo "Your WordPress environment is ready to use:"
    echo ""
    echo "  WordPress:     http://localhost:8080"
    echo "  Admin:         http://localhost:8080/wp-admin"
    echo "  Bot Analytics: http://localhost:8080/wp-admin/admin.php?page=third-audience-bot-analytics"
    echo "  phpMyAdmin:    http://localhost:8081"
    echo ""
    echo "Login: admin / admin123"
    echo ""

    if [ "$WARNINGS" -gt 0 ]; then
        echo -e "${YELLOW}⚠️  $WARNINGS warning(s) detected${NC}"
        echo ""
    fi

    echo "Next steps:"
    echo "  1. Run bot crawler: ./testing/bot-crawler.sh"
    echo "  2. View analytics:  open http://localhost:8080/wp-admin/admin.php?page=third-audience-bot-analytics"
    echo "  3. Run QA tests:    ./testing/playwright-qa-automation.sh"
else
    echo -e "${RED}❌ $ISSUES issue(s) detected${NC}"

    if [ "$WARNINGS" -gt 0 ]; then
        echo -e "${YELLOW}⚠️  $WARNINGS warning(s) detected${NC}"
    fi

    echo ""
    echo "Troubleshooting:"
    echo "  1. Check logs:       docker logs ta-wordpress"
    echo "  2. Restart services: docker-compose restart"
    echo "  3. Fresh install:    docker-compose down -v && docker-compose up -d"
    echo "  4. Setup WordPress:  ./testing/setup-wordpress.sh"
    echo "  5. Read guide:       cat testing/DEBUG-GUIDE.md"
    echo ""

    exit 1
fi

echo ""
