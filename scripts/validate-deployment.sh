#!/bin/bash

# Script de Validación para Despliegue Masivo - WP Cupón WhatsApp v1.4.0
# Verifica que todos los archivos y configuraciones estén correctos

set -e

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Contadores
TOTAL_CHECKS=0
PASSED_CHECKS=0
FAILED_CHECKS=0
WARNING_CHECKS=0

# Función para logging
log() {
    local level=$1
    shift
    local message="$@"
    
    case $level in
        "PASS")
            echo -e "${GREEN}[✓]${NC} $message"
            ((PASSED_CHECKS++))
            ;;
        "FAIL")
            echo -e "${RED}[✗]${NC} $message"
            ((FAILED_CHECKS++))
            ;;
        "WARN")
            echo -e "${YELLOW}[!]${NC} $message"
            ((WARNING_CHECKS++))
            ;;
        "INFO")
            echo -e "${BLUE}[i]${NC} $message"
            ;;
    esac
    
    ((TOTAL_CHECKS++))
}

# Función para verificar archivo
check_file() {
    local file=$1
    local description=$2
    
    if [[ -f $file ]]; then
        log "PASS" "$description: $file"
        return 0
    else
        log "FAIL" "$description: $file (NO ENCONTRADO)"
        return 1
    fi
}

# Función para verificar directorio
check_directory() {
    local dir=$1
    local description=$2
    
    if [[ -d $dir ]]; then
        log "PASS" "$description: $dir"
        return 0
    else
        log "FAIL" "$description: $dir (NO ENCONTRADO)"
        return 1
    fi
}

# Función para verificar sintaxis PHP
check_php_syntax() {
    local file=$1
    
    if php -l "$file" >/dev/null 2>&1; then
        log "PASS" "Sintaxis PHP válida: $(basename $file)"
        return 0
    else
        log "FAIL" "Error de sintaxis PHP: $(basename $file)"
        return 1
    fi
}

# Función para verificar permisos
check_permissions() {
    local file=$1
    local expected_perm=$2
    
    if [[ -f $file ]] || [[ -d $file ]]; then
        local actual_perm=$(stat -c "%a" "$file" 2>/dev/null || stat -f "%A" "$file" 2>/dev/null)
        if [[ $actual_perm == $expected_perm ]]; then
            log "PASS" "Permisos correctos: $(basename $file) ($actual_perm)"
        else
            log "WARN" "Permisos diferentes: $(basename $file) (actual: $actual_perm, esperado: $expected_perm)"
        fi
    fi
}

# Función principal de validación
main() {
    echo -e "${BLUE}WP Cupón WhatsApp - Validación de Despliegue Masivo${NC}"
    echo -e "${BLUE}====================================================${NC}"
    echo ""
    
    # 1. Verificar archivos principales del plugin
    log "INFO" "Verificando archivos principales del plugin..."
    check_file "wp-cupon-whatsapp-fixed.php" "Archivo principal del plugin"
    check_file "includes/validation-enhanced.php" "Sistema de validación mejorada"
    
    # 2. Verificar clases para producción masiva
    log "INFO" "Verificando clases para producción masiva..."
    check_file "includes/class-wpcw-auto-config.php" "Configuración automática"
    check_file "includes/class-wpcw-regional-detector.php" "Detector regional"
    check_file "includes/class-wpcw-migration-tools.php" "Herramientas de migración"
    check_file "includes/class-wpcw-centralized-logger.php" "Sistema de logs centralizado"
    check_file "includes/class-wpcw-auto-installer.php" "Instalador automático"
    check_file "includes/class-wpcw-performance-optimizer.php" "Optimizador de rendimiento"
    check_file "includes/class-wpcw-cli-commands.php" "Comandos WP-CLI"
    
    # 3. Verificar scripts de despliegue
    log "INFO" "Verificando scripts de despliegue..."
    check_file "scripts/mass-deployment.sh" "Script de despliegue Bash"
    check_file "scripts/mass-deployment.ps1" "Script de despliegue PowerShell"
    
    # 4. Verificar archivos de configuración
    log "INFO" "Verificando archivos de configuración..."
    check_file "config/mass-deployment-config.json" "Configuración JSON"
    
    # 5. Verificar documentación
    log "INFO" "Verificando documentación..."
    check_file "README-MASS-DEPLOYMENT.md" "Guía de despliegue masivo"
    check_file "DOCUMENTACION_ADMINISTRADORES.md" "Documentación para administradores"
    check_file "CHANGELOG.md" "Registro de cambios"
    check_file "MEJORAS_VALIDACION.md" "Documentación de mejoras"
    
    # 6. Verificar ejemplos
    log "INFO" "Verificando archivos de ejemplo..."
    check_file "examples/sitios-ejemplo.txt" "Archivo de ejemplo de sitios"
    
    # 7. Verificar directorios
    log "INFO" "Verificando estructura de directorios..."
    check_directory "includes" "Directorio de clases"
    check_directory "scripts" "Directorio de scripts"
    check_directory "config" "Directorio de configuración"
    check_directory "examples" "Directorio de ejemplos"
    
    # 8. Verificar sintaxis PHP
    log "INFO" "Verificando sintaxis PHP..."
    
    # Verificar archivo principal
    if [[ -f "wp-cupon-whatsapp-fixed.php" ]]; then
        check_php_syntax "wp-cupon-whatsapp-fixed.php"
    fi
    
    # Verificar todas las clases PHP
    for php_file in includes/*.php; do
        if [[ -f $php_file ]]; then
            check_php_syntax "$php_file"
        fi
    done
    
    # 9. Verificar configuración JSON
    log "INFO" "Verificando configuración JSON..."
    if [[ -f "config/mass-deployment-config.json" ]]; then
        if python -m json.tool "config/mass-deployment-config.json" >/dev/null 2>&1 || 
           jq empty "config/mass-deployment-config.json" >/dev/null 2>&1; then
            log "PASS" "Configuración JSON válida"
        else
            log "FAIL" "Configuración JSON inválida"
        fi
    fi
    
    # 10. Verificar permisos de archivos
    log "INFO" "Verificando permisos de archivos..."
    
    # Scripts deben ser ejecutables
    if [[ -f "scripts/mass-deployment.sh" ]]; then
        if [[ -x "scripts/mass-deployment.sh" ]]; then
            log "PASS" "Script Bash es ejecutable"
        else
            log "WARN" "Script Bash no es ejecutable (ejecutar: chmod +x scripts/mass-deployment.sh)"
        fi
    fi
    
    # 11. Verificar dependencias del sistema
    log "INFO" "Verificando dependencias del sistema..."
    
    # PHP
    if command -v php >/dev/null 2>&1; then
        php_version=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1,2)
        log "PASS" "PHP disponible (versión: $php_version)"
        
        # Verificar versión mínima
        if php -r "exit(version_compare(PHP_VERSION, '7.4', '>=') ? 0 : 1);"; then
            log "PASS" "Versión de PHP compatible (>= 7.4)"
        else
            log "FAIL" "Versión de PHP incompatible (se requiere >= 7.4)"
        fi
    else
        log "FAIL" "PHP no está disponible"
    fi
    
    # WP-CLI (opcional pero recomendado)
    if command -v wp >/dev/null 2>&1; then
        wp_version=$(wp --version | cut -d' ' -f2)
        log "PASS" "WP-CLI disponible (versión: $wp_version)"
    else
        log "WARN" "WP-CLI no está disponible (recomendado para despliegue masivo)"
    fi
    
    # curl
    if command -v curl >/dev/null 2>&1; then
        log "PASS" "curl disponible"
    else
        log "WARN" "curl no está disponible (necesario para validaciones HTTP)"
    fi
    
    # jq (opcional)
    if command -v jq >/dev/null 2>&1; then
        log "PASS" "jq disponible (procesamiento JSON)"
    else
        log "WARN" "jq no está disponible (opcional, mejora el procesamiento JSON)"
    fi
    
    # 12. Verificar integridad de archivos
    log "INFO" "Verificando integridad de archivos..."
    
    # Verificar que los archivos no estén vacíos
    critical_files=(
        "wp-cupon-whatsapp-fixed.php"
        "includes/validation-enhanced.php"
        "includes/class-wpcw-auto-config.php"
        "includes/class-wpcw-regional-detector.php"
        "includes/class-wpcw-migration-tools.php"
    )
    
    for file in "${critical_files[@]}"; do
        if [[ -f $file ]]; then
            if [[ -s $file ]]; then
                log "PASS" "Archivo no vacío: $(basename $file)"
            else
                log "FAIL" "Archivo vacío: $(basename $file)"
            fi
        fi
    done
    
    # 13. Verificar configuración de WordPress (si está disponible)
    if [[ -f "wp-config.php" ]] || [[ -f "../wp-config.php" ]] || [[ -f "../../wp-config.php" ]]; then
        log "PASS" "Entorno WordPress detectado"
        
        # Verificar si WP-CLI puede conectarse
        if command -v wp >/dev/null 2>&1; then
            if wp core version >/dev/null 2>&1; then
                wp_core_version=$(wp core version)
                log "PASS" "WordPress funcional (versión: $wp_core_version)"
            else
                log "WARN" "WordPress detectado pero WP-CLI no puede conectarse"
            fi
        fi
    else
        log "INFO" "No se detectó entorno WordPress (normal si se ejecuta fuera de WordPress)"
    fi
    
    # Resumen final
    echo ""
    echo -e "${BLUE}=== RESUMEN DE VALIDACIÓN ===${NC}"
    echo -e "Total de verificaciones: $TOTAL_CHECKS"
    echo -e "${GREEN}Exitosas: $PASSED_CHECKS${NC}"
    echo -e "${RED}Fallidas: $FAILED_CHECKS${NC}"
    echo -e "${YELLOW}Advertencias: $WARNING_CHECKS${NC}"
    
    # Calcular porcentaje de éxito
    if [[ $TOTAL_CHECKS -gt 0 ]]; then
        success_rate=$(( (PASSED_CHECKS * 100) / TOTAL_CHECKS ))
        echo -e "Tasa de éxito: $success_rate%"
    fi
    
    echo ""
    
    # Determinar resultado final
    if [[ $FAILED_CHECKS -eq 0 ]]; then
        if [[ $WARNING_CHECKS -eq 0 ]]; then
            echo -e "${GREEN}🎉 VALIDACIÓN COMPLETADA EXITOSAMENTE${NC}"
            echo -e "${GREEN}El plugin está listo para despliegue masivo.${NC}"
            exit 0
        else
            echo -e "${YELLOW}⚠️  VALIDACIÓN COMPLETADA CON ADVERTENCIAS${NC}"
            echo -e "${YELLOW}El plugin puede desplegarse, pero revisa las advertencias.${NC}"
            exit 0
        fi
    else
        echo -e "${RED}❌ VALIDACIÓN FALLIDA${NC}"
        echo -e "${RED}Corrige los errores antes de proceder con el despliegue.${NC}"
        exit 1
    fi
}

# Mostrar ayuda
if [[ $1 == "-h" ]] || [[ $1 == "--help" ]]; then
    echo "Script de Validación - WP Cupón WhatsApp v1.4.0"
    echo ""
    echo "Uso: $0"
    echo ""
    echo "Este script verifica que todos los archivos y configuraciones"
    echo "estén correctos antes del despliegue masivo."
    echo ""
    echo "El script verifica:"
    echo "  - Archivos principales del plugin"
    echo "  - Clases para producción masiva"
    echo "  - Scripts de despliegue"
    echo "  - Configuración JSON"
    echo "  - Documentación"
    echo "  - Sintaxis PHP"
    echo "  - Permisos de archivos"
    echo "  - Dependencias del sistema"
    echo ""
    exit 0
fi

# Ejecutar validación
main