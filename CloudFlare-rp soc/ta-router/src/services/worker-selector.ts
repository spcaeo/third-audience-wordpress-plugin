// Third Audience Router - Worker Selection Service

import type { Env, WorkerConfig, WorkerUsage } from '../types';
import { KV_KEYS } from '../types';

export interface SelectedWorker {
  id: string;
  url: string;
  usage_today: number;
  daily_limit: number;
}

export async function selectWorker(env: Env): Promise<SelectedWorker> {
  const today = getTodayDateString();

  // Get list of worker IDs
  const workerIds = await env.KV.get<string[]>(KV_KEYS.WORKERS_LIST, 'json');
  if (!workerIds || workerIds.length === 0) {
    throw new NoWorkersError();
  }

  // Get configs and usages in parallel
  const workers: Array<{ config: WorkerConfig; usage: WorkerUsage }> = [];

  await Promise.all(
    workerIds.map(async (id) => {
      const [config, usage] = await Promise.all([
        env.KV.get<WorkerConfig>(KV_KEYS.workerConfig(id), 'json'),
        env.KV.get<WorkerUsage>(KV_KEYS.workerUsage(id, today), 'json'),
      ]);

      if (config && config.enabled) {
        workers.push({
          config,
          usage: usage || { count: 0, bytes_in: 0, bytes_out: 0, errors: 0, last_updated: '' },
        });
      }
    })
  );

  if (workers.length === 0) {
    throw new NoWorkersError();
  }

  // Filter workers with capacity (95% threshold)
  const available = workers.filter(
    (w) => w.usage.count < w.config.daily_limit * 0.95
  );

  if (available.length === 0) {
    throw new NoCapacityError();
  }

  // Select least-loaded worker
  let selected = available[0];
  let minUtilization = selected.usage.count / selected.config.daily_limit;

  for (const worker of available) {
    const utilization = worker.usage.count / worker.config.daily_limit;
    if (utilization < minUtilization) {
      minUtilization = utilization;
      selected = worker;
    }
  }

  return {
    id: selected.config.id,
    url: selected.config.url,
    usage_today: selected.usage.count,
    daily_limit: selected.config.daily_limit,
  };
}

export async function getWorkerStats(env: Env): Promise<Array<{
  id: string;
  usage_today: number;
  limit: number;
  utilization: number;
}>> {
  const today = getTodayDateString();
  const workerIds = await env.KV.get<string[]>(KV_KEYS.WORKERS_LIST, 'json');

  if (!workerIds || workerIds.length === 0) {
    return [];
  }

  const stats = await Promise.all(
    workerIds.map(async (id) => {
      const [config, usage] = await Promise.all([
        env.KV.get<WorkerConfig>(KV_KEYS.workerConfig(id), 'json'),
        env.KV.get<WorkerUsage>(KV_KEYS.workerUsage(id, today), 'json'),
      ]);

      if (!config) return null;

      const usageCount = usage?.count || 0;
      return {
        id: config.id,
        usage_today: usageCount,
        limit: config.daily_limit,
        utilization: usageCount / config.daily_limit,
      };
    })
  );

  return stats.filter((s): s is NonNullable<typeof s> => s !== null);
}

function getTodayDateString(): string {
  return new Date().toISOString().split('T')[0];
}

export class NoWorkersError extends Error {
  constructor() {
    super('No workers configured');
    this.name = 'NoWorkersError';
  }
}

export class NoCapacityError extends Error {
  constructor() {
    super('All workers at capacity');
    this.name = 'NoCapacityError';
  }
}
