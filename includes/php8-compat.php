<?php
/**
 * PHP 8.x Compatibility Layer
 *
 * Fixes deprecated warnings when null values are passed to string functions
 *
 * @package WP_Cupon_WhatsApp
 * @since 1.5.1
 * @author El Guardián de la Seguridad + Sarah Thompson (PHP Expert)
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Suppress specific PHP 8.x deprecated warnings
 *
 * These warnings come from WordPress core when null values are passed
 * to string functions. This is a known WordPress core issue that will be
 * fixed in future WordPress versions.
 *
 * @link https://core.trac.wordpress.org/ticket/53635
 */
function wpcw_php8_error_handler( $errno, $errstr, $errfile, $errline ) {
    // Only handle deprecation warnings
    if ( $errno !== E_DEPRECATED ) {
        return false; // Let PHP handle other errors normally
    }

    // List of known safe deprecations from WordPress core
    $safe_deprecations = array(
        'strpos(): Passing null to parameter',
        'str_replace(): Passing null to parameter',
        'trim(): Passing null to parameter',
        'strlen(): Passing null to parameter',
        'substr(): Passing null to parameter',
    );

    // Check if this is a known safe deprecation
    foreach ( $safe_deprecations as $pattern ) {
        if ( strpos( $errstr, $pattern ) !== false ) {
            // Check if it's from WordPress core
            if ( strpos( $errfile, 'wp-includes' ) !== false || strpos( $errfile, 'wp-admin' ) !== false ) {
                // Suppress this warning - it's a known WordPress core issue
                return true;
            }
        }
    }

    // Let PHP handle any other deprecations normally
    return false;
}

/**
 * Initialize PHP 8.x compatibility layer
 *
 * Only activate in admin area to avoid affecting frontend performance
 */
function wpcw_init_php8_compat() {
    // Only in admin
    if ( ! is_admin() ) {
        return;
    }

    // Only if PHP 8.0+
    if ( version_compare( PHP_VERSION, '8.0.0', '<' ) ) {
        return;
    }

    // Set custom error handler
    set_error_handler( 'wpcw_php8_error_handler', E_DEPRECATED );
}
add_action( 'admin_init', 'wpcw_init_php8_compat', 0 ); // Priority 0 - very early

/**
 * Helper function to safely get value with default
 *
 * Prevents null being passed to string functions
 *
 * @param mixed $value The value to check
 * @param string $default Default value if null
 * @return string Safe string value
 */
function wpcw_safe_string( $value, $default = '' ) {
    return ( $value !== null ) ? (string) $value : $default;
}

/**
 * Helper function to safely get array value
 *
 * @param array $array The array
 * @param string|int $key The key
 * @param mixed $default Default value
 * @return mixed The value or default
 */
function wpcw_array_get( $array, $key, $default = null ) {
    if ( ! is_array( $array ) ) {
        return $default;
    }

    return isset( $array[ $key ] ) ? $array[ $key ] : $default;
}
