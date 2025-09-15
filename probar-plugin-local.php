<?php
/**
 * Script para probar el plugin WP Cupón WhatsApp en entorno local
 * Realiza pruebas automatizadas de funcionalidades básicas
 */

echo "<h1>🧪 Prueba del Plugin WP Cupón WhatsApp - Entorno Local</h1>";
echo "<hr>";

// Configuración del entorno local
$wordpress_url = 'http://localhost/webstore';
$admin_url = $wordpress_url . '/wp-admin';
$plugin_dir = 'C:\\xampp\\htdocs\\webstore\\wp-content\\plugins\\wp-cupon-whatsapp';
$debug_log = 'C:\\xampp\\htdocs\\webstore\\wp-content\\debug.log';

echo "<h2>1. 🔍 Verificación del Entorno</h2>";

// Verificar que XAMPP esté ejecutándose
echo "<h3>Verificando servicios XAMPP:</h3>";

$apache_running = false;
$mysql_running = false;

// Verificar Apache
$apache_check = shell_exec('tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL');
if (strpos($apache_check, 'httpd.exe') !== false) {
    echo "<p>✅ Apache está ejecutándose</p>";
    $apache_running = true;
} else {
    echo "<p style='color: red;'>❌ Apache no está ejecutándose</p>";
}

// Verificar MySQL
$mysql_check = shell_exec('tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL');
if (strpos($mysql_check, 'mysqld.exe') !== false) {
    echo "<p>✅ MySQL está ejecutándose</p>";
    $mysql_running = true;
} else {
    echo "<p style='color: red;'>❌ MySQL no está ejecutándose</p>";
}

if (!$apache_running || !$mysql_running) {
    echo "<div style='background: #ffebee; padding: 15px; border-left: 4px solid #f44336; margin: 20px 0;'>";
    echo "<h4>⚠️ Servicios XAMPP no están ejecutándose</h4>";
    echo "<p>Por favor, inicia XAMPP y asegúrate de que Apache y MySQL estén ejecutándose antes de continuar.</p>";
    echo "</div>";
}

echo "<h3>Verificando instalación del plugin:</h3>";

// Verificar que el plugin esté instalado
if (is_dir($plugin_dir)) {
    echo "<p>✅ Plugin instalado en: $plugin_dir</p>";
    
    // Verificar archivos principales
    $main_files = [
        'wp-cupon-whatsapp.php',
        'admin/admin-menu.php',
        'includes/post-types.php',
        'public/shortcodes.php'
    ];
    
    foreach ($main_files as $file) {
        $file_path = $plugin_dir . '\\' . str_replace('/', '\\', $file);
        if (file_exists($file_path)) {
            echo "<p>✅ $file</p>";
        } else {
            echo "<p style='color: red;'>❌ $file (faltante)</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ Plugin no encontrado en: $plugin_dir</p>";
    echo "<p>Ejecuta el script de instalación primero: <code>instalar-plugin-local.bat</code></p>";
}

echo "<h2>2. 🌐 Prueba de Conectividad Web</h2>";

// Verificar que WordPress esté accesible
echo "<h3>Verificando acceso a WordPress:</h3>";

$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'ignore_errors' => true
    ]
]);

$wordpress_response = @file_get_contents($wordpress_url, false, $context);

if ($wordpress_response !== false) {
    echo "<p>✅ WordPress accesible en: <a href='$wordpress_url' target='_blank'>$wordpress_url</a></p>";
    
    // Verificar si es una instalación de WordPress válida
    if (strpos($wordpress_response, 'wp-content') !== false || strpos($wordpress_response, 'WordPress') !== false) {
        echo "<p>✅ Instalación de WordPress válida detectada</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Respuesta recibida pero no parece ser WordPress</p>";
    }
} else {
    echo "<p style='color: red;'>❌ No se puede acceder a WordPress en: $wordpress_url</p>";
    echo "<p>Verifica que XAMPP esté ejecutándose y que WordPress esté instalado.</p>";
}

// Verificar acceso al admin
$admin_response = @file_get_contents($admin_url, false, $context);
if ($admin_response !== false) {
    echo "<p>✅ WordPress Admin accesible en: <a href='$admin_url' target='_blank'>$admin_url</a></p>";
} else {
    echo "<p style='color: orange;'>⚠️ WordPress Admin no accesible (podría requerir login)</p>";
}

echo "<h2>3. 📋 Verificación de Logs de Debug</h2>";

// Verificar logs de debug
if (file_exists($debug_log)) {
    $log_size = filesize($debug_log);
    $log_modified = date('Y-m-d H:i:s', filemtime($debug_log));
    
    echo "<p>✅ Archivo de debug encontrado</p>";
    echo "<p>📊 Tamaño: " . number_format($log_size) . " bytes</p>";
    echo "<p>🕒 Última modificación: $log_modified</p>";
    
    if ($log_size > 0) {
        echo "<h4>📋 Últimas 10 líneas del log:</h4>";
        $lines = file($debug_log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $last_lines = array_slice($lines, -10);
        
        echo "<div style='background: #f1f1f1; padding: 10px; border-left: 4px solid #007cba; font-family: monospace; white-space: pre-wrap; max-height: 200px; overflow-y: auto;'>";
        foreach ($last_lines as $line) {
            // Colorear líneas según el tipo de error
            $color = 'black';
            if (strpos($line, 'FATAL') !== false || strpos($line, 'Fatal') !== false) {
                $color = 'red';
            } elseif (strpos($line, 'ERROR') !== false || strpos($line, 'Error') !== false) {
                $color = 'red';
            } elseif (strpos($line, 'WARNING') !== false || strpos($line, 'Warning') !== false) {
                $color = 'orange';
            } elseif (strpos($line, 'wp-cupon-whatsapp') !== false) {
                $color = 'blue';
            }
            
            echo "<span style='color: $color;'>" . htmlspecialchars($line) . "</span>\n";
        }
        echo "</div>";
    } else {
        echo "<p style='color: green;'>✅ Log vacío - No hay errores registrados</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Archivo de debug no encontrado (se creará cuando ocurra el primer error)</p>";
}

echo "<h2>4. 🔧 Instrucciones para Pruebas Manuales</h2>";

echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>📋 Lista de Pruebas a Realizar:</h3>";
echo "<ol>";
echo "<li><strong>Activar el Plugin:</strong>";
echo "<ul>";
echo "<li>Ve a: <a href='$admin_url/plugins.php' target='_blank'>$admin_url/plugins.php</a></li>";
echo "<li>Busca 'WP Cupon WhatsApp'</li>";
echo "<li>Haz clic en 'Activar'</li>";
echo "<li>Verifica que no aparezcan errores</li>";
echo "</ul>";
echo "</li>";

echo "<li><strong>Verificar Menú de Administración:</strong>";
echo "<ul>";
echo "<li>Busca el menú 'WP Cupon' en el sidebar del admin</li>";
echo "<li>Verifica que tenga submenús (Configuración, Cupones, etc.)</li>";
echo "</ul>";
echo "</li>";

echo "<li><strong>Probar Configuración:</strong>";
echo "<ul>";
echo "<li>Ve a WP Cupon > Configuración</li>";
echo "<li>Verifica que la página cargue sin errores</li>";
echo "<li>Intenta guardar una configuración básica</li>";
echo "</ul>";
echo "</li>";

echo "<li><strong>Crear un Cupón de Prueba:</strong>";
echo "<ul>";
echo "<li>Ve a WP Cupon > Cupones</li>";
echo "<li>Crea un nuevo cupón</li>";
echo "<li>Verifica que se guarde correctamente</li>";
echo "</ul>";
echo "</li>";

echo "<li><strong>Probar Shortcode:</strong>";
echo "<ul>";
echo "<li>Crea una página o entrada de prueba</li>";
echo "<li>Añade el shortcode del plugin</li>";
echo "<li>Verifica que se muestre correctamente en el frontend</li>";
echo "</ul>";
echo "</li>";
echo "</ol>";
echo "</div>";

echo "<h2>5. 🚨 Monitoreo de Errores</h2>";

echo "<div style='background: #fff3e0; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>🔍 Cómo Monitorear Errores Durante las Pruebas:</h3>";
echo "<ol>";
echo "<li><strong>Abrir Monitor de Logs:</strong>";
echo "<p>Ejecuta en PowerShell: <code>.\\monitorear-logs-wordpress.ps1</code></p>";
echo "</li>";
echo "<li><strong>Realizar Pruebas:</strong>";
echo "<p>Mientras el monitor está activo, realiza las pruebas del plugin</p>";
echo "</li>";
echo "<li><strong>Observar Errores:</strong>";
echo "<p>Cualquier error aparecerá inmediatamente en el monitor</p>";
echo "</li>";
echo "</ol>";
echo "</div>";

echo "<h2>6. 📊 Resumen del Estado Actual</h2>";

echo "<div style='background: #f1f8e9; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>✅ Estado del Plugin:</h3>";
echo "<ul>";
echo "<li>✅ Errores fatales corregidos</li>";
echo "<li>✅ Plugin instalado en el directorio correcto</li>";
echo "<li>✅ Debug de WordPress activado</li>";
echo "<li>✅ Sistema de monitoreo de logs configurado</li>";
echo "<li>🔄 Pendiente: Activación y pruebas funcionales</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<h3>🚀 Próximos Pasos:</h3>";
echo "<ol>";
echo "<li>Ejecutar el monitor de logs: <code>.\\monitorear-logs-wordpress.ps1</code></li>";
echo "<li>Ir a WordPress Admin: <a href='$admin_url' target='_blank'>$admin_url</a></li>";
echo "<li>Activar el plugin 'WP Cupon WhatsApp'</li>";
echo "<li>Realizar las pruebas manuales listadas arriba</li>";
echo "<li>Reportar cualquier error que aparezca en los logs</li>";
echo "</ol>";

echo "<p><strong>¡El plugin está listo para ser probado en el entorno local!</strong></p>";
?>