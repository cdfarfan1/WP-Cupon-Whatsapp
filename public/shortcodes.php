<?php
/**
 * Shortcodes for WP Canje Cupon Whatsapp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Renders the application form for businesses/institutions.
 *
 * @return string The form HTML.
 */
function wpcw_render_solicitud_adhesion_form() {
    $errors = array();

    if ( isset( $_POST['wpcw_submit_solicitud'] ) ) {
        // Verify Nonce
        if ( ! isset( $_POST['wpcw_solicitud_adhesion_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['wpcw_solicitud_adhesion_nonce'] ), 'wpcw_solicitud_adhesion_action' ) ) {
            $errors[] = __( 'Error de seguridad. Inténtalo de nuevo.', 'wp-cupon-whatsapp' );
        } else {
            // Validate Fields
            $applicant_type = isset( $_POST['wpcw_applicant_type'] ) ? sanitize_text_field( $_POST['wpcw_applicant_type'] ) : '';
            $fantasy_name = isset( $_POST['wpcw_fantasy_name'] ) ? sanitize_text_field( $_POST['wpcw_fantasy_name'] ) : '';
            $legal_name = isset( $_POST['wpcw_legal_name'] ) ? sanitize_text_field( $_POST['wpcw_legal_name'] ) : '';
            $cuit = isset( $_POST['wpcw_cuit'] ) ? sanitize_text_field( $_POST['wpcw_cuit'] ) : '';
            $contact_person = isset( $_POST['wpcw_contact_person'] ) ? sanitize_text_field( $_POST['wpcw_contact_person'] ) : '';
            $email = isset( $_POST['wpcw_email'] ) ? sanitize_email( $_POST['wpcw_email'] ) : '';
            $whatsapp = isset( $_POST['wpcw_whatsapp'] ) ? sanitize_text_field( $_POST['wpcw_whatsapp'] ) : '';
            // Description and address are not validated for emptiness here, but can be added if needed.

            if ( empty( $applicant_type ) ) {
                $errors[] = __( 'Por favor, selecciona el tipo de solicitante.', 'wp-cupon-whatsapp' );
            }
            if ( empty( $fantasy_name ) ) {
                $errors[] = __( 'Por favor, introduce el nombre de fantasía.', 'wp-cupon-whatsapp' );
            }
            if ( empty( $legal_name ) ) {
                $errors[] = __( 'Por favor, introduce el nombre legal.', 'wp-cupon-whatsapp' );
            }
            if ( empty( $cuit ) ) {
                $errors[] = __( 'Por favor, introduce el CUIT.', 'wp-cupon-whatsapp' );
            } elseif ( ! preg_match( '/^[0-9]{10,11}$/', $cuit ) ) { // Basic CUIT validation (10 or 11 digits)
                $errors[] = __( 'Por favor, introduce un CUIT válido (solo números, 10 u 11 dígitos).', 'wp-cupon-whatsapp' );
            }
            if ( empty( $contact_person ) ) {
                $errors[] = __( 'Por favor, introduce el nombre de la persona de contacto.', 'wp-cupon-whatsapp' );
            }
            if ( empty( $email ) ) {
                $errors[] = __( 'Por favor, introduce un email de contacto.', 'wp-cupon-whatsapp' );
            } elseif ( ! is_email( $email ) ) {
                $errors[] = __( 'Por favor, introduce un email de contacto válido.', 'wp-cupon-whatsapp' );
            }
            if ( empty( $whatsapp ) ) {
                $errors[] = __( 'Por favor, introduce un número de WhatsApp.', 'wp-cupon-whatsapp' );
            } elseif ( ! preg_match( '/^[0-9\s\+\(\)-]+$/', $whatsapp ) ) {
                $errors[] = __( 'Por favor, introduce un número de WhatsApp válido.', 'wp-cupon-whatsapp' );
            }

            // If no errors, proceed to create application (not in this step)
            // if ( empty( $errors ) ) {
            //    // Process application...
            // }

            if ( empty( $errors ) ) {
                // Sanitize Data
                // $applicant_type is already sanitized from validation block
                // $fantasy_name is already sanitized
                // $legal_name is already sanitized
                // $cuit is already sanitized
                // $contact_person is already sanitized
                // $email is already sanitized
                // $whatsapp is already sanitized
                $address_main = isset( $_POST['wpcw_address_main'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wpcw_address_main'] ) ) : '';
                $description = isset( $_POST['wpcw_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wpcw_description'] ) ) : '';

                // Prepare Post Data
                $post_data = array(
                    'post_title'  => $fantasy_name, // Using fantasy name as title
                    'post_content' => $description, // Using description as main content
                    'post_type'   => 'wpcw_application',
                    'post_status' => 'publish', // Or 'pending' if manual admin approval is always first
                );

                // Insert Post
                $post_id = wp_insert_post( $post_data );

                if ( ! is_wp_error( $post_id ) && $post_id > 0 ) {
                    // Save Meta Data
                    update_post_meta( $post_id, '_wpcw_applicant_type', $applicant_type );
                    update_post_meta( $post_id, '_wpcw_legal_name', $legal_name );
                    update_post_meta( $post_id, '_wpcw_cuit', $cuit );
                    update_post_meta( $post_id, '_wpcw_contact_person', $contact_person );
                    update_post_meta( $post_id, '_wpcw_email', $email );
                    update_post_meta( $post_id, '_wpcw_whatsapp', $whatsapp );
                    update_post_meta( $post_id, '_wpcw_address_main', $address_main );
                    // _wpcw_description is saved as post_content, but can be saved as meta too if needed for other purposes
                    // update_post_meta( $post_id, '_wpcw_description', $description );
                    update_post_meta( $post_id, '_wpcw_application_status', 'pendiente_revision' );
                    if ( is_user_logged_in() ) {
                        update_post_meta( $post_id, '_wpcw_created_user_id', get_current_user_id() );
                    }

                    // Send Email Notification to Admin
                    $admin_email = get_option( 'admin_email' );
                    $subject = sprintf( __( 'Nueva Solicitud de Adhesión: %s', 'wp-cupon-whatsapp' ), $fantasy_name );

                    $message_body = sprintf( __( 'Se ha recibido una nueva solicitud de adhesión para el programa WP Canje Cupon Whatsapp.', 'wp-cupon-whatsapp' ) ) . "\r\n\r\n";
                    $message_body .= sprintf( __( 'Nombre de Fantasía: %s', 'wp-cupon-whatsapp' ), $fantasy_name ) . "\r\n";
                    $message_body .= sprintf( __( 'Tipo de Solicitante: %s', 'wp-cupon-whatsapp' ), ucfirst( $applicant_type ) ) . "\r\n"; // ucfirst for better readability
                    $message_body .= sprintf( __( 'Nombre Legal: %s', 'wp-cupon-whatsapp' ), $legal_name ) . "\r\n";
                    $message_body .= sprintf( __( 'CUIT: %s', 'wp-cupon-whatsapp' ), $cuit ) . "\r\n";
                    $message_body .= sprintf( __( 'Persona de Contacto: %s', 'wp-cupon-whatsapp' ), $contact_person ) . "\r\n";
                    $message_body .= sprintf( __( 'Email de Contacto: %s', 'wp-cupon-whatsapp' ), $email ) . "\r\n";
                    $message_body .= sprintf( __( 'Número de WhatsApp: %s', 'wp-cupon-whatsapp' ), $whatsapp ) . "\r\n";
                    $message_body .= sprintf( __( 'Dirección Principal: %s', 'wp-cupon-whatsapp' ), $address_main ) . "\r\n";
                    $message_body .= sprintf( __( 'Descripción: %s', 'wp-cupon-whatsapp' ), $description ) . "\r\n";
                    $message_body .= "\r\n";

                    $edit_link = get_edit_post_link( $post_id );
                    if ( $edit_link ) {
                        $message_body .= sprintf( __( 'Puedes revisar y procesar esta solicitud aquí: %s', 'wp-cupon-whatsapp' ), $edit_link ) . "\r\n";
                    }

                    $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
                    wp_mail( $admin_email, $subject, $message_body, $headers );

                    // Clear POST data to prevent form re-submission issues on refresh (optional, PRG pattern is better)
                    // $_POST = array(); // Be careful with this, might affect other plugins or themes.
                                        // A redirect after successful submission is a more robust solution (PRG pattern).

                    // Return success message (ob_start will be called later only if form needs to be shown)
                    return '<div class="wpcw-form-success">' . __( 'Su solicitud ha sido enviada con éxito. Nos pondremos en contacto a la brevedad.', 'wp-cupon-whatsapp' ) . '</div>';

                } else {
                    $errors[] = __( 'Hubo un error al procesar su solicitud. Por favor, inténtelo de nuevo más tarde.', 'wp-cupon-whatsapp' );
                }
            }
        }
    }

    ob_start();

    if ( ! empty( $errors ) ) {
        echo '<div class="wpcw-form-errors" style="color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px;">';
        foreach ( $errors as $error ) {
            echo '<p>' . esc_html( $error ) . '</p>';
        }
        echo '</div>';
    }
    ?>
    <form id="wpcw-solicitud-adhesion-form" method="POST" action="">
        // Verify Nonce
        if ( ! isset( $_POST['wpcw_solicitud_adhesion_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['wpcw_solicitud_adhesion_nonce'] ), 'wpcw_solicitud_adhesion_action' ) ) {
            $errors[] = __( 'Error de seguridad. Inténtalo de nuevo.', 'wp-cupon-whatsapp' );
        } else {
            // Verify reCAPTCHA if enabled
            if ( function_exists('wpcw_is_recaptcha_enabled') && wpcw_is_recaptcha_enabled() ) {
                $recaptcha_token = isset($_POST['g-recaptcha-response']) ? sanitize_text_field($_POST['g-recaptcha-response']) : '';
                if ( function_exists('wpcw_verify_recaptcha') && !wpcw_verify_recaptcha($recaptcha_token) ) {
                    $errors[] = __('La verificación reCAPTCHA ha fallado. Por favor, inténtalo de nuevo.', 'wp-cupon-whatsapp');
                }
            }

            // Validate Fields only if no prior errors (nonce, recaptcha)
            if ( empty( $errors ) ) {
                $applicant_type = isset( $_POST['wpcw_applicant_type'] ) ? sanitize_text_field( $_POST['wpcw_applicant_type'] ) : '';
                $fantasy_name = isset( $_POST['wpcw_fantasy_name'] ) ? sanitize_text_field( $_POST['wpcw_fantasy_name'] ) : '';
                $legal_name = isset( $_POST['wpcw_legal_name'] ) ? sanitize_text_field( $_POST['wpcw_legal_name'] ) : '';
                $cuit = isset( $_POST['wpcw_cuit'] ) ? sanitize_text_field( $_POST['wpcw_cuit'] ) : '';
                $contact_person = isset( $_POST['wpcw_contact_person'] ) ? sanitize_text_field( $_POST['wpcw_contact_person'] ) : '';
                $email = isset( $_POST['wpcw_email'] ) ? sanitize_email( $_POST['wpcw_email'] ) : '';
                $whatsapp = isset( $_POST['wpcw_whatsapp'] ) ? sanitize_text_field( $_POST['wpcw_whatsapp'] ) : '';

                if ( empty( $applicant_type ) ) {
                    $errors[] = __( 'Por favor, selecciona el tipo de solicitante.', 'wp-cupon-whatsapp' );
                }
                if ( empty( $fantasy_name ) ) {
                    $errors[] = __( 'Por favor, introduce el nombre de fantasía.', 'wp-cupon-whatsapp' );
                }
                if ( empty( $legal_name ) ) {
                    $errors[] = __( 'Por favor, introduce el nombre legal.', 'wp-cupon-whatsapp' );
                }
                if ( empty( $cuit ) ) {
                    $errors[] = __( 'Por favor, introduce el CUIT.', 'wp-cupon-whatsapp' );
                } elseif ( ! preg_match( '/^[0-9]{10,11}$/', $cuit ) ) { // Basic CUIT validation (10 or 11 digits)
                    $errors[] = __( 'Por favor, introduce un CUIT válido (solo números, 10 u 11 dígitos).', 'wp-cupon-whatsapp' );
                }
                if ( empty( $contact_person ) ) {
                    $errors[] = __( 'Por favor, introduce el nombre de la persona de contacto.', 'wp-cupon-whatsapp' );
                }
                if ( empty( $email ) ) {
                    $errors[] = __( 'Por favor, introduce un email de contacto.', 'wp-cupon-whatsapp' );
                } elseif ( ! is_email( $email ) ) {
                    $errors[] = __( 'Por favor, introduce un email de contacto válido.', 'wp-cupon-whatsapp' );
                }
                if ( empty( $whatsapp ) ) {
                    $errors[] = __( 'Por favor, introduce un número de WhatsApp.', 'wp-cupon-whatsapp' );
                } elseif ( ! preg_match( '/^[0-9\s\+\(\)-]+$/', $whatsapp ) ) {
                    $errors[] = __( 'Por favor, introduce un número de WhatsApp válido.', 'wp-cupon-whatsapp' );
                }
            }
            // if ( empty( $errors ) ) { // This check is now duplicated, the save logic block also has it.
            //    // Process application...
            // }

            if ( empty( $errors ) ) { // This is the original block for saving
                // Sanitize Data
                // $applicant_type is already sanitized from validation block
                // $fantasy_name is already sanitized
                // $legal_name is already sanitized
                // $cuit is already sanitized
                // $contact_person is already sanitized
                // $email is already sanitized
                // $whatsapp is already sanitized
                $address_main = isset( $_POST['wpcw_address_main'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wpcw_address_main'] ) ) : '';
                $description = isset( $_POST['wpcw_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wpcw_description'] ) ) : '';

                // Prepare Post Data
                $post_data = array(
                    'post_title'  => $fantasy_name, // Using fantasy name as title
                    'post_content' => $description, // Using description as main content
                    'post_type'   => 'wpcw_application',
                    'post_status' => 'publish', // Or 'pending' if manual admin approval is always first
                );

                // Insert Post
                $post_id = wp_insert_post( $post_data );

                if ( ! is_wp_error( $post_id ) && $post_id > 0 ) {
                    // Save Meta Data
                    update_post_meta( $post_id, '_wpcw_applicant_type', $applicant_type );
                    update_post_meta( $post_id, '_wpcw_legal_name', $legal_name );
                    update_post_meta( $post_id, '_wpcw_cuit', $cuit );
                    update_post_meta( $post_id, '_wpcw_contact_person', $contact_person );
                    update_post_meta( $post_id, '_wpcw_email', $email );
                    update_post_meta( $post_id, '_wpcw_whatsapp', $whatsapp );
                    update_post_meta( $post_id, '_wpcw_address_main', $address_main );
                    // _wpcw_description is saved as post_content, but can be saved as meta too if needed for other purposes
                    // update_post_meta( $post_id, '_wpcw_description', $description );
                    update_post_meta( $post_id, '_wpcw_application_status', 'pendiente_revision' );
                    if ( is_user_logged_in() ) {
                        update_post_meta( $post_id, '_wpcw_created_user_id', get_current_user_id() );
                    }

                    // Send Email Notification to Admin
                    $admin_email = get_option( 'admin_email' );
                    $subject = sprintf( __( 'Nueva Solicitud de Adhesión: %s', 'wp-cupon-whatsapp' ), $fantasy_name );

                    $message_body = sprintf( __( 'Se ha recibido una nueva solicitud de adhesión para el programa WP Canje Cupon Whatsapp.', 'wp-cupon-whatsapp' ) ) . "\r\n\r\n";
                    $message_body .= sprintf( __( 'Nombre de Fantasía: %s', 'wp-cupon-whatsapp' ), $fantasy_name ) . "\r\n";
                    $message_body .= sprintf( __( 'Tipo de Solicitante: %s', 'wp-cupon-whatsapp' ), ucfirst( $applicant_type ) ) . "\r\n"; // ucfirst for better readability
                    $message_body .= sprintf( __( 'Nombre Legal: %s', 'wp-cupon-whatsapp' ), $legal_name ) . "\r\n";
                    $message_body .= sprintf( __( 'CUIT: %s', 'wp-cupon-whatsapp' ), $cuit ) . "\r\n";
                    $message_body .= sprintf( __( 'Persona de Contacto: %s', 'wp-cupon-whatsapp' ), $contact_person ) . "\r\n";
                    $message_body .= sprintf( __( 'Email de Contacto: %s', 'wp-cupon-whatsapp' ), $email ) . "\r\n";
                    $message_body .= sprintf( __( 'Número de WhatsApp: %s', 'wp-cupon-whatsapp' ), $whatsapp ) . "\r\n";
                    $message_body .= sprintf( __( 'Dirección Principal: %s', 'wp-cupon-whatsapp' ), $address_main ) . "\r\n";
                    $message_body .= sprintf( __( 'Descripción: %s', 'wp-cupon-whatsapp' ), $description ) . "\r\n";
                    $message_body .= "\r\n";

                    $edit_link = get_edit_post_link( $post_id );
                    if ( $edit_link ) {
                        $message_body .= sprintf( __( 'Puedes revisar y procesar esta solicitud aquí: %s', 'wp-cupon-whatsapp' ), $edit_link ) . "\r\n";
                    }

                    $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
                    wp_mail( $admin_email, $subject, $message_body, $headers );

                    // Clear POST data to prevent form re-submission issues on refresh (optional, PRG pattern is better)
                    // $_POST = array(); // Be careful with this, might affect other plugins or themes.
                                        // A redirect after successful submission is a more robust solution (PRG pattern).

                    // Return success message (ob_start will be called later only if form needs to be shown)
                    return '<div class="wpcw-form-success">' . __( 'Su solicitud ha sido enviada con éxito. Nos pondremos en contacto a la brevedad.', 'wp-cupon-whatsapp' ) . '</div>';

                } else {
                    $errors[] = __( 'Hubo un error al procesar su solicitud. Por favor, inténtelo de nuevo más tarde.', 'wp-cupon-whatsapp' );
                }
            }
        }
    }

    ob_start();

    if ( ! empty( $errors ) ) {
        echo '<div class="wpcw-form-errors" style="color: red; border: 1px solid red; padding: 10px; margin-bottom: 15px;">';
        foreach ( $errors as $error ) {
            echo '<p>' . esc_html( $error ) . '</p>';
        }
        echo '</div>';
    }
    ?>
    <form id="wpcw-solicitud-adhesion-form" method="POST" action="">
        <fieldset>
            <legend><?php _e( 'Tipo de Solicitante', 'wp-cupon-whatsapp' ); ?></legend>
            <p>
                <label>
                    <input type="radio" name="wpcw_applicant_type" value="comercio" <?php checked( (isset($_POST['wpcw_applicant_type']) && $_POST['wpcw_applicant_type'] === 'comercio'), true, true ); ?><?php echo ( !isset($_POST['wpcw_applicant_type']) ) ? 'checked' : ''; // Default to comercio if not set ?>>
                    <?php _e( 'Comercio', 'wp-cupon-whatsapp' ); ?>
                </label>
            </p>
            <p>
                <label>
                    <input type="radio" name="wpcw_applicant_type" value="institucion" <?php checked( (isset($_POST['wpcw_applicant_type']) && $_POST['wpcw_applicant_type'] === 'institucion'), true, true ); ?>>
                    <?php _e( 'Institución', 'wp-cupon-whatsapp' ); ?>
                </label>
            </p>
        </fieldset>

        <p>
            <label for="wpcw_fantasy_name"><?php _e( 'Nombre de Fantasía', 'wp-cupon-whatsapp' ); ?></label><br>
            <input type="text" id="wpcw_fantasy_name" name="wpcw_fantasy_name" value="<?php echo isset( $_POST['wpcw_fantasy_name'] ) ? esc_attr( wp_unslash( sanitize_text_field( $_POST['wpcw_fantasy_name'] ) ) ) : ''; ?>" required>
        </p>

        <p>
            <label for="wpcw_legal_name"><?php _e( 'Nombre Legal', 'wp-cupon-whatsapp' ); ?></label><br>
            <input type="text" id="wpcw_legal_name" name="wpcw_legal_name" value="<?php echo isset( $_POST['wpcw_legal_name'] ) ? esc_attr( wp_unslash( sanitize_text_field( $_POST['wpcw_legal_name'] ) ) ) : ''; ?>" required>
        </p>

        <p>
            <label for="wpcw_cuit"><?php _e( 'CUIT', 'wp-cupon-whatsapp' ); ?></label><br>
            <input type="text" id="wpcw_cuit" name="wpcw_cuit" value="<?php echo isset( $_POST['wpcw_cuit'] ) ? esc_attr( wp_unslash( sanitize_text_field( $_POST['wpcw_cuit'] ) ) ) : ''; ?>" required>
        </p>

        <p>
            <label for="wpcw_contact_person"><?php _e( 'Persona de Contacto', 'wp-cupon-whatsapp' ); ?></label><br>
            <input type="text" id="wpcw_contact_person" name="wpcw_contact_person" value="<?php echo isset( $_POST['wpcw_contact_person'] ) ? esc_attr( wp_unslash( sanitize_text_field( $_POST['wpcw_contact_person'] ) ) ) : ''; ?>" required>
        </p>

        <p>
            <label for="wpcw_email"><?php _e( 'Email de Contacto', 'wp-cupon-whatsapp' ); ?></label><br>
            <input type="email" id="wpcw_email" name="wpcw_email" value="<?php echo isset( $_POST['wpcw_email'] ) ? esc_attr( sanitize_email( $_POST['wpcw_email'] ) ) : ''; ?>" required>
        </p>

        <p>
            <label for="wpcw_whatsapp"><?php _e( 'Número de WhatsApp', 'wp-cupon-whatsapp' ); ?></label><br>
            <input type="tel" id="wpcw_whatsapp" name="wpcw_whatsapp" value="<?php echo isset( $_POST['wpcw_whatsapp'] ) ? esc_attr( wp_unslash( sanitize_text_field( $_POST['wpcw_whatsapp'] ) ) ) : ''; ?>" required>
        </p>

        <p>
            <label for="wpcw_address_main"><?php _e( 'Dirección Principal', 'wp-cupon-whatsapp' ); ?></label><br>
            <textarea id="wpcw_address_main" name="wpcw_address_main" rows="3" ><?php echo isset( $_POST['wpcw_address_main'] ) ? esc_textarea( wp_unslash( sanitize_textarea_field( $_POST['wpcw_address_main'] ) ) ) : ''; ?></textarea>
        </p>

        <p>
            <label for="wpcw_description"><?php _e( 'Descripción del Negocio/Institución', 'wp-cupon-whatsapp' ); ?></label><br>
            <textarea id="wpcw_description" name="wpcw_description" rows="5" ><?php echo isset( $_POST['wpcw_description'] ) ? esc_textarea( wp_unslash( sanitize_textarea_field( $_POST['wpcw_description'] ) ) ) : ''; ?></textarea>
        </p>

        <?php wp_nonce_field( 'wpcw_solicitud_adhesion_action', 'wpcw_solicitud_adhesion_nonce' ); ?>

        <?php
        // Display reCAPTCHA
        if ( function_exists('wpcw_display_recaptcha') ) {
            wpcw_display_recaptcha();
        }
        ?>

        <p>
            <input type="submit" name="wpcw_submit_solicitud" value="<?php _e( 'Enviar Solicitud', 'wp-cupon-whatsapp' ); ?>">
        </p>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode( 'wpcw_solicitud_adhesion_form', 'wpcw_render_solicitud_adhesion_form' );

/**
 * Renders the 'Mis Cupones Disponibles' page for logged-in users.
 *
 * @return string HTML content for the page.
 */
function wpcw_render_mis_cupones_page() {
    if ( ! is_user_logged_in() ) {
        // Puedes personalizar este mensaje o incluso retornar un formulario de login de WooCommerce.
        // wc_get_template( 'myaccount/form-login.php' ); OJO: esto imprimiría directamente.
        // Mejor un mensaje y un enlace.
        return '<p class="wpcw-login-required">' .
            sprintf(
                __( 'Por favor, <a href="%s">inicia sesión</a> para ver tus cupones disponibles.', 'wp-cupon-whatsapp' ),
                esc_url( wc_get_page_permalink( 'myaccount' ) ) // Obtener URL de "Mi Cuenta" de WC
            ) .
            '</p>';
    }

    // Contenido para usuarios logueados
    ob_start(); // Iniciar buffer de salida para capturar todo el HTML del shortcode

    echo '<div class="wpcw-mis-cupones-page">';
    echo '<h2>' . esc_html__( 'Mis Cupones de Lealtad Disponibles', 'wp-cupon-whatsapp' ) . '</h2>';

    $user_id = get_current_user_id(); // No se usa directamente en esta consulta básica, pero útil para futuro

    $args = array(
        'post_type'      => 'shop_coupon',
        'post_status'    => 'publish',
        'posts_per_page' => -1, // Mostrar todos los cupones de lealtad por ahora, paginación después.
        'meta_query'     => array(
            'relation' => 'AND', // Asegurar que todas las condiciones de meta se cumplan
            array(
                'key'     => '_wpcw_is_loyalty_coupon',
                'value'   => 'yes',
                'compare' => '=',
            ),
            // TODO: Añadir más meta_queries para filtrar por:
            // - Institución del usuario (_wpcw_user_institution_id) -> necesitaría un meta en el cupón que lo vincule a una institución, O si el cupón es de una institución específica.
            // - Categorías favoritas del usuario (_wpcw_user_favorite_coupon_categories vs _wpcw_coupon_category_id)
            // - Excluir cupones ya canjeados por este usuario (requerirá consulta a la tabla wpcw_canjes)
        ),
        // TODO: Considerar orden (ej. por fecha de expiración, si existe ese dato)
    );

    $loyalty_coupons_query = new WP_Query( $args );

    if ( $loyalty_coupons_query->have_posts() ) {
        echo '<div class="wpcw-coupons-grid">'; // Contenedor para las tarjetas de cupón

        while ( $loyalty_coupons_query->have_posts() ) : $loyalty_coupons_query->the_post();
            // Preparar datos para la plantilla coupon-card.php
            $coupon_id = get_the_ID();
            $coupon_title = get_the_title();
            // En WooCommerce, el código del cupón es el post_title del CPT shop_coupon.
            $coupon_code = get_the_title();

            $coupon_description = get_the_excerpt();
            if (empty($coupon_description)) {
                // Usar el contenido del cupón (pestaña General > Descripción del cupón) si el excerpt está vacío.
                $coupon_post_content = get_the_content();
                $coupon_description = wp_trim_words( $coupon_post_content, 20, '...' );
            }
            if (empty($coupon_description) && isset($coupon_post_content) && !empty($coupon_post_content)) {
                 // Si wp_trim_words devuelve vacío pero había contenido, usar el contenido tal cual (respetando HTML simple)
                 $coupon_description = $coupon_post_content;
            }


            $coupon_image_id = get_post_meta( $coupon_id, '_wpcw_coupon_image_id', true );
            $coupon_image_url = $coupon_image_id ? wp_get_attachment_image_url( $coupon_image_id, 'medium' ) : ''; // 'medium' o 'thumbnail'

            // Estas variables estarán disponibles en el scope de la plantilla incluida.
            // La plantilla coupon-card.php fue diseñada para usar estas variables directamente.
            // Y también tiene sus propios defaults si estas no están seteadas.

            // Ruta a la plantilla
            $template_path = WPCW_PLUGIN_DIR . 'public/templates/coupon-card.php';
            if ( file_exists( $template_path ) ) {
                include( $template_path );
            } else {
                echo '<p>' . sprintf(esc_html__('Error: Plantilla de tarjeta de cupón no encontrada en %s', 'wp-cupon-whatsapp'), esc_html($template_path)) . '</p>';
            }

        endwhile;

        echo '</div>'; // Fin de .wpcw-coupons-grid
        wp_reset_postdata(); // Importante después de un loop personalizado con WP_Query

    } else {
        echo '<p>' . esc_html__( 'No tienes cupones de lealtad disponibles en este momento.', 'wp-cupon-whatsapp' ) . '</p>';
    }
    //  wp_reset_postdata(); // Moved inside the if have_posts block

    echo '</div>'; // Fin de .wpcw-mis-cupones-page

    return ob_get_clean(); // Devolver todo el contenido capturado
}
add_shortcode( 'wpcw_mis_cupones', 'wpcw_render_mis_cupones_page' );

?>
