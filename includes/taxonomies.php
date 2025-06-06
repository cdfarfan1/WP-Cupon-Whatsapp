<?php
/**
 * Register Custom Taxonomies
 *
 * @package WP_Cupon_WhatsApp
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Register custom taxonomies.
 */
function wpcw_register_taxonomies() {

    // Register wpcw_coupon_category taxonomy for shop_coupon
    $coupon_category_labels = array(
        'name'              => _x( 'Coupon Categories', 'taxonomy general name', 'wp-cupon-whatsapp' ),
        'singular_name'     => _x( 'Coupon Category', 'taxonomy singular name', 'wp-cupon-whatsapp' ),
        'search_items'      => __( 'Search Coupon Categories', 'wp-cupon-whatsapp' ),
        'all_items'         => __( 'All Coupon Categories', 'wp-cupon-whatsapp' ),
        'parent_item'       => __( 'Parent Coupon Category', 'wp-cupon-whatsapp' ),
        'parent_item_colon' => __( 'Parent Coupon Category:', 'wp-cupon-whatsapp' ),
        'edit_item'         => __( 'Edit Coupon Category', 'wp-cupon-whatsapp' ),
        'update_item'       => __( 'Update Coupon Category', 'wp-cupon-whatsapp' ),
        'add_new_item'      => __( 'Add New Coupon Category', 'wp-cupon-whatsapp' ),
        'new_item_name'     => __( 'New Coupon Category Name', 'wp-cupon-whatsapp' ),
        'menu_name'         => __( 'Coupon Category', 'wp-cupon-whatsapp' ),
    );

    $coupon_category_args = array(
        'hierarchical'      => true,
        'labels'            => $coupon_category_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => 'wpcw-coupon-category',
        'rewrite'           => array( 'slug' => 'wpcw-coupon-category' ),
    );

    register_taxonomy( 'wpcw_coupon_category', array( 'shop_coupon' ), $coupon_category_args );
}
add_action( 'init', 'wpcw_register_taxonomies' );
