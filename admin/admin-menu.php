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
    // Menú Principal Unificado
    add_menu_page(
        'WP Cupón WhatsApp',
        'WP Cupón WhatsApp',
        'manage_options',
        'wpcw-main-menu',
        'wpcw_render_dashboard_page', // Nueva página de bienvenida/dashboard
        'dashicons-tickets-alt',
        30
    );

    // Submenú: Escritorio (usa el mismo slug que el principal para ser la página por defecto)
    add_submenu_page(
        'wpcw-main-menu',
        'Escritorio',
        'Escritorio',
        'manage_options',
        'wpcw-main-menu', // Slug del padre para que sea la página principal
        'wpcw_render_dashboard_page'
    );

    // Submenú: Ajustes
    add_submenu_page(
        'wpcw-main-menu',
        'Ajustes',
        'Ajustes',
        'manage_options',
        'wpcw-settings', // Nuevo slug para la página de ajustes
        'wpcw_render_plugin_settings_page'
    );

    // Submenú: Estadísticas Generales (para Superadmin)
    add_submenu_page(
        'wpcw-main-menu',
        'Estadísticas Generales',
        'Estadísticas',
        'manage_options',
        'wpcw-stats',
        'wpcw_render_superadmin_stats_page_content_wrapper'
    );

    // Menús para roles específicos (Comercio, Institución)
    // Estos se mostrarán como menús de nivel superior solo para esos roles.
    if ( current_user_can('wpcw_view_own_business_stats') && !current_user_can('manage_options') ) {
        add_menu_page(
            'Mis Estadísticas',
            'Mis Estadísticas',
            'wpcw_view_own_business_stats',
            'wpcw-business-stats',
            'wpcw_render_business_stats_page_content_wrapper',
            'dashicons-chart-line',
            31
        );
    }

    if ( current_user_can('wpcw_view_own_institution_stats') && !current_user_can('manage_options') ) {
        add_menu_page(
            'Mis Estadísticas',
            'Mis Estadísticas',
            'wpcw_view_own_institution_stats',
            'wpcw-institution-stats',
            'wpcw_render_institution_stats_page_content_wrapper',
            'dashicons-chart-bar',
            31
        );
    }
}
add_action( 'admin_menu', 'wpcw_register_plugin_admin_menu' );

/**
 * Renderiza la nueva página del Escritorio.
 */
function wpcw_render_dashboard_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__( 'Bienvenido a WP Cupón WhatsApp', 'wp-cupon-whatsapp' ); ?></h1>
        <p><?php echo esc_html__( 'Gestiona tus cupones, solicitudes y configuraciones desde un solo lugar.', 'wp-cupon-whatsapp' ); ?></p>

        <h2><?php echo esc_html__( 'Accesos Rápidos', 'wp-cupon-whatsapp' ); ?></h2>
        <ul style="list-style-type: disc; padding-left: 20px;">
            <li><a href="<?php echo admin_url('edit.php?post_type=wpcw_application'); ?>"><?php echo esc_html__( 'Ver Todas las Solicitudes de Adhesión', 'wp-cupon-whatsapp' ); ?></a></li>
            <li><a href="<?php echo admin_url('edit.php?post_type=wpcw_business'); ?>"><?php echo esc_html__( 'Gestionar Comercios', 'wp-cupon-whatsapp' ); ?></a></li>
            <li><a href="<?php echo admin_url('edit.php?post_type=wpcw_institution'); ?>"><?php echo esc_html__( 'Gestionar Instituciones', 'wp-cupon-whatsapp' ); ?></a></li>
            <li><a href="<?php echo admin_url('admin.php?page=wpcw-stats'); ?>"><?php echo esc_html__( 'Ver Estadísticas', 'wp-cupon-whatsapp' ); ?></a></li>
            <li><a href="<?php echo admin_url('admin.php?page=wpcw-settings'); ?>"><?php echo esc_html__( 'Ir a Ajustes', 'wp-cupon-whatsapp' ); ?></a></li>
            <li><a href="<?php echo admin_url('post-new.php?post_type=shop_coupon'); ?>" target="_blank"><?php echo esc_html__( 'Crear un Nuevo Cupón (en WooCommerce)', 'wp-cupon-whatsapp' ); ?></a></li>
        </ul>
    </div>
    <?php
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
