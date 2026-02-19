/**
 * Third Audience - Client-side AI Citation Tracker
 *
 * Detects citation clicks from AI platforms via UTM parameters and referrers.
 * Works even when pages are served from cache.
 *
 * @package ThirdAudience
 * @since   3.3.7
 */

(function() {
	'use strict';

	// AI platform detection patterns
	var AI_PLATFORMS = {
		// UTM source patterns
		utm: {
			'chatgpt': { name: 'ChatGPT', color: '#10A37F' },
			'perplexity': { name: 'Perplexity', color: '#1FB6D0' },
			'claude': { name: 'Claude', color: '#D97757' },
			'gemini': { name: 'Gemini', color: '#4285F4' },
			'copilot': { name: 'Copilot', color: '#00BCF2' }
		},
		// Referrer domain patterns
		referrer: {
			'chat.openai.com': { name: 'ChatGPT', color: '#10A37F' },
			'chatgpt.com': { name: 'ChatGPT', color: '#10A37F' },
			'perplexity.ai': { name: 'Perplexity', color: '#1FB6D0', queryParam: 'q' },
			'claude.ai': { name: 'Claude', color: '#D97757' },
			'gemini.google.com': { name: 'Gemini', color: '#4285F4' },
			'copilot.microsoft.com': { name: 'Copilot', color: '#00BCF2' },
			'you.com': { name: 'You.com', color: '#8B5CF6', queryParam: 'q' },
			'www.bing.com': { name: 'Bing AI', color: '#008373' },
			'bing.com': { name: 'Bing AI', color: '#008373' }
		}
	};

	// Session storage key to prevent duplicate tracking
	var TRACKED_KEY = 'ta_citation_tracked';

	/**
	 * Detect AI citation from UTM parameters
	 */
	function detectFromUTM() {
		var params = new URLSearchParams(window.location.search);
		var utmSource = params.get('utm_source');

		if (!utmSource) return null;

		utmSource = utmSource.toLowerCase();

		for (var pattern in AI_PLATFORMS.utm) {
			if (utmSource.indexOf(pattern) !== -1) {
				return {
					platform: AI_PLATFORMS.utm[pattern].name,
					method: 'utm_parameter',
					utmSource: utmSource,
					utmMedium: params.get('utm_medium') || null,
					utmCampaign: params.get('utm_campaign') || null
				};
			}
		}

		return null;
	}

	/**
	 * Detect AI citation from referrer
	 */
	function detectFromReferrer() {
		var referrer = document.referrer;
		if (!referrer) return null;

		var referrerUrl;
		try {
			referrerUrl = new URL(referrer);
		} catch (e) {
			return null;
		}

		var host = referrerUrl.hostname.toLowerCase();

		for (var domain in AI_PLATFORMS.referrer) {
			if (host.indexOf(domain) !== -1 || host === domain) {
				var config = AI_PLATFORMS.referrer[domain];
				var searchQuery = null;

				// Extract search query if available
				if (config.queryParam) {
					var refParams = new URLSearchParams(referrerUrl.search);
					searchQuery = refParams.get(config.queryParam);
				}

				return {
					platform: config.name,
					method: 'http_referrer',
					referrer: referrer,
					searchQuery: searchQuery
				};
			}
		}

		return null;
	}

	/**
	 * Track citation via AJAX
	 */
	function trackCitation(citationData) {
		// Check if already tracked this session (per platform, not per page)
		// This matches GA4 session behaviour: one session entry per AI platform per browser session.
		var trackingKey = TRACKED_KEY + '_' + citationData.platform.toLowerCase().replace(/\s+/g, '_');
		if (sessionStorage.getItem(trackingKey)) {
			return;
		}

		// Mark as tracked for this platform for the rest of this browser session
		sessionStorage.setItem(trackingKey, '1');

		// Prepare tracking data
		var data = {
			action: 'ta_track_citation_js',
			nonce: window.taCitationTracker ? window.taCitationTracker.nonce : '',
			platform: citationData.platform,
			method: citationData.method,
			url: window.location.href,
			path: window.location.pathname,
			referrer: citationData.referrer || document.referrer || '',
			search_query: citationData.searchQuery || '',
			utm_source: citationData.utmSource || '',
			utm_medium: citationData.utmMedium || '',
			utm_campaign: citationData.utmCampaign || '',
			page_title: document.title,
			client_user_agent: navigator.userAgent || '',
			request_type: 'html_page'
		};

		// Get AJAX URL
		var ajaxUrl = window.taCitationTracker ? window.taCitationTracker.ajaxUrl : '/wp-admin/admin-ajax.php';

		// Send tracking request (fire and forget)
		var xhr = new XMLHttpRequest();
		xhr.open('POST', ajaxUrl, true);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

		// Build query string
		var params = [];
		for (var key in data) {
			if (data[key] !== null && data[key] !== '') {
				params.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
			}
		}

		xhr.send(params.join('&'));

		// Log for debugging (remove in production)
		if (window.console && window.taCitationTracker && window.taCitationTracker.debug) {
			console.log('[Third Audience] Citation tracked:', citationData.platform, 'via', citationData.method);
		}
	}

	/**
	 * Initialize citation tracking
	 */
	function init() {
		// Don't track in admin
		if (document.body.classList.contains('wp-admin')) {
			return;
		}

		// Try UTM detection first (higher priority)
		var citation = detectFromUTM();

		// Fall back to referrer detection
		if (!citation) {
			citation = detectFromReferrer();
		}

		// Track if citation detected
		if (citation) {
			trackCitation(citation);
		}
	}

	// Run on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

})();
