<?php
/**
 * Gestión de Mensajes y Errores
 *
 * @package WP_Cupon_WhatsApp
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Clase para manejar mensajes y errores del plugin
 */
class WPCW_Messages {
    /**
     * Almacena los mensajes
     *
     * @var array
     */
    private static $messages = array();

    /**
     * Tipos de mensaje válidos
     *
     * @var array
     */
    private static $valid_types = array('success', 'error', 'warning', 'info');

    /**
     * Añade un mensaje
     *
     * @param string $message El mensaje a mostrar
     * @param string $type Tipo de mensaje (success|error|warning|info)
     * @param string $context Contexto del mensaje (admin|public)
     */
    public static function add($message, $type = 'info', $context = 'admin') {
        if (!in_array($type, self::$valid_types)) {
            $type = 'info';
        }

        self::$messages[] = array(
            'message' => $message,
            'type' => $type,
            'context' => $context
        );

        if ($context === 'admin') {
            add_action('admin_notices', array(__CLASS__, 'display_admin_messages'));
        }
    }

    /**
     * Muestra mensajes en el admin
     */
    public static function display_admin_messages() {
        foreach (self::$messages as $msg) {
            if ($msg['context'] === 'admin') {
                $class = 'notice notice-' . $msg['type'];
                printf(
                    '<div class="%1$s"><p>%2$s</p></div>',
                    esc_attr($class),
                    esc_html($msg['message'])
                );
            }
        }
    }

    /**
     * Obtiene mensajes para el frontend
     *
     * @return array
     */
    public static function get_public_messages() {
        $public_messages = array();
        foreach (self::$messages as $msg) {
            if ($msg['context'] === 'public') {
                $public_messages[] = $msg;
            }
        }
        return $public_messages;
    }

    /**
     * Limpia todos los mensajes
     */
    public static function clear() {
        self::$messages = array();
    }

    /**
     * Devuelve mensajes de error formateados para AJAX
     *
     * @param string $message
     * @param mixed $data
     * @return array
     */
    public static function ajax_error($message, $data = null) {
        return array(
            'success' => false,
            'message' => $message,
            'data' => $data
        );
    }

    /**
     * Devuelve mensajes de éxito formateados para AJAX
     *
     * @param string $message
     * @param mixed $data
     * @return array
     */
    public static function ajax_success($message, $data = null) {
        return array(
            'success' => true,
            'message' => $message,
            'data' => $data
        );
    }
}
