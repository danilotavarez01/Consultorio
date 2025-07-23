<?php
// Archivo para forzar la carga del odontograma en la nueva consulta si la especialidad es odontología
// Se debe incluir este archivo en nueva_consulta.php

// Obtener la especialidad configurada en el sistema
require_once "config.php";
$mostrarOdontograma = false;
try {
    $stmt = $conn->prepare("SELECT e.nombre FROM configuracion c 
                           JOIN especialidades e ON c.especialidad_id = e.id 
                           WHERE c.id = 1");
    $stmt->execute();
    $especialidad = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Determinar si se debe mostrar el odontograma (si es odontología)
    if ($especialidad) {
        $nombreEspecialidad = strtolower(trim($especialidad['nombre']));
        $especialidadesOdontologicas = ['odontologia', 'odontología', 'dental', 
                                      'odontologica', 'odontológica', 'dentista', 
                                      'odontopediatria', 'odontopediatría'];
        
        // Verificar si el nombre de la especialidad está en la lista o contiene "odonto" o "dental"
        if (in_array($nombreEspecialidad, $especialidadesOdontologicas) || 
            strpos($nombreEspecialidad, 'odonto') !== false ||
            strpos($nombreEspecialidad, 'dental') !== false) {
            $mostrarOdontograma = true;
        }
    }
} catch (Exception $e) {
    // Si hay error, no mostrar el odontograma
    $mostrarOdontograma = false;
}
?>
<script>
// Establecer variables globales para controlar el estado del odontograma
window.MOSTRAR_ODONTOGRAMA = <?php echo $mostrarOdontograma ? 'true' : 'false'; ?>;
window.odontogramaSVGCargado = window.odontogramaSVGCargado || false;
window.odontogramaSVGCargando = false;
window.odontogramaSVGInicializado = window.odontogramaSVGInicializado || false;

// Control para evitar loops infinitos
window._odontogramaInitCount = window._odontogramaInitCount || 0;

// Para depuración: Forzar odontograma en desarrollo
// window.MOSTRAR_ODONTOGRAMA = true; // Descomentar esta línea para forzar el odontograma

// Mostrar mensaje de depuración al inicio
console.log("INICIO - Estado de MOSTRAR_ODONTOGRAMA:", window.MOSTRAR_ODONTOGRAMA);

<?php if ($mostrarOdontograma): ?>
window.ESPECIALIDAD_NOMBRE = '<?php echo htmlspecialchars($especialidad['nombre']); ?>';
<?php endif; ?>

// Añadir botón de diagnóstico
// $(document).ready(function() {
//     $('body').append('<div id="diagnostico-btn" style="position:fixed; top:10px; right:10px; z-index:9999;"><button class="btn btn-warning btn-sm">Diagnosticar Odontograma</button></div>');
    
//     $('#diagnostico-btn').click(function() {
//         alert("Estado del odontograma:\n" + 
//               "MOSTRAR_ODONTOGRAMA: " + window.MOSTRAR_ODONTOGRAMA + "\n" + 
//               "odontogramaSVGCargado: " + window.odontogramaSVGCargado + "\n" +
//               "odontogramaSVGInicializado: " + window.odontogramaSVGInicializado + "\n" +
//               "Contador de inicializaciones: " + window._odontogramaInitCount + "\n" +
//               "Existe #campos_dinamicos: " + ($('#campos_dinamicos').length > 0) + "\n" +
//               "Existe #odontograma-dinamico: " + ($('#odontograma-dinamico').length > 0) + "\n" +
//               "Función insertarOdontograma: " + (typeof window.insertarOdontograma === 'function'));
        
//         // Si la especialidad es odontología pero no hay odontograma, intentar insertar
//         if (window.MOSTRAR_ODONTOGRAMA && $('#odontograma-dinamico').length === 0) {
//             if (confirm("El odontograma debería mostrarse pero no está presente. ¿Intentar insertarlo?")) {
//                 // Resetear variables de control para forzar una carga fresca
//                 window.odontogramaSVGCargado = false;
//                 window.odontogramaSVGCargando = false;
//                 window.odontogramaSVGInicializado = false;
//                 window._odontogramaInitCount = 0;
//                 window.insertarOdontograma();
//             }
//         }
//     });
// });

// Función para insertar el odontograma
function insertarOdontograma() {
    // Evitar bucles infinitos contando las inicializaciones
    window._odontogramaInitCount = window._odontogramaInitCount || 0;
    window._odontogramaInitCount++;
    
    // Si ya se han intentado demasiadas inserciones, bloqueamos para prevenir bucles
    if (window._odontogramaInitCount > 3) {
        console.error('DEBUG: Demasiados intentos de cargar el odontograma. Deteniendo para evitar bucle infinito.');
        // Mostrar mensaje de error
        if ($('#campos_dinamicos').length > 0 && $('#odontograma-error-loop').length === 0) {
            $('#campos_dinamicos').prepend(
                '<div id="odontograma-error-loop" class="alert alert-danger">' +
                '<strong>Error:</strong> Demasiados intentos de cargar el odontograma. ' +
                '<button class="btn btn-sm btn-danger ml-2" onclick="window.location.reload();">Recargar página</button>' +
                '</div>'
            );
        }
        return;
    }

    // Evitar cargas simultáneas que podrían causar problemas
    if (window.odontogramaSVGCargando) {
        console.log('DEBUG: Ya hay una carga del odontograma en curso, evitando duplicación');
        return;
    }

    // Si ya tenemos un odontograma cargado y funcionando, no volver a cargar
    if (window.odontogramaSVGCargado && $('#odontograma-dinamico').length > 0) {
        console.log('DEBUG: El odontograma ya está cargado y visible, no hace falta recargarlo');
        return;
    }

    console.log('DEBUG: Intentando insertar odontograma avanzado SVG (intento #' + window._odontogramaInitCount + ')');
    window.odontogramaSVGCargando = true;
    
    // Primero verificar si ya existe un odontograma y eliminarlo para evitar duplicados
    if ($('#odontograma-dinamico').length > 0) {
        console.log('DEBUG: Ya existe un odontograma, eliminándolo primero para evitar duplicados');
        $('#odontograma-dinamico').remove();
    }
    
    // Ahora insertar el nuevo odontograma
    console.log('DEBUG: Insertando nuevo odontograma');
    
    // Verificar que el contenedor existe
    if ($('#campos_dinamicos').length === 0) {
        console.error('DEBUG: ERROR - No se encontró el contenedor #campos_dinamicos');
        alert('ERROR: No se encontró el contenedor para el odontograma');
        window.odontogramaSVGCargando = false;
        return;
    }
    
    // Eliminar cualquier mensaje de carga anterior si existiera
    $('#loading-odontograma').remove();
    
    // Para depuración, insertamos un mensaje directo que será eliminado al cargar
    $('#campos_dinamicos').prepend('<div class="alert alert-info" id="loading-odontograma">Cargando odontograma avanzado SVG...</div>');
    
    // Variable para evitar múltiples inicializaciones
    let odontogramaSVGIniciado = false;
    
    // Cargar el contenido del odontograma desde el archivo PHP con manejo mejorado de errores
    $.ajax({
        url: 'odontograma_svg.php',
        type: 'GET',
        timeout: 10000, // 10 segundos de tiempo límite
        cache: false, // Evitar carga desde caché
        success: function(data) {
            console.log('DEBUG: Éxito al cargar odontograma_svg.php, insertando contenido...');
            
            // Eliminar el mensaje de carga
            $('#loading-odontograma').remove();
            
            // Insertar al inicio de campos_dinamicos
            $('#campos_dinamicos').prepend(data);
            console.log('DEBUG: Odontograma SVG insertado correctamente');
            
            // Script separado para inicializar el odontograma
            function inicializarOdontogramaSVG() {
                if (!window.drawOdontograma || odontogramaSVGIniciado) return;
                
                odontogramaSVGIniciado = true;
                
                try {
                    // Inicializar el odontograma
                    window.drawOdontograma();
                    console.log('DEBUG: Odontograma SVG inicializado correctamente');
                    
                    // Marcar como cargado e inicializado
                    window.odontogramaSVGCargado = true;
                    window.odontogramaSVGInicializado = true;
                    
                    // Asegurarse que los dientes son interactivos si existe la función
                    if (typeof window.setupTeethInteractions === 'function') {
                        window.setupTeethInteractions();
                    }
                    
                    // Sincronizar selección inicial si hay valor previo en el campo del formulario
                    const sincronizarDesdeFormulario = function() {
                        const campoFormulario = document.querySelector('form input[name="dientes_seleccionados"]');
                        if (campoFormulario && campoFormulario.value && typeof window.seleccionados !== 'undefined') {
                            console.log('DEBUG: Sincronizando odontograma con valor existente:', campoFormulario.value);
                            
                            const valores = campoFormulario.value.split(',');
                            valores.forEach(valor => {
                                if (valor.trim()) {
                                    // Agregar a la colección de seleccionados
                                    window.seleccionados.add(parseInt(valor.trim()));
                                    
                                    // Marcar visualmente como seleccionado
                                    const diente = document.querySelector(`.tooth-shape[data-num="${valor.trim()}"]`);
                                    if (diente) {
                                        diente.classList.add('tooth-selected');
                                    }
                                }
                            });
                            
                            // Actualizar visualización
                            if (typeof window.updateSeleccionados === 'function') {
                                window.updateSeleccionados();
                            }
                        }
                    };
                    
                    // Intentar sincronizar ahora y nuevamente después de un breve retraso
                    sincronizarDesdeFormulario();
                    setTimeout(sincronizarDesdeFormulario, 500);
                    
                } catch (e) {
                    console.error('ERROR: Falló la inicialización del odontograma SVG:', e);
                }
            }
            
            // Intentar inicializar inmediatamente si la función ya está definida
            if (typeof window.drawOdontograma === 'function') {
                console.log('DEBUG: Función drawOdontograma ya disponible, inicializando...');
                inicializarOdontogramaSVG();
            }
            
            // O también intentar después de un breve tiempo para asegurar que se cargaron todos los scripts
            setTimeout(function() {
                if (!odontogramaSVGIniciado && typeof window.drawOdontograma === 'function') {
                    console.log('DEBUG: Inicializando odontograma SVG después del timeout...');
                    inicializarOdontogramaSVG();
                } else if (!odontogramaSVGIniciado) {
                    console.error('DEBUG: No se pudo encontrar la función drawOdontograma después del timeout');
                }
                
                // Desmarcar estado de carga
                window.odontogramaSVGCargando = false;
            }, 500);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('DEBUG: Error al cargar odontograma SVG:', textStatus, errorThrown);
            
            // Eliminar el mensaje de carga
            $('#loading-odontograma').remove();
            
            // Mensaje de error visual
            $('#campos_dinamicos').prepend(
                '<div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">' +
                '<strong>Aviso:</strong> Error al cargar el odontograma avanzado. Usando versión básica como respaldo.' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                '<span aria-hidden="true">&times;</span></button></div>'
            );
            
            // Usar versión básica como respaldo
            insertarOdontogramaBasico();
            
            // Desmarcar estado de carga
            window.odontogramaSVGCargando = false;
        }
    });
}

// Función de respaldo para insertar odontograma básico (solo se usa si falla el SVG)
function insertarOdontogramaBasico() {
    console.log('Insertando odontograma básico (respaldo)');
    
    // Asegurarse que no existe un odontograma previo
    if ($('#odontograma-dinamico').length > 0) {
        console.log('El odontograma ya existe, eliminándolo primero para evitar duplicados');
        $('#odontograma-dinamico').remove();
    }
    
    var odontogramaHtml = `
        <div id="odontograma-dinamico" class="mb-4">
            <h5 class="mt-4 mb-2 text-primary">Odontograma (Versión Básica)</h5>
            <div id="odontograma-container" style="max-width: 900px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;">
                <h2 style="color: #0056b3; text-align: center; margin-bottom: 20px;">Odontograma - Selección de Dientes</h2>
                
                <div class="odontograma-simple" style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">
                    <div style="width: 100%; margin-bottom: 10px; text-align: center; font-weight: bold;">Arcada Superior</div>
                `;
                
    // Agregar dientes superiores en fila
    var dientesSup = [18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28];
    for (var i = 0; i < dientesSup.length; i++) {
        odontogramaHtml += '<button type="button" class="btn-diente" data-diente="' + dientesSup[i] + '" ' +
            'style="width: 40px; height: 40px; background: white; border: 1px solid #ccc; margin: 3px; cursor: pointer; border-radius: 4px;">' +
            dientesSup[i] + '</button>';
    }
    
    odontogramaHtml += '<div style="width: 100%; margin: 15px 0; text-align: center; font-weight: bold;">Arcada Inferior</div>';
    
    // Agregar dientes inferiores en fila
    var dientesInf = [48, 47, 46, 45, 44, 43, 42, 41,31, 32, 33, 34, 35, 36, 37, 38];
    for (var i = 0; i < dientesInf.length; i++) {
        odontogramaHtml += '<button type="button" class="btn-diente" data-diente="' + dientesInf[i] + '" ' +
            'style="width: 40px; height: 40px; background: white; border: 1px solid #ccc; margin: 3px; cursor: pointer; border-radius: 4px;">' +
            dientesInf[i] + '</button>';
    }
    
    odontogramaHtml += `
                </div>
                <div class="mt-4" style="padding: 15px; background-color: white; border-radius: 8px; box-shadow: 0 1px 3px #0001;">
                    <h4 style="color: #444; margin-bottom: 10px; font-size: 16px;">Dientes seleccionados:</h4>
                    <div id="dientes-seleccionados-lista" style="min-height: 30px;"><span style="color: #777;">Ninguno seleccionado</span></div>
                    <input type="hidden" id="dientes_seleccionados" name="dientes_seleccionados" value="">
                </div>
            </div>
        </div>`;
    
    // Insertar al inicio de campos_dinamicos
    $('#campos_dinamicos').prepend(odontogramaHtml);
    
    // Agregar funcionalidad a los botones de dientes
    $('.btn-diente').click(function() {
        $(this).toggleClass('seleccionado');
        if ($(this).hasClass('seleccionado')) {
            $(this).css({
                'background-color': '#ffebeb', 
                'border-color': '#ff6347',
                'color': '#ff0000',
                'font-weight': 'bold'
            });
        } else {
            $(this).css({
                'background-color': 'white', 
                'border-color': '#ccc',
                'color': '#000',
                'font-weight': 'normal'
            });
        }
        actualizarListaDientesSeleccionados();
    });
    
    console.log('Odontograma básico insertado correctamente como respaldo');
    
    // Marcar el estado como cargado aunque sea la versión básica
    window.odontogramaSVGCargado = true; 
    window.odontogramaSVGCargando = false;
}

// Función para actualizar la lista de dientes seleccionados (para la versión básica)
function actualizarListaDientesSeleccionados() {
    var dientesSeleccionados = [];
    $('.btn-diente.seleccionado').each(function() {
        dientesSeleccionados.push($(this).data('diente'));
    });
    
    // Ordenar numéricamente
    dientesSeleccionados.sort(function(a, b) { return a - b; });
    
    // Actualizar campo oculto
    $('#dientes_seleccionados').val(dientesSeleccionados.join(','));
    
    // Actualizar lista visual
    var listaHtml = '';
    if (dientesSeleccionados.length === 0) {
        listaHtml = '<span style="color: #777;">Ninguno seleccionado</span>';
    } else {
        // Agrupar dientes por cuadrantes para mejor visualización
        var cuadrante1 = dientesSeleccionados.filter(d => d >= 11 && d <= 18);
        var cuadrante2 = dientesSeleccionados.filter(d => d >= 21 && d <= 28);
        var cuadrante3 = dientesSeleccionados.filter(d => d >= 31 && d <= 38);
        var cuadrante4 = dientesSeleccionados.filter(d => d >= 41 && d <= 48);
        
        // Función para generar HTML de un grupo de dientes
        function generarGrupoDientes(cuadrante, nombre) {
            var html = '';
            if (cuadrante.length > 0) {
                html += '<div class="mb-2"><small class="text-muted">' + nombre + ':</small> ';
                cuadrante.forEach(function(diente) {
                    html += '<span class="badge badge-primary mr-1" style="background: #007bff; color: white; padding: 3px 6px; border-radius: 4px; display: inline-block; margin-right: 4px;">' + diente + '</span>';
                });
                html += '</div>';
            }
            return html;
        }
        
        // Generar HTML para cada cuadrante
        listaHtml += generarGrupoDientes(cuadrante1, 'Cuadrante 1 (Sup. Der.)');
        listaHtml += generarGrupoDientes(cuadrante2, 'Cuadrante 2 (Sup. Izq.)');
        listaHtml += generarGrupoDientes(cuadrante3, 'Cuadrante 3 (Inf. Izq.)');
        listaHtml += generarGrupoDientes(cuadrante4, 'Cuadrante 4 (Inf. Der.)');
    }
    
    $('#dientes-seleccionados-lista').html(listaHtml);
}

// Función única de inicialización - se llama solo una vez al cargar la página
function iniciarOdontogramaUnaVez() {
    // Para evitar reinicios múltiples
    if (window._odontogramaYaIniciado) return;
    window._odontogramaYaIniciado = true;
    
    // Verificar si debemos mostrar el odontograma según la especialidad configurada
    if (typeof window.MOSTRAR_ODONTOGRAMA === 'undefined' || !window.MOSTRAR_ODONTOGRAMA) {
        console.log('[ODONTOGRAMA] No se muestra porque la especialidad configurada no es Odontología');
        $('#odontograma-dinamico').remove(); 
        return;
    }

    // Solo intentamos cargar si aún no se ha cargado
    if (!window.odontogramaSVGCargado && $('#odontograma-dinamico').length === 0) {
        console.log('[ODONTOGRAMA] Realizando la primera carga del odontograma');
        insertarOdontograma();
    } else {
        console.log('[ODONTOGRAMA] El odontograma ya está cargado, no es necesario cargarlo nuevamente');
    }
    
    // Agregar listener para cambios en especialidad
    $(document).on('change', 'select[name="especialidad"], select#especialidad', function() {
        let especialidadSeleccionada = $(this).val() || '';
        let nombreEspecialidad = $(this).find('option:selected').text().toLowerCase();
        
        // Verificar si la especialidad seleccionada es odontología
        let esOdontologia = nombreEspecialidad.includes('odonto') || 
                           nombreEspecialidad.includes('dental') ||
                           nombreEspecialidad.includes('dentista');
        
        console.log('Cambio de especialidad detectado: ' + nombreEspecialidad + ' (Es odontología: ' + esOdontologia + ')');
        
        // Actualizar bandera global
        window.MOSTRAR_ODONTOGRAMA = esOdontologia;
        
        // Actualizar vista
        if (esOdontologia && $('#odontograma-dinamico').length === 0) {
            // Reiniciar contador de inicializaciones
            window._odontogramaInitCount = 0;
            insertarOdontograma();
        } else if (esOdontologia && $('#odontograma-dinamico').length > 0) {
            $('#odontograma-dinamico').show();
        } else if (!esOdontologia && $('#odontograma-dinamico').length > 0) {
            $('#odontograma-dinamico').hide();
        }
    });
}

// Iniciar una sola vez cuando el documento esté listo
$(document).ready(function() {
    // Pequeño delay para asegurar que el DOM esté completo
    setTimeout(iniciarOdontogramaUnaVez, 200);
});

// Exportar la función globalmente para que pueda ser llamada desde nueva_consulta.php
window.insertarOdontograma = insertarOdontograma;
</script>
