<?php
/**
 * WPCW - Utility Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Retrieves the encryption key from its secure file.
 *
 * @return string|false The encryption key, or false on failure.
 */
function wpcw_get_encryption_key() {
    $key_path = get_option( 'wpcw_encryption_key_path' );
    if ( empty( $key_path ) || ! file_exists( $key_path ) ) {
        // Attempt to regenerate if missing (self-healing)
        if ( class_exists( 'WPCW_Installer' ) ) {
            WPCW_Installer::setup_encryption();
            $key_path = get_option( 'wpcw_encryption_key_path' );
        }
        if ( empty( $key_path ) || ! file_exists( $key_path ) ) {
            error_log( 'WPCW Critical Error: Encryption key file not found and could not be regenerated.' );
            return false;
        }
    }
    return file_get_contents( $key_path );
}
