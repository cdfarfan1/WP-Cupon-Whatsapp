@echo off
setlocal enabledelayedexpansion

echo ========================================
echo    PREPARACION PARA TESTING LOCAL
echo    Plugin: WP-Cupon-Whatsapp
echo    Entorno: https://localhost/webstore
echo ========================================
echo.

set "PLUGIN_NAME=wp-cupon-whatsapp"
set "LOCAL_WP_PATH=C:\xampp\htdocs\webstore"
set "PLUGIN_DEST=%LOCAL_WP_PATH%\wp-content\plugins\%PLUGIN_NAME%"
set "CURRENT_DIR=%~dp0"

echo [PASO 1] Verificando entorno local de WordPress...
if not exist "%LOCAL_WP_PATH%" (
    echo ERROR: No se encontro WordPress en %LOCAL_WP_PATH%
    echo Ajusta la variable LOCAL_WP_PATH en este script
    pause
    exit /b 1
)
echo WordPress encontrado en %LOCAL_WP_PATH%

if not exist "%LOCAL_WP_PATH%\wp-content\plugins" (
    echo ERROR: Directorio de plugins no encontrado
    pause
    exit /b 1
)
echo Directorio de plugins encontrado
echo.

echo [PASO 2] Preparando directorio del plugin...
if exist "%PLUGIN_DEST%" (
    echo Plugin ya existe. Creando backup...
    if exist "%PLUGIN_DEST%-backup" (
        rmdir /s /q "%PLUGIN_DEST%-backup"
    )
    move "%PLUGIN_DEST%" "%PLUGIN_DEST%-backup"
    echo Backup creado en %PLUGIN_NAME%-backup
)

mkdir "%PLUGIN_DEST%" 2>nul
echo Directorio del plugin preparado
echo.

echo [PASO 3] Copiando archivos del plugin...
echo Copiando archivos principales...
copy "%CURRENT_DIR%wp-cupon-whatsapp.php" "%PLUGIN_DEST%\" >nul
copy "%CURRENT_DIR%readme.txt" "%PLUGIN_DEST%\" >nul
copy "%CURRENT_DIR%LICENSE" "%PLUGIN_DEST%\" >nul

echo Copiando archivos de diagnostico...
copy "%CURRENT_DIR%debug-headers.php" "%PLUGIN_DEST%\" >nul
copy "%CURRENT_DIR%wp-cupon-whatsapp-minimal.php" "%PLUGIN_DEST%\" >nul

echo Copiando directorios...
xcopy "%CURRENT_DIR%admin" "%PLUGIN_DEST%\admin\" /E /I /Q >nul
xcopy "%CURRENT_DIR%includes" "%PLUGIN_DEST%\includes\" /E /I /Q >nul
xcopy "%CURRENT_DIR%public" "%PLUGIN_DEST%\public\" /E /I /Q >nul
xcopy "%CURRENT_DIR%languages" "%PLUGIN_DEST%\languages\" /E /I /Q >nul
xcopy "%CURRENT_DIR%templates" "%PLUGIN_DEST%\templates\" /E /I /Q >nul
xcopy "%CURRENT_DIR%elementor" "%PLUGIN_DEST%\elementor\" /E /I /Q >nul

echo Archivos copiados exitosamente
echo.

echo [PASO 4] Verificando integridad de archivos...
set "ERROR_COUNT=0"

if not exist "%PLUGIN_DEST%\wp-cupon-whatsapp.php" (
    echo Falta: wp-cupon-whatsapp.php
    set /a ERROR_COUNT+=1
)

if not exist "%PLUGIN_DEST%\includes\post-types.php" (
    echo Falta: includes\post-types.php
    set /a ERROR_COUNT+=1
)

if not exist "%PLUGIN_DEST%\admin\admin-menu.php" (
    echo Falta: admin\admin-menu.php
    set /a ERROR_COUNT+=1
)

if !ERROR_COUNT! equ 0 (
    echo Todos los archivos criticos estan presentes
) else (
    echo Se encontraron !ERROR_COUNT! archivos faltantes
)
echo.

echo [PASO 5] Creando archivo de configuracion de testing...
echo ^<?php > "%PLUGIN_DEST%\testing-config.php"
echo // Configuracion para testing local >> "%PLUGIN_DEST%\testing-config.php"
echo define('WPCW_TESTING_MODE', true); >> "%PLUGIN_DEST%\testing-config.php"
echo define('WPCW_LOCAL_TESTING', true); >> "%PLUGIN_DEST%\testing-config.php"
echo define('WPCW_DEBUG_MODE', true); >> "%PLUGIN_DEST%\testing-config.php"
echo ?^> >> "%PLUGIN_DEST%\testing-config.php"
echo Archivo de configuracion de testing creado
echo.

echo [PASO 6] Verificando permisos de archivos...
attrib -R "%PLUGIN_DEST%\*" /S >nul 2>&1
echo Permisos de archivos configurados
echo.

echo ========================================
echo    PREPARACION COMPLETADA
echo ========================================
echo.
echo Plugin instalado en: %PLUGIN_DEST%
echo URL de testing: https://localhost/webstore/wp-admin/plugins.php
echo.
echo PROXIMOS PASOS:
echo 1. Abrir https://localhost/webstore/wp-admin/
echo 2. Ir a Plugins - Plugins instalados
echo 3. Buscar "WP Cupon WhatsApp"
echo 4. Hacer clic en "Activar"
echo 5. Verificar que no hay errores
echo.
echo HERRAMIENTAS DE DIAGNOSTICO DISPONIBLES:
echo - debug-headers.php (version corregida)
echo - wp-cupon-whatsapp-minimal.php (version minimal)
echo - testing-config.php (configuracion de testing)
echo.
echo IMPORTANTE PARA DISTRIBUCION MASIVA:
echo - No usar rutas absolutas
echo - No depender de configuraciones especificas
echo - Mantener compatibilidad con diferentes versiones de WP
echo - Seguir estandares de WordPress
echo.
echo Presiona cualquier tecla para continuar...
pause >nul

endlocal