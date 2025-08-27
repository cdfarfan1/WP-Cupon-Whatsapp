@echo off
echo ========================================
echo INSTALADOR DE PLUGIN WP CUPON WHATSAPP (STANDALONE)
echo ========================================
echo.

REM Definir rutas
set PLUGIN_SOURCE=%~dp0
set WORDPRESS_PLUGINS=C:\xampp\htdocs\webstore\wp-content\plugins
set PLUGIN_DEST=%WORDPRESS_PLUGINS%\wp-cupon-whatsapp

echo Verificando rutas...
echo Origen: %PLUGIN_SOURCE%
echo Destino: %PLUGIN_DEST%
echo.

REM Verificar si existe la carpeta de plugins de WordPress
if not exist "%WORDPRESS_PLUGINS%" (
    echo ERROR: No se encontro la carpeta de plugins de WordPress en:
    echo %WORDPRESS_PLUGINS%
    echo.
    echo Por favor, verifica que WordPress este instalado en C:\xampp\htdocs\webstore
    echo o modifica la ruta en este script.
    pause
    exit /b 1
)

REM Crear carpeta del plugin si no existe
if not exist "%PLUGIN_DEST%" (
    echo Creando carpeta del plugin...
    mkdir "%PLUGIN_DEST%"
)

echo Copiando archivos del plugin (version standalone)...
echo.

REM Copiar archivo principal standalone (sin dependencias)
echo - Copiando archivo principal standalone...
copy "%PLUGIN_SOURCE%wp-cupon-whatsapp.php" "%PLUGIN_DEST%\wp-cupon-whatsapp.php" >nul
copy "%PLUGIN_SOURCE%readme.txt" "%PLUGIN_DEST%\" >nul
copy "%PLUGIN_SOURCE%LICENSE" "%PLUGIN_DEST%\" 2>nul

REM Copiar carpetas
echo - Copiando carpeta admin...
xcopy "%PLUGIN_SOURCE%admin" "%PLUGIN_DEST%\admin\" /E /I /Y >nul

echo - Copiando carpeta includes...
xcopy "%PLUGIN_SOURCE%includes" "%PLUGIN_DEST%\includes\" /E /I /Y >nul

echo - Copiando carpeta public...
xcopy "%PLUGIN_SOURCE%public" "%PLUGIN_DEST%\public\" /E /I /Y >nul

echo - Copiando carpeta templates...
xcopy "%PLUGIN_SOURCE%templates" "%PLUGIN_DEST%\templates\" /E /I /Y >nul

echo - Copiando carpeta languages...
xcopy "%PLUGIN_SOURCE%languages" "%PLUGIN_DEST%\languages\" /E /I /Y >nul

REM Copiar carpeta elementor si existe
if exist "%PLUGIN_SOURCE%elementor" (
    echo - Copiando carpeta elementor...
    xcopy "%PLUGIN_SOURCE%elementor" "%PLUGIN_DEST%\elementor\" /E /I /Y >nul
)

echo.
echo ========================================
echo INSTALACION COMPLETADA EXITOSAMENTE
echo ========================================
echo.
echo El plugin ha sido copiado a:
echo %PLUGIN_DEST%
echo.
echo PROXIMOS PASOS:
echo 1. Ve a tu panel de WordPress: https://localhost/webstore/wp-admin
echo 2. Ve a Plugins ^> Plugins Instalados
echo 3. Busca "WP Cupon WhatsApp" y activalo
echo 4. Configura el plugin en WP Cupon ^> Configuracion
echo.
echo Presiona cualquier tecla para continuar...
pause >nul