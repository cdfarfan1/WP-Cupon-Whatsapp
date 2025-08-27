#!/bin/bash

# Script de Despliegue Masivo - WP Cupón WhatsApp v1.4.0
# Automatiza la instalación y configuración en múltiples sitios WordPress

set -e  # Salir si hay errores

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuración por defecto
DEFAULT_PROFILE="standard"
DEFAULT_OPTIMIZATION="standard"
PLUGIN_SLUG="wp-cupon-whatsapp"
LOG_FILE="deployment-$(date +%Y%m%d-%H%M%S).log"

# Función para mostrar ayuda
show_help() {
    echo -e "${BLUE}WP Cupón WhatsApp - Script de Despliegue Masivo${NC}"
    echo -e "${BLUE}================================================${NC}"
    echo ""
    echo "Uso: $0 [OPCIONES] [ARCHIVO_SITIOS]"
    echo ""
    echo "OPCIONES:"
    echo "  -p, --profile PROFILE        Perfil de instalación (minimal|standard|enterprise|development)"
    echo "  -o, --optimize LEVEL          Nivel de optimización (basic|standard|aggressive)"
    echo "  -c, --config FILE             Archivo de configuración personalizada"
    echo "  -t, --test                    Modo de prueba (no ejecuta cambios reales)"
    echo "  -v, --verbose                 Salida detallada"
    echo "  -h, --help                    Mostrar esta ayuda"
    echo ""
    echo "ARCHIVO_SITIOS:"
    echo "  Archivo de texto con URLs de sitios, uno por línea"
    echo "  Formato: https://sitio.com [perfil] [optimización]"
    echo ""
    echo "EJEMPLOS:"
    echo "  $0 sitios.txt"
    echo "  $0 -p enterprise -o aggressive sitios.txt"
    echo "  $0 --test --verbose sitios.txt"
    echo ""
}

# Función para logging
log() {
    local level=$1
    shift
    local message="$@"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    
    case $level in
        "INFO")
            echo -e "${GREEN}[INFO]${NC} $message" | tee -a "$LOG_FILE"
            ;;
        "WARN")
            echo -e "${YELLOW}[WARN]${NC} $message" | tee -a "$LOG_FILE"
            ;;
        "ERROR")
            echo -e "${RED}[ERROR]${NC} $message" | tee -a "$LOG_FILE"
            ;;
        "DEBUG")
            if [[ $VERBOSE == true ]]; then
                echo -e "${BLUE}[DEBUG]${NC} $message" | tee -a "$LOG_FILE"
            fi
            ;;
    esac
    
    echo "[$timestamp] [$level] $message" >> "$LOG_FILE"
}

# Función para verificar dependencias
check_dependencies() {
    log "INFO" "Verificando dependencias..."
    
    # Verificar WP-CLI
    if ! command -v wp &> /dev/null; then
        log "ERROR" "WP-CLI no está instalado. Instálalo desde https://wp-cli.org/"
        exit 1
    fi
    
    # Verificar curl
    if ! command -v curl &> /dev/null; then
        log "ERROR" "curl no está instalado"
        exit 1
    fi
    
    # Verificar jq para procesamiento JSON
    if ! command -v jq &> /dev/null; then
        log "WARN" "jq no está instalado. Algunas funciones avanzadas no estarán disponibles"
    fi
    
    log "INFO" "✅ Dependencias verificadas"
}

# Función para validar sitio
validate_site() {
    local site_url=$1
    
    log "DEBUG" "Validando sitio: $site_url"
    
    # Verificar que el sitio responde
    if ! curl -s --head "$site_url" | head -n 1 | grep -q "200 OK"; then
        log "WARN" "El sitio $site_url no responde correctamente"
        return 1
    fi
    
    # Verificar que es un sitio WordPress
    if ! wp --url="$site_url" core version &> /dev/null; then
        log "WARN" "$site_url no parece ser un sitio WordPress válido"
        return 1
    fi
    
    return 0
}

# Función para instalar plugin en un sitio
install_plugin_on_site() {
    local site_url=$1
    local profile=$2
    local optimization=$3
    
    log "INFO" "🚀 Instalando en $site_url (Perfil: $profile, Optimización: $optimization)"
    
    if [[ $TEST_MODE == true ]]; then
        log "INFO" "[MODO PRUEBA] Simulando instalación en $site_url"
        return 0
    fi
    
    # Verificar si el plugin ya está instalado
    if wp --url="$site_url" plugin is-installed "$PLUGIN_SLUG" 2>/dev/null; then
        log "INFO" "Plugin ya está instalado en $site_url, actualizando..."
        wp --url="$site_url" plugin update "$PLUGIN_SLUG" || {
            log "ERROR" "Error al actualizar plugin en $site_url"
            return 1
        }
    else
        # Instalar plugin
        log "DEBUG" "Instalando plugin en $site_url"
        wp --url="$site_url" plugin install "$PLUGIN_SLUG" --activate || {
            log "ERROR" "Error al instalar plugin en $site_url"
            return 1
        }
    fi
    
    # Ejecutar instalación automática
    log "DEBUG" "Ejecutando instalación automática con perfil $profile"
    wp --url="$site_url" eval "
        if (class_exists('WPCW_Auto_Installer')) {
            \$installer = WPCW_Auto_Installer::get_instance();
            \$result = \$installer->auto_install('$profile');
            if (\$result['success']) {
                echo 'Instalación completada exitosamente';
            } else {
                echo 'Error en instalación: ' . \$result['message'];
                exit(1);
            }
        } else {
            echo 'Clase WPCW_Auto_Installer no disponible';
            exit(1);
        }
    " || {
        log "ERROR" "Error en instalación automática en $site_url"
        return 1
    }
    
    # Aplicar optimización
    if [[ $optimization != "none" ]]; then
        log "DEBUG" "Aplicando optimización $optimization"
        wp --url="$site_url" eval "
            if (class_exists('WPCW_Performance_Optimizer')) {
                \$optimizer = WPCW_Performance_Optimizer::get_instance();
                \$result = \$optimizer->apply_optimization_profile('$optimization');
                if (\$result['success']) {
                    echo 'Optimización aplicada exitosamente';
                } else {
                    echo 'Error en optimización: ' . \$result['message'];
                }
            }
        " || {
            log "WARN" "Error al aplicar optimización en $site_url"
        }
    fi
    
    # Verificar instalación
    local status=$(wp --url="$site_url" eval "
        if (get_option('wpcw_installation_completed')) {
            echo 'completed';
        } else {
            echo 'incomplete';
        }
    ")
    
    if [[ $status == "completed" ]]; then
        log "INFO" "✅ Instalación completada exitosamente en $site_url"
        return 0
    else
        log "ERROR" "❌ Instalación incompleta en $site_url"
        return 1
    fi
}

# Función para procesar archivo de sitios
process_sites_file() {
    local sites_file=$1
    
    if [[ ! -f $sites_file ]]; then
        log "ERROR" "Archivo de sitios no encontrado: $sites_file"
        exit 1
    fi
    
    local total_sites=0
    local successful_sites=0
    local failed_sites=0
    
    # Contar total de sitios
    total_sites=$(grep -v '^#' "$sites_file" | grep -v '^$' | wc -l)
    log "INFO" "Procesando $total_sites sitios..."
    
    # Procesar cada sitio
    while IFS= read -r line; do
        # Ignorar comentarios y líneas vacías
        [[ $line =~ ^#.*$ ]] && continue
        [[ -z $line ]] && continue
        
        # Parsear línea: URL [perfil] [optimización]
        read -r site_url site_profile site_optimization <<< "$line"
        
        # Usar valores por defecto si no se especifican
        site_profile=${site_profile:-$DEFAULT_PROFILE}
        site_optimization=${site_optimization:-$DEFAULT_OPTIMIZATION}
        
        log "INFO" "Procesando sitio $((successful_sites + failed_sites + 1))/$total_sites: $site_url"
        
        # Validar sitio
        if ! validate_site "$site_url"; then
            log "ERROR" "Sitio no válido, saltando: $site_url"
            ((failed_sites++))
            continue
        fi
        
        # Instalar plugin
        if install_plugin_on_site "$site_url" "$site_profile" "$site_optimization"; then
            ((successful_sites++))
        else
            ((failed_sites++))
        fi
        
        # Pausa entre sitios para evitar sobrecarga
        sleep 2
        
    done < "$sites_file"
    
    # Resumen final
    log "INFO" "=== RESUMEN DEL DESPLIEGUE ==="
    log "INFO" "Total de sitios: $total_sites"
    log "INFO" "Exitosos: $successful_sites"
    log "INFO" "Fallidos: $failed_sites"
    log "INFO" "Tasa de éxito: $(( successful_sites * 100 / total_sites ))%"
    
    if [[ $failed_sites -gt 0 ]]; then
        log "WARN" "Algunos sitios fallaron. Revisa el log: $LOG_FILE"
        return 1
    fi
    
    return 0
}

# Función para crear archivo de ejemplo
create_example_sites_file() {
    local example_file="sitios-ejemplo.txt"
    
    cat > "$example_file" << EOF
# Archivo de ejemplo para despliegue masivo
# Formato: URL [perfil] [optimización]
# 
# Perfiles disponibles: minimal, standard, enterprise, development
# Optimizaciones disponibles: basic, standard, aggressive, none
#
# Ejemplos:
https://sitio1.com standard standard
https://sitio2.com enterprise aggressive
https://sitio3.com minimal basic
https://sitio4.com  # Usará valores por defecto

# Sitios de desarrollo
https://dev.sitio1.com development none
https://staging.sitio2.com standard basic
EOF
    
    log "INFO" "Archivo de ejemplo creado: $example_file"
}

# Función para generar reporte
generate_report() {
    local report_file="deployment-report-$(date +%Y%m%d-%H%M%S).html"
    
    cat > "$report_file" << EOF
<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Despliegue Masivo - WP Cupón WhatsApp</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: #0073aa; color: white; padding: 20px; border-radius: 5px; }
        .success { color: #46b450; }
        .error { color: #dc3232; }
        .warning { color: #ffb900; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Despliegue Masivo</h1>
        <p>WP Cupón WhatsApp v1.4.0 - $(date)</p>
    </div>
    
    <h2>Resumen</h2>
    <p>Log completo disponible en: <code>$LOG_FILE</code></p>
    
    <h2>Configuración Utilizada</h2>
    <ul>
        <li><strong>Perfil por defecto:</strong> $DEFAULT_PROFILE</li>
        <li><strong>Optimización por defecto:</strong> $DEFAULT_OPTIMIZATION</li>
        <li><strong>Modo de prueba:</strong> $([ "$TEST_MODE" = true ] && echo "Sí" || echo "No")</li>
    </ul>
    
    <h2>Detalles</h2>
    <p>Para ver los detalles completos, consulta el archivo de log: <code>$LOG_FILE</code></p>
</body>
</html>
EOF
    
    log "INFO" "Reporte HTML generado: $report_file"
}

# Función principal
main() {
    # Variables por defecto
    PROFILE="$DEFAULT_PROFILE"
    OPTIMIZATION="$DEFAULT_OPTIMIZATION"
    CONFIG_FILE=""
    TEST_MODE=false
    VERBOSE=false
    SITES_FILE=""
    
    # Parsear argumentos
    while [[ $# -gt 0 ]]; do
        case $1 in
            -p|--profile)
                PROFILE="$2"
                shift 2
                ;;
            -o|--optimize)
                OPTIMIZATION="$2"
                shift 2
                ;;
            -c|--config)
                CONFIG_FILE="$2"
                shift 2
                ;;
            -t|--test)
                TEST_MODE=true
                shift
                ;;
            -v|--verbose)
                VERBOSE=true
                shift
                ;;
            -h|--help)
                show_help
                exit 0
                ;;
            --example)
                create_example_sites_file
                exit 0
                ;;
            -*)
                log "ERROR" "Opción desconocida: $1"
                show_help
                exit 1
                ;;
            *)
                SITES_FILE="$1"
                shift
                ;;
        esac
    done
    
    # Verificar que se proporcionó archivo de sitios
    if [[ -z $SITES_FILE ]]; then
        log "ERROR" "Debe especificar un archivo de sitios"
        show_help
        exit 1
    fi
    
    # Actualizar valores por defecto
    DEFAULT_PROFILE="$PROFILE"
    DEFAULT_OPTIMIZATION="$OPTIMIZATION"
    
    # Mostrar configuración
    log "INFO" "=== CONFIGURACIÓN DE DESPLIEGUE ==="
    log "INFO" "Perfil por defecto: $DEFAULT_PROFILE"
    log "INFO" "Optimización por defecto: $DEFAULT_OPTIMIZATION"
    log "INFO" "Archivo de sitios: $SITES_FILE"
    log "INFO" "Modo de prueba: $([ "$TEST_MODE" = true ] && echo "Activado" || echo "Desactivado")"
    log "INFO" "Salida detallada: $([ "$VERBOSE" = true ] && echo "Activada" || echo "Desactivada")"
    log "INFO" "Archivo de log: $LOG_FILE"
    log "INFO" "======================================"
    
    # Verificar dependencias
    check_dependencies
    
    # Procesar sitios
    if process_sites_file "$SITES_FILE"; then
        log "INFO" "🎉 Despliegue masivo completado exitosamente"
        generate_report
        exit 0
    else
        log "ERROR" "❌ Despliegue masivo completado con errores"
        generate_report
        exit 1
    fi
}

# Ejecutar función principal
main "$@"