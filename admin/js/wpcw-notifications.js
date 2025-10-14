/**
 * WPCW Notifications JavaScript
 *
 * Handles real-time notification updates and interactions
 */

(function($) {
    'use strict';

    const WPCWNotifications = {

        /**
         * Initialize notifications system
         */
        init: function() {
            this.bindEvents();
            this.startPolling();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Mark notification as read when clicked
            $(document).on('click', '[data-mark-read]', function(e) {
                const notificationId = $(this).data('mark-read');
                WPCWNotifications.markAsRead(notificationId);

                // If it's a link, let it navigate
                if ($(this).is('a')) {
                    return true;
                }
                e.preventDefault();
            });

            // Handle notification center page interactions
            if ($('.wpcw-notifications-wrap').length) {
                // Handle individual mark as read buttons
                $('.wpcw-notification-action[data-mark-read]').on('click', function(e) {
                    e.preventDefault();
                    const notificationId = $(this).data('mark-read');
                    const $item = $(this).closest('.wpcw-notification-item');

                    WPCWNotifications.markAsRead(notificationId, function() {
                        $item.removeClass('unread');
                        $item.find('.wpcw-notification-status').remove();
                        $(this).remove();
                        WPCWNotifications.updateBadgeCount();
                    });
                });
            }
        },

        /**
         * Mark a notification as read
         *
         * @param {number} notificationId Notification ID
         * @param {function} callback Optional callback function
         */
        markAsRead: function(notificationId, callback) {
            $.ajax({
                url: wpcwNotifications.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wpcw_mark_notification_read',
                    nonce: wpcwNotifications.nonce,
                    notification_id: notificationId
                },
                success: function(response) {
                    if (response.success) {
                        WPCWNotifications.updateBadgeCount(response.data.unread_count);
                        if (typeof callback === 'function') {
                            callback();
                        }
                    }
                }
            });
        },

        /**
         * Mark all notifications as read
         */
        markAllAsRead: function() {
            $.ajax({
                url: wpcwNotifications.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wpcw_mark_all_read',
                    nonce: wpcwNotifications.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WPCWNotifications.updateBadgeCount(0);
                        $('.wpcw-notification-item').removeClass('unread');
                        $('.wpcw-notification-status').remove();
                        $('.wpcw-notification-action[data-mark-read]').remove();
                    }
                }
            });
        },

        /**
         * Update badge count in admin bar
         *
         * @param {number} count New count (optional, will fetch if not provided)
         */
        updateBadgeCount: function(count) {
            const $badge = $('#wp-admin-bar-wpcw-notifications .wpcw-notification-badge');
            const $titleBadge = $('.wpcw-notification-badge-title');

            if (typeof count !== 'undefined') {
                if (count === 0) {
                    $badge.remove();
                    $titleBadge.remove();
                } else {
                    if ($badge.length) {
                        $badge.text(count);
                    } else {
                        $('#wp-admin-bar-wpcw-notifications .ab-item').append(
                            '<span class="wpcw-notification-badge">' + count + '</span>'
                        );
                    }
                    if ($titleBadge.length) {
                        $titleBadge.text(count);
                    }
                }
            } else {
                // Fetch current count
                WPCWNotifications.fetchNotifications(true);
            }
        },

        /**
         * Fetch latest notifications
         *
         * @param {boolean} unreadOnly Only fetch unread notifications
         */
        fetchNotifications: function(unreadOnly) {
            $.ajax({
                url: wpcwNotifications.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wpcw_get_notifications',
                    nonce: wpcwNotifications.nonce,
                    unread_only: unreadOnly || false,
                    limit: 10
                },
                success: function(response) {
                    if (response.success) {
                        WPCWNotifications.updateBadgeCount(response.data.unread_count);
                    }
                }
            });
        },

        /**
         * Start polling for new notifications
         */
        startPolling: function() {
            if (typeof wpcwNotifications === 'undefined') {
                return;
            }

            // Initial fetch
            this.fetchNotifications(true);

            // Poll every 30 seconds
            setInterval(function() {
                WPCWNotifications.fetchNotifications(true);
            }, wpcwNotifications.refreshInterval || 30000);
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        WPCWNotifications.init();
    });

})(jQuery);
