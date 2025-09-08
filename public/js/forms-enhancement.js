/**
 * WP Cupón WhatsApp - Forms Enhancement
 *
 * Mejora la experiencia de usuario en los formularios
 */
(function ($) {
    'use strict';

    $(document).ready(function () {
        
        // Mejorar formulario de solicitud de adhesión
        enhanceAdhesionForm();
        
        // Validación en tiempo real
        addRealTimeValidation();
        
        // Mejorar accesibilidad
        improveAccessibility();
    });

    /**
     * Mejorar el formulario de solicitud de adhesión
     */
    function enhanceAdhesionForm() {
        var $form = $('#wpcw-solicitud-adhesion-form');
        
        if ($form.length === 0) {
            return;
        }

        // Agregar clases CSS para mejor styling
        $form.addClass('wpcw-enhanced-form');
        
        // Mejorar el fieldset de tipo de solicitante
        var $fieldset = $form.find('fieldset');
        if ($fieldset.length > 0) {
            $fieldset.addClass('wpcw-applicant-type-fieldset');
        }

        // Mejorar los campos de entrada
        $form.find('input[type="text"], input[type="email"], input[type="tel"]').each(function () {
            var $input = $(this);
            var $label = $input.prev('label');
            
            // Agregar placeholder si no existe
            if (!$input.attr('placeholder') && $label.length > 0) {
                $input.attr('placeholder', $label.text().replace(' *', ''));
            }
            
            // Agregar clases para styling
            $input.addClass('wpcw-form-input');
        });

        // Mejorar textareas
        $form.find('textarea').each(function () {
            var $textarea = $(this);
            var $label = $textarea.prev('label');
            
            // Agregar placeholder si no existe
            if (!$textarea.attr('placeholder') && $label.length > 0) {
                $textarea.attr('placeholder', $label.text().replace(' *', ''));
            }
            
            // Agregar clases para styling
            $textarea.addClass('wpcw-form-textarea');
        });

        // Mejorar botón de envío
        var $submitBtn = $form.find('input[type="submit"]');
        if ($submitBtn.length > 0) {
            $submitBtn.addClass('wpcw-submit-button');
        }
    }

    /**
     * Agregar validación en tiempo real
     */
    function addRealTimeValidation() {
        var $form = $('#wpcw-solicitud-adhesion-form');
        
        if ($form.length === 0) {
            return;
        }

        // Validación de email
        $form.find('input[type="email"]').on('blur', function () {
            var $input = $(this);
            var email = $input.val();
            
            if (email && !isValidEmail(email)) {
                showFieldError($input, 'Por favor, introduce un email válido.');
            } else {
                clearFieldError($input);
            }
        });

        // Validación de CUIT
        $form.find('input[name="wpcw_cuit"]').on('blur', function () {
            var $input = $(this);
            var cuit = $input.val().replace(/\D/g, ''); // Solo números
            
            if (cuit && (cuit.length < 10 || cuit.length > 11)) {
                showFieldError($input, 'El CUIT debe tener entre 10 y 11 dígitos.');
            } else {
                clearFieldError($input);
            }
        });

        // Validación de WhatsApp
        $form.find('input[name="wpcw_whatsapp"]').on('blur', function () {
            var $input = $(this);
            var whatsapp = $input.val();
            
            if (whatsapp && !isValidWhatsApp(whatsapp)) {
                showFieldError($input, 'Por favor, introduce un número de WhatsApp válido.');
            } else {
                clearFieldError($input);
            }
        });

        // Validación de campos requeridos
        $form.find('input[required], textarea[required]').on('blur', function () {
            var $input = $(this);
            
            if (!$input.val().trim()) {
                showFieldError($input, 'Este campo es obligatorio.');
            } else {
                clearFieldError($input);
            }
        });
    }

    /**
     * Mejorar accesibilidad
     */
    function improveAccessibility() {
        var $form = $('#wpcw-solicitud-adhesion-form');
        
        if ($form.length === 0) {
            return;
        }

        // Agregar aria-labels a campos sin label visible
        $form.find('input, textarea').each(function () {
            var $input = $(this);
            var $label = $input.prev('label');
            
            if ($label.length > 0 && !$input.attr('aria-label')) {
                $input.attr('aria-label', $label.text().replace(' *', ''));
            }
        });

        // Mejorar navegación con teclado
        $form.find('input, textarea, button').on('keydown', function (e) {
            if (e.key === 'Enter' && $(this).is('input[type="text"], input[type="email"], input[type="tel"]')) {
                e.preventDefault();
                var $next = $(this).closest('p').next('p').find('input, textarea, button');
                if ($next.length > 0) {
                    $next.focus();
                }
            }
        });
    }

    /**
     * Validar email
     */
    function isValidEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Validar WhatsApp
     */
    function isValidWhatsApp(whatsapp) {
        var whatsappRegex = /^[0-9\s\+\(\)-]+$/;
        return whatsappRegex.test(whatsapp);
    }

    /**
     * Mostrar error en campo
     */
    function showFieldError($input, message) {
        clearFieldError($input);
        
        $input.addClass('wpcw-field-error');
        $input.attr('aria-invalid', 'true');
        
        var $error = $('<div class="wpcw-field-error-message">' + message + '</div>');
        $input.after($error);
    }

    /**
     * Limpiar error de campo
     */
    function clearFieldError($input) {
        $input.removeClass('wpcw-field-error');
        $input.removeAttr('aria-invalid');
        $input.next('.wpcw-field-error-message').remove();
    }

})(jQuery);
