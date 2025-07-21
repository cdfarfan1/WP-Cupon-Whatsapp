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
function wpcw_track_coupon_in_completed_order($order_id) {
    if (empty($order_id)) {
        WPCW_Logger::log('error', 'Track coupon called without order_id');
        return;
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        WPCW_Logger::log('error', sprintf('No se pudo obtener el pedido #%d', $order_id));
        return;
    }

    // Obtener todos los cupones del pedido
    $used_coupons = $order->get_coupon_codes();
    if (empty($used_coupons)) {
        return; // No hay cupones que procesar
    }

    global $wpdb;
    $tabla_canjes = WPCW_CANJES_TABLE_NAME;

    foreach ($used_coupons as $coupon_code) {
        // Sanitizar el código del cupón
        $sanitized_code = sanitize_text_field($coupon_code);
        
        // Verificar si es un cupón de nuestro sistema
        $canje = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$tabla_canjes} 
            WHERE codigo_cupon_wc = %s 
            AND estado_canje = 'confirmado_por_negocio'",
            $sanitized_code
        ));

        if (!$canje) {
            continue; // No es un cupón de nuestro sistema o no está confirmado
        }

        // Actualizar estado del canje
        $update_result = $wpdb->update(
            $tabla_canjes,
            array(
                'estado_canje' => 'utilizado_en_pedido_wc',
                'fecha_uso' => current_time('mysql'),
                'id_pedido_wc' => $order_id,
                'monto_pedido' => $order->get_total()
            ),
            array('id' => $canje->id),
            array('%s', '%s', '%d', '%f'),
            array('%d')
        );

        if (false === $update_result) {
            WPCW_Logger::log('error', 'Error al actualizar canje', array(
                'canje_id' => $canje->id,
                'order_id' => $order_id,
                'error' => $wpdb->last_error
            ));
            continue;
        }

        // Actualizar el contador de uso en WooCommerce
        $wc_coupon = new WC_Coupon($sanitized_code);
        $wc_coupon->increase_usage_count();

        // Sincronizar con MongoDB si está habilitado
        if (get_option('wpcw_mongodb_enabled') && get_option('wpcw_mongodb_auto_sync')) {
            try {
                $mongo = WPCW_MongoDB::get_instance();
                $mongo->sync_to_mongodb();
            } catch (Exception $e) {
                WPCW_Logger::log('error', 'Error al sincronizar con MongoDB', array(
                    'canje_id' => $canje->id,
                    'error' => $e->getMessage()
                ));
            }
        }

        // Añadir nota al pedido
        $order->add_order_note(sprintf(
            __('Cupón WPCW "%1$s" (Solicitud #%2$s) utilizado. Monto del pedido: %3$s', 'wp-cupon-whatsapp'),
            $sanitized_code,
            $canje->numero_canje,
            wc_price($order->get_total())
        ));
    }
}
add_action( 'woocommerce_order_status_completed', 'wpcw_track_coupon_in_completed_order', 10, 1 );

?>
