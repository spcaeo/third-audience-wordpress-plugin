/**
 * Competitor Benchmarking JavaScript
 *
 * @package ThirdAudience
 * @since   3.1.0
 */

(function($) {
	'use strict';

	const CompetitorBenchmarking = {
		init: function() {
			this.bindEvents();
			this.initCustomPromptBuilder();
		},

		bindEvents: function() {
			// Add Competitor
			$('#ta-add-competitor-btn').on('click', this.showAddCompetitorForm);
			$('#ta-cancel-add-competitor').on('click', this.hideAddCompetitorForm);
			$('#ta-competitor-form').on('submit', this.addCompetitor);

			// Delete Competitor
			$(document).on('click', '.ta-delete-competitor', this.deleteCompetitor);

			// Generate Prompts
			$('#ta-generate-prompts-btn').on('click', this.generatePrompts);

			// Record Test
			$('#ta-test-form').on('submit', this.recordTest);

			// Delete Result
			$(document).on('click', '.ta-delete-result', this.deleteResult);

			// Copy Prompt
			$(document).on('click', '.ta-copy-prompt', this.copyPrompt);

			// Use Prompt in Test
			$(document).on('click', '.ta-use-prompt', this.usePromptInTest);

			// Use Custom Prompt
			$('#ta-use-custom-prompt').on('click', this.useCustomPrompt);

			// View Full Prompt Modal
			$(document).on('click', '.ta-view-full-prompt', this.showPromptModal);
			$('.ta-modal-close').on('click', this.hidePromptModal);
			$(window).on('click', function(e) {
				if ($(e.target).hasClass('ta-modal')) {
					$('.ta-modal').hide();
				}
			});
		},

		showAddCompetitorForm: function(e) {
			e.preventDefault();
			$('.ta-add-competitor-form').slideDown();
		},

		hideAddCompetitorForm: function(e) {
			e.preventDefault();
			$('.ta-add-competitor-form').slideUp();
			$('#ta-competitor-form')[0].reset();
		},

		addCompetitor: function(e) {
			e.preventDefault();

			const $form = $(this);
			const $button = $form.find('button[type="submit"]');
			const originalText = $button.text();

			const data = {
				action: 'ta_add_competitor',
				nonce: taCompetitorBenchmarking.nonce,
				competitor_url: $('#competitor_url').val(),
				competitor_name: $('#competitor_name').val()
			};

			$button.prop('disabled', true).text(taCompetitorBenchmarking.i18n.saving);

			$.ajax({
				url: taCompetitorBenchmarking.ajaxUrl,
				type: 'POST',
				data: data,
				success: function(response) {
					if (response.success) {
						CompetitorBenchmarking.showNotice(response.data.message, 'success');
						setTimeout(function() {
							window.location.reload();
						}, 1000);
					} else {
						CompetitorBenchmarking.showNotice(response.data.message, 'error');
						$button.prop('disabled', false).text(originalText);
					}
				},
				error: function() {
					CompetitorBenchmarking.showNotice(taCompetitorBenchmarking.i18n.error, 'error');
					$button.prop('disabled', false).text(originalText);
				}
			});
		},

		deleteCompetitor: function(e) {
			e.preventDefault();

			const url = $(this).data('url');
			const name = $(this).data('name');

			if (!confirm(taCompetitorBenchmarking.i18n.confirmDeleteCompetitor.replace('%s', name))) {
				return;
			}

			const data = {
				action: 'ta_delete_competitor',
				nonce: taCompetitorBenchmarking.nonce,
				competitor_url: url
			};

			$.ajax({
				url: taCompetitorBenchmarking.ajaxUrl,
				type: 'POST',
				data: data,
				success: function(response) {
					if (response.success) {
						CompetitorBenchmarking.showNotice(response.data.message, 'success');
						setTimeout(function() {
							window.location.reload();
						}, 1000);
					} else {
						CompetitorBenchmarking.showNotice(response.data.message, 'error');
					}
				},
				error: function() {
					CompetitorBenchmarking.showNotice(taCompetitorBenchmarking.i18n.error, 'error');
				}
			});
		},

		generatePrompts: function(e) {
			e.preventDefault();

			const competitorUrl = $('#competitor_url').val();
			const competitorName = $('#competitor_url option:selected').data('name');

			if (!competitorUrl) {
				alert(taCompetitorBenchmarking.i18n.selectCompetitor);
				return;
			}

			const $button = $(this);
			const originalText = $button.text();

			$button.prop('disabled', true).text(taCompetitorBenchmarking.i18n.generating);

			const data = {
				action: 'ta_generate_prompts',
				nonce: taCompetitorBenchmarking.nonce,
				competitor_url: competitorUrl,
				competitor_name: competitorName
			};

			$.ajax({
				url: taCompetitorBenchmarking.ajaxUrl,
				type: 'POST',
				data: data,
				success: function(response) {
					if (response.success) {
						CompetitorBenchmarking.displayGeneratedPrompts(response.data.prompts);
						$('.ta-generated-prompts').slideDown();
					} else {
						alert(response.data.message);
					}
					$button.prop('disabled', false).text(originalText);
				},
				error: function() {
					alert(taCompetitorBenchmarking.i18n.error);
					$button.prop('disabled', false).text(originalText);
				}
			});
		},

		displayGeneratedPrompts: function(prompts) {
			const $list = $('#ta-prompts-list');
			$list.empty();

			$.each(prompts, function(category, data) {
				const $category = $('<div class="ta-prompt-category"></div>');
				$category.append('<h4>' + data.name + '</h4>');

				const $promptsList = $('<div class="ta-prompts-list"></div>');

				$.each(data.prompts, function(index, prompt) {
					const $template = $('<div class="ta-prompt-template"></div>');
					$template.append('<div class="ta-prompt-text"><code>' + prompt + '</code></div>');

					const $actions = $('<div class="ta-prompt-actions"></div>');
					$actions.append('<button type="button" class="button button-small ta-use-prompt" data-prompt="' + prompt + '">' + taCompetitorBenchmarking.i18n.usePrompt + '</button>');

					$template.append($actions);
					$promptsList.append($template);
				});

				$category.append($promptsList);
				$list.append($category);
			});
		},

		recordTest: function(e) {
			e.preventDefault();

			const $form = $(this);
			const $button = $form.find('button[type="submit"]');
			const originalText = $button.text();

			// Get competitor name from selected option
			const competitorName = $('#competitor_url option:selected').data('name');

			const data = {
				action: 'ta_record_test',
				nonce: $('#ta_test_nonce').val(),
				competitor_url: $('#competitor_url').val(),
				competitor_name: competitorName,
				test_prompt: $('#test_prompt').val(),
				ai_platform: $('#ai_platform').val(),
				cited_rank: $('#cited_rank').val(),
				test_notes: $('#test_notes').val(),
				test_date: $('#test_date').val()
			};

			$button.prop('disabled', true).text(taCompetitorBenchmarking.i18n.saving);

			$.ajax({
				url: taCompetitorBenchmarking.ajaxUrl,
				type: 'POST',
				data: data,
				success: function(response) {
					if (response.success) {
						CompetitorBenchmarking.showNotice(response.data.message, 'success');
						$form[0].reset();
						// Redirect to results tab after 2 seconds
						setTimeout(function() {
							window.location.href = taCompetitorBenchmarking.resultsUrl;
						}, 2000);
					} else {
						CompetitorBenchmarking.showNotice(response.data.message, 'error');
						$button.prop('disabled', false).text(originalText);
					}
				},
				error: function() {
					CompetitorBenchmarking.showNotice(taCompetitorBenchmarking.i18n.error, 'error');
					$button.prop('disabled', false).text(originalText);
				}
			});
		},

		deleteResult: function(e) {
			e.preventDefault();

			const id = $(this).data('id');

			if (!confirm(taCompetitorBenchmarking.i18n.confirmDeleteResult)) {
				return;
			}

			const data = {
				action: 'ta_delete_test',
				nonce: taCompetitorBenchmarking.nonce,
				test_id: id
			};

			$.ajax({
				url: taCompetitorBenchmarking.ajaxUrl,
				type: 'POST',
				data: data,
				success: function(response) {
					if (response.success) {
						CompetitorBenchmarking.showNotice(response.data.message, 'success');
						setTimeout(function() {
							window.location.reload();
						}, 1000);
					} else {
						CompetitorBenchmarking.showNotice(response.data.message, 'error');
					}
				},
				error: function() {
					CompetitorBenchmarking.showNotice(taCompetitorBenchmarking.i18n.error, 'error');
				}
			});
		},

		copyPrompt: function(e) {
			e.preventDefault();

			const prompt = $(this).data('prompt');
			const $button = $(this);
			const originalText = $button.text();

			// Copy to clipboard
			navigator.clipboard.writeText(prompt).then(function() {
				$button.text(taCompetitorBenchmarking.i18n.copied);
				setTimeout(function() {
					$button.text(originalText);
				}, 2000);
			}).catch(function() {
				alert(taCompetitorBenchmarking.i18n.copyFailed);
			});
		},

		usePromptInTest: function(e) {
			e.preventDefault();

			const prompt = $(this).data('prompt');

			// Navigate to test tab with prompt
			const url = new URL(window.location.href);
			url.searchParams.set('tab', 'test');
			window.location.href = url.toString();

			// Store prompt in sessionStorage to populate on next page
			sessionStorage.setItem('ta_test_prompt', prompt);
		},

		useCustomPrompt: function(e) {
			e.preventDefault();

			const prompt = $('#ta-custom-prompt-result code').text();

			if (prompt === taCompetitorBenchmarking.i18n.fillFields) {
				return;
			}

			// Navigate to test tab with prompt
			const url = new URL(window.location.href);
			url.searchParams.set('tab', 'test');
			window.location.href = url.toString();

			// Store prompt in sessionStorage
			sessionStorage.setItem('ta_test_prompt', prompt);
		},

		initCustomPromptBuilder: function() {
			const $category = $('#custom_category');
			const $useCase = $('#custom_use_case');
			const $template = $('#custom_template');
			const $result = $('#ta-custom-prompt-result code');
			const $button = $('#ta-use-custom-prompt');

			function updatePrompt() {
				const category = $category.val();
				const useCase = $useCase.val();
				const templateText = $template.val();

				if (category && useCase) {
					let prompt = templateText
						.replace('{category}', category)
						.replace('{use_case}', useCase);

					$result.text(prompt);
					$button.prop('disabled', false);
				} else {
					$result.text(taCompetitorBenchmarking.i18n.fillFields);
					$button.prop('disabled', true);
				}
			}

			$category.on('input', updatePrompt);
			$useCase.on('input', updatePrompt);
			$template.on('change', updatePrompt);
		},

		showPromptModal: function(e) {
			e.preventDefault();

			const prompt = $(this).data('prompt');
			$('#ta-prompt-modal-text').text(prompt);
			$('#ta-prompt-modal').fadeIn();
		},

		hidePromptModal: function(e) {
			e.preventDefault();
			$('.ta-modal').fadeOut();
		},

		showNotice: function(message, type) {
			const $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
			$('.wrap').prepend($notice);

			// Auto-dismiss after 5 seconds
			setTimeout(function() {
				$notice.fadeOut(function() {
					$(this).remove();
				});
			}, 5000);
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		CompetitorBenchmarking.init();

		// Check if there's a prompt in sessionStorage
		const savedPrompt = sessionStorage.getItem('ta_test_prompt');
		if (savedPrompt && $('#test_prompt').length) {
			$('#test_prompt').val(savedPrompt);
			sessionStorage.removeItem('ta_test_prompt');
		}
	});

})(jQuery);
