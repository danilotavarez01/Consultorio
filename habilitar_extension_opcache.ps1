# Script para habilitar la extensión OPcache en php.ini
Write-Host "AGREGANDO EXTENSION OPCACHE A PHP.INI" -ForegroundColor Green

$phpIniPath = "C:\php\php.ini"

# Crear respaldo adicional
$backupPath = "$phpIniPath.backup_extension"
Copy-Item $phpIniPath $backupPath
Write-Host "Respaldo creado en: $backupPath" -ForegroundColor Yellow

# Leer contenido actual
$content = Get-Content $phpIniPath

# Buscar una ubicación apropiada para agregar la extensión
$newContent = @()
$extensionAdded = $false

foreach ($line in $content) {
    $newContent += $line
    
    # Agregar la extensión después de la sección de extensiones dinámicas
    if ($line -match "^; Dynamic Extensions" -and !$extensionAdded) {
        $newContent += ""
        $newContent += "; OPcache Extension - Agregado automáticamente"
        $newContent += "zend_extension=opcache"
        $newContent += ""
        $extensionAdded = $true
        Write-Host "Extension OPcache agregada después de la sección Dynamic Extensions" -ForegroundColor Green
    }
}

# Si no encontramos la sección, agregarlo al principio
if (!$extensionAdded) {
    $newContentWithExtension = @()
    $newContentWithExtension += "; OPcache Extension - Agregado automáticamente"
    $newContentWithExtension += "zend_extension=opcache"
    $newContentWithExtension += ""
    $newContentWithExtension += $content
    $newContent = $newContentWithExtension
    Write-Host "Extension OPcache agregada al principio del archivo" -ForegroundColor Green
}

# Guardar cambios
$newContent | Set-Content $phpIniPath -Encoding UTF8

Write-Host "Extension OPcache agregada exitosamente!" -ForegroundColor Green

# Reiniciar IIS
try {
    Write-Host "Reiniciando IIS..." -ForegroundColor Yellow
    iisreset /noforce
    Write-Host "IIS reiniciado" -ForegroundColor Green
} catch {
    Write-Host "No se pudo reiniciar IIS automaticamente" -ForegroundColor Yellow
}

Write-Host "COMPLETADO. Verifique en: test_opcache.php" -ForegroundColor Green
