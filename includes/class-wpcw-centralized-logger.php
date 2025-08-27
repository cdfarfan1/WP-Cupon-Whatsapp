<?php
/**
 * Sistema de logs centralizado para debugging masivo
 *
 * @package WP_Cupon_WhatsApp
 * @version 1.4.0
 * @since 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPCW_Centralized_Logger {

    private static $instance = null;
    private $log_levels      = array();
    private $log_handlers    = array();
    private $site_identifier = '';
    private $batch_logs      = array();
    private $max_batch_size  = 100;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_log_levels();
        $this->init_site_identifier();
        $this->init_log_handlers();

        // Hooks para capturar eventos importantes
        add_action( 'wp_ajax_wpcw_send_logs', array( $this, 'ajax_send_logs' ) );
        add_action( 'wp_ajax_wpcw_clear_logs', array( $this, 'ajax_clear_logs' ) );
        add_action( 'wp_ajax_wpcw_get_log_stats', array( $this, 'ajax_get_log_stats' ) );

        // Envío automático de logs en batch
        add_action( 'wpcw_send_batch_logs', array( $this, 'send_batch_logs' ) );

        // Programar envío de logs si está habilitado
        if ( get_option( 'wpcw_centralized_logging', false ) ) {
            if ( ! wp_next_scheduled( 'wpcw_send_batch_logs' ) ) {
                wp_schedule_event( time(), 'hourly', 'wpcw_send_batch_logs' );
            }
        }

        // Capturar errores PHP
        if ( get_option( 'wpcw_log_php_errors', false ) ) {
            set_error_handler( array( $this, 'handle_php_error' ) );
        }
    }

    /**
     * Inicializa los niveles de log
     */
    private function init_log_levels() {
        $this->log_levels = array(
            'emergency' => 0,
            'alert'     => 1,
            'critical'  => 2,
            'error'     => 3,
            'warning'   => 4,
            'notice'    => 5,
            'info'      => 6,
            'debug'     => 7,
        );
    }

    /**
     * Inicializa el identificador único del sitio
     */
    private function init_site_identifier() {
        $this->site_identifier = get_option( 'wpcw_site_identifier' );

        if ( ! $this->site_identifier ) {
            $this->site_identifier = $this->generate_site_identifier();
            update_option( 'wpcw_site_identifier', $this->site_identifier );
        }
    }

    /**
     * Genera un identificador único para el sitio
     */
    private function generate_site_identifier() {
        $site_url     = get_site_url();
        $admin_email  = get_option( 'admin_email' );
        $install_time = get_option( 'wpcw_install_time', time() );

        return md5( $site_url . $admin_email . $install_time );
    }

    /**
     * Inicializa los manejadores de logs
     */
    private function init_log_handlers() {
        $this->log_handlers = array(
            'file'     => array(
                'enabled' => true,
                'handler' => array( $this, 'write_to_file' ),
            ),
            'database' => array(
                'enabled' => get_option( 'wpcw_log_to_database', true ),
                'handler' => array( $this, 'write_to_database' ),
            ),
            'remote'   => array(
                'enabled' => get_option( 'wpcw_log_to_remote', false ),
                'handler' => array( $this, 'send_to_remote' ),
            ),
            'email'    => array(
                'enabled' => get_option( 'wpcw_log_critical_emails', false ),
                'handler' => array( $this, 'send_critical_email' ),
            ),
        );
    }

    /**
     * Registra un log con contexto completo
     */
    public function log( $level, $message, $context = array() ) {
        if ( ! $this->should_log( $level ) ) {
            return;
        }

        $log_entry = $this->create_log_entry( $level, $message, $context );

        // Procesar con cada handler habilitado
        foreach ( $this->log_handlers as $handler_name => $handler_config ) {
            if ( $handler_config['enabled'] ) {
                call_user_func( $handler_config['handler'], $log_entry, $level );
            }
        }

        // Agregar a batch para envío remoto
        if ( get_option( 'wpcw_centralized_logging', false ) ) {
            $this->add_to_batch( $log_entry );
        }
    }

    /**
     * Crea una entrada de log completa
     */
    private function create_log_entry( $level, $message, $context ) {
        global $wp_version;

        $log_entry = array(
            'timestamp'    => current_time( 'mysql' ),
            'microtime'    => microtime( true ),
            'level'        => $level,
            'message'      => $message,
            'context'      => $context,
            'site_info'    => array(
                'site_id'        => $this->site_identifier,
                'site_url'       => get_site_url(),
                'wp_version'     => $wp_version,
                'plugin_version' => WPCW_VERSION,
                'php_version'    => PHP_VERSION,
                'is_multisite'   => is_multisite(),
                'active_theme'   => get_template(),
                'memory_usage'   => memory_get_usage( true ),
                'memory_peak'    => memory_get_peak_usage( true ),
            ),
            'request_info' => array(
                'url'        => $_SERVER['REQUEST_URI'] ?? '',
                'method'     => $_SERVER['REQUEST_METHOD'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'ip'         => $this->get_client_ip(),
                'user_id'    => get_current_user_id(),
                'is_admin'   => is_admin(),
                'is_ajax'    => wp_doing_ajax(),
                'is_cron'    => wp_doing_cron(),
            ),
            'stack_trace'  => $this->get_stack_trace(),
            'session_id'   => $this->get_session_id(),
        );

        return $log_entry;
    }

    /**
     * Determina si se debe registrar el log según el nivel
     */
    private function should_log( $level ) {
        $min_level           = get_option( 'wpcw_min_log_level', 'info' );
        $min_level_value     = $this->log_levels[ $min_level ] ?? 6;
        $current_level_value = $this->log_levels[ $level ] ?? 7;

        return $current_level_value <= $min_level_value;
    }

    /**
     * Escribe log a archivo
     */
    private function write_to_file( $log_entry, $level ) {
        $log_dir = WP_CONTENT_DIR . '/wpcw-logs/';

        if ( ! file_exists( $log_dir ) ) {
            wp_mkdir_p( $log_dir );
            file_put_contents( $log_dir . '.htaccess', "Order deny,allow\nDeny from all" );
        }

        $log_file = $log_dir . 'wpcw-' . date( 'Y-m-d' ) . '.log';
        $log_line = sprintf(
            "[%s] %s.%s %s: %s %s\n",
            $log_entry['timestamp'],
            $this->site_identifier,
            strtoupper( $level ),
            $log_entry['request_info']['url'],
            $log_entry['message'],
            ! empty( $log_entry['context'] ) ? json_encode( $log_entry['context'] ) : ''
        );

        file_put_contents( $log_file, $log_line, FILE_APPEND | LOCK_EX );

        // Rotar logs si es necesario
        $this->rotate_log_files( $log_dir );
    }

    /**
     * Escribe log a base de datos
     */
    private function write_to_database( $log_entry, $level ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpcw_logs';

        // Crear tabla si no existe
        $this->ensure_log_table_exists();

        $wpdb->insert(
            $table_name,
            array(
                'timestamp'    => $log_entry['timestamp'],
                'microtime'    => $log_entry['microtime'],
                'level'        => $level,
                'message'      => $log_entry['message'],
                'context'      => json_encode( $log_entry['context'] ),
                'site_info'    => json_encode( $log_entry['site_info'] ),
                'request_info' => json_encode( $log_entry['request_info'] ),
                'stack_trace'  => $log_entry['stack_trace'],
                'session_id'   => $log_entry['session_id'],
            ),
            array( '%s', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
        );

        // Limpiar logs antiguos
        $this->cleanup_old_database_logs();
    }

    /**
     * Envía log a servidor remoto
     */
    private function send_to_remote( $log_entry, $level ) {
        $remote_url = get_option( 'wpcw_remote_log_url' );

        if ( ! $remote_url ) {
            return;
        }

        $payload = array(
            'api_key'   => get_option( 'wpcw_remote_log_api_key' ),
            'log_entry' => $log_entry,
        );

        wp_remote_post(
            $remote_url,
            array(
				'body'     => json_encode( $payload ),
				'headers'  => array(
					'Content-Type' => 'application/json',
					'User-Agent'   => 'WPCW-Logger/1.0',
				),
				'timeout'  => 5,
				'blocking' => false, // No bloquear la ejecución
            )
        );
    }

    /**
     * Envía email para logs críticos
     */
    private function send_critical_email( $log_entry, $level ) {
        $critical_levels = array( 'emergency', 'alert', 'critical', 'error' );

        if ( ! in_array( $level, $critical_levels ) ) {
            return;
        }

        $admin_email = get_option( 'admin_email' );
        $site_name   = get_bloginfo( 'name' );

        $subject = sprintf( '[%s] Error Crítico en WP Cupón WhatsApp', $site_name );

        $message = sprintf(
            "Se ha registrado un error crítico en el plugin WP Cupón WhatsApp:\n\n" .
            "Sitio: %s\n" .
            "Nivel: %s\n" .
            "Mensaje: %s\n" .
            "Fecha: %s\n" .
            "URL: %s\n" .
            "Usuario: %s\n\n" .
            "Contexto: %s\n\n" .
            'Stack Trace: %s',
            get_site_url(),
            strtoupper( $level ),
            $log_entry['message'],
            $log_entry['timestamp'],
            $log_entry['request_info']['url'],
            $log_entry['request_info']['user_id'],
            json_encode( $log_entry['context'], JSON_PRETTY_PRINT ),
            $log_entry['stack_trace']
        );

        wp_mail( $admin_email, $subject, $message );
    }

    /**
     * Agrega log al batch para envío remoto
     */
    private function add_to_batch( $log_entry ) {
        $this->batch_logs[] = $log_entry;

        if ( count( $this->batch_logs ) >= $this->max_batch_size ) {
            $this->send_batch_logs();
        }
    }

    /**
     * Envía logs en batch
     */
    public function send_batch_logs() {
        if ( empty( $this->batch_logs ) ) {
            return;
        }

        $remote_url = get_option( 'wpcw_remote_batch_log_url' );

        if ( ! $remote_url ) {
            return;
        }

        $payload = array(
            'api_key'    => get_option( 'wpcw_remote_log_api_key' ),
            'site_id'    => $this->site_identifier,
            'batch_id'   => uniqid(),
            'logs'       => $this->batch_logs,
            'batch_info' => array(
                'count'          => count( $this->batch_logs ),
                'sent_at'        => current_time( 'mysql' ),
                'plugin_version' => WPCW_VERSION,
            ),
        );

        $response = wp_remote_post(
            $remote_url,
            array(
				'body'    => json_encode( $payload ),
				'headers' => array(
					'Content-Type' => 'application/json',
					'User-Agent'   => 'WPCW-Logger/1.0',
				),
				'timeout' => 30,
            )
        );

        if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
            $this->batch_logs = array(); // Limpiar batch enviado
            update_option( 'wpcw_last_batch_sent', current_time( 'mysql' ) );
        }
    }

    /**
     * Maneja errores PHP
     */
    public function handle_php_error( $errno, $errstr, $errfile, $errline ) {
        $error_types = array(
            E_ERROR             => 'error',
            E_WARNING           => 'warning',
            E_PARSE             => 'critical',
            E_NOTICE            => 'notice',
            E_CORE_ERROR        => 'critical',
            E_CORE_WARNING      => 'warning',
            E_COMPILE_ERROR     => 'critical',
            E_COMPILE_WARNING   => 'warning',
            E_USER_ERROR        => 'error',
            E_USER_WARNING      => 'warning',
            E_USER_NOTICE       => 'notice',
            E_STRICT            => 'notice',
            E_RECOVERABLE_ERROR => 'error',
            E_DEPRECATED        => 'notice',
            E_USER_DEPRECATED   => 'notice',
        );

        $level = $error_types[ $errno ] ?? 'error';

        // Solo registrar errores relacionados con el plugin
        if ( strpos( $errfile, 'wp-cupon-whatsapp' ) !== false ) {
            $this->log(
                $level,
                $errstr,
                array(
					'file'       => $errfile,
					'line'       => $errline,
					'error_code' => $errno,
					'error_type' => 'php_error',
                )
            );
        }

        return false; // Permitir que el manejador de errores por defecto también procese
    }

    /**
     * Obtiene estadísticas de logs
     */
    public function get_log_stats( $days = 7 ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpcw_logs';
        $since_date = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

        $stats = array(
            'total_logs'  => 0,
            'by_level'    => array(),
            'by_day'      => array(),
            'top_errors'  => array(),
            'performance' => array(
                'avg_memory'    => 0,
                'peak_memory'   => 0,
                'slow_requests' => 0,
            ),
        );

        // Total de logs
        $total               = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE timestamp >= %s",
                $since_date
            )
        );
        $stats['total_logs'] = intval( $total );

        // Por nivel
        $by_level = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT level, COUNT(*) as count FROM {$table_name} WHERE timestamp >= %s GROUP BY level",
                $since_date
            ),
            ARRAY_A
        );

        foreach ( $by_level as $row ) {
            $stats['by_level'][ $row['level'] ] = intval( $row['count'] );
        }

        // Por día
        $by_day = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DATE(timestamp) as date, COUNT(*) as count FROM {$table_name} WHERE timestamp >= %s GROUP BY DATE(timestamp)",
                $since_date
            ),
            ARRAY_A
        );

        foreach ( $by_day as $row ) {
            $stats['by_day'][ $row['date'] ] = intval( $row['count'] );
        }

        // Top errores
        $top_errors = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT message, COUNT(*) as count FROM {$table_name} WHERE timestamp >= %s AND level IN ('error', 'critical', 'emergency') GROUP BY message ORDER BY count DESC LIMIT 10",
                $since_date
            ),
            ARRAY_A
        );

        $stats['top_errors'] = $top_errors;

        return $stats;
    }

    /**
     * Métodos auxiliares
     */
    private function get_client_ip() {
        $ip_keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );

        foreach ( $ip_keys as $key ) {
            if ( array_key_exists( $key, $_SERVER ) === true ) {
                foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
                    $ip = trim( $ip );
                    if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
                        return $ip;
                    }
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    private function get_stack_trace() {
        $trace           = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 10 );
        $formatted_trace = array();

        foreach ( $trace as $frame ) {
            if ( isset( $frame['file'] ) && strpos( $frame['file'], 'wp-cupon-whatsapp' ) !== false ) {
                $formatted_trace[] = sprintf(
                    '%s:%d %s%s%s()',
                    basename( $frame['file'] ),
                    $frame['line'] ?? 0,
                    $frame['class'] ?? '',
                    $frame['type'] ?? '',
                    $frame['function'] ?? ''
                );
            }
        }

        return implode( ' -> ', $formatted_trace );
    }

    private function get_session_id() {
        if ( session_status() === PHP_SESSION_ACTIVE ) {
            return session_id();
        }

        return md5( $_SERVER['HTTP_USER_AGENT'] ?? '' . $_SERVER['REMOTE_ADDR'] ?? '' );
    }

    private function ensure_log_table_exists() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wpcw_logs';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            timestamp datetime NOT NULL,
            microtime decimal(15,4) NOT NULL,
            level varchar(20) NOT NULL,
            message text NOT NULL,
            context longtext,
            site_info longtext,
            request_info longtext,
            stack_trace text,
            session_id varchar(64),
            PRIMARY KEY (id),
            KEY level (level),
            KEY timestamp (timestamp),
            KEY session_id (session_id)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    private function rotate_log_files( $log_dir ) {
        $max_files = get_option( 'wpcw_max_log_files', 30 );
        $files     = glob( $log_dir . 'wpcw-*.log' );

        if ( count( $files ) > $max_files ) {
            usort(
                $files,
                function ( $a, $b ) {
                    return filemtime( $a ) - filemtime( $b );
                }
            );

            $files_to_delete = array_slice( $files, 0, count( $files ) - $max_files );
            foreach ( $files_to_delete as $file ) {
                unlink( $file );
            }
        }
    }

    private function cleanup_old_database_logs() {
        global $wpdb;

        $table_name     = $wpdb->prefix . 'wpcw_logs';
        $retention_days = get_option( 'wpcw_log_retention_days', 30 );
        $cutoff_date    = date( 'Y-m-d H:i:s', strtotime( "-{$retention_days} days" ) );

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$table_name} WHERE timestamp < %s",
                $cutoff_date
            )
        );
    }

    /**
     * AJAX Handlers
     */
    public function ajax_send_logs() {
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }

        $this->send_batch_logs();

        wp_send_json_success(
            array(
				'message'   => 'Logs enviados correctamente',
				'last_sent' => get_option( 'wpcw_last_batch_sent' ),
            )
        );
    }

    public function ajax_clear_logs() {
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wpcw_logs';
        $wpdb->query( "TRUNCATE TABLE {$table_name}" );

        // Limpiar archivos de log
        $log_dir = WP_CONTENT_DIR . '/wpcw-logs/';
        $files   = glob( $log_dir . 'wpcw-*.log' );
        foreach ( $files as $file ) {
            unlink( $file );
        }

        wp_send_json_success(
            array(
				'message' => 'Logs limpiados correctamente',
            )
        );
    }

    public function ajax_get_log_stats() {
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }

        $days  = intval( $_GET['days'] ?? 7 );
        $stats = $this->get_log_stats( $days );

        wp_send_json_success( $stats );
    }

    /**
     * Métodos de conveniencia para logging
     */
    public function emergency( $message, $context = array() ) {
        $this->log( 'emergency', $message, $context );
    }

    public function alert( $message, $context = array() ) {
        $this->log( 'alert', $message, $context );
    }

    public function critical( $message, $context = array() ) {
        $this->log( 'critical', $message, $context );
    }

    public function error( $message, $context = array() ) {
        $this->log( 'error', $message, $context );
    }

    public function warning( $message, $context = array() ) {
        $this->log( 'warning', $message, $context );
    }

    public function notice( $message, $context = array() ) {
        $this->log( 'notice', $message, $context );
    }

    public function info( $message, $context = array() ) {
        $this->log( 'info', $message, $context );
    }

    public function debug( $message, $context = array() ) {
        $this->log( 'debug', $message, $context );
    }
}

// Inicializar la clase
WPCW_Centralized_Logger::get_instance();

// Función global para facilitar el uso
if ( ! function_exists( 'wpcw_log' ) ) {
    function wpcw_log( $level, $message, $context = array() ) {
        WPCW_Centralized_Logger::get_instance()->log( $level, $message, $context );
    }
}
