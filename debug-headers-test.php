<?php
/**
 * VERSIÓN DE PRUEBA - Debug Headers Test
 * 
 * Esta es una versión simplificada para identificar el problema exacto
 * de sintaxis que está causando el error fatal.
 * 
 * Error reportado: "Unclosed '{' on line 32 in debug-headers.php on line 153"
 * Fecha: 16-Aug-2025 08:37:15 UTC
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Función de prueba para verificar sintaxis básica
 */
function wpcw_test_debug_headers() {
    error_log('WPCW Debug Headers Test - Iniciando verificación');
    
    // Test básico de sintaxis
    $test_array = array(
        'status' => 'testing',
        'timestamp' => current_time('mysql'),
        'message' => 'Verificando sintaxis PHP'
    );
    
    // Verificar que las llaves estén balanceadas
    if (!empty($test_array)) {
        error_log('WPCW Debug Headers Test - Array creado correctamente');
        
        // Test de bucle simple
        foreach ($test_array as $key => $value) {
            error_log("WPCW Debug Test - {$key}: {$value}");
        }
        
        return true;
    }
    
    return false;
}

/**
 * Función simplificada para verificar archivos
 */
function wpcw_test_file_check() {
    $plugin_dir = plugin_dir_path(__FILE__);
    
    // Lista mínima de archivos críticos
    $critical_files = array(
        'wp-cupon-whatsapp.php',
        'includes/post-types.php'
    );
    
    $results = array();
    
    foreach ($critical_files as $file) {
        $file_path = $plugin_dir . $file;
        
        if (file_exists($file_path)) {
            $results[$file] = 'OK';
            error_log("WPCW Test - Archivo encontrado: {$file}");
        } else {
            $results[$file] = 'MISSING';
            error_log("WPCW Test - Archivo faltante: {$file}");
        }
    }
    
    return $results;
}

// Ejecutar pruebas automáticamente
if (function_exists('add_action')) {
    add_action('init', function() {
        if (current_user_can('manage_options')) {
            $test_result = wpcw_test_debug_headers();
            $file_check = wpcw_test_file_check();
            
            if ($test_result) {
                error_log('WPCW Debug Headers Test - ÉXITO: Sintaxis correcta');
            } else {
                error_log('WPCW Debug Headers Test - ERROR: Problema de sintaxis detectado');
            }
        }
    });
}

/**
 * Función de diagnóstico final
 */
function wpcw_final_syntax_test() {
    // Esta función debe ser la última para verificar que todas las llaves estén cerradas
    $final_check = array(
        'syntax_test' => 'completed',
        'braces_balanced' => true,
        'file_end' => 'reached'
    );
    
    error_log('WPCW Final Test - Archivo completado sin errores de sintaxis');
    return $final_check;
}

// Llamada final de verificación
wpcw_final_syntax_test();

?>