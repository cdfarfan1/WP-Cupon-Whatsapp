# Script de Validación Simple - WP Cupón WhatsApp v1.4.0

param([switch]$Help)

$Global:TotalChecks = 0
$Global:PassedChecks = 0
$Global:FailedChecks = 0
$Global:WarningChecks = 0

function Write-Log {
    param(
        [ValidateSet('PASS', 'FAIL', 'WARN', 'INFO')][string]$Level,
        [string]$Message
    )
    
    $Global:TotalChecks++
    
    switch ($Level) {
        'PASS' {
            Write-Host "[✓] $Message" -ForegroundColor Green
            $Global:PassedChecks++
        }
        'FAIL' {
            Write-Host "[✗] $Message" -ForegroundColor Red
            $Global:FailedChecks++
        }
        'WARN' {
            Write-Host "[!] $Message" -ForegroundColor Yellow
            $Global:WarningChecks++
        }
        'INFO' {
            Write-Host "[i] $Message" -ForegroundColor Blue
        }
    }
}

function Test-FileExists {
    param([string]$FilePath, [string]$Description)
    
    if (Test-Path $FilePath -PathType Leaf) {
        Write-Log -Level 'PASS' -Message "$Description : $FilePath"
    } else {
        Write-Log -Level 'FAIL' -Message "$Description : $FilePath (NO ENCONTRADO)"
    }
}

function Test-DirectoryExists {
    param([string]$DirectoryPath, [string]$Description)
    
    if (Test-Path $DirectoryPath -PathType Container) {
        Write-Log -Level 'PASS' -Message "$Description : $DirectoryPath"
    } else {
        Write-Log -Level 'FAIL' -Message "$Description : $DirectoryPath (NO ENCONTRADO)"
    }
}

function Start-Validation {
    Write-Host "WP Cupón WhatsApp - Validación de Despliegue Masivo" -ForegroundColor Blue
    Write-Host "====================================================" -ForegroundColor Blue
    Write-Host ""
    
    Write-Log -Level 'INFO' -Message "Verificando archivos principales del plugin..."
    Test-FileExists -FilePath "wp-cupon-whatsapp-fixed.php" -Description "Archivo principal del plugin"
    Test-FileExists -FilePath "includes\validation-enhanced.php" -Description "Sistema de validación mejorada"
    
    Write-Log -Level 'INFO' -Message "Verificando clases para producción masiva..."
    Test-FileExists -FilePath "includes\class-wpcw-auto-config.php" -Description "Configuración automática"
    Test-FileExists -FilePath "includes\class-wpcw-regional-detector.php" -Description "Detector regional"
    Test-FileExists -FilePath "includes\class-wpcw-migration-tools.php" -Description "Herramientas de migración"
    Test-FileExists -FilePath "includes\class-wpcw-centralized-logger.php" -Description "Sistema de logs centralizado"
    Test-FileExists -FilePath "includes\class-wpcw-auto-installer.php" -Description "Instalador automático"
    Test-FileExists -FilePath "includes\class-wpcw-performance-optimizer.php" -Description "Optimizador de rendimiento"
    Test-FileExists -FilePath "includes\class-wpcw-cli-commands.php" -Description "Comandos WP-CLI"
    
    Write-Log -Level 'INFO' -Message "Verificando scripts de despliegue..."
    Test-FileExists -FilePath "scripts\mass-deployment.sh" -Description "Script de despliegue Bash"
    Test-FileExists -FilePath "scripts\mass-deployment.ps1" -Description "Script de despliegue PowerShell"
    Test-FileExists -FilePath "scripts\validate-deployment.sh" -Description "Script de validación Bash"
    
    Write-Log -Level 'INFO' -Message "Verificando archivos de configuración..."
    Test-FileExists -FilePath "config\mass-deployment-config.json" -Description "Configuración JSON"
    
    Write-Log -Level 'INFO' -Message "Verificando documentación..."
    Test-FileExists -FilePath "README-MASS-DEPLOYMENT.md" -Description "Guía de despliegue masivo"
    Test-FileExists -FilePath "DOCUMENTACION_ADMINISTRADORES.md" -Description "Documentación para administradores"
    Test-FileExists -FilePath "CHANGELOG.md" -Description "Registro de cambios"
    Test-FileExists -FilePath "MEJORAS_VALIDACION.md" -Description "Documentación de mejoras"
    
    Write-Log -Level 'INFO' -Message "Verificando archivos de ejemplo..."
    Test-FileExists -FilePath "examples\sitios-ejemplo.txt" -Description "Archivo de ejemplo de sitios"
    
    Write-Log -Level 'INFO' -Message "Verificando estructura de directorios..."
    Test-DirectoryExists -DirectoryPath "includes" -Description "Directorio de clases"
    Test-DirectoryExists -DirectoryPath "scripts" -Description "Directorio de scripts"
    Test-DirectoryExists -DirectoryPath "config" -Description "Directorio de configuración"
    Test-DirectoryExists -DirectoryPath "examples" -Description "Directorio de ejemplos"
    
    Write-Log -Level 'INFO' -Message "Verificando dependencias del sistema..."
    
    try {
        $null = Get-Command php -ErrorAction Stop
        $phpOutput = php -v 2>&1
        if ($phpOutput -match "PHP (\d+\.\d+\.\d+)") {
            $version = $matches[1]
            Write-Log -Level 'PASS' -Message "PHP disponible (versión: $version)"
            if ([version]$version -ge [version]"7.4") {
                Write-Log -Level 'PASS' -Message "Versión de PHP compatible (>= 7.4)"
            } else {
                Write-Log -Level 'FAIL' -Message "Versión de PHP incompatible (se requiere >= 7.4)"
            }
        } else {
            Write-Log -Level 'WARN' -Message "No se pudo obtener la versión de PHP"
        }
    } catch {
        Write-Log -Level 'FAIL' -Message "PHP no está disponible"
    }
    
    try {
        $null = Get-Command wp -ErrorAction Stop
        $wpVersion = wp --version 2>&1
        Write-Log -Level 'PASS' -Message "WP-CLI disponible ($wpVersion)"
    } catch {
        Write-Log -Level 'WARN' -Message "WP-CLI no está disponible (recomendado para despliegue masivo)"
    }
    
    $wpConfigPaths = @("wp-config.php", "..\wp-config.php", "..\..\wp-config.php")
    $wpConfigFound = $false
    
    foreach ($path in $wpConfigPaths) {
        if (Test-Path $path) {
            $wpConfigFound = $true
            break
        }
    }
    
    if ($wpConfigFound) {
        Write-Log -Level 'PASS' -Message "Entorno WordPress detectado"
    } else {
        Write-Log -Level 'INFO' -Message "No se detectó entorno WordPress (normal si se ejecuta fuera de WordPress)"
    }
    
    Write-Host ""
    Write-Host "=== RESUMEN DE VALIDACIÓN ===" -ForegroundColor Blue
    Write-Host "Total de verificaciones: $Global:TotalChecks"
    Write-Host "Exitosas: $Global:PassedChecks" -ForegroundColor Green
    Write-Host "Fallidas: $Global:FailedChecks" -ForegroundColor Red
    Write-Host "Advertencias: $Global:WarningChecks" -ForegroundColor Yellow
    
    if ($Global:TotalChecks -gt 0) {
        $successRate = [math]::Round(($Global:PassedChecks / $Global:TotalChecks) * 100, 2)
        Write-Host "Tasa de éxito: $successRate%"
    }
    
    Write-Host ""
    
    if ($Global:FailedChecks -eq 0 -and $Global:WarningChecks -eq 0) {
        Write-Host "🎉 VALIDACIÓN COMPLETADA EXITOSAMENTE" -ForegroundColor Green
        Write-Host "El plugin está listo para despliegue masivo." -ForegroundColor Green
        exit 0
    } elseif ($Global:FailedChecks -eq 0) {
        Write-Host "⚠️  VALIDACIÓN COMPLETADA CON ADVERTENCIAS" -ForegroundColor Yellow
        Write-Host "El plugin puede desplegarse, pero revisa las advertencias." -ForegroundColor Yellow
        exit 0
    } else {
        Write-Host "❌ VALIDACIÓN FALLIDA" -ForegroundColor Red
        Write-Host "Corrige los errores antes de proceder con el despliegue." -ForegroundColor Red
        exit 1
    }
}

if ($Help) {
    Write-Host "Script de Validación - WP Cupón WhatsApp v1.4.0" -ForegroundColor Blue
    Write-Host ""
    Write-Host "Uso: .\validate-simple.ps1 [-Help]"
    Write-Host ""
    Write-Host "Este script verifica que todos los archivos y configuraciones"
    Write-Host "estén correctos antes del despliegue masivo."
    Write-Host ""
    exit 0
}

Start-Validation