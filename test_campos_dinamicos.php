<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test Campos Dinámicos - Puerto 83</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Test de Campos Dinámicos - Puerto 83</h1>
        
        <div class="alert alert-info">
            <strong>Paso 1:</strong> Verificar que el endpoint responde
        </div>
        
        <button id="testEndpoint" class="btn btn-primary">Probar Endpoint</button>
        <div id="endpointResult" class="mt-3"></div>
        
        <div class="alert alert-info mt-4">
            <strong>Paso 2:</strong> Probar carga de campos dinámicos
        </div>
        
        <button id="testCampos" class="btn btn-success">Cargar Campos</button>
        
        <div class="mt-4">
            <h3>Formulario de Prueba</h3>
            <form>
                <!-- Contenedor para campos dinámicos -->
                <div id="campos_dinamicos" style="border: 2px dashed #007bff; padding: 20px; min-height: 100px;">
                    <p class="text-muted">Los campos dinámicos aparecerán aquí...</p>
                </div>
                
                <button type="button" class="btn btn-info mt-3" onclick="mostrarValores()">Ver Valores del Formulario</button>
            </form>
        </div>
        
        <div id="valoresFormulario" class="mt-4"></div>
        
        <div class="mt-4">
            <h3>Logs de Depuración</h3>
            <div id="logs" style="background: #f8f9fa; padding: 15px; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px;"></div>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script>
        function log(mensaje) {
            const timestamp = new Date().toLocaleTimeString();
            const logDiv = $('#logs');
            logDiv.append(`<div>[${timestamp}] ${mensaje}</div>`);
            logDiv.scrollTop(logDiv[0].scrollHeight);
            console.log(mensaje);
        }
        
        function mostrarValores() {
            const formData = new FormData($('form')[0]);
            let valores = '<h4>Valores actuales del formulario:</h4><ul>';
            
            for (let [key, value] of formData.entries()) {
                valores += `<li><strong>${key}:</strong> ${value}</li>`;
            }
            valores += '</ul>';
            
            $('#valoresFormulario').html(valores);
        }
        
        $(document).ready(function() {
            log('Página cargada - Puerto 83');
            log('jQuery versión: ' + $.fn.jquery);
            log('Contenedor #campos_dinamicos existe: ' + ($('#campos_dinamicos').length > 0));
            
            $('#testEndpoint').click(function() {
                log('Probando endpoint...');
                
                $.ajax({
                    url: 'get_campos_simple.php',
                    type: 'GET',
                    dataType: 'json',
                    timeout: 5000,
                    success: function(response) {
                        log('✓ Endpoint responde correctamente');
                        $('#endpointResult').html(`
                            <div class="alert alert-success">
                                <strong>Endpoint funcionando</strong>
                                <pre>${JSON.stringify(response, null, 2)}</pre>
                            </div>
                        `);
                    },
                    error: function(xhr, status, error) {
                        log('✗ Error en endpoint: ' + error);
                        $('#endpointResult').html(`
                            <div class="alert alert-danger">
                                <strong>Error en endpoint:</strong> ${error}<br>
                                <strong>Status:</strong> ${status}<br>
                                <strong>Respuesta:</strong> ${xhr.responseText}
                            </div>
                        `);
                    }
                });
            });
            
            $('#testCampos').click(function() {
                cargarCamposDinamicos();
            });
        });
        
        function cargarCamposDinamicos() {
            log('Iniciando carga de campos dinámicos...');
            
            $.ajax({
                url: 'get_campos_simple.php',
                type: 'GET',
                dataType: 'json',
                timeout: 5000,
                success: function(response) {
                    log('✓ Respuesta recibida del servidor');
                    
                    if (response.success && response.campos) {
                        log('✓ Campos encontrados: ' + Object.keys(response.campos).length);
                        mostrarCampos(response.campos);
                    } else {
                        log('⚠ No hay campos en la respuesta');
                        mostrarCamposDePrueba();
                    }
                },
                error: function(xhr, status, error) {
                    log('✗ Error AJAX: ' + error);
                    log('⚠ Mostrando campos de prueba como fallback');
                    mostrarCamposDePrueba();
                }
            });
        }
        
        function mostrarCampos(campos) {
            log('Renderizando ' + Object.keys(campos).length + ' campos...');
            
            const container = $('#campos_dinamicos');
            container.empty();
            container.append('<h5 class="text-primary">Campos de la Especialidad</h5>');
            
            Object.keys(campos).forEach(function(nombre) {
                const config = campos[nombre];
                log('Procesando campo: ' + nombre + ' (' + config.tipo + ')');
                
                const fieldHtml = crearCampo(nombre, config);
                container.append(fieldHtml);
            });
            
            log('✓ Campos renderizados correctamente');
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
            
            mostrarCampos(camposPrueba);
        }
    </script>
</body>
</html>

