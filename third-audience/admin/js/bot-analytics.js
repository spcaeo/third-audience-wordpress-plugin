/**
 * Bot Analytics Dashboard JavaScript
 *
 * @package ThirdAudience
 * @since   1.4.0
 */

(function ($) {
	'use strict';

	/**
	 * Initialize analytics dashboard
	 */
	var TABotAnalytics = {
		/**
		 * Feed refresh interval ID
		 */
		feedRefreshInterval: null,

		/**
		 * Feed is paused
		 */
		feedPaused: false,

		/**
		 * Initialize
		 */
		init: function () {
			// Always initialize toggle and clear button, even if no chart data
			this.initCacheHelpToggle();
			this.initClearAllVisits();
			this.initExportDropdown();
			this.initLiveFeed();

			// Only initialize charts if data is available
			if (typeof taAnalyticsData === 'undefined') {
				return;
			}

			this.renderVisitsChart();
			this.renderBotDistributionChart();
		},

		/**
		 * Initialize cache help toggle (modal dialog)
		 */
		initCacheHelpToggle: function () {
			var self = this;

			// Open modal on button click
			$('.ta-cache-help-toggle').on('click', function(e) {
				e.preventDefault();
				self.openCacheGuideModal();
			});

			// Close modal on close button click
			$(document).on('click', '.ta-cache-modal-close', function(e) {
				e.preventDefault();
				self.closeCacheGuideModal();
			});

			// Close modal on overlay click
			$(document).on('click', '.ta-cache-modal-overlay', function(e) {
				if (e.target === this) {
					self.closeCacheGuideModal();
				}
			});

			// Close modal on Escape key
			$(document).on('keydown', function(e) {
				if (e.key === 'Escape' && $('.ta-cache-modal-overlay').is(':visible')) {
					self.closeCacheGuideModal();
				}
			});
		},

		/**
		 * Open cache guide modal
		 */
		openCacheGuideModal: function() {
			$('.ta-cache-modal-overlay').fadeIn(200);
			$('body').addClass('ta-modal-open');
		},

		/**
		 * Close cache guide modal
		 */
		closeCacheGuideModal: function() {
			$('.ta-cache-modal-overlay').fadeOut(200);
			$('body').removeClass('ta-modal-open');
		},

		/**
		 * Initialize clear all visits button
		 */
		initClearAllVisits: function() {
			var self = this;

			$('.ta-clear-all-visits').on('click', function(e) {
				e.preventDefault();
				self.handleClearAllVisits();
			});
		},

		/**
		 * Handle clear all visits button click
		 */
		handleClearAllVisits: function() {
			var self = this;

			if (!confirm('Are you sure you want to clear all bot visit records? This action cannot be undone.')) {
				return;
			}

			var $button = $('.ta-clear-all-visits');
			var originalText = $button.text();
			$button.prop('disabled', true).text('Clearing...');

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'ta_clear_all_visits',
					nonce: taAnalyticsData ? taAnalyticsData.nonce : ''
				},
				success: function(response) {
					if (response.success) {
						alert(response.data.message);
						// Reload the page to show updated data
						window.location.reload();
					} else {
						alert('Error: ' + (response.data.message || 'Failed to clear visits'));
						$button.prop('disabled', false).text(originalText);
					}
				},
				error: function() {
					alert('Error: Failed to communicate with server');
					$button.prop('disabled', false).text(originalText);
				}
			});
		},

		/**
		 * Initialize export dropdown
		 */
		initExportDropdown: function() {
			var self = this;

			// Toggle dropdown on button click
			$(document).on('click', '.ta-export-dropdown-toggle', function(e) {
				e.preventDefault();
				e.stopPropagation();
				var $dropdown = $(this).closest('.ta-export-dropdown');
				$dropdown.toggleClass('ta-export-dropdown-active');
			});

			// Close dropdown when clicking outside
			$(document).on('click', function(e) {
				if (!$(e.target).closest('.ta-export-dropdown').length) {
					$('.ta-export-dropdown').removeClass('ta-export-dropdown-active');
				}
			});

			// Close dropdown when clicking an option
			$(document).on('click', '.ta-export-option', function() {
				$('.ta-export-dropdown').removeClass('ta-export-dropdown-active');
			});
		},

		/**
		 * Render visits over time chart
		 */
		renderVisitsChart: function () {
			var ctx = document.getElementById('ta-visits-chart');
			if (!ctx) {
				return;
			}

			var data = taAnalyticsData.visitsOverTime;
			var labels = data.map(function (item) {
				return item.period;
			});
			var visits = data.map(function (item) {
				return parseInt(item.visits);
			});
			var uniqueBots = data.map(function (item) {
				return parseInt(item.unique_bots);
			});

			new Chart(ctx, {
				type: 'line',
				data: {
					labels: labels,
					datasets: [
						{
							label: 'Total Visits',
							data: visits,
							borderColor: '#2271b1',
							backgroundColor: 'rgba(34, 113, 177, 0.1)',
							borderWidth: 2,
							fill: true,
							tension: 0.4,
							pointRadius: 4,
							pointHoverRadius: 6,
							pointBackgroundColor: '#2271b1',
							pointBorderColor: '#fff',
							pointBorderWidth: 2
						},
						{
							label: 'Unique Bots',
							data: uniqueBots,
							borderColor: '#00a32a',
							backgroundColor: 'rgba(0, 163, 42, 0.1)',
							borderWidth: 2,
							fill: true,
							tension: 0.4,
							pointRadius: 4,
							pointHoverRadius: 6,
							pointBackgroundColor: '#00a32a',
							pointBorderColor: '#fff',
							pointBorderWidth: 2
						}
					]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							display: true,
							position: 'top',
							labels: {
								usePointStyle: true,
								padding: 15,
								font: {
									size: 13,
									weight: '500'
								}
							}
						},
						tooltip: {
							mode: 'index',
							intersect: false,
							backgroundColor: 'rgba(0, 0, 0, 0.8)',
							padding: 12,
							titleFont: {
								size: 13,
								weight: '600'
							},
							bodyFont: {
								size: 12
							},
							callbacks: {
								label: function (context) {
									var label = context.dataset.label || '';
									if (label) {
										label += ': ';
									}
									label += context.parsed.y.toLocaleString();
									return label;
								}
							}
						}
					},
					scales: {
						x: {
							grid: {
								display: false
							},
							ticks: {
								font: {
									size: 11
								},
								maxRotation: 45,
								minRotation: 0
							}
						},
						y: {
							beginAtZero: true,
							grid: {
								color: 'rgba(0, 0, 0, 0.05)'
							},
							ticks: {
								font: {
									size: 11
								},
								callback: function (value) {
									return value.toLocaleString();
								}
							}
						}
					},
					interaction: {
						mode: 'nearest',
						axis: 'x',
						intersect: false
					}
				}
			});
		},

		/**
		 * Initialize live feed
		 */
		initLiveFeed: function () {
			var self = this;

			// Load initial data
			this.loadLiveFeedData();

			// Set up auto-refresh interval (10 seconds)
			this.feedRefreshInterval = setInterval(function () {
				if (!self.feedPaused) {
					self.loadLiveFeedData();
				}
			}, 10000);

			// Initialize pause/play button
			$(document).on('click', '.ta-feed-toggle-btn', function (e) {
				e.preventDefault();
				self.toggleFeedPause();
			});
		},

		/**
		 * Load live feed data via AJAX
		 */
		loadLiveFeedData: function () {
			var self = this;

			if (typeof taAnalyticsData === 'undefined' || !taAnalyticsData.feedNonce) {
				return;
			}

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'ta_get_recent_accesses',
					nonce: taAnalyticsData.feedNonce
				},
				success: function (response) {
					if (response.success && response.data.accesses) {
						self.updateLiveFeedTable(response.data.accesses);
					}
				},
				error: function () {
					// Silently fail on AJAX error, just don't update
				}
			});
		},

		/**
		 * Update live feed table with new data
		 */
		updateLiveFeedTable: function (accesses) {
			var $tbody = $('#ta-live-feed-tbody');
			var currentIds = {};

			// Get current row IDs
			$tbody.find('tr').each(function () {
				var id = $(this).attr('data-access-id');
				if (id) {
					currentIds[id] = true;
				}
			});

			// Build new rows
			var newRows = [];
			var hasNewRows = false;

			for (var i = 0; i < accesses.length; i++) {
				var access = accesses[i];
				var rowHtml = this.buildAccessRow(access);

				if (!currentIds[access.id]) {
					hasNewRows = true;
				}

				newRows.push({
					id: access.id,
					html: rowHtml,
					isNew: !currentIds[access.id]
				});
			}

			// Only update if we have data or if the table is still loading
			if (newRows.length > 0) {
				// Clear loading state if present
				$tbody.find('.ta-feed-loading').remove();

				// Add new rows with fade-in animation
				for (var j = 0; j < newRows.length; j++) {
					var newRow = $(newRows[j].html);
					if (newRows[j].isNew) {
						newRow.addClass('ta-feed-row-new');
					}
					$tbody.prepend(newRow);
				}

				// Keep only last 20 rows
				var rows = $tbody.find('tr');
				if (rows.length > 20) {
					rows.slice(20).remove();
				}
			}
		},

		/**
		 * Build a single access row HTML
		 */
		buildAccessRow: function (access) {
			var timeText = this.formatRelativeTime(access.timestamp);
			var urlDisplay = access.url.split('/').pop() || access.url;
			if (urlDisplay.length > 40) {
				urlDisplay = urlDisplay.substring(0, 37) + '...';
			}

			var cacheClass = 'ta-cache-' + access.cache_status.toLowerCase();
			var responseTime = access.response_time ? access.response_time + 'ms' : '-';

			var html = '<tr data-access-id="' + access.id + '">' +
				'<td class="ta-feed-time" title="' + this.escapeHtml(access.timestamp) + '">' + timeText + '</td>' +
				'<td class="ta-feed-url"><a href="' + this.escapeHtml(access.url) + '" target="_blank" title="' + this.escapeHtml(access.url) + '">' + this.escapeHtml(urlDisplay) + '</a></td>' +
				'<td class="ta-feed-bot"><span class="ta-bot-badge">' + this.escapeHtml(access.bot_name) + '</span></td>' +
				'<td class="ta-feed-cache"><span class="ta-cache-badge ' + cacheClass + '">' + this.escapeHtml(access.cache_status) + '</span></td>' +
				'<td class="ta-feed-response">' + responseTime + '</td>' +
				'</tr>';

			return html;
		},

		/**
		 * Format relative time (e.g., "5s ago", "2m ago")
		 */
		formatRelativeTime: function (timestamp) {
			var now = new Date();
			var then = new Date(timestamp);
			var diff = Math.floor((now - then) / 1000); // seconds

			if (diff < 60) {
				return diff + 's ago';
			} else if (diff < 3600) {
				return Math.floor(diff / 60) + 'm ago';
			} else if (diff < 86400) {
				return Math.floor(diff / 3600) + 'h ago';
			} else {
				return Math.floor(diff / 86400) + 'd ago';
			}
		},

		/**
		 * Escape HTML to prevent XSS
		 */
		escapeHtml: function (text) {
			var div = document.createElement('div');
			div.textContent = text;
			return div.innerHTML;
		},

		/**
		 * Toggle feed pause/play
		 */
		toggleFeedPause: function () {
			var $btn = $('.ta-feed-toggle-btn');
			this.feedPaused = !this.feedPaused;

			if (this.feedPaused) {
				$btn.attr('data-paused', 'true');
				$btn.find('.dashicons').removeClass('dashicons-media-pause').addClass('dashicons-controls-play');
				$btn.find('span').text('Resume');
				$('.ta-live-feed-widget').addClass('ta-feed-paused');
			} else {
				$btn.attr('data-paused', 'false');
				$btn.find('.dashicons').removeClass('dashicons-controls-play').addClass('dashicons-media-pause');
				$btn.find('span').text('Pause');
				$('.ta-live-feed-widget').removeClass('ta-feed-paused');
				// Load fresh data when resuming
				this.loadLiveFeedData();
			}
		},

		/**
		 * Render bot distribution chart
		 */
		renderBotDistributionChart: function () {
			var ctx = document.getElementById('ta-bot-distribution-chart');
			if (!ctx) {
				return;
			}

			var data = taAnalyticsData.botDistribution;
			var labels = data.map(function (item) {
				return item.bot_name;
			});
			var counts = data.map(function (item) {
				return parseInt(item.count);
			});
			var colors = data.map(function (item) {
				return item.color;
			});

			new Chart(ctx, {
				type: 'doughnut',
				data: {
					labels: labels,
					datasets: [
						{
							data: counts,
							backgroundColor: colors,
							borderColor: '#fff',
							borderWidth: 2,
							hoverOffset: 10
						}
					]
				},
				options: {
					responsive: true,
					maintainAspectRatio: true,
					aspectRatio: 2,
					plugins: {
						legend: {
							display: false
						},
						tooltip: {
							backgroundColor: 'rgba(0, 0, 0, 0.8)',
							padding: 12,
							titleFont: {
								size: 13,
								weight: '600'
							},
							bodyFont: {
								size: 12
							},
							callbacks: {
								label: function (context) {
									var label = context.label || '';
									var value = context.parsed;
									var total = context.dataset.data.reduce(function (a, b) {
										return a + b;
									}, 0);
									var percentage = ((value / total) * 100).toFixed(1);

									return label + ': ' + value.toLocaleString() + ' (' + percentage + '%)';
								}
							}
						}
					}
				}
			});
		}
	};

	/**
	 * Initialize on document ready
	 */
	$(document).ready(function () {
		TABotAnalytics.init();
	});

})(jQuery);
