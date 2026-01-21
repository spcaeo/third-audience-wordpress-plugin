// Third Audience Router - Usage Tracking Service

import type { Env, WorkerUsage, SiteUsage, TrackUsageRequest } from '../types';
import { KV_KEYS } from '../types';

export interface TrackingResult {
  worker_today: number;
  site_today: number;
}

export async function trackUsage(
  env: Env,
  request: TrackUsageRequest
): Promise<TrackingResult> {
  const today = getTodayDateString();
  const domain = extractDomain(request.site_url);

  // Update worker usage
  const workerUsageKey = KV_KEYS.workerUsage(request.worker_id, today);
  const workerUsage = await env.KV.get<WorkerUsage>(workerUsageKey, 'json') || {
    count: 0,
    bytes_in: 0,
    bytes_out: 0,
    errors: 0,
    last_updated: '',
  };

  workerUsage.count += 1;
  workerUsage.bytes_in += request.bytes_in;
  workerUsage.bytes_out += request.bytes_out;
  if (!request.success) {
    workerUsage.errors += 1;
  }
  workerUsage.last_updated = new Date().toISOString();

  // Update site usage
  const siteUsageKey = KV_KEYS.siteUsage(domain, today);
  const siteUsage = await env.KV.get<SiteUsage>(siteUsageKey, 'json') || {
    count: 0,
    last_updated: '',
  };

  siteUsage.count += 1;
  siteUsage.last_updated = new Date().toISOString();

  // Write both updates (TTL of 7 days for cleanup)
  await Promise.all([
    env.KV.put(workerUsageKey, JSON.stringify(workerUsage), {
      expirationTtl: 86400 * 7,
    }),
    env.KV.put(siteUsageKey, JSON.stringify(siteUsage), {
      expirationTtl: 86400 * 7,
    }),
  ]);

  return {
    worker_today: workerUsage.count,
    site_today: siteUsage.count,
  };
}

export async function getAggregatedStats(
  env: Env,
  date: string
): Promise<{
  total_requests: number;
  unique_sites: number;
  cache_hit_rate: number;
  error_rate: number;
}> {
  const workerIds = await env.KV.get<string[]>(KV_KEYS.WORKERS_LIST, 'json') || [];

  let totalRequests = 0;
  let totalErrors = 0;

  await Promise.all(
    workerIds.map(async (id) => {
      const usage = await env.KV.get<WorkerUsage>(KV_KEYS.workerUsage(id, date), 'json');
      if (usage) {
        totalRequests += usage.count;
        totalErrors += usage.errors;
      }
    })
  );

  // For unique sites, we'd need to list all site keys - simplified for now
  return {
    total_requests: totalRequests,
    unique_sites: 0, // Would need KV list operation
    cache_hit_rate: 0, // Would need cache tracking
    error_rate: totalRequests > 0 ? totalErrors / totalRequests : 0,
  };
}

function getTodayDateString(): string {
  return new Date().toISOString().split('T')[0];
}

function extractDomain(url: string): string {
  try {
    return new URL(url).hostname;
  } catch {
    return url;
  }
}
