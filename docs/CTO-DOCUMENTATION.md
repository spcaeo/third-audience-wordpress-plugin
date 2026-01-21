# Third Audience
## CTO Technical & Strategic Documentation

**Version:** 1.0
**Status:** Planning Phase
**Last Updated:** January 2026

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Product Vision & Roadmap](#2-product-vision--roadmap)
3. [Business Considerations](#3-business-considerations)
4. [Technical Strategy](#4-technical-strategy)
5. [Implementation Phases](#5-implementation-phases)
6. [Risk Assessment](#6-risk-assessment)
7. [Success Metrics](#7-success-metrics)
8. [Appendices](#8-appendices)

---

## 1. Executive Summary

### 1.1 Problem Statement

For two decades, web publishers optimized for two audiences: **humans** (UX, design, accessibility) and **search engines** (SEO, structured data, sitemaps). AI agents are now the **third audience**, and the vast majority of websites are not optimized for them.

**The challenges:**

- **AI crawlers need clean content:** Bots like ClaudeBot, GPTBot, and Perplexity struggle with HTML-heavy pages filled with navigation, ads, scripts, and styling noise
- **WordPress dominates the web:** 43%+ of all websites run on WordPress, representing millions of sites that need AI optimization
- **Scale problem:** Site owners managing hundreds of sites with thousands of pages cannot manually optimize each one
- **No standardized solution exists:** Publishers are left to build custom solutions or ignore the problem entirely

### 1.2 Solution Overview

**Third Audience** is an open-source infrastructure layer that makes any WordPress site AI-crawler friendly by serving clean markdown versions of pages on demand.

**Core mechanism:**
```
yoursite.com/about-us      -> HTML (humans/browsers)
yoursite.com/about-us.md   -> Markdown (AI crawlers)
```

**Key components:**

1. **Cloudflare Worker** - On-the-fly HTML-to-Markdown conversion at the edge
2. **Multi-Account Router** - Load balancing across Cloudflare accounts for scale
3. **WordPress Plugin** - Zero-config integration with automatic discovery tags
4. **Usage Dashboard** - Monitoring, analytics, and account management

### 1.3 Why This Matters

**The AI revolution is here:**
- ChatGPT, Claude, Perplexity, and dozens of AI assistants are becoming primary information sources
- Users increasingly ask AI for answers instead of searching Google
- AI systems that can efficiently consume your content will surface it in responses
- Sites not optimized for AI crawlers risk becoming invisible in the AI era

**The opportunity:**
- First-mover advantage in an emerging category
- Open source creates community and trust
- Infrastructure play with network effects
- WordPress ecosystem is massive and underserved

**The reference point:**
- Dries Buytaert (Drupal founder) documented this exact need in "The Third Audience"
- His experiments showed AI crawlers immediately consuming markdown endpoints
- This validates both the problem and the solution approach

---

## 2. Product Vision & Roadmap

### 2.1 Vision Statement

**"Make every website AI-ready with zero configuration."**

We believe the web should be equally accessible to humans, search engines, and AI agents. Third Audience provides the infrastructure layer that bridges today's human-optimized web with tomorrow's AI-first discovery paradigm.

### 2.2 MVP Scope (v0.1)

**Goal:** Prove the core conversion works reliably

**Deliverables:**
- Single Cloudflare Worker that converts HTML to Markdown
- Support for `.md` URL extension pattern
- Basic content extraction (strip navigation, ads, scripts)
- Manual deployment via wrangler CLI

**Success criteria:**
- Clean markdown output for 90%+ of WordPress pages
- < 500ms response time for cached content
- Handles standard WordPress themes correctly

### 2.3 V1.0 Features

**Goal:** Production-ready for single-site deployment

**Deliverables:**
- Content negotiation via `Accept: text/markdown` header
- Intelligent content extraction using multiple strategies
- KV-based caching with configurable TTL
- Error handling and fallback responses
- WordPress plugin with auto-discovery tags:
  ```html
  <link rel="alternate" type="text/markdown" href="/page.md">
  ```
- Basic analytics (request counts, cache hit rates)
- Documentation and deployment guides

**Success criteria:**
- Works on 95%+ of WordPress themes
- Sub-200ms response for cached content
- Plugin installs in < 2 minutes
- Zero configuration required for basic usage

### 2.4 V2.0 Features

**Goal:** Scale to multi-site deployments

**Deliverables:**
- Multi-account router service
- Automatic load balancing across Cloudflare accounts
- Usage tracking and quota management
- Account provisioning workflow
- Admin dashboard with:
  - Per-site analytics
  - Crawler identification
  - Content health monitoring
  - Account status and quotas
- API for programmatic management
- Webhook notifications for quota alerts

**Success criteria:**
- Support 100+ sites across 10+ Cloudflare accounts
- Automatic failover when accounts hit limits
- < 50ms routing overhead
- 99.9% uptime

### 2.5 Long-Term Vision (V3.0+)

**Expansion possibilities:**

1. **Platform agnostic**
   - Shopify, Wix, Squarespace adapters
   - Generic HTML sites support
   - Headless CMS integrations

2. **Advanced AI optimization**
   - Structured data extraction (JSON-LD enhancement)
   - Semantic chunking for RAG systems
   - Entity recognition and linking
   - Content quality scoring

3. **Analytics and insights**
   - AI crawler identification and tracking
   - Content attribution tracking
   - Citation monitoring across AI systems
   - Competitive intelligence

4. **Enterprise features**
   - Custom domains
   - SLA guarantees
   - Priority support
   - Custom transformations
   - White-labeling

---

## 3. Business Considerations

### 3.1 Open Source Strategy

**License:** MIT or Apache 2.0

**Why open source:**

1. **Trust and transparency**
   - Publishers need to trust infrastructure touching their content
   - Open source enables security audits
   - Community can verify no data harvesting

2. **Adoption velocity**
   - Lower barrier to entry
   - WordPress community expects open source
   - Enables agency/developer recommendations

3. **Community contributions**
   - Theme-specific fixes from the community
   - Translation and localization
   - Testing across diverse environments

**What stays open:**
- Core Worker code
- WordPress plugin
- Router service
- Documentation

**Potential proprietary elements (future):**
- Hosted dashboard service
- Enterprise management features
- Advanced analytics
- Priority support

### 3.2 Target Users

**Primary: WordPress site owners**
- Bloggers and content creators
- Small business websites
- News and media sites
- Educational institutions
- Documentation sites

**Secondary: WordPress agencies**
- Managing multiple client sites
- Need scalable solutions
- Value open source for customization

**Tertiary: Enterprise publishers**
- Large WordPress multisite installations
- Custom enterprise requirements
- Need SLA and support

### 3.3 Potential Monetization

**Phase 1: Pure open source (Year 1)**
- Build community and adoption
- Establish credibility
- Gather feedback and requirements

**Phase 2: Services layer (Year 2+)**

| Offering | Price Point | Value Proposition |
|----------|-------------|-------------------|
| Hosted Router | $29/mo | No infrastructure management |
| Managed Accounts | $99/mo | We manage Cloudflare accounts |
| Analytics Dashboard | $49/mo | AI crawler insights |
| Enterprise Support | $499/mo | SLA, priority support |
| Custom Development | Project-based | Tailored solutions |

### 3.4 Competitive Landscape

**Direct competitors:** None identified (first-mover opportunity)

**Adjacent solutions:**

| Solution | Approach | Limitation |
|----------|----------|------------|
| robots.txt | Block/allow crawlers | Binary, no optimization |
| llms.txt | Manual markdown file | Doesn't scale, manual updates |
| RSS feeds | Structured content | Limited metadata, not markdown |
| AMP | Lightweight HTML | Different problem, deprecated |
| Cloudflare CDN | Caching | No content transformation |

**Our differentiation:**
- Automatic, not manual
- Scales to any site size
- WordPress-native integration
- Open source and auditable
- Multi-account scalability

---

## 4. Technical Strategy

### 4.1 Why Cloudflare Workers

**Performance:**
- Edge execution in 300+ locations globally
- < 50ms cold start, < 1ms warm execution
- Built-in caching with KV storage
- No origin server load for cached responses

**Economics:**
- 100,000 free requests/day per account
- $5/month for 10M requests (paid tier)
- Effectively unlimited scale with multi-account

**Developer experience:**
- JavaScript/TypeScript native
- Wrangler CLI for deployment
- Local development with miniflare
- Excellent documentation

**Ecosystem:**
- KV for usage tracking and configuration
- Durable Objects for state (future)
- R2 for storage (future)
- Workers AI for enhancements (future)

### 4.5 Caching Strategy

> **Architecture Decision:** WordPress Transients are the primary cache layer. This keeps cached content closest to the user and reduces external API calls.

**Multi-Level Caching (in order of priority):**

| Layer | Location | TTL | Purpose |
|-------|----------|-----|---------|
| L1 | WordPress Transients | 24h (configurable) | Primary cache, fastest response |
| L2 | Cloudflare Edge | 1h | CDN-level caching for worker responses |
| L3 | Worker Processing | N/A | On-demand conversion (cache miss) |

**Why Transients First:**
1. **Fastest response** - No network hop to external service
2. **Reduced costs** - Fewer worker invocations
3. **Reliability** - Works even if router/workers are down (for cached content)
4. **WordPress-native** - Uses existing WP caching infrastructure

**Cache Invalidation:**
- Automatic on `save_post`, `delete_post`, `edit_post` hooks
- Manual purge via admin settings page
- TTL expiration for stale content refresh

### 4.2 Why Multi-Account Approach

**The math:**
- 100K free requests/day/account = 3M/month
- 10 accounts = 30M free requests/month
- 100 accounts = 300M free requests/month

**Real-world scenario:**
- Site with 1000 pages
- 100 AI crawl requests/day
- = 100K requests/month per site
- One account supports ~30 sites
- 10 accounts support ~300 sites

**Benefits:**
- Massive cost reduction (potentially $0)
- Natural isolation between customers
- Graceful degradation if one account has issues
- Geographic distribution possible

### 4.3 Security Considerations

**Content security:**
- No content storage (pass-through only)
- Caching respects origin headers
- No modification of original content meaning
- Transparent transformation

**Infrastructure security:**
- Each Cloudflare account isolated
- API keys stored securely (env vars/secrets)
- Rate limiting at router level
- Abuse detection and blocking

**WordPress plugin security:**
- No external API calls in basic mode
- Minimal permissions required
- Code review friendly (simple, auditable)
- WordPress.org guidelines compliant

### 4.4 Scalability Plan

| Scale | Accounts | Monthly Requests | Monthly Cost |
|-------|----------|------------------|--------------|
| Starter | 1 | 3M | $0 |
| Growth | 10 | 30M | $0 |
| Scale | 50 | 150M | $0 |
| Enterprise | 100+ | 300M+ | ~$50* |

*Minimal costs for database hosting and router service

---

## 5. Implementation Phases

### Phase 1: Core Worker

**What:** A single Cloudflare Worker that intercepts requests ending in `.md` and converts the corresponding HTML page to clean markdown.

**Success criteria:**
- [ ] Handles standard WordPress themes (Twenty Twenty-X series)
- [ ] Extracts main content, ignores navigation/sidebar/footer
- [ ] Produces valid, readable markdown
- [ ] Response time < 500ms uncached, < 100ms cached
- [ ] Graceful error handling (returns 404/500 appropriately)

### Phase 2: Multi-Account Router

**What:** A lightweight service that sits in front of multiple Cloudflare Worker deployments and routes requests based on availability and usage quotas.

**Success criteria:**
- [ ] Routes to correct account based on domain
- [ ] Respects daily quota limits per account
- [ ] Automatic failover when account exhausted
- [ ] < 50ms routing overhead
- [ ] Admin API for CRUD operations on accounts
- [ ] Usage dashboard showing per-account metrics

### Phase 3: WordPress Plugin

**What:** A lightweight WordPress plugin that integrates sites with the Third Audience infrastructure, handling auto-discovery and optional direct conversion.

**Success criteria:**
- [ ] One-click install from WordPress.org
- [ ] Zero configuration for basic functionality
- [ ] Settings page for advanced options
- [ ] Works with popular caching plugins
- [ ] < 1ms overhead on page load
- [ ] Passes WordPress.org review guidelines

### Phase 4: Dashboard

**What:** A web-based admin dashboard for managing sites, accounts, and viewing analytics.

**Success criteria:**
- [ ] Add/manage sites in < 2 minutes
- [ ] Real-time usage metrics
- [ ] Crawler identification (ClaudeBot, GPTBot, etc.)
- [ ] Export data as CSV
- [ ] Mobile-responsive design

---

## 6. Risk Assessment

### 6.1 Technical Risks

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Content extraction inaccuracy | Medium | High | Multiple extraction strategies, fallback to full-page |
| Cloudflare ToS changes | Low | Critical | Multi-vendor strategy, self-hosted fallback |
| Performance degradation at scale | Low | Medium | Extensive caching, load testing |
| Theme incompatibility | High | Medium | Community contributions, theme-specific rules |

### 6.2 Business Risks

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Low adoption | Medium | High | Marketing, WordPress.org presence, content marketing |
| Competitor emerges | Medium | Medium | First-mover advantage, community building |
| Sustainability without revenue | High | Medium | Plan monetization path, sponsorships |

---

## 7. Success Metrics

### 7.1 Product Metrics

| Metric | MVP Target | V1.0 Target | V2.0 Target |
|--------|------------|-------------|-------------|
| Sites using Third Audience | 10 | 100 | 1,000 |
| Daily markdown requests | 1,000 | 100,000 | 1,000,000 |
| Plugin active installs | - | 500 | 5,000 |
| GitHub stars | 50 | 500 | 2,000 |

### 7.2 Quality Metrics

| Metric | Target |
|--------|--------|
| Conversion accuracy | > 95% of pages produce valid markdown |
| Uptime | > 99.9% |
| P95 response time (cached) | < 100ms |
| P95 response time (uncached) | < 500ms |
| Error rate | < 0.1% |

---

## 8. Appendices

### 8.1 Reference Architecture

> **Architecture Decision:** Plugin-first approach. The WordPress plugin intercepts `.md` requests and orchestrates the conversion flow. This is more WordPress-native and requires no DNS changes.

```
+-------------------------------------------------------------------+
|                         AI Crawlers                                |
|              (ClaudeBot, GPTBot, PerplexityBot)                    |
+-------------------------------------------------------------------+
                                |
                                | GET /page.md or Accept: text/markdown
                                v
+-------------------------------------------------------------------+
|                     WordPress Site                                 |
|  +---------------------------------------------------------+      |
|  |              Third Audience Plugin                       |      |
|  |  +------------------+  +------------------+  +--------+  |      |
|  |  | URL Router       |  | Content Negot.   |  | <link> |  |      |
|  |  | (.md handling)   |  | (Accept header)  |  | tags   |  |      |
|  |  +------------------+  +------------------+  +--------+  |      |
|  |                           |                              |      |
|  |              +------------+------------+                 |      |
|  |              v                         v                 |      |
|  |  +------------------+      +------------------+          |      |
|  |  | Transient Cache  |      | API Client       |          |      |
|  |  | (Primary cache)  |      | (Router calls)   |          |      |
|  |  +------------------+      +------------------+          |      |
|  +---------------------------------------------------------+      |
+-------------------------------------------------------------------+
                                |
                                | On cache MISS, call Router
                                v
+-------------------------------------------------------------------+
|                     Third Audience Router                          |
|  +---------------+---------------+---------------------------+     |
|  | Load Balancer | Usage Tracker | Account Manager           |     |
|  +---------------+---------------+---------------------------+     |
+-------------------------------------------------------------------+
                                |
            +-------------------+-------------------+
            v                   v                   v
+-------------------+ +-------------------+ +-------------------+
| CF Account 1      | | CF Account 2      | | CF Account N      |
| +---------------+ | | +---------------+ | | +---------------+ |
| |    Worker     | | | |    Worker     | | | |    Worker     | |
| +---------------+ | | +---------------+ | | +---------------+ |
| |   KV Cache    | | | |   KV Cache    | | | |   KV Cache    | |
| +---------------+ | | +---------------+ | | +---------------+ |
+-------------------+ +-------------------+ +-------------------+
                                |
                                | Fetch origin HTML
                                v
+-------------------------------------------------------------------+
|                     WordPress Origin (HTML)                        |
+-------------------------------------------------------------------+
```

**Request Flow Summary:**
1. AI crawler requests `example.com/page.md`
2. WordPress plugin intercepts via rewrite rules
3. Plugin checks transient cache (primary cache layer)
4. On cache miss, plugin calls Router to get best worker
5. Worker fetches origin HTML and converts to markdown
6. Plugin caches result and returns to crawler

### 8.2 Example API Response

**Markdown conversion response:**
```http
GET /about-us.md HTTP/1.1
Host: example.com

HTTP/1.1 200 OK
Content-Type: text/markdown; charset=utf-8
Cache-Control: public, max-age=3600
X-Third-Audience-Cache: HIT

# About Us

Welcome to our company...
```

**Discovery link tag:**
```html
<link rel="alternate" type="text/markdown" href="https://example.com/about-us.md" title="Markdown version">
```

---

## Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | January 2026 | CTO | Initial documentation |

---

*This document is the strategic foundation for Third Audience. It should be updated as the product evolves and market conditions change.*
