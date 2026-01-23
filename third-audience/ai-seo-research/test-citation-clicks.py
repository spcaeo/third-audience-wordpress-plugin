#!/usr/bin/env python3
"""
Test Citation Click Tracking - Simulating real user clicks from AI platforms
"""

import requests
import json
from datetime import datetime

# Real referrers when users click citations in AI platforms
AI_PLATFORM_REFERRERS = {
    'ChatGPT': 'https://chat.openai.com/',
    'ChatGPT_Search': 'https://chatgpt.com/search?q=wordpress+cache',
    'Perplexity': 'https://www.perplexity.ai/',
    'Perplexity_Search': 'https://www.perplexity.ai/search?q=wordpress+optimization',
    'Claude': 'https://claude.ai/chat/',
    'Gemini': 'https://gemini.google.com/',
}

def test_citation_click(platform_name, referer_url, target_url='http://localhost:8080'):
    """Simulate a user clicking a citation link from an AI platform"""

    print(f"\n{'='*60}")
    print(f"Testing Citation Click: {platform_name}")
    print(f"{'='*60}")

    # Regular browser user agent (NOT a bot)
    headers = {
        'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Referer': referer_url,
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
        'Accept-Language': 'en-US,en;q=0.5',
        'Accept-Encoding': 'gzip, deflate',
        'DNT': '1',
        'Connection': 'keep-alive',
        'Upgrade-Insecure-Requests': '1',
    }

    try:
        response = requests.get(
            target_url,
            headers=headers,
            timeout=10,
            allow_redirects=True
        )

        print(f"Referer: {referer_url}")
        print(f"Status: {response.status_code}")
        print(f"Content-Type: {response.headers.get('Content-Type')}")

        # Check if this gets logged as special traffic
        result = {
            'timestamp': datetime.now().isoformat(),
            'platform': platform_name,
            'referer': referer_url,
            'status': response.status_code,
            'content_type': response.headers.get('Content-Type'),
            'is_html': 'text/html' in response.headers.get('Content-Type', ''),
        }

        return result

    except Exception as e:
        print(f"❌ Error: {e}")
        return {'error': str(e)}

def main():
    print("\n" + "="*60)
    print("AI CITATION CLICK TRACKING TEST")
    print("="*60)
    print("Simulating users clicking citations from AI platforms")
    print("="*60)

    results = []

    for platform, referer in AI_PLATFORM_REFERRERS.items():
        result = test_citation_click(platform, referer)
        results.append(result)

    # Save results
    with open('analysis/citation_click_test.json', 'w') as f:
        json.dump(results, f, indent=2)

    print("\n" + "="*60)
    print("NEXT STEPS")
    print("="*60)
    print("1. Check WordPress Analytics → See if these visits appear")
    print("2. Look for referrer data in logs/analytics")
    print("3. Check if Third Audience tracks AI platform referrers")
    print("\n📁 Results saved to: analysis/citation_click_test.json")

if __name__ == '__main__':
    main()
