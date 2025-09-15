# Script de Validación para Despliegue Masivo - WP Cupón WhatsApp v1.4.0
# Verifica que todos los archivos y configuraciones estén correctos

param(
    [switch]$Help
)

# Variables globales
$Global:TotalChecks = 0
$Global:PassedChecks = 0
$Global:FailedChecks = 0
$Global:WarningChecks = 0

# Colores para la salida
$Colors = @{
    Red = "Red"
    Green = "Green"
    Yellow = "Yellow"
    Blue = "Blue"
    White = "White"
}

# Función para escribir logs con colores
function Write-Log {
    param(
        [Parameter(Mandatory=$true)]
        [ValidateSet('PASS', 'FAIL', 'WARN', 'INFO')]
        [string]$Level,
        
        [Parameter(Mandatory=$true)]
        [string]$Message
    )
    
    $Global:TotalChecks++
    
    switch ($Level) {
        'PASS' {
            Write-Host "[✓] $Message" -ForegroundColor $Colors.Green
            $Global:PassedChecks++
        }
        'FAIL' {
            Write-Host "[✗] $Message" -ForegroundColor $Colors.Red
            $Global:FailedChecks++
        }
        'WARN' {
            Write-Host "[!] $Message" -ForegroundColor $Colors.Yellow
            $Global:WarningChecks++
        }
        'INFO' {
            Write-Host "[i] $Message" -ForegroundColor $Colors.Blue
        }
    }
}

# Función para verificar si existe un archivo
function Test-FileExists {
    param(
        [string]$FilePath,
        [string]$Description
    )
    
    if (Test-Path $FilePath -PathType Leaf) {
        Write-Log -Level 'PASS' -Message "$Description`: $FilePath"
    } else {
        Write-Log -Level 'FAIL' -Message "$Description`: $FilePath (NO ENCONTRADO)"
    }
}

# Función para verificar si existe un directorio
function Test-DirectoryExists {
    param(
        [string]$DirectoryPath,
        [string]$Description
    )
    
    if (Test-Path $DirectoryPath -PathType Container) {
        Write-Log -Level 'PASS' -Message "$Description`: $DirectoryPath"
    } else {
        Write-Log -Level 'FAIL' -Message "$Description`: $DirectoryPath (NO ENCONTRADO)"
    }
}

# Función para verificar sintaxis PHP
function Test-PHPSyntax {
    param(
        [string]$FilePath
    )
    
    try {
        $result = php -l $FilePath 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Log -Level 'PASS' -Message "Sintaxis PHP válida: $(Split-Path $FilePath -Leaf)"
        } else {
            Write-Log -Level 'FAIL' -Message "Error de sintaxis PHP: $(Split-Path $FilePath -Leaf)"
        }
    } catch {
        Write-Log -Level 'FAIL' -Message "No se pudo verificar sintaxis PHP: $(Split-Path $FilePath -Leaf)"
    }
}

# Función para verificar sintaxis JSON
function Test-JSONSyntax {
    param(
        [string]$FilePath
    )
    
    try {
        $jsonContent = Get-Content $FilePath -Raw | ConvertFrom-Json
        Write-Log -Level 'PASS' -Message "Configuración JSON válida"
    } catch {
        Write-Log -Level 'FAIL' -Message "Configuración JSON inválida: $($_.Exception.Message)"
    }
}

# Función para verificar si un comando está disponible
function Test-CommandAvailable {
    param(
        [string]$Command
    )
    
    try {
        $null = Get-Command $Command -ErrorAction Stop
        return $true
    } catch {
        return $false
    }
}

# Función principal de validación
function Start-Validation {
    Write-Host "WP Cupón WhatsApp - Validación de Despliegue Masivo" -ForegroundColor $Colors.Blue
    Write-Host "====================================================" -ForegroundColor $Colors.Blue
    Write-Host ""
    
    # Verificar archivos principales
    Write-Log -Level 'INFO' -Message "Verificando archivos principales del plugin..."
    Test-FileExists -FilePath "wp-cupon-whatsapp-fixed.php" -Description "Archivo principal del plugin"
    Test-FileExists -FilePath "includes\validation-enhanced.php" -Description "Sistema de validación mejorada"
    
    # Verificar clases para producción masiva
    Write-Log -Level 'INFO' -Message "Verificando clases para producción masiva..."
    Test-FileExists -FilePath "includes\class-wpcw-auto-config.php" -Description "Configuración automática"
    Test-FileExists -FilePath "includes\class-wpcw-regional-detector.php" -Description "Detector regional"
    Test-FileExists -FilePath "includes\class-wpcw-migration-tools.php" -Description "Herramientas de migración"
    Test-FileExists -FilePath "includes\class-wpcw-centralized-logger.php" -Description "Sistema de logs centralizado"
    Test-FileExists -FilePath "includes\class-wpcw-auto-installer.php" -Description "Instalador automático"
    Test-FileExists -FilePath "includes\class-wpcw-performance-optimizer.php" -Description "Optimizador de rendimiento"
    Test-FileExists -FilePath "includes\class-wpcw-cli-commands.php" -Description "Comandos WP-CLI"
    
    # Verificar scripts de despliegue
    Write-Log -Level 'INFO' -Message "Verificando scripts de despliegue..."
    Test-FileExists -FilePath "scripts\mass-deployment.sh" -Description "Script de despliegue Bash"
    Test-FileExists -FilePath "scripts\mass-deployment.ps1" -Description "Script de despliegue PowerShell"
    Test-FileExists -FilePath "scripts\validate-deployment.sh" -Description "Script de validación Bash"
    
    # Verificar archivos de configuración
    Write-Log -Level 'INFO' -Message "Verificando archivos de configuración..."
    Test-FileExists -FilePath "config\mass-deployment-config.json" -Description "Configuración JSON"
    
    # Verificar documentación
    Write-Log -Level 'INFO' -Message "Verificando documentación..."
    Test-FileExists -FilePath "README-MASS-DEPLOYMENT.md" -Description "Guía de despliegue masivo"
    Test-FileExists -FilePath "DOCUMENTACION_ADMINISTRADORES.md" -Description "Documentación para administradores"
    Test-FileExists -FilePath "CHANGELOG.md" -Description "Registro de cambios"
    Test-FileExists -FilePath "MEJORAS_VALIDACION.md" -Description "Documentación de mejoras"
    
    # Verificar archivos de ejemplo
    Write-Log -Level 'INFO' -Message "Verificando archivos de ejemplo..."
    Test-FileExists -FilePath "examples\sitios-ejemplo.txt" -Description "Archivo de ejemplo de sitios"
    
    # Verificar estructura de directorios
    Write-Log -Level 'INFO' -Message "Verificando estructura de directorios..."
    Test-DirectoryExists -DirectoryPath "includes" -Description "Directorio de clases"
    Test-DirectoryExists -DirectoryPath "scripts" -Description "Directorio de scripts"
    Test-DirectoryExists -DirectoryPath "config" -Description "Directorio de configuración"
    Test-DirectoryExists -DirectoryPath "examples" -Description "Directorio de ejemplos"
    
    # Verificar sintaxis PHP
    Write-Log -Level 'INFO' -Message "Verificando sintaxis PHP..."
    
    # Verificar archivo principal
    if (Test-Path "wp-cupon-whatsapp-fixed.php") {
        Test-PHPSyntax -FilePath "wp-cupon-whatsapp-fixed.php"
    }
    
    # Verificar archivos PHP en includes
    $phpFiles = Get-ChildItem -Path "includes\*.php" -ErrorAction SilentlyContinue
    foreach ($file in $phpFiles) {
        Test-PHPSyntax -FilePath $file.FullName
    }
    
    # Verificar configuración JSON
    Write-Log -Level 'INFO' -Message "Verificando configuración JSON..."
    if (Test-Path "config\mass-deployment-config.json") {
        Test-JSONSyntax -FilePath "config\mass-deployment-config.json"
    }
    
    # Verificar dependencias del sistema
    Write-Log -Level 'INFO' -Message "Verificando dependencias del sistema..."
    
    # Verificar PHP
    if (Test-CommandAvailable -Command "php") {
        try {
            $phpOutput = php -v 2>&1
            if ($phpOutput -match "PHP (\d+\.\d+\.\d+)") {
                $version = $matches[1]
                Write-Log -Level 'PASS' -Message "PHP disponible (versión: $version)"
                
                # Verificar versión mínima
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
    } else {
        Write-Log -Level 'FAIL' -Message "PHP no está disponible"
    }
    
    # Verificar WP-CLI
    if (Test-CommandAvailable -Command "wp") {
        try {
            $wpVersion = wp --version 2>&1
            Write-Log -Level 'PASS' -Message "WP-CLI disponible ($wpVersion)"
        } catch {
            Write-Log -Level 'WARN' -Message "WP-CLI detectado pero no funcional"
        }
    } else {
        Write-Log -Level 'WARN' -Message "WP-CLI no está disponible (recomendado para despliegue masivo)"
    }
    
    # Verificar curl
    if (Test-CommandAvailable -Command "curl") {
        Write-Log -Level 'PASS' -Message "curl disponible"
    } else {
        Write-Log -Level 'WARN' -Message "curl no está disponible (necesario para validaciones HTTP)"
    }
    
    # Verificar PowerShell
    $psVersion = $PSVersionTable.PSVersion
    if ($psVersion.Major -ge 5) {
        Write-Log -Level 'PASS' -Message "PowerShell compatible (versión: $($psVersion.Major).$($psVersion.Minor))"
    } else {
        Write-Log -Level 'WARN' -Message "PowerShell versión antigua (versión: $($psVersion.Major).$($psVersion.Minor))"
    }
    
    # Verificar integridad de archivos
    Write-Log -Level 'INFO' -Message "Verificando integridad de archivos..."
    
    $criticalFiles = @(
        "wp-cupon-whatsapp-fixed.php",
        "includes\validation-enhanced.php",
        "includes\class-wpcw-auto-config.php",
        "includes\class-wpcw-regional-detector.php",
        "includes\class-wpcw-migration-tools.php"
    )
    
    foreach ($file in $criticalFiles) {
        if (Test-Path $file) {
            $content = Get-Content $file -Raw -ErrorAction SilentlyContinue
            if ($content -and $content.Trim().Length -gt 0) {
                Write-Log -Level 'PASS' -Message "Archivo no vacío: $(Split-Path $file -Leaf)"
            } else {
                Write-Log -Level 'FAIL' -Message "Archivo vacío: $(Split-Path $file -Leaf)"
            }
        }
    }
    
    # Verificar entorno WordPress (opcional)
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
        
        # Verificar funcionalidad de WordPress
        if (Test-CommandAvailable -Command "wp") {
            try {
                $wpCoreVersion = wp core version 2>&1
                if ($wpCoreVersion -match "\d+\.\d+") {
                    Write-Log -Level 'PASS' -Message "WordPress funcional (versión: $wpCoreVersion)"
                } else {
                    Write-Log -Level 'WARN' -Message "WordPress detectado pero WP-CLI no puede conectarse"
                }
            } catch {
                Write-Log -Level 'WARN' -Message "WordPress detectado pero WP-CLI no puede conectarse"
            }
        }
    } else {
        Write-Log -Level 'INFO' -Message "No se detectó entorno WordPress (normal si se ejecuta fuera de WordPress)"
    }
    
    # Mostrar resumen
    Write-Host ""
    Write-Host "=== RESUMEN DE VALIDACIÓN ===" -ForegroundColor $Colors.Blue
    Write-Host "Total de verificaciones: $Global:TotalChecks"
    Write-Host "Exitosas: $Global:PassedChecks" -ForegroundColor $Colors.Green
    Write-Host "Fallidas: $Global:FailedChecks" -ForegroundColor $Colors.Red
    Write-Host "Advertencias: $Global:WarningChecks" -ForegroundColor $Colors.Yellow
    
    if ($Global:TotalChecks -gt 0) {
        $successRate = [math]::Round(($Global:PassedChecks / $Global:TotalChecks) * 100, 2)
        Write-Host "Tasa de éxito: $successRate%"
    }
    
    Write-Host ""
    
    # Determinar resultado final
    if ($Global:FailedChecks -eq 0 -and $Global:WarningChecks -eq 0) {
        Write-Host "🎉 VALIDACIÓN COMPLETADA EXITOSAMENTE" -ForegroundColor $Colors.Green
        Write-Host "El plugin está listo para despliegue masivo." -ForegroundColor $Colors.Green
        exit 0
    } elseif ($Global:FailedChecks -eq 0) {
        Write-Host "⚠️  VALIDACIÓN COMPLETADA CON ADVERTENCIAS" -ForegroundColor $Colors.Yellow
        Write-Host "El plugin puede desplegarse, pero revisa las advertencias." -ForegroundColor $Colors.Yellow
        exit 0
    } else {
        Write-Host "❌ VALIDACIÓN FALLIDA" -ForegroundColor $Colors.Red
        Write-Host "Corrige los errores antes de proceder con el despliegue." -ForegroundColor $Colors.Red
        exit 1
    }
}

# Mostrar ayuda si se solicita
if ($Help) {
    Write-Host "Script de Validación - WP Cupón WhatsApp v1.4.0" -ForegroundColor $Colors.Blue
    Write-Host ""
    Write-Host "Uso: .\validate-deployment.ps1 [-Help]"
    Write-Host ""
    Write-Host "Este script verifica que todos los archivos y configuraciones"
    Write-Host "estén correctos antes del despliegue masivo."
    Write-Host ""
    Write-Host "El script verifica:"
    Write-Host "  - Archivos principales del plugin"
    Write-Host "  - Clases para producción masiva"
    Write-Host "  - Scripts de despliegue"
    Write-Host "  - Configuración JSON"
    Write-Host "  - Documentación"
    Write-Host "  - Sintaxis PHP"
    Write-Host "  - Dependencias del sistema"
    Write-Host ""
    Write-Host "Parámetros:"
    Write-Host "  -Help    Muestra esta ayuda"
    Write-Host ""
    exit 0
}

# Ejecutar validación
Start-Validation