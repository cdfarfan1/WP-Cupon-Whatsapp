@echo off
echo ========================================
echo VERIFICACION FINAL DE SINTAXIS
echo debug-headers.php - WP Cupon WhatsApp
echo ========================================
echo.

echo [INFO] Creando backup del archivo anterior...
copy "debug-headers.php" "debug-headers-backup-final-%date:~-4,4%%date:~-10,2%%date:~-7,2%-%time:~0,2%%time:~3,2%%time:~6,2%.php" >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] Backup creado exitosamente
) else (
    echo [WARNING] No se pudo crear backup
)

echo.
echo [INFO] Verificando estructura del archivo...
findstr /n "function" debug-headers.php
echo.
echo [INFO] Contando llaves de apertura y cierre...
findstr /o "{" debug-headers.php | find /c "{"
findstr /o "}" debug-headers.php | find /c "}"

echo.
echo [INFO] Verificando lineas problematicas reportadas...
echo Linea 32:
more +31 debug-headers.php | findstr /n ".*" | findstr "^1:"
echo Linea 46:
more +45 debug-headers.php | findstr /n ".*" | findstr "^1:"

echo.
echo [INFO] Verificando caracteres especiales...
findstr /r "[^\x20-\x7E\x09\x0A\x0D]" debug-headers.php >nul 2>&1
if %errorlevel% equ 0 (
    echo [WARNING] Caracteres especiales encontrados
) else (
    echo [OK] No se encontraron caracteres especiales problematicos
)

echo.
echo [INFO] Informacion del archivo:
dir debug-headers.php | findstr "debug-headers.php"

echo.
echo ========================================
echo VERIFICACION COMPLETADA
echo ========================================
echo.
echo INSTRUCCIONES:
echo 1. Subir debug-headers.php al servidor
echo 2. Activar el plugin WP-Cupon-Whatsapp
echo 3. Verificar que no aparezcan errores
echo.
pause