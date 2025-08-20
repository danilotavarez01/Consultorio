# Script simple para habilitar OPcache
Write-Host "HABILITANDO OPCACHE EN PHP" -ForegroundColor Green

$phpIniPath = "C:\php\php.ini"

# Verificar archivo
if (!(Test-Path $phpIniPath)) {
    Write-Host "ERROR: No se encontro php.ini en $phpIniPath" -ForegroundColor Red
    exit 1
}

# Crear respaldo
$backupPath = "$phpIniPath.backup_opcache"
Copy-Item $phpIniPath $backupPath
Write-Host "Respaldo creado en: $backupPath" -ForegroundColor Yellow

# Leer contenido
$content = Get-Content $phpIniPath

# Reemplazar configuraciones de OPcache
$newContent = @()
foreach ($line in $content) {
    if ($line -match "^;opcache\.enable=") {
        $newContent += "opcache.enable=1"
    }
    elseif ($line -match "^;opcache\.enable_cli=") {
        $newContent += "opcache.enable_cli=1"
    }
    elseif ($line -match "^;opcache\.memory_consumption=") {
        $newContent += "opcache.memory_consumption=128"
    }
    elseif ($line -match "^;opcache\.interned_strings_buffer=") {
        $newContent += "opcache.interned_strings_buffer=8"
    }
    elseif ($line -match "^;opcache\.max_accelerated_files=") {
        $newContent += "opcache.max_accelerated_files=4000"
    }
    elseif ($line -match "^;opcache\.revalidate_freq=") {
        $newContent += "opcache.revalidate_freq=2"
    }
    else {
        $newContent += $line
    }
}

# Guardar cambios
$newContent | Set-Content $phpIniPath -Encoding UTF8

Write-Host "OPcache habilitado exitosamente!" -ForegroundColor Green

# Reiniciar IIS
try {
    Write-Host "Reiniciando IIS..." -ForegroundColor Yellow
    iisreset /noforce
    Write-Host "IIS reiniciado" -ForegroundColor Green
} catch {
    Write-Host "No se pudo reiniciar IIS automaticamente" -ForegroundColor Yellow
}

Write-Host "COMPLETADO. Verifique en: verificar_optimizaciones.php" -ForegroundColor Green
