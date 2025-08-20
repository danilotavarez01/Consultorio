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
$historial = [];
$error = null;

// Verificar si se proporcionó un ID de paciente
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    
    // Obtener datos del paciente
    $sql = "SELECT * FROM pacientes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$paciente) {
        $error = "Paciente no encontrado";
    } else {
        // Obtener historial médico
        $sql = "SELECT * FROM historial_medico WHERE paciente_id = ? ORDER BY fecha DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    $error = "ID de paciente no proporcionado";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial Médico - Consultorio Médico</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; padding: 10px 15px; display: block; }
        .sidebar a:hover { background-color: #454d55; text-decoration: none; }
        .content { padding: 20px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Content -->
            <div class="col-md-10 content">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2>Historial Médico</h2>
                    <?php if ($paciente): ?>
                    <div>
                        <a href="nueva_consulta.php?paciente_id=<?php echo $paciente['id']; ?>" class="btn btn-success mr-2">
                            <i class="fas fa-plus-circle"></i> Nueva Consulta
                        </a>
                        <a href="ver_paciente.php?id=<?php echo $paciente['id']; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Paciente
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

                <?php if ($paciente): ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Historial de: <?php echo htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']); ?></h4>
                    </div>
                    <div class="card-body">
                        <?php if (count($historial) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">                                <thead class="thead-dark">
                                    <tr>                                        <th>Fecha</th>
                                        <th>Motivo de Consulta</th>
                                        <th>Diagnóstico</th>
                                        <th>Presión Sang.</th>
                                        <th>Frec. Card.</th>
                                        <th>Peso (lb)</th>
                                        <th>Tratamiento</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historial as $consulta): ?>
                                    <tr>                                        <td><?php echo date('d/m/Y', strtotime($consulta['fecha'])); ?></td>
                                        <td><?php echo htmlspecialchars(substr($consulta['motivo_consulta'], 0, 50)) . (strlen($consulta['motivo_consulta']) > 50 ? '...' : ''); ?></td>
                                        <td><?php echo htmlspecialchars(substr($consulta['diagnostico'], 0, 50)) . (strlen($consulta['diagnostico']) > 50 ? '...' : ''); ?></td>
                                        <td><?php echo !empty($consulta['presion_sanguinea']) ? htmlspecialchars($consulta['presion_sanguinea']).' mmHg' : ''; ?></td>
                                        <td><?php echo !empty($consulta['frecuencia_cardiaca']) ? htmlspecialchars($consulta['frecuencia_cardiaca']).' lpm' : ''; ?></td>
                                        <td><?php echo !empty($consulta['peso']) ? htmlspecialchars($consulta['peso']).' lb' : ''; ?></td>
                                        <td><?php echo htmlspecialchars(substr($consulta['tratamiento'], 0, 50)) . (strlen($consulta['tratamiento']) > 50 ? '...' : ''); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="ver_consulta.php?id=<?php echo $consulta['id']; ?>" class="btn btn-info btn-sm" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="imprimir_receta.php?id=<?php echo $consulta['id']; ?>" class="btn btn-primary btn-sm" title="Imprimir receta">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                <a href="editar_consulta.php?id=<?php echo $consulta['id']; ?>" class="btn btn-warning btn-sm" title="Editar consulta">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <p>No hay registros médicos para este paciente.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <!-- Removed the card-footer with the duplicate Nueva Consulta button -->
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
