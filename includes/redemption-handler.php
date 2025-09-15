<?php
/**
 * Manejador de Canjes
 *
 * Gestiona el proceso de canje de cupones incluyendo verificaciones y estadísticas
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPCW_Redemption_Handler {
    /**
     * Procesa la solicitud de canje
     */
    public static function process_redemption_request( $user_id, $coupon_id ) {
        // 1. Verificación de usuario y email
        if ( ! self::verify_user_status( $user_id ) ) {
            return new WP_Error( 'user_not_verified', __( 'Tu cuenta debe estar verificada para canjear cupones.', 'wp-cupon-whatsapp' ) );
        }

        // 2. Verificación de WhatsApp
        $whatsapp = get_user_meta( $user_id, '_wpcw_whatsapp_number', true );
        if ( empty( $whatsapp ) ) {
            return new WP_Error( 'no_whatsapp', __( 'Debes tener un número de WhatsApp configurado para canjear cupones.', 'wp-cupon-whatsapp' ) );
        }

        // 3. Limpieza del número de WhatsApp para wa.me
        $whatsapp_cleaned = preg_replace( '/[^0-9]/', '', (string) $whatsapp );

        // 4. Generación de token y número de canje
        $token             = wp_generate_password( 32, false );
        $redemption_number = self::generate_redemption_number();

        // 5. Registrar el canje en la base de datos
        $canje_id = self::register_redemption( $user_id, $coupon_id, $token, $redemption_number );
        if ( is_wp_error( $canje_id ) ) {
            return $canje_id;
        }

        // 6. Crear mensaje para WhatsApp
        $message = self::generate_whatsapp_message( $coupon_id, $redemption_number, $token );

        // 7. Generar URL de wa.me
        $whatsapp_url = 'https://wa.me/' . $whatsapp_cleaned . '?text=' . rawurlencode( $message );

        return array(
            'success'           => true,
            'whatsapp_url'      => $whatsapp_url,
            'redemption_id'     => $canje_id,
            'redemption_number' => $redemption_number,
            'token'             => $token,
        );
    }

    /**
     * Verifica el estado del usuario
     */
    private static function verify_user_status( $user_id ) {
        if ( ! class_exists( 'WPCW_Email_Verification' ) ) {
            return true; // Si no está habilitada la verificación, permitir
        }
        return WPCW_Email_Verification::is_email_verified( $user_id );
    }

    /**
     * Genera un número único de canje
     */
    private static function generate_redemption_number() {
        global $wpdb;
        $prefix = date( 'Ymd' );

        do {
            $number = $prefix . wp_rand( 1000, 9999 );
            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}wpcw_canjes WHERE numero_canje = %s",
                    $number
                )
            );
        } while ( $exists > 0 );

        return $number;
    }

    /**
     * Registra el canje en la base de datos
     */
    private static function register_redemption( $user_id, $coupon_id, $token, $redemption_number ) {
        global $wpdb;

        $data = array(
            'user_id'            => $user_id,
            'coupon_id'          => $coupon_id,
            'numero_canje'       => $redemption_number,
            'token_confirmacion' => $token,
            'estado'             => 'pendiente_confirmacion',
            'fecha_solicitud'    => current_time( 'mysql' ),
            'origen'             => 'webapp',
        );

        $result = $wpdb->insert(
            $wpdb->prefix . 'wpcw_canjes',
            $data,
            array( '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
        );

        if ( $result === false ) {
            return new WP_Error(
                'db_error',
                __( 'Error al registrar el canje en la base de datos.', 'wp-cupon-whatsapp' )
            );
        }

        return $wpdb->insert_id;
    }

    /**
     * Genera el mensaje para WhatsApp
     */
    private static function generate_whatsapp_message( $coupon_id, $redemption_number, $token ) {
        $coupon = get_post( $coupon_id );
        if ( ! $coupon ) {
            return '';
        }

        return sprintf(
            __( 'Hola, quiero canjear mi cupón "%1$s" (Solicitud Nro: %2$s). Muestra este código al negocio para confirmar: %3$s', 'wp-cupon-whatsapp' ),
            $coupon->post_title,
            $redemption_number,
            $token
        );
    }

    /**
     * Registra el uso del cupón en las estadísticas
     */
    public static function record_redemption_usage( $redemption_id, $order_id ) {
        global $wpdb;

        // 1. Actualizar estado del canje
        $wpdb->update(
            $wpdb->prefix . 'wpcw_canjes',
            array(
                'estado'    => 'utilizado',
                'order_id'  => $order_id,
                'fecha_uso' => current_time( 'mysql' ),
            ),
            array( 'id' => $redemption_id ),
            array( '%s', '%d', '%s' ),
            array( '%d' )
        );

        // 2. Obtener datos del canje
        $redemption = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wpcw_canjes WHERE id = %d",
                $redemption_id
            )
        );

        if ( ! $redemption ) {
            return false;
        }

        // 3. Actualizar estadísticas
        $stats = get_post_meta( $redemption->coupon_id, '_wpcw_coupon_stats', true );
        if ( ! is_array( $stats ) ) {
            $stats = array();
        }

        $current_month = date( 'Y-m' );
        if ( ! isset( $stats[ $current_month ] ) ) {
            $stats[ $current_month ] = array(
                'redeemed' => 0,
                'used'     => 0,
            );
        }

        ++$stats[ $current_month ]['used'];
        update_post_meta( $redemption->coupon_id, '_wpcw_coupon_stats', $stats );

        // 4. Registrar en MongoDB si está habilitado
        if ( class_exists( 'WPCW_MongoDB' ) && get_option( 'wpcw_mongodb_enabled', false ) ) {
            $mongo = WPCW_MongoDB::get_instance();
            $mongo->sync_redemption_usage( $redemption_id );
        }

        return true;
    }
}
