<?php
/**
 * WP Canje Cupon Whatsapp AJAX Handlers
 *
 * Handles AJAX requests for the plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handles the AJAX request for initiating a coupon redemption.
 */
function wpcw_request_canje_handler() {
    // 1. Verificar Nonce y que el usuario esté logueado
    // El segundo parámetro 'nonce' es el nombre del campo nonce que se espera en $_POST o $_REQUEST
    // que fue configurado en wp_localize_script como 'nonce'.
    check_ajax_referer( 'wpcw_request_canje_action_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => __( 'Debes iniciar sesión para canjear un cupón.', 'wp-cupon-whatsapp' ) ) );
        // wp_die() is called automatically by wp_send_json_error
    }

    // TODO: Lógica completa de canje (siguiente paso):
    // - Recibir coupon_id de $_POST.
    // - Validar cupón (existe, está activo, no expirado, etc.).
    // - Obtener WhatsApp del cliente (user_meta _wpcw_whatsapp_number).
    // - Generar token único y número de canje único.
    // - Insertar registro en la tabla wpcw_canjes con estado 'pendiente_confirmacion'.
    // - Construir URL de WhatsApp con el token/número de canje y el mensaje predefinido.
    // - Devolver wp_send_json_success con la URL de WhatsApp.

    // 2. Recibir y sanitizar coupon_id
    if ( ! isset( $_POST['coupon_id'] ) || empty( $_POST['coupon_id'] ) ) {
        wp_send_json_error( array( 'message' => __( 'ID de cupón no proporcionado.', 'wp-cupon-whatsapp' ) ) );
    }
    $coupon_id = absint( $_POST['coupon_id'] );

    // 3. Validación Inicial Simplificada del Cupón
    $coupon = get_post( $coupon_id );
    if ( ! $coupon || 'shop_coupon' !== $coupon->post_type || 'publish' !== $coupon->post_status ) {
        wp_send_json_error( array( 'message' => __( 'El cupón seleccionado no es válido o no está disponible.', 'wp-cupon-whatsapp' ) ) );
    }
    // TODO: Validación más exhaustiva: no expirado, límite de usos, etc.

    // 4. Obtener el número de WhatsApp del cliente
    $user_id = get_current_user_id();
    $cliente_whatsapp = get_user_meta( $user_id, '_wpcw_whatsapp_number', true );
    if ( empty( $cliente_whatsapp ) ) {
        wp_send_json_error( array( 'message' => __( 'No tienes un número de WhatsApp configurado en tu perfil. Por favor, actualízalo para poder canjear cupones.', 'wp-cupon-whatsapp' ) ) );
    }
    // Limpiar el número de WhatsApp (quitar +, espacios, etc. para URL wa.me)
    $cliente_whatsapp_cleaned = preg_replace( '/[^0-9]/', '', $cliente_whatsapp );


    // 5. Generar token_confirmacion
    $token_confirmacion = wp_generate_password( 32, false, false ); // 32 caracteres, sin caracteres especiales

    // 6. Generar numero_canje
    $numero_canje = 'WPCW-' . time() . '-' . wp_rand( 100, 999 );

    // 7. Insertar registro en la tabla wpcw_canjes
    global $wpdb;
    $tabla_canjes = WPCW_CANJES_TABLE_NAME; // Usar la constante definida

    $insert_data = array(
        'cliente_id'            => $user_id,
        'cupon_id'              => $coupon_id,
        'fecha_solicitud_canje' => current_time( 'mysql' ),
        'estado_canje'          => 'pendiente_confirmacion', // Estado inicial
        'token_confirmacion'    => $token_confirmacion,
        'numero_canje'          => $numero_canje,
        'comercio_id'           => get_post_meta( $coupon_id, '_wpcw_associated_business_id', true ), // Guardar el comercio asociado al cupón
        'origen_canje'          => 'mis_cupones', // Asumimos que viene de "Mis Cupones" por ahora
    );
    $format = array( '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%s' );

    $inserted = $wpdb->insert( $tabla_canjes, $insert_data, $format );

    // 8. Si la inserción falla
    if ( false === $inserted ) {
        error_log("WPCW DB Error: No se pudo insertar el registro de canje. Data: " . print_r($insert_data, true) . " Error: " . $wpdb->last_error);
        wp_send_json_error( array( 'message' => __( 'Hubo un problema al registrar tu solicitud de canje. Inténtalo de nuevo.', 'wp-cupon-whatsapp' ) ) );
    }
    $canje_id = $wpdb->insert_id; // ID del registro de canje recién creado

    // 9. Obtener nombre/título del cupón
    $coupon_title = $coupon->post_title;

    // 10. Construir mensaje para WhatsApp
    // El token se envía al cliente para que lo muestre al negocio.
    // El negocio usará este token para confirmar el canje.
    $mensaje_whatsapp = sprintf(
        __( 'Hola, quiero canjear mi cupón "%1$s" (Solicitud Nro: %2$s). Muestra este código al negocio para confirmar: %3$s', 'wp-cupon-whatsapp' ),
        $coupon_title,
        $numero_canje,
        $token_confirmacion
    );

    // 11. Construir URL wa.me
    $whatsapp_url = 'https://wa.me/' . $cliente_whatsapp_cleaned . '?text=' . rawurlencode( $mensaje_whatsapp );

    // 12. Devolver éxito
    wp_send_json_success( array(
        'message'       => __( '¡Solicitud de canje iniciada! Serás redirigido a WhatsApp.', 'wp-cupon-whatsapp' ),
        'whatsapp_url'  => $whatsapp_url,
        'numero_canje'  => $numero_canje,
        'token'         => $token_confirmacion, // El JS podría mostrarlo brevemente
        'canje_db_id'   => $canje_id
    ) );

	// wp_die() es llamado automáticamente por wp_send_json_success y wp_send_json_error
}
// Enganchar solo para usuarios logueados
add_action( 'wp_ajax_wpcw_request_canje', 'wpcw_request_canje_handler' );

// Si se necesitara para usuarios no logueados (no es el caso para canjear cupones de lealtad):
// add_action( 'wp_ajax_nopriv_wpcw_request_canje', 'wpcw_request_canje_handler' );

?>
