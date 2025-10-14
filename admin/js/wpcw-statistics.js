/**
 * WPCW Statistics Dashboard JavaScript
 */

(function($) {
	'use strict';

	// Wait for DOM and Chart.js to load
	$(document).ready(function() {
		if (typeof Chart === 'undefined') {
			console.error('Chart.js not loaded');
			return;
		}

		// Initialize admin marketplace charts
		if (typeof wpcwMarketplaceMetrics !== 'undefined') {
			initMarketplaceCharts(wpcwMarketplaceMetrics);
		}

		// Initialize entity-specific charts
		if (typeof wpcwEntityMetrics !== 'undefined') {
			initEntityCharts(wpcwEntityMetrics);
		}
	});

	/**
	 * Initialize marketplace-level charts (admin view)
	 */
	function initMarketplaceCharts(metrics) {
		// Status Distribution Doughnut Chart
		const statusCtx = document.getElementById('statusDistributionChart');
		if (statusCtx) {
			const statusData = metrics.status_distribution || {};
			const statusLabels = Object.keys(statusData).map(formatStatusLabel);
			const statusValues = Object.values(statusData);
			const statusColors = generateStatusColors(Object.keys(statusData));

			new Chart(statusCtx, {
				type: 'doughnut',
				data: {
					labels: statusLabels,
					datasets: [{
						data: statusValues,
						backgroundColor: statusColors,
						borderWidth: 2,
						borderColor: '#fff'
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							position: 'right',
							labels: {
								padding: 15,
								font: {
									size: 13
								}
							}
						},
						tooltip: {
							callbacks: {
								label: function(context) {
									const label = context.label || '';
									const value = context.parsed || 0;
									const total = context.dataset.data.reduce((a, b) => a + b, 0);
									const percentage = ((value / total) * 100).toFixed(1);
									return label + ': ' + value + ' (' + percentage + '%)';
								}
							}
						}
					}
				}
			});
		}

		// Network Overview Bar Chart
		const networkCtx = document.getElementById('networkOverviewChart');
		if (networkCtx) {
			new Chart(networkCtx, {
				type: 'bar',
				data: {
					labels: ['Instituciones', 'Comercios', 'Convenios Activos', 'Canjes Totales'],
					datasets: [{
						label: 'Cantidad',
						data: [
							metrics.institutions_count,
							metrics.businesses_count,
							metrics.active_convenios,
							Math.min(metrics.total_redemptions, 1000) // Scale down for visibility
						],
						backgroundColor: [
							'rgba(231, 76, 60, 0.7)',
							'rgba(46, 204, 113, 0.7)',
							'rgba(52, 152, 219, 0.7)',
							'rgba(155, 89, 182, 0.7)'
						],
						borderColor: [
							'rgb(231, 76, 60)',
							'rgb(46, 204, 113)',
							'rgb(52, 152, 219)',
							'rgb(155, 89, 182)'
						],
						borderWidth: 2
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							display: false
						},
						tooltip: {
							callbacks: {
								label: function(context) {
									let label = context.dataset.label || '';
									if (label) {
										label += ': ';
									}
									// Show real number for redemptions
									if (context.dataIndex === 3) {
										label += metrics.total_redemptions.toLocaleString();
									} else {
										label += context.parsed.y.toLocaleString();
									}
									return label;
								}
							}
						}
					},
					scales: {
						y: {
							beginAtZero: true,
							ticks: {
								callback: function(value) {
									return value.toLocaleString();
								}
							}
						}
					}
				}
			});
		}
	}

	/**
	 * Initialize entity-specific charts (institution/business view)
	 */
	function initEntityCharts(metrics) {
		// TODO: Add entity-specific charts
		// Could include:
		// - Redemptions over time (line chart)
		// - Top performing convenios (horizontal bar)
		// - Engagement trend (area chart)
	}

	/**
	 * Format status labels for display
	 */
	function formatStatusLabel(status) {
		const statusMap = {
			'draft': '📝 Borrador',
			'pending_review': '👀 Pendiente Revisión',
			'under_negotiation': '💬 En Negociación',
			'counter_offered': '🔄 Contraoferta',
			'awaiting_approval': '⏳ Esperando Aprobación',
			'pending_supervisor': '👔 Pendiente Supervisor',
			'approved': '✅ Aprobado',
			'active': '🟢 Activo',
			'paused': '⏸️ Pausado',
			'near_expiry': '⚠️ Próximo a Vencer',
			'rejected': '❌ Rechazado',
			'expired': '⏰ Expirado',
			'cancelled': '🚫 Cancelado'
		};

		return statusMap[status] || status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
	}

	/**
	 * Generate colors for status distribution
	 */
	function generateStatusColors(statuses) {
		const colorMap = {
			'draft': 'rgba(149, 165, 166, 0.7)',
			'pending_review': 'rgba(243, 156, 18, 0.7)',
			'under_negotiation': 'rgba(52, 152, 219, 0.7)',
			'counter_offered': 'rgba(155, 89, 182, 0.7)',
			'awaiting_approval': 'rgba(230, 126, 34, 0.7)',
			'pending_supervisor': 'rgba(211, 84, 0, 0.7)',
			'approved': 'rgba(39, 174, 96, 0.7)',
			'active': 'rgba(46, 204, 113, 0.7)',
			'paused': 'rgba(127, 140, 141, 0.7)',
			'near_expiry': 'rgba(243, 156, 18, 0.7)',
			'rejected': 'rgba(231, 76, 60, 0.7)',
			'expired': 'rgba(149, 165, 166, 0.7)',
			'cancelled': 'rgba(192, 57, 43, 0.7)'
		};

		return statuses.map(status => colorMap[status] || 'rgba(150, 150, 150, 0.7)');
	}

	/**
	 * Refresh metrics via AJAX
	 */
	function refreshMetrics() {
		// TODO: Implement AJAX refresh for real-time updates
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'wpcw_refresh_metrics'
			},
			success: function(response) {
				if (response.success) {
					location.reload(); // Simple reload for now
				}
			}
		});
	}

	// Expose refresh function globally
	window.wpcwRefreshMetrics = refreshMetrics;

})(jQuery);
