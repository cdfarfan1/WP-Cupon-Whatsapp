<?php
/**
 * WP Canje Cupon Whatsapp REST API Endpoints
 *
 * Defines custom REST API endpoints for the plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers custom REST API routes for WPCW.
 */
function wpcw_register_rest_routes() {
    register_rest_route( 'wpcw/v1', '/confirm-redemption', array(
        'methods'  => WP_REST_Server::READABLE, // GET request
        'callback' => 'wpcw_handle_redemption_confirmation_request',
        'args'     => array(
            'token' => array(
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => function( $param, $request, $key ) {
                    return ! empty( $param ) && is_string( $param ) && strlen( $param ) >= 22 && strlen( $param ) <= 64;
                },
                'description'       => __( 'El token de confirmación único para el canje.', 'wp-cupon-whatsapp' ),
            ),
            'canje_id' => array(
                'required'          => true,
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'validate_callback' => function( $param, $request, $key ) {
                    return is_numeric( $param ) && $param > 0;
                },
                'description'       => __( 'El ID del registro de canje en la base de datos.', 'wp-cupon-whatsapp' ),
            ),
        ),
        'permission_callback' => '__return_true', // Publicly accessible, authorization is handled by token validity in the callback
    ) );
}
add_action( 'rest_api_init', 'wpcw_register_rest_routes' );

/**
 * Handles the REST API request for confirming a coupon redemption.
 *
 * @param WP_REST_Request $request The current REST API request object.
 * @return WP_REST_Response The REST API response.
 */
function wpcw_handle_redemption_confirmation_request( WP_REST_Request $request ) {
    $params = $request->get_params();

    $token    = $params['token'];
    $canje_id = $params['canje_id'];

    global $wpdb;
    $tabla_canjes = WPCW_CANJES_TABLE_NAME;

    $canje_row = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$tabla_canjes} WHERE id = %d AND token_confirmacion = %s",
        $canje_id,
        $token
    ) );

    if ( ! $canje_row ) {
        $html_error = wpcw_confirm_redemption_html_error_page(__('Confirmación de Canje Fallida', 'wp-cupon-whatsapp'), __('El enlace de confirmación no es válido o el canje no existe. Por favor, verifica el enlace o contacta al soporte.', 'wp-cupon-whatsapp'));
        $response = new WP_REST_Response( $html_error, 404 );
        $response->header( 'Content-Type', 'text/html; charset=' . get_option( 'blog_charset', 'UTF-8' ) );
        return $response;
    }

    if ( $canje_row->estado_canje !== 'pendiente_confirmacion' ) {
        $error_title = __('Canje Ya Procesado o Inválido', 'wp-cupon-whatsapp');
        $error_message = '';
        if ( $canje_row->estado_canje === 'confirmado_por_negocio' ) {
            $error_message = __( 'Este canje ya ha sido confirmado anteriormente.', 'wp-cupon-whatsapp' );
        } elseif ( $canje_row->estado_canje === 'utilizado_en_pedido_wc' ) {
            $error_message = __( 'Este canje ya ha sido utilizado en un pedido.', 'wp-cupon-whatsapp' );
        } else {
            $error_message = sprintf(__( 'Este canje no puede ser procesado en este momento (estado: %s).', 'wp-cupon-whatsapp' ), esc_html($canje_row->estado_canje));
        }
        $html_already_processed = wpcw_confirm_redemption_html_error_page($error_title, $error_message);
        $response = new WP_REST_Response( $html_already_processed, 400 );
        $response->header( 'Content-Type', 'text/html; charset=' . get_option( 'blog_charset' ) );
        return $response;
    }

    $cliente_id = $canje_row->cliente_id;
    $original_coupon_id = $canje_row->cupon_id;
    $numero_canje_original = $canje_row->numero_canje;

    $original_coupon_post = get_post( $original_coupon_id );
    if ( ! $original_coupon_post || $original_coupon_post->post_type !== 'shop_coupon' ) {
        error_log("WPCW Error: Cupón original (ID: " . $original_coupon_id . ") no encontrado o no es un shop_coupon durante confirmación del canje ID: " . $canje_id);
        $html_error = wpcw_confirm_redemption_html_error_page(__('Error Interno del Servidor', 'wp-cupon-whatsapp'), __('No se pudo encontrar el cupón original asociado a este canje.', 'wp-cupon-whatsapp'));
        $response = new WP_REST_Response( $html_error, 500 );
        $response->header( 'Content-Type', 'text/html; charset=' . get_option('blog_charset', 'UTF-8') );
        return $response;
    }

    $new_coupon_code = strtoupper( 'CANJE-' . str_replace(array('WPCW-', '-'), '', (string) $numero_canje_original) . '-' . wp_rand(100,999) );
    $new_coupon_description = sprintf( __('Cupón de canje para "%s". Solicitud Nro: %s.', 'wp-cupon-whatsapp'), $original_coupon_post->post_title, $numero_canje_original );

    $coupon_args = array(
        'post_title'   => $new_coupon_code,
        'post_content' => '',
        'post_status'  => 'publish',
        'post_type'    => 'shop_coupon',
        'post_excerpt' => $new_coupon_description,
    );
    $new_wc_coupon_id = wp_insert_post( $coupon_args, true );

    if ( is_wp_error( $new_wc_coupon_id ) ) {
        error_log("WPCW Error: Falló la creación del cupón WC dinámico para canje ID: " . $canje_id . ". Error: " . $new_wc_coupon_id->get_error_message());
        $html_error = wpcw_confirm_redemption_html_error_page(__('Error al Generar Cupón', 'wp-cupon-whatsapp'), __('Hubo un problema al generar el código de cupón final.', 'wp-cupon-whatsapp'));
        $response = new WP_REST_Response( $html_error, 500 );
        $response->header( 'Content-Type', 'text/html; charset=' . get_option('blog_charset', 'UTF-8') );
        return $response;
    }

    $meta_fields_to_copy = array(
        'discount_type', 'coupon_amount', 'free_shipping',
        'minimum_amount', 'maximum_amount', 'exclude_sale_items',
        'product_ids', 'exclude_product_ids', 'product_categories', 'exclude_product_categories',
    );
    foreach ( $meta_fields_to_copy as $meta_key ) {
        $value = get_post_meta( $original_coupon_id, $meta_key, true );
        if ( $value !== '' ) {
            update_post_meta( $new_wc_coupon_id, $meta_key, $value );
        }
    }

    $original_expiry_date = get_post_meta( $original_coupon_id, 'date_expires', true );
    if ( $original_expiry_date ) {
        update_post_meta( $new_wc_coupon_id, 'date_expires', $original_expiry_date );
    }

    update_post_meta( $new_wc_coupon_id, 'individual_use', 'yes' );
    update_post_meta( $new_wc_coupon_id, 'usage_limit', '1' );
    update_post_meta( $new_wc_coupon_id, 'usage_limit_per_user', '1' );

    $cliente_info = get_userdata($cliente_id);
    if ($cliente_info && !empty($cliente_info->user_email)) {
        update_post_meta( $new_wc_coupon_id, 'customer_email', $cliente_info->user_email );
    }

    update_post_meta( $new_wc_coupon_id, '_wpcw_is_redeemed_coupon', 'yes' );
    update_post_meta( $new_wc_coupon_id, '_wpcw_original_canje_id', $canje_id );
    update_post_meta( $new_wc_coupon_id, '_wpcw_original_shop_coupon_id', $original_coupon_id );

    $update_canje_result = $wpdb->update(
        $tabla_canjes,
        array(
            'estado_canje'             => 'confirmado_por_negocio',
            'fecha_confirmacion_canje' => current_time( 'mysql' ),
            'codigo_cupon_wc'          => $new_coupon_code,
        ),
        array( 'id' => $canje_id ),
        array( '%s', '%s', '%s' ),
        array( '%d' )
    );

    if ( false === $update_canje_result ) {
        error_log("WPCW DB Error: No se pudo actualizar el registro de canje ID: " . $canje_id . " tras crear cupón WC. Error: " . $wpdb->last_error);
        $html_error = wpcw_confirm_redemption_html_error_page(__('Error al Actualizar Canje', 'wp-cupon-whatsapp'), __('Hubo un problema al finalizar el proceso de canje.', 'wp-cupon-whatsapp'));
        $response = new WP_REST_Response( $html_error, 500 );
        $response->header( 'Content-Type', 'text/html; charset=' . get_option('blog_charset', 'UTF-8') );
        return $response;
    }

    $cliente_whatsapp = get_user_meta( $cliente_id, '_wpcw_whatsapp_number', true );
    $cliente_whatsapp_cleaned = preg_replace( '/[^0-9]/', '', (string) $cliente_whatsapp );

    $html_title = __('¡Canje Confirmado Exitosamente!', 'wp-cupon-whatsapp');
    $page_content = '<h1>' . esc_html($html_title) . '</h1>';
    $page_content .= '<p>' . sprintf(esc_html__('El canje para "%s" (Solicitud Nro: %s) ha sido confirmado.', 'wp-cupon-whatsapp'), esc_html($original_coupon_post->post_title), esc_html($numero_canje_original)) . '</p>';
    $page_content .= '<h2>' . esc_html__('Código de Cupón WooCommerce Generado:', 'wp-cupon-whatsapp') . '</h2>';
    $page_content .= '<p style="font-size: 1.5em; font-weight: bold; color: green; border: 1px solid green; padding: 10px; text-align:center;">' . esc_html($new_coupon_code) . '</p>';
    $page_content .= '<h3>' . esc_html__('Instrucciones:', 'wp-cupon-whatsapp') . '</h3>';
    $page_content .= '<ol>';
    $page_content .= '<li>' . esc_html__('Proporciona este código de cupón al cliente.', 'wp-cupon-whatsapp') . '</li>';

    if ( !empty($cliente_whatsapp_cleaned) ) {
        $whatsapp_message_to_client = sprintf(
            __('¡Hola! Tu canje para el cupón "%1\$s" ha sido confirmado. Aquí está tu código de cupón para usar en tu próxima compra: %2\$s ¡Gracias!', 'wp-cupon-whatsapp'),
            $original_coupon_post->post_title,
            $new_coupon_code
        );
        $whatsapp_link_to_client = 'https://wa.me/' . $cliente_whatsapp_cleaned . '?text=' . rawurlencode($whatsapp_message_to_client);
        $page_content .= '<li>' . sprintf(wp_kses_post(__('Puedes enviárselo directamente por WhatsApp haciendo clic aquí: <a href="%s" target="_blank">Enviar Código por WhatsApp al Cliente</a> (Número: %s)', 'wp-cupon-whatsapp')), esc_url($whatsapp_link_to_client), esc_html($cliente_whatsapp)) . '</li>';
    } else {
        $page_content .= '<li>' . esc_html__('El cliente no tiene un número de WhatsApp registrado en su perfil. Deberás contactarlo por otros medios si es necesario.', 'wp-cupon-whatsapp') . '</li>';
    }
    $page_content .= '<li>' . esc_html__('El cliente usará este código en el carrito o checkout de WooCommerce.', 'wp-cupon-whatsapp') . '</li>';
    $page_content .= '</ol>';

    $full_html_output = wpcw_confirm_redemption_html_wrapper($html_title, $page_content);
    $response = new WP_REST_Response( $full_html_output, 200 );
    $response->header( 'Content-Type', 'text/html; charset=' . get_option('blog_charset', 'UTF-8') );
    return $response;
}

// Helper function to generate basic HTML page structure for responses
if (!function_exists('wpcw_confirm_redemption_html_wrapper')) {
    function wpcw_confirm_redemption_html_wrapper($title, $content) {
        $charset = get_option('blog_charset', 'UTF-8');
        $html = "<!DOCTYPE html>\n"; // Added newline for readability
        $html .= "<html lang=\"" . esc_attr(get_locale()) . "\">\n<head>\n";
        $html .= "<meta charset=\"" . esc_attr($charset) . "\">\n";
        $html .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n";
        $html .= "<title>" . esc_html($title) . "</title>\n";
        $html .= "<style>
            body { font-family: sans-serif; line-height: 1.6; color: #333; max-width: 700px; margin: 20px auto; padding: 15px; border: 1px solid #ddd; }
            h1 { color: #0073aa; } h2 { color: #2271b1; } h3 {color: #555;}
            ol { margin-left: 20px; } li { margin-bottom: 8px; }
            a { color: #0073aa; text-decoration: none; } a:hover { text-decoration: underline; }
        </style>\n";
        $html .= "</head>\n<body>\n";
        $html .= $content;
        $html .= "\n</body>\n</html>";
        return $html;
    }
}
if (!function_exists('wpcw_confirm_redemption_html_error_page')) {
    function wpcw_confirm_redemption_html_error_page($title, $message) {
        $content = '<h1>' . esc_html($title) . '</h1>';
        $content .= '<p style="color: red;">' . esc_html($message) . '</p>';
        return wpcw_confirm_redemption_html_wrapper($title, $content);
    }
}

?>
