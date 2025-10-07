<?php
/**
 * WP Cupón WhatsApp - Dashboard Render Functions
 *
 * Contains all render/display functions for admin pages
 *
 * @package WP_Cupon_WhatsApp
 * @since 1.5.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Render dashboard page
 * Displays the main plugin dashboard with system information and status
 */
function wpcw_render_dashboard() {
    // Security check
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-cupon-whatsapp' ) );
    }

    // Get system information
    $system_info = wpcw_get_system_info();

    echo '<div class="wrap">';
    echo '<h1 class="wp-heading-inline">' . esc_html__( '🎫 WP Cupón WhatsApp', 'wp-cupon-whatsapp' ) . '</h1>';

    // Plugin status notice
    $plugin_status = wpcw_get_plugin_status();
    echo '<div class="notice notice-' . esc_attr( $plugin_status['type'] ) . ' is-dismissible">';
    echo '<p><strong>' . wp_kses_post( $plugin_status['title'] ) . '</strong></p>';
    echo '<p>' . wp_kses_post( $plugin_status['message'] ) . '</p>';
    echo '</div>';

    // Quick stats section
    echo '<div class="wpcw-dashboard-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">';

    // Get stats data
    $stats = wpcw_get_dashboard_stats();

    foreach ( $stats as $stat ) {
        echo '<div class="wpcw-stat-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 8px; padding: 20px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
        echo '<div class="wpcw-stat-icon" style="font-size: 2em; margin-bottom: 10px;">' . wp_kses_post( $stat['icon'] ) . '</div>';
        echo '<div class="wpcw-stat-number" style="font-size: 2em; font-weight: bold; color: ' . esc_attr( $stat['color'] ) . ';">' . esc_html( $stat['value'] ) . '</div>';
        echo '<div class="wpcw-stat-label" style="color: #666; font-size: 14px;">' . esc_html( $stat['label'] ) . '</div>';
        echo '</div>';
    }

    echo '</div>';

    // System Information
    echo '<div class="wpcw-system-info">';
    echo '<h2>' . esc_html__( 'Información del Sistema', 'wp-cupon-whatsapp' ) . '</h2>';
    echo '<table class="widefat striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>' . esc_html__( 'Componente', 'wp-cupon-whatsapp' ) . '</th>';
    echo '<th>' . esc_html__( 'Versión', 'wp-cupon-whatsapp' ) . '</th>';
    echo '<th>' . esc_html__( 'Estado', 'wp-cupon-whatsapp' ) . '</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    foreach ( $system_info as $component => $info ) {
        echo '<tr>';
        echo '<td><strong>' . esc_html( $component ) . '</strong></td>';
        echo '<td>' . esc_html( $info['version'] ) . '</td>';
        echo '<td>' . wp_kses_post( $info['status'] ) . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';

    // Features overview
    echo '<div class="wpcw-features-overview">';
    echo '<h2>' . esc_html__( 'Funcionalidades del Plugin', 'wp-cupon-whatsapp' ) . '</h2>';
    echo '<div class="wpcw-features-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">';

    $features = wpcw_get_features_list();
    foreach ( $features as $feature ) {
        echo '<div class="wpcw-feature-card" style="background: #fff; border: 1px solid #e1e1e1; border-radius: 8px; padding: 20px;">';
        echo '<h3 style="margin-top: 0; color: #23282d;">' . esc_html( $feature['title'] ) . '</h3>';
        echo '<p style="color: #666; margin-bottom: 15px;">' . esc_html( $feature['description'] ) . '</p>';
        echo '<div class="wpcw-feature-status">';
        echo '<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> ';
        echo '<span style="color: #46b450; font-weight: 600;">' . esc_html__( 'Activo', 'wp-cupon-whatsapp' ) . '</span>';
        echo '</div>';
        echo '</div>';
    }

    echo '</div>';
    echo '</div>';

    echo '</div>';
}

/**
 * Render settings page
 */
function wpcw_render_settings() {
    echo '<div class="wrap">';
    echo '<h1>⚙️ Configuración - WP Cupón WhatsApp</h1>';

    echo '<div class="notice notice-info">';
    echo '<p><strong>Configuración del Plugin</strong></p>';
    echo '<p>Aquí puedes configurar las opciones principales del plugin.</p>';
    echo '</div>';

    echo '<form method="post" action="options.php">';
    echo '<table class="form-table">';
    echo '<tr>';
    echo '<th scope="row">WhatsApp Business API</th>';
    echo '<td><input type="text" name="wpcw_whatsapp_api" value="" class="regular-text" placeholder="Token de API de WhatsApp Business" /></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Número de WhatsApp</th>';
    echo '<td><input type="text" name="wpcw_whatsapp_number" value="" class="regular-text" placeholder="+5491123456789" /></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th scope="row">Mensaje de Cupón</th>';
    echo '<td><textarea name="wpcw_coupon_message" rows="4" cols="50" placeholder="Tu cupón de descuento es: {coupon_code}">Tu cupón de descuento es: {coupon_code}</textarea></td>';
    echo '</tr>';
    echo '</table>';
    echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Guardar Configuración" /></p>';
    echo '</form>';

    echo '</div>';
}

/**
 * Render canjes page
 */
function wpcw_render_canjes() {
    echo '<div class="wrap">';
    echo '<h1>🎫 Canjes de Cupones</h1>';

    echo '<div class="notice notice-info">';
    echo '<p><strong>Historial de Canjes</strong></p>';
    echo '<p>Aquí puedes ver todos los canjes de cupones realizados.</p>';
    echo '</div>';

    // Simular datos de canjes
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>ID</th>';
    echo '<th>Usuario</th>';
    echo '<th>Código de Cupón</th>';
    echo '<th>Fecha de Canje</th>';
    echo '<th>Estado</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    echo '<tr>';
    echo '<td>1</td>';
    echo '<td>Usuario Demo</td>';
    echo '<td>DESCUENTO20</td>';
    echo '<td>' . date('Y-m-d H:i:s') . '</td>';
    echo '<td><span class="dashicons dashicons-yes-alt" style="color: green;"></span> Canjeado</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td colspan="5" style="text-align: center; color: #666;">No hay canjes registrados aún</td>';
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';

    echo '</div>';
}

/**
 * Render estadisticas page
 */
function wpcw_render_estadisticas() {
    echo '<div class="wrap">';
    echo '<h1>📊 Estadísticas - WP Cupón WhatsApp</h1>';

    echo '<div class="notice notice-info">';
    echo '<p><strong>Estadísticas del Plugin</strong></p>';
    echo '<p>Aquí puedes ver las estadísticas de uso del plugin.</p>';
    echo '</div>';

    echo '<div class="wpcw-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">';

    // Tarjeta de cupones generados
    echo '<div class="wpcw-stat-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">';
    echo '<h3 style="margin: 0 0 10px 0; color: #1d2327;">Cupones Generados</h3>';
    echo '<div style="font-size: 2em; font-weight: bold; color: #2271b1;">0</div>';
    echo '<p style="margin: 5px 0 0 0; color: #666;">Total de cupones creados</p>';
    echo '</div>';

    // Tarjeta de canjes realizados
    echo '<div class="wpcw-stat-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">';
    echo '<h3 style="margin: 0 0 10px 0; color: #1d2327;">Canjes Realizados</h3>';
    echo '<div style="font-size: 2em; font-weight: bold; color: #00a32a;">0</div>';
    echo '<p style="margin: 5px 0 0 0; color: #666;">Cupones canjeados</p>';
    echo '</div>';

    // Tarjeta de usuarios activos
    echo '<div class="wpcw-stat-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; text-align: center;">';
    echo '<h3 style="margin: 0 0 10px 0; color: #1d2327;">Usuarios Activos</h3>';
    echo '<div style="font-size: 2em; font-weight: bold; color: #d63638;">0</div>';
    echo '<p style="margin: 5px 0 0 0; color: #666;">Usuarios que han usado cupones</p>';
    echo '</div>';

    echo '</div>';

    echo '</div>';
}

/**
 * Get system information
 */
function wpcw_get_system_info() {
    return array(
        __( 'WP Cupón WhatsApp', 'wp-cupon-whatsapp' ) => array(
            'version' => WPCW_VERSION,
            'status' => '<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> ' . __( 'Activo', 'wp-cupon-whatsapp' ),
        ),
        __( 'WordPress', 'wp-cupon-whatsapp' ) => array(
            'version' => get_bloginfo( 'version' ),
            'status' => version_compare( get_bloginfo( 'version' ), '5.0', '>=' ) ?
                '<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> ' . __( 'Compatible', 'wp-cupon-whatsapp' ) :
                '<span class="dashicons dashicons-warning" style="color: #f56e28;"></span> ' . __( 'Actualización recomendada', 'wp-cupon-whatsapp' ),
        ),
        __( 'WooCommerce', 'wp-cupon-whatsapp' ) => array(
            'version' => defined( 'WC_VERSION' ) ? WC_VERSION : __( 'No instalado', 'wp-cupon-whatsapp' ),
            'status' => class_exists( 'WooCommerce' ) ?
                '<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> ' . __( 'Activo', 'wp-cupon-whatsapp' ) :
                '<span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span> ' . __( 'Requerido', 'wp-cupon-whatsapp' ),
        ),
        __( 'PHP', 'wp-cupon-whatsapp' ) => array(
            'version' => PHP_VERSION,
            'status' => version_compare( PHP_VERSION, '7.4', '>=' ) ?
                '<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> ' . __( 'Compatible', 'wp-cupon-whatsapp' ) :
                '<span class="dashicons dashicons-no-alt" style="color: #dc3232;"></span> ' . __( 'Versión insuficiente', 'wp-cupon-whatsapp' ),
        ),
        __( 'MySQL', 'wp-cupon-whatsapp' ) => array(
            'version' => wpcw_get_mysql_version(),
            'status' => version_compare( wpcw_get_mysql_version(), '5.6', '>=' ) ?
                '<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> ' . __( 'Compatible', 'wp-cupon-whatsapp' ) :
                '<span class="dashicons dashicons-warning" style="color: #f56e28;"></span> ' . __( 'Actualización recomendada', 'wp-cupon-whatsapp' ),
        ),
    );
}

/**
 * Get MySQL version
 */
function wpcw_get_mysql_version() {
    global $wpdb;
    try {
        return $wpdb->get_var( 'SELECT VERSION()' );
    } catch ( Exception $e ) {
        return __( 'Desconocida', 'wp-cupon-whatsapp' );
    }
}

/**
 * Get plugin status
 */
function wpcw_get_plugin_status() {
    $errors = array();

    // Check critical dependencies
    if ( ! class_exists( 'WooCommerce' ) ) {
        $errors[] = 'woocommerce_missing';
    }

    if ( ! function_exists( 'wp_get_current_user' ) ) {
        $errors[] = 'wordpress_core_missing';
    }

    if ( empty( $errors ) ) {
        return array(
            'type' => 'success',
            'title' => __( '✅ Plugin funcionando correctamente', 'wp-cupon-whatsapp' ),
            'message' => __( 'Todas las dependencias están activas y el plugin está listo para usar.', 'wp-cupon-whatsapp' ),
        );
    } else {
        return array(
            'type' => 'warning',
            'title' => __( '⚠️ Plugin con problemas de configuración', 'wp-cupon-whatsapp' ),
            'message' => __( 'Algunas dependencias no están disponibles. Revisa la información del sistema para más detalles.', 'wp-cupon-whatsapp' ),
        );
    }
}

/**
 * Get dashboard statistics
 */
function wpcw_get_dashboard_stats() {
    // Get basic stats - in a real implementation, these would come from the database
    return array(
        array(
            'icon' => '🎫',
            'value' => '0',
            'label' => __( 'Cupones Activos', 'wp-cupon-whatsapp' ),
            'color' => '#2271b1',
        ),
        array(
            'icon' => '🏪',
            'value' => '0',
            'label' => __( 'Comercios Registrados', 'wp-cupon-whatsapp' ),
            'color' => '#46b450',
        ),
        array(
            'icon' => '📱',
            'value' => '0',
            'label' => __( 'Canjes Realizados', 'wp-cupon-whatsapp' ),
            'color' => '#00a32a',
        ),
        array(
            'icon' => '👥',
            'value' => '0',
            'label' => __( 'Usuarios Activos', 'wp-cupon-whatsapp' ),
            'color' => '#d63638',
        ),
    );
}

/**
 * Get features list
 */
function wpcw_get_features_list() {
    return array(
        array(
            'title' => __( 'Sistema de Cupones', 'wp-cupon-whatsapp' ),
            'description' => __( 'Gestión completa de cupones de fidelización y promocionales integrados con WooCommerce.', 'wp-cupon-whatsapp' ),
        ),
        array(
            'title' => __( 'Integración WhatsApp', 'wp-cupon-whatsapp' ),
            'description' => __( 'Canje de cupones directamente a través de WhatsApp con confirmación automática.', 'wp-cupon-whatsapp' ),
        ),
        array(
            'title' => __( 'Panel de Administración', 'wp-cupon-whatsapp' ),
            'description' => __( 'Interfaz completa para gestionar comercios, cupones y canjes con reportes detallados.', 'wp-cupon-whatsapp' ),
        ),
        array(
            'title' => __( 'APIs REST', 'wp-cupon-whatsapp' ),
            'description' => __( 'APIs completas para integración con sistemas externos y aplicaciones móviles.', 'wp-cupon-whatsapp' ),
        ),
        array(
            'title' => __( 'Shortcodes', 'wp-cupon-whatsapp' ),
            'description' => __( 'Shortcodes para integrar formularios y listados en cualquier página de WordPress.', 'wp-cupon-whatsapp' ),
        ),
        array(
            'title' => __( 'Elementor Integration', 'wp-cupon-whatsapp' ),
            'description' => __( 'Widgets drag-and-drop para Elementor con controles completos de personalización.', 'wp-cupon-whatsapp' ),
        ),
    );
}
