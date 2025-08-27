<?php
/**
 * Descargador de archivo específico faltante
 * Descarga solo wp-admin/load.php desde GitHub
 */

// Configuración
$wordpress_path = 'C:/xampp/htdocs/webstore';
$missing_file = 'wp-admin/load.php';
$github_url = 'https://raw.githubusercontent.com/WordPress/WordPress/trunk/wp-admin/load.php';

echo "=== DESCARGADOR DE ARCHIVO FALTANTE ===\n\n";

echo "📁 Archivo a descargar: $missing_file\n";
echo "🌐 Fuente: GitHub WordPress\n";
echo "📂 Destino: $wordpress_path/$missing_file\n\n";

// Verificar que el directorio de destino existe
$target_dir = dirname($wordpress_path . '/' . $missing_file);
if (!is_dir($target_dir)) {
    echo "❌ ERROR: El directorio de destino no existe: $target_dir\n";
    exit(1);
}

// Crear directorio si no existe
if (!is_dir($target_dir)) {
    if (!mkdir($target_dir, 0755, true)) {
        echo "❌ ERROR: No se pudo crear el directorio: $target_dir\n";
        exit(1);
    }
}

// Descargar el archivo
echo "=== DESCARGANDO ARCHIVO ===\n";
echo "🌐 Descargando desde: $github_url\n";

$context = stream_context_create([
    'http' => [
        'timeout' => 30,
        'user_agent' => 'WordPress-File-Downloader/1.0'
    ]
]);

$file_content = file_get_contents($github_url, false, $context);
if ($file_content === false) {
    echo "❌ ERROR: No se pudo descargar el archivo\n";
    exit(1);
}

$file_size = strlen($file_content);
echo "✅ Archivo descargado correctamente ($file_size bytes)\n";

// Guardar el archivo
$target_file = $wordpress_path . '/' . $missing_file;
if (file_put_contents($target_file, $file_content) === false) {
    echo "❌ ERROR: No se pudo guardar el archivo en: $target_file\n";
    exit(1);
}

echo "✅ Archivo guardado correctamente en: $target_file\n\n";

// Verificar que el archivo se guardó
if (file_exists($target_file)) {
    $actual_size = filesize($target_file);
    echo "✅ Verificación exitosa:\n";
    echo "   - Archivo existe: Sí\n";
    echo "   - Tamaño: $actual_size bytes\n";
    echo "   - Permisos: " . substr(sprintf('%o', fileperms($target_file)), -4) . "\n";
} else {
    echo "❌ ERROR: El archivo no se guardó correctamente\n";
    exit(1);
}

echo "\n=== ARCHIVO RESTAURADO ===\n";
echo "🎉 El archivo $missing_file ha sido restaurado exitosamente.\n";
echo "\n=== PRÓXIMOS PASOS ===\n";
echo "1. Ejecuta 'verificar-wordpress-completo.php' para confirmar\n";
echo "2. Intenta acceder a https://localhost/webstore/wp-admin/\n";
echo "3. Si funciona, el error HTTP 500 debería estar resuelto\n";

echo "\n🎯 ¡Problema resuelto!\n";
?>
