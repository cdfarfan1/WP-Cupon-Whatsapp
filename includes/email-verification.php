<?php
/**
 * Email Verification Functionality
 *
 * Maneja la verificación de email para usuarios nuevos y existentes.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WPCW_Email_Verification {
    /**
     * Envía el email de verificación al usuario
     */
    public static function send_verification_email( $user_id ) {
        $user = get_user_by( 'id', $user_id );
        if ( ! $user ) {
            return false;
        }

        $token  = wp_generate_password( 32, false );
        $expiry = time() + ( get_option( 'wpcw_email_verification_expiry', 24 ) * HOUR_IN_SECONDS );

        update_user_meta( $user_id, '_wpcw_email_verification_token', $token );
        update_user_meta( $user_id, '_wpcw_email_verification_expiry', $expiry );

        $verification_link = add_query_arg(
            array(
				'wpcw_verify' => $token,
				'user'        => $user_id,
            ),
            home_url( '/' )
        );

        $subject = sprintf( __( '[%s] Verifica tu dirección de email', 'wp-cupon-whatsapp' ), get_bloginfo( 'name' ) );

        $message = sprintf(
            __( 'Hola %s,', 'wp-cupon-whatsapp' ) . "\n\n" .
            __( 'Gracias por registrarte en %s. Para completar tu registro y poder canjear cupones, por favor verifica tu dirección de email haciendo clic en el siguiente enlace:', 'wp-cupon-whatsapp' ) . "\n\n" .
            '%s' . "\n\n" .
            __( 'Este enlace expirará en %d horas.', 'wp-cupon-whatsapp' ) . "\n\n" .
            __( 'Si no has solicitado este registro, puedes ignorar este mensaje.', 'wp-cupon-whatsapp' ),
            $user->display_name,
            get_bloginfo( 'name' ),
            esc_url( $verification_link ),
            get_option( 'wpcw_email_verification_expiry', 24 )
        );

        return wp_mail( $user->user_email, $subject, $message );
    }

    /**
     * Verifica el token de email
     */
    public static function verify_email( $user_id, $token ) {
        $stored_token = get_user_meta( $user_id, '_wpcw_email_verification_token', true );
        $expiry       = get_user_meta( $user_id, '_wpcw_email_verification_expiry', true );

        if ( ! $stored_token || ! $expiry ) {
            return new WP_Error( 'invalid_token', __( 'Token de verificación inválido.', 'wp-cupon-whatsapp' ) );
        }

        if ( time() > $expiry ) {
            return new WP_Error( 'token_expired', __( 'El token de verificación ha expirado.', 'wp-cupon-whatsapp' ) );
        }

        if ( $token !== $stored_token ) {
            return new WP_Error( 'token_mismatch', __( 'Token de verificación incorrecto.', 'wp-cupon-whatsapp' ) );
        }

        update_user_meta( $user_id, '_wpcw_email_verified', true );
        delete_user_meta( $user_id, '_wpcw_email_verification_token' );
        delete_user_meta( $user_id, '_wpcw_email_verification_expiry' );

        return true;
    }

    /**
     * Verifica si el email del usuario está verificado
     */
    public static function is_email_verified( $user_id ) {
        return (bool) get_user_meta( $user_id, '_wpcw_email_verified', true );
    }

    /**
     * Hook para manejar la verificación de email en el registro
     */
    public static function handle_new_user_registration( $user_id ) {
        if ( ! get_option( 'wpcw_email_verification_enabled', true ) ) {
            return;
        }

        self::send_verification_email( $user_id );
    }

    /**
     * Verifica que el usuario tenga el email verificado antes de permitir canjear cupones
     */
    public static function verify_before_coupon_redemption( $user_id, $coupon_id ) {
        if ( ! get_option( 'wpcw_email_verification_enabled', true ) ) {
            return true;
        }

        if ( ! self::is_email_verified( $user_id ) ) {
            return new WP_Error(
                'email_not_verified',
                __( 'Debes verificar tu email antes de poder canjear cupones. Revisa tu bandeja de entrada o solicita un nuevo email de verificación.', 'wp-cupon-whatsapp' )
            );
        }

        return true;
    }
}

// Hooks para la verificación de email
add_action( 'user_register', array( 'WPCW_Email_Verification', 'handle_new_user_registration' ) );

// Hook para verificar email antes de canjear cupones
add_filter( 'wpcw_before_coupon_redemption', array( 'WPCW_Email_Verification', 'verify_before_coupon_redemption' ), 10, 2 );

// Manejador para la verificación de email vía URL
function wpcw_handle_email_verification() {
    if ( isset( $_GET['wpcw_verify'] ) && isset( $_GET['user'] ) ) {
        $token   = sanitize_text_field( $_GET['wpcw_verify'] );
        $user_id = absint( $_GET['user'] );

        $result = WPCW_Email_Verification::verify_email( $user_id, $token );

        if ( is_wp_error( $result ) ) {
            wp_die( $result->get_error_message(), __( 'Error de Verificación', 'wp-cupon-whatsapp' ) );
        } else {
            $dashboard_url = wc_get_account_endpoint_url( 'dashboard' );
            $redirect_url  = $dashboard_url ? add_query_arg( 'email_verified', '1', $dashboard_url ) : home_url( '/?email_verified=1' );
            wp_redirect( $redirect_url );
            exit;
        }
    }
}
add_action( 'init', 'wpcw_handle_email_verification' );
