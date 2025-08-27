<?php
/**
 * Comandos WP-CLI para WP Cupón WhatsApp
 *
 * Proporciona comandos de línea de comandos para gestión masiva
 *
 * @package WP_Cupon_WhatsApp
 * @subpackage CLI
 * @since 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Comandos WP-CLI para gestión masiva del plugin
 */
class WPCW_CLI_Commands {

    /**
     * Instala y configura el plugin automáticamente
     *
     * ## OPTIONS
     *
     * [--profile=<profile>]
     * : Perfil de instalación (minimal|standard|enterprise|development)
     * ---
     * default: standard
     * options:
     *   - minimal
     *   - standard
     *   - enterprise
     *   - development
     * ---
     *
     * [--optimization=<level>]
     * : Nivel de optimización (none|basic|standard|aggressive)
     * ---
     * default: standard
     * options:
     *   - none
     *   - basic
     *   - standard
     *   - aggressive
     * ---
     *
     * [--region=<region>]
     * : Código de región (AR|MX|CO|ES|US|BR)
     *
     * [--force]
     * : Forzar reinstalación
     *
     * ## EXAMPLES
     *
     *     wp wpcw install
     *     wp wpcw install --profile=enterprise --optimization=aggressive
     *     wp wpcw install --region=AR --force
     *
     * @when after_wp_load
     */
    public function install( $args, $assoc_args ) {
        $profile      = WP_CLI\Utils\get_flag_value( $assoc_args, 'profile', 'standard' );
        $optimization = WP_CLI\Utils\get_flag_value( $assoc_args, 'optimization', 'standard' );
        $region       = WP_CLI\Utils\get_flag_value( $assoc_args, 'region', '' );
        $force        = WP_CLI\Utils\get_flag_value( $assoc_args, 'force', false );

        WP_CLI::log( 'Iniciando instalación automática de WP Cupón WhatsApp...' );

        // Verificar si ya está instalado
        if ( get_option( 'wpcw_installation_completed' ) && ! $force ) {
            WP_CLI::warning( 'El plugin ya está instalado. Use --force para reinstalar.' );
            return;
        }

        try {
            // Ejecutar instalación automática
            if ( class_exists( 'WPCW_Auto_Installer' ) ) {
                $installer = WPCW_Auto_Installer::get_instance();

                WP_CLI::log( "Aplicando perfil de instalación: {$profile}" );
                $result = $installer->auto_install( $profile );

                if ( ! $result['success'] ) {
                    WP_CLI::error( 'Error en instalación automática: ' . $result['message'] );
                    return;
                }

                WP_CLI::success( 'Instalación automática completada.' );
            } else {
                WP_CLI::error( 'Clase WPCW_Auto_Installer no disponible.' );
                return;
            }

            // Configurar región si se especifica
            if ( $region ) {
                WP_CLI::log( "Configurando región: {$region}" );
                if ( class_exists( 'WPCW_Auto_Config' ) ) {
                    $config        = WPCW_Auto_Config::get_instance();
                    $config_result = $config->apply_regional_config( $region );

                    if ( $config_result['success'] ) {
                        WP_CLI::success( "Configuración regional aplicada: {$region}" );
                    } else {
                        WP_CLI::warning( 'Error al aplicar configuración regional: ' . $config_result['message'] );
                    }
                }
            }

            // Aplicar optimización
            if ( $optimization !== 'none' ) {
                WP_CLI::log( "Aplicando optimización: {$optimization}" );
                if ( class_exists( 'WPCW_Performance_Optimizer' ) ) {
                    $optimizer  = WPCW_Performance_Optimizer::get_instance();
                    $opt_result = $optimizer->apply_optimization_profile( $optimization );

                    if ( $opt_result['success'] ) {
                        WP_CLI::success( "Optimización aplicada: {$optimization}" );
                    } else {
                        WP_CLI::warning( 'Error al aplicar optimización: ' . $opt_result['message'] );
                    }
                }
            }

            WP_CLI::success( '🎉 Instalación completada exitosamente!' );

        } catch ( Exception $e ) {
            WP_CLI::error( 'Error durante la instalación: ' . $e->getMessage() );
        }
    }

    /**
     * Muestra el estado actual del plugin
     *
     * [--format=<format>]
     * : Formato de salida
     * ---
     * default: table
     * options:
     *   - table
     *   - json
     *   - yaml
     *   - csv
     * ---
     *
     * ## EXAMPLES
     *
     *     wp wpcw status
     *     wp wpcw status --format=json
     *
     * @when after_wp_load
     */
    public function status( $args, $assoc_args ) {
        $format = WP_CLI\Utils\get_flag_value( $assoc_args, 'format', 'table' );

        $status_data = array();

        // Estado de instalación
        $status_data[] = array(
            'component' => 'Instalación',
            'status'    => get_option( 'wpcw_installation_completed' ) ? '✅ Completada' : '❌ Incompleta',
            'details'   => get_option( 'wpcw_installation_date', 'N/A' ),
        );

        // Versión del plugin
        $status_data[] = array(
            'component' => 'Versión',
            'status'    => get_option( 'wpcw_version', 'Desconocida' ),
            'details'   => 'Plugin WP Cupón WhatsApp',
        );

        // Configuración regional
        $region        = get_option( 'wpcw_detected_region', 'No detectada' );
        $status_data[] = array(
            'component' => 'Región',
            'status'    => $region,
            'details'   => get_option( 'wpcw_region_confidence', 'N/A' ) . '% confianza',
        );

        // Estado de optimización
        $optimization  = get_option( 'wpcw_optimization_profile', 'Ninguna' );
        $status_data[] = array(
            'component' => 'Optimización',
            'status'    => $optimization,
            'details'   => get_option( 'wpcw_optimization_applied_date', 'N/A' ),
        );

        // Estado del cache
        $cache_enabled = get_option( 'wpcw_cache_enabled', false );
        $status_data[] = array(
            'component' => 'Cache',
            'status'    => $cache_enabled ? '✅ Habilitado' : '❌ Deshabilitado',
            'details'   => get_option( 'wpcw_cache_type', 'N/A' ),
        );

        // Logs centralizados
        $logging_enabled = get_option( 'wpcw_centralized_logging', false );
        $status_data[]   = array(
            'component' => 'Logs Centralizados',
            'status'    => $logging_enabled ? '✅ Habilitado' : '❌ Deshabilitado',
            'details'   => get_option( 'wpcw_log_level', 'N/A' ),
        );

        // Backups
        $backup_enabled = get_option( 'wpcw_backup_enabled', false );
        $status_data[]  = array(
            'component' => 'Backups',
            'status'    => $backup_enabled ? '✅ Habilitado' : '❌ Deshabilitado',
            'details'   => get_option( 'wpcw_last_backup_date', 'Nunca' ),
        );

        WP_CLI\Utils\format_items( $format, $status_data, array( 'component', 'status', 'details' ) );
    }

    /**
     * Configura el plugin con opciones específicas
     *
     * ## OPTIONS
     *
     * <setting>
     * : Nombre de la configuración
     *
     * <value>
     * : Valor de la configuración
     *
     * [--region=<region>]
     * : Aplicar configuración regional
     *
     * ## EXAMPLES
     *
     *     wp wpcw configure phone_validation enhanced
     *     wp wpcw configure --region=AR
     *
     * @when after_wp_load
     */
    public function configure( $args, $assoc_args ) {
        $region = WP_CLI\Utils\get_flag_value( $assoc_args, 'region', '' );

        if ( $region ) {
            // Aplicar configuración regional
            WP_CLI::log( "Aplicando configuración regional: {$region}" );

            if ( class_exists( 'WPCW_Auto_Config' ) ) {
                $config = WPCW_Auto_Config::get_instance();
                $result = $config->apply_regional_config( $region );

                if ( $result['success'] ) {
                    WP_CLI::success( "Configuración regional aplicada: {$region}" );
                } else {
                    WP_CLI::error( 'Error al aplicar configuración regional: ' . $result['message'] );
                }
            } else {
                WP_CLI::error( 'Clase WPCW_Auto_Config no disponible.' );
            }
            return;
        }

        if ( count( $args ) < 2 ) {
            WP_CLI::error( 'Debe especificar configuración y valor.' );
            return;
        }

        $setting = $args[0];
        $value   = $args[1];

        // Validar configuración
        $valid_settings = array(
            'phone_validation' => array( 'basic', 'enhanced', 'advanced' ),
            'email_validation' => array( 'basic', 'enhanced', 'advanced' ),
            'cache_enabled'    => array( 'true', 'false', '1', '0' ),
            'debug_mode'       => array( 'true', 'false', '1', '0' ),
            'auto_updates'     => array( 'true', 'false', '1', '0' ),
        );

        if ( ! isset( $valid_settings[ $setting ] ) ) {
            WP_CLI::error( "Configuración no válida: {$setting}" );
            return;
        }

        if ( ! in_array( $value, $valid_settings[ $setting ] ) ) {
            WP_CLI::error( "Valor no válido para {$setting}: {$value}" );
            return;
        }

        // Aplicar configuración
        $option_name = 'wpcw_' . $setting;

        // Convertir valores booleanos
        if ( in_array( $value, array( 'true', '1' ) ) ) {
            $value = true;
        } elseif ( in_array( $value, array( 'false', '0' ) ) ) {
            $value = false;
        }

        update_option( $option_name, $value );

        WP_CLI::success( "Configuración actualizada: {$setting} = {$value}" );
    }

    /**
     * Optimiza el rendimiento del plugin
     *
     * [--level=<level>]
     * : Nivel de optimización
     * ---
     * default: standard
     * options:
     *   - basic
     *   - standard
     *   - aggressive
     * ---
     *
     * [--clear-cache]
     * : Limpiar cache después de optimizar
     *
     * ## EXAMPLES
     *
     *     wp wpcw optimize
     *     wp wpcw optimize --level=aggressive --clear-cache
     *
     * @when after_wp_load
     */
    public function optimize( $args, $assoc_args ) {
        $level       = WP_CLI\Utils\get_flag_value( $assoc_args, 'level', 'standard' );
        $clear_cache = WP_CLI\Utils\get_flag_value( $assoc_args, 'clear-cache', false );

        WP_CLI::log( "Aplicando optimización nivel: {$level}" );

        if ( class_exists( 'WPCW_Performance_Optimizer' ) ) {
            $optimizer = WPCW_Performance_Optimizer::get_instance();
            $result    = $optimizer->apply_optimization_profile( $level );

            if ( $result['success'] ) {
                WP_CLI::success( "Optimización aplicada: {$level}" );

                // Mostrar métricas si están disponibles
                $metrics = $optimizer->get_performance_metrics();
                if ( $metrics ) {
                    WP_CLI::log( 'Métricas de rendimiento:' );
                    foreach ( $metrics as $metric => $value ) {
                        WP_CLI::log( "  {$metric}: {$value}" );
                    }
                }
            } else {
                WP_CLI::error( 'Error al aplicar optimización: ' . $result['message'] );
                return;
            }
        } else {
            WP_CLI::error( 'Clase WPCW_Performance_Optimizer no disponible.' );
            return;
        }

        // Limpiar cache si se solicita
        if ( $clear_cache ) {
            WP_CLI::log( 'Limpiando cache...' );
            $cache_result = $optimizer->clear_all_cache();

            if ( $cache_result['success'] ) {
                WP_CLI::success( 'Cache limpiado exitosamente.' );
            } else {
                WP_CLI::warning( 'Error al limpiar cache: ' . $cache_result['message'] );
            }
        }
    }

    /**
     * Gestiona backups del plugin
     *
     * ## OPTIONS
     *
     * <action>
     * : Acción a realizar
     * ---
     * options:
     *   - create
     *   - restore
     *   - list
     *   - delete
     * ---
     *
     * [--backup-id=<id>]
     * : ID del backup (para restore/delete)
     *
     * [--description=<desc>]
     * : Descripción del backup
     *
     * ## EXAMPLES
     *
     *     wp wpcw backup create
     *     wp wpcw backup create --description="Antes de actualización"
     *     wp wpcw backup list
     *     wp wpcw backup restore --backup-id=123
     *
     * @when after_wp_load
     */
    public function backup( $args, $assoc_args ) {
        if ( empty( $args ) ) {
            WP_CLI::error( 'Debe especificar una acción: create, restore, list, delete' );
            return;
        }

        $action      = $args[0];
        $backup_id   = WP_CLI\Utils\get_flag_value( $assoc_args, 'backup-id', '' );
        $description = WP_CLI\Utils\get_flag_value( $assoc_args, 'description', '' );

        if ( ! class_exists( 'WPCW_Migration_Tools' ) ) {
            WP_CLI::error( 'Clase WPCW_Migration_Tools no disponible.' );
            return;
        }

        $migration_tools = WPCW_Migration_Tools::get_instance();

        switch ( $action ) {
            case 'create':
                WP_CLI::log( 'Creando backup...' );
                $result = $migration_tools->create_backup( $description );

                if ( $result['success'] ) {
                    WP_CLI::success( "Backup creado exitosamente. ID: {$result['backup_id']}" );
                } else {
                    WP_CLI::error( 'Error al crear backup: ' . $result['message'] );
                }
                break;

            case 'restore':
                if ( ! $backup_id ) {
                    WP_CLI::error( 'Debe especificar --backup-id para restaurar.' );
                    return;
                }

                WP_CLI::log( "Restaurando backup ID: {$backup_id}" );
                $result = $migration_tools->restore_backup( $backup_id );

                if ( $result['success'] ) {
                    WP_CLI::success( 'Backup restaurado exitosamente.' );
                } else {
                    WP_CLI::error( 'Error al restaurar backup: ' . $result['message'] );
                }
                break;

            case 'list':
                $backups = $migration_tools->list_backups();

                if ( empty( $backups ) ) {
                    WP_CLI::log( 'No hay backups disponibles.' );
                    return;
                }

                $backup_data = array();
                foreach ( $backups as $backup ) {
                    $backup_data[] = array(
                        'id'          => $backup['id'],
                        'date'        => $backup['date'],
                        'description' => $backup['description'],
                        'size'        => $backup['size'],
                    );
                }

                WP_CLI\Utils\format_items( 'table', $backup_data, array( 'id', 'date', 'description', 'size' ) );
                break;

            case 'delete':
                if ( ! $backup_id ) {
                    WP_CLI::error( 'Debe especificar --backup-id para eliminar.' );
                    return;
                }

                WP_CLI::log( "Eliminando backup ID: {$backup_id}" );
                $result = $migration_tools->delete_backup( $backup_id );

                if ( $result['success'] ) {
                    WP_CLI::success( 'Backup eliminado exitosamente.' );
                } else {
                    WP_CLI::error( 'Error al eliminar backup: ' . $result['message'] );
                }
                break;

            default:
                WP_CLI::error( "Acción no válida: {$action}" );
        }
    }

    /**
     * Gestiona logs del plugin
     *
     * ## OPTIONS
     *
     * <action>
     * : Acción a realizar
     * ---
     * options:
     *   - view
     *   - clear
     *   - export
     *   - stats
     * ---
     *
     * [--level=<level>]
     * : Nivel de log a mostrar
     * ---
     * options:
     *   - debug
     *   - info
     *   - warning
     *   - error
     * ---
     *
     * [--lines=<number>]
     * : Número de líneas a mostrar
     * ---
     * default: 50
     * ---
     *
     * ## EXAMPLES
     *
     *     wp wpcw logs view
     *     wp wpcw logs view --level=error --lines=100
     *     wp wpcw logs clear
     *     wp wpcw logs stats
     *
     * @when after_wp_load
     */
    public function logs( $args, $assoc_args ) {
        if ( empty( $args ) ) {
            WP_CLI::error( 'Debe especificar una acción: view, clear, export, stats' );
            return;
        }

        $action = $args[0];
        $level  = WP_CLI\Utils\get_flag_value( $assoc_args, 'level', '' );
        $lines  = WP_CLI\Utils\get_flag_value( $assoc_args, 'lines', 50 );

        if ( ! class_exists( 'WPCW_Centralized_Logger' ) ) {
            WP_CLI::error( 'Clase WPCW_Centralized_Logger no disponible.' );
            return;
        }

        $logger = WPCW_Centralized_Logger::get_instance();

        switch ( $action ) {
            case 'view':
                $logs = $logger->get_logs( $level, $lines );

                if ( empty( $logs ) ) {
                    WP_CLI::log( 'No hay logs disponibles.' );
                    return;
                }

                foreach ( $logs as $log ) {
                    $color = $this->get_log_color( $log['level'] );
                    WP_CLI::line(
                        WP_CLI::colorize( "%{$color}[{$log['timestamp']}] [{$log['level']}] {$log['message']}%n" )
                    );
                }
                break;

            case 'clear':
                $result = $logger->clear_logs();

                if ( $result['success'] ) {
                    WP_CLI::success( 'Logs limpiados exitosamente.' );
                } else {
                    WP_CLI::error( 'Error al limpiar logs: ' . $result['message'] );
                }
                break;

            case 'export':
                $export_file = 'wpcw-logs-' . date( 'Y-m-d-H-i-s' ) . '.txt';
                $result      = $logger->export_logs( $export_file );

                if ( $result['success'] ) {
                    WP_CLI::success( "Logs exportados a: {$export_file}" );
                } else {
                    WP_CLI::error( 'Error al exportar logs: ' . $result['message'] );
                }
                break;

            case 'stats':
                $stats = $logger->get_log_statistics();

                WP_CLI::log( 'Estadísticas de logs:' );
                foreach ( $stats as $stat => $value ) {
                    WP_CLI::log( "  {$stat}: {$value}" );
                }
                break;

            default:
                WP_CLI::error( "Acción no válida: {$action}" );
        }
    }

    /**
     * Obtiene el color para el nivel de log
     */
    private function get_log_color( $level ) {
        switch ( strtolower( $level ) ) {
            case 'error':
                return 'R';
            case 'warning':
                return 'Y';
            case 'info':
                return 'G';
            case 'debug':
                return 'B';
            default:
                return 'W';
        }
    }
}

// Registrar comandos WP-CLI
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    WP_CLI::add_command( 'wpcw', 'WPCW_CLI_Commands' );
}
