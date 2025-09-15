<?php
/**
 * Manejador del Formulario de Canje
 *
 * Procesa el formulario de canje y maneja la integración con WhatsApp
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Procesa el formulario de canje
 */
function wpcw_process_redemption_form() {
    // Verificar nonce
    if ( ! isset( $_POST['wpcw_redemption_nonce'] ) ||
        ! wp_verify_nonce( $_POST['wpcw_redemption_nonce'], 'wpcw_redeem_coupon' ) ) {
        wp_die( __( 'Error de seguridad. Por favor, intenta nuevamente.', 'wp-cupon-whatsapp' ) );
    }

    // Verificar que el usuario está logueado
    if ( ! is_user_logged_in() ) {
        wp_die( __( 'Debes iniciar sesión para canjear cupones.', 'wp-cupon-whatsapp' ) );
    }

    // Obtener datos del formulario
    $coupon_id   = isset( $_POST['coupon_id'] ) ? absint( $_POST['coupon_id'] ) : 0;
    $business_id = isset( $_POST['business_id'] ) ? absint( $_POST['business_id'] ) : 0;
    $user_id     = get_current_user_id();

    // Validar datos
    if ( ! $coupon_id || ! $business_id ) {
        wpcw_redirect_with_error( __( 'Datos del formulario incompletos.', 'wp-cupon-whatsapp' ) );
        return;
    }

    // Verificar si el usuario puede canjear
    if ( ! WPCW_Redemption_Handler::can_redeem( $coupon_id, $user_id ) ) {
        wpcw_redirect_with_error( __( 'No puedes canjear este cupón.', 'wp-cupon-whatsapp' ) );
        return;
    }

    // Iniciar el proceso de canje
    $result = WPCW_Redemption_Handler::initiate_redemption( $coupon_id, $user_id, $business_id );

    if ( is_wp_error( $result ) ) {
        wpcw_redirect_with_error( $result->get_error_message() );
        return;
    }

    // Obtener la URL de WhatsApp
    global $wpdb;
    $whatsapp_url = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT whatsapp_url FROM {$wpdb->prefix}wpcw_canjes WHERE id = %d",
            $result
        )
    );

    if ( $whatsapp_url ) {
        // Redirigir a WhatsApp
        wp_redirect( $whatsapp_url );
        exit;
    }

    // Si no hay URL de WhatsApp, redirigir a la página de éxito
    $cupones_url  = wc_get_account_endpoint_url( 'mis-cupones' );
    $redirect_url = $cupones_url ? add_query_arg( 'redemption_id', $result, $cupones_url ) : home_url( '/?redemption_id=' . $result );
    wp_redirect( $redirect_url );
    exit;
}
add_action( 'admin_post_wpcw_redeem_coupon', 'wpcw_process_redemption_form' );

/**
 * Redirige con un mensaje de error
 */
function wpcw_redirect_with_error( $message ) {
    $referer      = wp_get_referer();
    $redirect_url = $referer ? add_query_arg(
        array(
			'error' => urlencode( $message ),
        ),
        $referer
    ) : home_url( '/?error=' . urlencode( $message ) );

    wp_redirect( $redirect_url );
    exit;
}

/**
 * Shortcode para mostrar el formulario de canje
 */
function wpcw_redemption_form_shortcode( $atts ) {
    ob_start();
    include WPCW_PLUGIN_DIR . 'templates/redemption-form.php';
    return ob_get_clean();
}
add_shortcode( 'wpcw_redemption_form', 'wpcw_redemption_form_shortcode' );
