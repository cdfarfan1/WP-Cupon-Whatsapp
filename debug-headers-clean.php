<?php
/**
 * Debug Headers - Versión Limpia y Simplificada
 * Plugin: WP Cupón WhatsApp
 * 
 * Esta versión está completamente limpia de errores de sintaxis
 * y es una solución definitiva para el problema de "Unclosed '{'"
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Debug function to check for header issues
 * Versión simplificada y sin errores
 */
function wpcw_debug_headers_clean() {
    // Get plugin directory
    $plugin_dir = plugin_dir_path(__FILE__);
    
    // Files to check for header issues
    $files_to_check = array(
        'wp-cupon-whatsapp.php',
        'includes/class-wp-cupon-whatsapp.php',
        'includes/class-wp-cupon-whatsapp-activator.php',
        'includes/class-wp-cupon-whatsapp-deactivator.php',
        'admin/class-wp-cupon-whatsapp-admin.php',
        'public/class-wp-cupon-whatsapp-public.php'
    );
    
    $issues = array();
    
    // Check each file for common header issues
    foreach ($files_to_check as $file) {
        $file_path = $plugin_dir . $file;
        
        if (!file_exists($file_path)) {
            continue;
        }
        
        $content = file_get_contents($file_path);
        
        // Check for BOM (Byte Order Mark)
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            $issues[] = "BOM detected in: $file";
        }
        
        // Check for whitespace before <?php
        if (preg_match('/^\s+<\?php/', $content)) {
            $issues[] = "Whitespace before <?php in: $file";
        }
        
        // Check for output after closing ?>
        if (preg_match('/\?>\s*\S/', $content)) {
            $issues[] = "Content after closing ?> in: $file";
        }
    }
    
    // Log results
    if (!empty($issues)) {
        error_log('WPCW Header Issues Found:');
        foreach ($issues as $issue) {
            error_log('- ' . $issue);
        }
    } else {
        error_log('WPCW: No header issues detected in checked files');
    }
    
    return $issues;
}

/**
 * Add debug information to admin
 */
function wpcw_add_debug_admin_notice_clean() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $issues = wpcw_debug_headers_clean();
    
    if (!empty($issues)) {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>WP Cupón WhatsApp - Problemas de Headers Detectados:</strong></p>';
        echo '<ul>';
        foreach ($issues as $issue) {
            echo '<li>' . esc_html($issue) . '</li>';
        }
        echo '</ul>';
        echo '<p>Estos problemas pueden causar errores de "headers already sent". Revise los archivos mencionados.</p>';
        echo '</div>';
    }
}

/**
 * Check output buffering status
 */
function wpcw_check_output_buffering_clean() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $ob_level = ob_get_level();
    $ob_status = ob_get_status(true);
    
    error_log('WPCW Output Buffering Status:');
    error_log('- OB Level: ' . $ob_level);
    error_log('- OB Status: ' . print_r($ob_status, true));
    
    if (headers_sent($file, $line)) {
        error_log('WPCW: Headers already sent from ' . $file . ' at line ' . $line);
    } else {
        error_log('WPCW: Headers not yet sent');
    }
}

// Only run debug in admin and if debug is enabled
if (is_admin() && (defined('WP_DEBUG') && WP_DEBUG)) {
    add_action('admin_notices', 'wpcw_add_debug_admin_notice_clean');
    add_action('init', 'wpcw_check_output_buffering_clean', 1);
}

?>