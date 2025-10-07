<?php
/**
 * WP Cupón WhatsApp - Extended WooCommerce Coupon
 *
 * Extends WC_Coupon to add WhatsApp redemption functionality
 *
 * @package WP_Cupon_WhatsApp
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * WPCW_Coupon class - extends WC_Coupon
 */
class WPCW_Coupon extends WC_Coupon {

    /**
     * Constructor
     *
     * @param int|WC_Coupon|WP_Post $coupon Coupon ID, WC_Coupon object, or WP_Post object
     */
    public function __construct( $coupon = 0 ) {
        parent::__construct( $coupon );

        // Add custom meta keys for WPCW
        $this->meta_keys = array_merge( $this->meta_keys, array(
            'wpcw_enabled' => '_wpcw_enabled',
            'wpcw_associated_business_id' => '_wpcw_associated_business_id',
            'wpcw_is_loyalty_coupon' => '_wpcw_is_loyalty_coupon',
            'wpcw_is_public_coupon' => '_wpcw_is_public_coupon',
            'wpcw_whatsapp_text' => '_wpcw_whatsapp_text',
            'wpcw_redemption_hours' => '_wpcw_redemption_hours',
            'wpcw_expiry_reminder' => '_wpcw_expiry_reminder',
            'wpcw_max_uses_per_user' => '_wpcw_max_uses_per_user',
        ) );
    }

    /**
     * Check if coupon is enabled for WhatsApp redemption
     *
     * @return bool
     */
    public function is_wpcw_enabled() {
        return 'yes' === $this->get_meta( 'wpcw_enabled' );
    }

    /**
     * Enable/disable WhatsApp redemption
     *
     * @param bool $enabled
     */
    public function set_wpcw_enabled( $enabled ) {
        $this->update_meta_data( 'wpcw_enabled', $enabled ? 'yes' : 'no' );
    }

    /**
     * Get associated business ID
     *
     * @return int
     */
    public function get_associated_business_id() {
        return (int) $this->get_meta( 'wpcw_associated_business_id' );
    }

    /**
     * Set associated business ID
     *
     * @param int $business_id
     */
    public function set_associated_business_id( $business_id ) {
        $this->update_meta_data( 'wpcw_associated_business_id', $business_id );
    }

    /**
     * Check if it's a loyalty coupon
     *
     * @return bool
     */
    public function is_loyalty_coupon() {
        return 'yes' === $this->get_meta( 'wpcw_is_loyalty_coupon' );
    }

    /**
     * Set as loyalty coupon
     *
     * @param bool $is_loyalty
     */
    public function set_loyalty_coupon( $is_loyalty ) {
        $this->update_meta_data( 'wpcw_is_loyalty_coupon', $is_loyalty ? 'yes' : 'no' );
    }

    /**
     * Check if it's a public coupon
     *
     * @return bool
     */
    public function is_public_coupon() {
        return 'yes' === $this->get_meta( 'wpcw_is_public_coupon' );
    }

    /**
     * Set as public coupon
     *
     * @param bool $is_public
     */
    public function set_public_coupon( $is_public ) {
        $this->update_meta_data( 'wpcw_is_public_coupon', $is_public ? 'yes' : 'no' );
    }

    /**
     * Get WhatsApp message text
     *
     * @return string
     */
    public function get_whatsapp_text() {
        $text = $this->get_meta( 'wpcw_whatsapp_text' );
        if ( empty( $text ) ) {
            $text = sprintf(
                __( '¡Hola! Tengo el cupón %s y quiero canjearlo. Código: %s', 'wp-cupon-whatsapp' ),
                $this->get_code(),
                $this->get_code()
            );
        }
        return $text;
    }

    /**
     * Set WhatsApp message text
     *
     * @param string $text
     */
    public function set_whatsapp_text( $text ) {
        $this->update_meta_data( 'wpcw_whatsapp_text', sanitize_textarea_field( $text ) );
    }

    /**
     * Get redemption hours
     *
     * @return string
     */
    public function get_redemption_hours() {
        return $this->get_meta( 'wpcw_redemption_hours' );
    }

    /**
     * Set redemption hours
     *
     * @param string $hours
     */
    public function set_redemption_hours( $hours ) {
        $this->update_meta_data( 'wpcw_redemption_hours', sanitize_text_field( $hours ) );
    }

    /**
     * Get expiry reminder days
     *
     * @return int
     */
    public function get_expiry_reminder() {
        return (int) $this->get_meta( 'wpcw_expiry_reminder' );
    }

    /**
     * Set expiry reminder days
     *
     * @param int $days
     */
    public function set_expiry_reminder( $days ) {
        $this->update_meta_data( 'wpcw_expiry_reminder', absint( $days ) );
    }

    /**
     * Get max uses per user
     *
     * @return int
     */
    public function get_max_uses_per_user() {
        return (int) $this->get_meta( 'wpcw_max_uses_per_user' );
    }

    /**
     * Set max uses per user
     *
     * @param int $max_uses
     */
    public function set_max_uses_per_user( $max_uses ) {
        $this->update_meta_data( 'wpcw_max_uses_per_user', absint( $max_uses ) );
    }

    /**
     * Generate WhatsApp redemption URL
     *
     * @param int $user_id User ID for business WhatsApp number
     * @param string $redemption_number Redemption number
     * @param string $token Confirmation token
     * @return string WhatsApp URL
     */
    public function get_whatsapp_redemption_url( $user_id = 0, $redemption_number = '', $token = '' ) {
        $business_id = $this->get_associated_business_id();

        if ( $business_id ) {
            $business_whatsapp = get_post_meta( $business_id, '_wpcw_whatsapp', true );
        } else {
            $business_whatsapp = get_option( 'wpcw_default_business_whatsapp', '' );
        }

        if ( empty( $business_whatsapp ) ) {
            return '';
        }

        // Generate message using redemption handler
        if ( ! empty( $redemption_number ) && ! empty( $token ) ) {
            $message = WPCW_Redemption_Handler::generate_whatsapp_message( $this->get_id(), $redemption_number, $token );
        } else {
            // Fallback to simple message
            $message = $this->get_whatsapp_text();
            $message = str_replace( '{coupon_code}', $this->get_code(), $message );
            $message = str_replace( '{user_id}', $user_id, $message );
        }

        // Clean phone number
        $clean_phone = preg_replace( '/\D/', '', $business_whatsapp );

        return 'https://wa.me/' . $clean_phone . '?text=' . urlencode( $message );
    }

    /**
     * Check if user can redeem this coupon
     *
     * @param int $user_id User ID
     * @return bool|WP_Error
     */
    public function can_user_redeem( $user_id ) {
        // Check if coupon is enabled for WPCW
        if ( ! $this->is_wpcw_enabled() ) {
            return new WP_Error( 'coupon_not_enabled', __( 'Este cupón no está habilitado para canje por WhatsApp.', 'wp-cupon-whatsapp' ) );
        }

        // Check if coupon is expired
        if ( $this->get_date_expires() && time() > $this->get_date_expires()->getTimestamp() ) {
            return new WP_Error( 'coupon_expired', __( 'Este cupón ha expirado.', 'wp-cupon-whatsapp' ) );
        }

        // Check usage limits
        if ( $this->get_usage_limit() && $this->get_usage_count() >= $this->get_usage_limit() ) {
            return new WP_Error( 'usage_limit_reached', __( 'Se ha alcanzado el límite de uso de este cupón.', 'wp-cupon-whatsapp' ) );
        }

        // Check user-specific limits
        $max_uses_per_user = $this->get_max_uses_per_user();
        if ( $max_uses_per_user > 0 ) {
            $user_usage = $this->get_usage_by_user_id( $user_id );
            if ( $user_usage >= $max_uses_per_user ) {
                return new WP_Error( 'user_limit_reached', __( 'Has alcanzado el límite de uso de este cupón.', 'wp-cupon-whatsapp' ) );
            }
        }

        // Check if user has WhatsApp number
        $user_whatsapp = get_user_meta( $user_id, '_wpcw_whatsapp_number', true );
        if ( empty( $user_whatsapp ) ) {
            return new WP_Error( 'no_whatsapp', __( 'Debes configurar tu número de WhatsApp para canjear cupones.', 'wp-cupon-whatsapp' ) );
        }

        // Check if it's a loyalty coupon and user belongs to institution
        if ( $this->is_loyalty_coupon() ) {
            $user_institution = get_user_meta( $user_id, '_wpcw_user_institution_id', true );
            if ( empty( $user_institution ) ) {
                return new WP_Error( 'not_loyalty_member', __( 'Este cupón es solo para miembros de lealtad.', 'wp-cupon-whatsapp' ) );
            }
        }

        return true;
    }

    /**
     * Get usage count by user ID
     *
     * @param int $user_id User ID
     * @return int
     */
    public function get_usage_by_user_id( $user_id ) {
        global $wpdb;

        $used_by = $this->get_used_by();

        if ( ! is_array( $used_by ) ) {
            return 0;
        }

        return count( array_filter( $used_by, function( $usage ) use ( $user_id ) {
            return isset( $usage['user_id'] ) && $usage['user_id'] == $user_id;
        } ) );
    }
}

/**
 * Factory function to create WPCW_Coupon instance
 *
 * @param int|WC_Coupon|WP_Post $coupon Coupon ID, WC_Coupon object, or WP_Post object
 * @return WPCW_Coupon
 */
function wpcw_get_coupon( $coupon = 0 ) {
    return new WPCW_Coupon( $coupon );
}