<?php
/**
 * Script de diagnóstico - Verificar estado de la base de datos
 * EJECUTAR: Ir a http://localhost/wp-content/plugins/WP-Cupon-Whatsapp/check-database.php
 */

// Load WordPress
require_once('../../../wp-load.php');

global $wpdb;

echo "<h1>Diagnóstico de Base de Datos - WP Cupón WhatsApp</h1>";
echo "<hr>";

$table_name = $wpdb->prefix . 'wpcw_canjes';

// 1. Verificar si la tabla existe
echo "<h2>1. ¿Existe la tabla?</h2>";
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
if ($table_exists) {
    echo "✅ SÍ - La tabla '{$table_name}' existe<br>";
} else {
    echo "❌ NO - La tabla '{$table_name}' NO existe<br>";
    die("Error: La tabla no existe. Debes activar el plugin primero.");
}

echo "<hr>";

// 2. Ver estructura actual
echo "<h2>2. Estructura Actual de la Tabla</h2>";
$columns = $wpdb->get_results("DESCRIBE {$table_name}");
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
foreach ($columns as $column) {
    echo "<tr>";
    echo "<td><strong>{$column->Field}</strong></td>";
    echo "<td>{$column->Type}</td>";
    echo "<td>{$column->Null}</td>";
    echo "<td>{$column->Key}</td>";
    echo "<td>{$column->Default}</td>";
    echo "<td>{$column->Extra}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p><strong>Total de columnas:</strong> " . count($columns) . "</p>";

echo "<hr>";

// 3. Verificar columnas críticas
echo "<h2>3. Verificación de Columnas Críticas</h2>";
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
    'created_at',
    'updated_at'
);

$existing_columns = array();
foreach ($columns as $column) {
    $existing_columns[] = $column->Field;
}

echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Columna Requerida</th><th>Estado</th></tr>";
foreach ($required_columns as $req_col) {
    $exists = in_array($req_col, $existing_columns);
    echo "<tr>";
    echo "<td>{$req_col}</td>";
    echo "<td>" . ($exists ? "✅ Existe" : "❌ FALTA") . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";

// 4. Verificar datos
echo "<h2>4. Datos en la Tabla</h2>";
$row_count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
echo "<p><strong>Total de registros:</strong> {$row_count}</p>";

if ($row_count > 0) {
    echo "<p>⚠️ <strong>IMPORTANTE:</strong> La tabla contiene {$row_count} registros. Se creará backup antes de migrar.</p>";
} else {
    echo "<p>✅ La tabla está vacía. Migración será rápida (sin backup necesario).</p>";
}

echo "<hr>";

// 5. Verificar opciones de migración
echo "<h2>5. Estado de Migración</h2>";
$migration_completed = get_option('wpcw_table_migration_completed');
$backup_name = get_option('wpcw_table_backup_name');

if ($migration_completed) {
    echo "✅ Migración marcada como completada: {$migration_completed}<br>";
    if ($backup_name) {
        echo "📦 Backup creado: {$backup_name}<br>";
    }
} else {
    echo "❌ Migración NO ejecutada<br>";
}

echo "<hr>";

// 6. Diagnóstico final
echo "<h2>6. Diagnóstico Final</h2>";

$missing_columns = array();
foreach ($required_columns as $req_col) {
    if (!in_array($req_col, $existing_columns)) {
        $missing_columns[] = $req_col;
    }
}

if (empty($missing_columns)) {
    echo "✅ <strong style='color: green;'>TODO CORRECTO</strong> - La tabla tiene todas las columnas necesarias<br>";
} else {
    echo "❌ <strong style='color: red;'>MIGRACIÓN NECESARIA</strong><br>";
    echo "<p>Faltan " . count($missing_columns) . " columnas:</p>";
    echo "<ul>";
    foreach ($missing_columns as $col) {
        echo "<li><strong>{$col}</strong></li>";
    }
    echo "</ul>";

    echo "<h3>Solución:</h3>";
    echo "<p>Ve a: <strong>http://localhost/phpmyadmin/</strong></p>";
    echo "<p>1. Selecciona base de datos: <strong>tienda</strong></p>";
    echo "<p>2. Click en pestaña <strong>SQL</strong></p>";
    echo "<p>3. Copia y pega el contenido del archivo: <strong>database/migration-update-canjes-table.sql</strong></p>";
    echo "<p>4. Click en <strong>Continuar</strong></p>";
}

echo "<hr>";
echo "<p><em>Script generado el " . date('Y-m-d H:i:s') . "</em></p>";
?>
