jQuery(document).ready(function($) {
    // Validación de formularios
    function validateForm($form) {
        let isValid = true;
        const messages = window.wpcw_forms.messages;

        // Limpiar mensajes de error previos
        $form.find('.error-message').remove();
        $form.find('.has-error').removeClass('has-error');

        // Validar campos requeridos
        $form.find('[required]').each(function() {
            if (!$(this).val()) {
                $(this).closest('.wpcw-form-group').addClass('has-error')
                    .append(`<span class="error-message">${messages.required}</span>`);
                isValid = false;
            }
        });

        // Validar email
        $form.find('input[type="email"]').each(function() {
            const email = $(this).val();
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                $(this).closest('.wpcw-form-group').addClass('has-error')
                    .append(`<span class="error-message">${messages.email}</span>`);
                isValid = false;
            }
        });

        // Validar teléfono
        $form.find('input[type="tel"]').each(function() {
            const phone = $(this).val();
            if (phone && !/^\+?[\d\s-]{10,}$/.test(phone)) {
                $(this).closest('.wpcw-form-group').addClass('has-error')
                    .append(`<span class="error-message">${messages.phone}</span>`);
                isValid = false;
            }
        });

        return isValid;
    }

    // Manejar envío del formulario de comercio
    $('#wpcw-business-registration').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);

        if (!validateForm($form)) {
            return;
        }

        $form.addClass('loading');
        
        $.ajax({
            url: wpcw_forms.ajaxurl,
            type: 'POST',
            data: {
                action: 'wpcw_register_business',
                nonce: wpcw_forms.nonce,
                business_name: $('#business_name').val(),
                business_email: $('#business_email').val(),
                business_phone: $('#business_phone').val(),
                business_address: $('#business_address').val(),
                business_description: $('#business_description').val(),
                owner_name: $('#owner_name').val(),
                owner_email: $('#owner_email').val(),
                password: $('#password').val()
            },
            success: function(response) {
                $form.removeClass('loading');
                const $messages = $form.find('.wpcw-form-messages');
                
                if (response.success) {
                    $messages.removeClass('error').addClass('success')
                        .html(response.data).show();
                    $form[0].reset();
                } else {
                    $messages.removeClass('success').addClass('error')
                        .html(response.data).show();
                }
            },
            error: function() {
                $form.removeClass('loading');
                $form.find('.wpcw-form-messages')
                    .removeClass('success').addClass('error')
                    .html(wpcw_forms.messages.error).show();
            }
        });
    });

    // Manejar envío del formulario de cliente
    $('#wpcw-customer-registration').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);

        if (!validateForm($form)) {
            return;
        }

        $form.addClass('loading');

        $.ajax({
            url: wpcw_forms.ajaxurl,
            type: 'POST',
            data: {
                action: 'wpcw_register_customer',
                nonce: wpcw_forms.nonce,
                customer_name: $('#customer_name').val(),
                customer_email: $('#customer_email').val(),
                customer_phone: $('#customer_phone').val(),
                customer_password: $('#customer_password').val()
            },
            success: function(response) {
                $form.removeClass('loading');
                const $messages = $form.find('.wpcw-form-messages');
                
                if (response.success) {
                    $messages.removeClass('error').addClass('success')
                        .html(response.data).show();
                    $form[0].reset();
                } else {
                    $messages.removeClass('success').addClass('error')
                        .html(response.data).show();
                }
            },
            error: function() {
                $form.removeClass('loading');
                $form.find('.wpcw-form-messages')
                    .removeClass('success').addClass('error')
                    .html(wpcw_forms.messages.error).show();
            }
        });
    });

    // Formateo de teléfono en tiempo real
    $('input[type="tel"]').on('input', function() {
        let phone = $(this).val().replace(/\D/g, '');
        if (phone.length > 10) {
            phone = phone.substring(0, 10);
        }
        if (phone.length >= 6) {
            phone = phone.substring(0, 3) + '-' + phone.substring(3, 6) + '-' + phone.substring(6);
        } else if (phone.length >= 3) {
            phone = phone.substring(0, 3) + '-' + phone.substring(3);
        }
        $(this).val(phone);
    });
});
