<?php
/**
 * Verificador completo de instalación de WordPress
 * Detecta archivos faltantes y problemas de configuración
 */

// Configuración
$wordpress_path = 'C:/xampp/htdocs/webstore';
$wordpress_url = 'https://wordpress.org/latest.zip';
$temp_dir = __DIR__ . '/temp-wordpress';

echo "=== VERIFICADOR COMPLETO DE WORDPRESS ===\n\n";

// 1. Verificar que el directorio existe
if (!is_dir($wordpress_path)) {
    echo "❌ ERROR: El directorio $wordpress_path no existe\n";
    exit(1);
}

echo "✅ Directorio WordPress encontrado: $wordpress_path\n\n";

// 2. Verificar archivos críticos
$critical_files = [
    'wp-config.php' => 'Configuración de WordPress',
    'index.php' => 'Archivo principal',
    'wp-load.php' => 'Cargador de WordPress',
    'wp-settings.php' => 'Configuración del sistema',
    'wp-includes/version.php' => 'Información de versión',
    'wp-includes/wp-db.php' => 'Base de datos',
    'wp-includes/plugin.php' => 'Sistema de plugins',
    'wp-includes/theme.php' => 'Sistema de temas',
    'wp-admin/index.php' => 'Panel de administración',
    'wp-admin/admin.php' => 'Funciones de admin',
    'wp-admin/admin-ajax.php' => 'AJAX de admin',
    'wp-admin/load.php' => 'Cargador de admin',
    'wp-content/index.php' => 'Contenido del sitio',
    'wp-content/plugins/index.php' => 'Plugins',
    'wp-content/themes/index.php' => 'Temas'
];

echo "=== VERIFICANDO ARCHIVOS CRÍTICOS ===\n";
$missing_files = [];
$existing_files = [];

foreach ($critical_files as $file => $description) {
    $full_path = $wordpress_path . '/' . $file;
    if (file_exists($full_path)) {
        echo "✅ $file - $description\n";
        $existing_files[] = $file;
    } else {
        echo "❌ $file - $description (FALTANTE)\n";
        $missing_files[] = $file;
    }
}

echo "\n=== RESUMEN ===\n";
echo "Archivos existentes: " . count($existing_files) . "\n";
echo "Archivos faltantes: " . count($missing_files) . "\n";

if (empty($missing_files)) {
    echo "\n🎉 ¡Tu instalación de WordPress está completa!\n";
    exit(0);
}

echo "\n=== ARCHIVOS FALTANTES ===\n";
foreach ($missing_files as $file) {
    echo "- $file\n";
}

// 3. Verificar estructura de directorios
echo "\n=== VERIFICANDO ESTRUCTURA DE DIRECTORIOS ===\n";
$critical_dirs = [
    'wp-admin',
    'wp-includes', 
    'wp-content',
    'wp-content/plugins',
    'wp-content/themes',
    'wp-content/uploads'
];

foreach ($critical_dirs as $dir) {
    $full_path = $wordpress_path . '/' . $dir;
    if (is_dir($full_path)) {
        $file_count = count(glob($full_path . '/*'));
        echo "✅ $dir/ ($file_count archivos)\n";
    } else {
        echo "❌ $dir/ (NO EXISTE)\n";
    }
}

// 4. Verificar wp-config.php
echo "\n=== VERIFICANDO CONFIGURACIÓN ===\n";
$wp_config = $wordpress_path . '/wp-config.php';
if (file_exists($wp_config)) {
    $config_content = file_get_contents($wp_config);
    
    // Verificar constantes importantes
    $constants = [
        'DB_NAME' => 'Nombre de base de datos',
        'DB_USER' => 'Usuario de base de datos', 
        'DB_PASSWORD' => 'Contraseña de base de datos',
        'DB_HOST' => 'Host de base de datos',
        'WP_DEBUG' => 'Modo debug',
        'WP_HOME' => 'URL del sitio',
        'WP_SITEURL' => 'URL del sitio'
    ];
    
    foreach ($constants as $constant => $description) {
        if (preg_match("/define\s*\(\s*['\"]" . preg_quote($constant) . "['\"]\s*,\s*['\"]([^'\"]*)['\"]\s*\)/", $config_content, $matches)) {
            $value = $matches[1];
            if ($constant === 'DB_PASSWORD') {
                $value = str_repeat('*', strlen($value));
            }
            echo "✅ $constant: $value\n";
        } else {
            echo "❌ $constant: NO DEFINIDA\n";
        }
    }
} else {
    echo "❌ wp-config.php no encontrado\n";
}

// 5. Verificar base de datos
echo "\n=== VERIFICANDO BASE DE DATOS ===\n";
if (file_exists($wp_config)) {
    // Extraer configuración de BD
    if (preg_match("/define\s*\(\s*['\"]DB_HOST['\"]\s*,\s*['\"]([^'\"]*)['\"]\s*\)/", $config_content, $matches) &&
        preg_match("/define\s*\(\s*['\"]DB_NAME['\"]\s*,\s*['\"]([^'\"]*)['\"]\s*\)/", $config_content, $matches2) &&
        preg_match("/define\s*\(\s*['\"]DB_USER['\"]\s*,\s*['\"]([^'\"]*)['\"]\s*\)/", $config_content, $matches3)) {
        
        $db_host = $matches[1];
        $db_name = $matches2[1];
        $db_user = $matches3[1];
        
        try {
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, '');
            echo "✅ Conexión a base de datos exitosa\n";
            
            // Verificar tablas principales
            $tables = ['wp_options', 'wp_posts', 'wp_users', 'wp_usermeta'];
            foreach ($tables as $table) {
                $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                if ($stmt->rowCount() > 0) {
                    echo "✅ Tabla $table existe\n";
                } else {
                    echo "❌ Tabla $table NO EXISTE\n";
                }
            }
        } catch (PDOException $e) {
            echo "❌ Error de conexión a BD: " . $e->getMessage() . "\n";
        }
    } else {
        echo "❌ No se pudieron extraer los parámetros de BD del wp-config.php\n";
    }
}

// 6. Recomendaciones
echo "\n=== RECOMENDACIONES ===\n";
if (count($missing_files) > 5) {
    echo "🔴 PROBLEMA CRÍTICO: Muchos archivos faltan. Necesitas reinstalar WordPress.\n";
} elseif (count($missing_files) > 0) {
    echo "🟡 PROBLEMA MODERADO: Algunos archivos faltan. Puedes restaurarlos individualmente.\n";
}

echo "\n=== PRÓXIMOS PASOS ===\n";
echo "1. Ejecuta 'descargar-wordpress.php' para descargar archivos faltantes\n";
echo "2. Ejecuta 'restaurar-wordpress.php' para restaurar la instalación\n";
echo "3. Ejecuta 'verificar-wordpress-completo.php' nuevamente para confirmar\n";

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
?>
