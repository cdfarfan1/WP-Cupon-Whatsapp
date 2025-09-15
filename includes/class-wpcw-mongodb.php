<?php
/**
 * WPCW MongoDB Integration Class
 *
 * @package WP_Cupon_WhatsApp
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class WPCW_MongoDB
 */
class WPCW_MongoDB {
    private static $instance = null;
    private $mongo;
    private $database;
    private $collections = array();

    /**
     * Constructor privado para Singleton
     */
    private function __construct() {
        $this->connect();
    }

    /**
     * Obtener instancia (Singleton)
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Conectar a MongoDB
     */
    private function connect() {
        try {
            $mongo_uri = get_option( 'wpcw_mongodb_uri', '' );
            $mongo_db  = get_option( 'wpcw_mongodb_database', '' );

            if ( empty( $mongo_uri ) || empty( $mongo_db ) ) {
                throw new Exception( 'MongoDB configuration is missing' );
            }

            $this->mongo    = new MongoDB\Client( $mongo_uri );
            $this->database = $this->mongo->selectDatabase( $mongo_db );
        } catch ( Exception $e ) {
            if ( class_exists( 'WPCW_Logger' ) ) {
                WPCW_Logger::log(
                    'error',
                    'MongoDB connection error',
                    array(
						'error' => $e->getMessage(),
                    )
                );
            }
        }
    }

    /**
     * Sincronizar datos de WordPress a MongoDB
     */
    public function sync_to_mongodb() {
        global $wpdb;

        try {
            // Sincronizar canjes
            $table_name = WPCW_CANJES_TABLE_NAME;
            $canjes     = $wpdb->get_results( "SELECT * FROM $table_name", ARRAY_A );

            $collection = $this->database->selectCollection( 'canjes' );

            foreach ( $canjes as $canje ) {
                // Convertir fechas a formato MongoDB
                if ( ! empty( $canje['fecha_solicitud_canje'] ) ) {
                    $canje['fecha_solicitud_canje'] = new MongoDB\BSON\UTCDateTime( strtotime( $canje['fecha_solicitud_canje'] ) * 1000 );
                }
                if ( ! empty( $canje['fecha_confirmacion_canje'] ) ) {
                    $canje['fecha_confirmacion_canje'] = new MongoDB\BSON\UTCDateTime( strtotime( $canje['fecha_confirmacion_canje'] ) * 1000 );
                }

                // Insertar o actualizar en MongoDB
                $collection->updateOne(
                    array( 'numero_canje' => $canje['numero_canje'] ),
                    array( '$set' => $canje ),
                    array( 'upsert' => true )
                );
            }

            // Registrar sincronización exitosa
            update_option( 'wpcw_last_mongo_sync', current_time( 'mysql' ) );
            return true;
        } catch ( Exception $e ) {
            if ( class_exists( 'WPCW_Logger' ) ) {
                WPCW_Logger::log(
                    'error',
                    'MongoDB sync error',
                    array(
						'error' => $e->getMessage(),
                    )
                );
            }
            return false;
        }
    }

    /**
     * Exportar datos a diferentes formatos
     */
    public function export_data( $format = 'json', $collection = 'canjes' ) {
        try {
            $data = $this->database->selectCollection( $collection )->find()->toArray();

            switch ( $format ) {
                case 'json':
                    return json_encode( $data ?: array() );

                case 'csv':
                    $output = fopen( 'php://temp', 'r+' );
                    // Escribir encabezados
                    if ( ! empty( $data ) ) {
                        fputcsv( $output, array_keys( (array) $data[0] ) );
                    }
                    // Escribir datos
                    foreach ( $data as $row ) {
                        fputcsv( $output, (array) $row );
                    }
                    rewind( $output );
                    $csv = stream_get_contents( $output );
                    fclose( $output );
                    return $csv;

                case 'xml':
                    $xml = new SimpleXMLElement( '<?xml version="1.0"?><data></data>' );
                    foreach ( $data as $item ) {
                        $node = $xml->addChild( 'item' );
                        foreach ( (array) $item as $key => $value ) {
                            $node->addChild( $key, (string) $value );
                        }
                    }
                    return $xml->asXML();

                default:
                    throw new Exception( 'Unsupported export format' );
            }
        } catch ( Exception $e ) {
            if ( class_exists( 'WPCW_Logger' ) ) {
                WPCW_Logger::log(
                    'error',
                    'Export error',
                    array(
						'error'  => $e->getMessage(),
						'format' => $format,
                    )
                );
            }
            return false;
        }
    }

    /**
     * Verificar conexión a MongoDB
     */
    public function test_connection() {
        try {
            $this->database->command( array( 'ping' => 1 ) );
            return true;
        } catch ( Exception $e ) {
            return false;
        }
    }
}
