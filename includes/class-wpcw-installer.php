<?php
/**
 * WPCW Installer Class
 *
 * @package WP_Cupon_WhatsApp
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class WPCW_Installer
 */
class WPCW_Installer {

    /**
     * Initialize plugin settings
     */
    public static function init_settings() {
        // MongoDB settings
        add_option('wpcw_mongodb_enabled', '0');
        add_option('wpcw_mongodb_uri', '');
        add_option('wpcw_mongodb_database', '');
        add_option('wpcw_mongodb_auto_sync', '0');
        add_option('wpcw_last_mongo_sync', '');
    }

    /**
     * Create canjes table.
     */
    public static function create_canjes_table() {
        global $wpdb;
        $table_name = WPCW_CANJES_TABLE_NAME;
        
        // Verificar si la tabla ya existe
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
            return true;
        }
        
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            numero_canje VARCHAR(255) NOT NULL,
            cliente_id BIGINT UNSIGNED NOT NULL,
            cupon_id BIGINT UNSIGNED NOT NULL,
            comercio_id BIGINT UNSIGNED,
            fecha_solicitud_canje DATETIME NOT NULL,
            fecha_confirmacion_canje DATETIME NULL,
            estado_canje VARCHAR(50) NOT NULL DEFAULT 'pendiente_confirmacion',
            token_confirmacion VARCHAR(255) NOT NULL,
            codigo_cupon_wc VARCHAR(255) NULL,
            id_pedido_wc BIGINT UNSIGNED NULL,
            origen_canje VARCHAR(50) NULL COMMENT 'ej: mis_cupones, publicos',
            notas_internas TEXT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY numero_canje (numero_canje),
            KEY cliente_id (cliente_id),
            KEY cupon_id (cupon_id),
            KEY comercio_id (comercio_id),
            KEY token_confirmacion (token_confirmacion)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        // Capturar errores de base de datos
        $wpdb->show_errors(false);
        $wpdb->suppress_errors(true);
        
        // Ejecutar la creación de la tabla
        dbDelta($sql);
        
        // Obtener cualquier error que haya ocurrido
        $last_error = $wpdb->last_error;
        
        // Restaurar la configuración de errores
        $wpdb->show_errors(true);
        $wpdb->suppress_errors(false);

        // Verificar si la tabla se creó correctamente
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
            // Verificar la estructura de la tabla
            $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
            if (count($columns) >= 13) { // Número esperado de columnas
                return true;
            }
        }
        
        // Si hay error, registrarlo
        if ($last_error && class_exists('WPCW_Logger')) {
            WPCW_Logger::log('error', 'Error creando tabla de canjes', array(
                'error' => $last_error,
                'query' => $sql
            ));
        }
        
        return false;
    }
}
