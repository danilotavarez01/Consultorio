<?php
// Test simple de logout
require_once 'session_config.php';
session_start();

// Simular un usuario logueado
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'usuario_test';
    $_SESSION['rol'] = 'admin';
    $_SESSION['loggedin'] = true;
    $_SESSION['id'] = 1;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Logout Simple</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/fontawesome.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="fas fa-test-tube"></i> Test de Logout Simple</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> Estado Actual de la Sesión:</h6>
                    <ul class="mb-0">
                        <li><strong>Usuario:</strong> <?= htmlspecialchars($_SESSION['username'] ?? 'No definido') ?></li>
                        <li><strong>Rol:</strong> <?= htmlspecialchars($_SESSION['rol'] ?? 'No definido') ?></li>
                        <li><strong>Loggedin:</strong> <?= isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? 'Sí' : 'No' ?></li>
                        <li><strong>ID de Sesión:</strong> <?= session_id() ?></li>
                        <li><strong>Nombre de Sesión:</strong> <?= session_name() ?></li>
                    </ul>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6>🧪 Métodos de Logout para Probar:</h6>
                        
                        <div class="list-group">
                            <a href="logout.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-sign-out-alt text-danger"></i>
                                <strong>Logout Directo</strong>
                                <small class="d-block text-muted">Sin confirmación</small>
                            </a>
                            
                            <a href="logout.php" onclick="return confirm('¿Confirmas cerrar sesión?')" class="list-group-item list-group-item-action">
                                <i class="fas fa-question-circle text-warning"></i>
                                <strong>Logout con Confirmación</strong>
                                <small class="d-block text-muted">Con ventana de confirmación</small>
                            </a>
                            
                            <button onclick="logoutJS()" class="list-group-item list-group-item-action btn btn-light text-left">
                                <i class="fas fa-code text-info"></i>
                                <strong>Logout via JavaScript</strong>
                                <small class="d-block text-muted">Usando window.location</small>
                            </button>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>📊 Información del Sistema:</h6>
                        <div class="card bg-light">
                            <div class="card-body small">
                                <strong>Session Status:</strong> <?= session_status() === PHP_SESSION_ACTIVE ? 'Activa' : 'Inactiva' ?><br>
                                <strong>Session Path:</strong> <?= session_save_path() ?><br>
                                <strong>Cookie Lifetime:</strong> <?= ini_get('session.cookie_lifetime') ?><br>
                                <strong>GC Max Lifetime:</strong> <?= ini_get('session.gc_maxlifetime') ?><br>
                                <strong>Use Cookies:</strong> <?= ini_get('session.use_only_cookies') ? 'Sí' : 'No' ?>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button onclick="recargarPagina()" class="btn btn-secondary btn-sm">
                                <i class="fas fa-sync-alt"></i> Recargar Página
                            </button>
                            <a href="login.php" class="btn btn-info btn-sm">
                                <i class="fas fa-arrow-left"></i> Ir a Login
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <h6><i class="fas fa-exclamation-triangle"></i> Instrucciones de Prueba:</h6>
                    <ol class="mb-0">
                        <li>Prueba cada método de logout</li>
                        <li>Verifica que te redirija al login</li>
                        <li>Intenta volver a esta página escribiendo la URL directamente</li>
                        <li>Deberías ser redirigido al login si el logout funcionó</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <script>
        function logoutJS() {
            if (confirm('¿Confirmas cerrar sesión via JavaScript?')) {
                console.log('Cerrando sesión via JS...');
                window.location.href = 'logout.php';
            }
        }
        
        function recargarPagina() {
            location.reload();
        }
        
        // Log para debug
        console.log('Test de logout cargado');
        console.log('Session ID:', '<?= session_id() ?>');
        console.log('Usuario actual:', '<?= $_SESSION['username'] ?? 'No definido' ?>');
    </script>
</body>
</html>


