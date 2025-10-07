/**
 * WP Cupón WhatsApp Dashboard JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize dashboard components
        initializeDashboard();

        // Auto-refresh metrics every 5 minutes
        setInterval(refreshMetrics, 300000);
    });

    /**
     * Initialize dashboard components
     */
    function initializeDashboard() {
        // Initialize charts if Chart.js is available
        if (typeof Chart !== 'undefined') {
            initializeCharts();
        }

        // Initialize tooltips
        initializeTooltips();

        // Initialize quick actions
        initializeQuickActions();
    }

    /**
     * Initialize charts
     */
    function initializeCharts() {
        // Charts are initialized inline in the PHP render methods
        // This function can be used for additional chart initialization if needed
        console.log('WPCW Dashboard: Charts initialized');
    }

    /**
     * Initialize tooltips
     */
    function initializeTooltips() {
        // Add tooltips to metric cards
        $('.wpcw-metric-card').each(function() {
            var $card = $(this);
            var title = $card.find('h3').text();
            var value = $card.find('.wpcw-metric-value').text();

            $card.attr('title', title + ': ' + value);
        });

        // Add tooltips to notification items
        $('.wpcw-notification-item').each(function() {
            var $item = $(this);
            var time = $item.find('time').text();

            $item.attr('title', 'Hace ' + time);
        });
    }

    /**
     * Initialize quick actions
     */
    function initializeQuickActions() {
        // Add click handlers for quick actions
        $('.wpcw-card .button').on('click', function(e) {
            var $button = $(this);
            var href = $button.attr('href');

            // Add loading state
            $button.addClass('loading').prop('disabled', true);
            $button.html('<span class="dashicons dashicons-update spin"></span> Cargando...');

            // Navigate after a short delay to show loading state
            setTimeout(function() {
                window.location.href = href;
            }, 500);
        });
    }

    /**
     * Refresh dashboard metrics
     */
    function refreshMetrics() {
        // AJAX call to refresh metrics
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wpcw_refresh_dashboard_metrics'
            },
            success: function(response) {
                if (response.success) {
                    updateMetricsDisplay(response.data.metrics);
                    updateChartData(response.data.chart_data);
                    showRefreshNotification();
                }
            },
            error: function() {
                console.error('WPCW Dashboard: Error refreshing metrics');
            }
        });
    }

    /**
     * Update metrics display
     */
    function updateMetricsDisplay(metrics) {
        // Update metric values
        $('.wpcw-metric-value').each(function() {
            var $value = $(this);
            var metricType = $value.closest('.wpcw-metric-card').find('h3').text().toLowerCase();

            // Map metric types to data keys
            var dataKey = '';
            switch(metricType) {
                case 'solicitudes':
                    dataKey = 'applications.pending';
                    break;
                case 'comercios':
                    dataKey = 'businesses.total';
                    break;
                case 'cupones':
                    dataKey = 'coupons.wpcw_enabled';
                    break;
                case 'canjes':
                    dataKey = 'redemptions.confirmed';
                    break;
                case 'usuarios':
                    dataKey = 'users.with_whatsapp';
                    break;
                case 'instituciones':
                    dataKey = 'institutions.total';
                    break;
            }

            if (dataKey && metrics[dataKey] !== undefined) {
                animateValueChange($value, metrics[dataKey]);
            }
        });
    }

    /**
     * Update chart data
     */
    function updateChartData(chartData) {
        // Update chart if it exists
        if (window.wpcwChart && typeof window.wpcwChart.update === 'function') {
            window.wpcwChart.data = chartData;
            window.wpcwChart.update();
        }
    }

    /**
     * Animate value change
     */
    function animateValueChange($element, newValue) {
        var currentValue = parseInt($element.text().replace(/[^\d]/g, ''));
        var difference = newValue - currentValue;

        if (difference === 0) return;

        var duration = 1000; // 1 second
        var steps = 20;
        var stepValue = difference / steps;
        var currentStep = 0;

        var animation = setInterval(function() {
            currentStep++;
            var animatedValue = Math.round(currentValue + (stepValue * currentStep));

            $element.text(number_format(animatedValue));

            if (currentStep >= steps) {
                clearInterval(animation);
                $element.text(number_format(newValue));
            }
        }, duration / steps);
    }

    /**
     * Show refresh notification
     */
    function showRefreshNotification() {
        // Create and show a temporary notification
        var $notification = $('<div class="wpcw-refresh-notification">')
            .text('Dashboard actualizado')
            .css({
                position: 'fixed',
                top: '40px',
                right: '20px',
                background: '#46b450',
                color: 'white',
                padding: '10px 20px',
                borderRadius: '4px',
                zIndex: 9999,
                opacity: 0
            })
            .appendTo('body');

        $notification.animate({ opacity: 1 }, 300);

        setTimeout(function() {
            $notification.animate({ opacity: 0 }, 300, function() {
                $notification.remove();
            });
        }, 3000);
    }

    /**
     * Format numbers
     */
    function number_format(number) {
        return new Intl.NumberFormat('es-AR').format(number);
    }

    /**
     * Handle dashboard errors
     */
    function handleDashboardError(error) {
        console.error('WPCW Dashboard Error:', error);

        // Show error notification
        var $error = $('<div class="wpcw-error-notification">')
            .html('<strong>Error:</strong> ' + error.message)
            .css({
                position: 'fixed',
                top: '40px',
                right: '20px',
                background: '#dc3232',
                color: 'white',
                padding: '10px 20px',
                borderRadius: '4px',
                zIndex: 9999,
                maxWidth: '300px'
            })
            .appendTo('body');

        setTimeout(function() {
            $error.fadeOut(300, function() {
                $error.remove();
            });
        }, 5000);
    }

    // Expose functions globally for AJAX callbacks
    window.WPCW_Dashboard = {
        refreshMetrics: refreshMetrics,
        updateMetricsDisplay: updateMetricsDisplay,
        handleError: handleDashboardError
    };

})(jQuery);