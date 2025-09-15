#!/usr/bin/env pwsh
# Script de verificación automática de linting para WP Cupón WhatsApp
# Ejecuta PHPCS, ESLint y Stylelint en todo el proyecto

Write-Host "=== VERIFICACIÓN AUTOMÁTICA DE LINTING ==="
Write-Host "Plugin: WP Cupón WhatsApp"
Write-Host "Fecha: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
Write-Host ""

# Variables de configuración
$projectRoot = $PSScriptRoot
$phpcsConfig = ".phpcs.xml"
$eslintConfig = ".eslintrc.js"
$stylelintConfig = ".stylelintrc.json"
$logFile = "lint-results.log"

# Función para escribir en log
function Write-Log {
    param([string]$Message)
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    "[$timestamp] $Message" | Out-File -FilePath $logFile -Append
    Write-Host $Message
}

# Limpiar log anterior
if (Test-Path $logFile) {
    Remove-Item $logFile
}

Write-Log "Iniciando verificación de linting..."

# Verificar si estamos en el directorio correcto
if (-not (Test-Path "wp-cupon-whatsapp.php")) {
    Write-Log "ERROR: No se encontró wp-cupon-whatsapp.php. Ejecute desde la raíz del plugin."
    exit 1
}

# Contadores de errores
$phpcsErrors = 0
$eslintErrors = 0
$stylelintErrors = 0
$totalErrors = 0

# === VERIFICACIÓN PHPCS ===
Write-Log ""
Write-Log "=== VERIFICANDO CÓDIGO PHP CON PHPCS ==="

if (Test-Path $phpcsConfig) {
    Write-Log "Ejecutando PHPCS con configuración: $phpcsConfig"
    
    # Verificar si phpcs está disponible
    $phpcsPath = $null
    if (Test-Path "vendor/bin/phpcs.bat") {
        $phpcsPath = "vendor/bin/phpcs.bat"
    } elseif (Get-Command phpcs -ErrorAction SilentlyContinue) {
        $phpcsPath = "phpcs"
    }
    
    if ($phpcsPath) {
        try {
            $phpcsOutput = & $phpcsPath --standard=$phpcsConfig --report=summary . 2>&1
            $phpcsExitCode = $LASTEXITCODE
            
            Write-Log "Salida PHPCS:"
            $phpcsOutput | ForEach-Object { Write-Log "  $_" }
            
            if ($phpcsExitCode -eq 0) {
                Write-Log "✅ PHPCS: Sin errores encontrados"
            } else {
                $phpcsErrors = 1
                Write-Log "❌ PHPCS: Se encontraron errores (código: $phpcsExitCode)"
            }
        } catch {
            Write-Log "❌ Error ejecutando PHPCS: $($_.Exception.Message)"
            $phpcsErrors = 1
        }
    } else {
        Write-Log "⚠️  PHPCS no encontrado. Instale con: composer install"
        $phpcsErrors = 1
    }
} else {
    Write-Log "⚠️  Configuración PHPCS no encontrada: $phpcsConfig"
    $phpcsErrors = 1
}

# === VERIFICACIÓN ESLINT ===
Write-Log ""
Write-Log "=== VERIFICANDO CÓDIGO JAVASCRIPT CON ESLINT ==="

if (Test-Path $eslintConfig) {
    Write-Log "Ejecutando ESLint con configuración: $eslintConfig"
    
    # Buscar archivos JavaScript
    $jsFiles = Get-ChildItem -Path . -Include "*.js" -Recurse | Where-Object { 
        $_.FullName -notmatch "node_modules" -and 
        $_.FullName -notmatch "vendor" -and
        $_.FullName -notmatch "\.min\.js$"
    }
    
    if ($jsFiles.Count -gt 0) {
        Write-Log "Archivos JavaScript encontrados: $($jsFiles.Count)"
        
        # Verificar si eslint está disponible
        $eslintPath = $null
        if (Test-Path "node_modules/.bin/eslint.cmd") {
            $eslintPath = "node_modules/.bin/eslint.cmd"
        } elseif (Get-Command eslint -ErrorAction SilentlyContinue) {
            $eslintPath = "eslint"
        }
        
        if ($eslintPath) {
            try {
                $jsFilesList = $jsFiles | ForEach-Object { $_.FullName }
                $eslintOutput = & $eslintPath $jsFilesList 2>&1
                $eslintExitCode = $LASTEXITCODE
                
                Write-Log "Salida ESLint:"
                $eslintOutput | ForEach-Object { Write-Log "  $_" }
                
                if ($eslintExitCode -eq 0) {
                    Write-Log "✅ ESLint: Sin errores encontrados"
                } else {
                    $eslintErrors = 1
                    Write-Log "❌ ESLint: Se encontraron errores (código: $eslintExitCode)"
                }
            } catch {
                Write-Log "❌ Error ejecutando ESLint: $($_.Exception.Message)"
                $eslintErrors = 1
            }
        } else {
            Write-Log "⚠️  ESLint no encontrado. Instale con: npm install eslint"
            $eslintErrors = 1
        }
    } else {
        Write-Log "ℹ️  No se encontraron archivos JavaScript para verificar"
    }
} else {
    Write-Log "⚠️  Configuración ESLint no encontrada: $eslintConfig"
    $eslintErrors = 1
}

# === VERIFICACIÓN STYLELINT ===
Write-Log ""
Write-Log "=== VERIFICANDO CÓDIGO CSS CON STYLELINT ==="

if (Test-Path $stylelintConfig) {
    Write-Log "Ejecutando Stylelint con configuración: $stylelintConfig"
    
    # Buscar archivos CSS
    $cssFiles = Get-ChildItem -Path . -Include "*.css" -Recurse | Where-Object { 
        $_.FullName -notmatch "node_modules" -and 
        $_.FullName -notmatch "vendor" -and
        $_.FullName -notmatch "\.min\.css$"
    }
    
    if ($cssFiles.Count -gt 0) {
        Write-Log "Archivos CSS encontrados: $($cssFiles.Count)"
        
        # Verificar si stylelint está disponible
        $stylelintPath = $null
        if (Test-Path "node_modules/.bin/stylelint.cmd") {
            $stylelintPath = "node_modules/.bin/stylelint.cmd"
        } elseif (Get-Command stylelint -ErrorAction SilentlyContinue) {
            $stylelintPath = "stylelint"
        }
        
        if ($stylelintPath) {
            try {
                $cssFilesList = $cssFiles | ForEach-Object { $_.FullName }
                $stylelintOutput = & $stylelintPath $cssFilesList 2>&1
                $stylelintExitCode = $LASTEXITCODE
                
                Write-Log "Salida Stylelint:"
                $stylelintOutput | ForEach-Object { Write-Log "  $_" }
                
                if ($stylelintExitCode -eq 0) {
                    Write-Log "✅ Stylelint: Sin errores encontrados"
                } else {
                    $stylelintErrors = 1
                    Write-Log "❌ Stylelint: Se encontraron errores (código: $stylelintExitCode)"
                }
            } catch {
                Write-Log "❌ Error ejecutando Stylelint: $($_.Exception.Message)"
                $stylelintErrors = 1
            }
        } else {
            Write-Log "⚠️  Stylelint no encontrado. Instale con: npm install stylelint"
            $stylelintErrors = 1
        }
    } else {
        Write-Log "ℹ️  No se encontraron archivos CSS para verificar"
    }
} else {
    Write-Log "⚠️  Configuración Stylelint no encontrada: $stylelintConfig"
    $stylelintErrors = 1
}

# === RESUMEN FINAL ===
$totalErrors = $phpcsErrors + $eslintErrors + $stylelintErrors

Write-Log ""
Write-Log "=== RESUMEN DE VERIFICACIÓN ==="
Write-Log "PHPCS (PHP): $(if ($phpcsErrors -eq 0) { '✅ PASÓ' } else { '❌ FALLÓ' })"
Write-Log "ESLint (JS): $(if ($eslintErrors -eq 0) { '✅ PASÓ' } else { '❌ FALLÓ' })"
Write-Log "Stylelint (CSS): $(if ($stylelintErrors -eq 0) { '✅ PASÓ' } else { '❌ FALLÓ' })"
Write-Log ""
Write-Log "Total de herramientas con errores: $totalErrors"

if ($totalErrors -eq 0) {
    Write-Log "🎉 ¡TODAS LAS VERIFICACIONES PASARON!"
    Write-Log "El código cumple con todos los estándares de calidad."
    exit 0
} else {
    Write-Log "⚠️  SE ENCONTRARON PROBLEMAS DE CALIDAD DE CÓDIGO"
    Write-Log "Revise los errores anteriores y corrija antes de continuar."
    Write-Log "Log completo guardado en: $logFile"
    exit 1
}