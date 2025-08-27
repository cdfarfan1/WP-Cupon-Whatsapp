<?php
/**
 * Herramientas de migración y backup para configuraciones masivas
 *
 * @package WP_Cupon_WhatsApp
 * @version 1.4.0
 * @since 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPCW_Migration_Tools {

    private static $instance = null;
    private $backup_dir      = '';
    private $config_keys     = array();

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->backup_dir = WP_CONTENT_DIR . '/wpcw-backups/';
        $this->init_config_keys();
        $this->ensure_backup_directory();

        add_action( 'wp_ajax_wpcw_export_config', array( $this, 'ajax_export_config' ) );
        add_action( 'wp_ajax_wpcw_import_config', array( $this, 'ajax_import_config' ) );
        add_action( 'wp_ajax_wpcw_create_backup', array( $this, 'ajax_create_backup' ) );
        add_action( 'wp_ajax_wpcw_restore_backup', array( $this, 'ajax_restore_backup' ) );
        add_action( 'wp_ajax_wpcw_bulk_configure', array( $this, 'ajax_bulk_configure' ) );
    }

    /**
     * Inicializa las claves de configuración a migrar
     */
    private function init_config_keys() {
        $this->config_keys = array(
            // Configuraciones básicas
            'wpcw_phone_format',
            'wpcw_phone_regex',
            'wpcw_whatsapp_code',
            'wpcw_currency',
            'wpcw_currency_symbol',
            'wpcw_date_format',
            'wpcw_detected_country',
            'wpcw_manual_country',

            // Validaciones
            'wpcw_phone_required',
            'wpcw_email_suggestions',
            'wpcw_whatsapp_validation',

            // Configuraciones de formularios
            'wpcw_form_settings',
            'wpcw_field_configurations',
            'wpcw_validation_rules',

            // Configuraciones de roles
            'wpcw_role_permissions',
            'wpcw_user_capabilities',

            // Configuraciones de notificaciones
            'wpcw_email_templates',
            'wpcw_whatsapp_templates',
            'wpcw_notification_settings',

            // Configuraciones de integración
            'wpcw_elementor_settings',
            'wpcw_api_settings',
            'wpcw_third_party_integrations',
        );
    }

    /**
     * Asegura que el directorio de backup existe
     */
    private function ensure_backup_directory() {
        if ( ! file_exists( $this->backup_dir ) ) {
            wp_mkdir_p( $this->backup_dir );

            // Crear archivo .htaccess para proteger backups
            $htaccess_content = "Order deny,allow\nDeny from all";
            file_put_contents( $this->backup_dir . '.htaccess', $htaccess_content );

            // Crear index.php vacío
            file_put_contents( $this->backup_dir . 'index.php', '<?php // Silence is golden' );
        }
    }

    /**
     * Crea un backup completo de la configuración
     */
    public function create_backup( $backup_name = null ) {
        if ( ! $backup_name ) {
            $backup_name = 'backup_' . date( 'Y-m-d_H-i-s' );
        }

        $backup_data = array(
            'metadata'          => array(
                'version'           => WPCW_VERSION,
                'wordpress_version' => get_bloginfo( 'version' ),
                'site_url'          => get_site_url(),
                'admin_email'       => get_option( 'admin_email' ),
                'created_at'        => current_time( 'mysql' ),
                'created_by'        => get_current_user_id(),
                'backup_type'       => 'full',
            ),
            'configuration'     => $this->export_configuration(),
            'database_schema'   => $this->export_database_schema(),
            'custom_post_types' => $this->export_custom_post_types(),
            'user_roles'        => $this->export_user_roles(),
            'active_plugins'    => get_option( 'active_plugins', array() ),
            'theme_info'        => array(
                'template'   => get_template(),
                'stylesheet' => get_stylesheet(),
            ),
        );

        $backup_file = $this->backup_dir . $backup_name . '.json';
        $result      = file_put_contents( $backup_file, json_encode( $backup_data, JSON_PRETTY_PRINT ) );

        if ( $result !== false ) {
            // Crear backup comprimido
            $this->create_compressed_backup( $backup_name, $backup_data );

            // Log del backup
            $this->log_backup_created( $backup_name, $backup_file );

            return array(
                'success'     => true,
                'backup_name' => $backup_name,
                'backup_file' => $backup_file,
                'size'        => filesize( $backup_file ),
            );
        }

        return array(
            'success' => false,
            'message' => 'Error al crear el backup',
        );
    }

    /**
     * Exporta la configuración actual
     */
    public function export_configuration() {
        $config = array();

        foreach ( $this->config_keys as $key ) {
            $value = get_option( $key );
            if ( $value !== false ) {
                $config[ $key ] = $value;
            }
        }

        // Agregar configuraciones adicionales
        $config['timezone_string'] = get_option( 'timezone_string' );
        $config['date_format']     = get_option( 'date_format' );
        $config['time_format']     = get_option( 'time_format' );
        $config['start_of_week']   = get_option( 'start_of_week' );

        return $config;
    }

    /**
     * Exporta el esquema de base de datos personalizado
     */
    private function export_database_schema() {
        global $wpdb;

        $tables = array(
            $wpdb->prefix . 'wpcw_coupons',
            $wpdb->prefix . 'wpcw_redemptions',
            $wpdb->prefix . 'wpcw_applications',
            $wpdb->prefix . 'wpcw_stats',
        );

        $schema = array();

        foreach ( $tables as $table ) {
            $result = $wpdb->get_results( "SHOW CREATE TABLE `{$table}`", ARRAY_A );
            if ( ! empty( $result ) ) {
                $schema[ $table ] = $result[0]['Create Table'];
            }
        }

        return $schema;
    }

    /**
     * Exporta tipos de post personalizados
     */
    private function export_custom_post_types() {
        $post_types = array( 'wpcw_coupon', 'wpcw_application' );
        $data       = array();

        foreach ( $post_types as $post_type ) {
            $posts = get_posts(
                array(
					'post_type'   => $post_type,
					'numberposts' => -1,
					'post_status' => 'any',
                )
            );

            $data[ $post_type ] = array();
            foreach ( $posts as $post ) {
                $post_data            = $post->to_array();
                $post_data['meta']    = get_post_meta( $post->ID );
                $data[ $post_type ][] = $post_data;
            }
        }

        return $data;
    }

    /**
     * Exporta roles de usuario personalizados
     */
    private function export_user_roles() {
        global $wp_roles;

        $custom_roles = array();
        $wpcw_roles   = array( 'wpcw_business', 'wpcw_institution' );

        foreach ( $wpcw_roles as $role ) {
            if ( isset( $wp_roles->roles[ $role ] ) ) {
                $custom_roles[ $role ] = $wp_roles->roles[ $role ];
            }
        }

        return $custom_roles;
    }

    /**
     * Importa configuración desde archivo
     */
    public function import_configuration( $config_data, $options = array() ) {
        $default_options = array(
            'overwrite_existing' => true,
            'create_backup'      => true,
            'validate_data'      => true,
            'import_posts'       => false,
            'import_users'       => false,
        );

        $options = array_merge( $default_options, $options );

        // Crear backup antes de importar
        if ( $options['create_backup'] ) {
            $backup_result = $this->create_backup( 'pre_import_' . date( 'Y-m-d_H-i-s' ) );
            if ( ! $backup_result['success'] ) {
                return array(
                    'success' => false,
                    'message' => 'Error al crear backup previo a la importación',
                );
            }
        }

        // Validar datos
        if ( $options['validate_data'] ) {
            $validation = $this->validate_import_data( $config_data );
            if ( ! $validation['valid'] ) {
                return array(
                    'success' => false,
                    'message' => 'Datos de importación inválidos: ' . $validation['message'],
                );
            }
        }

        $imported_items = array();
        $errors         = array();

        try {
            // Importar configuración básica
            if ( isset( $config_data['configuration'] ) ) {
                $result                          = $this->import_basic_configuration( $config_data['configuration'], $options );
                $imported_items['configuration'] = $result;
            }

            // Importar roles de usuario
            if ( isset( $config_data['user_roles'] ) && ! empty( $config_data['user_roles'] ) ) {
                $result                       = $this->import_user_roles( $config_data['user_roles'] );
                $imported_items['user_roles'] = $result;
            }

            // Importar posts personalizados
            if ( $options['import_posts'] && isset( $config_data['custom_post_types'] ) ) {
                $result                              = $this->import_custom_post_types( $config_data['custom_post_types'] );
                $imported_items['custom_post_types'] = $result;
            }

            // Log de importación exitosa
            $this->log_import_completed( $imported_items );

            return array(
                'success'        => true,
                'imported_items' => $imported_items,
                'message'        => 'Configuración importada exitosamente',
            );

        } catch ( Exception $e ) {
            return array(
                'success' => false,
                'message' => 'Error durante la importación: ' . $e->getMessage(),
                'errors'  => $errors,
            );
        }
    }

    /**
     * Importa configuración básica
     */
    private function import_basic_configuration( $config, $options ) {
        $imported = 0;
        $skipped  = 0;

        foreach ( $config as $key => $value ) {
            $current_value = get_option( $key );

            if ( ! $options['overwrite_existing'] && $current_value !== false ) {
                ++$skipped;
                continue;
            }

            update_option( $key, $value );
            ++$imported;
        }

        return array(
            'imported' => $imported,
            'skipped'  => $skipped,
        );
    }

    /**
     * Importa roles de usuario
     */
    private function import_user_roles( $roles ) {
        $imported = 0;

        foreach ( $roles as $role_name => $role_data ) {
            remove_role( $role_name ); // Remover si existe
            add_role( $role_name, $role_data['name'], $role_data['capabilities'] );
            ++$imported;
        }

        return array( 'imported' => $imported );
    }

    /**
     * Importa tipos de post personalizados
     */
    private function import_custom_post_types( $post_types ) {
        $imported = array();

        foreach ( $post_types as $post_type => $posts ) {
            $imported[ $post_type ] = 0;

            foreach ( $posts as $post_data ) {
                // Remover meta antes de insertar
                $meta = $post_data['meta'];
                unset( $post_data['meta'] );
                unset( $post_data['ID'] ); // Permitir nuevo ID

                $post_id = wp_insert_post( $post_data );

                if ( ! is_wp_error( $post_id ) ) {
                    // Agregar meta
                    foreach ( $meta as $meta_key => $meta_values ) {
                        foreach ( $meta_values as $meta_value ) {
                            add_post_meta( $post_id, $meta_key, maybe_unserialize( $meta_value ) );
                        }
                    }
                    ++$imported[ $post_type ];
                }
            }
        }

        return $imported;
    }

    /**
     * Valida datos de importación
     */
    private function validate_import_data( $data ) {
        if ( ! is_array( $data ) ) {
            return array(
				'valid'   => false,
				'message' => 'Formato de datos inválido',
            );
        }

        if ( ! isset( $data['metadata'] ) ) {
            return array(
				'valid'   => false,
				'message' => 'Metadatos faltantes',
            );
        }

        $metadata = $data['metadata'];

        // Verificar versión
        if ( isset( $metadata['version'] ) ) {
            if ( version_compare( $metadata['version'], WPCW_VERSION, '>' ) ) {
                return array(
                    'valid'   => false,
                    'message' => 'La configuración es de una versión más nueva del plugin',
                );
            }
        }

        return array( 'valid' => true );
    }

    /**
     * Crea backup comprimido
     */
    private function create_compressed_backup( $backup_name, $backup_data ) {
        if ( class_exists( 'ZipArchive' ) ) {
            $zip      = new ZipArchive();
            $zip_file = $this->backup_dir . $backup_name . '.zip';

            if ( $zip->open( $zip_file, ZipArchive::CREATE ) === true ) {
                $zip->addFromString( $backup_name . '.json', json_encode( $backup_data, JSON_PRETTY_PRINT ) );
                $zip->addFromString( 'README.txt', $this->generate_backup_readme( $backup_data['metadata'] ) );
                $zip->close();

                return $zip_file;
            }
        }

        return false;
    }

    /**
     * Genera README para backup
     */
    private function generate_backup_readme( $metadata ) {
        return sprintf(
            "WP Cupón WhatsApp - Backup\n" .
            "========================\n\n" .
            "Versión del Plugin: %s\n" .
            "Versión de WordPress: %s\n" .
            "Sitio: %s\n" .
            "Creado: %s\n" .
            "Tipo: %s\n\n" .
            "Para restaurar este backup, use las herramientas de migración del plugin.\n",
            $metadata['version'],
            $metadata['wordpress_version'],
            $metadata['site_url'],
            $metadata['created_at'],
            $metadata['backup_type']
        );
    }

    /**
     * Lista backups disponibles
     */
    public function list_backups() {
        $backups = array();
        $files   = glob( $this->backup_dir . '*.json' );

        foreach ( $files as $file ) {
            $backup_data = json_decode( file_get_contents( $file ), true );
            if ( $backup_data && isset( $backup_data['metadata'] ) ) {
                $backups[] = array(
                    'name'     => basename( $file, '.json' ),
                    'file'     => $file,
                    'size'     => filesize( $file ),
                    'created'  => $backup_data['metadata']['created_at'],
                    'version'  => $backup_data['metadata']['version'],
                    'site_url' => $backup_data['metadata']['site_url'],
                );
            }
        }

        // Ordenar por fecha de creación
        usort(
            $backups,
            function ( $a, $b ) {
                return strtotime( $b['created'] ) - strtotime( $a['created'] );
            }
        );

        return $backups;
    }

    /**
     * Restaura un backup
     */
    public function restore_backup( $backup_name, $options = array() ) {
        $backup_file = $this->backup_dir . $backup_name . '.json';

        if ( ! file_exists( $backup_file ) ) {
            return array(
                'success' => false,
                'message' => 'Archivo de backup no encontrado',
            );
        }

        $backup_data = json_decode( file_get_contents( $backup_file ), true );

        if ( ! $backup_data ) {
            return array(
                'success' => false,
                'message' => 'Error al leer el archivo de backup',
            );
        }

        return $this->import_configuration( $backup_data, $options );
    }

    /**
     * Configuración masiva para múltiples sitios
     */
    public function bulk_configure( $sites_config ) {
        $results = array();

        foreach ( $sites_config as $site_id => $config ) {
            try {
                // Aplicar configuración específica del sitio
                $result              = $this->apply_site_configuration( $site_id, $config );
                $results[ $site_id ] = $result;
            } catch ( Exception $e ) {
                $results[ $site_id ] = array(
                    'success' => false,
                    'message' => $e->getMessage(),
                );
            }
        }

        return $results;
    }

    /**
     * Aplica configuración específica de sitio
     */
    private function apply_site_configuration( $site_id, $config ) {
        // En un entorno multisite, cambiar al sitio específico
        if ( is_multisite() && $site_id !== get_current_blog_id() ) {
            switch_to_blog( $site_id );
        }

        $result = $this->import_basic_configuration( $config, array( 'overwrite_existing' => true ) );

        // Restaurar sitio original en multisite
        if ( is_multisite() && $site_id !== get_current_blog_id() ) {
            restore_current_blog();
        }

        return array(
            'success'  => true,
            'imported' => $result['imported'],
            'skipped'  => $result['skipped'],
        );
    }

    /**
     * AJAX Handlers
     */
    public function ajax_export_config() {
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }

        $config = $this->export_configuration();

        wp_send_json_success(
            array(
				'config'   => $config,
				'filename' => 'wpcw_config_' . date( 'Y-m-d_H-i-s' ) . '.json',
            )
        );
    }

    public function ajax_import_config() {
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }

        if ( ! isset( $_FILES['config_file'] ) ) {
            wp_send_json_error( 'No se recibió archivo de configuración' );
        }

        $file        = $_FILES['config_file'];
        $config_data = json_decode( file_get_contents( $file['tmp_name'] ), true );

        if ( ! $config_data ) {
            wp_send_json_error( 'Archivo de configuración inválido' );
        }

        $result = $this->import_configuration( $config_data );

        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result['message'] );
        }
    }

    public function ajax_create_backup() {
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }

        $backup_name = sanitize_text_field( $_POST['backup_name'] ?? '' );
        $result      = $this->create_backup( $backup_name );

        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result['message'] );
        }
    }

    public function ajax_restore_backup() {
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }

        $backup_name = sanitize_text_field( $_POST['backup_name'] ?? '' );
        $result      = $this->restore_backup( $backup_name );

        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result['message'] );
        }
    }

    public function ajax_bulk_configure() {
        check_ajax_referer( 'wpcw_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }

        $sites_config = $_POST['sites_config'] ?? array();
        $result       = $this->bulk_configure( $sites_config );

        wp_send_json_success( $result );
    }

    /**
     * Logging methods
     */
    private function log_backup_created( $backup_name, $backup_file ) {
        if ( class_exists( 'WPCW_Logger' ) ) {
            WPCW_Logger::log(
                'migration',
                sprintf(
                    'Backup creado: %s (Archivo: %s, Tamaño: %s bytes)',
                    $backup_name,
                    $backup_file,
                    filesize( $backup_file )
                )
            );
        }
    }

    private function log_import_completed( $imported_items ) {
        if ( class_exists( 'WPCW_Logger' ) ) {
            WPCW_Logger::log(
                'migration',
                sprintf(
                    'Importación completada. Items importados: %s',
                    json_encode( $imported_items )
                )
            );
        }
    }
}

// Inicializar la clase
WPCW_Migration_Tools::get_instance();
