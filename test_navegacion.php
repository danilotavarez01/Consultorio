<?php
require_once 'session_config.php';
session_start();

// Debug información
error_log("=== TEST NAVEGACION ===");
error_log("Session ID: " . session_id());
error_log("Loggedin: " . (isset($_SESSION['loggedin']) ? ($_SESSION['loggedin'] ? 'true' : 'false') : 'no_existe'));
error_log("User ID: " . (isset($_SESSION['id']) ? $_SESSION['id'] : 'no_existe'));
error_log("Username: " . (isset($_SESSION['username']) ? $_SESSION['username'] : 'no_existe'));

// Verificación simple de sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    error_log("TEST NAVEGACION: Sesión inválida, redirigiendo a login");
    header("location: login.php?error=test_navegacion");
    exit;
}

$_SESSION['last_activity'] = time();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test de Navegación - Consultorio</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        .test-card { margin: 20px 0; }
        .status-ok { color: #28a745; }
        .status-error { color: #dc3545; }
    </style>
</head>
<body>
    <!-- Header con modo oscuro -->
    <?php include 'includes/header.php'; ?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card test-card">
                <div class="card-header bg-primary text-white">
                    <h4><i class="fas fa-vials"></i> Test de Navegación del Sistema</h4>
                </div>
                <div class="card-body">
                    <h5>Estado de la Sesión</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID de Sesión:</strong> <code><?= session_id() ?></code></p>
                            <p><strong>Usuario:</strong> 
                                <span class="status-ok"><?= htmlspecialchars($_SESSION['username'] ?? 'N/A') ?></span>
                            </p>
                            <p><strong>User ID:</strong> 
                                <span class="status-ok"><?= htmlspecialchars($_SESSION['id'] ?? 'N/A') ?></span>
                            </p>
                            <p><strong>Rol:</strong> 
                                <span class="status-ok"><?= htmlspecialchars($_SESSION['role'] ?? $_SESSION['rol'] ?? 'N/A') ?></span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Estado Login:</strong> 
                                <span class="status-ok">✓ Activo</span>
                            </p>
                            <p><strong>Timestamp:</strong> <?= date('Y-m-d H:i:s') ?></p>
                            <p><strong>Última Actividad:</strong> <?= date('H:i:s', $_SESSION['last_activity'] ?? time()) ?></p>
                        </div>
                    </div>

                    <hr>

                    <h5>Enlaces de Navegación (Test)</h5>
                    <p class="text-muted">Estos enlaces deben mantener la sesión activa:</p>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="list-group">
                                <a href="pacientes.php" class="list-group-item list-group-item-action" target="_self">
                                    <i class="fas fa-users"></i> Gestión de Pacientes
                                </a>
                                <a href="citas.php" class="list-group-item list-group-item-action" target="_self">
                                    <i class="fas fa-calendar-check"></i> Gestión de Citas
                                </a>
                                <a href="turnos.php" class="list-group-item list-group-item-action" target="_self">
                                    <i class="fas fa-clock"></i> Gestión de Turnos
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="list-group">
                                <a href="facturacion.php" class="list-group-item list-group-item-action" target="_self">
                                    <i class="fas fa-file-invoice-dollar"></i> Facturación
                                </a>
                                <a href="configuracion.php" class="list-group-item list-group-item-action" target="_self">
                                    <i class="fas fa-cog"></i> Configuración
                                </a>
                                <a href="usuarios.php" class="list-group-item list-group-item-action" target="_self">
                                    <i class="fas fa-user-cog"></i> Usuarios
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="list-group">
                                <a href="index.php" class="list-group-item list-group-item-action" target="_self">
                                    <i class="fas fa-home"></i> Inicio (Original)
                                </a>
                                <a href="index_temporal.php" class="list-group-item list-group-item-action" target="_self">
                                    <i class="fas fa-home"></i> Inicio (Temporal)
                                </a>
                                <a href="test_navegacion.php" class="list-group-item list-group-item-action list-group-item-info" target="_self">
                                    <i class="fas fa-sync"></i> Recargar Test
                                </a>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Instrucciones del Test</h6>
                        <ol>
                            <li>Haga clic en cualquier enlace de navegación arriba</li>
                            <li>Verifique que <strong>NO</strong> sea redirigido al login</li>
                            <li>Verifique que la página se cargue correctamente</li>
                            <li>Use el botón "Atrás" del navegador para volver aquí</li>
                            <li>Si funciona correctamente, el problema de sesión está resuelto</li>
                        </ol>
                    </div>

                    <div class="mt-3">
                        <button class="btn btn-primary" onclick="location.reload()">
                            <i class="fas fa-sync"></i> Actualizar Test
                        </button>
                        <a href="diagnostico_rapido.php" class="btn btn-info">
                            <i class="fas fa-search"></i> Diagnóstico Completo
                        </a>
                        <a href="logout.php" class="btn btn-danger">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="js/theme-manager.js"></script>
<script>
// Debug de JavaScript
console.log('=== TEST NAVEGACION CARGADO ===');
console.log('Sesión activa:', '<?= session_id() ?>');
console.log('Usuario:', '<?= $_SESSION['username'] ?? 'N/A' ?>');

// Mostrar mensaje de confirmación al hacer clic en enlaces
$('a[href$=".php"]').on('click', function(e) {
    const url = $(this).attr('href');
    console.log('Navegando a:', url);
    console.log('Sesión antes de navegar:', '<?= session_id() ?>');
    
    // No prevenir la navegación, solo loggear
    setTimeout(() => {
        console.log('Navegación iniciada a', url);
    }, 100);
});
</script>

</body>
</html>

