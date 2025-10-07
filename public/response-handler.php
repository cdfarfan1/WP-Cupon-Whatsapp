<?php
/**
 * WPCW - Convenio Response Handler
 *
 * Handles the virtual page for accepting or rejecting a convenio proposal.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WPCW_Response_Handler {

    /**
     * Initialize all hooks for the response handler.
     */
    public static function init() {
        add_action( 'init', [ __CLASS__, 'add_rewrite_rule' ] );
        add_filter( 'query_vars', [ __CLASS__, 'register_query_var' ] );
        add_action( 'template_redirect', [ __CLASS__, 'handle_request' ] );
    }

    /**
     * Add the rewrite rule for our virtual page.
     */
    public static function add_rewrite_rule() {
        add_rewrite_rule( 'responder-convenio/?$', 'index.php?wpcw_convenio_response=1', 'top' );
    }

    /**
     * Register our custom query variable with WordPress.
     */
    public static function register_query_var( $vars ) {
        $vars[] = 'wpcw_convenio_response';
        return $vars;
    }

    /**
     * Intercept the request if it's for our virtual page.
     */
    public static function handle_request() {
        // Check if our query var is present
        if ( ! get_query_var( 'wpcw_convenio_response' ) ) {
            return;
        }

        // --- Logic by El Artesano, supervised by El Guardián ---
        $convenio_id = isset( $_GET['convenio_id'] ) ? absint( $_GET['convenio_id'] ) : 0;
        $token = isset( $_GET['token'] ) ? sanitize_text_field( $_GET['token'] ) : '';

        // 1. Basic validation
        if ( ! $convenio_id || empty( $token ) ) {
            wp_die( 'Información insuficiente para procesar la solicitud.', 'Enlace Inválido' );
        }

        // 2. Get the stored token and status
        $stored_token = get_post_meta( $convenio_id, '_convenio_response_token', true );
        $convenio_status = get_post_status( $convenio_id );

        // 3. Security and validity checks
        if ( empty( $stored_token ) || ! hash_equals( $stored_token, $token ) ) {
            wp_die( 'El enlace de respuesta no es válido o ha expirado. (Error: Token mismatch)', 'Enlace Inválido' );
        }

        if ( $convenio_status !== 'pending' ) {
            wp_die( 'Este convenio ya ha sido procesado (aceptado o rechazado). El enlace ya no es válido.', 'Convenio ya Procesado' );
        }

        // --- UI by La Diseñadora de Experiencias ---
        // If all checks pass, render the response page.
        self::render_response_page( $convenio_id );
        
        // Stop WordPress from loading any other template
        exit;
    }

    /**
     * Renders the actual response page UI.
     * @param int $convenio_id The ID of the convenio to display.
     */
    private static function render_response_page( $convenio_id ) {
        // For now, this is a placeholder. La Diseñadora will build the real UI here.
        $convenio = get_post( $convenio_id );
        $provider_id = get_post_meta( $convenio_id, '_convenio_provider_id', true );
        $provider_name = get_the_title( $provider_id );
        $terms = get_post_meta( $convenio_id, '_convenio_terms', true );

        // Basic HTML structure
        wp_head();
        echo '<div style="font-family: sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; box-shadow: 0 0 10px #eee;">';
        echo '<h1>Revisión de Propuesta de Convenio</h1>';
        echo '<p>Has recibido una propuesta del siguiente negocio:</p>';
        echo '<h2 style="background: #f9f9f9; padding: 15px; border-left: 5px solid #0073aa;">' . esc_html( $provider_name ) . '</h2>';
        echo '<h3>Términos Propuestos:</h3>';
        echo '<p style="font-style: italic;">' . nl2br( esc_html( $terms ) ) . '</p>';
        echo '<hr>';
        echo '<p>¿Deseas aceptar este convenio?</p>';
        echo '<div style="margin-top: 20px;">';
        echo '<a href="#" style="padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;">Aceptar Convenio</a>';
        echo '<a href="#" style="padding: 10px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px;">Rechazar Convenio</a>';
        echo '</div>';
        echo '</div>';
        wp_footer();
    }
}

WPCW_Response_Handler::init();
