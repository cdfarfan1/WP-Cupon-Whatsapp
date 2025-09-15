@echo off
setlocal enabledelayedexpansion

echo ========================================
echo  WP Cupon WhatsApp - Verificacion de Codigo
echo ========================================
echo.

set "errors=0"
set "warnings=0"

REM Verificar si existe composer.phar
if exist "composer.phar" (
    echo [INFO] Usando composer.phar local
    set "composer_cmd=php composer.phar"
) else (
    echo [INFO] Intentando usar composer global
    set "composer_cmd=composer"
)

REM Verificar si las dependencias están instaladas
if not exist "vendor\bin\phpcs.bat" (
    echo [WARN] PHPCS no encontrado. Instalando dependencias...
    %composer_cmd% install --dev
    if errorlevel 1 (
        echo [ERROR] No se pudieron instalar las dependencias de Composer
        set /a "errors+=1"
        goto :summary
    )
)

REM Ejecutar PHPCS si está disponible
if exist "vendor\bin\phpcs.bat" (
    echo.
    echo [1/3] Ejecutando PHP_CodeSniffer...
    echo ----------------------------------------
    vendor\bin\phpcs --standard=.phpcs.xml --report=summary .
    if errorlevel 1 (
        echo [WARN] Se encontraron problemas de estilo en PHP
        set /a "warnings+=1"
    ) else (
        echo [OK] Codigo PHP cumple con los estándares
    )
) else (
    echo [SKIP] PHPCS no disponible
    set /a "warnings+=1"
)

REM Ejecutar ESLint si hay archivos JS
echo.
echo [2/3] Verificando archivos JavaScript...
echo ----------------------------------------
dir /s /b *.js >nul 2>&1
if errorlevel 1 (
    echo [SKIP] No se encontraron archivos JavaScript
) else (
    eslint **/*.js 2>nul
    if errorlevel 1 (
        echo [WARN] ESLint no disponible o problemas encontrados
        set /a "warnings+=1"
    ) else (
        echo [OK] Archivos JavaScript verificados
    )
)

REM Ejecutar Stylelint si hay archivos CSS
echo.
echo [3/3] Verificando archivos CSS...
echo ----------------------------------------
dir /s /b *.css >nul 2>&1
if errorlevel 1 (
    echo [SKIP] No se encontraron archivos CSS
) else (
    stylelint **/*.css 2>nul
    if errorlevel 1 (
        echo [WARN] Stylelint no disponible o problemas encontrados
        set /a "warnings+=1"
    ) else (
        echo [OK] Archivos CSS verificados
    )
)

:summary
echo.
echo ========================================
echo  RESUMEN DE VERIFICACION
echo ========================================
echo Errores: %errors%
echo Advertencias: %warnings%

if %errors% gtr 0 (
    echo.
    echo [ERROR] La verificacion fallo con errores criticos
    exit /b 1
) else if %warnings% gtr 0 (
    echo.
    echo [WARN] La verificacion completo con advertencias
    exit /b 0
) else (
    echo.
    echo [OK] Verificacion completada exitosamente
    exit /b 0
)