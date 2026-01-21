/**
 * Cache Browser JavaScript
 *
 * @package ThirdAudience
 * @since   1.6.0
 */

/* global jQuery, taCacheBrowser */

(function($) {
	'use strict';

	var TACacheBrowser = {
		warmupInProgress: false,
		warmupCancelled: false,

		init: function() {
			this.bindEvents();
			this.loadWarmupStats();
			this.initFilters();
		},

		bindEvents: function() {
			$('#ta-select-all').on('click', this.handleSelectAll);
			$('#ta-bulk-delete-btn').on('click', this.handleBulkDelete.bind(this));
			$('#ta-clear-expired-btn').on('click', this.handleClearExpired.bind(this));
			$('.ta-delete-btn').on('click', this.handleDelete.bind(this));
			$('.ta-view-btn').on('click', this.handleView.bind(this));
			$('.ta-regen-btn').on('click', this.handleRegenerate.bind(this));
			$('#ta-modal-close, #ta-modal').on('click', this.closeModal);
			$('#ta-warmup-all-btn').on('click', this.handleWarmup.bind(this));
			$('#ta-warmup-cancel-btn').on('click', this.handleCancelWarmup.bind(this));

			// Filter events.
			$('.ta-toggle-filters').on('click', this.toggleFilters.bind(this));
			$('.ta-size-preset').on('click', this.handleSizePreset.bind(this));
			$('.ta-date-preset').on('click', this.handleDatePreset.bind(this));
			$('#ta-clear-filters').on('click', this.clearFilters.bind(this));

			// Sorting events.
			$('.ta-sortable').on('click', this.handleSort.bind(this));
		},

		initFilters: function() {
			// Check if filters are active and show panel if so.
			var urlParams = new URLSearchParams(window.location.search);
			var hasFilters = urlParams.has('status') || urlParams.has('size_min') ||
			                 urlParams.has('size_max') || urlParams.has('date_from') ||
			                 urlParams.has('date_to');

			if (hasFilters) {
				$('.ta-filters-content').show();
				$('.ta-toggle-filters .dashicons').removeClass('dashicons-arrow-down-alt2')
					.addClass('dashicons-arrow-up-alt2');
			}
		},

		toggleFilters: function(e) {
			e.preventDefault();
			$('.ta-filters-content').slideToggle(300);
			$('.ta-toggle-filters .dashicons').toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
		},

		handleSizePreset: function(e) {
			e.preventDefault();
			var $btn = $(e.currentTarget);
			var min = $btn.data('min');
			var max = $btn.data('max');

			$('#ta-filter-size-min').val(min);
			$('#ta-filter-size-max').val(max);
		},

		handleDatePreset: function(e) {
			e.preventDefault();
			var $btn = $(e.currentTarget);
			var preset = $btn.data('preset');
			var today = new Date();
			var dateTo = this.formatDate(today);
			var dateFrom;

			switch (preset) {
				case '24h':
					dateFrom = this.formatDate(new Date(today.getTime() - 24 * 60 * 60 * 1000));
					break;
				case '7d':
					dateFrom = this.formatDate(new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000));
					break;
				case '30d':
					dateFrom = this.formatDate(new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000));
					break;
			}

			$('#ta-filter-date-from').val(dateFrom);
			$('#ta-filter-date-to').val(dateTo);
		},

		formatDate: function(date) {
			var year = date.getFullYear();
			var month = String(date.getMonth() + 1).padStart(2, '0');
			var day = String(date.getDate()).padStart(2, '0');
			return year + '-' + month + '-' + day;
		},

		clearFilters: function(e) {
			e.preventDefault();
			window.location.href = '?page=third-audience-cache-browser';
		},

		handleSort: function(e) {
			var $th = $(e.currentTarget);
			var column = $th.data('column');
			var currentOrderBy = this.getUrlParam('orderby');
			var currentOrder = this.getUrlParam('order') || 'DESC';
			var newOrder = 'DESC';

			// If clicking the same column, toggle order.
			if (column === currentOrderBy) {
				newOrder = currentOrder === 'DESC' ? 'ASC' : 'DESC';
			}

			// Build URL with sorting params.
			var url = new URL(window.location.href);
			url.searchParams.set('orderby', column);
			url.searchParams.set('order', newOrder);

			window.location.href = url.toString();
		},

		getUrlParam: function(param) {
			var urlParams = new URLSearchParams(window.location.search);
			return urlParams.get(param);
		},

		handleSelectAll: function() {
			$('.ta-cache-checkbox').prop('checked', $(this).prop('checked'));
		},

		handleDelete: function(e) {
			if (!confirm(taCacheBrowser.i18n.confirmDelete)) return;

			var $btn = $(e.target);
			var key = $btn.data('key');

			$btn.prop('disabled', true).text('...');

			$.ajax({
				url: taCacheBrowser.ajaxUrl,
				type: 'POST',
				data: {
					action: 'ta_delete_cache_entry',
					nonce: taCacheBrowser.nonce,
					cache_key: key
				},
				success: function(response) {
					if (response.success) {
						location.reload();
					} else {
						alert(response.data.message);
					}
				},
				error: function() {
					alert(taCacheBrowser.i18n.error);
					$btn.prop('disabled', false);
				}
			});
		},

		handleBulkDelete: function() {
			var keys = [];
			$('.ta-cache-checkbox:checked').each(function() {
				keys.push($(this).val());
			});

			if (keys.length === 0) {
				alert(taCacheBrowser.i18n.selectEntries);
				return;
			}

			if (!confirm(taCacheBrowser.i18n.confirmBulkDelete)) return;

			$.ajax({
				url: taCacheBrowser.ajaxUrl,
				type: 'POST',
				data: {
					action: 'ta_bulk_delete_cache',
					nonce: taCacheBrowser.nonce,
					cache_keys: keys
				},
				success: function(response) {
					if (response.success) {
						location.reload();
					}
				}
			});
		},

		handleClearExpired: function() {
			if (!confirm(taCacheBrowser.i18n.confirmClearExpired)) return;

			$.ajax({
				url: taCacheBrowser.ajaxUrl,
				type: 'POST',
				data: {
					action: 'ta_clear_expired_cache',
					nonce: taCacheBrowser.nonce
				},
				success: function(response) {
					if (response.success) {
						location.reload();
					}
				}
			});
		},

		handleRegenerate: function(e) {
			var $btn = $(e.target);
			var postId = $btn.data('id');

			$btn.prop('disabled', true).text('...');

			$.ajax({
				url: taCacheBrowser.ajaxUrl,
				type: 'POST',
				data: {
					action: 'ta_regenerate_cache',
					nonce: taCacheBrowser.nonce,
					post_id: postId
				},
				success: function(response) {
					if (response.success) {
						location.reload();
					} else {
						alert(response.data.message);
						$btn.prop('disabled', false);
					}
				}
			});
		},

		handleView: function(e) {
			var key = $(e.target).data('key');

			$.ajax({
				url: taCacheBrowser.ajaxUrl,
				type: 'POST',
				data: {
					action: 'ta_view_cache_content',
					nonce: taCacheBrowser.nonce,
					cache_key: key
				},
				success: function(response) {
					if (response.success) {
						$('#ta-modal-content').text(response.data.content);
						$('#ta-modal').addClass('active');
					}
				}
			});
		},

		closeModal: function(e) {
			if (e.target.id === 'ta-modal' || e.target.id === 'ta-modal-close') {
				$('#ta-modal').removeClass('active');
			}
		},

		loadWarmupStats: function() {
			$.ajax({
				url: taCacheBrowser.ajaxUrl,
				type: 'POST',
				data: {
					action: 'ta_get_warmup_stats',
					nonce: taCacheBrowser.nonce
				},
				success: function(response) {
					if (response.success) {
						var stats = response.data;
						$('#ta-warmup-coverage').text(stats.percentage + '% (' + stats.cached + '/' + stats.total + ')');
						$('#ta-warmup-uncached').text(stats.uncached);
					}
				}
			});
		},

		handleWarmup: function() {
			if (this.warmupInProgress) return;

			if (!confirm(taCacheBrowser.i18n.confirmWarmup || 'Start warming all cache? This may take a few minutes.')) {
				return;
			}

			this.warmupInProgress = true;
			this.warmupCancelled = false;

			$('#ta-warmup-all-btn').prop('disabled', true);
			$('#ta-warmup-cancel-btn').show();
			$('#ta-warmup-progress').show();

			this.processWarmupBatch(0, 0);
		},

		processWarmupBatch: function(offset, totalWarmed) {
			var self = this;

			if (this.warmupCancelled) {
				this.finishWarmup(totalWarmed, true);
				return;
			}

			$.ajax({
				url: taCacheBrowser.ajaxUrl,
				type: 'POST',
				data: {
					action: 'ta_start_warmup_batch',
					nonce: taCacheBrowser.nonce,
					batch_size: 10,
					offset: offset
				},
				success: function(response) {
					if (response.success) {
						var results = response.data.results;
						var stats = response.data.stats;

						totalWarmed += results.warmed;

						// Update progress.
						var percentage = stats.total > 0 ? Math.round((stats.cached / stats.total) * 100) : 100;
						$('.progress-fill').css('width', percentage + '%');
						$('#ta-warmup-status').text('Warmed ' + totalWarmed + ' entries...');
						$('#ta-warmup-percentage').text(percentage + '%');

						// Continue if there are more uncached posts.
						if (stats.uncached > 0 && !self.warmupCancelled) {
							setTimeout(function() {
								self.processWarmupBatch(offset + 10, totalWarmed);
							}, 500);
						} else {
							self.finishWarmup(totalWarmed, false);
						}
					} else {
						alert(taCacheBrowser.i18n.error);
						self.finishWarmup(totalWarmed, false);
					}
				},
				error: function() {
					alert(taCacheBrowser.i18n.error);
					self.finishWarmup(totalWarmed, false);
				}
			});
		},

		finishWarmup: function(totalWarmed, cancelled) {
			this.warmupInProgress = false;

			$('#ta-warmup-all-btn').prop('disabled', false);
			$('#ta-warmup-cancel-btn').hide();

			if (cancelled) {
				$('#ta-warmup-status').text('Warmup cancelled. Warmed ' + totalWarmed + ' entries.');
			} else {
				$('#ta-warmup-status').text('Warmup complete! Warmed ' + totalWarmed + ' entries.');
			}

			// Reload stats and page after a delay.
			setTimeout(function() {
				location.reload();
			}, 2000);
		},

		handleCancelWarmup: function() {
			this.warmupCancelled = true;
			$('#ta-warmup-cancel-btn').prop('disabled', true).text('Cancelling...');
		}
	};

	$(document).ready(function() {
		TACacheBrowser.init();
	});
})(jQuery);
