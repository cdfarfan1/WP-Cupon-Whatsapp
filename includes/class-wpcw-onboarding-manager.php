<?php
/**
 * WP Cupón WhatsApp - Onboarding Manager
 *
 * Maneja el proceso completo de onboarding para usuarios nuevos
 *
 * @package WP_Cupon_WhatsApp
 * @since 1.6.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

class WPCW_Onboarding_Manager {

    /**
     * Pasos del proceso de onboarding
     */
    const STEPS = [
        1 => 'email_verification',
        2 => 'basic_info',
        3 => 'institution_selection',
        4 => 'preferences_setup',
        5 => 'activation_complete'
    ];

    /**
     * Porcentaje de completitud por paso
     */
    const STEP_COMPLETION = [
        1 => 20,  // Email verificado
        2 => 40,  // Info básica completa
        3 => 60,  // Institución seleccionada
        4 => 80,  // Preferencias configuradas
        5 => 100 // Onboarding completo
    ];

    /**
     * Inicializar el sistema de onboarding
     */
    public static function init() {
        // Hooks para el proceso de registro
        add_action( 'user_register', array( __CLASS__, 'start_onboarding_process' ), 10, 1 );
        add_action( 'wp_login', array( __CLASS__, 'check_onboarding_status' ), 10, 2 );

        // AJAX handlers
        add_action( 'wp_ajax_wpcw_onboarding_update', array( __CLASS__, 'handle_onboarding_update' ) );
        add_action( 'wp_ajax_wpcw_onboarding_skip', array( __CLASS__, 'handle_onboarding_skip' ) );

        // Shortcodes
        add_shortcode( 'wpcw_onboarding_form', array( __CLASS__, 'render_onboarding_form' ) );

        // Admin hooks
        if ( is_admin() ) {
            add_action( 'show_user_profile', array( __CLASS__, 'add_onboarding_fields_to_profile' ) );
            add_action( 'edit_user_profile', array( __CLASS__, 'add_onboarding_fields_to_profile' ) );
            add_action( 'personal_options_update', array( __CLASS__, 'save_onboarding_fields_from_profile' ) );
            add_action( 'edit_user_profile_update', array( __CLASS__, 'save_onboarding_fields_from_profile' ) );
        }
    }

    /**
     * Iniciar el proceso de onboarding para un nuevo usuario
     *
     * @param int $user_id ID del usuario
     */
    public static function start_onboarding_process( $user_id ) {
        try {
            // Crear perfil extendido del usuario
            self::create_user_profile( $user_id );

            // Enviar email de verificación
            self::send_verification_email( $user_id );

            // Log del inicio del proceso
            WPCW_Logger::log( 'info', 'Onboarding process started for user', array(
                'user_id' => $user_id,
                'timestamp' => current_time( 'mysql' )
            ) );

        } catch ( Exception $e ) {
            WPCW_Logger::log( 'error', 'Failed to start onboarding process', array(
                'user_id' => $user_id,
                'error' => $e->getMessage()
            ) );
        }
    }

    /**
     * Crear perfil extendido del usuario
     *
     * @param int $user_id ID del usuario
     */
    private static function create_user_profile( $user_id ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpcw_user_profiles';

        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'onboarding_step' => 1,
                'profile_completion_percentage' => 0,
                'created_at' => current_time( 'mysql' ),
                'updated_at' => current_time( 'mysql' )
            ),
            array( '%d', '%d', '%d', '%s', '%s' )
        );

        if ( $result === false ) {
            throw new Exception( 'Failed to create user profile: ' . $wpdb->last_error );
        }

        // Inicializar meta data del usuario
        update_user_meta( $user_id, 'wpcw_onboarding_started', current_time( 'mysql' ) );
        update_user_meta( $user_id, 'wpcw_onboarding_step', 1 );
        update_user_meta( $user_id, 'wpcw_profile_completion', 0 );
    }

    /**
     * Enviar email de verificación
     *
     * @param int $user_id ID del usuario
     */
    private static function send_verification_email( $user_id ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return;
        }

        // Generar token único
        $token = wp_generate_password( 32, false );
        update_user_meta( $user_id, 'wpcw_email_verification_token', $token );

        // Crear enlace de verificación
        $verification_url = add_query_arg(
            array(
                'wpcw_verify_email' => $token,
                'user_id' => $user_id
            ),
            home_url()
        );

        // Preparar contenido del email
        $subject = __( '¡Bienvenido a WP Cupón WhatsApp! Confirma tu email', 'wp-cupon-whatsapp' );

        $message = self::get_verification_email_template( $user, $verification_url );

        // Enviar email
        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        $sent = wp_mail( $user->user_email, $subject, $message, $headers );

        if ( ! $sent ) {
            WPCW_Logger::log( 'error', 'Failed to send verification email', array(
                'user_id' => $user_id,
                'email' => $user->user_email
            ) );
        }
    }

    /**
     * Template del email de verificación
     *
     * @param WP_User $user Usuario
     * @param string $verification_url URL de verificación
     * @return string HTML del email
     */
    private static function get_verification_email_template( $user, $verification_url ) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php _e( 'Confirma tu email - WP Cupón WhatsApp', 'wp-cupon-whatsapp' ); ?></title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #007cba 0%, #005a87 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
                .button { display: inline-block; background: #46b450; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>🎫 WP Cupón WhatsApp</h1>
                <h2><?php _e( '¡Bienvenido!', 'wp-cupon-whatsapp' ); ?></h2>
            </div>

            <div class="content">
                <h3><?php printf( __( 'Hola %s,', 'wp-cupon-whatsapp' ), esc_html( $user->display_name ) ); ?></h3>

                <p><?php _e( 'Gracias por registrarte en WP Cupón WhatsApp. Para completar tu registro y comenzar a disfrutar de nuestros cupones exclusivos, necesitas confirmar tu dirección de email.', 'wp-cupon-whatsapp' ); ?></p>

                <p><?php _e( 'Haz clic en el botón de abajo para verificar tu email:', 'wp-cupon-whatsapp' ); ?></p>

                <div style="text-align: center;">
                    <a href="<?php echo esc_url( $verification_url ); ?>" class="button">
                        <?php _e( 'Confirmar Email', 'wp-cupon-whatsapp' ); ?>
                    </a>
                </div>

                <p><?php _e( 'Si el botón no funciona, copia y pega este enlace en tu navegador:', 'wp-cupon-whatsapp' ); ?></p>
                <p style="word-break: break-all; background: #e1e1e1; padding: 10px; border-radius: 4px;">
                    <?php echo esc_url( $verification_url ); ?>
                </p>

                <p><?php _e( 'Este enlace expirará en 24 horas por seguridad.', 'wp-cupon-whatsapp' ); ?></p>

                <p><?php _e( 'Si no te registraste en WP Cupón WhatsApp, puedes ignorar este email.', 'wp-cupon-whatsapp' ); ?></p>
            </div>

            <div class="footer">
                <p><?php _e( 'Este email fue enviado automáticamente por WP Cupón WhatsApp.', 'wp-cupon-whatsapp' ); ?></p>
                <p><?php printf( __( '© %s WP Cupón WhatsApp. Todos los derechos reservados.', 'wp-cupon-whatsapp' ), date( 'Y' ) ); ?></p>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Verificar email del usuario
     *
     * @param string $token Token de verificación
     * @param int $user_id ID del usuario
     * @return bool True si la verificación fue exitosa
     */
    public static function verify_user_email( $token, $user_id ) {
        $stored_token = get_user_meta( $user_id, 'wpcw_email_verification_token', true );

        if ( empty( $stored_token ) || $stored_token !== $token ) {
            return false;
        }

        // Marcar email como verificado
        update_user_meta( $user_id, 'wpcw_email_verified', true );
        delete_user_meta( $user_id, 'wpcw_email_verification_token' );

        // Actualizar progreso del onboarding
        self::update_onboarding_progress( $user_id, 1, true );

        // Log de verificación exitosa
        WPCW_Logger::log( 'info', 'User email verified successfully', array(
            'user_id' => $user_id,
            'timestamp' => current_time( 'mysql' )
        ) );

        return true;
    }

    /**
     * Verificar estado del onboarding al hacer login
     *
     * @param string $user_login Username del usuario
     * @param WP_User $user Objeto usuario
     */
    public static function check_onboarding_status( $user_login, $user ) {
        $onboarding_completed = get_user_meta( $user->ID, 'wpcw_onboarding_completed', true );

        if ( ! $onboarding_completed ) {
            $current_step = get_user_meta( $user->ID, 'wpcw_onboarding_step', true ) ?: 1;

            // Redirigir al proceso de onboarding si no está completo
            if ( ! wp_doing_ajax() && ! is_admin() ) {
                $onboarding_url = add_query_arg(
                    array( 'wpcw_onboarding' => 'continue', 'step' => $current_step ),
                    home_url( '/onboarding' )
                );
                wp_redirect( $onboarding_url );
                exit;
            }
        }
    }

    /**
     * Renderizar formulario de onboarding
     *
     * @param array $atts Atributos del shortcode
     * @return string HTML del formulario
     */
    public static function render_onboarding_form( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<p>' . __( 'Debes iniciar sesión para acceder al proceso de onboarding.', 'wp-cupon-whatsapp' ) . '</p>';
        }

        $user_id = get_current_user_id();
        $current_step = get_user_meta( $user_id, 'wpcw_onboarding_step', true ) ?: 1;
        $onboarding_completed = get_user_meta( $user_id, 'wpcw_onboarding_completed', true );

        if ( $onboarding_completed ) {
            return '<div class="wpcw-onboarding-completed"><p>' . __( '¡Tu perfil ya está completo! Puedes acceder a tu dashboard.', 'wp-cupon-whatsapp' ) . '</p></div>';
        }

        // Forzar paso específico si se proporciona
        if ( isset( $_GET['step'] ) && is_numeric( $_GET['step'] ) ) {
            $requested_step = intval( $_GET['step'] );
            if ( $requested_step >= 1 && $requested_step <= 5 ) {
                $current_step = $requested_step;
            }
        }

        ob_start();
        ?>
        <div class="wpcw-onboarding-container">
            <div class="wpcw-onboarding-header">
                <h2><?php _e( 'Completa tu Perfil', 'wp-cupon-whatsapp' ); ?></h2>
                <div class="wpcw-progress-bar">
                    <div class="wpcw-progress-fill" style="width: <?php echo esc_attr( self::STEP_COMPLETION[$current_step] ); ?>%"></div>
                </div>
                <p class="wpcw-progress-text">
                    <?php printf( __( 'Paso %d de %d - %d%% completado', 'wp-cupon-whatsapp' ), $current_step, count( self::STEPS ), self::STEP_COMPLETION[$current_step] ); ?>
                </p>
            </div>

            <div class="wpcw-onboarding-steps">
                <?php for ( $i = 1; $i <= count( self::STEPS ); $i++ ) : ?>
                    <div class="wpcw-step <?php echo $i < $current_step ? 'completed' : ( $i === $current_step ? 'active' : 'pending' ); ?>">
                        <span class="wpcw-step-number"><?php echo $i; ?></span>
                        <span class="wpcw-step-label"><?php echo esc_html( self::get_step_label( $i ) ); ?></span>
                    </div>
                <?php endfor; ?>
            </div>

            <div class="wpcw-onboarding-content">
                <?php echo self::render_step_content( $current_step, $user_id ); ?>
            </div>
        </div>

        <style>
            .wpcw-onboarding-container { max-width: 800px; margin: 0 auto; padding: 20px; }
            .wpcw-onboarding-header { text-align: center; margin-bottom: 30px; }
            .wpcw-progress-bar { background: #e1e1e1; height: 8px; border-radius: 4px; margin: 15px 0; overflow: hidden; }
            .wpcw-progress-fill { background: linear-gradient(135deg, #007cba 0%, #005a87 100%); height: 100%; transition: width 0.3s ease; }
            .wpcw-progress-text { color: #666; font-size: 14px; }
            .wpcw-onboarding-steps { display: flex; justify-content: space-between; margin: 30px 0; }
            .wpcw-step { display: flex; flex-direction: column; align-items: center; flex: 1; }
            .wpcw-step-number { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-bottom: 8px; }
            .wpcw-step.completed .wpcw-step-number { background: #46b450; color: white; }
            .wpcw-step.active .wpcw-step-number { background: #007cba; color: white; }
            .wpcw-step.pending .wpcw-step-number { background: #e1e1e1; color: #666; }
            .wpcw-step-label { font-size: 12px; text-align: center; color: #666; }
            .wpcw-onboarding-content { background: #f9f9f9; padding: 30px; border-radius: 8px; }
        </style>

        <script>
            jQuery(document).ready(function($) {
                // Manejar envío de formularios
                $('.wpcw-onboarding-form').on('submit', function(e) {
                    e.preventDefault();

                    var form = $(this);
                    var formData = new FormData(this);
                    formData.append('action', 'wpcw_onboarding_update');
                    formData.append('step', <?php echo $current_step; ?>);
                    formData.append('nonce', '<?php echo wp_create_nonce( 'wpcw_onboarding_nonce' ); ?>');

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        beforeSend: function() {
                            form.find('button[type="submit"]').prop('disabled', true).text('<?php _e( 'Guardando...', 'wp-cupon-whatsapp' ); ?>');
                        },
                        success: function(response) {
                            if (response.success) {
                                if (response.data.redirect) {
                                    window.location.href = response.data.redirect;
                                } else {
                                    location.reload();
                                }
                            } else {
                                alert(response.data.message || '<?php _e( 'Error al guardar los datos', 'wp-cupon-whatsapp' ); ?>');
                            }
                        },
                        error: function() {
                            alert('<?php _e( 'Error de conexión', 'wp-cupon-whatsapp' ); ?>');
                        },
                        complete: function() {
                            form.find('button[type="submit"]').prop('disabled', false).text('<?php _e( 'Siguiente', 'wp-cupon-whatsapp' ); ?>');
                        }
                    });
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Renderizar contenido del paso específico
     *
     * @param int $step Número del paso
     * @param int $user_id ID del usuario
     * @return string HTML del contenido
     */
    private static function render_step_content( $step, $user_id ) {
        $user_data = self::get_user_onboarding_data( $user_id );

        switch ( $step ) {
            case 1:
                return self::render_email_verification_step( $user_data );
            case 2:
                return self::render_basic_info_step( $user_data );
            case 3:
                return self::render_institution_step( $user_data );
            case 4:
                return self::render_preferences_step( $user_data );
            case 5:
                return self::render_completion_step( $user_data );
            default:
                return '<p>' . __( 'Paso no válido', 'wp-cupon-whatsapp' ) . '</p>';
        }
    }

    /**
     * Renderizar paso 1: Verificación de email
     */
    private static function render_email_verification_step( $user_data ) {
        $email_verified = get_user_meta( get_current_user_id(), 'wpcw_email_verified', true );

        if ( $email_verified ) {
            return '
                <div class="wpcw-step-content">
                    <h3>' . __( '✅ Email Verificado', 'wp-cupon-whatsapp' ) . '</h3>
                    <p>' . __( 'Tu dirección de email ha sido verificada exitosamente. Puedes continuar con el siguiente paso.', 'wp-cupon-whatsapp' ) . '</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="' . add_query_arg( 'step', 2, remove_query_arg( 'step' ) ) . '" class="button button-primary button-large">
                            ' . __( 'Continuar al Siguiente Paso', 'wp-cupon-whatsapp' ) . '
                        </a>
                    </div>
                </div>
            ';
        }

        return '
            <div class="wpcw-step-content">
                <h3>' . __( 'Verifica tu Email', 'wp-cupon-whatsapp' ) . '</h3>
                <p>' . __( 'Hemos enviado un email de verificación a tu dirección de correo electrónico. Por favor, revisa tu bandeja de entrada y haz clic en el enlace de verificación.', 'wp-cupon-whatsapp' ) . '</p>
                <div class="wpcw-verification-notice">
                    <p><strong>' . __( '¿No recibiste el email?', 'wp-cupon-whatsapp' ) . '</strong></p>
                    <ul>
                        <li>' . __( 'Revisa tu carpeta de spam', 'wp-cupon-whatsapp' ) . '</li>
                        <li>' . __( 'El email puede tardar unos minutos en llegar', 'wp-cupon-whatsapp' ) . '</li>
                        <li>' . __( 'Asegúrate de que la dirección sea correcta', 'wp-cupon-whatsapp' ) . '</li>
                    </ul>
                    <button type="button" class="button" id="resend-verification" style="margin-top: 15px;">
                        ' . __( 'Reenviar Email de Verificación', 'wp-cupon-whatsapp' ) . '
                    </button>
                </div>
            </div>
        ';
    }

    /**
     * Renderizar paso 2: Información básica
     */
    private static function render_basic_info_step( $user_data ) {
        ob_start();
        ?>
        <form class="wpcw-onboarding-form">
            <h3><?php _e( 'Información Personal', 'wp-cupon-whatsapp' ); ?></h3>
            <p><?php _e( 'Completa tu información personal para personalizar tu experiencia.', 'wp-cupon-whatsapp' ); ?></p>

            <div class="wpcw-form-grid">
                <div class="wpcw-form-row">
                    <label for="first_name"><?php _e( 'Nombre(s)', 'wp-cupon-whatsapp' ); ?> *</label>
                    <input type="text" id="first_name" name="first_name" required
                           value="<?php echo esc_attr( $user_data['first_name'] ?? '' ); ?>">
                </div>

                <div class="wpcw-form-row">
                    <label for="last_name"><?php _e( 'Apellido(s)', 'wp-cupon-whatsapp' ); ?> *</label>
                    <input type="text" id="last_name" name="last_name" required
                           value="<?php echo esc_attr( $user_data['last_name'] ?? '' ); ?>">
                </div>

                <div class="wpcw-form-row">
                    <label for="dni"><?php _e( 'DNI/Cédula', 'wp-cupon-whatsapp' ); ?> *</label>
                    <input type="text" id="dni" name="dni" required
                           value="<?php echo esc_attr( $user_data['dni'] ?? '' ); ?>"
                           placeholder="12345678">
                </div>

                <div class="wpcw-form-row">
                    <label for="birth_date"><?php _e( 'Fecha de Nacimiento', 'wp-cupon-whatsapp' ); ?> *</label>
                    <input type="date" id="birth_date" name="birth_date" required
                           value="<?php echo esc_attr( $user_data['birth_date'] ?? '' ); ?>">
                </div>

                <div class="wpcw-form-row">
                    <label for="gender"><?php _e( 'Género', 'wp-cupon-whatsapp' ); ?></label>
                    <select id="gender" name="gender">
                        <option value=""><?php _e( 'Seleccionar...', 'wp-cupon-whatsapp' ); ?></option>
                        <option value="M" <?php selected( $user_data['gender'] ?? '', 'M' ); ?>><?php _e( 'Masculino', 'wp-cupon-whatsapp' ); ?></option>
                        <option value="F" <?php selected( $user_data['gender'] ?? '', 'F' ); ?>><?php _e( 'Femenino', 'wp-cupon-whatsapp' ); ?></option>
                        <option value="O" <?php selected( $user_data['gender'] ?? '', 'O' ); ?>><?php _e( 'Otro', 'wp-cupon-whatsapp' ); ?></option>
                        <option value="N" <?php selected( $user_data['gender'] ?? '', 'N' ); ?>><?php _e( 'Prefiero no decir', 'wp-cupon-whatsapp' ); ?></option>
                    </select>
                </div>

                <div class="wpcw-form-row">
                    <label for="whatsapp"><?php _e( 'WhatsApp', 'wp-cupon-whatsapp' ); ?> *</label>
                    <input type="tel" id="whatsapp" name="whatsapp" required
                           value="<?php echo esc_attr( $user_data['whatsapp'] ?? '' ); ?>"
                           placeholder="+5491123456789">
                </div>
            </div>

            <div class="wpcw-form-actions">
                <button type="submit" class="button button-primary button-large">
                    <?php _e( 'Guardar y Continuar', 'wp-cupon-whatsapp' ); ?>
                </button>
            </div>
        </form>

        <style>
            .wpcw-form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
            .wpcw-form-row { margin-bottom: 15px; }
            .wpcw-form-row label { display: block; margin-bottom: 5px; font-weight: 600; }
            .wpcw-form-row input, .wpcw-form-row select { width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; }
            .wpcw-form-actions { text-align: center; margin-top: 30px; }
        </style>
        <?php
        return ob_get_clean();
    }

    /**
     * Renderizar paso 3: Selección de institución
     */
    private static function render_institution_step( $user_data ) {
        ob_start();
        ?>
        <form class="wpcw-onboarding-form">
            <h3><?php _e( 'Asociación Institucional', 'wp-cupon-whatsapp' ); ?></h3>
            <p><?php _e( 'Selecciona si perteneces a alguna institución adherida o si eres usuario independiente.', 'wp-cupon-whatsapp' ); ?></p>

            <div class="wpcw-institution-type">
                <div class="wpcw-type-option">
                    <input type="radio" id="type_employee" name="institution_type" value="employee"
                           <?php checked( $user_data['institution_type'] ?? '', 'employee' ); ?>>
                    <label for="type_employee">
                        <strong><?php _e( 'Soy Empleado', 'wp-cupon-whatsapp' ); ?></strong>
                        <p><?php _e( 'Perteneczo a una empresa o institución adherida al programa.', 'wp-cupon-whatsapp' ); ?></p>
                    </label>
                </div>

                <div class="wpcw-type-option">
                    <input type="radio" id="type_independent" name="institution_type" value="independent"
                           <?php checked( $user_data['institution_type'] ?? 'independent', 'independent' ); ?>>
                    <label for="type_independent">
                        <strong><?php _e( 'Usuario Independiente', 'wp-cupon-whatsapp' ); ?></strong>
                        <p><?php _e( 'No pertenezco a ninguna institución específica.', 'wp-cupon-whatsapp' ); ?></p>
                    </label>
                </div>
            </div>

            <div id="employee-fields" class="wpcw-conditional-fields" style="display: none;">
                <h4><?php _e( 'Información de Empleo', 'wp-cupon-whatsapp' ); ?></h4>
                <div class="wpcw-form-row">
                    <label for="institution_search"><?php _e( 'Buscar Institución', 'wp-cupon-whatsapp' ); ?></label>
                    <input type="text" id="institution_search" name="institution_search"
                           placeholder="<?php _e( 'Escribe el nombre de tu empresa...', 'wp-cupon-whatsapp' ); ?>"
                           value="<?php echo esc_attr( $user_data['institution_search'] ?? '' ); ?>">
                    <div id="institution-results" class="wpcw-search-results"></div>
                </div>

                <div class="wpcw-form-row">
                    <label for="employee_code"><?php _e( 'Código de Empleado (opcional)', 'wp-cupon-whatsapp' ); ?></label>
                    <input type="text" id="employee_code" name="employee_code"
                           value="<?php echo esc_attr( $user_data['employee_code'] ?? '' ); ?>">
                </div>
            </div>

            <div class="wpcw-form-actions">
                <button type="submit" class="button button-primary button-large">
                    <?php _e( 'Guardar y Continuar', 'wp-cupon-whatsapp' ); ?>
                </button>
            </div>
        </form>

        <style>
            .wpcw-institution-type { margin: 20px 0; }
            .wpcw-type-option { margin: 15px 0; padding: 20px; border: 2px solid #e1e1e1; border-radius: 8px; cursor: pointer; }
            .wpcw-type-option:hover { border-color: #007cba; }
            .wpcw-type-option input[type="radio"] { display: none; }
            .wpcw-type-option input[type="radio"]:checked + label { border-color: #007cba; background: #f0f8ff; }
            .wpcw-type-option label { display: block; margin: 0; cursor: pointer; }
            .wpcw-type-option label strong { display: block; margin-bottom: 5px; }
            .wpcw-conditional-fields { margin-top: 30px; padding: 20px; background: #f9f9f9; border-radius: 8px; }
        </style>

        <script>
            jQuery(document).ready(function($) {
                // Mostrar/ocultar campos condicionales
                $('input[name="institution_type"]').on('change', function() {
                    if ($(this).val() === 'employee') {
                        $('#employee-fields').slideDown();
                    } else {
                        $('#employee-fields').slideUp();
                    }
                });

                // Trigger inicial
                $('input[name="institution_type"]:checked').trigger('change');
            });
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Renderizar paso 4: Preferencias
     */
    private static function render_preferences_step( $user_data ) {
        $categories = self::get_available_categories();
        ob_start();
        ?>
        <form class="wpcw-onboarding-form">
            <h3><?php _e( 'Preferencias Personales', 'wp-cupon-whatsapp' ); ?></h3>
            <p><?php _e( 'Selecciona tus categorías favoritas para recibir cupones personalizados.', 'wp-cupon-whatsapp' ); ?></p>

            <div class="wpcw-categories-grid">
                <?php foreach ( $categories as $category ) : ?>
                    <div class="wpcw-category-card">
                        <input type="checkbox" id="cat_<?php echo esc_attr( $category['id'] ); ?>"
                               name="favorite_categories[]" value="<?php echo esc_attr( $category['id'] ); ?>"
                               <?php checked( in_array( $category['id'], $user_data['favorite_categories'] ?? [] ) ); ?>>
                        <label for="cat_<?php echo esc_attr( $category['id'] ); ?>">
                            <span class="category-icon"><?php echo esc_html( $category['icon'] ); ?></span>
                            <span class="category-name"><?php echo esc_html( $category['name'] ); ?></span>
                            <span class="category-count"><?php echo esc_html( $category['business_count'] ); ?> negocios</span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="wpcw-notification-preferences">
                <h4><?php _e( 'Preferencias de Notificaciones', 'wp-cupon-whatsapp' ); ?></h4>

                <div class="wpcw-pref-section">
                    <h5><?php _e( '📧 Notificaciones por Email', 'wp-cupon-whatsapp' ); ?></h5>
                    <label>
                        <input type="checkbox" name="email_notifications" value="1"
                               <?php checked( $user_data['email_notifications'] ?? true ); ?>>
                        <?php _e( 'Recibir notificaciones por email', 'wp-cupon-whatsapp' ); ?>
                    </label>
                </div>

                <div class="wpcw-pref-section">
                    <h5><?php _e( '📱 Notificaciones por WhatsApp', 'wp-cupon-whatsapp' ); ?></h5>
                    <label>
                        <input type="checkbox" name="whatsapp_notifications" value="1"
                               <?php checked( $user_data['whatsapp_notifications'] ?? true ); ?>>
                        <?php _e( 'Recibir notificaciones por WhatsApp', 'wp-cupon-whatsapp' ); ?>
                    </label>
                </div>

                <div class="wpcw-pref-section">
                    <h5><?php _e( '🔔 Notificaciones Push', 'wp-cupon-whatsapp' ); ?></h5>
                    <label>
                        <input type="checkbox" name="push_notifications" value="1"
                               <?php checked( $user_data['push_notifications'] ?? false ); ?>>
                        <?php _e( 'Recibir notificaciones push en el navegador', 'wp-cupon-whatsapp' ); ?>
                    </label>
                </div>
            </div>

            <div class="wpcw-form-actions">
                <button type="submit" class="button button-primary button-large">
                    <?php _e( 'Guardar Preferencias', 'wp-cupon-whatsapp' ); ?>
                </button>
            </div>
        </form>

        <style>
            .wpcw-categories-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
            .wpcw-category-card { position: relative; }
            .wpcw-category-card input[type="checkbox"] { position: absolute; opacity: 0; }
            .wpcw-category-card label { display: block; padding: 15px; border: 2px solid #e1e1e1; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; }
            .wpcw-category-card input[type="checkbox"]:checked + label { border-color: #007cba; background: #f0f8ff; }
            .category-icon { font-size: 24px; display: block; margin-bottom: 8px; }
            .category-name { display: block; font-weight: 600; margin-bottom: 4px; }
            .category-count { display: block; font-size: 12px; color: #666; }
            .wpcw-notification-preferences { margin-top: 30px; }
            .wpcw-pref-section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 8px; }
            .wpcw-pref-section h5 { margin-top: 0; }
            .wpcw-pref-section label { display: block; margin: 10px 0; cursor: pointer; }
        </style>
        <?php
        return ob_get_clean();
    }

    /**
     * Renderizar paso 5: Completado
     */
    private static function render_completion_step( $user_data ) {
        ob_start();
        ?>
        <div class="wpcw-onboarding-completion">
            <div class="wpcw-completion-header">
                <div class="wpcw-success-icon">🎉</div>
                <h3><?php _e( '¡Felicitaciones! Tu perfil está completo', 'wp-cupon-whatsapp' ); ?></h3>
                <p><?php _e( 'Ahora puedes disfrutar de todos los beneficios de WP Cupón WhatsApp.', 'wp-cupon-whatsapp' ); ?></p>
            </div>

            <div class="wpcw-completion-summary">
                <h4><?php _e( 'Resumen de tu configuración:', 'wp-cupon-whatsapp' ); ?></h4>
                <ul class="wpcw-summary-list">
                    <li>✅ <?php _e( 'Email verificado', 'wp-cupon-whatsapp' ); ?></li>
                    <li>✅ <?php _e( 'Información personal guardada', 'wp-cupon-whatsapp' ); ?></li>
                    <li>✅ <?php _e( 'Preferencias configuradas', 'wp-cupon-whatsapp' ); ?></li>
                    <li>✅ <?php _e( 'Perfil 100% completo', 'wp-cupon-whatsapp' ); ?></li>
                </ul>
            </div>

            <div class="wpcw-next-steps">
                <h4><?php _e( '¿Qué puedes hacer ahora?', 'wp-cupon-whatsapp' ); ?></h4>
                <div class="wpcw-action-cards">
                    <div class="wpcw-action-card">
                        <div class="action-icon">🎫</div>
                        <h5><?php _e( 'Ver Cupones Disponibles', 'wp-cupon-whatsapp' ); ?></h5>
                        <p><?php _e( 'Explora cupones exclusivos para ti', 'wp-cupon-whatsapp' ); ?></p>
                        <a href="<?php echo esc_url( home_url( '/mis-cupones' ) ); ?>" class="button">
                            <?php _e( 'Ver Cupones', 'wp-cupon-whatsapp' ); ?>
                        </a>
                    </div>

                    <div class="wpcw-action-card">
                        <div class="action-icon">🏪</div>
                        <h5><?php _e( 'Buscar Comercios', 'wp-cupon-whatsapp' ); ?></h5>
                        <p><?php _e( 'Descubre nuevos lugares con descuentos', 'wp-cupon-whatsapp' ); ?></p>
                        <a href="<?php echo esc_url( home_url( '/comercios' ) ); ?>" class="button">
                            <?php _e( 'Buscar Comercios', 'wp-cupon-whatsapp' ); ?>
                        </a>
                    </div>

                    <div class="wpcw-action-card">
                        <div class="action-icon">⚙️</div>
                        <h5><?php _e( 'Configurar Perfil', 'wp-cupon-whatsapp' ); ?></h5>
                        <p><?php _e( 'Ajusta tus preferencias en cualquier momento', 'wp-cupon-whatsapp' ); ?></p>
                        <a href="<?php echo esc_url( home_url( '/mi-cuenta' ) ); ?>" class="button">
                            <?php _e( 'Mi Perfil', 'wp-cupon-whatsapp' ); ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="wpcw-completion-actions">
                <a href="<?php echo esc_url( home_url( '/dashboard' ) ); ?>" class="button button-primary button-large">
                    <?php _e( 'Ir al Dashboard', 'wp-cupon-whatsapp' ); ?>
                </a>
            </div>
        </div>

        <style>
            .wpcw-onboarding-completion { text-align: center; }
            .wpcw-completion-header { margin: 40px 0; }
            .wpcw-success-icon { font-size: 64px; margin-bottom: 20px; }
            .wpcw-completion-summary { background: #f0f8ff; padding: 20px; border-radius: 8px; margin: 30px 0; }
            .wpcw-summary-list { text-align: left; display: inline-block; }
            .wpcw-summary-list li { margin: 10px 0; }
            .wpcw-next-steps { margin: 40px 0; }
            .wpcw-action-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0; }
            .wpcw-action-card { background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #e1e1e1; }
            .action-icon { font-size: 32px; margin-bottom: 10px; }
            .wpcw-action-card h5 { margin: 10px 0; }
            .wpcw-completion-actions { margin-top: 40px; }
        </style>
        <?php
        return ob_get_clean();
    }

    /**
     * Obtener datos de onboarding del usuario
     */
    private static function get_user_onboarding_data( $user_id ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpcw_user_profiles';
        $data = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table_name} WHERE user_id = %d", $user_id ),
            ARRAY_A
        );

        if ( ! $data ) {
            return array();
        }

        // Decodificar campos JSON
        if ( ! empty( $data['favorite_categories'] ) ) {
            $data['favorite_categories'] = json_decode( $data['favorite_categories'], true );
        }

        if ( ! empty( $data['notification_preferences'] ) ) {
            $data['notification_preferences'] = json_decode( $data['notification_preferences'], true );
        }

        return $data;
    }

    /**
     * Obtener categorías disponibles
     */
    private static function get_available_categories() {
        $businesses = get_posts([
            'post_type' => 'wpcw_business',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ]);

        $categories = [];
        foreach ( $businesses as $business ) {
            $business_categories = wp_get_post_terms( $business->ID, 'wpcw_business_category' );
            foreach ( $business_categories as $category ) {
                if ( ! isset( $categories[ $category->term_id ] ) ) {
                    $categories[ $category->term_id ] = [
                        'id' => $category->term_id,
                        'name' => $category->name,
                        'icon' => get_term_meta( $category->term_id, 'wpcw_category_icon', true ) ?: '🏪',
                        'business_count' => 0
                    ];
                }
                $categories[ $category->term_id ]['business_count']++;
            }
        }

        return array_values( $categories );
    }

    /**
     * Obtener etiqueta del paso
     */
    private static function get_step_label( $step ) {
        $labels = [
            1 => __( 'Verificación Email', 'wp-cupon-whatsapp' ),
            2 => __( 'Información Personal', 'wp-cupon-whatsapp' ),
            3 => __( 'Institución', 'wp-cupon-whatsapp' ),
            4 => __( 'Preferencias', 'wp-cupon-whatsapp' ),
            5 => __( 'Completado', 'wp-cupon-whatsapp' )
        ];

        return $labels[ $step ] ?? '';
    }

    /**
     * Actualizar progreso del onboarding
     */
    private static function update_onboarding_progress( $user_id, $step, $completed = false ) {
        update_user_meta( $user_id, 'wpcw_onboarding_step', $step );
        update_user_meta( $user_id, 'wpcw_profile_completion', self::STEP_COMPLETION[ $step ] );

        if ( $completed ) {
            update_user_meta( $user_id, 'wpcw_onboarding_completed', true );
            update_user_meta( $user_id, 'wpcw_onboarding_completed_at', current_time( 'mysql' ) );

            // Trigger acciones de completado
            do_action( 'wpcw_onboarding_completed', $user_id );
        }
    }

    /**
     * Manejar actualización de onboarding vía AJAX
     */
    public static function handle_onboarding_update() {
        try {
            // Verificar nonce
            if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'wpcw_onboarding_nonce' ) ) {
                throw new Exception( __( 'Verificación de seguridad fallida', 'wp-cupon-whatsapp' ) );
            }

            // Verificar que el usuario esté logueado
            if ( ! is_user_logged_in() ) {
                throw new Exception( __( 'Usuario no autenticado', 'wp-cupon-whatsapp' ) );
            }

            $user_id = get_current_user_id();
            $step = intval( $_POST['step'] ?? 1 );

            // Procesar datos según el paso
            $result = self::process_step_data( $user_id, $step, $_POST );

            if ( $result['success'] ) {
                // Actualizar progreso
                $next_step = $step + 1;
                $is_completed = ( $next_step > count( self::STEPS ) );

                if ( $is_completed ) {
                    self::update_onboarding_progress( $user_id, count( self::STEPS ), true );
                    $redirect_url = home_url( '/dashboard' );
                } else {
                    self::update_onboarding_progress( $user_id, $next_step, false );
                    $redirect_url = add_query_arg( 'step', $next_step, remove_query_arg( 'step' ) );
                }

                wp_send_json_success( array(
                    'message' => $result['message'],
                    'redirect' => $redirect_url,
                    'completed' => $is_completed
                ) );
            } else {
                wp_send_json_error( array( 'message' => $result['message'] ) );
            }

        } catch ( Exception $e ) {
            WPCW_Logger::log( 'error', 'Onboarding update error: ' . $e->getMessage(), array(
                'user_id' => get_current_user_id(),
                'step' => $_POST['step'] ?? 'unknown'
            ) );

            wp_send_json_error( array( 'message' => __( 'Error al procesar los datos', 'wp-cupon-whatsapp' ) ) );
        }
    }

    /**
     * Procesar datos del paso específico
     */
    private static function process_step_data( $user_id, $step, $data ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpcw_user_profiles';

        switch ( $step ) {
            case 2: // Información básica
                return self::process_basic_info( $user_id, $data, $table_name );

            case 3: // Asociación institucional
                return self::process_institution_info( $user_id, $data, $table_name );

            case 4: // Preferencias
                return self::process_preferences( $user_id, $data, $table_name );

            default:
                return array(
                    'success' => true,
                    'message' => __( 'Paso procesado correctamente', 'wp-cupon-whatsapp' )
                );
        }
    }

    /**
     * Procesar información básica
     */
    private static function process_basic_info( $user_id, $data, $table_name ) {
        global $wpdb;

        // Validar datos requeridos
        $required_fields = array( 'first_name', 'last_name', 'dni', 'birth_date', 'whatsapp' );
        foreach ( $required_fields as $field ) {
            if ( empty( $data[ $field ] ) ) {
                return array(
                    'success' => false,
                    'message' => sprintf( __( 'El campo %s es requerido', 'wp-cupon-whatsapp' ), $field )
                );
            }
        }

        // Sanitizar datos
        $sanitized_data = array(
            'first_name' => sanitize_text_field( $data['first_name'] ),
            'last_name' => sanitize_text_field( $data['last_name'] ),
            'dni' => sanitize_text_field( $data['dni'] ),
            'birth_date' => sanitize_text_field( $data['birth_date'] ),
            'gender' => sanitize_text_field( $data['gender'] ?? '' ),
            'whatsapp' => sanitize_text_field( $data['whatsapp'] ),
            'updated_at' => current_time( 'mysql' )
        );

        // Actualizar o insertar
        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT user_id FROM {$table_name} WHERE user_id = %d",
            $user_id
        ) );

        if ( $existing ) {
            $result = $wpdb->update( $table_name, $sanitized_data, array( 'user_id' => $user_id ) );
        } else {
            $sanitized_data['user_id'] = $user_id;
            $sanitized_data['onboarding_step'] = 2;
            $sanitized_data['profile_completion_percentage'] = self::STEP_COMPLETION[2];
            $sanitized_data['created_at'] = current_time( 'mysql' );
            $result = $wpdb->insert( $table_name, $sanitized_data );
        }

        if ( $result === false ) {
            return array(
                'success' => false,
                'message' => __( 'Error al guardar la información personal', 'wp-cupon-whatsapp' )
            );
        }

        return array(
            'success' => true,
            'message' => __( 'Información personal guardada correctamente', 'wp-cupon-whatsapp' )
        );
    }

    /**
     * Procesar información institucional
     */
    private static function process_institution_info( $user_id, $data, $table_name ) {
        global $wpdb;

        $institution_type = sanitize_text_field( $data['institution_type'] ?? 'independent' );

        $update_data = array(
            'institution_type' => $institution_type,
            'updated_at' => current_time( 'mysql' )
        );

        if ( $institution_type === 'employee' ) {
            $update_data['institution_search'] = sanitize_text_field( $data['institution_search'] ?? '' );
            $update_data['employee_code'] = sanitize_text_field( $data['employee_code'] ?? '' );

            // Aquí podrías agregar lógica para verificar el código de empleado
            // y asociar automáticamente con la institución
        }

        $result = $wpdb->update( $table_name, $update_data, array( 'user_id' => $user_id ) );

        if ( $result === false ) {
            return array(
                'success' => false,
                'message' => __( 'Error al guardar la información institucional', 'wp-cupon-whatsapp' )
            );
        }

        return array(
            'success' => true,
            'message' => __( 'Información institucional guardada correctamente', 'wp-cupon-whatsapp' )
        );
    }

    /**
     * Procesar preferencias del usuario
     */
    private static function process_preferences( $user_id, $data, $table_name ) {
        global $wpdb;

        // Procesar categorías favoritas
        $favorite_categories = isset( $data['favorite_categories'] ) ?
            array_map( 'intval', (array) $data['favorite_categories'] ) : array();

        // Procesar preferencias de notificación
        $notification_preferences = array(
            'email' => array(
                'enabled' => ! empty( $data['email_notifications'] ),
                'frequency' => 'daily'
            ),
            'whatsapp' => array(
                'enabled' => ! empty( $data['whatsapp_notifications'] )
            ),
            'push' => array(
                'enabled' => ! empty( $data['push_notifications'] )
            )
        );

        $update_data = array(
            'favorite_categories' => wp_json_encode( $favorite_categories ),
            'notification_preferences' => wp_json_encode( $notification_preferences ),
            'updated_at' => current_time( 'mysql' )
        );

        $result = $wpdb->update( $table_name, $update_data, array( 'user_id' => $user_id ) );

        if ( $result === false ) {
            return array(
                'success' => false,
                'message' => __( 'Error al guardar las preferencias', 'wp-cupon-whatsapp' )
            );
        }

        // Enviar cupones iniciales basados en preferencias
        self::send_initial_coupons_based_on_preferences( $user_id, $favorite_categories );

        return array(
            'success' => true,
            'message' => __( 'Preferencias guardadas correctamente', 'wp-cupon-whatsapp' )
        );
    }

    /**
     * Enviar cupones iniciales basados en preferencias
     */
    private static function send_initial_coupons_based_on_preferences( $user_id, $categories ) {
        if ( empty( $categories ) ) {
            return;
        }

        // Obtener cupones de las categorías preferidas
        $args = array(
            'post_type' => 'shop_coupon',
            'posts_per_page' => 3,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => '_wpcw_enabled',
                    'value' => 'yes',
                    'compare' => '='
                )
            ),
            'tax_query' => array(
                array(
                    'taxonomy' => 'wpcw_coupon_category',
                    'field' => 'term_id',
                    'terms' => $categories,
                    'operator' => 'IN'
                )
            )
        );

        $coupons = get_posts( $args );

        if ( ! empty( $coupons ) ) {
            // Enviar notificación con cupones sugeridos
            WPCW_Notification_Manager::send_notification( $user_id, 'welcome_coupons', array(
                'coupons' => $coupons,
                'categories' => $categories
            ) );
        }
    }

    /**
     * Manejar skip de onboarding
     */
    public static function handle_onboarding_skip() {
        try {
            // Verificar nonce
            if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'wpcw_onboarding_nonce' ) ) {
                throw new Exception( __( 'Verificación de seguridad fallida', 'wp-cupon-whatsapp' ) );
            }

            if ( ! is_user_logged_in() ) {
                throw new Exception( __( 'Usuario no autenticado', 'wp-cupon-whatsapp' ) );
            }

            $user_id = get_current_user_id();

            // Marcar onboarding como completado con datos mínimos
            self::update_onboarding_progress( $user_id, count( self::STEPS ), true );

            // Crear perfil básico
            global $wpdb;
            $table_name = $wpdb->prefix . 'wpcw_user_profiles';

            $wpdb->replace(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'onboarding_step' => count( self::STEPS ),
                    'profile_completion_percentage' => 100,
                    'onboarding_completed' => true,
                    'updated_at' => current_time( 'mysql' )
                ),
                array( '%d', '%d', '%d', '%d', '%s' )
            );

            wp_send_json_success( array(
                'message' => __( 'Onboarding omitido. Puedes completar tu perfil más tarde.', 'wp-cupon-whatsapp' ),
                'redirect' => home_url( '/dashboard' )
            ) );

        } catch ( Exception $e ) {
            WPCW_Logger::log( 'error', 'Onboarding skip error: ' . $e->getMessage() );
            wp_send_json_error( array( 'message' => __( 'Error al omitir el onboarding', 'wp-cupon-whatsapp' ) ) );
        }
    }

    /**
     * Agregar campos de onboarding al perfil de usuario (admin)
     */
    public static function add_onboarding_fields_to_profile( $user ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $onboarding_data = self::get_user_onboarding_data( $user->ID );
        ?>
        <h3><?php _e( 'WP Cupón WhatsApp - Onboarding', 'wp-cupon-whatsapp' ); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="wpcw_onboarding_step"><?php _e( 'Paso Actual', 'wp-cupon-whatsapp' ); ?></label></th>
                <td>
                    <select name="wpcw_onboarding_step" id="wpcw_onboarding_step">
                        <?php for ( $i = 1; $i <= count( self::STEPS ); $i++ ) : ?>
                            <option value="<?php echo $i; ?>" <?php selected( $onboarding_data['onboarding_step'] ?? 1, $i ); ?>>
                                <?php echo self::get_step_label( $i ); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="wpcw_profile_completion"><?php _e( 'Completitud del Perfil (%)', 'wp-cupon-whatsapp' ); ?></label></th>
                <td>
                    <input type="number" name="wpcw_profile_completion" id="wpcw_profile_completion"
                           value="<?php echo esc_attr( $onboarding_data['profile_completion_percentage'] ?? 0 ); ?>"
                           min="0" max="100" step="1">
                </td>
            </tr>
            <tr>
                <th><label for="wpcw_onboarding_completed"><?php _e( 'Onboarding Completado', 'wp-cupon-whatsapp' ); ?></label></th>
                <td>
                    <input type="checkbox" name="wpcw_onboarding_completed" id="wpcw_onboarding_completed"
                           value="1" <?php checked( $onboarding_data['onboarding_completed'] ?? false ); ?>>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Guardar campos de onboarding desde el perfil de usuario
     */
    public static function save_onboarding_fields_from_profile( $user_id ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wpcw_user_profiles';

        $update_data = array(
            'onboarding_step' => intval( $_POST['wpcw_onboarding_step'] ?? 1 ),
            'profile_completion_percentage' => intval( $_POST['wpcw_profile_completion'] ?? 0 ),
            'onboarding_completed' => ! empty( $_POST['wpcw_onboarding_completed'] ),
            'updated_at' => current_time( 'mysql' )
        );

        $wpdb->replace(
            $table_name,
            array_merge( array( 'user_id' => $user_id ), $update_data ),
            array( '%d', '%d', '%d', '%d', '%s' )
        );
    }

    /**
     * Crear tabla de perfiles de usuario
     */
    public static function create_user_profiles_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpcw_user_profiles';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            user_id bigint(20) UNSIGNED NOT NULL,
            first_name varchar(50) DEFAULT '',
            last_name varchar(50) DEFAULT '',
            dni varchar(20) DEFAULT '',
            birth_date date NULL,
            gender char(1) DEFAULT 'N',
            nationality varchar(50) DEFAULT '',
            phone varchar(20) DEFAULT '',
            whatsapp varchar(20) DEFAULT '',
            province varchar(50) DEFAULT '',
            city varchar(50) DEFAULT '',
            postal_code varchar(10) DEFAULT '',
            institution_type enum('employee','independent') DEFAULT 'independent',
            institution_id bigint(20) UNSIGNED NULL,
            employee_code varchar(50) DEFAULT '',
            employment_verified boolean DEFAULT FALSE,
            favorite_categories json NULL,
            notification_preferences json NULL,
            location_preferences json NULL,
            onboarding_completed boolean DEFAULT FALSE,
            onboarding_step tinyint DEFAULT 1,
            profile_completion_percentage tinyint DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id),
            KEY institution_id (institution_id),
            KEY onboarding_completed (onboarding_completed),
            KEY institution_type (institution_type)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        return $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) === $table_name;
    }
}

// Inicializar el sistema de onboarding
WPCW_Onboarding_Manager::init();
