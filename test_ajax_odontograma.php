<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test AJAX Odontograma</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>🧪 Test AJAX - Carga de Odontograma</h1>
        
        <div class="row">
            <div class="col-md-6">
                <h3>1️⃣ Test directo del archivo</h3>
                <a href="odontograma_svg.php" target="_blank" class="btn btn-primary">Abrir odontograma_svg.php</a>
            </div>
            
            <div class="col-md-6">
                <h3>2️⃣ Test AJAX</h3>
                <button id="test-ajax" class="btn btn-success">Cargar vía AJAX</button>
            </div>
        </div>
        
        <hr>
        
        <h3>3️⃣ Resultado AJAX:</h3>
        <div id="ajax-status" class="alert alert-info">Presiona el botón "Cargar vía AJAX" para probar</div>
        
        <h3>4️⃣ Contenido cargado:</h3>
        <div id="odontograma-container" style="border: 2px solid #ccc; padding: 15px; min-height: 200px; background: #f9f9f9;">
            <!-- Aquí se cargará el odontograma -->
        </div>
        
        <h3>5️⃣ Test de marcado de dientes:</h3>
        <div class="form-group">
            <label>Dientes a marcar (separados por comas):</label>
            <input type="text" id="dientes-input" class="form-control" value="11,12,13,21,22,23" placeholder="ej: 11,12,21,22">
            <button id="marcar-dientes" class="btn btn-warning mt-2">Marcar Dientes</button>
        </div>
        
        <h3>6️⃣ Log de debug:</h3>
        <div id="debug-log" style="background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 12px; max-height: 300px; overflow-y: auto;">
            <!-- Log de debug -->
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            
            function log(message) {
                const timestamp = new Date().toLocaleTimeString();
                $('#debug-log').append('[' + timestamp + '] ' + message + '<br>');
                $('#debug-log').scrollTop($('#debug-log')[0].scrollHeight);
                console.log('[DEBUG]', message);
            }
            
            log('Página cargada. jQuery versión: ' + $.fn.jquery);
            
            // Test AJAX
            $('#test-ajax').click(function() {
                log('Iniciando test AJAX...');
                $('#ajax-status').removeClass().addClass('alert alert-warning').text('🔄 Cargando...');
                
                $.ajax({
                    url: 'odontograma_svg.php',
                    type: 'GET',
                    cache: false,
                    timeout: 10000,
                    beforeSend: function() {
                        log('Enviando petición a odontograma_svg.php...');
                    },
                    success: function(data, textStatus, xhr) {
                        log('✅ AJAX exitoso!');
                        log('Status: ' + textStatus);
                        log('Tamaño de respuesta: ' + data.length + ' caracteres');
                        log('Content-Type: ' + xhr.getResponseHeader('Content-Type'));
                        
                        if (data && data.length > 100) {
                            $('#ajax-status').removeClass().addClass('alert alert-success')
                                .html('✅ AJAX exitoso! (' + data.length + ' caracteres cargados)');
                            
                            // Insertar el contenido
                            $('#odontograma-container').html(data);
                            log('✅ Contenido insertado en el DOM');
                            
                            // Verificar elementos
                            const svg = document.getElementById('odontograma');
                            if (svg) {
                                log('✅ SVG encontrado: #odontograma');
                                const dientes = $('.tooth-shape');
                                log('✅ Elementos tooth-shape encontrados: ' + dientes.length);
                            } else {
                                log('❌ SVG #odontograma NO encontrado');
                            }
                            
                        } else {
                            $('#ajax-status').removeClass().addClass('alert alert-danger')
                                .text('❌ Respuesta vacía o muy pequeña');
                            log('❌ Respuesta demasiado pequeña: ' + data.length + ' caracteres');
                        }
                    },
                    error: function(xhr, status, error) {
                        log('❌ ERROR AJAX: ' + status + ' - ' + error);
                        log('Status Code: ' + xhr.status);
                        log('Response Text: ' + xhr.responseText.substring(0, 200));
                        
                        $('#ajax-status').removeClass().addClass('alert alert-danger')
                            .html('❌ Error AJAX: ' + status + ' - ' + error + ' (Código: ' + xhr.status + ')');
                    }
                });
            });
            
            // Test marcado de dientes
            $('#marcar-dientes').click(function() {
                const dientesText = $('#dientes-input').val();
                if (!dientesText) {
                    log('⚠️ No se especificaron dientes para marcar');
                    return;
                }
                
                const dientes = dientesText.split(',').map(d => parseInt(d.trim())).filter(d => !isNaN(d));
                log('Marcando dientes: ' + dientes.join(', '));
                
                // Verificar que el SVG esté cargado
                const svg = document.getElementById('odontograma');
                if (!svg) {
                    log('❌ ERROR: SVG no cargado. Primero carga el odontograma con AJAX');
                    alert('Primero carga el odontograma con el botón "Cargar vía AJAX"');
                    return;
                }
                
                // Resetear todos los dientes
                $('.tooth-shape').css({
                    'fill': '',
                    'stroke': '',
                    'stroke-width': '',
                    'opacity': '0.7'
                });
                
                // Marcar dientes seleccionados
                let marcados = 0;
                dientes.forEach(function(numDiente) {
                    const diente = $('.tooth-shape[data-num="' + numDiente + '"]');
                    if (diente.length > 0) {
                        diente.css({
                            'fill': '#28a745',
                            'stroke': '#155724',
                            'stroke-width': '3',
                            'opacity': '1'
                        });
                        marcados++;
                        log('✅ Diente ' + numDiente + ' marcado');
                    } else {
                        log('⚠️ Diente ' + numDiente + ' NO encontrado');
                    }
                });
                
                log('✅ Proceso completado: ' + marcados + '/' + dientes.length + ' dientes marcados');
            });
        });
    </script>
</body>
</html>

