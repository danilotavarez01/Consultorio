<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Prueba de Odontograma</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        body {
            padding: 20px;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>    <div class="container">
        <h1>Prueba de Odontograma Mejorado con Cuadrantes</h1>
        <hr>
        
        <div id="info-panel" class="mb-3"></div>
        
        <!-- Contenedor para campos dinámicos -->
        <div id="campos_dinamicos" class="border p-3 mb-3">
            <h5>Este es el contenedor de campos dinámicos donde aparecerá el odontograma</h5>
        </div>
        
        <div class="alert alert-info">
            <p>Esta página simula la estructura básica de nueva_consulta.php pero sin necesidad de consultas a la base de datos 
            ni otras dependencias. Servirá para verificar el correcto funcionamiento del odontograma.</p>
        </div>
        
        <button id="btn-forzar" class="btn btn-primary">Forzar inserción del odontograma</button>
    </div>
    
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    
    <?php include_once 'forzar_odontograma.php'; ?>
      <style>
        /* Estilos adicionales para el odontograma */
        .cuadrante-titulo {
            font-weight: bold;
            margin: 10px 0;
            color: #0056b3;
            padding: 5px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .tabla-cuadrantes {
            width: 100%;
            margin-top: 20px;
            border-collapse: separate;
            border-spacing: 10px;
        }
        
        .btn-diente:hover {
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.5);
            z-index: 1;
        }
    </style>
    
    <script>
        $(document).ready(function() {
            // Botón para forzar la inserción manualmente
            $('#btn-forzar').click(function() {
                if (typeof insertarOdontograma === 'function') {
                    insertarOdontograma();
                    $(this).prop('disabled', true).text('Odontograma insertado');
                } else {
                    alert('La función insertarOdontograma no está disponible');
                }
            });
            
            // Mostrar información sobre la nueva característica
            $('#info-panel').html(
                '<div class="alert alert-success">' +
                '<h5>Odontograma Mejorado</h5>' +
                '<p>El nuevo odontograma incluye:</p>' +
                '<ul>' +
                '<li>Organización por cuadrantes</li>' +
                '<li>Formas diferentes según el tipo de diente (molares, premolares, incisivos)</li>' +
                '<li>Diseño visual más cercano a una carta dental real</li>' +
                '</ul>' +
                '</div>'
            );
        });
    </script>
</body>
</html>

