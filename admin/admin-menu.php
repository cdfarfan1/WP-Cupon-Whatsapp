<?php
/**
 * WP Cupón WhatsApp (Versión Corregida) Admin Menu
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
        $setup_completed = get_option( 'wpcw_setup_wizard_completed', false );
        $show_success    = isset( $_GET['setup'] ) && $_GET['setup'] === 'completed';

        ?>
        <div class="wrap">
            <h1>🎯 Dashboard - WP Cupón WhatsApp</h1>
            <p class="description">Centro de control para la gestión del sistema de cupones y programa de fidelización.</p>
            
            <?php
            // Verificar dependencias y mostrar advertencias
            $missing_dependencies = array();
            if ( ! class_exists( 'WooCommerce' ) ) {
                $missing_dependencies[] = 'WooCommerce no está instalado o activado';
            }
            if ( ! did_action( 'elementor/loaded' ) ) {
                $missing_dependencies[] = 'Elementor no está instalado o activado';
            }

            if ( ! empty( $missing_dependencies ) ) :
                ?>
            <div class="notice notice-warning">
                <p><strong>⚠️ Dependencias Faltantes:</strong></p>
                <ul style="list-style-type: disc; margin-left: 20px;">
                    <?php foreach ( $missing_dependencies as $dependency ) : ?>
                        <li><?php echo esc_html( $dependency ); ?></li>
                    <?php endforeach; ?>
                </ul>
                <p>El plugin funcionará con limitaciones hasta que se instalen todas las dependencias.</p>
            </div>
            <?php endif; ?>
            
            <?php if ( $show_success ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>🎉 ¡Configuración completada exitosamente!</strong> El plugin está listo para usar. Puedes comenzar creando cupones y configurando comercios.</p>
            </div>
            <?php endif; ?>
            
            <?php if ( ! $setup_completed && ! $show_success ) : ?>
            <div class="notice notice-info" style="border-left-color: #0073aa; padding: 15px; margin: 20px 0;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="font-size: 24px;">🚀</div>
                    <div>
                        <h3 style="margin: 0 0 10px 0; color: #0073aa;">¡Configuración Inicial Recomendada!</h3>
                        <p style="margin: 0 0 10px 0;">Para aprovechar al máximo el plugin, te recomendamos completar la configuración inicial guiada.</p>
                        <a href="<?php echo admin_url( 'admin.php?page=wpcw-setup-wizard' ); ?>" class="button button-primary">🎯 Iniciar Configuración</a>
                        <a href="<?php echo admin_url( 'admin.php?page=wpcw-settings' ); ?>" class="button button-secondary" style="margin-left: 10px;">⚙️ Configuración Manual</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Enhanced Dashboard with Real Metrics -->
            <?php WPCW_Dashboard::render_metrics_cards(); ?>

            <!-- Chart Section -->
            <?php WPCW_Dashboard::render_chart(); ?>

            <!-- Notifications and Health Status -->
            <div class="wpcw-dashboard-secondary">
                <div class="wpcw-dashboard-notifications">
                    <?php WPCW_Dashboard::render_notifications(); ?>
                </div>
                <div class="wpcw-dashboard-health">
                    <?php WPCW_Dashboard::render_system_health(); ?>
                </div>
            </div>

            <style>
            .wpcw-dashboard-secondary {
                display: grid;
                grid-template-columns: 2fr 1fr;
                gap: 20px;
                margin: 30px 0;
            }

            @media (max-width: 768px) {
                .wpcw-dashboard-secondary {
                    grid-template-columns: 1fr;
                }
            }
            </style>

            <!-- Database Migration Status Widget -->
            <?php
            // Quick check for migration status
            global $wpdb;
            $table_name = $wpdb->prefix . 'wpcw_canjes';
            $columns = $wpdb->get_results( "DESCRIBE {$table_name}" );
            $has_estado_canje = false;
            foreach ( $columns as $column ) {
                if ( $column->Field === 'estado_canje' ) {
                    $has_estado_canje = true;
                    break;
                }
            }
            ?>
            <?php if ( $has_estado_canje ) : ?>
            <div class="notice notice-success" style="display: flex; align-items: center; gap: 15px; margin: 20px 0; padding: 15px; border-left-width: 5px;">
                <div style="font-size: 32px;">✅</div>
                <div style="flex: 1;">
                    <h3 style="margin: 0 0 5px 0;">Base de Datos Migrada Correctamente</h3>
                    <p style="margin: 0;">
                        La base de datos está actualizada. Todas las funcionalidades están disponibles.
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpcw-database-status' ) ); ?>" style="margin-left: 10px;">
                            Ver detalles →
                        </a>
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="wpcw-dashboard">
                <div class="wpcw-dashboard-cards">
                    <div class="wpcw-card wpcw-card-primary">
                        <h2>📝 Solicitudes</h2>
                        <p>Administra las solicitudes de adhesión de comercios e instituciones al programa.</p>
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=wpcw_application' ) ); ?>" class="button button-primary">Ver Solicitudes</a>
                    </div>
                    <div class="wpcw-card wpcw-card-success">
                        <h2>🏪 Comercios</h2>
                        <p>Controla y administra todos los comercios registrados en el sistema.</p>
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=wpcw_business' ) ); ?>" class="button button-primary">Ver Comercios</a>
                    </div>
                    <div class="wpcw-card wpcw-card-info">
                        <h2>🏫 Instituciones</h2>
                        <p>Supervisa todas las instituciones participantes del programa.</p>
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=wpcw_institution' ) ); ?>" class="button button-primary">Ver Instituciones</a>
                    </div>
                    <div class="wpcw-card wpcw-card-warning">
                        <h2>🎫 Canjes</h2>
                        <p>Supervisa y administra todos los canjes realizados en el sistema.</p>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpcw-canjes' ) ); ?>" class="button button-primary">Ver Canjes</a>
                    </div>
                    <div class="wpcw-card wpcw-card-secondary">
                        <h2>📊 Estadísticas</h2>
                        <p>Analiza métricas y reportes generales del programa de fidelización.</p>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpcw-stats' ) ); ?>" class="button button-primary">Ver Estadísticas</a>
                    </div>
                    <div class="wpcw-card wpcw-card-dark">
                        <h2>⚙️ Configuración</h2>
                        <p>Configura parámetros y ajustes generales del sistema.</p>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpcw-settings' ) ); ?>" class="button button-primary">Ir a Configuración</a>
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
    // error_log('WPCW: *** INICIANDO FUNCIÓN wpcw_register_plugin_admin_menu ***');
    // error_log('WPCW: Registrando menú de administración');
    // error_log('WPCW: Usuario actual puede manage_options: ' . (current_user_can('manage_options') ? 'SÍ' : 'NO'));
    // error_log('WPCW: Admin actual: ' . (is_admin() ? 'SÍ' : 'NO'));
    // error_log('WPCW: Función existe: ' . (function_exists('add_menu_page') ? 'SÍ' : 'NO'));

    // Verificar permisos de administrador
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Menú Principal del Plugin - Gestión Unificada
    $result = add_menu_page(
        'WP Cupón WhatsApp',                     // Título de la página
        'WP Cupón WhatsApp',                     // Título del menú
        'manage_options',                        // Capacidad mínima requerida
        'wpcw-main-dashboard',                   // Slug del menú (cambiado)
        'wpcw_render_plugin_dashboard_page',     // Callback para página de escritorio
        'dashicons-tickets-alt',                 // Icono de cupones
        25                                       // Posición en el menú
    );

    // error_log('WPCW: Resultado add_menu_page: ' . ($result ? 'ÉXITO' : 'FALLÓ'));

    // Submenú Dashboard (página principal)
    add_submenu_page(
        'wpcw-main-dashboard',                   // Slug del menú padre (actualizado)
        'Dashboard',                             // Título de la página
        'Dashboard',                             // Título del submenú
        'manage_options',                        // Capacidad mínima requerida
        'wpcw-dashboard',                        // Mismo slug que el menú principal
        'wpcw_render_plugin_dashboard_page'      // Callback para el contenido
    );

    // Solicitudes de Adhesión
    add_submenu_page(
        'wpcw-main-dashboard',                   // Slug del menú padre (actualizado)
        'Solicitudes de Adhesión',               // Título de la página
        'Solicitudes',                           // Título del submenú
        'manage_options',                        // Capacidad requerida
        'wpcw-applications',                     // Slug único del submenú
        'wpcw_render_business_applications_page' // Callback para la página
    );

    // Comercios
    add_submenu_page(
        'wpcw-main-dashboard',                   // Slug del menú padre (actualizado)
        'Comercios',                             // Título de la página
        'Comercios',                             // Título del submenú
        'manage_options',                        // Capacidad requerida
        'wpcw-businesses',                       // Slug único del submenú
        'wpcw_render_businesses_page'            // Callback para la página
    );

    // Instituciones
    add_submenu_page(
        'wpcw-main-dashboard',                   // Slug del menú padre (actualizado)
        'Instituciones',                         // Título de la página
        'Instituciones',                         // Título del submenú
        'manage_options',                        // Capacidad requerida
        'wpcw-instituciones',                    // Slug único del submenú
        'wpcw_redirect_to_instituciones'         // Callback de redirección
    );

    // Canjes
    add_submenu_page(
        'wpcw-main-dashboard',                   // Slug del menú padre (actualizado)
        'Canjes',                                // Título de la página
        'Canjes',                                // Título del submenú
        'manage_options',                        // Capacidad requerida
        'wpcw-canjes',                          // Slug de este submenú
        'wpcw_canjes_page'                      // Callback para el contenido
    );

    // Gestión de Usuarios de Comercios
    add_submenu_page(
        null,                                    // No mostrar en menú
        'Usuarios del Comercio',                 // Título de la página
        'Usuarios del Comercio',                 // Título del submenú
        'manage_options',                        // Capacidad requerida
        'wpcw-business-users',                   // Slug de este submenú
        'wpcw_render_business_users_page'        // Callback
    );

    // Estadísticas
    add_submenu_page(
        'wpcw-main-dashboard',                   // Slug del menú padre (actualizado)
        'Estadísticas',                          // Título de la página
        'Estadísticas',                          // Título del submenú
        'manage_options',                        // Capacidad requerida
        'wpcw-stats',                           // Slug de este submenú
        'wpcw_render_superadmin_stats_page_content_wrapper' // Callback
    );

    // Configuración
    add_submenu_page(
        'wpcw-main-dashboard',                   // Slug del menú padre (actualizado)
        'Configuración',                         // Título de la página
        'Configuración',                         // Título del submenú
        'manage_options',                        // Capacidad requerida
        'wpcw-settings',                         // Slug de este submenú
        'wpcw_render_plugin_settings_page'       // Callback
    );

    // Los submenús específicos para comercios e instituciones se manejan dentro de las estadísticas generales
    // basándose en los permisos del usuario actual

    // error_log('WPCW: *** FUNCIÓN wpcw_register_plugin_admin_menu COMPLETADA ***');
}

// Enqueue scripts and styles for dashboard
function wpcw_enqueue_dashboard_assets( $hook ) {
    // Only load on our dashboard page
    if ( $hook !== 'toplevel_page_wpcw-main-dashboard' ) {
        return;
    }

    // Enqueue Chart.js
    wp_enqueue_script(
        'chart-js',
        'https://cdn.jsdelivr.net/npm/chart.js',
        array(),
        '4.4.0',
        true
    );

    // Enqueue our dashboard script
    wp_enqueue_script(
        'wpcw-dashboard',
        WPCW_PLUGIN_URL . 'admin/js/dashboard.js',
        array( 'chart-js' ),
        WPCW_VERSION,
        true
    );

    // Enqueue dashboard styles
    wp_enqueue_style(
        'wpcw-dashboard',
        WPCW_PLUGIN_URL . 'admin/css/dashboard.css',
        array(),
        WPCW_VERSION
    );
}
add_action( 'admin_enqueue_scripts', 'wpcw_enqueue_dashboard_assets' );

// AJAX handler for refreshing dashboard metrics
function wpcw_refresh_dashboard_metrics() {
    // For now, skip nonce check for simplicity - in production, add proper nonce
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( __( 'No tienes permisos para realizar esta acción.', 'wp-cupon-whatsapp' ) );
    }

    $metrics = WPCW_Dashboard::get_metrics();
    $chart_data = WPCW_Dashboard::get_chart_data();

    wp_send_json_success( array(
        'metrics' => $metrics,
        'chart_data' => $chart_data,
    ) );
}
add_action( 'wp_ajax_wpcw_refresh_dashboard_metrics', 'wpcw_refresh_dashboard_metrics' );

// Registrar el hook para crear el menú administrativo
add_action( 'admin_menu', 'wpcw_register_plugin_admin_menu', 1 );

/**
 * Legacy redirect: wpcw-dashboard → wpcw-main-dashboard
 * 
 * Redirige URLs antiguas al nuevo slug para compatibilidad con bookmarks
 * y enlaces que aún no se hayan actualizado.
 * 
 * @since 1.5.1
 * @deprecated-slug wpcw-dashboard (remover en v1.6.0 - 6 meses)
 * @author Marcus Chen - Legacy compatibility strategy
 * @security wp_safe_redirect() con status 301
 */
function wpcw_redirect_legacy_menu_slug() {
    global $pagenow;
    
    // Solo en admin
    if ( ! is_admin() ) {
        return;
    }
    
    // Solo en página admin.php
    if ( $pagenow !== 'admin.php' ) {
        return;
    }
    
    // Verificar si es una página legacy
    if ( ! isset( $_GET['page'] ) ) {
        return;
    }

    $page = sanitize_text_field( $_GET['page'] );
    $legacy_slugs = array(
        'wpcw-dashboard'      => 'wpcw-main-dashboard',
        'wp-cupon-whatsapp'   => 'wpcw-main-dashboard',
        'wpcw_dashboard'      => 'wpcw-main-dashboard',
    );

    // Si no es un slug legacy, salir
    if ( ! isset( $legacy_slugs[ $page ] ) ) {
        return;
    }

    // Construir URL nueva preservando parámetros adicionales
    $query_params = $_GET;
    $query_params['page'] = $legacy_slugs[ $page ]; // Cambiar al nuevo slug

    $new_url = add_query_arg( $query_params, admin_url( 'admin.php' ) );

    // Redirect 301 (permanente) - SEO friendly
    wp_safe_redirect( $new_url, 301 );
    exit;
}
add_action( 'admin_init', 'wpcw_redirect_legacy_menu_slug', 1 );

/**
 * Funciones de redirección para mantener los elementos agrupados en el menú
 */
function wpcw_redirect_to_solicitudes() {
    $redirect_url = admin_url( 'edit.php?post_type=wpcw_application' );
    if ( ! empty( $redirect_url ) ) {
        wp_redirect( $redirect_url );
        exit;
    }
}

function wpcw_redirect_to_comercios() {
    $redirect_url = admin_url( 'edit.php?post_type=wpcw_business' );
    if ( ! empty( $redirect_url ) ) {
        wp_redirect( $redirect_url );
        exit;
    }
}

function wpcw_redirect_to_instituciones() {
    $redirect_url = admin_url( 'edit.php?post_type=wpcw_institution' );
    if ( ! empty( $redirect_url ) ) {
        wp_redirect( $redirect_url );
        exit;
    }
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
