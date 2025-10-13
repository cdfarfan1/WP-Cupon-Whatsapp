<?php
/**
 * Database Status & Migration Verification Page
 *
 * Permite verificar el estado de la migración de la base de datos
 *
 * @package WP_Cupon_WhatsApp
 * @since 1.5.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registrar página de estado de base de datos
 */
function wpcw_register_database_status_page() {
    add_submenu_page(
        'wpcw-main-dashboard',
        'Estado de Base de Datos',
        '🔍 Estado BD',
        'manage_options',
        'wpcw-database-status',
        'wpcw_render_database_status_page'
    );
}
add_action( 'admin_menu', 'wpcw_register_database_status_page' );

/**
 * Verificar estado de la migración
 */
function wpcw_check_migration_status() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'wpcw_canjes';
    $status = array(
        'table_exists' => false,
        'migration_completed' => false,
        'total_columns' => 0,
        'required_columns_missing' => array(),
        'required_columns_present' => array(),
        'backup_exists' => false,
        'backup_name' => '',
        'total_records' => 0,
        'indexes' => array(),
        'migration_date' => '',
    );

    // 1. Verificar si la tabla existe
    $table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" );
    $status['table_exists'] = ( $table_exists === $table_name );

    if ( ! $status['table_exists'] ) {
        return $status;
    }

    // 2. Obtener columnas actuales
    $columns = $wpdb->get_results( "DESCRIBE {$table_name}" );
    $existing_columns = array();
    foreach ( $columns as $column ) {
        $existing_columns[] = $column->Field;
    }
    $status['total_columns'] = count( $existing_columns );

    // 3. Verificar columnas requeridas
    $required_columns = array(
        'id',
        'user_id',
        'coupon_id',
        'numero_canje',
        'token_confirmacion',
        'estado_canje',
        'fecha_solicitud_canje',
        'fecha_confirmacion_canje',
        'comercio_id',
        'whatsapp_url',
        'codigo_cupon_wc',
        'id_pedido_wc',
        'origen_canje',
        'notas_internas',
        'fecha_rechazo',
        'fecha_cancelacion',
        'created_at',
        'updated_at',
    );

    foreach ( $required_columns as $required_column ) {
        if ( in_array( $required_column, $existing_columns ) ) {
            $status['required_columns_present'][] = $required_column;
        } else {
            $status['required_columns_missing'][] = $required_column;
        }
    }

    // 4. Verificar si la migración está completa
    $status['migration_completed'] = empty( $status['required_columns_missing'] );

    // 5. Obtener fecha de migración
    $migration_date = get_option( 'wpcw_table_migration_completed' );
    if ( $migration_date ) {
        $status['migration_date'] = $migration_date;
    }

    // 6. Verificar backup
    $backup_name = get_option( 'wpcw_table_backup_name' );
    if ( $backup_name ) {
        $backup_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$backup_name}'" );
        $status['backup_exists'] = ( $backup_exists === $backup_name );
        $status['backup_name'] = $backup_name;
    }

    // 7. Contar registros
    $status['total_records'] = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );

    // 8. Verificar índices
    $indexes = $wpdb->get_results( "SHOW INDEX FROM {$table_name}" );
    $index_names = array();
    foreach ( $indexes as $index ) {
        if ( $index->Key_name !== 'PRIMARY' ) {
            $index_names[] = $index->Key_name;
        }
    }
    $status['indexes'] = array_unique( $index_names );

    return $status;
}

/**
 * Renderizar página de estado de base de datos
 */
function wpcw_render_database_status_page() {
    $status = wpcw_check_migration_status();

    ?>
    <div class="wrap">
        <h1>🔍 Estado de Base de Datos - WP Cupón WhatsApp</h1>
        <p class="description">Verificación del estado de la migración de la base de datos</p>

        <!-- Estado General -->
        <div class="wpcw-db-status-card" style="margin: 20px 0; padding: 20px; background: #fff; border-left: 5px solid <?php echo $status['migration_completed'] ? '#46b450' : '#dc3232'; ?>; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; gap: 20px;">
                <div style="font-size: 64px;">
                    <?php echo $status['migration_completed'] ? '✅' : '❌'; ?>
                </div>
                <div style="flex: 1;">
                    <h2 style="margin: 0 0 10px 0; font-size: 24px; color: <?php echo $status['migration_completed'] ? '#46b450' : '#dc3232'; ?>;">
                        <?php echo $status['migration_completed'] ? 'Migración Completada Correctamente' : 'Migración Pendiente'; ?>
                    </h2>
                    <p style="margin: 0; font-size: 16px; color: #666;">
                        <?php
                        if ( $status['migration_completed'] ) {
                            echo 'La base de datos tiene todas las columnas requeridas. El plugin funcionará correctamente.';
                            if ( $status['migration_date'] ) {
                                echo '<br><strong>Fecha de migración:</strong> ' . esc_html( $status['migration_date'] );
                            }
                        } else {
                            echo 'La base de datos necesita ser migrada. Faltan ' . count( $status['required_columns_missing'] ) . ' columnas.';
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Detalles de la Tabla -->
        <div class="wpcw-db-details" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;">

            <!-- Tabla -->
            <div class="wpcw-detail-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 15px 0; color: #0073aa; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">📊</span> Información de Tabla
                </h3>
                <table class="widefat">
                    <tbody>
                        <tr>
                            <td><strong>Tabla Existe</strong></td>
                            <td><?php echo $status['table_exists'] ? '✅ Sí' : '❌ No'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total Columnas</strong></td>
                            <td><?php echo esc_html( $status['total_columns'] ); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total Registros</strong></td>
                            <td><?php echo esc_html( $status['total_records'] ); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Índices Creados</strong></td>
                            <td><?php echo esc_html( count( $status['indexes'] ) ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Columnas -->
            <div class="wpcw-detail-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 15px 0; color: #46b450; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">✅</span> Columnas Presentes
                </h3>
                <div style="max-height: 300px; overflow-y: auto;">
                    <?php if ( ! empty( $status['required_columns_present'] ) ) : ?>
                        <ul style="margin: 0; padding-left: 20px; line-height: 1.8;">
                            <?php foreach ( $status['required_columns_present'] as $column ) : ?>
                                <li style="color: #46b450;">
                                    <code><?php echo esc_html( $column ); ?></code>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else : ?>
                        <p style="color: #dc3232;">Ninguna columna requerida presente</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Columnas Faltantes -->
            <?php if ( ! empty( $status['required_columns_missing'] ) ) : ?>
            <div class="wpcw-detail-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #dc3232;">
                <h3 style="margin: 0 0 15px 0; color: #dc3232; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">❌</span> Columnas Faltantes
                </h3>
                <ul style="margin: 0; padding-left: 20px; line-height: 1.8;">
                    <?php foreach ( $status['required_columns_missing'] as $column ) : ?>
                        <li style="color: #dc3232;">
                            <code><?php echo esc_html( $column ); ?></code>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Backup -->
            <?php if ( $status['backup_exists'] ) : ?>
            <div class="wpcw-detail-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 15px 0; color: #826eb4; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">💾</span> Backup Disponible
                </h3>
                <p style="margin: 0 0 10px 0;">
                    <strong>Nombre:</strong><br>
                    <code><?php echo esc_html( $status['backup_name'] ); ?></code>
                </p>
                <p style="margin: 0; color: #666; font-size: 13px;">
                    ℹ️ Puedes restaurar este backup desde phpMyAdmin si es necesario.
                </p>
            </div>
            <?php endif; ?>

            <!-- Índices -->
            <?php if ( ! empty( $status['indexes'] ) ) : ?>
            <div class="wpcw-detail-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 15px 0; color: #00a0d2; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">⚡</span> Índices
                </h3>
                <ul style="margin: 0; padding-left: 20px; line-height: 1.8;">
                    <?php foreach ( $status['indexes'] as $index ) : ?>
                        <li><code><?php echo esc_html( $index ); ?></code></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>

        <!-- Acciones -->
        <div class="wpcw-actions" style="margin: 30px 0; padding: 20px; background: #f0f6fc; border-radius: 8px;">
            <h3 style="margin: 0 0 15px 0;">Acciones Disponibles</h3>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <button type="button" class="button button-primary" onclick="location.reload();">
                    🔄 Refrescar Estado
                </button>

                <?php if ( ! $status['migration_completed'] ) : ?>
                <a href="<?php echo esc_url( WPCW_PLUGIN_URL . 'run-migration.php' ); ?>"
                   class="button button-primary"
                   target="_blank"
                   style="background: #00a32a; border-color: #00a32a;">
                    ▶️ Ejecutar Migración Ahora
                </a>
                <?php endif; ?>

                <a href="<?php echo esc_url( WPCW_PLUGIN_URL . 'check-database.php' ); ?>"
                   class="button button-secondary"
                   target="_blank">
                    📋 Ver Diagnóstico Completo
                </a>

                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpcw-main-dashboard' ) ); ?>"
                   class="button button-secondary">
                    ← Volver al Dashboard
                </a>
            </div>
        </div>

        <!-- SQL Info -->
        <?php if ( ! $status['migration_completed'] ) : ?>
        <div class="notice notice-warning" style="margin: 20px 0; padding: 15px;">
            <h3 style="margin: 0 0 10px 0;">⚠️ Migración Manual (Alternativa)</h3>
            <p>Si prefieres ejecutar la migración manualmente en phpMyAdmin:</p>
            <ol style="margin: 10px 0; padding-left: 20px;">
                <li>Ir a: <a href="http://localhost/phpmyadmin/" target="_blank">http://localhost/phpmyadmin/</a></li>
                <li>Seleccionar base de datos: <strong>tienda</strong></li>
                <li>Click en pestaña <strong>SQL</strong></li>
                <li>Copiar contenido de: <code>database/migration-update-canjes-table.sql</code></li>
                <li>Ejecutar</li>
            </ol>
        </div>
        <?php endif; ?>

        <!-- Success Message -->
        <?php if ( $status['migration_completed'] ) : ?>
        <div class="notice notice-success" style="margin: 20px 0; padding: 15px;">
            <h3 style="margin: 0 0 10px 0;">🎉 ¡Excelente! La Base de Datos está Correcta</h3>
            <p>
                Todas las columnas requeridas están presentes. El plugin funcionará al 100%.
                Puedes continuar usando WP Cupón WhatsApp sin problemas.
            </p>
        </div>
        <?php endif; ?>
    </div>

    <style>
    .wpcw-db-status-card {
        animation: fadeIn 0.5s;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .wpcw-detail-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .wpcw-detail-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    }
    </style>
    <?php
}

/**
 * AJAX handler para verificar migración
 */
function wpcw_ajax_check_migration() {
    check_ajax_referer( 'wpcw_check_migration', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Permisos insuficientes' ) );
    }

    $status = wpcw_check_migration_status();
    wp_send_json_success( $status );
}
add_action( 'wp_ajax_wpcw_check_migration', 'wpcw_ajax_check_migration' );
