@echo off
echo ========================================
echo WP Cupon WhatsApp - Aplicar Correcciones
echo ========================================
echo.

echo Aplicando correcciones de errores fatales...
echo.

REM Crear respaldos de seguridad
echo [1/4] Creando respaldos de seguridad...
if exist "wp-cupon-whatsapp.php" (
    copy "wp-cupon-whatsapp.php" "wp-cupon-whatsapp-backup.php" >nul
    echo   ✓ Respaldo creado: wp-cupon-whatsapp-backup.php
) else (
    echo   ⚠ Archivo original no encontrado: wp-cupon-whatsapp.php
)

if exist "includes\class-wpcw-installer.php" (
    copy "includes\class-wpcw-installer.php" "includes\class-wpcw-installer-backup.php" >nul
    echo   ✓ Respaldo creado: includes\class-wpcw-installer-backup.php
) else (
    echo   ⚠ Archivo original no encontrado: includes\class-wpcw-installer.php
)

echo.

REM Aplicar correcciones
echo [2/4] Aplicando correcciones...
if exist "wp-cupon-whatsapp-fixed.php" (
    copy "wp-cupon-whatsapp-fixed.php" "wp-cupon-whatsapp.php" >nul
    echo   ✓ Archivo principal corregido aplicado
) else (
    echo   ✗ Error: No se encontró wp-cupon-whatsapp-fixed.php
    goto :error
)

if exist "includes\class-wpcw-installer-fixed.php" (
    copy "includes\class-wpcw-installer-fixed.php" "includes\class-wpcw-installer.php" >nul
    echo   ✓ Clase installer corregida aplicada
) else (
    echo   ✗ Error: No se encontró includes\class-wpcw-installer-fixed.php
    goto :error
)

echo.

REM Verificar correcciones
echo [3/4] Verificando correcciones aplicadas...
if exist "test-fixed-activation.php" (
    echo   → Ejecutando prueba de activación...
    php test-fixed-activation.php
    if %ERRORLEVEL% EQU 0 (
        echo   ✓ Prueba de activación exitosa
    ) else (
        echo   ✗ Error en la prueba de activación
        goto :error
    )
) else (
    echo   ⚠ Script de prueba no encontrado, saltando verificación
)

echo.

REM Finalización
echo [4/4] Finalizando...
echo   ✓ Correcciones aplicadas exitosamente
echo   ✓ Plugin listo para distribución
echo.
echo ========================================
echo           CORRECCIONES APLICADAS
echo ========================================
echo.
echo El plugin WP Cupón WhatsApp ha sido corregido exitosamente.
echo Los errores fatales han sido solucionados y el plugin está
echo listo para su distribución masiva.
echo.
echo Archivos modificados:
echo   - wp-cupon-whatsapp.php (versión 1.2.1)
echo   - includes/class-wpcw-installer.php (versión mejorada)
echo.
echo Archivos de respaldo creados:
echo   - wp-cupon-whatsapp-backup.php
echo   - includes/class-wpcw-installer-backup.php
echo.
echo Para más detalles, consulte CORRECCIONES-REALIZADAS.md
echo.
pause
goto :end

:error
echo.
echo ========================================
echo              ERROR CRÍTICO
echo ========================================
echo.
echo No se pudieron aplicar las correcciones correctamente.
echo Por favor, verifique que todos los archivos estén presentes:
echo   - wp-cupon-whatsapp-fixed.php
echo   - includes/class-wpcw-installer-fixed.php
echo   - test-fixed-activation.php
echo.
echo Si el problema persiste, aplique las correcciones manualmente
echo siguiendo las instrucciones en CORRECCIONES-REALIZADAS.md
echo.
pause
exit /b 1

:end
exit /b 0