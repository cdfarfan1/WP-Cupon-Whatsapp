<?php
/**
 * Register Custom Post Types
 *
 * @package WP_Cupon_WhatsApp
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Register custom post types.
 */
function wpcw_register_post_types() {

    // Register wpcw_business CPT
    $business_labels = array(
        'name'                  => _x( 'Businesses', 'Post type general name', 'wp-cupon-whatsapp' ),
        'singular_name'         => _x( 'Business', 'Post type singular name', 'wp-cupon-whatsapp' ),
    );
    $business_args = array(
        'labels'             => $business_labels,
        'public'             => true,
        'has_archive'        => true,
        'rewrite'            => array( 'slug' => 'wpcw-business' ),
        'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
        'capability_type'    => 'post',
    );
    register_post_type( 'wpcw_business', $business_args );

    // Register wpcw_institution CPT
    $institution_labels = array(
        'name'                  => _x( 'Institutions', 'Post type general name', 'wp-cupon-whatsapp' ),
        'singular_name'         => _x( 'Institution', 'Post type singular name', 'wp-cupon-whatsapp' ),
    );
    $institution_args = array(
        'labels'             => $institution_labels,
        'public'             => true,
        'has_archive'        => true,
        'rewrite'            => array( 'slug' => 'wpcw-institution' ),
        'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
        'capability_type'    => 'post',
    );
    register_post_type( 'wpcw_institution', $institution_args );

    // Register wpcw_application CPT
    $application_labels = array(
        'name'                  => _x( 'Applications', 'Post type general name', 'wp-cupon-whatsapp' ),
        'singular_name'         => _x( 'Application', 'Post type singular name', 'wp-cupon-whatsapp' ),
    );
    $application_args = array(
        'labels'             => $application_labels,
        'public'             => false,
        'show_ui'            => true,
        'rewrite'            => array( 'slug' => 'wpcw-applications' ),
        'supports'           => array( 'title', 'editor', 'custom-fields' ),
        'capability_type'    => 'post',
    );
    register_post_type( 'wpcw_application', $application_args );
}
add_action( 'init', 'wpcw_register_post_types' );
