<?php
// Versión simplificada para probar el odontograma en ver_consulta.php
require_once "config.php";

// Simular una consulta con dientes seleccionados
$consulta_simulada = [
    'id' => 31,
    'dientes_seleccionados' => '11,12,13,21,22,23',
    'nombre' => 'Paciente Prueba',
    'apellido' => 'Test',
    'fecha' => '2025-07-02'
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Prueba Odontograma en Consulta</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        #debug-info { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px; margin: 20px 0; }
        #odontograma-consulta-container { border: 2px solid #e9ecef; border-radius: 8px; padding: 15px; background-color: #f8f9fa; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🧪 Prueba de Odontograma en Vista de Consulta</h2>
        
        <div class="alert alert-info">
            <strong>Consulta simulada:</strong> ID <?php echo $consulta_simulada['id']; ?><br>
            <strong>Paciente:</strong> <?php echo $consulta_simulada['nombre'] . ' ' . $consulta_simulada['apellido']; ?><br>
            <strong>Dientes seleccionados:</strong> <?php echo $consulta_simulada['dientes_seleccionados']; ?>
        </div>

        <?php if (!empty($consulta_simulada['dientes_seleccionados'])): ?>
        <div class="card">
            <div class="card-header">
                <h3>Odontograma - Dientes Tratados</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-primary mb-3">
                    <strong>Dientes que deberían aparecer marcados:</strong> <?php echo htmlspecialchars($consulta_simulada['dientes_seleccionados']); ?>
                </div>
                
                <div id="odontograma-consulta-container">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando odontograma...</span>
                        </div>
                        <p class="mt-2">Cargando odontograma...</p>
                    </div>
                </div>
                
                <div id="debug-info">
                    <strong>Debug:</strong> Preparando carga del odontograma...<br>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="mt-4">
            <a href="ver_consulta.php?id=31" class="btn btn-primary">🔗 Ir a ver_consulta.php real</a>
            <a href="test_odontograma_directo.php" class="btn btn-secondary">🧪 Volver a diagnóstico</a>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Dientes seleccionados de la consulta
            const dientesSeleccionados = '<?php echo htmlspecialchars($consulta_simulada['dientes_seleccionados']); ?>';
            const dientesArray = dientesSeleccionados.split(',').map(d => parseInt(d.trim())).filter(d => !isNaN(d));
            
            // Función para agregar debug info
            function addDebugInfo(message) {
                $('#debug-info').append(message + '<br>');
                console.log('[ODONTOGRAMA DEBUG]', message);
            }
            
            addDebugInfo('✅ jQuery cargado');
            addDebugInfo('✅ Iniciando carga del odontograma...');
            addDebugInfo('✅ Dientes a marcar: ' + dientesArray.join(', '));
            
            // Cargar el odontograma desde el archivo PHP
            addDebugInfo('🔄 Haciendo petición AJAX a odontograma_svg.php...');
            
            $.ajax({
                url: 'odontograma_svg.php',
                type: 'GET',
                cache: false,
                timeout: 15000,
                beforeSend: function() {
                    addDebugInfo('📤 Enviando petición AJAX...');
                },
                success: function(data, textStatus, xhr) {
                    addDebugInfo('✅ AJAX exitoso. Status: ' + textStatus);
                    addDebugInfo('✅ Tamaño de respuesta: ' + data.length + ' caracteres');
                    
                    if (data && data.length > 100) {
                        // Insertar el odontograma en el contenedor
                        $('#odontograma-consulta-container').html(data);
                        addDebugInfo('✅ Odontograma insertado en el DOM');
                        
                        // Verificar que se insertó correctamente
                        if ($('#odontograma').length > 0) {
                            addDebugInfo('✅ Elemento SVG #odontograma encontrado');
                        } else {
                            addDebugInfo('❌ Elemento SVG #odontograma NO encontrado');
                        }
                        
                        if ($('.tooth-shape').length > 0) {
                            addDebugInfo('✅ Elementos .tooth-shape encontrados: ' + $('.tooth-shape').length);
                        } else {
                            addDebugInfo('❌ Elementos .tooth-shape NO encontrados');
                        }
                        
                        // Esperar a que se inicialice y luego marcar los dientes
                        setTimeout(function() {
                            addDebugInfo('🎯 Iniciando marcado de dientes...');
                            marcarDientesSeleccionados(dientesArray);
                        }, 1000);
                    } else {
                        addDebugInfo('❌ ERROR: Respuesta vacía o muy pequeña');
                        mostrarError('La respuesta del servidor está vacía');
                    }
                },
                error: function(xhr, status, error) {
                    addDebugInfo('❌ ERROR AJAX: ' + status + ' - ' + error);
                    addDebugInfo('❌ Status Code: ' + xhr.status);
                    addDebugInfo('❌ Response Text: ' + xhr.responseText.substring(0, 200));
                    mostrarError('Error al cargar odontograma: ' + status + ' - ' + error);
                },
                complete: function() {
                    addDebugInfo('🏁 Petición AJAX completada');
                }
            });
            
            // Función para mostrar error
            function mostrarError(mensaje) {
                $('#odontograma-consulta-container').html(
                    '<div class="alert alert-danger">' +
                    '<h5>⚠️ Error al cargar odontograma</h5>' +
                    '<p>' + mensaje + '</p>' +
                    '<p><strong>Dientes seleccionados:</strong> ' + dientesSeleccionados + '</p>' +
                    '<button class="btn btn-sm btn-secondary" onclick="location.reload()">🔄 Intentar de nuevo</button>' +
                    '</div>'
                );
            }
            
            // Función para marcar los dientes seleccionados
            function marcarDientesSeleccionados(dientes) {
                addDebugInfo('🎯 Ejecutando marcarDientesSeleccionados con ' + dientes.length + ' dientes');
                
                // Verificar que el SVG esté presente
                const svg = document.getElementById('odontograma');
                if (!svg) {
                    addDebugInfo('❌ ERROR: No se encontró el elemento SVG #odontograma');
                    return;
                }
                addDebugInfo('✅ SVG encontrado correctamente');
                
                // Hacer que todos los dientes sean de solo lectura
                $('.tooth-shape').css('pointer-events', 'none');
                $('.tooth-shape').css('cursor', 'default');
                $('.tooth-shape').css('opacity', '0.6');
                addDebugInfo('✅ Dientes configurados como solo lectura');
                
                // Marcar los dientes seleccionados
                let dientesEncontrados = 0;
                dientes.forEach(function(numDiente) {
                    const diente = $('.tooth-shape[data-num="' + numDiente + '"]');
                    if (diente.length > 0) {
                        diente.addClass('tooth-selected-readonly');
                        diente.css({
                            'fill': '#28a745',
                            'stroke': '#155724',
                            'stroke-width': '3',
                            'opacity': '1',
                            'filter': 'drop-shadow(0 2px 8px #28a74555)'
                        });
                        dientesEncontrados++;
                        addDebugInfo('✅ Diente ' + numDiente + ' marcado correctamente');
                    } else {
                        addDebugInfo('❌ Diente ' + numDiente + ' NO encontrado en el SVG');
                    }
                });
                
                addDebugInfo('🎉 Proceso completado: ' + dientesEncontrados + '/' + dientes.length + ' dientes marcados');
                
                // Agregar mensaje de éxito
                if (dientesEncontrados === dientes.length) {
                    $('#odontograma-consulta-container').prepend(
                        '<div class="alert alert-success">' +
                        '✅ ¡Odontograma cargado exitosamente! ' + dientesEncontrados + ' dientes marcados en verde.' +
                        '</div>'
                    );
                } else {
                    $('#odontograma-consulta-container').prepend(
                        '<div class="alert alert-warning">' +
                        '⚠️ Odontograma cargado parcialmente. ' + dientesEncontrados + '/' + dientes.length + ' dientes marcados.' +
                        '</div>'
                    );
                }
            }
        });
    </script>
</body>
</html>

