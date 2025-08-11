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
        'show_in_menu'       => false, // No mostrar en menú automáticamente
        'show_ui'            => true,  // Pero sí mostrar interfaz
        'menu_position'      => 20,
        'has_archive'        => true,
        'rewrite'            => array( 'slug' => 'wpcw-business' ),
        'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
        'capability_type'    => 'post',
    );
    register_post_type( 'wpcw_business', $business_args );

    // Register wpcw_institution CPT
    $institution_labels = array(
        'name'                  => 'Instituciones',
        'singular_name'         => 'Institución',
        'menu_name'             => 'Instituciones',
        'name_admin_bar'        => 'Institución',
        'add_new_item'          => 'Añadir Nueva Institución',
        'add_new'               => 'Añadir Nueva',
        'new_item'              => 'Nueva Institución',
        'edit_item'             => 'Editar Institución',
        'view_item'             => 'Ver Institución',
        'all_items'             => 'Todas las Instituciones',
        'search_items'          => 'Buscar Instituciones',
        'not_found'             => 'No se encontraron Instituciones.',
        'not_found_in_trash'    => 'No se encontraron Instituciones en la papelera.',
    );
    $institution_args = array(
        'labels'             => $institution_labels,
        'public'             => true,
        'show_in_menu'       => false, // No mostrar en menú automáticamente
        'show_ui'            => true,  // Pero sí mostrar interfaz
        'menu_position'      => 21,
        'has_archive'        => true,
        'rewrite'            => array( 'slug' => 'wpcw-institution' ),
        'supports'           => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
        'capability_type'    => 'post',
    );
    register_post_type( 'wpcw_institution', $institution_args );

    // Register wpcw_application CPT
    $application_labels = array(
        'name'                  => 'Solicitudes de Adhesión',
        'singular_name'         => 'Solicitud de Adhesión',
        'menu_name'             => 'Solicitudes',
        'name_admin_bar'        => 'Solicitud',
        'add_new_item'          => 'Añadir Nueva Solicitud',
        'add_new'               => 'Añadir Nueva',
        'new_item'              => 'Nueva Solicitud',
        'edit_item'             => 'Editar Solicitud',
        'view_item'             => 'Ver Solicitud',
        'all_items'             => 'Todas las Solicitudes',
        'search_items'          => 'Buscar Solicitudes',
        'not_found'             => 'No se encontraron Solicitudes.',
        'not_found_in_trash'    => 'No se encontraron Solicitudes en la papelera.',
    );
    $application_args = array(
        'labels'             => $application_labels,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => false, // No mostrar en menú automáticamente
        'menu_position'      => 19,
        'rewrite'            => array( 'slug' => 'wpcw-applications' ),
        'supports'           => array( 'title', 'editor', 'custom-fields' ),
        'capability_type'    => 'post',
    );
    register_post_type( 'wpcw_application', $application_args );
}
add_action( 'init', 'wpcw_register_post_types' );
