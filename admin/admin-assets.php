<?php
/**
 * Enqueue admin scripts and styles
 */
function wpcw_enqueue_admin_assets( $hook ) {
    global $post_type;

    // Cargar estilos de roles en la página de roles
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'wpcw_roles' ) {
        wp_enqueue_style(
            'wpcw-roles',
            WPCW_PLUGIN_URL . 'admin/css/roles.css',
            array(),
            WPCW_VERSION
        );
        return;
    }

    // Solo cargar en la pantalla de edición de cupones
    if ( $hook !== 'post.php' && $hook !== 'post-new.php' || $post_type !== 'shop_coupon' ) {
        return;
    }

    // Registrar y encolar estilos de metaboxes
    wp_enqueue_style(
        'wpcw-meta-boxes',
        WPCW_PLUGIN_URL . 'admin/css/meta-boxes.css',
        array(),
        WPCW_VERSION
    );

    // Registrar y encolar scripts de metaboxes
    wp_enqueue_script(
        'wpcw-meta-boxes',
        WPCW_PLUGIN_URL . 'admin/js/meta-boxes.js',
        array( 'jquery' ),
        WPCW_VERSION,
        true
    );
}
add_action( 'admin_enqueue_scripts', 'wpcw_enqueue_admin_assets' );
