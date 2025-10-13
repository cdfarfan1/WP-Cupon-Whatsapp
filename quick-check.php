<?php
/**
 * Quick Database Check
 *
 * Ejecuta este script en el navegador para verificar rápidamente el estado de la migración
 * URL: http://localhost/tienda/wp-content/plugins/wp-cupon-whatsapp/quick-check.php
 */

// Cargar WordPress
require_once __DIR__ . '/../../../wp-load.php';

// Verificar permisos
if ( ! current_user_can( 'manage_options' ) ) {
    die( '❌ No tienes permisos para ver esta página' );
}

global $wpdb;
$table_name = $wpdb->prefix . 'wpcw_canjes';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación Rápida BD - WP Cupón WhatsApp</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #f0f0f1;
        }
        .status-card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .success {
            border-left: 5px solid #46b450;
        }
        .error {
            border-left: 5px solid #dc3232;
        }
        .emoji {
            font-size: 64px;
            line-height: 1;
        }
        .status-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        h1 {
            margin: 0;
            font-size: 28px;
        }
        h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .success h2 {
            color: #46b450;
        }
        .error h2 {
            color: #dc3232;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f9f9f9;
            font-weight: 600;
        }
        .column-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin: 20px 0;
        }
        .column-item {
            padding: 8px 12px;
            background: #f0f0f1;
            border-radius: 4px;
            font-family: monospace;
            font-size: 13px;
        }
        .column-present {
            background: #d4edda;
            color: #155724;
        }
        .column-missing {
            background: #f8d7da;
            color: #721c24;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #2271b1;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
            margin-top: 10px;
        }
        .button:hover {
            background: #135e96;
        }
        .button-success {
            background: #00a32a;
        }
        .button-success:hover {
            background: #008a20;
        }
    </style>
</head>
<body>
    <h1>🔍 Verificación Rápida de Base de Datos</h1>

    <?php
    // Verificar tabla existe
    $table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) === $table_name;

    if ( ! $table_exists ) {
        echo '<div class="status-card error">';
        echo '<div class="status-header">';
        echo '<div class="emoji">❌</div>';
        echo '<div>';
        echo '<h2>Tabla No Existe</h2>';
        echo '<p>La tabla <code>' . esc_html( $table_name ) . '</code> no existe en la base de datos.</p>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        exit;
    }

    // Obtener columnas actuales
    $columns = $wpdb->get_results( "DESCRIBE {$table_name}" );
    $existing_columns = array();
    foreach ( $columns as $column ) {
        $existing_columns[] = $column->Field;
    }

    // Columnas requeridas
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

    // Verificar cuáles faltan
    $missing_columns = array();
    $present_columns = array();

    foreach ( $required_columns as $req_col ) {
        if ( in_array( $req_col, $existing_columns ) ) {
            $present_columns[] = $req_col;
        } else {
            $missing_columns[] = $req_col;
        }
    }

    $migration_completed = empty( $missing_columns );

    // Contar registros
    $total_records = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );

    // Obtener índices
    $indexes = $wpdb->get_results( "SHOW INDEX FROM {$table_name}" );
    $index_names = array();
    foreach ( $indexes as $index ) {
        if ( $index->Key_name !== 'PRIMARY' ) {
            $index_names[] = $index->Key_name;
        }
    }
    $index_names = array_unique( $index_names );

    // Fecha de migración
    $migration_date = get_option( 'wpcw_table_migration_completed' );

    // Backup
    $backup_name = get_option( 'wpcw_table_backup_name' );
    $backup_exists = false;
    if ( $backup_name ) {
        $backup_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$backup_name}'" ) === $backup_name;
    }
    ?>

    <!-- Estado General -->
    <div class="status-card <?php echo $migration_completed ? 'success' : 'error'; ?>">
        <div class="status-header">
            <div class="emoji"><?php echo $migration_completed ? '✅' : '❌'; ?></div>
            <div>
                <h2><?php echo $migration_completed ? 'Migración Completada' : 'Migración Pendiente'; ?></h2>
                <p>
                    <?php
                    if ( $migration_completed ) {
                        echo 'La base de datos tiene todas las columnas requeridas. El plugin funcionará correctamente.';
                        if ( $migration_date ) {
                            echo '<br><strong>Fecha de migración:</strong> ' . esc_html( $migration_date );
                        }
                    } else {
                        echo 'La base de datos necesita ser migrada. Faltan <strong>' . count( $missing_columns ) . '</strong> columnas de <strong>' . count( $required_columns ) . '</strong> requeridas.';
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Información General -->
    <div class="status-card">
        <h2>📊 Información General</h2>
        <table>
            <tr>
                <th>Concepto</th>
                <th>Valor</th>
            </tr>
            <tr>
                <td>Tabla</td>
                <td><code><?php echo esc_html( $table_name ); ?></code></td>
            </tr>
            <tr>
                <td>Total de Columnas</td>
                <td><?php echo count( $existing_columns ); ?></td>
            </tr>
            <tr>
                <td>Columnas Presentes</td>
                <td><strong style="color: #46b450;"><?php echo count( $present_columns ); ?></strong> de <?php echo count( $required_columns ); ?></td>
            </tr>
            <tr>
                <td>Columnas Faltantes</td>
                <td><strong style="color: #dc3232;"><?php echo count( $missing_columns ); ?></strong></td>
            </tr>
            <tr>
                <td>Total de Registros</td>
                <td><?php echo esc_html( $total_records ); ?></td>
            </tr>
            <tr>
                <td>Índices Creados</td>
                <td><?php echo count( $index_names ); ?></td>
            </tr>
            <?php if ( $backup_exists ) : ?>
            <tr>
                <td>Backup Disponible</td>
                <td>✅ <code><?php echo esc_html( $backup_name ); ?></code></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Columnas Presentes -->
    <div class="status-card">
        <h2>✅ Columnas Presentes (<?php echo count( $present_columns ); ?>)</h2>
        <div class="column-list">
            <?php foreach ( $present_columns as $col ) : ?>
                <div class="column-item column-present">✓ <?php echo esc_html( $col ); ?></div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ( ! empty( $missing_columns ) ) : ?>
    <!-- Columnas Faltantes -->
    <div class="status-card error">
        <h2>❌ Columnas Faltantes (<?php echo count( $missing_columns ); ?>)</h2>
        <div class="column-list">
            <?php foreach ( $missing_columns as $col ) : ?>
                <div class="column-item column-missing">✗ <?php echo esc_html( $col ); ?></div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ( ! empty( $index_names ) ) : ?>
    <!-- Índices -->
    <div class="status-card">
        <h2>⚡ Índices (<?php echo count( $index_names ); ?>)</h2>
        <div class="column-list">
            <?php foreach ( $index_names as $idx ) : ?>
                <div class="column-item column-present"><?php echo esc_html( $idx ); ?></div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Acciones -->
    <div class="status-card">
        <h2>Acciones Disponibles</h2>
        <a href="javascript:location.reload();" class="button">🔄 Refrescar</a>

        <?php if ( ! $migration_completed ) : ?>
        <a href="<?php echo WPCW_PLUGIN_URL . 'run-migration.php'; ?>" class="button button-success" target="_blank">
            ▶️ Ejecutar Migración
        </a>
        <?php endif; ?>

        <a href="<?php echo admin_url( 'admin.php?page=wpcw-database-status' ); ?>" class="button">
            🔍 Ver en WordPress Admin
        </a>

        <a href="<?php echo admin_url( 'admin.php?page=wpcw-main-dashboard' ); ?>" class="button">
            📊 Ir al Dashboard
        </a>
    </div>

    <?php if ( $migration_completed ) : ?>
    <div class="status-card success">
        <h2>🎉 ¡Todo Listo!</h2>
        <p>
            La base de datos está correctamente configurada. Puedes usar todas las funcionalidades de WP Cupón WhatsApp sin problemas.
        </p>
        <p>
            <strong>Siguiente paso:</strong> Puedes borrar estos archivos de verificación por seguridad:
        </p>
        <ul>
            <li><code>quick-check.php</code> (este archivo)</li>
            <li><code>run-migration.php</code></li>
            <li><code>check-database.php</code></li>
        </ul>
    </div>
    <?php endif; ?>

</body>
</html>
