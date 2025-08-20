# Script completo para localizar TODOS los recursos externos
$directorio = "c:\inetpub\wwwroot\Consultorio2"

# Definir los reemplazos - VERSIÓN COMPLETA
$reemplazos = @{
    # Bootstrap CSS - versiones múltiples
    'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css' = 'assets/css/bootstrap.min.css';
    'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' = 'assets/css/bootstrap-5.1.3.min.css';
    'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css' = 'assets/css/bootstrap.min.css';
    'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css' = 'assets/css/bootstrap.min.css';
    
    # Font Awesome CSS - versiones múltiples
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css' = 'assets/css/fontawesome.min.css';
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css' = 'assets/css/fontawesome.min.css';
    
    # jQuery UI CSS
    'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css' = 'assets/css/jquery-ui.css';
    
    # jQuery JS - versiones múltiples
    'https://code.jquery.com/jquery-3.5.1.min.js' = 'assets/js/jquery.min.js';
    'https://code.jquery.com/jquery-3.6.0.min.js' = 'assets/js/jquery.min.js';
    
    # jQuery UI JS
    'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js' = 'assets/js/jquery-ui.min.js';
    
    # Popper JS - versiones múltiples
    'https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js' = 'assets/js/popper.min.js';
    'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js' = 'assets/js/popper.min.js';
    'https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js' = 'assets/js/popper-2.5.4.min.js';
    
    # Bootstrap JS - versiones múltiples
    'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js' = 'assets/js/bootstrap.min.js';
    'https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js' = 'assets/js/bootstrap.bundle.min.js';
    'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js' = 'assets/js/bootstrap.bundle.min.js';
    'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js' = 'assets/js/bootstrap.bundle.min.js';
    'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js' = 'assets/js/bootstrap.bundle.min.js';
    'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js' = 'assets/js/bootstrap.bundle.min.js'
}

# Obtener todos los archivos PHP y HTML
$archivos = Get-ChildItem -Path $directorio -Include "*.php", "*.html" -Recurse

Write-Host "Procesando $($archivos.Count) archivos (PHP y HTML)..."

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
Write-Host "Proceso COMPLETO terminado!"
Write-Host "Archivos modificados: $archivosModificados"
