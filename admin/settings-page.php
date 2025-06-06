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
            // Este grupo de ajustes ('wpcw_options_group') debe ser registrado en la función de inicialización de la API de Ajustes
            settings_fields( 'wpcw_options_group' );

            // Este slug de página ('wpcw_plugin_settings') se usará para añadir secciones y campos
            // El slug para do_settings_sections debe coincidir con el usado en add_settings_section
            do_settings_sections( 'wpcw_plugin_settings_page_slug' ); // Usaremos este slug para las secciones

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
    $page_slug = 'wpcw_plugin_settings_page_slug'; // Slug de la página de ajustes
    $option_group = 'wpcw_options_group';     // Nombre del grupo de opciones

    // Registrar los ajustes (opciones que se guardarán en la tabla wp_options)
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

    // Añadir Sección para reCAPTCHA
    add_settings_section(
        'wpcw_recaptcha_settings_section', // ID de la sección
        __( 'Ajustes de Google reCAPTCHA v2', 'wp-cupon-whatsapp' ), // Título de la sección
        'wpcw_recaptcha_section_callback', // Función callback para renderizar la descripción de la sección
        $page_slug // Slug de la página donde se mostrará esta sección
    );

    // Añadir Campos a la Sección de reCAPTCHA
    add_settings_field(
        'wpcw_field_recaptcha_site_key', // ID del campo
        __( 'Site Key (Clave del Sitio)', 'wp-cupon-whatsapp' ), // Título del campo
        'wpcw_render_text_input_field', // Función callback para renderizar el campo
        $page_slug, // Slug de la página
        'wpcw_recaptcha_settings_section', // ID de la sección a la que pertenece
        array( // Argumentos para la función callback
            'option_name' => 'wpcw_recaptcha_site_key',
            'id'          => 'wpcw_recaptcha_site_key', // Coincide con la opción para el get_option
            'label_for'   => 'wpcw_recaptcha_site_key', // Para que el label haga focus en el input
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
            'id'          => 'wpcw_recaptcha_secret_key', // Coincide con la opción para el get_option
            'label_for'   => 'wpcw_recaptcha_secret_key',
            'description' => __('Introduce tu Google reCAPTCHA v2 Secret Key.', 'wp-cupon-whatsapp')
        )
    );

    // TODO: Registrar otras secciones y campos aquí (Campos de Registro, Utilidades, Exportar)
}
add_action( 'admin_init', 'wpcw_settings_api_init' );

/**
 * Callback para la descripción de la sección de reCAPTCHA.
 */
function wpcw_recaptcha_section_callback() {
    echo '<p>' . esc_html__( 'Configura las claves de API para Google reCAPTCHA v2 para proteger tus formularios.', 'wp-cupon-whatsapp' ) . '</p>';
    // Podrías añadir un enlace a cómo obtener las claves de reCAPTCHA.
}

/**
 * Renderiza un campo de input de texto genérico para la Settings API.
 *
 * @param array $args Argumentos pasados desde add_settings_field.
 *                    Debe incluir 'option_name', 'id'. 'description' es opcional.
 */
function wpcw_render_text_input_field( $args ) {
    $option_name = $args['option_name'];
    // Obtener valor guardado, default vacío si no está seteado.
    // El default de register_setting se usa si la opción NO EXISTE en la BD.
    // Si existe pero está vacía, get_option devolverá vacío.
    $option_value = get_option( $option_name );
    if ( $option_value === false && isset($args['default']) ) { // Si la opción no existe en la BD
        $option_value = $args['default'];
    } elseif ($option_value === false) { // Si no existe y no hay default en args
        $option_value = '';
    }

    $id = isset( $args['id'] ) ? $args['id'] : $option_name;

    echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $option_value ) . '" class="regular-text" />';

    if ( isset( $args['description'] ) && ! empty( $args['description'] ) ) {
        echo '<p class="description">' . wp_kses_post( $args['description'] ) . '</p>'; // wp_kses_post si la descripción puede tener HTML simple
    }
}

// TODO: Callbacks para otros tipos de campos (checkboxes, etc.)
?>
