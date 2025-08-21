<?php
/**
 * Debug Headers - WP Cupon WhatsApp
 * Versión completamente reescrita para eliminar errores de sintaxis
 */

// Prevenir ejecución directa
if (!defined('ABSPATH')) {
    exit;
}

// Prevenir múltiples inclusiones
if (defined('WPCW_DEBUG_HEADERS_LOADED')) {
    return;
}
define('WPCW_DEBUG_HEADERS_LOADED', true);

/**
 * Función principal de debug de headers
 */
function wpcw_debug_headers() {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return array();
    }
    
    $plugin_dir = WPCW_PLUGIN_DIR;
    $files_to_check = array(
        'wp-cupon-whatsapp.php',
        'includes/post-types.php',
        'includes/taxonomies.php',
        'admin/admin-menu.php',
        'admin/settings-page.php'
    );
    
    $issues = array();
    
    foreach ($files_to_check as $file) {
        $file_path = $plugin_dir . $file;
        
        if (!file_exists($file_path)) {
            continue;
        }
        
        $content = file_get_contents($file_path);
        if ($content === false) {
            continue;
        }
        
        // Verificar BOM
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            $issues[] = "BOM detected in: $file";
        }
        
        // Verificar espacios antes de <?php
        if (preg_match('/^\s+<\?php/', $content)) {
            $issues[] = "Whitespace before <?php in: $file";
        }
        
        // Verificar contenido después de ?>
        if (preg_match('/\?>\s*\S/', $content)) {
            $issues[] = "Content after closing ?> in: $file";
        }
    }
    
    if (!empty($issues)) {
        error_log('WPCW Header Issues Found: ' . implode(', ', $issues));
    }
    
    return $issues;
}

/**
 * Mostrar avisos de administración
 */
function wpcw_add_debug_admin_notice() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $issues = wpcw_debug_headers();
    
    if (!empty($issues)) {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>WP Cupón WhatsApp - Problemas de Headers:</strong></p>';
        echo '<ul>';
        foreach ($issues as $issue) {
            echo '<li>' . esc_html($issue) . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }
}

/**
 * Verificar estado del buffer de salida
 */
function wpcw_check_output_buffering() {
    if (!current_user_can('manage_options') || !defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }
    
    if (headers_sent($file, $line)) {
        error_log('WPCW: Headers already sent from ' . $file . ' at line ' . $line);
    }
}

// Registrar hooks solo en admin y con debug activo
if (is_admin() && defined('WP_DEBUG') && WP_DEBUG) {
    add_action('admin_notices', 'wpcw_add_debug_admin_notice');
    add_action('init', 'wpcw_check_output_buffering', 1);
}

/**
 * Función de utilidad para verificación manual
 */
function wpcw_manual_header_check() {
    if (!current_user_can('manage_options')) {
        return false;
    }
    
    return wpcw_debug_headers();
}

?>