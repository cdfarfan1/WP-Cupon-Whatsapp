<?php
/**
 * Plugin Name: WP Canje Cupon Whatsapp
 * Plugin URI: https://www.pragmaticsolutions.com.ar
 * Description: Plugin para programa de fidelización y canje de cupones por WhatsApp integrado con WooCommerce.
 * Version: 1.2.0
 * Author: Cristian Farfan, Pragmatic Solutions
 * Author URI: https://www.pragmaticsolutions.com.ar
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wp-cupon-whatsapp
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// DIAGNÓSTICO: Log que el plugin se está cargando
error_log('WPCW: Plugin iniciando carga...');

// Define constants first
define('WPCW_VERSION', '1.2.0');
define('WPCW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPCW_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPCW_TEXT_DOMAIN', 'wp-cupon-whatsapp');
define('WPCW_PLUGIN_FILE', __FILE__);
define('WPCW_MIN_WP_VERSION', '5.0');
define('WPCW_MIN_PHP_VERSION', '7.4');
define('WPCW_MIN_ELEMENTOR_VERSION', '3.0.0');
define('WPCW_MIN_WOOCOMMERCE_VERSION', '6.0.0');

// Table name for canjes (redeems)
global $wpdb;
define('WPCW_CANJES_TABLE_NAME', $wpdb->prefix . 'wpcw_canjes');

/**
 * Initialize plugin functionality
 */
function wpcw_init() {
    // Register post types
    if (function_exists('wpcw_register_post_types')) {
        wpcw_register_post_types();
    }
    
    // Register taxonomies
    if (function_exists('wpcw_register_taxonomies')) {
        wpcw_register_taxonomies();
    }
    
    // Initialize roles
    if (function_exists('wpcw_add_roles')) {
        wpcw_add_roles();
    }
}

// Hook into WordPress init action for main functionality
add_action('init', 'wpcw_init');

/**
 * Load plugin textdomain for translations
 */
function wpcw_load_textdomain() {
    load_plugin_textdomain('wp-cupon-whatsapp', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'wpcw_load_textdomain');

/**
 * Function to check dependencies
 */
function wpcw_check_dependencies() {
    $dependency_errors = array();

    // Verificar PHP version
    if (version_compare(PHP_VERSION, WPCW_MIN_PHP_VERSION, '<')) {
        $dependency_errors[] = sprintf(
            'PHP %s o superior',
            WPCW_MIN_PHP_VERSION
        );
    }

    // Verificar WooCommerce
    if (!class_exists('WooCommerce')) {
        $dependency_errors[] = 'WooCommerce instalado y activado';
    } elseif (version_compare(WC_VERSION, WPCW_MIN_WOOCOMMERCE_VERSION, '<')) {
        $dependency_errors[] = sprintf(
            'WooCommerce %s o superior',
            WPCW_MIN_WOOCOMMERCE_VERSION
        );
    }

    // Verificar Elementor
    if (!did_action('elementor/loaded')) {
        $dependency_errors[] = 'Elementor instalado y activado';
    } elseif (defined('ELEMENTOR_VERSION') && version_compare(ELEMENTOR_VERSION, WPCW_MIN_ELEMENTOR_VERSION, '<')) {
        $dependency_errors[] = sprintf(
            'Elementor %s o superior',
            WPCW_MIN_ELEMENTOR_VERSION
        );
    }

    if (!empty($dependency_errors)) {
        add_action('admin_notices', function() use ($dependency_errors) {
            ?>
            <div class="error">
                <p>
                    <strong><?php echo esc_html('WP Canje Cupón WhatsApp requiere:'); ?></strong>
                    <ul style="list-style-type: disc; margin-left: 20px;">
                        <?php foreach ($dependency_errors as $error) : ?>
                            <li><?php echo esc_html($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </p>
            </div>
            <?php
        });
        return false;
    }

    return true;
}

// Load core files
$core_files = [
    'includes/class-wpcw-logger.php',
    'includes/class-wpcw-messages.php',
    'includes/class-wpcw-installer.php',
    'includes/post-types.php',
    'includes/taxonomies.php',
    'includes/roles.php',
    'includes/approval-handler.php',
    'includes/redemption-handler.php',
    'includes/redemption-form-handler.php'
];

foreach ($core_files as $file) {
    $file_path = WPCW_PLUGIN_DIR . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    }
}

// Plugin activation
register_activation_hook(__FILE__, function() {
    if (!wpcw_check_dependencies()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('Por favor, verifica los requisitos del plugin antes de activarlo.');
    }
    
    if (class_exists('WPCW_Installer')) {
        // Inicializar configuraciones
        WPCW_Installer::init_settings();
        
        // Crear tabla de canjes
        WPCW_Installer::create_canjes_table();
        
        // Registrar roles iniciales
        if (function_exists('wpcw_add_roles')) {
            wpcw_add_roles();
        }
    }
});

// Plugin deactivation
register_deactivation_hook(__FILE__, function() {
    if (function_exists('wpcw_remove_roles')) {
        wpcw_remove_roles();
    }
    flush_rewrite_rules();
});

// Verificar dependencias
if (!wpcw_check_dependencies()) {
    return;
}

// Verificar versión mínima de WordPress
if (version_compare(get_bloginfo('version'), WPCW_MIN_WP_VERSION, '<')) {
    add_action('admin_notices', function() {
        ?>
        <div class="error">
            <p><?php echo sprintf('WP Canje Cupón WhatsApp requiere WordPress %s o superior.', WPCW_MIN_WP_VERSION); ?></p>
        </div>
        <?php
    });
    return;
}

// Cargar estilos y scripts del admin
function wpcw_enqueue_admin_scripts($hook) {
    // Solo cargar en páginas de nuestro plugin
    if (strpos($hook, 'wpcw') === false) {
        return;
    }

    // Estilos del admin
    wp_enqueue_style('wpcw-admin-styles', WPCW_PLUGIN_URL . 'admin/css/admin.css', array(), WPCW_VERSION);

    // Estilos específicos de canjes
    if ($hook === 'wpcw-main-menu_page_wpcw-canjes') {
        wp_enqueue_style('wpcw-canjes-styles', WPCW_PLUGIN_URL . 'admin/css/canjes.css', array(), WPCW_VERSION);
    }
}
add_action('admin_enqueue_scripts', 'wpcw_enqueue_admin_scripts');

// Las constantes ya están definidas arriba

/**
 * The code that runs during plugin activation.
 */
// Acción para limpiar logs antiguos
add_action('wpcw_clean_old_logs', array('WPCW_Logger', 'limpiar_logs_antiguos'));

function wpcw_activate_plugin() {
    // Verificar requisitos mínimos
    if (version_compare(PHP_VERSION, WPCW_MIN_PHP_VERSION, '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(sprintf(
            'WP Canje Cupón WhatsApp requiere PHP %s o superior.',
            WPCW_MIN_PHP_VERSION
        ));
    }
    
    // Verificar WordPress
    if (version_compare(get_bloginfo('version'), WPCW_MIN_WP_VERSION, '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(sprintf(
            'WP Canje Cupón WhatsApp requiere WordPress %s o superior.',
            WPCW_MIN_WP_VERSION
        ));
    }
    
    // Verificar WooCommerce
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('WP Canje Cupón WhatsApp requiere que WooCommerce esté instalado y activado.');
    }
    
    // Verificar versión de WooCommerce
    if (defined('WC_VERSION') && version_compare(WC_VERSION, WPCW_MIN_WOOCOMMERCE_VERSION, '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(sprintf(
            'WP Canje Cupón WhatsApp requiere WooCommerce %s o superior.',
            WPCW_MIN_WOOCOMMERCE_VERSION
        ));
    }

    if (version_compare(get_bloginfo('version'), WPCW_MIN_WP_VERSION, '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(sprintf(
            'WP Canje Cupón WhatsApp requiere WordPress %s o superior.',
            WPCW_MIN_WP_VERSION
        ));
    }

    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('WP Canje Cupón WhatsApp requiere que WooCommerce esté instalado y activado.');
    }

    // Include required files
        // Load required files
    require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-installer.php';
    require_once WPCW_PLUGIN_DIR . 'includes/class-wpcw-mongodb.php';
    require_once WPCW_PLUGIN_DIR . 'includes/post-types.php';
    require_once WPCW_PLUGIN_DIR . 'includes/taxonomies.php';
    require_once WPCW_PLUGIN_DIR . 'includes/roles.php';
    require_once WPCW_PLUGIN_DIR . 'includes/email-verification.php';
    require_once WPCW_PLUGIN_DIR . 'includes/redemption-handler.php';

    // Create canjes table.
    $table_created = WPCW_Installer::create_canjes_table();
    if (!$table_created) {
        add_action('admin_notices', function() {
            ?>
            <div class="error">
                <p><?php echo esc_html('Error: No se pudo crear la tabla de canjes. Por favor, revise los permisos de la base de datos.'); ?></p>
            </div>
            <?php
        });
    }

    // Register CPTs.
    wpcw_register_post_types();
    // Register Taxonomies.
    wpcw_register_taxonomies();
    // Add User Roles.
    wpcw_add_roles();

    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * The code that runs during plugin deactivation.
 */
function wpcw_deactivate_plugin() {
    // Include roles management.
    require_once WPCW_PLUGIN_DIR . 'includes/roles.php';
    
    // Remove User Roles
    wpcw_remove_roles();
    
    // Limpiar las reglas de reescritura
    flush_rewrite_rules();
    
    // Eliminar opciones del plugin si es necesario
    // delete_option('wpcw_settings');
    
    // No eliminamos la tabla de canjes por defecto para preservar los datos
    // Si se desea eliminar la tabla, descomentar la siguiente línea:
    // global $wpdb;
    // $wpdb->query("DROP TABLE IF EXISTS " . WPCW_CANJES_TABLE_NAME);
}

register_activation_hook( __FILE__, 'wpcw_activate_plugin' );
register_deactivation_hook( __FILE__, 'wpcw_deactivate_plugin' );

/**
 * Verifica la instalación y versión de WooCommerce
 */
function wpcw_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            ?>
            <div class="error">
                <p><?php echo esc_html('WP Cupón WhatsApp requiere que WooCommerce esté instalado y activado.'); ?></p>
            </div>
            <?php
        });
        return false;
    }

    if (version_compare(WC_VERSION, WPCW_MIN_WOOCOMMERCE_VERSION, '<')) {
        add_action('admin_notices', function() {
            ?>
            <div class="error">
                <p><?php echo sprintf('WP Cupón WhatsApp requiere WooCommerce %s o superior.', WPCW_MIN_WOOCOMMERCE_VERSION); ?></p>
            </div>
            <?php
        });
        return false;
    }

    return true;
}

// Verificar WooCommerce antes de cargar el plugin
if (!wpcw_check_woocommerce()) {
    return;
}

// Core functionality files - orden basado en dependencias
$core_files = array(
    'includes/class-wpcw-logger.php',        // Sistema de logging (sin dependencias)
    'includes/class-wpcw-messages.php',      // Sistema de mensajes (sin dependencias)
    'includes/estados-canje.php',            // Estados de canje (sin dependencias)
    'includes/customer-fields.php',          // Campos de cliente (depende de messages)
    'includes/recaptcha-integration.php',    // Integración reCAPTCHA (depende de messages)
    'includes/application-processing.php',    // Procesamiento de solicitudes (depende de messages, logger)
    'includes/ajax-handlers.php',            // Manejadores AJAX (depende de messages, logger)
    'includes/rest-api.php',                 // API REST (depende de messages, logger)
    'includes/redemption-logic.php',         // Lógica de canje (depende de estados-canje, messages, logger)
    'includes/stats-functions.php',          // Funciones de estadísticas (depende de logger)
    'includes/export-functions.php',         // Funciones de exportación (depende de stats-functions)
);

// Cargar archivos core y verificar existencia
foreach ($core_files as $file) {
    $file_path = WPCW_PLUGIN_DIR . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    } else {
        // Registrar error y mostrar notificación
        if (class_exists('WPCW_Logger')) {
            WPCW_Logger::log('error', sprintf('Archivo core no encontrado: %s', $file));
        }
        add_action('admin_notices', function() use ($file) {
            ?>
            <div class="error">
                <p><?php echo sprintf('WP Canje Cupón WhatsApp: Archivo core no encontrado: %s', $file); ?></p>
            </div>
            <?php
        });
    }
}

// Public functionality files
$public_files = array(
    'public/shortcodes.php',
    'public/my-account-endpoints.php'
);

// Cargar archivos públicos
foreach ($public_files as $file) {
    $file_path = WPCW_PLUGIN_DIR . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    } else {
        if (class_exists('WPCW_Logger')) {
            WPCW_Logger::log('error', sprintf('Archivo público no encontrado: %s', $file));
        }
    }
}
require_once WPCW_PLUGIN_DIR . 'includes/customer-fields.php';
// Include reCAPTCHA integration
require_once WPCW_PLUGIN_DIR . 'includes/recaptcha-integration.php';
// Include application processing
require_once WPCW_PLUGIN_DIR . 'includes/application-processing.php';
// Include AJAX handlers
require_once WPCW_PLUGIN_DIR . 'includes/ajax-handlers.php';
// Include REST API endpoints
require_once WPCW_PLUGIN_DIR . 'includes/rest-api.php';
// Include redemption logic
require_once WPCW_PLUGIN_DIR . 'includes/redemption-logic.php';
// Include statistics functions
require_once WPCW_PLUGIN_DIR . 'includes/stats-functions.php';
// Include export functions
require_once WPCW_PLUGIN_DIR . 'includes/export-functions.php';
// Include public shortcodes
require_once WPCW_PLUGIN_DIR . 'public/shortcodes.php';
// Include My Account endpoint functions
require_once WPCW_PLUGIN_DIR . 'public/my-account-endpoints.php';

// Elementor Addon
if (did_action('elementor/loaded')) {
    // Verificar versión mínima de Elementor
    if (version_compare(ELEMENTOR_VERSION, WPCW_MIN_ELEMENTOR_VERSION, '>=')) {
        require_once WPCW_PLUGIN_DIR . 'elementor/elementor-addon.php';
    } else {
        add_action('admin_notices', function() {
            ?>
            <div class="error">
                <p><?php echo sprintf('WP Canje Cupón WhatsApp requiere Elementor %s o superior para sus widgets.', WPCW_MIN_ELEMENTOR_VERSION); ?></p>
            </div>
            <?php
        });
    }
}


// Admin specific includes
if ( is_admin() ) {
    error_log('WPCW: Entrando en sección admin, cargando archivos...');
    require_once WPCW_PLUGIN_DIR . 'admin/admin-menu.php';
    require_once WPCW_PLUGIN_DIR . 'admin/settings-page.php';
    require_once WPCW_PLUGIN_DIR . 'admin/stats-page.php';
    require_once WPCW_PLUGIN_DIR . 'admin/business-stats-page.php';
    require_once WPCW_PLUGIN_DIR . 'admin/institution-stats-page.php';
    require_once WPCW_PLUGIN_DIR . 'admin/canjes-page.php';
    require_once WPCW_PLUGIN_DIR . 'admin/meta-boxes.php';
    require_once WPCW_PLUGIN_DIR . 'admin/coupon-meta-boxes.php';
    error_log('WPCW: Archivos admin cargados correctamente');
    
    // DIAGNÓSTICO TEMPORAL - incluir archivo de diagnóstico
    require_once WPCW_PLUGIN_DIR . 'diagnostico-temp.php';
    error_log('WPCW: Archivo de diagnóstico cargado');
    
    // Register admin menu DIRECTAMENTE AQUÍ - método alternativo
    error_log('WPCW: Registrando hook admin_menu DIRECTO...');
    add_action('admin_menu', function() {
        error_log('WPCW: Hook admin_menu DIRECTO ejecutándose');
        
        if (!current_user_can('manage_options')) {
            error_log('WPCW: Usuario sin permisos en hook directo');
            return;
        }
        
        // Menú directo simple
        $result = add_menu_page(
            'WPCW Gestión',
            'WPCW GESTIÓN',
            'manage_options',
            'wpcw-directo',
            function() {
                echo '<div class="wrap"><h1>MENÚ DIRECTO FUNCIONANDO!</h1><p>Este menú se registra directamente desde wp-cupon-whatsapp.php</p></div>';
            },
            'dashicons-admin-tools',
            25
        );
        
        error_log('WPCW: Resultado menú directo: ' . ($result ? 'ÉXITO' : 'FALLÓ'));
    });
    
    // Register admin menu ORIGINAL
    error_log('WPCW: Registrando hook admin_menu original...');
    add_action('admin_menu', 'wpcw_register_plugin_admin_menu');
    error_log('WPCW: Hook admin_menu registrado');
}

/**
 * Enqueue public-facing scripts and styles.
 */
function wpcw_public_enqueue_scripts_styles() {
    if (is_admin()) {
        return;
    }

    // Verificar si estamos en una página relevante
    global $post;
    $should_load = false;
    $should_load_scripts = false;

    // Verificar si es una página que necesita nuestros recursos
    if ($post instanceof WP_Post) {
        $should_load = (
            // Verificar shortcodes
            has_shortcode($post->post_content, 'wpcw_mis_cupones') ||
            has_shortcode($post->post_content, 'wpcw_cupones_publicos') ||
            has_shortcode($post->post_content, 'wpcw_solicitud_adhesion_form') ||
            // Verificar páginas de Elementor
            (did_action('elementor/loaded') && \Elementor\Plugin::$instance->db->is_built_with_elementor($post->ID)) ||
            // Verificar si es una página de Mi Cuenta de WooCommerce
            is_account_page() ||
            // Verificar si es una página configurada en las opciones del plugin
            $post->ID == get_option('wpcw_page_id_mis_cupones') ||
            $post->ID == get_option('wpcw_page_id_cupones_publicos') ||
            $post->ID == get_option('wpcw_page_id_solicitud_adhesion')
        );

        // Verificar si necesitamos cargar scripts
        $should_load_scripts = (
            has_shortcode($post->post_content, 'wpcw_mis_cupones') ||
            has_shortcode($post->post_content, 'wpcw_cupones_publicos') ||
            has_shortcode($post->post_content, 'wpcw_solicitud_adhesion_form') ||
            // Verificar páginas de Elementor que usan nuestros widgets
            (did_action('elementor/loaded') && \Elementor\Plugin::$instance->db->is_built_with_elementor($post->ID))
        );
    }

    // Si es una página relevante, cargar los recursos necesarios
    if ($should_load) {
        // Encolar estilos
        wp_enqueue_style(
            'wpcw-public-style',
            WPCW_PLUGIN_URL . 'public/css/public.css',
            array(),
            WPCW_VERSION
        );

        // Si se necesitan scripts, cargarlos
        if ($should_load_scripts) {
            wp_enqueue_script(
                'wpcw-canje-handler',
                WPCW_PLUGIN_URL . 'public/js/canje-handler.js',
                array('jquery'), // Dependencia de jQuery
                WPCW_VERSION,    // Versión del plugin
                true             // Cargar en el footer
            );

            // Localizar el script para pasar datos de PHP a JS
            wp_localize_script(
                'wpcw-canje-handler', // Handle del script al que se le pasan los datos
                'wpcw_canje_obj',     // Nombre del objeto JavaScript que contendrá los datos
                array(
                    'ajax_url' => admin_url('admin-ajax.php'), // URL para peticiones AJAX
                    'nonce'    => wp_create_nonce('wpcw_request_canje_action_nonce'), // Nonce para la acción específica
                    // Se pueden añadir más datos aquí si son necesarios, como mensajes traducibles.
                    'text_processing' => 'Procesando...',
                    'text_error_generic' => 'Ocurrió un error. Por favor, inténtalo de nuevo.',
                )
            );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'wpcw_public_enqueue_scripts_styles' );
