<?php
/**
 * WP Canje Cupon Whatsapp reCAPTCHA Integration
 *
 * This file will handle the integration of Google reCAPTCHA v2.
 * It will be used in forms like:
 * - WooCommerce Registration Form (if enabled in settings)
 * - Adhesion Application Form (wpcw_solicitud_adhesion_form shortcode)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Function to get reCAPTCHA site key
function wpcw_get_recaptcha_site_key() {
    return get_option( 'wpcw_recaptcha_site_key', '' );
}

// Function to get reCAPTCHA secret key
function wpcw_get_recaptcha_secret_key() {
    return get_option( 'wpcw_recaptcha_secret_key', '' );
}

// Function to check if reCAPTCHA is enabled (both keys are set)
function wpcw_is_recaptcha_enabled() {
    $site_key   = wpcw_get_recaptcha_site_key();
    $secret_key = wpcw_get_recaptcha_secret_key();
    return ! empty( $site_key ) && ! empty( $secret_key );
}

// Function to display the reCAPTCHA widget
function wpcw_display_recaptcha() {
    if ( ! wpcw_is_recaptcha_enabled() ) {
        return;
    }
    $site_key = wpcw_get_recaptcha_site_key();
    echo '<div class="wpcw-recaptcha-container" style="margin-bottom: 15px;">'; // Basic styling
    echo '<div class="g-recaptcha" data-sitekey="' . esc_attr( $site_key ) . '"></div>';
    echo '</div>';
}

// Function to verify the reCAPTCHA response
function wpcw_verify_recaptcha( $token ) {
    if ( ! wpcw_is_recaptcha_enabled() || empty( $token ) ) {
        return false;
    }
    $secret_key = wpcw_get_recaptcha_secret_key();
    $response   = wp_remote_post(
        'https://www.google.com/recaptcha/api/siteverify',
        array(
			'method' => 'POST',
			'body'   => array(
				'secret'   => $secret_key,
				'response' => $token,
				'remoteip' => isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '', // Optional, and ensure it exists
			),
        )
    );

    if ( is_wp_error( $response ) ) {
        // Handle error, maybe log it
        error_log( 'WP Canje Cupon Whatsapp reCAPTCHA verification error: ' . $response->get_error_message() );
        return false;
    }

    $body   = wp_remote_retrieve_body( $response );
    $result = json_decode( $body );

    return isset( $result->success ) && $result->success === true;
}

// Enqueue reCAPTCHA script
function wpcw_enqueue_recaptcha_script() {
    if ( wpcw_is_recaptcha_enabled() ) {
        // A more robust check would be if the specific shortcode is being rendered or specific page is shown.
        // For now, if enabled, we assume it might be used on the frontend.
        // Consider checking if ( !is_admin() && 'your_form_page_condition_here' ) for more targeted enqueuing
        wp_enqueue_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true );
    }
}
add_action( 'wp_enqueue_scripts', 'wpcw_enqueue_recaptcha_script' );

// Hooks to integrate reCAPTCHA into forms will be added here.
// Example: add_action('woocommerce_register_form', 'wpcw_display_recaptcha_on_registration');
// Example: add_action('wpcw_application_form_before_submit', 'wpcw_display_recaptcha_on_application');
