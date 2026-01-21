#!/bin/bash
# SneakyBot 🕵️ - Custom Bot Crawler for Testing Third Audience Analytics
# This script simulates AI bot visits to test the bot tracking system

set -e

echo "🕵️  SneakyBot - Third Audience Bot Crawler"
echo "=========================================="
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
DELAY=1  # Delay between requests in seconds

# Funny bot user agents (bash 3.2 compatible)
BOT_NAMES=(
    "SneakyBot"
    "ClaudeBot"
    "GPTBot"
    "PerplexityBot"
    "LazyBot"
    "HungryBot"
    "CuriousBot"
)

BOT_AGENTS=(
    "Mozilla/5.0 (compatible; SneakyBot/1.0; +https://sneakybot.ai) 🕵️"
    "Mozilla/5.0 (compatible; ClaudeBot/1.0; +https://claude.ai)"
    "Mozilla/5.0 (compatible; GPTBot/1.0; +https://openai.com/gptbot)"
    "Mozilla/5.0 (compatible; PerplexityBot/1.0; +https://perplexity.ai)"
    "Mozilla/5.0 (compatible; LazyBot/1.0; +https://lazybot.ai) 😴"
    "Mozilla/5.0 (compatible; HungryBot/1.0; +https://hungrybot.ai) 🍕"
    "Mozilla/5.0 (compatible; CuriousBot/1.0; +https://curiousbot.ai) 🤔"
)

# Function to get bot agent by name
get_bot_agent() {
    local bot_name="$1"
    for i in "${!BOT_NAMES[@]}"; do
        if [ "${BOT_NAMES[$i]}" = "$bot_name" ]; then
            echo "${BOT_AGENTS[$i]}"
            return
        fi
    done
}

# Function to get all WordPress posts
get_posts() {
    docker exec -u 33:33 ta-wpcli wp post list --post_type=post --field=url --format=csv 2>/dev/null | tail -n +2
}

# Function to get all WordPress pages
get_pages() {
    docker exec -u 33:33 ta-wpcli wp post list --post_type=page --field=url --format=csv 2>/dev/null | tail -n +2
}

# Function to make a bot request
bot_request() {
    local url="$1"
    local bot_name="$2"
    local user_agent=$(get_bot_agent "$bot_name")
    local method="$3"  # "md_url" or "accept_header"

    if [ "$method" = "md_url" ]; then
        # Request .md URL directly
        local md_url="${url}.md"
        echo -e "${BLUE}   → $bot_name${NC} requesting: $md_url"

        response=$(curl -s -w "\n%{http_code}" \
            -H "User-Agent: $user_agent" \
            "$md_url")

        http_code=$(echo "$response" | tail -n1)

        if [ "$http_code" = "200" ]; then
            echo -e "${GREEN}   ✓ Success (200) - Markdown returned${NC}"
        else
            echo -e "${RED}   ✗ Failed ($http_code)${NC}"
        fi
    else
        # Request with Accept: text/markdown header
        echo -e "${BLUE}   → $bot_name${NC} requesting with Accept header: $url"

        response=$(curl -s -w "\n%{http_code}" \
            -H "User-Agent: $user_agent" \
            -H "Accept: text/markdown" \
            -L \
            "$url")

        http_code=$(echo "$response" | tail -n1)

        if [ "$http_code" = "200" ]; then
            echo -e "${GREEN}   ✓ Success (200) - Content negotiation worked${NC}"
        else
            echo -e "${RED}   ✗ Failed ($http_code)${NC}"
        fi
    fi

    sleep "$DELAY"
}

# Main menu
echo "Select crawling mode:"
echo "  1) Quick Test (5 requests)"
echo "  2) Full Crawl (All content)"
echo "  3) Stress Test (Multiple bots, all content)"
echo "  4) Custom Bot Simulation"
echo ""
read -p "Enter choice [1-4]: " choice

case $choice in
    1)
        echo ""
        echo "🚀 Running Quick Test..."
        echo ""

        posts=($(get_posts | head -n 3))
        pages=($(get_pages | head -n 2))

        bot_request "${posts[0]}" "SneakyBot" "md_url"
        bot_request "${posts[1]}" "ClaudeBot" "accept_header"
        bot_request "${posts[2]}" "GPTBot" "md_url"
        bot_request "${pages[0]}" "LazyBot" "accept_header"
        bot_request "${pages[1]}" "HungryBot" "md_url"

        echo ""
        echo -e "${GREEN}✅ Quick test complete! Check analytics dashboard.${NC}"
        ;;

    2)
        echo ""
        echo "🔍 Running Full Crawl..."
        echo ""

        all_urls=($(get_posts) $(get_pages))
        total=${#all_urls[@]}

        echo "Found $total URLs to crawl"
        echo ""

        count=1
        for url in "${all_urls[@]}"; do
            echo "[$count/$total]"

            # Alternate between methods
            if [ $((count % 2)) -eq 0 ]; then
                bot_request "$url" "SneakyBot" "md_url"
            else
                bot_request "$url" "ClaudeBot" "accept_header"
            fi

            ((count++))
        done

        echo ""
        echo -e "${GREEN}✅ Full crawl complete!${NC}"
        ;;

    3)
        echo ""
        echo "💪 Running Stress Test..."
        echo ""

        all_urls=($(get_posts) $(get_pages))
        bot_names=("SneakyBot" "ClaudeBot" "GPTBot" "PerplexityBot" "LazyBot" "HungryBot" "CuriousBot")
        methods=("md_url" "accept_header")

        total_requests=$((${#all_urls[@]} * ${#bot_names[@]}))
        echo "Will make $total_requests requests..."
        echo ""

        count=1
        for url in "${all_urls[@]}"; do
            for bot in "${bot_names[@]}"; do
                method=${methods[$((RANDOM % 2))]}

                echo "[$count/$total_requests]"
                bot_request "$url" "$bot" "$method"

                ((count++))
            done
        done

        echo ""
        echo -e "${GREEN}✅ Stress test complete! ${total_requests} requests made.${NC}"
        ;;

    4)
        echo ""
        echo "🤖 Custom Bot Simulation"
        echo ""
        echo "Available bots:"
        i=1
        for bot in "${BOT_NAMES[@]}"; do
            echo "  $i) $bot"
            ((i++))
        done
        echo ""

        read -p "Select bot [1-${#BOT_NAMES[@]}]: " bot_choice
        read -p "Enter URL to crawl: " custom_url
        read -p "Method (1=.md URL, 2=Accept header): " method_choice

        selected_bot="${BOT_NAMES[$((bot_choice-1))]}"

        if [ "$method_choice" = "1" ]; then
            method="md_url"
        else
            method="accept_header"
        fi

        echo ""
        bot_request "$custom_url" "$selected_bot" "$method"

        echo ""
        echo -e "${GREEN}✅ Custom simulation complete!${NC}"
        ;;

    *)
        echo -e "${RED}Invalid choice${NC}"
        exit 1
        ;;
esac

echo ""
echo "📊 View Analytics:"
echo "   $WP_URL/wp-admin/admin.php?page=third-audience-bot-analytics"
echo ""
echo "💡 Analytics tracked:"
echo "   • Bot type & name"
echo "   • URLs visited"
echo "   • Request method"
echo "   • Cache status"
echo "   • Response time"
echo "   • Timestamp"
echo ""
