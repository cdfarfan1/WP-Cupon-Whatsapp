<?php
/**
 * Debug script to detect output issues
 * This file helps identify where output is being generated before headers
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Start output buffering very early to catch any output
 * COMMENTED OUT: Function already declared in fix-headers.php
 */
/*
function wpcw_start_output_buffering() {
    if (!ob_get_level()) {
        ob_start();
    }
}
*/

/**
 * Check for any output that might cause header issues
 */
function wpcw_check_early_output() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Check if headers have been sent
    if (headers_sent($file, $line)) {
        error_log('WPCW DEBUG: Headers already sent from ' . $file . ' at line ' . $line);
        
        // Try to get any buffered output
        $output = ob_get_contents();
        if ($output) {
            error_log('WPCW DEBUG: Buffered output found: ' . substr($output, 0, 200) . '...');
        }
    } else {
        error_log('WPCW DEBUG: Headers not yet sent at ' . current_action());
    }
    
    // Log current output buffer level
    error_log('WPCW DEBUG: Output buffer level: ' . ob_get_level());
}

/**
 * Log plugin loading sequence
 */
function wpcw_log_plugin_loading() {
    error_log('WPCW DEBUG: Plugin loading at action: ' . current_action());
    
    // Check for any output
    $output = ob_get_contents();
    if ($output) {
        error_log('WPCW DEBUG: Output detected during plugin loading: ' . substr($output, 0, 100));
    }
}

// Only run in debug mode
if (defined('WP_DEBUG') && WP_DEBUG) {
    // Start output buffering as early as possible
    wpcw_start_output_buffering();
    
    // Hook into various WordPress actions to track when headers are sent
    add_action('plugins_loaded', 'wpcw_check_early_output', 1);
    add_action('init', 'wpcw_check_early_output', 1);
    add_action('wp_loaded', 'wpcw_check_early_output', 1);
    add_action('admin_init', 'wpcw_check_early_output', 1);
    
    // Log plugin loading
    add_action('plugins_loaded', 'wpcw_log_plugin_loading', 1);
}

/**
 * Clean any unwanted output
 */
function wpcw_clean_output() {
    if (ob_get_level()) {
        $output = ob_get_clean();
        if (trim($output)) {
            error_log('WPCW DEBUG: Cleaned unwanted output: ' . substr($output, 0, 200));
        }
        ob_start();
    }
}

// Try to clean output on admin_init if headers haven't been sent
if (is_admin() && defined('WP_DEBUG') && WP_DEBUG) {
    add_action('admin_init', function() {
        if (!headers_sent()) {
            wpcw_clean_output();
        }
    }, 0);
}