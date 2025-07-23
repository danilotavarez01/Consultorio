/**
 * Script simplificado para cargar campos dinámicos según la especialidad
 * Este script evita problemas de XML mal formado
 */

$(document).ready(function() {
    console.log('Script cargando campos dinámicos iniciado');
    
    // Obtener el contenedor donde se mostrarán los campos dinámicos
    const $camposDinamicos = $('#campos_dinamicos');
    
    if ($camposDinamicos.length === 0) {
        console.error('No se encontró el contenedor de campos dinámicos');
        return;
    }
    
    // Función para cargar campos dinámicos
    function cargarCamposDinamicos(especialidadId) {
        console.log('Cargando campos para especialidad ID: ' + especialidadId);
        
        // Mostrar indicador de carga
        $camposDinamicos.html('<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando campos...</p>');
        
        // Realizar petición AJAX al nuevo endpoint
        $.ajax({
            url: 'get_campos_simple_nuevo.php', // Usar el nuevo script simplificado
            type: 'GET',
            dataType: 'json',
            cache: false, // Importante para evitar cacheo
            success: function(data) {
                console.log('Datos recibidos:', data);
                
                if (data && data.success && data.campos) {
                    mostrarCampos(data.campos);
                } else {
                    console.error('La respuesta no tiene el formato esperado:', data);
                    $camposDinamicos.html('<div class="alert alert-warning">No se pudieron cargar los campos adicionales.</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar campos:', status, error);
                console.error('Respuesta:', xhr.responseText);
                
                $camposDinamicos.html(`
                    <div class="alert alert-danger">
                        <strong>Error al cargar campos adicionales</strong><br>
                        ${error}<br>
                        <small>Inténtelo nuevamente o contacte al administrador.</small>
                    </div>
                `);
            }
        });
    }
    
    // Función para mostrar los campos en el formulario
    function mostrarCampos(campos) {
        // Si no hay campos, mostrar mensaje
        if (Object.keys(campos).length === 0) {
            $camposDinamicos.html('<p class="text-muted">No hay campos adicionales para esta especialidad.</p>');
            return;
        }
        
        // Crear HTML para los campos
        let html = '<div class="card mb-4">';
        html += '<div class="card-header bg-info text-white">Campos específicos de la especialidad</div>';
        html += '<div class="card-body">';
        
        // Empezar una fila
        html += '<div class="form-row">';
        
        // Contador para dividir en filas de 3 elementos
        let contador = 0;
        
        // Iterar por cada campo
        for (const [nombreCampo, campo] of Object.entries(campos)) {
            // Crear un div para el campo (4 columnas en dispositivos medianos)
            html += '<div class="form-group col-md-4">';
            html += `<label>${campo.label}${campo.requerido ? ' <span class="text-danger">*</span>' : ''}</label>`;
            
            // Crear el input según el tipo
            if (campo.tipo === 'textarea') {
                html += `<textarea name="${nombreCampo}" class="form-control" rows="3"${campo.requerido ? ' required' : ''}></textarea>`;
            } else if (campo.tipo === 'select' && Array.isArray(campo.opciones) && campo.opciones.length > 0) {
                html += `<select name="${nombreCampo}" class="form-control"${campo.requerido ? ' required' : ''}>`;
                html += '<option value="">Seleccione...</option>';
                
                campo.opciones.forEach(opcion => {
                    html += `<option value="${opcion}">${opcion}</option>`;
                });
                
                html += '</select>';
            } else if (campo.tipo === 'checkbox') {
                html += '<div class="form-check">';
                html += `<input type="checkbox" name="${nombreCampo}" id="${nombreCampo}" class="form-check-input"${campo.requerido ? ' required' : ''}>`;
                html += `<label class="form-check-label" for="${nombreCampo}">${campo.label}</label>`;
                html += '</div>';
            } else {
                // Tipos estándar: text, number, date, etc.
                html += `<input type="${campo.tipo}" name="${nombreCampo}" class="form-control"${campo.requerido ? ' required' : ''}>`;
            }
            
            html += '</div>'; // Cierre del form-group
            
            // Incrementar contador
            contador++;
            
            // Si completamos 3 columnas, cerrar la fila y empezar una nueva
            if (contador % 3 === 0) {
                html += '</div><div class="form-row">';
            }
        }
        
        // Cerrar la última fila
        html += '</div>';
        
        // Cerrar el card
        html += '</div></div>';
        
        // Mostrar los campos
        $camposDinamicos.html(html);
    }
    
    // Detectar cambios en la selección de médico (si existe)
    $('#doctor_id').on('change', function() {
        const $doctorSelect = $(this);
        const especialidadId = $doctorSelect.find('option:selected').data('especialidad');
        
        // Si hay especialidad, cargar campos
        if (especialidadId) {
            cargarCamposDinamicos(especialidadId);
        } else {
            $camposDinamicos.html('<p class="text-muted">Seleccione un médico para cargar campos adicionales.</p>');
        }
    });
    
    // Inicializar - cargar campos para la selección actual o el médico actual
    const $doctorSelect = $('#doctor_id');
    
    // Si hay selección de médico y está visible, usar su especialidad
    if ($doctorSelect.is(':visible') && $doctorSelect.val()) {
        const especialidadId = $doctorSelect.find('option:selected').data('especialidad');
        if (especialidadId) {
            cargarCamposDinamicos(especialidadId);
        }
    } else {
        // Si el médico ya está definido (oculto), cargar campos directamente
        cargarCamposDinamicos(null); // null = usar el configurado por defecto
    }
});
