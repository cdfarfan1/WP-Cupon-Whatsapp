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
     * Create canjes table.
     */
    public static function create_canjes_table() {
        global $wpdb;
        $table_name = WPCW_CANJES_TABLE_NAME;
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
        dbDelta( $sql );
    }
}
