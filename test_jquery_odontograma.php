<?php
// Verificar la carga correcta de jQuery
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test jQuery</title>
    <script src="assets/js/jquery.min.js"></script>
</head>
<body>
    <h2>Test de jQuery y odontograma</h2>
    
    <div id="test-container"></div>
    <div id="campos_dinamicos"></div>
    
    <script>
        $(document).ready(function() {
            $('#test-container').html('<p>jQuery está cargado correctamente!</p>');
            console.log('jQuery version:', $.fn.jquery);
            console.log('Contenedor campos_dinamicos existe:', $('#campos_dinamicos').length > 0);
            
            // Verificar si la variable global MOSTRAR_ODONTOGRAMA está definida
            if (typeof window.MOSTRAR_ODONTOGRAMA !== 'undefined') {
                $('#test-container').append('<p>MOSTRAR_ODONTOGRAMA: ' + window.MOSTRAR_ODONTOGRAMA + '</p>');
            } else {
                $('#test-container').append('<p>MOSTRAR_ODONTOGRAMA no está definido</p>');
            }
            
            // Incluir el odontograma manualmente
            $.getScript('forzar_odontograma.php')
                .done(function() {
                    $('#test-container').append('<p>Script forzar_odontograma.php cargado correctamente</p>');
                    // Intentar llamar a la función si está disponible
                    if (typeof insertarOdontograma === 'function') {
                        $('#test-container').append('<p>Función insertarOdontograma encontrada, intentando ejecutar...</p>');
                        insertarOdontograma();
                    } else {
                        $('#test-container').append('<p>ERROR: Función insertarOdontograma no está disponible</p>');
                    }
                })
                .fail(function(jqxhr, settings, exception) {
                    $('#test-container').append('<p>ERROR cargando forzar_odontograma.php: ' + exception + '</p>');
                });
        });
    </script>
</body>
</html>

