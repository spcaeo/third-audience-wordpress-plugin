// Third Audience Router - Type Definitions

export interface Env {
  KV: KVNamespace;
  ADMIN_TOKEN: string;
  ENVIRONMENT: string;
}

export interface WorkerConfig {
  id: string;
  url: string;
  daily_limit: number;
  enabled: boolean;
  created_at: string;
}

export interface WorkerUsage {
  count: number;
  bytes_in: number;
  bytes_out: number;
  errors: number;
  last_updated: string;
}

export interface SiteConfig {
  domain: string;
  api_key_hash: string;
  daily_limit: number;
  enabled: boolean;
  created_at: string;
}

export interface SiteUsage {
  count: number;
  last_updated: string;
}

export interface GetWorkerResponse {
  success: true;
  worker: {
    id: string;
    url: string;
    convert_endpoint: string;
  };
  usage: {
    worker_today: number;
    worker_limit: number;
    worker_remaining: number;
  };
}

export interface TrackUsageRequest {
  worker_id: string;
  site_url: string;
  url_converted: string;
  bytes_in: number;
  bytes_out: number;
  conversion_time_ms: number;
  cache_hit: boolean;
  success: boolean;
}

export interface TrackUsageResponse {
  success: true;
  usage: {
    worker_today: number;
    site_today: number;
  };
}

export interface StatsResponse {
  date: string;
  summary: {
    total_requests: number;
    unique_sites: number;
    cache_hit_rate: number;
    error_rate: number;
  };
  workers: Array<{
    id: string;
    usage_today: number;
    limit: number;
    utilization: number;
  }>;
}

export interface ErrorResponse {
  success: false;
  error: {
    code: string;
    message: string;
    retry_after?: number;
  };
  request_id?: string;
}

export interface RateLimitInfo {
  count: number;
  window_start: number;
}

// KV Key patterns
export const KV_KEYS = {
  WORKERS_LIST: 'workers:list',
  workerConfig: (id: string) => `worker:${id}:config`,
  workerUsage: (id: string, date: string) => `usage:${id}:${date}`,
  siteConfig: (domain: string) => `site:${domain}:config`,
  siteUsage: (domain: string, date: string) => `site:${domain}:usage:${date}`,
  rateLimit: (key: string, window: string) => `ratelimit:${key}:${window}`,
} as const;
