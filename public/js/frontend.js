/**
 * WP Cupón WhatsApp - Frontend JavaScript
 * Funcionalidad para shortcodes y páginas de beneficiarios
 */

(function($) {
    'use strict';

    // Objeto principal
    var WPCWFrontend = {

        /**
         * Inicializar
         */
        init: function() {
            this.handleCouponRedemption();
            this.handleFormValidation();
            this.handleInstitutionSelect();
            this.enhanceUI();
        },

        /**
         * Manejar canje de cupones
         */
        handleCouponRedemption: function() {
            $(document).on('click', '.wpcw-use-coupon-btn, .wpcw-canjear-cupon-btn', function(e) {
                e.preventDefault();

                var $btn = $(this);
                var couponId = $btn.data('coupon-id');

                if (!couponId) {
                    alert(wpcw_ajax.strings.redemption_error);
                    return;
                }

                // Confirmación
                if (!confirm(wpcw_ajax.strings.confirm_redemption)) {
                    return;
                }

                // Deshabilitar botón
                $btn.prop('disabled', true).addClass('wpcw-loading');

                // AJAX request
                $.ajax({
                    url: wpcw_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wpcw_redeem_coupon',
                        coupon_id: couponId,
                        nonce: wpcw_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            // Mostrar mensaje de éxito
                            WPCWFrontend.showMessage(wpcw_ajax.strings.redemption_success, 'success');

                            // Si hay URL de WhatsApp, redirigir
                            if (response.data && response.data.whatsapp_url) {
                                setTimeout(function() {
                                    window.location.href = response.data.whatsapp_url;
                                }, 1000);
                            } else {
                                // Recargar página después de 2 segundos
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            }
                        } else {
                            WPCWFrontend.showMessage(response.data || wpcw_ajax.strings.redemption_error, 'error');
                            $btn.prop('disabled', false).removeClass('wpcw-loading');
                        }
                    },
                    error: function() {
                        WPCWFrontend.showMessage(wpcw_ajax.strings.redemption_error, 'error');
                        $btn.prop('disabled', false).removeClass('wpcw-loading');
                    }
                });
            });
        },

        /**
         * Validación de formularios
         */
        handleFormValidation: function() {
            // Validar formulario de registro
            $('.wpcw-registration-form').on('submit', function(e) {
                var $form = $(this);
                var $email = $form.find('input[name="wpcw_email"]');
                var $password = $form.find('input[name="wpcw_password"]');
                var $institution = $form.find('select[name="wpcw_institution_id"]');
                var errors = [];

                // Validar email
                if (!$email.val() || !WPCWFrontend.isValidEmail($email.val())) {
                    errors.push('Por favor, introduce un email válido.');
                    $email.addClass('wpcw-error-field');
                } else {
                    $email.removeClass('wpcw-error-field');
                }

                // Validar contraseña
                if (!$password.val() || $password.val().length < 6) {
                    errors.push('La contraseña debe tener al menos 6 caracteres.');
                    $password.addClass('wpcw-error-field');
                } else {
                    $password.removeClass('wpcw-error-field');
                }

                // Validar institución
                if (!$institution.val()) {
                    errors.push('Debes seleccionar una institución.');
                    $institution.addClass('wpcw-error-field');
                } else {
                    $institution.removeClass('wpcw-error-field');
                }

                if (errors.length > 0) {
                    e.preventDefault();
                    WPCWFrontend.showMessage(errors.join('<br>'), 'error');
                    return false;
                }
            });

            // Validar formulario de adhesión
            $('.wpcw-adhesion-form').on('submit', function(e) {
                var $form = $(this);
                var requiredFields = $form.find('[required]');
                var errors = [];

                requiredFields.each(function() {
                    var $field = $(this);
                    if (!$field.val()) {
                        errors.push('Por favor, completa todos los campos requeridos.');
                        $field.addClass('wpcw-error-field');
                    } else {
                        $field.removeClass('wpcw-error-field');
                    }
                });

                if (errors.length > 0) {
                    e.preventDefault();
                    WPCWFrontend.showMessage(errors[0], 'error');
                    return false;
                }
            });
        },

        /**
         * Manejar selección de institución
         */
        handleInstitutionSelect: function() {
            $('select[name="wpcw_institution_id"]').on('change', function() {
                var $select = $(this);
                var $form = $select.closest('form');
                var institutionId = $select.val();

                if (institutionId) {
                    // Aquí se puede agregar lógica adicional
                    // Por ejemplo, cargar información de la institución
                    $select.removeClass('wpcw-error-field');
                }
            });
        },

        /**
         * Mejoras de UI
         */
        enhanceUI: function() {
            // Agregar efecto de focus a campos de formulario
            $('.wpcw-form-group input, .wpcw-form-group select, .wpcw-form-group textarea').on('focus', function() {
                $(this).closest('.wpcw-form-group').addClass('wpcw-focused');
            }).on('blur', function() {
                $(this).closest('.wpcw-form-group').removeClass('wpcw-focused');
            });

            // Contar caracteres en textareas
            $('.wpcw-form-group textarea[maxlength]').each(function() {
                var $textarea = $(this);
                var maxLength = $textarea.attr('maxlength');
                var $counter = $('<div class="char-counter"></div>');
                $textarea.after($counter);

                $textarea.on('input', function() {
                    var remaining = maxLength - $(this).val().length;
                    $counter.text(remaining + ' caracteres restantes');
                });

                $textarea.trigger('input');
            });

            // Smooth scroll para anclas
            $('a[href^="#"]').on('click', function(e) {
                var target = $(this.hash);
                if (target.length) {
                    e.preventDefault();
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100
                    }, 500);
                }
            });
        },

        /**
         * Mostrar mensajes
         */
        showMessage: function(message, type) {
            type = type || 'info';

            var $message = $('<div class="wpcw-message wpcw-' + type + '">' + message + '</div>');

            // Buscar contenedor de mensajes o agregar al principio del body
            var $container = $('.wpcw-messages-container');
            if (!$container.length) {
                $container = $('<div class="wpcw-messages-container"></div>');
                if ($('.wpcw-beneficiary-portal, .wpcw-registration-form, .wpcw-adhesion-form').length) {
                    $('.wpcw-beneficiary-portal, .wpcw-registration-form, .wpcw-adhesion-form').first().prepend($container);
                } else {
                    $('body').prepend($container);
                }
            }

            $container.empty().append($message);

            // Scroll al mensaje
            $('html, body').animate({
                scrollTop: $message.offset().top - 100
            }, 300);

            // Auto-ocultar después de 5 segundos para mensajes de éxito
            if (type === 'success') {
                setTimeout(function() {
                    $message.fadeOut(500, function() {
                        $(this).remove();
                    });
                }, 5000);
            }
        },

        /**
         * Validar email
         */
        isValidEmail: function(email) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
    };

    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        WPCWFrontend.init();
    });

})(jQuery);
