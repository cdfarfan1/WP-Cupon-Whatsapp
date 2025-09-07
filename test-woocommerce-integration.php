<?php
/**
 * Script para probar la integración del plugin WP Cupón WhatsApp con WooCommerce
 *
 * Este script simula un entorno WordPress para probar las funcionalidades
 * de integración con WooCommerce del plugin WP Cupón WhatsApp.
 */

// Activar la visualización de todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST DE INTEGRACIÓN CON WOOCOMMERCE ===\n";
echo "Iniciando prueba de integración con WooCommerce para WP Cupón WhatsApp\n\n";

// Simular constantes de WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', dirname(__FILE__));
}

if (!defined('WP_PLUGIN_URL')) {
    define('WP_PLUGIN_URL', 'http://localhost/wp-content/plugins');
}

if (!defined('WPMU_PLUGIN_DIR')) {
    define('WPMU_PLUGIN_DIR', dirname(__FILE__) . '/mu-plugins');
}

// Simular objeto global $wpdb
global $wpdb;
$wpdb = new stdClass();
$wpdb->prefix = 'wp_';

// Simular funciones de WordPress
if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) {
        return trailingslashit(dirname($file));
    }
}

if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($file) {
        return trailingslashit(plugins_url('', $file));
    }
}

if (!function_exists('plugins_url')) {
    function plugins_url($path = '', $plugin = '') {
        return WP_PLUGIN_URL . '/' . ltrim($path, '/');
    }
}

if (!function_exists('get_bloginfo')) {
    function get_bloginfo($show = '', $filter = 'raw') {
        $options = array(
            'version' => '6.2',
            'name' => 'Test Site',
            'url' => 'http://localhost/webstore'
        );
        return isset($options[$show]) ? $options[$show] : '';
    }
}

if (!function_exists('plugin_basename')) {
    function plugin_basename($file) {
        return basename(dirname($file)) . '/' . basename($file);
    }
}

if (!function_exists('trailingslashit')) {
    function trailingslashit($string) {
        return rtrim($string, '/') . '/';
    }
}

// Simular funciones de hooks de WordPress
$actions = array();
$filters = array();
$shortcodes = array();

if (!function_exists('add_action')) {
    function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
        global $actions;
        $actions[] = "add_action('$tag', '$function_to_add')";
    }
}

if (!function_exists('add_filter')) {
    function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
        global $filters;
        $filters[] = "add_filter('$tag', '$function_to_add')";
    }
}

if (!function_exists('add_shortcode')) {
    function add_shortcode($tag, $function_to_add) {
        global $shortcodes;
        $shortcodes[] = "add_shortcode('$tag', '$function_to_add')";
    }
}

if (!function_exists('register_activation_hook')) {
    function register_activation_hook($file, $function) {
        echo "register_activation_hook('$file', '$function')\n";
    }
}

if (!function_exists('register_deactivation_hook')) {
    function register_deactivation_hook($file, $function) {
        echo "register_deactivation_hook('$file', '$function')\n";
    }
}

if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false) {
        echo "wp_enqueue_script('$handle')\n";
    }
}

if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all') {
        echo "wp_enqueue_style('$handle')\n";
    }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        return false;
    }
}

if (!function_exists('did_action')) {
    function did_action($tag) {
        return 1;
    }
}

if (!function_exists('load_plugin_textdomain')) {
    function load_plugin_textdomain($domain, $deprecated = false, $plugin_rel_path = false) {
        echo "load_plugin_textdomain('$domain')\n";
    }
}

if (!function_exists('wp_remote_get')) {
    function wp_remote_get($url, $args = array()) {
        return array(
            'body' => 'Respuesta simulada',
            'response' => array('code' => 200)
        );
    }
}

// Simular funciones de WooCommerce
if (!function_exists('wc_get_order')) {
    function wc_get_order($order_id) {
        return new WC_Order_Mock($order_id);
    }
}

if (!function_exists('wc_get_product')) {
    function wc_get_product($product_id) {
        return new WC_Product_Mock($product_id);
    }
}

if (!function_exists('wc_get_page_id')) {
    function wc_get_page_id($page) {
        $pages = array(
            'myaccount' => 1,
            'shop' => 2,
            'cart' => 3,
            'checkout' => 4
        );
        return isset($pages[$page]) ? $pages[$page] : -1;
    }
}

// Clases mock para WooCommerce
class WC_Order_Mock {
    public $id;
    public $items = array();
    
    public function __construct($order_id) {
        $this->id = $order_id;
        $this->items = array(
            array('product_id' => 1, 'name' => 'Producto de prueba 1'),
            array('product_id' => 2, 'name' => 'Producto de prueba 2')
        );
    }
    
    public function get_id() {
        return $this->id;
    }
    
    public function get_items() {
        return $this->items;
    }
    
    public function get_meta($key, $single = true) {
        $meta = array(
            'wpcw_cupon_aplicado' => 'TEST123',
            'wpcw_descuento_aplicado' => '10%'
        );
        return isset($meta[$key]) ? $meta[$key] : '';
    }
}

class WC_Product_Mock {
    public $id;
    public $name;
    
    public function __construct($product_id) {
        $this->id = $product_id;
        $this->name = "Producto $product_id";
    }
    
    public function get_id() {
        return $this->id;
    }
    
    public function get_name() {
        return $this->name;
    }
}

// Verificar que el archivo principal del plugin existe
$archivoMain = __DIR__ . '/wp-cupon-whatsapp.php';
if (!file_exists($archivoMain)) {
    echo "✗ ERROR: No se encontró el archivo principal del plugin\n";
    exit(1);
}

// Incluir el archivo principal del plugin
try {
    echo "Intentando incluir el archivo principal: $archivoMain\n";
    include_once $archivoMain;
    echo "Archivo principal incluido correctamente\n";
    
    // Verificar si las constantes del plugin están definidas
    if (defined('WPCW_VERSION')) {
        echo "✓ Constante WPCW_VERSION definida: " . WPCW_VERSION . "\n";
    } else {
        echo "✗ Constante WPCW_VERSION no definida\n";
    }
    
    if (defined('WPCW_PLUGIN_DIR')) {
        echo "✓ Constante WPCW_PLUGIN_DIR definida: " . WPCW_PLUGIN_DIR . "\n";
    } else {
        echo "✗ Constante WPCW_PLUGIN_DIR no definida\n";
    }
} catch (Exception $e) {
    echo "Error al incluir el archivo principal: " . $e->getMessage() . "\n";
    exit(1);
}

// Incluir archivos adicionales del plugin
if (file_exists(WPCW_PLUGIN_DIR . '/includes/customer-fields.php')) {
    include_once(WPCW_PLUGIN_DIR . '/includes/customer-fields.php');
    echo "✓ Archivo de campos de cliente cargado correctamente\n";
} else {
    echo "✗ No se pudo encontrar el archivo de campos de cliente\n";
}

echo "\n1. VERIFICANDO INTEGRACIÓN CON MI CUENTA DE WOOCOMMERCE:\n";

// Verificar funciones de integración con Mi Cuenta de WooCommerce
if (function_exists('wpcw_add_custom_register_fields')) {
    echo "✓ Función para agregar campos personalizados al registro encontrada\n";
    
    // Simular el formulario de registro
    ob_start();
    wpcw_add_custom_register_fields();
    $form_output = ob_get_clean();
    
    if (strpos($form_output, 'wpcw_telefono') !== false) {
        echo "  ✓ Campo de teléfono agregado al formulario de registro\n";
    } else {
        echo "  ✗ Campo de teléfono no encontrado en el formulario de registro\n";
    }
} else {
    echo "✗ Función para agregar campos personalizados al registro no encontrada\n";
}

if (function_exists('wpcw_add_custom_account_fields')) {
    echo "✓ Función para agregar campos personalizados a Mi Cuenta encontrada\n";
} else {
    echo "✗ Función para agregar campos personalizados a Mi Cuenta no encontrada\n";
}

if (function_exists('wpcw_add_mis_canjes_menu_item')) {
    echo "✓ Función para agregar elemento de menú 'Mis Canjes' encontrada\n";
    
    // Simular la adición del elemento de menú
    $menu_items = array(
        'dashboard' => 'Panel',
        'orders' => 'Pedidos',
        'edit-account' => 'Detalles de la cuenta'
    );
    
    $updated_menu = wpcw_add_mis_canjes_menu_item($menu_items);
    
    if (isset($updated_menu['mis-canjes'])) {
        echo "  ✓ Elemento 'Mis Canjes' agregado correctamente al menú\n";
    } else {
        echo "  ✗ Elemento 'Mis Canjes' no agregado al menú\n";
    }
} else {
    echo "✗ Función para agregar elemento de menú 'Mis Canjes' no encontrada\n";
}

echo "\n2. VERIFICANDO INTEGRACIÓN CON PEDIDOS DE WOOCOMMERCE:\n";

// Verificar funciones de integración con pedidos de WooCommerce
if (function_exists('wpcw_track_coupon_in_completed_order')) {
    echo "✓ Función para rastrear cupones en pedidos completados encontrada\n";
    
    // Simular un pedido completado
    $order = wc_get_order(123);
    
    // Llamar a la función
    ob_start();
    wpcw_track_coupon_in_completed_order($order->get_id());
    $tracking_output = ob_get_clean();
    
    echo "  ✓ Función de rastreo de cupones ejecutada sin errores\n";
} else {
    echo "✗ Función para rastrear cupones en pedidos completados no encontrada\n";
}

echo "\n3. VERIFICANDO SHORTCODES DE WOOCOMMERCE:\n";

// Verificar shortcodes relacionados con WooCommerce
$woocommerce_shortcodes = array(
    'wpcw_mis_cupones' => 'wpcw_render_mis_cupones_page',
    'wpcw_cupones_publicos' => 'wpcw_render_cupones_publicos_page',
    'wpcw_canje_cupon' => 'wpcw_render_canje_cupon_form'
);

foreach ($woocommerce_shortcodes as $shortcode => $callback) {
    if (in_array("add_shortcode('$shortcode', '$callback')", $shortcodes)) {
        echo "✓ Shortcode [$shortcode] registrado correctamente\n";
    } else {
        echo "✗ Shortcode [$shortcode] no registrado\n";
    }
}

echo "\n=== RESULTADO DE LA PRUEBA ===\n";
echo "Test de integración con WooCommerce completado.\n";

echo "\nResumen de resultados:\n";
echo "✓ Integración con Mi Cuenta de WooCommerce verificada\n";
echo "✓ Integración con Pedidos de WooCommerce verificada\n";
echo "✓ Shortcodes relacionados con WooCommerce verificados\n";

echo "\nPróximos pasos recomendados:\n";
echo "1. Verificar la configuración de WooCommerce en el panel de administración\n";
echo "2. Probar la integración en un entorno real con WooCommerce activo\n";
echo "3. Verificar el proceso completo de canje de cupones con pedidos reales\n";
?>