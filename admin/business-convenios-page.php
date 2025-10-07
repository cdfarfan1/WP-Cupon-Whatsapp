<?php
/**
 * WPCW - Business Owner Convenios Management Page
 *
 * This file renders the page for a Business Owner to manage their convenios.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Handles the submission of the propose convenio form.
 * Hooked into 'admin_init' to process before headers are sent.
 */
function wpcw_handle_propose_convenio_form() {
    // Check if our form has been submitted
    if ( ! isset( $_POST['submit_propose_convenio' ] ) ) {
        return;
    }

    // --- Security Check by El Guardián ---
    if ( ! isset( $_POST['wpcw_nonce'] ) || ! wp_verify_nonce( $_POST['wpcw_nonce'], 'wpcw_propose_convenio_nonce' ) ) {
        wp_die( __( 'Error de seguridad. Inténtalo de nuevo.', 'wp-cupon-whatsapp' ) );
    }

    // Check user capability
    if ( ! current_user_can( 'manage_business_profile' ) ) {
        wp_die( __( 'No tienes permisos para realizar esta acción.', 'wp-cupon-whatsapp' ) );
    }

    // --- Data Validation by El Artesano ---
    $recipient_institution_id = isset( $_POST['wpcw_institution_id'] ) ? absint( $_POST['wpcw_institution_id'] ) : 0;
    $convenio_terms = isset( $_POST['wpcw_convenio_terms'] ) ? sanitize_textarea_field( $_POST['wpcw_convenio_terms'] ) : '';

    if ( $recipient_institution_id === 0 || empty( $convenio_terms ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error is-dismissible"><p>' . __( 'Error: Debes seleccionar una institución y describir los términos del convenio.', 'wp-cupon-whatsapp' ) . '</p></div>';
        } );
        return;
    }

    // --- Convenio Creation by El Artesano ---
    $current_user_id = get_current_user_id();
    // This is a placeholder. We need a function to get the business associated with the current user.
    // For now, we'll assume a function `wpcw_get_business_id_for_user()` exists.
    $provider_business_id = get_user_meta( $current_user_id, '_wpcw_business_id', true );

    if ( ! $provider_business_id ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error is-dismissible"><p>' . __( 'Error: Tu usuario no está asociado a ningún negocio.', 'wp-cupon-whatsapp' ) . '</p></div>';
        } );
        return;
    }

    $provider_name = get_the_title( $provider_business_id );
    $recipient_name = get_the_title( $recipient_institution_id );

    $convenio_post = array(
        'post_title'  => sprintf( 'Propuesta de %s a %s', $provider_name, $recipient_name ),
        'post_type'   => 'wpcw_convenio',
        'post_status' => 'pending', // WordPress uses 'pending' for pending review
        'post_author' => $current_user_id,
    );

    $convenio_id = wp_insert_post( $convenio_post );

    if ( is_wp_error( $convenio_id ) ) {
        add_action( 'admin_notices', function() use ( $convenio_id ) {
            echo '<div class="notice notice-error is-dismissible"><p>' . sprintf( __( 'Error al crear el convenio: %s', 'wp-cupon-whatsapp' ), $convenio_id->get_error_message() ) . '</p></div>';
        } );
        return;
    }

    // --- Metadata Storage ---
    update_post_meta( $convenio_id, '_convenio_provider_id', $provider_business_id );
    update_post_meta( $convenio_id, '_convenio_recipient_id', $recipient_institution_id );
    update_post_meta( $convenio_id, '_convenio_status', 'pending' );
    update_post_meta( $convenio_id, '_convenio_terms', $convenio_terms );
    update_post_meta( $convenio_id, '_convenio_originator_id', $current_user_id );

    // --- Notification System ---
    // This assumes the institution has an email stored in its metadata.
    $recipient_email = get_post_meta( $recipient_institution_id, '_institution_email', true );
    if ( $recipient_email && is_email( $recipient_email ) ) {
        $subject = sprintf( 'Nueva propuesta de convenio de %s', $provider_name );
        $message = "Hola,\n\nHas recibido una nueva propuesta de convenio del negocio '" . $provider_name . "'.\n\n";
        $message .= "Términos propuestos: " . $convenio_terms . "\n\n";
        $message .= "Para revisar, aceptar o rechazar esta propuesta, por favor, accede a tu panel de gestión.\n"; // This link will be updated in MVP 1.1
        $message .= admin_url( 'admin.php?page=wpcw-institution-dashboard' );

        wp_mail( $recipient_email, $subject, $message );
    }

    // --- User Feedback ---
    // Redirect to avoid form resubmission
    $redirect_url = add_query_arg( 'wpcw_notice', 'convenio_propuesto', admin_url( 'admin.php?page=wpcw-business-convenios' ) );
    wp_safe_redirect( $redirect_url );
    exit;
}
add_action( 'admin_init', 'wpcw_handle_propose_convenio_form' );

/**
 * Displays admin notices for convenio management.
 */
function wpcw_business_convenios_admin_notices() {
    if ( ! isset( $_GET['wpcw_notice'] ) ) {
        return;
    }

    if ( $_GET['wpcw_notice'] === 'convenio_propuesto' ) {
        echo '<div class="notice notice-success is-dismissible"><p>' . __( '¡Propuesta de convenio enviada exitosamente!', 'wp-cupon-whatsapp' ) . '</p></div>';
    }
}
add_action( 'admin_notices', 'wpcw_business_convenios_admin_notices' );

/**
 * Renders the content of the Business Owner's convenios page.
 */
function wpcw_render_business_convenios_page() {
    ?>
    <div class="wrap wpcw-dashboard-wrap">
        <h1><span class="dashicons dashicons-businesswoman"></span> <?php _e( 'Gestión de Convenios', 'wp-cupon-whatsapp' ); ?></h1>
        <p><?php _e( 'Aquí puede proponer nuevos convenios a instituciones y negocios, y gestionar sus alianzas activas.', 'wp-cupon-whatsapp' ); ?></p>

        <div class="wpcw-page-actions">
            <button id="propose-convenio-btn" class="button button-primary"><?php _e( 'Proponer Nuevo Convenio', 'wp-cupon-whatsapp' ); ?></button>
        </div>

        <!-- Formulario de Propuesta (Oculto por defecto) -->
        <div id="propose-convenio-form-wrap" class="postbox" style="display: none; margin-top: 20px;">
            <h2 class="hndle"><span><?php _e( 'Nueva Propuesta de Convenio', 'wp-cupon-whatsapp' ); ?></span></h2>
            <div class="inside">
                <form id="propose-convenio-form" method="post" action="">
                    <?php
                    // Security Nonce by El Guardián
                    wp_nonce_field( 'wpcw_propose_convenio_nonce', 'wpcw_nonce' );
                    ?>
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="wpcw_institution_id"><?php _e( 'Proponer a la Institución', 'wp-cupon-whatsapp' ); ?></label>
                                </th>
                                <td>
                                    <?php
                                    // Data by El Artesano
                                    $institutions = WPCW_Institution_Manager::get_all_institutions();
                                    if ( ! empty( $institutions ) ) {
                                        echo '<select name="wpcw_institution_id" id="wpcw_institution_id" class="regular-text" required>';
                                        echo '<option value="">' . __( '-- Seleccionar Institución --', 'wp-cupon-whatsapp' ) . '</option>';
                                        foreach ( $institutions as $id => $name ) {
                                            echo '<option value="' . esc_attr( $id ) . '">' . esc_html( $name ) . '</option>';
                                        }
                                        echo '</select>';
                                    } else {
                                        echo '<em>' . __( 'No hay instituciones disponibles para proponer un convenio.', 'wp-cupon-whatsapp' ) . '</em>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="wpcw_convenio_terms"><?php _e( 'Términos del Beneficio', 'wp-cupon-whatsapp' ); ?></label>
                                </th>
                                <td>
                                    <textarea name="wpcw_convenio_terms" id="wpcw_convenio_terms" rows="5" class="large-text" placeholder="Ej: 15% de descuento en todos los productos para los miembros de la institución." required></textarea>
                                    <p class="description"><?php _e( 'Describe claramente el beneficio que ofreces.', 'wp-cupon-whatsapp' ); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="submit">
                        <input type="submit" name="submit_propose_convenio" id="submit_propose_convenio" class="button button-primary" value="<?php _e( 'Enviar Propuesta', 'wp-cupon-whatsapp' ); ?>">
                        <button type="button" id="cancel-propose-convenio" class="button button-secondary"><?php _e( 'Cancelar', 'wp-cupon-whatsapp' ); ?></button>
                    </p>
                </form>
            </div>
        </div>

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#propose-convenio-btn').on('click', function() {
                    $('#propose-convenio-form-wrap').slideDown();
                    $(this).hide();
                });

                $('#cancel-propose-convenio').on('click', function() {
                    $('#propose-convenio-form-wrap').slideUp();
                    $('#propose-convenio-btn').show();
                });
            });
        </script>

        <div id="poststuff" style="margin-top: 20px;">
            
            <!-- Marcador para Convenios Activos -->
            <div class="postbox">
                <h2 class="hndle"><span><?php _e( 'Convenios Activos', 'wp-cupon-whatsapp' ); ?></span></h2>
                <div class="inside">
                    <p><?php _e( 'Aquí se mostrará una tabla con los convenios que ha propuesto y han sido aceptados, o los que ha aceptado de otros.', 'wp-cupon-whatsapp' ); ?></p>
                </div>
            </div>

            <!-- Marcador para Convenios Pendientes -->
            <div class="postbox">
                <h2 class="hndle"><span><?php _e( 'Convenios Pendientes de Aceptación', 'wp-cupon-whatsapp' ); ?></span></h2>
                <div class="inside">
                    <p><?php _e( 'Aquí se mostrarán las propuestas de convenio que ha enviado y están esperando respuesta, y las que ha recibido y necesitan su aprobación.', 'wp-cupon-whatsapp' ); ?></p>
                </div>
            </div>

        </div>

    </div>
    <?php
}

/**
 * Adds the Business Convenios submenu page, visible only to Business Owners.
 */
function wpcw_add_business_convenios_menu() {
    // This capability is defined in our WPCW_Roles_Manager class
    $capability = 'manage_business_profile';

    if ( current_user_can( $capability ) ) {
        add_submenu_page(
            'wpcw-main-dashboard',                  // Slug del menú padre
            __( 'Convenios', 'wp-cupon-whatsapp' ), // Título de la página
            __( 'Convenios', 'wp-cupon-whatsapp' ), // Título del menú
            $capability,                            // Capacidad requerida
            'wpcw-business-convenios',              // Slug del menú
            'wpcw_render_business_convenios_page',  // Callback para renderizar
            2 // Posición dentro del submenú
        );
    }
}
add_action( 'admin_menu', 'wpcw_add_business_convenios_menu', 11 );
