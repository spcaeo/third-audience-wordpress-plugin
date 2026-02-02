/**
 * Third Audience - Auto-Endpoint Detector
 *
 * This script automatically detects whether to use:
 * 1. REST API endpoint
 * 2. AJAX fallback endpoint
 *
 * Include this in your headless frontend for automatic endpoint detection
 * and citation tracking that works regardless of server configuration.
 *
 * @package ThirdAudience
 * @since   3.4.0
 */

class ThirdAudienceTracker {
    /**
     * Initialize tracker
     *
     * @param {string} wordpressUrl - WordPress site URL
     * @param {string} apiKey - Third Audience API key
     */
    constructor(wordpressUrl, apiKey) {
        this.wordpressUrl = wordpressUrl.replace(/\/$/, '');
        this.apiKey = apiKey;
        this.endpoint = null;
        this.endpointType = null;
        this.debug = false;
    }

    /**
     * Enable debug logging
     */
    enableDebug() {
        this.debug = true;
    }

    /**
     * Log debug message
     */
    log(message, ...args) {
        if (this.debug) {
            console.log(`[Third Audience] ${message}`, ...args);
        }
    }

    /**
     * Auto-detect best endpoint
     *
     * @returns {Promise<boolean>} True if endpoint found
     */
    async detectEndpoint() {
        this.log('Detecting best endpoint...');

        // Try REST API first
        const restEndpoint = `${this.wordpressUrl}/wp-json/third-audience/v1/health`;

        try {
            this.log('Testing REST API:', restEndpoint);

            const response = await fetch(restEndpoint, {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' },
                // Don't send credentials for health check
                credentials: 'omit'
            });

            if (response.ok) {
                const data = await response.json();
                this.log('REST API response:', data);

                // REST API works!
                this.endpoint = `${this.wordpressUrl}/wp-json/third-audience/v1/track-citation`;
                this.endpointType = 'rest';
                this.log('✓ Using REST API endpoint');
                return true;
            } else {
                this.log('REST API returned status:', response.status);
            }
        } catch (error) {
            this.log('REST API failed:', error.message);
        }

        // Fallback to admin-ajax.php
        this.log('Falling back to AJAX endpoint...');
        this.endpoint = `${this.wordpressUrl}/wp-admin/admin-ajax.php`;
        this.endpointType = 'ajax';
        this.log('✓ Using AJAX fallback endpoint');

        return true;
    }

    /**
     * Track citation (auto-selects endpoint)
     *
     * @param {Object} data - Citation data
     * @param {string} data.url - Page URL
     * @param {string} data.platform - AI platform name (ChatGPT, Perplexity, etc)
     * @param {string} [data.referer] - Referrer URL
     * @param {string} [data.searchQuery] - Search query from AI platform
     * @returns {Promise<Object>} API response
     */
    async trackCitation(data) {
        // Auto-detect endpoint if not already detected
        if (!this.endpoint) {
            await this.detectEndpoint();
        }

        const payload = {
            url: data.url,
            platform: data.platform,
            referer: data.referer || document.referrer || '',
            search_query: data.searchQuery || '',
        };

        this.log('Tracking citation:', payload);

        if (this.endpointType === 'rest') {
            // REST API call
            return this.trackViaREST(payload);
        } else {
            // AJAX fallback
            return this.trackViaAJAX(payload);
        }
    }

    /**
     * Track via REST API
     *
     * @private
     * @param {Object} payload - Citation data
     * @returns {Promise<Object>} API response
     */
    async trackViaREST(payload) {
        try {
            const response = await fetch(this.endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-TA-Api-Key': this.apiKey
                },
                body: JSON.stringify(payload),
                credentials: 'omit'
            });

            const data = await response.json();

            if (!response.ok) {
                this.log('REST API error:', data);
                throw new Error(data.message || 'API request failed');
            }

            this.log('Citation tracked successfully (REST):', data);
            return data;

        } catch (error) {
            this.log('REST API tracking failed:', error);

            // If REST fails, try AJAX fallback
            this.log('Attempting AJAX fallback...');
            this.endpoint = `${this.wordpressUrl}/wp-admin/admin-ajax.php`;
            this.endpointType = 'ajax';

            return this.trackViaAJAX(payload);
        }
    }

    /**
     * Track via AJAX fallback
     *
     * @private
     * @param {Object} payload - Citation data
     * @returns {Promise<Object>} API response
     */
    async trackViaAJAX(payload) {
        const formData = new FormData();
        formData.append('action', 'ta_track_citation');
        formData.append('api_key', this.apiKey);
        formData.append('url', payload.url);
        formData.append('platform', payload.platform);
        formData.append('referer', payload.referer);
        formData.append('search_query', payload.search_query);

        try {
            const response = await fetch(this.endpoint, {
                method: 'POST',
                body: formData,
                credentials: 'omit'
            });

            const data = await response.json();

            if (!data.success) {
                this.log('AJAX error:', data);
                throw new Error(data.data?.message || 'AJAX request failed');
            }

            this.log('Citation tracked successfully (AJAX):', data);
            return data.data;

        } catch (error) {
            this.log('AJAX tracking failed:', error);
            throw error;
        }
    }

    /**
     * Get current endpoint info
     *
     * @returns {Object} Endpoint information
     */
    getEndpointInfo() {
        return {
            endpoint: this.endpoint,
            type: this.endpointType,
            wordpressUrl: this.wordpressUrl
        };
    }

    /**
     * Test connection to WordPress
     *
     * @returns {Promise<Object>} Connection test result
     */
    async testConnection() {
        const results = {
            rest: { available: false, error: null },
            ajax: { available: false, error: null }
        };

        // Test REST API
        try {
            const restEndpoint = `${this.wordpressUrl}/wp-json/third-audience/v1/health`;
            const response = await fetch(restEndpoint, {
                method: 'GET',
                credentials: 'omit'
            });

            if (response.ok) {
                results.rest.available = true;
                results.rest.data = await response.json();
            } else {
                results.rest.error = `HTTP ${response.status}`;
            }
        } catch (error) {
            results.rest.error = error.message;
        }

        // Test AJAX
        try {
            const ajaxEndpoint = `${this.wordpressUrl}/wp-admin/admin-ajax.php`;
            const formData = new FormData();
            formData.append('action', 'ta_health_check');

            const response = await fetch(ajaxEndpoint, {
                method: 'POST',
                body: formData,
                credentials: 'omit'
            });

            const data = await response.json();
            if (data.success) {
                results.ajax.available = true;
                results.ajax.data = data.data;
            } else {
                results.ajax.error = data.data?.message || 'Unknown error';
            }
        } catch (error) {
            results.ajax.error = error.message;
        }

        this.log('Connection test results:', results);
        return results;
    }
}

// Export for use in different module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThirdAudienceTracker;
}

if (typeof define === 'function' && define.amd) {
    define([], function() {
        return ThirdAudienceTracker;
    });
}

// Global export
if (typeof window !== 'undefined') {
    window.ThirdAudienceTracker = ThirdAudienceTracker;
}
