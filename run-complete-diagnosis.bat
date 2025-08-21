@echo off
setlocal enabledelayedexpansion
chcp 65001 >nul

echo ========================================
echo    DIAGNÓSTICO COMPLETO WP-CUPON-WHATSAPP
echo ========================================
echo.

set "TIMESTAMP=%date:~-4,4%-%date:~-10,2%-%date:~-7,2%_%time:~0,2%-%time:~3,2%-%time:~6,2%"
set "TIMESTAMP=!TIMESTAMP: =0!"
set "REPORT_FILE=diagnostic-report-!TIMESTAMP!.txt"

echo Generando reporte: %REPORT_FILE%
echo.

echo ======================================== > "%REPORT_FILE%"
echo REPORTE DE DIAGNÓSTICO COMPLETO >> "%REPORT_FILE%"
echo Fecha: %date% %time% >> "%REPORT_FILE%"
echo ======================================== >> "%REPORT_FILE%"
echo. >> "%REPORT_FILE%"

echo [1/6] Verificando estructura de archivos...
echo === VERIFICACIÓN DE ESTRUCTURA === >> "%REPORT_FILE%"
dir /b *.php >> "%REPORT_FILE%" 2>&1
echo. >> "%REPORT_FILE%"

echo [2/6] Ejecutando verificación de sintaxis final...
echo === VERIFICACIÓN DE SINTAXIS === >> "%REPORT_FILE%"
if exist "verify-syntax-final.bat" (
    call "verify-syntax-final.bat" >> "%REPORT_FILE%" 2>&1
) else (
    echo ERROR: verify-syntax-final.bat no encontrado >> "%REPORT_FILE%"
)
echo. >> "%REPORT_FILE%"

echo [3/6] Ejecutando test de activación de plugin...
echo === TEST DE ACTIVACIÓN === >> "%REPORT_FILE%"
if exist "plugin-activation-tester.php" (
    php "plugin-activation-tester.php" >> "%REPORT_FILE%" 2>&1
) else (
    echo ERROR: plugin-activation-tester.php no encontrado >> "%REPORT_FILE%"
)
echo. >> "%REPORT_FILE%"

echo [4/6] Verificando archivos críticos del plugin...
echo === ARCHIVOS CRÍTICOS === >> "%REPORT_FILE%"
echo Verificando wp-cupon-whatsapp.php: >> "%REPORT_FILE%"
if exist "wp-cupon-whatsapp.php" (
    echo [OK] Archivo principal encontrado >> "%REPORT_FILE%"
    php -l "wp-cupon-whatsapp.php" >> "%REPORT_FILE%" 2>&1
) else (
    echo [ERROR] Archivo principal NO encontrado >> "%REPORT_FILE%"
)

echo Verificando debug-headers.php: >> "%REPORT_FILE%"
if exist "debug-headers.php" (
    echo [OK] Archivo debug-headers encontrado >> "%REPORT_FILE%"
    php -l "debug-headers.php" >> "%REPORT_FILE%" 2>&1
) else (
    echo [ERROR] Archivo debug-headers NO encontrado >> "%REPORT_FILE%"
)

echo Verificando wp-cupon-whatsapp-minimal.php: >> "%REPORT_FILE%"
if exist "wp-cupon-whatsapp-minimal.php" (
    echo [OK] Archivo minimal encontrado >> "%REPORT_FILE%"
    php -l "wp-cupon-whatsapp-minimal.php" >> "%REPORT_FILE%" 2>&1
) else (
    echo [ERROR] Archivo minimal NO encontrado >> "%REPORT_FILE%"
)
echo. >> "%REPORT_FILE%"

echo [5/6] Verificando directorios del plugin...
echo === ESTRUCTURA DE DIRECTORIOS === >> "%REPORT_FILE%"
echo Directorio includes: >> "%REPORT_FILE%"
if exist "includes" (
    echo [OK] Directorio includes encontrado >> "%REPORT_FILE%"
    dir /b "includes\*.php" >> "%REPORT_FILE%" 2>&1
) else (
    echo [ERROR] Directorio includes NO encontrado >> "%REPORT_FILE%"
)

echo Directorio admin: >> "%REPORT_FILE%"
if exist "admin" (
    echo [OK] Directorio admin encontrado >> "%REPORT_FILE%"
    dir /b "admin\*.php" >> "%REPORT_FILE%" 2>&1
) else (
    echo [ERROR] Directorio admin NO encontrado >> "%REPORT_FILE%"
)

echo Directorio assets: >> "%REPORT_FILE%"
if exist "assets" (
    echo [OK] Directorio assets encontrado >> "%REPORT_FILE%"
    dir /b "assets" >> "%REPORT_FILE%" 2>&1
) else (
    echo [WARNING] Directorio assets NO encontrado >> "%REPORT_FILE%"
)
echo. >> "%REPORT_FILE%"

echo [6/6] Generando resumen de errores...
echo === RESUMEN DE ERRORES === >> "%REPORT_FILE%"
echo Buscando errores en archivos de log... >> "%REPORT_FILE%"
if exist "*.log" (
    for %%f in (*.log) do (
        echo --- Contenido de %%f --- >> "%REPORT_FILE%"
        type "%%f" >> "%REPORT_FILE%" 2>&1
        echo. >> "%REPORT_FILE%"
    )
) else (
    echo No se encontraron archivos de log >> "%REPORT_FILE%"
)

echo === RECOMENDACIONES === >> "%REPORT_FILE%"
echo 1. Revisar errores de sintaxis en archivos PHP >> "%REPORT_FILE%"
echo 2. Verificar que todos los archivos críticos existan >> "%REPORT_FILE%"
echo 3. Comprobar permisos de archivos en el servidor >> "%REPORT_FILE%"
echo 4. Revisar logs de WordPress para errores adicionales >> "%REPORT_FILE%"
echo 5. Probar activación con plugin minimal >> "%REPORT_FILE%"
echo. >> "%REPORT_FILE%"

echo ======================================== >> "%REPORT_FILE%"
echo FIN DEL REPORTE >> "%REPORT_FILE%"
echo ======================================== >> "%REPORT_FILE%"

echo.
echo ========================================
echo    DIAGNÓSTICO COMPLETADO
echo ========================================
echo.
echo Reporte generado: %REPORT_FILE%
echo.
echo PRÓXIMOS PASOS:
echo 1. Revisar el reporte generado
echo 2. Identificar errores específicos
echo 3. Aplicar correcciones necesarias
echo 4. Probar activación del plugin
echo.
echo Presiona cualquier tecla para abrir el reporte...
pause >nul
notepad "%REPORT_FILE%"

endlocal