#!/usr/bin/env python3
"""
Query existing bot analytics from Third Audience database
"""

import mysql.connector
import json
from datetime import datetime

def query_bot_analytics():
    """Query real bot data from WordPress database"""
    try:
        # Connect to WordPress database
        conn = mysql.connector.connect(
            host='localhost',
            user='root',
            password='root',
            database='wordpress'
        )
        
        cursor = conn.cursor(dictionary=True)
        
        # Get recent bot visits
        cursor.execute("""
            SELECT bot_type, bot_name, user_agent, referer, url, visited_at
            FROM wp_ta_bot_analytics
            ORDER BY visited_at DESC
            LIMIT 20
        """)
        
        results = cursor.fetchall()
        
        print("\n" + "="*80)
        print("REAL BOT ANALYTICS FROM DATABASE")
        print("="*80)
        print(f"Found {len(results)} recent bot visits\n")
        
        if len(results) == 0:
            print("⚠️  No bot visits found in database!")
            print("This suggests:")
            print("  1. Third Audience plugin may not be active")
            print("  2. No real AI bots have visited yet")
            print("  3. Bot tracking isn't working")
            return
        
        # Group by bot type
        bot_counts = {}
        for row in results:
            bot_type = row['bot_type']
            bot_counts[bot_type] = bot_counts.get(bot_type, 0) + 1
            
            print(f"Bot: {row['bot_name']} ({row['bot_type']})")
            print(f"  User-Agent: {row['user_agent'][:80]}...")
            print(f"  Referer: {row['referer'] or 'None'}")
            print(f"  URL: {row['url']}")
            print(f"  Visited: {row['visited_at']}")
            print()
        
        print("="*80)
        print("BOT TYPE SUMMARY")
        print("="*80)
        for bot_type, count in sorted(bot_counts.items(), key=lambda x: x[1], reverse=True):
            print(f"  {bot_type}: {count} visits")
        
        # Save to file
        with open('analysis/real_bot_data.json', 'w') as f:
            # Convert datetime to string for JSON serialization
            for row in results:
                if 'visited_at' in row and row['visited_at']:
                    row['visited_at'] = str(row['visited_at'])
            json.dump(results, f, indent=2)
        
        print(f"\n📁 Saved to: analysis/real_bot_data.json")
        
        cursor.close()
        conn.close()
        
    except mysql.connector.Error as e:
        print(f"❌ Database Error: {e}")
        print("\nMake sure:")
        print("  1. MySQL is running (docker-compose up)")
        print("  2. Database credentials are correct")
        print("  3. Third Audience plugin is activated in WordPress")

if __name__ == '__main__':
    query_bot_analytics()
