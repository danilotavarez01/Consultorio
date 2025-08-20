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
$historial = [];

// Verificar si se creó una consulta exitosamente
if (isset($_GET['consulta_creada']) && $_GET['consulta_creada'] == '1') {
    $success = "Consulta médica creada exitosamente.";
}

// Verificar si se proporcionó un ID de paciente
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
      // Obtener datos del paciente
    $sql = "SELECT p.id, p.nombre, p.apellido, p.dni, p.sexo, p.fecha_nacimiento, 
                  p.telefono, p.email, p.direccion, p.fecha_registro, p.seguro_medico, 
                  p.numero_poliza, p.contacto_emergencia, p.telefono_emergencia, p.foto,
                  GROUP_CONCAT(e.nombre SEPARATOR ', ') as enfermedades 
            FROM pacientes p 
            LEFT JOIN paciente_enfermedades pe ON p.id = pe.paciente_id 
            LEFT JOIN enfermedades e ON pe.enfermedad_id = e.id 
            WHERE p.id = ?
            GROUP BY p.id, p.nombre, p.apellido, p.dni, p.sexo, p.fecha_nacimiento, 
                     p.telefono, p.email, p.direccion, p.fecha_registro, p.seguro_medico, 
                     p.numero_poliza, p.contacto_emergencia, p.telefono_emergencia, p.foto";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$paciente) {
        $error = "Paciente no encontrado";
    } else {
        // Calcular edad
        $birthDate = new DateTime($paciente['fecha_nacimiento']);
        $today = new DateTime('today');
        $paciente['edad'] = $birthDate->diff($today)->y;
        
        // Obtener historial médico si existe
        // Primero verificar qué columnas existen en la tabla usuarios
        $columnasUsuarios = [];
        try {
            $stmtColumns = $conn->query("DESCRIBE usuarios");
            $columns = $stmtColumns->fetchAll(PDO::FETCH_ASSOC);
            foreach ($columns as $column) {
                $columnasUsuarios[] = $column['Field'];
            }
        } catch (Exception $e) {
            $columnasUsuarios = ['username']; // fallback básico
        }
        
        // Construir la consulta según las columnas disponibles
        if (in_array('nombre', $columnasUsuarios) && in_array('apellido', $columnasUsuarios)) {
            $sql = "SELECT hm.*, u.nombre as medico_nombre, u.apellido as medico_apellido 
                    FROM historial_medico hm 
                    LEFT JOIN usuarios u ON hm.doctor_id = u.id 
                    WHERE hm.paciente_id = ? 
                    ORDER BY hm.fecha DESC";
        } elseif (in_array('nombre', $columnasUsuarios)) {
            $sql = "SELECT hm.*, u.nombre as medico_nombre 
                    FROM historial_medico hm 
                    LEFT JOIN usuarios u ON hm.doctor_id = u.id 
                    WHERE hm.paciente_id = ? 
                    ORDER BY hm.fecha DESC";
        } elseif (in_array('username', $columnasUsuarios)) {
            $sql = "SELECT hm.*, u.username as medico_nombre 
                    FROM historial_medico hm 
                    LEFT JOIN usuarios u ON hm.doctor_id = u.id 
                    WHERE hm.paciente_id = ? 
                    ORDER BY hm.fecha DESC";
        } else {
            // Sin JOIN si no hay columnas de nombre apropiadas
            $sql = "SELECT hm.*, CONCAT('Doctor ID: ', hm.doctor_id) as medico_nombre 
                    FROM historial_medico hm 
                    WHERE hm.paciente_id = ? 
                    ORDER BY hm.fecha DESC";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Mostrar el médico del turno (no el de la consulta)
        // JOIN historial_medico -> turnos -> usuarios
        $sql = "SELECT hm.*, t.medico_id, t.medico_nombre as medico_turno_nombre, u.nombre as usuario_nombre, u.username as usuario_username
                FROM historial_medico hm
                LEFT JOIN turnos t ON hm.paciente_id = t.paciente_id AND DATE(hm.fecha) = t.fecha_turno
                LEFT JOIN usuarios u ON t.medico_id = u.id
                WHERE hm.paciente_id = ?
                ORDER BY hm.fecha DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    $error = "ID de paciente no proporcionado";
}

// Verificar permisos para gestionar enfermedades
$mostrarEnfermedades = hasPermission('manage_diseases');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalles del Paciente - Consultorio Médico</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; padding: 10px 15px; display: block; }
        .sidebar a:hover { background-color: #454d55; text-decoration: none; }
        .content { padding: 20px; }
        .patient-info { margin-bottom: 20px; }
        .patient-info h3 { border-bottom: 1px solid #dee2e6; padding-bottom: 10px; margin-bottom: 20px; }
        .patient-info .row { margin-bottom: 10px; }
        .foto-paciente {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #ddd;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 15px;
        }
        .foto-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            height: 100%;
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
                    <h2>Detalles del Paciente</h2>
                    <a href="pacientes.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
                </div>
                <hr>

                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if ($paciente): ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><?php echo htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']); ?></h4>
                    </div>                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3 order-md-3">
                                <div class="foto-container">
                                    <?php 
                                    if (!empty($paciente['foto'])) {
                                        // Verificar si el archivo existe y construir la ruta correcta
                                        $rutaFotoLocal = 'uploads/pacientes/' . $paciente['foto'];
                                        $rutaFotoCompleta = __DIR__ . '/' . $rutaFotoLocal;
                                        
                                        if (file_exists($rutaFotoCompleta)) {
                                            echo '<img src="' . htmlspecialchars($rutaFotoLocal) . '" class="foto-paciente" alt="Foto del paciente" '
                                               . 'onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">';
                                            echo '<div class="foto-paciente d-none align-items-center justify-content-center bg-light text-muted" style="display:none!important;">'
                                               . '<i class="fas fa-image fa-2x"></i><br>'
                                               . '<small class="text-muted mt-2">Error al cargar foto</small>'
                                               . '</div>';
                                        } else {
                                            echo '<div class="foto-paciente d-flex align-items-center justify-content-center bg-warning text-dark">'
                                               . '<i class="fas fa-exclamation-triangle fa-2x"></i><br>'
                                               . '<small class="text-muted mt-2">Archivo no encontrado</small><br>'
                                               . '<small class="text-muted">' . htmlspecialchars($paciente['foto']) . '</small>'
                                               . '</div>';
                                        }
                                    } else {
                                        echo '<div class="foto-paciente d-flex align-items-center justify-content-center bg-light text-muted">'
                                           . '<i class="fas fa-user-circle fa-3x"></i><br>'
                                           . '<small class="text-muted mt-2">Sin foto</small>'
                                           . '</div>';
                                    }
                                    ?>
                                    <div class="text-center mt-2">
                                        <small class="text-muted">ID: <?php echo $paciente['id']; ?></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5 patient-info">
                                <h3>Información Personal</h3>
                                <div class="row">
                                    <div class="col-md-4 font-weight-bold">DNI:</div>
                                    <div class="col-md-8"><?php echo htmlspecialchars($paciente['dni']); ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 font-weight-bold">Sexo:</div>
                                    <div class="col-md-8">
                                        <?php 
                                        $sexo = '';
                                        if (isset($paciente['sexo'])) {
                                            switch($paciente['sexo']) {
                                                case 'M': $sexo = 'Masculino'; break;
                                                case 'F': $sexo = 'Femenino'; break;
                                                case 'O': $sexo = 'Otro'; break;
                                                default: $sexo = 'No especificado';
                                            }
                                        } else {
                                            $sexo = 'No especificado';
                                        }
                                        echo $sexo;
                                        ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 font-weight-bold">Fecha de Nacimiento:</div>
                                    <div class="col-md-8"><?php echo date('d/m/Y', strtotime($paciente['fecha_nacimiento'])); ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 font-weight-bold">Edad:</div>
                                    <div class="col-md-8"><?php echo $paciente['edad']; ?> años</div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 font-weight-bold">Dirección:</div>
                                    <div class="col-md-8"><?php echo htmlspecialchars($paciente['direccion'] ?: 'No especificada'); ?></div>
                                </div>                            </div>
                            
                            <div class="col-md-4 patient-info">
                                <h3>Información de Contacto</h3>
                                <div class="row">
                                    <div class="col-md-5 font-weight-bold">Teléfono:</div>
                                    <div class="col-md-7"><?php echo htmlspecialchars($paciente['telefono'] ?: 'No especificado'); ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 font-weight-bold">Email:</div>
                                    <div class="col-md-8"><?php echo htmlspecialchars($paciente['email'] ?: 'No especificado'); ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 font-weight-bold">Contacto de Emergencia:</div>
                                    <div class="col-md-8"><?php echo isset($paciente['contacto_emergencia']) ? htmlspecialchars($paciente['contacto_emergencia']) : 'No especificado'; ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 font-weight-bold">Teléfono de Emergencia:</div>
                                    <div class="col-md-8"><?php echo isset($paciente['telefono_emergencia']) ? htmlspecialchars($paciente['telefono_emergencia']) : 'No especificado'; ?></div>
                                </div>
                            </div>
                        </div>
                          <div class="row mt-4">
                            <div class="col-md-5 patient-info">
                                <h3>Información Médica</h3>
                                <?php if ($mostrarEnfermedades): ?>
                                <div class="row">
                                    <div class="col-md-4 font-weight-bold">Enfermedades:</div>
                                    <div class="col-md-8"><?php echo $paciente['enfermedades'] ? htmlspecialchars($paciente['enfermedades']) : 'Ninguna registrada'; ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-4 patient-info">
                                <h3>Información de Seguro</h3>
                                <div class="row">
                                    <div class="col-md-5 font-weight-bold">Seguro Médico:</div>
                                    <div class="col-md-7"><?php echo isset($paciente['seguro_medico']) ? htmlspecialchars($paciente['seguro_medico']) : 'No especificado'; ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 font-weight-bold">Número de Póliza:</div>
                                    <div class="col-md-8"><?php echo isset($paciente['numero_poliza']) ? htmlspecialchars($paciente['numero_poliza']) : 'No especificado'; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">                            <div>
                                <a href="editar_paciente.php?id=<?php echo $paciente['id']; ?>" class="btn btn-warning mr-2"><i class="fas fa-edit"></i> Editar Paciente</a>
                                <a href="nueva_consulta.php?paciente_id=<?php echo $paciente['id']; ?>" class="btn btn-success"><i class="fas fa-plus-circle"></i> Nueva Consulta</a>
                            </div>
                            <?php if (!empty($paciente['foto'])): ?>
                            <div class="text-muted">
                                <small><i class="fas fa-camera"></i> Foto registrada</small>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Historial Médico Reciente -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0">Historial Médico Reciente</h4>
                    </div>
                    <div class="card-body">
                        <?php if (count($historial) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Motivo</th>
                                            <th>Diagnóstico</th>
                                            <th>Médico</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historial as $registro): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($registro['fecha'])); ?></td>
                                            <td><?php echo htmlspecialchars($registro['motivo_consulta']); ?></td>
                                            <td><?php echo htmlspecialchars($registro['diagnostico']); ?></td>
                                            <td><?php 
                                                // Mostrar el médico del turno
                                                if (!empty($registro['medico_turno_nombre'])) {
                                                    echo htmlspecialchars($registro['medico_turno_nombre']);
                                                } elseif (!empty($registro['usuario_nombre'])) {
                                                    echo htmlspecialchars($registro['usuario_nombre']);
                                                } elseif (!empty($registro['usuario_username'])) {
                                                    echo htmlspecialchars($registro['usuario_username']);
                                                } else {
                                                    echo 'No especificado';
                                                }
                                            ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="ver_consulta.php?id=<?php echo $registro['id']; ?>" class="btn btn-info btn-sm" title="Ver detalles"><i class="fas fa-eye"></i></a>
                                                    <a href="editar_consulta.php?id=<?php echo $registro['id']; ?>" class="btn btn-warning btn-sm" title="Editar consulta"><i class="fas fa-edit"></i></a>
                                                    <a href="imprimir_receta.php?id=<?php echo $registro['id']; ?>" class="btn btn-primary btn-sm" title="Imprimir receta"><i class="fas fa-print"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>                            </div>
                        <?php else: ?>
                            <p class="text-muted">No hay registros médicos para este paciente.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="js/theme-manager.js"></script>
</body>
</html>
