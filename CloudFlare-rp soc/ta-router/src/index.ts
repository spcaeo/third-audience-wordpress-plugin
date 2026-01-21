// Third Audience Router - Main Entry Point
// Load balances requests across multiple Cloudflare Worker accounts

import type { Env, GetWorkerResponse, TrackUsageRequest, TrackUsageResponse, StatsResponse, ErrorResponse, WorkerConfig, SiteConfig } from './types';
import { KV_KEYS } from './types';
import { selectWorker, getWorkerStats, NoWorkersError, NoCapacityError } from './services/worker-selector';
import { trackUsage, getAggregatedStats } from './services/usage-tracker';
import { authenticate, hashToken } from './middleware/auth';
import { checkRateLimit, getRateLimitHeaders } from './middleware/rate-limiter';

export default {
  async fetch(request: Request, env: Env, ctx: ExecutionContext): Promise<Response> {
    const url = new URL(request.url);
    const path = url.pathname;

    // CORS headers
    const corsHeaders = {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Methods': 'GET, POST, PATCH, DELETE, OPTIONS',
      'Access-Control-Allow-Headers': 'Content-Type, Authorization, X-Site-URL',
    };

    // Handle CORS preflight
    if (request.method === 'OPTIONS') {
      return new Response(null, { headers: corsHeaders });
    }

    try {
      // Health check - no auth required
      if (path === '/health' || path === '/') {
        return handleHealth(corsHeaders);
      }

      // Admin endpoints
      if (path.startsWith('/admin/')) {
        return handleAdminEndpoint(request, env, path, corsHeaders);
      }

      // Public API endpoints (require site auth)
      if (path === '/get-worker' && request.method === 'GET') {
        return handleGetWorker(request, env, corsHeaders);
      }

      if (path === '/track-usage' && request.method === 'POST') {
        return handleTrackUsage(request, env, corsHeaders);
      }

      if (path === '/stats' && request.method === 'GET') {
        return handleStats(request, env, corsHeaders);
      }

      // Not found
      return jsonResponse({ success: false, error: { code: 'NOT_FOUND', message: 'Endpoint not found' } }, 404, corsHeaders);

    } catch (error) {
      console.error('Router error:', error);
      return jsonResponse({
        success: false,
        error: { code: 'INTERNAL_ERROR', message: error instanceof Error ? error.message : 'Internal error' }
      }, 500, corsHeaders);
    }
  },
};

function handleHealth(corsHeaders: Record<string, string>): Response {
  return jsonResponse({
    status: 'healthy',
    service: 'third-audience-router',
    version: '1.0.0',
    timestamp: new Date().toISOString(),
    endpoints: {
      '/': 'Health check',
      '/health': 'Health check',
      '/get-worker': 'GET - Select a worker for conversion',
      '/track-usage': 'POST - Track usage after conversion',
      '/stats': 'GET - Get usage statistics (admin)',
      '/admin/init': 'POST - Initialize workers (admin)',
      '/admin/workers': 'GET/POST - Manage workers (admin)',
      '/admin/sites': 'GET/POST - Manage sites (admin)',
    }
  }, 200, corsHeaders);
}

async function handleGetWorker(request: Request, env: Env, corsHeaders: Record<string, string>): Promise<Response> {
  // Skip auth for now (simplified deployment) - can be enabled later
  // const auth = await authenticate(request, env);
  // if (!auth.authenticated) {
  //   return jsonResponse({ success: false, error: { code: 'UNAUTHORIZED', message: auth.error } }, 401, corsHeaders);
  // }

  // Rate limiting by IP
  const clientIP = request.headers.get('CF-Connecting-IP') || 'unknown';
  const rateLimit = await checkRateLimit(env, clientIP, '/get-worker');
  if (!rateLimit.allowed) {
    return jsonResponse({
      success: false,
      error: { code: 'RATE_LIMITED', message: 'Too many requests', retry_after: rateLimit.resetTime - Math.floor(Date.now() / 1000) }
    }, 429, { ...corsHeaders, ...getRateLimitHeaders(rateLimit) });
  }

  try {
    const worker = await selectWorker(env);

    const response: GetWorkerResponse = {
      success: true,
      worker: {
        id: worker.id,
        url: worker.url,
        convert_endpoint: `${worker.url}/convert`,
      },
      usage: {
        worker_today: worker.usage_today,
        worker_limit: worker.daily_limit,
        worker_remaining: worker.daily_limit - worker.usage_today,
      },
    };

    return jsonResponse(response, 200, { ...corsHeaders, ...getRateLimitHeaders(rateLimit) });

  } catch (error) {
    if (error instanceof NoWorkersError) {
      return jsonResponse({ success: false, error: { code: 'NO_WORKERS', message: 'No workers available' } }, 503, corsHeaders);
    }
    if (error instanceof NoCapacityError) {
      return jsonResponse({ success: false, error: { code: 'NO_CAPACITY', message: 'All workers at capacity' } }, 503, corsHeaders);
    }
    throw error;
  }
}

async function handleTrackUsage(request: Request, env: Env, corsHeaders: Record<string, string>): Promise<Response> {
  // Parse body
  let body: TrackUsageRequest;
  try {
    body = await request.json() as TrackUsageRequest;
  } catch {
    return jsonResponse({ success: false, error: { code: 'INVALID_JSON', message: 'Invalid JSON body' } }, 400, corsHeaders);
  }

  // Validate required fields
  if (!body.worker_id || !body.site_url) {
    return jsonResponse({ success: false, error: { code: 'MISSING_FIELDS', message: 'worker_id and site_url are required' } }, 400, corsHeaders);
  }

  const result = await trackUsage(env, body);

  const response: TrackUsageResponse = {
    success: true,
    usage: result,
  };

  return jsonResponse(response, 200, corsHeaders);
}

async function handleStats(request: Request, env: Env, corsHeaders: Record<string, string>): Promise<Response> {
  // Require admin auth for stats
  const auth = await authenticate(request, env);
  if (!auth.authenticated || !auth.isAdmin) {
    return jsonResponse({ success: false, error: { code: 'UNAUTHORIZED', message: 'Admin access required' } }, 401, corsHeaders);
  }

  const url = new URL(request.url);
  const date = url.searchParams.get('date') || new Date().toISOString().split('T')[0];

  const [summary, workers] = await Promise.all([
    getAggregatedStats(env, date),
    getWorkerStats(env),
  ]);

  const response: StatsResponse = {
    date,
    summary,
    workers,
  };

  return jsonResponse(response, 200, corsHeaders);
}

async function handleAdminEndpoint(request: Request, env: Env, path: string, corsHeaders: Record<string, string>): Promise<Response> {
  // Admin auth required
  const auth = await authenticate(request, env);
  if (!auth.authenticated || !auth.isAdmin) {
    return jsonResponse({ success: false, error: { code: 'UNAUTHORIZED', message: 'Admin access required' } }, 401, corsHeaders);
  }

  // POST /admin/init - Initialize workers
  if (path === '/admin/init' && request.method === 'POST') {
    const body = await request.json() as { workers: WorkerConfig[] };

    // Store worker list
    const workerIds = body.workers.map(w => w.id);
    await env.KV.put(KV_KEYS.WORKERS_LIST, JSON.stringify(workerIds));

    // Store each worker config
    await Promise.all(
      body.workers.map(w =>
        env.KV.put(KV_KEYS.workerConfig(w.id), JSON.stringify({
          ...w,
          created_at: new Date().toISOString(),
        }))
      )
    );

    return jsonResponse({ success: true, message: `Initialized ${body.workers.length} workers`, workers: workerIds }, 200, corsHeaders);
  }

  // GET /admin/workers - List workers
  if (path === '/admin/workers' && request.method === 'GET') {
    const workerIds = await env.KV.get<string[]>(KV_KEYS.WORKERS_LIST, 'json') || [];
    const workers = await Promise.all(
      workerIds.map(id => env.KV.get<WorkerConfig>(KV_KEYS.workerConfig(id), 'json'))
    );
    return jsonResponse({ success: true, workers: workers.filter(Boolean) }, 200, corsHeaders);
  }

  // POST /admin/workers - Add worker
  if (path === '/admin/workers' && request.method === 'POST') {
    const worker = await request.json() as WorkerConfig;

    // Add to list
    const workerIds = await env.KV.get<string[]>(KV_KEYS.WORKERS_LIST, 'json') || [];
    if (!workerIds.includes(worker.id)) {
      workerIds.push(worker.id);
      await env.KV.put(KV_KEYS.WORKERS_LIST, JSON.stringify(workerIds));
    }

    // Store config
    await env.KV.put(KV_KEYS.workerConfig(worker.id), JSON.stringify({
      ...worker,
      created_at: new Date().toISOString(),
    }));

    return jsonResponse({ success: true, message: 'Worker added', worker }, 200, corsHeaders);
  }

  // POST /admin/sites - Register site
  if (path === '/admin/sites' && request.method === 'POST') {
    const body = await request.json() as { domain: string; api_key: string; daily_limit?: number };

    const siteConfig: SiteConfig = {
      domain: body.domain,
      api_key_hash: await hashToken(body.api_key),
      daily_limit: body.daily_limit || 10000,
      enabled: true,
      created_at: new Date().toISOString(),
    };

    await env.KV.put(KV_KEYS.siteConfig(body.domain), JSON.stringify(siteConfig));

    return jsonResponse({ success: true, message: 'Site registered', domain: body.domain }, 200, corsHeaders);
  }

  return jsonResponse({ success: false, error: { code: 'NOT_FOUND', message: 'Admin endpoint not found' } }, 404, corsHeaders);
}

function jsonResponse(data: unknown, status: number, headers: Record<string, string>): Response {
  return new Response(JSON.stringify(data), {
    status,
    headers: { ...headers, 'Content-Type': 'application/json' },
  });
}
