<?php
// Versión de ver_consulta.php sin autenticación para debug
require_once "config.php";

$consulta = null;
$error = null;

// Verificar si se proporcionó un ID de consulta
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    // Obtener datos de la consulta y del paciente
    $sql = "SELECT h.*, p.nombre, p.apellido, p.dni, p.id as paciente_id 
            FROM historial_medico h 
            JOIN pacientes p ON h.paciente_id = p.id 
            WHERE h.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$consulta) {
        $error = "Consulta no encontrada";
    }
} else {
    $error = "ID de consulta no proporcionado";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver Consulta (Sin Autenticación) - Debug</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <style>
        .content { padding: 20px; }
        .consultation-detail { margin-bottom: 20px; }
        .consultation-detail h3 { border-bottom: 1px solid #dee2e6; padding-bottom: 10px; margin-bottom: 20px; }
        .debug { background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0; font-family: monospace; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>🔍 Ver Consulta (Debug - Sin Autenticación)</h2>
                <div class="debug">
                    <strong>Modo Debug:</strong> Sin validación de sesión<br>
                    <strong>URL actual:</strong> <?php echo $_SERVER['REQUEST_URI']; ?>
                </div>
            </div>
            <hr>

            <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($consulta): ?>
            <div class="debug">
                <strong>Datos de consulta encontrados:</strong><br>
                - ID: <?php echo $consulta['id']; ?><br>
                - Paciente: <?php echo $consulta['nombre'] . ' ' . $consulta['apellido']; ?><br>
                - Dientes seleccionados: "<?php echo $consulta['dientes_seleccionados'] ?? 'NULL'; ?>"<br>
                - ¿Dientes vacío?: <?php echo empty($consulta['dientes_seleccionados']) ? 'SÍ' : 'NO'; ?><br>
                - ¿Dientes es null?: <?php echo is_null($consulta['dientes_seleccionados']) ? 'SÍ' : 'NO'; ?><br>
            </div>

            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Consulta para: <?php echo htmlspecialchars($consulta['nombre'] . ' ' . $consulta['apellido']); ?></h4>
                </div>
                <div class="card-body">
                    
                    <div class="row">
                        <div class="col-md-6 consultation-detail">
                            <h3>Información de la Consulta</h3>
                            <div class="row">
                                <div class="col-md-4 font-weight-bold">Fecha:</div>
                                <div class="col-md-8"><?php echo date('d/m/Y', strtotime($consulta['fecha'])); ?></div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 font-weight-bold">Paciente:</div>
                                <div class="col-md-8">
                                    <?php echo htmlspecialchars($consulta['nombre'] . ' ' . $consulta['apellido']); ?>
                                    (DNI: <?php echo htmlspecialchars($consulta['dni']); ?>)
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12 consultation-detail">
                            <h3>Motivo de Consulta</h3>
                            <p><?php echo nl2br(htmlspecialchars($consulta['motivo_consulta'])); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12 consultation-detail">
                            <h3>Diagnóstico</h3>
                            <p><?php echo nl2br(htmlspecialchars($consulta['diagnostico'])); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12 consultation-detail">
                            <h3>Tratamiento</h3>
                            <p><?php echo nl2br(htmlspecialchars($consulta['tratamiento'])); ?></p>
                        </div>
                    </div>

                    <?php if (!empty($consulta['dientes_seleccionados'])): ?>
                    <div class="alert alert-success">
                        ✅ CONDICIÓN CUMPLIDA: !empty($consulta['dientes_seleccionados']) es TRUE
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12 consultation-detail">
                            <h3>Odontograma - Dientes Tratados</h3>
                            <div class="alert alert-info mb-3">
                                <strong>Dientes seleccionados:</strong> <?php echo htmlspecialchars($consulta['dientes_seleccionados']); ?>
                            </div>
                            <div id="odontograma-consulta-container" class="mb-3">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Cargando odontograma...</span>
                                    </div>
                                    <p class="mt-2">Cargando odontograma...</p>
                                </div>
                            </div>
                            <div id="debug-info" class="debug">
                                <strong>Debug JavaScript:</strong> Preparando carga del odontograma...<br>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        ❌ CONDICIÓN NO CUMPLIDA: !empty($consulta['dientes_seleccionados']) es FALSE
                    </div>
                    <?php endif; ?>

                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    
    <?php if (!empty($consulta['dientes_seleccionados'])): ?>
    <script>
        $(document).ready(function() {
            console.log('=== DEBUG VER CONSULTA SIN AUTH ===');
            
            // Dientes seleccionados de la consulta
            const dientesSeleccionados = '<?php echo htmlspecialchars($consulta['dientes_seleccionados']); ?>';
            const dientesArray = dientesSeleccionados.split(',').map(d => parseInt(d.trim())).filter(d => !isNaN(d));
            
            console.log('Dientes string:', dientesSeleccionados);
            console.log('Dientes array:', dientesArray);
            
            // Función para agregar debug info
            function addDebugInfo(message) {
                $('#debug-info').append(new Date().toLocaleTimeString() + ': ' + message + '<br>');
                console.log('[DEBUG]', message);
            }
            
            addDebugInfo('Iniciando script de odontograma...');
            addDebugInfo('Dientes a marcar: ' + dientesArray.join(', '));
            
            // Cargar el odontograma desde el archivo PHP
            addDebugInfo('Iniciando petición AJAX a odontograma_svg.php...');
            
            $.ajax({
                url: 'odontograma_svg.php',
                type: 'GET',
                cache: false,
                timeout: 20000,
                beforeSend: function() {
                    addDebugInfo('Enviando petición AJAX...');
                },
                success: function(data, textStatus, xhr) {
                    addDebugInfo('✅ AJAX exitoso! Status: ' + textStatus);
                    addDebugInfo('Tamaño de respuesta: ' + data.length + ' caracteres');
                    addDebugInfo('Content-Type: ' + xhr.getResponseHeader('Content-Type'));
                    
                    if (data && data.length > 100) {
                        // Insertar el odontograma en el contenedor
                        $('#odontograma-consulta-container').html(data);
                        addDebugInfo('✅ Odontograma HTML insertado en contenedor');
                        
                        // Verificar que se insertó correctamente
                        setTimeout(function() {
                            const svg = document.getElementById('odontograma');
                            const toothElements = $('.tooth-shape');
                            
                            addDebugInfo('Verificando elementos después de inserción...');
                            addDebugInfo('SVG encontrado: ' + (svg ? 'SÍ' : 'NO'));
                            addDebugInfo('Elementos .tooth-shape: ' + toothElements.length);
                            
                            if (svg && toothElements.length > 0) {
                                addDebugInfo('✅ Elementos necesarios encontrados, iniciando marcado...');
                                marcarDientesSeleccionados(dientesArray);
                            } else {
                                addDebugInfo('❌ No se encontraron elementos necesarios en el SVG');
                            }
                        }, 1500);
                        
                    } else {
                        addDebugInfo('❌ ERROR: Respuesta vacía o muy pequeña (' + data.length + ' chars)');
                        mostrarError('La respuesta del servidor está vacía o es demasiado pequeña');
                    }
                },
                error: function(xhr, status, error) {
                    addDebugInfo('❌ ERROR AJAX: ' + status + ' - ' + error);
                    addDebugInfo('Status Code: ' + xhr.status);
                    addDebugInfo('Ready State: ' + xhr.readyState);
                    if (xhr.responseText) {
                        addDebugInfo('Response Preview: ' + xhr.responseText.substring(0, 100) + '...');
                    }
                    mostrarError('Error al cargar odontograma: ' + status + ' - ' + error);
                }
            });
            
            // Función para mostrar error
            function mostrarError(mensaje) {
                $('#odontograma-consulta-container').html(
                    '<div class="alert alert-danger">' +
                    '<h5>⚠️ Error al cargar odontograma</h5>' +
                    '<p>' + mensaje + '</p>' +
                    '<p><strong>Dientes que se querían mostrar:</strong> ' + dientesSeleccionados + '</p>' +
                    '<button class="btn btn-sm btn-primary" onclick="location.reload()">🔄 Reintentar</button>' +
                    '<a href="odontograma_svg.php" target="_blank" class="btn btn-sm btn-secondary ml-2">🔗 Ver odontograma directo</a>' +
                    '</div>'
                );
            }
            
            // Función para marcar los dientes seleccionados
            function marcarDientesSeleccionados(dientes) {
                addDebugInfo('=== INICIANDO MARCADO DE DIENTES ===');
                
                // Verificar elementos
                const svg = document.getElementById('odontograma');
                const toothElements = $('.tooth-shape');
                
                if (!svg) {
                    addDebugInfo('❌ No se encontró elemento SVG #odontograma');
                    return;
                }
                
                addDebugInfo('✅ SVG encontrado: ' + svg.tagName);
                addDebugInfo('✅ Elementos tooth-shape: ' + toothElements.length);
                
                // Configurar todos los dientes como solo lectura
                toothElements.css({
                    'pointer-events': 'none',
                    'cursor': 'default',
                    'opacity': '0.6'
                });
                addDebugInfo('✅ Dientes configurados como solo lectura');
                
                // Marcar los dientes seleccionados
                let marcados = 0;
                dientes.forEach(function(numDiente) {
                    const selector = '.tooth-shape[data-num="' + numDiente + '"]';
                    const diente = $(selector);
                    
                    addDebugInfo('Buscando diente ' + numDiente + ' con selector: ' + selector);
                    
                    if (diente.length > 0) {
                        diente.css({
                            'fill': '#28a745',
                            'stroke': '#155724',
                            'stroke-width': '3',
                            'opacity': '1',
                            'filter': 'drop-shadow(0 2px 8px rgba(40, 167, 69, 0.3))'
                        });
                        diente.addClass('tooth-selected-readonly');
                        marcados++;
                        addDebugInfo('✅ Diente ' + numDiente + ' marcado correctamente');
                    } else {
                        addDebugInfo('❌ Diente ' + numDiente + ' NO encontrado');
                    }
                });
                
                addDebugInfo('=== RESUMEN FINAL ===');
                addDebugInfo('Dientes marcados: ' + marcados + '/' + dientes.length);
                
                if (marcados === dientes.length) {
                    addDebugInfo('🎉 ¡ÉXITO TOTAL! Todos los dientes marcados');
                    
                    // Ocultar debug después de éxito
                    setTimeout(function() {
                        $('#debug-info').fadeOut();
                    }, 8000);
                } else {
                    addDebugInfo('⚠️ Algunos dientes no fueron encontrados');
                }
            }
        });
    </script>
    
    <style>
        #odontograma-consulta-container {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            background-color: #f8f9fa;
            min-height: 300px;
        }
        
        .tooth-selected-readonly {
            pointer-events: none !important;
            cursor: default !important;
        }
    </style>
    <?php endif; ?>
    
    <hr>
    <div class="mt-4">
        <h5>Enlaces de prueba:</h5>
        <ul>
            <li><a href="?id=30">Ver consulta ID 30</a></li>
            <li><a href="?id=31">Ver consulta ID 31</a></li>
            <li><a href="odontograma_svg.php" target="_blank">Ver odontograma directo</a></li>
            <li><a href="test_odontograma_simple_debug.php">Test odontograma simplificado</a></li>
        </ul>
    </div>
</body>
</html>

