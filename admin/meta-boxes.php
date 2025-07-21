<?php
/**
 * WP Canje Cupon Whatsapp Admin Meta Boxes
 *
 * Handles the creation of custom meta boxes for CPTs, including shop_coupon.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Adds the custom metabox to the shop_coupon edit screen.
 */
function wpcw_add_coupon_details_metabox() {
    add_meta_box(
        'wpcw_coupon_additional_details', // Metabox ID
        __( 'Detalles Adicionales WPCW', 'wp-cupon-whatsapp' ), // Title
        'wpcw_coupon_details_metabox_html', // Callback function to render HTML
        'shop_coupon', // Post type
        'normal', // Context (normal, side, advanced)
        'high' // Priority (high, core, default, low)
    );
}
// Hook to the specific post type 'shop_coupon'
// This hook is more specific and generally preferred for WooCommerce coupons.
add_action( 'add_meta_boxes_shop_coupon', 'wpcw_add_coupon_details_metabox' );

// Fallback for older WooCommerce or broader compatibility if needed:
// add_action( 'add_meta_boxes', 'wpcw_maybe_add_coupon_details_metabox', 10, 2 );
// function wpcw_maybe_add_coupon_details_metabox( $post_type, $post ) {
//    if ( $post_type === 'shop_coupon' ) {
//        wpcw_add_coupon_details_metabox();
//    }
// }


// Placeholder for the metabox HTML rendering function (to be implemented in the next step)
// if ( ! function_exists( 'wpcw_coupon_details_metabox_html' ) ) { // Remove this if defining the function
function wpcw_coupon_details_metabox_html( $post ) {
    // Añadir Nonce para seguridad
    wp_nonce_field( 'wpcw_save_coupon_meta', 'wpcw_coupon_meta_nonce' );

    // Obtener valores guardados
    $is_loyalty_coupon = get_post_meta( $post->ID, '_wpcw_is_loyalty_coupon', true );
    $is_public_coupon = get_post_meta( $post->ID, '_wpcw_is_public_coupon', true );
    $associated_business_id = get_post_meta( $post->ID, '_wpcw_associated_business_id', true );
    $coupon_category_id = get_post_meta( $post->ID, '_wpcw_coupon_category_id', true );
    $coupon_image_id = get_post_meta( $post->ID, '_wpcw_coupon_image_id', true );

    // Estructura de tabla para alinear campos y etiquetas
    echo '<table class="form-table wpcw-metabox-table">';

    // Campo _wpcw_is_loyalty_coupon (Checkbox)
    echo '<tr>';
    echo '<th><label for="wpcw_is_loyalty_coupon">' . esc_html__( 'Cupón de Lealtad', 'wp-cupon-whatsapp' ) . '</label></th>';
    echo '<td><input type="checkbox" id="wpcw_is_loyalty_coupon" name="wpcw_is_loyalty_coupon" value="yes" ' . checked( $is_loyalty_coupon, 'yes', false ) . ' />';
    echo '<p class="description">' . esc_html__( 'Marcar si este cupón es parte del programa de lealtad.', 'wp-cupon-whatsapp' ) . '</p></td>';
    echo '</tr>';

    // Campo _wpcw_is_public_coupon (Checkbox)
    echo '<tr>';
    echo '<th><label for="wpcw_is_public_coupon">' . esc_html__( 'Cupón Público', 'wp-cupon-whatsapp' ) . '</label></th>';
    echo '<td><input type="checkbox" id="wpcw_is_public_coupon" name="wpcw_is_public_coupon" value="yes" ' . checked( $is_public_coupon, 'yes', false ) . ' />';
    echo '<p class="description">' . esc_html__( 'Marcar si este cupón es visible para todos los usuarios (en la página de cupones públicos).', 'wp-cupon-whatsapp' ) . '</p></td>';
    echo '</tr>';

    // Campo _wpcw_coupon_category_id (Selector de Taxonomía)
    $categories = get_terms( array( 'taxonomy' => 'wpcw_coupon_category', 'hide_empty' => false ) );
    echo '<tr>';
    echo '<th><label for="wpcw_coupon_category_id">' . esc_html__( 'Categoría del Cupón', 'wp-cupon-whatsapp' ) . '</label></th>';
    echo '<td>';
    if ( !empty($categories) && !is_wp_error($categories) ) {
        echo '<select id="wpcw_coupon_category_id" name="wpcw_coupon_category_id">';
        echo '<option value="">' . esc_html__( 'Seleccionar categoría...', 'wp-cupon-whatsapp' ) . '</option>';
        foreach ( $categories as $category ) {
            echo '<option value="' . esc_attr( $category->term_id ) . '" ' . selected( $coupon_category_id, $category->term_id, false ) . '>' . esc_html( $category->name ) . '</option>';
        }
        echo '</select>';
    } else {
        echo '<p>' . esc_html__( 'No hay categorías de cupón definidas. Por favor, créalas primero.', 'wp-cupon-whatsapp' ) . '</p>';
    }
    echo '</td></tr>';

    // Campo _wpcw_associated_business_id (Selector de CPT wpcw_business)
    // Solo mostrar si el usuario actual NO es wpcw_business_owner,
    // o si es admin/manager que puede asignar a cualquier negocio.
    // Un wpcw_business_owner no debería poder cambiar el comercio asociado de su propio cupón una vez creado por él.
    // Esta lógica de visibilidad puede ser más compleja y manejarse aquí o con JS.
    // Por ahora, lo mostramos para todos los que puedan editar el cupón.
    $businesses = get_posts( array( 'post_type' => 'wpcw_business', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC', 'suppress_filters' => false ) );
    echo '<tr>';
    echo '<th><label for="wpcw_associated_business_id">' . esc_html__( 'Comercio Asociado (Opcional)', 'wp-cupon-whatsapp' ) . '</label></th>';
    echo '<td>';
    if ( !empty($businesses) ) {
        echo '<select id="wpcw_associated_business_id" name="wpcw_associated_business_id">';
        echo '<option value="">' . esc_html__( 'Ninguno (cupón genérico/institucional)', 'wp-cupon-whatsapp' ) . '</option>';
        foreach ( $businesses as $business ) {
            echo '<option value="' . esc_attr( $business->ID ) . '" ' . selected( $associated_business_id, $business->ID, false ) . '>' . esc_html( $business->post_title ) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__( 'Asociar este cupón a un comercio específico. Requerido si NO es un cupón de institución.', 'wp-cupon-whatsapp' ) . '</p>';
    } else {
        echo '<p>' . esc_html__( 'No hay comercios registrados. Los cupones de comercio no pueden ser creados/asignados.', 'wp-cupon-whatsapp' ) . '</p>';
    }
    echo '</td></tr>';

    // Campo _wpcw_coupon_image_id (Media Uploader)
    echo '<tr>';
    echo '<th><label for="wpcw_coupon_image_id">' . esc_html__( 'Imagen del Cupón', 'wp-cupon-whatsapp' ) . '</label></th>';
    echo '<td>';
    echo '<input type="hidden" id="wpcw_coupon_image_id" name="wpcw_coupon_image_id" value="' . esc_attr( $coupon_image_id ) . '" />';
    echo '<button type="button" class="button" id="wpcw_upload_image_button">' . esc_html__( 'Seleccionar/Subir Imagen', 'wp-cupon-whatsapp' ) . '</button> '; // Added space for clarity
    echo '<button type="button" class="button" id="wpcw_remove_image_button" style="' . ( empty( $coupon_image_id ) ? 'display:none;' : '' ) . '">' . esc_html__( 'Quitar Imagen', 'wp-cupon-whatsapp' ) . '</button>';
    echo '<div id="wpcw_coupon_image_preview" style="margin-top:10px;">';
    if ( $coupon_image_id ) {
        echo wp_get_attachment_image( $coupon_image_id, 'thumbnail' );
    }
    echo '</div>';
    echo '<p class="description">' . esc_html__( 'Selecciona una imagen para el cupón.', 'wp-cupon-whatsapp' ) . '</p>';
    echo '</td></tr>';


    // TODO: Campos para wpcw_institution_manager (aplicabilidad a comercios/categorías)
    // _wpcw_instit_coupon_applicable_businesses (array IDs wpcw_business)
    // _wpcw_instit_coupon_applicable_categories (array Term IDs wpcw_coupon_category)

    echo '</table>';

    // Script para el Media Uploader (se encolará de forma más robusta en el siguiente paso)
    // Por ahora, un script inline básico para el concepto, o mejor, solo el HTML y el JS en un archivo separado.
    // Para este paso, solo el HTML es suficiente. El JS se abordará en el paso de "Encolar script".
}
// } // Fin del if function_exists

/**
 * Saves the custom meta data for shop_coupon.
 *
 * @param int $post_id The ID of the coupon being saved.
 * @param WC_Coupon $coupon The coupon object (available in some WC hooks, might not be directly passed by woocommerce_coupon_options_save, so rely on $post_id).
 */
function wpcw_save_coupon_metabox_fields( $post_id, $coupon ) { // $coupon might not always be passed or be useful, $post_id is key.
    // 1. Verificar Nonce
    if ( ! isset( $_POST['wpcw_coupon_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['wpcw_coupon_meta_nonce'] ), 'wpcw_save_coupon_meta' ) ) {
        return;
    }

    // 2. Verificar Permisos (WordPress ya debería hacer esto, pero una comprobación extra no hace daño)
    // El hook woocommerce_coupon_options_save se dispara dentro del contexto de guardado de WC, que ya tiene chequeos.
    // Si usáramos save_post_shop_coupon, necesitaríamos current_user_can('edit_post', $post_id).
    // Por ahora, confiamos en el contexto del hook de WC.
    if ( ! current_user_can( 'edit_post', $post_id ) ) { // Aunque WC hace chequeos, es buena práctica.
        return;
    }


    // 3. Sanitizar y Guardar Datos
    // _wpcw_is_loyalty_coupon (checkbox)
    $is_loyalty = isset( $_POST['wpcw_is_loyalty_coupon'] ) ? 'yes' : 'no';
    update_post_meta( $post_id, '_wpcw_is_loyalty_coupon', $is_loyalty );

    // _wpcw_is_public_coupon (checkbox)
    $is_public = isset( $_POST['wpcw_is_public_coupon'] ) ? 'yes' : 'no';
    update_post_meta( $post_id, '_wpcw_is_public_coupon', $is_public );

    // _wpcw_coupon_category_id (selector)
    if ( isset( $_POST['wpcw_coupon_category_id'] ) ) {
        $category_id = sanitize_text_field( $_POST['wpcw_coupon_category_id'] );
        if ( empty( $category_id ) ) {
            delete_post_meta( $post_id, '_wpcw_coupon_category_id' );
        } else {
            update_post_meta( $post_id, '_wpcw_coupon_category_id', absint( $category_id ) );
        }
    }

    // _wpcw_associated_business_id (selector)
    if ( isset( $_POST['wpcw_associated_business_id'] ) ) {
        $business_id = sanitize_text_field( $_POST['wpcw_associated_business_id'] );
        if ( empty( $business_id ) ) {
            delete_post_meta( $post_id, '_wpcw_associated_business_id' );
        } else {
            update_post_meta( $post_id, '_wpcw_associated_business_id', absint( $business_id ) );
        }
    }

    // _wpcw_coupon_image_id (input hidden)
    if ( isset( $_POST['wpcw_coupon_image_id'] ) ) {
        $image_id = sanitize_text_field( $_POST['wpcw_coupon_image_id'] );
        if ( empty( $image_id ) ) {
            delete_post_meta( $post_id, '_wpcw_coupon_image_id' );
        } else {
            update_post_meta( $post_id, '_wpcw_coupon_image_id', absint( $image_id ) );
        }
    }

    // TODO: Guardar campos para wpcw_institution_manager
    // _wpcw_instit_coupon_applicable_businesses (array IDs wpcw_business)
    // _wpcw_instit_coupon_applicable_categories (array Term IDs wpcw_coupon_category)
}
// El hook woocommerce_coupon_options_save pasa 2 argumentos: post_id y coupon object.
add_action( 'woocommerce_coupon_options_save', 'wpcw_save_coupon_metabox_fields', 10, 2 );

/**
 * Enqueues admin scripts for WPCW metaboxes.
 *
 * @param string $hook The current admin page.
 */
function wpcw_admin_enqueue_metabox_scripts( $hook ) {
    global $post_type; // Use global $post_type to check the CPT

    // Solo encolar en la pantalla de edición de 'shop_coupon'
    if ( ('post.php' == $hook || 'post-new.php' == $hook) && isset($post_type) && 'shop_coupon' === $post_type ) {

        // Encolar el script de WordPress Media
        wp_enqueue_media();

        // Registrar y encolar nuestro script personalizado
        // Ensure WPCW_PLUGIN_URL and WPCW_VERSION are defined and accessible
        // These are typically defined in the main plugin file.
        $plugin_url = defined('WPCW_PLUGIN_URL') ? WPCW_PLUGIN_URL : plugin_dir_url(dirname(__FILE__)); // Fallback for URL
        $plugin_version = defined('WPCW_VERSION') ? WPCW_VERSION : '1.0.0'; // Fallback for version

        // Registrar y encolar estilos
        wp_register_style(
            'wpcw-meta-boxes',
            $plugin_url . 'admin/css/meta-boxes.css',
            array(),
            $plugin_version
        );
        wp_enqueue_style('wpcw-meta-boxes');

        // Registrar y encolar scripts
        wp_register_script(
            'wpcw-admin-metaboxes-js',
            $plugin_url . 'admin/js/wpcw-admin-metaboxes.js',
            array('jquery', 'wp-media'),
            $plugin_version,
            true
        );
        wp_enqueue_script('wpcw-admin-metaboxes-js');

        wp_register_script(
            'wpcw-meta-boxes',
            $plugin_url . 'admin/js/meta-boxes.js',
            array('jquery'),
            $plugin_version,
            true
        );
        wp_enqueue_script('wpcw-meta-boxes');

        // Localizar el script con cadenas traducibles
        wp_localize_script(
            'wpcw-admin-metaboxes-js',
            'wpcw_metabox_opts',
            array(
                'frameTitle'       => __( 'Seleccionar o Subir Imagen para Cupón', 'wp-cupon-whatsapp' ),
                'buttonText'       => __( 'Usar esta imagen', 'wp-cupon-whatsapp' ),
                'imageAlt'         => __( 'Previsualización de Imagen del Cupón', 'wp-cupon-whatsapp' ),
                'removeButtonText' => __( 'Quitar Imagen', 'wp-cupon-whatsapp' ) // Though button text is now static in HTML
            )
        );
    }
}
add_action( 'admin_enqueue_scripts', 'wpcw_admin_enqueue_metabox_scripts' );

?>
