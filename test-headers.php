<?php
/**
 * Test file to verify header fixes are working
 * This file can be temporarily included to test header status
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Test function to verify headers are working correctly
 */
function wpcw_test_headers() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Log current header status
    $headers_sent = headers_sent($file, $line);
    $ob_level = ob_get_level();
    
    $message = sprintf(
        'WPCW Header Test - Headers: %s | OB Level: %d | File: %s | Line: %d',
        $headers_sent ? 'SENT' : 'NOT SENT',
        $ob_level,
        $file ?: 'N/A',
        $line ?: 0
    );
    
    error_log($message);
    
    // Show admin notice with test results
    if (!$headers_sent && function_exists('wpcw_safe_admin_notice')) {
        $notice_content = '<p><strong>🧪 Test de Headers:</strong></p>';
        $notice_content .= '<ul>';
        $notice_content .= '<li>Estado de Headers: ' . ($headers_sent ? '❌ Ya enviados' : '✅ No enviados') . '</li>';
        $notice_content .= '<li>Nivel de Output Buffer: ' . $ob_level . '</li>';
        $notice_content .= '<li>Archivo: ' . ($file ?: 'N/A') . '</li>';
        $notice_content .= '<li>Línea: ' . ($line ?: 'N/A') . '</li>';
        $notice_content .= '</ul>';
        
        wpcw_safe_admin_notice($notice_content, $headers_sent ? 'error' : 'success');
    }
}

// Only run test in debug mode
if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('admin_notices', 'wpcw_test_headers', 5);
}

/**
 * Test AJAX functionality
 */
function wpcw_test_ajax_headers() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'wpcw_test_headers')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    $headers_sent = headers_sent($file, $line);
    
    wp_send_json_success([
        'headers_sent' => $headers_sent,
        'file' => $file ?: 'N/A',
        'line' => $line ?: 0,
        'ob_level' => ob_get_level(),
        'message' => $headers_sent ? 'Headers already sent' : 'Headers OK'
    ]);
}

// Register AJAX handler for testing
if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('wp_ajax_wpcw_test_headers', 'wpcw_test_ajax_headers');
}