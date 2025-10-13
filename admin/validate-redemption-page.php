<?php
/**
 * WPCW - Validate Redemption Page
 *
 * This file renders the page for staff to validate a beneficiary's redemption code.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Renders the content of the validation page.
 */
function wpcw_render_validate_redemption_page() {
    ?>
    <div class="wrap wpcw-validate-wrap">
        <h1><span class="dashicons dashicons-qr-code"></span> <?php _e( 'Validar Canje de Cupón', 'wp-cupon-whatsapp' ); ?></h1>
        <p><?php _e( 'Introduce el código de canje que te muestra el beneficiario para validarlo.', 'wp-cupon-whatsapp' ); ?></p>

        <div id="wpcw-validation-form">
            <input type="text" id="wpcw_redemption_code" name="wpcw_redemption_code" placeholder="Ej: B4C8" style="width: 200px; text-align: center; font-size: 24px; letter-spacing: 4px;" maxlength="4">
            <button type="button" id="wpcw_validate_code_btn" class="button button-primary button-large"><?php _e( 'Validar Código', 'wp-cupon-whatsapp' ); ?></button>
            <span class="spinner"></span>
        </div>

        <div id="wpcw-validation-result" style="margin-top: 20px; font-size: 18px;"></div>
    </div>
    <?php
}

/**
 * Adds the validation page to a menu, accessible by staff.
 */
function wpcw_add_validate_redemption_menu() {
    // This capability is defined in our WPCW_Roles_Manager class
    $capability = 'redeem_coupons'; // Only users who can redeem coupons

    add_submenu_page(
        'wpcw-main-dashboard',                  // Parent slug
        __( 'Validar Canje', 'wp-cupon-whatsapp' ),    // Page title
        __( 'Validar Canje', 'wp-cupon-whatsapp' ),    // Menu title
        $capability,                            // Capability required
        'wpcw-validate-redemption',             // Menu slug
        'wpcw_render_validate_redemption_page', // Callback
        3 // Position
    );
}
add_action( 'admin_menu', 'wpcw_add_validate_redemption_menu', 12 );

/**
 * AJAX handler for validating a redemption code.
 */
function wpcw_ajax_validate_redemption_code() {
    check_ajax_referer( 'wpcw_validate_redemption_nonce', 'nonce' );
    if ( ! current_user_can( 'redeem_coupons' ) ) {
        wp_send_json_error( [ 'message' => 'Permiso denegado.' ] );
    }

    $code = isset( $_POST['code'] ) ? sanitize_text_field( $_POST['code'] ) : '';
    if ( empty( $code ) ) {
        wp_send_json_error( [ 'message' => 'El código no puede estar vacío.' ] );
    }

    // Placeholder: Logic to find and validate the code will go here.
    // For now, we'll simulate a success.
    $is_valid = true; // Simulated validation

    if ( $is_valid ) {
        // Placeholder: Logic to mark coupon as used.
        wp_send_json_success( [ 'message' => 'ÉXITO: Cupón válido y canjeado.' ] );
    } else {
        wp_send_json_error( [ 'message' => 'ERROR: Código inválido o expirado.' ] );
    }
}
add_action( 'wp_ajax_wpcw_validate_redemption_code', 'wpcw_ajax_validate_redemption_code' );

/**
 * Enqueue script for the validation page.
 */
function wpcw_enqueue_validation_page_scripts( $hook ) {
    // Check if we're on the validation page
    $screen = get_current_screen();
    if ( ! $screen || strpos( $screen->id, 'wpcw-validate-redemption' ) === false ) {
        return;
    }

    wp_enqueue_script( 'wpcw-validation-script', false );
    wp_add_inline_script( 'wpcw-validation-script', "
        jQuery(document).ready(function($) {
            $('#wpcw_validate_code_btn').on('click', function() {
                const btn = $(this);
                const code = $('#wpcw_redemption_code').val();
                const resultDiv = $('#wpcw_validation_result');
                const spinner = btn.siblings('.spinner');

                resultDiv.hide();
                spinner.addClass('is-active');
                btn.prop('disabled', true);

                $.post(ajaxurl, {
                    action: 'wpcw_validate_redemption_code',
                    nonce: '" . wp_create_nonce( 'wpcw_validate_redemption_nonce' ) . "',
                    code: code
                }, function(response) {
                    if (response.success) {
                        resultDiv.css('color', 'green').html(response.data.message).show();
                    } else {
                        resultDiv.css('color', 'red').html(response.data.message).show();
                    }
                }).always(function() {
                    spinner.removeClass('is-active');
                    btn.prop('disabled', false);
                });
            });
        });
    " );
}
add_action( 'admin_enqueue_scripts', 'wpcw_enqueue_validation_page_scripts' );

