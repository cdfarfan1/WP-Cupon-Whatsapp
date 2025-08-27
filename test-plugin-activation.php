<?php
/**
 * Script de Testing para Activación del Plugin WP-Cupon-Whatsapp
 * Simula el entorno de WordPress para probar la activación
 * 
 * IMPORTANTE: Este script es para testing local únicamente
 * El plugin debe funcionar en cualquier instalación de WordPress
 */

// Configuración del entorno de testing
define('WPCW_TESTING_MODE', true);
define('WPCW_LOCAL_TESTING', true);
define('WPCW_DEBUG_MODE', true);

// Ruta al plugin en el entorno local
$plugin_path = 'C:\\xampp\\htdocs\\webstore\\wp-content\\plugins\\wp-cupon-whatsapp';
$wp_path = 'C:\\xampp\\htdocs\\webstore';

echo "========================================\n";
echo "   TEST DE ACTIVACIÓN DEL PLUGIN\n";
echo "   WP-Cupon-Whatsapp\n";
echo "========================================\n\n";

// Función para simular funciones básicas de WordPress
function simulate_wordpress_environment() {
    // Simular constantes de WordPress
    if (!defined('ABSPATH')) {
        define('ABSPATH', 'C:\\xampp\\htdocs\\webstore\\');
    }
    
    if (!defined('WP_PLUGIN_DIR')) {
        define('WP_PLUGIN_DIR', ABSPATH . 'wp-content/plugins');
    }
    
    if (!defined('WP_PLUGIN_URL')) {
        define('WP_PLUGIN_URL', 'https://localhost/webstore/wp-content/plugins');
    }
    
    // Simular funciones básicas de WordPress
    if (!function_exists('add_action')) {
        function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
            echo "[HOOK] add_action: $hook\n";
            return true;
        }
    }
    
    if (!function_exists('add_filter')) {
        function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
            echo "[HOOK] add_filter: $hook\n";
            return true;
        }
    }
    
    if (!function_exists('register_post_type')) {
        function register_post_type($post_type, $args = array()) {
            echo "[POST_TYPE] Registrando: $post_type\n";
            return true;
        }
    }
    
    if (!function_exists('register_taxonomy')) {
        function register_taxonomy($taxonomy, $object_type, $args = array()) {
            echo "[TAXONOMY] Registrando: $taxonomy\n";
            return true;
        }
    }
    
    if (!function_exists('wp_enqueue_script')) {
        function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false) {
            echo "[SCRIPT] Encolando: $handle\n";
            return true;
        }
    }
    
    if (!function_exists('wp_enqueue_style')) {
        function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all') {
            echo "[STYLE] Encolando: $handle\n";
            return true;
        }
    }
    
    if (!function_exists('plugin_dir_path')) {
        function plugin_dir_path($file) {
            return dirname($file) . '/';
        }
    }
    
    if (!function_exists('plugin_dir_url')) {
        function plugin_dir_url($file) {
            $plugin_dir = str_replace(ABSPATH, '', dirname($file));
            return 'https://localhost/webstore/' . str_replace('\\', '/', $plugin_dir) . '/';
        }
    }
    
    if (!function_exists('is_admin')) {
        function is_admin() {
            return true;
        }
    }
    
    if (!function_exists('current_user_can')) {
        function current_user_can($capability) {
            return true;
        }
    }
    
    if (!function_exists('get_option')) {
        function get_option($option, $default = false) {
            return $default;
        }
    }
    
    if (!function_exists('update_option')) {
        function update_option($option, $value) {
            echo "[OPTION] Actualizando: $option\n";
            return true;
        }
    }
    
    echo "✓ Entorno de WordPress simulado\n\n";
}

// Función para verificar la estructura del plugin
function verify_plugin_structure($plugin_path) {
    echo "[PASO 1] Verificando estructura del plugin...\n";
    
    $required_files = [
        'wp-cupon-whatsapp.php' => 'Archivo principal del plugin',
        'includes/post-types.php' => 'Definición de tipos de post',
        'admin/admin-menu.php' => 'Menú de administración',
        'readme.txt' => 'Documentación del plugin'
    ];
    
    $missing_files = [];
    
    foreach ($required_files as $file => $description) {
        $full_path = $plugin_path . '\\' . str_replace('/', '\\', $file);
        if (file_exists($full_path)) {
            echo "✓ $description: $file\n";
        } else {
            echo "✗ FALTA: $description: $file\n";
            $missing_files[] = $file;
        }
    }
    
    if (empty($missing_files)) {
        echo "✓ Estructura del plugin verificada\n\n";
        return true;
    } else {
        echo "✗ Faltan archivos críticos\n\n";
        return false;
    }
}

// Función para probar la carga del archivo principal
function test_main_plugin_file($plugin_path) {
    echo "[PASO 2] Probando carga del archivo principal...\n";
    
    $main_file = $plugin_path . '\\wp-cupon-whatsapp.php';
    
    if (!file_exists($main_file)) {
        echo "✗ ERROR: Archivo principal no encontrado\n\n";
        return false;
    }
    
    // Verificar sintaxis PHP
    $syntax_check = shell_exec("php -l \"$main_file\" 2>&1");
    if (strpos($syntax_check, 'No syntax errors') !== false) {
        echo "✓ Sintaxis PHP correcta\n";
    } else {
        echo "✗ ERROR de sintaxis: $syntax_check\n\n";
        return false;
    }
    
    // Intentar incluir el archivo (con captura de errores)
    ob_start();
    $error_occurred = false;
    
    try {
        // Simular el entorno antes de incluir
        simulate_wordpress_environment();
        
        // Incluir el archivo principal
        include_once $main_file;
        echo "✓ Archivo principal cargado exitosamente\n";
        
    } catch (Exception $e) {
        $error_occurred = true;
        echo "✗ ERROR al cargar: " . $e->getMessage() . "\n";
    } catch (ParseError $e) {
        $error_occurred = true;
        echo "✗ ERROR de parsing: " . $e->getMessage() . "\n";
    } catch (Error $e) {
        $error_occurred = true;
        echo "✗ ERROR fatal: " . $e->getMessage() . "\n";
    }
    
    $output = ob_get_clean();
    echo $output;
    
    if (!$error_occurred) {
        echo "\n✓ Plugin cargado sin errores críticos\n\n";
        return true;
    } else {
        echo "\n✗ Se encontraron errores durante la carga\n\n";
        return false;
    }
}

// Función para verificar compatibilidad para distribución masiva
function verify_mass_distribution_compatibility($plugin_path) {
    echo "[PASO 3] Verificando compatibilidad para distribución masiva...\n";
    
    $main_file = $plugin_path . '\\wp-cupon-whatsapp.php';
    $content = file_get_contents($main_file);
    
    $issues = [];
    
    // Verificar rutas absolutas hardcodeadas
    if (preg_match('/[C-Z]:\\\\/', $content)) {
        $issues[] = "Posibles rutas absolutas hardcodeadas detectadas";
    }
    
    // Verificar uso de localhost
    if (strpos($content, 'localhost') !== false) {
        $issues[] = "Referencias a localhost encontradas";
    }
    
    // Verificar configuraciones específicas del entorno
    if (strpos($content, 'xampp') !== false) {
        $issues[] = "Referencias específicas a XAMPP encontradas";
    }
    
    // Verificar encabezado del plugin
    if (!preg_match('/Plugin Name:/', $content)) {
        $issues[] = "Falta el encabezado 'Plugin Name' requerido";
    }
    
    if (!preg_match('/Version:/', $content)) {
        $issues[] = "Falta el encabezado 'Version' requerido";
    }
    
    if (empty($issues)) {
        echo "✓ Plugin compatible para distribución masiva\n";
        echo "✓ No se encontraron dependencias específicas del entorno\n";
        echo "✓ Sigue estándares de WordPress\n\n";
        return true;
    } else {
        echo "⚠ Posibles problemas de compatibilidad:\n";
        foreach ($issues as $issue) {
            echo "  - $issue\n";
        }
        echo "\n";
        return false;
    }
}

// Función principal de testing
function run_activation_test() {
    global $plugin_path, $wp_path;
    
    echo "Iniciando test de activación...\n\n";
    
    // Verificar que WordPress existe
    if (!file_exists($wp_path)) {
        echo "✗ ERROR: WordPress no encontrado en $wp_path\n";
        return false;
    }
    echo "✓ WordPress encontrado en $wp_path\n\n";
    
    // Verificar que el plugin existe
    if (!file_exists($plugin_path)) {
        echo "✗ ERROR: Plugin no encontrado en $plugin_path\n";
        return false;
    }
    echo "✓ Plugin encontrado en $plugin_path\n\n";
    
    // Ejecutar tests
    $structure_ok = verify_plugin_structure($plugin_path);
    $loading_ok = test_main_plugin_file($plugin_path);
    $compatibility_ok = verify_mass_distribution_compatibility($plugin_path);
    
    // Resultado final
    echo "========================================\n";
    echo "   RESULTADO DEL TEST\n";
    echo "========================================\n";
    
    if ($structure_ok && $loading_ok && $compatibility_ok) {
        echo "✓ ÉXITO: Plugin listo para activación\n";
        echo "✓ Compatible para distribución masiva\n";
        echo "\nPróximos pasos:\n";
        echo "1. Abrir https://localhost/webstore/wp-admin/\n";
        echo "2. Ir a Plugins > Plugins instalados\n";
        echo "3. Activar 'WP Cupon WhatsApp'\n";
        echo "4. Verificar que no aparecen errores\n";
        return true;
    } else {
        echo "✗ FALLÓ: Se encontraron problemas\n";
        echo "\nRevisar los errores anteriores antes de activar\n";
        return false;
    }
}

// Ejecutar el test
run_activation_test();

echo "\n========================================\n";
echo "Test completado.\n";
echo "========================================\n";
?>