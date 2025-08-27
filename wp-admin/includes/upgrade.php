<?php
/**
 * Archivo simulado de WordPress wp-admin/includes/upgrade.php
 * Solo para testing del plugin
 */

if (!function_exists('dbDelta')) {
    /**
     * Función simulada dbDelta para testing
     */
    function dbDelta($queries) {
        echo "DBDELTA: Simulando creación/actualización de tabla\n";
        return array(
            'queries' => is_array($queries) ? $queries : array($queries),
            'results' => array('Table created successfully')
        );
    }
}

?>