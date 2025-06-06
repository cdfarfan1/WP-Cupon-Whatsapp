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
        'name'                  => __( 'Comercios', 'wp-cupon-whatsapp' ),
        'singular_name'         => __( 'Comercio', 'wp-cupon-whatsapp' ),
        'menu_name'             => __( 'Comercios', 'wp-cupon-whatsapp' ),
        'name_admin_bar'        => __( 'Comercio', 'wp-cupon-whatsapp' ),
        'add_new_item'          => __( 'Añadir Nuevo Comercio', 'wp-cupon-whatsapp' ),
        'add_new'               => __( 'Añadir Nuevo', 'wp-cupon-whatsapp' ),
        'new_item'              => __( 'Nuevo Comercio', 'wp-cupon-whatsapp' ),
        'edit_item'             => __( 'Editar Comercio', 'wp-cupon-whatsapp' ),
        'view_item'             => __( 'Ver Comercio', 'wp-cupon-whatsapp' ),
        'all_items'             => __( 'Todos los Comercios', 'wp-cupon-whatsapp' ),
        'search_items'          => __( 'Buscar Comercios', 'wp-cupon-whatsapp' ),
        'not_found'             => __( 'No se encontraron Comercios.', 'wp-cupon-whatsapp' ),
        'not_found_in_trash'    => __( 'No se encontraron Comercios en la papelera.', 'wp-cupon-whatsapp' ),
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
        'name'                  => __( 'Instituciones', 'wp-cupon-whatsapp' ),
        'singular_name'         => __( 'Institución', 'wp-cupon-whatsapp' ),
        'menu_name'             => __( 'Instituciones', 'wp-cupon-whatsapp' ),
        'name_admin_bar'        => __( 'Institución', 'wp-cupon-whatsapp' ),
        'add_new_item'          => __( 'Añadir Nueva Institución', 'wp-cupon-whatsapp' ),
        'add_new'               => __( 'Añadir Nueva', 'wp-cupon-whatsapp' ),
        'new_item'              => __( 'Nueva Institución', 'wp-cupon-whatsapp' ),
        'edit_item'             => __( 'Editar Institución', 'wp-cupon-whatsapp' ),
        'view_item'             => __( 'Ver Institución', 'wp-cupon-whatsapp' ),
        'all_items'             => __( 'Todas las Instituciones', 'wp-cupon-whatsapp' ),
        'search_items'          => __( 'Buscar Instituciones', 'wp-cupon-whatsapp' ),
        'not_found'             => __( 'No se encontraron Instituciones.', 'wp-cupon-whatsapp' ),
        'not_found_in_trash'    => __( 'No se encontraron Instituciones en la papelera.', 'wp-cupon-whatsapp' ),
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
        'name'                  => __( 'Solicitudes de Adhesión', 'wp-cupon-whatsapp' ),
        'singular_name'         => __( 'Solicitud de Adhesión', 'wp-cupon-whatsapp' ),
        'menu_name'             => __( 'Solicitudes', 'wp-cupon-whatsapp' ),
        'name_admin_bar'        => __( 'Solicitud', 'wp-cupon-whatsapp' ),
        'add_new_item'          => __( 'Añadir Nueva Solicitud', 'wp-cupon-whatsapp' ),
        'add_new'               => __( 'Añadir Nueva', 'wp-cupon-whatsapp' ),
        'new_item'              => __( 'Nueva Solicitud', 'wp-cupon-whatsapp' ),
        'edit_item'             => __( 'Editar Solicitud', 'wp-cupon-whatsapp' ),
        'view_item'             => __( 'Ver Solicitud', 'wp-cupon-whatsapp' ),
        'all_items'             => __( 'Todas las Solicitudes', 'wp-cupon-whatsapp' ),
        'search_items'          => __( 'Buscar Solicitudes', 'wp-cupon-whatsapp' ),
        'not_found'             => __( 'No se encontraron Solicitudes.', 'wp-cupon-whatsapp' ),
        'not_found_in_trash'    => __( 'No se encontraron Solicitudes en la papelera.', 'wp-cupon-whatsapp' ),
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
