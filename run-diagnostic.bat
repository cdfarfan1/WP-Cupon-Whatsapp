@echo off
echo ========================================
echo DIAGNOSTICO AVANZADO DE SINTAXIS PHP
echo Plugin: WP-Cupon-Whatsapp
echo ========================================
echo.

REM Verificar si PHP está disponible
php --version >nul 2>&1
if %errorlevel% == 0 (
    echo PHP encontrado en el sistema
    echo Ejecutando diagnostico...
    echo.
    php syntax-diagnostic.php debug-headers.php
    goto :end
)

echo PHP no encontrado en PATH del sistema
echo.
echo Intentando con XAMPP...
if exist "C:\xampp\php\php.exe" (
    echo XAMPP encontrado
    "C:\xampp\php\php.exe" syntax-diagnostic.php debug-headers.php
    goto :end
)

echo Intentando con WAMP...
if exist "C:\wamp64\bin\php\php7.4.33\php.exe" (
    echo WAMP encontrado
    "C:\wamp64\bin\php\php7.4.33\php.exe" syntax-diagnostic.php debug-headers.php
    goto :end
)

if exist "C:\wamp\bin\php\php7.4.33\php.exe" (
    echo WAMP encontrado
    "C:\wamp\bin\php\php7.4.33\php.exe" syntax-diagnostic.php debug-headers.php
    goto :end
)

echo.
echo ========================================
echo ERROR: No se pudo encontrar PHP
echo ========================================
echo.
echo Opciones disponibles:
echo.
echo 1. Instalar PHP y agregarlo al PATH del sistema
echo 2. Instalar XAMPP (https://www.apachefriends.org/)
echo 3. Instalar WAMP (https://www.wampserver.com/)
echo 4. Ejecutar el diagnostico via web:
echo    - Subir syntax-diagnostic.php al servidor
echo    - Acceder via: http://tudominio.com/wp-content/plugins/WP-Cupon-Whatsapp/syntax-diagnostic.php
echo.
echo DIAGNOSTICO MANUAL:
echo ========================================
echo.
echo El error reportado es:
echo "PHP Parse error: Unclosed '{' on line 32 in debug-headers.php on line 153"
echo.
echo Esto indica que:
echo 1. Hay una llave '{' abierta en la linea 32 que no se cierra
echo 2. El error se detecta hasta la linea 153
echo 3. Revisar manualmente las lineas 32-153 en debug-headers.php
echo.
echo SOLUCION TEMPORAL:
echo ========================================
echo.
echo 1. Usar debug-headers-test.php en lugar de debug-headers.php
echo 2. Renombrar debug-headers.php a debug-headers-backup.php
echo 3. Renombrar debug-headers-test.php a debug-headers.php
echo 4. Subir el archivo renombrado al servidor
echo.

:end
echo.
echo Presiona cualquier tecla para continuar...
pause >nul