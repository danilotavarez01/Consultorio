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

$paciente = null;
$error = null;
$success = null;

// Verificar si se proporcionó un ID de paciente
if (isset($_GET['paciente_id']) && !empty($_GET['paciente_id'])) {
    $paciente_id = $_GET['paciente_id'];
    
    // Obtener datos del paciente
    $sql = "SELECT * FROM pacientes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$paciente_id]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$paciente) {
        $error = "Paciente no encontrado";
    }
} else {
    $error = "ID de paciente no proporcionado";
}

// Procesar el formulario de nueva consulta
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'crear_consulta') {
    try {
        $conn->beginTransaction();
        
        // Obtener la especialidad seleccionada (puede ser diferente a la configuración global)
        $especialidad_id = $_POST['especialidad_id'] ?? null;
        
        // Preparar el array de campos personalizados
        $campos_adicionales = [];
        foreach ($_POST as $key => $value) {
            // Si el campo comienza con 'campo_' es un campo dinámico
            if (strpos($key, 'campo_') === 0) {
                $campo_nombre = substr($key, 6); // Remover el prefijo 'campo_'
                $campos_adicionales[$campo_nombre] = $value;
            }
        }
        
        // Convertir a JSON si hay campos adicionales
        $campos_adicionales = !empty($campos_adicionales) ? json_encode($campos_adicionales) : null;
        
        // Insertar consulta con campos adicionales
        $sql = "INSERT INTO historial_medico (
                    paciente_id, 
                    doctor_id, 
                    fecha, 
                    motivo_consulta, 
                    diagnostico, 
                    tratamiento, 
                    presion_sanguinea, 
                    frecuencia_cardiaca, 
                    peso,
                    observaciones,
                    campos_adicionales,
                    especialidad_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $_POST['paciente_id'],
            $_POST['doctor_id'],
            $_POST['fecha'],
            $_POST['motivo_consulta'],
            $_POST['diagnostico'],
            $_POST['tratamiento'],
            $_POST['presion_sanguinea'] ?? null,
            $_POST['frecuencia_cardiaca'] ?? null,
            $_POST['peso'] ?? null,
            $_POST['observaciones'] ?? null,
            $campos_adicionales,
            $especialidad_id
        ]);
        
        $consulta_id = $conn->lastInsertId();
        
        // Guardar valores de campos personalizados en tabla consulta_campos_valores
        if (!empty($_POST) && $especialidad_id) {
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'campo_') === 0) {
                    $campo_nombre = substr($key, 6); // Remover el prefijo 'campo_'
                    
                    // Obtener el ID del campo desde la tabla especialidad_campos
                    $stmt = $conn->prepare("
                        SELECT id FROM especialidad_campos 
                        WHERE especialidad_id = ? AND nombre_campo = ?
                    ");
                    $stmt->execute([$especialidad_id, $campo_nombre]);
                    $campo = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($campo) {
                        // Insertar el valor en consulta_campos_valores
                        $stmt = $conn->prepare("
                            INSERT INTO consulta_campos_valores (consulta_id, campo_id, valor)
                            VALUES (?, ?, ?)
                        ");
                        $stmt->execute([$consulta_id, $campo['id'], $value]);
                    }
                }
            }
        }
        
        $conn->commit();
        
        // Redirigir a la receta
        header("location: imprimir_receta.php?id=" . $consulta_id);
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

// Obtener lista de médicos con sus especialidades
$medicos = [];
try {
    $sql = "SELECT u.id, CONCAT(u.nombre, ' ', u.apellido) as nombre_completo, u.especialidad_id, e.nombre as especialidad 
            FROM usuarios u 
            LEFT JOIN especialidades e ON u.especialidad_id = e.id 
            WHERE u.rol = 'doctor' 
            ORDER BY u.apellido, u.nombre";
    $medicos = $conn->query($sql)->fetchAll();
} catch (Exception $e) {
    // Si hay error al obtener médicos, continuar sin la lista
}

// Obtener todas las especialidades disponibles
$especialidades = [];
try {
    $sql = "SELECT id, codigo, nombre, descripcion FROM especialidades WHERE estado = 'activo' ORDER BY nombre";
    $especialidades = $conn->query($sql)->fetchAll();
} catch (Exception $e) {
    // Si hay error al obtener especialidades, continuar sin la lista
}

// Obtener especialidad configurada por defecto
$especialidad_default = null;
try {
    $stmt = $conn->prepare("SELECT especialidad_id FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    $especialidad_default = $config['especialidad_id'] ?? null;
} catch (Exception $e) {
    // Si hay error, continuar sin especialidad por defecto
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Consulta Avanzada - Consultorio Médico</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <style>
        .sidebar { 
            min-height: 100vh; 
            background-color: #343a40; 
            padding-top: 20px; 
        }
        .sidebar a { 
            color: #fff; 
            padding: 10px 15px; 
            display: block; 
        }
        .sidebar a:hover { 
            background-color: #454d55; 
            text-decoration: none; 
        }
        .content { 
            padding: 20px; 
        }
        .specialty-selector {
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .specialty-card {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .specialty-card:hover {
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0,123,255,0.15);
        }
        .specialty-card.selected {
            border-color: #007bff;
            background-color: #e7f3ff;
        }
        .specialty-card .card-title {
            color: #007bff;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .specialty-card .card-text {
            color: #6c757d;
            font-size: 0.9em;
        }
        .campos-dinamicos-container {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            min-height: 100px;
        }
        .loading-spinner {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        .campo-requerido {
            color: #dc3545;
        }
        .form-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .section-title {
            color: #495057;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
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
                    <h2><i class="fas fa-stethoscope"></i> Nueva Consulta Médica Avanzada</h2>
                    <?php if ($paciente): ?>
                    <a href="historial_medico.php?id=<?php echo $paciente['id']; ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Historial
                    </a>
                    <?php else: ?>
                    <a href="pacientes.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Pacientes
                    </a>
                    <?php endif; ?>
                </div>
                <hr>

                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
                <?php endif; ?>

                <?php if ($paciente): ?>
                <!-- Información del Paciente -->
                <div class="form-section">
                    <h4 class="section-title"><i class="fas fa-user"></i> Información del Paciente</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Nombre:</strong> <?php echo htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']); ?>
                        </div>
                        <div class="col-md-4">
                            <strong>DNI:</strong> <?php echo htmlspecialchars($paciente['dni']); ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Fecha de Nacimiento:</strong> 
                            <?php 
                            if ($paciente['fecha_nacimiento']) {
                                echo date('d/m/Y', strtotime($paciente['fecha_nacimiento']));
                                $edad = date_diff(date_create($paciente['fecha_nacimiento']), date_create('today'))->y;
                                echo " ({$edad} años)";
                            } else {
                                echo "No registrada";
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Selector de Especialidad -->
                <div class="form-section specialty-selector">
                    <h4 class="section-title"><i class="fas fa-user-md"></i> Seleccionar Especialidad/Perfil de Consulta</h4>
                    <p class="text-muted">Seleccione la especialidad médica para personalizar los campos de la consulta:</p>
                    
                    <div id="especialidades-container" class="row">
                        <?php if (!empty($especialidades)): ?>
                            <?php foreach ($especialidades as $esp): ?>
                            <div class="col-md-4">
                                <div class="specialty-card" data-especialidad-id="<?php echo $esp['id']; ?>" 
                                     data-especialidad-nombre="<?php echo htmlspecialchars($esp['nombre']); ?>"
                                     <?php echo ($esp['id'] == $especialidad_default) ? 'data-default="true"' : ''; ?>>
                                    <div class="card-title">
                                        <i class="fas fa-stethoscope"></i> <?php echo htmlspecialchars($esp['nombre']); ?>
                                        <?php if ($esp['id'] == $especialidad_default): ?>
                                        <span class="badge badge-primary ml-2">Por defecto</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-text">
                                        <?php echo htmlspecialchars($esp['descripcion'] ?: 'Especialidad médica'); ?>
                                    </div>
                                    <small class="text-muted">Código: <?php echo htmlspecialchars($esp['codigo']); ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> 
                                No hay especialidades configuradas en el sistema.
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div id="especialidad-seleccionada" class="mt-3" style="display: none;">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Especialidad seleccionada:</strong> <span id="nombre-especialidad-seleccionada"></span>
                        </div>
                    </div>
                </div>

                <!-- Formulario Principal -->
                <div class="form-section">
                    <form method="POST" id="consultaForm">
                        <input type="hidden" name="action" value="crear_consulta">
                        <input type="hidden" name="paciente_id" value="<?php echo $paciente['id']; ?>">
                        <input type="hidden" name="especialidad_id" id="especialidad_id_input" value="">
                        
                        <h4 class="section-title"><i class="fas fa-calendar-alt"></i> Información Básica de la Consulta</h4>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label><i class="fas fa-calendar"></i> Fecha de Consulta <span class="campo-requerido">*</span></label>
                                <input type="date" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                              <?php 
                            // Mostrar selección de médico si es admin/recepcionista O si multi_médico está activado
                            $mostrar_selector = ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'receptionist') || 
                                              (isset($config['multi_medico']) && $config['multi_medico'] == 1);
                            if ($mostrar_selector): 
                            ?>
                            <div class="form-group col-md-6">
                                <label><i class="fas fa-user-md"></i> Médico <span class="campo-requerido">*</span></label>
                                <select name="doctor_id" id="doctor_id" class="form-control" required>
                                    <option value="">Seleccione un médico...</option>
                                    <?php foreach($medicos as $medico): ?>
                                        <option value="<?php echo $medico['id']; ?>" 
                                                data-especialidad="<?php echo $medico['especialidad_id']; ?>"
                                                <?php echo (isset($_SESSION['id']) && $_SESSION['id'] == $medico['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($medico['nombre_completo']); ?> 
                                            (<?php echo htmlspecialchars($medico['especialidad'] ?: 'Sin especialidad'); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php else: ?>
                            <input type="hidden" name="doctor_id" id="doctor_id" value="<?php echo $_SESSION['id']; ?>">
                            <?php endif; ?>
                        </div>

                        <!-- Signos Vitales -->
                        <h5 class="section-title"><i class="fas fa-heartbeat"></i> Signos Vitales</h5>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label><i class="fas fa-tachometer-alt"></i> Presión Sanguínea (mmHg)</label>
                                <input type="text" name="presion_sanguinea" class="form-control" placeholder="Ej: 120/80">
                                <small class="form-text text-muted">Formato: sistólica/diastólica</small>
                            </div>
                            <div class="form-group col-md-4">
                                <label><i class="fas fa-heartbeat"></i> Frecuencia Cardíaca (lpm)</label>
                                <input type="number" name="frecuencia_cardiaca" class="form-control" placeholder="Latidos por minuto">
                            </div>
                            <div class="form-group col-md-4">
                                <label><i class="fas fa-weight"></i> Peso (lb)</label>
                                <input type="number" step="0.01" name="peso" class="form-control" placeholder="Ej: 75.5">
                            </div>
                        </div>

                        <!-- Contenedor para campos dinámicos según la especialidad -->
                        <div id="campos-dinamicos-section" style="display: none;">
                            <h5 class="section-title"><i class="fas fa-clipboard-list"></i> Campos Específicos de la Especialidad</h5>
                            <div id="campos_dinamicos" class="campos-dinamicos-container">
                                <div class="loading-spinner">
                                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                                    <p class="mt-2">Seleccione una especialidad para cargar los campos específicos...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Campos principales de la consulta -->
                        <h5 class="section-title"><i class="fas fa-notes-medical"></i> Información Clínica</h5>
                        
                        <div class="form-group">
                            <label><i class="fas fa-comment-medical"></i> Motivo de Consulta <span class="campo-requerido">*</span></label>
                            <textarea name="motivo_consulta" class="form-control" rows="3" required 
                                      placeholder="Describa el motivo principal de la consulta..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-diagnosis"></i> Diagnóstico <span class="campo-requerido">*</span></label>
                            <textarea name="diagnostico" class="form-control" rows="3" required
                                      placeholder="Diagnóstico médico..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-pills"></i> Tratamiento <span class="campo-requerido">*</span></label>
                            <textarea name="tratamiento" class="form-control" rows="3" required
                                      placeholder="Tratamiento prescrito..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-sticky-note"></i> Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="3"
                                      placeholder="Observaciones adicionales (opcional)..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Guardar Consulta
                            </button>
                            <button type="button" class="btn btn-secondary btn-lg ml-2" onclick="window.history.back()">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="js/theme-manager.js"></script>
    
    <script>
        $(document).ready(function() {
            console.log('Nueva consulta avanzada cargada');
            
            // Seleccionar especialidad por defecto si existe
            const defaultCard = $('.specialty-card[data-default="true"]');
            if (defaultCard.length > 0) {
                defaultCard.click();
            }
            
            // Manejar selección de especialidad
            $('.specialty-card').click(function() {
                // Remover selección anterior
                $('.specialty-card').removeClass('selected');
                
                // Marcar como seleccionada
                $(this).addClass('selected');
                
                const especialidadId = $(this).data('especialidad-id');
                const especialidadNombre = $(this).data('especialidad-nombre');
                
                // Actualizar campos ocultos
                $('#especialidad_id_input').val(especialidadId);
                
                // Mostrar información de especialidad seleccionada
                $('#nombre-especialidad-seleccionada').text(especialidadNombre);
                $('#especialidad-seleccionada').show();
                
                // Cargar campos dinámicos
                cargarCamposEspecialidad(especialidadId);
                
                console.log('Especialidad seleccionada:', especialidadId, especialidadNombre);
            });
            
            // Validación del formulario
            $('#consultaForm').submit(function(e) {
                if (!$('#especialidad_id_input').val()) {
                    e.preventDefault();
                    alert('Por favor seleccione una especialidad antes de continuar.');
                    return false;
                }
            });
        });
        
        function cargarCamposEspecialidad(especialidadId) {
            console.log('Cargando campos para especialidad:', especialidadId);
            
            const container = $('#campos_dinamicos');
            
            // Mostrar loading
            container.html(`
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Cargando campos específicos...</p>
                </div>
            `);
            
            // Mostrar sección de campos dinámicos
            $('#campos-dinamicos-section').show();
            
            $.ajax({
                url: 'get_campos_especialidad_por_id.php',
                type: 'GET',
                data: { especialidad_id: especialidadId },
                dataType: 'json',
                timeout: 10000,
                success: function(response) {
                    console.log('Respuesta del servidor:', response);
                    
                    if (response.success && response.campos) {
                        mostrarCamposDinamicos(response.campos);
                    } else {
                        mostrarMensajeError('No se encontraron campos específicos para esta especialidad.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    
                    mostrarMensajeError('Error al cargar los campos específicos. ' + error);
                }
            });
        }
        
        function mostrarCamposDinamicos(campos) {
            console.log('Mostrando campos dinámicos:', campos);
            
            const container = $('#campos_dinamicos');
            container.empty();
            
            if (!campos || Object.keys(campos).length === 0) {
                container.html(`
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        No hay campos específicos configurados para esta especialidad.
                    </div>
                `);
                return;
            }
            
            // Crear formulario de campos dinámicos
            let html = '<div class="row">';
            
            Object.keys(campos).forEach(function(nombre, index) {
                const config = campos[nombre];
                const required = config.requerido ? 'required' : '';
                const asterisk = config.requerido ? ' <span class="campo-requerido">*</span>' : '';
                
                let inputHtml = '';
                let colClass = 'col-md-6'; // Por defecto 2 columnas
                
                switch(config.tipo) {
                    case 'text':
                        inputHtml = `<input type="text" class="form-control" name="campo_${nombre}" ${required} 
                                    placeholder="Ingrese ${config.label.toLowerCase()}">`;
                        break;
                    case 'number':
                        inputHtml = `<input type="number" class="form-control" name="campo_${nombre}" ${required} 
                                    placeholder="Ingrese ${config.label.toLowerCase()}">`;
                        break;
                    case 'date':
                        inputHtml = `<input type="date" class="form-control" name="campo_${nombre}" ${required}>`;
                        break;
                    case 'textarea':
                        inputHtml = `<textarea class="form-control" name="campo_${nombre}" rows="3" ${required} 
                                    placeholder="Ingrese ${config.label.toLowerCase()}"></textarea>`;
                        colClass = 'col-md-12'; // Textarea ocupa toda la fila
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
                                <input type="checkbox" class="custom-control-input" id="campo_${nombre}" 
                                       name="campo_${nombre}" value="1">
                                <label class="custom-control-label" for="campo_${nombre}">
                                    ${config.label}${asterisk}
                                </label>
                            </div>`;
                        colClass = 'col-md-12';
                        break;
                    default:
                        inputHtml = `<input type="text" class="form-control" name="campo_${nombre}" ${required}>`;
                }
                
                if (config.tipo === 'checkbox') {
                    html += `<div class="${colClass} form-group">${inputHtml}</div>`;
                } else {
                    html += `
                        <div class="${colClass} form-group">
                            <label><i class="fas fa-clipboard-check"></i> ${config.label}${asterisk}</label>
                            ${inputHtml}
                        </div>`;
                }
            });
            
            html += '</div>';
            container.html(html);
            
            console.log('Campos dinámicos renderizados correctamente');
        }
        
        function mostrarMensajeError(mensaje) {
            const container = $('#campos_dinamicos');
            container.html(`
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    ${mensaje}
                </div>
            `);
        }
    </script>
</body>
</html>
