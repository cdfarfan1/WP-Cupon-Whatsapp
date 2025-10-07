<?php
/**
 * WP Cupón WhatsApp - AJAX Handlers
 *
 * Centraliza todos los handlers AJAX del plugin
 *
 * @package WP_Cupon_WhatsApp
 * @since 1.5.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class WPCW_AJAX_Handlers
 *
 * Handles all AJAX requests for the plugin
 */
class WPCW_AJAX_Handlers {

    /**
     * Initialize AJAX handlers
     */
    public static function init() {
        // Admin AJAX handlers
        add_action( 'wp_ajax_wpcw_admin_action', array( __CLASS__, 'handle_admin_action' ) );
        add_action( 'wp_ajax_wpcw_approve_redemption', array( __CLASS__, 'approve_redemption' ) );
        add_action( 'wp_ajax_wpcw_reject_redemption', array( __CLASS__, 'reject_redemption' ) );
        add_action( 'wp_ajax_wpcw_bulk_process_redemptions', array( __CLASS__, 'bulk_process_redemptions' ) );
        add_action( 'wp_ajax_wpcw_approve_business', array( __CLASS__, 'approve_business_application' ) );
        add_action( 'wp_ajax_wpcw_reject_business', array( __CLASS__, 'reject_business_application' ) );

        // Public AJAX handlers (logged in users)
        add_action( 'wp_ajax_wpcw_public_action', array( __CLASS__, 'handle_public_action' ) );
        add_action( 'wp_ajax_wpcw_redeem_coupon', array( __CLASS__, 'redeem_coupon' ) );
        add_action( 'wp_ajax_wpcw_submit_business_application', array( __CLASS__, 'submit_business_application' ) );

        // Public AJAX handlers (non-logged in users)
        add_action( 'wp_ajax_nopriv_wpcw_public_action', array( __CLASS__, 'handle_public_action' ) );
        add_action( 'wp_ajax_nopriv_wpcw_submit_business_application', array( __CLASS__, 'submit_business_application' ) );
    }

    /**
     * Handle generic admin AJAX action
     */
    public static function handle_admin_action() {
        // Verify nonce
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        // Check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array(
                'message' => __( 'Permisos insuficientes', 'wp-cupon-whatsapp' ),
            ) );
        }

        // Get action
        $item_action = isset( $_POST['item_action'] ) ? sanitize_text_field( $_POST['item_action'] ) : '';
        $item_id = isset( $_POST['item_id'] ) ? absint( $_POST['item_id'] ) : 0;

        // Route to specific handler based on action
        switch ( $item_action ) {
            case 'delete_coupon':
                self::delete_coupon( $item_id );
                break;

            case 'delete_business':
                self::delete_business( $item_id );
                break;

            default:
                wp_send_json_error( array(
                    'message' => __( 'Acción no válida', 'wp-cupon-whatsapp' ),
                ) );
        }
    }

    /**
     * Handle generic public AJAX action
     */
    public static function handle_public_action() {
        // Verify nonce
        check_ajax_referer( 'wpcw_public_nonce', 'nonce' );

        // Get operation
        $operation = isset( $_POST['operation'] ) ? sanitize_text_field( $_POST['operation'] ) : '';

        // Route to specific handler
        switch ( $operation ) {
            case 'redeem_coupon':
                self::redeem_coupon();
                break;

            default:
                wp_send_json_error( array(
                    'message' => __( 'Operación no válida', 'wp-cupon-whatsapp' ),
                ) );
        }
    }

    /**
     * Redeem coupon via AJAX
     */
    public static function redeem_coupon() {
        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array(
                'message' => __( 'Debes iniciar sesión para canjear cupones', 'wp-cupon-whatsapp' ),
                'redirect' => wp_login_url(),
            ) );
        }

        $coupon_id = isset( $_POST['coupon_id'] ) ? absint( $_POST['coupon_id'] ) : 0;
        $business_id = isset( $_POST['business_id'] ) ? absint( $_POST['business_id'] ) : 0;
        $user_id = get_current_user_id();

        if ( ! $coupon_id ) {
            wp_send_json_error( array(
                'message' => __( 'Cupón no válido', 'wp-cupon-whatsapp' ),
            ) );
        }

        // Process redemption
        $result = WPCW_Redemption_Handler::initiate_redemption( $coupon_id, $user_id, $business_id );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array(
                'message' => $result->get_error_message(),
            ) );
        }

        // Get redemption details
        global $wpdb;
        $redemption = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wpcw_canjes WHERE id = %d",
                $result
            )
        );

        wp_send_json_success( array(
            'message' => __( 'Cupón canjeado exitosamente', 'wp-cupon-whatsapp' ),
            'redemption_id' => $result,
            'whatsapp_url' => $redemption ? $redemption->whatsapp_url : '',
        ) );
    }

    /**
     * Approve redemption
     */
    public static function approve_redemption() {
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array(
                'message' => __( 'Permisos insuficientes', 'wp-cupon-whatsapp' ),
            ) );
        }

        $redemption_id = isset( $_POST['redemption_id'] ) ? absint( $_POST['redemption_id'] ) : 0;

        if ( ! $redemption_id ) {
            wp_send_json_error( array(
                'message' => __( 'ID de canje no válido', 'wp-cupon-whatsapp' ),
            ) );
        }

        $result = WPCW_Redemption_Handler::confirm_redemption( $redemption_id, get_current_user_id() );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array(
                'message' => $result->get_error_message(),
            ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Canje aprobado exitosamente', 'wp-cupon-whatsapp' ),
            'reload' => true,
        ) );
    }

    /**
     * Reject redemption
     */
    public static function reject_redemption() {
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array(
                'message' => __( 'Permisos insuficientes', 'wp-cupon-whatsapp' ),
            ) );
        }

        $redemption_id = isset( $_POST['redemption_id'] ) ? absint( $_POST['redemption_id'] ) : 0;
        $reason = isset( $_POST['reason'] ) ? sanitize_textarea_field( $_POST['reason'] ) : '';

        if ( ! $redemption_id ) {
            wp_send_json_error( array(
                'message' => __( 'ID de canje no válido', 'wp-cupon-whatsapp' ),
            ) );
        }

        $result = WPCW_Redemption_Manager::reject_redemption( $redemption_id, $reason );

        if ( ! $result ) {
            wp_send_json_error( array(
                'message' => __( 'Error al rechazar el canje', 'wp-cupon-whatsapp' ),
            ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Canje rechazado', 'wp-cupon-whatsapp' ),
            'reload' => true,
        ) );
    }

    /**
     * Bulk process redemptions
     */
    public static function bulk_process_redemptions() {
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array(
                'message' => __( 'Permisos insuficientes', 'wp-cupon-whatsapp' ),
            ) );
        }

        $redemption_ids = isset( $_POST['redemption_ids'] ) ? array_map( 'absint', $_POST['redemption_ids'] ) : array();
        $action = isset( $_POST['bulk_action'] ) ? sanitize_text_field( $_POST['bulk_action'] ) : '';
        $reason = isset( $_POST['reason'] ) ? sanitize_textarea_field( $_POST['reason'] ) : '';

        if ( empty( $redemption_ids ) ) {
            wp_send_json_error( array(
                'message' => __( 'No se seleccionaron canjes', 'wp-cupon-whatsapp' ),
            ) );
        }

        $results = WPCW_Redemption_Manager::bulk_process_redemptions( $redemption_ids, $action, $reason );

        wp_send_json_success( array(
            'message' => sprintf(
                __( '%d canjes procesados correctamente', 'wp-cupon-whatsapp' ),
                $results['success']
            ),
            'results' => $results,
            'reload' => true,
        ) );
    }

    /**
     * Approve business application
     */
    public static function approve_business_application() {
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array(
                'message' => __( 'Permisos insuficientes', 'wp-cupon-whatsapp' ),
            ) );
        }

        $application_id = isset( $_POST['application_id'] ) ? absint( $_POST['application_id'] ) : 0;

        if ( ! $application_id ) {
            wp_send_json_error( array(
                'message' => __( 'ID de solicitud no válido', 'wp-cupon-whatsapp' ),
            ) );
        }

        $result = WPCW_Business_Manager::approve_application( $application_id );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array(
                'message' => $result->get_error_message(),
            ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Solicitud aprobada exitosamente', 'wp-cupon-whatsapp' ),
            'business_id' => $result,
            'reload' => true,
        ) );
    }

    /**
     * Reject business application
     */
    public static function reject_business_application() {
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array(
                'message' => __( 'Permisos insuficientes', 'wp-cupon-whatsapp' ),
            ) );
        }

        $application_id = isset( $_POST['application_id'] ) ? absint( $_POST['application_id'] ) : 0;
        $reason = isset( $_POST['reason'] ) ? sanitize_textarea_field( $_POST['reason'] ) : '';

        if ( ! $application_id ) {
            wp_send_json_error( array(
                'message' => __( 'ID de solicitud no válido', 'wp-cupon-whatsapp' ),
            ) );
        }

        $result = WPCW_Business_Manager::reject_application( $application_id, $reason );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array(
                'message' => $result->get_error_message(),
            ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Solicitud rechazada', 'wp-cupon-whatsapp' ),
            'reload' => true,
        ) );
    }

    /**
     * Submit business application (public)
     */
    public static function submit_business_application() {
        check_ajax_referer( 'wpcw_public_nonce', 'nonce' );

        // Validate and sanitize form data
        $business_name = isset( $_POST['business_name'] ) ? sanitize_text_field( $_POST['business_name'] ) : '';
        $legal_name = isset( $_POST['legal_name'] ) ? sanitize_text_field( $_POST['legal_name'] ) : '';
        $cuit = isset( $_POST['cuit'] ) ? sanitize_text_field( $_POST['cuit'] ) : '';
        $contact_person = isset( $_POST['contact_person'] ) ? sanitize_text_field( $_POST['contact_person'] ) : '';
        $email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
        $whatsapp = isset( $_POST['whatsapp'] ) ? sanitize_text_field( $_POST['whatsapp'] ) : '';
        $address = isset( $_POST['address'] ) ? sanitize_textarea_field( $_POST['address'] ) : '';

        // Validation
        if ( empty( $business_name ) || empty( $email ) || empty( $contact_person ) ) {
            wp_send_json_error( array(
                'message' => __( 'Por favor completa todos los campos requeridos', 'wp-cupon-whatsapp' ),
            ) );
        }

        // Create application post
        $application_data = array(
            'post_title' => $business_name,
            'post_type' => 'wpcw_application',
            'post_status' => 'publish',
        );

        $application_id = wp_insert_post( $application_data );

        if ( is_wp_error( $application_id ) ) {
            wp_send_json_error( array(
                'message' => __( 'Error al enviar la solicitud', 'wp-cupon-whatsapp' ),
            ) );
        }

        // Save meta data
        update_post_meta( $application_id, '_wpcw_legal_name', $legal_name );
        update_post_meta( $application_id, '_wpcw_cuit', $cuit );
        update_post_meta( $application_id, '_wpcw_contact_person', $contact_person );
        update_post_meta( $application_id, '_wpcw_email', $email );
        update_post_meta( $application_id, '_wpcw_whatsapp', $whatsapp );
        update_post_meta( $application_id, '_wpcw_address_main', $address );
        update_post_meta( $application_id, '_wpcw_application_status', 'pendiente' );

        wp_send_json_success( array(
            'message' => __( 'Solicitud enviada exitosamente', 'wp-cupon-whatsapp' ),
            'application_id' => $application_id,
        ) );
    }

    /**
     * Delete coupon
     */
    private static function delete_coupon( $coupon_id ) {
        if ( ! $coupon_id ) {
            wp_send_json_error( array(
                'message' => __( 'ID de cupón no válido', 'wp-cupon-whatsapp' ),
            ) );
        }

        $result = wp_delete_post( $coupon_id, true );

        if ( ! $result ) {
            wp_send_json_error( array(
                'message' => __( 'Error al eliminar el cupón', 'wp-cupon-whatsapp' ),
            ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Cupón eliminado exitosamente', 'wp-cupon-whatsapp' ),
            'reload' => true,
        ) );
    }

    /**
     * Delete business
     */
    private static function delete_business( $business_id ) {
        if ( ! $business_id ) {
            wp_send_json_error( array(
                'message' => __( 'ID de comercio no válido', 'wp-cupon-whatsapp' ),
            ) );
        }

        $result = wp_delete_post( $business_id, true );

        if ( ! $result ) {
            wp_send_json_error( array(
                'message' => __( 'Error al eliminar el comercio', 'wp-cupon-whatsapp' ),
            ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Comercio eliminado exitosamente', 'wp-cupon-whatsapp' ),
            'reload' => true,
        ) );
    }
}

// Initialize AJAX handlers
WPCW_AJAX_Handlers::init();
