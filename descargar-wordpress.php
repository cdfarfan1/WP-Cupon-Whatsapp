<?php
/**
 * Descargador automático de WordPress
 * Descarga la última versión y extrae archivos faltantes
 */

// Configuración
$wordpress_path = 'C:/xampp/htdocs/webstore';
$wordpress_url = 'https://wordpress.org/latest.zip';
$temp_dir = __DIR__ . '/temp-wordpress';
$backup_dir = __DIR__ . '/backup-wordpress-' . date('Y-m-d-H-i-s');

echo "=== DESCARGADOR AUTOMÁTICO DE WORDPRESS ===\n\n";

// Verificar que el directorio existe
if (!is_dir($wordpress_path)) {
    echo "❌ ERROR: El directorio $wordpress_path no existe\n";
    exit(1);
}

// Crear directorio temporal
if (!is_dir($temp_dir)) {
    if (!mkdir($temp_dir, 0755, true)) {
        echo "❌ ERROR: No se pudo crear el directorio temporal $temp_dir\n";
        exit(1);
    }
}

// Crear directorio de backup
if (!is_dir($backup_dir)) {
    if (!mkdir($backup_dir, 0755, true)) {
        echo "❌ ERROR: No se pudo crear el directorio de backup $backup_dir\n";
        exit(1);
    }
}

echo "✅ Directorios creados correctamente\n";
echo "📁 Temporal: $temp_dir\n";
echo "📁 Backup: $backup_dir\n\n";

// PASO 1: Descargar WordPress
echo "=== PASO 1: DESCARGANDO WORDPRESS ===\n";
$zip_file = $temp_dir . '/wordpress-latest.zip';

echo "🌐 Descargando desde: $wordpress_url\n";
echo "📥 Guardando en: $zip_file\n";

$context = stream_context_create([
    'http' => [
        'timeout' => 300, // 5 minutos
        'user_agent' => 'WordPress-Downloader/1.0'
    ]
]);

$wordpress_content = file_get_contents($wordpress_url, false, $context);
if ($wordpress_content === false) {
    echo "❌ ERROR: No se pudo descargar WordPress\n";
    exit(1);
}

if (file_put_contents($zip_file, $wordpress_content) === false) {
    echo "❌ ERROR: No se pudo guardar el archivo ZIP\n";
    exit(1);
}

$file_size = filesize($zip_file);
echo "✅ WordPress descargado correctamente ($file_size bytes)\n\n";

// PASO 2: Extraer archivo ZIP
echo "=== PASO 2: EXTRAYENDO ARCHIVO ZIP ===\n";

$zip = new ZipArchive();
if ($zip->open($zip_file) !== true) {
    echo "❌ ERROR: No se pudo abrir el archivo ZIP\n";
    exit(1);
}

echo "📂 Extrayendo contenido...\n";
if (!$zip->extractTo($temp_dir)) {
    echo "❌ ERROR: No se pudo extraer el archivo ZIP\n";
    $zip->close();
    exit(1);
}

$zip->close();
echo "✅ Archivo ZIP extraído correctamente\n\n";

// PASO 3: Identificar archivos faltantes
echo "=== PASO 3: IDENTIFICANDO ARCHIVOS FALTANTES ===\n";

$extracted_dir = $temp_dir . '/wordpress';
if (!is_dir($extracted_dir)) {
    echo "❌ ERROR: No se encontró el directorio 'wordpress' extraído\n";
    exit(1);
}

$critical_files = [
    'wp-admin' => 'Directorio completo de administración',
    'wp-includes' => 'Directorio completo de includes',
    'wp-content/index.php' => 'Archivo de contenido',
    'wp-content/plugins/index.php' => 'Archivo de plugins',
    'wp-content/themes/index.php' => 'Archivo de temas',
    'index.php' => 'Archivo principal',
    'wp-load.php' => 'Cargador de WordPress',
    'wp-settings.php' => 'Configuración del sistema',
    'wp-config-sample.php' => 'Configuración de ejemplo',
    'wp-blog-header.php' => 'Header del blog',
    'wp-comments-post.php' => 'Sistema de comentarios',
    'wp-cron.php' => 'Sistema de cron',
    'wp-links-opml.php' => 'OPML de enlaces',
    'wp-login.php' => 'Sistema de login',
    'wp-mail.php' => 'Sistema de correo',
    'wp-trackback.php' => 'Sistema de trackback',
    'xmlrpc.php' => 'API XML-RPC',
    'readme.html' => 'Documentación',
    'license.txt' => 'Licencia'
];

$missing_files = [];
$existing_files = [];

foreach ($critical_files as $file => $description) {
    $source_path = $extracted_dir . '/' . $file;
    $target_path = $wordpress_path . '/' . $file;
    
    if (file_exists($source_path)) {
        if (file_exists($target_path)) {
            echo "✅ $file - Ya existe\n";
            $existing_files[] = $file;
        } else {
            echo "❌ $file - Faltante (se restaurará)\n";
            $missing_files[] = $file;
        }
    } else {
        echo "⚠️  $file - No disponible en la descarga\n";
    }
}

echo "\n=== RESUMEN ===\n";
echo "Archivos existentes: " . count($existing_files) . "\n";
echo "Archivos a restaurar: " . count($missing_files) . "\n";

if (empty($missing_files)) {
    echo "\n🎉 ¡No hay archivos que restaurar!\n";
    cleanup_temp_files();
    exit(0);
}

// PASO 4: Crear backup de archivos existentes
echo "\n=== PASO 4: CREANDO BACKUP ===\n";

foreach ($missing_files as $file) {
    $source_path = $wordpress_path . '/' . $file;
    $backup_path = $backup_dir . '/' . $file;
    
    if (file_exists($source_path)) {
        $backup_dir_path = dirname($backup_path);
        if (!is_dir($backup_dir_path)) {
            mkdir($backup_dir_path, 0755, true);
        }
        
        if (is_dir($source_path)) {
            copy_directory($source_path, $backup_path);
        } else {
            copy($source_path, $backup_path);
        }
        echo "💾 Backup creado: $file\n";
    }
}

echo "✅ Backup completado en: $backup_dir\n\n";

// PASO 5: Restaurar archivos faltantes
echo "=== PASO 5: RESTAURANDO ARCHIVOS ===\n";

$restored_count = 0;
foreach ($missing_files as $file) {
    $source_path = $extracted_dir . '/' . $file;
    $target_path = $wordpress_path . '/' . $file;
    
    if (file_exists($source_path)) {
        $target_dir = dirname($target_path);
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        if (is_dir($source_path)) {
            if (copy_directory($source_path, $target_path)) {
                echo "✅ Restaurado: $file\n";
                $restored_count++;
            } else {
                echo "❌ Error restaurando: $file\n";
            }
        } else {
            if (copy($source_path, $target_path)) {
                echo "✅ Restaurado: $file\n";
                $restored_count++;
            } else {
                echo "❌ Error restaurando: $file\n";
            }
        }
    }
}

echo "\n=== RESTAURACIÓN COMPLETADA ===\n";
echo "Archivos restaurados: $restored_count\n";
echo "Backup guardado en: $backup_dir\n";

// PASO 6: Limpiar archivos temporales
cleanup_temp_files();

echo "\n=== PRÓXIMOS PASOS ===\n";
echo "1. Ejecuta 'verificar-wordpress-completo.php' para confirmar la restauración\n";
echo "2. Intenta acceder a https://localhost/webstore/wp-admin/\n";
echo "3. Si hay problemas, revisa el backup en: $backup_dir\n";

echo "\n🎉 ¡Restauración completada!\n";

// Funciones auxiliares
function copy_directory($source, $dest) {
    if (!is_dir($dest)) {
        mkdir($dest, 0755, true);
    }
    
    $dir = opendir($source);
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            $source_path = $source . '/' . $file;
            $dest_path = $dest . '/' . $file;
            
            if (is_dir($source_path)) {
                copy_directory($source_path, $dest_path);
            } else {
                copy($source_path, $dest_path);
            }
        }
    }
    closedir($dir);
    return true;
}

function cleanup_temp_files() {
    global $temp_dir;
    echo "\n🧹 Limpiando archivos temporales...\n";
    
    if (is_dir($temp_dir)) {
        delete_directory($temp_dir);
        echo "✅ Archivos temporales eliminados\n";
    }
}

function delete_directory($dir) {
    if (!is_dir($dir)) {
        return;
    }
    
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            delete_directory($path);
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}
?>
