<?php
/**
 * WP Canje Cupon Whatsapp Admin Menu
 *
 * Handles the creation of the plugin's admin menu and submenus.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the admin menu pages for the WPCW plugin.
 */
function wpcw_register_plugin_admin_menu() {
    // Menú Principal del Plugin
    add_menu_page(
        __( 'WP Canje Cupon', 'wp-cupon-whatsapp' ), // Título de la página
        __( 'WP Canje Cupon', 'wp-cupon-whatsapp' ), // Título del menú
        'manage_options',                         // Capacidad requerida (Superadmin)
        'wpcw-main-menu',                         // Slug del menú (para identificarlo)
        'wpcw_render_main_admin_page_placeholder', // Función callback para el contenido de la página principal
        'dashicons-tickets-alt',                  // Icono (Dashicon)
        30                                        // Posición en el menú
    );

    // Submenú para Estadísticas Generales (Superadmin)
    // Este submenú usará el mismo slug que el menú principal para que la página principal sea la de estadísticas.
    // O, si queremos una página de bienvenida separada, le damos un slug diferente a la principal
    // y un slug diferente a estadísticas. Por ahora, la página principal es un placeholder.
    add_submenu_page(
        'wpcw-main-menu',                         // Slug del menú padre
        __( 'Estadísticas Generales', 'wp-cupon-whatsapp' ), // Título de la página
        __( 'Estadísticas', 'wp-cupon-whatsapp' ),    // Título del submenú
        'manage_options',                         // Capacidad requerida (Superadmin)
        'wpcw-stats',                             // Slug de este submenú
        'wpcw_render_superadmin_stats_page_content_wrapper' // Callback para el contenido (definida en admin/stats-page.php)
        // Posición (opcional, por defecto al final)
    );

    // Futuros submenús (Ajustes, etc.) se añadirán aquí.
    // add_submenu_page(
    //     'wpcw-main-menu',
    //     __( 'Ajustes WPCW', 'wp-cupon-whatsapp' ),
    //     __( 'Ajustes', 'wp-cupon-whatsapp' ),
    //     'manage_options', // Capacidad para la página de ajustes
    //     'wpcw-settings',
    //     'wpcw_render_settings_page_content'
    // );

    // TODO: Añadir submenús para Comercios e Instituciones para que vean sus propias estadísticas
    // if ( current_user_can('wpcw_business_owner_cap') ) { // Asumiendo una cap específica
    //     add_menu_page( ... 'wpcw-comercio-stats' ... 'wpcw_render_comercio_stats_page');
    // }
}
add_action( 'admin_menu', 'wpcw_register_plugin_admin_menu' );

/**
 * Placeholder callback para la página principal del menú del plugin.
 */
if ( ! function_exists( 'wpcw_render_main_admin_page_placeholder' ) ) {
    function wpcw_render_main_admin_page_placeholder() {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'Bienvenido a WP Canje Cupon Whatsapp', 'wp-cupon-whatsapp' ) . '</h1>';
        echo '<p>' . esc_html__( 'Esta es la página principal del plugin. Selecciona una opción del submenú.', 'wp-cupon-whatsapp' ) . '</p>';
        echo '<p>' . sprintf( wp_kses_post( __( 'Puedes ver las <a href="%s">Estadísticas Generales</a>.', 'wp-cupon-whatsapp' ) ), esc_url( admin_url( 'admin.php?page=wpcw-stats' ) ) ) . '</p>';
        // Aquí se podrían mostrar algunos KIPs o enlaces rápidos en el futuro.
        echo '</div>';
    }
}

/**
 * Wrapper function for rendering the superadmin stats page.
 * This function will call the actual rendering function from stats-page.php.
 */
if ( ! function_exists( 'wpcw_render_superadmin_stats_page_content_wrapper' ) ) {
    function wpcw_render_superadmin_stats_page_content_wrapper() {
        // El archivo admin/stats-page.php ya está incluido a través de wp-cupon-whatsapp.php if is_admin()
        if ( function_exists( 'wpcw_render_superadmin_stats_page' ) ) {
            wpcw_render_superadmin_stats_page();
        } else {
            echo '<div class="wrap"><h1>' . esc_html__( 'Error', 'wp-cupon-whatsapp' ) . '</h1><p>' . esc_html__( 'La función para renderizar la página de estadísticas no está disponible.', 'wp-cupon-whatsapp' ) . '</p></div>';
        }
    }
}

// La función wpcw_render_superadmin_stats_page() se definirá en admin/stats-page.php
// La inclusión ya se maneja en wp-cupon-whatsapp.php.

?>
