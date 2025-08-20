# Script para localizar recursos externos en archivos HTML
$directorio = "c:\inetpub\wwwroot\Consultorio2"

# Definir los reemplazos para HTML
$reemplazos = @{
    'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css' = 'assets/css/bootstrap.min.css';
    'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css' = 'assets/css/bootstrap.min.css';
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css' = 'assets/css/fontawesome.min.css';
    'https://code.jquery.com/jquery-3.5.1.min.js' = 'assets/js/jquery.min.js';
    'https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js' = 'assets/js/bootstrap.bundle.min.js';
    'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js' = 'assets/js/bootstrap.bundle.min.js'
}

# Obtener todos los archivos HTML
$archivos = Get-ChildItem -Path $directorio -Filter "*.html" -Recurse

Write-Host "Procesando $($archivos.Count) archivos HTML..."

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
Write-Host "Proceso HTML completado!"
Write-Host "Archivos HTML modificados: $archivosModificados"
