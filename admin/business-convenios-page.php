<?php
/**
 * WPCW - Business Owner Convenios Management Page
 *
 * This file renders the page for a Business Owner to manage their convenios.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Renders the content of the Business Owner's convenios page.
 */
function wpcw_render_business_convenios_page() {
    ?>
    <div class="wrap wpcw-dashboard-wrap">
        <h1><span class="dashicons dashicons-businesswoman"></span> <?php _e( 'Gestión de Convenios', 'wp-cupon-whatsapp' ); ?></h1>
        <p><?php _e( 'Aquí puede proponer nuevos convenios a instituciones y negocios, y gestionar sus alianzas activas.', 'wp-cupon-whatsapp' ); ?></p>

        <div class="wpcw-page-actions">
            <a href="#" class="button button-primary"><?php _e( 'Proponer Nuevo Convenio', 'wp-cupon-whatsapp' ); ?></a>
        </div>

        <div id="poststuff" style="margin-top: 20px;">
            
            <!-- Marcador para Convenios Activos -->
            <div class="postbox">
                <h2 class="hndle"><span><?php _e( 'Convenios Activos', 'wp-cupon-whatsapp' ); ?></span></h2>
                <div class="inside">
                    <p><?php _e( 'Aquí se mostrará una tabla con los convenios que ha propuesto y han sido aceptados, o los que ha aceptado de otros.', 'wp-cupon-whatsapp' ); ?></p>
                </div>
            </div>

            <!-- Marcador para Convenios Pendientes -->
            <div class="postbox">
                <h2 class="hndle"><span><?php _e( 'Convenios Pendientes de Aceptación', 'wp-cupon-whatsapp' ); ?></span></h2>
                <div class="inside">
                    <p><?php _e( 'Aquí se mostrarán las propuestas de convenio que ha enviado y están esperando respuesta, y las que ha recibido y necesitan su aprobación.', 'wp-cupon-whatsapp' ); ?></p>
                </div>
            </div>

        </div>

    </div>
    <?php
}

/**
 * Adds the Business Convenios submenu page, visible only to Business Owners.
 */
function wpcw_add_business_convenios_menu() {
    // This capability is defined in our WPCW_Roles_Manager class
    $capability = 'manage_business_profile';

    if ( current_user_can( $capability ) ) {
        add_submenu_page(
            'wpcw-main-dashboard',                  // Slug del menú padre
            __( 'Convenios', 'wp-cupon-whatsapp' ), // Título de la página
            __( 'Convenios', 'wp-cupon-whatsapp' ), // Título del menú
            $capability,                            // Capacidad requerida
            'wpcw-business-convenios',              // Slug del menú
            'wpcw_render_business_convenios_page',  // Callback para renderizar
            2 // Posición dentro del submenú
        );
    }
}
add_action( 'admin_menu', 'wpcw_add_business_convenios_menu', 11 );
