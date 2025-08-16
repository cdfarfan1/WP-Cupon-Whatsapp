@echo off
REM Script para ejecutar verificación de sintaxis PHP
REM Plugin: WP-Cupon-Whatsapp

echo ============================================================
echo VERIFICADOR AUTOMATICO DE SINTAXIS PHP
echo Plugin: WP-Cupon-Whatsapp
echo ============================================================
echo.

REM Verificar si PHP está disponible
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: PHP no esta disponible en el PATH del sistema
    echo.
    echo Opciones para resolver:
    echo 1. Instalar PHP y agregarlo al PATH
    echo 2. Usar XAMPP/WAMP y ejecutar desde su directorio
    echo 3. Subir syntax-checker.php al servidor y ejecutar via web
    echo.
    echo Ejemplo con XAMPP:
    echo C:\xampp\php\php.exe syntax-checker.php
    echo.
    echo Ejemplo via web:
    echo https://tu-sitio.com/wp-content/plugins/WP-Cupon-Whatsapp/syntax-checker.php?check=1
    echo.
    pause
    exit /b 1
)

echo PHP encontrado. Ejecutando verificacion...
echo.

REM Ejecutar verificación
php syntax-checker.php

REM Verificar resultado
if %errorlevel% equ 0 (
    echo.
    echo ============================================================
    echo VERIFICACION COMPLETADA EXITOSAMENTE
    echo ============================================================
) else (
    echo.
    echo ============================================================
    echo VERIFICACION FALLIDA - Se encontraron errores
    echo Revisa el archivo syntax-check.log para mas detalles
    echo ============================================================
)

echo.
echo Presiona cualquier tecla para continuar...
pause >nul