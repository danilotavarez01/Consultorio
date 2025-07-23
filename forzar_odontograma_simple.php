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
// Establecer una variable global para indicar si mostrar o no el odontograma
window.MOSTRAR_ODONTOGRAMA = <?php echo $mostrarOdontograma ? 'true' : 'false'; ?>;

// Para depuración: Forzar odontograma en desarrollo
// window.MOSTRAR_ODONTOGRAMA = true; // Descomentar esta línea para forzar el odontograma

// Mostrar mensaje de depuración al inicio
console.log("INICIO - Estado de MOSTRAR_ODONTOGRAMA:", window.MOSTRAR_ODONTOGRAMA);

<?php if ($mostrarOdontograma): ?>
window.ESPECIALIDAD_NOMBRE = '<?php echo htmlspecialchars($especialidad['nombre']); ?>';
<?php endif; ?>

// Añadir botón de diagnóstico
$(document).ready(function() {
    $('body').append('<div id="diagnostico-btn" style="position:fixed; top:10px; right:10px; z-index:9999;"><button class="btn btn-warning btn-sm">Diagnosticar Odontograma</button></div>');
    
    $('#diagnostico-btn').click(function() {
        alert("Estado del odontograma:\n" + 
              "MOSTRAR_ODONTOGRAMA: " + window.MOSTRAR_ODONTOGRAMA + "\n" + 
              "Existe #campos_dinamicos: " + ($('#campos_dinamicos').length > 0) + "\n" +
              "Existe #odontograma-dinamico: " + ($('#odontograma-dinamico').length > 0) + "\n" +
              "Función insertarOdontograma: " + (typeof window.insertarOdontograma === 'function'));
        
        // Si la especialidad es odontología pero no hay odontograma, intentar insertar
        if (window.MOSTRAR_ODONTOGRAMA && $('#odontograma-dinamico').length === 0) {
            if (confirm("El odontograma debería mostrarse pero no está presente. ¿Intentar insertarlo?")) {
                window.insertarOdontograma();
            }
        }
    });
});

// Variable para controlar el estado de la carga del odontograma
window.odontogramaSVGCargando = window.odontogramaSVGCargando || false;

// Función para insertar el odontograma
function insertarOdontograma() {
    // Evitar cargas simultáneas que podrían causar problemas
    if (window.odontogramaSVGCargando) {
        console.log('DEBUG: Ya hay una carga del odontograma en curso, evitando duplicación');
        return;
    }

    console.log('DEBUG: Intentando insertar odontograma avanzado SVG');
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
            
            // Asegurarse de que se inicialice correctamente con mejor manejo
            let initCheckCount = 0;
            const checkAndInitialize = function() {
                initCheckCount++;
                console.log('Intento #' + initCheckCount + ' de inicializar el odontograma SVG...');
                
                if (typeof window.drawOdontograma === 'function') {
                    console.log('Función drawOdontograma encontrada, inicializando odontograma SVG');
                    try {
                        // Asegurar que solo se inicializa una vez
                        if (!window.odontogramaSVGInicializado) {
                            window.drawOdontograma();
                            console.log('Odontograma SVG inicializado correctamente');
                            
                            // Marcar como inicializado para evitar llamadas duplicadas
                            window.odontogramaSVGInicializado = true;
                            
                            // Marcar también como cargado para el sistema completo
                            window.odontogramaSVGCargado = true;
                            
                            // Asegurarse que los dientes son interactivos
                            if (typeof window.setupTeethInteractions === 'function') {
                                window.setupTeethInteractions();
                            }
                        } else {
                            console.log('Odontograma SVG ya estaba inicializado, evitando doble inicialización');
                        }
                    } catch (e) {
                        console.error('Error al inicializar odontograma SVG:', e);
                        // Resetear estado de inicialización en caso de error
                        window.odontogramaSVGInicializado = false;
                    }
                } else if (initCheckCount < 5) {
                    // Intentar nuevamente hasta 5 veces con tiempo incremental
                    console.log('Función drawOdontograma no encontrada, reintentando en ' + (400 * initCheckCount) + 'ms...');
                    setTimeout(checkAndInitialize, 400 * initCheckCount);
                } else {
                    console.error('No se pudo inicializar el odontograma SVG después de múltiples intentos');
                }
            };
            
            // Primer intento después de un breve tiempo
            setTimeout(checkAndInitialize, 300);        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('DEBUG: Error al cargar odontograma SVG:', textStatus, errorThrown);
            console.log('Intentando nuevamente cargar el odontograma SVG...');
            
            // Segundo intento con un pequeño retraso antes de usar la versión básica
            setTimeout(function() {
                $.ajax({
                    url: 'odontograma_svg.php',
                    type: 'GET',
                    timeout: 15000, // 15 segundos en el segundo intento
                    success: function(data) {
                        console.log('DEBUG: Éxito en el segundo intento de cargar odontograma_svg.php');
                        // Eliminar el mensaje de carga
                        $('#loading-odontograma').remove();
                        
                        // Insertar al inicio de campos_dinamicos
                        $('#campos_dinamicos').prepend(data);
                        console.log('DEBUG: Odontograma SVG insertado correctamente (segundo intento)');
                        
                        // Inicializar con retraso para asegurar que los scripts se carguen
                        setTimeout(function() {
                            if (typeof window.drawOdontograma === 'function') {
                                console.log('Inicializando funciones del odontograma SVG (segundo intento)');
                                window.drawOdontograma();
                            }
                        }, 800);
                    },
                    error: function() {
                        console.error('DEBUG: Error persistente al cargar odontograma SVG después de dos intentos');
                        
                        // Mensaje de error más descriptivo
                        let errorMsg = 'No se pudo cargar el odontograma SVG avanzado después de varios intentos. ' +
                                      'Se usará la versión básica como respaldo. Por favor, verifique la conexión ' +
                                      'y el estado del servidor.';
                        
                        // Mostrar mensaje en consola y alerta menos intrusiva
                        console.error(errorMsg);
                        
                        // Eliminar el mensaje de carga
                        $('#loading-odontograma').remove();
                        
                        // Crear un mensaje de alerta en lugar de un alert
                        $('#campos_dinamicos').prepend(
                            '<div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">' +
                            '<strong>Aviso:</strong> Usando odontograma básico como respaldo. No se pudo cargar la versión avanzada.' +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                            '<span aria-hidden="true">&times;</span></button></div>'
                        );
                        
                        // Usar versión básica como respaldo
                        insertarOdontogramaBasico();
                    }
                });
            }, 1500);
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
    var dientesInf = [48, 47, 46, 45, 44, 43, 42, 41, 31, 32, 33, 34, 35, 36, 37, 38];
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

// Ejecutar al cargar
$(document).ready(function() {    // Para evitar recargas múltiples y problemas, usamos una variable para controlar si ya se cargó
    window.odontogramaSVGCargado = window.odontogramaSVGCargado || false;
    
    // Verificar si debemos mostrar el odontograma según la especialidad configurada
    if (typeof window.MOSTRAR_ODONTOGRAMA === 'undefined' || !window.MOSTRAR_ODONTOGRAMA) {
        console.log('[ODONTOGRAMA] No se muestra porque la especialidad configurada no es Odontología');
        $('#odontograma-dinamico').remove(); // Eliminar el odontograma si existe pero no debería
        return; // Salir si no es odontología
    }

    // Mostrar mensaje de depuración
    console.log('[ODONTOGRAMA] Inicializando carga del odontograma para especialidad: ' + 
                (typeof window.ESPECIALIDAD_NOMBRE !== 'undefined' ? window.ESPECIALIDAD_NOMBRE : 'Desconocida'));
                
    // Función para detectar y cargar el odontograma en campos_dinamicos SOLO SI ES ODONTOLOGÍA
    function detectarYCargar() {
        // Si ya se cargó exitosamente, no intentar de nuevo para evitar duplicados
        if (window.odontogramaSVGCargado && $('#odontograma-dinamico').length > 0) {
            console.log('[ODONTOGRAMA] Ya se cargó previamente, omitiendo carga duplicada');
            return true;
        }
        
        // PRIMERO verificamos si es una especialidad odontológica - si no lo es, salimos de la función
        if (typeof window.MOSTRAR_ODONTOGRAMA === 'undefined' || !window.MOSTRAR_ODONTOGRAMA) {
            console.log('[ODONTOGRAMA] No se detecta ni carga porque la especialidad configurada no es Odontología');
            // Asegurarse que cualquier odontograma existente sea eliminado
            if ($('#odontograma-dinamico').length > 0) {
                $('#odontograma-dinamico').remove();
            }
            return false;
        }
        
        // Si llegamos aquí, es porque es odontología - verificamos que exista el contenedor
        if ($('#campos_dinamicos').length > 0) {
            // Solo insertamos si no existe o si tuvimos un error previo
            if ($('#odontograma-dinamico').length === 0) {
                console.log('Contenedor encontrado, insertando odontograma para especialidad odontológica...');
                insertarOdontograma();
                return true;
            } else {
                console.log('[ODONTOGRAMA] El odontograma ya existe, no es necesario insertarlo de nuevo');
                return true;
            }
        }
        return false;
    }
      
    // Solo intentamos cargar el odontograma si estamos en una especialidad odontológica
    if (window.MOSTRAR_ODONTOGRAMA) {
        // Primera intento inmediatamente - solo si el odontograma aún no se ha cargado
        if (!window.odontogramaSVGCargado) {
            if (detectarYCargar()) {
                console.log('Odontograma cargado en el primer intento');
                // Marcamos como cargado para evitar múltiples cargas
                window.odontogramaSVGCargado = true;
            } else {
                // Segundo intento después de que la página esté completamente cargada
                setTimeout(function() {
                    console.log('Segundo intento de inserción del odontograma...');
                    if (detectarYCargar()) {
                        console.log('Odontograma cargado en el segundo intento');
                        // Marcamos como cargado para evitar múltiples cargas
                        window.odontogramaSVGCargado = true;
                    }
                }, 1000);
            }
        } else {
            console.log('[ODONTOGRAMA] Omitiendo carga, el odontograma ya fue cargado previamente');
        }
    } else {
        console.log('[ODONTOGRAMA] No se intenta cargar porque la especialidad configurada no es Odontología');
    }
    
    // Agregar listener para cambios en especialidad, en caso de que la interfaz permita cambiarla
    $(document).on('change', 'select[name="especialidad"], select#especialidad', function() {
        let especialidadSeleccionada = $(this).val() || '';
        let nombreEspecialidad = $(this).find('option:selected').text().toLowerCase();
        
        // Verificar si la especialidad seleccionada es odontología
        let esOdontologia = nombreEspecialidad.includes('odonto') || 
                           nombreEspecialidad.includes('dental') ||
                           nombreEspecialidad.includes('dentista');
        
        console.log('Cambio de especialidad detectado: ' + nombreEspecialidad + ' (Es odontología: ' + esOdontologia + ')');
        
        // Si es odontología y no existe el odontograma, insertar
        if (esOdontologia && $('#odontograma-dinamico').length === 0) {
            console.log('Especialidad odontológica seleccionada, insertando odontograma...');
            insertarOdontograma();
        } 
        // Si no es odontología y existe el odontograma, ocultar
        else if (!esOdontologia && $('#odontograma-dinamico').length > 0) {
            console.log('Especialidad no odontológica seleccionada, ocultando odontograma...');
            $('#odontograma-dinamico').hide();
        }
        // Si es odontología y existe el odontograma (pero podría estar oculto), mostrar
        else if (esOdontologia && $('#odontograma-dinamico').length > 0) {
            $('#odontograma-dinamico').show();
        }
    });
});

// Exportar la función globalmente para que pueda ser llamada desde nueva_consulta.php
window.insertarOdontograma = insertarOdontograma;
</script>
