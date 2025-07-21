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
        'wpcw_render_plugin_settings_page',       // NUEVO CALLBACK
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

    // Submenú para Gestión de Canjes (Superadmin)
    add_submenu_page(
        'wpcw-main-menu',                         // Slug del menú padre
        __( 'Gestión de Canjes', 'wp-cupon-whatsapp' ), // Título de la página
        __( 'Canjes', 'wp-cupon-whatsapp' ),     // Título del submenú
        'manage_woocommerce',                     // Capacidad requerida (Admin/SuperAdmin)
        'wpcw-canjes',                           // Slug de este submenú
        'wpcw_canjes_page'                       // Callback para el contenido
    );

    // Submenú para Estadísticas del Comercio (wpcw_business_owner)
    add_submenu_page(
        'wpcw-main-menu',                         // Slug del menú padre
        __( 'Estadísticas de Mi Comercio', 'wp-cupon-whatsapp' ), // Título de la página
        __( 'Mis Estadísticas', 'wp-cupon-whatsapp' ),    // Título del submenú (lo que ve el rol)
        'wpcw_view_own_business_stats',           // Capacidad requerida (definida en roles.php)
        'wpcw-business-stats',                    // Slug de este submenú
        'wpcw_render_business_stats_page_content_wrapper' // Callback (definida en admin/business-stats-page.php)
    );

    // Submenú para Estadísticas de la Institución (wpcw_institution_manager)
    add_submenu_page(
        'wpcw-main-menu',                         // Slug del menú padre
        __( 'Estadísticas de Mi Institución', 'wp-cupon-whatsapp' ), // Título de la página
        __( 'Mis Estadísticas', 'wp-cupon-whatsapp' ),    // Título del submenú (mismo que para comercio, se muestra según rol/cap)
        'wpcw_view_own_institution_stats',        // Capacidad requerida (definida en roles.php)
        'wpcw-institution-stats',                 // Slug de este submenú
        'wpcw_render_institution_stats_page_content_wrapper' // Callback (a definir su wrapper y la función real)
    );
}
add_action( 'admin_menu', 'wpcw_register_plugin_admin_menu' );

// La función wpcw_render_main_admin_page_placeholder() ya no es necesaria,
// ya que la página principal del menú será la página de Ajustes.
// Su definición será eliminada.

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
            echo '<div class="wrap"><h1>' . esc_html__( 'Error', 'wp-cupon-whatsapp' ) . '</h1><p>' . esc_html__( 'La función para renderizar la página de estadísticas generales no está disponible.', 'wp-cupon-whatsapp' ) . '</p></div>';
        }
    }
}

/**
 * Wrapper function for rendering the business owner stats page.
 * This function will call the actual rendering function from business-stats-page.php.
 */
if ( ! function_exists( 'wpcw_render_business_stats_page_content_wrapper' ) ) {
    function wpcw_render_business_stats_page_content_wrapper() {
        // El archivo admin/business-stats-page.php ya está incluido.
        if ( function_exists( 'wpcw_render_business_stats_page_content' ) ) {
            wpcw_render_business_stats_page_content();
        } else {
            echo '<div class="wrap"><h1>' . esc_html__( 'Error', 'wp-cupon-whatsapp' ) . '</h1><p>' . esc_html__( 'La función para renderizar la página de estadísticas del comercio no está disponible.', 'wp-cupon-whatsapp' ) . '</p></div>';
        }
    }
}

// Crear el wrapper para el callback de institution-stats, similar a los otros.
if ( ! function_exists( 'wpcw_render_institution_stats_page_content_wrapper' ) ) {
    function wpcw_render_institution_stats_page_content_wrapper() {
        // require_once WPCW_PLUGIN_DIR . 'admin/institution-stats-page.php'; // Ya incluido globalmente en admin

        if ( function_exists( 'wpcw_render_institution_stats_page_content' ) ) {
            wpcw_render_institution_stats_page_content();
        } else {
            echo '<div class="wrap"><h1>' . esc_html__( 'Error', 'wp-cupon-whatsapp' ) . '</h1><p>' . esc_html__( 'La función para renderizar la página de estadísticas de la institución no está disponible.', 'wp-cupon-whatsapp' ) . '</p></div>';
        }
    }
}

// Las funciones de renderizado real (wpcw_render_superadmin_stats_page, wpcw_render_business_stats_page_content y wpcw_render_institution_stats_page_content)
// se definirán en sus respectivos archivos.

?>
