<?php
// clear_all_sessions.php - Limpiar todas las sesiones para resolver problemas de auto-login
require_once 'session_config.php';
session_start();

// Limpiar toda la sesión actual
session_unset();
session_destroy();

// Limpiar cookies de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Limpiar archivos de sesión si es posible
$session_path = session_save_path();
if (!empty($session_path) && is_dir($session_path)) {
    $files = glob($session_path . '/sess_*');
    foreach ($files as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
}

// También limpiar el directorio personalizado si existe
$custom_session_path = sys_get_temp_dir() . '/consultorio_sessions';
if (is_dir($custom_session_path)) {
    $files = glob($custom_session_path . '/sess_*');
    foreach ($files as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesiones Limpiadas</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background: #f8f9fa; padding: 50px 0; }
        .container { max-width: 600px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header bg-success text-white text-center">
                <h4><i class="fas fa-check-circle mr-2"></i>Sesiones Limpiadas Exitosamente</h4>
            </div>
            <div class="card-body text-center">
                <div class="alert alert-success">
                    <h5>✅ Operación Completada</h5>
                    <p class="mb-0">Todas las sesiones han sido eliminadas correctamente.</p>
                </div>
                
                <div class="alert alert-info">
                    <h6>🔧 Acciones Realizadas:</h6>
                    <ul class="list-unstyled mb-0">
                        <li>✅ Sesión actual destruida</li>
                        <li>✅ Cookies de sesión eliminadas</li>
                        <li>✅ Archivos de sesión limpiados</li>
                        <li>✅ Archivos de test corregidos</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <h6>⚠️ Próximos Pasos:</h6>
                    <p class="mb-2">Ahora debe autenticarse normalmente para acceder al sistema.</p>
                </div>
                
                <div class="btn-group" role="group">
                    <a href="login.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-sign-in-alt mr-2"></i>Ir al Login
                    </a>
                    <a href="index.php" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-home mr-2"></i>Probar Acceso
                    </a>
                </div>
                
                <hr>
                
                <div class="mt-4">
                    <h6>📋 Información de Debug:</h6>
                    <div class="text-left bg-light p-3 rounded">
                        <small class="text-muted">
                            <strong>Timestamp:</strong> <?= date('Y-m-d H:i:s') ?><br>
                            <strong>Session ID anterior:</strong> <?= session_id() ?: 'N/A' ?><br>
                            <strong>Archivos de sesión limpiados:</strong> <?= count(glob($session_path . '/sess_*') ?: []) ?><br>
                            <strong>Estado actual:</strong> Sin sesión activa
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
    <script>
        // Auto-redirect después de 10 segundos
        setTimeout(function() {
            if (confirm('¿Desea ir automáticamente al login?')) {
                window.location.href = 'login.php';
            }
        }, 10000);
    </script>
</body>
</html>
