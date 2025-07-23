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

// Verificar permisos para gestionar citas (mismo permiso que para turnos)
if (!hasPermission('manage_appointments')) {
    header("location: unauthorized.php");
    exit;
}

$title = "Gestión de Citas";

require_once "config.php";

// Verificar si la tabla citas existe, de lo contrario crearla
try {    $tableExists = $conn->query("SHOW TABLES LIKE 'citas'")->rowCount() > 0;
    if (!$tableExists) {
        // Crear la tabla citas basada en la estructura de la tabla turnos pero adaptada
        $conn->exec("CREATE TABLE IF NOT EXISTS citas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            paciente_id INT NOT NULL,
            fecha DATE NOT NULL,
            hora TIME NOT NULL,
            doctor_id INT NOT NULL,
            estado ENUM('Pendiente', 'Confirmada', 'Cancelada', 'Completada') DEFAULT 'Pendiente',
            observaciones TEXT,
            INDEX idx_paciente (paciente_id),
            INDEX idx_doctor (doctor_id),
            FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE,
            FOREIGN KEY (doctor_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }
    $db_connected = true;
} catch (Exception $e) {
    $db_connected = false;
    $db_error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title; ?> - Consultorio Médico</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; padding: 10px 15px; display: block; }
        .sidebar a:hover { background-color: #454d55; text-decoration: none; }
        .content { padding: 20px; }
        /* Estilos para los filtros */
        .filtro-badge { margin-right: 5px; }
        .form-inline .form-group { margin-bottom: 10px; }
        
        /* Mejoras responsive para el formulario de búsqueda */
        @media (max-width: 768px) {
            .form-inline {
                flex-direction: column;
                align-items: flex-start;
            }
            .form-inline .form-group {
                width: 100%;
                margin-right: 0;
            }
            .form-inline .btn {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Header con modo oscuro -->
    <?php include 'includes/header.php'; ?>    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Content -->
            <div class="col-md-10 content">
<?php
// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener datos del formulario
    $id = isset($_POST['id_cita']) ? $_POST['id_cita'] : null;
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $paciente_id = $_POST['paciente_id'];
    $doctor_id = $_POST['doctor_id'];
    $estado = $_POST['estado'];
    $observaciones = $_POST['observaciones'];
    
    // Usar la conexión $conn en lugar de $pdo
    
    // Validar datos
    $errores = [];
    
    if (empty($fecha)) {
        $errores[] = "La fecha es obligatoria";
    }
    
    if (empty($hora)) {
        $errores[] = "La hora es obligatoria";
    }
    
    if (empty($paciente_id)) {
        $errores[] = "Debe seleccionar un paciente";
    }
    
    if (empty($doctor_id)) {
        $errores[] = "Debe seleccionar un doctor";
    }
      // Si no hay errores, guardar en la base de datos
    if (empty($errores)) {
        if ($id) {
            // Actualizar cita existente
            $stmt = $conn->prepare("UPDATE citas SET fecha = ?, hora = ?, paciente_id = ?, doctor_id = ?, estado = ?, observaciones = ? WHERE id = ?");
            $stmt->execute([$fecha, $hora, $paciente_id, $doctor_id, $estado, $observaciones, $id]);
            $mensaje = "Cita actualizada correctamente";
        } else {
            // Crear nueva cita
            $stmt = $conn->prepare("INSERT INTO citas (fecha, hora, paciente_id, doctor_id, estado, observaciones) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$fecha, $hora, $paciente_id, $doctor_id, $estado, $observaciones]);
            $mensaje = "Cita registrada correctamente";
        }
          // Redireccionar para evitar reenvío de formulario
        header("Location: Citas.php?mensaje=" . urlencode($mensaje));
        exit;
    }
}

// Eliminar cita
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $conn->prepare("DELETE FROM citas WHERE id = ?");
    $stmt->execute([$id]);
      $mensaje = "Cita eliminada correctamente";
    header("Location: Citas.php?mensaje=" . urlencode($mensaje));
    exit;
}

// Obtener cita para editar
$cita = null;
if (isset($_GET['editar'])) {
    $id = $_GET['editar'];
    $stmt = $conn->prepare("SELECT * FROM citas WHERE id = ?");
    $stmt->execute([$id]);
    $cita = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Obtener lista de citas
$sql = "
    SELECT c.id, c.fecha, c.hora, c.paciente_id, c.doctor_id,
           CONCAT(p.nombre, ' ', p.apellido) as paciente, 
           p.telefono as paciente_telefono,
           u.nombre as doctor, 
           c.estado, c.observaciones
    FROM citas c
    JOIN pacientes p ON c.paciente_id = p.id
    JOIN usuarios u ON c.doctor_id = u.id";

// Inicializar el WHERE
$where = [];
$params = [];

// Aplicar filtro si existe
if (isset($_GET['filtro']) && $_GET['filtro'] == 'hoy') {
    $where[] = "c.fecha = CURDATE()";
}

// Aplicar filtro por rango de fechas
if (isset($_GET['fecha_inicial']) && !empty($_GET['fecha_inicial'])) {
    $where[] = "c.fecha >= ?";
    $params[] = $_GET['fecha_inicial'];
}

if (isset($_GET['fecha_final']) && !empty($_GET['fecha_final'])) {
    $where[] = "c.fecha <= ?";
    $params[] = $_GET['fecha_final'];
}

// Filtrar por doctor si se seleccionó
if (isset($_GET['doctor_filtro']) && !empty($_GET['doctor_filtro'])) {
    $where[] = "c.doctor_id = ?";
    $params[] = $_GET['doctor_filtro'];
}

// Filtrar por estado si se seleccionó
if (isset($_GET['estado_filtro']) && !empty($_GET['estado_filtro'])) {
    $where[] = "c.estado = ?";
    $params[] = $_GET['estado_filtro'];
}

// Filtrar por nombre de paciente si se ingresó
if (isset($_GET['nombre_filtro']) && !empty($_GET['nombre_filtro'])) {
    $where[] = "(p.nombre LIKE ? OR p.apellido LIKE ? OR CONCAT(p.nombre, ' ', p.apellido) LIKE ?)";
    $nombre_busqueda = '%' . $_GET['nombre_filtro'] . '%';
    $params[] = $nombre_busqueda;
    $params[] = $nombre_busqueda;
    $params[] = $nombre_busqueda;
}

// Construir cláusula WHERE si hay condiciones
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY c.fecha DESC, c.hora DESC";

// Preparar y ejecutar la consulta
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener lista de pacientes para el formulario
$stmt = $conn->query("SELECT id, CONCAT(nombre, ' ', apellido) as nombre FROM pacientes ORDER BY nombre");
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener configuración para ver si multi_medico está habilitado
$stmt = $conn->query("SELECT multi_medico, medico_nombre FROM configuracion WHERE id = 1");
$config = $stmt->fetch(PDO::FETCH_ASSOC);
$multi_medico = isset($config['multi_medico']) && $config['multi_medico'] == 1;

// Obtener lista de doctores para el formulario si multi_medico está habilitado
if ($multi_medico) {
    $stmt = $conn->query("SELECT id, nombre FROM usuarios WHERE rol = 'doctor' ORDER BY nombre");
    $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Si no está habilitado multi_medico, se usa un arreglo con un solo médico (el configurado)
    $doctores = [
        [
            'id' => 1,
            'nombre' => $config['medico_nombre'] ?? 'Médico Tratante'
        ]
    ];
}
?>

<div class="container mt-4">
    <h1><?php echo $title; ?></h1>
    
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['mensaje']); ?></div>
    <?php endif; ?>
    
    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <?php echo $cita ? 'Editar Cita' : 'Nueva Cita'; ?>
        </div>
        <div class="card-body">
            <form method="post">
                <?php if ($cita): ?>
                    <input type="hidden" name="id_cita" value="<?php echo $cita['id']; ?>">
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fecha">Fecha</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" 
                                   value="<?php echo $cita ? $cita['fecha'] : date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="hora">Hora</label>
                            <input type="time" class="form-control" id="hora" name="hora" 
                                   value="<?php echo $cita ? $cita['hora'] : ''; ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="paciente_id">Paciente</label>
                            <select class="form-control" id="paciente_id" name="paciente_id" required>
                                <option value="">Seleccione un paciente</option>
                                <?php foreach ($pacientes as $paciente): ?>
                                    <option value="<?php echo $paciente['id']; ?>" 
                                            <?php echo ($cita && $cita['paciente_id'] == $paciente['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($paciente['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">                        <div class="form-group">
                            <label for="doctor_id">Doctor</label>
                            <?php if ($multi_medico): ?>
                            <select class="form-control" id="doctor_id" name="doctor_id" required>
                                <option value="">Seleccione un doctor</option>
                                <?php foreach ($doctores as $doctor): ?>
                                    <option value="<?php echo $doctor['id']; ?>" 
                                            <?php echo ($cita && $cita['doctor_id'] == $doctor['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($doctor['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php else: ?>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($config['medico_nombre'] ?? 'Médico Tratante'); ?>" readonly>
                            <input type="hidden" name="doctor_id" id="doctor_id" value="1">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="estado">Estado</label>
                            <select class="form-control" id="estado" name="estado">
                                <option value="Pendiente" <?php echo ($cita && $cita['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="Confirmada" <?php echo ($cita && $cita['estado'] == 'Confirmada') ? 'selected' : ''; ?>>Confirmada</option>
                                <option value="Cancelada" <?php echo ($cita && $cita['estado'] == 'Cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                                <option value="Completada" <?php echo ($cita && $cita['estado'] == 'Completada') ? 'selected' : ''; ?>>Completada</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="observaciones">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?php echo $cita ? htmlspecialchars($cita['observaciones']) : ''; ?></textarea>
                        </div>
                    </div>
                </div>
                  <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="Citas.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
      <div class="card mb-4">
        <div class="card-header">
            Filtros de Búsqueda
        </div>
        <div class="card-body">
            <form method="get" class="form-row">
                <div class="form-group col-md-3">
                    <label for="fecha_inicial">Desde:</label>
                    <input type="date" class="form-control" id="fecha_inicial" name="fecha_inicial" 
                           value="<?php echo isset($_GET['fecha_inicial']) ? htmlspecialchars($_GET['fecha_inicial']) : ''; ?>">
                </div>
                <div class="form-group col-md-3">
                    <label for="fecha_final">Hasta:</label>
                    <input type="date" class="form-control" id="fecha_final" name="fecha_final" 
                           value="<?php echo isset($_GET['fecha_final']) ? htmlspecialchars($_GET['fecha_final']) : ''; ?>">
                </div>                <div class="form-group col-md-3">
                    <label for="nombre_filtro">Nombre del paciente:</label>
                    <input type="text" class="form-control" id="nombre_filtro" name="nombre_filtro"
                           placeholder="Buscar por nombre" 
                           value="<?php echo isset($_GET['nombre_filtro']) ? htmlspecialchars($_GET['nombre_filtro']) : ''; ?>">
                </div>
                
                <?php if ($multi_medico): ?>
                <div class="form-group col-md-3">
                    <label for="doctor_filtro">Doctor:</label>
                    <select class="form-control" id="doctor_filtro" name="doctor_filtro">
                        <option value="">Todos los doctores</option>
                        <?php foreach ($doctores as $doctor): ?>
                            <option value="<?php echo $doctor['id']; ?>" 
                                    <?php echo (isset($_GET['doctor_filtro']) && $_GET['doctor_filtro'] == $doctor['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($doctor['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="form-group col-md-3">
                    <label for="estado_filtro">Estado:</label>
                    <select class="form-control" id="estado_filtro" name="estado_filtro">
                        <option value="">Todos los estados</option>
                        <option value="Pendiente" <?php echo (isset($_GET['estado_filtro']) && $_GET['estado_filtro'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="Confirmada" <?php echo (isset($_GET['estado_filtro']) && $_GET['estado_filtro'] == 'Confirmada') ? 'selected' : ''; ?>>Confirmada</option>
                        <option value="Cancelada" <?php echo (isset($_GET['estado_filtro']) && $_GET['estado_filtro'] == 'Cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                        <option value="Completada" <?php echo (isset($_GET['estado_filtro']) && $_GET['estado_filtro'] == 'Completada') ? 'selected' : ''; ?>>Completada</option>
                    </select>
                </div>
                <div class="form-group col-12 mt-3">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <a href="Citas.php" class="btn btn-secondary ml-2">Limpiar filtros</a>
                </div>
            </form>
            
            <?php 
            // Mostrar filtros activos
            $filtros_activos = [];
            if (isset($_GET['filtro']) && $_GET['filtro'] == 'hoy') {
                $filtros_activos[] = "Citas de hoy";
            }
            if (isset($_GET['fecha_inicial']) && !empty($_GET['fecha_inicial'])) {
                $filtros_activos[] = "Desde: " . date('d/m/Y', strtotime($_GET['fecha_inicial']));
            }
            if (isset($_GET['fecha_final']) && !empty($_GET['fecha_final'])) {
                $filtros_activos[] = "Hasta: " . date('d/m/Y', strtotime($_GET['fecha_final']));
            }
            if (isset($_GET['doctor_filtro']) && !empty($_GET['doctor_filtro'])) {
                $doctor_nombre = "";
                foreach($doctores as $doctor) {
                    if ($doctor['id'] == $_GET['doctor_filtro']) {
                        $doctor_nombre = $doctor['nombre'];
                        break;
                    }
                }
                $filtros_activos[] = "Doctor: " . htmlspecialchars($doctor_nombre);
            }            if (isset($_GET['estado_filtro']) && !empty($_GET['estado_filtro'])) {
                $filtros_activos[] = "Estado: " . htmlspecialchars($_GET['estado_filtro']);
            }
            if (isset($_GET['nombre_filtro']) && !empty($_GET['nombre_filtro'])) {
                $filtros_activos[] = "Paciente: " . htmlspecialchars($_GET['nombre_filtro']);
            }
            
            if (!empty($filtros_activos)): 
            ?>
            <div class="mt-3">
                <span class="text-muted">Filtros activos: </span>
                <?php foreach($filtros_activos as $filtro): ?>
                    <span class="badge badge-info filtro-badge"><?php echo htmlspecialchars($filtro); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
      <div class="card">        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Lista de Citas</span>
            <div>
                <a href="Citas.php?filtro=hoy" class="btn btn-sm btn-outline-primary">Hoy</a>
                <a href="<?php echo 'Citas.php?fecha_inicial=' . date('Y-m-d', strtotime('monday this week')) . '&fecha_final=' . date('Y-m-d', strtotime('sunday this week')); ?>" class="btn btn-sm btn-outline-primary">Esta semana</a>
                <a href="<?php echo 'Citas.php?fecha_inicial=' . date('Y-m-01') . '&fecha_final=' . date('Y-m-t'); ?>" class="btn btn-sm btn-outline-primary">Este mes</a>
                <button id="btnEnviarWhatsapp" class="btn btn-sm btn-success"><i class="fab fa-whatsapp"></i> WhatsApp</button>
            </div>
        </div><div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Paciente</th>
                            <th>Doctor</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($citas as $c): ?>
                            <tr>
                                <td><?php echo $c['id']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($c['fecha'])); ?></td>
                                <td><?php echo date('H:i', strtotime($c['hora'])); ?></td>
                                <td><?php echo htmlspecialchars($c['paciente']); ?></td>
                                <td><?php echo htmlspecialchars($c['doctor']); ?></td>
                                <td>
                                    <span class="badge <?php 
                                        echo $c['estado'] == 'Pendiente' ? 'bg-warning' : 
                                            ($c['estado'] == 'Confirmada' ? 'bg-info' : 
                                                ($c['estado'] == 'Completada' ? 'bg-success' : 'bg-danger')); 
                                    ?>">
                                        <?php echo $c['estado']; ?>
                                    </span>
                                </td>                                <td>
                                    <a href="Citas.php?editar=<?php echo $c['id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                                    <a href="Citas.php?eliminar=<?php echo $c['id']; ?>" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('¿Está seguro de eliminar esta cita?')">Eliminar</a>
                                    <a href="turnos.php?agregar_desde_cita=<?php echo $c['id']; ?>&paciente_id=<?php echo $c['paciente_id']; ?>&fecha=<?php echo $c['fecha']; ?>&doctor_id=<?php echo $c['doctor_id']; ?>" class="btn btn-sm btn-success mt-1">Agregar a Turnos</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (count($citas) == 0): ?>
                            <tr>
                                <td colspan="7" class="text-center">No hay citas registradas</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>                </table>
            </div>
        </div>
    </div>
            </div> <!-- /Content -->
        </div> <!-- /row -->
    </div> <!-- /container-fluid -->    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="js/theme-manager.js"></script>
    
    <!-- Preparar los datos de citas para el script de WhatsApp -->
    <script>
        // Variable global con información de citas para WhatsApp
        var citasData = <?php 
            // Extraer los datos de las citas con teléfonos
            $citas_json = [];
            foreach ($citas as $cita) {
                if (!empty($cita['paciente_telefono'])) {
                    // Limpiar el número de teléfono (eliminar todo lo que no sea número)
                    $telefono = preg_replace('/[^0-9]/', '', $cita['paciente_telefono']);
                    
                    // Solo procesar si hay un número válido
                    if (!empty($telefono) && strlen($telefono) >= 8) {
                        $citas_json[] = [
                            'telefono' => $telefono,
                            'paciente' => $cita['paciente'],
                            'fecha' => date('d/m/Y', strtotime($cita['fecha'])),
                            'hora' => date('H:i', strtotime($cita['hora'])),
                            'doctor' => $cita['doctor']
                        ];
                    }
                }
            }
            echo json_encode($citas_json);
        ?>;
    </script>
    
    <!-- Incluir el script de WhatsApp mejorado -->
    <script src="whatsapp_button.js"></script>
    
    <script>
        $(document).ready(function() {
            // Auto-completar fecha final si está vacía cuando se selecciona fecha inicial
            $('#fecha_inicial').on('change', function() {
                if ($('#fecha_inicial').val() && !$('#fecha_final').val()) {
                    $('#fecha_final').val($('#fecha_inicial').val());
                }
            });
            
            // Validar que la fecha final no sea anterior a la fecha inicial
            $('form').on('submit', function(e) {
                var fechaInicial = $('#fecha_inicial').val();
                var fechaFinal = $('#fecha_final').val();
                
                if (fechaInicial && fechaFinal && fechaInicial > fechaFinal) {
                    e.preventDefault();
                    alert('La fecha final no puede ser anterior a la fecha inicial');
                }
            });            // Fin de la inicialización de interfaz
            });
        });
    </script>
</body>
</html>