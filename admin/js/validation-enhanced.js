/**
 * Validaciones Mejoradas JavaScript - WP Cupón WhatsApp
 * 
 * Maneja validación de email y WhatsApp usando wa.me
 * Corrige errores de carga de teléfono
 *
 * @package WP_Cupon_WhatsApp
 * @version 1.4.0
 */

(function($) {
    'use strict';

    // Variables globales
    let validationCache = {};
    let validationTimeouts = {};

    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        initEnhancedValidation();
    });

    /**
     * Inicializar validaciones mejoradas
     */
    function initEnhancedValidation() {
        bindValidationEvents();
        fixPhoneLoadingIssues();
        initEmailSuggestions();
        initWhatsAppTesting();
    }

    /**
     * Vincular eventos de validación
     */
    function bindValidationEvents() {
        // Validación de email mejorada
        $(document).on('input blur', 'input[type="email"], input[data-validation*="email"]', function() {
            const $field = $(this);
            debounceValidation($field, validateEmailField, 500);
        });

        // Validación de WhatsApp mejorada
        $(document).on('input blur', 'input[data-validation*="whatsapp"], input[name*="whatsapp"]', function() {
            const $field = $(this);
            debounceValidation($field, validateWhatsAppField, 500);
        });

        // Formateo automático de teléfono
        $(document).on('input', 'input[type="tel"], input[data-validation*="phone"], input[data-validation*="whatsapp"]', function() {
            const $field = $(this);
            formatPhoneInput($field);
        });

        // Probar enlace de WhatsApp
        $(document).on('click', '.wpcw-test-whatsapp', function(e) {
            e.preventDefault();
            const $button = $(this);
            const $field = $button.siblings('input[data-validation*="whatsapp"]');
            testWhatsAppLink($field, $button);
        });

        // Aceptar sugerencia de email
        $(document).on('click', '.wpcw-email-suggestion', function(e) {
            e.preventDefault();
            const $suggestion = $(this);
            const $field = $suggestion.closest('.wpcw-field-container').find('input[type="email"]');
            const suggestedEmail = $suggestion.data('email');
            
            $field.val(suggestedEmail);
            $suggestion.closest('.wpcw-email-suggestions').remove();
            validateEmailField($field);
        });
    }

    /**
     * Corregir problemas de carga de teléfono
     */
    function fixPhoneLoadingIssues() {
        // Verificar campos de teléfono al cargar la página
        $('input[data-validation*="phone"], input[data-validation*="whatsapp"]').each(function() {
            const $field = $(this);
            let value = $field.val();
            
            // Corregir valores null o undefined
            if (value === null || value === undefined || value === 'null') {
                $field.val('');
            }
            
            // Corregir arrays serializados (error común)
            if (typeof value === 'string' && value.startsWith('[') && value.endsWith(']')) {
                try {
                    const parsed = JSON.parse(value);
                    if (Array.isArray(parsed) && parsed.length > 0) {
                        $field.val(parsed[0]);
                    } else {
                        $field.val('');
                    }
                } catch (e) {
                    $field.val('');
                }
            }
        });
    }

    /**
     * Validar campo de email
     */
    function validateEmailField($field) {
        const email = $field.val().trim();
        const fieldId = $field.attr('id') || $field.attr('name');
        
        if (!email) {
            setFieldState($field, 'empty');
            return;
        }

        // Verificar caché
        if (validationCache[`email_${email}`]) {
            const cached = validationCache[`email_${email}`];
            setFieldState($field, cached.valid ? 'valid' : 'invalid', cached.message);
            if (cached.suggestions) {
                showEmailSuggestions($field, cached.suggestions);
            }
            return;
        }

        setFieldState($field, 'validating');

        $.ajax({
            url: wpcwValidation.ajaxurl,
            type: 'POST',
            data: {
                action: 'wpcw_validate_email',
                nonce: wpcwValidation.nonce,
                email: email
            },
            success: function(response) {
                // Guardar en caché
                validationCache[`email_${email}`] = response;
                
                if (response.valid) {
                    setFieldState($field, 'valid', response.message);
                    hideEmailSuggestions($field);
                } else {
                    setFieldState($field, 'invalid', response.message);
                    if (response.suggestions && response.suggestions.length > 0) {
                        showEmailSuggestions($field, response.suggestions);
                    }
                }
            },
            error: function() {
                setFieldState($field, 'error', 'Error al validar email');
            }
        });
    }

    /**
     * Validar campo de WhatsApp
     */
    function validateWhatsAppField($field) {
        const phone = $field.val().trim();
        const fieldId = $field.attr('id') || $field.attr('name');
        
        if (!phone) {
            setFieldState($field, 'empty');
            hideWhatsAppActions($field);
            return;
        }

        // Verificar caché
        if (validationCache[`whatsapp_${phone}`]) {
            const cached = validationCache[`whatsapp_${phone}`];
            setFieldState($field, cached.valid ? 'valid' : 'invalid', cached.message);
            if (cached.valid) {
                showWhatsAppActions($field, cached.wa_link);
            } else {
                hideWhatsAppActions($field);
            }
            return;
        }

        setFieldState($field, 'validating');

        $.ajax({
            url: wpcwValidation.ajaxurl,
            type: 'POST',
            data: {
                action: 'wpcw_validate_whatsapp',
                nonce: wpcwValidation.nonce,
                phone: phone
            },
            success: function(response) {
                // Guardar en caché
                validationCache[`whatsapp_${phone}`] = response;
                
                if (response.valid) {
                    setFieldState($field, 'valid', response.message);
                    $field.val(formatPhoneDisplay(response.formatted));
                    showWhatsAppActions($field, response.wa_link);
                } else {
                    setFieldState($field, 'invalid', response.message);
                    hideWhatsAppActions($field);
                }
            },
            error: function() {
                setFieldState($field, 'error', 'Error al validar WhatsApp');
                hideWhatsAppActions($field);
            }
        });
    }

    /**
     * Formatear entrada de teléfono
     */
    function formatPhoneInput($field) {
        let value = $field.val();
        
        // Solo números, + y espacios
        value = value.replace(/[^0-9+\s-]/g, '');
        
        // Formateo específico para Argentina
        if (value.length > 0) {
            // Remover espacios y guiones para procesamiento
            const cleanValue = value.replace(/[\s-]/g, '');
            
            // Agregar +54 si no está presente
            if (!cleanValue.startsWith('+54') && !cleanValue.startsWith('54')) {
                if (cleanValue.startsWith('0')) {
                    value = '+54 ' + cleanValue.substring(1);
                } else if (cleanValue.startsWith('9')) {
                    value = '+54 ' + cleanValue;
                } else {
                    value = '+54 9 ' + cleanValue;
                }
            }
        }
        
        $field.val(value);
    }

    /**
     * Formatear teléfono para mostrar
     */
    function formatPhoneDisplay(phone) {
        // Formato: +54 9 11 1234-5678
        if (phone.startsWith('54')) {
            const numbers = phone.substring(2);
            if (numbers.length >= 10) {
                return `+54 ${numbers.substring(0, 1)} ${numbers.substring(1, 3)} ${numbers.substring(3, 7)}-${numbers.substring(7)}`;
            }
        }
        return phone;
    }

    /**
     * Establecer estado del campo
     */
    function setFieldState($field, state, message = '') {
        const $container = $field.closest('.wpcw-field-container');
        const $feedback = $container.find('.wpcw-field-feedback');
        
        // Remover clases de estado previas
        $field.removeClass('wpcw-valid wpcw-invalid wpcw-validating wpcw-error wpcw-empty');
        $container.removeClass('wpcw-field-valid wpcw-field-invalid wpcw-field-validating wpcw-field-error wpcw-field-empty');
        
        // Agregar nueva clase de estado
        $field.addClass(`wpcw-${state}`);
        $container.addClass(`wpcw-field-${state}`);
        
        // Actualizar mensaje de feedback
        if (message) {
            $feedback.html(message).show();
        } else {
            $feedback.hide();
        }
        
        // Agregar icono de estado
        updateFieldIcon($field, state);
    }

    /**
     * Actualizar icono del campo
     */
    function updateFieldIcon($field, state) {
        const $container = $field.closest('.wpcw-field-container');
        let $icon = $container.find('.wpcw-field-icon');
        
        if ($icon.length === 0) {
            $icon = $('<span class="wpcw-field-icon"></span>');
            $field.after($icon);
        }
        
        const icons = {
            'valid': '✓',
            'invalid': '✗',
            'validating': '⟳',
            'error': '!',
            'empty': ''
        };
        
        $icon.text(icons[state] || '').attr('title', getStateMessage(state));
    }

    /**
     * Obtener mensaje de estado
     */
    function getStateMessage(state) {
        const messages = {
            'valid': 'Válido',
            'invalid': 'Inválido',
            'validating': 'Validando...',
            'error': 'Error de validación',
            'empty': ''
        };
        
        return messages[state] || '';
    }

    /**
     * Mostrar sugerencias de email
     */
    function showEmailSuggestions($field, suggestions) {
        hideEmailSuggestions($field);
        
        if (suggestions.length === 0) return;
        
        const $container = $field.closest('.wpcw-field-container');
        const $suggestions = $('<div class="wpcw-email-suggestions"></div>');
        
        $suggestions.append('<p class="wpcw-suggestions-title">¿Quisiste decir?</p>');
        
        suggestions.forEach(function(suggestion) {
            const $suggestion = $(`<button type="button" class="wpcw-email-suggestion" data-email="${suggestion}">${suggestion}</button>`);
            $suggestions.append($suggestion);
        });
        
        $container.append($suggestions);
    }

    /**
     * Ocultar sugerencias de email
     */
    function hideEmailSuggestions($field) {
        const $container = $field.closest('.wpcw-field-container');
        $container.find('.wpcw-email-suggestions').remove();
    }

    /**
     * Mostrar acciones de WhatsApp
     */
    function showWhatsAppActions($field, waLink) {
        hideWhatsAppActions($field);
        
        const $container = $field.closest('.wpcw-field-container');
        const $actions = $('<div class="wpcw-whatsapp-actions"></div>');
        
        const $testButton = $(`<button type="button" class="wpcw-test-whatsapp button button-secondary">Probar WhatsApp</button>`);
        const $openButton = $(`<a href="${waLink}" target="_blank" class="wpcw-open-whatsapp button button-primary">Abrir WhatsApp</a>`);
        
        $actions.append($testButton).append($openButton);
        $container.append($actions);
    }

    /**
     * Ocultar acciones de WhatsApp
     */
    function hideWhatsAppActions($field) {
        const $container = $field.closest('.wpcw-field-container');
        $container.find('.wpcw-whatsapp-actions').remove();
    }

    /**
     * Probar enlace de WhatsApp
     */
    function testWhatsAppLink($field, $button) {
        const phone = $field.val().trim();
        
        if (!phone) {
            showNotification('Ingrese un número de WhatsApp primero', 'error');
            return;
        }
        
        $button.prop('disabled', true).text('Probando...');
        
        $.ajax({
            url: wpcwValidation.ajaxurl,
            type: 'POST',
            data: {
                action: 'wpcw_test_whatsapp_link',
                nonce: wpcwValidation.nonce,
                phone: phone,
                message: 'Prueba de conexión desde WP Cupón WhatsApp'
            },
            success: function(response) {
                if (response.success) {
                    window.open(response.data.link, '_blank');
                    showNotification('Enlace de WhatsApp abierto correctamente', 'success');
                } else {
                    showNotification(response.data.message, 'error');
                }
            },
            error: function() {
                showNotification('Error al probar el enlace de WhatsApp', 'error');
            },
            complete: function() {
                $button.prop('disabled', false).text('Probar WhatsApp');
            }
        });
    }

    /**
     * Inicializar sugerencias de email
     */
    function initEmailSuggestions() {
        // Agregar contenedores para sugerencias si no existen
        $('input[type="email"], input[data-validation*="email"]').each(function() {
            const $field = $(this);
            if (!$field.closest('.wpcw-field-container').length) {
                $field.wrap('<div class="wpcw-field-container"></div>');
            }
            
            const $container = $field.closest('.wpcw-field-container');
            if (!$container.find('.wpcw-field-feedback').length) {
                $container.append('<div class="wpcw-field-feedback"></div>');
            }
        });
    }

    /**
     * Inicializar testing de WhatsApp
     */
    function initWhatsAppTesting() {
        // Agregar contenedores para acciones si no existen
        $('input[data-validation*="whatsapp"], input[name*="whatsapp"]').each(function() {
            const $field = $(this);
            if (!$field.closest('.wpcw-field-container').length) {
                $field.wrap('<div class="wpcw-field-container"></div>');
            }
            
            const $container = $field.closest('.wpcw-field-container');
            if (!$container.find('.wpcw-field-feedback').length) {
                $container.append('<div class="wpcw-field-feedback"></div>');
            }
        });
    }

    /**
     * Debounce para validación
     */
    function debounceValidation($field, validationFunction, delay) {
        const fieldId = $field.attr('id') || $field.attr('name');
        
        if (validationTimeouts[fieldId]) {
            clearTimeout(validationTimeouts[fieldId]);
        }
        
        validationTimeouts[fieldId] = setTimeout(function() {
            validationFunction($field);
        }, delay);
    }

    /**
     * Mostrar notificación
     */
    function showNotification(message, type = 'info') {
        const $notification = $(`<div class="wpcw-notification wpcw-notification-${type}">${message}</div>`);
        
        $('body').append($notification);
        
        setTimeout(function() {
            $notification.addClass('wpcw-notification-show');
        }, 100);
        
        setTimeout(function() {
            $notification.removeClass('wpcw-notification-show');
            setTimeout(function() {
                $notification.remove();
            }, 300);
        }, 3000);
    }

    // Exponer funciones globalmente
    window.wpcwValidationEnhanced = {
        validateEmail: validateEmailField,
        validateWhatsApp: validateWhatsAppField,
        formatPhone: formatPhoneInput,
        testWhatsApp: testWhatsAppLink
    };

})(jQuery);

/**
 * Estilos CSS inyectados
 */
if (!document.getElementById('wpcw-validation-enhanced-styles')) {
    const style = document.createElement('style');
    style.id = 'wpcw-validation-enhanced-styles';
    style.textContent = `
        .wpcw-field-container {
            position: relative;
        }
        
        .wpcw-field-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-weight: bold;
            pointer-events: none;
        }
        
        .wpcw-valid .wpcw-field-icon {
            color: #46b450;
        }
        
        .wpcw-invalid .wpcw-field-icon {
            color: #dc3232;
        }
        
        .wpcw-validating .wpcw-field-icon {
            color: #0073aa;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: translateY(-50%) rotate(0deg); }
            100% { transform: translateY(-50%) rotate(360deg); }
        }
        
        .wpcw-email-suggestions {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 5px;
            padding: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .wpcw-suggestions-title {
            margin: 0 0 5px 0;
            font-size: 12px;
            color: #666;
        }
        
        .wpcw-email-suggestion {
            background: #f7f7f7;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 5px 10px;
            margin: 2px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .wpcw-email-suggestion:hover {
            background: #0073aa;
            color: white;
        }
        
        .wpcw-whatsapp-actions {
            margin-top: 5px;
        }
        
        .wpcw-whatsapp-actions .button {
            margin-right: 5px;
            font-size: 12px;
            padding: 3px 8px;
            height: auto;
        }
        
        .wpcw-notification {
            position: fixed;
            top: 32px;
            right: 20px;
            background: #fff;
            border-left: 4px solid #0073aa;
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
            padding: 12px;
            margin: 5px 15px 2px;
            z-index: 9999;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        }
        
        .wpcw-notification-show {
            opacity: 1;
            transform: translateX(0);
        }
        
        .wpcw-notification-success {
            border-left-color: #46b450;
        }
        
        .wpcw-notification-error {
            border-left-color: #dc3232;
        }
        
        .wpcw-field-feedback {
            font-size: 12px;
            margin-top: 5px;
        }
        
        .wpcw-field-valid .wpcw-field-feedback {
            color: #46b450;
        }
        
        .wpcw-field-invalid .wpcw-field-feedback {
            color: #dc3232;
        }
    `;
    document.head.appendChild(style);
}