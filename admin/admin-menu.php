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
 * Renders the plugin dashboard page.
 */
if ( ! function_exists( 'wpcw_render_plugin_dashboard_page' ) ) {
    function wpcw_render_plugin_dashboard_page() {
        // Verificar si se completó el setup wizard
        $setup_completed = get_option('wpcw_setup_wizard_completed', false);
        $show_success = isset($_GET['setup']) && $_GET['setup'] === 'completed';
        
        ?>
        <div class="wrap">
            <h1>🎯 Dashboard - WP Cupón WhatsApp</h1>
            <p class="description">Centro de control para la gestión del sistema de cupones y programa de fidelización.</p>
            
            <?php if ($show_success): ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>🎉 ¡Configuración completada exitosamente!</strong> El plugin está listo para usar. Puedes comenzar creando cupones y configurando comercios.</p>
            </div>
            <?php endif; ?>
            
            <?php if (!$setup_completed && !$show_success): ?>
            <div class="notice notice-info" style="border-left-color: #0073aa; padding: 15px; margin: 20px 0;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="font-size: 24px;">🚀</div>
                    <div>
                        <h3 style="margin: 0 0 10px 0; color: #0073aa;">¡Configuración Inicial Recomendada!</h3>
                        <p style="margin: 0 0 10px 0;">Para aprovechar al máximo el plugin, te recomendamos completar la configuración inicial guiada.</p>
                        <a href="<?php echo admin_url('admin.php?page=wpcw-setup-wizard'); ?>" class="button button-primary">🎯 Iniciar Configuración</a>
                        <a href="<?php echo admin_url('admin.php?page=wpcw-settings'); ?>" class="button button-secondary" style="margin-left: 10px;">⚙️ Configuración Manual</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="wpcw-dashboard">
                <div class="wpcw-dashboard-cards">
                    <div class="wpcw-card wpcw-card-primary">
                        <h2>📝 Solicitudes</h2>
                        <p>Administra las solicitudes de adhesión de comercios e instituciones al programa.</p>
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=wpcw_application')); ?>" class="button button-primary">Ver Solicitudes</a>
                    </div>
                    <div class="wpcw-card wpcw-card-success">
                        <h2>🏪 Comercios</h2>
                        <p>Controla y administra todos los comercios registrados en el sistema.</p>
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=wpcw_business')); ?>" class="button button-primary">Ver Comercios</a>
                    </div>
                    <div class="wpcw-card wpcw-card-info">
                        <h2>🏫 Instituciones</h2>
                        <p>Supervisa todas las instituciones participantes del programa.</p>
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=wpcw_institution')); ?>" class="button button-primary">Ver Instituciones</a>
                    </div>
                    <div class="wpcw-card wpcw-card-warning">
                        <h2>🎫 Canjes</h2>
                        <p>Supervisa y administra todos los canjes realizados en el sistema.</p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=wpcw-canjes')); ?>" class="button button-primary">Ver Canjes</a>
                    </div>
                    <div class="wpcw-card wpcw-card-secondary">
                        <h2>📊 Estadísticas</h2>
                        <p>Analiza métricas y reportes generales del programa de fidelización.</p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=wpcw-stats')); ?>" class="button button-primary">Ver Estadísticas</a>
                    </div>
                    <div class="wpcw-card wpcw-card-dark">
                        <h2>⚙️ Configuración</h2>
                        <p>Configura parámetros y ajustes generales del sistema.</p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=wpcw-settings')); ?>" class="button button-primary">Ir a Configuración</a>
                    </div>
                </div>
             </div>
             <style>
             .wpcw-dashboard-cards {
                 display: grid;
                 grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
                 gap: 20px;
                 margin-top: 30px;
             }
             .wpcw-card {
                 background: #fff;
                 border-left: 5px solid #ddd;
                 border-radius: 8px;
                 padding: 25px;
                 box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                 transition: transform 0.2s ease, box-shadow 0.2s ease;
             }
             .wpcw-card:hover {
                 transform: translateY(-2px);
                 box-shadow: 0 5px 20px rgba(0,0,0,0.15);
             }
             .wpcw-card-primary { border-left-color: #007cba; }
             .wpcw-card-success { border-left-color: #46b450; }
             .wpcw-card-info { border-left-color: #00a0d2; }
             .wpcw-card-warning { border-left-color: #ffb900; }
             .wpcw-card-secondary { border-left-color: #826eb4; }
             .wpcw-card-dark { border-left-color: #32373c; }
             .wpcw-card h2 {
                 margin-top: 0;
                 margin-bottom: 15px;
                 color: #333;
                 font-size: 18px;
             }
             .wpcw-card p {
                 color: #666;
                 margin-bottom: 20px;
                 line-height: 1.5;
             }
             .description {
                 font-size: 16px;
                 color: #666;
                 margin-bottom: 10px;
             }
             </style>
         </div>
         <?php
     }
 }

/**
 * Registers the admin menu pages for the WPCW plugin.
 */
function wpcw_register_plugin_admin_menu() {
    // Debug: Log que la función se está ejecutando
    error_log('WPCW: Registrando menú de administración');
    error_log('WPCW: Usuario actual puede manage_options: ' . (current_user_can('manage_options') ? 'SÍ' : 'NO'));
    error_log('WPCW: Admin actual: ' . (is_admin() ? 'SÍ' : 'NO'));
    
    // Verificar si el usuario tiene permisos
    if (!current_user_can('manage_options')) {
        error_log('WPCW: Usuario sin permisos manage_options, menú no registrado');
        return;
    }
    
    // Menú Principal del Plugin - Gestión Unificada
    $result = add_menu_page(
        'WP Cupón WhatsApp',                     // Título de la página
        'WP Cupón WhatsApp',                     // Título del menú
        'manage_options',                        // Capacidad mínima requerida
        'wpcw-dashboard',                        // Slug del menú (corregido)
        'wpcw_render_plugin_dashboard_page',     // Callback para página de escritorio
        'dashicons-tickets-alt',                 // Icono de cupones
        25                                       // Posición en el menú
    );
    
    error_log('WPCW: Resultado add_menu_page: ' . ($result ? 'ÉXITO' : 'FALLÓ'));

    // Submenú Dashboard (página principal)
    add_submenu_page(
        'wpcw-dashboard',                        // Slug del menú padre (corregido)
        'Dashboard',                             // Título de la página
        'Dashboard',                             // Título del submenú
        'manage_options',                        // Capacidad mínima requerida
        'wpcw-dashboard',                        // Mismo slug que el menú principal
        'wpcw_render_plugin_dashboard_page'      // Callback para el contenido
    );

    // Solicitudes
    add_submenu_page(
        'wpcw-dashboard',                        // Slug del menú padre
        'Solicitudes',                           // Título de la página
        'Solicitudes',                           // Título del submenú
        'manage_options',                        // Capacidad requerida
        'wpcw-solicitudes',                      // Slug único del submenú
        'wpcw_redirect_to_solicitudes'           // Callback de redirección
    );

    // Comercios
    add_submenu_page(
        'wpcw-dashboard',                        // Slug del menú padre
        'Comercios',                             // Título de la página
        'Comercios',                             // Título del submenú
        'manage_options',                        // Capacidad requerida
        'wpcw-comercios',                        // Slug único del submenú
        'wpcw_redirect_to_comercios'             // Callback de redirección
    );

    // Instituciones
    add_submenu_page(
        'wpcw-dashboard',                        // Slug del menú padre
        'Instituciones',                         // Título de la página
        'Instituciones',                         // Título del submenú
        'manage_options',                        // Capacidad requerida
        'wpcw-instituciones',                    // Slug único del submenú
        'wpcw_redirect_to_instituciones'         // Callback de redirección
    );

    // Canjes
    add_submenu_page(
        'wpcw-dashboard',                        // Slug del menú padre (corregido)
        'Canjes',                                // Título de la página
        'Canjes',                                // Título del submenú
        'manage_woocommerce',                    // Capacidad requerida
        'wpcw-canjes',                          // Slug de este submenú
        'wpcw_canjes_page'                      // Callback para el contenido
    );

    // Estadísticas
    add_submenu_page(
        'wpcw-dashboard',                        // Slug del menú padre (corregido)
        'Estadísticas',                          // Título de la página
        'Estadísticas',                          // Título del submenú
        'manage_options',                        // Capacidad requerida
        'wpcw-stats',                           // Slug de este submenú
        'wpcw_render_superadmin_stats_page_content_wrapper' // Callback
    );

    // Configuración
    add_submenu_page(
        'wpcw-dashboard',                        // Slug del menú padre (corregido)
        'Configuración',                         // Título de la página
        'Configuración',                         // Título del submenú
        'manage_options',                        // Capacidad requerida
        'wpcw-settings',                         // Slug de este submenú
        'wpcw_render_plugin_settings_page'       // Callback
    );

    // Los submenús específicos para comercios e instituciones se manejan dentro de las estadísticas generales
    // basándose en los permisos del usuario actual
}

// Registrar el hook para crear el menú administrativo
add_action('admin_menu', 'wpcw_register_plugin_admin_menu');

/**
 * Funciones de redirección para mantener los elementos agrupados en el menú
 */
function wpcw_redirect_to_solicitudes() {
    wp_redirect(admin_url('edit.php?post_type=wpcw_application'));
    exit;
}

function wpcw_redirect_to_comercios() {
    wp_redirect(admin_url('edit.php?post_type=wpcw_business'));
    exit;
}

function wpcw_redirect_to_instituciones() {
    wp_redirect(admin_url('edit.php?post_type=wpcw_institution'));
    exit;
}

// Los post types ahora tienen show_in_menu => false, por lo que no necesitan remoción manual

/**
 * Wrapper function for rendering the superadmin stats page.
 */
if ( ! function_exists( 'wpcw_render_superadmin_stats_page_content_wrapper' ) ) {
    function wpcw_render_superadmin_stats_page_content_wrapper() {
        if ( function_exists( 'wpcw_render_superadmin_stats_page' ) ) {
            wpcw_render_superadmin_stats_page();
        } else {
            echo '<div class="wrap"><h1>Error</h1><p>La función para renderizar la página de estadísticas no está disponible.</p></div>';
        }
    }
}
