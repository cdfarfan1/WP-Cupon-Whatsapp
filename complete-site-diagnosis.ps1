# Script de diagnóstico completo del sitio WordPress
# Verifica el estado del sitio con y sin el plugin WP Cupón WhatsApp

Write-Host "=== DIAGNOSTICO COMPLETO DEL SITIO WORDPRESS ===" -ForegroundColor Cyan
Write-Host ""

# Verificar estado de servicios
Write-Host "1. VERIFICANDO SERVICIOS..." -ForegroundColor Yellow
$apacheProcess = Get-Process | Where-Object {$_.ProcessName -like '*httpd*'}
$mysqlProcess = Get-Process | Where-Object {$_.ProcessName -like '*mysqld*'}

if ($apacheProcess) {
    Write-Host "   ✓ Apache está ejecutándose (PID: $($apacheProcess.Id -join ', '))" -ForegroundColor Green
} else {
    Write-Host "   ✗ Apache NO está ejecutándose" -ForegroundColor Red
}

if ($mysqlProcess) {
    Write-Host "   ✓ MySQL está ejecutándose (PID: $($mysqlProcess.Id -join ', '))" -ForegroundColor Green
} else {
    Write-Host "   ✗ MySQL NO está ejecutándose" -ForegroundColor Red
}

# Verificar conectividad de puertos
Write-Host ""
Write-Host "2. VERIFICANDO CONECTIVIDAD..." -ForegroundColor Yellow
$port80 = Test-NetConnection -ComputerName localhost -Port 80 -WarningAction SilentlyContinue
$port443 = Test-NetConnection -ComputerName localhost -Port 443 -WarningAction SilentlyContinue

if ($port80.TcpTestSucceeded) {
    Write-Host "   ✓ Puerto 80 (HTTP) disponible" -ForegroundColor Green
} else {
    Write-Host "   ✗ Puerto 80 (HTTP) NO disponible" -ForegroundColor Red
}

if ($port443.TcpTestSucceeded) {
    Write-Host "   ✓ Puerto 443 (HTTPS) disponible" -ForegroundColor Green
} else {
    Write-Host "   ✗ Puerto 443 (HTTPS) NO disponible" -ForegroundColor Red
}

# Verificar archivos de WordPress
Write-Host ""
Write-Host "3. VERIFICANDO ARCHIVOS DE WORDPRESS..." -ForegroundColor Yellow
$wpFiles = @(
    'C:\xampp\htdocs\webstore\wp-config.php',
    'C:\xampp\htdocs\webstore\index.php',
    'C:\xampp\htdocs\webstore\.htaccess'
)

foreach ($file in $wpFiles) {
    if (Test-Path $file) {
        Write-Host "   ✓ $(Split-Path $file -Leaf) existe" -ForegroundColor Green
    } else {
        Write-Host "   ✗ $(Split-Path $file -Leaf) NO existe" -ForegroundColor Red
    }
}

# Verificar estado del plugin
Write-Host ""
Write-Host "4. VERIFICANDO ESTADO DEL PLUGIN..." -ForegroundColor Yellow
$pluginActive = Test-Path 'C:\xampp\htdocs\webstore\wp-content\plugins\wp-cupon-whatsapp'
$pluginDisabled = Test-Path 'C:\xampp\htdocs\webstore\wp-content\plugins\wp-cupon-whatsapp-DISABLED'

if ($pluginActive) {
    Write-Host "   ✓ Plugin WP Cupón WhatsApp está ACTIVO" -ForegroundColor Green
} elseif ($pluginDisabled) {
    Write-Host "   ⚠ Plugin WP Cupón WhatsApp está DESACTIVADO (para diagnóstico)" -ForegroundColor Yellow
} else {
    Write-Host "   ✗ Plugin WP Cupón WhatsApp NO encontrado" -ForegroundColor Red
}

# Verificar configuración de URLs
Write-Host ""
Write-Host "5. VERIFICANDO CONFIGURACION DE URLs..." -ForegroundColor Yellow
$wpConfig = Get-Content 'C:\xampp\htdocs\webstore\wp-config.php' -Raw
if ($wpConfig -match "define\('WP_HOME','([^']+)'\)") {
    Write-Host "   WP_HOME: $($matches[1])" -ForegroundColor Cyan
}
if ($wpConfig -match "define\('WP_SITEURL','([^']+)'\)") {
    Write-Host "   WP_SITEURL: $($matches[1])" -ForegroundColor Cyan
}

Write-Host ""
Write-Host "=== RESUMEN DEL DIAGNOSTICO ===" -ForegroundColor Cyan

if ($apacheProcess -and $mysqlProcess -and $port443.TcpTestSucceeded) {
    Write-Host "✓ SERVICIOS: Funcionando correctamente" -ForegroundColor Green
    Write-Host "✓ ACCESO: https://localhost/webstore/" -ForegroundColor Green
    
    if ($pluginDisabled) {
        Write-Host "⚠ PLUGIN: Desactivado para diagnóstico" -ForegroundColor Yellow
        Write-Host ""
        Write-Host "INSTRUCCIONES:" -ForegroundColor White
        Write-Host "1. Accede a https://localhost/webstore/wp-admin/" -ForegroundColor White
        Write-Host "2. Si el sitio funciona correctamente, el plugin era la causa" -ForegroundColor White
        Write-Host "3. Para reactivar el plugin: .\restore-plugin.ps1" -ForegroundColor White
    } else {
        Write-Host "✓ PLUGIN: Activo y funcionando" -ForegroundColor Green
    }
} else {
    Write-Host "✗ PROBLEMA: Servicios no están funcionando correctamente" -ForegroundColor Red
    Write-Host "   Verifica que XAMPP esté iniciado correctamente" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== FIN DEL DIAGNOSTICO ===" -ForegroundColor Cyan