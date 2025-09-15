@echo off
chcp 65001 >nul
echo ========================================
echo    RESTAURADOR AUTOMÁTICO DE WORDPRESS
echo ========================================
echo.
echo Este script ejecutará automáticamente todos los pasos
echo para restaurar tu instalación de WordPress.
echo.
echo ⚠️  IMPORTANTE: Asegúrate de que XAMPP esté funcionando
echo    (Apache y MySQL iniciados)
echo.
pause

echo.
echo ========================================
echo PASO 1: VERIFICANDO INSTALACIÓN ACTUAL
echo ========================================
php verificar-wordpress-completo.php
echo.
pause

echo.
echo ========================================
echo PASO 2: DESCARGANDO WORDPRESS
echo ========================================
echo ⏳ Esto puede tomar varios minutos...
php descargar-wordpress.php
echo.
pause

echo.
echo ========================================
echo PASO 3: RESTAURANDO ARCHIVOS
echo ========================================
php restaurar-wordpress.php
echo.
pause

echo.
echo ========================================
echo PASO 4: VERIFICACIÓN FINAL
echo ========================================
php limpiar-y-verificar.php
echo.
pause

echo.
echo ========================================
echo    PROCESO COMPLETADO
echo ========================================
echo.
echo 🎉 Tu WordPress ha sido restaurado.
echo.
echo 🌐 Prueba acceder a:
echo    https://localhost/webstore/wp-admin/
echo.
echo Si tienes problemas, revisa los logs de XAMPP.
echo.
pause
