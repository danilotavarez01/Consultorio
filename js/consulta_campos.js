// Archivo para manejo de campos dinámicos de consulta
console.log('Cargando consulta_campos.js');

$(document).ready(function() {
    console.log('DOM listo, iniciando carga de campos');
    cargarCamposEspecialidad();
});

function cargarCamposEspecialidad() {
    console.log('Ejecutando cargarCamposEspecialidad()');
    
    // Verificar que el contenedor existe
    if ($('#campos_dinamicos').length === 0) {
        console.error('No se encontró el contenedor #campos_dinamicos');
        return;
    }
    
    console.log('Contenedor encontrado, realizando petición AJAX');
      $.ajax({
        url: 'get_campos_especialidad_nuevo.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Respuesta recibida:', response);
            
            if (response.success && response.campos) {
                mostrarCampos(response.campos);
            } else {
                console.warn('No hay campos para mostrar o error en la respuesta');
                mostrarCamposPrueba(); // Mostrar campos de prueba
            }
        },
        error: function(xhr, status, error) {
            console.error('Error en petición AJAX:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            mostrarCamposPrueba(); // Mostrar campos de prueba en caso de error
        }
    });
}

function mostrarCampos(campos) {
    console.log('Mostrando campos:', campos);
    
    const container = $('#campos_dinamicos');
    container.empty();
    
    // Agregar título
    container.append('<h5 class="mt-3 mb-3">Campos Específicos de la Especialidad</h5>');
    
    Object.keys(campos).forEach(function(nombreCampo) {
        const config = campos[nombreCampo];
        console.log('Procesando campo:', nombreCampo, config);
        
        const formGroup = $('<div class="form-group"></div>');
        const label = $('<label></label>').text(config.label + (config.requerido ? ' *' : ''));
        
        formGroup.append(label);
        
        let input;
        
        switch(config.tipo) {
            case 'text':
                input = $('<input type="text" class="form-control">')
                    .attr('name', 'campo_' + nombreCampo)
                    .prop('required', config.requerido);
                break;
                
            case 'number':
                input = $('<input type="number" class="form-control">')
                    .attr('name', 'campo_' + nombreCampo)
                    .prop('required', config.requerido);
                break;
                
            case 'date':
                input = $('<input type="date" class="form-control">')
                    .attr('name', 'campo_' + nombreCampo)
                    .prop('required', config.requerido);
                break;
                
            case 'textarea':
                input = $('<textarea class="form-control" rows="3"></textarea>')
                    .attr('name', 'campo_' + nombreCampo)
                    .prop('required', config.requerido);
                break;
                
            case 'select':
                input = $('<select class="form-control"></select>')
                    .attr('name', 'campo_' + nombreCampo)
                    .prop('required', config.requerido);
                
                if (config.opciones && Array.isArray(config.opciones)) {
                    input.append('<option value="">Seleccione...</option>');
                    config.opciones.forEach(function(opcion) {
                        input.append('<option value="' + opcion + '">' + opcion + '</option>');
                    });
                }
                break;
                
            case 'checkbox':
                input = $('<div class="custom-control custom-checkbox"></div>');
                const checkbox = $('<input type="checkbox" class="custom-control-input">')
                    .attr('name', 'campo_' + nombreCampo)
                    .attr('id', 'campo_' + nombreCampo);
                const checkboxLabel = $('<label class="custom-control-label"></label>')
                    .attr('for', 'campo_' + nombreCampo)
                    .text(config.label);
                
                input.append(checkbox).append(checkboxLabel);
                formGroup.empty().append(input); // Para checkbox, reemplazamos todo el form-group
                break;
                
            default:
                input = $('<input type="text" class="form-control">')
                    .attr('name', 'campo_' + nombreCampo)
                    .prop('required', config.requerido);
        }
        
        if (config.tipo !== 'checkbox') {
            formGroup.append(input);
        }
        
        container.append(formGroup);
    });
    
    console.log('Campos agregados al DOM');
}

function mostrarCamposPrueba() {
    console.log('Mostrando campos de prueba');
    
    const camposPrueba = {
        'temperatura': {
            'label': 'Temperatura (°C)',
            'tipo': 'number',
            'requerido': true
        },
        'tension_arterial': {
            'label': 'Tensión Arterial',
            'tipo': 'text',
            'requerido': true
        },
        'notas_adicionales': {
            'label': 'Notas Adicionales',
            'tipo': 'textarea',
            'requerido': false
        }
    };
    
    mostrarCampos(camposPrueba);
}