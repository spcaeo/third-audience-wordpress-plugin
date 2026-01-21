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
		init: function() {
			this.bindEvents();
		},

		bindEvents: function() {
			$('#ta-select-all').on('click', this.handleSelectAll);
			$('#ta-bulk-delete-btn').on('click', this.handleBulkDelete.bind(this));
			$('#ta-clear-expired-btn').on('click', this.handleClearExpired.bind(this));
			$('.ta-delete-btn').on('click', this.handleDelete.bind(this));
			$('.ta-view-btn').on('click', this.handleView.bind(this));
			$('.ta-regen-btn').on('click', this.handleRegenerate.bind(this));
			$('#ta-modal-close, #ta-modal').on('click', this.closeModal);
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
		}
	};

	$(document).ready(function() {
		TACacheBrowser.init();
	});
})(jQuery);
