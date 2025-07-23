<?php
// Página para probar la numeración del odontograma
// Creado: <?php echo date('Y-m-d H:i:s'); ?>
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Numeración del Odontograma</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        .cuadrante {
            border: 1px solid #ccc;
            padding: 10px;
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2>Test de Numeración del Odontograma</h2>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h4>Instrucciones:</h4>
                    <p>Esta página verifica que la numeración de los dientes sea correcta tanto visualmente como en la selección.</p>
                    <p>La numeración debe ser:</p>
                    <div class="row">
                        <div class="col-md-6 text-center">
                            <div class="cuadrante">
                                <h5>Primer Cuadrante (Superior Derecho)</h5>
                                <p>18 17 16 15 14 13 12 11</p>
                            </div>
                            <div class="cuadrante">
                                <h5>Cuarto Cuadrante (Inferior Derecho)</h5>
                                <p>48 47 46 45 44 43 42 41</p>
                            </div>
                        </div>
                        <div class="col-md-6 text-center">
                            <div class="cuadrante">
                                <h5>Segundo Cuadrante (Superior Izquierdo)</h5>
                                <p>21 22 23 24 25 26 27 28</p>
                            </div>
                            <div class="cuadrante">
                                <h5>Tercer Cuadrante (Inferior Izquierdo)</h5>
                                <p>31 32 33 34 35 36 37 38</p>
                            </div>
                        </div>
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
                
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5>Test de Selección de Dientes</h5>
                    </div>
                    <div class="card-body">
                        <p>Seleccione dientes en cada cuadrante y verifique que se muestran correctamente en la lista de seleccionados:</p>
                        <div class="test-buttons mb-3">
                            <button class="btn btn-sm btn-outline-primary mr-2" onclick="seleccionarDiente(18)">18</button>
                            <button class="btn btn-sm btn-outline-primary mr-2" onclick="seleccionarDiente(11)">11</button>
                            <button class="btn btn-sm btn-outline-primary mr-2" onclick="seleccionarDiente(21)">21</button>
                            <button class="btn btn-sm btn-outline-primary mr-2" onclick="seleccionarDiente(28)">28</button>
                            <button class="btn btn-sm btn-outline-primary mr-2" onclick="seleccionarDiente(48)">48</button>
                            <button class="btn btn-sm btn-outline-primary mr-2" onclick="seleccionarDiente(41)">41</button>
                            <button class="btn btn-sm btn-outline-primary mr-2" onclick="seleccionarDiente(31)">31</button>
                            <button class="btn btn-sm btn-outline-primary mr-2" onclick="seleccionarDiente(38)">38</button>
                        </div>
                        
                        <div id="seleccion-status" class="alert alert-secondary">
                            Haga clic en los botones para probar la selección.
                        </div>
                    </div>
                </div>
                
                <div class="mt-3 text-center">
                    <button id="verificar-numeracion" class="btn btn-primary">Verificar Numeración de Dientes</button>
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
                // Verificar que la numeración coincida con la visual
                setTimeout(verificarNumeracion, 1000);
            }
        });
        
        $('#verificar-numeracion').click(function() {
            verificarNumeracion();
        });
    });
    
    // Función para seleccionar un diente específico
    function seleccionarDiente(numero) {
        // Obtener todos los dientes
        const dientes = document.querySelectorAll('.tooth-shape');
        
        // Buscar el diente con el número correcto en el atributo data-num
        let dienteEncontrado = false;
        
        dientes.forEach(function(diente) {
            const numDiente = parseInt(diente.getAttribute('data-num'));
            if (numDiente === numero) {
                dienteEncontrado = true;
                // Simular un clic en el diente
                diente.dispatchEvent(new MouseEvent('click', {
                    bubbles: true,
                    cancelable: true,
                    view: window
                }));
                
                $('#seleccion-status').removeClass('alert-secondary alert-danger').addClass('alert-success');
                $('#seleccion-status').html(`<strong>Éxito:</strong> Se ha seleccionado el diente ${numero}.`);
            }
        });
        
        if (!dienteEncontrado) {
            $('#seleccion-status').removeClass('alert-secondary alert-success').addClass('alert-danger');
            $('#seleccion-status').html(`<strong>Error:</strong> No se ha encontrado el diente ${numero}.`);
        }
    }
    
    // Verificar la numeración
    function verificarNumeracion() {
        // Obtener todos los números de dientes
        const labels = document.querySelectorAll('.tooth-label');
        
        // Agrupar por cuadrantes
        const cuadrante1 = [];
        const cuadrante2 = [];
        const cuadrante3 = [];
        const cuadrante4 = [];
        
        labels.forEach(function(label) {
            const num = parseInt(label.textContent);
            const x = parseFloat(label.getAttribute('x'));
            const y = parseFloat(label.getAttribute('y'));
            
            // Asignar al cuadrante correspondiente según su posición
            if (y < 200) { // Superior
                if (x < 450) { // Derecho
                    cuadrante1.push({ num, x });
                } else { // Izquierdo
                    cuadrante2.push({ num, x });
                }
            } else { // Inferior
                if (x < 450) { // Derecho
                    cuadrante4.push({ num, x });
                } else { // Izquierdo
                    cuadrante3.push({ num, x });
                }
            }
        });
        
        // Ordenar por posición X
        cuadrante1.sort((a, b) => a.x - b.x);
        cuadrante2.sort((a, b) => a.x - b.x);
        cuadrante3.sort((a, b) => a.x - b.x);
        cuadrante4.sort((a, b) => a.x - b.x);
        
        // Verificar y mostrar resultados
        mostrarResultado('Primer Cuadrante (Superior Derecho)', 
                        cuadrante1.map(item => item.num), 
                        [18, 17, 16, 15, 14, 13, 12, 11]);
                        
        mostrarResultado('Segundo Cuadrante (Superior Izquierdo)', 
                        cuadrante2.map(item => item.num), 
                        [21, 22, 23, 24, 25, 26, 27, 28]);
                        
        mostrarResultado('Tercer Cuadrante (Inferior Izquierdo)', 
                        cuadrante3.map(item => item.num), 
                        [31, 32, 33, 34, 35, 36, 37, 38]);
                        
        mostrarResultado('Cuarto Cuadrante (Inferior Derecho)', 
                        cuadrante4.map(item => item.num), 
                        [48, 47, 46, 45, 44, 43, 42, 41]);
    }
    
    function mostrarResultado(nombreCuadrante, actual, esperado) {
        // Crear un div para mostrar el resultado
        let esCorrecta = true;
        
        if (actual.length !== esperado.length) {
            esCorrecta = false;
        } else {
            for (let i = 0; i < actual.length; i++) {
                if (actual[i] !== esperado[i]) {
                    esCorrecta = false;
                    break;
                }
            }
        }
        
        // Construir el mensaje
        const divId = `resultado-${nombreCuadrante.toLowerCase().replace(/[\s()]/g, '-')}`;
        
        // Eliminar resultados anteriores
        if (document.getElementById(divId)) {
            document.getElementById(divId).remove();
        }
        
        const divResultado = document.createElement('div');
        divResultado.id = divId;
        divResultado.className = `alert ${esCorrecta ? 'alert-success' : 'alert-danger'} mt-2`;
        
        let contenido = `<h5>${nombreCuadrante}</h5>`;
        contenido += `<p><strong>${esCorrecta ? '✓ CORRECTO' : '✗ ERROR'}</strong></p>`;
        contenido += `<p>Esperado: ${esperado.join(', ')}</p>`;
        contenido += `<p>Actual: ${actual.join(', ')}</p>`;
        
        divResultado.innerHTML = contenido;
        
        // Insertar antes del botón verificar
        $('#verificar-numeracion').before(divResultado);
    }
    </script>
</body>
</html>
