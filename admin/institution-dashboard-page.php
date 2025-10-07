<?php
/**
 * WPCW - Institution Manager Dashboard Page
 *
 * This file renders the dashboard for the Institution Manager role.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Renders the content of the Institution Manager dashboard.
 */
function wpcw_render_institution_dashboard_page() {
    ?>
    <div class="wrap wpcw-dashboard-wrap">
        <h1><span class="dashicons dashicons-building"></span> <?php _e( 'Panel de Gestión de Institución', 'wp-cupon-whatsapp' ); ?></h1>
        <p><?php _e( 'Desde aquí puede gestionar sus negocios adheridos, supervisar campañas y analizar estadísticas.', 'wp-cupon-whatsapp' ); ?></p>

        <?php
        // Aquí es donde cargaremos los componentes del dashboard definidos en la Fase 1.
        // Por ahora, dejaremos marcadores de posición.
        ?>

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <!-- Columna Principal -->
                <div id="post-body-content">
                    <div class="meta-box-sortables ui-sortable">
                        
                        <!-- Marcador para Métricas Clave -->
                        <div class="postbox">
                            <h2 class="hndle"><span><?php _e( 'Vista General', 'wp-cupon-whatsapp' ); ?></span></h2>
                            <div class="inside">
                                <p><?php _e( 'Aquí se mostrarán las métricas clave: total de negocios, canjes del mes, etc.', 'wp-cupon-whatsapp' ); ?></p>
                            </div>
                        </div>

                        <!-- Marcador para Lista de Negocios -->
                        <div class="postbox">
                            <h2 class="hndle"><span><?php _e( 'Mis Negocios', 'wp-cupon-whatsapp' ); ?></span></h2>
                            <div class="inside">
                                <p><?php _e( 'Aquí se mostrará la tabla con los negocios adheridos y la opción de invitar nuevos.', 'wp-cupon-whatsapp' ); ?></p>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Columna Lateral -->
                <div id="postbox-container-1" class="postbox-container">
                    <div class="meta-box-sortables">

                        <!-- Marcador para Campañas -->
                        <div class="postbox">
                            <h2 class="hndle"><span><?php _e( 'Campañas Institucionales', 'wp-cupon-whatsapp' ); ?></span></h2>
                            <div class="inside">
                                <p><?php _e( 'Aquí se mostrará el seguimiento de adhesión a las campañas.', 'wp-cupon-whatsapp' ); ?></p>
                            </div>
                        </div>

                        <!-- Marcador para Acciones Rápidas -->
                        <div class="postbox">
                            <h2 class="hndle"><span><?php _e( 'Acciones Rápidas', 'wp-cupon-whatsapp' ); ?></span></h2>
                            <div class="inside">
                                <p><a href="#" class="button button-primary"><?php _e( 'Invitar Negocio', 'wp-cupon-whatsapp' ); ?></a></p>
                                <p><a href="#" class="button button-secondary"><?php _e( 'Exportar Reporte', 'wp-cupon-whatsapp' ); ?></a></p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <br class="clear">
        </div>
    </div>
    <?php
}

/**
 * Adds the Institution Dashboard submenu page, but only for users with the correct capability.
 */
function wpcw_add_institution_dashboard_menu() {
    // Asumimos que el rol 'institution_manager' tiene esta capability única.
    // Esto lo definiremos formalmente al crear los roles.
    $capability = 'manage_institution';

    if ( current_user_can( $capability ) ) {
        add_submenu_page(
            'wpcw-main-dashboard',                  // Slug del menú padre
            __( 'Panel de Institución', 'wp-cupon-whatsapp' ), // Título de la página
            __( 'Panel de Institución', 'wp-cupon-whatsapp' ), // Título del menú
            $capability,                            // Capacidad requerida
            'wpcw-institution-dashboard',           // Slug del menú
            'wpcw_render_institution_dashboard_page', // Callback para renderizar
            1 // Posición dentro del submenú
        );
    }
}
add_action( 'admin_menu', 'wpcw_add_institution_dashboard_menu', 10 ); // Prioridad 10 para que se ejecute después del menú principal
