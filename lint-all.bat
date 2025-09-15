@echo off
REM Script de verificación automática de linting para WP Cupón WhatsApp
REM Ejecuta PHPCS, ESLint y Stylelint en todo el proyecto

echo === VERIFICACIÓN AUTOMÁTICA DE LINTING ===
echo Plugin: WP Cupón WhatsApp
echo Fecha: %date% %time%
echo.

REM Verificar si estamos en el directorio correcto
if not exist "wp-cupon-whatsapp.php" (
    echo ERROR: No se encontró wp-cupon-whatsapp.php
    echo Ejecute desde la raíz del plugin.
    pause
    exit /b 1
)

set ERROR_COUNT=0

REM === VERIFICACIÓN PHPCS ===
echo.
echo === VERIFICANDO CÓDIGO PHP CON PHPCS ===

if exist ".phpcs.xml" (
    echo Ejecutando PHPCS con configuración: .phpcs.xml
    
    if exist "vendor\bin\phpcs.bat" (
        vendor\bin\phpcs.bat --standard=.phpcs.xml --report=summary .
        if errorlevel 1 (
            echo ❌ PHPCS: Se encontraron errores
            set /a ERROR_COUNT+=1
        ) else (
            echo ✅ PHPCS: Sin errores encontrados
        )
    ) else (
        echo ⚠️  PHPCS no encontrado. Instale con: composer install
        set /a ERROR_COUNT+=1
    )
) else (
    echo ⚠️  Configuración PHPCS no encontrada: .phpcs.xml
    set /a ERROR_COUNT+=1
)

REM === VERIFICACIÓN ESLINT ===
echo.
echo === VERIFICANDO CÓDIGO JAVASCRIPT CON ESLINT ===

if exist ".eslintrc.js" (
    echo Ejecutando ESLint con configuración: .eslintrc.js
    
    if exist "node_modules\.bin\eslint.cmd" (
        node_modules\.bin\eslint.cmd admin/js/**/*.js public/js/**/*.js elementor/**/*.js
        if errorlevel 1 (
            echo ❌ ESLint: Se encontraron errores
            set /a ERROR_COUNT+=1
        ) else (
            echo ✅ ESLint: Sin errores encontrados
        )
    ) else (
        echo ⚠️  ESLint no encontrado. Instale con: npm install eslint
        set /a ERROR_COUNT+=1
    )
) else (
    echo ⚠️  Configuración ESLint no encontrada: .eslintrc.js
    set /a ERROR_COUNT+=1
)

REM === VERIFICACIÓN STYLELINT ===
echo.
echo === VERIFICANDO CÓDIGO CSS CON STYLELINT ===

if exist ".stylelintrc.json" (
    echo Ejecutando Stylelint con configuración: .stylelintrc.json
    
    if exist "node_modules\.bin\stylelint.cmd" (
        node_modules\.bin\stylelint.cmd admin/css/**/*.css public/css/**/*.css elementor/css/**/*.css
        if errorlevel 1 (
            echo ❌ Stylelint: Se encontraron errores
            set /a ERROR_COUNT+=1
        ) else (
            echo ✅ Stylelint: Sin errores encontrados
        )
    ) else (
        echo ⚠️  Stylelint no encontrado. Instale con: npm install stylelint
        set /a ERROR_COUNT+=1
    )
) else (
    echo ⚠️  Configuración Stylelint no encontrada: .stylelintrc.json
    set /a ERROR_COUNT+=1
)

REM === RESUMEN FINAL ===
echo.
echo === RESUMEN DE VERIFICACIÓN ===
echo Total de herramientas con errores: %ERROR_COUNT%

if %ERROR_COUNT% EQU 0 (
    echo 🎉 ¡TODAS LAS VERIFICACIONES PASARON!
    echo El código cumple con todos los estándares de calidad.
) else (
    echo ⚠️  SE ENCONTRARON PROBLEMAS DE CALIDAD DE CÓDIGO
    echo Revise los errores anteriores y corrija antes de continuar.
)

echo.
echo Presione cualquier tecla para continuar...
pause >nul
exit /b %ERROR_COUNT%