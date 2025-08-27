# Script para verificar y crear archivos faltantes
# WP Cupon WhatsApp Plugin

Write-Host "=== VERIFICACION DE ARCHIVOS FALTANTES ===" -ForegroundColor Cyan
Write-Host "Plugin: WP Cupon WhatsApp"
Write-Host "Fecha: $(Get-Date)"
Write-Host ""

# Lista de archivos que el plugin intenta cargar
$archivosRequeridos = @(
    "includes/customer-fields.php",
    "includes/recaptcha-integration.php",
    "includes/application-processing.php", 
    "includes/ajax-handlers.php",
    "includes/rest-api.php",
    "includes/redemption-logic.php",
    "includes/stats-functions.php",
    "includes/export-functions.php",
    "public/shortcodes.php",
    "public/my-account-endpoints.php",
    "debug-output.php",
    "debug-headers.php",
    "test-headers.php",
    "admin/institution-stats-page.php",
    "elementor/elementor-addon.php"
)

Write-Host "1. VERIFICANDO ARCHIVOS EN DIRECTORIO LOCAL:" -ForegroundColor Yellow
Write-Host ""

$archivosFaltantes = @()
foreach ($archivo in $archivosRequeridos) {
    if (Test-Path $archivo) {
        Write-Host "✓ $archivo" -ForegroundColor Green
    } else {
        Write-Host "✗ FALTA: $archivo" -ForegroundColor Red
        $archivosFaltantes += $archivo
    }
}

Write-Host ""
Write-Host "2. VERIFICANDO SINTAXIS DEL ARCHIVO PRINCIPAL:" -ForegroundColor Yellow
$archivoMain = "wp-cupon-whatsapp.php"
if (Test-Path $archivoMain) {
    $sintaxis = php -l $archivoMain 2>&1
    if ($sintaxis -match "No syntax errors") {
        Write-Host "✓ Sintaxis correcta en $archivoMain" -ForegroundColor Green
    } else {
        Write-Host "✗ Error de sintaxis en $archivoMain" -ForegroundColor Red
        Write-Host $sintaxis -ForegroundColor Red
    }
} else {
    Write-Host "✗ Archivo principal no encontrado: $archivoMain" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== RESUMEN ===" -ForegroundColor Cyan
Write-Host "Archivos faltantes: $($archivosFaltantes.Count)"

if ($archivosFaltantes.Count -gt 0) {
    Write-Host ""
    Write-Host "ARCHIVOS FALTANTES:" -ForegroundColor Yellow
    foreach ($archivo in $archivosFaltantes) {
        Write-Host "  - $archivo" -ForegroundColor Red
    }
    
    Write-Host ""
    Write-Host "Creando archivos faltantes automáticamente..." -ForegroundColor Yellow
    
    foreach ($archivo in $archivosFaltantes) {
        $directorio = Split-Path $archivo -Parent
        
        # Crear directorio si no existe
        if ($directorio -and -not (Test-Path $directorio)) {
            New-Item -ItemType Directory -Path $directorio -Force | Out-Null
            Write-Host "✓ Directorio creado: $directorio" -ForegroundColor Green
        }
        
        # Crear archivo con contenido básico
        $contenidoBasico = @'
<?php
/**
 * Plugin: WP Cupon WhatsApp
 * Generado automáticamente
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// TODO: Implementar funcionalidad específica
'@
        
        Set-Content -Path $archivo -Value $contenidoBasico -Encoding UTF8
        Write-Host "✓ Archivo creado: $archivo" -ForegroundColor Green
    }
    
    Write-Host ""
    Write-Host "✓ Archivos faltantes creados exitosamente" -ForegroundColor Green
    Write-Host "Ahora puedes intentar activar el plugin nuevamente" -ForegroundColor Cyan
} else {
    Write-Host "✓ Todos los archivos requeridos están presentes" -ForegroundColor Green
}

Write-Host ""
Write-Host "Script completado." -ForegroundColor Cyan