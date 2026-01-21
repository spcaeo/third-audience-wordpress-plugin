#!/bin/bash
# WordPress Automated Setup Script for Third Audience Testing
# This script sets up WordPress, activates the plugin, and creates test content

set -e

echo "🚀 Third Audience - WordPress Setup Script"
echo "==========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
WP_TITLE="Third Audience Test Site"
WP_ADMIN_USER="admin"
WP_ADMIN_PASSWORD="admin123"
WP_ADMIN_EMAIL="admin@example.com"
WP_URL="http://localhost:8080"

# Function to run WP-CLI commands
wp_cli() {
    docker exec -u 33:33 ta-wpcli wp "$@"
}

# Function to check if WordPress is ready
wait_for_wordpress() {
    echo "⏳ Waiting for WordPress to be ready..."
    local max_attempts=30
    local attempt=1

    while [ $attempt -le $max_attempts ]; do
        if curl -s -f "$WP_URL" > /dev/null 2>&1; then
            echo -e "${GREEN}✓ WordPress is ready!${NC}"
            return 0
        fi
        echo "   Attempt $attempt/$max_attempts..."
        sleep 2
        ((attempt++))
    done

    echo -e "${RED}✗ WordPress failed to start${NC}"
    exit 1
}

# Step 1: Check if Docker containers are running
echo "📦 Step 1: Checking Docker containers..."
if ! docker ps | grep -q ta-wordpress; then
    echo -e "${YELLOW}⚠ Containers not running. Starting them...${NC}"
    docker-compose up -d
    sleep 10
else
    echo -e "${GREEN}✓ Containers are running${NC}"
fi

# Wait for WordPress to be ready
wait_for_wordpress

# Step 2: Check if WordPress is installed
echo ""
echo "🔍 Step 2: Checking WordPress installation..."
if wp_cli core is-installed 2>/dev/null; then
    echo -e "${YELLOW}⚠ WordPress already installed${NC}"
    read -p "Do you want to reinstall? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "🗑️  Removing existing installation..."
        wp_cli db reset --yes
    else
        echo "Skipping WordPress installation"
        SKIP_INSTALL=true
    fi
fi

# Step 3: Install WordPress
if [ "$SKIP_INSTALL" != "true" ]; then
    echo ""
    echo "📥 Step 3: Installing WordPress..."
    wp_cli core install \
        --url="$WP_URL" \
        --title="$WP_TITLE" \
        --admin_user="$WP_ADMIN_USER" \
        --admin_password="$WP_ADMIN_PASSWORD" \
        --admin_email="$WP_ADMIN_EMAIL" \
        --skip-email

    echo -e "${GREEN}✓ WordPress installed successfully!${NC}"
fi

# Step 4: Activate Third Audience plugin
echo ""
echo "🔌 Step 4: Activating Third Audience plugin..."
if wp_cli plugin is-active third-audience 2>/dev/null; then
    echo -e "${YELLOW}⚠ Plugin already active${NC}"
else
    wp_cli plugin activate third-audience
    echo -e "${GREEN}✓ Third Audience plugin activated!${NC}"
fi

# Step 5: Configure plugin settings
echo ""
echo "⚙️  Step 5: Configuring plugin settings..."
wp_cli option update ta_enabled_post_types '["post","page"]' --format=json
wp_cli option update ta_enable_content_negotiation '1'
wp_cli option update ta_enable_discovery_tags '1'
wp_cli option update ta_cache_ttl '3600'
echo -e "${GREEN}✓ Plugin configured${NC}"

# Step 6: Create test content
echo ""
echo "📝 Step 6: Creating test content..."

# Create posts
for i in {1..5}; do
    POST_TITLE="Test Post $i - AI Bot Tracking Demo"
    POST_CONTENT="<h2>Introduction</h2>
<p>This is test post number $i for demonstrating the Third Audience bot analytics system.</p>

<h2>What is Third Audience?</h2>
<p>Third Audience is a WordPress plugin that optimizes your content for AI crawlers like ClaudeBot, GPTBot, and PerplexityBot.</p>

<h2>Key Features</h2>
<ul>
<li>Automatic markdown conversion</li>
<li>Multi-tier caching system</li>
<li>Comprehensive bot analytics</li>
<li>Real-time tracking dashboard</li>
<li>Zero configuration needed</li>
</ul>

<h2>How It Works</h2>
<p>When an AI bot requests your content with <code>Accept: text/markdown</code> or by accessing <code>.md</code> URLs, Third Audience automatically converts your HTML content to clean, semantic markdown.</p>

<h3>Performance Benefits</h3>
<p>Our multi-tier caching ensures blazing-fast responses:</p>
<ol>
<li>Memory cache (instant)</li>
<li>Object cache (sub-second)</li>
<li>Transient cache (fast)</li>
<li>On-demand conversion (when needed)</li>
</ol>

<blockquote>
<p>\"Third Audience makes my content AI-ready without any effort!\" - Happy User</p>
</blockquote>

<p>Visit our <a href=\"https://github.com/anthropics/third-audience\">GitHub repository</a> to learn more.</p>"

    POST_ID=$(wp_cli post create \
        --post_title="$POST_TITLE" \
        --post_content="$POST_CONTENT" \
        --post_status=publish \
        --post_author=1 \
        --porcelain)

    echo "   Created: $POST_TITLE (ID: $POST_ID)"
done

# Create pages
for i in {1..3}; do
    PAGE_TITLE="About Page $i"
    PAGE_CONTENT="<h1>About Third Audience</h1>
<p>This is test page $i demonstrating bot analytics tracking.</p>
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>"

    PAGE_ID=$(wp_cli post create \
        --post_type=page \
        --post_title="$PAGE_TITLE" \
        --post_content="$PAGE_CONTENT" \
        --post_status=publish \
        --post_author=1 \
        --porcelain)

    echo "   Created: $PAGE_TITLE (ID: $PAGE_ID)"
done

echo -e "${GREEN}✓ Test content created${NC}"

# Step 7: Flush rewrite rules
echo ""
echo "🔄 Step 7: Flushing rewrite rules..."
wp_cli rewrite flush --hard
echo -e "${GREEN}✓ Rewrite rules flushed${NC}"

# Summary
echo ""
echo "=========================================="
echo -e "${GREEN}✅ Setup Complete!${NC}"
echo "=========================================="
echo ""
echo "📍 Access URLs:"
echo "   WordPress:     $WP_URL"
echo "   Admin:         $WP_URL/wp-admin"
echo "   phpMyAdmin:    http://localhost:8081"
echo "   Bot Analytics: $WP_URL/wp-admin/admin.php?page=third-audience-bot-analytics"
echo ""
echo "🔐 Login Credentials:"
echo "   Username: $WP_ADMIN_USER"
echo "   Password: $WP_ADMIN_PASSWORD"
echo ""
echo "📄 Test Content:"
echo "   5 posts created"
echo "   3 pages created"
echo ""
echo "🤖 Next Steps:"
echo "   1. Run bot crawler: ./testing/bot-crawler.sh"
echo "   2. View analytics: $WP_URL/wp-admin/admin.php?page=third-audience-bot-analytics"
echo ""
