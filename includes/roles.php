<?php
/**
 * User Roles for WP Cupon WhatsApp
 *
 * @package WP_Cupon_WhatsApp
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Add custom user roles.
 */
function wpcw_add_roles() {
    // Business Owner Role
    remove_role( 'wpcw_business_owner' );
    add_role(
        'wpcw_business_owner',
        __( 'Dueño de Comercio', 'wp-cupon-whatsapp' ),
        array(
            'read'                          => true,
            'upload_files'                  => true,
            'edit_posts'                    => true,
            'edit_published_posts'          => true,
            'publish_posts'                 => true,
            'delete_posts'                  => true,
            'delete_posts'                  => true, // Allows deleting their own posts
            'delete_published_posts'        => true, // Allows deleting their own published posts
            // shop_coupon capabilities
            'edit_shop_coupons'             => true,
            'edit_published_shop_coupons'   => true,
            'publish_shop_coupons'          => true,
            'delete_shop_coupons'           => true,
            'delete_published_shop_coupons' => true,
            'assign_shop_coupon_terms'      => true, // For wpcw_coupon_category
            // Specific CPT capabilities (WordPress maps these from meta capabilities like edit_post)
            // It's good to be explicit if we need fine-grained control later.
            // For wpcw_business:
            'edit_wpcw_business'            => true,
            'read_wpcw_business'            => true,
            'delete_wpcw_business'          => true,
            'edit_wpcw_businesses'          => true, // edit_posts equivalent for CPT
            'edit_others_wpcw_businesses'   => false,
            'publish_wpcw_businesses'       => true,
            'read_private_wpcw_businesses'  => false,
            'delete_wpcw_businesses'        => true, // delete_posts equivalent for CPT
            // Custom capabilities
            'manage_wpcw_redemptions'       => true,
            'view_wpcw_reports'             => true,
            'manage_wpcw_settings'          => false,
        )
    );

    // Business Staff Role
    remove_role( 'wpcw_business_staff' );
    add_role(
        'wpcw_business_staff',
        __( 'Personal de Comercio', 'wp-cupon-whatsapp' ),
        array(
            'read'                    => true,
            // Specific CPT capabilities
            'read_wpcw_business'      => true,
            'edit_wpcw_business'      => false,
            'delete_wpcw_business'    => false,
            // Coupon capabilities
            'read_shop_coupons'       => true,
            'edit_shop_coupons'       => false,
            'publish_shop_coupons'    => false,
            // Custom capabilities
            'manage_wpcw_redemptions' => true,
            'view_wpcw_reports'       => true,
            'manage_wpcw_settings'    => false,
        )
    );

    // Administrator capabilities
    $admin = get_role( 'administrator' );
    if ( $admin ) {
        $capabilities = array(
            'manage_wpcw_settings'             => true,
            'manage_wpcw_redemptions'          => true,
            'view_wpcw_reports'                => true,
            'edit_wpcw_businesses'             => true,
            'edit_others_wpcw_businesses'      => true,
            'publish_wpcw_businesses'          => true,
            'read_private_wpcw_businesses'     => true,
            'delete_wpcw_businesses'           => true,
            'delete_private_wpcw_businesses'   => true,
            'delete_published_wpcw_businesses' => true,
            'delete_others_wpcw_businesses'    => true,
            'edit_published_wpcw_businesses'   => true,
            'create_wpcw_businesses'           => true,
            'wpcw_view_own_business_stats'     => true,
        );

        foreach ( $capabilities as $cap => $grant ) {
            $admin->add_cap( $cap, $grant );
        }
    }

    // Institution Manager Role
    // Remove the role first to ensure capabilities are updated if they change.
    remove_role( 'wpcw_institution_manager' );
    add_role(
        'wpcw_institution_manager',
        __( 'Institution Manager', 'wp-cupon-whatsapp' ), // Consider changing display name
        array(
            'read'                               => true,
            'upload_files'                       => true,
            // wpcw_institution CPT capabilities
            'edit_posts'                         => true,
            'edit_published_posts'               => true,
            'delete_posts'                       => true,
            'delete_published_posts'             => true,
            // shop_coupon capabilities (full control)
            'edit_shop_coupons'                  => true,
            'read_shop_coupons'                  => true,
            'delete_shop_coupons'                => true,
            'edit_others_shop_coupons'           => true,
            'publish_shop_coupons'               => true,
            'read_private_shop_coupons'          => true,
            'delete_private_shop_coupons'        => true,
            'delete_published_shop_coupons'      => true,
            'delete_others_shop_coupons'         => true,
            'edit_published_shop_coupons'        => true,
            'assign_shop_coupon_terms'           => true, // For wpcw_coupon_category
            // Specific CPT capabilities for wpcw_institution:
            'edit_wpcw_institution'              => true,
            'read_wpcw_institution'              => true,
            'delete_wpcw_institution'            => true,
            'edit_wpcw_institutions'             => true,
            'edit_others_wpcw_institutions'      => false,
            'publish_wpcw_institutions'          => true,
            'read_private_wpcw_institutions'     => false,
            'delete_wpcw_institutions'           => true,
            'delete_private_wpcw_institutions'   => false,
            'delete_published_wpcw_institutions' => true,
            'delete_others_wpcw_institutions'    => false,
            'edit_published_wpcw_institutions'   => true,
            'create_wpcw_institutions'           => true,
            // Nueva capacidad
            'wpcw_view_own_institution_stats'    => true,
        )
    );
}

/**
 * Remove custom user roles.
 */
function wpcw_remove_roles() {
    global $wp_roles;

    if ( ! isset( $wp_roles ) ) {
        $wp_roles = new WP_Roles();
    }

    if ( $wp_roles->is_role( 'wpcw_business_owner' ) ) {
        remove_role( 'wpcw_business_owner' );
    }

    if ( $wp_roles->is_role( 'wpcw_institution_manager' ) ) {
        remove_role( 'wpcw_institution_manager' );
    }
}
