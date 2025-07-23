jQuery.noConflict();
(function($) {
    $(document).ready(function() {
        console.log('Inicialización de consulta_dinamica.js');        // Verificar si estamos en la página de edición o creación
        const esEdicion = window.location.href.includes('editar_consulta.php');
        
        if (esEdicion) {
            console.log('Modo edición detectado');
            
            // En modo edición, cargar valores desde data attribute
            const camposContainer = $('#campos_dinamicos');
            if (camposContainer.length > 0) {
                try {
                    let camposActualesStr = camposContainer.attr('data-campos-actuales') || '{}';
                    
                    // Verificar si es un JSON válido
                    if (camposActualesStr) {
                        try {
                            const camposActuales = JSON.parse(camposActualesStr);
                            console.log('Valores actuales:', camposActuales);
                            
                            // Aquí se pueden usar los valores para pre-poblar campos cuando se carguen
                            window.camposActualesData = camposActuales; // Guardar para uso posterior
                        } catch (jsonError) {
                            console.error('Error al parsear JSON:', jsonError);
                            console.log('JSON problemático:', camposActualesStr);
                            window.camposActualesData = {};
                        }
                    } else {
                        window.camposActualesData = {};
                    }
                } catch (e) {
                    console.error('Error al procesar campos actuales:', e);
                    window.camposActualesData = {};
                }
            }
        } else {
            console.log('Modo creación detectado');
            window.camposActualesData = {};
        }

        // Cargar los campos dinámicos
        cargarCamposEspecialidad();
    });    // Función para cargar los campos según la especialidad configurada
    // VERSIÓN MEJORADA: 2025-06-20 - Superresiliencia con timeouts reducidos
    function cargarCamposEspecialidad() {
        console.log('Iniciando carga de campos de especialidad (versión superresiliencia)');
        
        // Mostrar indicador de carga más informativo
        $('#campos_dinamicos').html('<div class="text-center my-3"><i class="fas fa-spinner fa-spin"></i> Cargando campos específicos...<small class="d-block text-muted">Intentando endpoint principal...</small></div>');
        
        // Variables para controlar reintentos
        var intentos = 0;
        var maxIntentos = 2;
        
        // Intentar con diferentes endpoints en caso de fallos
        // Primero intentamos con el endpoint principal con timeout reducido
        $.ajax({
            url: 'get_campos_especialidad_nuevo.php',
            type: 'POST',
            data: { doctor_id: $('#doctor_id').val() || 1 },
            dataType: 'json',
            timeout: 3000, // Timeout reducido a 3 segundos para no bloquear la interfaz
            cache: false, // Evitar caché
            success: function(response) {
                console.log('Respuesta recibida de get_campos_especialidad_nuevo.php:', response);
                if (response && response.success) {
                    procesarCamposEspecialidad(response.campos);
                } else {
                    console.log('No se obtuvieron campos específicos o hubo un error en la respuesta');
                    $('#campos_dinamicos small').text('El endpoint principal falló, intentando alternativa...');
                    intentarEndpointAlternativo();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la petición AJAX a get_campos_especialidad_nuevo.php:', status, error);
                console.log('Respuesta completa:', xhr.responseText);
                // Si falla, intentar con el endpoint alternativo
                $('#campos_dinamicos small').text('Error en endpoint principal, intentando alternativa...');
                intentarEndpointAlternativo();
            }
        });
    }
      function intentarEndpointAlternativo() {
        console.log('Intentando con endpoint alternativo get_campos_simple.php');
        
        // Actualizar mensaje para el usuario
        $('#campos_dinamicos small').text('Intentando con endpoint alternativo...');
        
        $.ajax({
            url: 'get_campos_simple.php',
            type: 'GET',
            dataType: 'json',
            timeout: 3000, // Timeout reducido a 3 segundos
            cache: false, // Evitar caché
            success: function(response) {
                console.log('Respuesta recibida de get_campos_simple.php:', response);
                if (response && response.success) {
                    procesarCamposEspecialidad(response.campos);
                } else {
                    // Si falla el segundo intento, intentar con el endpoint de emergencia
                    $('#campos_dinamicos small').text('Fallaron endpoints principales, intentando solución de emergencia...');
                    setTimeout(function() {
                        intentarEndpointEmergencia(); 
                    }, 300); // Pequeña espera para no saturar el servidor
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la petición AJAX a get_campos_simple.php:', status, error);
                console.log('Respuesta completa:', xhr.responseText);
                $('#campos_dinamicos small').text('Fallaron endpoints principales, intentando solución de emergencia...');
                setTimeout(function() {
                    intentarEndpointEmergencia(); 
                }, 300); // Pequeña espera para no saturar el servidor
            }
        });
    }
      function intentarEndpointEmergencia() {
        console.log('Intentando con endpoint de emergencia get_campos_emergencia.php');
        
        $('#campos_dinamicos small').text('Utilizando sistema de emergencia...');
        
        // Usar una técnica diferente para cargar el endpoint de emergencia
        // que podría funcionar incluso con problemas de CORS o bloqueos de servidores
        
        // Primero, intentemos con fetch que a veces funciona cuando $.ajax falla
        try {
            fetch('get_campos_emergencia.php?nocache=' + new Date().getTime(), {
                method: 'GET',
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en respuesta de red: ' + response.status);
                }
                return response.json();
            })
            .then(response => {
                console.log('Respuesta recibida de get_campos_emergencia.php usando fetch:', response);
                if (response && response.success) {
                    procesarCamposEspecialidad(response.campos);
                } else {
                    console.log('Respuesta inválida del endpoint de emergencia, usando hardcoded');
                    mostrarCamposPredefinidos();
                }
            })
            .catch(error => {
                console.error('Error usando fetch:', error);
                // Si falla fetch, intentar con XMLHttpRequest directo
                intentarXHRDirecto();
            });
        } catch (e) {
            console.error('Error general con fetch:', e);
            intentarXHRDirecto();
        }
    }
    
    // Función para intentar con XMLHttpRequest directo cuando todo lo demás falla
    function intentarXHRDirecto() {
        console.log('Intentando con XMLHttpRequest directo');
        $('#campos_dinamicos small').text('Utilizando método de rescate final...');
        
        try {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_campos_emergencia.php?final=1&nocache=' + new Date().getTime(), true);
            xhr.setRequestHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
            xhr.timeout = 5000; // 5 segundos de timeout
            
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        console.log('Respuesta XHR directo:', response);
                        if (response && response.success) {
                            procesarCamposEspecialidad(response.campos);
                        } else {
                            mostrarCamposPredefinidos();
                        }
                    } catch (e) {
                        console.error('Error al parsear JSON:', e);
                        mostrarCamposPredefinidos();
                    }
                } else {
                    console.error('Error XHR estado:', xhr.status);
                    mostrarCamposPredefinidos();
                }
            };
            
            xhr.onerror = function() {
                console.error('Error de red en XHR');
                mostrarCamposPredefinidos();
            };
            
            xhr.ontimeout = function() {
                console.error('Timeout en XHR');
                mostrarCamposPredefinidos();
            };
            
            xhr.send();
        } catch (e) {
            console.error('Error general con XHR:', e);
            mostrarCamposPredefinidos();
        }
    }
    }
    
    function mostrarCamposPredefinidos() {
        console.log('Mostrando campos predefinidos como último recurso');
        $('#campos_dinamicos small').text('Utilizando campos incorporados de respaldo');
        
        // Mostrar un mensaje de advertencia visible
        const alertDiv = $('<div class="alert alert-warning mb-4" role="alert"></div>')
            .html(
                '<strong>Información:</strong> Se están utilizando campos básicos predefinidos debido a un problema de conexión. ' +
                'Todos los datos serán guardados correctamente. ' +
                '<button type="button" class="btn btn-sm btn-outline-dark mt-2" id="btn-diagnostico">Ver diagnóstico</button>'
            );
            
        // Al hacer clic en el botón de diagnóstico
        $('#campos_dinamicos').html(alertDiv).on('click', '#btn-diagnostico', function() {
            window.open('diagnostico_campos_dinamicos.php', '_blank');
        });
        
        // Si todo falla, mostrar campos predefinidos extendidos
        procesarCamposEspecialidad({
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
            'frecuencia_cardiaca': {
                'label': 'Frecuencia Cardíaca (lpm)',
                'tipo': 'number',
                'requerido': false
            },
            'peso': {
                'label': 'Peso (kg)',
                'tipo': 'number',
                'requerido': false
            },
            'altura': {
                'label': 'Altura (cm)',
                'tipo': 'number',
                'requerido': false
            },
            'diagnostico': {
                'label': 'Diagnóstico',
                'tipo': 'textarea',
                'requerido': true
            },
            'observaciones': {
                'label': 'Observaciones Adicionales',
                'tipo': 'textarea',
                'requerido': false
            }
        });
    }// Procesar los campos recibidos y crear el formulario dinámico
    function procesarCamposEspecialidad(campos) {
        const camposContainer = $('#campos_dinamicos');
        if (!camposContainer.length) {
            console.error('No se encontró el contenedor #campos_dinamicos');
            return;
        }

        // Limpiar el contenedor
        camposContainer.empty();
        
        // Si no hay campos, mostrar un mensaje informativo
        if (!campos || Object.keys(campos).length === 0) {
            console.log('No hay campos para mostrar');
            camposContainer.html('<div class="alert alert-info">No hay campos específicos configurados para esta especialidad.</div>');
            return;
        }
        
        // Titulo de la sección
        camposContainer.append('<h4 class="mt-4 mb-3">Información específica de la especialidad</h4>');
        
        // Crear una fila para los campos
        let currentRow = $('<div class="form-row"></div>');
        camposContainer.append(currentRow);
        let fieldCount = 0;
        
        // Obtener valores actuales (si existen)
        const valoresActuales = window.camposActualesData || {};
        
        // Procesar cada campo
        Object.entries(campos).forEach(([nombreCampo, configCampo]) => {
            // Crear un nuevo contenedor cada 3 campos
            if (fieldCount % 3 === 0 && fieldCount > 0) {
                currentRow = $('<div class="form-row mt-3"></div>');
                camposContainer.append(currentRow);
            }
            
            // Crear el contenedor del campo
            const colDiv = $('<div class="col-md-4 form-group"></div>');
            
            // Crear la etiqueta
            const label = $('<label></label>')
                .text(configCampo.label || nombreCampo);
                
            if (configCampo.requerido) {
                label.append(' <span class="text-danger">*</span>');
            }
            
            colDiv.append(label);
            
            // Crear el input según el tipo
            let inputElement;
            
            switch(configCampo.tipo) {                case 'textarea':
                    inputElement = $('<textarea></textarea>')
                        .addClass('form-control')
                        .attr('name', 'campos_adicionales[' + nombreCampo + ']')
                        .attr('rows', '3');
                        
                    // Establecer valor si existe
                    if (valoresActuales[nombreCampo]) {
                        inputElement.val(valoresActuales[nombreCampo]);
                    }
                    break;
                    
                case 'select':
                case 'seleccion':
                    inputElement = $('<select></select>')
                        .addClass('form-control')
                        .attr('name', 'campos_adicionales[' + nombreCampo + ']');
                    
                    // Opción vacía
                    inputElement.append($('<option value="">Seleccionar...</option>'));
                    
                    // Opciones del select
                    if (configCampo.opciones) {
                        let opciones = configCampo.opciones;
                        if (typeof opciones === 'string') {
                            opciones = opciones.split(',');
                        }
                        
                        opciones.forEach(opcion => {
                            const valorOpcion = opcion.trim();
                            const selected = valoresActuales[nombreCampo] === valorOpcion ? 'selected' : '';
                            inputElement.append(
                                $('<option></option>')
                                    .val(valorOpcion)
                                    .text(valorOpcion)
                                    .prop('selected', selected)
                            );
                        });
                    }
                    break;
                    
                case 'checkbox':
                    const checkboxId = 'check_' + nombreCampo;
                    const isChecked = valoresActuales[nombreCampo] === 'si';
                    
                    inputElement = $('<div class="custom-control custom-checkbox"></div>')
                        .append(
                            $('<input type="checkbox">')
                                .addClass('custom-control-input')
                                .attr('id', checkboxId)
                                .attr('name', 'campos_adicionales[' + nombreCampo + ']')
                                .val('si')
                                .prop('checked', isChecked)
                        )
                        .append(
                            $('<label></label>')
                                .addClass('custom-control-label')
                                .attr('for', checkboxId)
                                .text(configCampo.label || nombreCampo)
                        );
                    break;
                    
                case 'date':
                case 'fecha':
                    inputElement = $('<input type="date">')
                        .addClass('form-control')
                        .attr('name', 'campos_adicionales[' + nombreCampo + ']');
                        
                    // Establecer valor si existe
                    if (valoresActuales[nombreCampo]) {
                        inputElement.val(valoresActuales[nombreCampo]);
                    }
                    break;
                    
                case 'number':
                case 'numero':
                    inputElement = $('<input type="number">')
                        .addClass('form-control')
                        .attr('name', 'campos_adicionales[' + nombreCampo + ']')
                        .attr('step', '0.01');
                        
                    // Establecer valor si existe
                    if (valoresActuales[nombreCampo]) {
                        inputElement.val(valoresActuales[nombreCampo]);
                    }
                    break;
                    
                default: // text o cualquier otro
                    inputElement = $('<input type="text">')
                        .addClass('form-control')
                        .attr('name', 'campos_adicionales[' + nombreCampo + ']');
                        
                    // Establecer valor si existe
                    if (valoresActuales[nombreCampo]) {
                        inputElement.val(valoresActuales[nombreCampo]);
                    }
            }
            
            // Agregar el atributo required si es necesario
            if (configCampo.requerido && configCampo.tipo !== 'checkbox') {
                inputElement.attr('required', 'required');
            }
            
            // Si es checkbox, ya se agregó la etiqueta dentro del div
            if (configCampo.tipo !== 'checkbox') {
                colDiv.append(inputElement);
            } else {
                // Reemplazar la etiqueta con el elemento checkbox completo
                colDiv.empty().append(inputElement);
            }
            
            // Agregar el campo al contenedor
            currentRow.append(colDiv);
            fieldCount++;
        });
        
        console.log('Campos generados correctamente');
    }
    
})(jQuery);
