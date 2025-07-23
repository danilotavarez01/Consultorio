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
<?php if ($mostrarOdontograma): ?>
window.ESPECIALIDAD_NOMBRE = '<?php echo htmlspecialchars($especialidad['nombre']); ?>';
<?php endif; ?>

// Función para insertar el odontograma
function insertarOdontograma() {
    console.log('Insertando odontograma básico');
    
    // Solo insertar si no existe ya
    if ($('#odontograma-dinamico').length === 0) {
        var odontogramaHtml = `
            <div id="odontograma-dinamico" class="mb-4">
                <h5 class="mt-4 mb-2 text-primary">Odontograma</h5>
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
        
        console.log('Odontograma básico insertado correctamente');
    } else {
        console.log('El odontograma ya existe, no se inserta de nuevo');
    }
}

// Función para actualizar la lista de dientes seleccionados
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
$(document).ready(function() {
    // Verificar si debemos mostrar el odontograma según la especialidad configurada
    if (typeof window.MOSTRAR_ODONTOGRAMA === 'undefined' || !window.MOSTRAR_ODONTOGRAMA) {
        console.log('[ODONTOGRAMA] No se muestra porque la especialidad configurada no es Odontología');
        return; // Salir si no es odontología
    }

    // Mostrar mensaje de depuración
    console.log('[ODONTOGRAMA] Inicializando carga del odontograma para especialidad: ' + 
                (typeof window.ESPECIALIDAD_NOMBRE !== 'undefined' ? window.ESPECIALIDAD_NOMBRE : 'Desconocida'));

    // Función para detectar y cargar el odontograma en campos_dinamicos
    function detectarYCargar() {
        if ($('#campos_dinamicos').length > 0) {
            if ($('#odontograma-dinamico').length === 0) {
                console.log('Contenedor encontrado, insertando odontograma...');
                insertarOdontograma();
                return true;
            } else {
                console.log('Odontograma ya insertado');
                return true;
            }
        }
        return false;
    }
    
    // Primera intento inmediatamente
    if (detectarYCargar()) {
        console.log('Odontograma cargado en el primer intento');
    } else {
        // Segundo intento después de que la página esté completamente cargada
        setTimeout(function() {
            console.log('Segundo intento de inserción del odontograma...');
            if (detectarYCargar()) {
                console.log('Odontograma cargado en el segundo intento');
            } else {
                // Tercer intento después de un tiempo más largo (por si acaso)
                setTimeout(function() {
                    console.log('Tercer intento de inserción del odontograma...');
                    detectarYCargar();
                }, 2000);
            }
        }, 1000);
    }
    
    // Forzar la inserción del odontograma directamente (para asegurar que aparezca)
    insertarOdontograma();
    
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
