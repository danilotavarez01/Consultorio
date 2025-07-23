<?php
session_start();
require_once "permissions.php";

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Auto logout after inactivity (30 minutes = 1800 seconds)
$inactive_time = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive_time)) {
    // Last activity was more than 30 minutes ago
    session_unset();     // Unset all session variables
    session_destroy();   // Destroy the session
    header("location: login.php?logout=inactive");
    exit;
}
// Update last activity time
$_SESSION['last_activity'] = time();

// Require config but catch any potential errors
try {
    require_once "config.php";
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
    <title>Panel de Control - Consultorio Médico</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; padding: 10px 15px; display: block; }
        .sidebar a:hover { background-color: #454d55; text-decoration: none; }
        .content { padding: 20px; }
        /* Estilos para tarjetas más pequeñas */
        .card-compact .card-header {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            font-weight: bold;
        }
        .card-compact .card-body {
            padding: 0.75rem;
        }
        .card-compact h5.card-title {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }
        .card-compact p.card-text {
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }
        .dashboard-card {
            margin-bottom: 0.75rem;
        }
        /* Reducir el espacio entre filas */
        .mt-compact {
            margin-top: 0.75rem !important;
        }
        /* Hacer las tablas más compactas */
        .table-compact td, .table-compact th {
            padding: 0.4rem;
            font-size: 0.9rem;
        }
        .btn-xs {
            padding: 0.1rem 0.25rem;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

            <!-- Content -->
            <div class="col-md-10 content">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION["nombre"]); ?></h2>
                        <hr>
                        <?php if (!$db_connected): ?>
                        <div class="alert alert-danger">
                            <strong>Error de conexión a la base de datos:</strong> No se puede mostrar la información del panel. 
                            Por favor, contacte al administrador del sistema.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Dashboard Cards -->
                <?php if ($db_connected): ?>
                <div class="row mt-4">
                    <?php if (hasPermission('manage_appointments')): ?>
                    <div class="col-md-4">
                        <div class="card text-white bg-primary mb-3 card-compact">
                            <div class="card-header">Turnos de Hoy</div>
                            <div class="card-body">
                                <?php
                                try {
                                    $sql = "SELECT COUNT(*) as total FROM turnos WHERE fecha_turno = CURDATE()";
                                    $stmt = $conn->query($sql);
                                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                    echo '<h5 class="card-title">' . $row['total'] . ' turnos</h5>';
                                } catch (PDOException $e) {
                                    echo '<h5 class="card-title">Error al cargar datos</h5>';
                                }
                                ?>
                                <p class="card-text">
                                    <a href="turnos.php" class="text-white">Ver turnos <i class="fas fa-arrow-right"></i></a>
                                    <br>
                                    <a href="Citas.php?filtro=hoy" class="text-white mt-2">Ver citas de hoy <i class="fas fa-calendar-check"></i></a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (hasPermission('manage_patients')): ?>
                    <div class="col-md-4">
                        <div class="card text-white bg-success mb-3 card-compact">
                            <div class="card-header">Total Pacientes</div>
                            <div class="card-body">
                                <?php
                                try {
                                    $sql = "SELECT COUNT(*) as total FROM pacientes";
                                    $stmt = $conn->query($sql);
                                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                    echo '<h5 class="card-title">' . $row['total'] . ' pacientes</h5>';
                                } catch (PDOException $e) {
                                    echo '<h5 class="card-title">Error al cargar datos</h5>';
                                }
                                ?>
                                <p class="card-text"><a href="pacientes.php" class="text-white">Ver pacientes <i class="fas fa-arrow-right"></i></a></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (hasPermission('view_appointments')): ?>
                    <div class="col-md-4">
                        <div class="card text-white bg-warning mb-3 card-compact">
                            <div class="card-header">Citas de Hoy</div>
                            <div class="card-body">
                                <?php
                                try {
                                    $sql = "SELECT COUNT(*) as total FROM citas WHERE fecha = CURDATE()";
                                    $stmt = $conn->query($sql);
                                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                    echo '<h5 class="card-title">' . $row['total'] . ' citas</h5>';
                                } catch (PDOException $e) {
                                    echo '<h5 class="card-title">Error al cargar datos</h5>';
                                }
                                ?>
                                <p class="card-text"><a href="Citas.php?filtro=hoy" class="text-white">Ver citas de hoy <i class="fas fa-arrow-right"></i></a></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (hasPermission('manage_prescriptions') || hasPermission('view_prescriptions')): ?>
                    <div class="col-md-4">
                        <div class="card text-white bg-info mb-3 card-compact">
                            <div class="card-header">Recetas del Mes</div>
                            <div class="card-body">
                                <?php
                                try {
                                    $sql = "SELECT COUNT(*) as total FROM recetas WHERE MONTH(fecha_receta) = MONTH(CURRENT_DATE())";
                                    $stmt = $conn->query($sql);
                                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                    echo '<h5 class="card-title">' . $row['total'] . ' recetas</h5>';
                                } catch (PDOException $e) {
                                    echo '<h5 class="card-title">Error al cargar datos</h5>';
                                }
                                ?>
                                <p class="card-text"><a href="recetas.php" class="text-white">Ver recetas <i class="fas fa-arrow-right"></i></a></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Citas del día actual -->
                <?php if (hasPermission('manage_appointments') || hasPermission('view_appointments')): ?>
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Citas de Hoy (<?php echo date('d/m/Y'); ?>)</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-compact">
                                        <thead>
                                            <tr>
                                                <th>Hora</th>
                                                <th>Paciente</th>
                                                <th>Doctor</th>
                                                <th>Estado</th>
                                                <th>Observaciones</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            try {
                                                $sql = "SELECT 
                                                    c.id,
                                                    c.hora,
                                                    c.estado,
                                                    c.observaciones,                                                    CONCAT(p.nombre, ' ', p.apellido) as paciente,
                                                    u.nombre as doctor,
                                                    p.id as paciente_id,
                                                    u.id as doctor_id
                                                    FROM citas c
                                                    JOIN pacientes p ON c.paciente_id = p.id
                                                    JOIN usuarios u ON c.doctor_id = u.id
                                                    WHERE c.fecha = CURDATE()
                                                    ORDER BY c.hora ASC";
                                                $stmt = $conn->query($sql);
                                                $count = 0;
                                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                    $count++;
                                                    $statusClass = '';
                                                    switch($row['estado']) {
                                                        case 'Confirmada': $statusClass = 'success'; break;
                                                        case 'Pendiente': $statusClass = 'warning'; break;
                                                        case 'Cancelada': $statusClass = 'danger'; break;
                                                        case 'Completada': $statusClass = 'info'; break;
                                                        default: $statusClass = 'secondary';
                                                    }
                                                    
                                                    echo "<tr>";
                                                    echo "<td>" . $row['hora'] . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['paciente']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['doctor']) . "</td>";
                                                    echo "<td><span class='badge badge-".$statusClass."'>" . htmlspecialchars($row['estado']) . "</span></td>";
                                                    echo "<td>" . htmlspecialchars($row['observaciones']) . "</td>";
                                                    echo "<td>
                                                        <a href='Citas.php?action=view&id=" . $row['id'] . "' class='btn btn-info btn-sm btn-xs' title='Ver detalles'><i class='fas fa-eye'></i></a>
                                                        <a href='ver_paciente.php?id=" . $row['paciente_id'] . "' class='btn btn-primary btn-sm btn-xs' title='Ver paciente'><i class='fas fa-user'></i></a>
                                                      </td>";
                                                    echo "</tr>";
                                                }
                                                if ($count == 0) {
                                                    echo "<tr><td colspan='6' class='text-center'>No hay citas programadas para hoy</td></tr>";
                                                }
                                            } catch (PDOException $e) {
                                                echo "<tr><td colspan='6' class='text-center'>Error al cargar las citas del día: " . $e->getMessage() . "</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Resumen de actividad reciente -->
                <?php if (hasPermission('view_medical_history')): ?>
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Actividad de Hoy (<?php echo date('d/m/Y'); ?>)</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-compact">
                                        <thead>
                                            <tr>
                                                <th>Hora</th>
                                                <th>Paciente</th>
                                                <th>Tipo</th>
                                                <th>Detalles</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            try {
                                                $sql = "SELECT 
                                                    h.id,
                                                    h.fecha, 
                                                    CONCAT(p.nombre, ' ', p.apellido) as paciente,
                                                    p.id as paciente_id,
                                                    h.tipo_consulta as tipo,
                                                    h.motivo_consulta as detalle
                                                    FROM historial_medico h
                                                    JOIN pacientes p ON h.paciente_id = p.id
                                                    WHERE DATE(h.fecha) = CURDATE()
                                                    ORDER BY h.fecha DESC";
                                                $stmt = $conn->query($sql);
                                                $count = 0;
                                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                    $count++;
                                                    echo "<tr>";
                                                    // Use PHP's date function to format the time
                                                    echo "<td>" . date('H:i', strtotime($row['fecha'])) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['paciente']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['tipo']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['detalle']) . "</td>";
                                                    echo "<td>
                                                        <a href='ver_consulta.php?id=" . $row['id'] . "' class='btn btn-info btn-sm btn-xs' title='Ver detalles'><i class='fas fa-eye'></i></a>
                                                        <a href='ver_paciente.php?id=" . $row['paciente_id'] . "' class='btn btn-primary btn-sm btn-xs' title='Ver paciente'><i class='fas fa-user'></i></a>
                                                      </td>";
                                                    echo "</tr>";
                                                }
                                                if ($count == 0) {
                                                    echo "<tr><td colspan='5' class='text-center'>No hay actividad registrada para hoy</td></tr>";
                                                }
                                            } catch (PDOException $e) {
                                                echo "<tr><td colspan='5'>Error al cargar la actividad del día</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>