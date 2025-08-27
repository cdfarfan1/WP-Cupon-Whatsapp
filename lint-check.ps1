# Script de verificación de linting para WP Cupón WhatsApp
param(
    [switch]$Fix,
    [switch]$Verbose
)

# Verificar que estamos en el directorio correcto
if (-not (Test-Path ".phpcs.xml")) {
    Write-Host "❌ Error: No se encontró .phpcs.xml. Ejecuta este script desde el directorio raíz del plugin." -ForegroundColor Red
    exit 1
}

Write-Host "🔍 Iniciando verificación de linting para WP Cupón WhatsApp" -ForegroundColor Cyan
Write-Host ("=" * 60) -ForegroundColor Cyan

$totalErrors = 0
$totalWarnings = 0

# Verificar PHP con PHPCS
Write-Host "`n📋 Verificando archivos PHP con PHPCS..." -ForegroundColor Yellow

try {
    $phpcsExists = Get-Command "phpcs" -ErrorAction Stop
    
    if ($Fix) {
        try {
            $phpcbfExists = Get-Command "phpcbf" -ErrorAction Stop
            Write-Host "🔧 Intentando corregir problemas PHP automáticamente..." -ForegroundColor Yellow
            & phpcbf --standard=.phpcs.xml --extensions=php --ignore=*/vendor/*,*/node_modules/*,*/debug-*,*/test-*,*/isolated-* .
        } catch {
            Write-Host "⚠️  phpcbf no encontrado." -ForegroundColor Yellow
        }
    }
    
    $phpcsResult = & phpcs --standard=.phpcs.xml --extensions=php --ignore=*/vendor/*,*/node_modules/*,*/debug-*,*/test-*,*/isolated-* . --report=summary 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ PHP: Sin problemas encontrados" -ForegroundColor Green
    } else {
        Write-Host "❌ PHP: Problemas encontrados" -ForegroundColor Red
        if ($Verbose) {
            $phpcsResult | ForEach-Object { Write-Host $_ -ForegroundColor Red }
        }
        $totalErrors++
    }
} catch {
    Write-Host "⚠️  PHPCS no encontrado. Instala PHP_CodeSniffer: composer global require squizlabs/php_codesniffer" -ForegroundColor Yellow
    $totalWarnings++
}

# Verificar JavaScript con ESLint
Write-Host "`n📋 Verificando archivos JavaScript con ESLint..." -ForegroundColor Yellow

try {
    $eslintExists = Get-Command "eslint" -ErrorAction Stop
    
    if ($Fix) {
        Write-Host "🔧 Intentando corregir problemas JavaScript automáticamente..." -ForegroundColor Yellow
        & eslint "**/*.js" --ignore-pattern "node_modules/" --ignore-pattern "vendor/" --ignore-pattern "*.min.js" --fix
    }
    
    $eslintResult = & eslint "**/*.js" --ignore-pattern "node_modules/" --ignore-pattern "vendor/" --ignore-pattern "*.min.js" 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ JavaScript: Sin problemas encontrados" -ForegroundColor Green
    } else {
        Write-Host "❌ JavaScript: Problemas encontrados" -ForegroundColor Red
        if ($Verbose) {
            $eslintResult | ForEach-Object { Write-Host $_ -ForegroundColor Red }
        }
        $totalErrors++
    }
} catch {
    Write-Host "⚠️  ESLint no encontrado. Instala ESLint: npm install -g eslint" -ForegroundColor Yellow
    $totalWarnings++
}

# Verificar CSS con Stylelint
Write-Host "`n📋 Verificando archivos CSS con Stylelint..." -ForegroundColor Yellow

try {
    $stylelintExists = Get-Command "stylelint" -ErrorAction Stop
    
    if ($Fix) {
        Write-Host "🔧 Intentando corregir problemas CSS automáticamente..." -ForegroundColor Yellow
        & stylelint "**/*.css" --ignore-pattern "node_modules/" --ignore-pattern "vendor/" --ignore-pattern "*.min.css" --fix
    }
    
    $stylelintResult = & stylelint "**/*.css" --ignore-pattern "node_modules/" --ignore-pattern "vendor/" --ignore-pattern "*.min.css" 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ CSS: Sin problemas encontrados" -ForegroundColor Green
    } else {
        Write-Host "❌ CSS: Problemas encontrados" -ForegroundColor Red
        if ($Verbose) {
            $stylelintResult | ForEach-Object { Write-Host $_ -ForegroundColor Red }
        }
        $totalErrors++
    }
} catch {
    Write-Host "⚠️  Stylelint no encontrado. Instala Stylelint: npm install -g stylelint stylelint-config-standard stylelint-order" -ForegroundColor Yellow
    $totalWarnings++
}

# Resumen final
Write-Host ("`n" + ("=" * 60)) -ForegroundColor Cyan
Write-Host "📊 Resumen de verificación de linting:" -ForegroundColor Cyan

if ($totalErrors -eq 0 -and $totalWarnings -eq 0) {
    Write-Host "🎉 ¡Excelente! Todos los archivos cumplen con los estándares de linting." -ForegroundColor Green
    exit 0
} elseif ($totalErrors -eq 0) {
    Write-Host "⚠️  Verificación completada con $totalWarnings advertencias." -ForegroundColor Yellow
    Write-Host "💡 Considera instalar las herramientas faltantes para una verificación completa." -ForegroundColor Yellow
    exit 0
} else {
    Write-Host "❌ Se encontraron $totalErrors errores de linting." -ForegroundColor Red
    if ($totalWarnings -gt 0) {
        Write-Host "⚠️  También hay $totalWarnings advertencias." -ForegroundColor Yellow
    }
    Write-Host "💡 Ejecuta con -Fix para intentar correcciones automáticas." -ForegroundColor Yellow
    Write-Host "💡 Ejecuta con -Verbose para ver detalles de los errores." -ForegroundColor Yellow
    exit 1
}