# Script para diagnosticar y corregir error de sintaxis en wp-load.php
Write-Host "=== DIAGNOSTICO Y CORRECCION DE SINTAXIS ===" -ForegroundColor Yellow
Write-Host ""

# Definir rutas
$wpPath = "C:\xampp\htdocs\webstore"
$wpLoadFile = "$wpPath\wp-load.php"
$backupFile = "$wpPath\wp-load.php.backup.sintaxis.$(Get-Date -Format 'yyyyMMdd-HHmmss')"

# Verificar si el archivo existe
if (-not (Test-Path $wpLoadFile)) {
    Write-Host "ERROR: wp-load.php no existe en $wpLoadFile" -ForegroundColor Red
    exit 1
}

# Crear backup
Write-Host "Creando backup del archivo actual..." -ForegroundColor Cyan
try {
    Copy-Item $wpLoadFile $backupFile -Force
    Write-Host "Backup creado: $backupFile" -ForegroundColor Green
} catch {
    Write-Host "ERROR al crear backup: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Continuando sin backup..." -ForegroundColor Yellow
}

# Leer contenido actual
Write-Host "Leyendo contenido actual..." -ForegroundColor Cyan
$content = Get-Content $wpLoadFile -Raw

# Mostrar las primeras lineas para diagnostico
Write-Host "Primeras 10 lineas del archivo:" -ForegroundColor Cyan
$lines = $content -split "`n"
for ($i = 0; $i -lt [Math]::Min(10, $lines.Length); $i++) {
    Write-Host "$($i+1): $($lines[$i])" -ForegroundColor Gray
}

# Verificar sintaxis con PHP
Write-Host ""
Write-Host "Verificando sintaxis con PHP..." -ForegroundColor Cyan
try {
    $syntaxCheck = php -l $wpLoadFile 2>&1
    Write-Host "Resultado de sintaxis: $syntaxCheck" -ForegroundColor $(if ($syntaxCheck -match "No syntax errors") { 'Green' } else { 'Red' })
} catch {
    Write-Host "No se pudo verificar sintaxis (PHP no disponible)" -ForegroundColor Yellow
}

# Buscar problemas comunes de sintaxis
Write-Host ""
Write-Host "Buscando problemas de sintaxis..." -ForegroundColor Cyan

# Contar llaves
$openBraces = ($content.ToCharArray() | Where-Object { $_ -eq '{' }).Count
$closeBraces = ($content.ToCharArray() | Where-Object { $_ -eq '}' }).Count
Write-Host "Llaves abiertas '{': $openBraces" -ForegroundColor $(if ($openBraces -eq $closeBraces) { 'Green' } else { 'Red' })
Write-Host "Llaves cerradas '}': $closeBraces" -ForegroundColor $(if ($openBraces -eq $closeBraces) { 'Green' } else { 'Red' })

# Contar parentesis
$openParens = ($content.ToCharArray() | Where-Object { $_ -eq '(' }).Count
$closeParens = ($content.ToCharArray() | Where-Object { $_ -eq ')' }).Count
Write-Host "Parentesis abiertos '(': $openParens" -ForegroundColor $(if ($openParens -eq $closeParens) { 'Green' } else { 'Red' })
Write-Host "Parentesis cerrados ')': $closeParens" -ForegroundColor $(if ($openParens -eq $closeParens) { 'Green' } else { 'Red' })

# Buscar linea 44 especificamente (mencionada en el error)
Write-Host ""
Write-Host "Analizando linea 44 (mencionada en el error):" -ForegroundColor Cyan
if ($lines.Length -ge 44) {
    Write-Host "Linea 44: $($lines[43])" -ForegroundColor Yellow
    # Mostrar contexto alrededor de la linea 44
    Write-Host "Contexto (lineas 40-48):" -ForegroundColor Cyan
    for ($i = 39; $i -lt [Math]::Min(48, $lines.Length); $i++) {
        $marker = if ($i -eq 43) { ">>> " } else { "    " }
        Write-Host "$marker$($i+1): $($lines[$i])" -ForegroundColor $(if ($i -eq 43) { 'Yellow' } else { 'Gray' })
    }
} else {
    Write-Host "El archivo tiene menos de 44 lineas" -ForegroundColor Red
}

# Contenido correcto de wp-load.php
$correctContent = @'
<?php
/**
 * Bootstrap file for setting the ABSPATH constant
 * and loading the wp-config.php file. The wp-config.php
 * file will then load the wp-settings.php file, which
 * will then set up the WordPress environment.
 *
 * If the wp-config.php file is not found then an error
 * will be displayed asking the visitor to set up the
 * wp-config.php file.
 *
 * Will also search for wp-config.php in WordPress' parent
 * directory to allow the WordPress directory to remain
 * untouched.
 *
 * @package WordPress
 */

/** Define ABSPATH as this file's directory */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );

/*
 * If wp-config.php exists in the WordPress root, or if it exists in the root and wp-settings.php
 * doesn't, load wp-config.php. The secondary check for wp-settings.php has the added benefit
 * of avoiding cases where the current directory is a nested installation, e.g. / is WordPress(a)
 * and /blog/ is WordPress(b).
 *
 * If neither set of conditions is true, initiate loading the setup process.
 */
if ( file_exists( ABSPATH . 'wp-config.php' ) ) {

	/** The config file resides in ABSPATH */
	require_once( ABSPATH . 'wp-config.php' );

} elseif ( @file_exists( dirname( ABSPATH ) . '/wp-config.php' ) && ! @file_exists( dirname( ABSPATH ) . '/wp-settings.php' ) ) {

	/** The config file resides one level above ABSPATH but is not part of another installation */
	require_once( dirname( ABSPATH ) . '/wp-config.php' );

} else {

	// A config file doesn't exist

	define( 'WPINC', 'wp-includes' );
	require_once( ABSPATH . WPINC . '/load.php' );

	// Standardize $_SERVER variables across setups.
	wp_fix_server_vars();

	require_once( ABSPATH . WPINC . '/functions.php' );

	$path = wp_guess_url() . '/wp-admin/setup-config.php';

	/*
	 * We're going to redirect to setup-config.php. While this shouldn't result
	 * in an infinite loop, that's a silly thing to assume, don't you think? If
	 * we're traveling in circles, our last-ditch effort is "Need more help?"
	 */
	if ( false === strpos( $_SERVER['REQUEST_URI'], 'setup-config' ) ) {
		header( 'Location: ' . $path );
		exit;
	}

	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
	require_once( ABSPATH . WPINC . '/version.php' );

	wp_check_php_mysql_versions();
	wp_load_translations_early();

	// Die with an error message
	$die = sprintf(
		/* translators: %s: wp-config.php */
		__( "There doesn't seem to be a %s file. I need this before we can get started." ),
		'<code>wp-config.php</code>'
	) . '</p>';
	$die .= '<p>' . sprintf(
		/* translators: %s: Documentation URL. */
		__( "Need more help? <a href='%s'>We got it</a>." ),
		__( 'https://wordpress.org/support/article/editing-wp-config-php/' )
	) . '</p>';
	$die .= '<p>' . sprintf(
		/* translators: %s: wp-config.php */
		__( "You can create a %s file through a web interface, but this doesn't work for all server setups. The safest way is to manually create the file." ),
		'<code>wp-config.php</code>'
	) . '</p>';
	$die .= '<p><a href="' . $path . '" class="button button-large">' . __( 'Create a Configuration File' ) . '</a>';

	wp_die( $die, __( 'WordPress &rsaquo; Error' ) );
}
'@

Write-Host ""
Write-Host "=== SOLUCION ===" -ForegroundColor Yellow
$corregir = Read-Host "Deseas reemplazar wp-load.php con el contenido correcto de WordPress? (s/n)"

if ($corregir -eq 's' -or $corregir -eq 'S') {
    Write-Host "Escribiendo contenido correcto..." -ForegroundColor Cyan
    try {
        Set-Content -Path $wpLoadFile -Value $correctContent -Encoding UTF8 -Force
        Write-Host "wp-load.php corregido exitosamente" -ForegroundColor Green
        
        # Verificar sintaxis nuevamente
        Write-Host "Verificando sintaxis del archivo corregido..." -ForegroundColor Cyan
        try {
            $newSyntaxCheck = php -l $wpLoadFile 2>&1
            Write-Host "Resultado: $newSyntaxCheck" -ForegroundColor $(if ($newSyntaxCheck -match "No syntax errors") { 'Green' } else { 'Red' })
        } catch {
            Write-Host "No se pudo verificar sintaxis" -ForegroundColor Yellow
        }
        
    } catch {
        Write-Host "ERROR al escribir archivo: $($_.Exception.Message)" -ForegroundColor Red
        
        # Restaurar backup
        if (Test-Path $backupFile) {
            Write-Host "Restaurando backup..." -ForegroundColor Yellow
            try {
                Copy-Item $backupFile $wpLoadFile -Force
                Write-Host "Backup restaurado" -ForegroundColor Green
            } catch {
                Write-Host "ERROR al restaurar backup: $($_.Exception.Message)" -ForegroundColor Red
            }
        }
    }
} else {
    Write-Host "Operacion cancelada" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== DIAGNOSTICO COMPLETADO ===" -ForegroundColor Green
if (Test-Path $backupFile) {
    Write-Host "Backup disponible en: $backupFile" -ForegroundColor Yellow
}
Write-Host "Puedes acceder a WordPress en: https://localhost/webstore/" -ForegroundColor Cyan