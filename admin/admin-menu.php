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
    // Verificar permisos básicos
    if (!current_user_can('manage_options')) {
        return;
    }

    // Menú Principal
    add_menu_page(
        'WP Cupón WhatsApp',           // page_title
        'WP Cupón WhatsApp',           // menu_title
        'manage_options',              // capability
        'wpcw-dashboard',              // menu_slug
        'wpcw_render_dashboard_page',  // function
        'dashicons-tickets-alt',       // icon_url
        25                             // position
    );

    // Submenú: Dashboard (página principal)
    add_submenu_page(
        'wpcw-dashboard',              // parent_slug
        'Dashboard',                   // page_title
        'Dashboard',                   // menu_title
        'manage_options',              // capability
        'wpcw-dashboard',              // menu_slug (mismo que el padre)
        'wpcw_render_dashboard_page'   // function
    );

    // Submenú: Solicitudes
    add_submenu_page(
        'wpcw-dashboard',
        'Solicitudes de Adhesión',
        'Solicitudes',
        'manage_options',
        'edit.php?post_type=wpcw_application'
    );

    // Submenú: Comercios
    add_submenu_page(
        'wpcw-dashboard',
        'Gestionar Comercios',
        'Comercios',
        'manage_options',
        'edit.php?post_type=wpcw_business'
    );

    // Submenú: Instituciones
    add_submenu_page(
        'wpcw-dashboard',
        'Gestionar Instituciones',
        'Instituciones',
        'manage_options',
        'edit.php?post_type=wpcw_institution'
    );

    // Submenú: Canjes
    add_submenu_page(
        'wpcw-dashboard',
        'Gestionar Canjes',
        'Canjes',
        'manage_options',
        'wpcw-canjes',
        'wpcw_canjes_page'
    );

    // Submenú: Estadísticas
    add_submenu_page(
        'wpcw-dashboard',
        'Estadísticas Generales',
        'Estadísticas',
        'manage_options',
        'wpcw-stats',
        'wpcw_render_superadmin_stats_page_content_wrapper'
    );

    // Submenú: Configuración
    add_submenu_page(
        'wpcw-dashboard',
        'Configuración del Plugin',
        'Configuración',
        'manage_options',
        'wpcw-settings',
        'wpcw_render_plugin_settings_page'
    );
}

// Registrar el menú con alta prioridad
add_action('admin_menu', 'wpcw_register_plugin_admin_menu', 5);

/**
 * Renderiza la página del Dashboard principal.
 */
function wpcw_render_dashboard_page() {
    // Mostrar mensaje de éxito si viene del setup wizard
    if (isset($_GET['setup']) && $_GET['setup'] === 'completed') {
        echo '<div class="notice notice-success is-dismissible" style="margin: 20px 0;">';
        echo '<p><strong>🎉 ¡Configuración completada exitosamente!</strong> Tu plugin WP Cupón WhatsApp está listo para usar.</p>';
        echo '</div>';
    }
    
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('WP Cupón WhatsApp - Dashboard', 'wp-cupon-whatsapp'); ?></h1>
        
        <div class="wpcw-dashboard-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
            
            <!-- Tarjeta de Solicitudes -->
            <div class="wpcw-dashboard-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; color: #0073aa;">📋 Solicitudes de Adhesión</h3>
                <p>Gestiona las solicitudes de comercios e instituciones que desean adherirse al programa.</p>
                <a href="<?php echo admin_url('edit.php?post_type=wpcw_application'); ?>" class="button button-primary">Ver Solicitudes</a>
            </div>

            <!-- Tarjeta de Comercios -->
            <div class="wpcw-dashboard-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; color: #0073aa;">🏪 Comercios</h3>
                <p>Administra todos los comercios adheridos al programa de cupones.</p>
                <a href="<?php echo admin_url('edit.php?post_type=wpcw_business'); ?>" class="button button-primary">Gestionar Comercios</a>
            </div>

            <!-- Tarjeta de Instituciones -->
            <div class="wpcw-dashboard-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; color: #0073aa;">🏛️ Instituciones</h3>
                <p>Gestiona las instituciones participantes en el programa.</p>
                <a href="<?php echo admin_url('edit.php?post_type=wpcw_institution'); ?>" class="button button-primary">Ver Instituciones</a>
            </div>

            <!-- Tarjeta de Canjes -->
            <div class="wpcw-dashboard-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; color: #0073aa;">🎫 Canjes</h3>
                <p>Supervisa todos los canjes de cupones realizados por los usuarios.</p>
                <a href="<?php echo admin_url('admin.php?page=wpcw-canjes'); ?>" class="button button-primary">Ver Canjes</a>
            </div>

            <!-- Tarjeta de Estadísticas -->
            <div class="wpcw-dashboard-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; color: #0073aa;">📊 Estadísticas</h3>
                <p>Visualiza estadísticas generales del programa de cupones.</p>
                <a href="<?php echo admin_url('admin.php?page=wpcw-stats'); ?>" class="button button-primary">Ver Estadísticas</a>
            </div>

            <!-- Tarjeta de Configuración -->
            <div class="wpcw-dashboard-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; color: #0073aa;">⚙️ Configuración</h3>
                <p>Configura los ajustes generales del plugin.</p>
                <a href="<?php echo admin_url('admin.php?page=wpcw-settings'); ?>" class="button button-primary">Configurar</a>
            </div>

        </div>

        <hr style="margin: 30px 0;">

        <h2>🔗 Enlaces Rápidos a WooCommerce</h2>
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <a href="<?php echo admin_url('post-new.php?post_type=shop_coupon'); ?>" class="button button-secondary" target="_blank">➕ Crear Nuevo Cupón</a>
            <a href="<?php echo admin_url('edit.php?post_type=shop_coupon'); ?>" class="button button-secondary" target="_blank">📝 Gestionar Cupones</a>
            <a href="<?php echo admin_url('admin.php?page=wc-admin'); ?>" class="button button-secondary" target="_blank">🛒 WooCommerce Dashboard</a>
        </div>

        <div style="margin-top: 30px; padding: 15px; background: #f0f8ff; border-left: 4px solid #0073aa;">
            <h4 style="margin-top: 0;">ℹ️ Información del Plugin</h4>
            <p><strong>Versión:</strong> <?php echo defined('WPCW_VERSION') ? WPCW_VERSION : '1.2.0'; ?></p>
            <p><strong>Estado:</strong> <span style="color: green;">✅ Activo y funcionando</span></p>
            
            <?php if (!get_option('wpcw_setup_wizard_completed', false)): ?>
            <div style="margin-top: 15px; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px;">
                <p style="margin: 0; color: #856404;"><strong>🚀 ¿Primera vez usando el plugin?</strong></p>
                <p style="margin: 5px 0 10px 0; color: #856404;">Te recomendamos completar la configuración inicial guiada.</p>
                <a href="<?php echo admin_url('admin.php?page=wpcw-setup-wizard'); ?>" class="button button-primary" style="margin-right: 10px;">Iniciar Configuración Guiada</a>
                <a href="#" onclick="fetch('<?php echo admin_url('admin-ajax.php'); ?>', {method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=wpcw_dismiss_setup_notice&nonce=<?php echo wp_create_nonce('wpcw_dismiss_setup'); ?>'}).then(() => location.reload()); return false;" class="button button-link" style="color: #856404;">No mostrar más</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Wrapper functions para las páginas de estadísticas
 */
if (!function_exists('wpcw_render_superadmin_stats_page_content_wrapper')) {
    function wpcw_render_superadmin_stats_page_content_wrapper() {
        if (function_exists('wpcw_render_superadmin_stats_page')) {
            wpcw_render_superadmin_stats_page();
        } else {
            echo '<div class="wrap"><h1>Estadísticas</h1><p>La página de estadísticas se está cargando...</p></div>';
        }
    }
}

?>
