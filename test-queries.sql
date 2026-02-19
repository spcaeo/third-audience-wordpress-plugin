-- SQL Test Queries for Third Audience v3.5.0
-- Run these in phpMyAdmin or MySQL CLI to verify fixes

-- ============================================
-- TEST 1: Check if new columns exist
-- ============================================
SHOW COLUMNS FROM wp_ta_bot_analytics 
WHERE Field IN ('client_user_agent', 'http_status', 'request_type');

-- Expected: 3 rows showing these columns


-- ============================================
-- TEST 2: Recent citations with all new fields
-- ============================================
SELECT
    id,
    bot_name AS platform,
    -- Check User Agents
    CASE
        WHEN user_agent LIKE '%Headless%' THEN '🚨 Server: Headless'
        ELSE CONCAT('Server: ', SUBSTRING(user_agent, 1, 20), '...')
    END AS server_ua,
    CASE
        WHEN client_user_agent IS NULL THEN '⚠️  Missing'
        WHEN client_user_agent LIKE '%Chrome%' THEN '✅ Chrome'
        WHEN client_user_agent LIKE '%Safari%' THEN '✅ Safari'
        WHEN client_user_agent LIKE '%Edge%' THEN '✅ Edge'
        WHEN client_user_agent LIKE '%Firefox%' THEN '✅ Firefox'
        ELSE '✓ Other Browser'
    END AS client_ua_browser,
    -- Check HTTP Status
    CASE
        WHEN http_status IS NULL THEN '⚠️  NULL'
        WHEN http_status = 200 THEN '✅ 200 OK'
        WHEN http_status = 404 THEN '❌ 404 Not Found'
        ELSE CONCAT('ℹ️  ', http_status)
    END AS status,
    -- Check Request Type
    CASE
        WHEN request_type IS NULL THEN '⚠️  NULL'
        WHEN request_type = 'html_page' THEN '✅ HTML Page'
        WHEN request_type = 'rsc_prefetch' THEN '🔄 RSC Prefetch'
        WHEN request_type = 'js_fallback' THEN '📄 JS Fallback'
        ELSE request_type
    END AS req_type,
    url,
    visit_timestamp
FROM wp_ta_bot_analytics
WHERE traffic_type = 'citation_click'
ORDER BY visit_timestamp DESC
LIMIT 10;

-- Expected: All rows should have values, not NULL


-- ============================================
-- TEST 3: Statistics Summary
-- ============================================
SELECT
    COUNT(*) AS total_citations,
    SUM(CASE WHEN client_user_agent IS NOT NULL THEN 1 ELSE 0 END) AS has_client_ua,
    SUM(CASE WHEN http_status IS NOT NULL THEN 1 ELSE 0 END) AS has_http_status,
    SUM(CASE WHEN request_type IS NOT NULL THEN 1 ELSE 0 END) AS has_request_type,
    ROUND(SUM(CASE WHEN client_user_agent IS NOT NULL THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1) AS client_ua_percent,
    ROUND(SUM(CASE WHEN http_status IS NOT NULL THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1) AS http_status_percent,
    ROUND(SUM(CASE WHEN request_type IS NOT NULL THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1) AS request_type_percent
FROM wp_ta_bot_analytics
WHERE traffic_type = 'citation_click'
AND visit_timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY);

-- Expected: All percentages > 80%


-- ============================================
-- TEST 4: Request Type Breakdown
-- ============================================
SELECT
    request_type,
    COUNT(*) AS count,
    ROUND(COUNT(*) * 100.0 / (
        SELECT COUNT(*) 
        FROM wp_ta_bot_analytics 
        WHERE traffic_type = 'citation_click'
    ), 1) AS percentage
FROM wp_ta_bot_analytics
WHERE traffic_type = 'citation_click'
GROUP BY request_type
ORDER BY count DESC;

-- Expected: Mostly 'html_page' and 'js_fallback', very few 'rsc_prefetch'


-- ============================================
-- TEST 5: Find "Headless Frontend" issues
-- ============================================
SELECT
    id,
    bot_name,
    user_agent AS server_ua,
    client_user_agent,
    CASE
        WHEN user_agent LIKE '%Headless%' AND client_user_agent IS NULL THEN '❌ Problem: No Client UA'
        WHEN user_agent LIKE '%Headless%' AND client_user_agent IS NOT NULL THEN '✅ Fixed: Has Client UA'
        ELSE '✓ OK'
    END AS status,
    visit_timestamp
FROM wp_ta_bot_analytics
WHERE traffic_type = 'citation_click'
AND user_agent LIKE '%Headless%'
ORDER BY visit_timestamp DESC
LIMIT 10;

-- Expected: Status should be '✅ Fixed: Has Client UA'


-- ============================================
-- TEST 6: Browser Detection from Client UA
-- ============================================
SELECT
    CASE
        WHEN client_user_agent LIKE '%Chrome/%' AND client_user_agent NOT LIKE '%Edge/%' THEN 'Chrome'
        WHEN client_user_agent LIKE '%Edge/%' THEN 'Edge'
        WHEN client_user_agent LIKE '%Safari/%' AND client_user_agent NOT LIKE '%Chrome/%' THEN 'Safari'
        WHEN client_user_agent LIKE '%Firefox/%' THEN 'Firefox'
        WHEN client_user_agent IS NULL THEN 'NULL'
        ELSE 'Other'
    END AS browser,
    COUNT(*) AS count
FROM wp_ta_bot_analytics
WHERE traffic_type = 'citation_click'
GROUP BY browser
ORDER BY count DESC;

-- Expected: Real browser names (Chrome, Safari, Edge, Firefox) instead of NULL/Unknown


-- ============================================
-- TEST 7: HTTP Status Code Distribution
-- ============================================
SELECT
    http_status,
    COUNT(*) AS count,
    CASE
        WHEN http_status = 200 THEN '✅ Success'
        WHEN http_status = 404 THEN '❌ Broken Link'
        WHEN http_status = 500 THEN '🔥 Server Error'
        WHEN http_status IS NULL THEN '⚠️  Not Captured'
        ELSE CONCAT('ℹ️  Status ', http_status)
    END AS description
FROM wp_ta_bot_analytics
WHERE traffic_type = 'citation_click'
GROUP BY http_status
ORDER BY count DESC;

-- Expected: Mostly 200, some 404s if broken links exist, very few NULLs
