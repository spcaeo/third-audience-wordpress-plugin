// Third Audience Router - Authentication Middleware

import type { Env, SiteConfig } from '../types';
import { KV_KEYS } from '../types';

export interface AuthResult {
  authenticated: boolean;
  isAdmin: boolean;
  domain?: string;
  error?: string;
}

export async function authenticate(
  request: Request,
  env: Env
): Promise<AuthResult> {
  const authHeader = request.headers.get('Authorization');

  if (!authHeader || !authHeader.startsWith('Bearer ')) {
    return { authenticated: false, isAdmin: false, error: 'Missing or invalid Authorization header' };
  }

  const token = authHeader.substring(7); // Remove 'Bearer '

  // Check if admin token
  if (token.startsWith('ta_admin_')) {
    if (token === env.ADMIN_TOKEN) {
      return { authenticated: true, isAdmin: true };
    }
    return { authenticated: false, isAdmin: false, error: 'Invalid admin token' };
  }

  // Check if site token (ta_live_ or ta_test_)
  if (token.startsWith('ta_live_') || token.startsWith('ta_test_')) {
    // Get site URL from header
    const siteUrl = request.headers.get('X-Site-URL');
    if (!siteUrl) {
      return { authenticated: false, isAdmin: false, error: 'Missing X-Site-URL header' };
    }

    const domain = extractDomain(siteUrl);
    const siteConfig = await env.KV.get<SiteConfig>(KV_KEYS.siteConfig(domain), 'json');

    if (!siteConfig) {
      return { authenticated: false, isAdmin: false, error: 'Site not registered' };
    }

    // Verify token hash
    const tokenHash = await hashToken(token);
    if (tokenHash !== siteConfig.api_key_hash) {
      return { authenticated: false, isAdmin: false, error: 'Invalid API key' };
    }

    if (!siteConfig.enabled) {
      return { authenticated: false, isAdmin: false, error: 'Site is disabled' };
    }

    return { authenticated: true, isAdmin: false, domain };
  }

  return { authenticated: false, isAdmin: false, error: 'Invalid token format' };
}

export async function hashToken(token: string): Promise<string> {
  const encoder = new TextEncoder();
  const data = encoder.encode(token);
  const hashBuffer = await crypto.subtle.digest('SHA-256', data);
  const hashArray = Array.from(new Uint8Array(hashBuffer));
  return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
}

function extractDomain(url: string): string {
  try {
    return new URL(url).hostname;
  } catch {
    return url;
  }
}
