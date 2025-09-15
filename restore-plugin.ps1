# Script para restaurar el plugin WP Cupón WhatsApp después del diagnóstico

$pluginPath = 'C:\xampp\htdocs\webstore\wp-content\plugins\wp-cupon-whatsapp'
$pluginBackupPath = 'C:\xampp\htdocs\webstore\wp-content\plugins\wp-cupon-whatsapp-DISABLED'

Write-Host "=== RESTAURANDO PLUGIN WP CUPÓN WHATSAPP ===" -ForegroundColor Cyan
Write-Host ""

if (Test-Path $pluginBackupPath) {
    Write-Host "Restaurando plugin..." -ForegroundColor Yellow
    
    if (Test-Path $pluginPath) {
        Remove-Item $pluginPath -Recurse -Force
    }
    
    Rename-Item $pluginBackupPath $pluginPath
    Write-Host "✓ Plugin WP Cupón WhatsApp restaurado exitosamente" -ForegroundColor Green
    Write-Host "✓ El plugin está nuevamente activo en: $pluginPath" -ForegroundColor Green
    
} else {
    Write-Host "✗ No se encontró backup del plugin en: $pluginBackupPath" -ForegroundColor Red
    Write-Host "El plugin puede que ya esté activo o no se haya desactivado correctamente" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== RESTAURACIÓN COMPLETADA ===" -ForegroundColor Cyan