# Script de Despliegue Masivo - WP Cupón WhatsApp v1.4.0
# PowerShell Script para Windows
# Automatiza la instalación y configuración en múltiples sitios WordPress

param(
    [string]$SitesFile,
    [string]$Profile = "standard",
    [string]$Optimization = "standard",
    [string]$ConfigFile = "",
    [switch]$TestMode,
    [switch]$Verbose,
    [switch]$Help,
    [switch]$Example
)

# Configuración
$Script:PluginSlug = "wp-cupon-whatsapp"
$Script:LogFile = "deployment-$(Get-Date -Format 'yyyyMMdd-HHmmss').log"
$Script:DefaultProfile = $Profile
$Script:DefaultOptimization = $Optimization

# Colores para output
$Script:Colors = @{
    Red = "Red"
    Green = "Green"
    Yellow = "Yellow"
    Blue = "Blue"
    White = "White"
}

# Función para mostrar ayuda
function Show-Help {
    Write-Host "WP Cupón WhatsApp - Script de Despliegue Masivo" -ForegroundColor Blue
    Write-Host "================================================" -ForegroundColor Blue
    Write-Host ""
    Write-Host "Uso: .\mass-deployment.ps1 [PARÁMETROS]"
    Write-Host ""
    Write-Host "PARÁMETROS:"
    Write-Host "  -SitesFile <archivo>          Archivo con lista de sitios"
    Write-Host "  -Profile <perfil>             Perfil de instalación (minimal|standard|enterprise|development)"
    Write-Host "  -Optimization <nivel>         Nivel de optimización (basic|standard|aggressive)"
    Write-Host "  -ConfigFile <archivo>         Archivo de configuración personalizada"
    Write-Host "  -TestMode                     Modo de prueba (no ejecuta cambios reales)"
    Write-Host "  -Verbose                      Salida detallada"
    Write-Host "  -Help                         Mostrar esta ayuda"
    Write-Host "  -Example                      Crear archivo de ejemplo"
    Write-Host ""
    Write-Host "EJEMPLOS:"
    Write-Host "  .\mass-deployment.ps1 -SitesFile sitios.txt"
    Write-Host "  .\mass-deployment.ps1 -SitesFile sitios.txt -Profile enterprise -Optimization aggressive"
    Write-Host "  .\mass-deployment.ps1 -SitesFile sitios.txt -TestMode -Verbose"
    Write-Host ""
}

# Función para logging
function Write-Log {
    param(
        [string]$Level,
        [string]$Message
    )
    
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logEntry = "[$timestamp] [$Level] $Message"
    
    switch ($Level) {
        "INFO" {
            Write-Host "[INFO] $Message" -ForegroundColor Green
        }
        "WARN" {
            Write-Host "[WARN] $Message" -ForegroundColor Yellow
        }
        "ERROR" {
            Write-Host "[ERROR] $Message" -ForegroundColor Red
        }
        "DEBUG" {
            if ($Verbose) {
                Write-Host "[DEBUG] $Message" -ForegroundColor Blue
            }
        }
    }
    
    Add-Content -Path $Script:LogFile -Value $logEntry
}

# Función para verificar dependencias
function Test-Dependencies {
    Write-Log "INFO" "Verificando dependencias..."
    
    # Verificar WP-CLI
    try {
        $wpVersion = wp --version 2>$null
        if (-not $wpVersion) {
            throw "WP-CLI no encontrado"
        }
        Write-Log "DEBUG" "WP-CLI encontrado: $wpVersion"
    }
    catch {
        Write-Log "ERROR" "WP-CLI no está instalado. Descárgalo desde https://wp-cli.org/"
        return $false
    }
    
    # Verificar PowerShell version
    if ($PSVersionTable.PSVersion.Major -lt 5) {
        Write-Log "WARN" "Se recomienda PowerShell 5.0 o superior"
    }
    
    Write-Log "INFO" "✅ Dependencias verificadas"
    return $true
}

# Función para validar sitio
function Test-Site {
    param([string]$SiteUrl)
    
    Write-Log "DEBUG" "Validando sitio: $SiteUrl"
    
    try {
        # Verificar que el sitio responde
        $response = Invoke-WebRequest -Uri $SiteUrl -Method Head -TimeoutSec 30 -ErrorAction Stop
        if ($response.StatusCode -ne 200) {
            Write-Log "WARN" "El sitio $SiteUrl no responde correctamente (Status: $($response.StatusCode))"
            return $false
        }
        
        # Verificar que es un sitio WordPress
        $wpCheck = wp --url="$SiteUrl" core version 2>$null
        if (-not $wpCheck) {
            Write-Log "WARN" "$SiteUrl no parece ser un sitio WordPress válido"
            return $false
        }
        
        Write-Log "DEBUG" "Sitio válido: $SiteUrl (WordPress $wpCheck)"
        return $true
    }
    catch {
        Write-Log "WARN" "Error al validar sitio $SiteUrl : $($_.Exception.Message)"
        return $false
    }
}

# Función para instalar plugin en un sitio
function Install-PluginOnSite {
    param(
        [string]$SiteUrl,
        [string]$Profile,
        [string]$Optimization
    )
    
    Write-Log "INFO" "🚀 Instalando en $SiteUrl (Perfil: $Profile, Optimización: $Optimization)"
    
    if ($TestMode) {
        Write-Log "INFO" "[MODO PRUEBA] Simulando instalación en $SiteUrl"
        return $true
    }
    
    try {
        # Verificar si el plugin ya está instalado
        $pluginInstalled = wp --url="$SiteUrl" plugin is-installed $Script:PluginSlug 2>$null
        
        if ($pluginInstalled) {
            Write-Log "INFO" "Plugin ya está instalado en $SiteUrl, actualizando..."
            $updateResult = wp --url="$SiteUrl" plugin update $Script:PluginSlug 2>&1
            if ($LASTEXITCODE -ne 0) {
                Write-Log "ERROR" "Error al actualizar plugin en $SiteUrl : $updateResult"
                return $false
            }
        }
        else {
            # Instalar plugin
            Write-Log "DEBUG" "Instalando plugin en $SiteUrl"
            $installResult = wp --url="$SiteUrl" plugin install $Script:PluginSlug --activate 2>&1
            if ($LASTEXITCODE -ne 0) {
                Write-Log "ERROR" "Error al instalar plugin en $SiteUrl : $installResult"
                return $false
            }
        }
        
        # Ejecutar instalación automática
        Write-Log "DEBUG" "Ejecutando instalación automática con perfil $Profile"
        $autoInstallScript = @"
if (class_exists('WPCW_Auto_Installer')) {
    `$installer = WPCW_Auto_Installer::get_instance();
    `$result = `$installer->auto_install('$Profile');
    if (`$result['success']) {
        echo 'Instalación completada exitosamente';
    } else {
        echo 'Error en instalación: ' . `$result['message'];
        exit(1);
    }
} else {
    echo 'Clase WPCW_Auto_Installer no disponible';
    exit(1);
}
"@
        
        $installResult = wp --url="$SiteUrl" eval $autoInstallScript 2>&1
        if ($LASTEXITCODE -ne 0) {
            Write-Log "ERROR" "Error en instalación automática en $SiteUrl : $installResult"
            return $false
        }
        
        # Aplicar optimización
        if ($Optimization -ne "none") {
            Write-Log "DEBUG" "Aplicando optimización $Optimization"
            $optimizeScript = @"
if (class_exists('WPCW_Performance_Optimizer')) {
    `$optimizer = WPCW_Performance_Optimizer::get_instance();
    `$result = `$optimizer->apply_optimization_profile('$Optimization');
    if (`$result['success']) {
        echo 'Optimización aplicada exitosamente';
    } else {
        echo 'Error en optimización: ' . `$result['message'];
    }
}
"@
            
            $optimizeResult = wp --url="$SiteUrl" eval $optimizeScript 2>&1
            if ($LASTEXITCODE -ne 0) {
                Write-Log "WARN" "Error al aplicar optimización en $SiteUrl : $optimizeResult"
            }
        }
        
        # Verificar instalación
        $statusScript = @"
if (get_option('wpcw_installation_completed')) {
    echo 'completed';
} else {
    echo 'incomplete';
}
"@
        
        $status = wp --url="$SiteUrl" eval $statusScript 2>$null
        
        if ($status -eq "completed") {
            Write-Log "INFO" "✅ Instalación completada exitosamente en $SiteUrl"
            return $true
        }
        else {
            Write-Log "ERROR" "❌ Instalación incompleta en $SiteUrl"
            return $false
        }
    }
    catch {
        Write-Log "ERROR" "Error inesperado en $SiteUrl : $($_.Exception.Message)"
        return $false
    }
}

# Función para procesar archivo de sitios
function Invoke-SitesProcessing {
    param([string]$SitesFile)
    
    if (-not (Test-Path $SitesFile)) {
        Write-Log "ERROR" "Archivo de sitios no encontrado: $SitesFile"
        return $false
    }
    
    $sites = Get-Content $SitesFile | Where-Object { $_ -notmatch '^#' -and $_ -ne '' }
    $totalSites = $sites.Count
    $successfulSites = 0
    $failedSites = 0
    
    Write-Log "INFO" "Procesando $totalSites sitios..."
    
    for ($i = 0; $i -lt $sites.Count; $i++) {
        $line = $sites[$i]
        $parts = $line -split '\s+'
        
        $siteUrl = $parts[0]
        $siteProfile = if ($parts.Count -gt 1) { $parts[1] } else { $Script:DefaultProfile }
        $siteOptimization = if ($parts.Count -gt 2) { $parts[2] } else { $Script:DefaultOptimization }
        
        Write-Log "INFO" "Procesando sitio $($i + 1)/$totalSites : $siteUrl"
        
        # Validar sitio
        if (-not (Test-Site $siteUrl)) {
            Write-Log "ERROR" "Sitio no válido, saltando: $siteUrl"
            $failedSites++
            continue
        }
        
        # Instalar plugin
        if (Install-PluginOnSite $siteUrl $siteProfile $siteOptimization) {
            $successfulSites++
        }
        else {
            $failedSites++
        }
        
        # Pausa entre sitios
        Start-Sleep -Seconds 2
    }
    
    # Resumen final
    Write-Log "INFO" "=== RESUMEN DEL DESPLIEGUE ==="
    Write-Log "INFO" "Total de sitios: $totalSites"
    Write-Log "INFO" "Exitosos: $successfulSites"
    Write-Log "INFO" "Fallidos: $failedSites"
    
    if ($totalSites -gt 0) {
        $successRate = [math]::Round(($successfulSites * 100) / $totalSites, 2)
        Write-Log "INFO" "Tasa de éxito: $successRate%"
    }
    
    if ($failedSites -gt 0) {
        Write-Log "WARN" "Algunos sitios fallaron. Revisa el log: $Script:LogFile"
        return $false
    }
    
    return $true
}

# Función para crear archivo de ejemplo
function New-ExampleSitesFile {
    $exampleFile = "sitios-ejemplo.txt"
    
    $exampleContent = @"
# Archivo de ejemplo para despliegue masivo
# Formato: URL [perfil] [optimización]
# 
# Perfiles disponibles: minimal, standard, enterprise, development
# Optimizaciones disponibles: basic, standard, aggressive, none
#
# Ejemplos:
https://sitio1.com standard standard
https://sitio2.com enterprise aggressive
https://sitio3.com minimal basic
https://sitio4.com  # Usará valores por defecto

# Sitios de desarrollo
https://dev.sitio1.com development none
https://staging.sitio2.com standard basic
"@
    
    Set-Content -Path $exampleFile -Value $exampleContent -Encoding UTF8
    Write-Log "INFO" "Archivo de ejemplo creado: $exampleFile"
}

# Función para generar reporte
function New-DeploymentReport {
    $reportFile = "deployment-report-$(Get-Date -Format 'yyyyMMdd-HHmmss').html"
    
    $reportContent = @"
<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Despliegue Masivo - WP Cupón WhatsApp</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: #0073aa; color: white; padding: 20px; border-radius: 5px; }
        .success { color: #46b450; }
        .error { color: #dc3232; }
        .warning { color: #ffb900; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .config-box { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Despliegue Masivo</h1>
        <p>WP Cupón WhatsApp v1.4.0 - $(Get-Date)</p>
    </div>
    
    <h2>Resumen</h2>
    <p>Log completo disponible en: <code>$Script:LogFile</code></p>
    
    <div class="config-box">
        <h3>Configuración Utilizada</h3>
        <ul>
            <li><strong>Perfil por defecto:</strong> $Script:DefaultProfile</li>
            <li><strong>Optimización por defecto:</strong> $Script:DefaultOptimization</li>
            <li><strong>Modo de prueba:</strong> $(if ($TestMode) { "Sí" } else { "No" })</li>
            <li><strong>Salida detallada:</strong> $(if ($Verbose) { "Sí" } else { "No" })</li>
        </ul>
    </div>
    
    <h2>Detalles</h2>
    <p>Para ver los detalles completos del despliegue, consulta el archivo de log: <code>$Script:LogFile</code></p>
    
    <h2>Próximos Pasos</h2>
    <ul>
        <li>Verificar que todos los sitios estén funcionando correctamente</li>
        <li>Configurar monitoreo de rendimiento</li>
        <li>Programar backups regulares</li>
        <li>Revisar logs de errores periódicamente</li>
    </ul>
</body>
</html>
"@
    
    Set-Content -Path $reportFile -Value $reportContent -Encoding UTF8
    Write-Log "INFO" "Reporte HTML generado: $reportFile"
}

# Función principal
function Main {
    # Mostrar ayuda si se solicita
    if ($Help) {
        Show-Help
        return
    }
    
    # Crear archivo de ejemplo si se solicita
    if ($Example) {
        New-ExampleSitesFile
        return
    }
    
    # Verificar que se proporcionó archivo de sitios
    if (-not $SitesFile) {
        Write-Log "ERROR" "Debe especificar un archivo de sitios con -SitesFile"
        Show-Help
        return
    }
    
    # Mostrar configuración
    Write-Log "INFO" "=== CONFIGURACIÓN DE DESPLIEGUE ==="
    Write-Log "INFO" "Perfil por defecto: $Script:DefaultProfile"
    Write-Log "INFO" "Optimización por defecto: $Script:DefaultOptimization"
    Write-Log "INFO" "Archivo de sitios: $SitesFile"
    Write-Log "INFO" "Modo de prueba: $(if ($TestMode) { "Activado" } else { "Desactivado" })"
    Write-Log "INFO" "Salida detallada: $(if ($Verbose) { "Activada" } else { "Desactivada" })"
    Write-Log "INFO" "Archivo de log: $Script:LogFile"
    Write-Log "INFO" "======================================"
    
    # Verificar dependencias
    if (-not (Test-Dependencies)) {
        Write-Log "ERROR" "Faltan dependencias requeridas"
        return
    }
    
    # Procesar sitios
    $success = Invoke-SitesProcessing $SitesFile
    
    # Generar reporte
    New-DeploymentReport
    
    if ($success) {
        Write-Log "INFO" "🎉 Despliegue masivo completado exitosamente"
    }
    else {
        Write-Log "ERROR" "❌ Despliegue masivo completado con errores"
    }
}

# Ejecutar función principal
Main