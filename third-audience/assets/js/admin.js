/**
 * Third Audience Admin JavaScript - Enterprise Edition
 *
 * Handles AJAX operations, UI interactions, caching, and debouncing
 * for the admin settings page.
 *
 * @package ThirdAudience
 * @since   1.2.0
 */

/* global jQuery, taAdmin */

(function($) {
	'use strict';

	/**
	 * Simple client-side AJAX response cache.
	 */
	var AjaxCache = {
		cache: {},
		ttl: 60000, // 1 minute default TTL

		/**
		 * Get cached response.
		 *
		 * @param {string} key Cache key.
		 * @return {*} Cached value or null.
		 */
		get: function(key) {
			var entry = this.cache[key];
			if (entry && (Date.now() - entry.timestamp) < this.ttl) {
				return entry.value;
			}
			if (entry) {
				delete this.cache[key];
			}
			return null;
		},

		/**
		 * Set cached response.
		 *
		 * @param {string} key   Cache key.
		 * @param {*}      value Value to cache.
		 * @param {number} ttl   Optional TTL in ms.
		 */
		set: function(key, value, ttl) {
			this.cache[key] = {
				value: value,
				timestamp: Date.now(),
				ttl: ttl || this.ttl
			};
		},

		/**
		 * Clear cache.
		 *
		 * @param {string} key Optional specific key to clear.
		 */
		clear: function(key) {
			if (key) {
				delete this.cache[key];
			} else {
				this.cache = {};
			}
		}
	};

	/**
	 * Debounce utility.
	 *
	 * @param {Function} func      Function to debounce.
	 * @param {number}   wait      Delay in milliseconds.
	 * @param {boolean}  immediate Execute on leading edge.
	 * @return {Function} Debounced function.
	 */
	function debounce(func, wait, immediate) {
		var timeout;
		return function() {
			var context = this;
			var args = arguments;
			var later = function() {
				timeout = null;
				if (!immediate) {
					func.apply(context, args);
				}
			};
			var callNow = immediate && !timeout;
			clearTimeout(timeout);
			timeout = setTimeout(later, wait);
			if (callNow) {
				func.apply(context, args);
			}
		};
	}

	/**
	 * Throttle utility.
	 *
	 * @param {Function} func  Function to throttle.
	 * @param {number}   limit Time limit in milliseconds.
	 * @return {Function} Throttled function.
	 */
	function throttle(func, limit) {
		var lastFunc;
		var lastRan;
		return function() {
			var context = this;
			var args = arguments;
			if (!lastRan) {
				func.apply(context, args);
				lastRan = Date.now();
			} else {
				clearTimeout(lastFunc);
				lastFunc = setTimeout(function() {
					if ((Date.now() - lastRan) >= limit) {
						func.apply(context, args);
						lastRan = Date.now();
					}
				}, limit - (Date.now() - lastRan));
			}
		};
	}

	/**
	 * Third Audience Admin module.
	 */
	var TAAdmin = {

		/**
		 * Pending save operations.
		 */
		pendingSaves: {},

		/**
		 * Request queue for batch operations.
		 */
		requestQueue: [],

		/**
		 * Processing flag.
		 */
		isProcessing: false,

		/**
		 * Initialize the module.
		 */
		init: function() {
			this.bindEvents();
			this.setupPasswordToggle();
			this.setupAutoSave();
			this.setupHealthStatus();
			this.setupBulkOperations();
		},

		/**
		 * Bind event handlers.
		 */
		bindEvents: function() {
			// Clear cache confirmation
			$('#ta-clear-cache-btn').on('click', this.confirmClearCache.bind(this));

			// Clear errors confirmation
			$('#ta-clear-errors-btn').on('click', this.confirmClearErrors.bind(this));

			// AJAX test connection
			$('#ta-test-connection-btn').on('click', this.testConnectionAjax.bind(this));
			$('#ta-test-connection-ajax').on('click', this.testConnectionAjax.bind(this));

			// AJAX test SMTP
			$('#ta-test-smtp-btn').on('click', this.testSmtpAjax.bind(this));
			$('#ta-test-smtp-ajax').on('click', this.testSmtpAjax.bind(this));

			// AJAX clear cache
			$('#ta-clear-cache-ajax').on('click', this.clearCacheAjax.bind(this));

			// Settings form changes
			$('.ta-settings-main form').on('change', 'input, select, textarea', debounce(this.onSettingChange.bind(this), 500));

			// Bulk operations
			$('#ta-warm-cache-btn').on('click', this.warmCache.bind(this));
			$('#ta-run-diagnostics-btn').on('click', this.runDiagnostics.bind(this));
		},

		/**
		 * Setup password field toggle visibility.
		 */
		setupPasswordToggle: function() {
			$('input[type="password"]').each(function() {
				var $input = $(this);

				// Skip if already wrapped
				if ($input.parent().hasClass('ta-password-field')) {
					return;
				}

				var $wrapper = $('<div class="ta-password-field"></div>');
				var showLabel = taAdmin.i18n && taAdmin.i18n.showPassword ? taAdmin.i18n.showPassword : 'Show password';
				var $toggle = $('<button type="button" class="ta-password-toggle" aria-label="' + showLabel + '">' +
					'<span class="dashicons dashicons-visibility"></span></button>');

				$input.wrap($wrapper);
				$input.after($toggle);

				$toggle.on('click', function(e) {
					e.preventDefault();
					if ($input.attr('type') === 'password') {
						$input.attr('type', 'text');
						$toggle.find('.dashicons').removeClass('dashicons-visibility').addClass('dashicons-hidden');
					} else {
						$input.attr('type', 'password');
						$toggle.find('.dashicons').removeClass('dashicons-hidden').addClass('dashicons-visibility');
					}
				});
			});
		},

		/**
		 * Setup auto-save functionality with debouncing.
		 */
		setupAutoSave: function() {
			// Mark form as dirty on change
			$('.ta-settings-main form').on('change', 'input, select, textarea', function() {
				$(this).closest('form').addClass('ta-form-dirty');
			});

			// Warn before leaving with unsaved changes
			$(window).on('beforeunload', function() {
				if ($('.ta-form-dirty').length > 0) {
					return taAdmin.i18n && taAdmin.i18n.unsavedChanges ?
						taAdmin.i18n.unsavedChanges :
						'You have unsaved changes. Are you sure you want to leave?';
				}
			});

			// Clear dirty flag on submit
			$('.ta-settings-main form').on('submit', function() {
				$(this).removeClass('ta-form-dirty');
			});
		},

		/**
		 * Setup health status indicator.
		 */
		setupHealthStatus: function() {
			// Check health status periodically
			this.checkHealth();
			setInterval(this.checkHealth.bind(this), 60000); // Every minute
		},

		/**
		 * Setup bulk operations.
		 */
		setupBulkOperations: function() {
			// Add bulk operation buttons if on appropriate tab
			if ($('.ta-tab-general').length > 0) {
				this.addBulkOperationButtons();
			}
		},

		/**
		 * Add bulk operation buttons to the page.
		 */
		addBulkOperationButtons: function() {
			var $container = $('<div class="ta-bulk-operations ta-card">' +
				'<h2>' + (taAdmin.i18n && taAdmin.i18n.bulkOperations || 'Bulk Operations') + '</h2>' +
				'<p>' + (taAdmin.i18n && taAdmin.i18n.bulkOperationsDesc || 'Perform batch operations on cache and content.') + '</p>' +
				'<div class="ta-button-group">' +
				'<button type="button" id="ta-warm-cache-btn" class="button button-secondary">' +
				(taAdmin.i18n && taAdmin.i18n.warmCache || 'Warm Cache') +
				'</button>' +
				'<button type="button" id="ta-run-diagnostics-btn" class="button button-secondary">' +
				(taAdmin.i18n && taAdmin.i18n.runDiagnostics || 'Run Diagnostics') +
				'</button>' +
				'</div>' +
				'</div>');

			$('.ta-settings-sidebar').append($container);
		},

		/**
		 * Handle setting change with debounced validation.
		 *
		 * @param {Event} e Change event.
		 */
		onSettingChange: function(e) {
			var $input = $(e.target);
			var name = $input.attr('name');

			if (!name) {
				return;
			}

			// Validate URL fields
			if ($input.attr('type') === 'url') {
				this.validateUrl($input);
			}

			// Validate email fields
			if ($input.attr('type') === 'email' || name.indexOf('email') !== -1) {
				this.validateEmail($input);
			}
		},

		/**
		 * Validate URL field.
		 *
		 * @param {jQuery} $input The input element.
		 */
		validateUrl: function($input) {
			var value = $input.val();
			var $feedback = $input.next('.ta-field-feedback');

			if (!$feedback.length) {
				$feedback = $('<span class="ta-field-feedback"></span>');
				$input.after($feedback);
			}

			if (value && !this.isValidUrl(value)) {
				$input.addClass('ta-field-error');
				$feedback.text(taAdmin.i18n && taAdmin.i18n.invalidUrl || 'Please enter a valid URL').addClass('ta-feedback-error');
			} else {
				$input.removeClass('ta-field-error');
				$feedback.text('').removeClass('ta-feedback-error');
			}
		},

		/**
		 * Validate email field.
		 *
		 * @param {jQuery} $input The input element.
		 */
		validateEmail: function($input) {
			var value = $input.val();
			var $feedback = $input.next('.ta-field-feedback');

			if (!$feedback.length) {
				$feedback = $('<span class="ta-field-feedback"></span>');
				$input.after($feedback);
			}

			// Handle comma-separated emails
			var emails = value.split(',').map(function(e) { return e.trim(); });
			var invalid = emails.filter(function(e) { return e && !TAAdmin.isValidEmail(e); });

			if (invalid.length > 0) {
				$input.addClass('ta-field-error');
				$feedback.text(taAdmin.i18n && taAdmin.i18n.invalidEmail || 'Please enter valid email addresses').addClass('ta-feedback-error');
			} else {
				$input.removeClass('ta-field-error');
				$feedback.text('').removeClass('ta-feedback-error');
			}
		},

		/**
		 * Check if URL is valid.
		 *
		 * @param {string} url The URL to check.
		 * @return {boolean} Whether the URL is valid.
		 */
		isValidUrl: function(url) {
			try {
				new URL(url);
				return true;
			} catch (e) {
				return false;
			}
		},

		/**
		 * Check if email is valid.
		 *
		 * @param {string} email The email to check.
		 * @return {boolean} Whether the email is valid.
		 */
		isValidEmail: function(email) {
			return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
		},

		/**
		 * Check health status.
		 */
		checkHealth: throttle(function() {
			// Use cache if available
			var cached = AjaxCache.get('health_status');
			if (cached) {
				this.updateHealthIndicator(cached);
				return;
			}

			$.ajax({
				url: taAdmin.restUrl + 'third-audience/v1/health',
				type: 'GET',
				beforeSend: function(xhr) {
					xhr.setRequestHeader('X-WP-Nonce', taAdmin.restNonce);
				},
				success: function(response) {
					AjaxCache.set('health_status', response, 30000); // Cache for 30 seconds
					TAAdmin.updateHealthIndicator(response);
				}
			});
		}, 30000),

		/**
		 * Update health indicator in the UI.
		 *
		 * @param {Object} health Health status data.
		 */
		updateHealthIndicator: function(health) {
			var $indicator = $('.ta-health-indicator');

			if (!$indicator.length) {
				$indicator = $('<div class="ta-health-indicator"></div>');
				$('.ta-settings-wrap h1').append($indicator);
			}

			var statusClass = 'ta-health-' + (health.status || 'unknown');
			var statusText = health.status ? health.status.charAt(0).toUpperCase() + health.status.slice(1) : 'Unknown';

			$indicator.attr('class', 'ta-health-indicator ' + statusClass)
				.attr('title', statusText)
				.text(statusText);
		},

		/**
		 * Confirm before clearing cache.
		 *
		 * @param {Event} e Click event.
		 * @return {boolean} Whether to proceed.
		 */
		confirmClearCache: function(e) {
			var confirmMsg = taAdmin.i18n && taAdmin.i18n.confirmClear ?
				taAdmin.i18n.confirmClear :
				'Are you sure you want to clear all cached items?';

			if (!confirm(confirmMsg)) {
				e.preventDefault();
				return false;
			}
			$(e.target).addClass('ta-btn-loading').prop('disabled', true);
			return true;
		},

		/**
		 * Confirm before clearing errors.
		 *
		 * @param {Event} e Click event.
		 * @return {boolean} Whether to proceed.
		 */
		confirmClearErrors: function(e) {
			var confirmMsg = taAdmin.i18n && taAdmin.i18n.confirmClearErrors ?
				taAdmin.i18n.confirmClearErrors :
				'Are you sure you want to clear all error logs?';

			if (!confirm(confirmMsg)) {
				e.preventDefault();
				return false;
			}
			$(e.target).addClass('ta-btn-loading').prop('disabled', true);
			return true;
		},

		/**
		 * Test connection via AJAX with caching.
		 *
		 * @param {Event} e Click event.
		 */
		testConnectionAjax: function(e) {
			e.preventDefault();

			var $btn = $(e.target);
			var originalText = $btn.text();

			$btn.addClass('ta-btn-loading').prop('disabled', true);

			$.ajax({
				url: taAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'ta_test_connection',
					nonce: taAdmin.nonce
				},
				success: function(response) {
					if (response.success) {
						TAAdmin.showToast(response.data.message, 'success');
						// Cache the result briefly
						AjaxCache.set('connection_test', response.data, 10000);
					} else {
						TAAdmin.showToast(response.data.message || (taAdmin.i18n && taAdmin.i18n.error) || 'Error', 'error');
					}
				},
				error: function() {
					TAAdmin.showToast((taAdmin.i18n && taAdmin.i18n.error) || 'Error', 'error');
				},
				complete: function() {
					$btn.removeClass('ta-btn-loading').prop('disabled', false).text(originalText);
				}
			});
		},

		/**
		 * Test SMTP via AJAX.
		 *
		 * @param {Event} e Click event.
		 */
		testSmtpAjax: function(e) {
			e.preventDefault();

			var $btn = $(e.target);
			var originalText = $btn.text();

			$btn.addClass('ta-btn-loading').prop('disabled', true);

			$.ajax({
				url: taAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'ta_test_smtp',
					nonce: taAdmin.nonce
				},
				success: function(response) {
					if (response.success) {
						TAAdmin.showToast(response.data.message, 'success');
					} else {
						TAAdmin.showToast(response.data.message || (taAdmin.i18n && taAdmin.i18n.error) || 'Error', 'error');
					}
				},
				error: function() {
					TAAdmin.showToast((taAdmin.i18n && taAdmin.i18n.error) || 'Error', 'error');
				},
				complete: function() {
					$btn.removeClass('ta-btn-loading').prop('disabled', false).text(originalText);
				}
			});
		},

		/**
		 * Clear cache via AJAX.
		 *
		 * @param {Event} e Click event.
		 */
		clearCacheAjax: function(e) {
			e.preventDefault();

			var confirmMsg = taAdmin.i18n && taAdmin.i18n.confirmClear ?
				taAdmin.i18n.confirmClear :
				'Are you sure you want to clear all cached items?';

			if (!confirm(confirmMsg)) {
				return;
			}

			var $btn = $(e.target);
			var originalText = $btn.text();

			$btn.addClass('ta-btn-loading').prop('disabled', true);

			$.ajax({
				url: taAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'ta_clear_cache',
					nonce: taAdmin.nonce
				},
				success: function(response) {
					if (response.success) {
						TAAdmin.showToast(response.data.message, 'success');
						// Update cache stats on page
						$('.ta-stat-value').first().text(0);
						$('.ta-stat-value').eq(1).text('0 B');
						// Clear local cache
						AjaxCache.clear();
					} else {
						TAAdmin.showToast(response.data.message || (taAdmin.i18n && taAdmin.i18n.error) || 'Error', 'error');
					}
				},
				error: function() {
					TAAdmin.showToast((taAdmin.i18n && taAdmin.i18n.error) || 'Error', 'error');
				},
				complete: function() {
					$btn.removeClass('ta-btn-loading').prop('disabled', false).text(originalText);
				}
			});
		},

		/**
		 * Warm cache for popular posts.
		 *
		 * @param {Event} e Click event.
		 */
		warmCache: function(e) {
			e.preventDefault();

			var $btn = $(e.target);
			var originalText = $btn.text();

			$btn.addClass('ta-btn-loading').prop('disabled', true).text(
				taAdmin.i18n && taAdmin.i18n.warming || 'Warming...'
			);

			$.ajax({
				url: taAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'ta_warm_cache',
					nonce: taAdmin.nonce,
					limit: 10
				},
				success: function(response) {
					if (response.success) {
						TAAdmin.showToast(response.data.message, 'success');
						// Refresh cache stats
						TAAdmin.refreshCacheStats();
					} else {
						TAAdmin.showToast(response.data.message || (taAdmin.i18n && taAdmin.i18n.error) || 'Error', 'error');
					}
				},
				error: function() {
					TAAdmin.showToast((taAdmin.i18n && taAdmin.i18n.error) || 'Error', 'error');
				},
				complete: function() {
					$btn.removeClass('ta-btn-loading').prop('disabled', false).text(originalText);
				}
			});
		},

		/**
		 * Run diagnostics.
		 *
		 * @param {Event} e Click event.
		 */
		runDiagnostics: function(e) {
			e.preventDefault();

			var $btn = $(e.target);
			var originalText = $btn.text();

			$btn.addClass('ta-btn-loading').prop('disabled', true).text(
				taAdmin.i18n && taAdmin.i18n.running || 'Running...'
			);

			$.ajax({
				url: taAdmin.restUrl + 'third-audience/v1/diagnostics',
				type: 'GET',
				beforeSend: function(xhr) {
					xhr.setRequestHeader('X-WP-Nonce', taAdmin.restNonce);
				},
				success: function(response) {
					TAAdmin.showDiagnosticsModal(response);
				},
				error: function() {
					TAAdmin.showToast((taAdmin.i18n && taAdmin.i18n.error) || 'Error', 'error');
				},
				complete: function() {
					$btn.removeClass('ta-btn-loading').prop('disabled', false).text(originalText);
				}
			});
		},

		/**
		 * Show diagnostics in a modal.
		 *
		 * @param {Object} diagnostics Diagnostics data.
		 */
		showDiagnosticsModal: function(diagnostics) {
			// Remove existing modal
			$('.ta-modal').remove();

			var content = '<pre style="max-height: 400px; overflow: auto;">' +
				JSON.stringify(diagnostics, null, 2) + '</pre>';

			var $modal = $('<div class="ta-modal">' +
				'<div class="ta-modal-content">' +
				'<div class="ta-modal-header">' +
				'<h3>' + (taAdmin.i18n && taAdmin.i18n.diagnosticsTitle || 'System Diagnostics') + '</h3>' +
				'<button type="button" class="ta-modal-close">&times;</button>' +
				'</div>' +
				'<div class="ta-modal-body">' + content + '</div>' +
				'<div class="ta-modal-footer">' +
				'<button type="button" class="button button-secondary ta-modal-copy">' +
				(taAdmin.i18n && taAdmin.i18n.copyToClipboard || 'Copy to Clipboard') +
				'</button>' +
				'<button type="button" class="button button-primary ta-modal-close">' +
				(taAdmin.i18n && taAdmin.i18n.close || 'Close') +
				'</button>' +
				'</div>' +
				'</div>' +
				'</div>');

			$('body').append($modal);

			// Close handlers
			$modal.on('click', '.ta-modal-close', function() {
				$modal.remove();
			});

			$modal.on('click', function(e) {
				if ($(e.target).hasClass('ta-modal')) {
					$modal.remove();
				}
			});

			// Copy handler
			$modal.on('click', '.ta-modal-copy', function() {
				var text = JSON.stringify(diagnostics, null, 2);
				navigator.clipboard.writeText(text).then(function() {
					TAAdmin.showToast(taAdmin.i18n && taAdmin.i18n.copied || 'Copied!', 'success');
				});
			});
		},

		/**
		 * Refresh cache stats.
		 */
		refreshCacheStats: function() {
			$.ajax({
				url: taAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'ta_get_cache_stats',
					nonce: taAdmin.nonce
				},
				success: function(response) {
					if (response.success && response.data) {
						$('.ta-stat-value').first().text(response.data.count || 0);
						$('.ta-stat-value').eq(1).text(response.data.size_human || '0 B');
					}
				}
			});
		},

		/**
		 * Show a toast notification.
		 *
		 * @param {string} message The message to show.
		 * @param {string} type    The type (success, error, info).
		 */
		showToast: function(message, type) {
			type = type || 'info';

			// Remove existing toasts
			$('.ta-toast').remove();

			var icon = type === 'success' ? 'yes' :
				type === 'error' ? 'no' : 'info';

			var $toast = $('<div class="ta-toast ta-toast-' + type + '">' +
				'<span class="dashicons dashicons-' + icon + '"></span>' +
				'<span class="ta-toast-message">' + message + '</span>' +
				'</div>');

			$('body').append($toast);

			// Trigger reflow for animation
			$toast[0].offsetHeight;
			$toast.addClass('ta-toast-visible');

			// Auto-hide after 4 seconds
			setTimeout(function() {
				$toast.removeClass('ta-toast-visible');
				setTimeout(function() {
					$toast.remove();
				}, 300);
			}, 4000);
		},

		/**
		 * Refresh error list via AJAX.
		 */
		refreshErrors: function() {
			$.ajax({
				url: taAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'ta_get_recent_errors',
					nonce: taAdmin.nonce,
					limit: 10
				},
				success: function(response) {
					if (response.success) {
						// Update error count
						if (response.data.stats) {
							$('.ta-stat-value').first().text(response.data.stats.errors_today || 0);
							$('.ta-stat-value').eq(1).text(response.data.stats.total_errors || 0);
						}
					}
				}
			});
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		TAAdmin.init();
	});

	// Expose to global scope for external access
	window.TAAdmin = TAAdmin;
	window.TAAdminCache = AjaxCache;

})(jQuery);
