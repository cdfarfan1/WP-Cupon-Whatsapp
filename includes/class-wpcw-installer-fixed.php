<?php
/**
 * WPCW Installer Class - Versión Corregida
 *
 * Handles plugin installation, database table creation, and initial setup
 * with improved error handling and robustness.
 *
 * @package WP_Cupon_WhatsApp
 * @since 1.2.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

class WPCW_Installer {

    /**
     * Initialize plugin settings
     */
    public static function init_settings() {
        try {
            // MongoDB settings
            add_option( 'wpcw_mongodb_enabled', false );
            add_option( 'wpcw_mongodb_connection_string', '' );
            add_option( 'wpcw_mongodb_database', 'wp_cupon_whatsapp' );
            add_option( 'wpcw_mongodb_collection', 'canjes' );

            // General settings
            add_option( 'wpcw_auto_create_pages', true );
            add_option( 'wpcw_whatsapp_api_enabled', false );
            add_option( 'wpcw_whatsapp_api_token', '' );
            add_option( 'wpcw_email_verification_enabled', true );
            add_option( 'wpcw_debug_mode', false );

            // Coupon settings
            add_option( 'wpcw_default_coupon_validity_days', 30 );
            add_option( 'wpcw_max_coupons_per_user', 5 );
            add_option( 'wpcw_allow_public_coupons', true );

            return true;

        } catch ( Exception $e ) {
            if ( class_exists( 'WPCW_Logger' ) ) {
                WPCW_Logger::log( 'Error initializing settings: ' . $e->getMessage(), 'error' );
            } else {
                error_log( 'WPCW_Installer: Error initializing settings: ' . $e->getMessage() );
            }
            return false;
        }
    }

    /**
     * Create canjes table with improved error handling
     */
    public static function create_canjes_table() {
        global $wpdb;

        try {
            // Verify $wpdb is available and has required methods
            if ( ! isset( $wpdb ) || ! is_object( $wpdb ) ) {
                throw new Exception( 'WordPress database object ($wpdb) is not available.' );
            }

            // Check if required methods exist
            $required_methods = array( 'get_var', 'get_charset_collate', 'show_errors', 'suppress_errors' );
            foreach ( $required_methods as $method ) {
                if ( ! method_exists( $wpdb, $method ) ) {
                    throw new Exception( "Required method $method not found in \$wpdb object." );
                }
            }

            // Ensure the constant is defined
            if ( ! defined( 'WPCW_CANJES_TABLE_NAME' ) ) {
                if ( property_exists( $wpdb, 'prefix' ) ) {
                    define( 'WPCW_CANJES_TABLE_NAME', $wpdb->prefix . 'wpcw_canjes' );
                } else {
                    throw new Exception( 'Cannot determine table name: $wpdb->prefix not available.' );
                }
            }

            $table_name      = WPCW_CANJES_TABLE_NAME;
            $charset_collate = $wpdb->get_charset_collate();

            // Check if table already exists
            $table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );

            if ( $table_exists === $table_name ) {
                // Table exists, verify structure
                return self::verify_table_structure( $table_name );
            }

            // Create table SQL
            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) UNSIGNED NOT NULL,
                coupon_id bigint(20) UNSIGNED NOT NULL,
                numero_canje varchar(20) NOT NULL,
                token_confirmacion varchar(64) NOT NULL,
                estado_canje varchar(50) NOT NULL DEFAULT 'pendiente_confirmacion',
                fecha_solicitud_canje datetime NOT NULL,
                fecha_confirmacion_canje datetime DEFAULT NULL,
                comercio_id bigint(20) UNSIGNED DEFAULT NULL,
                whatsapp_url text DEFAULT NULL,
                codigo_cupon_wc varchar(100) DEFAULT NULL,
                id_pedido_wc bigint(20) UNSIGNED DEFAULT NULL,
                origen_canje varchar(50) DEFAULT 'webapp',
                notas_internas text DEFAULT NULL,
                fecha_rechazo datetime DEFAULT NULL,
                fecha_cancelacion datetime DEFAULT NULL,
                created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY user_id (user_id),
                KEY coupon_id (coupon_id),
                KEY numero_canje (numero_canje),
                KEY estado_canje (estado_canje),
                KEY fecha_solicitud_canje (fecha_solicitud_canje),
                KEY comercio_id (comercio_id)
            ) $charset_collate;";

            // Load WordPress upgrade functions
            if ( ! function_exists( 'dbDelta' ) ) {
                $upgrade_file = ABSPATH . 'wp-admin/includes/upgrade.php';
                if ( file_exists( $upgrade_file ) ) {
                    require_once $upgrade_file;
                } else {
                    // Fallback: try to create table directly
                    return self::create_table_directly( $sql );
                }
            }

            // Enable error reporting temporarily
            $wpdb->show_errors();
            $suppress_errors = $wpdb->suppress_errors( false );

            // Log before dbDelta
            if ( class_exists( 'WPCW_Logger' ) ) {
                WPCW_Logger::log( 'info', 'Executing dbDelta for canjes table', array(
                    'table_name' => $table_name,
                    'memory_usage' => memory_get_usage(true),
                ) );
            }

            // Execute dbDelta
            $result = dbDelta( $sql );

            // Log after dbDelta
            if ( class_exists( 'WPCW_Logger' ) ) {
                WPCW_Logger::log( 'info', 'dbDelta execution completed', array(
                    'result' => $result,
                    'memory_usage' => memory_get_usage(true),
                ) );
            }

            // Restore error suppression
            $wpdb->suppress_errors( $suppress_errors );

            // Verify table was created
            $table_created = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );

            if ( $table_created !== $table_name ) {
                throw new Exception( 'Table creation failed: Table does not exist after dbDelta execution.' );
            }

            // Verify table structure
            $structure_ok = self::verify_table_structure( $table_name );

            if ( ! $structure_ok ) {
                throw new Exception( 'Table structure verification failed.' );
            }

            // Log success
            if ( class_exists( 'WPCW_Logger' ) ) {
                WPCW_Logger::log( 'Canjes table created successfully: ' . $table_name, 'info' );
            }

            return true;

        } catch ( Exception $e ) {
            $error_message = 'Error creating canjes table: ' . $e->getMessage();

            if ( class_exists( 'WPCW_Logger' ) ) {
                WPCW_Logger::log( $error_message, 'error' );
            } else {
                error_log( 'WPCW_Installer: ' . $error_message );
            }

            return false;
        }
    }

    /**
     * Create table directly without dbDelta (fallback method)
     */
    private static function create_table_directly( $sql ) {
        global $wpdb;

        try {
            $result = $wpdb->query( $sql );

            if ( $result === false ) {
                throw new Exception( 'Direct table creation failed: ' . $wpdb->last_error );
            }

            return true;

        } catch ( Exception $e ) {
            if ( class_exists( 'WPCW_Logger' ) ) {
                WPCW_Logger::log( 'Direct table creation error: ' . $e->getMessage(), 'error' );
            } else {
                error_log( 'WPCW_Installer: Direct table creation error: ' . $e->getMessage() );
            }
            return false;
        }
    }

    /**
     * Verify table structure
     */
    private static function verify_table_structure( $table_name ) {
        global $wpdb;

        try {
            // Get table columns
            $columns = $wpdb->get_results( "DESCRIBE $table_name" );

            if ( empty( $columns ) ) {
                return false;
            }

            // Required columns
            $required_columns = array(
                'id',
    'user_id',
    'coupon_id',
    'numero_canje',
    'token_confirmacion',
    'estado_canje',
    'fecha_solicitud_canje',
    'created_at',
    'updated_at',
            );

            $existing_columns = array();
            foreach ( $columns as $column ) {
                $existing_columns[] = $column->Field;
            }

            // Check if all required columns exist
            foreach ( $required_columns as $required_column ) {
                if ( ! in_array( $required_column, $existing_columns ) ) {
                    if ( class_exists( 'WPCW_Logger' ) ) {
                        WPCW_Logger::log( "Missing required column: $required_column", 'warning' );
                    }
                    return false;
                }
            }

            return true;

        } catch ( Exception $e ) {
            if ( class_exists( 'WPCW_Logger' ) ) {
                WPCW_Logger::log( 'Table structure verification error: ' . $e->getMessage(), 'error' );
            }
            return false;
        }
    }

    /**
     * Create plugin pages with improved error handling
     */
    public static function create_pages() {
        try {
            $pages = array(
                'mis_cupones'         => array(
                    'title'   => __( 'Mis Cupones Disponibles', 'wp-cupon-whatsapp' ),
                    'content' => '[wpcw_mis_cupones]',
                    'slug'    => 'mis-cupones-disponibles',
                ),
                'cupones_publicos'    => array(
                    'title'   => __( 'Cupones Públicos', 'wp-cupon-whatsapp' ),
                    'content' => '[wpcw_cupones_publicos]',
                    'slug'    => 'cupones-publicos',
                ),
                'formulario_adhesion' => array(
                    'title'   => __( 'Formulario de Adhesión', 'wp-cupon-whatsapp' ),
                    'content' => '[wpcw_formulario_adhesion]',
                    'slug'    => 'formulario-adhesion',
                ),
                'canje_cupon'         => array(
                    'title'   => __( 'Canje de Cupón', 'wp-cupon-whatsapp' ),
                    'content' => '[wpcw_canje_cupon]',
                    'slug'    => 'canje-cupon',
                ),
                'registro_beneficiario' => array(
                    'title'   => __( 'Registro de Beneficiarios', 'wp-cupon-whatsapp' ),
                    'content' => '[wpcw_registro_beneficiario]',
                    'slug'    => 'registro-beneficiarios',
                ),
                'portal_beneficiario'   => array(
                    'title'   => __( 'Portal de Beneficios', 'wp-cupon-whatsapp' ),
                    'content' => '[wpcw_portal_beneficiario]',
                    'slug'    => 'portal-beneficios',
                ),
            );

            $created_pages = array();

            foreach ( $pages as $page_key => $page_data ) {
                // Check if page already exists
                $existing_page = get_page_by_path( $page_data['slug'] );

                if ( $existing_page ) {
                    $created_pages[ $page_key ] = $existing_page->ID;
                    continue;
                }

                // Prepare page data
                $page_args = array(
                    'post_title'     => $page_data['title'],
                    'post_content'   => $page_data['content'],
                    'post_status'    => 'publish',
                    'post_type'      => 'page',
                    'post_name'      => $page_data['slug'],
                    'post_author'    => get_current_user_id(),
                    'comment_status' => 'closed',
                    'ping_status'    => 'closed',
                );

                // Insert page
                $page_id = wp_insert_post( $page_args, true );

                if ( is_wp_error( $page_id ) ) {
                    if ( class_exists( 'WPCW_Logger' ) ) {
                        WPCW_Logger::log( 'Error creating page ' . $page_key . ': ' . $page_id->get_error_message(), 'error' );
                    }
                    continue;
                }

                $created_pages[ $page_key ] = $page_id;

                // Log success
                if ( class_exists( 'WPCW_Logger' ) ) {
                    WPCW_Logger::log( 'Page created successfully: ' . $page_data['title'] . ' (ID: ' . $page_id . ')', 'info' );
                }
            }

            // Save page IDs
            update_option( 'wpcw_created_pages', $created_pages );

            return $created_pages;

        } catch ( Exception $e ) {
            if ( class_exists( 'WPCW_Logger' ) ) {
                WPCW_Logger::log( 'Error creating pages: ' . $e->getMessage(), 'error' );
            } else {
                error_log( 'WPCW_Installer: Error creating pages: ' . $e->getMessage() );
            }
            return false;
        }
    }

    /**
     * Auto create pages during activation
     */
    public static function auto_create_pages() {
        try {
            $auto_create = get_option( 'wpcw_auto_create_pages', true );

            if ( ! $auto_create ) {
                return true;
            }

            $result = self::create_pages();

            if ( $result === false ) {
                throw new Exception( 'Failed to create pages automatically.' );
            }

            // Update option to indicate pages were created
            update_option( 'wpcw_pages_created', true );

            return true;

        } catch ( Exception $e ) {
            if ( class_exists( 'WPCW_Logger' ) ) {
                WPCW_Logger::log( 'Error in auto_create_pages: ' . $e->getMessage(), 'error' );
            } else {
                error_log( 'WPCW_Installer: Error in auto_create_pages: ' . $e->getMessage() );
            }
            return false;
        }
    }

    /**
     * Check system requirements
     */
    public static function check_system_requirements() {
        $requirements = array(
            'php_version'   => array(
                'required' => '7.4',
                'current'  => PHP_VERSION,
                'met'      => version_compare( PHP_VERSION, '7.4', '>=' ),
            ),
            'wp_version'    => array(
                'required' => '5.0',
                'current'  => get_bloginfo( 'version' ),
                'met'      => version_compare( get_bloginfo( 'version' ), '5.0', '>=' ),
            ),
            'mysql_version' => array(
                'required' => '5.6',
                'current'  => self::get_mysql_version(),
                'met'      => version_compare( self::get_mysql_version(), '5.6', '>=' ),
            ),
        );

        return $requirements;
    }

    /**
     * Get MySQL version
     */
    private static function get_mysql_version() {
        global $wpdb;

        try {
            if ( isset( $wpdb ) && method_exists( $wpdb, 'get_var' ) ) {
                return $wpdb->get_var( 'SELECT VERSION()' );
            }
            return 'Unknown';
        } catch ( Exception $e ) {
            return 'Unknown';
        }
    }

    /**
     * Run installation checks
     */
    public static function run_installation_checks() {
        $checks = array();

        // Log start of checks
        if ( class_exists( 'WPCW_Logger' ) ) {
            WPCW_Logger::log( 'info', 'Starting installation checks', array(
                'memory_usage' => memory_get_usage(true),
            ) );
        }

        $checks['system_requirements'] = self::check_system_requirements();

        if ( class_exists( 'WPCW_Logger' ) ) {
            WPCW_Logger::log( 'info', 'System requirements check completed', array(
                'result' => $checks['system_requirements'],
                'memory_usage' => memory_get_usage(true),
            ) );
        }

        $checks['table_creation'] = self::create_canjes_table();

        if ( class_exists( 'WPCW_Logger' ) ) {
            WPCW_Logger::log( 'info', 'Table creation check completed', array(
                'result' => $checks['table_creation'],
                'memory_usage' => memory_get_usage(true),
            ) );
        }

        $checks['settings_initialization'] = self::init_settings();

        if ( class_exists( 'WPCW_Logger' ) ) {
            WPCW_Logger::log( 'info', 'Settings initialization check completed', array(
                'result' => $checks['settings_initialization'],
                'memory_usage' => memory_get_usage(true),
            ) );
        }

        $checks['pages_creation'] = self::auto_create_pages();

        if ( class_exists( 'WPCW_Logger' ) ) {
            WPCW_Logger::log( 'info', 'Pages creation check completed', array(
                'result' => $checks['pages_creation'],
                'memory_usage' => memory_get_usage(true),
            ) );
        }

        $checks['encryption_setup'] = self::setup_encryption();

        return $checks;
    }

    /**
     * Setup encryption key on activation.
     * Design by El Guardián de la Seguridad.
     */
    public static function setup_encryption() {
        // Check if key already exists
        if ( get_option( 'wpcw_encryption_key_path' ) && file_exists( get_option( 'wpcw_encryption_key_path' ) ) ) {
            return true;
        }

        try {
            $uploads = wp_upload_dir();
            $secure_dir_name = 'wpcw-secure-' . wp_generate_password( 12, false );
            $secure_dir_path = $uploads['basedir'] . '/' . $secure_dir_name;

            // Create secure directory
            if ( ! wp_mkdir_p( $secure_dir_path ) ) {
                throw new Exception( 'Could not create secure directory for encryption key.' );
            }

            // Create .htaccess for security
            $htaccess_content = "Deny from all";
            @file_put_contents( $secure_dir_path . '/.htaccess', $htaccess_content );

            // Create key file
            $key_file_path = $secure_dir_path . '/encryption.key';
            $encryption_key = bin2hex( random_bytes( 32 ) ); // 64 characters hex

            if ( ! @file_put_contents( $key_file_path, $encryption_key ) ) {
                throw new Exception( 'Could not write to encryption key file.' );
            }

            // Store the path to the key file in the database
            update_option( 'wpcw_encryption_key_path', $key_file_path );

            return true;

        } catch ( Exception $e ) {
            error_log( 'WPCW Critical Error: ' . $e->getMessage() );
            return false;
        }
    }
}
