# Script FINAL para localizar los últimos recursos externos
$directorio = "c:\inetpub\wwwroot\Consultorio2"

# Definir los reemplazos finales
$reemplazos = @{
    # jQuery Slim
    'https://code.jquery.com/jquery-3.5.1.slim.min.js' = 'assets/js/jquery-3.5.1.slim.min.js';
    
    # Font Awesome 6.0.0
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' = 'assets/css/fontawesome-6.0.0.min.css'
}

# Obtener todos los archivos PHP y HTML
$archivos = Get-ChildItem -Path $directorio -Include "*.php", "*.html" -Recurse

Write-Host "Procesando recursos finales en $($archivos.Count) archivos..."

$archivosModificados = 0

foreach ($archivo in $archivos) {
    $contenido = Get-Content $archivo.FullName -Raw -Encoding UTF8
    $contenidoOriginal = $contenido
    
    foreach ($buscar in $reemplazos.Keys) {
        $reemplazar = $reemplazos[$buscar]
        if ($contenido -match [regex]::Escape($buscar)) {
            $contenido = $contenido -replace [regex]::Escape($buscar), $reemplazar
            Write-Host "  - Reemplazado en: $($archivo.Name)"
        }
    }
    
    # Solo escribir si hubo cambios
    if ($contenido -ne $contenidoOriginal) {
        Set-Content -Path $archivo.FullName -Value $contenido -Encoding UTF8
        $archivosModificados++
    }
}

Write-Host ""
Write-Host "Proceso FINAL terminado!"
Write-Host "Archivos modificados: $archivosModificados"
