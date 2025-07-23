<?php
// Archivo de prueba simple para diagnosticar problemas con el odontograma
header('Content-Type: text/html; charset=utf-8');
require_once "config.php";

// Establecer que sí queremos mostrar el odontograma para esta prueba
$mostrarOdontograma = true;
$especialidad = ['nombre' => 'Odontología'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Prueba Simple Odontograma</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .test-container { max-width: 1000px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>Prueba Simple del Odontograma</h1>
        <p class="lead">Esta página prueba la inclusión directa del odontograma sin depender de otras configuraciones.</p>
        
        <hr>
        
        <div class="alert alert-info">
            <p><strong>Variables de prueba:</strong></p>
            <pre>
$mostrarOdontograma = true;
$especialidad = ['nombre' => 'Odontología'];
            </pre>
        </div>
        
        <!-- Contenedor para campos dinámicos -->
        <div id="campos_dinamicos" class="mt-4 border p-3">
            <h5>Contenedor #campos_dinamicos</h5>
            <p>Aquí debería aparecer el odontograma</p>
        </div>
        
        <div class="mt-4">
            <button id="insertar-btn" class="btn btn-primary">Insertar Odontograma Manualmente</button>
            <button id="check-btn" class="btn btn-secondary">Verificar Variables</button>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Establecer una variable global para indicar que sí queremos mostrar el odontograma
        window.MOSTRAR_ODONTOGRAMA = true;
        window.ESPECIALIDAD_NOMBRE = 'Odontología';
        
        $(document).ready(function() {
            console.log("Página de prueba cargada");
            console.log("MOSTRAR_ODONTOGRAMA:", window.MOSTRAR_ODONTOGRAMA);
            console.log("ESPECIALIDAD_NOMBRE:", window.ESPECIALIDAD_NOMBRE);
            
            // Botón para insertar el odontograma manualmente
            $('#insertar-btn').click(function() {
                $.get('odontograma_svg.php', function(data) {
                    $('#campos_dinamicos').html(data);
                    alert("Odontograma insertado manualmente");
                }).fail(function(xhr, status, error) {
                    alert("Error al cargar odontograma: " + error);
                });
            });
            
            // Botón para verificar variables
            $('#check-btn').click(function() {
                alert("MOSTRAR_ODONTOGRAMA: " + window.MOSTRAR_ODONTOGRAMA + "\n" +
                      "ESPECIALIDAD_NOMBRE: " + window.ESPECIALIDAD_NOMBRE + "\n" +
                      "Existe #campos_dinamicos: " + ($('#campos_dinamicos').length > 0));
            });
        });
    </script>
</body>
</html>
