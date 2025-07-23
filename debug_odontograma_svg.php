<?php
// Archivo de diagnóstico para detectar problemas con odontograma_svg.php
header('Content-Type: text/html; charset=utf-8');

// Intentar cargar el archivo para verificar si hay errores
$odontograma_path = __DIR__ . '/odontograma_svg.php';
$output = '';
$error = null;

try {
    // Intentar incluir el archivo en un buffer de salida
    ob_start();
    include $odontograma_path;
    $output = ob_get_clean();
    
    // Obtener información del archivo
    $filesize = filesize($odontograma_path);
    $last_modified = date("Y-m-d H:i:s", filemtime($odontograma_path));
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de odontograma_svg.php</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        pre { max-height: 300px; overflow-y: auto; background: #f8f9fa; padding: 10px; border-radius: 5px; }
        .output-container { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Diagnóstico de odontograma_svg.php</h1>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Información del Archivo</h5>
            </div>
            <div class="card-body">
                <?php if (file_exists($odontograma_path)): ?>
                    <div class="alert alert-success">
                        <p><strong>El archivo existe</strong></p>
                        <ul>
                            <li>Ruta: <?php echo $odontograma_path; ?></li>
                            <li>Tamaño: <?php echo $filesize; ?> bytes</li>
                            <li>Última modificación: <?php echo $last_modified; ?></li>
                            <li>¿Es legible?: <?php echo is_readable($odontograma_path) ? 'Sí' : 'No'; ?></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <p><strong>El archivo no existe</strong></p>
                        <p>Ruta buscada: <?php echo $odontograma_path; ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <p><strong>Error al cargar el archivo:</strong></p>
                        <pre><?php echo htmlspecialchars($error); ?></pre>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Prueba Directa de Carga</h5>
            </div>
            <div class="card-body">
                <p>El siguiente botón intentará cargar el contenido del odontograma directamente:</p>
                <button id="load-direct" class="btn btn-primary">Cargar Contenido</button>
                
                <div id="status-message" class="mt-3"></div>
                <div id="content-preview" class="output-container d-none">
                    <h6>Vista previa del contenido:</h6>
                    <pre id="content-code"></pre>
                </div>
                
                <div id="test-container" class="mt-4">
                    <h6>Prueba de visualización:</h6>
                    <div id="test-area" class="border p-3">
                        <p>El contenido del odontograma se insertará aquí al hacer clic en "Probar Visualización":</p>
                    </div>
                    <button id="test-visual" class="btn btn-success mt-2">Probar Visualización</button>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">Solución de Problemas</h5>
            </div>
            <div class="card-body">
                <p>Si el odontograma no se carga correctamente:</p>
                <ol>
                    <li>Verifica que el archivo <code>odontograma_svg.php</code> exista y sea legible.</li>
                    <li>Asegúrate de que no haya errores de sintaxis PHP en el archivo.</li>
                    <li>Comprueba que el HTML/SVG generado sea válido.</li>
                    <li>Intenta usar la versión básica del odontograma:</li>
                </ol>
                
                <button id="load-basic" class="btn btn-warning">Usar Versión Básica</button>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Botón para cargar el contenido directamente
            $('#load-direct').click(function() {
                $('#status-message').html('<div class="alert alert-info">Cargando contenido...</div>');
                
                $.ajax({
                    url: 'odontograma_svg.php',
                    type: 'GET',
                    timeout: 5000,
                    success: function(data) {
                        $('#status-message').html('<div class="alert alert-success">Contenido cargado correctamente.</div>');
                        $('#content-preview').removeClass('d-none');
                        $('#content-code').text(data.substring(0, 500) + '... [contenido truncado]');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $('#status-message').html('<div class="alert alert-danger">Error al cargar el contenido: ' + textStatus + ' - ' + errorThrown + '</div>');
                    }
                });
            });
            
            // Botón para probar visualización
            $('#test-visual').click(function() {
                $('#test-area').html('<p>Cargando visualización...</p>');
                
                $.get('odontograma_svg.php', function(data) {
                    $('#test-area').html(data);
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    $('#test-area').html('<div class="alert alert-danger">Error al cargar el odontograma: ' + textStatus + ' - ' + errorThrown + '</div>');
                });
            });
            
            // Botón para usar versión básica
            $('#load-basic').click(function() {
                window.location.href = 'test_odontograma_simple.php';
            });
        });
    </script>
</body>
</html>
