<?php
/**
 * WP Cupón WhatsApp - Business Manager Class
 *
 * Handles business registration, approval, and management
 *
 * @package WP_Cupon_WhatsApp
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * WPCW_Business_Manager class
 */
class WPCW_Business_Manager {

    /**
     * Get business applications with filtering
     *
     * @param array $args Query arguments
     * @return array Business applications
     */
    public static function get_business_applications( $args = array() ) {
        $defaults = array(
            'post_type' => 'wpcw_application',
            'post_status' => 'publish',
            'posts_per_page' => 20,
            'paged' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array(),
        );

        $args = wp_parse_args( $args, $defaults );

        // Add status filter if specified
        if ( isset( $args['application_status'] ) && ! empty( $args['application_status'] ) ) {
            $args['meta_query'][] = array(
                'key' => '_wpcw_application_status',
                'value' => $args['application_status'],
                'compare' => '=',
            );
        }

        // Add applicant type filter if specified
        if ( isset( $args['applicant_type'] ) && ! empty( $args['applicant_type'] ) ) {
            $args['meta_query'][] = array(
                'key' => '_wpcw_applicant_type',
                'value' => $args['applicant_type'],
                'compare' => '=',
            );
        }

        $query = new WP_Query( $args );

        $applications = array();
        foreach ( $query->posts as $post ) {
            $applications[] = self::format_application_data( $post );
        }

        return array(
            'applications' => $applications,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'current_page' => $args['paged'],
        );
    }

    /**
     * Get businesses with filtering
     *
     * @param array $args Query arguments
     * @return array Businesses
     */
    public static function get_businesses( $args = array() ) {
        $defaults = array(
            'post_type' => 'wpcw_business',
            'post_status' => 'publish',
            'posts_per_page' => 20,
            'paged' => 1,
            'orderby' => 'title',
            'order' => 'ASC',
            'meta_query' => array(),
        );

        $args = wp_parse_args( $args, $defaults );

        // Add search filter if specified
        if ( isset( $args['s'] ) && ! empty( $args['s'] ) ) {
            $args['s'] = $args['s'];
        }

        $query = new WP_Query( $args );

        $businesses = array();
        foreach ( $query->posts as $post ) {
            $businesses[] = self::format_business_data( $post );
        }

        return array(
            'businesses' => $businesses,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'current_page' => $args['paged'],
        );
    }

    /**
     * Approve business application
     *
     * @param int $application_id Application ID
     * @return bool|WP_Error Success or error
     */
    public static function approve_application( $application_id ) {
        $application = get_post( $application_id );

        if ( ! $application || $application->post_type !== 'wpcw_application' ) {
            return new WP_Error( 'invalid_application', __( 'Solicitud no válida.', 'wp-cupon-whatsapp' ) );
        }

        // Check if already approved
        $status = get_post_meta( $application_id, '_wpcw_application_status', true );
        if ( $status === 'aprobada' ) {
            return new WP_Error( 'already_approved', __( 'Esta solicitud ya ha sido aprobada.', 'wp-cupon-whatsapp' ) );
        }

        // Get application data
        $applicant_type = get_post_meta( $application_id, '_wpcw_applicant_type', true );
        $legal_name = get_post_meta( $application_id, '_wpcw_legal_name', true );
        $cuit = get_post_meta( $application_id, '_wpcw_cuit', true );
        $contact_person = get_post_meta( $application_id, '_wpcw_contact_person', true );
        $email = get_post_meta( $application_id, '_wpcw_email', true );
        $whatsapp = get_post_meta( $application_id, '_wpcw_whatsapp', true );
        $address_main = get_post_meta( $application_id, '_wpcw_address_main', true );

        // Create business post
        $business_data = array(
            'post_title' => $application->post_title,
            'post_content' => $application->post_content,
            'post_type' => 'wpcw_business',
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        );

        $business_id = wp_insert_post( $business_data );

        if ( is_wp_error( $business_id ) ) {
            return $business_id;
        }

        // Save business meta data
        update_post_meta( $business_id, '_wpcw_legal_name', $legal_name );
        update_post_meta( $business_id, '_wpcw_cuit', $cuit );
        update_post_meta( $business_id, '_wpcw_contact_person', $contact_person );
        update_post_meta( $business_id, '_wpcw_email', $email );
        update_post_meta( $business_id, '_wpcw_whatsapp', $whatsapp );
        update_post_meta( $business_id, '_wpcw_address_main', $address_main );
        update_post_meta( $business_id, '_wpcw_application_id', $application_id );
        update_post_meta( $business_id, '_wpcw_approval_date', current_time( 'mysql' ) );

        // Update application status
        update_post_meta( $application_id, '_wpcw_application_status', 'aprobada' );
        update_post_meta( $application_id, '_wpcw_approved_business_id', $business_id );
        update_post_meta( $application_id, '_wpcw_approval_date', current_time( 'mysql' ) );

        // Create business owner user if needed
        $user_id = get_post_meta( $application_id, '_wpcw_created_user_id', true );
        if ( $user_id ) {
            // Assign business to user
            update_user_meta( $user_id, '_wpcw_business_id', $business_id );
            update_user_meta( $user_id, '_wpcw_user_type', 'business_owner' );

            // Add business owner role if not already
            $user = new WP_User( $user_id );
            if ( ! in_array( 'wpcw_business_owner', $user->roles ) ) {
                $user->add_role( 'wpcw_business_owner' );
            }
        }

        // Send approval notification
        self::send_approval_notification( $application_id, $business_id );

        // Log the approval
        WPCW_Logger::log( 'info', 'Business application approved', array(
            'application_id' => $application_id,
            'business_id' => $business_id,
            'approved_by' => get_current_user_id(),
        ) );

        return $business_id;
    }

    /**
     * Reject business application
     *
     * @param int $application_id Application ID
     * @param string $reason Rejection reason
     * @return bool|WP_Error Success or error
     */
    public static function reject_application( $application_id, $reason = '' ) {
        $application = get_post( $application_id );

        if ( ! $application || $application->post_type !== 'wpcw_application' ) {
            return new WP_Error( 'invalid_application', __( 'Solicitud no válida.', 'wp-cupon-whatsapp' ) );
        }

        // Update application status
        update_post_meta( $application_id, '_wpcw_application_status', 'rechazada' );
        update_post_meta( $application_id, '_wpcw_rejection_reason', $reason );
        update_post_meta( $application_id, '_wpcw_rejection_date', current_time( 'mysql' ) );

        // Send rejection notification
        self::send_rejection_notification( $application_id, $reason );

        // Log the rejection
        WPCW_Logger::log( 'info', 'Business application rejected', array(
            'application_id' => $application_id,
            'reason' => $reason,
            'rejected_by' => get_current_user_id(),
        ) );

        return true;
    }

    /**
     * Send approval notification
     *
     * @param int $application_id Application ID
     * @param int $business_id Business ID
     */
    private static function send_approval_notification( $application_id, $business_id ) {
        $application = get_post( $application_id );
        $business = get_post( $business_id );
        $email = get_post_meta( $application_id, '_wpcw_email', true );

        if ( ! $email ) {
            return;
        }

        $subject = sprintf( __( '¡Felicitaciones! Su solicitud para %s ha sido aprobada', 'wp-cupon-whatsapp' ), $application->post_title );

        $message = sprintf(
            __( 'Estimado %s,

Su solicitud de adhesión al programa WP Cupón WhatsApp ha sido APROBADA.

Detalles de su comercio:
- Nombre: %s
- ID de Comercio: %s

Ya puede comenzar a crear y gestionar sus cupones de descuento.

Para acceder al panel de administración:
%s

Atentamente,
Equipo WP Cupón WhatsApp', 'wp-cupon-whatsapp' ),
            get_post_meta( $application_id, '_wpcw_contact_person', true ),
            $business->post_title,
            $business_id,
            admin_url()
        );

        wp_mail( $email, $subject, $message );
    }

    /**
     * Send rejection notification
     *
     * @param int $application_id Application ID
     * @param string $reason Rejection reason
     */
    private static function send_rejection_notification( $application_id, $reason = '' ) {
        $application = get_post( $application_id );
        $email = get_post_meta( $application_id, '_wpcw_email', true );

        if ( ! $email ) {
            return;
        }

        $subject = sprintf( __( 'Actualización sobre su solicitud para %s', 'wp-cupon-whatsapp' ), $application->post_title );

        $message = sprintf(
            __( 'Estimado %s,

Lamentablemente, su solicitud de adhesión al programa WP Cupón WhatsApp ha sido rechazada.

%s

Si tiene alguna pregunta, no dude en contactarnos.

Atentamente,
Equipo WP Cupón WhatsApp', 'wp-cupon-whatsapp' ),
            get_post_meta( $application_id, '_wpcw_contact_person', true ),
            ! empty( $reason ) ? sprintf( __( 'Razón: %s', 'wp-cupon-whatsapp' ), $reason ) : ''
        );

        wp_mail( $email, $subject, $message );
    }

    /**
     * Format application data for display
     *
     * @param WP_Post $post Application post
     * @return array Formatted application data
     */
    private static function format_application_data( $post ) {
        return array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'status' => get_post_meta( $post->ID, '_wpcw_application_status', true ),
            'applicant_type' => get_post_meta( $post->ID, '_wpcw_applicant_type', true ),
            'legal_name' => get_post_meta( $post->ID, '_wpcw_legal_name', true ),
            'cuit' => get_post_meta( $post->ID, '_wpcw_cuit', true ),
            'contact_person' => get_post_meta( $post->ID, '_wpcw_contact_person', true ),
            'email' => get_post_meta( $post->ID, '_wpcw_email', true ),
            'whatsapp' => get_post_meta( $post->ID, '_wpcw_whatsapp', true ),
            'address' => get_post_meta( $post->ID, '_wpcw_address_main', true ),
            'created_date' => $post->post_date,
            'created_user_id' => get_post_meta( $post->ID, '_wpcw_created_user_id', true ),
            'approved_business_id' => get_post_meta( $post->ID, '_wpcw_approved_business_id', true ),
            'rejection_reason' => get_post_meta( $post->ID, '_wpcw_rejection_reason', true ),
        );
    }

    /**
     * Format business data for display
     *
     * @param WP_Post $post Business post
     * @return array Formatted business data
     */
    private static function format_business_data( $post ) {
        return array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'legal_name' => get_post_meta( $post->ID, '_wpcw_legal_name', true ),
            'cuit' => get_post_meta( $post->ID, '_wpcw_cuit', true ),
            'contact_person' => get_post_meta( $post->ID, '_wpcw_contact_person', true ),
            'email' => get_post_meta( $post->ID, '_wpcw_email', true ),
            'whatsapp' => get_post_meta( $post->ID, '_wpcw_whatsapp', true ),
            'address' => get_post_meta( $post->ID, '_wpcw_address_main', true ),
            'owner_user_id' => get_post_meta( $post->ID, '_wpcw_owner_user_id', true ),
            'application_id' => get_post_meta( $post->ID, '_wpcw_application_id', true ),
            'approval_date' => get_post_meta( $post->ID, '_wpcw_approval_date', true ),
            'created_date' => $post->post_date,
        );
    }

    /**
     * Get business statistics
     *
     * @param int $business_id Business ID
     * @return array Business statistics
     */
    public static function get_business_stats( $business_id ) {
        global $wpdb;

        $stats = array(
            'coupons_total' => 0,
            'coupons_active' => 0,
            'redemptions_total' => 0,
            'redemptions_this_month' => 0,
            'users_served' => 0,
        );

        // Count coupons for this business
        $coupons = get_posts( array(
            'post_type' => 'shop_coupon',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_wpcw_associated_business_id',
                    'value' => $business_id,
                    'compare' => '=',
                ),
            ),
        ) );

        $stats['coupons_total'] = count( $coupons );

        // Count active coupons
        $active_coupons = 0;
        foreach ( $coupons as $coupon ) {
            $expiry = get_post_meta( $coupon->ID, 'date_expires', true );
            if ( empty( $expiry ) || strtotime( $expiry ) > time() ) {
                $active_coupons++;
            }
        }
        $stats['coupons_active'] = $active_coupons;

        // Get redemptions for this business
        $redemptions_table = $wpdb->prefix . 'wpcw_canjes';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$redemptions_table'" ) == $redemptions_table ) {
            $stats['redemptions_total'] = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $redemptions_table WHERE comercio_id = %d",
                    $business_id
                )
            );

            $stats['redemptions_this_month'] = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM $redemptions_table
                    WHERE comercio_id = %d
                    AND MONTH(fecha_solicitud_canje) = MONTH(CURRENT_DATE())
                    AND YEAR(fecha_solicitud_canje) = YEAR(CURRENT_DATE())",
                    $business_id
                )
            );

            // Count unique users served
            $stats['users_served'] = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(DISTINCT user_id) FROM $redemptions_table WHERE comercio_id = %d",
                    $business_id
                )
            );
        }

        return $stats;
    }

    /**
     * Assign user to business
     *
     * @param int $user_id User ID
     * @param int $business_id Business ID
     * @param string $role User role in business
     * @return bool|WP_Error Success or error
     */
    public static function assign_user_to_business( $user_id, $business_id, $role = 'staff' ) {
        $user = get_user_by( 'id', $user_id );
        $business = get_post( $business_id );

        if ( ! $user ) {
            return new WP_Error( 'invalid_user', __( 'Usuario no válido.', 'wp-cupon-whatsapp' ) );
        }

        if ( ! $business || $business->post_type !== 'wpcw_business' ) {
            return new WP_Error( 'invalid_business', __( 'Comercio no válido.', 'wp-cupon-whatsapp' ) );
        }

        // Update user meta
        update_user_meta( $user_id, '_wpcw_business_id', $business_id );
        update_user_meta( $user_id, '_wpcw_business_role', $role );

        // Add business access
        $business_access = get_user_meta( $user_id, '_wpcw_business_access', true );
        if ( ! is_array( $business_access ) ) {
            $business_access = array();
        }
        $business_access[] = $business_id;
        $business_access = array_unique( $business_access );
        update_user_meta( $user_id, '_wpcw_business_access', $business_access );

        // Add appropriate role
        if ( $role === 'owner' ) {
            if ( ! in_array( 'wpcw_business_owner', $user->roles ) ) {
                $user->add_role( 'wpcw_business_owner' );
            }
        } elseif ( $role === 'staff' ) {
            if ( ! in_array( 'wpcw_business_staff', $user->roles ) ) {
                $user->add_role( 'wpcw_business_staff' );
            }
        }

        // Log the assignment
        WPCW_Logger::log( 'info', 'User assigned to business', array(
            'user_id' => $user_id,
            'business_id' => $business_id,
            'role' => $role,
            'assigned_by' => get_current_user_id(),
        ) );

        return true;
    }

    /**
     * Remove user from business
     *
     * @param int $user_id User ID
     * @param int $business_id Business ID
     * @return bool|WP_Error Success or error
     */
    public static function remove_user_from_business( $user_id, $business_id ) {
        $user = get_user_by( 'id', $user_id );

        if ( ! $user ) {
            return new WP_Error( 'invalid_user', __( 'Usuario no válido.', 'wp-cupon-whatsapp' ) );
        }

        // Remove business access
        $business_access = get_user_meta( $user_id, '_wpcw_business_access', true );
        if ( is_array( $business_access ) ) {
            $business_access = array_diff( $business_access, array( $business_id ) );
            update_user_meta( $user_id, '_wpcw_business_access', $business_access );
        }

        // Remove business-specific meta
        delete_user_meta( $user_id, '_wpcw_business_id' );
        delete_user_meta( $user_id, '_wpcw_business_role' );

        // Remove roles if no other business access
        if ( empty( $business_access ) ) {
            $user->remove_role( 'wpcw_business_owner' );
            $user->remove_role( 'wpcw_business_staff' );
        }

        // Log the removal
        WPCW_Logger::log( 'info', 'User removed from business', array(
            'user_id' => $user_id,
            'business_id' => $business_id,
            'removed_by' => get_current_user_id(),
        ) );

        return true;
    }
}