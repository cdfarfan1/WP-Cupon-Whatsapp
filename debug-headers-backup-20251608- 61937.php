<?php
/**
 * Debug script to check for header issues
 * This file helps identify potential causes of "headers already sent" errors
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check for whitespace or output before PHP opening tags
 */
function wpcw_debug_headers() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $plugin_dir = plugin_dir_path(__FILE__);
    $files_to_check = array(
        'wp-cupon-whatsapp.php',
        'admin/interactive-forms.php',
        'admin/meta-boxes.php',
        'admin/admin-menu.php',
        'includes/post-types.php',
        'includes/roles.php'
    );
    
    $issues = array();
    
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
        
        // Check for echo/print statements outside of functions
        $lines = explode("\n", $content);
        $in_function = false;
        $brace_count = 0;
        
        foreach ($lines as $line_num => $line) {
            $line = trim($line);
            
            // Skip comments and empty lines
            if (empty($line) || strpos($line, '//') === 0 || strpos($line, '/*') === 0) {
                continue;
            }
            
            // Track function/class boundaries
            if (preg_match('/^(function|class)\s+/', $line)) {
                $in_function = true;
                $brace_count = 0;
            }
            
            $brace_count += substr_count($line, '{') - substr_count($line, '}');
            
            if ($in_function && $brace_count <= 0) {
                $in_function = false;
            }
            
            // Check for output statements outside functions
            if (!$in_function && (strpos($line, 'echo ') !== false || strpos($line, 'print ') !== false)) {
                $issues[] = "Output statement outside function in $file at line " . ($line_num + 1) . ": $line";
            }
        }
    } // Added missing closing brace for the main foreach loop

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
function wpcw_add_debug_admin_notice() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $issues = wpcw_debug_headers();
    
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

// Only run debug in admin and if debug is enabled
if (is_admin() && (defined('WP_DEBUG') && WP_DEBUG)) {
    add_action('admin_notices', 'wpcw_add_debug_admin_notice');
}

/**
 * Check output buffering status
 */
function wpcw_check_output_buffering() {
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

// Run output buffering check on init
if (is_admin() && (defined('WP_DEBUG') && WP_DEBUG)) {
    add_action('init', 'wpcw_check_output_buffering', 1);
}