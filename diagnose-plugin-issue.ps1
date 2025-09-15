# Script para diagnosticar si el plugin WP Cupón WhatsApp está causando problemas
# Desactiva temporalmente el plugin para verificar si es la causa del problema HTML

$pluginPath = 'C:\xampp\htdocs\webstore\wp-content\plugins\wp-cupon-whatsapp'
$pluginBackupPath = 'C:\xampp\htdocs\webstore\wp-content\plugins\wp-cupon-whatsapp-DISABLED'

Write-Host "=== DIAGNOSTICO DEL PLUGIN WP CUPON WHATSAPP ===" -ForegroundColor Cyan
Write-Host ""

# Verificar si el plugin existe
if (Test-Path $pluginPath) {
    Write-Host "✓ Plugin encontrado en: $pluginPath" -ForegroundColor Green
    
    # Crear backup y desactivar plugin temporalmente
    Write-Host "Desactivando plugin temporalmente..." -ForegroundColor Yellow
    
    if (Test-Path $pluginBackupPath) {
        Remove-Item $pluginBackupPath -Recurse -Force
    }
    
    Rename-Item $pluginPath $pluginBackupPath
    Write-Host "✓ Plugin desactivado (renombrado a wp-cupon-whatsapp-DISABLED)" -ForegroundColor Green
    
    Write-Host ""
    Write-Host "INSTRUCCIONES:" -ForegroundColor Cyan
    Write-Host "1. Ahora accede a https://localhost/webstore/wp-admin/" -ForegroundColor White
    Write-Host "2. Verifica si el problema del HTML persiste" -ForegroundColor White
    Write-Host "3. Si el problema se soluciona, el plugin era la causa" -ForegroundColor White
    Write-Host "4. Para reactivar el plugin, ejecuta: restore-plugin.ps1" -ForegroundColor White
    
} else {
    Write-Host "✗ Plugin no encontrado en: $pluginPath" -ForegroundColor Red
    Write-Host "El problema NO es causado por el plugin WP Cupón WhatsApp" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== FIN DEL DIAGNOSTICO ===" -ForegroundColor Cyan