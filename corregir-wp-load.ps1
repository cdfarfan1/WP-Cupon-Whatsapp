# Script para corregir wp-load.php con el contenido correcto
Write-Host "=== CORRIGIENDO WP-LOAD.PHP ===" -ForegroundColor Yellow
Write-Host ""

# Definir rutas
$wpPath = "C:\xampp\htdocs\webstore"
$wpLoadFile = "$wpPath\wp-load.php"
$backupFile = "$wpPath\wp-load.php.backup.$(Get-Date -Format 'yyyyMMdd-HHmmss')"

# Contenido correcto de wp-load.php (sin las comillas here-string que causan problemas)
$correctContent = "<?php`n /** `n  * Bootstrap file for setting the ABSPATH constant `n  * and loading the wp-config.php file. The wp-config.php `n  * file will then load the wp-settings.php file, which `n  * will then set up the WordPress environment. `n  * `n  * If the wp-config.php file is not found then an error `n  * will be displayed asking the visitor to set up the `n  * wp-config.php file. `n  * `n  * Will also search for wp-config.php in WordPress' parent `n  * directory to allow the WordPress directory to remain `n  * untouched. `n  * `n  * @package WordPress `n  */ `n `n /** Define ABSPATH as this file's directory */ `n if ( ! defined( 'ABSPATH' ) ) { `n     define( 'ABSPATH', dirname( __FILE__ ) . '/' ); `n } `n `n error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR ); `n `n /* `n  * If wp-config.php exists in the WordPress root, or if it exists in the root and wp-settings.php `n  * doesn't, load wp-config.php. The secondary check for wp-settings.php has the added benefit `n  * of avoiding cases where the current directory is a nested installation, e.g. / is WordPress(a) `n  * and /blog/ is WordPress(b). `n  * `n  * If neither set of conditions is true, initiate loading the setup process. `n  */ `n if ( file_exists( ABSPATH . 'wp-config.php' ) ) { `n `n     /** The config file resides in ABSPATH */ `n     require_once( ABSPATH . 'wp-config.php' ); `n `n } elseif ( @file_exists( dirname( ABSPATH ) . '/wp-config.php' ) && ! @file_exists( dirname( ABSPATH ) . '/wp-settings.php' ) ) { `n `n     /** The config file resides one level above ABSPATH but is not part of another installation */ `n     require_once( dirname( ABSPATH ) . '/wp-config.php' ); `n `n } else { `n `n     // A config file doesn't exist `n `n     define( 'WPINC', 'wp-includes' ); `n     require_once( ABSPATH . WPINC . '/load.php' ); `n `n     // Standardize `$_SERVER variables across setups. `n     wp_fix_server_vars(); `n `n     require_once( ABSPATH . WPINC . '/functions.php' ); `n `n     `$path = wp_guess_url() . '/wp-admin/setup-config.php'; `n `n     /* `n      * We're going to redirect to setup-config.php. While this shouldn't result `n      * in an infinite loop, that's a silly thing to assume, don't you think? If `n      * we're traveling in circles, our last-ditch effort is `"Need more help?`" `n      */ `n     if ( false === strpos( `$_SERVER['REQUEST_URI'], 'setup-config' ) ) { `n         header( 'Location: ' . `$path ); `n         exit; `n     } `n `n     define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' ); `n     require_once( ABSPATH . WPINC . '/version.php' ); `n `n     wp_check_php_mysql_versions(); `n     wp_load_translations_early(); `n `n     // Die with an error message `n     `$die = sprintf( `n         /* translators: %s: wp-config.php */ `n         __( `"There doesn't seem to be a %s file. I need this before we can get started.`" ), `n         '<code>wp-config.php</code>' `n     ) . '</p>'; `n     `$die .= '<p>' . sprintf( `n         /* translators: %s: Documentation URL. */ `n         __( `"Need more help? <a href='%s'>We got it</a>.`" ), `n         __( ' ``https://wordpress.org/support/article/editing-wp-config-php/`` ' ) `n     ) . '</p>'; `n     `$die .= '<p>' . sprintf( `n         /* translators: %s: wp-config.php */ `n         __( `"You can create a %s file through a web interface, but this doesn't work for all server setups. The safest way is to manually create the file.`" ), `n         '<code>wp-config.php</code>' `n     ) . '</p>'; `n     `$die .= '<p><a href=`"' . `$path . '`" class=`"button button-large`">' . __( 'Create a Configuration File' ) . '</a>'; `n `n     wp_die( `$die, __( 'WordPress &rsaquo; Error' ) );"

# Verificar si el archivo existe
if (Test-Path $wpLoadFile) {
    # Crear backup del archivo actual
    Write-Host "Creando backup del archivo actual..." -ForegroundColor Cyan
    try {
        Copy-Item $wpLoadFile $backupFile -Force
        Write-Host "Backup creado: $backupFile" -ForegroundColor Green
    } catch {
        Write-Host "ERROR al crear backup: $($_.Exception.Message)" -ForegroundColor Red
        Write-Host "Continuando sin backup..." -ForegroundColor Yellow
    }
    
    # Verificar contenido actual
    $currentContent = Get-Content $wpLoadFile -Raw
    Write-Host "Contenido actual detectado:" -ForegroundColor Cyan
    $preview = $currentContent.Substring(0, [Math]::Min(100, $currentContent.Length))
    Write-Host "$preview..." -ForegroundColor Gray
    
    if ($currentContent -match "^ok<\?php") {
        Write-Host "Detectado contenido corrupto 'ok<?php'" -ForegroundColor Red
    } elseif ($currentContent -match "^<\?php") {
        Write-Host "El archivo ya parece tener el formato correcto" -ForegroundColor Yellow
        Write-Host "Reemplazando de todas formas para asegurar contenido correcto..." -ForegroundColor Cyan
    }
} else {
    Write-Host "ERROR: wp-load.php no existe en $wpLoadFile" -ForegroundColor Red
    exit 1
}

# Escribir el contenido correcto
Write-Host "Escribiendo contenido correcto..." -ForegroundColor Cyan
try {
    Set-Content -Path $wpLoadFile -Value $correctContent -Encoding UTF8 -Force
    Write-Host "wp-load.php actualizado exitosamente" -ForegroundColor Green
} catch {
    Write-Host "ERROR al escribir archivo: $($_.Exception.Message)" -ForegroundColor Red
    
    # Intentar restaurar backup si existe
    if (Test-Path $backupFile) {
        Write-Host "Intentando restaurar backup..." -ForegroundColor Yellow
        try {
            Copy-Item $backupFile $wpLoadFile -Force
            Write-Host "Backup restaurado" -ForegroundColor Green
        } catch {
            Write-Host "ERROR al restaurar backup: $($_.Exception.Message)" -ForegroundColor Red
        }
    }
    exit 1
}

# Verificar que el archivo se escribio correctamente
Write-Host "Verificando archivo actualizado..." -ForegroundColor Cyan
$newContent = Get-Content $wpLoadFile -Raw
if ($newContent -match "^<\?php") {
    Write-Host "EXITO: wp-load.php corregido correctamente" -ForegroundColor Green
    Write-Host "Primeras lineas del archivo:" -ForegroundColor Cyan
    $lines = $newContent -split "`n" | Select-Object -First 5
    foreach ($line in $lines) {
        Write-Host "  $line" -ForegroundColor Gray
    }
} else {
    Write-Host "ERROR: El archivo no se escribio correctamente" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "=== CORRECCION COMPLETADA ===" -ForegroundColor Green
Write-Host "wp-load.php ha sido corregido con el contenido estandar de WordPress" -ForegroundColor Green
Write-Host "Ahora puedes acceder a: https://localhost/webstore/wp-admin/" -ForegroundColor Cyan

if (Test-Path $backupFile) {
    Write-Host "Backup disponible en: $backupFile" -ForegroundColor Yellow
}