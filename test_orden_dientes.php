<?php
// Script para probar específicamente el orden de los dientes en el odontograma
// Creado: <?php echo date('Y-m-d H:i:s'); ?>
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Orden de Dientes</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2>Test de Orden de Dientes en Odontograma</h2>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h4>Instrucciones:</h4>
                    <p>Esta página verifica que el orden de los dientes en el odontograma sea el correcto.</p>
                    <p>Los dientes deben aparecer en el siguiente orden:</p>
                    <ul>
                        <li><strong>Primer cuadrante (superior derecho):</strong> 18, 17, 16, 15, 14, 13, 12, 11 (de izquierda a derecha)</li>
                        <li><strong>Segundo cuadrante (superior izquierdo):</strong> 21, 22, 23, 24, 25, 26, 27, 28 (de izquierda a derecha)</li>
                        <li><strong>Tercer cuadrante (inferior izquierdo):</strong> 31, 32, 33, 34, 35, 36, 37, 38 (de izquierda a derecha)</li>
                        <li><strong>Cuarto cuadrante (inferior derecho):</strong> 48, 47, 46, 45, 44, 43, 42, 41 (de izquierda a derecha)</li>
                    </ul>
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
                    <h4>Verificación de Orden:</h4>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Primer Cuadrante (Superior Derecho)</h5>
                                    <div id="verificacion-c1" class="verificacion-cuadrante alert alert-secondary">
                                        Esperando datos...
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5>Segundo Cuadrante (Superior Izquierdo)</h5>
                                    <div id="verificacion-c2" class="verificacion-cuadrante alert alert-secondary">
                                        Esperando datos...
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <h5>Cuarto Cuadrante (Inferior Derecho)</h5>
                                    <div id="verificacion-c4" class="verificacion-cuadrante alert alert-secondary">
                                        Esperando datos...
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5>Tercer Cuadrante (Inferior Izquierdo)</h5>
                                    <div id="verificacion-c3" class="verificacion-cuadrante alert alert-secondary">
                                        Esperando datos...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3 text-center">
                    <button id="verificar-orden" class="btn btn-primary">Verificar Orden de Dientes</button>
                    <button id="volver" class="btn btn-secondary ml-2" onclick="window.location.href='test_odontograma.php'">Volver a Test Principal</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Cargar el odontograma SVG mejorado
        $('#odontograma-container').load('odontograma_svg_mejorado.php', function(response, status, xhr) {
            $('#loading').hide();
            
            if (status === "error") {
                $('#odontograma-container').html('<div class="alert alert-danger">Error al cargar el odontograma: ' + xhr.status + ' ' + xhr.statusText + '</div>');
            } else {
                console.log('Odontograma cargado con éxito');
            }
        });
        
        $('#verificar-orden').click(function() {
            // Verificar el orden de los dientes en cada cuadrante
            
            // Obtener todos los dientes visibles
            const dientesVisibles = [];
            
            $('.tooth-label').each(function() {
                const num = parseInt($(this).text().trim());
                const x = parseFloat($(this).attr('x'));
                const y = parseFloat($(this).attr('y'));
                
                // Determinar el cuadrante basado en la posición y
                let cuadrante = null;
                if (y < 200) { // Superior
                    cuadrante = x < 450 ? 1 : 2;
                } else { // Inferior
                    cuadrante = x < 450 ? 4 : 3;
                }
                
                dientesVisibles.push({
                    num,
                    x,
                    cuadrante
                });
            });
            
            // Separar por cuadrantes y ordenar por posición X
            const cuadrante1 = dientesVisibles.filter(d => d.cuadrante === 1).sort((a, b) => a.x - b.x);
            const cuadrante2 = dientesVisibles.filter(d => d.cuadrante === 2).sort((a, b) => a.x - b.x);
            const cuadrante3 = dientesVisibles.filter(d => d.cuadrante === 3).sort((a, b) => a.x - b.x);
            const cuadrante4 = dientesVisibles.filter(d => d.cuadrante === 4).sort((a, b) => a.x - b.x);
            
            // Mostrar resultados
            mostrarResultadoCuadrante('verificacion-c1', cuadrante1, [18, 17, 16, 15, 14, 13, 12, 11]);
            mostrarResultadoCuadrante('verificacion-c2', cuadrante2, [21, 22, 23, 24, 25, 26, 27, 28]);
            mostrarResultadoCuadrante('verificacion-c3', cuadrante3, [31, 32, 33, 34, 35, 36, 37, 38]);
            mostrarResultadoCuadrante('verificacion-c4', cuadrante4, [48, 47, 46, 45, 44, 43, 42, 41]);
        });
        
        // Función para mostrar el resultado de la verificación
        function mostrarResultadoCuadrante(elementId, dientesActuales, dientesEsperados) {
            const numerosActuales = dientesActuales.map(d => d.num);
            const $elemento = $('#' + elementId);
            
            // Verificar si los dientes están en el orden esperado
            let ordenCorrecto = true;
            let mensaje = '';
            
            if (numerosActuales.length !== dientesEsperados.length) {
                ordenCorrecto = false;
                mensaje = `Error: Se esperaban ${dientesEsperados.length} dientes, pero se encontraron ${numerosActuales.length}.`;
            } else {
                for (let i = 0; i < numerosActuales.length; i++) {
                    if (numerosActuales[i] !== dientesEsperados[i]) {
                        ordenCorrecto = false;
                        break;
                    }
                }
            }
            
            if (ordenCorrecto) {
                $elemento.removeClass('alert-secondary alert-danger').addClass('alert-success');
                mensaje = '<strong>¡CORRECTO!</strong> Los dientes están en el orden esperado.';
            } else {
                $elemento.removeClass('alert-secondary alert-success').addClass('alert-danger');
                if (!mensaje) {
                    mensaje = '<strong>¡ERROR!</strong> Los dientes no están en el orden esperado.';
                }
            }
            
            // Mostrar el detalle
            let html = `
                ${mensaje}
                <div class="mt-2">
                    <strong>Orden esperado:</strong> ${dientesEsperados.join(', ')}
                </div>
                <div>
                    <strong>Orden actual:</strong> ${numerosActuales.join(', ')}
                </div>
            `;
            
            $elemento.html(html);
        }
    });
    </script>
</body>
</html>
