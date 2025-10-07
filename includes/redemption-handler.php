<?php
/**
 * WP Cupón WhatsApp - Redemption Handler
 *
 * RESPONSABILIDAD: Procesar redenciones INDIVIDUALES de cupones
 * - Iniciar proceso de canje
 * - Verificar elegibilidad de usuario
 * - Generar tokens y números de canje
 * - Enviar notificaciones a comercios
 * - Confirmar/rechazar canjes individuales
 *
 * NOTA: Para operaciones MASIVAS y REPORTES usar WPCW_Redemption_Manager
 *
 * @package WP_Cupon_WhatsApp
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WPCW_Redemption_Handler
 *
 * Handles individual redemption processing
 */
class WPCW_Redemption_Handler {
    /**
     * Inicia el proceso de canje
     */
    public static function initiate_redemption( $coupon_id, $user_id, $business_id ) {
        error_log( 'WPCW Debug: Iniciando initiate_redemption para coupon_id=' . $coupon_id . ', user_id=' . $user_id . ', business_id=' . $business_id );

        // Llamar al método principal
        $result = self::process_redemption_request( $user_id, $coupon_id );

        if ( is_wp_error( $result ) ) {
            error_log( 'WPCW Debug: Error en process_redemption_request: ' . $result->get_error_message() );
            return $result;
        }

        // Actualizar con business_id si es necesario
        if ( $business_id ) {
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'wpcw_canjes',
                array( 'comercio_id' => $business_id ),
                array( 'id' => $result['redemption_id'] ),
                array( '%d' ),
                array( '%d' )
            );
            error_log( 'WPCW Debug: Business ID actualizado: ' . $business_id );
        }

        // Almacenar la URL de WhatsApp en la DB para recuperación posterior
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'wpcw_canjes',
            array( 'whatsapp_url' => $result['whatsapp_url'] ),
            array( 'id' => $result['redemption_id'] ),
            array( '%s' ),
            array( '%d' )
        );
        error_log( 'WPCW Debug: WhatsApp URL almacenada en DB' );

        // Send notification to business
        self::notify_business_redemption_request( $result['redemption_id'] );

        return $result['redemption_id'];
    }

    /**
     * Verifica si el usuario puede canjear el cupón
     */
    public static function can_redeem( $coupon_id, $user_id ) {
        $coupon = wpcw_get_coupon( $coupon_id );

        if ( ! $coupon ) {
            return new WP_Error( 'invalid_coupon', __( 'Cupón no válido.', 'wp-cupon-whatsapp' ) );
        }

        // Use the coupon's built-in validation
        $can_redeem = $coupon->can_user_redeem( $user_id );

        if ( is_wp_error( $can_redeem ) ) {
            return $can_redeem;
        }

        return true;
    }

    /**
     * Procesa la solicitud de canje
     */
    public static function process_redemption_request( $user_id, $coupon_id ) {
        error_log( 'WPCW Debug: Iniciando process_redemption_request para user_id=' . $user_id . ', coupon_id=' . $coupon_id );

        // 1. Get coupon object
        $coupon = wpcw_get_coupon( $coupon_id );
        if ( ! $coupon ) {
            error_log( 'WPCW Debug: Cupón no encontrado' );
            return new WP_Error( 'invalid_coupon', __( 'Cupón no válido.', 'wp-cupon-whatsapp' ) );
        }

        // 2. Verificación de elegibilidad del usuario
        $can_redeem = $coupon->can_user_redeem( $user_id );
        if ( is_wp_error( $can_redeem ) ) {
            error_log( 'WPCW Debug: Usuario no puede canjear: ' . $can_redeem->get_error_message() );
            return $can_redeem;
        }

        // 3. Verificación de usuario y email
        if ( ! self::verify_user_status( $user_id ) ) {
            error_log( 'WPCW Debug: Usuario no verificado' );
            return new WP_Error( 'user_not_verified', __( 'Tu cuenta debe estar verificada para canjear cupones.', 'wp-cupon-whatsapp' ) );
        }

        // 4. Generación de token y número de canje
        $token             = wp_generate_password( 32, false );
        error_log( 'WPCW Debug: Token generado: ' . $token );
        $redemption_number = self::generate_redemption_number();
        error_log( 'WPCW Debug: Número de canje generado: ' . $redemption_number );

        // 5. Registrar el canje en la base de datos
        $canje_id = self::register_redemption( $user_id, $coupon_id, $token, $redemption_number );
        if ( is_wp_error( $canje_id ) ) {
            error_log( 'WPCW Debug: Error al registrar canje: ' . $canje_id->get_error_message() );
            return $canje_id;
        }
        error_log( 'WPCW Debug: Canje registrado con ID: ' . $canje_id );

        // 6. Generar URL de WhatsApp usando el método del coupon
        $whatsapp_url = $coupon->get_whatsapp_redemption_url( $user_id, $redemption_number, $token );
        if ( empty( $whatsapp_url ) ) {
            error_log( 'WPCW Debug: No se pudo generar URL de WhatsApp' );
            return new WP_Error( 'whatsapp_error', __( 'Error al generar enlace de WhatsApp.', 'wp-cupon-whatsapp' ) );
        }
        error_log( 'WPCW Debug: URL generada: ' . $whatsapp_url );

        // 7. Log the redemption attempt
        WPCW_Logger::log( 'info', 'Redemption request processed', array(
            'user_id' => $user_id,
            'coupon_id' => $coupon_id,
            'redemption_id' => $canje_id,
            'redemption_number' => $redemption_number,
        ) );

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
        error_log( 'WPCW Debug: Verificando estado de usuario: ' . $user_id );
        if ( ! class_exists( 'WPCW_Email_Verification' ) ) {
            error_log( 'WPCW Debug: Clase WPCW_Email_Verification no existe' );
            return true; // Si no está habilitada la verificación, permitir
        }
        $verified = WPCW_Email_Verification::is_email_verified( $user_id );
        error_log( 'WPCW Debug: Usuario verificado: ' . ($verified ? 'sí' : 'no') );
        return $verified;
    }

    /**
     * Genera un número único de canje
     */
    private static function generate_redemption_number() {
        global $wpdb;
        error_log( 'WPCW Debug: Generando número de canje' );
        $prefix = date( 'Ymd' );
        error_log( 'WPCW Debug: Prefijo: ' . $prefix );

        // Verificar si la tabla existe
        $table_name = $wpdb->prefix . 'wpcw_canjes';
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
            error_log( 'WPCW Debug: Tabla wpcw_canjes no existe' );
            return $prefix . wp_rand( 1000, 9999 ); // Retornar sin verificar unicidad
        }

        do {
            $number = $prefix . wp_rand( 1000, 9999 );
            error_log( 'WPCW Debug: Probando número: ' . $number );
            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}wpcw_canjes WHERE numero_canje = %s",
                    $number
                )
            );
            error_log( 'WPCW Debug: Existe: ' . $exists );
        } while ( $exists > 0 );

        error_log( 'WPCW Debug: Número generado: ' . $number );
        return $number;
    }

    /**
     * Registra el canje en la base de datos
     */
    private static function register_redemption( $user_id, $coupon_id, $token, $redemption_number ) {
        global $wpdb;
        error_log( 'WPCW Debug: Registrando canje en DB' );

        $data = array(
            'user_id'                  => $user_id,
            'coupon_id'                => $coupon_id,
            'numero_canje'             => $redemption_number,
            'token_confirmacion'       => $token,
            'estado_canje'             => 'pendiente_confirmacion',
            'fecha_solicitud_canje'    => current_time( 'mysql' ),
            'origen_canje'             => 'webapp',
        );
        error_log( 'WPCW Debug: Datos a insertar: ' . print_r( $data, true ) );

        $result = $wpdb->insert(
            $wpdb->prefix . 'wpcw_canjes',
            $data,
            array( '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
        );
        error_log( 'WPCW Debug: Resultado insert: ' . $result );
        error_log( 'WPCW Debug: Último error DB: ' . $wpdb->last_error );

        if ( $result === false ) {
            error_log( 'WPCW Debug: Error en insert' );
            return new WP_Error(
                'db_error',
                __( 'Error al registrar el canje en la base de datos.', 'wp-cupon-whatsapp' )
            );
        }

        $insert_id = $wpdb->insert_id;
        error_log( 'WPCW Debug: Insert ID: ' . $insert_id );
        return $insert_id;
    }

    /**
     * Genera el mensaje para WhatsApp
     */
    private static function generate_whatsapp_message( $coupon_id, $redemption_number, $token ) {
        error_log( 'WPCW Debug: Generando mensaje WhatsApp para coupon_id: ' . $coupon_id );
        $coupon = wpcw_get_coupon( $coupon_id );
        if ( ! $coupon ) {
            error_log( 'WPCW Debug: Cupón no encontrado' );
            return '';
        }

        // Use coupon's custom WhatsApp text if available
        $custom_text = $coupon->get_whatsapp_text();
        if ( ! empty( $custom_text ) ) {
            $message = str_replace(
                array( '{coupon_code}', '{redemption_number}', '{token}' ),
                array( $coupon->get_code(), $redemption_number, $token ),
                $custom_text
            );
        } else {
            // Fallback to default message
            $message = sprintf(
                __( 'Hola, quiero canjear mi cupón "%1$s" (Solicitud Nro: %2$s). Código de confirmación: %3$s', 'wp-cupon-whatsapp' ),
                $coupon->get_code(),
                $redemption_number,
                $token
            );
        }

        error_log( 'WPCW Debug: Mensaje generado: ' . $message );
        return $message;
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
                'estado_canje' => 'utilizado_en_pedido_wc',
                'id_pedido_wc' => $order_id,
                'fecha_uso'    => current_time( 'mysql' ),
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

    /**
     * Send notification to business about redemption request
     *
     * @param int $redemption_id Redemption ID
     * @return bool Success status
     */
    public static function notify_business_redemption_request( $redemption_id ) {
        global $wpdb;

        // Get redemption details
        $redemption = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wpcw_canjes WHERE id = %d",
                $redemption_id
            )
        );

        if ( ! $redemption ) {
            return false;
        }

        $coupon = wpcw_get_coupon( $redemption->coupon_id );
        $user = get_user_by( 'id', $redemption->user_id );

        if ( ! $coupon || ! $user ) {
            return false;
        }

        // Get business WhatsApp
        $business_id = $coupon->get_associated_business_id();
        $business_whatsapp = '';

        if ( $business_id ) {
            $business_whatsapp = get_post_meta( $business_id, '_wpcw_whatsapp', true );
        }

        if ( empty( $business_whatsapp ) ) {
            $business_whatsapp = get_option( 'wpcw_default_business_whatsapp', '' );
        }

        if ( empty( $business_whatsapp ) ) {
            WPCW_Logger::log( 'warning', 'No business WhatsApp number found for redemption notification', array(
                'redemption_id' => $redemption_id,
                'business_id' => $business_id,
            ) );
            return false;
        }

        // Create notification message
        $message = sprintf(
            __( 'Nueva solicitud de canje - Cupón: %1$s, Usuario: %2$s, Solicitud: %3$s', 'wp-cupon-whatsapp' ),
            $coupon->get_code(),
            $user->display_name,
            $redemption->numero_canje
        );

        // Clean phone number
        $clean_phone = preg_replace( '/\D/', '', $business_whatsapp );

        // Generate WhatsApp URL for business notification
        $notification_url = 'https://wa.me/' . $clean_phone . '?text=' . urlencode( $message );

        // Log the notification
        WPCW_Logger::log( 'info', 'Business notification sent', array(
            'redemption_id' => $redemption_id,
            'business_whatsapp' => $business_whatsapp,
            'notification_url' => $notification_url,
        ) );

        return $notification_url;
    }

    /**
     * Confirm redemption by business
     *
     * @param int $redemption_id Redemption ID
     * @param int $business_user_id Business user ID confirming
     * @return bool|WP_Error Success or error
     */
    public static function confirm_redemption( $redemption_id, $business_user_id ) {
        global $wpdb;

        // Get redemption
        $redemption = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wpcw_canjes WHERE id = %d",
                $redemption_id
            )
        );

        if ( ! $redemption ) {
            return new WP_Error( 'redemption_not_found', __( 'Solicitud de canje no encontrada.', 'wp-cupon-whatsapp' ) );
        }

        if ( $redemption->estado_canje !== 'pendiente_confirmacion' ) {
            return new WP_Error( 'invalid_status', __( 'Esta solicitud ya ha sido procesada.', 'wp-cupon-whatsapp' ) );
        }

        // Verify business permission
        $coupon = wpcw_get_coupon( $redemption->coupon_id );
        if ( $coupon ) {
            $business_id = $coupon->get_associated_business_id();
            if ( $business_id ) {
                // Check if business user has permission for this business
                $user_business_access = get_user_meta( $business_user_id, '_wpcw_business_access', true );
                if ( ! is_array( $user_business_access ) || ! in_array( $business_id, $user_business_access ) ) {
                    return new WP_Error( 'access_denied', __( 'No tienes permisos para confirmar este canje.', 'wp-cupon-whatsapp' ) );
                }
            }
        }

        // Update redemption status
        $result = $wpdb->update(
            $wpdb->prefix . 'wpcw_canjes',
            array(
                'estado_canje' => 'confirmado_por_negocio',
                'fecha_confirmacion_canje' => current_time( 'mysql' ),
            ),
            array( 'id' => $redemption_id ),
            array( '%s', '%s' ),
            array( '%d' )
        );

        if ( $result === false ) {
            return new WP_Error( 'db_error', __( 'Error al actualizar la base de datos.', 'wp-cupon-whatsapp' ) );
        }

        // Log the confirmation
        WPCW_Logger::log( 'info', 'Redemption confirmed by business', array(
            'redemption_id' => $redemption_id,
            'business_user_id' => $business_user_id,
        ) );

        return true;
    }
}
