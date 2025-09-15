<?php
/**
 * Restaurador inteligente de WordPress
 * Restaura solo los archivos necesarios sin sobrescribir contenido personalizado
 */

// Configuración
$wordpress_path = 'C:/xampp/htdocs/webstore';
$temp_dir = __DIR__ . '/temp-wordpress';

echo "=== RESTAURADOR INTELIGENTE DE WORDPRESS ===\n\n";

// Verificar que existe el directorio temporal
if (!is_dir($temp_dir)) {
    echo "❌ ERROR: No existe el directorio temporal. Ejecuta 'descargar-wordpress.php' primero.\n";
    exit(1);
}

$extracted_dir = $temp_dir . '/wordpress';
if (!is_dir($extracted_dir)) {
    echo "❌ ERROR: No se encontró WordPress extraído. Ejecuta 'descargar-wordpress.php' primero.\n";
    exit(1);
}

echo "✅ WordPress encontrado en: $extracted_dir\n\n";

// Archivos críticos que SIEMPRE deben restaurarse
$critical_files = [
    'wp-admin' => 'Panel de administración completo',
    'wp-includes' => 'Sistema de includes completo',
    'wp-load.php' => 'Cargador principal',
    'wp-settings.php' => 'Configuración del sistema',
    'wp-blog-header.php' => 'Header del blog',
    'wp-comments-post.php' => 'Sistema de comentarios',
    'wp-cron.php' => 'Sistema de cron',
    'wp-links-opml.php' => 'OPML de enlaces',
    'wp-login.php' => 'Sistema de login',
    'wp-mail.php' => 'Sistema de correo',
    'wp-trackback.php' => 'Sistema de trackback',
    'xmlrpc.php' => 'API XML-RPC'
];

// Archivos que NO se deben sobrescribir si existen
$preserve_files = [
    'wp-config.php' => 'Configuración personalizada',
    'wp-content' => 'Contenido personalizado',
    '.htaccess' => 'Configuración del servidor'
];

echo "=== RESTAURANDO ARCHIVOS CRÍTICOS ===\n";

$restored_count = 0;
$skipped_count = 0;

foreach ($critical_files as $file => $description) {
    $source_path = $extracted_dir . '/' . $file;
    $target_path = $wordpress_path . '/' . $file;
    
    if (file_exists($source_path)) {
        $target_dir = dirname($target_path);
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        if (is_dir($source_path)) {
            if (restore_directory($source_path, $target_path)) {
                echo "✅ Restaurado: $file - $description\n";
                $restored_count++;
            } else {
                echo "❌ Error restaurando: $file\n";
            }
        } else {
            if (copy($source_path, $target_path)) {
                echo "✅ Restaurado: $file - $description\n";
                $restored_count++;
            } else {
                echo "❌ Error restaurando: $file\n";
            }
        }
    } else {
        echo "⚠️  No disponible: $file\n";
    }
}

echo "\n=== ARCHIVOS PRESERVADOS ===\n";
foreach ($preserve_files as $file => $description) {
    $target_path = $wordpress_path . '/' . $file;
    if (file_exists($target_path)) {
        echo "🛡️  Preservado: $file - $description\n";
        $skipped_count++;
    }
}

echo "\n=== RESTAURACIÓN COMPLETADA ===\n";
echo "Archivos restaurados: $restored_count\n";
echo "Archivos preservados: $skipped_count\n";

echo "\n=== PRÓXIMOS PASOS ===\n";
echo "1. Ejecuta 'verificar-wordpress-completo.php' para confirmar\n";
echo "2. Intenta acceder a https://localhost/webstore/wp-admin/\n";
echo "3. Si funciona, elimina el directorio temporal: $temp_dir\n";

echo "\n🎉 ¡Restauración completada!\n";

function restore_directory($source, $dest) {
    if (!is_dir($dest)) {
        mkdir($dest, 0755, true);
    }
    
    $dir = opendir($source);
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            $source_path = $source . '/' . $file;
            $dest_path = $dest . '/' . $file;
            
            if (is_dir($source_path)) {
                restore_directory($source_path, $dest_path);
            } else {
                copy($source_path, $dest_path);
            }
        }
    }
    closedir($dir);
    return true;
}
?>
