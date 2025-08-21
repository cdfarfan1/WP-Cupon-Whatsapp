<?php
/**
 * Plugin Name: WP Cupon WhatsApp - Minimal Test Version
 * Plugin URI: https://github.com/cristian-tc/WP-Cupon-Whatsapp
 * Description: Version minimal para testing de activacion - WP Cupon WhatsApp
 * Version: 1.3.1-minimal
 * Author: Cristian TC
 * License: GPL v2 or later
 * Text Domain: wp-cupon-whatsapp
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.3
 * Requires PHP: 7.4
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Logging detallado para debugging
function wpcw_minimal_log($message) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[WPCW-MINIMAL] ' . $message);
    }
}

wpcw_minimal_log('=== INICIO CARGA PLUGIN MINIMAL ===');

// Definir constantes básicas
if (!defined('WPCW_VERSION')) {
    define('WPCW_VERSION', '1.3.1-minimal');
    wpcw_minimal_log('Constante WPCW_VERSION definida: ' . WPCW_VERSION);
}

if (!defined('WPCW_PLUGIN_DIR')) {
    define('WPCW_PLUGIN_DIR', plugin_dir_path(__FILE__));
    wpcw_minimal_log('Constante WPCW_PLUGIN_DIR definida: ' . WPCW_PLUGIN_DIR);
}

if (!defined('WPCW_PLUGIN_URL')) {
    define('WPCW_PLUGIN_URL', plugin_dir_url(__FILE__));
    wpcw_minimal_log('Constante WPCW_PLUGIN_URL definida: ' . WPCW_PLUGIN_URL);
}

if (!defined('WPCW_MIN_PHP_VERSION')) {
    define('WPCW_MIN_PHP_VERSION', '7.4');
    wpcw_minimal_log('Constante WPCW_MIN_PHP_VERSION definida: ' . WPCW_MIN_PHP_VERSION);
}

/**
 * Verificar dependencias críticas
 */
function wpcw_minimal_check_dependencies() {
    wpcw_minimal_log('Verificando dependencias...');
    
    // Verificar versión de PHP
    if (version_compare(PHP_VERSION, WPCW_MIN_PHP_VERSION, '<')) {
        wpcw_minimal_log('ERROR: PHP version insuficiente - ' . PHP_VERSION);
        add_action('admin_notices', 'wpcw_minimal_php_version_notice');
        return false;
    }
    wpcw_minimal_log('PHP version OK: ' . PHP_VERSION);
    
    // Verificar WordPress
    global $wp_version;
    if (version_compare($wp_version, '5.0', '<')) {
        wpcw_minimal_log('ERROR: WordPress version insuficiente - ' . $wp_version);
        add_action('admin_notices', 'wpcw_minimal_wp_version_notice');
        return false;
    }
    wpcw_minimal_log('WordPress version OK: ' . $wp_version);
    
    return true;
}

/**
 * Avisos de versión PHP
 */
function wpcw_minimal_php_version_notice() {
    echo '<div class="notice notice-error"><p>';
    echo '<strong>WP Cupon WhatsApp:</strong> Requiere PHP ' . WPCW_MIN_PHP_VERSION . ' o superior. ';
    echo 'Version actual: ' . PHP_VERSION;
    echo '</p></div>';
}

/**
 * Avisos de versión WordPress
 */
function wpcw_minimal_wp_version_notice() {
    echo '<div class="notice notice-error"><p>';
    echo '<strong>WP Cupon WhatsApp:</strong> Requiere WordPress 5.0 o superior.';
    echo '</p></div>';
}

/**
 * Inicialización básica del plugin
 */
function wpcw_minimal_init() {
    wpcw_minimal_log('Ejecutando wpcw_minimal_init()');
    
    // Verificar dependencias
    if (!wpcw_minimal_check_dependencies()) {
        wpcw_minimal_log('ERROR: Dependencias no cumplidas');
        return;
    }
    
    // Cargar textdomain
    wpcw_minimal_log('Cargando textdomain...');
    load_plugin_textdomain('wp-cupon-whatsapp', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Registrar post type básico
    wpcw_minimal_log('Registrando post types...');
    wpcw_minimal_register_post_types();
    
    wpcw_minimal_log('Inicialización completada exitosamente');
}

/**
 * Registrar post types básicos
 */
function wpcw_minimal_register_post_types() {
    wpcw_minimal_log('Registrando post type: wpcw_cupon');
    
    $args = array(
        'label' => 'Cupones WhatsApp',
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'supports' => array('title', 'editor', 'thumbnail'),
        'menu_icon' => 'dashicons-tickets-alt'
    );
    
    $result = register_post_type('wpcw_cupon', $args);
    
    if (is_wp_error($result)) {
        wpcw_minimal_log('ERROR registrando post type: ' . $result->get_error_message());
    } else {
        wpcw_minimal_log('Post type wpcw_cupon registrado exitosamente');
    }
}

/**
 * Hook de activación
 */
function wpcw_minimal_activate() {
    wpcw_minimal_log('=== HOOK DE ACTIVACIÓN EJECUTADO ===');
    
    // Verificar dependencias en activación
    if (!wpcw_minimal_check_dependencies()) {
        wpcw_minimal_log('ERROR: No se puede activar - dependencias no cumplidas');
        wp_die('WP Cupon WhatsApp no se puede activar. Verifique los requisitos del sistema.');
    }
    
    // Registrar post types para flush_rewrite_rules
    wpcw_minimal_register_post_types();
    
    // Flush rewrite rules
    wpcw_minimal_log('Ejecutando flush_rewrite_rules()');
    flush_rewrite_rules();
    
    wpcw_minimal_log('=== ACTIVACIÓN COMPLETADA EXITOSAMENTE ===');
}

/**
 * Hook de desactivación
 */
function wpcw_minimal_deactivate() {
    wpcw_minimal_log('=== HOOK DE DESACTIVACIÓN EJECUTADO ===');
    flush_rewrite_rules();
    wpcw_minimal_log('=== DESACTIVACIÓN COMPLETADA ===');
}

/**
 * Test de carga de archivos críticos
 */
function wpcw_minimal_test_critical_files() {
    wpcw_minimal_log('Probando carga de archivos críticos...');
    
    $critical_files = array(
        'includes/post-types.php',
        'admin/admin-menu.php'
    );
    
    foreach ($critical_files as $file) {
        $file_path = WPCW_PLUGIN_DIR . $file;
        if (file_exists($file_path)) {
            wpcw_minimal_log("Archivo encontrado: $file");
            
            // Intentar incluir de forma segura
            try {
                include_once $file_path;
                wpcw_minimal_log("Archivo incluido exitosamente: $file");
            } catch (Exception $e) {
                wpcw_minimal_log("ERROR incluyendo $file: " . $e->getMessage());
            } catch (ParseError $e) {
                wpcw_minimal_log("PARSE ERROR en $file: " . $e->getMessage());
            }
        } else {
            wpcw_minimal_log("ARCHIVO FALTANTE: $file");
        }
    }
}

/**
 * Aviso de modo minimal
 */
function wpcw_minimal_admin_notice() {
    echo '<div class="notice notice-info"><p>';
    echo '<strong>WP Cupon WhatsApp - Modo Minimal:</strong> ';
    echo 'Esta es una versión de testing. Funcionalidad limitada para diagnóstico.';
    echo '</p></div>';
}

// Registrar hooks principales
wpcw_minimal_log('Registrando hooks principales...');

// Hook de inicialización
add_action('init', 'wpcw_minimal_init');

// Hooks de activación/desactivación
register_activation_hook(__FILE__, 'wpcw_minimal_activate');
register_deactivation_hook(__FILE__, 'wpcw_minimal_deactivate');

// Aviso de admin
if (is_admin()) {
    add_action('admin_notices', 'wpcw_minimal_admin_notice');
}

// Test de archivos críticos (solo en admin)
if (is_admin() && defined('WP_DEBUG') && WP_DEBUG) {
    add_action('admin_init', 'wpcw_minimal_test_critical_files');
}

wpcw_minimal_log('=== PLUGIN MINIMAL CARGADO COMPLETAMENTE ===');

?>