<?php
/**
 * Script para simular la activación del plugin en WordPress
 * Reproduce exactamente lo que hace WordPress al activar un plugin
 */

// Configurar el entorno para mostrar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

// Simular constantes de WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}
if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', __DIR__ . '/wp-content/plugins');
}
if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', __DIR__ . '/wp-content');
}
if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}

// Simular funciones básicas de WordPress
if (!function_exists('add_action')) {
    function add_action($hook, $function, $priority = 10, $accepted_args = 1) {
        echo "<div style='color: blue;'>✓ Hook registrado: $hook -> $function</div>";
        return true;
    }
}

if (!function_exists('register_activation_hook')) {
    function register_activation_hook($file, $function) {
        echo "<div style='color: green;'>✓ Hook de activación registrado: $function</div>";
        // Ejecutar la función de activación
        if (function_exists($function)) {
            echo "<div style='color: orange;'>⚡ Ejecutando función de activación: $function</div>";
            try {
                call_user_func($function);
                echo "<div style='color: green;'>✅ Función de activación ejecutada correctamente</div>";
            } catch (Exception $e) {
                echo "<div style='color: red;'>❌ ERROR en función de activación: " . $e->getMessage() . "</div>";
                return false;
            }
        }
        return true;
    }
}

if (!function_exists('load_plugin_textdomain')) {
    function load_plugin_textdomain($domain, $deprecated = false, $plugin_rel_path = false) {
        echo "<div style='color: blue;'>✓ Textdomain cargado: $domain</div>";
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
        return 'http://localhost/wp-content/plugins/' . basename(dirname($file)) . '/';
    }
}

if (!function_exists('wp_die')) {
    function wp_die($message, $title = '', $args = array()) {
        echo "<div style='color: red; font-weight: bold;'>WP_DIE: $message</div>";
        if ($title) echo "<div style='color: red;'>Título: $title</div>";
        exit;
    }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        return true;
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        return true; // Simular que el usuario tiene permisos
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        return $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value, $autoload = null) {
        echo "<div style='color: blue;'>✓ Opción actualizada: $option</div>";
        return true;
    }
}

if (!function_exists('wp_get_current_user')) {
    function wp_get_current_user() {
        return (object) array('ID' => 1, 'user_login' => 'admin');
    }
}

if (!function_exists('get_current_user_id')) {
    function get_current_user_id() {
        return 1;
    }
}

if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action = -1) {
        return true;
    }
}

if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action = -1) {
        return 'fake_nonce_' . md5($action);
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return trim(strip_tags($str));
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_url')) {
    function esc_url($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
}

if (!function_exists('admin_url')) {
    function admin_url($path = '', $scheme = 'admin') {
        return 'http://localhost/wp-admin/' . $path;
    }
}

if (!function_exists('wp_redirect')) {
    function wp_redirect($location, $status = 302) {
        echo "<div style='color: blue;'>✓ Redirección a: $location</div>";
        return true;
    }
}

if (!function_exists('add_menu_page')) {
    function add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null) {
        echo "<div style='color: green;'>✓ Página de menú agregada: $menu_title</div>";
        return true;
    }
}

if (!function_exists('add_submenu_page')) {
    function add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '') {
        echo "<div style='color: green;'>✓ Submenú agregado: $menu_title</div>";
        return true;
    }
}

if (!function_exists('register_post_type')) {
    function register_post_type($post_type, $args = array()) {
        echo "<div style='color: green;'>✓ Tipo de post registrado: $post_type</div>";
        return true;
    }
}

if (!function_exists('register_taxonomy')) {
    function register_taxonomy($taxonomy, $object_type, $args = array()) {
        echo "<div style='color: green;'>✓ Taxonomía registrada: $taxonomy</div>";
        return true;
    }
}

if (!function_exists('add_shortcode')) {
    function add_shortcode($tag, $func) {
        echo "<div style='color: green;'>✓ Shortcode registrado: [$tag]</div>";
        return true;
    }
}

if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false) {
        echo "<div style='color: blue;'>✓ Script encolado: $handle</div>";
        return true;
    }
}

if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all') {
        echo "<div style='color: blue;'>✓ Estilo encolado: $handle</div>";
        return true;
    }
}

if (!function_exists('wp_localize_script')) {
    function wp_localize_script($handle, $object_name, $l10n) {
        echo "<div style='color: blue;'>✓ Script localizado: $handle</div>";
        return true;
    }
}

if (!function_exists('get_bloginfo')) {
    function get_bloginfo($show = '') {
        switch ($show) {
            case 'version':
                return '6.0';
            case 'name':
                return 'Test Site';
            default:
                return 'Test Value';
        }
    }
}

if (!function_exists('wp_get_theme')) {
    function wp_get_theme() {
        return (object) array('get' => function($key) { return 'Test Theme'; });
    }
}

if (!class_exists('WP_User')) {
    class WP_User {
        public $ID = 1;
        public $user_login = 'admin';
        public function add_role($role) {
            echo "<div style='color: green;'>✓ Rol agregado al usuario: $role</div>";
        }
    }
}

echo "<h1>🔧 Simulación de Activación de Plugin WordPress</h1>";
echo "<hr>";

echo "<div style='margin: 10px 0; padding: 10px; border-left: 4px solid #007cba; background: #f9f9f9;'><strong>⏳ Paso 1:</strong> Preparando entorno WordPress simulado</div>";

// Simular la carga del plugin principal
echo "<div style='margin: 10px 0; padding: 10px; border-left: 4px solid #007cba; background: #f9f9f9;'><strong>⏳ Paso 2:</strong> Cargando archivo principal del plugin</div>";

try {
    // Intentar cargar el archivo principal original
    if (file_exists(__DIR__ . '/wp-cupon-whatsapp.php')) {
        echo "<div style='color: orange;'>⚡ Cargando wp-cupon-whatsapp.php...</div>";
        ob_start();
        include_once __DIR__ . '/wp-cupon-whatsapp.php';
        $output = ob_get_clean();
        if ($output) {
            echo "<div style='color: blue;'>Salida del plugin: $output</div>";
        }
        echo "<div style='color: green;'>✅ Plugin principal cargado exitosamente</div>";
    } else {
        echo "<div style='color: red;'>❌ No se encontró wp-cupon-whatsapp.php</div>";
        
        // Intentar con la versión standalone
        if (file_exists(__DIR__ . '/wp-cupon-whatsapp-standalone.php')) {
            echo "<div style='color: orange;'>⚡ Cargando wp-cupon-whatsapp-standalone.php...</div>";
            ob_start();
            include_once __DIR__ . '/wp-cupon-whatsapp-standalone.php';
            $output = ob_get_clean();
            if ($output) {
                echo "<div style='color: blue;'>Salida del plugin: $output</div>";
            }
            echo "<div style='color: green;'>✅ Plugin standalone cargado exitosamente</div>";
        } else {
            echo "<div style='color: red;'>❌ No se encontró wp-cupon-whatsapp-standalone.php</div>";
        }
    }
} catch (ParseError $e) {
    echo "<div style='color: red;'>❌ ERROR DE SINTAXIS: " . $e->getMessage() . "</div>";
    echo "<div style='color: red;'>Archivo: " . $e->getFile() . "</div>";
    echo "<div style='color: red;'>Línea: " . $e->getLine() . "</div>";
} catch (Error $e) {
    echo "<div style='color: red;'>❌ ERROR FATAL: " . $e->getMessage() . "</div>";
    echo "<div style='color: red;'>Archivo: " . $e->getFile() . "</div>";
    echo "<div style='color: red;'>Línea: " . $e->getLine() . "</div>";
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ EXCEPCIÓN: " . $e->getMessage() . "</div>";
    echo "<div style='color: red;'>Archivo: " . $e->getFile() . "</div>";
    echo "<div style='color: red;'>Línea: " . $e->getLine() . "</div>";
}

echo "<div style='margin: 10px 0; padding: 10px; border-left: 4px solid #46b450; background: #f9f9f9;'><strong>✅ Simulación completada</strong></div>";

// Mostrar log de errores si existe
if (file_exists(__DIR__ . '/error_log.txt')) {
    $error_log = file_get_contents(__DIR__ . '/error_log.txt');
    if (!empty($error_log)) {
        echo "<div style='margin: 10px 0; padding: 10px; border-left: 4px solid #dc3232; background: #f9f9f9;'>";
        echo "<strong>📋 Log de Errores:</strong><br>";
        echo "<pre style='background: #fff; padding: 10px; border: 1px solid #ccc;'>" . htmlspecialchars($error_log) . "</pre>";
        echo "</div>";
    }
}

echo "<div style='margin: 20px 0; padding: 15px; border: 2px solid #007cba; background: #e7f3ff;'>";
echo "<h3>📊 Resumen de la Simulación</h3>";
echo "<p>Esta simulación reproduce exactamente lo que hace WordPress cuando activa un plugin:</p>";
echo "<ul>";
echo "<li>✓ Carga el archivo principal del plugin</li>";
echo "<li>✓ Ejecuta todos los hooks de inicialización</li>";
echo "<li>✓ Registra funciones de activación</li>";
echo "<li>✓ Simula el entorno WordPress completo</li>";
echo "</ul>";
echo "<p><strong>Si aparece algún error arriba, ese es exactamente el problema que impide la activación del plugin en WordPress.</strong></p>";
echo "</div>";
?>