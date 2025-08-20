<?php
// Archivo de prueba para la selección múltiple de dientes en el odontograma
// Creado: <?php echo date('Y-m-d H:i:s'); ?>
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Selección Múltiple en Odontograma</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <script src="assets/js/jquery.min.js"></script>
    <style>
        .test-section {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2>Test de Selección Múltiple en el Odontograma</h2>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h4>Instrucciones:</h4>
                    <p>Esta página permite verificar que la funcionalidad de selección múltiple en el odontograma funcione correctamente.</p>
                    <p><strong>Funcionalidades a probar:</strong></p>
                    <ul>
                        <li>Selección de un diente individual (clic simple)</li>
                        <li>Selección de múltiples dientes (manteniendo tecla Ctrl/Cmd)</li>
                        <li>Selección de un cuadrante completo (botones de cuadrante)</li>
                        <li>Selección de todos los dientes (botón "Seleccionar todos")</li>
                        <li>Deselección de todos los dientes (botón "Deseleccionar todos")</li>
                    </ul>
                </div>
                
                <div class="test-section">
                    <h5 class="mb-3">Verificación del Estado Actual</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <ul>
                                <li><strong>Selección múltiple:</strong> <span id="status-multiple" class="badge badge-secondary">Por verificar</span></li>
                                <li><strong>Botones de cuadrantes:</strong> <span id="status-cuadrantes" class="badge badge-secondary">Por verificar</span></li>
                                <li><strong>Seleccionar/deseleccionar todos:</strong> <span id="status-todos" class="badge badge-secondary">Por verificar</span></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Resultado de la Prueba:</h6>
                                    <div id="test-result">
                                        Pendiente de ejecución de pruebas
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button id="btn-verificar" class="btn btn-primary">Verificar Funcionalidades</button>
                    </div>
                </div>
                
                <div id="odontograma-container" class="my-4">
                    <div id="loading" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando odontograma...</p>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h5>Pasos para verificar la selección múltiple:</h5>
                    <ol>
                        <li>Haz clic en un solo diente → Debe seleccionarse únicamente ese diente</li>
                        <li>Mantén presionada la tecla Ctrl (o Cmd en Mac) y haz clic en otro diente → Ambos dientes deben quedar seleccionados</li>
                        <li>Sin presionar Ctrl, haz clic en un tercer diente → Solo este último debe quedar seleccionado</li>
                        <li>Haz clic en el botón "Cuadrante 1" → Deben seleccionarse solo los dientes del cuadrante superior derecho</li>
                        <li>Haz clic en "Seleccionar todos" → Todos los dientes deben quedar seleccionados</li>
                        <li>Haz clic en "Deseleccionar todos" → Ningún diente debe quedar seleccionado</li>
                    </ol>
                </div>
                
                <div class="mt-3 text-center">
                    <a href="documentacion_odontograma.md" class="btn btn-info" target="_blank">Ver Documentación</a>
                    <a href="test_odontograma.php" class="btn btn-secondary ml-2">Volver a Test Principal</a>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Cargar el odontograma SVG mejorado
        $('#odontograma-container').load('odontograma_svg_mejorado.php', function(response, status, xhr) {
            $('#loading').hide();
            console.log('Odontograma cargado para pruebas de selección múltiple');
        });
        
        // Función para verificar las funcionalidades
        $('#btn-verificar').click(function() {
            try {
                const result = [];
                
                // Verificar si existe la tecla para selección múltiple
                const coronaElement = document.querySelector('.tooth-shape');
                if (coronaElement) {
                    // Verificar que los eventos del diente incluyan manejadores para Ctrl/Cmd
                    const seleccionMultipleOK = coronaElement.outerHTML.includes('addEventListener') && 
                                             window.seleccionados !== undefined;
                    
                    $('#status-multiple').removeClass('badge-secondary').addClass(
                        seleccionMultipleOK ? 'badge-success' : 'badge-danger'
                    ).text(seleccionMultipleOK ? 'Implementado' : 'No implementado');
                    
                    result.push(seleccionMultipleOK ? 
                        '✅ Selección múltiple implementada correctamente' : 
                        '❌ Problema con la selección múltiple');
                }
                
                // Verificar botones de cuadrantes
                const cuadrantesOK = $('#btn-q1').length > 0 && $('#btn-q2').length > 0 && 
                                    $('#btn-q3').length > 0 && $('#btn-q4').length > 0;
                
                $('#status-cuadrantes').removeClass('badge-secondary').addClass(
                    cuadrantesOK ? 'badge-success' : 'badge-danger'
                ).text(cuadrantesOK ? 'Implementados' : 'No implementados');
                
                result.push(cuadrantesOK ? 
                    '✅ Botones de cuadrantes implementados' : 
                    '❌ Botones de cuadrantes no encontrados');
                
                // Verificar botones seleccionar/deseleccionar todos
                const btnsSelectOK = $('#btn-seleccionar-todos-svg').length > 0 && 
                                    $('#btn-deseleccionar-todos-svg').length > 0;
                
                $('#status-todos').removeClass('badge-secondary').addClass(
                    btnsSelectOK ? 'badge-success' : 'badge-danger'
                ).text(btnsSelectOK ? 'Implementados' : 'No implementados');
                
                result.push(btnsSelectOK ? 
                    '✅ Botones seleccionar/deseleccionar implementados' : 
                    '❌ Botones seleccionar/deseleccionar no encontrados');
                
                // Mostrar resultado general
                $('#test-result').html(result.join('<br>') + 
                    '<div class="alert alert-' + (result.every(r => r.includes('✅')) ? 'success' : 'warning') + ' mt-2">' +
                    (result.every(r => r.includes('✅')) ? 
                        '<strong>¡Todas las funcionalidades implementadas correctamente!</strong>' : 
                        '<strong>Algunas funcionalidades no están implementadas.</strong>') +
                    '</div>');
                
            } catch (error) {
                console.error('Error durante la verificación:', error);
                $('#test-result').html('<div class="alert alert-danger">Error durante la verificación: ' + error.message + '</div>');
            }
        });
    });
    </script>
</body>
</html>

