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
		 * Initialize
		 */
		init: function () {
			if (typeof taAnalyticsData === 'undefined') {
				return;
			}

			this.renderVisitsChart();
			this.renderBotDistributionChart();
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
