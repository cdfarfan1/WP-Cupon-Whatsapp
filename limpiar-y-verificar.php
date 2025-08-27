<?php
/**
 * Limpiador y verificador final de WordPress
 * Limpia archivos temporales y verifica la instalación
 */

// Configuración
$wordpress_path = 'C:/xampp/htdocs/webstore';
$temp_dir = __DIR__ . '/temp-wordpress';

echo "=== LIMPIADOR Y VERIFICADOR FINAL ===\n\n";

// PASO 1: Limpiar archivos temporales
echo "=== PASO 1: LIMPIANDO ARCHIVOS TEMPORALES ===\n";

if (is_dir($temp_dir)) {
    echo "🧹 Eliminando directorio temporal: $temp_dir\n";
    if (delete_directory($temp_dir)) {
        echo "✅ Archivos temporales eliminados correctamente\n";
    } else {
        echo "❌ Error eliminando archivos temporales\n";
    }
} else {
    echo "✅ No hay archivos temporales que limpiar\n";
}

// PASO 2: Verificar instalación final
echo "\n=== PASO 2: VERIFICACIÓN FINAL ===\n";

$critical_files = [
    'wp-admin/index.php' => 'Panel de administración',
    'wp-includes/version.php' => 'Información de versión',
    'wp-config.php' => 'Configuración',
    'index.php' => 'Archivo principal'
];

$all_good = true;
foreach ($critical_files as $file => $description) {
    $full_path = $wordpress_path . '/' . $file;
    if (file_exists($full_path)) {
        echo "✅ $file - $description\n";
    } else {
        echo "❌ $file - $description (FALTANTE)\n";
        $all_good = false;
    }
}

// PASO 3: Verificar acceso web
echo "\n=== PASO 3: VERIFICACIÓN DE ACCESO WEB ===\n";

$test_urls = [
    'https://localhost/webstore/' => 'Página principal',
    'https://localhost/webstore/wp-admin/' => 'Panel de administración'
];

foreach ($test_urls as $url => $description) {
    echo "🌐 Probando: $description\n";
    echo "   URL: $url\n";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'WordPress-Tester/1.0'
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        echo "   ❌ Error de conexión\n";
        $all_good = false;
    } else {
        $http_response = $http_response_header ?? [];
        $status_line = $http_response[0] ?? 'Unknown';
        
        if (strpos($status_line, '200') !== false) {
            echo "   ✅ Acceso exitoso (200 OK)\n";
        } elseif (strpos($status_line, '500') !== false) {
            echo "   ❌ Error del servidor (500)\n";
            $all_good = false;
        } else {
            echo "   ⚠️  Respuesta: $status_line\n";
        }
    }
}

// PASO 4: Resumen final
echo "\n=== RESUMEN FINAL ===\n";

if ($all_good) {
    echo "🎉 ¡INSTALACIÓN COMPLETADA EXITOSAMENTE!\n";
    echo "✅ Todos los archivos críticos están presentes\n";
    echo "✅ El acceso web funciona correctamente\n";
    echo "✅ El error HTTP 500 ha sido resuelto\n\n";
    
    echo "🚀 Tu WordPress está listo para usar:\n";
    echo "   - Panel de admin: https://localhost/webstore/wp-admin/\n";
    echo "   - Página principal: https://localhost/webstore/\n";
    
} else {
    echo "⚠️  LA INSTALACIÓN TIENE PROBLEMAS\n";
    echo "❌ Algunos archivos críticos faltan o hay errores de acceso\n\n";
    
    echo "🔧 ACCIONES RECOMENDADAS:\n";
    echo "1. Ejecuta 'verificar-wordpress-completo.php' para diagnóstico detallado\n";
    echo "2. Verifica que XAMPP esté funcionando correctamente\n";
    echo "3. Revisa los logs de error de Apache en XAMPP\n";
    echo "4. Considera reinstalar WordPress completamente\n";
}

echo "\n=== INSTRUCCIONES DE MANTENIMIENTO ===\n";
echo "📋 Para mantener tu WordPress funcionando:\n";
echo "   - Mantén XAMPP actualizado\n";
echo "   - Haz backups regulares de wp-content y wp-config.php\n";
echo "   - No modifiques archivos del core de WordPress\n";
echo "   - Usa plugins y temas compatibles\n";

echo "\n=== FIN DEL PROCESO ===\n";

function delete_directory($dir) {
    if (!is_dir($dir)) {
        return true;
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
    return rmdir($dir);
}
?>
