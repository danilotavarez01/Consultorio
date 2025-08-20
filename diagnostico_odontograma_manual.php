<?php
// Diagnóstico del odontograma 
?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico Odontograma</title>
    <script src="assets/js/jquery.min.js"></script>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Diagnóstico del Odontograma</h2>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                Estado del Odontograma
            </div>
            <div class="card-body">
                <div id="status"></div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                Probar inserción del Odontograma
            </div>
            <div class="card-body">
                <button id="btn-test" class="btn btn-primary">Insertar Odontograma aquí</button>
                <div id="test-container" class="mt-3"></div>
                <div id="campos_dinamicos" class="mt-3"></div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        const status = $('#status');
        
        // Mostrar versión de jQuery
        status.append(`<p><strong>jQuery Version:</strong> ${$.fn.jquery}</p>`);
        
        // Verificar si el div necesario existe
        status.append(`<p><strong>Div #campos_dinamicos existe:</strong> ${$('#campos_dinamicos').length > 0 ? 'Sí' : 'No'}</p>`);
        
        // Verificar si la variable global existe
        status.append(`<p><strong>Variable MOSTRAR_ODONTOGRAMA existe:</strong> ${typeof window.MOSTRAR_ODONTOGRAMA !== 'undefined' ? 'Sí' : 'No'}</p>`);
        
        // Verificar si la función existe
        status.append(`<p><strong>Función insertarOdontograma existe:</strong> ${typeof window.insertarOdontograma === 'function' ? 'Sí' : 'No'}</p>`);
        
        // Botón para probar la inserción del odontograma
        $('#btn-test').click(function() {
            status.append(`<p>Intentando cargar forzar_odontograma.php...</p>`);
            
            // Cargar el script del odontograma
            $.getScript('forzar_odontograma.php')
                .done(function() {
                    status.append(`<p class="text-success">Script forzar_odontograma.php cargado correctamente</p>`);
                    
                    // Verificar si la función está disponible ahora
                    if (typeof window.insertarOdontograma === 'function') {
                        status.append(`<p>Función insertarOdontograma encontrada, ejecutando...</p>`);
                        window.insertarOdontograma();
                    } else {
                        status.append(`<p class="text-danger">ERROR: Función insertarOdontograma no disponible después de cargar el script</p>`);
                    }
                })
                .fail(function(jqxhr, settings, exception) {
                    status.append(`<p class="text-danger">ERROR cargando forzar_odontograma.php: ${exception}</p>`);
                });
        });
    });
    </script>
</body>
</html>

