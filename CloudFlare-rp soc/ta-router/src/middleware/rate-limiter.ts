// Third Audience Router - Rate Limiting Middleware

import type { Env, RateLimitInfo } from '../types';
import { KV_KEYS } from '../types';

export interface RateLimitConfig {
  limit: number;
  windowSeconds: number;
}

export interface RateLimitResult {
  allowed: boolean;
  limit: number;
  remaining: number;
  resetTime: number;
}

const ENDPOINT_LIMITS: Record<string, RateLimitConfig> = {
  '/get-worker': { limit: 100, windowSeconds: 60 },
  '/track-usage': { limit: 200, windowSeconds: 60 },
  '/stats': { limit: 10, windowSeconds: 60 },
  '/admin/workers': { limit: 30, windowSeconds: 60 },
  '/admin/sites': { limit: 30, windowSeconds: 60 },
  default: { limit: 60, windowSeconds: 60 },
};

export async function checkRateLimit(
  env: Env,
  identifier: string,
  endpoint: string
): Promise<RateLimitResult> {
  const config = ENDPOINT_LIMITS[endpoint] || ENDPOINT_LIMITS.default;
  const windowStart = Math.floor(Date.now() / (config.windowSeconds * 1000));
  const key = KV_KEYS.rateLimit(identifier, `${windowStart}`);

  // Get current count
  const info = await env.KV.get<RateLimitInfo>(key, 'json') || {
    count: 0,
    window_start: windowStart,
  };

  const resetTime = (windowStart + 1) * config.windowSeconds;

  if (info.count >= config.limit) {
    return {
      allowed: false,
      limit: config.limit,
      remaining: 0,
      resetTime,
    };
  }

  // Increment counter
  info.count += 1;
  await env.KV.put(key, JSON.stringify(info), {
    expirationTtl: config.windowSeconds * 2, // Keep for 2 windows for safety
  });

  return {
    allowed: true,
    limit: config.limit,
    remaining: config.limit - info.count,
    resetTime,
  };
}

export function getRateLimitHeaders(result: RateLimitResult): Record<string, string> {
  return {
    'X-RateLimit-Limit': String(result.limit),
    'X-RateLimit-Remaining': String(result.remaining),
    'X-RateLimit-Reset': String(result.resetTime),
  };
}
