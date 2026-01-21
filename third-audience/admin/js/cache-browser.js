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
