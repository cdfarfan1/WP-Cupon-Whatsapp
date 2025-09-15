# Script de PowerShell para monitorear logs de WordPress en tiempo real
# Uso: .\monitorear-logs-wordpress.ps1

Write-Host "===========================================" -ForegroundColor Cyan
Write-Host "   MONITOR DE LOGS DE WORDPRESS" -ForegroundColor Cyan
Write-Host "===========================================" -ForegroundColor Cyan
Write-Host ""

$debugLogPath = "C:\xampp\htdocs\webstore\wp-content\debug.log"
$errorLogPath = "C:\xampp\htdocs\webstore\wp-content\error.log"

# Verificar si el archivo de debug existe
if (Test-Path $debugLogPath) {
    Write-Host "✅ Archivo debug.log encontrado" -ForegroundColor Green
    $fileSize = (Get-Item $debugLogPath).Length
    $lastModified = (Get-Item $debugLogPath).LastWriteTime
    Write-Host "📊 Tamaño: $([math]::Round($fileSize/1KB, 2)) KB" -ForegroundColor Yellow
    Write-Host "🕒 Última modificación: $lastModified" -ForegroundColor Yellow
} else {
    Write-Host "⚠️  Archivo debug.log no encontrado. Se creará cuando ocurra el primer error." -ForegroundColor Yellow
    # Crear archivo vacío para poder monitorearlo
    New-Item -Path $debugLogPath -ItemType File -Force | Out-Null
    Write-Host "📁 Archivo debug.log creado para monitoreo" -ForegroundColor Green
}

Write-Host ""
Write-Host "🔍 INICIANDO MONITOREO EN TIEMPO REAL..." -ForegroundColor Green
Write-Host "   Presiona Ctrl+C para detener" -ForegroundColor Gray
Write-Host "" 
Write-Host "=================== LOGS ==================" -ForegroundColor Cyan

# Función para colorear los logs según el tipo de mensaje
function Format-LogLine {
    param([string]$line)
    
    if ($line -match "FATAL|Fatal|fatal") {
        Write-Host $line -ForegroundColor Red
    }
    elseif ($line -match "ERROR|Error|error") {
        Write-Host $line -ForegroundColor Red
    }
    elseif ($line -match "WARNING|Warning|warning") {
        Write-Host $line -ForegroundColor Yellow
    }
    elseif ($line -match "NOTICE|Notice|notice") {
        Write-Host $line -ForegroundColor Cyan
    }
    elseif ($line -match "DEBUG|Debug|debug|TEST") {
        Write-Host $line -ForegroundColor Magenta
    }
    elseif ($line -match "wp-cupon-whatsapp|WP Cupon|WPCW") {
        Write-Host $line -ForegroundColor Green
    }
    else {
        Write-Host $line -ForegroundColor White
    }
}

# Mostrar las últimas 5 líneas existentes
if ((Get-Item $debugLogPath).Length -gt 0) {
    Write-Host "📋 Últimas entradas existentes:" -ForegroundColor Blue
    Write-Host "----------------------------------------" -ForegroundColor Gray
    Get-Content $debugLogPath -Tail 5 | ForEach-Object { Format-LogLine $_ }
    Write-Host "----------------------------------------" -ForegroundColor Gray
    Write-Host "🔄 Monitoreando nuevas entradas..." -ForegroundColor Blue
    Write-Host ""
}

# Monitorear el archivo en tiempo real
try {
    Get-Content $debugLogPath -Wait -Tail 0 | ForEach-Object {
        $timestamp = Get-Date -Format "HH:mm:ss"
        Write-Host "[$timestamp] " -NoNewline -ForegroundColor Gray
        Format-LogLine $_
    }
}
catch {
    Write-Host "❌ Error al monitorear el archivo: $($_.Exception.Message)" -ForegroundColor Red
}
finally {
    Write-Host ""
    Write-Host "🛑 Monitoreo detenido" -ForegroundColor Yellow
    Write-Host "===========================================" -ForegroundColor Cyan
}