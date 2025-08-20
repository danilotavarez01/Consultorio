<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test Final - Campos Dinámicos</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f1c2c7; }
        .warning { background-color: #fff3cd; border-color: #ffeaa7; }
        #logs { background: #f8f9fa; padding: 15px; height: 200px; overflow-y: auto; font-family: monospace; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Test Final - Sistema de Campos Dinámicos</h1>
        
        <div id="status" class="test-section">
            <h3>Estado del Sistema</h3>
            <p>Verificando configuración...</p>
        </div>
        
        <div class="test-section">
            <h3>Test de Endpoint</h3>
            <button id="testEndpoint" class="btn btn-primary">Probar get_campos_simple.php</button>
            <div id="endpointResult" class="mt-3"></div>
        </div>
        
        <div class="test-section">
            <h3>Test de Campos Dinámicos</h3>
            <div id="campos_dinamicos" style="border: 2px dashed #007bff; padding: 20px; min-height: 100px;">
                <p class="text-muted">Los campos dinámicos aparecerán aquí...</p>
            </div>
            <button id="cargarCampos" class="btn btn-success mt-3">Cargar Campos</button>
        </div>
        
        <div class="test-section">
            <h3>Logs del Sistema</h3>
            <div id="logs"></div>
            <button id="clearLogs" class="btn btn-warning btn-sm">Limpiar Logs</button>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script>
        function log(mensaje) {
            const timestamp = new Date().toLocaleTimeString();
            const logDiv = $('#logs');
            logDiv.append(`<div>[${timestamp}] ${mensaje}</div>`);
            logDiv.scrollTop(logDiv[0].scrollHeight);
            console.log(`[${timestamp}] ${mensaje}`);
        }
        
        function updateStatus(mensaje, tipo = 'info') {
            const colors = {
                success: 'success',
                error: 'error', 
                warning: 'warning',
                info: 'info'
            };
            
            $('#status').removeClass('success error warning').addClass(colors[tipo]);
            $('#status p').text(mensaje);
        }
        
        $(document).ready(function() {
            log('Sistema iniciado');
            updateStatus('Sistema cargado correctamente', 'success');
            
            // Test de endpoint
            $('#testEndpoint').click(function() {
                log('=== PROBANDO ENDPOINT ===');
                
                $.ajax({
                    url: 'get_campos_simple.php',
                    type: 'GET',
                    dataType: 'json',
                    timeout: 10000,
                    success: function(response) {
                        log('✓ Endpoint responde correctamente');
                        log('Respuesta: ' + JSON.stringify(response, null, 2));
                        
                        $('#endpointResult').html(`
                            <div class="alert alert-success">
                                <h5>✓ Endpoint funcionando</h5>
                                <p><strong>Campos encontrados:</strong> ${response.campos ? Object.keys(response.campos).length : 0}</p>
                                <p><strong>Fuente:</strong> ${response.debug?.source || 'no especificada'}</p>
                                <p><strong>Especialidad ID:</strong> ${response.debug?.especialidad_id || 'no especificada'}</p>
                                <details>
                                    <summary>Ver respuesta completa</summary>
                                    <pre>${JSON.stringify(response, null, 2)}</pre>
                                </details>
                            </div>
                        `);
                        
                        updateStatus('Endpoint funcionando correctamente', 'success');
                    },
                    error: function(xhr, status, error) {
                        log('✗ Error en endpoint: ' + error);
                        log('Status: ' + status);
                        log('Response: ' + xhr.responseText);
                        
                        $('#endpointResult').html(`
                            <div class="alert alert-danger">
                                <h5>✗ Error en endpoint</h5>
                                <p><strong>Error:</strong> ${error}</p>
                                <p><strong>Status:</strong> ${status}</p>
                                <details>
                                    <summary>Ver respuesta del servidor</summary>
                                    <pre>${xhr.responseText}</pre>
                                </details>
                            </div>
                        `);
                        
                        updateStatus('Error en el endpoint', 'error');
                    }
                });
            });
            
            // Cargar campos
            $('#cargarCampos').click(function() {
                log('=== CARGANDO CAMPOS DINÁMICOS ===');
                cargarCamposEspecialidad();
            });
            
            // Limpiar logs
            $('#clearLogs').click(function() {
                $('#logs').empty();
            });
            
            // Auto-test al cargar
            setTimeout(function() {
                log('Ejecutando auto-test...');
                $('#testEndpoint').click();
            }, 1000);
        });
        
        function cargarCamposEspecialidad() {
            log('Iniciando carga de campos...');
            
            if ($('#campos_dinamicos').length === 0) {
                log('✗ ERROR: Contenedor #campos_dinamicos no encontrado');
                updateStatus('Error: Contenedor no encontrado', 'error');
                return;
            }
            
            log('✓ Contenedor encontrado');
            
            $.ajax({
                url: 'get_campos_simple.php',
                type: 'GET',
                dataType: 'json',
                timeout: 10000,
                success: function(response) {
                    log('✓ Respuesta recibida del endpoint');
                    
                    if (response.success && response.campos) {
                        log('✓ Campos válidos encontrados: ' + Object.keys(response.campos).length);
                        mostrarCamposDinamicos(response.campos);
                        updateStatus(`${Object.keys(response.campos).length} campos cargados correctamente`, 'success');
                    } else {
                        log('⚠ No hay campos válidos, usando fallback');
                        mostrarCamposDePrueba();
                        updateStatus('Sin campos en BD, mostrando campos de prueba', 'warning');
                    }
                },
                error: function(xhr, status, error) {
                    log('✗ Error AJAX: ' + error);
                    log('Status: ' + status);
                    log('Response: ' + xhr.responseText);
                    
                    mostrarCamposDePrueba();
                    updateStatus('Error de conexión, mostrando campos de prueba', 'error');
                }
            });
        }
        
        function mostrarCamposDinamicos(campos) {
            log('Renderizando ' + Object.keys(campos).length + ' campos...');
            
            const container = $('#campos_dinamicos');
            container.empty();
            
            container.append('<div class="alert alert-info"><h5>Campos de la Especialidad</h5></div>');
            
            Object.keys(campos).forEach(function(nombre) {
                const config = campos[nombre];
                log(`- Creando campo: ${nombre} (${config.tipo})`);
                
                const fieldHtml = crearCampo(nombre, config);
                container.append(fieldHtml);
            });
            
            log('✓ Todos los campos renderizados');
        }
        
        function crearCampo(nombre, config) {
            const required = config.requerido ? 'required' : '';
            const asterisk = config.requerido ? ' *' : '';
            
            let inputHtml = '';
            
            switch(config.tipo) {
                case 'text':
                    inputHtml = `<input type="text" class="form-control" name="campo_${nombre}" ${required} placeholder="Ingrese ${config.label.toLowerCase()}">`;
                    break;
                case 'number':
                    inputHtml = `<input type="number" class="form-control" name="campo_${nombre}" ${required} placeholder="Ingrese ${config.label.toLowerCase()}">`;
                    break;
                case 'date':
                    inputHtml = `<input type="date" class="form-control" name="campo_${nombre}" ${required}>`;
                    break;
                case 'textarea':
                    inputHtml = `<textarea class="form-control" name="campo_${nombre}" rows="3" ${required} placeholder="Ingrese ${config.label.toLowerCase()}"></textarea>`;
                    break;
                case 'select':
                    inputHtml = `<select class="form-control" name="campo_${nombre}" ${required}>
                        <option value="">Seleccione...</option>`;
                    if (config.opciones && Array.isArray(config.opciones)) {
                        config.opciones.forEach(function(opcion) {
                            inputHtml += `<option value="${opcion}">${opcion}</option>`;
                        });
                    }
                    inputHtml += '</select>';
                    break;
                case 'checkbox':
                    return `
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="campo_${nombre}" name="campo_${nombre}">
                                <label class="custom-control-label" for="campo_${nombre}">${config.label}</label>
                            </div>
                        </div>`;
                default:
                    inputHtml = `<input type="text" class="form-control" name="campo_${nombre}" ${required}>`;
            }
            
            return `
                <div class="form-group">
                    <label class="font-weight-bold">${config.label}${asterisk}</label>
                    ${inputHtml}
                </div>`;
        }
        
        function mostrarCamposDePrueba() {
            log('Mostrando campos de prueba hardcodeados');
            
            const camposPrueba = {
                'temperatura': {
                    'label': 'Temperatura (°C)',
                    'tipo': 'number',
                    'requerido': true
                },
                'presion_arterial': {
                    'label': 'Presión Arterial',
                    'tipo': 'text',
                    'requerido': true
                },
                'observaciones_especialidad': {
                    'label': 'Observaciones de la Especialidad',
                    'tipo': 'textarea',
                    'requerido': false
                },
                'tipo_consulta': {
                    'label': 'Tipo de Consulta',
                    'tipo': 'select',
                    'requerido': true,
                    'opciones': ['Primera vez', 'Seguimiento', 'Control', 'Urgencia']
                },
                'requiere_seguimiento': {
                    'label': 'Requiere cita de seguimiento',
                    'tipo': 'checkbox',
                    'requerido': false
                }
            };
            
            mostrarCamposDinamicos(camposPrueba);
        }
    </script>
</body>
</html>

