/**
 * WP Cupón WhatsApp - Public JavaScript
 *
 * Main public-facing JavaScript file for WP Cupón WhatsApp plugin
 *
 * @package WP_Cupon_WhatsApp
 * @version 1.5.0
 */

(function($) {
    'use strict';

    /**
     * Main Public Object
     */
    const WPCWPublic = {
        /**
         * Initialize public functionality
         */
        init: function() {
            this.setupCouponRedemption();
            this.setupFormValidation();
            this.setupLoadingStates();

            console.log('WPCW Public initialized');
        },

        /**
         * Setup coupon redemption functionality
         */
        setupCouponRedemption: function() {
            const self = this;

            $(document).on('click', '.wpcw-redeem-coupon-btn', function(e) {
                e.preventDefault();

                const $button = $(this);
                const couponId = $button.data('coupon-id');
                const businessId = $button.data('business-id');

                if (!couponId) {
                    self.showNotice('error', 'Cupón no válido');
                    return;
                }

                self.redeemCoupon(couponId, businessId, $button);
            });
        },

        /**
         * Redeem coupon via AJAX
         */
        redeemCoupon: function(couponId, businessId, $button) {
            const self = this;
            const originalText = $button.text();

            $button.prop('disabled', true).text(wpcw_public.strings.loading || 'Procesando...');

            $.ajax({
                url: wpcw_public.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpcw_public_action',
                    nonce: wpcw_public.nonce,
                    coupon_id: couponId,
                    business_id: businessId,
                    operation: 'redeem_coupon'
                },
                success: function(response) {
                    if (response.success) {
                        self.showNotice('success', 'Cupón canjeado exitosamente');

                        // Redirect to WhatsApp if URL provided
                        if (response.data.whatsapp_url) {
                            setTimeout(function() {
                                window.location.href = response.data.whatsapp_url;
                            }, 1500);
                        }
                    } else {
                        self.showNotice('error', response.data.message || wpcw_public.strings.error);
                    }
                },
                error: function(xhr, status, error) {
                    self.showNotice('error', wpcw_public.strings.error || 'Error al procesar el cupón');
                    console.error('AJAX Error:', error);
                },
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Setup form validation
         */
        setupFormValidation: function() {
            $('.wpcw-form').on('submit', function(e) {
                const $form = $(this);
                const $requiredFields = $form.find('[required]');
                let isValid = true;

                $requiredFields.each(function() {
                    const $field = $(this);
                    if (!$field.val().trim()) {
                        $field.addClass('wpcw-field-error');
                        isValid = false;
                    } else {
                        $field.removeClass('wpcw-field-error');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    WPCWPublic.showNotice('error', 'Por favor completa todos los campos requeridos');
                }
            });

            // Remove error class on input
            $(document).on('input', '.wpcw-field-error', function() {
                $(this).removeClass('wpcw-field-error');
            });
        },

        /**
         * Setup loading states for forms
         */
        setupLoadingStates: function() {
            $('.wpcw-form').on('submit', function() {
                const $form = $(this);
                const $submitBtn = $form.find('[type="submit"]');
                const originalText = $submitBtn.val() || $submitBtn.text();

                $submitBtn
                    .prop('disabled', true)
                    .val(wpcw_public.strings.loading || 'Cargando...')
                    .text(wpcw_public.strings.loading || 'Cargando...');

                // Re-enable after 10 seconds as fallback
                setTimeout(function() {
                    $submitBtn
                        .prop('disabled', false)
                        .val(originalText)
                        .text(originalText);
                }, 10000);
            });
        },

        /**
         * Show notice message
         */
        showNotice: function(type, message) {
            const noticeClass = type === 'success' ? 'wpcw-notice-success' : 'wpcw-notice-error';
            const iconClass = type === 'success' ? '✓' : '✕';

            const $notice = $('<div>', {
                class: 'wpcw-notice ' + noticeClass,
                html: '<span class="wpcw-notice-icon">' + iconClass + '</span><span class="wpcw-notice-message">' + message + '</span>'
            });

            // Remove existing notices
            $('.wpcw-notice').remove();

            // Insert notice
            $('body').prepend($notice);

            // Show with animation
            setTimeout(function() {
                $notice.addClass('wpcw-notice-show');
            }, 10);

            // Auto-hide after 5 seconds
            setTimeout(function() {
                $notice.removeClass('wpcw-notice-show');
                setTimeout(function() {
                    $notice.remove();
                }, 300);
            }, 5000);
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        WPCWPublic.init();
    });

    // Expose to global scope
    window.WPCWPublic = WPCWPublic;

})(jQuery);
