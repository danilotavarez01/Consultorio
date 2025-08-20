<?php
require_once 'session_config.php';
session_start();

// Debug de inicio
error_log("=== INDEX TEMPORAL DEBUG ===");
error_log("Session ID: " . session_id());
error_log("Loggedin: " . (isset($_SESSION['loggedin']) ? ($_SESSION['loggedin'] ? 'true' : 'false') : 'no_existe'));
error_log("User ID: " . (isset($_SESSION['id']) ? $_SESSION['id'] : 'no_existe'));
error_log("Username: " . (isset($_SESSION['username']) ? $_SESSION['username'] : 'no_existe'));

// Verificación MUY simple - solo verificar si existe la variable loggedin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    error_log("INDEX TEMPORAL: Redirigiendo a login - sesión inválida");
    header("location: login.php?error=sesion_invalida");
    exit;
}

// Actualizar última actividad (sin timeout por ahora)
$_SESSION['last_activity'] = time();

error_log("INDEX TEMPORAL: Usuario autenticado correctamente");

// Incluir config de forma segura
$db_connected = false;
try {
    require_once "config.php";
    $db_connected = true;
} catch (Exception $e) {
    error_log("INDEX TEMPORAL: Error de conexión DB: " . $e->getMessage());
    $db_error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control - Consultorio Médico (Modo Temporal)</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="css/dark-mode.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; padding-top: 20px; }
        .sidebar a { color: #fff; padding: 10px 15px; display: block; text-decoration: none; }
        .sidebar a:hover { background-color: #454d55; }
        .content { padding: 20px; }
        .alert-temporal { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
    </style>
</head>
<body>

<!-- Header con modo oscuro -->
<?php include 'includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar">
            <div class="sidebar-sticky">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>MENÚ PRINCIPAL</span>
                </h6>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="index_temporal.php">
                            <i class="fas fa-home"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pacientes.php">
                            <i class="fas fa-users"></i> Gestión de Pacientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="citas.php">
                            <i class="fas fa-calendar-check"></i> Gestión de Citas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="facturacion.php">
                            <i class="fas fa-file-invoice-dollar"></i> Facturación
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="configuracion_impresora_80mm.php">
                            <i class="fas fa-print"></i> Configuración Impresora
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="diagnostico_rapido.php">
                            <i class="fas fa-cog"></i> Diagnóstico
                        </a>
                    </li>
                </ul>

                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>HERRAMIENTAS</span>
                </h6>
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link" href="reparar_sesiones.php">
                            <i class="fas fa-wrench"></i> Reparar Sesiones
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="test_login_basico.php">
                            <i class="fas fa-vial"></i> Test Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Contenido principal -->
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 content">
            
            <div class="alert-temporal">
                <h5><i class="fas fa-exclamation-triangle"></i> Modo Temporal Activo</h5>
                <p><strong>Este es un panel temporal</strong> diseñado para evitar deslogueos mientras se resuelven los problemas de sesión.</p>
                <p><strong>Usuario:</strong> <?= htmlspecialchars($_SESSION['username'] ?? $_SESSION['id'] ?? 'Desconocido') ?> | 
                   <strong>Sesión:</strong> <?= substr(session_id(), 0, 8) ?>... | 
                   <strong>Última actividad:</strong> <?= date('H:i:s') ?></p>
            </div>

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Panel de Control</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group mr-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tarjetas de navegación rápida -->
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-users"></i> Pacientes
                        </div>
                        <div class="card-body">
                            <p class="card-text">Gestión completa de pacientes, historiales médicos y datos personales.</p>
                            <a href="pacientes.php" class="btn btn-primary">Acceder</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <i class="fas fa-calendar-check"></i> Citas
                        </div>
                        <div class="card-body">
                            <p class="card-text">Programación y gestión de citas médicas y turnos.</p>
                            <a href="citas.php" class="btn btn-success">Acceder</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <i class="fas fa-file-invoice-dollar"></i> Facturación
                        </div>
                        <div class="card-body">
                            <p class="card-text">Sistema de facturación, pagos y recibos térmicos.</p>
                            <a href="facturacion.php" class="btn btn-warning">Acceder</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado del sistema -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-info-circle"></i> Estado del Sistema</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Estado de la Base de Datos:</strong> 
                                        <?php if ($db_connected): ?>
                                            <span class="badge badge-success">Conectado</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Error</span>
                                            <?php if (isset($db_error)): ?>
                                                <br><small class="text-danger"><?= htmlspecialchars($db_error) ?></small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </p>
                                    <p><strong>Sesión PHP:</strong> <span class="badge badge-success">Activa</span></p>
                                    <p><strong>ID de Sesión:</strong> <code><?= session_id() ?></code></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Usuario Actual:</strong> <?= htmlspecialchars($_SESSION['username'] ?? 'N/A') ?></p>
                                    <p><strong>Rol:</strong> <?= htmlspecialchars($_SESSION['role'] ?? 'N/A') ?></p>
                                    <p><strong>Última Actividad:</strong> <?= date('Y-m-d H:i:s') ?></p>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <a href="reparar_sesiones.php" class="btn btn-warning btn-sm">
                                    <i class="fas fa-wrench"></i> Reparar Sesiones
                                </a>
                                <a href="diagnostico_rapido.php" class="btn btn-info btn-sm">
                                    <i class="fas fa-search"></i> Diagnóstico Completo
                                </a>
                                <a href="index.php" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-undo"></i> Volver al Index Original
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="js/theme-manager.js"></script>

<script>
// Debug de JavaScript
console.log('=== INDEX TEMPORAL CARGADO ===');
console.log('Sesión activa:', '<?= session_id() ?>');
console.log('Usuario:', '<?= $_SESSION['username'] ?? 'N/A' ?>');

// Verificar estado cada 30 segundos
setInterval(function() {
    console.log('Verificando estado de sesión...');
    // Aquí se podría añadir una verificación AJAX si es necesario
}, 30000);
</script>

</body>
</html>

