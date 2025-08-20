<?php
// Script de configuración de permisos para carpetas de sesiones
// Este script debe ejecutarse con un usuario administrador

// Definir las carpetas para comprobar/crear
$carpetas = [
    __DIR__ . '/temp',
    __DIR__ . '/temp/sessions',
    __DIR__ . '/uploads'
];

// Resultado de operaciones
$resultados = [];

foreach ($carpetas as $carpeta) {
    $resultado = [];
    $resultado['carpeta'] = $carpeta;
    
    // Verificar si existe
    if (!is_dir($carpeta)) {
        // Intentar crear
        $crear = @mkdir($carpeta, 0777, true);
        $resultado['crear'] = $crear ? 'OK' : 'Error';
    } else {
        $resultado['crear'] = 'Ya existe';
    }
    
    // Ajustar permisos
    $permisos = @chmod($carpeta, 0777);
    $resultado['permisos'] = $permisos ? 'OK' : 'Error';
    
    // Verificar permisos actuales
    $resultado['permisos_actuales'] = substr(sprintf('%o', fileperms($carpeta)), -4);
    
    // Verificar escritura
    $test_file = $carpeta . '/test_' . uniqid() . '.txt';
    $escritura = @file_put_contents($test_file, 'test');
    $resultado['escritura'] = ($escritura !== false) ? 'OK' : 'Error';
    
    if ($escritura !== false) {
        @unlink($test_file);
    }
    
    $resultados[] = $resultado;
}

// Verificar nombre de usuario para IIS
$usuario_iis = 'N/A';
if (function_exists('exec')) {
    @exec('whoami', $output);
    if (!empty($output)) {
        $usuario_iis = $output[0];
    }
}

// Información de permisos para cada carpeta
$permisos_info = "Información de permisos de carpetas:\n";
foreach ($resultados as $r) {
    $permisos_info .= "- Carpeta: {$r['carpeta']}\n";
    $permisos_info .= "  - Crear: {$r['crear']}\n";
    $permisos_info .= "  - Permisos: {$r['permisos']}\n";
    $permisos_info .= "  - Permisos actuales: {$r['permisos_actuales']}\n";
    $permisos_info .= "  - Prueba escritura: {$r['escritura']}\n";
    $permisos_info .= "\n";
}

// Registrar en log
file_put_contents(__DIR__ . '/consulta_test_log.txt', 
    date('Y-m-d H:i:s') . " - Configuración de permisos ejecutada\n" .
    "Usuario IIS: $usuario_iis\n" .
    $permisos_info . 
    "\n", 
    FILE_APPEND
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Permisos</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
    <div class="container py-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h1 class="h3 mb-0">Configuración de Permisos</h1>
            </div>
            <div class="card-body">
                <h2>Resultados de la configuración</h2>
                
                <table class="table table-striped mt-4">
                    <thead>
                        <tr>
                            <th>Carpeta</th>
                            <th>Crear</th>
                            <th>Permisos</th>
                            <th>Permisos actuales</th>
                            <th>Prueba escritura</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultados as $resultado): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($resultado['carpeta']); ?></code></td>
                            <td class="<?php echo $resultado['crear'] === 'Error' ? 'text-danger' : 'text-success'; ?>">
                                <?php echo htmlspecialchars($resultado['crear']); ?>
                            </td>
                            <td class="<?php echo $resultado['permisos'] === 'Error' ? 'text-danger' : 'text-success'; ?>">
                                <?php echo htmlspecialchars($resultado['permisos']); ?>
                            </td>
                            <td><code><?php echo htmlspecialchars($resultado['permisos_actuales']); ?></code></td>
                            <td class="<?php echo $resultado['escritura'] === 'Error' ? 'text-danger' : 'text-success'; ?>">
                                <?php echo htmlspecialchars($resultado['escritura']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="mt-4">
                    <h3>Usuario IIS:</h3>
                    <pre class="bg-light p-3"><?php echo htmlspecialchars($usuario_iis); ?></pre>
                </div>
                
                <div class="alert alert-info mt-4">
                    <p><strong>Nota:</strong> Para que el sistema funcione correctamente, asegúrese de que todas las carpetas tengan permisos de escritura.</p>
                    <p>Esta información se ha guardado en el archivo de log para diagnóstico futuro.</p>
                </div>
            </div>
            <div class="card-footer">
                <a href="test_session.php" class="btn btn-primary">Probar Sesiones</a>
                <a href="index.php" class="btn btn-secondary">Volver al Inicio</a>
            </div>
        </div>
    </div>
</body>
</html>

