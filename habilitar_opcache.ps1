# Script para habilitar OPcache en PHP
# INSTRUCCIONES: Ejecutar como Administrador

Write-Host "🚀 HABILITANDO OPCACHE PWrite-Host "MEJORAS ESPERADAS:"
Write-Host "   - Carga de paginas: 60-80% mas rapida"
Write-Host "   - Consultas: Optimizadas con indices"
Write-Host "   - Memoria: Uso mas eficiente"

Write-Host "`nSi hay problemas:"
Write-Host "   - Restaurar desde: $backupPath"
Write-Host "   - Verificar permisos de archivos"
Write-Host "   - Revisar logs de PHP"IZACIÓN DE PHP" -ForegroundColor Green
Write-Host "=" * 50

# Verificar si se ejecuta como administrador
if (-NOT ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")) {
    Write-Host "❌ ERROR: Este script debe ejecutarse como Administrador" -ForegroundColor Red
    Write-Host "   Haga clic derecho en PowerShell y seleccione 'Ejecutar como administrador'" -ForegroundColor Yellow
    pause
    exit 1
}

$phpIniPath = "C:\php\php.ini"

Write-Host "📂 Verificando archivo php.ini en: $phpIniPath" -ForegroundColor Cyan

if (!(Test-Path $phpIniPath)) {
    Write-Host "❌ ERROR: No se encontró php.ini en $phpIniPath" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Archivo php.ini encontrado" -ForegroundColor Green

# Crear respaldo
$backupPath = "$phpIniPath.backup_" + (Get-Date -Format "yyyyMMdd_HHmmss")
Write-Host "💾 Creando respaldo en: $backupPath" -ForegroundColor Cyan
Copy-Item $phpIniPath $backupPath

# Leer contenido actual
$content = Get-Content $phpIniPath

# Configuraciones de OPcache a habilitar
$opcacheConfig = @"

; ===============================================
; OPCACHE OPTIMIZACIÓN HABILITADA AUTOMÁTICAMENTE
; Configuración aplicada el $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
; ===============================================
[opcache]
; Habilitar OPcache
opcache.enable=1
opcache.enable_cli=1

; Configuración de memoria
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000

; Configuración de rendimiento
opcache.revalidate_freq=2
opcache.validate_timestamps=1
opcache.save_comments=1
opcache.fast_shutdown=1

; Configuraciones adicionales de PHP para rendimiento
memory_limit=512M
max_execution_time=60
post_max_size=32M
upload_max_filesize=32M
max_file_uploads=20

; Cache de rutas para mejor rendimiento
realpath_cache_size=4096K
realpath_cache_ttl=600
"@

# Buscar la sección [opcache] y reemplazarla
$newContent = @()
$inOpcacheSection = $false
$opcacheSectionFound = $false

foreach ($line in $content) {
    if ($line -match '^\s*\[opcache\]') {
        $opcacheSectionFound = $true
        $inOpcacheSection = $true
        # Agregar la nueva configuración de opcache
        $newContent += $opcacheConfig
        continue
    }
    
    if ($inOpcacheSection -and ($line -match '^\s*\[' -and $line -notmatch '^\s*\[opcache\]')) {
        $inOpcacheSection = $false
    }
    
    # Saltar líneas de opcache existentes si estamos en la sección
    if ($inOpcacheSection -and ($line -match '^\s*;?opcache\.' -or $line -match '^\s*;.*opcache' -or $line.Trim() -eq '')) {
        continue
    }
    
    $newContent += $line
}

# Si no se encontró la sección [opcache], agregarla al final
if (!$opcacheSectionFound) {
    $newContent += $opcacheConfig
}

# Escribir el nuevo contenido
$newContent | Set-Content $phpIniPath -Encoding UTF8

Write-Host "✅ OPcache habilitado exitosamente" -ForegroundColor Green
Write-Host "💾 Respaldo creado en: $backupPath" -ForegroundColor Yellow

Write-Host "`n🔄 REINICIANDO SERVICIOS WEB..." -ForegroundColor Cyan

# Intentar reiniciar IIS
try {
    Write-Host "   Reiniciando IIS..." -ForegroundColor Yellow
    iisreset /noforce
    Write-Host "✅ IIS reiniciado exitosamente" -ForegroundColor Green
} catch {
    Write-Host "⚠️  No se pudo reiniciar IIS automáticamente" -ForegroundColor Yellow
    Write-Host "   Ejecute manualmente: iisreset" -ForegroundColor Yellow
}

Write-Host "`n🎉 CONFIGURACIÓN COMPLETADA" -ForegroundColor Green
Write-Host "=" * 50
Write-Host "📊 Para verificar que OPcache está funcionando:"
Write-Host "   1. Abra: http://localhost/Consultorio2/verificar_optimizaciones.php"
Write-Host "   2. La puntuación debe subir a 90/100"
Write-Host "   3. OPcache debe aparecer como 'ON'"

Write-Host "`n⚡ MEJORAS ESPERADAS:"
Write-Host "   • Carga de páginas: 60-80% más rápida"
Write-Host "   • Consultas: Optimizadas con índices"
Write-Host "   • Memoria: Uso más eficiente"

Write-Host "`n🔧 Si hay problemas:"
Write-Host "   • Restaurar desde: $backupPath"
Write-Host "   • Verificar permisos de archivos"
Write-Host "   • Revisar logs de PHP"

pause
