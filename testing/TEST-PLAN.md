# Third Audience - Complete Test Plan 📋

## Test Objectives

1. ✅ Verify Docker WordPress environment setup
2. ✅ Confirm plugin installation and activation
3. ✅ Test bot detection and tracking
4. ✅ Validate analytics data accuracy
5. ✅ Verify dashboard UI/UX functionality
6. ✅ Test performance and caching
7. ✅ Validate export functionality

---

## Test Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    START TESTING                             │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
         ┌──────────────────────────────┐
         │  1. Docker Environment       │
         │  • Start containers          │
         │  • Verify health checks      │
         │  • Check port availability   │
         └──────────┬───────────────────┘
                    │
                    ▼
         ┌──────────────────────────────┐
         │  2. WordPress Setup          │
         │  • Install WordPress         │
         │  • Activate plugin           │
         │  • Configure settings        │
         └──────────┬───────────────────┘
                    │
                    ▼
         ┌──────────────────────────────┐
         │  3. Content Generation       │
         │  • Create 5 test posts       │
         │  • Create 3 test pages       │
         │  • Flush rewrite rules       │
         └──────────┬───────────────────┘
                    │
                    ▼
         ┌──────────────────────────────┐
         │  4. Bot Crawler Simulation   │
         │  • SneakyBot (custom)        │
         │  • ClaudeBot                 │
         │  • GPTBot                    │
         │  • PerplexityBot             │
         │  • LazyBot, HungryBot        │
         └──────────┬───────────────────┘
                    │
                    ▼
         ┌──────────────────────────────┐
         │  5. Analytics Verification   │
         │  • Check database table      │
         │  • Verify visit counts       │
         │  • Validate tracking data    │
         └──────────┬───────────────────┘
                    │
                    ▼
         ┌──────────────────────────────┐
         │  6. Dashboard Testing        │
         │  • Access admin UI           │
         │  • Test filters              │
         │  • Verify charts             │
         │  • Test export               │
         └──────────┬───────────────────┘
                    │
                    ▼
         ┌──────────────────────────────┐
         │  7. Generate Report          │
         │  • Collect metrics           │
         │  • Save test results         │
         │  • Output summary            │
         └──────────┬───────────────────┘
                    │
                    ▼
         ┌──────────────────────────────┐
         │     TESTING COMPLETE         │
         └──────────────────────────────┘
```

---

## Detailed Test Cases

### Phase 1: Environment Setup
**Script**: `docker-compose up -d`

| Test ID | Test Case | Expected Result | Status |
|---------|-----------|----------------|--------|
| ENV-01 | Start WordPress container | Container running on port 8080 | ⏳ |
| ENV-02 | Start MySQL container | Database accessible | ⏳ |
| ENV-03 | Start phpMyAdmin | UI accessible on 8081 | ⏳ |
| ENV-04 | Start WP-CLI container | CLI commands available | ⏳ |
| ENV-05 | Health checks pass | All containers healthy | ⏳ |

### Phase 2: WordPress Installation
**Script**: `./testing/setup-wordpress.sh`

| Test ID | Test Case | Expected Result | Status |
|---------|-----------|----------------|--------|
| WP-01 | Install WordPress core | WordPress accessible | ⏳ |
| WP-02 | Create admin user | Login successful | ⏳ |
| WP-03 | Activate plugin | Plugin active | ⏳ |
| WP-04 | Configure plugin settings | Settings saved | ⏳ |
| WP-05 | Create test posts | 5 posts published | ⏳ |
| WP-06 | Create test pages | 3 pages published | ⏳ |
| WP-07 | Flush rewrite rules | .md URLs working | ⏳ |

### Phase 3: Bot Detection
**Script**: `./testing/bot-crawler.sh`

| Test ID | Test Case | Expected Result | Status |
|---------|-----------|----------------|--------|
| BOT-01 | Detect SneakyBot | Bot identified correctly | ⏳ |
| BOT-02 | Detect ClaudeBot | Bot identified correctly | ⏳ |
| BOT-03 | Detect GPTBot | Bot identified correctly | ⏳ |
| BOT-04 | Detect PerplexityBot | Bot identified correctly | ⏳ |
| BOT-05 | Detect custom bots | All bots identified | ⏳ |
| BOT-06 | Ignore regular browsers | No tracking for humans | ⏳ |

### Phase 4: Request Handling
**Script**: `./testing/bot-crawler.sh`

| Test ID | Test Case | Expected Result | Status |
|---------|-----------|----------------|--------|
| REQ-01 | .md URL request | Markdown returned | ⏳ |
| REQ-02 | Accept header request | 303 redirect to .md | ⏳ |
| REQ-03 | Post not found | 404 error | ⏳ |
| REQ-04 | Disabled post type | 404 error | ⏳ |
| REQ-05 | Cache hit | Cached content served | ⏳ |
| REQ-06 | Cache miss | Fresh conversion | ⏳ |

### Phase 5: Analytics Tracking
**Script**: `./testing/verify-analytics.sh`

| Test ID | Test Case | Expected Result | Status |
|---------|-----------|----------------|--------|
| ANA-01 | Database table exists | wp_ta_bot_analytics found | ⏳ |
| ANA-02 | Visit recorded | New row in table | ⏳ |
| ANA-03 | Bot type captured | Correct bot type saved | ⏳ |
| ANA-04 | URL captured | Full URL saved | ⏳ |
| ANA-05 | Post metadata saved | Title, type, ID recorded | ⏳ |
| ANA-06 | Request method saved | md_url or accept_header | ⏳ |
| ANA-07 | Cache status saved | HIT, MISS, or PRE_GENERATED | ⏳ |
| ANA-08 | Response time recorded | Milliseconds captured | ⏳ |
| ANA-09 | Response size recorded | Bytes captured | ⏳ |
| ANA-10 | IP address captured | Client IP saved | ⏳ |
| ANA-11 | Timestamp recorded | Accurate datetime | ⏳ |

### Phase 6: Dashboard UI
**Manual Testing Required**

| Test ID | Test Case | Expected Result | Status |
|---------|-----------|----------------|--------|
| UI-01 | Access analytics page | Page loads | ⏳ |
| UI-02 | Summary cards display | 4 cards with data | ⏳ |
| UI-03 | Visits chart renders | Line chart showing trends | ⏳ |
| UI-04 | Bot distribution chart | Doughnut chart with legend | ⏳ |
| UI-05 | Top pages table | Table with visit data | ⏳ |
| UI-06 | Recent visits table | Latest visits listed | ⏳ |
| UI-07 | Date filter works | Data filtered correctly | ⏳ |
| UI-08 | Bot type filter works | Filtered by bot | ⏳ |
| UI-09 | Post type filter works | Filtered by post type | ⏳ |
| UI-10 | Cache status filter works | Filtered by cache | ⏳ |
| UI-11 | Search works | Results match query | ⏳ |
| UI-12 | Reset filters works | All filters cleared | ⏳ |
| UI-13 | Period selector works | Chart updates | ⏳ |
| UI-14 | Pagination works | Next/prev page loads | ⏳ |
| UI-15 | Responsive design | Mobile-friendly | ⏳ |

### Phase 7: Export Functionality
**Manual Testing Required**

| Test ID | Test Case | Expected Result | Status |
|---------|-----------|----------------|--------|
| EXP-01 | Export CSV | File downloads | ⏳ |
| EXP-02 | CSV headers correct | All columns present | ⏳ |
| EXP-03 | CSV data accurate | Matches dashboard | ⏳ |
| EXP-04 | Filtered export works | Only filtered data | ⏳ |
| EXP-05 | Large export (10k rows) | No timeout | ⏳ |

### Phase 8: Performance
**Script**: `./testing/verify-analytics.sh`

| Test ID | Test Case | Expected Result | Status |
|---------|-----------|----------------|--------|
| PERF-01 | Cache hit rate >50% | After multiple visits | ⏳ |
| PERF-02 | Avg response time <200ms | With cache | ⏳ |
| PERF-03 | Dashboard load <2s | Initial page load | ⏳ |
| PERF-04 | Chart render <1s | After data load | ⏳ |
| PERF-05 | Filter apply <500ms | User interaction | ⏳ |

### Phase 9: Edge Cases
**Manual Testing Required**

| Test ID | Test Case | Expected Result | Status |
|---------|-----------|----------------|--------|
| EDGE-01 | 1000+ visits | No performance issues | ⏳ |
| EDGE-02 | Empty database | No errors shown | ⏳ |
| EDGE-03 | Invalid filter input | Sanitized correctly | ⏳ |
| EDGE-04 | SQL injection attempt | Blocked/escaped | ⏳ |
| EDGE-05 | XSS attempt | Escaped properly | ⏳ |
| EDGE-06 | Very long URL | Truncated if needed | ⏳ |
| EDGE-07 | Very long post title | Truncated if needed | ⏳ |
| EDGE-08 | Unknown bot | Not tracked | ⏳ |
| EDGE-09 | Concurrent requests | All tracked | ⏳ |
| EDGE-10 | Database connection lost | Error handled gracefully | ⏳ |

---

## Test Execution Log

### Run 1: [Date]
- **Tester**:
- **Environment**: Docker Desktop / Mac
- **WordPress Version**: 6.7
- **Plugin Version**: 1.4.0
- **Test Duration**:
- **Pass Rate**:
- **Notes**:

### Run 2: [Date]
- **Tester**:
- **Environment**:
- **Pass Rate**:
- **Notes**:

---

## Success Criteria

### Must Pass (Critical)
- ✅ All Docker containers start successfully
- ✅ WordPress installs without errors
- ✅ Plugin activates without errors
- ✅ Bot detection works for all known bots
- ✅ Analytics database table created
- ✅ Visits are tracked and stored
- ✅ Dashboard is accessible
- ✅ Charts render correctly

### Should Pass (Important)
- ✅ Cache hit rate >30% after multiple visits
- ✅ Average response time <300ms
- ✅ All filters work correctly
- ✅ CSV export includes all data
- ✅ No PHP errors or warnings
- ✅ No JavaScript console errors

### Nice to Have (Optional)
- ✅ Cache hit rate >70%
- ✅ Average response time <100ms
- ✅ Dashboard loads <1s
- ✅ Mobile-optimized UI
- ✅ Pagination smooth

---

## Post-Test Checklist

- [ ] All critical tests passed
- [ ] Test report generated
- [ ] Screenshots captured
- [ ] Issues documented
- [ ] Performance metrics recorded
- [ ] CSV export validated
- [ ] Database inspected
- [ ] Logs reviewed
- [ ] Cleanup performed
- [ ] Results shared

---

## Known Issues / Limitations

1. **First request is slow**: Initial cache miss takes longer (expected)
2. **Development environment**: Performance may differ in production
3. **Limited bot types**: Only 10 bot types currently supported
4. **No GeoIP**: Country code not populated (requires GeoIP database)

---

## Future Test Additions

- [ ] Load testing (ApacheBench / k6)
- [ ] Security scanning (WPScan)
- [ ] Accessibility testing (WAVE)
- [ ] Browser compatibility (Selenium)
- [ ] API endpoint testing
- [ ] Multisite testing
- [ ] Plugin conflict testing
- [ ] Theme compatibility testing

---

**Test Plan Version**: 1.0
**Last Updated**: 2026-01-21
**Next Review**: After significant code changes
