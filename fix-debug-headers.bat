@echo off
echo ========================================
echo SOLUCION DEFINITIVA - DEBUG HEADERS
echo Plugin: WP-Cupon-Whatsapp
echo ========================================
echo.

echo Creando backup del archivo problemático...
if exist "debug-headers.php" (
    copy "debug-headers.php" "debug-headers-backup-%date:~-4,4%%date:~-10,2%%date:~-7,2%-%time:~0,2%%time:~3,2%%time:~6,2%.php" >nul
    echo Backup creado: debug-headers-backup-%date:~-4,4%%date:~-10,2%%date:~-7,2%-%time:~0,2%%time:~3,2%%time:~6,2%.php
) else (
    echo ADVERTENCIA: debug-headers.php no encontrado
)

echo.
echo Reemplazando con versión limpia...
if exist "debug-headers-clean.php" (
    copy "debug-headers-clean.php" "debug-headers.php" >nul
    echo ✓ debug-headers.php reemplazado con versión limpia
    echo.
    echo ARCHIVOS DISPONIBLES:
    echo ========================================
    echo ✓ debug-headers.php (VERSIÓN LIMPIA - USAR ESTA)
    echo ✓ debug-headers-clean.php (RESPALDO DE LA VERSIÓN LIMPIA)
    echo ✓ debug-headers-test.php (VERSIÓN DE PRUEBA SIMPLE)
    if exist "debug-headers-backup-*.php" (
        echo ✓ debug-headers-backup-*.php (BACKUP DEL ARCHIVO PROBLEMÁTICO)
    )
    echo.
    echo PRÓXIMOS PASOS:
    echo ========================================
    echo 1. Subir debug-headers.php (versión limpia) al servidor
    echo 2. Activar el plugin en WordPress
    echo 3. Verificar que no hay errores
    echo.
    echo El archivo debug-headers.php ahora está completamente limpio
    echo y libre de errores de sintaxis.
    echo.
) else (
    echo ERROR: debug-headers-clean.php no encontrado
    echo No se pudo realizar el reemplazo
    echo.
    echo SOLUCIÓN MANUAL:
    echo 1. Verificar que debug-headers-clean.php existe
    echo 2. Renombrar debug-headers-clean.php a debug-headers.php
    echo 3. Subir al servidor
)

echo.
echo Presiona cualquier tecla para continuar...
pause >nul