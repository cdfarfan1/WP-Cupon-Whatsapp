<?php
/**
 * Admin Notice for Database Migration
 *
 * Shows a dismissible notice when database migration is needed
 *
 * @package WP_Cupon_WhatsApp
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Check if migration is needed
 */
function wpcw_needs_migration() {
    global $wpdb;

    // Check if migration already completed
    if ( get_option( 'wpcw_table_migration_completed' ) ) {
        return false;
    }

    // Check if table exists
    $table_name = $wpdb->prefix . 'wpcw_canjes';
    $table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" );

    if ( ! $table_exists ) {
        return false;
    }

    // Check if critical columns exist
    $columns = $wpdb->get_results( "DESCRIBE {$table_name}" );
    $existing_columns = array();

    foreach ( $columns as $column ) {
        $existing_columns[] = $column->Field;
    }

    // Check for critical columns
    $critical_columns = array( 'estado_canje', 'fecha_solicitud_canje' );

    foreach ( $critical_columns as $col ) {
        if ( ! in_array( $col, $existing_columns ) ) {
            return true; // Migration needed
        }
    }

    return false; // All good
}

/**
 * Display admin notice for migration
 */
function wpcw_migration_admin_notice() {
    // Only show to administrators
    if ( ! current_user_can( 'administrator' ) ) {
        return;
    }

    // Only show if migration is needed
    if ( ! wpcw_needs_migration() ) {
        return;
    }

    // Check if notice was dismissed
    if ( get_transient( 'wpcw_migration_notice_dismissed' ) ) {
        return;
    }

    $migration_url = WPCW_PLUGIN_URL . 'run-migration.php';
    $check_url = WPCW_PLUGIN_URL . 'check-database.php';

    ?>
    <div class="notice notice-error is-dismissible wpcw-migration-notice" style="border-left-width: 8px; padding: 20px; margin: 20px 20px 20px 0;">
        <div style="display: flex; align-items: flex-start; gap: 20px;">
            <div style="font-size: 48px; line-height: 1;">🚨</div>
            <div style="flex: 1;">
                <h2 style="margin: 0 0 15px 0; font-size: 24px; color: #dc3232;">
                    🔧 ACCIÓN URGENTE REQUERIDA - Migración de Base de Datos
                </h2>
                <p style="font-size: 16px; margin: 10px 0;">
                    <strong style="color: #dc3232;">La base de datos DEBE ser actualizada para que el plugin funcione correctamente.</strong>
                </p>
                <p style="font-size: 14px; margin: 10px 0; color: #666;">
                    Tu tabla <code style="background: #f0f0f1; padding: 2px 6px; border-radius: 3px;">wp_wpcw_canjes</code>
                    tiene el esquema antiguo. Los errores que ves se deben a esto.
                </p>

                <div style="background: #f0f6fc; border: 2px solid #0073aa; border-radius: 8px; padding: 20px; margin: 20px 0;">
                    <h3 style="margin: 0 0 15px 0; color: #0073aa; font-size: 18px;">
                        ⚡ Opción 1: Migración Automática (RECOMENDADA - 1 click)
                    </h3>
                    <p style="margin: 0 0 15px 0; font-size: 14px;">
                        Click en el botón verde para ejecutar la migración automáticamente.
                        Se creará un backup antes de cualquier cambio.
                    </p>
                    <p>
                        <a href="<?php echo esc_url( $migration_url ); ?>"
                           class="button button-primary button-hero"
                           target="_blank"
                           style="background: #00a32a; border-color: #00a32a; font-size: 16px; padding: 10px 20px; height: auto; text-shadow: none; box-shadow: 0 3px 8px rgba(0,163,42,0.3);">
                            ▶️ EJECUTAR MIGRACIÓN AHORA (1-2 minutos)
                        </a>
                        <a href="<?php echo esc_url( $check_url ); ?>"
                           class="button button-secondary"
                           target="_blank"
                           style="margin-left: 15px; font-size: 14px; height: auto; padding: 10px 16px;">
                            🔍 Verificar Estado de BD Primero
                        </a>
                    </p>
                </div>

                <details style="margin: 20px 0; padding: 15px; background: #fff; border: 1px solid #ddd; border-radius: 5px;">
                    <summary style="cursor: pointer; font-weight: 600; font-size: 15px; padding: 5px 0;">
                        📋 Opción 2: Migración Manual (phpMyAdmin) - Click para ver instrucciones
                    </summary>
                    <ol style="margin: 15px 0 0 20px; line-height: 1.8;">
                        <li>Ir a: <a href="http://localhost/phpmyadmin/" target="_blank" style="font-weight: 600;">http://localhost/phpmyadmin/</a></li>
                        <li>Seleccionar base de datos: <strong>tienda</strong></li>
                        <li>Click en pestaña <strong>SQL</strong> (arriba)</li>
                        <li>Abrir archivo: <code>wp-content/plugins/WP-Cupon-Whatsapp/database/migration-update-canjes-table.sql</code></li>
                        <li>Copiar TODO el contenido (Ctrl+A, Ctrl+C)</li>
                        <li>Pegar en phpMyAdmin (Ctrl+V)</li>
                        <li>Click en <strong>Continuar</strong></li>
                    </ol>
                </details>

                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-top: 20px; border-radius: 4px;">
                    <p style="margin: 0; font-size: 14px;">
                        <strong>⚠️ IMPORTANTE:</strong> Se creará un backup automático (<code>wp_wpcw_canjes_backup_YYYYMMDD_HHMMSS</code>)
                        antes de cualquier cambio. Si algo falla, podrás restaurar en segundos. Es 100% seguro.
                    </p>
                </div>

                <div style="background: #e8f4f8; border-left: 4px solid #00a0d2; padding: 15px; margin-top: 15px; border-radius: 4px;">
                    <p style="margin: 0; font-size: 13px; color: #555;">
                        <strong>ℹ️ ¿Por qué veo este aviso?</strong><br>
                        El plugin se actualizó recientemente y la base de datos necesita actualizarse también.
                        Esto agregará 13 columnas nuevas necesarias para las estadísticas, canjes y funcionalidad completa.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <style>
    .wpcw-migration-notice {
        animation: wpcw-pulse 2s infinite;
    }
    @keyframes wpcw-pulse {
        0%, 100% { border-left-color: #dc3232; }
        50% { border-left-color: #ff6b6b; }
    }
    .wpcw-migration-notice .button-hero:hover {
        background: #008a24 !important;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,163,42,0.4) !important;
    }
    </style>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        $('.wpcw-migration-notice').on('click', '.notice-dismiss', function() {
            $.post(ajaxurl, {
                action: 'wpcw_dismiss_migration_notice'
            });
        });

        // Auto-scroll to notice on page load
        if ($('.wpcw-migration-notice').length) {
            $('html, body').animate({
                scrollTop: $('.wpcw-migration-notice').offset().top - 50
            }, 500);
        }
    });
    </script>
    <?php
}
add_action( 'admin_notices', 'wpcw_migration_admin_notice' );

/**
 * Handle notice dismissal
 */
function wpcw_dismiss_migration_notice_ajax() {
    set_transient( 'wpcw_migration_notice_dismissed', true, HOUR_IN_SECONDS );
    wp_die();
}
add_action( 'wp_ajax_wpcw_dismiss_migration_notice', 'wpcw_dismiss_migration_notice_ajax' );
