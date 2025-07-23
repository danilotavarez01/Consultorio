<?php
// Versión simplificada de ver_consulta.php sin autenticación para debug
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
    <title>Test Odontograma - Consulta</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .debug-info { background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0; font-family: monospace; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Odontograma en Consulta</h1>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <p><a href="?id=31">Probar con ID 31</a> | <a href="?id=30">Probar con ID 30</a></p>
        <?php endif; ?>

        <?php if ($consulta): ?>
        <div class="card">
            <div class="card-header">
                <h4>Consulta para: <?php echo htmlspecialchars($consulta['nombre'] . ' ' . $consulta['apellido']); ?></h4>
                <small>ID: <?php echo $consulta['id']; ?> | Fecha: <?php echo $consulta['fecha']; ?></small>
            </div>
            <div class="card-body">
                
                <div class="debug-info">
                    <strong>DEBUG:</strong><br>
                    Campo dientes_seleccionados: '<?php echo htmlspecialchars($consulta['dientes_seleccionados'] ?? 'NULL'); ?>'<br>
                    Está vacío: <?php echo empty($consulta['dientes_seleccionados']) ? 'SÍ' : 'NO'; ?><br>
                    Condición para mostrar: <?php echo !empty($consulta['dientes_seleccionados']) ? 'VERDADERO - Debería mostrar odontograma' : 'FALSO - No mostrará odontograma'; ?>
                </div>

                <?php if (!empty($consulta['dientes_seleccionados'])): ?>
                <div class="alert alert-success">
                    ✅ Condición cumplida: se debería mostrar el odontograma
                </div>
                
                <h3>Odontograma - Dientes Tratados</h3>
                <div class="alert alert-info mb-3">
                    <strong>Dientes seleccionados:</strong> <?php echo htmlspecialchars($consulta['dientes_seleccionados']); ?>
                </div>
                
                <div id="odontograma-consulta-container" class="mb-3" style="border: 2px solid #007bff; padding: 15px; border-radius: 8px;">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando odontograma...</span>
                        </div>
                        <p class="mt-2">Cargando odontograma...</p>
                    </div>
                </div>
                
                <div id="debug-info" class="debug-info">
                    <strong>Debug AJAX:</strong> Preparando carga del odontograma...<br>
                </div>
                
                <?php else: ?>
                <div class="alert alert-warning">
                    ❌ Condición NO cumplida: campo dientes_seleccionados está vacío, por eso no se muestra el odontograma
                </div>
                <?php endif; ?>
                
                <h3>Otros datos de la consulta:</h3>
                <p><strong>Motivo:</strong> <?php echo nl2br(htmlspecialchars($consulta['motivo_consulta'])); ?></p>
                <p><strong>Diagnóstico:</strong> <?php echo nl2br(htmlspecialchars($consulta['diagnostico'])); ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <hr>
        <p><a href="debug_ver_consulta_query.php">🔍 Ver debug de consulta</a></p>
        <p><a href="test_ver_consulta_odontograma.php">📋 Ver lista de consultas con dientes</a></p>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    
    <?php if (!empty($consulta['dientes_seleccionados'])): ?>
    <script>
        $(document).ready(function() {
            // Dientes seleccionados de la consulta
            const dientesSeleccionados = '<?php echo htmlspecialchars($consulta['dientes_seleccionados']); ?>';
            const dientesArray = dientesSeleccionados.split(',').map(d => parseInt(d.trim())).filter(d => !isNaN(d));
            
            // Función para agregar debug info
            function addDebugInfo(message) {
                $('#debug-info').append(message + '<br>');
                console.log('[ODONTOGRAMA DEBUG]', message);
            }
            
            addDebugInfo('Iniciando carga del odontograma...');
            addDebugInfo('Dientes a marcar: ' + dientesArray.join(', '));
            
            // Cargar el odontograma desde el archivo PHP
            addDebugInfo('Haciendo petición AJAX a odontograma_svg.php...');
            
            $.ajax({
                url: 'odontograma_svg.php',
                type: 'GET',
                cache: false,
                timeout: 10000,
                success: function(data, textStatus, xhr) {
                    addDebugInfo('✅ AJAX exitoso. Status: ' + textStatus);
                    addDebugInfo('Tamaño de respuesta: ' + data.length + ' caracteres');
                    
                    if (data && data.length > 100) {
                        // Insertar el odontograma en el contenedor
                        $('#odontograma-consulta-container').html(data);
                        addDebugInfo('✅ Odontograma insertado en el DOM');
                        
                        // Esperar a que se inicialice y luego marcar los dientes
                        setTimeout(function() {
                            addDebugInfo('Iniciando marcado de dientes...');
                            marcarDientesSeleccionados(dientesArray);
                        }, 1000);
                    } else {
                        addDebugInfo('❌ ERROR: Respuesta vacía o muy pequeña');
                        mostrarError('La respuesta del servidor está vacía');
                    }
                },
                error: function(xhr, status, error) {
                    addDebugInfo('❌ ERROR AJAX: ' + status + ' - ' + error);
                    addDebugInfo('Status Code: ' + xhr.status);
                    addDebugInfo('Response Text: ' + xhr.responseText.substring(0, 200));
                    mostrarError('Error al cargar odontograma: ' + status + ' - ' + error);
                }
            });
            
            // Función para mostrar error
            function mostrarError(mensaje) {
                $('#odontograma-consulta-container').html(
                    '<div class="alert alert-danger">' +
                    '<h5>⚠️ No se pudo cargar el odontograma visual</h5>' +
                    '<p>' + mensaje + '</p>' +
                    '<p><strong>Dientes seleccionados:</strong> ' + dientesSeleccionados + '</p>' +
                    '<button class="btn btn-sm btn-secondary" onclick="location.reload()">🔄 Intentar de nuevo</button>' +
                    '</div>'
                );
            }
            
            // Función para marcar los dientes seleccionados
            function marcarDientesSeleccionados(dientes) {
                addDebugInfo('Ejecutando marcarDientesSeleccionados con ' + dientes.length + ' dientes');
                
                // Verificar que el SVG esté presente
                const svg = document.getElementById('odontograma');
                if (!svg) {
                    addDebugInfo('❌ ERROR: No se encontró el elemento SVG #odontograma');
                    return;
                }
                addDebugInfo('✅ SVG encontrado correctamente');
                
                // Hacer que todos los dientes sean de solo lectura (no clicables)
                $('.tooth-shape').css('pointer-events', 'none');
                $('.tooth-shape').css('cursor', 'default');
                $('.tooth-shape').css('opacity', '0.6');
                addDebugInfo('✅ Dientes configurados como solo lectura');
                
                // Marcar los dientes seleccionados
                let dientesEncontrados = 0;
                dientes.forEach(function(numDiente) {
                    const diente = $('.tooth-shape[data-num="' + numDiente + '"]');
                    if (diente.length > 0) {
                        diente.addClass('tooth-selected-readonly');
                        diente.css({
                            'fill': '#28a745',
                            'stroke': '#155724',
                            'stroke-width': '3',
                            'opacity': '1',
                            'filter': 'drop-shadow(0 2px 8px #28a74555)'
                        });
                        dientesEncontrados++;
                        addDebugInfo('✅ Diente ' + numDiente + ' marcado correctamente');
                    } else {
                        addDebugInfo('⚠️ Diente ' + numDiente + ' NO encontrado en el SVG');
                    }
                });
                
                addDebugInfo('✅ Proceso completado: ' + dientesEncontrados + '/' + dientes.length + ' dientes marcados');
                
                // Cambiar color del borde del contenedor si todo salió bien
                if (dientesEncontrados === dientes.length) {
                    $('#odontograma-consulta-container').css('border-color', '#28a745');
                    setTimeout(function() {
                        $('#debug-info').html('<strong>✅ Odontograma cargado correctamente con ' + dientesEncontrados + ' dientes marcados</strong>');
                    }, 2000);
                }
            }
        });
    </script>
    
    <style>
        .tooth-selected-readonly {
            pointer-events: none !important;
            cursor: default !important;
        }
        
        #odontograma-consulta-container .tooth-shape {
            opacity: 0.7;
        }
        
        #odontograma-consulta-container .tooth-selected-readonly {
            opacity: 1 !important;
        }
    </style>
    <?php endif; ?>
</body>
</html>
