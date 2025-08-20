
<?php

require_once 'session_config.php';

session_start();
require_once "permissions.php";

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Check if user has at least one permission
$hasAnyPermission = false;
$permisosRequeridos = [
    'manage_patients', 'manage_appointments', 'view_appointments',
    'manage_prescriptions', 'view_prescriptions', 'manage_diseases',
    'view_medical_history', 'edit_medical_history', 'manage_users',
    'manage_doctors', 'manage_receptionist_permissions'
];

foreach ($permisosRequeridos as $permiso) {
    if (hasPermission($permiso)) {
        $hasAnyPermission = true;
        break;
    }
}

// If user has no permissions, show error message or redirect
if (!$hasAnyPermission && $_SESSION["username"] !== "admin") {
    // Special page for users with no permissions
    header("location: no_permissions.php");
    exit;
}

// Auto logout after inactivity (2 horas = 7200 segundos - más permisivo)
$inactive_time = 7200;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive_time)) {
    // Log de debug para troubleshooting
    error_log("INDEX.PHP: Sesión expirada por inactividad. Última actividad: " . $_SESSION['last_activity'] . ", Tiempo actual: " . time());
    
    // Last activity was more than 2 hours ago
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
    
    // Cargar configuración del consultorio
    $stmt = $conn->query("SELECT nombre_consultorio FROM configuracion WHERE id = 1");
    $config_consultorio = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$config_consultorio) {
        $config_consultorio = ['nombre_consultorio' => 'Consultorio Médico'];
    }
} catch (Exception $e) {
    $db_connected = false;
    $db_error = $e->getMessage();
    $config_consultorio = ['nombre_consultorio' => 'Consultorio Médico'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control - <?php echo htmlspecialchars($config_consultorio['nombre_consultorio'] ?? 'Consultorio Médico'); ?></title>
    <link rel="stylesheet" href="assets/libs/bootstrap.min.css">
    <link rel="stylesheet" href="assets/libs/fontawesome.local.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <link rel="stylesheet" href="assets/css/form-style.css">
    <style>
        body {
            background-color: #f8f9fa;
            color: #212529;
        }
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
        .card-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        .dashboard-card {
            margin-bottom: 0.75rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .dashboard-card .card-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: #fff;
            border-radius: 8px 8px 0 0;
        }
        .dashboard-card .card-body {
            color: #212529;
        }
        .dashboard-card .card-title {
            font-weight: bold;
        }
        .dashboard-card .card-text a {
            color: #fff;
            text-decoration: none;
        }
        .dashboard-card .card-text a:hover {
            opacity: 0.8;
            text-decoration: underline;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f2f2f2;
        }
        .table-hover tbody tr:hover {
            background-color: #e9ecef;
        }
        .table-compact td, .table-compact th {
            padding: 0.3rem;
            font-size: 0.85rem;
        }
        .btn-xs {
            padding: 0.1rem 0.25rem;
            font-size: 0.7rem;
        }
        h2 {
            margin-bottom: 0.5rem;
            color: var(--text-color, #212529); /* Color adaptativo según el tema */
        }
        hr {
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        /* Estilos para modo oscuro */
        [data-theme="dark"] h2 {
            color: #ffffff !important;
        }
        
        /* Estilos para modo claro */
        [data-theme="light"] h2,
        body:not([data-theme]) h2 {
            color: #212529 !important;
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
                <div class="row mt-compact">
                    <?php if (hasPermission('manage_appointments')): ?>
                    <div class="col-md-3">
                        <div class="card text-white bg-primary dashboard-card card-compact">
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
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (hasPermission('manage_patients')): ?>
                    <div class="col-md-3">
                        <div class="card text-white bg-success dashboard-card card-compact">
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
                    <div class="col-md-3">
                        <div class="card text-white bg-warning dashboard-card card-compact">
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
                                <p class="card-text"><a href="Citas.php?filtro=hoy" class="text-white">Ver citas <i class="fas fa-arrow-right"></i></a></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (hasPermission('manage_prescriptions') || hasPermission('view_prescriptions')): ?>
                    <div class="col-md-3">
                        <div class="card text-white bg-info dashboard-card card-compact">
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
                    <?php endif; ?>                </div>

                <!-- Resumen de actividad reciente -->

                <!-- Resumen de actividad reciente -->
                <?php if (hasPermission('view_medical_history')): ?>
                <div class="row mt-compact">
                    <div class="col-md-12">
                        <div class="card card-compact">
                            <div class="card-header">
                                <h5 class="mb-0" style="font-size: 0.95rem;">Actividad de Hoy (<?php echo date('d/m/Y'); ?>)</h5>
                            </div>
                            <div class="card-body p-2">
                                <div class="table-responsive">
                                    <table class="table table-striped table-compact mb-0">
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
                                                    echo "<td>" . date('H:i', strtotime($row['fecha'])) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['paciente']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['tipo']) . "</td>";
                                                    echo "<td>" . substr(htmlspecialchars($row['detalle']), 0, 30) . (strlen($row['detalle']) > 30 ? '...' : '') . "</td>";
                                                    echo "<td>
                                                        <a href='ver_consulta.php?id=" . $row['id'] . "' class='btn btn-info btn-xs' title='Ver detalles'><i class='fas fa-eye'></i></a>
                                                        <a href='ver_paciente.php?id=" . $row['paciente_id'] . "' class='btn btn-primary btn-xs' title='Ver paciente'><i class='fas fa-user'></i></a>
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

    <script src="/assets/libs/jquery-3.6.0.min.js"></script>
    <script src="/assets/libs/bootstrap.bundle.min.js"></script>
    <script src="js/theme-manager.js"></script>
    
    <!-- Inicialización adicional del modo oscuro para index.php -->
    <script>
        // Asegurar que el modo oscuro se aplique correctamente en el inicio
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Index.php: Inicializando modo oscuro...');
            
            // Forzar verificación del tema después de que todo esté cargado
            setTimeout(function() {
                if (window.themeManager) {
                    const currentTheme = window.themeManager.getCurrentTheme();
                    console.log('Index.php: Tema actual detectado:', currentTheme);
                    
                    // Re-aplicar el tema para asegurar que se muestre correctamente
                    window.themeManager.setTheme(currentTheme);
                    window.themeManager.updateToggleButton(currentTheme === 'dark');
                    console.log('Index.php: Tema re-aplicado');
                } else {
                    console.warn('Index.php: ThemeManager no disponible, creando instancia...');
                    window.themeManager = new ThemeManager();
                }
            }, 200);
        });
    </script>
</body>
</html>
