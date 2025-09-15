# Script para verificar que el problema de headers se haya solucionado
Write-Host "=== VERIFICACION DE SOLUCION DE HEADERS ===" -ForegroundColor Yellow
Write-Host ""

# Definir rutas
$wpPath = "C:\xampp\htdocs\webstore"
$wpLoadFile = "$wpPath\wp-load.php"
$pluginPath = "$wpPath\wp-content\plugins\wp-cupon-whatsapp"

# Verificar que wp-load.php existe y tiene contenido correcto
Write-Host "1. Verificando wp-load.php..." -ForegroundColor Cyan
if (Test-Path $wpLoadFile) {
    $content = Get-Content $wpLoadFile -Raw
    if ($content -match "^ok<\?php") {
        Write-Host "   ERROR: wp-load.php aun contiene 'ok<?php'" -ForegroundColor Red
        Write-Host "   Ejecutando correccion..." -ForegroundColor Yellow
        & ".\actualizar-wp-load.ps1"
    } elseif ($content -match "^<\?php") {
        Write-Host "   OK: wp-load.php tiene el formato correcto" -ForegroundColor Green
    } else {
        Write-Host "   ADVERTENCIA: wp-load.php tiene contenido inesperado" -ForegroundColor Yellow
        $preview = $content.Substring(0, [Math]::Min(50, $content.Length))
        Write-Host "   Primeras lineas: $preview" -ForegroundColor Gray
    }
} else {
    Write-Host "   ERROR: wp-load.php no existe" -ForegroundColor Red
}

# Verificar estado del plugin
Write-Host ""
Write-Host "2. Verificando estado del plugin..." -ForegroundColor Cyan
if (Test-Path $pluginPath) {
    Write-Host "   Plugin esta ACTIVO (directorio existe)" -ForegroundColor Green
} else {
    # Buscar directorio desactivado
    $disabledPlugins = Get-ChildItem "$wpPath\wp-content\plugins" -Directory | Where-Object { $_.Name -like "wp-cupon-whatsapp.disabled.*" }
    if ($disabledPlugins) {
        Write-Host "   Plugin esta DESACTIVADO" -ForegroundColor Yellow
        Write-Host "   Directorio desactivado: $($disabledPlugins[0].Name)" -ForegroundColor Gray
        
        # Preguntar si reactivar
        Write-Host ""
        $reactivar = Read-Host "Deseas reactivar el plugin para probar? (s/n)"
        if ($reactivar -eq 's' -or $reactivar -eq 'S') {
            try {
                Rename-Item -Path $disabledPlugins[0].FullName -NewName $pluginPath -Force
                Write-Host "   Plugin reactivado exitosamente" -ForegroundColor Green
            } catch {
                Write-Host "   ERROR al reactivar: $($_.Exception.Message)" -ForegroundColor Red
            }
        }
    } else {
        Write-Host "   Plugin no encontrado (ni activo ni desactivado)" -ForegroundColor Red
    }
}

# Verificar sintaxis del plugin principal
Write-Host ""
Write-Host "3. Verificando sintaxis del plugin..." -ForegroundColor Cyan
$mainPluginFile = "$pluginPath\wp-cupon-whatsapp.php"
if (Test-Path $mainPluginFile) {
    try {
        $syntaxCheck = php -l $mainPluginFile 2>&1
        if ($syntaxCheck -match "No syntax errors") {
            Write-Host "   OK: Sintaxis correcta" -ForegroundColor Green
        } else {
            Write-Host "   ERROR de sintaxis: $syntaxCheck" -ForegroundColor Red
        }
    } catch {
        Write-Host "   No se pudo verificar sintaxis (PHP no disponible)" -ForegroundColor Yellow
    }
} else {
    Write-Host "   Plugin no esta activo, no se puede verificar sintaxis" -ForegroundColor Yellow
}

# Probar acceso a WordPress
Write-Host ""
Write-Host "4. Probando acceso a WordPress..." -ForegroundColor Cyan
try {
    $response = Invoke-WebRequest -Uri "https://localhost/webstore/" -UseBasicParsing -TimeoutSec 10 2>$null
    if ($response.StatusCode -eq 200) {
        Write-Host "   OK: WordPress responde correctamente" -ForegroundColor Green
    } else {
        Write-Host "   ADVERTENCIA: WordPress responde con codigo $($response.StatusCode)" -ForegroundColor Yellow
    }
} catch {
    Write-Host "   ERROR: No se puede acceder a WordPress: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "   Verifica que Apache este ejecutandose" -ForegroundColor Yellow
}

# Resumen final
Write-Host ""
Write-Host "=== RESUMEN ===" -ForegroundColor Yellow

# Verificar wp-load.php
$wpLoadStatus = "NECESITA ATENCION"
$wpLoadColor = "Red"
if (Test-Path $wpLoadFile) {
    $content = Get-Content $wpLoadFile -Raw
    if ($content -match "^<\?php") {
        $wpLoadStatus = "CORREGIDO"
        $wpLoadColor = "Green"
    }
}
Write-Host "1. wp-load.php: $wpLoadStatus" -ForegroundColor $wpLoadColor

# Verificar plugin
$pluginStatus = "DESACTIVADO"
$pluginColor = "Yellow"
if (Test-Path $pluginPath) {
    $pluginStatus = "ACTIVO"
    $pluginColor = "Green"
}
Write-Host "2. Plugin: $pluginStatus" -ForegroundColor $pluginColor

# Verificar WordPress
$wpStatus = "NO ACCESIBLE"
$wpColor = "Red"
try {
    $r = Invoke-WebRequest -Uri "https://localhost/webstore/" -UseBasicParsing -TimeoutSec 5 2>$null
    if ($r.StatusCode -eq 200) {
        $wpStatus = "FUNCIONANDO"
        $wpColor = "Green"
    } else {
        $wpStatus = "CON PROBLEMAS"
        $wpColor = "Yellow"
    }
} catch {
    # Ya esta definido arriba
}
Write-Host "3. WordPress: $wpStatus" -ForegroundColor $wpColor

Write-Host ""
Write-Host "Proceso de verificacion completado!" -ForegroundColor Green
Write-Host "Puedes acceder a: https://localhost/webstore/wp-admin/" -ForegroundColor Cyan