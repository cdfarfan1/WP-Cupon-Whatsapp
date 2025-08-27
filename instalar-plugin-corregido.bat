@echo off
echo ========================================
echo INSTALACION DEL PLUGIN CORREGIDO
echo WP Cupon WhatsApp - Version Corregida
echo ========================================
echo.

echo Verificando directorio de WordPress...
if not exist "C:\xampp\htdocs\webstore\wp-content\plugins" (
    echo ERROR: No se encontro el directorio de plugins de WordPress
    echo Verifica que WordPress este instalado en C:\xampp\htdocs\webstore
    pause
    exit /b 1
)

echo ✓ Directorio de WordPress encontrado
echo.

echo Eliminando version anterior del plugin (si existe)...
if exist "C:\xampp\htdocs\webstore\wp-content\plugins\wp-cupon-whatsapp" (
    rmdir /s /q "C:\xampp\htdocs\webstore\wp-content\plugins\wp-cupon-whatsapp"
    echo ✓ Version anterior eliminada
) else (
    echo ✓ No hay version anterior instalada
)

echo.
echo Creando directorio del plugin...
mkdir "C:\xampp\htdocs\webstore\wp-content\plugins\wp-cupon-whatsapp"
echo ✓ Directorio creado

echo.
echo Copiando archivos del plugin corregido...
xcopy ".\*" "C:\xampp\htdocs\webstore\wp-content\plugins\wp-cupon-whatsapp\" /E /H /C /I /Y
echo ✓ Archivos copiados

echo.
echo ========================================
echo INSTALACION COMPLETADA
echo ========================================
echo.
echo El plugin WP Cupon WhatsApp (version corregida) ha sido instalado.
echo.
echo PROXIMOS PASOS:
echo 1. Abre tu sitio WordPress: https://localhost/webstore/wp-admin/
echo 2. Ve a Plugins > Plugins Instalados
echo 3. Busca "WP Canje Cupon Whatsapp"
echo 4. Haz clic en "Activar"
echo 5. Verifica que las paginas se muestren correctamente
echo.
echo NOTA: El problema del HTML sin procesar ha sido solucionado.
echo.
pause