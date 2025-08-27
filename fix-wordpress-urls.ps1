# Script para corregir las URLs de WordPress de HTTPS a HTTP
# Esto solucionará el problema de que solo se muestre HTML

$wpConfigPath = 'C:\xampp\htdocs\webstore\wp-config.php'
$backupPath = 'C:\xampp\htdocs\webstore\wp-config-backup.php'

Write-Host "Creando backup de wp-config.php..." -ForegroundColor Yellow
Copy-Item $wpConfigPath $backupPath -Force

Write-Host "Leyendo configuración actual..." -ForegroundColor Yellow
$content = Get-Content $wpConfigPath -Raw

Write-Host "Cambiando URLs de HTTPS a HTTP..." -ForegroundColor Yellow
$content = $content -replace "define\('WP_HOME','https://localhost/webstore'\);", "define('WP_HOME','http://localhost/webstore');"
$content = $content -replace "define\('WP_SITEURL','https://localhost/webstore'\);", "define('WP_SITEURL','http://localhost/webstore');"

Write-Host "Guardando configuración corregida..." -ForegroundColor Yellow
$content | Set-Content $wpConfigPath -Encoding UTF8

Write-Host "¡URLs de WordPress corregidas!" -ForegroundColor Green
Write-Host "WP_HOME: http://localhost/webstore" -ForegroundColor Green
Write-Host "WP_SITEURL: http://localhost/webstore" -ForegroundColor Green
Write-Host "Backup guardado en: $backupPath" -ForegroundColor Cyan