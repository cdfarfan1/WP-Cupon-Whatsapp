/**
 * WP Cupón WhatsApp - Admin JavaScript
 *
 * Main admin JavaScript file for WP Cupón WhatsApp plugin
 *
 * @package WP_Cupon_WhatsApp
 * @version 1.5.0
 */

(function($) {
    'use strict';

    /**
     * Main Admin Object
     */
    const WPCWAdmin = {
        /**
         * Initialize admin functionality
         */
        init: function() {
            this.setupDeleteConfirmations();
            this.setupAjaxHandlers();
            this.setupTooltips();

            console.log('WPCW Admin initialized');
        },

        /**
         * Setup delete confirmation dialogs
         */
        setupDeleteConfirmations: function() {
            $(document).on('click', '.wpcw-delete-btn, .delete', function(e) {
                const confirmMessage = wpcw_admin.strings.confirm_delete || '¿Estás seguro de que quieres eliminar este elemento?';

                if (!confirm(confirmMessage)) {
                    e.preventDefault();
                    return false;
                }
            });
        },

        /**
         * Setup AJAX handlers
         */
        setupAjaxHandlers: function() {
            const self = this;

            // Generic AJAX handler for admin actions
            $(document).on('click', '.wpcw-ajax-action', function(e) {
                e.preventDefault();

                const $button = $(this);
                const action = $button.data('action');
                const itemId = $button.data('item-id');

                if (!action) {
                    console.error('No action specified');
                    return;
                }

                self.performAjaxAction(action, itemId, $button);
            });
        },

        /**
         * Perform AJAX action
         */
        performAjaxAction: function(action, itemId, $button) {
            const originalText = $button.text();
            $button.prop('disabled', true).text(wpcw_admin.strings.loading || 'Cargando...');

            $.ajax({
                url: wpcw_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpcw_admin_action',
                    nonce: wpcw_admin.nonce,
                    item_action: action,
                    item_id: itemId
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        WPCWAdmin.showNotice('success', response.data.message || 'Operación completada');

                        // Reload page if needed
                        if (response.data.reload) {
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        }
                    } else {
                        WPCWAdmin.showNotice('error', response.data.message || wpcw_admin.strings.error);
                    }
                },
                error: function(xhr, status, error) {
                    WPCWAdmin.showNotice('error', wpcw_admin.strings.error || 'Ha ocurrido un error');
                    console.error('AJAX Error:', error);
                },
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Show admin notice
         */
        showNotice: function(type, message) {
            const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
            const $notice = $('<div>', {
                class: 'notice ' + noticeClass + ' is-dismissible',
                html: '<p>' + message + '</p>'
            });

            // Insert notice after h1 or at top of wrap
            if ($('.wrap > h1').length) {
                $notice.insertAfter('.wrap > h1');
            } else {
                $('.wrap').prepend($notice);
            }

            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },

        /**
         * Setup tooltips
         */
        setupTooltips: function() {
            // Add tooltips to elements with title attribute
            $('[title]').tooltip();
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        WPCWAdmin.init();
    });

    // Expose to global scope for external access
    window.WPCWAdmin = WPCWAdmin;

})(jQuery);
