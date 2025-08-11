<?php
/**
 * Sistema de Logging
 *
 * @package WP_Cupon_WhatsApp
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Clase para manejar el logging del plugin
 */
class WPCW_Logger {
    /**
     * Registra un mensaje en el log
     *
     * @param string $tipo error|warning|info|debug
     * @param string $mensaje
     * @param array  $contexto
     */
    public static function log($tipo, $mensaje, $contexto = array()) {
        if (!in_array($tipo, array('error', 'warning', 'info', 'debug'))) {
            $tipo = 'info';
        }

        $tiempo = current_time('mysql');
        $datos_log = array(
            'tiempo' => $tiempo,
            'tipo' => $tipo,
            'mensaje' => $mensaje,
            'contexto' => $contexto
        );

        // Guardar en la base de datos
        self::guardar_log($datos_log);

        // Si es un error, también lo registramos en error_log
        if ($tipo === 'error') {
            error_log(sprintf(
                '[WPCW] %s - %s - %s',
                $tiempo,
                $mensaje,
                json_encode($contexto ?: array())
            ));
        }
    }

    /**
     * Guarda el log en la base de datos
     *
     * @param array $datos_log
     */
    private static function guardar_log($datos_log) {
        global $wpdb;
        
        // Crear tabla si no existe
        self::crear_tabla_log();
        
        $wpdb->insert(
            $wpdb->prefix . 'wpcw_logs',
            array(
                'tiempo' => $datos_log['tiempo'],
                'tipo' => $datos_log['tipo'],
                'mensaje' => $datos_log['mensaje'],
                'contexto' => json_encode($datos_log['contexto'] ?: array())
            ),
            array('%s', '%s', '%s', '%s')
        );
    }

    /**
     * Crea la tabla de logs si no existe
     */
    public static function crear_tabla_log() {
        global $wpdb;
        $tabla = $wpdb->prefix . 'wpcw_logs';

        if ($wpdb->get_var("SHOW TABLES LIKE '$tabla'") != $tabla) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $tabla (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                tiempo datetime NOT NULL,
                tipo varchar(20) NOT NULL,
                mensaje text NOT NULL,
                contexto longtext NOT NULL,
                PRIMARY KEY  (id),
                KEY tipo (tipo),
                KEY tiempo (tiempo)
            ) $charset_collate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }
    }

    /**
     * Limpia logs antiguos
     *
     * @param int $dias Número de días a mantener
     */
    public static function limpiar_logs_antiguos($dias = 30) {
        global $wpdb;
        $tabla = $wpdb->prefix . 'wpcw_logs';
        
        // Verificar si la tabla existe
        if ($wpdb->get_var("SHOW TABLES LIKE '$tabla'") !== $tabla) {
            return false;
        }
        
        try {
            $result = $wpdb->query($wpdb->prepare(
                "DELETE FROM $tabla WHERE tiempo < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $dias
            ));
            
            return $result !== false;
        } catch (Exception $e) {
            error_log(sprintf(
                '[WPCW] Error limpiando logs antiguos: %s',
                $e->getMessage()
            ));
            return false;
        }
    }
    
    /**
     * Programa la limpieza automática de logs
     */
    public static function schedule_log_cleanup() {
        if (!wp_next_scheduled('wpcw_clean_old_logs')) {
            wp_schedule_event(time(), 'daily', 'wpcw_clean_old_logs');
        }
    }
    
    /**
     * Elimina la tarea programada de limpieza de logs
     */
    public static function unschedule_log_cleanup() {
        $timestamp = wp_next_scheduled('wpcw_clean_old_logs');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'wpcw_clean_old_logs');
        }
    }
}
