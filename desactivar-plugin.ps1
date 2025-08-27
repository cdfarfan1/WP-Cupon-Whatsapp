# Script para desactivar el plugin WP Cupon WhatsApp
Write-Host "=== DESACTIVANDO PLUGIN WP CUPON WHATSAPP ===" -ForegroundColor Yellow
Write-Host ""

# Definir rutas
$wpPath = "C:\xampp\htdocs\webstore"
$pluginPath = "$wpPath\wp-content\plugins\wp-cupon-whatsapp"

# Verificar si WordPress existe
if (-not (Test-Path $wpPath)) {
    Write-Host "Error: No se encontro WordPress en $wpPath" -ForegroundColor Red
    exit 1
}

# Verificar si el plugin existe
if (-not (Test-Path $pluginPath)) {
    Write-Host "El plugin no esta instalado en $pluginPath" -ForegroundColor Yellow
    Write-Host "No hay nada que desactivar" -ForegroundColor Green
    exit 0
}

Write-Host "Plugin encontrado en: $pluginPath" -ForegroundColor Cyan

# Metodo principal: Renombrar directorio del plugin
Write-Host "Desactivando plugin mediante renombrado de directorio..." -ForegroundColor Cyan

$backupPath = "$pluginPath.disabled.$(Get-Date -Format 'yyyyMMdd-HHmmss')"

try {
    if (Test-Path $pluginPath) {
        Rename-Item -Path $pluginPath -NewName $backupPath -Force
        Write-Host "Plugin movido a: $backupPath" -ForegroundColor Green
        Write-Host "Esto asegura que WordPress no pueda cargarlo" -ForegroundColor Gray
        
        # Verificar que se movio correctamente
        if (Test-Path $backupPath) {
            Write-Host "Verificacion exitosa: Plugin desactivado" -ForegroundColor Green
        } else {
            Write-Host "Error en la verificacion" -ForegroundColor Red
        }
    }
} catch {
    Write-Host "No se pudo renombrar el directorio: $($_.Exception.Message)" -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "=== RESUMEN ===" -ForegroundColor Yellow
Write-Host "Plugin WP Cupon WhatsApp desactivado" -ForegroundColor Green
Write-Host "Directorio del plugin respaldado como: $(Split-Path $backupPath -Leaf)" -ForegroundColor Cyan
Write-Host "Puedes verificar en: https://localhost/webstore/wp-admin/plugins.php" -ForegroundColor Cyan
Write-Host ""
Write-Host "Para reactivar el plugin:" -ForegroundColor Yellow
Write-Host "1. Renombra la carpeta de vuelta a 'wp-cupon-whatsapp'" -ForegroundColor Gray
Write-Host "2. Activalo desde el panel de WordPress" -ForegroundColor Gray
Write-Host ""
Write-Host "Proceso completado!" -ForegroundColor Green