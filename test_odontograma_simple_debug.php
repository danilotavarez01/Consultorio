<?php
session_start();
require_once "config.php";

// Simular una consulta con dientes (para evitar problemas de autenticación)
$consulta = [
    'id' => 31,
    'nombre' => 'Nilo',
    'apellido' => 'Tavarez',
    'dientes_seleccionados' => '11,12,13,21,22,23',
    'fecha' => '2025-07-02',
    'motivo_consulta' => 'Prueba',
    'diagnostico' => 'Prueba',
    'tratamiento' => 'Prueba'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test Odontograma Simple</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .debug { background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0; font-family: monospace; }
    </style>
</head>
<body>
    <h2>🧪 Test Directo del Odontograma en Ver Consulta</h2>
    
    <div class="debug">
        <strong>Datos de prueba:</strong><br>
        - Consulta ID: <?php echo $consulta['id']; ?><br>
        - Paciente: <?php echo $consulta['nombre'] . ' ' . $consulta['apellido']; ?><br>
        - Dientes: <?php echo $consulta['dientes_seleccionados']; ?><br>
        - Dientes vacío?: <?php echo empty($consulta['dientes_seleccionados']) ? 'SÍ' : 'NO'; ?>
    </div>
    
    <?php if (!empty($consulta['dientes_seleccionados'])): ?>
    <div class="alert alert-success">
        ✅ Condición <?php echo htmlspecialchars('!empty($consulta[\'dientes_seleccionados\'])'); ?> es VERDADERA
    </div>
    
    <div class="card">
        <div class="card-header">
            <h4>Odontograma - Dientes Tratados</h4>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-3">
                <strong>Dientes seleccionados:</strong> <?php echo htmlspecialchars($consulta['dientes_seleccionados']); ?>
            </div>
            
            <div id="odontograma-consulta-container" class="mb-3">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando odontograma...</span>
                    </div>
                    <p class="mt-2">Cargando odontograma...</p>
                </div>
            </div>
            
            <div id="debug-info" class="debug">
                <strong>Debug:</strong> Preparando carga del odontograma...<br>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-warning">
        ❌ Condición <?php echo htmlspecialchars('!empty($consulta[\'dientes_seleccionados\'])'); ?> es FALSA
    </div>
    <?php endif; ?>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    
    <?php if (!empty($consulta['dientes_seleccionados'])): ?>
    <script>
        $(document).ready(function() {
            console.log('=== INICIO DEBUG ODONTOGRAMA ===');
            
            // Dientes seleccionados de la consulta
            const dientesSeleccionados = '<?php echo htmlspecialchars($consulta['dientes_seleccionados']); ?>';
            const dientesArray = dientesSeleccionados.split(',').map(d => parseInt(d.trim())).filter(d => !isNaN(d));
            
            console.log('Dientes seleccionados string:', dientesSeleccionados);
            console.log('Dientes array:', dientesArray);
            
            // Función para agregar debug info
            function addDebugInfo(message) {
                $('#debug-info').append(message + '<br>');
                console.log('[ODONTOGRAMA]', message);
            }
            
            addDebugInfo('Iniciando carga del odontograma...');
            addDebugInfo('Dientes a marcar: ' + dientesArray.join(', '));
            
            // Cargar el odontograma desde el archivo PHP
            addDebugInfo('Haciendo petición AJAX a odontograma_svg.php...');
            
            $.ajax({
                url: 'odontograma_svg.php',
                type: 'GET',
                cache: false,
                timeout: 15000,
                success: function(data, textStatus, xhr) {
                    console.log('AJAX Success!', textStatus, data.length);
                    addDebugInfo('✅ AJAX exitoso. Status: ' + textStatus);
                    addDebugInfo('Tamaño de respuesta: ' + data.length + ' caracteres');
                    
                    if (data && data.length > 100) {
                        // Insertar el odontograma en el contenedor
                        $('#odontograma-consulta-container').html(data);
                        addDebugInfo('✅ Odontograma insertado en el DOM');
                        
                        // Verificar que se insertó correctamente
                        const svg = document.getElementById('odontograma');
                        if (svg) {
                            addDebugInfo('✅ Elemento SVG encontrado');
                        } else {
                            addDebugInfo('❌ Elemento SVG NO encontrado');
                        }
                        
                        // Esperar y luego marcar los dientes
                        setTimeout(function() {
                            addDebugInfo('Iniciando marcado de dientes...');
                            marcarDientesSeleccionados(dientesArray);
                        }, 1000);
                    } else {
                        addDebugInfo('❌ ERROR: Respuesta vacía o muy pequeña');
                        mostrarError('La respuesta del servidor está vacía');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error, xhr);
                    addDebugInfo('❌ ERROR AJAX: ' + status + ' - ' + error);
                    addDebugInfo('Status Code: ' + xhr.status);
                    if (xhr.responseText) {
                        addDebugInfo('Response Text: ' + xhr.responseText.substring(0, 200));
                    }
                    mostrarError('Error al cargar odontograma: ' + status + ' - ' + error);
                }
            });
            
            // Función para mostrar error
            function mostrarError(mensaje) {
                $('#odontograma-consulta-container').html(
                    '<div class="alert alert-danger">' +
                    '<h5>⚠️ Error al cargar odontograma</h5>' +
                    '<p>' + mensaje + '</p>' +
                    '<p><strong>Dientes seleccionados:</strong> ' + dientesSeleccionados + '</p>' +
                    '<button class="btn btn-sm btn-secondary" onclick="location.reload()">🔄 Reintentar</button>' +
                    '</div>'
                );
            }
            
            // Función para marcar los dientes seleccionados
            function marcarDientesSeleccionados(dientes) {
                addDebugInfo('=== MARCANDO DIENTES ===');
                addDebugInfo('Ejecutando marcarDientesSeleccionados con ' + dientes.length + ' dientes');
                
                // Verificar que el SVG esté presente
                const svg = document.getElementById('odontograma');
                if (!svg) {
                    addDebugInfo('❌ ERROR: No se encontró el elemento SVG #odontograma');
                    return;
                }
                addDebugInfo('✅ SVG encontrado correctamente');
                
                // Verificar cuántos elementos tooth-shape hay
                const toothElements = $('.tooth-shape');
                addDebugInfo('✅ Elementos tooth-shape encontrados: ' + toothElements.length);
                
                // Hacer que todos los dientes sean de solo lectura
                toothElements.css({
                    'pointer-events': 'none',
                    'cursor': 'default',
                    'opacity': '0.6'
                });
                addDebugInfo('✅ Dientes configurados como solo lectura');
                
                // Marcar los dientes seleccionados
                let dientesEncontrados = 0;
                dientes.forEach(function(numDiente) {
                    const diente = $('.tooth-shape[data-num="' + numDiente + '"]');
                    addDebugInfo('Buscando diente ' + numDiente + ': ' + (diente.length > 0 ? 'ENCONTRADO' : 'NO ENCONTRADO'));
                    
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
                
                addDebugInfo('=== RESUMEN ===');
                addDebugInfo('Proceso completado: ' + dientesEncontrados + '/' + dientes.length + ' dientes marcados');
                
                if (dientesEncontrados === dientes.length) {
                    addDebugInfo('🎉 ¡ÉXITO! Todos los dientes fueron marcados correctamente');
                } else {
                    addDebugInfo('⚠️ Algunos dientes no fueron encontrados');
                }
            }
            
            console.log('=== FIN SETUP DEBUG ODONTOGRAMA ===');
        });
    </script>
    
    <style>
        .tooth-selected-readonly {
            pointer-events: none !important;
            cursor: default !important;
        }
        
        #odontograma-consulta-container {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            background-color: #f8f9fa;
            min-height: 200px;
        }
    </style>
    <?php endif; ?>
    
    <hr>
    <p><a href="ver_consulta.php?id=31">🔗 Ver consulta real</a></p>
    <p><a href="odontograma_svg.php">🔗 Ver odontograma directo</a></p>
</body>
</html>
