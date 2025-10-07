<?php
/**
 * WP Cupón WhatsApp - REST API Class
 *
 * Handles REST API endpoints for the plugin
 *
 * @package WP_Cupon_WhatsApp
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * WPCW_REST_API class
 */
class WPCW_REST_API {

    /**
     * Initialize REST API
     */
    public static function init() {
        add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
    }

    /**
     * Register REST API routes
     */
    public static function register_routes() {
        // Coupons endpoints
        register_rest_route( 'wpcw/v1', '/coupons', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( __CLASS__, 'get_coupons' ),
                'permission_callback' => array( __CLASS__, 'check_coupons_permission' ),
                'args' => array(
                    'user_id' => array(
                        'required' => false,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'business_id' => array(
                        'required' => false,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'type' => array(
                        'required' => false,
                        'type' => 'string',
                        'enum' => array( 'loyalty', 'public', 'all' ),
                    ),
                    'page' => array(
                        'required' => false,
                        'type' => 'integer',
                        'default' => 1,
                        'sanitize_callback' => 'absint',
                    ),
                    'per_page' => array(
                        'required' => false,
                        'type' => 'integer',
                        'default' => 20,
                        'sanitize_callback' => 'absint',
                    ),
                ),
            ),
        ) );

        // Single coupon endpoint
        register_rest_route( 'wpcw/v1', '/coupons/(?P<id>\d+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( __CLASS__, 'get_coupon' ),
                'permission_callback' => array( __CLASS__, 'check_coupons_permission' ),
                'args' => array(
                    'id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                ),
            ),
        ) );

        // Redemptions endpoints
        register_rest_route( 'wpcw/v1', '/redemptions', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( __CLASS__, 'get_redemptions' ),
                'permission_callback' => array( __CLASS__, 'check_redemptions_permission' ),
                'args' => array(
                    'user_id' => array(
                        'required' => false,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'business_id' => array(
                        'required' => false,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'status' => array(
                        'required' => false,
                        'type' => 'string',
                        'enum' => array( 'pending', 'confirmed', 'rejected', 'used', 'expired' ),
                    ),
                    'page' => array(
                        'required' => false,
                        'type' => 'integer',
                        'default' => 1,
                        'sanitize_callback' => 'absint',
                    ),
                    'per_page' => array(
                        'required' => false,
                        'type' => 'integer',
                        'default' => 20,
                        'sanitize_callback' => 'absint',
                    ),
                ),
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array( __CLASS__, 'create_redemption' ),
                'permission_callback' => array( __CLASS__, 'check_create_redemption_permission' ),
                'args' => array(
                    'coupon_id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'user_id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                ),
            ),
        ) );

        // Single redemption endpoint
        register_rest_route( 'wpcw/v1', '/redemptions/(?P<id>\d+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( __CLASS__, 'get_redemption' ),
                'permission_callback' => array( __CLASS__, 'check_redemptions_permission' ),
                'args' => array(
                    'id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                ),
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array( __CLASS__, 'update_redemption' ),
                'permission_callback' => array( __CLASS__, 'check_update_redemption_permission' ),
                'args' => array(
                    'id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'action' => array(
                        'required' => true,
                        'type' => 'string',
                        'enum' => array( 'confirm', 'reject' ),
                    ),
                    'reason' => array(
                        'required' => false,
                        'type' => 'string',
                    ),
                ),
            ),
        ) );

        // Statistics endpoint
        register_rest_route( 'wpcw/v1', '/statistics', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( __CLASS__, 'get_statistics' ),
                'permission_callback' => array( __CLASS__, 'check_statistics_permission' ),
                'args' => array(
                    'business_id' => array(
                        'required' => false,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'period' => array(
                        'required' => false,
                        'type' => 'string',
                        'enum' => array( 'day', 'week', 'month', 'year' ),
                        'default' => 'month',
                    ),
                    'type' => array(
                        'required' => false,
                        'type' => 'string',
                        'enum' => array( 'coupons', 'redemptions', 'businesses', 'users', 'all' ),
                        'default' => 'all',
                    ),
                ),
            ),
        ) );

        // Applications endpoint
        register_rest_route( 'wpcw/v1', '/applications', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( __CLASS__, 'get_applications' ),
                'permission_callback' => array( __CLASS__, 'check_applications_permission' ),
                'args' => array(
                    'status' => array(
                        'required' => false,
                        'type' => 'string',
                        'enum' => array( 'pending', 'approved', 'rejected' ),
                    ),
                    'page' => array(
                        'required' => false,
                        'type' => 'integer',
                        'default' => 1,
                        'sanitize_callback' => 'absint',
                    ),
                    'per_page' => array(
                        'required' => false,
                        'type' => 'integer',
                        'default' => 20,
                        'sanitize_callback' => 'absint',
                    ),
                ),
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array( __CLASS__, 'create_application' ),
                'permission_callback' => '__return_true', // Public endpoint
                'args' => array(
                    'applicant_type' => array(
                        'required' => true,
                        'type' => 'string',
                        'enum' => array( 'comercio', 'institucion' ),
                    ),
                    'fantasy_name' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'legal_name' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'cuit' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'contact_person' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'email' => array(
                        'required' => true,
                        'type' => 'string',
                        'validate_callback' => 'is_email',
                        'sanitize_callback' => 'sanitize_email',
                    ),
                    'whatsapp' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'address_main' => array(
                        'required' => false,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ),
                    'description' => array(
                        'required' => false,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ),
                ),
            ),
        ) );

        // Single application endpoint
        register_rest_route( 'wpcw/v1', '/applications/(?P<id>\d+)', array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array( __CLASS__, 'update_application' ),
                'permission_callback' => array( __CLASS__, 'check_update_application_permission' ),
                'args' => array(
                    'id' => array(
                        'required' => true,
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                    ),
                    'action' => array(
                        'required' => true,
                        'type' => 'string',
                        'enum' => array( 'approve', 'reject' ),
                    ),
                    'reason' => array(
                        'required' => false,
                        'type' => 'string',
                    ),
                ),
            ),
        ) );
    }

    /**
     * Get coupons
     */
    public static function get_coupons( $request ) {
        $params = $request->get_params();

        $args = array(
            'post_type' => 'shop_coupon',
            'post_status' => 'publish',
            'posts_per_page' => $params['per_page'],
            'paged' => $params['page'],
            'meta_query' => array(
                array(
                    'key' => '_wpcw_enabled',
                    'value' => 'yes',
                    'compare' => '=',
                ),
            ),
        );

        // Add user-specific filters
        if ( isset( $params['user_id'] ) ) {
            $user_institution = get_user_meta( $params['user_id'], '_wpcw_user_institution_id', true );
            if ( $user_institution ) {
                $args['meta_query'][] = array(
                    'key' => '_wpcw_associated_business_id',
                    'value' => $user_institution,
                    'compare' => '=',
                );
            }
        }

        // Add business filter
        if ( isset( $params['business_id'] ) ) {
            $args['meta_query'][] = array(
                'key' => '_wpcw_associated_business_id',
                'value' => $params['business_id'],
                'compare' => '=',
            );
        }

        // Add type filter
        if ( isset( $params['type'] ) && $params['type'] !== 'all' ) {
            if ( $params['type'] === 'loyalty' ) {
                $args['meta_query'][] = array(
                    'key' => '_wpcw_is_loyalty_coupon',
                    'value' => 'yes',
                    'compare' => '=',
                );
            } elseif ( $params['type'] === 'public' ) {
                $args['meta_query'][] = array(
                    'key' => '_wpcw_is_public_coupon',
                    'value' => 'yes',
                    'compare' => '=',
                );
            }
        }

        $coupons_query = new WP_Query( $args );
        $coupons = array();

        foreach ( $coupons_query->posts as $coupon_post ) {
            $coupon = new WC_Coupon( $coupon_post->ID );
            $coupons[] = self::format_coupon_data( $coupon );
        }

        return new WP_REST_Response( array(
            'coupons' => $coupons,
            'total' => $coupons_query->found_posts,
            'pages' => $coupons_query->max_num_pages,
            'current_page' => $params['page'],
        ), 200 );
    }

    /**
     * Get single coupon
     */
    public static function get_coupon( $request ) {
        $coupon_id = $request->get_param( 'id' );
        $coupon = new WC_Coupon( $coupon_id );

        if ( ! $coupon->get_id() ) {
            return new WP_Error( 'coupon_not_found', 'Cupón no encontrado', array( 'status' => 404 ) );
        }

        return new WP_REST_Response( self::format_coupon_data( $coupon ), 200 );
    }

    /**
     * Get redemptions
     */
    public static function get_redemptions( $request ) {
        $params = $request->get_params();

        $filters = array(
            'per_page' => $params['per_page'],
            'paged' => $params['page'],
        );

        if ( isset( $params['user_id'] ) ) {
            $filters['user_id'] = $params['user_id'];
        }

        if ( isset( $params['business_id'] ) ) {
            $filters['business_id'] = $params['business_id'];
        }

        if ( isset( $params['status'] ) ) {
            $status_map = array(
                'pending' => 'pendiente_confirmacion',
                'confirmed' => 'confirmado_por_negocio',
                'rejected' => 'rechazado',
                'used' => 'utilizado_en_pedido_wc',
                'expired' => 'expirado',
            );
            $filters['status'] = $status_map[$params['status']];
        }

        $redemptions_data = WPCW_Redemption_Manager::get_pending_redemptions( $filters );

        return new WP_REST_Response( $redemptions_data, 200 );
    }

    /**
     * Create redemption
     */
    public static function create_redemption( $request ) {
        $params = $request->get_params();

        // Validate coupon
        $coupon = get_post( $params['coupon_id'] );
        if ( ! $coupon || $coupon->post_type !== 'shop_coupon' ) {
            return new WP_Error( 'invalid_coupon', 'Cupón inválido', array( 'status' => 400 ) );
        }

        // Check if coupon is WPCW enabled
        if ( get_post_meta( $params['coupon_id'], '_wpcw_enabled', true ) !== 'yes' ) {
            return new WP_Error( 'coupon_not_enabled', 'Este cupón no está habilitado para WPCW', array( 'status' => 400 ) );
        }

        // Create redemption
        $redemption_data = array(
            'user_id' => $params['user_id'],
            'coupon_id' => $params['coupon_id'],
            'business_id' => get_post_meta( $params['coupon_id'], '_wpcw_associated_business_id', true ),
        );

        $redemption_id = WPCW_Redemption_Handler::create_redemption_request( $redemption_data );

        if ( is_wp_error( $redemption_id ) ) {
            return $redemption_id;
        }

        return new WP_REST_Response( array(
            'redemption_id' => $redemption_id,
            'message' => 'Solicitud de canje creada exitosamente',
        ), 201 );
    }

    /**
     * Get single redemption
     */
    public static function get_redemption( $request ) {
        $redemption_id = $request->get_param( 'id' );

        $filters = array( 'redemption_id' => $redemption_id );
        $redemptions_data = WPCW_Redemption_Manager::get_pending_redemptions( $filters );

        if ( empty( $redemptions_data['redemptions'] ) ) {
            return new WP_Error( 'redemption_not_found', 'Canje no encontrado', array( 'status' => 404 ) );
        }

        return new WP_REST_Response( $redemptions_data['redemptions'][0], 200 );
    }

    /**
     * Update redemption
     */
    public static function update_redemption( $request ) {
        $redemption_id = $request->get_param( 'id' );
        $params = $request->get_params();

        if ( $params['action'] === 'confirm' ) {
            $result = WPCW_Redemption_Handler::confirm_redemption( $redemption_id );
        } elseif ( $params['action'] === 'reject' ) {
            $result = WPCW_Redemption_Manager::reject_redemption( $redemption_id, $params['reason'] );
        }

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return new WP_REST_Response( array(
            'message' => 'Canje actualizado exitosamente',
        ), 200 );
    }

    /**
     * Get statistics
     */
    public static function get_statistics( $request ) {
        $params = $request->get_params();

        $filters = array();
        if ( isset( $params['business_id'] ) ) {
            $filters['business_id'] = $params['business_id'];
        }

        $statistics = array();

        if ( $params['type'] === 'coupons' || $params['type'] === 'all' ) {
            $statistics['coupons'] = WPCW_Coupon_Manager::get_coupon_statistics( $filters );
        }

        if ( $params['type'] === 'redemptions' || $params['type'] === 'all' ) {
            $statistics['redemptions'] = WPCW_Redemption_Manager::get_redemption_statistics( $filters );
        }

        if ( $params['type'] === 'businesses' || $params['type'] === 'all' ) {
            $statistics['businesses'] = WPCW_Business_Manager::get_business_stats( $params['business_id'] ?? 0 );
        }

        return new WP_REST_Response( $statistics, 200 );
    }

    /**
     * Get applications
     */
    public static function get_applications( $request ) {
        $params = $request->get_params();

        $filters = array(
            'per_page' => $params['per_page'],
            'paged' => $params['page'],
        );

        if ( isset( $params['status'] ) ) {
            $status_map = array(
                'pending' => 'pendiente_revision',
                'approved' => 'aprobada',
                'rejected' => 'rechazada',
            );
            $filters['application_status'] = $status_map[$params['status']];
        }

        $applications_data = WPCW_Business_Manager::get_business_applications( $filters );

        return new WP_REST_Response( $applications_data, 200 );
    }

    /**
     * Create application
     */
    public static function create_application( $request ) {
        $params = $request->get_params();

        // Create application post
        $application_data = array(
            'post_title' => $params['fantasy_name'],
            'post_content' => $params['description'] ?? '',
            'post_type' => 'wpcw_application',
            'post_status' => 'publish',
        );

        $application_id = wp_insert_post( $application_data );

        if ( is_wp_error( $application_id ) ) {
            return new WP_Error( 'application_creation_failed', 'Error al crear la solicitud', array( 'status' => 500 ) );
        }

        // Save meta data
        $meta_fields = array(
            '_wpcw_applicant_type',
            '_wpcw_legal_name',
            '_wpcw_cuit',
            '_wpcw_contact_person',
            '_wpcw_email',
            '_wpcw_whatsapp',
            '_wpcw_address_main',
        );

        foreach ( $meta_fields as $field ) {
            $key = str_replace( '_wpcw_', '', $field );
            if ( isset( $params[$key] ) ) {
                update_post_meta( $application_id, $field, $params[$key] );
            }
        }

        update_post_meta( $application_id, '_wpcw_application_status', 'pendiente_revision' );

        if ( is_user_logged_in() ) {
            update_post_meta( $application_id, '_wpcw_created_user_id', get_current_user_id() );
        }

        // Log application creation
        WPCW_Logger::log( 'info', 'Application created via API', array(
            'application_id' => $application_id,
            'applicant_type' => $params['applicant_type'],
            'email' => $params['email'],
        ) );

        return new WP_REST_Response( array(
            'application_id' => $application_id,
            'message' => 'Solicitud creada exitosamente',
        ), 201 );
    }

    /**
     * Update application
     */
    public static function update_application( $request ) {
        $application_id = $request->get_param( 'id' );
        $params = $request->get_params();

        if ( $params['action'] === 'approve' ) {
            $result = WPCW_Business_Manager::approve_application( $application_id );
        } elseif ( $params['action'] === 'reject' ) {
            $result = WPCW_Business_Manager::reject_application( $application_id, $params['reason'] );
        }

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return new WP_REST_Response( array(
            'message' => 'Solicitud actualizada exitosamente',
        ), 200 );
    }

    /**
     * Permission callbacks
     */
    public static function check_coupons_permission( $request ) {
        return current_user_can( 'read' );
    }

    public static function check_redemptions_permission( $request ) {
        return current_user_can( 'manage_options' );
    }

    public static function check_create_redemption_permission( $request ) {
        return current_user_can( 'read' );
    }

    public static function check_update_redemption_permission( $request ) {
        return current_user_can( 'manage_options' );
    }

    public static function check_statistics_permission( $request ) {
        return current_user_can( 'manage_options' );
    }

    public static function check_applications_permission( $request ) {
        return current_user_can( 'manage_options' );
    }

    public static function check_update_application_permission( $request ) {
        return current_user_can( 'manage_options' );
    }

    /**
     * Format coupon data for API response
     */
    private static function format_coupon_data( $coupon ) {
        $coupon_post = get_post( $coupon->get_id() );

        return array(
            'id' => $coupon->get_id(),
            'code' => $coupon->get_code(),
            'description' => $coupon_post->post_content,
            'discount_type' => $coupon->get_discount_type(),
            'amount' => $coupon->get_amount(),
            'usage_limit' => $coupon->get_usage_limit(),
            'usage_limit_per_user' => $coupon->get_usage_limit_per_user(),
            'expiry_date' => $coupon->get_date_expires() ? $coupon->get_date_expires()->date( 'Y-m-d' ) : null,
            'minimum_amount' => get_post_meta( $coupon->get_id(), 'minimum_amount', true ),
            'maximum_amount' => get_post_meta( $coupon->get_id(), 'maximum_amount', true ),
            'individual_use' => $coupon->get_individual_use(),
            'wpcw_enabled' => get_post_meta( $coupon->get_id(), '_wpcw_enabled', true ) === 'yes',
            'business_id' => get_post_meta( $coupon->get_id(), '_wpcw_associated_business_id', true ),
            'is_loyalty' => get_post_meta( $coupon->get_id(), '_wpcw_is_loyalty_coupon', true ) === 'yes',
            'is_public' => get_post_meta( $coupon->get_id(), '_wpcw_is_public_coupon', true ) === 'yes',
            'whatsapp_text' => get_post_meta( $coupon->get_id(), '_wpcw_whatsapp_text', true ),
        );
    }
}

// Initialize REST API
WPCW_REST_API::init();