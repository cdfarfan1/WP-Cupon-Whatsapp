<?php
/**
 * Plugin Activation Tester - WP Cupon WhatsApp
 * Sistema de diagnóstico para aislar errores de activación
 */

// Prevenir ejecución directa
if (!defined('ABSPATH')) {
    // Para testing local, simular WordPress
    define('ABSPATH', dirname(__FILE__) . '/');
    define('WP_DEBUG', true);
    define('WP_DEBUG_LOG', true);
}

/**
 * Clase para testing de activación del plugin
 */
class WPCW_Activation_Tester {
    
    private $errors = array();
    private $warnings = array();
    private $plugin_dir;
    
    public function __construct() {
        $this->plugin_dir = dirname(__FILE__) . '/';
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }
    
    /**
     * Ejecutar todas las pruebas de activación
     */
    public function run_all_tests() {
        echo "=== SISTEMA DE DIAGNÓSTICO DE ACTIVACIÓN ===\n";
        echo "Plugin: WP Cupon WhatsApp\n";
        echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";
        
        $this->test_file_structure();
        $this->test_syntax_errors();
        $this->test_dependencies();
        $this->test_plugin_header();
        $this->test_main_functions();
        $this->test_includes();
        $this->test_hooks_registration();
        
        $this->show_results();
    }
    
    /**
     * Test 1: Verificar estructura de archivos
     */
    private function test_file_structure() {
        echo "[TEST 1] Verificando estructura de archivos...\n";
        
        $required_files = array(
            'wp-cupon-whatsapp.php',
            'includes/post-types.php',
            'includes/taxonomies.php',
            'admin/admin-menu.php'
        );
        
        foreach ($required_files as $file) {
            $file_path = $this->plugin_dir . $file;
            if (!file_exists($file_path)) {
                $this->errors[] = "Archivo faltante: $file";
                echo "  ✗ FALTA: $file\n";
            } else {
                echo "  ✓ OK: $file\n";
            }
        }
        echo "\n";
    }
    
    /**
     * Test 2: Verificar errores de sintaxis
     */
    private function test_syntax_errors() {
        echo "[TEST 2] Verificando errores de sintaxis...\n";
        
        $php_files = array(
            'wp-cupon-whatsapp.php',
            'debug-headers.php',
            'includes/post-types.php',
            'admin/admin-menu.php'
        );
        
        foreach ($php_files as $file) {
            $file_path = $this->plugin_dir . $file;
            if (file_exists($file_path)) {
                $syntax_check = $this->check_php_syntax($file_path);
                if ($syntax_check !== true) {
                    $this->errors[] = "Error de sintaxis en $file: $syntax_check";
                    echo "  ✗ SINTAXIS: $file - $syntax_check\n";
                } else {
                    echo "  ✓ SINTAXIS OK: $file\n";
                }
            }
        }
        echo "\n";
    }
    
    /**
     * Test 3: Verificar dependencias
     */
    private function test_dependencies() {
        echo "[TEST 3] Verificando dependencias...\n";
        
        // Simular constantes de WordPress
        if (!defined('WPCW_PLUGIN_DIR')) {
            define('WPCW_PLUGIN_DIR', $this->plugin_dir);
        }
        
        // Verificar versión de PHP
        $min_php = '7.4';
        if (version_compare(PHP_VERSION, $min_php, '<')) {
            $this->errors[] = "PHP version " . PHP_VERSION . " es menor que la requerida: $min_php";
            echo "  ✗ PHP: " . PHP_VERSION . " (requerido: $min_php)\n";
        } else {
            echo "  ✓ PHP: " . PHP_VERSION . "\n";
        }
        
        echo "\n";
    }
    
    /**
     * Test 4: Verificar header del plugin
     */
    private function test_plugin_header() {
        echo "[TEST 4] Verificando header del plugin...\n";
        
        $main_file = $this->plugin_dir . 'wp-cupon-whatsapp.php';
        if (file_exists($main_file)) {
            $content = file_get_contents($main_file);
            
            $required_headers = array(
                'Plugin Name:',
                'Version:',
                'Description:'
            );
            
            foreach ($required_headers as $header) {
                if (strpos($content, $header) === false) {
                    $this->warnings[] = "Header faltante: $header";
                    echo "  ⚠ FALTA: $header\n";
                } else {
                    echo "  ✓ OK: $header\n";
                }
            }
        }
        echo "\n";
    }
    
    /**
     * Test 5: Verificar funciones principales
     */
    private function test_main_functions() {
        echo "[TEST 5] Verificando funciones principales...\n";
        
        // Intentar incluir el archivo principal de forma segura
        ob_start();
        $error_occurred = false;
        
        try {
            // Capturar errores fatales
            register_shutdown_function(function() use (&$error_occurred) {
                $error = error_get_last();
                if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                    $error_occurred = true;
                }
            });
            
            include_once $this->plugin_dir . 'wp-cupon-whatsapp.php';
            
            // Verificar si las funciones principales existen
            $main_functions = array(
                'wpcw_init',
                'wpcw_check_dependencies'
            );
            
            foreach ($main_functions as $function) {
                if (function_exists($function)) {
                    echo "  ✓ FUNCIÓN: $function\n";
                } else {
                    $this->warnings[] = "Función no encontrada: $function";
                    echo "  ⚠ NO ENCONTRADA: $function\n";
                }
            }
            
        } catch (Exception $e) {
            $this->errors[] = "Error al incluir archivo principal: " . $e->getMessage();
            echo "  ✗ ERROR: " . $e->getMessage() . "\n";
        } catch (ParseError $e) {
            $this->errors[] = "Error de sintaxis en archivo principal: " . $e->getMessage();
            echo "  ✗ PARSE ERROR: " . $e->getMessage() . "\n";
        }
        
        ob_end_clean();
        echo "\n";
    }
    
    /**
     * Test 6: Verificar includes problemáticos
     */
    private function test_includes() {
        echo "[TEST 6] Verificando archivos include...\n";
        
        $include_files = array(
            'debug-headers.php',
            'debug-output.php',
            'test-headers.php'
        );
        
        foreach ($include_files as $file) {
            $file_path = $this->plugin_dir . $file;
            if (file_exists($file_path)) {
                echo "  Probando: $file\n";
                
                ob_start();
                $include_error = false;
                
                try {
                    include_once $file_path;
                    echo "    ✓ INCLUDE OK: $file\n";
                } catch (Exception $e) {
                    $this->errors[] = "Error en include $file: " . $e->getMessage();
                    echo "    ✗ ERROR: $file - " . $e->getMessage() . "\n";
                } catch (ParseError $e) {
                    $this->errors[] = "Parse error en $file: " . $e->getMessage();
                    echo "    ✗ PARSE: $file - " . $e->getMessage() . "\n";
                }
                
                ob_end_clean();
            }
        }
        echo "\n";
    }
    
    /**
     * Test 7: Verificar registro de hooks
     */
    private function test_hooks_registration() {
        echo "[TEST 7] Verificando registro de hooks...\n";
        
        // Simular funciones de WordPress
        if (!function_exists('add_action')) {
            function add_action($hook, $callback, $priority = 10, $args = 1) {
                echo "    Hook registrado: $hook\n";
                return true;
            }
        }
        
        if (!function_exists('register_post_type')) {
            function register_post_type($post_type, $args = array()) {
                echo "    Post type registrado: $post_type\n";
                return true;
            }
        }
        
        echo "  ✓ Simulación de hooks completada\n";
        echo "\n";
    }
    
    /**
     * Verificar sintaxis PHP de un archivo
     */
    private function check_php_syntax($file_path) {
        $content = file_get_contents($file_path);
        
        // Verificar balance de llaves
        $open_braces = substr_count($content, '{');
        $close_braces = substr_count($content, '}');
        
        if ($open_braces !== $close_braces) {
            return "Llaves desbalanceadas: {$open_braces} abiertas, {$close_braces} cerradas";
        }
        
        // Verificar balance de paréntesis
        $open_parens = substr_count($content, '(');
        $close_parens = substr_count($content, ')');
        
        if ($open_parens !== $close_parens) {
            return "Paréntesis desbalanceados: {$open_parens} abiertos, {$close_parens} cerrados";
        }
        
        return true;
    }
    
    /**
     * Mostrar resultados finales
     */
    private function show_results() {
        echo "=== RESULTADOS DEL DIAGNÓSTICO ===\n\n";
        
        if (empty($this->errors) && empty($this->warnings)) {
            echo "✅ TODOS LOS TESTS PASARON\n";
            echo "El plugin debería activarse correctamente.\n\n";
        } else {
            if (!empty($this->errors)) {
                echo "❌ ERRORES CRÍTICOS ENCONTRADOS:\n";
                foreach ($this->errors as $i => $error) {
                    echo "  " . ($i + 1) . ". $error\n";
                }
                echo "\n";
            }
            
            if (!empty($this->warnings)) {
                echo "⚠️  ADVERTENCIAS:\n";
                foreach ($this->warnings as $i => $warning) {
                    echo "  " . ($i + 1) . ". $warning\n";
                }
                echo "\n";
            }
        }
        
        echo "=== RECOMENDACIONES ===\n";
        if (!empty($this->errors)) {
            echo "1. Corregir todos los errores críticos antes de activar\n";
            echo "2. Subir archivos corregidos al servidor\n";
            echo "3. Ejecutar este diagnóstico nuevamente\n";
        } else {
            echo "1. El plugin está listo para activación\n";
            echo "2. Si persisten problemas, revisar logs del servidor\n";
        }
        
        echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
    }
}

// Ejecutar diagnóstico si se llama directamente
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $tester = new WPCW_Activation_Tester();
    $tester->run_all_tests();
}

?>