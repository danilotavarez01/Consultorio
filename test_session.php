<?php
/**
 * Script de diagnóstico para sesiones
 * Creado: 2025-06-20
 * 
 * Utilice este script para verificar que las sesiones están funcionando correctamente.
 */

// Incluir el gestor de sesiones mejorado
require_once __DIR__ . '/session_manager.php';

// Registrar visita
$log_message = date('Y-m-d H:i:s') . " - Herramienta de diagnóstico de sesiones ejecutada - ID: " . session_id() . "\n";
@file_put_contents(__DIR__ . '/consulta_test_log.txt', $log_message, FILE_APPEND);

// Función para verificar permisos
function checkPermissions($path) {
    $result = [];
    $result['exists'] = is_dir($path);
    $result['writable'] = is_writable($path);
    $result['readable'] = is_readable($path);
    $result['owner'] = function_exists('posix_getpwuid') ? @posix_getpwuid(fileowner($path))['name'] : 'N/A';
    $result['permissions'] = substr(sprintf('%o', fileperms($path)), -4);
    
    // Test de escritura
    $testFile = $path . '/session_test_file_' . uniqid();
    $writeTest = @file_put_contents($testFile, 'test');
    $result['write_test'] = ($writeTest !== false);
    if ($result['write_test']) {
        @unlink($testFile);
    }
    
    return $result;
}

// Verificar sesión
$sessionActive = (session_status() === PHP_SESSION_ACTIVE);

// Variable de contador para demostrar que la sesión persiste
if (!isset($_SESSION['counter'])) {
    $_SESSION['counter'] = 1;
} else {
    $_SESSION['counter']++;
}

// Obtener información del sistema
$systemInfo = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'session_save_path' => ini_get('session.save_path'),
    'session_name' => session_name(),
    'session_id' => session_id(),
    'session_active' => $sessionActive
];

// Comprobar posibles rutas de sesión
$sessionPaths = [
    'custom_path' => __DIR__ . '/temp/sessions',
    'temp_dir' => __DIR__ . '/temp',
    'root_dir' => __DIR__,
    'sys_temp' => sys_get_temp_dir()
];

$pathResults = [];
foreach ($sessionPaths as $name => $path) {
    $pathResults[$name] = checkPermissions($path);
}

// Para limpiar la sesión si se solicita
if (isset($_GET['reset'])) {
    session_destroy();
    header("Location: test_session.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Sesiones - Consultorio Médico</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col">
                <h1>Diagnóstico de Sesiones</h1>
                <p class="lead">Esta herramienta verifica que las sesiones de PHP estén funcionando correctamente.</p>
                
                <?php if ($sessionActive): ?>
                <div class="alert alert-success">
                    <strong>¡Sesión activa!</strong> El sistema de sesiones está funcionando correctamente.
                </div>
                <?php else: ?>
                <div class="alert alert-danger">
                    <strong>¡Error!</strong> No hay una sesión activa. Esto puede causar problemas de inicio de sesión.
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Contador de Sesión</h3>
            </div>
            <div class="card-body">
                <p>Este contador aumenta cada vez que recarga la página, demostrando que la sesión persiste:</p>
                <h2 class="display-4 text-center"><?php echo $_SESSION['counter']; ?></h2>
                <p class="text-muted text-center">Recargue la página para incrementar el contador.</p>
                <div class="text-center">
                    <a href="test_session.php?reset=1" class="btn btn-warning">Reiniciar Contador</a>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h3 class="mb-0">Información del Sistema</h3>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tbody>
                        <?php foreach ($systemInfo as $key => $value): ?>
                        <tr>
                            <th><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?></th>
                            <td><?php echo htmlspecialchars(is_bool($value) ? ($value ? 'Sí' : 'No') : $value); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h3 class="mb-0">Verificación de Directorios para Sesiones</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Ubicación</th>
                                <th>Existe</th>
                                <th>Permisos de Escritura</th>
                                <th>Prueba de Escritura</th>
                                <th>Permisos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pathResults as $name => $result): ?>
                            <tr>
                                <td>
                                    <code><?php echo htmlspecialchars($sessionPaths[$name]); ?></code>
                                    <?php if ($systemInfo['session_save_path'] === $sessionPaths[$name]): ?>
                                    <span class="badge badge-success">Activo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($result['exists']): ?>
                                    <span class="text-success"><strong>✓</strong></span>
                                    <?php else: ?>
                                    <span class="text-danger"><strong>✗</strong></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($result['writable']): ?>
                                    <span class="text-success"><strong>✓</strong></span>
                                    <?php else: ?>
                                    <span class="text-danger"><strong>✗</strong></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($result['write_test']): ?>
                                    <span class="text-success"><strong>✓</strong></span>
                                    <?php else: ?>
                                    <span class="text-danger"><strong>✗</strong></span>
                                    <?php endif; ?>
                                </td>
                                <td><code><?php echo htmlspecialchars($result['permissions']); ?></code></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <p class="mb-0"><strong>Nota:</strong> Para que las sesiones funcionen correctamente, al menos uno de estos directorios debe existir y tener permisos de escritura.</p>
            </div>
        </div>
        
        <div class="mt-4 text-center">
            <a href="index.php" class="btn btn-primary mr-2">Volver al Inicio</a>
            <a href="diagnostico_campos_dinamicos.php" class="btn btn-secondary">Ver Diagnóstico de Campos</a>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
