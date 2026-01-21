/**
 * Third Audience Admin JavaScript
 *
 * Handles AJAX operations and UI interactions for the admin settings page.
 *
 * @package ThirdAudience
 * @since   1.1.0
 */

/* global jQuery, taAdmin */

(function($) {
	'use strict';

	/**
	 * Third Audience Admin module.
	 */
	var TAAdmin = {

		/**
		 * Initialize the module.
		 */
		init: function() {
			this.bindEvents();
			this.setupPasswordToggle();
			this.setupHomepagePatternToggle();
		},

		/**
		 * Bind event handlers.
		 */
		bindEvents: function() {
			// Clear cache confirmation
			$('#ta-clear-cache-btn').on('click', this.confirmClearCache.bind(this));

			// Clear errors confirmation
			$('#ta-clear-errors-btn').on('click', this.confirmClearErrors.bind(this));

			// AJAX test connection (optional enhancement)
			$('#ta-test-connection-ajax').on('click', this.testConnectionAjax.bind(this));

			// AJAX test SMTP (optional enhancement)
			$('#ta-test-smtp-ajax').on('click', this.testSmtpAjax.bind(this));
		},

		/**
		 * Setup password field toggle visibility.
		 */
		setupPasswordToggle: function() {
			$('input[type="password"]').each(function() {
				var $input = $(this);
				var $wrapper = $('<div class="ta-password-field"></div>');
				var $toggle = $('<button type="button" class="ta-password-toggle" aria-label="' +
					(taAdmin.i18n.showPassword || 'Show password') + '">' +
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
		 * Setup homepage pattern dropdown toggle for custom input.
		 */
		setupHomepagePatternToggle: function() {
			var $select = $('#ta_homepage_md_pattern');
			var $customInput = $('#ta_homepage_md_pattern_custom');

			if ($select.length && $customInput.length) {
				// Handle dropdown change
				$select.on('change', function() {
					if ($(this).val() === 'custom') {
						$customInput.show().focus();
					} else {
						$customInput.hide();
					}
					// Update preview URL
					TAAdmin.updateHomepagePreview();
				});

				// Handle custom input change
				$customInput.on('input', function() {
					TAAdmin.updateHomepagePreview();
				});

				// Trigger on page load if custom is selected
				if ($select.val() === 'custom') {
					$customInput.show();
				}
			}
		},

		/**
		 * Update homepage markdown URL preview dynamically.
		 */
		updateHomepagePreview: function() {
			var $select = $('#ta_homepage_md_pattern');
			var $customInput = $('#ta_homepage_md_pattern_custom');
			var $previewCode = $('.ta-homepage-preview-url');
			var $testLink = $('.ta-homepage-test-link');

			if (!$select.length || !$previewCode.length) {
				return;
			}

			// Get selected pattern
			var pattern = $select.val();

			// If custom, use custom input value
			if (pattern === 'custom') {
				pattern = $customInput.val() || 'index.md';
			}

			// Ensure .md extension
			if (pattern.substr(-3) !== '.md') {
				pattern += '.md';
			}

			// Build preview URL (use taAdmin.homeUrl if available, fallback to extracting from page)
			var homeUrl = (typeof taAdmin !== 'undefined' && taAdmin.homeUrl) ?
				taAdmin.homeUrl : window.location.origin;

			// Ensure trailing slash
			if (homeUrl.substr(-1) !== '/') {
				homeUrl += '/';
			}

			var previewUrl = homeUrl + pattern;

			// Update preview display
			$previewCode.text(previewUrl);

			// Update test link
			$testLink.attr('href', previewUrl);
		},

		/**
		 * Confirm before clearing cache.
		 *
		 * @param {Event} e Click event.
		 * @return {boolean} Whether to proceed.
		 */
		confirmClearCache: function(e) {
			if (!confirm(taAdmin.i18n.confirmClear)) {
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
			if (!confirm(taAdmin.i18n.confirmClearErrors)) {
				e.preventDefault();
				return false;
			}
			$(e.target).addClass('ta-btn-loading').prop('disabled', true);
			return true;
		},

		/**
		 * Test connection via AJAX.
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
					} else {
						TAAdmin.showToast(response.data.message || taAdmin.i18n.error, 'error');
					}
				},
				error: function() {
					TAAdmin.showToast(taAdmin.i18n.error, 'error');
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
						TAAdmin.showToast(response.data.message || taAdmin.i18n.error, 'error');
					}
				},
				error: function() {
					TAAdmin.showToast(taAdmin.i18n.error, 'error');
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

			if (!confirm(taAdmin.i18n.confirmClear)) {
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
					} else {
						TAAdmin.showToast(response.data.message || taAdmin.i18n.error, 'error');
					}
				},
				error: function() {
					TAAdmin.showToast(taAdmin.i18n.error, 'error');
				},
				complete: function() {
					$btn.removeClass('ta-btn-loading').prop('disabled', false).text(originalText);
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

			var $toast = $('<div class="ta-toast ta-toast-' + type + '">' + message + '</div>');
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
						// Could also update the error table here
					}
				}
			});
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		TAAdmin.init();
	});

	// Expose to global scope for external access if needed
	window.TAAdmin = TAAdmin;

})(jQuery);
