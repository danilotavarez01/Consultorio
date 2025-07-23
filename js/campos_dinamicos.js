// Campos dinámicos para consultas médicas
console.log('Iniciando campos_dinamicos.js');

$(document).ready(function() {
    console.log('DOM listo - cargando campos de especialidad');
    
    // Verificar que existe el contenedor
    if ($('#campos_dinamicos').length === 0) {
        console.error('Contenedor #campos_dinamicos no encontrado');
        return;
    }
    
    // Cargar campos inmediatamente
    cargarCamposEspecialidad();
});

function cargarCamposEspecialidad() {
    console.log('Cargando campos de especialidad...');
    
    // Mostrar indicador de carga
    const container = $('#campos_dinamicos');
    container.html('<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Cargando campos específicos...</div>');    $.ajax({
        url: 'get_campos_mysql_fixed.php',
        type: 'GET',
        dataType: 'json',
        timeout: 10000,
        success: function(response) {
            console.log('Respuesta exitosa:', response);
            
            if (response && response.success) {
                if (response.campos && Object.keys(response.campos).length > 0) {
                    mostrarCamposDinamicos(response.campos);
                } else {
                    container.html('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> No hay campos específicos configurados para esta especialidad.</div>');
                }
            } else {
                console.error('Respuesta indica error:', response);
                mostrarError('El servidor devolvió un error: ' + (response.message || 'Error desconocido'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', {
                status: status,
                error: error,
                response: xhr.responseText,
                statusCode: xhr.status
            });
            
            let errorMessage = 'Error al cargar los campos específicos.';
            
            if (xhr.status === 404) {
                errorMessage = 'El archivo get_campos_simple.php no se encontró.';
            } else if (xhr.status === 500) {
                errorMessage = 'Error interno del servidor. Revisa la configuración.';
            } else if (status === 'timeout') {
                errorMessage = 'La solicitud ha expirado. Intenta nuevamente.';
            }
            
            mostrarError(errorMessage);
        }
    });
}

function mostrarError(mensaje) {
    const container = $('#campos_dinamicos');
    container.html(`
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> 
            <strong>Error:</strong> ${mensaje}
            <br><small>Revisa la consola del navegador para más detalles.</small>
        </div>
    `);
}

function mostrarCamposDinamicos(campos) {
    console.log('Mostrando campos:', campos);
    
    const container = $('#campos_dinamicos');
    container.empty();
    
    if (!campos || Object.keys(campos).length === 0) {
        console.log('No hay campos para mostrar');
        return;
    }
    
    // Título
    container.append('<div class="alert alert-info"><strong>Campos Específicos de la Especialidad</strong></div>');
    
    // Crear cada campo
    Object.keys(campos).forEach(function(nombre) {
        const config = campos[nombre];
        const fieldHtml = crearCampo(nombre, config);
        container.append(fieldHtml);
    });
    
    console.log('Campos agregados al DOM exitosamente');
}

function crearCampo(nombre, config) {
    const required = config.requerido ? 'required' : '';
    const asterisk = config.requerido ? ' *' : '';
    
    let inputHtml = '';
    
    switch(config.tipo) {
        case 'text':
            inputHtml = `<input type="text" class="form-control" name="campo_${nombre}" ${required}>`;
            break;
        case 'number':
            inputHtml = `<input type="number" class="form-control" name="campo_${nombre}" ${required}>`;
            break;
        case 'date':
            inputHtml = `<input type="date" class="form-control" name="campo_${nombre}" ${required}>`;
            break;
        case 'textarea':
            inputHtml = `<textarea class="form-control" name="campo_${nombre}" rows="3" ${required}></textarea>`;
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
            inputHtml = `
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="campo_${nombre}" name="campo_${nombre}">
                    <label class="custom-control-label" for="campo_${nombre}">${config.label}</label>
                </div>`;
            break;
        default:
            inputHtml = `<input type="text" class="form-control" name="campo_${nombre}" ${required}>`;
    }
    
    if (config.tipo === 'checkbox') {
        return `<div class="form-group">${inputHtml}</div>`;
    } else {
        return `
            <div class="form-group">
                <label>${config.label}${asterisk}</label>
                ${inputHtml}
            </div>`;
    }
}

function mostrarCamposDePrueba() {
    console.log('Mostrando campos de prueba');
    
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
        'requiere_cita_seguimiento': {
            'label': 'Requiere cita de seguimiento',
            'tipo': 'checkbox',
            'requerido': false
        }
    };
    
    mostrarCamposDinamicos(camposPrueba);
}
