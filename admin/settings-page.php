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
function wpcw_mongodb_section_callback() {
    echo '<p>' . esc_html__('Configura la integración con MongoDB para sincronización y respaldo de datos.', 'wp-cupon-whatsapp') . '</p>';
    
    // Mostrar estado de la conexión si MongoDB está habilitado
    if (get_option('wpcw_mongodb_enabled', false)) {
        $mongo = WPCW_MongoDB::get_instance();
        if ($mongo->test_connection()) {
            echo '<div class="notice notice-success inline"><p>' . 
                 esc_html__('Conexión a MongoDB establecida correctamente.', 'wp-cupon-whatsapp') . 
                 '</p></div>';
            
            // Mostrar última sincronización
            $last_sync = get_option('wpcw_last_mongo_sync', '');
            if ($last_sync) {
                echo '<p>' . sprintf(
                    esc_html__('Última sincronización: %s', 'wp-cupon-whatsapp'),
                    date_i18n(get_option('date_format', 'Y-m-d') . ' ' . get_option('time_format', 'H:i:s'), strtotime($last_sync))
                ) . '</p>';
            }
        } else {
            echo '<div class="notice notice-error inline"><p>' . 
                 esc_html__('Error al conectar con MongoDB. Verifica la configuración.', 'wp-cupon-whatsapp') . 
                 '</p></div>';
        }
    }
}

/**
 * Callback para la descripción de la sección de reCAPTCHA.
 */
function wpcw_recaptcha_section_callback() {
    echo '<p>' . esc_html__('Configura las claves de Google reCAPTCHA v2 para proteger los formularios.', 'wp-cupon-whatsapp') . '</p>';
}

function wpcw_approval_section_callback() {
    echo '<p>' . esc_html__('Configura cómo se manejan los nuevos registros de comercios e instituciones.', 'wp-cupon-whatsapp') . '</p>';
}

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

    // Email Verification Settings
    register_setting(
        $option_group,
        'wpcw_email_verification_enabled',
        array(
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => true
        )
    );

    register_setting(
        $option_group,
        'wpcw_email_verification_expiry',
        array(
            'type' => 'number',
            'sanitize_callback' => 'absint',
            'default' => 24 // horas
        )
    );

    // MongoDB Settings
    register_setting(
        $option_group,
        'wpcw_mongodb_enabled',
        array(
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => false
        )
    );

    // Configuración de Aprobación de Comercios/Instituciones
    register_setting(
        $option_group,
        'wpcw_auto_approve_businesses',
        array(
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => false
        )
    );

    register_setting(
        $option_group,
        'wpcw_mongodb_uri',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    register_setting(
        $option_group,
        'wpcw_mongodb_database',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        )
    );

    register_setting(
        $option_group,
        'wpcw_mongodb_auto_sync',
        array(
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => false
        )
    );

    // reCAPTCHA Settings
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

    // Sección de Aprobación de Comercios/Instituciones
    add_settings_section(
        'wpcw_approval_section',
        __('Aprobación de Comercios e Instituciones', 'wp-cupon-whatsapp'),
        'wpcw_approval_section_callback',
        $page_slug
    );

    add_settings_field(
        'wpcw_auto_approve_businesses',
        __('Aprobación Automática', 'wp-cupon-whatsapp'),
        'wpcw_render_settings_checkbox_field',
        $page_slug,
        'wpcw_approval_section',
        array(
            'option_name' => 'wpcw_auto_approve_businesses',
            'label' => __('Aprobar automáticamente los nuevos registros de comercios e instituciones', 'wp-cupon-whatsapp'),
            'description' => __('Si está desactivado, los registros requerirán aprobación manual por un administrador.', 'wp-cupon-whatsapp')
        )
    );

    // Email Verification Section
    add_settings_section(
        'wpcw_email_verification_section',
        __('Verificación por Email', 'wp-cupon-whatsapp'),
        'wpcw_email_verification_section_callback',
        $page_slug
    );

    add_settings_field(
        'wpcw_email_verification_enabled',
        __('Habilitar Verificación', 'wp-cupon-whatsapp'),
        'wpcw_render_settings_checkbox_field',
        $page_slug,
        'wpcw_email_verification_section',
        array(
            'option_name' => 'wpcw_email_verification_enabled',
            'field_key' => 'enabled',
            'label' => __('Requerir verificación de email para nuevos usuarios', 'wp-cupon-whatsapp'),
            'description' => __('Los usuarios deberán verificar su email antes de poder canjear cupones o acceder a funciones del plugin.', 'wp-cupon-whatsapp')
        )
    );

    add_settings_field(
        'wpcw_email_verification_expiry',
        __('Tiempo de Expiración', 'wp-cupon-whatsapp'),
        'wpcw_render_number_input_field',
        $page_slug,
        'wpcw_email_verification_section',
        array(
            'option_name' => 'wpcw_email_verification_expiry',
            'label_for' => 'wpcw_email_verification_expiry',
            'description' => __('Horas de validez del enlace de verificación', 'wp-cupon-whatsapp'),
            'min' => 1,
            'max' => 72,
            'step' => 1
        )
    );

    add_settings_section(
        'wpcw_mongodb_section',
        __('Configuración de MongoDB', 'wp-cupon-whatsapp'),
        'wpcw_mongodb_section_callback',
        $page_slug
    );

    add_settings_field(
        'wpcw_mongodb_enabled',
        __('Habilitar MongoDB', 'wp-cupon-whatsapp'),
        'wpcw_render_settings_checkbox_field',
        $page_slug,
        'wpcw_mongodb_section',
        array(
            'label_for' => 'wpcw_mongodb_enabled',
            'description' => __('Activar la integración con MongoDB', 'wp-cupon-whatsapp')
        )
    );

    add_settings_field(
        'wpcw_mongodb_uri',
        __('URI de MongoDB', 'wp-cupon-whatsapp'),
        'wpcw_render_text_input_field',
        $page_slug,
        'wpcw_mongodb_section',
        array(
            'label_for' => 'wpcw_mongodb_uri',
            'description' => __('URI de conexión a MongoDB (ej: mongodb://localhost:27017)', 'wp-cupon-whatsapp')
        )
    );

    add_settings_field(
        'wpcw_mongodb_database',
        __('Base de datos MongoDB', 'wp-cupon-whatsapp'),
        'wpcw_render_text_input_field',
        $page_slug,
        'wpcw_mongodb_section',
        array(
            'label_for' => 'wpcw_mongodb_database',
            'description' => __('Nombre de la base de datos en MongoDB', 'wp-cupon-whatsapp')
        )
    );

    add_settings_field(
        'wpcw_mongodb_auto_sync',
        __('Sincronización automática', 'wp-cupon-whatsapp'),
        'wpcw_render_settings_checkbox_field',
        $page_slug,
        'wpcw_mongodb_section',
        array(
            'label_for' => 'wpcw_mongodb_auto_sync',
            'description' => __('Sincronizar automáticamente los cambios con MongoDB', 'wp-cupon-whatsapp')
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

    // Añadir Sección para Exportar Datos
    add_settings_section(
        'wpcw_export_data_section', // ID de la sección
        __( 'Exportar Datos a CSV', 'wp-cupon-whatsapp' ), // Título
        'wpcw_export_data_section_callback', // Callback para descripción
        $page_slug
    );

    $export_buttons = array(
        'comercios' => array(
            'label' => __('Exportar Comercios', 'wp-cupon-whatsapp'),
            'action_name' => 'wpcw_export_comercios_action', // Este será el 'name' del botón submit
            'nonce_action' => 'wpcw_export_comercios_nonce', // Para wp_nonce_field y check_admin_referer
            'description' => __('Descarga un archivo CSV con todos los datos de los comercios registrados.', 'wp-cupon-whatsapp')
        ),
        'instituciones' => array(
            'label' => __('Exportar Instituciones', 'wp-cupon-whatsapp'),
            'action_name' => 'wpcw_export_instituciones_action',
            'nonce_action' => 'wpcw_export_instituciones_nonce',
            'description' => __('Descarga un archivo CSV con todos los datos de las instituciones registradas.', 'wp-cupon-whatsapp')
        ),
        'clientes' => array(
            'label' => __('Exportar Clientes (WPCW)', 'wp-cupon-whatsapp'),
            'action_name' => 'wpcw_export_clientes_action',
            'nonce_action' => 'wpcw_export_clientes_nonce',
            'description' => __('Descarga un archivo CSV con los datos de los clientes (rol "customer") y sus campos WPCW asociados.', 'wp-cupon-whatsapp')
        ),
        'cupones' => array(
            'label' => __('Exportar Cupones (WPCW)', 'wp-cupon-whatsapp'),
            'action_name' => 'wpcw_export_cupones_action',
            'nonce_action' => 'wpcw_export_cupones_nonce',
            'description' => __('Descarga un archivo CSV con los cupones de WooCommerce y sus campos WPCW asociados.', 'wp-cupon-whatsapp')
        ),
        'canjes' => array(
            'label' => __('Exportar Historial de Canjes', 'wp-cupon-whatsapp'),
            'action_name' => 'wpcw_export_canjes_action',
            'nonce_action' => 'wpcw_export_canjes_nonce',
            'description' => __('Descarga un archivo CSV con todos los registros de la tabla de canjes.', 'wp-cupon-whatsapp')
        ),
    );

    foreach ( $export_buttons as $key => $button_args ) {
        add_settings_field(
            'wpcw_field_export_' . $key, // ID del campo
            $button_args['label'], // Título del campo (label del botón)
            'wpcw_render_export_button_field', // Callback para renderizar el botón y su form
            $page_slug,
            'wpcw_export_data_section', // ID de la sección
            $button_args // Pasar todos los args al callback
        );
    }
}
add_action( 'admin_init', 'wpcw_settings_api_init' );

/**
 * Callback para la descripción de la sección de Exportar Datos.
 */
function wpcw_export_data_section_callback() {
    echo '<p>' . esc_html__( 'Herramientas para descargar datos del plugin en formato CSV.', 'wp-cupon-whatsapp' ) . '</p>';
}

/**
 * Renderiza un formulario con un botón para una acción de exportación específica.
 *
 * @param array $args Argumentos pasados desde add_settings_field.
 *                    Debe incluir 'action_name', 'label' (para el botón), 'nonce_action'.
 *                    'description' es opcional para mostrar junto al botón.
 */
function wpcw_render_export_button_field( $args ) {
    $action_name = isset($args['action_name']) ? $args['action_name'] : 'wpcw_export_default_action';
    $button_label = isset($args['label']) ? $args['label'] : __('Exportar Datos', 'wp-cupon-whatsapp');
    // El nonce_action es el 'action' para wp_nonce_field, y el 'name' del campo nonce será $args['nonce_action'] . '_field'
    $nonce_action_string = isset($args['nonce_action']) ? $args['nonce_action'] : 'wpcw_export_default_nonce';
    $nonce_field_name = $nonce_action_string . '_nonce_name'; // Nombre único para el campo nonce en POST
    $description = isset($args['description']) ? $args['description'] : '';

    // Nonce para la acción específica de exportación.
    wp_nonce_field( $nonce_action_string, $nonce_field_name );

    submit_button(
        $button_label,
        'secondary', // tipo
        $action_name, // name del botón submit
        false,        // wrap: false para no envolver en <p> automáticamente, lo controlamos nosotros.
        null         // otros atributos
    );

    if ( !empty($description) ) {
        echo '<p class="description" style="margin-top:0;">' . esc_html( $description ) . '</p>';
    }
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
    // Manejar Acción de Crear Páginas
    if ( isset( $_POST['wpcw_create_pages_action'] ) ) {
        check_admin_referer( 'wpcw_create_pages_action_nonce', 'wpcw_nonce_create_pages' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'No tienes permisos para realizar esta acción.', 'wp-cupon-whatsapp' ) );
        }

        $results = WPCW_Installer::create_pages();

        // Mostrar mensajes según los resultados
        if (!empty($results['created'])) {
            $message = sprintf(
                _n(
                    'Se ha creado la página: %s',
                    'Se han creado las siguientes páginas: %s',
                    count($results['created']),
                    'wp-cupon-whatsapp'
                ),
                implode(', ', $results['created'])
            );
            add_settings_error(
                'wpcw_options_group',
                'pages_created',
                $message,
                'success'
            );
        }

        if (!empty($results['existing'])) {
            $message = sprintf(
                _n(
                    'La siguiente página ya existe: %s',
                    'Las siguientes páginas ya existen: %s',
                    count($results['existing']),
                    'wp-cupon-whatsapp'
                ),
                implode(', ', $results['existing'])
            );
            add_settings_error(
                'wpcw_options_group',
                'pages_exist',
                $message,
                'info'
            );
        }

        if (!empty($results['failed'])) {
            foreach ($results['failed'] as $failed) {
                $message = sprintf(
                    __('Error al crear la página "%s": %s', 'wp-cupon-whatsapp'),
                    $failed['title'],
                    $failed['error']
                );
                add_settings_error(
                    'wpcw_options_group',
                    'page_creation_error',
                    $message,
                    'error'
                );
            }
        }

        // Redirigir para evitar reenvío del formulario
        wp_redirect(add_query_arg('settings-updated', 'true', wp_get_referer()));
        exit;
    }

    // Definir las acciones de exportación y sus detalles
    $export_actions = array(
        'wpcw_export_comercios_action' => array(
            'nonce_field_name' => 'wpcw_export_comercios_nonce_nonce_name', // Concatenado como en el render
            'nonce_action'     => 'wpcw_export_comercios_nonce',     // Acción del nonce usado en wp_nonce_field
            'export_type'      => 'comercios'
        ),
        'wpcw_export_instituciones_action' => array(
            'nonce_field_name' => 'wpcw_export_instituciones_nonce_nonce_name',
            'nonce_action'     => 'wpcw_export_instituciones_nonce',
            'export_type'      => 'instituciones'
        ),
        'wpcw_export_clientes_action' => array(
            'nonce_field_name' => 'wpcw_export_clientes_nonce_nonce_name',
            'nonce_action'     => 'wpcw_export_clientes_nonce',
            'export_type'      => 'clientes'
        ),
        'wpcw_export_cupones_action' => array(
            'nonce_field_name' => 'wpcw_export_cupones_nonce_nonce_name',
            'nonce_action'     => 'wpcw_export_cupones_nonce',
            'export_type'      => 'cupones'
        ),
        'wpcw_export_canjes_action' => array(
            'nonce_field_name' => 'wpcw_export_canjes_nonce_nonce_name',
            'nonce_action'     => 'wpcw_export_canjes_nonce',
            'export_type'      => 'canjes'
        ),
    );

    // Iterar sobre las acciones de exportación definidas
    foreach ( $export_actions as $action_name => $config ) {
        if ( isset( $_POST[$action_name] ) ) {
            // Verificar Nonce específico para esta acción de exportación
            // El segundo argumento de check_admin_referer es el 'name' del campo nonce que se envió.
            // En wpcw_render_export_button_field, el nombre del campo nonce es $args['nonce_action'] . '_nonce_name'.
            check_admin_referer( $config['nonce_action'], $config['nonce_action'] . '_nonce_name' );

            // Verificar Permisos
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( esc_html__( 'No tienes permisos para realizar esta acción de exportación.', 'wp-cupon-whatsapp' ) );
            }

            // Llamar a la función que genera el CSV y fuerza la descarga
            // Esta función (a implementar en includes/export-functions.php) debe manejar el exit()
            if ( function_exists('wpcw_generate_and_download_csv') ) {
                wpcw_generate_and_download_csv( $config['export_type'] );
                // La función de exportación debe llamar a exit() después de enviar el archivo.
                // Si no lo hace, podemos añadir exit aquí para estar seguros.
                exit;
            } else {
                // Mostrar un error de WordPress si la función no está disponible
                add_settings_error(
                    'wpcw_options_group',
                    'export_function_missing',
                    sprintf( __('Error: La función de exportación para "%s" no está disponible.', 'wp-cupon-whatsapp'), $config['export_type'] ),
                    'error'
                );
                // No hacer exit aquí para que el error se muestre en la página de ajustes
            }
        }
    }

    // TODO: Manejar otras acciones de admin aquí si es necesario en el futuro.
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
            $page_id = get_option( $page_config['option_name'], 0 );
            $page_exists_valid = false;

            if ( $page_id ) {
                $existing_post = get_post( $page_id );
                if ( $existing_post && 'page' === $existing_post->post_type && 'trash' !== $existing_post->post_status ) {
                    if ( has_shortcode( $existing_post->post_content, str_replace(array('[', ']'), '', (string) $page_config['shortcode']) ) ) {
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
