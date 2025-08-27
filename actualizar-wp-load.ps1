# Script para actualizar wp-load.php en WordPress
# Copia el archivo y cambia permisos a 444 (solo lectura)

$sourceFile = "$PSScriptRoot\wp-load.php"
$destinationFile = "C:\xampp\htdocs\webstore\wp-load.php"

Write-Host "=== ACTUALIZADOR DE WP-LOAD.PHP ===" -ForegroundColor Yellow
Write-Host ""

# Verificar que el archivo fuente existe
if (-not (Test-Path $sourceFile)) {
    Write-Host "❌ ERROR: No se encuentra el archivo fuente: $sourceFile" -ForegroundColor Red
    exit 1
}

# Verificar que el directorio de destino existe
$destinationDir = Split-Path $destinationFile -Parent
if (-not (Test-Path $destinationDir)) {
    Write-Host "❌ ERROR: No se encuentra el directorio de WordPress: $destinationDir" -ForegroundColor Red
    Write-Host "   Asegúrate de que XAMPP esté instalado y WordPress esté en la ruta correcta." -ForegroundColor Yellow
    exit 1
}

Write-Host "✅ Archivo fuente encontrado: $sourceFile" -ForegroundColor Green
Write-Host "✅ Directorio de destino encontrado: $destinationDir" -ForegroundColor Green
Write-Host ""

# Hacer backup del archivo existente si existe
if (Test-Path $destinationFile) {
    $backupFile = "$destinationFile.backup.$(Get-Date -Format 'yyyyMMdd-HHmmss')"
    Write-Host "📁 Creando backup del archivo existente..." -ForegroundColor Cyan
    try {
        Copy-Item $destinationFile $backupFile -Force
        Write-Host "✅ Backup creado: $backupFile" -ForegroundColor Green
    } catch {
        Write-Host "⚠️  Advertencia: No se pudo crear backup: $($_.Exception.Message)" -ForegroundColor Yellow
    }
    Write-Host ""
}

# Copiar el nuevo archivo
Write-Host "📋 Copiando nuevo wp-load.php..." -ForegroundColor Cyan
try {
    Copy-Item $sourceFile $destinationFile -Force
    Write-Host "✅ Archivo copiado exitosamente" -ForegroundColor Green
} catch {
    Write-Host "❌ ERROR al copiar archivo: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Cambiar permisos a 444 (solo lectura para todos)
Write-Host "🔒 Cambiando permisos a 444 (solo lectura)..." -ForegroundColor Cyan
try {
    # Obtener el archivo
    $file = Get-Item $destinationFile
    
    # Establecer como solo lectura
    $file.IsReadOnly = $true
    
    # Configurar permisos específicos usando icacls
    $icaclsResult = icacls $destinationFile /grant "Everyone:(R)" /inheritance:r 2>&1
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Permisos cambiados a solo lectura (444)" -ForegroundColor Green
    } else {
        Write-Host "⚠️  Advertencia: Archivo marcado como solo lectura, pero icacls falló: $icaclsResult" -ForegroundColor Yellow
    }
} catch {
    Write-Host "⚠️  Advertencia: No se pudieron cambiar todos los permisos: $($_.Exception.Message)" -ForegroundColor Yellow
    Write-Host "   El archivo se copió correctamente, pero los permisos pueden no ser exactamente 444." -ForegroundColor Yellow
}

# Verificar el resultado
Write-Host ""
Write-Host "=== VERIFICACIÓN FINAL ===" -ForegroundColor Yellow
if (Test-Path $destinationFile) {
    $fileInfo = Get-Item $destinationFile
    $fileSize = $fileInfo.Length
    $isReadOnly = $fileInfo.IsReadOnly
    
    Write-Host "✅ Archivo existe: $destinationFile" -ForegroundColor Green
    Write-Host "📏 Tamaño: $fileSize bytes" -ForegroundColor Cyan
    Write-Host "🔒 Solo lectura: $isReadOnly" -ForegroundColor Cyan
    
    # Mostrar las primeras líneas para verificar el contenido
    Write-Host ""
    Write-Host "📄 Primeras líneas del archivo:" -ForegroundColor Cyan
    Get-Content $destinationFile -Head 5 | ForEach-Object { Write-Host "   $_" -ForegroundColor Gray }
    
    Write-Host ""
    Write-Host "🎉 ¡wp-load.php actualizado exitosamente!" -ForegroundColor Green
    Write-Host ""
    Write-Host "=== PRÓXIMOS PASOS ===" -ForegroundColor Yellow
    Write-Host "1. Reinicia Apache en XAMPP" -ForegroundColor White
    Write-Host "2. Prueba acceder a: http://localhost:8888/webstore/" -ForegroundColor White
    Write-Host "3. Verifica que WordPress funcione correctamente" -ForegroundColor White
    
} else {
    Write-Host "❌ ERROR: El archivo no se copió correctamente" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "✨ Proceso completado" -ForegroundColor Green