jQuery.noConflict();
(function($) {
    // Función para cargar los campos según la especialidad configurada    function cargarCamposEspecialidad() {
        console.log('Iniciando carga de campos de especialidad');
        $.ajax({
            url: 'get_campos_especialidad.php',
            type: 'POST',
            data: { doctor_id: 1 }, // Enviamos un valor dummy para cumplir con la validación
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta recibida:', response);
                if (response.success) {
                    const camposContainer = $('#campos_dinamicos');
                    camposContainer.empty();
                    console.log('Campos a mostrar:', response.campos);
                    
                    // Iterar sobre los campos y crear los elementos del formulario
                    Object.entries(response.campos).forEach(([nombre, config]) => {
                        const formGroup = $('<div>').addClass('form-group');
                        const label = $('<label>').text(config.label);
                        
                        if (config.requerido) {
                            label.append(' *');
                        }

                        formGroup.append(label);

                        let input;
                        switch(config.tipo) {
                            case 'text':
                            case 'number':
                            case 'date':
                                input = $('<input>')
                                    .attr('type', config.tipo)
                                    .addClass('form-control')
                                    .attr('name', 'campo_' + nombre)
                                    .prop('required', config.requerido);
                                break;

                            case 'textarea':
                                input = $('<textarea>')
                                    .addClass('form-control')
                                    .attr('name', 'campo_' + nombre)
                                    .prop('required', config.requerido)
                                    .attr('rows', '3');
                                break;

                            case 'select':
                                input = $('<select>')
                                    .addClass('form-control')
                                    .attr('name', 'campo_' + nombre)
                                    .prop('required', config.requerido);
                                
                                if (config.opciones) {
                                    config.opciones.forEach(opcion => {
                                        input.append($('<option>').val(opcion).text(opcion));
                                    });
                                }
                                break;

                            case 'checkbox':
                                input = $('<div>').addClass('custom-control custom-checkbox')
                                    .append(
                                        $('<input>')
                                            .attr('type', 'checkbox')
                                            .addClass('custom-control-input')
                                            .attr('name', 'campo_' + nombre)
                                            .attr('id', nombre)
                                            .prop('required', config.requerido),
                                        $('<label>')
                                            .addClass('custom-control-label')
                                            .attr('for', nombre)
                                            .text(config.label)
                                    );
                                break;
                        }

                        if (config.tipo !== 'checkbox') {
                            formGroup.append(input);
                        } else {
                            formGroup.html(input);
                        }

                        camposContainer.append(formGroup);
                    });
                } else {
                    console.error('Error al cargar los campos:', response.message || 'Error desconocido');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error de AJAX:', textStatus, errorThrown);
                console.log('Respuesta completa:', jqXHR.responseText);
            }
        });
    }

    // Cargar los campos al iniciar la página
    $(document).ready(function() {
        cargarCamposEspecialidad();
    });
})(jQuery);
