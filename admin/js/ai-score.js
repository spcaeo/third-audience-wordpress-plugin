/**
 * AI-Friendliness Score - Admin JavaScript
 *
 * @package ThirdAudience
 * @since   2.8.0
 */

(function($) {
	'use strict';

	/**
	 * AI Score handler.
	 */
	const TAIScore = {

		/**
		 * Initialize.
		 */
		init: function() {
			this.bindEvents();
		},

		/**
		 * Bind event handlers.
		 */
		bindEvents: function() {
			// Recalculate score button.
			$(document).on('click', '.ta-recalculate-score', this.recalculateScore);
		},

		/**
		 * Recalculate AI-Friendliness score.
		 */
		recalculateScore: function(e) {
			e.preventDefault();

			const $button = $(this);
			const $metabox = $button.closest('.ta-ai-score-metabox');
			const $loading = $metabox.find('.ta-score-loading');
			const postId = $button.data('post-id');

			if (!postId) {
				alert('Error: Post ID not found.');
				return;
			}

			// Show loading state.
			$button.prop('disabled', true);
			$loading.show();

			// Make AJAX request.
			$.ajax({
				url: taAIScore.ajaxUrl,
				type: 'POST',
				data: {
					action: 'ta_recalculate_ai_score',
					nonce: taAIScore.nonce,
					post_id: postId
				},
				success: function(response) {
					if (response.success) {
						// Reload the page to show updated score.
						location.reload();
					} else {
						alert(response.data.message || 'Failed to recalculate score.');
					}
				},
				error: function(xhr, status, error) {
					console.error('AJAX error:', error);
					alert('Failed to recalculate score. Please try again.');
				},
				complete: function() {
					$button.prop('disabled', false);
					$loading.hide();
				}
			});
		}
	};

	// Initialize on document ready.
	$(document).ready(function() {
		TAIScore.init();
	});

})(jQuery);
