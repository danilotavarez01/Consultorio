<?php
require_once 'session_config.php';
session_start();
require_once "permissions.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Verificar permisos para gestionar pacientes
if (!hasPermission('manage_patients')) {
    header("location: unauthorized.php");
    exit;
}

require_once "config.php";

$consulta = null;
$paciente = null;
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
    
    // Obtener configuración del consultorio para el nombre del médico y especialidad
    $stmt = $conn->query("SELECT medico_nombre, especialidad_id FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Obtener información de la especialidad
    $especialidad_info = null;
    $es_odontologia = false;
    if ($config && $config['especialidad_id']) {
        $stmt = $conn->prepare("SELECT codigo, nombre FROM especialidades WHERE id = ?");
        $stmt->execute([$config['especialidad_id']]);
        $especialidad_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verificar si es odontología
        if ($especialidad_info) {
            $es_odontologia = stripos($especialidad_info['nombre'], 'odont') !== false || 
                             stripos($especialidad_info['codigo'], 'odon') !== false;
        }
    }
    
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
    <title>Detalles de Consulta - Consultorio Médico</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; padding: 10px 15px; display: block; }
        .sidebar a:hover { background-color: #454d55; text-decoration: none; }
        .content { padding: 20px; }
        .consultation-detail { margin-bottom: 20px; }
        .consultation-detail h3 { border-bottom: 1px solid #dee2e6; padding-bottom: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <!-- Header con modo oscuro -->
    <?php include 'includes/header.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Content -->
            <div class="col-md-10 content">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2>Detalles de Consulta</h2>
                    <?php if ($consulta): ?>
                    <div>
                        <a href="imprimir_receta.php?id=<?php echo $consulta['id']; ?>" class="btn btn-primary mr-2">
                            <i class="fas fa-print"></i> Imprimir Receta
                        </a>
                        <a href="historial_medico.php?id=<?php echo $consulta['paciente_id']; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Historial
                        </a>
                    </div>
                    <?php else: ?>
                    <a href="pacientes.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver a Pacientes</a>
                    <?php endif; ?>
                </div>
                <hr>

                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($consulta): ?>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Consulta para: <?php echo htmlspecialchars($consulta['nombre'] . ' ' . $consulta['apellido']); ?></h4>
                    </div>
                    <div class="card-body">
                        <div class="row">                            <div class="col-md-6 consultation-detail">
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
                                </div>                                <div class="row">
                                    <div class="col-md-4 font-weight-bold">Médico:</div>
                                    <div class="col-md-8"><?php echo htmlspecialchars($config['medico_nombre'] ?? 'Médico Tratante'); ?></div>
                                </div>
                                <?php if (isset($consulta['presion_sanguinea']) && !empty($consulta['presion_sanguinea'])): ?>
                                <div class="row">
                                    <div class="col-md-4 font-weight-bold">Presión Sanguínea:</div>
                                    <div class="col-md-8"><?php echo htmlspecialchars($consulta['presion_sanguinea']); ?> mmHg</div>
                                </div>
                                <?php endif; ?>                                <?php if (isset($consulta['frecuencia_cardiaca']) && !empty($consulta['frecuencia_cardiaca'])): ?>
                                <div class="row">
                                    <div class="col-md-4 font-weight-bold">Frecuencia Cardíaca:</div>
                                    <div class="col-md-8"><?php echo htmlspecialchars($consulta['frecuencia_cardiaca']); ?> lpm</div>
                                </div>
                                <?php endif; ?>                                <?php if (isset($consulta['peso']) && !empty($consulta['peso'])): ?>
                                <div class="row">
                                    <div class="col-md-4 font-weight-bold">Peso:</div>
                                    <div class="col-md-8"><?php echo htmlspecialchars($consulta['peso']); ?> lb</div>
                                </div>
                                <?php endif; ?>
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
                        
                        <?php if (!empty($consulta['campos_adicionales'])): 
                            $campos = json_decode($consulta['campos_adicionales'], true);
                            if ($campos && is_array($campos)): ?>
                        <div class="row mt-4">
                            <div class="col-12 consultation-detail">
                                <h3>Información Específica de la Especialidad</h3>
                                <?php foreach($campos as $campo => $valor): ?>
                                <div class="row">
                                    <div class="col-md-4 font-weight-bold"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $campo))); ?>:</div>
                                    <div class="col-md-8">
                                        <?php 
                                        if (is_array($valor)) {
                                            echo htmlspecialchars(implode(", ", $valor));
                                        } else {
                                            echo htmlspecialchars($valor);
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; endif; ?>

                        <?php 
                        // Obtener dientes seleccionados SOLO desde campos_adicionales JSON
                        $dientes_seleccionados = '';
                        $campos_adicionales = json_decode($consulta['campos_adicionales'] ?? '{}', true);
                        
                        if (isset($campos_adicionales['dientes_seleccionados'])) {
                            $dientes_seleccionados = $campos_adicionales['dientes_seleccionados'];
                        }
                        
                        $tiene_dientes = !empty($dientes_seleccionados) && trim($dientes_seleccionados) !== '';
                        ?>
                        
                        <?php if ($tiene_dientes && $es_odontologia): ?>
                        <div class="row mt-4">
                            <div class="col-12 consultation-detail">
                                <h3>Odontograma - Dientes Tratados</h3>
                                <div class="alert alert-info mb-3">
                                    <strong>Dientes seleccionados:</strong> <?php echo htmlspecialchars($dientes_seleccionados); ?>
                                </div>
                                <div id="odontograma-consulta-container" class="mb-3">
                                    <div class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Cargando odontograma...</span>
                                        </div>
                                        <p class="mt-2">Cargando odontograma...</p>
                                    </div>
                                </div>
                                <div id="debug-info" style="background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 12px; display:none;">
                                    <strong>Debug:</strong> Preparando carga del odontograma...<br>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!$tiene_dientes && $es_odontologia): ?>
                        <div class="alert alert-info mt-4">
                            <h5>ℹ️ Consulta Odontológica</h5>
                            <p>Esta es una consulta de odontología, pero no se seleccionaron dientes específicos para tratamiento en esta sesión.</p>
                        </div>
                        <?php elseif ($tiene_dientes && !$es_odontologia): ?>
                        <div class="alert alert-warning mt-4">
                            <h5>ℹ️ Odontograma No Disponible</h5>
                            <p>Esta consulta tiene dientes seleccionados, pero el odontograma solo se muestra para la especialidad de <strong>Odontología</strong>.</p>
                            <p><strong>Especialidad actual:</strong> <?php echo $especialidad_info ? htmlspecialchars($especialidad_info['nombre']) : 'No configurada'; ?></p>
                            <p><strong>Dientes registrados:</strong> <?php echo htmlspecialchars($dientes_seleccionados); ?></p>
                        </div>
                        <?php endif; ?>

                        <div class="row mt-4">
                            <div class="col-12 consultation-detail">
                                <h3>Notas Adicionales</h3>
                                <p><?php echo nl2br(htmlspecialchars($consulta['notas'] ?? $consulta['observaciones'] ?? '')); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="btn-group">
                            <a href="editar_consulta.php?id=<?php echo $consulta['id']; ?>" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Editar Consulta
                            </a>
                            <a href="imprimir_receta.php?id=<?php echo $consulta['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-print"></i> Imprimir Receta
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="js/theme-manager.js"></script>
    
    <?php if ($tiene_dientes && $es_odontologia): ?>
    <script>
        $(document).ready(function() {
            // Dientes seleccionados de la consulta
            const dientesSeleccionados = '<?php echo htmlspecialchars($dientes_seleccionados); ?>';
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
                    addDebugInfo('AJAX exitoso. Status: ' + textStatus);
                    addDebugInfo('Tamaño de respuesta: ' + data.length + ' caracteres');
                    
                    if (data && data.length > 100) {
                        // Insertar el odontograma en el contenedor
                        $('#odontograma-consulta-container').html(data);
                        addDebugInfo('Odontograma insertado en el DOM');
                        
                        // Esperar a que se inicialice y luego marcar los dientes
                        setTimeout(function() {
                            addDebugInfo('Iniciando marcado de dientes...');
                            marcarDientesSeleccionados(dientesArray);
                        }, 1000);
                    } else {
                        addDebugInfo('ERROR: Respuesta vacía o muy pequeña');
                        mostrarError('La respuesta del servidor está vacía');
                    }
                },
                error: function(xhr, status, error) {
                    addDebugInfo('ERROR AJAX: ' + status + ' - ' + error);
                    addDebugInfo('Status Code: ' + xhr.status);
                    addDebugInfo('Response Text: ' + xhr.responseText.substring(0, 200));
                    mostrarError('Error al cargar odontograma: ' + status + ' - ' + error);
                }
            });
            
            // Función para mostrar error
            function mostrarError(mensaje) {
                $('#odontograma-consulta-container').html(
                    '<div class="alert alert-warning">' +
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
                    addDebugInfo('ERROR: No se encontró el elemento SVG #odontograma');
                    return;
                }
                addDebugInfo('SVG encontrado correctamente');
                
                // Hacer que todos los dientes sean de solo lectura (no clicables)
                $('.tooth-shape').css('pointer-events', 'none');
                $('.tooth-shape').css('cursor', 'default');
                $('.tooth-shape').css('opacity', '0.6');
                addDebugInfo('Dientes configurados como solo lectura');
                
                // Agregar gradiente para dientes históricos si no existe
                if (!document.getElementById('coronaGradSelected')) {
                    const defs = svg.querySelector('defs');
                    if (defs) {
                        const gradiente = document.createElementNS('http://www.w3.org/2000/svg', 'linearGradient');
                        gradiente.setAttribute('id', 'coronaGradSelected');
                        gradiente.setAttribute('x1', '0');
                        gradiente.setAttribute('y1', '0');
                        gradiente.setAttribute('x2', '0');
                        gradiente.setAttribute('y2', '1');
                        
                        const stop1 = document.createElementNS('http://www.w3.org/2000/svg', 'stop');
                        stop1.setAttribute('offset', '0%');
                        stop1.setAttribute('stop-color', '#d4edda');
                        
                        const stop2 = document.createElementNS('http://www.w3.org/2000/svg', 'stop');
                        stop2.setAttribute('offset', '100%');
                        stop2.setAttribute('stop-color', '#28a745');
                        
                        gradiente.appendChild(stop1);
                        gradiente.appendChild(stop2);
                        defs.appendChild(gradiente);
                        addDebugInfo('Gradiente personalizado agregado');
                    }
                }
                
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
                        addDebugInfo('✓ Diente ' + numDiente + ' marcado correctamente');
                    } else {
                        addDebugInfo('✗ Diente ' + numDiente + ' NO encontrado en el SVG');
                    }
                });
                
                addDebugInfo('Proceso completado: ' + dientesEncontrados + '/' + dientes.length + ' dientes marcados');
                
                // Actualizar la lista visual si existe
                if (typeof window.updateSeleccionados === 'function') {
                    window.seleccionados = new Set(dientes);
                    window.updateSeleccionados();
                    addDebugInfo('Lista visual actualizada');
                }
                
                // Agregar leyenda específica para consulta
                const leyenda = $('#odontograma-consulta-container').find('.leyenda');
                if (leyenda.length > 0) {
                    leyenda.append(
                        '<span class="leyenda-item" style="display: flex; align-items: center; gap: 6px; font-size: 15px; margin-left: 10px;">' +
                        '<svg class="leyenda-svg" style="width: 28px; height: 32px;">' +
                        '<ellipse cx="14" cy="16" rx="12" ry="15" fill="#28a745" stroke="#155724" stroke-width="3"/>' +
                        '</svg> Tratado en esta consulta</span>'
                    );
                    addDebugInfo('Leyenda agregada');
                }
                
                // Ocultar debug info después de 5 segundos si todo salió bien
                if (dientesEncontrados === dientes.length) {
                    setTimeout(function() {
                        $('#debug-info').fadeOut();
                    }, 5000);
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
        
        #odontograma-consulta-container {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            background-color: #f8f9fa;
        }
        
        #odontograma-consulta-container h2 {
            color: #6c757d !important;
            font-size: 1.5rem !important;
        }
        
        #odontograma-consulta-container::before {
            content: "Vista de Solo Lectura";
            display: block;
            text-align: center;
            background-color: #17a2b8;
            color: white;
            padding: 5px;
            margin: -15px -15px 15px -15px;
            border-radius: 6px 6px 0 0;
            font-weight: bold;
            font-size: 0.9rem;
        }
    </style>
    <?php endif; ?>
</body>
</html>