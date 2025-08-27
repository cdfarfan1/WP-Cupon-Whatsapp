/**
 * WP Canje Cupon Whatsapp - Canje Handler
 *
 * Handles AJAX requests for coupon redemption.
 */
(function ($) {
    'use strict';

    $( document ).ready(
        function () {

            $( 'body' ).on(
                'click',
                '.wpcw-canjear-cupon-btn',
                function (e) {
                    e.preventDefault();

                    var $button            = $( this );
                    var couponId           = $button.data( 'coupon-id' );
                    var originalButtonText = $button.text();

                    if ( ! couponId) {
                        // En un escenario real, esto podría ser un mensaje más amigable o un log
                        console.error( 'WPCW Error: No se pudo obtener el ID del cupón del botón.' );
                        alert( wpcw_canje_obj.text_error_generic || 'Error: No se pudo obtener la información del cupón.' );
                        return;
                    }

                    // Mostrar indicador de "Procesando..."
                    $button.text( wpcw_canje_obj.text_processing || 'Procesando...' ).prop( 'disabled', true );

                    // Datos para la petición AJAX
                    var ajaxData = {
                        action: 'wpcw_request_canje', // La acción registrada en PHP
                        nonce: wpcw_canje_obj.nonce,    // El nonce pasado desde PHP
                        coupon_id: couponId
                    };

                    $.ajax(
                        {
                            url: wpcw_canje_obj.ajax_url, // La URL de admin-ajax.php pasada desde PHP
                            type: 'POST',
                            data: ajaxData,
                            dataType: 'json', // Esperamos una respuesta JSON
                            success: function (response) {
                                if (response.success) {
                                    // Éxito: redirigir a WhatsApp
                                    // $button.text('¡Redireccionando!'); // Opcional, pero la redirección es casi inmediata
                                    if (response.data && response.data.whatsapp_url) {
                                        window.location.href = response.data.whatsapp_url;
                                        // No se restaura el botón aquí porque hay redirección.
                                        // Si la redirección fallara o se abriera en nueva pestaña,
                                        // podríamos necesitar restaurar el botón después de un timeout.
                                    } else {
                                        // Esto no debería suceder si el backend funciona bien
                                        console.error( 'WPCW Error: No se recibió URL de WhatsApp en respuesta exitosa.' );
                                        alert( wpcw_canje_obj.text_error_generic || 'Error: Respuesta del servidor incompleta.' );
                                        $button.text( originalButtonText ).prop( 'disabled', false );
                                    }
                                } else {
                                    // Error manejado por el backend (ej. validación fallida, nonce incorrecto, etc.)
                                    var errorMessage = (response.data && response.data.message) ? response.data.message : wpcw_canje_obj.text_error_generic;
                                    alert( errorMessage );
                                    $button.text( originalButtonText ).prop( 'disabled', false );
                                }
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                // Error de red o del servidor (ej. error 500, 403, etc.)
                                console.error( 'WPCW AJAX Error:', textStatus, errorThrown, jqXHR.responseText );
                                alert( wpcw_canje_obj.text_error_generic || 'Error de comunicación. Por favor, inténtalo de nuevo.' );
                                $button.text( originalButtonText ).prop( 'disabled', false );
                            }
                        }
                    );
                }
            );
        }
    );

})( jQuery );
