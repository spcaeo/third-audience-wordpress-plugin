#!/usr/bin/env python3
"""
AI Search Bot Simulator
Tests what data Third Audience can capture from AI search bots
"""

import requests
import json
import time
from datetime import datetime
from urllib.parse import urljoin

# AI Bot User Agents (matches Third Audience detection patterns)
AI_BOTS = {
    'ChatGPT': {
        'user_agent': 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; ChatGPT-User/1.0; +https://openai.com/bot',
        'referer': 'https://chat.openai.com/',
    },
    'GPTBot': {
        'user_agent': 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; GPTBot/1.0; +https://openai.com/gptbot',
        'referer': 'https://openai.com/',
    },
    'Perplexity': {
        'user_agent': 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; PerplexityBot/1.0; +https://perplexity.ai/bot',
        'referer': 'https://www.perplexity.ai/',
    },
    'Claude': {
        'user_agent': 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; ClaudeBot/1.0; +https://www.anthropic.com/bot',
        'referer': 'https://claude.ai/',
    }
}

# Test scenarios based on Anthony Lee's research
TEST_SCENARIOS = [
    {
        'name': 'Search Query in Referer',
        'query': 'best AI automation tools',
        'custom_headers': {'X-Search-Query': 'best AI automation tools'}
    },
    {
        'name': 'Citation Request',
        'query': 'wordpress cache optimization',
        'custom_headers': {'X-Citation-Request': 'true'}
    },
    {
        'name': 'Standard Crawl',
        'query': None,
        'custom_headers': {}
    }
]

class AIBotSimulator:
    def __init__(self, base_url='http://localhost:8080'):
        self.base_url = base_url
        self.results = []

    def test_bot_request(self, bot_name, bot_config, scenario):
        """Simulate a single bot request"""
        print(f"\n{'='*60}")
        print(f"Testing: {bot_name} - {scenario['name']}")
        print(f"{'='*60}")

        headers = {
            'User-Agent': bot_config['user_agent'],
            'Referer': bot_config['referer'],
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language': 'en-US,en;q=0.5',
        }

        # Add custom headers from scenario
        headers.update(scenario['custom_headers'])

        # Add search query to referer if present
        if scenario['query']:
            headers['Referer'] = f"{bot_config['referer']}?q={scenario['query']}"

        try:
            # Request homepage
            response = requests.get(
                self.base_url,
                headers=headers,
                timeout=10,
                allow_redirects=True
            )

            result = {
                'timestamp': datetime.now().isoformat(),
                'bot_name': bot_name,
                'scenario': scenario['name'],
                'query': scenario['query'],
                'status_code': response.status_code,
                'headers_sent': dict(headers),
                'response_headers': dict(response.headers),
                'content_type': response.headers.get('Content-Type'),
                'response_size': len(response.content),
                'is_markdown': 'text/markdown' in response.headers.get('Content-Type', ''),
            }

            # Check if Third Audience detected this as a bot
            if 'text/markdown' in response.headers.get('Content-Type', ''):
                print(f"✅ Third Audience served markdown (bot detected!)")
                result['third_audience_detection'] = True
            else:
                print(f"❌ Regular HTML served (not detected as bot)")
                result['third_audience_detection'] = False

            print(f"Status: {response.status_code}")
            print(f"Content-Type: {response.headers.get('Content-Type')}")
            print(f"Size: {len(response.content)} bytes")

            self.results.append(result)

        except Exception as e:
            print(f"❌ Error: {e}")
            self.results.append({
                'timestamp': datetime.now().isoformat(),
                'bot_name': bot_name,
                'scenario': scenario['name'],
                'error': str(e)
            })

    def run_all_tests(self):
        """Run all test scenarios for all bots"""
        print("\n" + "="*60)
        print("AI Search Bot Simulator - Starting Tests")
        print("="*60)
        print(f"Target: {self.base_url}")
        print(f"Bots: {len(AI_BOTS)}")
        print(f"Scenarios: {len(TEST_SCENARIOS)}")
        print("="*60)

        for bot_name, bot_config in AI_BOTS.items():
            for scenario in TEST_SCENARIOS:
                self.test_bot_request(bot_name, bot_config, scenario)
                time.sleep(1)  # Be nice to localhost

        # Save results
        self.save_results()
        self.print_summary()

    def save_results(self):
        """Save test results to JSON file"""
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        filename = f'analysis/test_results_{timestamp}.json'

        import os
        os.makedirs('analysis', exist_ok=True)

        with open(filename, 'w') as f:
            json.dump(self.results, f, indent=2)

        print(f"\n📁 Results saved to: {filename}")

    def print_summary(self):
        """Print summary of findings"""
        print("\n" + "="*60)
        print("SUMMARY")
        print("="*60)

        detected = sum(1 for r in self.results if r.get('third_audience_detection'))
        total = len([r for r in self.results if 'error' not in r])

        print(f"Total Requests: {len(self.results)}")
        print(f"Successful: {total}")
        print(f"Bot Detection Rate: {detected}/{total} ({detected/total*100:.1f}%)")

        # Group by bot
        print("\nDetection by Bot:")
        for bot_name in AI_BOTS.keys():
            bot_results = [r for r in self.results if r.get('bot_name') == bot_name and 'error' not in r]
            bot_detected = sum(1 for r in bot_results if r.get('third_audience_detection'))
            print(f"  {bot_name}: {bot_detected}/{len(bot_results)}")

        print("\n" + "="*60)

if __name__ == '__main__':
    import sys

    # Allow custom URL via command line
    base_url = sys.argv[1] if len(sys.argv) > 1 else 'http://localhost:8080'

    simulator = AIBotSimulator(base_url)
    simulator.run_all_tests()

    print("\n✅ Testing complete! Check analysis/ folder for detailed results.")
