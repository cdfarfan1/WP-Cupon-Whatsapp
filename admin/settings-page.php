<?php
/**
 * WP Canje Cupon Whatsapp - Settings Page
 *
 * Handles the plugin's settings page, registers settings, sections, and fields.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Renders the main settings page for the plugin.
 */
function wpcw_render_plugin_settings_page() {
    // Verificar si el usuario actual tiene permisos para acceder a la página de ajustes
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'No tienes suficientes permisos para acceder a esta página.', 'wp-cupon-whatsapp' ) );
    }
    ?>
    <div class="wrap wpcw-settings-page">
        <h1><?php echo esc_html( get_admin_page_title() ); // Obtiene el título definido en add_menu_page ?></h1>

        <form method="post" action="options.php">
            <?php
            settings_fields( 'wpcw_options_group' );
            do_settings_sections( 'wpcw_plugin_settings_page_slug' );
            submit_button( __( 'Guardar Cambios', 'wp-cupon-whatsapp' ) );
            ?>
        </form>
    </div>
    <?php
}

/**
 * Initializes the WordPress Settings API for WPCW plugin.
 */
function wpcw_settings_api_init() {
    $page_slug = 'wpcw_plugin_settings_page_slug';
    $option_group = 'wpcw_options_group';

    register_setting(
        $option_group,
        'wpcw_recaptcha_site_key',
        array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => ''
        )
    );
    register_setting(
        $option_group,
        'wpcw_recaptcha_secret_key',
        array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => ''
        )
    );

    add_settings_section(
        'wpcw_recaptcha_settings_section',
        __( 'Ajustes de Google reCAPTCHA v2', 'wp-cupon-whatsapp' ),
        'wpcw_recaptcha_section_callback',
        $page_slug
    );

    add_settings_field(
        'wpcw_field_recaptcha_site_key',
        __( 'Site Key (Clave del Sitio)', 'wp-cupon-whatsapp' ),
        'wpcw_render_text_input_field',
        $page_slug,
        'wpcw_recaptcha_settings_section',
        array(
            'option_name' => 'wpcw_recaptcha_site_key',
            'id'          => 'wpcw_recaptcha_site_key',
            'label_for'   => 'wpcw_recaptcha_site_key',
            'description' => __('Introduce tu Google reCAPTCHA v2 Site Key.', 'wp-cupon-whatsapp')
        )
    );
    add_settings_field(
        'wpcw_field_recaptcha_secret_key',
        __( 'Secret Key (Clave Secreta)', 'wp-cupon-whatsapp' ),
        'wpcw_render_text_input_field',
        $page_slug,
        'wpcw_recaptcha_settings_section',
        array(
            'option_name' => 'wpcw_recaptcha_secret_key',
            'id'          => 'wpcw_recaptcha_secret_key',
            'label_for'   => 'wpcw_recaptcha_secret_key',
            'description' => __('Introduce tu Google reCAPTCHA v2 Secret Key.', 'wp-cupon-whatsapp')
        )
    );

    register_setting(
        $option_group,
        'wpcw_required_fields_settings',
        array(
            'type'              => 'array',
            'sanitize_callback' => 'wpcw_sanitize_required_fields_array',
            'default'           => array(),
        )
    );

    add_settings_section(
        'wpcw_required_fields_section',
        __( 'Campos de Cliente Obligatorios en Registro/Perfil', 'wp-cupon-whatsapp' ),
        'wpcw_required_fields_section_callback',
        $page_slug
    );

    $option_name_required = 'wpcw_required_fields_settings';
    $configurable_fields = array(
        'dni_number' => __('DNI', 'wp-cupon-whatsapp'),
        'birth_date' => __('Fecha de Nacimiento', 'wp-cupon-whatsapp'),
        'whatsapp_number' => __('Número de WhatsApp', 'wp-cupon-whatsapp'),
    );

    foreach ( $configurable_fields as $key => $label ) {
        add_settings_field(
            'wpcw_field_req_' . $key,
            '',
            'wpcw_render_settings_checkbox_field',
            $page_slug,
            'wpcw_required_fields_section',
            array(
                'option_name' => $option_name_required,
                'field_key'   => $key,
                'id'          => 'wpcw_req_' . $key,
                'label'       => sprintf( __('Hacer obligatorio el campo "%s"', 'wp-cupon-whatsapp'), $label ),
            )
        );
    }

    // Añadir Sección para Utilidades
    add_settings_section(
        'wpcw_utilities_section',
        __( 'Utilidades del Plugin', 'wp-cupon-whatsapp' ),
        'wpcw_utilities_section_callback',
        $page_slug
    );

    // Añadir Campo para el Botón "Crear Páginas"
    add_settings_field(
        'wpcw_field_create_pages',
        __( 'Páginas del Plugin', 'wp-cupon-whatsapp' ),
        'wpcw_render_create_pages_button_field',
        $page_slug,
        'wpcw_utilities_section'
    );
}
add_action( 'admin_init', 'wpcw_settings_api_init' );

/**
 * Callback para la descripción de la sección de reCAPTCHA.
 */
function wpcw_recaptcha_section_callback() {
    echo '<p>' . esc_html__( 'Configura las claves de API para Google reCAPTCHA v2 para proteger tus formularios.', 'wp-cupon-whatsapp' ) . '</p>';
}

/**
 * Renderiza un campo de input de texto genérico para la Settings API.
 */
function wpcw_render_text_input_field( $args ) {
    $option_name = $args['option_name'];
    $option_value = get_option( $option_name );
    if ( $option_value === false && isset($args['default']) ) {
        $option_value = $args['default'];
    } elseif ($option_value === false) {
        $option_value = '';
    }
    $id = isset( $args['id'] ) ? $args['id'] : $option_name;
    echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $option_value ) . '" class="regular-text" />';
    if ( isset( $args['description'] ) && ! empty( $args['description'] ) ) {
        echo '<p class="description">' . wp_kses_post( $args['description'] ) . '</p>';
    }
}

/**
 * Callback para la descripción de la sección de Campos Obligatorios.
 */
function wpcw_required_fields_section_callback() {
    echo '<p>' . esc_html__( 'Selecciona qué campos personalizados de cliente deben ser obligatorios durante el registro y en la página de perfil "Mi Cuenta".', 'wp-cupon-whatsapp' ) . '</p>';
}

/**
 * Sanitiza el array de ajustes para los campos obligatorios.
 */
function wpcw_sanitize_required_fields_array( $input ) {
    $sanitized_input = array();
    $allowed_field_keys = array(
        'dni_number',
        'birth_date',
        'whatsapp_number',
        'user_institution_id',
        'user_favorite_coupon_categories'
    );
    if ( is_array( $input ) ) {
        foreach ( $allowed_field_keys as $key ) {
            $sanitized_input[$key] = ( isset( $input[$key] ) && $input[$key] == '1' ) ? '1' : '0';
        }
    }
    return $sanitized_input;
}

/**
 * Renderiza un campo de input de checkbox genérico para la Settings API.
 */
function wpcw_render_settings_checkbox_field( $args ) {
    $option_name = $args['option_name'];
    $field_key = $args['field_key'];
    $id = isset( $args['id'] ) ? $args['id'] : $option_name . '_' . $field_key;
    $label = isset( $args['label'] ) ? $args['label'] : '';
    $options = get_option( $option_name, array() );
    $is_checked = isset( $options[$field_key] ) && $options[$field_key] === '1';
    echo '<label for="' . esc_attr( $id ) . '">';
    echo '<input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $option_name . '[' . $field_key . ']' ) . '" value="1" ' . checked( $is_checked, true, false ) . ' /> ';
    echo esc_html( $label );
    echo '</label>';
    if ( isset( $args['description'] ) && ! empty( $args['description'] ) ) {
        echo '<p class="description">' . wp_kses_post( $args['description'] ) . '</p>';
    }
}

/**
 * Callback para la descripción de la sección de Utilidades.
 */
function wpcw_utilities_section_callback() {
    echo '<p>' . esc_html__( 'Herramientas adicionales para la gestión del plugin.', 'wp-cupon-whatsapp' ) . '</p>';
}

/**
 * Renderiza el botón para la utilidad "Crear Páginas".
 */
function wpcw_render_create_pages_button_field() {
    wp_nonce_field( 'wpcw_create_pages_action_nonce', 'wpcw_nonce_create_pages' );
    submit_button(
        __( 'Crear Páginas del Plugin', 'wp-cupon-whatsapp' ),
        'secondary',
        'wpcw_create_pages_action',
        true,
        null
    );
    echo '<p class="description">' . esc_html__('Haz clic aquí para crear automáticamente las páginas necesarias para los shortcodes del plugin (ej. Mis Cupones, Cupones Públicos, Solicitud de Adhesión). Las páginas no se duplicarán si ya existen y contienen el shortcode correcto o si ya fueron creadas por esta utilidad.', 'wp-cupon-whatsapp') . '</p>';
}

/**
 * Handles admin actions triggered from settings page or other admin areas.
 */
function wpcw_handle_admin_actions() {
    if ( isset( $_POST['wpcw_create_pages_action'] ) ) {
        check_admin_referer( 'wpcw_create_pages_action_nonce', 'wpcw_nonce_create_pages' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'No tienes permisos para realizar esta acción.', 'wp-cupon-whatsapp' ) );
        }
        wpcw_do_create_plugin_pages();
    }
}
add_action( 'admin_init', 'wpcw_handle_admin_actions' );

/**
 * Creates plugin pages if they don't exist or if the stored page ID is invalid.
 */
if ( ! function_exists( 'wpcw_do_create_plugin_pages' ) ) {
    function wpcw_do_create_plugin_pages() {
        $pages_to_create = array(
            array(
                'option_name' => 'wpcw_page_id_mis_cupones',
                'title'       => __( 'Mis Cupones Disponibles', 'wp-cupon-whatsapp' ),
                'shortcode'   => '[wpcw_mis_cupones]',
                'slug'        => 'mis-cupones-wpcw'
            ),
            array(
                'option_name' => 'wpcw_page_id_cupones_publicos',
                'title'       => __( 'Cupones Públicos', 'wp-cupon-whatsapp' ),
                'shortcode'   => '[wpcw_cupones_publicos]',
                'slug'        => 'cupones-publicos-wpcw'
            ),
            array(
                'option_name' => 'wpcw_page_id_solicitud_adhesion',
                'title'       => __( 'Solicitud de Adhesión', 'wp-cupon-whatsapp' ),
                'shortcode'   => '[wpcw_solicitud_adhesion_form]',
                'slug'        => 'solicitud-adhesion-wpcw'
            ),
        );

        $created_pages_count = 0;
        $existing_pages_count = 0;
        $error_pages_count = 0;

        foreach ( $pages_to_create as $page_config ) {
            $page_id = get_option( $page_config['option_name'] );
            $page_exists_valid = false;

            if ( $page_id ) {
                $existing_post = get_post( $page_id );
                if ( $existing_post && 'page' === $existing_post->post_type && 'trash' !== $existing_post->post_status ) {
                    if ( has_shortcode( $existing_post->post_content, str_replace(array('[', ']'), '', $page_config['shortcode']) ) ) {
                        $page_exists_valid = true;
                    } else {
                         add_settings_error(
                            'wpcw_options_group',
                            'page_content_mismatch_' . sanitize_key($page_config['slug']),
                            sprintf( __('La página "%s" existe (ID: %d) pero su contenido no coincide con el shortcode esperado (%s). Considera revisarla manualmente o eliminarla para que se recree.', 'wp-cupon-whatsapp'), $page_config['title'], $page_id, $page_config['shortcode'] ),
                            'warning'
                        );
                        $page_exists_valid = true; // Still count as existing to avoid auto-recreation for now
                    }
                }
            }

            if ( $page_exists_valid ) {
                $existing_pages_count++;
            } else {
                $page_data = array(
                    'post_title'     => $page_config['title'],
                    'post_content'   => $page_config['shortcode'],
                    'post_status'    => 'publish',
                    'post_type'      => 'page',
                    'post_name'      => $page_config['slug'],
                    'comment_status' => 'closed',
                    'ping_status'    => 'closed',
                );
                $new_page_id = wp_insert_post( $page_data, true );

                if ( is_wp_error( $new_page_id ) ) {
                    $error_pages_count++;
                    add_settings_error(
                        'wpcw_options_group',
                        'page_creation_error_' . sanitize_key($page_config['slug']),
                        sprintf( __('Error al crear la página "%s": %s', 'wp-cupon-whatsapp'), $page_config['title'], $new_page_id->get_error_message() ),
                        'error'
                    );
                } else {
                    $created_pages_count++;
                    update_option( $page_config['option_name'], $new_page_id );
                    add_settings_error(
                        'wpcw_options_group',
                        'page_created_' . sanitize_key($page_config['slug']),
                        sprintf( __('Página "%s" creada exitosamente.', 'wp-cupon-whatsapp'), $page_config['title'] ),
                        'success'
                    );
                }
            }
        }

        if ($created_pages_count > 0 && $error_pages_count === 0) {
             add_settings_error('wpcw_options_group', 'some_pages_created', sprintf(_n('%d página del plugin fue creada exitosamente.', '%d páginas del plugin fueron creadas exitosamente.', $created_pages_count, 'wp-cupon-whatsapp'), $created_pages_count), 'success');
        } elseif ($created_pages_count === 0 && $error_pages_count === 0 && $existing_pages_count === count($pages_to_create)) {
             add_settings_error('wpcw_options_group', 'all_pages_exist', __('Todas las páginas del plugin necesarias ya existían y parecen estar configuradas correctamente.', 'wp-cupon-whatsapp'), 'info');
        } elseif ($error_pages_count > 0) {
            add_settings_error('wpcw_options_group', 'pages_creation_errors', __('Algunas páginas no pudieron ser creadas. Revisa los mensajes de error individuales.', 'wp-cupon-whatsapp'), 'error');
        } elseif ($created_pages_count > 0 && $error_pages_count === 0 && $existing_pages_count > 0 && ($created_pages_count + $existing_pages_count === count($pages_to_create)) ){
            add_settings_error('wpcw_options_group', 'pages_creation_mixed_created', sprintf(_n('%d página del plugin fue creada exitosamente. Las demás ya existían.', '%d páginas del plugin fueron creadas exitosamente. Las demás ya existían.', $created_pages_count, 'wp-cupon-whatsapp'), $created_pages_count), 'success');
        }
    }
}
?>
