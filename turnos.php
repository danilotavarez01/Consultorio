<?php
require_once 'session_config.php';
session_start();
require_once "permissions.php";

// Set timezone to Dominican Republic
date_default_timezone_set('America/Santo_Domingo');

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Verificar permisos para gestionar turnos
if (!hasPermission('manage_appointments')) {
    header("location: unauthorized.php");
    exit;
}

require_once "config.php";

// Add this code after the require_once "config.php"; line
// Check if the estado column can accept 'en_consulta'
try {
    $checkColumn = $conn->query("SHOW COLUMNS FROM turnos WHERE Field = 'estado'");
    $columnInfo = $checkColumn->fetch(PDO::FETCH_ASSOC);
    
    // If estado is an ENUM type, we need to modify it
    if (strpos($columnInfo['Type'], 'enum') !== false) {
        // Add 'en_consulta' to the enum if it's not already there
        if (strpos($columnInfo['Type'], 'en_consulta') === false) {
            $conn->exec("ALTER TABLE turnos MODIFY COLUMN estado ENUM('pendiente', 'atendido', 'cancelado', 'en_consulta') DEFAULT 'pendiente'");
        }
    } else {
        // If it's not an enum, make sure it's large enough
        $conn->exec("ALTER TABLE turnos MODIFY COLUMN estado VARCHAR(20) DEFAULT 'pendiente'");
    }
} catch(PDOException $e) {
    // Just continue if there's an error, we'll handle it when actually using the column
}

// Obtener configuración para verificar si multi_medico está habilitado
$config = null;
$multi_medico = false;
$doctores = [];
try {
    $stmt = $conn->query("SELECT multi_medico, medico_nombre FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    $multi_medico = isset($config['multi_medico']) && $config['multi_medico'] == 1;
    
    // Obtener lista de doctores si multi_medico está habilitado
    if ($multi_medico) {
        $stmt = $conn->query("SELECT id, nombre, username FROM usuarios WHERE rol IN ('admin', 'doctor') ORDER BY nombre");
        $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch(PDOException $e) {
    // Si hay error al obtener configuración, continuar con valores por defecto
}

// Verificar y crear columna medico_id en turnos si no existe
try {
    $checkColumn = $conn->query("SHOW COLUMNS FROM turnos LIKE 'medico_id'");
    if ($checkColumn->rowCount() == 0) {
        // Column doesn't exist, create it
        $conn->exec("ALTER TABLE turnos ADD COLUMN medico_id INT NULL");
    }
    
    // También verificar columna medico_nombre por si se necesita
    $checkColumn = $conn->query("SHOW COLUMNS FROM turnos LIKE 'medico_nombre'");
    if ($checkColumn->rowCount() == 0) {
        $conn->exec("ALTER TABLE turnos ADD COLUMN medico_nombre VARCHAR(100) NULL");
    }
} catch(PDOException $e) {
    // Continuar si hay error
}

// Procesar la adición automática de turnos desde Citas.php
if (isset($_GET['agregar_desde_cita']) && isset($_GET['paciente_id']) && isset($_GET['fecha'])) {
    try {
        // Verificar si la cita existe
        $stmt = $conn->prepare("SELECT * FROM citas WHERE id = ?");
        $stmt->execute([$_GET['agregar_desde_cita']]);
        $cita = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cita) {
            // Verificar si ya existe un turno para esta cita
            $stmt = $conn->prepare("SELECT * FROM turnos WHERE paciente_id = ? AND fecha_turno = ? AND hora_turno = ?");
            $stmt->execute([$cita['paciente_id'], $cita['fecha'], $cita['hora']]);
            if ($stmt->rowCount() == 0) {
                // No existe un turno para esta cita, entonces lo creamos
                
                // Check if tipo_turno column exists
                $checkColumn = $conn->query("SHOW COLUMNS FROM turnos LIKE 'tipo_turno'");
                if ($checkColumn->rowCount() == 0) {
                    // Column doesn't exist, create it
                    $conn->exec("ALTER TABLE turnos ADD COLUMN tipo_turno VARCHAR(50) DEFAULT 'Consulta'");
                }
                
                // Obtener nombre del médico para las notas
                $stmt = $conn->prepare("SELECT nombre FROM usuarios WHERE id = ?");
                $stmt->execute([$cita['doctor_id']]);
                $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
                $doctor_nombre = $doctor ? $doctor['nombre'] : 'No especificado';
                
                // Crear notas con información de la cita
                $notas = "Turno generado automáticamente desde la cita #" . $cita['id'] . ". " . ($cita['observaciones'] ?? '');
                
                // Insertar el nuevo turno con información del médico
                if ($multi_medico) {
                    // Si multi_medico está habilitado, usar medico_id y medico_nombre
                    $sql = "INSERT INTO turnos (paciente_id, fecha_turno, hora_turno, notas, tipo_turno, estado, medico_id, medico_nombre) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        $cita['paciente_id'],
                        $cita['fecha'],
                        $cita['hora'],
                        $notas,
                        'Consulta',  // Tipo de turno default
                        'pendiente',  // Estado default
                        $cita['doctor_id'],
                        $doctor_nombre
                    ]);
                } else {
                    // Si multi_medico no está habilitado, usar solo medico_nombre del config
                    $sql = "INSERT INTO turnos (paciente_id, fecha_turno, hora_turno, notas, tipo_turno, estado, medico_nombre) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        $cita['paciente_id'],
                        $cita['fecha'],
                        $cita['hora'],
                        $notas,
                        'Consulta',  // Tipo de turno default
                        'pendiente',  // Estado default
                        $config['medico_nombre'] ?? 'Médico Tratante'
                    ]);
                }
                
                // Actualizar el estado de la cita a "Confirmada"
                $stmt = $conn->prepare("UPDATE citas SET estado = 'Confirmada' WHERE id = ?");
                $stmt->execute([$cita['id']]);
                
                // Redirigir a la página de turnos con la fecha de la cita
                header("location: turnos.php?fecha=" . $cita['fecha'] . "&mensaje=Turno agregado correctamente");
                exit();
            } else {
                // Ya existe un turno para esta cita, redirigir con mensaje
                header("location: turnos.php?fecha=" . $cita['fecha'] . "&mensaje=Ya existe un turno para esta cita");
                exit();
            }
        } else {
            // La cita no existe, redirigir con error
            header("location: turnos.php?error=La cita especificada no existe");
            exit();
        }
    } catch(PDOException $e) {
        // Error en la base de datos
        header("location: turnos.php?error=" . urlencode("Error: " . $e->getMessage()));
        exit();
    }
}

// Procesar formulario de nuevo turno
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'registrar_llegada') {
            try {
                // Verificar si el turno existe
                $stmt = $conn->prepare("SELECT * FROM turnos WHERE id = ?");
                $stmt->execute([$_POST['turno_id']]);
                $turno = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($turno) {
                    // Obtener el número máximo de orden_llegada para la fecha actual
                    $stmt = $conn->prepare("SELECT MAX(orden_llegada) as max_orden FROM turnos WHERE fecha_turno = ?");
                    $stmt->execute([$turno['fecha_turno']]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $nuevo_orden = ($result['max_orden'] ?? 0) + 1;
                    
                    // Actualizar el orden de llegada
                    $stmt = $conn->prepare("UPDATE turnos SET orden_llegada = ? WHERE id = ?");
                    $stmt->execute([$nuevo_orden, $_POST['turno_id']]);
                    
                    // Redirigir a la misma página
                    header("location: turnos.php?fecha=" . $turno['fecha_turno'] . "&mensaje=Llegada registrada correctamente");
                    exit();
                } else {
                    header("location: turnos.php?error=El turno especificado no existe");
                    exit();
                }
            } catch(PDOException $e) {
                header("location: turnos.php?error=" . urlencode("Error: " . $e->getMessage()));
                exit();
            }        } elseif ($_POST['action'] == 'crear') {
            // Check if tipo_turno column exists
            try {
                $checkColumn = $conn->query("SHOW COLUMNS FROM turnos LIKE 'tipo_turno'");
                if ($checkColumn->rowCount() == 0) {
                    // Column doesn't exist, create it
                    $conn->exec("ALTER TABLE turnos ADD COLUMN tipo_turno VARCHAR(50) DEFAULT 'Consulta'");
                }
                
                // Si no se proporciona la hora, usar la hora actual con segundos
                $hora_turno = isset($_POST['hora_turno']) && !empty($_POST['hora_turno']) ? 
                              $_POST['hora_turno'] : date('H:i:s');
                
                // Determinar médico según configuración
                $medico_id = null;
                $medico_nombre = null;
                
                if ($multi_medico && isset($_POST['medico_id']) && !empty($_POST['medico_id'])) {
                    // Si multi_medico está habilitado y se seleccionó un médico
                    $medico_id = $_POST['medico_id'];
                    // Obtener el nombre del médico seleccionado
                    $stmt_medico = $conn->prepare("SELECT nombre FROM usuarios WHERE id = ?");
                    $stmt_medico->execute([$medico_id]);
                    $medico_data = $stmt_medico->fetch(PDO::FETCH_ASSOC);
                    $medico_nombre = $medico_data ? $medico_data['nombre'] : null;
                } else {
                    // Si no está habilitado multi_medico, usar el médico por defecto de configuración
                    $medico_nombre = $config['medico_nombre'] ?? 'Médico Tratante';
                }
                
                $sql = "INSERT INTO turnos (paciente_id, fecha_turno, hora_turno, notas, tipo_turno, medico_id, medico_nombre) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$_POST['paciente_id'], $_POST['fecha_turno'], $hora_turno, $_POST['notas'], $_POST['tipo_turno'], $medico_id, $medico_nombre]);
                header("location: turnos.php?fecha=" . $_POST['fecha_turno']);
                exit();
            } catch(PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        } elseif ($_POST['action'] == 'actualizar_estado') {
            // Solo doctores y admin pueden cambiar el estado a 'atendido'
            if ($_POST['estado'] == 'atendido' && !hasPermission('edit_medical_history')) {
                // Si es una petición AJAX, devolver JSON
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    echo json_encode(['error' => 'No tienes permisos para marcar como atendido']);
                    exit;
                } else {
                    header("location: unauthorized.php");
                    exit;
                }
            }
            
            // Validación especial para estado "en_consulta"
            if ($_POST['estado'] == 'en_consulta') {
                try {
                    // Obtener información del turno actual
                    $stmt = $conn->prepare("SELECT medico_id, medico_nombre FROM turnos WHERE id = ?");
                    $stmt->execute([$_POST['turno_id']]);
                    $turno_actual = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($turno_actual) {
                        // Verificar si ya existe otro paciente en consulta con el mismo médico
                        if ($multi_medico && $turno_actual['medico_id']) {
                            // Si está habilitado multi_medico, verificar por medico_id
                            $stmt = $conn->prepare("SELECT COUNT(*) as count, t.id, p.nombre, p.apellido 
                                                   FROM turnos t 
                                                   JOIN pacientes p ON t.paciente_id = p.id 
                                                   WHERE t.medico_id = ? AND t.estado = 'en_consulta' AND t.id != ?");
                            $stmt->execute([$turno_actual['medico_id'], $_POST['turno_id']]);
                        } else {
                            // Si no está habilitado multi_medico, verificar por medico_nombre
                            $stmt = $conn->prepare("SELECT COUNT(*) as count, t.id, p.nombre, p.apellido 
                                                   FROM turnos t 
                                                   JOIN pacientes p ON t.paciente_id = p.id 
                                                   WHERE t.medico_nombre = ? AND t.estado = 'en_consulta' AND t.id != ?");
                            $stmt->execute([$turno_actual['medico_nombre'], $_POST['turno_id']]);
                        }
                        
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($result['count'] > 0) {
                            // Ya existe otro paciente en consulta con este médico
                            $mensaje_error = "Ya hay otro paciente en consulta con " . htmlspecialchars($turno_actual['medico_nombre']) . ". Solo se puede atender un paciente a la vez por médico.";
                            
                            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                                echo json_encode(['error' => $mensaje_error]);
                                exit;
                            } else {
                                header("location: turnos.php?error=" . urlencode($mensaje_error));
                                exit;
                            }
                        }
                    }
                } catch(PDOException $e) {
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        echo json_encode(['error' => 'Error al validar disponibilidad del médico: ' . $e->getMessage()]);
                        exit;
                    } else {
                        header("location: turnos.php?error=" . urlencode("Error: " . $e->getMessage()));
                        exit;
                    }
                }
            }
            
            $sql = "UPDATE turnos SET estado = ? WHERE id = ?";
            try {
                $stmt = $conn->prepare($sql);
                $stmt->execute([$_POST['estado'], $_POST['turno_id']]);
                
                // Si es una petición AJAX, devolver JSON
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
                    exit;
                } else {
                    // Obtener la fecha del turno para redirect normal
                    $sql = "SELECT fecha_turno FROM turnos WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$_POST['turno_id']]);
                    $turno = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    header("location: turnos.php?fecha=" . $turno['fecha_turno']);
                    exit();
                }
            } catch(PDOException $e) {
                // Si es una petición AJAX, devolver JSON con error
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_With']) == 'xmlhttprequest') {
                    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
                    exit;
                } else {
                    echo "Error: " . $e->getMessage();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Turnos - Consultorio Médico</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; padding: 10px 15px; display: block; }
        .sidebar a:hover { background-color: #454d55; text-decoration: none; }
        .content { padding: 20px; }
        
        /* Estilos mejorados para el estado de los turnos */
        .estado-pendiente { 
            background: linear-gradient(135deg, #e7c34cff 0%, #e7c34cff 100%);
            color: #856404; 
            border-left: 4px solid #ffc107;
            box-shadow: 0 2px 4px rgba(255, 193, 7, 0.2);
        }
        .estado-en_consulta { 
            background: linear-gradient(135deg, #519ae2ff 0%, #519ae2ff 100%);
            color: #0c5460; 
            border-left: 4px solid #17a2b8;
            box-shadow: 0 2px 4px rgba(23, 162, 184, 0.2);
        }
        .estado-atendido { 
            background: linear-gradient(135deg, #66b157ff 0%, #66b157ff 100%);
            color: #155724; 
            border-left: 4px solid #28a745;
            box-shadow: 0 2px 4px rgba(40, 167, 69, 0.2);
        }
        .estado-cancelado { 
            background: linear-gradient(135deg, #e64454ff 0%, #e64454ff 100%);
            color: #721c24; 
            border-left: 4px solid #dc3545;
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2);
        }
        
        /* Efecto hover para las filas de turnos */
        .table-hover tbody tr:hover {
            transform: translateY(-1px);
            transition: all 0.2s ease;
        }
        
        .estado-pendiente:hover { 
            background: linear-gradient(135deg, #fff2cc 0%, #ffdf7e 100%);
            box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
        }
        .estado-en_consulta:hover { 
            background: linear-gradient(135deg, #b8daff 0%, #9aceff 100%);
            box-shadow: 0 4px 8px rgba(23, 162, 184, 0.3);
        }
        .estado-atendido:hover { 
            background: linear-gradient(135deg, #c8e6d0 0%, #b1dfbb 100%);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        }
        .estado-cancelado:hover { 
            background: linear-gradient(135deg, #f6c2c7 0%, #f1aeb5 100%);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }
        
        /* Estilos para dropdown - Implementación manual */
        .dropdown-menu {
            display: none !important;
            position: absolute !important;
            top: 100% !important;
            left: 0 !important;
            z-index: 9999 !important;
            min-width: 180px !important;
            padding: 8px 0 !important;
            margin: 2px 0 0 !important;
            background-color: #fff !important;
            border: 1px solid rgba(0,0,0,.15) !important;
            border-radius: 0.375rem !important;
            box-shadow: 0 10px 25px rgba(0,0,0,.25) !important;
            animation: fadeIn 0.15s ease-in !important;
            overflow: visible !important;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .dropdown-menu.show {
            display: block !important;
            opacity: 1 !important;
        }
        
        .dropdown-item {
            display: block !important;
            width: 100% !important;
            padding: 10px 20px !important;
            clear: both !important;
            font-weight: 400 !important;
            color: #212529 !important;
            text-align: left !important;
            white-space: nowrap !important;
            background-color: transparent !important;
            border: 0 !important;
            cursor: pointer !important;
            text-decoration: none !important;
            transition: all 0.15s ease-in-out !important;
            line-height: 1.5 !important;
        }
        
        .dropdown-item:hover, .dropdown-item:focus {
            color: #16181b !important;
            background-color: #f8f9fa !important;
            text-decoration: none !important;
            transform: translateX(3px) !important;
        }
        
        .btn-group {
            position: relative !important;
            display: inline-flex !important;
            vertical-align: middle !important;
        }
        
        .dropdown-toggle {
            cursor: pointer !important;
            user-select: none !important;
        }
        
        .dropdown-toggle::after {
            display: inline-block !important;
            margin-left: 0.255em !important;
            vertical-align: 0.255em !important;
            content: "" !important;
            border-top: 0.3em solid !important;
            border-right: 0.3em solid transparent !important;
            border-bottom: 0 !important;
            border-left: 0.3em solid transparent !important;
            transition: transform 0.15s ease-in-out !important;
        }
        
        .dropdown-toggle[aria-expanded="true"]::after {
            transform: rotate(180deg) !important;
        }
        
        /* Asegurar que el caret esté presente incluso cuando se actualiza dinámicamente */
        .dropdown-toggle .caret {
            display: inline-block !important;
            width: 0 !important;
            height: 0 !important;
            margin-left: 0.255em !important;
            vertical-align: 0.255em !important;
            border-top: 0.3em solid !important;
            border-right: 0.3em solid transparent !important;
            border-bottom: 0 !important;
            border-left: 0.3em solid transparent !important;
            transition: transform 0.15s ease-in-out !important;

             
        }
        
        /* Asegurar que el contenedor de la tabla no corte el dropdown */
        .table-responsive {
            overflow: visible !important;
        }
        
        /* Asegurar que las celdas no corten el dropdown */
        .table td {
            overflow: visible !important;
        }
        
        /* Asegurar que el contenedor principal no corte el dropdown */
        .content {
            overflow: visible !important;
        }
        
        /* Estilos específicos para los botones de estado */
        .btn-estado {
            transition: all 0.2s ease-in-out !important;
            font-weight: 500 !important;
            border-width: 1px !important;
            min-width: 130px !important; /* Ancho fijo para todos los estados */
            width: 130px !important; /* Ancho fijo para asegurar tamaño consistente */
            text-align: center !important;
        }
        
        /* Estados específicos con !important para asegurar que se apliquen */
        .btn-warning.btn-estado {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
            color: #212529 !important;
        }
        
        .btn-info.btn-estado {
            background-color: #17a2b8 !important;
            border-color: #17a2b8 !important;
            color: #fff !important;
        }
        
        .btn-success.btn-estado {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
            color: #fff !important;
        }
        
        .btn-danger.btn-estado {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: #fff !important;
       
          
        }
        
        .btn-secondary.btn-estado {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            color: #fff !important;
        }
        
        /* Estilos para mensajes de notificación */
        .notification-message {
            position: fixed !important;
            top: 20px !important;
            right: 20px !important;
            z-index: 9999 !important;
            max-width: 400px !important;
            min-width: 300px !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2) !important;
            border-radius: 8px !important;
            font-weight: 500 !important;
        }
        
        .notification-message .close {
            padding: 0.5rem 0.75rem !important;
            margin: -0.5rem -0.75rem -0.5rem auto !important;
        }
    </style>
</head>
<body>
    <!-- Header con modo oscuro -->
    <?php include 'includes/header.php'; ?>
    <div class="container-fluid">
        <div class="row">            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Content -->            <div class="col-md-10 content">                <h2>Gestión de Turnos</h2>
                <hr>
                  <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Sistema de orden por llegada:</strong> Los turnos ahora se organizan automáticamente por orden de llegada.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <?php if(isset($_GET['mensaje'])): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>
                    <div><?php echo htmlspecialchars($_GET['mensaje']); ?></div>
                    <button type="button" class="close ml-auto" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <div><?php echo htmlspecialchars($_GET['error']); ?></div>
                    <button type="button" class="close ml-auto" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>

                <!-- Botón para nuevo turno -->
                <button type="button" class="btn btn-gradient-primary mb-4 px-4 py-2 shadow" data-toggle="modal" data-target="#nuevoTurnoModal" style="font-size:1.15rem; font-weight:500; border-radius:30px; background: linear-gradient(90deg,#007bff 0,#00c6ff 100%); color:#fff;">
                    <i class="fas fa-calendar-plus fa-lg fa-bounce mr-2"></i> Nuevo Turno
                </button>

                <!-- Filtros -->
                <div class="row mb-4 align-items-end" style="background:#f8f9fa; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.04); padding:18px 8px;">
                    <div class="col-md-3">
                        <input type="date" id="filtroFecha" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-3">
                        <select id="filtroEstado" class="form-control">
                            <option value="">Todos los estados</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="en_consulta">En Consulta</option>
                            <option value="atendido">Atendido</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                    <?php if ($multi_medico): ?>
                    <div class="col-md-3">
                        <select id="filtroMedico" class="form-control">
                            <option value="">Todos los médicos</option>
                            <?php foreach ($doctores as $doctor): ?>
                            <option value="<?php echo $doctor['id']; ?>">
                                <?php echo htmlspecialchars($doctor['nombre']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" id="aplicarFiltros" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <button type="button" id="limpiarFiltros" class="btn btn-secondary">
                            <i class="fas fa-eraser"></i> Limpiar
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="col-md-6">
                        <button type="button" id="aplicarFiltros" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <button type="button" id="limpiarFiltros" class="btn btn-secondary">
                            <i class="fas fa-eraser"></i> Limpiar
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Tabla de turnos -->
                <div class="table-responsive">
                    <table class="table table-hover">                        
                        <thead>                           
                            <tr>
                                <th>#</th>
                                <th>Hora</th>
                                <th>Paciente</th>
                                <th>Cedula</th>
                                <th>Tipo</th>
                                <?php if ($multi_medico): ?>
                                <th>Médico</th>
                                <?php endif; ?>
                                <!-- <th>Estado</th> -->
                                <th>Notas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Obtener la fecha del filtro o usar la fecha actual
                            $fecha_mostrar = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
                            
                            // Construir la consulta con filtros dinámicos
                            $where_conditions = ["fecha_turno = ?"];
                            $params = [$fecha_mostrar];
                            
                            // Agregar filtro por estado si se especifica
                            if (isset($_GET['estado']) && !empty($_GET['estado'])) {
                                $where_conditions[] = "t.estado = ?";
                                $params[] = $_GET['estado'];
                            }
                            
                            // Agregar filtro por médico si se especifica y multi_medico está habilitado
                            if ($multi_medico && isset($_GET['medico']) && !empty($_GET['medico'])) {
                                $where_conditions[] = "t.medico_id = ?";
                                $params[] = $_GET['medico'];
                            }
                            
                            $where_clause = implode(" AND ", $where_conditions);
                            
                            // Primero verificar si existe la columna orden_llegada
                            $checkColumn = $conn->query("SHOW COLUMNS FROM turnos LIKE 'orden_llegada'");
                            
                            if ($checkColumn->rowCount() > 0) {
                                // Si la columna existe, ordenar por ella
                                $sql = "SELECT t.*, p.nombre, p.apellido, p.dni 
                                        , p.seguro_medico 
                                        FROM turnos t 
                                        JOIN pacientes p ON t.paciente_id = p.id 
                                        WHERE $where_clause 
                                        ORDER BY t.orden_llegada IS NULL, t.orden_llegada, t.hora_turno";
                            } else {
                                // Si la columna no existe, usar el orden normal
                                $sql = "SELECT t.*, p.nombre, p.apellido, p.dni 
                                        FROM turnos t 
                                        JOIN pacientes p ON t.paciente_id = p.id 
                                        WHERE $where_clause 
                                        ORDER BY t.hora_turno";
                            }
                            try {
                                $stmt = $conn->prepare($sql);
                                $stmt->execute($params);
                                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {                                    $clase_estado = "estado-" . $row['estado'];
                                    echo "<tr class='".$clase_estado."'>";
                                    if (array_key_exists('orden_llegada', $row)) {
                                        echo "<td>".($row['orden_llegada'] ? $row['orden_llegada'] : '-')."</td>";
                                    } else {
                                        echo "<td>-</td>";
                                    }
                                    echo "<td>".date('H:i:s', strtotime($row['hora_turno']))."</td>";
                                    echo "<td>".$row['nombre']." ".$row['apellido']."</td>";
                                    echo "<td>".$row['dni']."</td>";
                                    echo "<td>".htmlspecialchars($row['tipo_turno'] ?? 'Consulta')."</td>";
                                    if ($multi_medico) {
                                        echo "<td>".htmlspecialchars($row['medico_nombre'] ?? 'No asignado')."</td>";
                                    }
                                    // echo "<td>".$row['estado']."</td>";
                                    echo "<td>".$row['notas']."</td>";
                                    echo "<td>";
                                    echo "<input type='hidden' name='turno_id' value='".$row['id']."'>"; // Campo oculto para turno_id
                                    echo "<input type='hidden' class='medico-nombre-hidden' value='".htmlspecialchars($row['medico_nombre'] ?? '')."'>"; // Campo oculto para medico_nombre
                                    echo "<input type='hidden' class='paciente-id-hidden' value='".$row['paciente_id']."'>"; // Campo oculto para paciente_id
                                    // Obtener el texto y icono del estado actual
                                    $estado_actual = $row['estado'];
                                    $estado_texto = '';
                                    $estado_icono = '';
                                    $estado_color = 'btn-secondary';
                                    
                                    switch($estado_actual) {
                                        case 'pendiente':
                                            $estado_texto = 'Pendiente';
                                            $estado_icono = 'fas fa-clock';
                                            $estado_color = 'btn-warning';
                                            break;
                                        case 'en_consulta':
                                            $estado_texto = 'En Consulta';
                                            $estado_icono = 'fas fa-stethoscope';
                                            $estado_color = 'btn-info';
                                            break;
                                        case 'atendido':
                                            $estado_texto = 'Atendido';
                                            $estado_icono = 'fas fa-check-circle';
                                            $estado_color = 'btn-success';
                                            break;
                                        case 'cancelado':
                                            $estado_texto = 'Cancelado';
                                            $estado_icono = 'fas fa-times-circle';
                                            $estado_color = 'btn-danger';
                                            break;
                                        default:
                                            $estado_texto = 'Estado';
                                            $estado_icono = 'fas fa-cog';
                                            $estado_color = 'btn-secondary';
                                    }
                                    
                                    echo "<div class='d-flex align-items-center flex-wrap'>";
                                    echo "<div class='btn-group mr-2 mb-1'>
                                            <button type='button' class='btn btn-sm $estado_color btn-estado dropdown-toggle' aria-haspopup='true' aria-expanded='false' data-estado-actual='$estado_actual'>
                                                <i class='$estado_icono mr-1'></i>$estado_texto
                                            </button>
                                            <div class='dropdown-menu' style='display: none;'>
                                                <form method='POST' class='estado-form'>
                                                    <input type='hidden' name='action' value='actualizar_estado'>
                                                    <input type='hidden' name='turno_id' value='".$row['id']."'>
                                                    <button type='submit' name='estado' value='pendiente' class='dropdown-item'>
                                                        <i class='fas fa-clock text-warning mr-2'></i>Pendiente
                                                    </button>
                                                    <button type='submit' name='estado' value='en_consulta' class='dropdown-item'>
                                                        <i class='fas fa-stethoscope text-info mr-2'></i>En Consulta
                                                    </button>
                                                    <button type='submit' name='estado' value='atendido' class='dropdown-item'>
                                                        <i class='fas fa-check-circle text-success mr-2'></i>Atendido
                                                    </button>
                                                    <button type='submit' name='estado' value='cancelado' class='dropdown-item'>
                                                        <i class='fas fa-times-circle text-danger mr-2'></i>Cancelado
                                                    </button>
                                                </form>
                                            </div>
                                        </div>";
                                    echo "<a href='ver_paciente.php?id=".$row['paciente_id']."' class='btn btn-success btn-sm mr-1 mb-1'><i class='fas fa-user'></i></a>";
                                    
                                    // Solo mostrar botón de facturar si el paciente está en consulta
                                    if ($row['estado'] === 'en_consulta') {
                                        $seguro_valor = (array_key_exists('seguro_medico', $row) && $row['seguro_medico'] !== null) ? $row['seguro_medico'] : '';
                                        echo "<button type='button' class='btn btn-warning btn-sm mb-1' data-toggle='modal' data-target='#modalFacturar' 
                                            data-paciente-nombre='".htmlspecialchars($row['nombre'].' '.$row['apellido'])."' 
                                            data-seguro='".htmlspecialchars($seguro_valor)."' 
                                            data-seguro-monto='' 
                                            data-pacienteid='".$row['paciente_id']."'>
                                            <i class='fas fa-file-invoice-dollar'></i> Facturar
                                        </button>";
                                    }
                                    echo "</div>"; // Cerrar el div d-flex
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } catch(PDOException $e) {
                                echo "Error: " . $e->getMessage();
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Facturar Paciente -->
    <div class="modal fade" id="modalFacturar" tabindex="-1">
        <div class="modal-dialog modal-xl" style="max-width:1500px;">
            <div class="modal-content shadow-lg rounded-3" style="border:2px solid #ffc107; background:linear-gradient(135deg,#fffbe6 0,#fff 100%);">
                <div class="modal-header bg-warning text-dark rounded-top" style="border-bottom:2px solid #ffc107;">
                    <h5 class="modal-title font-weight-bold" style="font-size:1.35rem;"><i class="fas fa-file-invoice-dollar mr-2"></i> Generar Factura</h5>
                    <button type="button" class="close" data-dismiss="modal" style="font-size:1.5rem;"><span>&times;</span></button>
                </div>
                <form id="formFacturar" method="POST" action="facturacion.php">
                    <input type="hidden" name="action" value="create_factura">
                    <input type="hidden" name="paciente_id" id="facturar_paciente_id">
                    <input type="hidden" name="turno_id" id="facturar_turno_id">
                    <input type="hidden" name="medico_nombre" id="facturar_medico_nombre">
                    <div class="modal-body py-4 px-4" style="background:linear-gradient(90deg,#fffbe6 0,#fff 100%);">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3 border-success shadow-sm" style="background:#f8fff4;">
                                    <div class="card-body py-3 px-4" style="color:#222;">
                                        <div class="row align-items-center mb-2">
                                            <div class="col-md-7">
                                                <label class="font-weight-bold mb-1" style="color:#1a3c1a;"><i class="fas fa-user mr-1"></i>Paciente</label>
                                                <input type="text" class="form-control border-success bg-white" id="facturar_paciente_nombre" name="facturar_paciente_nombre" readonly style="color:#222; font-size:1.1rem; font-weight:500;">
                                            </div>
                                            <div class="col-md-5">
                                                <label class="font-weight-bold mb-1" style="color:#1a3c1a;"><i class="fas fa-shield-alt mr-1"></i>Seguro</label>
                                                <input type="text" class="form-control border-success bg-white" id="facturar_seguro_nombre" name="facturar_seguro_nombre" readonly style="color:#222; font-size:1.1rem; font-weight:500;">
                                            </div>
                                        </div>
                                        
                                        <!-- Campo del Doctor del Turno -->
                                        <div class="row align-items-center mb-2">
                                            <div class="col-md-12">
                                                <div class="card border-info shadow-sm" style="background: linear-gradient(90deg,#e7f3ff 60%,#d1ecf1 100%);">
                                                    <div class="card-body py-2 px-3">
                                                        <label class="font-weight-bold mb-1" style="color:#0a3c6a;"><i class="fas fa-user-md mr-1"></i>Doctor del Turno</label>
                                                        <input type="text" class="form-control border-info bg-white" id="facturar_doctor_nombre" name="facturar_doctor_nombre" readonly style="color:#222; font-size:1.1rem; font-weight:500;" placeholder="Doctor no asignado">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label class="font-weight-bold mb-1" style="color:#1a3c1a;"><i class="fas fa-dollar-sign mr-1"></i> Monto Seguro</label>
                                                            <input type="text" class="form-control border-success bg-white" id="facturar_seguro_monto" name="facturar_seguro_monto" style="color:#222; font-size:1.1rem; font-weight:500; height:40px;" inputmode="decimal" placeholder="Monto" required autocomplete="off">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label class="font-weight-bold mb-1" style="color:#1a3c1a;"><i class="fas fa-credit-card mr-1"></i> Método de Pago</label>
                                                            <select class="form-control border-success bg-white" id="facturar_metodo_pago" name="facturar_metodo_pago" style="color:#222; font-size:1.1rem; font-weight:500; height:40px;">
                                                                <option value="Efectivo">Efectivo</option>
                                                                <option value="Tarjeta">Tarjeta</option>
                                                                <option value="Transferencia">Transferencia</option>
                                                                <option value="Cheque">Cheque</option>
                                                                <option value="Otro">Otro</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group mb-3">
                                                            <label class="font-weight-bold mb-1" style="color:#1a3c1a;"><i class="fas fa-hashtag mr-1"></i> Recibo No.</label>
                                                            <input type="text" class="form-control border-success bg-white" id="facturar_recibo_no" name="facturar_recibo_no" style="color:#222; font-size:1.1rem; font-weight:500; height:40px;" placeholder="Número de recibo" autocomplete="off">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group mb-3">
                                                            <label class="font-weight-bold mb-1" style="color:#1a3c1a;"><i class="fas fa-percent mr-1"></i> Descuento (%)</label>
                                                            <input type="number" class="form-control border-success bg-white" id="facturar_descuento" name="facturar_descuento" min="0" max="100" value="0" style="color:#222; font-size:1.1rem; font-weight:500; height:40px;">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group mb-3">
                                                            <label class="font-weight-bold mb-1" style="color:#1a3c1a;"><i class="fas fa-receipt mr-1"></i> Impuesto (%)</label>
                                                            <input type="number" class="form-control border-success bg-white" id="facturar_impuesto" name="facturar_impuesto" min="0" max="100" value="0" style="color:#222; font-size:1.1rem; font-weight:500; height:40px;">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group mb-0">
                                                            <label class="font-weight-bold mb-2" style="color:#1a3c1a;"><i class="fas fa-comment-dots mr-1"></i> Observaciones</label>
                                                            <textarea class="form-control border-success bg-white" id="facturar_observaciones" name="facturar_observaciones" rows="3" style="color:#222; font-size:1.1rem; font-weight:500; min-height:80px; resize:vertical;"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-md-12">
                                                        <div class="card border-warning">
                                                            <div class="card-body py-2 px-3">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span class="font-weight-bold" style="font-size:1.15rem; color:#b8860b;">Total Factura:</span>
                                                                    <span id="facturar_total" class="font-weight-bold" style="font-size:1.25rem; color:#d35400;">$0.00</span>
                                                                </div>
                                                                <!-- <button type="button" class="btn btn-outline-warning btn-sm mt-2" id="calcularTotalFactura" style="border-radius:20px; font-weight:500;"><i class="fas fa-calculator mr-1"></i>Calcular Total</button> -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3 border-info shadow-sm" style="background:#f8fbff;">
                                    <div class="card-body py-3 px-4">
                                        <h5 class="font-weight-bold mb-3" style="color:#0a3c6a;"><i class="fas fa-stethoscope mr-2"></i>Procedimientos</h5>
                                        <div id="factura-items-container" class="mb-2">
                                            <!-- Procedimiento Item Rows -->
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm mt-2 shadow-sm" id="agregarItemFactura" style="border-radius:20px; font-weight:500;">
                                            <i class="fas fa-plus mr-1"></i>Agregar Procedimiento
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom" style="border-top:2px solid #ffc107;">
                        <button type="button" class="btn btn-secondary px-4 py-2" data-dismiss="modal" style="border-radius:20px; font-weight:500;">Cancelar</button>
                        <button type="submit" class="btn btn-warning px-4 py-2" style="border-radius:20px; font-weight:500;"><i class="fas fa-save mr-2"></i>Generar Factura</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal Nuevo Turno -->
    <div class="modal fade" id="nuevoTurnoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Turno</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="crear">
                        <div class="form-group">
                            <label>Paciente</label>
                            <select name="paciente_id" class="form-control" required>
                                <?php
                                $sql = "SELECT id, nombre, apellido, dni FROM pacientes ORDER BY apellido, nombre";
                                try {
                                    $stmt = $conn->query($sql);
                                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='".$row['id']."'>".$row['apellido'].", ".$row['nombre']." - DNI: ".$row['dni']."</option>";
                                    }
                                } catch(PDOException $e) {
                                    echo "Error: " . $e->getMessage();
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Fecha</label>
                            <input type="date" name="fecha_turno" class="form-control" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>">
                        </div>                        <div class="form-group">
                            <label>Hora</label>
                            <input type="hidden" name="hora_turno" value="<?php echo date('H:i:s', time()); ?>">
                            <input type="text" class="form-control" disabled value="<?php echo date('H:i:s', time()); ?>">
                            <small class="form-text text-muted">Hora actual: <?php echo date('h:i:s A', time()); ?> (se aplica automáticamente)</small>
                        </div>
                        <div class="form-group">
                            <label>Tipo de Turno</label>
                            <select name="tipo_turno" class="form-control" required>
                                <option value="Consulta">Consulta</option>
                                <option value="Resultados">Resultados</option>
                                <option value="Control">Control</option>
                                <option value="Procedimiento">Procedimiento</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <?php if ($multi_medico): ?>
                        <div class="form-group">
                            <label>Médico</label>
                            <select name="medico_id" class="form-control" required>
                                <option value="">Seleccione un médico</option>
                                <?php foreach ($doctores as $doctor): ?>
                                <option value="<?php echo $doctor['id']; ?>">
                                    <?php echo htmlspecialchars($doctor['nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php else: ?>
                        <div class="form-group">
                            <label>Médico</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($config['medico_nombre'] ?? 'Médico Tratante'); ?>" readonly>
                            <small class="form-text text-muted">Médico asignado por defecto en configuración</small>
                        </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label>Notas</label>
                            <textarea name="notas" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/popper-2.5.4.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="js/theme-manager.js"></script>    <script>
        $(document).ready(function() {
            // --- Factura Items dinámicos ---
            let facturaItemIndex = 0;
            function agregarItemFactura() {
                const container = $('#factura-items-container');
                const itemHtml = `
                    <div class="factura-item-row mb-2" data-index="${facturaItemIndex}">
                        <div class="card border-info">
                            <div class="card-body py-2 px-3" style="color:#222;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <select class="form-control procedimiento-select" name="items[${facturaItemIndex}][procedimiento_id]" required>
                                            <option value="">Seleccionar procedimiento...</option>
                                            <?php
                                            $stmt_proc = $conn->query("SELECT id, nombre, precio_venta FROM procedimientos WHERE activo = 1 ORDER BY nombre");
                                            while($proc = $stmt_proc->fetch(PDO::FETCH_ASSOC)) {
                                                echo "<option value='".$proc['id']."' data-precio='".$proc['precio_venta']."'>".htmlspecialchars($proc['nombre'])." ($".number_format($proc['precio_venta'],2).")</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" class="form-control item-cantidad" name="items[${facturaItemIndex}][cantidad]" min="1" value="1" required style="color:#222;">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" class="form-control item-precio" name="items[${facturaItemIndex}][precio]" required style="color:#222;">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger btn-sm eliminar-item-factura" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            // Actualizar el campo de precio al seleccionar procedimiento y refrescar el total
            $(document).on('change', '.procedimiento-select', function() {
                var precio = $(this).find('option:selected').data('precio');
                var row = $(this).closest('.factura-item-row');
                if (precio !== undefined) {
                    row.find('.item-precio').val(precio);
                } else {
                    row.find('.item-precio').val('');
                }
                calcularTotalFactura();
            });
                container.append(itemHtml);
                facturaItemIndex++;
            }

            // Calcular total de la factura
            function calcularTotalFactura() {
                let subtotal = 0;
                $('.factura-item-row').each(function() {
                    const cantidad = parseFloat($(this).find('.item-cantidad').val()) || 0;
                    const precio = parseFloat($(this).find('.item-precio').val()) || 0;
                    subtotal += cantidad * precio;
                });
                let descuento = parseFloat($('#facturar_descuento').val()) || 0;
                let impuesto = parseFloat($('#facturar_impuesto').val()) || 0;
                let total = subtotal;
                if (descuento > 0) {
                    total = total - (total * (descuento / 100));
                }
                if (impuesto > 0) {
                    total = total + (total * (impuesto / 100));
                }
                if (total < 0) total = 0;
                $('#facturar_total').text('$' + total.toFixed(2));
            }

            // Evento para calcular total
            $('#calcularTotalFactura').click(function(){
                calcularTotalFactura();
            });

            // Recalcular total al cambiar valores
            $(document).on('input', '.item-precio, .item-cantidad, #facturar_descuento, #facturar_impuesto, #facturar_seguro_monto', function(){
                calcularTotalFactura();
            });
            // Validar que seguroMonto solo acepte números y punto, no letras
            $('#facturar_seguro_monto').on('input', function() {
                let value = $(this).val();
                // Permitir solo dígitos y máximo un punto decimal
                value = value.replace(/[^\d.]/g, '');
                // Si hay más de un punto, dejar solo el primero
                let parts = value.split('.');
                if (parts.length > 2) {
                    value = parts[0] + '.' + parts.slice(1).join('');
                }
                $(this).val(value);
            });

            // Inicializar con un item SOLO cuando se abre el modal
            $('#modalFacturar').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var pacienteNombre = button.data('paciente-nombre') || '';
                var seguroMonto = button.data('seguro-monto') || '';
                var pacienteId = button.data('pacienteid') || '';
                var turnoId = button.closest('tr').find('input[name="turno_id"]').val() || '';
                var medicoNombre = button.closest('tr').find('.medico-nombre-hidden').val() || '';

                // AJAX para buscar el nombre del seguro
                $.ajax({
                    url: 'buscar_seguro_paciente.php',
                    type: 'POST',
                    data: { paciente_id: pacienteId },
                    success: function(seguroNombre) {
                        $('#facturar_paciente_nombre').val(pacienteNombre);
                        $('#facturar_seguro_nombre').val(seguroNombre);
                        $('#facturar_seguro_monto').val(seguroMonto);
                        $('#facturar_paciente_id').val(pacienteId);
                        $('#facturar_turno_id').val(turnoId);
                        $('#facturar_medico_nombre').val(medicoNombre);
                        $('#facturar_doctor_nombre').val(medicoNombre || 'Doctor no asignado');
                    },
                    error: function() {
                        $('#facturar_paciente_nombre').val(pacienteNombre);
                        $('#facturar_seguro_nombre').val('');
                        $('#facturar_seguro_monto').val(seguroMonto);
                        $('#facturar_paciente_id').val(pacienteId);
                        $('#facturar_turno_id').val(turnoId);
                        $('#facturar_medico_nombre').val(medicoNombre);
                        $('#facturar_doctor_nombre').val(medicoNombre || 'Doctor no asignado');
                    }
                });

                // Limpiar items previos y agregar uno nuevo
                facturaItemIndex = 0;
                $('#factura-items-container').empty();
                agregarItemFactura();
            });


            // Mostrar alert con el nombre del médico al generar factura
            $('#formFacturar').submit(function(e){
                var medicoNombre = $('#facturar_medico_nombre').val();
                alert('Médico: ' + medicoNombre);
                // El formulario se envía normalmente después del alert
            });

            // Botón para agregar más items
            $('#agregarItemFactura').click(function(){
                agregarItemFactura();
            });

            // Eliminar item
            $(document).on('click', '.eliminar-item-factura', function(){
                $(this).closest('.factura-item-row').remove();
                setTimeout(calcularTotalFactura, 50);
            });
            // Actualizar la hora en tiempo real cuando se abre el modal de nuevo turno
            $('#nuevoTurnoModal').on('shown.bs.modal', function () {
                updateHoraActual();
                // Actualizar la hora cada segundo
                window.horaInterval = setInterval(updateHoraActual, 1000);
            });
            
            // Detener la actualización cuando se cierra el modal
            $('#nuevoTurnoModal').on('hidden.bs.modal', function () {
                clearInterval(window.horaInterval);
            });
            
            // Función para actualizar la hora actual
            function updateHoraActual() {
                var now = new Date();
                var hours = now.getHours().toString().padStart(2, '0');
                var minutes = now.getMinutes().toString().padStart(2, '0');
                var seconds = now.getSeconds().toString().padStart(2, '0');
                var ampm = hours >= 12 ? 'PM' : 'AM';
                var horaCompleta = hours + ':' + minutes + ':' + seconds;
                var horaAMPM = (hours > 12 ? hours - 12 : hours) + ':' + minutes + ':' + seconds + ' ' + ampm;
                
                // Actualizar el campo oculto y el campo visible
                $('input[name="hora_turno"]').val(horaCompleta);
                $('input[name="hora_turno"]').next('input').val(horaCompleta);
                $('.form-text.text-muted').text('Hora actual: ' + horaAMPM + ' (se aplica automáticamente)');
            }
            
            // Función para aplicar filtros
            function aplicarFiltros() {
                var fecha = $('#filtroFecha').val();
                var estado = $('#filtroEstado').val();
                var medico = $('#filtroMedico').length ? $('#filtroMedico').val() : '';
                
                var url = 'turnos.php?fecha=' + fecha;
                if (estado) {
                    url += '&estado=' + estado;
                }
                if (medico) {
                    url += '&medico=' + medico;
                }
                
                window.location.href = url;
            }
            
            // Función para limpiar filtros
            function limpiarFiltros() {
                var fecha = $('#filtroFecha').val();
                window.location.href = 'turnos.php?fecha=' + fecha;
            }
            
            // Manejar botón aplicar filtros
            $('#aplicarFiltros').click(function() {
                aplicarFiltros();
            });
            
            // Manejar botón limpiar filtros
            $('#limpiarFiltros').click(function() {
                limpiarFiltros();
            });
            
            // Manejar cambio de fecha (aplicar inmediatamente)
            $('#filtroFecha').change(function() {
                aplicarFiltros();
            });

            // Manejar el envío del formulario de estado
            $('.estado-form').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                var fecha = $('#filtroFecha').val();
                var estado = form.find('button[type="submit"]:focus').val() || 
                            form.find('button[type="submit"][name="estado"]').val();
                
                console.log('Enviando estado:', estado); // Debug
                
                $.ajax({
                    url: 'turnos.php',
                    type: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        console.log('Response:', response); // Debug
                        if (response.success) {
                            // Mostrar mensaje de éxito temporal
                            var $successMsg = $('<div class="alert alert-success alert-dismissible fade show notification-message"><i class="fas fa-check-circle mr-2"></i>' + response.message + '<button type="button" class="close" onclick="$(this).parent().remove()"><span>&times;</span></button></div>');
                            $('body').append($successMsg);
                            
                            // Ocultar mensaje después de 3 segundos
                            setTimeout(function() {
                                $successMsg.fadeOut(500, function() {
                                    $(this).remove();
                                });
                            }, 3000);
                            
                            // Opcional: recargar después de mostrar el mensaje
                            // setTimeout(function() {
                            //     window.location.href = 'turnos.php?fecha=' + fecha;
                            // }, 1000);
                        } else if (response.error) {
                            // Mostrar mensaje de error más amigable
                            var $errorMsg = $('<div class="alert alert-danger alert-dismissible fade show notification-message"><i class="fas fa-exclamation-triangle mr-2"></i>' + response.error + '<button type="button" class="close ml-2" onclick="$(this).parent().remove()"><span>&times;</span></button></div>');
                            $('body').append($errorMsg);
                            
                            // Ocultar mensaje después de 5 segundos
                            setTimeout(function() {
                                $errorMsg.fadeOut(500, function() {
                                    $(this).remove();
                                });
                            }, 5000);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error:', xhr.responseText); // Debug
                        console.log('Status:', status, 'Error:', error); // Debug
                        
                        // Si no es JSON, probablemente sea HTML (error del servidor)
                        if (xhr.responseText.includes('<!DOCTYPE html>') || xhr.responseText.includes('<html')) {
                            console.log('Received HTML instead of JSON, probably a PHP error');
                            // Intentar extraer el error del HTML
                            var tempDiv = document.createElement('div');
                            tempDiv.innerHTML = xhr.responseText;
                            var bodyText = tempDiv.textContent || tempDiv.innerText || '';
                            
                            if (bodyText.includes('Error:')) {
                                var errorMatch = bodyText.match(/Error:\s*(.+)/);
                                if (errorMatch) {
                                    alert('Error del servidor: ' + errorMatch[1]);
                                } else {
                                    alert('Error del servidor. Ver consola para detalles.');
                                }
                            } else {
                                alert('Error del servidor. Ver consola para detalles.');
                            }
                        } else {
                            alert('Error al actualizar el estado: ' + error);
                        }
                    }
                });
            });
            
            // Manejar el registro de llegada
            $('.registro-llegada-form').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                var fecha = $('#filtroFecha').val();
                var row = $(this).closest('tr');
                
                $.post('turnos.php', form.serialize(), function(response) {
                    // Recargar la página para mostrar los cambios
                    window.location.href = 'turnos.php?fecha=' + fecha + '&mensaje=Llegada registrada correctamente';
                }).fail(function() {
                    alert('Error al registrar la llegada');
                });
            });

            // Establecer los valores de los filtros desde la URL
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('fecha')) {
                $('#filtroFecha').val(urlParams.get('fecha'));
            }
            if (urlParams.has('estado')) {
                $('#filtroEstado').val(urlParams.get('estado'));
            }
            if (urlParams.has('medico') && $('#filtroMedico').length) {
                $('#filtroMedico').val(urlParams.get('medico'));
            }
            
            // === SOLUCIÓN MANUAL PARA DROPDOWN SIN BOOTSTRAP ===
            // Desactivar la inicialización de Bootstrap dropdown para evitar errores
            // $('[data-toggle="dropdown"]').dropdown();
            
            // Debugging - verificar si jQuery está cargado
            console.log('jQuery version:', $.fn.jquery);
            console.log('Bootstrap loaded:', typeof $.fn.dropdown !== 'undefined');
            
            // Implementación manual completa del dropdown
            $(document).on('click', '.dropdown-toggle', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Dropdown toggle clicked!'); // Debug
                
                var $button = $(this);
                var $dropdown = $button.next('.dropdown-menu');
                var $allDropdowns = $('.dropdown-menu');
                
                // Cerrar otros dropdowns abiertos
                $allDropdowns.not($dropdown).removeClass('show').hide();
                
                // Toggle el dropdown actual
                if ($dropdown.hasClass('show')) {
                    $dropdown.removeClass('show').hide();
                    console.log('Dropdown hidden');
                } else {
                    $dropdown.addClass('show').show();
                    
                    // Ajustar posición si se sale de la pantalla
                    var buttonOffset = $button.offset();
                    var dropdownHeight = $dropdown.outerHeight();
                    var dropdownWidth = $dropdown.outerWidth();
                    var windowHeight = $(window).height();
                    var windowWidth = $(window).width();
                    var scrollTop = $(window).scrollTop();
                    var scrollLeft = $(window).scrollLeft();
                    
                    // Verificar si se sale por abajo
                    if ((buttonOffset.top + $button.outerHeight() + dropdownHeight - scrollTop) > windowHeight) {
                        // Mostrar arriba del botón
                        $dropdown.css({
                            'top': 'auto',
                            'bottom': '100%',
                            'margin-bottom': '2px',
                            'margin-top': '0'
                        });
                    } else {
                        // Posición normal (abajo del botón)
                        $dropdown.css({
                            'top': '100%',
                            'bottom': 'auto',
                            'margin-top': '2px',
                            'margin-bottom': '0'
                        });
                    }
                    
                    // Verificar si se sale por la derecha
                    if ((buttonOffset.left + dropdownWidth - scrollLeft) > windowWidth) {
                        // Alinear a la derecha
                        $dropdown.css({
                            'left': 'auto',
                            'right': '0'
                        });
                    } else {
                        // Posición normal (alineado a la izquierda)
                        $dropdown.css({
                            'left': '0',
                            'right': 'auto'
                        });
                    }
                    
                    console.log('Dropdown shown');
                }
                
                console.log('Dropdown show class:', $dropdown.hasClass('show')); // Debug
            });
            
            // Cerrar dropdown al hacer click fuera
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.dropdown').length) {
                    $('.dropdown-menu').removeClass('show').hide();
                    console.log('Dropdown closed by outside click'); // Debug
                }
            });
            
            // Prevenir que se cierre el dropdown al hacer click en los items del formulario
            $(document).on('click', '.dropdown-menu', function(e) {
                e.stopPropagation();
            });
            
            // Manejar clicks en los items del dropdown
            $(document).on('click', '.dropdown-item', function(e) {
                e.preventDefault();
                var $item = $(this);
                var estadoValue = $item.attr('value') || $item.data('value');
                var $form = $item.closest('.estado-form');
                var $button = $item.closest('.btn-group').find('.dropdown-toggle');
                
                console.log('Dropdown item clicked:', $item.text().trim(), 'Value:', estadoValue);
                
                // Validación especial para estado "en_consulta"
                if (estadoValue === 'en_consulta') {
                    var currentRow = $button.closest('tr');
                    var currentMedicoNombre = currentRow.find('.medico-nombre-hidden').val();
                    var currentTurnoId = currentRow.find('input[name="turno_id"]').val();
                    
                    // Obtener la fecha actual del filtro para verificar solo turnos del día actual
                    var fechaActual = $('#filtroFecha').val() || new Date().toISOString().split('T')[0];
                    
                    // Verificar si ya hay otro paciente en consulta con el mismo médico en la fecha actual
                    var conflictFound = false;
                    var conflictPatient = '';
                    
                    $('table tbody tr').each(function() {
                        var $row = $(this);
                        var rowTurnoId = $row.find('input[name="turno_id"]').val();
                        var rowMedicoNombre = $row.find('.medico-nombre-hidden').val();
                        var $rowButton = $row.find('.dropdown-toggle');
                        var currentEstado = $rowButton.attr('data-estado-actual');
                        
                        // Si es una fila diferente, mismo médico, y ya está en consulta
                        // Como la tabla ya filtra por fecha, todos los turnos mostrados son del día seleccionado
                        if (rowTurnoId !== currentTurnoId && 
                            rowMedicoNombre === currentMedicoNombre && 
                            currentEstado === 'en_consulta') {
                            
                            conflictFound = true;
                            var pacienteNombre = $row.find('td:nth-child(3)').text().trim();
                            conflictPatient = pacienteNombre;
                            return false; // Break the loop
                        }
                    });
                    
                    if (conflictFound) {
                        var fechaFormateada = new Date(fechaActual + 'T00:00:00').toLocaleDateString('es-DO', {
                            year: 'numeric', 
                            month: '2-digit', 
                            day: '2-digit'
                        });
                        alert('Ya hay otro paciente (' + conflictPatient + ') en consulta con ' + currentMedicoNombre + ' en la fecha ' + fechaFormateada + '. Solo se puede atender un paciente a la vez por médico.');
                        $('.dropdown-menu').removeClass('show').hide();
                        return false;
                    }
                }
                
                // Agregar el valor del estado al formulario si no existe
                if (estadoValue) {
                    var $estadoInput = $form.find('input[name="estado"]');
                    if ($estadoInput.length === 0) {
                        $form.append('<input type="hidden" name="estado" value="' + estadoValue + '">');
                    } else {
                        $estadoInput.val(estadoValue);
                    }
                }
                
                // Actualizar el botón inmediatamente con el nuevo estado
                updateButtonState($button, estadoValue, $item.text().trim());
                
                // También actualizar la clase de la fila de la tabla
                var $row = $button.closest('tr');
                $row.removeClass('estado-pendiente estado-en_consulta estado-atendido estado-cancelado');
                $row.addClass('estado-' + estadoValue);
                
                // Cerrar el dropdown
                $('.dropdown-menu').removeClass('show').hide();
                
                // Enviar el formulario
                $form.trigger('submit');
            });
            
            // Función para actualizar el estado del botón
            function updateButtonState($button, estadoValue, estadoTexto) {
                console.log('Updating button state to:', estadoValue, estadoTexto); // Debug
                
                // Remover todas las clases de color anteriores
                $button.removeClass('btn-warning btn-info btn-success btn-danger btn-secondary');
                
                // Determinar la nueva clase de color, texto e icono
                var newColorClass = 'btn-secondary';
                var newText = 'Estado';
                var newIcon = 'fas fa-cog';
                
                switch(estadoValue) {
                    case 'pendiente':
                        newColorClass = 'btn-warning';
                        newText = 'Pendiente';
                        newIcon = 'fas fa-clock';
                        break;
                    case 'en_consulta':
                        newColorClass = 'btn-info';
                        newText = 'En Consulta';
                        newIcon = 'fas fa-stethoscope';
                        break;
                    case 'atendido':
                        newColorClass = 'btn-success';
                        newText = 'Atendido';
                        newIcon = 'fas fa-check-circle';
                        break;
                    case 'cancelado':
                        newColorClass = 'btn-danger';
                        newText = 'Cancelado';
                        newIcon = 'fas fa-times-circle';
                        break;
                }
                
                // Aplicar la nueva clase con !important forzando el estilo
                $button.addClass(newColorClass + ' btn-estado');
                $button.attr('data-estado-actual', estadoValue);
                
                // Actualizar el HTML del botón
                $button.html('<i class="' + newIcon + ' mr-1"></i>' + newText);
                
                // Manejar el botón de facturar según el estado
                var $row = $button.closest('tr');
                var $actionsDiv = $row.find('.d-flex.align-items-center.flex-wrap');
                var $facturarBtn = $actionsDiv.find('.btn-warning[data-target="#modalFacturar"]');
                
                if (estadoValue === 'en_consulta') {
                    // Si el estado es "en consulta" y no existe el botón de facturar, agregarlo
                    if ($facturarBtn.length === 0) {
                        // Obtener datos del paciente de la fila
                        var pacienteNombre = $row.find('td:nth-child(3)').text().trim();
                        var pacienteId = $row.find('.paciente-id-hidden').val();
                        
                        // Crear el botón de facturar
                        var facturarHtml = '<button type="button" class="btn btn-warning btn-sm mb-1" data-toggle="modal" data-target="#modalFacturar" ' +
                            'data-paciente-nombre="' + pacienteNombre + '" ' +
                            'data-seguro="" ' +
                            'data-seguro-monto="" ' +
                            'data-pacienteid="' + pacienteId + '">' +
                            '<i class="fas fa-file-invoice-dollar"></i> Facturar' +
                            '</button>';
                        
                        // Insertar el botón después del botón de ver paciente
                        $actionsDiv.find('.btn-success[href*="ver_paciente"]').after(' ' + facturarHtml);
                    }
                } else {
                    // Si el estado no es "en consulta", remover el botón de facturar si existe
                    if ($facturarBtn.length > 0) {
                        $facturarBtn.remove();
                    }
                }
                
                // Forzar la aplicación de estilos usando CSS directo si es necesario
                setTimeout(function() {
                    switch(estadoValue) {
                        case 'pendiente':
                            $button.css({
                                'background-color': '#ffc107',
                                'border-color': '#ffc107',
                                'color': '#212529'
                            });
                            break;
                        case 'en_consulta':
                            $button.css({
                                'background-color': '#17a2b8',
                                'border-color': '#17a2b8',
                                'color': '#fff'
                            });
                            break;
                        case 'atendido':
                            $button.css({
                                'background-color': '#28a745',
                                'border-color': '#28a745',
                                'color': '#fff'
                            });
                            break;
                        case 'cancelado':
                            $button.css({
                                'background-color': '#dc3545',
                                'border-color': '#dc3545',
                                'color': '#fff'
                            });
                            break;
                        default:
                            $button.css({
                                'background-color': '#6c757d',
                                'border-color': '#6c757d',
                                'color': '#fff'
                            });
                    }
                }, 50);
                
                console.log('Button updated - Class:', newColorClass, 'Text:', newText, 'Icon:', newIcon);
            }
        });
    </script>
</body>
</html>
