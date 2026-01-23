import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';
import { fetchRedirections } from './lib/api';

/**
 * ========================================
 * AI CRAWLER DETECTION - START
 * Tracks AI crawler visits and logs to WordPress
 * Added: January 2026
 * ========================================
 */
const AI_CRAWLERS = [
  // OpenAI / ChatGPT
  { name: 'GPTBot', pattern: /GPTBot/i },
  { name: 'ChatGPT-User', pattern: /ChatGPT-User/i },
  { name: 'OAI-SearchBot', pattern: /OAI-SearchBot/i },
  // Google AI / Gemini
  { name: 'Google-Extended', pattern: /Google-Extended/i },
  { name: 'GoogleOther', pattern: /GoogleOther/i },
  // Perplexity
  { name: 'PerplexityBot', pattern: /PerplexityBot/i },
];

function detectAICrawler(userAgent: string | null): string | null {
  if (!userAgent) return null;
  for (const crawler of AI_CRAWLERS) {
    if (crawler.pattern.test(userAgent)) {
      return crawler.name;
    }
  }
  return null;
}

function logCrawlerVisit(crawlerName: string, request: NextRequest) {
  const data = {
    crawler: crawlerName,
    url: request.nextUrl.pathname,
    ip: request.headers.get('x-forwarded-for')?.split(',')[0] ||
        request.headers.get('x-real-ip') ||
        'unknown',
  };

  // Always use cms.fieldcamp.ai for crawler logging (separate from main WORDPRESS_BASE_URL)
  const crawlerApiUrl = process.env.CRAWLER_API_URL || 'https://cms.fieldcamp.ai/wp-json/fieldcamp/v1/ai-crawler-log';
  const apiKey = process.env.CRAWLER_API_KEY || 'fc-crawler-2026-fieldcamp-secure-key';

  // Log for debugging
  console.log(`[AI Crawler Detected] ${crawlerName} visited ${data.url} from IP: ${data.ip}`);

  // Fire and forget - non-blocking request with error logging
  fetch(crawlerApiUrl, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Crawler-Api-Key': apiKey,
    },
    body: JSON.stringify(data),
  })
    .then((response) => {
      if (!response.ok) {
        console.error(`[AI Crawler Log] Failed to log: ${response.status} ${response.statusText}`);
      } else {
        console.log(`[AI Crawler Log] Successfully logged ${crawlerName} visit`);
      }
    })
    .catch((error) => {
      console.error(`[AI Crawler Log] Error sending to API:`, error.message);
    });
}
/**
 * ========================================
 * AI CRAWLER DETECTION - END
 * ========================================
 */

let redirections: any[] = [];

// Function to revalidate redirections
const revalidateRedirections = async () => {
  redirections = await fetchRedirections();
};

// Set an interval to revalidate every hour (3600000 ms)
setInterval(revalidateRedirections, 3600000);

// Fetch redirections once when the middleware is initialized
(async () => {
  redirections = await fetchRedirections();
})();

export async function middleware(request: NextRequest) {
  // AI Crawler Detection - check every request
  const userAgent = request.headers.get('user-agent');
  const crawlerName = detectAICrawler(userAgent);
  if (crawlerName) {
    logCrawlerVisit(crawlerName, request);
  }

  const { pathname, origin } = request.nextUrl;

  for (const redirection of redirections) {
    if (redirection.status === 'ACTIVE') {
      for (const source of redirection.sources) {
        let pattern = source.pattern;

        const regex = new RegExp(`^/${pattern}/?$`);
        if (regex.test(`${pathname}`)) {
          console.log('Match found:', `${origin}${pathname}`);
          const destination = redirection.redirectToUrl;

          if (redirection.type === 'REDIRECT_301') {
            return NextResponse.redirect(new URL(destination, request.url), 301);
          } else if (redirection.type === 'REDIRECT_302') {
            return NextResponse.redirect(new URL(destination, request.url), 302);
          } else if (redirection.type === 'REDIRECT_410') {
            const notFoundPage = '<h1>Page Gone</h1><p>The requested page has been removed.</p>'; // Custom 410 page content
            return new NextResponse(notFoundPage, { status: 410, headers: { 'Content-Type': 'text/html' } }); // Return custom 410 page

          }
        }
      }
    }
  }

  return NextResponse.next(); // Continue to the next middleware or route
} 

export const config = {
  matcher: '/:path*', // Apply middleware to all paths
};