<?php
/**
 * MIGRACIÓN MANUAL DE BASE DE DATOS
 * EJECUTAR: http://localhost/wp-content/plugins/WP-Cupon-Whatsapp/run-migration.php
 *
 * IMPORTANTE: Borra este archivo después de ejecutarlo (seguridad)
 */

// Load WordPress
require_once('../../../wp-load.php');

// Verificar que sea administrador
if (!current_user_can('administrator')) {
    die('❌ Error: Debes ser administrador para ejecutar este script.');
}

global $wpdb;

echo "<h1>🔧 Migración Manual de Base de Datos</h1>";
echo "<p><em>WP Cupón WhatsApp - Actualización de Esquema</em></p>";
echo "<hr>";

$table_name = $wpdb->prefix . 'wpcw_canjes';

// Verificar que la tabla exista
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
if (!$table_exists) {
    die("❌ Error: La tabla '{$table_name}' no existe. Activa el plugin primero.");
}

// Obtener columnas actuales
$columns = $wpdb->get_results("DESCRIBE {$table_name}");
$existing_columns = array();
foreach ($columns as $column) {
    $existing_columns[] = $column->Field;
}

echo "<h2>📋 Estado Actual</h2>";
echo "<p>Tabla: <strong>{$table_name}</strong></p>";
echo "<p>Columnas actuales: <strong>" . count($existing_columns) . "</strong></p>";

// Verificar si ya está migrado
if (in_array('estado_canje', $existing_columns) && in_array('fecha_solicitud_canje', $existing_columns)) {
    echo "<p style='color: green;'>✅ <strong>La migración ya fue ejecutada anteriormente.</strong></p>";
    echo "<p>Columnas críticas detectadas:</p>";
    echo "<ul>";
    echo "<li>✅ estado_canje</li>";
    echo "<li>✅ fecha_solicitud_canje</li>";
    echo "<li>✅ fecha_confirmacion_canje</li>";
    echo "</ul>";
    echo "<hr>";
    echo "<p><a href='" . admin_url('admin.php?page=wp-cupon-whatsapp') . "'>← Volver al Dashboard</a></p>";
    exit;
}

echo "<hr>";
echo "<h2>🚀 Ejecutando Migración...</h2>";

try {
    // 1. Crear backup
    echo "<p>1️⃣ Creando backup de seguridad...</p>";
    $row_count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");

    if ($row_count > 0) {
        $backup_table = $table_name . '_backup_' . date('Ymd_His');
        $wpdb->query("CREATE TABLE IF NOT EXISTS {$backup_table} LIKE {$table_name}");
        $wpdb->query("INSERT INTO {$backup_table} SELECT * FROM {$table_name}");
        echo "<p style='color: green;'>✅ Backup creado: <strong>{$backup_table}</strong> ({$row_count} registros)</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Tabla vacía, no se requiere backup</p>";
    }

    // 2. Construir ALTER TABLE con todas las columnas
    echo "<p>2️⃣ Agregando columnas faltantes...</p>";

    $column_definitions = array(
        'coupon_id'                => "coupon_id bigint(20) UNSIGNED NULL",
        'numero_canje'             => "numero_canje varchar(20) NULL",
        'token_confirmacion'       => "token_confirmacion varchar(64) NULL",
        'estado_canje'             => "estado_canje varchar(50) NOT NULL DEFAULT 'pendiente_confirmacion'",
        'fecha_solicitud_canje'    => "fecha_solicitud_canje datetime NULL",
        'fecha_confirmacion_canje' => "fecha_confirmacion_canje datetime NULL",
        'comercio_id'              => "comercio_id bigint(20) UNSIGNED NULL",
        'whatsapp_url'             => "whatsapp_url text NULL",
        'codigo_cupon_wc'          => "codigo_cupon_wc varchar(100) NULL",
        'id_pedido_wc'             => "id_pedido_wc bigint(20) UNSIGNED NULL",
        'origen_canje'             => "origen_canje varchar(50) DEFAULT 'webapp'",
        'notas_internas'           => "notas_internas text NULL",
        'fecha_rechazo'            => "fecha_rechazo datetime NULL",
        'fecha_cancelacion'        => "fecha_cancelacion datetime NULL",
        'created_at'               => "created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP",
        'updated_at'               => "updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
    );

    $alter_statements = array();
    foreach ($column_definitions as $column_name => $definition) {
        if (!in_array($column_name, $existing_columns)) {
            $alter_statements[] = "ADD COLUMN {$definition}";
        }
    }

    if (!empty($alter_statements)) {
        $sql = "ALTER TABLE {$table_name} " . implode(', ', $alter_statements);
        $result = $wpdb->query($sql);

        if ($result === false) {
            throw new Exception("Error al agregar columnas: " . $wpdb->last_error);
        }

        echo "<p style='color: green;'>✅ Agregadas <strong>" . count($alter_statements) . "</strong> columnas nuevas</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Todas las columnas ya existen</p>";
    }

    // 3. Migrar datos antiguos
    echo "<p>3️⃣ Migrando datos antiguos...</p>";

    if (in_array('business_id', $existing_columns) && in_array('redeemed_at', $existing_columns)) {
        $wpdb->query("
            UPDATE {$table_name} SET
                comercio_id = business_id,
                fecha_solicitud_canje = redeemed_at,
                fecha_confirmacion_canje = redeemed_at,
                estado_canje = 'confirmado_por_negocio'
            WHERE business_id IS NOT NULL AND redeemed_at IS NOT NULL
        ");
        echo "<p style='color: green;'>✅ Datos migrados de columnas antiguas</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ No hay columnas antiguas para migrar</p>";
    }

    // 4. Crear índices
    echo "<p>4️⃣ Creando índices para mejor rendimiento...</p>";

    $indexes = array(
        'idx_user_id'         => 'user_id',
        'idx_coupon_id'       => 'coupon_id',
        'idx_numero_canje'    => 'numero_canje',
        'idx_estado_canje'    => 'estado_canje',
        'idx_fecha_solicitud' => 'fecha_solicitud_canje',
        'idx_comercio_id'     => 'comercio_id',
    );

    $index_statements = array();
    foreach ($indexes as $index_name => $column_name) {
        $index_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = %s
             AND INDEX_NAME = %s",
            $table_name,
            $index_name
        ));

        if (!$index_exists) {
            $index_statements[] = "ADD INDEX {$index_name} ({$column_name})";
        }
    }

    if (!empty($index_statements)) {
        $sql = "ALTER TABLE {$table_name} " . implode(', ', $index_statements);
        $wpdb->query($sql);
        echo "<p style='color: green;'>✅ Creados <strong>" . count($index_statements) . "</strong> índices</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Todos los índices ya existen</p>";
    }

    // 5. Marcar migración como completada
    update_option('wpcw_table_migration_completed', current_time('mysql'));
    if (isset($backup_table)) {
        update_option('wpcw_table_backup_name', $backup_table);
    }

    echo "<hr>";
    echo "<h2 style='color: green;'>✅ MIGRACIÓN COMPLETADA EXITOSAMENTE</h2>";
    echo "<p>La base de datos ha sido actualizada correctamente.</p>";

    // Verificación final
    $columns_after = $wpdb->get_results("DESCRIBE {$table_name}");
    echo "<p><strong>Columnas totales ahora:</strong> " . count($columns_after) . "</p>";

    echo "<h3>Próximos Pasos:</h3>";
    echo "<ol>";
    echo "<li>✅ Ve al <a href='" . admin_url('admin.php?page=wp-cupon-whatsapp') . "'>Dashboard del Plugin</a></li>";
    echo "<li>✅ Verifica que ya no hay errores de base de datos</li>";
    echo "<li>⚠️ <strong>BORRA este archivo (run-migration.php)</strong> por seguridad</li>";
    echo "</ol>";

    if (isset($backup_table)) {
        echo "<h3>🔄 Rollback (si algo falla):</h3>";
        echo "<p>Si algo sale mal, ejecuta esto en phpMyAdmin:</p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
        echo "DROP TABLE {$table_name};\n";
        echo "RENAME TABLE {$backup_table} TO {$table_name};";
        echo "</pre>";
    }

} catch (Exception $e) {
    echo "<hr>";
    echo "<h2 style='color: red;'>❌ ERROR EN LA MIGRACIÓN</h2>";
    echo "<p><strong>Mensaje de error:</strong> " . $e->getMessage() . "</p>";

    if (isset($backup_table)) {
        echo "<h3>🔄 Restaurar Backup:</h3>";
        echo "<p>Ejecuta esto en phpMyAdmin para restaurar:</p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
        echo "DROP TABLE {$table_name};\n";
        echo "RENAME TABLE {$backup_table} TO {$table_name};";
        echo "</pre>";
    }

    echo "<p>Si el error persiste, contacta al equipo de desarrollo.</p>";
}

echo "<hr>";
echo "<p><em>Migración ejecutada el " . date('Y-m-d H:i:s') . "</em></p>";
?>
