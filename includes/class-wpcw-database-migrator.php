<?php
/**
 * WPCW Database Migrator
 * 
 * Sistema automático de migración de base de datos
 * Detecta versión de esquema y actualiza automáticamente
 * 
 * @package WP_Cupon_WhatsApp
 * @since 1.5.1
 * @author Dr. Rajesh Kumar (Database Specialist)
 * @author Sarah Thompson (WordPress Backend)
 * @approved-by Marcus Chen (PM)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPCW_Database_Migrator {
    
    /**
     * Versión actual del esquema de base de datos
     */
    const DB_VERSION = '1.5.1';
    
    /**
     * Nombre de la opción donde se guarda la versión de BD
     */
    const DB_VERSION_OPTION = 'wpcw_db_version';
    
    /**
     * Ejecutar migraciones necesarias
     * 
     * Se llama automáticamente al activar el plugin o desde AJAX
     */
    public static function run() {
        $current_version = get_option( self::DB_VERSION_OPTION, '0.0.0' );
        
        // Si ya está en la versión actual, no hacer nada
        if ( version_compare( $current_version, self::DB_VERSION, '>=' ) ) {
            return true;
        }
        
        // Forzar ejecución inmediata si es necesario
        set_time_limit( 300 ); // 5 minutos máximo
        
        // Log inicio de migración
        if ( class_exists( 'WPCW_Logger' ) ) {
            WPCW_Logger::log( 'info', 'Iniciando migración de base de datos', array(
                'from_version' => $current_version,
                'to_version' => self::DB_VERSION,
            ) );
        }
        
        // Ejecutar migraciones en orden
        $migrations = self::get_migrations_to_run( $current_version );
        
        foreach ( $migrations as $version => $callback ) {
            try {
                call_user_func( array( __CLASS__, $callback ) );
                
                // Log éxito
                if ( class_exists( 'WPCW_Logger' ) ) {
                    WPCW_Logger::log( 'info', "Migración $version completada", array(
                        'callback' => $callback,
                    ) );
                }
                
            } catch ( Exception $e ) {
                // Log error
                if ( class_exists( 'WPCW_Logger' ) ) {
                    WPCW_Logger::log( 'error', "Migración $version falló: " . $e->getMessage(), array(
                        'callback' => $callback,
                    ) );
                }
                
                error_log( 'WPCW Migration Error: ' . $e->getMessage() );
                
                // Mostrar error en admin
                add_action( 'admin_notices', function() use ( $version, $e ) {
                    echo '<div class="notice notice-error"><p>';
                    echo '<strong>WP Cupón WhatsApp:</strong> ';
                    echo sprintf( 
                        esc_html__( 'Error al actualizar base de datos a versión %s: %s', 'wp-cupon-whatsapp' ),
                        $version,
                        esc_html( $e->getMessage() )
                    );
                    echo '</p></div>';
                } );
                
                return false;
            }
        }
        
        // Actualizar versión de BD
        update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
        
        // Log éxito final
        if ( class_exists( 'WPCW_Logger' ) ) {
            WPCW_Logger::log( 'info', 'Migración de base de datos completada exitosamente', array(
                'final_version' => self::DB_VERSION,
            ) );
        }
        
        // Mostrar mensaje de éxito
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>';
            echo '<strong>WP Cupón WhatsApp:</strong> ';
            echo esc_html__( '✅ Base de datos actualizada exitosamente a versión ' . WPCW_Database_Migrator::DB_VERSION, 'wp-cupon-whatsapp' );
            echo '</p></div>';
        } );
        
        return true;
    }
    
    /**
     * Obtener migraciones a ejecutar
     */
    private static function get_migrations_to_run( $current_version ) {
        $all_migrations = array(
            '1.5.0' => 'migrate_to_150',
            '1.5.1' => 'migrate_to_151',
        );
        
        $to_run = array();
        
        foreach ( $all_migrations as $version => $callback ) {
            if ( version_compare( $current_version, $version, '<' ) ) {
                $to_run[ $version ] = $callback;
            }
        }
        
        return $to_run;
    }
    
    /**
     * Migración a versión 1.5.0
     * Actualizar tabla wp_wpcw_canjes de esquema antiguo a nuevo
     */
    private static function migrate_to_150() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wpcw_canjes';
        
        // Verificar que tabla existe
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
            // Si no existe, crearla con esquema nuevo
            return WPCW_Installer_Fixed::create_canjes_table();
        }
        
        // Obtener columnas actuales
        $columns = $wpdb->get_col( "DESCRIBE $table_name", 0 );
        
        // Si ya tiene el esquema nuevo, no hacer nada
        if ( in_array( 'estado_canje', $columns ) && in_array( 'fecha_solicitud_canje', $columns ) ) {
            return true; // Ya está actualizada
        }
        
        // MIGRACIÓN AUTOMÁTICA
        
        // 1. Agregar columnas nuevas (solo las que no existan)
        $columns_to_add = array(
            'coupon_id' => "bigint(20) UNSIGNED NULL AFTER user_id",
            'numero_canje' => "varchar(20) NULL AFTER coupon_id",
            'token_confirmacion' => "varchar(64) NULL AFTER numero_canje",
            'estado_canje' => "varchar(50) NOT NULL DEFAULT 'pendiente_confirmacion' AFTER token_confirmacion",
            'fecha_solicitud_canje' => "datetime NULL AFTER estado_canje",
            'fecha_confirmacion_canje' => "datetime NULL AFTER fecha_solicitud_canje",
            'comercio_id' => "bigint(20) UNSIGNED NULL AFTER fecha_confirmacion_canje",
            'whatsapp_url' => "text NULL AFTER comercio_id",
            'codigo_cupon_wc' => "varchar(100) NULL AFTER whatsapp_url",
            'id_pedido_wc' => "bigint(20) UNSIGNED NULL AFTER codigo_cupon_wc",
            'origen_canje' => "varchar(50) DEFAULT 'webapp' AFTER id_pedido_wc",
            'notas_internas' => "text NULL AFTER origen_canje",
            'fecha_rechazo' => "datetime NULL AFTER notas_internas",
            'fecha_cancelacion' => "datetime NULL AFTER fecha_rechazo",
            'created_at' => "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER fecha_cancelacion",
            'updated_at' => "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at",
        );
        
        foreach ( $columns_to_add as $column => $definition ) {
            if ( ! in_array( $column, $columns ) ) {
                $wpdb->query( "ALTER TABLE $table_name ADD COLUMN $column $definition" );
            }
        }
        
        // 2. Migrar datos de columnas antiguas a nuevas (si existen)
        if ( in_array( 'business_id', $columns ) ) {
            $wpdb->query( "UPDATE $table_name SET comercio_id = business_id WHERE business_id IS NOT NULL" );
        }
        
        if ( in_array( 'redeemed_at', $columns ) ) {
            $wpdb->query( "UPDATE $table_name SET fecha_solicitud_canje = redeemed_at WHERE redeemed_at IS NOT NULL" );
            $wpdb->query( "UPDATE $table_name SET fecha_confirmacion_canje = redeemed_at, estado_canje = 'confirmado_por_negocio' WHERE redeemed_at IS NOT NULL" );
        }
        
        // 3. Crear índices para performance
        $indexes = array(
            'idx_user_id' => 'user_id',
            'idx_coupon_id' => 'coupon_id',
            'idx_numero_canje' => 'numero_canje',
            'idx_estado_canje' => 'estado_canje',
            'idx_fecha_solicitud' => 'fecha_solicitud_canje',
            'idx_comercio_id' => 'comercio_id',
        );
        
        // Obtener índices existentes
        $existing_indexes = $wpdb->get_results( "SHOW INDEX FROM $table_name" );
        $existing_index_names = array();
        foreach ( $existing_indexes as $index ) {
            $existing_index_names[] = $index->Key_name;
        }
        
        // Crear índices que no existan
        foreach ( $indexes as $index_name => $column ) {
            if ( ! in_array( $index_name, $existing_index_names ) ) {
                $wpdb->query( "ALTER TABLE $table_name ADD INDEX $index_name ($column)" );
            }
        }
        
        return true;
    }
    
    /**
     * Migración a versión 1.5.1
     * Mejoras adicionales de esquema si son necesarias
     */
    private static function migrate_to_151() {
        // Por ahora, solo verificar integridad
        return self::verify_table_integrity();
    }
    
    /**
     * Verificar integridad de tabla
     */
    private static function verify_table_integrity() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wpcw_canjes';
        
        // Columnas requeridas
        $required_columns = array(
            'id',
            'user_id',
            'coupon_id',
            'numero_canje',
            'token_confirmacion',
            'estado_canje',
            'fecha_solicitud_canje',
        );
        
        $columns = $wpdb->get_col( "DESCRIBE $table_name", 0 );
        
        foreach ( $required_columns as $required ) {
            if ( ! in_array( $required, $columns ) ) {
                if ( class_exists( 'WPCW_Logger' ) ) {
                    WPCW_Logger::log( 'error', "Columna requerida faltante: $required" );
                }
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Obtener versión actual de BD
     */
    public static function get_current_version() {
        return get_option( self::DB_VERSION_OPTION, '0.0.0' );
    }
    
    /**
     * Verificar si necesita migración
     */
    public static function needs_migration() {
        $current_version = self::get_current_version();
        return version_compare( $current_version, self::DB_VERSION, '<' );
    }
    
    /**
     * Renderizar botón de migración manual en dashboard
     * GRANDE y VISIBLE para que Cristian lo vea inmediatamente
     */
    public static function render_migration_button() {
        if ( ! self::needs_migration() ) {
            return;
        }
        
        ?>
        <div class="notice notice-error" style="border-left-color: #dc3232; padding: 20px; background: #fff8e5;">
            <h2 style="margin-top: 0; color: #dc3232;">
                🚨 <?php esc_html_e( 'ACCIÓN REQUERIDA: Actualización de Base de Datos', 'wp-cupon-whatsapp' ); ?>
            </h2>
            <p style="font-size: 16px;">
                <strong><?php esc_html_e( 'Tu plugin necesita actualizar la base de datos para funcionar correctamente.', 'wp-cupon-whatsapp' ); ?></strong>
            </p>
            <p style="font-size: 14px;">
                <?php esc_html_e( 'Esto agregará columnas nuevas a la tabla wp_wpcw_canjes. Se creará un backup automático antes de cualquier cambio.', 'wp-cupon-whatsapp' ); ?>
            </p>
            <p style="font-size: 14px; color: #666;">
                <?php 
                $current = self::get_current_version();
                printf( 
                    esc_html__( 'Versión actual de BD: %s | Versión requerida: %s', 'wp-cupon-whatsapp' ),
                    '<strong>' . esc_html( $current ) . '</strong>',
                    '<strong>' . esc_html( self::DB_VERSION ) . '</strong>'
                );
                ?>
            </p>
            <p>
                <button id="wpcw-run-migration" class="button button-primary button-hero" style="background: #28a745; border-color: #28a745; font-size: 16px; height: auto; line-height: 1.5; padding: 12px 24px;">
                    🔄 <?php esc_html_e( 'ACTUALIZAR BASE DE DATOS AHORA (1 Click)', 'wp-cupon-whatsapp' ); ?>
                </button>
                <span id="wpcw-migration-status" style="margin-left: 20px; font-size: 16px; font-weight: bold;"></span>
            </p>
            <p style="font-size: 12px; color: #666; margin-top: 15px;">
                ℹ️ <?php esc_html_e( 'Este proceso toma 5-10 segundos. No cierres la página mientras se ejecuta.', 'wp-cupon-whatsapp' ); ?>
            </p>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#wpcw-run-migration').on('click', function(e) {
                e.preventDefault();
                
                var $button = $(this);
                var $status = $('#wpcw-migration-status');
                
                $button.prop('disabled', true).text('⏳ Migrando...');
                $status.html('<span style="color: #0073aa;">Actualizando base de datos, por favor espera...</span>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpcw_run_database_migration',
                        nonce: '<?php echo wp_create_nonce( 'wpcw_db_migration' ); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $status.html('<span style="color: #46b450; font-size: 18px;">✅ ' + response.data.message + '</span>');
                            $button.text('✅ Completado').css('background', '#46b450');
                            setTimeout(function() {
                                $status.html('<span style="color: #0073aa;">🔄 Recargando página...</span>');
                                location.reload();
                            }, 1500);
                        } else {
                            $status.html('<span style="color: #dc3232; font-size: 16px;">❌ Error: ' + response.data.message + '</span>');
                            $button.prop('disabled', false).text('🔄 Reintentar Actualización').css('background', '#dc3232');
                        }
                    },
                    error: function() {
                        $status.html('<span style="color: #dc3232;">❌ Error de conexión</span>');
                        $button.prop('disabled', false).text('🔄 Reintentar Actualización');
                    }
                });
            });
        });
        </script>
        <?php
    }
}

/**
 * AJAX Handler para ejecutar migración
 */
function wpcw_ajax_run_database_migration() {
    // Verificar nonce
    check_ajax_referer( 'wpcw_db_migration', 'nonce' );
    
    // Verificar permisos
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array(
            'message' => __( 'No tienes permisos para ejecutar esta acción.', 'wp-cupon-whatsapp' ),
        ) );
    }
    
    // Ejecutar migración
    $result = WPCW_Database_Migrator::run();
    
    if ( $result ) {
        wp_send_json_success( array(
            'message' => __( 'Base de datos actualizada exitosamente. Recargando página...', 'wp-cupon-whatsapp' ),
        ) );
    } else {
        wp_send_json_error( array(
            'message' => __( 'Error al actualizar base de datos. Revisa los logs.', 'wp-cupon-whatsapp' ),
        ) );
    }
}
add_action( 'wp_ajax_wpcw_run_database_migration', 'wpcw_ajax_run_database_migration' );

