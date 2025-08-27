<?php
/**
 * Fix Headers Already Sent Issues
 * This file implements a comprehensive solution to prevent header issues
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Start output buffering very early to prevent header issues
 */
function wpcw_start_output_buffering() {
    // Only start if not already started
    if ( ! ob_get_level() ) {
        ob_start();
    }
}

/**
 * Clean and flush output buffer safely
 */
function wpcw_clean_output_buffer() {
    if ( ob_get_level() ) {
        $output = ob_get_clean();

        // Log any unexpected output for debugging
        if ( trim( $output ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'WPCW: Cleaned unexpected output: ' . substr( $output, 0, 200 ) );
        }

        // Restart output buffering
        ob_start();
    }
}

/**
 * Handle output buffering for admin pages
 */
function wpcw_handle_admin_output_buffering() {
    // Clean any existing output
    wpcw_clean_output_buffer();

    // Start fresh output buffering
    wpcw_start_output_buffering();
}

/**
 * Flush output buffer at the end of admin page load
 */
function wpcw_flush_admin_output() {
    if ( ob_get_level() ) {
        ob_end_flush();
    }
}

/**
 * Prevent headers already sent by cleaning output early
 */
function wpcw_prevent_headers_sent() {
    // Check if headers have been sent
    if ( headers_sent( $file, $line ) ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'WPCW: Headers already sent from ' . $file . ' at line ' . $line );
        }
        return;
    }

    // Clean any output that might have been generated
    wpcw_clean_output_buffer();
}

/**
 * Initialize output buffering system
 */
function wpcw_init_output_buffering() {
    // Start output buffering as early as possible
    wpcw_start_output_buffering();

    // Hook into various WordPress actions to manage output
    add_action( 'init', 'wpcw_prevent_headers_sent', 0 );
    add_action( 'admin_init', 'wpcw_handle_admin_output_buffering', 0 );
    add_action( 'wp_loaded', 'wpcw_prevent_headers_sent', 0 );

    // Clean output before admin notices
    add_action( 'admin_notices', 'wpcw_prevent_headers_sent', 0 );

    // Flush output at the end
    add_action( 'admin_footer', 'wpcw_flush_admin_output', 999 );
    add_action( 'wp_footer', 'wpcw_flush_admin_output', 999 );
}

/**
 * Safe echo function that checks headers
 */
function wpcw_safe_echo( $content ) {
    if ( ! headers_sent() ) {
        echo $content;
    } elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'WPCW: Attempted to output content after headers sent: ' . substr( $content, 0, 100 ) );
    }
}

/**
 * Safe output function for admin notices
 */
function wpcw_safe_admin_notice( $content, $type = 'info' ) {
    if ( headers_sent() ) {
        return;
    }

    ob_start();
    ?>
    <div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
        <?php echo $content; ?>
    </div>
    <?php
    $notice = ob_get_clean();
    echo $notice;
}

// Initialize the output buffering system
// PROBLEMA IDENTIFICADO: Esta línea causa que las páginas muestren HTML sin procesar
// wpcw_init_output_buffering();

/**
 * Override WordPress functions that might cause issues
 */
if ( ! function_exists( 'wpcw_wp_redirect_safe' ) ) {
    function wpcw_wp_redirect_safe( $location, $status = 302 ) {
        if ( headers_sent() ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'WPCW: Cannot redirect, headers already sent' );
            }
            return false;
        }

        // Clean any output before redirect
        wpcw_clean_output_buffer();

        return wp_redirect( $location, $status );
    }
}

/**
 * Log function for debugging header issues
 */
function wpcw_log_header_status( $context = '' ) {
    if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
        return;
    }

    $status   = headers_sent( $file, $line ) ? 'SENT' : 'NOT SENT';
    $ob_level = ob_get_level();

    error_log(
        sprintf(
            'WPCW Header Status [%s]: %s | OB Level: %d | File: %s | Line: %d',
            $context,
            $status,
            $ob_level,
            $file ?: 'N/A',
            $line ?: 0
        )
    );
}

// Log header status at key points
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    add_action(
        'plugins_loaded',
        function () {
            wpcw_log_header_status( 'plugins_loaded' );
        },
        1
    );
    add_action(
        'init',
        function () {
            wpcw_log_header_status( 'init' );
        },
        1
    );
    add_action(
        'admin_init',
        function () {
            wpcw_log_header_status( 'admin_init' );
        },
        1
    );
    add_action(
        'admin_notices',
        function () {
            wpcw_log_header_status( 'admin_notices' );
        },
        0
    );
}