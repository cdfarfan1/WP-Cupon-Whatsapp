<?php
/**
 * WP Canje Cupon Whatsapp Redemption Logic
 *
 * Handles logic related to coupon redemption status and tracking in WooCommerce orders.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Tracks the usage of WPCW redeemed coupons when a WC order is completed.
 *
 * @param int $order_id The ID of the order that has been completed.
 */
function wpcw_track_coupon_in_completed_order( $order_id ) {
    // Log para verificar que el hook se dispara y recibimos el order_id
    // error_log("WPCW Log: Hook woocommerce_order_status_completed disparado para Order ID: " . $order_id);

    // TODO: Implementar la lógica completa de seguimiento:
    // 1. Obtener el objeto $order.
    // 2. Obtener los códigos de cupón usados en el pedido.
    // 3. Iterar sobre los códigos:
    //    a. Consultar la tabla wpcw_canjes por codigo_cupon_wc y estado 'confirmado_por_negocio'.
    //    b. Si se encuentra, actualizar estado a 'utilizado_en_pedido_wc' y guardar order_id.
    //    c. (Opcional) Añadir nota al pedido.

    if ( empty($order_id) ) {
        error_log("WPCW Warning: wpcw_track_coupon_in_completed_order fue llamado sin order_id.");
        return;
    }

    // Placeholder para la lógica principal
    // error_log("WPCW Debug: Procesando orden completada ID: " . $order_id . " para seguimiento de cupones WPCW.");

    $order = wc_get_order( $order_id );

    if ( ! $order ) {
        error_log("WPCW Warning: No se pudo obtener el objeto WC_Order para el ID de pedido: " . $order_id);
        return;
    }

    $coupon_codes = $order->get_coupon_codes();

    if ( empty( $coupon_codes ) ) {
        // No hay cupones en este pedido, nada que hacer.
        // error_log("WPCW Log: Pedido ID: " . $order_id . " no tiene cupones.");
        return;
    }

    global $wpdb;
    $tabla_canjes = WPCW_CANJES_TABLE_NAME; // Asegúrate que esta constante esté definida

    foreach ( $coupon_codes as $code ) {
        // Sanitizar el código del cupón por si acaso, aunque WooCommerce debería devolverlo limpio.
        $sanitized_code = sanitize_text_field( $code );

        // Buscar en la tabla de canjes un cupón que coincida con el código y esté confirmado
        $canje_row = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$tabla_canjes} WHERE codigo_cupon_wc = %s AND estado_canje = %s",
            $sanitized_code,
            'confirmado_por_negocio' // Solo nos interesan los que están listos para ser usados
        ) );

        if ( $canje_row ) {
            // Se encontró un canje que coincide y está en el estado correcto. Actualizarlo.
            $update_result = $wpdb->update(
                $tabla_canjes,
                array(
                    'estado_canje'   => 'utilizado_en_pedido_wc', // Nuevo estado
                    'id_pedido_wc'   => $order_id,               // Guardar el ID del pedido
                ),
                array( 'id' => $canje_row->id ), // Condición WHERE para el ID del canje
                array( '%s', '%d' ),              // Formato de los datos a actualizar
                array( '%d' )                     // Formato de la condición WHERE
            );

            if ( false === $update_result ) {
                error_log("WPCW DB Error: No se pudo actualizar el estado del canje ID: " . $canje_row->id . " para el pedido ID: " . $order_id . ". Error: " . $wpdb->last_error);
            } else {
                error_log("WPCW Log: Canje ID " . $canje_row->id . " actualizado a 'utilizado_en_pedido_wc' para el pedido ID: " . $order_id);
                // (Opcional) Añadir nota al pedido
                $order_note = sprintf(
                    __('Cupón de canje WPCW "%1$s" (Nro. Solicitud Canje: %2$s) utilizado en este pedido.', 'wp-cupon-whatsapp'),
                    $sanitized_code,
                    $canje_row->numero_canje
                );
                $order->add_order_note( $order_note );
            }
        }
        // Si no se encuentra $canje_row, o el estado no es 'confirmado_por_negocio', no hacemos nada.
        // Podría ser un cupón de WooCommerce normal no relacionado con WPCW, o un cupón WPCW que no estaba listo.
    }
}
add_action( 'woocommerce_order_status_completed', 'wpcw_track_coupon_in_completed_order', 10, 1 );

?>
